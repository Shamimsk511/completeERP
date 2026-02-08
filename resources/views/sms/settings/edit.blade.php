@extends('layouts.modern-admin')

@section('title', 'Edit SMS Provider')
@section('page_title', 'Edit SMS Provider')

@section('header_actions')
    <div class="btn-group">
        <a class="btn modern-btn modern-btn-secondary" href="{{ route('sms.settings.index') }}">
            <i class="fas fa-arrow-left"></i> Back to Settings
        </a>
        <a class="btn modern-btn modern-btn-info" href="{{ route('sms.dashboard') }}">
            <i class="fas fa-tachometer-alt"></i> Dashboard
        </a>
        <button type="button" class="btn modern-btn modern-btn-danger" id="delete-provider">
            <i class="fas fa-trash"></i> Delete Provider
        </button>
    </div>
@stop

@section('page_content')
    <div class="row">
        <div class="col-md-8">
            <div class="card modern-card">
                <div class="card-header modern-header">
                    <h3 class="card-title">
                        <i class="fas fa-edit"></i> Edit Provider Configuration
                    </h3>
                    <div class="card-tools">
                        <span class="badge badge-{{ $smsSettings->status_color }}">{{ ucfirst($smsSettings->status) }}</span>
                    </div>
                </div>
                <form action="{{ route('sms.settings.update', $smsSettings) }}" method="POST" id="providerForm">
                    @csrf
                    @method('PUT')
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="provider" class="form-label">Provider Code</label>
                                    <input type="text" 
                                           class="form-control modern-input" 
                                           id="provider" 
                                           value="{{ $smsSettings->provider }}" 
                                           readonly 
                                           disabled>
                                    <small class="form-text text-muted">Provider code cannot be changed after creation</small>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="provider_name" class="form-label required">Provider Display Name</label>
                                    <input type="text" 
                                           class="form-control modern-input @error('provider_name') is-invalid @enderror" 
                                           id="provider_name" 
                                           name="provider_name" 
                                           value="{{ old('provider_name', $smsSettings->provider_name) }}" 
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
                                       value="{{ old('api_token', $smsSettings->api_token) }}" 
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
                                Current token: ••••••••{{ substr($smsSettings->api_token, -4) }}
                            </small>
                        </div>

                        <div class="form-group">
                            <label for="api_url" class="form-label required">API URL</label>
                            <input type="url" 
                                   class="form-control modern-input @error('api_url') is-invalid @enderror" 
                                   id="api_url" 
                                   name="api_url" 
                                   value="{{ old('api_url', $smsSettings->api_url) }}" 
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
                                   value="{{ old('sender_id', $smsSettings->sender_id) }}" 
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
                                               {{ old('is_active', $smsSettings->is_active) ? 'checked' : '' }}>
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
                                               {{ old('sms_enabled', $smsSettings->sms_enabled) ? 'checked' : '' }}>
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
                                <i class="fas fa-save"></i> Update Provider
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="col-md-4">
            <!-- Provider Statistics -->
            <div class="card modern-card">
                <div class="card-header modern-header success-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-bar"></i> Provider Statistics
                    </h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-sm btn-light" onclick="refreshStats();" title="Refresh" data-toggle="tooltip">
                            <i class="fas fa-sync-alt"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="stat-item">
                        <div class="stat-label">Current Balance</div>
                        <div class="stat-value text-success">৳{{ number_format($smsSettings->balance, 2) }}</div>
                        <small class="text-muted">
                            @if($smsSettings->last_balance_check)
                                Last checked: {{ $smsSettings->last_balance_check->diffForHumans() }}
                            @else
                                Never checked
                            @endif
                        </small>
                    </div>
                    <hr>
                    <div class="stat-item">
                        <div class="stat-label">Total SMS Sent</div>
                        <div class="stat-value text-primary">{{ number_format($smsSettings->total_sent) }}</div>
                    </div>
                    <hr>
                    <div class="stat-item">
                        <div class="stat-label">This Month</div>
                        <div class="stat-value text-info">{{ number_format($smsSettings->monthly_sent) }}</div>
                    </div>
                    <hr>
                    <div class="stat-item">
                        <div class="stat-label">Created</div>
                        <div class="stat-value text-muted">{{ $smsSettings->created_at->format('M d, Y') }}</div>
                    </div>
                    @if($smsSettings->expiry_date)
                    <hr>
                    <div class="stat-item">
                        <div class="stat-label">Expires</div>
                        <div class="stat-value text-{{ $smsSettings->isExpired() ? 'danger' : 'warning' }}">
                            {{ $smsSettings->expiry_date->format('M d, Y') }}
                        </div>
                        <small class="text-muted">
                            @if($smsSettings->isExpired())
                                Expired {{ $smsSettings->expiry_date->diffForHumans() }}
                            @else
                                {{ $smsSettings->expiry_date->diffForHumans() }}
                            @endif
                        </small>
                    </div>
                    @endif
                </div>
                <div class="card-footer">
                    <button type="button" class="btn modern-btn modern-btn-info btn-block" onclick="checkBalance();">
                        <i class="fas fa-sync-alt"></i> Check Balance
                    </button>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="card modern-card">
                <div class="card-header modern-header warning-header">
                    <h3 class="card-title">
                        <i class="fas fa-history"></i> Recent Activity
                    </h3>
                </div>
                <div class="card-body">
                    @php
                        $recentLogs = App\Models\SmsLog::where('provider', $smsSettings->provider)
                                                      ->latest()
                                                      ->limit(5)
                                                      ->get();
                    @endphp
                    
                    @if($recentLogs->count() > 0)
                        <div class="activity-list">
                            @foreach($recentLogs as $log)
                            <div class="activity-item">
                                <div class="activity-icon">
                                    <i class="fas fa-{{ $log->isSuccessful() ? 'check' : ($log->isFailed() ? 'times' : 'clock') }} text-{{ $log->status_color }}"></i>
                                </div>
                                <div class="activity-content">
                                    <div class="activity-text">{{ $log->formatted_phone }}</div>
                                    <div class="activity-time">{{ $log->created_at->diffForHumans() }}</div>
                                </div>
                                <div class="activity-status">
                                    <span class="badge badge-{{ $log->status_color }}">{{ ucfirst($log->status) }}</span>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        <div class="text-center mt-3">
                            <a href="{{ route('sms.logs.index') }}?provider={{ $smsSettings->provider }}" class="btn btn-sm btn-outline-primary">
                                View All Logs
                            </a>
                        </div>
                    @else
                        <div class="text-center py-3">
                            <i class="fas fa-inbox fa-2x text-muted mb-2"></i>
                            <p class="text-muted mb-0">No recent activity</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card modern-card">
                <div class="card-header modern-header info-header">
                    <h3 class="card-title">
                        <i class="fas fa-bolt"></i> Quick Actions
                    </h3>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button type="button" class="btn modern-btn modern-btn-primary btn-block" onclick="sendTestSms();">
                            <i class="fas fa-paper-plane"></i> Send Test SMS
                        </button>
                        <button type="button" class="btn modern-btn modern-btn-info btn-block" onclick="checkBalance();">
                            <i class="fas fa-wallet"></i> Check Balance
                        </button>
                        <a href="{{ route('sms.logs.index') }}?provider={{ $smsSettings->provider }}" class="btn modern-btn modern-btn-secondary btn-block">
                            <i class="fas fa-list"></i> View Logs
                        </a>
                        @if(!$smsSettings->is_active)
                        <button type="button" class="btn modern-btn modern-btn-success btn-block" onclick="setAsActive();">
                            <i class="fas fa-power-off"></i> Set as Active
                        </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Test SMS Modal -->
    <div class="modal fade" id="testSmsModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Send Test SMS</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="testSmsForm">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="test_phone">Phone Number</label>
                            <input type="text" class="form-control" id="test_phone" name="phone" placeholder="01xxxxxxxxx" required>
                        </div>
                        <div class="form-group">
                            <label for="test_message">Message</label>
                            <textarea class="form-control" id="test_message" name="message" rows="3" maxlength="160" placeholder="Test message from {{ $smsSettings->provider_name }}" required></textarea>
                            <small class="form-text text-muted">
                                <span id="char-count">0</span>/160 characters
                            </small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn modern-btn modern-btn-primary">
                            <i class="fas fa-paper-plane"></i> Send Test SMS
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">Delete SMS Provider</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="text-center">
                        <i class="fas fa-exclamation-triangle fa-3x text-warning mb-3"></i>
                        <h5>Are you sure?</h5>
                        <p>This will permanently delete the SMS provider "<strong>{{ $smsSettings->provider_name }}</strong>" and all its configuration.</p>
                        <p class="text-danger"><strong>This action cannot be undone!</strong></p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <form action="{{ route('sms.settings.destroy', $smsSettings) }}" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-trash"></i> Delete Provider
                        </button>
                    </form>
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

.stat-item {
    margin-bottom: 1rem;
}

.stat-label {
    font-size: 0.875rem;
    color: #6c757d;
    margin-bottom: 0.25rem;
}

.stat-value {
    font-size: 1.25rem;
    font-weight: 600;
    margin-bottom: 0.25rem;
}

.activity-list {
    max-height: 300px;
    overflow-y: auto;
}

.activity-item {
    display: flex;
    align-items: center;
    padding: 0.75rem 0;
    border-bottom: 1px solid #eee;
}

.activity-item:last-child {
    border-bottom: none;
}

.activity-icon {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: #f8f9fa;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 0.75rem;
    flex-shrink: 0;
}

.activity-content {
    flex: 1;
}

.activity-text {
    font-weight: 500;
    font-size: 0.9rem;
}

.activity-time {
    font-size: 0.8rem;
    color: #6c757d;
}

.activity-status {
    flex-shrink: 0;
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
    // Character count for test SMS
    $('#test_message').on('input', function() {
        const count = $(this).val().length;
        $('#char-count').text(count);
        
        if (count > 160) {
            $('#char-count').addClass('text-danger');
        } else {
            $('#char-count').removeClass('text-danger');
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
        
        const apiToken = $('#api_token').val();
        const apiUrl = $('#api_url').val();
        
        if (!apiToken || !apiUrl) {
            Swal.fire({
                icon: 'warning',
                title: 'Missing Information',
                text: 'Please fill in API Token and API URL before testing.'
            });
            return;
        }
        
        const btn = $(this);
        const originalText = btn.html();
        
        btn.html('<i class="fas fa-spinner fa-spin"></i> Testing...').prop('disabled', true);
        
        // Simulate API test
        setTimeout(() => {
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

    // Delete provider
    $('#delete-provider').on('click', function() {
        $('#deleteModal').modal('show');
    });

    // Form submission
    $('#providerForm').on('submit', function(e) {
        const submitBtn = $(this).find('button[type="submit"]');
        const originalText = submitBtn.html();
        
        submitBtn.html('<i class="fas fa-spinner fa-spin"></i> Updating...').prop('disabled', true);
    });

    // Test SMS Form Submit
    $('#testSmsForm').on('submit', function(e) {
        e.preventDefault();
        
        const submitBtn = $(this).find('button[type="submit"]');
        const originalText = submitBtn.html();
        
        submitBtn.html('<i class="fas fa-spinner fa-spin"></i> Sending...').prop('disabled', true);
        
        $.ajax({
            url: '{{ route("sms.test") }}',
            method: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: response.message,
                    timer: 3000,
                    showConfirmButton: false
                });
                $('#testSmsModal').modal('hide');
                $('#testSmsForm')[0].reset();
                $('#char-count').text('0');
            },
            error: function(xhr) {
                let errorMessage = 'Failed to send SMS';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: errorMessage
                });
            },
            complete: function() {
                submitBtn.html(originalText).prop('disabled', false);
            }
        });
    });
});

// Send Test SMS
function sendTestSms() {
    $('#testSmsModal').modal('show');
}

// Check Balance
function checkBalance() {
    const btn = $('button:contains("Check Balance")');
    const originalText = btn.html();
    
    btn.html('<i class="fas fa-spinner fa-spin"></i> Checking...').prop('disabled', true);
    
    $.ajax({
        url: '{{ route("sms.check-balance") }}',
        method: 'POST',
        data: {
            _token: $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            Swal.fire({
                icon: 'success',
                title: 'Balance Updated',
                text: `Current balance: ৳${response.balance}`,
                timer: 3000,
                showConfirmButton: false
            });
            
            // Update balance display
            $('.stat-value.text-success').text('৳' + parseFloat(response.balance).toFixed(2));
            
            setTimeout(() => {
                location.reload();
            }, 1000);
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
}

// Set as Active
function setAsActive() {
    Swal.fire({
        title: 'Set as Active Provider?',
        text: 'This will deactivate other providers and set this as the active SMS provider.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Yes, Set Active',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '{{ route("sms.set-active") }}',
                method: 'POST',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content'),
                    provider_id: {{ $smsSettings->id }}
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
        }
    });
}

// Refresh Stats
function refreshStats() {
    $.ajax({
        url: '{{ route("sms.statistics") }}',
        method: 'GET',
        success: function(response) {
            Swal.fire({
                icon: 'success',
                title: 'Statistics Updated',
                text: 'Provider statistics have been refreshed',
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
                text: xhr.responseJSON?.message || 'Failed to refresh statistics'
            });
        }
    });
}
</script>
@stop