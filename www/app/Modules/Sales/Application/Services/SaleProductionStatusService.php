<?php

declare(strict_types=1);

namespace App\Modules\Sales\Application\Services;

use App\Models\SaaS\AuditLog;
use App\Modules\Bom\Infrastructure\Persistence\Models\BomHeader;
use App\Modules\Identity\Infrastructure\Persistence\Models\User;
use App\Modules\Inventory\Infrastructure\Persistence\Models\InventoryReservation;
use App\Modules\Product\Infrastructure\Persistence\Models\Product;
use App\Modules\Production\Infrastructure\Persistence\Models\ProductionOrder;
use App\Modules\Purchasing\Infrastructure\Persistence\Models\PurchaseOrder;
use App\Modules\Purchasing\Infrastructure\Persistence\Models\PurchaseOrderLine;
use App\Modules\Purchasing\Infrastructure\Persistence\Models\PurchaseRequisition;
use App\Modules\Purchasing\Infrastructure\Persistence\Models\SupplierProduct;
use App\Modules\Sales\Infrastructure\Persistence\Models\Sale;
use App\Modules\Sales\Infrastructure\Persistence\Models\SaleLine;
use App\Modules\Scheduling\Infrastructure\Persistence\Models\WorkCenterHourRate;
use Illuminate\Support\Collection;

final class SaleProductionStatusService
{
    /** @var array<int, float|null> */
    private array $unitCosts = [];

    /** @var array<string, float|null> */
    private array $hourRates = [];

    /** @var array<int, array{amount: float|null, source: string|null, reference: string|null}> */
    private array $unitCostEvidence = [];

    /** @var array<string, array{amount: float|null, source: string|null, reference: string|null}> */
    private array $hourRateEvidence = [];

    /** @var array<int, Product> */
    private array $treeProducts = [];

    /** @var array<string, BomHeader|null> */
    private array $treeBoms = [];

    public function __construct(
        private readonly SaleMaterialRequirementService $materialRequirementService
    ) {}

    /** @return array<string, mixed> */
    public function analyze(Sale $sale): array
    {
        $this->unitCosts = [];
        $this->hourRates = [];
        $this->unitCostEvidence = [];
        $this->hourRateEvidence = [];
        $this->treeProducts = [];
        $this->treeBoms = [];
        $sale->loadMissing(['customer:id,name', 'lines.product.unit']);

        $orders = ProductionOrder::query()
            ->where('source_reference_type', 'sale')
            ->where('source_reference_id', $sale->id)
            ->where('status', '!=', 'CANCELLED')
            ->with([
                'product.unit:id,code',
                'warehouse:id,code,name',
                'operations.workCenter:id,code,name,resource_type',
                'outputs',
                'materialConsumptions.product:id,sku,description,product_type,unit_id',
                'materialConsumptions.product.unit:id,code',
                'snapshot.bomSnapshot.items',
            ])
            ->orderBy('id')
            ->get();

        $purchaseOrders = $this->purchaseOrders($sale);
        $purchasingByProduct = $this->purchasingByProduct($purchaseOrders);

        $items = $sale->lines->map(function (SaleLine $line) use ($sale, $orders, $purchasingByProduct): array {
            $lineOrders = $orders->filter(fn (ProductionOrder $order): bool => $this->belongsToLine($order, $line));
            $materials = $this->materialRequirementService->analyzeLine($sale, $line);
            $orderProductIds = $lineOrders->pluck('product_id')->map(static fn ($id): int => (int) $id)->unique();
            $orderRows = $lineOrders
                ->map(fn (ProductionOrder $order): array => $this->orderRow($order, $sale, $orderProductIds))
                ->sortBy([['level', 'asc'], ['id', 'asc']])
                ->values();
            $forecasts = $this->forecastRows($line, $materials, $lineOrders);
            $estimatedMaterials = $this->estimatedMaterialCost($materials);
            $enrichedMaterials = collect($materials['materials'])
                ->map(fn (array $material): array => $this->enrichMaterialQuantities($material, $lineOrders, $purchasingByProduct))
                ->values();

            $costs = [
                'estimated_material' => $estimatedMaterials['amount'],
                'estimated_production' => round((float) $orderRows->sum('costs.estimated_production'), 2),
                'estimated_labor' => round((float) $orderRows->sum('costs.estimated_labor'), 2),
                'estimated_machine' => round((float) $orderRows->sum('costs.estimated_machine'), 2),
                'actual_material' => round((float) $orderRows->sum('costs.actual_material'), 2),
                'actual_production' => round((float) $orderRows->sum('costs.actual_production'), 2),
                'actual_labor' => round((float) $orderRows->sum('costs.actual_labor'), 2),
                'actual_machine' => round((float) $orderRows->sum('costs.actual_machine'), 2),
                'actual_scrap' => round((float) $orderRows->sum('costs.actual_scrap'), 2),
                'estimated_incomplete' => $estimatedMaterials['incomplete']
                    || $orderRows->contains('costs.estimated_incomplete', true)
                    || $forecasts->isNotEmpty(),
                'actual_incomplete' => $orderRows->contains('costs.actual_incomplete', true),
            ];
            $costs['estimated_total'] = round($costs['estimated_material'] + $costs['estimated_production'], 2);
            $costs['actual_total'] = round($costs['actual_material'] + $costs['actual_production'] + $costs['actual_scrap'], 2);
            $costs['estimated_per_unit'] = (float) $line->quantity > 0 ? round($costs['estimated_total'] / (float) $line->quantity, 2) : null;
            $costs['actual_per_unit'] = (float) $line->quantity > 0 ? round($costs['actual_total'] / (float) $line->quantity, 2) : null;
            $costs['variance'] = round($costs['actual_total'] - $costs['estimated_total'], 2);
            $costs['variance_percent'] = $costs['estimated_total'] > 0 ? round(($costs['variance'] / $costs['estimated_total']) * 100, 1) : null;
            $costs['evidence'] = [
                'materials' => $enrichedMaterials->map(fn (array $material): array => [
                    'sku' => $material['sku'],
                    'unit_cost' => $material['unit_cost'],
                    'source' => $material['cost_source'],
                    'reference' => $material['cost_reference'],
                ])->all(),
                'rates' => $orderRows->flatMap(static fn (array $row): array => $row['costs']['rate_evidence'])->unique('key')->values()->all(),
            ];

            $allProductionRows = $orderRows->concat($forecasts);
            $finishedProductCoverage = $materials['finished_products'][0] ?? null;

            return [
                'line_id' => (int) $line->id,
                'product_id' => (int) $line->product_id,
                'sku' => (string) ($line->product?->sku ?? '—'),
                'description' => (string) ($line->product?->description ?? '—'),
                'unit' => (string) ($line->product?->unit?->code ?? ''),
                'quantity' => round((float) $line->quantity, 6),
                'coverage' => $finishedProductCoverage,
                'production_status' => $this->itemStatus($materials, $orderRows, $forecasts),
                'production_orders' => $orderRows->all(),
                'forecasts' => $forecasts->all(),
                'materials' => $enrichedMaterials->all(),
                'tree' => $this->buildProductTree($line, $materials, $orderRows, $forecasts, $purchasingByProduct, $sale->sale_date?->toDateString() ?? now()->toDateString()),
                'missing_boms' => $materials['missing_boms'],
                'cycles' => $materials['cycles'],
                'counts' => [
                    'completed' => $allProductionRows->where('status_group', 'completed')->count(),
                    'in_progress' => $allProductionRows->where('status_group', 'in_progress')->count(),
                    'planned' => $allProductionRows->where('status_group', 'planned')->count(),
                    'forecast' => $allProductionRows->where('status_group', 'forecast')->count(),
                    'materials_short' => $enrichedMaterials->where('net_shortage', '>', 0)->count(),
                    'materials_in_stock' => $materials['materials_in_stock_count'],
                    'to_buy' => $enrichedMaterials->filter(static fn (array $material): bool => $material['recommended_action'] === 'BUY' && (float) $material['net_shortage'] > 0)->count(),
                    'to_produce' => $materials['production_items_count'],
                ],
                'costs' => $costs,
            ];
        })->values();

        $counts = [
            'completed' => $items->sum('counts.completed'),
            'in_progress' => $items->sum('counts.in_progress'),
            'planned' => $items->sum('counts.planned'),
            'forecast' => $items->sum('counts.forecast'),
            'materials_short' => $items->sum('counts.materials_short'),
            'overdue' => $items->sum(static fn (array $item): int => collect($item['production_orders'])->where('is_overdue', true)->count()),
        ];
        $realOrderRows = $items->flatMap(static fn (array $item): array => $item['production_orders']);
        $productionRows = $items->flatMap(
            static fn (array $item): array => array_merge($item['production_orders'], $item['forecasts'])
        );
        $allCoveredWithoutProduction = $items->isNotEmpty() && $items->every(
            static fn (array $item): bool => (float) ($item['coverage']['quantity_to_produce'] ?? 0) <= 0
        );
        $progressPercent = $productionRows->isNotEmpty()
            ? round((float) $productionRows->avg('progress_percent'), 1)
            : ($allCoveredWithoutProduction ? 100.0 : 0.0);
        $scheduledEnds = $realOrderRows->pluck('scheduled_end')->filter()->sort()->values();
        $scheduledStarts = $realOrderRows->pluck('scheduled_start')->filter()->sort()->values();
        $purchasePromisedDates = $purchaseOrders
            ->flatMap(static fn (PurchaseOrder $order): array => $order->lines
                ->map(static fn (PurchaseOrderLine $line): ?string => $line->promised_date?->toDateString() ?? $order->expected_delivery_date?->toDateString())
                ->all())
            ->filter()
            ->sort()
            ->values();
        $scheduleIncomplete = $counts['forecast'] > 0 || $realOrderRows->contains(
            static fn (array $order): bool => $order['status_group'] !== 'completed' && $order['scheduled_end'] === null
        );
        $estimatedTotal = round((float) $items->sum('costs.estimated_total'), 2);
        $actualTotal = round((float) $items->sum('costs.actual_total'), 2);
        $costVariance = round($actualTotal - $estimatedTotal, 2);
        $alerts = $this->alerts($items, $counts, $scheduleIncomplete);
        $readiness = $this->readiness($items, $counts, $scheduleIncomplete);
        $promisedDate = data_get($sale->metadata, 'production_tracking.promised_date');
        $projectedCompletion = collect([$scheduledEnds->last(), $purchasePromisedDates->last()])->filter()->sort()->last();
        $daysLate = $promisedDate !== null
            ? max(0, today()->parse($projectedCompletion ?? today())->startOfDay()->diffInDays(today()->parse($promisedDate)->startOfDay(), false) * -1)
            : 0;
        if ($daysLate > 0 && $readiness !== 'blocked_materials') {
            $readiness = 'at_risk';
        }
        $criticalOrder = $realOrderRows->filter(static fn (array $order): bool => $order['scheduled_end'] !== null)->sortByDesc('scheduled_end')->first();
        $limitingMaterial = $items->flatMap(static fn (array $item): array => $item['materials'])
            ->filter(static fn (array $material): bool => $material['recommended_action'] === 'BUY'
                && ((float) $material['net_shortage'] > 0 || (float) $material['in_purchase'] > 0))
            ->sortByDesc(static fn (array $material): string => (string) ($material['latest_promised_date'] ?? '9999-12-31'))
            ->first();
        $salesAmount = round((float) $sale->amount_cents / 100, 2);
        $margin = round($salesAmount - $estimatedTotal, 2);

        return [
            'sale_id' => (int) $sale->id,
            'items' => $items->all(),
            'readiness' => $readiness,
            'progress_percent' => $progressPercent,
            'projected_completion' => $projectedCompletion,
            'schedule_incomplete' => $scheduleIncomplete,
            'schedule' => [
                'promised_date' => $promisedDate,
                'planned_start' => $scheduledStarts->first(),
                'planned_end' => $scheduledEnds->last(),
                'projected_completion' => $projectedCompletion,
                'days_late' => $daysLate,
                'critical_path' => $criticalOrder !== null ? [
                    'order_id' => $criticalOrder['id'],
                    'order_number' => $criticalOrder['order_number'],
                    'product' => $criticalOrder['sku'],
                    'date' => $criticalOrder['scheduled_end'],
                ] : null,
                'limiting_material' => $limitingMaterial !== null ? [
                    'product_id' => $limitingMaterial['product_id'],
                    'sku' => $limitingMaterial['sku'],
                    'quantity' => $limitingMaterial['net_shortage'],
                    'date' => $limitingMaterial['latest_promised_date'],
                ] : null,
            ],
            'last_updated_at' => now()->toIso8601String(),
            'alerts' => $alerts,
            'timeline' => $this->timeline($sale, $orders, $purchaseOrders),
            'tracking' => $this->tracking($sale),
            'history' => $this->history($sale),
            'counts' => $counts,
            'costs' => [
                'estimated_total' => $estimatedTotal,
                'actual_total' => $actualTotal,
                'variance' => $costVariance,
                'variance_percent' => $estimatedTotal > 0 ? round(($costVariance / $estimatedTotal) * 100, 1) : null,
                'estimated_material' => round((float) $items->sum('costs.estimated_material'), 2),
                'estimated_labor' => round((float) $items->sum('costs.estimated_labor'), 2),
                'estimated_machine' => round((float) $items->sum('costs.estimated_machine'), 2),
                'actual_material' => round((float) $items->sum('costs.actual_material'), 2),
                'actual_labor' => round((float) $items->sum('costs.actual_labor'), 2),
                'actual_machine' => round((float) $items->sum('costs.actual_machine'), 2),
                'actual_scrap' => round((float) $items->sum('costs.actual_scrap'), 2),
                'sales_amount' => $salesAmount,
                'estimated_margin' => $margin,
                'estimated_margin_percent' => $salesAmount > 0 ? round(($margin / $salesAmount) * 100, 1) : null,
                'estimated_incomplete' => $items->contains('costs.estimated_incomplete', true),
                'actual_incomplete' => $items->contains('costs.actual_incomplete', true),
            ],
        ];
    }

    private function belongsToLine(ProductionOrder $order, SaleLine $line): bool
    {
        $metadata = $order->metadata ?? [];
        $saleLineId = (int) ($metadata['sale_line_id'] ?? 0);

        if ($saleLineId > 0) {
            return $saleLineId === (int) $line->id;
        }

        $rootProductId = (int) ($metadata['root_product_id'] ?? 0);

        return $rootProductId > 0
            ? $rootProductId === (int) $line->product_id
            : (int) $order->product_id === (int) $line->product_id;
    }

    /** @param Collection<int, int> $orderProductIds */
    private function orderRow(ProductionOrder $order, Sale $sale, Collection $orderProductIds): array
    {
        $estimatedProduction = 0.0;
        $actualProduction = 0.0;
        $estimatedLabor = 0.0;
        $estimatedMachine = 0.0;
        $actualLabor = 0.0;
        $actualMachine = 0.0;
        $actualScrap = 0.0;
        $estimatedIncomplete = false;
        $actualIncomplete = false;
        $rateEvidence = collect();

        foreach ($order->operations as $operation) {
            $estimatedMinutes = (float) $operation->productive_time_minutes;
            $actualMinutes = (float) $operation->actual_productive_minutes;
            $rateDate = $operation->planned_start_at?->toDateString()
                ?? $order->scheduled_start_date?->toDateString()
                ?? $sale->sale_date?->toDateString()
                ?? now()->toDateString();
            $rate = $this->hourRate((int) $operation->work_center_id, $rateDate);
            $rateDetails = $this->hourRateDetails((int) $operation->work_center_id, $rateDate);
            $isMachine = (string) $operation->workCenter?->resource_type === 'MACHINE';

            $rateEvidence->push([
                'key' => (int) $operation->work_center_id.'|'.$rateDate,
                'work_center' => $operation->workCenter?->code,
                'date' => $rateDate,
                'rate' => $rateDetails['amount'],
                'source' => $rateDetails['source'],
                'reference' => $rateDetails['reference'],
            ]);

            if ($estimatedMinutes > 0 && $rate === null) {
                $estimatedIncomplete = true;
            } else {
                $amount = ($estimatedMinutes / 60) * (float) $rate;
                $estimatedProduction += $amount;
                $isMachine ? $estimatedMachine += $amount : $estimatedLabor += $amount;
            }

            if ($actualMinutes > 0 && $rate === null) {
                $actualIncomplete = true;
            } else {
                $amount = ($actualMinutes / 60) * (float) $rate;
                $actualProduction += $amount;
                $isMachine ? $actualMachine += $amount : $actualLabor += $amount;
            }
        }

        if ($order->operations->isEmpty()) {
            $estimatedIncomplete = (float) $order->quantity_planned > 0;

            foreach ($order->outputs as $output) {
                $minutes = (float) $output->setup_time_minutes + (float) $output->process_time_minutes;
                $workCenterId = (int) ($output->work_center_id ?? 0);

                if ($minutes <= 0) {
                    continue;
                }

                $rate = $workCenterId > 0
                    ? $this->hourRate($workCenterId, $output->reported_at?->toDateString() ?? now()->toDateString())
                    : null;

                if ($rate === null) {
                    $actualIncomplete = true;
                } else {
                    $amount = ($minutes / 60) * $rate;
                    $actualProduction += $amount;
                    $actualMachine += $amount;
                }
            }
        }

        $actualMaterial = 0.0;

        foreach ($order->materialConsumptions->whereNull('reversed_by_movement_id') as $consumption) {
            $product = $consumption->product;

            if ($product !== null
                && in_array((string) $product->product_type, ['FG', 'WIP'], true)
                && $orderProductIds->contains((int) $product->id)) {
                continue;
            }

            $quantity = (float) $consumption->quantity_consumed;
            $unitCost = $this->unitCost((int) $consumption->product_id);

            if ($quantity > 0 && $unitCost === null) {
                $actualIncomplete = true;

                continue;
            }

            $scrappedQuantity = (float) $consumption->quantity_scrapped;
            $actualMaterial += max(0.0, $quantity - $scrappedQuantity) * (float) $unitCost;

            if ($scrappedQuantity > 0 && $unitCost === null) {
                $actualIncomplete = true;
            } else {
                $actualScrap += $scrappedQuantity * (float) $unitCost;
            }
        }

        $plannedConsumptionByProduct = collect($order->snapshot?->bomSnapshot?->items ?? [])
            ->groupBy('component_product_id')
            ->map(static fn ($rows): float => round((float) $rows->sum('quantity_required'), 6));
        $actualConsumptionByProduct = $order->materialConsumptions
            ->whereNull('reversed_by_movement_id')
            ->groupBy('product_id')
            ->map(static fn ($rows): float => round((float) $rows->sum('quantity_consumed'), 6));
        $excessConsumptionCount = $actualConsumptionByProduct->filter(
            static fn (float $quantity, int $productId): bool => $quantity > (float) ($plannedConsumptionByProduct[$productId] ?? 0) + 0.000001
        )->count();
        $scrapTarget = (float) ($order->snapshot?->quantity_scrapped_target ?? 0);

        return [
            'id' => (int) $order->id,
            'order_number' => (string) $order->order_number,
            'product_id' => (int) $order->product_id,
            'sku' => (string) ($order->product?->sku ?? '—'),
            'description' => (string) ($order->product?->description ?? '—'),
            'unit' => (string) ($order->product?->unit?->code ?? ''),
            'level' => (int) (($order->metadata ?? [])['dependency_level'] ?? 0),
            'status' => (string) $order->status,
            'status_group' => $this->statusGroup((string) $order->status),
            'quantity_planned' => round((float) $order->quantity_planned, 6),
            'quantity_produced' => round((float) $order->quantity_produced, 6),
            'progress_percent' => (float) $order->quantity_planned > 0
                ? min(100.0, round(((float) $order->quantity_produced / (float) $order->quantity_planned) * 100, 1))
                : 0.0,
            'scheduled_start' => $order->scheduled_start_date?->toDateString(),
            'scheduled_end' => $order->scheduled_end_date?->toDateString(),
            'is_overdue' => $order->status !== 'COMPLETED'
                && $order->scheduled_end_date !== null
                && $order->scheduled_end_date->isBefore(today()),
            'warehouse' => $order->warehouse?->code,
            'operations_count' => $order->operations->count(),
            'missing_routing' => $order->operations->isEmpty(),
            'excess_consumption_count' => $excessConsumptionCount,
            'scrap_above_limit' => (float) $order->quantity_scrapped > $scrapTarget + 0.000001,
            'days_overdue' => $order->status !== 'COMPLETED' && $order->scheduled_end_date?->isBefore(today())
                ? $order->scheduled_end_date->diffInDays(today())
                : 0,
            'costs' => [
                'estimated_production' => round($estimatedProduction, 2),
                'estimated_labor' => round($estimatedLabor, 2),
                'estimated_machine' => round($estimatedMachine, 2),
                'actual_material' => round($actualMaterial, 2),
                'actual_production' => round($actualProduction, 2),
                'actual_labor' => round($actualLabor, 2),
                'actual_machine' => round($actualMachine, 2),
                'actual_scrap' => round($actualScrap, 2),
                'estimated_incomplete' => $estimatedIncomplete,
                'actual_incomplete' => $actualIncomplete,
                'rate_evidence' => $rateEvidence->unique('key')->values()->all(),
            ],
        ];
    }

    /** @return Collection<int, array<string, mixed>> */
    private function forecastRows(SaleLine $line, array $analysis, Collection $orders): Collection
    {
        $requirements = collect();
        $finished = collect($analysis['finished_products'])->firstWhere('product_id', (int) $line->product_id);

        if (($finished['quantity_to_produce'] ?? 0) > 0) {
            $requirements->push([
                'product_id' => (int) $line->product_id,
                'sku' => (string) ($line->product?->sku ?? '—'),
                'description' => (string) ($line->product?->description ?? '—'),
                'unit' => (string) ($line->product?->unit?->code ?? ''),
                'level' => 0,
                'quantity_planned' => (float) $finished['quantity_to_produce'],
            ]);
        }

        foreach ($analysis['materials'] as $material) {
            if ($material['recommended_action'] !== 'PRODUCE' || (float) $material['shortage_quantity'] <= 0) {
                continue;
            }

            $requirements->push([
                'product_id' => (int) $material['product_id'],
                'sku' => (string) $material['sku'],
                'description' => (string) $material['description'],
                'unit' => (string) ($material['unit'] ?? ''),
                'level' => (int) $material['level'],
                'quantity_planned' => (float) $material['shortage_quantity'],
            ]);
        }

        return $requirements
            ->reject(static fn (array $requirement): bool => $orders->contains(
                static fn (ProductionOrder $order): bool => (int) $order->product_id === $requirement['product_id']
                    && $order->status !== 'CANCELLED'
            ))
            ->map(static fn (array $requirement): array => array_merge($requirement, [
                'id' => null,
                'order_number' => null,
                'status' => 'FORECAST',
                'status_group' => 'forecast',
                'quantity_produced' => 0.0,
                'progress_percent' => 0.0,
                'scheduled_start' => null,
                'scheduled_end' => null,
                'is_overdue' => false,
                'warehouse' => null,
                'operations_count' => 0,
                'missing_routing' => false,
                'excess_consumption_count' => 0,
                'scrap_above_limit' => false,
                'days_overdue' => 0,
                'costs' => null,
            ]))
            ->sortBy([['level', 'asc'], ['sku', 'asc']])
            ->values();
    }

    /** @return array{amount: float, incomplete: bool} */
    private function estimatedMaterialCost(array $analysis): array
    {
        $amount = 0.0;
        $incomplete = $analysis['missing_boms'] !== [] || $analysis['cycles'] !== [];

        foreach ($analysis['materials'] as $material) {
            if ($material['recommended_action'] !== 'BUY') {
                continue;
            }

            $quantity = (float) $material['required_quantity'];
            $unitCost = $this->unitCost((int) $material['product_id']);

            if ($quantity > 0 && $unitCost === null) {
                $incomplete = true;

                continue;
            }

            $amount += $quantity * (float) $unitCost;
        }

        return ['amount' => round($amount, 2), 'incomplete' => $incomplete];
    }

    private function unitCost(int $productId): ?float
    {
        if (array_key_exists($productId, $this->unitCosts)) {
            return $this->unitCosts[$productId];
        }

        return $this->unitCosts[$productId] = $this->unitCostDetails($productId)['amount'];
    }

    /** @return array{amount: float|null, source: string|null, reference: string|null} */
    private function unitCostDetails(int $productId): array
    {
        if (isset($this->unitCostEvidence[$productId])) {
            return $this->unitCostEvidence[$productId];
        }

        $purchaseLine = PurchaseOrderLine::query()
            ->where('product_id', $productId)
            ->whereNotNull('unit_price')
            ->where('unit_price', '>', 0)
            ->whereHas('purchaseOrder', static fn ($query) => $query->where('status', '!=', 'CANCELLED'))
            ->with('purchaseOrder:id,purchase_order_number')
            ->latest('id')
            ->first(['id', 'purchase_order_id', 'unit_price']);

        $supplierRule = SupplierProduct::query()
            ->where('product_id', $productId)
            ->where('is_active', true)
            ->whereNotNull('unit_price')
            ->where('unit_price', '>', 0)
            ->with('supplier:id,code')
            ->orderByDesc('is_preferred')
            ->orderByDesc('id')
            ->first(['id', 'supplier_id', 'unit_price']);

        if ($purchaseLine instanceof PurchaseOrderLine) {
            return $this->unitCostEvidence[$productId] = [
                'amount' => (float) $purchaseLine->unit_price,
                'source' => 'purchase_order',
                'reference' => $purchaseLine->purchaseOrder?->purchase_order_number,
            ];
        }

        return $this->unitCostEvidence[$productId] = [
            'amount' => $supplierRule !== null ? (float) $supplierRule->unit_price : null,
            'source' => $supplierRule !== null ? 'supplier_catalog' : null,
            'reference' => $supplierRule?->supplier?->code,
        ];
    }

    private function hourRate(int $workCenterId, string $date): ?float
    {
        $key = $workCenterId.'|'.$date;

        if (array_key_exists($key, $this->hourRates)) {
            return $this->hourRates[$key];
        }

        return $this->hourRates[$key] = $this->hourRateDetails($workCenterId, $date)['amount'];
    }

    /** @return array{amount: float|null, source: string|null, reference: string|null} */
    private function hourRateDetails(int $workCenterId, string $date): array
    {
        $key = $workCenterId.'|'.$date;

        if (isset($this->hourRateEvidence[$key])) {
            return $this->hourRateEvidence[$key];
        }

        $rate = WorkCenterHourRate::query()
            ->where('work_center_id', $workCenterId)
            ->where('status', 'ACTIVE')
            ->whereDate('effective_from', '<=', $date)
            ->where(function ($query) use ($date): void {
                $query->whereNull('effective_to')->orWhereDate('effective_to', '>=', $date);
            })
            ->with('workCenter:id,code')
            ->orderByDesc('effective_from')
            ->first(['id', 'work_center_id', 'hourly_rate', 'effective_from']);

        return $this->hourRateEvidence[$key] = [
            'amount' => $rate !== null ? (float) $rate->hourly_rate : null,
            'source' => $rate !== null ? 'work_center_rate' : null,
            'reference' => $rate !== null ? ($rate->workCenter?->code.' · '.$rate->effective_from?->toDateString()) : null,
        ];
    }

    /** @return Collection<int, PurchaseOrder> */
    private function purchaseOrders(Sale $sale): Collection
    {
        $requisitionIds = PurchaseRequisition::query()
            ->where('source_reference_type', 'sale')
            ->where('source_reference_id', $sale->id)
            ->pluck('id');

        if ($requisitionIds->isEmpty()) {
            return collect();
        }

        return PurchaseOrder::query()
            ->whereIn('purchase_requisition_id', $requisitionIds)
            ->where('status', '!=', 'CANCELLED')
            ->with(['supplier:id,code,name', 'lines.product:id,sku,description,unit_id', 'lines.product.unit:id,code'])
            ->orderBy('id')
            ->get();
    }

    /** @param Collection<int, PurchaseOrder> $purchaseOrders */
    private function purchasingByProduct(Collection $purchaseOrders): Collection
    {
        return $purchaseOrders
            ->flatMap(function (PurchaseOrder $order): array {
                return $order->lines->map(static fn (PurchaseOrderLine $line): array => [
                    'product_id' => (int) $line->product_id,
                    'ordered' => (float) $line->quantity_ordered,
                    'received' => (float) $line->quantity_received,
                    'open' => max(0.0, round((float) $line->quantity_ordered - (float) $line->quantity_received, 6)),
                    'promised_date' => $line->promised_date?->toDateString() ?? $order->expected_delivery_date?->toDateString(),
                    'order' => [
                        'id' => (int) $order->id,
                        'number' => (string) $order->purchase_order_number,
                        'status' => (string) $order->status,
                        'supplier' => $order->supplier?->name,
                    ],
                ])->all();
            })
            ->groupBy('product_id')
            ->map(static fn (Collection $rows): array => [
                'ordered' => round((float) $rows->sum('ordered'), 6),
                'received' => round((float) $rows->sum('received'), 6),
                'open' => round((float) $rows->sum('open'), 6),
                'latest_promised_date' => $rows->pluck('promised_date')->filter()->sort()->last(),
                'orders' => $rows->pluck('order')->unique('id')->values()->all(),
            ]);
    }

    private function enrichMaterialQuantities(array $material, Collection $orders, Collection $purchasingByProduct): array
    {
        $productId = (int) $material['product_id'];
        $productionOrders = $orders->where('product_id', $productId);
        $inProduction = round((float) $productionOrders->sum(
            static fn (ProductionOrder $order): float => max(0.0, (float) $order->quantity_planned - (float) $order->quantity_produced)
        ), 6);
        $purchasing = $purchasingByProduct->get($productId, [
            'ordered' => 0.0,
            'received' => 0.0,
            'open' => 0.0,
            'latest_promised_date' => null,
            'orders' => [],
        ]);
        $cost = $this->unitCostDetails($productId);

        return array_merge($material, [
            'reserved_quantity' => round((float) $material['linked_quantity'], 6),
            'available_quantity' => round((float) $material['available_to_link'], 6),
            'in_production' => $inProduction,
            'in_purchase' => round((float) $purchasing['open'], 6),
            'received_quantity' => round((float) $purchasing['received'], 6),
            'net_shortage' => max(0.0, round((float) $material['shortage_quantity'] - $inProduction - (float) $purchasing['open'], 6)),
            'latest_promised_date' => $purchasing['latest_promised_date'],
            'purchase_orders' => $purchasing['orders'],
            'unit_cost' => $cost['amount'],
            'cost_source' => $cost['source'],
            'cost_reference' => $cost['reference'],
        ]);
    }

    private function buildProductTree(
        SaleLine $line,
        array $analysis,
        Collection $orderRows,
        Collection $forecasts,
        Collection $purchasingByProduct,
        string $referenceDate
    ): array {
        $materials = collect($analysis['materials'])->keyBy('product_id');
        $rows = $orderRows->concat($forecasts)->groupBy('product_id');
        $coverage = collect($analysis['finished_products'])->firstWhere('product_id', (int) $line->product_id);

        return $this->buildProductTreeNode(
            productId: (int) $line->product_id,
            requiredQuantity: (float) $line->quantity,
            level: 0,
            path: [],
            referenceDate: $referenceDate,
            materials: $materials,
            rows: $rows,
            purchasingByProduct: $purchasingByProduct,
            rootCoverage: $coverage,
        );
    }

    private function buildProductTreeNode(
        int $productId,
        float $requiredQuantity,
        int $level,
        array $path,
        string $referenceDate,
        Collection $materials,
        Collection $rows,
        Collection $purchasingByProduct,
        ?array $rootCoverage = null,
    ): array {
        $product = $this->treeProducts[$productId] ??= Product::query()->with('unit:id,code')->findOrFail($productId);
        $material = $materials->get($productId, []);
        $productRows = $rows->get($productId, collect());
        $purchasing = $purchasingByProduct->get($productId, ['open' => 0.0, 'received' => 0.0, 'orders' => []]);
        $reserved = (float) ($rootCoverage['linked_quantity'] ?? $material['linked_quantity'] ?? 0);
        $available = (float) ($rootCoverage['available_to_link'] ?? $material['available_to_link'] ?? 0);
        $inProduction = round((float) $productRows->whereNotNull('id')->sum(
            static fn (array $row): float => max(0.0, (float) $row['quantity_planned'] - (float) $row['quantity_produced'])
        ), 6);
        $produced = round((float) $productRows->whereNotNull('id')->sum('quantity_produced'), 6);
        $inPurchase = (float) ($purchasing['open'] ?? 0);
        $received = round((float) ($purchasing['received'] ?? 0) + $produced, 6);
        $netShortage = max(0.0, round($requiredQuantity - $reserved - $available - $inProduction - $received - $inPurchase, 6));
        $isCycle = in_array($productId, $path, true);
        $children = [];

        if (! $isCycle && $level < 20) {
            $bomKey = $productId.'|'.$referenceDate;
            $bom = $this->treeBoms[$bomKey] ??= BomHeader::query()
                ->where('product_id', $productId)
                ->where('status', 'APPROVED')
                ->whereDate('effective_from', '<=', $referenceDate)
                ->where(static fn ($query) => $query->whereNull('effective_to')->orWhereDate('effective_to', '>=', $referenceDate))
                ->with('items.componentProduct:id,sku,description,product_type,unit_id')
                ->orderByDesc('effective_from')
                ->orderByDesc('version_number')
                ->first();

            foreach ($bom?->items ?? [] as $bomItem) {
                $children[] = $this->buildProductTreeNode(
                    productId: (int) $bomItem->component_product_id,
                    requiredQuantity: round($requiredQuantity * (float) $bomItem->quantity_per, 6),
                    level: $level + 1,
                    path: array_merge($path, [$productId]),
                    referenceDate: $referenceDate,
                    materials: $materials,
                    rows: $rows,
                    purchasingByProduct: $purchasingByProduct,
                );
            }
        }

        $primaryOrder = $productRows->sortByDesc(static fn (array $row): int => $row['id'] !== null ? 1 : 0)->first();

        return [
            'product_id' => $productId,
            'sku' => (string) $product->sku,
            'description' => (string) $product->description,
            'unit' => (string) ($product->unit?->code ?? ''),
            'level' => $level,
            'required_quantity' => round($requiredQuantity, 6),
            'reserved_quantity' => round($reserved, 6),
            'available_quantity' => round($available, 6),
            'in_production' => $inProduction,
            'in_purchase' => round($inPurchase, 6),
            'received_quantity' => $received,
            'net_shortage' => $netShortage,
            'status' => $isCycle ? 'blocked' : ($netShortage > 0 ? 'shortage' : ($primaryOrder['status_group'] ?? 'available')),
            'order' => $primaryOrder,
            'purchase_orders' => $purchasing['orders'] ?? [],
            'is_cycle' => $isCycle,
            'children' => $children,
        ];
    }

    private function tracking(Sale $sale): array
    {
        $tracking = (array) data_get($sale->metadata, 'production_tracking', []);
        $responsibleId = (int) ($tracking['responsible_user_id'] ?? 0);

        return [
            'promised_date' => $tracking['promised_date'] ?? null,
            'responsible_user_id' => $responsibleId > 0 ? $responsibleId : null,
            'responsible_name' => $responsibleId > 0 ? User::query()->whereKey($responsibleId)->value('name') : null,
            'comments' => collect($tracking['comments'] ?? [])->sortByDesc('created_at')->take(30)->values()->all(),
        ];
    }

    private function history(Sale $sale): array
    {
        return AuditLog::query()
            ->where(static function ($query): void {
                $query->where('event', 'like', 'tenant_sale.%')
                    ->orWhere('event', 'tenant_production_order.rescheduled');
            })
            ->orderByDesc('occurred_at')
            ->limit(500)
            ->get(['id', 'user_id', 'event', 'context', 'occurred_at'])
            ->filter(static fn (AuditLog $log): bool => (int) data_get($log->context, 'company_id') === (int) $sale->company_id
                && (int) data_get($log->context, 'sale_id') === (int) $sale->id)
            ->take(30)
            ->map(static fn (AuditLog $log): array => [
                'id' => (int) $log->id,
                'event' => (string) $log->event,
                'date' => $log->occurred_at?->toIso8601String(),
                'user_id' => $log->user_id,
                'context' => $log->context,
            ])
            ->values()
            ->all();
    }

    private function statusGroup(string $status): string
    {
        return match ($status) {
            'COMPLETED' => 'completed',
            'IN_PROGRESS', 'PARTIALLY_COMPLETED' => 'in_progress',
            default => 'planned',
        };
    }

    private function itemStatus(array $materials, Collection $orders, Collection $forecasts): string
    {
        if ($forecasts->isNotEmpty()) {
            return 'forecast';
        }

        if ($orders->contains('status_group', 'in_progress')) {
            return 'in_progress';
        }

        if ($orders->contains('status_group', 'planned')) {
            return 'planned';
        }

        if ($orders->isNotEmpty() && $orders->every(static fn (array $order): bool => $order['status_group'] === 'completed')) {
            return 'completed';
        }

        $finished = $materials['finished_products'][0] ?? null;

        return $finished !== null && (float) $finished['quantity_to_produce'] <= 0
            ? 'available'
            : 'forecast';
    }

    private function alerts(Collection $items, array $counts, bool $scheduleIncomplete): array
    {
        $alerts = collect();

        foreach ($items as $item) {
            $context = [
                'line_id' => $item['line_id'],
                'product' => $item['sku'],
            ];

            if ($item['cycles'] !== []) {
                $alerts->push($context + ['key' => 'bom_cycle', 'severity' => 'error']);
            }

            if ($item['missing_boms'] !== []) {
                $alerts->push($context + ['key' => 'missing_bom', 'severity' => 'error']);
            }

            if ($item['counts']['to_buy'] > 0) {
                $alerts->push($context + [
                    'key' => 'materials_to_buy',
                    'severity' => 'warning',
                    'count' => $item['counts']['to_buy'],
                ]);
            }

            foreach ($item['materials'] as $material) {
                if ((float) $material['net_shortage'] > 0) {
                    $alerts->push($context + [
                        'key' => 'stock_insufficient',
                        'severity' => 'error',
                        'product' => $material['sku'],
                        'count' => $material['net_shortage'],
                    ]);
                }

                if ((float) $material['required_quantity'] > 0 && $material['unit_cost'] === null && $material['recommended_action'] === 'BUY') {
                    $alerts->push($context + [
                        'key' => 'material_without_price',
                        'severity' => 'warning',
                        'product' => $material['sku'],
                    ]);
                }
            }

            foreach ($item['production_orders'] as $order) {
                $orderContext = $context + ['order' => $order['order_number']];

                if ($order['missing_routing']) {
                    $alerts->push($orderContext + ['key' => 'order_without_routing', 'severity' => 'warning']);
                }

                if (collect($order['costs']['rate_evidence'])->contains('rate', null)) {
                    $alerts->push($orderContext + ['key' => 'work_center_without_rate', 'severity' => 'warning']);
                }

                if ($order['excess_consumption_count'] > 0) {
                    $alerts->push($orderContext + [
                        'key' => 'consumption_above_plan',
                        'severity' => 'error',
                        'count' => $order['excess_consumption_count'],
                    ]);
                }

                if ($order['scrap_above_limit']) {
                    $alerts->push($orderContext + ['key' => 'scrap_above_limit', 'severity' => 'error']);
                }
            }

            if ($item['counts']['forecast'] > 0) {
                $alerts->push($context + [
                    'key' => 'orders_not_created',
                    'severity' => 'warning',
                    'count' => $item['counts']['forecast'],
                ]);
            }

            if ($item['costs']['estimated_incomplete'] || $item['costs']['actual_incomplete']) {
                $alerts->push($context + ['key' => 'cost_incomplete', 'severity' => 'info']);
            }
        }

        if ($counts['overdue'] > 0) {
            $alerts->prepend([
                'key' => 'orders_overdue',
                'severity' => 'error',
                'count' => $counts['overdue'],
                'line_id' => null,
                'product' => null,
            ]);
        }

        if ($scheduleIncomplete) {
            $alerts->push([
                'key' => 'schedule_incomplete',
                'severity' => 'info',
                'line_id' => null,
                'product' => null,
            ]);
        }

        return $alerts->values()->all();
    }

    private function readiness(Collection $items, array $counts, bool $scheduleIncomplete): string
    {
        if ($items->flatMap(static fn (array $item): array => $item['materials'])
            ->contains(static fn (array $material): bool => $material['recommended_action'] === 'BUY' && (float) $material['net_shortage'] > 0)) {
            return 'blocked_materials';
        }

        if ($counts['overdue'] > 0) {
            return 'at_risk';
        }

        if ($counts['forecast'] > 0 || $scheduleIncomplete
            || $items->contains(static fn (array $item): bool => $item['cycles'] !== [] || $item['missing_boms'] !== [])) {
            return 'unscheduled';
        }

        if ($counts['in_progress'] > 0 || $counts['planned'] > 0) {
            return 'in_progress';
        }

        return 'ready';
    }

    /**
     * @param  Collection<int, ProductionOrder>  $orders
     * @param  Collection<int, PurchaseOrder>  $purchaseOrders
     */
    private function timeline(Sale $sale, Collection $orders, Collection $purchaseOrders): array
    {
        $events = collect();

        foreach ([
            ['date' => $sale->confirmed_at, 'type' => 'sale_confirmed'],
            ['date' => $sale->picking_at, 'type' => 'sale_picking'],
            ['date' => $sale->invoiced_at, 'type' => 'sale_invoiced'],
            ['date' => $sale->shipped_at, 'type' => 'sale_shipped'],
            ['date' => $sale->delivered_at, 'type' => 'sale_delivered'],
        ] as $event) {
            if ($event['date'] !== null) {
                $events->push($event + ['order_number' => null]);
            }
        }

        foreach ($orders as $order) {
            foreach ([
                ['date' => $order->created_at, 'type' => 'order_created'],
                ['date' => $order->released_at, 'type' => 'order_released'],
                ['date' => $order->started_at, 'type' => 'order_started'],
                ['date' => $order->completed_at, 'type' => 'order_completed'],
            ] as $event) {
                if ($event['date'] !== null) {
                    $events->push($event + ['order_number' => (string) $order->order_number]);
                }
            }
        }

        InventoryReservation::query()
            ->where('reference_type', 'sale')
            ->where('reference_id', $sale->id)
            ->where('status', 'RESERVED')
            ->orderBy('reserved_at')
            ->get(['reserved_at'])
            ->each(static fn (InventoryReservation $reservation) => $events->push([
                'date' => $reservation->reserved_at,
                'type' => 'material_reserved',
                'order_number' => null,
            ]));

        foreach ($purchaseOrders as $purchaseOrder) {
            $events->push([
                'date' => $purchaseOrder->created_at,
                'type' => 'purchase_order_created',
                'order_number' => $purchaseOrder->purchase_order_number,
            ]);

            if ((float) $purchaseOrder->lines->sum('quantity_received') > 0) {
                $events->push([
                    'date' => $purchaseOrder->updated_at,
                    'type' => 'purchase_received',
                    'order_number' => $purchaseOrder->purchase_order_number,
                ]);
            }
        }

        return $events
            ->sortByDesc('date')
            ->take(30)
            ->unique(static fn (array $event): string => $event['type'].'|'.($event['order_number'] ?? ''))
            ->sortBy('date')
            ->map(static fn (array $event): array => [
                'date' => $event['date']->toIso8601String(),
                'type' => $event['type'],
                'order_number' => $event['order_number'],
            ])
            ->values()
            ->all();
    }
}
