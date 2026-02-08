@extends('layouts.modern-admin')

@section('title', 'Product Details')

@section('page_title', 'Product Details')

@section('header_actions')
    <a href="{{ route('products.index') }}" class="btn modern-btn modern-btn-outline">
        <i class="fas fa-arrow-left"></i> Back to List
    </a>
    <a href="{{ route('products.edit', $product) }}" class="btn modern-btn modern-btn-warning">
        <i class="fas fa-edit"></i> Edit Product
    </a>
@stop

@section('page_content')
    <!-- Main Product Card -->
    <div class="card modern-card">
        <div class="card-header modern-header">
            <div class="header-content">
                <div class="header-title">
                    <i class="fas fa-box header-icon"></i>
                    <h3 class="card-title">{{ $product->name }}</h3>
                </div>
                <div class="header-badge">
                    <span class="badge modern-badge">
                        @if($product->is_stock_managed)
                            Stock: {{ number_format($product->current_stock, 2) }}
                        @else
                            Service Item
                        @endif
                    </span>
                </div>
            </div>
        </div>
        
        <div class="card-body modern-card-body">
            <div class="details-grid">
                <!-- Product Information Section -->
                <div class="details-section">
                    <div class="section-header">
                        <h4 class="section-title">
                            <i class="fas fa-info-circle"></i>
                            Product Information
                        </h4>
                    </div>
                    
                    <div class="details-content">
                        <div class="detail-item">
                            <div class="detail-label">
                                <i class="fas fa-box"></i>
                                Product Name
                            </div>
                            <div class="detail-value">{{ $product->name }}</div>
                        </div>
                        
                        <div class="detail-item">
                            <div class="detail-label">
                                <i class="fas fa-align-left"></i>
                                Description
                            </div>
                            <div class="detail-value">
                                @if($product->description)
                                    {{ $product->description }}
                                @else
                                    <span class="text-muted">No description provided</span>
                                @endif
                            </div>
                        </div>
                        
                        <div class="detail-item">
                            <div class="detail-label">
                                <i class="fas fa-building"></i>
                                Company
                            </div>
                            <div class="detail-value">
                                <span class="company-name">{{ $product->company?->name ?? 'N/A' }}</span>
                            </div>
                        </div>

                        <div class="detail-item">
                            <div class="detail-label">
                                <i class="fas fa-tags"></i>
                                Category
                            </div>
                            <div class="detail-value">
                                <span class="category-name">{{ $product->category?->name ?? 'N/A' }}</span>
                                @if($product->category)
                                <div class="category-specs">
                                    <span class="spec-item">
                                        <i class="fas fa-cube"></i>
                                        {{ $product->category->box_pcs }} pcs/box
                                    </span>
                                    <span class="spec-item">
                                        <i class="fas fa-ruler"></i>
                                        {{ $product->category->pieces_feet }} pcs/feet
                                    </span>
                                </div>
                                @endif
                            </div>
                        </div>

                        @if(\App\Models\ErpFeatureSetting::isEnabled('product_weight'))
                            @php
                                $weightValue = $product->weight_value;
                                $weightUnit = $product->weight_unit;
                                if ((empty($weightValue) || empty($weightUnit)) && $product->category) {
                                    $weightValue = $product->category->weight_value;
                                    $weightUnit = $product->category->weight_unit;
                                }
                                $weightUnitLabels = [
                                    'per_piece' => 'Per Piece',
                                    'per_box' => 'Per Box',
                                    'per_unit' => 'Per Unit',
                                ];
                                $weightLabel = $weightUnit ? ($weightUnitLabels[$weightUnit] ?? ucwords(str_replace('_', ' ', $weightUnit))) : null;
                            @endphp
                            <div class="detail-item">
                                <div class="detail-label">
                                    <i class="fas fa-weight-hanging"></i>
                                    Weight
                                </div>
                                <div class="detail-value">
                                    @if(!empty($weightValue) && !empty($weightLabel))
                                        {{ number_format($weightValue, 3) }} ({{ $weightLabel }})
                                    @else
                                        <span class="text-muted">Not set</span>
                                    @endif
                                </div>
                            </div>
                        @endif

                        @if(\App\Models\ErpFeatureSetting::isEnabled('godown_management'))
                            <div class="detail-item">
                                <div class="detail-label">
                                    <i class="fas fa-warehouse"></i>
                                    Default Godown
                                </div>
                                <div class="detail-value">
                                    @if($product->defaultGodown)
                                        <div class="godown-display">
                                            <span class="godown-name">{{ $product->defaultGodown->name }}</span>
                                            <span class="godown-location">
                                                {{ $product->defaultGodown->location ?: 'No location' }}
                                            </span>
                                        </div>
                                    @else
                                        <span class="text-muted">Not assigned</span>
                                    @endif
                                </div>
                            </div>
                        @endif
                        
                        <div class="detail-item">
                            <div class="detail-label">
                                <i class="fas fa-warehouse"></i>
                                Stock Management
                            </div>
                            <div class="detail-value">
                                @if($product->is_stock_managed)
                                    <span class="status-badge status-enabled">
                                        <i class="fas fa-check-circle"></i>
                                        Enabled
                                    </span>
                                @else
                                    <span class="status-badge status-disabled">
                                        <i class="fas fa-times-circle"></i>
                                        Disabled
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Stock & Pricing Section -->
                <div class="details-section">
                    <div class="section-header">
                        <h4 class="section-title">
                            <i class="fas fa-chart-line"></i>
                            Stock & Pricing
                        </h4>
                    </div>
                    
                    <div class="metrics-grid">
                        @if($product->is_stock_managed)
                            <div class="metric-card metric-primary">
                                <div class="metric-icon">
                                    <i class="fas fa-boxes"></i>
                                </div>
                                <div class="metric-content">
                                    <div class="metric-label">Opening Stock</div>
                                    <div class="metric-value">{{ number_format($product->opening_stock, 2) }}</div>
                                    <div class="metric-unit">units</div>
                                </div>
                            </div>
                            
                            <div class="metric-card metric-success">
                                <div class="metric-icon">
                                    <i class="fas fa-warehouse"></i>
                                </div>
                                <div class="metric-content">
                                    <div class="metric-label">Current Stock</div>
                                    <div class="metric-value">{{ number_format($product->current_stock, 2) }}</div>
                                    <div class="metric-unit">units</div>
                                </div>
                                @if($product->current_stock <= 10)
                                    <div class="metric-alert">
                                        <i class="fas fa-exclamation-triangle"></i>
                                        Low Stock
                                    </div>
                                @endif
                            </div>
                        @endif
                        
                        <div class="metric-card metric-warning">
                            <div class="metric-icon">
                                <i class="fas fa-shopping-cart"></i>
                            </div>
                            <div class="metric-content">
                                <div class="metric-label">Purchase Price</div>
                                <div class="metric-value">৳{{ number_format($product->purchase_price, 2) }}</div>
                                <div class="metric-unit">per unit</div>
                            </div>
                        </div>
                        
                        <div class="metric-card metric-info">
                            <div class="metric-icon">
                                <i class="fas fa-tag"></i>
                            </div>
                            <div class="metric-content">
                                <div class="metric-label">Sale Price</div>
                                <div class="metric-value">৳{{ number_format($product->sale_price, 2) }}</div>
                                <div class="metric-unit">per unit</div>
                            </div>
                        </div>
                    </div>

                    @if(\App\Models\ErpFeatureSetting::isEnabled('godown_management'))
                        <div class="godown-stock-section">
                            <div class="godown-stock-header">
                                <i class="fas fa-warehouse"></i>
                                Godown Stock
                            </div>
                            @if(!$product->is_stock_managed)
                                <div class="text-muted">Stock tracking is disabled for this product.</div>
                            @else
                                <div class="godown-stock-grid">
                                    @forelse($product->godownStocks as $stock)
                                        <div class="godown-stock-card">
                                            <div class="godown-stock-name">{{ $stock->godown?->name ?? 'Unknown Godown' }}</div>
                                            <div class="godown-stock-location">{{ $stock->godown?->location ?: 'No location' }}</div>
                                            <div class="godown-stock-qty">Qty: {{ number_format($stock->quantity ?? 0, 2) }}</div>
                                        </div>
                                    @empty
                                        <div class="text-muted">No godown stock records found.</div>
                                    @endforelse
                                </div>
                            @endif
                        </div>
                    @endif
                </div>

                <!-- Profit Analysis Section -->
                <div class="details-section full-width">
                    <div class="section-header">
                        <h4 class="section-title">
                            <i class="fas fa-analytics"></i>
                            Profit Analysis
                        </h4>
                    </div>
                    
                    <div class="profit-analysis">
                        @php
                            $profitAmount = $product->sale_price - $product->purchase_price;
                            $profitMargin = $product->sale_price > 0 ? (($profitAmount / $product->sale_price) * 100) : 0;
                            $markup = $product->purchase_price > 0 ? (($profitAmount / $product->purchase_price) * 100) : 0;
                            $roi = $markup; // ROI same as markup
                        @endphp
                        
                        <div class="profit-metrics">
                            <div class="profit-metric {{ $profitAmount >= 0 ? 'profit-positive' : 'profit-negative' }}">
                                <div class="profit-icon">
                                    <i class="fas fa-coins"></i>
                                </div>
                                <div class="profit-content">
                                    <div class="profit-label">Profit Amount</div>
                                    <div class="profit-value">৳{{ number_format($profitAmount, 2) }}</div>
                                </div>
                            </div>
                            
                            <div class="profit-metric {{ $profitMargin >= 15 ? 'profit-positive' : ($profitMargin >= 5 ? 'profit-neutral' : 'profit-negative') }}">
                                <div class="profit-icon">
                                    <i class="fas fa-percentage"></i>
                                </div>
                                <div class="profit-content">
                                    <div class="profit-label">Profit Margin</div>
                                    <div class="profit-value">{{ number_format($profitMargin, 2) }}%</div>
                                </div>
                            </div>
                            
                            <div class="profit-metric {{ $markup >= 15 ? 'profit-positive' : ($markup >= 5 ? 'profit-neutral' : 'profit-negative') }}">
                                <div class="profit-icon">
                                    <i class="fas fa-chart-line"></i>
                                </div>
                                <div class="profit-content">
                                    <div class="profit-label">Markup</div>
                                    <div class="profit-value">{{ number_format($markup, 2) }}%</div>
                                </div>
                            </div>
                            
                            <div class="profit-metric {{ $roi >= 15 ? 'profit-positive' : ($roi >= 5 ? 'profit-neutral' : 'profit-negative') }}">
                                <div class="profit-icon">
                                    <i class="fas fa-trending-up"></i>
                                </div>
                                <div class="profit-content">
                                    <div class="profit-label">ROI</div>
                                    <div class="profit-value">{{ number_format($roi, 2) }}%</div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="profit-status">
                            <div class="status-card {{ $profitAmount >= 0 ? 'status-positive' : 'status-negative' }}">
                                <div class="status-icon">
                                    @if($profitAmount < 0)
                                        <i class="fas fa-exclamation-triangle"></i>
                                    @elseif($profitMargin < 10)
                                        <i class="fas fa-info-circle"></i>
                                    @elseif($profitMargin < 20)
                                        <i class="fas fa-check-circle"></i>
                                    @else
                                        <i class="fas fa-star"></i>
                                    @endif
                                </div>
                                <div class="status-content">
                                    <div class="status-title">
                                        @if($profitAmount < 0)
                                            Loss Detected
                                        @elseif($profitMargin < 10)
                                            Low Profit Margin
                                        @elseif($profitMargin < 20)
                                            Good Profit Margin
                                        @elseif($profitMargin < 50)
                                            Excellent Profit Margin
                                        @else
                                            Very High Profit Margin
                                        @endif
                                    </div>
                                    <div class="status-description">
                                        @if($profitAmount < 0)
                                            Sale price is below purchase price. Consider adjusting pricing.
                                        @elseif($profitMargin < 10)
                                            Consider increasing sale price to improve profitability.
                                        @elseif($profitMargin < 20)
                                            Competitive pricing with decent profitability.
                                        @elseif($profitMargin < 50)
                                            Great profitability! Well-priced product.
                                        @else
                                            Ensure pricing remains competitive in the market.
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Activity Timeline Section -->
                <div class="details-section full-width">
                    <div class="section-header">
                        <h4 class="section-title">
                            <i class="fas fa-history"></i>
                            Activity Timeline
                        </h4>
                    </div>
                    
                    <div class="timeline">
                        <div class="timeline-item">
                            <div class="timeline-marker timeline-created">
                                <i class="fas fa-plus"></i>
                            </div>
                            <div class="timeline-content">
                                <div class="timeline-title">Product Created</div>
                                <div class="timeline-description">
                                    Product was initially created with opening stock of {{ number_format($product->opening_stock, 2) }} units
                                </div>
                                <div class="timeline-time">
                                    <i class="fas fa-clock"></i>
                                    {{ $product->created_at->format('F d, Y \a\t h:i A') }}
                                </div>
                            </div>
                        </div>
                        
                        @if($product->updated_at->ne($product->created_at))
                            <div class="timeline-item">
                                <div class="timeline-marker timeline-updated">
                                    <i class="fas fa-edit"></i>
                                </div>
                                <div class="timeline-content">
                                    <div class="timeline-title">Last Updated</div>
                                    <div class="timeline-description">
                                        Product information was last modified
                                    </div>
                                    <div class="timeline-time">
                                        <i class="fas fa-clock"></i>
                                        {{ $product->updated_at->format('F d, Y \a\t h:i A') }}
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Product Movement Section -->
    <div class="card modern-card mt-4">
        <div class="card-header modern-header">
            <div class="header-content">
                <div class="header-title">
                    <i class="fas fa-exchange-alt header-icon"></i>
                    <h3 class="card-title">Product Movement & History</h3>
                </div>
            </div>
        </div>

        <div class="card-body modern-card-body">
            <!-- Movement Date Range Filter -->
            <form method="GET" action="{{ route('products.show', $product) }}" class="movement-filter-form mb-3">
                <div class="filter-row">
                    <div class="filter-field">
                        <label for="movement_start_date">Start Date</label>
                        <input type="date" id="movement_start_date" name="start_date" class="form-control form-control-sm"
                               value="{{ $movementFilters['start_date'] ?? '' }}">
                    </div>
                    <div class="filter-field">
                        <label for="movement_end_date">End Date</label>
                        <input type="date" id="movement_end_date" name="end_date" class="form-control form-control-sm"
                               value="{{ $movementFilters['end_date'] ?? '' }}">
                    </div>
                    <div class="filter-actions">
                        <button type="submit" class="btn btn-sm btn-primary">
                            <i class="fas fa-filter"></i> Apply
                        </button>
                        <a href="{{ route('products.show', $product) }}" class="btn btn-sm btn-light">
                            Reset
                        </a>
                    </div>
                </div>
                @if(!empty($movementFilters['has_filter']))
                    <div class="filter-note">
                        Showing movement between {{ $movementFilters['start_date'] }} and {{ $movementFilters['end_date'] }}.
                    </div>
                @endif
            </form>

            @php
                $purchaseQtyTotal = $purchaseHistory->sum('quantity');
                $purchaseAmountTotal = $purchaseHistory->sum('total_price');
                $purchaseAvgRate = $purchaseQtyTotal > 0 ? $purchaseAmountTotal / $purchaseQtyTotal : 0;

                $salesQtyTotal = $salesHistory->sum('quantity');
                $salesAmountTotal = $salesHistory->sum('total');
                $salesAvgRate = $salesQtyTotal > 0 ? $salesAmountTotal / $salesQtyTotal : 0;

                $deliveryQtyTotal = $deliveryHistory->sum('quantity');
                $otherDeliveryQtyTotal = $otherDeliveryHistory->sum('quantity');
            @endphp

            <!-- Movement Summary Cards -->
            <div class="movement-summary-grid mb-4">
                <div class="movement-card movement-purchase">
                    <div class="movement-icon">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <div class="movement-content">
                        <div class="movement-label">Total Purchased</div>
                        <div class="movement-value">{{ number_format($movementSummary['total_purchased'], 2) }}</div>
                        <div class="movement-sub">৳{{ number_format($movementSummary['total_purchase_value'], 2) }}</div>
                    </div>
                </div>

                <div class="movement-card movement-sale">
                    <div class="movement-icon">
                        <i class="fas fa-file-invoice-dollar"></i>
                    </div>
                    <div class="movement-content">
                        <div class="movement-label">Total Sold</div>
                        <div class="movement-value">{{ number_format($movementSummary['total_sold'], 2) }}</div>
                        <div class="movement-sub">৳{{ number_format($movementSummary['total_sales_value'], 2) }}</div>
                    </div>
                </div>

                <div class="movement-card movement-delivery">
                    <div class="movement-icon">
                        <i class="fas fa-truck"></i>
                    </div>
                    <div class="movement-content">
                        <div class="movement-label">Total Delivered</div>
                        <div class="movement-value">{{ number_format($movementSummary['total_delivered'], 2) }}</div>
                    </div>
                </div>

                <div class="movement-card movement-pending">
                    <div class="movement-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="movement-content">
                        <div class="movement-label">Pending Delivery</div>
                        <div class="movement-value">{{ number_format($movementSummary['pending_delivery'], 2) }}</div>
                    </div>
                </div>
            </div>

            <!-- Movement Tabs -->
            <ul class="nav nav-tabs movement-tabs" id="movementTabs" role="tablist">
                <li class="nav-item">
                    <button class="nav-link active" id="purchases-tab" data-toggle="tab" data-target="#purchases" type="button" role="tab">
                        <i class="fas fa-shopping-cart"></i> Purchases
                        <span class="badge badge-primary ml-1">{{ $purchaseHistory->count() }}</span>
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" id="sales-tab" data-toggle="tab" data-target="#sales" type="button" role="tab">
                        <i class="fas fa-file-invoice-dollar"></i> Sales
                        <span class="badge badge-success ml-1">{{ $salesHistory->count() }}</span>
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" id="deliveries-tab" data-toggle="tab" data-target="#deliveries" type="button" role="tab">
                        <i class="fas fa-truck"></i> Challans
                        <span class="badge badge-info ml-1">{{ $deliveryHistory->count() }}</span>
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" id="other-deliveries-tab" data-toggle="tab" data-target="#other-deliveries" type="button" role="tab">
                        <i class="fas fa-dolly"></i> Other Delivery
                        <span class="badge badge-warning ml-1">{{ $otherDeliveryHistory->count() }}</span>
                    </button>
                </li>
            </ul>

            <div class="tab-content movement-tab-content" id="movementTabsContent">
                <!-- Purchase History Tab -->
                <div class="tab-pane fade show active" id="purchases" role="tabpanel">
                    <!-- Filter Row -->
                    <div class="filter-row mb-3">
                        <input type="text" class="form-control form-control-sm filter-input" id="purchaseFilter" placeholder="Search purchases...">
                    </div>
                    @if($purchaseHistory->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover movement-table" id="purchaseTable">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Invoice No</th>
                                        <th>Company</th>
                                        <th class="text-right">Quantity</th>
                                        <th class="text-right">Rate</th>
                                        <th class="text-right">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($purchaseHistory as $item)
                                    <tr>
                                        <td>{{ $item->purchase->purchase_date->format('d M Y') }}</td>
                                        <td>
                                            <a href="{{ route('purchases.show', $item->purchase_id) }}" class="text-primary">
                                                {{ $item->purchase->invoice_no ?? '#' . $item->purchase_id }}
                                            </a>
                                        </td>
                                        <td>{{ $item->purchase->company->name ?? 'N/A' }}</td>
                                        <td class="text-right">{{ number_format($item->quantity, 2) }}</td>
                                        <td class="text-right">৳{{ number_format($item->purchase_price, 2) }}</td>
                                        <td class="text-right">৳{{ number_format($item->total_price, 2) }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th colspan="3" class="text-right">Total</th>
                                        <th class="text-right">{{ number_format($purchaseQtyTotal, 2) }}</th>
                                        <th class="text-right">৳{{ number_format($purchaseAvgRate, 2) }}</th>
                                        <th class="text-right">৳{{ number_format($purchaseAmountTotal, 2) }}</th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    @else
                        <div class="empty-state">
                            <i class="fas fa-shopping-cart"></i>
                            <p>No purchase history found for this product.</p>
                        </div>
                    @endif
                </div>

                <!-- Sales History Tab -->
                <div class="tab-pane fade" id="sales" role="tabpanel">
                    <!-- Filter Row -->
                    <div class="filter-row mb-3">
                        <input type="text" class="form-control form-control-sm filter-input" id="salesFilter" placeholder="Search sales...">
                    </div>
                    @if($salesHistory->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover movement-table" id="salesTable">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Invoice No</th>
                                        <th>Customer</th>
                                        <th class="text-right">Quantity</th>
                                        <th class="text-right">Rate</th>
                                        <th class="text-right">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($salesHistory as $item)
                                    <tr>
                                        <td>{{ $item->invoice->invoice_date->format('d M Y') }}</td>
                                        <td>
                                            <a href="{{ route('invoices.show', $item->invoice_id) }}" class="text-primary">
                                                {{ $item->invoice->invoice_number ?? '#' . $item->invoice_id }}
                                            </a>
                                        </td>
                                        <td>{{ $item->invoice->customer->name ?? 'N/A' }}</td>
                                        <td class="text-right">{{ number_format($item->quantity, 2) }}</td>
                                        <td class="text-right">৳{{ number_format($item->unit_price, 2) }}</td>
                                        <td class="text-right">৳{{ number_format($item->total, 2) }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th colspan="3" class="text-right">Total</th>
                                        <th class="text-right">{{ number_format($salesQtyTotal, 2) }}</th>
                                        <th class="text-right">৳{{ number_format($salesAvgRate, 2) }}</th>
                                        <th class="text-right">৳{{ number_format($salesAmountTotal, 2) }}</th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    @else
                        <div class="empty-state">
                            <i class="fas fa-file-invoice-dollar"></i>
                            <p>No sales history found for this product.</p>
                        </div>
                    @endif
                </div>

                <!-- Delivery History Tab -->
                <div class="tab-pane fade" id="deliveries" role="tabpanel">
                    <!-- Filter Row -->
                    <div class="filter-row mb-3">
                        <input type="text" class="form-control form-control-sm filter-input" id="deliveryFilter" placeholder="Search challans...">
                    </div>
                    @if($deliveryHistory->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover movement-table" id="deliveryTable">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Challan No</th>
                                        <th>Invoice</th>
                                        <th>Customer</th>
                                        <th class="text-right">Quantity</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($deliveryHistory as $item)
                                    <tr>
                                        <td>{{ $item->challan->challan_date->format('d M Y') }}</td>
                                        <td>
                                            <a href="{{ route('challans.show', $item->challan_id) }}" class="text-primary">
                                                {{ $item->challan->challan_number ?? '#' . $item->challan_id }}
                                            </a>
                                        </td>
                                        <td>
                                            @if($item->challan->invoice)
                                                <a href="{{ route('invoices.show', $item->challan->invoice_id) }}" class="text-secondary">
                                                    {{ $item->challan->invoice->invoice_number }}
                                                </a>
                                            @else
                                                N/A
                                            @endif
                                        </td>
                                        <td>{{ $item->challan->invoice->customer->name ?? 'N/A' }}</td>
                                        <td class="text-right">{{ number_format($item->quantity, 2) }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th colspan="4" class="text-right">Total</th>
                                        <th class="text-right">{{ number_format($deliveryQtyTotal, 2) }}</th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    @else
                        <div class="empty-state">
                            <i class="fas fa-truck"></i>
                            <p>No challan delivery history found for this product.</p>
                        </div>
                    @endif
                </div>

                <!-- Other Delivery History Tab -->
                <div class="tab-pane fade" id="other-deliveries" role="tabpanel">
                    <!-- Filter Row -->
                    <div class="filter-row mb-3">
                        <input type="text" class="form-control form-control-sm filter-input" id="otherDeliveryFilter" placeholder="Search other deliveries...">
                    </div>
                    @if($otherDeliveryHistory->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover movement-table" id="otherDeliveryTable">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Challan No</th>
                                        <th>Recipient</th>
                                        <th>Status</th>
                                        <th class="text-right">Quantity</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($otherDeliveryHistory as $item)
                                    <tr>
                                        <td>{{ $item->otherDelivery->delivery_date->format('d M Y') }}</td>
                                        <td>
                                            <a href="{{ route('other-deliveries.show', $item->other_delivery_id) }}" class="text-primary">
                                                {{ $item->otherDelivery->challan_number ?? '#' . $item->other_delivery_id }}
                                            </a>
                                        </td>
                                        <td>{{ $item->otherDelivery->recipient_name ?? 'N/A' }}</td>
                                        <td>
                                            <span class="badge badge-{{ $item->otherDelivery->status == 'delivered' ? 'success' : ($item->otherDelivery->status == 'pending' ? 'warning' : 'secondary') }}">
                                                {{ ucfirst($item->otherDelivery->status ?? 'N/A') }}
                                            </span>
                                        </td>
                                        <td class="text-right">{{ number_format($item->quantity, 2) }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th colspan="4" class="text-right">Total</th>
                                        <th class="text-right">{{ number_format($otherDeliveryQtyTotal, 2) }}</th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    @else
                        <div class="empty-state">
                            <i class="fas fa-dolly"></i>
                            <p>No other delivery history found for this product.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions Card -->
    <div class="card modern-card mt-4">
        <div class="card-header modern-header">
            <div class="header-content">
                <div class="header-title">
                    <i class="fas fa-bolt header-icon"></i>
                    <h3 class="card-title">Quick Actions</h3>
                </div>
            </div>
        </div>
        
        <div class="card-body modern-card-body">
            <div class="quick-actions-grid">
                <a href="{{ route('products.edit', $product) }}" class="action-card action-primary">
                    <div class="action-icon">
                        <i class="fas fa-edit"></i>
                    </div>
                    <div class="action-content">
                        <div class="action-title">Edit Product</div>
                        <div class="action-description">Modify product details, pricing, or stock</div>
                    </div>
                </a>
                
                <a href="{{ route('products.index') }}" class="action-card action-secondary">
                    <div class="action-icon">
                        <i class="fas fa-list"></i>
                    </div>
                    <div class="action-content">
                        <div class="action-title">All Products</div>
                        <div class="action-description">View complete products list</div>
                    </div>
                </a>
                
                <a href="{{ route('products.create') }}" class="action-card action-success">
                    <div class="action-icon">
                        <i class="fas fa-plus"></i>
                    </div>
                    <div class="action-content">
                        <div class="action-title">Add New Product</div>
                        <div class="action-description">Create another product</div>
                    </div>
                </a>
                
                <button type="button" class="action-card action-info" onclick="printProduct()">
                    <div class="action-icon">
                        <i class="fas fa-print"></i>
                    </div>
                    <div class="action-content">
                        <div class="action-title">Print Details</div>
                        <div class="action-description">Print product information</div>
                    </div>
                </button>
            </div>
        </div>
    </div>
@stop

@section('additional_css')
    <!-- External Modern Admin Styles -->
    <link href="{{ asset('css/modern-admin.css') }}" rel="stylesheet">
    
    <!-- Page-specific styles for product details -->
    <style>
        .movement-filter-form {
            display: block;
        }

        .movement-filter-form .filter-row {
            display: grid;
            grid-template-columns: repeat(2, minmax(160px, 200px)) auto;
            gap: 12px;
            align-items: end;
        }

        .movement-filter-form .filter-field label {
            display: block;
            font-size: 12px;
            font-weight: 600;
            margin-bottom: 4px;
            color: #4b5563;
        }

        .movement-filter-form .filter-actions {
            display: flex;
            gap: 8px;
            align-items: center;
            padding-bottom: 2px;
        }

        .movement-filter-form .filter-note {
            margin-top: 6px;
            font-size: 12px;
            color: #6b7280;
        }

        @media (max-width: 768px) {
            .movement-filter-form .filter-row {
                grid-template-columns: 1fr;
            }

            .movement-filter-form .filter-actions {
                justify-content: flex-start;
            }
        }
        /* Details Grid Layout */
        .details-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 24px;
        }

        .details-section.full-width {
            grid-column: 1 / -1;
        }

        /* Detail Items */
        .details-content {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .detail-item {
            display: flex;
            align-items: flex-start;
            padding: 16px;
            background: linear-gradient(135deg, rgba(99, 102, 241, 0.02) 0%, rgba(139, 92, 246, 0.02) 100%);
            border: 1px solid rgba(99, 102, 241, 0.1);
            border-radius: 12px;
            transition: all 0.2s ease;
        }

        .detail-item:hover {
            background: linear-gradient(135deg, rgba(99, 102, 241, 0.05) 0%, rgba(139, 92, 246, 0.05) 100%);
            border-color: rgba(99, 102, 241, 0.2);
            transform: translateY(-1px);
        }

        .detail-label {
            min-width: 140px;
            font-weight: 600;
            color: #374151;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .detail-label i {
            color: #6366f1;
            width: 16px;
        }

        .detail-value {
            flex: 1;
            color: #6b7280;
            font-size: 14px;
            line-height: 1.5;
        }

        .company-name, .category-name {
            font-weight: 600;
            color: #374151;
        }

        .category-specs {
            display: flex;
            gap: 16px;
            margin-top: 8px;
        }

        .spec-item {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 12px;
            color: #6b7280;
            background: rgba(99, 102, 241, 0.1);
            padding: 4px 8px;
            border-radius: 6px;
        }

        .spec-item i {
            color: #6366f1;
        }

        .godown-display {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .godown-name {
            font-weight: 600;
            color: #374151;
        }

        .godown-location {
            font-size: 12px;
            color: #9ca3af;
        }

        /* Status Badges */
        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .status-enabled {
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.1) 0%, rgba(5, 150, 105, 0.1) 100%);
            color: #059669;
            border: 1px solid rgba(16, 185, 129, 0.2);
        }

        .status-disabled {
            background: linear-gradient(135deg, rgba(239, 68, 68, 0.1) 0%, rgba(220, 38, 38, 0.1) 100%);
            color: #dc2626;
            border: 1px solid rgba(239, 68, 68, 0.2);
        }

        /* Metrics Grid */
        .metrics-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
        }

        .metric-card {
            background: white;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            padding: 20px;
            display: flex;
            align-items: center;
            gap: 16px;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .godown-stock-section {
            margin-top: 20px;
            padding-top: 16px;
            border-top: 1px solid #e5e7eb;
        }

        .godown-stock-header {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 12px;
        }

        .godown-stock-header i {
            color: #6366f1;
        }

        .godown-stock-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 12px;
        }

        .godown-stock-card {
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            padding: 12px;
        }

        .godown-stock-name {
            font-size: 13px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 4px;
        }

        .godown-stock-location {
            font-size: 12px;
            color: #9ca3af;
            margin-bottom: 6px;
        }

        .godown-stock-qty {
            font-size: 12px;
            font-weight: 600;
            color: #2563eb;
        }

        .metric-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        }

        .metric-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(135deg, var(--metric-color-1), var(--metric-color-2));
        }

        .metric-primary {
            --metric-color-1: #6366f1;
            --metric-color-2: #8b5cf6;
        }

        .metric-success {
            --metric-color-1: #10b981;
            --metric-color-2: #059669;
        }

        .metric-warning {
            --metric-color-1: #f59e0b;
            --metric-color-2: #d97706;
        }

        .metric-info {
            --metric-color-1: #3b82f6;
            --metric-color-2: #2563eb;
        }

        .metric-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            color: white;
            background: linear-gradient(135deg, var(--metric-color-1), var(--metric-color-2));
        }

        .metric-content {
            flex: 1;
        }

        .metric-label {
            font-size: 12px;
            color: #6b7280;
            font-weight: 500;
            margin-bottom: 4px;
        }

        .metric-value {
            font-size: 24px;
            font-weight: 700;
            color: #374151;
            line-height: 1;
        }

        .metric-unit {
            font-size: 12px;
            color: #9ca3af;
            margin-top: 4px;
        }

        .metric-alert {
            position: absolute;
            top: 8px;
            right: 8px;
            background: #dc2626;
            color: white;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 10px;
            font-weight: 600;
        }

        /* Profit Analysis */
        .profit-analysis {
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.02) 0%, rgba(5, 150, 105, 0.02) 100%);
            border: 1px solid rgba(16, 185, 129, 0.1);
            border-radius: 16px;
            padding: 24px;
        }

        .profit-metrics {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
            margin-bottom: 24px;
        }

        .profit-metric {
            background: white;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            padding: 16px;
            display: flex;
            align-items: center;
            gap: 12px;
            transition: all 0.3s ease;
        }

        .profit-positive {
            border-color: #10b981;
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.05) 0%, rgba(5, 150, 105, 0.05) 100%);
        }

        .profit-neutral {
            border-color: #f59e0b;
            background: linear-gradient(135deg, rgba(245, 158, 11, 0.05) 0%, rgba(217, 119, 6, 0.05) 100%);
        }

        .profit-negative {
            border-color: #ef4444;
            background: linear-gradient(135deg, rgba(239, 68, 68, 0.05) 0%, rgba(220, 38, 38, 0.05) 100%);
        }

        .profit-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            color: white;
        }

        .profit-positive .profit-icon {
            background: linear-gradient(135deg, #10b981, #059669);
        }

        .profit-neutral .profit-icon {
            background: linear-gradient(135deg, #f59e0b, #d97706);
        }

        .profit-negative .profit-icon {
            background: linear-gradient(135deg, #ef4444, #dc2626);
        }

        .profit-content {
            flex: 1;
        }

        .profit-label {
            font-size: 12px;
            color: #6b7280;
            font-weight: 500;
            margin-bottom: 4px;
        }

        .profit-value {
            font-size: 18px;
            font-weight: 700;
            color: #374151;
        }

        /* Profit Status */
        .profit-status {
            margin-top: 20px;
        }

        .status-card {
            background: white;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            padding: 20px;
            display: flex;
            align-items: flex-start;
            gap: 16px;
        }

        .status-positive {
            border-color: #10b981;
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.05) 0%, rgba(5, 150, 105, 0.05) 100%);
        }

        .status-negative {
            border-color: #ef4444;
            background: linear-gradient(135deg, rgba(239, 68, 68, 0.05) 0%, rgba(220, 38, 38, 0.05) 100%);
        }

        .status-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            color: white;
            flex-shrink: 0;
        }

        .status-positive .status-icon {
            background: linear-gradient(135deg, #10b981, #059669);
        }

        .status-negative .status-icon {
            background: linear-gradient(135deg, #ef4444, #dc2626);
        }

        .status-content {
            flex: 1;
        }

        .status-title {
            font-size: 16px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 8px;
        }

        .status-description {
            font-size: 14px;
            color: #6b7280;
            line-height: 1.5;
        }

        /* Timeline */
        .timeline {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .timeline-item {
            display: flex;
            align-items: flex-start;
            gap: 16px;
        }

        .timeline-marker {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            color: white;
            flex-shrink: 0;
        }

        .timeline-created {
            background: linear-gradient(135deg, #10b981, #059669);
        }

        .timeline-updated {
            background: linear-gradient(135deg, #3b82f6, #2563eb);
        }

        .timeline-content {
            flex: 1;
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 16px;
        }

        .timeline-title {
            font-size: 16px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 8px;
        }

        .timeline-description {
            font-size: 14px;
            color: #6b7280;
            margin-bottom: 12px;
            line-height: 1.5;
        }

        .timeline-time {
            font-size: 12px;
            color: #9ca3af;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        /* Quick Actions Grid */
        .quick-actions-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 16px;
        }

        .action-card {
            background: white;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            padding: 20px;
            display: flex;
            align-items: center;
            gap: 16px;
            text-decoration: none;
            color: inherit;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .action-card:hover {
            text-decoration: none;
            color: inherit;
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        }

        .action-primary:hover {
            border-color: #6366f1;
            background: linear-gradient(135deg, rgba(99, 102, 241, 0.05) 0%, rgba(139, 92, 246, 0.05) 100%);
        }

        .action-secondary:hover {
            border-color: #6b7280;
            background: linear-gradient(135deg, rgba(107, 114, 128, 0.05) 0%, rgba(75, 85, 99, 0.05) 100%);
        }

        .action-success:hover {
            border-color: #10b981;
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.05) 0%, rgba(5, 150, 105, 0.05) 100%);
        }

        .action-info:hover {
            border-color: #3b82f6;
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.05) 0%, rgba(37, 99, 235, 0.05) 100%);
        }

        .action-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            color: white;
        }

        .action-primary .action-icon {
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
        }

        .action-secondary .action-icon {
            background: linear-gradient(135deg, #6b7280, #4b5563);
        }

        .action-success .action-icon {
            background: linear-gradient(135deg, #10b981, #059669);
        }

        .action-info .action-icon {
            background: linear-gradient(135deg, #3b82f6, #2563eb);
        }

        .action-content {
            flex: 1;
        }

        .action-title {
            font-size: 16px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 6px;
        }

        .action-description {
            font-size: 14px;
            color: #6b7280;
            line-height: 1.4;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .details-grid {
                grid-template-columns: 1fr;
                gap: 16px;
            }

            .metrics-grid {
                grid-template-columns: 1fr;
                gap: 12px;
            }

            .profit-metrics {
                grid-template-columns: repeat(2, 1fr);
                gap: 12px;
            }

            .quick-actions-grid {
                grid-template-columns: 1fr;
                gap: 12px;
            }

            .detail-item {
                flex-direction: column;
                gap: 8px;
                align-items: flex-start;
            }

            .detail-label {
                min-width: auto;
            }

            .category-specs {
                flex-direction: column;
                gap: 8px;
            }

            .status-card {
                flex-direction: column;
                text-align: center;
                gap: 12px;
            }

            .timeline-item {
                flex-direction: column;
                gap: 12px;
            }

            .timeline-marker {
                align-self: flex-start;
            }
        }

        /* Movement Summary Grid */
        .movement-summary-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
        }

        .movement-card {
            background: white;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            padding: 16px;
            display: flex;
            align-items: center;
            gap: 12px;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .movement-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
        }

        .movement-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .movement-purchase::before { background: linear-gradient(135deg, #6366f1, #8b5cf6); }
        .movement-sale::before { background: linear-gradient(135deg, #10b981, #059669); }
        .movement-delivery::before { background: linear-gradient(135deg, #3b82f6, #2563eb); }
        .movement-pending::before { background: linear-gradient(135deg, #f59e0b, #d97706); }

        .movement-icon {
            width: 44px;
            height: 44px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            color: white;
        }

        .movement-purchase .movement-icon { background: linear-gradient(135deg, #6366f1, #8b5cf6); }
        .movement-sale .movement-icon { background: linear-gradient(135deg, #10b981, #059669); }
        .movement-delivery .movement-icon { background: linear-gradient(135deg, #3b82f6, #2563eb); }
        .movement-pending .movement-icon { background: linear-gradient(135deg, #f59e0b, #d97706); }

        .movement-content {
            flex: 1;
        }

        .movement-label {
            font-size: 12px;
            color: #6b7280;
            font-weight: 500;
            margin-bottom: 2px;
        }

        .movement-value {
            font-size: 20px;
            font-weight: 700;
            color: #374151;
            line-height: 1.2;
        }

        .movement-sub {
            font-size: 12px;
            color: #9ca3af;
            margin-top: 2px;
        }

        /* Movement Tabs */
        .movement-tabs {
            border-bottom: 2px solid #e5e7eb;
            margin-bottom: 0;
        }

        .movement-tabs .nav-link {
            border: none;
            color: #6b7280;
            padding: 12px 20px;
            font-weight: 500;
            border-radius: 8px 8px 0 0;
            transition: all 0.2s ease;
        }

        .movement-tabs .nav-link:hover {
            color: #6366f1;
            background: rgba(99, 102, 241, 0.05);
        }

        .movement-tabs .nav-link.active {
            color: #6366f1;
            background: linear-gradient(135deg, rgba(99, 102, 241, 0.1) 0%, rgba(139, 92, 246, 0.1) 100%);
            border-bottom: 2px solid #6366f1;
            margin-bottom: -2px;
        }

        .movement-tabs .nav-link i {
            margin-right: 6px;
        }

        .movement-tabs .badge {
            font-size: 10px;
            padding: 3px 6px;
        }

        .movement-tab-content {
            padding: 20px 0;
        }

        /* Movement Table */
        .movement-table {
            margin-bottom: 0;
        }

        .movement-table thead th {
            background: linear-gradient(135deg, rgba(99, 102, 241, 0.05) 0%, rgba(139, 92, 246, 0.05) 100%);
            border-top: none;
            font-weight: 600;
            font-size: 13px;
            color: #374151;
            padding: 12px 16px;
        }

        .movement-table tbody td {
            padding: 12px 16px;
            font-size: 13px;
            vertical-align: middle;
            border-color: #f3f4f6;
        }

        .movement-table tbody tr:hover {
            background-color: rgba(99, 102, 241, 0.03);
        }

        .movement-table a {
            font-weight: 500;
        }

        .movement-table a:hover {
            text-decoration: underline;
        }

        /* Filter Row */
        .filter-row {
            display: flex;
            gap: 10px;
            padding: 10px 0;
        }

        .filter-input {
            max-width: 300px;
            border-radius: 8px;
            border: 1px solid #e5e7eb;
            padding: 8px 12px;
            font-size: 13px;
        }

        .filter-input:focus {
            border-color: #6366f1;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
            outline: none;
        }

        /* Tab Buttons */
        .movement-tabs .nav-link {
            cursor: pointer;
            background: none;
            border: none;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #9ca3af;
        }

        .empty-state i {
            font-size: 48px;
            margin-bottom: 16px;
            opacity: 0.5;
        }

        .empty-state p {
            margin: 0;
            font-size: 14px;
        }

        /* Print Styles */
        @media print {
            .modern-header,
            .quick-actions-grid,
            .action-card {
                display: none !important;
            }
            
            .modern-card {
                box-shadow: none;
                border: 1px solid #ddd;
            }
            
            .details-grid {
                display: block;
            }
            
            .details-section {
                page-break-inside: avoid;
                margin-bottom: 20px;
            }
        }
    </style>
@stop

@section('additional_js')
    <!-- External Modern Admin Scripts -->
    <script src="{{ asset('js/modern-admin.js') }}"></script>
    
    <!-- Page-specific Product Show Logic -->
    <script>
        $(document).ready(function() {
            // Product Show specific functionality
            const ProductShow = {
                // Initialize product show specific features
                init: function() {
                    this.initAnimations();
                    this.initInteractions();
                    this.monitorStock();
                    
                    console.log('Product Show initialized');
                },

                // Initialize scroll animations
                initAnimations: function() {
                    // Animate metric cards on scroll
                    const observerOptions = {
                        threshold: 0.1,
                        rootMargin: '0px 0px -50px 0px'
                    };

                    const observer = new IntersectionObserver((entries) => {
                        entries.forEach(entry => {
                            if (entry.isIntersecting) {
                                entry.target.style.opacity = '1';
                                entry.target.style.transform = 'translateY(0)';
                            }
                        });
                    }, observerOptions);

                    // Observe all animatable elements
                    document.querySelectorAll('.metric-card, .profit-metric, .timeline-item, .action-card').forEach(el => {
                        el.style.opacity = '0';
                        el.style.transform = 'translateY(20px)';
                        el.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
                        observer.observe(el);
                    });

                    // Stagger animation for metric cards
                    document.querySelectorAll('.metric-card').forEach((card, index) => {
                        setTimeout(() => {
                            card.style.opacity = '1';
                            card.style.transform = 'translateY(0)';
                        }, index * 150);
                    });
                },

                // Initialize interactive elements
                initInteractions: function() {
                    // Add hover effects for detail items
                    $('.detail-item').on('mouseenter', function() {
                        $(this).addClass('pulse');
                        setTimeout(() => {
                            $(this).removeClass('pulse');
                        }, 300);
                    });

                    // Add click to copy functionality for values
                    $('.metric-value, .profit-value').on('click', function() {
                        const text = $(this).text();
                        if (navigator.clipboard) {
                            navigator.clipboard.writeText(text).then(() => {
                                ModernAdmin.showAlert(`Copied "${text}" to clipboard`, 'success', 2000);
                            });
                        }
                    });

                    // Add tooltips for metric cards
                    $('.metric-card').each(function() {
                        const label = $(this).find('.metric-label').text();
                        const value = $(this).find('.metric-value').text();
                        $(this).attr('title', `${label}: ${value}`);
                    });

                    // Initialize tooltips
                    ModernAdmin.initTooltips();
                },

                // Monitor stock levels (visual indicator only, no alerts)
                monitorStock: function() {
                    // Stock alerts disabled to avoid showing on every page refresh
                    // Low stock indicator is already visible in the metric cards
                }
            };

            // Global functions
            window.printProduct = function() {
                // Set print title
                document.title = 'Product Details - {{ $product->name }}';
                
                // Show print message
                ModernAdmin.showAlert('Preparing product details for printing...', 'info', 2000);
                
                // Trigger print after short delay
                setTimeout(() => {
                    window.print();
                }, 500);
            };

            // Enhanced keyboard shortcuts
            $(document).on('keydown', function(e) {
                // 'E' key to edit
                if (e.keyCode === 69 && !e.ctrlKey && !e.altKey) {
                    const activeElement = document.activeElement;
                    if (activeElement.tagName !== 'INPUT' && activeElement.tagName !== 'TEXTAREA') {
                        e.preventDefault();
                        window.location.href = "{{ route('products.edit', $product) }}";
                    }
                }
                
                // 'P' key to print
                if (e.keyCode === 80 && !e.ctrlKey && !e.altKey) {
                    const activeElement = document.activeElement;
                    if (activeElement.tagName !== 'INPUT' && activeElement.tagName !== 'TEXTAREA') {
                        e.preventDefault();
                        printProduct();
                    }
                }
                
                // 'B' key to go back
                if (e.keyCode === 66 && !e.ctrlKey && !e.altKey) {
                    const activeElement = document.activeElement;
                    if (activeElement.tagName !== 'INPUT' && activeElement.tagName !== 'TEXTAREA') {
                        e.preventDefault();
                        window.location.href = "{{ route('products.index') }}";
                    }
                }
            });

            // Initialize product show functionality
            ProductShow.init();

            // Show keyboard shortcuts help on first visit
            if (!ModernAdmin.utils.storage.get('product_show_help_shown')) {
                setTimeout(() => {
                    ModernAdmin.showAlert(
                        'Keyboard shortcuts: Press "E" to edit, "P" to print, "B" to go back', 
                        'info', 
                        5000
                    );
                    ModernAdmin.utils.storage.set('product_show_help_shown', true);
                }, 2000);
            }

            // Movement Tab handling - prevent scroll to top
            $('.movement-tabs .nav-link').on('click', function(e) {
                e.preventDefault();
                e.stopPropagation();

                var target = $(this).data('target');

                // Remove active from all tabs and panes
                $('.movement-tabs .nav-link').removeClass('active');
                $('.movement-tab-content .tab-pane').removeClass('show active');

                // Add active to clicked tab and target pane
                $(this).addClass('active');
                $(target).addClass('show active');
            });

            // Table filtering functionality
            function filterTable(inputId, tableId) {
                $('#' + inputId).on('keyup', function() {
                    var value = $(this).val().toLowerCase();
                    $('#' + tableId + ' tbody tr').filter(function() {
                        $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
                    });
                });
            }

            // Initialize filters for all tables
            filterTable('purchaseFilter', 'purchaseTable');
            filterTable('salesFilter', 'salesTable');
            filterTable('deliveryFilter', 'deliveryTable');
            filterTable('otherDeliveryFilter', 'otherDeliveryTable');

            console.log('Product details page loaded successfully');
        });
    </script>
@stop
