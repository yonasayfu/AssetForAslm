<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verification Code</title>
</head>
<body style="font-family: sans-serif; line-height: 1.5;">
    <div style="padding: 24px;">
        <h1 style="font-size: 24px; font-weight: bold;">Password Reset Verification</h1>
        <p>Your verification code is:</p>
        <p style="font-size: 32px; font-weight: bold; letter-spacing: 4px; margin: 24px 0; text-align: center;">{{ $code }}</p>
        <p>This code will expire in 15 minutes.</p>
        <p>If you did not request a password reset, no further action is required.</p>
    </div>
</body>
</html>
