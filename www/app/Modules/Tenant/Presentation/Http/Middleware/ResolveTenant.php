<?php

declare(strict_types=1);

namespace App\Modules\Tenant\Presentation\Http\Middleware;

use App\Shared\Infrastructure\Tenancy\TenantContext;
use App\Shared\Presentation\Http\Responses\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class ResolveTenant
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return ApiResponse::error('Unauthenticated', 401);
        }

        $headerCompanyId = $request->header('X-Company-Id');
        $companyId = $headerCompanyId !== null ? (int) $headerCompanyId : (int) $user->current_company_id;

        if ($companyId <= 0 || ! $user->companies()->whereKey($companyId)->exists()) {
            return ApiResponse::error('Tenant not allowed for current user', 403);
        }

        app(TenantContext::class)->setCompanyId($companyId);

        return $next($request);
    }
}
