<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Identity\Infrastructure\Persistence\Models\User;
use App\Modules\Tenant\Infrastructure\Persistence\Models\Company;
use App\Services\SaaS\AuditLogService;
use App\Services\SaaS\CompanyUserAccessService;
use App\Support\Security\PasswordPolicy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

final class GlobalCustomerController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search'));
        $searchTerms = preg_split('/\s+/', mb_strtolower($search), -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $sort = (string) $request->query('sort', 'name');
        $direction = (string) $request->query('direction', 'asc') === 'desc' ? 'desc' : 'asc';

        abort_unless(in_array($sort, ['id', 'name', 'email', 'company', 'is_active', 'created_at'], true), 404);

        $customersQuery = User::query()
            ->with(['currentCompany:id,name,code,is_active,created_at,updated_at'])
            ->when($search !== '', fn (Builder $query) => $this->applySearchFilters($query, $searchTerms));

        if ($sort === 'company') {
            $customersQuery->orderBy(Company::query()->select('name')->whereColumn('companies.id', 'users.current_company_id'), $direction);
        } else {
            $customersQuery->orderBy($sort, $direction);
        }

        $customers = $customersQuery
            ->orderBy('id')
            ->paginate(15)
            ->withQueryString();

        return view('admin.customer.search', compact('customers', 'search', 'sort', 'direction'));
    }

    public function create(Request $request, CompanyUserAccessService $access): View
    {
        $contextCompany = $this->resolveContextCompany($request);

        return view('admin.customer.form', $this->formData(null, $contextCompany, $access));

    }

    public function show(User $customer, CompanyUserAccessService $access): View
    {
        $customer->loadMissing(
            'currentCompany:id,name,code,is_active,created_at,updated_at',
            'companies:id,name,code,is_active,created_at,updated_at',
            'roles.permissions'
        );

        $companyAccesses = $customer->companies
            ->sortBy('name')
            ->values()
            ->map(fn (Company $company): array => [
                'company' => $company,
                'access' => $access->accessFor($customer, $company),
                'is_current' => (int) $customer->current_company_id === (int) $company->id,
            ]);

        return view('admin.customer.show', compact('customer', 'companyAccesses'));
    }

    public function store(Request $request, AuditLogService $audit, CompanyUserAccessService $access): RedirectResponse
    {
        $data = $this->validateCustomer($request);
        $company = Company::query()->findOrFail($data['company_id']);
        $isFirstCompanyUser = $company->users()->doesntExist();

        $customer = DB::transaction(function () use ($access, $company, $data, $isFirstCompanyUser): User {
            $customer = User::query()->create([
                'name' => $data['name'],
                'email' => mb_strtolower($data['email']),
                'password' => Hash::make($data['password']),
                'is_active' => true,
                'current_company_id' => $company->id,
            ]);

            $access->sync(
                $customer,
                $company,
                $data['access_profile'],
                $data['modules'] ?? [],
                $isFirstCompanyUser,
            );

            return $customer;
        });

        $audit->record(
            'platform_customer.created',
            context: ['customer_id' => $customer->id, 'company_id' => $company->id, 'admin_id' => $request->user('admin')->id],
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return $this->redirectAfterSave($request, __('global_customer.created'));
    }

    public function edit(Request $request, User $customer, CompanyUserAccessService $access): View
    {
        $customer->loadMissing('currentCompany', 'companies');
        $contextCompany = $this->resolveContextCompany($request, $customer);

        return view('admin.customer.form', $this->formData($customer, $contextCompany, $access));
    }

    public function update(Request $request, User $customer, AuditLogService $audit, CompanyUserAccessService $access): RedirectResponse
    {
        $data = $this->validateCustomer($request, $customer);
        $company = Company::query()->findOrFail($data['company_id']);
        $isFirstCompanyUser = $company->users()->doesntExist() || $access->isFirstCompanyUser($customer, $company);

        DB::transaction(function () use ($access, $company, $customer, $data, $isFirstCompanyUser): void {
            $customer->fill([
                'name' => $data['name'],
                'email' => mb_strtolower($data['email']),
                'is_active' => (bool) ($data['is_active'] ?? false),
            ]);

            if (! empty($data['password'])) {
                $customer->password = Hash::make($data['password']);
            }

            $customer->save();

            $access->sync(
                $customer,
                $company,
                $data['access_profile'],
                $data['modules'] ?? [],
                $isFirstCompanyUser,
            );
        });

        $audit->record(
            'platform_customer.updated',
            context: ['customer_id' => $customer->id, 'company_id' => $company->id, 'admin_id' => $request->user('admin')->id],
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return $this->redirectAfterSave($request, __('global_customer.updated'));
    }

    public function destroy(Request $request, User $customer, AuditLogService $audit): RedirectResponse
    {
        $customerId = $customer->id;
        $customer->delete();

        $audit->record(
            'platform_customer.removed',
            context: ['customer_id' => $customerId, 'admin_id' => $request->user('admin')->id],
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return redirect()->route('global-admin.customers.index')->with('status', __('global_customer.removed'));
    }

    private function validateCustomer(Request $request, ?User $customer = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:190', Rule::unique('users', 'email')->ignore($customer)],
            'password' => [$customer ? 'nullable' : 'required', 'confirmed', PasswordPolicy::rule()],
            'is_active' => ['nullable', 'boolean'],
            'company_id' => ['required', 'integer', Rule::exists('companies', 'id')],
            'access_profile' => ['required', Rule::in([CompanyUserAccessService::ADMINISTRATOR_PROFILE, CompanyUserAccessService::CUSTOM_PROFILE])],
            'modules' => ['nullable', 'array', 'required_if:access_profile,'.CompanyUserAccessService::CUSTOM_PROFILE, 'min:1'],
            'modules.*' => ['required', 'string', 'distinct', Rule::exists('permissions', 'module')],
            'return_to_company_id' => ['nullable', 'integer', Rule::exists('companies', 'id')],
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function formData(?User $customer, ?Company $contextCompany, CompanyUserAccessService $access): array
    {
        $selectedCompany = $contextCompany ?? $customer?->currentCompany;
        $companyAccess = $customer !== null && $selectedCompany !== null
            ? $access->accessFor($customer, $selectedCompany)
            : ['profile' => CompanyUserAccessService::CUSTOM_PROFILE, 'modules' => []];

        return [
            'customer' => $customer,
            'contextCompany' => $contextCompany,
            'companies' => Company::query()->orderBy('name')->get(['id', 'name', 'code']),
            'modules' => $access->modules(),
            'accessProfile' => $companyAccess['profile'],
            'selectedModules' => $companyAccess['modules'],
            'mustBeAdministrator' => $selectedCompany !== null && ($customer === null
                ? $selectedCompany->users()->doesntExist()
                : $access->isFirstCompanyUser($customer, $selectedCompany)),
        ];
    }

    private function resolveContextCompany(Request $request, ?User $customer = null): ?Company
    {
        $companyId = $request->query('company_id');

        if (! ctype_digit((string) $companyId)) {
            return null;
        }

        $company = Company::query()->find((int) $companyId);

        if ($company === null || $customer === null) {
            return $company;
        }

        $isLinked = $customer->companies->contains(static fn (Company $item): bool => (int) $item->id === (int) $company->id)
            || (int) $customer->current_company_id === (int) $company->id;

        return $isLinked ? $company : null;
    }

    private function redirectAfterSave(Request $request, string $status): RedirectResponse
    {
        $returnCompanyId = $request->input('return_to_company_id');

        if (ctype_digit((string) $returnCompanyId) && Company::query()->whereKey((int) $returnCompanyId)->exists()) {
            return redirect()->route('global-admin.companies.show', ['company' => (int) $returnCompanyId])
                ->with('status', $status);
        }

        return redirect()->route('global-admin.customers.index')->with('status', $status);
    }

    private function applySearchFilters(Builder $query, array $searchTerms): void
    {
        $query->where(function (Builder $customerQuery) use ($searchTerms): void {
            foreach ($searchTerms as $term) {
                $customerQuery->where(fn (Builder $termQuery) => $this->applyTermFilters($termQuery, $term));
            }
        });
    }

    private function applyTermFilters(Builder $termQuery, string $term): void
    {
        $termIsActive = in_array($term, ['ativo', 'active'], true);
        $termIsInactive = in_array($term, ['inativo', 'inactive'], true);
        $termIsNumeric = ctype_digit($term);

        $termQuery
            ->where('users.name', 'like', "%{$term}%")
            ->orWhere('users.email', 'like', "%{$term}%")
            ->orWhereHas('currentCompany', static fn (Builder $companyQuery) => $companyQuery
                ->where('companies.name', 'like', "%{$term}%")
                ->orWhere('companies.code', 'like', "%{$term}%"))
            ->orWhereHas('companies', static fn (Builder $companyQuery) => $companyQuery
                ->where('companies.name', 'like', "%{$term}%")
                ->orWhere('companies.code', 'like', "%{$term}%"));

        if ($termIsNumeric) {
            $termId = (int) $term;

            $termQuery->orWhere('users.id', $termId)
                ->orWhere('users.current_company_id', $termId)
                ->orWhereHas('currentCompany', static fn (Builder $companyQuery) => $companyQuery->where('companies.id', $termId))
                ->orWhereHas('companies', static fn (Builder $companyQuery) => $companyQuery->where('companies.id', $termId));
        }

        if ($termIsActive) {
            $termQuery->orWhere('users.is_active', true)
                ->orWhereHas('currentCompany', static fn (Builder $companyQuery) => $companyQuery->where('companies.is_active', true));
        }

        if ($termIsInactive) {
            $termQuery->orWhere('users.is_active', false)
                ->orWhereHas('currentCompany', static fn (Builder $companyQuery) => $companyQuery->where('companies.is_active', false));
        }
    }
}
