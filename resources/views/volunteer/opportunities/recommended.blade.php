@extends('layouts.volunteer')

@section('title', 'Recommended Opportunities - MVMS')

@section('page-title', 'Recommended for You')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-md-8">
            <h2>
                <i class="bi bi-star me-2"></i>Recommended Opportunities
            </h2>
            <p>These opportunities are specially selected based on your profile, skills, and preferences.</p>
        </div>
        <div class="col-md-4 text-md-end">
            <a href="{{ route('volunteer.opportunities.index') }}" class="btn btn-outline-primary">
                <i class="bi bi-search me-1"></i>Browse All Opportunities
            </a>
        </div>
    </div>

    @if($recommendedOpportunities->count() > 0)
        <!-- Match Score Info -->
        <div class="alert alert-info mb-4">
            <div class="d-flex align-items-center">
                <i class="bi bi-info-circle me-3" style="font-size: 1.5rem;"></i>
                <div>
                    <h6 class="mb-1">How we calculate your match score:</h6>
                    <small>
                        We analyze your skills (40%), location preferences (25%), availability (20%), 
                        travel capability (10%), and relevant experience (5%) to find the best opportunities for you.
                    </small>
                </div>
            </div>
        </div>

        <!-- Recommended Opportunities Grid -->
        <div class="row">
            @foreach($recommendedOpportunities as $opportunity)
                <div class="col-lg-6 col-xl-4 mb-4">
                    <div class="card h-100 opportunity-card border-success">
                        <div class="card-header bg-success text-white d-flex justify-content-between align-items-start">
                            <div>
                                <span class="badge bg-light text-dark">{{ $opportunity->category }}</span>
                                @if($opportunity->urgency === 'urgent')
                                    <span class="badge bg-danger">Urgent</span>
                                @elseif($opportunity->urgency === 'high')
                                    <span class="badge bg-warning text-dark">High Priority</span>
                                @endif
                                @if($opportunity->is_paid)
                                    <span class="badge bg-warning text-dark">Paid</span>
                                @endif
                            </div>
                            <div class="text-end">
                                <div class="badge bg-light text-success fw-bold fs-6">
                                    {{ $opportunity->match_score }}% Match
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <h6 class="card-title">{{ $opportunity->title }}</h6>
                            <p class="card-text">
                                <small class="text-muted">
                                    <i class="bi bi-building me-1"></i>{{ $opportunity->organization->name }}
                                </small>
                            </p>
                            <p class="card-text">{{ Str::limit($opportunity->description, 120) }}</p>
                            
                            <div class="mb-2">
                                <small>
                                    <i class="bi bi-geo-alt me-1"></i>{{ $opportunity->district }}, {{ $opportunity->region }}
                                </small>
                            </div>
                            <div class="mb-2">
                                <small>
                                    <i class="bi bi-calendar me-1"></i>{{ $opportunity->start_date->format('M j, Y') }}
                                    @if($opportunity->start_time)
                                        at {{ $opportunity->start_time->format('g:i A') }}
                                    @endif
                                </small>
                            </div>
                            <div class="mb-2">
                                <small>
                                    <i class="bi bi-people me-1"></i>{{ $opportunity->spots_remaining }} spots remaining
                                </small>
                            </div>
                            
                            @if($opportunity->required_skills && count($opportunity->required_skills) > 0)
                                <div class="mb-2">
                                    @foreach(array_slice($opportunity->required_skills, 0, 3) as $skill)
                                        <span class="badge bg-light text-dark me-1">{{ $skill }}</span>
                                    @endforeach
                                    @if(count($opportunity->required_skills) > 3)
                                        <span class="badge bg-light text-dark">+{{ count($opportunity->required_skills) - 3 }} more</span>
                                    @endif
                                </div>
                            @endif

                            <!-- Match Breakdown -->
                            <div class="mt-3">
                                <small class="text-muted">Why this matches you:</small>
                                <div class="progress-stacked" style="height: 8px;">
                                    @php
                                        $skillsMatch = 0;
                                        $locationMatch = 0;
                                        $availabilityMatch = 0;
                                        
                                        // Simple match calculation for display
                                        if(Auth::user()->volunteerProfile) {
                                            $profile = Auth::user()->volunteerProfile;
                                            
                                            // Skills match
                                            if($opportunity->required_skills && $profile->skills) {
                                                $matchingSkills = array_intersect($opportunity->required_skills, $profile->skills);
                                                $skillsMatch = (count($matchingSkills) / count($opportunity->required_skills)) * 40;
                                            }
                                            
                                            // Location match
                                            if($opportunity->district === $profile->district) {
                                                $locationMatch = 25;
                                            } elseif($opportunity->region === $profile->region) {
                                                $locationMatch = 15;
                                            }
                                            
                                            // Availability (simplified)
                                            $availabilityMatch = 15;
                                        }
                                    @endphp
                                    
                                    @if($skillsMatch > 0)
                                        <div class="progress" role="progressbar" style="width: {{ $skillsMatch }}%">
                                            <div class="progress-bar bg-primary" title="Skills Match: {{ round($skillsMatch) }}%"></div>
                                        </div>
                                    @endif
                                    @if($locationMatch > 0)
                                        <div class="progress" role="progressbar" style="width: {{ $locationMatch }}%">
                                            <div class="progress-bar bg-success" title="Location Match: {{ round($locationMatch) }}%"></div>
                                        </div>
                                    @endif
                                    @if($availabilityMatch > 0)
                                        <div class="progress" role="progressbar" style="width: {{ $availabilityMatch }}%">
                                            <div class="progress-bar bg-info" title="Availability Match: {{ round($availabilityMatch) }}%"></div>
                                        </div>
                                    @endif
                                </div>
                                <div class="d-flex justify-content-between mt-1">
                                    <small class="text-primary">Skills</small>
                                    <small class="text-success">Location</small>
                                    <small class="text-info">Availability</small>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    @if(isset($userApplications[$opportunity->id]))
                                        @php $status = $userApplications[$opportunity->id]; @endphp
                                        @if($status === 'pending')
                                            <span class="badge bg-warning">Application Pending</span>
                                        @elseif($status === 'accepted')
                                            <span class="badge bg-success">Accepted</span>
                                        @elseif($status === 'rejected')
                                            <span class="badge bg-danger">Not Selected</span>
                                        @endif
                                    @else
                                        @if($opportunity->is_full)
                                            <span class="badge bg-secondary">Full</span>
                                        @elseif($opportunity->application_status !== 'Open for applications')
                                            <span class="badge bg-warning">{{ $opportunity->application_status }}</span>
                                        @endif
                                    @endif
                                </div>
                                <div>
                                    <a href="{{ route('volunteer.opportunities.show', $opportunity) }}" 
                                       class="btn btn-success btn-sm">
                                        <i class="bi bi-eye me-1"></i>View Details
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Call to Action -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card bg-light">
                    <div class="card-body text-center">
                        <h5>Don't see what you're looking for?</h5>
                        <p>Browse all available opportunities or update your profile to get better recommendations.</p>
                        <div class="d-flex justify-content-center gap-2">
                            <a href="{{ route('volunteer.opportunities.index') }}" class="btn btn-primary">
                                <i class="bi bi-search me-1"></i>Browse All Opportunities
                            </a>
                            <a href="{{ route('volunteer.profile.edit') }}" class="btn btn-outline-primary">
                                <i class="bi bi-person-gear me-1"></i>Update Profile
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @else
        <!-- No Recommendations -->
        <div class="text-center py-5">
            <i class="bi bi-star" style="font-size: 4rem; color: #dee2e6;"></i>
            <h4 class="mt-3">No Recommendations Yet</h4>
            <p>We couldn't find any opportunities that match your current profile.</p>
            <p class="text-muted">This could be because:</p>
            <ul class="list-unstyled text-muted">
                <li>• Your profile needs more details</li>
                <li>• No opportunities match your skills and location</li>
                <li>• All matching opportunities are currently full</li>
            </ul>
            <div class="mt-4">
                <a href="{{ route('volunteer.profile.edit') }}" class="btn btn-primary me-2">
                    <i class="bi bi-person-gear me-1"></i>Complete Your Profile
                </a>
                <a href="{{ route('volunteer.opportunities.index') }}" class="btn btn-outline-primary">
                    <i class="bi bi-search me-1"></i>Browse All Opportunities
                </a>
            </div>
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Opportunity card hover effects
    $('.opportunity-card').hover(
        function() {
            $(this).addClass('shadow-lg').css('transform', 'translateY(-2px)');
        },
        function() {
            $(this).removeClass('shadow-lg').css('transform', 'translateY(0)');
        }
    );
    
    // Initialize tooltips for progress bars
    $('[title]').tooltip();
});
</script>
@endpush

@push('styles')
<style>
.opportunity-card {
    transition: all 0.3s ease;
}

.opportunity-card:hover {
    border-color: #28a745 !important;
}

.progress-stacked {
    display: flex;
    gap: 2px;
}

.progress-stacked .progress {
    flex: 0 0 auto;
    background-color: transparent;
}
</style>
@endpush
