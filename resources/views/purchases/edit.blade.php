@extends('layouts.modern-admin')

@section('title', 'Edit Purchase')

@section('page_title', 'Edit Purchase Order')

@section('header_actions')
    <a href="{{ route('purchases.index') }}" class="btn modern-btn modern-btn-secondary">
        <i class="fas fa-arrow-left"></i> Back to Purchases
    </a>
    <a href="{{ route('purchases.show', $purchase) }}" class="btn modern-btn modern-btn-info">
        <i class="fas fa-eye"></i> View Purchase
    </a>
@stop

@section('page_content')
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

    <form action="{{ route('purchases.update', $purchase) }}" method="POST" id="purchaseForm">
        @csrf
        @method('PUT')
        
        <!-- Purchase Information Row -->
        <div class="row mb-4">
            <!-- Purchase Info Section -->
            <div class="col-md-6">
                <div class="card modern-card">
                    <div class="card-header modern-header purchase-header">
                        <h3 class="card-title">
                            <i class="fas fa-shopping-cart"></i> Purchase Information
                        </h3>
                    </div>
                    <div class="card-body modern-card-body">
                        <div class="form-group modern-form-group">
                            <label for="purchase_date" class="modern-label">
                                Purchase Date <span class="required">*</span>
                            </label>
                            <input type="date" name="purchase_date" id="purchase_date" 
                                   class="form-control modern-input @error('purchase_date') is-invalid @enderror" 
                                   value="{{ old('purchase_date', $purchase->purchase_date->format('Y-m-d')) }}" required>
                            @error('purchase_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="form-group modern-form-group">
                            <label for="invoice_no" class="modern-label">
                                Invoice Number
                            </label>
                            <input type="text" name="invoice_no" id="invoice_no" 
                                   class="form-control modern-input @error('invoice_no') is-invalid @enderror" 
                                   value="{{ old('invoice_no', $purchase->invoice_no) }}">
                            @error('invoice_no')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        @if($godowns->isNotEmpty())
                            @php
                                $defaultGodownId = optional($godowns->firstWhere('is_default', true))->id;
                            @endphp
                            <div class="form-group modern-form-group">
                                <label for="godown_id" class="modern-label">Godown</label>
                                <select name="godown_id" id="godown_id" class="form-control modern-select @error('godown_id') is-invalid @enderror">
                                    <option value="">Default Godown</option>
                                    @foreach($godowns as $godown)
                                        <option value="{{ $godown->id }}" {{ old('godown_id', $purchase->godown_id ?? $defaultGodownId) == $godown->id ? 'selected' : '' }}>
                                            {{ $godown->name }}{{ $godown->location ? ' - ' . $godown->location : '' }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('godown_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            
            <!-- Supplier Information Section -->
            <div class="col-md-6">
                <div class="card modern-card">
                    <div class="card-header modern-header supplier-header">
                        <h3 class="card-title">
                            <i class="fas fa-building"></i> Supplier Information
                        </h3>
                    </div>
                    <div class="card-body modern-card-body">
                        <div class="form-group modern-form-group">
                            <label for="company_id" class="modern-label">
                                Supplier <span class="required">*</span>
                            </label>
                            <select name="company_id" id="company_id" 
                                    class="form-control select2 modern-select @error('company_id') is-invalid @enderror" required>
                                <option value="">Select Supplier</option>
                                @foreach($companies as $company)
                                    <option value="{{ $company->id }}" {{ old('company_id', $purchase->company_id) == $company->id ? 'selected' : '' }}>
                                        {{ $company->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('company_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="form-group modern-form-group">
                            <label for="notes" class="modern-label">Notes</label>
                            <textarea name="notes" id="notes" 
                                      class="form-control modern-textarea @error('notes') is-invalid @enderror" 
                                      rows="3">{{ old('notes', $purchase->notes) }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Purchase Items Section -->
        <div class="card modern-card mb-4">
            <div class="card-header modern-header items-header">
                <h3 class="card-title">
                    <i class="fas fa-boxes"></i> Purchase Items
                </h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" id="add-row">
                        <i class="fas fa-plus"></i> Add Item
                    </button>
                </div>
            </div>
            <div class="card-body modern-card-body">
                <div class="table-container">
                    <div class="table-responsive modern-table-responsive">
                        <table class="table modern-table" id="items-table">
                            <thead class="modern-thead">
                                <tr>
                                    <th width="30%">
                                        <div class="th-content">
                                            <i class="fas fa-box"></i>
                                            <span>Product</span>
                                        </div>
                                    </th>
                                    <th width="15%">
                                        <div class="th-content">
                                            <i class="fas fa-sort-numeric-up"></i>
                                            <span>Quantity</span>
                                        </div>
                                    </th>
                                    <th width="20%">
                                        <div class="th-content">
                                            <i class="fas fa-dollar-sign"></i>
                                            <span>Purchase Price</span>
                                        </div>
                                    </th>
                                    <th width="20%">
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
                                @foreach($purchase->items as $index => $item)
                                    <tr id="item-row-{{ $index + 1 }}" class="item-row">
                                        <td>
                                            <select class="form-control select2 product-select modern-select-sm" 
                                                    name="items[{{ $index }}][product_id]" required>
                                                <option value="">Select Product</option>
                                                @foreach($products as $product)
                                                    <option value="{{ $product->id }}" 
                                                        data-price="{{ $product->purchase_price }}"
                                                        {{ $item->product_id == $product->id ? 'selected' : '' }}>
                                                        {{ $product->name }} - {{ $product->company->name }}
                                                    </option>
                                                @endforeach
                                                <option value="new" class="add-new-option">
                                                    <i class="fas fa-plus"></i> Add New Product
                                                </option>
                                            </select>
                                            <input type="hidden" name="items[{{ $index }}][id]" value="{{ $item->id }}">
                                        </td>
                                        <td>
                                            <input type="number" step="0.01" min="0.01" 
                                                   class="form-control modern-input-sm item-quantity" 
                                                   name="items[{{ $index }}][quantity]" 
                                                   required value="{{ $item->quantity }}">
                                        </td>
                                        <td>
                                            <input type="number" step="0.01" min="0.01" 
                                                   class="form-control modern-input-sm item-price" 
                                                   name="items[{{ $index }}][purchase_price]" 
                                                   required value="{{ $item->purchase_price }}">
                                        </td>
                                        <td>
                                            <input type="text" class="form-control modern-input-sm item-total" 
                                                   readonly value="{{ $item->total_price }}">
                                        </td>
                                        <td>
                                            <button type="button" class="btn action-btn action-btn-delete delete-row" 
                                                    {{ count($purchase->items) <= 1 ? 'disabled' : '' }}>
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="modern-tfoot">
                                <tr class="total-row">
                                    <td colspan="3" class="text-right">
                                        <strong class="grand-total-label">
                                            <i class="fas fa-calculator"></i> Grand Total:
                                        </strong>
                                    </td>
                                    <td>
                                        <input type="text" id="grand-total" 
                                               class="form-control modern-input-sm grand-total-field" 
                                               readonly value="{{ $purchase->total_amount }}">
                                    </td>
                                    <td>
                                        <span class="total-currency">৳</span>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Additional Costs Section -->
        <div class="modern-card additional-costs-card mt-4">
            <div class="modern-card-header collapsible-header" data-toggle="collapse" data-target="#additionalCostsSection">
                <h5 class="modern-card-title">
                    <i class="fas fa-plus-circle"></i>
                    Additional Costs (Labour, Transport, etc.)
                    <i class="fas fa-chevron-down toggle-icon float-right"></i>
                </h5>
            </div>
            <div class="collapse {{ ($purchase->labour_cost > 0 || $purchase->transportation_cost > 0 || $purchase->other_cost > 0) ? 'show' : '' }}" id="additionalCostsSection">
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
                                       value="{{ old('labour_cost', $purchase->labour_cost ?? 0) }}"
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
                                       value="{{ old('transportation_cost', $purchase->transportation_cost ?? 0) }}"
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
                                       value="{{ old('other_cost', $purchase->other_cost ?? 0) }}"
                                       min="0" step="0.01" placeholder="0.00">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 col-12 mb-3">
                            <label class="modern-label">Other Cost Description</label>
                            <input type="text" name="other_cost_description"
                                   class="form-control modern-input"
                                   value="{{ old('other_cost_description', $purchase->other_cost_description) }}"
                                   placeholder="Describe other costs (if any)">
                        </div>
                        <div class="col-md-6 col-12 mb-3">
                            <label class="modern-label">
                                <i class="fas fa-balance-scale"></i> Cost Distribution Method
                            </label>
                            <select name="cost_distribution_method" id="cost_distribution_method" class="form-control modern-select">
                                <option value="per_value" {{ old('cost_distribution_method', $purchase->cost_distribution_method ?? 'per_value') == 'per_value' ? 'selected' : '' }}>
                                    By Value (proportional to item price)
                                </option>
                                <option value="per_quantity" {{ old('cost_distribution_method', $purchase->cost_distribution_method) == 'per_quantity' ? 'selected' : '' }}>
                                    By Quantity (equal per unit)
                                </option>
                                <option value="equal" {{ old('cost_distribution_method', $purchase->cost_distribution_method) == 'equal' ? 'selected' : '' }}>
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
                                       name="update_product_prices" value="1" {{ old('update_product_prices', $purchase->update_product_prices) ? 'checked' : '' }}>
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
                                <div class="h6 mb-0" id="products-subtotal">৳{{ number_format($purchase->total_amount, 2) }}</div>
                            </div>
                            <div class="col-md-3 col-6">
                                <small class="text-muted">Additional Costs</small>
                                <div class="h6 mb-0 text-warning" id="additional-costs-total">৳{{ number_format(($purchase->labour_cost ?? 0) + ($purchase->transportation_cost ?? 0) + ($purchase->other_cost ?? 0), 2) }}</div>
                            </div>
                            <div class="col-md-3 col-6">
                                <small class="text-muted">Grand Total</small>
                                <div class="h5 mb-0 text-success" id="final-grand-total">৳{{ number_format($purchase->grand_total ?? $purchase->total_amount, 2) }}</div>
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

        <!-- Submit Buttons -->
        <div class="text-center mt-4 mb-4">
            <button type="submit" class="btn modern-btn modern-btn-primary btn-lg" id="submit-btn">
                <i class="fas fa-save"></i> Update Purchase
            </button>
            <a href="{{ route('purchases.index') }}" class="btn modern-btn modern-btn-secondary btn-lg ml-3">
                <i class="fas fa-times"></i> Cancel
            </a>
        </div>
    </form>

    <!-- Add New Product Modal -->
    <div class="modal fade" id="addProductModal" tabindex="-1" role="dialog" aria-labelledby="addProductModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content modern-modal">
                <div class="modal-header modern-modal-header">
                    <h5 class="modal-title" id="addProductModalLabel">
                        <i class="fas fa-plus-circle"></i> Add New Product
                    </h5>
                    <button type="button" class="close modern-close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body modern-modal-body">
                    <form id="newProductForm">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group modern-form-group">
                                    <label for="new_name" class="modern-label">
                                        Product Name <span class="required">*</span>
                                    </label>
                                    <input type="text" class="form-control modern-input" 
                                           id="new_name" name="name" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group modern-form-group">
                                    <label for="new_company_id" class="modern-label">
                                        Company <span class="required">*</span>
                                    </label>
                                    <select class="form-control modern-select" id="new_company_id" name="company_id" required>
                                        <option value="">Select Company</option>
                                        @foreach($companies as $company)
                                            <option value="{{ $company->id }}">{{ $company->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group modern-form-group">
                                    <label for="new_category_id" class="modern-label">
                                        Category <span class="required">*</span>
                                    </label>
                                    <select class="form-control modern-select" id="new_category_id" name="category_id" required>
                                        <option value="">Select Category</option>
                                        @foreach($categories as $category)
                                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group modern-form-group">
                                    <label for="new_opening_stock" class="modern-label">Opening Stock</label>
                                    <input type="number" step="0.01" min="0" 
                                           class="form-control modern-input" 
                                           id="new_opening_stock" name="opening_stock" value="0">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group modern-form-group">
                                    <label for="new_purchase_price" class="modern-label">
                                        Purchase Price <span class="required">*</span>
                                    </label>
                                    <input type="number" step="0.01" min="0" 
                                           class="form-control modern-input" 
                                           id="new_purchase_price" name="purchase_price" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group modern-form-group">
                                    <label for="new_sale_price" class="modern-label">
                                        Sale Price <span class="required">*</span>
                                    </label>
                                    <input type="number" step="0.01" min="0" 
                                           class="form-control modern-input" 
                                           id="new_sale_price" name="sale_price" required>
                                </div>
                            </div>
                        </div>
                        <div class="form-group modern-form-group">
                            <label for="new_description" class="modern-label">Description</label>
                            <textarea class="form-control modern-textarea" 
                                      id="new_description" name="description" rows="3"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer modern-modal-footer">
                    <button type="button" class="btn modern-btn modern-btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                    <button type="button" class="btn modern-btn modern-btn-primary" id="saveNewProduct">
                        <i class="fas fa-save"></i> Save Product
                    </button>
                </div>
            </div>
        </div>
    </div>
@stop

@section('additional_css')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.min.css">
    
    <style>
        /* Modern Form Enhancements */
        .modern-form-group {
            margin-bottom: 24px;
        }

        .modern-label {
            font-weight: 600;
            color: #374151;
            margin-bottom: 8px;
            font-size: 14px;
        }

        .modern-input, .modern-textarea, .modern-select {
            border: 2px solid #e5e7eb;
            border-radius: 10px;
            padding: 12px 16px;
            font-size: 14px;
            transition: all 0.2s ease;
            background: white;
            color: #374151;
        }

        .modern-input:focus, .modern-textarea:focus, .modern-select:focus {
            outline: none;
            border-color: #6366f1;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
            transform: translateY(-1px);
        }

        .modern-input-sm, .modern-select-sm {
            border: 1px solid #d1d5db;
            border-radius: 6px;
            padding: 8px 12px;
            font-size: 13px;
            transition: all 0.2s ease;
            background: white;
            color: #374151;
        }

        .modern-input-sm:focus, .modern-select-sm:focus {
            outline: none;
            border-color: #6366f1;
            box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.1);
        }

        /* Section-specific header colors */
        .purchase-header {
            background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
        }

        .supplier-header {
            background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);
        }

        .items-header {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
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
            padding: 16px 12px;
            font-weight: 600;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: white !important;
            background: transparent !important;
        }

        .th-content {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            color: white;
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
            padding: 12px;
            vertical-align: middle;
            border: none !important;
            font-size: 13px;
            color: #374151 !important;
            background: transparent !important;
        }

        /* Modern Table Footer */
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

        .grand-total-field {
            font-weight: 700;
            font-size: 16px;
            color: #059669;
            background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%) !important;
            border-color: #10b981 !important;
        }

        /* Additional Costs Section */
        .additional-costs-card {
            border: 2px dashed #e5e7eb;
            transition: all 0.3s ease;
            border-radius: 12px;
        }

        .additional-costs-card.has-costs {
            border-color: #f59e0b;
            background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%);
        }

        .additional-costs-card .collapsible-header {
            cursor: pointer;
            padding: 15px 20px;
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            border-radius: 10px 10px 0 0;
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
            margin: 0;
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

        .total-currency {
            font-size: 18px;
            font-weight: 700;
            color: #059669;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Action Buttons */
        .action-btn {
            padding: 6px 12px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 500;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            transition: all 0.2s ease;
            border: none;
            cursor: pointer;
        }

        .action-btn:hover {
            transform: translateY(-1px);
            text-decoration: none;
        }

        .action-btn-delete {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: white;
        }

        .action-btn-delete:disabled {
            background: #9ca3af;
            cursor: not-allowed;
            transform: none;
        }

        /* Modern Modal */
        .modern-modal {
            border: none;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        }

        .modern-modal-header {
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
            color: white;
            border-bottom: none;
            padding: 20px 24px;
        }

        .modern-modal-header .modal-title {
            font-weight: 600;
            font-size: 18px;
        }

        .modern-close {
            color: white;
            opacity: 0.8;
            font-size: 24px;
        }

        .modern-close:hover {
            color: white;
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

        /* Select2 Enhancements */
        .select2-container .select2-selection--single {
            height: 46px;
            border: 2px solid #e5e7eb;
            border-radius: 10px;
            background: white;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 42px;
            color: #374151;
            padding-left: 16px;
            font-size: 14px;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 42px;
            right: 16px;
        }

        .select2-container--default.select2-container--focus .select2-selection--single {
            border-color: #6366f1;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
        }

        .select2-dropdown {
            border: none;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
            border-radius: 10px;
            overflow: hidden;
        }

        .select2-results__option {
            padding: 12px 16px;
            font-size: 14px;
            transition: all 0.2s ease;
        }

        .select2-results__option--highlighted {
            background: linear-gradient(135deg, rgba(99, 102, 241, 0.1) 0%, rgba(139, 92, 246, 0.05) 100%);
            color: #6366f1;
        }

        .add-new-option {
            color: #10b981;
            font-weight: 600;
        }

        /* Form validation styles */
        .is-invalid {
            border-color: #ef4444 !important;
            box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1) !important;
        }

        .invalid-feedback {
            color: #ef4444;
            font-size: 12px;
            margin-top: 4px;
        }

        /* Required field indicator */
        .required {
            color: #ef4444;
            font-weight: 600;
        }

        /* Loading states */
        .loading {
            opacity: 0.7;
            pointer-events: none;
        }

        /* Card Tools */
        .card-tools .btn-tool {
            color: white;
            opacity: 0.8;
            border: none;
            background: transparent;
            padding: 8px 12px;
            border-radius: 6px;
            transition: all 0.2s ease;
            font-size: 14px;
        }

        .card-tools .btn-tool:hover {
            opacity: 1;
            background: rgba(255, 255, 255, 0.1);
            transform: translateY(-1px);
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

        /* Responsive Design */
        @media (max-width: 768px) {
            .modern-form-group {
                margin-bottom: 20px;
            }

            .modern-input, .modern-textarea, .modern-select {
                padding: 10px 14px;
                font-size: 16px;
            }

            .modern-tbody td {
                padding: 8px 6px;
                font-size: 12px;
            }

            .modern-thead th {
                padding: 12px 8px;
                font-size: 11px;
            }

            .action-btn {
                padding: 4px 8px;
                font-size: 11px;
            }

            .grand-total-label {
                font-size: 14px;
            }

            .grand-total-field {
                font-size: 14px;
            }

            .total-currency {
                font-size: 16px;
            }
        }
    </style>
@stop

@section('additional_js')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"></script>
    
    <script>
        $(document).ready(function() {
            // Configure Toastr
            toastr.options = {
                "closeButton": true,
                "debug": false,
                "newestOnTop": true,
                "progressBar": true,
                "positionClass": "toast-top-right",
                "preventDuplicates": false,
                "onclick": null,
                "showDuration": "300",
                "hideDuration": "1000",
                "timeOut": "5000",
                "extendedTimeOut": "1000",
                "showEasing": "swing",
                "hideEasing": "linear",
                "showMethod": "fadeIn",
                "hideMethod": "fadeOut"
            };
            
            // Initialize meta csrf-token
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            
            // Initialize Select2 with modern styling
            $('.select2').select2({
                width: '100%',
                placeholder: 'Select an option',
                allowClear: true
            });
            
            // Auto-hide alerts after 5 seconds
            setTimeout(function() {
                $('.modern-alert').addClass('alert-auto-hide');
            }, 5000);
            
            // Set initial row count
            let rowCount = {{ count($purchase->items) }};
            
            // Add new row
            $('#add-row').click(function() {
                rowCount++;
                const newRow = `
                    <tr id="item-row-${rowCount}" class="item-row">
                        <td>
                            <select class="form-control select2 product-select modern-select-sm" 
                                    name="items[${rowCount-1}][product_id]" required>
                                <option value="">Select Product</option>
                                @foreach($products as $product)
                                    <option value="{{ $product->id }}" data-price="{{ $product->purchase_price }}">
                                        {{ $product->name }} - {{ $product->company->name }}
                                    </option>
                                @endforeach
                                <option value="new" class="add-new-option">
                                    <i class="fas fa-plus"></i> Add New Product
                                </option>
                            </select>
                        </td>
                        <td>
                            <input type="number" step="0.01" min="0.01" 
                                   class="form-control modern-input-sm item-quantity" 
                                   name="items[${rowCount-1}][quantity]" required value="1">
                        </td>
                        <td>
                            <input type="number" step="0.01" min="0.01" 
                                   class="form-control modern-input-sm item-price" 
                                   name="items[${rowCount-1}][purchase_price]" required value="0.00">
                        </td>
                        <td>
                            <input type="text" class="form-control modern-input-sm item-total" 
                                   readonly value="0.00">
                        </td>
                        <td>
                            <button type="button" class="btn action-btn action-btn-delete delete-row">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                `;
                $('#items-table tbody').append(newRow);
                
                // Initialize Select2 for new row
                $(`#item-row-${rowCount} .select2`).select2({
                    width: '100%',
                    placeholder: 'Select Product',
                    allowClear: true
                });
                
                // Enable all delete buttons when we have more than one row
                if ($('#items-table tbody tr').length > 1) {
                    $('.delete-row').prop('disabled', false);
                }
                
                // Calculate totals
                calculateTotals();
                
                // Show success message
                toastr.success('New item row added');
            });
            
            // Delete row
            $(document).on('click', '.delete-row', function() {
                if ($('#items-table tbody tr').length > 1) {
                    $(this).closest('tr').remove();
                    
                    // Disable delete buttons if only one row remains
                    if ($('#items-table tbody tr').length === 1) {
                        $('.delete-row').prop('disabled', true);
                    }
                    
                    calculateTotals();
                    toastr.info('Item row removed');
                } else {
                    toastr.warning('At least one item is required');
                }
            });
            
            // Product selection change
            $(document).on('change', '.product-select', function() {
                const selectedOption = $(this).find('option:selected');
                const row = $(this).closest('tr');
                
                if ($(this).val() === 'new') {
                    // Reset modal form
                    $('#newProductForm')[0].reset();
                    
                    // Set company from purchase form if selected
                    const companyId = $('#company_id').val();
                    if (companyId) {
                        $('#new_company_id').val(companyId);
                    }
                    
                    // Store reference to the current row
                    window.currentProductRow = row;
                    
                    // Show modal
                    $('#addProductModal').modal('show');
                    
                    // Reset the select back to empty
                    $(this).val('').trigger('change');
                } else if ($(this).val()) {
                    const price = selectedOption.data('price');
                    row.find('.item-price').val(price);
                    calculateRowTotal(row);
                }
            });
            
            // Quantity or price change
            $(document).on('input', '.item-quantity, .item-price', function() {
                calculateRowTotal($(this).closest('tr'));
            });
            
            // Calculate row total
            function calculateRowTotal(row) {
                const quantity = parseFloat(row.find('.item-quantity').val()) || 0;
                const price = parseFloat(row.find('.item-price').val()) || 0;
                const total = quantity * price;
                
                row.find('.item-total').val(total.toFixed(2));
                calculateTotals();
            }
            
            // Calculate all totals
            function calculateTotals() {
                let productsTotal = 0;
                let totalQuantity = 0;

                $('.item-total').each(function() {
                    productsTotal += parseFloat($(this).val()) || 0;
                });

                $('.item-quantity').each(function() {
                    totalQuantity += parseFloat($(this).val()) || 0;
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
            }

            // Additional costs field handlers
            $(document).on('input', '.additional-cost-field', function() {
                calculateTotals();
            });

            // Toggle additional costs section icon
            $('.collapsible-header').on('click', function() {
                $(this).find('.toggle-icon').toggleClass('fa-chevron-down fa-chevron-up');
            });

            // Initialize additional costs calculation
            calculateTotals();
            
            // Save new product
            $('#saveNewProduct').click(function() {
                const formData = $('#newProductForm').serialize();
                const button = $(this);
                
                // Show loading state
                button.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');
                
                $.ajax({
                    url: '{{ route("purchases.createProduct") }}',
                    type: 'POST',
                    data: formData,
                    success: function(response) {
                        if (response.success) {
                            // Add new product to all dropdowns
                            const newOption = new Option(
                                `${response.product.name} - ${response.product.company.name}`, 
                                response.product.id, 
                                true, 
                                true
                            );
                            $(newOption).data('price', response.product.purchase_price);
                            
                            $('.product-select').each(function() {
                                // Add option before the "Add New" option
                                const addNewOption = $(this).find('option[value="new"]').detach();
                                $(this).append(newOption.cloneNode(true));
                                $(this).append(addNewOption);
                            });
                            
                            // Set values in the current row
                            if (window.currentProductRow) {
                                const row = window.currentProductRow;
                                row.find('.product-select').val(response.product.id).trigger('change');
                                row.find('.item-price').val(response.product.purchase_price);
                                calculateRowTotal(row);
                            }
                            
                            // Close modal and show success message
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
                        // Reset button
                        button.prop('disabled', false).html('<i class="fas fa-save"></i> Save Product');
                    }
                });
            });
            
            // Form submission
            $('#purchaseForm').submit(function(e) {
                if ($('#items-table tbody tr').length === 0) {
                    e.preventDefault();
                    toastr.error('Please add at least one product');
                    return false;
                }
                
                // Show loading state
                const submitBtn = $('#submit-btn');
                const originalText = submitBtn.html();
                submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Updating Purchase...');
                
                return true;
            });
            
            // Initialize row calculations
            $('#items-table tbody tr').each(function() {
                calculateRowTotal($(this));
            });
            
            // Calculate initial totals
            calculateTotals();
        });
    </script>
@stop
