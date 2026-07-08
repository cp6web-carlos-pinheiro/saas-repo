<?php

declare(strict_types=1);

namespace App\Modules\Scheduling\Application\Services;

use App\Modules\Scheduling\Infrastructure\Persistence\Models\ProductionCalendarDay;
use App\Modules\Scheduling\Infrastructure\Persistence\Models\WorkCenter;
use App\Shared\Application\Cache\CacheManager;
use App\Shared\Application\Services\BaseService;
use App\Shared\Application\Transactions\TransactionManager;
use App\Shared\Infrastructure\Logging\AppLogger;
use Carbon\Carbon;
use Illuminate\Support\Collection;

final class ProductionCalendarService extends BaseService
{
    public function __construct(
        TransactionManager $transaction,
        CacheManager $cache,
        AppLogger $logger
    ) {
        parent::__construct($transaction, $cache, $logger);
    }

    public function listByRange(int $workCenterId, string $fromDate, string $toDate): Collection
    {
        WorkCenter::query()->findOrFail($workCenterId);

        return ProductionCalendarDay::query()
            ->where('work_center_id', $workCenterId)
            ->whereDate('calendar_date', '>=', $fromDate)
            ->whereDate('calendar_date', '<=', $toDate)
            ->orderBy('calendar_date')
            ->get();
    }

    public function upsertDay(int $workCenterId, array $payload): array
    {
        $workCenter = WorkCenter::query()->findOrFail($workCenterId);

        $day = $this->inTransaction(function () use ($workCenter, $payload) {
            $entity = ProductionCalendarDay::query()->firstOrNew([
                'work_center_id' => $workCenter->id,
                'calendar_date' => $payload['calendar_date'],
            ]);

            $entity->fill([
                'is_working_day' => $payload['is_working_day'],
                'available_capacity' => $payload['available_capacity'] ?? null,
                'notes' => $payload['notes'] ?? null,
            ]);

            $entity->save();

            return $entity;
        });

        return $day->toArray();
    }

    public function bulkGenerate(int $workCenterId, string $fromDate, string $toDate): int
    {
        $workCenter = WorkCenter::query()->findOrFail($workCenterId);
        $from = Carbon::parse($fromDate)->startOfDay();
        $to = Carbon::parse($toDate)->startOfDay();

        $createdOrUpdated = 0;

        $this->inTransaction(function () use ($workCenter, $from, $to, &$createdOrUpdated): void {
            for ($date = $from->copy(); $date->lte($to); $date->addDay()) {
                $isWeekend = in_array($date->dayOfWeekIso, [6, 7], true);

                ProductionCalendarDay::query()->updateOrCreate(
                    [
                        'work_center_id' => $workCenter->id,
                        'calendar_date' => $date->toDateString(),
                    ],
                    [
                        'is_working_day' => ! $isWeekend,
                        'available_capacity' => ! $isWeekend
                            ? round($workCenter->capacity_per_day * ($workCenter->efficiency_factor / 100), 2)
                            : 0,
                    ]
                );

                $createdOrUpdated++;
            }
        });

        return $createdOrUpdated;
    }
}
