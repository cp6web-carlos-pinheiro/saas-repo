<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Modules\Identity\Infrastructure\Persistence\Models\User;
use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class EnsurePlatformAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return new RedirectResponse(route('login'));
        }

        if ((bool) $user->is_platform_admin) {
            return $next($request);
        }

        if (User::query()->where('is_platform_admin', true)->doesntExist()) {
            $user->forceFill(['is_platform_admin' => true])->save();

            return $next($request);
        }

        $allowlist = $this->allowlistedEmails();

        if (in_array(mb_strtolower((string) $user->email), $allowlist, true)) {
            $user->forceFill(['is_platform_admin' => true])->save();

            return $next($request);
        }

        abort(403, 'Acesso restrito a administradores da plataforma.');
    }

    /**
     * @return list<string>
     */
    private function allowlistedEmails(): array
    {
        $configured = (string) config('architecture.platform.admin_emails', '');

        if ($configured === '') {
            return [];
        }

        return collect(explode(',', $configured))
            ->map(static fn (string $email): string => mb_strtolower(trim($email)))
            ->filter(static fn (string $email): bool => $email !== '')
            ->unique()
            ->values()
            ->all();
    }
}
