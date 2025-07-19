<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'MVMS - Malawi Volunteer Management System')</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Custom CSS -->
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%);
            --primary-color: #0ea5e9;
            --primary-dark: #0284c7;
            --secondary-color: #64748b;
            --success-color: #10b981;
            --warning-color: #f59e0b;
            --danger-color: #ef4444;
            --info-color: #3b82f6;
            --light-bg: #f8fafc;
            --white: #ffffff;
            --text-dark: #1e293b;
            --text-muted: #64748b;
            --border-color: #e2e8f0;
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            --shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            font-weight: 400;
            line-height: 1.6;
            color: var(--text-dark);
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }

        .auth-container {
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            position: relative;
        }

        .auth-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="rgba(255,255,255,0.1)"/><circle cx="75" cy="75" r="1" fill="rgba(255,255,255,0.1)"/><circle cx="50" cy="10" r="0.5" fill="rgba(255,255,255,0.05)"/><circle cx="20" cy="60" r="0.5" fill="rgba(255,255,255,0.05)"/><circle cx="80" cy="40" r="0.5" fill="rgba(255,255,255,0.05)"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
            opacity: 0.3;
        }

        .auth-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 24px;
            box-shadow: var(--shadow-xl);
            border: 1px solid rgba(255, 255, 255, 0.2);
            position: relative;
            z-index: 1;
        }

        .form-control {
            border: 2px solid var(--border-color);
            border-radius: 12px;
            padding: 16px 18px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background-color: rgba(255, 255, 255, 0.9);
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(14, 165, 233, 0.15);
            background-color: var(--white);
        }

        .form-control.is-invalid {
            border-color: var(--danger-color);
            box-shadow: 0 0 0 0.2rem rgba(239, 68, 68, 0.15);
        }

        .form-control.is-valid {
            border-color: var(--success-color);
            box-shadow: 0 0 0 0.2rem rgba(16, 185, 129, 0.15);
        }

        .btn-primary {
            background: var(--primary-gradient);
            border: none;
            border-radius: 12px;
            padding: 18px 24px;
            font-weight: 600;
            font-size: 1.1rem;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }

        .btn-primary:active {
            transform: translateY(0);
        }

        .btn-outline-primary {
            border: 2px solid var(--primary-color);
            color: var(--primary-color);
            border-radius: 12px;
            padding: 12px 24px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-outline-primary:hover {
            background: var(--primary-gradient);
            border-color: transparent;
            color: white;
            transform: translateY(-1px);
            box-shadow: var(--shadow-md);
        }

        .form-label {
            font-weight: 600;
            color: var(--text-dark);
            margin-bottom: 8px;
        }

        .invalid-feedback {
            font-size: 0.875rem;
            font-weight: 500;
        }

        .alert {
            border: none;
            border-radius: 12px;
            padding: 16px 20px;
            font-weight: 500;
            margin-bottom: 24px;
        }

        .alert-success {
            background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
            color: #065f46;
            border-left: 4px solid var(--success-color);
        }

        .alert-danger {
            background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
            color: #991b1b;
            border-left: 4px solid var(--danger-color);
        }

        .text-primary {
            color: var(--primary-color) !important;
        }

        .text-muted {
            color: var(--text-muted) !important;
        }

        .loading-spinner {
            width: 1rem;
            height: 1rem;
        }

        /* Responsive adjustments */
        @media (max-width: 576px) {
            .auth-card {
                margin: 20px;
                border-radius: 20px;
            }
            
            .form-control {
                padding: 14px 16px;
                font-size: 0.95rem;
            }
            
            .btn-primary {
                padding: 16px 20px;
                font-size: 1rem;
            }
        }

        /* Animation for form elements */
        .form-control, .btn {
            animation: slideInUp 0.6s ease-out;
        }

        @keyframes slideInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Logo animation */
        .auth-card h1 {
            animation: fadeInDown 0.8s ease-out;
        }

        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
    
    @stack('styles')
</head>
<body>
    <!-- Main Content (No Navigation) -->
    <main>
        @yield('content')
    </main>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    
    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
    
    <!-- Common JavaScript -->
    <script>
        // CSRF Token setup for AJAX requests
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        // Global error handler for AJAX requests
        $(document).ajaxError(function(event, xhr, settings, thrownError) {
            console.error('AJAX Error:', xhr.responseText);
            if (xhr.status === 419) {
                showAlert('Session expired. Please refresh the page and try again.', 'danger');
            }
        });

        // Global alert function
        function showAlert(message, type = 'info') {
            const alertContainer = $('.alert-container');
            const alertClass = type === 'error' ? 'danger' : type;
            
            const alertHtml = `
                <div class="alert alert-${alertClass} alert-dismissible fade show" role="alert">
                    <i class="bi bi-${getAlertIcon(type)} me-2"></i>
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `;
            
            alertContainer.html(alertHtml);
            
            // Auto-dismiss after 5 seconds
            setTimeout(() => {
                $('.alert').fadeOut();
            }, 5000);
        }

        function getAlertIcon(type) {
            switch(type) {
                case 'success': return 'check-circle-fill';
                case 'danger': 
                case 'error': return 'exclamation-triangle-fill';
                case 'warning': return 'exclamation-triangle-fill';
                case 'info': return 'info-circle-fill';
                default: return 'info-circle-fill';
            }
        }
    </script>
    
    @stack('scripts')
</body>
</html>
