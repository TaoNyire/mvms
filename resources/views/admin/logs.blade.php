@extends('layouts.admin')

@section('title', 'System Logs - Admin Panel')

@section('page-title', 'System Logs')

@section('breadcrumb')
    <li class="breadcrumb-item active">Logs</li>
@endsection

@section('content')
<div class="container-fluid">


    <!-- Log Statistics -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-bar-chart me-2"></i>Log Statistics for {{ $logDate }}
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-2 col-sm-4 col-6 mb-3">
                            <a href="{{ route('admin.logs', ['date' => $logDate, 'level' => 'all']) }}" class="text-decoration-none">
                                <div class="text-center p-2 border rounded hover-shadow">
                                    <h4 class="text-primary mb-1">{{ $stats['total'] }}</h4>
                                    <small class="text-muted">Total</small>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-2 col-sm-4 col-6 mb-3">
                            <a href="{{ route('admin.logs', ['date' => $logDate, 'level' => 'error']) }}" class="text-decoration-none">
                                <div class="text-center p-2 border rounded hover-shadow">
                                    <h4 class="text-danger mb-1">{{ $stats['error'] }}</h4>
                                    <small class="text-muted">Errors</small>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-2 col-sm-4 col-6 mb-3">
                            <a href="{{ route('admin.logs', ['date' => $logDate, 'level' => 'warning']) }}" class="text-decoration-none">
                                <div class="text-center p-2 border rounded hover-shadow">
                                    <h4 class="text-warning mb-1">{{ $stats['warning'] }}</h4>
                                    <small class="text-muted">Warnings</small>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-2 col-sm-4 col-6 mb-3">
                            <a href="{{ route('admin.logs', ['date' => $logDate, 'level' => 'info']) }}" class="text-decoration-none">
                                <div class="text-center p-2 border rounded hover-shadow">
                                    <h4 class="text-info mb-1">{{ $stats['info'] }}</h4>
                                    <small class="text-muted">Info</small>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-2 col-sm-4 col-6 mb-3">
                            <a href="{{ route('admin.logs', ['date' => $logDate, 'level' => 'debug']) }}" class="text-decoration-none">
                                <div class="text-center p-2 border rounded hover-shadow">
                                    <h4 class="text-secondary mb-1">{{ $stats['debug'] }}</h4>
                                    <small class="text-muted">Debug</small>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-2 col-sm-4 col-6 mb-3">
                            <a href="{{ route('admin.logs', ['date' => $logDate, 'level' => 'critical']) }}" class="text-decoration-none">
                                <div class="text-center p-2 border rounded hover-shadow">
                                    <h4 class="text-dark mb-1">{{ $stats['critical'] + $stats['emergency'] + $stats['alert'] }}</h4>
                                    <small class="text-muted">Critical</small>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Log Filters and Controls -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-funnel me-2"></i>Log Filters
                    </h5>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('admin.logs') }}" class="row g-3">
                        <div class="col-md-3">
                            <label for="date" class="form-label">Date</label>
                            <input type="date" class="form-control" id="date" name="date" value="{{ $logDate }}">
                        </div>
                        <div class="col-md-3">
                            <label for="level" class="form-label">Log Level</label>
                            <select class="form-select" id="level" name="level">
                                <option value="all" {{ $logLevel === 'all' ? 'selected' : '' }}>All Levels</option>
                                <option value="emergency" {{ $logLevel === 'emergency' ? 'selected' : '' }}>Emergency</option>
                                <option value="alert" {{ $logLevel === 'alert' ? 'selected' : '' }}>Alert</option>
                                <option value="critical" {{ $logLevel === 'critical' ? 'selected' : '' }}>Critical</option>
                                <option value="error" {{ $logLevel === 'error' ? 'selected' : '' }}>Error</option>
                                <option value="warning" {{ $logLevel === 'warning' ? 'selected' : '' }}>Warning</option>
                                <option value="notice" {{ $logLevel === 'notice' ? 'selected' : '' }}>Notice</option>
                                <option value="info" {{ $logLevel === 'info' ? 'selected' : '' }}>Info</option>
                                <option value="debug" {{ $logLevel === 'debug' ? 'selected' : '' }}>Debug</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="search" class="form-label">Search</label>
                            <input type="text" class="form-control" id="search" name="search"
                                   value="{{ $search }}" placeholder="Search in log messages...">
                        </div>
                        <div class="col-md-2">
                            <label for="per_page" class="form-label">Per Page</label>
                            <select class="form-select" id="per_page" name="per_page">
                                <option value="25" {{ $perPage == 25 ? 'selected' : '' }}>25</option>
                                <option value="50" {{ $perPage == 50 ? 'selected' : '' }}>50</option>
                                <option value="100" {{ $perPage == 100 ? 'selected' : '' }}>100</option>
                                <option value="200" {{ $perPage == 200 ? 'selected' : '' }}>200</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-search me-1"></i>Filter Logs
                            </button>
                            <a href="{{ route('admin.logs') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-clockwise me-1"></i>Reset
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>



    <!-- Log Entries -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bi bi-list-ul me-2"></i>Log Entries
                        @if($logs->count() > 0)
                            <span class="badge bg-primary ms-2">{{ $logs->count() }}</span>
                        @endif
                    </h5>
                    <div>
                        <button class="btn btn-sm btn-outline-primary" onclick="refreshLogs()">
                            <i class="bi bi-arrow-clockwise me-1"></i>Refresh
                        </button>
                    </div>
                </div>
                <div class="card-body p-0">
                    @forelse($logs as $log)
                    <div class="log-entry border-bottom p-3 log-{{ strtolower($log['level']) }}">
                        <div class="row">
                            <div class="col-md-2 col-sm-3">
                                <small class="text-muted">{{ $log['timestamp'] }}</small>
                            </div>
                            <div class="col-md-1 col-sm-2">
                                <span class="badge bg-{{
                                    match(strtoupper($log['level'])) {
                                        'EMERGENCY', 'ALERT', 'CRITICAL', 'ERROR' => 'danger',
                                        'WARNING' => 'warning',
                                        'NOTICE', 'INFO' => 'info',
                                        'DEBUG' => 'secondary',
                                        default => 'secondary'
                                    }
                                }}">
                                    {{ $log['level'] }}
                                </span>
                            </div>
                            <div class="col-md-9 col-sm-7">
                                <div class="log-message">
                                    {{ Str::limit($log['message'], 200) }}
                                    @if(strlen($log['message']) > 200)
                                        <button class="btn btn-link btn-sm p-0 ms-2" onclick="toggleFullMessage(this)">
                                            Show More
                                        </button>
                                        <div class="full-message d-none">
                                            {{ $log['message'] }}
                                        </div>
                                    @endif
                                </div>
                                @if(!empty($log['context']))
                                <div class="mt-2">
                                    <button class="btn btn-outline-secondary btn-sm" onclick="toggleContext(this)">
                                        <i class="bi bi-code me-1"></i>Show Context
                                    </button>
                                    <div class="context-data d-none mt-2">
                                        <pre class="bg-light p-2 rounded"><code>{{ $log['context'] }}</code></pre>
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-5">
                        <i class="bi bi-file-text" style="font-size: 4rem; color: #dee2e6;"></i>
                        <h4 class="mt-3">No Log Entries Found</h4>
                        <p class="text-muted">
                            @if($search)
                                No log entries match your search criteria for "{{ $search }}".
                            @else
                                No log entries found for {{ $logDate }}.
                            @endif
                        </p>
                        <a href="{{ route('admin.logs') }}" class="btn btn-outline-primary">
                            <i class="bi bi-arrow-clockwise me-1"></i>View Today's Logs
                        </a>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.log-entry {
    transition: background-color 0.2s;
}

.log-entry:hover {
    background-color: #f8f9fa;
}

.log-entry.log-error {
    border-left: 4px solid #dc3545;
}

.log-entry.log-warning {
    border-left: 4px solid #ffc107;
}

.log-entry.log-info {
    border-left: 4px solid #0dcaf0;
}

.log-entry.log-debug {
    border-left: 4px solid #6c757d;
}

.log-entry.log-critical,
.log-entry.log-emergency,
.log-entry.log-alert {
    border-left: 4px solid #dc3545;
    background-color: #fff5f5;
}

.log-message {
    font-family: 'Courier New', monospace;
    font-size: 0.9rem;
    line-height: 1.4;
}

.context-data pre {
    font-size: 0.8rem;
    max-height: 300px;
    overflow-y: auto;
}

.full-message {
    font-family: 'Courier New', monospace;
    font-size: 0.9rem;
    line-height: 1.4;
    background-color: #f8f9fa;
    padding: 10px;
    border-radius: 4px;
    margin-top: 10px;
}

.hover-shadow {
    transition: box-shadow 0.3s ease, transform 0.3s ease;
}

.hover-shadow:hover {
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
    transform: translateY(-2px);
}
</style>
@endpush

@push('scripts')
<script>
function refreshLogs() {
    window.location.reload();
}

function toggleFullMessage(button) {
    const fullMessage = button.nextElementSibling;
    const isHidden = fullMessage.classList.contains('d-none');

    if (isHidden) {
        fullMessage.classList.remove('d-none');
        button.textContent = 'Show Less';
        button.parentElement.querySelector('.log-message').style.display = 'none';
    } else {
        fullMessage.classList.add('d-none');
        button.textContent = 'Show More';
        button.parentElement.querySelector('.log-message').style.display = 'block';
    }
}

function toggleContext(button) {
    const contextData = button.nextElementSibling;
    const isHidden = contextData.classList.contains('d-none');

    if (isHidden) {
        contextData.classList.remove('d-none');
        button.innerHTML = '<i class="bi bi-code me-1"></i>Hide Context';
    } else {
        contextData.classList.add('d-none');
        button.innerHTML = '<i class="bi bi-code me-1"></i>Show Context';
    }
}

// Auto-refresh every 30 seconds if viewing today's logs
@if($logDate === now()->format('Y-m-d'))
setInterval(function() {
    // Only refresh if no search filters are applied
    const urlParams = new URLSearchParams(window.location.search);
    if (!urlParams.has('search') && !urlParams.has('level') || urlParams.get('level') === 'all') {
        refreshLogs();
    }
}, 30000);
@endif

// Keyboard shortcuts
document.addEventListener('keydown', function(e) {
    // Ctrl/Cmd + R for refresh
    if ((e.ctrlKey || e.metaKey) && e.key === 'r') {
        e.preventDefault();
        refreshLogs();
    }

    // Ctrl/Cmd + F for search focus
    if ((e.ctrlKey || e.metaKey) && e.key === 'f') {
        e.preventDefault();
        document.getElementById('search').focus();
    }
});

// Initialize tooltips if Bootstrap is available
document.addEventListener('DOMContentLoaded', function() {
    if (typeof bootstrap !== 'undefined') {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    }
});
</script>
@endpush
