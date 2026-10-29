<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ setting('site_name') }}</title>
</head>
<body style="margin:0;padding:0;background:#f4f1ea;font-family:'Segoe UI',Arial,sans-serif;color:#333;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f4f1ea;padding:24px 12px;">
    <tr><td align="center">
        <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="max-width:600px;width:100%;background:#ffffff;border-radius:12px;overflow:hidden;">
            <tr>
                <td style="background:{{ setting('primary_color', '#1f4d36') }};padding:22px 28px;text-align:center;">
                    @if(setting('logo'))
                        <img src="{{ upload_url(setting('logo')) }}" alt="{{ setting('site_name') }}" style="max-height:48px;">
                    @else
                        <div style="font-size:26px;font-weight:700;color:#ffffff;">গৃহস্থালী</div>
                        <div style="font-size:11px;letter-spacing:3px;color:{{ setting('secondary_color', '#c9a24a') }};">{{ strtoupper(setting('site_name')) }}</div>
                    @endif
                </td>
            </tr>
            <tr>
                <td style="padding:30px 28px;font-size:15px;line-height:1.65;">
                    @yield('body')
                </td>
            </tr>
            <tr>
                <td style="background:#faf7f0;padding:18px 28px;text-align:center;font-size:12px;color:#888;border-top:1px solid #eee;">
                    {{ setting('site_name') }} · {{ setting('address') }}<br>
                    {{ setting('phone') }} · <a href="{{ url('/') }}" style="color:{{ setting('primary_color', '#1f4d36') }};">{{ preg_replace('#^https?://#', '', url('/')) }}</a><br>
                    <span style="color:{{ setting('secondary_color', '#c9a24a') }};">{{ setting('footer_tagline') }}</span>
                </td>
            </tr>
        </table>
    </td></tr>
</table>
</body>
</html>
