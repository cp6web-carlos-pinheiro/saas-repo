<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\Tenant\Concerns\HandlesTenantAuthorization;
use App\Modules\Purchasing\Infrastructure\Persistence\Models\PurchaseOrder;
use App\Modules\Purchasing\Infrastructure\Persistence\Models\PurchaseReceipt;
use App\Modules\Purchasing\Infrastructure\Persistence\Models\Supplier;
use App\Modules\Tenant\Infrastructure\Persistence\Models\Company;
use App\Services\SaaS\AuditLogService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

final class PurchaseReceiptController extends Controller
{
    use HandlesTenantAuthorization;

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

        abort_unless(in_array($sort, ['id', 'receipt_date', 'status', 'created_at'], true), 404);

        $receipts = PurchaseReceipt::query()
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
            })
            ->orderBy($sort, $direction)
            ->paginate(15)
            ->withQueryString();

        return view('client.purchasing.receipts.search', compact('receipts', 'search', 'status', 'sort', 'direction', 'company'));
    }

    public function create(Request $request): View
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensurePermission($request, self::CREATE_PERMISSION, $company->id);

        return view('client.purchasing.receipts.form', [
            'receipt' => null,
            'company' => $company,
            'suppliers' => $this->supplierOptions($company),
            'orders' => $this->orderOptions($company),
        ]);
    }

    public function show(Request $request, PurchaseReceipt $receipt): View
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensurePermission($request, self::READ_PERMISSION, $company->id);

        $receipt->load(['supplier:id,name', 'purchaseOrder:id,purchase_order_number']);

        return view('client.purchasing.receipts.show', compact('receipt', 'company'));
    }

    public function store(Request $request, AuditLogService $audit): RedirectResponse
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensurePermission($request, self::CREATE_PERMISSION, $company->id);

        $data = $this->validateReceipt($request);

        $receipt = PurchaseReceipt::query()->create([
            'company_id' => $company->id,
            'receipt_number' => $this->generateNumber($company),
            'purchase_order_id' => isset($data['purchase_order_id']) ? (int) $data['purchase_order_id'] : null,
            'supplier_id' => isset($data['supplier_id']) ? (int) $data['supplier_id'] : null,
            'receipt_date' => $data['receipt_date'],
            'status' => $data['status'],
            'notes' => $data['notes'] ?? null,
        ]);

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

        return view('client.purchasing.receipts.form', [
            'receipt' => $receipt,
            'company' => $company,
            'suppliers' => $this->supplierOptions($company),
            'orders' => $this->orderOptions($company),
        ]);
    }

    public function update(Request $request, PurchaseReceipt $receipt, AuditLogService $audit): RedirectResponse
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensurePermission($request, self::UPDATE_PERMISSION, $company->id);

        $data = $this->validateReceipt($request);

        $receipt->fill([
            'purchase_order_id' => isset($data['purchase_order_id']) ? (int) $data['purchase_order_id'] : null,
            'supplier_id' => isset($data['supplier_id']) ? (int) $data['supplier_id'] : null,
            'receipt_date' => $data['receipt_date'],
            'status' => $data['status'],
            'notes' => $data['notes'] ?? null,
        ]);
        $receipt->save();

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

    /**
     * @return array{supplier_id?: int|string|null, purchase_order_id?: int|string|null, receipt_date: string, status: string, notes?: string|null}
     */
    private function validateReceipt(Request $request): array
    {
        return $request->validate([
            'supplier_id' => ['nullable', 'integer', Rule::exists('suppliers', 'id')],
            'purchase_order_id' => ['nullable', 'integer', Rule::exists('purchase_orders', 'id')],
            'receipt_date' => ['required', 'date'],
            'status' => ['required', Rule::in(['DRAFT', 'POSTED', 'CANCELLED'])],
            'notes' => ['nullable', 'string'],
        ]);
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
