<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\SaaS\Organization;
use App\Models\SaaS\Subscription;
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
                $subscription = Subscription::query()
                    ->where('organization_id', $organization->id)
                    ->latest('id')
                    ->first();
                $trial = Trial::query()
                    ->where('organization_id', $organization->id)
                    ->latest('id')
                    ->first();

                $hasActivePaidSubscription = $subscription !== null
                    && $subscription->status === 'active'
                    && ($subscription->ends_at === null || $subscription->ends_at->isFuture());

                if ($hasActivePaidSubscription) {
                    return $next($request);
                }

                if (! $trial) {
                    $redirect = redirect()->route('onboarding.wizard');
                } elseif ($trial->trial_end_date->isPast()) {
                    $trial->update([
                        'status' => 'expired',
                        'is_expired' => true,
                        'expired_at' => now(),
                    ]);

                    $redirect = new RedirectResponse(route('billing.subscription.show'));
                }
            }
        }

        if ($redirect !== null) {
            return $redirect;
        }

        return $next($request);
    }
}
