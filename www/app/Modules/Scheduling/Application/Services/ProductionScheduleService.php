<?php

declare(strict_types=1);

namespace App\Modules\Scheduling\Application\Services;

use App\Modules\Production\Infrastructure\Persistence\Models\ProductionOrder;
use App\Modules\Production\Infrastructure\Persistence\Models\ProductionOrderOperation;
use App\Modules\Scheduling\Infrastructure\Persistence\Models\ProductionSchedule;
use App\Modules\Scheduling\Infrastructure\Persistence\Models\ProductionScheduleLine;
use App\Shared\Application\Cache\CacheManager;
use App\Shared\Application\Services\BaseService;
use App\Shared\Application\Transactions\TransactionManager;
use App\Shared\Infrastructure\Logging\AppLogger;
use App\Shared\Presentation\Exceptions\DomainException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class ProductionScheduleService extends BaseService
{
    public function __construct(
        private readonly ProductionSchedulingService $scheduler,
        TransactionManager $transaction,
        CacheManager $cache,
        AppLogger $logger
    ) { parent::__construct($transaction, $cache, $logger); }

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return ProductionSchedule::query()->withCount('lines')->orderByDesc('id')->paginate($perPage);
    }

    public function show(int $id): array
    {
        return ProductionSchedule::query()->with(['lines.productionOrder', 'lines.productionOrderOperation'])->findOrFail($id)->toArray();
    }

    public function createDraft(array $payload, ?int $userId = null): array
    {
        $result = $this->scheduler->schedule($payload);
        $schedule = $this->inTransaction(function () use ($payload, $result, $userId) {
            $schedule = ProductionSchedule::query()->create([
                'schedule_number' => $this->nextNumber(),
                'version_number' => 1,
                'status' => 'DRAFT',
                'reference_date' => $result['reference_date'],
                'mode' => $result['mode'],
                'direction' => $result['direction'],
                'sequencing_rule' => $result['sequencing_rule'],
                'parameters' => $payload,
                'source_run_key' => $payload['source_run_key'] ?? null,
                'created_by' => $userId,
            ]);
            foreach ($result['orders'] as $orderResult) {
                foreach ($orderResult['operations'] as $line) {
                    if (empty($line['production_order_operation_id'])) continue;
                    ProductionScheduleLine::query()->create([
                        'production_schedule_id' => $schedule->id,
                        'production_order_id' => $orderResult['production_order_id'],
                        'production_order_operation_id' => $line['production_order_operation_id'],
                        'work_center_id' => $line['work_center_id'],
                        'production_resource_id' => $line['production_resource_id'] ?? null,
                        'planned_start_at' => $line['start_at'],
                        'planned_end_at' => $line['end_at'],
                        'total_time_minutes' => $line['duration_minutes'],
                        'capacity_time_minutes' => $line['capacity_time_minutes'] ?? $line['duration_minutes'],
                        'lead_time_minutes' => $line['lead_time_minutes'] ?? 0,
                        'segments' => $line['segments'] ?? [],
                        'status' => 'PLANNED',
                        'metadata' => ['mode' => $result['mode'], 'direction' => $result['direction']],
                    ]);
                }
            }
            return $schedule;
        });
        return $schedule->load('lines')->toArray();
    }

    public function publish(int $id, ?int $userId = null, ?string $reason = null): array
    {
        $schedule = ProductionSchedule::query()->with('lines')->findOrFail($id);
        if ($schedule->status === 'PUBLISHED') return $schedule->load('lines')->toArray();
        if ($schedule->status === 'CANCELLED') throw new DomainException('Cancelled schedule cannot be published', 422);
        $updated = $this->inTransaction(function () use ($schedule, $userId, $reason) {
            foreach ($schedule->lines as $line) {
                $operation = ProductionOrderOperation::query()->findOrFail($line->production_order_operation_id);
                if (in_array($operation->status, ['COMPLETED', 'CANCELLED'], true)) throw new DomainException('Schedule contains a closed operation', 422);
                $operation->update(['planned_start_at' => $line->planned_start_at, 'planned_end_at' => $line->planned_end_at, 'production_resource_id' => $line->production_resource_id, 'status' => 'SCHEDULED']);
            }
            $schedule->update(['status' => 'PUBLISHED', 'approved_by' => $userId, 'approved_at' => now(), 'published_by' => $userId, 'published_at' => now(), 'change_reason' => $reason]);
            return $schedule;
        });
        return $updated->load('lines')->toArray();
    }

    public function cancel(int $id, ?int $userId = null, ?string $reason = null): array
    {
        $schedule = ProductionSchedule::query()->findOrFail($id);
        if ($schedule->status === 'PUBLISHED') throw new DomainException('Published schedule must be superseded, not cancelled', 422);
        $schedule->update(['status' => 'CANCELLED', 'change_reason' => $reason, 'approved_by' => $userId]);
        return $schedule->toArray();
    }

    public function compare(int $id, int $otherId): array
    {
        $left = ProductionScheduleLine::query()->where('production_schedule_id', $id)->get()->keyBy('production_order_operation_id');
        $right = ProductionScheduleLine::query()->where('production_schedule_id', $otherId)->get()->keyBy('production_order_operation_id');
        $keys = $left->keys()->merge($right->keys())->unique();
        return $keys->map(fn ($key) => ['production_order_operation_id' => $key, 'current' => $left->get($key)?->toArray(), 'other' => $right->get($key)?->toArray()])->values()->all();
    }

    private function nextNumber(): string
    {
        return 'PS-'.now()->format('Ymd-His').'-'.random_int(100, 999);
    }
}
