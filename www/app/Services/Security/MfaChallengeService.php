<?php

declare(strict_types=1);

namespace App\Services\Security;

use App\Mail\MfaCodeMail;
use App\Modules\Identity\Infrastructure\Persistence\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

final class MfaChallengeService
{
    public function shouldChallenge(User $user): bool
    {
        if (! (bool) config('security.mfa.enabled', false)) {
            return false;
        }

        return filter_var($user->email, FILTER_VALIDATE_EMAIL) !== false;
    }

    public function issueChallenge(User $user, Request $request): void
    {
        $digits = max(4, (int) config('security.mfa.code_digits', 6));
        $ttlMinutes = max(1, (int) config('security.mfa.ttl_minutes', 10));

        $max = (10 ** $digits) - 1;
        $code = str_pad((string) random_int(0, $max), $digits, '0', STR_PAD_LEFT);

        $request->session()->put('mfa.code_hash', hash('sha256', $code));
        $request->session()->put('mfa.expires_at', now()->addMinutes($ttlMinutes)->getTimestamp());

        Mail::to($user->email)->send(new MfaCodeMail($user, $code, $ttlMinutes));
    }

    public function verify(Request $request, string $inputCode): bool
    {
        $expectedHash = (string) $request->session()->get('mfa.code_hash', '');
        $expiresAt = (int) $request->session()->get('mfa.expires_at', 0);

        if ($expectedHash === '' || $expiresAt === 0 || now()->getTimestamp() > $expiresAt) {
            return false;
        }

        return hash_equals($expectedHash, hash('sha256', $inputCode));
    }

    public function clear(Request $request): void
    {
        $request->session()->forget([
            'mfa.user_id',
            'mfa.remember',
            'mfa.intended',
            'mfa.code_hash',
            'mfa.expires_at',
        ]);
    }
}
