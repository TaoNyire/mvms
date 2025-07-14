@extends('layouts.admin')

@section('title', 'Organization Details - Admin Panel')

@section('page-title', 'Organization Details')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.organizations.index') }}">Organizations</a></li>
    <li class="breadcrumb-item active">{{ $organization->organization_name ?? $organization->org_name }}</li>
@endsection

@section('content')
<div class="container-fluid">
    <!-- Organization Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-4" 
                             style="width: 80px; height: 80px; font-size: 2rem;">
                            {{ strtoupper(substr($organization->organization_name ?? $organization->org_name ?? 'ORG', 0, 2)) }}
                        </div>
                        <div class="flex-grow-1">
                            <h2 class="mb-1">{{ $organization->organization_name ?? $organization->org_name }}</h2>
                            <p class="text-muted mb-2">{{ $organization->user->email }}</p>
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-{{ 
                                    $organization->status === 'approved' ? 'success' : 
                                    ($organization->status === 'rejected' ? 'danger' : 
                                    ($organization->status === 'suspended' ? 'warning' : 'secondary')) 
                                }}">
                                    {{ ucfirst($organization->status) }}
                                </span>
                                <span class="badge bg-{{ $organization->user->is_active ? 'success' : 'danger' }}">
                                    {{ $organization->user->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </div>
                        </div>
                        <div class="text-end">
                            @if($organization->status === 'pending')
                                <button type="button" class="btn btn-success me-2" onclick="approveOrganization({{ $organization->id }})">
                                    <i class="bi bi-check me-1"></i>Approve
                                </button>
                                <button type="button" class="btn btn-danger me-2" onclick="rejectOrganization({{ $organization->id }})">
                                    <i class="bi bi-x me-1"></i>Reject
                                </button>
                                <button type="button" class="btn btn-info me-2" onclick="requestInfo({{ $organization->id }})">
                                    <i class="bi bi-question-circle me-1"></i>Request Info
                                </button>
                            @elseif($organization->status === 'approved')
                                <button type="button" class="btn btn-warning me-2" onclick="suspendOrganization({{ $organization->id }})">
                                    <i class="bi bi-pause me-1"></i>Suspend
                                </button>
                            @elseif($organization->status === 'suspended')
                                <button type="button" class="btn btn-success me-2" onclick="reactivateOrganization({{ $organization->id }})">
                                    <i class="bi bi-play me-1"></i>Reactivate
                                </button>
                            @endif
                            <a href="{{ route('admin.organizations.index') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left me-1"></i>Back to Organizations
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Organization Information -->
        <div class="col-lg-8">
            <!-- Basic Information -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-building me-2"></i>Organization Information
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Organization Name:</strong></td>
                                    <td>{{ $organization->org_name ?? 'Not provided' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Type:</strong></td>
                                    <td>{{ $organization->org_type ?? 'Not specified' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Registration Number:</strong></td>
                                    <td>{{ $organization->registration_number ?? 'Not provided' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Email:</strong></td>
                                    <td>{{ $organization->email ?? $organization->user->email }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Phone:</strong></td>
                                    <td>{{ $organization->phone ?? $organization->user->phone ?? 'Not provided' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>District:</strong></td>
                                    <td>{{ $organization->district ?? $organization->user->district ?? 'Not provided' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Website:</strong></td>
                                    <td>
                                        @if($organization->website)
                                            <a href="{{ $organization->website }}" target="_blank">{{ $organization->website }}</a>
                                        @else
                                            Not provided
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Registration Date:</strong></td>
                                    <td>{{ $organization->created_at->format('M j, Y g:i A') }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    
                    @if($organization->description)
                        <div class="mt-3">
                            <p><strong>Description:</strong></p>
                            <p class="text-muted">{{ $organization->description }}</p>
                        </div>
                    @endif

                    @if($organization->mission)
                        <div class="mt-3">
                            <p><strong>Mission:</strong></p>
                            <p class="text-muted">{{ $organization->mission }}</p>
                        </div>
                    @endif

                    @if($organization->vision)
                        <div class="mt-3">
                            <p><strong>Vision:</strong></p>
                            <p class="text-muted">{{ $organization->vision }}</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Contact Information -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-person-lines-fill me-2"></i>Contact Information
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Contact Person:</strong></td>
                                    <td>{{ $organization->contact_person_name ?? $organization->user->name }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Title:</strong></td>
                                    <td>{{ $organization->contact_person_title ?? 'Not specified' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Phone:</strong></td>
                                    <td>{{ $organization->contact_person_phone ?? $organization->phone ?? 'Not provided' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Email:</strong></td>
                                    <td>{{ $organization->contact_person_email ?? $organization->user->email }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Physical Address:</strong></td>
                                    <td>{{ $organization->physical_address ?? 'Not provided' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Postal Address:</strong></td>
                                    <td>{{ $organization->postal_address ?? 'Not provided' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Region:</strong></td>
                                    <td>{{ $organization->region ?? 'Not specified' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>District:</strong></td>
                                    <td>{{ $organization->district ?? 'Not specified' }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Admin Notes -->
            @if($organization->admin_notes)
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="bi bi-sticky me-2"></i>Admin Notes
                        </h5>
                    </div>
                    <div class="card-body">
                        <p class="mb-0">{{ $organization->admin_notes }}</p>
                    </div>
                </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Status Information -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">Status Information</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <strong>Current Status:</strong>
                        <span class="badge bg-{{ 
                            $organization->status === 'approved' ? 'success' : 
                            ($organization->status === 'rejected' ? 'danger' : 
                            ($organization->status === 'suspended' ? 'warning' : 'secondary')) 
                        }} ms-2">
                            {{ ucfirst($organization->status) }}
                        </span>
                    </div>

                    <div class="mb-3">
                        <strong>Profile Complete:</strong>
                        <span class="badge bg-{{ $organization->is_complete ? 'success' : 'warning' }} ms-2">
                            {{ $organization->is_complete ? 'Complete' : 'Incomplete' }}
                        </span>
                    </div>

                    <div class="mb-3">
                        <strong>Verified:</strong>
                        <span class="badge bg-{{ $organization->is_verified ? 'success' : 'warning' }} ms-2">
                            {{ $organization->is_verified ? 'Verified' : 'Not Verified' }}
                        </span>
                    </div>

                    @if($organization->approved_at)
                        <div class="mb-3">
                            <strong>Approved:</strong>
                            <small class="text-muted d-block">{{ $organization->approved_at->format('M j, Y g:i A') }}</small>
                        </div>
                    @endif

                    @if($organization->rejected_at)
                        <div class="mb-3">
                            <strong>Rejected:</strong>
                            <small class="text-muted d-block">{{ $organization->rejected_at->format('M j, Y g:i A') }}</small>
                        </div>
                    @endif

                    @if($organization->rejection_reason)
                        <div class="mb-3">
                            <strong>Rejection Reason:</strong>
                            <p class="text-muted mb-0">{{ $organization->rejection_reason }}</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- User Account Information -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">User Account</h6>
                </div>
                <div class="card-body">
                    <div class="mb-2">
                        <strong>Account Status:</strong>
                        <span class="badge bg-{{ $organization->user->is_active ? 'success' : 'danger' }} ms-2">
                            {{ $organization->user->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </div>
                    <div class="mb-2">
                        <strong>Email Verified:</strong>
                        <span class="badge bg-{{ $organization->user->email_verified_at ? 'success' : 'warning' }} ms-2">
                            {{ $organization->user->email_verified_at ? 'Verified' : 'Not Verified' }}
                        </span>
                    </div>
                    <div class="mb-2">
                        <strong>Last Login:</strong>
                        <small class="text-muted d-block">
                            {{ $organization->user->last_login_at ? $organization->user->last_login_at->format('M j, Y g:i A') : 'Never' }}
                        </small>
                    </div>
                    <div class="mb-2">
                        <strong>Registration:</strong>
                        <small class="text-muted d-block">{{ $organization->user->created_at->format('M j, Y g:i A') }}</small>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">Quick Actions</h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('admin.users.show', $organization->user) }}" class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-person me-1"></i>View User Account
                        </a>
                        @if($organization->status === 'pending')
                            <button type="button" class="btn btn-outline-success btn-sm" onclick="approveOrganization({{ $organization->id }})">
                                <i class="bi bi-check me-1"></i>Approve Organization
                            </button>
                            <button type="button" class="btn btn-outline-danger btn-sm" onclick="rejectOrganization({{ $organization->id }})">
                                <i class="bi bi-x me-1"></i>Reject Organization
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
</script>
@endpush
