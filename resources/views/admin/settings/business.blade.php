@extends('layouts.modern-admin')

@section('title', 'Business Settings')

@section('page_title', 'Business Settings Configuration')

@section('header_actions')
    <button type="button" class="btn modern-btn modern-btn-info" onclick="previewSettings()">
        <i class="fas fa-eye"></i> Preview
    </button>
@stop

@section('page_content')
    <!-- Success/Error Messages -->
    @if(session('success'))
        <div class="alert modern-alert modern-alert-success" id="success-alert">
            <div class="alert-content">
                <i class="fas fa-check-circle alert-icon"></i>
                <div class="alert-message">
                    <strong>Success!</strong>
                    <span>{{ session('success') }}</span>
                </div>
                <button type="button" class="alert-close" onclick="this.parentElement.parentElement.style.display='none'">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
    @endif

    @if($errors->any())
        <div class="alert modern-alert modern-alert-error" id="error-alert">
            <div class="alert-content">
                <i class="fas fa-exclamation-triangle alert-icon"></i>
                <div class="alert-message">
                    <strong>Error!</strong>
                    <span>Please check the form for errors</span>
                </div>
                <button type="button" class="alert-close" onclick="this.parentElement.parentElement.style.display='none'">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
    @endif

    <section class="settings-hero mb-4">
        <div class="settings-hero__content">
            <div>
                <p class="hero-eyebrow">Configuration</p>
                <h2 class="hero-title">Business Settings</h2>
                <p class="hero-subtitle">Tune identity, finance, system preferences, and interface styling for your company.</p>
                @if(isset($tenantList) && $tenantList)
                    <span class="hero-pill">
                        Editing: {{ $tenantList->firstWhere('id', $selectedTenantId)?->name ?? 'Selected Company' }}
                    </span>
                @endif
            </div>
            <div class="hero-stats">
                <div class="hero-stat">
                    <span class="hero-stat__label">Active Theme</span>
                    <span class="hero-stat__value">
                        {{ data_get($themes, old('theme', $settings->theme ?? 'indigo') . '.name', 'Default') }}
                    </span>
                </div>
                <div class="hero-stat">
                    <span class="hero-stat__label">Timezone</span>
                    <span class="hero-stat__value">{{ $settings->timezone ?? 'Asia/Dhaka' }}</span>
                </div>
            </div>
        </div>
        <div class="settings-hero__accent"></div>
    </section>

    @if(isset($tenantList) && $tenantList)
        <div class="card modern-card mb-4">
            <div class="card-header modern-header system-header">
                <h3 class="card-title">
                    <i class="fas fa-building"></i> Company Context
                </h3>
            </div>
            <div class="card-body modern-card-body">
                <form method="GET" action="{{ route('business-settings.index') }}" class="tenant-switch-form">
                    <div class="form-group modern-form-group">
                        <label for="tenant_id" class="modern-label">
                            Select Company
                        </label>
                        <div class="d-flex flex-wrap align-items-center gap-2">
                            <select name="tenant_id" id="tenant_id" class="form-control modern-select" style="max-width: 320px;">
                                @foreach($tenantList as $tenant)
                                    <option value="{{ $tenant->id }}" {{ (int) $selectedTenantId === (int) $tenant->id ? 'selected' : '' }}>
                                        {{ $tenant->name }}
                                    </option>
                                @endforeach
                            </select>
                            <button type="submit" class="btn modern-btn modern-btn-primary">
                                Load Settings
                            </button>
                        </div>
                        <small class="text-muted d-block mt-2">
                            You are editing settings for <strong>{{ $tenantList->firstWhere('id', $selectedTenantId)?->name ?? 'Selected Company' }}</strong>.
                        </small>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <form action="{{ route('business-settings.update') }}" method="POST" enctype="multipart/form-data" id="settingsForm">
        @csrf
        @method('PUT')
        @if(isset($tenantList) && $tenantList && !empty($selectedTenantId))
            <input type="hidden" name="tenant_id" value="{{ $selectedTenantId }}">
        @endif
        
        <!-- Business Information Row -->
        <div class="row mb-4">
            <!-- Company Details -->
            <div class="col-md-6">
                <div class="card modern-card">
                    <div class="card-header modern-header company-header">
                        <h3 class="card-title">
                            <i class="fas fa-building"></i> Company Information
                        </h3>
                    </div>
                    <div class="card-body modern-card-body">
                        <div class="form-group modern-form-group">
                            <label for="business_name" class="modern-label">
                                Business Name <span class="required">*</span>
                            </label>
                            <input type="text" name="business_name" id="business_name"
                                   class="form-control modern-input @error('business_name') is-invalid @enderror" 
                                   value="{{ old('business_name', $settings->business_name) }}" required>
                            @error('business_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="form-group modern-form-group">
                            <label for="email" class="modern-label">
                                Email Address
                            </label>
                            <input type="email" name="email" id="email"
                                   class="form-control modern-input @error('email') is-invalid @enderror" 
                                   value="{{ old('email', $settings->email) }}">
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="form-group modern-form-group">
                            <label for="phone" class="modern-label">
                                Phone Number <span class="required">*</span>
                            </label>
                            <input type="text" name="phone" id="phone"
                                   class="form-control modern-input @error('phone') is-invalid @enderror" 
                                   value="{{ old('phone', $settings->phone) }}" required>
                            @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="form-group modern-form-group">
                            <label for="bin_number" class="modern-label">
                                BIN Number
                            </label>
                            <input type="text" name="bin_number" id="bin_number"
                                   class="form-control modern-input @error('bin_number') is-invalid @enderror" 
                                   value="{{ old('bin_number', $settings->bin_number) }}">
                            @error('bin_number')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="form-group modern-form-group">
                            <label for="address" class="modern-label">
                                Address
                            </label>
                            <textarea name="address" id="address"
                                      class="form-control modern-textarea @error('address') is-invalid @enderror" 
                                      rows="3">{{ old('address', $settings->address) }}</textarea>
                            @error('address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Logo and Branding -->
            <div class="col-md-6">
                <div class="card modern-card">
                    <div class="card-header modern-header branding-header">
                        <h3 class="card-title">
                            <i class="fas fa-image"></i> Logo & Branding
                        </h3>
                    </div>
                    <div class="card-body modern-card-body">
                        <div class="form-group modern-form-group">
                            <label for="logo" class="modern-label">Company Logo</label>
                            
                            @if($settings->logo)
                                <div class="current-logo-container mb-3">
                                    <div class="current-logo-preview">
                                        <img src="{{ Storage::url($settings->logo) }}" alt="Current Logo" class="current-logo">
                                        <div class="logo-overlay">
                                            <span class="logo-text">Current Logo</span>
                                        </div>
                                    </div>
                                </div>
                            @endif
                            
                            <div class="modern-file-upload">
                                <div class="file-upload-container">
                                    <input type="file" name="logo" id="logo" class="file-input" accept="image/*">
                                    <label for="logo" class="file-upload-label">
                                        <div class="file-upload-content">
                                            <i class="fas fa-cloud-upload-alt file-upload-icon"></i>
                                            <span class="file-upload-text">Choose Logo File</span>
                                            <span class="file-upload-subtext">or drag and drop</span>
                                        </div>
                                    </label>
                                </div>
                                <div class="file-upload-info">
                                    <small class="text-muted">Recommended size: 200x200px. Supported formats: JPG, PNG, SVG</small>
                                </div>
                            </div>
                            @error('logo')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                        
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Financial Information -->
        <div class="card modern-card mb-4">
            <div class="card-header modern-header financial-header">
                <h3 class="card-title">
                    <i class="fas fa-university"></i> Financial Information
                </h3>
            </div>
            <div class="card-body modern-card-body">
                <div class="row">
                    <div class="col-lg-7">
                        <div class="form-group modern-form-group">
                            <label for="bank_details" class="modern-label">
                                Bank Details
                            </label>
                            <textarea name="bank_details" id="bank_details"
                                      class="form-control modern-textarea @error('bank_details') is-invalid @enderror"
                                      rows="4" placeholder="Enter bank account details, routing numbers, etc.">{{ old('bank_details', $settings->bank_details) }}</textarea>
                            @error('bank_details')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-lg-5">
                        <div class="form-group modern-form-group">
                            <label for="footer_message" class="modern-label">
                                Footer Message
                            </label>
                            <textarea name="footer_message" id="footer_message"
                                      class="form-control modern-textarea @error('footer_message') is-invalid @enderror"
                                      rows="4">{{ old('footer_message', $settings->footer_message) }}</textarea>
                            @error('footer_message')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted d-block mt-2">
                                Shows on invoices and printouts.
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Policy Settings -->
        <div class="card modern-card mb-4">
            <div class="card-header modern-header policy-header">
                <h3 class="card-title">
                    <i class="fas fa-shield-alt"></i> Return Policy Settings
                </h3>
            </div>
            <div class="card-body modern-card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group modern-form-group">
                            <label for="return_policy_days" class="modern-label">
                                Return Policy Days
                            </label>
                            <div class="input-group modern-input-group">
                                <input type="number" name="return_policy_days" id="return_policy_days"
                                       class="form-control modern-input @error('return_policy_days') is-invalid @enderror" 
                                       value="{{ old('return_policy_days', $settings->return_policy_days) }}" min="0">
                                <div class="input-group-append">
                                    <span class="input-group-text modern-input-addon">days</span>
                                </div>
                            </div>
                            @error('return_policy_days')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group modern-form-group">
                            <label for="return_policy_message" class="modern-label">
                                Return Policy Message
                            </label>
                            <input type="text" name="return_policy_message" id="return_policy_message"
                                   class="form-control modern-input @error('return_policy_message') is-invalid @enderror" 
                                   value="{{ old('return_policy_message', $settings->return_policy_message) }}"
                                   placeholder="e.g., Items can be returned within specified days">
                            @error('return_policy_message')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- System Settings -->
        <div class="card modern-card mb-4">
            <div class="card-header modern-header system-header">
                <h3 class="card-title">
                    <i class="fas fa-cogs"></i> System Settings
                </h3>
            </div>
            <div class="card-body modern-card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group modern-form-group">
                            <label for="timezone" class="modern-label">
                                <i class="fas fa-globe text-primary"></i> Timezone
                            </label>
                            <select name="timezone" id="timezone"
                                    class="form-control modern-select @error('timezone') is-invalid @enderror">
                                @foreach($timezones as $region => $zones)
                                    <optgroup label="{{ $region }}">
                                        @foreach($zones as $tz => $label)
                                            <option value="{{ $tz }}" {{ old('timezone', $settings->timezone ?? 'Asia/Dhaka') == $tz ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </optgroup>
                                @endforeach
                            </select>
                            @error('timezone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted mt-1 d-block">
                                <i class="fas fa-info-circle"></i>
                                Current server time: <strong id="current-server-time">{{ now()->format('Y-m-d h:i:s A') }}</strong>
                            </small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group modern-form-group">
                            <label class="modern-label">
                                <i class="fas fa-clock text-info"></i> Time Preview
                            </label>
                            <div class="timezone-preview-box">
                                <div class="preview-time" id="preview-time">
                                    <span class="time-display">--:--:--</span>
                                    <span class="date-display">----/--/--</span>
                                </div>
                                <div class="preview-label">Selected timezone time</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group modern-form-group">
                            <label for="customer_qr_expiry_days" class="modern-label">
                                <i class="fas fa-qrcode text-success"></i> Customer QR Expiry (days)
                            </label>
                            <div class="input-group modern-input-group">
                                <input type="number" name="customer_qr_expiry_days" id="customer_qr_expiry_days"
                                       class="form-control modern-input @error('customer_qr_expiry_days') is-invalid @enderror"
                                       value="{{ old('customer_qr_expiry_days', $settings->customer_qr_expiry_days ?? 30) }}" min="1" max="3650">
                                <div class="input-group-append">
                                    <span class="input-group-text modern-input-addon">days</span>
                                </div>
                            </div>
                            @error('customer_qr_expiry_days')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted mt-1 d-block">
                                <i class="fas fa-info-circle"></i>
                                Controls how long invoice QR login links remain valid.
                            </small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group modern-form-group">
                            <label class="modern-label">
                                <i class="fas fa-calendar-week text-warning"></i> Weekend Days
                            </label>
                            <div class="weekend-grid">
                                @php
                                    $weekendDays = old('weekend_days', $settings->weekend_days ?? ['Friday']);
                                @endphp
                                @foreach(['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'] as $day)
                                    <label class="weekend-pill">
                                        <input type="checkbox" name="weekend_days[]" value="{{ $day }}"
                                               {{ in_array($day, $weekendDays) ? 'checked' : '' }}>
                                        <span>{{ $day }}</span>
                                    </label>
                                @endforeach
                            </div>
                            @error('weekend_days')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Theme & Appearance -->
        <div class="card modern-card mb-4">
            <div class="card-header modern-header theme-header">
                <h3 class="card-title">
                    <i class="fas fa-palette"></i> Theme & Appearance
                </h3>
            </div>
            <div class="card-body modern-card-body">
                @php
                    $currentThemeKey = old('theme', $settings->theme ?? 'indigo');
                    $currentThemeName = data_get($themes, $currentThemeKey . '.name', 'Default');
                    $gradientThemes = [];
                    $solidThemes = [];
                    foreach ($themes as $key => $theme) {
                        $style = $theme['style'] ?? 'gradient';
                        if ($style === 'solid') {
                            $solidThemes[$key] = $theme;
                        } else {
                            $gradientThemes[$key] = $theme;
                        }
                    }
                @endphp

                <div class="theme-intro">
                    <div>
                        <p class="text-muted mb-1">
                            Choose a theme to style the entire application interface.
                        </p>
                        <small class="text-muted">Solid colors are lightweight and ideal for faster rendering.</small>
                    </div>
                    <div class="theme-active-badge">
                        <i class="fas fa-check"></i> Active: <span id="active-theme-name">{{ $currentThemeName }}</span>
                    </div>
                </div>

                <div class="theme-section">
                    <div class="theme-section__header">
                        <h6>Gradient Themes</h6>
                        <span class="theme-section__meta">{{ count($gradientThemes) }} options</span>
                    </div>
                    <div class="theme-grid">
                        @forelse($gradientThemes as $key => $theme)
                            <label class="theme-card" data-style="{{ $theme['style'] ?? 'gradient' }}" data-theme-name="{{ $theme['name'] }}">
                                <input type="radio" name="theme" value="{{ $key }}"
                                       {{ $currentThemeKey === $key ? 'checked' : '' }}>
                                <div class="theme-card__visual"
                                     style="--theme-primary: {{ $theme['primary'] }};
                                            --theme-accent: {{ $theme['accent'] }};
                                            --theme-bg: {{ $theme['bg'] }};">
                                    <span class="theme-dot primary"></span>
                                    <span class="theme-dot accent"></span>
                                    <span class="theme-swatch"></span>
                                </div>
                                <div class="theme-card__content">
                                    <div class="theme-name">{{ $theme['name'] }}</div>
                                    <div class="theme-meta">{{ strtoupper($key) }}</div>
                                </div>
                            </label>
                        @empty
                            <div class="text-muted">No gradient themes available.</div>
                        @endforelse
                    </div>
                </div>

                <div class="theme-section">
                    <div class="theme-section__header">
                        <h6>Solid Colors</h6>
                        <span class="theme-section__meta">{{ count($solidThemes) }} options</span>
                    </div>
                    <div class="theme-grid theme-grid--compact">
                        @forelse($solidThemes as $key => $theme)
                            <label class="theme-card" data-style="{{ $theme['style'] ?? 'solid' }}" data-theme-name="{{ $theme['name'] }}">
                                <input type="radio" name="theme" value="{{ $key }}"
                                       {{ $currentThemeKey === $key ? 'checked' : '' }}>
                                <div class="theme-card__visual"
                                     style="--theme-primary: {{ $theme['primary'] }};
                                            --theme-accent: {{ $theme['accent'] }};
                                            --theme-bg: {{ $theme['bg'] }};">
                                    <span class="theme-dot primary"></span>
                                    <span class="theme-dot accent"></span>
                                    <span class="theme-swatch"></span>
                                </div>
                                <div class="theme-card__content">
                                    <div class="theme-name">{{ $theme['name'] }}</div>
                                    <div class="theme-meta">{{ strtoupper($key) }}</div>
                                </div>
                            </label>
                        @empty
                            <div class="text-muted">No solid themes available.</div>
                        @endforelse
                    </div>
                </div>
                @error('theme')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <!-- Invoice Template Studio -->
        <div class="card modern-card mb-4">
            <div class="card-header modern-header invoice-header">
                <h3 class="card-title">
                    <i class="fas fa-file-invoice"></i> Invoice Template Studio
                </h3>
            </div>
            <div class="card-body modern-card-body">
                <ul class="nav nav-tabs invoice-tabs" id="invoice-template-tabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="invoice-layout-tab" data-toggle="tab" href="#invoice-layout-pane" role="tab" aria-controls="invoice-layout-pane" aria-selected="true">
                            Layout & Visibility
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="invoice-preview-tab" data-toggle="tab" href="#invoice-preview-pane" role="tab" aria-controls="invoice-preview-pane" aria-selected="false">
                            Template Preview
                        </a>
                    </li>
                </ul>

                <div class="tab-content invoice-tab-content" id="invoice-template-tab-content">
                    <div class="tab-pane fade show active" id="invoice-layout-pane" role="tabpanel" aria-labelledby="invoice-layout-tab">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group modern-form-group">
                                    <label for="invoice_template" class="modern-label">Default Invoice Template</label>
                                    <select name="invoice_template" id="invoice_template" class="form-control modern-select">
                                        @foreach($invoiceTemplates as $key => $label)
                                            <option value="{{ $key }}" {{ old('invoice_template', $settings->invoice_template ?? 'standard') === $key ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <small class="text-muted d-block mt-2">
                                        This becomes the default print style for invoice, challan, ledger, returns, and other printable pages.
                                    </small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group modern-form-group">
                                    <label for="invoice_phone_override" class="modern-label">Invoice Phone Override (Optional)</label>
                                    <input type="text"
                                           name="invoice_print_options[invoice_phone_override]"
                                           id="invoice_phone_override"
                                           class="form-control modern-input"
                                           value="{{ old('invoice_print_options.invoice_phone_override', $invoicePrintOptions['invoice_phone_override'] ?? '') }}"
                                           placeholder="Leave blank to use business phone">
                                    <small class="text-muted d-block mt-2">
                                        Only changes the phone shown on invoice prints. Main business phone stays unchanged.
                                    </small>
                                </div>
                            </div>
                        </div>

                        @php
                            $invoiceToggleItems = [
                                'show_company_phone' => ['Company Phone', 'fas fa-phone'],
                                'show_company_email' => ['Company Email', 'fas fa-envelope'],
                                'show_company_address' => ['Company Address', 'fas fa-map-marker-alt'],
                                'show_company_bin' => ['Company BIN', 'fas fa-id-card'],
                                'show_bank_details' => ['Bank Details', 'fas fa-university'],
                                'show_terms' => ['Terms & Conditions', 'fas fa-file-contract'],
                                'show_footer_message' => ['Footer Message', 'fas fa-quote-right'],
                                'show_customer_qr' => ['Customer QR Block', 'fas fa-qrcode'],
                                'show_signatures' => ['Signature Section', 'fas fa-signature'],
                            ];
                            $truthyValues = [1, '1', true, 'true', 'on'];
                        @endphp

                        <div class="row">
                            @foreach($invoiceToggleItems as $optionKey => [$optionLabel, $optionIcon])
                                @php
                                    $optionValue = old("invoice_print_options.$optionKey", data_get($invoicePrintOptions, $optionKey, true));
                                    $isChecked = in_array($optionValue, $truthyValues, true);
                                @endphp
                                <div class="col-lg-4 col-md-6">
                                    <div class="invoice-toggle-item">
                                        <input type="hidden" name="invoice_print_options[{{ $optionKey }}]" value="0">
                                        <div class="custom-control custom-switch">
                                            <input type="checkbox"
                                                   class="custom-control-input invoice-toggle"
                                                   id="{{ $optionKey }}"
                                                   name="invoice_print_options[{{ $optionKey }}]"
                                                   value="1"
                                                   {{ $isChecked ? 'checked' : '' }}>
                                            <label class="custom-control-label" for="{{ $optionKey }}">
                                                <i class="{{ $optionIcon }}"></i> {{ $optionLabel }}
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="tab-pane fade" id="invoice-preview-pane" role="tabpanel" aria-labelledby="invoice-preview-tab">
                        @if($previewInvoiceId)
                            <div class="alert modern-alert modern-alert-info mb-3">
                                <div class="alert-content">
                                    <i class="fas fa-info-circle alert-icon"></i>
                                    <div class="alert-message">
                                        <strong>Preview Ready</strong>
                                        <span>Templates open using your latest invoice in this tenant. Save to apply default system-wide.</span>
                                    </div>
                                </div>
                            </div>
                            <div class="template-preview-grid">
                                @foreach($invoiceTemplates as $key => $label)
                                    <button type="button" class="btn modern-btn modern-btn-secondary invoice-preview-btn" data-template="{{ $key }}">
                                        <i class="fas fa-eye"></i> Preview {{ $label }}
                                    </button>
                                @endforeach
                            </div>
                            <div class="mt-3">
                                <button type="button" class="btn modern-btn modern-btn-primary" id="preview-current-template">
                                    <i class="fas fa-external-link-alt"></i> Preview Current Selection
                                </button>
                            </div>
                        @else
                            <div class="alert modern-alert modern-alert-error">
                                <div class="alert-content">
                                    <i class="fas fa-exclamation-triangle alert-icon"></i>
                                    <div class="alert-message">
                                        <strong>No Invoice Found</strong>
                                        <span>Create at least one invoice to use live template preview.</span>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Submit Buttons -->
        <div class="text-center mt-4 mb-4">
            <button type="submit" class="btn modern-btn modern-btn-primary btn-lg" id="submit-btn">
                <i class="fas fa-save"></i> Save Settings
            </button>
            <button type="button" class="btn modern-btn modern-btn-secondary btn-lg ml-3" onclick="resetForm()">
                <i class="fas fa-undo"></i> Reset
            </button>
        </div>
    </form>

    <!-- Preview Modal -->
    <div class="modal fade" id="previewModal" tabindex="-1" role="dialog" aria-labelledby="previewModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content modern-modal">
                <div class="modal-header modern-modal-header">
                    <h5 class="modal-title" id="previewModalLabel">
                        <i class="fas fa-eye"></i> Settings Preview
                    </h5>
                    <button type="button" class="close modern-close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body modern-modal-body">
                    <div class="preview-content">
                        <div class="preview-section">
                            <h6 class="preview-title">Company Information</h6>
                            <div class="preview-item">
                                <strong>Business Name:</strong> <span id="preview-business-name">-</span>
                            </div>
                            <div class="preview-item">
                                <strong>Email:</strong> <span id="preview-email">-</span>
                            </div>
                            <div class="preview-item">
                                <strong>Phone:</strong> <span id="preview-phone">-</span>
                            </div>
                            <div class="preview-item">
                                <strong>Address:</strong> <span id="preview-address">-</span>
                            </div>
                        </div>
                        
                        <div class="preview-section">
                            <h6 class="preview-title">Return Policy</h6>
                            <div class="preview-item">
                                <strong>Days:</strong> <span id="preview-return-days">-</span>
                            </div>
                            <div class="preview-item">
                                <strong>Message:</strong> <span id="preview-return-message">-</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer modern-modal-footer">
                    <button type="button" class="btn modern-btn modern-btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times"></i> Close
                    </button>
                </div>
            </div>
        </div>
    </div>
@stop

@section('additional_css')
    <style>
        body,
        .content-wrapper {
            overflow-x: hidden;
        }

        .settings-hero,
        .modern-card,
        .theme-grid,
        .theme-card,
        .file-upload-container {
            max-width: 100%;
        }

        .settings-hero {
            background: linear-gradient(135deg, var(--app-primary, #1d4ed8) 0%, var(--app-accent, #60a5fa) 100%);
            border-radius: 20px;
            color: #e0f2fe;
            padding: 24px 28px;
            position: relative;
            overflow: hidden;
            box-shadow: 0 18px 45px rgba(15, 23, 42, 0.25);
        }

        .settings-hero__content {
            display: flex;
            flex-wrap: wrap;
            gap: 24px;
            align-items: center;
            justify-content: space-between;
            position: relative;
            z-index: 1;
        }

        .settings-hero__accent {
            position: absolute;
            inset: -40% -10% auto auto;
            width: 220px;
            height: 220px;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.35), rgba(255, 255, 255, 0));
            filter: blur(0);
        }

        .hero-eyebrow {
            text-transform: uppercase;
            letter-spacing: 0.2em;
            font-size: 12px;
            margin-bottom: 8px;
            color: rgba(226, 232, 240, 0.75);
        }

        .hero-title {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 6px;
            color: #ffffff;
        }

        .hero-subtitle {
            margin: 0;
            max-width: 520px;
            color: rgba(226, 232, 240, 0.9);
        }

        .hero-pill {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 6px 12px;
            border-radius: 999px;
            background: rgba(15, 23, 42, 0.35);
            color: #e2e8f0;
            font-size: 12px;
            margin-top: 12px;
        }

        .hero-stats {
            display: grid;
            gap: 12px;
            background: rgba(15, 23, 42, 0.25);
            border-radius: 14px;
            padding: 14px 18px;
            min-width: 220px;
        }

        .hero-stat {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .hero-stat__label {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.18em;
            color: rgba(226, 232, 240, 0.7);
        }

        .hero-stat__value {
            font-size: 14px;
            font-weight: 600;
            color: #ffffff;
        }

        /* Modern Form Enhancements */
        .modern-form-group {
            margin-bottom: 24px;
        }

        .modern-label {
            font-weight: 600;
            color: #374151;
            margin-bottom: 8px;
            font-size: 14px;
        }

        .modern-input, .modern-textarea {
            border: 2px solid #e5e7eb;
            border-radius: 10px;
            padding: 12px 16px;
            font-size: 14px;
            transition: all 0.2s ease;
            background: white;
            color: #374151;
        }

        .modern-input:focus, .modern-textarea:focus {
            outline: none;
            border-color: #6366f1;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
            transform: translateY(-1px);
        }

        /* Modern Input Group */
        .modern-input-group {
            display: flex;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .modern-input-group .modern-input {
            border-radius: 0;
            border-right: none;
            margin: 0;
        }

        .modern-input-addon {
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            border: 2px solid #e5e7eb;
            border-left: none;
            color: #6b7280;
            font-weight: 600;
            font-size: 14px;
            padding: 12px 16px;
            display: flex;
            align-items: center;
        }

        /* Section-specific header colors */
        .company-header {
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
        }

        .branding-header {
            background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);
        }

        .financial-header {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        }

        .policy-header {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        }

        .system-header {
            background: linear-gradient(135deg, #8b5cf6 0%, #6d28d9 100%);
        }

        .theme-header {
            background: linear-gradient(135deg, #1d4ed8 0%, #3b82f6 100%);
        }

        .invoice-header {
            background: linear-gradient(135deg, #0f766e 0%, #0ea5a4 100%);
        }

        .invoice-tabs {
            border-bottom: 1px solid #e5e7eb;
            margin-bottom: 16px;
        }

        .invoice-tabs .nav-link {
            border: none;
            color: #6b7280;
            font-weight: 600;
            border-radius: 10px 10px 0 0;
            padding: 10px 14px;
        }

        .invoice-tabs .nav-link.active {
            color: #0f766e;
            background: rgba(15, 118, 110, 0.08);
        }

        .invoice-tab-content {
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 18px;
            background: #ffffff;
        }

        .invoice-toggle-item {
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            padding: 10px 12px;
            margin-bottom: 12px;
            background: #f8fafc;
        }

        .invoice-toggle-item .custom-control-label {
            font-weight: 600;
            color: #374151;
        }

        .invoice-toggle-item .custom-control-label i {
            color: #0f766e;
            margin-right: 6px;
        }

        .template-preview-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 10px;
        }

        /* Modern Select */
        .modern-select {
            border: 2px solid #e5e7eb;
            border-radius: 10px;
            padding: 10px 16px;
            font-size: 14px;
            line-height: 1.4;
            min-height: 44px;
            transition: all 0.2s ease;
            background: white;
            color: #374151;
            cursor: pointer;
        }

        .modern-select:focus {
            outline: none;
            border-color: #6366f1;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
        }

        .modern-select option {
            line-height: 1.4;
        }

        .modern-select optgroup {
            font-weight: 600;
            color: #374151;
            background: #f8fafc;
        }

        .modern-select option {
            padding: 8px;
            font-weight: 400;
        }

        /* Timezone Preview Box */
        .timezone-preview-box {
            background: linear-gradient(135deg, #1e1b4b 0%, #312e81 100%);
            border-radius: 12px;
            padding: 24px;
            text-align: center;
            box-shadow: 0 4px 15px rgba(99, 102, 241, 0.3);
        }

        .preview-time {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 8px;
        }

        .time-display {
            font-size: 36px;
            font-weight: 700;
            color: #ffffff;
            font-family: 'Courier New', monospace;
            letter-spacing: 2px;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
            font-variant-numeric: tabular-nums;
        }

        .date-display {
            font-size: 16px;
            font-weight: 500;
            color: #a5b4fc;
            font-family: 'Courier New', monospace;
        }

        /* Theme Selection */
        .theme-intro {
            display: flex;
            flex-wrap: wrap;
            gap: 16px;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
        }

        .theme-active-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 6px 12px;
            border-radius: 999px;
            background: #eff6ff;
            color: #1d4ed8;
            font-weight: 600;
            font-size: 12px;
        }

        .theme-section {
            padding: 16px 0 12px;
            border-top: 1px solid #e5e7eb;
        }

        .theme-section:first-of-type {
            border-top: none;
            padding-top: 0;
        }

        .theme-section__header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 14px;
        }

        .theme-section__header h6 {
            margin: 0;
            font-weight: 600;
            color: #111827;
        }

        .theme-section__meta {
            font-size: 12px;
            color: #6b7280;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        .theme-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
        }

        .theme-grid--compact {
            grid-template-columns: repeat(auto-fit, minmax(170px, 1fr));
        }

        .theme-card {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 14px 16px;
            border-radius: 14px;
            border: 2px solid #e5e7eb;
            background: #ffffff;
            cursor: pointer;
            transition: all 0.2s ease;
            position: relative;
        }

        .theme-card input[type="radio"] {
            position: absolute;
            opacity: 0;
            pointer-events: none;
        }

        .theme-card__visual {
            height: 44px;
            width: 44px;
            border-radius: 14px;
            background: var(--theme-bg);
            display: grid;
            place-items: center;
            position: relative;
            border: 1px solid rgba(0, 0, 0, 0.05);
        }

        .theme-card[data-style="solid"] .theme-card__visual {
            background: var(--theme-primary);
        }

        .theme-card[data-style="solid"] .theme-dot {
            display: none;
        }

        .theme-card[data-style="solid"] .theme-swatch {
            background: var(--theme-primary);
        }

        .theme-dot {
            height: 10px;
            width: 10px;
            border-radius: 999px;
            position: absolute;
            left: 10px;
        }

        .theme-dot.primary {
            top: 10px;
            background: var(--theme-primary);
        }

        .theme-dot.accent {
            top: 24px;
            background: var(--theme-accent);
        }

        .theme-swatch {
            height: 18px;
            width: 18px;
            border-radius: 6px;
            background: linear-gradient(135deg, var(--theme-primary), var(--theme-accent));
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.12);
        }

        .theme-card__content {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .theme-name {
            font-weight: 600;
            color: #111827;
        }

        .theme-meta {
            font-size: 12px;
            color: #6b7280;
            letter-spacing: 0.06em;
        }

        .theme-card:hover {
            border-color: #c7d2fe;
            transform: translateY(-2px);
            box-shadow: 0 12px 25px rgba(15, 23, 42, 0.12);
        }

        .theme-card.is-selected {
            border-color: #6366f1;
            box-shadow: 0 10px 24px rgba(79, 70, 229, 0.18);
        }

        .weekend-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .weekend-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 12px;
            border-radius: 999px;
            border: 1px solid #e5e7eb;
            background: #f8fafc;
            font-size: 13px;
            cursor: pointer;
        }

        .weekend-pill input {
            accent-color: #6366f1;
        }

        .preview-label {
            margin-top: 12px;
            font-size: 12px;
            color: #c7d2fe;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        /* Modern File Upload */
        .modern-file-upload {
            position: relative;
        }

        .file-upload-container {
            position: relative;
            border: 2px dashed #d1d5db;
            border-radius: 12px;
            padding: 40px 20px;
            text-align: center;
            transition: all 0.3s ease;
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
        }

        .file-upload-container:hover {
            border-color: #6366f1;
            background: linear-gradient(135deg, rgba(99, 102, 241, 0.05) 0%, rgba(139, 92, 246, 0.02) 100%);
        }

        .file-upload-container.drag-over {
            border-color: #60a5fa;
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.08) 0%, rgba(14, 165, 233, 0.04) 100%);
        }

        .file-input {
            position: absolute;
            opacity: 0;
            width: 100%;
            height: 100%;
            cursor: pointer;
        }

        .file-upload-label {
            cursor: pointer;
            margin: 0;
        }

        .file-upload-content {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 8px;
        }

        .file-upload-icon {
            font-size: 32px;
            color: #6366f1;
            margin-bottom: 8px;
        }

        .file-upload-text {
            font-size: 16px;
            font-weight: 600;
            color: #374151;
        }

        .file-upload-subtext {
            font-size: 14px;
            color: #6b7280;
        }

        .file-upload-info {
            margin-top: 12px;
            text-align: center;
        }

        /* Current Logo Display */
        .current-logo-container {
            display: flex;
            justify-content: center;
        }

        .current-logo-preview {
            position: relative;
            display: inline-block;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }

        .current-logo {
            max-height: 120px;
            max-width: 200px;
            display: block;
        }

        .logo-overlay {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(to top, rgba(0, 0, 0, 0.7), transparent);
            color: white;
            padding: 8px 12px;
            text-align: center;
        }

        .logo-text {
            font-size: 12px;
            font-weight: 600;
        }

        /* Modern Modal */
        .modern-modal {
            border: none;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        }

        .modern-modal-header {
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
            color: white;
            border-bottom: none;
            padding: 20px 24px;
        }

        .modern-modal-header .modal-title {
            font-weight: 600;
            font-size: 18px;
        }

        .modern-close {
            color: white;
            opacity: 0.8;
            font-size: 24px;
        }

        .modern-close:hover {
            color: white;
            opacity: 1;
        }

        .modern-modal-body {
            padding: 24px;
        }

        .modern-modal-footer {
            padding: 20px 24px;
            border-top: 1px solid #f1f5f9;
            background: #f8fafc;
        }

        /* Preview Content */
        .preview-content {
            display: flex;
            flex-direction: column;
            gap: 24px;
        }

        .preview-section {
            background: #f8fafc;
            border-radius: 8px;
            padding: 16px;
        }

        .preview-title {
            font-weight: 600;
            color: #374151;
            margin-bottom: 12px;
            font-size: 16px;
            border-bottom: 2px solid #e5e7eb;
            padding-bottom: 8px;
        }

        .preview-item {
            margin-bottom: 8px;
            font-size: 14px;
            color: #6b7280;
        }

        .preview-item strong {
            color: #374151;
            min-width: 120px;
            display: inline-block;
        }

        /* Form validation styles */
        .is-invalid {
            border-color: #ef4444 !important;
            box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1) !important;
        }

        .invalid-feedback {
            color: #ef4444;
            font-size: 12px;
            margin-top: 4px;
        }

        /* Required field indicator */
        .required {
            color: #ef4444;
            font-weight: 600;
        }

        /* Loading states */
        .loading {
            opacity: 0.7;
            pointer-events: none;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .settings-hero {
                padding: 20px 22px;
            }

            .hero-title {
                font-size: 22px;
            }

            .modern-form-group {
                margin-bottom: 20px;
            }

            .modern-input, .modern-textarea {
                padding: 10px 14px;
                font-size: 16px;
            }

            .file-upload-container {
                padding: 30px 15px;
            }

            .file-upload-icon {
                font-size: 24px;
            }

            .file-upload-text {
                font-size: 14px;
            }

            .current-logo {
                max-height: 80px;
                max-width: 150px;
            }

            .preview-content {
                gap: 16px;
            }

            .preview-section {
                padding: 12px;
            }
        }
    </style>
@stop

@section('additional_js')
    <script>
        $(document).ready(function() {
            // Auto-hide alerts after 5 seconds
            setTimeout(function() {
                $('.modern-alert').fadeOut();
            }, 5000);
            
            // File upload handling
            $('#logo').on('change', function() {
                const file = this.files[0];
                if (file) {
                    const fileName = file.name;
                    $('.file-upload-text').text(fileName);
                    $('.file-upload-subtext').text('File selected');
                } else {
                    $('.file-upload-text').text('Choose Logo File');
                    $('.file-upload-subtext').text('or drag and drop');
                }
            });
            
            // Drag and drop functionality
            const fileUploadContainer = $('.file-upload-container');
            
            fileUploadContainer.on('dragover', function(e) {
                e.preventDefault();
                $(this).addClass('drag-over');
            });
            
            fileUploadContainer.on('dragleave', function(e) {
                e.preventDefault();
                $(this).removeClass('drag-over');
            });
            
            fileUploadContainer.on('drop', function(e) {
                e.preventDefault();
                $(this).removeClass('drag-over');
                
                const files = e.originalEvent.dataTransfer.files;
                if (files.length > 0) {
                    $('#logo')[0].files = files;
                    $('#logo').trigger('change');
                }
            });
            
            // Form submission
            $('#settingsForm').submit(function(e) {
                // Show loading state
                const submitBtn = $('#submit-btn');
                const originalText = submitBtn.html();
                submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving Settings...');
                
                // Let the form submit normally
                return true;
            });
            
            // Preview settings function
            window.previewSettings = function() {
                $('#preview-business-name').text($('#business_name').val() || '-');
                $('#preview-email').text($('#email').val() || '-');
                $('#preview-phone').text($('#phone').val() || '-');
                $('#preview-address').text($('#address').val() || '-');
                $('#preview-return-days').text($('#return_policy_days').val() || '-');
                $('#preview-return-message').text($('#return_policy_message').val() || '-');
                
                $('#previewModal').modal('show');
            };
            
            // Reset form function
            window.resetForm = function() {
                if (confirm('Are you sure you want to reset all changes?')) {
                    $('#settingsForm')[0].reset();
                    $('.file-upload-text').text('Choose Logo File');
                    $('.file-upload-subtext').text('or drag and drop');
                }
            };
            
            // Real-time validation
            $('input, textarea').on('blur', function() {
                $(this).removeClass('is-invalid');
            });
            
            // Phone number formatting (optional)
            $('#phone').on('input', function() {
                let value = $(this).val().replace(/\D/g, '');
                if (value.length >= 10) {
                    value = value.replace(/(\d{3})(\d{3})(\d{4})/, '$1-$2-$3');
                }
                $(this).val(value);
            });

            // Timezone preview functionality
            function updateTimezonePreview() {
                const timezone = $('#timezone').val();
                if (!timezone) return;

                try {
                    const now = new Date();
                    const options = {
                        timeZone: timezone,
                        hour: '2-digit',
                        minute: '2-digit',
                        second: '2-digit',
                        hour12: true
                    };
                    const dateOptions = {
                        timeZone: timezone,
                        year: 'numeric',
                        month: '2-digit',
                        day: '2-digit'
                    };

                    const timeStr = now.toLocaleTimeString('en-US', options);
                    const dateStr = now.toLocaleDateString('en-US', dateOptions);

                    $('.time-display').text(timeStr);
                    $('.date-display').text(dateStr);
                } catch (e) {
                    console.error('Invalid timezone:', timezone);
                    $('.time-display').text('--:--:--');
                    $('.date-display').text('----/--/--');
                }
            }

            // Update preview when timezone changes
            $('#timezone').on('change', function() {
                updateTimezonePreview();
            });

            // Update time preview every 10 seconds
            setInterval(updateTimezonePreview, 10000);

            // Initial update
            updateTimezonePreview();

            function syncThemeSelection() {
                const selected = $('input[name="theme"]:checked').closest('.theme-card');
                $('.theme-card').removeClass('is-selected');
                selected.addClass('is-selected');
                const themeName = selected.data('theme-name');
                if (themeName) {
                    $('#active-theme-name').text(themeName);
                }
            }

            $('input[name="theme"]').on('change', syncThemeSelection);
            syncThemeSelection();

            const previewBaseUrl = @json($previewInvoiceId ? route('invoices.print', ['invoice' => $previewInvoiceId]) : null);
            const invoicePreviewToggleKeys = [
                'show_company_phone',
                'show_company_email',
                'show_company_address',
                'show_company_bin',
                'show_bank_details',
                'show_terms',
                'show_footer_message',
                'show_customer_qr',
                'show_signatures'
            ];

            function buildInvoicePreviewUrl(templateOverride) {
                if (!previewBaseUrl) {
                    return null;
                }

                const params = new URLSearchParams();
                params.set('preview', '1');
                params.set('template', templateOverride || $('#invoice_template').val() || 'standard');

                invoicePreviewToggleKeys.forEach((key) => {
                    const checkbox = document.getElementById(key);
                    params.set(key, checkbox && checkbox.checked ? '1' : '0');
                });

                const phoneOverride = ($('#invoice_phone_override').val() || '').trim();
                if (phoneOverride.length > 0) {
                    params.set('invoice_phone_override', phoneOverride);
                }

                return `${previewBaseUrl}?${params.toString()}`;
            }

            window.previewInvoiceTemplate = function(templateOverride = null) {
                const url = buildInvoicePreviewUrl(templateOverride);
                if (!url) {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'warning',
                            title: 'No Invoice Found',
                            text: 'Create at least one invoice to preview templates.'
                        });
                    } else {
                        alert('Create at least one invoice to preview templates.');
                    }
                    return;
                }
                window.open(url, '_blank');
            };

            $('.invoice-preview-btn').on('click', function() {
                const template = $(this).data('template');
                window.previewInvoiceTemplate(template);
            });

            $('#preview-current-template').on('click', function() {
                window.previewInvoiceTemplate();
            });
        });
    </script>
@stop
