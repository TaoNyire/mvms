@extends('layouts.organization')

@section('title', 'Organization Dashboard - MVMS')

@section('page-title', 'Organization Dashboard')

@section('content')
<div class="container-fluid">
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
                                    <h2 class="mb-1">MVMS Organization Dashboard</h2>
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
            <a href="{{ route('opportunities.index') }}" class="text-decoration-none">
                <div class="card h-100 hover-shadow">
                    <div class="card-body text-center">
                        <div class="text-primary mb-3">
                            <i class="bi bi-briefcase" style="font-size: 2.5rem;"></i>
                        </div>
                        <h4 class="text-primary mb-1">{{ $publishedOpportunities }}</h4>
                        <h6 class="card-title text-muted">Published Opportunities</h6>
                        <small class="text-muted">{{ $draftOpportunities }} drafts</small>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <a href="{{ route('organization.applications.index', ['status' => 'accepted']) }}" class="text-decoration-none">
                <div class="card h-100 hover-shadow">
                    <div class="card-body text-center">
                        <div class="text-success mb-3">
                            <i class="bi bi-people" style="font-size: 2.5rem;"></i>
                        </div>
                        <h4 class="text-success mb-1">{{ $volunteerCount }}</h4>
                        <h6 class="card-title text-muted">Active Volunteers</h6>
                        <small class="text-muted">{{ $acceptedApplications }} accepted</small>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <a href="{{ route('organization.applications.index', ['status' => 'pending']) }}" class="text-decoration-none">
                <div class="card h-100 hover-shadow">
                    <div class="card-body text-center">
                        <div class="text-warning mb-3">
                            <i class="bi bi-file-earmark-text" style="font-size: 2.5rem;"></i>
                        </div>
                        <h4 class="text-warning mb-1">{{ $pendingApplications }}</h4>
                        <h6 class="card-title text-muted">Pending Applications</h6>
                        <small class="text-muted">Needs review</small>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <a href="{{ route('organization.applications.index') }}" class="text-decoration-none">
                <div class="card h-100 hover-shadow">
                    <div class="card-body text-center">
                        <div class="text-info mb-3">
                            <i class="bi bi-check-circle" style="font-size: 2.5rem;"></i>
                        </div>
                        <h4 class="text-info mb-1">{{ $totalApplications }}</h4>
                        <h6 class="card-title text-muted">Total Applications</h6>
                        <small class="text-muted">All time</small>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <!-- Main Content Row -->
    <div class="row">
        <!-- Recent Applications -->
        <div class="col-lg-8 mb-4">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bi bi-file-earmark-text me-2 text-primary"></i>Recent Applications
                    </h5>
                    <a href="{{ route('organization.applications.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Volunteer</th>
                                    <th>Opportunity</th>
                                    <th>Applied</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if($recentApplications->count() > 0)
                                    @foreach($recentApplications as $application)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="bg-primary rounded-circle text-white d-flex align-items-center justify-content-center me-2" style="width: 32px; height: 32px; font-size: 0.8rem;">
                                                    {{ strtoupper(substr($application->volunteer->name ?? 'V', 0, 1) . substr(explode(' ', $application->volunteer->name ?? 'V')[1] ?? '', 0, 1)) }}
                                                </div>
                                                <div>
                                                    <div class="fw-semibold">{{ $application->volunteer->name ?? 'Volunteer' }}</div>
                                                    <small class="text-muted">{{ $application->volunteer->email ?? 'No email' }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>{{ Str::limit($application->opportunity->title, 30) }}</td>
                                        <td>{{ $application->created_at->format('M j, H:i') }}</td>
                                        <td>
                                            @if($application->status == 'pending')
                                                <span class="badge bg-warning text-dark">Pending</span>
                                            @elseif($application->status == 'accepted')
                                                <span class="badge bg-success">Accepted</span>
                                            @else
                                                <span class="badge bg-danger">Rejected</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($application->status == 'pending')
                                                <div class="btn-group btn-group-sm">
                                                    <form action="{{ route('applications.accept', $application) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="btn btn-outline-success">Accept</button>
                                                    </form>
                                                    <form action="{{ route('applications.reject', $application) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="btn btn-outline-danger">Reject</button>
                                                    </form>
                                                </div>
                                            @else
                                                <a href="{{ route('applications.show', $application) }}" class="btn btn-sm btn-outline-primary">View Details</a>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="5" class="text-center py-4">
                                            <i class="bi bi-file-earmark-text display-4 text-muted"></i>
                                            <p class="mt-2 mb-0 text-muted">No applications yet</p>
                                            <small class="text-muted">Applications will appear here when volunteers apply to your opportunities</small>
                                        </td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions & Notifications -->
        <div class="col-lg-4 mb-4">
            <div class="card mb-3">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="bi bi-lightning-fill me-2 text-warning"></i>Quick Actions
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('opportunities.create') }}" class="btn btn-primary">
                            <i class="bi bi-plus-circle me-2"></i>Post New Opportunity
                        </a>
                        <a href="{{ route('organization.applications.index') }}" class="btn btn-outline-primary">
                            <i class="bi bi-people me-2"></i>Manage Applications
                        </a>
                        <a href="{{ route('organization.profile.show') }}" class="btn btn-outline-primary">
                            <i class="bi bi-building-gear me-2"></i>Update Profile
                        </a>
                        <a href="{{ route('organization.calendar') }}" class="btn btn-outline-primary">
                            <i class="bi bi-calendar-event me-2"></i>View Calendar
                        </a>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">
                        <i class="bi bi-bell me-2 text-primary"></i>Recent Notifications
                    </h6>
                    <a href="{{ route('notifications.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        <div class="list-group-item border-0 px-0 py-2">
                            <div class="d-flex align-items-start">
                                <div class="flex-shrink-0">
                                    <div class="bg-success rounded-circle p-1 text-white" style="width: 8px; height: 8px;"></div>
                                </div>
                                <div class="flex-grow-1 ms-2">
                                    <small class="text-muted">New volunteer application received for "Community Garden"</small>
                                    <div class="text-muted" style="font-size: 0.75rem;">30 minutes ago</div>
                                </div>
                            </div>
                        </div>
                        <div class="list-group-item border-0 px-0 py-2">
                            <div class="d-flex align-items-start">
                                <div class="flex-shrink-0">
                                    <div class="bg-primary rounded-circle p-1 text-white" style="width: 8px; height: 8px;"></div>
                                </div>
                                <div class="flex-grow-1 ms-2">
                                    <small class="text-muted">Volunteer completed task: "Tree Planting Event"</small>
                                    <div class="text-muted" style="font-size: 0.75rem;">2 hours ago</div>
                                </div>
                            </div>
                        </div>
                        <div class="list-group-item border-0 px-0 py-2">
                            <div class="d-flex align-items-start">
                                <div class="flex-shrink-0">
                                    <div class="bg-warning rounded-circle p-1 text-white" style="width: 8px; height: 8px;"></div>
                                </div>
                                <div class="flex-grow-1 ms-2">
                                    <small class="text-muted">Opportunity "Health Workshop" expires in 3 days</small>
                                    <div class="text-muted" style="font-size: 0.75rem;">1 day ago</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Active Opportunities -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bi bi-briefcase me-2 text-primary"></i>Active Opportunities
                    </h5>
                    <a href="{{ route('opportunities.create') }}" class="btn btn-primary">
                        <i class="bi bi-plus-circle me-1"></i>Add New
                    </a>
                </div>
                <div class="card-body">
                    @if($recentOpportunities->count() > 0)
                        <div class="row">
                            @foreach($recentOpportunities as $opportunity)
                            <div class="col-lg-4 col-md-6 mb-3">
                                <div class="card border hover-shadow" style="transition: all 0.3s ease;">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <h6 class="card-title mb-1">{{ Str::limit($opportunity->title, 25) }}</h6>
                                            @if($opportunity->status == 'published')
                                                <span class="badge bg-success">Published</span>
                                            @elseif($opportunity->status == 'draft')
                                                <span class="badge bg-secondary">Draft</span>
                                            @else
                                                <span class="badge bg-info">{{ ucfirst($opportunity->status) }}</span>
                                            @endif
                                        </div>
                                        <p class="card-text text-muted small">{{ Str::limit($opportunity->description, 60) }}</p>
                                        <div class="row text-center mb-2">
                                            <div class="col-4">
                                                <div class="fw-bold text-primary">{{ $opportunity->applications()->count() }}</div>
                                                <small class="text-muted">Applied</small>
                                            </div>
                                            <div class="col-4">
                                                <div class="fw-bold text-success">{{ $opportunity->applications()->where('status', 'accepted')->count() }}</div>
                                                <small class="text-muted">Accepted</small>
                                            </div>
                                            <div class="col-4">
                                                <div class="fw-bold text-info">{{ $opportunity->volunteers_needed ?? 0 }}</div>
                                                <small class="text-muted">Needed</small>
                                            </div>
                                        </div>
                                        <div class="d-flex justify-content-between">
                                            <small class="text-muted">
                                                <i class="bi bi-calendar me-1"></i>{{ $opportunity->start_date ? $opportunity->start_date->format('M j') : 'TBD' }}
                                            </small>
                                            <a href="{{ route('opportunities.show', $opportunity) }}" class="btn btn-sm btn-outline-primary">Manage</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        <div class="text-center mt-3">
                            <a href="{{ route('opportunities.index') }}" class="btn btn-outline-primary">
                                <i class="bi bi-briefcase me-1"></i>View All Opportunities
                            </a>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="bi bi-briefcase display-4 text-muted"></i>
                            <h6 class="mt-3 text-muted">No Opportunities Yet</h6>
                            <p class="text-muted">Create your first volunteer opportunity to get started.</p>
                            <a href="{{ route('opportunities.create') }}" class="btn btn-primary">
                                <i class="bi bi-plus-circle me-1"></i>Create Opportunity
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

.table-hover tbody tr:hover {
    background-color: rgba(0, 123, 255, 0.05);
}
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // Add any organization dashboard specific JavaScript here
    console.log('Organization dashboard loaded successfully');

    // Example: Auto-refresh applications every 60 seconds
    // setInterval(function() {
    //     // Refresh applications table
    // }, 60000);
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
    transition: box-shadow 0.3s ease, transform 0.3s ease;
}

.hover-shadow:hover {
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
    transform: translateY(-2px);
}

.card {
    transition: all 0.3s ease;
}

.card:hover {
    transform: translateY(-2px);
}
</style>
@endpush
