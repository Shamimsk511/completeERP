@extends('layouts.modern-admin')

@section('title', 'View Delivery')

@section('page_title', 'Delivery Challan #' . $otherDelivery->challan_number)

@section('header_actions')
    <div class="header-actions-group">
        <a href="{{ route('other-deliveries.index') }}" class="btn modern-btn modern-btn-outline">
            <i class="fas fa-arrow-left"></i> Back to List
        </a>
        @if($otherDelivery->status != 'delivered')
            <a href="{{ route('other-deliveries.edit', $otherDelivery) }}" class="btn modern-btn modern-btn-warning">
                <i class="fas fa-edit"></i> Edit Delivery
            </a>
        @endif
        <a href="{{ route('other-deliveries.print', $otherDelivery) }}" class="btn modern-btn modern-btn-info" target="_blank">
            <i class="fas fa-print"></i> Print Challan
        </a>
        <a href="{{ route('other-deliveries.recipient-history', $otherDelivery->recipient_name) }}" class="btn modern-btn modern-btn-secondary">
            <i class="fas fa-history"></i> Recipient History
        </a>
    </div>
@stop

@section('page_content')
    <!-- Status Summary Cards -->
    <div class="summary-cards-container">
        <div class="summary-card status-card">
            <div class="summary-content">
                <div class="summary-icon">
                    @if($otherDelivery->status == 'pending')
                        <i class="fas fa-clock"></i>
                    @elseif($otherDelivery->status == 'delivered')
                        <i class="fas fa-check-circle"></i>
                    @else
                        <i class="fas fa-times-circle"></i>
                    @endif
                </div>
                <div class="summary-details">
                    <h3 class="summary-title">Status</h3>
                    <h2 class="summary-value">
                        @if($otherDelivery->status == 'pending')
                            Pending
                        @elseif($otherDelivery->status == 'delivered')
                            Delivered
                        @else
                            Cancelled
                        @endif
                    </h2>
                    <p class="summary-subtitle">Current delivery status</p>
                </div>
            </div>
        </div>
        
        <div class="summary-card items-card">
            <div class="summary-content">
                <div class="summary-icon">
                    <i class="fas fa-boxes"></i>
                </div>
                <div class="summary-details">
                    <h3 class="summary-title">Items</h3>
                    <h2 class="summary-value">{{ $otherDelivery->items->count() }}</h2>
                    <p class="summary-subtitle">Products in delivery</p>
                </div>
            </div>
        </div>
        
        <div class="summary-card quantity-card">
            <div class="summary-content">
                <div class="summary-icon">
                    <i class="fas fa-weight-hanging"></i>
                </div>
                <div class="summary-details">
                    <h3 class="summary-title">Total Quantity</h3>
                    <h2 class="summary-value">{{ number_format($otherDelivery->items->sum('quantity'), 2) }}</h2>
                    <p class="summary-subtitle">Total amount delivered</p>
                </div>
            </div>
        </div>
        
        <div class="summary-card date-card">
            <div class="summary-content">
                <div class="summary-icon">
                    <i class="fas fa-calendar-alt"></i>
                </div>
                <div class="summary-details">
                    <h3 class="summary-title">Delivery Date</h3>
                    <h2 class="summary-value">{{ $otherDelivery->delivery_date->format('d') }}</h2>
                    <p class="summary-subtitle">{{ $otherDelivery->delivery_date->format('M Y') }}</p>
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

    <div class="main-content-grid">
        <!-- Challan Information -->
        <div class="info-card">
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
                    <div class="info-grid">
                        <div class="info-item">
                            <div class="info-label">Challan Number</div>
                            <div class="info-value">{{ $otherDelivery->challan_number }}</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Delivery Date</div>
                            <div class="info-value">{{ $otherDelivery->delivery_date->format('d-m-Y') }}</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Status</div>
                            <div class="info-value">
                                @if($otherDelivery->status == 'pending')
                                    <span class="badge badge-warning status-badge">
                                        <i class="fas fa-clock"></i> Pending
                                    </span>
                                @elseif($otherDelivery->status == 'delivered')
                                    <span class="badge badge-success status-badge">
                                        <i class="fas fa-check-circle"></i> Delivered
                                    </span>
                                @else
                                    <span class="badge badge-danger status-badge">
                                        <i class="fas fa-times-circle"></i> Cancelled
                                    </span>
                                @endif
                            </div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Delivered By</div>
                            <div class="info-value">
                                @if($otherDelivery->deliveredBy)
                                    <div class="delivery-person">
                                        <i class="fas fa-user"></i>
                                        {{ $otherDelivery->deliveredBy->name }}
                                    </div>
                                @else
                                    <span class="text-muted">Not assigned</span>
                                @endif
                            </div>
                        </div>
                    </div>
                    
                    @if($otherDelivery->status != 'delivered')
                        <div class="status-update-section">
                            <h5 class="section-title">Update Status</h5>
                            <form action="{{ route('other-deliveries.update-status', $otherDelivery) }}" method="POST" id="status-form">
                                @csrf
                                @method('PATCH')
                                <div class="modern-input-group">
                                    <select name="status" class="form-control modern-select" id="status-select">
                                        <option value="pending" {{ $otherDelivery->status == 'pending' ? 'selected' : '' }}>Pending</option>
                                        <option value="delivered" {{ $otherDelivery->status == 'delivered' ? 'selected' : '' }}>Delivered</option>
                                        <option value="cancelled" {{ $otherDelivery->status == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                    </select>
                                    <button type="submit" class="btn modern-btn modern-btn-primary">
                                        <i class="fas fa-save"></i> Update
                                    </button>
                                </div>
                            </form>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        
        <!-- Recipient Information -->
        <div class="info-card">
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
                    <div class="info-grid">
                        <div class="info-item">
                            <div class="info-label">Recipient Name</div>
                            <div class="info-value">{{ $otherDelivery->recipient_name }}</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Phone</div>
                            <div class="info-value">
                                @if($otherDelivery->recipient_phone)
                                    <a href="tel:{{ $otherDelivery->recipient_phone }}" class="phone-link">
                                        <i class="fas fa-phone"></i> {{ $otherDelivery->recipient_phone }}
                                    </a>
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </div>
                        </div>
                        <div class="info-item full-width">
                            <div class="info-label">Address</div>
                            <div class="info-value">{{ $otherDelivery->recipient_address }}</div>
                        </div>
                    </div>
                    
                    <div class="recipient-actions">
                        <a href="{{ route('other-deliveries.recipient-history', $otherDelivery->recipient_name) }}" 
                           class="btn modern-btn modern-btn-info btn-sm">
                            <i class="fas fa-history"></i> View All Deliveries
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Transport Information -->
    <div class="card modern-card">
        <div class="card-header modern-header">
            <div class="header-content">
                <div class="header-title">
                    <i class="fas fa-truck header-icon"></i>
                    <h3 class="card-title">Transport Information</h3>
                </div>
            </div>
        </div>
        <div class="card-body modern-card-body">
            <div class="transport-grid">
                <div class="transport-section">
                    <div class="info-grid">
                        <div class="info-item">
                            <div class="info-label">Vehicle Type</div>
                            <div class="info-value">{{ $otherDelivery->vehicle_type ?? 'N/A' }}</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Vehicle Number</div>
                            <div class="info-value">{{ $otherDelivery->vehicle_number ?? 'N/A' }}</div>
                        </div>
                    </div>
                </div>
                <div class="transport-section">
                    <div class="info-grid">
                        <div class="info-item">
                            <div class="info-label">Driver Name</div>
                            <div class="info-value">{{ $otherDelivery->driver_name ?? 'N/A' }}</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Driver Phone</div>
                            <div class="info-value">
                                @if($otherDelivery->driver_phone)
                                    <a href="tel:{{ $otherDelivery->driver_phone }}" class="phone-link">
                                        <i class="fas fa-phone"></i> {{ $otherDelivery->driver_phone }}
                                    </a>
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            @if($otherDelivery->notes)
                <div class="notes-section">
                    <h5 class="section-title">Notes</h5>
                    <div class="notes-content">
                        {{ $otherDelivery->notes }}
                    </div>
                </div>
            @endif
        </div>
    </div>
    
    <!-- Delivered Products -->
    <div class="card modern-card">
        <div class="card-header modern-header">
            <div class="header-content">
                <div class="header-title">
                    <i class="fas fa-boxes header-icon"></i>
                    <h3 class="card-title">Delivered Products</h3>
                </div>
            </div>
        </div>
        <div class="card-body modern-card-body">
            <div class="mobile-table-wrapper">
                <div class="table-responsive modern-table-responsive">
                    <table class="table modern-table" id="products-table">
                        <thead class="modern-thead">
                            <tr>
                                <th>#</th>
                                <th>Product</th>
                                <th>Description</th>
                                <th>Quantity</th>
                                <th>Cartons</th>
                                <th>Pieces</th>
                            </tr>
                        </thead>
                        <tbody class="modern-tbody">
                            @foreach($otherDelivery->items as $index => $item)
                                <tr>
                                    <td>
                                        <span class="item-number">{{ $index + 1 }}</span>
                                    </td>
                                    <td>
                                        <div class="product-info">
                                            <strong class="product-name">{{ $item->product->name }}</strong>
                                            @if($item->product->category)
                                                <br><small class="product-category">{{ $item->product->category->name }}</small>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <span class="product-description">{{ $item->description ?: 'N/A' }}</span>
                                    </td>
                                    <td>
                                        <span class="quantity-value">{{ number_format($item->quantity, 2) }}</span>
                                    </td>
                                    <td>
                                        <span class="carton-value">{{ $item->cartons ?? 'N/A' }}</span>
                                    </td>
                                    <td>
                                        <span class="pieces-value">{{ $item->pieces ?? 'N/A' }}</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="modern-tfoot">
                            <tr>
                                <th colspan="3">Total</th>
                                <th>{{ number_format($otherDelivery->items->sum('quantity'), 2) }}</th>
                                <th>{{ $otherDelivery->items->sum('cartons') ?: 'N/A' }}</th>
                                <th>{{ $otherDelivery->items->sum('pieces') ?: 'N/A' }}</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Action Buttons -->
    <div class="action-section">
        <div class="action-buttons">
            @if($otherDelivery->status != 'delivered')
                <a href="{{ route('other-deliveries.edit', $otherDelivery) }}" class="btn modern-btn modern-btn-warning btn-lg">
                    <i class="fas fa-edit"></i> Edit Delivery
                </a>
                <button type="button" class="btn modern-btn modern-btn-danger btn-lg" id="delete-delivery-btn">
                    <i class="fas fa-trash"></i> Delete Delivery
                </button>
            @endif
            <a href="{{ route('other-deliveries.print', $otherDelivery) }}" class="btn modern-btn modern-btn-info btn-lg" target="_blank">
                <i class="fas fa-print"></i> Print Challan
            </a>
            <a href="{{ route('other-deliveries.index') }}" class="btn modern-btn modern-btn-outline btn-lg">
                <i class="fas fa-list"></i> Back to List
            </a>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content modern-modal">
                <div class="modal-header modern-modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-exclamation-triangle text-warning"></i>
                        Confirm Deletion
                    </h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete delivery challan <strong>{{ $otherDelivery->challan_number }}</strong>?</p>
                    <p class="text-muted">This action cannot be undone and will restore the products to inventory.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn modern-btn modern-btn-outline" data-dismiss="modal">Cancel</button>
                    <form action="{{ route('other-deliveries.destroy', $otherDelivery) }}" method="POST" style="display: inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn modern-btn modern-btn-danger">
                            <i class="fas fa-trash"></i> Delete
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@stop

@section('additional_css')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.min.css">
    
    <style>
        /* Mobile-First Base Styles */
        .container-fluid {
            padding: 8px;
        }

        /* Summary Cards - Mobile Optimized */
        .summary-cards-container {
            display: grid;
            grid-template-columns: 1fr;
            gap: 16px;
            margin-bottom: 24px;
        }

        .summary-card {
            background: white;
            border: none;
            border-radius: 12px;
            overflow: hidden;
            transition: all 0.3s ease;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        }

        .summary-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }

        .summary-content {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            gap: 12px;
            padding: 20px 16px;
        }

        .summary-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            color: white;
            flex-shrink: 0;
        }

        .summary-details {
            flex: 1;
        }

        .summary-title {
            font-size: 12px;
            color: #6b7280;
            margin: 0 0 8px 0;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .summary-value {
            font-size: 24px;
            font-weight: 700;
            color: #1f2937;
            margin: 0 0 4px 0;
            line-height: 1;
        }

        .summary-subtitle {
            font-size: 11px;
            color: #9ca3af;
            margin: 0;
        }

        /* Status-specific colors */
        .status-card .summary-icon {
            @if($otherDelivery->status == 'pending')
                background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            @elseif($otherDelivery->status == 'delivered')
                background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            @else
                background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            @endif
        }

        .items-card .summary-icon {
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
        }

        .quantity-card .summary-icon {
            background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);
        }

        .date-card .summary-icon {
            background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
        }

        /* Main Content Grid - Mobile First */
        .main-content-grid {
            display: block;
            gap: 24px;
            margin-bottom: 24px;
        }

        .info-card {
            margin-bottom: 24px;
        }

        /* Info Grid - Mobile Optimized */
        .info-grid {
            display: block;
            gap: 16px;
            margin-bottom: 20px;
        }

        .info-item {
            display: flex;
            flex-direction: column;
            gap: 8px;
            margin-bottom: 16px;
            padding: 12px;
            background: #f8fafc;
            border-radius: 8px;
            border-left: 4px solid #6366f1;
        }

        .info-item.full-width {
            grid-column: 1 / -1;
        }

        .info-label {
            font-size: 11px;
            font-weight: 600;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 4px;
        }

        .info-value {
            font-size: 16px;
            color: #374151;
            font-weight: 600;
        }

        /* Transport Grid - Mobile */
        .transport-grid {
            display: block;
            gap: 20px;
        }

        .transport-section {
            margin-bottom: 20px;
        }

        /* Status Badge Enhancements */
        .status-badge {
            padding: 8px 16px;
            font-size: 13px;
            font-weight: 600;
            border-radius: 20px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .status-badge i {
            font-size: 12px;
        }

        /* Delivery Person Styling */
        .delivery-person {
            color: #374151;
            font-size: 16px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .delivery-person i {
            color: #6b7280;
            font-size: 14px;
        }

        /* Phone Link - Touch Friendly */
        .phone-link {
            display: inline-block;
            padding: 8px 12px;
            background: #f0f9ff;
            border-radius: 6px;
            color: #0369a1;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.2s ease;
            min-height: 44px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .phone-link:hover,
        .phone-link:focus {
            background: #e0f2fe;
            color: #0c4a6e;
            transform: translateY(-1px);
            text-decoration: none;
        }

        /* Status Update Section - Mobile */
        .status-update-section {
            margin-top: 24px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
        }

        .section-title {
            font-size: 16px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 16px;
        }

        .modern-input-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
            border-radius: 8px;
        }

        .modern-input-group .form-control,
        .modern-input-group .btn {
            width: 100%;
            border-radius: 8px;
            min-height: 44px;
        }

        /* Recipient Actions */
        .recipient-actions {
            margin-top: 20px;
            padding-top: 16px;
            border-top: 1px solid #e5e7eb;
        }

        /* Notes Section */
        .notes-section {
            margin-top: 24px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
        }

        .notes-content {
            background: #f8fafc;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 16px;
            color: #374151;
            line-height: 1.6;
            font-size: 16px;
        }

        /* Mobile-Friendly Table */
        .mobile-table-wrapper {
            display: block;
            overflow-x: auto;
            white-space: nowrap;
            -webkit-overflow-scrolling: touch;
        }

        .modern-table-responsive {
            border-radius: 8px;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            scrollbar-width: thin;
        }

        .modern-table-responsive::-webkit-scrollbar {
            height: 6px;
        }

        .modern-table-responsive::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 3px;
        }

        .modern-table-responsive::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 3px;
        }

        .modern-table {
            margin-bottom: 0;
            background: white !important;
            color: #1f2937 !important;
            width: 100%;
            min-width: 100%;
            font-size: 14px;
        }

        .modern-thead {
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%) !important;
            border-bottom: none;
        }

        .modern-thead th {
            border: none !important;
            padding: 12px 8px;
            font-weight: 600;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: white !important;
            white-space: nowrap;
            background: transparent !important;
            min-width: 100px;
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
            padding: 12px 8px;
            vertical-align: middle;
            border: none !important;
            font-size: 14px;
            color: #374151 !important;
            background: transparent !important;
            min-width: 100px;
        }

        /* Product Table Styling */
        .item-number {
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
            color: white;
            padding: 4px 8px;
            border-radius: 50%;
            font-size: 12px;
            font-weight: 600;
            min-width: 24px;
            height: 24px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .product-info {
            line-height: 1.4;
        }

        .product-name {
            color: #374151;
            font-size: 14px;
        }

        .product-category {
            color: #6b7280;
            font-size: 12px;
        }

        .product-description {
            color: #6b7280;
            font-style: italic;
        }

        .quantity-value, .carton-value, .pieces-value {
            font-weight: 600;
            color: #374151;
        }

        .modern-tfoot {
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            font-weight: 600;
        }

        .modern-tfoot th {
            border-top: 2px solid #6366f1;
            color: #374151;
            padding: 16px 8px;
        }

        /* Action Section - Mobile */
        .action-section {
            margin-top: 32px;
            text-align: center;
            padding: 24px 16px;
            background: #f8fafc;
            border-radius: 12px;
        }

        .action-buttons {
            display: flex;
            flex-direction: column;
            gap: 12px;
            align-items: center;
        }

        .action-buttons .btn {
            width: 100%;
            max-width: 300px;
            min-height: 44px;
            justify-content: center;
        }

        /* Modern Card Styles */
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
            font-size: 20px;
            color: white;
        }

        .card-title {
            color: white;
            font-weight: 600;
            margin: 0;
            font-size: 16px;
        }

        .modern-card-body {
            padding: 24px 16px;
            background: white;
        }

        /* Button Styles - Touch Friendly */
        .modern-btn {
            padding: 12px 20px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 16px;
            border: 2px solid transparent;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            cursor: pointer;
            min-height: 44px;
            touch-action: manipulation;
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

        .modern-btn-warning {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: white;
            border-color: #f59e0b;
        }

        .modern-btn-warning:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(245, 158, 11, 0.4);
            color: white;
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

        .modern-btn-secondary {
            background: linear-gradient(135deg, #6b7280 0%, #4b5563 100%);
            color: white;
            border-color: #6b7280;
        }

        .modern-btn-secondary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(107, 114, 128, 0.4);
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

        /* Header Actions - Mobile */
        .header-actions-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
            width: 100%;
        }

        .header-actions-group .btn {
            width: 100%;
            justify-content: center;
        }

        /* Alert Styles */
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

        /* Form Controls */
        .modern-input, .modern-select {
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            padding: 12px 15px;
            background: white;
            color: #374151;
            transition: all 0.2s ease;
            font-size: 16px;
            min-height: 44px;
        }

        .modern-input:focus, .modern-select:focus {
            outline: none;
            border-color: #6366f1;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
        }

        /* Modal Styling - Mobile Friendly */
        .modern-modal {
            margin: 16px;
            max-width: calc(100vw - 32px);
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
            font-size: 16px;
        }

        .modern-modal-header .close {
            color: white;
            opacity: 0.8;
            font-size: 24px;
        }

        .modern-modal-header .close:hover {
            opacity: 1;
        }

        .modal-body {
            padding: 16px;
            font-size: 16px;
            line-height: 1.5;
        }

        .modal-footer {
            padding: 16px;
            flex-direction: column;
            gap: 8px;
        }

        .modal-footer .btn {
            width: 100%;
            order: 2;
        }

        .modal-footer .modern-btn-outline {
            order: 1;
        }

        /* Loading States */
        .btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .loading-spinner {
            display: inline-block;
            width: 16px;
            height: 16px;
            border: 2px solid #ffffff;
            border-radius: 50%;
            border-top-color: transparent;
            animation: spin 1s ease-in-out infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* Button Press Effect */
        .btn-pressed {
            transform: scale(0.98);
            opacity: 0.8;
        }

        /* Scroll Indicator */
        .has-scroll::after {
            content: "← Scroll to see more →";
            display: block;
            text-align: center;
            font-size: 12px;
            color: #6b7280;
            padding: 8px;
            background: #f9fafb;
            border-top: 1px solid #e5e7eb;
        }

        /* Mobile Device Specific */
        .mobile-device .modern-card {
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .mobile-device .summary-card:active {
            transform: scale(0.98);
        }

        /* Responsive Breakpoints */
        @media (min-width: 576px) {
            .container-fluid {
                padding: 12px;
            }
            
            .summary-cards-container {
                grid-template-columns: 1fr 1fr;
            }
            
            .summary-content {
                flex-direction: row;
                text-align: left;
            }
            
            .info-grid {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 16px;
            }
            
            .info-item {
                margin-bottom: 0;
            }
            
            .header-actions-group {
                flex-direction: row;
                flex-wrap: wrap;
            }
            
            .header-actions-group .btn {
                flex: 1 1 calc(50% - 6px);
                min-width: 140px;
            }
            
            .modern-input-group {
                flex-direction: row;
            }
            
            .modern-input-group .form-control {
                width: auto;
                flex: 1;
            }
            
            .modern-input-group .btn {
                width: auto;
            }
        }

        @media (min-width: 768px) {
            .container-fluid {
                padding: 16px;
            }
            
            .summary-cards-container {
                grid-template-columns: repeat(4, 1fr);
            }
            
            .main-content-grid {
                display: grid;
                grid-template-columns: 1fr 1fr;
            }
            
            .transport-grid {
                display: grid;
                grid-template-columns: 1fr 1fr;
            }
            
            .action-buttons {
                flex-direction: row;
                justify-content: center;
                flex-wrap: wrap;
            }
            
            .action-buttons .btn {
                flex: 1 1 calc(33.333% - 8px);
                min-width: 160px;
                max-width: 200px;
            }
            
            .modal-footer {
                flex-direction: row;
                justify-content: flex-end;
            }
            
            .modal-footer .btn {
                width: auto;
                order: unset;
            }
            
            .modern-modal {
                margin: auto;
                max-width: 500px;
            }
        }

        @media (min-width: 992px) {
            .container-fluid {
                padding: 24px;
            }
            
            .header-actions-group {
                flex-wrap: nowrap;
            }
            
            .header-actions-group .btn {
                flex: none;
                min-width: 160px;
            }
        }

        /* Print Styles */
        @media print {
            .header-actions-group, .action-section, .status-update-section {
                display: none;
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
            
            .summary-card {
                break-inside: avoid;
            }
        }
    </style>
@stop

@section('additional_js')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"></script>
    
    <script>
        $(document).ready(function() {
            // Detect mobile device
            const isMobile = window.innerWidth <= 768;
            
            // Configure toastr
            toastr.options = {
                "closeButton": true,
                "newestOnTop": true,
                "progressBar": true,
                "positionClass": "toast-top-right",
                "timeOut": "3000"
            };

            // Mobile-specific optimizations
            if (isMobile) {
                // Add mobile class to body
                $('body').addClass('mobile-device');
                
                // Optimize table scrolling
                $('.modern-table-responsive').on('scroll', function() {
                    const scrollLeft = $(this).scrollLeft();
                    const maxScroll = $(this)[0].scrollWidth - $(this)[0].clientWidth;
                    
                    if (scrollLeft > 0) {
                        $(this).addClass('scrolled-left');
                    } else {
                        $(this).removeClass('scrolled-left');
                    }
                    
                    if (scrollLeft >= maxScroll - 1) {
                        $(this).addClass('scrolled-right');
                    } else {
                        $(this).removeClass('scrolled-right');
                    }
                });
                
                // Touch-friendly modal handling
                $('.modal').on('show.bs.modal', function() {
                    $('body').css('overflow', 'hidden');
                }).on('hidden.bs.modal', function() {
                    $('body').css('overflow', 'auto');
                });
                
                // Improve button feedback
                $('.modern-btn').on('touchstart', function() {
                    $(this).addClass('btn-pressed');
                }).on('touchend touchcancel', function() {
                    $(this).removeClass('btn-pressed');
                });
            }
            
            // Responsive table handling
            function handleTableResponsiveness() {
                const tables = $('.modern-table');
                
                tables.each(function() {
                    const table = $(this);
                    const wrapper = table.closest('.modern-table-responsive');
                    
                    if (table[0].scrollWidth > wrapper.width()) {
                        wrapper.addClass('has-scroll');
                    } else {
                        wrapper.removeClass('has-scroll');
                    }
                });
            }
            
            // Call on load and resize
            handleTableResponsiveness();
            $(window).on('resize', handleTableResponsiveness);

            // Delete delivery button
            $('#delete-delivery-btn').on('click', function() {
                $('#deleteModal').modal('show');
            });

            // Status form submission with mobile-friendly feedback
            $('#status-form').on('submit', function(e) {
                const selectedStatus = $('#status-select').val();
                const currentStatus = '{{ $otherDelivery->status }}';
                
                if (selectedStatus === currentStatus) {
                    e.preventDefault();
                    toastr.info('Status is already set to ' + selectedStatus);
                    return false;
                }
                
                // Show loading state
                const submitBtn = $(this).find('button[type="submit"]');
                const originalText = submitBtn.html();
                submitBtn.data('original-text', originalText);
                submitBtn.prop('disabled', true).html('<span class="loading-spinner"></span> Updating...');
                
                // Re-enable after 5 seconds as fallback
                setTimeout(() => {
                    submitBtn.prop('disabled', false);
                    submitBtn.html(originalText);
                }, 5000);
                
                return true;
            });

            // Mobile-friendly form validation
            $('form').on('submit', function() {
                const submitBtn = $(this).find('button[type="submit"]');
                
                if (submitBtn.length && !submitBtn.prop('disabled')) {
                    const originalText = submitBtn.html();
                    submitBtn.data('original-text', originalText);
                    submitBtn.prop('disabled', true);
                    submitBtn.html('<span class="loading-spinner"></span> Processing...');
                    
                    // Re-enable after 5 seconds as fallback
                    setTimeout(() => {
                        submitBtn.prop('disabled', false);
                        submitBtn.html(originalText);
                    }, 5000);
                }
            });

            // Auto-hide alerts after 5 seconds
            setTimeout(function() {
                $('.alert').fadeOut('slow');
            }, 5000);

            // Phone number click handling
            $('.phone-link').on('click', function(e) {
                const phoneNumber = $(this).text().trim();
                if (phoneNumber && phoneNumber !== 'N/A') {
                    console.log('Calling:', phoneNumber);
                }
            });

            // Summary card interaction feedback
            $('.summary-card').on('click', function() {
                $(this).addClass('summary-card-clicked');
                setTimeout(() => {
                    $(this).removeClass('summary-card-clicked');
                }, 200);
            });
        });
    </script>
@stop
