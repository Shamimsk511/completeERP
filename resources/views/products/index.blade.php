@extends('layouts.modern-admin')

@section('title', 'Products')

@section('page_title', 'Products')

@section('header_actions')
    <div class="header-actions-group">
        @can('product-edit')
            <a href="{{ route('products.reports.index') }}" class="btn modern-btn modern-btn-success">
                <i class="fas fa-chart-bar"></i> Product Reports
            </a>
            <button type="button" class="btn modern-btn modern-btn-warning" data-toggle="modal" data-target="#mergeDuplicatesModal">
                <i class="fas fa-object-group"></i> Merge Duplicates
            </button>
        @endcan
        <button type="button" class="btn modern-btn modern-btn-secondary" data-toggle="modal" data-target="#importModal">
            <i class="fas fa-file-import"></i> Import Products
        </button>
        <a href="{{ route('products.create') }}" class="btn modern-btn modern-btn-primary">
            <i class="fas fa-plus"></i> Add Product
        </a>
    </div>
@stop

@section('page_content')
    <!-- Success/Error Messages -->
    @if(session('success'))
        <div class="alert modern-alert modern-alert-success" id="success-alert">
            <div class="alert-content">
                <i class="fas fa-check-circle alert-icon"></i>
                <div class="alert-message">
                    <strong>Success!</strong>
                    <span>{{ session('success') }}</span>
                </div>
                <button type="button" class="alert-close" onclick="this.parentElement.parentElement.style.display='none'">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
    @endif
    
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

    @if(session('import_failures'))
        <div class="alert modern-alert modern-alert-warning" id="warning-alert">
            <div class="alert-content">
                <i class="fas fa-exclamation-triangle alert-icon"></i>
                <div class="alert-message">
                    <strong>Import Warning!</strong>
                    <span>{{ session('warning') }}</span>
                </div>
                <button type="button" class="alert-close" onclick="this.parentElement.parentElement.style.display='none'">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
    @endif

    <!-- Advanced Filters Card -->
    <div class="card modern-card filter-card collapsed-card">
        <div class="card-header modern-header header-contrast">
            <div class="header-content">
                <div class="header-title">
                    <i class="fas fa-filter header-icon"></i>
                    <h3 class="card-title">Advanced Filters</h3>
                </div>
                <button type="button" class="btn modern-btn modern-btn-outline" data-card-widget="collapse">
                    <i class="fas fa-plus"></i>
                </button>
            </div>
        </div>
        <div class="card-body modern-card-body" style="display: none;">
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
                <div class="filter-group">
                    <label for="stock-managed-filter" class="filter-label">Stock Managed</label>
                    <select id="stock-managed-filter" class="form-control modern-select">
                        <option value="">All</option>
                        <option value="managed">Managed</option>
                        <option value="unmanaged">Not Managed</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label for="min-stock" class="filter-label">Min Stock</label>
                    <input type="number" id="min-stock" class="form-control modern-input" placeholder="Min qty...">
                </div>
                <div class="filter-group">
                    <label for="max-stock" class="filter-label">Max Stock</label>
                    <input type="number" id="max-stock" class="form-control modern-input" placeholder="Max qty...">
                </div>
                <div class="filter-group">
                    <label for="min-price" class="filter-label">Min Sale Price</label>
                    <input type="number" id="min-price" class="form-control modern-input" placeholder="Min sale price...">
                </div>
                <div class="filter-group">
                    <label for="max-price" class="filter-label">Max Sale Price</label>
                    <input type="number" id="max-price" class="form-control modern-input" placeholder="Max sale price...">
                </div>
                <div class="filter-group">
                    <label for="min-purchase-price" class="filter-label">Min Purchase Price</label>
                    <input type="number" id="min-purchase-price" class="form-control modern-input" placeholder="Min purchase price...">
                </div>
                <div class="filter-group">
                    <label for="max-purchase-price" class="filter-label">Max Purchase Price</label>
                    <input type="number" id="max-purchase-price" class="form-control modern-input" placeholder="Max purchase price...">
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

    <!-- Main Content Card -->
    <div class="card modern-card">
        <div class="card-header modern-header header-contrast">
            <div class="header-content">
                <div class="header-title">
                    <i class="fas fa-boxes header-icon"></i>
                    <h3 class="card-title">All Products</h3>
                </div>
                <div class="header-stats">
                    <div class="stat-item">
                        <span class="stat-label">Total Products</span>
                        <span class="stat-value" id="total-count">-</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-label">Low Stock</span>
                        <span class="stat-value" id="low-stock-count">-</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-label">Out of Stock</span>
                        <span class="stat-value" id="out-stock-count">-</span>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card-body modern-card-body">
            <div class="products-layout">
                <div class="table-container">
                    <div class="table-responsive modern-table-responsive">
                        <table class="table modern-table" id="products-table">
                        <thead class="modern-thead">
                            <tr>
                                <th class="sortable">
                                    <div class="th-content">
                                        <span>Product Name</span>
                                        <i class="fas fa-sort sort-icon"></i>
                                    </div>
                                </th>
                                <th class="sortable">
                                    <div class="th-content">
                                        <span>Company</span>
                                        <i class="fas fa-sort sort-icon"></i>
                                    </div>
                                </th>
                                <th class="sortable">
                                    <div class="th-content">
                                        <span>Category</span>
                                        <i class="fas fa-sort sort-icon"></i>
                                    </div>
                                </th>
                                <th class="sortable">
                                    <div class="th-content">
                                        <span>Stock Status</span>
                                        <i class="fas fa-sort sort-icon"></i>
                                    </div>
                                </th>
                                <th class="sortable">
                                    <div class="th-content">
                                        <span>Price (Buy/Sell)</span>
                                        <i class="fas fa-sort sort-icon"></i>
                                    </div>
                                </th>
                                <th class="actions-column">
                                    <div class="th-content">
                                        <span>Actions</span>
                                    </div>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="modern-tbody">
                            <!-- DataTable will populate this -->
                        </tbody>
                        </table>
                    </div>
                </div>
                <div class="group-browser-rail">
                    <div class="group-browser-card">
                        <div class="group-browser-header">
                            <div class="group-browser-title">Browse Groups</div>
                            <div class="btn-group group-browser-toggle" role="group" aria-label="Browse products by group">
                                <button type="button" class="btn modern-btn modern-btn-outline group-browser-btn active" data-group="category">Category</button>
                                <button type="button" class="btn modern-btn modern-btn-outline group-browser-btn" data-group="company">Company</button>
                            </div>
                        </div>
                        <div class="group-browser-search">
                            <input type="text" id="group-browser-search" class="form-control modern-input" placeholder="Search categories...">
                        </div>
                        <div class="group-browser-hint">Click a group to filter the table.</div>
                        <div class="group-browser-list" id="group-browser-list">
                            <div class="text-muted small">Loading...</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <button type="button" class="group-browser-float-btn" title="Browse groups" aria-label="Browse groups">
        <i class="fas fa-layer-group"></i>
    </button>

    <!-- Import Modal -->
    <div class="modal fade modern-modal" id="importModal">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content modern-modal-content">
                <div class="modal-header modern-modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-file-import"></i>
                        Import Products
                    </h5>
                    <button type="button" class="close modern-close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body modern-modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <form action="{{ route('products.import') }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <div class="form-group">
                                    <label for="file" class="form-label">Select Excel File</label>
                                    <div class="custom-file modern-file-input">
                                        <input type="file" class="custom-file-input @error('file') is-invalid @enderror" 
                                               id="file" name="file" accept=".xlsx,.xls,.csv">
                                        <label class="custom-file-label" for="file">Choose file</label>
                                    </div>
                                    @error('file')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="form-actions">
                                    <button type="submit" class="btn modern-btn modern-btn-primary">
                                        <i class="fas fa-upload"></i> Upload and Import
                                    </button>
                                    <a href="{{ route('products.template.download') }}" class="btn modern-btn modern-btn-info">
                                        <i class="fas fa-download"></i> Download Template
                                    </a>
                                </div>
                            </form>
                        </div>
                        <div class="col-md-6">
                            <div class="info-panel">
                                <h5><i class="fas fa-info-circle"></i> Import Instructions</h5>
                                <ol class="instruction-list">
                                    <li>Download the template file first</li>
                                    <li>Fill in your product data</li>
                                    <li>Required fields: name, company_id, category_id, purchase_price, sale_price</li>
                                    <li>Optional fields: opening_stock, is_stock_managed, default_godown_id, weight_value, weight_unit, description</li>
                                    <li>Upload the completed file</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                    
                    @if(session('import_failures'))
                        <div class="import-failures-section">
                            <div class="alert modern-alert modern-alert-warning">
                                <div class="alert-content">
                                    <i class="fas fa-exclamation-triangle alert-icon"></i>
                                    <div class="alert-message">
                                        <strong>Import Warnings!</strong>
                                        <span>{{ session('warning') }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="table-responsive">
                                <table class="table modern-table table-sm">
                                    <thead class="modern-thead">
                                        <tr>
                                            <th>Row #</th>
                                            <th>Data</th>
                                            <th>Error</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach(session('import_failures') as $failure)
                                            <tr>
                                                <td>{{ $failure['row_number'] }}</td>
                                                <td><code>{{ $failure['row_data'] }}</code></td>
                                                <td>{{ $failure['error'] }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif
                </div>
                <div class="modal-footer modern-modal-footer">
                    <button type="button" class="btn modern-btn modern-btn-outline" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Merge Duplicates Modal -->
    <div class="modal fade modern-modal" id="mergeDuplicatesModal">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content modern-modal-content">
                <div class="modal-header modern-modal-header" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);">
                    <h5 class="modal-title">
                        <i class="fas fa-object-group"></i>
                        Merge Duplicate Products
                    </h5>
                    <button type="button" class="close modern-close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body modern-modal-body">
                    <!-- Mode Tabs -->
                    <ul class="nav nav-tabs merge-mode-tabs mb-4" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="auto-tab" data-toggle="tab" href="#auto-mode" role="tab">
                                <i class="fas fa-magic"></i> Auto Detect
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="manual-tab" data-toggle="tab" href="#manual-mode" role="tab">
                                <i class="fas fa-search"></i> Manual Merge
                            </a>
                        </li>
                    </ul>

                    <div class="tab-content">
                        <!-- Auto Detect Mode -->
                        <div class="tab-pane fade show active" id="auto-mode" role="tabpanel">
                            <!-- Summary Stats -->
                            <div class="row mb-4">
                                <div class="col-md-4">
                                    <div class="preview-stat">
                                        <span class="label">Duplicate Groups</span>
                                        <span class="value" id="total-duplicate-groups">-</span>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="preview-stat">
                                        <span class="label">Total Duplicate Products</span>
                                        <span class="value" id="total-duplicate-products">-</span>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="preview-stat">
                                        <span class="label">Products to Remove</span>
                                        <span class="value" id="products-to-remove">-</span>
                                    </div>
                                </div>
                            </div>

                            <div class="alert modern-alert modern-alert-warning mb-3">
                                <div class="alert-content">
                                    <i class="fas fa-info-circle alert-icon"></i>
                                    <div class="alert-message">
                                        <strong>Auto-detected duplicates:</strong>
                                        <span>Products with most transaction history are automatically selected as PRIMARY.</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Loading indicator -->
                            <div id="duplicates-loading" class="text-center py-5">
                                <i class="fas fa-spinner fa-spin fa-3x text-primary"></i>
                                <p class="mt-3 text-muted">Scanning for duplicate products...</p>
                            </div>

                            <!-- No duplicates message -->
                            <div id="no-duplicates-message" class="text-center py-5" style="display: none;">
                                <i class="fas fa-check-circle fa-3x text-success"></i>
                                <h5 class="mt-3">No Duplicate Products Found</h5>
                                <p class="text-muted">All products have unique names. Use Manual Merge to combine any products.</p>
                            </div>

                            <!-- Duplicate Groups List -->
                            <div id="duplicate-groups-container" style="display: none; max-height: 400px; overflow-y: auto;"></div>
                        </div>

                        <!-- Manual Merge Mode -->
                        <div class="tab-pane fade" id="manual-mode" role="tabpanel">
                            <div class="alert modern-alert modern-alert-info mb-3">
                                <div class="alert-content">
                                    <i class="fas fa-info-circle alert-icon"></i>
                                    <div class="alert-message">
                                        <strong>Manual Merge:</strong>
                                        <span>Search and select the PRIMARY product (to keep), then add duplicate products to merge into it.</span>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="card modern-card mb-3">
                                        <div class="card-header modern-header py-2" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
                                            <h6 class="mb-0 text-white"><i class="fas fa-star"></i> Primary Product (Keep)</h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="form-group">
                                                <input type="text" id="primary-product-search" class="form-control modern-input" placeholder="Search by product name or ID...">
                                            </div>
                                            <div id="primary-product-results" class="search-results-container" style="max-height: 200px; overflow-y: auto;"></div>
                                            <div id="selected-primary-product" class="selected-product-card mt-3" style="display: none;">
                                                <div class="card border-success">
                                                    <div class="card-body py-2">
                                                        <div class="d-flex justify-content-between align-items-center">
                                                            <div>
                                                                <strong id="primary-product-name"></strong>
                                                                <small class="d-block text-muted" id="primary-product-details"></small>
                                                            </div>
                                                            <button type="button" class="btn btn-sm btn-outline-danger" id="remove-primary-product">
                                                                <i class="fas fa-times"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <input type="hidden" id="primary-product-id">
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="card modern-card mb-3">
                                        <div class="card-header modern-header py-2" style="background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);">
                                            <h6 class="mb-0 text-white"><i class="fas fa-trash-alt"></i> Duplicate Products (To Merge & Delete)</h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="form-group">
                                                <input type="text" id="duplicate-product-search" class="form-control modern-input" placeholder="Search by product name or ID...">
                                            </div>
                                            <div id="duplicate-product-results" class="search-results-container" style="max-height: 200px; overflow-y: auto;"></div>
                                            <div id="selected-duplicates-list" class="mt-3"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div id="merge-preview" class="merge-preview-section mt-3" style="display: none;">
                                <div class="card modern-card">
                                    <div class="card-header modern-header py-2">
                                        <h6 class="mb-0 text-white"><i class="fas fa-eye"></i> Merge Preview</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="preview-stat">
                                                    <span class="label">Products to Merge:</span>
                                                    <span class="value" id="preview-merge-count">0</span>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="preview-stat">
                                                    <span class="label">Total Stock to Add:</span>
                                                    <span class="value" id="preview-stock-add">0</span>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="preview-stat">
                                                    <span class="label">New Total Stock:</span>
                                                    <span class="value" id="preview-new-stock">0</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer modern-modal-footer">
                    <button type="button" class="btn modern-btn modern-btn-outline" data-dismiss="modal">Close</button>
                    <!-- Auto mode button -->
                    <button type="button" class="btn modern-btn modern-btn-danger auto-mode-btn" id="merge-all-btn" disabled>
                        <i class="fas fa-object-group"></i> Merge All Selected
                    </button>
                    <!-- Manual mode button -->
                    <button type="button" class="btn modern-btn modern-btn-warning manual-mode-btn" id="merge-products-btn" disabled style="display: none;">
                        <i class="fas fa-object-group"></i> Merge Products
                    </button>
                </div>
            </div>
        </div>
    </div>
@stop

@section('additional_css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.25/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.min.css">
    
    <style>
        /* Modern Alert Styles */
        .modern-alert {
            border: none;
            border-radius: 12px;
            padding: 0;
            margin-bottom: 24px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            backdrop-filter: blur(10px);
            animation: slideInDown 0.3s ease-out;
        }

        .modern-alert-success {
            background: linear-gradient(135deg, rgba(34, 197, 94, 0.1) 0%, rgba(34, 197, 94, 0.05) 100%);
            border-left: 4px solid #22c55e;
        }

        .modern-alert-error {
            background: linear-gradient(135deg, rgba(239, 68, 68, 0.1) 0%, rgba(239, 68, 68, 0.05) 100%);
            border-left: 4px solid #ef4444;
        }

        .modern-alert-warning {
            background: linear-gradient(135deg, rgba(245, 158, 11, 0.1) 0%, rgba(245, 158, 11, 0.05) 100%);
            border-left: 4px solid #f59e0b;
        }

        .alert-content {
            display: flex;
            align-items: center;
            padding: 16px 20px;
            gap: 12px;
        }

        .alert-icon {
            font-size: 20px;
            flex-shrink: 0;
        }

        .modern-alert-success .alert-icon {
            color: #22c55e;
        }

        .modern-alert-error .alert-icon {
            color: #ef4444;
        }

        .modern-alert-warning .alert-icon {
            color: #f59e0b;
        }

        .alert-message {
            flex: 1;
            font-size: 14px;
            line-height: 1.5;
        }

        .alert-message strong {
            font-weight: 600;
            margin-right: 8px;
        }

        .alert-close {
            background: none;
            border: none;
            padding: 4px;
            cursor: pointer;
            color: #6b7280;
            transition: all 0.2s ease;
            border-radius: 6px;
        }

        .alert-close:hover {
            background: rgba(0, 0, 0, 0.05);
            color: #374151;
        }

        /* Header Actions */
        .header-actions-group {
            display: flex;
            gap: 12px;
            align-items: center;
        }

        /* Filter Card Styles */
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

        /* Modern Header Enhancements */
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

        .header-stats {
            display: flex;
            gap: 20px;
        }

        .stat-item {
            text-align: center;
            padding: 12px 20px;
            background: white;
            border-radius: 10px;
            border: 1px solid #e5e7eb;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            min-width: 120px;
        }

        .stat-label {
            display: block;
            font-size: 12px;
            color: #6b7280;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .stat-value {
            display: block;
            font-size: 20px;
            font-weight: 700;
            color: #374151;
            margin-top: 4px;
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

        .th-content {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            color: white;
        }

        .sortable .th-content {
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .sortable:hover .th-content {
            color: #e0e7ff;
            transform: translateY(-1px);
        }

        .sort-icon {
            font-size: 10px;
            opacity: 0.7;
            transition: all 0.2s ease;
            color: white;
        }

        .sortable:hover .sort-icon {
            opacity: 1;
            color: #e0e7ff;
        }

        .actions-column {
            width: 10%;
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
            padding: 16px;
            vertical-align: middle;
            border: none !important;
            font-size: 14px;
            color: #374151 !important;
            background: transparent !important;
        }

        /* Enhanced Stock Status Badges */
        .stock-status-container {
            display: flex;
            align-items: center;
            gap: 8px;
        }

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
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
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

        .stock-icon {
            font-size: 14px;
        }

        .stock-qty {
            font-weight: 700;
            margin-left: 4px;
        }

        /* Price Display */
        .price-display {
            font-weight: 600;
            color: #374151;
            font-size: 14px;
        }

        /* Product Name Link Styles */
        .product-name-link {
            color: #6366f1;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.2s ease;
            position: relative;
            display: inline-block;
            border-radius: 4px;
            padding: 2px 4px;
        }

        .product-name-link:hover {
            color: #4f46e5;
            text-decoration: none;
            background: linear-gradient(135deg, rgba(99, 102, 241, 0.1) 0%, rgba(139, 92, 246, 0.1) 100%);
            transform: translateX(2px);
            box-shadow: 0 2px 8px rgba(99, 102, 241, 0.2);
        }

        .product-name-link:focus {
            outline: none;
            color: #4f46e5;
            background: linear-gradient(135deg, rgba(99, 102, 241, 0.15) 0%, rgba(139, 92, 246, 0.15) 100%);
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.3);
        }

        .product-name-link:active {
            transform: translateX(1px);
            color: #3730a3;
        }

        /* Add an icon to indicate it's clickable */
        .product-name-link::after {
            content: '\f35d'; /* FontAwesome external link icon */
            font-family: 'Font Awesome 5 Free';
            font-weight: 900;
            font-size: 10px;
            margin-left: 6px;
            opacity: 0;
            transition: all 0.2s ease;
            color: #6366f1;
        }

        .product-name-link:hover::after {
            opacity: 0.7;
            transform: translateX(2px);
        }

        /* Modern Modal Styles */
        .modern-modal .modal-content {
            border: none;
            border-radius: 16px;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.15);
        }

        .modern-modal-header {
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
            color: white;
            border-radius: 16px 16px 0 0;
            padding: 20px 24px;
            border-bottom: none;
        }

        .modern-modal-header .modal-title {
            font-weight: 600;
            font-size: 18px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .modern-close {
            color: white;
            opacity: 0.8;
            font-size: 24px;
            transition: all 0.2s ease;
        }

        .modern-close:hover {
            opacity: 1;
            color: white;
        }

        .modern-modal-body {
            padding: 24px;
            background: white;
        }

        .modern-modal-footer {
            padding: 20px 24px;
            background: #f8fafc;
            border-radius: 0 0 16px 16px;
            border-top: 1px solid #e5e7eb;
        }

        /* File Input Styles */
        .modern-file-input .custom-file-input:focus ~ .custom-file-label {
            border-color: #6366f1;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
        }

        .modern-file-input .custom-file-label {
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            padding: 10px 15px;
            background: white;
            color: #6b7280;
            transition: all 0.2s ease;
        }

        .modern-file-input .custom-file-label::after {
            background: #6366f1;
            color: white;
            border-radius: 6px;
            border: none;
            padding: 8px 16px;
            font-weight: 500;
        }

        /* Info Panel */
        .info-panel {
            background: linear-gradient(135deg, rgba(99, 102, 241, 0.05) 0%, rgba(139, 92, 246, 0.05) 100%);
            border: 1px solid rgba(99, 102, 241, 0.1);
            border-radius: 12px;
            padding: 20px;
        }

        .info-panel h5 {
            color: #6366f1;
            font-weight: 600;
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .instruction-list {
            margin: 0;
            padding-left: 20px;
            color: #374151;
        }

        .instruction-list li {
            margin-bottom: 8px;
            line-height: 1.5;
        }

        /* Import Failures Section */
        .import-failures-section {
            margin-top: 24px;
            padding-top: 24px;
            border-top: 1px solid #e5e7eb;
        }

        /* Form Actions */
        .form-actions {
            display: flex;
            gap: 12px;
            margin-top: 20px;
        }

        /* Modern Card and Button Styles */
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

        /* Merge Duplicates Modal Styles */
        .search-results-container {
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            background: #f9fafb;
        }

        .search-result-item {
            padding: 10px 15px;
            border-bottom: 1px solid #e5e7eb;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .search-result-item:last-child {
            border-bottom: none;
        }

        .search-result-item:hover {
            background: linear-gradient(135deg, rgba(99, 102, 241, 0.1) 0%, rgba(139, 92, 246, 0.1) 100%);
        }

        .search-result-item .product-name {
            font-weight: 600;
            color: #374151;
        }

        .search-result-item .product-details {
            font-size: 12px;
            color: #6b7280;
        }

        .selected-duplicate-item {
            background: linear-gradient(135deg, rgba(239, 68, 68, 0.1) 0%, rgba(220, 38, 38, 0.1) 100%);
            border: 1px solid #fca5a5;
            border-radius: 8px;
            padding: 10px 15px;
            margin-bottom: 8px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .selected-duplicate-item .product-info {
            flex: 1;
        }

        .selected-duplicate-item .product-name {
            font-weight: 600;
            color: #dc2626;
        }

        .selected-duplicate-item .product-details {
            font-size: 12px;
            color: #6b7280;
        }

        .preview-stat {
            text-align: center;
            padding: 15px;
            background: #f8fafc;
            border-radius: 8px;
        }

        .preview-stat .label {
            display: block;
            font-size: 12px;
            color: #6b7280;
            margin-bottom: 5px;
        }

        .preview-stat .value {
            display: block;
            font-size: 24px;
            font-weight: 700;
            color: #374151;
        }

        .no-results-message {
            padding: 20px;
            text-align: center;
            color: #6b7280;
            font-style: italic;
        }

        /* Duplicate Group Styles */
        .duplicate-group {
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            margin-bottom: 16px;
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .duplicate-group:hover {
            border-color: #f59e0b;
            box-shadow: 0 4px 12px rgba(245, 158, 11, 0.15);
        }

        .duplicate-group-header {
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            padding: 12px 16px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #e5e7eb;
        }

        .duplicate-group-header .group-name {
            font-weight: 700;
            font-size: 16px;
            color: #374151;
        }

        .duplicate-group-header .group-badge {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .duplicate-group-body {
            padding: 12px;
        }

        .duplicate-product-row {
            display: flex;
            align-items: center;
            padding: 10px 12px;
            border-radius: 8px;
            margin-bottom: 8px;
            transition: all 0.2s ease;
        }

        .duplicate-product-row:last-child {
            margin-bottom: 0;
        }

        .duplicate-product-row.primary-product {
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.15) 0%, rgba(5, 150, 105, 0.1) 100%);
            border: 2px solid #10b981;
        }

        .duplicate-product-row.duplicate-product {
            background: linear-gradient(135deg, rgba(239, 68, 68, 0.1) 0%, rgba(220, 38, 38, 0.05) 100%);
            border: 1px solid #fca5a5;
        }

        .product-radio {
            margin-right: 12px;
        }

        .product-radio input[type="radio"] {
            width: 18px;
            height: 18px;
            cursor: pointer;
        }

        .product-info-col {
            flex: 1;
        }

        .product-name-row {
            font-weight: 600;
            color: #374151;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .primary-badge {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .delete-badge {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: white;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .product-meta-row {
            font-size: 12px;
            color: #6b7280;
            margin-top: 4px;
        }

        .product-meta-row span {
            margin-right: 12px;
        }

        .movement-badge {
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
            color: white;
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 600;
            white-space: nowrap;
        }

        .merge-group-btn {
            margin-left: 12px;
        }

        /* Merge Mode Tabs */
        .merge-mode-tabs {
            border-bottom: 2px solid #e5e7eb;
        }

        .merge-mode-tabs .nav-link {
            border: none;
            border-bottom: 3px solid transparent;
            color: #6b7280;
            font-weight: 600;
            padding: 12px 20px;
            margin-bottom: -2px;
            transition: all 0.2s ease;
        }

        .merge-mode-tabs .nav-link:hover {
            color: #f59e0b;
            border-color: transparent;
        }

        .merge-mode-tabs .nav-link.active {
            color: #f59e0b;
            border-bottom-color: #f59e0b;
            background: transparent;
        }

        .merge-mode-tabs .nav-link i {
            margin-right: 8px;
        }

        .modern-alert-info {
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.1) 0%, rgba(37, 99, 235, 0.05) 100%);
            border-left: 4px solid #3b82f6;
        }

        .modern-alert-info .alert-icon {
            color: #3b82f6;
        }

        /* DataTable Enhancements */
        .dataTables_wrapper {
            background: white;
            padding: 0;
        }

        .dataTables_wrapper .dataTables_length select,
        .dataTables_wrapper .dataTables_filter input {
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            padding: 8px 12px;
            background: white;
            color: #374151;
            transition: all 0.2s ease;
        }

        .dataTables_wrapper .dataTables_length select:focus,
        .dataTables_wrapper .dataTables_filter input:focus {
            outline: none;
            border-color: #6366f1;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button {
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            padding: 8px 12px;
            margin: 0 2px;
            background: white;
            color: #374151;
            transition: all 0.2s ease;
            font-weight: 500;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
            background: #6366f1;
            color: white;
            border-color: #6366f1;
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(99, 102, 241, 0.3);
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button.current {
            background: #6366f1;
            color: white;
            border-color: #6366f1;
            box-shadow: 0 4px 8px rgba(99, 102, 241, 0.3);
        }

        /* Animations */
        @keyframes slideInDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes pulse {
            0%, 100% { opacity: 0.8; }
            50% { opacity: 1; }
        }

        .modern-table tbody tr {
            /* animation: fadeIn 0.3s ease-in-out; */
            animation: none;
            transition: all 0.2s ease;
            border-bottom: 1px solid #f1f5f9;
            background: white !important;
        }

        /* Enhanced Mobile Responsive Design */
        @media (max-width: 768px) {
            .header-content {
                flex-direction: column;
                gap: 16px;
                align-items: flex-start;
            }

            .header-stats {
                align-self: stretch;
                justify-content: space-between;
                flex-wrap: wrap;
                gap: 12px;
            }

            .stat-item {
                flex: 1;
                min-width: 100px;
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

            .modern-table-responsive {
                min-width: 800px;
            }

            .modern-thead th {
                white-space: nowrap;
                min-width: 100px;
                padding: 12px 8px;
                font-size: 12px;
            }

            .modern-tbody td {
                padding: 12px 8px;
                font-size: 13px;
                white-space: nowrap;
                min-width: 100px;
            }

            .actions-column,
            .modern-tbody td:last-child {
                min-width: 120px;
                width: 120px;
            }

            .stock-badge {
                min-width: 80px;
                font-size: 10px;
                padding: 6px 12px;
            }

            .price-display {
                font-size: 13px;
            }

            .product-name-link {
                font-size: 13px;
                max-width: 150px;
                overflow: hidden;
                text-overflow: ellipsis;
                white-space: nowrap;
                display: inline-block;
            }

            /* Add scroll indicator for better UX */
            .table-container::after {
                content: "Scroll right to see more →";
                position: absolute;
                top: 50%;
                right: 10px;
                transform: translateY(-50%);
                background: rgba(99, 102, 241, 0.9);
                color: white;
                padding: 8px 12px;
                border-radius: 20px;
                font-size: 10px;
                font-weight: 600;
                z-index: 10;
                opacity: 0.8;
                animation: pulse 2s infinite;
                pointer-events: none;
            }
        }

        /* Additional styles for very small screens */
        @media (max-width: 480px) {
            .modern-thead th {
                padding: 10px 6px;
                font-size: 11px;
                min-width: 80px;
            }

            .modern-tbody td {
                padding: 10px 6px;
                font-size: 12px;
                min-width: 80px;
            }

            .stock-badge {
                min-width: 70px;
                font-size: 9px;
                padding: 4px 8px;
            }

            .product-name-link {
                max-width: 120px;
                font-size: 12px;
            }

            .modern-table {
                min-width: 700px;
            }

            .modern-table-responsive {
                min-width: 700px;
            }

            .table-container::after {
                font-size: 9px;
                padding: 6px 10px;
                right: 5px;
            }
        }

        /* Compact layout overrides (match invoice index density) */
        .modern-card {
            border-radius: 12px;
            margin-bottom: 16px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.06);
        }

        .modern-header {
            background: #ffffff;
            color: #111827;
            padding: 12px 16px;
            border-bottom: 1px solid #e5e7eb;
        }

        .header-content {
            flex-wrap: wrap;
            gap: 12px;
        }

        .modern-card-body {
            padding: 16px;
        }

        .products-layout {
            display: grid;
            grid-template-columns: 1fr 260px;
            gap: 16px;
            align-items: start;
            position: relative;
        }

        .products-layout > .table-container {
            min-width: 0;
        }

        .group-browser-rail {
            position: relative;
            min-width: 0;
        }

        .group-browser-card {
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            background: #ffffff;
            padding: 12px;
        }

        .group-browser-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            margin-bottom: 10px;
            flex-wrap: wrap;
        }

        .group-browser-title {
            font-size: 13px;
            font-weight: 600;
            color: #111827;
        }

        .group-browser-toggle .btn {
            padding: 4px 8px;
            font-size: 11px;
            line-height: 1.2;
        }

        .group-browser-toggle .btn.active {
            background: #6366f1;
            color: #ffffff;
            border-color: #6366f1;
        }

        .group-browser-search {
            margin-bottom: 8px;
        }

        .group-browser-search .modern-input {
            width: 100%;
        }

        .group-browser-hint {
            font-size: 11px;
            color: #6b7280;
            margin-bottom: 8px;
        }

        .group-browser-list {
            display: flex;
            flex-direction: column;
            gap: 6px;
            max-height: 420px;
            overflow: auto;
        }

        .group-item {
            width: 100%;
            text-align: left;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            padding: 6px 8px;
            background: #f9fafb;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            font-size: 12px;
            color: #374151;
            cursor: pointer;
        }

        .group-item:hover {
            background: #eef2ff;
            border-color: #c7d2fe;
        }

        .group-item.active {
            background: #6366f1;
            color: #ffffff;
            border-color: #6366f1;
        }

        .group-item.disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .group-count-badge {
            font-size: 11px;
            padding: 2px 6px;
            border-radius: 999px;
            background: rgba(17, 24, 39, 0.1);
            color: inherit;
            white-space: nowrap;
        }

        .group-item.active .group-count-badge {
            background: rgba(255, 255, 255, 0.2);
        }

        .header-icon {
            color: #6366f1;
            font-size: 18px;
        }

        .card-title {
            color: #111827;
            font-size: 16px;
        }

        .header-actions-group {
            gap: 8px;
        }

        .header-stats {
            gap: 12px;
        }

        .header-contrast {
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
            color: #ffffff;
        }

        .header-contrast .header-icon {
            color: #ffffff;
        }

        .header-contrast .card-title {
            color: #ffffff;
        }

        .stat-item {
            padding: 6px 10px;
            min-width: 96px;
            box-shadow: none;
            border: 1px solid #e5e7eb;
        }

        .stat-label {
            font-size: 11px;
        }

        .stat-value {
            font-size: 16px;
        }

        .filter-grid {
            grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
            gap: 12px;
        }

        .modern-input,
        .modern-select {
            border-width: 1px;
            padding: 8px 10px;
            font-size: 13px;
            line-height: 1.3;
            min-height: 36px;
        }

        .modern-select {
            height: 36px;
            padding-right: 28px;
        }

        .modern-input-group .modern-input {
            height: 36px;
        }

        .modern-input-addon,
        .modern-input-group .input-group-text {
            height: 36px;
        }

        .filter-actions .btn {
            padding: 7px 12px;
            font-size: 12px;
        }

        .modern-btn {
            padding: 6px 12px;
            font-size: 12px;
            border-radius: 6px;
        }

        .modern-table-responsive {
            box-shadow: none;
            border: 1px solid #e5e7eb;
            overflow: auto;
        }

        .table-container {
            overflow: visible;
        }

        .modern-thead {
            background: #f8fafc !important;
        }

        .modern-thead th {
            padding: 12px;
            font-size: 11px;
            color: #374151 !important;
        }

        .th-content {
            color: #374151;
        }

        .sort-icon {
            color: #9ca3af;
        }

        .sortable:hover .th-content {
            color: #111827;
        }

        .sortable:hover .sort-icon {
            color: #6b7280;
        }

        .modern-tbody td {
            padding: 10px 12px;
            font-size: 13px;
        }

        .modern-tbody tr:hover {
            transform: none;
            box-shadow: none;
        }

        /* Group rows */
        .products-layout.group-browser-hidden {
            grid-template-columns: 1fr 0;
        }

        .group-browser-card.is-hidden {
            display: none;
        }

        .group-browser-float-btn {
            position: fixed;
            bottom: 24px;
            right: 24px;
            z-index: 1050;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 56px;
            height: 56px;
            border-radius: 50%;
            border: none;
            background: linear-gradient(135deg, #2563eb, #6366f1);
            color: #ffffff;
            font-size: 20px;
            box-shadow: 0 10px 25px rgba(37, 99, 235, 0.35);
            cursor: pointer;
        }

        .group-browser-float-btn:hover {
            background: linear-gradient(135deg, #1d4ed8, #4f46e5);
            transform: translateY(-2px);
        }

        .products-layout.group-browser-hidden .group-browser-rail {
            width: 0;
            overflow: hidden;
        }

        .stock-badge {
            min-width: 0;
            padding: 4px 8px;
            font-size: 11px;
            text-transform: none;
            letter-spacing: 0;
            box-shadow: none;
        }

        .stock-qty {
            font-weight: 600;
            margin-left: 6px;
        }

        .price-sep {
            color: #9ca3af;
            padding: 0 4px;
        }

        .dataTables_wrapper .dataTables_length select,
        .dataTables_wrapper .dataTables_filter input {
            padding: 6px 8px;
            font-size: 12px;
            height: 32px;
            line-height: 1.2;
        }

        .compact-toolbar,
        .compact-footer {
            margin: 8px 0;
        }

        .dataTables_wrapper .dataTables_info {
            font-size: 12px;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button {
            padding: 4px 8px;
            margin: 0 1px;
            border-radius: 6px;
            box-shadow: none;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
            background: #f3f4f6;
            color: #111827;
            border-color: #d1d5db;
            transform: none;
            box-shadow: none;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button.current {
            background: #6366f1;
            color: white;
            border-color: #6366f1;
            box-shadow: none;
        }


        table.dataTable.dtr-inline.collapsed > tbody > tr > td.dtr-control {
            padding-left: 28px;
        }

        table.dataTable.dtr-inline.collapsed > tbody > tr > td:first-child:before {
            top: 50%;
            left: 8px;
            margin-top: -8px;
            height: 16px;
            width: 16px;
            line-height: 16px;
            border-radius: 8px;
            background-color: #6366f1;
            border: none;
            color: #ffffff;
            box-shadow: none;
        }

        table.dataTable > tbody > tr.child ul.dtr-details {
            width: 100%;
        }

        table.dataTable > tbody > tr.child span.dtr-title {
            font-weight: 600;
            color: #374151;
        }

        @media (max-width: 768px) {
            .table-container {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }

            .modern-table-responsive {
                overflow-x: auto;
            }

            .modern-table,
            .modern-table-responsive {
                min-width: 720px;
                width: max-content;
            }

            .table-container::after {
                display: none;
            }

            .dataTables_wrapper .dataTables_filter input {
                width: 100% !important;
            }

            .header-stats {
                width: 100%;
            }

            .header-actions-group .btn {
                font-size: 12px;
            }

            .products-layout {
                grid-template-columns: 1fr;
            }

            .group-browser-card {
                order: 0;
            }
        }

        @media (max-width: 480px) {
            .stat-item {
                min-width: 80px;
            }

            .filter-actions {
                flex-direction: column;
                align-items: stretch;
            }

            .filter-actions .btn {
                width: 100%;
            }
        }
    </style>
@stop

@section('additional_js')
    <script src="https://cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.25/js/dataTables.bootstrap4.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.2.9/js/responsive.bootstrap4.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/moment/moment.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"></script>
    
    <script>
        $(document).ready(function() {
            // Configure toastr for modern notifications
            toastr.options = {
                "closeButton": true,
                "debug": false,
                "newestOnTop": true,
                "progressBar": true,
                "positionClass": "toast-top-right",
                "preventDuplicates": true,        // Add this
                 "preventOpenDuplicates": true,    // Add this
                  "maxOpened": 1,                   // Add this to limit to 1 toast
                 "autoDismiss": true,  
                "onclick": null,
                "showDuration": "300",
                "hideDuration": "1000",
                "timeOut": "3000",
                "extendedTimeOut": "1000",
                "showEasing": "swing",
                "hideEasing": "linear",
                "showMethod": "fadeIn",
                "hideMethod": "fadeOut"
            };

            var groupBrowserBy = 'category';
            var autoFilterTimeout = null;
            var groupBrowserVisible = true;

            // Initialize date range picker
            $('#date-range').daterangepicker({
                autoUpdateInput: false,
                locale: {
                    cancelLabel: 'Clear'
                }
            });

            $('#date-range').on('apply.daterangepicker', function(ev, picker) {
                $(this).val(picker.startDate.format('YYYY-MM-DD') + ' - ' + picker.endDate.format('YYYY-MM-DD'));
                triggerAutoFilter(0);
            });

            $('#date-range').on('cancel.daterangepicker', function(ev, picker) {
                $(this).val('');
                triggerAutoFilter(0);
            });
            
            // Show filename when file is selected
            $(".custom-file-input").on("change", function() {
                var fileName = $(this).val().split("\\").pop();
                $(this).siblings(".custom-file-label").addClass("selected").html(fileName);
            });
            
            // Show import modal if there are import failures
            @if(session('import_failures'))
                $('#importModal').modal('show');
            @endif

            // Initialize DataTable with server-side processing
            var table = $('#products-table').DataTable({
                processing: false,
                serverSide: true,
                responsive: false,
                scrollX: true,
                scrollCollapse: true,
                autoWidth: false,
                pageLength: 15,
                lengthMenu: [10, 15, 25, 50],
                ajax: {
                    url: "{{ route('products.getProducts') }}",
                    data: function(d) {
                        // Add custom filter parameters
                        d.name = $('#name-filter').val();
                        d.company_id = $('#company-filter').val();
                        d.category_id = $('#category-filter').val();
                        d.godown_id = $('#godown-filter').length ? $('#godown-filter').val() : '';
                        d.stock_status = $('#stock-filter').val();
                        d.stock_managed = $('#stock-managed-filter').val();
                        d.min_stock = $('#min-stock').val();
                        d.max_stock = $('#max-stock').val();
                        d.min_price = $('#min-price').val();
                        d.max_price = $('#max-price').val();
                        d.min_purchase_price = $('#min-purchase-price').val();
                        d.max_purchase_price = $('#max-purchase-price').val();
                        
                        // Handle date range
                        var dateRange = $('#date-range').val();
                        if (dateRange) {
                            var dates = dateRange.split(' - ');
                            d.start_date = dates[0];
                            d.end_date = dates.length > 1 ? dates[1] : dates[0];
                        }
                    }
                },
                columns: [
                    {
                        data: 'name', 
                        name: 'name',
                        render: function(data, type, row) {
                            if (type === 'display') {
                                // Create a clickable link for the product name
                                var viewUrl = "{{ route('products.show', ':id') }}".replace(':id', row.id);
                                return '<a href="' + viewUrl + '" class="product-name-link" title="View product details">' + 
                                       data + 
                                       '</a>';
                            }
                            return data;
                        }
                    },
                    {data: 'company_name', name: 'company_name'},
                    {data: 'category_name', name: 'category_name'},
                    {
                        data: 'current_stock',
                        name: 'current_stock',
                        orderable: true,
                        searchable: false,
                        render: function(data, type) {
                            if (type !== 'display') {
                                return data;
                            }
                            var stockValue = parseFloat(data);
                            if (isNaN(stockValue)) {
                                stockValue = 0;
                            }
                            var stockDisplay = stockValue % 1 === 0 ? stockValue.toFixed(0) : stockValue.toFixed(2);
                            if (stockValue <= 0) {
                                return '<div class="stock-status-container">' +
                                    '<span class="stock-badge stock-out">' +
                                        '<i class="fas fa-exclamation-circle stock-icon"></i>' +
                                        'Out of Stock' +
                                        '<span class="stock-qty">' + stockDisplay + '</span>' +
                                    '</span>' +
                                '</div>';
                            }
                            if (stockValue <= 10) {
                                return '<div class="stock-status-container">' +
                                    '<span class="stock-badge stock-low">' +
                                        '<i class="fas fa-exclamation-triangle stock-icon"></i>' +
                                        'Low Stock' +
                                        '<span class="stock-qty">' + stockDisplay + '</span>' +
                                    '</span>' +
                                '</div>';
                            }
                            return '<div class="stock-status-container">' +
                                '<span class="stock-badge stock-good">' +
                                    '<i class="fas fa-check-circle stock-icon"></i>' +
                                    'In Stock' +
                                    '<span class="stock-qty">' + stockDisplay + '</span>' +
                                '</span>' +
                            '</div>';
                        }
                    },
                    {
                        data: null,
                        name: 'sale_price',
                        orderable: true,
                        searchable: false,
                        render: function(data, type, row) {
                            if (type !== 'display') {
                                return row.sale_price;
                            }
                            var purchaseValue = parseFloat(row.purchase_price);
                            var saleValue = parseFloat(row.sale_price);
                            if (isNaN(purchaseValue)) {
                                purchaseValue = 0;
                            }
                            if (isNaN(saleValue)) {
                                saleValue = 0;
                            }
                            return '<span class="price-display">৳' + purchaseValue.toFixed(2) + '</span>' +
                                   '<span class="price-sep"> / </span>' +
                                   '<span class="price-display">৳' + saleValue.toFixed(2) + '</span>';
                        }
                    },
                    {data: 'action', name: 'action', orderable: false, searchable: false},
                ],
                order: [[0, 'asc']],
                dom: '<"row align-items-center compact-toolbar"<"col-md-6"l><"col-md-6"f>>rt<"row align-items-center compact-footer"<"col-md-5"i><"col-md-7"p>>',
                drawCallback: function(settings) {
                    var api = this.api();
                    var data = api.ajax.json();
                    
                    // Update header stats
                    $('#total-count').text(api.page.info().recordsTotal);
                    
                    // Update stock counts if available from server
                    if (data.stockCounts) {
                        $('#low-stock-count').text(data.stockCounts.low_stock || 0);
                        $('#out-stock-count').text(data.stockCounts.out_of_stock || 0);
                    }
                    
                    // Add smooth animation to newly loaded rows
                    // $('.modern-tbody tr').each(function(index) {
                    //     $(this).css('animation-delay', (index * 50) + 'ms');
                    // });

                }
            });

            function triggerAutoFilter(delay) {
                clearTimeout(autoFilterTimeout);
                autoFilterTimeout = setTimeout(function() {
                    table.draw();
                    if (groupBrowserVisible) {
                        loadGroupSummary();
                    }
                }, delay || 0);
            }

            function getFilterParams() {
                var params = {
                    name: $('#name-filter').val(),
                    company_id: $('#company-filter').val(),
                    category_id: $('#category-filter').val(),
                    godown_id: $('#godown-filter').length ? $('#godown-filter').val() : '',
                    stock_status: $('#stock-filter').val(),
                    stock_managed: $('#stock-managed-filter').val(),
                    min_stock: $('#min-stock').val(),
                    max_stock: $('#max-stock').val(),
                    min_price: $('#min-price').val(),
                    max_price: $('#max-price').val(),
                    min_purchase_price: $('#min-purchase-price').val(),
                    max_purchase_price: $('#max-purchase-price').val()
                };

                var dateRange = $('#date-range').val();
                if (dateRange) {
                    var dates = dateRange.split(' - ');
                    params.start_date = dates[0];
                    params.end_date = dates.length > 1 ? dates[1] : dates[0];
                }

                return params;
            }

            function updateGroupBrowserPlaceholder() {
                var placeholder = groupBrowserBy === 'category' ? 'Search categories...' : 'Search companies...';
                $('#group-browser-search').attr('placeholder', placeholder).val('');
            }

            function adjustProductsTableLayout() {
                if (!table) {
                    return;
                }
                setTimeout(function() {
                    table.columns.adjust().draw(false);
                }, 80);
            }

            function setGroupBrowserVisible(visible) {
                groupBrowserVisible = visible;
                if (visible) {
                    $('.group-browser-card').removeClass('is-hidden');
                    $('.products-layout').removeClass('group-browser-hidden');
                } else {
                    $('.group-browser-card').addClass('is-hidden');
                    $('.products-layout').addClass('group-browser-hidden');
                    var hadGroupFilter = $('#company-filter').val() || $('#category-filter').val();
                    $('#company-filter').val('');
                    $('#category-filter').val('');
                    $('#group-browser-search').val('');
                    if (hadGroupFilter) {
                        table.draw();
                    }
                }
                $('.group-browser-float-btn').attr('aria-pressed', visible ? 'true' : 'false');
                try {
                    localStorage.setItem('productsGroupBrowserHidden', visible ? '0' : '1');
                } catch (e) {
                    // no-op
                }
                adjustProductsTableLayout();
            }

            function renderGroupSummary(response) {
                var groups = (response && response.groups) ? response.groups : [];
                var currentId = groupBrowserBy === 'category' ? $('#category-filter').val() : $('#company-filter').val();
                var totalCount = 0;

                groups.forEach(function(group) {
                    totalCount += parseInt(group.product_count, 10) || 0;
                });

                var allLabel = groupBrowserBy === 'category' ? 'All Categories' : 'All Companies';
                var html = '';
                var allActive = !currentId;

                html += '<button type="button" class="group-item' + (allActive ? ' active' : '') + '" data-id="">' +
                    '<span class="group-name">' + allLabel + '</span>' +
                    '<span class="group-count-badge">' + totalCount + '</span>' +
                '</button>';

                groups.forEach(function(group) {
                    var groupId = group.group_id;
                    var name = group.group_name || 'Unassigned';
                    var count = group.product_count || 0;
                    var isDisabled = groupId === null || groupId === undefined || groupId === '';
                    var isActive = currentId && String(currentId) === String(groupId);

                    var safeGroupId = (groupId === null || groupId === undefined) ? '' : groupId;
                    html += '<button type="button" class="group-item' + (isActive ? ' active' : '') + (isDisabled ? ' disabled' : '') + '" data-id="' + safeGroupId + '" data-name="' + escapeHtml(name) + '">' +
                        '<span class="group-name">' + escapeHtml(name) + '</span>' +
                        '<span class="group-count-badge">' + count + '</span>' +
                    '</button>';
                });

                if (!groups.length) {
                    html += '<div class="text-muted small">No groups found.</div>';
                }

                $('#group-browser-list').html(html);
            }

            function loadGroupSummary() {
                if (!groupBrowserVisible) {
                    return;
                }
                var params = getFilterParams();
                params.group_by = groupBrowserBy;

                $.ajax({
                    url: "{{ route('products.group-summary') }}",
                    method: 'GET',
                    data: params,
                    success: function(response) {
                        renderGroupSummary(response);
                    },
                    error: function() {
                        $('#group-browser-list').html('<div class="text-muted small">Failed to load groups.</div>');
                    }
                });
            }

                $('#name-filter, #min-stock, #max-stock, #min-price, #max-price, #min-purchase-price, #max-purchase-price')
                .on('input', function() {
                    triggerAutoFilter(350);
                });

            $('#company-filter, #category-filter, #godown-filter, #stock-filter, #stock-managed-filter')
                .on('change', function() {
                    triggerAutoFilter(0);
                });

            $(document).on('click', '.group-browser-float-btn', function(e) {
                e.preventDefault();
                e.stopPropagation();
                setGroupBrowserVisible(!groupBrowserVisible);
                if (groupBrowserVisible) {
                    loadGroupSummary();
                }
            });

            $('#group-browser-search').on('input', function() {
                var query = $(this).val().toLowerCase();
                $('#group-browser-list .group-item').each(function() {
                    var name = ($(this).data('name') || '').toLowerCase();
                    if (!query || name.indexOf(query) !== -1) {
                        $(this).show();
                    } else {
                        $(this).hide();
                    }
                });
            });

            $(document).on('click', '.group-item', function() {
                if ($(this).hasClass('disabled')) {
                    return;
                }
                var id = $(this).data('id') || '';
                if (groupBrowserBy === 'category') {
                    $('#category-filter').val(id).trigger('change');
                } else {
                    $('#company-filter').val(id).trigger('change');
                }
            });

            // Apply filters button click
            $('#apply-filters').on('click', function() {
                table.draw();
                if (groupBrowserVisible) {
                    loadGroupSummary();
                }
                toastr.info('Filters applied successfully');
            });
            
            // Reset filters button click
            $('#reset-filters').on('click', function() {
                $('#name-filter').val('');
                $('#company-filter').val('');
                $('#category-filter').val('');
                $('#godown-filter').val('');
                $('#stock-filter').val('');
                $('#stock-managed-filter').val('');
                $('#min-stock').val('');
                $('#max-stock').val('');
                $('#min-price').val('');
                $('#max-price').val('');
                $('#min-purchase-price').val('');
                $('#max-purchase-price').val('');
                $('#date-range').val('');
                updateGroupBrowserPlaceholder();
                table.draw();
                if (groupBrowserVisible) {
                    loadGroupSummary();
                }
                toastr.info('Filters reset successfully');
            });

            function setGroupBrowserMode(mode) {
                if (groupBrowserBy === mode) {
                    return;
                }
                groupBrowserBy = mode;
                $('.group-browser-btn').removeClass('active');
                $('.group-browser-btn[data-group="' + groupBrowserBy + '"]').addClass('active');
                updateGroupBrowserPlaceholder();
                loadGroupSummary();
            }

            $('.group-browser-btn').on('click', function() {
                setGroupBrowserMode($(this).data('group'));
            });

            $(window).on('resize', function() {
                adjustProductsTableLayout();
            });

            updateGroupBrowserPlaceholder();
            try {
                var savedHidden = localStorage.getItem('productsGroupBrowserHidden');
                if (savedHidden === '1') {
                    setGroupBrowserVisible(false);
                }
            } catch (e) {
                // no-op
            }
            if (groupBrowserVisible) {
                loadGroupSummary();
            }
            
            // Handle delete confirmation
            $(document).on('click', '.delete-btn', function() {
                var deleteUrl = $(this).data('url');
                var productName = $(this).data('name') || 'this product';
                var csrfToken = $('meta[name="csrf-token"]').attr('content') || '{{ csrf_token() }}';

                function runDelete() {
                    $.ajax({
                        url: deleteUrl,
                        type: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken
                        },
                        success: function(response) {
                            table.draw(false);
                            if (typeof Swal !== 'undefined') {
                                Swal.fire({
                                    title: 'Deleted',
                                    text: response.message || 'Product deleted successfully.',
                                    icon: 'success',
                                    timer: 1800,
                                    showConfirmButton: false
                                });
                            }
                        },
                        error: function(xhr) {
                            var message = 'Failed to delete product.';
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                message = xhr.responseJSON.message;
                            }
                            if (typeof Swal !== 'undefined') {
                                Swal.fire({
                                    title: 'Error',
                                    text: message,
                                    icon: 'error'
                                });
                            } else {
                                alert(message);
                            }
                        }
                    });
                }

                if (typeof Swal === 'undefined') {
                    if (!confirm('Delete "' + productName + '"? This action cannot be undone.')) {
                        return;
                    }
                    runDelete();
                    return;
                }

                Swal.fire({
                    title: 'Delete Product?',
                    text: 'Are you sure you want to delete "' + productName + '"? This action cannot be undone.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#ef4444',
                    cancelButtonColor: '#6b7280',
                    confirmButtonText: 'Yes, delete it'
                }).then(function(result) {
                    if (!result.isConfirmed) {
                        return;
                    }

                    runDelete();
                });
            });

            // Auto-hide alerts after 5 seconds
            setTimeout(function() {
                $('.modern-alert').fadeOut(500);
            }, 5000);

            // Enhanced sorting animations


            // Filter card collapse/expand animation
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

            // ============================================
            // Merge Duplicates Functionality
            // ============================================
            var duplicateGroups = [];
            var selectedPrimaryProduct = null;
            var selectedDuplicates = [];
            var searchTimeout = null;

            // Tab switching - show/hide appropriate buttons
            $('a[data-toggle="tab"]').on('shown.bs.tab', function(e) {
                var target = $(e.target).attr('href');
                if (target === '#auto-mode') {
                    $('.auto-mode-btn').show();
                    $('.manual-mode-btn').hide();
                } else {
                    $('.auto-mode-btn').hide();
                    $('.manual-mode-btn').show();
                }
            });

            // Load duplicates when modal opens
            $('#mergeDuplicatesModal').on('show.bs.modal', function() {
                loadDuplicateProducts();
            });

            // Load duplicate products from server
            function loadDuplicateProducts() {
                $('#duplicates-loading').show();
                $('#no-duplicates-message').hide();
                $('#duplicate-groups-container').hide();
                $('#merge-all-btn').prop('disabled', true);

                $.ajax({
                    url: "{{ route('products.merge.duplicates') }}",
                    method: 'GET',
                    success: function(response) {
                        duplicateGroups = response.groups;

                        $('#total-duplicate-groups').text(response.total_duplicate_groups);
                        $('#total-duplicate-products').text(response.total_duplicate_products);

                        var productsToRemove = response.total_duplicate_products - response.total_duplicate_groups;
                        $('#products-to-remove').text(productsToRemove);

                        if (response.total_duplicate_groups === 0) {
                            $('#duplicates-loading').hide();
                            $('#no-duplicates-message').show();
                        } else {
                            renderDuplicateGroups();
                            $('#duplicates-loading').hide();
                            $('#duplicate-groups-container').show();
                            $('#merge-all-btn').prop('disabled', false);
                        }
                    },
                    error: function() {
                        $('#duplicates-loading').hide();
                        toastr.error('Failed to load duplicate products.');
                    }
                });
            }

            // Render duplicate groups
            function renderDuplicateGroups() {
                var html = '';

                duplicateGroups.forEach(function(group, groupIndex) {
                    html += '<div class="duplicate-group" data-group-index="' + groupIndex + '">';
                    html += '<div class="duplicate-group-header">';
                    html += '<div class="group-name">"' + escapeHtml(group.display_name) + '"</div>';
                    html += '<div class="d-flex align-items-center">';
                    html += '<span class="group-badge">' + group.product_count + ' products</span>';
                    html += '<button type="button" class="btn btn-sm modern-btn modern-btn-warning merge-group-btn" data-group-index="' + groupIndex + '">';
                    html += '<i class="fas fa-object-group"></i> Merge Group';
                    html += '</button>';
                    html += '</div>';
                    html += '</div>';
                    html += '<div class="duplicate-group-body">';

                    group.products.forEach(function(product, productIndex) {
                        var isPrimary = productIndex === 0;
                        var rowClass = isPrimary ? 'primary-product' : 'duplicate-product';

                        html += '<div class="duplicate-product-row ' + rowClass + '">';
                        html += '<div class="product-radio">';
                        html += '<input type="radio" name="primary_' + groupIndex + '" value="' + product.id + '"' + (isPrimary ? ' checked' : '') + ' data-group-index="' + groupIndex + '" data-product-index="' + productIndex + '">';
                        html += '</div>';
                        html += '<div class="product-info-col">';
                        html += '<div class="product-name-row">';
                        html += escapeHtml(product.name) + ' <small class="text-muted">(ID: ' + product.id + ')</small>';
                        if (isPrimary) {
                            html += ' <span class="primary-badge">Keep</span>';
                        } else {
                            html += ' <span class="delete-badge">Delete</span>';
                        }
                        html += '</div>';
                        html += '<div class="product-meta-row">';
                        html += '<span><i class="fas fa-building"></i> ' + escapeHtml(product.company) + '</span>';
                        html += '<span><i class="fas fa-tag"></i> ' + escapeHtml(product.category) + '</span>';
                        html += '<span><i class="fas fa-boxes"></i> Stock: ' + product.current_stock + '</span>';
                        html += '<span><i class="fas fa-money-bill"></i> Sale: ৳' + parseFloat(product.sale_price).toFixed(2) + '</span>';
                        html += '</div>';
                        html += '</div>';
                        html += '<div class="movement-badge">';
                        html += '<i class="fas fa-exchange-alt"></i> ' + product.total_movement + ' transactions';
                        html += '</div>';
                        html += '</div>';
                    });

                    html += '</div>';
                    html += '</div>';
                });

                $('#duplicate-groups-container').html(html);
            }

            // Handle primary product selection change (Auto mode)
            $(document).on('change', 'input[type="radio"][name^="primary_"]', function() {
                var groupIndex = $(this).data('group-index');
                var selectedProductId = parseInt($(this).val());

                duplicateGroups[groupIndex].primary_id = selectedProductId;

                var groupContainer = $(this).closest('.duplicate-group');
                groupContainer.find('.duplicate-product-row').each(function() {
                    var radio = $(this).find('input[type="radio"]');
                    var productId = parseInt(radio.val());

                    $(this).removeClass('primary-product duplicate-product');
                    $(this).find('.primary-badge, .delete-badge').remove();

                    if (productId === selectedProductId) {
                        $(this).addClass('primary-product');
                        $(this).find('.product-name-row').append(' <span class="primary-badge">Keep</span>');
                    } else {
                        $(this).addClass('duplicate-product');
                        $(this).find('.product-name-row').append(' <span class="delete-badge">Delete</span>');
                    }
                });
            });

            // Merge single group (Auto mode)
            $(document).on('click', '.merge-group-btn', function() {
                var groupIndex = $(this).data('group-index');
                var group = duplicateGroups[groupIndex];
                mergeGroup(group, $(this));
            });

            // Merge all groups (Auto mode)
            $('#merge-all-btn').on('click', function() {
                if (duplicateGroups.length === 0) {
                    toastr.warning('No duplicate groups to merge.');
                    return;
                }

                var totalGroups = duplicateGroups.length;
                var totalToDelete = duplicateGroups.reduce(function(sum, g) {
                    return sum + (g.product_count - 1);
                }, 0);

                if (!confirm('Are you sure you want to merge ALL ' + totalGroups + ' duplicate groups?\n\nThis will delete ' + totalToDelete + ' duplicate products.\n\nThis action cannot be undone!')) {
                    return;
                }

                var btn = $(this);
                btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Merging All...');

                var successCount = 0;
                var errorCount = 0;

                processNextGroup(0);

                function processNextGroup(index) {
                    if (index >= duplicateGroups.length) {
                        btn.prop('disabled', false).html('<i class="fas fa-object-group"></i> Merge All Selected');
                        toastr.success('Merged ' + successCount + ' groups successfully.' + (errorCount > 0 ? ' ' + errorCount + ' failed.' : ''));
                        if (successCount > 0) {
                            table.draw();
                            loadDuplicateProducts();
                        }
                        return;
                    }

                    var group = duplicateGroups[index];
                    var primaryId = group.primary_id;
                    var duplicateIds = group.products
                        .filter(function(p) { return p.id !== primaryId; })
                        .map(function(p) { return p.id; });

                    $.ajax({
                        url: "{{ route('products.merge') }}",
                        method: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            primary_product_id: primaryId,
                            duplicate_product_ids: duplicateIds
                        },
                        success: function() { successCount++; },
                        error: function() { errorCount++; },
                        complete: function() { processNextGroup(index + 1); }
                    });
                }
            });

            // Merge a single group (Auto mode)
            function mergeGroup(group, btn) {
                var primaryId = group.primary_id;
                var primaryProduct = group.products.find(function(p) { return p.id === primaryId; });
                var duplicateIds = group.products
                    .filter(function(p) { return p.id !== primaryId; })
                    .map(function(p) { return p.id; });
                var duplicateNames = group.products
                    .filter(function(p) { return p.id !== primaryId; })
                    .map(function(p) { return p.name; })
                    .join(', ');

                if (!confirm('Merge into "' + primaryProduct.name + '"?\n\nProducts to delete: ' + duplicateNames + '\n\nThis action cannot be undone!')) {
                    return;
                }

                var originalHtml = btn.html();
                btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');

                $.ajax({
                    url: "{{ route('products.merge') }}",
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        primary_product_id: primaryId,
                        duplicate_product_ids: duplicateIds
                    },
                    success: function(response) {
                        if (response.success) {
                            toastr.success(response.message);
                            table.draw();
                            var groupIndex = duplicateGroups.indexOf(group);
                            if (groupIndex > -1) {
                                duplicateGroups.splice(groupIndex, 1);
                            }
                            if (duplicateGroups.length === 0) {
                                $('#duplicate-groups-container').hide();
                                $('#no-duplicates-message').show();
                                $('#merge-all-btn').prop('disabled', true);
                                $('#total-duplicate-groups').text('0');
                                $('#total-duplicate-products').text('0');
                                $('#products-to-remove').text('0');
                            } else {
                                renderDuplicateGroups();
                                updateAutoStats();
                            }
                        } else {
                            toastr.error(response.message || 'Failed to merge products.');
                            btn.prop('disabled', false).html(originalHtml);
                        }
                    },
                    error: function(xhr) {
                        var message = 'Failed to merge products.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            message = xhr.responseJSON.message;
                        }
                        toastr.error(message);
                        btn.prop('disabled', false).html(originalHtml);
                    }
                });
            }

            // Update auto mode stats
            function updateAutoStats() {
                var totalGroups = duplicateGroups.length;
                var totalProducts = duplicateGroups.reduce(function(sum, g) {
                    return sum + g.product_count;
                }, 0);
                var productsToRemove = totalProducts - totalGroups;

                $('#total-duplicate-groups').text(totalGroups);
                $('#total-duplicate-products').text(totalProducts);
                $('#products-to-remove').text(productsToRemove);
            }

            // ============================================
            // Manual Merge Functionality
            // ============================================

            // Search for primary product
            $('#primary-product-search').on('input', function() {
                var query = $(this).val();
                clearTimeout(searchTimeout);

                if (query.length < 2) {
                    $('#primary-product-results').html('');
                    return;
                }

                searchTimeout = setTimeout(function() {
                    searchProducts(query, 'primary');
                }, 300);
            });

            // Search for duplicate products
            $('#duplicate-product-search').on('input', function() {
                var query = $(this).val();
                clearTimeout(searchTimeout);

                if (query.length < 2) {
                    $('#duplicate-product-results').html('');
                    return;
                }

                searchTimeout = setTimeout(function() {
                    searchProducts(query, 'duplicate');
                }, 300);
            });

            // Search products function
            function searchProducts(query, type) {
                $.ajax({
                    url: "{{ route('products.merge.search') }}",
                    method: 'GET',
                    data: { q: query },
                    success: function(products) {
                        var resultsContainer = type === 'primary' ? '#primary-product-results' : '#duplicate-product-results';
                        var html = '';

                        if (products.length === 0) {
                            html = '<div class="no-results-message">No products found</div>';
                        } else {
                            products.forEach(function(product) {
                                if (type === 'primary' && selectedPrimaryProduct && selectedPrimaryProduct.id === product.id) {
                                    return;
                                }
                                if (type === 'duplicate') {
                                    if (selectedPrimaryProduct && selectedPrimaryProduct.id === product.id) {
                                        return;
                                    }
                                    if (selectedDuplicates.some(function(d) { return d.id === product.id; })) {
                                        return;
                                    }
                                }

                                html += '<div class="search-result-item" data-product=\'' + JSON.stringify(product) + '\' data-type="' + type + '">' +
                                    '<div class="product-name">' + product.name + ' (ID: ' + product.id + ')</div>' +
                                    '<div class="product-details">' +
                                        '<span class="mr-3"><i class="fas fa-building"></i> ' + product.company + '</span>' +
                                        '<span class="mr-3"><i class="fas fa-tag"></i> ' + product.category + '</span>' +
                                        '<span><i class="fas fa-boxes"></i> Stock: ' + product.current_stock + '</span>' +
                                    '</div>' +
                                '</div>';
                            });
                        }

                        $(resultsContainer).html(html);
                    }
                });
            }

            // Select product from search results
            $(document).on('click', '.search-result-item', function() {
                var product = $(this).data('product');
                var type = $(this).data('type');

                if (type === 'primary') {
                    selectPrimaryProduct(product);
                } else {
                    addDuplicateProduct(product);
                }
            });

            // Select primary product
            function selectPrimaryProduct(product) {
                selectedPrimaryProduct = product;
                $('#primary-product-id').val(product.id);
                $('#primary-product-name').text(product.name + ' (ID: ' + product.id + ')');
                $('#primary-product-details').html(
                    '<span class="mr-3"><i class="fas fa-building"></i> ' + product.company + '</span>' +
                    '<span class="mr-3"><i class="fas fa-tag"></i> ' + product.category + '</span>' +
                    '<span><i class="fas fa-boxes"></i> Stock: ' + product.current_stock + '</span>'
                );
                $('#selected-primary-product').show();
                $('#primary-product-search').val('');
                $('#primary-product-results').html('');
                updateManualMergePreview();
            }

            // Remove primary product
            $('#remove-primary-product').on('click', function() {
                selectedPrimaryProduct = null;
                $('#primary-product-id').val('');
                $('#selected-primary-product').hide();
                updateManualMergePreview();
            });

            // Add duplicate product
            function addDuplicateProduct(product) {
                if (selectedPrimaryProduct && selectedPrimaryProduct.id === product.id) {
                    toastr.warning('This product is already selected as the primary product.');
                    return;
                }

                if (selectedDuplicates.some(function(d) { return d.id === product.id; })) {
                    toastr.warning('This product is already in the duplicates list.');
                    return;
                }

                selectedDuplicates.push(product);
                renderManualDuplicatesList();
                $('#duplicate-product-search').val('');
                $('#duplicate-product-results').html('');
                updateManualMergePreview();
            }

            // Remove duplicate product
            $(document).on('click', '.remove-duplicate-btn', function() {
                var productId = $(this).data('id');
                selectedDuplicates = selectedDuplicates.filter(function(d) {
                    return d.id !== productId;
                });
                renderManualDuplicatesList();
                updateManualMergePreview();
            });

            // Render manual duplicates list
            function renderManualDuplicatesList() {
                var html = '';
                selectedDuplicates.forEach(function(product) {
                    html += '<div class="selected-duplicate-item">' +
                        '<div class="product-info">' +
                            '<div class="product-name">' + product.name + ' (ID: ' + product.id + ')</div>' +
                            '<div class="product-details">' +
                                '<span class="mr-3"><i class="fas fa-building"></i> ' + product.company + '</span>' +
                                '<span class="mr-3"><i class="fas fa-tag"></i> ' + product.category + '</span>' +
                                '<span><i class="fas fa-boxes"></i> Stock: ' + product.current_stock + '</span>' +
                            '</div>' +
                        '</div>' +
                        '<button type="button" class="btn btn-sm btn-danger remove-duplicate-btn" data-id="' + product.id + '">' +
                            '<i class="fas fa-times"></i>' +
                        '</button>' +
                    '</div>';
                });
                $('#selected-duplicates-list').html(html);
            }

            // Update manual merge preview
            function updateManualMergePreview() {
                if (selectedPrimaryProduct && selectedDuplicates.length > 0) {
                    var totalStockToAdd = selectedDuplicates.reduce(function(sum, p) {
                        return sum + parseFloat(p.current_stock);
                    }, 0);
                    var newTotalStock = parseFloat(selectedPrimaryProduct.current_stock) + totalStockToAdd;

                    $('#preview-merge-count').text(selectedDuplicates.length);
                    $('#preview-stock-add').text(totalStockToAdd.toFixed(2));
                    $('#preview-new-stock').text(newTotalStock.toFixed(2));
                    $('#merge-preview').show();
                    $('#merge-products-btn').prop('disabled', false);
                } else {
                    $('#merge-preview').hide();
                    $('#merge-products-btn').prop('disabled', true);
                }
            }

            // Manual merge products button click
            $('#merge-products-btn').on('click', function() {
                if (!selectedPrimaryProduct || selectedDuplicates.length === 0) {
                    toastr.error('Please select a primary product and at least one duplicate product.');
                    return;
                }

                var duplicateIds = selectedDuplicates.map(function(p) { return p.id; });
                var duplicateNames = selectedDuplicates.map(function(p) { return p.name; }).join(', ');

                if (!confirm('Are you sure you want to merge the following products into "' + selectedPrimaryProduct.name + '"?\n\nProducts to delete: ' + duplicateNames + '\n\nThis action cannot be undone!')) {
                    return;
                }

                var btn = $(this);
                btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Merging...');

                $.ajax({
                    url: "{{ route('products.merge') }}",
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        primary_product_id: selectedPrimaryProduct.id,
                        duplicate_product_ids: duplicateIds
                    },
                    success: function(response) {
                        if (response.success) {
                            toastr.success(response.message);
                            $('#mergeDuplicatesModal').modal('hide');
                            table.draw();
                            resetManualMerge();
                        } else {
                            toastr.error(response.message || 'Failed to merge products.');
                        }
                    },
                    error: function(xhr) {
                        var message = 'Failed to merge products.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            message = xhr.responseJSON.message;
                        }
                        toastr.error(message);
                    },
                    complete: function() {
                        btn.prop('disabled', false).html('<i class="fas fa-object-group"></i> Merge Products');
                    }
                });
            });

            // Reset manual merge
            function resetManualMerge() {
                selectedPrimaryProduct = null;
                selectedDuplicates = [];
                $('#primary-product-id').val('');
                $('#primary-product-search').val('');
                $('#duplicate-product-search').val('');
                $('#primary-product-results').html('');
                $('#duplicate-product-results').html('');
                $('#selected-primary-product').hide();
                $('#selected-duplicates-list').html('');
                $('#merge-preview').hide();
                $('#merge-products-btn').prop('disabled', true);
            }

            // Escape HTML to prevent XSS
            function escapeHtml(text) {
                if (!text) return '';
                var div = document.createElement('div');
                div.appendChild(document.createTextNode(text));
                return div.innerHTML;
            }

            // Reset modal when closed
            $('#mergeDuplicatesModal').on('hidden.bs.modal', function() {
                duplicateGroups = [];
                $('#duplicate-groups-container').html('');
                resetManualMerge();
                // Reset to auto tab
                $('#auto-tab').tab('show');
                $('.auto-mode-btn').show();
                $('.manual-mode-btn').hide();
            });
        });
    </script>
@stop
