<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Tenant;

use App\Http\Controllers\Controller;
use App\Modules\Identity\Infrastructure\Persistence\Models\User;
use App\Modules\Tenant\Infrastructure\Persistence\Models\Company;
use App\Services\SaaS\CompanyUserAccessService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

final class DomainDashboardController extends Controller
{
    private const SLA_SALES_PENDING_DAYS = 3;

    private const SLA_RELEASED_ORDER_DAYS = 2;

    private const SLA_REWORK_OPEN_DAYS = 2;

    private const SLA_INVITATION_DAYS = 3;

    private const AGING_DRAFT_DAYS = 7;

    private const AGING_ANALYTICS_DAYS = 7;

    private const HIGH_PRIORITY_THRESHOLD = 100;

    private const DOMAIN_REQUIREMENTS = [
        'engineering' => ['production_mrp', 'products'],
        'planning' => ['production_mrp'],
        'shop_floor' => ['production_mrp'],
        'analysis' => ['production_mrp'],
        'inventory' => ['inventory'],
        'purchasing' => ['purchasing'],
        'sales' => ['sales'],
        'administration' => ['users'],
    ];

    public function __invoke(Request $request, string $domain): View
    {
        $user = $request->user();

        abort_unless($user instanceof User, 403);

        $companyId = (int) ($user->current_company_id ?? 0);
        abort_unless($companyId > 0, 404);

        $company = Company::query()->findOrFail($companyId);
        $accessService = app(CompanyUserAccessService::class);
        $accessibleModules = $accessService->accessibleModules($user, $company);

        $requirements = self::DOMAIN_REQUIREMENTS[$domain] ?? null;
        abort_unless(is_array($requirements), 404);

        $allowed = false;

        foreach ($requirements as $requiredModule) {
            if (in_array($requiredModule, $accessibleModules, true)) {
                $allowed = true;
                break;
            }
        }

        abort_unless($allowed, 403);

        $dashboard = $this->buildDashboardData($companyId, $domain);

        return view('dashboard.domain', [
            'dashboard' => $dashboard,
        ]);
    }

    /** @return array<string, mixed> */
    private function buildDashboardData(int $companyId, string $domain): array
    {
        return match ($domain) {
            'engineering' => $this->engineeringData($companyId),
            'planning' => $this->planningData($companyId),
            'shop_floor' => $this->shopFloorData($companyId),
            'analysis' => $this->analysisData($companyId),
            'inventory' => $this->inventoryData($companyId),
            'purchasing' => $this->purchasingData($companyId),
            'sales' => $this->salesData($companyId),
            'administration' => $this->administrationData($companyId),
            default => [],
        };
    }

    /** @return array<string, mixed> */
    private function engineeringData(int $companyId): array
    {
        $pendingItems = [
            $this->metric(__('domain_dashboard.metrics.draft_product_versions', ['days' => self::AGING_DRAFT_DAYS]), $this->countStatusAging($companyId, 'product_versions', ['DRAFT'], 'created_at', self::AGING_DRAFT_DAYS), 1, 5),
            $this->metric(__('domain_dashboard.metrics.draft_boms', ['days' => self::AGING_DRAFT_DAYS]), $this->countStatusAging($companyId, 'bom_headers', ['DRAFT'], 'created_at', self::AGING_DRAFT_DAYS), 1, 5),
            $this->metric(__('domain_dashboard.metrics.draft_routings', ['days' => self::AGING_DRAFT_DAYS]), $this->countStatusAging($companyId, 'routing_versions', ['DRAFT'], 'created_at', self::AGING_DRAFT_DAYS), 1, 5),
        ];

        $inProgressItems = [
            $this->metric(__('domain_dashboard.metrics.ecos_in_progress'), $this->countByStatus($companyId, 'engineering_change_orders', ['SUBMITTED', 'IN_REVIEW', 'APPROVED']), 5, 15),
            $this->metric(__('domain_dashboard.metrics.versions_awaiting_approval'), $this->countByStatus($companyId, 'product_versions', ['DRAFT']), 3, 10),
        ];

        return $this->compose(
            domain: 'engineering',
            title: __('ui.domain_engineering'),
            description: __('domain_dashboard.descriptions.engineering'),
            pendingItems: $pendingItems,
            inProgressItems: $inProgressItems,
            shortcuts: [
                ['label' => __('ui.product_register'), 'href' => route('products.index')],
                ['label' => __('ui.product_versions'), 'href' => route('products.versions')],
                ['label' => __('ui.bom_structures'), 'href' => route('bom.structures.index')],
                ['label' => __('ui.bom_revisions'), 'href' => route('bom.material-lists.index')],
                ['label' => __('ui.module_routing'), 'href' => route('production.routing.index')],
                ['label' => __('ui.work_centers'), 'href' => route('production.work-centers.index')],
            ]
        );
    }

    /** @return array<string, mixed> */
    private function planningData(int $companyId): array
    {
        $pendingItems = [
            $this->metric(__('domain_dashboard.metrics.overdue_mrp_suggestions'), $this->countMrpSuggestionsOverdue($companyId), 1, 3),
            $this->metric(__('domain_dashboard.metrics.high_priority_mrp', ['priority' => self::HIGH_PRIORITY_THRESHOLD]), $this->countMrpSuggestionsHighPriority($companyId), 2, 8),
            $this->metric(__('domain_dashboard.metrics.draft_schedules'), $this->countStatusAging($companyId, 'production_schedules', ['DRAFT'], 'created_at', 2), 1, 4),
        ];

        $inProgressItems = [
            $this->metric(__('domain_dashboard.metrics.mrp_runs'), $this->countByStatus($companyId, 'mrp_plan_runs', ['RUNNING']), 2, 5),
            $this->metric(__('domain_dashboard.metrics.draft_orders_priority'), $this->countStatusAging($companyId, 'production_orders', ['DRAFT'], 'created_at', 1), 2, 6),
        ];

        return $this->compose(
            domain: 'planning',
            title: __('ui.domain_planning'),
            description: __('domain_dashboard.descriptions.planning'),
            pendingItems: $pendingItems,
            inProgressItems: $inProgressItems,
            shortcuts: [
                ['label' => __('ui.module_scheduling'), 'href' => route('production.scheduling.index')],
                ['label' => __('ui.production_calendar'), 'href' => route('production.calendar.index')],
                ['label' => __('domain_dashboard.shortcuts_labels.new_schedule'), 'href' => route('production.scheduling.create')],
                ['label' => __('domain_dashboard.shortcuts_labels.generate_calendar'), 'href' => route('production.calendar.create')],
            ]
        );
    }

    /** @return array<string, mixed> */
    private function shopFloorData(int $companyId): array
    {
        $pendingItems = [
            $this->metric(__('domain_dashboard.metrics.released_orders_sla', ['days' => self::SLA_RELEASED_ORDER_DAYS]), $this->countStatusAging($companyId, 'production_orders', ['RELEASED'], 'released_at', self::SLA_RELEASED_ORDER_DAYS), 1, 3),
            $this->metric(__('domain_dashboard.metrics.late_orders'), $this->countProductionOrdersLate($companyId), 1, 3),
            $this->metric(__('domain_dashboard.metrics.open_reworks', ['days' => self::SLA_REWORK_OPEN_DAYS]), $this->countStatusAging($companyId, 'production_rework_orders', ['OPEN'], 'created_at', self::SLA_REWORK_OPEN_DAYS), 1, 3),
        ];

        $inProgressItems = [
            $this->metric(__('domain_dashboard.metrics.orders_in_progress'), $this->countByStatus($companyId, 'production_orders', ['IN_PROGRESS', 'PARTIALLY_COMPLETED']), 4, 12),
            $this->metric(__('domain_dashboard.metrics.pending_quality'), $this->countByStatus($companyId, 'production_quality_records', ['PENDING']), 2, 6),
        ];

        return $this->compose(
            domain: 'shop_floor',
            title: __('ui.domain_shop_floor'),
            description: __('domain_dashboard.descriptions.shop_floor'),
            pendingItems: $pendingItems,
            inProgressItems: $inProgressItems,
            shortcuts: [
                ['label' => __('ui.production_orders'), 'href' => route('production.orders.index')],
                ['label' => __('domain_dashboard.shortcuts_labels.new_order'), 'href' => route('production.orders.create')],
            ]
        );
    }

    /** @return array<string, mixed> */
    private function analysisData(int $companyId): array
    {
        $pendingItems = [
            $this->metric(__('domain_dashboard.metrics.pending_recommendations', ['days' => self::AGING_ANALYTICS_DAYS]), $this->countStatusAging($companyId, 'manufacturing_analytics_recommendations', ['PENDING', 'INVESTIGATE'], 'created_at', self::AGING_ANALYTICS_DAYS), 1, 4),
            $this->metric(__('domain_dashboard.metrics.quality_closing'), $this->countByStatus($companyId, 'production_quality_records', ['PENDING']), 2, 6),
        ];

        $inProgressItems = [
            $this->metric(__('domain_dashboard.metrics.partial_postings'), $this->countByStatus($companyId, 'production_orders', ['PARTIALLY_COMPLETED']), 2, 8),
            $this->metric(__('domain_dashboard.metrics.today_postings'), $this->countToday($companyId, 'production_operation_outputs', 'reported_at'), 1, 999999),
        ];

        return $this->compose(
            domain: 'analysis',
            title: __('ui.domain_analysis'),
            description: __('domain_dashboard.descriptions.analysis'),
            pendingItems: $pendingItems,
            inProgressItems: $inProgressItems,
            shortcuts: [
                ['label' => __('ui.production_postings'), 'href' => route('production.analytics.index')],
                ['label' => __('ui.module_scheduling'), 'href' => route('production.scheduling.index')],
            ]
        );
    }

    /** @return array<string, mixed> */
    private function inventoryData(int $companyId): array
    {
        $pendingItems = [
            $this->metric(__('domain_dashboard.metrics.high_priority_reservations'), $this->countInventoryReservationsHighPriority($companyId, 20), 2, 6),
            $this->metric(__('domain_dashboard.metrics.expired_reservations'), $this->countExpiredReservationsStillActive($companyId), 1, 3),
            $this->metric(__('domain_dashboard.metrics.low_stock'), $this->countLowStock($companyId), 2, 8),
        ];

        $inProgressItems = [
            $this->metric(__('domain_dashboard.metrics.today_movements'), $this->countToday($companyId, 'stock_ledger_movements', 'movement_at'), 1, 999999),
            $this->metric(__('domain_dashboard.metrics.today_transfers'), $this->countByMovementTypeToday($companyId, ['TRANSFER_OUT', 'TRANSFER_IN']), 1, 999999),
            $this->metric(__('domain_dashboard.metrics.inspection_items'), $this->countInspectionQueue($companyId), 2, 6),
        ];

        return $this->compose(
            domain: 'inventory',
            title: __('ui.module_inventory'),
            description: __('domain_dashboard.descriptions.inventory'),
            pendingItems: $pendingItems,
            inProgressItems: $inProgressItems,
            shortcuts: [
                ['label' => __('ui.inventory_count'), 'href' => route('inventory.balances.index')],
                ['label' => __('ui.inventory_movements'), 'href' => route('inventory.movements.index')],
                ['label' => __('domain_dashboard.shortcuts_labels.new_movement'), 'href' => route('inventory.movements.create')],
                ['label' => __('ui.inventory_warehouses'), 'href' => route('inventory.warehouses.index')],
            ]
        );
    }

    /** @return array<string, mixed> */
    private function purchasingData(int $companyId): array
    {
        $pendingItems = [
            $this->metric(__('domain_dashboard.metrics.overdue_requisition_lines'), $this->countOpenRequisitionLinesOverdue($companyId), 1, 3),
            $this->metric(__('domain_dashboard.metrics.late_purchase_orders'), $this->countPurchaseOrdersDeliveryOverdue($companyId), 1, 3),
            $this->metric(__('domain_dashboard.metrics.draft_receipts'), $this->countStatusAging($companyId, 'purchase_receipts', ['DRAFT'], 'created_at', 2), 2, 5),
        ];

        $inProgressItems = [
            $this->metric(__('domain_dashboard.metrics.open_purchase_orders'), $this->countByStatus($companyId, 'purchase_orders', ['APPROVED', 'OPEN']), 5, 15),
            $this->metric(__('domain_dashboard.metrics.urgent_requisition_lines'), $this->countOpenRequisitionLinesUrgent($companyId), 2, 6),
        ];

        return $this->compose(
            domain: 'purchasing',
            title: __('ui.module_purchasing'),
            description: __('domain_dashboard.descriptions.purchasing'),
            pendingItems: $pendingItems,
            inProgressItems: $inProgressItems,
            shortcuts: [
                ['label' => __('ui.purchasing_suppliers'), 'href' => route('purchasing.suppliers.index')],
                ['label' => __('ui.purchasing_requisition'), 'href' => route('purchasing.requisitions.index')],
                ['label' => __('ui.purchasing_order'), 'href' => route('purchasing.orders.index')],
                ['label' => __('ui.purchasing_receipt'), 'href' => route('purchasing.receipts.index')],
            ]
        );
    }

    /** @return array<string, mixed> */
    private function salesData(int $companyId): array
    {
        $pendingItems = [
            $this->metric(__('domain_dashboard.metrics.draft_sales'), $this->countByStatus($companyId, 'sales', ['DRAFT']), 3, 8),
            $this->metric(__('domain_dashboard.metrics.pending_sales_sla', ['days' => self::SLA_SALES_PENDING_DAYS]), $this->countSalesPendingSlaBreached($companyId), 1, 4),
            $this->metric(__('domain_dashboard.metrics.pending_sales_total'), $this->countConfirmedPendingOperational($companyId), 3, 10),
        ];

        $inProgressItems = [
            $this->metric(__('domain_dashboard.metrics.sales_in_fulfillment'), $this->countByOperationalStatus($companyId, ['PICKING', 'INVOICED', 'SHIPPED']), 4, 12),
            $this->metric(__('domain_dashboard.metrics.shipped_sales_aging'), $this->countStatusAgingByOperationalStatus($companyId, ['SHIPPED'], 'shipped_at', 2), 2, 6),
        ];

        return $this->compose(
            domain: 'sales',
            title: __('ui.module_sales'),
            description: __('domain_dashboard.descriptions.sales'),
            pendingItems: $pendingItems,
            inProgressItems: $inProgressItems,
            shortcuts: [
                ['label' => __('ui.sales_register'), 'href' => route('sales.index')],
                ['label' => __('domain_dashboard.shortcuts_labels.new_sale'), 'href' => route('sales.create')],
                ['label' => __('ui.sales_customers'), 'href' => route('customers.index')],
                ['label' => __('domain_dashboard.shortcuts_labels.new_customer'), 'href' => route('customers.create')],
            ]
        );
    }

    /** @return array<string, mixed> */
    private function administrationData(int $companyId): array
    {
        $pendingItems = [
            $this->metric(__('domain_dashboard.metrics.pending_invitations_sla', ['days' => self::SLA_INVITATION_DAYS]), $this->countPendingInvitationsSlaBreached($companyId), 1, 3),
            $this->metric(__('domain_dashboard.metrics.pending_access_approvals'), $this->countStatusAging($companyId, 'role_assignment_approvals', ['pending'], 'created_at', 2), 2, 5),
        ];

        $inProgressItems = [
            $this->metric(__('domain_dashboard.metrics.active_company_users'), $this->countActiveCompanyUsers($companyId), 1, 999999),
            $this->metric(__('domain_dashboard.metrics.today_invitations'), $this->countTodayInvitations($companyId), 1, 999999),
        ];

        return $this->compose(
            domain: 'administration',
            title: __('ui.domain_administration'),
            description: __('domain_dashboard.descriptions.administration'),
            pendingItems: $pendingItems,
            inProgressItems: $inProgressItems,
            shortcuts: [
                ['label' => __('ui.manage_accesses'), 'href' => route('company-access.users.index')],
                ['label' => __('ui.rbac_roles'), 'href' => route('company-access.rbac.roles.index')],
            ]
        );
    }

    /**
    * @param list<array{label: string, count: int, severity: string, severity_label: string}> $pendingItems
    * @param list<array{label: string, count: int, severity: string, severity_label: string}> $inProgressItems
     * @param list<array{label: string, href: string}> $shortcuts
     *
     * @return array<string, mixed>
     */
    private function compose(
        string $domain,
        string $title,
        string $description,
        array $pendingItems,
        array $inProgressItems,
        array $shortcuts
    ): array {
        return [
            'domain' => $domain,
            'title' => $title,
            'description' => $description,
            'pending_items' => $pendingItems,
            'in_progress_items' => $inProgressItems,
            'pending_total' => array_sum(array_map(static fn (array $item): int => (int) $item['count'], $pendingItems)),
            'in_progress_total' => array_sum(array_map(static fn (array $item): int => (int) $item['count'], $inProgressItems)),
            'shortcuts' => $shortcuts,
        ];
    }

    /** @param list<string> $statuses */
    private function countByStatus(int $companyId, string $table, array $statuses): int
    {
        if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'company_id') || ! Schema::hasColumn($table, 'status')) {
            return 0;
        }

        return (int) DB::table($table)
            ->where('company_id', $companyId)
            ->whereIn('status', $statuses)
            ->count();
    }

    /** @param list<string> $statuses */
    private function countByOperationalStatus(int $companyId, array $statuses): int
    {
        if (! Schema::hasTable('sales') || ! Schema::hasColumn('sales', 'company_id') || ! Schema::hasColumn('sales', 'operational_status')) {
            return 0;
        }

        return (int) DB::table('sales')
            ->where('company_id', $companyId)
            ->whereIn('operational_status', $statuses)
            ->count();
    }

    private function countConfirmedPendingOperational(int $companyId): int
    {
        if (! Schema::hasTable('sales')) {
            return 0;
        }

        return (int) DB::table('sales')
            ->where('company_id', $companyId)
            ->where('status', 'CONFIRMED')
            ->whereIn('operational_status', ['PENDING', 'PICKING', 'INVOICED', 'SHIPPED'])
            ->count();
    }

    private function countToday(int $companyId, string $table, string $dateColumn): int
    {
        if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'company_id') || ! Schema::hasColumn($table, $dateColumn)) {
            return 0;
        }

        return (int) DB::table($table)
            ->where('company_id', $companyId)
            ->whereDate($dateColumn, now()->toDateString())
            ->count();
    }

    /** @param list<string> $types */
    private function countByMovementTypeToday(int $companyId, array $types): int
    {
        if (! Schema::hasTable('stock_ledger_movements') || ! Schema::hasColumn('stock_ledger_movements', 'movement_type')) {
            return 0;
        }

        return (int) DB::table('stock_ledger_movements')
            ->where('company_id', $companyId)
            ->whereIn('movement_type', $types)
            ->whereDate('movement_at', now()->toDateString())
            ->count();
    }

    private function countLowStock(int $companyId): int
    {
        if (! Schema::hasTable('inventory_balances') || ! Schema::hasTable('products')) {
            return 0;
        }

        return (int) DB::table('inventory_balances')
            ->join('products', 'products.id', '=', 'inventory_balances.product_id')
            ->where('inventory_balances.company_id', $companyId)
            ->where('products.company_id', $companyId)
            ->where('products.safety_stock', '>', 0)
            ->whereRaw('inventory_balances.qty_available < products.safety_stock')
            ->distinct('inventory_balances.product_id')
            ->count('inventory_balances.product_id');
    }

    private function countInspectionQueue(int $companyId): int
    {
        if (! Schema::hasTable('inventory_balances') || ! Schema::hasColumn('inventory_balances', 'qty_inspection')) {
            return 0;
        }

        return (int) DB::table('inventory_balances')
            ->where('company_id', $companyId)
            ->where('qty_inspection', '>', 0)
            ->count();
    }

    private function countPendingInvitations(int $companyId): int
    {
        if (! Schema::hasTable('account_invitations')) {
            return 0;
        }

        return (int) DB::table('account_invitations')
            ->where('company_id', $companyId)
            ->whereNull('accepted_at')
            ->whereNull('revoked_at')
            ->count();
    }

    private function countTodayInvitations(int $companyId): int
    {
        if (! Schema::hasTable('account_invitations')) {
            return 0;
        }

        return (int) DB::table('account_invitations')
            ->where('company_id', $companyId)
            ->whereDate('created_at', now()->toDateString())
            ->count();
    }

    private function countCompanyUsers(int $companyId): int
    {
        if (! Schema::hasTable('company_user')) {
            return 0;
        }

        return (int) DB::table('company_user')
            ->where('company_id', $companyId)
            ->count();
    }

    /** @param list<string> $statuses */
    private function countStatusAging(int $companyId, string $table, array $statuses, string $dateColumn, int $minDays): int
    {
        if (! Schema::hasTable($table)
            || ! Schema::hasColumn($table, 'company_id')
            || ! Schema::hasColumn($table, 'status')
            || ! Schema::hasColumn($table, $dateColumn)) {
            return 0;
        }

        return (int) DB::table($table)
            ->where('company_id', $companyId)
            ->whereIn('status', $statuses)
            ->whereDate($dateColumn, '<=', now()->subDays($minDays)->toDateString())
            ->count();
    }

    /** @param list<string> $operationalStatuses */
    private function countStatusAgingByOperationalStatus(int $companyId, array $operationalStatuses, string $dateColumn, int $minDays): int
    {
        if (! Schema::hasTable('sales')
            || ! Schema::hasColumn('sales', 'company_id')
            || ! Schema::hasColumn('sales', 'operational_status')
            || ! Schema::hasColumn('sales', $dateColumn)) {
            return 0;
        }

        return (int) DB::table('sales')
            ->where('company_id', $companyId)
            ->whereIn('operational_status', $operationalStatuses)
            ->whereDate($dateColumn, '<=', now()->subDays($minDays)->toDateString())
            ->count();
    }

    private function countMrpSuggestionsOverdue(int $companyId): int
    {
        if (! Schema::hasTable('mrp_suggestions')
            || ! Schema::hasColumn('mrp_suggestions', 'company_id')
            || ! Schema::hasColumn('mrp_suggestions', 'status')
            || ! Schema::hasColumn('mrp_suggestions', 'need_by_date')) {
            return 0;
        }

        return (int) DB::table('mrp_suggestions')
            ->where('company_id', $companyId)
            ->whereIn('status', ['GENERATED', 'IN_REVIEW', 'APPROVED'])
            ->whereDate('need_by_date', '<', now()->toDateString())
            ->count();
    }

    private function countMrpSuggestionsHighPriority(int $companyId): int
    {
        if (! Schema::hasTable('mrp_suggestions')
            || ! Schema::hasColumn('mrp_suggestions', 'company_id')
            || ! Schema::hasColumn('mrp_suggestions', 'status')
            || ! Schema::hasColumn('mrp_suggestions', 'priority')) {
            return 0;
        }

        return (int) DB::table('mrp_suggestions')
            ->where('company_id', $companyId)
            ->whereIn('status', ['GENERATED', 'IN_REVIEW', 'APPROVED'])
            ->where('priority', '<=', self::HIGH_PRIORITY_THRESHOLD)
            ->count();
    }

    private function countProductionOrdersLate(int $companyId): int
    {
        if (! Schema::hasTable('production_orders')
            || ! Schema::hasColumn('production_orders', 'company_id')
            || ! Schema::hasColumn('production_orders', 'status')
            || ! Schema::hasColumn('production_orders', 'scheduled_end_date')) {
            return 0;
        }

        return (int) DB::table('production_orders')
            ->where('company_id', $companyId)
            ->whereIn('status', ['IN_PROGRESS', 'PARTIALLY_COMPLETED'])
            ->whereDate('scheduled_end_date', '<', now()->toDateString())
            ->count();
    }

    private function countInventoryReservationsHighPriority(int $companyId, int $priorityThreshold): int
    {
        if (! Schema::hasTable('inventory_reservations')
            || ! Schema::hasColumn('inventory_reservations', 'company_id')
            || ! Schema::hasColumn('inventory_reservations', 'status')
            || ! Schema::hasColumn('inventory_reservations', 'priority')) {
            return 0;
        }

        return (int) DB::table('inventory_reservations')
            ->where('company_id', $companyId)
            ->where('status', 'RESERVED')
            ->where('priority', '<=', $priorityThreshold)
            ->count();
    }

    private function countExpiredReservationsStillActive(int $companyId): int
    {
        if (! Schema::hasTable('inventory_reservations')
            || ! Schema::hasColumn('inventory_reservations', 'company_id')
            || ! Schema::hasColumn('inventory_reservations', 'status')
            || ! Schema::hasColumn('inventory_reservations', 'expires_at')) {
            return 0;
        }

        return (int) DB::table('inventory_reservations')
            ->where('company_id', $companyId)
            ->where('status', 'RESERVED')
            ->whereNotNull('expires_at')
            ->where('expires_at', '<', now())
            ->count();
    }

    private function countOpenRequisitionLinesOverdue(int $companyId): int
    {
        if (! Schema::hasTable('purchase_requisition_lines')
            || ! Schema::hasColumn('purchase_requisition_lines', 'company_id')
            || ! Schema::hasColumn('purchase_requisition_lines', 'status')
            || ! Schema::hasColumn('purchase_requisition_lines', 'need_by_date')) {
            return 0;
        }

        return (int) DB::table('purchase_requisition_lines')
            ->where('company_id', $companyId)
            ->where('status', 'OPEN')
            ->whereDate('need_by_date', '<', now()->toDateString())
            ->count();
    }

    private function countOpenRequisitionLinesUrgent(int $companyId): int
    {
        if (! Schema::hasTable('purchase_requisition_lines')
            || ! Schema::hasColumn('purchase_requisition_lines', 'company_id')
            || ! Schema::hasColumn('purchase_requisition_lines', 'status')
            || ! Schema::hasColumn('purchase_requisition_lines', 'need_by_date')) {
            return 0;
        }

        return (int) DB::table('purchase_requisition_lines')
            ->where('company_id', $companyId)
            ->where('status', 'OPEN')
            ->whereDate('need_by_date', '<=', now()->addDays(3)->toDateString())
            ->count();
    }

    private function countPurchaseOrdersDeliveryOverdue(int $companyId): int
    {
        if (! Schema::hasTable('purchase_orders')
            || ! Schema::hasColumn('purchase_orders', 'company_id')
            || ! Schema::hasColumn('purchase_orders', 'status')
            || ! Schema::hasColumn('purchase_orders', 'expected_delivery_date')) {
            return 0;
        }

        return (int) DB::table('purchase_orders')
            ->where('company_id', $companyId)
            ->whereIn('status', ['APPROVED', 'OPEN'])
            ->whereNotNull('expected_delivery_date')
            ->whereDate('expected_delivery_date', '<', now()->toDateString())
            ->count();
    }

    private function countSalesPendingSlaBreached(int $companyId): int
    {
        if (! Schema::hasTable('sales')
            || ! Schema::hasColumn('sales', 'company_id')
            || ! Schema::hasColumn('sales', 'status')
            || ! Schema::hasColumn('sales', 'operational_status')
            || ! Schema::hasColumn('sales', 'confirmed_at')
            || ! Schema::hasColumn('sales', 'sale_date')) {
            return 0;
        }

        $limitDate = now()->subDays(self::SLA_SALES_PENDING_DAYS)->toDateString();

        return (int) DB::table('sales')
            ->where('company_id', $companyId)
            ->where('status', 'CONFIRMED')
            ->whereIn('operational_status', ['PENDING', 'PICKING', 'INVOICED', 'SHIPPED'])
            ->where(function ($query) use ($limitDate): void {
                $query
                    ->whereDate('confirmed_at', '<=', $limitDate)
                    ->orWhere(function ($subQuery) use ($limitDate): void {
                        $subQuery
                            ->whereNull('confirmed_at')
                            ->whereDate('sale_date', '<=', $limitDate);
                    });
            })
            ->count();
    }

    private function countPendingInvitationsSlaBreached(int $companyId): int
    {
        if (! Schema::hasTable('account_invitations')
            || ! Schema::hasColumn('account_invitations', 'company_id')
            || ! Schema::hasColumn('account_invitations', 'accepted_at')
            || ! Schema::hasColumn('account_invitations', 'revoked_at')
            || ! Schema::hasColumn('account_invitations', 'created_at')) {
            return 0;
        }

        return (int) DB::table('account_invitations')
            ->where('company_id', $companyId)
            ->whereNull('accepted_at')
            ->whereNull('revoked_at')
            ->whereDate('created_at', '<=', now()->subDays(self::SLA_INVITATION_DAYS)->toDateString())
            ->count();
    }

    private function countActiveCompanyUsers(int $companyId): int
    {
        if (! Schema::hasTable('company_user')
            || ! Schema::hasTable('users')
            || ! Schema::hasColumn('users', 'is_active')) {
            return 0;
        }

        return (int) DB::table('company_user')
            ->join('users', 'users.id', '=', 'company_user.user_id')
            ->where('company_user.company_id', $companyId)
            ->where('users.is_active', true)
            ->count();
    }

    /** @return array{label: string, count: int, severity: string, severity_label: string} */
    private function metric(string $label, int $count, int $attentionAt = 1, int $criticalAt = 5): array
    {
        $severity = 'normal';

        if ($count >= $criticalAt) {
            $severity = 'critical';
        } elseif ($count >= $attentionAt) {
            $severity = 'attention';
        }

        return [
            'label' => $label,
            'count' => $count,
            'severity' => $severity,
            'severity_label' => $this->severityLabel($severity),
        ];
    }

    private function severityLabel(string $severity): string
    {
        return match ($severity) {
            'critical' => __('domain_dashboard.severity.critical'),
            'attention' => __('domain_dashboard.severity.attention'),
            default => __('domain_dashboard.severity.normal'),
        };
    }
}
