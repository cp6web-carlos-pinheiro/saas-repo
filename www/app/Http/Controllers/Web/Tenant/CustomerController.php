<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Tenant;

use App\Http\Controllers\Controller;
use App\Modules\Customer\Infrastructure\Persistence\Models\Customer;
use App\Modules\Identity\Infrastructure\Persistence\Models\User;
use App\Modules\Tenant\Infrastructure\Persistence\Models\Company;
use App\Modules\Tenant\Infrastructure\Persistence\Models\MasterDataRecord;
use App\Services\SaaS\AuditLogService;
use App\Services\SaaS\CompanyUserAccessService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

final class CustomerController extends Controller
{
    private const READ_PERMISSION = 'sales.customers.read';

    private const CREATE_PERMISSION = 'sales.customers.create';

    private const UPDATE_PERMISSION = 'sales.customers.update';

    public function index(Request $request): View
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensurePermission($request, self::READ_PERMISSION, $company->id);

        $search = trim((string) $request->query('search'));
        $searchTerms = preg_split('/\s+/', mb_strtolower($search), -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $personType = mb_strtoupper(trim((string) $request->query('person_type')));
        $status = mb_strtoupper(trim((string) $request->query('status')));
        $sort = (string) $request->query('sort', 'name');
        $direction = (string) $request->query('direction', 'asc') === 'desc' ? 'desc' : 'asc';

        if (! in_array($personType, ['PF', 'PJ'], true)) {
            $personType = '';
        }

        if (! in_array($status, ['ACTIVE', 'INACTIVE'], true)) {
            $status = '';
        }

        abort_unless(in_array($sort, ['name', 'person_type', 'status', 'created_at'], true), 404);

        $customers = Customer::query()
            ->when($personType !== '', static fn (Builder $query) => $query->where('person_type', $personType))
            ->when($status !== '', static fn (Builder $query) => $query->where('status', $status))
            ->when($search !== '', fn (Builder $query) => $this->applySearchFilters($query, $searchTerms))
            ->orderBy($sort, $direction)
            ->paginate(15)
            ->withQueryString();

        return view('client.customers.search', compact('customers', 'search', 'sort', 'direction', 'personType', 'status', 'company'));
    }

    public function create(Request $request): View
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensurePermission($request, self::CREATE_PERMISSION, $company->id);

        return view('client.customers.form', [
            'customer' => null,
            'company' => $company,
            'cfops' => $this->masterDataOptions($company, 'cfops'),
            'taxProfiles' => $this->masterDataOptions($company, 'taxes'),
        ]);
    }

    public function show(Request $request, Customer $customer): View
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensurePermission($request, self::READ_PERMISSION, $company->id);

        return view('client.customers.show', compact('customer', 'company'));
    }

    public function store(Request $request, AuditLogService $audit): RedirectResponse
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensurePermission($request, self::CREATE_PERMISSION, $company->id);

        $data = $this->validateCustomer($request, $company);
        $customer = Customer::query()->create([
            'company_id' => $company->id,
            'code' => $this->generateCode($company),
            'name' => $data['name'],
            'person_type' => $data['person_type'],
            'tax_id' => $data['tax_id'] ?? null,
            'email' => $data['email'] ?? null,
            'phone' => $data['phone'] ?? null,
            'status' => $data['status'],
            'default_cfop_id' => isset($data['default_cfop_id']) ? (int) $data['default_cfop_id'] : null,
            'tax_profile_id' => isset($data['tax_profile_id']) ? (int) $data['tax_profile_id'] : null,
        ]);

        $audit->record(
            'tenant_customer.created',
            context: [
                'customer_id' => $customer->id,
                'company_id' => $company->id,
                'actor_user_id' => $request->user()?->id,
            ],
            userId: $request->user()?->id,
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return redirect()->route('customers.index')->with('status', __('customer.created'));
    }

    public function edit(Request $request, Customer $customer): View
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensurePermission($request, self::UPDATE_PERMISSION, $company->id);

        return view('client.customers.form', [
            'customer' => $customer,
            'company' => $company,
            'cfops' => $this->masterDataOptions($company, 'cfops'),
            'taxProfiles' => $this->masterDataOptions($company, 'taxes'),
        ]);
    }

    public function update(Request $request, Customer $customer, AuditLogService $audit): RedirectResponse
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensurePermission($request, self::UPDATE_PERMISSION, $company->id);

        $data = $this->validateCustomer($request, $company, $customer);

        $customer->fill([
            'name' => $data['name'],
            'person_type' => $data['person_type'],
            'tax_id' => $data['tax_id'] ?? null,
            'email' => $data['email'] ?? null,
            'phone' => $data['phone'] ?? null,
            'status' => $data['status'],
            'default_cfop_id' => isset($data['default_cfop_id']) ? (int) $data['default_cfop_id'] : null,
            'tax_profile_id' => isset($data['tax_profile_id']) ? (int) $data['tax_profile_id'] : null,
        ]);
        $customer->save();

        $audit->record(
            'tenant_customer.updated',
            context: [
                'customer_id' => $customer->id,
                'company_id' => $company->id,
                'actor_user_id' => $request->user()?->id,
            ],
            userId: $request->user()?->id,
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return redirect()->route('customers.index')->with('status', __('customer.updated'));
    }

    public function destroy(Request $request, Customer $customer, AuditLogService $audit): RedirectResponse
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensurePermission($request, self::UPDATE_PERMISSION, $company->id);

        $customerId = $customer->id;
        $customerName = $customer->name;
        $customer->delete();

        $audit->record(
            'tenant_customer.removed',
            context: [
                'customer_id' => $customerId,
                'customer_name' => $customerName,
                'company_id' => $company->id,
                'actor_user_id' => $request->user()?->id,
            ],
            userId: $request->user()?->id,
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return redirect()->route('customers.index')->with('status', __('customer.removed'));
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

    private function validateCustomer(Request $request, Company $company, ?Customer $customer = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:180'],
            'person_type' => ['required', Rule::in(['PF', 'PJ'])],
            'tax_id' => [
                'nullable',
                'string',
                'max:30',
                function (string $attribute, mixed $value, \Closure $fail) use ($request): void {
                    if ($value === null || $value === '') {
                        return;
                    }

                    $personType = (string) $request->input('person_type', 'PJ');

                    if (! $this->isValidTaxIdFormat((string) $value, $personType)) {
                        $fail(__('validation.regex', ['attribute' => $attribute]));
                    }
                },
            ],
            'email' => ['nullable', 'email', 'max:180'],
            'phone' => ['nullable', 'string', 'max:50'],
            'status' => ['required', Rule::in(['ACTIVE', 'INACTIVE'])],
            'default_cfop_id' => [
                'nullable',
                'integer',
                Rule::exists('master_data_records', 'id')->where(static fn ($query) => $query->where('company_id', $company->id)->where('domain', 'cfops')),
            ],
            'tax_profile_id' => [
                'nullable',
                'integer',
                Rule::exists('master_data_records', 'id')->where(static fn ($query) => $query->where('company_id', $company->id)->where('domain', 'taxes')),
            ],
        ]);
    }

    /**
     * @return array<int, string>
     */
    private function masterDataOptions(Company $company, string $domain): array
    {
        return MasterDataRecord::query()
            ->where('company_id', $company->id)
            ->where('domain', $domain)
            ->where('is_active', true)
            ->orderBy('code')
            ->get(['id', 'code', 'name'])
            ->mapWithKeys(static fn (MasterDataRecord $record): array => [$record->id => sprintf('%s - %s', $record->code, $record->name)])
            ->all();
    }

    private function generateCode(Company $company): string
    {
        do {
            $code = 'CUS-'.strtoupper(bin2hex(random_bytes(3)));
        } while (Customer::query()->where('company_id', $company->id)->where('code', $code)->exists());

        return $code;
    }

    private function isValidTaxIdFormat(string $taxId, string $personType): bool
    {
        $digits = preg_replace('/\D+/', '', $taxId) ?? '';

        if ($personType === 'PF') {
            if (strlen($digits) !== 11) {
                return false;
            }

            if (preg_match('/^(\d)\1{10}$/', $digits) === 1) {
                return false;
            }

            if (preg_match('/^(\d{3}\.\d{3}\.\d{3}-\d{2}|\d{11})$/', $taxId) !== 1) {
                return false;
            }

            return $this->isValidCpfDigits($digits);
        }

        if (strlen($digits) !== 14) {
            return false;
        }

        if (preg_match('/^(\d)\1{13}$/', $digits) === 1) {
            return false;
        }

        if (preg_match('/^(\d{2}\.\d{3}\.\d{3}\/\d{4}-\d{2}|\d{14})$/', $taxId) !== 1) {
            return false;
        }

        return $this->isValidCnpjDigits($digits);
    }

    private function isValidCpfDigits(string $digits): bool
    {
        $sum = 0;

        for ($i = 0; $i < 9; $i++) {
            $sum += ((int) $digits[$i]) * (10 - $i);
        }

        $remainder = $sum % 11;
        $firstDigit = $remainder < 2 ? 0 : 11 - $remainder;

        if ($firstDigit !== (int) $digits[9]) {
            return false;
        }

        $sum = 0;

        for ($i = 0; $i < 10; $i++) {
            $sum += ((int) $digits[$i]) * (11 - $i);
        }

        $remainder = $sum % 11;
        $secondDigit = $remainder < 2 ? 0 : 11 - $remainder;

        return $secondDigit === (int) $digits[10];
    }

    private function isValidCnpjDigits(string $digits): bool
    {
        $firstWeights = [5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
        $secondWeights = [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];

        $sum = 0;

        for ($i = 0; $i < 12; $i++) {
            $sum += ((int) $digits[$i]) * $firstWeights[$i];
        }

        $remainder = $sum % 11;
        $firstDigit = $remainder < 2 ? 0 : 11 - $remainder;

        if ($firstDigit !== (int) $digits[12]) {
            return false;
        }

        $sum = 0;

        for ($i = 0; $i < 13; $i++) {
            $sum += ((int) $digits[$i]) * $secondWeights[$i];
        }

        $remainder = $sum % 11;
        $secondDigit = $remainder < 2 ? 0 : 11 - $remainder;

        return $secondDigit === (int) $digits[13];
    }

    /**
     * @param  array<int, string>  $searchTerms
     */
    private function applySearchFilters(Builder $query, array $searchTerms): void
    {
        foreach ($searchTerms as $term) {
            $query->where(function (Builder $nested) use ($term): void {
                $nested->whereRaw('LOWER(name) like ?', ["%{$term}%"])
                    ->orWhereRaw('LOWER(person_type) like ?', ["%{$term}%"])
                    ->orWhereRaw("LOWER(coalesce(tax_id, '')) like ?", ["%{$term}%"])
                    ->orWhereRaw("LOWER(coalesce(email, '')) like ?", ["%{$term}%"])
                    ->orWhereRaw("LOWER(coalesce(phone, '')) like ?", ["%{$term}%"])
                    ->orWhereRaw('LOWER(status) like ?', ["%{$term}%"]);

                if (ctype_digit($term)) {
                    $nested->orWhere('id', (int) $term);
                }
            });
        }
    }
}
