@extends('layouts.modern-admin')
<style>
.payment-methods {
    display: none;
}
.loading-state {
    background-color: #f8f9fa;
    min-height: 200px;
}
.overpayment-indicator {
    color: #28a745;
    font-weight: bold;
}
.underpayment-indicator {
    color: #dc3545;
    font-weight: bold;
}
</style>
@section('title', 'Create Invoice')

@section('page_title', 'Create New Invoice')

@section('header_actions')
    <!-- Calculator now floats bottom-right -->
@stop

@section('page_content')
    <form action="{{ route('invoices.store') }}" method="POST" id="invoice-form">
        @csrf
        <input type="hidden" name="idempotency_key" value="{{ old('idempotency_key', (string) \Illuminate\Support\Str::uuid()) }}">
        
        <!-- Invoice Details Header -->
        <div class="card modern-card">
            <div class="card-header modern-header">
                <h3 class="card-title"><i class="fas fa-file-invoice"></i> Invoice Details</h3>
            </div>
            <div class="card-body">
                <!-- Invoice Type and Delivery Status Row -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <label class="form-label">Invoice Type</label>
                        <div class="toggle-group">
                            <input type="radio" id="invoice_type_tiles" name="invoice_type" value="tiles" 
                                   {{ (!isset($defaultInvoiceType) || $defaultInvoiceType === 'tiles') ? 'checked' : '' }}>
                            <label for="invoice_type_tiles" class="toggle-option">
                                <i class="fas fa-th-large"></i> Tiles
                            </label>
                            
                            <input type="radio" id="invoice_type_other" name="invoice_type" value="other" 
                                   {{ (isset($defaultInvoiceType) && $defaultInvoiceType === 'other') ? 'checked' : '' }}>
                            <label for="invoice_type_other" class="toggle-option">
                                <i class="fas fa-cube"></i> Other
                            </label>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <label class="form-label">Delivery Status</label>
                        <div class="toggle-group">
                            <input type="radio" id="delivery_pending" name="delivery_status" value="pending" checked>
                            <label for="delivery_pending" class="toggle-option">
                                <i class="fas fa-clock"></i> Pending
                            </label>
                            
                            <input type="radio" id="delivery_delivered" name="delivery_status" value="delivered">
                            <label for="delivery_delivered" class="toggle-option">
                                <i class="fas fa-check"></i> Delivered
                            </label>
                        </div>
                    </div>
                </div>
                    
                    <!-- Invoice Info and Customer Info Row -->
                    <div class="row">
                        <!-- Invoice Information -->
                        <div class="col-md-5">
                            <div class="info-section">
                                <div class="section-header">
                                    <i class="fas fa-info-circle"></i> Invoice Info
                                </div>
                                <div class="section-content">
                                    <div class="form-group">
                                        <label for="invoice_number_display">Invoice Number <span class="required">*</span></label>
                                        <input type="text" id="invoice_number_display" class="form-control modern-input" 
                                               value="{{ $invoice_number }}" readonly>
                                        <small class="form-text">Auto-generated upon saving</small>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="invoice_date">Invoice Date <span class="required">*</span></label>
                                        <input type="date" name="invoice_date" id="invoice_date" class="form-control modern-input" value="{{ $invoice_date }}" required>
                                    </div>

                                </div>
                            </div>
                        </div>
                        
                        <!-- Customer Information -->
                        <div class="col-md-7">
                            <div class="info-section">
                                <div class="section-header">
                                    <i class="fas fa-user"></i> Customer Information
                                </div>
                                <div class="section-content">
                                    <div class="row">
                                        <div class="col-md-8">
                                            <div class="form-group">
                                                <label for="customer_id">Customer <span class="required">*</span></label>
                                                <div class="input-group">
                                                    <select name="customer_id" id="customer_id" class="form-control select2 modern-select" required tabindex="1">
                                                        <option value="">Select Customer</option>
                                                        @foreach($customers as $customer)
                                                            <option value="{{ $customer->id }}" data-phone="{{ $customer->phone }}" data-address="{{ $customer->address }}">
                                                               {{ $customer->name }}{{ $customer->phone ? ' - ' . $customer->phone : '' }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    <div class="input-group-append">
                                                        <button type="button" class="btn btn-success add-btn" data-toggle="modal" data-target="#newCustomerModal" tabindex="-1">
                                                            <i class="fas fa-plus"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>Outstanding Balance</label>
                                                <input type="text" id="customer_balance" class="form-control modern-input" readonly>
                                                <small class="form-text">Current balance</small>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="customer_phone">Phone</label>
                                                <input type="text" id="customer_phone" name="customer_phone" class="form-control modern-input" readonly tabindex="-1">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="customer_address">Address</label>
                                                <input type="text" id="customer_address" name="customer_address" class="form-control modern-input" readonly tabindex="-1">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label for="referrer_id">Referrer</label>
                                                <div class="input-group">
                                                    <select name="referrer_id" id="referrer_id" class="form-control select2 modern-select">
                                                        <option value="">Select Referrer (Optional)</option>
                                                        @foreach($referrers as $referrer)
                                                            <option value="{{ $referrer->id }}" {{ old('referrer_id') == $referrer->id ? 'selected' : '' }}>
                                                                {{ $referrer->name }}{{ $referrer->phone ? ' - ' . $referrer->phone : '' }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    <div class="input-group-append">
                                                        <button type="button" class="btn btn-success add-btn" data-toggle="modal" data-target="#newReferrerModal" tabindex="-1">
                                                            <i class="fas fa-plus"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                                <small class="form-text">Optional referral source</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Products Section -->
            <div class="card modern-card mt-4">
                <div class="card-header modern-header products-header">
                    <h3 class="card-title"><i class="fas fa-boxes"></i> Products</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-sm btn-light" data-toggle="modal" data-target="#newProductModal">
                            <i class="fas fa-plus"></i> New Product
                        </button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table modern-table compact-table" id="product-table">
                            <thead>
                                <tr>
                                    <th style="width: 22%;">Product <span class="required">*</span></th>
                                    <th style="width: 15%;">Description</th>
                                    <th style="width: 8%;">Qty <span class="required">*</span></th>
                                    <th class="box-pieces-column" style="width: 6%;">Box</th>
                                    <th class="box-pieces-column" style="width: 6%;">Pcs</th>
                                    <th style="width: 10%;">Price <span class="required">*</span></th>
                                    <th style="width: 10%;">Total</th>
                                    <th style="width: 8%;">Act</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="product-row">
                                    <td>
                                        <select name="product_id[]" class="form-control select2 product-select modern-select" required tabindex="5">
                                            <option value="">Select Product</option>
                                            @foreach($products as $product)
                                                 <option value="{{ $product->id }}"
                                                        data-price="{{ $product->sale_price }}"
                                                        data-stock="{{ $product->current_stock }}"
                                                        data-purchase="{{ $product->purchase_price }}"
                                                        data-company="{{ $product->company->name ?? '' }}"
                                                        data-category="{{ $product->category->name ?? '' }}">
                                                    {{ $product->name }} ({{ $product->current_stock }})
                                                </option>
                                            @endforeach
                                            <option value="__new__">+ New Product</option>
                                        </select>
                                        <input type="hidden" class="product-company-hidden" value="">
                                        <input type="hidden" class="product-category-hidden" value="">
                                    </td>
                                    <td>
                                        <input type="text" name="description[]" class="form-control product-description modern-input compact-input" required placeholder="Desc">
                                        <input type="text" name="code[]" class="form-control product-code modern-input compact-input mt-1" placeholder="Code">
                                    </td>
                                    <td>
                                        <input type="number" name="quantity[]" class="form-control product-quantity modern-input compact-input" step="0.01" min="0.01" required placeholder="0">
                                    </td>
                                    <td class="box-pieces-cell">
                                        <input type="number" name="boxes[]" class="form-control product-boxes modern-input compact-input" readonly>
                                    </td>
                                    <td class="box-pieces-cell">
                                        <input type="number" name="pieces[]" class="form-control product-pieces modern-input compact-input" readonly>
                                    </td>
                                    <td>
                                        <input type="number" name="unit_price[]" class="form-control product-price modern-input compact-input" step="0.01" min="0" required placeholder="0">
                                    </td>
                                    <td>
                                        <input type="number" name="item_total[]" class="form-control product-total modern-input compact-input" step="0.01" min="0" readonly required>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <button type="button" class="btn btn-danger btn-xs remove-row" title="Remove">
                                                <i class="fas fa-times"></i>
                                            </button>
                                            <button type="button" class="btn btn-success btn-xs add-product-row" title="Add Row">
                                                <i class="fas fa-plus"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                            <tfoot>
                                <!-- Totals Row -->
                                <tr class="totals-row">
                                    <td colspan="2" class="text-right font-weight-bold">Totals:</td>
                                    <td>
                                        <input type="text" id="total_quantity" class="form-control modern-input compact-input font-weight-bold" readonly>
                                    </td>
                                    <td class="box-pieces-cell">
                                        <input type="text" id="total_boxes" class="form-control modern-input compact-input font-weight-bold" readonly>
                                    </td>
                                    <td class="box-pieces-cell">
                                        <div>
                                            <input type="text" id="total_pieces" class="form-control modern-input compact-input font-weight-bold" readonly>
                                            <small class="text-muted d-block mt-1" id="total_weight_display">Apprx. 0.00 kg</small>
                                        </div>
                                    </td>
                                    <td colspan="3">
                                        <button type="button" class="btn btn-success btn-sm add-product-row-bottom">
                                            <i class="fas fa-plus"></i> Add Row
                                        </button>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
            
            <!-- Payment and Totals Row -->
            <div class="row mt-4">
                <!-- Payment Information -->
                <div class="col-md-6">
                    <div class="card modern-card">
                        <div class="card-header payment-header">
                            <h3 class="card-title"><i class="fas fa-credit-card"></i> Payment Information</h3>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label for="payment_method">Payment Method <span class="required">*</span></label>
                                <select name="payment_method" id="payment_method" class="form-control modern-select" required>
                                    <option value="cash" data-account-type="cash">Cash</option>
                                    <option value="bank" data-account-type="bank">Bank Transfer</option>
                                    <option value="mobile_bank" data-account-type="bank">Mobile Banking</option>
                                    <option value="cheque" data-account-type="bank">Cheque</option>
                                </select>
                            </div>

                            <!-- Accounting Integration: Account Selection -->
                            @if(isset($cashBankAccounts) && $cashBankAccounts->count() > 0)
                            <div class="form-group">
                                <label for="account_id">
                                    <i class="fas fa-university"></i> Cash/Bank Account
                                </label>
                                <select name="account_id" id="account_id" class="form-control modern-select">
                                    <option value="">Auto-select based on method</option>
                                    @foreach ($cashBankAccounts as $account)
                                        @php
                                            $balance = $account->running_balance;
                                            $balanceStr = number_format($balance['balance'], 2) . ' ' . ($balance['balance_type'] === 'debit' ? 'Dr' : 'Cr');
                                            $isDefault = ($account->code === 'CASH-PRIMARY') ? 'true' : 'false';
                                        @endphp
                                        <option value="{{ $account->id }}"
                                            data-type="{{ $account->account_type }}"
                                            data-code="{{ $account->code }}"
                                            data-balance="{{ $balance['balance'] }}"
                                            data-balance-type="{{ $balance['balance_type'] }}"
                                            data-default="{{ $isDefault }}"
                                            {{ $account->code === 'CASH-PRIMARY' ? 'selected' : '' }}>
                                            {{ $account->name }} [৳{{ $balanceStr }}]
                                        </option>
                                    @endforeach
                                </select>
                                <small class="form-text text-muted"><i class="fas fa-info-circle"></i> Select account for accounting integration</small>
                            </div>
                            @endif

                            <div class="form-group">
                                <label for="notes">Notes</label>
                                <textarea name="notes" id="notes" class="form-control modern-textarea" rows="3" placeholder="Additional notes or comments..."></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Invoice Totals -->
                <div class="col-md-6">
                    <div class="card modern-card">
                        <div class="card-header totals-header">
                            <h3 class="card-title"><i class="fas fa-calculator"></i> Invoice Totals</h3>
                        </div>
                        <div class="card-body">
                            <div class="totals-section">
                                <div class="total-row">
                                    <label>Subtotal:</label>
                                    <input type="number" name="subtotal" id="subtotal" class="form-control modern-input" step="0.01" readonly>
                                </div>
                                
                                <div class="total-row">
                                    <label>Discount:</label>
                                    <input type="number" name="discount" id="discount" class="form-control modern-input" step="0.01" min="0" value="0">
                                </div>
                                
                                <div class="total-row total-main">
                                    <label>Total:</label>
                                    <input type="number" name="total" id="total" class="form-control modern-input total-input" step="0.01" min="0" readonly required>
                                </div>
                                
                                <div class="total-row">
                                    <label>Paid Amount:</label>
                                    <input type="number" name="paid_amount" id="paid_amount" class="form-control modern-input" step="0.01" min="0" value="0" required>
                                </div>
                                
                                <div class="total-row">
                                    <label>Due Amount:</label>
                                    <div class="input-group">
                                        <input type="number" name="due_amount" id="due_amount" class="form-control modern-input" step="0.01" readonly>
                                        <div class="input-group-append">
                                            <span class="input-group-text" id="payment-status-indicator"></span>
                                        </div>
                                    </div>
                                    <small class="form-text text-muted">Negative value indicates overpayment/credit</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Submit Buttons -->
        <div class="text-center mt-4 mb-4">
            <button type="submit" class="btn modern-btn modern-btn-primary btn-lg" id="save-invoice-btn">
                <i class="fas fa-save"></i> Save Invoice
            </button>
            <a href="{{ route('invoices.index') }}" class="btn btn-outline-secondary btn-lg ml-3">
                <i class="fas fa-times"></i> Cancel
            </a>
        </div>
    </form>
            
    
    <!-- Include modals -->
    <x-product-modal :companies="$companies" :categories="$categories" />
    <x-customer-modal />
    <x-referrer-modal />
    <x-decor-calculator-modal />
@stop

@section('additional_css')
<style>
/* Hide number input spinners */
input::-webkit-outer-spin-button,
input::-webkit-inner-spin-button {
    -webkit-appearance: none;
    margin: 0;
}
input[type=number] {
    -moz-appearance: textfield;
}

/* Compact Table Styles */
.compact-table {
    font-size: 13px;
}

.compact-table th {
    padding: 8px 4px;
    font-size: 12px;
    font-weight: 600;
    white-space: nowrap;
    background: #f8f9fa;
}

.compact-table td {
    padding: 4px;
    vertical-align: middle;
}

.compact-input {
    padding: 4px 8px;
    font-size: 13px;
    height: auto;
}

.compact-input::placeholder {
    color: #adb5bd;
    font-size: 11px;
}

/* Product select styling */
.compact-table .select2-container .select2-selection--single {
    height: 32px;
    padding: 2px 0;
}

.compact-table .select2-container--default .select2-selection--single .select2-selection__rendered {
    line-height: 28px;
    font-size: 12px;
}

.compact-table .select2-container--default .select2-selection--single .select2-selection__arrow {
    height: 30px;
}

/* Action buttons */
.btn-xs {
    padding: 2px 6px;
    font-size: 11px;
}

.compact-table .btn-group-sm > .btn {
    padding: 3px 8px;
}

/* SFT Suggestion styling */
.sft-suggestion {
    margin-top: 4px;
    padding: 4px 8px;
    background: linear-gradient(135deg, rgba(23, 162, 184, 0.1) 0%, rgba(23, 162, 184, 0.05) 100%);
    border: 1px solid rgba(23, 162, 184, 0.3);
    border-radius: 6px;
}

.sft-suggestion small {
    font-size: 11px;
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 4px;
}

.sft-suggestion .btn-xs {
    padding: 1px 6px;
    font-size: 10px;
    line-height: 1.4;
}

/* Totals row */
.totals-row {
    background-color: #f8f9fa;
    border-top: 2px solid #dee2e6;
}

.totals-row td {
    padding: 8px 4px;
    vertical-align: middle;
}

.totals-row input {
    background-color: #e9ecef;
    border: 1px solid #ced4da;
    font-weight: bold;
    text-align: center;
}

/* Payment status indicators */
.payment-status-overpaid {
    background-color: #d4edda;
    color: #155724;
}

.payment-status-underpaid {
    background-color: #f8d7da;
    color: #721c24;
}

.payment-status-exact {
    background-color: #d1ecf1;
    color: #0c5460;
}

/* New row highlight */
.new-row {
    animation: highlightRow 1.5s ease-out;
}

@keyframes highlightRow {
    0% { background-color: #d4edda; }
    100% { background-color: transparent; }
}

/* Card tools alignment */
.products-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.products-header .card-tools {
    margin-left: auto;
}
</style>
@stop

@section('additional_js')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    $(document).ready(function() {
        // Initialize Select2
        initializeSelect2();

        // Initialize calculations on page load
        calculateDueAmount();
        calculateProductTotals();

        // Auto-select accounting account based on payment method
        $('#payment_method').on('change', function() {
        const method = $(this).val();
        const accountSelect = $('#account_id');

        if (accountSelect.length === 0) return;

        // Map payment methods to account types
        const methodToType = {
            'cash': 'cash',
            'bank': 'bank',
            'mobile_bank': 'bank',
            'cheque': 'bank'
        };

        const targetType = methodToType[method] || 'cash';

        // Find the appropriate account
        let selectedAccount = null;
        accountSelect.find('option').each(function() {
            const $opt = $(this);
            const accType = $opt.data('type');
            const code = $opt.data('code');

            if (accType === targetType) {
                // Prefer primary accounts
                if (code === 'CASH-PRIMARY' && targetType === 'cash') {
                    selectedAccount = $opt.val();
                    return false; // break
                }
                if (code === 'BANK-PRIMARY' && targetType === 'bank') {
                    selectedAccount = $opt.val();
                    return false; // break
                }
                if (code === 'MOBILE-BANK' && method === 'mobile_bank') {
                    selectedAccount = $opt.val();
                    return false; // break
                }
                // Fallback to first matching type
                if (!selectedAccount && $opt.val()) {
                    selectedAccount = $opt.val();
                }
            }
        });


        if (selectedAccount) {
            accountSelect.val(selectedAccount).trigger('change');
        }
    });

    // Trigger initial account selection based on default payment method
    $('#payment_method').trigger('change');
    
    // Enhanced tab navigation that works with Enter key
    $(document).on('keydown', '.product-select, .product-description, .product-quantity, .product-price', function(e) {
        const isEnter = e.keyCode === 13 || e.which === 13;
        const isTab = e.keyCode === 9 || e.which === 9;
        const isShiftTab = isTab && e.shiftKey;
        
        if (isEnter || (isTab && !isShiftTab)) {
            const currentRow = $(this).closest('tr');
            const currentField = $(this);
            
            // Prevent default behavior for Enter
            if (isEnter) {
                e.preventDefault();
            }
            
            // Define field order for navigation
            const fieldOrder = ['.product-select', '.product-description', '.product-quantity', '.product-price'];
            const currentFieldClass = getCurrentFieldClass(currentField);
            const currentIndex = fieldOrder.indexOf(currentFieldClass);
            
            // Check if we're on the last field of the row
            if (currentIndex === fieldOrder.length - 1) {
                const productId = currentRow.find('.product-select').val();
                const quantity = currentRow.find('.product-quantity').val();
                const price = currentRow.find('.product-price').val();
                
                // Only create new row if essential data is present
                if (productId && quantity && price) {
                    if (isTab) e.preventDefault(); // Prevent default tab behavior
                    
                    setTimeout(() => {
                        const newRow = addProductRowAfter(currentRow);
                        newRow.find('.product-select').focus();
                        
                        // Open Select2 dropdown for immediate selection
                        if (newRow.find('.product-select').hasClass('select2-hidden-accessible')) {
                            newRow.find('.product-select').select2('open');
                        }
                    }, 50);
                }
            } else if (isEnter) {
                // Move to next field in same row for Enter key
                const nextFieldClass = fieldOrder[currentIndex + 1];
                const nextField = currentRow.find(nextFieldClass);
                
                if (nextField.hasClass('select2-hidden-accessible')) {
                    nextField.select2('open');
                } else {
                    nextField.focus();
                }
            }
        }
    });

    // Show modal when "+ New Product" is selected
    $(document).on('change', '.product-select', function() {
        if ($(this).val() === '__new__') {
            // Track which row triggered the modal for auto-selection after creation
            window.productModalTriggerRow = $(this).closest('tr');
            $('#newProductModal').modal('show');
            $('#new-product-form')[0].reset();
            $(this).val('').trigger('change');
        } else {
            handleProductSelection($(this));
        }
    });

    // Product modal logic handled in components/product-modal.blade.php

    // Initial state setup
    const initialInvoiceType = $('input[name="invoice_type"]:checked').val();
    toggleBoxPiecesColumns(initialInvoiceType === 'tiles');

    // Event handlers
    $('input[name="invoice_type"]').change(function() {
        const isTiles = $(this).val() === 'tiles';
        toggleBoxPiecesColumns(isTiles);
        toggleTotalsColumns(isTiles);
    });
    
    // Handle delivery status change with stock validation
    $('input[name="delivery_status"]').change(function() {
        const deliveryStatus = $(this).val();
        const isDelivered = deliveryStatus === 'delivered';
        
        toggleStockWarnings(isDelivered);
        
        // Clear ALL stock warnings when switching away from delivered
        if (!isDelivered) {
            $('.product-row').each(function() {
                $(this).removeClass('insufficient-stock');
                $(this).find('.product-quantity').removeClass('is-invalid');
                $(this).find('.stock-warning-text').remove();
            });
        } else {
            // Re-validate all rows when switching to delivered
            $('.product-row').each(function() {
                validateRowStock($(this));
            });
        }
    });
    
    // Add product row handlers - multiple ways to add rows
    $('#add-product-row').click(addProductRow);
    $('.add-product-row-bottom').click(addProductRow);
    
    // Add row from the + button in each row
    $(document).on('click', '.add-product-row', function() {
        const newRow = addProductRowAfter($(this).closest('tr'));
        // Focus on the newly added row's product select
        newRow.find('.product-select').select2('open');
    });
    
    // Remove product row handler
    $(document).on('click', '.remove-row', function() {
        if ($('.product-row').length > 1) {
            $(this).closest('tr').remove();
            calculateInvoiceTotal();
            calculateProductTotals(); // Update totals after removal
        } else {
            alert('At least one product is required.');
        }
    });

    // Apply sft suggestion when clicked
    $(document).on('click', '.apply-suggestion', function(e) {
        e.preventDefault();
        const suggestedSft = parseFloat($(this).data('sft'));
        const row = $(this).closest('tr');
        row.find('.product-quantity').val(suggestedSft).trigger('input');
    });

    // Enhanced calculation event handlers
    $(document).on('input', '.product-quantity, .product-price', function() {
        const row = $(this).closest('tr');
        calculateRowTotal(row);
        calculateInvoiceTotal();
        
        // If it's a quantity change, also update boxes/pieces
        if ($(this).hasClass('product-quantity')) {
            calculateBoxesAndPieces(row);
            
            // ONLY validate stock for delivered orders
            const deliveryStatus = $('input[name="delivery_status"]:checked').val();
            if (deliveryStatus === 'delivered') {
                validateRowStock(row);
            } else {
                // Clear any existing stock warnings for non-delivered orders
                row.removeClass('insufficient-stock');
                row.find('.product-quantity').removeClass('is-invalid');
                row.find('.stock-warning-text').remove();
            }
        }
    });

    // Make sure due amount is calculated when discount or paid amount changes
    $('#discount').on('input', function() {
        calculateInvoiceTotal();
    });
    
    $('#paid_amount').on('input', function() {
        calculateDueAmount();
        updatePaymentStatusIndicator();
    });
    
    // Also calculate when total changes
    $('#total').on('change input', function() {
        calculateDueAmount();
        updatePaymentStatusIndicator();
    });
    
    // Product selection handler
    $(document).on('change', '.product-select', function() {
        if ($(this).val() !== '__new__') {
            handleProductSelection($(this));
        }
    });
    
    // Customer selection handler
    $('#customer_id').change(function() {
        handleCustomerSelection($(this).val());
    });
    
    // Enhanced form submission handler with stock validation and SweetAlert
    $('#invoice-form').on('submit', function(e) {
        e.preventDefault(); // Always prevent default submission
        
        const form = this;
        const formData = new FormData(form);
        
        // Basic validation first
        let isValid = true;
        $(form).find('input[required], select[required]').each(function() {
            if (!validateField(this)) {
                isValid = false;
            }
        });
        
        if (!isValid) {
            const firstInvalid = $(form).find('.is-invalid').first();
            if (firstInvalid.length) {
                $('html, body').animate({
                    scrollTop: firstInvalid.offset().top - 100
                }, 200);
                firstInvalid.focus();
            }
            return false;
        }
        
        // Submit via AJAX to handle stock validation
        submitInvoiceForm(formData);
    });
    
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

    // Initialize tooltips
    $('[data-toggle="tooltip"]').tooltip();

    // For customer modal
    $('#newCustomerModal').on('shown.bs.modal', function() {
        $(this).find('.select2').each(function() {
            $(this).select2({
                dropdownParent: $('#newCustomerModal'),
                width: '100%'
            });
        });
    });

    // Add event listeners for inline validation
    $(document).on('blur', 'input[required], select[required]', function() {
        validateField(this);
    });

    // Focus handler for Select2
    $(document).on('select2:open', () => {
        document.querySelector('.select2-search__field').focus();
    });
    
});

// Helper Functions
function getCurrentFieldClass(field) {
    if (field.hasClass('product-select')) return '.product-select';
    if (field.hasClass('product-description')) return '.product-description';
    if (field.hasClass('product-quantity')) return '.product-quantity';
    if (field.hasClass('product-price')) return '.product-price';
    return '';
}

function initializeSelect2() {
    $('.select2').each(function() {
        let options = {
            width: '100%',
            dropdownAutoWidth: true
        };
        
        // If the select is inside a modal, set the dropdown parent
        const modalParent = $(this).closest('.modal');
        if (modalParent.length) {
            options.dropdownParent = modalParent;
        }
        
        $(this).select2(options);
    });
}

// Function to calculate and update all product totals
function calculateProductTotals() {
    let totalQuantity = 0;
    let totalBoxes = 0;
    let totalPieces = 0;
    let totalWeight = 0;
    
    $('.product-row').each(function() {
        const row = $(this);
        const quantity = parseFloat(row.find('.product-quantity').val()) || 0;
        const boxes = parseInt(row.find('.product-boxes').val()) || 0;
        const pieces = parseInt(row.find('.product-pieces').val()) || 0;
        const boxPcs = parseFloat(row.data('box-pcs')) || 0;
        const piecesFeet = parseFloat(row.data('pieces-feet')) || 0;
        const weightValue = parseFloat(row.data('weight-value')) || 0;
        const weightUnit = row.data('weight-unit') || '';
        
        totalQuantity += quantity;
        totalBoxes += boxes;
        totalPieces += pieces;

        if (weightValue > 0 && weightUnit) {
            let totalRowPieces = 0;
            if (boxPcs > 0) {
                totalRowPieces = (boxes * boxPcs) + pieces;
            } else if (pieces > 0) {
                totalRowPieces = pieces;
            } else if (piecesFeet > 0 && quantity > 0) {
                totalRowPieces = quantity / piecesFeet;
            }

            if (weightUnit === 'per_piece') {
                totalWeight += totalRowPieces * weightValue;
            } else if (weightUnit === 'per_box') {
                const boxCount = boxPcs > 0 ? (totalRowPieces / boxPcs) : boxes;
                totalWeight += boxCount * weightValue;
            } else if (weightUnit === 'per_unit') {
                totalWeight += quantity * weightValue;
            }
        }
    });
    
    // Update the totals display
    $('#total_quantity').val(totalQuantity.toFixed(2));
    $('#total_boxes').val(totalBoxes);
    $('#total_pieces').val(totalPieces);
    $('#total_weight_display').text('Apprx. ' + totalWeight.toFixed(2) + ' kg');
}

// Function to toggle totals visibility based on invoice type
function toggleTotalsColumns(showBoxPieces) {
    if (showBoxPieces) {
        $('.totals-row .box-pieces-cell').show();
    } else {
        $('.totals-row .box-pieces-cell').hide();
    }
}

function addProductRow() {
    // Get the first row as template
    const templateRow = $('.product-row:first');
    
    // Destroy Select2 on template row temporarily
    const templateSelect = templateRow.find('.product-select');
    if (templateSelect.hasClass('select2-hidden-accessible')) {
        templateSelect.select2('destroy');
    }
    
    // Clone the clean row
    const newRow = templateRow.clone();

    // Clear values
    newRow.find('input').val('');
    newRow.find('select').val('').trigger('change');

    // Remove Select2 artifacts
    newRow.find('.select2-container').remove();
    newRow.removeClass('insufficient-stock');
    newRow.find('.is-invalid').removeClass('is-invalid');
    newRow.find('.invalid-feedback').remove();

    // Clear data attributes to prevent suggestion issues
    newRow.removeData('box-pcs');
    newRow.removeData('pieces-feet');
    newRow.removeData('weight-value');
    newRow.removeData('weight-unit');
    newRow.find('.sft-suggestion').remove();
    
    // Add highlighting class
    newRow.addClass('new-row');
    setTimeout(function() {
        newRow.removeClass('new-row');
    }, 1500);
    
    // Insert before the totals row
    $('.totals-row').before(newRow);
    
    // Apply visibility based on invoice type
    if ($('input[name="invoice_type"]:checked').val() !== 'tiles') {
        newRow.find('.box-pieces-cell').hide();
    }
    
    // Reinitialize Select2 on both template and new row
    initializeRowSelect2(templateRow);
    initializeRowSelect2(newRow);
    
    // Focus on the product select in the new row
    newRow.find('.product-select').select2('open');
    
    // Update totals
    calculateProductTotals();
    
    return newRow;
}

function addProductRowAfter(currentRow) {
    // Get the original select element before cloning
    const originalSelect = currentRow.find('.product-select');
    
    // Destroy Select2 on the original row temporarily to get clean HTML
    if (originalSelect.hasClass('select2-hidden-accessible')) {
        originalSelect.select2('destroy');
    }
    
    // Clone the row (now without Select2 artifacts)
    const newRow = currentRow.clone();

    // Clear all input values in the new row
    newRow.find('input').val('');
    newRow.find('select').val('').trigger('change');
    newRow.find('.product-total').val('');

    // Remove any validation classes and Select2 artifacts
    newRow.find('.is-invalid').removeClass('is-invalid');
    newRow.find('.invalid-feedback').remove();
    newRow.removeClass('insufficient-stock');

    // Remove any Select2 containers that might have been cloned
    newRow.find('.select2-container').remove();

    // Clear data attributes to prevent suggestion issues
    newRow.removeData('box-pcs');
    newRow.removeData('pieces-feet');
    newRow.removeData('weight-value');
    newRow.removeData('weight-unit');
    newRow.find('.sft-suggestion').remove();
    
    // Add visual feedback for new row
    newRow.addClass('new-row');
    setTimeout(() => {
        newRow.removeClass('new-row');
    }, 1500);
    
    // Insert after the current row
    currentRow.after(newRow);
    
    // Apply visibility based on invoice type
    if ($('input[name="invoice_type"]:checked').val() !== 'tiles') {
        newRow.find('.box-pieces-cell').hide();
    }
    
    // Reinitialize Select2 on BOTH the original and new row
    initializeRowSelect2(currentRow);
    initializeRowSelect2(newRow);
    
    // Update totals
    calculateProductTotals();
    
    return newRow;
}

function initializeRowSelect2(row) {
    row.find('.select2').each(function() {
        // Ensure any existing Select2 is destroyed first
        if ($(this).hasClass('select2-hidden-accessible')) {
            $(this).select2('destroy');
        }
        
        // Remove any orphaned Select2 containers
        $(this).siblings('.select2-container').remove();
        
        const modalParent = $(this).closest('.modal');
        const options = {
            width: '100%',
            dropdownAutoWidth: true,
            closeOnSelect: true
        };
        
        if (modalParent.length) {
            options.dropdownParent = modalParent;
        }
        
        $(this).select2(options);
    });
}

function calculateRowTotal(row) {
    const quantity = parseFloat(row.find('.product-quantity').val()) || 0;
    const price = parseFloat(row.find('.product-price').val()) || 0;
    const total = quantity * price;
    row.find('.product-total').val(total.toFixed(2));
    
    // Update product totals after row calculation
    calculateProductTotals();
}

function calculateBoxesAndPieces(row) {
    const quantity = parseFloat(row.find('.product-quantity').val()) || 0;
    const boxPcs = parseFloat(row.data('box-pcs')) || 0;
    const piecesFeet = parseFloat(row.data('pieces-feet')) || 0;
    const price = parseFloat(row.find('.product-price').val()) || 0;

    // Clear previous suggestion
    row.find('.sft-suggestion').remove();

    // Only need piecesFeet to calculate pieces (boxPcs is optional)
    if (piecesFeet > 0 && quantity > 0) {
        // Calculate exact pieces: quantity (sft) / sqft per piece = number of pieces
        const exactPieces = quantity / piecesFeet;
        const roundedPieces = Math.round(exactPieces);
        const fractionalDiff = Math.abs(exactPieces - roundedPieces);

        // Check if we have fractional pieces (use tolerance for floating point)
        // Consider it whole if within 0.1 of a whole number (handles floating point precision)
        // e.g., 1499.925 should round to 1500, 158.008 should round to 158
        const tolerance = 0.1;
        const isWholeNumber = fractionalDiff < tolerance;

        // Use rounded value if close to whole, otherwise use exact
        let displayPieces = isWholeNumber ? roundedPieces : exactPieces;

        // Calculate boxes and loose pieces
        if (boxPcs > 0) {
            const boxes = Math.floor(displayPieces / boxPcs);
            const loosePieces = displayPieces - (boxes * boxPcs);
            row.find('.product-boxes').val(boxes);
            // Round loose pieces to handle floating point
            row.find('.product-pieces').val(Math.round(loosePieces));
        } else {
            // No box_pcs, show total pieces only
            row.find('.product-boxes').val(0);
            row.find('.product-pieces').val(Math.round(displayPieces));
        }

        // Show suggestion only if truly fractional (not just floating point error)
        if (!isWholeNumber) {
            const flooredPieces = Math.floor(exactPieces);
            const fractionalPieces = exactPieces - flooredPieces;
            const ceilPieces = Math.ceil(exactPieces);
            const suggestedSft = parseFloat((ceilPieces * piecesFeet).toFixed(2));

            // Verify the suggestion won't create a loop (check if applying it gives whole pieces)
            const verifyPieces = suggestedSft / piecesFeet;
            const verifyDiff = Math.abs(verifyPieces - Math.round(verifyPieces));
            const isCleanSuggestion = verifyDiff < tolerance;

            // Only show suggestion if:
            // 1. Difference is more than 0.10 sft
            // 2. The suggestion results in clean whole pieces (no loop)
            if (Math.abs(suggestedSft - quantity) > 0.10 && isCleanSuggestion) {
                let boxInfo = '';
                if (boxPcs > 0) {
                    const suggestedBoxes = Math.floor(ceilPieces / boxPcs);
                    const suggestedLoosePcs = ceilPieces - (suggestedBoxes * boxPcs);
                    boxInfo = ` (${suggestedBoxes} box + ${suggestedLoosePcs} pcs)`;
                }

                const suggestionHtml = `<div class="sft-suggestion">
                    <small class="text-info d-flex align-items-center flex-wrap">
                        <i class="fas fa-lightbulb text-warning mr-1"></i>
                        <span>For ${ceilPieces} full pcs: <strong>${suggestedSft} sft</strong>${boxInfo}</span>
                        <button type="button" class="btn btn-xs btn-info ml-2 apply-suggestion"
                            data-sft="${suggestedSft}" title="Apply ${suggestedSft} sft">
                            Apply
                        </button>
                    </small>
                </div>`;
                row.find('.product-quantity').closest('td').append(suggestionHtml);
            }
        }
    } else if (boxPcs > 0 && quantity > 0) {
        // Fallback: only boxPcs without piecesFeet - use old calculation
        const totalPieces = Math.round(quantity);
        const boxes = Math.floor(totalPieces / boxPcs);
        const pieces = totalPieces - (boxes * boxPcs);
        row.find('.product-boxes').val(boxes);
        row.find('.product-pieces').val(pieces);
    } else {
        row.find('.product-boxes').val('');
        row.find('.product-pieces').val('');
    }

    // Update product totals after boxes/pieces calculation
    calculateProductTotals();
}

function calculateInvoiceTotal() {
    let subtotal = 0;
    $('.product-total').each(function() {
        const value = parseFloat($(this).val()) || 0;
        subtotal += value;
    });
    
    const discount = parseFloat($('#discount').val()) || 0;
    const total = subtotal - discount;
    
    $('#subtotal').val(subtotal.toFixed(2));
    $('#total').val(total.toFixed(2));
    
    // Always calculate due amount after total changes
    calculateDueAmount();
    updatePaymentStatusIndicator();
}

function calculateDueAmount() {
    const total = parseFloat($('#total').val()) || 0;
    const paidAmount = parseFloat($('#paid_amount').val()) || 0;
    const dueAmount = total - paidAmount; // Allow negative values for overpayment
    $('#due_amount').val(dueAmount.toFixed(2));
    return dueAmount;
}

function updatePaymentStatusIndicator() {
    const total = parseFloat($('#total').val()) || 0;
    const paidAmount = parseFloat($('#paid_amount').val()) || 0;
    const dueAmount = total - paidAmount;
    const indicator = $('#payment-status-indicator');
    const dueField = $('#due_amount');
    
    // Remove all payment status classes
    dueField.removeClass('payment-status-overpaid payment-status-underpaid payment-status-exact');
    
    if (dueAmount < 0) {
        // Overpayment
        indicator.html('<i class="fas fa-arrow-up text-success"></i>').attr('title', 'Overpayment');
        dueField.addClass('payment-status-overpaid');
    } else if (dueAmount > 0) {
        // Underpayment
        indicator.html('<i class="fas fa-arrow-down text-danger"></i>').attr('title', 'Outstanding');
        dueField.addClass('payment-status-underpaid');
    } else {
        // Exact payment
        indicator.html('<i class="fas fa-check text-info"></i>').attr('title', 'Paid in full');
        dueField.addClass('payment-status-exact');
    }
}

function toggleBoxPiecesColumns(show) {
    if (show) {
        $('.box-pieces-column').show();
        $('.box-pieces-cell').show();
    } else {
        $('.box-pieces-column').hide();
        $('.box-pieces-cell').hide();
    }
}

function validateRowStock(row) {
    // Get current delivery status
    const deliveryStatus = $('input[name="delivery_status"]:checked').val();
    
    // Skip ALL stock validation if not delivered
    if (deliveryStatus !== 'delivered') {
        // Clear any existing warnings
        row.removeClass('insufficient-stock');
        row.find('.product-quantity').removeClass('is-invalid');
        row.find('.stock-warning-text').remove();
        return; // Exit function completely
    }
    
    // Only run stock validation for delivered orders
    const quantity = parseFloat(row.find('.product-quantity').val()) || 0;
    const stock = parseFloat(row.find('.product-select option:selected').data('stock')) || 0;
    
    if (quantity > stock) {
        row.addClass('insufficient-stock');
        row.find('.product-quantity').addClass('is-invalid');
        
        if (!row.find('.stock-warning-text').length) {
            row.find('.product-quantity').after('<div class="invalid-feedback stock-warning-text">Exceeds available stock (' + stock + ') - Will result in negative stock</div>');
        }
    } else {
        row.removeClass('insufficient-stock');
        row.find('.product-quantity').removeClass('is-invalid');
        row.find('.stock-warning-text').remove();
    }
}

function toggleStockWarnings(isDelivered) {
    if (isDelivered) {
        $('#delivery-stock-warning').slideDown();
        // Validate stock for all rows
        $('.product-row').each(function() {
            validateRowStock($(this));
        });
    } else {
        $('#delivery-stock-warning').slideUp();
        $('.insufficient-stock').removeClass('insufficient-stock');
        $('.product-quantity.is-invalid').removeClass('is-invalid');
        $('.stock-warning-text').remove();
    }
}

function handleProductSelection(selectElement) {
    const row = selectElement.closest('tr');
    const productId = selectElement.val();
    
    if (productId) {
        $.ajax({
            url: '/product-details/' + productId,
            type: 'GET',
            dataType: 'json',
            success: function(data) {
                row.find('.product-description').val(data.name);
                row.find('.product-company').val(data.company ? data.company.name : '');
                row.find('.product-category').val(data.category ? data.category.name : '');
                row.find('.product-price').val(data.sale_price);
                
                row.data('box-pcs', data.category ? data.category.box_pcs : 0);
                row.data('pieces-feet', data.category ? data.category.pieces_feet : 0);
                // Prioritize product weight over category weight
                const productWeightValue = data.weight_value ? parseFloat(data.weight_value) : 0;
                const productWeightUnit = data.weight_unit || '';
                const categoryWeightValue = data.category && data.category.weight_value ? parseFloat(data.category.weight_value) : 0;
                const categoryWeightUnit = data.category ? (data.category.weight_unit || '') : '';
                row.data('weight-value', productWeightValue > 0 && productWeightUnit ? productWeightValue : categoryWeightValue);
                row.data('weight-unit', productWeightValue > 0 && productWeightUnit ? productWeightUnit : categoryWeightUnit);
                
                row.find('.product-quantity').val('');
                row.find('.product-boxes').val('');
                row.find('.product-pieces').val('');
                row.find('.product-total').val('');
                
                // Update totals after clearing values
                calculateProductTotals();
                
                row.find('.product-quantity').focus();
            },
            error: function() {
                // Silent error handling
            }
        });
    } else {
        // Clear fields if no product selected
        row.find('.product-description, .product-company, .product-category, .product-price, .product-quantity, .product-boxes, .product-pieces, .product-total').val('');
        row.data('box-pcs', 0);
        row.data('pieces-feet', 0);
        row.data('weight-value', 0);
        row.data('weight-unit', '');
        
        // Update totals after clearing values
        calculateProductTotals();
    }
}

function handleCustomerSelection(customerId) {
    if (customerId) {
        $.ajax({
            url: '/customer-details/' + customerId,
            type: 'GET',
            dataType: 'json',
            success: function(data) {
                $('#customer_phone').val(data.phone || '');
                $('#customer_address').val(data.address || '');
                const fallbackValue = parseFloat(data.outstanding_balance ?? 0) || 0;
                const fallbackFormatted = (fallbackValue < 0 ? '-' : '') + Math.abs(fallbackValue).toFixed(2);
                const ledgerDisplay = data.ledger_balance_formatted ?? fallbackFormatted;
                $('#customer_balance').val(ledgerDisplay);
            },
            error: function() {
                // Silent error handling
            }
        });
    } else {
        $('#customer_phone, #customer_address, #customer_balance').val('');
    }
}

function validateField(field) {
    if (field.hasAttribute('required') && !field.value.trim()) {
        field.classList.add('is-invalid');
        if (!field.nextElementSibling || !field.nextElementSibling.classList.contains('invalid-feedback')) {
            const feedback = document.createElement('div');
            feedback.classList.add('invalid-feedback');
            feedback.textContent = 'This field is required';
            field.after(feedback);
        }
        return false;
    } else {
        field.classList.remove('is-invalid');
        const feedback = field.nextElementSibling;
        if (feedback && feedback.classList.contains('invalid-feedback')) {
            feedback.remove();
        }
        return true;
    }
}

function submitInvoiceForm(formData, forceNegativeStock = false) {
    // Check if at least one product row has valid data
    let hasValidProduct = false;
    $('.product-row').each(function() {
        const productId = $(this).find('.product-select').val();
        const quantity = parseFloat($(this).find('.product-quantity').val()) || 0;
        const unitPrice = parseFloat($(this).find('.product-price').val()) || 0;
        const itemTotal = parseFloat($(this).find('.product-total').val()) || 0;
        if (productId && quantity > 0 && unitPrice >= 0 && itemTotal >= 0) {
            hasValidProduct = true;
        }
    });

    if (!hasValidProduct) {
        Swal.fire({
            icon: 'error',
            title: 'Validation Error',
            text: 'At least one product with valid quantity, unit price, and total is required.'
        });
        $('#save-invoice-btn').prop('disabled', false).html('<i class="fas fa-save"></i> Save Invoice');
        return;
    }

    // Validate numeric fields
    const subtotal = parseFloat($('#subtotal').val()) || 0;
    const total = parseFloat($('#total').val()) || 0;
    const paidAmount = parseFloat($('#paid_amount').val()) || 0;

    if (subtotal <= 0 || total <= 0) {
        Swal.fire({
            icon: 'error',
            title: 'Validation Error',
            text: 'Invalid invoice totals. Please check your inputs.'
        });
        $('#save-invoice-btn').prop('disabled', false).html('<i class="fas fa-save"></i> Save Invoice');
        return;
    }

    // Check delivery status - if pending or partial, allow negative stock
    const deliveryStatus = $('input[name="delivery_status"]:checked').val();
    if (deliveryStatus === 'pending' || deliveryStatus === 'partial') {
        forceNegativeStock = true;
    }

    if (forceNegativeStock) {
        formData.set('force_negative_stock', '1');
    }

    // Show loading state
    const submitBtn = $('#save-invoice-btn');
    const originalText = submitBtn.html();
    submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');

    $.ajax({
        url: $('#invoice-form').attr('action'),
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        },
        success: function(response) {
            if (response.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    html: `Invoice created successfully!<br><strong>Invoice Number: ${response.invoice_number || 'Generated'}</strong>`,
                    showConfirmButton: false,
                    timer: 2000
                }).then(() => {
                    window.location.href = response.redirect;
                });
            }
        },
        error: function(xhr) {
            submitBtn.prop('disabled', false).html(originalText);

            if (xhr.status === 422) {
                const response = xhr.responseJSON;
                if (response.stock_issues && deliveryStatus === 'delivered') {
                    showStockWarningAlert(response.stock_issues, formData);
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Validation Error',
                        text: response.message || 'Please check your input and try again.'
                    });
                }
            } else {
                const errorMessage = xhr.responseJSON?.message || 'An error occurred while saving the invoice.';
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: errorMessage
                });
            }
        }
    });
}

function showStockWarningAlert(stockIssues, formData) {
    let warningHtml = '<div class="text-left"><strong>The following products have insufficient stock:</strong><br><br>';
    
    stockIssues.forEach(issue => {
        warningHtml += `<div class="mb-2">
            <strong>${issue.name}</strong><br>
            <span class="text-muted">Available: ${issue.available} | Required: ${issue.required} | Shortage: ${issue.shortage}</span>
        </div>`;
    });
    
    warningHtml += '<br><strong>Proceeding will result in negative stock levels.</strong></div>';
    
    Swal.fire({
        icon: 'warning',
        title: 'Insufficient Stock Warning',
        html: warningHtml,
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: '⚠️ Proceed Anyway',
        cancelButtonText: '❌ Cancel',
        reverseButtons: true,
        customClass: {
            popup: 'swal-wide'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            // User confirmed, submit with force flag
            submitInvoiceForm(formData, true);
        } else {
            // User cancelled, re-enable submit button
            const submitBtn = $('#save-invoice-btn');
            submitBtn.prop('disabled', false).html('<i class="fas fa-save"></i> Save Invoice');
        }
    });
}
</script>
@stop
