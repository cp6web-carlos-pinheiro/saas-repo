<?php

declare(strict_types=1);

namespace App\Modules\Routing\Application\Services;

use App\Modules\Routing\Infrastructure\Persistence\Models\RoutingOperation;
use App\Modules\Routing\Infrastructure\Persistence\Models\RoutingOperationStandardTime;
use App\Modules\Routing\Infrastructure\Persistence\Models\RoutingOperationSnapshot;
use App\Modules\Routing\Infrastructure\Persistence\Models\RoutingVersionSnapshot;
use App\Modules\Routing\Infrastructure\Persistence\Models\RoutingVersion;
use App\Modules\Scheduling\Infrastructure\Persistence\Models\WorkCenter;
use App\Shared\Application\Cache\CacheManager;
use App\Shared\Application\Services\BaseService;
use App\Shared\Application\Transactions\TransactionManager;
use App\Shared\Infrastructure\Logging\AppLogger;
use App\Shared\Presentation\Exceptions\DomainException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class RoutingService extends BaseService
{
    private const VALID_STATUS = ['DRAFT', 'APPROVED', 'OBSOLETE'];

    public function __construct(
        TransactionManager $transaction,
        CacheManager $cache,
        AppLogger $logger
    ) {
        parent::__construct($transaction, $cache, $logger);
    }

    public function paginateVersions(int $perPage = 15): LengthAwarePaginator
    {
        return RoutingVersion::query()
            ->withCount('operations')
            ->orderByDesc('id')
            ->paginate($perPage);
    }

    public function createVersion(array $payload): array
    {
        $this->assertDraftStatus($payload['status']);

        $entity = $this->inTransaction(function () use ($payload) {
            return RoutingVersion::query()->create([
                'product_id' => $payload['product_id'],
                'version_number' => $payload['version_number'],
                'status' => 'DRAFT',
                'effective_from' => $payload['effective_from'] ?? null,
                'effective_to' => $payload['effective_to'] ?? null,
                'description' => $payload['description'] ?? null,
            ]);
        });

        return $entity->toArray();
    }

    public function showVersion(int $id): array
    {
        $entity = RoutingVersion::query()
            ->with(['operations.workCenter', 'snapshot.operations.workCenter'])
            ->findOrFail($id);

        return $entity->toArray();
    }

    public function addOperation(int $routingVersionId, array $payload): array
    {
        $routingVersion = RoutingVersion::query()->findOrFail($routingVersionId);
        $this->assertDraftVersion($routingVersion);

        WorkCenter::query()->findOrFail((int) $payload['work_center_id']);

        $operation = $this->inTransaction(function () use ($routingVersion, $payload) {
            return RoutingOperation::query()->create([
                'routing_version_id' => $routingVersion->id,
                'work_center_id' => $payload['work_center_id'],
                'operation_no' => $payload['operation_no'],
                'operation_code' => $payload['operation_code'],
                'operation_name' => $payload['operation_name'],
                'sequence' => $payload['sequence'],
                'setup_time_minutes' => $payload['setup_time_minutes'] ?? 0,
                'runtime_minutes' => $payload['runtime_minutes'] ?? 0,
                'queue_time_minutes' => $payload['queue_time_minutes'] ?? 0,
                'move_time_minutes' => $payload['move_time_minutes'] ?? 0,
                'is_outsourced' => $payload['is_outsourced'] ?? false,
            ]);
        });

        return $operation->toArray();
    }

    public function updateOperation(int $routingVersionId, int $operationId, array $payload): array
    {
        $routingVersion = RoutingVersion::query()->findOrFail($routingVersionId);
        $this->assertDraftVersion($routingVersion);
        WorkCenter::query()->findOrFail((int) $payload['work_center_id']);

        $operation = $this->inTransaction(function () use ($routingVersionId, $operationId, $payload) {
            $entity = RoutingOperation::query()
                ->where('routing_version_id', $routingVersionId)
                ->findOrFail($operationId);

            $entity->fill($payload);
            $entity->save();

            return $entity;
        });

        return $operation->toArray();
    }

    public function deleteOperation(int $routingVersionId, int $operationId): void
    {
        $routingVersion = RoutingVersion::query()->findOrFail($routingVersionId);
        $this->assertDraftVersion($routingVersion);

        $this->inTransaction(function () use ($routingVersionId, $operationId): void {
            $entity = RoutingOperation::query()
                ->where('routing_version_id', $routingVersionId)
                ->findOrFail($operationId);

            $entity->delete();
        });
    }

    public function approveVersion(int $routingVersionId, array $payload, ?int $userId = null): array
    {
        $routingVersion = RoutingVersion::query()
            ->with(['operations.workCenter'])
            ->findOrFail($routingVersionId);

        $this->assertDraftVersion($routingVersion);

        $effectiveFrom = $payload['effective_from'] ?? $routingVersion->effective_from?->format('Y-m-d');

        if (! $effectiveFrom) {
            throw new DomainException('effective_from is required for approval', 422);
        }

        $effectiveTo = $payload['effective_to'] ?? $routingVersion->effective_to?->format('Y-m-d');

        if ($effectiveTo && $effectiveTo < $effectiveFrom) {
            throw new DomainException('effective_to must be greater or equal to effective_from', 422);
        }

        $hasOverlap = RoutingVersion::query()
            ->where('product_id', $routingVersion->product_id)
            ->where('status', 'APPROVED')
            ->where('id', '!=', $routingVersion->id)
            ->whereDate('effective_from', '<=', $effectiveTo ?? $effectiveFrom)
            ->where(static function ($query) use ($effectiveFrom): void {
                $query->whereNull('effective_to')
                    ->orWhereDate('effective_to', '>=', $effectiveFrom);
            })
            ->exists();

        if ($hasOverlap) {
            throw new DomainException('Approved routing versions cannot overlap in effective dating', 422);
        }

        $standardTimesByOperation = RoutingOperationStandardTime::query()
            ->whereIn('routing_operation_id', $routingVersion->operations->pluck('id'))
            ->where('status', 'APPROVED')
            ->whereDate('effective_from', '<=', $effectiveFrom)
            ->where(static function ($query) use ($effectiveFrom): void {
                $query->whereNull('effective_to')->orWhereDate('effective_to', '>=', $effectiveFrom);
            })
            ->orderByDesc('effective_from')
            ->get()
            ->unique('routing_operation_id')
            ->keyBy('routing_operation_id');

        $approvedAt = now();

        $snapshot = $this->inTransaction(function () use ($routingVersion, $payload, $userId, $effectiveFrom, $effectiveTo, $approvedAt, $standardTimesByOperation) {
            $updated = RoutingVersion::query()->whereKey($routingVersion->id)->firstOrFail();
            $updated->fill([
                'status' => 'APPROVED',
                'effective_from' => $effectiveFrom,
                'effective_to' => $effectiveTo,
                'approved_by' => $userId,
                'approved_at' => $approvedAt,
            ]);
            $updated->save();

            $snapshotPayload = [
                'routing_version_id' => $updated->id,
                'product_id' => $updated->product_id,
                'version_number' => $updated->version_number,
                'status' => $updated->status,
                'effective_from' => $effectiveFrom,
                'effective_to' => $effectiveTo,
                'description' => $updated->description,
                'approved_by' => $userId,
                'approved_at' => $approvedAt->toDateTimeString(),
                'operations' => $updated->operations->map(static function (RoutingOperation $operation) use ($standardTimesByOperation): array {
                    $standard = $standardTimesByOperation->get($operation->id);
                    $setupTime = $operation->is_outsourced
                        ? 0
                        : ($standard?->setup_scope === 'ROUTING' && (int) $operation->sequence !== 1
                            ? 0
                            : ($standard?->setup_time_minutes ?? $operation->setup_time_minutes));
                    $runtimeTime = $operation->is_outsourced ? 0 : ($standard?->runtime_minutes ?? $operation->runtime_minutes);
                    $queueTime = $operation->is_outsourced ? 0 : ($standard?->queue_time_minutes ?? $operation->queue_time_minutes);
                    $moveTime = $operation->is_outsourced ? 0 : ($standard?->move_time_minutes ?? $operation->move_time_minutes);

                    return [
                        'routing_version_id' => $operation->routing_version_id,
                        'standard_time_id' => $standard?->id,
                        'standard_time_version' => $standard?->version_number,
                        'work_center_id' => $operation->work_center_id,
                        'operation_no' => $operation->operation_no,
                        'operation_code' => $operation->operation_code,
                        'operation_name' => $operation->operation_name,
                        'sequence' => $operation->sequence,
                        'setup_time_minutes' => $setupTime,
                        'runtime_minutes' => $runtimeTime,
                        'queue_time_minutes' => $queueTime,
                        'move_time_minutes' => $moveTime,
                        'is_outsourced' => $operation->is_outsourced,
                    ];
                })->values()->all(),
            ];

            $snapshot = RoutingVersionSnapshot::query()->create([
                'company_id' => $updated->company_id,
                'routing_version_id' => $updated->id,
                'product_id' => $updated->product_id,
                'version_number' => $updated->version_number,
                'status' => $updated->status,
                'effective_from' => $effectiveFrom,
                'effective_to' => $effectiveTo,
                'description' => $updated->description,
                'approved_by' => $userId,
                'approved_at' => $approvedAt,
                'frozen_at' => $approvedAt,
                'snapshot_hash' => hash('sha256', json_encode($snapshotPayload, JSON_THROW_ON_ERROR)),
                'created_by' => $userId,
            ]);

            $operationRows = $updated->operations->map(static function (RoutingOperation $operation) use ($snapshot, $standardTimesByOperation): array {
                $standard = $standardTimesByOperation->get($operation->id);
                $setupTime = $operation->is_outsourced
                    ? 0
                    : ($standard?->setup_scope === 'ROUTING' && (int) $operation->sequence !== 1
                        ? 0
                        : ($standard?->setup_time_minutes ?? $operation->setup_time_minutes));
                $runtimeTime = $operation->is_outsourced ? 0 : ($standard?->runtime_minutes ?? $operation->runtime_minutes);
                $queueTime = $operation->is_outsourced ? 0 : ($standard?->queue_time_minutes ?? $operation->queue_time_minutes);
                $moveTime = $operation->is_outsourced ? 0 : ($standard?->move_time_minutes ?? $operation->move_time_minutes);

                return [
                    'company_id' => $operation->company_id,
                    'routing_version_snapshot_id' => $snapshot->id,
                    'routing_version_id' => $operation->routing_version_id,
                    'standard_time_id' => $standard?->id,
                    'standard_time_version' => $standard?->version_number,
                    'work_center_id' => $operation->work_center_id,
                    'operation_no' => $operation->operation_no,
                    'operation_code' => $operation->operation_code,
                    'operation_name' => $operation->operation_name,
                    'sequence' => $operation->sequence,
                    'setup_time_minutes' => $setupTime,
                    'runtime_minutes' => $runtimeTime,
                    'queue_time_minutes' => $queueTime,
                    'move_time_minutes' => $moveTime,
                    'is_outsourced' => $operation->is_outsourced,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            })->all();

            if ($operationRows !== []) {
                RoutingOperationSnapshot::query()->insert($operationRows);
            }

            return $snapshot;
        });

        $this->logger->info('routing_version.approved', [
            'routing_version_id' => $routingVersionId,
            'snapshot_id' => $snapshot->id,
            'product_id' => $routingVersion->product_id,
            'version_number' => $routingVersion->version_number,
        ]);

        return [
            'routing_version_id' => $routingVersionId,
            'snapshot_id' => (int) $snapshot->id,
            'status' => 'APPROVED',
            'effective_from' => $snapshot->effective_from?->toDateString(),
            'effective_to' => $snapshot->effective_to?->toDateString(),
            'operations_count' => $snapshot->operations()->count(),
        ];
    }

    public function markObsolete(int $routingVersionId): array
    {
        $routingVersion = RoutingVersion::query()->findOrFail($routingVersionId);

        if ($routingVersion->status !== 'APPROVED') {
            throw new DomainException('Only approved routing versions can be marked obsolete', 422);
        }

        $obsolete = $this->inTransaction(function () use ($routingVersion) {
            $routingVersion->status = 'OBSOLETE';
            $routingVersion->effective_to = now()->toDateString();
            $routingVersion->save();

            return $routingVersion;
        });

        $this->logger->info('routing_version.obsolete', [
            'routing_version_id' => $routingVersionId,
        ]);

        return $obsolete->toArray();
    }

    private function assertDraftStatus(string $status): void
    {
        if ($status !== 'DRAFT') {
            throw new DomainException('Routing versions must be created as DRAFT', 422, [
                'status' => ['DRAFT'],
            ]);
        }
    }

    private function assertDraftVersion(RoutingVersion $routingVersion): void
    {
        if ($routingVersion->status !== 'DRAFT') {
            throw new DomainException('Only draft routing versions can be modified', 422);
        }
    }
}
