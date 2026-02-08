@extends('adminlte::page')

@section('title', 'Product Insights')

@section('content_header')
    <h1>Product Insights</h1>
@stop

@section('content')
    <form method="GET" action="{{ route('reports.products.index') }}" class="card card-outline card-primary mb-3">
        <div class="card-header">
            <h3 class="card-title">Filters</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <label for="start_date">Start Date</label>
                    <input type="date" name="start_date" id="start_date" class="form-control"
                           value="{{ $filters['start_date'] ?? '' }}">
                </div>
                <div class="col-md-3">
                    <label for="end_date">End Date</label>
                    <input type="date" name="end_date" id="end_date" class="form-control"
                           value="{{ $filters['end_date'] ?? '' }}">
                </div>
                <div class="col-md-3">
                    <label for="category_id">Category Breakdown</label>
                    <select name="category_id" id="category_id" class="form-control"
                            data-default-category="{{ $filters['category_id'] ?? '' }}">
                        <option value="">Auto (top category)</option>
                        <option value="0" {{ (string)($filters['category_id'] ?? '') === '0' ? 'selected' : '' }}>
                            Uncategorized
                        </option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}"
                                {{ (string)($filters['category_id'] ?? '') === (string)$category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                @if($godowns->isNotEmpty())
                    <div class="col-md-3">
                        <label for="godown_id">Godown</label>
                        <select name="godown_id" id="godown_id" class="form-control">
                            <option value="">All Godowns</option>
                            @foreach($godowns as $godown)
                                <option value="{{ $godown->id }}"
                                    {{ (string)($filters['godown_id'] ?? '') === (string)$godown->id ? 'selected' : '' }}>
                                    {{ $godown->name }}{{ $godown->location ? ' - ' . $godown->location : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif
            </div>
            <div class="row mt-3">
                <div class="col-md-12">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-filter"></i> Apply Filters
                    </button>
                </div>
            </div>
        </div>
    </form>

    <div class="row">
        <div class="col-md-3">
            <div class="info-box bg-info">
                <span class="info-box-icon"><i class="fas fa-boxes"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Total Movement Qty</span>
                    <span class="info-box-number">{{ number_format($movementSummary->total_quantity ?? 0) }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="info-box bg-success">
                <span class="info-box-icon"><i class="fas fa-coins"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Movement Amount</span>
                    <span class="info-box-number">{{ number_format($movementSummary->total_amount ?? 0, 2) }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="info-box bg-warning">
                <span class="info-box-icon"><i class="fas fa-file-invoice"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Sales Invoices</span>
                    <span class="info-box-number">{{ number_format($movementSummary->invoice_count ?? 0) }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="info-box bg-primary">
                <span class="info-box-icon"><i class="fas fa-tags"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Products Moved</span>
                    <span class="info-box-number">{{ number_format($movementSummary->product_count ?? 0) }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Product Movement (Sales + Returns + Other Deliveries)</h3>
            <div class="card-tools">
                <button type="button" class="btn btn-sm btn-success export-excel"
                        data-url="{{ route('reports.products.export.movement-products') }}">
                    <i class="fas fa-file-excel"></i> Export Excel
                </button>
            </div>
        </div>
        <div class="card-body table-responsive">
            <table id="movement-products-table" class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Category</th>
                        <th>Company</th>
                        <th>Movement Qty</th>
                        <th>Movement Amount</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Non Moving Products (No Movement in Period)</h3>
            <div class="card-tools">
                <button type="button" class="btn btn-sm btn-success export-excel"
                        data-url="{{ route('reports.products.export.non-moving-products') }}">
                    <i class="fas fa-file-excel"></i> Export Excel
                </button>
            </div>
        </div>
        <div class="card-body table-responsive">
            <table id="non-moving-products-table" class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Category</th>
                        <th>Company</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Company Summary (Top Product per Company)</h3>
            <div class="card-tools">
                <button type="button" class="btn btn-sm btn-success export-excel"
                        data-url="{{ route('reports.products.export.company-summary') }}">
                    <i class="fas fa-file-excel"></i> Export Excel
                </button>
            </div>
        </div>
        <div class="card-body table-responsive">
            <table id="company-summary-table" class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Company</th>
                        <th>Total Movement Qty</th>
                        <th>Total Movement Amount</th>
                        <th>Top Product</th>
                        <th>Top Product Qty</th>
                        <th>Top Product Amount</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Category Movement</h3>
            <div class="card-tools">
                <button type="button" class="btn btn-sm btn-success export-excel"
                        data-url="{{ route('reports.products.export.category-summary') }}">
                    <i class="fas fa-file-excel"></i> Export Excel
                </button>
            </div>
        </div>
        <div class="card-body table-responsive">
            <table id="category-summary-table" class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Category</th>
                        <th>Total Movement Qty</th>
                        <th>Total Movement Amount</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                Company Sales for Category: {{ $selectedCategoryName ?? 'N/A' }}
            </h3>
            <div class="card-tools">
                <button type="button" class="btn btn-sm btn-success export-excel"
                        data-url="{{ route('reports.products.export.category-company-summary') }}">
                    <i class="fas fa-file-excel"></i> Export Excel
                </button>
            </div>
        </div>
        <div class="card-body table-responsive">
            <table id="category-company-summary-table" class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Company</th>
                        <th>Total Movement Qty</th>
                        <th>Total Movement Amount</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.25/css/dataTables.bootstrap4.min.css">
@stop

@section('js')
    <script src="https://cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.25/js/dataTables.bootstrap4.min.js"></script>
    <script>
        $(document).ready(function() {
            const qtyFormatter = new Intl.NumberFormat('en-US', { maximumFractionDigits: 2 });
            const moneyFormatter = new Intl.NumberFormat('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

            function getCategoryId() {
                const selected = $('#category_id').val();
                if (selected !== '') {
                    return selected;
                }
                return $('#category_id').data('default-category') || '';
            }

            function applyFilters(d) {
                d.start_date = $('#start_date').val();
                d.end_date = $('#end_date').val();
                d.category_id = getCategoryId();
                d.godown_id = $('#godown_id').length ? $('#godown_id').val() : '';
            }

            $('#movement-products-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route('reports.products.data.movement-products') }}',
                    data: applyFilters
                },
                order: [[3, 'desc']],
                columns: [
                    { data: 'product_name', name: 'product_name' },
                    { data: 'category_name', name: 'category_name' },
                    { data: 'company_name', name: 'company_name' },
                    {
                        data: 'movement_quantity',
                        name: 'movement_quantity',
                        render: function(data) { return qtyFormatter.format(data); }
                    },
                    {
                        data: 'movement_amount',
                        name: 'movement_amount',
                        render: function(data) { return moneyFormatter.format(data); }
                    }
                ]
            });

            $('#non-moving-products-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route('reports.products.data.non-moving-products') }}',
                    data: applyFilters
                },
                order: [[0, 'asc']],
                columns: [
                    { data: 'product_name', name: 'product_name' },
                    { data: 'category_name', name: 'category_name' },
                    { data: 'company_name', name: 'company_name' }
                ]
            });

            $('#company-summary-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route('reports.products.data.company-summary') }}',
                    data: applyFilters
                },
                order: [[1, 'desc']],
                columns: [
                    { data: 'company_name', name: 'company_name' },
                    {
                        data: 'total_quantity',
                        name: 'total_quantity',
                        render: function(data) { return qtyFormatter.format(data); }
                    },
                    {
                        data: 'total_amount',
                        name: 'total_amount',
                        render: function(data) { return moneyFormatter.format(data); }
                    },
                    { data: 'top_product', name: 'top_product' },
                    {
                        data: 'top_quantity',
                        name: 'top_quantity',
                        render: function(data) { return qtyFormatter.format(data); }
                    },
                    {
                        data: 'top_amount',
                        name: 'top_amount',
                        render: function(data) { return moneyFormatter.format(data); }
                    }
                ]
            });

            $('#category-summary-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route('reports.products.data.category-summary') }}',
                    data: applyFilters
                },
                order: [[1, 'desc']],
                columns: [
                    { data: 'category_name', name: 'category_name' },
                    {
                        data: 'total_quantity',
                        name: 'total_quantity',
                        render: function(data) { return qtyFormatter.format(data); }
                    },
                    {
                        data: 'total_amount',
                        name: 'total_amount',
                        render: function(data) { return moneyFormatter.format(data); }
                    }
                ]
            });

            $('#category-company-summary-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route('reports.products.data.category-company-summary') }}',
                    data: applyFilters
                },
                order: [[1, 'desc']],
                columns: [
                    { data: 'company_name', name: 'company_name' },
                    {
                        data: 'total_quantity',
                        name: 'total_quantity',
                        render: function(data) { return qtyFormatter.format(data); }
                    },
                    {
                        data: 'total_amount',
                        name: 'total_amount',
                        render: function(data) { return moneyFormatter.format(data); }
                    }
                ]
            });

            $('.export-excel').on('click', function() {
                const baseUrl = $(this).data('url');
                const params = new URLSearchParams({
                    start_date: $('#start_date').val(),
                    end_date: $('#end_date').val(),
                    category_id: getCategoryId()
                });
                const godownId = $('#godown_id').length ? $('#godown_id').val() : '';
                if (godownId) {
                    params.append('godown_id', godownId);
                }
                window.location.href = `${baseUrl}?${params.toString()}`;
            });
        });
    </script>
@stop
