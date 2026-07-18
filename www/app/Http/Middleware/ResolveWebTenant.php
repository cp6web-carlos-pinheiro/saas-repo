<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Shared\Infrastructure\Tenancy\TenantContext;
use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class ResolveWebTenant
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return new RedirectResponse(route('login'));
        }

        $companyId = (int) $user->current_company_id;

        if ($companyId <= 0 || ! $user->companies()->whereKey($companyId)->exists()) {
            return new RedirectResponse(route('onboarding.wizard'));
        }

        app(TenantContext::class)->setCompanyId($companyId);

        return $next($request);
    }
}
