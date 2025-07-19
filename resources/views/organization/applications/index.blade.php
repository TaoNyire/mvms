@extends('layouts.organization')

@section('title', 'Applications - MVMS')

@section('page-title', 'Volunteer Applications')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 bg-gradient" style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%);">
                <div class="card-body text-white p-4">
                    <h2 class="mb-2">
                        <i class="bi bi-people me-2"></i>Volunteer Applications
                    </h2>
                    <p class="mb-0">Manage applications from volunteers for your opportunities</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Success/Error Messages -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            <div class="d-flex align-items-center">
                <div class="flex-shrink-0">
                    <i class="bi bi-check-circle-fill fs-4 text-success"></i>
                </div>
                <div class="flex-grow-1 ms-3">
                    <h6 class="alert-heading mb-1">Success!</h6>
                    <p class="mb-0">{{ session('success') }}</p>
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
            <div class="d-flex align-items-center">
                <div class="flex-shrink-0">
                    <i class="bi bi-exclamation-triangle-fill fs-4 text-danger"></i>
                </div>
                <div class="flex-grow-1 ms-3">
                    <h6 class="alert-heading mb-1">Error!</h6>
                    <p class="mb-0">{{ session('error') }}</p>
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Applications List -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bi bi-inbox me-2"></i>Applications Received
                        <span class="badge bg-primary ms-2">{{ $applications->total() }}</span>
                    </h5>
                    <div class="d-flex gap-2">
                        <div class="dropdown">
                            <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                <i class="bi bi-funnel me-1"></i>Filter
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="?status=pending">Pending</a></li>
                                <li><a class="dropdown-item" href="?status=accepted">Accepted</a></li>
                                <li><a class="dropdown-item" href="?status=rejected">Rejected</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="{{ route('organization.applications.index') }}">All Applications</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    @if($applications->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Volunteer</th>
                                        <th>Opportunity</th>
                                        <th>Applied Date</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($applications as $application)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar-circle bg-primary text-white me-3">
                                                        {{ strtoupper(substr($application->volunteer->name, 0, 1)) }}
                                                    </div>
                                                    <div>
                                                        <h6 class="mb-1">
                                                            {{ $application->volunteer->volunteerProfile->full_name ?? $application->volunteer->name }}
                                                        </h6>
                                                        <small class="text-muted">
                                                            <i class="bi bi-envelope me-1"></i>{{ $application->volunteer->email }}
                                                        </small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <a href="{{ route('opportunities.show', $application->opportunity) }}" class="text-decoration-none">
                                                    <h6 class="mb-1">{{ $application->opportunity->title }}</h6>
                                                </a>
                                                <small class="text-muted">
                                                    <i class="bi bi-calendar me-1"></i>{{ $application->opportunity->start_date->format('M d, Y') }}
                                                </small>
                                            </td>
                                            <td>
                                                <span class="text-muted">{{ $application->applied_at->format('M d, Y') }}</span>
                                                <br>
                                                <small class="text-muted">{{ $application->applied_at->format('h:i A') }}</small>
                                            </td>
                                            <td>
                                                @if($application->status === 'pending')
                                                    <span class="badge bg-warning">
                                                        <i class="bi bi-clock me-1"></i>Pending
                                                    </span>
                                                @elseif($application->status === 'accepted')
                                                    <span class="badge bg-success">
                                                        <i class="bi bi-check-circle me-1"></i>Accepted
                                                    </span>
                                                @elseif($application->status === 'rejected')
                                                    <span class="badge bg-danger">
                                                        <i class="bi bi-x-circle me-1"></i>Rejected
                                                    </span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('applications.show', $application) }}" 
                                                       class="btn btn-outline-primary btn-sm" 
                                                       title="View Details">
                                                        <i class="bi bi-eye"></i>
                                                    </a>
                                                    @if($application->status === 'pending')
                                                        <form action="{{ route('applications.accept', $application) }}" method="POST" class="d-inline">
                                                            @csrf
                                                            <button type="submit" 
                                                                    class="btn btn-outline-success btn-sm" 
                                                                    title="Accept Application"
                                                                    onclick="return confirm('Accept this application?')">
                                                                <i class="bi bi-check"></i>
                                                            </button>
                                                        </form>
                                                        <form action="{{ route('applications.reject', $application) }}" method="POST" class="d-inline">
                                                            @csrf
                                                            <button type="submit" 
                                                                    class="btn btn-outline-danger btn-sm" 
                                                                    title="Reject Application"
                                                                    onclick="return confirm('Reject this application?')">
                                                                <i class="bi bi-x"></i>
                                                            </button>
                                                        </form>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="bi bi-inbox display-1 text-muted"></i>
                            <h4 class="mt-3 text-muted">No Applications Yet</h4>
                            <p class="text-muted">Applications from volunteers will appear here when they apply for your opportunities.</p>
                            <a href="{{ route('opportunities.index') }}" class="btn btn-primary">
                                <i class="bi bi-briefcase me-1"></i>View Your Opportunities
                            </a>
                        </div>
                    @endif
                </div>
                @if($applications->hasPages())
                    <div class="card-footer bg-white">
                        {{ $applications->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .avatar-circle {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        font-size: 16px;
    }
    
    .table th {
        border-top: none;
        font-weight: 600;
        color: #495057;
    }
    
    .btn-group .btn {
        border-radius: 0.375rem;
        margin-right: 2px;
    }
    
    .btn-group .btn:last-child {
        margin-right: 0;
    }
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // Auto-dismiss alerts after 5 seconds
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 5000);
});
</script>
@endpush
@endsection
