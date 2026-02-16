@extends('layouts.modern-admin')

@section('title', 'Create Challan')

@section('page_title', 'Create New Delivery Challan')

@section('header_actions')
    <a href="{{ route('challans.index') }}" class="btn modern-btn modern-btn-secondary">
        <i class="fas fa-arrow-left"></i> Back to Challans
    </a>
@stop

@section('page_content')
    <!-- Error Messages -->
    @if(session('error'))
        <div class="alert modern-alert modern-alert-error" id="error-alert">
            <div class="alert-content">
                <i class="fas fa-exclamation-triangle alert-icon"></i>
                <div class="alert-message">
                    <strong>Error!</strong>
                    <span>{{ session('error') }}</span>
                </div>
                <button type="button" class="alert-close" onclick="this.parentElement.parentElement.style.display='none'">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
    @endif

    <form action="{{ route('challans.store') }}" method="POST" id="challan-form">
        @csrf
        
        <!-- Challan and Invoice Information Row -->
        <div class="row mb-4">
            <!-- Challan Info Section -->
            <div class="col-md-6">
                <div class="card modern-card">
                    <div class="card-header modern-header challan-header">
                        <h3 class="card-title">
                            <i class="fas fa-file-alt"></i> Challan Information
                        </h3>
                    </div>
                    <div class="card-body modern-card-body">
                        <div class="form-group modern-form-group">
                            <label for="challan_number" class="modern-label">
                                Challan Number <span class="required">*</span>
                            </label>
                            <input type="text" name="challan_number" id="challan_number" 
                                   class="form-control modern-input" value="{{ $challan_number }}" readonly>
                            <small class="form-text text-muted">Auto-generated</small>
                        </div>
                        
                        <div class="form-group modern-form-group">
                            <label for="challan_date" class="modern-label">
                                Challan Date <span class="required">*</span>
                            </label>
                            <input type="date" name="challan_date" id="challan_date" 
                                   class="form-control modern-input" value="{{ $challan_date }}" required>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Invoice Selection Section -->
            <div class="col-md-6">
                <div class="card modern-card">
                    <div class="card-header modern-header invoice-header">
                        <h3 class="card-title">
                            <i class="fas fa-file-invoice"></i> Invoice Information
                        </h3>
                    </div>
                    <div class="card-body modern-card-body">
                        @php
                            $selectedInvoiceId = old('invoice_id', $invoice_id);
                        @endphp
                        <div class="form-group modern-form-group">
                            <label for="invoice_id" class="modern-label">
                                Select Invoice <span class="required">*</span>
                            </label>
                            <select name="invoice_id" id="invoice_id" class="form-control select2 modern-select" required>
                                <option value="">Select Invoice</option>
                                @foreach($invoices as $invoice)
                                    <option value="{{ $invoice->id }}" {{ (string) $selectedInvoiceId === (string) $invoice->id ? 'selected' : '' }}>
                                        {{ $invoice->invoice_number }} - {{ $invoice->customer->name }} ({{ $invoice->invoice_date->format('d-m-Y') }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Delivery Information -->
        <div class="card modern-card mb-4">
            <div class="card-header modern-header delivery-header">
                <h3 class="card-title">
                    <i class="fas fa-truck"></i> Delivery Information
                </h3>
            </div>
            <div class="card-body modern-card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group modern-form-group">
                            <label for="shipping_address" class="modern-label">
                                Shipping Address <span class="required">*</span>
                            </label>
                            <textarea name="shipping_address" id="shipping_address"
                                      class="form-control modern-textarea" rows="3" required>{{ $invoice ? $invoice->customer->address : '' }}</textarea>
                        </div>

                        <div class="form-group modern-form-group">
                            <label for="receiver_name" class="modern-label">
                                Receiver Name <span class="required">*</span>
                            </label>
                            <input type="text" name="receiver_name" id="receiver_name"
                                   class="form-control modern-input" value="{{ $invoice ? $invoice->customer->name : '' }}" required>
                        </div>

                        <div class="form-group modern-form-group">
                            <label for="receiver_phone" class="modern-label">Receiver Phone</label>
                            <input type="text" name="receiver_phone" id="receiver_phone"
                                   class="form-control modern-input" value="{{ $invoice ? $invoice->customer->phone : '' }}">
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group modern-form-group">
                            <label for="vehicle_number" class="modern-label">Vehicle Number</label>
                            <input type="text" name="vehicle_number" id="vehicle_number" 
                                   class="form-control modern-input">
                        </div>
                        
                        <div class="form-group modern-form-group">
                            <label for="driver_name" class="modern-label">Driver Name</label>
                            <input type="text" name="driver_name" id="driver_name" 
                                   class="form-control modern-input">
                        </div>
                        
                        <div class="form-group modern-form-group">
                            <label for="driver_phone" class="modern-label">Driver Phone</label>
                            <input type="text" name="driver_phone" id="driver_phone" 
                                   class="form-control modern-input">
                        </div>
                    </div>
                </div>
                
                <div class="form-group modern-form-group">
                    <label for="notes" class="modern-label">Notes</label>
                    <textarea name="notes" id="notes" class="form-control modern-textarea" rows="2"></textarea>
                </div>
            </div>
        </div>
        
        <!-- Invoice Items Section -->
        <div class="card modern-card mb-4">
            <div class="card-header modern-header items-header">
                <h3 class="card-title">
                    <i class="fas fa-boxes"></i> Items for Delivery
                </h3>
            </div>
            <div class="card-body modern-card-body">
                @if(!$invoiceItems || $invoiceItems->isEmpty())
                    <div class="alert modern-alert modern-alert-info" id="no-items-alert">
                        <div class="alert-content">
                            <i class="fas fa-info-circle alert-icon"></i>
                            <div class="alert-message">
                                Select an invoice to view available items for delivery.
                            </div>
                        </div>
                    </div>
                @endif
                
                <div id="invoice-items-container" class="{{ !$invoiceItems || $invoiceItems->isEmpty() ? 'd-none' : '' }}">
                    <div class="table-container">
                        <div class="table-responsive modern-table-responsive">
                            <table class="table modern-table" id="items-table">
                                <thead class="modern-thead">
                                    <tr>
                                        <th width="5%">
                                            <div class="th-content">
                                                <i class="fas fa-check"></i>
                                            </div>
                                        </th>
                                        <th width="20%">
                                            <div class="th-content">
                                                <span>Product</span>
                                            </div>
                                        </th>
                                        <th width="20%">
                                            <div class="th-content">
                                                <span>Description</span>
                                            </div>
                                        </th>
                                        <th width="10%">
                                            <div class="th-content">
                                                <span>Available</span>
                                            </div>
                                        </th>
                                        @if($godowns->isNotEmpty())
                                            <th width="15%">
                                                <div class="th-content">
                                                    <span>Godown</span>
                                                </div>
                                            </th>
                                        @endif
                                        <th width="10%">
                                            <div class="th-content">
                                                <span>Quantity</span>
                                            </div>
                                        </th>
                                        <th width="10%">
                                            <div class="th-content">
                                                <span>Unit</span>
                                            </div>
                                        </th>
                                        <th width="10%">
                                            <div class="th-content">
                                                <span>Boxes</span>
                                            </div>
                                        </th>
                                        <th width="10%">
                                            <div class="th-content">
                                                <span>Pieces</span>
                                            </div>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="modern-tbody">
                                    @if($invoiceItems && $invoiceItems->isNotEmpty())
                                        @foreach($invoiceItems as $index => $item)
                                            <tr class="item-row">
                                                <td>
                                                    <div class="modern-checkbox">
                                                        <input type="checkbox" name="item_selected[]" id="item{{ $index }}" 
                                                               value="1" checked class="item-checkbox">
                                                        <label for="item{{ $index }}"></label>
                                                    </div>
                                                </td>
                                                <td>
                                                    <input type="hidden" name="invoice_item_id[]" value="{{ $item->id }}">
                                                    <input type="hidden" name="product_id[]" value="{{ $item->product_id }}">
                                                    <strong>{{ $item->product->name ?? $item->description ?? 'Product' }}</strong>
                                                </td>
                                                <td>
                                                    <input type="text" name="description[]"
                                                           class="form-control modern-input-sm"
                                                           value="{{ $item->description ?: ($item->code ?: ($item->product->name ?? '')) }}">
                                                </td>
                                                <td>
                                                    <span class="availability-badge">{{ $item->remaining_quantity }}</span>
                                                    <input type="hidden" class="remaining-quantity" value="{{ $item->remaining_quantity }}">
                                                    
                                                    @if($item->product && $item->product->category)
                                                        <input type="hidden" class="box-pcs" value="{{ $item->product->category->box_pcs }}">
                                                        <input type="hidden" class="pieces-feet" value="{{ $item->product->category->pieces_feet }}">
                                                    @else
                                                        <input type="hidden" class="box-pcs" value="0">
                                                        <input type="hidden" class="pieces-feet" value="0">
                                                    @endif
                                                </td>
                                                @if($godowns->isNotEmpty())
                                                    <td>
                                                        <select name="godown_id[]" class="form-control modern-select-sm godown-select">
                                                            <option value="">Select Godown</option>
                                                            @foreach(($item->godowns ?? collect()) as $godown)
                                                                <option value="{{ $godown->id }}"
                                                                    {{ (old('godown_id.' . $index, $item->recommended_godown_id ?? null) == $godown->id) ? 'selected' : '' }}>
                                                                    {{ $godown->name }}{{ $godown->location ? ' - ' . $godown->location : '' }} ({{ number_format($godown->stock, 2) }})
                                                                </option>
                                                            @endforeach
                                                            @if(isset($item->godowns) && $item->godowns->isEmpty())
                                                                <option value="" disabled>No stock available</option>
                                                            @endif
                                                        </select>
                                                    </td>
                                                @endif
                                                <td>
                                                    <input type="number" name="quantity[]" 
                                                           class="form-control modern-input-sm quantity-field" 
                                                           min="0.01" max="{{ $item->remaining_quantity }}" 
                                                           step="0.01" value="{{ $item->remaining_quantity }}" required>
                                                </td>
                                                <td>
                                                    <select class="form-control modern-select-sm quantity-type">
                                                        <option value="quantity">Quantity</option>
                                                        <option value="box_pieces">Box/Pieces</option>
                                                    </select>
                                                </td>
                                                <td>
                                                    <input type="number" name="boxes[]" 
                                                           class="form-control modern-input-sm box-field" min="0" readonly>
                                                </td>
                                                <td>
                                                    <input type="number" name="pieces[]" 
                                                           class="form-control modern-input-sm pieces-field" min="0" readonly>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @endif
                                </tbody>
                                <tfoot>
                                    <tr class="totals-row">
                                        <td colspan="{{ $godowns->isNotEmpty() ? 5 : 4 }}" class="text-right font-weight-bold">
                                            Totals:
                                        </td>
                                        <td>
                                            <input type="text" id="total-quantity" class="form-control modern-input compact-input font-weight-bold" value="0.00" readonly>
                                        </td>
                                        <td></td>
                                        <td>
                                            <input type="text" id="total-boxes" class="form-control modern-input compact-input font-weight-bold" value="0" readonly>
                                        </td>
                                        <td>
                                            <input type="text" id="total-pieces" class="form-control modern-input compact-input font-weight-bold" value="0" readonly>
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Submit Buttons -->
        <div class="text-center mt-4 mb-4">
            <button type="submit" class="btn modern-btn modern-btn-primary btn-lg" id="submit-btn">
                <i class="fas fa-save"></i> Create Challan
            </button>
            <a href="{{ route('challans.index') }}" class="btn modern-btn modern-btn-secondary btn-lg ml-3">
                <i class="fas fa-times"></i> Cancel
            </a>
        </div>
    </form>
@stop

@section('additional_css')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
    
    <style>
        /* Modern Form Enhancements */
        .modern-form-group {
            margin-bottom: 24px;
        }

        .modern-label {
            font-weight: 600;
            color: #374151;
            margin-bottom: 8px;
            font-size: 14px;
        }

        .modern-input, .modern-textarea, .modern-select {
            border: 2px solid #e5e7eb;
            border-radius: 10px;
            padding: 12px 16px;
            font-size: 14px;
            transition: all 0.2s ease;
            background: white;
            color: #374151;
        }

        .modern-input:focus, .modern-textarea:focus, .modern-select:focus {
            outline: none;
            border-color: #6366f1;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
            transform: translateY(-1px);
        }

        .modern-input-sm, .modern-select-sm {
            border: 1px solid #d1d5db;
            border-radius: 6px;
            padding: 8px 12px;
            font-size: 13px;
            transition: all 0.2s ease;
            background: white;
            color: #374151;
        }

        .modern-input-sm:focus, .modern-select-sm:focus {
            outline: none;
            border-color: #6366f1;
            box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.1);
        }

        /* Section-specific header colors */
        .challan-header {
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
        }

        .invoice-header {
            background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);
        }

        .delivery-header {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        }

        .items-header {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        }

        /* Enhanced Alerts */
        .modern-alert-info {
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.1) 0%, rgba(29, 78, 216, 0.05) 100%);
            border-left: 4px solid #3b82f6;
        }

        .modern-alert-info .alert-icon {
            color: #3b82f6;
        }

        /* Modern Table Enhancements */
        .table-container {
            position: relative;
            overflow: hidden;
            border-radius: 12px;
            background: white;
        }

        .modern-table-responsive {
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
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
            padding: 16px 12px;
            font-weight: 600;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: white !important;
            background: transparent !important;
        }

        .th-content {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            color: white;
            text-align: center;
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
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.1);
        }

        .modern-tbody td {
            padding: 12px;
            vertical-align: middle;
            border: none !important;
            font-size: 13px;
            color: #374151 !important;
            background: transparent !important;
        }

        /* Make table fields compact but clearly visible */
        #items-table .modern-input-sm,
        #items-table .modern-select-sm {
            height: 30px;
            padding: 2px 6px;
            font-size: 11px;
            border: 1.5px solid #94a3b8;
            border-radius: 6px;
            background: #ffffff;
            color: #0f172a;
        }

        #items-table .modern-input-sm:focus,
        #items-table .modern-select-sm:focus {
            border-color: #475569;
            box-shadow: 0 0 0 2px rgba(71, 85, 105, 0.15);
        }

        #items-table .modern-input-sm[readonly] {
            background: #f8fafc;
            border-color: #94a3b8;
            color: #334155;
            opacity: 1;
        }

        #items-table .quantity-field,
        #items-table .box-field,
        #items-table .pieces-field {
            min-width: 78px;
        }

        #items-table.modern-table td {
            padding: 8px 6px;
        }

        /* Modern Checkbox */
        .modern-checkbox {
            position: relative;
            display: inline-block;
        }

        .modern-checkbox input[type="checkbox"] {
            position: absolute;
            opacity: 0;
            cursor: pointer;
        }

        .modern-checkbox label {
            position: relative;
            display: inline-block;
            width: 20px;
            height: 20px;
            background: white;
            border: 2px solid #d1d5db;
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .modern-checkbox label:after {
            content: "";
            position: absolute;
            left: 6px;
            top: 2px;
            width: 6px;
            height: 10px;
            border: solid white;
            border-width: 0 2px 2px 0;
            transform: rotate(45deg);
            opacity: 0;
            transition: all 0.2s ease;
        }

        .modern-checkbox input[type="checkbox"]:checked + label {
            background: #6366f1;
            border-color: #6366f1;
        }

        .modern-checkbox input[type="checkbox"]:checked + label:after {
            opacity: 1;
        }

        .modern-checkbox:hover label {
            border-color: #6366f1;
            transform: translateY(-1px);
        }

        /* Availability Badge */
        .availability-badge {
            display: inline-block;
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
        }

        /* Select2 Enhancements */
        .select2-container .select2-selection--single {
            height: 46px;
            border: 2px solid #e5e7eb;
            border-radius: 10px;
            background: white;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 42px;
            color: #374151;
            padding-left: 16px;
            font-size: 14px;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 42px;
            right: 16px;
        }

        .select2-container--default.select2-container--focus .select2-selection--single {
            border-color: #6366f1;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
        }

        .select2-dropdown {
            border: none;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
            border-radius: 10px;
            overflow: hidden;
        }

        .select2-results__option {
            padding: 12px 16px;
            font-size: 14px;
            transition: all 0.2s ease;
        }

        .select2-results__option--highlighted {
            background: linear-gradient(135deg, rgba(99, 102, 241, 0.1) 0%, rgba(139, 92, 246, 0.05) 100%);
            color: #6366f1;
        }

        /* Form validation styles */
        .is-invalid {
            border-color: #ef4444 !important;
            box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1) !important;
        }

        .invalid-feedback {
            color: #ef4444;
            font-size: 12px;
            margin-top: 4px;
        }

        /* Required field indicator */
        .required {
            color: #ef4444;
            font-weight: 600;
        }

        /* Loading states */
        .loading {
            opacity: 0.7;
            pointer-events: none;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .modern-form-group {
                margin-bottom: 20px;
            }

            .modern-input, .modern-textarea, .modern-select {
                padding: 10px 14px;
                font-size: 16px; /* Prevents zoom on iOS */
            }

            .modern-tbody td {
                padding: 8px 6px;
                font-size: 12px;
            }

            .modern-thead th {
                padding: 12px 8px;
                font-size: 11px;
            }

            .availability-badge {
                font-size: 10px;
                padding: 2px 6px;
            }

            .modern-checkbox label {
                width: 18px;
                height: 18px;
            }

            .modern-checkbox label:after {
                left: 5px;
                top: 1px;
                width: 5px;
                height: 9px;
            }
        }

        /* Additional styles for enhanced UX */
        .disabled-row {
            opacity: 0.5;
            background: #f9fafb !important;
        }

        .readonly-field {
            background: #f9fafb !important;
            color: #6b7280 !important;
        }

        .readonly-field:focus {
            box-shadow: none !important;
            border-color: #d1d5db !important;
        }

        /* Enhanced button loading state */
        .btn:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none !important;
        }

        /* Form validation enhancement */
        .form-control.is-invalid:focus {
            border-color: #ef4444 !important;
            box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1) !important;
        }

        /* Select2 validation styles */
        .select2-container--default.is-invalid .select2-selection--single {
            border-color: #ef4444 !important;
            box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1) !important;
        }

        .compact-input {
            height: 30px;
            padding: 2px 6px;
            font-size: 11px;
        }

        .totals-row {
            background-color: #f8f9fa;
            border-top: 2px solid #dee2e6;
        }

        .totals-row td {
            padding: 8px 4px !important;
            vertical-align: middle;
            border: none !important;
        }

        .totals-row input {
            background-color: #e9ecef;
            border: 1.5px solid #94a3b8;
            font-weight: bold;
            text-align: center;
            color: #0f172a;
        }
    </style>
@stop

@section('additional_js')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            // Initialize Select2 with modern styling
            $('.select2').select2({
                width: '100%',
                placeholder: 'Select an option',
                allowClear: true
            });
            
            // Auto-hide alerts after 5 seconds
            setTimeout(function() {
                $('.modern-alert').addClass('alert-auto-hide');
            }, 5000);
            
            function loadInvoiceItems(invoiceId) {
                if (invoiceId) {
                    // Show loading with modern styling
                    $('#no-items-alert .alert-message').html('<i class="fas fa-spinner fa-spin"></i> Loading invoice items...');
                    $('#no-items-alert').removeClass('d-none').show();
                    $('#invoice-items-container').addClass('d-none');
                    
                    // Get invoice details with AJAX
                    $.ajax({
                        url: '/get-invoice-items/' + invoiceId,
                        type: 'GET',
                        dataType: 'json',
                        success: function(response) {
                            const invoice = response.invoice || {};
                            const items = response.items || [];
                            const customer = invoice.customer || {};
                            
                            // Update shipping info
                            $('#shipping_address').val(customer.address || '');
                            $('#receiver_name').val(customer.name || '');
                            $('#receiver_phone').val(customer.phone || '');
                            
                            // Clear existing items
                            $('#items-table tbody').empty();
                            
                            if (items.length > 0) {
                                // Add new items with modern styling
                                $.each(items, function(index, item) {
                                    let boxPcs = item.category ? item.category.box_pcs : 0;
                                    let piecesFeet = item.category ? item.category.pieces_feet : 0;
                                    let description = item.description || item.code || item.product_name || ''; // Handle null/undefined descriptions
                                    let productName = item.product_name || item.description || item.code || 'Product';
                                    @if($godowns->isNotEmpty())
                                        let godownOptions = '<option value="">Select Godown</option>';
                                        if (item.godowns && item.godowns.length > 0) {
                                            item.godowns.forEach(function(godown) {
                                                const selected = item.recommended_godown_id && String(godown.id) === String(item.recommended_godown_id) ? 'selected' : '';
                                                const location = godown.location ? ' - ' + godown.location : '';
                                                const stock = godown.stock !== null ? parseFloat(godown.stock).toFixed(2) : '0.00';
                                                godownOptions += `<option value="${godown.id}" ${selected}>${godown.name}${location} (${stock})</option>`;
                                            });
                                        } else {
                                            godownOptions += '<option value="" disabled>No stock available</option>';
                                        }
                                        const godownCell = `
                                            <td>
                                                <select name="godown_id[]" class="form-control modern-select-sm godown-select">
                                                    ${godownOptions}
                                                </select>
                                            </td>
                                        `;
                                    @else
                                        const godownCell = '';
                                    @endif
                                    
                                    const row = `
                                        <tr class="item-row">
                                            <td>
                                                <div class="modern-checkbox">
                                                    <input type="checkbox" name="item_selected[]" id="item${index}" value="1" checked class="item-checkbox">
                                                    <label for="item${index}"></label>
                                                </div>
                                            </td>
                                            <td>
                                                <input type="hidden" name="invoice_item_id[]" value="${item.id}">
                                                <input type="hidden" name="product_id[]" value="${item.product_id}">
                                                <strong>${productName}</strong>
                                            </td>
                                            <td>
                                                <input type="text" name="description[]" class="form-control modern-input-sm" value="${description}" required>
                                            </td>
                                            <td>
                                                <span class="availability-badge">${item.remaining_quantity}</span>
                                                <input type="hidden" class="remaining-quantity" value="${item.remaining_quantity}">
                                                <input type="hidden" class="box-pcs" value="${boxPcs}">
                                                <input type="hidden" class="pieces-feet" value="${piecesFeet}">
                                            </td>
                                            ${godownCell}
                                            <td>
                                                <input type="number" name="quantity[]" class="form-control modern-input-sm quantity-field" min="0.01" max="${item.remaining_quantity}" step="0.01" value="${item.remaining_quantity}" required>
                                            </td>
                                            <td>
                                                <select class="form-control modern-select-sm quantity-type">
                                                    <option value="quantity">Quantity</option>
                                                    <option value="box_pieces">Box/Pieces</option>
                                                </select>
                                            </td>
                                            <td>
                                                <input type="number" name="boxes[]" class="form-control modern-input-sm box-field" min="0" readonly>
                                            </td>
                                            <td>
                                                <input type="number" name="pieces[]" class="form-control modern-input-sm pieces-field" min="0" readonly>
                                            </td>
                                        </tr>
                                    `;
                                    
                                    $('#items-table tbody').append(row);
                                });
                                
                                // Show items table
                                $('#no-items-alert').hide();
                                $('#invoice-items-container').removeClass('d-none');
                                
                                // Recalculate boxes and pieces for each row
                                $('.item-row').each(function() {
                                    calculateBoxesAndPieces($(this));
                                });
                                
                                // Initialize box/pieces for each row
                                initializeQuantityTypes();

                                // Calculate totals after items are loaded
                                calculateTotals();

                                // Auto-focus on first description field after items are loaded
                                setTimeout(function() {
                                    $('#items-table tbody tr:first-child .modern-input-sm[name="description[]"]').focus();
                                }, 100);
                            } else {
                                $('#no-items-alert .alert-message').html('<i class="fas fa-info-circle"></i> No items available for delivery in this invoice.');
                                $('#no-items-alert').removeClass('d-none').show();
                                $('#invoice-items-container').addClass('d-none');
                            }
                        },
                        error: function() {
                            $('#no-items-alert .alert-message').html('<i class="fas fa-exclamation-triangle"></i> Error loading invoice items.');
                            $('#no-items-alert').removeClass('d-none').show();
                            $('#invoice-items-container').addClass('d-none');
                        }
                    });
                } else {
                    // Clear and hide invoice items
                    $('#no-items-alert .alert-message').html('<i class="fas fa-info-circle"></i> Select an invoice to view available items for delivery.');
                    $('#no-items-alert').removeClass('d-none').show();
                    $('#invoice-items-container').addClass('d-none');
                    $('.customer-info').hide();
                    
                    // Clear shipping info
                    $('#shipping_address').val('');
                    $('#receiver_name').val('');
                    $('#receiver_phone').val('');
                }
            }

            // When invoice selection changes
            $('#invoice_id').on('change', function() {
                loadInvoiceItems($(this).val());
            });

            // Auto-load when visiting with invoice_id
            const presetInvoiceId = @json($invoice_id);
            if (presetInvoiceId) {
                $('#invoice_id').val(String(presetInvoiceId)).trigger('change.select2');
                loadInvoiceItems(String(presetInvoiceId));
            }
            
            // Initialize quantity type handling
            initializeQuantityTypes();
            
            // Item checkbox change
            $(document).on('change', '.item-checkbox', function() {
                const row = $(this).closest('tr');
                const isChecked = $(this).prop('checked');
                
                if (isChecked) {
                    // Enable fields and remove disabled styling
                    row.find('input, select').not('.item-checkbox').prop('disabled', false);
                    row.removeClass('disabled-row');
                } else {
                    // Disable fields and add disabled styling
                    row.find('input, select').not('.item-checkbox').prop('disabled', true);
                    row.addClass('disabled-row');
                }

                // Recalculate totals when checkbox changes
                calculateTotals();
            });
            
            // Enhanced form submission validation
            $('#challan-form').submit(function(e) {
                let valid = false;
                
                // Check if at least one item is selected
                $('.item-checkbox').each(function() {
                    if ($(this).prop('checked')) {
                        valid = true;
                        return false; // Break the each loop
                    }
                });
                
                if (!valid) {
                    e.preventDefault();
                    showErrorAlert('Please select at least one item for delivery.');
                    return false;
                }
                
                // Check if quantities are valid
                let quantityError = false;
                $('.item-row').each(function() {
                    if (!$(this).find('.item-checkbox').prop('checked')) {
                        return true; // Skip unchecked rows
                    }
                    
                    const quantityField = $(this).find('.quantity-field');
                    const quantity = parseFloat(quantityField.val());
                    const maxQuantity = parseFloat($(this).find('.remaining-quantity').val());
                    
                    if (isNaN(quantity) || quantity <= 0 || quantity > maxQuantity) {
                        quantityError = true;
                        quantityField.addClass('is-invalid');
                        return false; // Break the each loop
                    } else {
                        quantityField.removeClass('is-invalid');
                    }
                });
                
                if (quantityError) {
                    e.preventDefault();
                    showErrorAlert('Please enter valid quantities for all selected items.');
                    return false;
                }
                
                // Show loading state
                const submitBtn = $('#submit-btn');
                const originalText = submitBtn.html();
                submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Creating Challan...');
                
                return true;
            });
            
            // Calculate boxes and pieces on quantity change
            $(document).on('input', '.quantity-field', function() {
                $(this).removeClass('is-invalid');
                calculateBoxesAndPieces($(this).closest('tr'));
                calculateTotals();
            });
            
            // Calculate quantity based on boxes and pieces
            $(document).on('input', '.box-field, .pieces-field', function() {
                calculateQuantityFromBoxPieces($(this).closest('tr'));
                calculateTotals();
            });
            
            // Calculate initial values
            $('.item-row').each(function() {
                calculateBoxesAndPieces($(this));
            });

            // Calculate initial totals
            calculateTotals();
        });
        
        // Initialize quantity type handling
        function initializeQuantityTypes() {
            $(document).on('change', '.quantity-type', function() {
                const row = $(this).closest('tr');
                const value = $(this).val();
                
                if (value === 'quantity') {
                    // Quantity mode
                    row.find('.box-field, .pieces-field').prop('readonly', true).addClass('readonly-field');
                    row.find('.quantity-field').prop('readonly', false).removeClass('readonly-field');

                    // Recalculate boxes and pieces
                    calculateBoxesAndPieces(row);
                } else {
                    // Box/Pieces mode
                    row.find('.box-field, .pieces-field').prop('readonly', false).removeClass('readonly-field');
                    row.find('.quantity-field').prop('readonly', true).addClass('readonly-field');

                    // Initialize box/pieces fields if empty
                    if (!row.find('.box-field').val() && !row.find('.pieces-field').val()) {
                        calculateBoxesAndPieces(row);
                    }
                }

                // Recalculate totals
                calculateTotals();
            });
            
            // Initialize existing rows
            $('.quantity-type').trigger('change');
        }
        
        // Calculate boxes and pieces based on quantity (same logic as invoice)
        function calculateBoxesAndPieces(row) {
            const quantity = parseFloat(row.find('.quantity-field').val()) || 0;
            const boxPcs = parseFloat(row.find('.box-pcs').val()) || 0;
            const piecesFeet = parseFloat(row.find('.pieces-feet').val()) || 0;

            // Convert quantity -> whole pieces first, then split into box + remainder.
            if (piecesFeet > 0 && quantity > 0) {
                const totalPieces = Math.max(0, Math.round(quantity / piecesFeet));

                // Calculate boxes and loose pieces
                if (boxPcs > 0) {
                    const boxes = Math.floor(totalPieces / boxPcs);
                    const loosePieces = totalPieces % boxPcs;
                    row.find('.box-field').val(boxes);
                    row.find('.pieces-field').val(loosePieces);
                } else {
                    // No box_pcs, show total pieces only
                    row.find('.box-field').val(0);
                    row.find('.pieces-field').val(totalPieces);
                }
            } else if (boxPcs > 0 && quantity > 0) {
                // Fallback: only boxPcs without piecesFeet - use old calculation
                const totalPieces = Math.round(quantity);
                const boxes = Math.floor(totalPieces / boxPcs);
                const pieces = totalPieces - (boxes * boxPcs);
                row.find('.box-field').val(boxes);
                row.find('.pieces-field').val(pieces);
            } else {
                row.find('.box-field').val('');
                row.find('.pieces-field').val('');
            }
        }
        
        // Calculate quantity based on boxes and pieces
        function calculateQuantityFromBoxPieces(row) {
            let boxes = parseInt(row.find('.box-field').val()) || 0;
            let pieces = parseInt(row.find('.pieces-field').val()) || 0;
            const boxPcs = parseFloat(row.find('.box-pcs').val()) || 0;
            const piecesFeet = parseFloat(row.find('.pieces-feet').val()) || 0;

            if (boxPcs > 0 && pieces >= boxPcs) {
                boxes += Math.floor(pieces / boxPcs);
                pieces = pieces % boxPcs;
                row.find('.box-field').val(boxes);
                row.find('.pieces-field').val(pieces);
            }
            
            if (boxPcs > 0 && piecesFeet > 0) {
                // Calculate total pieces
                const totalPieces = (boxes * boxPcs) + pieces;
                
                // Calculate quantity
                const quantity = (totalPieces * piecesFeet).toFixed(2);
                
                // Check if quantity exceeds maximum
                const maxQuantity = parseFloat(row.find('.remaining-quantity').val());
                if (quantity > maxQuantity) {
                    showErrorAlert('Quantity exceeds available amount. Adjusting to maximum.');
                    calculateBoxesAndPieces(row);
                } else {
                    row.find('.quantity-field').val(quantity);
                }
            }
        }
        
        // Calculate and update totals
        function calculateTotals() {
            let totalQuantity = 0;
            let totalBoxes = 0;
            let totalPieces = 0;

            $('.item-row').each(function() {
                // Only include checked items
                if ($(this).find('.item-checkbox').prop('checked')) {
                    const quantity = parseFloat($(this).find('.quantity-field').val()) || 0;
                    const boxes = parseInt($(this).find('.box-field').val()) || 0;
                    const pieces = parseInt($(this).find('.pieces-field').val()) || 0;

                    totalQuantity += quantity;
                    totalBoxes += boxes;
                    totalPieces += pieces;
                }
            });

            $('#total-quantity').val(totalQuantity.toFixed(2));
            $('#total-boxes').val(totalBoxes);
            $('#total-pieces').val(totalPieces);
        }

        // Show error alert function
        function showErrorAlert(message) {
            const alertHtml = `
                <div class="alert modern-alert modern-alert-error alert-auto-hide" style="animation-delay: 0s;">
                    <div class="alert-content">
                        <i class="fas fa-exclamation-triangle alert-icon"></i>
                        <div class="alert-message">
                            <strong>Validation Error!</strong>
                            <span>${message}</span>
                        </div>
                        <button type="button" class="alert-close" onclick="this.parentElement.parentElement.style.display='none'">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            `;
            
            // Remove existing error alerts
            $('.modern-alert-error').remove();
            
            // Add new alert at the top
            $('form').prepend(alertHtml);
            
            // Auto-hide after 5 seconds
            setTimeout(function() {
                $('.alert-auto-hide').addClass('alert-auto-hide');
            }, 5000);
            
            // Scroll to top
            $('html, body').animate({
                scrollTop: 0
            }, 300);
        }
    </script>
@stop
