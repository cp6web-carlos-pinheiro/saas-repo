<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Tenant;

use App\Http\Controllers\Controller;
use App\Modules\Identity\Infrastructure\Persistence\Models\User;
use App\Modules\Tenant\Infrastructure\Persistence\Models\Company;
use App\Services\SaaS\AuditLogService;
use App\Services\SaaS\CompanyUserAccessService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

final class CompanyAccessUserController extends Controller
{
    private const READ_PERMISSION = 'company-access.users.read';

    private const CREATE_PERMISSION = 'company-access.users.create';

    private const UPDATE_PERMISSION = 'company-access.users.update';

    private const DELETE_PERMISSION = 'company-access.users.delete';

    public function index(Request $request): View
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensurePermission($request, self::READ_PERMISSION, $company->id);

        $search = trim((string) $request->query('search'));
        $searchTerms = preg_split('/\s+/', mb_strtolower($search), -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $sort = (string) $request->query('sort', 'name');
        $direction = (string) $request->query('direction', 'asc') === 'desc' ? 'desc' : 'asc';

        abort_unless(in_array($sort, ['name', 'email', 'is_active', 'created_at'], true), 404);

        $customers = User::query()
            ->whereHas('companies', static fn (Builder $query) => $query->where('companies.id', $company->id))
            ->with(['currentCompany:id,name'])
            ->when($search !== '', fn (Builder $query) => $this->applySearchFilters($query, $searchTerms))
            ->orderBy($sort, $direction)
            ->paginate(15)
            ->withQueryString();

        return view('client.access.search', compact('customers', 'search', 'sort', 'direction', 'company'));
    }

    public function create(Request $request, CompanyUserAccessService $access): View
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensurePermission($request, self::CREATE_PERMISSION, $company->id);

        return view('client.access.form', $this->formData(null, $company, $access));
    }

    public function show(Request $request, User $customer, CompanyUserAccessService $access): View
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensurePermission($request, self::READ_PERMISSION, $company->id);

        $customer = $this->companyCustomerOrFail($company, $customer->id);
        $customer->loadMissing('currentCompany:id,name,code,is_active,created_at,updated_at');

        return view('client.access.show', [
            'customer' => $customer,
            'company' => $company,
            'companyAccess' => $access->accessFor($customer, $company),
        ]);
    }

    public function store(Request $request, AuditLogService $audit, CompanyUserAccessService $access): RedirectResponse
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensurePermission($request, self::CREATE_PERMISSION, $company->id);

        $data = $this->validateCustomer($request);
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
            'tenant_access_user.created',
            context: [
                'customer_id' => $customer->id,
                'company_id' => $company->id,
                'actor_user_id' => $request->user()?->id,
            ],
            userId: $request->user()?->id,
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return redirect()->route('company-access.users.index')->with('status', __('company_access.created'));
    }

    public function edit(Request $request, User $customer, CompanyUserAccessService $access): View
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensurePermission($request, self::UPDATE_PERMISSION, $company->id);

        $customer = $this->companyCustomerOrFail($company, $customer->id);

        return view('client.access.form', $this->formData($customer, $company, $access));
    }

    public function update(Request $request, User $customer, AuditLogService $audit, CompanyUserAccessService $access): RedirectResponse
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensurePermission($request, self::UPDATE_PERMISSION, $company->id);

        $customer = $this->companyCustomerOrFail($company, $customer->id);
        $data = $this->validateCustomer($request, $customer);
        $isFirstCompanyUser = $company->users()->doesntExist() || $access->isFirstCompanyUser($customer, $company);
        $currentIsAdmin = $access->isCompanyAdministrator($customer, $company);
        $requestedProfile = (string) $data['access_profile'];
        $willBeAdmin = $isFirstCompanyUser || $requestedProfile === CompanyUserAccessService::ADMINISTRATOR_PROFILE;
        $willBeActive = (bool) ($data['is_active'] ?? false);

        if ($currentIsAdmin && ! $willBeAdmin) {
            return back()
                ->withInput()
                ->withErrors(['customer' => __('company_access.administrator_profile_locked')]);
        }

        if ($currentIsAdmin && ! $willBeActive && $access->countActiveCompanyAdministrators($company, $customer->id) === 0) {
            return back()
                ->withInput()
                ->withErrors(['customer' => __('company_access.last_administrator_required')]);
        }

        DB::transaction(function () use ($access, $company, $customer, $data, $isFirstCompanyUser): void {
            $customer->fill([
                'name' => $data['name'],
                'email' => mb_strtolower($data['email']),
                'is_active' => (bool) ($data['is_active'] ?? false),
            ]);

            if (! empty($data['password'])) {
                $customer->password = Hash::make($data['password']);
            }

            if ((int) ($customer->current_company_id ?? 0) !== (int) $company->id) {
                $customer->current_company_id = $company->id;
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
            'tenant_access_user.updated',
            context: [
                'customer_id' => $customer->id,
                'company_id' => $company->id,
                'actor_user_id' => $request->user()?->id,
            ],
            userId: $request->user()?->id,
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return redirect()->route('company-access.users.index')->with('status', __('company_access.updated'));
    }

    public function destroy(Request $request, User $customer, AuditLogService $audit): RedirectResponse
    {
        $company = $this->activeCompanyFrom($request);
        $this->ensurePermission($request, self::DELETE_PERMISSION, $company->id);

        $customer = $this->companyCustomerOrFail($company, $customer->id);

        if ((int) ($request->user()?->id ?? 0) === (int) $customer->id) {
            return back()->withErrors(['customer' => __('company_access.cannot_remove_self')]);
        }

        if (app(CompanyUserAccessService::class)->isCompanyAdministrator($customer, $company)
            && app(CompanyUserAccessService::class)->countActiveCompanyAdministrators($company, $customer->id) === 0) {
            return back()->withErrors(['customer' => __('company_access.last_administrator_required')]);
        }

        $customerId = $customer->id;
        $customerName = $customer->name;

        DB::transaction(function () use ($company, $customer): void {
            $customer->roles()->newPivotStatement()
                ->where('user_id', $customer->id)
                ->where('company_id', $company->id)
                ->delete();

            $customer->companies()->detach($company->id);

            if ((int) ($customer->current_company_id ?? 0) === (int) $company->id) {
                $nextCompanyId = $customer->companies()->orderBy('company_user.is_default', 'desc')->value('companies.id');
                $customer->forceFill(['current_company_id' => $nextCompanyId])->save();
            }

            $hasAnyCompany = $customer->companies()->exists();

            if (! $hasAnyCompany) {
                $customer->delete();
            }
        });

        $audit->record(
            'tenant_access_user.removed',
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

        return redirect()->route('company-access.users.index')->with('status', __('company_access.removed'));
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

    private function companyCustomerOrFail(Company $company, int $customerId): User
    {
        return User::query()
            ->whereKey($customerId)
            ->whereHas('companies', static fn (Builder $query) => $query->where('companies.id', $company->id))
            ->firstOrFail();
    }

    private function validateCustomer(Request $request, ?User $customer = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:190', Rule::unique('users', 'email')->ignore($customer)],
            'password' => [$customer ? 'nullable' : 'required', 'confirmed', 'min:10'],
            'is_active' => ['nullable', 'boolean'],
            'access_profile' => ['required', Rule::in([CompanyUserAccessService::ADMINISTRATOR_PROFILE, CompanyUserAccessService::CUSTOM_PROFILE])],
            'modules' => ['nullable', 'array', 'required_if:access_profile,'.CompanyUserAccessService::CUSTOM_PROFILE, 'min:1'],
            'modules.*' => ['required', 'string', 'distinct', Rule::exists('permissions', 'module')],
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function formData(?User $customer, Company $company, CompanyUserAccessService $access): array
    {
        $companyAccess = $customer !== null
            ? $access->accessFor($customer, $company)
            : ['profile' => CompanyUserAccessService::CUSTOM_PROFILE, 'modules' => []];

        $isAdministratorProfileLocked = $customer !== null
            && $companyAccess['profile'] === CompanyUserAccessService::ADMINISTRATOR_PROFILE;

        return [
            'customer' => $customer,
            'company' => $company,
            'modules' => $access->modules(),
            'accessProfile' => $companyAccess['profile'],
            'selectedModules' => $companyAccess['modules'],
            'isAdministratorProfileLocked' => $isAdministratorProfileLocked,
            'mustBeAdministrator' => $customer === null
                ? $company->users()->doesntExist()
                : $access->isFirstCompanyUser($customer, $company),
        ];
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
            ->orWhere('users.email', 'like', "%{$term}%");

        if ($termIsNumeric) {
            $termQuery->orWhere('users.id', (int) $term);
        }

        if ($termIsActive) {
            $termQuery->orWhere('users.is_active', true);
        }

        if ($termIsInactive) {
            $termQuery->orWhere('users.is_active', false);
        }
    }
}
