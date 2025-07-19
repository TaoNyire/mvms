@extends('layouts.admin')

@section('title', 'Edit Admin Profile - MVMS')

@section('page-title', 'Edit Admin Profile')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="bi bi-person-gear me-2"></i>Edit Profile Information
                        </h5>
                        <a href="{{ route('admin.profile.show') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-arrow-left me-1"></i>Back to Profile
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.profile.update') }}" method="POST">
                        @csrf
                        @method('PUT')

                        <!-- Basic Information -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h6 class="text-muted mb-3">
                                    <i class="bi bi-person me-1"></i>Basic Information
                                </h6>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label">Full Name <span class="text-danger">*</span></label>
                                <input type="text" 
                                       class="form-control @error('name') is-invalid @enderror" 
                                       id="name" 
                                       name="name" 
                                       value="{{ old('name', $admin->name) }}" 
                                       required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                                <input type="email" 
                                       class="form-control @error('email') is-invalid @enderror" 
                                       id="email" 
                                       name="email" 
                                       value="{{ old('email', $admin->email) }}" 
                                       required>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="phone" class="form-label">Phone Number</label>
                                <input type="text"
                                       class="form-control @error('phone') is-invalid @enderror"
                                       id="phone"
                                       name="phone"
                                       value="{{ old('phone', session('admin_profile_data.phone', '')) }}"
                                       placeholder="+265 123 456 789">
                                @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12 mb-3">
                                <label for="bio" class="form-label">Bio</label>
                                <textarea class="form-control @error('bio') is-invalid @enderror"
                                          id="bio"
                                          name="bio"
                                          rows="4"
                                          placeholder="Brief description about yourself and your role...">{{ old('bio', session('admin_profile_data.bio', '')) }}</textarea>
                                @error('bio')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Maximum 1000 characters</div>
                            </div>
                        </div>

                        <!-- Notification Preferences -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h6 class="text-muted mb-3">
                                    <i class="bi bi-bell me-1"></i>Notification Preferences
                                </h6>
                            </div>

                            <div class="col-12">
                                <div class="card bg-light border-0">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <div class="form-check">
                                                    <input class="form-check-input"
                                                           type="checkbox"
                                                           id="email_notifications"
                                                           name="notification_preferences[email_notifications]"
                                                           value="1"
                                                           {{ old('notification_preferences.email_notifications', session('admin_profile_data.notification_preferences.email_notifications', true)) ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="email_notifications">
                                                        Email Notifications
                                                    </label>
                                                    <div class="form-text">Receive system notifications via email</div>
                                                </div>
                                            </div>

                                            <div class="col-md-6 mb-3">
                                                <div class="form-check">
                                                    <input class="form-check-input"
                                                           type="checkbox"
                                                           id="security_alerts"
                                                           name="notification_preferences[security_alerts]"
                                                           value="1"
                                                           {{ old('notification_preferences.security_alerts', session('admin_profile_data.notification_preferences.security_alerts', true)) ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="security_alerts">
                                                        Security Alerts
                                                    </label>
                                                    <div class="form-text">Receive security-related notifications</div>
                                                </div>
                                            </div>

                                            <div class="col-md-6 mb-3">
                                                <div class="form-check">
                                                    <input class="form-check-input"
                                                           type="checkbox"
                                                           id="user_activity"
                                                           name="notification_preferences[user_activity]"
                                                           value="1"
                                                           {{ old('notification_preferences.user_activity', session('admin_profile_data.notification_preferences.user_activity', true)) ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="user_activity">
                                                        User Activity Alerts
                                                    </label>
                                                    <div class="form-text">Notifications about user registrations and activities</div>
                                                </div>
                                            </div>

                                            <div class="col-md-6 mb-3">
                                                <div class="form-check">
                                                    <input class="form-check-input"
                                                           type="checkbox"
                                                           id="system_updates"
                                                           name="notification_preferences[system_updates]"
                                                           value="1"
                                                           {{ old('notification_preferences.system_updates', session('admin_profile_data.notification_preferences.system_updates', true)) ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="system_updates">
                                                        System Updates
                                                    </label>
                                                    <div class="form-text">Notifications about system maintenance and updates</div>
                                                </div>
                                            </div>

                                            <div class="col-md-6 mb-3">
                                                <div class="form-check">
                                                    <input class="form-check-input"
                                                           type="checkbox"
                                                           id="organization_approvals"
                                                           name="notification_preferences[organization_approvals]"
                                                           value="1"
                                                           {{ old('notification_preferences.organization_approvals', session('admin_profile_data.notification_preferences.organization_approvals', true)) ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="organization_approvals">
                                                        Organization Approvals
                                                    </label>
                                                    <div class="form-text">Notifications about pending organization approvals</div>
                                                </div>
                                            </div>

                                            <div class="col-md-6 mb-3">
                                                <div class="form-check">
                                                    <input class="form-check-input"
                                                           type="checkbox"
                                                           id="reports_ready"
                                                           name="notification_preferences[reports_ready]"
                                                           value="1"
                                                           {{ old('notification_preferences.reports_ready', session('admin_profile_data.notification_preferences.reports_ready', false)) ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="reports_ready">
                                                        Report Generation
                                                    </label>
                                                    <div class="form-text">Notifications when reports are ready</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="row">
                            <div class="col-12">
                                <div class="d-flex justify-content-between">
                                    <a href="{{ route('admin.profile.show') }}" class="btn btn-outline-secondary">
                                        <i class="bi bi-x-circle me-1"></i>Cancel
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-check-circle me-1"></i>Update Profile
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Additional Actions -->
            <div class="row mt-4">
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body text-center">
                            <i class="bi bi-key text-warning mb-2" style="font-size: 2rem;"></i>
                            <h6 class="mb-2">Change Password</h6>
                            <p class="text-muted small mb-3">Update your account password for security</p>
                            <a href="{{ route('admin.profile.change-password') }}" class="btn btn-outline-warning btn-sm">
                                <i class="bi bi-key me-1"></i>Change Password
                            </a>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body text-center">
                            <i class="bi bi-shield-check text-info mb-2" style="font-size: 2rem;"></i>
                            <h6 class="mb-2">Security Settings</h6>
                            <p class="text-muted small mb-3">Manage your account security preferences</p>
                            <a href="{{ route('admin.profile.security') }}" class="btn btn-outline-info btn-sm">
                                <i class="bi bi-shield-check me-1"></i>Security Settings
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
