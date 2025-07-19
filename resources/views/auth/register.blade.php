@extends('layouts.auth')

@section('title', 'Register - MVMS')

@section('content')
<div class="auth-container d-flex align-items-center justify-content-center py-4">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-sm-10 col-md-8 col-lg-6 col-xl-5">
                <div class="auth-card p-4 p-md-5">
                    <!-- Professional Header -->
                    <div class="text-center mb-4">
                        <!-- MVMS Logo -->
                        <div class="mb-4">
                            <h1 class="fw-bold mb-3 d-flex align-items-center justify-content-center"
                                style="font-family: 'Poppins', sans-serif; color: var(--primary-color); font-size: 2.2rem;">
                                <i class="bi bi-grid-3x3-gap me-3"></i>MVMS
                            </h1>
                            <h2 class="fw-semibold text-dark mb-2" style="font-size: 1.8rem;">Create Your Account</h2>
                            <p class="text-muted mb-0">Join the volunteer community</p>
                        </div>
                    </div>

                    <!-- Alert Container -->
                    <div class="alert-container mb-3"></div>

                    <!-- Registration Form -->
                    <form id="registerForm" method="POST" action="{{ route('register.submit') }}">
                        @csrf
                        
                        <!-- Name Field -->
                        <div class="mb-3">
                            <label for="name" class="form-label fw-semibold">
                                <i class="bi bi-person me-1"></i>Full Name
                            </label>
                            <input type="text" 
                                   class="form-control @error('name') is-invalid @enderror" 
                                   id="name" 
                                   name="name" 
                                   value="{{ old('name') }}" 
                                   placeholder="Enter your full name"
                                   required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Email Field -->
                        <div class="mb-3">
                            <label for="email" class="form-label fw-semibold">
                                <i class="bi bi-envelope me-1"></i>Email Address
                            </label>
                            <input type="email" 
                                   class="form-control @error('email') is-invalid @enderror" 
                                   id="email" 
                                   name="email" 
                                   value="{{ old('email') }}" 
                                   placeholder="Enter your email address"
                                   required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Role Selection -->
                        <div class="mb-3">
                            <label for="role" class="form-label fw-semibold">
                                <i class="bi bi-shield-check me-1"></i>Account Type
                            </label>
                            <select class="form-select @error('role') is-invalid @enderror" 
                                    id="role" 
                                    name="role" 
                                    required>
                                <option value="">Register As</option>
                                <option value="volunteer" {{ old('role') == 'volunteer' ? 'selected' : '' }}>
                                    Volunteer
                                </option>
                                <option value="organization" {{ old('role') == 'organization' ? 'selected' : '' }}>
                                    Organization
                                </option>
                            </select>
                            @error('role')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Password Field -->
                        <div class="mb-3">
                            <label for="password" class="form-label fw-semibold">
                                <i class="bi bi-lock me-1"></i>Password
                            </label>
                            <div class="position-relative">
                                <input type="password" 
                                       class="form-control @error('password') is-invalid @enderror" 
                                       id="password" 
                                       name="password" 
                                       placeholder="Create a strong password"
                                       required>
                                <button type="button" 
                                        class="btn btn-link position-absolute end-0 top-50 translate-middle-y pe-3" 
                                        id="togglePassword"
                                        style="border: none; background: none; z-index: 10;">
                                    <i class="bi bi-eye" id="toggleIcon"></i>
                                </button>
                            </div>
                            <div class="form-text">
                                <small>Password must be at least 6 characters long</small>
                            </div>
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Confirm Password Field -->
                        <div class="mb-3">
                            <label for="password_confirmation" class="form-label fw-semibold">
                                <i class="bi bi-lock-fill me-1"></i>Confirm Password
                            </label>
                            <div class="position-relative">
                                <input type="password" 
                                       class="form-control @error('password_confirmation') is-invalid @enderror" 
                                       id="password_confirmation" 
                                       name="password_confirmation" 
                                       placeholder="Confirm your password"
                                       required>
                                <button type="button" 
                                        class="btn btn-link position-absolute end-0 top-50 translate-middle-y pe-3" 
                                        id="togglePasswordConfirm"
                                        style="border: none; background: none; z-index: 10;">
                                    <i class="bi bi-eye" id="toggleIconConfirm"></i>
                                </button>
                            </div>
                            @error('password_confirmation')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Terms and Conditions -->
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="terms" name="terms" required>
                            <label class="form-check-label" for="terms">
                                I agree to the <a href="#" class="text-decoration-none">Terms of Service</a> 
                                and <a href="#" class="text-decoration-none">Privacy Policy</a>
                            </label>
                        </div>

                        <!-- Submit Button -->
                        <div class="d-grid mb-3">
                            <button type="submit" class="btn btn-primary btn-lg" id="registerBtn">
                                <!-- <span class="loading-spinner spinner-border spinner-border-sm me-2" role="status"></span> -->
                                <span class="btn-text">
                                    <i class="bi bi-person-plus me-1"></i>Create Account
                                </span>
                            </button>
                        </div>

                        <!-- Professional Footer -->
                        <div class="mt-4">
                            <!-- Divider -->
                            <hr class="my-4" style="opacity: 0.3;">

                            <!-- Login Link -->
                            <div class="text-center">
                                <p class="text-muted mb-3">Already have an account?</p>
                                <a href="{{ route('login') }}" class="btn btn-outline-primary">
                                    <i class="bi bi-box-arrow-in-right me-2"></i>Sign In
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Password toggle functionality
    $('#togglePassword').click(function() {
        const passwordField = $('#password');
        const toggleIcon = $('#toggleIcon');
        
        if (passwordField.attr('type') === 'password') {
            passwordField.attr('type', 'text');
            toggleIcon.removeClass('bi-eye').addClass('bi-eye-slash');
        } else {
            passwordField.attr('type', 'password');
            toggleIcon.removeClass('bi-eye-slash').addClass('bi-eye');
        }
    });

    $('#togglePasswordConfirm').click(function() {
        const passwordField = $('#password_confirmation');
        const toggleIcon = $('#toggleIconConfirm');
        
        if (passwordField.attr('type') === 'password') {
            passwordField.attr('type', 'text');
            toggleIcon.removeClass('bi-eye').addClass('bi-eye-slash');
        } else {
            passwordField.attr('type', 'password');
            toggleIcon.removeClass('bi-eye-slash').addClass('bi-eye');
        }
    });

    // Password confirmation validation
    $('#password_confirmation').on('input', function() {
        const password = $('#password').val();
        const confirmPassword = $(this).val();
        
        if (confirmPassword && password !== confirmPassword) {
            $(this).addClass('is-invalid');
            $(this).siblings('.invalid-feedback').remove();
            $(this).after('<div class="invalid-feedback">Passwords do not match</div>');
        } else {
            $(this).removeClass('is-invalid');
            $(this).siblings('.invalid-feedback').remove();
        }
    });

    // Form validation and submission
    $('#registerForm').on('submit', function(e) {
        e.preventDefault();
        
        const form = $(this);
        const submitBtn = $('#registerBtn');
        const formData = form.serialize();
        
        // Clear previous errors
        clearFieldErrors();
        
        // Set loading state
        setLoadingState(submitBtn, true);
        
        // Submit form via AJAX
        $.ajax({
            url: form.attr('action'),
            method: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    showAlert('Registration successful! Redirecting to dashboard...', 'success');
                    setTimeout(() => {
                        window.location.href = response.redirect || '{{ route("dashboard") }}';
                    }, 1500);
                } else {
                    showAlert(response.message || 'Registration failed. Please try again.', 'danger');
                }
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    // Validation errors
                    const errors = xhr.responseJSON.errors;
                    Object.keys(errors).forEach(function(field) {
                        showFieldError(field, errors[field][0]);
                    });
                } else {
                    showAlert('An error occurred. Please try again.', 'danger');
                }
            },
            complete: function() {
                setLoadingState(submitBtn, false);
            }
        });
    });

    // Real-time validation
    $('#name, #email, #password, #password_confirmation, #role').on('input change', function() {
        $(this).removeClass('is-invalid');
        $(this).siblings('.invalid-feedback').remove();
    });

    // Auto-focus on name field
    $('#name').focus();

    // Helper functions
    function clearFieldErrors() {
        $('.form-control').removeClass('is-invalid');
        $('.invalid-feedback').remove();
    }

    function setLoadingState(button, loading) {
        if (loading) {
            button.prop('disabled', true);
            button.find('.loading-spinner').show();
            button.find('.btn-text').html('<i class="bi bi-hourglass-split me-2"></i>Creating Account...');
        } else {
            button.prop('disabled', false);
            button.find('.loading-spinner').hide();
            button.find('.btn-text').html('<i class="bi bi-person-plus me-1"></i>Create Account');
        }
    }

    function showFieldError(field, message) {
        const fieldElement = $('#' + field);
        fieldElement.addClass('is-invalid');

        // Remove existing error message
        fieldElement.siblings('.invalid-feedback').remove();

        // Add new error message
        fieldElement.after('<div class="invalid-feedback mt-2">' + message + '</div>');
    }
});
</script>
@endpush
