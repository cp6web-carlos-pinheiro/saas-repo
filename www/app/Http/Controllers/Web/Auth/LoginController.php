<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Auth;

use App\Http\Controllers\Controller;
use App\Services\SaaS\AuditLogService;
use App\Services\Security\MfaChallengeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\View\View;

final class LoginController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(Request $request, AuditLogService $audit, MfaChallengeService $mfa): RedirectResponse
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

            Log::channel('auth')->warning('auth.login.failed', [
                'channel' => 'web',
                'email' => mb_strtolower((string) $credentials['email']),
                'ip' => $request->ip(),
            ]);

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

        $user = Auth::guard('web')->user();

        if ($user !== null && $mfa->shouldChallenge($user)) {
            $request->session()->regenerate();
            $request->session()->put('mfa.user_id', (int) $user->id);
            $request->session()->put('mfa.remember', $remember);
            $request->session()->forget('mfa.verified_user_id');

            $mfa->issueChallenge($user, $request);

            Log::channel('auth')->info('auth.mfa.challenge.sent', [
                'channel' => 'web',
                'user_id' => $user->id,
                'ip' => $request->ip(),
            ]);

            $audit->record(
                event: 'auth.mfa.challenge.sent',
                context: ['channel' => 'web'],
                userId: $user->id,
                ipAddress: $request->ip(),
                userAgent: $request->userAgent(),
            );

            return redirect()->route('mfa.challenge')->with('status', __('messages.mfa_code_sent'));
        }

        $request->session()->regenerate();
        if (is_string($user?->preferred_locale)) {
            $request->session()->put('locale', $user->preferred_locale);
        }

        $request->session()->put('mfa.verified_user_id', (int) ($user?->id ?? 0));

        Log::channel('auth')->info('auth.login.success', [
            'channel' => 'web',
            'user_id' => $user?->id,
            'ip' => $request->ip(),
        ]);

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

        Log::channel('auth')->info('auth.logout', [
            'channel' => 'web',
            'user_id' => $user?->id,
            'ip' => $request->ip(),
        ]);

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
