@extends('layouts.auth')

@section('title', 'Login - MVMS')

@section('content')
<div class="auth-container d-flex align-items-center justify-content-center">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-sm-8 col-md-6 col-lg-5 col-xl-4">
                <div class="auth-card p-4 p-md-5">
                    <!-- Professional Header -->
                    <div class="text-center mb-4">
                        <!-- MVMS Logo -->
                        <div class="mb-4">
                            <h1 class="fw-bold mb-3 d-flex align-items-center justify-content-center"
                                style="font-family: 'Poppins', sans-serif; color: var(--primary-color); font-size: 2.2rem;">
                                <i class="bi bi-grid-3x3-gap me-3"></i>MVMS
                            </h1>
                            <h2 class="fw-semibold text-dark mb-2" style="font-size: 1.8rem;">Welcome Back</h2>
                            <p class="text-muted mb-0">Sign in to your account</p>
                        </div>
                    </div>

                    <!-- Alert Container -->
                    <div class="alert-container mb-4"></div>

                    <!-- Login Form -->
                    <form id="loginForm" method="POST" action="{{ route('login.submit') }}" class="needs-validation" novalidate>
                        @csrf

                        <!-- Email Field -->
                        <div class="mb-4">
                            <label for="email" class="form-label fw-semibold text-dark mb-3">
                                <i class="bi bi-envelope me-2 text-primary"></i>Email Address
                            </label>
                            <input type="email"
                                   class="form-control @error('email') is-invalid @enderror"
                                   id="email"
                                   name="email"
                                   value="{{ old('email') }}"
                                   placeholder="Enter your email address"
                                   style="padding: 16px 18px; font-size: 1rem; border-radius: 12px;"
                                   required>
                            @error('email')
                                <div class="invalid-feedback mt-2">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Password Field -->
                        <div class="mb-4">
                            <label for="password" class="form-label fw-semibold text-dark mb-3">
                                <i class="bi bi-lock me-2 text-primary"></i>Password
                            </label>
                            <div class="position-relative">
                                <input type="password"
                                       class="form-control @error('password') is-invalid @enderror"
                                       id="password"
                                       name="password"
                                       placeholder="Enter your password"
                                       style="padding: 16px 18px; font-size: 1rem; padding-right: 55px; border-radius: 12px;"
                                       required>
                                <button type="button"
                                        class="btn btn-link position-absolute end-0 top-50 translate-middle-y pe-3 text-muted"
                                        id="togglePassword"
                                        style="border: none; background: none; z-index: 10; transition: color 0.3s ease;">
                                    <i class="bi bi-eye" id="toggleIcon"></i>
                                </button>
                            </div>
                            @error('password')
                                <div class="invalid-feedback mt-2">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Remember Me -->
                        <div class="mb-5">
                            <div class="form-check d-flex align-items-center">
                                <input type="checkbox" class="form-check-input me-3" id="remember" name="remember"
                                       style="transform: scale(1.2); border-radius: 4px;">
                                <label class="form-check-label fw-medium text-dark" for="remember" style="font-size: 0.95rem;">
                                    Remember Me Next Time
                                </label>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="d-grid mb-5">
                            <button type="submit" class="btn btn-primary btn-lg" id="loginBtn"
                                    style="padding: 18px 24px; font-size: 1.1rem; font-weight: 600; border-radius: 12px; transition: all 0.3s ease;">
                                <span class="loading-spinner spinner-border spinner-border-sm me-2" role="status" style="display: none;"></span>
                                <span class="btn-text">
                                    <i class="bi bi-box-arrow-in-right me-2"></i>Sign In
                                </span>
                            </button>
                        </div>

                        <!-- Fallback for non-JavaScript users -->
                        <noscript>
                            <div class="alert alert-warning">
                                <i class="bi bi-exclamation-triangle me-2"></i>
                                JavaScript is required for the best experience. Please enable JavaScript and try again.
                            </div>
                        </noscript>

                        <!-- Professional Footer -->
                        <div class="mt-4">
                            <!-- Forgot Password -->
                            <div class="text-center mb-4">
                                <a href="{{ route('password.request') }}" class="text-decoration-none text-primary fw-medium">
                                    <i class="bi bi-question-circle me-1"></i>Forgot your password?
                                </a>
                            </div>

                            <!-- Divider -->
                            <hr class="my-4" style="opacity: 0.3;">

                            <!-- Register Link -->
                            <div class="text-center">
                                <p class="text-muted mb-3">Don't have an account?</p>
                                <a href="{{ route('register') }}" class="btn btn-outline-primary">
                                    <i class="bi bi-person-plus me-2"></i>Sign Up
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

    // Form validation and submission
    $('#loginForm').on('submit', function(e) {
        e.preventDefault();

        const form = $(this);
        const submitBtn = $('#loginBtn');
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
                    showAlert('Login successful! Redirecting...', 'success');
                    setTimeout(() => {
                        window.location.href = response.redirect || '{{ route("dashboard") }}';
                    }, 1000);
                } else {
                    showAlert(response.message || 'Login failed. Please try again.', 'danger');
                }
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    // Validation errors
                    const errors = xhr.responseJSON.errors;
                    Object.keys(errors).forEach(function(field) {
                        showFieldError(field, errors[field][0]);
                    });
                } else if (xhr.status === 401) {
                    showAlert('Invalid email or password. Please try again.', 'danger');
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
    $('#email, #password').on('input', function() {
        $(this).removeClass('is-invalid');
        $(this).siblings('.invalid-feedback').remove();
    });

    // Auto-focus on email field
    $('#email').focus();



    // Helper functions
    function clearFieldErrors() {
        $('.form-control').removeClass('is-invalid');
        $('.invalid-feedback').remove();
    }

    function setLoadingState(button, loading) {
        if (loading) {
            button.prop('disabled', true);
            button.find('.loading-spinner').show();
            button.find('.btn-text').html('<i class="bi bi-hourglass-split me-2"></i>Signing In...');
        } else {
            button.prop('disabled', false);
            button.find('.loading-spinner').hide();
            button.find('.btn-text').html('<i class="bi bi-box-arrow-in-right me-2"></i>Sign In to Dashboard');
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
