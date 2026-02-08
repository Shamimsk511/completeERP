@extends('layouts.modern-admin')

@section('title', 'Edit Product Return')
@section('page_title', 'Edit Product Return #' . $return->return_number)

@section('header_actions')
    <a class="btn modern-btn modern-btn-secondary" href="{{ route('returns.index') }}">
        <i class="fas fa-arrow-left"></i> Back to Returns
    </a>
    <a class="btn modern-btn modern-btn-info" href="{{ route('returns.show', $return) }}">
        <i class="fas fa-eye"></i> View Return
    </a>
@stop

@section('page_content')
    <!-- Return Overview Cards -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6">
            <div class="stats-card stats-card-primary">
                <div class="stats-icon">
                    <i class="fas fa-undo"></i>
                </div>
                <div class="stats-content">
                    <h3 class="stats-number">{{ $return->return_number }}</h3>
                    <p class="stats-label">Return Number</p>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6">
            <div class="stats-card stats-card-info">
                <div class="stats-icon">
                    <i class="fas fa-calendar"></i>
                </div>
                <div class="stats-content">
                    <h3 class="stats-number">{{ \Carbon\Carbon::parse($return->return_date)->format('d M') }}</h3>
                    <p class="stats-label">Return Date</p>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6">
            <div class="stats-card stats-card-warning">
                <div class="stats-icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div class="stats-content">
                    <h3 class="stats-number" id="return-total">৳{{ number_format($return->total, 2) }}</h3>
                    <p class="stats-label">Return Amount</p>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6">
            <div class="stats-card stats-card-success">
                <div class="stats-icon">
                    <i class="fas fa-boxes"></i>
                </div>
                <div class="stats-content">
                    <h3 class="stats-number" id="total-items">{{ $return->items->count() }}</h3>
                    <p class="stats-label">Total Items</p>
                </div>
            </div>
        </div>
    </div>

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible modern-alert">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
            <i class="fas fa-exclamation-triangle"></i>
            {{ session('error') }}
        </div>
    @endif

    <form action="{{ route('returns.update', $return) }}" method="POST" id="return-form">
        @csrf
        @method('PUT')
        
        <!-- Return Information Card -->
        <div class="card modern-card mb-4">
            <div class="card-header modern-header">
                <h3 class="card-title">
                    <i class="fas fa-info-circle"></i> Return Information
                </h3>
            </div>
            <div class="card-body modern-card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="info-item">
                            <div class="info-label">
                                <i class="fas fa-hashtag text-primary"></i> Return Number
                            </div>
                            <input type="text" name="return_number" id="return_number" class="form-control modern-input" value="{{ $return->return_number }}" readonly>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info-item">
                            <div class="info-label">
                                <i class="fas fa-calendar text-info"></i> Return Date <span class="text-danger">*</span>
                            </div>
                            <input type="date" name="return_date" id="return_date" class="form-control modern-input" value="{{ $return->return_date }}" required>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Customer Information Card -->
        <div class="card modern-card mb-4">
            <div class="card-header modern-header info-header">
                <h3 class="card-title">
                    <i class="fas fa-user"></i> Customer Information
                </h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-sm btn-light" id="customer-history-btn">
                        <i class="fas fa-history"></i> Purchase History
                    </button>
                </div>
            </div>
            <div class="card-body modern-card-body">
                <div class="row">
                    <div class="col-md-8">
                        <div class="info-item">
                            <div class="info-label">
                                <i class="fas fa-user text-primary"></i> Customer <span class="text-danger">*</span>
                            </div>
                            <div class="input-group">
                                <select name="customer_id" id="customer_id" class="form-control select2 modern-select" required>
                                    <option value="">Select Customer</option>
                                    @foreach($customers as $customer)
                                        <option value="{{ $customer->id }}" 
                                                data-phone="{{ $customer->phone }}" 
                                                data-address="{{ $customer->address }}"
                                                data-balance="{{ $customer->outstanding_balance }}"
                                                {{ $customer->id == $return->customer_id ? 'selected' : '' }}>
                                            {{ $customer->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="input-group-append">
                                    <a href="{{ route('customers.create') }}" class="btn modern-btn modern-btn-success" id="new-customer-btn">
                                        <i class="fas fa-plus"></i> New
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="info-item">
                            <div class="info-label">
                                <i class="fas fa-wallet text-warning"></i> Outstanding Balance
                            </div>
                            <input type="text" id="customer_balance" class="form-control modern-input balance-display" value="৳{{ number_format($return->customer->outstanding_balance, 2) }}" readonly>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="info-item">
                            <div class="info-label">
                                <i class="fas fa-phone text-success"></i> Phone
                            </div>
                            <input type="text" id="customer_phone" class="form-control modern-input" value="{{ $return->customer->phone ?? 'N/A' }}" readonly>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info-item">
                            <div class="info-label">
                                <i class="fas fa-map-marker-alt text-danger"></i> Address
                            </div>
                            <input type="text" id="customer_address" class="form-control modern-input" value="{{ $return->customer->address ?? 'No address provided' }}" readonly>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Products Section -->
        <div class="card modern-card mb-4">
            <div class="card-header modern-header success-header">
                <h3 class="card-title">
                    <i class="fas fa-boxes"></i> Return Products
                </h3>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table modern-table mb-0" id="product-table">
                        <thead>
                            <tr>
                                <th width="20%">Product</th>
                                <th width="15%">Description</th>
                                <th width="12%">Company</th>
                                <th width="12%">Category</th>
                                <th width="8%">Available</th>
                                <th width="8%">Quantity</th>
                                <th width="6%">Box</th>
                                <th width="6%">Pieces</th>
                                <th width="8%">Unit Price</th>
                                <th width="8%">Total</th>
                                <th width="5%">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($return->items as $index => $item)
                            <tr class="product-row">
                                <td>
                                    <select name="product_id[]" class="form-control select2 product-select modern-select" required>
                                        <option value="">Select Product</option>
                                        @foreach($products as $product)
                                            <option value="{{ $product->id }}"
                                                data-price="{{ $product->sale_price }}"
                                                data-stock="{{ $product->current_stock }}"
                                                data-purchase="{{ $product->purchase_price }}"
                                                {{ $product->id == $item->product_id ? 'selected' : '' }}>
                                                {{ $product->name }} (Stock: {{ $product->current_stock }})
                                            </option>
                                        @endforeach
                                    </select>
                                    <input type="hidden" name="invoice_item_id[]" class="invoice-item-id" value="{{ $item->invoice_item_id }}">
                                </td>
                                <td>
                                    <input type="text" name="description[]" class="form-control modern-input product-description" value="{{ $item->description }}" required>
                                </td>
                                <td>
                                    <input type="text" class="form-control modern-input product-company" value="{{ $item->product->company->name ?? '' }}" readonly>
                                </td>
                                <td>
                                    <input type="text" class="form-control modern-input product-category" value="{{ $item->product->category->name ?? '' }}" readonly>
                                </td>
                                <td>
                                    <input type="number" class="form-control modern-input product-available" readonly>
                                    <small class="text-muted available-info"></small>
                                </td>
                                <td>
                                    <input type="number" name="quantity[]" class="form-control modern-input product-quantity" step="0.01" min="0.01" value="{{ $item->quantity }}" required>
                                </td>
                                <td>
                                    <input type="number" name="boxes[]" class="form-control modern-input product-boxes" value="{{ $item->boxes }}" readonly>
                                </td>
                                <td>
                                    <input type="number" name="pieces[]" class="form-control modern-input product-pieces" value="{{ $item->pieces }}" readonly>
                                </td>
                                <td>
                                    <input type="number" name="unit_price[]" class="form-control modern-input product-price" step="0.01" min="0" value="{{ $item->unit_price }}" required>
                                </td>
                                <td>
                                    <input type="number" name="item_total[]" class="form-control modern-input product-total" step="0.01" min="0" value="{{ $item->total }}" readonly required>
                                </td>
                                <td>
                                    <button type="button" class="btn modern-btn modern-btn-danger remove-row">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <!-- Add Product Button at Bottom -->
            <div class="card-footer modern-card-footer text-center">
                <button type="button" class="btn modern-btn modern-btn-primary" id="add-product-row">
                    <i class="fas fa-plus"></i> Add Another Product
                </button>
                <small class="text-muted d-block mt-2">
                    <i class="fas fa-info-circle"></i> Click to add more products to this return
                </small>
            </div>
        </div>

        <!-- Payment and Totals Section -->
        <div class="row">
            <div class="col-md-6">
                <div class="card modern-card">
                    <div class="card-header modern-header warning-header">
                        <h3 class="card-title">
                            <i class="fas fa-credit-card"></i> Payment Information
                        </h3>
                    </div>
                    <div class="card-body modern-card-body">
                        <div class="info-item">
                            <div class="info-label">
                                <i class="fas fa-money-bill-wave text-success"></i> Payment Method <span class="text-danger">*</span>
                            </div>
                            <select name="payment_method" id="payment_method" class="form-control modern-select" required>
                                <option value="cash" {{ $return->payment_method == 'cash' ? 'selected' : '' }}>Cash</option>
                                <option value="bank" {{ $return->payment_method == 'bank' ? 'selected' : '' }}>Bank Transfer</option>
                                <option value="mobile_bank" {{ $return->payment_method == 'mobile_bank' ? 'selected' : '' }}>Mobile Banking</option>
                                <option value="cheque" {{ $return->payment_method == 'cheque' ? 'selected' : '' }}>Cheque</option>
                            </select>
                        </div>
                        <div class="info-item">
                            <div class="info-label">
                                <i class="fas fa-hand-holding-usd text-primary"></i> Refund Amount
                            </div>
                            <input type="number" name="refund_amount" id="refund_amount" class="form-control modern-input" step="0.01" min="0" value="{{ number_format($refundAmount ?? 0, 2, '.', '') }}">
                            <small class="text-muted" id="refund-help">Refund to customer (only when you owe them)</small>
                        </div>
                        <div class="info-item" id="refund-account-section" style="display: none;">
                            <div class="info-label">
                                <i class="fas fa-university text-info"></i> Refund Account
                            </div>
                            <select name="refund_account_id" id="refund_account_id" class="form-control modern-select">
                                <option value="">Select Account</option>
                                @foreach($cashBankAccounts as $account)
                                    <option value="{{ $account->id }}" {{ ($refundAccountId ?? null) == $account->id ? 'selected' : '' }}>{{ $account->name }}</option>
                                @endforeach
                            </select>
                            <small class="text-muted">Cash/Bank account to pay the refund from</small>
                        </div>
                        <div class="info-item">
                            <div class="info-label">
                                <i class="fas fa-sticky-note text-info"></i> Notes
                            </div>
                            <textarea name="notes" id="notes" class="form-control modern-input" rows="4" placeholder="Add any additional notes...">{{ $return->notes }}</textarea>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card modern-card">
                    <div class="card-header modern-header danger-header">
                        <h3 class="card-title">
                            <i class="fas fa-calculator"></i> Return Totals
                        </h3>
                    </div>
                    <div class="card-body modern-card-body">
                        <div class="info-item">
                            <div class="info-label">
                                <i class="fas fa-receipt text-primary"></i> Subtotal
                            </div>
                            <input type="number" name="subtotal" id="subtotal" class="form-control modern-input amount-display" step="0.01" min="0" value="{{ $return->subtotal }}" readonly required>
                        </div>
                        <div class="info-item">
                            <div class="info-label">
                                <i class="fas fa-dollar-sign text-success"></i> Total Amount
                            </div>
                            <input type="number" name="total" id="total" class="form-control modern-input amount-display total-highlight" step="0.01" min="0" value="{{ $return->total }}" readonly required>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="card modern-card mt-4">
            <div class="card-body text-center">
                <button type="submit" class="btn modern-btn modern-btn-primary btn-lg" id="save-return-btn">
                    <i class="fas fa-save"></i> Update Return
                </button>
                <a href="{{ route('returns.show', $return) }}" class="btn modern-btn modern-btn-info btn-lg">
                    <i class="fas fa-eye"></i> View Return
                </a>
                <a href="{{ route('returns.index') }}" class="btn modern-btn modern-btn-secondary btn-lg">
                    <i class="fas fa-times"></i> Cancel
                </a>
            </div>
        </div>
    </form>

    <!-- Customer Purchase History Modal -->
    <div class="modal fade" id="customer-history-modal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content modern-modal">
                <div class="modal-header modern-modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-history"></i> Customer Purchase History
                    </h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="customer-history-content">
                    <div class="text-center">
                        <i class="fas fa-spinner fa-spin fa-2x"></i>
                        <p>Loading purchase history...</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn modern-btn modern-btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times"></i> Close
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Return Validation Warning Modal -->
    <div class="modal fade" id="return-warning-modal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content modern-modal">
                <div class="modal-header modern-modal-header bg-warning">
                    <h5 class="modal-title text-white">
                        <i class="fas fa-exclamation-triangle"></i> Return Validation Warning
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="warning-content">
                    <!-- Warning content will be populated by JavaScript -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn modern-btn modern-btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                    <button type="button" class="btn modern-btn modern-btn-warning" id="proceed-anyway">
                        <i class="fas fa-exclamation-triangle"></i> Proceed Anyway
                    </button>
                </div>
            </div>
        </div>
    </div>
@stop

@section('additional_css')
<link rel="stylesheet" href="/css/modern-admin.css">
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

<style>
/* Modern input styling */
.modern-input {
    border: 2px solid #e5e7eb;
    border-radius: 8px;
    padding: 0.75rem;
    font-size: 0.875rem;
    transition: all 0.3s ease;
    background: white;
}

.modern-input:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    outline: none;
}

.modern-select {
    border: 2px solid #e5e7eb;
    border-radius: 8px;
    min-height: 42px;
}

/* Balance display styling */
.balance-display {
    font-family: 'Courier New', monospace;
    font-weight: 600;
    text-align: right;
}

.amount-display {
    font-family: 'Courier New', monospace;
    font-weight: 600;
    text-align: right;
    background: rgba(102, 126, 234, 0.05);
}

.total-highlight {
    background: rgba(220, 38, 38, 0.1);
    border-color: #dc2626;
    font-size: 1.1rem;
    font-weight: 700;
}

/* Available quantity styling */
.product-available {
    background: rgba(17, 153, 142, 0.1);
    border-color: #11998e;
    color: #11998e;
    font-weight: 600;
}

.available-info {
    font-size: 0.75rem;
    font-weight: 500;
}

/* Warning states */
.quantity-warning {
    border-color: #f59e0b !important;
    background: rgba(245, 158, 11, 0.1) !important;
}

.quantity-error {
    border-color: #ef4444 !important;
    background: rgba(239, 68, 68, 0.1) !important;
}

/* Product row animations */
.product-row {
    transition: all 0.3s ease;
}

.product-row.warning {
    background: rgba(245, 158, 11, 0.05);
    border-left: 4px solid #f59e0b;
}

.product-row.error {
    background: rgba(239, 68, 68, 0.05);
    border-left: 4px solid #ef4444;
}

/* Modern card footer styling */
.modern-card-footer {
    background: rgba(102, 126, 234, 0.02);
    border-top: 1px solid rgba(102, 126, 234, 0.1);
    padding: 1.5rem;
    border-radius: 0 0 12px 12px;
}

.modern-card-footer .btn {
    min-width: 200px;
    padding: 0.75rem 1.5rem;
    font-weight: 600;
    border-radius: 8px;
    transition: all 0.3s ease;
}

.modern-card-footer .btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
}

.modern-card-footer small {
    font-size: 0.8rem;
    opacity: 0.8;
}

.modern-card-footer small i {
    margin-right: 0.25rem;
}

/* Modal styling */
.modern-modal .modal-content {
    border: none;
    border-radius: 12px;
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
}

.modern-modal-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 12px 12px 0 0;
    border: none;
}

.modern-modal-header.bg-warning {
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
}

/* Select2 custom styling */
.select2-container--default .select2-selection--single {
    border: 2px solid #e5e7eb;
    border-radius: 8px;
    height: 42px;
    line-height: 42px;
}

.select2-container--default .select2-selection--single:focus-within {
    border-color: #667eea;
}

.select2-container--default .select2-selection--single .select2-selection__rendered {
    line-height: 42px;
    padding-left: 12px;
}

.select2-container--default .select2-selection--single .select2-selection__arrow {
    height: 42px;
}

/* Animation for new rows */
.product-row.newly-added {
    animation: slideInFromBottom 0.5s ease-out;
}

@keyframes slideInFromBottom {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Responsive design */
@media (max-width: 768px) {
    .stats-card {
        margin-bottom: 1rem;
    }
    
    .table-responsive {
        font-size: 0.75rem;
    }
    
    .modern-input, .modern-select {
        font-size: 0.8rem;
        padding: 0.5rem;
    }
    
    .btn-lg {
        padding: 0.75rem 1.5rem;
        font-size: 1rem;
    }
    
    .modern-card-footer {
        padding: 1rem;
    }
    
    .modern-card-footer .btn {
        width: 100%;
        min-width: auto;
    }
}
</style>
@stop

@section('additional_js')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
$(document).ready(function() {
    let customerPurchaseHistory = {};
    let returnValidationWarnings = [];
    let customerOutstanding = parseFloat(@json($return->customer->outstanding_balance ?? 0));
    
    // Initialize Select2
    initializeSelect2();
    
    // Load initial customer purchase history
    const customerId = $('#customer_id').val();
    if (customerId) {
        loadCustomerPurchaseHistory(customerId);
    }
    
    // Calculate initial totals
    calculateReturnTotal();
    updateStatsCards();

    // Add product row
    $('#add-product-row').click(function() {
        addProductRow();
    });

    // Remove product row
    $(document).on('click', '.remove-row', function() {
        if ($('.product-row').length > 1) {
            $(this).closest('tr').remove();
            calculateReturnTotal();
            updateStatsCards();
        } else {
            alert('At least one product is required.');
        }
    });

    // Calculate totals when quantity or price changes
    $(document).on('input', '.product-quantity, .product-price', function() {
        const row = $(this).closest('tr');
        calculateRowTotal(row);
        calculateReturnTotal();
        updateStatsCards();
        validateReturnQuantity(row);
    });

    // Refund amount change
    $('#refund_amount').on('input', function() {
        updateRefundLimits();
    });

    // Product selection change
    $(document).on('change', '.product-select', function() {
        const row = $(this).closest('tr');
        const productId = $(this).val();
        
        if (productId) {
            loadProductDetails(productId, row);
        } else {
            clearProductRow(row);
        }
    });

    // Customer selection change
    $('#customer_id').change(function() {
        const customerId = $(this).val();
        if (customerId) {
            loadCustomerDetails(customerId);
            loadCustomerPurchaseHistory(customerId);
            $('#customer-history-btn').show();
        } else {
            clearCustomerDetails();
            $('#customer-history-btn').hide();
            customerPurchaseHistory = {};
        }
    });

    // Show customer history modal
    $('#customer-history-btn').click(function() {
        $('#customer-history-modal').modal('show');
    });

    // Form submission with validation
    $('#return-form').submit(function(e) {
        e.preventDefault();
        validateAndSubmitReturn();
    });

    // Proceed anyway button
    $('#proceed-anyway').click(function() {
        $('#return-warning-modal').modal('hide');
        submitReturnForm();
    });

    // Initialize Select2 for new elements
    function initializeSelect2() {
        $('.select2').select2({
            width: '100%',
            dropdownParent: $('#return-form')
        });
    }

    // Load product details
    function loadProductDetails(productId, row) {
        $.ajax({
            url: '/product-details/' + productId,
            type: 'GET',
            dataType: 'json',
            success: function(data) {
                row.find('.product-description').val(data.name);
                row.find('.product-company').val(data.company ? data.company.name : '');
                row.find('.product-category').val(data.category ? data.category.name : '');
                row.find('.product-price').val(data.sale_price);
                
                // Store category details for box/pieces calculation
                row.data('box-pcs', data.category ? data.category.box_pcs : 0);
                row.data('pieces-feet', data.category ? data.category.pieces_feet : 0);
                
                // Load available quantity for return
                loadAvailableQuantity(productId, row);
                
                // Focus on quantity field
                row.find('.product-quantity').focus();
            },
            error: function() {
                alert('Error loading product details');
            }
        });
    }

    // Load available quantity for return
    function loadAvailableQuantity(productId, row) {
        const customerId = $('#customer_id').val();
        if (!customerId) {
            row.find('.product-available').val(0);
            row.find('.available-info').text('Select customer first').removeClass('text-success text-warning text-danger').addClass('text-muted');
            return;
        }

        // Calculate available quantity from purchase history
        if (customerPurchaseHistory[productId]) {
            const product = customerPurchaseHistory[productId];
            const purchased = product.purchased || 0;
            const returned = product.returned || 0;
            const available = purchased - returned;
            
            row.find('.product-available').val(available.toFixed(2));
            row.find('.available-info').text(`Purchased: ${purchased.toFixed(2)}, Returned: ${returned.toFixed(2)}`);
            
            // Add visual indicators
            row.find('.product-available').removeClass('quantity-error quantity-warning');
            row.find('.available-info').removeClass('text-danger text-warning text-success');
            
            if (available <= 0) {
                row.find('.product-available').addClass('quantity-error');
                row.find('.available-info').addClass('text-danger');
            } else if (available < 5) {
                row.find('.product-available').addClass('quantity-warning');
                row.find('.available-info').addClass('text-warning');
            } else {
                row.find('.available-info').addClass('text-success');
            }
        } else {
            row.find('.product-available').val(0);
            row.find('.available-info').text('No purchase history').removeClass('text-success text-warning').addClass('text-danger');
            row.find('.product-available').addClass('quantity-error');
        }
    }

    // Load customer details
    function loadCustomerDetails(customerId) {
        $.ajax({
            url: '/customer-details/' + customerId,
            type: 'GET',
            dataType: 'json',
            success: function(data) {
                $('#customer_phone').val(data.phone || 'N/A');
                $('#customer_address').val(data.address || 'No address provided');
                
                const balance = parseFloat(data.outstanding_balance || 0);
                customerOutstanding = balance;
                $('#customer_balance').val('৳' + balance.toLocaleString('en-US', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }));
                
                // Add balance color coding
                const balanceField = $('#customer_balance');
                balanceField.removeClass('text-success text-warning text-danger');
                if (balance > 5000) {
                    balanceField.addClass('text-danger');
                } else if (balance > 0) {
                    balanceField.addClass('text-warning');
                } else {
                    balanceField.addClass('text-success');
                }

                updateRefundLimits();
            },
            error: function() {
                alert('Error loading customer details');
            }
        });
    }

    // Load customer purchase history
    function loadCustomerPurchaseHistory(customerId) {
        if (!customerId) {
            customerPurchaseHistory = {};
            return;
        }
        
        $.ajax({
            url: '/customer-purchase-history/' + customerId,
            type: 'GET',
            dataType: 'json',
            beforeSend: function() {
                $('#customer-history-content').html(`
                    <div class="text-center p-4">
                        <i class="fas fa-spinner fa-spin fa-2x text-primary"></i>
                        <p class="mt-2">Loading purchase history...</p>
                    </div>
                `);
            },
            success: function(response) {
                if (response.success) {
                    customerPurchaseHistory = response.products || {};
                    
                    // Update available quantities for existing product rows
                    $('.product-row').each(function() {
                        const productId = $(this).find('.product-select').val();
                        if (productId) {
                            loadAvailableQuantity(productId, $(this));
                        }
                    });
                    
                    // Update history modal content
                    updateHistoryModalContent(response);
                } else {
                    $('#customer-history-content').html(`
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle"></i>
                            ${response.message || 'Failed to load purchase history'}
                        </div>
                    `);
                }
            },
            error: function(xhr, status, error) {
                console.error('Purchase history error:', error);
                $('#customer-history-content').html(`
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle"></i>
                        Error loading purchase history. Please try again.
                    </div>
                `);
                customerPurchaseHistory = {};
            }
        });
    }

    // Update history modal content
    function updateHistoryModalContent(data) {
        const products = data.products || {};
        const summary = data.summary || {};
        
        let content = '';
        
        // Add summary section
        if (Object.keys(products).length > 0) {
            content += `
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="text-center">
                            <h4 class="text-primary">${summary.total_products || 0}</h4>
                            <small class="text-muted">Products</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-center">
                            <h4 class="text-success">${(summary.total_purchased || 0).toFixed(2)}</h4>
                            <small class="text-muted">Total Purchased</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-center">
                            <h4 class="text-danger">${(summary.total_returned || 0).toFixed(2)}</h4>
                            <small class="text-muted">Total Returned</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-center">
                            <h4 class="text-info">${(summary.total_available || 0).toFixed(2)}</h4>
                            <small class="text-muted">Available</small>
                        </div>
                    </div>
                </div>
                <hr>
            `;
        }
        
        // Add table
        content += '<div class="table-responsive">';
        content += '<table class="table history-table table-striped">';
        content += `
            <thead class="thead-light">
                <tr>
                    <th>Product</th>
                    <th>Category</th>
                    <th>Company</th>
                    <th>Purchased</th>
                    <th>Returned</th>
                    <th>Available</th>
                    <th>Invoices</th>
                    <th>Last Purchase</th>
                </tr>
            </thead>
        `;
        content += '<tbody>';
        
        if (Object.keys(products).length > 0) {
            Object.entries(products).forEach(([productId, product]) => {
                const available = product.available || 0;
                const statusClass = available <= 0 ? 'danger' : (available < 5 ? 'warning' : 'success');
                
                content += `
                    <tr>
                        <td>
                            <strong>${product.name || 'Unknown Product'}</strong>
                        </td>
                        <td>
                            <span class="badge badge-secondary">${product.category || 'N/A'}</span>
                        </td>
                        <td>
                            <span class="badge badge-info">${product.company || 'N/A'}</span>
                        </td>
                        <td>
                            <span class="badge badge-primary">${(product.purchased || 0).toFixed(2)}</span>
                        </td>
                        <td>
                            <span class="badge badge-danger">${(product.returned || 0).toFixed(2)}</span>
                        </td>
                        <td>
                            <span class="badge badge-${statusClass}">${available.toFixed(2)}</span>
                        </td>
                        <td>
                            <span class="badge badge-light">${product.invoice_count || 0}</span>
                        </td>
                        <td>
                            <small>${product.last_purchase || 'N/A'}</small>
                        </td>
                    </tr>
                `;
            });
        } else {
            content += `
                <tr>
                    <td colspan="8" class="text-center py-4">
                        <i class="fas fa-shopping-cart fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No Purchase History</h5>
                        <p class="text-muted">This customer has not purchased any products yet.</p>
                    </td>
                </tr>
            `;
        }
        
        content += '</tbody></table></div>';
        
        $('#customer-history-content').html(content);
    }

    // Validate return quantity
    function validateReturnQuantity(row) {
        const productId = row.find('.product-select').val();
        const quantity = parseFloat(row.find('.product-quantity').val()) || 0;
        const available = parseFloat(row.find('.product-available').val()) || 0;
        
        // Remove existing warning classes
        row.removeClass('warning error');
        row.find('.product-quantity').removeClass('quantity-warning quantity-error');
        
        if (!productId || quantity <= 0) {
            return;
        }
        
        if (available <= 0) {
            // Customer never bought this product
            row.addClass('error');
            row.find('.product-quantity').addClass('quantity-error');
        } else if (quantity > available) {
            // Returning more than purchased
            row.addClass('warning');
            row.find('.product-quantity').addClass('quantity-warning');
        }
    }

    // Validate and submit return
    function validateAndSubmitReturn() {
        returnValidationWarnings = [];
        
        // Check each product row for validation issues
        $('.product-row').each(function() {
            const row = $(this);
            const productId = row.find('.product-select').val();
            const productName = row.find('.product-description').val();
            const quantity = parseFloat(row.find('.product-quantity').val()) || 0;
            const available = parseFloat(row.find('.product-available').val()) || 0;
            
            if (!productId || quantity <= 0) return;
            
            if (available <= 0) {
                returnValidationWarnings.push({
                    type: 'never_purchased',
                    product: productName,
                    quantity: quantity,
                    message: `Customer never purchased "${productName}" but trying to return ${quantity.toFixed(2)} units.`
                });
            } else if (quantity > available) {
                returnValidationWarnings.push({
                    type: 'excess_quantity',
                    product: productName,
                    quantity: quantity,
                    available: available,
                    message: `Trying to return ${quantity.toFixed(2)} units of "${productName}" but only ${available.toFixed(2)} units available for return.`
                });
            }
        });
        
        if (returnValidationWarnings.length > 0) {
            showValidationWarnings();
        } else {
            submitReturnForm();
        }
    }

    // Show validation warnings modal
    function showValidationWarnings() {
        let content = '<div class="alert alert-warning">';
        content += '<h6><i class="fas fa-exclamation-triangle"></i> The following issues were detected:</h6>';
        content += '<ul class="mb-0">';
        
        returnValidationWarnings.forEach(warning => {
            content += `<li>${warning.message}</li>`;
        });
        
        content += '</ul></div>';
        content += '<p><strong>Do you want to proceed with this return anyway?</strong></p>';
        content += '<p class="text-muted"><small>This action will update the return record regardless of purchase history validation.</small></p>';
        
        $('#warning-content').html(content);
        $('#return-warning-modal').modal('show');
    }

    // Submit return form
    function submitReturnForm() {
        const $submitBtn = $('#save-return-btn');
        const originalText = $submitBtn.html();
        
        $submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Updating...');
        
        $('#return-form')[0].submit();
    }

    // Clear product row
    function clearProductRow(row) {
        row.find('input:not(.product-select)').val('');
        row.find('.product-available').removeClass('quantity-warning quantity-error');
        row.find('.available-info').removeClass('text-danger text-warning text-success').text('');
        row.removeClass('warning error');
        row.data('box-pcs', 0);
        row.data('pieces-feet', 0);
    }

    // Clear customer details
    function clearCustomerDetails() {
        $('#customer_phone').val('');
        $('#customer_address').val('');
        $('#customer_balance').val('').removeClass('text-success text-warning text-danger');
        customerPurchaseHistory = {};
        customerOutstanding = 0;
        updateRefundLimits();
        
        // Clear available quantities
        $('.product-row').each(function() {
            $(this).find('.product-available').val(0);
            $(this).find('.available-info').text('Select customer first');
        });
    }

    // Add new product row
    function addProductRow(focusNewRow = true) {
        const newRow = $('.product-row:first').clone();
        
        // Destroy existing Select2 on the cloned row to prevent conflicts
        newRow.find('.select2').each(function() {
            if ($(this).hasClass('select2-hidden-accessible')) {
                $(this).select2('destroy');
            }
        });
        
        // Clear values
        newRow.find('input').val('');
        newRow.find('select').val('').removeClass('select2-hidden-accessible');
        newRow.removeClass('warning error');
        
        // Remove any existing Select2 containers from the cloned row
        newRow.find('.select2-container').remove();
        
        // Append to table
        $('#product-table tbody').append(newRow);
        
        // Initialize Select2 only for the new row
        newRow.find('.product-select').select2({
            width: '100%',
            dropdownParent: $('#return-form'),
            placeholder: 'Select Product',
            allowClear: true
        });
        
        if (focusNewRow) {
            setTimeout(function() {
                newRow.find('.product-select').select2('open');
            }, 100);
        }
        
        // Update stats
        updateStatsCards();
        
        return newRow;
    }

    // Calculate row total
    function calculateRowTotal(row) {
        const quantity = parseFloat(row.find('.product-quantity').val()) || 0;
        const price = parseFloat(row.find('.product-price').val()) || 0;
        const total = quantity * price;
        
        row.find('.product-total').val(total.toFixed(2));
        calculateBoxesAndPieces(row);
    }

    // Calculate boxes and pieces
    function calculateBoxesAndPieces(row) {
        const quantity = parseFloat(row.find('.product-quantity').val()) || 0;
        const boxPcs = parseFloat(row.data('box-pcs')) || 0;
        const piecesFeet = parseFloat(row.data('pieces-feet')) || 0;
        
        if (boxPcs > 0 && piecesFeet > 0) {
            const totalPieces = Math.round(quantity / piecesFeet);
            const boxes = Math.floor(totalPieces / boxPcs);
            const pieces = totalPieces - (boxes * boxPcs);
            
            row.find('.product-boxes').val(boxes);
            row.find('.product-pieces').val(pieces);
        } else {
            row.find('.product-boxes').val('');
            row.find('.product-pieces').val('');
        }
    }

    // Calculate return totals
    function calculateReturnTotal() {
        let subtotal = 0;
        
        $('.product-total').each(function() {
            subtotal += parseFloat($(this).val()) || 0;
        });
        
        const total = subtotal;
        
        $('#subtotal').val(subtotal.toFixed(2));
        $('#total').val(total.toFixed(2));
        updateRefundLimits();
    }

    function updateRefundLimits() {
        const returnTotal = parseFloat($('#total').val()) || 0;
        const maxRefund = Math.max(0, returnTotal - customerOutstanding);
        const refundInput = $('#refund_amount');
        const refundSection = $('#refund-account-section');

        refundInput.attr('max', maxRefund.toFixed(2));

        const currentRefund = parseFloat(refundInput.val()) || 0;
        if (currentRefund > maxRefund) {
            refundInput.val(maxRefund.toFixed(2));
        }

        if (currentRefund > 0) {
            refundSection.show();
        } else {
            refundSection.hide();
            $('#refund_account_id').val('');
        }

        $('#refund-help').text(maxRefund > 0
            ? `Max refundable now: ৳${maxRefund.toFixed(2)}`
            : 'Refund available only if return exceeds outstanding');
    }

    // Update stats cards
    function updateStatsCards() {
        const total = parseFloat($('#total').val()) || 0;
        const itemCount = $('.product-row').length;
        
        $('#return-total').text('৳' + total.toLocaleString('en-US', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        }));
        $('#total-items').text(itemCount);
    }

    // Initialize tooltips
    $('[data-toggle="tooltip"]').tooltip();
    
    console.log('Modern return edit form initialized successfully');
});
</script>
@stop
