@extends('layouts.organization')

@section('title', 'Organization Calendar - MVMS')

@section('page-title', 'Organization Calendar')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-md-8">
            <h2>
                <i class="bi bi-calendar me-2"></i>Organization Calendar
            </h2>
            <p>Manage your tasks, assignments, and volunteer schedules.</p>
        </div>
        <div class="col-md-4 text-md-end">
            <div class="btn-group me-2" role="group">
                <button type="button" class="btn btn-outline-primary" id="todayBtn">Today</button>
                <button type="button" class="btn btn-outline-primary" id="monthBtn">Month</button>
                <button type="button" class="btn btn-outline-primary" id="weekBtn">Week</button>
                <button type="button" class="btn btn-outline-primary" id="dayBtn">Day</button>
            </div>
            <div class="btn-group" role="group">
                <button type="button" class="btn btn-primary dropdown-toggle" data-bs-toggle="dropdown">
                    <i class="bi bi-plus-circle me-1"></i>Create
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="{{ route('opportunities.create') }}">
                        <i class="bi bi-briefcase me-2"></i>New Opportunity
                    </a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="#" onclick="showCreateTaskModal()">
                        <i class="bi bi-plus-circle me-2"></i>New Task
                    </a></li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Calendar Legend -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="card">
                <div class="card-body py-2">
                    <div class="d-flex flex-wrap align-items-center">
                        <span class="me-3"><strong>Legend:</strong></span>
                        <span class="badge bg-danger me-2">Urgent Tasks</span>
                        <span class="badge bg-warning me-2">High Priority</span>
                        <span class="badge bg-primary me-2">Medium Priority</span>
                        <span class="badge bg-success me-2">Low Priority</span>
                        <span class="badge bg-info me-2">Completed</span>
                        <span class="badge bg-secondary me-2">Cancelled</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Calendar Container -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div id="calendar"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="row mt-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6>Active Tasks</h6>
                            <h3 id="activeTasks">-</h3>
                        </div>
                        <i class="bi bi-list-task" style="font-size: 2rem;"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-dark">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6>Pending Assignments</h6>
                            <h3 id="pendingAssignments">-</h3>
                        </div>
                        <i class="bi bi-clock" style="font-size: 2rem;"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6>Volunteers Assigned</h6>
                            <h3 id="volunteersAssigned">-</h3>
                        </div>
                        <i class="bi bi-people" style="font-size: 2rem;"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6>This Week</h6>
                            <h3 id="thisWeekTasks">-</h3>
                        </div>
                        <i class="bi bi-calendar-week" style="font-size: 2rem;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Task Details Modal -->
<div class="modal fade" id="taskModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="taskModalTitle">Task Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="taskModalBody">
                <!-- Task details will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <div id="taskModalActions">
                    <!-- Action buttons will be added here -->
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Create Task Modal -->
<div class="modal fade" id="createTaskModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Quick Create Task</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="quickTaskForm">
                    <div class="mb-3">
                        <label for="quickOpportunity" class="form-label">Opportunity</label>
                        <select class="form-select" id="quickOpportunity" required>
                            <option value="">Select Opportunity</option>
                            <!-- Options will be loaded dynamically -->
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="quickTitle" class="form-label">Task Title</label>
                        <input type="text" class="form-control" id="quickTitle" required>
                    </div>
                    <div class="mb-3">
                        <label for="quickStart" class="form-label">Start Date & Time</label>
                        <input type="datetime-local" class="form-control" id="quickStart" required>
                    </div>
                    <div class="mb-3">
                        <label for="quickEnd" class="form-label">End Date & Time</label>
                        <input type="datetime-local" class="form-control" id="quickEnd" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="createQuickTask()">Create Task</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css" rel="stylesheet">
<style>
.fc-event {
    border: none !important;
    border-radius: 4px !important;
}

.fc-event-title {
    font-weight: 600;
}

.fc-toolbar-title {
    font-size: 1.5rem !important;
    font-weight: 600;
}

.fc-button {
    background-color: #007bff !important;
    border-color: #007bff !important;
}

.fc-button:hover {
    background-color: #0056b3 !important;
    border-color: #0056b3 !important;
}

.task-urgent {
    background-color: #dc3545 !important;
    color: #fff !important;
}

.task-high {
    background-color: #fd7e14 !important;
    color: #fff !important;
}

.task-medium {
    background-color: #007bff !important;
    color: #fff !important;
}

.task-low {
    background-color: #28a745 !important;
    color: #fff !important;
}

.task-completed {
    background-color: #17a2b8 !important;
    color: #fff !important;
}

.task-cancelled {
    background-color: #6c757d !important;
    color: #fff !important;
}
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const calendarEl = document.getElementById('calendar');
    
    const calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        headerToolbar: {
            left: 'prev,next',
            center: 'title',
            right: ''
        },
        height: 'auto',
        events: function(fetchInfo, successCallback, failureCallback) {
            fetch(`{{ route('organization.calendar.events') }}?start=${fetchInfo.startStr}&end=${fetchInfo.endStr}`)
                .then(response => response.json())
                .then(data => {
                    successCallback(data);
                    updateStats(data);
                })
                .catch(error => {
                    console.error('Error loading calendar events:', error);
                    failureCallback(error);
                });
        },
        eventClick: function(info) {
            showTaskDetails(info.event);
        },
        eventClassNames: function(arg) {
            const event = arg.event;
            const priority = event.extendedProps.priority;
            const status = event.extendedProps.status;
            
            if (status === 'completed') return ['task-completed'];
            if (status === 'cancelled') return ['task-cancelled'];
            
            return [`task-${priority}`];
        },
        dayMaxEvents: 3,
        moreLinkClick: 'popover'
    });
    
    calendar.render();
    
    // View buttons
    document.getElementById('todayBtn').addEventListener('click', () => calendar.today());
    document.getElementById('monthBtn').addEventListener('click', () => calendar.changeView('dayGridMonth'));
    document.getElementById('weekBtn').addEventListener('click', () => calendar.changeView('timeGridWeek'));
    document.getElementById('dayBtn').addEventListener('click', () => calendar.changeView('timeGridDay'));
    
    function showTaskDetails(event) {
        const modal = new bootstrap.Modal(document.getElementById('taskModal'));
        const title = document.getElementById('taskModalTitle');
        const body = document.getElementById('taskModalBody');
        const actions = document.getElementById('taskModalActions');
        
        title.textContent = event.title;
        
        let html = `
            <div class="row">
                <div class="col-md-6">
                    <strong>Start:</strong> ${event.start.toLocaleString()}
                </div>
                <div class="col-md-6">
                    <strong>End:</strong> ${event.end ? event.end.toLocaleString() : 'Not specified'}
                </div>
            </div>
            <div class="row mt-2">
                <div class="col-md-6">
                    <strong>Priority:</strong> ${getPriorityBadge(event.extendedProps.priority)}
                </div>
                <div class="col-md-6">
                    <strong>Status:</strong> ${getStatusBadge(event.extendedProps.status)}
                </div>
            </div>
        `;
        
        if (event.extendedProps.opportunity) {
            html += `<div class="mt-2"><strong>Opportunity:</strong> ${event.extendedProps.opportunity}</div>`;
        }
        
        if (event.extendedProps.location) {
            html += `<div class="mt-2"><strong>Location:</strong> ${event.extendedProps.location}</div>`;
        }
        
        if (event.extendedProps.volunteers_needed) {
            html += `<div class="mt-2"><strong>Volunteers:</strong> ${event.extendedProps.volunteers_assigned}/${event.extendedProps.volunteers_needed}</div>`;
        }
        
        body.innerHTML = html;
        
        // Add action buttons
        actions.innerHTML = '';
        if (event.url) {
            const viewBtn = document.createElement('a');
            viewBtn.href = event.url;
            viewBtn.className = 'btn btn-primary';
            viewBtn.textContent = 'View Details';
            actions.appendChild(viewBtn);
        }
        
        modal.show();
    }
    
    function getPriorityBadge(priority) {
        const badges = {
            'urgent': '<span class="badge bg-danger">Urgent</span>',
            'high': '<span class="badge bg-warning">High</span>',
            'medium': '<span class="badge bg-primary">Medium</span>',
            'low': '<span class="badge bg-success">Low</span>'
        };
        return badges[priority] || `<span class="badge bg-secondary">${priority}</span>`;
    }
    
    function getStatusBadge(status) {
        const badges = {
            'draft': '<span class="badge bg-secondary">Draft</span>',
            'published': '<span class="badge bg-primary">Published</span>',
            'in_progress': '<span class="badge bg-warning">In Progress</span>',
            'completed': '<span class="badge bg-success">Completed</span>',
            'cancelled': '<span class="badge bg-danger">Cancelled</span>'
        };
        return badges[status] || `<span class="badge bg-secondary">${status}</span>`;
    }
    
    function updateStats(events) {
        const activeTasks = events.filter(e => e.extendedProps.status === 'published').length;
        const thisWeek = events.filter(e => {
            const eventDate = new Date(e.start);
            const now = new Date();
            const weekStart = new Date(now.setDate(now.getDate() - now.getDay()));
            const weekEnd = new Date(weekStart);
            weekEnd.setDate(weekEnd.getDate() + 6);
            return eventDate >= weekStart && eventDate <= weekEnd;
        }).length;
        
        document.getElementById('activeTasks').textContent = activeTasks;
        document.getElementById('thisWeekTasks').textContent = thisWeek;
        document.getElementById('pendingAssignments').textContent = '-';
        document.getElementById('volunteersAssigned').textContent = '-';
    }
});

function showCreateTaskModal() {
    const modal = new bootstrap.Modal(document.getElementById('createTaskModal'));
    
    // Load opportunities
    fetch('/opportunities')
        .then(response => response.json())
        .then(data => {
            const select = document.getElementById('quickOpportunity');
            select.innerHTML = '<option value="">Select Opportunity</option>';
            data.forEach(opp => {
                select.innerHTML += `<option value="${opp.id}">${opp.title}</option>`;
            });
        })
        .catch(error => console.error('Error loading opportunities:', error));
    
    modal.show();
}

function createQuickTask() {
    const form = document.getElementById('quickTaskForm');
    const formData = new FormData(form);
    
    // This would submit the form to create a new task
    console.log('Creating task...', Object.fromEntries(formData));
    
    // Close modal and refresh calendar
    bootstrap.Modal.getInstance(document.getElementById('createTaskModal')).hide();
}
</script>
@endpush
