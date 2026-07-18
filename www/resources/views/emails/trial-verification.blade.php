<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>{{ __('emails.verify_title') }}</title>
</head>
<body style="margin:0;padding:0;background:#f8fafc;font-family:Roboto,Arial,sans-serif;color:#0f172a;">
  <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="padding:24px;">
    <tr>
      <td align="center">
        <table role="presentation" width="620" cellpadding="0" cellspacing="0" style="max-width:620px;background:#ffffff;border-radius:18px;overflow:hidden;border:1px solid #e2e8f0;">
          <tr>
            <td style="padding:26px;background:linear-gradient(130deg,#0f172a 0%,#1a73e8 100%);color:#ffffff;">
              <p style="margin:0;font-size:12px;letter-spacing:0.16em;text-transform:uppercase;opacity:0.9;">{{ __('ui.app_name') }}</p>
              <h1 style="margin:10px 0 0;font-size:28px;line-height:1.2;font-weight:700;">{{ __('emails.verify_heading') }}</h1>
              <p style="margin:10px 0 0;font-size:15px;color:#dbeafe;">{{ __('emails.verify_subheading') }}</p>
            </td>
          </tr>
          <tr>
            <td style="padding:28px;">
              <p style="margin:0 0 12px;font-size:16px;">{{ __('emails.hello_user', ['name' => $user->name]) }}</p>
              <p style="margin:0 0 18px;font-size:15px;color:#334155;">{{ __('emails.verify_body') }}</p>

              <p style="margin:0 0 22px;">
                <a href="{{ $verificationUrl }}" style="display:inline-block;padding:14px 24px;border-radius:999px;background:#1a73e8;color:#ffffff;text-decoration:none;font-weight:700;font-size:13px;">{{ __('emails.verify_action') }}</a>
              </p>

              <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:0 0 18px;border:1px solid #e2e8f0;border-radius:12px;">
                <tr>
                  <td style="padding:12px 14px;font-size:13px;color:#475569;">
                    {{ __('emails.link_copy_notice') }}
                  </td>
                </tr>
                <tr>
                  <td style="padding:0 14px 14px;font-size:12px;word-break:break-all;">
                    <a href="{{ $verificationUrl }}" style="color:#1a73e8;text-decoration:none;">{{ $verificationUrl }}</a>
                  </td>
                </tr>
              </table>

              <p style="margin:0;color:#64748b;font-size:13px;">{{ __('emails.ignore_verification') }}</p>
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
</body>
</html>
