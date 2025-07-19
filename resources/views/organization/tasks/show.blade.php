@extends('layouts.organization')

@section('title', 'Task Details - ' . $task->title)

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-1">{{ $task->title }}</h2>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('organization.dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('opportunities.index') }}">Opportunities</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('organization.opportunities.tasks.index', $opportunity) }}">{{ Str::limit($opportunity->title, 30) }}</a></li>
                            <li class="breadcrumb-item active">{{ Str::limit($task->title, 30) }}</li>
                        </ol>
                    </nav>
                </div>
                <div class="btn-group">
                    <a href="{{ route('organization.opportunities.tasks.index', $opportunity) }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-2"></i>Back to Tasks
                    </a>
                    @if($task->can_assign_volunteers ?? true)
                    <button class="btn btn-success" onclick="showAssignModal()">
                        <i class="bi bi-person-plus me-2"></i>Assign Volunteers
                    </button>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Task Status and Quick Actions -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <div class="d-flex align-items-center">
                                <span class="badge bg-{{ $task->priority === 'urgent' ? 'danger' : ($task->priority === 'high' ? 'warning' : ($task->priority === 'medium' ? 'info' : 'secondary')) }} fs-6 me-3">
                                    {{ ucfirst($task->priority) }} Priority
                                </span>
                                <span class="badge bg-{{ $task->status === 'active' ? 'success' : ($task->status === 'completed' ? 'primary' : 'secondary') }} fs-6">
                                    {{ ucfirst($task->status) }}
                                </span>
                            </div>
                        </div>
                        <div class="col-md-6 text-md-end">
                            <div class="btn-group btn-group-sm">
                                <button class="btn btn-outline-primary" onclick="updateTaskStatus('active')" 
                                        {{ $task->status === 'active' ? 'disabled' : '' }}>
                                    <i class="bi bi-play-circle me-1"></i>Activate
                                </button>
                                <button class="btn btn-outline-warning" onclick="updateTaskStatus('paused')"
                                        {{ $task->status === 'paused' ? 'disabled' : '' }}>
                                    <i class="bi bi-pause-circle me-1"></i>Pause
                                </button>
                                <button class="btn btn-outline-success" onclick="updateTaskStatus('completed')"
                                        {{ $task->status === 'completed' ? 'disabled' : '' }}>
                                    <i class="bi bi-check-circle me-1"></i>Complete
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Assignment Statistics -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card bg-primary text-white h-100">
                <div class="card-body text-center">
                    <i class="bi bi-people display-4 mb-2"></i>
                    <h3 class="mb-1">{{ $assignmentStats['total'] }}</h3>
                    <small>Total Assignments</small>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card bg-warning text-white h-100">
                <div class="card-body text-center">
                    <i class="bi bi-clock display-4 mb-2"></i>
                    <h3 class="mb-1">{{ $assignmentStats['pending'] }}</h3>
                    <small>Pending</small>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card bg-success text-white h-100">
                <div class="card-body text-center">
                    <i class="bi bi-check-circle display-4 mb-2"></i>
                    <h3 class="mb-1">{{ $assignmentStats['accepted'] }}</h3>
                    <small>Accepted</small>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card bg-info text-white h-100">
                <div class="card-body text-center">
                    <i class="bi bi-award display-4 mb-2"></i>
                    <h3 class="mb-1">{{ $assignmentStats['completed'] }}</h3>
                    <small>Completed</small>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Task Details -->
        <div class="col-lg-8 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="card-title mb-0">Task Details</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-sm-3"><strong>Description:</strong></div>
                        <div class="col-sm-9">{{ $task->description }}</div>
                    </div>
                    
                    @if($task->instructions)
                    <div class="row mb-3">
                        <div class="col-sm-3"><strong>Instructions:</strong></div>
                        <div class="col-sm-9">{{ $task->instructions }}</div>
                    </div>
                    @endif

                    <div class="row mb-3">
                        <div class="col-sm-3"><strong>Schedule:</strong></div>
                        <div class="col-sm-9">
                            <div class="d-flex align-items-center mb-1">
                                <i class="bi bi-calendar me-2 text-primary"></i>
                                {{ $task->start_datetime->format('l, F j, Y') }}
                            </div>
                            <div class="d-flex align-items-center">
                                <i class="bi bi-clock me-2 text-primary"></i>
                                {{ $task->start_datetime->format('g:i A') }} - {{ $task->end_datetime->format('g:i A') }}
                                @if($task->duration_hours)
                                    ({{ $task->duration_hours }} hours)
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-sm-3"><strong>Location:</strong></div>
                        <div class="col-sm-9">
                            <div class="d-flex align-items-center mb-1">
                                <i class="bi bi-geo-alt me-2 text-primary"></i>
                                {{ ucfirst(str_replace('_', ' ', $task->location_type)) }}
                            </div>
                            @if($task->location_address)
                            <div class="text-muted">{{ $task->location_address }}</div>
                            @endif
                            @if($task->location_instructions)
                            <small class="text-muted">{{ $task->location_instructions }}</small>
                            @endif
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-sm-3"><strong>Volunteers:</strong></div>
                        <div class="col-sm-9">
                            <div class="d-flex align-items-center">
                                <span class="me-2">{{ $task->volunteers_assigned }}/{{ $task->volunteers_needed }}</span>
                                <div class="progress flex-grow-1 me-2" style="height: 8px;">
                                    <div class="progress-bar" style="width: {{ $task->volunteers_needed > 0 ? ($task->volunteers_assigned / $task->volunteers_needed) * 100 : 0 }}%"></div>
                                </div>
                                <small class="text-muted">
                                    {{ $task->volunteers_needed - $task->volunteers_assigned }} spots remaining
                                </small>
                            </div>
                        </div>
                    </div>

                    @if($task->required_skills)
                    <div class="row mb-3">
                        <div class="col-sm-3"><strong>Required Skills:</strong></div>
                        <div class="col-sm-9">
                            @foreach($task->required_skills as $skill)
                                <span class="badge bg-light text-dark me-1">{{ $skill }}</span>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    @if($task->equipment_needed)
                    <div class="row mb-3">
                        <div class="col-sm-3"><strong>Equipment Needed:</strong></div>
                        <div class="col-sm-9">
                            @foreach($task->equipment_needed as $equipment)
                                <span class="badge bg-info text-white me-1">{{ $equipment }}</span>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    @if($task->special_requirements)
                    <div class="row mb-3">
                        <div class="col-sm-3"><strong>Special Requirements:</strong></div>
                        <div class="col-sm-9">{{ $task->special_requirements }}</div>
                    </div>
                    @endif

                    @if($task->safety_requirements)
                    <div class="row mb-3">
                        <div class="col-sm-3"><strong>Safety Requirements:</strong></div>
                        <div class="col-sm-9">{{ $task->safety_requirements }}</div>
                    </div>
                    @endif

                    <div class="row mb-3">
                        <div class="col-sm-3"><strong>Assignment Type:</strong></div>
                        <div class="col-sm-9">
                            <span class="badge bg-{{ $task->assignment_type === 'auto' ? 'success' : ($task->assignment_type === 'manual' ? 'primary' : 'info') }}">
                                {{ ucfirst(str_replace('_', ' ', $task->assignment_type)) }}
                            </span>
                            @if($task->allow_self_assignment)
                                <span class="badge bg-secondary ms-1">Self-assignment allowed</span>
                            @endif
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-sm-3"><strong>Created:</strong></div>
                        <div class="col-sm-9">
                            {{ $task->created_at->format('M j, Y \a\t g:i A') }}
                            @if($task->creator)
                                by {{ $task->creator->name }}
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Assigned Volunteers -->
        <div class="col-lg-4 mb-4">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Assigned Volunteers</h5>
                    <button class="btn btn-sm btn-primary" onclick="showAssignModal()">
                        <i class="bi bi-plus"></i>
                    </button>
                </div>
                <div class="card-body">
                    @if($task->assignments->count() > 0)
                        @foreach($task->assignments as $assignment)
                        <div class="d-flex align-items-center justify-content-between mb-3 p-2 border rounded">
                            <div class="d-flex align-items-center">
                                <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 40px; height: 40px;">
                                    <i class="bi bi-person text-white"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0">{{ $assignment->volunteer->name }}</h6>
                                    <small class="text-muted">
                                        <span class="badge bg-{{ $assignment->status === 'accepted' ? 'success' : ($assignment->status === 'pending' ? 'warning' : ($assignment->status === 'completed' ? 'primary' : 'danger')) }}">
                                            {{ ucfirst($assignment->status) }}
                                        </span>
                                    </small>
                                </div>
                            </div>
                            <div class="dropdown">
                                <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="dropdown">
                                    <i class="bi bi-three-dots-vertical"></i>
                                </button>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="#" onclick="viewVolunteerDetails({{ $assignment->volunteer->id }})">
                                        <i class="bi bi-eye me-2"></i>View Details
                                    </a></li>
                                    <li><a class="dropdown-item" href="#" onclick="sendMessage({{ $assignment->volunteer->id }})">
                                        <i class="bi bi-envelope me-2"></i>Send Message
                                    </a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item text-danger" href="#" onclick="removeAssignment({{ $assignment->id }})">
                                        <i class="bi bi-trash me-2"></i>Remove
                                    </a></li>
                                </ul>
                            </div>
                        </div>
                        @endforeach
                    @else
                        <div class="text-center py-4">
                            <i class="bi bi-people display-4 text-muted mb-2"></i>
                            <p class="text-muted">No volunteers assigned yet</p>
                            <button class="btn btn-primary btn-sm" onclick="showAssignModal()">
                                <i class="bi bi-person-plus me-1"></i>Assign Volunteers
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Assign Volunteers Modal -->
<div class="modal fade" id="assignModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Assign Volunteers to Task</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('organization.opportunities.tasks.assign', [$opportunity, $task]) }}">
                @csrf
                <div class="modal-body">
                    <div id="assignModalContent">
                        <!-- Content loaded via AJAX -->
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Assign Selected Volunteers</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function showAssignModal() {
    const modal = new bootstrap.Modal(document.getElementById('assignModal'));
    const content = document.getElementById('assignModalContent');
    
    content.innerHTML = '<div class="text-center"><div class="spinner-border"></div></div>';
    modal.show();
    
    // Load available volunteers
    fetch(`/organization/opportunities/{{ $opportunity->id }}/tasks/{{ $task->id }}/volunteers`)
        .then(response => response.text())
        .then(html => {
            content.innerHTML = html;
        })
        .catch(error => {
            content.innerHTML = '<div class="alert alert-danger">Failed to load volunteers</div>';
        });
}

function updateTaskStatus(status) {
    if (confirm(`Are you sure you want to ${status} this task?`)) {
        fetch(`{{ route('organization.opportunities.tasks.updateStatus', [$opportunity, $task]) }}`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ status: status })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Failed to update task status');
            }
        })
        .catch(error => {
            alert('Error updating task status');
        });
    }
}

function removeAssignment(assignmentId) {
    if (confirm('Are you sure you want to remove this volunteer assignment?')) {
        // Implementation for removing assignment
        console.log('Remove assignment:', assignmentId);
    }
}

function viewVolunteerDetails(volunteerId) {
    // Implementation for viewing volunteer details
    console.log('View volunteer:', volunteerId);
}

function sendMessage(volunteerId) {
    // Implementation for sending message
    console.log('Send message to:', volunteerId);
}
</script>
@endpush
