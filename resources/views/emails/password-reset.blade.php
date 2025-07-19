<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Reset Your Password - MVMS</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f8f9fa;
        }
        .container {
            background: white;
            border-radius: 12px;
            padding: 40px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #e9ecef;
        }
        .logo {
            font-size: 2rem;
            color: #0d6efd;
            margin-bottom: 10px;
        }
        .title {
            color: #0d6efd;
            font-size: 1.5rem;
            font-weight: bold;
            margin: 0;
        }
        .content {
            margin-bottom: 30px;
        }
        .greeting {
            font-size: 1.1rem;
            margin-bottom: 20px;
        }
        .message {
            margin-bottom: 25px;
            line-height: 1.7;
        }
        .button {
            display: inline-block;
            background: linear-gradient(135deg, #0d6efd 0%, #0b5ed7 100%);
            color: white;
            text-decoration: none;
            padding: 15px 30px;
            border-radius: 8px;
            font-weight: 600;
            text-align: center;
            margin: 20px 0;
            box-shadow: 0 4px 15px rgba(13, 110, 253, 0.3);
        }
        .button:hover {
            background: linear-gradient(135deg, #0b5ed7 0%, #0a58ca 100%);
        }
        .alternative-link {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
            border-left: 4px solid #0d6efd;
        }
        .alternative-link p {
            margin: 0;
            font-size: 0.9rem;
            color: #6c757d;
        }
        .alternative-link a {
            color: #0d6efd;
            word-break: break-all;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e9ecef;
            text-align: center;
            color: #6c757d;
            font-size: 0.9rem;
        }
        .warning {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 8px;
            padding: 15px;
            margin: 20px 0;
            color: #856404;
        }
        .warning strong {
            color: #664d03;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">🏛️</div>
            <h1 class="title">MVMS</h1>
            <p style="margin: 0; color: #6c757d;">Malawi Volunteer Management System</p>
        </div>

        <div class="content">
            <div class="greeting">
                Hello {{ $user->name }},
            </div>

            <div class="message">
                <p>You are receiving this email because we received a password reset request for your account.</p>
                
                <p>Click the button below to reset your password:</p>
            </div>

            <div style="text-align: center;">
                <a href="{{ url('/reset-password/' . $token) }}" class="button">
                    🔐 Reset Password
                </a>
            </div>

            <div class="alternative-link">
                <p><strong>Button not working?</strong> Copy and paste this link into your browser:</p>
                <a href="{{ url('/reset-password/' . $token) }}">{{ url('/reset-password/' . $token) }}</a>
            </div>

            <div class="warning">
                <strong>⚠️ Security Notice:</strong>
                <ul style="margin: 10px 0; padding-left: 20px;">
                    <li>This password reset link will expire in 60 minutes</li>
                    <li>If you did not request a password reset, please ignore this email</li>
                    <li>For security, do not share this link with anyone</li>
                </ul>
            </div>

            <div class="message">
                <p>If you're having trouble with the password reset process, please contact our support team.</p>
            </div>
        </div>

        <div class="footer">
            <p><strong>MVMS - Malawi Volunteer Management System</strong></p>
            <p>This is an automated message. Please do not reply to this email.</p>
            <p style="margin-top: 15px;">
                <small>© {{ date('Y') }} MVMS. All rights reserved.</small>
            </p>
        </div>
    </div>
</body>
</html>
