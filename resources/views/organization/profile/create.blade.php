@extends('layouts.organization')

@section('title', 'Complete Organization Profile - MVMS')

@section('page-title', 'Complete Your Organization Profile')

@section('content')
<div class="container-fluid">
    <!-- Progress Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 bg-gradient" style="background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);">
                <div class="card-body text-white p-4">
                    <h2 class="mb-2">
                        <i class="bi bi-building me-2"></i>Complete Your Organization Profile
                    </h2>
                    <p class="mb-0">
                        Help volunteers find and connect with your organization by completing your profile.
                    </p>
                    <div class="progress mt-3" style="height: 8px;">
                        <div class="progress-bar bg-white" role="progressbar" style="width: 0%" id="progressBar"></div>
                    </div>
                    <small class="mt-2 d-block">
                        <span id="progressText">0% Complete</span> - All fields marked with * are required
                    </small>
                </div>
            </div>
        </div>
    </div>

    <!-- Error Display -->
    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <h6><i class="bi bi-exclamation-triangle me-2"></i>Please fix the following errors:</h6>
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Profile Form -->
    <form id="organizationProfileForm" action="{{ route('organization.profile.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        
        <!-- Step 1: Basic Organization Information -->
        <div class="card mb-4" id="step1">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">
                    <i class="bi bi-info-circle me-2"></i>Step 1: Basic Organization Information
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="org_name" class="form-label fw-bold">
                            Organization Name <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control" id="org_name" name="org_name" 
                               value="{{ Auth::user()->name }}" required>
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="org_type" class="form-label fw-bold">
                            Organization Type <span class="text-danger">*</span>
                        </label>
                        <select class="form-select" id="org_type" name="org_type" required>
                            <option value="">Select Organization Type</option>
                            <option value="NGO">Non-Governmental Organization (NGO)</option>
                            <option value="CBO">Community Based Organization (CBO)</option>
                            <option value="FBO">Faith Based Organization (FBO)</option>
                            <option value="Government">Government Agency</option>
                            <option value="Private">Private Company</option>
                            <option value="International">International Organization</option>
                            <option value="Educational">Educational Institution</option>
                            <option value="Healthcare">Healthcare Institution</option>
                            <option value="Other">Other</option>
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="sector" class="form-label fw-bold">
                            Primary Sector <span class="text-danger">*</span>
                        </label>
                        <select class="form-select" id="sector" name="sector" required>
                            <option value="">Select Primary Sector</option>
                            <option value="Education">Education</option>
                            <option value="Health">Health & Medical</option>
                            <option value="Environment">Environment & Conservation</option>
                            <option value="Agriculture">Agriculture & Food Security</option>
                            <option value="Water">Water & Sanitation</option>
                            <option value="Community">Community Development</option>
                            <option value="Youth">Youth Development</option>
                            <option value="Women">Women Empowerment</option>
                            <option value="Disability">Disability Support</option>
                            <option value="Emergency">Emergency Response</option>
                            <option value="Human Rights">Human Rights</option>
                            <option value="Technology">Technology & Innovation</option>
                            <option value="Arts">Arts & Culture</option>
                            <option value="Sports">Sports & Recreation</option>
                            <option value="Other">Other</option>
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="established_date" class="form-label fw-bold">
                            Established Date
                        </label>
                        <input type="date" class="form-control" id="established_date" name="established_date" max="{{ date('Y-m-d', strtotime('-1 day')) }}">
                        <div class="form-text">Date must be before today</div>
                    </div>
                    <div class="col-12 mb-3">
                        <label for="description" class="form-label fw-bold">
                            Organization Description <span class="text-danger">*</span>
                        </label>
                        <textarea class="form-control" id="description" name="description" rows="4" 
                                  placeholder="Describe your organization, its purpose, and activities..." required></textarea>
                        <div class="form-text">Minimum 50 characters</div>
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="mission" class="form-label fw-bold">
                            Mission Statement <span class="text-danger">*</span>
                        </label>
                        <textarea class="form-control" id="mission" name="mission" rows="3" 
                                  placeholder="Your organization's mission..." required></textarea>
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="vision" class="form-label fw-bold">
                            Vision Statement
                        </label>
                        <textarea class="form-control" id="vision" name="vision" rows="3" 
                                  placeholder="Your organization's vision..."></textarea>
                    </div>
                </div>
            </div>
        </div>

        <!-- Step 2: Registration & Legal Information -->
        <div class="card mb-4" id="step2">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0">
                    <i class="bi bi-file-earmark-check me-2"></i>Step 2: Registration & Legal Information
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-12 mb-3">
                        <div class="form-check">
                            <input class="form-check-input @error('is_registered') is-invalid @enderror" type="checkbox" id="is_registered" name="is_registered" value="1" {{ old('is_registered', true) ? 'checked' : '' }}>
                            <label class="form-check-label fw-bold" for="is_registered">
                                Organization is officially registered
                            </label>
                            @error('is_registered')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div id="registration_details">
                        <div class="col-md-6 mb-3">
                            <label for="registration_number" class="form-label fw-bold">
                                Registration Number
                            </label>
                            <input type="text" class="form-control" id="registration_number" name="registration_number" 
                                   placeholder="e.g., NGO/001/2020">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="registration_authority" class="form-label fw-bold">
                                Registration Authority
                            </label>
                            <select class="form-select" id="registration_authority" name="registration_authority">
                                <option value="">Select Registration Authority</option>
                                <option value="NGO Board">NGO Board</option>
                                <option value="Ministry of Gender">Ministry of Gender, Community Development and Social Welfare</option>
                                <option value="Registrar General">Registrar General</option>
                                <option value="District Council">District Council</option>
                                <option value="Traditional Authority">Traditional Authority</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="registration_date" class="form-label fw-bold">
                                Registration Date
                            </label>
                            <input type="date" class="form-control" id="registration_date" name="registration_date" max="{{ date('Y-m-d') }}">
                            <div class="form-text">Date cannot be in the future</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="tax_id" class="form-label fw-bold">
                                Tax ID / TPIN
                            </label>
                            <input type="text" class="form-control" id="tax_id" name="tax_id" 
                                   placeholder="Tax Payer Identification Number">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Step 3: Contact Information -->
        <div class="card mb-4" id="step3">
            <div class="card-header bg-warning text-dark">
                <h5 class="mb-0">
                    <i class="bi bi-telephone me-2"></i>Step 3: Contact Information
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-12 mb-3">
                        <label for="physical_address" class="form-label fw-bold">
                            Physical Address <span class="text-danger">*</span>
                        </label>
                        <textarea class="form-control" id="physical_address" name="physical_address" 
                                  rows="2" placeholder="Enter your organization's physical address" required></textarea>
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="district" class="form-label fw-bold">
                            District <span class="text-danger">*</span>
                        </label>
                        <select class="form-select" id="district" name="district" required>
                            <option value="">Select District</option>
                            <option value="Lilongwe">Lilongwe</option>
                            <option value="Blantyre">Blantyre</option>
                            <option value="Mzuzu">Mzuzu</option>
                            <option value="Zomba">Zomba</option>
                            <option value="Kasungu">Kasungu</option>
                            <option value="Mangochi">Mangochi</option>
                            <!-- Add more districts as needed -->
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="region" class="form-label fw-bold">
                            Region <span class="text-danger">*</span>
                        </label>
                        <select class="form-select" id="region" name="region" required>
                            <option value="">Select Region</option>
                            <option value="Northern">Northern</option>
                            <option value="Central">Central</option>
                            <option value="Southern">Southern</option>
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="postal_address" class="form-label fw-bold">Postal Address</label>
                        <input type="text" class="form-control" id="postal_address" name="postal_address" 
                               placeholder="P.O. Box 123, Lilongwe">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="email" class="form-label fw-bold">
                            Email Address <span class="text-danger">*</span>
                        </label>
                        <input type="email" class="form-control" id="email" name="email" 
                               value="{{ Auth::user()->email }}" required>
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="phone" class="form-label fw-bold">
                            Phone Number <span class="text-danger">*</span>
                        </label>
                        <input type="tel" class="form-control" id="phone" name="phone" 
                               placeholder="+265 123 456 789" required>
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="alternative_phone" class="form-label fw-bold">Alternative Phone</label>
                        <input type="tel" class="form-control" id="alternative_phone" name="alternative_phone" 
                               placeholder="+265 987 654 321">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="website" class="form-label fw-bold">Website</label>
                        <input type="url" class="form-control" id="website" name="website"
                               placeholder="https://www.yourorganization.org">
                        <div class="form-text">Please include http:// or https:// (e.g., https://www.example.org)</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Step 4: Focus Areas & Contact Person -->
        <div class="card mb-4" id="step4">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0">
                    <i class="bi bi-person-badge me-2"></i>Step 4: Focus Areas & Contact Person
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-12 mb-3">
                        <label class="form-label fw-bold">
                            Focus Areas <span class="text-danger">*</span>
                        </label>
                        <div class="form-text mb-2">Select the main areas your organization focuses on (select at least one)</div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="focus_areas[]" value="Education" id="focus_education">
                                    <label class="form-check-label" for="focus_education">Education</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="focus_areas[]" value="Health" id="focus_health">
                                    <label class="form-check-label" for="focus_health">Health & Medical</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="focus_areas[]" value="Environment" id="focus_environment">
                                    <label class="form-check-label" for="focus_environment">Environment</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="focus_areas[]" value="Agriculture" id="focus_agriculture">
                                    <label class="form-check-label" for="focus_agriculture">Agriculture</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="focus_areas[]" value="Water" id="focus_water">
                                    <label class="form-check-label" for="focus_water">Water & Sanitation</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="focus_areas[]" value="Community" id="focus_community">
                                    <label class="form-check-label" for="focus_community">Community Development</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="focus_areas[]" value="Youth" id="focus_youth">
                                    <label class="form-check-label" for="focus_youth">Youth Development</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="focus_areas[]" value="Women" id="focus_women">
                                    <label class="form-check-label" for="focus_women">Women Empowerment</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="focus_areas[]" value="Disability" id="focus_disability">
                                    <label class="form-check-label" for="focus_disability">Disability Support</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="focus_areas[]" value="Emergency" id="focus_emergency">
                                    <label class="form-check-label" for="focus_emergency">Emergency Response</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="focus_areas[]" value="Human Rights" id="focus_rights">
                                    <label class="form-check-label" for="focus_rights">Human Rights</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="focus_areas[]" value="Other" id="focus_other">
                                    <label class="form-check-label" for="focus_other">Other</label>
                                </div>
                            </div>
                        </div>
                        <div class="invalid-feedback" id="focus-areas-error"></div>
                    </div>

                    <div class="col-12">
                        <h6 class="fw-bold mb-3">Primary Contact Person</h6>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="contact_person_name" class="form-label fw-bold">
                            Contact Person Name <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control" id="contact_person_name" name="contact_person_name"
                               placeholder="Full name of primary contact person" required>
                        <div class="invalid-feedback"></div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="contact_person_title" class="form-label fw-bold">
                            Contact Person Title
                        </label>
                        <input type="text" class="form-control" id="contact_person_title" name="contact_person_title"
                               placeholder="e.g., Executive Director, Program Manager">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="contact_person_email" class="form-label fw-bold">
                            Contact Person Email <span class="text-danger">*</span>
                        </label>
                        <input type="email" class="form-control" id="contact_person_email" name="contact_person_email"
                               placeholder="contact@organization.org" required>
                        <div class="invalid-feedback"></div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="contact_person_phone" class="form-label fw-bold">
                            Contact Person Phone
                        </label>
                        <input type="tel" class="form-control" id="contact_person_phone" name="contact_person_phone"
                               placeholder="+265 123 456 789">
                    </div>
                </div>
            </div>
        </div>

        <!-- Navigation Buttons -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between">
                    <button type="button" class="btn btn-secondary" id="prevBtn" style="display: none;">
                        <i class="bi bi-arrow-left me-1"></i>Previous
                    </button>
                    <button type="button" class="btn btn-primary" id="nextBtn">
                        Next <i class="bi bi-arrow-right ms-1"></i>
                    </button>
                    <button type="submit" class="btn btn-success" id="submitBtn" style="display: none;">
                        <i class="bi bi-check-circle me-1"></i>Complete Profile
                    </button>
                    <button type="button" class="btn btn-warning" id="quickSubmitBtn" style="display: none;">
                        <i class="bi bi-lightning me-1"></i>Quick Submit (Testing)
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    let currentStep = 1;
    const totalSteps = 4; // Updated to 4 steps
    
    // Update progress
    updateProgress();
    
    // Navigation
    $('#nextBtn').click(function() {
        if (validateCurrentStep()) {
            if (currentStep < totalSteps) {
                currentStep++;
                showStep(currentStep);
                updateProgress();
            }
        }
    });
    
    $('#prevBtn').click(function() {
        if (currentStep > 1) {
            currentStep--;
            showStep(currentStep);
            updateProgress();
        }
    });
    
    function showStep(step) {
        // Hide all steps
        $('[id^="step"]').hide();
        
        // Show current step
        $(`#step${step}`).show();
        
        // Update navigation buttons
        $('#prevBtn').toggle(step > 1);
        $('#nextBtn').toggle(step < totalSteps);
        $('#submitBtn').toggle(step === totalSteps);
        $('#quickSubmitBtn').toggle(step === totalSteps);
    }
    
    function updateProgress() {
        const progress = (currentStep / totalSteps) * 100;
        $('#progressBar').css('width', progress + '%');
        $('#progressText').text(Math.round(progress) + '% Complete');
    }
    
    function validateCurrentStep() {
        // Basic validation - just check required fields
        const currentStepElement = $(`#step${currentStep}`);
        const requiredFields = currentStepElement.find('[required]');
        let isValid = true;

        requiredFields.each(function() {
            const field = $(this);
            if (!field.val() || field.val().trim() === '') {
                field.addClass('is-invalid');
                isValid = false;
            } else {
                field.removeClass('is-invalid');
            }
        });

        // Special validation for Step 4 (Focus Areas)
        if (currentStep === 4) {
            const selectedFocusAreas = $('input[name="focus_areas[]"]:checked').length;
            if (selectedFocusAreas === 0) {
                $('#focus-areas-error').text('Please select at least one focus area').show();
                isValid = false;
            } else {
                $('#focus-areas-error').hide();
            }
        }

        return isValid;
    }
    
    // Registration toggle
    $('#is_registered').change(function() {
        $('#registration_details').toggle(this.checked);
    });
    
    // Quick submit for testing
    $('#quickSubmitBtn').click(function() {
        // Fill in minimum required fields automatically
        if (!$('#org_name').val()) $('#org_name').val('Test Organization');
        if (!$('#org_type').val()) $('#org_type').val('NGO');
        if (!$('#sector').val()) $('#sector').val('Community');
        if (!$('#description').val()) $('#description').val('This is a test organization profile created for testing purposes. We work in community development and volunteer coordination.');
        if (!$('#mission').val()) $('#mission').val('To serve the community through volunteer coordination and development programs.');
        if (!$('#physical_address').val()) $('#physical_address').val('Test Address, Lilongwe');
        if (!$('#district').val()) $('#district').val('Lilongwe');
        if (!$('#region').val()) $('#region').val('Central');
        if (!$('#email').val()) $('#email').val($('#email').attr('value') || 'test@organization.org');
        if (!$('#phone').val()) $('#phone').val('+265 123 456 789');

        // Fill in Step 4 required fields
        if (!$('#contact_person_name').val()) $('#contact_person_name').val('Test Contact Person');
        if (!$('#contact_person_email').val()) $('#contact_person_email').val('contact@test-org.org');

        // Select at least one focus area
        if ($('input[name="focus_areas[]"]:checked').length === 0) {
            $('#focus_community').prop('checked', true);
        }

        // Submit the form
        $('#organizationProfileForm').submit();
    });
    
    // Website validation helper
    $('#website').on('blur', function() {
        let website = $(this).val().trim();
        if (website && !website.match(/^https?:\/\//)) {
            $(this).val('https://' + website);
        }
    });

    // Established date validation
    $('#established_date').on('change', function() {
        const selectedDate = new Date($(this).val());
        const today = new Date();
        today.setHours(0, 0, 0, 0);

        if (selectedDate >= today) {
            showAlert('Established date must be before today.', 'warning');
            $(this).val('');
        }
    });

    // Registration date validation
    $('#registration_date').on('change', function() {
        const selectedDate = new Date($(this).val());
        const today = new Date();
        today.setHours(23, 59, 59, 999); // Allow today's date

        if (selectedDate > today) {
            showAlert('Registration date cannot be in the future.', 'warning');
            $(this).val('');
        }
    });

    // Form submission
    $('#organizationProfileForm').submit(function(e) {
        e.preventDefault();

        // Basic validation
        const orgName = $('#org_name').val();
        const email = $('#email').val();
        const phone = $('#phone').val();
        const contactPersonName = $('#contact_person_name').val();
        const contactPersonEmail = $('#contact_person_email').val();

        if (!orgName || !email || !phone) {
            showAlert('Please provide at least organization name, email, and phone number.', 'warning');
            return;
        }

        if (!contactPersonName || !contactPersonEmail) {
            showAlert('Please provide contact person name and email.', 'warning');
            return;
        }

        // Validate focus areas
        const selectedFocusAreas = $('input[name="focus_areas[]"]:checked').length;
        if (selectedFocusAreas === 0) {
            showAlert('Please select at least one focus area.', 'warning');
            return;
        }

        // Validate established date
        const establishedDate = $('#established_date').val();
        if (establishedDate) {
            const selectedDate = new Date(establishedDate);
            const today = new Date();
            today.setHours(0, 0, 0, 0);

            if (selectedDate >= today) {
                showAlert('Established date must be before today.', 'warning');
                return;
            }
        }

        // Validate registration date
        const registrationDate = $('#registration_date').val();
        if (registrationDate) {
            const selectedDate = new Date(registrationDate);
            const today = new Date();
            today.setHours(23, 59, 59, 999);

            if (selectedDate > today) {
                showAlert('Registration date cannot be in the future.', 'warning');
                return;
            }
        }

        // Validate required fields
        const description = $('#description').val();
        const mission = $('#mission').val();

        if (description && description.length < 50) {
            showAlert('Organization description must be at least 50 characters.', 'warning');
            return;
        }

        if (!mission || mission.trim() === '') {
            showAlert('Mission statement is required.', 'warning');
            return;
        }

        const formData = new FormData(this);

        // Debug: Log form data before sending
        console.log('Form data being sent:');
        for (let pair of formData.entries()) {
            console.log(pair[0] + ': ' + pair[1]);
        }

        const submitBtn = $('#submitBtn');
        
        // Set loading state
        submitBtn.prop('disabled', true);
        submitBtn.html('<span class="spinner-border spinner-border-sm me-2"></span>Saving...');
        
        $.ajax({
            url: '{{ route("organization.profile.store") }}',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    showAlert('Organization profile completed successfully! Redirecting to dashboard...', 'success');
                    setTimeout(() => {
                        window.location.href = '{{ route("organization.dashboard") }}';
                    }, 2000);
                } else {
                    showAlert(response.message || 'An error occurred. Please try again.', 'danger');
                }
            },
            error: function(xhr) {
                console.log('Error response:', xhr.responseJSON);
                console.log('Status:', xhr.status);
                console.log('Response text:', xhr.responseText);

                if (xhr.status === 422) {
                    const errors = xhr.responseJSON.errors;
                    let errorMessage = 'Please fix the following errors:<br><ul>';
                    Object.keys(errors).forEach(function(field) {
                        const fieldName = field.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
                        errorMessage += `<li><strong>${fieldName}:</strong> ${errors[field][0]}</li>`;
                    });
                    errorMessage += '</ul>';
                    showAlert(errorMessage, 'danger');
                } else if (xhr.status === 500) {
                    showAlert('Server error occurred. Please check your data and try again.', 'danger');
                } else if (xhr.status === 419) {
                    showAlert('Session expired. Please refresh the page and try again.', 'danger');
                } else {
                    showAlert(`An error occurred (${xhr.status}). Please try again.`, 'danger');
                }
            },
            complete: function() {
                submitBtn.prop('disabled', false);
                submitBtn.html('<i class="bi bi-check-circle me-1"></i>Complete Profile');
            }
        });
    });
    
    function showAlert(message, type) {
        const alertHtml = `
            <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        
        $('#organizationProfileForm').prepend(alertHtml);
        
        setTimeout(() => {
            $('.alert').alert('close');
        }, 5000);
    }
    
    // Initialize first step
    showStep(1);
});
</script>
@endpush
