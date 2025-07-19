@extends('layouts.admin')

@section('title', 'Admin Dashboard - MVMS')

@section('page-title', 'Admin Dashboard')

@section('breadcrumb')
    <li class="breadcrumb-item active">Dashboard</li>
@endsection

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
                                    <h2 class="mb-1">MVMS Admin Dashboard</h2>
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
            <a href="{{ route('admin.users.index') }}" class="text-decoration-none">
                <div class="card h-100 hover-shadow">
                    <div class="card-body text-center">
                        <div class="text-primary mb-3">
                            <i class="bi bi-people" style="font-size: 2.5rem;"></i>
                        </div>
                        <h4 class="text-primary mb-1">{{ number_format($stats['total_users']) }}</h4>
                        <h6 class="card-title text-muted">Total Users</h6>
                        <small class="text-muted">{{ $stats['new_users_today'] }} new today</small>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <a href="{{ route('admin.organizations.index') }}" class="text-decoration-none">
                <div class="card h-100 hover-shadow">
                    <div class="card-body text-center">
                        <div class="text-success mb-3">
                            <i class="bi bi-building" style="font-size: 2.5rem;"></i>
                        </div>
                        <h4 class="text-success mb-1">{{ number_format($stats['approved_organizations']) }}</h4>
                        <h6 class="card-title text-muted">Organizations</h6>
                        <small class="text-muted">{{ $stats['pending_organizations'] }} pending approval</small>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card h-100">
                <div class="card-body text-center">
                    <div class="text-warning mb-3">
                        <i class="bi bi-briefcase" style="font-size: 2.5rem;"></i>
                    </div>
                    <h4 class="text-warning mb-1">{{ number_format($stats['total_opportunities']) }}</h4>
                    <h6 class="card-title text-muted">Active Opportunities</h6>
                    <small class="text-muted">{{ number_format($stats['total_applications']) }} applications</small>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <a href="{{ route('admin.users.index', ['role' => 'volunteer']) }}" class="text-decoration-none">
                <div class="card h-100 hover-shadow">
                    <div class="card-body text-center">
                        <div class="text-info mb-3">
                            <i class="bi bi-person-check" style="font-size: 2.5rem;"></i>
                        </div>
                        <h4 class="text-info mb-1">{{ number_format($stats['active_volunteers']) }}</h4>
                        <h6 class="card-title text-muted">Active Volunteers</h6>
                        <small class="text-muted">Ready to help</small>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <!-- Detailed Statistics Row -->
    <div class="row mb-4">
        <!-- Volunteer Insights -->
        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-person-heart me-2"></i>Volunteer Insights
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-6">
                            <div class="text-center p-3 border rounded">
                                <h4 class="text-success">{{ $stats['completed_volunteer_profiles'] }}</h4>
                                <small class="text-muted">Complete Profiles</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="text-center p-3 border rounded">
                                <h4 class="text-info">{{ number_format($stats['volunteers_available']) }}</h4>
                                <small class="text-muted">Available Now</small>
                            </div>
                        </div>
                    </div>


                </div>
            </div>
        </div>

        <!-- Organization Insights -->
        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-building me-2"></i>Organization Insights
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-4">
                            <div class="text-center p-3 border rounded">
                                <h4 class="text-success">{{ $stats['approved_organizations'] }}</h4>
                                <small class="text-muted">Approved</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="text-center p-3 border rounded">
                                <h4 class="text-warning">{{ $stats['pending_organizations'] }}</h4>
                                <small class="text-muted">Pending</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="text-center p-3 border rounded">
                                <h4 class="text-danger">{{ $stats['rejected_organizations'] }}</h4>
                                <small class="text-muted">Rejected</small>
                            </div>
                        </div>
                    </div>


                </div>
            </div>
        </div>
    </div>





    <!-- Quick Actions & Pending Items -->
    <div class="row mb-4">
        <!-- Pending Organization Approvals -->
        <div class="col-lg-8 mb-4">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bi bi-clipboard-check me-2"></i>Pending Organization Approvals
                    </h5>
                    <a href="{{ route('admin.organizations.index', ['status' => 'pending']) }}" class="btn btn-sm btn-outline-primary">
                        View All
                    </a>
                </div>
                <div class="card-body">
                    @if($pending_organizations->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Organization</th>
                                        <th>Contact Person</th>
                                        <th>Registration Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($pending_organizations as $org)
                                        <tr>
                                            <td>
                                                <div>
                                                    <strong>{{ $org->org_name }}</strong>
                                                    <br>
                                                    <small class="text-muted">{{ $org->org_type }}</small>
                                                </div>
                                            </td>
                                            <td>
                                                <div>
                                                    {{ $org->user->name }}
                                                    <br>
                                                    <small class="text-muted">{{ $org->user->email }}</small>
                                                </div>
                                            </td>
                                            <td>
                                                {{ $org->created_at->format('M j, Y') }}
                                                <br>
                                                <small class="text-muted">{{ $org->created_at->diffForHumans() }}</small>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('admin.organizations.show', $org) }}" 
                                                       class="btn btn-sm btn-outline-primary">
                                                        <i class="bi bi-eye"></i>
                                                    </a>
                                                    <button type="button" class="btn btn-sm btn-outline-success" 
                                                            onclick="approveOrganization({{ $org->id }})">
                                                        <i class="bi bi-check"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-outline-danger" 
                                                            onclick="rejectOrganization({{ $org->id }})">
                                                        <i class="bi bi-x"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="bi bi-check-circle text-success" style="font-size: 3rem;"></i>
                            <h6 class="mt-2">No Pending Approvals</h6>
                            <p class="text-muted">All organizations have been processed.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="col-lg-4 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-lightning me-2"></i>Quick Actions
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('admin.users.index') }}" class="btn btn-outline-primary">
                            <i class="bi bi-people me-2"></i>Manage Users
                        </a>
                        <a href="{{ route('admin.organizations.index') }}" class="btn btn-outline-success">
                            <i class="bi bi-building me-2"></i>Review Organizations
                        </a>
                        <a href="{{ route('admin.logs') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-file-text me-2"></i>System Logs
                        </a>
                    </div>
                </div>
            </div>

            <!-- System Health -->
            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-heart-pulse me-2"></i>System Health
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span>Database</span>
                        <span class="badge bg-{{ $health['database_status'] === 'healthy' ? 'success' : 'danger' }}">
                            {{ ucfirst($health['database_status']) }}
                        </span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span>Storage</span>
                        <span class="badge bg-info">{{ $health['storage_usage'] }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span>Recent Errors</span>
                        <span class="badge bg-{{ $health['recent_errors'] > 0 ? 'warning' : 'success' }}">
                            {{ $health['recent_errors'] }}
                        </span>
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
                        <i class="bi bi-activity me-2"></i>Recent User Registrations
                    </h5>
                </div>
                <div class="card-body">
                    @if($recent_users->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>User</th>
                                        <th>Role</th>
                                        <th>Status</th>
                                        <th>Registration Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recent_users as $user)
                                        <tr>
                                            <td>
                                                <div>
                                                    <strong>{{ $user->name }}</strong>
                                                    <br>
                                                    <small class="text-muted">{{ $user->email }}</small>
                                                </div>
                                            </td>
                                            <td>
                                                @foreach($user->roles as $role)
                                                    <span class="badge bg-secondary">{{ ucfirst($role->name) }}</span>
                                                @endforeach
                                            </td>
                                            <td>
                                                <span class="badge bg-{{ $user->is_active ? 'success' : 'danger' }}">
                                                    {{ $user->is_active ? 'Active' : 'Inactive' }}
                                                </span>
                                            </td>
                                            <td>
                                                {{ $user->created_at->format('M j, Y') }}
                                                <br>
                                                <small class="text-muted">{{ $user->created_at->diffForHumans() }}</small>
                                            </td>
                                            <td>
                                                <a href="{{ route('admin.users.show', $user) }}" 
                                                   class="btn btn-sm btn-outline-primary">
                                                    <i class="bi bi-eye"></i> View
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="bi bi-person-plus text-muted" style="font-size: 3rem;"></i>
                            <h6 class="mt-2">No Recent Registrations</h6>
                            <p class="text-muted">No new users have registered recently.</p>
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
function approveOrganization(orgId) {
    if (confirm('Are you sure you want to approve this organization?')) {
        fetch(`/admin/organizations/${orgId}/approve`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while approving the organization.');
        });
    }
}

function rejectOrganization(orgId) {
    const reason = prompt('Please provide a reason for rejection:');
    if (reason && reason.trim()) {
        fetch(`/admin/organizations/${orgId}/reject`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                rejection_reason: reason.trim()
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while rejecting the organization.');
        });
    }
}

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
    transition: box-shadow 0.3s ease;
}

.hover-shadow:hover {
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
    transform: translateY(-2px);
}
</style>
@endpush
