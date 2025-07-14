@extends('layouts.admin')

@section('title', 'User Details - Admin Panel')

@section('page-title', 'User Details')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.users.index') }}">Users</a></li>
    <li class="breadcrumb-item active">{{ $user->name }}</li>
@endsection

@section('content')
<div class="container-fluid">
    <!-- User Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-4" 
                             style="width: 80px; height: 80px; font-size: 2rem;">
                            {{ strtoupper(substr($user->name, 0, 2)) }}
                        </div>
                        <div class="flex-grow-1">
                            <h2 class="mb-1">{{ $user->name }}</h2>
                            <p class="text-muted mb-2">{{ $user->email }}</p>
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-{{ $user->is_active ? 'success' : 'danger' }}">
                                    {{ $user->is_active ? 'Active' : 'Inactive' }}
                                </span>
                                @foreach($user->roles as $role)
                                    <span class="badge bg-{{ $role->name === 'admin' ? 'danger' : ($role->name === 'organization' ? 'primary' : 'success') }}">
                                        {{ ucfirst($role->name) }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                        <div class="text-end">
                            <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-primary">
                                <i class="bi bi-pencil me-1"></i>Edit User
                            </a>
                            <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left me-1"></i>Back to Users
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- User Information -->
        <div class="col-lg-8">
            <!-- Basic Information -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-person me-2"></i>Basic Information
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Full Name:</strong></td>
                                    <td>{{ $user->name }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Email:</strong></td>
                                    <td>{{ $user->email }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Phone:</strong></td>
                                    <td>{{ $user->phone ?: 'Not provided' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>District:</strong></td>
                                    <td>{{ $user->district ?: 'Not provided' }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Registration Date:</strong></td>
                                    <td>{{ $user->created_at->format('M j, Y g:i A') }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Email Verified:</strong></td>
                                    <td>
                                        @if($user->email_verified_at)
                                            <span class="badge bg-success">Verified</span>
                                            <small class="text-muted d-block">{{ $user->email_verified_at->format('M j, Y') }}</small>
                                        @else
                                            <span class="badge bg-warning">Not Verified</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Last Login:</strong></td>
                                    <td>
                                        @if($user->last_login_at)
                                            {{ $user->last_login_at->format('M j, Y g:i A') }}
                                            <small class="text-muted d-block">{{ $user->last_login_at->diffForHumans() }}</small>
                                        @else
                                            <span class="text-muted">Never logged in</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Profile Completed:</strong></td>
                                    <td>
                                        <span class="badge bg-{{ $user->profile_completed ? 'success' : 'warning' }}">
                                            {{ $user->profile_completed ? 'Complete' : 'Incomplete' }}
                                        </span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Profile Information -->
            @if($user->hasRole('volunteer') && $user->volunteerProfile)
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="bi bi-person-heart me-2"></i>Volunteer Profile
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Skills:</strong></p>
                                @if($user->volunteerProfile->skills && is_array($user->volunteerProfile->skills))
                                    <div class="mb-3">
                                        @foreach($user->volunteerProfile->skills as $skill)
                                            <span class="badge bg-secondary me-1">{{ $skill }}</span>
                                        @endforeach
                                    </div>
                                @elseif($user->volunteerProfile->skills && is_string($user->volunteerProfile->skills))
                                    <div class="mb-3">
                                        @foreach(json_decode($user->volunteerProfile->skills, true) as $skill)
                                            <span class="badge bg-secondary me-1">{{ $skill }}</span>
                                        @endforeach
                                    </div>
                                @else
                                    <p class="text-muted">No skills listed</p>
                                @endif
                            </div>
                            <div class="col-md-6">
                                <p><strong>Availability:</strong></p>
                                <p class="text-muted">{{ $user->volunteerProfile->availability ?: 'Not specified' }}</p>
                            </div>
                        </div>
                        @if($user->volunteerProfile->bio)
                            <div class="mt-3">
                                <p><strong>Bio:</strong></p>
                                <p class="text-muted">{{ $user->volunteerProfile->bio }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            @if($user->hasRole('organization') && $user->organizationProfile)
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="bi bi-building me-2"></i>Organization Profile
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <table class="table table-borderless">
                                    <tr>
                                        <td><strong>Organization Name:</strong></td>
                                        <td>{{ $user->organizationProfile->org_name }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Type:</strong></td>
                                        <td>{{ $user->organizationProfile->org_type }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Registration Number:</strong></td>
                                        <td>{{ $user->organizationProfile->registration_number ?: 'Not provided' }}</td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <table class="table table-borderless">
                                    <tr>
                                        <td><strong>Status:</strong></td>
                                        <td>
                                            <span class="badge bg-{{ 
                                                $user->organizationProfile->status === 'approved' ? 'success' : 
                                                ($user->organizationProfile->status === 'rejected' ? 'danger' : 'warning') 
                                            }}">
                                                {{ ucfirst($user->organizationProfile->status) }}
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><strong>Website:</strong></td>
                                        <td>
                                            @if($user->organizationProfile->website)
                                                <a href="{{ $user->organizationProfile->website }}" target="_blank">
                                                    {{ $user->organizationProfile->website }}
                                                </a>
                                            @else
                                                Not provided
                                            @endif
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                        @if($user->organizationProfile->description)
                            <div class="mt-3">
                                <p><strong>Description:</strong></p>
                                <p class="text-muted">{{ $user->organizationProfile->description }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            <!-- Admin Notes -->
            @if($user->admin_notes)
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="bi bi-sticky me-2"></i>Admin Notes
                        </h5>
                    </div>
                    <div class="card-body">
                        <p class="mb-0">{{ $user->admin_notes }}</p>
                    </div>
                </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Account Status -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">Account Status</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <strong>Current Status:</strong>
                        <span class="badge bg-{{ $user->is_active ? 'success' : 'danger' }} ms-2">
                            {{ $user->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </div>

                    <div class="mb-3">
                        <strong>Account Status:</strong>
                        <span class="badge bg-secondary ms-2">
                            {{ ucfirst(str_replace('_', ' ', $user->account_status)) }}
                        </span>
                    </div>

                    @if($user->status_reason)
                        <div class="mb-3">
                            <strong>Status Reason:</strong>
                            <p class="text-muted mb-0">{{ $user->status_reason }}</p>
                        </div>
                    @endif

                    @if($user->activated_at)
                        <div class="mb-3">
                            <strong>Activated:</strong>
                            <small class="text-muted d-block">{{ $user->activated_at->format('M j, Y g:i A') }}</small>
                        </div>
                    @endif

                    @if($user->deactivated_at)
                        <div class="mb-3">
                            <strong>Deactivated:</strong>
                            <small class="text-muted d-block">{{ $user->deactivated_at->format('M j, Y g:i A') }}</small>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">Quick Actions</h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        @if($user->is_active)
                            <button type="button" class="btn btn-outline-warning btn-sm" 
                                    onclick="deactivateUser({{ $user->id }})">
                                <i class="bi bi-pause-circle me-1"></i>Deactivate Account
                            </button>
                            <button type="button" class="btn btn-outline-danger btn-sm" 
                                    onclick="suspendUser({{ $user->id }})">
                                <i class="bi bi-ban me-1"></i>Suspend Account
                            </button>
                        @else
                            <button type="button" class="btn btn-outline-success btn-sm" 
                                    onclick="activateUser({{ $user->id }})">
                                <i class="bi bi-play-circle me-1"></i>Activate Account
                            </button>
                        @endif
                        
                        <button type="button" class="btn btn-outline-info btn-sm" 
                                onclick="resetPassword({{ $user->id }})">
                            <i class="bi bi-key me-1"></i>Reset Password
                        </button>
                        
                        @if(!$user->hasRole('admin'))
                            <button type="button" class="btn btn-outline-danger btn-sm" 
                                    onclick="deleteUser({{ $user->id }})">
                                <i class="bi bi-trash me-1"></i>Delete User
                            </button>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Statistics -->
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">User Statistics</h6>
                </div>
                <div class="card-body">
                    @if($user->hasRole('volunteer'))
                        <div class="mb-2">
                            <strong>Applications:</strong>
                            <span class="badge bg-info ms-2">{{ $user->applications->count() }}</span>
                        </div>
                        <div class="mb-2">
                            <strong>Accepted Applications:</strong>
                            <span class="badge bg-success ms-2">{{ $user->applications->where('status', 'accepted')->count() }}</span>
                        </div>
                    @endif

                    @if($user->hasRole('organization'))
                        <div class="mb-2">
                            <strong>Opportunities Created:</strong>
                            <span class="badge bg-primary ms-2">{{ $user->opportunities->count() }}</span>
                        </div>
                        <div class="mb-2">
                            <strong>Active Opportunities:</strong>
                            <span class="badge bg-success ms-2">{{ $user->opportunities->where('status', 'published')->count() }}</span>
                        </div>
                    @endif

                    <div class="mb-2">
                        <strong>Notifications:</strong>
                        <span class="badge bg-secondary ms-2">{{ $user->notifications->count() }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function activateUser(userId) {
    if (confirm('Are you sure you want to activate this user?')) {
        fetch(`/admin/users/${userId}/activate`, {
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
        });
    }
}

function deactivateUser(userId) {
    const reason = prompt('Please provide a reason for deactivation:');
    if (reason && reason.trim()) {
        fetch(`/admin/users/${userId}/deactivate`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ reason: reason.trim() })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        });
    }
}

function suspendUser(userId) {
    const reason = prompt('Please provide a reason for suspension:');
    if (reason && reason.trim()) {
        fetch(`/admin/users/${userId}/suspend`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ reason: reason.trim() })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        });
    }
}

function deleteUser(userId) {
    const reason = prompt('Please provide a reason for deletion:');
    if (reason && reason.trim()) {
        if (confirm('Are you sure you want to permanently delete this user? This action cannot be undone.')) {
            fetch(`/admin/users/${userId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ 
                    reason: reason.trim(),
                    confirm: true
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.href = '/admin/users';
                } else {
                    alert('Error: ' + data.message);
                }
            });
        }
    }
}

function resetPassword(userId) {
    const newPassword = prompt('Enter new password (minimum 8 characters):');
    if (newPassword && newPassword.length >= 8) {
        const confirmPassword = prompt('Confirm new password:');
        if (newPassword === confirmPassword) {
            fetch(`/admin/users/${userId}/reset-password`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    password: newPassword,
                    password_confirmation: confirmPassword
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Password reset successfully!');
                } else {
                    alert('Error: ' + data.message);
                }
            });
        } else {
            alert('Passwords do not match.');
        }
    } else {
        alert('Password must be at least 8 characters long.');
    }
}
</script>
@endpush
