<?php

declare(strict_types=1);

namespace App\Modules\Analysis\Application\Services;

use App\Modules\Analysis\Infrastructure\Persistence\Models\ManufacturingAnalyticsRecommendation;
use App\Modules\Production\Infrastructure\Persistence\Models\ProductionOrderMaterialConsumption;
use App\Modules\Production\Infrastructure\Persistence\Models\ProductionOrderOperation;
use App\Shared\Application\Cache\CacheManager;
use App\Shared\Application\Services\BaseService;
use App\Shared\Application\Transactions\TransactionManager;
use App\Shared\Infrastructure\Logging\AppLogger;
use App\Shared\Presentation\Exceptions\DomainException;
use Illuminate\Support\Collection;

final class ManufacturingAnalyticsService extends BaseService
{
    public function __construct(TransactionManager $transaction, CacheManager $cache, AppLogger $logger)
    {
        parent::__construct($transaction, $cache, $logger);
    }

    public function overview(array $filters = []): array
    {
        $facts = $this->facts($filters);

        return ['contract_version' => 'ANA-001.v1', 'filters' => $filters, 'facts_count' => $facts->count(), 'totals' => $this->totals($facts), 'by_operation' => $this->group($facts, 'operation'), 'by_resource' => $this->group($facts, 'resource'), 'by_work_center' => $this->group($facts, 'work_center'), 'by_operator' => $this->group($facts, 'operator'), 'material_consumption' => $this->consumptionFacts($filters), 'facts' => $facts->values()->all()];
    }

    public function efficiency(array $filters = []): array
    {
        $facts = $this->facts($filters);

        return ['contract_version' => 'ANA-002.v1', 'formula' => 'min(100, standard_or_planned_minutes / actual_productive_minutes * 100)', 'pause_policy' => 'planned pauses are included in denominator and exposed separately', 'no_standard_policy' => 'actual productive time is used as standard', 'data' => $this->group($facts, 'operation', true), 'by_resource' => $this->group($facts, 'resource', true), 'by_work_center' => $this->group($facts, 'work_center', true), 'by_operator' => $this->group($facts, 'operator', true)];
    }

    public function oee(array $filters = []): array
    {
        $facts = $this->facts($filters);

        return ['contract_version' => 'ANA-003.v1', 'formulae' => ['availability' => 'productive/(productive+pause)', 'performance' => 'min(100, planned_productive/productive)', 'quality' => 'good/processed', 'oee' => 'availability*performance*quality'], 'data' => $this->group($facts, 'resource', false, true), 'warnings' => $facts->filter(fn (array $f) => $f['actual_productive_minutes'] <= 0 || $f['quantity_processed'] <= 0)->count() ? ['PERIOD_WITHOUT_MINIMUM_DATA'] : []];
    }

    public function standardTimeEvidence(array $filters = [], int $minimumSample = 5): array
    {
        $groups = $this->facts($filters)->filter(fn (array $f) => $f['actual_productive_minutes'] > 0)->groupBy('operation_key');
        $rows = [];
        foreach ($groups as $key => $items) {
            $values = $items->pluck('actual_productive_minutes')->sort()->values();
            $sample = $values->count();
            $avg = $sample ? $values->avg() : 0;
            $rows[] = ['operation_key' => $key, 'operation_code' => $items->first()['operation_code'], 'sample_size' => $sample, 'average_minutes' => round($avg, 2), 'median_minutes' => round($this->percentile($values, .5), 2), 'p90_minutes' => round($this->percentile($values, .9), 2), 'min_minutes' => round((float) $values->min(), 2), 'max_minutes' => round((float) $values->max(), 2), 'outliers' => $values->filter(fn ($v) => $avg > 0 && abs($v - $avg) / $avg > .3)->values()->all(), 'eligible_for_recommendation' => $sample >= $minimumSample];
        }

        return ['contract_version' => 'ANA-004.v1', 'minimum_sample' => $minimumSample, 'rows' => $rows];
    }

    public function createRecommendation(array $filters = [], int $minimumSample = 5, ?int $userId = null): array
    {
        $evidence = $this->standardTimeEvidence($filters, $minimumSample);
        $created = [];
        foreach ($evidence['rows'] as $row) {
            if (! $row['eligible_for_recommendation']) {
                continue;
            }$operation = ProductionOrderOperation::query()->where('operation_code', $row['operation_code'])->where('status', 'COMPLETED')->first();
            if (! $operation) {
                continue;
            }$created[] = ManufacturingAnalyticsRecommendation::query()->create(['production_order_operation_id' => $operation->id, 'standard_time_id' => $operation->standard_time_id, 'standard_time_version' => $operation->standard_time_version, 'status' => 'PENDING', 'current_time_minutes' => $operation->productive_time_minutes, 'suggested_time_minutes' => $row['median_minutes'], 'sample_size' => $row['sample_size'], 'statistics' => $row, 'filters' => $filters])->toArray();
        }

        return ['evidence' => $evidence, 'recommendations' => $created, 'created_by' => $userId];
    }

    public function decideRecommendation(int $id, string $status, ?string $reason, ?int $userId = null): array
    {
        $recommendation = ManufacturingAnalyticsRecommendation::query()->findOrFail($id);
        if (! in_array($status, ['ACCEPTED', 'REJECTED', 'INVESTIGATE', 'ECO_REQUIRED'], true)) {
            throw new DomainException('Invalid recommendation decision', 422);
        }$recommendation->update(['status' => $status, 'decision_reason' => $reason, 'decided_by' => $userId, 'decided_at' => now()]);

        return $recommendation->fresh()->toArray();
    }

    private function facts(array $filters): Collection
    {
        $query = ProductionOrderOperation::query()->with(['productionOrder.product', 'workCenter', 'productionResource', 'events', 'outputs']);
        if (isset($filters['production_order_id'])) {
            $query->where('production_order_id', (int) $filters['production_order_id']);
        }if (isset($filters['work_center_id'])) {
            $query->where('work_center_id', (int) $filters['work_center_id']);
        }if (isset($filters['production_resource_id'])) {
            $query->where('actual_production_resource_id', (int) $filters['production_resource_id']);
        }if (isset($filters['operator_id'])) {
            $query->where('operator_id', (int) $filters['operator_id']);
        }if (isset($filters['product_id'])) {
            $query->whereHas('productionOrder', fn ($q) => $q->where('product_id', (int) $filters['product_id']));
        }if (isset($filters['date_from'])) {
            $query->whereDate('actual_started_at', '>=', $filters['date_from']);
        }if (isset($filters['date_to'])) {
            $query->whereDate('actual_completed_at', '<=', $filters['date_to']);
        }

        return $query->get()->map(function (ProductionOrderOperation $op): array {
            $planned = (float) $op->productive_time_minutes;
            $actual = (float) $op->actual_productive_minutes;

            return ['production_order_id' => (int) $op->production_order_id, 'order_number' => $op->productionOrder?->order_number, 'product_id' => (int) ($op->productionOrder?->product_id ?? 0), 'operation_id' => (int) $op->id, 'operation_key' => $op->operation_code.'|'.$op->work_center_id, 'operation_code' => $op->operation_code, 'operation_name' => $op->operation_name, 'work_center_id' => (int) $op->work_center_id, 'work_center' => $op->workCenter?->code, 'production_resource_id' => $op->actual_production_resource_id ?: $op->production_resource_id, 'resource' => $op->productionResource?->code, 'operator_id' => $op->operator_id, 'planned_productive_minutes' => $planned, 'planned_total_minutes' => (float) $op->total_time_minutes, 'actual_productive_minutes' => $actual, 'actual_pause_minutes' => (float) $op->actual_pause_minutes, 'quantity_planned' => (float) $op->quantity_planned, 'quantity_processed' => (float) $op->quantity_processed, 'quantity_good' => (float) $op->quantity_good, 'quantity_scrapped' => (float) $op->quantity_scrapped, 'quantity_rework' => (float) $op->quantity_rework, 'status' => $op->status, 'standard_time_id' => $op->standard_time_id, 'standard_time_version' => $op->standard_time_version, 'planned_start_at' => $op->planned_start_at?->toIso8601String(), 'actual_completed_at' => $op->actual_completed_at?->toIso8601String()];
        });
    }

    private function consumptionFacts(array $filters): array
    {
        $query = ProductionOrderMaterialConsumption::query()->with(['product', 'productionOrder']);
        if (isset($filters['production_order_id'])) {
            $query->where('production_order_id', (int) $filters['production_order_id']);
        }if (isset($filters['product_id'])) {
            $query->where('product_id', (int) $filters['product_id']);
        }if (isset($filters['date_from'])) {
            $query->whereDate('consumed_at', '>=', $filters['date_from']);
        }if (isset($filters['date_to'])) {
            $query->whereDate('consumed_at', '<=', $filters['date_to']);
        }$rows = $query->whereNull('reversed_by_movement_id')->get();
        $grouped = $rows->groupBy('product_id')->map(function (Collection $items): array {
            $additional = $items->filter(fn ($item) => (bool) data_get($item->metadata, 'is_unplanned'))->sum('quantity_consumed');

            return ['product_id' => (int) $items->first()->product_id, 'sku' => $items->first()->product?->sku, 'description' => $items->first()->product?->description, 'consumed_quantity' => round($items->sum('quantity_consumed'), 6), 'additional_quantity' => round($additional, 6), 'planned_quantity' => round($items->filter(fn ($item) => ! data_get($item->metadata, 'is_unplanned'))->sum(fn ($item) => (float) (data_get($item->metadata, 'planned_quantity') ?? 0)), 6), 'is_additional' => round($additional, 6) > 0];
        })->values();

        return ['totals' => ['consumed_quantity' => round($grouped->sum('consumed_quantity'), 6), 'additional_quantity' => round($grouped->sum('additional_quantity'), 6)], 'by_product' => $grouped->all()];
    }

    private function totals(Collection $facts): array
    {
        return ['planned_quantity' => round($facts->sum('quantity_planned'), 6), 'processed_quantity' => round($facts->sum('quantity_processed'), 6), 'good_quantity' => round($facts->sum('quantity_good'), 6), 'scrapped_quantity' => round($facts->sum('quantity_scrapped'), 6), 'rework_quantity' => round($facts->sum('quantity_rework'), 6), 'planned_productive_minutes' => round($facts->sum('planned_productive_minutes'), 2), 'actual_productive_minutes' => round($facts->sum('actual_productive_minutes'), 2), 'pause_minutes' => round($facts->sum('actual_pause_minutes'), 2)];
    }

    private function group(Collection $facts, string $dimension, bool $withEfficiency = false, bool $withOee = false): array
    {
        $grouped = $facts->groupBy(fn (array $f) => match ($dimension) {
            'operation' => $f['operation_key'],'resource' => (string) ($f['production_resource_id'] ?: 'UNASSIGNED'),'work_center' => (string) $f['work_center_id'],'operator' => (string) ($f['operator_id'] ?: 'UNIDENTIFIED'),default => 'ALL'
        });

        return $grouped->map(function (Collection $items, $key) use ($dimension, $withEfficiency, $withOee) {
            $row = ['dimension' => $dimension, 'key' => (string) $key, 'count' => $items->count(), 'totals' => $this->totals($items)];
            if ($withEfficiency) {
                $actual = $row['totals']['actual_productive_minutes'];
                $standard = $row['totals']['planned_productive_minutes'] ?: $actual;
                $row['efficiency_percent'] = $actual > 0 ? min(100, round($standard / $actual * 100, 2)) : null;
            }if ($withOee) {
                $t = $row['totals'];
                $a = ($t['actual_productive_minutes'] + $t['pause_minutes']) > 0 ? $t['actual_productive_minutes'] / ($t['actual_productive_minutes'] + $t['pause_minutes']) : null;
                $p = $t['actual_productive_minutes'] > 0 ? min(1, $t['planned_productive_minutes'] / $t['actual_productive_minutes']) : null;
                $q = $t['processed_quantity'] > 0 ? min(1, $t['good_quantity'] / $t['processed_quantity']) : null;
                $row['availability_percent'] = $a === null ? null : round($a * 100, 2);
                $row['performance_percent'] = $p === null ? null : round($p * 100, 2);
                $row['quality_percent'] = $q === null ? null : round($q * 100, 2);
                $row['oee_percent'] = ($a !== null && $p !== null && $q !== null) ? round($a * $p * $q * 100, 2) : null;
            }

            return $row;
        })->values()->all();
    }

    private function percentile(Collection $values, float $percentile): float
    {
        return ManufacturingMetricCalculator::percentile($values->all(), $percentile);
    }
}
