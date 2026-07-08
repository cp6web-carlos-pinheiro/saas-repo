<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Auth;

use App\Http\Controllers\Controller;
use App\Mail\TrialVerificationMail;
use App\Models\SaaS\EmailVerification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\View\View;

final class EmailVerificationController extends Controller
{
    public function notice(): View
    {
        return view('auth.verify-email');
    }

    public function verifyToken(string $token): RedirectResponse
    {
        $hashed = hash('sha256', $token);

        $verification = EmailVerification::query()
            ->where('token', $hashed)
            ->whereNull('verified_at')
            ->first();

        if (! $verification || $verification->expires_at->isPast()) {
            return redirect()->route('verification.notice')->withErrors([
                'email' => 'Link de verificacao invalido ou expirado.',
            ]);
        }

        $user = $verification->user;
        $user->forceFill(['email_verified_at' => now()])->save();

        $verification->update(['verified_at' => now()]);

        if (! Auth::check()) {
            Auth::guard('web')->login($user, true);
            request()->session()->regenerate();
        }

        return redirect()->route('onboarding.wizard')->with('status', 'Email confirmado com sucesso.');
    }

    public function resend(Request $request): RedirectResponse
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        if ($user->email_verified_at !== null) {
            return redirect()->route('onboarding.wizard');
        }

        $plainToken = Str::random(64);

        EmailVerification::query()->create([
            'user_id' => $user->id,
            'token' => hash('sha256', $plainToken),
            'expires_at' => now()->addDay(),
            'requested_ip' => $request->ip(),
        ]);

        Mail::to($user->email)->queue(new TrialVerificationMail(
            $user,
            route('verification.verify-token', ['token' => $plainToken]),
        ));

        return back()->with('status', 'Novo e-mail de confirmacao enviado.');
    }
}
