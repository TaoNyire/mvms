@extends('layouts.volunteer')

@section('title', 'Complete Your Profile - MVMS')

@section('page-title', 'Complete Your Volunteer Profile')

@section('content')
<div class="container-fluid">
    <!-- Progress Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 bg-gradient" style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%);">
                <div class="card-body text-white p-4">
                    <h2 class="mb-2">
                        <i class="bi bi-person-plus me-2"></i>Complete Your Volunteer Profile
                    </h2>
                    <p class="mb-0 opacity-90">
                        Help us match you with the perfect volunteer opportunities by completing your profile.
                    </p>
                    <div class="progress mt-3" style="height: 8px;">
                        <div class="progress-bar bg-white" role="progressbar" style="width: 0%" id="progressBar"></div>
                    </div>
                    <small class="opacity-75 mt-2 d-block">
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
    <form id="volunteerProfileForm" action="{{ route('volunteer.profile.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        
        <!-- Step 1: Basic Information -->
        <div class="card mb-4" id="step1">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">
                    <i class="bi bi-person me-2"></i>Step 1: Basic Information
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="full_name" class="form-label fw-semibold">
                            Full Name <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control" id="full_name" name="full_name" 
                               value="{{ Auth::user()->name }}" required>
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="date_of_birth" class="form-label fw-semibold">
                            Date of Birth <span class="text-danger">*</span>
                        </label>
                        <input type="date" class="form-control" id="date_of_birth" name="date_of_birth" max="{{ date('Y-m-d', strtotime('-1 day')) }}" required>
                        <div class="form-text">Date must be before today</div>
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="gender" class="form-label fw-semibold">Gender</label>
                        <select class="form-select" id="gender" name="gender">
                            <option value="">Select Gender</option>
                            <option value="male">Male</option>
                            <option value="female">Female</option>
                            <option value="other">Other</option>
                            <option value="prefer_not_to_say">Prefer not to say</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="phone" class="form-label fw-semibold">
                            Phone Number <span class="text-danger">*</span>
                        </label>
                        <input type="tel" class="form-control" id="phone" name="phone" 
                               placeholder="+265 123 456 789" required>
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="alternative_phone" class="form-label fw-semibold">Alternative Phone</label>
                        <input type="tel" class="form-control" id="alternative_phone" name="alternative_phone" 
                               placeholder="+265 123 456 789">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="emergency_contact_name" class="form-label fw-semibold">
                            Emergency Contact Name <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control" id="emergency_contact_name" 
                               name="emergency_contact_name" required>
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="emergency_contact_phone" class="form-label fw-semibold">
                            Emergency Contact Phone <span class="text-danger">*</span>
                        </label>
                        <input type="tel" class="form-control" id="emergency_contact_phone" 
                               name="emergency_contact_phone" required>
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="col-12 mb-3">
                        <label for="bio" class="form-label fw-semibold">
                            About Yourself <span class="text-danger">*</span>
                        </label>
                        <textarea class="form-control" id="bio" name="bio" rows="4" 
                                  placeholder="Tell us about yourself, your interests, and why you want to volunteer..." required></textarea>
                        <div class="form-text">Minimum 50 characters</div>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Step 2: Location Information -->
        <div class="card mb-4" id="step2">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0">
                    <i class="bi bi-geo-alt me-2"></i>Step 2: Location Information
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-12 mb-3">
                        <label for="physical_address" class="form-label fw-semibold">
                            Physical Address <span class="text-danger">*</span>
                        </label>
                        <textarea class="form-control" id="physical_address" name="physical_address" 
                                  rows="2" placeholder="Enter your full physical address" required></textarea>
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="district" class="form-label fw-semibold">
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
                            <option value="Salima">Salima</option>
                            <option value="Balaka">Balaka</option>
                            <option value="Chiradzulu">Chiradzulu</option>
                            <option value="Chitipa">Chitipa</option>
                            <option value="Dedza">Dedza</option>
                            <option value="Dowa">Dowa</option>
                            <option value="Karonga">Karonga</option>
                            <option value="Machinga">Machinga</option>
                            <option value="Mchinji">Mchinji</option>
                            <option value="Mulanje">Mulanje</option>
                            <option value="Mwanza">Mwanza</option>
                            <option value="Neno">Neno</option>
                            <option value="Nkhata Bay">Nkhata Bay</option>
                            <option value="Nkhotakota">Nkhotakota</option>
                            <option value="Nsanje">Nsanje</option>
                            <option value="Ntcheu">Ntcheu</option>
                            <option value="Ntchisi">Ntchisi</option>
                            <option value="Phalombe">Phalombe</option>
                            <option value="Rumphi">Rumphi</option>
                            <option value="Thyolo">Thyolo</option>
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="region" class="form-label fw-semibold">
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
                        <label for="postal_code" class="form-label fw-semibold">Postal Code</label>
                        <input type="text" class="form-control" id="postal_code" name="postal_code">
                    </div>
                </div>
            </div>
        </div>

        <!-- Step 3: Skills and Interests -->
        <div class="card mb-4" id="step3">
            <div class="card-header bg-warning text-dark">
                <h5 class="mb-0">
                    <i class="bi bi-award me-2"></i>Step 3: Skills and Interests
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-12 mb-3">
                        <label class="form-label fw-semibold">
                            Skills <span class="text-danger">*</span>
                        </label>
                        <div class="row" id="skillsContainer">
                            <!-- Skills will be loaded dynamically -->
                        </div>
                        <div class="invalid-feedback" id="skills-error"></div>
                    </div>
                    <div class="col-12 mb-3">
                        <label for="experience_description" class="form-label fw-semibold">
                            Experience Description
                        </label>
                        <textarea class="form-control" id="experience_description" name="experience_description" 
                                  rows="3" placeholder="Describe your relevant experience and achievements..."></textarea>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="education_level" class="form-label fw-semibold">
                            Education Level <span class="text-danger">*</span>
                        </label>
                        <select class="form-select" id="education_level" name="education_level" required>
                            <option value="">Select Education Level</option>
                            <option value="Primary">Primary Education</option>
                            <option value="Secondary">Secondary Education</option>
                            <option value="Certificate">Certificate</option>
                            <option value="Diploma">Diploma</option>
                            <option value="Degree">Bachelor's Degree</option>
                            <option value="Masters">Master's Degree</option>
                            <option value="PhD">PhD</option>
                            <option value="Other">Other</option>
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="field_of_study" class="form-label fw-semibold">Field of Study</label>
                        <input type="text" class="form-control" id="field_of_study" name="field_of_study" 
                               placeholder="e.g., Computer Science, Education, Medicine">
                    </div>
                </div>
            </div>
        </div>

        <!-- Step 4: Availability -->
        <div class="card mb-4" id="step4">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0">
                    <i class="bi bi-calendar-event me-2"></i>Step 4: Availability
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-12 mb-3">
                        <label class="form-label fw-semibold">
                            Available Days <span class="text-danger">*</span>
                        </label>
                        <div class="row">
                            <div class="col-md-3 col-sm-6 mb-2">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="available_days[]"
                                           value="monday" id="day_monday">
                                    <label class="form-check-label" for="day_monday">Monday</label>
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-6 mb-2">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="available_days[]"
                                           value="tuesday" id="day_tuesday">
                                    <label class="form-check-label" for="day_tuesday">Tuesday</label>
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-6 mb-2">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="available_days[]"
                                           value="wednesday" id="day_wednesday">
                                    <label class="form-check-label" for="day_wednesday">Wednesday</label>
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-6 mb-2">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="available_days[]"
                                           value="thursday" id="day_thursday">
                                    <label class="form-check-label" for="day_thursday">Thursday</label>
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-6 mb-2">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="available_days[]"
                                           value="friday" id="day_friday">
                                    <label class="form-check-label" for="day_friday">Friday</label>
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-6 mb-2">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="available_days[]"
                                           value="saturday" id="day_saturday">
                                    <label class="form-check-label" for="day_saturday">Saturday</label>
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-6 mb-2">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="available_days[]"
                                           value="sunday" id="day_sunday">
                                    <label class="form-check-label" for="day_sunday">Sunday</label>
                                </div>
                            </div>
                        </div>
                        <div class="invalid-feedback" id="available_days-error"></div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="available_time_start" class="form-label fw-semibold">Available From</label>
                        <input type="time" class="form-control" id="available_time_start" name="available_time_start">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="available_time_end" class="form-label fw-semibold">Available Until</label>
                        <input type="time" class="form-control" id="available_time_end" name="available_time_end">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="availability_type" class="form-label fw-semibold">
                            Availability Type <span class="text-danger">*</span>
                        </label>
                        <select class="form-select" id="availability_type" name="availability_type" required>
                            <option value="">Select Availability Type</option>
                            <option value="full_time">Full Time</option>
                            <option value="part_time">Part Time</option>
                            <option value="weekends">Weekends Only</option>
                            <option value="flexible">Flexible</option>
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="form-check mt-4">
                            <input class="form-check-input @error('can_travel') is-invalid @enderror" type="checkbox" id="can_travel" name="can_travel" value="1" {{ old('can_travel') ? 'checked' : '' }}>
                            <label class="form-check-label" for="can_travel">
                                I can travel for volunteer work
                            </label>
                            @error('can_travel')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-12 mb-3" id="travel_distance_container" style="display: none;">
                        <label for="max_travel_distance" class="form-label fw-semibold">
                            Maximum Travel Distance (km)
                        </label>
                        <input type="number" class="form-control" id="max_travel_distance"
                               name="max_travel_distance" min="1" max="500" value="">
                    </div>
                </div>
            </div>
        </div>

        <!-- Step 5: Documents and Motivation -->
        <div class="card mb-4" id="step5">
            <div class="card-header bg-secondary text-white">
                <h5 class="mb-0">
                    <i class="bi bi-file-earmark-text me-2"></i>Step 5: Documents & Motivation
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="id_document" class="form-label fw-semibold">
                            ID Document (Optional)
                        </label>
                        <input type="file" class="form-control" id="id_document" name="id_document"
                               accept=".pdf,.jpg,.jpeg,.png">
                        <div class="form-text">Upload a copy of your national ID or passport</div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="cv_document" class="form-label fw-semibold">
                            CV/Resume (Optional)
                        </label>
                        <input type="file" class="form-control" id="cv_document" name="cv_document"
                               accept=".pdf,.doc,.docx">
                        <div class="form-text">Upload your CV or resume</div>
                    </div>
                    <div class="col-12 mb-3">
                        <label for="certificates" class="form-label fw-semibold">
                            Certificates (Optional)
                        </label>
                        <input type="file" class="form-control" id="certificates" name="certificates[]"
                               accept=".pdf,.jpg,.jpeg,.png" multiple>
                        <div class="form-text">Upload any relevant certificates or qualifications</div>
                    </div>
                    <div class="col-12 mb-3">
                        <label for="motivation" class="form-label fw-semibold">
                            Why do you want to volunteer? <span class="text-danger">*</span>
                        </label>
                        <textarea class="form-control" id="motivation" name="motivation" rows="4"
                                  placeholder="Tell us what motivates you to volunteer and what you hope to achieve..." required></textarea>
                        <div class="form-text">Minimum 100 characters</div>
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="col-12 mb-3">
                        <label class="form-label fw-semibold">Volunteer Experience</label>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="radio" name="has_volunteered_before"
                                   value="1" id="volunteered_yes">
                            <label class="form-check-label" for="volunteered_yes">
                                Yes, I have volunteered before
                            </label>
                        </div>
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="radio" name="has_volunteered_before"
                                   value="0" id="volunteered_no" checked>
                            <label class="form-check-label" for="volunteered_no">
                                No, this will be my first time
                            </label>
                        </div>
                        <div id="previous_experience_container" style="display: none;">
                            <label for="previous_volunteer_experience" class="form-label fw-semibold">
                                Previous Volunteer Experience
                            </label>
                            <textarea class="form-control" id="previous_volunteer_experience"
                                      name="previous_volunteer_experience" rows="3"
                                      placeholder="Describe your previous volunteer experience..."></textarea>
                        </div>
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
    const totalSteps = 5; // We'll add more steps
    
    // Load skills
    loadSkills();
    
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
        const currentStepElement = $(`#step${currentStep}`);
        let isValid = true;

        // Clear previous errors
        currentStepElement.find('.is-invalid').removeClass('is-invalid');
        currentStepElement.find('.invalid-feedback').hide();

        // Validate based on current step
        if (currentStep === 1) {
            // Step 1: Basic Information
            const requiredFields = ['full_name', 'date_of_birth', 'phone', 'emergency_contact_name', 'emergency_contact_phone', 'bio'];
            requiredFields.forEach(fieldName => {
                const field = $(`[name="${fieldName}"]`);
                if (!field.val() || field.val().trim() === '') {
                    field.addClass('is-invalid');
                    isValid = false;
                }
            });

            // Bio minimum length check
            const bio = $('#bio').val();
            if (bio && bio.length < 50) {
                $('#bio').addClass('is-invalid');
                $('#bio').siblings('.invalid-feedback').text('Bio must be at least 50 characters long').show();
                isValid = false;
            }
        } else if (currentStep === 2) {
            // Step 2: Location Information
            const requiredFields = ['physical_address', 'district', 'region'];
            requiredFields.forEach(fieldName => {
                const field = $(`[name="${fieldName}"]`);
                if (!field.val() || field.val().trim() === '') {
                    field.addClass('is-invalid');
                    isValid = false;
                }
            });
        } else if (currentStep === 3) {
            // Step 3: Skills and Education
            const selectedSkills = $('input[name="skills[]"]:checked').length;
            if (selectedSkills === 0) {
                $('#skills-error').text('Please select at least one skill').show();
                isValid = false;
            }

            const educationLevel = $('#education_level').val();
            if (!educationLevel) {
                $('#education_level').addClass('is-invalid');
                isValid = false;
            }
        } else if (currentStep === 4) {
            // Step 4: Availability
            const selectedDays = $('input[name="available_days[]"]:checked').length;
            if (selectedDays === 0) {
                $('#available_days-error').text('Please select at least one available day').show();
                isValid = false;
            }

            const availabilityType = $('#availability_type').val();
            if (!availabilityType) {
                $('#availability_type').addClass('is-invalid');
                isValid = false;
            }
        } else if (currentStep === 5) {
            // Step 5: Motivation
            const motivation = $('#motivation').val();
            if (!motivation || motivation.trim() === '') {
                $('#motivation').addClass('is-invalid');
                isValid = false;
            } else if (motivation.length < 100) {
                $('#motivation').addClass('is-invalid');
                $('#motivation').siblings('.invalid-feedback').text('Motivation must be at least 100 characters long').show();
                isValid = false;
            }
        }

        return isValid;
    }
    
    function loadSkills() {
        // Mock skills data - replace with actual API call
        const skills = [
            'Teaching', 'Healthcare', 'Technology', 'Agriculture', 'Construction',
            'Administration', 'Marketing', 'Finance', 'Social Work', 'Environment'
        ];
        
        const container = $('#skillsContainer');
        skills.forEach(skill => {
            container.append(`
                <div class="col-md-4 col-sm-6 mb-2">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="skills[]" 
                               value="${skill}" id="skill_${skill}">
                        <label class="form-check-label" for="skill_${skill}">
                            ${skill}
                        </label>
                    </div>
                </div>
            `);
        });
    }
    
    // Travel distance toggle
    $('#can_travel').change(function() {
        $('#travel_distance_container').toggle(this.checked);
    });

    // Previous experience toggle
    $('input[name="has_volunteered_before"]').change(function() {
        $('#previous_experience_container').toggle(this.value === '1');
    });

    // Quick submit for testing (bypasses validation)
    $('#quickSubmitBtn').click(function() {
        // Fill in minimum required fields automatically
        if (!$('#full_name').val()) $('#full_name').val($('#full_name').attr('value') || 'Test Volunteer');
        if (!$('#phone').val()) $('#phone').val('+265 123 456 789');
        if (!$('#bio').val()) $('#bio').val('This is a test bio for the volunteer profile. I am interested in helping the community through various volunteer activities.');
        if (!$('#physical_address').val()) $('#physical_address').val('Test Address, Lilongwe');
        if (!$('#district').val()) $('#district').val('Lilongwe');
        if (!$('#region').val()) $('#region').val('Central');
        if (!$('#education_level').val()) $('#education_level').val('Degree');
        if (!$('#motivation').val()) $('#motivation').val('I am motivated to volunteer because I want to give back to my community and help make a positive difference in people\'s lives. Through volunteering, I hope to develop new skills while contributing to meaningful causes.');

        // Select at least one skill
        if ($('input[name="skills[]"]:checked').length === 0) {
            $('input[name="skills[]"]:first').prop('checked', true);
        }

        // Select at least one available day
        if ($('input[name="available_days[]"]:checked').length === 0) {
            $('input[name="available_days[]"]:first').prop('checked', true);
        }

        // Set availability type
        if (!$('#availability_type').val()) $('#availability_type').val('flexible');

        // Submit the form
        $('#volunteerProfileForm').submit();
    });

    // Date of birth validation
    $('#date_of_birth').on('change', function() {
        const selectedDate = new Date($(this).val());
        const today = new Date();
        today.setHours(0, 0, 0, 0);

        if (selectedDate >= today) {
            showAlert('Date of birth must be before today.', 'warning');
            $(this).val('');
        }
    });

    // Form submission
    $('#volunteerProfileForm').submit(function(e) {
        e.preventDefault();

        // Basic validation - just check if we have a name and some basic info
        const fullName = $('#full_name').val();
        const phone = $('#phone').val();

        if (!fullName || !phone) {
            showAlert('Please provide at least your full name and phone number.', 'warning');
            return;
        }

        // Validate date of birth
        const dateOfBirth = $('#date_of_birth').val();
        if (dateOfBirth) {
            const selectedDate = new Date(dateOfBirth);
            const today = new Date();
            today.setHours(0, 0, 0, 0);

            if (selectedDate >= today) {
                showAlert('Date of birth must be before today.', 'warning');
                return;
            }
        }

        // Prepare form data
        const formData = new FormData(this);

        // Handle hidden fields - remove them if they're not visible
        if (!$('#can_travel').is(':checked')) {
            formData.delete('max_travel_distance');
        }

        // Ensure boolean fields are properly set
        formData.set('can_travel', $('#can_travel').is(':checked') ? '1' : '0');
        formData.set('has_volunteered_before', $('input[name="has_volunteered_before"]:checked').val() || '0');

        // Debug: Log form data
        console.log('Form data being sent:');
        for (let pair of formData.entries()) {
            console.log(pair[0] + ': ' + pair[1]);
        }

        const submitBtn = $('#submitBtn');

        // Set loading state
        submitBtn.prop('disabled', true);
        submitBtn.html('<span class="spinner-border spinner-border-sm me-2"></span>Saving...');

        $.ajax({
            url: '{{ route("volunteer.profile.store") }}',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    showAlert('Profile completed successfully! Redirecting to dashboard...', 'success');
                    setTimeout(() => {
                        window.location.href = '{{ route("volunteer.dashboard") }}';
                    }, 2000);
                } else {
                    showAlert(response.message || 'An error occurred. Please try again.', 'danger');
                }
            },
            error: function(xhr) {
                console.log('Error response:', xhr.responseJSON);
                if (xhr.status === 422) {
                    const errors = xhr.responseJSON.errors;
                    console.log('Validation errors:', errors);

                    let errorMessage = 'Validation errors:<br>';
                    Object.keys(errors).forEach(function(field) {
                        errorMessage += `• ${field}: ${errors[field][0]}<br>`;
                        showFieldError(field, errors[field][0]);
                    });
                    showAlert(errorMessage, 'danger');
                } else {
                    showAlert('An error occurred. Please try again.', 'danger');
                }
            },
            complete: function() {
                submitBtn.prop('disabled', false);
                submitBtn.html('<i class="bi bi-check-circle me-1"></i>Complete Profile');
            }
        });
    });

    // Helper functions
    function showAlert(message, type) {
        const alertHtml = `
            <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;

        // Insert alert at the top of the form
        $('#volunteerProfileForm').prepend(alertHtml);

        // Auto dismiss after 5 seconds
        setTimeout(() => {
            $('.alert').alert('close');
        }, 5000);
    }

    function showFieldError(fieldName, message) {
        const field = $(`[name="${fieldName}"]`);
        field.addClass('is-invalid');
        field.siblings('.invalid-feedback').text(message);
    }

    // Initialize first step
    showStep(1);
});
</script>
@endpush
