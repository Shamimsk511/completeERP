{{-- resources/views/components/customer-modal.blade.php --}}
<div class="modal fade" id="newCustomerModal" tabindex="-1" role="dialog" aria-labelledby="newCustomerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content modern-modal">
            <div class="modal-header modern-modal-header">
                <h5 class="modal-title" id="newCustomerModalLabel">
                    <i class="fas fa-user-plus mr-2"></i>Add New Customer
                </h5>
                <button type="button" class="close modern-close" data-dismiss="modal" aria-label="Close">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body modern-modal-body">
                <div id="modal-errors" class="alert modern-alert-danger" style="display: none;">
                    <div class="alert-icon">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div class="alert-content">
                        <strong>Validation Error</strong>
                        <div class="alert-description">Please correct the following errors:</div>
                        <ul id="error-list" class="alert-list"></ul>
                    </div>
                </div>
                
                <form id="customerForm" class="modern-form">
                    @csrf
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group modern-form-group">
                                <label for="modal-name" class="modern-label">
                                    <i class="fas fa-user mr-2"></i>Customer Name
                                </label>
                                <input type="text" 
                                       name="name" 
                                       id="modal-name" 
                                       class="form-control modern-input" 
                                       placeholder="Enter customer name" 
                                       required>
                                <div class="modern-input-focus"></div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group modern-form-group">
                                <label for="modal-phone" class="modern-label">
                                    <i class="fas fa-phone mr-2"></i>Phone Number
                                </label>
                                <input type="text" 
                                       name="phone" 
                                       id="modal-phone" 
                                       class="form-control modern-input" 
                                       placeholder="Enter phone number" 
                                       required>
                                <div class="modern-input-focus"></div>
                            </div>
                        </div>
                    </div>

                    <div id="modal-duplicate-warning" class="duplicate-warning" style="display: none;"></div>
                    
                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group modern-form-group">
                                <label for="modal-address" class="modern-label">
                                    <i class="fas fa-map-marker-alt mr-2"></i>Address
                                </label>
                                <textarea class="form-control modern-textarea"
                                          name="address"
                                          id="modal-address"
                                          rows="3"
                                          placeholder="Enter customer address"></textarea>
                                <div class="modern-input-focus"></div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group modern-form-group">
                                <label for="modal-opening-balance" class="modern-label">
                                    <i class="fas fa-dollar-sign mr-2"></i>Opening Balance
                                </label>
                                <div class="input-group modern-input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text modern-input-addon">৳</span>
                                    </div>
                                    <input type="number"
                                           name="opening_balance"
                                           id="modal-opening-balance"
                                           class="form-control modern-input"
                                           placeholder="0.00"
                                           step="0.01"
                                           value="0">
                                </div>
                                <div class="modern-input-focus"></div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group modern-form-group">
                                <label for="modal-account-group" class="modern-label">
                                    <i class="fas fa-users mr-2"></i>Customer Group
                                </label>
                                <select name="account_group_id"
                                        id="modal-account-group"
                                        class="form-control modern-input select2">
                                    <option value="">-- Select Group (Optional) --</option>
                                    @foreach($customerGroups as $group)
                                        <option value="{{ $group->id }}">
                                            {{ str_repeat('— ', $group->depth) }}{{ $group->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <small class="form-text text-muted">Categorize customer under a debtor group</small>
                                <div class="modern-input-focus"></div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-info">
                        <i class="fas fa-info-circle mr-2"></i>
                        <span>Customer will be immediately available for selection after creation.</span>
                    </div>
                </form>
            </div>
            <div class="modal-footer modern-modal-footer">
                <button type="button" class="btn modern-btn-outline-secondary" data-dismiss="modal">
                    <i class="fas fa-times mr-2"></i>Cancel
                </button>
                <button type="button" class="btn modern-btn modern-btn-primary" id="saveCustomerBtn">
                    <i class="fas fa-save mr-2"></i>Save Customer
                </button>
            </div>
        </div>
    </div>
</div>

@push('js')
<script>
$(document).ready(function() {
    // Fix for Bootstrap modals and Select2
    $.fn.modal.Constructor.prototype.enforceFocus = function() {
        var modal = this;
        $(document).on('focusin.modal', function(e) {
            if (modal.$element[0] !== e.target && 
                !modal.$element.has(e.target).length && 
                !$(e.target).closest('.select2-container').length) {
                modal.$element.focus();
            }
        });
    };

    let duplicateCheckTimeout = null;
    let hasDuplicate = false;

    function renderDuplicateWarning(customers) {
        if (!customers || !customers.length) {
            $('#modal-duplicate-warning').hide().empty();
            return;
        }

        let items = '';
        customers.forEach(function(customer) {
            const phone = customer.phone ? ` | ${customer.phone}` : '';
            const address = customer.address ? ` | ${customer.address}` : '';
            items += `
                <div class="duplicate-item">
                    <div class="item-name">${customer.name}${phone}</div>
                    <div class="item-details">Outstanding: ৳${Number(customer.outstanding_balance || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}${address}</div>
                </div>
            `;
        });

        const html = `
            <div class="warning-header">
                <i class="fas fa-exclamation-triangle"></i>
                Customer with this name or phone already exists.
            </div>
            ${items}
        `;

        $('#modal-duplicate-warning').html(html).show();
    }

    function checkDuplicateCustomer() {
        const name = $('#modal-name').val().trim();
        const phone = $('#modal-phone').val().trim();

        if (name.length < 2 && phone.length < 6) {
            hasDuplicate = false;
            $('#modal-duplicate-warning').hide().empty();
            return;
        }

        $.ajax({
            url: "{{ route('customers.check-duplicate') }}",
            method: "GET",
            data: { name: name, phone: phone },
            success: function(response) {
                if (response.exists) {
                    hasDuplicate = true;
                    renderDuplicateWarning(response.customers || []);
                } else {
                    hasDuplicate = false;
                    $('#modal-duplicate-warning').hide().empty();
                }
            },
            error: function() {
                hasDuplicate = false;
                $('#modal-duplicate-warning').hide().empty();
            }
        });
    }

    // Customer modal handling with loading states
    $('#saveCustomerBtn').click(function() {
        const $btn = $(this);
        const originalText = $btn.html();
        
        if (hasDuplicate) {
            Swal.fire({
                icon: 'warning',
                title: 'Duplicate Customer',
                text: 'A customer with this name or phone already exists.',
                background: '#f8f9fa',
                color: '#333'
            });
            return;
        }

        // Clear previous errors
        $('#modal-errors').slideUp();
        $('#error-list').empty();
        
        // Show loading state
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-2"></i>Creating...');
        
        // Get form data
        const formData = {
            name: $('#modal-name').val().trim(),
            phone: $('#modal-phone').val().trim(),
            address: $('#modal-address').val().trim(),
            opening_balance: $('#modal-opening-balance').val() || 0,
            account_group_id: $('#modal-account-group').val() || null,
            _token: $('meta[name="csrf-token"]').attr('content')
        };
        
        // Submit form via AJAX
        $.ajax({
            url: "{{ route('customers.store') }}",
            type: "POST",
            data: formData,
            success: function(response) {
                // Close modal with animation
                $('#newCustomerModal').modal('hide');
                
                // Add the new customer to the dropdown
                const newOption = new Option(response.customer.name, response.customer.id, true, true);
                $('#customer_id').append(newOption).trigger('change');
                
                // Set customer details if fields exist
                if ($('#customer_phone').length) $('#customer_phone').val(response.customer.phone);
                if ($('#customer_address').length) $('#customer_address').val(response.customer.address);
                if ($('#customer_balance').length) $('#customer_balance').val(response.customer.outstanding_balance);
                
                // Show success message with modern styling
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: 'Customer "' + response.customer.name + '" created successfully',
                    timer: 3000,
                    showConfirmButton: false,
                    toast: true,
                    position: 'top-end',
                    background: '#f8f9fa',
                    color: '#333'
                });
                
                // Reset form
                $('#customerForm')[0].reset();
                $('#modal-opening-balance').val('0');
                
                // Remove any input focus states
                $('.modern-form-group').removeClass('focused');
            },
            error: function(xhr) {
                if(xhr.status === 422) {
                    const errors = xhr.responseJSON.errors;
                    $('#modal-errors').slideDown();
                    
                    // Display each error with animation
                    $.each(errors, function(key, value) {
                        $('#error-list').append('<li>' + value + '</li>');
                    });
                    
                    // Highlight error fields
                    $.each(errors, function(key, value) {
                        $('#modal-' + key.replace('_', '-')).addClass('is-invalid');
                    });
                } else {
                    // Show general error
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: 'Something went wrong. Please try again.',
                        background: '#f8f9fa',
                        color: '#333'
                    });
                }
            },
            complete: function() {
                // Reset button state
                $btn.prop('disabled', false).html(originalText);
            }
        });
    });

    // Enhanced form interactions
    $('.modern-input, .modern-textarea').on('focus', function() {
        $(this).closest('.modern-form-group').addClass('focused');
        $(this).removeClass('is-invalid');
    });

    $('.modern-input, .modern-textarea').on('blur', function() {
        if (!$(this).val()) {
            $(this).closest('.modern-form-group').removeClass('focused');
        }
    });

    // Real-time validation
    $('#modal-name').on('input', function() {
        if ($(this).val().length >= 2) {
            $(this).removeClass('is-invalid').addClass('is-valid');
        }

        clearTimeout(duplicateCheckTimeout);
        duplicateCheckTimeout = setTimeout(checkDuplicateCustomer, 500);
    });

    $('#modal-phone').on('input', function() {
        const phone = $(this).val();
        if (phone.length >= 10) {
            $(this).removeClass('is-invalid').addClass('is-valid');
        }

        clearTimeout(duplicateCheckTimeout);
        duplicateCheckTimeout = setTimeout(checkDuplicateCustomer, 500);
    });

    // Reset form when modal is closed
    $('#newCustomerModal').on('hidden.bs.modal', function () {
        $('#customerForm')[0].reset();
        $('#modal-opening-balance').val('0');
        $('#modal-account-group').val('').trigger('change');
        $('#modal-errors').hide();
        $('#error-list').empty();
        $('#modal-duplicate-warning').hide().empty();
        hasDuplicate = false;
        $('.modern-form-group').removeClass('focused');
        $('.modern-input, .modern-textarea').removeClass('is-invalid is-valid');
        $('#saveCustomerBtn').prop('disabled', false).html('<i class="fas fa-save mr-2"></i>Save Customer');
    });
    
    // Initialize form state when modal is shown
    $('#newCustomerModal').on('shown.bs.modal', function() {
        $('#modal-name').focus();

        // Initialize Select2 for account group dropdown
        $('#modal-account-group').select2({
            dropdownParent: $('#newCustomerModal'),
            width: '100%',
            placeholder: '-- Select Group (Optional) --',
            allowClear: true
        });
    });

    // Keyboard shortcuts
    $('#newCustomerModal').on('keydown', function(e) {
        // Enter key to save (if not in textarea)
        if (e.keyCode === 13 && !$(e.target).is('textarea')) {
            e.preventDefault();
            $('#saveCustomerBtn').click();
        }
        
        // Escape key to close
        if (e.keyCode === 27) {
            $(this).modal('hide');
        }
    });
});
</script>
@endpush

@push('css')
<style>
/* Modern Modal Styling */
.modern-modal {
    border: none;
    border-radius: 1rem;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.12), 0 4px 16px rgba(0, 0, 0, 0.08);
    overflow: hidden;
}

.modern-modal-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
    padding: 1.5rem 2rem;
    position: relative;
}

.modern-modal-header .modal-title {
    font-weight: 600;
    font-size: 1.25rem;
    margin: 0;
}

.modern-close {
    background: none;
    border: none;
    color: white;
    font-size: 1.5rem;
    opacity: 0.8;
    transition: all 0.3s ease;
    padding: 0.5rem;
    border-radius: 0.5rem;
}

.modern-close:hover {
    opacity: 1;
    background: rgba(255, 255, 255, 0.1);
    transform: scale(1.1);
}

.modern-modal-body {
    padding: 2rem;
    background: #f8f9fc;
}

.modern-modal-footer {
    background: white;
    border: none;
    padding: 1.5rem 2rem;
    border-top: 1px solid #e9ecef;
}

/* Modern Form Styling */
.modern-form {
    margin: 0;
}

.modern-form-group {
    margin-bottom: 1.5rem;
    position: relative;
}

.modern-label {
    font-weight: 600;
    color: #495057;
    margin-bottom: 0.5rem;
    font-size: 0.9rem;
    display: flex;
    align-items: center;
}

.modern-label i {
    color: #667eea;
    width: 16px;
}

.modern-input, .modern-textarea {
    border: 2px solid #e9ecef;
    border-radius: 0.75rem;
    padding: 0.75rem 1rem;
    font-size: 0.95rem;
    transition: all 0.3s ease;
    background: white;
    position: relative;
}

.modern-input:focus, .modern-textarea:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.15);
    outline: none;
    background: white;
}

.modern-input.is-valid {
    border-color: #28a745;
    background: white;
}

.modern-input.is-invalid {
    border-color: #dc3545;
    background: white;
}

.modern-textarea {
    resize: vertical;
    min-height: 80px;
}

.modern-input-group .modern-input {
    border-top-left-radius: 0;
    border-bottom-left-radius: 0;
}

.modern-input-addon {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: 2px solid #667eea;
    border-right: none;
    font-weight: 600;
    padding: 0.75rem 1rem;
}

/* Form Info */
.form-info {
    background: linear-gradient(135deg, rgba(102, 126, 234, 0.1), rgba(118, 75, 162, 0.1));
    border: 1px solid rgba(102, 126, 234, 0.2);
    border-radius: 0.75rem;
    padding: 1rem;
    margin-top: 1rem;
    color: #495057;
    font-size: 0.9rem;
}

.form-info i {
    color: #667eea;
}

/* Modern Alert */
.modern-alert-danger {
    background: linear-gradient(135deg, rgba(220, 53, 69, 0.1), rgba(255, 193, 7, 0.1));
    border: 1px solid rgba(220, 53, 69, 0.2);
    border-radius: 0.75rem;
    padding: 1rem;
    margin-bottom: 1.5rem;
    display: flex;
    align-items: flex-start;
}

.alert-icon {
    color: #dc3545;
    font-size: 1.2rem;
    margin-right: 1rem;
    margin-top: 0.1rem;
}

.alert-content {
    flex: 1;
}

.alert-content strong {
    color: #dc3545;
    font-weight: 600;
}

.alert-description {
    color: #6c757d;
    font-size: 0.9rem;
    margin-bottom: 0.5rem;
}

.alert-list {
    margin: 0;
    padding-left: 1.2rem;
    color: #dc3545;
}

.alert-list li {
    margin-bottom: 0.25rem;
    font-size: 0.9rem;
}

/* Modern Buttons */
.modern-btn {
    padding: 0.75rem 1.5rem;
    border-radius: 0.75rem;
    font-weight: 600;
    text-transform: none;
    letter-spacing: 0.025em;
    transition: all 0.3s ease;
    border: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 120px;
}

.modern-btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
}

.modern-btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
    color: white;
}

.modern-btn-primary:disabled {
    opacity: 0.6;
    transform: none;
    cursor: not-allowed;
}

.modern-btn-outline-secondary {
    background: transparent;
    color: #6c757d;
    border: 2px solid #e9ecef;
}

.modern-btn-outline-secondary:hover {
    background: #f8f9fa;
    border-color: #dee2e6;
    color: #495057;
    transform: translateY(-1px);
}

/* Animation enhancements */
.modal.fade .modal-dialog {
    transform: translate(0, -50px);
    transition: transform 0.3s ease-out;
}

.modal.show .modal-dialog {
    transform: translate(0, 0);
}

/* Focus states */
.modern-form-group.focused .modern-label {
    color: #667eea;
}

.modern-form-group.focused .modern-label i {
    transform: scale(1.1);
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .modal-lg {
        max-width: 95%;
        margin: 1rem auto;
    }
    
    .modern-modal-body {
        padding: 1.5rem;
    }
    
    .modern-modal-header,
    .modern-modal-footer {
        padding: 1rem 1.5rem;
    }
    
    .modern-btn {
        padding: 0.6rem 1.2rem;
        font-size: 0.9rem;
    }
}

/* Ensure modal is visible on top of everything */
#newCustomerModal {
    z-index: 9999 !important;
}

/* Fix for select2 inside modals */
#newCustomerModal .select2-container {
    width: 100% !important;
    z-index: 10000 !important;
}

#newCustomerModal .select2-dropdown {
    z-index: 10001 !important;
}

/* Loading spinner animation */
@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.fa-spin {
    animation: spin 1s linear infinite;
}

/* Duplicate warning */
.duplicate-warning {
    margin-top: 1rem;
    background: #fff7ed;
    border: 1px solid #fed7aa;
    border-radius: 0.75rem;
    padding: 0.75rem 1rem;
    color: #9a3412;
    font-size: 0.9rem;
}

.duplicate-warning .warning-header {
    display: flex;
    align-items: center;
    font-weight: 600;
    margin-bottom: 0.5rem;
    gap: 0.5rem;
}

.duplicate-warning .duplicate-item {
    padding: 0.5rem 0;
    border-top: 1px dashed #fdba74;
}

.duplicate-warning .duplicate-item:first-child {
    border-top: none;
    padding-top: 0;
}

.duplicate-warning .item-name {
    font-weight: 600;
    color: #7c2d12;
}

.duplicate-warning .item-details {
    font-size: 0.8rem;
    color: #9a3412;
}
</style>
@endpush
