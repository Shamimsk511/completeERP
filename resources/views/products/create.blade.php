@extends('layouts.modern-admin')

@section('title', 'Create Product')

@section('page_title', 'Create Product')

@section('header_actions')
    <a href="{{ route('products.index') }}" class="btn modern-btn modern-btn-outline">
        <i class="fas fa-arrow-left"></i> Back to Products
    </a>
@stop

@section('page_content')
    <!-- Success/Error Messages -->
    @if($errors->any())
        <div class="alert modern-alert modern-alert-error" id="error-alert">
            <div class="alert-content">
                <i class="fas fa-exclamation-triangle alert-icon"></i>
                <div class="alert-message">
                    <strong>Validation Errors!</strong>
                    <ul class="error-list">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                <button type="button" class="alert-close" onclick="this.parentElement.parentElement.style.display='none'">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
    @endif

    <!-- Main Form Card -->
    <div class="card modern-card">
        <div class="card-header modern-header">
            <div class="header-content">
                <div class="header-title">
                    <i class="fas fa-plus-circle header-icon"></i>
                    <h3 class="card-title">Add New Product</h3>
                </div>
                <div class="header-badge">
                    <span class="badge modern-badge">New Product</span>
                </div>
            </div>
        </div>
        
        <div class="card-body modern-card-body">
            <form action="{{ route('products.store') }}" method="POST" id="product-form" class="modern-form">
                @csrf
                <input type="hidden" name="idempotency_key" value="{{ old('idempotency_key', (string) \Illuminate\Support\Str::uuid()) }}">
                
                <div class="form-grid">
                    <!-- Basic Information Section -->
                    <div class="form-section">
                        <div class="section-header">
                            <h4 class="section-title">
                                <i class="fas fa-info-circle"></i>
                                Basic Information
                            </h4>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="name" class="modern-label required">Product Name</label>
                                <div class="input-wrapper">
                                    <input type="text" 
                                           name="name" 
                                           id="name" 
                                           class="form-control modern-input" 
                                           value="{{ old('name') }}" 
                                           required 
                                           tabindex="1"
                                           placeholder="Enter product name">
                                    <div class="input-icon">
                                        <i class="fas fa-box"></i>
                                    </div>
                                </div>
                                <div class="field-validation" id="name-validation"></div>
                            </div>
                            
                            <div class="form-group">
                                <label for="company_id" class="modern-label required">Company</label>
                                <div class="select-wrapper">
                                    <select name="company_id" 
                                            id="company_id" 
                                            class="form-control modern-select select2" 
                                            required 
                                            tabindex="2">
                                        <option value="">Select Company</option>
                                        @foreach($companies as $company)
                                            <option value="{{ $company->id }}" {{ old('company_id') == $company->id ? 'selected' : '' }}>
                                                {{ $company->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="select-icon">
                                        <i class="fas fa-building"></i>
                                    </div>
                                </div>
                                <button type="button" class="btn btn-sm modern-btn modern-btn-outline mt-2" id="open-company-modal">
                                    <i class="fas fa-plus"></i> Add Company
                                </button>
                                <div class="field-validation" id="company-validation"></div>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="category_id" class="modern-label required">Category</label>
                                <div class="select-wrapper">
                                    <select name="category_id" 
                                            id="category_id" 
                                            class="form-control modern-select select2" 
                                            required 
                                            tabindex="3">
                                        <option value="">Select Category</option>
                                        @foreach($categories as $category)
                                            <option value="{{ $category->id }}" 
                                                    data-box-pcs="{{ $category->box_pcs }}"
                                                    data-pieces-feet="{{ $category->pieces_feet }}"
                                                    {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                                {{ $category->name }} ({{ $category->box_pcs }} pcs/box, {{ $category->pieces_feet }} pcs/feet)
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="select-icon">
                                        <i class="fas fa-tags"></i>
                                    </div>
                                </div>
                                <button type="button" class="btn btn-sm modern-btn modern-btn-outline mt-2" id="open-category-modal">
                                    <i class="fas fa-plus"></i> Add Category
                                </button>
                                <div class="field-validation" id="category-validation"></div>
                                <div class="category-info" id="category-info" style="display: none;">
                                    <div class="info-card">
                                        <div class="info-item">
                                            <span class="info-label">Pieces per Box:</span>
                                            <span class="info-value" id="box-pcs">-</span>
                                        </div>
                                        <div class="info-item">
                                            <span class="info-label">Pieces per Feet:</span>
                                            <span class="info-value" id="pieces-feet">-</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="description" class="modern-label">Description</label>
                                <div class="textarea-wrapper">
                                    <textarea name="description" 
                                              id="description" 
                                              class="form-control modern-textarea" 
                                              rows="3" 
                                              tabindex="4"
                                              maxlength="500"
                                              placeholder="Enter product description (optional)">{{ old('description') }}</textarea>
                                    <div class="textarea-icon">
                                        <i class="fas fa-align-left"></i>
                                    </div>
                                </div>
                                <div class="char-counter">
                                    <span class="char-count">0</span>/500 characters
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Stock Management Section -->
                    <div class="form-section">
                        <div class="section-header">
                            <h4 class="section-title">
                                <i class="fas fa-warehouse"></i>
                                Stock Management
                            </h4>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group full-width">
                                <div class="toggle-section">
                                    <div class="toggle-header">
                                        <label for="is_stock_managed" class="modern-label">Stock Management</label>
                                        <div class="modern-toggle">
                                            <input type="checkbox" 
                                                   class="toggle-input" 
                                                   id="is_stock_managed" 
                                                   name="is_stock_managed" 
                                                   value="1" 
                                                   {{ old('is_stock_managed', true) ? 'checked' : '' }} 
                                                   tabindex="5">
                                            <label class="toggle-label" for="is_stock_managed">
                                                <span class="toggle-slider"></span>
                                            </label>
                                            <span class="toggle-status" id="stock-status-text">
                                                {{ old('is_stock_managed', true) ? 'Enabled' : 'Disabled' }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="toggle-description">
                                        <p class="help-text">Turn off for services or non-trackable items</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-row" id="stock-fields">
                            <div class="form-group" id="opening-stock-group">
                                <label for="opening_stock" class="modern-label required">Opening Stock</label>
                                <div class="input-wrapper">
                                    <input type="number" 
                                           name="opening_stock" 
                                           id="opening_stock" 
                                           class="form-control modern-input" 
                                           value="{{ old('opening_stock', 0) }}" 
                                           step="0.01" 
                                           min="0" 
                                           required 
                                           tabindex="6"
                                           placeholder="0.00">
                                    <div class="input-icon">
                                        <i class="fas fa-boxes"></i>
                                    </div>
                                    <div class="input-suffix">units</div>
                                </div>
                                <div class="help-text">Initial stock quantity when creating the product</div>
                                <div class="field-validation" id="opening-stock-validation"></div>
                            </div>
                            
                            <div class="form-group" id="current-stock-group">
                                <label for="current_stock" class="modern-label">Current Stock</label>
                                <div class="input-wrapper">
                                    <input type="number" 
                                           id="current_stock" 
                                           class="form-control modern-input readonly-input" 
                                           value="{{ old('opening_stock', 0) }}" 
                                           step="0.01" 
                                           min="0" 
                                           readonly>
                                    <div class="input-icon">
                                        <i class="fas fa-warehouse"></i>
                                    </div>
                                    <div class="input-suffix">units</div>
                                </div>
                                <div class="help-text">This will be set automatically based on opening stock</div>
                            </div>
                            
                            @if($godowns->isNotEmpty())
                                @php
                                    $defaultGodownId = optional($godowns->firstWhere('is_default', true))->id;
                                @endphp
                                <div class="form-group" id="default-godown-group">
                                    <label for="default_godown_id" class="modern-label">Default Godown</label>
                                    <div class="select-wrapper">
                                        <select name="default_godown_id" 
                                                id="default_godown_id" 
                                                class="form-control modern-select select2">
                                            <option value="">Default Godown</option>
                                            @foreach($godowns as $godown)
                                                <option value="{{ $godown->id }}" {{ old('default_godown_id', $defaultGodownId) == $godown->id ? 'selected' : '' }}>
                                                    {{ $godown->name }}{{ $godown->location ? ' - ' . $godown->location : '' }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <div class="select-icon">
                                            <i class="fas fa-warehouse"></i>
                                        </div>
                                    </div>
                                    <div class="help-text">Used when allocating stock in purchases and deliveries.</div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Pricing Section -->
                    <div class="form-section">
                        <div class="section-header">
                            <h4 class="section-title">
                                <i class="fas fa-dollar-sign"></i>
                                Pricing & Profit
                            </h4>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="purchase_price" class="modern-label required">Purchase Price</label>
                                <div class="input-wrapper">
                                    <div class="input-prefix">৳</div>
                                    <input type="number" 
                                           name="purchase_price" 
                                           id="purchase_price" 
                                           class="form-control modern-input currency" 
                                           value="{{ old('purchase_price', 0) }}" 
                                           step="0.01" 
                                           min="0" 
                                           required 
                                           tabindex="7"
                                           placeholder="0.00">
                                    <div class="input-icon">
                                        <i class="fas fa-shopping-cart"></i>
                                    </div>
                                </div>
                                <div class="field-validation" id="purchase-price-validation"></div>
                            </div>
                            
                            <div class="form-group">
                                <label for="profit_margin" class="modern-label">Profit Percentage (%)</label>
                                <div class="input-wrapper">
                                    <input type="number" 
                                           id="profit_margin" 
                                           class="form-control modern-input" 
                                           value="15" 
                                           step="0.01" 
                                           min="0" 
                                           max="1000"
                                           tabindex="8"
                                           placeholder="15.00">
                                    <div class="input-icon">
                                        <i class="fas fa-percentage"></i>
                                    </div>
                                    <div class="input-suffix">%</div>
                                </div>
                                <div class="help-text">Default: 15% - Profit on purchase price (Purchase + 15% = Sale)</div>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="sale_price" class="modern-label required">Sale Price</label>
                                <div class="input-wrapper">
                                    <div class="input-prefix">৳</div>
                                    <input type="number" 
                                           name="sale_price" 
                                           id="sale_price" 
                                           class="form-control modern-input currency" 
                                           value="{{ old('sale_price', 0) }}" 
                                           step="0.01" 
                                           min="0" 
                                           required 
                                           tabindex="9"
                                           placeholder="0.00">
                                    <div class="input-icon">
                                        <i class="fas fa-tag"></i>
                                    </div>
                                </div>
                                <div class="field-validation" id="sale-price-validation"></div>
                            </div>
                            
                            <div class="form-group">
                                <label class="modern-label">Quick Actions</label>
                                <div class="quick-actions">
                                    <button type="button" id="copy-purchase-price-btn" class="btn modern-btn modern-btn-secondary btn-sm" data-pulse-on-click>
                                        <i class="fas fa-copy"></i> Copy Purchase Price
                                    </button>
                                    <button type="button" id="reset-prices-btn" class="btn modern-btn modern-btn-outline btn-sm" data-pulse-on-click>
                                        <i class="fas fa-undo"></i> Reset
                                    </button>
                                </div>
                                <div class="help-text">Sale price calculates automatically as you type</div>
                            </div>
                        </div>
                        
                        <!-- Real-time Profit Display -->
                        <div class="form-row">
                            <div class="form-group full-width">
                                <div class="profit-display" id="profit-display">
                                    <div class="profit-card">
                                        <div class="profit-header">
                                            <h5><i class="fas fa-chart-line"></i> Profit Analysis</h5>
                                        </div>
                                        <div class="profit-metrics">
                                            <div class="metric">
                                                <span class="metric-label">Profit Amount</span>
                                                <span class="metric-value" id="profit-amount">৳0.00</span>
                                            </div>
                                            <div class="metric">
                                                <span class="metric-label">Profit Margin</span>
                                                <span class="metric-value" id="calculated-margin">0%</span>
                                            </div>
                                            <div class="metric">
                                                <span class="metric-label">Markup</span>
                                                <span class="metric-value" id="markup-percentage">0%</span>
                                            </div>
                                            <div class="metric">
                                                <span class="metric-label">ROI</span>
                                                <span class="metric-value" id="roi-percentage">0%</span>
                                            </div>
                                        </div>
                                        <div class="profit-status" id="profit-status">
                                            <div class="status-indicator">
                                                <i class="fas fa-info-circle"></i>
                                                <span id="profit-status-text">Enter prices to see profit analysis</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Weight Override Section -->
                    <div class="form-section">
                        <div class="section-header">
                            <h4 class="section-title">
                                <i class="fas fa-weight-hanging"></i>
                                Weight (Optional Override)
                            </h4>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="weight_value" class="modern-label">Weight Value</label>
                                <div class="input-wrapper">
                                    <input type="number"
                                           name="weight_value"
                                           id="weight_value"
                                           class="form-control modern-input"
                                           value="{{ old('weight_value') }}"
                                           step="0.001"
                                           min="0"
                                           placeholder="e.g., 5.5">
                                    <div class="input-icon">
                                        <i class="fas fa-weight-hanging"></i>
                                    </div>
                                    <div class="input-suffix">kg</div>
                                </div>
                                <div class="help-text">Leave empty to use category weight. Set a value to override category weight for this product.</div>
                            </div>

                            <div class="form-group">
                                <label for="weight_unit" class="modern-label">Weight Unit</label>
                                <div class="select-wrapper">
                                    <select name="weight_unit"
                                            id="weight_unit"
                                            class="form-control modern-select">
                                        <option value="">Use Category Default</option>
                                        <option value="per_piece" {{ old('weight_unit') == 'per_piece' ? 'selected' : '' }}>Per Piece</option>
                                        <option value="per_box" {{ old('weight_unit') == 'per_box' ? 'selected' : '' }}>Per Box</option>
                                        <option value="per_unit" {{ old('weight_unit') == 'per_unit' ? 'selected' : '' }}>Per Unit</option>
                                    </select>
                                    <div class="select-icon">
                                        <i class="fas fa-balance-scale"></i>
                                    </div>
                                </div>
                                <div class="help-text">If weight value is set, select the unit type.</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="form-actions">
                    <button type="button" id="preview-btn" class="btn modern-btn modern-btn-primary" tabindex="10">
                        <i class="fas fa-eye"></i> Preview & Create Product
                    </button>
                    <button type="button" id="quick-save-btn" class="btn modern-btn modern-btn-success" tabindex="11" data-loading-text="<i class='fas fa-spinner fa-spin'></i> Saving...">
                        <i class="fas fa-save"></i> Quick Save
                    </button>
                    <a href="{{ route('products.index') }}" class="btn modern-btn modern-btn-outline" tabindex="12">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Review Confirmation Modal -->
    <div class="modal fade modern-modal" id="reviewModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content modern-modal-content">
                <div class="modal-header modern-modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-check-circle"></i>
                        Review Product Details
                    </h5>
                    <button type="button" class="close modern-close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body modern-modal-body">
                    <div class="review-section">
                        <div class="review-card">
                            <div class="review-header">
                                <h6><i class="fas fa-info-circle"></i> Basic Information</h6>
                            </div>
                            <div class="review-content">
                                <div class="review-item">
                                    <span class="review-label">Product Name:</span>
                                    <span class="review-value" id="review-name">-</span>
                                </div>
                                <div class="review-item">
                                    <span class="review-label">Company:</span>
                                    <span class="review-value" id="review-company">-</span>
                                </div>
                                <div class="review-item">
                                    <span class="review-label">Category:</span>
                                    <span class="review-value" id="review-category">-</span>
                                </div>
                                <div class="review-item">
                                    <span class="review-label">Description:</span>
                                    <span class="review-value" id="review-description">-</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="review-card">
                            <div class="review-header">
                                <h6><i class="fas fa-warehouse"></i> Stock & Pricing</h6>
                            </div>
                            <div class="review-content">
                                <div class="review-item">
                                    <span class="review-label">Stock Management:</span>
                                    <span class="review-value" id="review-stock-management">-</span>
                                </div>
                                <div class="review-item">
                                    <span class="review-label">Opening Stock:</span>
                                    <span class="review-value" id="review-opening-stock">-</span>
                                </div>
                                <div class="review-item">
                                    <span class="review-label">Purchase Price:</span>
                                    <span class="review-value" id="review-purchase-price">-</span>
                                </div>
                                <div class="review-item">
                                    <span class="review-label">Sale Price:</span>
                                    <span class="review-value" id="review-sale-price">-</span>
                                </div>
                                <div class="review-item">
                                    <span class="review-label">Profit Amount:</span>
                                    <span class="review-value" id="review-profit-amount">-</span>
                                </div>
                                <div class="review-item">
                                    <span class="review-label">Profit Margin:</span>
                                    <span class="review-value" id="review-profit-margin">-</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer modern-modal-footer">
                    <button type="button" class="btn modern-btn modern-btn-outline" data-dismiss="modal">
                        <i class="fas fa-edit"></i> Edit Details
                    </button>
                    <button type="button" id="save-btn" class="btn modern-btn modern-btn-success" data-loading-text="<i class='fas fa-spinner fa-spin'></i> Saving...">
                        <i class="fas fa-save"></i> Save
                    </button>
                    <button type="button" id="save-new-btn" class="btn modern-btn modern-btn-primary" data-loading-text="<i class='fas fa-spinner fa-spin'></i> Saving...">
                        <i class="fas fa-plus"></i> Save & New
                    </button>
                    <button type="button" class="btn modern-btn modern-btn-danger" data-dismiss="modal">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Company Modal -->
    <div class="modal fade" id="addCompanyModal" tabindex="-1" role="dialog" aria-labelledby="addCompanyModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-md modal-dialog-centered" role="document">
            <div class="modal-content modern-modal">
                <div class="modal-header modern-modal-header">
                    <h5 class="modal-title" id="addCompanyModalLabel">
                        <i class="fas fa-building"></i> Add Company
                    </h5>
                    <button type="button" class="close modern-close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="add-company-form">
                    @csrf
                    <div class="modal-body modern-modal-body">
                        <div class="form-group modern-form-group">
                            <label for="company_name_modal" class="modern-label required">Company Name</label>
                            <input type="text" id="company_name_modal" name="name" class="form-control modern-input" required>
                        </div>
                        <div class="form-group modern-form-group">
                            <label for="company_type_modal" class="modern-label">Company Type</label>
                            <select id="company_type_modal" name="type" class="form-control modern-select">
                                <option value="brand" selected>Brand</option>
                                <option value="supplier">Supplier</option>
                                <option value="both">Brand & Supplier</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer modern-modal-footer">
                        <button type="button" class="btn modern-btn modern-btn-outline" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn modern-btn modern-btn-primary" id="save-company-btn">
                            <i class="fas fa-save"></i> Save Company
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Add Category Modal -->
    <div class="modal fade" id="addCategoryModal" tabindex="-1" role="dialog" aria-labelledby="addCategoryModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-md modal-dialog-centered" role="document">
            <div class="modal-content modern-modal">
                <div class="modal-header modern-modal-header">
                    <h5 class="modal-title" id="addCategoryModalLabel">
                        <i class="fas fa-tags"></i> Add Category
                    </h5>
                    <button type="button" class="close modern-close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="add-category-form">
                    @csrf
                    <div class="modal-body modern-modal-body">
                        <div class="form-group modern-form-group">
                            <label for="category_name_modal" class="modern-label required">Category Name</label>
                            <input type="text" id="category_name_modal" name="name" class="form-control modern-input" required>
                        </div>
                        <div class="form-group modern-form-group">
                            <label class="modern-label">Simple Product</label>
                            <div class="modern-toggle">
                                <input type="checkbox" class="toggle-input" id="category_simple_modal" checked>
                                <label class="toggle-label" for="category_simple_modal">
                                    <span class="toggle-slider"></span>
                                </label>
                                <span class="toggle-status text-success" id="category_simple_status">On</span>
                            </div>
                        </div>
                        <div id="category-dimensions-modal" style="display: none;">
                            <div class="form-group modern-form-group">
                                <label for="category_box_pcs_modal" class="modern-label required">Box PCS</label>
                                <input type="number" id="category_box_pcs_modal" class="form-control modern-input" min="1" step="1" value="1">
                            </div>
                            <div class="form-group modern-form-group">
                                <label for="category_tile_width_modal" class="modern-label required">Tile Width (in)</label>
                                <input type="number" id="category_tile_width_modal" class="form-control modern-input" min="0" step="0.01" value="1">
                            </div>
                            <div class="form-group modern-form-group">
                                <label for="category_tile_length_modal" class="modern-label required">Tile Length (in)</label>
                                <input type="number" id="category_tile_length_modal" class="form-control modern-input" min="0" step="0.01" value="1">
                            </div>
                        </div>
                        <div class="form-group modern-form-group">
                            <label for="category_weight_value_modal" class="modern-label">Weight (optional)</label>
                            <div class="input-wrapper">
                                <input type="number" id="category_weight_value_modal" class="form-control modern-input" min="0" step="0.001" placeholder="0.000">
                                <div class="input-suffix">KG</div>
                            </div>
                        </div>
                        <div class="form-group modern-form-group">
                            <label for="category_weight_unit_modal" class="modern-label">Weight Unit</label>
                            <select id="category_weight_unit_modal" class="form-control modern-select">
                                <option value="per_piece">Per Piece</option>
                                <option value="per_box">Per Box</option>
                                <option value="per_unit">Per Unit</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer modern-modal-footer">
                        <button type="button" class="btn modern-btn modern-btn-outline" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn modern-btn modern-btn-primary" id="save-category-btn">
                            <i class="fas fa-save"></i> Save Category
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop

@section('additional_css')
    <!-- External Modern Admin Styles -->
    <link href="{{ asset('css/modern-admin.css') }}" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    
    <!-- Page-specific styles for profit display -->
    <style>
        /* Profit Display Specific Styles */
        .profit-card {
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.05) 0%, rgba(5, 150, 105, 0.05) 100%);
            border: 1px solid rgba(16, 185, 129, 0.1);
            border-radius: 12px;
            padding: 20px;
            transition: all 0.3s ease;
        }

        .profit-card.negative {
            background: linear-gradient(135deg, rgba(239, 68, 68, 0.05) 0%, rgba(220, 38, 38, 0.05) 100%);
            border-color: rgba(239, 68, 68, 0.1);
        }

        .profit-header {
            margin-bottom: 16px;
        }

        .profit-header h5 {
            color: #059669;
            font-weight: 600;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .profit-card.negative .profit-header h5 {
            color: #dc2626;
        }

        .profit-metrics {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            gap: 20px;
            margin-bottom: 16px;
        }

        .metric {
            display: flex;
            flex-direction: column;
            gap: 4px;
            text-align: center;
            padding: 12px;
            background: white;
            border-radius: 8px;
            border: 1px solid rgba(16, 185, 129, 0.1);
            transition: all 0.3s ease;
        }

        .profit-card.negative .metric {
            border-color: rgba(239, 68, 68, 0.1);
        }

        .metric-label {
            font-size: 12px;
            color: #6b7280;
            font-weight: 500;
        }

        .metric-value {
            font-size: 16px;
            color: #059669;
            font-weight: 700;
        }

        .metric-value.negative {
            color: #dc2626;
        }

        .metric-value.neutral {
            color: #6b7280;
        }

        .profit-status {
            padding: 12px;
            background: rgba(16, 185, 129, 0.1);
            border-radius: 8px;
            border: 1px solid rgba(16, 185, 129, 0.2);
        }

        .profit-card.negative .profit-status {
            background: rgba(239, 68, 68, 0.1);
            border-color: rgba(239, 68, 68, 0.2);
        }

        .status-indicator {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            font-weight: 500;
        }

        .status-indicator i {
            color: #059669;
        }

        .profit-card.negative .status-indicator i {
            color: #dc2626;
        }

        @media (max-width: 768px) {
            .profit-metrics {
                grid-template-columns: repeat(2, 1fr);
                gap: 12px;
            }
        }

        /* Duplicate Warning Styles */
        .duplicate-warning {
            background: linear-gradient(135deg, rgba(245, 158, 11, 0.1) 0%, rgba(217, 119, 6, 0.1) 100%);
            border: 1px solid rgba(245, 158, 11, 0.3);
            border-radius: 8px;
            padding: 12px 16px;
            margin-top: 8px;
            animation: slideDown 0.3s ease;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .duplicate-warning .warning-header {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #b45309;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .duplicate-warning .warning-header i {
            font-size: 16px;
        }

        .duplicate-warning .duplicate-list {
            background: white;
            border-radius: 6px;
            padding: 10px;
            margin-top: 8px;
        }

        .duplicate-warning .duplicate-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px;
            border-bottom: 1px solid #f3f4f6;
        }

        .duplicate-warning .duplicate-item:last-child {
            border-bottom: none;
        }

        .duplicate-warning .duplicate-item .item-info {
            flex: 1;
        }

        .duplicate-warning .duplicate-item .item-name {
            font-weight: 600;
            color: #374151;
        }

        .duplicate-warning .duplicate-item .item-details {
            font-size: 12px;
            color: #6b7280;
        }

        .duplicate-warning .duplicate-item .item-action {
            margin-left: 10px;
        }

        .duplicate-warning .warning-actions {
            display: flex;
            gap: 10px;
            margin-top: 12px;
        }

        .duplicate-warning .btn-view-product {
            background: #f59e0b;
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 12px;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .duplicate-warning .btn-view-product:hover {
            background: #d97706;
        }

        .name-checking {
            position: relative;
        }

        .name-checking::after {
            content: '';
            position: absolute;
            right: 45px;
            top: 50%;
            transform: translateY(-50%);
            width: 16px;
            height: 16px;
            border: 2px solid #e5e7eb;
            border-top-color: #3b82f6;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }

        @keyframes spin {
            to { transform: translateY(-50%) rotate(360deg); }
        }

        .name-valid .input-icon i {
            color: #10b981 !important;
        }

        .name-duplicate .input-icon i {
            color: #f59e0b !important;
        }
    </style>
@stop

@section('additional_js')
    <!-- External Modern Admin Scripts -->
    <script src="{{ asset('js/modern-admin.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    
    <!-- Page-specific Product Create Logic -->
    <script>
        $(document).ready(function() {
            // Product-specific functionality
            const ProductCreate = {
                duplicateCheckTimeout: null,
                hasDuplicate: false,

                // Initialize product create specific features
                init: function() {
                    this.initDuplicateCheck();
                    this.initProfitCalculations();
                    this.initStockManagement();
                    this.initCategoryHandler();
                    this.initFormActions();
                    this.enableAutoSave();

                    // Focus on first field
                    $('#name').focus();

                    console.log('Product Create initialized');
                },

                // Initialize duplicate name checking
                initDuplicateCheck: function() {
                    const self = this;
                    const nameInput = $('#name');
                    const nameWrapper = nameInput.closest('.input-wrapper');

                    // Check on input with debounce
                    nameInput.on('input', function() {
                        clearTimeout(self.duplicateCheckTimeout);
                        const name = $(this).val().trim();

                        // Remove previous states
                        nameWrapper.removeClass('name-checking name-valid name-duplicate');
                        $('#duplicate-warning').remove();

                        if (name.length < 2) {
                            self.hasDuplicate = false;
                            return;
                        }

                        // Show checking state
                        nameWrapper.addClass('name-checking');

                        // Debounce the check
                        self.duplicateCheckTimeout = setTimeout(function() {
                            self.checkDuplicateName(name);
                        }, 500);
                    });

                    // Also check on blur
                    nameInput.on('blur', function() {
                        clearTimeout(self.duplicateCheckTimeout);
                        const name = $(this).val().trim();
                        if (name.length >= 2) {
                            self.checkDuplicateName(name);
                        }
                    });
                },

                // Check for duplicate product name via AJAX
                checkDuplicateName: function(name) {
                    const self = this;
                    const nameWrapper = $('#name').closest('.input-wrapper');
                    const validationDiv = $('#name-validation');

                    $.ajax({
                        url: '{{ route("products.check-duplicate") }}',
                        method: 'GET',
                        data: { name: name },
                        success: function(response) {
                            nameWrapper.removeClass('name-checking');

                            if (response.exists) {
                                self.hasDuplicate = true;
                                nameWrapper.addClass('name-duplicate');
                                self.showDuplicateWarning(response.products, validationDiv);
                            } else {
                                self.hasDuplicate = false;
                                nameWrapper.addClass('name-valid');
                                $('#duplicate-warning').remove();
                            }
                        },
                        error: function() {
                            nameWrapper.removeClass('name-checking');
                            self.hasDuplicate = false;
                        }
                    });
                },

                // Show duplicate warning with product details
                showDuplicateWarning: function(products, container) {
                    // Remove existing warning
                    $('#duplicate-warning').remove();

                    let productItems = '';
                    products.forEach(function(product) {
                        productItems += `
                            <div class="duplicate-item">
                                <div class="item-info">
                                    <div class="item-name">${product.name}</div>
                                    <div class="item-details">
                                        Company: ${product.company} | Category: ${product.category} | Stock: ${product.current_stock}
                                    </div>
                                </div>
                                <div class="item-action">
                                    <a href="/products/${product.id}" target="_blank" class="btn-view-product">
                                        <i class="fas fa-external-link-alt"></i> View
                                    </a>
                                </div>
                            </div>
                        `;
                    });

                    const warningHtml = `
                        <div id="duplicate-warning" class="duplicate-warning">
                            <div class="warning-header">
                                <i class="fas fa-exclamation-triangle"></i>
                                <span>Product with this name already exists!</span>
                            </div>
                            <p style="margin: 0; color: #92400e; font-size: 13px;">
                                Please use a different name or consider editing the existing product instead of creating a duplicate.
                            </p>
                            <div class="duplicate-list">
                                ${productItems}
                            </div>
                        </div>
                    `;

                    container.after(warningHtml);
                },

                // Profit calculation functions
                initProfitCalculations: function() {
                    // Auto-calculate on price and margin changes
                    $('#purchase_price, #profit_margin').on('input', function() {
                        const purchasePrice = parseFloat($('#purchase_price').val()) || 0;
                        const profitPercentage = parseFloat($('#profit_margin').val()) || 0;
                        
                        // Auto-calculate sale price when both purchase price and profit percentage are available
                        if (purchasePrice > 0 && profitPercentage >= 0) {
                            const result = ProductCreate.calculateProfitAndSalePrice(purchasePrice, profitPercentage);
                            $('#sale_price').val(result.salePrice.toFixed(2));
                        }
                        
                        // Always calculate profit metrics
                        ProductCreate.calculateProfit();
                    });

                    // Only calculate profit when sale price changes (don't auto-update sale price)
                    $('#sale_price').on('input', function() {
                        ProductCreate.calculateProfit();
                    });

                    // Initialize with current values
                    ProductCreate.calculateProfit();
                },

                // Calculate profit and sale price using the correct formula
                calculateProfitAndSalePrice: function(purchasePrice, profitPercentage) {
                    let profit = purchasePrice * (profitPercentage / 100);
                    let salePrice = purchasePrice + profit;
                    return { profit, salePrice };
                },

                // Calculate all profit metrics
                calculateProfit: function() {
                    const purchasePrice = parseFloat($('#purchase_price').val()) || 0;
                    const salePrice = parseFloat($('#sale_price').val()) || 0;
                    
                    if (purchasePrice === 0 && salePrice === 0) {
                        // Reset display when both are zero
                        $('#profit-amount').text('৳0.00');
                        $('#calculated-margin').text('0%');
                        $('#markup-percentage').text('0%');
                        $('#roi-percentage').text('0%');
                        this.updateProfitStatus(0, 0);
                        return;
                    }
                    
                    const profitAmount = salePrice - purchasePrice;
                    
                    // Profit margin = (Profit / Sale Price) * 100
                    const profitMargin = salePrice > 0 ? ((profitAmount / salePrice) * 100) : 0;
                    
                    // Markup = (Profit / Purchase Price) * 100  
                    const markup = purchasePrice > 0 ? ((profitAmount / purchasePrice) * 100) : 0;
                    
                    // ROI = Same as markup (Return on Investment)
                    const roi = markup;
                    
                    // Update display
                    $('#profit-amount').text('৳' + profitAmount.toFixed(2));
                    $('#calculated-margin').text(profitMargin.toFixed(2) + '%');
                    $('#markup-percentage').text(markup.toFixed(2) + '%');
                    $('#roi-percentage').text(roi.toFixed(2) + '%');
                    
                    // Update profit status
                    this.updateProfitStatus(profitAmount, profitMargin);
                    
                    // Color coding and card styling
                    const profitCard = $('.profit-card');
                    const metricValues = $('.metric-value');
                    
                    if (profitAmount > 0) {
                        profitCard.removeClass('negative');
                        metricValues.removeClass('negative neutral');
                    } else if (profitAmount < 0) {
                        profitCard.addClass('negative');
                        metricValues.removeClass('positive neutral').addClass('negative');
                    } else {
                        profitCard.removeClass('negative');
                        metricValues.removeClass('positive negative').addClass('neutral');
                    }
                },

                // Update profit status text
                updateProfitStatus: function(profitAmount, profitMargin) {
                    let statusText = '';
                    
                    if (profitAmount === 0) {
                        statusText = 'Break-even pricing. No profit or loss.';
                    } else if (profitAmount < 0) {
                        statusText = 'Loss detected! Sale price is below purchase price.';
                    } else if (profitMargin < 10) {
                        statusText = 'Low profit margin. Consider increasing sale price.';
                    } else if (profitMargin < 20) {
                        statusText = 'Good profit margin. Competitive pricing.';
                    } else if (profitMargin < 50) {
                        statusText = 'Excellent profit margin! Great profitability.';
                    } else {
                        statusText = 'Very high profit margin. Ensure competitive pricing.';
                    }
                    
                    $('#profit-status-text').text(statusText);
                },

                // Stock management toggle
                initStockManagement: function() {
                    // Update current stock when opening stock changes
                    $('#opening_stock').on('input', function() {
                        $('#current_stock').val($(this).val());
                    });

                    // Handle stock management toggle
                    const toggleStockManagement = () => {
                        const isChecked = $('#is_stock_managed').is(':checked');
                        const statusText = $('#stock-status-text');
                        const stockFields = $('#stock-fields');
                        const openingStockInput = $('#opening_stock');
                        
                        if (isChecked) {
                            statusText.text('Enabled').removeClass('text-danger').addClass('text-success');
                            stockFields.removeClass('stock-disabled');
                            openingStockInput.prop('required', true);
                        } else {
                            statusText.text('Disabled').removeClass('text-success').addClass('text-danger');
                            stockFields.addClass('stock-disabled');
                            openingStockInput.prop('required', false);
                            openingStockInput.val(0);
                            $('#current_stock').val(0);
                        }
                    };

                    // Initialize toggle state
                    toggleStockManagement();

                    // Handle toggle change
                    $('#is_stock_managed').on('change', toggleStockManagement);
                },

                // Category selection handler
                initCategoryHandler: function() {
                    $('#category_id').on('change', function() {
                        const selectedOption = $(this).find('option:selected');
                        const boxPcs = selectedOption.data('box-pcs');
                        const piecesFeet = selectedOption.data('pieces-feet');
                        
                        if (boxPcs && piecesFeet) {
                            $('#box-pcs').text(boxPcs);
                            $('#pieces-feet').text(piecesFeet);
                            $('#category-info').slideDown(300);
                        } else {
                            $('#category-info').slideUp(300);
                        }
                        
                        // Auto-focus on description if category is selected
                        if ($(this).val()) {
                            setTimeout(() => {
                                $('#description').focus();
                            }, 100);
                        }
                    });

                    // Company change handler
                    $('#company_id').on('change', function() {
                        if ($(this).val()) {
                            // Auto-focus on category
                            setTimeout(() => {
                                $('#category_id').select2('open');
                            }, 100);
                        }
                    });
                },

                // Form action handlers
                initFormActions: function() {
                    let submitting = false;
                    const self = this;

                    // Check for duplicate before form submission
                    const checkDuplicateBeforeSubmit = function(callback) {
                        if (self.hasDuplicate) {
                            ModernAdmin.showAlert('A product with this name already exists. Please use a different name.', 'warning');
                            $('#name').focus();
                            return false;
                        }
                        callback();
                        return true;
                    };

                    $('#product-form').on('submit', function(e) {
                        if (submitting) {
                            e.preventDefault();
                            return false;
                        }
                        if (self.hasDuplicate) {
                            e.preventDefault();
                            ModernAdmin.showAlert('A product with this name already exists. Please use a different name.', 'warning');
                            $('#name').focus();
                            return false;
                        }
                        submitting = true;
                        $('#quick-save-btn, #save-btn, #save-new-btn').prop('disabled', true);
                        return true;
                    });

                    // Copy purchase price button
                    $('#copy-purchase-price-btn').on('click', function() {
                        const purchasePrice = $('#purchase_price').val();
                        $('#sale_price').val(purchasePrice);
                        ProductCreate.calculateProfit();
                    });

                    // Reset prices button
                    $('#reset-prices-btn').on('click', function() {
                        $('#purchase_price').val(0);
                        $('#sale_price').val(0);
                        $('#profit_margin').val(15);
                        ProductCreate.calculateProfit();
                    });

                    // Preview button
                    $('#preview-btn').on('click', function() {
                        if (!ModernAdmin.validateForm('#product-form')) {
                            ModernAdmin.showAlert('Please fill in all required fields before previewing.', 'warning');
                            return;
                        }
                        if (self.hasDuplicate) {
                            ModernAdmin.showAlert('A product with this name already exists. Please use a different name before previewing.', 'warning');
                            $('#name').focus();
                            return;
                        }
                        ProductCreate.updateReviewSection();
                        $('#reviewModal').modal('show');
                    });

                    // Quick save button
                    $('#quick-save-btn').on('click', function() {
                        if (!ModernAdmin.validateForm('#product-form')) {
                            ModernAdmin.showAlert('Please fill in all required fields before saving.', 'warning');
                            return;
                        }
                        if (self.hasDuplicate) {
                            ModernAdmin.showAlert('A product with this name already exists. Please use a different name.', 'warning');
                            $('#name').focus();
                            return;
                        }
                        ModernAdmin.setButtonLoading($(this), true);
                        $('#product-form')[0].submit();
                    });

                    // Save button
                    $('#save-btn').on('click', function() {
                        if (self.hasDuplicate) {
                            ModernAdmin.showAlert('A product with this name already exists. Please use a different name.', 'warning');
                            $('#reviewModal').modal('hide');
                            $('#name').focus();
                            return;
                        }
                        ModernAdmin.setButtonLoading($(this), true);
                        $('#product-form')[0].submit();
                    });

                    // Save & New button
                    $('#save-new-btn').on('click', function() {
                        if (self.hasDuplicate) {
                            ModernAdmin.showAlert('A product with this name already exists. Please use a different name.', 'warning');
                            $('#reviewModal').modal('hide');
                            $('#name').focus();
                            return;
                        }
                        // Remove any existing save_and_new input
                        $('input[name="save_and_new"]').remove();

                        // Add hidden input to indicate save and new action
                        $('<input>').attr({
                            type: 'hidden',
                            name: 'save_and_new',
                            value: '1'
                        }).appendTo('#product-form');

                        ModernAdmin.setButtonLoading($(this), true);
                        $('#product-form')[0].submit();
                    });
                },

                // Update review modal with current form data
                updateReviewSection: function() {
                    $('#review-name').text($('#name').val() || '-');
                    $('#review-company').text($('#company_id option:selected').text() || '-');
                    $('#review-category').text($('#category_id option:selected').text() || '-');
                    $('#review-description').text($('#description').val() || 'No description provided');
                    $('#review-stock-management').text($('#is_stock_managed').is(':checked') ? 'Enabled' : 'Disabled');
                    $('#review-opening-stock').text($('#opening_stock').val() + ' units' || '-');
                    $('#review-purchase-price').text(ModernAdmin.utils.formatCurrency($('#purchase_price').val() || 0));
                    $('#review-sale-price').text(ModernAdmin.utils.formatCurrency($('#sale_price').val() || 0));
                    $('#review-profit-amount').text($('#profit-amount').text());
                    $('#review-profit-margin').text($('#calculated-margin').text());
                },

                // Enable auto-save functionality
                enableAutoSave: function() {
                    ModernAdmin.form.enableAutoSave('#product-form', 'product_draft', 30000);
                    ModernAdmin.form.loadSavedData('#product-form', 'product_draft');
                }
            };

            // Add Company modal handling
            $('#open-company-modal').on('click', function() {
                $('#addCompanyModal').modal('show');
                setTimeout(() => $('#company_name_modal').trigger('focus'), 150);
            });

            $('#add-company-form').on('submit', function(e) {
                e.preventDefault();
                const $btn = $('#save-company-btn');
                const originalHtml = $btn.html();
                const name = $('#company_name_modal').val().trim();
                const type = $('#company_type_modal').val() || 'brand';

                if (!name) {
                    return;
                }

                $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');

                $.ajax({
                    url: '/companies',
                    method: 'POST',
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content'),
                        name: name,
                        type: type
                    },
                    success: function(response) {
                        const newOption = new Option(response.name, response.id, true, true);
                        $('#company_id').append(newOption).trigger('change');
                        $('#addCompanyModal').modal('hide');
                        $('#company_name_modal').val('');
                        $('#company_type_modal').val('brand');
                        if (response.was_existing) {
                            if (typeof Swal !== 'undefined') {
                                Swal.fire({
                                    icon: 'info',
                                    title: 'Already exists',
                                    text: response.name + ' was already created. Selected it for you.',
                                    timer: 2000,
                                    showConfirmButton: false
                                });
                            }
                        }
                    },
                    error: function(xhr) {
                        let msg = 'Failed to create company';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            msg = xhr.responseJSON.message;
                        } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                            msg = Object.values(xhr.responseJSON.errors).flat().join('\n');
                        }
                        alert(msg);
                    },
                    complete: function() {
                        $btn.prop('disabled', false).html(originalHtml);
                    }
                });
            });

            // Add Category modal handling
            const toggleCategoryDims = function() {
                const isSimple = $('#category_simple_modal').is(':checked');
                $('#category_simple_status').text(isSimple ? 'On' : 'Off')
                    .toggleClass('text-success', isSimple)
                    .toggleClass('text-danger', !isSimple);
                $('#category-dimensions-modal').toggle(!isSimple);
            };

            $('#open-category-modal').on('click', function() {
                $('#addCategoryModal').modal('show');
                setTimeout(() => $('#category_name_modal').trigger('focus'), 150);
            });

            $('#category_simple_modal').on('change', toggleCategoryDims);
            toggleCategoryDims();

            $('#add-category-form').on('submit', function(e) {
                e.preventDefault();
                const $btn = $('#save-category-btn');
                const originalHtml = $btn.html();

                const name = $('#category_name_modal').val().trim();
                const isSimple = $('#category_simple_modal').is(':checked');
                const boxPcs = $('#category_box_pcs_modal').val();
                const tileWidth = $('#category_tile_width_modal').val();
                const tileLength = $('#category_tile_length_modal').val();
                const weightValue = $('#category_weight_value_modal').val();
                const weightUnit = $('#category_weight_unit_modal').val();

                if (!name) {
                    return;
                }

                $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');

                $.ajax({
                    url: '/categories',
                    method: 'POST',
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content'),
                        name: name,
                        is_simple_product: isSimple ? 1 : 0,
                        box_pcs: isSimple ? 0 : boxPcs,
                        tile_width_in: isSimple ? null : tileWidth,
                        tile_length_in: isSimple ? null : tileLength,
                        weight_value: weightValue || null,
                        weight_unit: weightValue ? weightUnit : null
                    },
                    success: function(response) {
                        const box = response.box_pcs || 0;
                        const feet = response.pieces_feet || 0;
                        const optionText = `${response.name} (${box} pcs/box, ${feet} pcs/feet)`;
                        const newOption = $('<option>', {
                            value: response.id,
                            text: optionText,
                            selected: true
                        }).attr('data-box-pcs', box)
                          .attr('data-pieces-feet', feet);

                        $('#category_id').append(newOption).trigger('change');
                        $('#addCategoryModal').modal('hide');
                        $('#category_name_modal').val('');
                        $('#category_simple_modal').prop('checked', true);
                        $('#category_box_pcs_modal').val(1);
                        $('#category_tile_width_modal').val(1);
                        $('#category_tile_length_modal').val(1);
                        $('#category_weight_value_modal').val('');
                        $('#category_weight_unit_modal').val('per_piece');
                        toggleCategoryDims();
                    },
                    error: function(xhr) {
                        let msg = 'Failed to create category';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            msg = xhr.responseJSON.message;
                        } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                            msg = Object.values(xhr.responseJSON.errors).flat().join('\n');
                        }
                        alert(msg);
                    },
                    complete: function() {
                        $btn.prop('disabled', false).html(originalHtml);
                    }
                });
            });

            // Initialize product create functionality
            ProductCreate.init();

            // Initialize character counter for description
            ModernAdmin.updateCharacterCounter($('#description'));
        });
    </script>
@stop
