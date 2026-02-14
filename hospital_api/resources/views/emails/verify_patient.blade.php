<!DOCTYPE html>
<html>

<head>
    <title>Verify Your Email</title>
</head>

<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 10px;">
        <h2 style="color: #3b82f6; text-align: center;">Welcome to TeleHealth BD!</h2>
        <p>Hello {{ $patient->name }},</p>
        <p>Thank you for registering with us. To verify your account and access all features, please click the button
            below:</p>

        <div style="text-align: center; margin: 30px 0;">
            <a href="http://localhost:3000/verify-email?token={{ $token }}"
                style="background-color: #3b82f6; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; font-weight: bold;">Verify
                Email Address</a>
        </div>

        <p>Or copy and paste this link in your browser:</p>
        <p style="background: #f3f4f6; padding: 10px; word-break: break-all;">
            http://localhost:3000/verify-email?token={{ $token }}</p>

        <p>If you did not create an account, no further action is required.</p>
        <p>Regards,<br>TeleHealth BD Team</p>
    </div>
</body>

</html>