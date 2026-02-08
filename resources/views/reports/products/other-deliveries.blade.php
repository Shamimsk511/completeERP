@extends('adminlte::page')

@section('title', 'Other Deliveries Report')

@section('content_header')
    <h1>Other Deliveries Report</h1>
@stop

@section('content')
    @include('reports.products.components.filters', [
        'action' => route('reports.products.other-deliveries'),
        'filters' => $filters,
        'products' => $products,
        'categories' => $categories,
        'companies' => $companies,
        'godowns' => $godowns
    ])
    
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Other Deliveries Summary</h3>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                    <i class="fas fa-minus"></i>
                </button>
            </div>
        </div>
        <div class="card-body">
            
            
            <div class="table-responsive">
                <table id="deliveries-report-table" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Category</th>
                            <th>Company</th>
                            <th>Quantity</th>
                            <th>Deliveries Count</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($deliveriesData as $item)
                        <tr>
                            <td>{{ $item['product']->name }}</td>
                            <td>{{ $item['product']->category->name ?? 'N/A' }}</td>
                            <td>{{ $item['product']->company->name ?? 'N/A' }}</td>
                            <td>{{ number_format($item['quantity']) }}</td>
                            <td>{{ $item['deliveries'] }}</td>
                            <td>
                                <button type="button" class="btn btn-sm btn-info view-details" 
                                        data-product-id="{{ $item['product']->id }}">
                                    <i class="fas fa-eye"></i> Details
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="row">
                <div class="col-md-12 mb-4">
                    <canvas id="deliveriesChart" height="120"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Delivery Details Modal -->
    <div class="modal fade" id="deliveryDetailsModal">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header bg-danger">
                    <h4 class="modal-title text-white"><i class="fas fa-truck mr-2"></i>Delivery Details</h4>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="deliveryDetailsContent">
                    <div class="text-center p-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="sr-only">Loading...</span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
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
            $('.select2').select2({
                theme: 'bootstrap4'
            });
            
            $('#deliveries-report-table').DataTable({
                "paging": true,
                "lengthChange": true,
                "searching": true,
                "ordering": true,
                "info": true,
                "autoWidth": false,
                "responsive": true,
            });
            
            // Chart initialization
            const ctx = document.getElementById('deliveriesChart').getContext('2d');
            const chart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: {!! json_encode($deliveriesData->pluck('product.name')->take(10)) !!},
                    datasets: [{
                        label: 'Quantity Delivered',
                        data: {!! json_encode($deliveriesData->pluck('quantity')->take(10)) !!},
                        backgroundColor: 'rgba(108, 117, 125, 0.5)',
                        borderColor: 'rgba(108, 117, 125, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true
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
            
            // View details button handler - use event delegation for DataTable pagination
            $(document).on('click', '.view-details', function() {
                const productId = $(this).data('product-id');
                const productName = $(this).closest('tr').find('td:first').text();
                const startDate = $('#start_date').val();
                const endDate = $('#end_date').val();

                // Update modal title
                $('#deliveryDetailsModal .modal-title').html(`<i class="fas fa-truck mr-2"></i>Delivery Details: ${productName}`);

                // Show loading
                $('#deliveryDetailsContent').html(`
                    <div class="text-center p-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="sr-only">Loading...</span>
                        </div>
                    </div>
                `);

                $('#deliveryDetailsModal').modal('show');

                const godownId = $('#godown_id').length ? $('#godown_id').val() : '';
                $.ajax({
                    url: '{{ route("reports.products.other-deliveries.details") }}',
                    type: 'GET',
                    data: {
                        product_id: productId,
                        start_date: startDate,
                        end_date: endDate,
                        godown_id: godownId
                    },
                    success: function(response) {
                        $('#deliveryDetailsContent').html(response);
                    },
                    error: function() {
                        $('#deliveryDetailsContent').html(`
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-triangle mr-2"></i>
                                Failed to load data. Please try again.
                            </div>
                        `);
                    }
                });
            });
        });
    </script>
@stop
