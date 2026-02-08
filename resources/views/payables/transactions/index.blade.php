@extends('layouts.modern-admin')

@section('title', 'Payable Transactions')

@section('page_title', 'Payable Transactions')

@section('header_actions')
    <a href="{{ route('payable-transactions.create') }}" class="btn modern-btn modern-btn-primary">
        <i class="fas fa-plus"></i> Add Transaction
    </a>
    <a href="{{ route('payees.index') }}" class="btn modern-btn modern-btn-outline">
        <i class="fas fa-users"></i> Payees
    </a>
@stop

@section('page_content')
    <div class="card modern-card">
        <div class="card-header modern-header">
            <h3 class="card-title"><i class="fas fa-chart-pie"></i> Summary</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4 mb-3">
                    <div class="info-section">
                        <div class="section-header">
                            <i class="fas fa-arrow-down"></i> Total Cash In
                        </div>
                        <div class="section-content">
                            <h4 class="mb-0">৳{{ number_format($totalCashIn, 2) }}</h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="info-section">
                        <div class="section-header">
                            <i class="fas fa-arrow-up"></i> Total Cash Out
                        </div>
                        <div class="section-content">
                            <h4 class="mb-0">৳{{ number_format($totalCashOut, 2) }}</h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="info-section">
                        <div class="section-header">
                            <i class="fas fa-balance-scale"></i> Net Payable
                        </div>
                        <div class="section-content">
                            <h4 class="mb-0">৳{{ number_format($netPayable, 2) }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card modern-card mt-4">
        <div class="card-header modern-header">
            <h3 class="card-title"><i class="fas fa-filter"></i> Filters</h3>
        </div>
        <div class="card-body">
            <form id="filter-form">
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label class="form-label">From Date</label>
                        <input type="date" id="date_from" class="form-control modern-input">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">To Date</label>
                        <input type="date" id="date_to" class="form-control modern-input">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Payee</label>
                        <select id="payee_id" class="form-control modern-select">
                            <option value="">All Payees</option>
                            @foreach($payees as $payee)
                                <option value="{{ $payee->id }}">{{ $payee->name }}{{ $payee->phone ? ' - ' . $payee->phone : '' }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Type</label>
                        <select id="transaction_type" class="form-control modern-select">
                            <option value="">All Types</option>
                            <option value="cash_in">Payment (Cash In)</option>
                            <option value="cash_out">Received (Cash Out)</option>
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Category</label>
                        <select id="category" class="form-control modern-select">
                            <option value="">All Categories</option>
                            <option value="payment">Payment</option>
                            <option value="commission">Commission</option>
                            <option value="adjustment">Adjustment</option>
                            <option value="other_in">Other (In)</option>
                            <option value="purchase">Purchase</option>
                            <option value="borrow">Borrow</option>
                            <option value="other_out">Other (Out)</option>
                            <option value="interest_payment">Interest Payment</option>
                        </select>
                    </div>
                </div>
                <div class="d-flex gap-2">
                    <button type="button" id="reset-filter" class="btn modern-btn modern-btn-outline btn-sm">Reset Filters</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card modern-card mt-4">
        <div class="card-header modern-header">
            <h3 class="card-title"><i class="fas fa-list"></i> Transactions</h3>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="transactions-table" class="table modern-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Payee</th>
                            <th>Type</th>
                            <th>Category</th>
                            <th>Amount</th>
                            <th>Reference</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
@stop

@section('additional_css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap4.min.css">
@stop

@section('additional_js')
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap4.min.js"></script>
    <script>
        $(document).ready(function() {
            if ($.fn.select2) {
                $('#payee_id').select2({
                    width: '100%'
                });
            }

            const table = $('#transactions-table').DataTable({
                processing: true,
                serverSide: true,
                order: [[0, 'desc']],
                ajax: {
                    url: "{{ route('payable-transactions.index') }}",
                    data: function(d) {
                        d.date_from = $('#date_from').val();
                        d.date_to = $('#date_to').val();
                        d.payee_id = $('#payee_id').val();
                        d.transaction_type = $('#transaction_type').val();
                        d.category = $('#category').val();
                    }
                },
                columns: [
                    { data: 'transaction_date', name: 'transaction_date' },
                    { data: 'payee_name', name: 'payee.name' },
                    { data: 'transaction_type', name: 'transaction_type' },
                    { data: 'category', name: 'category' },
                    { data: 'amount', name: 'amount', render: function(data) { return `৳${data}`; } },
                    { data: 'reference_no', name: 'reference_no' },
                    { data: 'action', name: 'action', orderable: false, searchable: false }
                ],
                pageLength: 25,
                lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
                dom: '<"row mb-2"<"col-md-6"l><"col-md-6"f>>rt<"row mt-2"<"col-md-5"i><"col-md-7"p>>',
                language: {
                    emptyTable: 'No transactions found',
                    zeroRecords: 'No transactions match your filters'
                }
            });

            const applyFilters = function() {
                table.draw();
            };

            $('#filter-form input, #filter-form select').on('change', function() {
                applyFilters();
            });

            $('#reset-filter').on('click', function() {
                $('#filter-form')[0].reset();
                if ($.fn.select2) {
                    $('#payee_id').val(null).trigger('change');
                }
                applyFilters();
            });

            $(document).on('click', '.delete-transaction', function() {
                const transactionId = $(this).data('id');

                const runDelete = function() {
                    $.ajax({
                        url: '/payables/transactions/' + transactionId,
                        type: 'DELETE',
                        data: {
                            _token: "{{ csrf_token() }}"
                        },
                        success: function(response) {
                            if (response.success) {
                                if (window.Swal) {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Deleted',
                                        text: response.message,
                                        timer: 2000,
                                        showConfirmButton: false
                                    });
                                }
                                table.ajax.reload(null, false);
                            } else if (window.Swal) {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Delete Failed',
                                    text: response.message || 'Unable to delete transaction.'
                                });
                            }
                        },
                        error: function() {
                            if (window.Swal) {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Delete Failed',
                                    text: 'Something went wrong. Please try again.'
                                });
                            }
                        }
                    });
                };

                if (!window.Swal) {
                    if (confirm('Are you sure you want to delete this transaction?')) {
                        runDelete();
                    }
                    return;
                }

                Swal.fire({
                    title: 'Delete Transaction',
                    text: 'Are you sure you want to delete this transaction?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#ef4444',
                    cancelButtonColor: '#6b7280',
                    confirmButtonText: 'Yes, delete it',
                    cancelButtonText: 'Cancel',
                    reverseButtons: true,
                    focusCancel: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        runDelete();
                    }
                });
            });
        });
    </script>
@stop
