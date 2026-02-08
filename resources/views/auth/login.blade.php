<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - ERP System</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.min.css">
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Source Sans Pro', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .login-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            overflow: hidden;
            width: 100%;
            max-width: 900px;
            min-height: 600px;
            display: flex;
        }

        .login-left {
            flex: 1;
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
            padding: 60px 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            color: white;
            position: relative;
            overflow: hidden;
        }

        .login-left::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grid" width="10" height="10" patternUnits="userSpaceOnUse"><path d="M 10 0 L 0 0 0 10" fill="none" stroke="rgba(255,255,255,0.1)" stroke-width="0.5"/></pattern></defs><rect width="100" height="100" fill="url(%23grid)"/></svg>');
            animation: float 20s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translate(0, 0) rotate(0deg); }
            33% { transform: translate(30px, -30px) rotate(120deg); }
            66% { transform: translate(-20px, 20px) rotate(240deg); }
        }

        .logo-section {
            text-align: center;
            z-index: 1;
        }

        .logo-icon {
            width: 80px;
            height: 80px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            backdrop-filter: blur(10px);
        }

        .logo-icon i {
            font-size: 36px;
            color: white;
        }

        .logo-title {
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 10px;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .logo-subtitle {
            font-size: 16px;
            opacity: 0.9;
            margin-bottom: 30px;
        }

        .feature-list {
            list-style: none;
            text-align: left;
        }

        .feature-item {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
            opacity: 0.9;
        }

        .feature-item i {
            margin-right: 12px;
            font-size: 16px;
        }

        .login-right {
            flex: 1;
            padding: 60px 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .login-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .login-title {
            font-size: 28px;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 8px;
        }

        .login-subtitle {
            color: #6b7280;
            font-size: 16px;
        }

        .login-form {
            width: 100%;
        }

        .form-group {
            margin-bottom: 24px;
        }

        .form-label {
            display: block;
            font-weight: 600;
            color: #374151;
            margin-bottom: 8px;
            font-size: 14px;
        }

        .input-group {
            position: relative;
        }

        .form-input {
            width: 100%;
            padding: 14px 16px 14px 50px;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            font-size: 16px;
            transition: all 0.2s ease;
            background: white;
            color: #374151;
        }

        .form-input:focus {
            outline: none;
            border-color: #6366f1;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
            transform: translateY(-1px);
        }

        .form-input.is-invalid {
            border-color: #ef4444;
            box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1);
        }

        .input-icon {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #6b7280;
            font-size: 16px;
            z-index: 1;
        }

        .form-input:focus + .input-icon {
            color: #6366f1;
        }

        .invalid-feedback {
            color: #ef4444;
            font-size: 12px;
            margin-top: 6px;
            display: block;
        }

        .remember-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .checkbox-group {
            display: flex;
            align-items: center;
        }

        .modern-checkbox {
            position: relative;
            display: inline-block;
            margin-right: 8px;
        }

        .modern-checkbox input[type="checkbox"] {
            position: absolute;
            opacity: 0;
            cursor: pointer;
        }

        .modern-checkbox label {
            position: relative;
            display: inline-block;
            width: 20px;
            height: 20px;
            background: white;
            border: 2px solid #d1d5db;
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .modern-checkbox label:after {
            content: "";
            position: absolute;
            left: 6px;
            top: 2px;
            width: 6px;
            height: 10px;
            border: solid white;
            border-width: 0 2px 2px 0;
            transform: rotate(45deg);
            opacity: 0;
            transition: all 0.2s ease;
        }

        .modern-checkbox input[type="checkbox"]:checked + label {
            background: #6366f1;
            border-color: #6366f1;
        }

        .modern-checkbox input[type="checkbox"]:checked + label:after {
            opacity: 1;
        }

        .checkbox-label {
            color: #374151;
            font-size: 14px;
            cursor: pointer;
        }

        .forgot-link {
            color: #6366f1;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.2s ease;
        }

        .forgot-link:hover {
            color: #4f46e5;
            text-decoration: none;
        }

        .login-btn {
            width: 100%;
            padding: 14px 24px;
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .login-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(99, 102, 241, 0.3);
        }

        .login-btn:active {
            transform: translateY(0);
        }

        .login-btn:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none;
        }

        .login-footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
        }

        .footer-text {
            color: #6b7280;
            font-size: 14px;
        }

        .footer-link {
            color: #6366f1;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.2s ease;
        }

        .footer-link:hover {
            color: #4f46e5;
            text-decoration: none;
        }

        /* Loading Animation */
        .spinner {
            width: 20px;
            height: 20px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            border-top-color: white;
            animation: spin 1s ease-in-out infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* Toastr Customization */
        .toast-success {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        }

        .toast-error {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        }

        .toast-warning {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        }

        .toast-info {
            background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .login-container {
                flex-direction: column;
                max-width: 400px;
                margin: 20px;
            }

            .login-left {
                padding: 40px 30px;
                min-height: 300px;
            }

            .login-right {
                padding: 40px 30px;
            }

            .logo-title {
                font-size: 24px;
            }

            .login-title {
                font-size: 24px;
            }

            .feature-list {
                display: none;
            }

            .form-input {
                font-size: 16px; /* Prevents zoom on iOS */
            }
        }

        @media (max-width: 480px) {
            body {
                padding: 10px;
            }

            .login-container {
                margin: 10px;
            }

            .login-left,
            .login-right {
                padding: 30px 20px;
            }
        }

        /* Animation for form elements */
        .form-group {
            opacity: 0;
            transform: translateY(20px);
            animation: slideUp 0.6s ease forwards;
        }

        .form-group:nth-child(1) { animation-delay: 0.1s; }
        .form-group:nth-child(2) { animation-delay: 0.2s; }
        .form-group:nth-child(3) { animation-delay: 0.3s; }
        .form-group:nth-child(4) { animation-delay: 0.4s; }

        @keyframes slideUp {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <!-- Left Side - Branding -->
        <div class="login-left">
            <div class="logo-section">
                <div class="logo-icon">
                    <i class="fas fa-chart-line"></i>
                </div>
                <h1 class="logo-title">ERP System</h1>
                <p class="logo-subtitle">Complete Business Management Solution</p>
                
                <ul class="feature-list">
                    <li class="feature-item">
                        <i class="fas fa-check"></i>
                        <span>Inventory Management</span>
                    </li>
                    <li class="feature-item">
                        <i class="fas fa-check"></i>
                        <span>Sales & Purchase Tracking</span>
                    </li>
                    <li class="feature-item">
                        <i class="fas fa-check"></i>
                        <span>Delivery Management</span>
                    </li>
                    <li class="feature-item">
                        <i class="fas fa-check"></i>
                        <span>Real-time Reports</span>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Right Side - Login Form -->
        <div class="login-right">
            <div class="login-header">
                <h2 class="login-title">Welcome Back</h2>
                <p class="login-subtitle">Sign in to your account to continue</p>
            </div>

            <form method="POST" action="{{ route('login') }}" class="login-form" id="loginForm">
                @csrf

                <!-- Email Field -->
                <div class="form-group">
                    <label for="email" class="form-label">Email Address</label>
                    <div class="input-group">
                        <input type="email" 
                               name="email" 
                               id="email"
                               class="form-input @error('email') is-invalid @enderror"
                               value="{{ old('email') }}" 
                               placeholder="Enter your email address"
                               autofocus 
                               required>
                        <i class="fas fa-envelope input-icon"></i>
                    </div>
                    @error('email')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Password Field -->
                <div class="form-group">
                    <label for="password" class="form-label">Password</label>
                    <div class="input-group">
                        <input type="password" 
                               name="password" 
                               id="password"
                               class="form-input @error('password') is-invalid @enderror"
                               placeholder="Enter your password"
                               required>
                        <i class="fas fa-lock input-icon"></i>
                    </div>
                    @error('password')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Company Selection -->
                <div class="form-group">
                    <label for="tenant_id" class="form-label">Company</label>
                    <div class="input-group">
                        <select
                            name="tenant_id"
                            id="tenant_id"
                            class="form-input @error('tenant_id') is-invalid @enderror"
                            required
                        >
                            <option value="">Select Company</option>
                            @foreach($tenants as $tenant)
                                <option value="{{ $tenant->id }}" {{ old('tenant_id') == $tenant->id ? 'selected' : '' }}>
                                    {{ $tenant->name }}
                                </option>
                            @endforeach
                        </select>
                        <i class="fas fa-building input-icon"></i>
                    </div>
                    @error('tenant_id')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Create New Company -->
                <div class="form-group">
                    <div class="remember-row">
                        <div class="checkbox-group">
                            <div class="modern-checkbox">
                                <input type="checkbox" name="create_company" id="create_company" value="1" {{ old('create_company') ? 'checked' : '' }}>
                                <label for="create_company"></label>
                            </div>
                            <label for="create_company" class="checkbox-label">Create new company</label>
                        </div>
                    </div>
                </div>

                <div class="form-group" id="companyNameGroup" style="display: none;">
                    <label for="company_name" class="form-label">New Company Name</label>
                    <div class="input-group">
                        <input type="text"
                               name="company_name"
                               id="company_name"
                               class="form-input @error('company_name') is-invalid @enderror"
                               value="{{ old('company_name') }}"
                               placeholder="Enter company name">
                        <i class="fas fa-briefcase input-icon"></i>
                    </div>
                    @error('company_name')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Remember Me & Forgot Password -->
                <div class="form-group">
                    <div class="remember-row">
                        <div class="checkbox-group">
                            <div class="modern-checkbox">
                                <input type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                                <label for="remember"></label>
                            </div>
                            <label for="remember" class="checkbox-label">Remember me</label>
                        </div>
                        <a href="{{ route('password.request') }}" class="forgot-link">
                            Forgot password?
                        </a>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="form-group">
                    <button type="submit" class="login-btn" id="submitBtn">
                        <i class="fas fa-sign-in-alt"></i>
                        <span>Sign In</span>
                    </button>
                </div>
            </form>

            {{-- Uncomment if registration is needed
            <div class="login-footer">
                <p class="footer-text">
                    Don't have an account? 
                    <a href="{{ route('register') }}" class="footer-link">Create one here</a>
                </p>
            </div>
            --}}
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"></script>
    
    <script>
        $(document).ready(function() {
            // Configure Toastr
            toastr.options = {
                "closeButton": true,
                "debug": false,
                "newestOnTop": true,
                "progressBar": true,
                "positionClass": "toast-top-right",
                "preventDuplicates": false,
                "onclick": null,
                "showDuration": "300",
                "hideDuration": "1000",
                "timeOut": "5000",
                "extendedTimeOut": "1000",
                "showEasing": "swing",
                "hideEasing": "linear",
                "showMethod": "fadeIn",
                "hideMethod": "fadeOut"
            };

            // Show error messages if any
            @if($errors->any())
                @foreach($errors->all() as $error)
                    toastr.error('{{ $error }}');
                @endforeach
            @endif

            // Show success message if any
            @if(session('success'))
                toastr.success('{{ session('success') }}');
            @endif

            // Show info message if any
            @if(session('status'))
                toastr.info('{{ session('status') }}');
            @endif

            function toggleCompanyCreate() {
                const isCreating = $('#create_company').is(':checked');
                $('#companyNameGroup').toggle(isCreating);
                $('#tenant_id').prop('disabled', isCreating);
            }

            toggleCompanyCreate();
            $('#create_company').on('change', toggleCompanyCreate);

            // Form submission handling
            $('#loginForm').submit(function(e) {
                const submitBtn = $('#submitBtn');
                const btnText = submitBtn.find('span');
                const btnIcon = submitBtn.find('i');
                
                // Show loading state
                submitBtn.prop('disabled', true);
                btnIcon.removeClass('fa-sign-in-alt').addClass('fa-spinner fa-spin');
                btnText.text('Signing In...');
                
                // Let the form submit normally
                return true;
            });

            // Input focus effects
            $('.form-input').on('focus', function() {
                $(this).parent().addClass('focused');
            }).on('blur', function() {
                $(this).parent().removeClass('focused');
                
                // Remove invalid class on blur if field has value
                if ($(this).val()) {
                    $(this).removeClass('is-invalid');
                }
            });

            // Real-time validation
            $('#email').on('input', function() {
                const email = $(this).val();
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                
                if (email && !emailRegex.test(email)) {
                    $(this).addClass('is-invalid');
                } else {
                    $(this).removeClass('is-invalid');
                }
            });

            $('#password').on('input', function() {
                const password = $(this).val();
                
                if (password && password.length < 6) {
                    $(this).addClass('is-invalid');
                } else {
                    $(this).removeClass('is-invalid');
                }
            });

            // Keyboard shortcuts
            $(document).keydown(function(e) {
                // Enter key to submit form
                if (e.which === 13 && !$('#submitBtn').prop('disabled')) {
                    $('#loginForm').submit();
                }
            });

            // Auto-focus on email if empty
            if (!$('#email').val()) {
                $('#email').focus();
            }
        });
    </script>
</body>
</html>
