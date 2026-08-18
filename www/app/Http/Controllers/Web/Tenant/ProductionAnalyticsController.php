<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\Tenant\Concerns\HandlesTenantAuthorization;
use App\Modules\Production\Infrastructure\Persistence\Models\ProductionOperationOutput;
use App\Modules\Production\Infrastructure\Persistence\Models\ProductionOrder;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class ProductionAnalyticsController extends Controller
{
    use HandlesTenantAuthorization;

    private const READ_PERMISSION = 'production-orders.read';

    public function index(Request $request): View
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensurePermission($request, self::READ_PERMISSION, $company->id);

        $period = (int) $request->query('days', 30);
        $period = max(7, min($period, 180));
        $from = now()->subDays($period)->startOfDay();

        $orders = ProductionOrder::query()
            ->where('created_at', '>=', $from)
            ->get(['id', 'status', 'quantity_planned', 'quantity_produced', 'quantity_scrapped']);

        $outputs = ProductionOperationOutput::query()
            ->where('created_at', '>=', $from)
            ->get(['inspection_status', 'quantity_good', 'quantity_scrapped', 'setup_time_minutes', 'process_time_minutes', 'reported_at']);

        $planned = (float) $orders->sum('quantity_planned');
        $produced = (float) $orders->sum('quantity_produced');
        $scrapped = (float) $orders->sum('quantity_scrapped');
        $good = max(0.0, $produced - $scrapped);
        $totalOutput = max(0.0, $good + $scrapped);

        $qualityRate = $totalOutput > 0 ? ($good / $totalOutput) * 100 : 0.0;
        $planAdherence = $planned > 0 ? ($produced / $planned) * 100 : 0.0;

        $approvedCount = $outputs->where('inspection_status', 'APPROVED')->count();
        $rejectedCount = $outputs->where('inspection_status', 'REJECTED')->count();
        $pendingCount = $outputs->where('inspection_status', 'PENDING')->count();

        $setupMinutes = (float) $outputs->sum('setup_time_minutes');
        $processMinutes = (float) $outputs->sum('process_time_minutes');

        $statusCards = [
            'draft' => $orders->where('status', 'DRAFT')->count(),
            'released' => $orders->where('status', 'RELEASED')->count(),
            'in_progress' => $orders->whereIn('status', ['IN_PROGRESS', 'PARTIALLY_COMPLETED'])->count(),
            'completed' => $orders->where('status', 'COMPLETED')->count(),
        ];

        $scrapByDay = ProductionOperationOutput::query()
            ->selectRaw('DATE(reported_at) as day, SUM(quantity_scrapped) as total_scrap')
            ->where('reported_at', '>=', $from)
            ->groupBy('day')
            ->orderBy('day')
            ->get();

        $operationEfficiency = ProductionOperationOutput::query()
            ->join('production_order_operations as operation', 'operation.id', '=', 'production_operation_outputs.production_order_operation_id')
            ->selectRaw('operation.operation_no, SUM(production_operation_outputs.quantity_good) as good_qty, SUM(production_operation_outputs.quantity_scrapped) as scrap_qty, SUM(production_operation_outputs.process_time_minutes) as process_minutes')
            ->where('production_operation_outputs.created_at', '>=', $from)
            ->groupBy('operation.operation_no')
            ->orderBy('operation.operation_no')
            ->get();

        return view('client.production.analytics.index', [
            'company' => $company,
            'period' => $period,
            'qualityRate' => $qualityRate,
            'planAdherence' => $planAdherence,
            'setupMinutes' => $setupMinutes,
            'processMinutes' => $processMinutes,
            'approvedCount' => $approvedCount,
            'rejectedCount' => $rejectedCount,
            'pendingCount' => $pendingCount,
            'statusCards' => $statusCards,
            'scrapByDay' => $scrapByDay,
            'operationEfficiency' => $operationEfficiency,
        ]);
    }
}
