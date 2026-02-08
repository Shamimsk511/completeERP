<div class="modal fade" id="newProductModal" tabindex="-1" role="dialog" aria-labelledby="newProductModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content modern-modal">
            <div class="modal-header modern-modal-header">
                <h5 class="modal-title" id="newProductModalLabel">
                    <i class="fas fa-plus-circle"></i> Create New Product
                </h5>
                <button type="button" class="close modern-close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <form id="new-product-form">
                @csrf
                <input type="hidden" name="idempotency_key" id="modal-idempotency-key" value="{{ (string) \Illuminate\Support\Str::uuid() }}">
                <div class="modal-body modern-modal-body">
                    <!-- Product Basic Information -->
                    <div class="form-section">
                        <h6 class="section-title">
                            <i class="fas fa-info-circle"></i> Basic Information
                        </h6>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group modern-form-group">
                                    <label for="product_name" class="modern-label">
                                        Product Name <span class="required">*</span>
                                    </label>
                                    <div class="product-name-input-wrapper">
                                        <input type="text"
                                               name="name"
                                               id="product_name"
                                               class="form-control modern-input"
                                               placeholder="Enter product name"
                                               required>
                                        <span class="name-check-icon"></span>
                                    </div>
                                    <div class="invalid-feedback"></div>
                                    <div id="modal-duplicate-warning" class="modal-duplicate-warning" style="display: none;"></div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group modern-form-group">
                                    <label for="product_description" class="modern-label">
                                        Description
                                    </label>
                                    <textarea name="description"
                                              id="product_description"
                                              class="form-control modern-textarea"
                                              rows="2"
                                              placeholder="Enter product description"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Company and Category -->
                    <div class="form-section">
                        <h6 class="section-title">
                            <i class="fas fa-tags"></i> Classification
                        </h6>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group modern-form-group">
                                    <label for="product_company" class="modern-label">
                                        Company <span class="required">*</span>
                                    </label>
                                    <div class="input-group">
                                        <select name="company_id"
                                                id="product_company"
                                                class="form-control modern-select product-modal-select2"
                                                required
                                                style="width: calc(100% - 46px);">
                                            <option value="">Select Company</option>
                                            @foreach($companies as $company)
                                                <option value="{{ $company->id }}">{{ $company->name }}</option>
                                            @endforeach
                                        </select>
                                        <div class="input-group-append">
                                            <button type="button" class="btn btn-success quick-add-btn" id="quick-add-company-btn" title="Quick Add Company">
                                                <i class="fas fa-plus"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="invalid-feedback"></div>
                                    <!-- Quick Add Company Form (Hidden by default) -->
                                    <div id="quick-company-form" class="quick-add-form" style="display:none;">
                                        <div class="input-group mt-2">
                                            <input type="text" id="quick_company_name" class="form-control" placeholder="Company name">
                                            <div class="input-group-append">
                                                <button type="button" class="btn btn-primary" id="save-quick-company">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                                <button type="button" class="btn btn-secondary" id="cancel-quick-company">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </div>
                                        </div>
                                        <div class="mt-2">
                                            <select id="quick_company_type" class="form-control">
                                                <option value="brand" selected>Brand</option>
                                                <option value="supplier">Supplier</option>
                                                <option value="both">Brand & Supplier</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group modern-form-group">
                                    <label for="product_category" class="modern-label">
                                        Category <span class="required">*</span>
                                    </label>
                                    <div class="input-group">
                                        <select name="category_id"
                                                id="product_category"
                                                class="form-control modern-select product-modal-select2"
                                                required
                                                style="width: calc(100% - 46px);">
                                            <option value="">Select Category</option>
                                            @foreach($categories as $category)
                                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                                            @endforeach
                                        </select>
                                        <div class="input-group-append">
                                            <button type="button" class="btn btn-success quick-add-btn" id="quick-add-category-btn" title="Quick Add Category">
                                                <i class="fas fa-plus"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="invalid-feedback"></div>
                                    <!-- Quick Add Category Form (Hidden by default) -->
                                    <div id="quick-category-form" class="quick-add-form" style="display:none;">
                                        <div class="input-group mt-2">
                                            <input type="text" id="quick_category_name" class="form-control" placeholder="Category name">
                                            <div class="input-group-append">
                                                <button type="button" class="btn btn-primary" id="save-quick-category">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                                <button type="button" class="btn btn-secondary" id="cancel-quick-category">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Stock Management Section -->
                    <div class="form-section">
                        <h6 class="section-title">
                            <i class="fas fa-warehouse"></i> Stock Management
                        </h6>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="toggle-section compact">
                                    <label for="modal_is_stock_managed" class="modern-label">Manage Stock</label>
                                    <div class="modern-toggle">
                                        <input type="checkbox"
                                               class="toggle-input"
                                               id="modal_is_stock_managed"
                                               name="is_stock_managed"
                                               value="1"
                                               checked>
                                        <label class="toggle-label" for="modal_is_stock_managed">
                                            <span class="toggle-slider"></span>
                                        </label>
                                        <span class="toggle-status text-success" id="modal-stock-status-text">On</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4" id="modal-stock-fields">
                                <div class="form-group modern-form-group mb-0">
                                    <label for="opening_stock" class="modern-label">Opening Stock</label>
                                    <input type="number"
                                           name="opening_stock"
                                           id="opening_stock"
                                           class="form-control modern-input"
                                           min="0"
                                           step="0.01"
                                           value="0">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group modern-form-group mb-0">
                                    <label class="modern-label">Current Stock</label>
                                    <input type="text" id="modal_current_stock" class="form-control modern-input" value="0" readonly>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Pricing -->
                    <div class="form-section">
                        <h6 class="section-title">
                            <i class="fas fa-dollar-sign"></i> Pricing
                        </h6>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group modern-form-group mb-0">
                                    <label for="purchase_price" class="modern-label">
                                        Purchase Price <span class="required">*</span>
                                    </label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">৳</span>
                                        </div>
                                        <input type="number"
                                               name="purchase_price"
                                               id="purchase_price"
                                               class="form-control modern-input"
                                               min="0"
                                               step="0.01"
                                               placeholder="0.00"
                                               required>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group modern-form-group mb-0">
                                    <label for="modal_profit_margin" class="modern-label">Profit %</label>
                                    <div class="input-group">
                                        <input type="number"
                                               id="modal_profit_margin"
                                               class="form-control modern-input"
                                               min="0"
                                               step="0.01"
                                               value="15">
                                        <div class="input-group-append">
                                            <span class="input-group-text">%</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group modern-form-group mb-0">
                                    <label for="sale_price" class="modern-label">
                                        Sale Price <span class="required">*</span>
                                    </label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">৳</span>
                                        </div>
                                        <input type="number"
                                               name="sale_price"
                                               id="sale_price"
                                               class="form-control modern-input"
                                               min="0"
                                               step="0.01"
                                               placeholder="0.00"
                                               required>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Profit Display -->
                        <div class="profit-display mt-2" id="modal-profitMarginDisplay" style="display: none;">
                            <span class="badge badge-success" id="modal-profit-badge">
                                Profit: ৳<span id="modal-marginAmount">0.00</span> (<span id="modal-marginPercentage">0%</span>)
                            </span>
                        </div>
                    </div>
                </div>

                <div class="modal-footer modern-modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-primary" id="saveProductBtn">
                        <i class="fas fa-save"></i> Save Product
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
/* Modern Modal Styles */
.modern-modal {
    border: none;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
}

.modern-modal-header {
    background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
    color: white;
    border-bottom: none;
    padding: 15px 20px;
}

.modern-modal-header .modal-title {
    font-weight: 600;
    font-size: 16px;
}

.modern-close {
    color: white;
    opacity: 0.8;
}

.modern-close:hover {
    color: white;
    opacity: 1;
}

.modern-modal-body {
    padding: 15px;
    background: #f8fafc;
    max-height: 70vh;
    overflow-y: auto;
}

.modern-modal-footer {
    padding: 12px 20px;
    border-top: 1px solid #e5e7eb;
    background: white;
}

/* Compact Form Sections */
.form-section {
    background: white;
    border-radius: 8px;
    padding: 12px 15px;
    margin-bottom: 12px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
}

.section-title {
    font-weight: 600;
    color: #374151;
    margin-bottom: 10px;
    padding-bottom: 6px;
    border-bottom: 1px solid #e5e7eb;
    font-size: 13px;
    display: flex;
    align-items: center;
    gap: 6px;
}

.modern-form-group {
    margin-bottom: 12px;
}

.modern-label {
    font-weight: 500;
    color: #374151;
    margin-bottom: 4px;
    font-size: 12px;
    display: block;
}

.modern-input, .modern-textarea, .modern-select {
    border: 1px solid #d1d5db;
    border-radius: 6px;
    padding: 6px 10px;
    font-size: 13px;
    transition: all 0.2s ease;
}

.modern-input:focus, .modern-textarea:focus, .modern-select:focus {
    outline: none;
    border-color: #6366f1;
    box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.1);
}

/* Quick Add Button */
.quick-add-btn {
    padding: 6px 10px;
    border-radius: 0 6px 6px 0;
}

.quick-add-form {
    background: #f0f9ff;
    border-radius: 6px;
    padding: 8px;
    border: 1px dashed #3b82f6;
}

/* Toggle Section Compact */
.toggle-section.compact {
    padding: 8px;
    background: #f8fafc;
    border-radius: 6px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    height: 100%;
}

.toggle-section.compact .modern-label {
    margin-bottom: 0;
    margin-right: 10px;
}

.modern-toggle {
    display: flex;
    align-items: center;
    gap: 8px;
}

.toggle-input {
    display: none;
}

.toggle-label {
    position: relative;
    display: inline-block;
    width: 36px;
    height: 18px;
    cursor: pointer;
    margin-bottom: 0;
}

.toggle-slider {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: #d1d5db;
    border-radius: 18px;
    transition: all 0.3s ease;
}

.toggle-slider:before {
    position: absolute;
    content: "";
    height: 14px;
    width: 14px;
    left: 2px;
    bottom: 2px;
    background-color: white;
    border-radius: 50%;
    transition: all 0.3s ease;
}

.toggle-input:checked + .toggle-label .toggle-slider {
    background-color: #10b981;
}

.toggle-input:checked + .toggle-label .toggle-slider:before {
    transform: translateX(18px);
}

.toggle-status {
    font-weight: 500;
    font-size: 11px;
    min-width: 25px;
}

/* Profit Display */
.profit-display {
    text-align: center;
}

.profit-display .badge {
    font-size: 12px;
    padding: 5px 10px;
}

/* Required indicator */
.required {
    color: #ef4444;
}

/* Select2 Fixes for Modal */
.product-modal-select2 + .select2-container {
    width: calc(100% - 46px) !important;
}

.select2-container--open {
    z-index: 9999 !important;
}

#newProductModal .select2-container {
    z-index: 9999;
}

#newProductModal .select2-dropdown {
    z-index: 10000 !important;
}

/* Responsive */
@media (max-width: 768px) {
    .modern-modal-body {
        padding: 10px;
    }

    .form-section {
        padding: 10px;
    }
}

/* Product Name Duplicate Check Styles */
.product-name-input-wrapper {
    position: relative;
}

.product-name-input-wrapper .name-check-icon {
    position: absolute;
    right: 10px;
    top: 50%;
    transform: translateY(-50%);
    display: none;
}

.product-name-input-wrapper.checking .name-check-icon {
    display: block;
}

.product-name-input-wrapper.checking .name-check-icon::after {
    content: '';
    display: inline-block;
    width: 14px;
    height: 14px;
    border: 2px solid #e5e7eb;
    border-top-color: #3b82f6;
    border-radius: 50%;
    animation: modal-spin 0.8s linear infinite;
}

@keyframes modal-spin {
    to { transform: rotate(360deg); }
}

.product-name-input-wrapper.valid .name-check-icon {
    display: block;
    color: #10b981;
}

.product-name-input-wrapper.valid .name-check-icon::after {
    content: '\f00c';
    font-family: 'Font Awesome 5 Free';
    font-weight: 900;
}

.product-name-input-wrapper.duplicate .name-check-icon {
    display: block;
    color: #f59e0b;
}

.product-name-input-wrapper.duplicate .name-check-icon::after {
    content: '\f071';
    font-family: 'Font Awesome 5 Free';
    font-weight: 900;
}

.modal-duplicate-warning {
    background: linear-gradient(135deg, rgba(245, 158, 11, 0.1) 0%, rgba(217, 119, 6, 0.1) 100%);
    border: 1px solid rgba(245, 158, 11, 0.3);
    border-radius: 6px;
    padding: 10px 12px;
    margin-top: 8px;
    animation: modal-slide-down 0.3s ease;
}

@keyframes modal-slide-down {
    from {
        opacity: 0;
        transform: translateY(-8px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.modal-duplicate-warning .warning-title {
    display: flex;
    align-items: center;
    gap: 6px;
    color: #b45309;
    font-weight: 600;
    font-size: 12px;
    margin-bottom: 6px;
}

.modal-duplicate-warning .warning-text {
    color: #92400e;
    font-size: 11px;
    margin-bottom: 8px;
}

.modal-duplicate-warning .duplicate-product-item {
    background: white;
    border-radius: 4px;
    padding: 6px 10px;
    margin-bottom: 4px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 11px;
}

.modal-duplicate-warning .duplicate-product-item:last-child {
    margin-bottom: 0;
}

.modal-duplicate-warning .product-name {
    font-weight: 600;
    color: #374151;
}

.modal-duplicate-warning .product-meta {
    color: #6b7280;
    font-size: 10px;
}

.modal-duplicate-warning .btn-view-existing {
    background: #f59e0b;
    color: white;
    border: none;
    padding: 3px 8px;
    border-radius: 4px;
    font-size: 10px;
    cursor: pointer;
    text-decoration: none;
}

.modal-duplicate-warning .btn-view-existing:hover {
    background: #d97706;
    color: white;
}
</style>

<script>
// Wait for jQuery to be available, then initialize
(function initProductModal() {
    if (typeof jQuery === 'undefined') {
        setTimeout(initProductModal, 50);
        return;
    }

    jQuery(document).ready(function($) {
        window.productModalTriggerRow = null;
        var modalDuplicateCheckTimeout = null;
        var modalHasDuplicate = false;

        // Duplicate name checking functionality
        function initModalDuplicateCheck() {
            var nameInput = $('#product_name');
            var nameWrapper = nameInput.closest('.product-name-input-wrapper');

            nameInput.on('input', function() {
                clearTimeout(modalDuplicateCheckTimeout);
                var name = $(this).val().trim();

                // Remove previous states
                nameWrapper.removeClass('checking valid duplicate');
                $('#modal-duplicate-warning').hide().empty();

                if (name.length < 2) {
                    modalHasDuplicate = false;
                    return;
                }

                // Show checking state
                nameWrapper.addClass('checking');

                // Debounce the check
                modalDuplicateCheckTimeout = setTimeout(function() {
                    checkModalDuplicateName(name);
                }, 500);
            });

            // Also check on blur
            nameInput.on('blur', function() {
                clearTimeout(modalDuplicateCheckTimeout);
                var name = $(this).val().trim();
                if (name.length >= 2) {
                    checkModalDuplicateName(name);
                }
            });
        }

        function checkModalDuplicateName(name) {
            var nameWrapper = $('#product_name').closest('.product-name-input-wrapper');

            $.ajax({
                url: '{{ route("products.check-duplicate") }}',
                method: 'GET',
                data: { name: name },
                success: function(response) {
                    nameWrapper.removeClass('checking');

                    if (response.exists) {
                        modalHasDuplicate = true;
                        nameWrapper.addClass('duplicate');
                        showModalDuplicateWarning(response.products);
                    } else {
                        modalHasDuplicate = false;
                        nameWrapper.addClass('valid');
                        $('#modal-duplicate-warning').hide().empty();
                    }
                },
                error: function() {
                    nameWrapper.removeClass('checking');
                    modalHasDuplicate = false;
                }
            });
        }

        function showModalDuplicateWarning(products) {
            var warningDiv = $('#modal-duplicate-warning');
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

        // Initialize duplicate check when modal opens
        $(document).on('shown.bs.modal', '#newProductModal', function() {
            initModalDuplicateCheck();
            // Reset duplicate state
            modalHasDuplicate = false;
            $('#product_name').closest('.product-name-input-wrapper').removeClass('checking valid duplicate');
            $('#modal-duplicate-warning').hide().empty();
        });

    function generateIdempotencyKey() {
        if (window.crypto && typeof window.crypto.randomUUID === 'function') {
            return window.crypto.randomUUID();
        }
        return 'idemp-' + Date.now() + '-' + Math.random().toString(16).slice(2);
    }

    function refreshModalIdempotencyKey() {
        $('#modal-idempotency-key').val(generateIdempotencyKey());
    }

    // Initialize modal Select2 when modal opens
    $(document).on('shown.bs.modal', '#newProductModal', function() {
        refreshModalIdempotencyKey();

        // Destroy any existing Select2 instances first
        if ($('#product_company').hasClass('select2-hidden-accessible')) {
            $('#product_company').select2('destroy');
        }
        if ($('#product_category').hasClass('select2-hidden-accessible')) {
            $('#product_category').select2('destroy');
        }

        // Initialize Select2 with dropdownParent set to the modal
        $('#product_company').select2({
            width: 'calc(100% - 46px)',
            dropdownParent: $('#newProductModal .modal-content'),
            placeholder: 'Select Company',
            allowClear: true
        });

        $('#product_category').select2({
            width: 'calc(100% - 46px)',
            dropdownParent: $('#newProductModal .modal-content'),
            placeholder: 'Select Category',
            allowClear: true
        });

        // Focus on product name
        setTimeout(function() {
            $('#product_name').focus();
        }, 100);
    });

    // Quick Add Company - using event delegation
    $(document).on('click', '#quick-add-company-btn', function(e) {
        e.preventDefault();
        e.stopPropagation();
        $('#quick-company-form').slideToggle(200);
        setTimeout(function() {
            $('#quick_company_name').focus();
        }, 250);
    });

    $(document).on('click', '#cancel-quick-company', function(e) {
        e.preventDefault();
        $('#quick-company-form').slideUp(200);
        $('#quick_company_name').val('');
        $('#quick_company_type').val('brand');
    });

    $(document).on('click', '#save-quick-company', function(e) {
        e.preventDefault();
        const companyName = $('#quick_company_name').val().trim();
        const companyType = ($('#quick_company_type').val() || 'brand').trim();
        if (!companyName) {
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
                name: companyName,
                type: companyType
            },
            success: function(response) {
                // Add new option to select
                const newOption = new Option(response.name, response.id, true, true);
                $('#product_company').append(newOption).trigger('change');

                // Hide and clear form
                $('#quick-company-form').slideUp(200);
                $('#quick_company_name').val('');
                $('#quick_company_type').val('brand');

                // Show success
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: response.was_existing ? 'info' : 'success',
                        title: response.was_existing ? 'Already Exists' : 'Company Created!',
                        text: response.was_existing
                            ? response.name + ' already exists and was selected.'
                            : response.name,
                        timer: 1500,
                        showConfirmButton: false
                    });
                } else {
                    alert('Company created: ' + response.name);
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

    // Quick Add Category - using event delegation
    $(document).on('click', '#quick-add-category-btn', function(e) {
        e.preventDefault();
        e.stopPropagation();
        $('#quick-category-form').slideToggle(200);
        setTimeout(function() {
            $('#quick_category_name').focus();
        }, 250);
    });

    $(document).on('click', '#cancel-quick-category', function(e) {
        e.preventDefault();
        $('#quick-category-form').slideUp(200);
        $('#quick_category_name').val('');
    });

    $(document).on('click', '#save-quick-category', function(e) {
        e.preventDefault();
        const categoryName = $('#quick_category_name').val().trim();
        if (!categoryName) {
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
                name: categoryName
            },
            success: function(response) {
                // Add new option to select
                const newOption = new Option(response.name, response.id, true, true);
                $('#product_category').append(newOption).trigger('change');

                // Hide and clear form
                $('#quick-category-form').slideUp(200);
                $('#quick_category_name').val('');

                // Show success
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Category Created!',
                        text: response.name,
                        timer: 1500,
                        showConfirmButton: false
                    });
                } else {
                    alert('Category created: ' + response.name);
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

    // Enter key support for quick add forms - using event delegation
    $(document).on('keypress', '#quick_company_name', function(e) {
        if (e.which === 13) {
            e.preventDefault();
            $('#save-quick-company').click();
        }
    });

    $(document).on('keypress', '#quick_category_name', function(e) {
        if (e.which === 13) {
            e.preventDefault();
            $('#save-quick-category').click();
        }
    });

    // Stock management toggle - using event delegation
    $(document).on('change', '#modal_is_stock_managed', function() {
        const isChecked = $(this).is(':checked');
        const statusText = $('#modal-stock-status-text');
        const stockFields = $('#modal-stock-fields');

        if (isChecked) {
            statusText.text('On').removeClass('text-danger').addClass('text-success');
            stockFields.css('opacity', '1').find('input').prop('disabled', false);
        } else {
            statusText.text('Off').removeClass('text-success').addClass('text-danger');
            stockFields.css('opacity', '0.5').find('input').prop('disabled', true);
        }
    });

    // Sync opening stock to current stock - using event delegation
    $(document).on('input', '#opening_stock', function() {
        $('#modal_current_stock').val($(this).val());
    });

    // Profit calculation - using event delegation
    $(document).on('input', '#purchase_price, #modal_profit_margin', function() {
        const purchasePrice = parseFloat($('#purchase_price').val()) || 0;
        const profitPct = parseFloat($('#modal_profit_margin').val()) || 0;

        if (purchasePrice > 0) {
            const profit = purchasePrice * (profitPct / 100);
            const salePrice = purchasePrice + profit;
            $('#sale_price').val(salePrice.toFixed(2));
            updateModalProfitDisplay();
        }
    });

    $(document).on('input', '#sale_price', function() {
        updateModalProfitDisplay();
    });

    function updateModalProfitDisplay() {
        const purchasePrice = parseFloat($('#purchase_price').val()) || 0;
        const salePrice = parseFloat($('#sale_price').val()) || 0;
        const profitAmount = salePrice - purchasePrice;
        const profitPct = purchasePrice > 0 ? ((profitAmount / purchasePrice) * 100) : 0;

        if (purchasePrice > 0 && salePrice > 0) {
            $('#modal-marginAmount').text(profitAmount.toFixed(2));
            $('#modal-marginPercentage').text(profitPct.toFixed(1) + '%');
            $('#modal-profitMarginDisplay').show();

            if (profitAmount >= 0) {
                $('#modal-profit-badge').removeClass('badge-danger').addClass('badge-success');
            } else {
                $('#modal-profit-badge').removeClass('badge-success').addClass('badge-danger');
            }
        } else {
            $('#modal-profitMarginDisplay').hide();
        }
    }

    // Form submission - using event delegation
    $(document).on('submit', '#new-product-form', function(e) {
        e.preventDefault();

        // Check for duplicate before submitting
        if (modalHasDuplicate) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'warning',
                    title: 'Duplicate Product',
                    text: 'A product with this name already exists. Please use a different name.'
                });
            } else {
                alert('A product with this name already exists. Please use a different name.');
            }
            $('#product_name').focus();
            return false;
        }

        const btn = $('#saveProductBtn');
        const originalText = btn.html();
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');

        $.ajax({
            url: '/products',
            method: 'POST',
            data: $(this).serialize(),
            success: function(product) {
                // Add product to all product selects on the page
                const optionText = product.name + ' (Stock: ' + (product.current_stock || 0) + ')';
                const newOption = '<option value="' + product.id + '" ' +
                    'data-price="' + product.sale_price + '" ' +
                    'data-stock="' + (product.current_stock || 0) + '" ' +
                    'data-purchase="' + product.purchase_price + '">' +
                    optionText + '</option>';

                // Add to all product selects (before the "+ New Product" option)
                $('.product-select').each(function() {
                    const $select = $(this);
                    // Remove current selection
                    // Add new option before the last option (+ New Product)
                    $select.find('option[value="__new__"]').before(newOption);
                });

                // If we know which row triggered the modal, select the product there
                if (window.productModalTriggerRow) {
                    const $targetSelect = window.productModalTriggerRow.find('.product-select');
                    $targetSelect.val(product.id).trigger('change');
                    window.productModalTriggerRow = null;
                } else {
                    // Otherwise select in the last/empty row
                    const $lastEmptySelect = $('.product-select').filter(function() {
                        return !$(this).val();
                    }).first();

                    if ($lastEmptySelect.length) {
                        $lastEmptySelect.val(product.id).trigger('change');
                    }
                }

                // Close modal
                $('#newProductModal').modal('hide');

                // Reset form
                $('#new-product-form')[0].reset();
                $('#modal_profit_margin').val('15');
                $('#modal_current_stock').val('0');
                $('#product_company').val('').trigger('change');
                $('#product_category').val('').trigger('change');
                $('#modal-profitMarginDisplay').hide();

                // Success message
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Product Created!',
                        text: product.name + ' has been added.',
                        timer: 2000,
                        showConfirmButton: false
                    });
                } else if (typeof toastr !== 'undefined') {
                    toastr.success('Product created: ' + product.name);
                }

                refreshModalIdempotencyKey();
            },
            error: function(xhr) {
                let msg = 'Failed to create product. Please check your input.';
                if (xhr.responseJSON) {
                    if (xhr.responseJSON.message) {
                        msg = xhr.responseJSON.message;
                    } else if (xhr.responseJSON.errors) {
                        const errors = xhr.responseJSON.errors;
                        msg = Object.values(errors).flat().join('\n');
                    }
                }

                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: msg
                    });
                } else {
                    alert(msg);
                }

                if (xhr.status === 409) {
                    refreshModalIdempotencyKey();
                }
            },
            complete: function() {
                btn.prop('disabled', false).html(originalText);
            }
        });
    });

    // Reset form when modal is hidden - using event delegation
    $(document).on('hidden.bs.modal', '#newProductModal', function() {
        var form = document.getElementById('new-product-form');
        if (form) form.reset();
        $('#modal_profit_margin').val('15');
        $('#modal_current_stock').val('0');
        $('#quick-company-form, #quick-category-form').hide();
        $('#quick_company_name, #quick_category_name').val('');
        $('#quick_company_type').val('brand');
        $('#modal-profitMarginDisplay').hide();
        $('.is-invalid').removeClass('is-invalid');

        // Reset duplicate check state
        modalHasDuplicate = false;
        $('#product_name').closest('.product-name-input-wrapper').removeClass('checking valid duplicate');
        $('#modal-duplicate-warning').hide().empty();

        // Reset Select2
        if ($('#product_company').hasClass('select2-hidden-accessible')) {
            $('#product_company').val('').trigger('change');
        }
        if ($('#product_category').hasClass('select2-hidden-accessible')) {
            $('#product_category').val('').trigger('change');
        }
    });

    });
})();
</script>
