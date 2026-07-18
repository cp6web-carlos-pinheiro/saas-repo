<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Auth;

use App\Http\Controllers\Controller;
use App\Mail\TrialVerificationMail;
use App\Modules\Identity\Infrastructure\Persistence\Models\User;
use App\Services\SaaS\AccountOnboardingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

final class RegisterController extends Controller
{
    public function create(): View
    {
        return view('auth.register');
    }

    public function store(Request $request, AccountOnboardingService $service): RedirectResponse
    {
        $limiterKey = 'trial-register:'.$request->ip();

        if (RateLimiter::tooManyAttempts($limiterKey, 12)) {
            return back()->withInput()->withErrors([
                'email' => __('messages.too_many_attempts'),
            ]);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email:rfc,dns', 'max:190'],
            'password' => ['required', 'confirmed', Password::min(10)->mixedCase()->numbers()->symbols()],
            'preferred_locale' => ['required', 'string', 'in:pt_BR,en,es'],
            'terms' => ['accepted'],
        ], [
            'terms.accepted' => __('messages.terms_required'),
        ]);

        RateLimiter::hit($limiterKey, 60);

        try {
            $result = $service->createMasterUser($validated, $request);
        } catch (ValidationException $exception) {
            return back()
                ->withInput()
                ->withErrors($exception->errors());
        }

        /** @var User $user */
        $user = $result['user'];

        $verifyUrl = route('verification.verify-token', [
            'token' => $result['emailVerificationToken'],
        ]);

        Mail::to($user->email)->queue(new TrialVerificationMail($user, $verifyUrl));

        Auth::guard('web')->login($user, true);
        $request->session()->regenerate();
        $request->session()->put('locale', $user->preferred_locale);
        $request->session()->put('onboarding.step', 2);
        $request->session()->put('onboarding.user_id', $user->id);

        return redirect()->route('onboarding.wizard')
            ->with('status', __('messages.user_created_verification_sent'));
    }
}
