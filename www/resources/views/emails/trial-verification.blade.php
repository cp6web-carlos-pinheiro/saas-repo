<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Confirme seu e-mail</title>
</head>
<body style="margin:0;padding:0;background:#f3f7fc;font-family:Manrope,Arial,sans-serif;color:#0f172a;">
  <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="padding:24px;">
    <tr>
      <td align="center">
        <table role="presentation" width="620" cellpadding="0" cellspacing="0" style="max-width:620px;background:#ffffff;border-radius:18px;overflow:hidden;border:1px solid #e2e8f0;">
          <tr>
            <td style="padding:28px;background:linear-gradient(120deg,#081021 0%,#101d3a 58%,#0b1832 100%);color:#fff;">
              <h1 style="margin:0;font-size:28px;line-height:1.2;font-family:'Space Grotesk',Arial,sans-serif;">Seu trial esta quase pronto</h1>
              <p style="margin:10px 0 0;color:#dde8f5;font-size:15px;">Confirme seu e-mail para liberar o acesso completo por 14 dias.</p>
            </td>
          </tr>
          <tr>
            <td style="padding:28px;">
              <p style="margin:0 0 12px;font-size:16px;">Oi, {{ $user->name }}.</p>
              <p style="margin:0 0 18px;font-size:15px;color:#334155;">Clique no botao abaixo para confirmar sua conta e iniciar seu onboarding assistido.</p>
              <p style="margin:0 0 22px;">
                <a href="{{ $verificationUrl }}" style="display:inline-block;padding:14px 22px;border-radius:999px;background:#ff7a59;color:#fff;text-decoration:none;font-weight:700;font-size:13px;">CONFIRMAR EMAIL E INICIAR TRIAL</a>
              </p>
              <p style="margin:0;color:#64748b;font-size:13px;">Se voce nao criou esta conta, ignore este e-mail.</p>
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
</body>
</html>
