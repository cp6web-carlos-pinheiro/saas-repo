<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Onboarding;

use App\Http\Controllers\Controller;
use App\Models\SaaS\OnboardingProfile;
use App\Models\SaaS\Organization;
use App\Models\SaaS\Trial;
use App\Services\SaaS\TrialOnboardingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class OnboardingController extends Controller
{
    public function show(Request $request): View
    {
        $user = $request->user();
        $organization = Organization::query()->where('company_id', $user->current_company_id)->first();
        $onboarding = null;
        $trial = null;

        if ($organization) {
            $onboarding = OnboardingProfile::query()
                ->where('organization_id', $organization->id)
                ->where('user_id', $user->id)
                ->first();

            $trial = Trial::query()
                ->where('organization_id', $organization->id)
                ->where('user_id', $user->id)
                ->latest('id')
                ->first();
        }

        return view('onboarding.wizard', [
            'organization' => $organization,
            'onboarding' => $onboarding,
            'trial' => $trial,
        ]);
    }

    public function store(Request $request, TrialOnboardingService $service): RedirectResponse
    {
        $validated = $request->validate([
            'segment' => ['nullable', 'string', 'max:120'],
            'operation_size' => ['nullable', 'string', 'max:80'],
            'timezone' => ['required', 'timezone'],
            'import_data' => ['nullable', 'boolean'],
            'connect_integrations' => ['nullable', 'boolean'],
            'invite_team' => ['nullable', 'boolean'],
        ]);

        $service->completeOnboarding($request->user(), $validated);

        return redirect()->route('trial.dashboard')->with('status', 'Onboarding concluido.');
    }
}
