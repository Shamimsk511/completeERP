@extends('layouts.modern-admin')

@section('title', 'Purchases')

@section('page_title', 'Purchase Management')

@section('header_actions')
    <div class="header-actions-group">
        <a href="{{ route('purchases.create') }}" class="btn modern-btn modern-btn-primary" 
           onclick="handleMobileAction(event, 'create', null)">
            <i class="fas fa-plus"></i> <span class="btn-text">New Purchase</span>
        </a>
    </div>
@stop

@section('page_content')
    <!-- Statistics Cards Row -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6">
            <div class="card modern-card stat-card total-stat">
                <div class="card-body modern-card-body">
                    <div class="stat-content">
                        <div class="stat-icon">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                        <div class="stat-info">
                            <h3 class="stat-number" id="total-purchases">{{ $purchases->count() }}</h3>
                            <p class="stat-label">Total Purchases</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="card modern-card stat-card value-stat">
                <div class="card-body modern-card-body">
                    <div class="stat-content">
                        <div class="stat-icon">
                            <i class="fas fa-dollar-sign"></i>
                        </div>
                        <div class="stat-info">
                            <h3 class="stat-number" id="total-value">৳{{ number_format($purchases->sum('total_amount'), 2) }}</h3>
                            <p class="stat-label">Total Value</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="card modern-card stat-card supplier-stat">
                <div class="card-body modern-card-body">
                    <div class="stat-content">
                        <div class="stat-icon">
                            <i class="fas fa-building"></i>
                        </div>
                        <div class="stat-info">
                            <h3 class="stat-number" id="supplier-count">{{ $purchases->unique('company_id')->count() }}</h3>
                            <p class="stat-label">Suppliers</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="card modern-card stat-card month-stat">
                <div class="card-body modern-card-body">
                    <div class="stat-content">
                        <div class="stat-icon">
                            <i class="fas fa-calendar-alt"></i>
                        </div>
                        <div class="stat-info">
                            <h3 class="stat-number" id="this-month">{{ $purchases->where('purchase_date', '>=', now()->startOfMonth())->count() }}</h3>
                            <p class="stat-label">This Month</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Card - Collapsible on Mobile -->
    <div class="card modern-card mb-4">
        <div class="card-header modern-header filter-header collapsible-header" data-toggle="collapse" data-target="#filter-content">
            <div class="header-content">
                <div class="header-title">
                    <i class="fas fa-filter header-icon"></i>
                    <h3 class="card-title">Filter Options</h3>
                </div>
                <i class="fas fa-chevron-down collapse-icon"></i>
            </div>
        </div>
        <div id="filter-content" class="collapse show">
            <div class="card-body modern-card-body">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group modern-form-group">
                            <label for="supplier_filter" class="form-label">Supplier</label>
                            <select class="form-control modern-select select2-searchable" id="supplier_filter">
                                <option value="">All Suppliers</option>
                                @foreach($purchases->unique('company_id')->pluck('company') as $company)
                                    <option value="{{ $company->id }}">{{ $company->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group modern-form-group">
                            <label for="date_from" class="form-label">Date From</label>
                            <input type="date" class="form-control modern-input" id="date_from">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group modern-form-group">
                            <label for="date_to" class="form-label">Date To</label>
                            <input type="date" class="form-control modern-input" id="date_to">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group modern-form-group">
                            <label>&nbsp;</label>
                            <button type="button" id="filter_button" class="btn modern-btn modern-btn-primary form-control btn-block-mobile">
                                <i class="fas fa-search"></i> <span class="btn-text">Apply Filter</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

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

    <!-- Purchases Section - Mobile Optimized -->
    <div class="card modern-card">
        <div class="card-header modern-header purchases-header">
            <div class="header-content">
                <div class="header-title">
                    <i class="fas fa-shopping-cart header-icon"></i>
                    <h3 class="card-title">All Purchases</h3>
                </div>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" onclick="refreshTable()">
                        <i class="fas fa-sync-alt"></i>
                    </button>
                </div>
            </div>
        </div>
        <div class="card-body modern-card-body">
            <!-- Mobile View Purchase Cards -->
            <div class="mobile-purchases-container" id="mobile-purchases">
                @foreach($purchases as $purchase)
                    <div class="mobile-purchase-card" data-purchase-id="{{ $purchase->id }}">
                        <div class="card-header-mobile">
                            <div class="purchase-id">
                                <span class="id-badge-mobile">#{{ $purchase->id }}</span>
                                @if($purchase->invoice_no)
                                    <span class="invoice-badge-mobile">{{ $purchase->invoice_no }}</span>
                                @else
                                    <span class="no-invoice-badge-mobile">No Invoice</span>
                                @endif
                            </div>
                            <div class="purchase-date">
                                <span class="date-text-mobile">{{ $purchase->purchase_date->format('d-m-Y') }}</span>
                                <small class="date-relative">{{ $purchase->purchase_date->diffForHumans() }}</small>
                            </div>
                        </div>
                        
                        <div class="card-content-mobile">
                            <div class="supplier-info-mobile">
                                <div class="supplier-name">
                                    <i class="fas fa-building"></i>
                                    <strong>{{ $purchase->company->name }}</strong>
                                </div>
                                @if($purchase->company->phone)
                                    <div class="supplier-phone">
                                        <i class="fas fa-phone"></i>
                                        <span>{{ $purchase->company->phone }}</span>
                                    </div>
                                @endif
                            </div>
                            
                            <div class="amount-info-mobile">
                                <div class="amount-label">Total Amount</div>
                                <div class="amount-value">৳{{ number_format($purchase->total_amount, 2) }}</div>
                            </div>
                            
                            @if($purchase->notes)
                                <div class="notes-info-mobile">
                                    <i class="fas fa-sticky-note"></i>
                                    <span>{{ Str::limit($purchase->notes, 50) }}</span>
                                </div>
                            @endif
                        </div>
                        
                        <div class="card-actions-mobile">
                            <a href="{{ route('purchases.show', $purchase) }}" class="action-btn-mobile action-btn-view-mobile" title="View" 
                               onclick="handleMobileAction(event, 'view', {{ $purchase->id }})">
                                <i class="fas fa-eye"></i>
                                <span>View</span>
                            </a>
                            <a href="{{ route('purchases.edit', $purchase) }}" class="action-btn-mobile action-btn-edit-mobile" title="Edit"
                               onclick="handleMobileAction(event, 'edit', {{ $purchase->id }})">
                                <i class="fas fa-edit"></i>
                                <span>Edit</span>
                            </a>
                            <button type="button" class="action-btn-mobile action-btn-delete-mobile" 
                                    onclick="handleMobileAction(event, 'delete', {{ $purchase->id }})" title="Delete">
                                <i class="fas fa-trash"></i>
                                <span>Delete</span>
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
            
            <!-- Desktop View Table -->
            <div class="desktop-purchases-container">
                <div class="table-container">
                    <div class="table-responsive modern-table-responsive">
                        <table class="table modern-table" id="purchases-table">
                            <thead class="modern-thead">
                                <tr>
                                    <th width="8%">
                                        <div class="th-content">
                                            <i class="fas fa-hashtag"></i>
                                            <span>ID</span>
                                        </div>
                                    </th>
                                    <th width="12%">
                                        <div class="th-content">
                                            <i class="fas fa-calendar"></i>
                                            <span>Date</span>
                                        </div>
                                    </th>
                                    <th width="15%">
                                        <div class="th-content">
                                            <i class="fas fa-file-invoice"></i>
                                            <span>Invoice No</span>
                                        </div>
                                    </th>
                                    <th width="25%">
                                        <div class="th-content">
                                            <i class="fas fa-building"></i>
                                            <span>Supplier</span>
                                        </div>
                                    </th>
                                    <th width="15%">
                                        <div class="th-content">
                                            <i class="fas fa-dollar-sign"></i>
                                            <span>Total Amount</span>
                                        </div>
                                    </th>
                                    <th width="25%">
                                        <div class="th-content">
                                            <i class="fas fa-cogs"></i>
                                            <span>Actions</span>
                                        </div>
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="modern-tbody">
                                @foreach($purchases as $purchase)
                                    <tr>
                                        <td>
                                            <span class="id-badge">{{ $purchase->id }}</span>
                                        </td>
                                        <td>
                                            <span class="date-text">{{ $purchase->purchase_date->format('d-m-Y') }}</span>
                                            <small class="text-muted d-block">{{ $purchase->purchase_date->diffForHumans() }}</small>
                                        </td>
                                        <td>
                                            @if($purchase->invoice_no)
                                                <span class="invoice-badge">{{ $purchase->invoice_no }}</span>
                                            @else
                                                <span class="no-invoice-badge">N/A</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="supplier-info">
                                                <strong>{{ $purchase->company->name }}</strong>
                                                @if($purchase->company->phone)
                                                    <small class="text-muted d-block">
                                                        <i class="fas fa-phone"></i> {{ $purchase->company->phone }}
                                                    </small>
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            <span class="amount-badge">৳{{ number_format($purchase->total_amount, 2) }}</span>
                                        </td>
                                        <td>
                                            <div class="action-buttons">
                                                <a href="{{ route('purchases.show', $purchase) }}" class="action-btn action-btn-view" title="View">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('purchases.edit', $purchase) }}" class="action-btn action-btn-edit" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <button type="button" class="action-btn action-btn-delete" 
                                                        onclick="deletePurchase({{ $purchase->id }})" title="Delete">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade modern-modal" id="deletePurchaseModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-dialog-centered modal-dialog-mobile" role="document">
            <div class="modal-content modern-modal-content">
                <div class="modal-header modern-modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-exclamation-triangle"></i> Confirm Deletion
                    </h5>
                    <button type="button" class="close modern-close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body modern-modal-body">
                    <div class="confirmation-content">
                        <div class="confirmation-icon">
                            <i class="fas fa-question-circle"></i>
                        </div>
                        <div class="confirmation-text">
                            <p>Are you sure you want to delete this purchase?</p>
                            <small class="text-muted">This action will update product stock levels and cannot be undone.</small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer modern-modal-footer">
                    <button type="button" class="btn modern-btn modern-btn-outline" data-dismiss="modal">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                    <button type="button" id="confirmDeleteBtn" class="btn modern-btn modern-btn-danger">
                        <i class="fas fa-trash"></i> Delete Purchase
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Hidden delete form -->
    <form id="delete-form" method="POST" style="display: none;">
        @csrf
        @method('DELETE')
    </form>
@stop

@section('additional_css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap4.min.css">
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

        .modern-form-group {
            margin-bottom: 20px;
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

        /* Enhanced Select2 styling */
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

        /* Section-specific header colors */
        .filter-header {
            background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
        }

        .purchases-header {
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
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

        /* Statistics Cards */
        .stat-card {
            border: none;
            border-radius: 16px;
            overflow: hidden;
            transition: all 0.3s ease;
            background: white;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
        }

        .stat-content {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            color: white;
        }

        .total-stat .stat-icon {
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
        }

        .value-stat .stat-icon {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        }

        .supplier-stat .stat-icon {
            background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);
        }

        .month-stat .stat-icon {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        }

        .stat-info {
            flex: 1;
        }

        .stat-number {
            font-size: 32px;
            font-weight: 700;
            color: #1f2937;
            margin: 0;
            line-height: 1;
        }

        .stat-label {
            font-size: 14px;
            color: #6b7280;
            margin: 4px 0 0 0;
            font-weight: 500;
        }

        /* Desktop specific styles */
        @media (min-width: 769px) {
            /* Hide mobile elements on desktop */
            .mobile-purchases-container {
                display: none !important;
            }

            /* Show desktop elements */
            .desktop-purchases-container {
                display: block !important;
            }

            .btn-block-mobile {
                width: auto;
            }
        }

        /* Mobile Responsive */
        @media (max-width: 768px) {
            /* Hide desktop elements on mobile */
            .desktop-purchases-container {
                display: none !important;
            }

            /* Show mobile elements */
            .mobile-purchases-container {
                display: block !important;
            }

            /* Mobile button styles */
            .btn-block-mobile {
                width: 100%;
            }

            .btn-text {
                display: none;
            }

            /* Statistics cards mobile */
            .stat-content {
                flex-direction: column;
                text-align: center;
                gap: 12px;
            }

            .stat-icon {
                width: 50px;
                height: 50px;
                font-size: 20px;
            }

            .stat-number {
                font-size: 24px;
            }

            /* Collapse filter by default on mobile */
            #filter-content {
                display: none;
            }

            #filter-content.show {
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

            .select2-results__option {
                padding: 14px 15px !important;
                font-size: 16px !important;
            }
        }

        /* Mobile Purchase Cards */
        .mobile-purchase-card {
            background: #f8fafc;
            border-radius: 12px;
            padding: 16px;
            margin-bottom: 16px;
            border: 2px solid #e5e7eb;
            transition: all 0.3s ease;
        }

        .mobile-purchase-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(99, 102, 241, 0.15);
            border-color: #6366f1;
        }

        .card-header-mobile {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 16px;
            flex-wrap: wrap;
            gap: 8px;
        }

        .purchase-id {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .purchase-date {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            text-align: right;
        }

        .id-badge-mobile {
            background: linear-gradient(135deg, #6b7280 0%, #4b5563 100%);
            color: white;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
        }

        .invoice-badge-mobile {
            background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);
            color: white;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
        }

        .no-invoice-badge-mobile {
            background: linear-gradient(135deg, #9ca3af 0%, #6b7280 100%);
            color: white;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
        }

        .date-text-mobile {
            font-weight: 600;
            color: #374151;
            font-size: 14px;
        }

        .date-relative {
            color: #6b7280;
            font-size: 12px;
        }

        .card-content-mobile {
            margin-bottom: 16px;
        }

        .supplier-info-mobile {
            margin-bottom: 12px;
        }

        .supplier-name {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 4px;
        }

        .supplier-name i {
            color: #6366f1;
        }

        .supplier-name strong {
            color: #374151;
            font-size: 15px;
        }

        .supplier-phone {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-left: 24px;
        }

        .supplier-phone i {
            color: #6b7280;
            font-size: 12px;
        }

        .supplier-phone span {
            color: #6b7280;
            font-size: 13px;
        }

        .amount-info-mobile {
            background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
            border: 2px solid #10b981;
            border-radius: 8px;
            padding: 12px;
            margin-bottom: 12px;
        }

        .amount-label {
            color: #6b7280;
            font-size: 12px;
            font-weight: 500;
            margin-bottom: 4px;
        }

        .amount-value {
            color: #059669;
            font-size: 18px;
            font-weight: 700;
        }

        .notes-info-mobile {
            display: flex;
            align-items: center;
            gap: 8px;
            background: #f1f5f9;
            padding: 8px 12px;
            border-radius: 6px;
            margin-bottom: 12px;
        }

        .notes-info-mobile i {
            color: #6366f1;
            font-size: 12px;
        }

        .notes-info-mobile span {
            color: #4b5563;
            font-size: 13px;
        }

        .card-actions-mobile {
            display: flex;
            gap: 8px;
            justify-content: space-between;
        }

        .action-btn-mobile {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            padding: 10px 12px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.2s ease;
            border: none;
            cursor: pointer;
        }

        .action-btn-mobile:hover {
            transform: translateY(-1px);
            text-decoration: none;
        }

        .action-btn-view-mobile {
            background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);
            color: white;
        }

        .action-btn-view-mobile:hover {
            color: white;
            box-shadow: 0 4px 12px rgba(6, 182, 212, 0.3);
        }

        .action-btn-edit-mobile {
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
            color: white;
        }

        .action-btn-edit-mobile:hover {
            color: white;
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
        }

        .action-btn-delete-mobile {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: white;
        }

        .action-btn-delete-mobile:hover {
            color: white;
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
        }

        /* Desktop Table Styles */
        .table-container {
            position: relative;
            overflow: hidden;
            border-radius: 12px;
            background: white;
        }

        .modern-table-responsive {
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1);
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
            padding: 18px 14px;
            font-weight: 600;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: white !important;
            background: transparent !important;
        }

        .th-content {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            color: white;
            text-align: center;
        }

        .modern-tbody {
            background: white !important;
        }

        .modern-tbody tr {
            transition: all 0.3s ease;
            border-bottom: 1px solid #f1f5f9;
            background: white !important;
        }

        .modern-tbody tr:hover {
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%) !important;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(99, 102, 241, 0.15);
        }

        .modern-tbody td {
            padding: 14px 12px;
            vertical-align: middle;
            border: none !important;
            font-size: 14px;
            color: #374151 !important;
            background: transparent !important;
        }

        /* Desktop Badges */
        .id-badge {
            display: inline-block;
            background: linear-gradient(135deg, #6b7280 0%, #4b5563 100%);
            color: white;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
        }

        .invoice-badge {
            display: inline-block;
            background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);
            color: white;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
        }

        .no-invoice-badge {
            display: inline-block;
            background: linear-gradient(135deg, #9ca3af 0%, #6b7280 100%);
            color: white;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
        }

        .amount-badge {
            display: inline-block;
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            padding: 6px 12px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: 700;
        }

        .date-text {
            font-weight: 600;
            color: #374151;
        }

        .supplier-info strong {
            color: #374151;
            font-size: 14px;
        }

        /* Desktop Action Buttons */
        .action-buttons {
            display: flex;
            gap: 8px;
            justify-content: center;
            align-items: center;
        }

        .action-btn {
            padding: 8px 12px;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 500;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            min-width: 44px;
            justify-content: center;
        }

        .action-btn:hover {
            transform: translateY(-2px);
            text-decoration: none;
        }

        .action-btn-view {
            background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(6, 182, 212, 0.3);
        }

        .action-btn-view:hover {
            background: linear-gradient(135deg, #0891b2 0%, #0e7490 100%);
            color: white;
            box-shadow: 0 6px 20px rgba(6, 182, 212, 0.4);
        }

        .action-btn-edit {
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(99, 102, 241, 0.3);
        }

        .action-btn-edit:hover {
            background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
            color: white;
            box-shadow: 0 6px 20px rgba(99, 102, 241, 0.4);
        }

        .action-btn-delete {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(239, 68, 68, 0.3);
        }

        .action-btn-delete:hover {
            background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
            color: white;
            box-shadow: 0 6px 20px rgba(239, 68, 68, 0.4);
        }

        /* Inherit all other styles from delivery design */
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

        .modern-btn-danger {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: white;
            border-color: #ef4444;
        }

        .modern-btn-danger:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(239, 68, 68, 0.4);
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

        /* Card tools */
        .card-tools {
            display: flex;
            gap: 8px;
        }

        .card-tools .btn-tool {
            color: white;
            opacity: 0.8;
            border: none;
            background: transparent;
            padding: 8px 12px;
            border-radius: 8px;
            transition: all 0.2s ease;
            cursor: pointer;
        }

        .card-tools .btn-tool:hover {
            opacity: 1;
            background: rgba(255, 255, 255, 0.1);
            transform: scale(1.1);
        }

        /* Modal styling */
        .modern-modal {
            border-radius: 16px;
            border: none;
            overflow: hidden;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
        }

        .modern-modal-header {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
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

        .modern-modal-body {
            padding: 24px;
        }

        .modern-modal-footer {
            padding: 20px 24px;
            border-top: 1px solid #f1f5f9;
            background: #f8fafc;
        }

        .confirmation-content {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .confirmation-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            color: white;
            flex-shrink: 0;
        }

        .confirmation-text {
            flex: 1;
        }

        .confirmation-text p {
            margin: 0 0 8px 0;
            font-size: 16px;
            color: #374151;
        }

        /* DataTables Enhancements */
        .dataTables_wrapper .dataTables_length,
        .dataTables_wrapper .dataTables_filter,
        .dataTables_wrapper .dataTables_info,
        .dataTables_wrapper .dataTables_paginate {
            color: #374151;
            margin-bottom: 16px;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button {
            border-radius: 8px;
            margin: 0 2px;
            padding: 8px 12px;
            border: 1px solid #e5e7eb;
            background: white;
            color: #374151;
            transition: all 0.2s ease;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
            background: #6366f1;
            color: white;
            border-color: #6366f1;
            transform: translateY(-1px);
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button.current {
            background: #6366f1;
            color: white;
            border-color: #6366f1;
        }

        /* Toastr Customization */
        .toast-success {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        }

        .toast-error {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        }

        .toast-warning {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        }

        .toast-info {
            background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);
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

        /* Animation for cards */
        .mobile-purchase-card {
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

        /* Mobile-specific improvements */
        @media (max-width: 480px) {
            .mobile-purchase-card {
                padding: 12px;
                margin-bottom: 12px;
            }

            .card-header-mobile {
                flex-direction: column;
                align-items: flex-start;
                gap: 8px;
            }

            .purchase-date {
                align-items: flex-start;
                text-align: left;
            }

            .card-actions-mobile {
                flex-direction: column;
                gap: 8px;
            }

            .action-btn-mobile {
                width: 100%;
            }

            .stat-icon {
                width: 40px;
                height: 40px;
                font-size: 18px;
            }

            .stat-number {
                font-size: 20px;
            }

            .confirmation-content {
                flex-direction: column;
                text-align: center;
                gap: 16px;
            }

            .confirmation-icon {
                width: 50px;
                height: 50px;
                font-size: 20px;
            }
        }

        /* Loading states */
        .loading {
            opacity: 0.7;
            pointer-events: none;
            position: relative;
        }

        .loading::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 20px;
            height: 20px;
            margin: -10px 0 0 -10px;
            border: 2px solid #6366f1;
            border-radius: 50%;
            border-top-color: transparent;
            animation: spin 1s linear infinite;
            z-index: 10;
        }

        .action-btn-mobile.loading::after {
            width: 16px;
            height: 16px;
            margin: -8px 0 0 -8px;
            border-width: 2px;
        }

        .mobile-purchases-container.loading {
            min-height: 200px;
        }

        .mobile-purchases-container.loading::after {
            width: 40px;
            height: 40px;
            margin: -20px 0 0 -20px;
            border-width: 4px;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        /* Enhanced mobile button interactions */
        .action-btn-mobile:active {
            transform: scale(0.95);
        }

        .modern-btn:active {
            transform: scale(0.98);
        }

        /* Better touch feedback */
        .touching {
            background: rgba(99, 102, 241, 0.1) !important;
            transform: scale(0.98);
        }

        /* Mobile-specific button enhancements */
        @media (max-width: 768px) {
            .action-btn-mobile {
                min-height: 44px; /* Better touch target */
                font-size: 14px;
            }

            .modern-btn {
                min-height: 48px; /* Better touch target */
                font-size: 16px;
                padding: 12px 20px;
            }

            /* Ensure buttons are easily tappable */
            .action-btn-mobile,
            .modern-btn {
                -webkit-tap-highlight-color: rgba(99, 102, 241, 0.2);
                tap-highlight-color: rgba(99, 102, 241, 0.2);
            }
        }
    </style>
@stop

@section('additional_js')
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap4.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"></script>
    
    <script>
        $(document).ready(function() {
            // Configure Toastr
            toastr.options = {
                "closeButton": true,
                "newestOnTop": true,
                "progressBar": true,
                "positionClass": "toast-top-right",
                "timeOut": "3000"
            };
            
            // Better mobile detection
            function isMobileView() {
                return window.innerWidth <= 768;
            }
            
            // Initialize Select2 with search
            initializeSelect2();
            
            // Auto-hide alerts after 5 seconds
            setTimeout(function() {
                $('.modern-alert').fadeOut();
            }, 5000);
            
            // Initialize DataTable with modern styling (desktop only)
            let purchasesTable;
            if (!isMobileView()) {
                purchasesTable = $('#purchases-table').DataTable({
                    "paging": true,
                    "lengthChange": true,
                    "searching": true,
                    "ordering": true,
                    "info": true,
                    "autoWidth": false,
                    "responsive": true,
                    "pageLength": 25,
                    "language": {
                        "processing": '<i class="fas fa-spinner fa-spin"></i> Loading...',
                        "emptyTable": 'No purchases found',
                        "zeroRecords": 'No matching records found'
                    },
                    "order": [[0, 'desc']]
                });
            }
            
            // Mobile-specific optimizations
            const PurchaseIndex = {
                init: function() {
                    this.initMobileOptimizations();
                    this.initFilterSystem();
                    this.initDeleteSystem();
                    
                    console.log('Purchase Index initialized with mobile optimizations');
                },

                // Mobile-specific optimizations
                initMobileOptimizations: function() {
                    // Mobile-friendly modal handling
                    this.initMobileModals();
                    
                    // Touch-friendly interactions
                    this.initTouchOptimizations();
                    
                    // Mobile search functionality
                    this.initMobileSearch();
                },

                // Mobile-friendly modal handling
                initMobileModals: function() {
                    $('.modal').on('show.bs.modal', function() {
                        $('body').addClass('modal-open-mobile');
                        // Scroll to top of modal
                        $(this).find('.modal-body').scrollTop(0);
                    }).on('hidden.bs.modal', function() {
                        $('body').removeClass('modal-open-mobile');
                    });
                    
                    // Close modal on outside tap (mobile-friendly)
                    $('.modal').on('click', function(e) {
                        if (e.target === this) {
                            $(this).modal('hide');
                        }
                    });
                },

                // Touch-friendly interactions
                initTouchOptimizations: function() {
                    // Add touch feedback to buttons
                    $('.action-btn-mobile, .modern-btn').on('touchstart', function() {
                        $(this).addClass('touching');
                    }).on('touchend touchcancel', function() {
                        const self = $(this);
                        setTimeout(() => {
                            self.removeClass('touching');
                        }, 150);
                    });
                    
                    // Enhanced mobile button handling
                    $('.action-btn-mobile').on('click', function(e) {
                        // Prevent double clicks
                        if ($(this).hasClass('loading')) {
                            e.preventDefault();
                            return false;
                        }
                    });
                    
                    // Mobile-specific link handling
                    $('a.action-btn-mobile, a.modern-btn').on('click', function(e) {
                        const href = $(this).attr('href');
                        const onclick = $(this).attr('onclick');
                        
                        // If there's an onclick handler, let it handle the navigation
                        if (onclick) {
                            e.preventDefault();
                            return;
                        }
                        
                        // Otherwise, add loading state and navigate
                        if (href && href !== '#' && !$(this).hasClass('loading')) {
                            $(this).addClass('loading');
                            // Let the browser handle the navigation
                        }
                    });
                },

                // Mobile search functionality
                initMobileSearch: function() {
                    // Simple client-side search for mobile cards
                    let searchTimeout;
                    
                    // Create search input for mobile (you can add this to the template if needed)
                    if (isMobileView()) {
                        // You can implement mobile-specific search here
                    }
                },

                // Filter system
                initFilterSystem: function() {
                    $('#filter_button').click(function() {
                        const button = $(this);
                        const originalText = button.html();
                        button.html('<i class="fas fa-spinner fa-spin"></i> Filtering...').prop('disabled', true);
                        
                        // Get filter values
                        const supplierId = $('#supplier_filter').val();
                        const dateFrom = $('#date_from').val();
                        const dateTo = $('#date_to').val();
                        
                        if (isMobileView()) {
                            // Mobile filtering - hide/show cards
                            PurchaseIndex.filterMobileCards(supplierId, dateFrom, dateTo);
                        } else {
                            // Desktop filtering - use DataTable
                            if (purchasesTable) {
                                if (supplierId) {
                                    purchasesTable.column(3).search(supplierId);
                                } else {
                                    purchasesTable.column(3).search('');
                                }
                                purchasesTable.draw();
                            }
                        }
                        
                        setTimeout(() => {
                            button.html(originalText).prop('disabled', false);
                            toastr.info('Filters applied successfully');
                        }, 1000);
                    });
                    
                    // Clear filters on change
                    $('#supplier_filter, #date_from, #date_to').on('change', function() {
                        if (!$(this).val()) {
                            $('#filter_button').click();
                        }
                    });
                },

                // Filter mobile cards
                filterMobileCards: function(supplierId, dateFrom, dateTo) {
                    $('.mobile-purchase-card').each(function() {
                        let showCard = true;
                        
                        // Filter by supplier (you'll need to add data attributes to cards)
                        if (supplierId) {
                            const cardSupplierId = $(this).data('supplier-id');
                            if (cardSupplierId != supplierId) {
                                showCard = false;
                            }
                        }
                        
                        // Filter by date range (you'll need to add data attributes)
                        if (dateFrom || dateTo) {
                            const cardDate = $(this).data('purchase-date');
                            if (dateFrom && cardDate < dateFrom) {
                                showCard = false;
                            }
                            if (dateTo && cardDate > dateTo) {
                                showCard = false;
                            }
                        }
                        
                        if (showCard) {
                            $(this).show();
                        } else {
                            $(this).hide();
                        }
                    });
                },

                // Delete system
                initDeleteSystem: function() {
                    // Delete purchase function
                    window.deletePurchase = function(purchaseId) {
                        $('#deletePurchaseModal').data('purchase-id', purchaseId);
                        $('#deletePurchaseModal').modal('show');
                    };
                    
                    // Handle delete confirmation
                    $('#confirmDeleteBtn').click(function() {
                        const purchaseId = $('#deletePurchaseModal').data('purchase-id');
                        const button = $(this);
                        
                        // Show loading state
                        button.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Deleting...');
                        
                        // Set form action and submit
                        const form = $('#delete-form');
                        form.attr('action', '/purchases/' + purchaseId);
                        form.submit();
                    });
                    
                    // Reset modal when hidden
                    $('#deletePurchaseModal').on('hidden.bs.modal', function() {
                        $('#confirmDeleteBtn').prop('disabled', false).html('<i class="fas fa-trash"></i> Delete Purchase');
                    });
                }
            };

            // Initialize purchase index functionality
            PurchaseIndex.init();
            
            // Handle mobile actions
            window.handleMobileAction = function(event, action, purchaseId) {
                // Add visual feedback
                const button = $(event.currentTarget);
                button.addClass('loading');
                
                // Add small delay to show loading state
                setTimeout(() => {
                    switch(action) {
                        case 'create':
                            window.location.href = "{{ route('purchases.create') }}";
                            break;
                        case 'view':
                            window.location.href = `/purchases/${purchaseId}`;
                            break;
                        case 'edit':
                            window.location.href = `/purchases/${purchaseId}/edit`;
                            break;
                        case 'delete':
                            button.removeClass('loading');
                            deletePurchase(purchaseId);
                            break;
                        default:
                            button.removeClass('loading');
                            toastr.error('Unknown action');
                    }
                }, 200);
                
                // Prevent default link behavior for better control
                if (action !== 'delete') {
                    event.preventDefault();
                }
            };
            
            // Enhanced delete function for mobile
            window.deletePurchase = function(purchaseId) {
                $('#deletePurchaseModal').data('purchase-id', purchaseId);
                $('#deletePurchaseModal').modal('show');
            };
            
            // Refresh table function
            window.refreshTable = function() {
                const isMobile = isMobileView();
                
                if (isMobile) {
                    // Add loading state for mobile
                    $('.mobile-purchases-container').addClass('loading');
                    
                    // Simulate refresh (in real app, you'd make an AJAX call)
                    setTimeout(() => {
                        $('.mobile-purchases-container').removeClass('loading');
                        toastr.success('Purchases refreshed');
                        location.reload(); // For now, just reload the page
                    }, 1000);
                } else {
                    if (purchasesTable) {
                        purchasesTable.ajax.reload();
                    } else {
                        location.reload();
                    }
                    toastr.info('Table refreshed');
                }
            };
            
            // Handle window resize for responsive adjustments
            $(window).on('resize', function() {
                setTimeout(function() {
                    if (isMobileView()) {
                        $('.mobile-purchases-container').show();
                        $('.desktop-purchases-container').hide();
                        
                        // Destroy DataTable on mobile
                        if (purchasesTable) {
                            purchasesTable.destroy();
                            purchasesTable = null;
                        }
                    } else {
                        $('.mobile-purchases-container').hide();
                        $('.desktop-purchases-container').show();
                        
                        // Initialize DataTable on desktop
                        if (!purchasesTable) {
                            purchasesTable = $('#purchases-table').DataTable({
                                "paging": true,
                                "lengthChange": true,
                                "searching": true,
                                "ordering": true,
                                "info": true,
                                "autoWidth": false,
                                "responsive": true,
                                "pageLength": 25,
                                "language": {
                                    "processing": '<i class="fas fa-spinner fa-spin"></i> Loading...',
                                    "emptyTable": 'No purchases found',
                                    "zeroRecords": 'No matching records found'
                                },
                                "order": [[0, 'desc']]
                            });
                        }
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
        }
    </script>
@stop