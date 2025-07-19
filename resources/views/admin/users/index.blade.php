@extends('layouts.admin')

@section('title', 'User Management - Admin Panel')

@section('page-title', 'User Management')

@section('breadcrumb')
    <li class="breadcrumb-item active">Users</li>
@endsection

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-md-8">
            <h2>
                <i class="bi bi-people me-2"></i>User Management
            </h2>
            <p>Manage user accounts, roles, and permissions.</p>
        </div>
        <div class="col-md-4 text-md-end">
            <div class="btn-group" role="group">
                <button type="button" class="btn btn-outline-primary" onclick="showBulkActions()">
                    <i class="bi bi-check2-square me-1"></i>Bulk Actions
                </button>
    </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="card">
                <div class="card-body py-3">
                    <form method="GET" class="row g-3 align-items-center">
                        <div class="col-md-3">
                            <select name="role" class="form-select" onchange="this.form.submit()">
                                <option value="all" {{ request('role') === 'all' ? 'selected' : '' }}>All Roles</option>
                                <option value="volunteer" {{ request('role') === 'volunteer' ? 'selected' : '' }}>Volunteers</option>
                                <option value="organization" {{ request('role') === 'organization' ? 'selected' : '' }}>Organizations</option>
                                <option value="admin" {{ request('role') === 'admin' ? 'selected' : '' }}>Administrators</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select name="status" class="form-select" onchange="this.form.submit()">
                                <option value="all" {{ request('status') === 'all' ? 'selected' : '' }}>All Status</option>
                                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                                <option value="suspended" {{ request('status') === 'suspended' ? 'selected' : '' }}>Suspended</option>
                                <option value="pending_approval" {{ request('status') === 'pending_approval' ? 'selected' : '' }}>Pending Approval</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <div class="input-group">
                                <input type="text" name="search" class="form-control" 
                                       placeholder="Search by name or email..." 
                                       value="{{ request('search') }}">
                                <button type="submit" class="btn btn-outline-secondary">
                                    <i class="bi bi-search"></i>
                                </button>
                            </div>
                        </div>
                        <div class="col-md-2 text-end">
                            <small class="text-muted">{{ $users->total() }} users found</small>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Users Table -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body p-0">
                    @if($users->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th width="40">
                                            <input type="checkbox" id="selectAll" class="form-check-input">
                                        </th>
                                        <th>User</th>
                                        <th>Role</th>
                                        <th>Status</th>
                                        <th>Registration</th>
                                        <th>Last Login</th>
                                        <th width="120">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($users as $user)
                                        <tr>
                                            <td>
                                                <input type="checkbox" class="form-check-input user-checkbox" 
                                                       value="{{ $user->id }}" data-user-id="{{ $user->id }}">
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-3" 
                                                         style="width: 40px; height: 40px;">
                                                        {{ strtoupper(substr($user->name, 0, 2)) }}
                                                    </div>
                                                    <div>
                                                        <strong>{{ $user->name }}</strong>
                                                        <br>
                                                        <small class="text-muted">{{ $user->email }}</small>
                                                        @if($user->phone)
                                                            <br>
                                                            <small class="text-muted">{{ $user->phone }}</small>
                                                        @endif
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                @foreach($user->roles as $role)
                                                    <span class="badge bg-{{ $role->name === 'admin' ? 'danger' : ($role->name === 'organization' ? 'primary' : 'success') }}">
                                                        {{ ucfirst($role->name) }}
                                                    </span>
                                                @endforeach
                                            </td>
                                            <td>
                                                <div>
                                                    <span class="badge bg-{{ $user->is_active ? 'success' : 'danger' }}">
                                                        {{ $user->is_active ? 'Active' : 'Inactive' }}
                                                    </span>
                                                    @if($user->account_status !== 'active')
                                                        <br>
                                                        <small class="text-muted">{{ ucfirst(str_replace('_', ' ', $user->account_status)) }}</small>
                                                    @endif
                                                </div>
                                            </td>
                                            <td>
                                                {{ $user->created_at->format('M j, Y') }}
                                                <br>
                                                <small class="text-muted">{{ $user->created_at->diffForHumans() }}</small>
                                            </td>
                                            <td>
                                                @if($user->last_login_at)
                                                    {{ $user->last_login_at->format('M j, Y') }}
                                                    <br>
                                                    <small class="text-muted">{{ $user->last_login_at->diffForHumans() }}</small>
                                                @else
                                                    <span class="text-muted">Never</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('admin.users.show', $user) }}" 
                                                       class="btn btn-sm btn-outline-primary" title="View Details">
                                                        <i class="bi bi-eye"></i>
                                                    </a>
                                                    <a href="{{ route('admin.users.edit', $user) }}" 
                                                       class="btn btn-sm btn-outline-secondary" title="Edit User">
                                                        <i class="bi bi-pencil"></i>
                                                    </a>
                                                    <div class="btn-group" role="group">
                                                        <button type="button" class="btn btn-sm btn-outline-danger dropdown-toggle" 
                                                                data-bs-toggle="dropdown" title="More Actions">
                                                            <i class="bi bi-three-dots"></i>
                                                        </button>
                                                        <ul class="dropdown-menu">
                                                            @if($user->is_active)
                                                                <li>
                                                                    <button class="dropdown-item" onclick="deactivateUser({{ $user->id }})">
                                                                        <i class="bi bi-pause-circle me-2"></i>Deactivate
                                                                    </button>
                                                                </li>
                                                                <li>
                                                                    <button class="dropdown-item" onclick="suspendUser({{ $user->id }})">
                                                                        <i class="bi bi-ban me-2"></i>Suspend
                                                                    </button>
                                                                </li>
                                                            @else
                                                                <li>
                                                                    <button class="dropdown-item" onclick="activateUser({{ $user->id }})">
                                                                        <i class="bi bi-play-circle me-2"></i>Activate
                                                                    </button>
                                                                </li>
                                                            @endif
                                                            <li><hr class="dropdown-divider"></li>
                                                            <li>
                                                                <button class="dropdown-item" onclick="resetPassword({{ $user->id }})">
                                                                    <i class="bi bi-key me-2"></i>Reset Password
                                                                </button>
                                                            </li>
                                                            @if(!$user->hasRole('admin'))
                                                                <li><hr class="dropdown-divider"></li>
                                                                <li>
                                                                    <button class="dropdown-item text-danger" onclick="deleteUser({{ $user->id }})">
                                                                        <i class="bi bi-trash me-2"></i>Delete User
                                                                    </button>
                                                                </li>
                                                            @endif
                                                        </ul>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Pagination -->
                        <div class="d-flex justify-content-center p-3">
                            {{ $users->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="bi bi-people" style="font-size: 4rem; color: #dee2e6;"></i>
                            <h4 class="mt-3">No Users Found</h4>
                            <p class="text-muted">No users match your current filters.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bulk Actions Modal -->
<div class="modal fade" id="bulkActionsModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Bulk Actions</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="bulkActionForm">
                    <div class="mb-3">
                        <label for="bulkAction" class="form-label">Select Action</label>
                        <select class="form-select" id="bulkAction" required>
                            <option value="">Choose action...</option>
                            <option value="activate">Activate Users</option>
                            <option value="deactivate">Deactivate Users</option>
                            <option value="suspend">Suspend Users</option>
                            <option value="delete">Delete Users</option>
                        </select>
                    </div>
                    <div class="mb-3" id="reasonField" style="display: none;">
                        <label for="actionReason" class="form-label">Reason</label>
                        <textarea class="form-control" id="actionReason" rows="3" 
                                  placeholder="Provide a reason for this action..."></textarea>
                    </div>
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        <span id="selectedCount">0</span> users selected for bulk action.
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="executeBulkAction()">Execute Action</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Checkbox handling
document.getElementById('selectAll').addEventListener('change', function() {
    const checkboxes = document.querySelectorAll('.user-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.checked = this.checked;
    });
    updateSelectedCount();
});

document.querySelectorAll('.user-checkbox').forEach(checkbox => {

<!-- Toast Container -->
<div id="toastContainer" class="position-fixed bottom-0 end-0 p-3" style="z-index: 9999;"></div>
<script>
// ...existing code...
function showToast(message, type = 'info') {
    const toastContainer = document.getElementById('toastContainer');
    const toastId = 'toast-' + Date.now();
    const bgClass = type === 'success' ? 'bg-success' : type === 'danger' ? 'bg-danger' : type === 'warning' ? 'bg-warning' : 'bg-info';
    const html = `
        <div id="${toastId}" class="toast align-items-center text-white ${bgClass} border-0 mb-2" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body">
                    ${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    `;
    toastContainer.insertAdjacentHTML('beforeend', html);
    const toastEl = document.getElementById(toastId);
    const toast = new bootstrap.Toast(toastEl, { delay: 4000 });
    toast.show();
    toastEl.addEventListener('hidden.bs.toast', () => toastEl.remove());
}

// Checkbox handling
document.getElementById('selectAll').addEventListener('change', function() {
    const checkboxes = document.querySelectorAll('.user-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.checked = this.checked;
    });
    updateSelectedCount();
});

document.querySelectorAll('.user-checkbox').forEach(checkbox => {
    checkbox.addEventListener('change', updateSelectedCount);
});

function updateSelectedCount() {
    const selected = document.querySelectorAll('.user-checkbox:checked').length;
    document.getElementById('selectedCount').textContent = selected;
}

function showBulkActions() {
    const selected = document.querySelectorAll('.user-checkbox:checked').length;
    if (selected === 0) {
        showToast('Please select at least one user.', 'warning');
        return;
    }
    updateSelectedCount();
    const modal = new bootstrap.Modal(document.getElementById('bulkActionsModal'));
    modal.show();
}

document.getElementById('bulkAction').addEventListener('change', function() {
    const reasonField = document.getElementById('reasonField');
    const needsReason = ['deactivate', 'suspend', 'delete'].includes(this.value);
    reasonField.style.display = needsReason ? 'block' : 'none';
    if (needsReason) {
        document.getElementById('actionReason').required = true;
    } else {
        document.getElementById('actionReason').required = false;
    }
});

function executeBulkAction() {
    const action = document.getElementById('bulkAction').value;
    const reason = document.getElementById('actionReason').value;
    const selectedUsers = Array.from(document.querySelectorAll('.user-checkbox:checked')).map(cb => cb.value);
    if (!action) {
        showToast('Please select an action.', 'warning');
        return;
    }
    if (['deactivate', 'suspend', 'delete'].includes(action) && !reason.trim()) {
        showToast('Please provide a reason for this action.', 'warning');
        return;
    }
    if (!confirm(`Are you sure you want to ${action} ${selectedUsers.length} users?`)) {
        return;
    }
    fetch('/admin/users/bulk-action', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            action: action,
            user_ids: selectedUsers,
            reason: reason
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            showToast('Error: ' + data.message, 'danger');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('An error occurred while executing the bulk action.', 'danger');
    });
}

// Individual user actions
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
                showToast('Error: ' + data.message, 'danger');
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
                showToast('Error: ' + data.message, 'danger');
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
                showToast('Error: ' + data.message, 'danger');
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
                    location.reload();
                } else {
                    showToast('Error: ' + data.message, 'danger');
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
                    showToast('Password reset successfully!', 'success');
                } else {
                    showToast('Error: ' + data.message, 'danger');
                }
            });
        } else {
            showToast('Passwords do not match.', 'warning');
        }
    } else {
        showToast('Password must be at least 8 characters long.', 'warning');
    }
}

    // exportUsers function removed
</script>
@endpush
