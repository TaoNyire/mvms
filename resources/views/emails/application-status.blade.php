<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Application Update - {{ $opportunity->title }}</title>
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
            @if($status === 'accepted')
                background-color: #27ae60;
            @elseif($status === 'rejected')
                background-color: #e74c3c;
            @else
                background-color: #3498db;
            @endif
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
        .status-badge {
            @if($status === 'accepted')
                background-color: #27ae60;
            @elseif($status === 'rejected')
                background-color: #e74c3c;
            @else
                background-color: #f39c12;
            @endif
            color: white;
            padding: 8px 15px;
            border-radius: 4px;
            font-weight: bold;
            display: inline-block;
            margin: 15px 0;
        }
        .button {
            display: inline-block;
            background-color: #3498db;
            color: #ffffff !important;
            text-decoration: none;
            padding: 12px 25px;
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
        .info-box {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            padding: 15px;
            margin: 20px 0;
        }
        .opportunity-details {
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
            <h1>Application Update</h1>
        </div>
        <div class="content">
            <p>Hello {{ $volunteer->name }},</p>
            
            <p>We have an update regarding your volunteer application:</p>
            
            <div class="status-badge">
                @if($status === 'accepted')
                    ✅ APPLICATION ACCEPTED
                @elseif($status === 'rejected')
                    ❌ APPLICATION NOT SELECTED
                @elseif($status === 'pending')
                    ⏳ APPLICATION UNDER REVIEW
                @else
                    📋 APPLICATION {{ strtoupper($status) }}
                @endif
            </div>
            
            <div class="opportunity-details">
                <strong>Opportunity Details:</strong><br>
                <strong>Title:</strong> {{ $opportunity->title }}<br>
                <strong>Organization:</strong> {{ $organization->organizationProfile->org_name ?? $organization->name }}<br>
                <strong>Location:</strong> {{ $opportunity->location }}<br>
                <strong>Start Date:</strong> {{ $opportunity->start_date->format('M d, Y') }}<br>
                @if($opportunity->end_date)
                <strong>End Date:</strong> {{ $opportunity->end_date->format('M d, Y') }}<br>
                @endif
            </div>
            
            @if($status === 'accepted')
                <div class="info-box">
                    <h3 style="color: #27ae60; margin-top: 0;">🎉 Congratulations!</h3>
                    <p>Your application has been accepted! The organization will contact you soon with next steps and additional details.</p>
                    
                    @if($application->organization_notes)
                    <p><strong>Message from Organization:</strong><br>
                    {{ $application->organization_notes }}</p>
                    @endif
                    
                    <p><strong>What's Next:</strong></p>
                    <ul>
                        <li>Wait for the organization to contact you with specific instructions</li>
                        <li>Prepare any required documents or materials</li>
                        <li>Mark your calendar for the opportunity dates</li>
                        <li>Check your MVMS dashboard for updates</li>
                    </ul>
                </div>
                
                <div style="text-align: center;">
                    <a href="{{ url('/volunteer/applications') }}" class="button">View My Applications</a>
                </div>
                
            @elseif($status === 'rejected')
                <div class="info-box">
                    <h3 style="color: #e74c3c; margin-top: 0;">Application Not Selected</h3>
                    <p>Thank you for your interest in this volunteer opportunity. While your application was not selected for this particular opportunity, we encourage you to apply for other opportunities that match your skills and interests.</p>
                    
                    @if($application->rejection_reason)
                    <p><strong>Feedback:</strong><br>
                    {{ $application->rejection_reason }}</p>
                    @endif
                    
                    <p><strong>Keep Volunteering:</strong></p>
                    <ul>
                        <li>Browse other available opportunities</li>
                        <li>Update your volunteer profile to highlight your skills</li>
                        <li>Consider opportunities in different categories or locations</li>
                        <li>Your dedication to volunteering is appreciated!</li>
                    </ul>
                </div>
                
                <div style="text-align: center;">
                    <a href="{{ url('/volunteer/opportunities') }}" class="button">Browse More Opportunities</a>
                </div>
                
            @else
                <div class="info-box">
                    <p>Your application status has been updated to: <strong>{{ $statusLabel }}</strong></p>
                    
                    @if($application->organization_notes)
                    <p><strong>Notes from Organization:</strong><br>
                    {{ $application->organization_notes }}</p>
                    @endif
                    
                    <p>Please check your MVMS dashboard for the most up-to-date information about your application.</p>
                </div>
                
                <div style="text-align: center;">
                    <a href="{{ url('/volunteer/applications') }}" class="button">View Application Details</a>
                </div>
            @endif
            
            <p>Thank you for your commitment to volunteering and making a difference in your community!</p>
            
            <p>Best regards,<br>
            {{ $organization->organizationProfile->org_name ?? $organization->name }}<br>
            <em>via Malawi Volunteer Management System</em></p>
        </div>
        <div class="footer">
            <p>&copy; {{ date('Y') }} Malawi Volunteer Management System. All rights reserved.</p>
            <p>This email was sent regarding your volunteer application. If you have questions, please contact the organization directly.</p>
        </div>
    </div>
</body>
</html>
