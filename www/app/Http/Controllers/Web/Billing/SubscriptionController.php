<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Billing;

use App\Http\Controllers\Controller;
use App\Models\SaaS\Subscription;
use App\Modules\Tenant\Infrastructure\Persistence\Models\Company;
use App\Services\SaaS\AccountOnboardingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

final class SubscriptionController extends Controller
{
    public function show(Request $request, AccountOnboardingService $service): View|RedirectResponse
    {
        $user = $request->user();

        if ($user === null) {
            return redirect()->route('login');
        }

        $company = Company::query()->find((int) $user->current_company_id);

        if ($company === null) {
            return redirect()->route('onboarding.wizard');
        }

        $subscription = Subscription::query()->where('company_id', $company->id)->latest('id')->first();
        $usedFreeTrial = $company->trials()->exists();

        return view('billing.subscription', [
            'organization' => $company,
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
            $response = redirect()->route('login');
        } else {
            $validated = $request->validate([
                'plan_code' => ['required', Rule::exists('plans', 'code')->where('is_active', true)],
            ]);

            $plan = $service->planForCode($validated['plan_code']);

            if ($plan === null || ($plan['is_active'] ?? false) !== true) {
                $response = redirect()->route('billing.subscription.show')->withErrors([
                    'plan_code' => __('messages.invalid_plan'),
                ]);
            } elseif (! isset($plan['trial_days'])) {
                $request->session()->put('onboarding.payment_plan', $validated['plan_code']);
                $request->session()->put('payment.context', 'billing');

                $response = redirect()->route('onboarding.payment.create', ['planCode' => $validated['plan_code']]);
            } else {
                $service->changePlanSubscription($user, $validated, $request);

                $response = redirect()->route('billing.subscription.show')->with('status', __('messages.plan_selected_successfully'));
            }
        }

        return $response;
    }
}
