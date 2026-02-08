@extends('layouts.modern-admin')

@section('title', 'Product Reports')

@section('page_title', 'Product Reports')

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
                <div class="report-type-option active" data-report="stock">
                    <div class="report-icon">
                        <i class="fas fa-boxes"></i>
                    </div>
                    <h4>Stock Report</h4>
                    <p>View current stock levels, stock differences, and stock status</p>
                </div>
                <div class="report-type-option" data-report="value">
                    <div class="report-icon">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                    <h4>Stock Value Report</h4>
                    <p>View stock values based on purchase/sale prices and profit margins</p>
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
                <div class="filter-group">
                    <label for="name-filter" class="filter-label">Product Name</label>
                    <input type="text" id="name-filter" class="form-control modern-input" placeholder="Search by name...">
                </div>
                <div class="filter-group">
                    <label for="company-filter" class="filter-label">Company</label>
                    <select id="company-filter" class="form-control modern-select">
                        <option value="">All Companies</option>
                        @foreach($companies as $company)
                            <option value="{{ $company->id }}">{{ $company->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="filter-group">
                    <label for="category-filter" class="filter-label">Category</label>
                    <select id="category-filter" class="form-control modern-select">
                        <option value="">All Categories</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                @if($godowns->isNotEmpty())
                    <div class="filter-group">
                        <label for="godown-filter" class="filter-label">Godown</label>
                        <select id="godown-filter" class="form-control modern-select">
                            <option value="">All Godowns</option>
                            @foreach($godowns as $godown)
                                <option value="{{ $godown->id }}">{{ $godown->name }}{{ $godown->location ? ' - ' . $godown->location : '' }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif
                <div class="filter-group">
                    <label for="stock-filter" class="filter-label">Stock Status</label>
                    <select id="stock-filter" class="form-control modern-select">
                        <option value="">All Stock Status</option>
                        <option value="in_stock">In Stock</option>
                        <option value="low_stock">Low Stock</option>
                        <option value="out_of_stock">Out of Stock</option>
                    </select>
                </div>
                
                <!-- Stock-specific filters -->
                <div class="filter-group stock-filters">
                    <label for="min-stock" class="filter-label">Min Stock Quantity</label>
                    <input type="number" id="min-stock" class="form-control modern-input" placeholder="Min stock...">
                </div>
                <div class="filter-group stock-filters">
                    <label for="max-stock" class="filter-label">Max Stock Quantity</label>
                    <input type="number" id="max-stock" class="form-control modern-input" placeholder="Max stock...">
                </div>
                
                <!-- Value-specific filters -->
                <div class="filter-group value-filters" style="display: none;">
                    <label for="price-type" class="filter-label">Price Type</label>
                    <select id="price-type" class="form-control modern-select">
                        <option value="sale_price">Sale Price</option>
                        <option value="purchase_price">Purchase Price</option>
                    </select>
                </div>
                <div class="filter-group value-filters" style="display: none;">
                    <label for="min-value" class="filter-label">Min Stock Value</label>
                    <input type="number" id="min-value" class="form-control modern-input" placeholder="Min value...">
                </div>
                <div class="filter-group value-filters" style="display: none;">
                    <label for="max-value" class="filter-label">Max Stock Value</label>
                    <input type="number" id="max-value" class="form-control modern-input" placeholder="Max value...">
                </div>
                
                <div class="filter-group">
                    <label for="date-range" class="filter-label">Created Date Range</label>
                    <div class="input-group modern-input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text modern-input-addon">
                                <i class="far fa-calendar-alt"></i>
                            </span>
                        </div>
                        <input type="text" id="date-range" class="form-control modern-input" placeholder="Select date range">
                    </div>
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
            <div class="summary-grid" id="stock-summary">
                <div class="summary-item">
                    <div class="summary-icon">
                        <i class="fas fa-boxes"></i>
                    </div>
                    <div class="summary-content">
                        <span class="summary-label">Total Products</span>
                        <span class="summary-value" id="total-products">-</span>
                    </div>
                </div>
                <div class="summary-item">
                    <div class="summary-icon in-stock">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="summary-content">
                        <span class="summary-label">In Stock</span>
                        <span class="summary-value" id="in-stock-count">-</span>
                    </div>
                </div>
                <div class="summary-item">
                    <div class="summary-icon low-stock">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div class="summary-content">
                        <span class="summary-label">Low Stock</span>
                        <span class="summary-value" id="low-stock-count">-</span>
                    </div>
                </div>
                <div class="summary-item">
                    <div class="summary-icon out-stock">
                        <i class="fas fa-exclamation-circle"></i>
                    </div>
                    <div class="summary-content">
                        <span class="summary-label">Out of Stock</span>
                        <span class="summary-value" id="out-stock-count">-</span>
                    </div>
                </div>
                <div class="summary-item">
                    <div class="summary-icon">
                        <i class="fas fa-warehouse"></i>
                    </div>
                    <div class="summary-content">
                        <span class="summary-label">Total Stock Qty</span>
                        <span class="summary-value" id="total-stock-qty">-</span>
                    </div>
                </div>
            </div>
            
            <div class="summary-grid" id="value-summary" style="display: none;">
                <div class="summary-item">
                    <div class="summary-icon">
                        <i class="fas fa-boxes"></i>
                    </div>
                    <div class="summary-content">
                        <span class="summary-label">Total Products</span>
                        <span class="summary-value" id="value-total-products">-</span>
                    </div>
                </div>
                <div class="summary-item">
                    <div class="summary-icon">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                    <div class="summary-content">
                        <span class="summary-label">Total Stock Value</span>
                        <span class="summary-value" id="total-stock-value">-</span>
                    </div>
                </div>
                <div class="summary-item">
                    <div class="summary-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div class="summary-content">
                        <span class="summary-label">Average Value</span>
                        <span class="summary-value" id="average-value">-</span>
                    </div>
                </div>
                <div class="summary-item">
                    <div class="summary-icon">
                        <i class="fas fa-arrow-up"></i>
                    </div>
                    <div class="summary-content">
                        <span class="summary-label">Max Value</span>
                        <span class="summary-value" id="max-value">-</span>
                    </div>
                </div>
                <div class="summary-item">
                    <div class="summary-icon">
                        <i class="fas fa-arrow-down"></i>
                    </div>
                    <div class="summary-content">
                        <span class="summary-label">Min Value</span>
                        <span class="summary-value" id="min-value">-</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Report Data Card -->
    <div class="card modern-card">
        <div class="card-header modern-header">
            <div class="header-content">
                <div class="header-title">
                    <i class="fas fa-table header-icon"></i>
                    <h3 class="card-title" id="report-title">Stock Report Data</h3>
                </div>
            </div>
        </div>
        
        <div class="card-body modern-card-body">
            <div class="table-container" id="printable-area">
                <!-- Stock Report Table -->
                <div class="table-responsive modern-table-responsive" id="stock-report-table">
                    <table class="table modern-table" id="stock-table">
                        <thead class="modern-thead">
                            <tr>
                                <th>ID</th>
                                <th>Product Name</th>
                                <th>Company</th>
                                <th>Category</th>
                                <th>Opening Stock</th>
                                <th>Current Stock</th>
                                <th>Stock Difference</th>
                                <th>Stock Status</th>
                                <th>Purchase Price</th>
                                <th>Sale Price</th>
                            </tr>
                        </thead>
                        <tbody class="modern-tbody">
                            <!-- DataTable will populate this -->
                        </tbody>
                    </table>
                </div>

                <!-- Stock Value Report Table -->
                <div class="table-responsive modern-table-responsive" id="value-report-table" style="display: none;">
                    <table class="table modern-table" id="value-table">
                        <thead class="modern-thead">
                            <tr>
                                <th>ID</th>
                                <th>Product Name</th>
                                <th>Company</th>
                                <th>Category</th>
                                <th>Current Stock</th>
                                <th>Purchase Price</th>
                                <th>Sale Price</th>
                                <th>Stock Value (Purchase)</th>
                                <th>Stock Value (Sale)</th>
                                <th>Potential Profit</th>
                                <th>Profit Margin %</th>
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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.min.css">
    
    <style>
        /* Report Type Selector Styles */
        .report-selector-card {
            margin-bottom: 24px;
        }

        .report-type-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
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

        /* Summary Card Styles */
        .summary-card {
            margin-bottom: 24px;
        }

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

        .summary-icon {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .summary-icon i {
            font-size: 20px;
            color: white;
        }

        .summary-icon.in-stock {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        }

        .summary-icon.low-stock {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        }

        .summary-icon.out-stock {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        }

        .summary-content {
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .summary-label {
            font-size: 12px;
            color: #6b7280;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 4px;
        }

        .summary-value {
            font-size: 24px;
            font-weight: 700;
            color: #374151;
        }

        /* Enhanced Filter Styles */
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

        .modern-input-group {
            position: relative;
        }

        .modern-input-addon {
            background: #f8fafc;
            border: 2px solid #e5e7eb;
            border-right: none;
            color: #6b7280;
        }

        .filter-actions {
            display: flex;
            gap: 12px;
            align-items: center;
        }

        /* Header Actions */
        .header-actions-group {
            display: flex;
            gap: 12px;
            align-items: center;
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
            
            .modern-tbody tr:hover {
                background: white !important;
            }
            
            .btn, .header-actions-group {
                display: none;
            }
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
            padding: 18px 16px;
            font-weight: 600;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: white !important;
            position: relative;
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

        /* Stock Status Badges */
        .stock-badge {
            min-width: 100px;
            text-align: center;
            font-size: 12px;
            font-weight: 600;
            padding: 8px 16px;
            border-radius: 25px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border: none;
            transition: all 0.3s ease;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
        }

        .stock-out {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: white;
        }

        .stock-low {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: white;
        }

        .stock-good {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
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
    </style>
@stop

@section('additional_js')
    <script src="https://cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.25/js/dataTables.bootstrap4.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/moment/moment.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"></script>
    
    <script>
        $(document).ready(function() {
            let currentReportType = 'stock';
            let stockTable, valueTable;

            // Configure toastr
            toastr.options = {
                "closeButton": true,
                "newestOnTop": true,
                "progressBar": true,
                "positionClass": "toast-top-right",
                "timeOut": "3000"
            };

            // Initialize date range picker
            $('#date-range').daterangepicker({
                autoUpdateInput: false,
                locale: {
                    cancelLabel: 'Clear'
                }
            });

            $('#date-range').on('apply.daterangepicker', function(ev, picker) {
                $(this).val(picker.startDate.format('YYYY-MM-DD') + ' - ' + picker.endDate.format('YYYY-MM-DD'));
            });

            $('#date-range').on('cancel.daterangepicker', function(ev, picker) {
                $(this).val('');
            });

            // Report type selector
            $('.report-type-option').on('click', function() {
                $('.report-type-option').removeClass('active');
                $(this).addClass('active');
                
                currentReportType = $(this).data('report');
                
                if (currentReportType === 'stock') {
                    $('.stock-filters').show();
                    $('.value-filters').hide();
                    $('#stock-summary').show();
                    $('#value-summary').hide();
                    $('#stock-report-table').show();
                    $('#value-report-table').hide();
                    $('#report-title').text('Stock Report Data');
                    
                    if (stockTable) {
                        stockTable.ajax.reload();
                    } else {
                        initializeStockTable();
                    }
                } else {
                    $('.stock-filters').hide();
                    $('.value-filters').show();
                    $('#stock-summary').hide();
                    $('#value-summary').show();
                    $('#stock-report-table').hide();
                    $('#value-report-table').show();
                    $('#report-title').text('Stock Value Report Data');
                    
                    if (valueTable) {
                        valueTable.ajax.reload();
                    } else {
                        initializeValueTable();
                    }
                }
                
                toastr.info('Switched to ' + (currentReportType === 'stock' ? 'Stock' : 'Stock Value') + ' Report');
            });

            // Initialize Stock Report Table
            function initializeStockTable() {
                stockTable = $('#stock-table').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: {
                        url: "{{ route('products.reports.stock') }}",
                        data: function(d) {
                            d.name = $('#name-filter').val();
                            d.company_id = $('#company-filter').val();
                            d.category_id = $('#category-filter').val();
                            d.godown_id = $('#godown-filter').length ? $('#godown-filter').val() : '';
                            d.stock_status = $('#stock-filter').val();
                            d.min_stock = $('#min-stock').val();
                            d.max_stock = $('#max-stock').val();
                            
                            var dateRange = $('#date-range').val();
                            if (dateRange) {
                                var dates = dateRange.split(' - ');
                                d.start_date = dates[0];
                                d.end_date = dates.length > 1 ? dates[1] : dates[0];
                            }
                        }
                    },
                    columns: [
                        {data: 'id', name: 'id'},
                        {data: 'name', name: 'name'},
                        {data: 'company_name', name: 'company_name'},
                        {data: 'category_name', name: 'category_name'},
                        {data: 'opening_stock', name: 'opening_stock'},
                        {data: 'current_stock', name: 'current_stock'},
                        {data: 'stock_difference', name: 'stock_difference'},
                        {data: 'stock_status', name: 'stock_status'},
                        {data: 'purchase_price', name: 'purchase_price'},
                        {data: 'sale_price', name: 'sale_price'}
                    ],
                    order: [[0, 'desc']],
                    drawCallback: function(settings) {
                        var response = this.api().ajax.json();
                        if (response.summary) {
                            updateStockSummary(response.summary);
                        }
                        formatStockTable();
                    }
                });
            }

            // Initialize Value Report Table
            function initializeValueTable() {
                valueTable = $('#value-table').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: {
                        url: "{{ route('products.reports.value') }}",
                        data: function(d) {
                            d.name = $('#name-filter').val();
                            d.company_id = $('#company-filter').val();
                            d.category_id = $('#category-filter').val();
                            d.godown_id = $('#godown-filter').length ? $('#godown-filter').val() : '';
                            d.stock_status = $('#stock-filter').val();
                            d.price_type = $('#price-type').val();
                            d.min_value = $('#min-value').val();
                            d.max_value = $('#max-value').val();
                            
                            var dateRange = $('#date-range').val();
                            if (dateRange) {
                                var dates = dateRange.split(' - ');
                                d.start_date = dates[0];
                                d.end_date = dates.length > 1 ? dates[1] : dates[0];
                            }
                        }
                    },
                    columns: [
                        {data: 'id', name: 'id'},
                        {data: 'name', name: 'name'},
                        {data: 'company_name', name: 'company_name'},
                        {data: 'category_name', name: 'category_name'},
                        {data: 'current_stock', name: 'current_stock'},
                        {data: 'purchase_price', name: 'purchase_price'},
                        {data: 'sale_price', name: 'sale_price'},
                        {data: 'stock_value_purchase', name: 'stock_value_purchase'},
                        {data: 'stock_value_sale', name: 'stock_value_sale'},
                        {data: 'potential_profit', name: 'potential_profit'},
                        {data: 'profit_margin', name: 'profit_margin'}
                    ],
                    order: [[0, 'desc']],
                    drawCallback: function(settings) {
                        var response = this.api().ajax.json();
                        if (response.summary) {
                            updateValueSummary(response.summary);
                        }
                        formatValueTable();
                    }
                });
            }

            // Update Stock Summary
            function updateStockSummary(summary) {
                $('#total-products').text(summary.total_products || 0);
                $('#in-stock-count').text(summary.in_stock_count || 0);
                $('#low-stock-count').text(summary.low_stock_count || 0);
                $('#out-stock-count').text(summary.out_of_stock_count || 0);
                $('#total-stock-qty').text(summary.total_stock_qty || 0);
            }

            // Update Value Summary
            function updateValueSummary(summary) {
                $('#value-total-products').text(summary.total_products || 0);
                $('#total-stock-value').text('৳' + (summary.total_stock_value || 0).toFixed(2));
                $('#average-value').text('৳' + (summary.average_value || 0).toFixed(2));
                $('#max-value').text('৳' + (summary.max_value || 0).toFixed(2));
                $('#min-value').text('৳' + (summary.min_value || 0).toFixed(2));
            }

            // Format Stock Table
            function formatStockTable() {
                $('#stock-table tbody tr').each(function() {
                    var stockStatusCell = $(this).find('td:eq(7)');
                    var stockDiffCell = $(this).find('td:eq(6)');
                    var purchasePriceCell = $(this).find('td:eq(8)');
                    var salePriceCell = $(this).find('td:eq(9)');
                    
                    // Format stock status
                    var status = stockStatusCell.text().trim();
                    if (status === 'Out of Stock') {
                        stockStatusCell.html('<span class="stock-badge stock-out">' + status + '</span>');
                    } else if (status === 'Low Stock') {
                        stockStatusCell.html('<span class="stock-badge stock-low">' + status + '</span>');
                    } else if (status === 'In Stock') {
                        stockStatusCell.html('<span class="stock-badge stock-good">' + status + '</span>');
                    }
                    
                    // Format stock difference
                    var diff = parseInt(stockDiffCell.text().trim());
                    if (!isNaN(diff)) {
                        var color = diff > 0 ? '#10b981' : (diff < 0 ? '#ef4444' : '#6b7280');
                        var icon = diff > 0 ? 'fa-arrow-up' : (diff < 0 ? 'fa-arrow-down' : 'fa-minus');
                        stockDiffCell.html('<span style="color: ' + color + ';"><i class="fas ' + icon + '"></i> ' + diff + '</span>');
                    }
                    
                    // Format prices
                    var purchasePrice = purchasePriceCell.text().trim();
                    var salePrice = salePriceCell.text().trim();
                    
                    if (purchasePrice && !isNaN(purchasePrice)) {
                        purchasePriceCell.html('৳' + parseFloat(purchasePrice).toFixed(2));
                    }
                    
                    if (salePrice && !isNaN(salePrice)) {
                        salePriceCell.html('৳' + parseFloat(salePrice).toFixed(2));
                    }
                });
            }

            // Format Value Table
            function formatValueTable() {
                $('#value-table tbody tr').each(function() {
                    var purchasePriceCell = $(this).find('td:eq(5)');
                    var salePriceCell = $(this).find('td:eq(6)');
                    var stockValuePurchaseCell = $(this).find('td:eq(7)');
                    var stockValueSaleCell = $(this).find('td:eq(8)');
                    var potentialProfitCell = $(this).find('td:eq(9)');
                    var profitMarginCell = $(this).find('td:eq(10)');
                    
                    // Format prices and values
                    var purchasePrice = purchasePriceCell.text().trim();
                    var salePrice = salePriceCell.text().trim();
                    var stockValuePurchase = stockValuePurchaseCell.text().trim();
                    var stockValueSale = stockValueSaleCell.text().trim();
                    var potentialProfit = potentialProfitCell.text().trim();
                    var profitMargin = profitMarginCell.text().trim();
                    
                    if (purchasePrice && !isNaN(purchasePrice)) {
                        purchasePriceCell.html('৳' + parseFloat(purchasePrice).toFixed(2));
                    }
                    
                    if (salePrice && !isNaN(salePrice)) {
                        salePriceCell.html('৳' + parseFloat(salePrice).toFixed(2));
                    }
                    
                    if (stockValuePurchase && !isNaN(stockValuePurchase)) {
                        stockValuePurchaseCell.html('৳' + parseFloat(stockValuePurchase).toFixed(2));
                    }
                    
                    if (stockValueSale && !isNaN(stockValueSale)) {
                        stockValueSaleCell.html('৳' + parseFloat(stockValueSale).toFixed(2));
                    }
                    
                    if (potentialProfit && !isNaN(potentialProfit)) {
                        var profit = parseFloat(potentialProfit);
                        var color = profit > 0 ? '#10b981' : (profit < 0 ? '#ef4444' : '#6b7280');
                        var icon = profit > 0 ? 'fa-arrow-up' : (profit < 0 ? 'fa-arrow-down' : 'fa-minus');
                        potentialProfitCell.html('<span style="color: ' + color + ';"><i class="fas ' + icon + '"></i> ৳' + Math.abs(profit).toFixed(2) + '</span>');
                    }
                    
                    if (profitMargin && !isNaN(profitMargin)) {
                        var margin = parseFloat(profitMargin);
                        var color = margin > 0 ? '#10b981' : (margin < 0 ? '#ef4444' : '#6b7280');
                        profitMarginCell.html('<span style="color: ' + color + ';">' + margin.toFixed(2) + '%</span>');
                    }
                });
            }

            // Apply filters
            $('#apply-filters').on('click', function() {
                if (currentReportType === 'stock' && stockTable) {
                    stockTable.ajax.reload();
                } else if (currentReportType === 'value' && valueTable) {
                    valueTable.ajax.reload();
                }
                toastr.success('Filters applied successfully');
            });

            // Reset filters
            $('#reset-filters').on('click', function() {
                $('#name-filter').val('');
                $('#company-filter').val('');
                $('#category-filter').val('');
                $('#godown-filter').val('');
                $('#stock-filter').val('');
                $('#min-stock').val('');
                $('#max-stock').val('');
                $('#price-type').val('sale_price');
                $('#min-value').val('');
                $('#max-value').val('');
                $('#date-range').val('');
                
                if (currentReportType === 'stock' && stockTable) {
                    stockTable.ajax.reload();
                } else if (currentReportType === 'value' && valueTable) {
                    valueTable.ajax.reload();
                }
                toastr.info('Filters reset successfully');
            });

            // Print Report
            $('#print-report').on('click', function() {
                var printWindow = window.open('', '_blank');
                var printContent = $('#printable-area').html();
                var reportTitle = currentReportType === 'stock' ? 'Stock Report' : 'Stock Value Report';
                
                printWindow.document.write(`
                    <html>
                        <head>
                            <title>${reportTitle}</title>
                            <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css">
                            <style>
                                body { font-family: Arial, sans-serif; }
                                .report-header { text-align: center; margin-bottom: 30px; }
                                .report-header h1 { color: #6366f1; margin-bottom: 10px; }
                                .report-header p { color: #6b7280; margin: 0; }
                                table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                                th, td { border: 1px solid #e5e7eb; padding: 8px; text-align: left; }
                                th { background-color: #f8f9fa; font-weight: 600; }
                                .stock-badge { padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: 600; }
                                .stock-good { background-color: #d1fae5; color: #065f46; }
                                .stock-low { background-color: #fef3c7; color: #92400e; }
                                .stock-out { background-color: #fee2e2; color: #991b1b; }
                                @media print {
                                    body { margin: 0; }
                                    .no-print { display: none; }
                                }
                            </style>
                        </head>
                        <body>
                            <div class="report-header">
                                <h1>${reportTitle}</h1>
                                <p>Generated on ${new Date().toLocaleDateString()}</p>
                            </div>
                            ${printContent}
                        </body>
                    </html>
                `);
                
                printWindow.document.close();
                printWindow.focus();
                setTimeout(() => {
                    printWindow.print();
                    printWindow.close();
                }, 500);
            });

            // Export to Excel
            $('#export-report').on('click', function() {
                var exportUrl = currentReportType === 'stock' ? 
                    "{{ route('products.reports.stock.export') }}" : 
                    "{{ route('products.reports.value.export') }}";
                
                // Collect current filter values
                var filters = {
                    name: $('#name-filter').val(),
                    company_id: $('#company-filter').val(),
                    category_id: $('#category-filter').val(),
                    godown_id: $('#godown-filter').length ? $('#godown-filter').val() : '',
                    stock_status: $('#stock-filter').val(),
                    min_stock: $('#min-stock').val(),
                    max_stock: $('#max-stock').val(),
                    price_type: $('#price-type').val(),
                    min_value: $('#min-value').val(),
                    max_value: $('#max-value').val()
                };
                
                var dateRange = $('#date-range').val();
                if (dateRange) {
                    var dates = dateRange.split(' - ');
                    filters.start_date = dates[0];
                    filters.end_date = dates.length > 1 ? dates[1] : dates[0];
                }
                
                // Create form and submit
                var form = $('<form>', {
                    'method': 'GET',
                    'action': exportUrl
                });
                
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

            // Initialize stock table by default
            initializeStockTable();

            // Auto-refresh summary every 30 seconds
            setInterval(function() {
                if (currentReportType === 'stock' && stockTable) {
                    stockTable.ajax.reload(null, false);
                } else if (currentReportType === 'value' && valueTable) {
                    valueTable.ajax.reload(null, false);
                }
            }, 30000);
        });
    </script>
@stop
