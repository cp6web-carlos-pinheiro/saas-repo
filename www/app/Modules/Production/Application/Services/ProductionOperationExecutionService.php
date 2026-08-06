<?php

declare(strict_types=1);

namespace App\Modules\Production\Application\Services;

use App\Modules\Production\Infrastructure\Persistence\Models\ProductionOperationEvent;
use App\Modules\Production\Infrastructure\Persistence\Models\ProductionOperationOutput;
use App\Modules\Production\Infrastructure\Persistence\Models\ProductionOrderOperation;
use App\Modules\Scheduling\Infrastructure\Persistence\Models\ProductionResource;
use App\Shared\Application\Cache\CacheManager;
use App\Shared\Application\Services\BaseService;
use App\Shared\Application\Transactions\TransactionManager;
use App\Shared\Infrastructure\Logging\AppLogger;
use App\Shared\Presentation\Exceptions\DomainException;
use Carbon\Carbon;

final class ProductionOperationExecutionService extends BaseService
{
    public function __construct(TransactionManager $transaction, CacheManager $cache, AppLogger $logger)
    { parent::__construct($transaction, $cache, $logger); }

    public function show(int $operationId): array
    { return ProductionOrderOperation::query()->with(['events','outputs','qualityRecords'])->findOrFail($operationId)->toArray(); }

    public function command(int $operationId, string $eventType, array $payload, ?int $userId = null): array
    {
        $operation = ProductionOrderOperation::query()->findOrFail($operationId);
        $eventType = strtoupper($eventType);
        $key = (string) ($payload['idempotency_key'] ?? '');
        if ($key === '') throw new DomainException('idempotency_key is required for MES commands', 422);
        $existing = ProductionOperationEvent::query()->where('idempotency_key', $key)->first();
        if ($existing) return $this->show($operationId);
        if (in_array($operation->status, ['COMPLETED','CANCELLED'], true)) throw new DomainException('Operation is already closed', 422);
        if (in_array($eventType, ['PAUSE','STOP','CANCEL'], true) && empty($payload['reason_code'])) throw new DomainException('reason_code is required for this command', 422);
        $this->validateTransition($operation, $eventType);
        $resourceId = isset($payload['production_resource_id']) ? (int) $payload['production_resource_id'] : ($operation->actual_production_resource_id ?: $operation->production_resource_id);
        if ($resourceId) {
            $resource = ProductionResource::query()->findOrFail($resourceId);
            if ((int) $resource->work_center_id !== (int) $operation->work_center_id || $resource->status !== 'ACTIVE') throw new DomainException('Selected resource is not active or does not belong to the operation work center', 422);
            if ($eventType === 'START') {
                $busy = ProductionOrderOperation::query()->where('id', '!=', $operation->id)->where('actual_production_resource_id', $resourceId)->where('status', 'IN_PROGRESS')->exists();
                if ($busy) throw new DomainException('Selected resource already has an active operation', 409);
            }
        }
        $occurredAt = isset($payload['occurred_at']) ? Carbon::parse($payload['occurred_at']) : now();
        $this->inTransaction(function () use ($operation, $eventType, $payload, $userId, $key, $occurredAt, $resourceId): void {
            $event = ProductionOperationEvent::query()->create([
                'company_id' => $operation->company_id, 'production_order_operation_id' => $operation->id, 'event_type' => $eventType,
                'idempotency_key' => $key, 'occurred_at' => $occurredAt, 'operator_id' => $payload['operator_id'] ?? $userId,
                'production_resource_id' => $resourceId, 'reason_code' => $payload['reason_code'] ?? null, 'notes' => $payload['notes'] ?? null, 'metadata' => $payload['metadata'] ?? null,
            ]);
            $status = match ($eventType) { 'START','RESUME' => 'IN_PROGRESS', 'PAUSE' => 'PAUSED', 'STOP' => 'STOPPED', 'COMPLETE' => 'COMPLETED', 'CANCEL' => 'CANCELLED', default => $operation->status };
            $operation->status = $status;
            $operation->actual_production_resource_id = $resourceId;
            $operation->operator_id = $payload['operator_id'] ?? $userId;
            if ($eventType === 'START') $operation->actual_started_at ??= $occurredAt;
            if (in_array($eventType, ['COMPLETE','CANCEL'], true)) $operation->actual_completed_at = $occurredAt;
            $totals = $this->calculateDurations($operation->id);
            $operation->actual_productive_minutes = $totals['productive_minutes'];
            $operation->actual_pause_minutes = $totals['pause_minutes'];
            $operation->save();
        });
        return $this->show($operationId);
    }

    public function reportOutput(int $operationId, array $payload, ?int $userId = null): array
    {
        $operation = ProductionOrderOperation::query()->findOrFail($operationId);
        if (in_array($operation->status, ['PLANNED','CANCELLED'], true)) throw new DomainException('Operation is not executable', 422);
        $good = round((float) ($payload['quantity_good'] ?? 0), 6); $scrap = round((float) ($payload['quantity_scrapped'] ?? 0), 6); $rework = round((float) ($payload['quantity_rework'] ?? 0), 6);
        if ($good + $scrap + $rework <= 0) throw new DomainException('At least one output quantity is required', 422);
        $current = (float) $operation->quantity_processed;
        if ($current + $good + $scrap + $rework > (float) $operation->quantity_planned + 0.000001 && empty($payload['allow_excess'])) throw new DomainException('Reported quantity exceeds planned quantity', 422);
        $output = $this->inTransaction(function () use ($operation, $payload, $userId, $good, $scrap, $rework) {
            $row = ProductionOperationOutput::query()->create(['company_id'=>$operation->company_id,'production_order_operation_id'=>$operation->id,'quantity_good'=>$good,'quantity_scrapped'=>$scrap,'quantity_rework'=>$rework,'lot_number'=>$payload['lot_number'] ?? null,'inspection_status'=>$payload['inspection_status'] ?? 'PENDING','scrap_cause_code'=>$payload['scrap_cause_code'] ?? null,'destination'=>$payload['destination'] ?? null,'operator_id'=>$payload['operator_id'] ?? $userId,'production_resource_id'=>$operation->actual_production_resource_id ?: $operation->production_resource_id,'reported_at'=>now(),'notes'=>$payload['notes'] ?? null,'metadata'=>$payload['metadata'] ?? null]);
            $operation->quantity_processed = round((float)$operation->quantity_processed + $good + $scrap + $rework, 6);
            $operation->quantity_good = round((float)$operation->quantity_good + $good, 6);
            $operation->quantity_scrapped = round((float)$operation->quantity_scrapped + $scrap, 6);
            $operation->quantity_rework = round((float)$operation->quantity_rework + $rework, 6);
            $operation->save();
            return $row;
        });
        return $output->toArray();
    }

    private function validateTransition(ProductionOrderOperation $operation, string $eventType): void
    {
        $valid = match ($eventType) {
            'START' => in_array($operation->status, ['PLANNED','READY','STOPPED'], true),
            'PAUSE' => $operation->status === 'IN_PROGRESS',
            'RESUME' => $operation->status === 'PAUSED',
            'STOP' => in_array($operation->status, ['IN_PROGRESS','PAUSED'], true),
            'COMPLETE' => in_array($operation->status, ['IN_PROGRESS','PAUSED','STOPPED'], true),
            'CANCEL' => ! in_array($operation->status, ['COMPLETED','CANCELLED'], true),
            default => false,
        };
        if (! $valid) throw new DomainException(sprintf('Invalid MES transition %s from status %s', $eventType, $operation->status), 422);
        if (in_array($eventType, ['START','RESUME'], true) && (int) $operation->sequence > 1) {
            $pending = ProductionOrderOperation::query()->where('production_order_id', $operation->production_order_id)->where('sequence', '<', $operation->sequence)->whereNotIn('status', ['COMPLETED','OUTSOURCED'])->exists();
            if ($pending) throw new DomainException('Previous operation must be completed before starting this operation', 422);
        }
    }

    private function calculateDurations(int $operationId): array
    {
        $events = ProductionOperationEvent::query()->where('production_order_operation_id', $operationId)->orderBy('occurred_at')->get(); $productive = 0.0; $pause = 0.0; $activeSince = null; $pauseSince = null;
        foreach ($events as $event) { if ($event->event_type === 'START') { $activeSince = $event->occurred_at; } elseif ($event->event_type === 'PAUSE' && $activeSince) { $productive += $activeSince->diffInSeconds($event->occurred_at) / 60; $activeSince = null; $pauseSince = $event->occurred_at; } elseif ($event->event_type === 'RESUME') { if ($pauseSince) $pause += $pauseSince->diffInSeconds($event->occurred_at) / 60; $pauseSince = null; $activeSince = $event->occurred_at; } elseif (in_array($event->event_type, ['STOP','COMPLETE','CANCEL'], true)) { if ($activeSince) $productive += $activeSince->diffInSeconds($event->occurred_at) / 60; if ($pauseSince) $pause += $pauseSince->diffInSeconds($event->occurred_at) / 60; $activeSince = null; $pauseSince = null; } }
        $last = $events->last(); if ($activeSince && $last && in_array($last->event_type, ['STOP','COMPLETE','CANCEL'], true)) $productive += $activeSince->diffInSeconds($last->occurred_at) / 60;
        return ['productive_minutes' => round(max(0, $productive), 2), 'pause_minutes' => round(max(0, $pause), 2)];
    }
}
