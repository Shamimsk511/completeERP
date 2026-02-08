@extends('layouts.modern-admin')

@section('title', 'Stock Adjustment')

@section('page_title', 'Stock Adjustment')

@section('header_actions')
    <button type="button" class="btn modern-btn modern-btn-outline" onclick="printStockCount()">
        <i class="fas fa-print"></i> Print Count Sheet
    </button>
    <a href="{{ route('products.index') }}" class="btn modern-btn modern-btn-outline">
        <i class="fas fa-arrow-left"></i> Back to Products
    </a>
@stop

@section('page_content')
    <div class="card modern-card">
        <div class="card-header modern-header">
            <div class="header-content">
                <div class="header-title">
                    <i class="fas fa-balance-scale header-icon"></i>
                    <h3 class="card-title">Physical Stock Count</h3>
                </div>
            </div>
        </div>

        <div class="card-body modern-card-body">
            <!-- Instructions -->
            <div class="alert alert-info mb-4">
                <i class="fas fa-info-circle mr-2"></i>
                <strong>Instructions:</strong> Enter the physical count for each product. The system will calculate the difference automatically.
                Changes are saved to your session until you click "Save All Adjustments". You can navigate between pages without losing your entered data.
            </div>

            <!-- Filter Row -->
            <div class="filter-section mb-4">
                <div class="row">
                    <div class="col-md-3">
                        <label for="companyFilter">Filter by Company</label>
                        <select id="companyFilter" class="form-control">
                            <option value="">All Companies</option>
                            @foreach($companies as $company)
                                <option value="{{ $company->id }}">{{ $company->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="categoryFilter">Filter by Category</label>
                        <select id="categoryFilter" class="form-control">
                            <option value="">All Categories</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @if($godowns->isNotEmpty())
                        <div class="col-md-3">
                            <label for="godownFilter">Filter by Godown</label>
                            <select id="godownFilter" class="form-control">
                                <option value="">All Godowns</option>
                                @foreach($godowns as $godown)
                                    <option value="{{ $godown->id }}">{{ $godown->name }}{{ $godown->location ? ' - ' . $godown->location : '' }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                    <div class="{{ $godowns->isNotEmpty() ? 'col-md-3' : 'col-md-6' }}">
                        <label for="nameFilter">Search by Name</label>
                        <input type="text" id="nameFilter" class="form-control" placeholder="Search product...">
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-md-12 d-flex align-items-end">
                        <button type="button" class="btn modern-btn modern-btn-primary mr-2" onclick="applyFilters()">
                            <i class="fas fa-filter"></i> Apply
                        </button>
                        <button type="button" class="btn modern-btn modern-btn-outline" onclick="resetFilters()">
                            <i class="fas fa-undo"></i> Reset
                        </button>
                    </div>
                </div>
            </div>

            <!-- Summary Cards -->
            <div class="summary-row mb-4" id="summaryCards" style="display: none;">
                <div class="row">
                    <div class="col-md-3">
                        <div class="summary-card summary-info">
                            <div class="summary-icon"><i class="fas fa-edit"></i></div>
                            <div class="summary-content">
                                <div class="summary-label">Products Edited</div>
                                <div class="summary-value" id="editedCount">0</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="summary-card summary-success">
                            <div class="summary-icon"><i class="fas fa-plus"></i></div>
                            <div class="summary-content">
                                <div class="summary-label">Total Added</div>
                                <div class="summary-value" id="totalAdded">0.00</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="summary-card summary-danger">
                            <div class="summary-icon"><i class="fas fa-minus"></i></div>
                            <div class="summary-content">
                                <div class="summary-label">Total Removed</div>
                                <div class="summary-value" id="totalRemoved">0.00</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="summary-card summary-warning">
                            <div class="summary-icon"><i class="fas fa-balance-scale"></i></div>
                            <div class="summary-content">
                                <div class="summary-label">Net Change</div>
                                <div class="summary-value" id="netChange">0.00</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- DataTable -->
            <div class="table-responsive">
                <table class="table table-hover" id="stockAdjustmentTable">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Product Name</th>
                            <th>Company</th>
                            <th>Category</th>
                            <th class="text-right">System Stock</th>
                            <th class="text-center">Physical Count</th>
                            <th class="text-right">Difference</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>

            <!-- Save Button -->
            <div class="mt-4 text-right">
                <button type="button" class="btn modern-btn modern-btn-success btn-lg" id="saveAdjustmentsBtn" onclick="saveAdjustments()" disabled>
                    <i class="fas fa-save"></i> Save All Adjustments (<span id="adjustmentCount">0</span>)
                </button>
            </div>
        </div>
    </div>
@stop

@section('additional_css')
    <link href="{{ asset('css/modern-admin.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
    <style>
        .filter-section {
            background: linear-gradient(135deg, rgba(99, 102, 241, 0.02) 0%, rgba(139, 92, 246, 0.02) 100%);
            border: 1px solid rgba(99, 102, 241, 0.1);
            border-radius: 12px;
            padding: 20px;
        }

        .summary-row .summary-card {
            background: white;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            padding: 16px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .summary-card.summary-info { border-left: 4px solid var(--app-primary, #3b82f6); }
        .summary-card.summary-success { border-left: 4px solid #10b981; }
        .summary-card.summary-danger { border-left: 4px solid #ef4444; }
        .summary-card.summary-warning { border-left: 4px solid #f59e0b; }

        .summary-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            color: white;
        }

        .summary-info .summary-icon {
            background: var(--app-topbar-gradient, linear-gradient(135deg, var(--app-primary-dark, #1d4ed8), var(--app-primary, #3b82f6)));
        }
        .summary-success .summary-icon { background: #10b981; }
        .summary-danger .summary-icon { background: #ef4444; }
        .summary-warning .summary-icon { background: #f59e0b; }

        .summary-label {
            font-size: 12px;
            color: #6b7280;
            margin-bottom: 2px;
        }

        .summary-value {
            font-size: 20px;
            font-weight: 700;
            color: #374151;
        }

        .stock-input {
            width: 120px;
            text-align: right;
        }

        .stock-input:focus {
            border-color: #6366f1;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
        }

        .stock-input.changed {
            background-color: #fef3c7;
            border-color: #f59e0b;
        }

        .difference-positive {
            color: #10b981;
            font-weight: 600;
        }

        .difference-negative {
            color: #ef4444;
            font-weight: 600;
        }

        .difference-zero {
            color: #6b7280;
        }

        #stockAdjustmentTable thead th {
            background: linear-gradient(135deg, rgba(99, 102, 241, 0.05) 0%, rgba(139, 92, 246, 0.05) 100%);
            font-weight: 600;
            font-size: 13px;
        }

        #stockAdjustmentTable tbody td {
            vertical-align: middle;
        }

        @media print {
            .filter-section, .summary-row, #saveAdjustmentsBtn, .dataTables_filter, .dataTables_length, .dataTables_paginate, .dataTables_info {
                display: none !important;
            }
        }
    </style>
@stop

@section('additional_js')
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>
    <script src="{{ asset('js/modern-admin.js') }}"></script>
    <script>
        // Store adjustments in session storage
        let adjustments = JSON.parse(sessionStorage.getItem('stockAdjustments') || '{}');
        let dataTable;

        $(document).ready(function() {
            // Initialize DataTable
            dataTable = $('#stockAdjustmentTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('products.stock-adjustment.data') }}",
                    data: function(d) {
                        d.company_id = $('#companyFilter').val();
                        d.category_id = $('#categoryFilter').val();
                        d.godown_id = $('#godownFilter').length ? $('#godownFilter').val() : '';
                        d.name = $('#nameFilter').val();
                    }
                },
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                    { data: 'name', name: 'name' },
                    { data: 'company_name', name: 'company_name' },
                    { data: 'category_name', name: 'category_name' },
                    { data: 'current_stock', name: 'current_stock', className: 'text-right', render: function(data) {
                        return parseFloat(data).toFixed(2);
                    }},
                    { data: 'stock_input', name: 'stock_input', orderable: false, searchable: false, className: 'text-center' },
                    { data: 'difference', name: 'difference', orderable: false, searchable: false, className: 'text-right' }
                ],
                order: [[1, 'asc']],
                pageLength: 25,
                drawCallback: function() {
                    restoreInputValues();
                }
            });

            // Handle stock input changes
            $(document).on('input', '.stock-input', function() {
                const productId = $(this).data('product-id');
                const systemStock = parseFloat($(this).data('system-stock'));
                const physicalCount = parseFloat($(this).val()) || 0;
                const difference = physicalCount - systemStock;

                // Update difference display
                const diffSpan = $(`.difference-value[data-product-id="${productId}"]`);
                if ($(this).val() === '') {
                    diffSpan.text('-').removeClass('difference-positive difference-negative').addClass('difference-zero');
                    $(this).removeClass('changed');
                    delete adjustments[productId];
                } else {
                    const diffText = (difference >= 0 ? '+' : '') + difference.toFixed(2);
                    diffSpan.text(diffText);
                    diffSpan.removeClass('difference-positive difference-negative difference-zero');
                    if (difference > 0) {
                        diffSpan.addClass('difference-positive');
                    } else if (difference < 0) {
                        diffSpan.addClass('difference-negative');
                    } else {
                        diffSpan.addClass('difference-zero');
                    }
                    $(this).addClass('changed');

                    // Store adjustment
                    adjustments[productId] = {
                        product_id: productId,
                        system_stock: systemStock,
                        physical_count: physicalCount,
                        difference: difference
                    };
                }

                // Save to session storage
                sessionStorage.setItem('stockAdjustments', JSON.stringify(adjustments));

                // Update summary
                updateSummary();
            });
        });

        function restoreInputValues() {
            $('.stock-input').each(function() {
                const productId = $(this).data('product-id');
                if (adjustments[productId]) {
                    $(this).val(adjustments[productId].physical_count);
                    $(this).addClass('changed');

                    const difference = adjustments[productId].difference;
                    const diffSpan = $(`.difference-value[data-product-id="${productId}"]`);
                    const diffText = (difference >= 0 ? '+' : '') + difference.toFixed(2);
                    diffSpan.text(diffText);
                    diffSpan.removeClass('difference-positive difference-negative difference-zero');
                    if (difference > 0) {
                        diffSpan.addClass('difference-positive');
                    } else if (difference < 0) {
                        diffSpan.addClass('difference-negative');
                    } else {
                        diffSpan.addClass('difference-zero');
                    }
                }
            });
        }

        function updateSummary() {
            const keys = Object.keys(adjustments);
            const count = keys.length;

            let totalAdded = 0;
            let totalRemoved = 0;

            keys.forEach(key => {
                const diff = adjustments[key].difference;
                if (diff > 0) {
                    totalAdded += diff;
                } else {
                    totalRemoved += Math.abs(diff);
                }
            });

            const netChange = totalAdded - totalRemoved;

            $('#editedCount').text(count);
            $('#totalAdded').text(totalAdded.toFixed(2));
            $('#totalRemoved').text(totalRemoved.toFixed(2));
            $('#netChange').text((netChange >= 0 ? '+' : '') + netChange.toFixed(2));
            $('#adjustmentCount').text(count);

            if (count > 0) {
                $('#summaryCards').show();
                $('#saveAdjustmentsBtn').prop('disabled', false);
            } else {
                $('#summaryCards').hide();
                $('#saveAdjustmentsBtn').prop('disabled', true);
            }
        }

        function applyFilters() {
            dataTable.ajax.reload();
        }

        function resetFilters() {
            $('#companyFilter').val('');
            $('#categoryFilter').val('');
            $('#godownFilter').val('');
            $('#nameFilter').val('');
            dataTable.ajax.reload();
        }

        function saveAdjustments() {
            const adjustmentList = Object.values(adjustments);

            if (adjustmentList.length === 0) {
                ModernAdmin.showAlert('No adjustments to save.', 'warning');
                return;
            }

            if (!confirm('Are you sure you want to save ' + adjustmentList.length + ' stock adjustments? This action cannot be undone.')) {
                return;
            }

            $.ajax({
                url: "{{ route('products.stock-adjustment.save') }}",
                method: 'POST',
                data: {
                    _token: "{{ csrf_token() }}",
                    godown_id: $('#godownFilter').length ? $('#godownFilter').val() : '',
                    adjustments: adjustmentList
                },
                success: function(response) {
                    if (response.success) {
                        // Clear session storage
                        sessionStorage.removeItem('stockAdjustments');
                        adjustments = {};

                        // Show success message with summary
                        const summary = response.summary;
                        ModernAdmin.showAlert(
                            `Adjusted ${summary.products_adjusted} products. Added: ${summary.total_added.toFixed(2)}, Removed: ${summary.total_removed.toFixed(2)}`,
                            'success',
                            5000
                        );

                        // Reload table
                        dataTable.ajax.reload();
                        updateSummary();
                    }
                },
                error: function(xhr) {
                    ModernAdmin.showAlert('Failed to save adjustments. Please try again.', 'danger');
                }
            });
        }

        function printStockCount() {
            const companyId = $('#companyFilter').val();
            const categoryId = $('#categoryFilter').val();
            const godownId = $('#godownFilter').length ? $('#godownFilter').val() : '';

            let url = "{{ route('products.stock-adjustment.print') }}";
            const params = [];
            if (companyId) params.push('company_id=' + companyId);
            if (categoryId) params.push('category_id=' + categoryId);
            if (godownId) params.push('godown_id=' + godownId);
            if (params.length > 0) url += '?' + params.join('&');

            window.open(url, '_blank');
        }

        // Initialize summary on page load
        updateSummary();

        // Reset adjustments when godown changes to avoid mixing stock locations
        $('#godownFilter').on('change', function() {
            if (Object.keys(adjustments).length > 0) {
                adjustments = {};
                sessionStorage.removeItem('stockAdjustments');
                updateSummary();
            }
        });
    </script>
@stop
