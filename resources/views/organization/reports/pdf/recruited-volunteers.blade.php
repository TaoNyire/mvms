<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        
        .header {
            text-align: center;
            border-bottom: 2px solid #0ea5e9;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        
        .header h1 {
            color: #0ea5e9;
            font-size: 24px;
            margin: 0 0 10px 0;
            font-weight: bold;
        }
        
        .header .subtitle {
            font-size: 16px;
            color: #666;
            margin: 5px 0;
        }
        
        .organization-info {
            background-color: #f8fafc;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        
        .organization-info h3 {
            margin: 0 0 10px 0;
            color: #0ea5e9;
            font-size: 16px;
        }
        
        .stats-grid {
            display: table;
            width: 100%;
            margin-bottom: 30px;
        }
        
        .stats-row {
            display: table-row;
        }
        
        .stats-cell {
            display: table-cell;
            width: 25%;
            padding: 10px;
            text-align: center;
            background-color: #f1f5f9;
            border: 1px solid #e2e8f0;
        }
        
        .stats-cell.primary { background-color: #dbeafe; }
        .stats-cell.success { background-color: #dcfce7; }
        .stats-cell.danger { background-color: #fee2e2; }
        .stats-cell.info { background-color: #dbeafe; }
        
        .stats-number {
            font-size: 20px;
            font-weight: bold;
            color: #1e293b;
            margin-bottom: 5px;
        }
        
        .stats-label {
            font-size: 11px;
            color: #64748b;
            text-transform: uppercase;
        }
        
        .volunteers-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        
        .volunteers-table th,
        .volunteers-table td {
            border: 1px solid #e2e8f0;
            padding: 8px;
            text-align: left;
            font-size: 10px;
        }
        
        .volunteers-table th {
            background-color: #0ea5e9;
            color: white;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .volunteers-table tr:nth-child(even) {
            background-color: #f8fafc;
        }
        
        .skills-list {
            font-size: 9px;
            color: #64748b;
        }
        
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e2e8f0;
            text-align: center;
            font-size: 10px;
            color: #64748b;
        }
        
        .page-break {
            page-break-before: always;
        }
        
        .no-data {
            text-align: center;
            padding: 40px;
            color: #64748b;
            font-style: italic;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <h1>{{ $title }}</h1>
        <div class="subtitle">{{ $period }}</div>
        <div class="subtitle">Generated on {{ $generated_at->format('F j, Y \a\t g:i A') }}</div>
    </div>

    <!-- Organization Information -->
    <div class="organization-info">
        <h3>{{ $organization->org_name ?? 'Organization' }}</h3>
        <p><strong>Contact:</strong> {{ $organization->email ?? 'N/A' }}</p>
        <p><strong>District:</strong> {{ $organization->district ?? 'N/A' }}</p>
        <p><strong>Region:</strong> {{ $organization->region ?? 'N/A' }}</p>
    </div>

    <!-- Statistics -->
    <div class="stats-grid">
        <div class="stats-row">
            <div class="stats-cell primary">
                <div class="stats-number">{{ $statistics['recruited_volunteers'] }}</div>
                <div class="stats-label">Recruited Volunteers</div>
            </div>
            <div class="stats-cell success">
                <div class="stats-number">{{ $statistics['completed_tasks'] }}</div>
                <div class="stats-label">Completed Tasks</div>
            </div>
            <div class="stats-cell danger">
                <div class="stats-number">{{ $statistics['failed_tasks'] }}</div>
                <div class="stats-label">Failed Tasks</div>
            </div>
            <div class="stats-cell info">
                <div class="stats-number">{{ $statistics['success_rate'] }}%</div>
                <div class="stats-label">Success Rate</div>
            </div>
        </div>
    </div>

    <!-- Volunteers List -->
    <h3 style="color: #0ea5e9; margin-bottom: 15px;">Recruited Volunteers ({{ $total_count }})</h3>

    @if($volunteers->count() > 0)
        <table class="volunteers-table">
            <thead>
                <tr>
                    <th style="width: 15%;">Volunteer Name</th>
                    <th style="width: 12%;">Contact</th>
                    <th style="width: 10%;">Location</th>
                    <th style="width: 18%;">Opportunity</th>
                    <th style="width: 15%;">Skills</th>
                    <th style="width: 10%;">Accepted Date</th>
                    <th style="width: 8%;">Assignments</th>
                    <th style="width: 12%;">Experience</th>
                </tr>
            </thead>
            <tbody>
                @foreach($volunteers as $volunteer)
                <tr>
                    <td>
                        <strong>{{ $volunteer['volunteer_name'] }}</strong><br>
                        <small>{{ $volunteer['volunteer_email'] }}</small>
                    </td>
                    <td>
                        {{ $volunteer['volunteer_phone'] }}<br>
                        <small>{{ $volunteer['volunteer_email'] }}</small>
                    </td>
                    <td>
                        {{ $volunteer['volunteer_district'] }}<br>
                        <small>{{ $volunteer['volunteer_region'] }}</small>
                    </td>
                    <td>
                        <strong>{{ $volunteer['opportunity_title'] }}</strong><br>
                        <small>{{ $volunteer['opportunity_category'] }}</small>
                    </td>
                    <td>
                        <div class="skills-list">
                            @if(is_array($volunteer['volunteer_skills']) && count($volunteer['volunteer_skills']) > 0)
                                {{ implode(', ', $volunteer['volunteer_skills']) }}
                            @else
                                No skills listed
                            @endif
                        </div>
                    </td>
                    <td>
                        {{ \Carbon\Carbon::parse($volunteer['accepted_at'])->format('M j, Y') }}
                    </td>
                    <td>
                        Total: {{ $volunteer['total_assignments'] }}<br>
                        <small>Active: {{ $volunteer['active_assignments'] }}</small>
                    </td>
                    <td>
                        <div style="font-size: 9px;">
                            {{ Str::limit($volunteer['relevant_experience'] ?? 'No experience provided', 100) }}
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <div class="no-data">
            <p>No volunteers were recruited during {{ $period }}.</p>
        </div>
    @endif

    <!-- Footer -->
    <div class="footer">
        <p>This report was generated by the Malawi Volunteer Management System (MVMS)</p>
        <p>Report generated on {{ $generated_at->format('F j, Y \a\t g:i A') }}</p>
    </div>
</body>
</html>
