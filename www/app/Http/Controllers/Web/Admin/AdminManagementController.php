<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\SaaS\Organization;
use App\Models\SaaS\Subscription;
use App\Models\SaaS\Trial;
use App\Modules\Identity\Infrastructure\Persistence\Models\User;
use App\Modules\Tenant\Infrastructure\Persistence\Models\Company;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class AdminManagementController extends Controller
{
    public function index(Request $request): View
    {
        $companyFilters = [
            'search' => trim((string) $request->query('company_search', '')),
            'is_active' => (string) $request->query('company_is_active', ''),
            'plan_status' => (string) $request->query('company_plan_status', ''),
        ];

        $userFilters = [
            'search' => trim((string) $request->query('user_search', '')),
            'is_active' => (string) $request->query('user_is_active', ''),
            'email_verified' => (string) $request->query('user_email_verified', ''),
            'is_platform_admin' => (string) $request->query('user_is_platform_admin', ''),
        ];

        $companiesQuery = Company::query()
            ->withCount('users')
            ->orderByDesc('id');

        if ($companyFilters['search'] !== '') {
            $term = '%'.$companyFilters['search'].'%';

            $companiesQuery->where(function ($query) use ($term): void {
                $query->where('name', 'like', $term)
                    ->orWhere('code', 'like', $term)
                    ->orWhereHas('organization', static function ($orgQuery) use ($term): void {
                        $orgQuery->where('name', 'like', $term)
                            ->orWhere('slug', 'like', $term)
                            ->orWhere('domain', 'like', $term);
                    });
            });
        }

        if (in_array($companyFilters['is_active'], ['0', '1'], true)) {
            $companiesQuery->where('is_active', (bool) ((int) $companyFilters['is_active']));
        }

        if ($companyFilters['plan_status'] !== '') {
            $planStatus = $companyFilters['plan_status'];

            $companiesQuery->whereHas('organization.subscriptions', static function ($subscriptionQuery) use ($planStatus): void {
                $subscriptionQuery->where('status', $planStatus);
            });
        }

        $companies = $companiesQuery
            ->paginate(12, ['*'], 'companies_page')
            ->appends($request->except('companies_page'));

        $companyIds = $companies->getCollection()->pluck('id')->values();

        $organizations = Organization::query()
            ->whereIn('company_id', $companyIds)
            ->get()
            ->keyBy('company_id');

        $organizationIds = $organizations->pluck('id')->values();

        $subscriptions = Subscription::query()
            ->whereIn('organization_id', $organizationIds)
            ->orderByDesc('id')
            ->get()
            ->unique('organization_id')
            ->keyBy('organization_id');

        $trials = Trial::query()
            ->whereIn('organization_id', $organizationIds)
            ->orderByDesc('id')
            ->get()
            ->unique('organization_id')
            ->keyBy('organization_id');

        $usersQuery = User::query()
            ->with(['currentCompany:id,name'])
            ->withCount('companies')
            ->orderByDesc('id');

        if ($userFilters['search'] !== '') {
            $term = '%'.$userFilters['search'].'%';

            $usersQuery->where(function ($query) use ($term): void {
                $query->where('name', 'like', $term)
                    ->orWhere('email', 'like', $term)
                    ->orWhereHas('currentCompany', static function ($companyQuery) use ($term): void {
                        $companyQuery->where('name', 'like', $term)
                            ->orWhere('code', 'like', $term);
                    })
                    ->orWhereHas('companies', static function ($companyQuery) use ($term): void {
                        $companyQuery->where('name', 'like', $term)
                            ->orWhere('code', 'like', $term);
                    });
            });
        }

        if (in_array($userFilters['is_active'], ['0', '1'], true)) {
            $usersQuery->where('is_active', (bool) ((int) $userFilters['is_active']));
        }

        if (in_array($userFilters['email_verified'], ['0', '1'], true)) {
            if ($userFilters['email_verified'] === '1') {
                $usersQuery->whereNotNull('email_verified_at');
            } else {
                $usersQuery->whereNull('email_verified_at');
            }
        }

        if (in_array($userFilters['is_platform_admin'], ['0', '1'], true)) {
            $usersQuery->where('is_platform_admin', (bool) ((int) $userFilters['is_platform_admin']));
        }

        $users = $usersQuery
            ->paginate(20, ['*'], 'users_page')
            ->appends($request->except('users_page'));

        return view('admin.management', [
            'companies' => $companies,
            'organizationsByCompany' => $organizations,
            'subscriptionsByOrganization' => $subscriptions,
            'trialsByOrganization' => $trials,
            'users' => $users,
            'companyFilters' => $companyFilters,
            'userFilters' => $userFilters,
        ]);
    }

    public function updateCompanyStatus(Request $request, Company $company): RedirectResponse
    {
        $validated = $request->validate([
            'is_active' => ['required', 'boolean'],
        ]);

        $company->update([
            'is_active' => (bool) $validated['is_active'],
        ]);

        return back()->with('status', __('messages.admin_company_status_updated'));
    }

    public function updateCompanyPlan(Request $request, Company $company): RedirectResponse
    {
        $validated = $request->validate([
            'plan_code' => ['required', 'string', 'max:80'],
            'status' => ['required', 'in:trialing,active,past_due,canceled,suspended'],
        ]);

        $organization = Organization::query()->where('company_id', $company->id)->first();

        if (! $organization) {
            return back()->withErrors([
                'plan' => __('messages.admin_company_plan_org_not_found'),
            ]);
        }

        $subscription = Subscription::query()
            ->where('organization_id', $organization->id)
            ->latest('id')
            ->first();

        if ($subscription === null) {
            Subscription::query()->create([
                'organization_id' => $organization->id,
                'provider' => 'manual',
                'plan_code' => $validated['plan_code'],
                'status' => $validated['status'],
                'starts_at' => now(),
                'ends_at' => null,
                'canceled_at' => $validated['status'] === 'canceled' ? now() : null,
            ]);

            return back()->with('status', __('messages.admin_company_plan_created'));
        }

        $subscription->update([
            'plan_code' => $validated['plan_code'],
            'status' => $validated['status'],
            'canceled_at' => $validated['status'] === 'canceled' ? now() : null,
        ]);

        return back()->with('status', __('messages.admin_company_plan_updated'));
    }

    public function updateUserStatus(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'is_active' => ['required', 'boolean'],
        ]);

        $user->update([
            'is_active' => (bool) $validated['is_active'],
        ]);

        return back()->with('status', __('messages.admin_user_status_updated'));
    }

    public function updateUserEmailVerification(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'is_verified' => ['required', 'boolean'],
        ]);

        $user->forceFill([
            'email_verified_at' => (bool) $validated['is_verified'] ? now() : null,
        ])->save();

        return back()->with('status', __('messages.admin_user_email_verification_updated'));
    }

    public function updatePlatformAdmin(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'is_platform_admin' => ['required', 'boolean'],
        ]);

        $promoteToAdmin = (bool) $validated['is_platform_admin'];

        if (! $promoteToAdmin && (bool) $user->is_platform_admin) {
            $adminCount = User::query()->where('is_platform_admin', true)->count();

            if ($adminCount <= 1) {
                return back()->withErrors([
                    'admin' => __('messages.admin_last_platform_admin_cannot_be_removed'),
                ]);
            }
        }

        $user->update([
            'is_platform_admin' => $promoteToAdmin,
        ]);

        return back()->with('status', __('messages.admin_permissions_updated'));
    }
}
