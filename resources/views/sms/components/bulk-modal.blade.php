<!-- Bulk SMS Modal - Add this to any view where you want bulk SMS functionality -->
<div class="modal fade" id="bulkSmsModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-envelope-bulk"></i> Send Bulk SMS
                </h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="bulkSmsForm">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="bulk_phones">Phone Numbers</label>
                                <textarea class="form-control" id="bulk_phones" name="phones" rows="8" 
                                          placeholder="Enter phone numbers (one per line)&#10;01xxxxxxxxx&#10;01xxxxxxxxx&#10;01xxxxxxxxx" 
                                          required></textarea>
                                <small class="form-text text-muted">
                                    Enter one phone number per line. <span id="phone-count">0</span> numbers detected.
                                </small>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="bulk_message">Message</label>
                                <textarea class="form-control" id="bulk_message" name="message" rows="5" 
                                          maxlength="160" placeholder="Enter your message..." required></textarea>
                                <small class="form-text text-muted">
                                    <span id="bulk-char-count">0</span>/160 characters
                                </small>
                            </div>
                            
                            <div class="alert alert-info">
                                <h6><i class="fas fa-info-circle"></i> Quick Tips:</h6>
                                <ul class="mb-0 small">
                                    <li>Use Excel/CSV to prepare phone lists</li>
                                    <li>Remove country codes (+880)</li>
                                    <li>Keep messages under 160 characters</li>
                                    <li>Check numbers before sending</li>
                                </ul>
                            </div>
                            
                            <div class="form-group">
                                <label>Upload Phone List (Optional)</label>
                                <div class="custom-file">
                                    <input type="file" class="custom-file-input" id="phoneFile" accept=".txt,.csv">
                                    <label class="custom-file-label" for="phoneFile">Choose file...</label>
                                </div>
                                <small class="form-text text-muted">
                                    Upload .txt or .csv file with phone numbers
                                </small>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Preview Section -->
                    <div class="mt-3">
                        <h6>Preview:</h6>
                        <div class="card bg-light">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4">
                                        <strong>Recipients:</strong> <span id="preview-count">0</span>
                                    </div>
                                    <div class="col-md-4">
                                        <strong>Est. Cost:</strong> ৳<span id="preview-cost">0.00</span>
                                    </div>
                                    <div class="col-md-4">
                                        <strong>Provider:</strong> <span id="preview-provider">Not Set</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-envelope-bulk"></i> Send Bulk SMS
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Character count for bulk SMS
    $('#bulk_message').on('input', function() {
        const count = $(this).val().length;
        $('#bulk-char-count').text(count);
        
        if (count > 160) {
            $('#bulk-char-count').addClass('text-danger');
        } else {
            $('#bulk-char-count').removeClass('text-danger');
        }
        updatePreview();
    });

    // Phone count for bulk SMS
    $('#bulk_phones').on('input', function() {
        const phones = $(this).val().split('\n').filter(line => line.trim().length > 0);
        $('#phone-count').text(phones.length);
        updatePreview();
    });

    // File upload handler
    $('#phoneFile').on('change', function() {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const content = e.target.result;
                let phones = [];
                
                if (file.name.endsWith('.csv')) {
                    // Simple CSV parsing (first column)
                    const lines = content.split('\n');
                    phones = lines.map(line => line.split(',')[0].trim()).filter(phone => phone);
                } else {
                    // Text file
                    phones = content.split('\n').filter(line => line.trim());
                }
                
                $('#bulk_phones').val(phones.join('\n'));
                $('#phone-count').text(phones.length);
                updatePreview();
            };
            reader.readAsText(file);
            
            // Update file label
            $(this).next('.custom-file-label').text(file.name);
        }
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
        
        if (phones.length > 100) {
            Swal.fire({
                icon: 'warning',
                title: 'Too Many Recipients',
                text: 'Maximum 100 recipients allowed per batch. Please split into smaller groups.'
            });
            return;
        }
        
        // Confirm before sending
        Swal.fire({
            title: 'Send Bulk SMS?',
            html: `You are about to send SMS to <strong>${phones.length}</strong> recipients.<br>
                   Estimated cost: <strong>৳${(phones.length * 0.50).toFixed(2)}</strong><br><br>
                   This action cannot be undone.`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes, Send SMS',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                sendBulkSms(phones, message);
            }
        });
    });
});

function updatePreview() {
    const phones = $('#bulk_phones').val().split('\n').filter(line => line.trim().length > 0);
    const count = phones.length;
    const cost = count * 0.50; // Estimated cost per SMS
    
    $('#preview-count').text(count);
    $('#preview-cost').text(cost.toFixed(2));
    
    // You can update provider dynamically if needed
    $('#preview-provider').text('Active Provider');
}

function sendBulkSms(phones, message) {
    const submitBtn = $('#bulkSmsForm').find('button[type="submit"]');
    const originalText = submitBtn.html();
    
    submitBtn.html('<i class="fas fa-spinner fa-spin"></i> Sending...').prop('disabled', true);
    
    // Show progress
    let progressHtml = `
        <div class="progress mb-3">
            <div class="progress-bar progress-bar-striped progress-bar-animated" 
                 role="progressbar" style="width: 0%" id="bulk-progress"></div>
        </div>
        <div id="bulk-status">Preparing to send...</div>
    `;
    
    Swal.fire({
        title: 'Sending Bulk SMS',
        html: progressHtml,
        allowOutsideClick: false,
        showConfirmButton: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    $.ajax({
        url: '/sms/actions/bulk',
        method: 'POST',
        data: {
            _token: $('meta[name="csrf-token"]').attr('content'),
            phones: phones,
            message: message
        },
        success: function(response) {
            const summary = response.summary;
            
            Swal.fire({
                icon: summary.failed === 0 ? 'success' : 'warning',
                title: 'Bulk SMS Completed',
                html: `
                    <div class="text-left">
                        <strong>Results:</strong><br>
                        ✅ Successful: ${summary.successful}<br>
                        ❌ Failed: ${summary.failed}<br>
                        📊 Total: ${summary.total}
                    </div>
                `,
                showConfirmButton: true,
                confirmButtonText: 'View Details'
            }).then(() => {
                if (summary.failed > 0) {
                    showBulkResults(response.results);
                }
            });
            
            $('#bulkSmsModal').modal('hide');
            $('#bulkSmsForm')[0].reset();
            $('#bulk-char-count').text('0');
            $('#phone-count').text('0');
            updatePreview();
            
            // Refresh page to update stats if on dashboard
            if (window.location.pathname.includes('dashboard')) {
                setTimeout(() => {
                    location.reload();
                }, 2000);
            }
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
}

function showBulkResults(results) {
    const failedResults = results.filter(r => !r.success);
    
    if (failedResults.length > 0) {
        let tableHtml = `
            <div class="table-responsive">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Phone</th>
                            <th>Status</th>
                            <th>Error</th>
                        </tr>
                    </thead>
                    <tbody>
        `;
        
        failedResults.forEach(result => {
            tableHtml += `
                <tr>
                    <td>${result.phone}</td>
                    <td><span class="badge badge-danger">Failed</span></td>
                    <td>${result.message}</td>
                </tr>
            `;
        });
        
        tableHtml += `
                    </tbody>
                </table>
            </div>
        `;
        
        Swal.fire({
            title: 'Failed SMS Details',
            html: tableHtml,
            width: '800px',
            showConfirmButton: true
        });
    }
}
</script>