@extends('layouts.app')

@section('title', 'Dashboard - MVMS')

@section('content')
<div class="container-fluid py-4">
    <!-- Welcome Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 bg-primary text-white">
                <div class="card-body p-4">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h1 class="h3 mb-2">Welcome back, {{ Auth::user()->name }}!</h1>
                            <p class="mb-0 opacity-75">
                                <i class="bi bi-shield-check me-1"></i>
                                Role: {{ ucfirst(optional(Auth::user()->roles()->first())->name ?? 'User') }}
                            </p>
                        </div>
                        <div class="col-md-4 text-md-end">
                            <div class="d-flex align-items-center justify-content-md-end">
                                <i class="bi bi-calendar-event me-2" style="font-size: 1.2rem;"></i>
                                <span>{{ now()->format('F j, Y') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="row mb-4">
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card h-100">
                <div class="card-body text-center">
                    <div class="text-primary mb-2">
                        <i class="bi bi-people-fill" style="font-size: 2rem;"></i>
                    </div>
                    <h5 class="card-title">Total Users</h5>
                    <h3 class="text-primary mb-0">1,234</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card h-100">
                <div class="card-body text-center">
                    <div class="text-success mb-2">
                        <i class="bi bi-briefcase-fill" style="font-size: 2rem;"></i>
                    </div>
                    <h5 class="card-title">Active Opportunities</h5>
                    <h3 class="text-success mb-0">89</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card h-100">
                <div class="card-body text-center">
                    <div class="text-warning mb-2">
                        <i class="bi bi-clock-fill" style="font-size: 2rem;"></i>
                    </div>
                    <h5 class="card-title">Pending Applications</h5>
                    <h3 class="text-warning mb-0">45</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card h-100">
                <div class="card-body text-center">
                    <div class="text-info mb-2">
                        <i class="bi bi-check-circle-fill" style="font-size: 2rem;"></i>
                    </div>
                    <h5 class="card-title">Completed Tasks</h5>
                    <h3 class="text-info mb-0">156</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Row -->
    <div class="row">
        <!-- Recent Activities -->
        <div class="col-lg-8 mb-4">
            <div class="card h-100">
                <div class="card-header bg-white border-bottom">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-activity me-2"></i>Recent Activities
                    </h5>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        <div class="list-group-item border-0 px-0">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <div class="bg-primary rounded-circle p-2 text-white">
                                        <i class="bi bi-person-plus"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h6 class="mb-1">New volunteer registered</h6>
                                    <p class="mb-1 text-muted">John Doe joined as a volunteer</p>
                                    <small class="text-muted">2 hours ago</small>
                                </div>
                            </div>
                        </div>
                        <div class="list-group-item border-0 px-0">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <div class="bg-success rounded-circle p-2 text-white">
                                        <i class="bi bi-briefcase"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h6 class="mb-1">New opportunity posted</h6>
                                    <p class="mb-1 text-muted">Community Clean-up Drive</p>
                                    <small class="text-muted">4 hours ago</small>
                                </div>
                            </div>
                        </div>
                        <div class="list-group-item border-0 px-0">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <div class="bg-warning rounded-circle p-2 text-white">
                                        <i class="bi bi-clock"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h6 class="mb-1">Application submitted</h6>
                                    <p class="mb-1 text-muted">Jane Smith applied for Teaching Assistant</p>
                                    <small class="text-muted">6 hours ago</small>
                                </div>
                            </div>
                        </div>
                        <div class="list-group-item border-0 px-0">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <div class="bg-info rounded-circle p-2 text-white">
                                        <i class="bi bi-check-circle"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h6 class="mb-1">Task completed</h6>
                                    <p class="mb-1 text-muted">Food Distribution completed by Mike Johnson</p>
                                    <small class="text-muted">1 day ago</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="col-lg-4 mb-4">
            <div class="card h-100">
                <div class="card-header bg-white border-bottom">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-lightning-fill me-2"></i>Quick Actions
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        @if(Auth::user()->hasRole('organization'))
                            <button class="btn btn-primary">
                                <i class="bi bi-plus-circle me-2"></i>Post New Opportunity
                            </button>
                            <button class="btn btn-outline-primary">
                                <i class="bi bi-people me-2"></i>Manage Volunteers
                            </button>
                        @elseif(Auth::user()->hasRole('volunteer'))
                            <button class="btn btn-primary">
                                <i class="bi bi-search me-2"></i>Browse Opportunities
                            </button>
                            <button class="btn btn-outline-primary">
                                <i class="bi bi-person-gear me-2"></i>Update Profile
                            </button>
                        @endif
                        
                        @if(Auth::user()->hasRole('admin'))
                            <button class="btn btn-warning">
                                <i class="bi bi-gear me-2"></i>System Settings
                            </button>
                            <button class="btn btn-outline-warning">
                                <i class="bi bi-shield-check me-2"></i>User Management
                            </button>
                        @endif
                        
                        <button class="btn btn-outline-secondary">
                            <i class="bi bi-chat-dots me-2"></i>Messages
                        </button>
                        <button class="btn btn-outline-info">
                            <i class="bi bi-bar-chart me-2"></i>View Reports
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Additional Information -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-white border-bottom">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-info-circle me-2"></i>System Information
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>User ID:</strong> {{ Auth::user()->id }}</p>
                            <p><strong>Email:</strong> {{ Auth::user()->email }}</p>
                            <p><strong>Member Since:</strong> {{ Auth::user()->created_at->format('F j, Y') }}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Last Login:</strong> {{ now()->format('F j, Y g:i A') }}</p>
                            <p><strong>Account Status:</strong> <span class="badge bg-success">Active</span></p>
                            <p><strong>Role:</strong> {{ ucfirst(optional(Auth::user()->roles()->first())->name ?? 'User') }}</p>
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
    // Add any dashboard-specific JavaScript here
    console.log('Dashboard loaded successfully');
    
    // Example: Auto-refresh stats every 30 seconds
    // setInterval(function() {
    //     // Refresh dashboard stats
    // }, 30000);
});
</script>
@endpush
