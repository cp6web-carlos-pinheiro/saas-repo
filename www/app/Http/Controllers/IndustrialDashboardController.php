<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\SaaS\Organization;
use App\Models\SaaS\Subscription;
use App\Services\SaaS\AccountOnboardingService;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class IndustrialDashboardController extends Controller
{
    public function __invoke(Request $request, AccountOnboardingService $service): View
    {
        $user = $request->user();
        $organization = null;
        $subscription = null;
        $subscriptionPlan = null;

        if ($user !== null && (int) ($user->current_company_id ?? 0) > 0) {
            $organization = Organization::query()->where('company_id', (int) $user->current_company_id)->first();
            $subscription = $organization
                ? Subscription::query()->where('organization_id', $organization->id)->latest('id')->first()
                : null;
            $subscriptionPlan = $subscription ? $service->planForCode($subscription->plan_code) : null;
        }

        return view('dashboard.industrial', [
            'organization' => $organization,
            'subscription' => $subscription,
            'subscriptionPlan' => $subscriptionPlan,
        ]);
    }
}
