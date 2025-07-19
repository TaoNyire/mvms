<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }}</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            padding: 20px;
            margin: 0;
            background-color: #f9f9f9;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .header {
            background-color: #27ae60;
            color: #ffffff;
            padding: 20px;
            text-align: center;
        }
        .content {
            padding: 20px;
        }
        .footer {
            background-color: #f5f5f5;
            padding: 15px;
            text-align: center;
            font-size: 12px;
            color: #666;
        }
        .success-badge {
            background-color: #27ae60;
            color: white;
            padding: 5px 10px;
            border-radius: 4px;
            font-weight: bold;
            display: inline-block;
            margin: 10px 0;
        }
        h1 {
            margin: 0;
            font-size: 24px;
        }
        p {
            margin-bottom: 15px;
        }
        .info-box {
            background-color: #e8f4fd;
            border-left: 4px solid #3498db;
            padding: 15px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>✅ {{ $title }}</h1>
        </div>
        <div class="content">
            <div class="success-badge">EMAIL CONFIGURATION WORKING!</div>
            
            <p>{!! nl2br(e($message)) !!}</p>
            
            <div class="info-box">
                <strong>Test Details:</strong><br>
                Sent at: {{ $timestamp }}<br>
                System: Malawi Volunteer Management System (MVMS)<br>
                Status: Email delivery successful
            </div>
            
            <p><strong>What this means:</strong></p>
            <ul>
                <li>Your SMTP configuration is correct</li>
                <li>Gmail authentication is working</li>
                <li>Email notifications will now be sent to users</li>
                <li>The system can communicate with external email services</li>
            </ul>
            
            <p><strong>Next Steps:</strong></p>
            <ul>
                <li>Email notifications are now active for your MVMS system</li>
                <li>Users will receive notifications for applications, messages, and announcements</li>
                <li>Monitor the system logs for any email delivery issues</li>
            </ul>
            
            <p>If you received this email, your MVMS email configuration is working perfectly!</p>
            
            <p>Best regards,<br>MVMS Development Team</p>
        </div>
        <div class="footer">
            <p>&copy; {{ date('Y') }} Malawi Volunteer Management System. All rights reserved.</p>
            <p>This is an automated test email from your MVMS installation.</p>
        </div>
    </div>
</body>
</html>
