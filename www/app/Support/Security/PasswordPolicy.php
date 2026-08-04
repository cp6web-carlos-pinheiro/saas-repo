<?php

declare(strict_types=1);

namespace App\Support\Security;

use Illuminate\Validation\Rules\Password;

final class PasswordPolicy
{
    public static function rule(): Password
    {
        $rule = Password::min(max(8, (int) config('security.password.min_length', 10)));

        if ((bool) config('security.password.require_mixed_case', true)) {
            $rule = $rule->mixedCase();
        }

        if ((bool) config('security.password.require_numbers', true)) {
            $rule = $rule->numbers();
        }

        if ((bool) config('security.password.require_symbols', true)) {
            $rule = $rule->symbols();
        }

        if ((bool) config('security.password.uncompromised', true)) {
            $rule = $rule->uncompromised(3);
        }

        return $rule;
    }
}
