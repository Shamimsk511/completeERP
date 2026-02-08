@extends('adminlte::page')

@section('title', 'Customer Insights')

@section('content_header')
    <h1>Customer Insights</h1>
@stop

@section('content')
    <form method="GET" action="{{ route('reports.customers.index') }}" class="card card-outline card-primary mb-3">
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
                <div class="col-md-6">
                    <label for="category_id">Category Filter (for category tables)</label>
                    <select name="category_id" id="category_id" class="form-control">
                        <option value="">All Categories</option>
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
                <span class="info-box-icon"><i class="fas fa-users"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Active Customers</span>
                    <span class="info-box-number">{{ number_format($summary->customer_count ?? 0) }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="info-box bg-success">
                <span class="info-box-icon"><i class="fas fa-file-invoice"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Invoices</span>
                    <span class="info-box-number">{{ number_format($summary->invoice_count ?? 0) }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="info-box bg-warning">
                <span class="info-box-icon"><i class="fas fa-coins"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Total Sales</span>
                    <span class="info-box-number">{{ number_format($summary->total_amount ?? 0, 2) }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="info-box bg-primary">
                <span class="info-box-icon"><i class="fas fa-shopping-basket"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Avg Basket</span>
                    <span class="info-box-number">{{ number_format($summary->avg_basket ?? 0, 2) }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Customer Purchase Summary</h3>
            <div class="card-tools">
                <button type="button" class="btn btn-sm btn-success export-excel"
                        data-url="{{ route('reports.customers.export.summary') }}">
                    <i class="fas fa-file-excel"></i> Export Excel
                </button>
            </div>
        </div>
        <div class="card-body table-responsive">
            <table id="customer-summary-table" class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Customer</th>
                        <th>Phone</th>
                        <th>Address</th>
                        <th>Invoices</th>
                        <th>Total Amount</th>
                        <th>Avg Basket</th>
                        <th>Frequency / Month</th>
                        <th>Last Purchase</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Category-wise Top Customer</h3>
            <div class="card-tools">
                <button type="button" class="btn btn-sm btn-success export-excel"
                        data-url="{{ route('reports.customers.export.category-top') }}">
                    <i class="fas fa-file-excel"></i> Export Excel
                </button>
            </div>
        </div>
        <div class="card-body table-responsive">
            <table id="category-top-table" class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Category</th>
                        <th>Top Customer</th>
                        <th>Total Qty</th>
                        <th>Total Amount</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Company-wise Top Customer</h3>
            <div class="card-tools">
                <button type="button" class="btn btn-sm btn-success export-excel"
                        data-url="{{ route('reports.customers.export.company-top') }}">
                    <i class="fas fa-file-excel"></i> Export Excel
                </button>
            </div>
        </div>
        <div class="card-body table-responsive">
            <table id="company-top-table" class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Company</th>
                        <th>Top Customer</th>
                        <th>Total Qty</th>
                        <th>Total Amount</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Customer Category Mix</h3>
            <div class="card-tools">
                <button type="button" class="btn btn-sm btn-success export-excel"
                        data-url="{{ route('reports.customers.export.category-customers') }}">
                    <i class="fas fa-file-excel"></i> Export Excel
                </button>
            </div>
        </div>
        <div class="card-body table-responsive">
            <table id="category-customer-table" class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Category</th>
                        <th>Customer</th>
                        <th>Total Qty</th>
                        <th>Total Amount</th>
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

            function applyFilters(d) {
                d.start_date = $('#start_date').val();
                d.end_date = $('#end_date').val();
                d.category_id = $('#category_id').val();
            }

            $('#customer-summary-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route('reports.customers.data.summary') }}',
                    data: applyFilters
                },
                order: [[4, 'desc']],
                columns: [
                    {
                        data: 'customer_name',
                        name: 'customer_name',
                        render: function(data, type, row) {
                            const base = '{{ url('customers') }}';
                            return `<a href="${base}/${row.customer_id}" class="customer-name">${data}</a>`;
                        }
                    },
                    { data: 'phone', name: 'phone' },
                    { data: 'address', name: 'address' },
                    {
                        data: 'invoice_count',
                        name: 'invoice_count',
                        render: function(data) { return qtyFormatter.format(data); }
                    },
                    {
                        data: 'total_amount',
                        name: 'total_amount',
                        render: function(data) { return moneyFormatter.format(data); }
                    },
                    {
                        data: 'avg_basket',
                        name: 'avg_basket',
                        render: function(data) { return moneyFormatter.format(data); }
                    },
                    {
                        data: 'frequency_per_month',
                        name: 'frequency_per_month',
                        render: function(data) { return qtyFormatter.format(data); }
                    },
                    { data: 'last_purchase', name: 'last_purchase' }
                ]
            });

            $('#category-top-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route('reports.customers.data.category-top') }}',
                    data: applyFilters
                },
                order: [[3, 'desc']],
                columns: [
                    { data: 'category_name', name: 'category_name' },
                    {
                        data: 'customer_name',
                        name: 'customer_name',
                        render: function(data, type, row) {
                            const base = '{{ url('customers') }}';
                            return `<a href="${base}/${row.customer_id}" class="customer-name">${data}</a>`;
                        }
                    },
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

            $('#company-top-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route('reports.customers.data.company-top') }}',
                    data: applyFilters
                },
                order: [[3, 'desc']],
                columns: [
                    { data: 'company_name', name: 'company_name' },
                    {
                        data: 'customer_name',
                        name: 'customer_name',
                        render: function(data, type, row) {
                            const base = '{{ url('customers') }}';
                            return `<a href="${base}/${row.customer_id}" class="customer-name">${data}</a>`;
                        }
                    },
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

            $('#category-customer-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route('reports.customers.data.category-customers') }}',
                    data: applyFilters
                },
                order: [[3, 'desc']],
                columns: [
                    { data: 'category_name', name: 'category_name' },
                    {
                        data: 'customer_name',
                        name: 'customer_name',
                        render: function(data, type, row) {
                            const base = '{{ url('customers') }}';
                            return `<a href="${base}/${row.customer_id}" class="customer-name">${data}</a>`;
                        }
                    },
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
                    category_id: $('#category_id').val()
                });
                window.location.href = `${baseUrl}?${params.toString()}`;
            });
        });
    </script>
@stop
