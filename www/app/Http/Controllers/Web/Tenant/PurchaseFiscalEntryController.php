<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\Tenant\Concerns\HandlesTenantAuthorization;
use App\Modules\Purchasing\Infrastructure\Persistence\Models\PurchaseFiscalEntry;
use App\Modules\Purchasing\Infrastructure\Persistence\Models\PurchaseOrder;
use App\Modules\Purchasing\Infrastructure\Persistence\Models\Supplier;
use App\Modules\Tenant\Infrastructure\Persistence\Models\Company;
use App\Services\SaaS\AuditLogService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

final class PurchaseFiscalEntryController extends Controller
{
    use HandlesTenantAuthorization;

    private const READ_PERMISSION = 'purchasing.fiscal-entries.read';

    private const CREATE_PERMISSION = 'purchasing.fiscal-entries.create';

    private const UPDATE_PERMISSION = 'purchasing.fiscal-entries.update';

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

        abort_unless(in_array($sort, ['id', 'entry_date', 'status', 'amount_cents', 'created_at'], true), 404);

        $entries = PurchaseFiscalEntry::query()
            ->with(['supplier:id,name', 'purchaseOrder:id,purchase_order_number'])
            ->when($status !== '', static fn (Builder $query) => $query->where('status', $status))
            ->when($search !== '', static function (Builder $query) use ($search, $searchId): void {
                $query->where(function (Builder $nested) use ($search): void {
                    $nested
                        ->where('entry_number', 'like', "%{$search}%")
                        ->orWhere('document_number', 'like', "%{$search}%")
                        ->orWhere('notes', 'like', "%{$search}%");
                });

                if ($searchId !== null) {
                    $query->orWhere('id', $searchId);
                }
            })
            ->orderBy($sort, $direction)
            ->paginate(15)
            ->withQueryString();

        return view('client.purchasing.fiscal-entries.search', compact('entries', 'search', 'status', 'sort', 'direction', 'company'));
    }

    public function create(Request $request): View
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensurePermission($request, self::CREATE_PERMISSION, $company->id);

        return view('client.purchasing.fiscal-entries.form', [
            'entry' => null,
            'company' => $company,
            'suppliers' => $this->supplierOptions($company),
            'orders' => $this->orderOptions($company),
        ]);
    }

    public function show(Request $request, PurchaseFiscalEntry $entry): View
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensurePermission($request, self::READ_PERMISSION, $company->id);

        $entry->load(['supplier:id,name', 'purchaseOrder:id,purchase_order_number']);

        return view('client.purchasing.fiscal-entries.show', compact('entry', 'company'));
    }

    public function store(Request $request, AuditLogService $audit): RedirectResponse
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensurePermission($request, self::CREATE_PERMISSION, $company->id);

        $data = $this->validateEntry($request);

        $entry = PurchaseFiscalEntry::query()->create([
            'company_id' => $company->id,
            'entry_number' => $this->generateNumber($company),
            'purchase_order_id' => isset($data['purchase_order_id']) ? (int) $data['purchase_order_id'] : null,
            'supplier_id' => isset($data['supplier_id']) ? (int) $data['supplier_id'] : null,
            'document_number' => $data['document_number'] ?? null,
            'issue_date' => $data['issue_date'] ?? null,
            'entry_date' => $data['entry_date'],
            'status' => $data['status'],
            'amount_cents' => $this->moneyToCents((string) $data['amount']),
            'notes' => $data['notes'] ?? null,
        ]);

        $audit->record(
            'tenant_purchase_fiscal_entry.created',
            context: [
                'purchase_fiscal_entry_id' => $entry->id,
                'company_id' => $company->id,
                'actor_user_id' => $request->user()?->id,
            ],
            userId: $request->user()?->id,
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return redirect()->route('purchasing.fiscal-entries.index')->with('status', __('purchase_fiscal_entry.created'));
    }

    public function edit(Request $request, PurchaseFiscalEntry $entry): View
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensurePermission($request, self::UPDATE_PERMISSION, $company->id);

        return view('client.purchasing.fiscal-entries.form', [
            'entry' => $entry,
            'company' => $company,
            'suppliers' => $this->supplierOptions($company),
            'orders' => $this->orderOptions($company),
        ]);
    }

    public function update(Request $request, PurchaseFiscalEntry $entry, AuditLogService $audit): RedirectResponse
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensurePermission($request, self::UPDATE_PERMISSION, $company->id);

        $data = $this->validateEntry($request);

        $entry->fill([
            'purchase_order_id' => isset($data['purchase_order_id']) ? (int) $data['purchase_order_id'] : null,
            'supplier_id' => isset($data['supplier_id']) ? (int) $data['supplier_id'] : null,
            'document_number' => $data['document_number'] ?? null,
            'issue_date' => $data['issue_date'] ?? null,
            'entry_date' => $data['entry_date'],
            'status' => $data['status'],
            'amount_cents' => $this->moneyToCents((string) $data['amount']),
            'notes' => $data['notes'] ?? null,
        ]);
        $entry->save();

        $audit->record(
            'tenant_purchase_fiscal_entry.updated',
            context: [
                'purchase_fiscal_entry_id' => $entry->id,
                'company_id' => $company->id,
                'actor_user_id' => $request->user()?->id,
            ],
            userId: $request->user()?->id,
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return redirect()->route('purchasing.fiscal-entries.index')->with('status', __('purchase_fiscal_entry.updated'));
    }

    public function destroy(Request $request, PurchaseFiscalEntry $entry, AuditLogService $audit): RedirectResponse
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensurePermission($request, self::UPDATE_PERMISSION, $company->id);

        $entryId = $entry->id;
        $entryNumber = $entry->entry_number;
        $entry->delete();

        $audit->record(
            'tenant_purchase_fiscal_entry.removed',
            context: [
                'purchase_fiscal_entry_id' => $entryId,
                'purchase_fiscal_entry_number' => $entryNumber,
                'company_id' => $company->id,
                'actor_user_id' => $request->user()?->id,
            ],
            userId: $request->user()?->id,
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return redirect()->route('purchasing.fiscal-entries.index')->with('status', __('purchase_fiscal_entry.removed'));
    }

    /**
     * @return array{supplier_id?: int|string|null, purchase_order_id?: int|string|null, document_number?: string|null, issue_date?: string|null, entry_date: string, status: string, amount: string|int|float, notes?: string|null}
     */
    private function validateEntry(Request $request): array
    {
        return $request->validate([
            'supplier_id' => ['nullable', 'integer', Rule::exists('suppliers', 'id')],
            'purchase_order_id' => ['nullable', 'integer', Rule::exists('purchase_orders', 'id')],
            'document_number' => ['nullable', 'string', 'max:80'],
            'issue_date' => ['nullable', 'date'],
            'entry_date' => ['required', 'date'],
            'status' => ['required', Rule::in(['DRAFT', 'POSTED', 'CANCELLED'])],
            'amount' => ['required', 'string', 'max:30'],
            'notes' => ['nullable', 'string'],
        ]);
    }

    private function generateNumber(Company $company): string
    {
        do {
            $number = 'FIS-'.strtoupper(bin2hex(random_bytes(3)));
        } while (PurchaseFiscalEntry::query()->where('company_id', $company->id)->where('entry_number', $number)->exists());

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
