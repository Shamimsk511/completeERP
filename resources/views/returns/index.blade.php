@extends('layouts.modern-admin')

@section('title', 'All Returns')

@section('page_title', 'Return Management')

@section('page_content')
    <!-- Returns Card -->
    <div class="card border-0 shadow-sm">
        <!-- Header with Quick Filters -->
        <div class="card-header bg-white border-bottom py-3">
            <div class="row align-items-center g-3">
                <!-- Quick Filter Buttons -->
                <div class="col-auto">
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-outline-secondary btn-sm active" data-filter="all">
                            All
                        </button>
                        <button type="button" class="btn btn-outline-warning btn-sm" data-filter="pending">
                            <i class="fas fa-clock me-1"></i>Pending
                        </button>
                        <button type="button" class="btn btn-outline-success btn-sm" data-filter="approved">
                            <i class="fas fa-check-circle me-1"></i>Approved
                        </button>
                        <button type="button" class="btn btn-outline-info btn-sm" data-filter="processed">
                            <i class="fas fa-check-double me-1"></i>Processed
                        </button>
                    </div>
                </div>

                <!-- Date Range (Compact) -->
                <div class="col-auto ms-auto">
                    <form id="filter-form" class="d-flex align-items-center gap-2">
                        <input type="date" class="form-control form-control-sm" id="from_date" name="from_date" style="width: 140px;" title="From Date">
                        <span class="text-muted">to</span>
                        <input type="date" class="form-control form-control-sm" id="to_date" name="to_date" style="width: 140px;" title="To Date">
                        <button type="button" class="btn btn-outline-secondary btn-sm" id="clear-all-filters" title="Clear Filters">
                            <i class="fas fa-times"></i>
                        </button>
                    </form>
                </div>

                <!-- Action Buttons -->
                <div class="col-auto">
                    <button class="btn btn-outline-secondary btn-sm" id="refresh-table" title="Refresh">
                        <i class="fas fa-sync-alt"></i>
                    </button>
                    <a href="{{ route('returns.create') }}" class="btn btn-primary btn-sm" title="New Return">
                        <i class="fas fa-plus"></i> New Return
                    </a>
                </div>
            </div>

            <!-- Hidden filters for DataTables -->
            <input type="hidden" id="status_filter" name="status" value="">
        </div>

        <!-- Table -->
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="returns-table">
                    <thead class="table-light">
                        <tr>
                            <th width="40">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="select-all">
                                </div>
                            </th>
                            <th class="fw-semibold text-uppercase">Return #</th>
                            <th class="fw-semibold text-uppercase">Customer</th>
                            <th class="fw-semibold text-uppercase">Date</th>
                            <th class="fw-semibold text-uppercase">Amount</th>
                            <th class="fw-semibold text-uppercase">Status</th>
                            <th class="fw-semibold text-uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Data will be loaded via AJAX -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@stop

@section('additional_css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastr@2.1.4/build/toastr.min.css">

<style>
    body {
        background-color: #f8f9fa;
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
    }

    .card {
        border-radius: 12px;
        overflow: hidden;
    }

    .card-header {
        border: none;
        padding: 1rem 1.5rem;
    }

    .form-control, .form-select {
        border: 1px solid #dee2e6;
        border-radius: 8px;
        padding: 0.625rem 0.875rem;
        font-size: 0.9rem;
        transition: all 0.2s ease;
    }

    .form-control:focus, .form-select:focus {
        border-color: #6366f1;
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
    }

    .btn {
        border-radius: 8px;
        font-weight: 500;
        padding: 0.5rem 1rem;
        transition: all 0.2s ease;
    }

    .btn-primary {
        background-color: #6366f1;
        border-color: #6366f1;
    }

    .btn-success {
        background-color: #10b981;
        border-color: #10b981;
    }

    /* Quick Filter Buttons */
    .btn-group .btn {
        padding: 0.375rem 0.75rem;
        font-size: 0.8rem;
    }

    .btn-group .btn.active {
        background-color: #374151 !important;
        border-color: #374151 !important;
        color: white !important;
    }

    /* DataTables Search Box Styling */
    .dataTables_filter {
        text-align: right !important;
    }

    .dataTables_filter label {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 8px;
        margin-bottom: 0;
    }

    .dataTables_filter input {
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 8px 12px;
        font-size: 0.9rem;
        width: 250px !important;
        transition: all 0.2s ease;
    }

    .dataTables_filter input:focus {
        border-color: #6366f1;
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
        outline: none;
    }

    .dataTables_length select {
        border: 1px solid #e2e8f0;
        border-radius: 6px;
        padding: 6px 10px;
        font-size: 0.85rem;
    }

    /* Compact Date Inputs */
    .form-control-sm {
        font-size: 0.8rem;
        padding: 0.375rem 0.5rem;
    }

    .table {
        font-size: 0.9rem;
    }

    .table th {
        background-color: #f8fafc;
        border-bottom: 2px solid #e2e8f0;
        color: #374151;
        font-weight: 600;
        font-size: 0.8rem;
        padding: 1rem 0.75rem;
    }

    .table td {
        padding: 1rem 0.75rem;
        vertical-align: middle;
        border-bottom: 1px solid #f1f5f9;
    }

    .table tbody tr:hover {
        background-color: #f8fafc;
    }

    /* Return Number Styling */
    .return-number {
        color: #8b5cf6;
        font-weight: 600;
        text-decoration: none;
    }

    .return-number:hover {
        color: #7c3aed;
        text-decoration: underline;
    }

    .return-date {
        color: #6b7280;
        font-size: 0.8rem;
    }

    /* Customer Info */
    .customer-name {
        color: #3b82f6;
        font-weight: 500;
        text-decoration: none;
    }

    .customer-name:hover {
        color: #1d4ed8;
        text-decoration: underline;
    }

    .customer-phone {
        color: #6b7280;
        font-size: 0.8rem;
    }

    /* Status Badges */
    .status-pending {
        background-color: #f59e0b;
        color: white;
        padding: 0.5rem 0.875rem;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
    }

    .status-approved {
        background-color: #10b981;
        color: white;
        padding: 0.5rem 0.875rem;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
    }

    .status-rejected {
        background-color: #ef4444;
        color: white;
        padding: 0.5rem 0.875rem;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
    }

    .status-processed {
        background-color: #6366f1;
        color: white;
        padding: 0.5rem 0.875rem;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
    }

    /* Amount Display */
    .amount-total {
        font-weight: 600;
        color: #ef4444;
        font-size: 0.95rem;
    }

    /* Action Buttons */
    .action-btn {
        width: 36px;
        height: 36px;
        border-radius: 8px;
        border: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin: 0 2px;
        font-size: 0.875rem;
        transition: all 0.2s ease;
    }

    .action-btn:hover {
        transform: translateY(-2px);
    }

    .btn-view {
        background-color: #0ea5e9;
        color: white;
    }

    .btn-edit {
        background-color: #f59e0b;
        color: white;
    }

    .btn-print {
        background-color: #6b7280;
        color: white;
    }

    .btn-delete {
        background-color: #ef4444;
        color: white;
    }

    .btn-approve {
        background-color: #10b981;
        color: white;
    }

    /* DataTable Customization */
    .dataTables_wrapper .dataTables_length,
    .dataTables_wrapper .dataTables_filter,
    .dataTables_wrapper .dataTables_info,
    .dataTables_wrapper .dataTables_paginate {
        margin: 1rem;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button {
        border-radius: 6px;
        margin: 0 2px;
        padding: 0.5rem 0.75rem;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button.current {
        background: #6366f1;
        border-color: #6366f1;
        color: white !important;
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .table-responsive {
            font-size: 0.8rem;
        }

        .action-btn {
            width: 32px;
            height: 32px;
            font-size: 0.75rem;
        }

        .status-pending, .status-approved, .status-rejected, .status-processed {
            padding: 0.375rem 0.625rem;
            font-size: 0.7rem;
        }
    }
</style>
@stop

@section('additional_js')
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/toastr@2.1.4/build/toastr.min.js"></script>

<script>
$(document).ready(function() {
    // Configuration
    const csrfToken = $('meta[name="csrf-token"]').attr('content') || '{{ csrf_token() }}';

    // Initialize DataTable
    const table = $('#returns-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route('returns.data') }}',
            data: function(d) {
                d.status = $('#status_filter').val();
                d.from_date = $('#from_date').val();
                d.to_date = $('#to_date').val();
            }
        },
        columns: [
            {
                data: null,
                orderable: false,
                searchable: false,
                width: '40px',
                render: function(data, type, row) {
                    return `<div class="form-check">
                        <input class="form-check-input return-checkbox" type="checkbox" value="${row.id}">
                    </div>`;
                }
            },
            {
                data: 'return_number',
                name: 'return_number',
                render: function(data, type, row) {
                    return `<div>
                        <a href="/returns/${row.id}" class="return-number">${data}</a>
                        <div class="return-date">${row.return_date}</div>
                    </div>`;
                }
            },
            {
                data: 'customer',
                name: 'customer.name',
                render: function(data, type, row) {
                    if (!data) return '<span class="text-muted">N/A</span>';
                    return `<div>
                        <a href="/customers/${row.customer_id}" class="customer-name">${data.name || data}</a>
                        ${data.phone ? `<div class="customer-phone">${data.phone}</div>` : ''}
                    </div>`;
                }
            },
            {
                data: 'return_date',
                name: 'return_date',
                render: function(data, type, row) {
                    return `<div class="return-date">${data}</div>`;
                }
            },
            {
                data: 'total',
                name: 'total',
                render: function(data, type, row) {
                    return `<div class="amount-total">৳${parseFloat(data).toLocaleString('en-BD', {minimumFractionDigits: 2})}</div>`;
                }
            },
            {
                data: 'status',
                name: 'status',
                render: function(data, type, row) {
                    const statusClasses = {
                        'pending': 'status-pending',
                        'approved': 'status-approved',
                        'rejected': 'status-rejected',
                        'processed': 'status-processed'
                    };
                    const statusIcons = {
                        'pending': 'fa-clock',
                        'approved': 'fa-check-circle',
                        'rejected': 'fa-times-circle',
                        'processed': 'fa-check-double'
                    };
                    const statusClass = statusClasses[data] || 'status-pending';
                    const statusIcon = statusIcons[data] || 'fa-question-circle';
                    return `<span class="${statusClass}"><i class="fas ${statusIcon} me-1"></i>${data.toUpperCase()}</span>`;
                }
            },
            {
                data: null,
                name: 'actions',
                orderable: false,
                searchable: false,
                render: function(data, type, row) {
                    let html = '<div class="d-flex">';

                    // View button
                    html += `<a href="/returns/${row.id}" class="action-btn btn-view" title="View">
                        <i class="fas fa-eye"></i>
                    </a>`;

                    // Edit button (only if not processed)
                    if (row.status !== 'processed') {
                        html += `<a href="/returns/${row.id}/edit" class="action-btn btn-edit" title="Edit">
                            <i class="fas fa-edit"></i>
                        </a>`;
                    }

                    // Print button
                    html += `<a href="/returns/${row.id}/print" target="_blank" class="action-btn btn-print" title="Print">
                        <i class="fas fa-print"></i>
                    </a>`;

                    // Approve button (only if pending)
                    if (row.status === 'pending') {
                        html += `<button class="action-btn btn-approve approve-return" data-id="${row.id}" title="Approve">
                            <i class="fas fa-check"></i>
                        </button>`;
                    }

                    // Delete button (only if not processed)
                    if (row.status !== 'processed') {
                        html += `<button class="action-btn btn-delete delete-return" data-id="${row.id}" title="Delete">
                            <i class="fas fa-trash"></i>
                        </button>`;
                    }

                    html += '</div>';
                    return html;
                }
            }
        ],
        order: [[1, 'desc']],
        pageLength: 15,
        responsive: true,
        language: {
            processing: '<div class="text-center"><div class="spinner-border text-primary" role="status"></div></div>',
            emptyTable: 'No returns found',
            info: 'Showing _START_ to _END_ of _TOTAL_ returns',
            infoEmpty: 'No returns to display',
            search: '',
            searchPlaceholder: 'Search returns...',
            lengthMenu: 'Show _MENU_ entries',
            paginate: {
                first: 'First',
                last: 'Last',
                next: 'Next',
                previous: 'Previous'
            }
        },
        dom: '<"row align-items-center mb-3"<"col-sm-6"l><"col-sm-6"f>>rt<"row align-items-center"<"col-sm-5"i><"col-sm-7"p>>'
    });

    // Event Handlers

    // Quick filter buttons
    $('[data-filter]').on('click', function() {
        const filter = $(this).data('filter');

        // Update button states
        $('[data-filter]').removeClass('active btn-secondary').addClass('btn-outline-secondary');
        $(this).removeClass('btn-outline-secondary').addClass('active btn-secondary');

        // Set hidden filter based on button clicked
        if (filter === 'all') {
            $('#status_filter').val('');
        } else {
            $('#status_filter').val(filter);
        }

        table.draw();
    });

    // Date filter changes
    $('#from_date, #to_date').on('change', function() {
        table.draw();
    });

    // Clear all filters
    $('#clear-all-filters').on('click', function() {
        $('#filter-form')[0].reset();
        $('#status_filter').val('');
        $('[data-filter]').removeClass('active btn-secondary').addClass('btn-outline-secondary');
        $('[data-filter="all"]').removeClass('btn-outline-secondary').addClass('active btn-secondary');
        table.draw();
        toastr.info('Filters cleared');
    });

    // Select all checkbox
    $('#select-all').on('change', function() {
        $('.return-checkbox').prop('checked', $(this).prop('checked'));
    });

    // Individual checkbox
    $(document).on('change', '.return-checkbox', function() {
        const totalCheckboxes = $('.return-checkbox').length;
        const checkedCheckboxes = $('.return-checkbox:checked').length;
        $('#select-all').prop('checked', checkedCheckboxes === totalCheckboxes);
    });

    // Approve return
    $(document).on('click', '.approve-return', function() {
        const returnId = $(this).data('id');

        Swal.fire({
            title: 'Approve Return?',
            text: 'This will approve the return and adjust stock accordingly.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#10b981',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Yes, approve it'
        }).then((result) => {
            if (result.isConfirmed) {
                approveReturn(returnId);
            }
        });
    });

    // Delete return
    $(document).on('click', '.delete-return', function() {
        const returnId = $(this).data('id');

        Swal.fire({
            title: 'Delete Return?',
            text: 'This action cannot be undone.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Yes, delete it'
        }).then((result) => {
            if (result.isConfirmed) {
                deleteReturn(returnId);
            }
        });
    });

    // Refresh table
    $('#refresh-table').on('click', function() {
        table.draw();
        toastr.success('Table refreshed');
    });

    // Helper Functions
    function approveReturn(returnId) {
        $.ajax({
            url: `/returns/${returnId}/approve`,
            type: 'POST',
            data: {
                _token: csrfToken
            },
            success: function(response) {
                if (response.success) {
                    toastr.success('Return approved successfully');
                    table.draw(false);
                } else {
                    toastr.error(response.message || 'Failed to approve return');
                }
            },
            error: function(xhr) {
                toastr.error('Error: ' + (xhr.responseJSON?.message || 'An error occurred'));
            }
        });
    }

    function deleteReturn(returnId) {
        $.ajax({
            url: `/returns/${returnId}`,
            type: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': csrfToken
            },
            success: function(response) {
                if (response.success) {
                    toastr.success('Return deleted successfully');
                    table.draw(false);
                } else {
                    toastr.error(response.message || 'Failed to delete return');
                }
            },
            error: function(xhr) {
                toastr.error('Error: ' + (xhr.responseJSON?.message || 'An error occurred'));
            }
        });
    }

    // Initialize Toastr
    toastr.options = {
        closeButton: true,
        debug: false,
        newestOnTop: true,
        progressBar: true,
        positionClass: 'toast-top-right',
        preventDuplicates: true,
        showDuration: 300,
        hideDuration: 1000,
        timeOut: 5000,
        extendedTimeOut: 1000
    };

});
</script>
@stop
