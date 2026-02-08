@extends('layouts.modern-admin')

@section('title', 'SMS Dashboard')
@section('page_title', 'SMS Management Dashboard')

@section('header_actions')
    <div class="btn-group">
        <a class="btn modern-btn modern-btn-primary" href="{{ route('sms.settings.index') }}">
            <i class="fas fa-cog"></i> Settings
        </a>
        <button type="button" class="btn modern-btn modern-btn-success" id="test-sms-btn">
            <i class="fas fa-paper-plane"></i> Test SMS
        </button>
        <button type="button" class="btn modern-btn modern-btn-info" id="refresh-stats-btn">
            <i class="fas fa-sync-alt"></i> Refresh Stats
        </button>
    </div>
@stop

@section('page_content')
    <!-- SMS Status Alert -->
    @if(!$stats['sms_enabled'])
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle mr-2"></i>
            SMS sending is currently disabled. Enable it from the settings to start sending SMS.
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6">
            <div class="stats-card stats-card-primary">
                <div class="stats-icon">
                    <i class="fas fa-sms"></i>
                </div>
                <div class="stats-content">
                    <h3 class="stats-number">{{ number_format($stats['total_sent']) }}</h3>
                    <p class="stats-label">Total SMS Sent</p>
                    <div class="stats-trend">
                        <i class="fas fa-arrow-up"></i> {{ number_format($stats['today_sent']) }} today
                    </div>
                </div>
                <a href="{{ route('sms.logs.index') }}" class="stats-link">
                    View Logs <i class="fas fa-arrow-right"></i>
                </a>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6">
            <div class="stats-card stats-card-success">
                <div class="stats-icon">
                    <i class="fas fa-wallet"></i>
                </div>
                <div class="stats-content">
                    <h3 class="stats-number">৳{{ number_format($stats['balance'], 2) }}</h3>
                    <p class="stats-label">Current Balance</p>
                    <div class="stats-trend">
                        <i class="fas fa-info-circle"></i> {{ $stats['provider'] }}
                    </div>
                </div>
                <a href="#" class="stats-link" onclick="checkBalance();">
                    Check Balance <i class="fas fa-arrow-right"></i>
                </a>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6">
            <div class="stats-card stats-card-warning">
                <div class="stats-icon">
                    <i class="fas fa-calendar-alt"></i>
                </div>
                <div class="stats-content">
                    <h3 class="stats-number">{{ number_format($stats['this_month_sent']) }}</h3>
                    <p class="stats-label">This Month</p>
                    <div class="stats-trend">
                        <i class="fas fa-calendar"></i> {{ date('F Y') }}
                    </div>
                </div>
                <a href="{{ route('sms.logs.index') }}?month={{ date('m') }}" class="stats-link">
                    View Details <i class="fas fa-arrow-right"></i>
                </a>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6">
            <div class="stats-card stats-card-{{ $stats['total_failed'] > 0 ? 'danger' : 'info' }}">
                <div class="stats-icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div class="stats-content">
                    <h3 class="stats-number">{{ number_format($stats['total_failed']) }}</h3>
                    <p class="stats-label">Failed SMS</p>
                    <div class="stats-trend">
                        <i class="fas fa-percentage"></i> 
                        {{ $stats['total_sent'] > 0 ? number_format(($stats['total_failed'] / ($stats['total_sent'] + $stats['total_failed'])) * 100, 1) : 0 }}% failure rate
                    </div>
                </div>
                <a href="{{ route('sms.logs.index') }}?status=failed" class="stats-link">
                    View Failed <i class="fas fa-arrow-right"></i>
                </a>
            </div>
        </div>
    </div>

    <!-- Provider Status and Quick Actions -->
    <div class="row mb-4">
        <div class="col-md-8">
            <div class="card modern-card">
                <div class="card-header modern-header success-header">
                    <h3 class="card-title">
                        <i class="fas fa-server"></i> SMS Providers Status
                    </h3>
                    <div class="card-tools">
                        <button class="btn btn-sm btn-light" onclick="refreshProviderStats();" title="Refresh" data-toggle="tooltip">
                            <i class="fas fa-sync-alt"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    @if($providers->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Provider</th>
                                        <th>Status</th>
                                        <th>Balance</th>
                                        <th>SMS Sent</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($providers as $provider)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                @if($provider->is_active)
                                                    <i class="fas fa-check-circle text-success mr-2"></i>
                                                @endif
                                                <div>
                                                    <strong>{{ $provider->provider_name }}</strong>
                                                    <br><small class="text-muted">{{ $provider->provider }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge badge-{{ $provider->status_color }}">
                                                {{ ucfirst($provider->status) }}
                                            </span>
                                        </td>
                                        <td>৳{{ number_format($provider->balance, 2) }}</td>
                                        <td>{{ number_format($provider->total_sent) }}</td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                @if(!$provider->is_active)
                                                    <button class="btn btn-success btn-sm set-active" data-id="{{ $provider->id }}" title="Set Active">
                                                        <i class="fas fa-power-off"></i>
                                                    </button>
                                                @endif
                                                <button class="btn btn-{{ $provider->sms_enabled ? 'warning' : 'success' }} btn-sm toggle-sms" 
                                                        data-id="{{ $provider->id }}" 
                                                        title="{{ $provider->sms_enabled ? 'Disable' : 'Enable' }} SMS">
                                                    <i class="fas fa-{{ $provider->sms_enabled ? 'pause' : 'play' }}"></i>
                                                </button>
                                                <a href="{{ route('sms.settings.edit', $provider) }}" class="btn btn-info btn-sm" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-server fa-3x text-muted mb-3"></i>
                            <h5>No SMS Providers Configured</h5>
                            <p class="text-muted">Add your first SMS provider to start sending messages</p>
                            <a href="{{ route('sms.settings.create') }}" class="btn modern-btn modern-btn-primary">
                                <i class="fas fa-plus"></i> Add Provider
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card modern-card">
                <div class="card-header modern-header info-header">
                    <h3 class="card-title">
                        <i class="fas fa-tools"></i> Quick Actions
                    </h3>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button class="btn modern-btn modern-btn-primary btn-block" id="test-sms-action">
                            <i class="fas fa-paper-plane"></i> Send Test SMS
                        </button>
                        <button class="btn modern-btn modern-btn-info btn-block" id="check-balance-action">
                            <i class="fas fa-wallet"></i> Check Balance
                        </button>
                        <button class="btn modern-btn modern-btn-warning btn-block" id="bulk-sms-action">
                            <i class="fas fa-envelope-bulk"></i> Bulk SMS
                        </button>
                        <a href="{{ route('sms.logs.index') }}" class="btn modern-btn modern-btn-secondary btn-block">
                            <i class="fas fa-list"></i> View All Logs
                        </a>
                        <a href="{{ route('sms.settings.index') }}" class="btn modern-btn modern-btn-success btn-block">
                            <i class="fas fa-cog"></i> Manage Settings
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent SMS Logs -->
    <div class="card modern-card">
        <div class="card-header modern-header">
            <h3 class="card-title">
                <i class="fas fa-history"></i> Recent SMS Activity
            </h3>
            <div class="card-tools">
                <a href="{{ route('sms.logs.index') }}" class="btn btn-sm btn-light">
                    View All <i class="fas fa-arrow-right"></i>
                </a>
            </div>
        </div>
        <div class="card-body p-0">
            @if($recentLogs->count() > 0)
                <div class="table-responsive">
                    <table class="table modern-table mb-0">
                        <thead>
                            <tr>
                                <th>Time</th>
                                <th>Phone</th>
                                <th>Message</th>
                                <th>Status</th>
                                <th>Provider</th>
                                <th>Cost</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentLogs as $log)
                            <tr>
                                <td>{{ $log->created_at->format('M d, H:i') }}</td>
                                <td>{{ $log->formatted_phone }}</td>
                                <td>{{ $log->getTruncatedMessageAttribute(40) }}</td>
                                <td>
                                    <span class="badge badge-{{ $log->status_color }}">
                                        {{ ucfirst($log->status) }}
                                    </span>
                                </td>
                                <td>{{ ucfirst($log->provider) }}</td>
                                <td>৳{{ number_format($log->cost, 4) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-4">
                    <i class="fas fa-history fa-3x text-muted mb-3"></i>
                    <h5>No SMS Activity Yet</h5>
                    <p class="text-muted">SMS logs will appear here once you start sending messages</p>
                </div>
            @endif
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
                            <textarea class="form-control" id="test_message" name="message" rows="3" maxlength="160" placeholder="Enter your test message..." required></textarea>
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

    <!-- Bulk SMS Modal -->
    <div class="modal fade" id="bulkSmsModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Send Bulk SMS</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="bulkSmsForm">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="bulk_phones">Phone Numbers</label>
                            <textarea class="form-control" id="bulk_phones" name="phones" rows="5" placeholder="Enter phone numbers (one per line)&#10;01xxxxxxxxx&#10;01xxxxxxxxx&#10;01xxxxxxxxx" required></textarea>
                            <small class="form-text text-muted">
                                Enter one phone number per line. <span id="phone-count">0</span> numbers detected.
                            </small>
                        </div>
                        <div class="form-group">
                            <label for="bulk_message">Message</label>
                            <textarea class="form-control" id="bulk_message" name="message" rows="3" maxlength="160" placeholder="Enter your message..." required></textarea>
                            <small class="form-text text-muted">
                                <span id="bulk-char-count">0</span>/160 characters
                            </small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn modern-btn modern-btn-warning">
                            <i class="fas fa-envelope-bulk"></i> Send Bulk SMS
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop

@section('additional_css')
<style>
/* Stats Cards Styling */
.stats-card {
    background: white;
    border-radius: var(--border-radius-lg);
    padding: 1.5rem;
    box-shadow: var(--shadow);
    border: none;
    position: relative;
    overflow: hidden;
    transition: all 0.3s ease;
    margin-bottom: 1.5rem;
}

.stats-card:hover {
    transform: translateY(-5px);
    box-shadow: var(--shadow-lg);
}

.stats-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.stats-card-primary::before {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.stats-card-success::before {
    background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
}

.stats-card-warning::before {
    background: linear-gradient(135deg, #fc466b 0%, #3f5efb 100%);
}

.stats-card-danger::before {
    background: linear-gradient(135deg, #ff9a9e 0%, #fecfef 100%);
}

.stats-card-info::before {
    background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);
}

.stats-icon {
    position: absolute;
    top: 1rem;
    right: 1rem;
    width: 60px;
    height: 60px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(102, 126, 234, 0.1);
    color: #667eea;
    font-size: 1.5rem;
}

.stats-card-success .stats-icon {
    background: rgba(17, 153, 142, 0.1);
    color: #11998e;
}

.stats-card-warning .stats-icon {
    background: rgba(252, 70, 107, 0.1);
    color: #fc466b;
}

.stats-card-danger .stats-icon {
    background: rgba(255, 154, 158, 0.1);
    color: #ff9a9e;
}

.stats-card-info .stats-icon {
    background: rgba(168, 237, 234, 0.1);
    color: #a8edea;
}

.stats-content {
    padding-right: 80px;
}

.stats-number {
    font-size: 2rem;
    font-weight: 700;
    color: #333;
    margin-bottom: 0.5rem;
    line-height: 1;
}

.stats-label {
    font-size: 0.875rem;
    color: var(--text-muted);
    margin-bottom: 0.5rem;
    font-weight: 500;
}

.stats-trend {
    font-size: 0.75rem;
    color: #28a745;
    font-weight: 500;
}

.stats-link {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    padding: 0.75rem 1.5rem;
    background: rgba(0, 0, 0, 0.03);
    color: #667eea;
    text-decoration: none;
    font-size: 0.875rem;
    font-weight: 500;
    transition: all 0.3s ease;
}

.stats-link:hover {
    background: rgba(102, 126, 234, 0.1);
    color: #667eea;
    text-decoration: none;
}

/* Loading States */
.loading {
    opacity: 0.6;
    pointer-events: none;
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

    // Character count for bulk SMS
    $('#bulk_message').on('input', function() {
        const count = $(this).val().length;
        $('#bulk-char-count').text(count);
        
        if (count > 160) {
            $('#bulk-char-count').addClass('text-danger');
        } else {
            $('#bulk-char-count').removeClass('text-danger');
        }
    });

    // Phone count for bulk SMS
    $('#bulk_phones').on('input', function() {
        const phones = $(this).val().split('\n').filter(line => line.trim().length > 0);
        $('#phone-count').text(phones.length);
    });

    // Test SMS Modal
    $('#test-sms-btn, #test-sms-action').click(function() {
        $('#testSmsModal').modal('show');
    });

    // Bulk SMS Modal
    $('#bulk-sms-action').click(function() {
        $('#bulkSmsModal').modal('show');
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

    // Bulk SMS Form Submit
    $('#bulkSmsForm').on('submit', function(e) {
        e.preventDefault();
        
        const phones = $('#bulk_phones').val().split('\n').filter(line => line.trim().length > 0);
        const message = $('#bulk_message').val();
        
        if (phones.length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'No Phone Numbers',
                text: 'Please enter at least one phone number'
            });
            return;
        }
        
        const submitBtn = $(this).find('button[type="submit"]');
        const originalText = submitBtn.html();
        
        submitBtn.html('<i class="fas fa-spinner fa-spin"></i> Sending...').prop('disabled', true);
        
        $.ajax({
            url: '{{ route("sms.bulk") }}',
            method: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                phones: phones,
                message: message
            },
            success: function(response) {
                const summary = response.summary;
                let message = `Bulk SMS completed!\n\nTotal: ${summary.total}\nSuccessful: ${summary.successful}\nFailed: ${summary.failed}`;
                
                Swal.fire({
                    icon: summary.failed === 0 ? 'success' : 'warning',
                    title: 'Bulk SMS Completed',
                    text: message,
                    showConfirmButton: true
                });
                
                $('#bulkSmsModal').modal('hide');
                $('#bulkSmsForm')[0].reset();
                $('#bulk-char-count').text('0');
                $('#phone-count').text('0');
                
                // Refresh page to update stats
                setTimeout(() => {
                    location.reload();
                }, 2000);
            },
            error: function(xhr) {
                let errorMessage = 'Failed to send bulk SMS';
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

    // Toggle SMS
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

    // Set Active Provider
    $('.set-active').on('click', function() {
        const btn = $(this);
        const providerId = btn.data('id');
        
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
            }
        });
    });

    // Check Balance
    $('#check-balance-action').on('click', function() {
        checkBalance();
    });

    // Refresh Stats
    $('#refresh-stats-btn').on('click', function() {
        refreshProviderStats();
    });
});

// Check Balance Function
function checkBalance() {
    const btn = $('#check-balance-action');
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
            $('.stats-card-success .stats-number').text('৳' + parseFloat(response.balance).toFixed(2));
            
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

// Refresh Provider Stats
function refreshProviderStats() {
    const btn = $('#refresh-stats-btn');
    const originalText = btn.html();
    
    btn.html('<i class="fas fa-spinner fa-spin"></i> Refreshing...').prop('disabled', true);
    
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
        },
        complete: function() {
            btn.html(originalText).prop('disabled', false);
        }
    });
}
</script>
@stop