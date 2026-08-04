<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Auth;

use App\Http\Controllers\Controller;
use App\Services\SaaS\AuditLogService;
use App\Support\Security\PasswordPolicy;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;

final class PasswordRecoveryController extends Controller
{
    public function forgot(): View
    {
        return view('auth.forgot-password');
    }

    public function sendResetLink(Request $request, AuditLogService $audit): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $email = (string) $request->input('email');

        Password::broker('users')->sendResetLink([
            'email' => (string) $request->input('email'),
        ]);

        Log::channel('auth')->info('auth.password.reset_link_requested', [
            'email' => mb_strtolower($email),
            'ip' => $request->ip(),
        ]);

        $audit->record(
            event: 'auth.password.reset_link_requested',
            context: ['email' => mb_strtolower($email)],
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return back()->with('status', __('messages.password_reset_link_sent'));
    }

    public function resetForm(Request $request): View
    {
        return view('auth.reset-password', [
            'token' => (string) $request->route('token'),
            'email' => (string) $request->query('email', ''),
        ]);
    }

    public function reset(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', PasswordPolicy::rule()],
        ]);

        $status = Password::broker('users')->reset(
            $validated,
            static function ($user, $password): void {
                $user->forceFill([
                    'password' => $password,
                ])->save();
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            Log::channel('auth')->warning('auth.password.reset_failed', [
                'email' => mb_strtolower((string) $validated['email']),
                'ip' => $request->ip(),
            ]);

            return back()->withErrors(['email' => __('messages.reset_token_invalid_or_expired')]);
        }

        Log::channel('auth')->info('auth.password.reset_success', [
            'email' => mb_strtolower((string) $validated['email']),
            'ip' => $request->ip(),
        ]);

        return redirect()->route('login')->with('status', __('messages.password_reset_success'));
    }
}
