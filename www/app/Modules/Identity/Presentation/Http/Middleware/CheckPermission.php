<?php

declare(strict_types=1);

namespace App\Modules\Identity\Presentation\Http\Middleware;

use App\Shared\Presentation\Http\Responses\ApiResponse;
use App\Shared\Infrastructure\Tenancy\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class CheckPermission
{
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $user = $request->user();
        $companyId = app(TenantContext::class)->companyId();

        if (! $user || ! $companyId || ! $user->hasPermission($permission, $companyId)) {
            return ApiResponse::error('Forbidden', 403, [
                'permission' => [$permission],
            ]);
        }

        return $next($request);
    }
}
