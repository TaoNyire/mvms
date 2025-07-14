@extends('layouts.organization')

@section('title', 'Organization Dashboard - MVMS')

@section('page-title', 'Organization Dashboard')

@section('content')
<div class="container-fluid">
    <!-- Welcome Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 bg-gradient" style="background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);">
                <div class="card-body text-white p-4">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h2 class="mb-2">Welcome, {{ Auth::user()->name }}!</h2>
                            <p class="mb-0 opacity-90">
                                <i class="bi bi-building me-2"></i>
                                Organization Dashboard
                            </p>
                            <p class="mb-0 opacity-90">
                                <i class="bi bi-calendar-event me-2"></i>
                                {{ now()->format('l, F j, Y') }}
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
                    <div class="text-primary mb-3">
                        <i class="bi bi-briefcase" style="font-size: 2.5rem;"></i>
                    </div>
                    <h4 class="text-primary mb-1">8</h4>
                    <h6 class="card-title text-muted">Active Opportunities</h6>
                    <small class="text-muted">3 new this week</small>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card h-100">
                <div class="card-body text-center">
                    <div class="text-success mb-3">
                        <i class="bi bi-people" style="font-size: 2.5rem;"></i>
                    </div>
                    <h4 class="text-success mb-1">45</h4>
                    <h6 class="card-title text-muted">Active Volunteers</h6>
                    <small class="text-muted">12 new this month</small>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card h-100">
                <div class="card-body text-center">
                    <div class="text-warning mb-3">
                        <i class="bi bi-file-earmark-text" style="font-size: 2.5rem;"></i>
                    </div>
                    <h4 class="text-warning mb-1">23</h4>
                    <h6 class="card-title text-muted">Pending Applications</h6>
                    <small class="text-muted">Needs review</small>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card h-100">
                <div class="card-body text-center">
                    <div class="text-info mb-3">
                        <i class="bi bi-check-circle" style="font-size: 2.5rem;"></i>
                    </div>
                    <h4 class="text-info mb-1">156</h4>
                    <h6 class="card-title text-muted">Completed Tasks</h6>
                    <small class="text-muted">This quarter</small>
                </div>
            </div>
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
                    <a href="#" class="btn btn-sm btn-outline-primary">View All</a>
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
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="bg-primary rounded-circle text-white d-flex align-items-center justify-content-center me-2" style="width: 32px; height: 32px; font-size: 0.8rem;">
                                                JD
                                            </div>
                                            <div>
                                                <div class="fw-semibold">John Doe</div>
                                                <small class="text-muted">john@example.com</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>Community Clean-up</td>
                                    <td>{{ now()->subHours(2)->format('M j, H:i') }}</td>
                                    <td><span class="badge bg-warning text-dark">Pending</span></td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <button class="btn btn-outline-success">Approve</button>
                                            <button class="btn btn-outline-danger">Reject</button>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="bg-success rounded-circle text-white d-flex align-items-center justify-content-center me-2" style="width: 32px; height: 32px; font-size: 0.8rem;">
                                                MS
                                            </div>
                                            <div>
                                                <div class="fw-semibold">Mary Smith</div>
                                                <small class="text-muted">mary@example.com</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>Teaching Assistant</td>
                                    <td>{{ now()->subHours(5)->format('M j, H:i') }}</td>
                                    <td><span class="badge bg-success">Approved</span></td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary">View Details</button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="bg-info rounded-circle text-white d-flex align-items-center justify-content-center me-2" style="width: 32px; height: 32px; font-size: 0.8rem;">
                                                PJ
                                            </div>
                                            <div>
                                                <div class="fw-semibold">Peter Johnson</div>
                                                <small class="text-muted">peter@example.com</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>Food Distribution</td>
                                    <td>{{ now()->subDay()->format('M j, H:i') }}</td>
                                    <td><span class="badge bg-warning text-dark">Pending</span></td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <button class="btn btn-outline-success">Approve</button>
                                            <button class="btn btn-outline-danger">Reject</button>
                                        </div>
                                    </td>
                                </tr>
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
                        <button class="btn btn-primary">
                            <i class="bi bi-plus-circle me-2"></i>Post New Opportunity
                        </button>
                        <button class="btn btn-outline-primary">
                            <i class="bi bi-people me-2"></i>Manage Volunteers
                        </button>
                        <button class="btn btn-outline-primary">
                            <i class="bi bi-building-gear me-2"></i>Update Profile
                        </button>
                        <button class="btn btn-outline-primary">
                            <i class="bi bi-graph-up me-2"></i>View Reports
                        </button>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="bi bi-bell me-2 text-primary"></i>Notifications
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
                    <button class="btn btn-primary">
                        <i class="bi bi-plus-circle me-1"></i>Add New
                    </button>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-4 col-md-6 mb-3">
                            <div class="card border">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <h6 class="card-title mb-1">Community Clean-up Drive</h6>
                                        <span class="badge bg-success">Active</span>
                                    </div>
                                    <p class="card-text text-muted small">Help clean up the local park and make our community beautiful...</p>
                                    <div class="row text-center mb-2">
                                        <div class="col-4">
                                            <div class="fw-bold text-primary">12</div>
                                            <small class="text-muted">Applied</small>
                                        </div>
                                        <div class="col-4">
                                            <div class="fw-bold text-success">8</div>
                                            <small class="text-muted">Approved</small>
                                        </div>
                                        <div class="col-4">
                                            <div class="fw-bold text-info">5</div>
                                            <small class="text-muted">Needed</small>
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <small class="text-muted">
                                            <i class="bi bi-calendar me-1"></i>{{ now()->addDays(5)->format('M j') }}
                                        </small>
                                        <button class="btn btn-sm btn-outline-primary">Manage</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4 col-md-6 mb-3">
                            <div class="card border">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <h6 class="card-title mb-1">Teaching Assistant Program</h6>
                                        <span class="badge bg-warning text-dark">Urgent</span>
                                    </div>
                                    <p class="card-text text-muted small">Assist teachers in local primary school with reading programs...</p>
                                    <div class="row text-center mb-2">
                                        <div class="col-4">
                                            <div class="fw-bold text-primary">8</div>
                                            <small class="text-muted">Applied</small>
                                        </div>
                                        <div class="col-4">
                                            <div class="fw-bold text-success">3</div>
                                            <small class="text-muted">Approved</small>
                                        </div>
                                        <div class="col-4">
                                            <div class="fw-bold text-info">7</div>
                                            <small class="text-muted">Needed</small>
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <small class="text-muted">
                                            <i class="bi bi-calendar me-1"></i>{{ now()->addDays(2)->format('M j') }}
                                        </small>
                                        <button class="btn btn-sm btn-outline-primary">Manage</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4 col-md-6 mb-3">
                            <div class="card border">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <h6 class="card-title mb-1">Food Distribution</h6>
                                        <span class="badge bg-primary">Ongoing</span>
                                    </div>
                                    <p class="card-text text-muted small">Help distribute food packages to families in need...</p>
                                    <div class="row text-center mb-2">
                                        <div class="col-4">
                                            <div class="fw-bold text-primary">15</div>
                                            <small class="text-muted">Applied</small>
                                        </div>
                                        <div class="col-4">
                                            <div class="fw-bold text-success">10</div>
                                            <small class="text-muted">Approved</small>
                                        </div>
                                        <div class="col-4">
                                            <div class="fw-bold text-info">0</div>
                                            <small class="text-muted">Needed</small>
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <small class="text-muted">
                                            <i class="bi bi-calendar me-1"></i>{{ now()->format('M j') }}
                                        </small>
                                        <button class="btn btn-sm btn-outline-primary">Manage</button>
                                    </div>
                                </div>
                            </div>
                        </div>
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
    // Add any organization dashboard specific JavaScript here
    console.log('Organization dashboard loaded successfully');
    
    // Example: Auto-refresh applications every 60 seconds
    // setInterval(function() {
    //     // Refresh applications table
    // }, 60000);
});
</script>
@endpush
