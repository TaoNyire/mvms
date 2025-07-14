@extends('layouts.organization')

@section('title', 'Edit Opportunity - MVMS')

@section('page-title', 'Edit Opportunity')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bi bi-pencil me-2"></i>Edit Opportunity
                    </h5>
                    <a href="{{ route('organization.opportunities.show', $opportunity) }}" class="btn btn-secondary btn-sm">
                        <i class="bi bi-arrow-left me-1"></i>Back to Opportunity
                    </a>
                </div>
                <div class="card-body">
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

                    <form action="{{ route('organization.opportunities.update', $opportunity) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <!-- Basic Information -->
                            <div class="col-12 mb-4">
                                <h6 class="fw-bold text-primary mb-3">Basic Information</h6>
                                
                                <div class="mb-3">
                                    <label for="title" class="form-label fw-bold">
                                        Opportunity Title <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" class="form-control @error('title') is-invalid @enderror" 
                                           id="title" name="title" value="{{ old('title', $opportunity->title) }}" required>
                                    @error('title')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="mb-3">
                                    <label for="description" class="form-label fw-bold">
                                        Description <span class="text-danger">*</span>
                                    </label>
                                    <textarea class="form-control @error('description') is-invalid @enderror" 
                                              id="description" name="description" rows="4" required>{{ old('description', $opportunity->description) }}</textarea>
                                    @error('description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="mb-3">
                                    <label for="requirements" class="form-label fw-bold">Requirements</label>
                                    <textarea class="form-control @error('requirements') is-invalid @enderror" 
                                              id="requirements" name="requirements" rows="3">{{ old('requirements', $opportunity->requirements) }}</textarea>
                                    @error('requirements')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <!-- Details -->
                            <div class="col-md-6 mb-3">
                                <label for="category" class="form-label fw-bold">Category</label>
                                <select class="form-select @error('category') is-invalid @enderror" id="category" name="category">
                                    <option value="">Select Category</option>
                                    <option value="Education" {{ old('category', $opportunity->category) === 'Education' ? 'selected' : '' }}>Education</option>
                                    <option value="Health" {{ old('category', $opportunity->category) === 'Health' ? 'selected' : '' }}>Health</option>
                                    <option value="Environment" {{ old('category', $opportunity->category) === 'Environment' ? 'selected' : '' }}>Environment</option>
                                    <option value="Community" {{ old('category', $opportunity->category) === 'Community' ? 'selected' : '' }}>Community Development</option>
                                    <option value="Youth" {{ old('category', $opportunity->category) === 'Youth' ? 'selected' : '' }}>Youth Development</option>
                                    <option value="Women" {{ old('category', $opportunity->category) === 'Women' ? 'selected' : '' }}>Women Empowerment</option>
                                    <option value="Agriculture" {{ old('category', $opportunity->category) === 'Agriculture' ? 'selected' : '' }}>Agriculture</option>
                                    <option value="Technology" {{ old('category', $opportunity->category) === 'Technology' ? 'selected' : '' }}>Technology</option>
                                    <option value="Other" {{ old('category', $opportunity->category) === 'Other' ? 'selected' : '' }}>Other</option>
                                </select>
                                @error('category')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="status" class="form-label fw-bold">Status</label>
                                <select class="form-select @error('status') is-invalid @enderror" id="status" name="status">
                                    <option value="active" {{ old('status', $opportunity->status) === 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="paused" {{ old('status', $opportunity->status) === 'paused' ? 'selected' : '' }}>Paused</option>
                                    <option value="closed" {{ old('status', $opportunity->status) === 'closed' ? 'selected' : '' }}>Closed</option>
                                </select>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="location" class="form-label fw-bold">Location</label>
                                <input type="text" class="form-control @error('location') is-invalid @enderror" 
                                       id="location" name="location" value="{{ old('location', $opportunity->location) }}" 
                                       placeholder="e.g., Lilongwe, Malawi">
                                @error('location')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="max_volunteers" class="form-label fw-bold">Maximum Volunteers</label>
                                <input type="number" class="form-control @error('max_volunteers') is-invalid @enderror" 
                                       id="max_volunteers" name="max_volunteers" value="{{ old('max_volunteers', $opportunity->max_volunteers) }}" 
                                       min="1" placeholder="e.g., 10">
                                @error('max_volunteers')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="start_date" class="form-label fw-bold">Start Date</label>
                                <input type="date" class="form-control @error('start_date') is-invalid @enderror" 
                                       id="start_date" name="start_date" value="{{ old('start_date', $opportunity->start_date ? $opportunity->start_date->format('Y-m-d') : '') }}">
                                @error('start_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="end_date" class="form-label fw-bold">End Date</label>
                                <input type="date" class="form-control @error('end_date') is-invalid @enderror" 
                                       id="end_date" name="end_date" value="{{ old('end_date', $opportunity->end_date ? $opportunity->end_date->format('Y-m-d') : '') }}">
                                @error('end_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('organization.opportunities.show', $opportunity) }}" class="btn btn-secondary">
                                <i class="bi bi-x-circle me-1"></i>Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle me-1"></i>Update Opportunity
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Date validation
    document.getElementById('start_date').addEventListener('change', function() {
        const startDate = this.value;
        const endDateInput = document.getElementById('end_date');
        
        if (startDate) {
            endDateInput.min = startDate;
            if (endDateInput.value && endDateInput.value < startDate) {
                endDateInput.value = '';
            }
        }
    });
    
    document.getElementById('end_date').addEventListener('change', function() {
        const endDate = this.value;
        const startDateInput = document.getElementById('start_date');
        
        if (endDate && startDateInput.value && endDate < startDateInput.value) {
            alert('End date cannot be before start date');
            this.value = '';
        }
    });
</script>
@endpush
@endsection
