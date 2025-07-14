@extends('layouts.volunteer')

@section('title', 'My Calendar - MVMS')

@section('page-title', 'My Calendar')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-md-8">
            <h2>
                <i class="bi bi-calendar me-2"></i>My Volunteer Calendar
            </h2>
            <p>View your volunteer assignments, schedules, and availability.</p>
        </div>
        <div class="col-md-4 text-md-end">
            <div class="btn-group" role="group">
                <button type="button" class="btn btn-outline-primary" id="todayBtn">Today</button>
                <button type="button" class="btn btn-outline-primary" id="monthBtn">Month</button>
                <button type="button" class="btn btn-outline-primary" id="weekBtn">Week</button>
                <button type="button" class="btn btn-outline-primary" id="dayBtn">Day</button>
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
                        <span class="badge bg-warning me-2">Pending Assignment</span>
                        <span class="badge bg-success me-2">Accepted Assignment</span>
                        <span class="badge bg-info me-2">Completed Assignment</span>
                        <span class="badge bg-secondary me-2">Personal Event</span>
                        <span class="badge bg-light text-dark me-2">Available Time</span>
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

    <!-- Upcoming Assignments -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-clock me-2"></i>Upcoming Assignments
                    </h5>
                </div>
                <div class="card-body">
                    <div id="upcomingAssignments">
                        <div class="text-center py-3">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Event Details Modal -->
<div class="modal fade" id="eventModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="eventModalTitle">Event Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="eventModalBody">
                <!-- Event details will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <div id="eventModalActions">
                    <!-- Action buttons will be added here based on event type -->
                </div>
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

.fc-daygrid-event {
    margin: 1px 0;
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

.fc-button-active {
    background-color: #0056b3 !important;
    border-color: #004085 !important;
}

.assignment-pending {
    background-color: #ffc107 !important;
    color: #000 !important;
}

.assignment-accepted {
    background-color: #28a745 !important;
    color: #fff !important;
}

.assignment-completed {
    background-color: #17a2b8 !important;
    color: #fff !important;
}

.event-personal {
    background-color: #6c757d !important;
    color: #fff !important;
}

.availability-background {
    background-color: #e9ecef !important;
    opacity: 0.3;
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
            fetch(`{{ route('volunteer.calendar.events') }}?start=${fetchInfo.startStr}&end=${fetchInfo.endStr}`)
                .then(response => response.json())
                .then(data => {
                    successCallback(data);
                    loadUpcomingAssignments();
                })
                .catch(error => {
                    console.error('Error loading calendar events:', error);
                    failureCallback(error);
                });
        },
        eventClick: function(info) {
            showEventDetails(info.event);
        },
        eventClassNames: function(arg) {
            const event = arg.event;
            const type = event.extendedProps.type || 'assignment';
            const status = event.extendedProps.status;
            
            if (type === 'assignment') {
                return [`assignment-${status}`];
            } else if (type === 'availability') {
                return ['availability-background'];
            } else {
                return ['event-personal'];
            }
        },
        dayMaxEvents: 3,
        moreLinkClick: 'popover',
        eventDisplay: 'block'
    });
    
    calendar.render();
    
    // View buttons
    document.getElementById('todayBtn').addEventListener('click', function() {
        calendar.today();
    });
    
    document.getElementById('monthBtn').addEventListener('click', function() {
        calendar.changeView('dayGridMonth');
    });
    
    document.getElementById('weekBtn').addEventListener('click', function() {
        calendar.changeView('timeGridWeek');
    });
    
    document.getElementById('dayBtn').addEventListener('click', function() {
        calendar.changeView('timeGridDay');
    });
    
    function showEventDetails(event) {
        const modal = new bootstrap.Modal(document.getElementById('eventModal'));
        const title = document.getElementById('eventModalTitle');
        const body = document.getElementById('eventModalBody');
        const actions = document.getElementById('eventModalActions');
        
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
        `;
        
        if (event.extendedProps.description) {
            html += `<div class="mt-3"><strong>Description:</strong><br>${event.extendedProps.description}</div>`;
        }
        
        if (event.extendedProps.location) {
            html += `<div class="mt-2"><strong>Location:</strong> ${event.extendedProps.location}</div>`;
        }
        
        if (event.extendedProps.opportunity) {
            html += `<div class="mt-2"><strong>Opportunity:</strong> ${event.extendedProps.opportunity}</div>`;
        }
        
        if (event.extendedProps.status) {
            const statusBadge = getStatusBadge(event.extendedProps.status);
            html += `<div class="mt-2"><strong>Status:</strong> ${statusBadge}</div>`;
        }
        
        body.innerHTML = html;
        
        // Add action buttons based on event type and status
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
    
    function getStatusBadge(status) {
        const badges = {
            'pending': '<span class="badge bg-warning">Pending Response</span>',
            'accepted': '<span class="badge bg-success">Accepted</span>',
            'completed': '<span class="badge bg-info">Completed</span>',
            'declined': '<span class="badge bg-danger">Declined</span>',
            'cancelled': '<span class="badge bg-secondary">Cancelled</span>'
        };
        return badges[status] || `<span class="badge bg-secondary">${status}</span>`;
    }
    
    function loadUpcomingAssignments() {
        const container = document.getElementById('upcomingAssignments');
        
        // This would fetch upcoming assignments from the server
        // For now, we'll show a placeholder
        container.innerHTML = `
            <div class="text-center text-muted py-3">
                <i class="bi bi-calendar-check" style="font-size: 2rem;"></i>
                <p class="mt-2">No upcoming assignments</p>
                <a href="{{ route('volunteer.opportunities.index') }}" class="btn btn-primary btn-sm">
                    <i class="bi bi-search me-1"></i>Find Opportunities
                </a>
            </div>
        `;
    }
});
</script>
@endpush
