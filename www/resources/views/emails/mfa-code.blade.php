@php($appName = config('app.name', 'Beyond MRP'))
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('messages.mfa_email_subject') }}</title>
</head>
<body style="font-family: Arial, sans-serif; color: #111827; line-height: 1.5;">
    <p>{{ __('messages.mfa_email_greeting', ['name' => $user->name]) }}</p>
    <p>{{ __('messages.mfa_email_intro', ['app' => $appName]) }}</p>
    <p style="font-size: 26px; letter-spacing: 4px; font-weight: 700; margin: 20px 0;">{{ $code }}</p>
    <p>{{ __('messages.mfa_email_expire', ['minutes' => $ttlMinutes]) }}</p>
    <p>{{ __('messages.mfa_email_ignore') }}</p>
</body>
</html>
