@extends('layouts.volunteer')

@section('title', 'My Tasks - MVMS')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-1">My Tasks</h2>
                    <p class="text-muted mb-0">Manage your assigned volunteer tasks</p>
                </div>
                <button class="btn btn-primary" onclick="alert('Task browsing will be implemented soon!')">
                    <i class="bi bi-search me-2"></i>Browse Available Tasks
                </button>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card bg-primary text-white h-100">
                <div class="card-body text-center">
                    <i class="bi bi-list-task display-4 mb-2"></i>
                    <h3 class="mb-1">{{ $stats['total'] }}</h3>
                    <small>Total Tasks</small>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card bg-warning text-white h-100">
                <div class="card-body text-center">
                    <i class="bi bi-clock display-4 mb-2"></i>
                    <h3 class="mb-1">{{ $stats['pending'] }}</h3>
                    <small>Pending Response</small>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card bg-success text-white h-100">
                <div class="card-body text-center">
                    <i class="bi bi-check-circle display-4 mb-2"></i>
                    <h3 class="mb-1">{{ $stats['accepted'] }}</h3>
                    <small>Accepted</small>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card bg-info text-white h-100">
                <div class="card-body text-center">
                    <i class="bi bi-calendar-event display-4 mb-2"></i>
                    <h3 class="mb-1">{{ $stats['upcoming'] }}</h3>
                    <small>Upcoming</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-4">
                            <label for="status" class="form-label">Status</label>
                            <select name="status" id="status" class="form-select">
                                <option value="all" {{ $status === 'all' ? 'selected' : '' }}>All Status</option>
                                <option value="pending" {{ $status === 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="accepted" {{ $status === 'accepted' ? 'selected' : '' }}>Accepted</option>
                                <option value="completed" {{ $status === 'completed' ? 'selected' : '' }}>Completed</option>
                                <option value="declined" {{ $status === 'declined' ? 'selected' : '' }}>Declined</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="priority" class="form-label">Priority</label>
                            <select name="priority" id="priority" class="form-select">
                                <option value="all" {{ $priority === 'all' ? 'selected' : '' }}>All Priorities</option>
                                <option value="urgent" {{ $priority === 'urgent' ? 'selected' : '' }}>Urgent</option>
                                <option value="high" {{ $priority === 'high' ? 'selected' : '' }}>High</option>
                                <option value="medium" {{ $priority === 'medium' ? 'selected' : '' }}>Medium</option>
                                <option value="low" {{ $priority === 'low' ? 'selected' : '' }}>Low</option>
                            </select>
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="bi bi-funnel me-1"></i>Filter
                            </button>
                            <a href="{{ route('volunteer.tasks.index') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-x-circle me-1"></i>Clear
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Tasks List -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Task Assignments</h5>
                </div>
                <div class="card-body">
                    @if($assignments->count() > 0)
                        @foreach($assignments as $assignment)
                        <div class="card mb-3 border-start border-{{ $assignment->status === 'pending' ? 'warning' : ($assignment->status === 'accepted' ? 'success' : ($assignment->status === 'completed' ? 'primary' : 'danger')) }} border-3">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col-md-8">
                                        <div class="d-flex align-items-start">
                                            <div class="me-3">
                                                <div class="bg-{{ $assignment->task->priority === 'urgent' ? 'danger' : ($assignment->task->priority === 'high' ? 'warning' : ($assignment->task->priority === 'medium' ? 'info' : 'secondary')) }} rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                                    <i class="bi bi-list-task text-white"></i>
                                                </div>
                                            </div>
                                            <div class="flex-grow-1">
                                                <h5 class="mb-1">{{ $assignment->task->title }}</h5>
                                                <p class="text-muted mb-2">{{ Str::limit($assignment->task->description, 100) }}</p>
                                                
                                                <div class="row g-2 mb-2">
                                                    <div class="col-auto">
                                                        <small class="text-muted">
                                                            <i class="bi bi-building me-1"></i>
                                                            {{ $assignment->task->opportunity->organization->name }}
                                                        </small>
                                                    </div>
                                                    <div class="col-auto">
                                                        <small class="text-muted">
                                                            <i class="bi bi-calendar me-1"></i>
                                                            {{ $assignment->scheduled_start->format('M j, Y') }}
                                                        </small>
                                                    </div>
                                                    <div class="col-auto">
                                                        <small class="text-muted">
                                                            <i class="bi bi-clock me-1"></i>
                                                            {{ $assignment->scheduled_start->format('g:i A') }} - {{ $assignment->scheduled_end->format('g:i A') }}
                                                        </small>
                                                    </div>
                                                    <div class="col-auto">
                                                        <small class="text-muted">
                                                            <i class="bi bi-geo-alt me-1"></i>
                                                            {{ ucfirst(str_replace('_', ' ', $assignment->task->location_type)) }}
                                                        </small>
                                                    </div>
                                                </div>

                                                <div class="d-flex align-items-center">
                                                    <span class="badge bg-{{ $assignment->task->priority === 'urgent' ? 'danger' : ($assignment->task->priority === 'high' ? 'warning' : ($assignment->task->priority === 'medium' ? 'info' : 'secondary')) }} me-2">
                                                        {{ ucfirst($assignment->task->priority) }}
                                                    </span>
                                                    <span class="badge bg-{{ $assignment->status === 'pending' ? 'warning' : ($assignment->status === 'accepted' ? 'success' : ($assignment->status === 'completed' ? 'primary' : 'danger')) }}">
                                                        {{ ucfirst($assignment->status) }}
                                                    </span>
                                                    @if($assignment->assignment_method === 'auto_assigned')
                                                        <span class="badge bg-info ms-2">Auto-assigned</span>
                                                    @elseif($assignment->assignment_method === 'self_assigned')
                                                        <span class="badge bg-success ms-2">Self-assigned</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4 text-md-end">
                                        <div class="btn-group-vertical btn-group-sm d-grid gap-1">
                                            <a href="{{ route('volunteer.tasks.show', $assignment) }}" class="btn btn-outline-primary">
                                                <i class="bi bi-eye me-1"></i>View Details
                                            </a>
                                            
                                            @if($assignment->status === 'pending')
                                                <button class="btn btn-success" onclick="acceptTask({{ $assignment->id }})">
                                                    <i class="bi bi-check-circle me-1"></i>Accept
                                                </button>
                                                <button class="btn btn-outline-danger" onclick="showDeclineModal({{ $assignment->id }})">
                                                    <i class="bi bi-x-circle me-1"></i>Decline
                                                </button>
                                            @elseif($assignment->status === 'accepted')
                                                @if(!$assignment->checked_in_at && $assignment->scheduled_start <= now()->addHours(2))
                                                    <button class="btn btn-warning" onclick="showCheckInModal({{ $assignment->id }})">
                                                        <i class="bi bi-box-arrow-in-right me-1"></i>Check In
                                                    </button>
                                                @elseif($assignment->checked_in_at && !$assignment->checked_out_at)
                                                    <button class="btn btn-info" onclick="showCheckOutModal({{ $assignment->id }})">
                                                        <i class="bi bi-box-arrow-left me-1"></i>Check Out
                                                    </button>
                                                @endif
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach

                        <!-- Pagination -->
                        <div class="d-flex justify-content-center mt-3">
                            {{ $assignments->appends(request()->query())->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="bi bi-list-task display-1 text-muted mb-3"></i>
                            <h4>No Task Assignments</h4>
                            <p class="text-muted mb-4">You don't have any task assignments yet. Browse available tasks to get started.</p>
                            <a href="{{ route('volunteer.tasks.browse') }}" class="btn btn-primary">
                                <i class="bi bi-search me-2"></i>Browse Available Tasks
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Decline Task Modal -->
<div class="modal fade" id="declineModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Decline Task Assignment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="declineForm" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="decline_reason" class="form-label">Reason for declining <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="decline_reason" name="decline_reason" rows="3" required 
                                  placeholder="Please provide a reason for declining this task..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Decline Task</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Check In Modal -->
<div class="modal fade" id="checkInModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Check In to Task</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="checkInForm" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="check_in_location" class="form-label">Current Location</label>
                        <input type="text" class="form-control" id="check_in_location" name="check_in_location" 
                               placeholder="e.g., Main entrance, Building A">
                    </div>
                    <div class="mb-3">
                        <label for="check_in_notes" class="form-label">Notes</label>
                        <textarea class="form-control" id="check_in_notes" name="check_in_notes" rows="2" 
                                  placeholder="Any additional notes..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Check In</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Check Out Modal -->
<div class="modal fade" id="checkOutModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Check Out from Task</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="checkOutForm" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Task Completion Status <span class="text-danger">*</span></label>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="task_completed_successfully" id="completed_yes" value="1" required>
                            <label class="form-check-label text-success" for="completed_yes">
                                <i class="bi bi-check-circle me-1"></i>Task completed successfully
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="task_completed_successfully" id="completed_no" value="0" required>
                            <label class="form-check-label text-warning" for="completed_no">
                                <i class="bi bi-exclamation-triangle me-1"></i>Task not completed / Issues encountered
                            </label>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="check_out_location" class="form-label">Current Location</label>
                        <input type="text" class="form-control" id="check_out_location" name="check_out_location" 
                               placeholder="e.g., Main entrance, Building A">
                    </div>
                    <div class="mb-3">
                        <label for="check_out_notes" class="form-label">Check-out Notes</label>
                        <textarea class="form-control" id="check_out_notes" name="check_out_notes" rows="2" 
                                  placeholder="Any notes about your departure..."></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="volunteer_feedback" class="form-label">Task Feedback</label>
                        <textarea class="form-control" id="volunteer_feedback" name="volunteer_feedback" rows="3" 
                                  placeholder="Share your experience with this task..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Check Out</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function acceptTask(assignmentId) {
    if (confirm('Are you sure you want to accept this task assignment?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/volunteer/tasks/${assignmentId}/accept`;
        
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = document.querySelector('meta[name="csrf-token"]').content;
        
        form.appendChild(csrfToken);
        document.body.appendChild(form);
        form.submit();
    }
}

function showDeclineModal(assignmentId) {
    const modal = new bootstrap.Modal(document.getElementById('declineModal'));
    const form = document.getElementById('declineForm');
    form.action = `/volunteer/tasks/${assignmentId}/decline`;
    modal.show();
}

function showCheckInModal(assignmentId) {
    const modal = new bootstrap.Modal(document.getElementById('checkInModal'));
    const form = document.getElementById('checkInForm');
    form.action = `/volunteer/tasks/${assignmentId}/check-in`;
    modal.show();
}

function showCheckOutModal(assignmentId) {
    const modal = new bootstrap.Modal(document.getElementById('checkOutModal'));
    const form = document.getElementById('checkOutForm');
    form.action = `/volunteer/tasks/${assignmentId}/check-out`;
    modal.show();
}

// Handle form submissions with AJAX for better UX
document.getElementById('checkInForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch(this.action, {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('checkInModal')).hide();
            location.reload();
        } else {
            alert('Failed to check in: ' + data.message);
        }
    })
    .catch(error => {
        alert('Error checking in to task');
    });
});

document.getElementById('checkOutForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch(this.action, {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('checkOutModal')).hide();
            location.reload();
        } else {
            alert('Failed to check out: ' + data.message);
        }
    })
    .catch(error => {
        alert('Error checking out from task');
    });
});
</script>
@endpush
