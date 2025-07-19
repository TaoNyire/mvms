<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MVMS - Malawi Volunteer Management System</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #3b82f6;
            --primary-dark: #1d4ed8;
            --primary-light: #60a5fa;
            --secondary-color: #64748b;
            --success-color: #10b981;
            --warning-color: #f59e0b;
            --danger-color: #ef4444;
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
            background-color: var(--white);
        }

        h1, h2, h3, h4, h5, h6 {
            font-family: 'Poppins', sans-serif;
            font-weight: 600;
            line-height: 1.4;
            color: var(--text-dark);
        }

        /* Hero Section */
        .hero-section {
            background: linear-gradient(135deg, #4f46e5 0%, #3730a3 100%);
            min-height: 100vh;
            position: relative;
            overflow: hidden;
        }

        .hero-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="rgba(255,255,255,0.1)"/><circle cx="75" cy="75" r="1" fill="rgba(255,255,255,0.1)"/><circle cx="50" cy="10" r="0.5" fill="rgba(255,255,255,0.05)"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
            opacity: 0.3;
        }

        .hero-content {
            position: relative;
            z-index: 1;
        }

        /* Navigation */
        .navbar {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
        }

        .navbar-brand {
            font-family: 'Poppins', sans-serif;
            font-weight: 700;
            font-size: 1.5rem;
            color: white !important;
        }

        .navbar-nav .nav-link {
            color: rgba(255, 255, 255, 0.9) !important;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .navbar-nav .nav-link:hover {
            color: white !important;
            transform: translateY(-1px);
        }

        /* Buttons */
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
            background: var(--white);
            color: var(--primary-color);
            box-shadow: var(--shadow-md);
        }

        .btn-primary:hover {
            background: var(--white);
            color: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }

        .btn-outline-light {
            border: 2px solid rgba(255, 255, 255, 0.8);
            color: white;
            background: transparent;
        }

        .btn-outline-light:hover {
            background: rgba(255, 255, 255, 0.1);
            color: white;
            transform: translateY(-1px);
            box-shadow: var(--shadow-md);
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

        .feature-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #6366f1 0%, #4338ca 100%);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            color: white;
            font-size: 2rem;
        }

        /* Stats Section */
        .stats-section {
            background: var(--light-bg);
        }

        .stat-card {
            text-align: center;
            padding: 2rem 1rem;
        }

        .stat-number {
            font-size: 3rem;
            font-weight: 800;
            color: var(--primary-color);
            font-family: 'Poppins', sans-serif;
        }

        .stat-label {
            color: var(--text-muted);
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-size: 0.9rem;
        }

        /* Footer */
        .footer {
            background: var(--text-dark);
            color: white;
        }

        .footer a {
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .footer a:hover {
            color: white;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .hero-section {
                min-height: 80vh;
            }
            
            .stat-number {
                font-size: 2.5rem;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg fixed-top">
        <div class="container">
            <a class="navbar-brand" href="#">
                <i class="bi bi-grid-3x3-gap me-2"></i>Malawi Volunteer Management System - MVMS
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                   
                    <li class="nav-item ms-2">
                        <a class="btn btn-outline-light btn-sm" href="/register">Register Today</a>
                    </li>
                    <li class="nav-item ms-2">
                        <a class="btn btn-outline-light btn-sm" href="/login">Login Now</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section d-flex align-items-center">
        <div class="container hero-content">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <div class="mb-4">
                        <h1 class="display-4 fw-bold text-white mb-4">
                            Empowering Communities Through 
                            <span class="text-warning">Volunteer Management in Malawi</span>
                        </h1>
                        <p class="lead text-white opacity-90 mb-5">
                            Connect volunteers with meaningful opportunities across Malawi. 
                            Our comprehensive platform streamlines volunteer coordination, 
                            making community impact more accessible and effective.
                        </p>
                        <div class="d-flex flex-wrap gap-3">
                            <a href="/register" class="btn btn-primary btn-lg">
                                <i class="bi bi-person-plus me-2"></i>Create Account
                            </a>
                            <a href="/login" class="btn btn-outline-light btn-lg">
                                <i class="bi bi-play-circle me-2"></i>Sign In
                            </a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="text-center">
                        <div class="bg-white bg-opacity-10 rounded-4 p-5">
                            <i class="bi bi-people-fill text-white" style="font-size: 8rem;"></i>
                            <h3 class="text-white mt-3">Join Our Community in Malawi</h3>
                            <p class="text-white opacity-75">Making a difference, together</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
