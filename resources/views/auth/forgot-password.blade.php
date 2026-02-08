<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Password Reset - ERP System</title>
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

        .reset-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            overflow: hidden;
            width: 100%;
            max-width: 900px;
            min-height: 600px;
            display: flex;
        }

        .reset-left {
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

        .reset-left::before {
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

        .security-features {
            list-style: none;
            text-align: left;
        }

        .security-item {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
            opacity: 0.9;
        }

        .security-item i {
            margin-right: 12px;
            font-size: 16px;
        }

        .reset-right {
            flex: 1;
            padding: 60px 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .reset-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .reset-title {
            font-size: 28px;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 8px;
        }

        .reset-subtitle {
            color: #6b7280;
            font-size: 16px;
            margin-bottom: 20px;
        }

        .status-message {
            background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
            border: 2px solid #10b981;
            border-radius: 12px;
            padding: 16px;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .status-icon {
            width: 40px;
            height: 40px;
            background: #10b981;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 16px;
        }

        .status-text {
            color: #047857;
            font-weight: 500;
        }

        .reset-form {
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

        .reset-btn {
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

        .reset-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(99, 102, 241, 0.3);
        }

        .reset-btn:active {
            transform: translateY(0);
        }

        .reset-btn:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none;
        }

        .reset-footer {
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

        .info-box {
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 24px;
        }

        .info-title {
            font-weight: 600;
            color: #374151;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .info-text {
            color: #6b7280;
            font-size: 14px;
            line-height: 1.5;
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
            .reset-container {
                flex-direction: column;
                max-width: 400px;
                margin: 20px;
            }

            .reset-left {
                padding: 40px 30px;
                min-height: 300px;
            }

            .reset-right {
                padding: 40px 30px;
            }

            .logo-title {
                font-size: 24px;
            }

            .reset-title {
                font-size: 24px;
            }

            .security-features {
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

            .reset-container {
                margin: 10px;
            }

            .reset-left,
            .reset-right {
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

        @keyframes slideUp {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>
<body>
    <div class="reset-container">
        <!-- Left Side - Security Info -->
        <div class="reset-left">
            <div class="logo-section">
                <div class="logo-icon">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <h1 class="logo-title">Secure Reset</h1>
                <p class="logo-subtitle">Your account security is our priority</p>
                
                <ul class="security-features">
                    <li class="security-item">
                        <i class="fas fa-check"></i>
                        <span>Encrypted password reset links</span>
                    </li>
                    <li class="security-item">
                        <i class="fas fa-check"></i>
                        <span>Time-limited access tokens</span>
                    </li>
                    <li class="security-item">
                        <i class="fas fa-check"></i>
                        <span>Secure email verification</span>
                    </li>
                    <li class="security-item">
                        <i class="fas fa-check"></i>
                        <span>Account activity monitoring</span>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Right Side - Reset Form -->
        <div class="reset-right">
            <div class="reset-header">
                <h2 class="reset-title">Reset Password</h2>
                <p class="reset-subtitle">Enter your email address and we'll send you a secure link to reset your password</p>
            </div>

            <!-- Status Message -->
            @if (session('status'))
                <div class="status-message">
                    <div class="status-icon">
                        <i class="fas fa-check"></i>
                    </div>
                    <div class="status-text">
                        {{ session('status') }}
                    </div>
                </div>
            @endif

            <!-- Info Box -->
            <div class="info-box">
                <div class="info-title">
                    <i class="fas fa-info-circle"></i>
                    <span>How it works</span>
                </div>
                <div class="info-text">
                    We'll send a secure password reset link to your email address. Click the link to create a new password for your account.
                </div>
            </div>

            <form method="POST" action="{{ route('password.email') }}" class="reset-form" id="resetForm">
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

                <!-- Submit Button -->
                <div class="form-group">
                    <button type="submit" class="reset-btn" id="submitBtn">
                        <i class="fas fa-paper-plane"></i>
                        <span>Send Reset Link</span>
                    </button>
                </div>
            </form>

            <div class="reset-footer">
                <p class="footer-text">
                    Remember your password? 
                    <a href="{{ route('login') }}" class="footer-link">Back to login</a>
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

            // Show status message if any
            @if(session('status'))
                toastr.success('{{ session('status') }}');
            @endif

            // Form submission handling
            $('#resetForm').submit(function(e) {
                const submitBtn = $('#submitBtn');
                const btnText = submitBtn.find('span');
                const btnIcon = submitBtn.find('i');
                
                // Show loading state
                submitBtn.prop('disabled', true);
                btnIcon.removeClass('fa-paper-plane').addClass('fa-spinner fa-spin');
                btnText.text('Sending...');
                
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

            // Real-time email validation
            $('#email').on('input', function() {
                const email = $(this).val();
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                
                if (email && !emailRegex.test(email)) {
                    $(this).addClass('is-invalid');
                } else {
                    $(this).removeClass('is-invalid');
                }
            });

            // Keyboard shortcuts
            $(document).keydown(function(e) {
                // Enter key to submit form
                if (e.which === 13 && !$('#submitBtn').prop('disabled')) {
                    $('#resetForm').submit();
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
