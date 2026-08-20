<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Reset your NextGenEM password</title>
</head>
<body style="margin:0;background:#f2f5f9;color:#10243f;font-family:Arial,Helvetica,sans-serif">
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#f2f5f9;padding:30px 12px">
    <tr><td align="center">
        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:600px;overflow:hidden;border:1px solid #d2dce7;border-radius:10px;background:#ffffff">
            <tr><td style="padding:24px 30px;background:#071d38;text-align:center">
                <img src="{{ asset('images/region-7-emergency-management-shield.webp') }}" width="88" height="88" alt="Region 7 Emergency Management shield" style="display:block;margin:0 auto 12px;width:88px;height:88px">
                <div style="color:#ffffff;font-size:24px;font-weight:700;letter-spacing:-.3px">NextGenEM</div>
                <div style="margin-top:5px;color:#f0a46f;font-size:11px;font-weight:700;letter-spacing:1.4px;text-transform:uppercase">Region 7 Emergency Management</div>
            </td></tr>
            <tr><td style="padding:34px 34px 30px">
                <h1 style="margin:0 0 18px;color:#10243f;font-size:25px;line-height:1.25">Reset your password</h1>
                <p style="margin:0 0 16px;color:#4d6075;font-size:16px;line-height:1.6">Hello {{ $name }},</p>
                <p style="margin:0 0 24px;color:#4d6075;font-size:16px;line-height:1.6">We received a request to reset the password for your NextGenEM account. Use the secure button below to choose a new password.</p>
                <p style="margin:0 0 26px;text-align:center"><a href="{{ $resetUrl }}" style="display:inline-block;border-radius:6px;background:#0759d9;color:#ffffff;padding:14px 24px;font-size:15px;font-weight:700;text-decoration:none">Reset NextGenEM password</a></p>
                <p style="margin:0;color:#6a788b;font-size:13px;line-height:1.6">If you did not request a password reset, no action is needed. This link expires in {{ config('auth.passwords.'.config('auth.defaults.passwords').'.expire') }} minutes.</p>
            </td></tr>
            <tr><td style="border-top:1px solid #dce4ec;padding:18px 34px;color:#718096;font-size:12px;line-height:1.5">NextGenEM · Region 7 Emergency Management</td></tr>
        </table>
    </td></tr>
</table>
</body>
</html>
