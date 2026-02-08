<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Customer Login - {{ config('app.name', 'ERP System') }}</title>
    
    <!-- Fonts -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.min.css">
    
    <style>
        /* Reset and Base Styles */
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
            overflow-x: hidden;
        }

        /* Login Container */
        .login-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            overflow: hidden;
            width: 100%;
            max-width: 900px;
            min-height: 600px;
            display: flex;
            position: relative;
        }

        /* Left Side - Branding */
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

        /* Right Side - Login Form */
        .login-right {
            flex: 1;
            padding: 60px 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            position: relative;
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

        /* Form Styles */
        .login-form {
            width: 100%;
            position: relative;
            z-index: 1;
        }

        .form-group {
            position: relative;
            margin-bottom: 24px;
            z-index: 1;
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
            pointer-events: none;
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

        /* Help Text */
        .help-text {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 12px;
            margin-top: 20px;
            font-size: 13px;
            color: #64748b;
        }

        .help-text h6 {
            color: #374151;
            font-weight: 600;
            margin-bottom: 8px;
            font-size: 14px;
        }

        .help-text .example {
            background: white;
            border: 1px dashed #cbd5e1;
            border-radius: 4px;
            padding: 8px;
            margin-top: 8px;
            font-family: 'Courier New', monospace;
            color: #1e293b;
        }

        .help-text .example strong {
            color: #6366f1;
        }

        /* Checkbox */
        .modern-checkbox {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
        }

        .modern-checkbox input[type="checkbox"] {
            margin-right: 8px;
            transform: scale(1.2);
        }

        .checkbox-label {
            color: #374151;
            font-size: 14px;
            cursor: pointer;
        }

        /* Submit Button */
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
            background: linear-gradient(135deg, #9ca3af 0%, #6b7280 100%);
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

        /* Footer */
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
            text-decoration: underline;
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

        /* Form Animation */
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

        /* Additional Input Styles */
        .form-input::placeholder {
            color: #9ca3af;
            opacity: 1;
        }

        .form-input:focus::placeholder {
            color: #d1d5db;
        }

        /* Error shake animation */
        .form-input.error-shake {
            animation: shake 0.5s ease-in-out;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <!-- Left Side - Branding -->
        <div class="login-left">
            <div class="logo-section">
                <div class="logo-icon">
                    <i class="fas fa-users"></i>
                </div>
                <h1 class="logo-title">Customer Portal</h1>
                <p class="logo-subtitle">Access Your Account Information</p>
                
                <ul class="feature-list">
                    <li class="feature-item">
                        <i class="fas fa-check"></i>
                        <span>View Invoices & Payments</span>
                    </li>
                    <li class="feature-item">
                        <i class="fas fa-check"></i>
                        <span>Track Account Balance</span>
                    </li>
                    <li class="feature-item">
                        <i class="fas fa-check"></i>
                        <span>Download Statements</span>
                    </li>
                    <li class="feature-item">
                        <i class="fas fa-check"></i>
                        <span>Transaction History</span>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Right Side - Login Form -->
        <div class="login-right">
            <div class="login-header">
                <h2 class="login-title">Customer Login</h2>
                <p class="login-subtitle">Enter your credentials to access your account</p>
            </div>

            <form method="POST" action="{{ route('customer.login') }}" class="login-form" id="loginForm">
                @csrf

                <!-- Username Field -->
                <div class="form-group">
                    <label for="username" class="form-label">Username or Customer ID</label>
                    <div class="input-group">
                        <input type="text" 
                               name="username" 
                               id="username"
                               class="form-input @error('username') is-invalid @enderror"
                               value="{{ old('username') }}" 
                               placeholder="Enter customer ID (e.g., 123) or username (e.g., john123)"
                               autofocus 
                               required
                               autocomplete="username">
                        <i class="fas fa-user input-icon"></i>
                    </div>
                    @error('username')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Password Field (Phone Number) -->
                <div class="form-group">
                    <label for="password" class="form-label">Phone Number</label>
                    <div class="input-group">
                        <input type="text" 
                               name="password" 
                               id="password"
                               class="form-input @error('password') is-invalid @enderror"
                               placeholder="Enter your registered phone number"
                               required
                               autocomplete="current-password">
                        <i class="fas fa-phone input-icon"></i>
                    </div>
                    @error('password')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Help Text -->
                <div class="help-text">
                    <h6><i class="fas fa-info-circle"></i> Login Instructions:</h6>
                    <p><strong>Username:</strong> Use your customer ID (123) or firstname + ID (john123)</p>
                    <p><strong>Password:</strong> Use your registered phone number</p>
                    <div class="example">
                        <strong>Example:</strong><br>
                        Customer: John Doe (ID: 123)<br>
                        Username: <strong>123</strong> or <strong>john123</strong><br>
                        Password: <strong>01234567890</strong>
                    </div>
                </div>

                <!-- Remember Me -->
                <div class="form-group">
                    <div class="modern-checkbox">
                        <input type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                        <label for="remember" class="checkbox-label">Remember me</label>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="form-group">
                    <button type="submit" class="login-btn" id="submitBtn">
                        <i class="fas fa-sign-in-alt"></i>
                        <span>Access Account</span>
                    </button>
                </div>
                <div class="form-group">
                    <button type="button" class="login-btn" id="" onclick="window.location.href='/'">
                        <i class="fas fa-arrow-left"></i>
                        <span>Go Back To Homepage</span>
                    </button>
                </div>
            </form>

            <div class="login-footer">
                <p class="footer-text">
                    For support, contact us at 
                    <a href="tel:{{ config('app.support_phone', '+8801970-598 449') }}" class="footer-link">
                        {{ config('app.support_phone', '+8801970-598 449') }}
                    </a>
                </p>
            </div>
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
            toastr.error('{{ addslashes($error) }}');
        @endforeach
    @endif

    // Show success message if any
    @if(session('success'))
        toastr.success('{{ addslashes(session('success')) }}');
    @endif

    // Show info message if any
    @if(session('status'))
        toastr.info('{{ addslashes(session('status')) }}');
    @endif

    // Form submission handling
    $('#loginForm').submit(function(e) {
        const submitBtn = $('#submitBtn');
        const btnText = submitBtn.find('span');
        const btnIcon = submitBtn.find('i');
        
        // Basic validation
        const username = $('#username').val().trim();
        const password = $('#password').val().trim();
        
        if (!username) {
            e.preventDefault();
            toastr.error('Please enter your username or customer ID');
            $('#username').addClass('error-shake is-invalid');
            setTimeout(() => $('#username').removeClass('error-shake'), 500);
            return false;
        }
        
        if (!password || password.length < 10) {
            e.preventDefault();
            toastr.error('Please enter a valid phone number (at least 10 digits)');
            $('#password').addClass('error-shake is-invalid');
            setTimeout(() => $('#password').removeClass('error-shake'), 500);
            return false;
        }
        
        // Show loading state
        submitBtn.prop('disabled', true);
        btnIcon.removeClass('fa-sign-in-alt').addClass('fa-spinner fa-spin');
        btnText.text('Logging In...');
        
        return true;
    });

    // Input validation and cleaning
    $('#username').on('input', function() {
        let val = $(this).val();
        // Allow alphanumeric characters, dots, hyphens, underscores, and spaces
        val = val.replace(/[^a-zA-Z0-9.\-_\s]/g, '');
        $(this).val(val);
        
        // Remove invalid class on input
        $(this).removeClass('is-invalid');
    });

    $('#password').on('input', function() {
        let val = $(this).val();
        // Allow only numbers, +, -, and spaces for phone numbers
        val = val.replace(/[^0-9+\-\s]/g, '');
        $(this).val(val);
        
        // Remove invalid class on input
        $(this).removeClass('is-invalid');
        
        // Validate phone number length
        const cleanPhone = val.replace(/[^0-9]/g, '');
        if (cleanPhone.length >= 10 && cleanPhone.length <= 15) {
            $(this).removeClass('is-invalid');
        }
    });

    // Auto-focus on username field
    $('#username').focus();

    // Prevent form zoom on mobile devices
    if (/iPhone|iPad|iPod|Android/i.test(navigator.userAgent)) {
        $('input[type="text"]').on('focus', function() {
            $('meta[name="viewport"]').attr('content', 'width=device-width, initial-scale=1, maximum-scale=1');
        }).on('blur', function() {
            $('meta[name="viewport"]').attr('content', 'width=device-width, initial-scale=1');
        });
    }
});
</script>
</body>
</html>