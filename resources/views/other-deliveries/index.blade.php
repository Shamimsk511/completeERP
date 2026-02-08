@extends('layouts.modern-admin')

@section('title', 'Other Deliveries')

@section('page_title', 'Other Deliveries')

@section('header_actions')
    <div class="header-actions-group">
        <a href="{{ route('other-deliveries.create') }}" class="btn modern-btn modern-btn-primary">
            <i class="fas fa-plus"></i> <span class="btn-text">Create New</span>
        </a>
        
        <button type="button" class="btn modern-btn modern-btn-secondary" id="print-deliveries">
            <i class="fas fa-print"></i> <span class="btn-text">Print</span>
        </button>
        <button type="button" class="btn modern-btn modern-btn-info" id="export-deliveries">
            <i class="fas fa-file-excel"></i> <span class="btn-text">Export</span>
        </button>
    </div>
@stop

@section('page_content')
    <!-- Mobile-Optimized Dashboard Summary Cards -->
    <div class="summary-grid">
        <div class="summary-card pending-card">
            <div class="summary-content">
                <div class="summary-icon">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="summary-details">
                    <h3 class="summary-title">Pending</h3>
                    <h2 class="summary-value">{{ $deliveries->where('status', 'pending')->count() }}</h2>
                    <p class="summary-subtitle">Awaiting delivery</p>
                </div>
            </div>
        </div>
        
        <div class="summary-card delivered-card">
            <div class="summary-content">
                <div class="summary-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="summary-details">
                    <h3 class="summary-title">Delivered</h3>
                    <h2 class="summary-value">{{ $deliveries->where('status', 'delivered')->count() }}</h2>
                    <p class="summary-subtitle">Successfully delivered</p>
                </div>
            </div>
        </div>
        
        <div class="summary-card cancelled-card">
            <div class="summary-content">
                <div class="summary-icon">
                    <i class="fas fa-times-circle"></i>
                </div>
                <div class="summary-details">
                    <h3 class="summary-title">Cancelled</h3>
                    <h2 class="summary-value">{{ $deliveries->where('status', 'cancelled')->count() }}</h2>
                    <p class="summary-subtitle">Cancelled deliveries</p>
                </div>
            </div>
        </div>
        
        <div class="summary-card total-card">
            <div class="summary-content">
                <div class="summary-icon">
                    <i class="fas fa-shipping-fast"></i>
                </div>
                <div class="summary-details">
                    <h3 class="summary-title">Total</h3>
                    <h2 class="summary-value">{{ $deliveries->count() }}</h2>
                    <p class="summary-subtitle">All deliveries</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Mobile-Optimized Quick Search -->
    <div class="mobile-search-container">
        <div class="search-input-group">
            <i class="fas fa-search search-icon"></i>
            <input type="text" id="mobile-search" class="mobile-search-input" placeholder="Search deliveries...">
            <button type="button" class="search-clear-btn" id="clear-search">
                <i class="fas fa-times"></i>
            </button>
        </div>
    </div>

    <!-- Mobile Filter Toggle -->
    <div class="mobile-filter-toggle">
        <button type="button" class="btn modern-btn modern-btn-outline btn-block" id="toggle-filters">
            <i class="fas fa-filter"></i> Filters & Options
            <i class="fas fa-chevron-down toggle-icon"></i>
        </button>
    </div>

    <!-- Collapsible Filters Card -->
    <div class="card modern-card filter-card" id="filters-card" style="display: none;">
        <div class="card-header modern-header">
            <div class="header-content">
                <div class="header-title">
                    <i class="fas fa-filter header-icon"></i>
                    <h3 class="card-title">Delivery Filters</h3>
                </div>
            </div>
        </div>
        <div class="card-body modern-card-body">
            <div class="mobile-filter-grid">
                <div class="filter-group">
                    <label for="start-date" class="filter-label">Start Date</label>
                    <input type="date" id="start-date" class="form-control modern-input">
                </div>
                <div class="filter-group">
                    <label for="end-date" class="filter-label">End Date</label>
                    <input type="date" id="end-date" class="form-control modern-input">
                </div>
                <div class="filter-group">
                    <label for="status-filter" class="filter-label">Status</label>
                    <select id="status-filter" class="form-control modern-select">
                        <option value="">All Status</option>
                        <option value="pending">Pending</option>
                        <option value="in_transit">In Transit</option>
                        <option value="delivered">Delivered</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>
                <div class="filter-actions">
                    <button id="apply-filters" class="btn modern-btn modern-btn-primary btn-block">
                        <i class="fas fa-search"></i> Apply Filters
                    </button>
                    <button id="reset-filters" class="btn modern-btn modern-btn-outline btn-block">
                        <i class="fas fa-undo"></i> Reset
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Success/Error Messages -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible modern-alert">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
            <i class="fas fa-check-circle"></i>
            {{ session('success') }}
        </div>
    @endif
    
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible modern-alert">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
            <i class="fas fa-exclamation-circle"></i>
            {{ session('error') }}
        </div>
    @endif

    <!-- Mobile-Optimized Deliveries List -->
    <div class="deliveries-container">
        <!-- Desktop Table View -->
        <div class="desktop-table-view">
            <div class="card modern-card">
                <div class="card-header modern-header">
                    <div class="header-content">
                        <div class="header-title">
                            <i class="fas fa-shipping-fast header-icon"></i>
                            <h3 class="card-title">Delivery Records</h3>
                        </div>
                    </div>
                </div>
                
                <div class="card-body modern-card-body">
                    <div class="table-container" id="printable-area">
                        <div class="table-responsive modern-table-responsive">
                            <table class="table modern-table" id="deliveries-table">
                                <thead class="modern-thead">
                                    <tr>
                                        <th>Challan #</th>
                                        <th>Date</th>
                                        <th>Recipient</th>
                                        <th>Status</th>
                                        <th>Delivered By</th>
                                        <th width="15%">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="modern-tbody">
                                    @foreach($deliveries as $delivery)
                                        <tr>
                                            <td>
                                                <span class="font-weight-bold text-primary">{{ $delivery->challan_number }}</span>
                                            </td>
                                            <td>{{ $delivery->delivery_date->format('d-m-Y') }}</td>
                                            <td>
                                                <div class="recipient-info">
                                                    <strong class="recipient-name-link" 
                                                            data-recipient="{{ $delivery->recipient_name }}"
                                                            data-phone="{{ $delivery->recipient_phone }}"
                                                            data-address="{{ $delivery->recipient_address }}">
                                                        {{ $delivery->recipient_name }}
                                                    </strong>
                                                    @if($delivery->recipient_phone)
                                                        <div class="recipient-phone">
                                                            <i class="fas fa-phone"></i> {{ $delivery->recipient_phone }}
                                                        </div>
                                                    @endif
                                                </div>
                                            </td>
                                            <td>
                                                <div class="status-dropdown-container">
                                                    <div class="status-badge-clickable" 
                                                         data-delivery-id="{{ $delivery->id }}" 
                                                         data-current-status="{{ $delivery->status }}"
                                                         data-challan="{{ $delivery->challan_number }}">
                                                        @if($delivery->status == 'pending')
                                                            <span class="badge badge-warning status-badge">
                                                                <i class="fas fa-clock"></i> Pending 
                                                                <i class="fas fa-chevron-down status-arrow"></i>
                                                            </span>
                                                        @elseif($delivery->status == 'delivered')
                                                            <span class="badge badge-success status-badge">
                                                                <i class="fas fa-check-circle"></i> Delivered 
                                                                <i class="fas fa-chevron-down status-arrow"></i>
                                                            </span>
                                                        @elseif($delivery->status == 'in_transit')
                                                            <span class="badge badge-info status-badge">
                                                                <i class="fas fa-truck"></i> In Transit 
                                                                <i class="fas fa-chevron-down status-arrow"></i>
                                                            </span>
                                                        @else
                                                            <span class="badge badge-danger status-badge">
                                                                <i class="fas fa-times-circle"></i> Cancelled 
                                                                <i class="fas fa-chevron-down status-arrow"></i>
                                                            </span>
                                                        @endif
                                                    </div>
                                                    <div class="status-dropdown-menu" style="display: none;">
                                                        <div class="status-option" data-status="pending">
                                                            <i class="fas fa-clock"></i> Pending
                                                        </div>
                                                        <div class="status-option" data-status="in_transit">
                                                            <i class="fas fa-truck"></i> In Transit
                                                        </div>
                                                        <div class="status-option" data-status="delivered">
                                                            <i class="fas fa-check-circle"></i> Delivered
                                                        </div>
                                                        <div class="status-option" data-status="cancelled">
                                                            <i class="fas fa-times-circle"></i> Cancelled
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                @if($delivery->deliveredBy)
                                                    <div class="delivery-person">
                                                        <i class="fas fa-user"></i>
                                                        {{ $delivery->deliveredBy->name }}
                                                    </div>
                                                @else
                                                    <span class="text-muted">Not assigned</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="action-buttons">
                                                    <a href="{{ route('other-deliveries.show', $delivery) }}" 
                                                       class="btn modern-btn-sm modern-btn-info" 
                                                       title="View Details">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    
                                                    @if($delivery->status != 'delivered')
                                                        <a href="{{ route('other-deliveries.edit', $delivery) }}" 
                                                           class="btn modern-btn-sm modern-btn-warning" 
                                                           title="Edit">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        
                                                        <button type="button" 
                                                                class="btn modern-btn-sm modern-btn-danger delete-delivery" 
                                                                data-delivery-id="{{ $delivery->id }}"
                                                                data-challan="{{ $delivery->challan_number }}"
                                                                title="Delete">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    @endif
                                                    
                                                    <a href="{{ route('other-deliveries.print', $delivery) }}" 
                                                       class="btn modern-btn-sm modern-btn-secondary" 
                                                       target="_blank"
                                                       title="Print Challan">
                                                        <i class="fas fa-print"></i>
                                                    </a>
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

        <!-- Mobile Card View -->
        <div class="mobile-card-view">
            <div class="mobile-deliveries-grid" id="mobile-deliveries">
                @foreach($deliveries as $delivery)
                    <div class="mobile-delivery-card" 
                         data-challan="{{ $delivery->challan_number }}" 
                         data-status="{{ $delivery->status }}" 
                         data-recipient="{{ $delivery->recipient_name }}">
                        <div class="delivery-card-header">
                            <div class="challan-info">
                                <h4 class="challan-number">{{ $delivery->challan_number }}</h4>
                                <span class="delivery-date">{{ $delivery->delivery_date->format('d M Y') }}</span>
                            </div>
                            <div class="status-container">
                                <div class="mobile-status-badge-clickable" 
                                     data-delivery-id="{{ $delivery->id }}" 
                                     data-current-status="{{ $delivery->status }}"
                                     data-challan="{{ $delivery->challan_number }}">
                                    @if($delivery->status == 'pending')
                                        <span class="mobile-status-badge pending">
                                            <i class="fas fa-clock"></i> Pending
                                        </span>
                                    @elseif($delivery->status == 'delivered')
                                        <span class="mobile-status-badge delivered">
                                            <i class="fas fa-check-circle"></i> Delivered
                                        </span>
                                    @elseif($delivery->status == 'in_transit')
                                        <span class="mobile-status-badge in-transit">
                                            <i class="fas fa-truck"></i> In Transit
                                        </span>
                                    @else
                                        <span class="mobile-status-badge cancelled">
                                            <i class="fas fa-times-circle"></i> Cancelled
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="delivery-card-body">
                            <div class="recipient-section">
                                <div class="recipient-label">
                                    <i class="fas fa-user"></i> Recipient
                                </div>
                                <div class="recipient-details">
                                    <div class="recipient-name-link mobile-recipient-link" 
                                         data-recipient="{{ $delivery->recipient_name }}"
                                         data-phone="{{ $delivery->recipient_phone }}"
                                         data-address="{{ $delivery->recipient_address }}">
                                        {{ $delivery->recipient_name }}
                                    </div>
                                    @if($delivery->recipient_phone)
                                        <div class="recipient-phone">
                                            <i class="fas fa-phone"></i> {{ $delivery->recipient_phone }}
                                        </div>
                                    @endif
                                </div>
                            </div>

                            @if($delivery->deliveredBy)
                                <div class="delivered-by-section">
                                    <div class="delivered-by-label">
                                        <i class="fas fa-truck"></i> Delivered By
                                    </div>
                                    <div class="delivered-by-name">{{ $delivery->deliveredBy->name }}</div>
                                </div>
                            @endif
                        </div>

                        <div class="delivery-card-actions">
                            <div class="primary-actions">
                                <a href="{{ route('other-deliveries.show', $delivery) }}" 
                                   class="btn mobile-btn mobile-btn-primary">
                                    <i class="fas fa-eye"></i> View
                                </a>
                                
                                <button type="button" 
                                        class="btn mobile-btn mobile-btn-info recipient-history-btn"
                                        data-recipient="{{ $delivery->recipient_name }}">
                                    <i class="fas fa-history"></i> History
                                </button>
                            </div>

                            <div class="secondary-actions">
                                @if($delivery->status != 'delivered')
                                    <a href="{{ route('other-deliveries.edit', $delivery) }}" 
                                       class="btn mobile-btn mobile-btn-warning">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    
                                    <button type="button" 
                                            class="btn mobile-btn mobile-btn-danger delete-delivery" 
                                            data-delivery-id="{{ $delivery->id }}"
                                            data-challan="{{ $delivery->challan_number }}">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                @endif
                                
                                <a href="{{ route('other-deliveries.print', $delivery) }}" 
                                   class="btn mobile-btn mobile-btn-secondary" 
                                   target="_blank">
                                    <i class="fas fa-print"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Mobile Load More Button -->
            @if($deliveries->count() > 10)
                <div class="load-more-container">
                    <button type="button" class="btn modern-btn modern-btn-outline btn-block" id="load-more-btn">
                        <i class="fas fa-plus"></i> Load More Deliveries
                    </button>
                </div>
            @endif
        </div>
    </div>

    <!-- Mobile Floating Action Button -->
    <div class="mobile-fab">
        <a href="{{ route('other-deliveries.create') }}" class="fab-button">
            <i class="fas fa-plus"></i>
        </a>
    </div>

    <!-- Compact Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-dialog-centered modal-dialog-compact" role="document">
            <div class="modal-content compact-modal">
                <div class="modal-header compact-modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-exclamation-triangle"></i>
                        Delete Delivery
                    </h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body compact-modal-body">
                    <p><strong>Delete delivery <span id="delete-challan"></span>?</strong></p>
                    <p class="text-muted">This cannot be undone.</p>
                </div>
                <div class="modal-footer compact-modal-footer">
                    <button type="button" class="btn modern-btn modern-btn-outline" data-dismiss="modal">Cancel</button>
                    <form id="delete-form" method="POST" style="flex: 1;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn modern-btn modern-btn-danger" style="width: 100%;">
                            <i class="fas fa-trash"></i> Delete
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Status Update Modal -->
    <div class="modal fade" id="statusModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-dialog-centered modal-dialog-compact" role="document">
            <div class="modal-content compact-modal">
                <div class="modal-header compact-modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-edit"></i>
                        Update Status
                    </h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body compact-modal-body">
                    <div class="current-status-info">
                        <p><strong>Challan:</strong> <span id="status-challan"></span></p>
                        <p><strong>Current Status:</strong> <span id="current-status-badge"></span></p>
                    </div>
                    <div class="status-selection">
                        <label for="new-status" class="filter-label">Select New Status</label>
                        <select id="new-status" class="form-control modern-select">
                            <option value="pending">Pending</option>
                            <option value="in_transit">In Transit</option>
                            <option value="delivered">Delivered</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                    <div class="delivery-notes" style="margin-top: 15px;">
                        <label for="status-notes" class="filter-label">Notes (Optional)</label>
                        <textarea id="status-notes" class="form-control modern-input" rows="3" placeholder="Add any notes about this status change..."></textarea>
                    </div>
                </div>
                <div class="modal-footer compact-modal-footer">
                    <button type="button" class="btn modern-btn modern-btn-outline" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn modern-btn modern-btn-primary" id="update-status-btn">
                        <i class="fas fa-save"></i> Update Status
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Recipient History Modal -->
    <div class="modal fade" id="recipientHistoryModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content modern-modal">
                <div class="modal-header modern-modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-history"></i>
                        Recipient History
                    </h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="recipient-info-header">
                        <div class="recipient-details">
                            <h6><strong>Name:</strong> <span id="history-recipient-name"></span></h6>
                            <p><strong>Phone:</strong> <span id="history-recipient-phone"></span></p>
                            <p><strong>Address:</strong> <span id="history-recipient-address"></span></p>
                        </div>
                    </div>
                    
                    <div class="history-loading" style="text-align: center; padding: 20px;">
                        <i class="fas fa-spinner fa-spin"></i> Loading history...
                    </div>
                    
                    <div class="history-content" style="display: none;">
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Challan #</th>
                                        <th>Date</th>
                                        <th>Status</th>
                                        <th>Items</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="history-table-body">
                                    <!-- History data will be loaded here -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <div class="no-history" style="display: none; text-align: center; padding: 20px;">
                        <i class="fas fa-inbox"></i>
                        <p>No delivery history found for this recipient.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

@stop

@section('additional_css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.25/css/dataTables.bootstrap4.min.css">
    
    <style>
        /* Hide all toast notifications */
        .toast-container,
        #toast-container,
        .toastr {
            display: none !important;
        }

        /* Hide product descriptions completely */
        .product__description,
        .product-description,
        .product_description,
        .rte.product-description,
        .product__description.rte,
        .product-tabs .product-description,
        .product-single__description,
        .product-tabs .collapsible-toggle,
        .product-description-toggle,
        .description-tab,
        .tab-description,
        .product-card__description,
        .card-product__description,
        .product-item__description,
        .shopify-section--product-description,
        .product-block-description,
        [data-block-type="description"] {
            display: none !important;
        }

        /* Mobile-First Responsive Design */
        
        /* Summary Cards - Mobile Optimized */
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 12px;
            margin-bottom: 20px;
        }

        .summary-card {
            border: none;
            border-radius: 12px;
            overflow: hidden;
            transition: all 0.3s ease;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .summary-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15);
        }

        .summary-content {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 16px;
        }

        .summary-icon {
            width: 48px;
            height: 48px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            color: white;
            flex-shrink: 0;
        }

        .summary-details {
            flex: 1;
            min-width: 0;
        }

        .summary-title {
            font-size: 11px;
            color: #6b7280;
            margin: 0 0 4px 0;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .summary-value {
            font-size: 20px;
            font-weight: 700;
            color: #1f2937;
            margin: 0 0 2px 0;
            line-height: 1;
        }

        .summary-subtitle {
            font-size: 10px;
            color: #9ca3af;
            margin: 0;
        }

        /* Status-specific colors */
        .pending-card .summary-icon {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        }

        .delivered-card .summary-icon {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        }

        .cancelled-card .summary-icon {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        }

        .total-card .summary-icon {
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
        }

        /* Mobile Search */
        .mobile-search-container {
            margin-bottom: 16px;
        }

        .search-input-group {
            position: relative;
            display: flex;
            align-items: center;
        }

        .search-icon {
            position: absolute;
            left: 15px;
            color: #6b7280;
            z-index: 2;
        }

        .mobile-search-input {
            width: 100%;
            padding: 12px 15px 12px 45px;
            border: 2px solid #e5e7eb;
            border-radius: 25px;
            background: white;
            font-size: 16px;
            transition: all 0.2s ease;
        }

        .mobile-search-input:focus {
            outline: none;
            border-color: #6366f1;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
        }

        .search-clear-btn {
            position: absolute;
            right: 15px;
            background: none;
            border: none;
            color: #6b7280;
            padding: 5px;
            cursor: pointer;
            display: none;
        }

        /* Mobile Filter Toggle */
        .mobile-filter-toggle {
            margin-bottom: 16px;
        }

        .toggle-icon {
            transition: transform 0.3s ease;
        }

        .toggle-icon.rotated {
            transform: rotate(180deg);
        }

        /* Mobile Filter Grid */
        .mobile-filter-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 16px;
        }

        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .filter-label {
            font-size: 13px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 0;
        }

        .filter-actions {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            margin-top: 8px;
        }

        /* Clickable Recipient Names */
        .recipient-name-link,
        .mobile-recipient-link {
            color: #6366f1;
            cursor: pointer;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.2s ease;
            position: relative;
        }

        .recipient-name-link:hover,
        .mobile-recipient-link:hover {
            color: #4f46e5;
            text-decoration: underline;
        }

        .recipient-name-link:after,
        .mobile-recipient-link:after {
            content: '\f007';
            font-family: 'Font Awesome 5 Free';
            font-weight: 900;
            margin-left: 5px;
            font-size: 12px;
            opacity: 0.7;
        }

        /* Status Dropdown Styles */
        .status-dropdown-container {
            position: relative;
            display: inline-block;
        }

        .status-badge-clickable,
        .mobile-status-badge-clickable {
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .status-badge-clickable:hover,
        .mobile-status-badge-clickable:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
        }

        .status-arrow {
            margin-left: 4px;
            font-size: 10px;
            transition: transform 0.2s ease;
        }

        .status-dropdown-menu {
            position: absolute;
            top: 100%;
            left: 0;
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
            z-index: 1000;
            min-width: 140px;
            overflow: hidden;
        }

        .status-option {
            padding: 10px 15px;
            cursor: pointer;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
            font-weight: 500;
        }

        .status-option:hover {
            background: #f8fafc;
            color: #6366f1;
        }

        .status-option i {
            width: 14px;
            text-align: center;
        }

        /* Mobile Delivery Cards */
        .desktop-table-view {
            display: none;
        }

        .mobile-card-view {
            display: block;
        }

        .mobile-deliveries-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 16px;
        }

        .mobile-delivery-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .mobile-delivery-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15);
        }

        .delivery-card-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            padding: 16px 16px 12px 16px;
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
        }

        .challan-info {
            flex: 1;
        }

        .challan-number {
            font-size: 16px;
            font-weight: 700;
            color: #6366f1;
            margin: 0 0 4px 0;
        }

        .delivery-date {
            font-size: 12px;
            color: #6b7280;
            font-weight: 500;
        }

        .status-container {
            flex-shrink: 0;
        }

        .mobile-status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        .mobile-status-badge.pending {
            background: #fef3c7;
            color: #d97706;
        }

        .mobile-status-badge.delivered {
            background: #d1fae5;
            color: #059669;
        }

        .mobile-status-badge.in-transit {
            background: #dbeafe;
            color: #0891b2;
        }

        .mobile-status-badge.cancelled {
            background: #fee2e2;
            color: #dc2626;
        }

        .delivery-card-body {
            padding: 16px;
        }

        .recipient-section, .delivered-by-section {
            margin-bottom: 12px;
        }

        .recipient-label, .delivered-by-label {
            font-size: 11px;
            color: #6b7280;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 6px;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .recipient-phone {
            font-size: 12px;
            color: #6b7280;
            display: flex;
            align-items: center;
            gap: 4px;
            margin-top: 4px;
        }

        .delivered-by-name {
            font-size: 14px;
            color: #374151;
            font-weight: 500;
        }

        .delivery-card-actions {
            padding: 12px 16px 16px 16px;
            background: #f8fafc;
            border-top: 1px solid #e5e7eb;
        }

        .primary-actions {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
            margin-bottom: 8px;
        }

        .secondary-actions {
            display: flex;
            gap: 6px;
            justify-content: center;
        }

        /* Mobile Buttons */
        .mobile-btn {
            padding: 10px 16px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            border: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            text-decoration: none;
            transition: all 0.2s ease;
            min-height: 40px;
        }

        .mobile-btn-primary {
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
            color: white;
        }

        .mobile-btn-info {
            background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);
            color: white;
        }

        .mobile-btn-warning {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: white;
            padding: 8px 12px;
            min-height: 36px;
        }

        .mobile-btn-danger {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: white;
            padding: 8px 12px;
            min-height: 36px;
        }

        .mobile-btn-secondary {
            background: linear-gradient(135deg, #6b7280 0%, #4b5563 100%);
            color: white;
            padding: 8px 12px;
            min-height: 36px;
        }

        .mobile-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        /* Floating Action Button */
        .mobile-fab {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 1000;
        }

        .fab-button {
            width: 56px;
            height: 56px;
            border-radius: 50%;
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            text-decoration: none;
            box-shadow: 0 4px 20px rgba(99, 102, 241, 0.4);
            transition: all 0.3s ease;
        }

        .fab-button:hover {
            transform: scale(1.1);
            box-shadow: 0 6px 25px rgba(99, 102, 241, 0.5);
            color: white;
        }

        /* Load More */
        .load-more-container {
            margin-top: 20px;
            text-align: center;
        }

        /* Header Actions Mobile */
        .header-actions-group {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .btn-text {
            display: none;
        }

        /* Modern Form Elements */
        .modern-input, .modern-select {
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            padding: 12px 15px;
            background: white;
            color: #374151;
            transition: all 0.2s ease;
            font-size: 16px;
        }

        .modern-input:focus, .modern-select:focus {
            outline: none;
            border-color: #6366f1;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
        }

        /* Modern Card and Button Styles */
        .modern-card {
            background: white;
            border: none;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            margin-bottom: 16px;
            overflow: hidden;
        }

        .modern-header {
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
            color: white;
            padding: 16px;
        }

        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header-title {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .header-icon {
            font-size: 18px;
        }

        .card-title {
            color: white;
            font-weight: 600;
            margin: 0;
            font-size: 16px;
        }

        .modern-card-body {
            padding: 16px;
        }

        .modern-btn {
            padding: 10px 16px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 14px;
            border: 2px solid transparent;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            text-decoration: none;
            cursor: pointer;
        }

        .modern-btn-primary {
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
            color: white;
        }

        .modern-btn-secondary {
            background: linear-gradient(135deg, #6b7280 0%, #4b5563 100%);
            color: white;
        }

        .modern-btn-info {
            background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);
            color: white;
        }

        .modern-btn-outline {
            background: white;
            color: #6366f1;
            border-color: #6366f1;
        }

        .modern-btn-danger {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: white;
        }

        .modern-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .btn-block {
            width: 100%;
            justify-content: center;
        }

        /* Compact Modal Styles */
        .modal-dialog-compact {
            margin: 20px auto;
            max-width: 400px;
            width: calc(100% - 40px);
        }

        .compact-modal {
            border-radius: 8px;
            border: none;
            overflow: hidden;
        }

        .compact-modal-header {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: white;
            padding: 12px 16px;
        }

        .compact-modal-header .modal-title {
            font-weight: 600;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .compact-modal-body {
            padding: 16px;
            text-align: center;
        }

        .compact-modal-body p {
            margin-bottom: 8px;
            font-size: 14px;
        }

        .compact-modal-footer {
            padding: 12px 16px;
            display: flex;
            gap: 8px;
        }

        .compact-modal-footer .btn {
            flex: 1;
            padding: 8px 12px;
            font-size: 13px;
            border-radius: 6px;
        }

        /* Modern Modal Styles */
        .modern-modal {
            border-radius: 12px;
            border: none;
            overflow: hidden;
        }

        .modern-modal-header {
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
            color: white;
            padding: 16px 20px;
        }

        .modern-modal-header .modal-title {
            font-weight: 600;
            font-size: 16px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        /* Recipient History Modal Styles */
        .recipient-info-header {
            background: #f8fafc;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .recipient-info-header h6 {
            margin: 0 0 8px 0;
            color: #374151;
        }

        .recipient-info-header p {
            margin: 0 0 4px 0;
            color: #6b7280;
            font-size: 14px;
        }

        /* Alerts */
        .modern-alert {
            border: none;
            border-radius: 8px;
            padding: 12px 16px;
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
        }

        /* Tablet and Desktop Responsive */
        @media (min-width: 768px) {
            .summary-grid {
                grid-template-columns: repeat(4, 1fr);
                gap: 16px;
                margin-bottom: 24px;
            }

            .summary-icon {
                width: 60px;
                height: 60px;
                font-size: 24px;
            }

            .summary-value {
                font-size: 28px;
            }

            .mobile-filter-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 20px;
            }

            .filter-actions {
                grid-column: span 2;
                display: flex;
                justify-content: flex-start;
            }

            .filter-actions .btn {
                width: auto;
                margin-right: 12px;
            }

            .mobile-deliveries-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 20px;
            }

            .btn-text {
                display: inline;
            }

            .mobile-fab {
                display: none;
            }

            .modal-dialog-compact {
                max-width: 450px;
                margin: 30px auto;
            }
            
            .compact-modal-body {
                padding: 20px;
            }
            
            .compact-modal-footer {
                padding: 16px 20px;
            }
        }

        @media (min-width: 1024px) {
            .desktop-table-view {
                display: block;
            }

            .mobile-card-view {
                display: none;
            }

            .mobile-search-container {
                display: none;
            }

            .mobile-filter-toggle {
                display: none;
            }

            .filter-card {
                display: block !important;
            }

            .summary-grid {
                margin-bottom: 32px;
            }

            .modern-card {
                margin-bottom: 24px;
                box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            }

            .modern-header {
                padding: 20px 24px;
            }

            .modern-card-body {
                padding: 24px;
            }

            .header-icon {
                font-size: 24px;
            }

            .card-title {
                font-size: 18px;
            }

            /* Desktop table styles */
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
                transform: translateY(-1px);
                box-shadow: 0 4px 12px rgba(99, 102, 241, 0.1);
            }

            .modern-tbody td {
                padding: 16px;
                vertical-align: middle;
                border: none !important;
                font-size: 14px;
                color: #374151 !important;
                background: transparent !important;
            }

            .action-buttons {
                display: flex;
                gap: 4px;
                flex-wrap: wrap;
            }

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

            .modern-btn-sm.modern-btn-info {
                background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);
                color: white;
            }

            .modern-btn-sm.modern-btn-warning {
                background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
                color: white;
            }

            .modern-btn-sm.modern-btn-danger {
                background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
                color: white;
            }

            .modern-btn-sm.modern-btn-secondary {
                background: linear-gradient(135deg, #6b7280 0%, #4b5563 100%);
                color: white;
            }

            .modern-btn-sm:hover {
                transform: translateY(-1px);
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            }

            .status-badge {
                padding: 6px 12px;
                font-size: 12px;
                font-weight: 600;
                border-radius: 20px;
                display: inline-flex;
                align-items: center;
                gap: 4px;
            }

            .recipient-info strong {
                color: #374151;
                font-size: 14px;
            }

            .delivery-person {
                color: #374151;
                font-size: 14px;
                display: flex;
                align-items: center;
                gap: 6px;
            }
        }

        /* Print Styles */
        @media print {
            body * {
                visibility: hidden;
            }
            
            #printable-area, #printable-area * {
                visibility: visible;
            }
            
            #printable-area {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
            }
            
            .modern-card {
                box-shadow: none;
                border: 1px solid #ddd;
            }
            
            .modern-thead {
                background: #f8f9fa !important;
                color: #000 !important;
            }
            
            .modern-thead th {
                color: #000 !important;
                background: #f8f9fa !important;
            }
            
            .btn, .header-actions-group, .mobile-fab {
                display: none;
            }
        }
    </style>
<style>
    /* Z-index hierarchy: Table base -> Status badges -> Modals */
    
    /* Hide all toast notifications */
    .toast-container,
    #toast-container,
    .toastr {
        display: none !important;
    }

    /* Hide product descriptions completely */
    .product__description,
    .product-description,
    .product_description,
    .rte.product-description,
    .product__description.rte,
    .product-tabs .product-description,
    .product-single__description,
    .product-tabs .collapsible-toggle,
    .product-description-toggle,
    .description-tab,
    .tab-description,
    .product-card__description,
    .card-product__description,
    .product-item__description,
    .shopify-section--product-description,
    .product-block-description,
    [data-block-type="description"] {
        display: none !important;
    }

    /* Base z-index for table elements */
    .modern-table-responsive,
    .modern-table {
        position: relative;
        z-index: 1;
    }
    
    .modern-tbody tr {
        position: relative;
        z-index: 2;
    }
    
    /* Clickable Status Badges - No dropdown, just clickable buttons */
    .status-badge-clickable,
    .mobile-status-badge-clickable {
        cursor: pointer;
        transition: all 0.2s ease;
        position: relative;
        z-index: 10;
        display: inline-block;
    }

    .status-badge-clickable:hover,
    .mobile-status-badge-clickable:hover {
        transform: translateY(-1px);
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
    }

    /* Remove dropdown elements completely */
    .status-arrow {
        display: none !important;
    }

    .status-dropdown-menu {
        display: none !important;
    }

    .status-dropdown-container {
        position: relative;
        display: inline-block;
        z-index: 10;
    }

    /* Clickable recipient names */
    .recipient-name-link,
    .mobile-recipient-link {
        color: #6366f1;
        cursor: pointer;
        text-decoration: none;
        font-weight: 600;
        transition: all 0.2s ease;
        position: relative;
    }

    .recipient-name-link:hover,
    .mobile-recipient-link:hover {
        color: #4f46e5;
        text-decoration: underline;
    }

    .recipient-name-link:after,
    .mobile-recipient-link:after {
        content: '\f007';
        font-family: 'Font Awesome 5 Free';
        font-weight: 900;
        margin-left: 5px;
        font-size: 12px;
        opacity: 0.7;
    }

    /* MOBILE-ONLY STYLES - Will not affect desktop */
    @media only screen and (max-width: 1023px) {
        /* Mobile-First Responsive Design */
        
        /* Summary Cards - Mobile Optimized */
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 12px;
            margin-bottom: 20px;
        }

        .summary-card {
            border: none;
            border-radius: 12px;
            overflow: hidden;
            transition: all 0.3s ease;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .summary-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15);
        }

        .summary-content {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 16px;
        }

        .summary-icon {
            width: 48px;
            height: 48px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            color: white;
            flex-shrink: 0;
        }

        .summary-details {
            flex: 1;
            min-width: 0;
        }

        .summary-title {
            font-size: 11px;
            color: #6b7280;
            margin: 0 0 4px 0;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .summary-value {
            font-size: 20px;
            font-weight: 700;
            color: #1f2937;
            margin: 0 0 2px 0;
            line-height: 1;
        }

        .summary-subtitle {
            font-size: 10px;
            color: #9ca3af;
            margin: 0;
        }

        /* Mobile Search */
        .mobile-search-container {
            margin-bottom: 16px;
        }

        .search-input-group {
            position: relative;
            display: flex;
            align-items: center;
        }

        .search-icon {
            position: absolute;
            left: 15px;
            color: #6b7280;
            z-index: 2;
        }

        .mobile-search-input {
            width: 100%;
            padding: 12px 15px 12px 45px;
            border: 2px solid #e5e7eb;
            border-radius: 25px;
            background: white;
            font-size: 16px;
            transition: all 0.2s ease;
        }

        .mobile-search-input:focus {
            outline: none;
            border-color: #6366f1;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
        }

        .search-clear-btn {
            position: absolute;
            right: 15px;
            background: none;
            border: none;
            color: #6b7280;
            padding: 5px;
            cursor: pointer;
            display: none;
        }

        /* Mobile Filter Toggle */
        .mobile-filter-toggle {
            margin-bottom: 16px;
        }

        .toggle-icon {
            transition: transform 0.3s ease;
        }

        .toggle-icon.rotated {
            transform: rotate(180deg);
        }

        /* Mobile Filter Grid */
        .mobile-filter-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 16px;
        }

        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .filter-label {
            font-size: 13px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 0;
        }

        .filter-actions {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            margin-top: 8px;
        }

        /* Mobile Delivery Cards */
        .desktop-table-view {
            display: none;
        }

        .mobile-card-view {
            display: block;
        }

        .mobile-deliveries-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 16px;
            position: relative;
            z-index: 1;
        }

        .mobile-delivery-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            transition: all 0.3s ease;
            position: relative;
            z-index: 1;
        }

        .mobile-delivery-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15);
        }

        .delivery-card-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            padding: 16px 16px 12px 16px;
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
        }

        .challan-info {
            flex: 1;
        }

        .challan-number {
            font-size: 16px;
            font-weight: 700;
            color: #6366f1;
            margin: 0 0 4px 0;
        }

        .delivery-date {
            font-size: 12px;
            color: #6b7280;
            font-weight: 500;
        }

        .status-container {
            flex-shrink: 0;
        }

        .mobile-status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            cursor: pointer;
            transition: all 0.2s ease;
            border: 2px solid transparent;
        }

        .mobile-status-badge:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
            border-color: rgba(255, 255, 255, 0.3);
        }

        .mobile-status-badge.pending {
            background: #fef3c7;
            color: #d97706;
        }

        .mobile-status-badge.pending:hover {
            background: #fde68a;
        }

        .mobile-status-badge.delivered {
            background: #d1fae5;
            color: #059669;
        }

        .mobile-status-badge.delivered:hover {
            background: #a7f3d0;
        }

        .mobile-status-badge.cancelled {
            background: #fee2e2;
            color: #dc2626;
        }

        .mobile-status-badge.cancelled:hover {
            background: #fecaca;
        }

        /* Continue with other mobile-only styles... */
        .btn-text {
            display: none;
        }

        .mobile-fab {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 1000;
        }

        .fab-button {
            width: 56px;
            height: 56px;
            border-radius: 50%;
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            text-decoration: none;
            box-shadow: 0 4px 20px rgba(99, 102, 241, 0.4);
            transition: all 0.3s ease;
        }

        .fab-button:hover {
            transform: scale(1.1);
            box-shadow: 0 6px 25px rgba(99, 102, 241, 0.5);
            color: white;
        }
    }

    /* TABLET STYLES */
    @media only screen and (min-width: 768px) and (max-width: 1023px) {
        .summary-grid {
            grid-template-columns: repeat(4, 1fr);
            gap: 16px;
            margin-bottom: 24px;
        }

        .summary-icon {
            width: 60px;
            height: 60px;
            font-size: 24px;
        }

        .summary-value {
            font-size: 28px;
        }

        .mobile-deliveries-grid {
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }

        .btn-text {
            display: inline;
        }

        .mobile-fab {
            display: none;
        }
    }

    /* DESKTOP-ONLY STYLES */
    @media only screen and (min-width: 1024px) {
        .desktop-table-view {
            display: block;
        }

        .mobile-card-view {
            display: none;
        }

        .mobile-search-container {
            display: none;
        }

        .mobile-filter-toggle {
            display: none;
        }

        .filter-card {
            display: block !important;
        }

        .summary-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 16px;
            margin-bottom: 32px;
        }

        .summary-icon {
            width: 60px;
            height: 60px;
            font-size: 24px;
        }

        .summary-value {
            font-size: 28px;
        }

        .btn-text {
            display: inline;
        }

        .mobile-fab {
            display: none;
        }

        /* Desktop table styles */
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
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.1);
        }

        .modern-tbody td {
            padding: 16px;
            vertical-align: middle;
            border: none !important;
            font-size: 14px;
            color: #374151 !important;
            background: transparent !important;
        }

        .action-buttons {
            display: flex;
            gap: 4px;
            flex-wrap: wrap;
        }

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

        .modern-btn-sm.modern-btn-info {
            background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);
            color: white;
        }

        .modern-btn-sm.modern-btn-warning {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: white;
        }

        .modern-btn-sm.modern-btn-danger {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: white;
        }

        .modern-btn-sm.modern-btn-secondary {
            background: linear-gradient(135deg, #6b7280 0%, #4b5563 100%);
            color: white;
        }

        .modern-btn-sm:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .status-badge {
            padding: 6px 12px;
            font-size: 12px;
            font-weight: 600;
            border-radius: 20px;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            cursor: pointer;
            transition: all 0.2s ease;
            border: 2px solid transparent;
        }

        .status-badge:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
            border-color: rgba(255, 255, 255, 0.3);
        }
    }

    /* Modal elements - highest priority across all devices */
    .modal {
        z-index: 1050;
    }
    
    .modal-backdrop {
        z-index: 1040;
    }
    
    .modal-dialog {
        position: relative;
        z-index: 1060;
    }
    
    .modal-content {
        position: relative;
        z-index: 1061;
    }
    
    /* Specific modal z-indexes */
    #deleteModal {
        z-index: 1070;
    }
    
    #statusModal {
        z-index: 1080;
    }
    
    #recipientHistoryModal {
        z-index: 1090;
    }

    /* Shared styles across all devices */
    .modern-input, .modern-select {
        border: 2px solid #e5e7eb;
        border-radius: 8px;
        padding: 12px 15px;
        background: white;
        color: #374151;
        transition: all 0.2s ease;
        font-size: 16px;
    }

    .modern-input:focus, .modern-select:focus {
        outline: none;
        border-color: #6366f1;
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
    }

    .modern-card {
        background: white;
        border: none;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        margin-bottom: 16px;
        overflow: hidden;
    }

    .modern-header {
        background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
        color: white;
        padding: 16px;
    }

    .modern-btn {
        padding: 10px 16px;
        border-radius: 8px;
        font-weight: 600;
        font-size: 14px;
        border: 2px solid transparent;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        text-decoration: none;
        cursor: pointer;
    }

    .modern-btn-primary {
        background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
        color: white;
    }

    .modern-btn-outline {
        background: white;
        color: #6366f1;
        border-color: #6366f1;
    }

    .modern-btn-danger {
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        color: white;
    }

    .modern-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }

    .btn-block {
        width: 100%;
        justify-content: center;
    }

    /* Status-specific colors for summary cards */
    .pending-card .summary-icon {
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
    }

    .delivered-card .summary-icon {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    }

    .cancelled-card .summary-icon {
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
    }

    .total-card .summary-icon {
        background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
    }
</style>
<style>
    /* Fix for status select dropdown in modal */
    #statusModal .modern-select,
    #new-status {
        min-height: 45px;
        height: 45px;
        padding: 12px 15px;
        font-size: 14px;
        line-height: 1.4;
        border: 2px solid #e5e7eb;
        border-radius: 8px;
        background: white;
        color: #374151;
        transition: all 0.2s ease;
    }

    #statusModal .modern-select:focus,
    #new-status:focus {
        outline: none;
        border-color: #6366f1;
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
    }

    /* Fix for all select elements in modals */
    .modal .form-control.modern-select {
        min-height: 45px;
        height: 45px;
        padding: 12px 15px;
        font-size: 14px;
        line-height: 1.4;
    }

    /* Ensure option text is not cut off */
    .modal select option {
        padding: 8px 12px;
        font-size: 14px;
        line-height: 1.4;
    }

    /* Mobile specific adjustments */
    @media (max-width: 768px) {
        #statusModal .modern-select,
        #new-status {
            min-height: 48px;
            height: 48px;
            font-size: 16px; /* Prevent zoom on iOS */
        }
    }

    /* Z-index hierarchy: Table base -> Status badges -> Modals */
    .modern-table-responsive,
    .modern-table {
        position: relative;
        z-index: 1;
    }
    
    .modern-tbody tr {
        position: relative;
        z-index: 2;
    }
    
    /* Status badges - clickable buttons */
    .status-badge-clickable,
    .mobile-status-badge-clickable {
        cursor: pointer;
        transition: all 0.2s ease;
        position: relative;
        z-index: 10;
        display: inline-block;
    }

    .status-badge-clickable:hover,
    .mobile-status-badge-clickable:hover {
        transform: translateY(-1px);
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
    }

    /* Remove dropdown elements */
    .status-arrow {
        display: none !important;
    }

    .status-dropdown-menu {
        display: none !important;
    }

    /* Modal z-indexes */
    .modal {
        z-index: 1050;
    }
    
    .modal-backdrop {
        z-index: 1040;
    }
    
    .modal-dialog {
        position: relative;
        z-index: 1060;
    }
    
    .modal-content {
        position: relative;
        z-index: 1061;
    }
    
    #deleteModal {
        z-index: 1070;
    }
    
    #statusModal {
        z-index: 1080;
    }
    
    #recipientHistoryModal {
        z-index: 1090;
    }
</style>

@stop

@section('additional_js')
    <script src="https://cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.25/js/dataTables.bootstrap4.min.js"></script>
    
    <script>
        $(document).ready(function() {
            // Check if mobile
            function isMobile() {
                return window.innerWidth <= 1023;
            }

            // Initialize DataTable for desktop only
            var table;
            if (!isMobile()) {
                table = $('#deliveries-table').DataTable({
                    "order": [[0, "desc"]],
                    "pageLength": 25,
                    "responsive": true,
                    "language": {
                        "search": "Search deliveries:",
                        "lengthMenu": "Show _MENU_ deliveries per page",
                        "info": "Showing _START_ to _END_ of _TOTAL_ deliveries",
                        "paginate": {
                            "first": "First",
                            "last": "Last",
                            "next": "Next",
                            "previous": "Previous"
                        }
                    }
                });
            }

            // Set default dates (current month)
            const today = new Date();
            const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
            const lastDay = new Date(today.getFullYear(), today.getMonth() + 1, 0);
            
            $('#start-date').val(firstDay.toISOString().split('T')[0]);
            $('#end-date').val(lastDay.toISOString().split('T')[0]);

            // Mobile search functionality
            $('#mobile-search').on('input', function() {
                const searchTerm = $(this).val().toLowerCase();
                const clearBtn = $('#clear-search');
                
                if (searchTerm.length > 0) {
                    clearBtn.show();
                } else {
                    clearBtn.hide();
                }

                // Filter mobile cards
                $('.mobile-delivery-card').each(function() {
                    const challan = $(this).data('challan').toLowerCase();
                    const recipient = $(this).data('recipient').toLowerCase();
                    const status = $(this).data('status').toLowerCase();
                    
                    if (challan.includes(searchTerm) || 
                        recipient.includes(searchTerm) || 
                        status.includes(searchTerm)) {
                        $(this).show();
                    } else {
                        $(this).hide();
                    }
                });
            });

            // Clear search
            $('#clear-search').on('click', function() {
                $('#mobile-search').val('');
                $(this).hide();
                $('.mobile-delivery-card').show();
            });

            // Toggle filters
            $('#toggle-filters').on('click', function() {
                const filtersCard = $('#filters-card');
                const toggleIcon = $('.toggle-icon');
                
                if (filtersCard.is(':visible')) {
                    filtersCard.slideUp(300);
                    toggleIcon.removeClass('rotated');
                } else {
                    filtersCard.slideDown(300);
                    toggleIcon.addClass('rotated');
                }
            });

            // Apply filters (without toast notifications)
            $('#apply-filters').on('click', function() {
                var startDate = $('#start-date').val();
                var endDate = $('#end-date').val();
                var status = $('#status-filter').val();
                var search = $('#search-filter').val();

                if (isMobile()) {
                    $('.mobile-delivery-card').each(function() {
                        var show = true;
                        
                        if (status && $(this).data('status') !== status) {
                            show = false;
                        }
                        
                        if (search) {
                            const challan = $(this).data('challan').toLowerCase();
                            const recipient = $(this).data('recipient').toLowerCase();
                            if (!challan.includes(search.toLowerCase()) && 
                                !recipient.includes(search.toLowerCase())) {
                                show = false;
                            }
                        }
                        
                        if (show) {
                            $(this).show();
                        } else {
                            $(this).hide();
                        }
                    });
                } else {
                    table.search(search);
                    table.columns().search('');
                    
                    if (status) {
                        table.column(3).search(status);
                    }
                    
                    table.draw();
                }
                
                console.log('Filters applied successfully');
            });

            // Reset filters (without toast notifications)
            $('#reset-filters').on('click', function() {
                $('#start-date').val(firstDay.toISOString().split('T')[0]);
                $('#end-date').val(lastDay.toISOString().split('T')[0]);
                $('#status-filter').val('');
                $('#search-filter').val('');
                $('#mobile-search').val('');
                $('#clear-search').hide();
                
                if (isMobile()) {
                    $('.mobile-delivery-card').show();
                } else {
                    table.search('').columns().search('').draw();
                }
                
                console.log('Filters reset successfully');
            });

            // Delete delivery modal
            $('.delete-delivery').on('click', function() {
                var deliveryId = $(this).data('delivery-id');
                var challanNumber = $(this).data('challan');
                
                $('#delete-challan').text(challanNumber);
                $('#delete-form').attr('action', '/other-deliveries/' + deliveryId);
                $('#deleteModal').modal('show');
            });

            // Status update functionality
            $(document).on('click', '.status-badge-clickable, .mobile-status-badge-clickable', function() {
                const deliveryId = $(this).data('delivery-id');
                const currentStatus = $(this).data('current-status');
                const challan = $(this).data('challan');
                
                $('#status-challan').text(challan);
                $('#current-status-badge').html(getStatusBadgeHtml(currentStatus));
                $('#new-status').val(currentStatus);
                $('#status-notes').val('');
                
                // Store delivery ID for update
                $('#update-status-btn').data('delivery-id', deliveryId);
                
                $('#statusModal').modal('show');
            });

                        // Update status button click
            $('#update-status-btn').on('click', function() {
                const deliveryId = $(this).data('delivery-id');
                const newStatus = $('#new-status').val();
                const notes = $('#status-notes').val();
                
                $.ajax({
                    url: `/other-deliveries/${deliveryId}/update-status`,
                    method: 'PUT', // Changed to PUT to match your route
                    data: {
                        status: newStatus,
                        notes: notes,
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        if (response.success) {
                            // Refresh the page or update the specific elements
                            location.reload();
                        }
                    },
                    error: function(xhr) {
                        console.error('Status update failed:', xhr.responseText);
                        alert('Failed to update status. Please try again.');
                    }
                });
                
                $('#statusModal').modal('hide');
            });

            // Recipient history functionality
            $(document).on('click', '.recipient-name-link, .mobile-recipient-link, .recipient-history-btn', function() {
                const recipientName = $(this).data('recipient');
                const recipientPhone = $(this).data('phone') || 'N/A';
                const recipientAddress = $(this).data('address') || 'N/A';
                
                // Set recipient info in modal
                $('#history-recipient-name').text(recipientName);
                $('#history-recipient-phone').text(recipientPhone);
                $('#history-recipient-address').text(recipientAddress);
                
                // Show loading state
                $('.history-loading').show();
                $('.history-content').hide();
                $('.no-history').hide();
                
                // Show modal
                $('#recipientHistoryModal').modal('show');
                
                // Fetch recipient history using your existing route
                $.ajax({
                    url: `/other-deliveries/recipient/${encodeURIComponent(recipientName)}/history-ajax`,
                    method: 'GET',
                    success: function(response) {
                        $('.history-loading').hide();
                        
                        if (response.length > 0) {
                            let historyHtml = '';
                            response.forEach(function(delivery) {
                                let statusBadge = getStatusBadgeHtml(delivery.status);
                                let itemsText = delivery.items ? delivery.items.length + ' items' : 'No items';
                                
                                historyHtml += `
                                    <tr>
                                        <td><strong>${delivery.challan_number}</strong></td>
                                        <td>${formatDate(delivery.delivery_date)}</td>
                                        <td>${statusBadge}</td>
                                        <td>${itemsText}</td>
                                        <td>
                                            <a href="/other-deliveries/${delivery.id}" class="btn btn-sm btn-info" title="View">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="/other-deliveries/${delivery.id}/print" class="btn btn-sm btn-secondary" target="_blank" title="Print">
                                                <i class="fas fa-print"></i>
                                            </a>
                                        </td>
                                    </tr>
                                `;
                            });
                            
                            $('#history-table-body').html(historyHtml);
                            $('.history-content').show();
                        } else {
                            $('.no-history').show();
                        }
                    },
                    error: function(xhr) {
                        $('.history-loading').hide();
                        $('.no-history').show();
                        console.error('Failed to load recipient history:', xhr.responseText);
                    }
                });
            });

            // Helper function to get status badge HTML
            function getStatusBadgeHtml(status) {
                switch(status) {
                    case 'pending':
                        return '<span class="badge badge-warning"><i class="fas fa-clock"></i> Pending</span>';
                    case 'delivered':
                        return '<span class="badge badge-success"><i class="fas fa-check-circle"></i> Delivered</span>';
                    case 'in_transit':
                        return '<span class="badge badge-info"><i class="fas fa-truck"></i> In Transit</span>';
                    case 'cancelled':
                        return '<span class="badge badge-danger"><i class="fas fa-times-circle"></i> Cancelled</span>';
                    default:
                        return '<span class="badge badge-secondary">' + status.charAt(0).toUpperCase() + status.slice(1) + '</span>';
                }
            }

            // Helper function to format date
            function formatDate(dateString) {
                const date = new Date(dateString);
                return date.toLocaleDateString('en-GB', {
                    day: '2-digit',
                    month: '2-digit',
                    year: 'numeric'
                });
            }

            // Status dropdown functionality
            $(document).on('click', '.status-dropdown-container', function(e) {
                e.stopPropagation();
                const dropdown = $(this).find('.status-dropdown-menu');
                
                // Close all other dropdowns
                $('.status-dropdown-menu').not(dropdown).hide();
                
                // Toggle current dropdown
                dropdown.toggle();
            });

            // Status option selection
            $(document).on('click', '.status-option', function(e) {
                e.stopPropagation();
                const newStatus = $(this).data('status');
                const container = $(this).closest('.status-dropdown-container');
                const deliveryId = container.find('.status-badge-clickable').data('delivery-id');
                const challan = container.find('.status-badge-clickable').data('challan');
                
                // Hide dropdown
                container.find('.status-dropdown-menu').hide();
                
                // Open status update modal
                $('#status-challan').text(challan);
                $('#current-status-badge').html(getStatusBadgeHtml(newStatus));
                $('#new-status').val(newStatus);
                $('#status-notes').val('');
                $('#update-status-btn').data('delivery-id', deliveryId);
                
                $('#statusModal').modal('show');
            });

            // Close dropdowns when clicking outside
            $(document).on('click', function() {
                $('.status-dropdown-menu').hide();
            });

            // Print deliveries list
            $('#print-deliveries').on('click', function() {
                window.print();
            });

            // Export deliveries (without toast notification)
            $('#export-deliveries').on('click', function() {
                // Use your existing export route
                window.location.href = '/other-deliveries/export';
            });

            // Load more functionality (for mobile)
            $('#load-more-btn').on('click', function() {
                console.log('Load more functionality would be implemented with AJAX');
            });

            // Auto-hide alerts after 5 seconds
            setTimeout(function() {
                $('.alert').fadeOut('slow');
            }, 5000);

            // Handle window resize
            $(window).on('resize', function() {
                // Reinitialize components if needed when switching between mobile/desktop
                if (isMobile() && table) {
                    table.destroy();
                    table = null;
                } else if (!isMobile() && !table) {
                    table = $('#deliveries-table').DataTable({
                        "order": [[0, "desc"]],
                        "pageLength": 25,
                        "responsive": true
                    });
                }
            });

            // Touch-friendly interactions for mobile
            if (isMobile()) {
                // Add touch feedback
                $('.mobile-delivery-card').on('touchstart', function() {
                    $(this).addClass('touching');
                }).on('touchend', function() {
                    $(this).removeClass('touching');
                });
            }
        });
    </script>
@stop
