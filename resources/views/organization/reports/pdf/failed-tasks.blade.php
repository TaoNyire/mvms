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
            border-bottom: 2px solid #ef4444;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        
        .header h1 {
            color: #ef4444;
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
            background-color: #fef2f2;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        
        .organization-info h3 {
            margin: 0 0 10px 0;
            color: #ef4444;
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
        
        .tasks-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        
        .tasks-table th,
        .tasks-table td {
            border: 1px solid #e2e8f0;
            padding: 8px;
            text-align: left;
            font-size: 10px;
        }
        
        .tasks-table th {
            background-color: #ef4444;
            color: white;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .tasks-table tr:nth-child(even) {
            background-color: #fef2f2;
        }
        
        .priority-badge {
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .priority-urgent { background-color: #fee2e2; color: #dc2626; }
        .priority-high { background-color: #fed7aa; color: #ea580c; }
        .priority-medium { background-color: #fef3c7; color: #d97706; }
        .priority-low { background-color: #dcfce7; color: #16a34a; }
        
        .failure-badge {
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
            background-color: #fee2e2;
            color: #dc2626;
        }
        
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e2e8f0;
            text-align: center;
            font-size: 10px;
            color: #64748b;
        }
        
        .no-data {
            text-align: center;
            padding: 40px;
            color: #64748b;
            font-style: italic;
        }
        
        .failure-analysis {
            background-color: #fef2f2;
            padding: 15px;
            border-radius: 5px;
            margin-top: 20px;
        }
        
        .failure-analysis h4 {
            color: #ef4444;
            margin-bottom: 10px;
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
                <div class="stats-number">{{ $statistics['failure_rate'] }}%</div>
                <div class="stats-label">Failure Rate</div>
            </div>
        </div>
    </div>

    <!-- Failed Tasks List -->
    <h3 style="color: #ef4444; margin-bottom: 15px;">Failed Tasks ({{ $total_count }})</h3>

    @if($tasks->count() > 0)
        <table class="tasks-table">
            <thead>
                <tr>
                    <th style="width: 15%;">Task Details</th>
                    <th style="width: 12%;">Volunteer</th>
                    <th style="width: 12%;">Opportunity</th>
                    <th style="width: 8%;">Priority</th>
                    <th style="width: 12%;">Failure Reason</th>
                    <th style="width: 10%;">Failure Date</th>
                    <th style="width: 10%;">Scheduled Time</th>
                    <th style="width: 21%;">Notes & Reasons</th>
                </tr>
            </thead>
            <tbody>
                @foreach($tasks as $task)
                <tr>
                    <td>
                        <strong>{{ $task['task_title'] }}</strong><br>
                        <small>{{ Str::limit($task['task_description'], 80) }}</small>
                    </td>
                    <td>
                        <strong>{{ $task['volunteer_name'] }}</strong><br>
                        <small>{{ $task['volunteer_phone'] }}</small>
                    </td>
                    <td>
                        {{ $task['opportunity_title'] }}
                    </td>
                    <td>
                        <span class="priority-badge priority-{{ $task['task_priority'] }}">
                            {{ ucfirst($task['task_priority']) }}
                        </span>
                    </td>
                    <td>
                        <span class="failure-badge">
                            {{ $task['failure_reason'] }}
                        </span>
                    </td>
                    <td>
                        {{ \Carbon\Carbon::parse($task['failure_date'])->format('M j, Y') }}
                    </td>
                    <td>
                        @if($task['scheduled_start'])
                            <div style="font-size: 9px;">
                                <strong>Start:</strong><br>
                                {{ \Carbon\Carbon::parse($task['scheduled_start'])->format('M j, g:i A') }}<br>
                                @if($task['scheduled_end'])
                                    <strong>End:</strong><br>
                                    {{ \Carbon\Carbon::parse($task['scheduled_end'])->format('M j, g:i A') }}
                                @endif
                            </div>
                        @else
                            <small>Not scheduled</small>
                        @endif
                    </td>
                    <td>
                        <div style="font-size: 9px;">
                            @if($task['decline_reason'])
                                <strong>Decline Reason:</strong><br>
                                {{ Str::limit($task['decline_reason'], 80) }}<br>
                            @endif
                            @if($task['assignment_notes'])
                                <strong>Assignment Notes:</strong><br>
                                {{ Str::limit($task['assignment_notes'], 60) }}<br>
                            @endif
                            @if($task['volunteer_notes'])
                                <strong>Volunteer Notes:</strong><br>
                                {{ Str::limit($task['volunteer_notes'], 60) }}
                            @endif
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Failure Analysis -->
        <div class="failure-analysis">
            <h4>Failure Analysis</h4>
            <div style="display: table; width: 100%;">
                @php
                    $failureReasons = $tasks->groupBy('failure_reason');
                    $totalFailed = $tasks->count();
                @endphp
                
                @foreach($failureReasons as $reason => $reasonTasks)
                <div style="display: table-row;">
                    <div style="display: table-cell; width: 40%; padding: 5px;">
                        <strong>{{ $reason }}:</strong>
                    </div>
                    <div style="display: table-cell; width: 20%; padding: 5px;">
                        {{ $reasonTasks->count() }} tasks
                    </div>
                    <div style="display: table-cell; width: 20%; padding: 5px;">
                        ({{ round(($reasonTasks->count() / $totalFailed) * 100, 1) }}%)
                    </div>
                    <div style="display: table-cell; width: 20%; padding: 5px;">
                        @php
                            $priorityBreakdown = $reasonTasks->groupBy('task_priority');
                        @endphp
                        @foreach($priorityBreakdown as $priority => $priorityTasks)
                            <span class="priority-badge priority-{{ $priority }}">
                                {{ ucfirst($priority) }}: {{ $priorityTasks->count() }}
                            </span>
                        @endforeach
                    </div>
                </div>
                @endforeach
            </div>
            
            <div style="margin-top: 15px; padding-top: 10px; border-top: 1px solid #fecaca;">
                <strong>Key Insights:</strong>
                <ul style="margin: 5px 0; padding-left: 20px; font-size: 10px;">
                    <li>Most common failure reason: {{ $failureReasons->sortByDesc(function($tasks) { return $tasks->count(); })->keys()->first() }}</li>
                    <li>High priority failed tasks: {{ $tasks->where('task_priority', 'high')->count() + $tasks->where('task_priority', 'urgent')->count() }}</li>
                    <li>Volunteer no-shows: {{ $tasks->where('failure_reason', 'Volunteer No-Show')->count() }}</li>
                    <li>Task cancellations: {{ $tasks->where('failure_reason', 'Task Cancelled')->count() }}</li>
                </ul>
            </div>
        </div>
    @else
        <div class="no-data">
            <p>No tasks failed during {{ $period }}. Excellent work!</p>
        </div>
    @endif

    <!-- Footer -->
    <div class="footer">
        <p>This report was generated by the Malawi Volunteer Management System (MVMS)</p>
        <p>Report generated on {{ $generated_at->format('F j, Y \a\t g:i A') }}</p>
    </div>
</body>
</html>
