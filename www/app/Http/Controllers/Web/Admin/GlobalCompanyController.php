<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\SaaS\Subscription;
use App\Modules\Tenant\Infrastructure\Persistence\Models\Company;
use App\Services\SaaS\AccountOnboardingService;
use App\Services\SaaS\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

final class GlobalCompanyController extends Controller
{
    public function index(Request $request, AccountOnboardingService $service): View
    {
        $search = trim((string) $request->query('search'));
        $sort = (string) $request->query('sort', 'name');
        $direction = (string) $request->query('direction', 'asc') === 'desc' ? 'desc' : 'asc';

        abort_unless(in_array($sort, ['id', 'name', 'code', 'active_plan', 'users_count', 'is_active', 'created_at'], true), 404);

        $companiesQuery = Company::query()
            ->withCount('users')
            ->with([
                'latestSubscription' => static fn ($query) => $query->select([
                    'subscriptions.id',
                    'subscriptions.company_id',
                    'subscriptions.plan_code',
                    'subscriptions.status',
                ]),
            ])
            ->when($search !== '', static fn ($query) => $query->where(static fn ($q) => $q
                ->where('name', 'like', "%{$search}%")
                ->orWhere('code', 'like', "%{$search}%")
                ->orWhere('id', 'like', "%{$search}%")));

        if ($sort === 'active_plan') {
            $companiesQuery->orderBy(
                Subscription::query()->select('plan_code')->whereColumn('subscriptions.company_id', 'companies.id')->latest('id')->limit(1),
                $direction
            );
        } else {
            $companiesQuery->orderBy($sort, $direction);
        }

        $companies = $companiesQuery
            ->orderBy('id')
            ->paginate(15)
            ->withQueryString();

        $companies->setCollection(
            $companies->getCollection()->map(function (Company $company) use ($service): Company {
                $subscription = $company->latestSubscription;
                $hasActivePlan = $subscription !== null && in_array((string) $subscription->status, ['active', 'trialing'], true);
                $plan = $hasActivePlan ? $service->planForCode((string) $subscription->plan_code) : null;

                $company->setAttribute('active_plan_label', $plan['label'] ?? null);

                return $company;
            })
        );

        return view('admin.company.search', compact('companies', 'search', 'sort', 'direction'));
    }

    public function create(): View
    {
        return view('admin.company.form', ['company' => null]);
    }

    public function show(Company $company, AccountOnboardingService $service): View
    {
        $company->load([
            'users' => static fn ($query) => $query->orderBy('name'),
            'latestSubscription' => static fn ($query) => $query->select([
                'subscriptions.id',
                'subscriptions.company_id',
                'subscriptions.plan_code',
                'subscriptions.status',
                'subscriptions.provider',
                'subscriptions.starts_at',
                'subscriptions.ends_at',
                'subscriptions.canceled_at',
            ]),
        ]);

        $subscription = $company->latestSubscription;
        $selectedPlan = $subscription ? $service->planForCode((string) $subscription->plan_code) : null;
        $selectedPlanStatus = match ((string) ($subscription->status ?? '')) {
            'active' => __('global_company.subscription_status_active'),
            'trialing' => __('global_company.subscription_status_trialing'),
            'canceled' => __('global_company.subscription_status_canceled'),
            default => (string) ($subscription->status ?? __('global_company.no_active_plan')),
        };

        return view('admin.company.show', compact('company', 'subscription', 'selectedPlan', 'selectedPlanStatus'));
    }

    public function store(Request $request, AuditLogService $audit): RedirectResponse
    {
        $data = $this->validateCompany($request);

        $company = Company::query()->create([
            'name' => $data['name'],
            'code' => mb_strtolower($data['code']),
            'is_active' => true,
        ]);

        $audit->record(
            'platform_company.created',
            context: ['company_id' => $company->id, 'admin_id' => $request->user('admin')->id],
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return redirect()->route('global-admin.companies.index')->with('status', __('global_company.created'));
    }

    public function edit(Company $company): View
    {
        return view('admin.company.form', compact('company'));
    }

    public function update(Request $request, Company $company, AuditLogService $audit): RedirectResponse
    {
        $data = $this->validateCompany($request, $company);

        $company->fill([
            'name' => $data['name'],
            'code' => mb_strtolower($data['code']),
            'is_active' => (bool) ($data['is_active'] ?? false),
        ]);

        $company->save();

        $audit->record(
            'platform_company.updated',
            context: ['company_id' => $company->id, 'admin_id' => $request->user('admin')->id],
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return redirect()->route('global-admin.companies.index')->with('status', __('global_company.updated'));
    }

    public function destroy(Request $request, Company $company, AuditLogService $audit): RedirectResponse
    {
        $companyId = $company->id;
        $company->delete();

        $audit->record(
            'platform_company.removed',
            context: ['company_id' => $companyId, 'admin_id' => $request->user('admin')->id],
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return redirect()->route('global-admin.companies.index')->with('status', __('global_company.removed'));
    }

    private function validateCompany(Request $request, ?Company $company = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'code' => ['required', 'string', 'max:50', Rule::unique('companies', 'code')->ignore($company)],
            'is_active' => ['nullable', 'boolean'],
        ]);
    }
}
