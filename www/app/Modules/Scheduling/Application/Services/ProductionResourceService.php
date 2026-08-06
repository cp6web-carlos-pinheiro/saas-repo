<?php

declare(strict_types=1);

namespace App\Modules\Scheduling\Application\Services;

use App\Modules\Scheduling\Infrastructure\Persistence\Models\ProductionResource;
use App\Modules\Scheduling\Infrastructure\Persistence\Models\WorkCenter;
use App\Modules\Scheduling\Infrastructure\Persistence\Models\WorkCenterHourRate;
use App\Shared\Application\Cache\CacheManager;
use App\Shared\Application\Services\BaseService;
use App\Shared\Application\Transactions\TransactionManager;
use App\Shared\Infrastructure\Logging\AppLogger;
use App\Shared\Presentation\Exceptions\DomainException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

final class ProductionResourceService extends BaseService
{
    private const RESOURCE_TYPES = ['MACHINE', 'EQUIPMENT', 'TOOL', 'LINE', 'OUTSOURCED'];
    private const STATUSES = ['ACTIVE', 'INACTIVE', 'MAINTENANCE', 'BLOCKED', 'DECOMMISSIONED'];

    public function __construct(TransactionManager $transaction, CacheManager $cache, AppLogger $logger)
    {
        parent::__construct($transaction, $cache, $logger);
    }

    public function paginateResources(int $perPage = 15): LengthAwarePaginator
    {
        return ProductionResource::query()
            ->with(['plant:id,code,name', 'workCenter:id,code,name'])
            ->orderBy('code')
            ->paginate($perPage);
    }

    public function showResource(int $id): array
    {
        return ProductionResource::query()
            ->with(['plant:id,code,name', 'workCenter:id,code,name'])
            ->findOrFail($id)
            ->toArray();
    }

    public function createResource(array $payload): array
    {
        $this->assertResourcePayload($payload);

        $resource = $this->inTransaction(function () use ($payload): ProductionResource {
            return ProductionResource::query()->create([
                'plant_id' => $payload['plant_id'],
                'work_center_id' => $payload['work_center_id'],
                'code' => $payload['code'],
                'name' => $payload['name'],
                'resource_type' => strtoupper((string) $payload['resource_type']),
                'status' => strtoupper((string) ($payload['status'] ?? 'ACTIVE')),
                'capacity_per_day' => $payload['capacity_per_day'] ?? null,
                'efficiency_factor' => $payload['efficiency_factor'] ?? null,
                'effective_from' => $payload['effective_from'] ?? null,
                'effective_to' => $payload['effective_to'] ?? null,
                'metadata' => $payload['metadata'] ?? null,
            ]);
        });

        return $resource->refresh()->load(['plant:id,code,name', 'workCenter:id,code,name'])->toArray();
    }

    public function updateResource(int $id, array $payload): array
    {
        $this->assertResourcePayload($payload);

        $resource = $this->inTransaction(function () use ($id, $payload): ProductionResource {
            $resource = ProductionResource::query()->findOrFail($id);
            $resource->fill([
                'plant_id' => $payload['plant_id'],
                'work_center_id' => $payload['work_center_id'],
                'code' => $payload['code'],
                'name' => $payload['name'],
                'resource_type' => strtoupper((string) $payload['resource_type']),
                'status' => strtoupper((string) ($payload['status'] ?? $resource->status)),
                'capacity_per_day' => $payload['capacity_per_day'] ?? null,
                'efficiency_factor' => $payload['efficiency_factor'] ?? null,
                'effective_from' => $payload['effective_from'] ?? null,
                'effective_to' => $payload['effective_to'] ?? null,
                'metadata' => $payload['metadata'] ?? $resource->metadata,
            ]);
            $resource->save();

            return $resource;
        });

        return $resource->refresh()->load(['plant:id,code,name', 'workCenter:id,code,name'])->toArray();
    }

    public function deleteResource(int $id): void
    {
        $this->inTransaction(function () use ($id): void {
            $resource = ProductionResource::query()->findOrFail($id);
            $resource->status = 'DECOMMISSIONED';
            $resource->save();
        });
    }

    public function paginateRates(int $workCenterId, int $perPage = 15): LengthAwarePaginator
    {
        WorkCenter::query()->findOrFail($workCenterId);

        return WorkCenterHourRate::query()
            ->where('work_center_id', $workCenterId)
            ->orderByDesc('effective_from')
            ->paginate($perPage);
    }

    public function createRate(int $workCenterId, array $payload, ?int $approvedBy = null): array
    {
        WorkCenter::query()->findOrFail($workCenterId);
        $this->assertRatePayload($payload);

        $this->assertNoRateOverlap($workCenterId, $payload['effective_from'], $payload['effective_to']);

        $rate = $this->inTransaction(function () use ($workCenterId, $payload, $approvedBy): WorkCenterHourRate {
            return WorkCenterHourRate::query()->create([
                'work_center_id' => $workCenterId,
                'hourly_rate' => $payload['hourly_rate'],
                'currency' => strtoupper((string) ($payload['currency'] ?? 'BRL')),
                'effective_from' => $payload['effective_from'],
                'effective_to' => $payload['effective_to'] ?? null,
                'status' => 'ACTIVE',
                'approved_by' => $approvedBy,
                'approved_at' => $approvedBy !== null ? now() : null,
                'change_reason' => $payload['change_reason'] ?? null,
                'metadata' => $payload['metadata'] ?? null,
            ]);
        });

        return $rate->refresh()->toArray();
    }

    public function effectiveRate(int $workCenterId, string $referenceDate): ?array
    {
        WorkCenter::query()->findOrFail($workCenterId);

        return WorkCenterHourRate::query()
            ->where('work_center_id', $workCenterId)
            ->where('status', 'ACTIVE')
            ->whereDate('effective_from', '<=', $referenceDate)
            ->where(static function (Builder $query) use ($referenceDate): void {
                $query->whereNull('effective_to')->orWhereDate('effective_to', '>=', $referenceDate);
            })
            ->orderByDesc('effective_from')
            ->first()?->toArray();
    }

    private function assertResourcePayload(array $payload): void
    {
        if (! in_array(strtoupper((string) $payload['resource_type']), self::RESOURCE_TYPES, true)) {
            throw new DomainException('Invalid production resource type', 422, ['resource_type' => self::RESOURCE_TYPES]);
        }

        if (! in_array(strtoupper((string) ($payload['status'] ?? 'ACTIVE')), self::STATUSES, true)) {
            throw new DomainException('Invalid production resource status', 422, ['status' => self::STATUSES]);
        }

        if ((isset($payload['effective_from'], $payload['effective_to'])) && $payload['effective_to'] < $payload['effective_from']) {
            throw new DomainException('effective_to must be greater or equal to effective_from', 422);
        }

        $workCenter = WorkCenter::query()->findOrFail((int) $payload['work_center_id']);

        if ((int) $workCenter->plant_id !== (int) $payload['plant_id']) {
            throw new DomainException('Production resource plant must match work center plant', 422);
        }
    }

    private function assertRatePayload(array $payload): void
    {
        if ((float) $payload['hourly_rate'] < 0) {
            throw new DomainException('hourly_rate must be greater or equal to zero', 422);
        }

        if (isset($payload['effective_to']) && $payload['effective_to'] < $payload['effective_from']) {
            throw new DomainException('effective_to must be greater or equal to effective_from', 422);
        }

        if (strlen((string) ($payload['currency'] ?? 'BRL')) !== 3) {
            throw new DomainException('currency must contain three characters', 422);
        }
    }

    private function assertNoRateOverlap(int $workCenterId, string $from, ?string $to, ?int $ignoreId = null): void
    {
        $query = WorkCenterHourRate::query()
            ->where('work_center_id', $workCenterId)
            ->where('status', 'ACTIVE')
            ->when($ignoreId !== null, static fn (Builder $builder) => $builder->where('id', '!=', $ignoreId))
            ->whereDate('effective_from', '<=', $to ?? $from)
            ->where(static function (Builder $builder) use ($from): void {
                $builder->whereNull('effective_to')->orWhereDate('effective_to', '>=', $from);
            });

        if ($query->exists()) {
            throw new DomainException('Active work center hour rates cannot overlap', 422);
        }
    }
}
