<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Auth;

use App\Http\Controllers\Controller;
use App\Services\SaaS\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

final class LoginController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(Request $request, AuditLogService $audit): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'remember' => ['nullable', 'boolean'],
        ]);

        $throttleKey = 'web-login:'.mb_strtolower((string) $credentials['email']).'|'.$request->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 8)) {
            throw ValidationException::withMessages([
                'email' => 'Muitas tentativas de login. Tente novamente em alguns minutos.',
            ]);
        }

        $remember = (bool) ($credentials['remember'] ?? false);

        if (! Auth::guard('web')->attempt([
            'email' => $credentials['email'],
            'password' => $credentials['password'],
            'is_active' => true,
        ], $remember)) {
            RateLimiter::hit($throttleKey, 120);

            $audit->record(
                event: 'auth.login.failed',
                severity: 'warning',
                context: ['email' => mb_strtolower((string) $credentials['email'])],
                ipAddress: $request->ip(),
                userAgent: $request->userAgent(),
            );

            throw ValidationException::withMessages([
                'email' => 'Credenciais invalidas.',
            ]);
        }

        RateLimiter::clear($throttleKey);

        $request->session()->regenerate();

        $user = Auth::user();
        $audit->record(
            event: 'auth.login.success',
            context: ['channel' => 'web'],
            userId: $user?->id,
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return redirect()->intended(route('onboarding.wizard'));
    }

    public function destroy(Request $request, AuditLogService $audit): RedirectResponse
    {
        $user = Auth::user();

        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        $audit->record(
            event: 'auth.logout',
            context: ['channel' => 'web'],
            userId: $user?->id,
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return redirect()->route('login');
    }
}
