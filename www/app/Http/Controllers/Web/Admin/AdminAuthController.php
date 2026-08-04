<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Services\SaaS\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\View\View;

final class AdminAuthController extends Controller
{
    public function create(): View
    {
        return view('admin.login');
    }

    public function store(Request $request, AuditLogService $audit): RedirectResponse
    {
        $credentials = $request->validate(['email' => ['required', 'email'], 'password' => ['required', 'string']]);
        $throttleKey = 'admin-login:'.mb_strtolower((string) $credentials['email']).'|'.$request->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 8)) {
            return back()->onlyInput('email')->withErrors([
                'email' => __('messages.login_too_many_attempts'),
            ]);
        }

        if (! Auth::guard('admin')->attempt([...$credentials, 'is_active' => true], (bool) $request->boolean('remember'))) {
            RateLimiter::hit($throttleKey, 120);
            Log::channel('auth')->warning('auth.login.failed', [
                'channel' => 'admin',
                'email' => mb_strtolower((string) $credentials['email']),
                'ip' => $request->ip(),
            ]);

            $audit->record(
                event: 'auth.login.failed',
                severity: 'warning',
                context: [
                    'channel' => 'admin',
                    'admin_id' => null,
                    'email' => mb_strtolower((string) $credentials['email']),
                ],
                ipAddress: $request->ip(),
                userAgent: $request->userAgent(),
            );

            return back()->withErrors(['email' => __('global_admin.invalid_credentials')])->onlyInput('email');
        }

        RateLimiter::clear($throttleKey);

        $request->session()->regenerate();

        $admin = Auth::guard('admin')->user();

        Log::channel('auth')->info('auth.login.success', [
            'channel' => 'admin',
            'user_id' => $admin?->id,
            'ip' => $request->ip(),
        ]);

        $audit->record(
            event: 'auth.login.success',
            context: ['channel' => 'admin', 'admin_id' => $admin?->id],
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return redirect()->intended(route('global-admin.home'));
    }

    public function destroy(Request $request, AuditLogService $audit): RedirectResponse
    {
        $admin = Auth::guard('admin')->user();

        Log::channel('auth')->info('auth.logout', [
            'channel' => 'admin',
            'user_id' => $admin?->id,
            'ip' => $request->ip(),
        ]);

        $audit->record(
            event: 'auth.logout',
            context: ['channel' => 'admin', 'admin_id' => $admin?->id],
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        Auth::guard('admin')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('global-admin.login');
    }
}
