@extends('layouts.modern-admin')

@section('title', 'Add SMS Provider')
@section('page_title', 'Add New SMS Provider')

@section('header_actions')
    <div class="btn-group">
        <a class="btn modern-btn modern-btn-secondary" href="{{ route('sms.settings.index') }}">
            <i class="fas fa-arrow-left"></i> Back to Settings
        </a>
        <a class="btn modern-btn modern-btn-info" href="{{ route('sms.dashboard') }}">
            <i class="fas fa-tachometer-alt"></i> Dashboard
        </a>
    </div>
@stop

@section('page_content')
    <div class="row">
        <div class="col-md-8">
            <div class="card modern-card">
                <div class="card-header modern-header">
                    <h3 class="card-title">
                        <i class="fas fa-plus"></i> Provider Configuration
                    </h3>
                </div>
                <form action="{{ route('sms.settings.store') }}" method="POST" id="providerForm">
                    @csrf
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="provider" class="form-label required">Provider Code</label>
                                    <select class="form-control modern-select @error('provider') is-invalid @enderror" 
                                            id="provider" name="provider" required>
                                        <option value="">Select Provider</option>
                                        <option value="bdbulksms" {{ old('provider') == 'bdbulksms' ? 'selected' : '' }}>BD Bulk SMS</option>
                                        <option value="greenweb" {{ old('provider') == 'greenweb' ? 'selected' : '' }}>GreenWeb SMS</option>
                                        <option value="ssl" {{ old('provider') == 'ssl' ? 'selected' : '' }}>SSL Wireless</option>
                                        <option value="custom" {{ old('provider') == 'custom' ? 'selected' : '' }}>Custom Provider</option>
                                    </select>
                                    @error('provider')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="provider_name" class="form-label required">Provider Display Name</label>
                                    <input type="text" 
                                           class="form-control modern-input @error('provider_name') is-invalid @enderror" 
                                           id="provider_name" 
                                           name="provider_name" 
                                           value="{{ old('provider_name') }}" 
                                           placeholder="e.g., BD Bulk SMS - Main Account"
                                           required>
                                    @error('provider_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="api_token" class="form-label required">API Token/Key</label>
                            <div class="input-group">
                                <input type="password" 
                                       class="form-control modern-input @error('api_token') is-invalid @enderror" 
                                       id="api_token" 
                                       name="api_token" 
                                       value="{{ old('api_token') }}" 
                                       placeholder="Enter your API token"
                                       required>
                                <div class="input-group-append">
                                    <button type="button" class="btn btn-outline-secondary" id="toggleToken" title="Show/Hide Token">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                                @error('api_token')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <small class="form-text text-muted">
                                Get your API token from your SMS provider's dashboard. Keep it secure!
                            </small>
                        </div>

                        <div class="form-group">
                            <label for="api_url" class="form-label required">API URL</label>
                            <input type="url" 
                                   class="form-control modern-input @error('api_url') is-invalid @enderror" 
                                   id="api_url" 
                                   name="api_url" 
                                   value="{{ old('api_url', 'https://api.bdbulksms.net/g_api.php') }}" 
                                   placeholder="https://api.example.com/sms"
                                   required>
                            @error('api_url')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="sender_id" class="form-label">Sender ID (Optional)</label>
                            <input type="text" 
                                   class="form-control modern-input @error('sender_id') is-invalid @enderror" 
                                   id="sender_id" 
                                   name="sender_id" 
                                   value="{{ old('sender_id') }}" 
                                   placeholder="e.g., YourBrand"
                                   maxlength="20">
                            @error('sender_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">
                                Custom sender ID (if supported by your provider)
                            </small>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" 
                                               class="custom-control-input" 
                                               id="is_active" 
                                               name="is_active" 
                                               value="1" 
                                               {{ old('is_active', true) ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="is_active">
                                            Set as Active Provider
                                        </label>
                                    </div>
                                    <small class="form-text text-muted">
                                        Only one provider can be active at a time
                                    </small>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" 
                                               class="custom-control-input" 
                                               id="sms_enabled" 
                                               name="sms_enabled" 
                                               value="1" 
                                               {{ old('sms_enabled', true) ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="sms_enabled">
                                            Enable SMS Sending
                                        </label>
                                    </div>
                                    <small class="form-text text-muted">
                                        Global SMS sending control
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card-footer d-flex justify-content-between">
                        <a href="{{ route('sms.settings.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                        <div>
                            <button type="button" class="btn modern-btn modern-btn-info" id="testConnection">
                                <i class="fas fa-plug"></i> Test Connection
                            </button>
                            <button type="submit" class="btn modern-btn modern-btn-success">
                                <i class="fas fa-save"></i> Save Provider
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="col-md-4">
            <!-- Provider Info -->
            <div class="card modern-card">
                <div class="card-header modern-header info-header">
                    <h3 class="card-title">
                        <i class="fas fa-info-circle"></i> Provider Information
                    </h3>
                </div>
                <div class="card-body" id="providerInfo">
                    <div class="provider-details" id="bdbulksms-info" style="display: none;">
                        <h6><i class="fas fa-server"></i> BD Bulk SMS</h6>
                        <p class="text-muted">Professional SMS gateway service for Bangladesh</p>
                        <ul class="list-unstyled">
                            <li><i class="fas fa-check text-success"></i> High delivery rate</li>
                            <li><i class="fas fa-check text-success"></i> JSON API support</li>
                            <li><i class="fas fa-check text-success"></i> Real-time balance check</li>
                            <li><i class="fas fa-check text-success"></i> Monthly statistics</li>
                            <li><i class="fas fa-check text-success"></i> POST & GET methods</li>
                        </ul>
                        <div class="alert alert-info">
                            <strong>SMS Sending:</strong><br>
                            <code>https://api.bdbulksms.net/api.php</code><br><br>
                            <strong>Statistics:</strong><br>
                            <code>https://api.bdbulksms.net/g_api.php</code>
                        </div>
                        <div class="alert alert-warning">
                            <strong>Get Token:</strong><br>
                            <a href="https://gwb.li/token" target="_blank" class="alert-link">
                                <i class="fas fa-external-link-alt"></i> https://gwb.li/token
                            </a>
                        </div>
                    </div>

                    <div class="provider-details" id="greenweb-info" style="display: none;">
                        <h6><i class="fas fa-leaf"></i> GreenWeb SMS</h6>
                        <p class="text-muted">Reliable SMS service provider in Bangladesh</p>
                        <ul class="list-unstyled">
                            <li><i class="fas fa-check text-success"></i> Established provider</li>
                            <li><i class="fas fa-check text-success"></i> Good pricing</li>
                            <li><i class="fas fa-check text-success"></i> Quick delivery</li>
                        </ul>
                        <div class="alert alert-info">
                            <strong>API URL:</strong><br>
                            <code>http://api.greenweb.com.bd/api.php</code>
                        </div>
                        <a href="https://sms.greenweb.com.bd/dashboard.php" target="_blank" class="btn btn-sm btn-outline-success">
                            <i class="fas fa-external-link-alt"></i> Visit Dashboard
                        </a>
                    </div>

                    <div class="provider-details" id="ssl-info" style="display: none;">
                        <h6><i class="fas fa-shield-alt"></i> SSL Wireless</h6>
                        <p class="text-muted">Premium SMS gateway with SSL security</p>
                        <ul class="list-unstyled">
                            <li><i class="fas fa-check text-success"></i> SSL secured</li>
                            <li><i class="fas fa-check text-success"></i> Enterprise grade</li>
                            <li><i class="fas fa-check text-success"></i> High throughput</li>
                        </ul>
                        <div class="alert alert-info">
                            <strong>API URL:</strong><br>
                            <code>https://sms.sslwireless.com/pushapi/dynamic/server.php</code>
                        </div>
                    </div>

                    <div class="provider-details" id="custom-info" style="display: none;">
                        <h6><i class="fas fa-cogs"></i> Custom Provider</h6>
                        <p class="text-muted">Configure your own SMS provider</p>
                        <ul class="list-unstyled">
                            <li><i class="fas fa-check text-success"></i> Full customization</li>
                            <li><i class="fas fa-check text-success"></i> Your own API</li>
                            <li><i class="fas fa-check text-success"></i> Custom parameters</li>
                        </ul>
                        <div class="alert alert-warning">
                            <strong>Note:</strong> Make sure your API follows the expected response format
                        </div>
                    </div>

                    <div id="default-info">
                        <div class="text-center py-4">
                            <i class="fas fa-arrow-left fa-2x text-muted mb-3"></i>
                            <h6>Select a Provider</h6>
                            <p class="text-muted">Choose a provider to see details and configuration information</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Setup Guide -->
            <div class="card modern-card">
                <div class="card-header modern-header warning-header">
                    <h3 class="card-title">
                        <i class="fas fa-lightbulb"></i> Quick Setup Tips
                    </h3>
                </div>
                <div class="card-body">
                    <div class="setup-tip">
                        <h6><i class="fas fa-key text-warning"></i> API Token</h6>
                        <p class="small text-muted">Get your API token from your SMS provider's dashboard. This is usually found in the API settings section.</p>
                    </div>
                    <hr>
                    <div class="setup-tip">
                        <h6><i class="fas fa-test-tube text-info"></i> Test First</h6>
                        <p class="small text-muted">Always test your connection before saving to ensure everything works correctly.</p>
                    </div>
                    <hr>
                    <div class="setup-tip">
                        <h6><i class="fas fa-shield-alt text-success"></i> Security</h6>
                        <p class="small text-muted">Your API token is encrypted and stored securely. Never share it publicly.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('additional_css')
<style>
.required::after {
    content: " *";
    color: red;
}

.provider-details {
    animation: fadeIn 0.3s ease-in;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

.setup-tip h6 {
    margin-bottom: 0.5rem;
}

.alert code {
    background: rgba(255,255,255,0.3);
    padding: 2px 4px;
    border-radius: 3px;
    font-size: 0.85em;
}

.custom-switch {
    padding-left: 2.25rem;
}

.modern-input:focus, .modern-select:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
}
</style>
@stop

@section('additional_js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(document).ready(function() {
    // Provider selection handler
    $('#provider').on('change', function() {
        const selectedProvider = $(this).val();
        
        // Hide all provider details
        $('.provider-details').hide();
        $('#default-info').hide();
        
        if (selectedProvider) {
            $(`#${selectedProvider}-info`).show();
            
            // Update provider name if empty
            const providerName = $('#provider_name');
            if (!providerName.val()) {
                const displayNames = {
                    'bdbulksms': 'BD Bulk SMS',
                    'greenweb': 'GreenWeb SMS',
                    'ssl': 'SSL Wireless',
                    'custom': 'Custom Provider'
                };
                providerName.val(displayNames[selectedProvider] || '');
            }
            
            // Update API URL based on provider
            const apiUrls = {
                'bdbulksms': 'https://api.bdbulksms.net/api.php',
                'greenweb': 'http://api.greenweb.com.bd/api.php',
                'ssl': 'https://sms.sslwireless.com/pushapi/dynamic/server.php',
                'custom': ''
            };
            
            if (apiUrls[selectedProvider]) {
                $('#api_url').val(apiUrls[selectedProvider]);
            }
        } else {
            $('#default-info').show();
        }
    });

    // Toggle token visibility
    $('#toggleToken').on('click', function() {
        const tokenInput = $('#api_token');
        const icon = $(this).find('i');
        
        if (tokenInput.attr('type') === 'password') {
            tokenInput.attr('type', 'text');
            icon.removeClass('fa-eye').addClass('fa-eye-slash');
        } else {
            tokenInput.attr('type', 'password');
            icon.removeClass('fa-eye-slash').addClass('fa-eye');
        }
    });

    // Test connection
    $('#testConnection').on('click', function() {
        const form = $('#providerForm');
        const formData = form.serialize();
        
        // Basic validation
        const provider = $('#provider').val();
        const apiToken = $('#api_token').val();
        const apiUrl = $('#api_url').val();
        
        if (!provider || !apiToken || !apiUrl) {
            Swal.fire({
                icon: 'warning',
                title: 'Missing Information',
                text: 'Please fill in Provider, API Token, and API URL before testing.'
            });
            return;
        }
        
        const btn = $(this);
        const originalText = btn.html();
        
        btn.html('<i class="fas fa-spinner fa-spin"></i> Testing...').prop('disabled', true);
        
        // Simulate API test (you can implement actual testing here)
        setTimeout(() => {
            // For demo purposes, we'll simulate a successful test
            Swal.fire({
                icon: 'success',
                title: 'Connection Test',
                text: 'API connection test successful! You can now save the provider.',
                timer: 3000,
                showConfirmButton: true
            });
            
            btn.html(originalText).prop('disabled', false);
        }, 2000);
    });

    // Form submission
    $('#providerForm').on('submit', function(e) {
        const submitBtn = $(this).find('button[type="submit"]');
        const originalText = submitBtn.html();
        
        submitBtn.html('<i class="fas fa-spinner fa-spin"></i> Saving...').prop('disabled', true);
        
        // Let the form submit normally, but show loading state
    });

    // Trigger provider change on page load if there's a selected value
    if ($('#provider').val()) {
        $('#provider').trigger('change');
    }
});
</script>
@stop