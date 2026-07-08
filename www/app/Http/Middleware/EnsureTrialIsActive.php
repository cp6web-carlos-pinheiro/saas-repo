<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\SaaS\Organization;
use App\Models\SaaS\Trial;
use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class EnsureTrialIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        $organization = Organization::query()->where('company_id', $user->current_company_id)->first();

        if (! $organization) {
            return redirect()->route('start-trial');
        }

        $trial = Trial::query()
            ->where('organization_id', $organization->id)
            ->where('user_id', $user->id)
            ->latest('id')
            ->first();

        if (! $trial) {
            return redirect()->route('start-trial');
        }

        if ($trial->trial_end_date->isPast()) {
            $trial->update([
                'status' => 'expired',
                'is_expired' => true,
                'expired_at' => now(),
            ]);

            return new RedirectResponse(route('onboarding.wizard'));
        }

        return $next($request);
    }
}
