<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'System Admin Section - MVMS')</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Custom CSS -->
    <style>
        :root {
            --primary-color: #0ea5e9;
            --primary-dark: #0284c7;
            --primary-light: #38bdf8;
            --secondary-color: #64748b;
            --success-color: #10b981;
            --warning-color: #f59e0b;
            --danger-color: #ef4444;
            --info-color: #3b82f6;
            --sidebar-width: 280px;
            --header-height: 70px;
            --footer-height: 60px;
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
            background-color: var(--light-bg);
        }

        h1, h2, h3, h4, h5, h6 {
            font-family: 'Poppins', sans-serif;
            font-weight: 600;
            line-height: 1.4;
            color: var(--text-dark);
        }

        /* Sidebar Styles */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: var(--sidebar-width);
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
            color: white;
            z-index: 1000;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            overflow-y: auto;
            box-shadow: var(--shadow-xl);
        }

        .sidebar::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="0.5" fill="rgba(255,255,255,0.05)"/><circle cx="75" cy="75" r="0.5" fill="rgba(255,255,255,0.05)"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
            opacity: 0.3;
        }

        .sidebar.collapsed {
            width: 70px;
        }

        .sidebar-header {
            padding: 2rem 1rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.15);
            text-align: center;
            position: relative;
            z-index: 1;
        }

        .sidebar-header h4 {
            margin: 0;
            font-family: 'Poppins', sans-serif;
            font-weight: 700;
            font-size: 1.4rem;
            transition: opacity 0.3s ease;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .sidebar.collapsed .sidebar-header h4 {
            opacity: 0;
        }

        .nav-item {
            margin: 0.3rem 0;
            position: relative;
            z-index: 1;
        }

        .nav-link {
            color: rgba(255, 255, 255, 0.85) !important;
            padding: 1rem 1.2rem;
            border-radius: 12px;
            margin: 0 0.8rem;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            align-items: center;
            font-weight: 500;
            position: relative;
            overflow: hidden;
        }

        .nav-link::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.1), transparent);
            transition: left 0.5s;
        }

        .nav-link:hover::before {
            left: 100%;
        }

        .nav-link:hover, .nav-link.active {
            background-color: rgba(255, 255, 255, 0.15);
            color: white !important;
            transform: translateX(8px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .nav-link.active {
            background-color: rgba(255, 255, 255, 0.2);
            font-weight: 600;
        }

        .nav-link i {
            width: 22px;
            margin-right: 1rem;
            text-align: center;
            font-size: 1.1rem;
        }

        .sidebar.collapsed .nav-link span {
            display: none;
        }

        /* Main Content */
        .main-content {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            transition: margin-left 0.3s ease;
        }

        .sidebar.collapsed + .main-content {
            margin-left: 70px;
        }

        /* Header */
        .top-header {
            background: white;
            height: var(--header-height);
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            display: flex;
            align-items: center;
            padding: 0 2rem;
            position: sticky;
            top: 0;
            z-index: 999;
        }

        .sidebar-toggle {
            background: none;
            border: none;
            font-size: 1.2rem;
            color: var(--secondary-color);
            margin-right: 1rem;
        }

        .user-menu .dropdown-toggle {
            background: none;
            border: none;
            display: flex;
            align-items: center;
            color: var(--secondary-color);
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--primary-color);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            margin-right: 0.5rem;
        }

        /* Content Area */
        .content-area {
            padding: 2rem;
            min-height: calc(100vh - var(--header-height) - var(--footer-height));
        }

        /* Footer */
        .footer {
            background: white;
            height: var(--footer-height);
            border-top: 1px solid #dee2e6;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--secondary-color);
            font-size: 0.9rem;
        }

        /* Cards */
        .card {
            border: none;
            border-radius: 16px;
            box-shadow: var(--shadow-md);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            background: var(--white);
            overflow: hidden;
        }

        .card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-lg);
        }

        .card-header {
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            border-bottom: 1px solid var(--border-color);
            font-weight: 600;
            font-family: 'Poppins', sans-serif;
            padding: 1.25rem 1.5rem;
        }

        .card-body {
            padding: 1.5rem;
        }

        .card-title {
            font-family: 'Poppins', sans-serif;
            font-weight: 600;
            color: var(--text-dark);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.show {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
            }

            .content-area {
                padding: 1rem;
            }
        }

        /* Admin-specific colors and components */
        .btn {
            font-weight: 600;
            border-radius: 12px;
            padding: 12px 24px;
            font-size: 0.95rem;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border: none;
            position: relative;
            overflow: hidden;
        }

        .btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s;
        }

        .btn:hover::before {
            left: 100%;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
            color: white;
            box-shadow: var(--shadow-md);
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, var(--primary-dark) 0%, #0369a1 100%);
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }

        .btn-outline-primary {
            border: 2px solid var(--primary-color);
            color: var(--primary-color);
            background: transparent;
        }

        .btn-outline-primary:hover {
            background: var(--primary-color);
            color: white;
            transform: translateY(-1px);
            box-shadow: var(--shadow-md);
        }

        .text-primary {
            color: var(--primary-color) !important;
        }

        .bg-primary {
            background: var(--primary-color) !important;
        }

        .admin-badge {
            background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%);
            color: white;
            padding: 0.4rem 0.8rem;
            border-radius: 8px;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            box-shadow: var(--shadow-sm);
        }

        /* Badge styles */
        .badge {
            font-weight: 600;
            padding: 0.5rem 0.8rem;
            border-radius: 8px;
            font-size: 0.8rem;
        }

        /* Table improvements */
        .table {
            border-radius: 12px;
            overflow: hidden;
        }

        .table th {
            background: var(--light-bg);
            font-weight: 600;
            color: var(--text-dark);
            border: none;
            padding: 1rem;
        }

        .table td {
            padding: 1rem;
            border-color: var(--border-color);
            vertical-align: middle;
        }

        .table-hover tbody tr:hover {
            background-color: rgba(14, 165, 233, 0.05);
        }

        /* Progress bars */
        .progress {
            border-radius: 8px;
            background-color: var(--border-color);
        }

        .progress-bar {
            border-radius: 8px;
        }

        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            background: var(--light-bg);
        }

        ::-webkit-scrollbar-thumb {
            background: var(--border-color);
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: var(--text-muted);
        }
    </style>
    
    @stack('styles')
</head>
<body>
    <!-- Sidebar -->
    <nav class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <h4><i class="bi bi-grid-3x3-gap me-2"></i><span>MVMS</span></h4>
        </div>
        
        <ul class="nav flex-column mt-3">
            <!-- Dashboard -->
            <li class="nav-item">
                <a class="nav-link" href="{{ route('admin.dashboard') }}">
                    <i class="bi bi-speedometer2"></i>
                    <span>Dashboard</span>
                </a>
            </li>

            <!-- User Management -->
            <li class="nav-item">
                <a class="nav-link" href="{{ route('admin.users.index') }}">
                    <i class="bi bi-people"></i>
                    <span>User Management</span>
                </a>
            </li>

            <!-- Organization Approval -->
            <li class="nav-item">
                <a class="nav-link" href="{{ route('admin.organizations.index') }}">
                    <i class="bi bi-building"></i>
                    <span>Organizations</span>
                </a>
            </li>

            <!-- Volunteers (filtered users) -->
            <li class="nav-item">
                <a class="nav-link" href="{{ route('admin.users.index', ['role' => 'volunteer']) }}">
                    <i class="bi bi-person-heart"></i>
                    <span>Volunteers</span>
                </a>
            </li>

            <!-- System Management Section -->
            <li class="nav-item mt-3">
                <div class="nav-section-header px-3 py-2">
                    <small class="text-light opacity-75 fw-semibold">SYSTEM MANAGEMENT</small>
                </div>
            </li>





            <!-- System Logs -->
            <li class="nav-item">
                <a class="nav-link" href="{{ route('admin.logs') }}">
                    <i class="bi bi-file-text"></i>
                    <span>System Logs</span>
                </a>
            </li>



        </ul>
    </nav>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Header -->
        <header class="top-header">
            <button class="sidebar-toggle" id="sidebarToggle">
                <i class="bi bi-list"></i>
            </button>
            
            <div class="flex-grow-1">
                <h5 class="mb-0">@yield('page-title', 'Admin Dashboard')</h5>
            </div>
            
            <div class="user-menu dropdown">
                <button class="dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    <div class="user-avatar">
                        {{ substr(Auth::user()->name, 0, 1) }}
                    </div>
                    <span>{{ Auth::user()->name }}</span>
                    <span class="badge bg-warning text-dark ms-2">Admin</span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li>
                        <form action="{{ route('logout') }}" method="POST" class="d-inline" id="logoutForm">
                            @csrf
                            <button type="button" class="dropdown-item" onclick="confirmLogout()">
                                <i class="bi bi-box-arrow-right me-2"></i>Logout
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
        </header>

        <!-- Content Area -->
        <main class="content-area">
            @yield('content')
        </main>

        <!-- Footer -->
        <footer class="footer">
            <div class="text-center">
                <span>&copy; {{ date('Y') }} Malawi Volunteer Management System. All rights reserved.</span>
            </div>
        </footer>
    </div>

    <!-- Logout Confirmation Modal -->
    <div class="modal fade" id="logoutModal" tabindex="-1" aria-labelledby="logoutModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="logoutModalLabel">
                        <i class="bi bi-question-circle me-2"></i>Confirm Logout
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to logout?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-1"></i>No
                    </button>
                    <button type="button" class="btn btn-danger" id="confirmLogoutBtn">
                        <i class="bi bi-box-arrow-right me-1"></i>Yes, Logout
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    
    <!-- Common JavaScript -->
    <script>
        // CSRF Token setup for AJAX requests
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        // Sidebar toggle functionality
        document.getElementById('sidebarToggle').addEventListener('click', function() {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('collapsed');
        });

        // Mobile sidebar toggle
        if (window.innerWidth <= 768) {
            document.getElementById('sidebarToggle').addEventListener('click', function() {
                const sidebar = document.getElementById('sidebar');
                sidebar.classList.toggle('show');
            });
        }

        // Logout confirmation function
        function confirmLogout() {
            const logoutModal = new bootstrap.Modal(document.getElementById('logoutModal'));
            logoutModal.show();
        }

        // Handle logout confirmation
        document.getElementById('confirmLogoutBtn').addEventListener('click', function() {
            document.getElementById('logoutForm').submit();
        });

        // Active nav link highlighting
        const currentPath = window.location.pathname;
        const navLinks = document.querySelectorAll('.nav-link');
        navLinks.forEach(link => {
            if (link.getAttribute('href') === currentPath) {
                link.classList.add('active');
            } else {
                link.classList.remove('active');
            }
        });


    </script>
    
    @stack('scripts')
</body>
</html>
