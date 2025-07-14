<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Admin Dashboard - MVMS')</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <!-- Custom CSS -->
    <style>
        :root {
            --primary-color: #6f42c1;
            --primary-dark: #5a2d91;
            --secondary-color: #6c757d;
            --sidebar-width: 280px;
            --header-height: 70px;
            --footer-height: 60px;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
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
            transition: all 0.3s ease;
            overflow-y: auto;
        }

        .sidebar.collapsed {
            width: 70px;
        }

        .sidebar-header {
            padding: 1.5rem 1rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            text-align: center;
        }

        .sidebar-header h4 {
            margin: 0;
            font-weight: 600;
            transition: opacity 0.3s ease;
        }

        .sidebar.collapsed .sidebar-header h4 {
            opacity: 0;
        }

        .nav-item {
            margin: 0.2rem 0;
        }

        .nav-link {
            color: rgba(255, 255, 255, 0.8) !important;
            padding: 0.8rem 1rem;
            border-radius: 0.5rem;
            margin: 0 0.5rem;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
        }

        .nav-link:hover, .nav-link.active {
            background-color: rgba(255, 255, 255, 0.1);
            color: white !important;
            transform: translateX(5px);
        }

        .nav-link i {
            width: 20px;
            margin-right: 0.8rem;
            text-align: center;
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
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.08);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 20px rgba(0,0,0,0.12);
        }

        .card-header {
            background: transparent;
            border-bottom: 1px solid #f1f3f4;
            font-weight: 600;
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

        /* Admin-specific colors */
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .btn-primary:hover {
            background-color: var(--primary-dark);
            border-color: var(--primary-dark);
        }

        .text-primary {
            color: var(--primary-color) !important;
        }

        .bg-primary {
            background-color: var(--primary-color) !important;
        }

        .admin-badge {
            background: linear-gradient(45deg, #ff6b6b, #feca57);
            color: white;
            padding: 0.2rem 0.5rem;
            border-radius: 0.3rem;
            font-size: 0.7rem;
            font-weight: 600;
        }
    </style>
    
    @stack('styles')
</head>
<body>
    <!-- Sidebar -->
    <nav class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <h4><i class="bi bi-shield-check me-2"></i><span>MVMS</span></h4>
            <small class="text-light opacity-75"><span>Admin Portal</span></small>
            <div class="admin-badge mt-2"><span>ADMINISTRATOR</span></div>
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
                    @php
                        $pendingOrgs = \App\Models\OrganizationProfile::where('status', 'pending')->count();
                    @endphp
                    @if($pendingOrgs > 0)
                        <span class="badge bg-warning text-dark ms-2">{{ $pendingOrgs }}</span>
                    @endif
                </a>
            </li>

            <!-- Volunteers (filtered users) -->
            <li class="nav-item">
                <a class="nav-link" href="{{ route('admin.users.index', ['role' => 'volunteer']) }}">
                    <i class="bi bi-person-heart"></i>
                    <span>Volunteers</span>
                </a>
            </li>a

            <!-- System Logs -->
            
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
                    <li><a class="dropdown-item" href="#"><i class="bi bi-person me-2"></i>Profile</a></li>
                    <li><a class="dropdown-item" href="#"><i class="bi bi-gear me-2"></i>Settings</a></li>
                    <li><a class="dropdown-item" href="#"><i class="bi bi-shield-check me-2"></i>Security</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <form action="{{ route('logout') }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="dropdown-item">
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
