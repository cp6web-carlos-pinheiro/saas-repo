<?php

declare(strict_types=1);

namespace App\Modules\Scheduling\Application\Services;

use App\Modules\Production\Infrastructure\Persistence\Models\ProductionOrder;
use App\Modules\Production\Application\Services\ProductionOrderOperationPlanningService;
use App\Modules\Scheduling\Infrastructure\Persistence\Models\ProductionCalendarDay;
use App\Modules\Scheduling\Infrastructure\Persistence\Models\WorkCenter;
use App\Modules\Scheduling\Infrastructure\Persistence\Models\WorkCenterShift;
use App\Modules\Scheduling\Infrastructure\Persistence\Models\ProductionResource;
use App\Shared\Application\Cache\CacheManager;
use App\Shared\Application\Services\BaseService;
use App\Shared\Application\Transactions\TransactionManager;
use App\Shared\Infrastructure\Logging\AppLogger;
use App\Shared\Presentation\Exceptions\DomainException;
use Carbon\Carbon;
use Illuminate\Support\Collection;

final class ProductionSchedulingService extends BaseService
{
    public function __construct(
        TransactionManager $transaction,
        CacheManager $cache,
        AppLogger $logger,
        private readonly ProductionOrderOperationPlanningService $operationPlanningService
    ) {
        parent::__construct($transaction, $cache, $logger);
    }

    public function schedule(array $payload): array
    {
        $referenceDate = Carbon::parse($payload['reference_date'] ?? now())->toDateString();
        $mode = (string) ($payload['mode'] ?? 'finite');
        $direction = (string) ($payload['direction'] ?? 'forward');
        $sequencingRule = (string) ($payload['sequencing_rule'] ?? 'priority_due_date');

        $cacheKey = sprintf(
            'production:schedule:%s',
            sha1(json_encode([
                'reference_date' => $referenceDate,
                'mode' => $mode,
                'direction' => $direction,
                'sequencing_rule' => $sequencingRule,
                'production_order_ids' => array_map('intval', $payload['production_order_ids']),
            ], JSON_THROW_ON_ERROR))
        );

        return $this->cacheRemember($cacheKey, 300, function () use ($payload, $referenceDate, $mode, $direction, $sequencingRule): array {
            $orders = $this->loadProductionOrders($payload['production_order_ids']);
            $orderedOrders = $this->sequenceOrders($orders, $sequencingRule);
            $scheduleState = [
                'work_center_usage' => [],
                'resource_usage' => [],
                'work_center_cursor' => [],
            ];
            $scheduledOrders = [];

            foreach ($orderedOrders as $order) {
                $scheduledOrders[] = $this->scheduleOrder(
                    $order,
                    $referenceDate,
                    $mode,
                    $direction,
                    $scheduleState
                );
            }

            return [
                'reference_date' => $referenceDate,
                'mode' => $mode,
                'direction' => $direction,
                'sequencing_rule' => $sequencingRule,
                'orders' => $scheduledOrders,
            ];
        });
    }

    private function loadProductionOrders(array $productionOrderIds): Collection
    {
        $orders = ProductionOrder::query()
            ->with(['product', 'snapshot.routingOperations.workCenter'])
            ->whereIn('id', array_map('intval', $productionOrderIds))
            ->get();

        foreach ($orders as $order) {
            if (! $order->snapshot || $order->snapshot->routingOperations->isEmpty()) {
                throw new DomainException(sprintf('Production order %d does not have a frozen routing snapshot', $order->id), 422);
            }
            $this->operationPlanningService->materialize((int) $order->id);
        }

        return $orders;
    }

    private function sequenceOrders(Collection $orders, string $sequencingRule): Collection
    {
        return $orders->sortBy(function (ProductionOrder $order) use ($sequencingRule): array {
            $priority = (int) ($order->metadata['priority'] ?? 1000);
            $dueDate = $order->scheduled_end_date?->toDateString() ?? '9999-12-31';
            $releaseDate = $order->scheduled_start_date?->toDateString() ?? '0001-01-01';

            return match ($sequencingRule) {
                'due_date_priority' => [$dueDate, $priority, $order->order_number],
                'release_date_priority' => [$releaseDate, $priority, $order->order_number],
                'order_number' => [$order->order_number],
                default => [$priority, $dueDate, $order->order_number],
            };
        })->values();
    }

    private function scheduleOrder(
        ProductionOrder $order,
        string $referenceDate,
        string $mode,
        string $direction,
        array &$scheduleState
    ): array {
        $operations = $order->operations->isNotEmpty()
            ? $order->operations->sortBy('sequence')->values()
            : $order->snapshot->routingOperations->sortBy('sequence')->values();

        $anchorStart = Carbon::parse($order->scheduled_start_date ?? $referenceDate)->startOfDay();
        $anchorEnd = Carbon::parse($order->scheduled_end_date ?? $referenceDate)->endOfDay();

        if ($direction === 'backward') {
            $cursor = $anchorEnd->copy();
            $scheduledOperations = [];

            foreach ($operations->reverse()->values() as $operation) {
                $scheduled = $this->scheduleOperationBackward($operation, $cursor, $mode, $scheduleState);
                $scheduledOperations[] = $scheduled;
                $cursor = Carbon::parse($scheduled['start_at']);
            }

            $scheduledOperations = array_reverse($scheduledOperations);

            return [
                'production_order_id' => $order->id,
                'order_number' => $order->order_number,
                'product_id' => $order->product_id,
                'sequencing_anchor' => $anchorEnd->toDateTimeString(),
                'direction' => $direction,
                'mode' => $mode,
                'operations' => $scheduledOperations,
            ];
        }

        $cursor = $anchorStart->copy();
        $scheduledOperations = [];

        foreach ($operations as $operation) {
            $scheduled = $this->scheduleOperationForward($operation, $cursor, $mode, $scheduleState);
            $scheduledOperations[] = $scheduled;
            $cursor = Carbon::parse($scheduled['end_at']);
        }

        return [
            'production_order_id' => $order->id,
            'order_number' => $order->order_number,
            'product_id' => $order->product_id,
            'sequencing_anchor' => $anchorStart->toDateTimeString(),
            'direction' => $direction,
            'mode' => $mode,
            'operations' => $scheduledOperations,
        ];
    }

    private function scheduleOperationForward(object $operation, Carbon $cursor, string $mode, array &$scheduleState): array
    {
        $workCenterId = (int) $operation->work_center_id;
        $resourceId = isset($operation->production_resource_id) ? (int) $operation->production_resource_id : null;
        $durationMinutes = $this->calculateOperationDurationMinutes($operation);

        if ($mode === 'infinite') {
            $startAt = $cursor->copy();
            $endAt = $startAt->copy()->addMinutes($durationMinutes);

            return $this->serializeOperationSchedule($operation, $workCenterId, $startAt, $endAt, $durationMinutes, $mode, 'forward');
        }

        $startAt = $this->alignToWorkingDayForward($workCenterId, $cursor, $scheduleState);
        $segments = [];
        $remainingMinutes = $durationMinutes;
        $current = $startAt->copy();

        while ($remainingMinutes > 0) {
            $dayKey = $current->toDateString();
            $capacity = $this->resolveDailyCapacityMinutes($workCenterId, $current, $resourceId);
            $usageKey = $resourceId ? 'resource_usage' : 'work_center_usage';
            $used = (int) ($scheduleState[$usageKey][$resourceId ?: $workCenterId][$dayKey] ?? 0);
            $available = max(0, $capacity - $used);

            if ($available <= 0) {
                $current = $this->nextWorkingDayStart($workCenterId, $current->copy()->addDay(), $scheduleState);
                continue;
            }

            $chunk = min($available, $remainingMinutes);
            $segmentStart = $current->copy();
            $segmentEnd = $segmentStart->copy()->addMinutes($chunk);

            $segments[] = [
                'start_at' => $segmentStart->toDateTimeString(),
                'end_at' => $segmentEnd->toDateTimeString(),
                'duration_minutes' => $chunk,
                'work_center_id' => $workCenterId,
            ];

            if ($resourceId) {
                $scheduleState['resource_usage'][$resourceId][$dayKey] = $used + $chunk;
            } else {
                $scheduleState['work_center_usage'][$workCenterId][$dayKey] = $used + $chunk;
            }
            $remainingMinutes -= $chunk;
            $current = $segmentEnd;

            if ($remainingMinutes > 0 && $current->toDateString() === $segmentStart->toDateString()) {
                $current = $this->nextWorkingDayStart($workCenterId, $current->copy()->addDay(), $scheduleState);
            }
        }

        $firstSegment = $segments[0];
        $lastSegment = $segments[count($segments) - 1];

        return $this->serializeOperationSchedule(
            $operation,
            $workCenterId,
            Carbon::parse($firstSegment['start_at']),
            Carbon::parse($lastSegment['end_at']),
            $durationMinutes,
            $mode,
            'forward',
            $segments
        );
    }

    private function scheduleOperationBackward(object $operation, Carbon $cursor, string $mode, array &$scheduleState): array
    {
        $workCenterId = (int) $operation->work_center_id;
        $resourceId = isset($operation->production_resource_id) ? (int) $operation->production_resource_id : null;
        $durationMinutes = $this->calculateOperationDurationMinutes($operation);

        if ($mode === 'infinite') {
            $endAt = $cursor->copy();
            $startAt = $endAt->copy()->subMinutes($durationMinutes);

            return $this->serializeOperationSchedule($operation, $workCenterId, $startAt, $endAt, $durationMinutes, $mode, 'backward');
        }

        $endAt = $this->alignToWorkingDayBackward($workCenterId, $cursor, $scheduleState);
        $segments = [];
        $remainingMinutes = $durationMinutes;
        $current = $endAt->copy();

        while ($remainingMinutes > 0) {
            $dayKey = $current->toDateString();
            $capacity = $this->resolveDailyCapacityMinutes($workCenterId, $current, $resourceId);
            $usageKey = $resourceId ? 'resource_usage' : 'work_center_usage';
            $used = (int) ($scheduleState[$usageKey][$resourceId ?: $workCenterId][$dayKey] ?? 0);
            $available = max(0, $capacity - $used);

            if ($available <= 0) {
                $current = $this->previousWorkingDayEnd($workCenterId, $current->copy()->subDay(), $scheduleState);
                continue;
            }

            $chunk = min($available, $remainingMinutes);
            $segmentEnd = $current->copy();
            $segmentStart = $segmentEnd->copy()->subMinutes($chunk);

            $segments[] = [
                'start_at' => $segmentStart->toDateTimeString(),
                'end_at' => $segmentEnd->toDateTimeString(),
                'duration_minutes' => $chunk,
                'work_center_id' => $workCenterId,
            ];

            if ($resourceId) {
                $scheduleState['resource_usage'][$resourceId][$dayKey] = $used + $chunk;
            } else {
                $scheduleState['work_center_usage'][$workCenterId][$dayKey] = $used + $chunk;
            }
            $remainingMinutes -= $chunk;
            $current = $segmentStart;

            if ($remainingMinutes > 0 && $current->toDateString() === $segmentEnd->toDateString()) {
                $current = $this->previousWorkingDayEnd($workCenterId, $current->copy()->subDay(), $scheduleState);
            }
        }

        $segments = array_reverse($segments);
        $firstSegment = $segments[0];
        $lastSegment = $segments[count($segments) - 1];

        return $this->serializeOperationSchedule(
            $operation,
            $workCenterId,
            Carbon::parse($firstSegment['start_at']),
            Carbon::parse($lastSegment['end_at']),
            $durationMinutes,
            $mode,
            'backward',
            $segments
        );
    }

    private function calculateOperationDurationMinutes(object $operation): int
    {
        return max(1, (int) ceil(
            (float) ($operation->productive_time_minutes ?? 0)
            ?: (float) ($operation->setup_time_minutes ?? 0) + (float) ($operation->runtime_time_minutes ?? $operation->runtime_minutes ?? 0)
        ));
    }

    private function serializeOperationSchedule(
        object $operation,
        int $workCenterId,
        Carbon $startAt,
        Carbon $endAt,
        int $durationMinutes,
        string $mode,
        string $direction,
        array $segments = []
    ): array {
        $leadTimeMinutes = max(0, (int) round((float) ($operation->lead_time_minutes ?? (($operation->queue_time_minutes ?? 0) + ($operation->move_time_minutes ?? 0)))));
        if ($direction === 'backward') {
            $startAt = $startAt->copy()->subMinutes($leadTimeMinutes);
        } else {
            $endAt = $endAt->copy()->addMinutes($leadTimeMinutes);
        }
        return [
            'operation_no' => (int) $operation->operation_no,
            'production_order_operation_id' => isset($operation->id) && $operation instanceof \App\Modules\Production\Infrastructure\Persistence\Models\ProductionOrderOperation ? (int) $operation->id : null,
            'operation_code' => (string) $operation->operation_code,
            'operation_name' => (string) $operation->operation_name,
            'sequence' => (int) $operation->sequence,
            'work_center_id' => $workCenterId,
            'production_resource_id' => $operation->production_resource_id ?? null,
            'mode' => $mode,
            'direction' => $direction,
            'duration_minutes' => $durationMinutes + $leadTimeMinutes,
            'capacity_time_minutes' => $durationMinutes,
            'lead_time_minutes' => $leadTimeMinutes,
            'start_at' => $startAt->toDateTimeString(),
            'end_at' => $endAt->toDateTimeString(),
            'segments' => $segments,
        ];
    }

    private function resolveDailyCapacityMinutes(int $workCenterId, Carbon $date, ?int $resourceId = null): int
    {
        $calendarDay = ProductionCalendarDay::query()
            ->where('work_center_id', $workCenterId)
            ->whereDate('calendar_date', $date->toDateString())
            ->first();

        if ($calendarDay && ! $calendarDay->is_working_day) {
            return 0;
        }

        if ($calendarDay && $calendarDay->available_capacity !== null && ! $resourceId) {
            return max(0, (int) round((float) $calendarDay->available_capacity * 60));
        }

        $workCenter = WorkCenter::query()->findOrFail($workCenterId);
        if ($resourceId) {
            $resource = ProductionResource::query()->where('id', $resourceId)->where('work_center_id', $workCenterId)->where('status', 'ACTIVE')->first();
            if (! $resource) return 0;
            $resourceCapacity = (float) ($resource->capacity_per_day ?: $workCenter->capacity_per_day);
            return max(0, (int) round($resourceCapacity * ((float) $resource->efficiency_factor / 100) * 60));
        }
        $shiftCapacityHours = (float) WorkCenterShift::query()
            ->where('work_center_id', $workCenterId)
            ->where('is_active', true)
            ->sum('capacity_hours');

        if ($shiftCapacityHours > 0) {
            return max(0, (int) round($shiftCapacityHours * ($workCenter->efficiency_factor / 100) * 60));
        }

        return max(0, (int) round((float) $workCenter->capacity_per_day * ($workCenter->efficiency_factor / 100) * 60));
    }

    private function isWorkingDay(int $workCenterId, Carbon $date): bool
    {
        $calendarDay = ProductionCalendarDay::query()
            ->where('work_center_id', $workCenterId)
            ->whereDate('calendar_date', $date->toDateString())
            ->first();

        if ($calendarDay) {
            return (bool) $calendarDay->is_working_day;
        }

        return ! in_array($date->dayOfWeekIso, [6, 7], true);
    }

    private function alignToWorkingDayForward(int $workCenterId, Carbon $cursor, array &$scheduleState): Carbon
    {
        $date = $cursor->copy();

        while (! $this->isWorkingDay($workCenterId, $date)) {
            $date->addDay()->startOfDay();
        }

        return $date->startOfDay();
    }

    private function alignToWorkingDayBackward(int $workCenterId, Carbon $cursor, array &$scheduleState): Carbon
    {
        $date = $cursor->copy();

        while (! $this->isWorkingDay($workCenterId, $date)) {
            $date->subDay()->endOfDay();
        }

        return $date->endOfDay();
    }

    private function nextWorkingDayStart(int $workCenterId, Carbon $date, array &$scheduleState): Carbon
    {
        $candidate = $date->copy()->startOfDay();

        while (! $this->isWorkingDay($workCenterId, $candidate)) {
            $candidate->addDay()->startOfDay();
        }

        return $candidate;
    }

    private function previousWorkingDayEnd(int $workCenterId, Carbon $date, array &$scheduleState): Carbon
    {
        $candidate = $date->copy()->endOfDay();

        while (! $this->isWorkingDay($workCenterId, $candidate)) {
            $candidate->subDay()->endOfDay();
        }

        return $candidate;
    }
}
