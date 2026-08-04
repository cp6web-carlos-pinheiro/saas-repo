<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Onboarding;

use App\Http\Controllers\Controller;
use App\Models\SaaS\AccountInvitation;
use App\Services\SaaS\AccountOnboardingService;
use App\Support\Security\PasswordPolicy;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class AccountInvitationController extends Controller
{
    public function show(string $token, Request $request): View|RedirectResponse
    {
        $invitation = $this->resolveInvitation($token);

        if ($invitation === null) {
            return redirect()->route('login')->withErrors([
                'token' => __('messages.invalid_or_expired_invitation'),
            ]);
        }

        if ($request->user() !== null && mb_strtolower((string) $request->user()->email) === mb_strtolower((string) $invitation->email)) {
            return $this->accept($request, $token, app(AccountOnboardingService::class));
        }

        return view('onboarding.invite-accept', [
            'invitation' => $invitation,
        ]);
    }

    public function accept(Request $request, string $token, AccountOnboardingService $service): RedirectResponse
    {
        $invitation = $this->resolveInvitation($token);

        if ($invitation === null) {
            return redirect()->route('login')->withErrors([
                'token' => __('messages.invalid_or_expired_invitation'),
            ]);
        }

        $user = $request->user();
        $response = null;

        if ($user !== null) {
            if (mb_strtolower((string) $user->email) !== mb_strtolower((string) $invitation->email)) {
                return back()->withErrors([
                    'email' => __('messages.login_with_invited_email'),
                ]);
            }

            $service->acceptInvitation($invitation, ['name' => $user->name, 'password' => ''], $request);

            $response = redirect()->route('dashboard.industrial')->with('status', __('messages.invitation_accepted_successfully'));
        } else {
            $validated = $request->validate([
                'name' => ['required', 'string', 'max:150'],
                'password' => ['required', 'confirmed', PasswordPolicy::rule()],
            ]);

            $acceptedUser = $service->acceptInvitation($invitation, $validated, $request);

            auth()->guard('web')->login($acceptedUser, true);
            $request->session()->regenerate();
            if (is_string($acceptedUser->preferred_locale)) {
                $request->session()->put('locale', $acceptedUser->preferred_locale);
            }

            $response = redirect()->route('dashboard.industrial')->with('status', __('messages.account_created_and_invitation_accepted'));
        }

        return $response;
    }

    private function resolveInvitation(string $token): ?AccountInvitation
    {
        return AccountInvitation::query()
            ->where('token', hash('sha256', $token))
            ->whereNull('revoked_at')
            ->whereNull('accepted_at')
            ->first();
    }
}
