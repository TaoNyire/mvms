@extends('layouts.organization')

@section('title', 'Create Opportunity - MVMS')

@section('page-title', 'Create New Volunteer Opportunity')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 bg-gradient" style="background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);">
                <div class="card-body text-white p-4">
                    <h2 class="mb-2">
                        <i class="bi bi-plus-circle me-2"></i>Create New Volunteer Opportunity
                    </h2>
                    <p class="mb-0">
                        Post a new volunteer opportunity to connect with passionate volunteers in your community.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Success/Error Messages -->
    <div id="alert-container">
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

        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                <div class="d-flex align-items-start">
                    <div class="flex-shrink-0">
                        <i class="bi bi-exclamation-triangle-fill fs-4 text-danger"></i>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h6 class="alert-heading mb-2">Please fix the following errors:</h6>
                        <ul class="mb-0 ps-3">
                            @foreach($errors->all() as $error)
                                <li class="mb-1">{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
    </div>

    <!-- Opportunity Form -->
    <form id="opportunityForm" method="POST" action="{{ route('opportunities.store') }}" novalidate>
        @csrf
        
        <!-- Basic Information -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">
                    <i class="bi bi-info-circle me-2"></i>Basic Information
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-8 mb-3">
                        <label for="title" class="form-label fw-bold">
                            Opportunity Title <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control @error('title') is-invalid @enderror"
                               id="title" name="title" value="{{ old('title') }}"
                               placeholder="e.g., Community Health Education Volunteer" required>
                        @error('title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @else
                            <div class="invalid-feedback">Please provide a clear opportunity title.</div>
                        @enderror
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="urgency" class="form-label fw-bold">
                            Urgency Level <span class="text-danger">*</span>
                        </label>
                        <select class="form-select @error('urgency') is-invalid @enderror" id="urgency" name="urgency" required>
                            <option value="">Select Urgency</option>
                            <option value="low" {{ old('urgency') == 'low' ? 'selected' : '' }}>Low</option>
                            <option value="medium" {{ old('urgency', 'medium') == 'medium' ? 'selected' : '' }}>Medium</option>
                            <option value="high" {{ old('urgency') == 'high' ? 'selected' : '' }}>High</option>
                            <option value="urgent" {{ old('urgency') == 'urgent' ? 'selected' : '' }}>Urgent</option>
                        </select>
                        @error('urgency')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @else
                            <div class="invalid-feedback">Please select an urgency level.</div>
                        @enderror
                    </div>
                    <div class="col-12 mb-3">
                        <label for="description" class="form-label fw-bold">
                            Description <span class="text-danger">*</span>
                        </label>
                        <textarea class="form-control @error('description') is-invalid @enderror"
                                  id="description" name="description" rows="4"
                                  placeholder="Describe the volunteer opportunity, what volunteers will do, and the impact they'll make..."
                                  required>{{ old('description') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @else
                            <div class="form-text">Minimum 50 characters</div>
                        @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="category" class="form-label fw-bold">
                            Category <span class="text-danger">*</span>
                        </label>
                        <select class="form-select" id="category" name="category" required>
                            <option value="">Select Category</option>
                            <option value="Education">Education</option>
                            <option value="Health">Health & Medical</option>
                            <option value="Environment">Environment & Conservation</option>
                            <option value="Community">Community Development</option>
                            <option value="Youth">Youth Development</option>
                            <option value="Women">Women Empowerment</option>
                            <option value="Emergency">Emergency Response</option>
                            <option value="Technology">Technology & Innovation</option>
                            <option value="Arts">Arts & Culture</option>
                            <option value="Sports">Sports & Recreation</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="type" class="form-label fw-bold">
                            Opportunity Type <span class="text-danger">*</span>
                        </label>
                        <select class="form-select" id="type" name="type" required>
                            <option value="">Select Type</option>
                            <option value="one_time">One-time Event</option>
                            <option value="recurring">Recurring</option>
                            <option value="ongoing">Ongoing</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- Location & Timing -->
        <div class="card mb-4">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0">
                    <i class="bi bi-geo-alt me-2"></i>Location & Timing
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="location_type" class="form-label fw-bold">
                            Location Type <span class="text-danger">*</span>
                        </label>
                        <select class="form-select" id="location_type" name="location_type" required>
                            <option value="">Select Location Type</option>
                            <option value="physical">Physical Location</option>
                            <option value="remote">Remote/Online</option>
                            <option value="hybrid">Hybrid</option>
                        </select>
                    </div>
                    <div class="col-md-4 mb-3" id="district_field">
                        <label for="district" class="form-label fw-bold">
                            District <span class="text-danger">*</span>
                        </label>
                        <select class="form-select" id="district" name="district">
                            <option value="">Select District</option>
                            <option value="Lilongwe">Lilongwe</option>
                            <option value="Blantyre">Blantyre</option>
                            <option value="Mzuzu">Mzuzu</option>
                            <option value="Zomba">Zomba</option>
                            <option value="Kasungu">Kasungu</option>
                            <option value="Mangochi">Mangochi</option>
                            <!-- Add more districts -->
                        </select>
                    </div>
                    <div class="col-md-4 mb-3" id="region_field">
                        <label for="region" class="form-label fw-bold">
                            Region <span class="text-danger">*</span>
                        </label>
                        <select class="form-select" id="region" name="region">
                            <option value="">Select Region</option>
                            <option value="Northern">Northern</option>
                            <option value="Central">Central</option>
                            <option value="Southern">Southern</option>
                        </select>
                    </div>
                    <div class="col-12 mb-3" id="address_field">
                        <label for="address" class="form-label fw-bold">
                            Specific Address <span class="text-danger">*</span>
                        </label>
                        <textarea class="form-control" id="address" name="address" rows="2" 
                                  placeholder="Enter the specific address or location details"></textarea>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="start_date" class="form-label fw-bold">
                            Start Date <span class="text-danger">*</span>
                        </label>
                        <input type="date" class="form-control" id="start_date" name="start_date" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="end_date" class="form-label fw-bold">End Date</label>
                        <input type="date" class="form-control" id="end_date" name="end_date">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="start_time" class="form-label fw-bold">Start Time</label>
                        <input type="time" class="form-control" id="start_time" name="start_time">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="end_time" class="form-label fw-bold">End Time</label>
                        <input type="time" class="form-control" id="end_time" name="end_time">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="duration_hours" class="form-label fw-bold">Duration (Hours)</label>
                        <input type="number" class="form-control" id="duration_hours" name="duration_hours" 
                               min="1" max="168" placeholder="e.g., 4">
                    </div>
                </div>
            </div>
        </div>

        <!-- Volunteer Requirements -->
        <div class="card mb-4">
            <div class="card-header bg-warning text-dark">
                <h5 class="mb-0">
                    <i class="bi bi-people me-2"></i>Volunteer Requirements
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="volunteers_needed" class="form-label fw-bold">
                            Number of Volunteers Needed <span class="text-danger">*</span>
                        </label>
                        <input type="number" class="form-control" id="volunteers_needed" name="volunteers_needed" 
                               min="1" max="100" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="application_deadline" class="form-label fw-bold">
                            Application Deadline
                            <small class="text-muted">(Optional)</small>
                        </label>
                        <input type="datetime-local"
                               class="form-control @error('application_deadline') is-invalid @enderror"
                               id="application_deadline"
                               name="application_deadline"
                               value="{{ old('application_deadline') }}"
                               placeholder="Select date and time"
                               title="Select the deadline for volunteer applications">
                        @error('application_deadline')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @else
                            <div class="form-text">
                                <i class="bi bi-info-circle me-1"></i>
                                Deadline for volunteers to apply (must be before start date and in the future)
                                <br><small class="text-muted">Example: If your opportunity starts on March 15, 2025, set deadline to March 10, 2025</small>
                            </div>
                        @enderror
                    </div>
                    <div class="col-12 mb-3">
                        <label class="form-label fw-bold">Required Skills</label>
                        <div class="row" id="skillsContainer">
                            <div class="col-md-3 mb-2">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="required_skills[]" value="Teaching" id="skill_teaching">
                                    <label class="form-check-label" for="skill_teaching">Teaching</label>
                                </div>
                            </div>
                            <div class="col-md-3 mb-2">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="required_skills[]" value="Healthcare" id="skill_healthcare">
                                    <label class="form-check-label" for="skill_healthcare">Healthcare</label>
                                </div>
                            </div>
                            <div class="col-md-3 mb-2">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="required_skills[]" value="Technology" id="skill_technology">
                                    <label class="form-check-label" for="skill_technology">Technology</label>
                                </div>
                            </div>
                            <div class="col-md-3 mb-2">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="required_skills[]" value="Agriculture" id="skill_agriculture">
                                    <label class="form-check-label" for="skill_agriculture">Agriculture</label>
                                </div>
                            </div>
                            <div class="col-md-3 mb-2">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="required_skills[]" value="Construction" id="skill_construction">
                                    <label class="form-check-label" for="skill_construction">Construction</label>
                                </div>
                            </div>
                            <div class="col-md-3 mb-2">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="required_skills[]" value="Administration" id="skill_administration">
                                    <label class="form-check-label" for="skill_administration">Administration</label>
                                </div>
                            </div>
                            <div class="col-md-3 mb-2">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="required_skills[]" value="Marketing" id="skill_marketing">
                                    <label class="form-check-label" for="skill_marketing">Marketing</label>
                                </div>
                            </div>
                            <div class="col-md-3 mb-2">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="required_skills[]" value="Social Work" id="skill_social_work">
                                    <label class="form-check-label" for="skill_social_work">Social Work</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 mb-3">
                        <label for="requirements" class="form-label fw-bold">Additional Requirements</label>
                        <textarea class="form-control" id="requirements" name="requirements" rows="3" 
                                  placeholder="Specify any additional requirements, qualifications, or expectations..."></textarea>
                    </div>
                </div>
            </div>
        </div>

        <!-- Benefits & Compensation -->
        <div class="card mb-4">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0">
                    <i class="bi bi-gift me-2"></i>Benefits & Compensation
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-12 mb-3">
                        <div class="form-check">
                            <!-- Hidden input to ensure false value is sent when unchecked -->
                            <input type="hidden" name="is_paid" value="0">
                            <input class="form-check-input @error('is_paid') is-invalid @enderror"
                                   type="checkbox"
                                   id="is_paid"
                                   name="is_paid"
                                   value="1"
                                   {{ old('is_paid') ? 'checked' : '' }}>
                            <label class="form-check-label fw-bold" for="is_paid">
                                This is a paid opportunity
                            </label>
                            @error('is_paid')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div id="payment_details" style="display: none;">
                        <div class="col-md-6 mb-3">
                            <label for="payment_amount" class="form-label fw-bold">Payment Amount (MWK)</label>
                            <input type="number" class="form-control" id="payment_amount" name="payment_amount" 
                                   min="0" step="0.01" placeholder="e.g., 5000">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="payment_frequency" class="form-label fw-bold">Payment Frequency</label>
                            <select class="form-select" id="payment_frequency" name="payment_frequency">
                                <option value="">Select Frequency</option>
                                <option value="hourly">Per Hour</option>
                                <option value="daily">Per Day</option>
                                <option value="total">Total Amount</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-12 mb-3">
                        <label class="form-label fw-bold">Additional Benefits</label>
                        <div class="row">
                            <div class="col-md-4 mb-2">
                                <div class="form-check">
                                    <input type="hidden" name="provides_transport" value="0">
                                    <input class="form-check-input @error('provides_transport') is-invalid @enderror"
                                           type="checkbox"
                                           id="provides_transport"
                                           name="provides_transport"
                                           value="1"
                                           {{ old('provides_transport') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="provides_transport">Transportation Provided</label>
                                    @error('provides_transport')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4 mb-2">
                                <div class="form-check">
                                    <input type="hidden" name="provides_meals" value="0">
                                    <input class="form-check-input @error('provides_meals') is-invalid @enderror"
                                           type="checkbox"
                                           id="provides_meals"
                                           name="provides_meals"
                                           value="1"
                                           {{ old('provides_meals') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="provides_meals">Meals Provided</label>
                                    @error('provides_meals')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4 mb-2">
                                <div class="form-check">
                                    <input type="hidden" name="provides_accommodation" value="0">
                                    <input class="form-check-input @error('provides_accommodation') is-invalid @enderror"
                                           type="checkbox"
                                           id="provides_accommodation"
                                           name="provides_accommodation"
                                           value="1"
                                           {{ old('provides_accommodation') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="provides_accommodation">Accommodation Provided</label>
                                    @error('provides_accommodation')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 mb-3">
                        <label for="benefits" class="form-label fw-bold">Other Benefits</label>
                        <textarea class="form-control" id="benefits" name="benefits" rows="3" 
                                  placeholder="Describe any other benefits, learning opportunities, or incentives..."></textarea>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between">
                    <a href="{{ route('opportunities.index') }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-left me-1"></i>Cancel
                    </a>
                    <div>

                        <button type="submit" class="btn btn-primary me-2" name="action" value="draft">
                            <i class="bi bi-save me-1"></i>Save as Draft
                        </button>
                        <button type="submit" class="btn btn-success" name="action" value="publish">
                            <i class="bi bi-check-circle me-1"></i>Save & Publish
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Location type change handler
    $('#location_type').change(function() {
        const locationType = $(this).val();
        const physicalFields = $('#district_field, #region_field, #address_field');
        
        if (locationType === 'remote') {
            physicalFields.hide();
            $('#district, #region, #address').prop('required', false);
        } else {
            physicalFields.show();
            $('#district, #region, #address').prop('required', true);
        }
    });
    
    // Payment toggle
    $('#is_paid').change(function() {
        $('#payment_details').toggle(this.checked);
    });
    
    // Set minimum date to today for start_date
    const today = new Date();
    const todayString = today.toISOString().split('T')[0];
    $('#start_date').attr('min', todayString);

    // Don't set min attribute for application_deadline to allow full year selection
    // We'll handle validation through JavaScript and server-side validation instead

    // Update end date minimum when start date changes
    $('#start_date').change(function() {
        const startDate = this.value;
        $('#end_date').attr('min', startDate);

        // Validate application deadline when start date changes
        validateApplicationDeadline();
    });

    // Validate application deadline in real-time
    $('#application_deadline').change(function() {
        validateApplicationDeadline();
    });

    // Function to validate application deadline
    function validateApplicationDeadline() {
        const $deadlineInput = $('#application_deadline');
        const deadlineValue = $deadlineInput.val();
        const startDateValue = $('#start_date').val();

        // Clear previous validation state
        $deadlineInput.removeClass('is-invalid is-valid');

        if (deadlineValue) {
            const deadline = new Date(deadlineValue);
            const now = new Date();
            let isValid = true;
            let errorMessage = '';

            // Check if deadline is in the past
            if (deadline <= now) {
                isValid = false;
                errorMessage = 'Application deadline must be in the future.';
            }

            // Check if deadline is before start date
            if (isValid && startDateValue) {
                const startDate = new Date(startDateValue);
                if (deadline >= startDate) {
                    isValid = false;
                    errorMessage = 'Application deadline must be before the start date.';
                }
            }

            // Apply validation styling
            if (isValid) {
                $deadlineInput.addClass('is-valid');
            } else {
                $deadlineInput.addClass('is-invalid');
                // Create or update error message
                let $errorDiv = $deadlineInput.siblings('.invalid-feedback');
                if ($errorDiv.length === 0) {
                    $errorDiv = $('<div class="invalid-feedback"></div>');
                    $deadlineInput.after($errorDiv);
                }
                $errorDiv.text(errorMessage);
            }
        }
    }
    
    // Form submission with enhanced loading states and feedback
    $('#opportunityForm').submit(function(e) {
        const $form = $(this);
        const $submitButtons = $form.find('button[type="submit"]');
        const $clickedButton = $('button[type="submit"]:focus');
        const action = $clickedButton.attr('name') === 'action' && $clickedButton.val() === 'publish' ? 'publish' : 'draft';

        // Clear any existing alerts
        $('#alert-container').empty();

        // Add loading state with specific messages
        $submitButtons.prop('disabled', true);
        $submitButtons.each(function() {
            const $btn = $(this);
            $btn.data('original-text', $btn.html());

            if ($btn.is($clickedButton)) {
                if (action === 'publish') {
                    $btn.html('<span class="spinner-border spinner-border-sm me-2"></span>Publishing...');
                } else {
                    $btn.html('<span class="spinner-border spinner-border-sm me-2"></span>Saving Draft...');
                }
            } else {
                $btn.html('<span class="spinner-border spinner-border-sm me-2"></span>Processing...');
            }
        });

        // Add publish_now field if publishing
        if (action === 'publish') {
            $form.append('<input type="hidden" name="publish_now" value="1">');
        }

        // Show processing message
        const processingMessage = action === 'publish'
            ? '📤 Publishing your opportunity...'
            : '💾 Saving your opportunity as draft...';

        $('#alert-container').html(`
            <div class="alert alert-info alert-dismissible fade show shadow-sm" role="alert">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="spinner-border spinner-border-sm text-info" role="status"></div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h6 class="alert-heading mb-1">Processing...</h6>
                        <p class="mb-0">${processingMessage}</p>
                    </div>
                </div>
            </div>
        `);

        // Scroll to top to show the processing message
        $('html, body').animate({ scrollTop: 0 }, 300);

        // Re-enable buttons after 15 seconds (fallback)
        setTimeout(function() {
            $submitButtons.prop('disabled', false);
            $submitButtons.each(function() {
                const $btn = $(this);
                if ($btn.data('original-text')) {
                    $btn.html($btn.data('original-text'));
                }
            });
        }, 15000);
    });

    // Real-time validation feedback
    $('#title').on('input', function() {
        const $this = $(this);
        if ($this.val().length < 5) {
            $this.addClass('is-invalid');
        } else {
            $this.removeClass('is-invalid');
        }
    });

    $('#description').on('input', function() {
        const $this = $(this);
        const length = $this.val().length;
        if (length < 50) {
            $this.addClass('is-invalid');
            $this.siblings('.form-text').text(`${length}/50 characters (minimum 50 required)`);
        } else {
            $this.removeClass('is-invalid');
            $this.siblings('.form-text').text(`${length} characters`);
        }
    });

    // Auto-dismiss alerts after 8 seconds
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 8000);

    // Smooth scroll to alerts when they appear
    if ($('.alert').length > 0) {
        $('html, body').animate({ scrollTop: 0 }, 500);
    }
});

// Function to show dynamic success/error messages
function showAlert(type, title, message) {
    const iconClass = type === 'success' ? 'bi-check-circle-fill text-success' : 'bi-exclamation-triangle-fill text-danger';
    const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';

    const alertHtml = `
        <div class="alert ${alertClass} alert-dismissible fade show shadow-sm" role="alert">
            <div class="d-flex align-items-center">
                <div class="flex-shrink-0">
                    <i class="bi ${iconClass} fs-4"></i>
                </div>
                <div class="flex-grow-1 ms-3">
                    <h6 class="alert-heading mb-1">${title}</h6>
                    <p class="mb-0">${message}</p>
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    `;

    $('#alert-container').html(alertHtml);
    $('html, body').animate({ scrollTop: 0 }, 300);

    // Auto-dismiss after 8 seconds
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 8000);
}
</script>
@endpush

@push('styles')
<style>
    /* Enhanced Alert Styling */
    .alert {
        border: none;
        border-radius: 12px;
        padding: 1.25rem;
        margin-bottom: 1.5rem;
        animation: slideInDown 0.5s ease-out;
    }

    .alert-success {
        background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
        border-left: 4px solid #10b981;
    }

    .alert-danger {
        background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
        border-left: 4px solid #ef4444;
    }

    .alert-info {
        background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
        border-left: 4px solid #3b82f6;
    }

    .alert-heading {
        font-weight: 600;
        font-size: 1.1rem;
    }

    @keyframes slideInDown {
        from {
            transform: translateY(-20px);
            opacity: 0;
        }
        to {
            transform: translateY(0);
            opacity: 1;
        }
    }

    /* Button Loading States */
    .btn:disabled {
        opacity: 0.7;
        cursor: not-allowed;
    }

    .spinner-border-sm {
        width: 1rem;
        height: 1rem;
    }

    /* Form Focus States */
    .form-control:focus {
        border-color: #0ea5e9;
        box-shadow: 0 0 0 0.2rem rgba(14, 165, 233, 0.25);
    }

    .form-select:focus {
        border-color: #0ea5e9;
        box-shadow: 0 0 0 0.2rem rgba(14, 165, 233, 0.25);
    }
</style>
@endpush
