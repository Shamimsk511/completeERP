@extends('layouts.modern-admin')

@section('title', 'Payee Ledger')

@section('page_title', 'Ledger for ' . $payee->name)

@section('header_actions')
    <div class="header-actions-group">
        <a href="{{ route('payees.show', $payee->id) }}" class="btn modern-btn modern-btn-outline">
            <i class="fas fa-arrow-left"></i> <span class="btn-text">Back to Payee</span>
        </a>
        <a href="{{ route('payable-transactions.create') }}?payee_id={{ $payee->id }}" class="btn modern-btn modern-btn-success">
            <i class="fas fa-plus"></i> <span class="btn-text">Add Transaction</span>
        </a>
    </div>
@stop

@section('page_content')
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible modern-alert">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
            <i class="fas fa-exclamation-circle"></i>
            {{ session('error') }}
        </div>
    @endif

    @if(session('success'))
        <div class="alert alert-success alert-dismissible modern-alert">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
            <i class="fas fa-check-circle"></i>
            {{ session('success') }}
        </div>
    @endif

    <!-- Payee Summary Cards -->
    <div class="row summary-row">
        <div class="col-lg-4 col-md-6 col-sm-6 col-12">
            <div class="summary-card summary-card-info">
                <div class="summary-content">
                    <div class="summary-icon">
                        <i class="fas fa-user"></i>
                    </div>
                    <div class="summary-info">
                        <h3 class="summary-title">{{ $payee->name }}</h3>
                        <p class="summary-subtitle">
                            <span class="type-badge type-{{ $payee->type }}">
                                <i class="fas fa-{{ $payee->type === 'supplier' ? 'truck' : 'user' }}"></i>
                                {{ ucfirst($payee->type) }}
                            </span>
                        </p>
                        @if($payee->phone)
                            <p class="summary-contact">
                                <i class="fas fa-phone"></i> {{ $payee->phone }}
                            </p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4 col-md-6 col-sm-6 col-12">
            <div class="summary-card summary-card-warning">
                <div class="summary-content">
                    <div class="summary-icon">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                    <div class="summary-info">
                        @php
                            $openingBalance = $ledgerOpeningBalance ?? $payee->opening_balance;
                        @endphp
                        <h3 class="summary-number">${{ number_format($openingBalance, 2) }}</h3>
                        <p class="summary-label">Opening Balance</p>
                        <div class="summary-trend">
                            <i class="fas fa-calendar-alt trend-neutral"></i>
                            <span class="trend-text">Initial Amount</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4 col-md-6 col-sm-6 col-12">
            @php
                $currentBalance = $ledgerCurrentBalance ?? $payee->current_balance;
            @endphp
            <div class="summary-card {{ $currentBalance > 0 ? 'summary-card-danger' : ($currentBalance < 0 ? 'summary-card-success' : 'summary-card-neutral') }}">
                <div class="summary-content">
                    <div class="summary-icon">
                        <i class="fas fa-balance-scale"></i>
                    </div>
                    <div class="summary-info">
                        <h3 class="summary-number">${{ number_format($currentBalance, 2) }}</h3>
                        <p class="summary-label">Current Balance</p>
                        <div class="summary-trend">
                            @if($currentBalance > 0)
                                <i class="fas fa-arrow-up trend-up"></i>
                                <span class="trend-text">Amount Owed</span>
                            @elseif($currentBalance < 0)
                                <i class="fas fa-arrow-down trend-down"></i>
                                <span class="trend-text">Overpaid</span>
                            @else
                                <i class="fas fa-check-circle trend-neutral"></i>
                                <span class="trend-text">Settled</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Ledger Table -->
    <div class="card modern-card">
        <div class="card-header modern-header">
            <div class="header-content">
                <div class="header-title">
                    <i class="fas fa-book header-icon"></i>
                    <h3 class="card-title">Transaction Ledger</h3>
                </div>
                <div class="header-actions">
                    <!-- Print Form -->
                    <form action="{{ route('payees.print-ledger', $payee->id) }}" method="GET" target="_blank" class="print-form">
                        <div class="print-controls">
                            <div class="date-inputs">
                                <div class="input-group modern-input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text modern-input-addon">
                                            <i class="fas fa-calendar-alt"></i>
                                        </span>
                                    </div>
                                    <input type="date" name="start_date" class="form-control modern-input" placeholder="Start Date">
                                </div>
                                <div class="input-group modern-input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text modern-input-addon">
                                            <i class="fas fa-calendar-check"></i>
                                        </span>
                                    </div>
                                    <input type="date" name="end_date" class="form-control modern-input" placeholder="End Date">
                                </div>
                            </div>
                            <button type="submit" class="btn modern-btn modern-btn-outline btn-sm">
                                <i class="fas fa-print"></i> <span class="btn-text">Print Ledger</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="card-body modern-card-body">
            <!-- Mobile View -->
            <div class="mobile-ledger-container">
                <div id="mobile-ledger-list">
                    <!-- Mobile cards will be populated by JavaScript -->
                </div>
                <div class="mobile-pagination">
                    <!-- Mobile pagination will be added here -->
                </div>
            </div>

            <!-- Desktop View -->
            <div class="desktop-ledger-container">
                <div class="table-responsive modern-table-responsive">
                    <table id="ledger-table" class="table modern-table">
                        <thead class="modern-thead">
                            <tr>
                                <th width="12%">Date</th>
                                <th width="15%">Reference</th>
                                <th width="12%">Type</th>
                                <th width="15%">Category</th>
                                <th width="25%">Description</th>
                                <th width="12%">Amount</th>
                                <th width="12%">Balance</th>
                            </tr>
                        </thead>
                        <tbody class="modern-tbody">
                            <!-- DataTable content -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Mobile Fixed Bottom Actions -->
    <div class="mobile-bottom-actions">
        <div class="mobile-button-group">
            <a href="{{ route('payable-transactions.create') }}?payee_id={{ $payee->id }}" class="btn modern-btn modern-btn-success btn-lg mobile-submit-btn">
                <i class="fas fa-plus"></i> Add Transaction
            </a>
        </div>
    </div>
@stop

@section('additional_css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.25/css/dataTables.bootstrap4.min.css">
    
    <style>
        /* Summary Cards */
        .summary-row {
            margin-bottom: 32px;
        }

        .summary-card {
            background: white;
            border-radius: 16px;
            padding: 24px;
            margin-bottom: 16px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            border: none;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .summary-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
        }

        .summary-card-info::before {
            background: var(--app-topbar-gradient, linear-gradient(135deg, var(--app-primary-dark, #1d4ed8), var(--app-primary, #3b82f6)));
        }

        .summary-card-warning::before {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        }

        .summary-card-danger::before {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        }

        .summary-card-success::before {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        }

        .summary-card-neutral::before {
            background: linear-gradient(135deg, #6b7280 0%, #4b5563 100%);
        }

        .summary-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
        }

        .summary-content {
            display: flex;
            align-items: flex-start;
            gap: 16px;
        }

        .summary-icon {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            color: #6366f1;
            font-size: 24px;
            flex-shrink: 0;
        }

        .summary-card-info .summary-icon {
            color: var(--app-primary, #4f46e5);
        }

        .summary-card-warning .summary-icon {
            color: #f59e0b;
        }

        .summary-card-danger .summary-icon {
            color: #ef4444;
        }

        .summary-card-success .summary-icon {
            color: #10b981;
        }

        .summary-card-neutral .summary-icon {
            color: #6b7280;
        }

        .summary-info {
            flex: 1;
        }

        .summary-title {
            font-size: 20px;
            font-weight: 700;
            color: #1f2937;
            margin: 0 0 8px 0;
            line-height: 1.2;
        }

        .summary-number {
            font-size: 24px;
            font-weight: 700;
            color: #1f2937;
            margin: 0 0 4px 0;
            line-height: 1.2;
        }

        .summary-label {
            font-size: 14px;
            color: #6b7280;
            margin: 0 0 8px 0;
            font-weight: 500;
        }

        .summary-subtitle {
            margin: 0 0 8px 0;
        }

        .summary-contact {
            font-size: 13px;
            color: #6b7280;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .summary-trend {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .trend-up {
            color: #ef4444;
        }

        .trend-down {
            color: #10b981;
        }

        .trend-neutral {
            color: #6b7280;
        }

        .trend-text {
            font-size: 12px;
            color: #6b7280;
            font-weight: 500;
        }

        /* Type badge styling */
        .type-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .type-supplier {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
        }

        .type-individual {
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
            color: white;
        }

        /* Print form styling */
        .print-form {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .print-controls {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
        }

        .date-inputs {
            display: flex;
            gap: 8px;
            align-items: center;
        }

        .modern-input-group {
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            min-width: 160px;
        }

        .modern-input-addon {
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
            color: white;
            border: none;
            font-weight: 600;
            padding: 8px 12px;
            font-size: 12px;
        }

        .modern-input-group .modern-input {
            border-left: none;
            border-radius: 0 8px 8px 0;
            font-size: 13px;
            padding: 8px 12px;
        }

        .modern-input-group .modern-input:focus {
            box-shadow: none;
            border-color: #6366f1;
        }

        /* Mobile Ledger Cards */
        .mobile-ledger-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 16px;
            border: 2px solid #e5e7eb;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
        }

        .mobile-ledger-card:hover {
            border-color: #6366f1;
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.1);
        }

        .mobile-card-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 16px;
        }

        .mobile-transaction-date {
            font-size: 14px;
            font-weight: 600;
            color: #1f2937;
        }

        .mobile-transaction-type {
            font-size: 12px;
            padding: 4px 8px;
            border-radius: 6px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .type-debit {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: white;
        }

        .type-credit {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
        }

        .mobile-card-body {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
            margin-bottom: 16px;
        }

        .mobile-field {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .mobile-field-label {
            font-size: 12px;
            color: #6b7280;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .mobile-field-value {
            font-size: 14px;
            color: #374151;
            font-weight: 500;
        }

        .mobile-amount {
            font-size: 16px;
            font-weight: 700;
        }

        .mobile-balance {
            font-size: 16px;
            font-weight: 700;
            color: #6366f1;
        }

        .mobile-reference {
            font-family: 'Courier New', monospace;
            background: #f3f4f6;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 13px;
        }

        .mobile-description {
            grid-column: 1 / -1;
            padding: 12px;
            background: #f8fafc;
            border-radius: 8px;
            border-left: 4px solid #6366f1;
        }

        /* Desktop specific styles */
        @media (min-width: 769px) {
            .mobile-ledger-container,
            .mobile-bottom-actions {
                display: none !important;
            }

            .desktop-ledger-container {
                display: block !important;
            }

            .btn-text {
                display: inline;
            }

            .print-controls {
                flex-wrap: nowrap;
            }

            .date-inputs {
                flex-direction: row;
            }
        }

        /* Mobile Responsive */
        @media (max-width: 768px) {
            .desktop-ledger-container {
                display: none !important;
            }

            .mobile-ledger-container,
            .mobile-bottom-actions {
                display: block !important;
            }

            .mobile-bottom-actions {
                position: fixed;
                bottom: 0;
                left: 0;
                right: 0;
                background: white;
                padding: 16px;
                box-shadow: 0 -4px 20px rgba(0, 0, 0, 0.1);
                z-index: 1000;
            }

            body {
                padding-bottom: 80px;
            }

            .mobile-button-group {
                display: flex;
                width: 100%;
            }

            .mobile-submit-btn {
                flex: 1;
                font-size: 16px;
                padding: 14px;
                justify-content: center;
            }

            .btn-text {
                display: none;
            }

            .header-actions-group {
                flex-direction: column;
                width: 100%;
                gap: 8px;
            }

            .header-actions-group .btn {
                width: 100%;
                justify-content: center;
            }

            /* Print form mobile adjustments */
            .print-form {
                width: 100%;
            }

            .print-controls {
                flex-direction: column;
                width: 100%;
                gap: 8px;
            }

            .date-inputs {
                flex-direction: column;
                width: 100%;
                gap: 8px;
            }

            .modern-input-group {
                min-width: 100%;
            }

            /* Summary cards mobile layout */
            .summary-card {
                margin-bottom: 12px;
                padding: 20px;
            }

            .summary-content {
                gap: 12px;
            }

            .summary-icon {
                width: 50px;
                height: 50px;
                font-size: 20px;
            }

            .summary-number,
            .summary-title {
                font-size: 20px;
            }

            .summary-label {
                font-size: 13px;
            }

            /* Mobile card adjustments */
            .mobile-ledger-card {
                padding: 16px;
                margin-bottom: 12px;
            }

            .mobile-card-body {
                grid-template-columns: 1fr;
                gap: 12px;
            }
        }

        /* Inherit all other styles from modern design */
        .modern-card {
            background: white;
            border: none;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            margin-bottom: 24px;
            backdrop-filter: blur(10px);
            overflow: hidden;
        }

        .modern-header {
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
            color: white;
            border-radius: 16px 16px 0 0;
            padding: 20px 24px;
            border-bottom: none;
        }

        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            width: 100%;
            flex-wrap: wrap;
            gap: 16px;
        }

        .header-title {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .header-icon {
            font-size: 24px;
            color: white;
        }

        .card-title {
            color: white;
            font-weight: 600;
            margin: 0;
            font-size: 18px;
        }

        .modern-card-body {
            padding: 24px;
            background: white;
        }

        .modern-table-responsive {
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            background: white;
        }

        .modern-table {
            margin-bottom: 0;
            background: white !important;
            color: #1f2937 !important;
            width: 100%;
        }

        .modern-thead {
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%) !important;
            border-bottom: none;
        }

        .modern-thead th {
            border: none !important;
            padding: 18px 16px;
            font-weight: 600;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: white !important;
            white-space: nowrap;
            background: transparent !important;
        }

        .modern-tbody {
            background: white !important;
        }

        .modern-tbody tr {
            transition: all 0.2s ease;
            border-bottom: 1px solid #f1f5f9;
            background: white !important;
        }

        .modern-tbody tr:hover {
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%) !important;
        }

        .modern-tbody td {
            padding: 16px;
            vertical-align: middle;
            border: none !important;
            font-size: 14px;
            color: #374151 !important;
            background: transparent !important;
        }

        .modern-btn {
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 14px;
            border: 2px solid transparent;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            cursor: pointer;
        }

        .modern-btn-success {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            border-color: #10b981;
        }

        .modern-btn-success:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(16, 185, 129, 0.4);
            color: white;
        }

        .modern-btn-outline {
            background: white;
            color: #6366f1;
            border-color: #6366f1;
        }

        .modern-btn-outline:hover {
            background: #6366f1;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(99, 102, 241, 0.3);
        }

        .header-actions-group {
            display: flex;
            gap: 12px;
            align-items: center;
        }

        .modern-alert {
            border: none;
            border-radius: 12px;
            padding: 16px 20px;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .modern-alert i {
            font-size: 18px;
        }

        .modern-input {
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            padding: 10px 15px;
            background: white;
            color: #374151;
            transition: all 0.2s ease;
            font-size: 14px;
        }

        .modern-input:focus {
            outline: none;
            border-color: #6366f1;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
        }

        /* Loading state */
        .loading-spinner {
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 40px;
            color: #6b7280;
        }

        .loading-spinner i {
            font-size: 24px;
            margin-right: 8px;
        }

        /* Empty state */
        .empty-state {
            text-align: center;
            padding: 40px;
            color: #6b7280;
        }

        .empty-state i {
            font-size: 48px;
            margin-bottom: 16px;
            color: #d1d5db;
        }

        .empty-state h4 {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 8px;
            color: #374151;
        }

        .empty-state p {
            font-size: 14px;
            margin-bottom: 20px;
        }

        /* Pagination styling */
        .pagination-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 16px 0;
            margin-top: 16px;
            border-top: 1px solid #e5e7eb;
        }

        .page-info {
            font-size: 14px;
            color: #6b7280;
            font-weight: 500;
        }

        .mobile-page-btn {
            font-size: 12px;
            padding: 8px 12px;
        }

        @media (max-width: 768px) {
            .pagination-container {
                flex-direction: column;
                gap: 12px;
            }

            .mobile-page-btn {
                width: 120px;
            }
        }
    </style>
@stop

@section('additional_js')
    <script src="https://cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.25/js/dataTables.bootstrap4.min.js"></script>
    
    <script>
        $(document).ready(function() {
            let ledgerTable;
            let currentMobileData = [];
            let currentMobilePage = 1;
            const itemsPerPage = 10;
            
            // Better mobile detection
            function isMobileView() {
                return window.innerWidth <= 768;
            }
            
            // Initialize DataTable for desktop
            function initializeDataTable() {
                if (ledgerTable) {
                    ledgerTable.destroy();
                }
                
                ledgerTable = $('#ledger-table').DataTable({
                    processing: true,
                    serverSide: true,
                    order: [[0, 'desc']],
                    ajax: {
                        url: "{{ route('payees.ledger', $payee->id) }}",
                        data: function(d) {
                            d.mobile = isMobileView();
                        }
                    },
                    columns: [
                        { 
                            data: 'transaction_date', 
                            name: 'transaction_date',
                            render: function(data, type, row) {
                                return '<span class="text-muted">' + new Date(data).toLocaleDateString() + '</span>';
                            }
                        },
                        { 
                            data: 'reference_no', 
                            name: 'reference_no',
                            render: function(data, type, row) {
                                return '<span class="reference-code">' + (data || 'N/A') + '</span>';
                            }
                        },
                        { 
                            data: 'transaction_type', 
                            name: 'transaction_type',
                            render: function(data, type, row) {
                                const badgeClass = data === 'debit' ? 'type-debit' : 'type-credit';
                                const icon = data === 'debit' ? 'minus' : 'plus';
                                return '<span class="mobile-transaction-type ' + badgeClass + '">' +
                                       '<i class="fas fa-' + icon + '"></i> ' +
                                       data.charAt(0).toUpperCase() + data.slice(1) +
                                       '</span>';
                            }
                        },
                        { 
                            data: 'category', 
                            name: 'category',
                            render: function(data, type, row) {
                                return data || '<span class="text-muted">N/A</span>';
                            }
                        },
                        { 
                            data: 'description', 
                            name: 'description',
                            render: function(data, type, row) {
                                return data || '<span class="text-muted">No description</span>';
                            }
                        },
                        { 
                            data: 'amount', 
                            name: 'amount',
                            render: function(data, type, row) {
                                const amount = parseFloat(data) || 0;
                                const color = row.transaction_type === 'debit' ? 'text-danger' : 'text-success';
                                const sign = row.transaction_type === 'debit' ? '-' : '+';
                                return '<span class="mobile-amount ' + color + '">' + sign + '$' + Math.abs(amount).toFixed(2) + '</span>';
                            }
                        },
                        { 
                            data: 'balance', 
                            name: 'balance',
                            render: function(data, type, row) {
                                const balance = parseFloat(data) || 0;
                                const color = balance > 0 ? 'text-danger' : (balance < 0 ? 'text-success' : 'text-muted');
                                return '<span class="mobile-balance ' + color + '">$' + balance.toFixed(2) + '</span>';
                            }
                        }
                    ],
                    pageLength: 25,
                    responsive: false,
                    language: {
                        processing: '<div class="loading-spinner"><i class="fas fa-spinner fa-spin"></i> Loading transactions...</div>',
                        emptyTable: '<div class="empty-state"><i class="fas fa-receipt"></i><h4>No Transactions Found</h4><p>No transactions have been recorded for this payee yet.</p></div>',
                        zeroRecords: '<div class="empty-state"><i class="fas fa-search"></i><h4>No Matching Records</h4><p>Try adjusting your search criteria.</p></div>'
                    },
                    drawCallback: function(settings) {
                        // Re-initialize tooltips and other UI elements
                        $('[title]').tooltip();
                    }
                });
            }
            
            // Load mobile data
            function loadMobileData(page = 1) {
                $('#mobile-ledger-list').html('<div class="loading-spinner"><i class="fas fa-spinner fa-spin"></i> Loading transactions...</div>');
                
                $.ajax({
                    url: "{{ route('payees.ledger', $payee->id) }}",
                    data: {
                        mobile: true,
                        page: page,
                        length: itemsPerPage
                    },
                    success: function(response) {
                        currentMobileData = response.data;
                        currentMobilePage = page;
                        renderMobileCards();
                        renderMobilePagination(response.recordsFiltered);
                    },
                    error: function() {
                        $('#mobile-ledger-list').html(
                            '<div class="empty-state">' +
                            '<i class="fas fa-exclamation-triangle"></i>' +
                            '<h4>Error Loading Data</h4>' +
                            '<p>Please try refreshing the page.</p>' +
                            '</div>'
                        );
                    }
                });
            }
            
            // Render mobile cards
            function renderMobileCards() {
                if (currentMobileData.length === 0) {
                    $('#mobile-ledger-list').html(
                        '<div class="empty-state">' +
                        '<i class="fas fa-receipt"></i>' +
                        '<h4>No Transactions Found</h4>' +
                        '<p>No transactions have been recorded for this payee yet.</p>' +
                        '</div>'
                    );
                    return;
                }
                
                let html = '';
                currentMobileData.forEach(function(transaction) {
                    const amount = parseFloat(transaction.amount) || 0;
                    const balance = parseFloat(transaction.balance) || 0;
                    const isDebit = transaction.transaction_type === 'debit';
                    const amountClass = isDebit ? 'text-danger' : 'text-success';
                    const balanceClass = balance > 0 ? 'text-danger' : (balance < 0 ? 'text-success' : 'text-muted');
                    const typeClass = isDebit ? 'type-debit' : 'type-credit';
                    const sign = isDebit ? '-' : '+';
                    const icon = isDebit ? 'minus' : 'plus';
                    
                    html += '<div class="mobile-ledger-card">' +
                            '<div class="mobile-card-header">' +
                            '<div class="mobile-transaction-date">' + new Date(transaction.transaction_date).toLocaleDateString() + '</div>' +
                            '<span class="mobile-transaction-type ' + typeClass + '">' +
                            '<i class="fas fa-' + icon + '"></i> ' +
                            transaction.transaction_type.charAt(0).toUpperCase() + transaction.transaction_type.slice(1) +
                            '</span>' +
                            '</div>' +
                            '<div class="mobile-card-body">' +
                            '<div class="mobile-field">' +
                            '<span class="mobile-field-label">Reference</span>' +
                            '<span class="mobile-field-value mobile-reference">' + (transaction.reference_no || 'N/A') + '</span>' +
                            '</div>' +
                            '<div class="mobile-field">' +
                            '<span class="mobile-field-label">Category</span>' +
                            '<span class="mobile-field-value">' + (transaction.category || 'N/A') + '</span>' +
                            '</div>' +
                            '<div class="mobile-field">' +
                            '<span class="mobile-field-label">Amount</span>' +
                            '<span class="mobile-field-value mobile-amount ' + amountClass + '">' + sign + '$' + Math.abs(amount).toFixed(2) + '</span>' +
                            '</div>' +
                            '<div class="mobile-field">' +
                            '<span class="mobile-field-label">Balance</span>' +
                            '<span class="mobile-field-value mobile-balance ' + balanceClass + '">$' + balance.toFixed(2) + '</span>' +
                            '</div>';
                    
                    if (transaction.description) {
                        html += '<div class="mobile-field mobile-description">' +
                                '<span class="mobile-field-label">Description</span>' +
                                '<span class="mobile-field-value">' + transaction.description + '</span>' +
                                '</div>';
                    }
                    
                    html += '</div></div>';
                });
                
                $('#mobile-ledger-list').html(html);
            }
            
            // Render mobile pagination
            function renderMobilePagination(totalRecords) {
                const totalPages = Math.ceil(totalRecords / itemsPerPage);
                
                if (totalPages <= 1) {
                    $('.mobile-pagination').html('');
                    return;
                }
                
                let paginationHtml = '<div class="pagination-container">';
                
                // Previous button
                if (currentMobilePage > 1) {
                    paginationHtml += '<button class="btn modern-btn modern-btn-outline btn-sm mobile-page-btn" data-page="' + (currentMobilePage - 1) + '">' +
                                     '<i class="fas fa-chevron-left"></i> Previous' +
                                     '</button>';
                }
                
                // Page info
                paginationHtml += '<span class="page-info">Page ' + currentMobilePage + ' of ' + totalPages + '</span>';
                
                // Next button
                if (currentMobilePage < totalPages) {
                    paginationHtml += '<button class="btn modern-btn modern-btn-outline btn-sm mobile-page-btn" data-page="' + (currentMobilePage + 1) + '">' +
                                     'Next <i class="fas fa-chevron-right"></i>' +
                                     '</button>';
                }
                
                paginationHtml += '</div>';
                $('.mobile-pagination').html(paginationHtml);
            }
            
            // Initialize based on screen size
            function initializeView() {
                if (isMobileView()) {
                    loadMobileData();
                } else {
                    initializeDataTable();
                }
            }
            
            // Mobile pagination click handler
            $(document).on('click', '.mobile-page-btn', function() {
                const page = $(this).data('page');
                loadMobileData(page);
            });
            
            // Window resize handler
            $(window).on('resize', function() {
                setTimeout(function() {
                    initializeView();
                }, 100);
            });
            
            // Initialize on page load
            initializeView();
        });
    </script>
    
    <style>
        /* Additional styling for reference codes */
        .reference-code {
            font-family: 'Courier New', monospace;
            background: #f3f4f6;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 13px;
            color: #374151;
        }
    </style>
@stop
