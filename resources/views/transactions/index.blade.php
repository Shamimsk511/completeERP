@extends('layouts.modern-admin')

@section('title', 'Transactions')

@section('page_title', 'Transactions')

@section('header_actions')
    <a href="{{ route('transactions.create') }}" class="btn modern-btn modern-btn-primary">
        <i class="fas fa-plus"></i> Add Transaction
    </a>
@stop

@section('page_content')
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    <div class="card modern-card">
        <div class="card-header modern-header">
            <h3 class="card-title"><i class="fas fa-filter"></i> Filter Transactions</h3>
        </div>
        <div class="card-body">
            <form id="filter-form">
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label class="form-label">From Date</label>
                        <input type="date" class="form-control modern-input" id="date_from" name="date_from">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">To Date</label>
                        <input type="date" class="form-control modern-input" id="date_to" name="date_to">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Customer</label>
                        <select class="form-control modern-select customer-select" id="customer_id" name="customer_id">
                            <option value="">All Customers</option>
                            @foreach($customers as $customer)
                                <option value="{{ $customer->id }}">
                                    {{ $customer->name }}{{ $customer->phone ? ' - ' . $customer->phone : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Type</label>
                        <select class="form-control modern-select" id="type" name="type">
                            <option value="">All Types</option>
                            <option value="debit" selected>Debit (Customer Payment)</option>
                            <option value="credit">Credit</option>
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Payment Method</label>
                        <select class="form-control modern-select" id="method" name="method">
                            <option value="">All Methods</option>
                            <option value="cash">Cash</option>
                            <option value="bank">Bank</option>
                            <option value="mobile_bank">Mobile Bank</option>
                            <option value="cheque">Cheque</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Purpose</label>
                        <input type="text" class="form-control modern-input" id="purpose" name="purpose" placeholder="Search by purpose">
                    </div>
                </div>

                <div class="d-flex flex-wrap gap-2">
                    <button type="button" id="apply-filter" class="btn modern-btn modern-btn-primary btn-sm">
                        Apply Filters
                    </button>
                    <button type="button" id="reset-filter" class="btn modern-btn modern-btn-outline btn-sm">
                        Reset
                    </button>
                    <button type="button" id="quick-today" class="btn modern-btn modern-btn-info btn-sm">
                        Today
                    </button>
                    <button type="button" id="quick-week" class="btn modern-btn modern-btn-info btn-sm">
                        This Week
                    </button>
                    <button type="button" id="quick-month" class="btn modern-btn modern-btn-info btn-sm">
                        This Month
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="card modern-card">
        <div class="card-header modern-header">
            <h3 class="card-title"><i class="fas fa-list"></i> Transactions</h3>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="transactions-table" class="table modern-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Date</th>
                            <th>Customer</th>
                            <th>Type</th>
                            <th>Purpose</th>
                            <th>Method</th>
                            <th>Amount</th>
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
                $('.customer-select').select2({
                    width: '100%'
                });
            }

            const table = $('#transactions-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('transactions.index') }}",
                    type: 'GET',
                    data: function(d) {
                        d.date_from = $('#date_from').val();
                        d.date_to = $('#date_to').val();
                        d.customer_id = $('#customer_id').val();
                        d.type = $('#type').val();
                        d.method = $('#method').val();
                        d.purpose = $('#purpose').val();
                    }
                },
                columns: [
                    { data: 'id', name: 'id' },
                    { data: 'date', name: 'created_at' },
                    { data: 'customer', name: 'customer_id' },
                    { data: 'type', name: 'type' },
                    { data: 'purpose', name: 'purpose' },
                    { data: 'method', name: 'method' },
                    { data: 'amount', name: 'amount' },
                    { data: 'actions', name: 'actions', orderable: false, searchable: false }
                ],
                order: [[1, 'desc']],
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

            const resetFilters = function() {
                $('#filter-form')[0].reset();
                if ($.fn.select2) {
                    $('#customer_id').val(null).trigger('change');
                }
                $('#type').val('debit');
                applyFilters();
            };

            const formatDate = function(date) {
                return date.toISOString().split('T')[0];
            };

            $('#apply-filter').on('click', applyFilters);
            $('#reset-filter').on('click', resetFilters);

            $('#quick-today').on('click', function() {
                const today = new Date();
                $('#date_from').val(formatDate(today));
                $('#date_to').val(formatDate(today));
                applyFilters();
            });

            $('#quick-week').on('click', function() {
                const today = new Date();
                const day = today.getDay();
                const diff = today.getDate() - day + (day === 0 ? -6 : 1);
                const start = new Date(today.setDate(diff));
                const end = new Date();
                $('#date_from').val(formatDate(start));
                $('#date_to').val(formatDate(end));
                applyFilters();
            });

            $('#quick-month').on('click', function() {
                const today = new Date();
                const start = new Date(today.getFullYear(), today.getMonth(), 1);
                const end = new Date();
                $('#date_from').val(formatDate(start));
                $('#date_to').val(formatDate(end));
                applyFilters();
            });

            $('#filter-form input, #filter-form select').on('change', function() {
                applyFilters();
            });

            // default to debit (customer payments)
            $('#type').val('debit');
            applyFilters();
        });
    </script>
@stop
