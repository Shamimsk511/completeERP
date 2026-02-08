@extends('adminlte::page')

@section('title', 'Create Return')

@section('content_header')
    <h1>Create New Return Record</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Return Details</h3>
        </div>
        <div class="card-body">
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                    {{ session('error') }}
                </div>
            @endif

            <form action="{{ route('other-delivery-returns.store') }}" method="POST" id="return-form">
                @csrf
                
                <div class="row">
                    <!-- Return Info Section -->
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header bg-primary">
                                <h3 class="card-title">Return Information</h3>
                            </div>
                            <div class="card-body">
                                <div class="form-group">
                                    <label for="return_number">Return Number <span class="text-danger">*</span></label>
                                    <input type="text" name="return_number" id="return_number" class="form-control" value="{{ $return_number }}" readonly>
                                </div>
                                
                                <div class="form-group">
                                    <label for="return_date">Return Date <span class="text-danger">*</span></label>
                                    <input type="date" name="return_date" id="return_date" class="form-control" value="{{ $return_date }}" required>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Returner Information -->
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header bg-info">
                                <h3 class="card-title">Returner Information</h3>
                            </div>
                            <div class="card-body">
                                <div class="form-group">
                                    <label for="returner_name">Returner Name <span class="text-danger">*</span></label>
                                    <input type="text" name="returner_name" id="returner_name" class="form-control" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="returner_phone">Returner Phone</label>
                                    <input type="text" name="returner_phone" id="returner_phone" class="form-control">
                                </div>
                                
                                <div class="form-group">
                                    <label for="returner_address">Returner Address <span class="text-danger">*</span></label>
                                    <textarea name="returner_address" id="returner_address" class="form-control" rows="3" required></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Additional Information -->
                <div class="card mt-4">
                    <div class="card-header bg-warning">
                        <h3 class="card-title">Additional Information</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="notes">Notes (Reason for Return)</label>
                            <textarea name="notes" id="notes" class="form-control" rows="3"></textarea>
                        </div>
                    </div>
                </div>
                
                <!-- Products Section -->
                <div class="card mt-4">
                    <div class="card-header bg-success">
                        <h3 class="card-title">Products Being Returned</h3>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered" id="products-table">
                                <thead>
                                    <tr>
                                        <th width="25%">Product</th>
                                        <th width="20%">Description</th>
                                        <th width="10%">Quantity</th>
                                        <th width="10%">Unit</th>
                                        <th width="10%">Cartons</th>
                                        <th width="10%">Pieces</th>
                                        <th width="5%"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="product-row">
                                        <td>
                                            <select name="product_id[]" class="form-control select2 product-select" required>
                                                <option value="">Select Product</option>
                                                @foreach($products as $product)
                                                    <option value="{{ $product->id }}" data-box-pcs="{{ $product->category->box_pcs ?? 0 }}" data-pieces-feet="{{ $product->category->pieces_feet ?? 0 }}">
                                                        {{ $product->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            <input type="text" name="description[]" class="form-control">
                                        </td>
                                        <td>
                                            <input type="number" name="quantity[]" class="form-control quantity-field" min="0.01" step="0.01" required>
                                            <input type="hidden" class="box-pcs" value="0">
                                            <input type="hidden" class="pieces-feet" value="0">
                                        </td>
                                        <td>
                                            <select class="form-control quantity-type">
                                                <option value="quantity">Quantity</option>
                                                <option value="carton_pieces">Carton/Pieces</option>
                                            </select>
                                        </td>
                                        <td>
                                            <input type="number" name="cartons[]" class="form-control carton-field" min="0" readonly>
                                        </td>
                                        <td>
                                            <input type="number" name="pieces[]" class="form-control pieces-field" min="0" readonly>
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-danger btn-sm remove-row">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="7">
                                            <button type="button" class="btn btn-success btn-sm" id="add-row">
                                                <i class="fas fa-plus"></i> Add Product
                                            </button>
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
                
                <div class="form-group mt-4 text-center">
                    <button type="submit" class="btn btn-primary btn-lg" id="submit-btn">
                        <i class="fas fa-save"></i> Create Return
                    </button>
                    <a href="{{ route('other-delivery-returns.index') }}" class="btn btn-default btn-lg">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
    <style>
        .select2-container .select2-selection--single {
            height: 38px;
            line-height: 38px;
        }
        
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 38px;
        }
        
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 38px;
        }
    </style>
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            // Initialize Select2
            initializeSelect2();
            
            // Add new row
            $('#add-row').click(function() {
                const newRow = $('.product-row:first').clone();
                newRow.find('input').val('');
                newRow.find('select.product-select').val('').trigger('change');
                $('#products-table tbody').append(newRow);
                initializeSelect2();
            });
            
            // Remove row
            $(document).on('click', '.remove-row', function() {
                if ($('.product-row').length > 1) {
                    $(this).closest('tr').remove();
                } else {
                    alert('At least one product is required.');
                }
            });
            
            // Product selection change
            $(document).on('change', '.product-select', function() {
                const row = $(this).closest('tr');
                const selectedOption = $(this).find('option:selected');
                const boxPcs = selectedOption.data('box-pcs') || 0;
                const piecesFeet = selectedOption.data('pieces-feet') || 0;
                
                row.find('.box-pcs').val(boxPcs);
                row.find('.pieces-feet').val(piecesFeet);
                
                // Reset quantity fields
                row.find('.quantity-field').val('');
                row.find('.carton-field').val('');
                row.find('.pieces-field').val('');
            });
            
            // Initialize quantity type handling
            initializeQuantityTypes();
            
            // Calculate boxes and pieces on quantity change
            $(document).on('input', '.quantity-field', function() {
                calculateCartonsAndPieces($(this).closest('tr'));
            });
            
            // Calculate quantity based on cartons and pieces
            $(document).on('input', '.carton-field, .pieces-field', function() {
                calculateQuantityFromCartonPieces($(this).closest('tr'));
            });
            
            // Form validation
            $('#return-form').submit(function(e) {
                let valid = true;
                let errorMessage = '';
                
                // Check if at least one product has a quantity
                let hasProducts = false;
                $('.quantity-field').each(function() {
                    if (parseFloat($(this).val()) > 0) {
                        hasProducts = true;
                        return false; // Break the loop
                    }
                });
                
                if (!hasProducts) {
                    valid = false;
                    errorMessage = 'Please add at least one product with a quantity.';
                }
                
                // Check if quantities are valid
                $('.product-row').each(function() {
                    const productSelect = $(this).find('.product-select');
                    const quantityField = $(this).find('.quantity-field');
                    const quantity = parseFloat(quantityField.val());
                    
                    if (productSelect.val() && (isNaN(quantity) || quantity <= 0)) {
                        valid = false;
                        errorMessage = 'Please enter valid quantities for all products.';
                        quantityField.addClass('is-invalid');
                        return false; // Break the loop
                    } else {
                        quantityField.removeClass('is-invalid');
                    }
                });
                
                if (!valid) {
                    e.preventDefault();
                    alert(errorMessage);
                    return false;
                }
                
                return true;
            });
        });
        
        function initializeSelect2() {
            $('.select2').select2({
                width: '100%'
            });
        }
        
        // Initialize quantity type handling
        function initializeQuantityTypes() {
            $(document).on('change', '.quantity-type', function() {
                const row = $(this).closest('tr');
                const value = $(this).val();
                
                if (value === 'quantity') {
                    // Quantity mode
                    row.find('.carton-field, .pieces-field').prop('readonly', true);
                    row.find('.quantity-field').prop('readonly', false);
                    
                    // Recalculate cartons and pieces
                    calculateCartonsAndPieces(row);
                } else {
                    // Carton/Pieces mode
                    row.find('.carton-field, .pieces-field').prop('readonly', false);
                    row.find('.quantity-field').prop('readonly', true);
                    
                    // Initialize carton/pieces fields if empty
                    if (!row.find('.carton-field').val() && !row.find('.pieces-field').val()) {
                        calculateCartonsAndPieces(row);
                    }
                }
            });
            
            // Initialize existing rows
            $('.quantity-type').trigger('change');
        }
        
        // Calculate cartons and pieces based on quantity
        function calculateCartonsAndPieces(row) {
            const quantity = parseFloat(row.find('.quantity-field').val()) || 0;
            const boxPcs = parseFloat(row.find('.box-pcs').val()) || 0;
            const piecesFeet = parseFloat(row.find('.pieces-feet').val()) || 0;
            
            if (boxPcs > 0 && piecesFeet > 0) {
                // Calculate total pieces
                const totalPieces = Math.round(quantity / piecesFeet);
                
                // Calculate cartons (whole number)
                const cartons = Math.floor(totalPieces / boxPcs);
                
                // Calculate remaining pieces
                const pieces = totalPieces - (cartons * boxPcs);
                
                row.find('.carton-field').val(cartons);
                row.find('.pieces-field').val(pieces);
            } else {
                row.find('.carton-field').val('');
                row.find('.pieces-field').val('');
            }
        }
        
        // Calculate quantity based on cartons and pieces
        function calculateQuantityFromCartonPieces(row) {
            const cartons = parseInt(row.find('.carton-field').val()) || 0;
            const pieces = parseInt(row.find('.pieces-field').val()) || 0;
            const boxPcs = parseFloat(row.find('.box-pcs').val()) || 0;
            const piecesFeet = parseFloat(row.find('.pieces-feet').val()) || 0;
            
            if (boxPcs > 0 && piecesFeet > 0) {
                // Calculate total pieces
                const totalPieces = (cartons * boxPcs) + pieces;
                
                // Calculate quantity
                const quantity = (totalPieces * piecesFeet).toFixed(2);
                
                row.find('.quantity-field').val(quantity);
            }
        }
    </script>
@stop
