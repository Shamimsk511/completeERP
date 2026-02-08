@extends('layouts.modern-admin')

@section('title', 'Company Details')

@section('page_title', 'Company Details')

@section('header_actions')
    <a href="{{ route('companies.index') }}" class="btn modern-btn modern-btn-outline">
        <i class="fas fa-arrow-left"></i> Back to Companies
    </a>
    <a href="{{ route('companies.edit', $company) }}" class="btn modern-btn modern-btn-warning">
        <i class="fas fa-edit"></i> Edit Company
    </a>
@stop

@section('page_content')
    @php
        $openingBalance = $company->opening_balance ?? 0;
        $openingType = ($company->opening_balance_type ?? 'credit') === 'debit' ? 'Dr' : 'Cr';
        $ledger = $company->ledgerAccount;
        $currentBalance = $ledger?->current_balance ?? null;
        $currentType = ($ledger?->current_balance_type ?? 'credit') === 'debit' ? 'Dr' : 'Cr';
    @endphp

    <div class="card modern-card">
        <div class="card-header modern-header">
            <h3 class="card-title"><i class="fas fa-building"></i> {{ $company->name }}</h3>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-3 mb-2">
                    <label for="company-filter-from" class="form-label">From Date</label>
                    <input type="date" id="company-filter-from" class="form-control modern-input">
                </div>
                <div class="col-md-3 mb-2">
                    <label for="company-filter-to" class="form-label">To Date</label>
                    <input type="date" id="company-filter-to" class="form-control modern-input">
                </div>
                <div class="col-md-6 mb-2 d-flex align-items-end gap-2">
                    <button type="button" id="company-filter-apply" class="btn modern-btn modern-btn-primary btn-sm">
                        Apply
                    </button>
                    <button type="button" id="company-filter-clear" class="btn modern-btn modern-btn-outline btn-sm">
                        Clear
                    </button>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-6 mb-4">
                    <div class="info-section">
                        <div class="section-header">
                            <i class="fas fa-info-circle"></i> Company Info
                        </div>
                        <div class="section-content">
                            <div class="form-group">
                                <label class="form-label">Company Name</label>
                                <div class="form-control modern-input" readonly>{{ $company->name }}</div>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Company Type</label>
                                <div class="form-control modern-input" readonly>{{ $company->type ? ucfirst($company->type) : 'Both' }}</div>
                            </div>

                            <div class="form-group mb-0">
                                <label class="form-label">Description</label>
                                <div class="form-control modern-textarea" style="min-height: 96px;" readonly>
                                    {{ $company->description ?? 'No description provided' }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6 mb-4">
                    <div class="info-section">
                        <div class="section-header">
                            <i class="fas fa-address-book"></i> Contact & Balance
                        </div>
                        <div class="section-content">
                            <div class="form-group">
                                <label class="form-label">Primary Contact</label>
                                <div class="form-control modern-input" readonly>{{ $company->contact ?? 'N/A' }}</div>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Address</label>
                                <div class="form-control modern-textarea" style="min-height: 80px;" readonly>
                                    {{ $company->address ?? 'N/A' }}
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Opening Balance</label>
                                <div class="form-control modern-input" readonly>
                                    ৳{{ number_format($openingBalance, 2) }} {{ $openingType }}
                                </div>
                            </div>

                            <div class="form-group mb-2">
                                <label class="form-label">Current Balance</label>
                                <div class="form-control modern-input" readonly>
                                    @if($currentBalance === null)
                                        —
                                    @else
                                        ৳{{ number_format($currentBalance, 2) }} {{ $currentType }}
                                    @endif
                                </div>
                            </div>

                            @if($ledger)
                                <a href="{{ route('accounting.accounts.ledger', $ledger) }}" class="btn modern-btn modern-btn-outline btn-sm">
                                    <i class="fas fa-book"></i> View Ledger
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-6 mb-4">
                    <div class="info-section">
                        <div class="section-header">
                            <i class="fas fa-receipt"></i> Recent Payments
                        </div>
                        <div class="section-content">
                            <div class="table-responsive">
                                <table class="table modern-table" id="recent-payments-table">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Type</th>
                                            <th>Method</th>
                                            <th class="text-right">Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6 mb-4">
                    <div class="info-section">
                        <div class="section-header">
                            <i class="fas fa-boxes"></i> Top Products (By Purchase Qty)
                        </div>
                        <div class="section-content">
                            <div class="table-responsive">
                                <table class="table modern-table" id="top-products-table">
                                    <thead>
                                        <tr>
                                            <th>Product</th>
                                            <th class="text-right">Qty</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                            <a href="{{ route('products.index') }}" class="btn modern-btn modern-btn-outline btn-sm mt-2">
                                View All Products
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-6 mb-4">
                    <div class="info-section">
                        <div class="section-header">
                            <i class="fas fa-shopping-cart"></i> Recent Purchase Products
                        </div>
                        <div class="section-content">
                            <div class="table-responsive">
                                <table class="table modern-table" id="recent-purchases-table">
                                    <thead>
                                        <tr>
                                            <th>Product</th>
                                            <th class="text-right">Qty</th>
                                            <th>Date</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                            <a href="{{ route('purchases.index') }}" class="btn modern-btn modern-btn-outline btn-sm mt-2">
                                View All Purchases
                            </a>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6 mb-4">
                    <div class="info-section">
                        <div class="section-header">
                            <i class="fas fa-exclamation-triangle"></i> Low Stock Products
                        </div>
                        <div class="section-content">
                            <div class="table-responsive">
                                <table class="table modern-table" id="low-stock-table">
                                    <thead>
                                        <tr>
                                            <th>Product</th>
                                            <th class="text-right">Stock</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="info-section">
                        <div class="section-header">
                            <i class="fas fa-truck"></i> Remaining Products Owed to Customers
                        </div>
                        <div class="section-content">
                            <div class="table-responsive">
                                <table class="table modern-table" id="remaining-products-table">
                                    <thead>
                                        <tr>
                                            <th>Customer</th>
                                            <th>Invoice</th>
                                            <th>Product</th>
                                            <th class="text-right">Remaining Qty</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                            <a href="{{ route('sales.remaining_products') }}" class="btn modern-btn modern-btn-outline btn-sm mt-2">
                                View Remaining Products
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('additional_css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.25/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.bootstrap4.min.css">
@stop

@section('additional_js')
    <script src="https://cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.25/js/dataTables.bootstrap4.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.2.9/js/responsive.bootstrap4.min.js"></script>
    <script>
        $(document).ready(function() {
            const productBaseUrl = "{{ url('/products') }}";
            const invoiceBaseUrl = "{{ url('/invoices') }}";

            const tableOptions = {
                processing: true,
                serverSide: true,
                paging: true,
                pageLength: 5,
                lengthChange: true,
                lengthMenu: [5, 10, 25, 50],
                info: true,
                searching: true,
                responsive: true,
                autoWidth: false,
                dom: '<"row mb-2"<"col-md-6"l><"col-md-6"f>>t<"row mt-2"<"col-md-6"i><"col-md-6"p>>',
                language: {
                    search: 'Search:'
                }
            };

            const getDateFilters = function() {
                return {
                    start_date: $('#company-filter-from').val(),
                    end_date: $('#company-filter-to').val()
                };
            };

            const paymentsTable = $('#recent-payments-table').DataTable(Object.assign({}, tableOptions, {
                ajax: {
                    url: "{{ route('companies.recent-payments', $company) }}",
                    data: function(d) {
                        Object.assign(d, getDateFilters());
                    }
                },
                order: [[0, 'desc']],
                columns: [
                    { data: 'transaction_date', name: 'transaction_date' },
                    { data: 'transaction_type', name: 'transaction_type' },
                    { data: 'payment_method', name: 'payment_method' },
                    { data: 'amount_formatted', name: 'amount', className: 'text-right', searchable: false }
                ]
            }));

            const topProductsTable = $('#top-products-table').DataTable(Object.assign({}, tableOptions, {
                ajax: {
                    url: "{{ route('companies.top-products', $company) }}",
                    data: function(d) {
                        Object.assign(d, getDateFilters());
                    }
                },
                order: [[1, 'desc']],
                columns: [
                    {
                        data: 'product_name',
                        name: 'products.name',
                        render: function(data, type, row) {
                            if (type === 'display' && row.product_id) {
                                return '<a href="' + productBaseUrl + '/' + row.product_id + '" class="text-primary">' + data + '</a>';
                            }
                            return data;
                        }
                    },
                    { data: 'total_qty_formatted', name: 'total_qty', className: 'text-right', searchable: false }
                ]
            }));

            const recentPurchasesTable = $('#recent-purchases-table').DataTable(Object.assign({}, tableOptions, {
                ajax: {
                    url: "{{ route('companies.recent-purchases', $company) }}",
                    data: function(d) {
                        Object.assign(d, getDateFilters());
                    }
                },
                order: [[2, 'desc']],
                columns: [
                    {
                        data: 'product_name',
                        name: 'products.name',
                        render: function(data, type, row) {
                            if (type === 'display' && row.product_id) {
                                return '<a href="' + productBaseUrl + '/' + row.product_id + '" class="text-primary">' + data + '</a>';
                            }
                            return data;
                        }
                    },
                    { data: 'quantity_formatted', name: 'quantity', className: 'text-right', searchable: false },
                    { data: 'purchase_date', name: 'purchase_date' }
                ]
            }));

            const lowStockTable = $('#low-stock-table').DataTable(Object.assign({}, tableOptions, {
                ajax: {
                    url: "{{ route('companies.low-stock', $company) }}",
                    data: function(d) {
                        Object.assign(d, getDateFilters());
                    }
                },
                order: [[1, 'asc']],
                columns: [
                    {
                        data: 'product_name',
                        name: 'name',
                        render: function(data, type, row) {
                            if (type === 'display' && row.product_id) {
                                return '<a href="' + productBaseUrl + '/' + row.product_id + '" class="text-primary">' + data + '</a>';
                            }
                            return data;
                        }
                    },
                    { data: 'current_stock_formatted', name: 'current_stock', className: 'text-right', searchable: false }
                ]
            }));

            const remainingProductsTable = $('#remaining-products-table').DataTable(Object.assign({}, tableOptions, {
                ajax: {
                    url: "{{ route('companies.remaining-products', $company) }}",
                    data: function(d) {
                        Object.assign(d, getDateFilters());
                    }
                },
                order: [[1, 'desc']],
                columns: [
                    { data: 'customer_name', name: 'customers.name' },
                    {
                        data: 'invoice_number',
                        name: 'invoices.invoice_number',
                        render: function(data, type, row) {
                            if (type === 'display' && row.invoice_id) {
                                const dateLine = row.invoice_date ? '<div class="text-muted small">' + row.invoice_date + '</div>' : '';
                                return '<a href="' + invoiceBaseUrl + '/' + row.invoice_id + '" class="text-primary">' + data + '</a>' + dateLine;
                            }
                            return data;
                        }
                    },
                    { data: 'product_name', name: 'products.name' },
                    { data: 'remaining_quantity_formatted', name: 'remaining_quantity', className: 'text-right', searchable: false }
                ]
            }));

            const reloadAllTables = function() {
                paymentsTable.ajax.reload();
                topProductsTable.ajax.reload();
                recentPurchasesTable.ajax.reload();
                lowStockTable.ajax.reload();
                remainingProductsTable.ajax.reload();
            };

            $('#company-filter-apply').on('click', function() {
                reloadAllTables();
            });

            $('#company-filter-clear').on('click', function() {
                $('#company-filter-from').val('');
                $('#company-filter-to').val('');
                reloadAllTables();
            });
        });
    </script>
@stop
