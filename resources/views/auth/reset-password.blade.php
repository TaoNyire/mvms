@extends('layouts.auth')

@section('title', 'Reset Password - MVMS')

@section('content')
<div class="container-fluid vh-100">
    <div class="row h-100">
        <!-- Left Side - Branding -->
        <div class="col-lg-6 d-none d-lg-flex align-items-center justify-content-center bg-primary text-white position-relative overflow-hidden">
            <div class="position-absolute top-0 start-0 w-100 h-100 opacity-10">
                <div class="bg-pattern"></div>
            </div>
            <div class="text-center z-index-2 position-relative">
                <div class="mb-4">
                    <i class="bi bi-grid-3x3-gap display-1 mb-3"></i>
                    <h1 class="display-4 fw-bold mb-3">MVMS</h1>
                    <p class="lead mb-4">Malawi Volunteer Management System</p>
                </div>
                <div class="row text-center">
                    <div class="col-4">
                        <i class="bi bi-people-fill display-6 mb-2"></i>
                        <p class="small">Connect Volunteers</p>
                    </div>
                    <div class="col-4">
                        <i class="bi bi-briefcase-fill display-6 mb-2"></i>
                        <p class="small">Manage Opportunities</p>
                    </div>
                    <div class="col-4">
                        <i class="bi bi-graph-up display-6 mb-2"></i>
                        <p class="small">Track Impact</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Side - Reset Password Form -->
        <div class="col-lg-6 d-flex align-items-center justify-content-center bg-light">
            <div class="w-100" style="max-width: 400px;">
                <div class="text-center mb-5">
                    <!-- Mobile Logo -->
                    <div class="d-lg-none mb-4">
                        <i class="bi bi-grid-3x3-gap text-primary" style="font-size: 3rem;"></i>
                        <h2 class="text-primary fw-bold">MVMS</h2>
                    </div>
                    
                    <h3 class="fw-bold text-dark mb-2">Reset Password</h3>
                    <p class="text-muted">Enter your new password below.</p>
                </div>

                <!-- Error Messages -->
                @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <!-- Reset Password Form -->
                <form method="POST" action="{{ route('password.update') }}" id="resetPasswordForm">
                    @csrf
                    <input type="hidden" name="token" value="{{ $token }}">
                    
                    <!-- Email Field -->
                    <div class="mb-3">
                        <label for="email" class="form-label fw-semibold text-dark">Email Address</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0">
                                <i class="bi bi-envelope text-muted"></i>
                            </span>
                            <input type="email" class="form-control border-start-0 @error('email') is-invalid @enderror" 
                                   id="email" name="email" value="{{ old('email') }}" required
                                   placeholder="Enter your email address"
                                   style="padding: 18px 16px; font-size: 1rem; border-radius: 0 12px 12px 0;">
                        </div>
                    </div>

                    <!-- Password Field -->
                    <div class="mb-3">
                        <label for="password" class="form-label fw-semibold text-dark">New Password</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0">
                                <i class="bi bi-lock text-muted"></i>
                            </span>
                            <input type="password" class="form-control border-start-0 border-end-0 @error('password') is-invalid @enderror" 
                                   id="password" name="password" required
                                   placeholder="Enter new password"
                                   style="padding: 18px 16px; font-size: 1rem;">
                            <button type="button" class="btn btn-outline-secondary border-start-0" 
                                    onclick="togglePassword('password')" style="border-radius: 0 12px 12px 0;">
                                <i class="bi bi-eye" id="password-toggle"></i>
                            </button>
                        </div>
                        <div class="form-text">Minimum 8 characters</div>
                    </div>

                    <!-- Confirm Password Field -->
                    <div class="mb-4">
                        <label for="password_confirmation" class="form-label fw-semibold text-dark">Confirm Password</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0">
                                <i class="bi bi-lock-fill text-muted"></i>
                            </span>
                            <input type="password" class="form-control border-start-0 border-end-0" 
                                   id="password_confirmation" name="password_confirmation" required
                                   placeholder="Confirm new password"
                                   style="padding: 18px 16px; font-size: 1rem;">
                            <button type="button" class="btn btn-outline-secondary border-start-0" 
                                    onclick="togglePassword('password_confirmation')" style="border-radius: 0 12px 12px 0;">
                                <i class="bi bi-eye" id="password_confirmation-toggle"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="d-grid mb-4">
                        <button type="submit" class="btn btn-primary btn-lg" id="resetBtn"
                                style="padding: 18px 24px; font-size: 1.1rem; font-weight: 600; border-radius: 12px;">
                            <span class="loading-spinner spinner-border spinner-border-sm me-2" role="status" style="display: none;"></span>
                            <span class="btn-text">
                                <i class="bi bi-check-circle me-2"></i>Reset Password
                            </span>
                        </button>
                    </div>

                    <!-- Back to Login -->
                    <div class="text-center">
                        <a href="{{ route('login') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left me-2"></i>Back to Sign In
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
.bg-pattern {
    background-image: 
        radial-gradient(circle at 25% 25%, rgba(255,255,255,0.1) 2px, transparent 2px),
        radial-gradient(circle at 75% 75%, rgba(255,255,255,0.1) 2px, transparent 2px);
    background-size: 50px 50px;
}

.input-group-text {
    border-radius: 12px 0 0 12px;
    border-color: #dee2e6;
}

.form-control:focus {
    border-color: #0d6efd;
    box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
}

.btn-primary {
    background: linear-gradient(135deg, #0d6efd 0%, #0b5ed7 100%);
    border: none;
    box-shadow: 0 4px 15px rgba(13, 110, 253, 0.3);
    transition: all 0.3s ease;
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(13, 110, 253, 0.4);
}

.alert {
    border-radius: 12px;
    border: none;
}

@media (max-width: 991.98px) {
    .container-fluid {
        padding: 20px;
    }
}
</style>

<script>
function togglePassword(fieldId) {
    const field = document.getElementById(fieldId);
    const toggle = document.getElementById(fieldId + '-toggle');
    
    if (field.type === 'password') {
        field.type = 'text';
        toggle.className = 'bi bi-eye-slash';
    } else {
        field.type = 'password';
        toggle.className = 'bi bi-eye';
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('resetPasswordForm');
    const submitBtn = document.getElementById('resetBtn');
    const spinner = submitBtn.querySelector('.loading-spinner');
    const btnText = submitBtn.querySelector('.btn-text');

    form.addEventListener('submit', function() {
        submitBtn.disabled = true;
        spinner.style.display = 'inline-block';
        btnText.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>Resetting...';
        
        // Re-enable after 10 seconds (fallback)
        setTimeout(function() {
            submitBtn.disabled = false;
            spinner.style.display = 'none';
            btnText.innerHTML = '<i class="bi bi-check-circle me-2"></i>Reset Password';
        }, 10000);
    });

    // Password confirmation validation
    const password = document.getElementById('password');
    const confirmPassword = document.getElementById('password_confirmation');
    
    function validatePassword() {
        if (password.value !== confirmPassword.value) {
            confirmPassword.setCustomValidity("Passwords don't match");
        } else {
            confirmPassword.setCustomValidity('');
        }
    }
    
    password.addEventListener('change', validatePassword);
    confirmPassword.addEventListener('keyup', validatePassword);
});
</script>
@endsection
