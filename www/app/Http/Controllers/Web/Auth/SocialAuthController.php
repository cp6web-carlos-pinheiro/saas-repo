<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Auth;

use App\Http\Controllers\Controller;
use App\Models\SaaS\SocialAccount;
use App\Modules\Identity\Infrastructure\Persistence\Models\User;
use App\Services\SaaS\TrialOnboardingService;
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

    public function callback(string $provider, Request $request, TrialOnboardingService $service): RedirectResponse
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

            return redirect()->route('onboarding.wizard');
        }

        $email = (string) $oauthUser->getEmail();

        if ($email === '') {
            return redirect()->route('login')->withErrors([
                'email' => 'Seu provedor social nao retornou email verificavel.',
            ]);
        }

        $user = User::query()->where('email', $email)->first();

        if (! $user) {
            $name = (string) ($oauthUser->getName() ?: 'Novo Usuario');
            $companyHint = Str::title(Str::before($email, '@')).' Company';

            $result = $service->registerFromSocial($name, $companyHint, $email, $request);

            $user = $result['user'];
            $user->forceFill(['email_verified_at' => now()])->save();
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

        return redirect()->route('onboarding.wizard');
    }
}
