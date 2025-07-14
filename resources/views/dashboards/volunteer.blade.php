@extends('layouts.volunteer')

@section('title', 'Volunteer Dashboard - MVMS')

@section('page-title', 'Volunteer Dashboard')

@section('content')
<div class="container-fluid">
    <!-- Welcome Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 bg-gradient" style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%);">
                <div class="card-body text-white p-4">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h2 class="mb-2">Welcome back, {{ Auth::user()->name }}!</h2>
                            <p class="mb-0 opacity-90">
                                <i class="bi bi-calendar-event me-2"></i>
                                Today is {{ now()->format('l, F j, Y') }}
                            </p>
                            <p class="mb-0 opacity-90">
                                <i class="bi bi-geo-alt me-2"></i>
                                Ready to make a difference in your community?
                            </p>
                        </div>
                        <div class="col-md-4 text-md-end">
                            <div class="d-flex align-items-center justify-content-md-end">
                                <div class="text-center">
                                    <div class="display-6 fw-bold">{{ now()->format('H:i') }}</div>
                                    <small class="opacity-75">Current Time</small>
                                </div>
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
            <div class="card h-100">
                <div class="card-body text-center">
                    <div class="text-success mb-3">
                        <i class="bi bi-file-earmark-text" style="font-size: 2.5rem;"></i>
                    </div>
                    <h4 class="text-success mb-1">5</h4>
                    <h6 class="card-title text-muted">Active Applications</h6>
                    <small class="text-muted">2 pending review</small>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card h-100">
                <div class="card-body text-center">
                    <div class="text-primary mb-3">
                        <i class="bi bi-check-circle" style="font-size: 2.5rem;"></i>
                    </div>
                    <h4 class="text-primary mb-1">12</h4>
                    <h6 class="card-title text-muted">Completed Tasks</h6>
                    <small class="text-muted">This month</small>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card h-100">
                <div class="card-body text-center">
                    <div class="text-warning mb-3">
                        <i class="bi bi-clock" style="font-size: 2.5rem;"></i>
                    </div>
                    <h4 class="text-warning mb-1">48</h4>
                    <h6 class="card-title text-muted">Hours Volunteered</h6>
                    <small class="text-muted">Total this year</small>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card h-100">
                <div class="card-body text-center">
                    <div class="text-info mb-3">
                        <i class="bi bi-award" style="font-size: 2.5rem;"></i>
                    </div>
                    <h4 class="text-info mb-1">3</h4>
                    <h6 class="card-title text-muted">Certifications</h6>
                    <small class="text-muted">Earned</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Row -->
    <div class="row">
        <!-- Recent Opportunities -->
        <div class="col-lg-8 mb-4">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bi bi-search me-2 text-success"></i>Recommended Opportunities
                    </h5>
                    <a href="#" class="btn btn-sm btn-outline-success">View All</a>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="border rounded p-3 h-100">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <h6 class="mb-1">Community Clean-up Drive</h6>
                                    <span class="badge bg-success">New</span>
                                </div>
                                <p class="text-muted small mb-2">Help clean up the local park and make our community beautiful...</p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <small class="text-muted">
                                        <i class="bi bi-geo-alt me-1"></i>Lilongwe
                                    </small>
                                    <button class="btn btn-sm btn-success">Apply</button>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="border rounded p-3 h-100">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <h6 class="mb-1">Teaching Assistant</h6>
                                    <span class="badge bg-primary">Urgent</span>
                                </div>
                                <p class="text-muted small mb-2">Assist teachers in local primary school with reading programs...</p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <small class="text-muted">
                                        <i class="bi bi-geo-alt me-1"></i>Blantyre
                                    </small>
                                    <button class="btn btn-sm btn-success">Apply</button>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="border rounded p-3 h-100">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <h6 class="mb-1">Food Distribution</h6>
                                    <span class="badge bg-warning text-dark">Weekend</span>
                                </div>
                                <p class="text-muted small mb-2">Help distribute food packages to families in need...</p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <small class="text-muted">
                                        <i class="bi bi-geo-alt me-1"></i>Mzuzu
                                    </small>
                                    <button class="btn btn-sm btn-success">Apply</button>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="border rounded p-3 h-100">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <h6 class="mb-1">Health Awareness Campaign</h6>
                                    <span class="badge bg-info">Remote</span>
                                </div>
                                <p class="text-muted small mb-2">Create awareness materials for health education programs...</p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <small class="text-muted">
                                        <i class="bi bi-laptop me-1"></i>Remote Work
                                    </small>
                                    <button class="btn btn-sm btn-success">Apply</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions & Updates -->
        <div class="col-lg-4 mb-4">
            <div class="card mb-3">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="bi bi-lightning-fill me-2 text-warning"></i>Quick Actions
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button class="btn btn-success">
                            <i class="bi bi-search me-2"></i>Browse Opportunities
                        </button>
                        <button class="btn btn-outline-success">
                            <i class="bi bi-person-gear me-2"></i>Update Profile
                        </button>
                        <button class="btn btn-outline-success">
                            <i class="bi bi-award me-2"></i>Add Skills
                        </button>
                        <button class="btn btn-outline-success">
                            <i class="bi bi-calendar-event me-2"></i>View Schedule
                        </button>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="bi bi-bell me-2 text-primary"></i>Recent Updates
                    </h6>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        <div class="list-group-item border-0 px-0 py-2">
                            <div class="d-flex align-items-start">
                                <div class="flex-shrink-0">
                                    <div class="bg-success rounded-circle p-1 text-white" style="width: 8px; height: 8px;"></div>
                                </div>
                                <div class="flex-grow-1 ms-2">
                                    <small class="text-muted">Your application for "Community Garden" was approved!</small>
                                    <div class="text-muted" style="font-size: 0.75rem;">2 hours ago</div>
                                </div>
                            </div>
                        </div>
                        <div class="list-group-item border-0 px-0 py-2">
                            <div class="d-flex align-items-start">
                                <div class="flex-shrink-0">
                                    <div class="bg-primary rounded-circle p-1 text-white" style="width: 8px; height: 8px;"></div>
                                </div>
                                <div class="flex-grow-1 ms-2">
                                    <small class="text-muted">New opportunity matching your skills available</small>
                                    <div class="text-muted" style="font-size: 0.75rem;">1 day ago</div>
                                </div>
                            </div>
                        </div>
                        <div class="list-group-item border-0 px-0 py-2">
                            <div class="d-flex align-items-start">
                                <div class="flex-shrink-0">
                                    <div class="bg-warning rounded-circle p-1 text-white" style="width: 8px; height: 8px;"></div>
                                </div>
                                <div class="flex-grow-1 ms-2">
                                    <small class="text-muted">Reminder: Complete your profile to get better matches</small>
                                    <div class="text-muted" style="font-size: 0.75rem;">3 days ago</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-activity me-2 text-info"></i>My Recent Activity
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Activity</th>
                                    <th>Organization</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <i class="bi bi-check-circle text-success me-2"></i>
                                            Completed: Tree Planting Event
                                        </div>
                                    </td>
                                    <td>Green Malawi Initiative</td>
                                    <td>{{ now()->subDays(2)->format('M j, Y') }}</td>
                                    <td><span class="badge bg-success">Completed</span></td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary">View Details</button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <i class="bi bi-clock text-warning me-2"></i>
                                            Applied: Youth Mentorship Program
                                        </div>
                                    </td>
                                    <td>Future Leaders Foundation</td>
                                    <td>{{ now()->subDays(5)->format('M j, Y') }}</td>
                                    <td><span class="badge bg-warning text-dark">Pending</span></td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary">View Details</button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <i class="bi bi-person-plus text-primary me-2"></i>
                                            Applied: Community Health Workshop
                                        </div>
                                    </td>
                                    <td>Health for All Malawi</td>
                                    <td>{{ now()->subWeek()->format('M j, Y') }}</td>
                                    <td><span class="badge bg-primary">Approved</span></td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary">View Details</button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
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
    
    // Example: Auto-refresh notifications every 30 seconds
    // setInterval(function() {
    //     // Refresh notifications
    // }, 30000);
});
</script>
@endpush
