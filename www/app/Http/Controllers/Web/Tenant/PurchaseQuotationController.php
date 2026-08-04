<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\Tenant\Concerns\HandlesTenantAuthorization;
use App\Modules\Product\Infrastructure\Persistence\Models\Product;
use App\Modules\Purchasing\Infrastructure\Persistence\Models\PurchaseQuotation;
use App\Modules\Purchasing\Infrastructure\Persistence\Models\PurchaseQuotationLine;
use App\Modules\Purchasing\Infrastructure\Persistence\Models\PurchaseRequisition;
use App\Modules\Purchasing\Infrastructure\Persistence\Models\Supplier;
use App\Modules\Tenant\Infrastructure\Persistence\Models\Company;
use App\Services\SaaS\AuditLogService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

final class PurchaseQuotationController extends Controller
{
    use HandlesTenantAuthorization;

    private const STATUS_TRANSITIONS = [
        'DRAFT' => ['RECEIVED', 'REJECTED'],
        'RECEIVED' => ['APPROVED', 'REJECTED'],
        'APPROVED' => [],
        'REJECTED' => [],
    ];

    private const READ_PERMISSION = 'purchasing.quotations.read';

    private const CREATE_PERMISSION = 'purchasing.quotations.create';

    private const UPDATE_PERMISSION = 'purchasing.quotations.update';

    public function index(Request $request): View
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensurePermission($request, self::READ_PERMISSION, $company->id);

        $search = trim((string) $request->query('search'));
        $searchId = ctype_digit($search) ? (int) $search : null;
        $status = mb_strtoupper(trim((string) $request->query('status')));
        $sort = (string) $request->query('sort', 'id');
        $direction = (string) $request->query('direction', 'desc') === 'asc' ? 'asc' : 'desc';

        if (! in_array($status, ['DRAFT', 'RECEIVED', 'APPROVED', 'REJECTED'], true)) {
            $status = '';
        }

        abort_unless(in_array($sort, ['id', 'quotation_date', 'status', 'amount_cents', 'created_at'], true), 404);

        $quotations = PurchaseQuotation::query()
            ->with('supplier:id,name')
            ->when($status !== '', static fn (Builder $query) => $query->where('status', $status))
            ->when($search !== '', static function (Builder $query) use ($search, $searchId): void {
                $query->where(function (Builder $nested) use ($search): void {
                    $nested
                        ->where('quotation_number', 'like', "%{$search}%")
                        ->orWhere('notes', 'like', "%{$search}%");
                });

                if ($searchId !== null) {
                    $query->orWhere('id', $searchId);
                }
            })
            ->orderBy($sort, $direction)
            ->paginate(15)
            ->withQueryString();

        return view('client.purchasing.quotations.search', compact('quotations', 'search', 'status', 'sort', 'direction', 'company'));
    }

    public function create(Request $request): View
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensurePermission($request, self::CREATE_PERMISSION, $company->id);

        $lineRows = $this->oldItemRows($request, $this->defaultLineRows());

        return view('client.purchasing.quotations.form', [
            'quotation' => null,
            'company' => $company,
            'suppliers' => $this->supplierOptions($company),
            'requisitions' => $this->requisitionOptions($company),
            'products' => $this->productOptionsByIds($company, $this->selectedProductIdsFromLineRows($lineRows)),
            'lineRows' => $lineRows,
        ]);
    }

    public function show(Request $request, PurchaseQuotation $quotation): View
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensurePermission($request, self::READ_PERMISSION, $company->id);

        $quotation->load(['supplier:id,name', 'requisition:id,requisition_number', 'lines.product:id,sku,description']);

        return view('client.purchasing.quotations.show', compact('quotation', 'company'));
    }

    public function store(Request $request, AuditLogService $audit): RedirectResponse
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensurePermission($request, self::CREATE_PERMISSION, $company->id);

        $data = $this->validateQuotation($request);

        $quotation = DB::transaction(function () use ($company, $data, $request): PurchaseQuotation {
            $header = PurchaseQuotation::query()->create([
                'company_id' => $company->id,
                'quotation_number' => $this->generateNumber($company),
                'purchase_requisition_id' => isset($data['purchase_requisition_id']) ? (int) $data['purchase_requisition_id'] : null,
                'supplier_id' => isset($data['supplier_id']) ? (int) $data['supplier_id'] : null,
                'quotation_date' => $data['quotation_date'],
                'valid_until' => $data['valid_until'] ?? null,
                'status' => 'DRAFT',
                'amount_cents' => $this->sumItemsAmountCents($data['items']),
                'notes' => $data['notes'] ?? null,
            ]);

            $this->syncLines($header, $data['items']);
            $this->applyStatusAudit($header, $data['status'], $request->user()?->id);
            $header->save();

            return $header;
        });

        $audit->record(
            'tenant_purchase_quotation.created',
            context: [
                'purchase_quotation_id' => $quotation->id,
                'company_id' => $company->id,
                'actor_user_id' => $request->user()?->id,
            ],
            userId: $request->user()?->id,
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return redirect()->route('purchasing.quotations.index')->with('status', __('purchase_quotation.created'));
    }

    public function edit(Request $request, PurchaseQuotation $quotation): View
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensurePermission($request, self::UPDATE_PERMISSION, $company->id);

        $lineRows = $this->oldItemRows($request, $this->lineRowsForForm($quotation));

        return view('client.purchasing.quotations.form', [
            'quotation' => $quotation,
            'company' => $company,
            'suppliers' => $this->supplierOptions($company),
            'requisitions' => $this->requisitionOptions($company),
            'products' => $this->productOptionsByIds($company, $this->selectedProductIdsFromLineRows($lineRows)),
            'lineRows' => $lineRows,
        ]);
    }

    public function update(Request $request, PurchaseQuotation $quotation, AuditLogService $audit): RedirectResponse
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensurePermission($request, self::UPDATE_PERMISSION, $company->id);

        $data = $this->validateQuotation($request);

        DB::transaction(function () use ($quotation, $data, $request): void {
            $this->assertStatusTransition($quotation->status, $data['status']);

            $quotation->fill([
                'purchase_requisition_id' => isset($data['purchase_requisition_id']) ? (int) $data['purchase_requisition_id'] : null,
                'supplier_id' => isset($data['supplier_id']) ? (int) $data['supplier_id'] : null,
                'quotation_date' => $data['quotation_date'],
                'valid_until' => $data['valid_until'] ?? null,
                'amount_cents' => $this->sumItemsAmountCents($data['items']),
                'notes' => $data['notes'] ?? null,
            ]);

            $this->syncLines($quotation, $data['items']);
            $this->applyStatusAudit($quotation, $data['status'], $request->user()?->id);
            $quotation->save();
        });

        $audit->record(
            'tenant_purchase_quotation.updated',
            context: [
                'purchase_quotation_id' => $quotation->id,
                'company_id' => $company->id,
                'actor_user_id' => $request->user()?->id,
            ],
            userId: $request->user()?->id,
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return redirect()->route('purchasing.quotations.index')->with('status', __('purchase_quotation.updated'));
    }

    public function destroy(Request $request, PurchaseQuotation $quotation, AuditLogService $audit): RedirectResponse
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensurePermission($request, self::UPDATE_PERMISSION, $company->id);

        $quotationId = $quotation->id;
        $quotationNumber = $quotation->quotation_number;
        $quotation->delete();

        $audit->record(
            'tenant_purchase_quotation.removed',
            context: [
                'purchase_quotation_id' => $quotationId,
                'purchase_quotation_number' => $quotationNumber,
                'company_id' => $company->id,
                'actor_user_id' => $request->user()?->id,
            ],
            userId: $request->user()?->id,
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return redirect()->route('purchasing.quotations.index')->with('status', __('purchase_quotation.removed'));
    }

    /**
     * @return array{supplier_id?: int|string|null, purchase_requisition_id?: int|string|null, quotation_date: string, valid_until?: string|null, status: string, notes?: string|null, items: array<int, array{product_id: int, quantity: float, unit_price: float, notes: string|null}>}
     */
    private function validateQuotation(Request $request): array
    {
        $data = $request->validate([
            'supplier_id' => ['nullable', 'integer', Rule::exists('suppliers', 'id')],
            'purchase_requisition_id' => ['nullable', 'integer', Rule::exists('purchase_requisitions', 'id')],
            'quotation_date' => ['required', 'date'],
            'valid_until' => ['nullable', 'date', 'after_or_equal:quotation_date'],
            'status' => ['required', Rule::in(['DRAFT', 'RECEIVED', 'APPROVED', 'REJECTED'])],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', Rule::exists('products', 'id')],
            'items.*.quantity' => ['required', 'numeric', 'gt:0'],
            'items.*.unit_price' => ['required', 'string', 'max:30'],
            'items.*.notes' => ['nullable', 'string'],
        ]);

        $data['items'] = collect($data['items'])
            ->map(function (array $item): array {
                return [
                    'product_id' => (int) $item['product_id'],
                    'quantity' => round((float) $item['quantity'], 6),
                    'unit_price' => $this->normalizeAmountToDecimal((string) $item['unit_price']),
                    'notes' => $item['notes'] ?? null,
                ];
            })->all();

        return $data;
    }

    private function generateNumber(Company $company): string
    {
        do {
            $number = 'QTN-'.strtoupper(bin2hex(random_bytes(3)));
        } while (PurchaseQuotation::query()->where('company_id', $company->id)->where('quotation_number', $number)->exists());

        return $number;
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
     * @param array<int, array{unit_price: float, quantity: float}> $items
     */
    private function sumItemsAmountCents(array $items): int
    {
        return (int) round(collect($items)->sum(static fn (array $item): float => $item['unit_price'] * $item['quantity']) * 100);
    }

    /**
     * @param array<int, array{product_id: int, quantity: float, unit_price: float, notes: string|null}> $items
     */
    private function syncLines(PurchaseQuotation $quotation, array $items): void
    {
        $quotation->lines()->delete();

        foreach ($items as $item) {
            PurchaseQuotationLine::query()->create([
                'company_id' => $quotation->company_id,
                'purchase_quotation_id' => $quotation->id,
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
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
                'status' => __('purchase_quotation.invalid_transition'),
            ]);
        }
    }

    private function applyStatusAudit(PurchaseQuotation $quotation, string $status, ?int $userId): void
    {
        $quotation->status = $status;

        if ($status === 'RECEIVED') {
            $quotation->received_by ??= $userId;
            $quotation->received_at ??= now();

            return;
        }

        if ($status === 'APPROVED') {
            $quotation->approved_by ??= $userId;
            $quotation->approved_at ??= now();
            $quotation->received_by ??= $userId;
            $quotation->received_at ??= now();

            return;
        }

        if ($status === 'REJECTED') {
            $quotation->rejected_by ??= $userId;
            $quotation->rejected_at ??= now();

            return;
        }
    }

    /**
     * @return array<int, array{product_id: int|null, quantity: string|int|float, unit_price: string, notes: string|null}>
     */
    private function defaultLineRows(): array
    {
        return [[
            'product_id' => null,
            'quantity' => 1,
            'unit_price' => '0,00',
            'notes' => null,
        ]];
    }

    /**
     * @return array<int, array{product_id: int|null, quantity: string, unit_price: string, notes: string|null}>
     */
    private function lineRowsForForm(PurchaseQuotation $quotation): array
    {
        $quotation->loadMissing('lines');

        $rows = $quotation->lines
            ->map(static fn (PurchaseQuotationLine $line): array => [
                'product_id' => $line->product_id,
                'quantity' => (string) $line->quantity,
                'unit_price' => number_format((float) $line->unit_price, 2, ',', '.'),
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
     * @param array<int, int> $ids
     */
    private function productOptionsByIds(Company $company, array $ids): Collection
    {
        if ($ids === []) {
            return collect();
        }

        return Product::query()
            ->where('company_id', $company->id)
            ->whereIn('id', $ids)
            ->orderBy('sku')
            ->get(['id', 'sku', 'description']);
    }
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
