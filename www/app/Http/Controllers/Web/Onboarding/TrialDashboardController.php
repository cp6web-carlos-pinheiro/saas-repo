<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Onboarding;

use App\Http\Controllers\Controller;
use App\Models\SaaS\OnboardingProfile;
use App\Models\SaaS\Organization;
use App\Models\SaaS\Trial;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class TrialDashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();
        $organization = Organization::query()->where('company_id', $user->current_company_id)->firstOrFail();

        $trial = Trial::query()
            ->where('organization_id', $organization->id)
            ->latest('id')
            ->firstOrFail();

        $onboarding = OnboardingProfile::query()
            ->where('organization_id', $organization->id)
            ->where('user_id', $user->id)
            ->first();

        return view('dashboard.trial', [
            'organization' => $organization,
            'trial' => $trial,
            'onboarding' => $onboarding,
            'daysRemaining' => $trial->daysRemaining(),
        ]);
    }
}
