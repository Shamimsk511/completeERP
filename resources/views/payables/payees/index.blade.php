@extends('layouts.modern-admin')

@section('title', 'Payees')

@section('page_title', 'Payees')

@section('header_actions')
    <a href="{{ route('payees.create') }}" class="btn modern-btn modern-btn-primary">
        <i class="fas fa-plus"></i> Add Payee
    </a>
    <a href="{{ route('payable-transactions.create') }}" class="btn modern-btn modern-btn-outline">
        <i class="fas fa-credit-card"></i> Add Transaction
    </a>
    <a href="{{ route('payable-transactions.index') }}" class="btn modern-btn modern-btn-outline">
        <i class="fas fa-list"></i> Transactions
    </a>
@stop

@section('page_content')
    <div class="card modern-card">
        <div class="card-header modern-header">
            <h3 class="card-title"><i class="fas fa-chart-pie"></i> Summary</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3 mb-3">
                    <div class="info-section">
                        <div class="section-header">
                            <i class="fas fa-money-bill-wave"></i> Total Payable
                        </div>
                        <div class="section-content">
                            <h4 class="mb-0">৳{{ number_format($totalPayable, 2) }}</h4>
                            <small class="text-muted">Ledger balance</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="info-section">
                        <div class="section-header">
                            <i class="fas fa-truck"></i> Suppliers
                        </div>
                        <div class="section-content">
                            <h4 class="mb-0">{{ $totalSuppliers }}</h4>
                            <small class="text-muted">Active suppliers</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="info-section">
                        <div class="section-header">
                            <i class="fas fa-user"></i> Individuals
                        </div>
                        <div class="section-content">
                            <h4 class="mb-0">{{ $totalIndividuals }}</h4>
                            <small class="text-muted">Personal payees</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="info-section">
                        <div class="section-header">
                            <i class="fas fa-users"></i> Total Payees
                        </div>
                        <div class="section-content">
                            <h4 class="mb-0">{{ $totalPayees }}</h4>
                            <small class="text-muted">All payees</small>
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
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Category/Type</label>
                    <select id="payee-category-filter" class="form-control modern-select">
                        <option value="">All Categories</option>
                        @foreach($categories as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="d-flex gap-2">
                <button type="button" id="reset-filter" class="btn modern-btn modern-btn-outline btn-sm">
                    Reset Filters
                </button>
            </div>
        </div>
    </div>

    <div class="card modern-card mt-4">
        <div class="card-header modern-header">
            <h3 class="card-title"><i class="fas fa-list"></i> Payees</h3>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="payees-table" class="table modern-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Type</th>
                            <th>Phone</th>
                            <th>Balance</th>
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
            const escapeHtml = function(value) {
                return $('<div>').text(value ?? '').html();
            };

            const formatBalance = function(value) {
                const raw = parseFloat(String(value ?? 0).replace(/,/g, '')) || 0;
                const cls = raw > 0 ? 'text-success' : (raw < 0 ? 'text-danger' : 'text-muted');
                return `<span class="${cls}">৳${raw.toFixed(2)}</span>`;
            };

            const formatType = function(value) {
                const type = String(value ?? '').toLowerCase();
                const labelMap = {
                    supplier: 'Supplier',
                    bank: 'Bank',
                    personal: 'Personal',
                    cc: 'CC Loan',
                    sme: 'SME Loan',
                    term_loan: 'Term Loan',
                    daily_kisti: 'Daily Kisti'
                };
                const badgeMap = {
                    supplier: 'badge-success',
                    bank: 'badge-info',
                    personal: 'badge-secondary',
                    cc: 'badge-warning',
                    sme: 'badge-warning',
                    term_loan: 'badge-warning',
                    daily_kisti: 'badge-warning'
                };
                const label = labelMap[type] || (type ? type.charAt(0).toUpperCase() + type.slice(1) : 'N/A');
                const badge = badgeMap[type] || 'badge-secondary';
                return `<span class="badge ${badge}">${label}</span>`;
            };

            const payeesTable = $('#payees-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('payees.index') }}",
                    data: function(d) {
                        d.category = $('#payee-category-filter').val();
                    }
                },
                columns: [
                    {
                        data: 'name',
                        name: 'name',
                        render: function(data, type, row) {
                            return `<a href="/payables/payees/${row.id}"><strong>${escapeHtml(data)}</strong></a>`;
                        }
                    },
                    {
                        data: 'type',
                        name: 'type',
                        render: function(data) {
                            return formatType(data);
                        }
                    },
                    {
                        data: 'phone',
                        name: 'phone',
                        render: function(data) {
                            return data ? escapeHtml(data) : '<span class="text-muted">N/A</span>';
                        }
                    },
                    {
                        data: 'ledger_balance',
                        name: 'ledger_balance',
                        render: function(data) {
                            return formatBalance(data);
                        }
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    }
                ],
                order: [[0, 'asc']],
                pageLength: 25,
                lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
                dom: '<"row mb-2"<"col-md-6"l><"col-md-6"f>>rt<"row mt-2"<"col-md-5"i><"col-md-7"p>>',
                language: {
                    emptyTable: 'No payees found',
                    zeroRecords: 'No payees match your filters'
                }
            });

            $('#payee-category-filter').on('change', function() {
                payeesTable.ajax.reload();
            });

            $('#reset-filter').on('click', function() {
                $('#payee-category-filter').val('');
                payeesTable.search('').draw();
            });

            $(document).on('click', '.delete-payee', function() {
                const payeeId = $(this).data('id');
                const runDelete = function() {
                    $.ajax({
                        url: `/payables/payees/${payeeId}`,
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
                                payeesTable.ajax.reload(null, false);
                            } else {
                                if (window.Swal) {
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Delete Failed',
                                        text: response.message || 'Unable to delete payee.'
                                    });
                                }
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
                    if (confirm('Are you sure you want to delete this payee?')) {
                        runDelete();
                    }
                    return;
                }

                Swal.fire({
                    title: 'Delete Payee',
                    text: 'Are you sure you want to delete this payee? This action cannot be undone.',
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
