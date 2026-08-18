<?php

declare(strict_types=1);

namespace App\Modules\Sales\Application\Services;

use App\Modules\Bom\Application\Services\BomExplosionService;
use App\Modules\Bom\Infrastructure\Persistence\Models\BomHeader;
use App\Modules\Inventory\Application\Services\InventoryService;
use App\Modules\Inventory\Infrastructure\Persistence\Models\InventoryBalance;
use App\Modules\Inventory\Infrastructure\Persistence\Models\InventoryReservation;
use App\Modules\Product\Infrastructure\Persistence\Models\Product;
use App\Modules\Production\Application\Services\ProductionOrderService;
use App\Modules\Production\Infrastructure\Persistence\Models\ProductionOrder;
use App\Modules\Sales\Infrastructure\Persistence\Models\Sale;
use App\Modules\Tenant\Infrastructure\Persistence\Models\Warehouse;
use App\Shared\Application\Cache\CacheManager;
use App\Shared\Application\Services\BaseService;
use App\Shared\Application\Transactions\TransactionManager;
use App\Shared\Infrastructure\Logging\AppLogger;

final class SaleFulfillmentService extends BaseService
{
    private const PRODUCTION_PRODUCT_TYPES = ['FG', 'WIP'];

    public function __construct(
        private readonly BomExplosionService $bomExplosionService,
        private readonly InventoryService $inventoryService,
        private readonly ProductionOrderService $productionOrderService,
        TransactionManager $transaction,
        CacheManager $cache,
        AppLogger $logger
    ) {
        parent::__construct($transaction, $cache, $logger);
    }

    public function fulfillConfirmedSale(Sale $sale, ?int $userId = null): array
    {
        $sale->loadMissing(['lines.product']);

        $referenceDate = $sale->sale_date?->toDateString() ?? now()->toDateString();
        $createdOrders = [];
        $reservationIds = [];
        $hasExistingOrders = ProductionOrder::query()
            ->where('source_reference_type', 'sale')
            ->where('source_reference_id', $sale->id)
            ->exists();

        foreach ($sale->lines as $line) {
            $lineQuantity = round((float) $line->quantity, 6);

            if ($lineQuantity <= 0) {
                continue;
            }

            $warehouseId = $this->resolveWarehouseId((int) $sale->company_id, (int) $line->product_id);

            if ($warehouseId === null) {
                continue;
            }

            $alreadyReserved = $this->reservedQuantityForSaleProduct((int) $sale->id, (int) $line->product_id);
            $toReserve = max(0.0, round($lineQuantity - $alreadyReserved, 6));

            if ($toReserve > 0) {
                $reserved = $this->reserveAvailable(
                    sale: $sale,
                    warehouseId: $warehouseId,
                    productId: (int) $line->product_id,
                    requestedQuantity: $toReserve,
                    reservationOrigin: 'SALE',
                    priority: 10,
                    userId: $userId,
                    metadata: [
                        'sale_id' => (int) $sale->id,
                        'sale_line_id' => (int) $line->id,
                        'allocation_scope' => sprintf('sale:%d', (int) $sale->id),
                        'allocation_type' => 'finished_good',
                    ]
                );

                $reservationIds = array_merge($reservationIds, $reserved['reservation_ids']);
                $toReserve = max(0.0, round($toReserve - $reserved['quantity'], 6));
            }

            if ($toReserve <= 0 || $hasExistingOrders) {
                continue;
            }

            $product = $line->product;

            if (! $product instanceof Product || ! in_array((string) $product->product_type, self::PRODUCTION_PRODUCT_TYPES, true)) {
                continue;
            }

            $requirements = $this->buildProductionRequirements((int) $product->id, $toReserve, $referenceDate);

            foreach ($requirements as $requirement) {
                $requirementProductId = (int) $requirement['product_id'];
                $requirementLevel = (int) $requirement['level'];
                $requiredQuantity = round((float) $requirement['quantity'], 6);

                if ($requiredQuantity <= 0) {
                    continue;
                }

                $requirementWarehouseId = $this->resolveWarehouseId((int) $sale->company_id, $requirementProductId);

                if ($requirementWarehouseId === null) {
                    continue;
                }

                $quantityToProduce = $requiredQuantity;

                if ($requirementLevel > 0) {
                    $componentReservation = $this->reserveAvailable(
                        sale: $sale,
                        warehouseId: $requirementWarehouseId,
                        productId: $requirementProductId,
                        requestedQuantity: $requiredQuantity,
                        reservationOrigin: 'PRODUCTION',
                        priority: 20,
                        userId: $userId,
                        metadata: [
                            'sale_id' => (int) $sale->id,
                            'sale_line_id' => (int) $line->id,
                            'allocation_scope' => sprintf('sale:%d', (int) $sale->id),
                            'allocation_type' => 'production_component',
                            'dependency_level' => $requirementLevel,
                            'root_product_id' => (int) $line->product_id,
                        ]
                    );

                    $reservationIds = array_merge($reservationIds, $componentReservation['reservation_ids']);
                    $quantityToProduce = max(0.0, round($requiredQuantity - $componentReservation['quantity'], 6));
                }

                if ($quantityToProduce <= 0 || ! $this->hasActiveBom($requirementProductId, $referenceDate)) {
                    continue;
                }

                $createdOrder = $this->productionOrderService->createForSale([
                    'product_id' => $requirementProductId,
                    'warehouse_id' => $requirementWarehouseId,
                    'quantity_planned' => $quantityToProduce,
                    'quantity_scrapped' => 0,
                    'reference_date' => $referenceDate,
                    'source_reference_id' => (int) $sale->id,
                    'source_reference_type' => 'sale',
                    'metadata' => [
                        'sale_id' => (int) $sale->id,
                        'sale_line_id' => (int) $line->id,
                        'root_product_id' => (int) $line->product_id,
                        'dependency_level' => $requirementLevel,
                        'allocation_scope' => sprintf('sale:%d', (int) $sale->id),
                    ],
                ], $userId);

                $createdOrders[] = [
                    'product_id' => $requirementProductId,
                    'level' => $requirementLevel,
                    'quantity_planned' => $quantityToProduce,
                    'order_id' => $createdOrder['id'] ?? null,
                ];
            }
        }

        return [
            'sale_id' => (int) $sale->id,
            'reservations_created' => count(array_unique($reservationIds)),
            'production_orders_created' => count($createdOrders),
            'orders' => $createdOrders,
        ];
    }

    public function releaseReservationsForCanceledSale(Sale $sale, ?int $userId = null): int
    {
        $reservationIds = InventoryReservation::query()
            ->where('reference_type', 'sale')
            ->where('reference_id', $sale->id)
            ->where('status', 'RESERVED')
            ->orderBy('priority')
            ->orderBy('id')
            ->pluck('id')
            ->all();

        $released = 0;

        foreach ($reservationIds as $reservationId) {
            $this->inventoryService->releaseReservation((int) $reservationId, [
                'reason' => 'sale_cancelled',
                'notes' => sprintf('Reserva liberada por cancelamento da venda %d.', (int) $sale->id),
                'metadata' => [
                    'sale_id' => (int) $sale->id,
                ],
            ], $userId);

            $released++;
        }

        return $released;
    }

    /** @return list<array{product_id: int, level: int, quantity: float}> */
    private function buildProductionRequirements(int $rootProductId, float $rootQuantity, string $referenceDate): array
    {
        $requirements = [
            '0:'.$rootProductId => [
                'product_id' => $rootProductId,
                'level' => 0,
                'quantity' => round($rootQuantity, 6),
            ],
        ];

        if (! $this->hasActiveBom($rootProductId, $referenceDate)) {
            return [];
        }

        $explosion = $this->bomExplosionService->explode($rootProductId, $referenceDate);

        foreach ($explosion['exploded_materials'] as $material) {
            $componentProductId = (int) $material['component_product_id'];
            $component = Product::query()->find($componentProductId);

            if (! $component instanceof Product || ! in_array((string) $component->product_type, self::PRODUCTION_PRODUCT_TYPES, true)) {
                continue;
            }

            $level = (int) $material['level'];
            $requiredQuantity = round($rootQuantity * (float) $material['quantity_accumulated'], 6);
            $key = $level.':'.$componentProductId;

            if (! isset($requirements[$key])) {
                $requirements[$key] = [
                    'product_id' => $componentProductId,
                    'level' => $level,
                    'quantity' => 0.0,
                ];
            }

            $requirements[$key]['quantity'] = round((float) $requirements[$key]['quantity'] + $requiredQuantity, 6);
        }

        $rows = array_values($requirements);

        usort($rows, static function (array $left, array $right): int {
            $levelCompare = $right['level'] <=> $left['level'];

            if ($levelCompare !== 0) {
                return $levelCompare;
            }

            return $left['product_id'] <=> $right['product_id'];
        });

        return $rows;
    }

    /**
     * @param  array<string, mixed>  $metadata
     * @return array{quantity: float, reservation_ids: list<int>}
     */
    private function reserveAvailable(
        Sale $sale,
        int $warehouseId,
        int $productId,
        float $requestedQuantity,
        string $reservationOrigin,
        int $priority,
        ?int $userId,
        array $metadata
    ): array {
        $available = round((float) (InventoryBalance::query()
            ->where('warehouse_id', $warehouseId)
            ->where('product_id', $productId)
            ->value('qty_available') ?? 0), 6);
        $quantity = min($requestedQuantity, $available);

        if ($quantity <= 0) {
            return ['quantity' => 0.0, 'reservation_ids' => []];
        }

        $reserved = $this->inventoryService->reserveStock([
            'warehouse_id' => $warehouseId,
            'product_id' => $productId,
            'reservation_origin' => $reservationOrigin,
            'priority' => $priority,
            'quantity' => $quantity,
            'reference_type' => 'sale',
            'reference_id' => (int) $sale->id,
            'notes' => sprintf('Reserva automática para venda %d.', (int) $sale->id),
            'metadata' => $metadata,
        ], $userId);

        return [
            'quantity' => round((float) ($reserved['reservation']['quantity'] ?? 0), 6),
            'reservation_ids' => isset($reserved['reservation']['id']) ? [(int) $reserved['reservation']['id']] : [],
        ];
    }

    private function reservedQuantityForSaleProduct(int $saleId, int $productId): float
    {
        return round((float) InventoryReservation::query()
            ->where('reference_type', 'sale')
            ->where('reference_id', $saleId)
            ->where('product_id', $productId)
            ->where('status', 'RESERVED')
            ->sum('quantity'), 6);
    }

    private function resolveWarehouseId(int $companyId, int $productId): ?int
    {
        $warehouseId = InventoryBalance::query()
            ->select('inventory_balances.warehouse_id')
            ->join('warehouses', 'warehouses.id', '=', 'inventory_balances.warehouse_id')
            ->where('warehouses.company_id', $companyId)
            ->where('inventory_balances.product_id', $productId)
            ->orderByDesc('inventory_balances.qty_available')
            ->value('inventory_balances.warehouse_id');

        if ($warehouseId !== null) {
            return (int) $warehouseId;
        }

        $fallbackWarehouse = Warehouse::query()
            ->where('company_id', $companyId)
            ->orderBy('id')
            ->value('id');

        return $fallbackWarehouse !== null ? (int) $fallbackWarehouse : null;
    }

    private function hasActiveBom(int $productId, string $referenceDate): bool
    {
        return BomHeader::query()
            ->where('product_id', $productId)
            ->where('status', 'APPROVED')
            ->whereDate('effective_from', '<=', $referenceDate)
            ->where(static function ($query) use ($referenceDate): void {
                $query
                    ->whereNull('effective_to')
                    ->orWhereDate('effective_to', '>=', $referenceDate);
            })
            ->exists();
    }
}
