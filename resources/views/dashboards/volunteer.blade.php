@extends('layouts.volunteer')

@section('title', 'Volunteer Dashboard - MVMS')

@section('page-title', 'Volunteer Dashboard')

@section('content')
<div class="container-fluid">
    <!-- Profile Completion Warning -->
    @if(session('profile_incomplete'))
    <div class="row mb-4">
        <div class="col-12">
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                <div class="d-flex align-items-center">
                    <i class="bi bi-exclamation-triangle-fill me-3" style="font-size: 1.5rem;"></i>
                    <div class="flex-grow-1">
                        <h5 class="alert-heading mb-1">Profile Incomplete</h5>
                        <p class="mb-2">
                            Your profile is {{ session('completion_percentage', 0) }}% complete.
                            Complete your profile to access all volunteer opportunities and features.
                        </p>
                        <a href="{{ route('volunteer.profile.create') }}" class="btn btn-warning btn-sm me-2">
                            <i class="bi bi-person-plus me-1"></i>Complete Profile
                        </a>
                        <a href="{{ route('volunteer.profile.quick-complete') }}" class="btn btn-outline-warning btn-sm">
                            <i class="bi bi-lightning me-1"></i>Quick Complete
                        </a>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        </div>
    </div>
    @endif
    <!-- Welcome Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card bg-primary text-white">
                <div class="card-body p-4">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <div class="d-flex align-items-center">
                                <div class="me-3">
                                    <i class="bi bi-grid-3x3-gap" style="font-size: 2.5rem;"></i>
                                </div>
                                <div>
                                    <h2 class="mb-1">MVMS Volunteer Dashboard</h2>
                                    <p class="mb-0 opacity-90">Welcome, {{ Auth::user()->name }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 text-md-end">
                            <div class="text-center">
                                <div class="h4 mb-1" id="system-time">{{ now()->format('H:i') }}</div>
                                <small class="opacity-75">{{ now()->format('M j, Y') }}</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-3">
            <a href="{{ route('volunteer.applications.index') }}" class="text-decoration-none">
                <div class="card h-100 border-0 shadow-sm hover-shadow">
                    <div class="card-body text-center">
                        <div class="text-primary mb-3">
                            <i class="bi bi-file-earmark-text" style="font-size: 2.5rem;"></i>
                        </div>
                        <h4 class="text-primary mb-1">{{ $totalApplications }}</h4>
                        <h6 class="card-title text-muted">Total Applications</h6>
                        <small class="text-muted">{{ $pendingApplications }} pending review</small>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <a href="{{ route('volunteer.applications.index', ['status' => 'accepted']) }}" class="text-decoration-none">
                <div class="card h-100 border-0 shadow-sm hover-shadow">
                    <div class="card-body text-center">
                        <div class="text-success mb-3">
                            <i class="bi bi-check-circle" style="font-size: 2.5rem;"></i>
                        </div>
                        <h4 class="text-success mb-1">{{ $acceptedApplications }}</h4>
                        <h6 class="card-title text-muted">Accepted Applications</h6>
                        <small class="text-muted">Active opportunities</small>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <a href="{{ route('volunteer.opportunities.index') }}" class="text-decoration-none">
                <div class="card h-100 border-0 shadow-sm hover-shadow">
                    <div class="card-body text-center">
                        <div class="text-warning mb-3">
                            <i class="bi bi-clock" style="font-size: 2.5rem;"></i>
                        </div>
                        <h4 class="text-warning mb-1">{{ $volunteerHours }}</h4>
                        <h6 class="card-title text-muted">Hours Volunteered</h6>
                        <small class="text-muted">Total this year</small>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <a href="{{ route('volunteer.opportunities.index') }}" class="text-decoration-none">
                <div class="card h-100 border-0 shadow-sm hover-shadow">
                    <div class="card-body text-center">
                        <div class="text-info mb-3">
                            <i class="bi bi-calendar-event" style="font-size: 2.5rem;"></i>
                        </div>
                        <h4 class="text-info mb-1">{{ $upcomingActivities->count() }}</h4>
                        <h6 class="card-title text-muted">Upcoming Activities</h6>
                        <small class="text-muted">Scheduled</small>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <!-- Main Content Row -->
    <div class="row">
        <!-- Available Opportunities -->
        <div class="col-lg-8 mb-4">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bi bi-search me-2 text-primary"></i>Available Opportunities
                    </h5>
                    <a href="{{ route('volunteer.opportunities.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
                <div class="card-body">
                    @if($availableOpportunities->count() > 0)
                        <div class="row">
                            @foreach($availableOpportunities->take(6) as $opportunity)
                            <div class="col-md-6 mb-3">
                                <div class="border rounded p-3 h-100 hover-shadow" style="transition: all 0.3s ease;">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <h6 class="mb-1">{{ Str::limit($opportunity->title, 30) }}</h6>
                                        <span class="badge bg-primary">{{ ucfirst($opportunity->urgency ?? 'normal') }}</span>
                                    </div>
                                    <p class="text-muted small mb-2">{{ Str::limit($opportunity->description, 80) }}</p>
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <small class="text-muted">
                                            <i class="bi bi-building me-1"></i>{{ $opportunity->organization->name ?? 'Organization' }}
                                        </small>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <small class="text-muted">
                                            <i class="bi bi-geo-alt me-1"></i>{{ $opportunity->district ?? 'Location' }}
                                        </small>
                                        <a href="{{ route('volunteer.opportunities.show', $opportunity) }}" class="btn btn-sm btn-primary">
                                            View Details
                                        </a>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="bi bi-search display-4 text-muted"></i>
                            <h6 class="mt-3 text-muted">No Available Opportunities</h6>
                            <p class="text-muted">Check back later for new volunteer opportunities.</p>
                            <a href="{{ route('volunteer.opportunities.index') }}" class="btn btn-outline-primary">
                                <i class="bi bi-arrow-right me-1"></i>Browse All Opportunities
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Quick Actions & Updates -->
        <div class="col-lg-4 mb-4">
            <div class="card mb-3 border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h6 class="mb-0">
                        <i class="bi bi-lightning-fill me-2 text-warning"></i>Quick Actions
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('volunteer.opportunities.index') }}" class="btn btn-success">
                            <i class="bi bi-search me-2"></i>Browse Opportunities
                        </a>
                        <a href="{{ route('volunteer.profile.show') }}" class="btn btn-outline-success">
                            <i class="bi bi-person-gear me-2"></i>Update Profile
                        </a>
                        <a href="{{ route('volunteer.applications.index') }}" class="btn btn-outline-success">
                            <i class="bi bi-file-earmark-text me-2"></i>My Applications
                        </a>
                        <a href="{{ route('volunteer.opportunities.recommended') }}" class="btn btn-outline-success">
                            <i class="bi bi-star me-2"></i>Recommended
                        </a>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h6 class="mb-0">
                        <i class="bi bi-clock-history me-2 text-primary"></i>Recent Applications
                    </h6>
                </div>
                <div class="card-body">
                    @if($recentApplications->count() > 0)
                        <div class="list-group list-group-flush">
                            @foreach($recentApplications as $application)
                            <div class="list-group-item border-0 px-0 py-2">
                                <div class="d-flex align-items-start">
                                    <div class="flex-shrink-0">
                                        @if($application->status == 'accepted')
                                            <div class="bg-success rounded-circle p-1 text-white" style="width: 8px; height: 8px;"></div>
                                        @elseif($application->status == 'rejected')
                                            <div class="bg-danger rounded-circle p-1 text-white" style="width: 8px; height: 8px;"></div>
                                        @else
                                            <div class="bg-warning rounded-circle p-1 text-white" style="width: 8px; height: 8px;"></div>
                                        @endif
                                    </div>
                                    <div class="flex-grow-1 ms-2">
                                        <small class="text-muted">
                                            Applied to "{{ Str::limit($application->opportunity->title, 30) }}"
                                            @if($application->status == 'accepted')
                                                <span class="text-success">- Accepted</span>
                                            @elseif($application->status == 'rejected')
                                                <span class="text-danger">- Rejected</span>
                                            @else
                                                <span class="text-warning">- Pending</span>
                                            @endif
                                        </small>
                                        <div class="text-muted" style="font-size: 0.75rem;">{{ $application->created_at->diffForHumans() }}</div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        <div class="text-center mt-3">
                            <a href="{{ route('volunteer.applications.index') }}" class="btn btn-sm btn-outline-primary">
                                View All Applications
                            </a>
                        </div>
                    @else
                        <div class="text-center py-3">
                            <i class="bi bi-file-earmark-text display-6 text-muted"></i>
                            <p class="text-muted mt-2 mb-0">No applications yet</p>
                            <small class="text-muted">Start applying to volunteer opportunities!</small>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">
                        <i class="bi bi-activity me-2 text-info"></i>My Applications
                    </h5>
                </div>
                <div class="card-body">
                    @if($recentApplications->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Opportunity</th>
                                        <th>Organization</th>
                                        <th>Applied Date</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentApplications as $application)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                @if($application->status == 'accepted')
                                                    <i class="bi bi-check-circle text-success me-2"></i>
                                                @elseif($application->status == 'rejected')
                                                    <i class="bi bi-x-circle text-danger me-2"></i>
                                                @else
                                                    <i class="bi bi-clock text-warning me-2"></i>
                                                @endif
                                                {{ $application->opportunity->title }}
                                            </div>
                                        </td>
                                        <td>{{ $application->opportunity->organization->name ?? 'Organization' }}</td>
                                        <td>{{ $application->created_at->format('M j, Y') }}</td>
                                        <td>
                                            @if($application->status == 'accepted')
                                                <span class="badge bg-success">Accepted</span>
                                            @elseif($application->status == 'rejected')
                                                <span class="badge bg-danger">Rejected</span>
                                            @else
                                                <span class="badge bg-warning text-dark">Pending</span>
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ route('volunteer.opportunities.show', $application->opportunity) }}" class="btn btn-sm btn-outline-primary">
                                                View Details
                                            </a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="bi bi-file-earmark-text display-4 text-muted"></i>
                            <h6 class="mt-3 text-muted">No Applications Yet</h6>
                            <p class="text-muted">Start applying to volunteer opportunities to see your activity here.</p>
                            <a href="{{ route('volunteer.opportunities.index') }}" class="btn btn-primary">
                                <i class="bi bi-search me-1"></i>Browse Opportunities
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Add any volunteer dashboard specific JavaScript here
    console.log('Volunteer dashboard loaded successfully');

    // Update current time every second
    function updateCurrentTime() {
        const now = new Date();
        const timeString = now.toLocaleTimeString('en-US', {
            hour: '2-digit',
            minute: '2-digit',
            hour12: false
        });
        $('#current-time').text(timeString);
    }

    // Update immediately and then every second
    updateCurrentTime();
    setInterval(updateCurrentTime, 1000);

    // Add hover effects to opportunity cards
    $('.hover-shadow').hover(
        function() {
            $(this).addClass('shadow-sm').css('transform', 'translateY(-2px)');
        },
        function() {
            $(this).removeClass('shadow-sm').css('transform', 'translateY(0)');
        }
    );
});

// Update system time every second
function updateSystemTime() {
    const now = new Date();
    const timeString = now.toLocaleTimeString('en-US', {
        hour: '2-digit',
        minute: '2-digit',
        hour12: false
    });
    const element = document.getElementById('system-time');
    if (element) {
        element.textContent = timeString;
    }
}

// Update immediately and then every second
document.addEventListener('DOMContentLoaded', function() {
    updateSystemTime();
    setInterval(updateSystemTime, 1000);
});
</script>

<style>
.hover-shadow {
    transition: all 0.3s ease;
}

.card {
    transition: all 0.3s ease;
}

.card:hover {
    transform: translateY(-2px);
}

.hover-shadow {
    transition: box-shadow 0.3s ease, transform 0.3s ease;
}

.hover-shadow:hover {
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
    transform: translateY(-2px);
}

.table-hover tbody tr:hover {
    background-color: rgba(0, 123, 255, 0.05);
}
</style>
@endpush
