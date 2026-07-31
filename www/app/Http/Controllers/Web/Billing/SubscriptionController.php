<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Billing;

use App\Http\Controllers\Controller;
use App\Models\SaaS\Organization;
use App\Models\SaaS\Subscription;
use App\Services\SaaS\AccountOnboardingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class SubscriptionController extends Controller
{
    public function show(Request $request, AccountOnboardingService $service): View|RedirectResponse
    {
        $user = $request->user();

        if ($user === null) {
            return redirect()->route('login');
        }

        $organization = Organization::query()->where('company_id', (int) $user->current_company_id)->first();

        if ($organization === null) {
            return redirect()->route('onboarding.wizard');
        }

        $subscription = Subscription::query()->where('organization_id', $organization->id)->latest('id')->first();
        $usedFreeTrial = $organization->trials()->exists();

        return view('billing.subscription', [
            'organization' => $organization,
            'subscription' => $subscription,
            'currentPlan' => $subscription ? $service->planForCode($subscription->plan_code) : null,
            'plans' => $service->planCatalog(),
            'usedFreeTrial' => $usedFreeTrial,
        ]);
    }

    public function update(Request $request, AccountOnboardingService $service): RedirectResponse
    {
        $user = $request->user();

        if ($user === null) {
            return redirect()->route('login');
        }

        $validated = $request->validate([
            'plan_code' => ['required', 'in:'.implode(',', array_keys($service->planCatalog()))],
        ]);

        $plan = $service->planForCode($validated['plan_code']);

        if ($plan !== null && ! isset($plan['trial_days'])) {
            $request->session()->put('onboarding.payment_plan', $validated['plan_code']);
            $request->session()->put('payment.context', 'billing');

            return redirect()->route('onboarding.payment.create', ['planCode' => $validated['plan_code']]);
        }

        $service->changePlanSubscription($user, $validated, $request);

        return redirect()->route('billing.subscription.show')->with('status', __('messages.plan_selected_successfully'));
    }
}
