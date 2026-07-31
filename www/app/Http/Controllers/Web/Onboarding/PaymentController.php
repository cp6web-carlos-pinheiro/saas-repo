<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Onboarding;

use App\Http\Controllers\Controller;
use App\Models\SaaS\Organization;
use App\Services\SaaS\AccountOnboardingService;
use App\Services\SaaS\PagarMePaymentService;
use App\Services\SaaS\PaymentFailedException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class PaymentController extends Controller
{
    public function create(Request $request, string $planCode, AccountOnboardingService $onboarding, PagarMePaymentService $payments): View|RedirectResponse
    {
        $plan = $onboarding->planForCode($planCode);

        if ($plan === null || isset($plan['trial_days'])) {
            return redirect()->route('onboarding.wizard');
        }

        if ((string) $request->session()->get('onboarding.payment_plan') !== $planCode) {
            return redirect()->route('onboarding.wizard');
        }

        return view('onboarding.payment', [
            'planCode' => $planCode,
            'plan' => $plan,
            'amount' => $payments->amountForPlan($planCode),
            'pagarMePublicKey' => (string) config('services.pagarme.public_key'),
            'pagarMeTokenUrl' => rtrim((string) config('services.pagarme.base_url'), '/').'/tokens',
            'simulatePayment' => $payments->usesSimulatedGateway(),
        ]);
    }

    public function process(Request $request, AccountOnboardingService $onboarding, PagarMePaymentService $payments): JsonResponse
    {
        $user = $request->user();
        $planCode = (string) $request->session()->get('onboarding.payment_plan');
        $plan = $onboarding->planForCode($planCode);

        if ($user === null || $plan === null || isset($plan['trial_days'])) {
            return response()->json(['redirect' => route('onboarding.wizard')], 422);
        }

        $validated = $request->validate([
            'card_token' => ['required', 'string', 'max:255'],
            'last_four' => ['required', 'string', 'digits:4'],
        ]);

        $organization = Organization::query()->where('company_id', (int) $user->current_company_id)->first();

        if ($organization === null) {
            return response()->json(['message' => __('payment.organization_not_found')], 422);
        }

        try {
            $payment = $payments->charge($user, $organization, $planCode, $validated);
            $subscription = $onboarding->createPlanSubscription($user, ['plan_code' => $planCode], $request);
            $onboarding->recordPaymentProvider($subscription, $payment);

            $paymentContext = (string) $request->session()->get('payment.context', 'onboarding');

            if ($paymentContext === 'onboarding') {
                $request->session()->put('onboarding.step', 4);
            }

            $request->session()->forget('onboarding.payment_plan');
            $request->session()->forget('payment.context');
            $request->session()->put('payment.result', [
                'success' => true,
                'plan_label' => $plan['label'],
                'last_four' => $payment['last_four'],
                'continue_url' => $paymentContext === 'billing'
                    ? route('billing.subscription.show')
                    : route('onboarding.wizard'),
            ]);
        } catch (PaymentFailedException $exception) {
            $request->session()->put('payment.result', [
                'success' => false,
                'reason' => $exception->getMessage(),
                'plan_code' => $planCode,
            ]);
        }

        return response()->json(['redirect' => route('onboarding.payment.result')]);
    }

    public function result(Request $request): View|RedirectResponse
    {
        $result = $request->session()->get('payment.result');

        if (! is_array($result)) {
            return redirect()->route('onboarding.wizard');
        }

        return view('onboarding.payment-result', ['result' => $result]);
    }
}
