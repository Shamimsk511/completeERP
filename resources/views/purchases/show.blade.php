@extends('layouts.modern-admin')

@section('title', 'Purchase Details')

@section('page_title', 'Purchase Order Details')

@section('header_actions')
    <div class="header-actions-group">
        <a href="{{ route('purchases.edit', $purchase) }}" class="btn modern-btn modern-btn-warning"
           onclick="handleMobileAction(event, 'edit', {{ $purchase->id }})">
            <i class="fas fa-edit"></i> <span class="btn-text">Edit Purchase</span>
        </a>
        <a href="{{ route('purchases.index') }}" class="btn modern-btn modern-btn-secondary"
           onclick="handleMobileAction(event, 'back', null)">
            <i class="fas fa-arrow-left"></i> <span class="btn-text">Back to List</span>
        </a>
    </div>
@stop

@section('page_content')
    <!-- Statistics Cards Row - Mobile Optimized -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6">
            <div class="card modern-card stat-card purchase-stat">
                <div class="card-body modern-card-body">
                    <div class="stat-content">
                        <div class="stat-icon">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                        <div class="stat-info">
                            <h3 class="stat-number">#{{ $purchase->id }}</h3>
                            <p class="stat-label">Purchase ID</p>
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
                            <h3 class="stat-number">৳{{ number_format($purchase->total_amount, 2) }}</h3>
                            <p class="stat-label">Total Amount</p>
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
                            <h3 class="stat-number">{{ $purchase->items->count() }}</h3>
                            <p class="stat-label">Items</p>
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
                            <h3 class="stat-number">{{ $purchase->purchase_date->format('d M') }}</h3>
                            <p class="stat-label">Purchase Date</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Purchase Information Card - Mobile Optimized -->
    <div class="card modern-card mb-4">
        <div class="card-header modern-header info-header collapsible-header" data-toggle="collapse" data-target="#purchase-info">
            <div class="header-content">
                <div class="header-title">
                    <i class="fas fa-info-circle header-icon"></i>
                    <h3 class="card-title">Purchase Information</h3>
                </div>
                <i class="fas fa-chevron-down collapse-icon"></i>
            </div>
        </div>
        <div id="purchase-info" class="collapse show">
            <div class="card-body modern-card-body">
                <!-- Mobile View Information Cards -->
                <div class="mobile-info-container" id="mobile-info">
                    <div class="mobile-info-card">
                        <div class="info-header-mobile">
                            <div class="info-icon-mobile">
                                <i class="fas fa-building"></i>
                            </div>
                            <div class="info-title-mobile">Supplier Information</div>
                        </div>
                        <div class="info-content-mobile">
                            <div class="info-item-mobile">
                                <span class="info-label-mobile">Company:</span>
                                <span class="info-value-mobile">{{ $purchase->company->name }}</span>
                            </div>
                            @if($purchase->company->phone)
                                <div class="info-item-mobile">
                                    <span class="info-label-mobile">Phone:</span>
                                    <span class="info-value-mobile">{{ $purchase->company->phone }}</span>
                                </div>
                            @endif
                            @if($purchase->company->email)
                                <div class="info-item-mobile">
                                    <span class="info-label-mobile">Email:</span>
                                    <span class="info-value-mobile">{{ $purchase->company->email }}</span>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="mobile-info-card">
                        <div class="info-header-mobile">
                            <div class="info-icon-mobile">
                                <i class="fas fa-file-invoice"></i>
                            </div>
                            <div class="info-title-mobile">Invoice Details</div>
                        </div>
                        <div class="info-content-mobile">
                            <div class="info-item-mobile">
                                <span class="info-label-mobile">Invoice No:</span>
                                <span class="info-value-mobile">
                                    @if($purchase->invoice_no)
                                        <span class="invoice-badge-mobile">{{ $purchase->invoice_no }}</span>
                                    @else
                                        <span class="no-invoice-badge-mobile">N/A</span>
                                    @endif
                                </span>
                            </div>
                            <div class="info-item-mobile">
                                <span class="info-label-mobile">Purchase Date:</span>
                                <span class="info-value-mobile">{{ $purchase->purchase_date->format('d M, Y') }}</span>
                            </div>
                            <div class="info-item-mobile">
                                <span class="info-label-mobile">Total Amount:</span>
                                <span class="info-value-mobile">
                                    <span class="amount-badge-mobile">৳{{ number_format($purchase->total_amount, 2) }}</span>
                                </span>
                            </div>
                        </div>
                    </div>

                    @if($purchase->notes)
                        <div class="mobile-info-card">
                            <div class="info-header-mobile">
                                <div class="info-icon-mobile">
                                    <i class="fas fa-sticky-note"></i>
                                </div>
                                <div class="info-title-mobile">Notes</div>
                            </div>
                            <div class="info-content-mobile">
                                <div class="notes-content-mobile">{{ $purchase->notes }}</div>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Desktop View Information Grid -->
                <div class="desktop-info-container">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="info-grid">
                                <div class="info-item">
                                    <div class="info-label">
                                        <i class="fas fa-calendar-alt"></i> Purchase Date
                                    </div>
                                    <div class="info-value">
                                        {{ $purchase->purchase_date->format('d M, Y') }}
                                        <small class="text-muted d-block">{{ $purchase->purchase_date->diffForHumans() }}</small>
                                    </div>
                                </div>
                                
                                <div class="info-item">
                                    <div class="info-label">
                                        <i class="fas fa-file-invoice"></i> Invoice Number
                                    </div>
                                    <div class="info-value">
                                        @if($purchase->invoice_no)
                                            <span class="invoice-badge">{{ $purchase->invoice_no }}</span>
                                        @else
                                            <span class="no-invoice-badge">N/A</span>
                                        @endif
                                    </div>
                                </div>
                                
                                <div class="info-item">
                                    <div class="info-label">
                                        <i class="fas fa-building"></i> Supplier
                                    </div>
                                    <div class="info-value">
                                        <strong>{{ $purchase->company->name }}</strong>
                                        @if($purchase->company->phone)
                                            <small class="text-muted d-block">
                                                <i class="fas fa-phone"></i> {{ $purchase->company->phone }}
                                            </small>
                                        @endif
                                        @if($purchase->company->email)
                                            <small class="text-muted d-block">
                                                <i class="fas fa-envelope"></i> {{ $purchase->company->email }}
                                            </small>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="info-grid">
                                <div class="info-item">
                                    <div class="info-label">
                                        <i class="fas fa-dollar-sign"></i> Total Amount
                                    </div>
                                    <div class="info-value">
                                        <span class="amount-badge">৳{{ number_format($purchase->total_amount, 2) }}</span>
                                    </div>
                                </div>
                                
                                <div class="info-item">
                                    <div class="info-label">
                                        <i class="fas fa-clock"></i> Created At
                                    </div>
                                    <div class="info-value">
                                        {{ $purchase->created_at->format('d M, Y H:i') }}
                                        <small class="text-muted d-block">{{ $purchase->created_at->diffForHumans() }}</small>
                                    </div>
                                </div>
                                
                                <div class="info-item">
                                    <div class="info-label">
                                        <i class="fas fa-sticky-note"></i> Notes
                                    </div>
                                    <div class="info-value">
                                        @if($purchase->notes)
                                            <div class="notes-content">{{ $purchase->notes }}</div>
                                        @else
                                            <span class="text-muted">No notes available</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Purchase Items Section - Mobile Optimized -->
    <div class="card modern-card">
        <div class="card-header modern-header items-header">
            <div class="header-content">
                <div class="header-title">
                    <i class="fas fa-boxes header-icon"></i>
                    <h3 class="card-title">Purchase Items</h3>
                </div>
                <div class="card-tools">
                    <span class="items-count-badge">{{ $purchase->items->count() }} Items</span>
                </div>
            </div>
        </div>
        <div class="card-body modern-card-body">
            <!-- Mobile View Item Cards -->
            <div class="mobile-items-container" id="mobile-items">
                @foreach($purchase->items as $index => $item)
                    <div class="mobile-item-card" data-item-id="{{ $item->id }}">
                        <div class="item-header-mobile">
                            <div class="item-number-mobile">
                                <span class="item-badge-mobile">#{{ $index + 1 }}</span>
                            </div>
                            <div class="item-category-mobile">
                                <span class="category-badge-mobile">{{ $item->product->category->name }}</span>
                            </div>
                        </div>
                        
                        <div class="item-content-mobile">
                            <div class="product-info-mobile">
                                <div class="product-name-mobile">
                                    <strong>{{ $item->product->name }}</strong>
                                </div>
                                @if($item->product->description)
                                    <div class="product-desc-mobile">
                                        {{ Str::limit($item->product->description, 60) }}
                                    </div>
                                @endif
                            </div>
                            
                            <div class="item-details-mobile">
                                <div class="detail-row-mobile">
                                    <div class="detail-item-mobile">
                                        <span class="detail-label-mobile">Quantity:</span>
                                        <span class="quantity-badge-mobile">{{ number_format($item->quantity, 2) }}</span>
                                    </div>
                                    <div class="detail-item-mobile">
                                        <span class="detail-label-mobile">Unit Price:</span>
                                        <span class="price-text-mobile">৳{{ number_format($item->purchase_price, 2) }}</span>
                                    </div>
                                </div>
                                
                                <div class="detail-row-mobile">
                                    <div class="detail-item-mobile">
                                        <span class="detail-label-mobile">Total:</span>
                                        <span class="total-price-badge-mobile">৳{{ number_format($item->total_price, 2) }}</span>
                                    </div>
                                    <div class="detail-item-mobile">
                                        <span class="detail-label-mobile">Margin:</span>
                                        @php
                                            $margin = $item->product->sale_price - $item->purchase_price;
                                            $marginPercent = $item->purchase_price > 0 ? ($margin / $item->purchase_price) * 100 : 0;
                                        @endphp
                                        <span class="margin-badge-mobile {{ $margin > 0 ? 'positive' : 'negative' }}">
                                            {{ number_format($marginPercent, 1) }}%
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
                
                <!-- Mobile Total Summary -->
                <div class="mobile-total-card">
                    <div class="total-header-mobile">
                        <i class="fas fa-calculator"></i>
                        <span>Purchase Summary</span>
                    </div>
                    <div class="total-content-mobile">
                        <div class="total-item-mobile">
                            <span class="total-label-mobile">Total Items:</span>
                            <span class="total-value-mobile">{{ $purchase->items->count() }}</span>
                        </div>
                        <div class="total-item-mobile grand-total-mobile">
                            <span class="total-label-mobile">Grand Total:</span>
                            <span class="grand-total-amount-mobile">৳{{ number_format($purchase->total_amount, 2) }}</span>
                        </div>
                        <div class="total-item-mobile">
                            <span class="total-label-mobile">Overall Margin:</span>
                            @php
                                $totalMargin = $purchase->items->sum(function($item) {
                                    return ($item->product->sale_price - $item->purchase_price) * $item->quantity;
                                });
                                $totalMarginPercent = $purchase->total_amount > 0 ? ($totalMargin / $purchase->total_amount) * 100 : 0;
                            @endphp
                            <span class="total-margin-badge-mobile {{ $totalMargin > 0 ? 'positive' : 'negative' }}">
                                {{ number_format($totalMarginPercent, 1) }}%
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Desktop View Table -->
            <div class="desktop-items-container">
                <div class="table-container">
                    <div class="table-responsive modern-table-responsive">
                        <table class="table modern-table">
                            <thead class="modern-thead">
                                <tr>
                                    <th width="8%">
                                        <div class="th-content">
                                            <i class="fas fa-hashtag"></i>
                                            <span>#</span>
                                        </div>
                                    </th>
                                    <th width="25%">
                                        <div class="th-content">
                                            <i class="fas fa-box"></i>
                                            <span>Product</span>
                                        </div>
                                    </th>
                                    <th width="15%">
                                        <div class="th-content">
                                            <i class="fas fa-tags"></i>
                                            <span>Category</span>
                                        </div>
                                    </th>
                                    <th width="12%">
                                        <div class="th-content">
                                            <i class="fas fa-sort-numeric-up"></i>
                                            <span>Quantity</span>
                                        </div>
                                    </th>
                                    <th width="15%">
                                        <div class="th-content">
                                            <i class="fas fa-dollar-sign"></i>
                                            <span>Unit Price</span>
                                        </div>
                                    </th>
                                    <th width="15%">
                                        <div class="th-content">
                                            <i class="fas fa-calculator"></i>
                                            <span>Total Price</span>
                                        </div>
                                    </th>
                                    <th width="10%">
                                        <div class="th-content">
                                            <i class="fas fa-chart-line"></i>
                                            <span>Margin</span>
                                        </div>
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="modern-tbody">
                                @foreach($purchase->items as $index => $item)
                                    <tr>
                                        <td>
                                            <span class="item-number">{{ $index + 1 }}</span>
                                        </td>
                                        <td>
                                            <div class="product-info">
                                                <strong>{{ $item->product->name }}</strong>
                                                @if($item->product->description)
                                                    <small class="text-muted d-block">{{ Str::limit($item->product->description, 50) }}</small>
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            <span class="category-badge">{{ $item->product->category->name }}</span>
                                        </td>
                                        <td>
                                            <span class="quantity-badge">{{ number_format($item->quantity, 2) }}</span>
                                        </td>
                                        <td>
                                            <span class="price-text">৳{{ number_format($item->purchase_price, 2) }}</span>
                                        </td>
                                        <td>
                                            <span class="total-price-badge">৳{{ number_format($item->total_price, 2) }}</span>
                                        </td>
                                        <td>
                                            @php
                                                $margin = $item->product->sale_price - $item->purchase_price;
                                                $marginPercent = $item->purchase_price > 0 ? ($margin / $item->purchase_price) * 100 : 0;
                                            @endphp
                                            <span class="margin-badge {{ $margin > 0 ? 'positive' : 'negative' }}">
                                                {{ number_format($marginPercent, 1) }}%
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="modern-tfoot">
                                <tr class="total-row">
                                    <td colspan="5" class="text-right">
                                        <strong class="grand-total-label">
                                            <i class="fas fa-calculator"></i> Grand Total:
                                        </strong>
                                    </td>
                                    <td>
                                        <span class="grand-total-amount">৳{{ number_format($purchase->total_amount, 2) }}</span>
                                    </td>
                                    <td>
                                        @php
                                            $totalMargin = $purchase->items->sum(function($item) {
                                                return ($item->product->sale_price - $item->purchase_price) * $item->quantity;
                                            });
                                            $totalMarginPercent = $purchase->total_amount > 0 ? ($totalMargin / $purchase->total_amount) * 100 : 0;
                                        @endphp
                                        <span class="total-margin-badge {{ $totalMargin > 0 ? 'positive' : 'negative' }}">
                                            {{ number_format($totalMarginPercent, 1) }}%
                                        </span>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('additional_css')
    <style>
        /* Inherit all base styles from index view */
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

        /* Section-specific header colors */
        .purchase-stat .stat-icon {
            background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
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

        .info-header {
            background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);
        }

        .items-header {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
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

        /* Desktop specific styles */
        @media (min-width: 769px) {
            /* Hide mobile elements on desktop */
            .mobile-info-container,
            .mobile-items-container {
                display: none !important;
            }

            /* Show desktop elements */
            .desktop-info-container,
            .desktop-items-container {
                display: block !important;
            }

            .btn-text {
                display: inline;
            }
        }

        /* Mobile Responsive */
        @media (max-width: 768px) {
            /* Hide desktop elements on mobile */
            .desktop-info-container,
            .desktop-items-container {
                display: none !important;
            }

            /* Show mobile elements */
            .mobile-info-container,
            .mobile-items-container {
                display: block !important;
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

            /* Card adjustments */
            .modern-card {
                margin-bottom: 16px;
                box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            }

            .modern-card-body {
                padding: 16px;
            }
        }

        /* Mobile Information Cards */
        .mobile-info-card {
            background: #f8fafc;
            border-radius: 12px;
            padding: 16px;
            margin-bottom: 16px;
            border: 2px solid #e5e7eb;
            transition: all 0.3s ease;
        }

        .mobile-info-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(99, 102, 241, 0.15);
            border-color: #6366f1;
        }

        .info-header-mobile {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 16px;
            padding-bottom: 12px;
            border-bottom: 2px solid #e5e7eb;
        }

        .info-icon-mobile {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 16px;
        }

        .info-title-mobile {
            font-weight: 600;
            color: #374151;
            font-size: 16px;
        }

        .info-content-mobile {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .info-item-mobile {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 0;
        }

        .info-label-mobile {
            font-weight: 500;
            color: #6b7280;
            font-size: 14px;
        }

        .info-value-mobile {
            font-weight: 600;
            color: #374151;
            font-size: 14px;
            text-align: right;
        }

        .notes-content-mobile {
            background: #f1f5f9;
            padding: 12px;
            border-radius: 8px;
            border-left: 3px solid #6366f1;
            font-style: italic;
            color: #4b5563;
            font-size: 14px;
            line-height: 1.5;
        }

        /* Mobile Item Cards */
        .mobile-item-card {
            background: #f8fafc;
            border-radius: 12px;
            padding: 16px;
            margin-bottom: 16px;
            border: 2px solid #e5e7eb;
            transition: all 0.3s ease;
        }

        .mobile-item-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(99, 102, 241, 0.15);
            border-color: #6366f1;
        }

        .item-header-mobile {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 16px;
        }

        .item-badge-mobile {
            background: linear-gradient(135deg, #6b7280 0%, #4b5563 100%);
            color: white;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
        }

        .category-badge-mobile {
            background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
            color: white;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
        }

        .product-info-mobile {
            margin-bottom: 16px;
        }

        .product-name-mobile strong {
            color: #374151;
            font-size: 16px;
            line-height: 1.4;
        }

        .product-desc-mobile {
            color: #6b7280;
            font-size: 13px;
            margin-top: 4px;
            line-height: 1.4;
        }

        .item-details-mobile {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .detail-row-mobile {
            display: flex;
            justify-content: space-between;
            gap: 16px;
        }

        .detail-item-mobile {
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .detail-label-mobile {
            font-size: 12px;
            color: #6b7280;
            font-weight: 500;
        }

        .quantity-badge-mobile {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: white;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
            display: inline-block;
        }

        .price-text-mobile {
            font-weight: 600;
            color: #374151;
            font-size: 13px;
        }

        .total-price-badge-mobile {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
            display: inline-block;
        }

        .margin-badge-mobile {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
            display: inline-block;
        }

        .margin-badge-mobile.positive {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
        }

        .margin-badge-mobile.negative {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: white;
        }

        /* Mobile Total Card */
        .mobile-total-card {
            background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
            border: 2px solid #10b981;
            border-radius: 12px;
            padding: 20px;
            margin-top: 20px;
        }

        .total-header-mobile {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 16px;
            font-weight: 600;
            color: #059669;
            font-size: 16px;
        }

        .total-content-mobile {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .total-item-mobile {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 0;
        }

        .grand-total-mobile {
            border-top: 2px solid #10b981;
            padding-top: 12px;
            margin-top: 8px;
        }

        .total-label-mobile {
            font-weight: 500;
            color: #059669;
            font-size: 14px;
        }

        .total-value-mobile {
            font-weight: 600;
            color: #059669;
            font-size: 14px;
        }

        .grand-total-amount-mobile {
            font-size: 18px;
            font-weight: 700;
            color: #059669;
            background: white;
            padding: 6px 12px;
            border-radius: 8px;
        }

        .total-margin-badge-mobile {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
        }

        .total-margin-badge-mobile.positive {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
        }

        .total-margin-badge-mobile.negative {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: white;
        }

        /* Badge styles for mobile */
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

        .amount-badge-mobile {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            padding: 6px 12px;
            border-radius: 15px;
            font-size: 14px;
            font-weight: 700;
        }

        /* Desktop styles - inherit from index view */
        .info-grid {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .info-item {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            padding: 16px 0;
            border-bottom: 1px solid #f1f5f9;
        }

        .info-item:last-child {
            border-bottom: none;
        }

        .info-label {
            font-weight: 600;
            color: #6b7280;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
            min-width: 140px;
        }

        .info-value {
            text-align: right;
            color: #374151;
            font-size: 14px;
            flex: 1;
        }

        .info-value strong {
            color: #1f2937;
            font-size: 16px;
        }

        /* Desktop badges */
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
            font-size: 16px;
            font-weight: 700;
        }

        .notes-content {
            background: #f8fafc;
            padding: 8px 12px;
            border-radius: 6px;
            border-left: 3px solid #6366f1;
            font-style: italic;
            color: #4b5563;
        }

        /* Desktop table styles - inherit from index view */
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

        .modern-tfoot {
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%) !important;
        }

        .modern-tfoot td {
            padding: 16px 12px;
            border: none !important;
            font-weight: 600;
            background: transparent !important;
        }

        .total-row {
            border-top: 2px solid #e5e7eb !important;
        }

        .grand-total-label {
            font-size: 16px;
            color: #374151;
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 8px;
        }

        .grand-total-amount {
            font-size: 18px;
            font-weight: 700;
            color: #059669;
            background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
            padding: 8px 12px;
            border-radius: 8px;
            display: inline-block;
        }

        /* All other badge styles from index view */
        .item-number {
            display: inline-block;
            background: linear-gradient(135deg, #6b7280 0%, #4b5563 100%);
            color: white;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
        }

        .category-badge {
            display: inline-block;
            background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
            color: white;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
        }

        .quantity-badge {
            display: inline-block;
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: white;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
        }

        .total-price-badge {
            display: inline-block;
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
        }

        .margin-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
        }

        .margin-badge.positive {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
        }

        .margin-badge.negative {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: white;
        }

        .total-margin-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: 700;
        }

        .total-margin-badge.positive {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
        }

        .total-margin-badge.negative {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: white;
        }

        .items-count-badge {
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.2) 0%, rgba(255, 255, 255, 0.1) 100%);
            color: white;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
        }

        .product-info strong {
            color: #374151;
            font-size: 14px;
        }

        .price-text {
            font-weight: 600;
            color: #374151;
        }

        /* Button styles */
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

        .header-actions-group {
            display: flex;
            gap: 12px;
            align-items: center;
        }

        /* Mobile-specific improvements */
        @media (max-width: 480px) {
            .mobile-info-card,
            .mobile-item-card {
                padding: 12px;
                margin-bottom: 12px;
            }

            .stat-icon {
                width: 40px;
                height: 40px;
                font-size: 18px;
            }

            .stat-number {
                font-size: 20px;
            }

            .info-icon-mobile {
                width: 35px;
                height: 35px;
                font-size: 14px;
            }

            .info-title-mobile {
                font-size: 14px;
            }
        }

        /* Animation for cards */
        .mobile-info-card,
        .mobile-item-card {
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
    </style>
@stop

@section('additional_js')
    <script>
        $(document).ready(function() {
            // Better mobile detection
            function isMobileView() {
                return window.innerWidth <= 768;
            }
            
            // Initialize mobile optimizations
            const PurchaseShow = {
                init: function() {
                    this.initMobileOptimizations();
                    console.log('Purchase show page initialized with mobile optimizations');
                },

                initMobileOptimizations: function() {
                    // Touch-friendly interactions
                    this.initTouchOptimizations();
                },

                initTouchOptimizations: function() {
                    // Add touch feedback to buttons
                    $('.modern-btn').on('touchstart', function() {
                        $(this).addClass('touching');
                    }).on('touchend touchcancel', function() {
                        const self = $(this);
                        setTimeout(() => {
                            self.removeClass('touching');
                        }, 150);
                    });
                }
            };

            // Initialize functionality
            PurchaseShow.init();
            
            // Handle mobile actions
            window.handleMobileAction = function(event, action, purchaseId) {
                const button = $(event.currentTarget);
                button.addClass('loading');
                
                setTimeout(() => {
                    switch(action) {
                        case 'edit':
                            window.location.href = `/purchases/${purchaseId}/edit`;
                            break;
                        case 'back':
                            window.location.href = "{{ route('purchases.index') }}";
                            break;
                        default:
                            button.removeClass('loading');
                    }
                }, 200);
                
                event.preventDefault();
            };
            
            // Handle window resize for responsive adjustments
            $(window).on('resize', function() {
                setTimeout(function() {
                    if (isMobileView()) {
                        $('.mobile-info-container, .mobile-items-container').show();
                        $('.desktop-info-container, .desktop-items-container').hide();
                    } else {
                        $('.mobile-info-container, .mobile-items-container').hide();
                        $('.desktop-info-container, .desktop-items-container').show();
                    }
                }, 100);
            });
        });

        /* Enhanced mobile button interactions */
        .modern-btn:active {
            transform: scale(0.98);
        }

        /* Better touch feedback */
        .touching {
            background: rgba(99, 102, 241, 0.1) !important;
            transform: scale(0.98);
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

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }
    </script>
@stop
