@extends('layouts.' . (auth()->user()->hasRole('volunteer') ? 'volunteer' : 'organization'))

@section('title', 'New Message - MVMS')

@section('page-title', 'New Message')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bi bi-plus-circle me-2"></i>Start New Conversation
                    </h5>
                    <a href="{{ route('messages.index') }}" class="btn btn-secondary btn-sm">
                        <i class="bi bi-arrow-left me-1"></i>Back to Messages
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

                    <form action="{{ route('messages.store') }}" method="POST">
                        @csrf
                        
                        <div class="mb-3">
                            <label for="recipients" class="form-label fw-bold">
                                Recipients <span class="text-danger">*</span>
                            </label>
                            <select class="form-select @error('recipients') is-invalid @enderror" 
                                    id="recipients" name="recipients[]" multiple required>
                                @foreach($recipients as $recipient)
                                    <option value="{{ $recipient->id }}" {{ in_array($recipient->id, old('recipients', [])) ? 'selected' : '' }}>
                                        {{ $recipient->name }} 
                                        @if($recipient->hasRole('volunteer') && $recipient->volunteerProfile)
                                            ({{ $recipient->volunteerProfile->full_name ?? 'Volunteer' }})
                                        @elseif($recipient->hasRole('organization') && $recipient->organizationProfile)
                                            ({{ $recipient->organizationProfile->org_name ?? 'Organization' }})
                                        @endif
                                        - {{ $recipient->email }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="form-text">Hold Ctrl (or Cmd) to select multiple recipients</div>
                            @error('recipients')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-3">
                            <label for="subject" class="form-label fw-bold">
                                Subject <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control @error('subject') is-invalid @enderror" 
                                   id="subject" name="subject" value="{{ old('subject') }}" 
                                   placeholder="Enter message subject" required>
                            @error('subject')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-3">
                            <label for="content" class="form-label fw-bold">
                                Message <span class="text-danger">*</span>
                            </label>
                            <textarea class="form-control @error('content') is-invalid @enderror" 
                                      id="content" name="content" rows="6" 
                                      placeholder="Type your message here..." required>{{ old('content') }}</textarea>
                            @error('content')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('messages.index') }}" class="btn btn-secondary">
                                <i class="bi bi-x-circle me-1"></i>Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-send me-1"></i>Send Message
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
    // Initialize select2 for better multi-select experience (if available)
    if (typeof $ !== 'undefined' && $.fn.select2) {
        $('#recipients').select2({
            placeholder: 'Select recipients...',
            allowClear: true
        });
    }
    
    // Character counter for message content
    const contentTextarea = document.getElementById('content');
    const maxLength = 1000; // Set a reasonable limit
    
    // Create character counter element
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
    updateCounter(); // Initial count
});
</script>
@endpush
@endsection
