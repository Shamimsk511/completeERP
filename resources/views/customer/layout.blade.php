<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Customer Portal') - {{ config('app.name', 'ERP System') }}</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #0f172a;
            --secondary-color: #0ea5e9;
            --accent-color: #f59e0b;
            --surface-color: #ffffff;
            --muted-color: #94a3b8;
            --ink-color: #0b1220;
            --bg-color: #f5f7fb;
            --card-shadow: 0 20px 50px rgba(15, 23, 42, 0.08);
            --radius-xl: 24px;
            --radius-lg: 18px;
            --radius-md: 14px;
        }

        body {
            font-family: "Plus Jakarta Sans", "Segoe UI", sans-serif;
            background: radial-gradient(circle at 15% 10%, #e0f2fe 0%, #f5f7fb 45%, #f8fafc 100%);
            color: var(--ink-color);
            min-height: 100vh;
        }

        .portal-shell {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        
        .navbar-brand {
            font-family: "Space Grotesk", "Segoe UI", sans-serif;
            font-weight: 700;
            letter-spacing: 0.5px;
        }
        
        .nav-link {
            font-weight: 500;
        }
        
        .card {
            border: none;
            box-shadow: var(--card-shadow);
            border-radius: var(--radius-lg);
        }
        
        .card-header {
            background: transparent;
            border: none;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            border: none;
            border-radius: 12px;
            padding: 0.6rem 1.2rem;
            font-weight: 600;
            box-shadow: 0 12px 24px rgba(15, 23, 42, 0.15);
        }
        
        .btn-primary:hover {
            background: linear-gradient(135deg, #111827, #1f2937);
        }
        
        .sidebar {
            min-height: calc(100vh - 76px);
            background: #f8f9fa;
        }
        
        .sidebar .nav-link {
            color: #495057;
            padding: 0.75rem 1rem;
            border-radius: 0.375rem;
            margin: 0.125rem 0;
        }
        
        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            background: var(--primary-color);
            color: white;
        }
        
        .main-content {
            min-height: calc(100vh - 76px);
        }

        .portal-navbar {
            background: linear-gradient(120deg, #0f172a 0%, #1e293b 48%, #0ea5e9 120%);
            border-bottom: 1px solid rgba(148, 163, 184, 0.2);
            box-shadow: 0 16px 40px rgba(15, 23, 42, 0.2);
        }

        .portal-navbar .nav-link,
        .portal-navbar .navbar-brand {
            color: #f8fafc !important;
        }

        .portal-navbar .nav-link.active {
            background: rgba(255, 255, 255, 0.15);
            border-radius: 999px;
            padding: 0.45rem 1rem;
        }

        .portal-header {
            margin: 2rem 0 1.5rem;
        }

        .portal-title {
            font-family: "Space Grotesk", "Segoe UI", sans-serif;
            font-size: clamp(1.6rem, 2vw, 2.4rem);
            font-weight: 700;
            color: var(--primary-color);
        }

        .portal-subtitle {
            color: var(--muted-color);
            font-size: 0.95rem;
        }

        .stat-chip {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            background: rgba(14, 165, 233, 0.1);
            color: #0369a1;
            padding: 0.35rem 0.65rem;
            border-radius: 999px;
            font-size: 0.78rem;
            font-weight: 600;
        }

        .glass-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(148, 163, 184, 0.2);
        }

        .quick-pill {
            border-radius: 999px;
            padding: 0.55rem 1.1rem;
            font-weight: 600;
            border: 1px solid transparent;
        }

        .quick-pill-outline {
            border-color: rgba(15, 23, 42, 0.15);
            color: var(--primary-color);
            background: #ffffff;
        }

        .portal-content {
            padding-bottom: 5rem;
        }

        .mobile-nav {
            position: fixed;
            bottom: 1rem;
            left: 50%;
            transform: translateX(-50%);
            display: none;
            background: rgba(15, 23, 42, 0.95);
            border-radius: 999px;
            padding: 0.5rem 1.2rem;
            gap: 1.5rem;
            z-index: 50;
            box-shadow: 0 12px 30px rgba(15, 23, 42, 0.25);
        }

        .mobile-nav a {
            color: #e2e8f0;
            font-size: 0.9rem;
            text-decoration: none;
        }

        .mobile-nav a.active {
            color: #38bdf8;
        }

        @media (max-width: 992px) {
            .portal-navbar .navbar-collapse {
                background: rgba(15, 23, 42, 0.95);
                padding: 1rem;
                border-radius: 16px;
                margin-top: 0.75rem;
            }
        }

        @media (max-width: 768px) {
            .portal-header {
                margin: 1.5rem 0 1rem;
            }

            .portal-content {
                padding-bottom: 6.5rem;
            }

            .mobile-nav {
                display: inline-flex;
            }
        }
    </style>
</head>
<body>
    <div class="portal-shell">
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark portal-navbar">
        <div class="container-fluid">
            <a class="navbar-brand" href="{{ route('customer.dashboard') }}">
                <i class="fas fa-users me-2"></i>
                Customer Portal
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('customer.dashboard') ? 'active' : '' }}" 
                           href="{{ route('customer.dashboard') }}">
                            <i class="fas fa-tachometer-alt me-1"></i>
                            Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('customer.invoices*') ? 'active' : '' }}" 
                           href="{{ route('customer.invoices') }}">
                            <i class="fas fa-file-invoice me-1"></i>
                            Invoices
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('customer.ledger') ? 'active' : '' }}" 
                           href="{{ route('customer.ledger') }}">
                            <i class="fas fa-book me-1"></i>
                            Ledger
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('customer.profile') ? 'active' : '' }}" 
                           href="{{ route('customer.profile') }}">
                            <i class="fas fa-user me-1"></i>
                            Profile
                        </a>
                    </li>
                </ul>
                
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user-circle me-1"></i>
                            {{ Auth::guard('customer')->user()->name }}
                        </a>
                        <ul class="dropdown-menu">
                            <li>
                                <a class="dropdown-item" href="{{ route('customer.profile') }}">
                                    <i class="fas fa-user me-2"></i>
                                    Profile
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form method="POST" action="{{ route('customer.logout') }}" style="display: inline;">
                                    @csrf
                                    <button type="submit" class="dropdown-item">
                                        <i class="fas fa-sign-out-alt me-2"></i>
                                        Logout
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container-fluid portal-content">
        <!-- Flash Messages -->
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
                <i class="fas fa-check-circle me-2"></i>
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i>
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('info'))
            <div class="alert alert-info alert-dismissible fade show mt-3" role="alert">
                <i class="fas fa-info-circle me-2"></i>
                {{ session('info') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <!-- Page Content -->
        @yield('content')
    </div>

    @if(session('customer_password_prompt'))
        <div class="modal fade" id="passwordPromptModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header border-0">
                        <h5 class="modal-title">Secure Your Account</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p class="mb-2">It looks like this is your first login. Would you like to set a password now?</p>
                        <p class="text-muted small mb-0">You can skip for now and set it later from your profile.</p>
                    </div>
                    <div class="modal-footer border-0">
                        <form method="POST" action="{{ route('customer.password.skip') }}">
                            @csrf
                            <button type="submit" class="btn quick-pill quick-pill-outline">Skip for now</button>
                        </form>
                        <a href="{{ route('customer.password.show') }}" class="btn btn-primary">
                            Set Password
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <div class="mobile-nav">
        <a href="{{ route('customer.dashboard') }}" class="{{ request()->routeIs('customer.dashboard') ? 'active' : '' }}">
            <i class="fas fa-home"></i>
        </a>
        <a href="{{ route('customer.invoices') }}" class="{{ request()->routeIs('customer.invoices*') ? 'active' : '' }}">
            <i class="fas fa-file-invoice"></i>
        </a>
        <a href="{{ route('customer.ledger') }}" class="{{ request()->routeIs('customer.ledger') ? 'active' : '' }}">
            <i class="fas fa-book"></i>
        </a>
        <a href="{{ route('customer.profile') }}" class="{{ request()->routeIs('customer.profile') ? 'active' : '' }}">
            <i class="fas fa-user"></i>
        </a>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    @if(session('customer_password_prompt'))
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const modal = new bootstrap.Modal(document.getElementById('passwordPromptModal'));
                modal.show();
            });
        </script>
    @endif
    
    @stack('scripts')
    </div>
</body>
</html>
