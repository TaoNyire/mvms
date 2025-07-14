@extends('layouts.' . (auth()->user()->hasRole('volunteer') ? 'volunteer' : 'organization'))

@section('title', 'Notification Preferences - MVMS')

@section('page-title', 'Notification Preferences')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bi bi-gear me-2"></i>Notification Preferences
                    </h5>
                    <a href="{{ route('notifications.index') }}" class="btn btn-secondary btn-sm">
                        <i class="bi bi-arrow-left me-1"></i>Back to Notifications
                    </a>
                </div>
                <div class="card-body">
                    <form action="{{ route('notifications.preferences.update') }}" method="POST">
                        @csrf
                        
                        <div class="row">
                            <div class="col-12 mb-4">
                                <h6 class="fw-bold text-primary mb-3">Email Notifications</h6>
                                
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" id="email_enabled" 
                                           name="email_enabled" value="1" 
                                           {{ $preferences->email_enabled ? 'checked' : '' }}>
                                    <label class="form-check-label" for="email_enabled">
                                        <strong>Enable Email Notifications</strong>
                                        <br><small class="text-muted">Receive notifications via email</small>
                                    </label>
                                </div>
                                
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" id="email_opportunities" 
                                           name="email_opportunities" value="1" 
                                           {{ $preferences->email_opportunities ? 'checked' : '' }}>
                                    <label class="form-check-label" for="email_opportunities">
                                        <strong>New Opportunities</strong>
                                        <br><small class="text-muted">Get notified about new volunteer opportunities</small>
                                    </label>
                                </div>
                                
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" id="email_applications" 
                                           name="email_applications" value="1" 
                                           {{ $preferences->email_applications ? 'checked' : '' }}>
                                    <label class="form-check-label" for="email_applications">
                                        <strong>Application Updates</strong>
                                        <br><small class="text-muted">Get notified about application status changes</small>
                                    </label>
                                </div>
                                
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" id="email_messages" 
                                           name="email_messages" value="1" 
                                           {{ $preferences->email_messages ? 'checked' : '' }}>
                                    <label class="form-check-label" for="email_messages">
                                        <strong>New Messages</strong>
                                        <br><small class="text-muted">Get notified about new messages</small>
                                    </label>
                                </div>
                                
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" id="email_announcements" 
                                           name="email_announcements" value="1" 
                                           {{ $preferences->email_announcements ? 'checked' : '' }}>
                                    <label class="form-check-label" for="email_announcements">
                                        <strong>Announcements</strong>
                                        <br><small class="text-muted">Get notified about new announcements</small>
                                    </label>
                                </div>
                            </div>
                            
                            <div class="col-12 mb-4">
                                <h6 class="fw-bold text-primary mb-3">Browser Notifications</h6>
                                
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" id="browser_enabled" 
                                           name="browser_enabled" value="1" 
                                           {{ $preferences->browser_enabled ? 'checked' : '' }}>
                                    <label class="form-check-label" for="browser_enabled">
                                        <strong>Enable Browser Notifications</strong>
                                        <br><small class="text-muted">Show notifications in your browser</small>
                                    </label>
                                </div>
                            </div>
                            
                            <div class="col-12 mb-4">
                                <h6 class="fw-bold text-primary mb-3">Frequency Settings</h6>
                                
                                <div class="mb-3">
                                    <label for="digest_frequency" class="form-label">Email Digest Frequency</label>
                                    <select class="form-select" id="digest_frequency" name="digest_frequency">
                                        <option value="immediate" {{ $preferences->digest_frequency === 'immediate' ? 'selected' : '' }}>Immediate</option>
                                        <option value="daily" {{ $preferences->digest_frequency === 'daily' ? 'selected' : '' }}>Daily</option>
                                        <option value="weekly" {{ $preferences->digest_frequency === 'weekly' ? 'selected' : '' }}>Weekly</option>
                                        <option value="never" {{ $preferences->digest_frequency === 'never' ? 'selected' : '' }}>Never</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-between">
                            <button type="button" class="btn btn-outline-danger" onclick="resetPreferences()">
                                <i class="bi bi-arrow-clockwise me-1"></i>Reset to Defaults
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle me-1"></i>Save Preferences
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
function resetPreferences() {
    if (confirm('Are you sure you want to reset all notification preferences to default values?')) {
        fetch('{{ route("notifications.preferences.reset") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error resetting preferences');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error resetting preferences');
        });
    }
}
</script>
@endpush
@endsection
