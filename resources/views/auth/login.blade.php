@extends('layouts.app')

@section('title', 'Login - MVMS')

@section('content')
<div class="auth-container d-flex align-items-center justify-content-center">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-sm-8 col-md-6 col-lg-5 col-xl-4">
                <div class="auth-card p-4 p-md-5">
                    <!-- Logo and Title -->
                    <div class="text-center mb-4">
                        <div class="mb-3">
                            <i class="bi bi-people-fill text-primary" style="font-size: 3rem;"></i>
                        </div>
                        <h2 class="fw-bold text-dark mb-2">Welcome Back</h2>
                        <p class="text-muted">Sign in to your MVMS account</p>
                    </div>

                    <!-- Alert Container -->
                    <div class="alert-container mb-3"></div>

                    <!-- Login Form -->
                    <form id="loginForm" method="POST" action="{{ route('login.submit') }}">
                        @csrf
                        
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
                                   placeholder="Enter your email"
                                   required>
                            @error('email')
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
                                       placeholder="Enter your password"
                                       required>
                                <button type="button" 
                                        class="btn btn-link position-absolute end-0 top-50 translate-middle-y pe-3" 
                                        id="togglePassword"
                                        style="border: none; background: none; z-index: 10;">
                                    <i class="bi bi-eye" id="toggleIcon"></i>
                                </button>
                            </div>
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Remember Me -->
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="remember" name="remember">
                            <label class="form-check-label" for="remember">
                                Remember me
                            </label>
                        </div>

                        <!-- Submit Button -->
                        <div class="d-grid mb-3">
                            <button type="submit" class="btn btn-primary btn-lg" id="loginBtn">
                                <span class="loading-spinner spinner-border spinner-border-sm me-2" role="status"></span>
                                <span class="btn-text">
                                    <i class="bi bi-box-arrow-in-right me-1"></i>Sign In
                                </span>
                            </button>
                        </div>

                        <!-- Forgot Password Link -->
                        <div class="text-center mb-3">
                            <a href="#" class="text-decoration-none">
                                <i class="bi bi-question-circle me-1"></i>Forgot your password?
                            </a>
                        </div>

                        <!-- Divider -->
                        <hr class="my-4">

                        <!-- Register Link -->
                        <div class="text-center">
                            <p class="mb-0">Don't have an account?</p>
                            <a href="{{ route('register') }}" class="btn btn-outline-primary mt-2">
                                <i class="bi bi-person-plus me-1"></i>Create Account
                            </a>
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
});
</script>
@endpush
