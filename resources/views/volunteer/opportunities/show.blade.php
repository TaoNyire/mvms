@extends('layouts.volunteer')

@section('title', $opportunity->title . ' - MVMS')

@section('page-title', 'Opportunity Details')

@section('content')
<div class="container-fluid">
    <!-- Back Button -->
    <div class="row mb-3">
        <div class="col-12">
            <a href="{{ route('volunteer.opportunities.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i>Back to Opportunities
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Main Content -->
        <div class="col-lg-8">
            <!-- Opportunity Header -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-start">
                    <div>
                        <h3 class="mb-2">{{ $opportunity->title }}</h3>
                        <div class="d-flex flex-wrap gap-2 mb-2">
                            <span class="badge bg-secondary">{{ $opportunity->category }}</span>
                            <span class="badge bg-info">{{ ucfirst(str_replace('_', ' ', $opportunity->type)) }}</span>
                            @if($opportunity->urgency === 'urgent')
                                <span class="badge bg-danger">Urgent</span>
                            @elseif($opportunity->urgency === 'high')
                                <span class="badge bg-warning">High Priority</span>
                            @endif
                            @if($opportunity->is_paid)
                                <span class="badge bg-success">Paid Position</span>
                            @endif
                        </div>
                        <p class="text-muted mb-0">
                            <i class="bi bi-building me-1"></i>{{ $opportunity->organization->name }}
                        </p>
                    </div>
                    @if($matchScore > 0)
                        <div class="text-end">
                            <div class="badge bg-success fs-6">
                                {{ $matchScore }}% Match
                            </div>
                            <small class="d-block text-muted">Based on your profile</small>
                        </div>
                    @endif
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <h6><i class="bi bi-geo-alt me-2"></i>Location</h6>
                            <p>{{ $opportunity->district }}, {{ $opportunity->region }}</p>
                            @if($opportunity->address)
                                <small class="text-muted">{{ $opportunity->address }}</small>
                            @endif
                        </div>
                        <div class="col-md-6">
                            <h6><i class="bi bi-calendar me-2"></i>Schedule</h6>
                            <p>
                                <strong>Start:</strong> {{ $opportunity->start_date->format('M j, Y') }}
                                @if($opportunity->start_time)
                                    at {{ $opportunity->start_time->format('g:i A') }}
                                @endif
                            </p>
                            @if($opportunity->end_date)
                                <p>
                                    <strong>End:</strong> {{ $opportunity->end_date->format('M j, Y') }}
                                    @if($opportunity->end_time)
                                        at {{ $opportunity->end_time->format('g:i A') }}
                                    @endif
                                </p>
                            @endif
                            @if($opportunity->duration_hours)
                                <p><strong>Duration:</strong> {{ $opportunity->duration_hours }} hours</p>
                            @endif
                        </div>
                    </div>

                    <h5>Description</h5>
                    <p>{{ $opportunity->description }}</p>

                    @if($opportunity->requirements)
                        <h5>Requirements</h5>
                        <p>{{ $opportunity->requirements }}</p>
                    @endif

                    @if($opportunity->required_skills && count($opportunity->required_skills) > 0)
                        <h5>Required Skills</h5>
                        <div class="mb-3">
                            @foreach($opportunity->required_skills as $skill)
                                <span class="badge bg-light text-dark me-1 mb-1">{{ $skill }}</span>
                            @endforeach
                        </div>
                    @endif

                    @if($opportunity->benefits)
                        <h5>Benefits</h5>
                        <p>{{ $opportunity->benefits }}</p>
                    @endif

                    <!-- Additional Benefits -->
                    @if($opportunity->provides_transport || $opportunity->provides_meals || $opportunity->provides_accommodation)
                        <h5>Additional Benefits</h5>
                        <ul class="list-unstyled">
                            @if($opportunity->provides_transport)
                                <li><i class="bi bi-check-circle text-success me-2"></i>Transportation Provided</li>
                            @endif
                            @if($opportunity->provides_meals)
                                <li><i class="bi bi-check-circle text-success me-2"></i>Meals Provided</li>
                            @endif
                            @if($opportunity->provides_accommodation)
                                <li><i class="bi bi-check-circle text-success me-2"></i>Accommodation Provided</li>
                            @endif
                        </ul>
                    @endif

                    @if($opportunity->is_paid && $opportunity->payment_amount)
                        <h5>Compensation</h5>
                        <p>
                            <strong>MWK {{ number_format($opportunity->payment_amount) }}</strong>
                            @if($opportunity->payment_frequency)
                                per {{ $opportunity->payment_frequency }}
                            @endif
                        </p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Application Status -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Application Status</h5>
                </div>
                <div class="card-body">
                    @if($userApplication)
                        @if($userApplication->status === 'pending')
                            <div class="alert alert-warning">
                                <i class="bi bi-clock me-2"></i>
                                <strong>Application Pending</strong><br>
                                Applied on {{ $userApplication->applied_at->format('M j, Y') }}
                            </div>
                            <button type="button" class="btn btn-outline-danger btn-sm" 
                                    onclick="withdrawApplication({{ $userApplication->id }})">
                                <i class="bi bi-x-circle me-1"></i>Withdraw Application
                            </button>
                        @elseif($userApplication->status === 'accepted')
                            <div class="alert alert-success">
                                <i class="bi bi-check-circle me-2"></i>
                                <strong>Application Accepted!</strong><br>
                                Accepted on {{ $userApplication->accepted_at->format('M j, Y') }}
                            </div>
                        @elseif($userApplication->status === 'rejected')
                            <div class="alert alert-danger">
                                <i class="bi bi-x-circle me-2"></i>
                                <strong>Application Not Selected</strong><br>
                                @if($userApplication->rejection_reason)
                                    <small>{{ $userApplication->rejection_reason }}</small>
                                @endif
                            </div>
                        @endif
                    @else
                        @if($opportunity->application_status === 'Open for applications')
                            <div class="d-grid">
                                <a href="{{ route('volunteer.opportunities.apply', $opportunity) }}" 
                                   class="btn btn-primary btn-lg">
                                    <i class="bi bi-send me-2"></i>Apply Now
                                </a>
                            </div>
                        @else
                            <div class="alert alert-warning">
                                <i class="bi bi-exclamation-triangle me-2"></i>
                                {{ $opportunity->application_status }}
                            </div>
                        @endif
                    @endif
                </div>
            </div>

            <!-- Opportunity Details -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Quick Details</h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6 mb-3">
                            <div class="border-end">
                                <h4 class="text-primary mb-0">{{ $opportunity->spots_remaining }}</h4>
                                <small>Spots Remaining</small>
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <h4 class="text-info mb-0">{{ $opportunity->applications_count ?? 0 }}</h4>
                            <small>Total Applications</small>
                        </div>
                    </div>
                    
                    @if($opportunity->application_deadline)
                        <hr>
                        <div class="text-center">
                            <h6>Application Deadline</h6>
                            <p class="mb-0">{{ $opportunity->application_deadline->format('M j, Y') }}</p>
                            <small class="text-muted">
                                {{ $opportunity->application_deadline->diffForHumans() }}
                            </small>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Organization Info -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">About the Organization</h5>
                </div>
                <div class="card-body">
                    <h6>{{ $opportunity->organization->name }}</h6>
                    @if($opportunity->organization->organizationProfile)
                        <p class="small">{{ Str::limit($opportunity->organization->organizationProfile->description, 150) }}</p>
                    @endif
                    
                    @if($opportunity->contact_person || $opportunity->contact_email || $opportunity->contact_phone)
                        <hr>
                        <h6>Contact Information</h6>
                        @if($opportunity->contact_person)
                            <p class="mb-1"><strong>Contact:</strong> {{ $opportunity->contact_person }}</p>
                        @endif
                        @if($opportunity->contact_email)
                            <p class="mb-1"><strong>Email:</strong> {{ $opportunity->contact_email }}</p>
                        @endif
                        @if($opportunity->contact_phone)
                            <p class="mb-0"><strong>Phone:</strong> {{ $opportunity->contact_phone }}</p>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Similar Opportunities -->
    @if($similarOpportunities->count() > 0)
        <div class="row mt-4">
            <div class="col-12">
                <h4>Similar Opportunities</h4>
                <div class="row">
                    @foreach($similarOpportunities as $similar)
                        <div class="col-md-4 mb-3">
                            <div class="card h-100">
                                <div class="card-body">
                                    <h6 class="card-title">{{ $similar->title }}</h6>
                                    <p class="card-text small">{{ Str::limit($similar->description, 100) }}</p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <small class="text-muted">{{ $similar->district }}</small>
                                        <a href="{{ route('volunteer.opportunities.show', $similar) }}" 
                                           class="btn btn-outline-primary btn-sm">View</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif
</div>

<!-- Withdraw Application Modal -->
<div class="modal fade" id="withdrawModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Withdraw Application</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to withdraw your application for this opportunity?</p>
                <p class="text-muted small">This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="withdrawForm" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Withdraw Application</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function withdrawApplication(applicationId) {
    const form = document.getElementById('withdrawForm');
    form.action = `/volunteer/applications/${applicationId}/withdraw`;
    
    const modal = new bootstrap.Modal(document.getElementById('withdrawModal'));
    modal.show();
}
</script>
@endpush
