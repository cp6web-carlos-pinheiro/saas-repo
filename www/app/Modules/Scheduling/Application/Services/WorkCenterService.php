<?php

declare(strict_types=1);

namespace App\Modules\Scheduling\Application\Services;

use App\Modules\Scheduling\Infrastructure\Persistence\Models\WorkCenter;
use App\Modules\Scheduling\Infrastructure\Persistence\Models\WorkCenterShift;
use App\Shared\Application\Cache\CacheManager;
use App\Shared\Application\Services\BaseService;
use App\Shared\Application\Transactions\TransactionManager;
use App\Shared\Infrastructure\Logging\AppLogger;
use App\Shared\Presentation\Exceptions\DomainException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class WorkCenterService extends BaseService
{
    private const VALID_TYPES = ['MACHINE', 'LINE'];

    public function __construct(
        TransactionManager $transaction,
        CacheManager $cache,
        AppLogger $logger
    ) {
        parent::__construct($transaction, $cache, $logger);
    }

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return WorkCenter::query()->orderBy('code')->paginate($perPage);
    }

    public function create(array $payload): array
    {
        $this->assertResourceType($payload['resource_type']);

        $workCenter = $this->inTransaction(function () use ($payload) {
            return WorkCenter::query()->create([
                'plant_id' => $payload['plant_id'],
                'code' => $payload['code'],
                'name' => $payload['name'],
                'resource_type' => $payload['resource_type'],
                'capacity_per_day' => $payload['capacity_per_day'],
                'efficiency_factor' => $payload['efficiency_factor'],
                'is_active' => $payload['is_active'] ?? true,
            ]);
        });

        return $workCenter->toArray();
    }

    public function show(int $id): array
    {
        $entity = WorkCenter::query()->with(['shifts'])->findOrFail($id);

        return $entity->toArray();
    }

    public function update(int $id, array $payload): array
    {
        $this->assertResourceType($payload['resource_type']);

        $entity = $this->inTransaction(function () use ($id, $payload) {
            $workCenter = WorkCenter::query()->findOrFail($id);
            $workCenter->fill($payload);
            $workCenter->save();

            return $workCenter;
        });

        return $entity->toArray();
    }

    public function delete(int $id): void
    {
        $this->inTransaction(function () use ($id): void {
            $entity = WorkCenter::query()->findOrFail($id);
            $entity->delete();
        });
    }

    public function addShift(int $workCenterId, array $payload): array
    {
        $workCenter = WorkCenter::query()->findOrFail($workCenterId);

        $shift = $this->inTransaction(function () use ($workCenter, $payload) {
            return WorkCenterShift::query()->create([
                'work_center_id' => $workCenter->id,
                'name' => $payload['name'],
                'shift_start' => $payload['shift_start'],
                'shift_end' => $payload['shift_end'],
                'capacity_hours' => $payload['capacity_hours'],
                'is_active' => $payload['is_active'] ?? true,
            ]);
        });

        return $shift->toArray();
    }

    private function assertResourceType(string $type): void
    {
        if (! in_array($type, self::VALID_TYPES, true)) {
            throw new DomainException('Invalid resource_type', 422, [
                'resource_type' => self::VALID_TYPES,
            ]);
        }
    }
}
