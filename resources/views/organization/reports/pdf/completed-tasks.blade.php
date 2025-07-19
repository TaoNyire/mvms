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
            border-bottom: 2px solid #10b981;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        
        .header h1 {
            color: #10b981;
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
            background-color: #f0fdf4;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        
        .organization-info h3 {
            margin: 0 0 10px 0;
            color: #10b981;
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
            background-color: #10b981;
            color: white;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .tasks-table tr:nth-child(even) {
            background-color: #f0fdf4;
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
        
        .rating-stars {
            color: #fbbf24;
            font-size: 12px;
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
        
        .duration-info {
            font-size: 9px;
            color: #64748b;
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

    <!-- Completed Tasks List -->
    <h3 style="color: #10b981; margin-bottom: 15px;">Completed Tasks ({{ $total_count }})</h3>

    @if($tasks->count() > 0)
        <table class="tasks-table">
            <thead>
                <tr>
                    <th style="width: 15%;">Task Details</th>
                    <th style="width: 12%;">Volunteer</th>
                    <th style="width: 12%;">Opportunity</th>
                    <th style="width: 8%;">Priority</th>
                    <th style="width: 15%;">Timeline</th>
                    <th style="width: 10%;">Duration</th>
                    <th style="width: 8%;">Rating</th>
                    <th style="width: 20%;">Notes</th>
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
                        <div class="duration-info">
                            <strong>Assigned:</strong><br>
                            {{ \Carbon\Carbon::parse($task['assigned_at'])->format('M j, Y') }}<br>
                            <strong>Completed:</strong><br>
                            {{ \Carbon\Carbon::parse($task['completed_at'])->format('M j, Y') }}
                        </div>
                    </td>
                    <td>
                        @if($task['duration_minutes'])
                            <div class="duration-info">
                                {{ floor($task['duration_minutes'] / 60) }}h {{ $task['duration_minutes'] % 60 }}m
                            </div>
                        @else
                            <small>N/A</small>
                        @endif
                    </td>
                    <td>
                        @if($task['performance_rating'])
                            <div class="rating-stars">
                                @for($i = 1; $i <= 5; $i++)
                                    @if($i <= $task['performance_rating'])
                                        ★
                                    @else
                                        ☆
                                    @endif
                                @endfor
                            </div>
                            <small>({{ $task['performance_rating'] }}/5)</small>
                        @else
                            <small>Not rated</small>
                        @endif
                    </td>
                    <td>
                        <div style="font-size: 9px;">
                            @if($task['performance_notes'])
                                <strong>Performance:</strong> {{ Str::limit($task['performance_notes'], 60) }}<br>
                            @endif
                            @if($task['volunteer_feedback'])
                                <strong>Feedback:</strong> {{ Str::limit($task['volunteer_feedback'], 60) }}
                            @endif
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Summary Statistics -->
        <div style="margin-top: 30px; background-color: #f0fdf4; padding: 15px; border-radius: 5px;">
            <h4 style="color: #10b981; margin-bottom: 10px;">Completion Summary</h4>
            <div style="display: table; width: 100%;">
                <div style="display: table-row;">
                    <div style="display: table-cell; width: 25%; padding: 5px;">
                        <strong>Total Completed:</strong> {{ $tasks->count() }}
                    </div>
                    <div style="display: table-cell; width: 25%; padding: 5px;">
                        <strong>Average Rating:</strong> 
                        @php
                            $ratedTasks = $tasks->filter(function($task) { return $task['performance_rating'] !== null; });
                            $avgRating = $ratedTasks->count() > 0 ? round($ratedTasks->avg('performance_rating'), 1) : 'N/A';
                        @endphp
                        {{ $avgRating }}
                    </div>
                    <div style="display: table-cell; width: 25%; padding: 5px;">
                        <strong>Successfully Completed:</strong> 
                        {{ $tasks->where('task_completed_successfully', true)->count() }}
                    </div>
                    <div style="display: table-cell; width: 25%; padding: 5px;">
                        <strong>With Check-in/out:</strong> 
                        {{ $tasks->filter(function($task) { return $task['checked_in_at'] && $task['checked_out_at']; })->count() }}
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="no-data">
            <p>No tasks were completed during {{ $period }}.</p>
        </div>
    @endif

    <!-- Footer -->
    <div class="footer">
        <p>This report was generated by the Malawi Volunteer Management System (MVMS)</p>
        <p>Report generated on {{ $generated_at->format('F j, Y \a\t g:i A') }}</p>
    </div>
</body>
</html>
