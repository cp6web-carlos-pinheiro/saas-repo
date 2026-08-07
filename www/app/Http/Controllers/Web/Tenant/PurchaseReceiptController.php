<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\Tenant\Concerns\HandlesTenantAuthorization;
use App\Modules\Product\Infrastructure\Persistence\Models\Product;
use App\Modules\Purchasing\Application\Services\PurchasingIntegrationService;
use App\Modules\Purchasing\Infrastructure\Persistence\Models\PurchaseOrder;
use App\Modules\Purchasing\Infrastructure\Persistence\Models\PurchaseOrderLine;
use App\Modules\Purchasing\Infrastructure\Persistence\Models\PurchaseReceipt;
use App\Modules\Purchasing\Infrastructure\Persistence\Models\PurchaseReceiptLine;
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

final class PurchaseReceiptController extends Controller
{
    use HandlesTenantAuthorization;

    private const STATUS_TRANSITIONS = [
        'DRAFT' => ['POSTED', 'CANCELLED'],
        'POSTED' => [],
        'CANCELLED' => [],
    ];

    private const READ_PERMISSION = 'purchasing.receipts.read';

    private const CREATE_PERMISSION = 'purchasing.receipts.create';

    private const UPDATE_PERMISSION = 'purchasing.receipts.update';

    public function index(Request $request): View
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensurePermission($request, self::READ_PERMISSION, $company->id);

        $search = trim((string) $request->query('search'));
        $searchId = ctype_digit($search) ? (int) $search : null;
        $status = mb_strtoupper(trim((string) $request->query('status')));
        $sort = (string) $request->query('sort', 'id');
        $direction = (string) $request->query('direction', 'desc') === 'asc' ? 'asc' : 'desc';

        if (! in_array($status, ['DRAFT', 'POSTED', 'CANCELLED'], true)) {
            $status = '';
        }

        abort_unless(in_array($sort, ['id', 'receipt_number', 'supplier', 'purchase_order', 'receipt_date', 'status'], true), 404);

        $receiptsQuery = PurchaseReceipt::query()
            ->with(['supplier:id,name', 'purchaseOrder:id,purchase_order_number'])
            ->when($status !== '', static fn (Builder $query) => $query->where('status', $status))
            ->when($search !== '', static function (Builder $query) use ($search, $searchId): void {
                $query->where(function (Builder $nested) use ($search): void {
                    $nested
                        ->where('receipt_number', 'like', "%{$search}%")
                        ->orWhere('notes', 'like', "%{$search}%");
                });

                if ($searchId !== null) {
                    $query->orWhere('id', $searchId);
                }
            });

        if ($sort === 'supplier') {
            $receiptsQuery->orderBy(Supplier::query()->select('name')->whereColumn('suppliers.id', 'purchase_receipts.supplier_id'), $direction);
        } elseif ($sort === 'purchase_order') {
            $receiptsQuery->orderBy(PurchaseOrder::query()->select('purchase_order_number')->whereColumn('purchase_orders.id', 'purchase_receipts.purchase_order_id'), $direction);
        } else {
            $receiptsQuery->orderBy($sort, $direction);
        }

        $receipts = $receiptsQuery
            ->orderBy('id', $direction)
            ->paginate(15)
            ->withQueryString();

        return view('client.purchasing.receipts.search', compact('receipts', 'search', 'status', 'sort', 'direction', 'company'));
    }

    public function create(Request $request): View
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensurePermission($request, self::CREATE_PERMISSION, $company->id);

        $lineRows = $this->oldItemRows($request, $this->defaultLineRows());
        $orderId = (int) old('purchase_order_id', 0);

        return view('client.purchasing.receipts.form', [
            'receipt' => null,
            'company' => $company,
            'suppliers' => $this->supplierOptions($company),
            'orders' => $this->orderOptions($company),
            'products' => $this->productOptionsByIds($company, $this->selectedProductIdsFromLineRows($lineRows)),
            'warehouses' => $this->warehouseOptionsByIds($company, $this->selectedWarehouseIdsFromLineRows($lineRows)),
            'orderLines' => $this->orderLineOptions($company, $orderId),
            'lineRows' => $lineRows,
        ]);
    }

    public function show(Request $request, PurchaseReceipt $receipt): View
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensurePermission($request, self::READ_PERMISSION, $company->id);

        $receipt->load([
            'supplier:id,name',
            'purchaseOrder:id,purchase_order_number',
            'lines.product:id,sku,description',
            'lines.warehouse:id,code,name',
            'lines.orderLine:id,purchase_order_id',
        ]);

        return view('client.purchasing.receipts.show', compact('receipt', 'company'));
    }

    public function store(Request $request, AuditLogService $audit, PurchasingIntegrationService $integration): RedirectResponse
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensurePermission($request, self::CREATE_PERMISSION, $company->id);

        $data = $this->validateReceipt($request, $company);

        $receipt = DB::transaction(function () use ($company, $data, $request, $integration): PurchaseReceipt {
            $header = PurchaseReceipt::query()->create([
                'company_id' => $company->id,
                'receipt_number' => $this->generateNumber($company),
                'purchase_order_id' => isset($data['purchase_order_id']) ? (int) $data['purchase_order_id'] : null,
                'supplier_id' => isset($data['supplier_id']) ? (int) $data['supplier_id'] : null,
                'receipt_date' => $data['receipt_date'],
                'status' => 'DRAFT',
                'notes' => $data['notes'] ?? null,
            ]);

            $this->syncLines($header, $data['items']);
            $this->applyStatusAudit($header, $data['status'], $request->user()?->id);
            $header->save();

            if ($data['status'] === 'POSTED') {
                $integration->postReceiptToInventory($header, $request->user()?->id);
            }

            return $header;
        });

        $audit->record(
            'tenant_purchase_receipt.created',
            context: [
                'purchase_receipt_id' => $receipt->id,
                'company_id' => $company->id,
                'actor_user_id' => $request->user()?->id,
            ],
            userId: $request->user()?->id,
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return redirect()->route('purchasing.receipts.index')->with('status', __('purchase_receipt.created'));
    }

    public function edit(Request $request, PurchaseReceipt $receipt): View
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensurePermission($request, self::UPDATE_PERMISSION, $company->id);

        if ($receipt->status === 'POSTED') {
            abort(403, __('purchase_receipt.locked_posted'));
        }

        $lineRows = $this->oldItemRows($request, $this->lineRowsForForm($receipt));
        $orderId = (int) old('purchase_order_id', $receipt->purchase_order_id ?? 0);

        return view('client.purchasing.receipts.form', [
            'receipt' => $receipt,
            'company' => $company,
            'suppliers' => $this->supplierOptions($company),
            'orders' => $this->orderOptions($company),
            'products' => $this->productOptionsByIds($company, $this->selectedProductIdsFromLineRows($lineRows)),
            'warehouses' => $this->warehouseOptionsByIds($company, $this->selectedWarehouseIdsFromLineRows($lineRows)),
            'orderLines' => $this->orderLineOptions($company, $orderId),
            'lineRows' => $lineRows,
        ]);
    }

    public function update(Request $request, PurchaseReceipt $receipt, AuditLogService $audit, PurchasingIntegrationService $integration): RedirectResponse
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensurePermission($request, self::UPDATE_PERMISSION, $company->id);

        $this->ensureNotPosted($receipt);

        $data = $this->validateReceipt($request, $company);

        DB::transaction(function () use ($receipt, $data, $request, $integration): void {
            $this->assertStatusTransition($receipt->status, $data['status']);

            $receipt->fill([
                'purchase_order_id' => isset($data['purchase_order_id']) ? (int) $data['purchase_order_id'] : null,
                'supplier_id' => isset($data['supplier_id']) ? (int) $data['supplier_id'] : null,
                'receipt_date' => $data['receipt_date'],
                'notes' => $data['notes'] ?? null,
            ]);

            $wasPosted = $receipt->status === 'POSTED';

            $this->syncLines($receipt, $data['items']);
            $this->applyStatusAudit($receipt, $data['status'], $request->user()?->id);
            $receipt->save();

            if (! $wasPosted && $data['status'] === 'POSTED') {
                $integration->postReceiptToInventory($receipt, $request->user()?->id);
            }
        });

        $audit->record(
            'tenant_purchase_receipt.updated',
            context: [
                'purchase_receipt_id' => $receipt->id,
                'company_id' => $company->id,
                'actor_user_id' => $request->user()?->id,
            ],
            userId: $request->user()?->id,
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return redirect()->route('purchasing.receipts.index')->with('status', __('purchase_receipt.updated'));
    }

    public function destroy(Request $request, PurchaseReceipt $receipt, AuditLogService $audit): RedirectResponse
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensurePermission($request, self::UPDATE_PERMISSION, $company->id);

        $this->ensureNotPosted($receipt);

        $receiptId = $receipt->id;
        $receiptNumber = $receipt->receipt_number;
        $receipt->delete();

        $audit->record(
            'tenant_purchase_receipt.removed',
            context: [
                'purchase_receipt_id' => $receiptId,
                'purchase_receipt_number' => $receiptNumber,
                'company_id' => $company->id,
                'actor_user_id' => $request->user()?->id,
            ],
            userId: $request->user()?->id,
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return redirect()->route('purchasing.receipts.index')->with('status', __('purchase_receipt.removed'));
    }

    public function reverse(Request $request, PurchaseReceipt $receipt, AuditLogService $audit, PurchasingIntegrationService $integration): RedirectResponse
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensurePermission($request, self::UPDATE_PERMISSION, $company->id);
        $data = $request->validate([
            'reverse_category' => ['required', Rule::in(['quality', 'fiscal', 'supplier', 'master_data'])],
            'reverse_reason' => ['required', 'string', 'max:1000'],
        ]);

        if ($receipt->status !== 'POSTED') {
            throw ValidationException::withMessages([
                'status' => __('purchase_receipt.reverse_only_posted'),
            ]);
        }

        DB::transaction(function () use ($receipt, $request, $integration, $data): void {
            $integration->reverseReceiptFromInventory($receipt, (string) $data['reverse_category'], (string) $data['reverse_reason'], $request->user()?->id);

            $metadata = is_array($receipt->metadata) ? $receipt->metadata : [];
            $metadata['reversal'] = [
                'category' => (string) $data['reverse_category'],
                'reason' => (string) $data['reverse_reason'],
                'reversed_by' => $request->user()?->id,
                'reversed_at' => now()->toIso8601String(),
            ];

            $receipt->status = 'CANCELLED';
            $receipt->cancelled_by = $request->user()?->id;
            $receipt->cancelled_at = now();
            $receipt->metadata = $metadata;
            $receipt->save();
        });

        $audit->record(
            'tenant_purchase_receipt.reversed',
            context: [
                'purchase_receipt_id' => $receipt->id,
                'purchase_receipt_number' => $receipt->receipt_number,
                'reverse_category' => (string) $data['reverse_category'],
                'reverse_reason' => (string) $data['reverse_reason'],
                'company_id' => $company->id,
                'actor_user_id' => $request->user()?->id,
            ],
            userId: $request->user()?->id,
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return redirect()->route('purchasing.receipts.show', $receipt)->with('status', __('purchase_receipt.reversed'));
    }

    /**
     * @return array{supplier_id?: int|string|null, purchase_order_id?: int|string|null, receipt_date: string, status: string, notes?: string|null, items: array<int, array{purchase_order_line_id: int|null, product_id: int, warehouse_id: int, quantity_received: float, lot_number: string|null, notes: string|null}>}
     */
    private function validateReceipt(Request $request, Company $company): array
    {
        $data = $request->validate([
            'supplier_id' => ['nullable', 'integer', Rule::exists('suppliers', 'id')],
            'purchase_order_id' => ['nullable', 'integer', Rule::exists('purchase_orders', 'id')],
            'receipt_date' => ['required', 'date'],
            'status' => ['required', Rule::in(['DRAFT', 'POSTED', 'CANCELLED'])],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.purchase_order_line_id' => ['nullable', 'integer', Rule::exists('purchase_order_lines', 'id')],
            'items.*.product_id' => ['required', 'integer', Rule::exists('products', 'id')],
            'items.*.warehouse_id' => ['required', 'integer', Rule::exists('warehouses', 'id')],
            'items.*.quantity_received' => ['required', 'numeric', 'gt:0'],
            'items.*.lot_number' => ['nullable', 'string', 'max:80'],
            'items.*.notes' => ['nullable', 'string'],
        ]);

        $data['items'] = collect($data['items'])
            ->map(static fn (array $item): array => [
                'purchase_order_line_id' => isset($item['purchase_order_line_id']) ? (int) $item['purchase_order_line_id'] : null,
                'product_id' => (int) $item['product_id'],
                'warehouse_id' => (int) $item['warehouse_id'],
                'quantity_received' => round((float) $item['quantity_received'], 6),
                'lot_number' => $item['lot_number'] ?? null,
                'notes' => $item['notes'] ?? null,
            ])->all();

        $productIds = collect($data['items'])
            ->pluck('product_id')
            ->map(static fn (int $id): int => (int) $id)
            ->unique()
            ->values()
            ->all();

        $products = Product::query()
            ->where('company_id', $company->id)
            ->whereIn('id', $productIds)
            ->get(['id', 'lot_control', 'serial_control'])
            ->keyBy('id');

        $errors = [];

        foreach ($data['items'] as $index => $item) {
            $product = $products->get((int) $item['product_id']);

            if (! $product instanceof Product) {
                continue;
            }

            $requiresTrace = (bool) $product->lot_control || (bool) $product->serial_control;
            $hasLotNumber = trim((string) ($item['lot_number'] ?? '')) !== '';

            if ($requiresTrace && ! $hasLotNumber) {
                $errors[sprintf('items.%d.lot_number', $index)] = __('purchase_receipt.lot_or_serial_required');
            }
        }

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }

        return $data;
    }

    /**
     * @param array<int, array{purchase_order_line_id: int|null, product_id: int, warehouse_id: int, quantity_received: float, lot_number: string|null, notes: string|null}> $items
     */
    private function syncLines(PurchaseReceipt $receipt, array $items): void
    {
        $receipt->lines()->delete();

        foreach ($items as $item) {
            PurchaseReceiptLine::query()->create([
                'company_id' => $receipt->company_id,
                'purchase_receipt_id' => $receipt->id,
                'purchase_order_line_id' => $item['purchase_order_line_id'],
                'product_id' => $item['product_id'],
                'warehouse_id' => $item['warehouse_id'],
                'quantity_received' => $item['quantity_received'],
                'lot_number' => $item['lot_number'],
                'notes' => $item['notes'],
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
                'status' => __('purchase_receipt.invalid_transition'),
            ]);
        }
    }

    private function applyStatusAudit(PurchaseReceipt $receipt, string $status, ?int $userId): void
    {
        $receipt->status = $status;

        if ($status === 'POSTED') {
            $receipt->posted_by ??= $userId;
            $receipt->posted_at ??= now();
            $receipt->cancelled_by = null;
            $receipt->cancelled_at = null;

            return;
        }

        if ($status === 'CANCELLED') {
            $receipt->cancelled_by ??= $userId;
            $receipt->cancelled_at ??= now();

            return;
        }
    }

    private function ensureNotPosted(PurchaseReceipt $receipt): void
    {
        if ($receipt->status === 'POSTED') {
            throw ValidationException::withMessages([
                'status' => __('purchase_receipt.locked_posted'),
            ]);
        }
    }

    /**
     * @return array<int, array{purchase_order_line_id: int|null, product_id: int|null, warehouse_id: int|null, quantity_received: string|int|float, lot_number: string|null, notes: string|null}>
     */
    private function defaultLineRows(): array
    {
        return [[
            'purchase_order_line_id' => null,
            'product_id' => null,
            'warehouse_id' => null,
            'quantity_received' => 1,
            'lot_number' => null,
            'notes' => null,
        ]];
    }

    /**
     * @return array<int, array{purchase_order_line_id: int|null, product_id: int|null, warehouse_id: int|null, quantity_received: string, lot_number: string|null, notes: string|null}>
     */
    private function lineRowsForForm(PurchaseReceipt $receipt): array
    {
        $receipt->loadMissing('lines');

        $rows = $receipt->lines
            ->map(static fn (PurchaseReceiptLine $line): array => [
                'purchase_order_line_id' => $line->purchase_order_line_id,
                'product_id' => $line->product_id,
                'warehouse_id' => $line->warehouse_id,
                'quantity_received' => (string) $line->quantity_received,
                'lot_number' => $line->lot_number,
                'notes' => $line->notes,
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

    /**
     * @return Collection<int, PurchaseOrderLine>
     */
    private function orderLineOptions(Company $company, int $orderId): Collection
    {
        if ($orderId <= 0) {
            return collect();
        }

        return PurchaseOrderLine::query()
            ->where('company_id', $company->id)
            ->where('purchase_order_id', $orderId)
            ->with('product:id,sku,description')
            ->orderBy('id')
            ->get();
    }

    private function generateNumber(Company $company): string
    {
        do {
            $number = 'RCT-'.strtoupper(bin2hex(random_bytes(3)));
        } while (PurchaseReceipt::query()->where('company_id', $company->id)->where('receipt_number', $number)->exists());

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
    private function orderOptions(Company $company): array
    {
        return PurchaseOrder::query()
            ->where('company_id', $company->id)
            ->orderByDesc('id')
            ->limit(200)
            ->pluck('purchase_order_number', 'id')
            ->mapWithKeys(static fn (string $number, int $id): array => [$id => "#{$id} - {$number}"])
            ->all();
    }
}
