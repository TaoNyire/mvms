@extends('layouts.volunteer')

@section('title', 'Apply for ' . $opportunity->title . ' - MVMS')

@section('page-title', 'Apply for Opportunity')

@section('content')
<div class="container-fluid">
    <!-- Back Button -->
    <div class="row mb-3">
        <div class="col-12">
            <a href="{{ route('volunteer.opportunities.show', $opportunity) }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i>Back to Opportunity
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Application Form -->
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">
                        <i class="bi bi-send me-2"></i>Apply for: {{ $opportunity->title }}
                    </h4>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('applications.store', $opportunity) }}" id="applicationForm">
                        @csrf
                        
                        <!-- Cover Letter / Message -->
                        <div class="mb-4">
                            <label for="message" class="form-label fw-bold">
                                Cover Letter / Motivation <span class="text-danger">*</span>
                            </label>
                            <textarea class="form-control @error('message') is-invalid @enderror" 
                                      id="message" name="message" rows="6" required
                                      placeholder="Tell the organization why you're interested in this opportunity and what you can contribute...">{{ old('message') }}</textarea>
                            <div class="form-text">Minimum 50 characters. Be specific about your motivation and relevant experience.</div>
                            @error('message')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Relevant Experience -->
                        <div class="mb-4">
                            <label for="relevant_experience" class="form-label fw-bold">
                                Relevant Experience
                            </label>
                            <textarea class="form-control @error('relevant_experience') is-invalid @enderror" 
                                      id="relevant_experience" name="relevant_experience" rows="4"
                                      placeholder="Describe any relevant experience, skills, or qualifications you have for this role...">{{ old('relevant_experience') }}</textarea>
                            <div class="form-text">Optional: Highlight experience that makes you a good fit for this opportunity.</div>
                            @error('relevant_experience')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Availability Details -->
                        <div class="mb-4">
                            <label for="availability_details" class="form-label fw-bold">
                                Availability for this Opportunity
                            </label>
                            <textarea class="form-control @error('availability_details') is-invalid @enderror" 
                                      id="availability_details" name="availability_details" rows="3"
                                      placeholder="Confirm your availability for the dates and times of this opportunity...">{{ old('availability_details') }}</textarea>
                            <div class="form-text">Optional: Specify your availability for this specific opportunity.</div>
                            @error('availability_details')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Terms and Conditions -->
                        <div class="mb-4">
                            <div class="form-check">
                                <input class="form-check-input @error('agrees_to_terms') is-invalid @enderror" 
                                       type="checkbox" id="agrees_to_terms" name="agrees_to_terms" required
                                       {{ old('agrees_to_terms') ? 'checked' : '' }}>
                                <label class="form-check-label" for="agrees_to_terms">
                                    I agree to the terms and conditions of this volunteer opportunity and understand my responsibilities. <span class="text-danger">*</span>
                                </label>
                                @error('agrees_to_terms')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('volunteer.opportunities.show', $opportunity) }}" 
                               class="btn btn-secondary">
                                <i class="bi bi-arrow-left me-1"></i>Cancel
                            </a>
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="bi bi-send me-2"></i>Submit Application
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Opportunity Summary -->
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Opportunity Summary</h5>
                </div>
                <div class="card-body">
                    <h6>{{ $opportunity->title }}</h6>
                    <p class="text-muted mb-2">
                        <i class="bi bi-building me-1"></i>{{ $opportunity->organization->name }}
                    </p>
                    
                    <hr>
                    
                    <div class="mb-3">
                        <strong>Location:</strong><br>
                        <small>{{ $opportunity->district }}, {{ $opportunity->region }}</small>
                    </div>
                    
                    <div class="mb-3">
                        <strong>Start Date:</strong><br>
                        <small>{{ $opportunity->start_date->format('M j, Y') }}
                        @if($opportunity->start_time)
                            at {{ $opportunity->start_time->format('g:i A') }}
                        @endif</small>
                    </div>
                    
                    @if($opportunity->duration_hours)
                        <div class="mb-3">
                            <strong>Duration:</strong><br>
                            <small>{{ $opportunity->duration_hours }} hours</small>
                        </div>
                    @endif
                    
                    <div class="mb-3">
                        <strong>Type:</strong><br>
                        <small>{{ ucfirst(str_replace('_', ' ', $opportunity->type)) }}</small>
                    </div>
                    
                    @if($opportunity->required_skills && count($opportunity->required_skills) > 0)
                        <div class="mb-3">
                            <strong>Required Skills:</strong><br>
                            @foreach($opportunity->required_skills as $skill)
                                <span class="badge bg-light text-dark me-1 mb-1">{{ $skill }}</span>
                            @endforeach
                        </div>
                    @endif
                    
                    @if($opportunity->is_paid && $opportunity->payment_amount)
                        <div class="mb-3">
                            <strong>Compensation:</strong><br>
                            <small>MWK {{ number_format($opportunity->payment_amount) }}
                            @if($opportunity->payment_frequency)
                                per {{ $opportunity->payment_frequency }}
                            @endif</small>
                        </div>
                    @endif
                    
                    <hr>
                    
                    <div class="text-center">
                        <div class="row">
                            <div class="col-6">
                                <h5 class="text-primary mb-0">{{ $opportunity->spots_remaining }}</h5>
                                <small>Spots Left</small>
                            </div>
                            <div class="col-6">
                                <h5 class="text-info mb-0">{{ $opportunity->applications_count ?? 0 }}</h5>
                                <small>Applications</small>
                            </div>
                        </div>
                    </div>
                    
                    @if($opportunity->application_deadline)
                        <hr>
                        <div class="text-center">
                            <strong>Application Deadline:</strong><br>
                            <small>{{ $opportunity->application_deadline->format('M j, Y') }}</small><br>
                            <small class="text-muted">{{ $opportunity->application_deadline->diffForHumans() }}</small>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Application Tips -->
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-lightbulb me-2"></i>Application Tips
                    </h5>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2">
                            <i class="bi bi-check-circle text-success me-2"></i>
                            <small>Be specific about your motivation and interest</small>
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-check-circle text-success me-2"></i>
                            <small>Highlight relevant skills and experience</small>
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-check-circle text-success me-2"></i>
                            <small>Confirm your availability for the dates</small>
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-check-circle text-success me-2"></i>
                            <small>Show enthusiasm for the cause</small>
                        </li>
                        <li class="mb-0">
                            <i class="bi bi-check-circle text-success me-2"></i>
                            <small>Proofread before submitting</small>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Character count for message field
    $('#message').on('input', function() {
        const length = $(this).val().length;
        const minLength = 50;
        
        if (length < minLength) {
            $(this).removeClass('is-valid').addClass('is-invalid');
        } else {
            $(this).removeClass('is-invalid').addClass('is-valid');
        }
    });
    
    // Form validation
    $('#applicationForm').on('submit', function(e) {
        const message = $('#message').val();
        const termsAccepted = $('#agrees_to_terms').is(':checked');
        
        if (message.length < 50) {
            e.preventDefault();
            alert('Please write at least 50 characters in your cover letter.');
            $('#message').focus();
            return false;
        }
        
        if (!termsAccepted) {
            e.preventDefault();
            alert('Please agree to the terms and conditions.');
            $('#agrees_to_terms').focus();
            return false;
        }
        
        // Show loading state
        $(this).find('button[type="submit"]').prop('disabled', true).html(
            '<span class="spinner-border spinner-border-sm me-2"></span>Submitting...'
        );
    });
});
</script>
@endpush
