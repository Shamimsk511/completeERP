@extends('layouts.modern-admin')

@section('title', 'Cash Flow Reports')

@section('page_title', 'Cash Flow Reports')

@section('header_actions')
    <div class="header-actions-group">
        <a href="{{ route('products.index') }}" class="btn modern-btn modern-btn-outline">
            <i class="fas fa-arrow-left"></i> Back to Products
        </a>
        <button type="button" class="btn modern-btn modern-btn-secondary" id="print-report">
            <i class="fas fa-print"></i> Print Report
        </button>
        <button type="button" class="btn modern-btn modern-btn-info" id="export-report">
            <i class="fas fa-file-excel"></i> Export to Excel
        </button>
    </div>
@stop

@section('page_content')
    <!-- Dashboard Summary Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card modern-card summary-card sales-card">
                <div class="card-body">
                    <div class="summary-content">
                        <div class="summary-icon">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <div class="summary-details">
                            <h3 class="summary-title">Sales</h3>
                            <h2 class="summary-value" id="total-sales">৳{{ number_format($dashboardData['sales_amount'], 2) }}</h2>
                            <p class="summary-subtitle">{{ $dashboardData['sales_count'] }} invoices this month</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6">
            <div class="card modern-card summary-card collections-card">
                <div class="card-body">
                    <div class="summary-content">
                        <div class="summary-icon">
                            <i class="fas fa-money-bill-wave"></i>
                        </div>
                        <div class="summary-details">
                            <h3 class="summary-title">Collections</h3>
                            <h2 class="summary-value" id="total-collections">৳{{ number_format($dashboardData['collections_amount'], 2) }}</h2>
                            <p class="summary-subtitle">Cash received this month</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6">
            <div class="card modern-card summary-card purchases-card">
                <div class="card-body">
                    <div class="summary-content">
                        <div class="summary-icon">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                        <div class="summary-details">
                            <h3 class="summary-title">Purchases</h3>
                            <h2 class="summary-value" id="total-purchases">৳{{ number_format($dashboardData['purchases_amount'], 2) }}</h2>
                            <p class="summary-subtitle">Inventory purchased</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6">
            <div class="card modern-card summary-card cashflow-card">
                <div class="card-body">
                    <div class="summary-content">
                        <div class="summary-icon">
                            <i class="fas fa-exchange-alt"></i>
                        </div>
                        <div class="summary-details">
                            <h3 class="summary-title">Net Cash Flow</h3>
                            <h2 class="summary-value" id="net-cashflow">৳{{ number_format($dashboardData['collections_amount'] - $dashboardData['purchases_amount'], 2) }}</h2>
                            <p class="summary-subtitle">This month</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Report Type Selector -->
    <div class="card modern-card report-selector-card">
        <div class="card-header modern-header">
            <div class="header-content">
                <div class="header-title">
                    <i class="fas fa-chart-bar header-icon"></i>
                    <h3 class="card-title">Report Type</h3>
                </div>
            </div>
        </div>
        <div class="card-body modern-card-body">
            <div class="report-type-grid">
                <div class="report-type-option active" data-report="sales">
                    <div class="report-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <h4>Sales Report</h4>
                    <p>View invoice sales, payment status, and delivery tracking</p>
                </div>
                <div class="report-type-option" data-report="collections">
                    <div class="report-icon">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                    <h4>Collection Report</h4>
                    <p>Track cash collections, payment methods, and discounts</p>
                </div>
                <div class="report-type-option" data-report="purchases">
                    <div class="report-icon">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <h4>Purchase Report</h4>
                    <p>Monitor inventory purchases and supplier payments</p>
                </div>
                <div class="report-type-option" data-report="cashflow">
                    <div class="report-icon">
                        <i class="fas fa-exchange-alt"></i>
                    </div>
                    <h4>Cash Flow Summary</h4>
                    <p>Comprehensive cash flow analysis with charts and trends</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Advanced Filters Card -->
    <div class="card modern-card filter-card">
        <div class="card-header modern-header">
            <div class="header-content">
                <div class="header-title">
                    <i class="fas fa-filter header-icon"></i>
                    <h3 class="card-title">Report Filters</h3>
                </div>
                <button type="button" class="btn modern-btn modern-btn-outline" data-card-widget="collapse">
                    <i class="fas fa-minus"></i>
                </button>
            </div>
        </div>
        <div class="card-body modern-card-body">
            <div class="filter-grid">
                <!-- Common Filters -->
                <div class="filter-group">
                    <label for="start-date" class="filter-label">Start Date</label>
                    <input type="date" id="start-date" class="form-control modern-input">
                </div>
                <div class="filter-group">
                    <label for="end-date" class="filter-label">End Date</label>
                    <input type="date" id="end-date" class="form-control modern-input">
                </div>
                <div class="filter-group">
                    <label for="period-filter" class="filter-label">Quick Period</label>
                    <select id="period-filter" class="form-control modern-select">
                        <option value="">Custom Date Range</option>
                        <option value="today">Today</option>
                        <option value="last_10_days">Last 10 Days</option>
                        <option value="last_30_days">Last 30 Days</option>
                        <option value="week">This Week</option>
                        <option value="month" selected>This Month</option>
                        <option value="quarter">This Quarter</option>
                        <option value="year">This Year</option>
                    </select>
                </div>
                
                <!-- Sales-specific filters -->
                <div class="filter-group sales-filters">
                    <label for="payment-status-filter" class="filter-label">Payment Status</label>
                    <select id="payment-status-filter" class="form-control modern-select">
                        <option value="">All Status</option>
                        <option value="paid">Paid</option>
                        <option value="partial">Partial</option>
                        <option value="due">Due</option>
                    </select>
                </div>
                <div class="filter-group sales-filters">
                    <label for="delivery-status-filter" class="filter-label">Delivery Status</label>
                    <select id="delivery-status-filter" class="form-control modern-select">
                        <option value="">All Status</option>
                        <option value="pending">Pending</option>
                        <option value="partial">Partial</option>
                        <option value="delivered">Delivered</option>
                    </select>
                </div>
                <div class="filter-group sales-filters">
                    <label for="invoice-type-filter" class="filter-label">Invoice Type</label>
                    <select id="invoice-type-filter" class="form-control modern-select">
                        <option value="">All Types</option>
                        <option value="tiles">Tiles</option>
                        <option value="other">Other</option>
                    </select>
                </div>

                <div class="filter-group sales-filters">
                    <label class="filter-label">Quick Range</label>
                    <div class="btn-group w-100">
                        <button type="button" class="btn btn-outline-secondary btn-sm quick-range-btn" data-range="today">Today</button>
                        <button type="button" class="btn btn-outline-secondary btn-sm quick-range-btn" data-range="last_10_days">10 Days</button>
                        <button type="button" class="btn btn-outline-secondary btn-sm quick-range-btn" data-range="last_30_days">30 Days</button>
                    </div>
                </div>
                
                <!-- Collection-specific filters -->
                <div class="filter-group collection-filters" style="display: none;">
                    <label for="payment-method-filter" class="filter-label">Payment Method</label>
                    <select id="payment-method-filter" class="form-control modern-select">
                        <option value="">All Methods</option>
                        <option value="cash">Cash</option>
                        <option value="bank">Bank</option>
                        <option value="mobile_bank">Mobile Bank</option>
                        <option value="cheque">Cheque</option>
                    </select>
                </div>
                
                <!-- Amount filters -->
                <div class="filter-group amount-filters">
                    <label for="min-amount" class="filter-label">Min Amount</label>
                    <input type="number" id="min-amount" class="form-control modern-input" placeholder="Min amount...">
                </div>
                <div class="filter-group amount-filters">
                    <label for="max-amount" class="filter-label">Max Amount</label>
                    <input type="number" id="max-amount" class="form-control modern-input" placeholder="Max amount...">
                </div>
                
                <div class="filter-actions">
                    <button id="apply-filters" class="btn modern-btn modern-btn-primary">
                        <i class="fas fa-search"></i> Apply Filters
                    </button>
                    <button id="reset-filters" class="btn modern-btn modern-btn-outline">
                        <i class="fas fa-undo"></i> Reset
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Report Summary Card -->
    <div class="card modern-card summary-card">
        <div class="card-header modern-header">
            <div class="header-content">
                <div class="header-title">
                    <i class="fas fa-chart-pie header-icon"></i>
                    <h3 class="card-title">Report Summary</h3>
                </div>
            </div>
        </div>
        <div class="card-body modern-card-body">
            <!-- Sales Summary -->
            <div class="summary-grid" id="sales-summary">
                <div class="summary-item">
                    <div class="summary-icon">
                        <i class="fas fa-file-invoice-dollar"></i>
                    </div>
                    <div class="summary-content">
                        <span class="summary-label">Total Sales</span>
                        <span class="summary-value" id="sales-total-amount">-</span>
                    </div>
                </div>
                <div class="summary-item">
                    <div class="summary-icon collected">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                    <div class="summary-content">
                        <span class="summary-label">Collected</span>
                        <span class="summary-value" id="sales-collected-amount">-</span>
                    </div>
                </div>
                <div class="summary-item">
                    <div class="summary-icon due">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="summary-content">
                        <span class="summary-label">Due Amount</span>
                        <span class="summary-value" id="sales-due-amount">-</span>
                    </div>
                </div>
                <div class="summary-item">
                    <div class="summary-icon">
                        <i class="fas fa-hashtag"></i>
                    </div>
                    <div class="summary-content">
                        <span class="summary-label">Invoice Count</span>
                        <span class="summary-value" id="sales-count">-</span>
                    </div>
                </div>
                <div class="summary-item">
                    <div class="summary-icon">
                        <i class="fas fa-chart-bar"></i>
                    </div>
                    <div class="summary-content">
                        <span class="summary-label">Average Sale</span>
                        <span class="summary-value" id="sales-average">-</span>
                    </div>
                </div>
            </div>
            
            <!-- Collection Summary -->
            <div class="summary-grid" id="collection-summary" style="display: none;">
                <div class="summary-item">
                    <div class="summary-icon">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                    <div class="summary-content">
                        <span class="summary-label">Total Collections</span>
                        <span class="summary-value" id="collection-total-amount">-</span>
                    </div>
                </div>
                <div class="summary-item">
                    <div class="summary-icon">
                        <i class="fas fa-percentage"></i>
                    </div>
                    <div class="summary-content">
                        <span class="summary-label">Total Discounts</span>
                        <span class="summary-value" id="collection-discount-amount">-</span>
                    </div>
                </div>
                <div class="summary-item">
                    <div class="summary-icon">
                        <i class="fas fa-hashtag"></i>
                    </div>
                    <div class="summary-content">
                        <span class="summary-label">Transaction Count</span>
                        <span class="summary-value" id="collection-count">-</span>
                    </div>
                </div>
                <div class="summary-item">
                    <div class="summary-icon">
                        <i class="fas fa-chart-bar"></i>
                    </div>
                    <div class="summary-content">
                        <span class="summary-label">Average Collection</span>
                        <span class="summary-value" id="collection-average">-</span>
                    </div>
                </div>
            </div>
            
            <!-- Purchase Summary -->
            <div class="summary-grid" id="purchase-summary" style="display: none;">
                <div class="summary-item">
                    <div class="summary-icon">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <div class="summary-content">
                        <span class="summary-label">Total Purchases</span>
                        <span class="summary-value" id="purchase-total-amount">-</span>
                    </div>
                </div>
                <div class="summary-item">
                    <div class="summary-icon">
                        <i class="fas fa-hashtag"></i>
                    </div>
                    <div class="summary-content">
                        <span class="summary-label">Purchase Count</span>
                        <span class="summary-value" id="purchase-count">-</span>
                    </div>
                </div>
                <div class="summary-item">
                    <div class="summary-icon">
                        <i class="fas fa-chart-bar"></i>
                    </div>
                    <div class="summary-content">
                        <span class="summary-label">Average Purchase</span>
                        <span class="summary-value" id="purchase-average">-</span>
                    </div>
                </div>
            </div>
            
            <!-- Cash Flow Summary -->
            <div class="summary-grid" id="cashflow-summary" style="display: none;">
                <div class="summary-item">
                    <div class="summary-icon inflow">
                        <i class="fas fa-arrow-up"></i>
                    </div>
                    <div class="summary-content">
                        <span class="summary-label">Total Inflows</span>
                        <span class="summary-value" id="cashflow-inflows">-</span>
                    </div>
                </div>
                <div class="summary-item">
                    <div class="summary-icon outflow">
                        <i class="fas fa-arrow-down"></i>
                    </div>
                    <div class="summary-content">
                        <span class="summary-label">Total Outflows</span>
                        <span class="summary-value" id="cashflow-outflows">-</span>
                    </div>
                </div>
                <div class="summary-item">
                    <div class="summary-icon">
                        <i class="fas fa-exchange-alt"></i>
                    </div>
                    <div class="summary-content">
                        <span class="summary-label">Net Cash Flow</span>
                        <span class="summary-value" id="cashflow-net">-</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Cash Flow Chart (for cash flow report) -->
    <div class="card modern-card" id="cashflow-chart-card" style="display: none;">
        <div class="card-header modern-header">
            <div class="header-content">
                <div class="header-title">
                    <i class="fas fa-chart-area header-icon"></i>
                    <h3 class="card-title">Cash Flow Trend</h3>
                </div>
            </div>
        </div>
        <div class="card-body modern-card-body">
            <canvas id="cashflow-chart" height="100"></canvas>
        </div>
    </div>

    <!-- Sales Insights -->
    <div class="card modern-card" id="sales-insights-card" style="display: none;">
        <div class="card-header modern-header">
            <div class="header-content">
                <div class="header-title">
                    <i class="fas fa-chart-line header-icon"></i>
                    <h3 class="card-title">Sales Insights</h3>
                </div>
                <span class="text-muted small" id="sales-insights-range"></span>
            </div>
        </div>
        <div class="card-body modern-card-body">
            <div class="row mb-4">
                <div class="col-md-4 mb-3">
                    <div class="summary-item">
                        <div class="summary-icon">
                            <i class="fas fa-walking"></i>
                        </div>
                        <div class="summary-content">
                            <span class="summary-label">Footfall (Invoices)</span>
                            <span class="summary-value" id="sales-footfall">-</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="summary-item">
                        <div class="summary-icon collected">
                            <i class="fas fa-shopping-basket"></i>
                        </div>
                        <div class="summary-content">
                            <span class="summary-label">Basket Size (Avg)</span>
                            <span class="summary-value" id="sales-basket-size">-</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="summary-item">
                        <div class="summary-icon due">
                            <i class="fas fa-coins"></i>
                        </div>
                        <div class="summary-content">
                            <span class="summary-label">Total Sales</span>
                            <span class="summary-value" id="sales-total-insights">-</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table modern-table" id="hourly-sales-table">
                    <thead class="modern-thead">
                        <tr id="hourly-sales-header">
                            <th>Hour</th>
                        </tr>
                    </thead>
                    <tbody class="modern-tbody">
                        <tr id="hourly-sales-values">
                            <td>Sales</td>
                        </tr>
                    </tbody>
                </table>
                <div class="text-center text-muted small" id="hourly-sales-loading">Loading hourly sales...</div>
            </div>
        </div>
    </div>

    <!-- Report Data Card -->
    <div class="card modern-card">
        <div class="card-header modern-header">
            <div class="header-content">
                <div class="header-title">
                    <i class="fas fa-table header-icon"></i>
                    <h3 class="card-title" id="report-title">Sales Report Data</h3>
                </div>
            </div>
        </div>
        
        <div class="card-body modern-card-body">
            <div class="table-container" id="printable-area">
                <!-- Sales Report Table -->
                <div class="table-responsive modern-table-responsive" id="sales-report-table">
                    <table class="table modern-table" id="sales-table">
                        <thead class="modern-thead">
                            <tr>
                                <th>Invoice #</th>
                                <th>Date</th>
                                <th>Customer</th>
                                <th>Phone</th>
                                <th>Type</th>
                                <th>Total Amount</th>
                                <th>Paid Amount</th>
                                <th>Due Amount</th>
                                <th>Payment Status</th>
                                <th>Delivery Status</th>
                            </tr>
                        </thead>
                        <tbody class="modern-tbody">
                            <!-- DataTable will populate this -->
                        </tbody>
                    </table>
                </div>

                <!-- Collection Report Table -->
                <div class="table-responsive modern-table-responsive" id="collection-report-table" style="display: none;">
                    <table class="table modern-table" id="collection-table">
                        <thead class="modern-thead">
                            <tr>
                                <th>Date</th>
                                <th>Customer</th>
                                <th>Phone</th>
                                <th>Purpose</th>
                                <th>Method</th>
                                <th>Amount</th>
                                <th>Discount</th>
                                <th>Total Received</th>
                                <th>Reference</th>
                            </tr>
                        </thead>
                        <tbody class="modern-tbody">
                            <!-- DataTable will populate this -->
                        </tbody>
                    </table>
                </div>

                <!-- Purchase Report Table -->
                <div class="table-responsive modern-table-responsive" id="purchase-report-table" style="display: none;">
                    <table class="table modern-table" id="purchase-table">
                        <thead class="modern-thead">
                            <tr>
                                <th>Date</th>
                                <th>Invoice #</th>
                                <th>Company</th>
                                <th>Items Count</th>
                                <th>Total Amount</th>
                                <th>Notes</th>
                            </tr>
                        </thead>
                        <tbody class="modern-tbody">
                            <!-- DataTable will populate this -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@stop

@section('additional_css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.25/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.min.css">
    
    <style>
        /* Dashboard Summary Cards */
        .summary-card {
            border: none;
            border-radius: 16px;
            overflow: hidden;
            transition: all 0.3s ease;
            margin-bottom: 24px;
        }

        .summary-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }

        .chart-card {
            background: #fff;
            border-radius: 16px;
            padding: 16px;
            box-shadow: 0 6px 18px rgba(15, 23, 42, 0.08);
        }

        .chart-title {
            font-weight: 600;
            font-size: 0.95rem;
            margin-bottom: 12px;
            color: #374151;
        }

        .summary-content {
            display: flex;
            align-items: center;
            gap: 20px;
            padding: 8px;
        }

        .summary-icon {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            color: white;
            flex-shrink: 0;
        }

        .sales-card .summary-icon {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .collections-card .summary-icon {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        }

        .purchases-card .summary-icon {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        }

        .cashflow-card .summary-icon {
            background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
        }

        .summary-details {
            flex: 1;
        }

        .summary-title {
            font-size: 14px;
            color: #6b7280;
            margin: 0 0 8px 0;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .summary-value {
            font-size: 28px;
            font-weight: 700;
            color: #1f2937;
            margin: 0 0 4px 0;
            line-height: 1;
        }

        .summary-subtitle {
            font-size: 12px;
            color: #9ca3af;
            margin: 0;
        }

        /* Report Type Selector Styles */
        .report-selector-card {
            margin-bottom: 24px;
        }

        .report-type-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }

        .report-type-option {
            background: white;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            padding: 24px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .report-type-option:hover {
            border-color: #6366f1;
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(99, 102, 241, 0.15);
        }

        .report-type-option.active {
            border-color: #6366f1;
            background: linear-gradient(135deg, rgba(99, 102, 241, 0.1) 0%, rgba(139, 92, 246, 0.1) 100%);
            box-shadow: 0 8px 25px rgba(99, 102, 241, 0.2);
        }

        .report-type-option.active::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
        }

        .report-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 16px;
            transition: all 0.3s ease;
        }

        .report-icon i {
            font-size: 24px;
            color: white;
        }

        .report-type-option:hover .report-icon {
            transform: scale(1.1);
            box-shadow: 0 8px 20px rgba(99, 102, 241, 0.4);
        }

        .report-type-option h4 {
            color: #374151;
            font-weight: 600;
            margin-bottom: 8px;
            font-size: 18px;
        }

        .report-type-option p {
            color: #6b7280;
            font-size: 14px;
            margin: 0;
            line-height: 1.5;
        }

        .report-type-option.active h4 {
            color: #6366f1;
        }

        /* Enhanced Summary Grid */
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
        }

        .summary-item {
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 20px;
            display: flex;
            align-items: center;
            gap: 16px;
            transition: all 0.3s ease;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .summary-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        }

        .summary-item .summary-icon {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .summary-item .summary-icon i {
            font-size: 20px;
            color: white;
        }

        .summary-item .summary-icon.collected {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        }

        .summary-item .summary-icon.due {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        }

        .summary-item .summary-icon.inflow {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        }

        .summary-item .summary-icon.outflow {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        }

        .summary-item .summary-content {
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .summary-item .summary-label {
            font-size: 12px;
            color: #6b7280;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 4px;
        }

        .summary-item .summary-value {
            font-size: 24px;
            font-weight: 700;
            color: #374151;
        }

        /* Filter and other existing styles from product reports */
        .filter-card {
            margin-bottom: 24px;
        }

        .filter-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            align-items: end;
        }

        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .filter-label {
            font-size: 13px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 0;
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

        .filter-actions {
            display: flex;
            gap: 12px;
            align-items: center;
        }

        /* Modern Card and Table styles */
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

        /* Button Styles */
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

        .header-actions-group {
            display: flex;
            gap: 12px;
            align-items: center;
        }

        /* Mobile Responsive */
        @media (max-width: 768px) {
            .report-type-grid {
                grid-template-columns: 1fr;
            }

            .summary-grid {
                grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
                gap: 12px;
            }

            .summary-item {
                padding: 16px;
                flex-direction: column;
                text-align: center;
                gap: 12px;
            }

            .filter-grid {
                grid-template-columns: 1fr;
                gap: 16px;
            }

            .filter-actions {
                grid-column: 1;
                justify-content: stretch;
            }

            .filter-actions .btn {
                flex: 1;
            }

            .header-actions-group {
                flex-direction: column;
                width: 100%;
                gap: 8px;
            }

            .header-actions-group .btn {
                width: 100%;
                justify-content: center;
            }

            .table-container {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }

            .modern-table {
                min-width: 800px;
            }
        }

        /* Print Styles */
        @media print {
            /* Hide non-essential elements */
            .header-actions-group,
            .btn,
            .report-selector-card,
            .filter-card,
            .summary-card:not(.report-data-card),
            #cashflow-chart-card,
            .card-header .btn,
            .modern-header .btn {
                display: none !important;
            }
            
            /* Show main content */
            body {
                background: white !important;
                font-size: 12px;
                line-height: 1.4;
                color: #000 !important;
            }
            
            .modern-card {
                box-shadow: none !important;
                border: 1px solid #ddd !important;
                margin-bottom: 20px !important;
                page-break-inside: avoid;
            }
            
            .modern-header {
                background: #f8f9fa !important;
                color: #000 !important;
                border-bottom: 2px solid #dee2e6 !important;
                padding: 15px !important;
            }
            
            .card-title {
                color: #000 !important;
                font-size: 18px !important;
                font-weight: bold !important;
            }
            
            .header-icon {
                display: none !important;
            }
            
            .modern-card-body {
                padding: 15px !important;
            }
            
            /* Table styles for print */
            .modern-table-responsive {
                box-shadow: none !important;
                border: 1px solid #dee2e6 !important;
            }
            
            .modern-table {
                font-size: 11px !important;
                width: 100% !important;
            }
            
            .modern-thead {
                background: #e9ecef !important;
                color: #000 !important;
                border-bottom: 2px solid #dee2e6 !important;
            }
            
            .modern-thead th {
                color: #000 !important;
                background: #e9ecef !important;
                border: 1px solid #dee2e6 !important;
                padding: 8px 6px !important;
                font-size: 10px !important;
                font-weight: bold !important;
            }
            
            .modern-tbody tr {
                background: white !important;
                border-bottom: 1px solid #dee2e6 !important;
                page-break-inside: avoid;
            }
            
            .modern-tbody tr:hover {
                background: white !important;
                transform: none !important;
                box-shadow: none !important;
            }
            
            .modern-tbody td {
                color: #000 !important;
                background: white !important;
                border: 1px solid #dee2e6 !important;
                padding: 6px 4px !important;
                font-size: 10px !important;
            }
            
            /* Badge styles for print */
            .badge {
                background: #6c757d !important;
                color: white !important;
                padding: 2px 6px !important;
                border-radius: 3px !important;
                font-size: 9px !important;
            }
            
            .badge-success {
                background: #28a745 !important;
            }
            
            .badge-warning {
                background: #ffc107 !important;
                color: #000 !important;
            }
            
            .badge-danger {
                background: #dc3545 !important;
            }
            
            .badge-info {
                background: #17a2b8 !important;
            }
            
            .badge-primary {
                background: #007bff !important;
            }
            
            /* Print header */
            @page {
                margin: 0.5in;
                size: A4;
            }
            
            /* Add print header */
            body::before {
                content: "Cash Flow Report - Generated on " attr(data-print-date);
                display: block;
                text-align: center;
                font-weight: bold;
                font-size: 16px;
                margin-bottom: 20px;
                padding-bottom: 10px;
                border-bottom: 2px solid #000;
            }
            
            /* Force page break before new sections */
            .modern-card {
                page-break-before: auto;
            }
            
            /* Avoid breaking table rows */
            .modern-tbody tr {
                page-break-inside: avoid;
            }
        }
    </style>
@stop

@section('additional_js')
    <script src="https://cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.25/js/dataTables.bootstrap4.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <script>
        $(document).ready(function() {
            let currentReportType = 'sales';
            let salesTable, collectionTable, purchaseTable;
            let cashFlowChart;

            // Configure toastr
            toastr.options = {
                "closeButton": true,
                "newestOnTop": true,
                "progressBar": true,
                "positionClass": "toast-top-right",
                "timeOut": "3000"
            };

            // Set default dates (current month)
            const today = new Date();
            const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
            const lastDay = new Date(today.getFullYear(), today.getMonth() + 1, 0);
            
            $('#start-date').val(firstDay.toISOString().split('T')[0]);
            $('#end-date').val(lastDay.toISOString().split('T')[0]);

            // Report type selector
            $('.report-type-option').on('click', function() {
                $('.report-type-option').removeClass('active');
                $(this).addClass('active');
                
                currentReportType = $(this).data('report');
                switchReportType(currentReportType);
                
                toastr.info('Switched to ' + $(this).find('h4').text());
            });

            // Period filter change
            $('#period-filter').on('change', function() {
                const period = $(this).val();
                if (period) {
                    setDateRange(period);
                }
            });

            // Filter functions
            function setDateRange(period) {
                const today = new Date();
                let startDate, endDate;

                switch (period) {
                    case 'today':
                        startDate = endDate = today;
                        break;
                    case 'last_10_days':
                        endDate = new Date(today);
                        startDate = new Date(today);
                        startDate.setDate(today.getDate() - 9);
                        break;
                    case 'last_30_days':
                        endDate = new Date(today);
                        startDate = new Date(today);
                        startDate.setDate(today.getDate() - 29);
                        break;
                    case 'week':
                        startDate = new Date(today);
                        startDate.setDate(today.getDate() - today.getDay());
                        endDate = new Date(startDate);
                        endDate.setDate(startDate.getDate() + 6);
                        break;
                    case 'month':
                        startDate = new Date(today.getFullYear(), today.getMonth(), 1);
                        endDate = new Date(today.getFullYear(), today.getMonth() + 1, 0);
                        break;
                    case 'quarter':
                        const quarter = Math.floor(today.getMonth() / 3);
                        startDate = new Date(today.getFullYear(), quarter * 3, 1);
                        endDate = new Date(today.getFullYear(), (quarter + 1) * 3, 0);
                        break;
                    case 'year':
                        startDate = new Date(today.getFullYear(), 0, 1);
                        endDate = new Date(today.getFullYear(), 11, 31);
                        break;
                }

                if (startDate && endDate) {
                    $('#start-date').val(startDate.toISOString().split('T')[0]);
                    $('#end-date').val(endDate.toISOString().split('T')[0]);
                }
            }

            function switchReportType(reportType) {
                // Hide all tables and summaries
                $('#sales-report-table, #collection-report-table, #purchase-report-table').hide();
                $('#sales-summary, #collection-summary, #purchase-summary, #cashflow-summary').hide();
                $('#cashflow-chart-card').hide();
                $('#sales-insights-card').hide();
                
                // Show/hide filters based on report type
                $('.sales-filters, .collection-filters, .amount-filters').hide();
                
                switch (reportType) {
                    case 'sales':
                        $('#sales-report-table').show();
                        $('#sales-summary').show();
                        $('#sales-insights-card').show();
                        $('.sales-filters, .amount-filters').show();
                        $('#report-title').text('Sales Report Data');
                        if (!salesTable) initializeSalesTable();
                        else salesTable.ajax.reload();
                        loadSalesInsights();
                        break;
                    case 'collections':
                        $('#collection-report-table').show();
                        $('#collection-summary').show();
                        $('#sales-insights-card').hide();
                        $('.collection-filters, .amount-filters').show();
                        $('#report-title').text('Collection Report Data');
                        if (!collectionTable) initializeCollectionTable();
                        else collectionTable.ajax.reload();
                        break;
                    case 'purchases':
                        $('#purchase-report-table').show();
                        $('#purchase-summary').show();
                        $('#sales-insights-card').hide();
                        $('.amount-filters').show();
                        $('#report-title').text('Purchase Report Data');
                        if (!purchaseTable) initializePurchaseTable();
                        else purchaseTable.ajax.reload();
                        break;
                    case 'cashflow':
                        $('#cashflow-summary').show();
                        $('#cashflow-chart-card').show();
                        $('#sales-insights-card').hide();
                        $('#report-title').text('Cash Flow Analysis');
                        loadCashFlowData();
                        break;
                }
            }

            // Initialize Sales Table
            function initializeSalesTable() {
                salesTable = $('#sales-table').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: {
                        url: "{{ route('cash-flow.sales') }}",
                        data: function(d) {
                            d.start_date = $('#start-date').val();
                            d.end_date = $('#end-date').val();
                            d.payment_status = $('#payment-status-filter').val();
                            d.delivery_status = $('#delivery-status-filter').val();
                            d.invoice_type = $('#invoice-type-filter').val();
                            d.min_amount = $('#min-amount').val();
                            d.max_amount = $('#max-amount').val();
                        }
                    },
                    columns: [
                        {data: 'invoice_number', name: 'invoice_number'},
                        {data: 'invoice_date', name: 'invoice_date'},
                        {data: 'customer_name', name: 'customer_name'},
                        {data: 'customer_phone', name: 'customer_phone'},
                        {data: 'invoice_type', name: 'invoice_type'},
                        {data: 'total', name: 'total'},
                        {data: 'paid_amount', name: 'paid_amount'},
                        {data: 'due_amount', name: 'due_amount'},
                        {data: 'payment_status_badge', name: 'payment_status', orderable: false},
                        {data: 'delivery_status_badge', name: 'delivery_status', orderable: false}
                    ],
                    order: [[1, 'desc']],
                    drawCallback: function(settings) {
                        var response = this.api().ajax.json();
                        if (response.summary) {
                            updateSalesSummary(response.summary);
                        }
                    }
                });
            }

            // Initialize Collection Table
            function initializeCollectionTable() {
                collectionTable = $('#collection-table').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: {
                        url: "{{ route('cash-flow.collections') }}",
                        data: function(d) {
                            d.start_date = $('#start-date').val();
                            d.end_date = $('#end-date').val();
                            d.payment_method = $('#payment-method-filter').val();
                            d.min_amount = $('#min-amount').val();
                            d.max_amount = $('#max-amount').val();
                        }
                    },
                    columns: [
                        {data: 'created_at', name: 'created_at'},
                        {data: 'customer_name', name: 'customer_name'},
                        {data: 'customer_phone', name: 'customer_phone'},
                        {data: 'purpose', name: 'purpose'},
                        {data: 'method_badge', name: 'method', orderable: false},
                        {data: 'amount', name: 'amount'},
                        {data: 'discount_amount', name: 'discount_amount'},
                        {data: 'total_received', name: 'total_received', orderable: false},
                        {data: 'reference', name: 'reference'}
                    ],
                    order: [[0, 'desc']],
                    drawCallback: function(settings) {
                        var response = this.api().ajax.json();
                        if (response.summary) {
                            updateCollectionSummary(response.summary);
                        }
                    }
                });
            }

            // Initialize Purchase Table
            function initializePurchaseTable() {
                purchaseTable = $('#purchase-table').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: {
                        url: "{{ route('cash-flow.purchases') }}",
                        data: function(d) {
                            d.start_date = $('#start-date').val();
                            d.end_date = $('#end-date').val();
                            d.min_amount = $('#min-amount').val();
                            d.max_amount = $('#max-amount').val();
                        }
                    },
                    columns: [
                        {data: 'purchase_date', name: 'purchase_date'},
                        {data: 'invoice_no', name: 'invoice_no'},
                        {data: 'company_name', name: 'company_name'},
                        {data: 'items_count', name: 'items_count', orderable: false},
                        {data: 'total_amount', name: 'total_amount'},
                        {data: 'notes', name: 'notes'}
                    ],
                    order: [[0, 'desc']],
                    drawCallback: function(settings) {
                        var response = this.api().ajax.json();
                        if (response.summary) {
                            updatePurchaseSummary(response.summary);
                        }
                    }
                });
            }

            // Load Cash Flow Data
            function loadCashFlowData() {
                $.ajax({
                    url: "{{ route('cash-flow.summary') }}",
                    data: {
                        start_date: $('#start-date').val(),
                        end_date: $('#end-date').val(),
                        period: $('#period-filter').val()
                    },
                    success: function(data) {
                        updateCashFlowSummary(data);
                        updateCashFlowChart(data.daily_data);
                    }
                });
            }

            function loadSalesInsights() {
                $.ajax({
                    url: "{{ route('cash-flow.sales-insights') }}",
                    data: {
                        start_date: $('#start-date').val(),
                        end_date: $('#end-date').val(),
                        invoice_type: $('#invoice-type-filter').val()
                    },
                    success: function(data) {
                        $('#sales-footfall').text(data.footfall ?? 0);
                        $('#sales-basket-size').text('৳' + numberFormat(data.basket_size ?? 0));
                        $('#sales-total-insights').text('৳' + numberFormat(data.total_sales ?? 0));
                        $('#sales-insights-range').text(`${data.period.start} to ${data.period.end}`);

                        updateHourlyTable(data.hourly);
                    }
                });
            }

            function updateHourlyTable(hourly) {
                const table = $('#hourly-sales-table');
                if (!table.length) return;

                $('#hourly-sales-loading').hide();
                const headerRow = $('#hourly-sales-header');
                const valueRow = $('#hourly-sales-values');

                headerRow.empty().append('<th>Hour</th>');
                valueRow.empty().append('<td>Sales</td>');

                hourly.labels.forEach((label, idx) => {
                    const value = hourly.totals[idx] ?? 0;
                    headerRow.append(`<th>${label}</th>`);
                    valueRow.append(`<td>৳${numberFormat(value)}</td>`);
                });
            }

            // Update summary functions
            function updateSalesSummary(summary) {
                $('#sales-total-amount').text('৳' + numberFormat(summary.total_sales));
                $('#sales-collected-amount').text('৳' + numberFormat(summary.total_collected));
                $('#sales-due-amount').text('৳' + numberFormat(summary.total_due));
                $('#sales-count').text(summary.count);
                $('#sales-average').text('৳' + numberFormat(summary.average_sale));
            }

            function updateCollectionSummary(summary) {
                $('#collection-total-amount').text('৳' + numberFormat(summary.total_amount));
                $('#collection-discount-amount').text('৳' + numberFormat(summary.total_discount));
                $('#collection-count').text(summary.count);
                $('#collection-average').text('৳' + numberFormat(summary.average_collection));
            }

            function updatePurchaseSummary(summary) {
                $('#purchase-total-amount').text('৳' + numberFormat(summary.total_amount));
                $('#purchase-count').text(summary.count);
                $('#purchase-average').text('৳' + numberFormat(summary.average_purchase));
            }

            function updateCashFlowSummary(data) {
                $('#cashflow-inflows').text('৳' + numberFormat(data.cash_flow.total_inflows));
                $('#cashflow-outflows').text('৳' + numberFormat(data.cash_flow.total_outflows));
                $('#cashflow-net').text('৳' + numberFormat(data.cash_flow.net_cash_flow));
            }

            // Update Cash Flow Chart
            function updateCashFlowChart(dailyData) {
                const ctx = document.getElementById('cashflow-chart').getContext('2d');
                
                if (cashFlowChart) {
                    cashFlowChart.destroy();
                }

                cashFlowChart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: dailyData.map(d => d.date),
                        datasets: [{
                            label: 'Sales',
                            data: dailyData.map(d => d.sales),
                            borderColor: '#6366f1',
                            backgroundColor: 'rgba(99, 102, 241, 0.1)',
                            tension: 0.4
                        }, {
                            label: 'Collections',
                            data: dailyData.map(d => d.collections),
                            borderColor: '#10b981',
                            backgroundColor: 'rgba(16, 185, 129, 0.1)',
                            tension: 0.4
                        }, {
                            label: 'Purchases',
                            data: dailyData.map(d => d.purchases),
                            borderColor: '#ef4444',
                            backgroundColor: 'rgba(239, 68, 68, 0.1)',
                            tension: 0.4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    callback: function(value) {
                                        return '৳' + numberFormat(value);
                                    }
                                }
                            }
                        },
                        plugins: {
                            legend: {
                                position: 'top',
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        return context.dataset.label + ': ৳' + numberFormat(context.parsed.y);
                                    }
                                }
                            }
                        }
                    }
                });
            }

            // Apply filters
            $('#apply-filters').on('click', function() {
                switch (currentReportType) {
                    case 'sales':
                        if (salesTable) salesTable.ajax.reload();
                        loadSalesInsights();
                        break;
                    case 'collections':
                        if (collectionTable) collectionTable.ajax.reload();
                        break;
                    case 'purchases':
                        if (purchaseTable) purchaseTable.ajax.reload();
                        break;
                    case 'cashflow':
                        loadCashFlowData();
                        break;
                }
                toastr.success('Filters applied successfully');
            });

            $('.quick-range-btn').on('click', function() {
                const range = $(this).data('range');
                $('#period-filter').val('');
                setDateRange(range);
                $('#apply-filters').click();
            });

            // Reset filters
            $('#reset-filters').on('click', function() {
                $('#payment-status-filter').val('');
                $('#delivery-status-filter').val('');
                $('#invoice-type-filter').val('');
                $('#payment-method-filter').val('');
                $('#min-amount').val('');
                $('#max-amount').val('');
                $('#period-filter').val('month');
                setDateRange('month');
                
                $('#apply-filters').click();
                toastr.info('Filters reset successfully');
            });

            // Print Report
            $('#print-report').on('click', function() {
                // Set print date
                const printDate = new Date().toLocaleString('en-US', {
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit',
                    hour12: true
                });
                $('body').attr('data-print-date', printDate);
                
                // Add print title based on current report
                let printTitle = '';
                switch (currentReportType) {
                    case 'sales':
                        printTitle = 'Sales Report';
                        break;
                    case 'collections':
                        printTitle = 'Collection Report';
                        break;
                    case 'purchases':
                        printTitle = 'Purchase Report';
                        break;
                    case 'cashflow':
                        printTitle = 'Cash Flow Summary';
                        break;
                }
                
                // Update page title for print
                const originalTitle = document.title;
                document.title = printTitle + ' - ' + printDate;
                
                // Print
                window.print();
                
                // Restore original title
                setTimeout(() => {
                    document.title = originalTitle;
                }, 1000);
            });

            // Export Report
            $('#export-report').on('click', function() {
                let exportUrl;
                switch (currentReportType) {
                    case 'sales':
                        exportUrl = "{{ route('cash-flow.export-sales') }}";
                        break;
                    case 'collections':
                        exportUrl = "{{ route('cash-flow.export-collections') }}";
                        break;
                    case 'purchases':
                        exportUrl = "{{ route('cash-flow.export-purchases') }}";
                        break;
                    case 'cashflow':
                        exportUrl = "{{ route('cash-flow.export-cashflow') }}";
                        break;
                }

                if (exportUrl) {
                    const form = $('<form>', {
                        'method': 'GET',
                        'action': exportUrl
                    });

                    // Add current filter parameters
                    const filters = {
                        start_date: $('#start-date').val(),
                        end_date: $('#end-date').val(),
                        payment_status: $('#payment-status-filter').val(),
                        delivery_status: $('#delivery-status-filter').val(),
                        invoice_type: $('#invoice-type-filter').val(),
                        payment_method: $('#payment-method-filter').val(),
                        min_amount: $('#min-amount').val(),
                        max_amount: $('#max-amount').val(),
                        period: $('#period-filter').val()
                    };

                    $.each(filters, function(key, value) {
                        if (value) {
                            form.append($('<input>', {
                                'type': 'hidden',
                                'name': key,
                                'value': value
                            }));
                        }
                    });

                    $('body').append(form);
                    form.submit();
                    form.remove();

                    toastr.success('Export started. Download will begin shortly.');
                }
            });

            // Filter card collapse/expand
            $('[data-card-widget="collapse"]').on('click', function() {
                var icon = $(this).find('i');
                var cardBody = $(this).closest('.card').find('.card-body');
                
                if (cardBody.is(':visible')) {
                    cardBody.slideUp(300);
                    icon.removeClass('fa-minus').addClass('fa-plus');
                } else {
                    cardBody.slideDown(300);
                    icon.removeClass('fa-plus').addClass('fa-minus');
                }
            });

            // Helper function for number formatting
            function numberFormat(number) {
                return new Intl.NumberFormat('en-US', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }).format(number);
            }

            // Initialize default report
            initializeSalesTable();
            $('#sales-insights-card').show();
            loadSalesInsights();
        });
    </script>
@stop
