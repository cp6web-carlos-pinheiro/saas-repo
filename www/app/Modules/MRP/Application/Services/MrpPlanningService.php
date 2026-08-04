<?php

declare(strict_types=1);

namespace App\Modules\MRP\Application\Services;

use App\Modules\Bom\Application\Services\BomExplosionService;
use App\Modules\Inventory\Infrastructure\Persistence\Models\InventoryBalance;
use App\Modules\Product\Infrastructure\Persistence\Models\Product;
use App\Shared\Application\Cache\CacheManager;
use App\Shared\Application\Services\BaseService;
use App\Shared\Application\Transactions\TransactionManager;
use App\Shared\Infrastructure\Logging\AppLogger;
use App\Shared\Presentation\Exceptions\DomainException;
use Carbon\Carbon;
use Illuminate\Support\Collection;

final class MrpPlanningService extends BaseService
{
    private const PURCHASE_PRODUCT_TYPES = ['RAW', 'CONSUMABLE'];

    private const PRODUCTION_PRODUCT_TYPES = ['FG', 'WIP'];

    private const PLAN_CACHE_TTL = 900;

    private const SLICE_CACHE_TTL = 900;

    public function __construct(
        private readonly BomExplosionService $bomExplosionService,
        TransactionManager $transaction,
        CacheManager $cache,
        AppLogger $logger
    ) {
        parent::__construct($transaction, $cache, $logger);
    }

    public function run(array $payload, ?int $createdBy = null): array
    {
        return $this->buildPlan($payload, $createdBy, false);
    }

    public function recalculateIncrementally(array $payload, ?int $createdBy = null, ?string $idempotencyKey = null): array
    {
        $fingerprint = $this->buildPlanFingerprint($payload);
        $cacheKey = sprintf('mrp:recalc:idempotency:%s', $idempotencyKey ?? $fingerprint);

        $cached = $this->cache->get($cacheKey);

        if (is_array($cached)) {
            return $cached;
        }

        $result = $this->buildPlan($payload, $createdBy, true);

        $this->cache->put($cacheKey, $result, self::PLAN_CACHE_TTL);

        return $result;
    }

    private function buildPlan(array $payload, ?int $createdBy, bool $incremental): array
    {
        $payload = $this->normalizePlanningPayload($payload);
        $referenceDate = Carbon::parse($payload['reference_date'] ?? now())->toDateString();
        $planningBucket = (string) ($payload['planning_bucket'] ?? 'daily');
        $priorityRule = (string) ($payload['priority_rule'] ?? 'priority_due_date');
        $sliceResults = $this->buildDemandSlices($payload, $referenceDate, $incremental);
        $demandAggregation = array_column($sliceResults, 'demand_lines');
        $demandAggregation = array_merge(...$demandAggregation);
        $explodedRequirements = array_column($sliceResults, 'exploded_requirements');
        $explodedRequirements = array_merge(...$explodedRequirements);
        $grossRequirements = $this->aggregateRequirements(array_merge($demandAggregation, $explodedRequirements));
        $products = $this->loadProducts($grossRequirements);
        $minimumStockAlerts = $this->buildMinimumStockAlerts($grossRequirements, $products);
        $stockDeduction = $this->deductStock($grossRequirements, $products, $priorityRule);
        $netRequirements = array_values(array_filter(
            $stockDeduction,
            static fn (array $requirement): bool => (float) $requirement['quantity_net'] > 0
        ));
        $leadTimeOffsets = $this->applyLeadTimeOffsets($netRequirements, $products, $referenceDate, $planningBucket);
        $timeBuckets = $this->buildTimeBuckets($leadTimeOffsets, $planningBucket);
        $purchaseSuggestions = $this->buildPurchaseSuggestions($leadTimeOffsets, $products, $createdBy, $referenceDate);
        $productionSuggestions = $this->buildProductionSuggestions($leadTimeOffsets, $products, $createdBy, $referenceDate);

        $result = [
            'reference_date' => $referenceDate,
            'planning_bucket' => $planningBucket,
            'priority_rule' => $priorityRule,
            'demand_aggregation' => $demandAggregation,
            'bom_explosion' => [
                'requirements' => $explodedRequirements,
            ],
            'minimum_stock_alerts' => $minimumStockAlerts,
            'stock_deduction' => $stockDeduction,
            'net_requirements' => $netRequirements,
            'lead_time_offset' => $leadTimeOffsets,
            'time_buckets' => $timeBuckets,
            'purchase_suggestions' => $purchaseSuggestions,
            'production_suggestions' => $productionSuggestions,
        ];

        $this->logger->info('mrp.plan.generated', [
            'reference_date' => $referenceDate,
            'demand_lines' => count($demandAggregation),
            'gross_requirements' => count($grossRequirements),
            'net_requirements' => count($netRequirements),
            'minimum_stock_alerts' => count($minimumStockAlerts),
            'planning_bucket' => $planningBucket,
            'priority_rule' => $priorityRule,
            'purchase_suggestions' => count($purchaseSuggestions),
            'production_suggestions' => count($productionSuggestions),
        ]);

        return $result;
    }

    private function buildDemandSlices(array $payload, string $referenceDate, bool $incremental): array
    {
        $slices = [];
        $demandLines = array_values($payload['demand_lines']);
        $scopeProductIds = collect($payload['recompute_scope']['product_ids'] ?? [])->map(static fn ($value): int => (int) $value)->all();

        foreach ($demandLines as $index => $demandLine) {
            $sliceKey = $this->sliceCacheKey($demandLine, $referenceDate, $payload);
            $shouldRecompute = ! $incremental
                || empty($scopeProductIds)
                || in_array((int) $demandLine['product_id'], $scopeProductIds, true);

            if ($shouldRecompute) {
                $slice = $this->rebuildDemandSlice($demandLine, $referenceDate, $payload);
                $this->cache->put($sliceKey, $slice, self::SLICE_CACHE_TTL);
            } else {
                $slice = $this->cache->get($sliceKey);

                if (! is_array($slice)) {
                    $slice = $this->rebuildDemandSlice($demandLine, $referenceDate, $payload);
                    $this->cache->put($sliceKey, $slice, self::SLICE_CACHE_TTL);
                }
            }

            $slice['slice_index'] = $index;
            $slices[] = $slice;
        }

        return $slices;
    }

    private function normalizePlanningPayload(array $payload): array
    {
        $normalized = $payload;
        $demandLines = array_values($payload['demand_lines'] ?? []);
        $forecastLines = array_values($payload['forecast_lines'] ?? []);

        foreach ($forecastLines as $forecastLine) {
            $demandLines[] = array_merge($forecastLine, [
                'source_type' => $forecastLine['source_type'] ?? 'forecast',
                'source_reference_type' => $forecastLine['source_reference_type'] ?? 'forecast',
                'source_reference_id' => $forecastLine['source_reference_id'] ?? null,
            ]);
        }

        $normalized['demand_lines'] = $demandLines;

        return $normalized;
    }

    private function rebuildDemandSlice(array $demandLine, string $referenceDate, array $payload): array
    {
        $aggregatedDemand = $this->aggregateDemandLines([$demandLine], $referenceDate);
        $explodedRequirements = $this->explodeDemand($aggregatedDemand, $referenceDate);

        return [
            'slice_key' => $this->sliceCacheKey($demandLine, $referenceDate, $payload),
            'demand_lines' => $aggregatedDemand,
            'exploded_requirements' => $explodedRequirements,
            'fingerprint' => $this->buildSliceFingerprint($demandLine, $referenceDate, $payload),
        ];
    }

    private function aggregateDemandLines(array $demandLines, string $referenceDate): array
    {
        $aggregated = [];

        foreach (array_values($demandLines) as $index => $demandLine) {
            $needByDate = Carbon::parse($demandLine['need_by_date'])->toDateString();
            $quantity = round((float) $demandLine['quantity'], 6);

            $aggregationKey = implode('|', [
                (int) $demandLine['product_id'],
                isset($demandLine['warehouse_id']) ? (string) (int) $demandLine['warehouse_id'] : 'null',
                $needByDate,
                isset($demandLine['bom_version_number']) ? (string) (int) $demandLine['bom_version_number'] : 'null',
                isset($demandLine['routing_version_id']) ? (string) (int) $demandLine['routing_version_id'] : 'null',
            ]);

            if (! isset($aggregated[$aggregationKey])) {
                $aggregated[$aggregationKey] = [
                    'requirement_key' => $aggregationKey,
                    'requirement_source' => 'DEMAND',
                    'product_id' => (int) $demandLine['product_id'],
                    'warehouse_id' => isset($demandLine['warehouse_id']) ? (int) $demandLine['warehouse_id'] : null,
                    'need_by_date' => $needByDate,
                    'quantity_gross' => 0.0,
                    'quantity_net' => 0.0,
                    'quantity_covered' => 0.0,
                    'priority' => (int) ($demandLine['priority'] ?? 1000),
                    'bom_version_number' => isset($demandLine['bom_version_number']) ? (int) $demandLine['bom_version_number'] : null,
                    'routing_version_id' => isset($demandLine['routing_version_id']) ? (int) $demandLine['routing_version_id'] : null,
                    'source_type' => $demandLine['source_type'] ?? 'demand',
                    'source_types' => [($demandLine['source_type'] ?? 'demand')],
                    'source_reference_id' => isset($demandLine['source_reference_id']) ? (int) $demandLine['source_reference_id'] : null,
                    'source_reference_type' => $demandLine['source_reference_type'] ?? null,
                    'source_keys' => [],
                    'metadata' => [],
                ];
            }

            $aggregated[$aggregationKey]['quantity_gross'] = round((float) $aggregated[$aggregationKey]['quantity_gross'] + $quantity, 6);
            $aggregated[$aggregationKey]['source_keys'][] = sprintf('demand-%d', $index + 1);
            $aggregated[$aggregationKey]['source_types'] = array_values(array_unique(array_merge(
                $aggregated[$aggregationKey]['source_types'],
                [$demandLine['source_type'] ?? 'demand']
            )));

            if (! empty($demandLine['metadata'])) {
                $aggregated[$aggregationKey]['metadata'][] = $demandLine['metadata'];
            }
        }

        return array_values(array_map(static function (array $row): array {
            $row['source_type'] = count($row['source_types']) === 1 ? $row['source_types'][0] : 'mixed';

            return $row;
        }, $aggregated));
    }

    private function explodeDemand(array $demandAggregation, string $referenceDate): array
    {
        $explodedRequirements = [];

        foreach ($demandAggregation as $demandLine) {
            $product = Product::query()->findOrFail((int) $demandLine['product_id']);

            if (! in_array($product->product_type, self::PRODUCTION_PRODUCT_TYPES, true)) {
                continue;
            }

            $explosion = $this->bomExplosionService->explode(
                productId: (int) $product->id,
                referenceDate: $referenceDate,
                versionNumber: $demandLine['bom_version_number'] !== null ? (int) $demandLine['bom_version_number'] : null
            );

            if ($explosion['has_cycle']) {
                throw new DomainException('BOM cycle detected while generating the MRP plan', 422);
            }

            foreach ($explosion['exploded_materials'] as $explodedMaterial) {
                $quantityGross = round((float) $demandLine['quantity_gross'] * (float) $explodedMaterial['quantity_accumulated'], 6);

                $explodedRequirements[] = [
                    'requirement_key' => implode('|', [
                        (int) $explodedMaterial['component_product_id'],
                        isset($demandLine['warehouse_id']) ? (string) (int) $demandLine['warehouse_id'] : 'null',
                        $demandLine['need_by_date'],
                        (string) $explodedMaterial['bom_version_number'],
                        isset($demandLine['routing_version_id']) ? (string) (int) $demandLine['routing_version_id'] : 'null',
                    ]),
                    'requirement_source' => 'BOM',
                    'product_id' => (int) $explodedMaterial['component_product_id'],
                    'warehouse_id' => $demandLine['warehouse_id'],
                    'need_by_date' => $demandLine['need_by_date'],
                    'quantity_gross' => $quantityGross,
                    'quantity_net' => 0.0,
                    'quantity_covered' => 0.0,
                    'priority' => (int) $demandLine['priority'],
                    'bom_version_number' => (int) $explodedMaterial['bom_version_number'],
                    'routing_version_id' => $demandLine['routing_version_id'],
                    'source_type' => 'bom_component',
                    'source_reference_id' => (int) $explodedMaterial['bom_header_id'],
                    'source_reference_type' => 'bom_header',
                    'source_keys' => $demandLine['source_keys'],
                    'metadata' => [
                        'parent_product_id' => (int) $demandLine['product_id'],
                        'parent_requirement_key' => $demandLine['requirement_key'],
                        'path' => $explodedMaterial['path'],
                        'level' => (int) $explodedMaterial['level'],
                    ],
                ];
            }
        }

        return $explodedRequirements;
    }

    private function aggregateRequirements(array $requirements): array
    {
        $aggregated = [];

        foreach ($requirements as $requirement) {
            $aggregationKey = implode('|', [
                (int) $requirement['product_id'],
                isset($requirement['warehouse_id']) ? (string) (int) $requirement['warehouse_id'] : 'null',
                (string) $requirement['need_by_date'],
                isset($requirement['bom_version_number']) ? (string) (int) $requirement['bom_version_number'] : 'null',
                isset($requirement['routing_version_id']) ? (string) (int) $requirement['routing_version_id'] : 'null',
            ]);

            if (! isset($aggregated[$aggregationKey])) {
                $aggregated[$aggregationKey] = $requirement;
                $aggregated[$aggregationKey]['requirement_key'] = $aggregationKey;
                $aggregated[$aggregationKey]['quantity_gross'] = round((float) $requirement['quantity_gross'], 6);
                $aggregated[$aggregationKey]['quantity_covered'] = 0.0;
                $aggregated[$aggregationKey]['quantity_net'] = 0.0;
                continue;
            }

            $aggregated[$aggregationKey]['quantity_gross'] = round(
                (float) $aggregated[$aggregationKey]['quantity_gross'] + (float) $requirement['quantity_gross'],
                6
            );

            $aggregated[$aggregationKey]['source_keys'] = array_values(array_unique(array_merge(
                $aggregated[$aggregationKey]['source_keys'],
                $requirement['source_keys']
            )));

            if (! empty($requirement['metadata'])) {
                $aggregated[$aggregationKey]['metadata'][] = $requirement['metadata'];
            }
        }

        return array_values($aggregated);
    }

    private function loadProducts(array $requirements): Collection
    {
        $productIds = collect($requirements)
            ->pluck('product_id')
            ->unique()
            ->values()
            ->all();

        return Product::query()
            ->whereIn('id', $productIds)
            ->get()
            ->keyBy('id');
    }

    private function buildMinimumStockAlerts(array $requirements, Collection $products): array
    {
        $alerts = [];

        foreach (collect($requirements)->groupBy(function (array $requirement): string {
            return implode('|', [
                (int) $requirement['product_id'],
                isset($requirement['warehouse_id']) ? (string) (int) $requirement['warehouse_id'] : 'null',
            ]);
        }) as $group) {
            $firstRequirement = $group->first();
            $product = $products->get((int) $firstRequirement['product_id']);

            if (! $product) {
                continue;
            }

            $warehouseId = $firstRequirement['warehouse_id'] !== null ? (int) $firstRequirement['warehouse_id'] : null;
            $freeStock = $this->resolveFreeStock((int) $firstRequirement['product_id'], $warehouseId);
            $safetyStock = (float) $product->safety_stock;

            if ($safetyStock <= 0 || $freeStock >= $safetyStock) {
                continue;
            }

            $alerts[] = [
                'alert_type' => 'MINIMUM_STOCK',
                'product_id' => (int) $firstRequirement['product_id'],
                'product_sku' => $product->sku,
                'product_description' => $product->description,
                'warehouse_id' => $warehouseId,
                'available_stock' => $freeStock,
                'safety_stock' => $safetyStock,
                'reorder_quantity' => round($safetyStock - $freeStock, 6),
                'source_requirement_keys' => $group->pluck('requirement_key')->values()->all(),
            ];
        }

        return $alerts;
    }

    private function resolveFreeStock(int $productId, ?int $warehouseId): float
    {
        $query = InventoryBalance::query()->where('product_id', $productId);

        if ($warehouseId !== null) {
            $query->where('warehouse_id', $warehouseId);
        }

        $balanceRows = $query->get(['qty_available', 'qty_reserved']);

        return round($balanceRows->sum(static function (InventoryBalance $balance): float {
            return max(0.0, (float) $balance->qty_available - (float) $balance->qty_reserved);
        }), 6);
    }

    private function deductStock(array $requirements, Collection $products, string $priorityRule): array
    {
        $deducted = [];
        $availableStockCache = [];

        $sortedRequirements = collect($requirements)
            ->sort($this->priorityRuleComparator($priorityRule))
            ->values()
            ->all();

        foreach ($sortedRequirements as $requirement) {
            $product = $products->get($requirement['product_id']);

            if (! $product) {
                throw new DomainException('MRP referenced a product that does not exist for the current tenant', 422);
            }

            $stockKey = implode('|', [
                (int) $requirement['product_id'],
                isset($requirement['warehouse_id']) ? (string) (int) $requirement['warehouse_id'] : 'null',
            ]);

            if (! array_key_exists($stockKey, $availableStockCache)) {
                $availableStockCache[$stockKey] = $this->resolvePlanningStock(
                    (int) $requirement['product_id'],
                    $requirement['warehouse_id'] !== null ? (int) $requirement['warehouse_id'] : null,
                    (int) $product->safety_stock
                );
            }

            $availableStock = $availableStockCache[$stockKey];
            $quantityGross = round((float) $requirement['quantity_gross'], 6);
            $quantityCovered = round(min($quantityGross, $availableStock), 6);
            $quantityNet = round($quantityGross - $quantityCovered, 6);

            $availableStockCache[$stockKey] = round($availableStock - $quantityCovered, 6);

            $deducted[] = array_merge($requirement, [
                'qty_free_before_safety_stock' => $availableStock,
                'qty_covered' => $quantityCovered,
                'quantity_covered' => $quantityCovered,
                'quantity_net' => $quantityNet,
                'product_type' => $product->product_type,
                'product_sku' => $product->sku,
                'product_description' => $product->description,
                'safety_stock' => (int) $product->safety_stock,
            ]);
        }

        return $deducted;
    }

    private function resolvePlanningStock(int $productId, ?int $warehouseId, int $safetyStock): float
    {
        $freeStock = $this->resolveFreeStock($productId, $warehouseId);

        return round(max(0.0, $freeStock - $safetyStock), 6);
    }

    private function applyLeadTimeOffsets(array $requirements, Collection $products, string $referenceDate, string $planningBucket): array
    {
        return array_map(
            function (array $requirement) use ($products, $referenceDate, $planningBucket): array {
                $product = $products->get($requirement['product_id']);
                $leadTimeDays = (int) ($product?->lead_time_days ?? 0);
                $needByDate = Carbon::parse($requirement['need_by_date']);
                $releaseDate = $needByDate->copy()->subDays($leadTimeDays)->toDateString();

                return array_merge($requirement, [
                    'lead_time_days' => $leadTimeDays,
                    'planned_release_date' => $releaseDate,
                    'planned_release_bucket' => $this->bucketDate($releaseDate, $planningBucket),
                    'need_by_bucket' => $this->bucketDate($requirement['need_by_date'], $planningBucket),
                    'reference_date' => $referenceDate,
                ]);
            },
            $requirements
        );
    }

    private function buildTimeBuckets(array $requirements, string $planningBucket): array
    {
        $buckets = [];

        foreach ($requirements as $requirement) {
            $bucketKey = $requirement['planned_release_bucket'];

            if (! isset($buckets[$bucketKey])) {
                $buckets[$bucketKey] = [
                    'bucket_key' => $bucketKey,
                    'bucket_type' => $planningBucket,
                    'bucket_start_date' => $this->bucketStartDate($requirement['planned_release_date'], $planningBucket),
                    'bucket_end_date' => $this->bucketEndDate($requirement['planned_release_date'], $planningBucket),
                    'total_quantity' => 0.0,
                    'requirements' => [],
                ];
            }

            $buckets[$bucketKey]['total_quantity'] = round(
                (float) $buckets[$bucketKey]['total_quantity'] + (float) $requirement['quantity_net'],
                6
            );
            $buckets[$bucketKey]['requirements'][] = [
                'requirement_key' => $requirement['requirement_key'],
                'product_id' => (int) $requirement['product_id'],
                'need_by_date' => $requirement['need_by_date'],
                'need_by_bucket' => $requirement['need_by_bucket'],
                'planned_release_date' => $requirement['planned_release_date'],
                'planned_release_bucket' => $requirement['planned_release_bucket'],
                'quantity_net' => (float) $requirement['quantity_net'],
                'priority' => (int) $requirement['priority'],
            ];
        }

        ksort($buckets);

        return array_values($buckets);
    }

    private function priorityRuleComparator(string $priorityRule): callable
    {
        return match ($priorityRule) {
            'due_date_priority' => static function (array $left, array $right): int {
                return $left['need_by_date'] <=> $right['need_by_date']
                    ?: $left['priority'] <=> $right['priority']
                    ?: $left['product_id'] <=> $right['product_id']
                    ?: $left['requirement_key'] <=> $right['requirement_key'];
            },
            default => static function (array $left, array $right): int {
                return $left['priority'] <=> $right['priority']
                    ?: $left['need_by_date'] <=> $right['need_by_date']
                    ?: $left['product_id'] <=> $right['product_id']
                    ?: $left['requirement_key'] <=> $right['requirement_key'];
            },
        };
    }

    private function bucketDate(string $date, string $planningBucket): string
    {
        $carbon = Carbon::parse($date);

        return match ($planningBucket) {
            'weekly' => $carbon->startOfWeek(Carbon::MONDAY)->toDateString(),
            default => $carbon->toDateString(),
        };
    }

    private function bucketStartDate(string $date, string $planningBucket): string
    {
        $carbon = Carbon::parse($date);

        return match ($planningBucket) {
            'weekly' => $carbon->copy()->startOfWeek(Carbon::MONDAY)->toDateString(),
            default => $carbon->toDateString(),
        };
    }

    private function bucketEndDate(string $date, string $planningBucket): string
    {
        $carbon = Carbon::parse($date);

        return match ($planningBucket) {
            'weekly' => $carbon->copy()->endOfWeek(Carbon::SUNDAY)->toDateString(),
            default => $carbon->toDateString(),
        };
    }

    private function buildPlanFingerprint(array $payload): string
    {
        return sha1(json_encode([
            'reference_date' => Carbon::parse($payload['reference_date'] ?? now())->toDateString(),
            'planning_bucket' => (string) ($payload['planning_bucket'] ?? 'daily'),
            'priority_rule' => (string) ($payload['priority_rule'] ?? 'priority_due_date'),
            'recompute_scope' => $payload['recompute_scope'] ?? null,
            'demand_lines' => $this->normalizeDemandLinesForFingerprint($payload['demand_lines'] ?? []),
            'forecast_lines' => $this->normalizeDemandLinesForFingerprint($payload['forecast_lines'] ?? []),
        ], JSON_THROW_ON_ERROR));
    }

    private function buildSliceFingerprint(array $demandLine, string $referenceDate, array $payload): string
    {
        return sha1(json_encode([
            'reference_date' => $referenceDate,
            'planning_bucket' => (string) ($payload['planning_bucket'] ?? 'daily'),
            'priority_rule' => (string) ($payload['priority_rule'] ?? 'priority_due_date'),
            'product_id' => (int) $demandLine['product_id'],
            'warehouse_id' => isset($demandLine['warehouse_id']) ? (int) $demandLine['warehouse_id'] : null,
            'quantity' => round((float) $demandLine['quantity'], 6),
            'need_by_date' => Carbon::parse($demandLine['need_by_date'])->toDateString(),
            'bom_version_number' => isset($demandLine['bom_version_number']) ? (int) $demandLine['bom_version_number'] : null,
            'routing_version_id' => isset($demandLine['routing_version_id']) ? (int) $demandLine['routing_version_id'] : null,
            'priority' => (int) ($demandLine['priority'] ?? 1000),
            'source_type' => $demandLine['source_type'] ?? 'demand',
            'source_reference_id' => isset($demandLine['source_reference_id']) ? (int) $demandLine['source_reference_id'] : null,
            'source_reference_type' => $demandLine['source_reference_type'] ?? null,
        ], JSON_THROW_ON_ERROR));
    }

    private function sliceCacheKey(array $demandLine, string $referenceDate, array $payload): string
    {
        return sprintf('mrp:slice:%s', $this->buildSliceFingerprint($demandLine, $referenceDate, $payload));
    }

    private function normalizeDemandLinesForFingerprint(array $demandLines): array
    {
        return array_values(array_map(static function (array $demandLine): array {
            return [
                'product_id' => (int) $demandLine['product_id'],
                'warehouse_id' => isset($demandLine['warehouse_id']) ? (int) $demandLine['warehouse_id'] : null,
                'quantity' => round((float) $demandLine['quantity'], 6),
                'need_by_date' => Carbon::parse($demandLine['need_by_date'])->toDateString(),
                'bom_version_number' => isset($demandLine['bom_version_number']) ? (int) $demandLine['bom_version_number'] : null,
                'routing_version_id' => isset($demandLine['routing_version_id']) ? (int) $demandLine['routing_version_id'] : null,
                'priority' => (int) ($demandLine['priority'] ?? 1000),
                'source_type' => $demandLine['source_type'] ?? 'demand',
                'source_reference_id' => isset($demandLine['source_reference_id']) ? (int) $demandLine['source_reference_id'] : null,
                'source_reference_type' => $demandLine['source_reference_type'] ?? null,
            ];
        }, $demandLines));
    }

    private function buildPurchaseSuggestions(array $requirements, Collection $products, ?int $createdBy, string $referenceDate): array
    {
        $suggestions = [];

        foreach ($requirements as $requirement) {
            $product = $products->get($requirement['product_id']);

            if (! $product || ! in_array($product->product_type, self::PURCHASE_PRODUCT_TYPES, true)) {
                continue;
            }

            $suggestions[] = [
                'suggestion_type' => 'PURCHASE',
                'product_id' => (int) $requirement['product_id'],
                'product_sku' => $product->sku,
                'product_description' => $product->description,
                'warehouse_id' => $requirement['warehouse_id'],
                'quantity' => (float) $requirement['quantity_net'],
                'need_by_date' => $requirement['need_by_date'],
                'order_date' => $requirement['planned_release_date'],
                'lead_time_days' => (int) $requirement['lead_time_days'],
                'source_requirement_key' => $requirement['requirement_key'],
                'source_keys' => $requirement['source_keys'],
                'created_by' => $createdBy,
                'reference_date' => $referenceDate,
                'purchase_order_payload' => [
                    'product_id' => (int) $requirement['product_id'],
                    'warehouse_id' => $requirement['warehouse_id'],
                    'quantity' => (float) $requirement['quantity_net'],
                    'need_by_date' => $requirement['need_by_date'],
                    'order_date' => $requirement['planned_release_date'],
                    'source_reference_id' => $requirement['source_reference_id'],
                    'source_reference_type' => $requirement['source_reference_type'],
                    'metadata' => $requirement['metadata'],
                ],
            ];
        }

        return $suggestions;
    }

    private function buildProductionSuggestions(array $requirements, Collection $products, ?int $createdBy, string $referenceDate): array
    {
        $suggestions = [];

        foreach ($requirements as $requirement) {
            $product = $products->get($requirement['product_id']);

            if (! $product || ! in_array($product->product_type, self::PRODUCTION_PRODUCT_TYPES, true)) {
                continue;
            }

            $suggestions[] = [
                'suggestion_type' => 'PRODUCTION',
                'product_id' => (int) $requirement['product_id'],
                'product_sku' => $product->sku,
                'product_description' => $product->description,
                'warehouse_id' => $requirement['warehouse_id'],
                'quantity' => (float) $requirement['quantity_net'],
                'need_by_date' => $requirement['need_by_date'],
                'release_date' => $requirement['planned_release_date'],
                'lead_time_days' => (int) $requirement['lead_time_days'],
                'bom_version_number' => $requirement['bom_version_number'],
                'routing_version_id' => $requirement['routing_version_id'],
                'source_requirement_key' => $requirement['requirement_key'],
                'source_keys' => $requirement['source_keys'],
                'created_by' => $createdBy,
                'reference_date' => $referenceDate,
                'production_order_payload' => [
                    'product_id' => (int) $requirement['product_id'],
                    'warehouse_id' => $requirement['warehouse_id'],
                    'quantity_planned' => (float) $requirement['quantity_net'],
                    'quantity_scrapped' => 0,
                    'bom_version_number' => $requirement['bom_version_number'],
                    'routing_version_id' => $requirement['routing_version_id'],
                    'reference_date' => $referenceDate,
                    'scheduled_start_date' => $requirement['planned_release_date'],
                    'scheduled_end_date' => $requirement['need_by_date'],
                    'source_reference_id' => $requirement['source_reference_id'],
                    'source_reference_type' => $requirement['source_reference_type'],
                    'metadata' => $requirement['metadata'],
                ],
            ];
        }

        return $suggestions;
    }
}