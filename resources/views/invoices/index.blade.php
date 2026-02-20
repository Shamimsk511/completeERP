@extends('layouts.modern-admin')

@section('title', 'All Invoices')

@section('page_title', 'Invoice Management')

@section('page_content')
    <!-- Invoices Card -->
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
                        <button type="button" class="btn btn-outline-danger btn-sm" data-filter="due">
                            <i class="fas fa-exclamation-circle me-1"></i>Due
                        </button>
                        <button type="button" class="btn btn-outline-warning btn-sm" data-filter="pending">
                            <i class="fas fa-clock me-1"></i>Pending Delivery
                        </button>
                        <button type="button" class="btn btn-outline-success btn-sm" data-filter="paid">
                            <i class="fas fa-check-circle me-1"></i>Paid
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
                    @can('trash-view')
                    <a href="{{ route('trash.index', ['type' => 'invoices']) }}" class="btn btn-outline-danger btn-sm" title="Invoice Trash">
                        <i class="fas fa-trash-restore"></i>
                    </a>
                    @endcan
                    <button class="btn btn-outline-secondary btn-sm" id="refresh-table" title="Refresh">
                        <i class="fas fa-sync-alt"></i>
                    </button>
                    <button class="btn btn-outline-secondary btn-sm" id="export-table" title="Export">
                        <i class="fas fa-download"></i>
                    </button>
                </div>
            </div>

            <!-- Hidden filters for DataTables -->
            <input type="hidden" id="payment_status" name="payment_status" value="">
            <input type="hidden" id="delivery_status" name="delivery_status" value="">
        </div>

        <!-- Table -->
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="invoices-table">
                    <thead class="table-light">
                        <tr>
                            <th width="40">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="select-all">
                                </div>
                            </th>
                            <th class="fw-semibold text-uppercase">Invoice #</th>
                            <th class="fw-semibold text-uppercase">Customer</th>
                            <th class="fw-semibold text-uppercase">Type</th>
                            <th class="fw-semibold text-uppercase">Amount</th>
                            <th class="fw-semibold text-uppercase">Payment</th>
                            <th class="fw-semibold text-uppercase">Delivery</th>
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

    <!-- Delivery Modal -->
    <div class="modal fade" id="deliveryModal" tabindex="-1" role="dialog" aria-labelledby="deliveryModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="deliveryModalLabel">
                        <i class="fas fa-truck"></i> Mark Invoice as Delivered
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="deliveryForm">
                    @csrf
                    <input type="hidden" name="invoice_id" id="modal_invoice_id">
                    <div class="modal-body">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            This will create a challan for all remaining items and adjust stock accordingly.
                            Delivery info is optional - leave blank if not applicable.
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Vehicle Number</label>
                                    <input type="text" name="vehicle_number" class="form-control" placeholder="e.g., DHA-123456">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Driver Name</label>
                                    <input type="text" name="driver_name" class="form-control" placeholder="Driver's name">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Driver Phone</label>
                                    <input type="text" name="driver_phone" class="form-control" placeholder="Driver's phone">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Receiver Name</label>
                                    <input type="text" name="receiver_name" id="modal_receiver_name" class="form-control" placeholder="Receiver's name">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Receiver Phone</label>
                                    <input type="text" name="receiver_phone" id="modal_receiver_phone" class="form-control" placeholder="Receiver's phone">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Shipping Address</label>
                                    <input type="text" name="shipping_address" id="modal_shipping_address" class="form-control" placeholder="Delivery address">
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Notes</label>
                            <textarea name="notes" class="form-control" rows="2" placeholder="Any delivery notes..."></textarea>
                        </div>

                        <!-- Items to be delivered -->
                        <div class="card mt-3">
                            <div class="card-header bg-light">
                                <strong><i class="fas fa-boxes"></i> Items to be Delivered</strong>
                            </div>
                            <div class="card-body p-0">
                                <table class="table table-sm mb-0">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>Product</th>
                                            <th>Invoice Qty</th>
                                            <th>Delivered</th>
                                            <th>Remaining</th>
                                        </tr>
                                    </thead>
                                    <tbody id="modal_items_body">
                                        <tr>
                                            <td colspan="4" class="text-center">
                                                <i class="fas fa-spinner fa-spin"></i> Loading items...
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">
                            <i class="fas fa-times"></i> Cancel
                        </button>
                        <button type="submit" class="btn btn-success" id="submitDeliveryBtn">
                            <i class="fas fa-check"></i> Confirm Delivery
                        </button>
                    </div>
                </form>
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

    /* Invoice Number Styling */
    .invoice-number {
        color: #3b82f6;
        font-weight: 600;
        text-decoration: none;
    }

    .invoice-number:hover {
        color: #1d4ed8;
        text-decoration: underline;
    }

    .invoice-date {
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

    /* Type Badges */
    .badge-tiles {
        background-color: #3b82f6;
        color: white;
        padding: 0.5rem 0.875rem;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
    }

    .badge-other {
        background-color: #6b7280;
        color: white;
        padding: 0.5rem 0.875rem;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
    }

    /* Payment Status */
    .payment-paid {
        background-color: #10b981;
        color: white;
        padding: 0.5rem 0.875rem;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
    }

    .payment-partial {
        background-color: #3b82f6;
        color: white;
        padding: 0.5rem 0.875rem;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
    }

    .payment-due {
        background-color: #ef4444;
        color: white;
        padding: 0.5rem 0.875rem;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
    }

    .mark-paid-btn {
        background-color: #10b981;
        color: white;
        border: none;
        padding: 0.25rem 0.75rem;
        border-radius: 15px;
        font-size: 0.75rem;
        font-weight: 500;
        cursor: pointer;
        margin-top: 0.25rem;
    }

    .mark-paid-btn:hover {
        background-color: #059669;
    }

    /* Delivery Status */
    .delivery-pending {
        background-color: #f59e0b;
        color: white;
        padding: 0.5rem 0.875rem;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
    }

    .delivery-delivered {
        background-color: #10b981;
        color: white;
        padding: 0.5rem 0.875rem;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
    }

    .delivery-status-select {
        border: none;
        background: transparent;
        font-size: 0.8rem;
        padding: 0.25rem;
        width: 100px;
        margin-top: 0.25rem;
    }

    /* Amount Display */
    .amount-total {
        font-weight: 600;
        color: #111827;
        font-size: 0.95rem;
    }

    .amount-paid {
        color: #10b981;
        font-size: 0.8rem;
    }

    .amount-due {
        color: #ef4444;
        font-size: 0.8rem;
    }

    .amount-outstanding {
        color: #6b7280;
        font-size: 0.8rem;
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

    .btn-truck {
        background-color: #3b82f6;
        color: white;
    }

    .btn-payment {
        background-color: #10b981;
        color: white;
    }

    .btn-delete {
        background-color: #ef4444;
        color: white;
    }

    .btn-delete:disabled {
        background-color: #d1d5db;
        color: #6b7280;
        cursor: not-allowed;
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
        
        .badge-tiles, .badge-other, .payment-paid, .payment-partial, 
        .payment-due, .delivery-pending, .delivery-delivered {
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
    const table = $('#invoices-table').DataTable({
        processing: true,
        serverSide: true,
        deferRender: true,
        ajax: {
            url: '{{ route('invoices.data') }}',
            data: function(d) {
                d.payment_status = $('#payment_status').val();
                d.delivery_status = $('#delivery_status').val();
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
                render: function(data) {
                    return `<div class="form-check">
                        <input class="form-check-input invoice-checkbox" type="checkbox" value="${data.DT_RowData['invoice-id']}">
                    </div>`;
                }
            },
            {
                data: 'invoice_number',
                name: 'invoice_number',
                render: function(data, type, row) {
                    return `<div>
                        <a href="${row.invoice_url}" class="invoice-number">${data}</a>
                        <div class="invoice-date">${row.invoice_date}</div>
                    </div>`;
                }
            },
            {
                data: null,
                name: 'customer_id',
                render: function(data, type, row) {
                    return `<div>
                        <a href="${row.customer_url}" class="customer-name">${row.customer_name}</a>
                        <div class="customer-phone">${row.customer_phone}</div>
                        <div class="customer-phone" style="font-size: 0.75rem; color: #9ca3af;">${row.customer_address}</div>
                    </div>`;
                }
            },
            {
                data: 'invoice_type',
                name: 'invoice_type',
                render: function(data) {
                    const type = (data || '').toLowerCase();
                    const badgeClass = type === 'tiles' ? 'badge-tiles' : 'badge-other';
                    const typeText = type === 'tiles' ? 'TILES' : 'OTHER';
                    return `<span class="${badgeClass}">${typeText}</span>`;
                }
            },
            {
                data: null,
                name: 'total',
                render: function(data, type, row) {
                    const dueValue = row.due_is_negative ? `<span class="text-success">-${row.due}</span>` : row.due;
                    const outstandingValue = row.customer_outstanding_is_negative
                        ? `<span class="text-success">-${row.customer_outstanding}</span>`
                        : row.customer_outstanding;
                    return `<div>
                        <div class="amount-total">৳${row.total}</div>
                        <div class="amount-paid">Paid: ৳${row.paid}</div>
                        <div class="amount-due">Due: ৳${dueValue}</div>
                        <div class="amount-outstanding">Outstanding: ${outstandingValue}</div>
                    </div>`;
                }
            },
            {
                data: 'payment_status',
                name: 'payment_status',
                render: function(data, type, row) {
                    const status = (data || '').toLowerCase();
                    let statusClass = 'payment-due';
                    let statusDisplay = 'DUE';

                    if (status === 'paid') {
                        statusClass = 'payment-paid';
                        statusDisplay = 'PAID';
                    } else if (status === 'partial') {
                        statusClass = 'payment-partial';
                        statusDisplay = 'PARTIAL';
                    }

                    let html = `<span class="${statusClass}">${statusDisplay}</span>`;

                    if (row.overpaid_amount) {
                        html += `<br><small class="text-success"><i class="fas fa-arrow-up"></i> Overpaid: ${row.overpaid_amount}</small>`;
                    }

                    if (row.can_mark_paid) {
                        const invoiceId = row.DT_RowData['invoice-id'];
                        html += `<br><button class="mark-paid-btn" data-invoice-id="${invoiceId}" data-due-amount="${row.mark_paid_due}">Paid</button>`;
                    }

                    return html;
                }
            },
            {
                data: 'delivery_status',
                name: 'delivery_status',
                render: function(data, type, row) {
                    const status = (data || '').toLowerCase();
                    let statusClass = 'delivery-pending';
                    let statusDisplay = status ? status.toUpperCase() : 'PENDING';

                    if (status === 'delivered') {
                        statusClass = 'delivery-delivered';
                    }

                    let html = `<span class="${statusClass}">${statusDisplay}</span>`;

                    if (status !== 'delivered') {
                        const invoiceId = row.DT_RowData['invoice-id'];
                        html += `<br><select class="delivery-status-select" data-invoice-id="${invoiceId}">
                            <option value="pending"${status === 'pending' ? ' selected' : ''}>Pending</option>
                            <option value="partial"${status === 'partial' ? ' selected' : ''}>Partial</option>
                            <option value="delivered"${status === 'delivered' ? ' selected' : ''}>Delivered</option>
                        </select>`;
                    }

                    return html;
                }
            },
            {
    data: 'actions',
    name: 'actions',
    orderable: false,
    searchable: false,
    render: function(data, type, row) {
        const invoiceId = row.DT_RowData['invoice-id'];
        const isDelivered = row.delivery_status === 'delivered';
        const isPartial = row.delivery_status === 'partial';
        const isPaymentPaid = row.payment_status === 'paid';

        let html = '<div class="d-flex">';

        html += `<a href="/invoices/${invoiceId}" class="action-btn btn-view" title="View">
            <i class="fas fa-eye"></i>
        </a>`;

        if (!isDelivered) {
            html += `<a href="/invoices/${invoiceId}/edit" class="action-btn btn-edit" title="Edit">
                <i class="fas fa-edit"></i>
            </a>`;
        }

        html += `<a href="/invoices/${invoiceId}/print" target="_blank" class="action-btn btn-print" title="Print">
            <i class="fas fa-print"></i>
        </a>`;

        if (!isDelivered) {
            html += `<a href="/challans/create?invoice_id=${invoiceId}" class="action-btn btn-truck" title="Create Challan">
                <i class="fas fa-truck"></i>
            </a>`;
        }

        if (!isPaymentPaid) {
            html += `<a href="/transactions/create?invoice_id=${invoiceId}" class="action-btn btn-payment" title="Add Payment">
                <i class="fas fa-dollar-sign"></i>
            </a>`;
        }

        if (isDelivered || isPartial) {
            html += `<button class="action-btn btn-delete" disabled title="Cannot delete invoice with ${row.delivery_status} status">
                <i class="fas fa-trash"></i>
            </button>`;
        } else {
            html += `<button class="action-btn btn-delete delete-invoice" data-invoice-id="${invoiceId}" title="Delete">
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
            emptyTable: 'No invoices found',
            info: 'Showing _START_ to _END_ of _TOTAL_ invoices',
            infoEmpty: 'No invoices to display',
            search: '',
            searchPlaceholder: 'Search invoices...',
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

        // Set hidden filters based on button clicked
        if (filter === 'all') {
            $('#payment_status').val('');
            $('#delivery_status').val('');
        } else if (filter === 'due') {
            $('#payment_status').val('due');
            $('#delivery_status').val('');
        } else if (filter === 'pending') {
            $('#payment_status').val('');
            $('#delivery_status').val('pending');
        } else if (filter === 'paid') {
            $('#payment_status').val('paid');
            $('#delivery_status').val('');
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
        $('#payment_status').val('');
        $('#delivery_status').val('');
        $('[data-filter]').removeClass('active btn-secondary').addClass('btn-outline-secondary');
        $('[data-filter="all"]').removeClass('btn-outline-secondary').addClass('active btn-secondary');
        table.draw();
        toastr.info('Filters cleared');
    });
    
    // Select all checkbox
    $('#select-all').on('change', function() {
        $('.invoice-checkbox').prop('checked', $(this).prop('checked'));
    });
    
    // Individual checkbox
    $(document).on('change', '.invoice-checkbox', function() {
        const totalCheckboxes = $('.invoice-checkbox').length;
        const checkedCheckboxes = $('.invoice-checkbox:checked').length;
        $('#select-all').prop('checked', checkedCheckboxes === totalCheckboxes);
    });
    
    // Mark as paid
    $(document).on('click', '.mark-paid-btn', function() {
        const invoiceId = $(this).data('invoice-id');
        const dueAmount = $(this).data('due-amount');
        
        Swal.fire({
            title: 'Mark Invoice as Paid',
            html: `Are you sure you want to mark this invoice as paid?<br><strong>Amount: ৳${dueAmount}</strong>`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#10b981',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Yes, mark as paid'
        }).then((result) => {
            if (result.isConfirmed) {
                markAsPaid(invoiceId);
            }
        });
    });
    
    // Delivery status change
    $(document).on('change', '.delivery-status-select', function() {
        const invoiceId = $(this).data('invoice-id');
        const newStatus = $(this).val();
        const selectElement = $(this);

        // If marking as delivered, show the modal instead
        if (newStatus === 'delivered') {
            // Reset to previous value until modal confirms
            selectElement.val(selectElement.data('previous-value') || 'pending');
            showDeliveryModal(invoiceId);
        } else {
            updateDeliveryStatus(invoiceId, newStatus, selectElement);
        }
    });
    
    // Delete invoice
    $(document).on('click', '.delete-invoice', function() {
        const invoiceId = $(this).data('invoice-id');
        
        Swal.fire({
            title: 'Delete Invoice?',
            text: 'This action cannot be undone.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Yes, delete it'
        }).then((result) => {
            if (result.isConfirmed) {
                deleteInvoice(invoiceId);
            }
        });
    });
    
    // Refresh table
    $('#refresh-table').on('click', function() {
        table.draw();
        toastr.success('Table refreshed');
    });

    
    // Export table
    $('#export-table').on('click', function() {
        window.location.href = '{{ route('invoices.export') }}';
    });

    // Helper Functions
    function markAsPaid(invoiceId) {
        $.ajax({
            url: `/invoices/${invoiceId}/mark-as-paid`,
            type: 'POST',
            data: {
                _token: csrfToken
            },
            success: function(response) {
                if (response.success) {
                    toastr.success('Invoice marked as paid successfully');
                    table.draw(false);
                } else {
                    toastr.error(response.message || 'Failed to mark invoice as paid');
                }
            },
            error: function(xhr) {
                toastr.error('Error: ' + (xhr.responseJSON?.message || 'An error occurred'));
            }
        });
    }
    
    function updateDeliveryStatus(invoiceId, status, selectElement) {
        $.ajax({
            url: '{{ route('invoices.update-delivery-status') }}',
            type: 'POST',
            data: {
                _token: csrfToken,
                invoice_id: invoiceId,
                delivery_status: status
            },
            success: function(response) {
                if (response.success) {
                    toastr.success('Delivery status updated successfully');
                    table.draw(false);
                } else {
                    toastr.error(response.message || 'Failed to update delivery status');
                }
            },
            error: function(xhr) {
                toastr.error('Error: ' + (xhr.responseJSON?.message || 'An error occurred'));
                selectElement.val(selectElement.data('previous-value') || 'pending');
            }
        });
    }
    
    function deleteInvoice(invoiceId) {
        $.ajax({
            url: `/invoices/${invoiceId}`,
            type: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': csrfToken
            },
            success: function(response) {
                if (response.success) {
                    toastr.success('Invoice deleted successfully');
                    table.draw(false);
                } else {
                    toastr.error(response.message || 'Failed to delete invoice');
                }
            },
            error: function(xhr) {
                toastr.error('Error: ' + (xhr.responseJSON?.message || 'An error occurred'));
            }
        });
    }

    // Show delivery modal
    function showDeliveryModal(invoiceId) {
        // Set the invoice ID in the form
        $('#modal_invoice_id').val(invoiceId);

        // Reset form fields
        $('#deliveryForm')[0].reset();
        $('#modal_invoice_id').val(invoiceId);

        // Show loading in items table
        $('#modal_items_body').html('<tr><td colspan="4" class="text-center"><i class="fas fa-spinner fa-spin"></i> Loading items...</td></tr>');

        // Fetch invoice details
        $.ajax({
            url: `/invoices/${invoiceId}`,
            type: 'GET',
            headers: {
                'Accept': 'application/json'
            },
            success: function(response) {
                const invoice = response.invoice || response;

                // Fill customer info
                if (invoice.customer) {
                    $('#modal_receiver_name').val(invoice.customer.name || '');
                    $('#modal_receiver_phone').val(invoice.customer.phone || '');
                    $('#modal_shipping_address').val(invoice.customer.address || '');
                }

                // Fill items table
                let itemsHtml = '';
                if (invoice.items && invoice.items.length > 0) {
                    invoice.items.forEach(function(item) {
                        const deliveredQty = item.delivered_quantity || 0;
                        const remainingQty = item.quantity - deliveredQty;

                        if (remainingQty > 0) {
                            itemsHtml += `<tr>
                                <td>${item.product?.name || item.description || 'N/A'}</td>
                                <td>${parseFloat(item.quantity).toFixed(2)}</td>
                                <td><span class="badge badge-info">${parseFloat(deliveredQty).toFixed(2)}</span></td>
                                <td><span class="badge badge-warning">${parseFloat(remainingQty).toFixed(2)}</span></td>
                            </tr>`;
                        }
                    });
                }

                if (!itemsHtml) {
                    itemsHtml = '<tr><td colspan="4" class="text-center text-muted">All items already delivered</td></tr>';
                }

                $('#modal_items_body').html(itemsHtml);
            },
            error: function(xhr) {
                $('#modal_items_body').html('<tr><td colspan="4" class="text-center text-danger">Failed to load invoice items</td></tr>');
            }
        });

        // Show the modal
        $('#deliveryModal').modal('show');
    }

    // Handle delivery form submission
    $('#deliveryForm').on('submit', function(e) {
        e.preventDefault();

        const btn = $('#submitDeliveryBtn');
        const originalText = btn.html();
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Processing...');

        $.ajax({
            url: '{{ route("challans.quick-store") }}',
            method: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                if (response.success) {
                    $('#deliveryModal').modal('hide');
                    toastr.success(response.message || 'Delivery completed successfully');
                    table.draw(false);
                } else {
                    toastr.error(response.message || 'An error occurred');
                }
            },
            error: function(xhr) {
                let msg = 'An error occurred';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    msg = xhr.responseJSON.message;
                }
                toastr.error(msg);
            },
            complete: function() {
                btn.prop('disabled', false).html(originalText);
            }
        });
    });
    
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
