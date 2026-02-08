@extends('layouts.modern-admin')

@section('title', 'SMS Settings')
@section('page_title', 'SMS Provider Settings')

@section('header_actions')
    <div class="btn-group">
        <a class="btn modern-btn modern-btn-success" href="{{ route('sms.settings.create') }}">
            <i class="fas fa-plus"></i> Add Provider
        </a>
        <a class="btn modern-btn modern-btn-info" href="{{ route('sms.dashboard') }}">
            <i class="fas fa-tachometer-alt"></i> Dashboard
        </a>
    </div>
@stop

@section('page_content')
    <!-- Quick Stats -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-left-primary">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Providers
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $providers->count() }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-server fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card border-left-success">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Active Providers
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $providers->where('is_active', true)->count() }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card border-left-info">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                SMS Enabled
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $providers->where('sms_enabled', true)->count() }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-sms fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card border-left-warning">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Total Balance
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">৳{{ number_format($providers->sum('balance'), 2) }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-wallet fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- SMS Providers Table -->
    <div class="card modern-card">
        <div class="card-header modern-header">
            <h3 class="card-title">
                <i class="fas fa-cog"></i> SMS Provider Configuration
            </h3>
            <div class="card-tools">
                <button type="button" class="btn btn-sm btn-light" onclick="refreshAll();" title="Refresh All" data-toggle="tooltip">
                    <i class="fas fa-sync-alt"></i> Refresh
                </button>
            </div>
        </div>
        <div class="card-body p-0">
            @if($providers->count() > 0)
                <div class="table-responsive">
                    <table class="table modern-table mb-0">
                        <thead>
                            <tr>
                                <th width="50px">Status</th>
                                <th>Provider Details</th>
                                <th>Configuration</th>
                                <th>Statistics</th>
                                <th>Balance Info</th>
                                <th width="200px">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($providers as $provider)
                            <tr id="provider-{{ $provider->id }}">
                                <td>
                                    <div class="d-flex flex-column align-items-center">
                                        @if($provider->is_active)
                                            <span class="badge badge-success mb-1">Active</span>
                                        @else
                                            <span class="badge badge-secondary mb-1">Inactive</span>
                                        @endif
                                        
                                        @if($provider->sms_enabled)
                                            <span class="badge badge-info">Enabled</span>
                                        @else
                                            <span class="badge badge-warning">Disabled</span>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <div class="provider-info">
                                        <h6 class="mb-1">{{ $provider->provider_name }}</h6>
                                        <p class="mb-1 text-muted">{{ $provider->provider }}</p>
                                        <small class="text-info">
                                            <i class="fas fa-link"></i> 
                                            <a href="{{ $provider->api_url }}" target="_blank" class="text-info">{{ $provider->api_url }}</a>
                                        </small>
                                        @if($provider->sender_id)
                                            <br><small class="text-muted">Sender: {{ $provider->sender_id }}</small>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <div class="config-info">
                                        <div class="mb-2">
                                            <strong>API Token:</strong>
                                            <span class="badge badge-dark">
                                                {{ $provider->api_token ? '••••••••' . substr($provider->api_token, -4) : 'Not Set' }}
                                            </span>
                                        </div>
                                        <div class="mb-2">
                                            <strong>Created:</strong>
                                            <span class="text-muted">{{ $provider->created_at->format('M d, Y') }}</span>
                                        </div>
                                        @if($provider->expiry_date)
                                        <div class="mb-2">
                                            <strong>Expires:</strong>
                                            <span class="text-{{ $provider->isExpired() ? 'danger' : 'success' }}">
                                                {{ $provider->expiry_date->format('M d, Y') }}
                                            </span>
                                        </div>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <div class="stats-info">
                                        <div class="mb-1">
                                            <strong>Total Sent:</strong>
                                            <span class="badge badge-primary">{{ number_format($provider->total_sent) }}</span>
                                        </div>
                                        <div class="mb-1">
                                            <strong>This Month:</strong>
                                            <span class="badge badge-info">{{ number_format($provider->monthly_sent) }}</span>
                                        </div>
                                        @if($provider->last_balance_check)
                                        <div class="mb-1">
                                            <small class="text-muted">
                                                Last checked: {{ $provider->last_balance_check->diffForHumans() }}
                                            </small>
                                        </div>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <div class="balance-info">
                                        <div class="mb-2">
                                            <strong>Balance:</strong>
                                            <span class="badge badge-{{ $provider->hasLowBalance() ? 'warning' : 'success' }} balance-display" data-id="{{ $provider->id }}">
                                                ৳{{ number_format($provider->balance, 2) }}
                                            </span>
                                        </div>
                                        <button class="btn btn-sm btn-outline-info check-balance" data-id="{{ $provider->id }}">
                                            <i class="fas fa-sync-alt"></i> Check
                                        </button>
                                    </div>
                                </td>
                                <td>
                                    <div class="btn-group-vertical btn-group-sm w-100">
                                        @if($provider->is_active)
                                            <button class="btn btn-warning toggle-active" data-id="{{ $provider->id }}" title="Deactivate">
                                                <i class="fas fa-power-off"></i> Deactivate
                                            </button>
                                        @else
                                            <button class="btn btn-success toggle-active" data-id="{{ $provider->id }}" title="Activate">
                                                <i class="fas fa-power-off"></i> Activate
                                            </button>
                                        @endif
                                        
                                        <button class="btn btn-{{ $provider->sms_enabled ? 'secondary' : 'info' }} toggle-sms" data-id="{{ $provider->id }}">
                                            <i class="fas fa-{{ $provider->sms_enabled ? 'pause' : 'play' }}"></i> 
                                            {{ $provider->sms_enabled ? 'Disable SMS' : 'Enable SMS' }}
                                        </button>
                                        
                                        <a href="{{ route('sms.settings.edit', $provider) }}" class="btn btn-primary">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                        
                                        <button class="btn btn-danger delete-provider" data-id="{{ $provider->id }}" data-name="{{ $provider->provider_name }}">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-server fa-4x text-muted mb-4"></i>
                    <h4>No SMS Providers Configured</h4>
                    <p class="text-muted mb-4">Set up your first SMS provider to start sending messages through your application.</p>
                    <a href="{{ route('sms.settings.create') }}" class="btn modern-btn modern-btn-primary btn-lg">
                        <i class="fas fa-plus"></i> Add Your First Provider
                    </a>
                </div>
            @endif
        </div>
    </div>

    <!-- Quick Setup Guide -->
    @if($providers->count() === 0)
    <div class="card modern-card mt-4">
        <div class="card-header modern-header info-header">
            <h3 class="card-title">
                <i class="fas fa-info-circle"></i> Quick Setup Guide
            </h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <div class="setup-step text-center">
                        <div class="step-icon">
                            <i class="fas fa-user-plus fa-2x text-primary"></i>
                        </div>
                        <h5>1. Create Account</h5>
                        <p class="text-muted">Sign up with an SMS provider like BD Bulk SMS, GreenWeb, or SSL Wireless</p>
                        <a href="https://sms.greenweb.com.bd/dashboard.php" target="_blank" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-external-link-alt"></i> GreenWeb SMS
                        </a>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="setup-step text-center">
                        <div class="step-icon">
                            <i class="fas fa-key fa-2x text-success"></i>
                        </div>
                        <h5>2. Get API Token</h5>
                        <p class="text-muted">Obtain your API token/key from your SMS provider's dashboard</p>
                        <small class="text-info">Keep your token secure and never share it publicly</small>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="setup-step text-center">
                        <div class="step-icon">
                            <i class="fas fa-cog fa-2x text-warning"></i>
                        </div>
                        <h5>3. Configure Provider</h5>
                        <p class="text-muted">Add your provider details and start sending SMS messages</p>
                        <a href="{{ route('sms.settings.create') }}" class="btn btn-sm btn-warning">
                            <i class="fas fa-plus"></i> Add Provider
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
@stop

@section('additional_css')
<style>
.provider-info h6 {
    color: #333;
    font-weight: 600;
}

.config-info, .stats-info, .balance-info {
    font-size: 0.875rem;
}

.setup-step {
    padding: 1rem;
    border-radius: 8px;
    transition: all 0.3s ease;
}

.setup-step:hover {
    background: #f8f9fa;
    transform: translateY(-2px);
}

.step-icon {
    margin-bottom: 1rem;
}

.border-left-primary {
    border-left: 4px solid #4e73df!important;
}

.border-left-success {
    border-left: 4px solid #1cc88a!important;
}

.border-left-info {
    border-left: 4px solid #36b9cc!important;
}

.border-left-warning {
    border-left: 4px solid #f6c23e!important;
}

.card {
    box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15)!important;
}

.text-xs {
    font-size: 0.7rem;
}

.loading-row {
    opacity: 0.6;
}
</style>
@stop

@section('additional_js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(document).ready(function() {
    // Toggle Active Status
    $('.toggle-active').on('click', function() {
        const btn = $(this);
        const providerId = btn.data('id');
        const isActive = btn.hasClass('btn-warning');
        
        const action = isActive ? 'deactivate' : 'activate';
        const confirmText = isActive ? 
            'This will deactivate this provider. Are you sure?' : 
            'This will deactivate other providers and set this as active. Continue?';
        
        Swal.fire({
            title: `${action.charAt(0).toUpperCase() + action.slice(1)} Provider?`,
            text: confirmText,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: `Yes, ${action}`,
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                if (!isActive) {
                    // Setting as active
                    $.ajax({
                        url: '{{ route("sms.set-active") }}',
                        method: 'POST',
                        data: {
                            _token: $('meta[name="csrf-token"]').attr('content'),
                            provider_id: providerId
                        },
                        success: function(response) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Success!',
                                text: response.message,
                                timer: 2000,
                                showConfirmButton: false
                            });
                            
                            setTimeout(() => {
                                location.reload();
                            }, 1000);
                        },
                        error: function(xhr) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error!',
                                text: xhr.responseJSON?.message || 'Operation failed'
                            });
                        }
                    });
                } else {
                    // Deactivating - just reload for now (you might want to add a deactivate endpoint)
                    Swal.fire({
                        icon: 'info',
                        title: 'Note',
                        text: 'To deactivate this provider, activate another one instead.',
                        timer: 3000
                    });
                }
            }
        });
    });

    // Toggle SMS Enable/Disable
    $('.toggle-sms').on('click', function() {
        const btn = $(this);
        const providerId = btn.data('id');
        
        $.ajax({
            url: '{{ route("sms.toggle") }}',
            method: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                provider_id: providerId
            },
            success: function(response) {
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: response.message,
                    timer: 2000,
                    showConfirmButton: false
                });
                
                setTimeout(() => {
                    location.reload();
                }, 1000);
            },
            error: function(xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: xhr.responseJSON?.message || 'Operation failed'
                });
            }
        });
    });

    // Check Balance
    $('.check-balance').on('click', function() {
        const btn = $(this);
        const providerId = btn.data('id');
        const originalText = btn.html();
        
        btn.html('<i class="fas fa-spinner fa-spin"></i>').prop('disabled', true);
        
        $.ajax({
            url: '{{ route("sms.check-balance") }}',
            method: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                const balanceDisplay = $(`.balance-display[data-id="${providerId}"]`);
                balanceDisplay.text('৳' + parseFloat(response.balance).toFixed(2));
                
                // Update badge color
                balanceDisplay.removeClass('badge-warning badge-success');
                balanceDisplay.addClass(response.balance < 100 ? 'badge-warning' : 'badge-success');
                
                Swal.fire({
                    icon: 'success',
                    title: 'Balance Updated',
                    text: `Current balance: ৳${response.balance}`,
                    timer: 2000,
                    showConfirmButton: false
                });
            },
            error: function(xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: xhr.responseJSON?.message || 'Failed to check balance'
                });
            },
            complete: function() {
                btn.html(originalText).prop('disabled', false);
            }
        });
    });

    // Delete Provider
    $('.delete-provider').on('click', function() {
        const btn = $(this);
        const providerId = btn.data('id');
        const providerName = btn.data('name');
        
        Swal.fire({
            title: 'Delete Provider?',
            text: `Are you sure you want to delete "${providerName}"? This action cannot be undone.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                const form = $('<form>', {
                    method: 'POST',
                    action: `/sms/settings/${providerId}`
                });
                form.append($('<input>', {
                    type: 'hidden',
                    name: '_token',
                    value: $('meta[name="csrf-token"]').attr('content')
                }));
                form.append($('<input>', {
                    type: 'hidden',
                    name: '_method',
                    value: 'DELETE'
                }));
                
                $('body').append(form);
                form.submit();
            }
        });
    });
});

// Refresh All Function
function refreshAll() {
    Swal.fire({
        title: 'Refreshing...',
        text: 'Updating all provider statistics',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    setTimeout(() => {
        location.reload();
    }, 2000);
}
</script>
@stop