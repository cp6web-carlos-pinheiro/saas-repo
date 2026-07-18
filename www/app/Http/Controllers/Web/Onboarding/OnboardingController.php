<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Onboarding;

use App\Http\Controllers\Controller;
use App\Models\SaaS\AccountInvitation;
use App\Models\SaaS\OnboardingProfile;
use App\Models\SaaS\Organization;
use App\Models\SaaS\Subscription;
use App\Services\SaaS\AccountOnboardingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class OnboardingController extends Controller
{
    public function show(Request $request, AccountOnboardingService $service): View|RedirectResponse
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        $organization = $this->organizationFor($user);
        $profile = $organization ? OnboardingProfile::query()->where('organization_id', $organization->id)->where('user_id', $user->id)->first() : null;
        $subscription = $organization ? Subscription::query()->where('organization_id', $organization->id)->latest('id')->first() : null;

        $step = $this->resolveStep($request, $organization, $subscription, $profile);

        if ($step === 5) {
            return redirect()->route('dashboard.industrial');
        }

        return view('onboarding.wizard', [
            'step' => $step,
            'organization' => $organization,
            'profile' => $profile,
            'subscription' => $subscription,
            'plans' => $service->planCatalog(),
            'invitationsSent' => $organization
                ? AccountInvitation::query()->where('organization_id', $organization->id)->latest('id')->get()
                : collect(),
        ]);
    }

    public function store(Request $request, AccountOnboardingService $service): RedirectResponse
    {
        $user = $request->user();
        $response = redirect()->route('dashboard.industrial');

        if (! $user) {
            $response = redirect()->route('login');
        } else {
            $organization = $this->organizationFor($user);
            $profile = $organization ? OnboardingProfile::query()->where('organization_id', $organization->id)->where('user_id', $user->id)->first() : null;
            $subscription = $organization ? Subscription::query()->where('organization_id', $organization->id)->latest('id')->first() : null;
            $step = $this->resolveStep($request, $organization, $subscription, $profile);

            if ($step === 2) {
                $response = $this->storeCompanyStep($request, $service, $user);
            } elseif ($step === 3) {
                $response = $this->storePlanStep($request, $service, $user);
            } elseif ($step === 4) {
                $response = $this->storeInviteStep($request, $service, $user);
            }
        }

        return $response;
    }

    private function storeCompanyStep(Request $request, AccountOnboardingService $service, $user): RedirectResponse
    {
        $validated = $request->validate([
            'company_name' => ['required', 'string', 'max:180'],
            'company_domain' => ['nullable', 'string', 'max:180'],
            'segment' => ['nullable', 'string', 'max:120'],
            'operation_size' => ['nullable', 'string', 'max:80'],
            'timezone' => ['required', 'timezone'],
        ]);

        $service->createCompany($user, $validated, $request);
        $request->session()->put('onboarding.step', 3);

        return redirect()->route('onboarding.wizard')->with('status', __('messages.company_data_saved'));
    }

    private function storePlanStep(Request $request, AccountOnboardingService $service, $user): RedirectResponse
    {
        $validated = $request->validate([
            'plan_code' => ['required', 'in:'.implode(',', array_keys($service->planCatalog()))],
        ]);

        $service->createPlanSubscription($user, $validated, $request);
        $request->session()->put('onboarding.step', 4);

        return redirect()->route('onboarding.wizard')->with('status', __('messages.plan_selected_successfully'));
    }

    private function storeInviteStep(Request $request, AccountOnboardingService $service, $user): RedirectResponse
    {
        $validated = $request->validate([
            'emails' => ['nullable', 'string', 'max:2000'],
        ]);

        $emails = collect(preg_split('/[\r\n,;]+/', (string) $validated['emails']) ?: [])
            ->map(static fn ($email) => trim((string) $email))
            ->filter()
            ->values()
            ->all();

        if ($emails !== []) {
            $service->sendInvitations($user, $emails, $request);
        }

        $organization = $this->organizationFor($user);

        if ($organization !== null) {
            OnboardingProfile::query()->updateOrCreate(
                ['organization_id' => $organization->id, 'user_id' => $user->id],
                [
                    'timezone' => $organization->timezone,
                    'progress' => 100,
                    'completed_at' => now(),
                ]
            );
        }

        $request->session()->forget('onboarding.step');

        return redirect()->route('dashboard.industrial')->with('status', __('messages.onboarding_invites_sent_and_account_ready'));
    }

    private function organizationFor($user): ?Organization
    {
        $companyId = (int) ($user->current_company_id ?? 0);

        if ($companyId <= 0) {
            return null;
        }

        return Organization::query()->where('company_id', $companyId)->first();
    }

    private function resolveStep(Request $request, ?Organization $organization, ?Subscription $subscription, ?OnboardingProfile $profile): int
    {
        $sessionStep = (int) $request->session()->get('onboarding.step', 0);

        return match (true) {
            $profile?->completed_at !== null => 5,
            $organization === null => max(2, $sessionStep),
            $subscription === null => max(3, $sessionStep),
            default => max(4, $sessionStep ?: 4),
        };
    }
}
