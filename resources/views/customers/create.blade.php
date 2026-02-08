@extends('layouts.modern-admin')

@section('title', 'Add Customer')
@section('page_title', 'Add New Customer')

@section('header_actions')
    <a href="{{ route('customers.index') }}" class="btn modern-btn modern-btn-secondary">
        <i class="fas fa-arrow-left"></i> Back to Customers
    </a>
@stop

@section('page_content')
    <!-- Customer Form Card -->
    <div class="card modern-card">
        <div class="card-header modern-header success-header">
            <h3 class="card-title">
                <i class="fas fa-user-plus"></i> Customer Information
            </h3>
        </div>

        <div class="card-body">
            @if ($errors->any())
                <div class="modern-alert modern-alert-danger mb-4">
                    <div class="alert-icon">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div class="alert-content">
                        <h5>Whoops! There were some problems with your input.</h5>
                        <ul class="error-list">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            <form action="{{ route('customers.store') }}" method="POST" class="modern-form">
                @csrf

                <div class="form-grid">
                    <div class="form-group-modern full-width">
                        <label for="name" class="modern-label">
                            <i class="fas fa-user"></i>
                            Customer Name
                        </label>
                        <input type="text" 
                               name="name" 
                               id="name" 
                               class="modern-input" 
                               placeholder="Enter customer name" 
                               value="{{ old('name') }}" 
                               required>
                    </div>

                    <div class="form-group-modern full-width">
                        <label for="phone" class="modern-label">
                            <i class="fas fa-phone"></i>
                            Phone Number
                        </label>
                        <input type="text" 
                               name="phone" 
                               id="phone" 
                               class="modern-input" 
                               placeholder="Enter phone number" 
                               value="{{ old('phone') }}" 
                               required>
                        <div id="customer-duplicate-warning" class="duplicate-warning" style="display: none;"></div>
                    </div>

                    <div class="form-group-modern full-width">
                        <label for="address" class="modern-label">
                            <i class="fas fa-map-marker-alt"></i>
                            Address
                        </label>
                        <textarea name="address"
                                  id="address"
                                  class="modern-input"
                                  rows="3"
                                  placeholder="Enter customer address">{{ old('address') }}</textarea>
                    </div>

                    <div class="form-group-modern half-width">
                        <label for="account_group_id" class="modern-label">
                            <i class="fas fa-users"></i>
                            Customer Group
                        </label>
                        <select name="account_group_id" id="account_group_id" class="modern-input select2">
                            <option value="">-- Select Group (Optional) --</option>
                            @foreach($customerGroups as $group)
                                <option value="{{ $group->id }}" {{ old('account_group_id') == $group->id ? 'selected' : '' }}>
                                    {{ str_repeat('— ', $group->depth) }}{{ $group->name }}
                                </option>
                            @endforeach
                        </select>
                        <small class="form-text text-muted">Categorize customer under a debtor group</small>
                    </div>

                    <div class="form-group-modern half-width">
                        <label for="opening_balance" class="modern-label">
                            <i class="fas fa-dollar-sign"></i>
                            Opening Balance
                        </label>
                        <input type="number"
                               name="opening_balance"
                               id="opening_balance"
                               class="modern-input"
                               placeholder="0.00"
                               step="0.01"
                               value="{{ old('opening_balance', 0) }}">
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn modern-btn modern-btn-success">
                        <i class="fas fa-save"></i> Create Customer
                    </button>
                    <a href="{{ route('customers.index') }}" class="btn modern-btn modern-btn-secondary">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Quick Help Section -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card modern-card">
                <div class="card-header modern-header info-header">
                    <h3 class="card-title">
                        <i class="fas fa-info-circle"></i> Quick Help
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <div class="help-item">
                                <div class="help-icon bg-success">
                                    <i class="fas fa-user"></i>
                                </div>
                                <div class="help-content">
                                    <h6>Customer Name</h6>
                                    <p>Enter the full name of the customer. This field is required and must be unique.</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="help-item">
                                <div class="help-icon bg-info">
                                    <i class="fas fa-phone"></i>
                                </div>
                                <div class="help-content">
                                    <h6>Phone Number</h6>
                                    <p>Enter a valid phone number. This field is required and must be unique.</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="help-item">
                                <div class="help-icon bg-warning">
                                    <i class="fas fa-dollar-sign"></i>
                                </div>
                                <div class="help-content">
                                    <h6>Opening Balance</h6>
                                    <p>Set the initial balance for this customer. Can be positive, negative, or zero.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('additional_css')
<style>
/* Form Grid Layout */
.form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.form-group-modern.full-width {
    grid-column: 1 / -1;
}

.form-group-modern.half-width {
    grid-column: span 1;
}

.form-group-modern {
    margin-bottom: 0;
}

/* Modern Form Styling */
.modern-form {
    padding: 0;
}

.modern-label {
    display: flex;
    align-items: center;
    font-weight: 600;
    color: #333;
    margin-bottom: 0.5rem;
    font-size: 0.9rem;
}

.modern-label i {
    margin-right: 0.5rem;
    color: var(--primary-color);
    width: 16px;
}

.modern-input {
    width: 100%;
    padding: 0.75rem 1rem;
    border: 2px solid var(--border-color);
    border-radius: var(--border-radius);
    font-size: 0.95rem;
    transition: all 0.3s ease;
    background: white;
}

.modern-input:focus {
    outline: none;
    border-color: var(--primary-color);
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.modern-input::placeholder {
    color: var(--text-muted);
}

/* Form Actions */
.form-actions {
    display: flex;
    gap: 1rem;
    justify-content: center;
    padding-top: 1.5rem;
    border-top: 1px solid var(--border-color);
}

/* Modern Alert Styling */
.modern-alert {
    display: flex;
    align-items: flex-start;
    padding: 1rem 1.5rem;
    border-radius: var(--border-radius);
    margin-bottom: 1.5rem;
}

.modern-alert-danger {
    background: linear-gradient(135deg, #fee 0%, #fdd 100%);
    border-left: 4px solid #dc3545;
}

.alert-icon {
    margin-right: 1rem;
    font-size: 1.25rem;
    color: #dc3545;
}

.alert-content h5 {
    margin: 0 0 0.5rem 0;
    color: #721c24;
    font-weight: 600;
}

.error-list {
    margin: 0;
    padding-left: 1.25rem;
    color: #721c24;
}

.error-list li {
    margin-bottom: 0.25rem;
}

/* Help Section */
.help-item {
    display: flex;
    align-items: flex-start;
    text-align: left;
}

.help-icon {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 1rem;
    color: white;
    font-size: 1rem;
    flex-shrink: 0;
}

.help-content h6 {
    margin: 0 0 0.5rem 0;
    font-weight: 600;
    color: #333;
    font-size: 0.95rem;
}

.help-content p {
    margin: 0;
    color: var(--text-muted);
    font-size: 0.85rem;
    line-height: 1.4;
}

/* Responsive Design */
@media (max-width: 768px) {
    .form-grid {
        grid-template-columns: 1fr;
        gap: 1rem;
    }
    
    .form-group-modern.half-width {
        grid-column: 1;
    }
    
    .form-actions {
        flex-direction: column;
    }
    
    .help-item {
        flex-direction: column;
        text-align: center;
    }
    
    .help-icon {
        margin-right: 0;
        margin-bottom: 0.75rem;
        align-self: center;
    }
}
/* Add to your existing CSS */
.form-group-modern.focused .modern-label {
    color: var(--primary-color);
    transform: translateY(-2px);
    transition: all 0.2s ease;
}

.modern-input.is-valid {
    border-color: #28a745;
    box-shadow: 0 0 0 2px rgba(40, 167, 69, 0.1);
}

.modern-input.is-invalid {
    border-color: #dc3545;
    box-shadow: 0 0 0 2px rgba(220, 53, 69, 0.1);
}

/* Smooth scrolling for the entire page */
html {
    scroll-behavior: smooth;
}

/* Hardware acceleration for better performance */
.modern-input, .modern-label {
    transform: translateZ(0);
    backface-visibility: hidden;
}

/* Remove spinner arrows from number inputs */
/* Chrome, Safari, Edge, Opera */
input[type="number"]::-webkit-outer-spin-button,
input[type="number"]::-webkit-inner-spin-button {
    -webkit-appearance: none;
    margin: 0;
}

/* Firefox */
input[type="number"] {
    -moz-appearance: textfield;
}

/* Duplicate warning */
.duplicate-warning {
    margin-top: 0.75rem;
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
@stop

@section('additional_js')
<script>
$(document).ready(function() {
    // Initialize Select2 for customer group dropdown
    $('#account_group_id').select2({
        placeholder: '-- Select Group (Optional) --',
        allowClear: true,
        width: '100%'
    });

    let duplicateCheckTimeout = null;
    let hasDuplicate = false;

    function renderDuplicateWarning(customers) {
        if (!customers || !customers.length) {
            $('#customer-duplicate-warning').hide().empty();
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

        $('#customer-duplicate-warning').html(html).show();
    }

    function checkDuplicateCustomer() {
        const name = $('#name').val().trim();
        const phone = $('#phone').val().trim();

        if (name.length < 2 && phone.length < 6) {
            hasDuplicate = false;
            $('#customer-duplicate-warning').hide().empty();
            return;
        }

        $.ajax({
            url: '{{ route("customers.check-duplicate") }}',
            method: 'GET',
            data: { name: name, phone: phone },
            success: function(response) {
                if (response.exists) {
                    hasDuplicate = true;
                    renderDuplicateWarning(response.customers || []);
                } else {
                    hasDuplicate = false;
                    $('#customer-duplicate-warning').hide().empty();
                }
            },
            error: function() {
                hasDuplicate = false;
                $('#customer-duplicate-warning').hide().empty();
            }
        });
    }
    // Debounce utility function
    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }
    
    // Smooth validation feedback
    function smoothValidation(input, isValid) {
        const $input = $(input);
        $input.removeClass('is-valid is-invalid');
        
        requestAnimationFrame(() => {
            if (isValid) {
                $input.addClass('is-valid');
            } else {
                $input.addClass('is-invalid');
            }
        });
    }
    
    // Optimized name capitalization
    const optimizedCapitalize = debounce(function(input) {
        const value = input.value;
        const cursorPosition = input.selectionStart;
        
        // Capitalize first letter of each word
        const capitalizedValue = value.replace(/\b\w/g, match => match.toUpperCase());
        
        if (value !== capitalizedValue) {
            // Use requestAnimationFrame for smooth updates
            requestAnimationFrame(() => {
                input.value = capitalizedValue;
                input.setSelectionRange(cursorPosition, cursorPosition);
            });
        }
    }, 150);
    
    // Enhanced name input handler
    $('#name').on('input', function(e) {
        const value = $(this).val();
        
        // Immediate validation feedback
        smoothValidation(this, value.length > 0);
        
        // Debounced capitalization
        optimizedCapitalize(this);

        clearTimeout(duplicateCheckTimeout);
        duplicateCheckTimeout = setTimeout(checkDuplicateCustomer, 500);
    });
    
    // Enhanced phone input handler
    $('#phone').on('input', function(e) {
        const $this = $(this);
        let value = $this.val().replace(/\D/g, '');
        
        if (value.length <= 11) {
            $this.val(value);
            smoothValidation(this, value.length >= 10);
        }

        clearTimeout(duplicateCheckTimeout);
        duplicateCheckTimeout = setTimeout(checkDuplicateCustomer, 500);
    });
    
    // Smooth focus effects
    $('.modern-input').on('focus', function() {
        $(this).closest('.form-group-modern').addClass('focused');
    }).on('blur', function() {
        $(this).closest('.form-group-modern').removeClass('focused');
        
        // Validation on blur
        if ($(this).prop('required') && !$(this).val()) {
            smoothValidation(this, false);
        }
    });
    
    // Enhanced form submission
    $('.modern-form').on('submit', function(e) {
        let isValid = true;
        const $form = $(this);
        
        // Smooth validation for all required fields
        $form.find('[required]').each(function() {
            const hasValue = $(this).val().trim() !== '';
            smoothValidation(this, hasValue);
            if (!hasValue) isValid = false;
        });
        
        if (!isValid || hasDuplicate) {
            e.preventDefault();
            
            // Smooth scroll to first invalid field
            const $firstInvalid = $form.find('.is-invalid').first();
            if ($firstInvalid.length) {
                $('html, body').animate({
                    scrollTop: $firstInvalid.offset().top - 100
                }, 300);
            }

            if (hasDuplicate) {
                $('html, body').animate({
                    scrollTop: $('#customer-duplicate-warning').offset().top - 120
                }, 300);
            }
        }
    });
});
</script>
@stop
