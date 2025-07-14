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
            <div class="card border-0 bg-gradient text-white" style="background: linear-gradient(135deg, #dc3545 0%, #b02a37 100%);">
                <div class="card-body p-4">
                    <h2 class="mb-2">Welcome back, {{ auth()->user()->name }}!</h2>
                    <p class="mb-0">Here's what's happening in your volunteer management system today.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <!-- Users Overview -->
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="stats-card">
                <div class="d-flex align-items-center">
                    <div class="stats-icon bg-primary me-3">
                        <i class="bi bi-people"></i>
                    </div>
                    <div>
                        <h3 class="mb-0">{{ $stats['total_users'] }}</h3>
                        <p class="text-muted mb-0">Total Users</p>
                        <small class="text-success">
                            <i class="bi bi-check-circle"></i>{{ $stats['active_users'] }} active
                        </small>
                        <small class="text-muted d-block">{{ $stats['new_users_today'] }} new today</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Volunteers -->
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="stats-card">
                <div class="d-flex align-items-center">
                    <div class="stats-icon bg-success me-3">
                        <i class="bi bi-person-heart"></i>
                    </div>
                    <div>
                        <h3 class="mb-0">{{ $stats['total_volunteers'] }}</h3>
                        <p class="text-muted mb-0">Volunteers</p>
                        <small class="text-success">
                            <i class="bi bi-person-check"></i>{{ $stats['active_volunteers'] }} active
                        </small>
                        <small class="text-muted d-block">{{ $stats['volunteers_with_profiles'] }} with profiles</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Organizations -->
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="stats-card">
                <div class="d-flex align-items-center">
                    <div class="stats-icon bg-info me-3">
                        <i class="bi bi-building"></i>
                    </div>
                    <div>
                        <h3 class="mb-0">{{ $stats['approved_organizations'] }}</h3>
                        <p class="text-muted mb-0">Organizations</p>
                        <small class="text-warning">
                            <i class="bi bi-clock"></i>{{ $stats['pending_organizations'] }} pending
                        </small>
                        <small class="text-muted d-block">{{ $stats['verified_organizations'] }} verified</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Opportunities -->
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="stats-card">
                <div class="d-flex align-items-center">
                    <div class="stats-icon bg-warning me-3">
                        <i class="bi bi-briefcase"></i>
                    </div>
                    <div>
                        <h3 class="mb-0">{{ $stats['total_opportunities'] }}</h3>
                        <p class="text-muted mb-0">Opportunities</p>
                        <small class="text-success">
                            <i class="bi bi-eye"></i>{{ $stats['active_opportunities'] }} active
                        </small>
                        <small class="text-muted d-block">{{ $stats['total_applications'] }} applications</small>
                    </div>
                </div>
            </div>
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
                                <h4 class="text-info">{{ $stats['volunteers_available'] ?? 0 }}</h4>
                                <small class="text-muted">Available Now</small>
                            </div>
                        </div>
                    </div>

                    @if(isset($volunteer_insights['top_skills']) && count($volunteer_insights['top_skills']) > 0)
                        <div class="mt-3">
                            <h6>Top Skills</h6>
                            <div class="d-flex flex-wrap gap-1">
                                @foreach(array_slice($volunteer_insights['top_skills'], 0, 8) as $skill => $count)
                                    <span class="badge bg-secondary">{{ $skill }} ({{ $count }})</span>
                                @endforeach
                            </div>
                        </div>
                    @endif
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

                    @if(isset($organization_insights['organization_types']) && count($organization_insights['organization_types']) > 0)
                        <div class="mt-3">
                            <h6>Organization Types</h6>
                            <div class="d-flex flex-wrap gap-1">
                                @foreach(array_slice($organization_insights['organization_types'], 0, 5) as $type => $count)
                                    <span class="badge bg-primary">{{ $type }} ({{ $count }})</span>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Geographic Distribution -->
    <div class="row mb-4">
        <!-- Volunteer Distribution -->
        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-geo-alt me-2"></i>Volunteer Distribution by District
                    </h5>
                </div>
                <div class="card-body">
                    @if(isset($volunteer_insights['districts_distribution']) && count($volunteer_insights['districts_distribution']) > 0)
                        @foreach(array_slice($volunteer_insights['districts_distribution'], 0, 8) as $district => $count)
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span>{{ $district }}</span>
                                <div class="d-flex align-items-center">
                                    <div class="progress me-2" style="width: 100px; height: 8px;">
                                        <div class="progress-bar bg-success"
                                             style="width: {{ ($count / max($volunteer_insights['districts_distribution'])) * 100 }}%"></div>
                                    </div>
                                    <span class="badge bg-success">{{ $count }}</span>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <p class="text-muted text-center">No volunteer distribution data available</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Organization Distribution -->
        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-geo-alt me-2"></i>Organization Distribution by District
                    </h5>
                </div>
                <div class="card-body">
                    @if(isset($organization_insights['districts_distribution']) && count($organization_insights['districts_distribution']) > 0)
                        @foreach(array_slice($organization_insights['districts_distribution'], 0, 8) as $district => $count)
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span>{{ $district }}</span>
                                <div class="d-flex align-items-center">
                                    <div class="progress me-2" style="width: 100px; height: 8px;">
                                        <div class="progress-bar bg-primary"
                                             style="width: {{ ($count / max($organization_insights['districts_distribution'])) * 100 }}%"></div>
                                    </div>
                                    <span class="badge bg-primary">{{ $count }}</span>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <p class="text-muted text-center">No organization distribution data available</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity Trends -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-activity me-2"></i>Recent Activity Overview
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="text-center p-3 border rounded">
                                <h4 class="text-primary">{{ $stats['new_users_today'] }}</h4>
                                <small class="text-muted">New Users Today</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center p-3 border rounded">
                                <h4 class="text-success">{{ $stats['new_users_this_week'] }}</h4>
                                <small class="text-muted">New Users This Week</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center p-3 border rounded">
                                <h4 class="text-info">{{ $stats['new_orgs_this_month'] }}</h4>
                                <small class="text-muted">New Orgs This Month</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center p-3 border rounded">
                                <h4 class="text-warning">{{ $stats['new_volunteers_this_month'] }}</h4>
                                <small class="text-muted">New Volunteers This Month</small>
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
                        <a href="{{ route('admin.reports') }}" class="btn btn-outline-info">
                            <i class="bi bi-graph-up me-2"></i>View Reports
                        </a>
                        <a href="{{ route('admin.settings') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-gear me-2"></i>System Settings
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
</script>
@endpush
