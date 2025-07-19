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
            border-bottom: 2px solid #3b82f6;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        
        .header h1 {
            color: #3b82f6;
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
            background-color: #eff6ff;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        
        .organization-info h3 {
            margin: 0 0 10px 0;
            color: #3b82f6;
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
            width: 20%;
            padding: 10px;
            text-align: center;
            background-color: #f1f5f9;
            border: 1px solid #e2e8f0;
        }
        
        .stats-cell.primary { background-color: #dbeafe; }
        .stats-cell.success { background-color: #dcfce7; }
        .stats-cell.danger { background-color: #fee2e2; }
        .stats-cell.info { background-color: #dbeafe; }
        .stats-cell.warning { background-color: #fef3c7; }
        
        .stats-number {
            font-size: 18px;
            font-weight: bold;
            color: #1e293b;
            margin-bottom: 5px;
        }
        
        .stats-label {
            font-size: 10px;
            color: #64748b;
            text-transform: uppercase;
        }
        
        .section-header {
            background-color: #f8fafc;
            padding: 10px 15px;
            margin: 30px 0 15px 0;
            border-left: 4px solid #3b82f6;
        }
        
        .section-header h3 {
            margin: 0;
            color: #3b82f6;
            font-size: 16px;
        }
        
        .summary-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        .summary-table th,
        .summary-table td {
            border: 1px solid #e2e8f0;
            padding: 8px;
            text-align: left;
            font-size: 11px;
        }
        
        .summary-table th {
            background-color: #f1f5f9;
            font-weight: bold;
            color: #374151;
        }
        
        .summary-table tr:nth-child(even) {
            background-color: #f9fafb;
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
        
        .insight-box {
            background-color: #eff6ff;
            border-left: 4px solid #3b82f6;
            padding: 15px;
            margin: 15px 0;
        }
        
        .insight-box h4 {
            margin: 0 0 10px 0;
            color: #1e40af;
            font-size: 14px;
        }
        
        .insight-box ul {
            margin: 0;
            padding-left: 20px;
            font-size: 11px;
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
        <p><strong>Contact:</strong> {{ $organization->email ?? 'N/A' }} | <strong>Phone:</strong> {{ $organization->phone ?? 'N/A' }}</p>
        <p><strong>Location:</strong> {{ $organization->district ?? 'N/A' }}, {{ $organization->region ?? 'N/A' }}</p>
        <p><strong>Focus Areas:</strong> 
            @if(is_array($organization->focus_areas ?? []) && count($organization->focus_areas) > 0)
                {{ implode(', ', $organization->focus_areas) }}
            @else
                Not specified
            @endif
        </p>
    </div>

    <!-- Executive Summary Statistics -->
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
            <div class="stats-cell warning">
                <div class="stats-number">{{ $statistics['recruitment_rate'] }}%</div>
                <div class="stats-label">Recruitment Rate</div>
            </div>
        </div>
    </div>

    <!-- Executive Summary -->
    <div class="insight-box">
        <h4>Executive Summary</h4>
        <ul>
            <li>Successfully recruited {{ $volunteers_count }} volunteers during {{ $period }}</li>
            <li>Completed {{ $completed_tasks_count }} tasks with a {{ $statistics['success_rate'] }}% success rate</li>
            <li>{{ $failed_tasks_count }} tasks failed or were cancelled</li>
            <li>Overall recruitment rate of {{ $statistics['recruitment_rate'] }}% from applications</li>
            @if($statistics['success_rate'] >= 80)
                <li><strong>Excellent performance:</strong> Success rate above 80%</li>
            @elseif($statistics['success_rate'] >= 60)
                <li><strong>Good performance:</strong> Success rate above 60%</li>
            @else
                <li><strong>Improvement needed:</strong> Success rate below 60%</li>
            @endif
        </ul>
    </div>

    <!-- Recruited Volunteers Section -->
    <div class="section-header">
        <h3>1. Recruited Volunteers Summary ({{ $volunteers_count }})</h3>
    </div>

    @if($volunteers_count > 0)
        <table class="summary-table">
            <thead>
                <tr>
                    <th>Volunteer Name</th>
                    <th>Contact</th>
                    <th>Location</th>
                    <th>Opportunity</th>
                    <th>Accepted Date</th>
                    <th>Assignments</th>
                </tr>
            </thead>
            <tbody>
                @foreach($volunteers->take(10) as $volunteer)
                <tr>
                    <td>{{ $volunteer['volunteer_name'] }}</td>
                    <td>{{ $volunteer['volunteer_phone'] }}</td>
                    <td>{{ $volunteer['volunteer_district'] }}</td>
                    <td>{{ Str::limit($volunteer['opportunity_title'], 30) }}</td>
                    <td>{{ \Carbon\Carbon::parse($volunteer['accepted_at'])->format('M j, Y') }}</td>
                    <td>{{ $volunteer['total_assignments'] }} ({{ $volunteer['active_assignments'] }} active)</td>
                </tr>
                @endforeach
                @if($volunteers_count > 10)
                <tr>
                    <td colspan="6" style="text-align: center; font-style: italic; color: #64748b;">
                        ... and {{ $volunteers_count - 10 }} more volunteers
                    </td>
                </tr>
                @endif
            </tbody>
        </table>
    @else
        <p style="text-align: center; color: #64748b; font-style: italic;">No volunteers were recruited during this period.</p>
    @endif

    <!-- Completed Tasks Section -->
    <div class="section-header">
        <h3>2. Completed Tasks Summary ({{ $completed_tasks_count }})</h3>
    </div>

    @if($completed_tasks_count > 0)
        <table class="summary-table">
            <thead>
                <tr>
                    <th>Task Title</th>
                    <th>Volunteer</th>
                    <th>Opportunity</th>
                    <th>Completed Date</th>
                    <th>Duration</th>
                    <th>Rating</th>
                </tr>
            </thead>
            <tbody>
                @foreach($completed_tasks->take(10) as $task)
                <tr>
                    <td>{{ Str::limit($task['task_title'], 25) }}</td>
                    <td>{{ $task['volunteer_name'] }}</td>
                    <td>{{ Str::limit($task['opportunity_title'], 20) }}</td>
                    <td>{{ \Carbon\Carbon::parse($task['completed_at'])->format('M j, Y') }}</td>
                    <td>
                        @if($task['duration_minutes'])
                            {{ floor($task['duration_minutes'] / 60) }}h {{ $task['duration_minutes'] % 60 }}m
                        @else
                            N/A
                        @endif
                    </td>
                    <td>
                        @if($task['performance_rating'])
                            {{ $task['performance_rating'] }}/5
                        @else
                            Not rated
                        @endif
                    </td>
                </tr>
                @endforeach
                @if($completed_tasks_count > 10)
                <tr>
                    <td colspan="6" style="text-align: center; font-style: italic; color: #64748b;">
                        ... and {{ $completed_tasks_count - 10 }} more completed tasks
                    </td>
                </tr>
                @endif
            </tbody>
        </table>
    @else
        <p style="text-align: center; color: #64748b; font-style: italic;">No tasks were completed during this period.</p>
    @endif

    <!-- Failed Tasks Section -->
    <div class="section-header">
        <h3>3. Failed Tasks Summary ({{ $failed_tasks_count }})</h3>
    </div>

    @if($failed_tasks_count > 0)
        <table class="summary-table">
            <thead>
                <tr>
                    <th>Task Title</th>
                    <th>Volunteer</th>
                    <th>Opportunity</th>
                    <th>Failure Reason</th>
                    <th>Failure Date</th>
                    <th>Priority</th>
                </tr>
            </thead>
            <tbody>
                @foreach($failed_tasks->take(10) as $task)
                <tr>
                    <td>{{ Str::limit($task['task_title'], 25) }}</td>
                    <td>{{ $task['volunteer_name'] }}</td>
                    <td>{{ Str::limit($task['opportunity_title'], 20) }}</td>
                    <td>{{ $task['failure_reason'] }}</td>
                    <td>{{ \Carbon\Carbon::parse($task['failure_date'])->format('M j, Y') }}</td>
                    <td>{{ ucfirst($task['task_priority']) }}</td>
                </tr>
                @endforeach
                @if($failed_tasks_count > 10)
                <tr>
                    <td colspan="6" style="text-align: center; font-style: italic; color: #64748b;">
                        ... and {{ $failed_tasks_count - 10 }} more failed tasks
                    </td>
                </tr>
                @endif
            </tbody>
        </table>

        <!-- Failure Analysis -->
        <div class="insight-box">
            <h4>Failure Analysis</h4>
            <ul>
                @php
                    $failureReasons = $failed_tasks->groupBy('failure_reason');
                @endphp
                @foreach($failureReasons as $reason => $reasonTasks)
                    <li>{{ $reason }}: {{ $reasonTasks->count() }} tasks ({{ round(($reasonTasks->count() / $failed_tasks_count) * 100, 1) }}%)</li>
                @endforeach
            </ul>
        </div>
    @else
        <p style="text-align: center; color: #64748b; font-style: italic;">No tasks failed during this period. Excellent work!</p>
    @endif

    <!-- Recommendations -->
    <div class="insight-box">
        <h4>Recommendations</h4>
        <ul>
            @if($statistics['success_rate'] < 70)
                <li>Focus on improving task success rate through better volunteer preparation and support</li>
            @endif
            @if($statistics['recruitment_rate'] < 50)
                <li>Review application process and volunteer requirements to improve recruitment rate</li>
            @endif
            @if($failed_tasks_count > 0)
                <li>Analyze common failure reasons and implement preventive measures</li>
            @endif
            @if($volunteers_count > 0)
                <li>Continue engaging recruited volunteers for future opportunities</li>
            @endif
            <li>Consider volunteer feedback to improve overall experience and retention</li>
        </ul>
    </div>

    <!-- Footer -->
    <div class="footer">
        <p>This comprehensive report was generated by the Malawi Volunteer Management System (MVMS)</p>
        <p>Report generated on {{ $generated_at->format('F j, Y \a\t g:i A') }}</p>
        <p>For detailed individual reports, please generate specific volunteer, completed task, or failed task reports.</p>
    </div>
</body>
</html>
