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
            background-color: #3498db;
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
        .button {
            display: inline-block;
            background-color: #3498db;
            color: #ffffff !important;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 4px;
            margin: 20px 0;
            font-weight: bold;
        }
        h1 {
            margin: 0;
            font-size: 24px;
        }
        p {
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>{{ $title }}</h1>
        </div>
        <div class="content">
            <p>Hello {{ $user->name }},</p>
            
            <p>{!! nl2br(e($message)) !!}</p>
            
            @if(isset($actionUrl) && isset($actionText))
            <div style="text-align: center;">
                <a href="{{ $actionUrl }}" class="button">{{ $actionText }}</a>
            </div>
            @endif
            
            <p>Thank you for using the Malawi Volunteer Management System.</p>
            
            <p>Best regards,<br>MVMS Team</p>
        </div>
        <div class="footer">
            <p>&copy; {{ date('Y') }} Malawi Volunteer Management System. All rights reserved.</p>
            <p>If you're having trouble clicking the "{{ $actionText ?? 'Action' }}" button, copy and paste the URL below into your web browser: {{ $actionUrl ?? '#' }}</p>
        </div>
    </div>
</body>
</html>
