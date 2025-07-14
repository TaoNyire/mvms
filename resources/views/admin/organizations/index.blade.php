@extends('layouts.admin')

@section('title', 'Organization Approval - Admin Panel')

@section('page-title', 'Organization Approval')

@section('breadcrumb')
    <li class="breadcrumb-item active">Organizations</li>
@endsection

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-md-8">
            <h2>
                <i class="bi bi-building me-2"></i>Organization Approval
            </h2>
            <p>Review and approve organization registrations.</p>
        </div>
        <div class="col-md-4 text-md-end">
            <div class="btn-group" role="group">
                <button type="button" class="btn btn-outline-primary" onclick="showBulkActions()">
                    <i class="bi bi-check2-square me-1"></i>Bulk Actions
                </button>
                <button type="button" class="btn btn-primary" onclick="exportOrganizations()">
                    <i class="bi bi-download me-1"></i>Export
                </button>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-warning">
                <div class="card-body text-center">
                    <h3 class="text-warning">{{ $organizations->where('status', 'pending')->count() }}</h3>
                    <p class="mb-0">Pending Approval</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-success">
                <div class="card-body text-center">
                    <h3 class="text-success">{{ $organizations->where('status', 'approved')->count() }}</h3>
                    <p class="mb-0">Approved</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-danger">
                <div class="card-body text-center">
                    <h3 class="text-danger">{{ $organizations->where('status', 'rejected')->count() }}</h3>
                    <p class="mb-0">Rejected</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-info">
                <div class="card-body text-center">
                    <h3 class="text-info">{{ $organizations->where('status', 'suspended')->count() }}</h3>
                    <p class="mb-0">Suspended</p>
                </div>
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
                            <select name="status" class="form-select" onchange="this.form.submit()">
                                <option value="all" {{ request('status') === 'all' ? 'selected' : '' }}>All Status</option>
                                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                                <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                                <option value="suspended" {{ request('status') === 'suspended' ? 'selected' : '' }}>Suspended</option>
                                <option value="info_requested" {{ request('status') === 'info_requested' ? 'selected' : '' }}>Info Requested</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <div class="input-group">
                                <input type="text" name="search" class="form-control" 
                                       placeholder="Search by organization name, registration number, or contact..." 
                                       value="{{ request('search') }}">
                                <button type="submit" class="btn btn-outline-secondary">
                                    <i class="bi bi-search"></i>
                                </button>
                            </div>
                        </div>
                        <div class="col-md-3 text-end">
                            <small class="text-muted">{{ $organizations->total() }} organizations found</small>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Organizations Table -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body p-0">
                    @if($organizations->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th width="40">
                                            <input type="checkbox" id="selectAll" class="form-check-input">
                                        </th>
                                        <th>Organization</th>
                                        <th>Contact Person</th>
                                        <th>Status</th>
                                        <th>Registration Date</th>
                                        <th width="150">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($organizations as $org)
                                        <tr>
                                            <td>
                                                <input type="checkbox" class="form-check-input org-checkbox" 
                                                       value="{{ $org->id }}" data-org-id="{{ $org->id }}">
                                            </td>
                                            <td>
                                                <div>
                                                    <strong>{{ $org->organization_name }}</strong>
                                                    <br>
                                                    <small class="text-muted">{{ $org->org_type }}</small>
                                                    @if($org->registration_number)
                                                        <br>
                                                        <small class="text-muted">Reg: {{ $org->registration_number }}</small>
                                                    @endif
                                                </div>
                                            </td>
                                            <td>
                                                <div>
                                                    {{ $org->user->name }}
                                                    <br>
                                                    <small class="text-muted">{{ $org->user->email }}</small>
                                                    @if($org->user->phone)
                                                        <br>
                                                        <small class="text-muted">{{ $org->user->phone }}</small>
                                                    @endif
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-{{ 
                                                    $org->status === 'approved' ? 'success' : 
                                                    ($org->status === 'rejected' ? 'danger' : 
                                                    ($org->status === 'suspended' ? 'warning' : 
                                                    ($org->status === 'info_requested' ? 'info' : 'secondary'))) 
                                                }}">
                                                    {{ ucfirst(str_replace('_', ' ', $org->status)) }}
                                                </span>
                                                @if($org->status === 'pending')
                                                    <br>
                                                    <small class="text-warning">
                                                        <i class="bi bi-clock"></i> Awaiting Review
                                                    </small>
                                                @endif
                                            </td>
                                            <td>
                                                {{ $org->created_at->format('M j, Y') }}
                                                <br>
                                                <small class="text-muted">{{ $org->created_at->diffForHumans() }}</small>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('admin.organizations.show', $org) }}" 
                                                       class="btn btn-sm btn-outline-primary" title="View Details">
                                                        <i class="bi bi-eye"></i>
                                                    </a>
                                                    
                                                    @if($org->status === 'pending')
                                                        <button type="button" class="btn btn-sm btn-outline-success" 
                                                                onclick="approveOrganization({{ $org->id }})" title="Approve">
                                                            <i class="bi bi-check"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-sm btn-outline-danger" 
                                                                onclick="rejectOrganization({{ $org->id }})" title="Reject">
                                                            <i class="bi bi-x"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-sm btn-outline-info" 
                                                                onclick="requestInfo({{ $org->id }})" title="Request Info">
                                                            <i class="bi bi-question-circle"></i>
                                                        </button>
                                                    @elseif($org->status === 'approved')
                                                        <button type="button" class="btn btn-sm btn-outline-warning" 
                                                                onclick="suspendOrganization({{ $org->id }})" title="Suspend">
                                                            <i class="bi bi-pause"></i>
                                                        </button>
                                                    @elseif($org->status === 'suspended')
                                                        <button type="button" class="btn btn-sm btn-outline-success" 
                                                                onclick="reactivateOrganization({{ $org->id }})" title="Reactivate">
                                                            <i class="bi bi-play"></i>
                                                        </button>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Pagination -->
                        <div class="d-flex justify-content-center p-3">
                            {{ $organizations->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="bi bi-building" style="font-size: 4rem; color: #dee2e6;"></i>
                            <h4 class="mt-3">No Organizations Found</h4>
                            <p class="text-muted">No organizations match your current filters.</p>
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
                            <option value="approve">Approve Organizations</option>
                            <option value="reject">Reject Organizations</option>
                            <option value="suspend">Suspend Organizations</option>
                        </select>
                    </div>
                    <div class="mb-3" id="reasonField" style="display: none;">
                        <label for="actionReason" class="form-label">Reason</label>
                        <textarea class="form-control" id="actionReason" rows="3" 
                                  placeholder="Provide a reason for this action..."></textarea>
                    </div>
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        <span id="selectedCount">0</span> organizations selected for bulk action.
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
    const checkboxes = document.querySelectorAll('.org-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.checked = this.checked;
    });
    updateSelectedCount();
});

document.querySelectorAll('.org-checkbox').forEach(checkbox => {
    checkbox.addEventListener('change', updateSelectedCount);
});

function updateSelectedCount() {
    const selected = document.querySelectorAll('.org-checkbox:checked').length;
    document.getElementById('selectedCount').textContent = selected;
}

function showBulkActions() {
    const selected = document.querySelectorAll('.org-checkbox:checked').length;
    if (selected === 0) {
        alert('Please select at least one organization.');
        return;
    }
    
    updateSelectedCount();
    const modal = new bootstrap.Modal(document.getElementById('bulkActionsModal'));
    modal.show();
}

document.getElementById('bulkAction').addEventListener('change', function() {
    const reasonField = document.getElementById('reasonField');
    const needsReason = ['reject', 'suspend'].includes(this.value);
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
    const selectedOrgs = Array.from(document.querySelectorAll('.org-checkbox:checked')).map(cb => cb.value);
    
    if (!action) {
        alert('Please select an action.');
        return;
    }
    
    if (['reject', 'suspend'].includes(action) && !reason.trim()) {
        alert('Please provide a reason for this action.');
        return;
    }
    
    if (!confirm(`Are you sure you want to ${action} ${selectedOrgs.length} organizations?`)) {
        return;
    }
    
    fetch('/admin/organizations/bulk-action', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            action: action,
            organization_ids: selectedOrgs,
            reason: reason
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
        alert('An error occurred while executing the bulk action.');
    });
}

// Individual organization actions
function approveOrganization(orgId) {
    const notes = prompt('Add any notes for this approval (optional):');
    
    fetch(`/admin/organizations/${orgId}/approve`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            admin_notes: notes || ''
        })
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
        });
    }
}

function requestInfo(orgId) {
    const message = prompt('What additional information do you need?');
    if (message && message.trim()) {
        fetch(`/admin/organizations/${orgId}/request-info`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                message: message.trim()
            })
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

function suspendOrganization(orgId) {
    const reason = prompt('Please provide a reason for suspension:');
    if (reason && reason.trim()) {
        fetch(`/admin/organizations/${orgId}/suspend`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                reason: reason.trim()
            })
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

function reactivateOrganization(orgId) {
    const notes = prompt('Add any notes for reactivation (optional):');
    
    fetch(`/admin/organizations/${orgId}/reactivate`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            notes: notes || ''
        })
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

function exportOrganizations() {
    // This would implement organization export functionality
    alert('Export functionality would be implemented here.');
}
</script>
@endpush
