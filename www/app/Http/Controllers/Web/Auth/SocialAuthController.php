<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Auth;

use App\Http\Controllers\Controller;
use App\Models\SaaS\SocialAccount;
use App\Modules\Identity\Infrastructure\Persistence\Models\User;
use App\Services\SaaS\AccountOnboardingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

final class SocialAuthController extends Controller
{
    public function redirect(string $provider): RedirectResponse
    {
        abort_unless(in_array($provider, ['google', 'microsoft'], true), 404);

        return Socialite::driver($provider)->redirect();
    }

    public function callback(string $provider, Request $request, AccountOnboardingService $service): RedirectResponse
    {
        abort_unless(in_array($provider, ['google', 'microsoft'], true), 404);

        $oauthUser = Socialite::driver($provider)->user();

        $linked = SocialAccount::query()
            ->where('provider', $provider)
            ->where('provider_user_id', (string) $oauthUser->getId())
            ->first();

        if ($linked) {
            Auth::guard('web')->loginUsingId($linked->user_id, true);
            $request->session()->regenerate();
            $linkedUser = Auth::user();
            if (is_string($linkedUser?->preferred_locale)) {
                $request->session()->put('locale', $linkedUser->preferred_locale);
            }

            return redirect()->route('onboarding.wizard');
        }

        $email = (string) $oauthUser->getEmail();

        if ($email === '') {
            return redirect()->route('login')->withErrors([
                'email' => __('messages.social_provider_no_email'),
            ]);
        }

        $user = User::query()->where('email', $email)->first();

        if (! $user) {
            $name = (string) ($oauthUser->getName() ?: 'Novo Usuario');
            $result = $service->createSocialUser($name, $email, $request);

            $user = $result['user'];
            $user->forceFill(['email_verified_at' => now()])->save();

            $request->session()->put('onboarding.step', 2);
            $request->session()->put('onboarding.user_id', $user->id);
        }

        SocialAccount::query()->firstOrCreate([
            'provider' => $provider,
            'provider_user_id' => (string) $oauthUser->getId(),
        ], [
            'user_id' => $user->id,
            'email' => $email,
            'meta' => [
                'nickname' => $oauthUser->getNickname(),
                'name' => $oauthUser->getName(),
                'avatar' => $oauthUser->getAvatar(),
            ],
        ]);

        Auth::guard('web')->login($user, true);
        $request->session()->regenerate();
        if (is_string($user->preferred_locale)) {
            $request->session()->put('locale', $user->preferred_locale);
        }

        return redirect()->route('onboarding.wizard');
    }
}
