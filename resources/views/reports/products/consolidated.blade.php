@extends('adminlte::page')

@section('title', 'Consolidated Product Report')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-chart-line mr-2"></i> Consolidated Product Report</h1>
        <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('reports.products.index') }}">Reports</a></li>
            <li class="breadcrumb-item active">Consolidated Report</li>
        </ol>
    </div>
@stop

@section('content')
    <!-- Filter Card -->
    <div class="card card-primary card-outline">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-filter"></i> Filter Report</h3>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                    <i class="fas fa-minus"></i>
                </button>
            </div>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('reports.products.consolidated') }}" id="report-filter-form">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="start_date">Start Date</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="far fa-calendar-alt"></i></span>
                                </div>
                                <input type="date" class="form-control" id="start_date" name="start_date" 
                                    value="{{ $filters['start_date'] ?? '' }}">
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="end_date">End Date</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="far fa-calendar-alt"></i></span>
                                </div>
                                <input type="date" class="form-control" id="end_date" name="end_date" 
                                    value="{{ $filters['end_date'] ?? '' }}">
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="product_id">Product</label>
                            <select class="form-control select2" id="product_id" name="product_id">
                                <option value="">All Products</option>
                                @foreach($products as $product)
                                    <option value="{{ $product->id }}" 
                                        {{ (isset($filters['product_id']) && $filters['product_id'] == $product->id) ? 'selected' : '' }}>
                                        {{ $product->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="category_id">Category</label>
                            <select class="form-control select2" id="category_id" name="category_id">
                                <option value="">All Categories</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" 
                                        {{ (isset($filters['category_id']) && $filters['category_id'] == $category->id) ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="company_id">Company</label>
                            <select class="form-control select2" id="company_id" name="company_id">
                                <option value="">All Companies</option>
                                @foreach($companies as $company)
                                    <option value="{{ $company->id }}" 
                                        {{ (isset($filters['company_id']) && $filters['company_id'] == $company->id) ? 'selected' : '' }}>
                                        {{ $company->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    @if($godowns->isNotEmpty())
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="godown_id">Godown</label>
                                <select class="form-control select2" id="godown_id" name="godown_id">
                                    <option value="">All Godowns</option>
                                    @foreach($godowns as $godown)
                                        <option value="{{ $godown->id }}" 
                                            {{ (isset($filters['godown_id']) && $filters['godown_id'] == $godown->id) ? 'selected' : '' }}>
                                            {{ $godown->name }}{{ $godown->location ? ' - ' . $godown->location : '' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    @endif
                    
                    @php
                        $actionColClass = $godowns->isNotEmpty() ? 'col-md-6' : 'col-md-9';
                    @endphp
                    <div class="{{ $actionColClass }} d-flex align-items-end">
                        <div class="btn-group">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i> Generate Report
                            </button>
                            
                            <a href="{{ route('reports.products.consolidated') }}" class="btn btn-default">
                                <i class="fas fa-sync"></i> Reset Filters
                            </a>
                            
                            <button type="button" class="btn btn-success" id="export-excel">
                                <i class="fas fa-file-excel"></i> Export to Excel
                            </button>
                            
                            <button type="button" class="btn btn-info" id="print-report">
                                <i class="fas fa-print"></i> Print Report
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Summary Stats -->
    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ number_format(collect($reportData)->sum('sales')) }}</h3>
                    <p>Total Units Sold</p>
                </div>
                <div class="icon">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <a href="{{ route('reports.products.sales') }}" class="small-box-footer">
                    View Sales Report <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
        
        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ number_format(collect($reportData)->sum('purchases')) }}</h3>
                    <p>Total Units Purchased</p>
                </div>
                <div class="icon">
                    <i class="fas fa-truck-loading"></i>
                </div>
                <a href="{{ route('reports.products.purchases') }}" class="small-box-footer">
                    View Purchase Report <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
        
        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ number_format(collect($reportData)->sum('returns')) }}</h3>
                    <p>Total Units Returned</p>
                </div>
                <div class="icon">
                    <i class="fas fa-undo"></i>
                </div>
                <a href="{{ route('reports.products.returns') }}" class="small-box-footer">
                    View Returns Report <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
        
        <div class="col-lg-3 col-6">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>{{ number_format(collect($reportData)->sum('other_deliveries')) }}</h3>
                    <p>Total Other Deliveries</p>
                </div>
                <div class="icon">
                    <i class="fas fa-truck"></i>
                </div>
                <a href="{{ route('reports.products.other-deliveries') }}" class="small-box-footer">
                    View Deliveries Report <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
    </div>

    
    <!-- Data Table Card -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-table mr-1"></i> 
                Complete Product Movement Data
            </h3>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                    <i class="fas fa-minus"></i>
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="product-report-table" class="table table-bordered table-hover table-striped">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Category</th>
                            <th>Company</th>
                            <th>Sales <i class="fas fa-shopping-cart text-info"></i></th>
                            <th>Returns <i class="fas fa-undo text-warning"></i></th>
                            <th>Purchases <i class="fas fa-truck-loading text-success"></i></th>
                            <th>Other Deliveries <i class="fas fa-truck text-danger"></i></th>
                            <th>Net Change</th>
                            <th>Current Stock</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($reportData as $item)
                        <tr>
                            <td>{{ $item['product']->name }}</td>
                            <td>{{ $item['product']->category->name ?? 'N/A' }}</td>
                            <td>{{ $item['product']->company->name ?? 'N/A' }}</td>
                            <td>{{ number_format($item['sales']) }}</td>
                            <td>{{ number_format($item['returns']) }}</td>
                            <td>{{ number_format($item['purchases']) }}</td>
                            <td>{{ number_format($item['other_deliveries']) }}</td>
                            <td>
                                <span class="badge badge-{{ $item['net_change'] >= 0 ? 'success' : 'danger' }} p-2">
                                    {{ number_format($item['net_change']) }}
                                    <i class="fas fa-{{ $item['net_change'] >= 0 ? 'arrow-up' : 'arrow-down' }} ml-1"></i>
                                </span>
                            </td>
                            <td>
                                <span class="badge p-2 {{ $item['product']->current_stock > 10 ? 'badge-success' : ($item['product']->current_stock > 0 ? 'badge-warning' : 'badge-danger') }}">
                                    {{ number_format($item['product']->current_stock) }}
                                </span>
                            </td>
                            <td>
                                <div class="btn-group">
                                    <button type="button" class="btn btn-sm btn-info view-details" data-product-id="{{ $item['product']->id }}">
                                        <i class="fas fa-chart-line"></i> Lifecycle
                                    </button>
                                    <a href="{{ route('products.show', $item['product']->id) }}" class="btn btn-sm btn-secondary" target="_blank" title="View Product">
                                        <i class="fas fa-external-link-alt"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="bg-light font-weight-bold">
                            <td colspan="3">TOTALS</td>
                            <td>{{ number_format(collect($reportData)->sum('sales')) }}</td>
                            <td>{{ number_format(collect($reportData)->sum('returns')) }}</td>
                            <td>{{ number_format(collect($reportData)->sum('purchases')) }}</td>
                            <td>{{ number_format(collect($reportData)->sum('other_deliveries')) }}</td>
                            <td>{{ number_format(collect($reportData)->sum('net_change')) }}</td>
                            <td>{{ number_format(collect($reportData)->sum(function($item) { return $item['product']->current_stock; })) }}</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
        
    <!-- Chart Card -->
    <div class="card card-primary">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-chart-bar mr-1"></i>
                Product Movement Summary
            </h3>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                    <i class="fas fa-minus"></i>
                </button>
                <button type="button" class="btn btn-tool" data-card-widget="maximize">
                    <i class="fas fa-expand"></i>
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="chart">
                <canvas id="productMovementChart" height="150"></canvas>
            </div>
        </div>
    </div>
    <!-- Product Movement Details Modal -->
    <div class="modal fade" id="productDetailsModal">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header bg-info">
                    <h4 class="modal-title">Product Movement Details</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="nav-tabs-custom">
                        <ul class="nav nav-tabs" id="productDetailsTabs" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" id="sales-tab" data-toggle="pill" href="#sales" role="tab">
                                    <i class="fas fa-shopping-cart mr-1"></i> Sales
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="returns-tab" data-toggle="pill" href="#returns" role="tab">
                                    <i class="fas fa-undo mr-1"></i> Returns
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="purchases-tab" data-toggle="pill" href="#purchases" role="tab">
                                    <i class="fas fa-truck-loading mr-1"></i> Purchases
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="deliveries-tab" data-toggle="pill" href="#deliveries" role="tab">
                                    <i class="fas fa-truck mr-1"></i> Other Deliveries
                                </a>
                            </li>
                        </ul>
                        <div class="tab-content p-3" id="productDetailsTabContent">
                            <div class="tab-pane fade show active" id="sales" role="tabpanel">
                                <!-- Sales data will be loaded by AJAX -->
                                <div class="text-center p-5">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="sr-only">Loading...</span>
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="returns" role="tabpanel">
                                <!-- Returns data will be loaded by AJAX -->
                            </div>
                            <div class="tab-pane fade" id="purchases" role="tabpanel">
                                <!-- Purchases data will be loaded by AJAX -->
                            </div>
                            <div class="tab-pane fade" id="deliveries" role="tabpanel">
                                <!-- Other Deliveries data will be loaded by AJAX -->
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-info" id="print-details">
                        <i class="fas fa-print"></i> Print Details
                    </button>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.25/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap4-theme@1.0.0/dist/select2-bootstrap4.min.css">
@stop

@section('js')
    <script src="https://cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.25/js/dataTables.bootstrap4.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        $(document).ready(function() {
            // Initialize select2
            $('.select2').select2({
                theme: 'bootstrap4',
                placeholder: 'Select an option'
            });
            
            // Initialize DataTable
            $('#product-report-table').DataTable({
                "paging": true,
                "lengthChange": true,
                "searching": true,
                "ordering": true,
                "info": true,
                "autoWidth": false,
                "responsive": true,
                "language": {
                    "emptyTable": "No product data available for the selected period"
                }
            });
            
            // Generate chart with the top 10 products by movement volume
            const labels = {!! json_encode(collect($reportData)->take(10)->map(function($item) { return $item['product']->name; })) !!};
            const sales = {!! json_encode(collect($reportData)->take(10)->pluck('sales')) !!};
            const returns = {!! json_encode(collect($reportData)->take(10)->pluck('returns')) !!};
            const purchases = {!! json_encode(collect($reportData)->take(10)->pluck('purchases')) !!};
            const deliveries = {!! json_encode(collect($reportData)->take(10)->pluck('other_deliveries')) !!};
            
            const ctx = document.getElementById('productMovementChart').getContext('2d');
            const chart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: 'Sales',
                            data: sales,
                            backgroundColor: 'rgba(60, 141, 188, 0.7)',
                            borderColor: 'rgba(60, 141, 188, 1)',
                            borderWidth: 1
                        },
                        {
                            label: 'Returns',
                            data: returns,
                            backgroundColor: 'rgba(255, 193, 7, 0.7)',
                            borderColor: 'rgba(255, 193, 7, 1)',
                            borderWidth: 1
                        },
                        {
                            label: 'Purchases',
                            data: purchases,
                            backgroundColor: 'rgba(40, 167, 69, 0.7)',
                            borderColor: 'rgba(40, 167, 69, 1)',
                            borderWidth: 1
                        },
                        {
                            label: 'Other Deliveries',
                            data: deliveries,
                            backgroundColor: 'rgba(220, 53, 69, 0.7)',
                            borderColor: 'rgba(220, 53, 69, 1)',
                            borderWidth: 1
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Quantity'
                            }
                        },
                        x: {
                            title: {
                                display: true,
                                text: 'Products'
                            },
                            ticks: {
                                maxRotation: 45,
                                minRotation: 45
                            }
                        }
                    },
                    plugins: {
                        title: {
                            display: true,
                            text: 'Top 10 Products by Movement Volume'
                        },
                        legend: {
                            position: 'top',
                        },
                        tooltip: {
                            mode: 'index',
                            intersect: false,
                        }
                    }
                }
            });
            
            // Export to Excel button handler
            $('#export-excel').click(function() {
                const url = new URL(window.location.href);
                url.searchParams.append('export', 'excel');
                window.location.href = url.toString();
            });
            
            // Print report handler
            $('#print-report').click(function() {
                window.print();
            });
            
            // View details button handler
            $('.view-details').click(function() {
                const productId = $(this).data('product-id');
                const productName = $(this).closest('tr').find('td:first').text();
                const startDate = $('#start_date').val();
                const endDate = $('#end_date').val();
                const godownId = $('#godown_id').length ? $('#godown_id').val() : '';

                // Update modal title with product name
                $('#productDetailsModal .modal-title').html(`<i class="fas fa-box mr-2"></i>Product Movement: ${productName}`);

                // Reset all tabs content and loaded state
                $('#productDetailsTabContent .tab-pane').html(`
                    <div class="text-center p-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="sr-only">Loading...</span>
                        </div>
                    </div>
                `).removeData('loaded');

                // Reset tabs to first tab
                $('#productDetailsTabs a:first').tab('show');

                // Remove previous tab click handlers to prevent multiple bindings
                $('#productDetailsTabs a').off('shown.bs.tab');

                // Show modal
                $('#productDetailsModal').modal('show');

                // Load sales details (default tab)
                loadTabData('sales', productId, startDate, endDate, godownId);
                $('#sales').data('loaded', true);

                // Setup tab click handlers to load data only when tab is clicked
                $('#productDetailsTabs a').on('shown.bs.tab', function(e) {
                    const tab = $(this).attr('href').replace('#', '');
                    // Only load if not already loaded
                    if (!$(`#${tab}`).data('loaded')) {
                        loadTabData(tab, productId, startDate, endDate, godownId);
                        $(`#${tab}`).data('loaded', true);
                    }
                });
            });
            
            // Print details handler
            $('#print-details').click(function() {
                const activeTab = $('#productDetailsTabs a.active').attr('href').replace('#', '');
                const tabContent = $(activeTab).html();
                
                const printWindow = window.open('', '_blank');
                printWindow.document.write(`
                    <html>
                    <head>
                        <title>Product Details</title>
                        <link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/adminlte.min.css') }}">
                        <style>
                            body { padding: 20px; }
                            @media print {
                                table { width: 100%; border-collapse: collapse; }
                                th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                                th { background-color: #f2f2f2; }
                            }
                        </style>
                    </head>
                    <body onload="window.print()">
                        <h2>Product Movement Details</h2>
                        ${tabContent}
                    </body>
                    </html>
                `);
                printWindow.document.close();
            });
            
            function loadTabData(tab, productId, startDate, endDate, godownId) {
                const endpoints = {
                    sales: '{{ route('reports.products.sales.details') }}',
                    returns: '{{ route('reports.products.returns.details') }}',
                    purchases: '{{ route('reports.products.purchases.details') }}',
                    deliveries: '{{ route('reports.products.other-deliveries.details') }}'
                };

                if (!endpoints[tab]) return;

                // Show loading indicator
                $(`#${tab}`).html(`
                    <div class="text-center p-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="sr-only">Loading...</span>
                        </div>
                    </div>
                `);

                // Load data via AJAX
                $.ajax({
                    url: endpoints[tab],
                    type: 'GET',
                    data: {
                        product_id: productId,
                        start_date: startDate || '{{ $filters['start_date'] ?? '' }}',
                        end_date: endDate || '{{ $filters['end_date'] ?? '' }}',
                        godown_id: godownId
                    },
                    success: function(response) {
                        $(`#${tab}`).html(response);
                    },
                    error: function() {
                        $(`#${tab}`).html(`
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-triangle mr-2"></i>
                                Failed to load data. Please try again.
                            </div>
                        `);
                    }
                });
            }
        });
    </script>
@stop
