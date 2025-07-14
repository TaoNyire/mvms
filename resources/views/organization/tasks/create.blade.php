@extends('layouts.organization')

@section('title', 'Create Task - MVMS')

@section('page-title', 'Create New Task')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 bg-gradient" style="background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);">
                <div class="card-body text-white p-4">
                    <h2 class="mb-2">
                        <i class="bi bi-plus-circle me-2"></i>Create New Task
                    </h2>
                    <p class="mb-0">
                        Create a specific task for the opportunity: <strong>{{ $opportunity->title }}</strong>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Task Form -->
    <form id="taskForm" method="POST" action="{{ route('tasks.store', $opportunity) }}">
        @csrf
        
        <!-- Basic Information -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">
                    <i class="bi bi-info-circle me-2"></i>Task Details
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-8 mb-3">
                        <label for="title" class="form-label fw-bold">
                            Task Title <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control" id="title" name="title" 
                               placeholder="e.g., Set up registration booth" required>
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="priority" class="form-label fw-bold">
                            Priority <span class="text-danger">*</span>
                        </label>
                        <select class="form-select" id="priority" name="priority" required>
                            <option value="">Select Priority</option>
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
                                  placeholder="Describe what volunteers will do in this task..." required></textarea>
                        <div class="form-text">Minimum 20 characters</div>
                    </div>
                    <div class="col-12 mb-3">
                        <label for="instructions" class="form-label fw-bold">
                            Detailed Instructions
                        </label>
                        <textarea class="form-control" id="instructions" name="instructions" rows="3" 
                                  placeholder="Provide step-by-step instructions for volunteers..."></textarea>
                    </div>
                </div>
            </div>
        </div>

        <!-- Scheduling -->
        <div class="card mb-4">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0">
                    <i class="bi bi-calendar me-2"></i>Schedule & Timing
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="start_datetime" class="form-label fw-bold">
                            Start Date & Time <span class="text-danger">*</span>
                        </label>
                        <input type="datetime-local" class="form-control" id="start_datetime" name="start_datetime" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="end_datetime" class="form-label fw-bold">
                            End Date & Time <span class="text-danger">*</span>
                        </label>
                        <input type="datetime-local" class="form-control" id="end_datetime" name="end_datetime" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="estimated_hours" class="form-label fw-bold">Estimated Hours</label>
                        <input type="number" class="form-control" id="estimated_hours" name="estimated_hours" 
                               min="1" max="24" placeholder="e.g., 4">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="assignment_deadline" class="form-label fw-bold">Assignment Deadline</label>
                        <input type="datetime-local" class="form-control" id="assignment_deadline" name="assignment_deadline">
                        <div class="form-text">Deadline for assigning volunteers to this task</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Location -->
        <div class="card mb-4">
            <div class="card-header bg-warning text-dark">
                <h5 class="mb-0">
                    <i class="bi bi-geo-alt me-2"></i>Location
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
                    <div class="col-md-8 mb-3" id="address_field">
                        <label for="location_address" class="form-label fw-bold">
                            Location Address <span class="text-danger">*</span>
                        </label>
                        <textarea class="form-control" id="location_address" name="location_address" rows="2" 
                                  placeholder="Enter the specific address or location details"></textarea>
                    </div>
                    <div class="col-12 mb-3">
                        <label for="location_instructions" class="form-label fw-bold">Location Instructions</label>
                        <textarea class="form-control" id="location_instructions" name="location_instructions" rows="2" 
                                  placeholder="Additional directions or meeting point instructions..."></textarea>
                    </div>
                </div>
            </div>
        </div>

        <!-- Volunteer Requirements -->
        <div class="card mb-4">
            <div class="card-header bg-success text-white">
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
                               min="1" max="50" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="assignment_type" class="form-label fw-bold">
                            Assignment Method <span class="text-danger">*</span>
                        </label>
                        <select class="form-select" id="assignment_type" name="assignment_type" required>
                            <option value="">Select Method</option>
                            <option value="manual" selected>Manual Assignment</option>
                            <option value="automatic">Automatic Assignment</option>
                            <option value="first_come">First Come, First Served</option>
                        </select>
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
                                    <input class="form-check-input" type="checkbox" name="required_skills[]" value="Administration" id="skill_administration">
                                    <label class="form-check-label" for="skill_administration">Administration</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 mb-3">
                        <label for="special_requirements" class="form-label fw-bold">Special Requirements</label>
                        <textarea class="form-control" id="special_requirements" name="special_requirements" rows="3" 
                                  placeholder="Any special requirements, qualifications, or equipment needed..."></textarea>
                    </div>
                </div>
            </div>
        </div>

        <!-- Additional Options -->
        <div class="card mb-4">
            <div class="card-header bg-secondary text-white">
                <h5 class="mb-0">
                    <i class="bi bi-gear me-2"></i>Additional Options
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-12 mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="allow_self_assignment" name="allow_self_assignment">
                            <label class="form-check-label fw-bold" for="allow_self_assignment">
                                Allow volunteers to self-assign to this task
                            </label>
                        </div>
                    </div>
                    <div class="col-12 mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="requires_check_in" name="requires_check_in">
                            <label class="form-check-label fw-bold" for="requires_check_in">
                                Require volunteers to check in when they arrive
                            </label>
                        </div>
                    </div>
                    <div class="col-12 mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="requires_check_out" name="requires_check_out">
                            <label class="form-check-label fw-bold" for="requires_check_out">
                                Require volunteers to check out when they leave
                            </label>
                        </div>
                    </div>
                    <div class="col-12 mb-3">
                        <label for="safety_requirements" class="form-label fw-bold">Safety Requirements</label>
                        <textarea class="form-control" id="safety_requirements" name="safety_requirements" rows="2" 
                                  placeholder="Any safety protocols or requirements..."></textarea>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between">
                    <a href="{{ route('tasks.index', $opportunity) }}" class="btn btn-secondary">
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
        const addressField = $('#address_field');
        
        if (locationType === 'remote') {
            addressField.hide();
            $('#location_address').prop('required', false);
        } else {
            addressField.show();
            $('#location_address').prop('required', true);
        }
    });
    
    // Set minimum datetime to now
    const now = new Date();
    now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
    const minDateTime = now.toISOString().slice(0, 16);
    $('#start_datetime, #assignment_deadline').attr('min', minDateTime);
    
    // Update end datetime minimum when start datetime changes
    $('#start_datetime').change(function() {
        $('#end_datetime').attr('min', this.value);
        $('#assignment_deadline').attr('max', this.value);
    });
    
    // Form submission
    $('#taskForm').submit(function(e) {
        const action = $('button[type="submit"]:focus').val() || 'draft';
        
        if (action === 'publish') {
            $(this).append('<input type="hidden" name="publish_now" value="1">');
        }
    });
});
</script>
@endpush
