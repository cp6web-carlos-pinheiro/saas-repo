<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Auth;

use App\Http\Controllers\Controller;
use App\Mail\TrialVerificationMail;
use App\Modules\Identity\Infrastructure\Persistence\Models\User;
use App\Services\SaaS\TrialOnboardingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

final class RegisterController extends Controller
{
    public function create(): View
    {
        return view('auth.register');
    }

    public function store(Request $request, TrialOnboardingService $service): RedirectResponse
    {
        $limiterKey = 'trial-register:'.$request->ip();

        if (RateLimiter::tooManyAttempts($limiterKey, 12)) {
            return back()->withInput()->withErrors([
                'email' => 'Muitas tentativas. Aguarde alguns minutos e tente novamente.',
            ]);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'company' => ['required', 'string', 'max:180'],
            'email' => ['required', 'email:rfc,dns', 'max:190'],
            'password' => ['required', 'confirmed', Password::min(10)->mixedCase()->numbers()->symbols()],
            'terms' => ['accepted'],
        ], [
            'terms.accepted' => 'Voce precisa aceitar os termos para continuar.',
        ]);

        RateLimiter::hit($limiterKey, 60);

        $result = $service->register($validated, $request);
        /** @var User $user */
        $user = $result['user'];

        $verifyUrl = route('verification.verify-token', [
            'token' => $result['emailVerificationToken'],
        ]);

        Mail::to($user->email)->queue(new TrialVerificationMail($user, $verifyUrl));

        Auth::guard('web')->login($user, true);
        $request->session()->regenerate();

        return redirect()->route('onboarding.wizard')
            ->with('status', 'Conta criada com sucesso. Enviamos o e-mail de confirmacao.');
    }
}
