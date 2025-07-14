@extends('layouts.admin')

@section('title', 'Admin Dashboard - MVMS')

@section('page-title', 'System Administration')

@section('content')
<div class="container-fluid">
    <!-- Welcome Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 bg-gradient" style="background: linear-gradient(135deg, #6f42c1 0%, #5a2d91 100%);">
                <div class="card-body text-white p-4">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h2 class="mb-2">
                                <i class="bi bi-shield-check me-2"></i>Admin Dashboard
                            </h2>
                            <p class="mb-0 opacity-90">
                                <i class="bi bi-person-badge me-2"></i>
                                Welcome, {{ Auth::user()->name }} - System Administrator
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
                                    <small class="opacity-75">System Time</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- System Overview Stats -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card h-100">
                <div class="card-body text-center">
                    <div class="text-primary mb-3">
                        <i class="bi bi-people" style="font-size: 2.5rem;"></i>
                    </div>
                    <h4 class="text-primary mb-1">1,247</h4>
                    <h6 class="card-title text-muted">Total Users</h6>
                    <small class="text-success">
                        <i class="bi bi-arrow-up me-1"></i>+12% this month
                    </small>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card h-100">
                <div class="card-body text-center">
                    <div class="text-success mb-3">
                        <i class="bi bi-building" style="font-size: 2.5rem;"></i>
                    </div>
                    <h4 class="text-success mb-1">89</h4>
                    <h6 class="card-title text-muted">Organizations</h6>
                    <small class="text-warning">
                        <i class="bi bi-clock me-1"></i>5 pending approval
                    </small>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card h-100">
                <div class="card-body text-center">
                    <div class="text-info mb-3">
                        <i class="bi bi-briefcase" style="font-size: 2.5rem;"></i>
                    </div>
                    <h4 class="text-info mb-1">234</h4>
                    <h6 class="card-title text-muted">Active Opportunities</h6>
                    <small class="text-success">
                        <i class="bi bi-arrow-up me-1"></i>+8% this week
                    </small>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card h-100">
                <div class="card-body text-center">
                    <div class="text-warning mb-3">
                        <i class="bi bi-exclamation-triangle" style="font-size: 2.5rem;"></i>
                    </div>
                    <h4 class="text-warning mb-1">7</h4>
                    <h6 class="card-title text-muted">System Alerts</h6>
                    <small class="text-danger">
                        <i class="bi bi-exclamation-circle me-1"></i>Requires attention
                    </small>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Row -->
    <div class="row">
        <!-- Recent System Activity -->
        <div class="col-lg-8 mb-4">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bi bi-activity me-2 text-primary"></i>Recent System Activity
                    </h5>
                    <a href="#" class="btn btn-sm btn-outline-primary">View All Logs</a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Activity</th>
                                    <th>User</th>
                                    <th>Type</th>
                                    <th>Time</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <i class="bi bi-person-plus text-success me-2"></i>
                                            New organization registration
                                        </div>
                                    </td>
                                    <td>Green Malawi Initiative</td>
                                    <td><span class="badge bg-info">Registration</span></td>
                                    <td>{{ now()->subMinutes(15)->format('H:i') }}</td>
                                    <td><span class="badge bg-warning text-dark">Pending</span></td>
                                </tr>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <i class="bi bi-shield-check text-primary me-2"></i>
                                            User role updated
                                        </div>
                                    </td>
                                    <td>john.doe@example.com</td>
                                    <td><span class="badge bg-primary">User Management</span></td>
                                    <td>{{ now()->subHour()->format('H:i') }}</td>
                                    <td><span class="badge bg-success">Completed</span></td>
                                </tr>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <i class="bi bi-exclamation-triangle text-warning me-2"></i>
                                            Failed login attempt
                                        </div>
                                    </td>
                                    <td>suspicious@email.com</td>
                                    <td><span class="badge bg-danger">Security</span></td>
                                    <td>{{ now()->subHours(2)->format('H:i') }}</td>
                                    <td><span class="badge bg-danger">Alert</span></td>
                                </tr>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <i class="bi bi-briefcase text-info me-2"></i>
                                            New opportunity posted
                                        </div>
                                    </td>
                                    <td>Health for All Malawi</td>
                                    <td><span class="badge bg-success">Content</span></td>
                                    <td>{{ now()->subHours(3)->format('H:i') }}</td>
                                    <td><span class="badge bg-success">Active</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Admin Quick Actions & System Status -->
        <div class="col-lg-4 mb-4">
            <div class="card mb-3">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="bi bi-lightning-fill me-2 text-warning"></i>Admin Actions
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button class="btn btn-primary">
                            <i class="bi bi-people me-2"></i>Manage Users
                        </button>
                        <button class="btn btn-outline-primary">
                            <i class="bi bi-building me-2"></i>Review Organizations
                        </button>
                        <button class="btn btn-outline-primary">
                            <i class="bi bi-shield-lock me-2"></i>Roles & Permissions
                        </button>
                        <button class="btn btn-outline-primary">
                            <i class="bi bi-gear me-2"></i>System Settings
                        </button>
                        <button class="btn btn-outline-danger">
                            <i class="bi bi-exclamation-triangle me-2"></i>View Alerts
                        </button>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="bi bi-cpu me-2 text-info"></i>System Status
                    </h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <small class="text-muted">Server Load</small>
                            <small class="text-success">Normal</small>
                        </div>
                        <div class="progress" style="height: 6px;">
                            <div class="progress-bar bg-success" style="width: 35%"></div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <small class="text-muted">Database</small>
                            <small class="text-success">Online</small>
                        </div>
                        <div class="progress" style="height: 6px;">
                            <div class="progress-bar bg-success" style="width: 100%"></div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <small class="text-muted">Storage</small>
                            <small class="text-warning">78% Used</small>
                        </div>
                        <div class="progress" style="height: 6px;">
                            <div class="progress-bar bg-warning" style="width: 78%"></div>
                        </div>
                    </div>
                    <div class="mb-0">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <small class="text-muted">Active Sessions</small>
                            <small class="text-info">142</small>
                        </div>
                        <div class="progress" style="height: 6px;">
                            <div class="progress-bar bg-info" style="width: 60%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Pending Approvals & User Analytics -->
    <div class="row">
        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bi bi-clock me-2 text-warning"></i>Pending Approvals
                    </h5>
                    <span class="badge bg-warning text-dark">5 Items</span>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        <div class="list-group-item border-0 px-0">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-1">Green Malawi Initiative</h6>
                                    <p class="mb-1 text-muted small">Organization registration pending review</p>
                                    <small class="text-muted">Submitted 2 hours ago</small>
                                </div>
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-outline-success">Approve</button>
                                    <button class="btn btn-outline-danger">Reject</button>
                                </div>
                            </div>
                        </div>
                        <div class="list-group-item border-0 px-0">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-1">Youth Development Center</h6>
                                    <p class="mb-1 text-muted small">Organization profile update</p>
                                    <small class="text-muted">Submitted 1 day ago</small>
                                </div>
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-outline-success">Approve</button>
                                    <button class="btn btn-outline-danger">Reject</button>
                                </div>
                            </div>
                        </div>
                        <div class="list-group-item border-0 px-0">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-1">Community Health Workshop</h6>
                                    <p class="mb-1 text-muted small">Opportunity flagged for review</p>
                                    <small class="text-muted">Flagged 3 days ago</small>
                                </div>
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-outline-primary">Review</button>
                                    <button class="btn btn-outline-danger">Remove</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-graph-up me-2 text-success"></i>User Growth Analytics
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row text-center mb-3">
                        <div class="col-4">
                            <div class="border-end">
                                <div class="h4 text-success mb-0">+45</div>
                                <small class="text-muted">This Week</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="border-end">
                                <div class="h4 text-primary mb-0">+178</div>
                                <small class="text-muted">This Month</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="h4 text-info mb-0">+892</div>
                            <small class="text-muted">This Year</small>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-muted">Volunteers</span>
                            <span class="fw-bold">68%</span>
                        </div>
                        <div class="progress mb-2" style="height: 8px;">
                            <div class="progress-bar bg-success" style="width: 68%"></div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-muted">Organizations</span>
                            <span class="fw-bold">25%</span>
                        </div>
                        <div class="progress mb-2" style="height: 8px;">
                            <div class="progress-bar bg-primary" style="width: 25%"></div>
                        </div>
                    </div>
                    
                    <div class="mb-0">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-muted">Admins</span>
                            <span class="fw-bold">7%</span>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar bg-warning" style="width: 7%"></div>
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
    // Add any admin dashboard specific JavaScript here
    console.log('Admin dashboard loaded successfully');
    
    // Example: Auto-refresh system status every 30 seconds
    // setInterval(function() {
    //     // Refresh system status
    // }, 30000);
    
    // Real-time notifications for admin alerts
    // setInterval(function() {
    //     // Check for new alerts
    // }, 10000);
});
</script>
@endpush
