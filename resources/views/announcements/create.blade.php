@extends('layouts.' . (auth()->user()->hasRole('volunteer') ? 'volunteer' : 'organization'))

@section('title', 'Create Announcement - MVMS')

@section('page-title', 'Create Announcement')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bi bi-megaphone me-2"></i>Create New Announcement
                    </h5>
                    <a href="{{ route('announcements.index') }}" class="btn btn-secondary btn-sm">
                        <i class="bi bi-arrow-left me-1"></i>Back to Announcements
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

                    <form action="{{ route('announcements.store') }}" method="POST">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-8 mb-3">
                                <label for="title" class="form-label fw-bold">
                                    Title <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control @error('title') is-invalid @enderror" 
                                       id="title" name="title" value="{{ old('title') }}" 
                                       placeholder="Enter announcement title" required>
                                @error('title')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label for="priority" class="form-label fw-bold">Priority</label>
                                <select class="form-select @error('priority') is-invalid @enderror" id="priority" name="priority">
                                    <option value="normal" {{ old('priority') === 'normal' ? 'selected' : '' }}>Normal</option>
                                    <option value="high" {{ old('priority') === 'high' ? 'selected' : '' }}>High</option>
                                    <option value="urgent" {{ old('priority') === 'urgent' ? 'selected' : '' }}>Urgent</option>
                                </select>
                                @error('priority')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="category" class="form-label fw-bold">Category</label>
                                <select class="form-select @error('category') is-invalid @enderror" id="category" name="category">
                                    <option value="">Select Category</option>
                                    <option value="General" {{ old('category') === 'General' ? 'selected' : '' }}>General</option>
                                    <option value="Opportunities" {{ old('category') === 'Opportunities' ? 'selected' : '' }}>Opportunities</option>
                                    <option value="Events" {{ old('category') === 'Events' ? 'selected' : '' }}>Events</option>
                                    <option value="Training" {{ old('category') === 'Training' ? 'selected' : '' }}>Training</option>
                                    <option value="Policy" {{ old('category') === 'Policy' ? 'selected' : '' }}>Policy Updates</option>
                                    <option value="Emergency" {{ old('category') === 'Emergency' ? 'selected' : '' }}>Emergency</option>
                                </select>
                                @error('category')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="audience" class="form-label fw-bold">Target Audience</label>
                                <select class="form-select @error('audience') is-invalid @enderror" id="audience" name="audience">
                                    <option value="all" {{ old('audience') === 'all' ? 'selected' : '' }}>All Users</option>
                                    <option value="volunteers" {{ old('audience') === 'volunteers' ? 'selected' : '' }}>All Volunteers</option>
                                    <option value="my_volunteers" {{ old('audience') === 'my_volunteers' ? 'selected' : '' }}>
                                        My Organization's Volunteers
                                        @if(isset($volunteerCount) && $volunteerCount > 0)
                                            ({{ $volunteerCount }} volunteers)
                                        @else
                                            (No volunteers yet)
                                        @endif
                                    </option>
                                    <option value="organizations" {{ old('audience') === 'organizations' ? 'selected' : '' }}>Organizations Only</option>
                                </select>
                                <div class="form-text">
                                    <small>
                                        <strong>"My Organization's Volunteers"</strong> will only send to volunteers who have accepted positions with your organization.
                                        @if(isset($volunteerCount) && $volunteerCount > 0)
                                            You currently have {{ $volunteerCount }} active volunteer(s).
                                        @else
                                            You don't have any active volunteers yet. Volunteers will see this announcement once they join your organization.
                                        @endif
                                    </small>
                                </div>
                                @error('audience')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-12 mb-3">
                                <label for="content" class="form-label fw-bold">
                                    Content <span class="text-danger">*</span>
                                </label>
                                <textarea class="form-control @error('content') is-invalid @enderror" 
                                          id="content" name="content" rows="8" 
                                          placeholder="Write your announcement content here..." required>{{ old('content') }}</textarea>
                                @error('content')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="expires_at" class="form-label fw-bold">Expiration Date (Optional)</label>
                                <input type="datetime-local" class="form-control @error('expires_at') is-invalid @enderror" 
                                       id="expires_at" name="expires_at" value="{{ old('expires_at') }}">
                                <div class="form-text">Leave empty if announcement doesn't expire</div>
                                @error('expires_at')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Options</label>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="is_pinned" 
                                           name="is_pinned" value="1" {{ old('is_pinned') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_pinned">
                                        Pin this announcement
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="send_notification" 
                                           name="send_notification" value="1" {{ old('send_notification', true) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="send_notification">
                                        Send notification to users
                                    </label>
                                </div>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('announcements.index') }}" class="btn btn-secondary">
                                <i class="bi bi-x-circle me-1"></i>Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-megaphone me-1"></i>Publish Announcement
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
document.addEventListener('DOMContentLoaded', function() {
    // Set minimum datetime for expiration
    const expiresInput = document.getElementById('expires_at');
    const now = new Date();
    now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
    expiresInput.min = now.toISOString().slice(0, 16);
    
    // Character counter for content
    const contentTextarea = document.getElementById('content');
    const maxLength = 2000;
    
    const counterElement = document.createElement('div');
    counterElement.className = 'form-text text-end';
    counterElement.id = 'content-counter';
    contentTextarea.parentNode.appendChild(counterElement);
    
    function updateCounter() {
        const currentLength = contentTextarea.value.length;
        counterElement.textContent = `${currentLength}/${maxLength} characters`;
        
        if (currentLength > maxLength * 0.9) {
            counterElement.className = 'form-text text-end text-warning';
        } else {
            counterElement.className = 'form-text text-end text-muted';
        }
    }
    
    contentTextarea.addEventListener('input', updateCounter);
    updateCounter();
});
</script>
@endpush
@endsection
