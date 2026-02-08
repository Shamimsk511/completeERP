<!-- SMS Statistics Widget - Add this to your main dashboard -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-sms"></i> SMS Overview
        </h3>
        <div class="card-tools">
            <a href="{{ route('sms.dashboard') }}" class="btn btn-sm btn-primary">
                <i class="fas fa-external-link-alt"></i> Full Dashboard
            </a>
        </div>
    </div>
    <div class="card-body">
        @php
            $smsStats = app(\App\Services\SmsService::class)->getDashboardStats();
            $activeProvider = \App\Models\SmsSettings::getActiveProvider();
        @endphp
        
        <div class="row">
            <div class="col-md-3">
                <div class="info-box">
                    <span class="info-box-icon bg-success">
                        <i class="fas fa-check-circle"></i>
                    </span>
                    <div class="info-box-content">
                        <span class="info-box-text">Sent Today</span>
                        <span class="info-box-number">{{ number_format($smsStats['today_sent']) }}</span>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="info-box">
                    <span class="info-box-icon bg-info">
                        <i class="fas fa-calendar-alt"></i>
                    </span>
                    <div class="info-box-content">
                        <span class="info-box-text">This Month</span>
                        <span class="info-box-number">{{ number_format($smsStats['this_month_sent']) }}</span>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="info-box">
                    <span class="info-box-icon bg-{{ $smsStats['balance'] > 100 ? 'success' : 'warning' }}">
                        <i class="fas fa-wallet"></i>
                    </span>
                    <div class="info-box-content">
                        <span class="info-box-text">Balance</span>
                        <span class="info-box-number">৳{{ number_format($smsStats['balance'], 2) }}</span>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="info-box">
                    <span class="info-box-icon bg-{{ $smsStats['sms_enabled'] ? 'primary' : 'secondary' }}">
                        <i class="fas fa-{{ $smsStats['sms_enabled'] ? 'power-off' : 'pause' }}"></i>
                    </span>
                    <div class="info-box-content">
                        <span class="info-box-text">Status</span>
                        <span class="info-box-number">{{ $smsStats['sms_enabled'] ? 'Active' : 'Disabled' }}</span>
                    </div>
                </div>
            </div>
        </div>
        
        @if(!$smsStats['sms_enabled'])
            <div class="alert alert-warning mt-3">
                <i class="fas fa-exclamation-triangle"></i>
                SMS sending is currently disabled. 
                <a href="{{ route('sms.settings.index') }}" class="alert-link">Enable it here</a>
            </div>
        @endif
        
        @if($activeProvider && $activeProvider->hasLowBalance())
            <div class="alert alert-warning mt-3">
                <i class="fas fa-exclamation-triangle"></i>
                Low SMS balance detected (৳{{ number_format($activeProvider->balance, 2) }}). 
                <a href="{{ route('sms.settings.index') }}" class="alert-link">Check provider settings</a>
            </div>
        @endif
        
        <div class="row mt-3">
            <div class="col-md-12">
                <div class="btn-group btn-group-sm w-100">
                    <button type="button" class="btn btn-primary" onclick="openTestSmsModal();">
                        <i class="fas fa-paper-plane"></i> Test SMS
                    </button>
                    <button type="button" class="btn btn-warning" onclick="openBulkSmsModal();">
                        <i class="fas fa-envelope-bulk"></i> Bulk SMS
                    </button>
                    <a href="{{ route('sms.logs.index') }}" class="btn btn-info">
                        <i class="fas fa-list"></i> View Logs
                    </a>
                    <a href="{{ route('sms.settings.index') }}" class="btn btn-secondary">
                        <i class="fas fa-cog"></i> Settings
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Test SMS Modal -->
<div class="modal fade" id="quickTestSmsModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Quick Test SMS</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="quickTestSmsForm">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label for="quick_test_phone">Phone Number</label>
                        <input type="text" class="form-control" id="quick_test_phone" name="phone" 
                               placeholder="01xxxxxxxxx" required>
                    </div>
                    <div class="form-group">
                        <label for="quick_test_message">Message</label>
                        <textarea class="form-control" id="quick_test_message" name="message" rows="3" 
                                  maxlength="160" placeholder="Test message..." required>Test SMS from {{ config('app.name') }} - {{ now()->format('Y-m-d H:i') }}</textarea>
                        <small class="form-text text-muted">
                            <span id="quick-char-count">0</span>/160 characters
                        </small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-paper-plane"></i> Send Test SMS
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Character count for quick test SMS
$(document).ready(function() {
    $('#quick_test_message').on('input', function() {
        const count = $(this).val().length;
        $('#quick-char-count').text(count);
        
        if (count > 160) {
            $('#quick-char-count').addClass('text-danger');
        } else {
            $('#quick-char-count').removeClass('text-danger');
        }
    });
    
    // Trigger character count on load
    $('#quick_test_message').trigger('input');
    
    // Quick Test SMS Form Submit
    $('#quickTestSmsForm').on('submit', function(e) {
        e.preventDefault();
        
        const submitBtn = $(this).find('button[type="submit"]');
        const originalText = submitBtn.html();
        
        submitBtn.html('<i class="fas fa-spinner fa-spin"></i> Sending...').prop('disabled', true);
        
        $.ajax({
            url: '/sms/actions/test',
            method: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                Swal.fire({
                    icon: 'success',
                    title: 'SMS Sent Successfully!',
                    text: `Sent via ${response.provider}`,
                    timer: 3000,
                    showConfirmButton: false
                });
                $('#quickTestSmsModal').modal('hide');
                $('#quickTestSmsForm')[0].reset();
                $('#quick-char-count').text('0');
            },
            error: function(xhr) {
                let errorMessage = 'Failed to send SMS';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                
                Swal.fire({
                    icon: 'error',
                    title: 'SMS Failed!',
                    text: errorMessage
                });
            },
            complete: function() {
                submitBtn.html(originalText).prop('disabled', false);
            }
        });
    });
});

function openTestSmsModal() {
    $('#quickTestSmsModal').modal('show');
}

function openBulkSmsModal() {
    if (typeof $('#bulkSmsModal').modal === 'function') {
        $('#bulkSmsModal').modal('show');
    } else {
        // Redirect to SMS dashboard if bulk modal not available
        window.location.href = "{{ route('sms.dashboard') }}";
    }
}
</script>