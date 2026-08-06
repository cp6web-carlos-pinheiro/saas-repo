<?php

declare(strict_types=1);

namespace App\Modules\Production\Application\Services;

use App\Modules\Production\Infrastructure\Persistence\Models\ProductionOrder;
use App\Modules\Production\Infrastructure\Persistence\Models\ProductionOrderOperation;
use App\Modules\Routing\Infrastructure\Persistence\Models\RoutingOperationStandardTime;
use App\Shared\Application\Cache\CacheManager;
use App\Shared\Application\Services\BaseService;
use App\Shared\Application\Transactions\TransactionManager;
use App\Shared\Infrastructure\Logging\AppLogger;
use App\Shared\Presentation\Exceptions\DomainException;

final class ProductionOrderOperationPlanningService extends BaseService
{
    public function __construct(TransactionManager $transaction, CacheManager $cache, AppLogger $logger)
    {
        parent::__construct($transaction, $cache, $logger);
    }

    public function materialize(int $productionOrderId, bool $force = false, ?int $userId = null): array
    {
        $order = ProductionOrder::query()->with('snapshot.routingOperations')->findOrFail($productionOrderId);
        $existing = ProductionOrderOperation::query()->where('production_order_id', $order->id)->orderBy('sequence')->get();

        if ($existing->isNotEmpty() && ! $force) {
            return $existing->toArray();
        }
        if ($force && in_array($order->status, ['COMPLETED', 'CANCELLED'], true)) {
            throw new DomainException('Cannot replan a closed production order', 422);
        }
        if (! $order->snapshot) {
            throw new DomainException('Production order snapshot is required before planning operations', 422);
        }

        $rows = $this->inTransaction(function () use ($order, $force, $userId): array {
            if ($force) {
                ProductionOrderOperation::query()->where('production_order_id', $order->id)->delete();
            }

            $quantity = (float) $order->quantity_planned;
            $operations = $order->snapshot->routingOperations->sortBy('sequence')->values();

            return $operations->map(function ($operation) use ($order, $quantity): array {
                $standard = $operation->standard_time_id
                    ? RoutingOperationStandardTime::query()->find($operation->standard_time_id)
                    : null;
                $basis = (string) ($standard?->time_basis ?? 'PER_PROCESS');
                $setupScope = (string) ($standard?->setup_scope ?? 'ROUTING');
                $outsourced = (bool) $operation->is_outsourced;
                $setup = $outsourced || ($setupScope === 'ROUTING' && (int) $operation->sequence !== 1) ? 0.0 : (float) ($standard?->setup_time_minutes ?? $operation->setup_time_minutes);
                $runtime = $outsourced ? 0.0 : (float) ($standard?->runtime_minutes ?? $operation->runtime_minutes);
                if ($basis === 'PER_UNIT') {
                    $runtime *= $quantity;
                }
                $queue = $outsourced ? 0.0 : (float) ($standard?->queue_time_minutes ?? $operation->queue_time_minutes);
                $move = $outsourced ? 0.0 : (float) ($standard?->move_time_minutes ?? $operation->move_time_minutes);
                $productive = $this->roundMinutes($setup + $runtime);
                $lead = $this->roundMinutes($queue + $move);

                return [
                    'company_id' => $order->company_id,
                    'production_order_id' => $order->id,
                    'production_order_routing_operation_snapshot_id' => $operation->id,
                    'routing_operation_id' => $operation->routing_version_id,
                    'standard_time_id' => $operation->standard_time_id,
                    'standard_time_version' => $operation->standard_time_version,
                    'operation_no' => $operation->operation_no,
                    'operation_code' => $operation->operation_code,
                    'operation_name' => $operation->operation_name,
                    'sequence' => $operation->sequence,
                    'work_center_id' => $operation->work_center_id,
                    'status' => $outsourced ? 'OUTSOURCED' : 'PLANNED',
                    'quantity_planned' => $quantity,
                    'setup_scope' => $setupScope,
                    'setup_time_minutes' => $setup,
                    'runtime_time_minutes' => $this->roundMinutes($runtime),
                    'queue_time_minutes' => $queue,
                    'move_time_minutes' => $move,
                    'productive_time_minutes' => $productive,
                    'lead_time_minutes' => $lead,
                    'total_time_minutes' => $this->roundMinutes($productive + $lead),
                    'calculation_metadata' => [
                        'time_basis' => $basis,
                        'efficiency_applied_to_time' => false,
                        'queue_and_move_are_lead_time_only' => true,
                        'outsourced_excluded_from_capacity' => $outsourced,
                        'planned_by' => $userId,
                    ],
                ];
            })->all();
        });

        foreach ($rows as $row) {
            ProductionOrderOperation::query()->create($row);
        }

        return ProductionOrderOperation::query()->where('production_order_id', $order->id)->orderBy('sequence')->get()->toArray();
    }

    private function roundMinutes(float $minutes): float
    {
        return round(max(0.0, $minutes) / 5) * 5.0;
    }
}
