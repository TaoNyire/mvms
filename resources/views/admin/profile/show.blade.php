@extends('layouts.admin')

@section('title', 'Admin Profile - MVMS')

@section('page-title', 'Admin Profile')

@section('content')
<div class="container-fluid">
    <!-- Profile Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 position-relative overflow-hidden" style="background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%); min-height: 200px;">
                <div class="card-body text-white p-4 position-relative">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <div class="d-flex align-items-center mb-3">
                                <div class="bg-white bg-opacity-20 rounded-circle p-3 me-3" style="width: 80px; height: 80px; display: flex; align-items: center; justify-content: center;">
                                    <i class="bi bi-person-circle" style="font-size: 2.5rem;"></i>
                                </div>
                                <div>
                                    <h1 class="mb-1 fw-bold" style="font-size: 2.2rem;">{{ $admin->name }}</h1>
                                    <div class="badge bg-white bg-opacity-20 fw-semibold px-3 py-2 mb-2">
                                        <i class="bi bi-shield-check me-1"></i>System Administrator
                                    </div>
                                    <p class="mb-0 opacity-90">{{ $admin->email }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 text-md-end">
                            <div class="d-flex flex-column align-items-md-end gap-2">
                                <a href="{{ route('admin.profile.edit') }}" class="btn btn-light btn-sm">
                                    <i class="bi bi-pencil me-1"></i>Edit Profile
                                </a>
                                <a href="{{ route('admin.profile.change-password') }}" class="btn btn-outline-light btn-sm">
                                    <i class="bi bi-key me-1"></i>Change Password
                                </a>
                                <a href="{{ route('admin.profile.security') }}" class="btn btn-outline-light btn-sm">
                                    <i class="bi bi-shield-check me-1"></i>Security Settings
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Profile Statistics -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="text-primary mb-2">
                        <i class="bi bi-calendar-check" style="font-size: 2rem;"></i>
                    </div>
                    <h5 class="fw-bold mb-1">{{ $stats['login_count'] }}</h5>
                    <small class="text-muted">Total Logins</small>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="text-success mb-2">
                        <i class="bi bi-lightning" style="font-size: 2rem;"></i>
                    </div>
                    <h5 class="fw-bold mb-1">{{ $stats['total_actions'] }}</h5>
                    <small class="text-muted">Admin Actions</small>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="text-info mb-2">
                        <i class="bi bi-people" style="font-size: 2rem;"></i>
                    </div>
                    <h5 class="fw-bold mb-1">{{ $stats['users_managed'] }}</h5>
                    <small class="text-muted">Users Managed</small>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="text-warning mb-2">
                        <i class="bi bi-building-check" style="font-size: 2rem;"></i>
                    </div>
                    <h5 class="fw-bold mb-1">{{ $stats['organizations_approved'] }}</h5>
                    <small class="text-muted">Organizations Approved</small>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Profile Information -->
        <div class="col-lg-8 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0">
                        <i class="bi bi-person-lines-fill me-2"></i>Profile Information
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold text-muted">Full Name</label>
                            <p class="mb-0">{{ $admin->name }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold text-muted">Email Address</label>
                            <p class="mb-0">{{ $admin->email }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold text-muted">Account Created</label>
                            <p class="mb-0">{{ $stats['account_created']->format('F j, Y') }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold text-muted">Last Login</label>
                            <p class="mb-0">
                                @if($stats['last_login'])
                                    {{ $stats['last_login']->diffForHumans() }}
                                @else
                                    Never
                                @endif
                            </p>
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label fw-semibold text-muted">Bio</label>
                            <p class="mb-0">
                                @if(session('admin_profile_data.bio'))
                                    {{ session('admin_profile_data.bio') }}
                                @else
                                    <em class="text-muted">No bio provided</em>
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Activities -->
            <div class="card border-0 shadow-sm mt-4">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0">
                        <i class="bi bi-activity me-2"></i>Recent Activities
                    </h5>
                </div>
                <div class="card-body">
                    @if(count($recent_activities) > 0)
                        <div class="timeline">
                            @foreach($recent_activities as $activity)
                                <div class="timeline-item d-flex mb-3">
                                    <div class="timeline-marker bg-primary rounded-circle me-3" style="width: 12px; height: 12px; margin-top: 6px;"></div>
                                    <div class="timeline-content">
                                        <p class="mb-1">
                                            <strong>{{ $activity['action'] }}</strong>
                                            @if($activity['target'])
                                                - {{ $activity['target'] }}
                                            @endif
                                        </p>
                                        <small class="text-muted">{{ $activity['timestamp']->diffForHumans() }}</small>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-muted text-center py-3">No recent activities</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Security Overview -->
        <div class="col-lg-4 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0">
                        <i class="bi bi-shield-check me-2"></i>Security Overview
                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-muted">Two-Factor Authentication</span>
                            @if($security_settings['two_factor_enabled'])
                                <span class="badge bg-success">Enabled</span>
                            @else
                                <span class="badge bg-warning">Disabled</span>
                            @endif
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-muted">Password Last Changed</span>
                            <small>{{ $security_settings['last_password_change']->diffForHumans() }}</small>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-muted">Active Sessions</span>
                            <span class="badge bg-info">{{ $security_settings['active_sessions'] }}</span>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-muted">Failed Login Attempts</span>
                            <span class="badge bg-{{ $security_settings['failed_login_attempts'] > 0 ? 'danger' : 'success' }}">
                                {{ $security_settings['failed_login_attempts'] }}
                            </span>
                        </div>
                    </div>

                    <div class="d-grid gap-2 mt-3">
                        <a href="{{ route('admin.profile.security') }}" class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-shield-check me-1"></i>View Security Details
                        </a>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card border-0 shadow-sm mt-4">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0">
                        <i class="bi bi-lightning me-2"></i>Quick Actions
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-speedometer2 me-1"></i>Dashboard
                        </a>
                        <a href="{{ route('admin.users.index') }}" class="btn btn-outline-success btn-sm">
                            <i class="bi bi-people me-1"></i>Manage Users
                        </a>

                        <a href="{{ route('admin.logs') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-file-text me-1"></i>System Logs
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.timeline-item {
    position: relative;
}

.timeline-item:not(:last-child)::before {
    content: '';
    position: absolute;
    left: 5px;
    top: 18px;
    width: 2px;
    height: calc(100% + 12px);
    background-color: #e9ecef;
}
</style>
@endsection
