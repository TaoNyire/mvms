@extends('layouts.admin')

@section('title', 'Edit User - Admin Panel')

@section('page-title', 'Edit User')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.users.index') }}">Users</a></li>
    <li class="breadcrumb-item active">Edit User</li>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-person-gear me-2"></i>Edit User: {{ $user->name }}
                    </h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.users.update', $user) }}">
                        @csrf
                        @method('PUT')
                        
                        <!-- Basic Information -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="name" class="form-label">Full Name</label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                       id="name" name="name" value="{{ old('name', $user->name) }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="email" class="form-label">Email Address</label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                       id="email" name="email" value="{{ old('email', $user->email) }}" required>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Profile Information (Read-only) -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Phone Number</label>
                                <input type="text" class="form-control" value="{{ $user->phone ?? 'Not provided' }}" readonly>
                                <small class="text-muted">Managed through user profile</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">District</label>
                                <input type="text" class="form-control" value="{{ $user->district ?? 'Not provided' }}" readonly>
                                <small class="text-muted">Managed through user profile</small>
                            </div>
                        </div>

                        <!-- Role Assignment -->
                        <div class="mb-3">
                            <label class="form-label">User Roles</label>
                            <div class="row">
                                @foreach($roles as $role)
                                    <div class="col-md-4">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" 
                                                   name="roles[]" value="{{ $role->id }}" 
                                                   id="role_{{ $role->id }}"
                                                   {{ $user->roles->contains($role->id) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="role_{{ $role->id }}">
                                                {{ ucfirst($role->name) }}
                                            </label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            @error('roles')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Account Status -->
                        <div class="mb-3">
                            <label for="account_status" class="form-label">Account Status</label>
                            <select class="form-select @error('account_status') is-invalid @enderror" 
                                    id="account_status" name="account_status" required>
                                <option value="active" {{ old('account_status', $user->account_status) === 'active' ? 'selected' : '' }}>
                                    Active
                                </option>
                                <option value="inactive" {{ old('account_status', $user->account_status) === 'inactive' ? 'selected' : '' }}>
                                    Inactive
                                </option>
                                <option value="suspended" {{ old('account_status', $user->account_status) === 'suspended' ? 'selected' : '' }}>
                                    Suspended
                                </option>
                                <option value="pending_approval" {{ old('account_status', $user->account_status) === 'pending_approval' ? 'selected' : '' }}>
                                    Pending Approval
                                </option>
                            </select>
                            @error('account_status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Admin Notes -->
                        <div class="mb-3">
                            <label for="admin_notes" class="form-label">Admin Notes</label>
                            <textarea class="form-control @error('admin_notes') is-invalid @enderror" 
                                      id="admin_notes" name="admin_notes" rows="3" 
                                      placeholder="Add any administrative notes about this user...">{{ old('admin_notes', $user->admin_notes) }}</textarea>
                            @error('admin_notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Form Actions -->
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('admin.users.show', $user) }}" class="btn btn-secondary">
                                <i class="bi bi-arrow-left me-1"></i>Back to User
                            </a>
                            <div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-lg me-1"></i>Update User
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- User Information Sidebar -->
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">User Information</h6>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center mx-auto" 
                             style="width: 80px; height: 80px; font-size: 2rem;">
                            {{ strtoupper(substr($user->name, 0, 2)) }}
                        </div>
                        <h5 class="mt-2 mb-0">{{ $user->name }}</h5>
                        <small class="text-muted">{{ $user->email }}</small>
                    </div>

                    <hr>

                    <div class="mb-2">
                        <strong>Current Status:</strong>
                        <span class="badge bg-{{ $user->is_active ? 'success' : 'danger' }} ms-2">
                            {{ $user->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </div>

                    <div class="mb-2">
                        <strong>Account Status:</strong>
                        <span class="badge bg-secondary ms-2">
                            {{ ucfirst(str_replace('_', ' ', $user->account_status)) }}
                        </span>
                    </div>

                    <div class="mb-2">
                        <strong>Roles:</strong>
                        <div class="mt-1">
                            @foreach($user->roles as $role)
                                <span class="badge bg-{{ $role->name === 'admin' ? 'danger' : ($role->name === 'organization' ? 'primary' : 'success') }} me-1">
                                    {{ ucfirst($role->name) }}
                                </span>
                            @endforeach
                        </div>
                    </div>

                    <div class="mb-2">
                        <strong>Registration:</strong>
                        <small class="text-muted d-block">{{ $user->created_at->format('M j, Y g:i A') }}</small>
                    </div>

                    <div class="mb-2">
                        <strong>Last Login:</strong>
                        <small class="text-muted d-block">
                            {{ $user->last_login_at ? $user->last_login_at->format('M j, Y g:i A') : 'Never' }}
                        </small>
                    </div>

                    @if($user->status_reason)
                        <div class="mb-2">
                            <strong>Status Reason:</strong>
                            <small class="text-muted d-block">{{ $user->status_reason }}</small>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card mt-3">
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
