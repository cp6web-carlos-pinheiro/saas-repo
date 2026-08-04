<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\Tenant\Concerns\HandlesTenantAuthorization;
use App\Modules\Product\Infrastructure\Persistence\Models\Product;
use App\Modules\Purchasing\Infrastructure\Persistence\Models\PurchaseOrder;
use App\Modules\Purchasing\Infrastructure\Persistence\Models\PurchaseOrderLine;
use App\Modules\Purchasing\Infrastructure\Persistence\Models\PurchaseRequisition;
use App\Modules\Purchasing\Infrastructure\Persistence\Models\Supplier;
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

final class PurchaseOrderController extends Controller
{
    use HandlesTenantAuthorization;

    private const STATUS_TRANSITIONS = [
        'DRAFT' => ['APPROVED', 'CANCELLED'],
        'APPROVED' => ['CANCELLED'],
        'CANCELLED' => [],
    ];

    private const READ_PERMISSION = 'purchasing.orders.read';

    private const CREATE_PERMISSION = 'purchasing.orders.create';

    private const UPDATE_PERMISSION = 'purchasing.orders.update';

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

        abort_unless(in_array($sort, ['id', 'order_date', 'status', 'created_at'], true), 404);

        $orders = PurchaseOrder::query()
            ->with('supplier:id,name')
            ->withCount('lines')
            ->when($status !== '', static fn (Builder $query) => $query->where('status', $status))
            ->when($search !== '', static function (Builder $query) use ($search, $searchId): void {
                $query->where(function (Builder $nested) use ($search): void {
                    $nested
                        ->where('purchase_order_number', 'like', "%{$search}%")
                        ->orWhere('notes', 'like', "%{$search}%");
                });

                if ($searchId !== null) {
                    $query->orWhere('id', $searchId);
                }
            })
            ->orderBy($sort, $direction)
            ->paginate(15)
            ->withQueryString();

        return view('client.purchasing.orders.search', compact('orders', 'search', 'status', 'sort', 'direction', 'company'));
    }

    public function create(Request $request): View
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensurePermission($request, self::CREATE_PERMISSION, $company->id);

        $lineRows = $this->oldItemRows($request, $this->defaultLineRows());

        return view('client.purchasing.orders.form', [
            'order' => null,
            'company' => $company,
            'suppliers' => $this->supplierOptions($company),
            'requisitions' => $this->requisitionOptions($company),
            'products' => $this->productOptionsByIds($company, $this->selectedProductIdsFromLineRows($lineRows)),
            'warehouses' => $this->warehouseOptionsByIds($company, $this->selectedWarehouseIdsFromLineRows($lineRows)),
            'lineRows' => $lineRows,
        ]);
    }

    public function show(Request $request, PurchaseOrder $order): View
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensurePermission($request, self::READ_PERMISSION, $company->id);

        $order->load(['supplier:id,name', 'requisition:id,requisition_number', 'lines.product:id,sku,description', 'lines.warehouse:id,code,name'])->loadCount('lines');

        return view('client.purchasing.orders.show', compact('order', 'company'));
    }

    public function store(Request $request, AuditLogService $audit): RedirectResponse
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensurePermission($request, self::CREATE_PERMISSION, $company->id);

        $data = $this->validateOrder($request);

        $order = DB::transaction(function () use ($company, $data, $request): PurchaseOrder {
            $header = PurchaseOrder::query()->create([
                'company_id' => $company->id,
                'purchase_order_number' => $this->generateNumber($company),
                'supplier_id' => (int) $data['supplier_id'],
                'purchase_requisition_id' => isset($data['purchase_requisition_id']) ? (int) $data['purchase_requisition_id'] : null,
                'status' => 'DRAFT',
                'order_date' => $data['order_date'],
                'expected_delivery_date' => $data['expected_delivery_date'] ?? null,
                'created_by' => $request->user()?->id,
                'notes' => $data['notes'] ?? null,
            ]);

            $this->syncLines($header, $data['items']);
            $this->applyStatusAudit($header, $data['status'], $request->user()?->id);
            $header->save();

            return $header;
        });

        $audit->record(
            'tenant_purchase_order.created',
            context: [
                'purchase_order_id' => $order->id,
                'company_id' => $company->id,
                'actor_user_id' => $request->user()?->id,
            ],
            userId: $request->user()?->id,
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return redirect()->route('purchasing.orders.index')->with('status', __('purchase_order.created'));
    }

    public function edit(Request $request, PurchaseOrder $order): View
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensurePermission($request, self::UPDATE_PERMISSION, $company->id);

        $lineRows = $this->oldItemRows($request, $this->lineRowsForForm($order));

        return view('client.purchasing.orders.form', [
            'order' => $order,
            'company' => $company,
            'suppliers' => $this->supplierOptions($company),
            'requisitions' => $this->requisitionOptions($company),
            'products' => $this->productOptionsByIds($company, $this->selectedProductIdsFromLineRows($lineRows)),
            'warehouses' => $this->warehouseOptionsByIds($company, $this->selectedWarehouseIdsFromLineRows($lineRows)),
            'lineRows' => $lineRows,
        ]);
    }

    public function update(Request $request, PurchaseOrder $order, AuditLogService $audit): RedirectResponse
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensurePermission($request, self::UPDATE_PERMISSION, $company->id);

        $data = $this->validateOrder($request);

        DB::transaction(function () use ($order, $data, $request): void {
            $this->assertStatusTransition($order->status, $data['status']);

            $order->fill([
                'supplier_id' => (int) $data['supplier_id'],
                'purchase_requisition_id' => isset($data['purchase_requisition_id']) ? (int) $data['purchase_requisition_id'] : null,
                'order_date' => $data['order_date'],
                'expected_delivery_date' => $data['expected_delivery_date'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]);

            $this->syncLines($order, $data['items']);
            $this->applyStatusAudit($order, $data['status'], $request->user()?->id);
            $order->save();
        });

        $audit->record(
            'tenant_purchase_order.updated',
            context: [
                'purchase_order_id' => $order->id,
                'company_id' => $company->id,
                'actor_user_id' => $request->user()?->id,
            ],
            userId: $request->user()?->id,
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return redirect()->route('purchasing.orders.index')->with('status', __('purchase_order.updated'));
    }

    public function destroy(Request $request, PurchaseOrder $order, AuditLogService $audit): RedirectResponse
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensurePermission($request, self::UPDATE_PERMISSION, $company->id);

        $orderId = $order->id;
        $orderNumber = $order->purchase_order_number;
        $order->delete();

        $audit->record(
            'tenant_purchase_order.removed',
            context: [
                'purchase_order_id' => $orderId,
                'purchase_order_number' => $orderNumber,
                'company_id' => $company->id,
                'actor_user_id' => $request->user()?->id,
            ],
            userId: $request->user()?->id,
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return redirect()->route('purchasing.orders.index')->with('status', __('purchase_order.removed'));
    }

    /**
     * @return array{supplier_id: int|string, purchase_requisition_id?: int|string|null, status: string, order_date: string, expected_delivery_date?: string|null, notes?: string|null, items: array<int, array{product_id: int, warehouse_id: int|null, quantity: float, unit_price: float|null, need_by_date: string|null, promised_date: string|null}>}
     */
    private function validateOrder(Request $request): array
    {
        $data = $request->validate([
            'supplier_id' => ['required', 'integer', Rule::exists('suppliers', 'id')],
            'purchase_requisition_id' => ['nullable', 'integer', Rule::exists('purchase_requisitions', 'id')],
            'status' => ['required', Rule::in(['DRAFT', 'APPROVED', 'CANCELLED'])],
            'order_date' => ['required', 'date'],
            'expected_delivery_date' => ['nullable', 'date', 'after_or_equal:order_date'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', Rule::exists('products', 'id')],
            'items.*.warehouse_id' => ['nullable', 'integer', Rule::exists('warehouses', 'id')],
            'items.*.quantity' => ['required', 'numeric', 'gt:0'],
            'items.*.unit_price' => ['nullable', 'string', 'max:30'],
            'items.*.need_by_date' => ['nullable', 'date'],
            'items.*.promised_date' => ['nullable', 'date'],
        ]);

        $data['items'] = collect($data['items'])
            ->map(function (array $item): array {
                $unitPrice = isset($item['unit_price']) && trim((string) $item['unit_price']) !== ''
                    ? $this->normalizeAmountToDecimal((string) $item['unit_price'])
                    : null;

                return [
                    'product_id' => (int) $item['product_id'],
                    'warehouse_id' => isset($item['warehouse_id']) ? (int) $item['warehouse_id'] : null,
                    'quantity' => round((float) $item['quantity'], 6),
                    'unit_price' => $unitPrice,
                    'need_by_date' => $item['need_by_date'] ?? null,
                    'promised_date' => $item['promised_date'] ?? null,
                ];
            })
            ->all();

        return $data;
    }

    /**
     * @param array<int, array{product_id: int, warehouse_id: int|null, quantity: float, unit_price: float|null, need_by_date: string|null, promised_date: string|null}> $items
     */
    private function syncLines(PurchaseOrder $order, array $items): void
    {
        $order->lines()->delete();

        foreach ($items as $item) {
            PurchaseOrderLine::query()->create([
                'company_id' => $order->company_id,
                'purchase_order_id' => $order->id,
                'product_id' => $item['product_id'],
                'warehouse_id' => $item['warehouse_id'],
                'quantity_ordered' => $item['quantity'],
                'quantity_received' => 0,
                'unit_price' => $item['unit_price'],
                'need_by_date' => $item['need_by_date'],
                'promised_date' => $item['promised_date'],
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
                'status' => __('purchase_order.invalid_transition'),
            ]);
        }
    }

    private function applyStatusAudit(PurchaseOrder $order, string $status, ?int $userId): void
    {
        $order->status = $status;

        if ($status === 'APPROVED') {
            $order->approved_by ??= $userId;
            $order->approved_at ??= now();
            $order->cancelled_by = null;
            $order->cancelled_at = null;

            return;
        }

        if ($status === 'CANCELLED') {
            $order->cancelled_by ??= $userId;
            $order->cancelled_at ??= now();

            return;
        }

        $order->approved_by = null;
        $order->approved_at = null;
        $order->cancelled_by = null;
        $order->cancelled_at = null;
    }

    private function normalizeAmountToDecimal(string $value): float
    {
        $normalized = trim($value);
        $normalized = str_replace(['R$', ' '], '', $normalized);

        if (str_contains($normalized, ',')) {
            $normalized = str_replace('.', '', $normalized);
            $normalized = str_replace(',', '.', $normalized);
        }

        return round((float) $normalized, 6);
    }

    /**
     * @return array<int, array{product_id: int|null, warehouse_id: int|null, quantity: string|int|float, unit_price: string, need_by_date: string, promised_date: string}>
     */
    private function defaultLineRows(): array
    {
        return [[
            'product_id' => null,
            'warehouse_id' => null,
            'quantity' => 1,
            'unit_price' => '0,00',
            'need_by_date' => now()->addDays(7)->toDateString(),
            'promised_date' => now()->addDays(7)->toDateString(),
        ]];
    }

    /**
     * @return array<int, array{product_id: int|null, warehouse_id: int|null, quantity: string, unit_price: string, need_by_date: string, promised_date: string}>
     */
    private function lineRowsForForm(PurchaseOrder $order): array
    {
        $order->loadMissing('lines');

        $rows = $order->lines
            ->map(static fn (PurchaseOrderLine $line): array => [
                'product_id' => $line->product_id,
                'warehouse_id' => $line->warehouse_id,
                'quantity' => (string) $line->quantity_ordered,
                'unit_price' => $line->unit_price !== null ? number_format((float) $line->unit_price, 2, ',', '.') : '0,00',
                'need_by_date' => $line->need_by_date?->format('Y-m-d') ?? '',
                'promised_date' => $line->promised_date?->format('Y-m-d') ?? '',
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

    private function generateNumber(Company $company): string
    {
        do {
            $number = 'PO-'.strtoupper(bin2hex(random_bytes(3)));
        } while (PurchaseOrder::query()->where('company_id', $company->id)->where('purchase_order_number', $number)->exists());

        return $number;
    }

    /**
     * @return array<int, string>
     */
    private function supplierOptions(Company $company): array
    {
        return Supplier::query()
            ->where('company_id', $company->id)
            ->orderBy('name')
            ->pluck('name', 'id')
            ->mapWithKeys(static fn (string $name, int $id): array => [$id => "#{$id} - {$name}"])
            ->all();
    }

    /**
     * @return array<int, string>
     */
    private function requisitionOptions(Company $company): array
    {
        return PurchaseRequisition::query()
            ->where('company_id', $company->id)
            ->orderByDesc('id')
            ->limit(200)
            ->pluck('requisition_number', 'id')
            ->mapWithKeys(static fn (string $number, int $id): array => [$id => "#{$id} - {$number}"])
            ->all();
    }

}
