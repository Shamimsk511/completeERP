@extends('layouts.modern-admin')

@section('title', 'Create Other Delivery')

@section('page_title', 'Create New Delivery Challan')

@section('header_actions')
    <div class="header-actions-group">
        <a href="{{ route('other-deliveries.index') }}" class="btn modern-btn modern-btn-outline">
            <i class="fas fa-arrow-left"></i> <span class="btn-text">Back to Deliveries</span>
        </a>
    </div>
@stop

@section('page_content')
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible modern-alert">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
            <i class="fas fa-exclamation-circle"></i>
            {{ session('error') }}
        </div>
    @endif

    @if(session('success'))
        <div class="alert alert-success alert-dismissible modern-alert">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
            <i class="fas fa-check-circle"></i>
            {{ session('success') }}
        </div>
    @endif

    <form action="{{ route('other-deliveries.store') }}" method="POST" id="delivery-form">
        @csrf
        
        <div class="row">
            <!-- Challan Information -->
            <div class="col-lg-6">
                <div class="card modern-card">
                    <div class="card-header modern-header">
                        <div class="header-content">
                            <div class="header-title">
                                <i class="fas fa-file-alt header-icon"></i>
                                <h3 class="card-title">Challan Information</h3>
                            </div>
                        </div>
                    </div>
                    <div class="card-body modern-card-body">
                        <div class="form-group">
                            <label for="challan_number" class="form-label">Challan Number <span class="text-danger">*</span></label>
                            <input type="text" name="challan_number" id="challan_number" class="form-control modern-input" value="{{ $challan_number }}" readonly>
                        </div>
                        
                        <div class="form-group">
                            <label for="delivery_date" class="form-label">Delivery Date <span class="text-danger">*</span></label>
                            <input type="date" name="delivery_date" id="delivery_date" class="form-control modern-input" value="{{ $delivery_date }}" required>
                        </div>

                        <div class="form-group">
                            <label for="delivery_status" class="form-label">Delivery Status <span class="text-danger">*</span></label>
                            <select name="delivery_status" id="delivery_status" class="form-control modern-select" required>
                                <option value="pending" selected>Pending</option>
                                <option value="in_transit">In Transit</option>
                                <option value="delivered">Delivered</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Recipient Information -->
            <div class="col-lg-6">
                <div class="card modern-card">
                    <div class="card-header modern-header">
                        <div class="header-content">
                            <div class="header-title">
                                <i class="fas fa-user header-icon"></i>
                                <h3 class="card-title">Recipient Information</h3>
                            </div>
                        </div>
                    </div>
                    <div class="card-body modern-card-body">
                        <!-- Recipient Selection -->
                        <div class="form-group">
                            <label for="recipient_select" class="form-label">Select Previous Recipient (Optional)</label>
                            <select id="recipient_select" class="form-control modern-select select2-searchable">
                                <option value="">-- Select Previous Recipient or Create New --</option>
                                @foreach($recipients as $recipient)
                                    <option value="{{ $recipient->recipient_name }}" 
                                            data-phone="{{ $recipient->recipient_phone }}" 
                                            data-address="{{ $recipient->recipient_address }}">
                                        {{ $recipient->recipient_name }}
                                        @if($recipient->recipient_phone)
                                            ({{ $recipient->recipient_phone }})
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Recipient Details -->
                        <div class="form-group">
                            <label for="recipient_name" class="form-label">Recipient Name <span class="text-danger">*</span></label>
                            <input type="text" name="recipient_name" id="recipient_name" class="form-control modern-input" required>
                        </div>

                        <div class="form-group">
                            <label for="recipient_phone" class="form-label">Phone</label>
                            <input type="text" name="recipient_phone" id="recipient_phone" class="form-control modern-input">
                        </div>

                        <div class="form-group">
                            <label for="recipient_address" class="form-label">Address <span class="text-danger">*</span></label>
                            <textarea name="recipient_address" id="recipient_address" class="form-control modern-input" rows="3" required></textarea>
                        </div>

                        <!-- View History Button -->
                        <div class="form-group" id="history-button-group" style="display: none;">
                            <button type="button" class="btn modern-btn modern-btn-info btn-sm" id="view-history-btn">
                                <i class="fas fa-history"></i> View Delivery History
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Transport Information - Collapsible on Mobile -->
        <div class="card modern-card transport-card">
            <div class="card-header modern-header collapsible-header" data-toggle="collapse" data-target="#transport-info">
                <div class="header-content">
                    <div class="header-title">
                        <i class="fas fa-truck header-icon"></i>
                        <h3 class="card-title">Transport Information</h3>
                    </div>
                    <i class="fas fa-chevron-down collapse-icon"></i>
                </div>
            </div>
            <div id="transport-info" class="collapse show">
                <div class="card-body modern-card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="vehicle_type" class="form-label">Vehicle Type</label>
                                <input type="text" name="vehicle_type" id="vehicle_type" class="form-control modern-input">
                            </div>
                            
                            <div class="form-group">
                                <label for="vehicle_number" class="form-label">Vehicle Number</label>
                                <input type="text" name="vehicle_number" id="vehicle_number" class="form-control modern-input">
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="driver_name" class="form-label">Driver Name</label>
                                <input type="text" name="driver_name" id="driver_name" class="form-control modern-input">
                            </div>
                            
                            <div class="form-group">
                                <label for="driver_phone" class="form-label">Driver Phone</label>
                                <input type="text" name="driver_phone" id="driver_phone" class="form-control modern-input">
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="notes" class="form-label">Notes</label>
                        <textarea name="notes" id="notes" class="form-control modern-input" rows="2"></textarea>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Products Section - Mobile Optimized -->
        <div class="card modern-card">
            <div class="card-header modern-header">
                <div class="header-content">
                    <div class="header-title">
                        <i class="fas fa-boxes header-icon"></i>
                        <h3 class="card-title">Products for Delivery</h3>
                    </div>
                </div>
            </div>
            <div class="card-body modern-card-body">
                <!-- Mobile View Product Cards -->
                <div class="mobile-products-container" id="mobile-products">
                    <div class="mobile-product-card product-row-mobile" data-row-index="0">
                        <div class="card-header-mobile">
                            <span class="product-number">Product #1</span>
                            <button type="button" class="btn-remove-mobile remove-row-mobile">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Product <span class="text-danger">*</span></label>
                            <select name="product_id[]" class="form-control modern-select product-select-mobile select2-searchable" required>
                                <option value="">Select Product</option>
                                @foreach($products as $product)
                                    <option value="{{ $product->id }}" 
                                            data-stock="{{ $product->current_stock }}" 
                                            data-box-pcs="{{ $product->category->box_pcs ?? 0 }}" 
                                            data-pieces-feet="{{ $product->category->pieces_feet ?? 0 }}">
                                        {{ $product->name }} ({{ $product->current_stock }} in stock)
                                    </option>
                                @endforeach
                            </select>
                            <div class="stock-info">
                                Available: <span class="available-stock-mobile">0</span>
                            </div>
                        </div>
                        
                        {{-- <div class="form-group">
                            <label class="form-label">Description</label>
                            <input type="text" name="description[]" class="form-control modern-input">
                        </div> --}}
                        
                        <div class="quantity-controls">
                            <div class="form-group">
                                <label class="form-label">Entry Type</label>
                                <select class="form-control modern-select quantity-type-mobile">
                                    <option value="quantity">Direct Quantity</option>
                                    <option value="carton_pieces">Carton/Pieces</option>
                                </select>
                            </div>
                            
                            <div class="quantity-inputs">
                                <div class="form-group quantity-group">
                                    <label class="form-label">Quantity <span class="text-danger">*</span></label>
                                    <input type="number" name="quantity[]" class="form-control modern-input quantity-field-mobile" min="0.01" step="0.01" required>
                                    <input type="hidden" class="box-pcs-mobile" value="0">
                                    <input type="hidden" class="pieces-feet-mobile" value="0">
                                </div>
                                
                                <div class="carton-pieces-group" style="display: none;">
                                    <div class="row">
                                        <div class="col-6">
                                            <div class="form-group">
                                                <label class="form-label">Cartons</label>
                                                <input type="number" name="cartons[]" class="form-control modern-input carton-field-mobile" min="0" readonly>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="form-group">
                                                <label class="form-label">Pieces</label>
                                                <input type="number" name="pieces[]" class="form-control modern-input pieces-field-mobile" min="0" readonly>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Desktop View Table -->
                <div class="desktop-products-container">
                    <div class="table-responsive modern-table-responsive">
                        <table class="table modern-table" id="products-table">
                            <thead class="modern-thead">
                                <tr>
                                    <th width="25%">Product</th>
                                    <th width="20%">Description</th>
                                    <th width="15%">Available</th>
                                    <th width="10%">Quantity</th>
                                    <th width="10%">Unit</th>
                                    <th width="10%">Cartons</th>
                                    <th width="10%">Pieces</th>
                                    <th width="5%">Action</th>
                                </tr>
                            </thead>
                            <tbody class="modern-tbody">
                                <tr class="product-row">
                                    <td>
                                        <select name="product_id[]" class="form-control modern-select product-select select2-searchable" required>
                                            <option value="">Select Product</option>
                                            @foreach($products as $product)
                                                <option value="{{ $product->id }}" 
                                                        data-stock="{{ $product->current_stock }}" 
                                                        data-box-pcs="{{ $product->category->box_pcs ?? 0 }}" 
                                                        data-pieces-feet="{{ $product->category->pieces_feet ?? 0 }}">
                                                    {{ $product->name }} ({{ $product->current_stock }} in stock)
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <input type="text" name="description[]" class="form-control modern-input">
                                    </td>
                                    <td class="available-stock text-center font-weight-bold">0</td>
                                    <td>
                                        <input type="number" name="quantity[]" class="form-control modern-input quantity-field" min="0.01" step="0.01" required>
                                        <input type="hidden" class="box-pcs" value="0">
                                        <input type="hidden" class="pieces-feet" value="0">
                                    </td>
                                    <td>
                                        <select class="form-control modern-select quantity-type">
                                            <option value="quantity">Quantity</option>
                                            <option value="carton_pieces">Carton/Pieces</option>
                                        </select>
                                    </td>
                                    <td>
                                        <input type="number" name="cartons[]" class="form-control modern-input carton-field" min="0" readonly>
                                    </td>
                                    <td>
                                        <input type="number" name="pieces[]" class="form-control modern-input pieces-field" min="0" readonly>
                                    </td>
                                    <td>
                                        <button type="button" class="btn modern-btn-sm modern-btn-danger remove-row">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <!-- Add Product Button -->
                <div class="add-product-container">
                    <button type="button" class="btn modern-btn modern-btn-success btn-block-mobile" id="add-row">
                        <i class="fas fa-plus"></i> Add Product
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile Fixed Bottom Actions -->
        <div class="mobile-bottom-actions">
            <div class="mobile-button-group">
                <button type="submit" class="btn modern-btn modern-btn-primary btn-lg mobile-submit-btn" id="submit-btn">
                    <i class="fas fa-save"></i> Create Delivery
                </button>
                <button type="submit" class="btn modern-btn modern-btn-success btn-lg mobile-submit-btn create-and-new" id="submit-btn-new">
                    <i class="fas fa-plus-circle"></i> Create & New
                </button>
            </div>
        </div>
        
        <!-- Desktop Form Actions -->
        <div class="form-actions desktop-only">
            <div class="button-group">
                <button type="submit" class="btn modern-btn modern-btn-primary btn-lg" id="submit-btn-desktop">
                    <i class="fas fa-save"></i> Create Delivery
                </button>
                <button type="submit" class="btn modern-btn modern-btn-success btn-lg create-and-new" id="submit-btn-new-desktop">
                    <i class="fas fa-plus-circle"></i> Create & New
                </button>
                <a href="{{ route('other-deliveries.index') }}" class="btn modern-btn modern-btn-outline btn-lg">
                    <i class="fas fa-times"></i> Cancel
                </a>
            </div>
        </div>
    </form>

    <!-- Previous Deliveries Modal -->
    <div class="modal fade" id="historyModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-xl modal-dialog-mobile" role="document">
            <div class="modal-content modern-modal">
                <div class="modal-header modern-modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-history"></i>
                        Delivery History for <span id="history-recipient-name"></span>
                    </h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table modern-table">
                            <thead class="modern-thead">
                                <tr>
                                    <th>Date</th>
                                    <th>Challan</th>
                                    <th>Items</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="delivery-history-body">
                                <!-- AJAX content will load here -->
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn modern-btn modern-btn-outline" data-dismiss="modal">Close</button>
                    <a href="#" id="view-full-history" class="btn modern-btn modern-btn-primary">
                        <i class="fas fa-external-link-alt"></i> View Full History
                    </a>
                </div>
            </div>
        </div>
    </div>
@stop

@section('additional_css')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.min.css">
    
    <style>
        /* Form styling */
        .form-label {
            font-size: 13px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 8px;
        }

        .form-actions {
            text-align: center;
            margin-top: 32px;
            padding: 24px;
            background: #f8fafc;
            border-radius: 12px;
        }

        .form-actions .btn {
            margin: 0 8px;
        }

        /* Mobile button group styling */
        .mobile-button-group {
            display: flex;
            gap: 8px;
            width: 100%;
        }

        .mobile-submit-btn {
            flex: 1;
            font-size: 14px;
            padding: 12px 8px;
        }

        /* Desktop button group */
        .button-group {
            display: flex;
            gap: 12px;
            justify-content: center;
            align-items: center;
            flex-wrap: wrap;
        }

        .button-group .btn {
            margin: 0;
        }

        /* Create and New button styling */
        .modern-btn-success {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            border-color: #10b981;
        }

        .modern-btn-success:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(16, 185, 129, 0.4);
            color: white;
        }

        /* Status badge styling */
        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .status-pending {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: white;
        }

        .status-in-transit {
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            color: white;
        }

        .status-delivered {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
        }

        .status-cancelled {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: white;
        }

        /* Enhanced Select2 styling to match modern design */
        .select2-container {
            width: 100% !important;
        }
        
        .select2-container .select2-selection--single {
            height: 44px !important;
            line-height: 44px !important;
            border: 2px solid #e5e7eb !important;
            border-radius: 8px !important;
            background: white !important;
            font-size: 14px !important;
            transition: all 0.2s ease !important;
        }
        
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 44px !important;
            padding-left: 15px !important;
            color: #374151 !important;
            font-weight: 500 !important;
        }
        
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 44px !important;
            right: 10px !important;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow b {
            border-color: #6366f1 transparent transparent transparent !important;
            border-width: 6px 6px 0 6px !important;
        }

        .select2-container--default.select2-container--focus .select2-selection--single,
        .select2-container--default.select2-container--open .select2-selection--single {
            border-color: #6366f1 !important;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1) !important;
        }

        .select2-dropdown {
            border: 2px solid #e5e7eb !important;
            border-radius: 8px !important;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15) !important;
            background: white !important;
        }

        .select2-search--dropdown .select2-search__field {
            padding: 12px 15px !important;
            border: 2px solid #e5e7eb !important;
            border-radius: 6px !important;
            font-size: 14px !important;
            margin: 8px !important;
            width: calc(100% - 16px) !important;
        }

        .select2-search--dropdown .select2-search__field:focus {
            border-color: #6366f1 !important;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1) !important;
            outline: none !important;
        }

        .select2-results__option {
            padding: 12px 15px !important;
            font-size: 14px !important;
            color: #374151 !important;
            transition: all 0.2s ease !important;
        }

        .select2-results__option--highlighted[aria-selected] {
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%) !important;
            color: white !important;
        }

        .select2-results__option[aria-selected="true"] {
            background: #f3f4f6 !important;
            color: #6366f1 !important;
            font-weight: 600 !important;
        }

        /* Product table styling */
        .available-stock-mobile {
            color: #059669;
            font-weight: 700;
        }

        .quantity-controls {
            margin-top: 16px;
        }

        .quantity-group {
            margin-bottom: 0;
        }

        .carton-pieces-group {
            margin-top: 12px;
        }

        .add-product-container {
            text-align: center;
            margin-top: 20px;
        }

        /* Prevent double-tap zoom on buttons */
        .btn {
            touch-action: manipulation;
            -webkit-touch-callout: none;
            -webkit-user-select: none;
            -khtml-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
            user-select: none;
        }

        /* Desktop specific styles - FIXED */
        @media (min-width: 769px) {
            /* Hide mobile elements on desktop */
            .mobile-products-container {
                display: none !important;
            }

            .mobile-bottom-actions {
                display: none !important;
            }

            /* Show desktop elements */
            .desktop-products-container {
                display: block !important;
            }

            .desktop-only {
                display: block !important;
            }

            .btn-block-mobile {
                width: auto;
            }

            .mobile-button-group {
                flex-direction: row;
            }
        }

        /* Mobile Responsive - FIXED */
        @media (max-width: 768px) {
            /* Hide desktop elements on mobile */
            .desktop-products-container {
                display: none !important;
            }

            .desktop-only {
                display: none !important;
            }

            /* Show mobile elements */
            .mobile-products-container {
                display: block !important;
            }

            .mobile-bottom-actions {
                display: block !important;
                position: fixed;
                bottom: 0;
                left: 0;
                right: 0;
                background: white;
                padding: 16px;
                box-shadow: 0 -4px 20px rgba(0, 0, 0, 0.1);
                z-index: 1000;
            }

            /* Adjust body padding for fixed bottom */
            body {
                padding-bottom: 80px;
            }

            /* Mobile button styles */
            .btn-block-mobile {
                width: 100%;
            }

            .btn-text {
                display: none;
            }

            .mobile-button-group {
                flex-direction: column;
            }
            
            .mobile-submit-btn {
                width: 100%;
                margin-bottom: 8px;
            }
            
            .mobile-submit-btn:last-child {
                margin-bottom: 0;
            }

            /* Collapsible transport section */
            #transport-info {
                display: none;
            }

            #transport-info.show {
                display: block;
            }

            /* Modal adjustments */
            .modal-dialog-mobile {
                margin: 10px;
                max-width: calc(100% - 20px);
            }

            .modal-content {
                border-radius: 12px;
            }

            /* Form adjustments */
            .form-label {
                font-size: 14px;
            }

            .modern-input, .modern-select {
                font-size: 16px; /* Prevent zoom on iOS */
            }

            /* Card adjustments */
            .modern-card {
                margin-bottom: 16px;
                box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            }

            .modern-card-body {
                padding: 16px;
            }

            /* Header adjustments */
            .header-actions-group {
                flex-direction: column;
                width: 100%;
                gap: 8px;
            }

            .header-actions-group .btn {
                width: 100%;
                justify-content: center;
            }

            /* Table in modal */
            .modal-body table {
                font-size: 12px;
            }

            .modal-body th,
            .modal-body td {
                padding: 8px 4px;
            }

            /* Mobile Select2 adjustments */
            .select2-container .select2-selection--single {
                height: 48px !important;
                line-height: 48px !important;
                font-size: 16px !important;
            }

            .select2-container--default .select2-selection--single .select2-selection__rendered {
                line-height: 48px !important;
                font-size: 16px !important;
            }

            .select2-container--default .select2-selection--single .select2-selection__arrow {
                height: 48px !important;
            }

            .select2-dropdown {
                font-size: 16px !important;
            }

            .select2-search--dropdown .select2-search__field {
                font-size: 16px !important;
                padding: 14px 15px !important;
                height: 48px !important;
            }

            .select2-results__option {
                padding: 14px 15px !important;
                font-size: 16px !important;
            }
        }

        /* Tablet adjustments */
        @media (min-width: 769px) and (max-width: 1024px) {
            .modern-table {
                font-size: 13px;
            }

            .modern-thead th {
                padding: 14px 12px;
                font-size: 12px;
            }

            .modern-tbody td {
                padding: 12px;
            }
        }

        /* Loading state */
        .btn:disabled {
            opacity: 0.7;
            cursor: not-allowed;
        }

        /* Animation for adding/removing rows */
        .product-row,
        .mobile-product-card {
            animation: slideIn 0.3s ease-out;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Error state */
        .is-invalid {
            border-color: #ef4444 !important;
        }

        .is-invalid:focus {
            box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1) !important;
        }

        /* Inherit all other styles from cash flow design */
        .modern-card {
            background: white;
            border: none;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            margin-bottom: 24px;
            backdrop-filter: blur(10px);
            overflow: hidden;
        }

        .modern-header {
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
            color: white;
            border-radius: 16px 16px 0 0;
            padding: 20px 24px;
            border-bottom: none;
        }

        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            width: 100%;
        }

        .header-title {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .header-icon {
            font-size: 24px;
            color: white;
        }

        .card-title {
            color: white;
            font-weight: 600;
            margin: 0;
            font-size: 18px;
        }

        .modern-card-body {
            padding: 24px;
            background: white;
        }

        .modern-input, .modern-select {
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            padding: 10px 15px;
            background: white;
            color: #374151;
            transition: all 0.2s ease;
            font-size: 14px;
        }

        .modern-input:focus, .modern-select:focus {
            outline: none;
            border-color: #6366f1;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
        }

        .modern-table-responsive {
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            background: white;
        }

        .modern-table {
            margin-bottom: 0;
            background: white !important;
            color: #1f2937 !important;
            width: 100%;
        }

        .modern-thead {
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%) !important;
            border-bottom: none;
        }

        .modern-thead th {
            border: none !important;
            padding: 18px 16px;
            font-weight: 600;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: white !important;
            white-space: nowrap;
            background: transparent !important;
        }

        .modern-tbody {
            background: white !important;
        }

        .modern-tbody tr {
            transition: all 0.2s ease;
            border-bottom: 1px solid #f1f5f9;
            background: white !important;
        }

        .modern-tbody tr:hover {
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%) !important;
        }

        .modern-tbody td {
            padding: 16px;
            vertical-align: middle;
            border: none !important;
            font-size: 14px;
            color: #374151 !important;
            background: transparent !important;
        }

        .modern-btn {
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 14px;
            border: 2px solid transparent;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            cursor: pointer;
        }

        .modern-btn-primary {
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
            color: white;
            border-color: #6366f1;
        }

        .modern-btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(99, 102, 241, 0.4);
            color: white;
        }

        .modern-btn-outline {
            background: white;
            color: #6366f1;
            border-color: #6366f1;
        }

        .modern-btn-outline:hover {
            background: #6366f1;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(99, 102, 241, 0.3);
        }

        .modern-btn-info {
            background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);
            color: white;
            border-color: #06b6d4;
        }

        .modern-btn-info:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(6, 182, 212, 0.4);
            color: white;
        }

        .header-actions-group {
            display: flex;
            gap: 12px;
            align-items: center;
        }

        .modern-alert {
            border: none;
            border-radius: 12px;
            padding: 16px 20px;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .modern-alert i {
            font-size: 18px;
        }

        /* Collapsible header */
        .collapsible-header {
            cursor: pointer;
            position: relative;
        }

        .collapse-icon {
            transition: transform 0.3s ease;
        }

        .collapsible-header[aria-expanded="false"] .collapse-icon {
            transform: rotate(180deg);
        }

        /* Mobile Product Card */
        .mobile-product-card {
            background: #f8fafc;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 16px;
            border: 2px solid #e5e7eb;
            position: relative;
        }

        .card-header-mobile {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 16px;
        }

        .product-number {
            font-weight: 600;
            color: #6366f1;
            font-size: 16px;
        }

        .btn-remove-mobile {
            background: #ef4444;
            color: white;
            border: none;
            border-radius: 50%;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .btn-remove-mobile:hover {
            background: #dc2626;
            transform: scale(1.1);
        }

        .stock-info {
            font-size: 12px;
            color: #6b7280;
            margin-top: 4px;
            font-weight: 600;
        }

        .available-stock {
            background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%);
            color: #374151;
            font-weight: 600;
        }

        /* Modal styling */
        .modern-modal {
            border-radius: 16px;
            border: none;
            overflow: hidden;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
        }

        .modern-modal-header {
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
            color: white;
            border-bottom: none;
            padding: 20px 24px;
        }

        .modern-modal-header .modal-title {
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .modern-modal-header .close {
            color: white;
            opacity: 0.8;
            font-size: 24px;
        }

        .modern-modal-header .close:hover {
            opacity: 1;
        }

        /* Button styling */
        .modern-btn-sm {
            padding: 6px 10px;
            border-radius: 6px;
            font-size: 12px;
            border: none;
            transition: all 0.2s ease;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 32px;
            height: 32px;
        }

        .modern-btn-sm.modern-btn-danger {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: white;
        }

        .modern-btn-sm:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }
    </style>
@stop

@section('additional_js')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"></script>
    
    <script>
$(document).ready(function() {
    // Configure toastr
    toastr.options = {
        "closeButton": true,
        "newestOnTop": true,
        "progressBar": true,
        "positionClass": "toast-top-right",
        "timeOut": "3000"
    };

    // Auto-focus first field when coming from "Create and New"
    @if(session('auto_focus'))
        setTimeout(function() {
            $('#recipient_name').focus();
            toastr.success('Ready to create another delivery!');
        }, 500);
    @endif

    // Initialize Select2 with search
    initializeSelect2();
    
    // Better mobile detection
    function isMobileView() {
        return window.innerWidth <= 768;
    }
    
    // Recipient selection functionality
    $('#recipient_select').on('change', function() {
        var selectedOption = $(this).find('option:selected');
        var recipientName = selectedOption.val();
        
        if (recipientName) {
            // Fill form fields
            $('#recipient_name').val(recipientName);
            $('#recipient_phone').val(selectedOption.data('phone'));
            $('#recipient_address').val(selectedOption.data('address'));
            
            // Show history button
            $('#history-button-group').show();
            
            toastr.success('Recipient details loaded');
        } else {
            // Clear form fields
            $('#recipient_name').val('');
            $('#recipient_phone').val('');
            $('#recipient_address').val('');
            $('#history-button-group').hide();
        }
    });

    // View history button
    $('#view-history-btn').on('click', function() {
        var recipientName = $('#recipient_name').val();
        if (recipientName) {
            loadDeliveryHistory(recipientName);
        } else {
            toastr.warning('Please enter a recipient name first');
        }
    });

    // Load delivery history function
    function loadDeliveryHistory(recipientName) {
        $('#history-recipient-name').text(recipientName);
        $('#view-full-history').attr('href', '/other-deliveries/recipient/' + encodeURIComponent(recipientName) + '/history');
        
        $.ajax({
            url: '{{ route("other-deliveries.recipient-history-ajax", ":name") }}'.replace(':name', encodeURIComponent(recipientName)),
            type: 'GET',
            beforeSend: function() {
                $('#delivery-history-body').html('<tr><td colspan="5" class="text-center"><i class="fas fa-spinner fa-spin"></i> Loading...</td></tr>');
            },
            success: function(deliveries) {
                var html = '';
                if (deliveries.length > 0) {
                    deliveries.forEach(function(delivery) {
                        var itemsList = delivery.items.map(function(item) {
                            return item.product.name + ' (' + item.quantity + ')';
                        }).join(', ');
                        
                        var statusBadge = '';
                        switch(delivery.status) {
                            case 'delivered':
                                statusBadge = '<span class="status-badge status-delivered"><i class="fas fa-check-circle"></i> Delivered</span>';
                                break;
                            case 'pending':
                                statusBadge = '<span class="status-badge status-pending"><i class="fas fa-clock"></i> Pending</span>';
                                break;
                            case 'in_transit':
                                statusBadge = '<span class="status-badge status-in-transit"><i class="fas fa-truck"></i> In Transit</span>';
                                break;
                            case 'cancelled':
                                statusBadge = '<span class="status-badge status-cancelled"><i class="fas fa-times-circle"></i> Cancelled</span>';
                                break;
                        }
                        
                        html += '<tr>' +
                            '<td>' + new Date(delivery.delivery_date).toLocaleDateString() + '</td>' +
                            '<td><strong>' + delivery.challan_number + '</strong></td>' +
                            '<td>' + itemsList + '</td>' +
                            '<td>' + statusBadge + '</td>' +
                            '<td>' +
                                '<a href="/other-deliveries/' + delivery.id + '" class="btn modern-btn-sm modern-btn-info" title="View Details">' +
                                    '<i class="fas fa-eye"></i>' +
                                '</a>' +
                            '</td>' +
                            '</tr>';
                    });
                } else {
                    html = '<tr><td colspan="5" class="text-center text-muted">No delivery history found</td></tr>';
                }
                
                $('#delivery-history-body').html(html);
                $('#historyModal').modal('show');
            },
            error: function() {
                toastr.error('Error loading delivery history');
                $('#delivery-history-body').html('<tr><td colspan="5" class="text-center text-danger">Error loading history</td></tr>');
            }
        });
    }
    
    // Add new row function
    $('#add-row').click(function() {
        if (isMobileView()) {
            addMobileProductCard();
        } else {
            addDesktopProductRow();
        }
        toastr.success('New product row added');
    });
    
    // Add desktop product row function
    function addDesktopProductRow() {
        const newRow = $('.product-row:first').clone();
        
        // Clear all input values
        newRow.find('input').val('');
        newRow.find('select').val('');
        newRow.find('.available-stock').text('0');
        
        // Remove any existing Select2 containers
        newRow.find('.select2-container').remove();
        
        // Reset select elements
        newRow.find('select').removeClass('select2-hidden-accessible');
        
        // Append to table
        $('#products-table tbody').append(newRow);
        
        // Initialize Select2 for the new row only
        newRow.find('.select2-searchable').select2({
            placeholder: "Search and select product...",
            allowClear: true,
            width: '100%'
        });
    }
    
    // Add mobile product card function
    function addMobileProductCard() {
        const currentCount = $('.mobile-product-card').length;
        const newIndex = currentCount;
        
        const newCard = $('.mobile-product-card:first').clone();
        newCard.attr('data-row-index', newIndex);
        newCard.find('.product-number').text('Product #' + (currentCount + 1));
        
        // Clear all input values
        newCard.find('input').val('');
        newCard.find('select').val('');
        newCard.find('.available-stock-mobile').text('0');
        newCard.find('.carton-pieces-group').hide();
        newCard.find('.quantity-type-mobile').val('quantity');
        
        // Remove any existing Select2 containers
        newCard.find('.select2-container').remove();
        
        // Reset select elements
        newCard.find('select').removeClass('select2-hidden-accessible');

        // Append to container
        $('#mobile-products').append(newCard);
        
        // Initialize Select2 for the new card only
        newCard.find('.select2-searchable').select2({
            placeholder: "Search and select product...",
            allowClear: true,
            width: '100%',
            dropdownParent: newCard
        });
    }
    
    // Remove row (Desktop)
    $(document).on('click', '.remove-row', function() {
        if ($('.product-row').length > 1) {
            $(this).closest('tr').fadeOut(300, function() {
                $(this).remove();
            });
            toastr.info('Product row removed');
        } else {
            toastr.warning('At least one product is required');
        }
    });
    
    // Remove row (Mobile)
    $(document).on('click', '.remove-row-mobile', function() {
        if ($('.mobile-product-card').length > 1) {
            $(this).closest('.mobile-product-card').fadeOut(300, function() {
                $(this).remove();
                updateMobileProductNumbers();
            });
            toastr.info('Product removed');
        } else {
            toastr.warning('At least one product is required');
        }
    });
    
    // Update mobile product numbers
    function updateMobileProductNumbers() {
        $('.mobile-product-card').each(function(index) {
            $(this).find('.product-number').text('Product #' + (index + 1));
        });
    }
    
    // Product selection change (Desktop)
    $(document).on('change', '.product-select', function() {
        const row = $(this).closest('tr');
        const selectedOption = $(this).find('option:selected');
        const stock = selectedOption.data('stock') || 0;
        const boxPcs = selectedOption.data('box-pcs') || 0;
        const piecesFeet = selectedOption.data('pieces-feet') || 0;
        
        row.find('.available-stock').text(stock);
        row.find('.box-pcs').val(boxPcs);
        row.find('.pieces-feet').val(piecesFeet);
        row.find('.quantity-field').attr('max', stock);
        
        // Reset quantity fields
        row.find('.quantity-field').val('');
        row.find('.carton-field').val('');
        row.find('.pieces-field').val('');
    });
    
    // Product selection change (Mobile)
    $(document).on('change', '.product-select-mobile', function() {
        const card = $(this).closest('.mobile-product-card');
        const selectedOption = $(this).find('option:selected');
        const stock = selectedOption.data('stock') || 0;
        const boxPcs = selectedOption.data('box-pcs') || 0;
        const piecesFeet = selectedOption.data('pieces-feet') || 0;
        
        card.find('.available-stock-mobile').text(stock);
        card.find('.box-pcs-mobile').val(boxPcs);
        card.find('.pieces-feet-mobile').val(piecesFeet);
        card.find('.quantity-field-mobile').attr('max', stock);
        
        // Reset quantity fields
        card.find('.quantity-field-mobile').val('');
        card.find('.carton-field-mobile').val('');
        card.find('.pieces-field-mobile').val('');
    });
    
    // Initialize quantity type handling
    initializeQuantityTypes();
    
    // Calculate boxes and pieces on quantity change (Desktop)
    $(document).on('input', '.quantity-field', function() {
        calculateCartonsAndPieces($(this).closest('tr'));
    });
    
    // Calculate boxes and pieces on quantity change (Mobile)
    $(document).on('input', '.quantity-field-mobile', function() {
        calculateCartonsAndPiecesMobile($(this).closest('.mobile-product-card'));
    });
    
    // Calculate quantity based on cartons and pieces (Desktop)
    $(document).on('input', '.carton-field, .pieces-field', function() {
        calculateQuantityFromCartonPieces($(this).closest('tr'));
    });
    
    // Calculate quantity based on cartons and pieces (Mobile)
    $(document).on('input', '.carton-field-mobile, .pieces-field-mobile', function() {
        calculateQuantityFromCartonPiecesMobile($(this).closest('.mobile-product-card'));
    });
    
    // Mobile quantity type change
    $(document).on('change', '.quantity-type-mobile', function() {
        const card = $(this).closest('.mobile-product-card');
        const value = $(this).val();

        if (value === 'quantity') {
            card.find('.carton-pieces-group').hide();
            card.find('.quantity-group').show();
            card.find('.carton-field-mobile, .pieces-field-mobile').prop('readonly', true);
            card.find('.quantity-field-mobile').prop('readonly', false);
        } else {
            card.find('.carton-pieces-group').show();
            card.find('.carton-field-mobile, .pieces-field-mobile').prop('readonly', false);
            card.find('.quantity-field-mobile').prop('readonly', true);
            
            // Initialize if empty
            if (!card.find('.carton-field-mobile').val() && !card.find('.pieces-field-mobile').val()) {
                calculateCartonsAndPiecesMobile(card);
            }
        }
    });
    
    // FIXED: Enhanced form validation and submission
    $('#delivery-form').on('submit', function(e) {
        console.log('Form submission started');
        
        let valid = true;
        let errorMessage = '';
        const isMobile = isMobileView();
        
        console.log('Is mobile view:', isMobile);
        
        // Clear previous error states
        $('.is-invalid').removeClass('is-invalid');
        
        // Check if at least one product has a quantity
        let hasProducts = false;
        
        if (isMobile) {
            console.log('Validating mobile fields');
            // Only validate visible mobile fields
            $('.mobile-product-card:visible').each(function(index) {
                const productSelect = $(this).find('.product-select-mobile');
                const quantityField = $(this).find('.quantity-field-mobile');
                const quantity = parseFloat(quantityField.val());
                
                console.log(`Mobile product ${index}: Selected=${productSelect.val()}, Quantity=${quantity}`);
                
                if (productSelect.val() && quantity > 0) {
                    hasProducts = true;
                }
                
                if (productSelect.val() && (isNaN(quantity) || quantity <= 0)) {
                    valid = false;
                    errorMessage = 'Please enter valid quantities for all products.';
                    quantityField.addClass('is-invalid');
                    return false;
                }
                
                const availableStock = parseFloat($(this).find('.available-stock-mobile').text());
                if (productSelect.val() && quantity > availableStock) {
                    valid = false;
                    errorMessage = 'Quantity cannot exceed available stock.';
                    quantityField.addClass('is-invalid');
                    return false;
                }
            });
            
            // Disable desktop fields to prevent submission conflicts
            $('.desktop-products-container input, .desktop-products-container select').prop('disabled', true);
            
        } else {
            console.log('Validating desktop fields');
            // Only validate visible desktop fields
            $('.product-row:visible').each(function(index) {
                const productSelect = $(this).find('.product-select');
                const quantityField = $(this).find('.quantity-field');
                const quantity = parseFloat(quantityField.val());
                
                console.log(`Desktop product ${index}: Selected=${productSelect.val()}, Quantity=${quantity}`);
                
                if (productSelect.val() && quantity > 0) {
                    hasProducts = true;
                }
                
                if (productSelect.val() && (isNaN(quantity) || quantity <= 0)) {
                    valid = false;
                    errorMessage = 'Please enter valid quantities for all products.';
                    quantityField.addClass('is-invalid');
                    return false;
                }
                
                const availableStock = parseFloat($(this).find('.available-stock').text());
                if (productSelect.val() && quantity > availableStock) {
                    valid = false;
                    errorMessage = 'Quantity cannot exceed available stock.';
                    quantityField.addClass('is-invalid');
                    return false;
                }
            });
            
            // Disable mobile fields to prevent submission conflicts
            $('.mobile-products-container input, .mobile-products-container select').prop('disabled', true);
        }
        
        if (!hasProducts) {
            valid = false;
            errorMessage = 'Please add at least one product with a quantity.';
        }
        
        console.log('Validation result:', valid, 'Has products:', hasProducts);
        
        if (!valid) {
            e.preventDefault(); // Only prevent if validation fails
            toastr.error(errorMessage);
            
            // Re-enable disabled fields for user to continue editing
            if (isMobile) {
                $('.desktop-products-container input, .desktop-products-container select').prop('disabled', false);
            } else {
                $('.mobile-products-container input, .mobile-products-container select').prop('disabled', false);
            }
            
            return false;
        }
        
        // Handle create and new button
        const clickedButton = $(document.activeElement);
        const isCreateAndNew = clickedButton.hasClass('create-and-new') || 
                               clickedButton.attr('id') === 'submit-btn-new' || 
                               clickedButton.attr('id') === 'submit-btn-new-desktop';
        
        console.log('Create and new:', isCreateAndNew);
        
        if (isCreateAndNew) {
            // Add hidden field for create and new
            if (!$('input[name="create_and_new"]').length) {
                $('<input>').attr({
                    type: 'hidden',
                    name: 'create_and_new',
                    value: '1'
                }).appendTo('#delivery-form');
            }
        }
        
        // Show loading state
        const submitBtn = clickedButton.length ? clickedButton : $('#submit-btn');
        const originalText = submitBtn.html();
        
        submitBtn.prop('disabled', true);
        
        if (isCreateAndNew) {
            submitBtn.html('<i class="fas fa-spinner fa-spin"></i> Creating & Preparing New...');
        } else {
            submitBtn.html('<i class="fas fa-spinner fa-spin"></i> Creating...');
        }
        
        console.log('Form validation passed, allowing natural submission');
        
        // FIXED: Let the form submit naturally - no preventDefault, no setTimeout
        // Just return true to allow normal form submission
        return true;
    });

    // FIXED: Button click handlers
    $(document).on('click', '.create-and-new', function(e) {
        e.preventDefault();
        console.log('Create and new button clicked');
        
        // Set a flag so form submission knows this was create-and-new
        $(this).addClass('active-create-new');
        $('#delivery-form').trigger('submit');
    });
    
    // FIXED: Regular submit button handlers
    $(document).on('click', '#submit-btn, #submit-btn-desktop', function(e) {
        if (!$(this).hasClass('create-and-new')) {
            e.preventDefault();
            console.log('Regular submit button clicked');
            $('#delivery-form').trigger('submit');
        }
    });
    
    // Handle both submit button types
    $(document).on('click', '#submit-btn-new, #submit-btn-new-desktop', function(e) {
        e.preventDefault();
        console.log('Create and new desktop button clicked');
        $(this).addClass('active-create-new');
        $('#delivery-form').trigger('submit');
    });
    
    // Window resize handler to update view
    $(window).on('resize', function() {
        setTimeout(function() {
            if (isMobileView()) {
                $('.mobile-products-container').show();
                $('.desktop-products-container').hide();
            } else {
                $('.mobile-products-container').hide();
                $('.desktop-products-container').show();
            }
        }, 100);
    });
});

function initializeSelect2() {
    // Destroy existing Select2 instances to prevent duplication
    $('.select2-searchable').each(function() {
        if ($(this).hasClass('select2-hidden-accessible')) {
            $(this).select2('destroy');
        }
    });
    
    // Initialize Select2 with enhanced styling
    $('.select2-searchable').select2({
        placeholder: "Search and select...",
        allowClear: true,
        width: '100%',
        theme: 'default'
    });
    
    // Make recipient select searchable
    $('#recipient_select').select2({
        placeholder: "Search for recipient or create new...",
        allowClear: true,
        width: '100%',
        theme: 'default'
    });
}

// Initialize quantity type handling
function initializeQuantityTypes() {
    $(document).on('change', '.quantity-type', function() {
        const row = $(this).closest('tr');
        const value = $(this).val();
        
        if (value === 'quantity') {
            row.find('.carton-field, .pieces-field').prop('readonly', true);
            row.find('.quantity-field').prop('readonly', false);
            calculateCartonsAndPieces(row);
        } else {
            row.find('.carton-field, .pieces-field').prop('readonly', false);
            row.find('.quantity-field').prop('readonly', true);
            
            if (!row.find('.carton-field').val() && !row.find('.pieces-field').val()) {
                calculateCartonsAndPieces(row);
            }
        }
    });
    
    $('.quantity-type').trigger('change');
}

// Calculate cartons and pieces (Desktop)
function calculateCartonsAndPieces(row) {
    const quantity = parseFloat(row.find('.quantity-field').val()) || 0;
    const boxPcs = parseFloat(row.find('.box-pcs').val()) || 0;
    const piecesFeet = parseFloat(row.find('.pieces-feet').val()) || 0;
    
    if (boxPcs > 0 && piecesFeet > 0) {
        const totalPieces = Math.round(quantity / piecesFeet);
        const cartons = Math.floor(totalPieces / boxPcs);
        const pieces = totalPieces - (cartons * boxPcs);
        
        row.find('.carton-field').val(cartons);
        row.find('.pieces-field').val(pieces);
    } else {
        row.find('.carton-field').val('');
        row.find('.pieces-field').val('');
    }
}

// Calculate cartons and pieces (Mobile)
function calculateCartonsAndPiecesMobile(card) {
    const quantity = parseFloat(card.find('.quantity-field-mobile').val()) || 0;
    const boxPcs = parseFloat(card.find('.box-pcs-mobile').val()) || 0;
    const piecesFeet = parseFloat(card.find('.pieces-feet-mobile').val()) || 0;
    
    if (boxPcs > 0 && piecesFeet > 0) {
        const totalPieces = Math.round(quantity / piecesFeet);
        const cartons = Math.floor(totalPieces / boxPcs);
        const pieces = totalPieces - (cartons * boxPcs);
        
        card.find('.carton-field-mobile').val(cartons);
        card.find('.pieces-field-mobile').val(pieces);
    } else {
        card.find('.carton-field-mobile').val('');
        card.find('.pieces-field-mobile').val('');
    }
}

// Calculate quantity from cartons and pieces (Desktop)
function calculateQuantityFromCartonPieces(row) {
    const cartons = parseInt(row.find('.carton-field').val()) || 0;
    const pieces = parseInt(row.find('.pieces-field').val()) || 0;
    const boxPcs = parseFloat(row.find('.box-pcs').val()) || 0;
    const piecesFeet = parseFloat(row.find('.pieces-feet').val()) || 0;
    
    if (boxPcs > 0 && piecesFeet > 0) {
        const totalPieces = (cartons * boxPcs) + pieces;
        const quantity = (totalPieces * piecesFeet).toFixed(2);
        const maxQuantity = parseFloat(row.find('.available-stock').text());
        
        if (quantity > maxQuantity) {
            toastr.warning('Quantity exceeds available amount. Adjusting to maximum.');
            row.find('.quantity-field').val(maxQuantity);
            calculateCartonsAndPieces(row);
        } else {
            row.find('.quantity-field').val(quantity);
        }
    }
}

// Calculate quantity from cartons and pieces (Mobile)
function calculateQuantityFromCartonPiecesMobile(card) {
    const cartons = parseInt(card.find('.carton-field-mobile').val()) || 0;
    const pieces = parseInt(card.find('.pieces-field-mobile').val()) || 0;
    const boxPcs = parseFloat(card.find('.box-pcs-mobile').val()) || 0;
    const piecesFeet = parseFloat(card.find('.pieces-feet-mobile').val()) || 0;
    
    if (boxPcs > 0 && piecesFeet > 0) {
        const totalPieces = (cartons * boxPcs) + pieces;
        const quantity = (totalPieces * piecesFeet).toFixed(2);
        const maxQuantity = parseFloat(card.find('.available-stock-mobile').text());
        
        if (quantity > maxQuantity) {
            toastr.warning('Quantity exceeds available amount. Adjusting to maximum.');
            card.find('.quantity-field-mobile').val(maxQuantity);
            calculateCartonsAndPiecesMobile(card);
        } else {
            card.find('.quantity-field-mobile').val(quantity);
        }
    }
}
</script>
@stop