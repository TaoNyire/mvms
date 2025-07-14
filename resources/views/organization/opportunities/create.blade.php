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

    <!-- Opportunity Form -->
    <form id="opportunityForm" method="POST" action="{{ route('opportunities.store') }}">
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
                        <input type="text" class="form-control" id="title" name="title" 
                               placeholder="e.g., Community Health Education Volunteer" required>
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="urgency" class="form-label fw-bold">
                            Urgency Level <span class="text-danger">*</span>
                        </label>
                        <select class="form-select" id="urgency" name="urgency" required>
                            <option value="">Select Urgency</option>
                            <option value="low">Low</option>
                            <option value="medium" selected>Medium</option>
                            <option value="high">High</option>
                            <option value="urgent">Urgent</option>
                        </select>
                    </div>
                    <div class="col-12 mb-3">
                        <label for="description" class="form-label fw-bold">
                            Description <span class="text-danger">*</span>
                        </label>
                        <textarea class="form-control" id="description" name="description" rows="4" 
                                  placeholder="Describe the volunteer opportunity, what volunteers will do, and the impact they'll make..." required></textarea>
                        <div class="form-text">Minimum 50 characters</div>
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
                        <label for="application_deadline" class="form-label fw-bold">Application Deadline</label>
                        <input type="date" class="form-control" id="application_deadline" name="application_deadline">
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
                            <input class="form-check-input" type="checkbox" id="is_paid" name="is_paid">
                            <label class="form-check-label fw-bold" for="is_paid">
                                This is a paid opportunity
                            </label>
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
                                    <input class="form-check-input" type="checkbox" id="provides_transport" name="provides_transport">
                                    <label class="form-check-label" for="provides_transport">Transportation Provided</label>
                                </div>
                            </div>
                            <div class="col-md-4 mb-2">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="provides_meals" name="provides_meals">
                                    <label class="form-check-label" for="provides_meals">Meals Provided</label>
                                </div>
                            </div>
                            <div class="col-md-4 mb-2">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="provides_accommodation" name="provides_accommodation">
                                    <label class="form-check-label" for="provides_accommodation">Accommodation Provided</label>
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
    
    // Set minimum date to tomorrow
    const tomorrow = new Date();
    tomorrow.setDate(tomorrow.getDate() + 1);
    const minDate = tomorrow.toISOString().split('T')[0];
    $('#start_date, #application_deadline').attr('min', minDate);
    
    // Update end date minimum when start date changes
    $('#start_date').change(function() {
        $('#end_date').attr('min', this.value);
        $('#application_deadline').attr('max', this.value);
    });
    
    // Form submission
    $('#opportunityForm').submit(function(e) {
        const action = $('button[type="submit"]:focus').val() || 'draft';
        
        if (action === 'publish') {
            $(this).append('<input type="hidden" name="publish_now" value="1">');
        }
    });
});
</script>
@endpush
