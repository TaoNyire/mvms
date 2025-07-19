@extends('layouts.volunteer')

@section('title', 'Task Details - ' . $assignment->task->title)

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-1">{{ $assignment->task->title }}</h2>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('volunteer.dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('volunteer.tasks.index') }}">My Tasks</a></li>
                            <li class="breadcrumb-item active">{{ Str::limit($assignment->task->title, 30) }}</li>
                        </ol>
                    </nav>
                </div>
                <a href="{{ route('volunteer.tasks.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-2"></i>Back to Tasks
                </a>
            </div>
        </div>
    </div>

    <!-- Assignment Status Card -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-{{ $assignment->status === 'pending' ? 'warning' : ($assignment->status === 'accepted' ? 'success' : ($assignment->status === 'completed' ? 'primary' : 'danger')) }}">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <div class="d-flex align-items-center">
                                <div class="bg-{{ $assignment->status === 'pending' ? 'warning' : ($assignment->status === 'accepted' ? 'success' : ($assignment->status === 'completed' ? 'primary' : 'danger')) }} rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 60px; height: 60px;">
                                    <i class="bi bi-{{ $assignment->status === 'pending' ? 'clock' : ($assignment->status === 'accepted' ? 'check-circle' : ($assignment->status === 'completed' ? 'award' : 'x-circle')) }} text-white fs-3"></i>
                                </div>
                                <div>
                                    <h5 class="mb-1">Assignment {{ ucfirst($assignment->status) }}</h5>
                                    <p class="text-muted mb-0">
                                        @if($assignment->status === 'pending')
                                            Please respond to this assignment
                                        @elseif($assignment->status === 'accepted')
                                            You have accepted this task
                                        @elseif($assignment->status === 'completed')
                                            Task completed successfully
                                        @else
                                            Assignment declined
                                        @endif
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 text-md-end">
                            @if($assignment->status === 'pending')
                                <button class="btn btn-success me-2" onclick="acceptTask()">
                                    <i class="bi bi-check-circle me-1"></i>Accept Task
                                </button>
                                <button class="btn btn-outline-danger" onclick="showDeclineModal()">
                                    <i class="bi bi-x-circle me-1"></i>Decline
                                </button>
                            @elseif($assignment->status === 'accepted')
                                @if(!$assignment->checked_in_at && $assignment->scheduled_start <= now()->addHours(2))
                                    <button class="btn btn-warning" onclick="showCheckInModal()">
                                        <i class="bi bi-box-arrow-in-right me-1"></i>Check In
                                    </button>
                                @elseif($assignment->checked_in_at && !$assignment->checked_out_at)
                                    <button class="btn btn-info" onclick="showCheckOutModal()">
                                        <i class="bi bi-box-arrow-left me-1"></i>Check Out
                                    </button>
                                @endif
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Task Details -->
        <div class="col-lg-8 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="card-title mb-0">Task Information</h5>
                </div>
                <div class="card-body">
                    <!-- Basic Info -->
                    <div class="row mb-4">
                        <div class="col-sm-3"><strong>Description:</strong></div>
                        <div class="col-sm-9">{{ $assignment->task->description }}</div>
                    </div>

                    @if($assignment->task->instructions)
                    <div class="row mb-4">
                        <div class="col-sm-3"><strong>Instructions:</strong></div>
                        <div class="col-sm-9">
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle me-2"></i>
                                {{ $assignment->task->instructions }}
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Schedule -->
                    <div class="row mb-4">
                        <div class="col-sm-3"><strong>Schedule:</strong></div>
                        <div class="col-sm-9">
                            <div class="d-flex align-items-center mb-2">
                                <i class="bi bi-calendar me-2 text-primary"></i>
                                <strong>{{ $assignment->scheduled_start->format('l, F j, Y') }}</strong>
                            </div>
                            <div class="d-flex align-items-center mb-2">
                                <i class="bi bi-clock me-2 text-primary"></i>
                                {{ $assignment->scheduled_start->format('g:i A') }} - {{ $assignment->scheduled_end->format('g:i A') }}
                                @if($assignment->task->duration_hours)
                                    <span class="badge bg-light text-dark ms-2">{{ $assignment->task->duration_hours }} hours</span>
                                @endif
                            </div>
                            @if($assignment->scheduled_start->isPast())
                                <small class="text-danger">
                                    <i class="bi bi-exclamation-triangle me-1"></i>
                                    This task was scheduled {{ $assignment->scheduled_start->diffForHumans() }}
                                </small>
                            @else
                                <small class="text-success">
                                    <i class="bi bi-clock me-1"></i>
                                    Starts {{ $assignment->scheduled_start->diffForHumans() }}
                                </small>
                            @endif
                        </div>
                    </div>

                    <!-- Location -->
                    <div class="row mb-4">
                        <div class="col-sm-3"><strong>Location:</strong></div>
                        <div class="col-sm-9">
                            <div class="d-flex align-items-center mb-1">
                                <i class="bi bi-geo-alt me-2 text-primary"></i>
                                <strong>{{ ucfirst(str_replace('_', ' ', $assignment->task->location_type)) }}</strong>
                            </div>
                            @if($assignment->task->location_address)
                            <div class="text-muted mb-1">{{ $assignment->task->location_address }}</div>
                            @endif
                            @if($assignment->task->location_instructions)
                            <div class="alert alert-warning">
                                <i class="bi bi-info-circle me-2"></i>
                                <strong>Location Instructions:</strong> {{ $assignment->task->location_instructions }}
                            </div>
                            @endif
                        </div>
                    </div>

                    <!-- Priority -->
                    <div class="row mb-4">
                        <div class="col-sm-3"><strong>Priority:</strong></div>
                        <div class="col-sm-9">
                            <span class="badge bg-{{ $assignment->task->priority === 'urgent' ? 'danger' : ($assignment->task->priority === 'high' ? 'warning' : ($assignment->task->priority === 'medium' ? 'info' : 'secondary')) }} fs-6">
                                {{ ucfirst($assignment->task->priority) }} Priority
                            </span>
                        </div>
                    </div>

                    <!-- Skills Required -->
                    @if($assignment->task->required_skills)
                    <div class="row mb-4">
                        <div class="col-sm-3"><strong>Skills Required:</strong></div>
                        <div class="col-sm-9">
                            @foreach($assignment->task->required_skills as $skill)
                                <span class="badge bg-light text-dark me-1 mb-1">{{ $skill }}</span>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    <!-- Equipment Needed -->
                    @if($assignment->task->equipment_needed)
                    <div class="row mb-4">
                        <div class="col-sm-3"><strong>Equipment to Bring:</strong></div>
                        <div class="col-sm-9">
                            @foreach($assignment->task->equipment_needed as $equipment)
                                <span class="badge bg-info text-white me-1 mb-1">{{ $equipment }}</span>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    <!-- Special Requirements -->
                    @if($assignment->task->special_requirements)
                    <div class="row mb-4">
                        <div class="col-sm-3"><strong>Special Requirements:</strong></div>
                        <div class="col-sm-9">
                            <div class="alert alert-warning">
                                <i class="bi bi-exclamation-triangle me-2"></i>
                                {{ $assignment->task->special_requirements }}
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Safety Requirements -->
                    @if($assignment->task->safety_requirements)
                    <div class="row mb-4">
                        <div class="col-sm-3"><strong>Safety Requirements:</strong></div>
                        <div class="col-sm-9">
                            <div class="alert alert-danger">
                                <i class="bi bi-shield-exclamation me-2"></i>
                                {{ $assignment->task->safety_requirements }}
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Assignment Details -->
                    <div class="row mb-4">
                        <div class="col-sm-3"><strong>Assignment Method:</strong></div>
                        <div class="col-sm-9">
                            <span class="badge bg-{{ $assignment->assignment_method === 'auto_assigned' ? 'info' : ($assignment->assignment_method === 'self_assigned' ? 'success' : 'primary') }}">
                                {{ ucfirst(str_replace('_', ' ', $assignment->assignment_method)) }}
                            </span>
                        </div>
                    </div>

                    @if($assignment->assignment_notes)
                    <div class="row mb-4">
                        <div class="col-sm-3"><strong>Assignment Notes:</strong></div>
                        <div class="col-sm-9">{{ $assignment->assignment_notes }}</div>
                    </div>
                    @endif

                    <!-- Completion Checklist -->
                    @if($assignment->task->completion_checklist)
                    <div class="row">
                        <div class="col-sm-3"><strong>Completion Checklist:</strong></div>
                        <div class="col-sm-9">
                            <ul class="list-group list-group-flush">
                                @foreach($assignment->task->completion_checklist as $item)
                                <li class="list-group-item px-0">
                                    <i class="bi bi-check-square me-2 text-success"></i>
                                    {{ $item }}
                                </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Organization & Assignment Info -->
        <div class="col-lg-4 mb-4">
            <!-- Organization Info -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Organization</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px;">
                            <i class="bi bi-building text-white"></i>
                        </div>
                        <div>
                            <h6 class="mb-0">{{ $assignment->task->opportunity->organization->name }}</h6>
                            <small class="text-muted">{{ $assignment->task->opportunity->title }}</small>
                        </div>
                    </div>
                    
                    @if($assignment->task->opportunity->contact_person)
                    <div class="mb-2">
                        <strong>Contact Person:</strong><br>
                        <span class="text-muted">{{ $assignment->task->opportunity->contact_person }}</span>
                    </div>
                    @endif
                    
                    @if($assignment->task->opportunity->contact_phone)
                    <div class="mb-2">
                        <strong>Phone:</strong><br>
                        <a href="tel:{{ $assignment->task->opportunity->contact_phone }}" class="text-decoration-none">
                            <i class="bi bi-telephone me-1"></i>{{ $assignment->task->opportunity->contact_phone }}
                        </a>
                    </div>
                    @endif
                    
                    @if($assignment->task->opportunity->contact_email)
                    <div class="mb-2">
                        <strong>Email:</strong><br>
                        <a href="mailto:{{ $assignment->task->opportunity->contact_email }}" class="text-decoration-none">
                            <i class="bi bi-envelope me-1"></i>{{ $assignment->task->opportunity->contact_email }}
                        </a>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Assignment Timeline -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Assignment Timeline</h5>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        <div class="timeline-item">
                            <div class="timeline-marker bg-primary"></div>
                            <div class="timeline-content">
                                <h6 class="mb-1">Task Assigned</h6>
                                <small class="text-muted">{{ $assignment->assigned_at->format('M j, Y \a\t g:i A') }}</small>
                                @if($assignment->assignedBy)
                                    <br><small class="text-muted">by {{ $assignment->assignedBy->name }}</small>
                                @endif
                            </div>
                        </div>

                        @if($assignment->responded_at)
                        <div class="timeline-item">
                            <div class="timeline-marker bg-{{ $assignment->status === 'accepted' ? 'success' : 'danger' }}"></div>
                            <div class="timeline-content">
                                <h6 class="mb-1">Response Given</h6>
                                <small class="text-muted">{{ $assignment->responded_at->format('M j, Y \a\t g:i A') }}</small>
                                <br><small class="text-{{ $assignment->status === 'accepted' ? 'success' : 'danger' }}">
                                    {{ ucfirst($assignment->status) }}
                                </small>
                            </div>
                        </div>
                        @endif

                        @if($assignment->checked_in_at)
                        <div class="timeline-item">
                            <div class="timeline-marker bg-warning"></div>
                            <div class="timeline-content">
                                <h6 class="mb-1">Checked In</h6>
                                <small class="text-muted">{{ $assignment->checked_in_at->format('M j, Y \a\t g:i A') }}</small>
                                @if($assignment->check_in_location)
                                    <br><small class="text-muted">at {{ $assignment->check_in_location }}</small>
                                @endif
                            </div>
                        </div>
                        @endif

                        @if($assignment->checked_out_at)
                        <div class="timeline-item">
                            <div class="timeline-marker bg-info"></div>
                            <div class="timeline-content">
                                <h6 class="mb-1">Checked Out</h6>
                                <small class="text-muted">{{ $assignment->checked_out_at->format('M j, Y \a\t g:i A') }}</small>
                                @if($assignment->check_out_location)
                                    <br><small class="text-muted">at {{ $assignment->check_out_location }}</small>
                                @endif
                            </div>
                        </div>
                        @endif

                        @if($assignment->completed_at)
                        <div class="timeline-item">
                            <div class="timeline-marker bg-success"></div>
                            <div class="timeline-content">
                                <h6 class="mb-1">Task Completed</h6>
                                <small class="text-muted">{{ $assignment->completed_at->format('M j, Y \a\t g:i A') }}</small>
                            </div>
                        </div>
                        @endif
                    </div>
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
            <form method="POST" action="{{ route('volunteer.tasks.decline', $assignment) }}">
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
            <form method="POST" action="{{ route('volunteer.tasks.checkIn', $assignment) }}">
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
            <form method="POST" action="{{ route('volunteer.tasks.checkOut', $assignment) }}">
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
function acceptTask() {
    if (confirm('Are you sure you want to accept this task assignment?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ route("volunteer.tasks.accept", $assignment) }}';
        
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = document.querySelector('meta[name="csrf-token"]').content;
        
        form.appendChild(csrfToken);
        document.body.appendChild(form);
        form.submit();
    }
}

function showDeclineModal() {
    const modal = new bootstrap.Modal(document.getElementById('declineModal'));
    modal.show();
}

function showCheckInModal() {
    const modal = new bootstrap.Modal(document.getElementById('checkInModal'));
    modal.show();
}

function showCheckOutModal() {
    const modal = new bootstrap.Modal(document.getElementById('checkOutModal'));
    modal.show();
}
</script>

<style>
.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 15px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #dee2e6;
}

.timeline-item {
    position: relative;
    margin-bottom: 20px;
}

.timeline-marker {
    position: absolute;
    left: -22px;
    top: 5px;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    border: 2px solid #fff;
    box-shadow: 0 0 0 2px #dee2e6;
}

.timeline-content {
    background: #f8f9fa;
    padding: 10px 15px;
    border-radius: 8px;
    border-left: 3px solid #007bff;
}
</style>
@endpush
