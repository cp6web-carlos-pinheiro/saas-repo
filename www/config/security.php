<?php

declare(strict_types=1);

return [
    'password' => [
        'min_length' => (int) env('SECURITY_PASSWORD_MIN_LENGTH', 10),
        'require_mixed_case' => (bool) env('SECURITY_PASSWORD_MIXED_CASE', true),
        'require_numbers' => (bool) env('SECURITY_PASSWORD_NUMBERS', true),
        'require_symbols' => (bool) env('SECURITY_PASSWORD_SYMBOLS', true),
        'uncompromised' => (bool) env('SECURITY_PASSWORD_UNCOMPROMISED', env('APP_ENV') === 'production'),
    ],

    'mfa' => [
        'enabled' => (bool) env('AUTH_MFA_ENABLED', env('APP_ENV') === 'production'),
        'code_digits' => (int) env('AUTH_MFA_CODE_DIGITS', 6),
        'ttl_minutes' => (int) env('AUTH_MFA_TTL_MINUTES', 10),
    ],

    'telemetry' => [
        'enabled' => (bool) env('TELEMETRY_REQUEST_LOG_ENABLED', true),
    ],
];
