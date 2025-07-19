@extends('layouts.admin')

@section('title', 'Change Password - Admin Profile')

@section('page-title', 'Change Password')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="bi bi-key me-2"></i>Change Password
                        </h5>
                        <a href="{{ route('admin.profile.show') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-arrow-left me-1"></i>Back to Profile
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Security Notice -->
                    <div class="alert alert-info border-0 mb-4">
                        <div class="d-flex">
                            <i class="bi bi-info-circle me-2 mt-1"></i>
                            <div>
                                <strong>Password Security Requirements:</strong>
                                <ul class="mb-0 mt-2">
                                    <li>Minimum 8 characters</li>
                                    <li>At least one uppercase letter</li>
                                    <li>At least one lowercase letter</li>
                                    <li>At least one number</li>
                                    <li>At least one special character</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <form action="{{ route('admin.profile.change-password') }}" method="POST" id="changePasswordForm">
                        @csrf

                        <!-- Current Password -->
                        <div class="mb-4">
                            <label for="current_password" class="form-label">Current Password <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="password" 
                                       class="form-control @error('current_password') is-invalid @enderror" 
                                       id="current_password" 
                                       name="current_password" 
                                       required>
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('current_password')">
                                    <i class="bi bi-eye" id="current_password_icon"></i>
                                </button>
                                @error('current_password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- New Password -->
                        <div class="mb-4">
                            <label for="password" class="form-label">New Password <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="password" 
                                       class="form-control @error('password') is-invalid @enderror" 
                                       id="password" 
                                       name="password" 
                                       required>
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('password')">
                                    <i class="bi bi-eye" id="password_icon"></i>
                                </button>
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <!-- Password Strength Indicator -->
                            <div class="mt-2">
                                <div class="progress" style="height: 5px;">
                                    <div class="progress-bar" id="passwordStrength" role="progressbar" style="width: 0%"></div>
                                </div>
                                <small class="text-muted" id="passwordStrengthText">Password strength will appear here</small>
                            </div>
                        </div>

                        <!-- Confirm New Password -->
                        <div class="mb-4">
                            <label for="password_confirmation" class="form-label">Confirm New Password <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="password" 
                                       class="form-control @error('password_confirmation') is-invalid @enderror" 
                                       id="password_confirmation" 
                                       name="password_confirmation" 
                                       required>
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('password_confirmation')">
                                    <i class="bi bi-eye" id="password_confirmation_icon"></i>
                                </button>
                                @error('password_confirmation')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mt-2">
                                <small class="text-muted" id="passwordMatchText"></small>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('admin.profile.show') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-x-circle me-1"></i>Cancel
                            </a>
                            <button type="submit" class="btn btn-primary" id="submitBtn" disabled>
                                <i class="bi bi-check-circle me-1"></i>Change Password
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Security Tips -->
            <div class="card border-0 shadow-sm mt-4">
                <div class="card-header bg-white border-bottom">
                    <h6 class="mb-0">
                        <i class="bi bi-shield-check me-2"></i>Password Security Tips
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="d-flex mb-3">
                                <i class="bi bi-check-circle text-success me-2 mt-1"></i>
                                <div>
                                    <strong>Use a unique password</strong>
                                    <p class="text-muted small mb-0">Don't reuse passwords from other accounts</p>
                                </div>
                            </div>
                            <div class="d-flex mb-3">
                                <i class="bi bi-check-circle text-success me-2 mt-1"></i>
                                <div>
                                    <strong>Make it complex</strong>
                                    <p class="text-muted small mb-0">Mix uppercase, lowercase, numbers, and symbols</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex mb-3">
                                <i class="bi bi-check-circle text-success me-2 mt-1"></i>
                                <div>
                                    <strong>Change regularly</strong>
                                    <p class="text-muted small mb-0">Update your password every 3-6 months</p>
                                </div>
                            </div>
                            <div class="d-flex mb-3">
                                <i class="bi bi-check-circle text-success me-2 mt-1"></i>
                                <div>
                                    <strong>Keep it private</strong>
                                    <p class="text-muted small mb-0">Never share your password with anyone</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function togglePassword(fieldId) {
    const field = document.getElementById(fieldId);
    const icon = document.getElementById(fieldId + '_icon');
    
    if (field.type === 'password') {
        field.type = 'text';
        icon.className = 'bi bi-eye-slash';
    } else {
        field.type = 'password';
        icon.className = 'bi bi-eye';
    }
}

// Password strength checker
document.getElementById('password').addEventListener('input', function() {
    const password = this.value;
    const strengthBar = document.getElementById('passwordStrength');
    const strengthText = document.getElementById('passwordStrengthText');
    const submitBtn = document.getElementById('submitBtn');
    
    let strength = 0;
    let feedback = [];
    
    // Check length
    if (password.length >= 8) strength += 20;
    else feedback.push('At least 8 characters');
    
    // Check uppercase
    if (/[A-Z]/.test(password)) strength += 20;
    else feedback.push('Uppercase letter');
    
    // Check lowercase
    if (/[a-z]/.test(password)) strength += 20;
    else feedback.push('Lowercase letter');
    
    // Check numbers
    if (/\d/.test(password)) strength += 20;
    else feedback.push('Number');
    
    // Check special characters
    if (/[^A-Za-z0-9]/.test(password)) strength += 20;
    else feedback.push('Special character');
    
    // Update strength bar
    strengthBar.style.width = strength + '%';
    
    if (strength < 40) {
        strengthBar.className = 'progress-bar bg-danger';
        strengthText.textContent = 'Weak - Missing: ' + feedback.join(', ');
        strengthText.className = 'text-danger';
    } else if (strength < 80) {
        strengthBar.className = 'progress-bar bg-warning';
        strengthText.textContent = 'Medium - Missing: ' + feedback.join(', ');
        strengthText.className = 'text-warning';
    } else {
        strengthBar.className = 'progress-bar bg-success';
        strengthText.textContent = 'Strong password';
        strengthText.className = 'text-success';
    }
    
    checkFormValidity();
});

// Password confirmation checker
document.getElementById('password_confirmation').addEventListener('input', function() {
    const password = document.getElementById('password').value;
    const confirmation = this.value;
    const matchText = document.getElementById('passwordMatchText');
    
    if (confirmation === '') {
        matchText.textContent = '';
        matchText.className = 'text-muted';
    } else if (password === confirmation) {
        matchText.textContent = 'Passwords match';
        matchText.className = 'text-success';
    } else {
        matchText.textContent = 'Passwords do not match';
        matchText.className = 'text-danger';
    }
    
    checkFormValidity();
});

function checkFormValidity() {
    const currentPassword = document.getElementById('current_password').value;
    const password = document.getElementById('password').value;
    const confirmation = document.getElementById('password_confirmation').value;
    const submitBtn = document.getElementById('submitBtn');
    
    const isValid = currentPassword.length > 0 && 
                   password.length >= 8 && 
                   password === confirmation &&
                   /[A-Z]/.test(password) &&
                   /[a-z]/.test(password) &&
                   /\d/.test(password) &&
                   /[^A-Za-z0-9]/.test(password);
    
    submitBtn.disabled = !isValid;
}

// Check validity on current password input
document.getElementById('current_password').addEventListener('input', checkFormValidity);
</script>
@endsection
