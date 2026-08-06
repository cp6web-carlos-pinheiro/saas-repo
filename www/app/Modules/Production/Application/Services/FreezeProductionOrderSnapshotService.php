<?php

declare(strict_types=1);

namespace App\Modules\Production\Application\Services;

use App\Modules\Bom\Infrastructure\Persistence\Models\ProductionOrderBomSnapshot;
use App\Modules\Production\Infrastructure\Persistence\Models\ProductionOrder;
use App\Modules\Production\Infrastructure\Persistence\Models\ProductionOrderRoutingOperationSnapshot;
use App\Modules\Production\Infrastructure\Persistence\Models\ProductionOrderSnapshot;
use App\Modules\Routing\Infrastructure\Persistence\Models\RoutingOperationSnapshot;
use App\Modules\Routing\Infrastructure\Persistence\Models\RoutingVersionSnapshot;
use App\Shared\Application\Cache\CacheManager;
use App\Shared\Application\Services\BaseService;
use App\Shared\Application\Transactions\TransactionManager;
use App\Shared\Infrastructure\Logging\AppLogger;
use App\Shared\Presentation\Exceptions\DomainException;

final class FreezeProductionOrderSnapshotService extends BaseService
{
    public function __construct(
        TransactionManager $transaction,
        CacheManager $cache,
        AppLogger $logger
    ) {
        parent::__construct($transaction, $cache, $logger);
    }

    public function freeze(int $productionOrderId, ?int $frozenBy = null): array
    {
        $order = ProductionOrder::query()->findOrFail($productionOrderId);

        // Idempotency: return existing snapshot if already frozen
        $existing = ProductionOrderSnapshot::query()
            ->where('company_id', $order->company_id)
            ->where('production_order_id', $order->id)
            ->first();

        if ($existing) {
            return $this->serializeSnapshot($existing->load(['product', 'bomSnapshot', 'routingOperations']));
        }

        // Resolve BOM snapshot (must have been frozen during OP creation)
        $bomSnapshot = ProductionOrderBomSnapshot::query()
            ->where('company_id', $order->company_id)
            ->where('production_order_id', $order->id)
            ->first();

        if (! $bomSnapshot) {
            throw new DomainException('BOM snapshot not found for this production order. Cannot freeze production snapshot.', 422);
        }

        // Resolve routing version snapshot if routing was assigned
        $routingVersionSnapshot = null;
        $routingOperations = collect();

        if ($order->routing_version_id !== null) {
            $routingVersionSnapshot = RoutingVersionSnapshot::query()
                ->where('routing_version_id', $order->routing_version_id)
                ->first();

            if (! $routingVersionSnapshot) {
                throw new DomainException('Routing version has not been approved/snapshotted. Cannot freeze production snapshot.', 422);
            }

            $routingOperations = RoutingOperationSnapshot::query()
                ->where('routing_version_snapshot_id', $routingVersionSnapshot->id)
                ->orderBy('sequence')
                ->get();
        }

        $snapshot = $this->inTransaction(function () use ($order, $bomSnapshot, $routingVersionSnapshot, $routingOperations, $frozenBy) {
            $hashPayload = [
                'production_order_id' => $order->id,
                'product_id' => $order->product_id,
                'quantity_planned' => (float) $order->quantity_planned,
                'quantity_scrapped_target' => (float) $order->quantity_scrapped,
                'bom_snapshot_id' => $bomSnapshot->id,
                'bom_header_id' => $bomSnapshot->source_bom_header_id,
                'bom_version_number' => $bomSnapshot->source_bom_version_number,
                'routing_version_snapshot_id' => $routingVersionSnapshot?->id,
                'routing_version_id' => $routingVersionSnapshot?->routing_version_id,
                'routing_version_number' => $routingVersionSnapshot?->version_number,
            ];

            $snapshot = ProductionOrderSnapshot::query()->create([
                'company_id' => $order->company_id,
                'production_order_id' => $order->id,
                'product_id' => $order->product_id,
                'bom_snapshot_id' => $bomSnapshot->id,
                'bom_header_id' => $bomSnapshot->source_bom_header_id,
                'bom_version_number' => $bomSnapshot->source_bom_version_number,
                'routing_version_snapshot_id' => $routingVersionSnapshot?->id,
                'routing_version_id' => $routingVersionSnapshot?->routing_version_id,
                'routing_version_number' => $routingVersionSnapshot?->version_number,
                'quantity_planned' => round((float) $order->quantity_planned, 6),
                'quantity_scrapped_target' => round((float) $order->quantity_scrapped, 6),
                'snapshot_hash' => hash('sha256', json_encode($hashPayload, JSON_THROW_ON_ERROR)),
                'frozen_at' => now(),
                'frozen_by' => $frozenBy,
            ]);

            if ($routingOperations->isNotEmpty()) {
                $operationRows = $routingOperations->map(static function (RoutingOperationSnapshot $op) use ($snapshot): array {
                    return [
                        'company_id' => $snapshot->company_id,
                        'production_order_snapshot_id' => $snapshot->id,
                        'routing_version_id' => $op->routing_version_id,
                        'standard_time_id' => $op->standard_time_id,
                        'standard_time_version' => $op->standard_time_version,
                        'work_center_id' => $op->work_center_id,
                        'operation_no' => $op->operation_no,
                        'operation_code' => $op->operation_code,
                        'operation_name' => $op->operation_name,
                        'sequence' => $op->sequence,
                        'setup_time_minutes' => $op->setup_time_minutes,
                        'runtime_minutes' => $op->runtime_minutes,
                        'queue_time_minutes' => $op->queue_time_minutes,
                        'move_time_minutes' => $op->move_time_minutes,
                        'is_outsourced' => $op->is_outsourced,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                })->all();

                ProductionOrderRoutingOperationSnapshot::query()->insert($operationRows);
            }

            return $snapshot;
        });

        $this->logger->info('production_order_snapshot.frozen', [
            'production_order_id' => $productionOrderId,
            'snapshot_id' => $snapshot->id,
            'bom_snapshot_id' => $bomSnapshot->id,
            'routing_version_snapshot_id' => $routingVersionSnapshot?->id,
            'routing_operations_copied' => $routingOperations->count(),
        ]);

        return $this->serializeSnapshot($snapshot->load(['product', 'bomSnapshot', 'routingOperations']));
    }

    public function getSnapshot(int $productionOrderId): array
    {
        $order = ProductionOrder::query()->findOrFail($productionOrderId);

        $snapshot = ProductionOrderSnapshot::query()
            ->where('company_id', $order->company_id)
            ->where('production_order_id', $order->id)
            ->with(['product', 'bomSnapshot.items', 'routingOperations.workCenter'])
            ->firstOrFail();

        return $this->serializeSnapshot($snapshot);
    }

    private function serializeSnapshot(ProductionOrderSnapshot $snapshot): array
    {
        return [
            'id' => (int) $snapshot->id,
            'production_order_id' => (int) $snapshot->production_order_id,
            'product_id' => (int) $snapshot->product_id,
            'bom_snapshot_id' => $snapshot->bom_snapshot_id ? (int) $snapshot->bom_snapshot_id : null,
            'bom_header_id' => $snapshot->bom_header_id ? (int) $snapshot->bom_header_id : null,
            'bom_version_number' => $snapshot->bom_version_number ? (int) $snapshot->bom_version_number : null,
            'routing_version_snapshot_id' => $snapshot->routing_version_snapshot_id ? (int) $snapshot->routing_version_snapshot_id : null,
            'routing_version_id' => $snapshot->routing_version_id ? (int) $snapshot->routing_version_id : null,
            'routing_version_number' => $snapshot->routing_version_number ? (int) $snapshot->routing_version_number : null,
            'quantity_planned' => (float) $snapshot->quantity_planned,
            'quantity_scrapped_target' => (float) $snapshot->quantity_scrapped_target,
            'snapshot_hash' => $snapshot->snapshot_hash,
            'frozen_at' => $snapshot->frozen_at?->toDateTimeString(),
            'frozen_by' => $snapshot->frozen_by,
            'routing_operations' => $snapshot->routingOperations->map(static fn (ProductionOrderRoutingOperationSnapshot $op): array => [
                'sequence' => $op->sequence,
                'operation_no' => $op->operation_no,
                'operation_code' => $op->operation_code,
                'operation_name' => $op->operation_name,
                'work_center_id' => $op->work_center_id,
                'standard_time_id' => $op->standard_time_id,
                'standard_time_version' => $op->standard_time_version,
                'setup_time_minutes' => $op->setup_time_minutes,
                'runtime_minutes' => $op->runtime_minutes,
                'queue_time_minutes' => $op->queue_time_minutes,
                'move_time_minutes' => $op->move_time_minutes,
                'is_outsourced' => $op->is_outsourced,
            ])->values()->all(),
        ];
    }
}
