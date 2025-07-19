@extends('layouts.volunteer')

@section('title', 'Settings - Volunteer Dashboard')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-md-8">
            <h2 class="mb-2">
                <i class="bi bi-gear me-2"></i>Settings
            </h2>
            <p>Manage your account settings and preferences.</p>
        </div>
        <div class="col-md-4 text-md-end">
            <a href="{{ route('volunteer.dashboard') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i>Back to Dashboard
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Settings Navigation -->
        <div class="col-lg-3 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h6 class="card-title">Settings</h6>
                    <div class="list-group list-group-flush">
                        <a href="#account" class="list-group-item list-group-item-action active" data-bs-toggle="pill">
                            <i class="bi bi-person me-2"></i>Account
                        </a>
                        <a href="#notifications" class="list-group-item list-group-item-action" data-bs-toggle="pill">
                            <i class="bi bi-bell me-2"></i>Notifications
                        </a>
                        <a href="#privacy" class="list-group-item list-group-item-action" data-bs-toggle="pill">
                            <i class="bi bi-shield-lock me-2"></i>Privacy
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Settings Content -->
        <div class="col-lg-9">
            <form action="{{ route('volunteer.settings.update') }}" method="POST">
                @csrf
                
                <div class="tab-content">
                    <!-- Account Settings -->
                    <div class="tab-pane fade show active" id="account">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white">
                                <h5 class="mb-0">Account Information</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="name" class="form-label">Full Name</label>
                                        <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                               id="name" name="name" value="{{ old('name', $user->name) }}" required>
                                        @error('name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="email" class="form-label">Email Address</label>
                                        <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                               id="email" name="email" value="{{ old('email', $user->email) }}" required>
                                        @error('email')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                
                                <hr>
                                
                                <h6>Change Password</h6>
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label for="current_password" class="form-label">Current Password</label>
                                        <input type="password" class="form-control @error('current_password') is-invalid @enderror" 
                                               id="current_password" name="current_password">
                                        @error('current_password')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="password" class="form-label">New Password</label>
                                        <input type="password" class="form-control @error('password') is-invalid @enderror" 
                                               id="password" name="password">
                                        @error('password')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="password_confirmation" class="form-label">Confirm Password</label>
                                        <input type="password" class="form-control" 
                                               id="password_confirmation" name="password_confirmation">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Notification Settings -->
                    <div class="tab-pane fade" id="notifications">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white">
                                <h5 class="mb-0">Notification Preferences</h5>
                            </div>
                            <div class="card-body">
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" id="email_notifications" 
                                           name="notification_preferences[email]" value="1" checked>
                                    <label class="form-check-label" for="email_notifications">
                                        Email Notifications
                                    </label>
                                    <small class="form-text text-muted d-block">Receive notifications via email</small>
                                </div>
                                
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" id="application_updates" 
                                           name="notification_preferences[applications]" value="1" checked>
                                    <label class="form-check-label" for="application_updates">
                                        Application Updates
                                    </label>
                                    <small class="form-text text-muted d-block">Get notified about application status changes</small>
                                </div>
                                
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" id="opportunity_matches" 
                                           name="notification_preferences[opportunities]" value="1" checked>
                                    <label class="form-check-label" for="opportunity_matches">
                                        New Opportunity Matches
                                    </label>
                                    <small class="form-text text-muted d-block">Get notified about opportunities matching your skills</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Privacy Settings -->
                    <div class="tab-pane fade" id="privacy">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white">
                                <h5 class="mb-0">Privacy Settings</h5>
                            </div>
                            <div class="card-body">
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" id="profile_visibility" 
                                           name="privacy_settings[profile_public]" value="1" checked>
                                    <label class="form-check-label" for="profile_visibility">
                                        Public Profile
                                    </label>
                                    <small class="form-text text-muted d-block">Allow organizations to view your profile</small>
                                </div>
                                
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" id="contact_visibility" 
                                           name="privacy_settings[contact_visible]" value="1" checked>
                                    <label class="form-check-label" for="contact_visibility">
                                        Show Contact Information
                                    </label>
                                    <small class="form-text text-muted d-block">Allow organizations to see your contact details</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Save Button -->
                <div class="row mt-4">
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg me-1"></i>Save Settings
                        </button>
                        <a href="{{ route('volunteer.dashboard') }}" class="btn btn-outline-secondary ms-2">
                            Cancel
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Handle tab switching
    $('.list-group-item-action').on('click', function(e) {
        e.preventDefault();
        
        // Remove active class from all tabs
        $('.list-group-item-action').removeClass('active');
        $('.tab-pane').removeClass('show active');
        
        // Add active class to clicked tab
        $(this).addClass('active');
        
        // Show corresponding content
        const target = $(this).attr('href');
        $(target).addClass('show active');
    });
});
</script>
@endpush
