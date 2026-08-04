<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Auth;

use App\Http\Controllers\Controller;
use App\Modules\Identity\Infrastructure\Persistence\Models\User;
use App\Services\SaaS\AuditLogService;
use App\Services\Security\MfaChallengeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\View\View;

final class MfaChallengeController extends Controller
{
    public function create(Request $request): View|RedirectResponse
    {
        $user = $request->user('web');

        if (! $user instanceof User) {
            return redirect()->route('login');
        }

        $sessionUserId = (int) $request->session()->get('mfa.user_id', 0);

        if ($sessionUserId !== (int) $user->id) {
            $redirectTo = (int) $user->current_company_id > 0
                ? route('dashboard.industrial')
                : route('onboarding.wizard');

            return redirect()->to($redirectTo);
        }

        return view('auth.mfa-challenge', [
            'email' => $this->maskEmail((string) $user->email),
        ]);
    }

    public function store(Request $request, MfaChallengeService $mfa, AuditLogService $audit): RedirectResponse
    {
        $user = $request->user('web');

        if (! $user instanceof User) {
            return redirect()->route('login');
        }

        $sessionUserId = (int) $request->session()->get('mfa.user_id', 0);

        if ($sessionUserId !== (int) $user->id) {
            return redirect()->route('login');
        }

        $validated = $request->validate([
            'code' => ['required', 'string', 'regex:/^[0-9]{4,8}$/'],
        ]);

        $key = 'mfa:'.(int) $user->id.'|'.$request->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            return back()->withErrors([
                'code' => __('messages.mfa_too_many_attempts'),
            ]);
        }

        if (! $mfa->verify($request, (string) $validated['code'])) {
            RateLimiter::hit($key, 120);

            $audit->record(
                event: 'auth.mfa.failed',
                severity: 'warning',
                context: ['channel' => 'web'],
                userId: $user->id,
                ipAddress: $request->ip(),
                userAgent: $request->userAgent(),
            );

            return back()->withErrors([
                'code' => __('messages.mfa_invalid_code'),
            ]);
        }

        RateLimiter::clear($key);

        $mfa->clear($request);
        $request->session()->put('mfa.verified_user_id', (int) $user->id);

        $audit->record(
            event: 'auth.mfa.verified',
            context: ['channel' => 'web'],
            userId: $user->id,
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        $audit->record(
            event: 'auth.login.success',
            context: ['channel' => 'web', 'mfa' => true],
            userId: $user->id,
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        $redirectTo = (int) $user->current_company_id > 0
            ? route('dashboard.industrial')
            : route('onboarding.wizard');

        return redirect()->intended($redirectTo);
    }

    public function resend(Request $request, MfaChallengeService $mfa, AuditLogService $audit): RedirectResponse
    {
        $user = $request->user('web');

        if (! $user instanceof User) {
            return redirect()->route('login');
        }

        $sessionUserId = (int) $request->session()->get('mfa.user_id', 0);

        if ($sessionUserId !== (int) $user->id) {
            return redirect()->route('login');
        }

        $mfa->issueChallenge($user, $request);

        $audit->record(
            event: 'auth.mfa.challenge.resent',
            context: ['channel' => 'web'],
            userId: $user->id,
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return back()->with('status', __('messages.mfa_code_resent'));
    }

    private function maskEmail(string $email): string
    {
        $parts = explode('@', $email, 2);

        if (count($parts) !== 2) {
            return $email;
        }

        $name = $parts[0];
        $domain = $parts[1];

        if (mb_strlen($name) <= 2) {
            return mb_substr($name, 0, 1).'***@'.$domain;
        }

        return mb_substr($name, 0, 2).'***@'.$domain;
    }
}
