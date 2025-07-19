@extends('layouts.volunteer')

@section('title', 'Browse Available Tasks - MVMS')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-1">Browse Available Tasks</h2>
                    <p class="text-muted mb-0">Find and self-assign to tasks that match your skills and availability</p>
                </div>
                <a href="{{ route('volunteer.tasks.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-2"></i>Back to My Tasks
                </a>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-3">
                            <label for="category" class="form-label">Category</label>
                            <select name="category" id="category" class="form-select">
                                <option value="all" {{ $category === 'all' ? 'selected' : '' }}>All Categories</option>
                                <option value="education" {{ $category === 'education' ? 'selected' : '' }}>Education</option>
                                <option value="healthcare" {{ $category === 'healthcare' ? 'selected' : '' }}>Healthcare</option>
                                <option value="environment" {{ $category === 'environment' ? 'selected' : '' }}>Environment</option>
                                <option value="community" {{ $category === 'community' ? 'selected' : '' }}>Community Development</option>
                                <option value="disaster" {{ $category === 'disaster' ? 'selected' : '' }}>Disaster Relief</option>
                                <option value="sports" {{ $category === 'sports' ? 'selected' : '' }}>Sports & Recreation</option>
                                <option value="arts" {{ $category === 'arts' ? 'selected' : '' }}>Arts & Culture</option>
                                <option value="technology" {{ $category === 'technology' ? 'selected' : '' }}>Technology</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="priority" class="form-label">Priority</label>
                            <select name="priority" id="priority" class="form-select">
                                <option value="all" {{ $priority === 'all' ? 'selected' : '' }}>All Priorities</option>
                                <option value="urgent" {{ $priority === 'urgent' ? 'selected' : '' }}>Urgent</option>
                                <option value="high" {{ $priority === 'high' ? 'selected' : '' }}>High</option>
                                <option value="medium" {{ $priority === 'medium' ? 'selected' : '' }}>Medium</option>
                                <option value="low" {{ $priority === 'low' ? 'selected' : '' }}>Low</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="location" class="form-label">Location</label>
                            <select name="location" id="location" class="form-select">
                                <option value="all" {{ $location === 'all' ? 'selected' : '' }}>All Locations</option>
                                <option value="Lilongwe" {{ $location === 'Lilongwe' ? 'selected' : '' }}>Lilongwe</option>
                                <option value="Blantyre" {{ $location === 'Blantyre' ? 'selected' : '' }}>Blantyre</option>
                                <option value="Mzuzu" {{ $location === 'Mzuzu' ? 'selected' : '' }}>Mzuzu</option>
                                <option value="Zomba" {{ $location === 'Zomba' ? 'selected' : '' }}>Zomba</option>
                                <option value="Kasungu" {{ $location === 'Kasungu' ? 'selected' : '' }}>Kasungu</option>
                                <option value="Mangochi" {{ $location === 'Mangochi' ? 'selected' : '' }}>Mangochi</option>
                            </select>
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="bi bi-funnel me-1"></i>Filter
                            </button>
                            <a href="{{ route('volunteer.tasks.browse') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-x-circle me-1"></i>Clear
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Available Tasks -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Available Tasks ({{ $availableTasks->total() }})</h5>
                    <small class="text-muted">Tasks you can self-assign to</small>
                </div>
                <div class="card-body">
                    @if($availableTasks->count() > 0)
                        <div class="row">
                            @foreach($availableTasks as $task)
                            <div class="col-lg-6 col-xl-4 mb-4">
                                <div class="card h-100 border-start border-{{ $task->priority === 'urgent' ? 'danger' : ($task->priority === 'high' ? 'warning' : ($task->priority === 'medium' ? 'info' : 'secondary')) }} border-3">
                                    <div class="card-body">
                                        <!-- Task Header -->
                                        <div class="d-flex justify-content-between align-items-start mb-3">
                                            <div class="flex-grow-1">
                                                <h5 class="card-title mb-1">{{ $task->title }}</h5>
                                                <h6 class="card-subtitle text-muted">{{ $task->opportunity->title }}</h6>
                                            </div>
                                            <span class="badge bg-{{ $task->priority === 'urgent' ? 'danger' : ($task->priority === 'high' ? 'warning' : ($task->priority === 'medium' ? 'info' : 'secondary')) }}">
                                                {{ ucfirst($task->priority) }}
                                            </span>
                                        </div>

                                        <!-- Task Description -->
                                        <p class="card-text text-muted mb-3">{{ Str::limit($task->description, 100) }}</p>

                                        <!-- Task Details -->
                                        <div class="mb-3">
                                            <div class="row g-2 small">
                                                <div class="col-12">
                                                    <i class="bi bi-building me-1 text-primary"></i>
                                                    {{ $task->opportunity->organization->name }}
                                                </div>
                                                <div class="col-12">
                                                    <i class="bi bi-calendar me-1 text-primary"></i>
                                                    {{ $task->start_datetime->format('M j, Y') }}
                                                </div>
                                                <div class="col-12">
                                                    <i class="bi bi-clock me-1 text-primary"></i>
                                                    {{ $task->start_datetime->format('g:i A') }} - {{ $task->end_datetime->format('g:i A') }}
                                                    @if($task->duration_hours)
                                                        ({{ $task->duration_hours }}h)
                                                    @endif
                                                </div>
                                                <div class="col-12">
                                                    <i class="bi bi-geo-alt me-1 text-primary"></i>
                                                    {{ ucfirst(str_replace('_', ' ', $task->location_type)) }}
                                                    @if($task->opportunity->district)
                                                        - {{ $task->opportunity->district }}
                                                    @endif
                                                </div>
                                                <div class="col-12">
                                                    <i class="bi bi-people me-1 text-primary"></i>
                                                    {{ $task->volunteers_assigned }}/{{ $task->volunteers_needed }} volunteers
                                                    <div class="progress mt-1" style="height: 4px;">
                                                        <div class="progress-bar" style="width: {{ ($task->volunteers_assigned / $task->volunteers_needed) * 100 }}%"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Required Skills -->
                                        @if($task->required_skills)
                                        <div class="mb-3">
                                            <small class="text-muted d-block mb-1">Skills Required:</small>
                                            @foreach(array_slice($task->required_skills, 0, 3) as $skill)
                                                <span class="badge bg-light text-dark me-1 mb-1">{{ $skill }}</span>
                                            @endforeach
                                            @if(count($task->required_skills) > 3)
                                                <span class="badge bg-light text-dark">+{{ count($task->required_skills) - 3 }} more</span>
                                            @endif
                                        </div>
                                        @endif

                                        <!-- Time Until Start -->
                                        <div class="mb-3">
                                            @if($task->start_datetime->isPast())
                                                <small class="text-danger">
                                                    <i class="bi bi-exclamation-triangle me-1"></i>
                                                    Started {{ $task->start_datetime->diffForHumans() }}
                                                </small>
                                            @elseif($task->start_datetime->isToday())
                                                <small class="text-warning">
                                                    <i class="bi bi-clock me-1"></i>
                                                    Starts today at {{ $task->start_datetime->format('g:i A') }}
                                                </small>
                                            @elseif($task->start_datetime->isTomorrow())
                                                <small class="text-info">
                                                    <i class="bi bi-clock me-1"></i>
                                                    Starts tomorrow at {{ $task->start_datetime->format('g:i A') }}
                                                </small>
                                            @else
                                                <small class="text-success">
                                                    <i class="bi bi-clock me-1"></i>
                                                    Starts {{ $task->start_datetime->diffForHumans() }}
                                                </small>
                                            @endif
                                        </div>

                                        <!-- Assignment Deadline -->
                                        @if($task->assignment_deadline)
                                        <div class="mb-3">
                                            <small class="text-muted">
                                                <i class="bi bi-hourglass me-1"></i>
                                                Apply by {{ $task->assignment_deadline->format('M j, Y \a\t g:i A') }}
                                            </small>
                                        </div>
                                        @endif
                                    </div>

                                    <!-- Card Footer -->
                                    <div class="card-footer bg-transparent">
                                        <div class="d-grid">
                                            @php
                                                $alreadyAssigned = $task->assignments->where('volunteer_id', Auth::id())->first();
                                            @endphp
                                            
                                            @if($alreadyAssigned)
                                                <button class="btn btn-outline-secondary" disabled>
                                                    <i class="bi bi-check-circle me-1"></i>Already Assigned
                                                </button>
                                            @elseif($task->volunteers_assigned >= $task->volunteers_needed)
                                                <button class="btn btn-outline-secondary" disabled>
                                                    <i class="bi bi-people-fill me-1"></i>Fully Assigned
                                                </button>
                                            @elseif($task->assignment_deadline && $task->assignment_deadline->isPast())
                                                <button class="btn btn-outline-secondary" disabled>
                                                    <i class="bi bi-clock me-1"></i>Deadline Passed
                                                </button>
                                            @else
                                                <button class="btn btn-primary" onclick="selfAssignTask({{ $task->id }}, '{{ $task->title }}')">
                                                    <i class="bi bi-person-plus me-1"></i>Self-Assign
                                                </button>
                                            @endif
                                        </div>
                                        
                                        <div class="mt-2 d-grid">
                                            <button class="btn btn-outline-info btn-sm" onclick="showTaskDetails({{ $task->id }})">
                                                <i class="bi bi-eye me-1"></i>View Details
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>

                        <!-- Pagination -->
                        <div class="d-flex justify-content-center mt-4">
                            {{ $availableTasks->appends(request()->query())->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="bi bi-search display-1 text-muted mb-3"></i>
                            <h4>No Available Tasks Found</h4>
                            <p class="text-muted mb-4">
                                @if($category !== 'all' || $priority !== 'all' || $location !== 'all')
                                    Try adjusting your filters to see more tasks.
                                @else
                                    There are currently no tasks available for self-assignment. Check back later or contact organizations directly.
                                @endif
                            </p>
                            @if($category !== 'all' || $priority !== 'all' || $location !== 'all')
                                <a href="{{ route('volunteer.tasks.browse') }}" class="btn btn-primary">
                                    <i class="bi bi-x-circle me-2"></i>Clear Filters
                                </a>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Task Details Modal -->
<div class="modal fade" id="taskDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Task Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="taskDetailsContent">
                    <!-- Content loaded via AJAX -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="modalSelfAssignBtn" style="display: none;">
                    <i class="bi bi-person-plus me-1"></i>Self-Assign
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Self-Assign Confirmation Modal -->
<div class="modal fade" id="selfAssignModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Self-Assignment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <i class="bi bi-info-circle me-2"></i>
                    <strong>Important:</strong> By self-assigning to this task, you are committing to complete it as scheduled. Please ensure you are available and prepared.
                </div>
                <p>Are you sure you want to self-assign to the task: <strong id="taskTitleConfirm"></strong>?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="selfAssignForm" method="POST" style="display: inline;">
                    @csrf
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle me-1"></i>Confirm Assignment
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function selfAssignTask(taskId, taskTitle) {
    const modal = new bootstrap.Modal(document.getElementById('selfAssignModal'));
    const form = document.getElementById('selfAssignForm');
    const titleElement = document.getElementById('taskTitleConfirm');
    
    form.action = `/volunteer/tasks/tasks/${taskId}/self-assign`;
    titleElement.textContent = taskTitle;
    
    modal.show();
}

function showTaskDetails(taskId) {
    const modal = new bootstrap.Modal(document.getElementById('taskDetailsModal'));
    const content = document.getElementById('taskDetailsContent');
    const assignBtn = document.getElementById('modalSelfAssignBtn');
    
    content.innerHTML = '<div class="text-center"><div class="spinner-border"></div></div>';
    modal.show();
    
    // Load task details via AJAX
    fetch(`/api/tasks/${taskId}/details`)
        .then(response => response.json())
        .then(data => {
            content.innerHTML = `
                <div class="row">
                    <div class="col-md-8">
                        <h5>${data.title}</h5>
                        <p class="text-muted">${data.description}</p>
                        
                        ${data.instructions ? `
                        <div class="alert alert-info">
                            <strong>Instructions:</strong><br>
                            ${data.instructions}
                        </div>
                        ` : ''}
                        
                        <div class="row g-3 mb-3">
                            <div class="col-sm-6">
                                <strong>Schedule:</strong><br>
                                <i class="bi bi-calendar me-1"></i>${data.start_date}<br>
                                <i class="bi bi-clock me-1"></i>${data.start_time} - ${data.end_time}
                            </div>
                            <div class="col-sm-6">
                                <strong>Location:</strong><br>
                                <i class="bi bi-geo-alt me-1"></i>${data.location_type}<br>
                                ${data.location_address || ''}
                            </div>
                        </div>
                        
                        ${data.required_skills && data.required_skills.length > 0 ? `
                        <div class="mb-3">
                            <strong>Required Skills:</strong><br>
                            ${data.required_skills.map(skill => `<span class="badge bg-light text-dark me-1">${skill}</span>`).join('')}
                        </div>
                        ` : ''}
                        
                        ${data.equipment_needed && data.equipment_needed.length > 0 ? `
                        <div class="mb-3">
                            <strong>Equipment Needed:</strong><br>
                            ${data.equipment_needed.map(equipment => `<span class="badge bg-info text-white me-1">${equipment}</span>`).join('')}
                        </div>
                        ` : ''}
                        
                        ${data.special_requirements ? `
                        <div class="alert alert-warning">
                            <strong>Special Requirements:</strong><br>
                            ${data.special_requirements}
                        </div>
                        ` : ''}
                    </div>
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-body">
                                <h6>Organization</h6>
                                <p class="mb-2">${data.organization_name}</p>
                                
                                <h6>Opportunity</h6>
                                <p class="mb-2">${data.opportunity_title}</p>
                                
                                <h6>Volunteers</h6>
                                <div class="d-flex align-items-center mb-2">
                                    <span class="me-2">${data.volunteers_assigned}/${data.volunteers_needed}</span>
                                    <div class="progress flex-grow-1" style="height: 6px;">
                                        <div class="progress-bar" style="width: ${(data.volunteers_assigned / data.volunteers_needed) * 100}%"></div>
                                    </div>
                                </div>
                                
                                <h6>Priority</h6>
                                <span class="badge bg-${data.priority === 'urgent' ? 'danger' : (data.priority === 'high' ? 'warning' : (data.priority === 'medium' ? 'info' : 'secondary'))}">${data.priority}</span>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            // Show self-assign button if available
            if (data.can_self_assign) {
                assignBtn.style.display = 'inline-block';
                assignBtn.onclick = () => {
                    modal.hide();
                    selfAssignTask(taskId, data.title);
                };
            } else {
                assignBtn.style.display = 'none';
            }
        })
        .catch(error => {
            content.innerHTML = '<div class="alert alert-danger">Failed to load task details</div>';
        });
}

// Auto-refresh available tasks every 5 minutes
setInterval(() => {
    if (document.visibilityState === 'visible') {
        location.reload();
    }
}, 300000);
</script>
@endpush
