<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $post->title }}</title>
</head>
<body style="margin:0;padding:0;background-color:#f4f5f7;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,'Helvetica Neue',Arial,sans-serif;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f4f5f7;padding:32px 16px;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background-color:#ffffff;border-radius:12px;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,0.1);">
                    <tr>
                        <td style="background-color:#003274;padding:24px 32px;">
                            <h1 style="margin:0;color:#ffffff;font-size:20px;font-weight:700;">Новая статья в блоге</h1>
                        </td>
                    </tr>
                    @if($post->image)
                    <tr>
                        <td>
                            <img src="{{ $post->image }}" alt="{{ $post->title }}" style="width:100%;height:auto;display:block;">
                        </td>
                    </tr>
                    @endif
                    <tr>
                        <td style="padding:32px;">
                            <h2 style="margin:0 0 12px;color:#1a1a1a;font-size:22px;font-weight:700;line-height:1.3;">
                                {{ $post->title }}
                            </h2>
                            @if($post->excerpt)
                            <p style="margin:0 0 24px;color:#555;font-size:15px;line-height:1.6;">
                                {{ $post->excerpt }}
                            </p>
                            @endif
                            <a href="{{ route('blog.show', $post->slug) }}" style="display:inline-block;background-color:#003274;color:#ffffff;text-decoration:none;padding:12px 28px;border-radius:8px;font-size:14px;font-weight:600;">
                                Читать статью →
                            </a>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:16px 32px;border-top:1px solid #eee;">
                            <p style="margin:0;color:#999;font-size:12px;line-height:1.5;">
                                Вы получили это письмо, потому что подписались на рассылку блога.<br>
                                <a href="{{ $unsubscribeUrl }}" style="color:#999;text-decoration:underline;">Отписаться от рассылки</a>
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
