<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\Tenant\Concerns\HandlesTenantAuthorization;
use App\Modules\Product\Infrastructure\Persistence\Models\Product;
use App\Modules\Purchasing\Infrastructure\Persistence\Models\PurchaseRequisition;
use App\Modules\Purchasing\Infrastructure\Persistence\Models\PurchaseRequisitionLine;
use App\Modules\Purchasing\Infrastructure\Persistence\Models\Supplier;
use App\Modules\Purchasing\Infrastructure\Persistence\Models\SupplierProduct;
use App\Modules\Sales\Infrastructure\Persistence\Models\SaleLine;
use App\Modules\Sales\Infrastructure\Persistence\Models\Sale;
use App\Modules\Tenant\Infrastructure\Persistence\Models\Company;
use App\Modules\Tenant\Infrastructure\Persistence\Models\Warehouse;
use App\Services\SaaS\AuditLogService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

final class PurchaseRequisitionController extends Controller
{
    use HandlesTenantAuthorization;

    private const STATUS_TRANSITIONS = [
        'DRAFT' => ['APPROVED', 'CANCELLED'],
        'APPROVED' => ['CANCELLED'],
        'CANCELLED' => [],
    ];

    private const READ_PERMISSION = 'purchasing.requisitions.read';

    private const CREATE_PERMISSION = 'purchasing.requisitions.create';

    private const UPDATE_PERMISSION = 'purchasing.requisitions.update';

    public function index(Request $request): View
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensurePermission($request, self::READ_PERMISSION, $company->id);

        $search = trim((string) $request->query('search'));
        $searchId = ctype_digit($search) ? (int) $search : null;
        $status = mb_strtoupper(trim((string) $request->query('status')));
        $sort = (string) $request->query('sort', 'id');
        $direction = (string) $request->query('direction', 'desc') === 'asc' ? 'asc' : 'desc';

        if (! in_array($status, ['DRAFT', 'APPROVED', 'CANCELLED'], true)) {
            $status = '';
        }

        abort_unless(in_array($sort, ['id', 'requisition_number', 'required_date', 'status', 'lines_count', 'created_at'], true), 404);

        $requisitions = PurchaseRequisition::query()
            ->withCount('lines')
            ->when($status !== '', static fn (Builder $query) => $query->where('status', $status))
            ->when($search !== '', static function (Builder $query) use ($search, $searchId): void {
                $query->where(function (Builder $nested) use ($search): void {
                    $nested
                        ->where('requisition_number', 'like', "%{$search}%")
                        ->orWhere('notes', 'like', "%{$search}%");
                });

                if ($searchId !== null) {
                    $query->orWhere('id', $searchId);
                }
            })
            ->orderBy($sort, $direction)
            ->paginate(15)
            ->withQueryString();

        return view('client.purchasing.requisitions.search', compact('requisitions', 'search', 'status', 'sort', 'direction', 'company'));
    }

    public function create(Request $request): View
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensurePermission($request, self::CREATE_PERMISSION, $company->id);

        $prefill = $this->salePrefill($request, $company);
        $lineRows = $this->oldItemRows($request, $prefill['lines'] ?? $this->defaultLineRows());

        return view('client.purchasing.requisitions.form', [
            'requisition' => null,
            'company' => $company,
            'products' => $this->productOptionsByIds($company, $this->selectedProductIdsFromLineRows($lineRows)),
            'warehouses' => $this->warehouseOptionsByIds($company, $this->selectedWarehouseIdsFromLineRows($lineRows)),
            'suppliers' => $this->supplierOptionsByIds($company, $this->selectedSupplierIdsFromLineRows($lineRows)),
            'lineRows' => $lineRows,
            'creationContext' => $prefill['context'] ?? null,
            'initialValues' => [
                'required_date' => $prefill['required_date'] ?? null,
                'source_type' => $prefill !== null ? 'sale' : 'manual',
                'notes' => $prefill !== null ? __('purchase_requisition.sale_context_notes', ['sale' => $prefill['context']['sale_id']]) : null,
            ],
        ]);
    }

    public function show(Request $request, PurchaseRequisition $requisition): View
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensurePermission($request, self::READ_PERMISSION, $company->id);

        $requisition->loadCount('lines');
        $requisition->load([
            'lines.product:id,sku,description',
            'lines.warehouse:id,code,name',
            'lines.supplier:id,code,name',
        ]);

        return view('client.purchasing.requisitions.show', compact('requisition', 'company'));
    }

    public function store(Request $request, AuditLogService $audit): RedirectResponse
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensurePermission($request, self::CREATE_PERMISSION, $company->id);

        $data = $this->validateRequisition($request);

        $requisition = DB::transaction(function () use ($company, $data, $request): PurchaseRequisition {
            $header = PurchaseRequisition::query()->create([
                'company_id' => $company->id,
                'requisition_number' => $this->generateNumber($company),
                'required_date' => $data['required_date'] ?? null,
                'status' => 'DRAFT',
                'source_type' => $data['source_type'] ?? 'manual',
                'source_reference_id' => $data['source_reference_id'] ?? null,
                'source_reference_type' => $data['source_reference_type'] ?? null,
                'requested_by' => $request->user()?->id,
                'notes' => $data['notes'] ?? null,
            ]);

            $this->syncLines($header, $data['items']);
            $this->applyStatusAudit($header, $data['status'], $request->user()?->id);
            $header->save();

            return $header;
        });

        $audit->record(
            'tenant_purchase_requisition.created',
            context: [
                'purchase_requisition_id' => $requisition->id,
                'company_id' => $company->id,
                'actor_user_id' => $request->user()?->id,
            ],
            userId: $request->user()?->id,
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return redirect()->route('purchasing.requisitions.index')->with('status', __('purchase_requisition.created'));
    }

    public function edit(Request $request, PurchaseRequisition $requisition): View
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensurePermission($request, self::UPDATE_PERMISSION, $company->id);

        $lineRows = $this->oldItemRows($request, $this->lineRowsForForm($requisition));

        return view('client.purchasing.requisitions.form', [
            'requisition' => $requisition,
            'company' => $company,
            'products' => $this->productOptionsByIds($company, $this->selectedProductIdsFromLineRows($lineRows)),
            'warehouses' => $this->warehouseOptionsByIds($company, $this->selectedWarehouseIdsFromLineRows($lineRows)),
            'suppliers' => $this->supplierOptionsByIds($company, $this->selectedSupplierIdsFromLineRows($lineRows)),
            'lineRows' => $lineRows,
            'creationContext' => null,
            'initialValues' => [
                'required_date' => null,
                'source_type' => 'manual',
                'notes' => null,
            ],
        ]);
    }

    public function update(Request $request, PurchaseRequisition $requisition, AuditLogService $audit): RedirectResponse
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensurePermission($request, self::UPDATE_PERMISSION, $company->id);

        $data = $this->validateRequisition($request);

        DB::transaction(function () use ($requisition, $data, $request): void {
            $this->assertStatusTransition($requisition->status, $data['status']);

            $requisition->fill([
                'required_date' => $data['required_date'] ?? null,
                'source_type' => $data['source_type'] ?? 'manual',
                'notes' => $data['notes'] ?? null,
            ]);

            $this->syncLines($requisition, $data['items']);
            $this->applyStatusAudit($requisition, $data['status'], $request->user()?->id);
            $requisition->save();
        });

        $audit->record(
            'tenant_purchase_requisition.updated',
            context: [
                'purchase_requisition_id' => $requisition->id,
                'company_id' => $company->id,
                'actor_user_id' => $request->user()?->id,
            ],
            userId: $request->user()?->id,
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return redirect()->route('purchasing.requisitions.index')->with('status', __('purchase_requisition.updated'));
    }

    public function destroy(Request $request, PurchaseRequisition $requisition, AuditLogService $audit): RedirectResponse
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensurePermission($request, self::UPDATE_PERMISSION, $company->id);

        $requisitionId = $requisition->id;
        $requisitionNumber = $requisition->requisition_number;
        $requisition->delete();

        $audit->record(
            'tenant_purchase_requisition.removed',
            context: [
                'purchase_requisition_id' => $requisitionId,
                'purchase_requisition_number' => $requisitionNumber,
                'company_id' => $company->id,
                'actor_user_id' => $request->user()?->id,
            ],
            userId: $request->user()?->id,
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return redirect()->route('purchasing.requisitions.index')->with('status', __('purchase_requisition.removed'));
    }

    /**
     * @return array{required_date?: string|null, status: string, source_type?: string|null, notes?: string|null, items: array<int, array{product_id: int, warehouse_id: int|null, supplier_id: int|null, quantity: float, need_by_date: string, order_date: string}>}
     */
    private function validateRequisition(Request $request): array
    {
        $data = $request->validate([
            'required_date' => ['nullable', 'date'],
            'status' => ['required', Rule::in(['DRAFT', 'APPROVED', 'CANCELLED'])],
            'source_type' => ['nullable', 'string', 'max:80'],
            'source_reference_id' => ['nullable', 'integer', 'required_with:source_reference_type'],
            'source_reference_type' => ['nullable', 'string', 'required_with:source_reference_id', Rule::in(['sale'])],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', Rule::exists('products', 'id')],
            'items.*.warehouse_id' => ['nullable', 'integer', Rule::exists('warehouses', 'id')],
            'items.*.supplier_id' => ['nullable', 'integer', Rule::exists('suppliers', 'id')],
            'items.*.quantity' => ['required', 'numeric', 'gt:0'],
            'items.*.need_by_date' => ['required', 'date'],
            'items.*.order_date' => ['required', 'date'],
        ]);

        $data['items'] = collect($data['items'])
            ->map(static fn (array $item): array => [
                'product_id' => (int) $item['product_id'],
                'warehouse_id' => isset($item['warehouse_id']) ? (int) $item['warehouse_id'] : null,
                'supplier_id' => isset($item['supplier_id']) ? (int) $item['supplier_id'] : null,
                'quantity' => round((float) $item['quantity'], 6),
                'need_by_date' => (string) $item['need_by_date'],
                'order_date' => (string) $item['order_date'],
            ])
            ->all();

        if (($data['source_reference_type'] ?? null) === 'sale'
            && ! Sale::query()->whereKey((int) $data['source_reference_id'])->exists()) {
            throw ValidationException::withMessages([
                'source_reference_id' => __('purchase_requisition.invalid_sale_context'),
            ]);
        }

        return $data;
    }

    /** @return array<string, mixed>|null */
    private function salePrefill(Request $request, Company $company): ?array
    {
        $saleId = (int) $request->query('sale_id', 0);
        $saleLineId = (int) $request->query('sale_line_id', 0);
        $productId = (int) $request->query('product_id', 0);
        $quantity = round((float) $request->query('quantity', 0), 6);

        if ($saleId <= 0 || $saleLineId <= 0 || $productId <= 0 || $quantity <= 0) {
            return null;
        }

        $saleLine = SaleLine::query()->whereKey($saleLineId)->where('sale_id', $saleId)->first();
        $product = Product::query()->where('company_id', $company->id)->find($productId);

        if (! $saleLine instanceof SaleLine || ! $product instanceof Product) {
            return null;
        }

        $supplierRule = SupplierProduct::query()
            ->where('product_id', $productId)
            ->where('is_active', true)
            ->orderByDesc('is_preferred')
            ->orderByDesc('id')
            ->first(['supplier_id', 'lead_time_days']);
        $needByDate = now()->addDays(max(1, (int) ($supplierRule?->lead_time_days ?? 7)))->toDateString();
        $warehouseId = (int) $request->query('warehouse_id', 0);

        if ($warehouseId > 0 && ! Warehouse::query()->where('company_id', $company->id)->whereKey($warehouseId)->exists()) {
            $warehouseId = 0;
        }

        return [
            'context' => [
                'sale_id' => $saleId,
                'sale_line_id' => $saleLineId,
            ],
            'required_date' => $needByDate,
            'lines' => [[
                'product_id' => $productId,
                'warehouse_id' => $warehouseId > 0 ? $warehouseId : null,
                'supplier_id' => $supplierRule?->supplier_id,
                'quantity' => $quantity,
                'need_by_date' => $needByDate,
                'order_date' => now()->toDateString(),
            ]],
        ];
    }

    /**
     * @param array<int, array{product_id: int, warehouse_id: int|null, supplier_id: int|null, quantity: float, need_by_date: string, order_date: string}> $items
     */
    private function syncLines(PurchaseRequisition $requisition, array $items): void
    {
        $requisition->lines()->delete();

        foreach ($items as $item) {
            PurchaseRequisitionLine::query()->create([
                'company_id' => $requisition->company_id,
                'purchase_requisition_id' => $requisition->id,
                'product_id' => $item['product_id'],
                'warehouse_id' => $item['warehouse_id'],
                'supplier_id' => $item['supplier_id'],
                'suggested_quantity' => $item['quantity'],
                'requested_quantity' => $item['quantity'],
                'moq_applied' => 1,
                'lead_time_days' => 0,
                'need_by_date' => $item['need_by_date'],
                'order_date' => $item['order_date'],
                'status' => 'OPEN',
            ]);
        }
    }

    private function assertStatusTransition(string $from, string $to): void
    {
        if ($from === $to) {
            return;
        }

        $allowed = self::STATUS_TRANSITIONS[$from] ?? [];

        if (! in_array($to, $allowed, true)) {
            throw ValidationException::withMessages([
                'status' => __('purchase_requisition.invalid_transition'),
            ]);
        }
    }

    private function applyStatusAudit(PurchaseRequisition $requisition, string $status, ?int $userId): void
    {
        $requisition->status = $status;

        if ($status === 'APPROVED') {
            $requisition->approved_by ??= $userId;
            $requisition->approved_at ??= now();
            $requisition->cancelled_by = null;
            $requisition->cancelled_at = null;

            return;
        }

        if ($status === 'CANCELLED') {
            $requisition->cancelled_by ??= $userId;
            $requisition->cancelled_at ??= now();

            return;
        }

        $requisition->approved_by = null;
        $requisition->approved_at = null;
        $requisition->cancelled_by = null;
        $requisition->cancelled_at = null;
    }

    /**
     * @return array<int, array{product_id: int|null, warehouse_id: int|null, supplier_id: int|null, quantity: string|float|int, need_by_date: string, order_date: string}>
     */
    private function defaultLineRows(): array
    {
        return [[
            'product_id' => null,
            'warehouse_id' => null,
            'supplier_id' => null,
            'quantity' => 1,
            'need_by_date' => now()->addDays(7)->toDateString(),
            'order_date' => now()->toDateString(),
        ]];
    }

    /**
     * @return array<int, array{product_id: int|null, warehouse_id: int|null, supplier_id: int|null, quantity: string, need_by_date: string, order_date: string}>
     */
    private function lineRowsForForm(PurchaseRequisition $requisition): array
    {
        $requisition->loadMissing('lines');

        $rows = $requisition->lines
            ->map(static fn (PurchaseRequisitionLine $line): array => [
                'product_id' => $line->product_id,
                'warehouse_id' => $line->warehouse_id,
                'supplier_id' => $line->supplier_id,
                'quantity' => (string) $line->requested_quantity,
                'need_by_date' => $line->need_by_date?->format('Y-m-d') ?? now()->toDateString(),
                'order_date' => $line->order_date?->format('Y-m-d') ?? now()->toDateString(),
            ])->all();

        return $rows !== [] ? $rows : $this->defaultLineRows();
    }

    /**
     * @param array<int, mixed> $fallback
     * @return array<int, mixed>
     */
    private function oldItemRows(Request $request, array $fallback): array
    {
        $items = $request->old('items');

        return is_array($items) && $items !== [] ? array_values($items) : $fallback;
    }

    /**
     * @param array<int, array<string, mixed>> $lineRows
     * @return array<int, int>
     */
    private function selectedProductIdsFromLineRows(array $lineRows): array
    {
        return collect($lineRows)
            ->pluck('product_id')
            ->filter(static fn ($id): bool => (int) $id > 0)
            ->map(static fn ($id): int => (int) $id)
            ->unique()
            ->values()
            ->all();
    }

    /**
     * @param array<int, array<string, mixed>> $lineRows
     * @return array<int, int>
     */
    private function selectedWarehouseIdsFromLineRows(array $lineRows): array
    {
        return collect($lineRows)
            ->pluck('warehouse_id')
            ->filter(static fn ($id): bool => (int) $id > 0)
            ->map(static fn ($id): int => (int) $id)
            ->unique()
            ->values()
            ->all();
    }

    /**
     * @param array<int, array<string, mixed>> $lineRows
     * @return array<int, int>
     */
    private function selectedSupplierIdsFromLineRows(array $lineRows): array
    {
        return collect($lineRows)
            ->pluck('supplier_id')
            ->filter(static fn ($id): bool => (int) $id > 0)
            ->map(static fn ($id): int => (int) $id)
            ->unique()
            ->values()
            ->all();
    }

    /**
     * @param array<int, int> $ids
     */
    private function productOptionsByIds(Company $company, array $ids): Collection
    {
        return Product::query()
            ->where('company_id', $company->id)
            ->when($ids !== [], static fn (Builder $query) => $query->whereIn('id', $ids))
            ->orderBy('sku')
            ->get(['id', 'sku', 'description']);
    }

    /**
     * @param array<int, int> $ids
     */
    private function warehouseOptionsByIds(Company $company, array $ids): Collection
    {
        return Warehouse::query()
            ->where('company_id', $company->id)
            ->when($ids !== [], static fn (Builder $query) => $query->whereIn('id', $ids))
            ->orderBy('code')
            ->get(['id', 'code', 'name']);
    }

    /**
     * @param array<int, int> $ids
     */
    private function supplierOptionsByIds(Company $company, array $ids): Collection
    {
        return Supplier::query()
            ->where('company_id', $company->id)
            ->when($ids !== [], static fn (Builder $query) => $query->whereIn('id', $ids))
            ->orderBy('name')
            ->get(['id', 'code', 'name']);
    }

    private function generateNumber(Company $company): string
    {
        do {
            $number = 'REQ-'.strtoupper(bin2hex(random_bytes(3)));
        } while (PurchaseRequisition::query()->where('company_id', $company->id)->where('requisition_number', $number)->exists());

        return $number;
    }

}
