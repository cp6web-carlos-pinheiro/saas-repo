<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\SaaS\Subscription;
use App\Modules\Identity\Infrastructure\Persistence\Models\User;
use App\Modules\Tenant\Infrastructure\Persistence\Models\Company;
use App\Services\SaaS\AccountOnboardingService;
use App\Services\SaaS\CompanyUserAccessService;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class IndustrialDashboardController extends Controller
{
    public function __invoke(Request $request, AccountOnboardingService $service, CompanyUserAccessService $access): View
    {
        $user = $request->user();
        $organization = null;
        $subscription = null;
        $subscriptionPlan = null;
        $canManageAccesses = false;
        $company = null;

        if ($user !== null && (int) ($user->current_company_id ?? 0) > 0) {
            $companyId = (int) $user->current_company_id;
            $company = Company::query()->find($companyId);
            $organization = $company;
            $subscription = $company
                ? Subscription::query()->where('company_id', $company->id)->latest('id')->first()
                : null;
            $subscriptionPlan = $subscription ? $service->planForCode($subscription->plan_code) : null;

            if ($user instanceof User) {
                if ($company !== null) {
                    $canManageAccesses = $access->canManageCompanyAccess($user, $company);
                }
            }
        }

        $availableModules = [];

        if ($user instanceof User && $company !== null) {
            $availableModules = $access->accessibleModules($user, $company);
        }

        return view('dashboard.industrial', [
            'organization' => $organization,
            'subscription' => $subscription,
            'subscriptionPlan' => $subscriptionPlan,
            'canManageAccesses' => $canManageAccesses,
            'availableModules' => $availableModules,
        ]);
    }
}
