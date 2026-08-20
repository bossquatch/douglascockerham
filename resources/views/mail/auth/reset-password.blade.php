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
            <tr><td style="padding:24px 30px;background:#ffffff;text-align:center;border-bottom:4px solid #0759d9">
                <img src="{{ asset('images/nextgenem-logo.png') }}" width="360" alt="NextGenEM" style="display:block;margin:0 auto;width:100%;max-width:360px;height:auto">
            </td></tr>
            <tr><td style="padding:34px 34px 30px">
                <h1 style="margin:0 0 18px;color:#10243f;font-size:25px;line-height:1.25">Reset your password</h1>
                <p style="margin:0 0 16px;color:#4d6075;font-size:16px;line-height:1.6">Hello {{ $name }},</p>
                <p style="margin:0 0 24px;color:#4d6075;font-size:16px;line-height:1.6">We received a request to reset the password for your NextGenEM account. Use the secure button below to choose a new password.</p>
                <p style="margin:0 0 26px;text-align:center"><a href="{{ $resetUrl }}" style="display:inline-block;border-radius:6px;background:#0759d9;color:#ffffff;padding:14px 24px;font-size:15px;font-weight:700;text-decoration:none">Reset NextGenEM password</a></p>
                <p style="margin:0;color:#6a788b;font-size:13px;line-height:1.6">If you did not request a password reset, no action is needed. This link expires in {{ config('auth.passwords.'.config('auth.defaults.passwords').'.expire') }} minutes.</p>
            </td></tr>
            <tr><td style="border-top:1px solid #dce4ec;padding:18px 34px;color:#718096;font-size:12px;line-height:1.5">NextGenEM</td></tr>
        </table>
    </td></tr>
</table>
</body>
</html>
