<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Modules\Bom\Infrastructure\Persistence\Models\BomHeader;
use App\Modules\Genealogy\Infrastructure\Persistence\Models\GenealogyRelation;
use App\Modules\Inventory\Infrastructure\Persistence\Models\InventoryBalance;
use App\Modules\Production\Infrastructure\Persistence\Models\ProductionOrder;
use App\Modules\Product\Infrastructure\Persistence\Models\Product;
use App\Modules\Scheduling\Infrastructure\Persistence\Models\ProductionCalendarDay;
use Illuminate\Database\QueryException;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

final class IndustrialDashboardController extends Controller
{
    public function __invoke(): View
    {
        $today = now()->startOfDay();
        $windowEnd = $today->copy()->addDays(30);

        $inventoryRows = $this->safe(fn () => InventoryBalance::query()
            ->with('product:id,sku,description,safety_stock,lead_time_days,product_type')
            ->orderByDesc('qty_available')
            ->limit(18)
            ->get());

        $inventoryKpis = [
            'available' => round((float) $inventoryRows->sum('qty_available'), 2),
            'reserved' => round((float) $inventoryRows->sum('qty_reserved'), 2),
            'in_transit' => round((float) $inventoryRows->sum('qty_in_transit'), 2),
            'inspection' => round((float) $inventoryRows->sum('qty_inspection'), 2),
        ];

        $materialShortages = $inventoryRows
            ->filter(static function (InventoryBalance $balance): bool {
                $free = (float) $balance->qty_available - (float) $balance->qty_reserved;
                $safety = (float) ($balance->product?->safety_stock ?? 0);
                $type = (string) ($balance->product?->product_type ?? '');

                return in_array($type, ['RAW', 'CONSUMABLE'], true) && $free < $safety;
            })
            ->values();

        $openOrders = $this->safe(fn () => ProductionOrder::query()
            ->with('product:id,sku,description')
            ->whereIn('status', ['DRAFT', 'RELEASED', 'IN_PROGRESS', 'PARTIALLY_COMPLETED'])
            ->orderBy('scheduled_end_date')
            ->limit(14)
            ->get());

        $statusBreakdown = $this->safe(fn () => ProductionOrder::query()
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status'));

        $bomHeaders = $this->safe(fn () => BomHeader::query()
            ->with('product:id,sku,description')
            ->withCount('items')
            ->orderByDesc('id')
            ->limit(12)
            ->get());

        $genealogyRelations = $this->safe(fn () => GenealogyRelation::query()
            ->with([
                'parentNode:id,node_type,source_reference,product_id',
                'childNode:id,node_type,source_reference,product_id',
            ])
            ->orderByDesc('id')
            ->limit(14)
            ->get());

        $calendarRows = $this->safe(fn () => ProductionCalendarDay::query()
            ->with('workCenter:id,code,name')
            ->whereBetween('calendar_date', [$today->toDateString(), $windowEnd->toDateString()])
            ->where('is_working_day', true)
            ->orderBy('calendar_date')
            ->limit(60)
            ->get());

        $ganttRows = $openOrders->map(static function (ProductionOrder $order) use ($today, $windowEnd): array {
            $start = Carbon::parse($order->scheduled_start_date ?? $today);
            $end = Carbon::parse($order->scheduled_end_date ?? $start);
            if ($end->lessThan($start)) {
                $end = $start->copy();
            }

            $windowDays = max(1, $today->diffInDays($windowEnd));
            $offsetDays = max(0, $today->diffInDays($start, false));
            $durationDays = max(1, $start->diffInDays($end) + 1);

            return [
                'order_number' => $order->order_number,
                'status' => $order->status,
                'product_sku' => (string) ($order->product?->sku ?? '-'),
                'product_description' => (string) ($order->product?->description ?? '-'),
                'start_date' => $start->toDateString(),
                'end_date' => $end->toDateString(),
                'left_percent' => min(100, max(0, round(($offsetDays / $windowDays) * 100, 2))),
                'width_percent' => min(100, max(2.5, round(($durationDays / $windowDays) * 100, 2))),
            ];
        })->values();

        $avgMaterialLeadTime = (float) $this->safe(fn () => Product::query()
            ->whereIn('product_type', ['RAW', 'CONSUMABLE'])
            ->avg('lead_time_days'), 0.0);

        return view('dashboard.industrial', [
            'today' => $today,
            'windowEnd' => $windowEnd,
            'mrpCockpit' => [
                'material_shortages' => $materialShortages,
                'purchase_signals' => $materialShortages->count(),
                'production_signals' => $openOrders->count(),
                'avg_material_lead_time' => round($avgMaterialLeadTime, 1),
            ],
            'production' => [
                'open_orders' => $openOrders,
                'status_breakdown' => $statusBreakdown,
            ],
            'inventory' => [
                'kpis' => $inventoryKpis,
                'rows' => $inventoryRows,
            ],
            'bom' => [
                'headers' => $bomHeaders,
            ],
            'genealogy' => [
                'relations' => $genealogyRelations,
            ],
            'scheduling' => [
                'calendar_rows' => $calendarRows,
                'gantt_rows' => $ganttRows,
            ],
        ]);
    }

    private function safe(callable $callback, mixed $fallback = null): mixed
    {
        try {
            return $callback();
        } catch (QueryException) {
            return $fallback ?? collect();
        }
    }
}
