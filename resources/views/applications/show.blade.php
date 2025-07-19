@extends(auth()->user()->hasRole('volunteer') ? 'layouts.volunteer' : 'layouts.organization')

@section('title', 'Application Details - MVMS')

@section('page-title', 'Application Details')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 bg-gradient" style="background: linear-gradient(135deg, #6f42c1 0%, #e83e8c 100%);">
                <div class="card-body text-white p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="mb-2">
                                <i class="bi bi-file-person me-2"></i>Application Details
                            </h2>
                            <p class="mb-0">Review volunteer application information</p>
                        </div>
                        <div class="text-end">
                            @if(auth()->user()->hasRole('volunteer'))
                                <a href="{{ route('volunteer.applications.index') }}" class="btn btn-light">
                                    <i class="bi bi-arrow-left me-1"></i>Back to My Applications
                                </a>
                            @else
                                <a href="{{ route('organization.applications.index') }}" class="btn btn-light">
                                    <i class="bi bi-arrow-left me-1"></i>Back to Applications
                                </a>
                            @endif
                        </div>
                    </div>
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

    <div class="row">
        <!-- Application Status -->
        <div class="col-12 mb-4">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="mb-1">Application Status</h5>
                            <p class="text-muted mb-0">Applied on {{ $application->applied_at->format('F d, Y \a\t h:i A') }}</p>
                        </div>
                        <div class="text-end">
                            @if($application->status === 'pending')
                                <span class="badge bg-warning fs-6 px-3 py-2">
                                    <i class="bi bi-clock me-1"></i>Pending Review
                                </span>
                            @elseif($application->status === 'accepted')
                                <span class="badge bg-success fs-6 px-3 py-2">
                                    <i class="bi bi-check-circle me-1"></i>Accepted
                                </span>
                            @elseif($application->status === 'rejected')
                                <span class="badge bg-danger fs-6 px-3 py-2">
                                    <i class="bi bi-x-circle me-1"></i>Rejected
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Opportunity Details -->
        <div class="col-md-6 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0">
                        <i class="bi bi-briefcase me-2"></i>Opportunity Details
                    </h6>
                </div>
                <div class="card-body">
                    <h5 class="card-title">{{ $application->opportunity->title }}</h5>
                    <p class="card-text">{{ Str::limit($application->opportunity->description, 150) }}</p>
                    
                    <div class="row text-sm">
                        <div class="col-6 mb-2">
                            <strong>Category:</strong><br>
                            <span class="text-muted">{{ $application->opportunity->category }}</span>
                        </div>
                        <div class="col-6 mb-2">
                            <strong>Type:</strong><br>
                            <span class="text-muted">{{ ucfirst($application->opportunity->type) }}</span>
                        </div>
                        <div class="col-6 mb-2">
                            <strong>Start Date:</strong><br>
                            <span class="text-muted">{{ $application->opportunity->start_date->format('M d, Y') }}</span>
                        </div>
                        <div class="col-6 mb-2">
                            <strong>Location:</strong><br>
                            <span class="text-muted">{{ $application->opportunity->district }}, {{ $application->opportunity->region }}</span>
                        </div>
                    </div>
                    
                    <div class="mt-3">
                        <a href="{{ route('opportunities.show', $application->opportunity) }}" class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-eye me-1"></i>View Full Opportunity
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Volunteer Details -->
        <div class="col-md-6 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-success text-white">
                    <h6 class="mb-0">
                        <i class="bi bi-person me-2"></i>Volunteer Details
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="avatar-circle bg-success text-white me-3">
                            {{ strtoupper(substr($application->volunteer->name, 0, 1)) }}
                        </div>
                        <div>
                            <h5 class="mb-1">{{ $application->volunteer->volunteerProfile->full_name ?? $application->volunteer->name }}</h5>
                            <p class="text-muted mb-0">{{ $application->volunteer->email }}</p>
                        </div>
                    </div>
                    
                    @if($application->volunteer->volunteerProfile)
                        <div class="row text-sm">
                            <div class="col-6 mb-2">
                                <strong>Phone:</strong><br>
                                <span class="text-muted">{{ $application->volunteer->volunteerProfile->phone ?? 'Not provided' }}</span>
                            </div>
                            <div class="col-6 mb-2">
                                <strong>Age:</strong><br>
                                <span class="text-muted">{{ $application->volunteer->volunteerProfile->age ?? 'Not provided' }}</span>
                            </div>
                            <div class="col-6 mb-2">
                                <strong>Location:</strong><br>
                                <span class="text-muted">{{ $application->volunteer->volunteerProfile->district }}, {{ $application->volunteer->volunteerProfile->region }}</span>
                            </div>
                            <div class="col-6 mb-2">
                                <strong>Experience:</strong><br>
                                <span class="text-muted">{{ $application->volunteer->volunteerProfile->experience_level ?? 'Not specified' }}</span>
                            </div>
                        </div>
                        
                        @if($application->volunteer->volunteerProfile->bio)
                            <div class="mt-3">
                                <strong>Bio:</strong>
                                <p class="text-muted mt-1">{{ $application->volunteer->volunteerProfile->bio }}</p>
                            </div>
                        @endif
                    @endif
                </div>
            </div>
        </div>

        <!-- Application Message -->
        @if($application->message)
            <div class="col-12 mb-4">
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="bi bi-chat-text me-2"></i>Application Message
                        </h6>
                    </div>
                    <div class="card-body">
                        <p class="mb-0">{{ $application->message }}</p>
                    </div>
                </div>
            </div>
        @endif

        <!-- Actions (for organization users) -->
        @if(auth()->user()->hasRole('organization') && $application->status === 'pending')
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="bi bi-gear me-2"></i>Actions
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="d-flex gap-2">
                            <form action="{{ route('applications.accept', $application) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" 
                                        class="btn btn-success"
                                        onclick="return confirm('Accept this application? The volunteer will be notified.')">
                                    <i class="bi bi-check-circle me-1"></i>Accept Application
                                </button>
                            </form>
                            
                            <form action="{{ route('applications.reject', $application) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" 
                                        class="btn btn-danger"
                                        onclick="return confirm('Reject this application? The volunteer will be notified.')">
                                    <i class="bi bi-x-circle me-1"></i>Reject Application
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>

@push('styles')
<style>
    .avatar-circle {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        font-size: 20px;
    }
    
    .text-sm {
        font-size: 0.875rem;
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
