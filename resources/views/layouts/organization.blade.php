<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Organization Dashboard - MVMS')</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <!-- Custom CSS -->
    <style>
        :root {
            --primary-color: #007bff;
            --primary-dark: #0056b3;
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

        .nav-section-header {
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            margin-bottom: 5px;
        }

        .nav-section-header small {
            letter-spacing: 0.5px;
            font-size: 0.75rem;
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

        /* Organization-specific colors */
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
    </style>
    
    @stack('styles')
</head>
<body>
    <!-- Sidebar -->
    <nav class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <h4><i class="bi bi-building me-2"></i><span>MVMS</span></h4>
            <small class="text-light opacity-75"><span>Organization Portal</span></small>
        </div>
        
        <ul class="nav flex-column mt-3">
            <li class="nav-item">
                <a class="nav-link" href="{{ route('organization.dashboard') }}">
                    <i class="bi bi-speedometer2"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('notifications.index') }}">
                    <i class="bi bi-bell"></i>
                    <span>Notifications</span>
                    <span class="badge bg-primary ms-2" id="notification-count" style="display: none;"></span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('messages.index') }}">
                    <i class="bi bi-chat-dots"></i>
                    <span>Messages</span>
                    <span class="badge bg-info ms-2" id="message-count" style="display: none;"></span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('announcements.index') }}">
                    <i class="bi bi-megaphone"></i>
                    <span>Announcements</span>
                </a>
            </li>

            <!-- Opportunity Management Section -->
            <li class="nav-item mt-3">
                <div class="nav-section-header px-3 py-2">
                    <small class="text-light opacity-75 fw-semibold">OPPORTUNITY MANAGEMENT</small>
                </div>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('opportunities.create') }}">
                    <i class="bi bi-plus-circle"></i>
                    <span>Create Opportunity</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('opportunities.index') }}">
                    <i class="bi bi-briefcase"></i>
                    <span>My Opportunities</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('organization.applications.index') }}">
                    <i class="bi bi-file-earmark-text"></i>
                    <span>Applications</span>
                    @php
                        $pendingCount = \App\Models\Application::whereHas('opportunity', function($query) {
                            $query->where('organization_id', Auth::id());
                        })->where('status', 'pending')->count();
                    @endphp
                    @if($pendingCount > 0)
                        <span class="badge bg-warning text-dark ms-2">{{ $pendingCount }}</span>
                    @endif
                </a>
            </li>

            <!-- Task & Assignment Management Section -->
            <li class="nav-item mt-3">
                <div class="nav-section-header px-3 py-2">
                    <small class="text-light opacity-75 fw-semibold">TASK & SCHEDULING</small>
                </div>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('organization.calendar') }}">
                    <i class="bi bi-calendar"></i>
                    <span>Calendar & Schedule</span>
                </a>
            </li>



            <!-- Organization Profile Section -->
            <li class="nav-item mt-3">
                <div class="nav-section-header px-3 py-2">
                    <small class="text-light opacity-75 fw-semibold">ORGANIZATION</small>
                </div>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('organization.profile.show') }}">
                    <i class="bi bi-building-gear"></i>
                    <span>Organization Profile</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('organization.profile.create') }}">
                    <i class="bi bi-pencil-square"></i>
                    <span>Edit Profile</span>
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
                <h5 class="mb-0">@yield('page-title', 'Dashboard')</h5>
            </div>
            
            <div class="user-menu dropdown">
                <button class="dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    <div class="user-avatar">
                        {{ substr(Auth::user()->name, 0, 1) }}
                    </div>
                    <span>{{ Auth::user()->name }}</span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="{{ route('organization.profile.show') }}"><i class="bi bi-building me-2"></i>View Profile</a></li>
                    <li><a class="dropdown-item" href="{{ route('organization.profile.create') }}"><i class="bi bi-pencil me-2"></i>Edit Profile</a></li>
                    <li><a class="dropdown-item" href="{{ route('opportunities.index') }}"><i class="bi bi-briefcase me-2"></i>My Opportunities</a></li>
                    <li><a class="dropdown-item" href="{{ route('organization.applications.index') }}"><i class="bi bi-file-earmark-text me-2"></i>Applications</a></li>
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
                <span>&copy; {{ date('Y') }} MVMS - Malawi Volunteer Management System. All rights reserved.</span>
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

        // Real-time notification and message updates
        function updateNotificationCount() {
            fetch('/notifications/unread-count')
                .then(response => response.json())
                .then(data => {
                    const badge = document.getElementById('notification-count');
                    if (data.count > 0) {
                        badge.textContent = data.count;
                        badge.style.display = 'inline';
                    } else {
                        badge.style.display = 'none';
                    }
                })
                .catch(error => console.error('Error fetching notification count:', error));
        }

        function updateMessageCount() {
            fetch('/messages/unread-count')
                .then(response => response.json())
                .then(data => {
                    const badge = document.getElementById('message-count');
                    if (data.count > 0) {
                        badge.textContent = data.count;
                        badge.style.display = 'inline';
                    } else {
                        badge.style.display = 'none';
                    }
                })
                .catch(error => console.error('Error fetching message count:', error));
        }

        // Update counts on page load and periodically
        updateNotificationCount();
        updateMessageCount();

        // Update counts every 30 seconds
        setInterval(function() {
            updateNotificationCount();
            updateMessageCount();
        }, 30000);

        // Show toast notifications for real-time updates
        function showToastNotification(title, message, type = 'info') {
            // Create toast element
            const toast = document.createElement('div');
            toast.className = `toast align-items-center text-white bg-${type} border-0`;
            toast.setAttribute('role', 'alert');
            toast.setAttribute('aria-live', 'assertive');
            toast.setAttribute('aria-atomic', 'true');

            toast.innerHTML = `
                <div class="d-flex">
                    <div class="toast-body">
                        <strong>${title}</strong><br>
                        ${message}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            `;

            // Add to toast container
            let container = document.getElementById('toast-container');
            if (!container) {
                container = document.createElement('div');
                container.id = 'toast-container';
                container.className = 'toast-container position-fixed top-0 end-0 p-3';
                container.style.zIndex = '1055';
                document.body.appendChild(container);
            }

            container.appendChild(toast);

            // Show toast
            const bsToast = new bootstrap.Toast(toast);
            bsToast.show();

            // Remove from DOM after hiding
            toast.addEventListener('hidden.bs.toast', function() {
                toast.remove();
            });
        }
    </script>
    
    @stack('scripts')
</body>
</html>
