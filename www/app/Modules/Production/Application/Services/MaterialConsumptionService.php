<?php

declare(strict_types=1);

namespace App\Modules\Production\Application\Services;

use App\Modules\Inventory\Application\Services\InventoryService;
use App\Modules\Production\Infrastructure\Persistence\Models\ProductionOrder;
use App\Modules\Production\Infrastructure\Persistence\Models\ProductionOrderMaterialConsumption;
use App\Modules\Production\Infrastructure\Persistence\Models\ProductionOrderOperation;
use App\Modules\Production\Infrastructure\Persistence\Models\ProductionMaterialConsumptionReversal;
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
        $order = ProductionOrder::query()->with('snapshot.bomSnapshot.items')->findOrFail($productionOrderId);

        if (in_array($order->status, ['DRAFT', 'COMPLETED', 'CANCELLED'], true)) {
            throw new DomainException(
                sprintf('Cannot record material consumption for a production order in status [%s]', $order->status),
                422
            );
        }

        Product::query()->findOrFail((int) $payload['product_id']);
        Warehouse::query()->findOrFail((int) $payload['warehouse_id']);

        $operation = null;
        if (! empty($payload['production_order_operation_id'])) {
            $operation = ProductionOrderOperation::query()->where('production_order_id', $order->id)->findOrFail((int) $payload['production_order_operation_id']);
        }
        $bomItems = $order->snapshot?->bomSnapshot?->items?->filter(fn ($item) => (int) $item->component_product_id === (int) $payload['product_id']) ?? collect();
        $bomItem = isset($payload['reference_bom_component_id'])
            ? $bomItems->first(fn ($item) => (string) $item->id === (string) $payload['reference_bom_component_id'])
            : $bomItems->first();
        $plannedBomQuantity = $bomItems->sum('quantity_required');
        if (! $bomItem && isset($payload['reference_bom_component_id'])) throw new DomainException('The selected BOM component does not belong to the consumed product', 422);
        if (! $bomItem && empty($payload['allow_unplanned'])) throw new DomainException('Consumed product is not present in the frozen BOM snapshot; set allow_unplanned to register additional consumption', 422);

        $quantityConsumed = round((float) $payload['quantity_consumed'], 6);
        $quantityScrapped = round((float) ($payload['quantity_scrapped'] ?? 0), 6);
        $idempotencyKey = $payload['idempotency_key'] ?? null;
        if ($idempotencyKey) {
            $previous = ProductionOrderMaterialConsumption::query()->where('idempotency_key', $idempotencyKey)->first();
            if ($previous) return $previous->refresh()->load(['product', 'warehouse'])->toArray();
        }
        $alreadyConsumed = (float) ProductionOrderMaterialConsumption::query()->where('production_order_id', $order->id)->where('product_id', $payload['product_id'])->whereNull('reversed_by_movement_id')->sum('quantity_consumed');
        if ($alreadyConsumed + $quantityConsumed > (float) $plannedBomQuantity + 0.000001 && empty($payload['allow_excess'])) throw new DomainException('Consumption exceeds the frozen BOM requirement', 422);
        $consumedAt = isset($payload['consumed_at'])
            ? Carbon::parse($payload['consumed_at'])
            : now();

        $result = $this->inTransaction(function () use ($order, $operation, $payload, $quantityConsumed, $quantityScrapped, $consumedAt, $operatorId, $idempotencyKey) {
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
                'production_order_operation_id' => $operation?->id,
                'product_id' => $payload['product_id'],
                'warehouse_id' => $payload['warehouse_id'],
                'lot_number' => $payload['lot_number'] ?? null,
                'quantity_consumed' => $quantityConsumed,
                'quantity_scrapped' => $quantityScrapped,
                'ledger_movement_id' => $ledgerMovementId,
                'reference_bom_component_id' => $payload['reference_bom_component_id'] ?? null,
                'consumed_at' => $consumedAt,
                'operator_id' => $operatorId,
                'idempotency_key' => $idempotencyKey,
                'notes' => $payload['notes'] ?? null,
                'metadata' => array_merge((array) ($payload['metadata'] ?? []), [
                    'is_unplanned' => $bomItem === null,
                    'bom_component_id' => $bomItem?->id,
                    'planned_quantity' => $bomItem ? $plannedBomQuantity : null,
                ]),
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

    public function reverse(int $consumptionId, string $reason, ?int $userId = null): array
    {
        if (trim($reason) === '') throw new DomainException('A reason is required to reverse material consumption', 422);
        $consumption = ProductionOrderMaterialConsumption::query()->findOrFail($consumptionId);
        $existing = ProductionMaterialConsumptionReversal::query()->where('production_order_material_consumption_id', $consumption->id)->first();
        if ($existing) return $existing->load('consumption')->toArray();
        if ($consumption->reversed_by_movement_id) throw new DomainException('Consumption has already been reversed', 422);
        $result = $this->inTransaction(function () use ($consumption, $reason, $userId) {
            $reversal = $this->inventoryService->reverseMovement(['movement_id' => $consumption->ledger_movement_id, 'reason' => $reason], $userId);
            $movementId = (int) ($reversal['reversal_movement']['id'] ?? 0);
            $consumption->reversed_by_movement_id = $movementId;
            $consumption->save();
            return ProductionMaterialConsumptionReversal::query()->create(['company_id'=>$consumption->company_id,'production_order_material_consumption_id'=>$consumption->id,'original_ledger_movement_id'=>$consumption->ledger_movement_id,'reversal_ledger_movement_id'=>$movementId,'quantity'=>$consumption->quantity_consumed,'reason'=>$reason,'created_by'=>$userId]);
        });
        return $result->load('consumption')->toArray();
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

            if (! $item->reversed_by_movement_id) $aggregated[$productId]['total_consumed'] = round($aggregated[$productId]['total_consumed'] + (float) $item->quantity_consumed, 6);
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
