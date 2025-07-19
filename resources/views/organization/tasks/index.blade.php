@extends('layouts.organization')

@section('title', 'Task Management - ' . $opportunity->title)

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-1">Task Management</h2>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('organization.dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('opportunities.index') }}">Opportunities</a></li>
                            <li class="breadcrumb-item">{{ Str::limit($opportunity->title, 30) }}</li>
                            <li class="breadcrumb-item active">Tasks</li>
                        </ol>
                    </nav>
                </div>
                <a href="{{ route('organization.opportunities.tasks.create', $opportunity) }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-2"></i>Create Task
                </a>
            </div>
        </div>
    </div>

    <!-- Success/Error Messages -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <div class="d-flex align-items-center">
                <i class="bi bi-check-circle-fill me-2 fs-5"></i>
                <div>
                    <strong>Success!</strong> {{ session('success') }}
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <div class="d-flex align-items-center">
                <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i>
                <div>
                    <strong>Error!</strong> {{ session('error') }}
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <div class="d-flex align-items-start">
                <i class="bi bi-exclamation-circle-fill me-2 fs-5 mt-1"></i>
                <div>
                    <strong>Please fix the following issues:</strong>
                    <ul class="mb-0 mt-2">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Opportunity Info -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-primary">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h5 class="card-title mb-1">{{ $opportunity->title }}</h5>
                            <p class="text-muted mb-0">{{ Str::limit($opportunity->description, 100) }}</p>
                        </div>
                        <div class="col-md-4 text-md-end">
                            <span class="badge bg-{{ $opportunity->status === 'published' ? 'success' : 'warning' }} fs-6">
                                {{ ucfirst($opportunity->status) }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
            <div class="card bg-primary text-white h-100">
                <div class="card-body text-center">
                    <i class="bi bi-list-task display-4 mb-2"></i>
                    <h3 class="mb-1">{{ $stats['total_tasks'] ?? 0 }}</h3>
                    <small>Total Tasks</small>
                </div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
            <div class="card bg-success text-white h-100">
                <div class="card-body text-center">
                    <i class="bi bi-play-circle display-4 mb-2"></i>
                    <h3 class="mb-1">{{ $stats['active_tasks'] ?? 0 }}</h3>
                    <small>Active Tasks</small>
                </div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
            <div class="card bg-info text-white h-100">
                <div class="card-body text-center">
                    <i class="bi bi-check-circle display-4 mb-2"></i>
                    <h3 class="mb-1">{{ $stats['completed_tasks'] ?? 0 }}</h3>
                    <small>Completed</small>
                </div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
            <div class="card bg-danger text-white h-100">
                <div class="card-body text-center">
                    <i class="bi bi-exclamation-triangle display-4 mb-2"></i>
                    <h3 class="mb-1">{{ $stats['overdue_tasks'] ?? 0 }}</h3>
                    <small>Overdue</small>
                </div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
            <div class="card bg-warning text-white h-100">
                <div class="card-body text-center">
                    <i class="bi bi-people display-4 mb-2"></i>
                    <h3 class="mb-1">{{ $stats['total_assignments'] ?? 0 }}</h3>
                    <small>Assignments</small>
                </div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
            <div class="card bg-secondary text-white h-100">
                <div class="card-body text-center">
                    <i class="bi bi-clock display-4 mb-2"></i>
                    <h3 class="mb-1">{{ $stats['pending_assignments'] ?? 0 }}</h3>
                    <small>Pending</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Tasks Table -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Tasks</h5>
                </div>
                <div class="card-body">
                    @if(isset($tasks) && $tasks->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Task</th>
                                        <th>Schedule</th>
                                        <th>Priority</th>
                                        <th>Volunteers</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($tasks as $task)
                                    <tr>
                                        <td>
                                            <div>
                                                <h6 class="mb-1">{{ $task->title }}</h6>
                                                <small class="text-muted">{{ Str::limit($task->description, 50) }}</small>
                                            </div>
                                        </td>
                                        <td>
                                            <div>
                                                <small class="d-block">
                                                    <i class="bi bi-calendar me-1"></i>
                                                    {{ $task->start_datetime->format('M j, Y') }}
                                                </small>
                                                <small class="text-muted">
                                                    <i class="bi bi-clock me-1"></i>
                                                    {{ $task->start_datetime->format('g:i A') }} - {{ $task->end_datetime->format('g:i A') }}
                                                </small>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $task->priority === 'urgent' ? 'danger' : ($task->priority === 'high' ? 'warning' : ($task->priority === 'medium' ? 'info' : 'secondary')) }}">
                                                {{ ucfirst($task->priority) }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <span class="me-2">{{ $task->volunteers_assigned }}/{{ $task->volunteers_needed }}</span>
                                                <div class="progress flex-grow-1" style="height: 6px;">
                                                    <div class="progress-bar" style="width: {{ $task->volunteers_needed > 0 ? ($task->volunteers_assigned / $task->volunteers_needed) * 100 : 0 }}%"></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $task->status === 'published' ? 'success' : ($task->status === 'completed' ? 'primary' : ($task->status === 'in_progress' ? 'warning' : 'secondary')) }}">
                                                {{ ucfirst(str_replace('_', ' ', $task->status)) }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <button class="btn btn-outline-primary" title="View Details"
                                                        onclick="alert('Task details will be implemented soon!')">
                                                    <i class="bi bi-eye"></i>
                                                </button>
                                                <button class="btn btn-outline-success" title="Assign Volunteers"
                                                        onclick="alert('Volunteer assignment will be implemented soon!')">
                                                    <i class="bi bi-person-plus"></i>
                                                </button>
                                                <button class="btn btn-outline-info" title="Analytics"
                                                        onclick="alert('Task analytics will be implemented soon!')">
                                                    <i class="bi bi-graph-up"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Pagination -->
                        @if($tasks->hasPages())
                            <div class="d-flex justify-content-center mt-4">
                                {{ $tasks->links() }}
                            </div>
                        @endif
                    @else
                        <div class="text-center py-5">
                            <i class="bi bi-list-task display-1 text-muted mb-3"></i>
                            <h4>No Tasks Created Yet</h4>
                            <p class="text-muted mb-4">Create your first task to start assigning volunteers.</p>
                            <a href="{{ route('organization.opportunities.tasks.create', $opportunity) }}" class="btn btn-primary">
                                <i class="bi bi-plus-circle me-2"></i>Create First Task
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modals will be added when functionality is implemented -->
@endsection

@push('scripts')
<script>
// Task management functionality
console.log('Task management page loaded successfully');

// Auto-dismiss success alerts after 5 seconds
document.addEventListener('DOMContentLoaded', function() {
    const successAlerts = document.querySelectorAll('.alert-success');
    successAlerts.forEach(function(alert) {
        setTimeout(function() {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }, 5000); // 5 seconds
    });
});

// Add smooth animations to alerts
document.addEventListener('DOMContentLoaded', function() {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(function(alert) {
        alert.style.animation = 'slideInDown 0.5s ease-out';
    });
});
</script>

<style>
@keyframes slideInDown {
    from {
        transform: translateY(-100%);
        opacity: 0;
    }
    to {
        transform: translateY(0);
        opacity: 1;
    }
}

.alert {
    border-left: 4px solid;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.alert-success {
    border-left-color: #10b981;
    background-color: #f0fdf4;
    border-color: #bbf7d0;
    color: #065f46;
}

.alert-danger {
    border-left-color: #ef4444;
    background-color: #fef2f2;
    border-color: #fecaca;
    color: #991b1b;
}

.alert-warning {
    border-left-color: #f59e0b;
    background-color: #fffbeb;
    border-color: #fed7aa;
    color: #92400e;
}
</style>
@endpush
