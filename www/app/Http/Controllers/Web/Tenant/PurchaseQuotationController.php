<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\Tenant\Concerns\HandlesTenantAuthorization;
use App\Modules\Purchasing\Infrastructure\Persistence\Models\PurchaseQuotation;
use App\Modules\Purchasing\Infrastructure\Persistence\Models\PurchaseRequisition;
use App\Modules\Purchasing\Infrastructure\Persistence\Models\Supplier;
use App\Modules\Tenant\Infrastructure\Persistence\Models\Company;
use App\Services\SaaS\AuditLogService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

final class PurchaseQuotationController extends Controller
{
    use HandlesTenantAuthorization;

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

        return view('client.purchasing.quotations.form', [
            'quotation' => null,
            'company' => $company,
            'suppliers' => $this->supplierOptions($company),
            'requisitions' => $this->requisitionOptions($company),
        ]);
    }

    public function show(Request $request, PurchaseQuotation $quotation): View
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensurePermission($request, self::READ_PERMISSION, $company->id);

        $quotation->load(['supplier:id,name', 'requisition:id,requisition_number']);

        return view('client.purchasing.quotations.show', compact('quotation', 'company'));
    }

    public function store(Request $request, AuditLogService $audit): RedirectResponse
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensurePermission($request, self::CREATE_PERMISSION, $company->id);

        $data = $this->validateQuotation($request);

        $quotation = PurchaseQuotation::query()->create([
            'company_id' => $company->id,
            'quotation_number' => $this->generateNumber($company),
            'purchase_requisition_id' => isset($data['purchase_requisition_id']) ? (int) $data['purchase_requisition_id'] : null,
            'supplier_id' => isset($data['supplier_id']) ? (int) $data['supplier_id'] : null,
            'quotation_date' => $data['quotation_date'],
            'valid_until' => $data['valid_until'] ?? null,
            'status' => $data['status'],
            'amount_cents' => $this->moneyToCents((string) $data['amount']),
            'notes' => $data['notes'] ?? null,
        ]);

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

        return view('client.purchasing.quotations.form', [
            'quotation' => $quotation,
            'company' => $company,
            'suppliers' => $this->supplierOptions($company),
            'requisitions' => $this->requisitionOptions($company),
        ]);
    }

    public function update(Request $request, PurchaseQuotation $quotation, AuditLogService $audit): RedirectResponse
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensurePermission($request, self::UPDATE_PERMISSION, $company->id);

        $data = $this->validateQuotation($request);

        $quotation->fill([
            'purchase_requisition_id' => isset($data['purchase_requisition_id']) ? (int) $data['purchase_requisition_id'] : null,
            'supplier_id' => isset($data['supplier_id']) ? (int) $data['supplier_id'] : null,
            'quotation_date' => $data['quotation_date'],
            'valid_until' => $data['valid_until'] ?? null,
            'status' => $data['status'],
            'amount_cents' => $this->moneyToCents((string) $data['amount']),
            'notes' => $data['notes'] ?? null,
        ]);
        $quotation->save();

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
     * @return array{supplier_id?: int|string|null, purchase_requisition_id?: int|string|null, quotation_date: string, valid_until?: string|null, status: string, amount: string|int|float, notes?: string|null}
     */
    private function validateQuotation(Request $request): array
    {
        return $request->validate([
            'supplier_id' => ['nullable', 'integer', Rule::exists('suppliers', 'id')],
            'purchase_requisition_id' => ['nullable', 'integer', Rule::exists('purchase_requisitions', 'id')],
            'quotation_date' => ['required', 'date'],
            'valid_until' => ['nullable', 'date', 'after_or_equal:quotation_date'],
            'status' => ['required', Rule::in(['DRAFT', 'RECEIVED', 'APPROVED', 'REJECTED'])],
            'amount' => ['required', 'string', 'max:30'],
            'notes' => ['nullable', 'string'],
        ]);
    }

    private function generateNumber(Company $company): string
    {
        do {
            $number = 'QTN-'.strtoupper(bin2hex(random_bytes(3)));
        } while (PurchaseQuotation::query()->where('company_id', $company->id)->where('quotation_number', $number)->exists());

        return $number;
    }

    private function moneyToCents(string $value): int
    {
        $normalized = trim($value);
        $normalized = str_replace(['R$', ' '], '', $normalized);

        if (str_contains($normalized, ',')) {
            $normalized = str_replace('.', '', $normalized);
            $normalized = str_replace(',', '.', $normalized);
        }

        return (int) round(((float) $normalized) * 100);
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
