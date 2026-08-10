<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Tenant;

use App\Http\Controllers\Controller;
use App\Modules\Customer\Infrastructure\Persistence\Models\Customer;
use App\Modules\Identity\Infrastructure\Persistence\Models\User;
use App\Modules\Inventory\Application\Services\InventoryService;
use App\Modules\Inventory\Infrastructure\Persistence\Models\InventoryBalance;
use App\Modules\Product\Infrastructure\Persistence\Models\Product;
use App\Modules\Sales\Application\Services\SaleFulfillmentService;
use App\Modules\Sales\Application\Services\SaleProductionStatusExportService;
use App\Modules\Sales\Application\Services\SaleProductionStatusService;
use App\Modules\Sales\Infrastructure\Persistence\Models\Sale;
use App\Modules\Sales\Infrastructure\Persistence\Models\SaleLine;
use App\Modules\Tenant\Infrastructure\Persistence\Models\Company;
use App\Services\SaaS\AuditLogService;
use App\Services\SaaS\CompanyUserAccessService;
use App\Shared\Presentation\Exceptions\DomainException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

final class SaleController extends Controller
{
    private const OPERATIONAL_TRANSITIONS = [
        'PENDING' => 'PICKING',
        'PICKING' => 'INVOICED',
        'INVOICED' => 'SHIPPED',
        'SHIPPED' => 'DELIVERED',
    ];

    private const READ_PERMISSION = 'sales.read';

    private const CREATE_PERMISSION = 'sales.create';

    private const UPDATE_PERMISSION = 'sales.update';

    public function index(Request $request): View
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensurePermission($request, self::READ_PERMISSION, $company->id);

        $search = trim((string) $request->query('search'));
        $searchTerms = preg_split('/\s+/', mb_strtolower($search), -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $status = mb_strtoupper(trim((string) $request->query('status')));
        $operationalStatus = mb_strtoupper(trim((string) $request->query('operational_status')));
        $sort = (string) $request->query('sort', 'id');
        $direction = (string) $request->query('direction', 'desc') === 'asc' ? 'asc' : 'desc';

        if (! in_array($status, ['DRAFT', 'CONFIRMED', 'CANCELLED'], true)) {
            $status = '';
        }

        if (! in_array($operationalStatus, ['PENDING', 'PICKING', 'INVOICED', 'SHIPPED', 'DELIVERED'], true)) {
            $operationalStatus = '';
        }

        abort_unless(in_array($sort, ['id', 'customer', 'sale_date', 'status', 'operational_status', 'amount_cents', 'created_at'], true), 404);

        $salesQuery = Sale::query()
            ->with('customer:id,name')
            ->when($status !== '', static fn (Builder $query) => $query->where('status', $status))
            ->when($operationalStatus !== '', static fn (Builder $query) => $query->where('operational_status', $operationalStatus))
            ->when($search !== '', fn (Builder $query) => $this->applySearchFilters($query, $searchTerms));

        if ($sort === 'customer') {
            $salesQuery->orderBy(Customer::query()->select('name')->whereColumn('customers.id', 'sales.customer_id'), $direction);
        } else {
            $salesQuery->orderBy($sort, $direction);
        }

        $sales = $salesQuery
            ->orderBy('id', $direction)
            ->paginate(15)
            ->withQueryString();

        return view('client.sales.search', compact('sales', 'search', 'sort', 'direction', 'status', 'operationalStatus', 'company'));
    }

    public function create(Request $request): View
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensurePermission($request, self::CREATE_PERMISSION, $company->id);
        $itemRows = $this->oldItemRows($request, $this->defaultItemRows());

        return view('client.sales.form', [
            'sale' => null,
            'company' => $company,
            'customers' => $this->customerOptions($company),
            'products' => $this->productOptionsByIds($company, $this->selectedProductIdsFromItemRows($itemRows)),
            'itemRows' => $itemRows,
        ]);
    }

    public function searchProducts(Request $request): JsonResponse
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensureAnyPermission($request, $company->id, [self::READ_PERMISSION, self::CREATE_PERMISSION, self::UPDATE_PERMISSION]);

        $term = trim((string) $request->query('q', ''));
        $page = max(1, (int) $request->query('page', 1));
        $perPage = 20;

        $query = Product::query()
            ->where('company_id', $company->id)
            ->select(['id', 'sku', 'description', 'is_active'])
            ->orderBy('sku');

        if ($term !== '') {
            $query->where(function (Builder $inner) use ($term): void {
                $inner
                    ->where('sku', 'like', "%{$term}%")
                    ->orWhere('description', 'like', "%{$term}%");
            });
        }

        $paginator = $query->paginate($perPage, ['id', 'sku', 'description', 'is_active'], 'page', $page);

        return response()->json([
            'results' => $paginator->getCollection()->map(fn (Product $product): array => [
                'id' => $product->id,
                'text' => sprintf(
                    '%s - %s%s',
                    $product->sku,
                    $product->description ?? '—',
                    $product->is_active ? '' : ' - '.__('sale.product_inactive')
                ),
            ])->values(),
            'pagination' => [
                'more' => $paginator->hasMorePages(),
            ],
        ]);
    }

    public function show(Request $request, Sale $sale): View
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensurePermission($request, self::READ_PERMISSION, $company->id);

        $sale->load(['customer:id,name', 'lines.product:id,sku,description,unit_id', 'lines.product.unit:id,code']);
        $sale->load([
            'confirmedBy:id,name',
            'canceledBy:id,name',
            'pickingBy:id,name',
            'invoicedBy:id,name',
            'shippedBy:id,name',
            'deliveredBy:id,name',
        ]);

        return view('client.sales.show', compact('sale', 'company'));
    }

    public function materials(Request $request, Sale $sale): RedirectResponse
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensurePermission($request, self::READ_PERMISSION, $company->id);

        return redirect()->route('sales.production-status', $sale);
    }

    public function productionStatus(
        Request $request,
        Sale $sale,
        SaleProductionStatusService $productionStatusService
    ): View {
        return view('client.sales.production-status', $this->productionStatusViewData($request, $sale, $productionStatusService));
    }

    public function productionStatusTab(
        Request $request,
        Sale $sale,
        string $tab,
        SaleProductionStatusService $productionStatusService,
    ): Response {
        abort_unless(in_array($tab, ['items', 'timeline', 'tracking'], true), 404);

        $data = $this->productionStatusViewData($request, $sale, $productionStatusService);
        $data['productionStatusTab'] = $tab;

        return response(view('client.sales.production-status', $data)->fragment('production-status-'.$tab));
    }

    /**
     * @return array{sale: Sale, analysis: array<string, mixed>, company: Company, capabilities: array<string, bool>, responsibleUsers: Collection}
     */
    private function productionStatusViewData(
        Request $request,
        Sale $sale,
        SaleProductionStatusService $productionStatusService,
    ): array {
        $company = $this->activeCompanyFrom($request);
        $this->ensurePermission($request, self::READ_PERMISSION, $company->id);

        $sale->load(['customer:id,name', 'lines.product:id,sku,description,product_type,unit_id', 'lines.product.unit:id,code']);
        $analysis = $productionStatusService->analyze($sale);
        $user = $request->user();
        $isAdministrator = $user instanceof User
            && app(CompanyUserAccessService::class)->isCompanyAdministrator($user, $company);
        $capabilities = [
            'create_production_order' => $isAdministrator
                || ($user instanceof User && $user->hasPermission('production-orders.create', $company->id)),
            'create_purchase_requisition' => $isAdministrator
                || ($user instanceof User && $user->hasPermission('purchasing.requisitions.create', $company->id)),
            'read_production_order' => $isAdministrator
                || ($user instanceof User && $user->hasPermission('production-orders.read', $company->id)),
            'read_purchase_order' => $isAdministrator
                || ($user instanceof User && $user->hasPermission('purchasing.orders.read', $company->id)),
            'reserve_stock' => $isAdministrator
                || ($user instanceof User && $user->hasPermission('inventory.update', $company->id)),
            'reschedule_production_order' => $isAdministrator
                || ($user instanceof User && $user->hasPermission('production-scheduling.run', $company->id)),
            'manage_tracking' => $isAdministrator
                || ($user instanceof User && $user->hasPermission(self::UPDATE_PERMISSION, $company->id)),
        ];
        $responsibleUsers = $company->users()->where('users.is_active', true)->orderBy('users.name')->get(['users.id', 'users.name']);

        return compact('sale', 'analysis', 'company', 'capabilities', 'responsibleUsers');
    }

    public function exportProductionStatus(
        Request $request,
        Sale $sale,
        string $format,
        SaleProductionStatusService $productionStatusService,
        SaleProductionStatusExportService $exportService,
    ): BinaryFileResponse {
        $company = $this->activeCompanyFrom($request);
        $this->ensurePermission($request, self::READ_PERMISSION, $company->id);
        abort_unless(in_array($format, ['xlsx', 'pdf'], true), 404);

        $sale->load(['customer:id,name', 'lines.product:id,sku,description,product_type,unit_id', 'lines.product.unit:id,code']);
        $analysis = $productionStatusService->analyze($sale);
        $path = $format === 'xlsx'
            ? $exportService->excel($sale, $analysis, $company->name)
            : $exportService->pdf($sale, $analysis, $company->name);

        return response()->download($path, 'venda-'.$sale->id.'-producao.'.$format)->deleteFileAfterSend(true);
    }

    public function updateProductionTracking(Request $request, Sale $sale, AuditLogService $audit): RedirectResponse
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensurePermission($request, self::UPDATE_PERMISSION, $company->id);

        $data = $request->validate([
            'promised_date' => ['nullable', 'date'],
            'responsible_user_id' => ['nullable', 'integer'],
            'comment' => ['nullable', 'string', 'max:2000'],
        ]);
        $responsibleId = (int) ($data['responsible_user_id'] ?? 0);

        if ($responsibleId > 0 && ! $company->users()->whereKey($responsibleId)->exists()) {
            throw ValidationException::withMessages(['responsible_user_id' => __('sale.production_status.invalid_responsible')]);
        }

        $metadata = (array) ($sale->metadata ?? []);
        $tracking = (array) data_get($metadata, 'production_tracking', []);
        $tracking['promised_date'] = $data['promised_date'] ?? null;
        $tracking['responsible_user_id'] = $responsibleId > 0 ? $responsibleId : null;

        if (trim((string) ($data['comment'] ?? '')) !== '') {
            $comments = (array) ($tracking['comments'] ?? []);
            $comments[] = [
                'id' => (string) Str::uuid(),
                'text' => trim((string) $data['comment']),
                'user_id' => $request->user()?->id,
                'user_name' => $request->user()?->name,
                'created_at' => now()->toIso8601String(),
            ];
            $tracking['comments'] = array_slice($comments, -100);
        }

        $metadata['production_tracking'] = $tracking;
        $sale->metadata = $metadata;
        $sale->save();

        $audit->record('tenant_sale.production_tracking.updated', context: [
            'sale_id' => $sale->id,
            'company_id' => $company->id,
            'promised_date' => $tracking['promised_date'],
            'responsible_user_id' => $tracking['responsible_user_id'],
            'comment_added' => trim((string) ($data['comment'] ?? '')) !== '',
        ], userId: $request->user()?->id, companyId: $company->id, ipAddress: $request->ip(), userAgent: $request->userAgent());

        return redirect()->route('sales.production-status', $sale)->with('status', __('sale.production_status.tracking_updated'));
    }

    public function reserveProductionMaterial(
        Request $request,
        Sale $sale,
        InventoryService $inventoryService,
        SaleProductionStatusService $productionStatusService,
        AuditLogService $audit,
    ): RedirectResponse {
        $company = $this->activeCompanyFrom($request);
        $this->ensurePermission($request, 'inventory.update', $company->id);
        $data = $request->validate([
            'sale_line_id' => ['required', 'integer'],
            'product_id' => ['required', 'integer'],
            'warehouse_id' => ['required', 'integer'],
            'quantity' => ['required', 'numeric', 'gt:0'],
        ]);
        $line = $sale->lines()->whereKey((int) $data['sale_line_id'])->firstOrFail();
        $sale->load(['lines.product.unit']);
        $item = collect($productionStatusService->analyze($sale)['items'])->firstWhere('line_id', (int) $line->id);
        $material = collect($item['materials'] ?? [])->firstWhere('product_id', (int) $data['product_id']);
        $balance = InventoryBalance::query()
            ->where('warehouse_id', (int) $data['warehouse_id'])
            ->where('product_id', (int) $data['product_id'])
            ->first();

        if ($material === null || $balance === null || (float) $data['quantity'] > (float) $material['available_quantity'] + 0.000001) {
            return redirect()->route('sales.production-status', $sale)->withErrors([
                'reservation' => __('sale.production_status.invalid_reservation'),
            ]);
        }

        try {
            $inventoryService->reserveStock([
                'warehouse_id' => (int) $data['warehouse_id'],
                'product_id' => (int) $data['product_id'],
                'reservation_origin' => 'PRODUCTION',
                'priority' => 20,
                'quantity' => (float) $data['quantity'],
                'reference_type' => 'sale',
                'reference_id' => (int) $sale->id,
                'notes' => __('sale.production_status.reservation_note', ['sale' => $sale->id]),
                'metadata' => [
                    'sale_id' => (int) $sale->id,
                    'sale_line_id' => (int) $line->id,
                    'allocation_scope' => sprintf('sale:%d', (int) $sale->id),
                    'allocation_type' => 'production_component',
                ],
            ], $request->user()?->id);
        } catch (DomainException) {
            return redirect()->route('sales.production-status', $sale)->withErrors([
                'reservation' => __('sale.production_status.invalid_reservation'),
            ]);
        }

        $audit->record('tenant_sale.production_material_reserved', context: [
            'sale_id' => $sale->id,
            'company_id' => $company->id,
            'sale_line_id' => $line->id,
            'product_id' => (int) $data['product_id'],
            'quantity' => (float) $data['quantity'],
        ], userId: $request->user()?->id, companyId: $company->id, ipAddress: $request->ip(), userAgent: $request->userAgent());

        return redirect()->route('sales.production-status', $sale)->with('status', __('sale.production_status.reserved'));
    }

    public function store(Request $request, AuditLogService $audit, SaleFulfillmentService $saleFulfillment): RedirectResponse
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensurePermission($request, self::CREATE_PERMISSION, $company->id);

        $data = $this->validateSale($request, $company);

        $sale = DB::transaction(function () use ($company, $data, $request, $saleFulfillment): Sale {
            $sale = Sale::query()->create([
                'company_id' => $company->id,
                'customer_id' => $data['customer_id'],
                'sale_date' => $data['sale_date'],
                'status' => $data['status'],
                'operational_status' => 'PENDING',
                'subtotal_cents' => $data['subtotal_cents'],
                'discount_cents' => $data['discount_cents'],
                'amount_cents' => $data['amount_cents'],
                'notes' => $data['notes'] ?? null,
            ]);

            $this->applyCommercialStatusAudit($sale, $data['status'], $data['cancel_reason'] ?? null, $request->user()?->id);
            $sale->save();

            $this->syncLines($sale, $data['items']);

            if ($sale->status === 'CONFIRMED') {
                $saleFulfillment->fulfillConfirmedSale($sale, $request->user()?->id);
            }

            return $sale;
        });

        $audit->record(
            'tenant_sale.created',
            context: [
                'sale_id' => $sale->id,
                'company_id' => $company->id,
                'actor_user_id' => $request->user()?->id,
            ],
            userId: $request->user()?->id,
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return redirect()->route('sales.index')->with('status', __('sale.created'));
    }

    public function edit(Request $request, Sale $sale): View|RedirectResponse
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensurePermission($request, self::UPDATE_PERMISSION, $company->id);

        if ($this->isLockedForEditing($sale)) {
            return redirect()
                ->route('sales.show', $sale)
                ->with('error', __('sale.edit_locked'));
        }

        $itemRows = $this->oldItemRows($request, $this->itemRowsForForm($sale));

        return view('client.sales.form', [
            'sale' => $sale,
            'company' => $company,
            'customers' => $this->customerOptions($company),
            'products' => $this->productOptionsByIds($company, $this->selectedProductIdsFromItemRows($itemRows)),
            'itemRows' => $itemRows,
        ]);
    }

    public function update(Request $request, Sale $sale, AuditLogService $audit, SaleFulfillmentService $saleFulfillment): RedirectResponse
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensurePermission($request, self::UPDATE_PERMISSION, $company->id);

        if ((string) $request->input('status') === 'CANCELLED' && in_array($sale->operational_status, ['INVOICED', 'SHIPPED', 'DELIVERED'], true)) {
            return redirect()
                ->route('sales.show', $sale)
                ->withErrors(['status' => __('sale.cancel_after_invoiced_forbidden')]);
        }

        if ($this->isLockedForEditing($sale)) {
            return redirect()
                ->route('sales.show', $sale)
                ->with('error', __('sale.edit_locked'));
        }

        $data = $this->validateSale($request, $company, $sale);

        DB::transaction(function () use ($sale, $data, $request, $saleFulfillment): void {
            $previousStatus = $sale->status;

            $sale->fill([
                'customer_id' => $data['customer_id'],
                'sale_date' => $data['sale_date'],
                'status' => $data['status'],
                'subtotal_cents' => $data['subtotal_cents'],
                'discount_cents' => $data['discount_cents'],
                'amount_cents' => $data['amount_cents'],
                'notes' => $data['notes'] ?? null,
                'cancel_reason' => $data['cancel_reason'] ?? null,
            ]);
            $this->applyCommercialStatusAudit($sale, $data['status'], $data['cancel_reason'] ?? null, $request->user()?->id);
            $sale->save();

            $this->syncLines($sale, $data['items']);

            if ($sale->status === 'CONFIRMED' && $previousStatus !== 'CONFIRMED') {
                $saleFulfillment->fulfillConfirmedSale($sale, $request->user()?->id);
            }

            if ($sale->status === 'CANCELLED' && $previousStatus === 'CONFIRMED') {
                $saleFulfillment->releaseReservationsForCanceledSale($sale, $request->user()?->id);
            }
        });

        $audit->record(
            'tenant_sale.updated',
            context: [
                'sale_id' => $sale->id,
                'company_id' => $company->id,
                'actor_user_id' => $request->user()?->id,
            ],
            userId: $request->user()?->id,
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return redirect()->route('sales.index')->with('status', __('sale.updated'));
    }

    public function transition(Request $request, Sale $sale, AuditLogService $audit): RedirectResponse
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensurePermission($request, self::UPDATE_PERMISSION, $company->id);

        $data = $request->validate([
            'next_operational_status' => ['required', Rule::in(['PICKING', 'INVOICED', 'SHIPPED', 'DELIVERED'])],
        ]);

        $nextStatus = (string) $data['next_operational_status'];
        $expectedNextStatus = self::OPERATIONAL_TRANSITIONS[$sale->operational_status] ?? null;

        if ($sale->status !== 'CONFIRMED') {
            return redirect()
                ->route('sales.show', $sale)
                ->with('error', __('sale.transition_requires_confirmed'));
        }

        if ($sale->status === 'CANCELLED' || $expectedNextStatus === null || $expectedNextStatus !== $nextStatus) {
            return redirect()
                ->route('sales.show', $sale)
                ->with('error', __('sale.invalid_transition'));
        }

        if (($prerequisiteError = $this->transitionPrerequisiteError($sale, $nextStatus)) !== null) {
            return redirect()
                ->route('sales.show', $sale)
                ->with('error', $prerequisiteError);
        }

        $fromStatus = $sale->operational_status;
        $this->applyOperationalTransitionAudit($sale, $nextStatus, $request->user()?->id);
        $sale->save();

        $audit->record(
            'tenant_sale.operational_status_transitioned',
            context: [
                'sale_id' => $sale->id,
                'company_id' => $company->id,
                'from_status' => $fromStatus,
                'to_status' => $nextStatus,
                'actor_user_id' => $request->user()?->id,
            ],
            userId: $request->user()?->id,
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return redirect()
            ->route('sales.show', $sale)
            ->with('status', __('sale.transitioned', ['status' => $this->operationalStatusLabel($nextStatus)]));
    }

    public function destroy(Request $request, Sale $sale, AuditLogService $audit): RedirectResponse
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensurePermission($request, self::UPDATE_PERMISSION, $company->id);

        if ($this->isLockedForEditing($sale)) {
            return redirect()
                ->route('sales.show', $sale)
                ->with('error', __('sale.delete_locked'));
        }

        $saleId = $sale->id;
        $sale->delete();

        $audit->record(
            'tenant_sale.removed',
            context: [
                'sale_id' => $saleId,
                'company_id' => $company->id,
                'actor_user_id' => $request->user()?->id,
            ],
            userId: $request->user()?->id,
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return redirect()->route('sales.index')->with('status', __('sale.removed'));
    }

    private function activeCompanyFrom(Request $request): Company
    {
        $companyId = (int) ($request->user()?->current_company_id ?? 0);

        abort_unless($companyId > 0, 404);

        return Company::query()->findOrFail($companyId);
    }

    private function ensurePermission(Request $request, string $permission, int $companyId): void
    {
        $user = $request->user();

        if (! $user instanceof User) {
            abort(403);
        }

        $company = Company::query()->findOrFail($companyId);

        if (app(CompanyUserAccessService::class)->isCompanyAdministrator($user, $company)) {
            return;
        }

        abort_unless($user->hasPermission($permission, $companyId), 403);
    }

    /**
     * @param  list<string>  $permissions
     */
    private function ensureAnyPermission(Request $request, int $companyId, array $permissions): void
    {
        $user = $request->user();

        if (! $user instanceof User) {
            abort(403);
        }

        $company = Company::query()->findOrFail($companyId);

        if (app(CompanyUserAccessService::class)->isCompanyAdministrator($user, $company)) {
            return;
        }

        foreach ($permissions as $permission) {
            if ($user->hasPermission($permission, $companyId)) {
                return;
            }
        }

        abort(403);
    }

    private function validateSale(Request $request, Company $company, ?Sale $sale = null): array
    {
        $data = $request->validate([
            'customer_id' => [
                'required',
                'integer',
                Rule::exists('customers', 'id')->where(static fn ($query) => $query->where('company_id', $company->id)),
            ],
            'sale_date' => ['required', 'date'],
            'status' => ['required', Rule::in(['DRAFT', 'CONFIRMED', 'CANCELLED'])],
            'discount_amount' => ['nullable', 'string', 'max:30'],
            'cancel_reason' => ['nullable', 'string', 'max:2000', 'required_if:status,CANCELLED'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => [
                'required',
                'integer',
                Rule::exists('products', 'id')->where(static fn ($query) => $query->where('company_id', $company->id)),
            ],
            'items.*.quantity' => ['required', 'numeric', 'gt:0'],
            'items.*.unit_price' => ['required', 'string', 'max:30'],
        ]);

        $items = collect($data['items'])
            ->map(function (array $item): array {
                $quantity = round((float) $item['quantity'], 6);
                $unitPrice = $this->normalizeAmountToDecimal((string) $item['unit_price']);

                return [
                    'product_id' => (int) $item['product_id'],
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'line_total_cents' => $this->lineTotalToCents($quantity, $unitPrice),
                ];
            })->values();

        if (($sale?->operational_status !== null) && $data['status'] === 'CANCELLED' && in_array($sale->operational_status, ['INVOICED', 'SHIPPED', 'DELIVERED'], true)) {
            throw ValidationException::withMessages([
                'status' => __('sale.cancel_after_invoiced_forbidden'),
            ]);
        }

        $data['items'] = $items->all();
        $data['subtotal_cents'] = (int) $items->sum('line_total_cents');
        $data['discount_cents'] = $this->normalizeAmountToCents((string) ($data['discount_amount'] ?? '0'), 'discount_amount');
        $data['amount_cents'] = $data['subtotal_cents'] - $data['discount_cents'];

        if ($data['amount_cents'] < 0) {
            throw ValidationException::withMessages([
                'discount_amount' => __('sale.invalid_discount_total'),
            ]);
        }

        unset($data['discount_amount']);

        return $data;
    }

    private function normalizeAmountToCents(string $rawAmount, string $field): int
    {
        return (int) round($this->normalizeAmountToDecimal($rawAmount, $field) * 100);
    }

    private function normalizeAmountToDecimal(string $rawAmount, string $field = 'items'): float
    {
        $normalized = trim($rawAmount);
        $normalized = str_replace(' ', '', $normalized);

        if (str_contains($normalized, ',')) {
            $normalized = str_replace('.', '', $normalized);
            $normalized = str_replace(',', '.', $normalized);
        }

        if (! is_numeric($normalized)) {
            throw ValidationException::withMessages([
                $field => __('sale.invalid_amount'),
            ]);
        }

        $value = round((float) $normalized, 6);

        if ($value < 0) {
            throw ValidationException::withMessages([
                $field => __('sale.invalid_amount'),
            ]);
        }

        return $value;
    }

    private function customerOptions(Company $company): Collection
    {
        return Customer::query()
            ->where('company_id', $company->id)
            ->orderBy('name')
            ->get(['id', 'name', 'status']);
    }

    private function productOptionsByIds(Company $company, array $ids): Collection
    {
        if ($ids === []) {
            return collect();
        }

        return Product::query()
            ->where('company_id', $company->id)
            ->whereIn('id', $ids)
            ->orderBy('sku')
            ->with('unit:id,code')
            ->get(['id', 'sku', 'description', 'unit_id', 'is_active']);
    }

    private function oldItemRows(Request $request, array $fallback): array
    {
        $items = $request->old('items');

        return is_array($items) && $items !== [] ? array_values($items) : $fallback;
    }

    private function selectedProductIdsFromItemRows(array $itemRows): array
    {
        return collect($itemRows)
            ->pluck('product_id')
            ->filter(static fn ($id): bool => (int) $id > 0)
            ->map(static fn ($id): int => (int) $id)
            ->unique()
            ->values()
            ->all();
    }

    private function defaultItemRows(): array
    {
        return [['product_id' => null, 'quantity' => 1, 'unit_price' => null]];
    }

    private function itemRowsForForm(Sale $sale): array
    {
        $sale->loadMissing(['lines.product:id,sku,description,unit_id', 'lines.product.unit:id,code']);

        $rows = $sale->lines
            ->map(static fn (SaleLine $line): array => [
                'product_id' => $line->product_id,
                'quantity' => $line->quantity,
                'unit_price' => number_format($line->unit_price, 2, ',', '.'),
            ])
            ->all();

        return $rows !== [] ? $rows : $this->defaultItemRows();
    }

    private function syncLines(Sale $sale, array $items): void
    {
        $sale->lines()->delete();

        foreach ($items as $item) {
            SaleLine::query()->create([
                'company_id' => $sale->company_id,
                'sale_id' => $sale->id,
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
            ]);
        }
    }

    private function lineTotalToCents(float $quantity, float $unitPrice): int
    {
        return (int) round(($quantity * $unitPrice) * 100);
    }

    private function isLockedForEditing(Sale $sale): bool
    {
        return in_array($sale->operational_status, ['INVOICED', 'SHIPPED', 'DELIVERED'], true);
    }

    private function nextOperationalStatus(Sale $sale): ?string
    {
        if ($sale->status !== 'CONFIRMED') {
            return null;
        }

        return self::OPERATIONAL_TRANSITIONS[$sale->operational_status] ?? null;
    }

    private function operationalStatusLabel(string $status): string
    {
        return match ($status) {
            'PICKING' => __('sale.operational_status_picking'),
            'INVOICED' => __('sale.operational_status_invoiced'),
            'SHIPPED' => __('sale.operational_status_shipped'),
            'DELIVERED' => __('sale.operational_status_delivered'),
            default => __('sale.operational_status_pending'),
        };
    }

    private function applyOperationalTransitionAudit(Sale $sale, string $nextStatus, ?int $userId): void
    {
        $sale->operational_status = $nextStatus;

        match ($nextStatus) {
            'PICKING' => $sale->forceFill([
                'picking_at' => $sale->picking_at ?? now(),
                'picking_by' => $sale->picking_by ?? $userId,
            ]),
            'INVOICED' => $sale->forceFill([
                'invoiced_at' => $sale->invoiced_at ?? now(),
                'invoiced_by' => $sale->invoiced_by ?? $userId,
            ]),
            'SHIPPED' => $sale->forceFill([
                'shipped_at' => $sale->shipped_at ?? now(),
                'shipped_by' => $sale->shipped_by ?? $userId,
            ]),
            'DELIVERED' => $sale->forceFill([
                'delivered_at' => $sale->delivered_at ?? now(),
                'delivered_by' => $sale->delivered_by ?? $userId,
            ]),
            default => null,
        };
    }

    private function applyCommercialStatusAudit(Sale $sale, string $status, ?string $cancelReason, ?int $userId): void
    {
        $sale->status = $status;

        if ($status === 'CONFIRMED') {
            $sale->confirmed_at ??= now();
            $sale->confirmed_by ??= $userId;
            $sale->cancel_reason = null;
            $sale->canceled_at = null;
            $sale->canceled_by = null;

            return;
        }

        if ($status === 'CANCELLED') {
            $sale->cancel_reason = $cancelReason;
            $sale->canceled_at ??= now();
            $sale->canceled_by ??= $userId;

            return;
        }

        if ($status === 'DRAFT') {
            $sale->cancel_reason = null;
            $sale->canceled_at = null;
            $sale->canceled_by = null;
        }
    }

    private function transitionPrerequisiteError(Sale $sale, string $nextStatus): ?string
    {
        return match ($nextStatus) {
            'INVOICED' => $sale->picking_at === null
                ? __('sale.transition_requires_picking_record')
                : null,
            'SHIPPED' => $sale->invoiced_at === null
                ? __('sale.transition_requires_invoiced_record')
                : null,
            'DELIVERED' => $sale->shipped_at === null
                ? __('sale.transition_requires_shipped_record')
                : null,
            default => null,
        };
    }

    private function applySearchFilters(Builder $query, array $searchTerms): void
    {
        $query->where(function (Builder $nestedQuery) use ($searchTerms): void {
            foreach ($searchTerms as $term) {
                $nestedQuery->where(function (Builder $searchQuery) use ($term): void {
                    $searchQuery
                        ->orWhereRaw('LOWER(status) LIKE ?', ['%'.$term.'%'])
                        ->orWhereRaw('LOWER(operational_status) LIKE ?', ['%'.$term.'%'])
                        ->orWhereRaw("LOWER(COALESCE(notes, '')) LIKE ?", ['%'.$term.'%'])
                        ->orWhereHas('customer', static fn (Builder $customerQuery) => $customerQuery->whereRaw('LOWER(name) LIKE ?', ['%'.$term.'%']));

                    if (ctype_digit($term)) {
                        $searchQuery->orWhere('id', (int) $term);
                    }
                });
            }
        });
    }
}
