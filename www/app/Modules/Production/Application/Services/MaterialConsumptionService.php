<?php

declare(strict_types=1);

namespace App\Modules\Production\Application\Services;

use App\Modules\Inventory\Application\Services\InventoryService;
use App\Modules\Production\Infrastructure\Persistence\Models\ProductionOrder;
use App\Modules\Production\Infrastructure\Persistence\Models\ProductionOrderMaterialConsumption;
use App\Modules\Product\Infrastructure\Persistence\Models\Product;
use App\Modules\Tenant\Infrastructure\Persistence\Models\Warehouse;
use App\Shared\Application\Cache\CacheManager;
use App\Shared\Application\Services\BaseService;
use App\Shared\Application\Transactions\TransactionManager;
use App\Shared\Infrastructure\Logging\AppLogger;
use App\Shared\Presentation\Exceptions\DomainException;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class MaterialConsumptionService extends BaseService
{
    public function __construct(
        private readonly InventoryService $inventoryService,
        TransactionManager $transaction,
        CacheManager $cache,
        AppLogger $logger
    ) {
        parent::__construct($transaction, $cache, $logger);
    }

    public function paginate(int $productionOrderId, int $perPage = 15): LengthAwarePaginator
    {
        return ProductionOrderMaterialConsumption::query()
            ->with(['product', 'warehouse'])
            ->where('production_order_id', $productionOrderId)
            ->orderByDesc('consumed_at')
            ->orderByDesc('id')
            ->paginate($perPage);
    }

    public function record(int $productionOrderId, array $payload, ?int $operatorId = null): array
    {
        $order = ProductionOrder::query()->findOrFail($productionOrderId);

        if (in_array($order->status, ['DRAFT', 'COMPLETED', 'CANCELLED'], true)) {
            throw new DomainException(
                sprintf('Cannot record material consumption for a production order in status [%s]', $order->status),
                422
            );
        }

        Product::query()->findOrFail((int) $payload['product_id']);
        Warehouse::query()->findOrFail((int) $payload['warehouse_id']);

        $quantityConsumed = round((float) $payload['quantity_consumed'], 6);
        $quantityScrapped = round((float) ($payload['quantity_scrapped'] ?? 0), 6);
        $consumedAt = isset($payload['consumed_at'])
            ? Carbon::parse($payload['consumed_at'])
            : now();

        $result = $this->inTransaction(function () use ($order, $payload, $quantityConsumed, $quantityScrapped, $consumedAt, $operatorId) {
            // Post ISSUE movement to the stock ledger so balance is updated
            $ledgerResult = $this->inventoryService->postMovement([
                'warehouse_id' => $payload['warehouse_id'],
                'product_id' => $payload['product_id'],
                'movement_type' => 'ISSUE',
                'quantity' => $quantityConsumed,
                'allocation_strategy' => $payload['allocation_strategy'] ?? null,
                'lot_number' => $payload['lot_number'] ?? null,
                'reference_type' => 'production_order',
                'reference_id' => $order->id,
                'notes' => $payload['notes'] ?? null,
                'metadata' => array_merge(
                    (array) ($payload['metadata'] ?? []),
                    ['operator_id' => $operatorId]
                ),
                'movement_at' => $consumedAt->toDateTimeString(),
            ], $operatorId);

            $ledgerMovementId = (int) $ledgerResult['movement']['id'];

            $consumption = ProductionOrderMaterialConsumption::query()->create([
                'company_id' => $order->company_id,
                'production_order_id' => $order->id,
                'product_id' => $payload['product_id'],
                'warehouse_id' => $payload['warehouse_id'],
                'lot_number' => $payload['lot_number'] ?? null,
                'quantity_consumed' => $quantityConsumed,
                'quantity_scrapped' => $quantityScrapped,
                'ledger_movement_id' => $ledgerMovementId,
                'reference_bom_component_id' => $payload['reference_bom_component_id'] ?? null,
                'consumed_at' => $consumedAt,
                'operator_id' => $operatorId,
                'notes' => $payload['notes'] ?? null,
                'metadata' => $payload['metadata'] ?? null,
            ]);

            return $consumption;
        });

        $this->logger->info('production_order.material_consumed', [
            'production_order_id' => $productionOrderId,
            'product_id' => $payload['product_id'],
            'quantity_consumed' => $quantityConsumed,
            'lot_number' => $payload['lot_number'] ?? null,
            'operator_id' => $operatorId,
        ]);

        return $result->refresh()->load(['product', 'warehouse'])->toArray();
    }

    public function aggregateByProduct(int $productionOrderId): array
    {
        $consumptions = ProductionOrderMaterialConsumption::query()
            ->with(['product'])
            ->where('production_order_id', $productionOrderId)
            ->get();

        $aggregated = [];

        foreach ($consumptions as $item) {
            $productId = (int) $item->product_id;

            if (! isset($aggregated[$productId])) {
                $aggregated[$productId] = [
                    'product_id' => $productId,
                    'product' => $item->product?->toArray(),
                    'total_consumed' => 0.0,
                    'total_scrapped' => 0.0,
                    'lots' => [],
                ];
            }

            $aggregated[$productId]['total_consumed'] = round($aggregated[$productId]['total_consumed'] + (float) $item->quantity_consumed, 6);
            $aggregated[$productId]['total_scrapped'] = round($aggregated[$productId]['total_scrapped'] + (float) $item->quantity_scrapped, 6);

            if ($item->lot_number !== null) {
                $lot = $item->lot_number;
                $aggregated[$productId]['lots'][$lot] = round(
                    ($aggregated[$productId]['lots'][$lot] ?? 0.0) + (float) $item->quantity_consumed,
                    6
                );
            }
        }

        return array_values(array_map(static function (array $row): array {
            $row['lots'] = array_map(
                static fn (string $lot, float $qty): array => ['lot_number' => $lot, 'quantity' => $qty],
                array_keys($row['lots']),
                array_values($row['lots'])
            );
            return $row;
        }, $aggregated));
    }
}
