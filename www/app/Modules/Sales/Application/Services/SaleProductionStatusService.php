<?php

declare(strict_types=1);

namespace App\Modules\Sales\Application\Services;

use App\Modules\Production\Infrastructure\Persistence\Models\ProductionOrder;
use App\Modules\Purchasing\Infrastructure\Persistence\Models\PurchaseOrderLine;
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

    public function __construct(
        private readonly SaleMaterialRequirementService $materialRequirementService
    ) {}

    /** @return array<string, mixed> */
    public function analyze(Sale $sale): array
    {
        $this->unitCosts = [];
        $this->hourRates = [];
        $sale->loadMissing(['customer:id,name', 'lines.product.unit']);

        $orders = ProductionOrder::query()
            ->where('source_reference_type', 'sale')
            ->where('source_reference_id', $sale->id)
            ->where('status', '!=', 'CANCELLED')
            ->with([
                'product.unit:id,code',
                'warehouse:id,code,name',
                'operations.workCenter:id,code,name',
                'outputs',
                'materialConsumptions.product:id,sku,description,product_type,unit_id',
                'materialConsumptions.product.unit:id,code',
            ])
            ->orderBy('id')
            ->get();

        $items = $sale->lines->map(function (SaleLine $line) use ($sale, $orders): array {
            $lineOrders = $orders->filter(fn (ProductionOrder $order): bool => $this->belongsToLine($order, $line));
            $materials = $this->materialRequirementService->analyzeLine($sale, $line);
            $orderProductIds = $lineOrders->pluck('product_id')->map(static fn ($id): int => (int) $id)->unique();
            $orderRows = $lineOrders
                ->map(fn (ProductionOrder $order): array => $this->orderRow($order, $sale, $orderProductIds))
                ->sortBy([['level', 'asc'], ['id', 'asc']])
                ->values();
            $forecasts = $this->forecastRows($line, $materials, $lineOrders);
            $estimatedMaterials = $this->estimatedMaterialCost($materials);

            $costs = [
                'estimated_material' => $estimatedMaterials['amount'],
                'estimated_production' => round((float) $orderRows->sum('costs.estimated_production'), 2),
                'actual_material' => round((float) $orderRows->sum('costs.actual_material'), 2),
                'actual_production' => round((float) $orderRows->sum('costs.actual_production'), 2),
                'estimated_incomplete' => $estimatedMaterials['incomplete']
                    || $orderRows->contains('costs.estimated_incomplete', true)
                    || $forecasts->isNotEmpty(),
                'actual_incomplete' => $orderRows->contains('costs.actual_incomplete', true),
            ];
            $costs['estimated_total'] = round($costs['estimated_material'] + $costs['estimated_production'], 2);
            $costs['actual_total'] = round($costs['actual_material'] + $costs['actual_production'], 2);

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
                'materials' => $materials['materials'],
                'missing_boms' => $materials['missing_boms'],
                'cycles' => $materials['cycles'],
                'counts' => [
                    'completed' => $allProductionRows->where('status_group', 'completed')->count(),
                    'in_progress' => $allProductionRows->where('status_group', 'in_progress')->count(),
                    'planned' => $allProductionRows->where('status_group', 'planned')->count(),
                    'forecast' => $allProductionRows->where('status_group', 'forecast')->count(),
                    'materials_short' => collect($materials['materials'])->where('shortage_quantity', '>', 0)->count(),
                    'materials_in_stock' => $materials['materials_in_stock_count'],
                    'to_buy' => $materials['purchase_items_count'],
                    'to_produce' => $materials['production_items_count'],
                ],
                'costs' => $costs,
            ];
        })->values();

        return [
            'sale_id' => (int) $sale->id,
            'items' => $items->all(),
            'counts' => [
                'completed' => $items->sum('counts.completed'),
                'in_progress' => $items->sum('counts.in_progress'),
                'planned' => $items->sum('counts.planned'),
                'forecast' => $items->sum('counts.forecast'),
                'materials_short' => $items->sum('counts.materials_short'),
            ],
            'costs' => [
                'estimated_total' => round((float) $items->sum('costs.estimated_total'), 2),
                'actual_total' => round((float) $items->sum('costs.actual_total'), 2),
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
        $estimatedIncomplete = false;
        $actualIncomplete = false;

        foreach ($order->operations as $operation) {
            $estimatedMinutes = (float) $operation->productive_time_minutes;
            $actualMinutes = (float) $operation->actual_productive_minutes;
            $rateDate = $operation->planned_start_at?->toDateString()
                ?? $order->scheduled_start_date?->toDateString()
                ?? $sale->sale_date?->toDateString()
                ?? now()->toDateString();
            $rate = $this->hourRate((int) $operation->work_center_id, $rateDate);

            if ($estimatedMinutes > 0 && $rate === null) {
                $estimatedIncomplete = true;
            } else {
                $estimatedProduction += ($estimatedMinutes / 60) * (float) $rate;
            }

            if ($actualMinutes > 0 && $rate === null) {
                $actualIncomplete = true;
            } else {
                $actualProduction += ($actualMinutes / 60) * (float) $rate;
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
                    $actualProduction += ($minutes / 60) * $rate;
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

            $actualMaterial += $quantity * (float) $unitCost;
        }

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
            'warehouse' => $order->warehouse?->code,
            'operations_count' => $order->operations->count(),
            'costs' => [
                'estimated_production' => round($estimatedProduction, 2),
                'actual_material' => round($actualMaterial, 2),
                'actual_production' => round($actualProduction, 2),
                'estimated_incomplete' => $estimatedIncomplete,
                'actual_incomplete' => $actualIncomplete,
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
                'warehouse' => null,
                'operations_count' => 0,
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

        $purchaseCost = PurchaseOrderLine::query()
            ->where('product_id', $productId)
            ->whereNotNull('unit_price')
            ->where('unit_price', '>', 0)
            ->whereHas('purchaseOrder', static fn ($query) => $query->where('status', '!=', 'CANCELLED'))
            ->latest('id')
            ->value('unit_price');

        $supplierCost = SupplierProduct::query()
            ->where('product_id', $productId)
            ->where('is_active', true)
            ->whereNotNull('unit_price')
            ->where('unit_price', '>', 0)
            ->orderByDesc('is_preferred')
            ->orderByDesc('id')
            ->value('unit_price');

        $cost = $purchaseCost ?? $supplierCost;

        return $this->unitCosts[$productId] = $cost !== null ? (float) $cost : null;
    }

    private function hourRate(int $workCenterId, string $date): ?float
    {
        $key = $workCenterId.'|'.$date;

        if (array_key_exists($key, $this->hourRates)) {
            return $this->hourRates[$key];
        }

        $rate = WorkCenterHourRate::query()
            ->where('work_center_id', $workCenterId)
            ->where('status', 'ACTIVE')
            ->whereDate('effective_from', '<=', $date)
            ->where(function ($query) use ($date): void {
                $query->whereNull('effective_to')->orWhereDate('effective_to', '>=', $date);
            })
            ->orderByDesc('effective_from')
            ->value('hourly_rate');

        return $this->hourRates[$key] = $rate !== null ? (float) $rate : null;
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
}
