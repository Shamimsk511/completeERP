@extends('layouts.modern-admin')

@section('title', 'Undelivered Products')

@section('page_title', 'Undelivered Products')

@section('header_actions')
    <a href="{{ route('challans.create') }}" class="btn modern-btn modern-btn-primary">
        <i class="fas fa-truck"></i> Create Delivery
    </a>
@stop

@section('page_content')
    <!-- Quick Stats -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="stat-card">
                <div class="stat-icon bg-danger">
                    <i class="fas fa-boxes"></i>
                </div>
                <div class="stat-info">
                    <h3>{{ $stats['total_items'] }}</h3>
                    <span>Pending Items</span>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card">
                <div class="stat-icon bg-warning">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-info">
                    <h3>{{ $stats['total_customers'] }}</h3>
                    <span>Customers Waiting</span>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card">
                <div class="stat-icon bg-info">
                    <i class="fas fa-cubes"></i>
                </div>
                <div class="stat-info">
                    <h3>{{ $stats['total_quantity'] }}</h3>
                    <span>Total Quantity</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Card -->
    <div class="card border-0 shadow-sm">
        <!-- Filters Header -->
        <div class="card-header bg-white py-3">
            <div class="row align-items-end g-3">
                <div class="col-md-4">
                    <label class="form-label small fw-bold text-muted">COMPANY</label>
                    <select class="form-select" id="company_filter">
                        <option value="">All Companies</option>
                        @foreach($companies as $company)
                            <option value="{{ $company->id }}">{{ $company->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-bold text-muted">CUSTOMER</label>
                    <select class="form-select" id="customer_filter">
                        <option value="">All Customers</option>
                        @foreach($customers as $customer)
                            <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <button type="button" class="btn btn-outline-secondary w-100" id="clear_filters">
                        <i class="fas fa-times me-1"></i> Clear Filters
                    </button>
                </div>
            </div>
        </div>

        <!-- Table -->
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0" id="undelivered-table">
                    <thead class="table-light">
                        <tr>
                            <th>Customer</th>
                            <th>Company</th>
                            <th>Product</th>
                            <th>Invoice</th>
                            <th class="text-center">Ordered</th>
                            <th class="text-center">Delivered</th>
                            <th class="text-center">Remaining</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- DataTables will populate -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@stop

@section('additional_css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css">
<style>
    /* Stat Cards */
    .stat-card {
        background: white;
        border-radius: 12px;
        padding: 20px;
        display: flex;
        align-items: center;
        gap: 16px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        transition: transform 0.2s;
    }

    .stat-card:hover {
        transform: translateY(-2px);
    }

    .stat-icon {
        width: 56px;
        height: 56px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        color: white;
    }

    .stat-icon.bg-danger { background: linear-gradient(135deg, #ef4444, #dc2626); }
    .stat-icon.bg-warning { background: linear-gradient(135deg, #f59e0b, #d97706); }
    .stat-icon.bg-info { background: linear-gradient(135deg, #06b6d4, #0891b2); }

    .stat-info h3 {
        font-size: 28px;
        font-weight: 700;
        margin: 0;
        color: #1f2937;
    }

    .stat-info span {
        font-size: 13px;
        color: #6b7280;
    }

    /* Table Styling */
    .table th {
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #6b7280;
        border-bottom: 2px solid #e5e7eb;
        padding: 12px;
    }

    .table td {
        padding: 12px;
        vertical-align: middle;
        border-bottom: 1px solid #f3f4f6;
    }

    .table tbody tr:hover {
        background-color: #f9fafb;
    }

    /* Customer/Product Info */
    .customer-info strong,
    .product-info strong {
        display: block;
        color: #1f2937;
        font-size: 14px;
    }

    .customer-info small,
    .product-info small {
        font-size: 12px;
    }

    /* Badges */
    .badge {
        font-weight: 500;
        padding: 6px 10px;
        font-size: 12px;
    }

    .badge-primary {
        background-color: #6366f1;
        color: white;
    }

    .badge-secondary {
        background-color: #6b7280;
        color: white;
    }

    .badge-info {
        background-color: #06b6d4;
        color: white;
    }

    .badge-danger {
        background-color: #ef4444;
        color: white;
    }

    /* Form Controls */
    .form-select {
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        padding: 10px 12px;
        font-size: 14px;
    }

    .form-select:focus {
        border-color: #6366f1;
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
    }

    /* Select2 Styling */
    .select2-container--bootstrap-5 .select2-selection {
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        min-height: 42px;
        padding: 4px 8px;
    }

    .select2-container--bootstrap-5 .select2-selection--single .select2-selection__rendered {
        line-height: 32px;
        color: #374151;
    }

    .select2-container--bootstrap-5.select2-container--focus .select2-selection {
        border-color: #6366f1;
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
    }

    .select2-container--bootstrap-5 .select2-dropdown {
        border-color: #e5e7eb;
        border-radius: 8px;
        box-shadow: 0 10px 25px rgba(0,0,0,0.1);
    }

    .select2-container--bootstrap-5 .select2-results__option--highlighted {
        background-color: #6366f1;
    }

    .select2-container--bootstrap-5 .select2-search--dropdown .select2-search__field {
        border: 1px solid #e5e7eb;
        border-radius: 6px;
        padding: 8px 12px;
    }

    .select2-container--bootstrap-5 .select2-search--dropdown .select2-search__field:focus {
        border-color: #6366f1;
        outline: none;
    }

    /* DataTables Overrides */
    .dataTables_filter input {
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        padding: 8px 12px;
        margin-left: 8px;
    }

    .dataTables_filter input:focus {
        border-color: #6366f1;
        outline: none;
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
    }

    .dataTables_length select {
        border: 1px solid #e5e7eb;
        border-radius: 6px;
        padding: 6px 8px;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button {
        border-radius: 6px;
        margin: 0 2px;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button.current {
        background: #6366f1 !important;
        border-color: #6366f1 !important;
        color: white !important;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
        background: #4f46e5 !important;
        border-color: #4f46e5 !important;
        color: white !important;
    }

    /* Action Buttons */
    .btn-group-sm .btn {
        padding: 4px 8px;
    }

    .btn-outline-info {
        color: #06b6d4;
        border-color: #06b6d4;
    }

    .btn-outline-info:hover {
        background-color: #06b6d4;
        color: white;
    }

    .btn-outline-success {
        color: #10b981;
        border-color: #10b981;
    }

    .btn-outline-success:hover {
        background-color: #10b981;
        color: white;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .stat-card {
            margin-bottom: 12px;
        }

        .stat-info h3 {
            font-size: 22px;
        }
    }
</style>
@stop

@section('additional_js')
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
$(document).ready(function() {
    // Initialize Select2 for Company and Customer filters
    $('#company_filter').select2({
        theme: 'bootstrap-5',
        placeholder: 'Select Company',
        allowClear: true,
        width: '100%'
    });

    $('#customer_filter').select2({
        theme: 'bootstrap-5',
        placeholder: 'Select Customer',
        allowClear: true,
        width: '100%'
    });

    // Initialize DataTable
    const table = $('#undelivered-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('sales.undelivered_items.data') }}",
            data: function(d) {
                d.customer_id = $('#customer_filter').val();
                d.company_id = $('#company_filter').val();
            }
        },
        columns: [
            { data: 'customer_name', name: 'customer_name' },
            { data: 'company_name', name: 'company_name' },
            { data: 'product_name', name: 'product_name' },
            { data: 'invoice_number', name: 'invoice_number' },
            { data: 'ordered_quantity', name: 'ordered_quantity', className: 'text-center' },
            { data: 'delivered_quantity', name: 'delivered_quantity', className: 'text-center' },
            { data: 'remaining_quantity', name: 'remaining_quantity', className: 'text-center' },
            { data: 'actions', name: 'actions', orderable: false, searchable: false, className: 'text-center' }
        ],
        order: [[0, 'asc']],
        pageLength: 25,
        language: {
            processing: '<i class="fas fa-spinner fa-spin fa-2x"></i>',
            emptyTable: 'No undelivered items found',
            zeroRecords: 'No matching records found',
            search: 'Search:'
        },
        dom: '<"row mb-3"<"col-sm-6"l><"col-sm-6"f>>rt<"row mt-3"<"col-sm-5"i><"col-sm-7"p>>'
    });

    // Filter on change
    $('#company_filter, #customer_filter').on('change', function() {
        table.draw();
    });

    // Clear filters
    $('#clear_filters').on('click', function() {
        $('#company_filter').val('').trigger('change');
        $('#customer_filter').val('').trigger('change');
        table.draw();
    });
});
</script>
@stop
