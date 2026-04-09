<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="margin:0; padding:0; background-color:#f3f4f6; font-family:Arial, Helvetica, sans-serif;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f3f4f6; padding:40px 20px;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background-color:#ffffff; border-radius:16px; overflow:hidden; box-shadow:0 4px 24px rgba(0,0,0,0.06);">
                    <tr>
                        <td style="background:linear-gradient(135deg, #001a3f 0%, #003274 45%, #025EA1 100%); padding:32px 40px; text-align:center;">
                            <p style="color:#92c4ed; font-size:13px; margin:0 0 8px; letter-spacing:0.02em;">{{ $mailFromName }}</p>
                            <h1 style="color:#ffffff; font-size:22px; margin:0; font-weight:700;">Сброс пароля</h1>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:40px;">
                            <p style="font-size:16px; color:#1f2937; margin:0 0 16px;">
                                Здравствуйте!
                            </p>
                            <p style="font-size:15px; color:#4b5563; line-height:1.6; margin:0 0 24px;">
                                Вы получили это письмо, потому что для вашего аккаунта запрошен сброс пароля на платформе
                                <strong>Высшей школы гостеприимства Росатома</strong>. Нажмите кнопку ниже, чтобы задать новый пароль.
                            </p>
                            <table width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td align="center" style="padding:8px 0 24px;">
                                        <a href="{{ $resetUrl }}"
                                           style="display:inline-block; background-color:#003274; color:#ffffff; text-decoration:none; font-size:15px; font-weight:600; padding:14px 36px; border-radius:10px;">
                                            Сбросить пароль
                                        </a>
                                    </td>
                                </tr>
                            </table>
                            <p style="font-size:14px; color:#4b5563; line-height:1.5; margin:0 0 16px;">
                                Ссылка действительна <strong>{{ $expireMinutes }} минут</strong>. Если вы не запрашивали сброс, просто проигнорируйте это письмо — пароль не изменится.
                            </p>
                            <p style="font-size:13px; color:#9ca3af; line-height:1.5; margin:0;">
                                Если кнопка не открывается, скопируйте адрес в браузер:<br>
                                <a href="{{ $resetUrl }}" style="color:#025EA1; word-break:break-all;">{{ $resetUrl }}</a>
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td style="background-color:#f9fafb; padding:20px 40px; text-align:center; border-top:1px solid #e5e7eb;">
                            <p style="font-size:12px; color:#9ca3af; margin:0;">
                                С уважением, команда {{ $mailFromName }}<br>
                                Автоматическое сообщение, отвечать не нужно.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
