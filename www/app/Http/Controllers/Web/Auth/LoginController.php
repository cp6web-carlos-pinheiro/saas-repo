<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Auth;

use App\Http\Controllers\Controller;
use App\Services\SaaS\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
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
            return back()->withInput($request->only('email', 'remember'))->withErrors([
                'email' => __('messages.login_too_many_attempts'),
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

            return back()->withInput($request->only('email', 'remember'))->withErrors([
                'email' => __('messages.invalid_credentials'),
            ]);
        }

        RateLimiter::clear($throttleKey);

        $user = Auth::user();
        $request->session()->regenerate();
        if (is_string($user?->preferred_locale)) {
            $request->session()->put('locale', $user->preferred_locale);
        }

        $audit->record(
            event: 'auth.login.success',
            context: ['channel' => 'web'],
            userId: $user?->id,
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        $redirectTo = $user && (int) $user->current_company_id > 0
            ? route('dashboard.industrial')
            : route('onboarding.wizard');

        return redirect()->intended($redirectTo);
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
