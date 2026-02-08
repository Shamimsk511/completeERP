@extends('layouts.modern-admin')

@section('title', 'Add Category')
@section('page_title', 'Add Category')

@section('header_actions')
    <a href="{{ route('categories.index') }}" class="btn modern-btn modern-btn-outline">
        <i class="fas fa-arrow-left"></i> Back
    </a>
@stop

@section('page_content')
@if($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="card modern-card">
    <div class="card-header modern-header">
        <h3 class="card-title"><i class="fas fa-tag"></i> New Category</h3>
    </div>
    <form action="{{ route('categories.store') }}" method="POST">
        @csrf
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="mb-1">Category Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group mt-4 pt-2">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="is_simple_product" name="is_simple_product" value="1" {{ old('is_simple_product') ? 'checked' : '' }}>
                            <label class="custom-control-label" for="is_simple_product">Simple product (no box/pcs)</label>
                        </div>
                    </div>
                </div>
            </div>

            <div id="boxed-fields">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="mb-1">Pieces per Box <span class="text-danger">*</span></label>
                            <input type="number" name="box_pcs" id="box_pcs" class="form-control" min="1" step="1" value="{{ old('box_pcs', 1) }}">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="mb-1">Tile Width (inch) <span class="text-danger">*</span></label>
                            <input type="number" name="tile_width_in" id="tile_width_in" class="form-control" min="0.01" step="0.01" value="{{ old('tile_width_in') }}">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="mb-1">Tile Length (inch) <span class="text-danger">*</span></label>
                            <input type="number" name="tile_length_in" id="tile_length_in" class="form-control" min="0.01" step="0.01" value="{{ old('tile_length_in') }}">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="mb-1">Sq Ft per Pcs</label>
                            <input type="number" name="pieces_feet" id="pieces_feet" class="form-control" step="0.0001" value="{{ old('pieces_feet', 0) }}" readonly>
                            <small class="text-muted">Auto-calculated from width × length ÷ 144</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label class="mb-1">Weight Value</label>
                        <input type="number" name="weight_value" class="form-control" min="0" step="0.001" value="{{ old('weight_value') }}">
                        <small class="text-muted">Optional: weight per unit/box/piece</small>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label class="mb-1">Weight Unit</label>
                        <select name="weight_unit" class="form-control">
                            <option value="">Select Unit</option>
                            <option value="per_piece" {{ old('weight_unit') === 'per_piece' ? 'selected' : '' }}>Per Piece</option>
                            <option value="per_box" {{ old('weight_unit') === 'per_box' ? 'selected' : '' }}>Per Box</option>
                            <option value="per_unit" {{ old('weight_unit') === 'per_unit' ? 'selected' : '' }}>Per Unit</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-success">Save</button>
            <a href="{{ route('categories.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@stop

@section('additional_js')
<script>
(function() {
    const simpleToggle = document.getElementById('is_simple_product');
    const boxedFields = document.getElementById('boxed-fields');
    const widthInput = document.getElementById('tile_width_in');
    const lengthInput = document.getElementById('tile_length_in');
    const piecesFeetInput = document.getElementById('pieces_feet');

    function updateVisibility() {
        const isSimple = simpleToggle.checked;
        boxedFields.style.display = isSimple ? 'none' : 'block';
        if (isSimple) {
            piecesFeetInput.value = 0;
        } else {
            calculatePiecesFeet();
        }
    }

    function calculatePiecesFeet() {
        const w = parseFloat(widthInput.value) || 0;
        const l = parseFloat(lengthInput.value) || 0;
        if (w > 0 && l > 0) {
            const pcs = (w * l) / 144;
            piecesFeetInput.value = pcs.toFixed(4);
        } else {
            piecesFeetInput.value = 0;
        }
    }

    simpleToggle.addEventListener('change', updateVisibility);
    widthInput.addEventListener('input', calculatePiecesFeet);
    lengthInput.addEventListener('input', calculatePiecesFeet);

    updateVisibility();
})();
</script>
@stop
