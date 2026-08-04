<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Modules\Identity\Infrastructure\Persistence\Models\User;
use App\Services\Security\MfaChallengeService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class EnsureMfaIsVerified
{
    public function __construct(private readonly MfaChallengeService $mfa) {}

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user('web');

        if (! $user instanceof User) {
            return $next($request);
        }

        if (! $this->mfa->shouldChallenge($user)) {
            return $next($request);
        }

        if ($this->isAllowedRoute($request)) {
            return $next($request);
        }

        $verifiedUserId = (int) $request->session()->get('mfa.verified_user_id', 0);

        if ($verifiedUserId !== (int) $user->id) {
            $request->session()->put('mfa.user_id', (int) $user->id);

            return redirect()->route('mfa.challenge');
        }

        return $next($request);
    }

    private function isAllowedRoute(Request $request): bool
    {
        return $request->routeIs('login')
            || $request->routeIs('login.store')
            || $request->routeIs('logout')
            || $request->routeIs('mfa.*');
    }
}
