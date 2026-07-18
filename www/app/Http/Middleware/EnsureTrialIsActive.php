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
        $redirect = null;

        if (! $user) {
            $redirect = redirect()->route('login');
        } else {
            $organization = Organization::query()->where('company_id', $user->current_company_id)->first();

            if (! $organization) {
                $redirect = redirect()->route('onboarding.wizard');
            } else {
                $trial = Trial::query()
                    ->where('organization_id', $organization->id)
                    ->latest('id')
                    ->first();

                if (! $trial) {
                    $redirect = redirect()->route('onboarding.wizard');
                } elseif ($trial->trial_end_date->isPast()) {
                    $trial->update([
                        'status' => 'expired',
                        'is_expired' => true,
                        'expired_at' => now(),
                    ]);

                    $redirect = new RedirectResponse(route('onboarding.wizard'));
                }
            }
        }

        if ($redirect !== null) {
            return $redirect;
        }

        return $next($request);
    }
}
