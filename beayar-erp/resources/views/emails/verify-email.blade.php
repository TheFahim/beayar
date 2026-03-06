<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title>Verify your email address - Beayar ERP</title>
    <style>
        body { font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #f3f4f6; margin: 0; padding: 0; -webkit-font-smoothing: antialiased; }
        .wrapper { width: 100%; table-layout: fixed; background-color: #f3f4f6; padding: 40px 0; }
        .container { max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06); }
        .header { background-color: #0b0f1a; padding: 32px 40px; text-align: center; }
        .header h1 { color: #ffffff; margin: 0; font-size: 24px; font-weight: 700; letter-spacing: -0.5px; }
        .content { padding: 40px; text-align: center; }
        .content h2 { color: #111827; font-size: 20px; font-weight: 600; margin-top: 0; margin-bottom: 16px; }
        .content p { color: #4b5563; font-size: 16px; line-height: 1.6; margin-top: 0; margin-bottom: 32px; }
        .button { display: inline-block; background-image: linear-gradient(135deg, #4f46e5 0%, #6366f1 50%, #818cf8 100%); color: #ffffff; text-decoration: none; padding: 16px 32px; border-radius: 8px; font-weight: 600; font-size: 16px; text-align: center; box-shadow: 0 4px 6px -1px rgba(79, 70, 229, 0.4); border: transparent; }
        .footer { background-color: #f9fafb; padding: 24px 40px; text-align: center; border-top: 1px solid #e5e7eb; }
        .footer p { color: #6b7280; font-size: 13px; line-height: 1.5; margin: 0; }
        .footer a { color: #4f46e5; text-decoration: none; }
    </style>
</head>
<body>
    <div class="wrapper">
        <table class="container" cellspacing="0" cellpadding="0" border="0" align="center">
            <tr>
                <td class="header">
                    <h1>Beayar ERP</h1>
                </td>
            </tr>
            <tr>
                <td class="content">
                    <h2>Hello {{ $user->name }},</h2>
                    <p>Welcome to Beayar ERP! We're excited to have you on board. Please click the button below to verify your email address and activate your account.</p>
                    <a href="{{ $url }}" class="button" target="_blank">Verify Email Address</a>
                    <p style="margin-top: 32px; font-size: 14px; text-align: left; color: #6b7280;">If you did not create an account, no further action is required.</p>
                </td>
            </tr>
            <tr>
                <td class="footer">
                    <p>&copy; {{ date('Y') }} Beayar ERP. All rights reserved.</p>
                    <p>If you're having trouble clicking the "Verify Email Address" button, copy and paste the URL below into your web browser:</p>
                    <p style="word-break: break-all; margin-top: 8px;"><a href="{{ $url }}">{{ $url }}</a></p>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
