@extends('layouts.modern-admin')

@section('title', 'Create Purchase')

@section('page_title', 'Create New Purchase Order')

@section('header_actions')
    <div class="header-actions-group">
        <a href="{{ route('purchases.index') }}" class="btn modern-btn modern-btn-outline"
           onclick="handleMobileAction(event, 'back', null)">
            <i class="fas fa-arrow-left"></i> <span class="btn-text">Back to Purchases</span>
        </a>
    </div>
@stop



@section('page_content')
    <!-- Error Messages -->
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

    <form action="{{ route('purchases.store') }}" method="POST" id="purchase-form" class="modern-form">
        @csrf
        
        <div class="row">
            <!-- Purchase Information -->
            <div class="col-lg-6">
                <div class="card modern-card">
                    <div class="card-header modern-header">
                        <div class="header-content">
                            <div class="header-title">
                                <i class="fas fa-shopping-cart header-icon"></i>
                                <h3 class="card-title">Purchase Information</h3>
                            </div>
                        </div>
                    </div>
                    <div class="card-body modern-card-body">
                        <div class="form-group">
                            <label for="purchase_date" class="form-label">Purchase Date <span class="text-danger">*</span></label>
                            <input type="date" 
                                   name="purchase_date" 
                                   id="purchase_date" 
                                   class="form-control modern-input" 
                                   value="{{ old('purchase_date', date('Y-m-d')) }}" 
                                   required 
                                   tabindex="1">
                        </div>
                        
                        <div class="form-group">
                            <label for="invoice_no" class="form-label">Invoice Number</label>
                            <input type="text" 
                                   name="invoice_no" 
                                   id="invoice_no" 
                                   class="form-control modern-input" 
                                   value="{{ old('invoice_no') }}" 
                                   tabindex="2"
                                   placeholder="Enter invoice number">
                        </div>

                        @if($godowns->isNotEmpty())
                            @php
                                $defaultGodownId = optional($godowns->firstWhere('is_default', true))->id;
                            @endphp
                            <div class="form-group">
                                <label for="godown_id" class="form-label">Godown</label>
                                <select name="godown_id" id="godown_id" class="form-control modern-select" tabindex="3">
                                    <option value="">Default Godown</option>
                                    @foreach($godowns as $godown)
                                        <option value="{{ $godown->id }}" {{ old('godown_id', $defaultGodownId) == $godown->id ? 'selected' : '' }}>
                                            {{ $godown->name }}{{ $godown->location ? ' - ' . $godown->location : '' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            
            <!-- Supplier Information -->
            <div class="col-lg-6">
                <div class="card modern-card">
                    <div class="card-header modern-header">
                        <div class="header-content">
                            <div class="header-title">
                                <i class="fas fa-building header-icon"></i>
                                <h3 class="card-title">Supplier Information</h3>
                            </div>
                        </div>
                    </div>
                    <div class="card-body modern-card-body">
                        <div class="form-group">
                            <label for="company_id" class="form-label">Supplier <span class="text-danger">*</span></label>
                            <select name="company_id" 
                                    id="company_id" 
                                    class="form-control modern-select select2-searchable" 
                                    required 
                                    tabindex="3">
                                <option value="">Select Supplier</option>
                                @foreach($companies as $company)
                                    <option value="{{ $company->id }}" {{ old('company_id') == $company->id ? 'selected' : '' }}>
                                        {{ $company->name }}
                                    </option>
                                @endforeach
                            </select>
                            <button type="button" class="btn btn-sm modern-btn modern-btn-outline mt-2" id="open-purchase-company-modal">
                                <i class="fas fa-plus"></i> Add Company
                            </button>
                        </div>
                        
                        <div class="form-group">
                            <label for="notes" class="form-label">Notes</label>
                            <textarea name="notes" 
                                      id="notes" 
                                      class="form-control modern-input" 
                                      rows="3" 
                                      tabindex="4"
                                      maxlength="500"
                                      placeholder="Enter purchase notes (optional)">{{ old('notes') }}</textarea>
                            <div class="char-counter">
                                <span class="char-count">0</span>/500 characters
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Total Amount</label>
                            <div class="total-amount-display">
                                <span class="currency-symbol">৳</span>
                                <span id="total_amount_display">0.00</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Purchase Items Section - Mobile Optimized -->
        <div class="card modern-card">
            <div class="card-header modern-header">
                <div class="header-content">
                    <div class="header-title">
                        <i class="fas fa-boxes header-icon"></i>
                        <h3 class="card-title">Purchase Items</h3>
                    </div>
                    <div class="section-actions">
                        <button type="button" class="btn modern-btn modern-btn-secondary btn-sm" data-toggle="modal" data-target="#addProductModal">
                            <i class="fas fa-plus-circle"></i> <span class="btn-text">New Product</span>
                        </button>
                    </div>
                </div>
            </div>
            <div class="card-body modern-card-body">
                <!-- Mobile View Product Cards -->
                <div class="mobile-products-container" id="mobile-products">
                    <div class="mobile-product-card product-row-mobile" data-row-index="0">
                        <div class="card-header-mobile">
                            <span class="product-number">Item #1</span>
                            <button type="button" class="btn-remove-mobile remove-row-mobile">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Product <span class="text-danger">*</span></label>
                            <select class="form-control modern-select product-select-mobile select2-searchable" 
                                    name="items[0][product_id]" required>
                                <option value="">Select Product</option>
                                @foreach($products as $product)
                                    <option value="{{ $product->id }}" 
                                        data-box-pcs="{{ $product->category->box_pcs ?? 0 }}" 
                                        data-pieces-feet="{{ $product->category->pieces_feet ?? 0 }}"
                                        data-purchase-price="{{ $product->purchase_price }}">
                                        {{ $product->name }} - {{ $product->company->name }}
                                    </option>
                                @endforeach
                                <option value="new" class="add-new-option">
                                    <i class="fas fa-plus"></i> Add New Product
                                </option>
                            </select>
                        </div>
                        
                        <div class="quantity-controls">
                            <div class="form-group">
                                <label class="form-label">Unit Type</label>
                                <select class="form-control modern-select unit-type-mobile">
                                    <option value="quantity">Direct Quantity</option>
                                    <option value="box_pieces">Box/Pieces</option>
                                </select>
                            </div>
                            
                            <div class="quantity-inputs">
                                <div class="form-group quantity-group">
                                    <label class="form-label">Quantity <span class="text-danger">*</span></label>
                                    <input type="number" name="items[0][quantity]" 
                                           class="form-control modern-input quantity-field-mobile" 
                                           min="0.01" step="0.01" required>
                                    <input type="hidden" class="box-pcs-mobile" value="0">
                                    <input type="hidden" class="pieces-feet-mobile" value="0">
                                </div>
                                
                                <div class="box-pieces-group" style="display: none;">
                                    <div class="row">
                                        <div class="col-6">
                                            <div class="form-group">
                                                <label class="form-label">Boxes</label>
                                                <input type="number" class="form-control modern-input box-field-mobile" min="0" readonly>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="form-group">
                                                <label class="form-label">Pieces</label>
                                                <input type="number" class="form-control modern-input pieces-field-mobile" min="0" readonly>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Purchase Price <span class="text-danger">*</span></label>
                            <div class="input-wrapper">
                                <div class="input-prefix">৳</div>
                                <input type="number" name="items[0][purchase_price]" 
                                       class="form-control modern-input purchase-price-mobile" 
                                       min="0.01" step="0.01" required>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Total</label>
                            <div class="total-display-mobile">
                                <span class="currency-symbol">৳</span>
                                <span class="total-price-mobile">0.00</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Desktop View Table -->
                <div class="desktop-products-container">
                    <div class="table-responsive modern-table-responsive">
                        <table class="table modern-table" id="items-table">
                            <thead class="modern-thead">
                                <tr>
                                    <th width="25%">
                                        <div class="th-content">
                                            <i class="fas fa-box"></i>
                                            <span>Product</span>
                                        </div>
                                    </th>
                                    <th width="12%">
                                        <div class="th-content">
                                            <i class="fas fa-cog"></i>
                                            <span>Unit Type</span>
                                        </div>
                                    </th>
                                    <th width="8%" class="mobile-hide">
                                        <div class="th-content">
                                            <i class="fas fa-cube"></i>
                                            <span>Boxes</span>
                                        </div>
                                    </th>
                                    <th width="8%" class="mobile-hide">
                                        <div class="th-content">
                                            <i class="fas fa-puzzle-piece"></i>
                                            <span>Pieces</span>
                                        </div>
                                    </th>
                                    <th width="10%">
                                        <div class="th-content">
                                            <i class="fas fa-sort-numeric-up"></i>
                                            <span>Quantity</span>
                                        </div>
                                    </th>
                                    <th width="12%">
                                        <div class="th-content">
                                            <i class="fas fa-dollar-sign"></i>
                                            <span>Price</span>
                                        </div>
                                    </th>
                                    <th width="10%">
                                        <div class="th-content">
                                            <i class="fas fa-calculator"></i>
                                            <span>Total</span>
                                        </div>
                                    </th>
                                    <th width="15%">
                                        <div class="th-content">
                                            <i class="fas fa-cogs"></i>
                                            <span>Actions</span>
                                        </div>
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="modern-tbody">
                                <tr id="item-row-1" class="item-row">
                                    <td>
                                        <select class="form-control select2 product-select modern-select-sm" 
                                                name="items[0][product_id]" required>
                                            <option value="">Select Product</option>
                                            @foreach($products as $product)
                                                <option value="{{ $product->id }}" 
                                                    data-box-pcs="{{ $product->category->box_pcs ?? 0 }}" 
                                                    data-pieces-feet="{{ $product->category->pieces_feet ?? 0 }}"
                                                    data-purchase-price="{{ $product->purchase_price }}">
                                                    {{ $product->name }} - {{ $product->company->name }}
                                                </option>
                                            @endforeach
                                            <option value="new" class="add-new-option">
                                                <i class="fas fa-plus"></i> Add New Product
                                            </option>
                                        </select>
                                    </td>
                                    <td>
                                        <select class="form-control modern-select-sm unit-type-select">
                                            <option value="quantity">Quantity</option>
                                            <option value="box_pieces">Box/Pieces</option>
                                        </select>
                                    </td>
                                    <td class="mobile-hide">
                                        <input type="number" class="form-control modern-input-sm box-field" 
                                               min="0" readonly>
                                    </td>
                                    <td class="mobile-hide">
                                        <input type="number" class="form-control modern-input-sm pieces-field" 
                                               min="0" readonly>
                                    </td>
                                    <td>
                                        <input type="number" name="items[0][quantity]" 
                                               class="form-control modern-input-sm quantity-field" 
                                               min="0.01" step="0.01" required>
                                    </td>
                                    <td>
                                        <input type="number" name="items[0][purchase_price]" 
                                               class="form-control modern-input-sm purchase-price-field" 
                                               min="0.01" step="0.01" required>
                                    </td>
                                    <td>
                                        <input type="text" class="form-control modern-input-sm total-price" 
                                               readonly>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <button type="button" class="btn action-btn action-btn-add add-row-btn" title="Add Item">
                                                <i class="fas fa-plus"></i>
                                            </button>
                                            <button type="button" class="btn action-btn action-btn-delete remove-row" disabled title="Delete Item">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                            <tfoot class="modern-tfoot">
                                <tr class="total-row">
                                    <td colspan="6" class="text-right">
                                        <strong class="grand-total-label">
                                            <i class="fas fa-calculator"></i> Grand Total:
                                        </strong>
                                    </td>
                                    <td>
                                        <input type="text" id="grand-total" 
                                               class="form-control modern-input-sm grand-total-field" 
                                               readonly value="0.00">
                                    </td>
                                    <td>
                                        <div class="footer-actions">
                                            <button type="button" class="btn action-btn action-btn-add" id="add-row-bottom" title="Add New Item">
                                                <i class="fas fa-plus"></i> <span class="btn-text">Add Item</span>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
                
                <!-- Add Item Button (Mobile) -->
                <div class="add-product-container">
                    <button type="button" class="btn modern-btn modern-btn-success btn-block-mobile" id="add-row-mobile">
                        <i class="fas fa-plus"></i> Add Item
                    </button>
                </div>
            </div>
        </div>

        <!-- Additional Costs Section -->
        <div class="modern-card additional-costs-card">
            <div class="modern-card-header collapsible-header" data-toggle="collapse" data-target="#additionalCostsSection">
                <h5 class="modern-card-title">
                    <i class="fas fa-plus-circle"></i>
                    Additional Costs (Labour, Transport, etc.)
                    <i class="fas fa-chevron-down toggle-icon float-right"></i>
                </h5>
            </div>
            <div class="collapse" id="additionalCostsSection">
                <div class="modern-card-body">
                    <div class="row">
                        <div class="col-md-4 col-12 mb-3">
                            <label class="modern-label">
                                <i class="fas fa-hard-hat"></i> Labour Cost
                            </label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">৳</span>
                                </div>
                                <input type="number" name="labour_cost" id="labour_cost"
                                       class="form-control modern-input additional-cost-field"
                                       value="{{ old('labour_cost', 0) }}"
                                       min="0" step="0.01" placeholder="0.00">
                            </div>
                            <small class="form-text text-muted">Loading/unloading charges</small>
                        </div>
                        <div class="col-md-4 col-12 mb-3">
                            <label class="modern-label">
                                <i class="fas fa-truck"></i> Transportation Cost
                            </label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">৳</span>
                                </div>
                                <input type="number" name="transportation_cost" id="transportation_cost"
                                       class="form-control modern-input additional-cost-field"
                                       value="{{ old('transportation_cost', 0) }}"
                                       min="0" step="0.01" placeholder="0.00">
                            </div>
                            <small class="form-text text-muted">Freight/delivery charges</small>
                        </div>
                        <div class="col-md-4 col-12 mb-3">
                            <label class="modern-label">
                                <i class="fas fa-receipt"></i> Other Cost
                            </label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">৳</span>
                                </div>
                                <input type="number" name="other_cost" id="other_cost"
                                       class="form-control modern-input additional-cost-field"
                                       value="{{ old('other_cost', 0) }}"
                                       min="0" step="0.01" placeholder="0.00">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 col-12 mb-3">
                            <label class="modern-label">Other Cost Description</label>
                            <input type="text" name="other_cost_description"
                                   class="form-control modern-input"
                                   value="{{ old('other_cost_description') }}"
                                   placeholder="Describe other costs (if any)">
                        </div>
                        <div class="col-md-6 col-12 mb-3">
                            <label class="modern-label">
                                <i class="fas fa-balance-scale"></i> Cost Distribution Method
                            </label>
                            <select name="cost_distribution_method" id="cost_distribution_method" class="form-control modern-select">
                                <option value="per_value" {{ old('cost_distribution_method', 'per_value') == 'per_value' ? 'selected' : '' }}>
                                    By Value (proportional to item price)
                                </option>
                                <option value="per_quantity" {{ old('cost_distribution_method') == 'per_quantity' ? 'selected' : '' }}>
                                    By Quantity (equal per unit)
                                </option>
                                <option value="equal" {{ old('cost_distribution_method') == 'equal' ? 'selected' : '' }}>
                                    Equal (same for each item line)
                                </option>
                            </select>
                            <small class="form-text text-muted">How to distribute additional costs across products</small>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="update_product_prices"
                                       name="update_product_prices" value="1" {{ old('update_product_prices') ? 'checked' : '' }}>
                                <label class="custom-control-label" for="update_product_prices">
                                    <strong>Update Product Purchase Prices</strong>
                                    <small class="d-block text-muted">
                                        Include additional costs in product's base purchase price for future reference
                                    </small>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Summary -->
                    <div class="additional-costs-summary mt-3 p-3 bg-light rounded">
                        <div class="row">
                            <div class="col-md-3 col-6">
                                <small class="text-muted">Products Total</small>
                                <div class="h6 mb-0" id="products-subtotal">৳0.00</div>
                            </div>
                            <div class="col-md-3 col-6">
                                <small class="text-muted">Additional Costs</small>
                                <div class="h6 mb-0 text-warning" id="additional-costs-total">৳0.00</div>
                            </div>
                            <div class="col-md-3 col-6">
                                <small class="text-muted">Grand Total</small>
                                <div class="h5 mb-0 text-success" id="final-grand-total">৳0.00</div>
                            </div>
                            <div class="col-md-3 col-6">
                                <small class="text-muted">Avg. Cost/Unit Increase</small>
                                <div class="h6 mb-0 text-info" id="avg-cost-increase">৳0.00</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Mobile Fixed Bottom Actions -->
        <div class="mobile-bottom-actions">
            <div class="mobile-button-group">
                <button type="button" id="preview-btn-mobile" class="btn modern-btn modern-btn-primary btn-lg mobile-submit-btn">
                    <i class="fas fa-eye"></i> Preview & Create
                </button>
                <button type="button" id="quick-save-btn-mobile" class="btn modern-btn modern-btn-success btn-lg mobile-submit-btn">
                    <i class="fas fa-save"></i> Quick Save
                </button>
            </div>
        </div>
        
        <!-- Desktop Form Actions -->
        <div class="form-actions desktop-only">
            <div class="button-group">
                <button type="button" id="preview-btn" class="btn modern-btn modern-btn-primary btn-lg">
                    <i class="fas fa-eye"></i> Preview & Create Purchase
                </button>
                <button type="button" id="quick-save-btn" class="btn modern-btn modern-btn-success btn-lg">
                    <i class="fas fa-save"></i> Quick Save
                </button>
                <a href="{{ route('purchases.index') }}" class="btn modern-btn modern-btn-outline btn-lg">
                    <i class="fas fa-times"></i> Cancel
                </a>
            </div>
        </div>
    </form>

    <!-- Add Company Modal -->
    <div class="modal fade" id="addPurchaseCompanyModal" tabindex="-1" role="dialog" aria-labelledby="addPurchaseCompanyModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-md modal-dialog-centered" role="document">
            <div class="modal-content modern-modal">
                <div class="modal-header modern-modal-header">
                    <h5 class="modal-title" id="addPurchaseCompanyModalLabel">
                        <i class="fas fa-building"></i> Add Company
                    </h5>
                    <button type="button" class="close modern-close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="add-purchase-company-form">
                    @csrf
                    <div class="modal-body modern-modal-body">
                        <div class="form-group modern-form-group">
                            <label for="purchase_company_name_modal" class="modern-label required">Company Name</label>
                            <input type="text" id="purchase_company_name_modal" name="name" class="form-control modern-input" required>
                        </div>
                        <div class="form-group modern-form-group">
                            <label for="purchase_company_type_modal" class="modern-label">Company Type</label>
                            <select id="purchase_company_type_modal" name="type" class="form-control modern-select">
                                <option value="supplier" selected>Supplier</option>
                                <option value="brand">Brand</option>
                                <option value="both">Brand & Supplier</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer modern-modal-footer">
                        <button type="button" class="btn modern-btn modern-btn-outline" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn modern-btn modern-btn-primary" id="save-purchase-company-btn">
                            <i class="fas fa-save"></i> Save Company
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Add New Product Modal -->
    <div class="modal fade modern-modal" id="addProductModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-mobile" role="document">
            <div class="modal-content modern-modal-content">
                <div class="modal-header modern-modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-plus-circle"></i>
                        Add New Product
                    </h5>
                    <button type="button" class="close modern-close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body modern-modal-body">
                    <form id="newProductForm">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="new_name" class="form-label required">Product Name</label>
                                <div class="purchase-product-name-wrapper">
                                    <input type="text" class="form-control modern-input"
                                           id="new_name" name="name" required>
                                    <span class="purchase-name-check-icon"></span>
                                </div>
                                <div id="purchase-modal-duplicate-warning" class="purchase-duplicate-warning" style="display: none;"></div>
                            </div>
                            <div class="form-group">
                                <label for="new_company_id" class="form-label required">Company</label>
                                <div class="input-group">
                                    <select class="form-control modern-select select2-searchable" id="new_company_id" name="company_id" required>
                                        <option value="">Select Company</option>
                                        @foreach($companies as $company)
                                            <option value="{{ $company->id }}">{{ $company->name }}</option>
                                        @endforeach
                                    </select>
                                    <div class="input-group-append">
                                        <button type="button" class="btn btn-success quick-add-btn" id="purchase-quick-add-company-btn" title="Quick Add Company">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    </div>
                                </div>
                                <div id="purchase-quick-company-form" class="quick-add-form" style="display:none;">
                                    <div class="input-group mt-2">
                                        <input type="text" id="purchase_quick_company_name" class="form-control" placeholder="Company name">
                                        <div class="input-group-append">
                                            <button type="button" class="btn btn-primary" id="purchase-save-quick-company">
                                                <i class="fas fa-check"></i>
                                            </button>
                                            <button type="button" class="btn btn-secondary" id="purchase-cancel-quick-company">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="mt-2">
                                        <select id="purchase_quick_company_type" class="form-control">
                                            <option value="brand" selected>Brand</option>
                                            <option value="supplier">Supplier</option>
                                            <option value="both">Brand & Supplier</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="new_category_id" class="form-label required">Category</label>
                                <div class="input-group">
                                    <select class="form-control modern-select select2-searchable" id="new_category_id" name="category_id" required>
                                        <option value="">Select Category</option>
                                        @foreach($categories as $category)
                                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                                        @endforeach
                                    </select>
                                    <div class="input-group-append">
                                        <button type="button" class="btn btn-success quick-add-btn" id="purchase-quick-add-category-btn" title="Quick Add Category">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    </div>
                                </div>
                                <div id="purchase-quick-category-form" class="quick-add-form" style="display:none;">
                                    <div class="input-group mt-2">
                                        <input type="text" id="purchase_quick_category_name" class="form-control" placeholder="Category name">
                                        <div class="input-group-append">
                                            <button type="button" class="btn btn-primary" id="purchase-save-quick-category">
                                                <i class="fas fa-check"></i>
                                            </button>
                                            <button type="button" class="btn btn-secondary" id="purchase-cancel-quick-category">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="new_opening_stock" class="form-label">Opening Stock</label>
                                <input type="number" step="0.01" min="0" 
                                       class="form-control modern-input" 
                                       id="new_opening_stock" name="opening_stock" value="0">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="new_purchase_price" class="form-label required">Purchase Price</label>
                                <div class="input-wrapper">
                                    <div class="input-prefix">৳</div>
                                    <input type="number" step="0.01" min="0" 
                                           class="form-control modern-input" 
                                           id="new_purchase_price" name="purchase_price" required>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="new_sale_price" class="form-label required">Sale Price</label>
                                <div class="input-wrapper">
                                    <div class="input-prefix">৳</div>
                                    <input type="number" step="0.01" min="0" 
                                           class="form-control modern-input" 
                                           id="new_sale_price" name="sale_price" required>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="new_description" class="form-label">Description</label>
                            <textarea class="form-control modern-input" 
                                      id="new_description" name="description" rows="3"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer modern-modal-footer">
                    <button type="button" class="btn modern-btn modern-btn-outline" data-dismiss="modal">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                    <button type="button" class="btn modern-btn modern-btn-success" id="saveNewProduct">
                        <i class="fas fa-save"></i> Save Product
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Review Confirmation Modal -->
    <div class="modal fade modern-modal" id="reviewModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-mobile" role="document">
            <div class="modal-content modern-modal-content">
                <div class="modal-header modern-modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-check-circle"></i>
                        Review Purchase Order
                    </h5>
                    <button type="button" class="close modern-close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body modern-modal-body">
                    <div class="review-section">
                        <div class="review-card">
                            <div class="review-header">
                                <h6><i class="fas fa-info-circle"></i> Purchase Information</h6>
                            </div>
                            <div class="review-content">
                                <div class="review-item">
                                    <span class="review-label">Purchase Date:</span>
                                    <span class="review-value" id="review-date">-</span>
                                </div>
                                <div class="review-item">
                                    <span class="review-label">Invoice Number:</span>
                                    <span class="review-value" id="review-invoice">-</span>
                                </div>
                                <div class="review-item">
                                    <span class="review-label">Supplier:</span>
                                    <span class="review-value" id="review-supplier">-</span>
                                </div>
                                <div class="review-item">
                                    <span class="review-label">Notes:</span>
                                    <span class="review-value" id="review-notes">-</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="review-card">
                            <div class="review-header">
                                <h6><i class="fas fa-boxes"></i> Purchase Summary</h6>
                            </div>
                            <div class="review-content">
                                <div class="review-item">
                                    <span class="review-label">Total Items:</span>
                                    <span class="review-value" id="review-total-items">-</span>
                                </div>
                                <div class="review-item">
                                    <span class="review-label">Total Amount:</span>
                                    <span class="review-value" id="review-total-amount">-</span>
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
                        <i class="fas fa-save"></i> Save Purchase
                    </button>
                    <button type="button" class="btn modern-btn modern-btn-danger" data-dismiss="modal">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>
@stop

@section('additional_css')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.min.css">

    <style>
        /* Form styling */
        .form-label {
            font-size: 13px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 8px;
        }

        .form-actions {
            text-align: center;
            margin-top: 32px;
            padding: 24px;
            background: #f8fafc;
            border-radius: 12px;
        }

        .form-actions .btn {
            margin: 0 8px;
        }

        /* Mobile button group styling */
        .mobile-button-group {
            display: flex;
            gap: 8px;
            width: 100%;
        }

        .mobile-submit-btn {
            flex: 1;
            font-size: 14px;
            padding: 12px 8px;
        }

        /* Desktop button group */
        .button-group {
            display: flex;
            gap: 12px;
            justify-content: center;
            align-items: center;
            flex-wrap: wrap;
        }

        .button-group .btn {
            margin: 0;
        }

        /* Enhanced Select2 styling to match modern design */
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

        .select2-search--dropdown .select2-search__field:focus {
            border-color: #6366f1 !important;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1) !important;
            outline: none !important;
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

        .select2-results__option[aria-selected="true"] {
            background: #f3f4f6 !important;
            color: #6366f1 !important;
            font-weight: 600 !important;
        }

        .input-group .select2-container {
            width: 100% !important;
            flex: 1 1 auto;
        }

        .input-group .select2-selection--single {
            border-top-right-radius: 0 !important;
            border-bottom-right-radius: 0 !important;
        }

        .quick-add-btn {
            border-radius: 0 8px 8px 0;
            padding: 6px 10px;
        }

        .quick-add-form {
            background: #f0f9ff;
            border-radius: 6px;
            padding: 8px;
            border: 1px dashed #3b82f6;
            margin-top: 6px;
        }

        .add-new-option {
            color: #10b981 !important;
            font-weight: 600 !important;
        }

        /* Total Amount Display */
        .total-amount-display {
            background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
            border: 2px solid #10b981;
            border-radius: 12px;
            padding: 16px 20px;
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 20px;
            font-weight: 700;
            color: #059669;
            transition: all 0.3s ease;
        }

        .total-amount-display:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(16, 185, 129, 0.3);
        }

        .currency-symbol {
            font-size: 22px;
            color: #059669;
        }

        /* Mobile Total Display */
        .total-display-mobile {
            background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
            border: 2px solid #10b981;
            border-radius: 8px;
            padding: 12px 16px;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 16px;
            font-weight: 700;
            color: #059669;
        }

        /* Input with prefix */
        .input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }

        .input-prefix {
            position: absolute;
            left: 15px;
            z-index: 2;
            color: #6b7280;
            font-weight: 600;
            pointer-events: none;
        }

        .input-wrapper .modern-input {
            padding-left: 35px;
        }

        /* Quantity controls */
        .quantity-controls {
            margin-top: 16px;
        }

        .quantity-group {
            margin-bottom: 0;
        }

        .box-pieces-group {
            margin-top: 12px;
        }

        .add-product-container {
            text-align: center;
            margin-top: 20px;
        }

        /* Character counter */
        .char-counter {
            font-size: 13px;
            color: #6b7280;
            text-align: right;
            margin-top: 6px;
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

        /* Desktop specific styles - FIXED */
        @media (min-width: 769px) {
            /* Hide mobile elements on desktop */
            .mobile-products-container {
                display: none !important;
            }

            .mobile-bottom-actions {
                display: none !important;
            }

            /* Show desktop elements */
            .desktop-products-container {
                display: block !important;
            }

            .desktop-only {
                display: block !important;
            }

            .btn-block-mobile {
                width: auto;
            }

            .mobile-button-group {
                flex-direction: row;
            }
        }

        /* Mobile Responsive - FIXED */
        @media (max-width: 768px) {
            /* Hide desktop elements on mobile */
            .desktop-products-container {
                display: none !important;
            }

            .desktop-only {
                display: none !important;
            }

            /* Show mobile elements */
            .mobile-products-container {
                display: block !important;
            }

            .mobile-bottom-actions {
                display: block !important;
                position: fixed;
                bottom: 0;
                left: 0;
                right: 0;
                background: white;
                padding: 16px;
                box-shadow: 0 -4px 20px rgba(0, 0, 0, 0.1);
                z-index: 1000;
            }

            /* Adjust body padding for fixed bottom */
            body {
                padding-bottom: 80px;
            }

            /* Mobile button styles */
            .btn-block-mobile {
                width: 100%;
            }

            .btn-text {
                display: none;
            }

            .mobile-button-group {
                flex-direction: column;
            }
            
            .mobile-submit-btn {
                width: 100%;
                margin-bottom: 8px;
            }
            
            .mobile-submit-btn:last-child {
                margin-bottom: 0;
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

            .select2-search--dropdown .select2-search__field {
                font-size: 16px !important;
                padding: 14px 15px !important;
                height: 48px !important;
            }

            .select2-results__option {
                padding: 14px 15px !important;
                font-size: 16px !important;
            }
        }

        /* Items Container */
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

        /* Modern Table Footer */
        .modern-tfoot {
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%) !important;
        }

        .modern-tfoot td {
            padding: 18px 14px;
            border: none !important;
            font-weight: 600;
            background: transparent !important;
        }

        .total-row {
            border-top: 3px solid #e5e7eb !important;
        }

        .grand-total-label {
            font-size: 16px;
            color: #374151;
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 10px;
        }

        .grand-total-field {
            font-weight: 700;
            font-size: 16px;
            color: #059669;
            background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%) !important;
            border: 2px solid #10b981 !important;
            border-radius: 8px;
        }

        /* Additional Costs Section */
        .additional-costs-card {
            margin-top: 20px;
            border: 2px dashed #e5e7eb;
            transition: all 0.3s ease;
        }

        .additional-costs-card.has-costs {
            border-color: #f59e0b;
            background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%);
        }

        .additional-costs-card .collapsible-header {
            cursor: pointer;
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
        }

        .additional-costs-card .collapsible-header:hover {
            background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%);
        }

        .additional-costs-card .toggle-icon {
            transition: transform 0.3s ease;
        }

        .additional-costs-card .modern-card-title {
            font-size: 14px;
            color: #64748b;
        }

        .additional-costs-card.has-costs .modern-card-title {
            color: #d97706;
        }

        .additional-costs-summary {
            border: 1px solid #e2e8f0;
        }

        .additional-cost-field {
            border: 2px solid #e5e7eb;
            transition: all 0.3s ease;
        }

        .additional-cost-field:focus {
            border-color: #f59e0b;
            box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.1);
        }

        /* Enhanced Action Buttons */
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

        .action-btn-add {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3);
        }

        .action-btn-add:hover {
            background: linear-gradient(135deg, #059669 0%, #047857 100%);
            color: white;
            box-shadow: 0 6px 20px rgba(16, 185, 129, 0.4);
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

        .action-btn-delete:disabled {
            background: #9ca3af;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        .action-btn-delete:disabled:hover {
            background: #9ca3af;
            transform: none;
            box-shadow: none;
        }

        /* Footer Actions */
        .footer-actions {
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .footer-actions .action-btn {
            padding: 12px 20px;
            font-size: 14px;
            font-weight: 600;
        }

        /* Loading state */
        .btn:disabled {
            opacity: 0.7;
            cursor: not-allowed;
        }

        /* Animation for adding/removing rows */
        .product-row,
        .mobile-product-card {
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

        /* Error state */
        .is-invalid {
            border-color: #ef4444 !important;
        }

        .is-invalid:focus {
            box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1) !important;
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

        /* Mobile Product Card */
        .mobile-product-card {
            background: #f8fafc;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 16px;
            border: 2px solid #e5e7eb;
            position: relative;
        }

        .card-header-mobile {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 16px;
        }

        .product-number {
            font-weight: 600;
            color: #6366f1;
            font-size: 16px;
        }

        .btn-remove-mobile {
            background: #ef4444;
            color: white;
            border: none;
            border-radius: 50%;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .btn-remove-mobile:hover {
            background: #dc2626;
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
        }

        .modern-modal-header .close {
            color: white;
            opacity: 0.8;
            font-size: 24px;
        }

        .modern-modal-header .close:hover {
            opacity: 1;
        }

        /* Section actions */
        .section-actions {
            display: flex;
            gap: 10px;
        }

        /* Review modal styles */
        .review-section {
            display: grid;
            gap: 24px;
        }

        .review-card {
            background: #f8fafc;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            overflow: hidden;
        }

        .review-header {
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
            color: white;
            padding: 16px 20px;
        }

        .review-header h6 {
            margin: 0;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .review-content {
            padding: 20px;
        }

        .review-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid #e5e7eb;
        }

        .review-item:last-child {
            border-bottom: none;
        }

        .review-label {
            font-weight: 500;
            color: #374151;
        }

        .review-value {
            color: #6b7280;
            font-weight: 400;
        }

        /* Small select for table */
        .modern-select-sm + .select2-container .select2-selection--single {
            height: 44px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
        }

        .modern-select-sm + .select2-container .select2-selection__rendered {
            line-height: 40px;
            padding-left: 14px;
            font-size: 13px;
        }

        .modern-select-sm + .select2-container .select2-selection__arrow {
            height: 40px;
            right: 14px;
        }

        .modern-input-sm {
            font-size: 13px;
            padding: 8px 12px;
            height: 44px;
        }

        /* Required field indicator */
        .required::after {
            content: " *";
            color: #ef4444;
        }

        /* Form row for modal */
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }

        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group.full-width {
            grid-column: 1 / -1;
        }
        @media (max-width: 768px) {
    .btn-text {
        display: none;
    }
    
    .header-actions-group {
        flex-direction: column;
        width: 100%;
        gap: 8px;
    }
    
    .header-actions-group .btn {
        width: 100%;
        justify-content: center;
        min-height: 48px;
    }
}

/* Loading state for buttons */
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

/* Purchase Product Name Duplicate Check Styles */
.purchase-product-name-wrapper {
    position: relative;
}

.purchase-product-name-wrapper .purchase-name-check-icon {
    position: absolute;
    right: 12px;
    top: 50%;
    transform: translateY(-50%);
    display: none;
}

.purchase-product-name-wrapper.checking .purchase-name-check-icon {
    display: block;
}

.purchase-product-name-wrapper.checking .purchase-name-check-icon::after {
    content: '';
    display: inline-block;
    width: 14px;
    height: 14px;
    border: 2px solid #e5e7eb;
    border-top-color: #3b82f6;
    border-radius: 50%;
    animation: spin 0.8s linear infinite;
}

.purchase-product-name-wrapper.valid .purchase-name-check-icon {
    display: block;
    color: #10b981;
}

.purchase-product-name-wrapper.valid .purchase-name-check-icon::after {
    content: '\f00c';
    font-family: 'Font Awesome 5 Free';
    font-weight: 900;
}

.purchase-product-name-wrapper.duplicate .purchase-name-check-icon {
    display: block;
    color: #f59e0b;
}

.purchase-product-name-wrapper.duplicate .purchase-name-check-icon::after {
    content: '\f071';
    font-family: 'Font Awesome 5 Free';
    font-weight: 900;
}

.purchase-duplicate-warning {
    background: linear-gradient(135deg, rgba(245, 158, 11, 0.1) 0%, rgba(217, 119, 6, 0.1) 100%);
    border: 1px solid rgba(245, 158, 11, 0.3);
    border-radius: 6px;
    padding: 10px 12px;
    margin-top: 8px;
    animation: purchase-slide-down 0.3s ease;
}

@keyframes purchase-slide-down {
    from {
        opacity: 0;
        transform: translateY(-8px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.purchase-duplicate-warning .warning-title {
    display: flex;
    align-items: center;
    gap: 6px;
    color: #b45309;
    font-weight: 600;
    font-size: 12px;
    margin-bottom: 6px;
}

.purchase-duplicate-warning .warning-text {
    color: #92400e;
    font-size: 11px;
    margin-bottom: 8px;
}

.purchase-duplicate-warning .duplicate-product-item {
    background: white;
    border-radius: 4px;
    padding: 6px 10px;
    margin-bottom: 4px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 11px;
}

.purchase-duplicate-warning .duplicate-product-item:last-child {
    margin-bottom: 0;
}

.purchase-duplicate-warning .product-name {
    font-weight: 600;
    color: #374151;
}

.purchase-duplicate-warning .product-meta {
    color: #6b7280;
    font-size: 10px;
}

.purchase-duplicate-warning .btn-view-existing {
    background: #f59e0b;
    color: white;
    border: none;
    padding: 3px 8px;
    border-radius: 4px;
    font-size: 10px;
    cursor: pointer;
    text-decoration: none;
}

.purchase-duplicate-warning .btn-view-existing:hover {
    background: #d97706;
    color: white;
}

    </style>
@stop

@section('additional_js')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"></script>

    <script>
        $(document).ready(function() {
            // Configure toastr
            toastr.options = {
                "closeButton": true,
                "newestOnTop": true,
                "progressBar": true,
                "positionClass": "toast-top-right",
                "timeOut": "3000"
            };
            $.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});

            // Purchase modal duplicate check variables
            var purchaseDuplicateCheckTimeout = null;
            var purchaseModalHasDuplicate = false;

            // Initialize duplicate check for purchase product modal
            function initPurchaseDuplicateCheck() {
                var nameInput = $('#new_name');
                var nameWrapper = nameInput.closest('.purchase-product-name-wrapper');

                nameInput.on('input', function() {
                    clearTimeout(purchaseDuplicateCheckTimeout);
                    var name = $(this).val().trim();

                    // Remove previous states
                    nameWrapper.removeClass('checking valid duplicate');
                    $('#purchase-modal-duplicate-warning').hide().empty();

                    if (name.length < 2) {
                        purchaseModalHasDuplicate = false;
                        return;
                    }

                    // Show checking state
                    nameWrapper.addClass('checking');

                    // Debounce the check
                    purchaseDuplicateCheckTimeout = setTimeout(function() {
                        checkPurchaseDuplicateName(name);
                    }, 500);
                });

                // Also check on blur
                nameInput.on('blur', function() {
                    clearTimeout(purchaseDuplicateCheckTimeout);
                    var name = $(this).val().trim();
                    if (name.length >= 2) {
                        checkPurchaseDuplicateName(name);
                    }
                });
            }

            function checkPurchaseDuplicateName(name) {
                var nameWrapper = $('#new_name').closest('.purchase-product-name-wrapper');

                $.ajax({
                    url: '{{ route("products.check-duplicate") }}',
                    method: 'GET',
                    data: { name: name },
                    success: function(response) {
                        nameWrapper.removeClass('checking');

                        if (response.exists) {
                            purchaseModalHasDuplicate = true;
                            nameWrapper.addClass('duplicate');
                            showPurchaseDuplicateWarning(response.products);
                        } else {
                            purchaseModalHasDuplicate = false;
                            nameWrapper.addClass('valid');
                            $('#purchase-modal-duplicate-warning').hide().empty();
                        }
                    },
                    error: function() {
                        nameWrapper.removeClass('checking');
                        purchaseModalHasDuplicate = false;
                    }
                });
            }

            function showPurchaseDuplicateWarning(products) {
                var warningDiv = $('#purchase-modal-duplicate-warning');
                var html = '<div class="warning-title"><i class="fas fa-exclamation-triangle"></i> Product already exists!</div>';
                html += '<div class="warning-text">A product with this name already exists. Please use a different name.</div>';

                products.forEach(function(product) {
                    html += '<div class="duplicate-product-item">';
                    html += '<div>';
                    html += '<div class="product-name">' + product.name + '</div>';
                    html += '<div class="product-meta">' + product.company + ' | ' + product.category + ' | Stock: ' + product.current_stock + '</div>';
                    html += '</div>';
                    html += '<a href="/products/' + product.id + '" target="_blank" class="btn-view-existing"><i class="fas fa-external-link-alt"></i> View</a>';
                    html += '</div>';
                });

                warningDiv.html(html).show();
            }

            // Initialize duplicate check when purchase product modal opens
            $('#addProductModal').on('shown.bs.modal', function() {
                initPurchaseDuplicateCheck();
                // Reset duplicate state
                purchaseModalHasDuplicate = false;
                $('#new_name').closest('.purchase-product-name-wrapper').removeClass('checking valid duplicate');
                $('#purchase-modal-duplicate-warning').hide().empty();
            });

            // Reset duplicate state when modal is hidden
            $('#addProductModal').on('hidden.bs.modal', function() {
                purchaseModalHasDuplicate = false;
                $('#new_name').closest('.purchase-product-name-wrapper').removeClass('checking valid duplicate');
                $('#purchase-modal-duplicate-warning').hide().empty();
            });

            // Initialize Select2 with search
            initializeSelect2();
            
            // Better mobile detection
            function isMobileView() {
                return window.innerWidth <= 768;
            }
            
            // Purchase-specific functionality
            const PurchaseCreate = {
                init: function() {
                    this.initItemManagement();
                    this.initFormActions();
                    this.initCharacterCounter();
                    this.initMobileOptimizations();
                    
                    // Focus on first field
                    $('#purchase_date').focus();
                    
                    console.log('Purchase Create initialized with mobile optimizations');
                },

                // Mobile-specific optimizations
                initMobileOptimizations: function() {
                    // Enhanced table scrolling for mobile
                    this.initMobileTableScroll();
                    
                    // Mobile-friendly modal handling
                    this.initMobileModals();
                    
                    // Touch-friendly interactions
                    this.initTouchOptimizations();
                },

                // Enhanced table scrolling for mobile
                initMobileTableScroll: function() {
                    const tableContainer = $('.table-responsive');
                    let isScrolling = false;
                    
                    tableContainer.on('touchstart', function() {
                        isScrolling = true;
                    }).on('touchend', function() {
                        setTimeout(() => { isScrolling = false; }, 100);
                    });
                    
                    // Prevent accidental form submission while scrolling table
                    tableContainer.find('input, select').on('focus', function() {
                        if (isScrolling) {
                            $(this).blur();
                        }
                    });
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
                    $('.action-btn, .modern-btn').on('touchstart', function() {
                        $(this).addClass('touching');
                    }).on('touchend touchcancel', function() {
                        $(this).removeClass('touching');
                    });
                    
                    // Prevent double-tap zoom on buttons
                    $('.action-btn, .modern-btn').on('touchend', function(e) {
                        e.preventDefault();
                        $(this).click();
                    });
                },

                // Item management functionality (enhanced for mobile)
                initItemManagement: function() {
                    let rowCount = 0;
                    
                    // Add new row function
                    const addNewRow = () => {
                        rowCount++;
                        const isMobile = isMobileView();
                        
                        if (isMobile) {
                            this.addMobileProductCard(rowCount);
                        } else {
                            this.addDesktopProductRow(rowCount);
                        }
                        
                        this.updateDeleteButtons();
                        this.updateGrandTotal();
                        toastr.success('New item added');
                    };
                    
                    // Add new row - both buttons
                    $(document).on('click', '.add-row-btn, #add-row-bottom, #add-row-mobile', addNewRow);
                    
                    // Remove row (Desktop)
                    $(document).on('click', '.remove-row', function() {
                        if ($('.item-row').length > 1) {
                            $(this).closest('tr').remove();
                            PurchaseCreate.updateDeleteButtons();
                            PurchaseCreate.updateGrandTotal();
                            toastr.info('Item removed');
                        } else {
                            toastr.warning('At least one item is required');
                        }
                    });
                    
                    // Remove row (Mobile)
                    $(document).on('click', '.remove-row-mobile', function() {
                        if ($('.mobile-product-card').length > 1) {
                            $(this).closest('.mobile-product-card').fadeOut(300, function() {
                                $(this).remove();
                                PurchaseCreate.updateMobileProductNumbers();
                                PurchaseCreate.updateGrandTotal();
                            });
                            toastr.info('Item removed');
                        } else {
                            toastr.warning('At least one item is required');
                        }
                    });
                    
                    // Product selection change (Desktop)
                    $(document).on('change', '.product-select', function() {
                        const selectedOption = $(this).find('option:selected');
                        const row = $(this).closest('tr');
                        
                        if ($(this).val() === 'new') {
                            PurchaseCreate.showNewProductModal(row);
                            $(this).val('').trigger('change');
                        } else if ($(this).val()) {
                            PurchaseCreate.handleProductSelection(row, selectedOption, false);
                        }
                    });
                    
                    // Product selection change (Mobile)
                    $(document).on('change', '.product-select-mobile', function() {
                        const selectedOption = $(this).find('option:selected');
                        const card = $(this).closest('.mobile-product-card');
                        
                        if ($(this).val() === 'new') {
                            PurchaseCreate.showNewProductModal(card);
                            $(this).val('').trigger('change');
                        } else if ($(this).val()) {
                            PurchaseCreate.handleProductSelection(card, selectedOption, true);
                        }
                    });
                    
                    // Unit type change (Desktop)
                    $(document).on('change', '.unit-type-select', function() {
                        const row = $(this).closest('tr');
                        PurchaseCreate.handleUnitTypeChange(row, $(this).val(), false);
                    });
                    
                    // Unit type change (Mobile)
                    $(document).on('change', '.unit-type-mobile', function() {
                        const card = $(this).closest('.mobile-product-card');
                        PurchaseCreate.handleUnitTypeChange(card, $(this).val(), true);
                    });
                    
                    // Calculate totals on input changes (Desktop)
                    $(document).on('input', '.box-field, .pieces-field', function() {
                        const row = $(this).closest('tr');
                        if (row.find('.unit-type-select').val() === 'box_pieces') {
                            PurchaseCreate.calculateQuantityFromBoxPieces(row, false);
                            PurchaseCreate.calculateRowTotal(row, false);
                            PurchaseCreate.updateGrandTotal();
                        }
                    });
                    
                    $(document).on('input', '.quantity-field', function() {
                        const row = $(this).closest('tr');
                        if (row.find('.unit-type-select').val() === 'quantity') {
                            PurchaseCreate.calculateBoxPiecesFromQuantity(row, false);
                        }
                        PurchaseCreate.calculateRowTotal(row, false);
                        PurchaseCreate.updateGrandTotal();
                    });
                    
                    $(document).on('input', '.purchase-price-field', function() {
                        const row = $(this).closest('tr');
                        PurchaseCreate.calculateRowTotal(row, false);
                        PurchaseCreate.updateGrandTotal();
                    });
                    
                    // Calculate totals on input changes (Mobile)
                    $(document).on('input', '.box-field-mobile, .pieces-field-mobile', function() {
                        const card = $(this).closest('.mobile-product-card');
                        if (card.find('.unit-type-mobile').val() === 'box_pieces') {
                            PurchaseCreate.calculateQuantityFromBoxPieces(card, true);
                            PurchaseCreate.calculateRowTotal(card, true);
                            PurchaseCreate.updateGrandTotal();
                        }
                    });
                    
                    $(document).on('input', '.quantity-field-mobile', function() {
                        const card = $(this).closest('.mobile-product-card');
                        if (card.find('.unit-type-mobile').val() === 'quantity') {
                            PurchaseCreate.calculateBoxPiecesFromQuantity(card, true);
                        }
                        PurchaseCreate.calculateRowTotal(card, true);
                        PurchaseCreate.updateGrandTotal();
                    });
                    
                    $(document).on('input', '.purchase-price-mobile', function() {
                        const card = $(this).closest('.mobile-product-card');
                        PurchaseCreate.calculateRowTotal(card, true);
                        PurchaseCreate.updateGrandTotal();
                    });

                    // Additional costs field handlers
                    $(document).on('input', '.additional-cost-field', function() {
                        PurchaseCreate.updateGrandTotal();
                    });

                    // Toggle additional costs section icon
                    $('.collapsible-header').on('click', function() {
                        $(this).find('.toggle-icon').toggleClass('fa-chevron-down fa-chevron-up');
                    });

                    // Initialize first row
                    this.updateDeleteButtons();
                    $('.unit-type-select, .unit-type-mobile').trigger('change');
                },

                // Add mobile product card
                addMobileProductCard: function(index) {
                    const currentCount = $('.mobile-product-card').length;
                    const newIndex = currentCount;
                    
                    const newCard = $('.mobile-product-card:first').clone();
                    newCard.attr('data-row-index', newIndex);
                    newCard.find('.product-number').text('Item #' + (currentCount + 1));
                    
                    // Update form field names
                    newCard.find('select[name^="items"]').attr('name', `items[${newIndex}][product_id]`);
                    newCard.find('input[name^="items"]').each(function() {
                        const name = $(this).attr('name');
                        if (name) {
                            const fieldName = name.match(/\[([^\]]+)\]$/)[1];
                            $(this).attr('name', `items[${newIndex}][${fieldName}]`);
                        }
                    });
                    
                    // Clear all input values
                    newCard.find('input').val('');
                    newCard.find('select').val('');
                    newCard.find('.total-price-mobile').text('0.00');
                    newCard.find('.box-pieces-group').hide();
                    newCard.find('.unit-type-mobile').val('quantity');
                    
                    // Remove any existing Select2 containers
                    newCard.find('.select2-container').remove();
                    newCard.find('select').removeClass('select2-hidden-accessible');

                    $('#mobile-products').append(newCard);
                    
                    // Initialize Select2 for the new card
                    newCard.find('.select2-searchable').select2({
                        placeholder: "Search and select product...",
                        allowClear: true,
                        width: '100%',
                        dropdownParent: newCard
                    });
                },

                // Add desktop product row
                addDesktopProductRow: function(index) {
                    const newRow = this.createNewDesktopRow(index);
                    $('#items-table tbody').append(newRow);
                    
                    // Initialize Select2 for new row
                    $(`#item-row-${index} .select2`).select2({
                        width: '100%',
                        placeholder: 'Select Product',
                        allowClear: true
                    });
                },

                // Create new desktop row HTML
                createNewDesktopRow: function(index) {
                    return `
                        <tr id="item-row-${index}" class="item-row">
                            <td>
                                <select class="form-control select2 product-select modern-select-sm" 
                                        name="items[${index}][product_id]" required>
                                    <option value="">Select Product</option>
                                    @foreach($products as $product)
                                        <option value="{{ $product->id }}" 
                                            data-box-pcs="{{ $product->category->box_pcs ?? 0 }}" 
                                            data-pieces-feet="{{ $product->category->pieces_feet ?? 0 }}"
                                            data-purchase-price="{{ $product->purchase_price }}">
                                            {{ $product->name }} - {{ $product->company->name }}
                                        </option>
                                    @endforeach
                                    <option value="new" class="add-new-option">
                                        <i class="fas fa-plus"></i> Add New Product
                                    </option>
                                </select>
                            </td>
                            <td>
                                <select class="form-control modern-select-sm unit-type-select">
                                    <option value="quantity">Quantity</option>
                                    <option value="box_pieces">Box/Pieces</option>
                                </select>
                            </td>
                            <td class="mobile-hide">
                                <input type="number" class="form-control modern-input-sm box-field" 
                                       min="0" readonly>
                            </td>
                            <td class="mobile-hide">
                                <input type="number" class="form-control modern-input-sm pieces-field" 
                                       min="0" readonly>
                            </td>
                            <td>
                                <input type="number" name="items[${index}][quantity]" 
                                       class="form-control modern-input-sm quantity-field" 
                                       min="0.01" step="0.01" required>
                            </td>
                            <td>
                                <input type="number" name="items[${index}][purchase_price]" 
                                       class="form-control modern-input-sm purchase-price-field" 
                                       min="0.01" step="0.01" required>
                            </td>
                            <td>
                                <input type="text" class="form-control modern-input-sm total-price" 
                                       readonly>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <button type="button" class="btn action-btn action-btn-add add-row-btn" title="Add Item">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                    <button type="button" class="btn action-btn action-btn-delete remove-row" title="Delete Item">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    `;
                },

                // Update mobile product numbers
                updateMobileProductNumbers: function() {
                    $('.mobile-product-card').each(function(index) {
                        $(this).find('.product-number').text('Item #' + (index + 1));
                    });
                },

                // Handle product selection
                handleProductSelection: function(container, selectedOption, isMobile) {
                    const boxPcs = selectedOption.data('box-pcs') || 0;
                    const piecesFeet = selectedOption.data('pieces-feet') || 0;
                    const purchasePrice = selectedOption.data('purchase-price') || 0;
                    
                    container.data('box-pcs', boxPcs);
                    container.data('pieces-feet', piecesFeet);
                    
                    if (isMobile) {
                        container.find('.purchase-price-mobile').val(purchasePrice);
                        container.find('.quantity-field-mobile, .box-field-mobile, .pieces-field-mobile').val('');
                        container.find('.total-price-mobile').text('0.00');
                        container.find('.unit-type-mobile').val('quantity').trigger('change');
                    } else {
                        container.find('.purchase-price-field').val(purchasePrice);
                        container.find('.quantity-field, .box-field, .pieces-field, .total-price').val('');
                        container.find('.unit-type-select').val('quantity').trigger('change');
                    }
                },

                // Handle unit type change
                handleUnitTypeChange: function(container, unitType, isMobile) {
                    if (isMobile) {
                        if (unitType === 'quantity') {
                            container.find('.box-pieces-group').hide();
                            container.find('.quantity-group').show();
                            container.find('.box-field-mobile, .pieces-field-mobile').prop('readonly', true);
                            container.find('.quantity-field-mobile').prop('readonly', false);
                        } else {
                            container.find('.box-pieces-group').show();
                            container.find('.box-field-mobile, .pieces-field-mobile').prop('readonly', false);
                            container.find('.quantity-field-mobile').prop('readonly', true);
                        }
                    } else {
                        if (unitType === 'quantity') {
                            container.find('.quantity-field').prop('readonly', false).removeClass('readonly-field');
                            container.find('.box-field, .pieces-field').prop('readonly', true).addClass('readonly-field');
                        } else {
                            container.find('.quantity-field').prop('readonly', true).addClass('readonly-field');
                            container.find('.box-field, .pieces-field').prop('readonly', false).removeClass('readonly-field');
                        }
                    }
                },

                // Calculate quantity from boxes and pieces
                calculateQuantityFromBoxPieces: function(container, isMobile) {
                    const boxes = parseInt(container.find(isMobile ? '.box-field-mobile' : '.box-field').val()) || 0;
                    const pieces = parseInt(container.find(isMobile ? '.pieces-field-mobile' : '.pieces-field').val()) || 0;
                    const boxPcs = parseFloat(container.data('box-pcs')) || 0;
                    const piecesFeet = parseFloat(container.data('pieces-feet')) || 0;
                    
                    if (boxPcs > 0 && piecesFeet > 0) {
                        const totalPieces = (boxes * boxPcs) + pieces;
                        const quantity = (totalPieces * piecesFeet).toFixed(2);
                        container.find(isMobile ? '.quantity-field-mobile' : '.quantity-field').val(quantity);
                    }
                },

                // Calculate boxes and pieces from quantity
                calculateBoxPiecesFromQuantity: function(container, isMobile) {
                    const quantity = parseFloat(container.find(isMobile ? '.quantity-field-mobile' : '.quantity-field').val()) || 0;
                    const boxPcs = parseFloat(container.data('box-pcs')) || 0;
                    const piecesFeet = parseFloat(container.data('pieces-feet')) || 0;
                    
                    if (boxPcs > 0 && piecesFeet > 0) {
                        const totalPieces = Math.round(quantity / piecesFeet);
                        const boxes = Math.floor(totalPieces / boxPcs);
                        const pieces = totalPieces - (boxes * boxPcs);
                        
                        container.find(isMobile ? '.box-field-mobile' : '.box-field').val(boxes);
                        container.find(isMobile ? '.pieces-field-mobile' : '.pieces-field').val(pieces);
                    }
                },

                // Calculate row total
                calculateRowTotal: function(container, isMobile) {
                    const quantity = parseFloat(container.find(isMobile ? '.quantity-field-mobile' : '.quantity-field').val()) || 0;
                    const purchasePrice = parseFloat(container.find(isMobile ? '.purchase-price-mobile' : '.purchase-price-field').val()) || 0;
                    const total = quantity * purchasePrice;
                    
                    if (isMobile) {
                        container.find('.total-price-mobile').text(total.toFixed(2));
                    } else {
                        container.find('.total-price').val(total.toFixed(2));
                    }
                },

                // Update grand total
                updateGrandTotal: function() {
                    let productsTotal = 0;
                    let totalQuantity = 0;

                    // Add desktop totals
                    $('.total-price').each(function() {
                        productsTotal += parseFloat($(this).val()) || 0;
                    });
                    $('#items-table tbody tr').each(function() {
                        totalQuantity += parseFloat($(this).find('.quantity-field').val()) || 0;
                    });

                    // Add mobile totals
                    $('.total-price-mobile').each(function() {
                        productsTotal += parseFloat($(this).text()) || 0;
                    });
                    $('.mobile-product-card').each(function() {
                        totalQuantity += parseFloat($(this).find('.quantity-mobile').val()) || 0;
                    });

                    // Calculate additional costs
                    const labourCost = parseFloat($('#labour_cost').val()) || 0;
                    const transportationCost = parseFloat($('#transportation_cost').val()) || 0;
                    const otherCost = parseFloat($('#other_cost').val()) || 0;
                    const additionalCostsTotal = labourCost + transportationCost + otherCost;

                    // Calculate final grand total
                    const grandTotal = productsTotal + additionalCostsTotal;

                    // Calculate average cost increase per unit
                    const avgCostIncrease = totalQuantity > 0 ? additionalCostsTotal / totalQuantity : 0;

                    // Update displays
                    $('#grand-total').val(productsTotal.toFixed(2));
                    $('#total_amount_display').text(productsTotal.toFixed(2));
                    $('#products-subtotal').text('৳' + productsTotal.toFixed(2));
                    $('#additional-costs-total').text('৳' + additionalCostsTotal.toFixed(2));
                    $('#final-grand-total').text('৳' + grandTotal.toFixed(2));
                    $('#avg-cost-increase').text('৳' + avgCostIncrease.toFixed(2));

                    // Highlight additional costs section if there are costs
                    if (additionalCostsTotal > 0) {
                        $('.additional-costs-card').addClass('has-costs');
                    } else {
                        $('.additional-costs-card').removeClass('has-costs');
                    }
                },

                // Update delete buttons state
                updateDeleteButtons: function() {
                    const desktopRowCount = $('#items-table tbody tr').length;
                    const mobileRowCount = $('.mobile-product-card').length;
                    
                    $('.remove-row').prop('disabled', desktopRowCount <= 1);
                    $('.remove-row-mobile').prop('disabled', mobileRowCount <= 1);
                },

                // Show new product modal
                showNewProductModal: function(container) {
                    $('#newProductForm')[0].reset();
                    
                    const companyId = $('#company_id').val();
                    if (companyId) {
                        $('#new_company_id').val(companyId).trigger('change');
                    }
                    
                    window.currentProductContainer = container;
                    $('#addProductModal').modal('show');
                },

                // Form action handlers
                initFormActions: function() {
                    // Save new product
                    $('#saveNewProduct').on('click', function() {
                        // Check for duplicate before submitting
                        if (purchaseModalHasDuplicate) {
                            toastr.warning('A product with this name already exists. Please use a different name.');
                            $('#new_name').focus();
                            return false;
                        }

                        const formData = $('#newProductForm').serialize();
                        const button = $(this);

                        button.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');
                        
                        $.ajax({
                            url: '{{ route("purchases.createProduct") }}',
                            type: 'POST',
                            data: formData,
                            success: function(response) {
                                if (response.success) {
                                    PurchaseCreate.addNewProductToDropdowns(response.product);
                                    $('#addProductModal').modal('hide');
                                    toastr.success('Product added successfully');
                                }
                            },
                            error: function(xhr) {
                                const errors = xhr.responseJSON;
                                let errorMessage = 'Error creating product';
                                
                                if (errors && errors.message) {
                                    errorMessage = errors.message;
                                }
                                
                                toastr.error(errorMessage);
                            },
                            complete: function() {
                                button.prop('disabled', false).html('<i class="fas fa-save"></i> Save Product');
                            }
                        });
                    });

                    // Preview button (both mobile and desktop)
                    $('#preview-btn, #preview-btn-mobile').on('click', function() {
                        if (!PurchaseCreate.validateForm()) {
                            return;
                        }
                        PurchaseCreate.updateReviewSection();
                        $('#reviewModal').modal('show');
                    });

                    // Quick save button (both mobile and desktop)
                    $('#quick-save-btn, #quick-save-btn-mobile').on('click', function() {
                        if (!PurchaseCreate.validateForm()) {
                            return;
                        }
                        
                        const button = $(this);
                        button.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');
                        
                        // Disable fields from non-active view to prevent conflicts
                        const isMobile = isMobileView();
                        if (isMobile) {
                            $('.desktop-products-container input, .desktop-products-container select').prop('disabled', true);
                        } else {
                            $('.mobile-products-container input, .mobile-products-container select').prop('disabled', true);
                        }
                        
                        $('#purchase-form')[0].submit();
                    });

                    // Save button in modal
                    $('#save-btn').on('click', function() {
                        const button = $(this);
                        button.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');
                        
                        // Disable fields from non-active view to prevent conflicts
                        const isMobile = isMobileView();
                        if (isMobile) {
                            $('.desktop-products-container input, .desktop-products-container select').prop('disabled', true);
                        } else {
                            $('.mobile-products-container input, .mobile-products-container select').prop('disabled', true);
                        }
                        
                        $('#purchase-form')[0].submit();
                    });
                },

                // Add new product to all dropdowns
                addNewProductToDropdowns: function(product) {
                    const newOption = new Option(
                        `${product.name} - ${product.company.name}`, 
                        product.id, 
                        true, 
                        true
                    );
                    $(newOption).data('box-pcs', product.category.box_pcs || 0);
                    $(newOption).data('pieces-feet', product.category.pieces_feet || 0);
                    $(newOption).data('purchase-price', product.purchase_price);
                    
                    $('.product-select, .product-select-mobile').each(function() {
                        const addNewOption = $(this).find('option[value="new"]').detach();
                        $(this).append(newOption.cloneNode(true));
                        $(this).append(addNewOption);
                    });
                    
                    if (window.currentProductContainer) {
                        const container = window.currentProductContainer;
                        const isMobile = container.hasClass('mobile-product-card');
                        
                        container.find(isMobile ? '.product-select-mobile' : '.product-select').val(product.id).trigger('change');
                        container.find(isMobile ? '.purchase-price-mobile' : '.purchase-price-field').val(product.purchase_price);
                        this.calculateRowTotal(container, isMobile);
                        this.updateGrandTotal();
                    }
                },

                // Validate form
                validateForm: function() {
                    let valid = true;
                    const isMobile = isMobileView();
                    
                    // Check if at least one product has a quantity
                    let hasProducts = false;
                    
                    if (isMobile) {
                        $('.mobile-product-card:visible').each(function() {
                            const productSelect = $(this).find('.product-select-mobile');
                            const quantity = parseFloat($(this).find('.quantity-field-mobile').val());
                            
                            if (productSelect.val() && quantity > 0) {
                                hasProducts = true;
                                return false;
                            }
                        });
                    } else {
                        $('.item-row:visible').each(function() {
                            const productSelect = $(this).find('.product-select');
                            const quantity = parseFloat($(this).find('.quantity-field').val());
                            
                            if (productSelect.val() && quantity > 0) {
                                hasProducts = true;
                                return false;
                            }
                        });
                    }
                    
                    if (!hasProducts) {
                        toastr.warning('Please add at least one product with a quantity');
                        return false;
                    }
                    
                    // Check required fields
                    const containers = isMobile ? $('.mobile-product-card:visible') : $('.item-row:visible');
                    
                    containers.each(function() {
                        const productSelect = $(this).find(isMobile ? '.product-select-mobile' : '.product-select');
                        const quantityField = $(this).find(isMobile ? '.quantity-field-mobile' : '.quantity-field');
                        const priceField = $(this).find(isMobile ? '.purchase-price-mobile' : '.purchase-price-field');
                        
                        const quantity = parseFloat(quantityField.val());
                        const price = parseFloat(priceField.val());
                        
                        if (productSelect.val() && (isNaN(quantity) || quantity <= 0)) {
                            toastr.warning('Please enter a valid quantity for all products');
                            quantityField.addClass('is-invalid').focus();
                            valid = false;
                            return false;
                        }
                        
                        if (productSelect.val() && (isNaN(price) || price <= 0)) {
                            toastr.warning('Please enter a valid purchase price for all products');
                            priceField.addClass('is-invalid').focus();
                            valid = false;
                            return false;
                        }
                    });
                    
                    return valid;
                },

                // Update review modal
                updateReviewSection: function() {
                    $('#review-date').text($('#purchase_date').val() || '-');
                    $('#review-invoice').text($('#invoice_no').val() || 'Not provided');
                    $('#review-supplier').text($('#company_id option:selected').text() || '-');
                    $('#review-notes').text($('#notes').val() || 'No notes provided');
                    
                    const isMobile = isMobileView();
                    const totalItems = isMobile ? $('.mobile-product-card').length : $('.item-row').length;
                    const totalAmount = $('#grand-total').val();
                    
                    $('#review-total-items').text(totalItems + ' items');
                    $('#review-total-amount').text('৳' + totalAmount);
                },

                // Initialize character counter
                initCharacterCounter: function() {
                    $('#notes').on('input', function() {
                        const count = $(this).val().length;
                        $('.char-count').text(count);
                        
                        if (count > 450) {
                            $('.char-counter').addClass('text-warning');
                        } else if (count > 500) {
                            $('.char-counter').addClass('text-danger');
                        } else {
                            $('.char-counter').removeClass('text-warning text-danger');
                        }
                    });
                }
            };

            // Add Company modal handling
            $('#open-purchase-company-modal').on('click', function() {
                $('#addPurchaseCompanyModal').modal('show');
                setTimeout(() => $('#purchase_company_name_modal').trigger('focus'), 150);
            });

            $('#add-purchase-company-form').on('submit', function(e) {
                e.preventDefault();
                const $btn = $('#save-purchase-company-btn');
                const originalHtml = $btn.html();
                const name = $('#purchase_company_name_modal').val().trim();
                const type = $('#purchase_company_type_modal').val() || 'supplier';

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
                        $('#addPurchaseCompanyModal').modal('hide');
                        $('#purchase_company_name_modal').val('');
                        $('#purchase_company_type_modal').val('supplier');
                        if (response.was_existing && typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'info',
                                title: 'Already exists',
                                text: response.name + ' was already created. Selected it for you.',
                                timer: 2000,
                                showConfirmButton: false
                            });
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

            function cleanupSelect2($el) {
                if ($el.hasClass('select2-hidden-accessible')) {
                    $el.select2('destroy');
                }
                $el.siblings('.select2').remove();
                $el.next('.select2').remove();
            }

            // Ensure Select2 dropdowns render inside Add Product modal
            $('#addProductModal').on('shown.bs.modal', function() {
                cleanupSelect2($('#new_company_id'));
                cleanupSelect2($('#new_category_id'));

                $('#new_company_id').select2({
                    width: '100%',
                    dropdownParent: $('#addProductModal .modal-content'),
                    placeholder: 'Select Company',
                    allowClear: true
                });

                $('#new_category_id').select2({
                    width: '100%',
                    dropdownParent: $('#addProductModal .modal-content'),
                    placeholder: 'Select Category',
                    allowClear: true
                });
            });

            // Quick add company in Add Product modal
            $(document).on('click', '#purchase-quick-add-company-btn', function(e) {
                e.preventDefault();
                e.stopPropagation();
                $('#purchase-quick-company-form').slideToggle(200);
                setTimeout(() => $('#purchase_quick_company_name').trigger('focus'), 200);
            });

            $(document).on('click', '#purchase-cancel-quick-company', function(e) {
                e.preventDefault();
                $('#purchase-quick-company-form').slideUp(200);
                $('#purchase_quick_company_name').val('');
                $('#purchase_quick_company_type').val('brand');
            });

            $(document).on('click', '#purchase-save-quick-company', function(e) {
                e.preventDefault();
                const name = $('#purchase_quick_company_name').val().trim();
                const type = ($('#purchase_quick_company_type').val() || 'brand').trim();
                if (!name) {
                    alert('Please enter a company name');
                    return;
                }

                const btn = $(this);
                btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');

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
                        $('#new_company_id').append(newOption).trigger('change');

                        $('#purchase-quick-company-form').slideUp(200);
                        $('#purchase_quick_company_name').val('');
                        $('#purchase_quick_company_type').val('brand');

                        if (response.was_existing && typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'info',
                                title: 'Already exists',
                                text: response.name + ' was already created. Selected it for you.',
                                timer: 2000,
                                showConfirmButton: false
                            });
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
                        btn.prop('disabled', false).html('<i class="fas fa-check"></i>');
                    }
                });
            });

            // Quick add category in Add Product modal
            $(document).on('click', '#purchase-quick-add-category-btn', function(e) {
                e.preventDefault();
                e.stopPropagation();
                $('#purchase-quick-category-form').slideToggle(200);
                setTimeout(() => $('#purchase_quick_category_name').trigger('focus'), 200);
            });

            $(document).on('click', '#purchase-cancel-quick-category', function(e) {
                e.preventDefault();
                $('#purchase-quick-category-form').slideUp(200);
                $('#purchase_quick_category_name').val('');
            });

            $(document).on('click', '#purchase-save-quick-category', function(e) {
                e.preventDefault();
                const name = $('#purchase_quick_category_name').val().trim();
                if (!name) {
                    alert('Please enter a category name');
                    return;
                }

                const btn = $(this);
                btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');

                $.ajax({
                    url: '/categories',
                    method: 'POST',
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content'),
                        name: name
                    },
                    success: function(response) {
                        const newOption = new Option(response.name, response.id, true, true);
                        $('#new_category_id').append(newOption).trigger('change');

                        $('#purchase-quick-category-form').slideUp(200);
                        $('#purchase_quick_category_name').val('');

                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Category Created!',
                                text: response.name,
                                timer: 1500,
                                showConfirmButton: false
                            });
                        }
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
                        btn.prop('disabled', false).html('<i class="fas fa-check"></i>');
                    }
                });
            });

            // Initialize purchase create functionality
            PurchaseCreate.init();
            
            // Window resize handler to update view
            $(window).on('resize', function() {
                setTimeout(function() {
                    if (isMobileView()) {
                        $('.mobile-products-container').show();
                        $('.desktop-products-container').hide();
                    } else {
                        $('.mobile-products-container').hide();
                        $('.desktop-products-container').show();
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
            $('.select2-searchable').not('#new_company_id, #new_category_id').select2({
                placeholder: "Search and select...",
                allowClear: true,
                width: '100%',
                theme: 'default'
            });
            
        }
    </script>
@stop
