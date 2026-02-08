@extends('layouts.modern-admin')

@section('title', 'Customer Ledger')

@section('page_title', 'Customer Ledger: ' . $customers->name)

@section('header_actions')
    <div class="header-actions-group">
        <a href="{{ route('customers.ledger.print', $customers->id) }}" target="_blank" class="btn modern-btn modern-btn-secondary">
            <i class="fas fa-print"></i> <span class="btn-text">Print</span>
        </a>
        <a href="{{ route('transactions.create', ['customer_id' => $customers->id]) }}" class="btn modern-btn modern-btn-primary">
            <i class="fas fa-plus"></i> <span class="btn-text">New Transaction</span>
        </a>
        <div class="dropdown">
            <button class="btn modern-btn modern-btn-outline dropdown-toggle" type="button" data-toggle="dropdown">
                <i class="fas fa-ellipsis-v"></i> <span class="btn-text">More</span>
            </button>
            <div class="dropdown-menu">
                <a class="dropdown-item" href="{{ route('transactions.index') }}">
                    <i class="fas fa-list"></i> All Transactions
                </a>
                <a class="dropdown-item" href="{{ route('customers.show', $customers->id) }}">
                    <i class="fas fa-user"></i> View Customer
                </a>
                <a class="dropdown-item" href="{{ route('customers.edit', $customers->id) }}">
                    <i class="fas fa-edit"></i> Edit Customer
                </a>
            </div>
        </div>
    </div>
@stop

@section('page_content')
    <!-- Customer Overview Cards -->
    <div class="overview-grid">
        <div class="overview-card customer-info">
            <div class="card-icon">
                <i class="fas fa-user"></i>
            </div>
            <div class="card-content">
                <h3 class="customer-name">{{ $customers->name }}</h3>
                <p class="customer-phone">{{ $customers->phone ?? 'No phone' }}</p>
            </div>
        </div>
        
        <div class="overview-card balance-info">
            <div class="balance-item">
                <span class="balance-label">Opening</span>
                <span class="balance-amount">৳{{ number_format($customers->opening_balance, 2) }}</span>
            </div>
            <div class="balance-item">
                <span class="balance-label">Outstanding</span>
                <span class="balance-amount {{ $closingBalance > 0 ? 'negative' : ($closingBalance < 0 ? 'positive' : 'zero') }}">
                    ৳{{ number_format($closingBalance, 2) }}
                </span>
            </div>
        </div>
        
        <div class="overview-card stats-info">
            <div class="stat-item">
                <span class="stat-number">{{ $transactions->count() }}</span>
                <span class="stat-label">Transactions</span>
            </div>
        </div>
    </div>

    <!-- Quick Filters -->
    <div class="filter-section">
        <div class="filter-header" id="filter-toggle">
            <h4><i class="fas fa-filter"></i> Filters</h4>
            <div class="filter-controls">
                <span class="filter-count" id="active-filters">0 filters</span>
                <i class="fas fa-chevron-down toggle-icon"></i>
            </div>
        </div>
        
        <div class="filter-content" id="filter-section" style="display: none;">
            <form id="ledger-filter">
                <div class="filter-row">
                    <div class="filter-col">
                        <label>From Date</label>
                        <input type="date" class="form-control" id="date-range-start" name="date_from">
                    </div>
                    <div class="filter-col">
                        <label>To Date</label>
                        <input type="date" class="form-control" id="date-range-end" name="date_to">
                    </div>
                </div>
                
                <div class="filter-row">
                    <div class="filter-col">
                        <label>Type</label>
                        <select class="form-control" id="transaction-type" name="type">
                            <option value="">All Types</option>
                            <option value="debit">Payment</option>
                            <option value="credit">Charge</option>
                        </select>
                    </div>
                    <div class="filter-col">
                        <label>Method</label>
                        <select class="form-control" id="payment-method" name="method">
                            <option value="">All Methods</option>
                            <option value="cash">Cash</option>
                            <option value="bank">Bank</option>
                            <option value="mobile_bank">Mobile Bank</option>
                            <option value="cheque">Cheque</option>
                        </select>
                    </div>
                </div>
                
                <div class="filter-actions">
                    <button type="button" id="apply-filter" class="btn btn-primary">Apply</button>
                    <button type="button" id="reset-filter" class="btn btn-secondary">Reset</button>
                    <button type="button" id="quick-month" class="btn btn-outline">This Month</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Transactions Table -->
    <div class="transactions-section">
        <div class="section-header">
            <h4><i class="fas fa-list"></i> Transaction History</h4>
            <div class="section-actions">
                <div class="dropdown">
                    <button class="btn btn-outline btn-sm dropdown-toggle" type="button" data-toggle="dropdown">
                        <i class="fas fa-download"></i> Export
                    </button>
                    <div class="dropdown-menu">
                        <a class="dropdown-item export-btn" href="#" data-type="csv">
                            <i class="fas fa-file-csv"></i> CSV
                        </a>
                        <a class="dropdown-item export-btn" href="#" data-type="excel">
                            <i class="fas fa-file-excel"></i> Excel
                        </a>
                        <a class="dropdown-item export-btn" href="#" data-type="pdf">
                            <i class="fas fa-file-pdf"></i> PDF
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Desktop Table -->
        <div class="table-container desktop-table">
            <table id="ledger-table" class="table">
    <thead>
        <tr>
            <th>Date</th>
            <th>Purpose</th>
            <th>Method</th>
            <th>Reference</th>
            <th>Payment</th>
            <th>Discount</th>
            <th>Total Debit</th>
            <th>Credit</th>
            <th>Balance</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        @php
            // Sort transactions chronologically (oldest first)
            $chronologicalTransactions = $transactions->sortBy(function($transaction) {
                return $transaction->created_at->timestamp . '.' . str_pad($transaction->id, 10, '0', STR_PAD_LEFT);
            });
            
            // Calculate running balances
            $transactionBalances = [];
            $runningBalance = $customers->opening_balance;
            
            foreach($chronologicalTransactions as $transaction) {
                if ($transaction->type == 'credit') {
                    $runningBalance += $transaction->amount;
                } else {
                    $runningBalance -= ($transaction->amount + ($transaction->discount_amount ?? 0));
                }
                $transactionBalances[$transaction->id] = $runningBalance;
            }
            
            // Reset for display
            $runningBalance = $customers->opening_balance;
        @endphp
        
        {{-- Opening Balance Row - EXACTLY 10 columns --}}
        <tr class="opening-balance-row">
            <td>
                <div class="date-info">
                    <div class="date"><strong>Opening</strong></div>
                    <div class="time">{{ $customers->created_at->format('Y-m-d') }}</div>
                </div>
            </td>
            <td><span class="purpose opening">Opening Balance</span></td>
            <td><span class="method-badge">-</span></td>
            <td><span class="reference">-</span></td>
            <td><span class="amount">-</span></td>
            <td><span class="amount">-</span></td>
            <td><span class="amount">-</span></td>
            <td><span class="amount">-</span></td>
            <td><span class="balance opening">৳{{ number_format($runningBalance, 2) }}</span></td>
            <td><span class="no-actions">-</span></td>
        </tr>
        
        {{-- Transaction Rows - EXACTLY 10 columns each --}}
        @foreach($chronologicalTransactions as $transaction)
            @php
                $hasDiscount = ($transaction->discount_amount ?? 0) > 0;
                $currentBalance = $transactionBalances[$transaction->id] ?? $runningBalance;
                $totalDebit = $transaction->type == 'debit' ? $transaction->amount + ($transaction->discount_amount ?? 0) : 0;
            @endphp
            <tr class="transaction-row {{ $hasDiscount ? 'discount-row' : '' }}" data-transaction-id="{{ $transaction->id }}">
                {{-- Column 1: Date --}}
                <td>
                    <div class="date-info">
                        <div class="date">{{ $transaction->created_at->format('Y-m-d') }}</div>
                        <div class="time">{{ $transaction->created_at->format('H:i') }}</div>
                    </div>
                </td>
                
                {{-- Column 2: Purpose --}}
                <td>
                    <div class="purpose-info">
                        <div class="purpose">{{ $transaction->purpose ?? 'N/A' }}</div>
                        @if($hasDiscount && $transaction->discount_reason)
                            <div class="discount-note">
                                <i class="fas fa-tag"></i> Discount Applied
                            </div>
                        @endif
                    </div>
                </td>
                
                {{-- Column 3: Method --}}
                <td><span class="method-badge">{{ ucfirst(str_replace('_', ' ', $transaction->method ?? 'N/A')) }}</span></td>
                
                {{-- Column 4: Reference --}}
                <td>
                    @if($transaction->invoice_id && Str::contains($transaction->reference ?? '', 'INV-'))
                        <button class="reference-link invoice-link" 
                                data-invoice-id="{{ $transaction->invoice_id }}" 
                                data-toggle="modal" 
                                data-target="#invoiceModal">
                            {{ $transaction->reference }}
                        </button>
                    @else
                        <span class="reference">{{ $transaction->reference ?? '-' }}</span>
                    @endif
                </td>
                
                {{-- Column 5: Payment --}}
                <td>
                    @if($transaction->type == 'debit')
                        <span class="amount debit">৳{{ number_format($transaction->amount, 2) }}</span>
                    @else
                        <span class="amount">-</span>
                    @endif
                </td>
                
                {{-- Column 6: Discount --}}
                <td>
                    @if($hasDiscount)
                        <span class="amount discount">৳{{ number_format($transaction->discount_amount, 2) }}</span>
                    @else
                        <span class="amount">-</span>
                    @endif
                </td>
                
                {{-- Column 7: Total Debit --}}
                <td>
                    @if($transaction->type == 'debit')
                        <span class="amount debit">৳{{ number_format($totalDebit, 2) }}</span>
                    @else
                        <span class="amount">-</span>
                    @endif
                </td>
                
                {{-- Column 8: Credit --}}
                <td>
                    @if($transaction->type == 'credit')
                        <span class="amount credit">৳{{ number_format($transaction->amount, 2) }}</span>
                    @else
                        <span class="amount">-</span>
                    @endif
                </td>
                
                {{-- Column 9: Balance --}}
                <td>
                    <span class="balance {{ $currentBalance >= 0 ? 'positive' : 'negative' }}">
                        ৳{{ number_format($currentBalance, 2) }}
                    </span>
                </td>
                
                {{-- Column 10: Actions --}}
                <td>
                    <div class="action-buttons">
                        <a href="{{ route('transactions.show', $transaction->id) }}" class="btn-action" title="View">
                            <i class="fas fa-eye"></i>
                        </a>
                        @if(!$transaction->invoice_id)
                            <a href="{{ route('transactions.edit', $transaction->id) }}" class="btn-action" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                        @endif
                        @if($transaction->invoice_id)
                            <a href="{{ route('invoices.show', $transaction->invoice_id) }}" class="btn-action" title="Invoice">
                                <i class="fas fa-file-invoice"></i>
                            </a>
                        @endif
                    </div>
                </td>
            </tr>
        @endforeach
    </tbody>
</table>


        </div>
        
<div class="mobile-cards">
    @foreach($chronologicalTransactions as $transaction)
        @php
            $currentBalance = $transactionBalances[$transaction->id];
        @endphp
        <div class="transaction-card" data-transaction-id="{{ $transaction->id }}">
            <div class="card-header">
                <div class="transaction-date">
                    <strong>{{ $transaction->created_at->format('M d, Y') }}</strong>
                    <span>{{ $transaction->created_at->format('H:i') }}</span>
                </div>
                <div class="transaction-type">
                    <span class="type-badge {{ $transaction->type }}">
                        {{ $transaction->type == 'credit' ? 'Charge' : 'Payment' }}
                    </span>
                </div>
            </div>
            
            <div class="card-body">
                <div class="transaction-info">
                    <div class="info-row">
                        <span class="label">Purpose:</span>
                        <span class="value">{{ $transaction->purpose ?? 'N/A' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="label">Method:</span>
                        <span class="value">{{ ucfirst(str_replace('_', ' ', $transaction->method)) }}</span>
                    </div>
                    @if($transaction->reference)
                    <div class="info-row">
                        <span class="label">Reference:</span>
                        <span class="value">{{ $transaction->reference }}</span>
                    </div>
                    @endif
                </div>
                
                <div class="amount-info">
                    @if($transaction->type == 'credit')
                        <div class="amount-row credit">
                            <span class="amount-label">Credit:</span>
                            <span class="amount-value">৳{{ number_format($transaction->amount, 2) }}</span>
                        </div>
                    @else
                        <div class="amount-row debit">
                            <span class="amount-label">Payment:</span>
                            <span class="amount-value">৳{{ number_format($transaction->amount, 2) }}</span>
                        </div>
                        @if($transaction->discount_amount > 0)
                        <div class="amount-row discount">
                            <span class="amount-label">Discount:</span>
                            <span class="amount-value">৳{{ number_format($transaction->discount_amount, 2) }}</span>
                        </div>
                        @endif
                    @endif
                    
                    <div class="balance-row">
                        <span class="balance-label">Balance:</span>
                        <span class="balance-value {{ $currentBalance >= 0 ? 'positive' : 'negative' }}">
                            ৳{{ number_format($currentBalance, 2) }}
                        </span>
                    </div>
                </div>
            </div>
            
            <div class="card-actions">
                <a href="{{ route('transactions.show', $transaction->id) }}" class="action-btn">
                    <i class="fas fa-eye"></i> View
                </a>
                @if(!$transaction->invoice_id)
                    <a href="{{ route('transactions.edit', $transaction->id) }}" class="action-btn">
                        <i class="fas fa-edit"></i> Edit
                    </a>
                @endif
                @if($transaction->invoice_id)
                    <a href="{{ route('invoices.show', $transaction->invoice_id) }}" class="action-btn">
                        <i class="fas fa-file-invoice"></i> Invoice
                    </a>
                @endif
            </div>
        </div>
    @endforeach
            
  <!-- Opening Balance Card -->
    <div class="transaction-card opening-card">
        <div class="card-header">
            <div class="transaction-date">
                <strong>Opening Balance</strong>
                <span>{{ $customers->created_at->format('M d, Y') }}</span>
            </div>
        </div>
        <div class="card-body">
            <div class="balance-row opening">
                <span class="balance-label">Opening Balance:</span>
                <span class="balance-value">৳{{ number_format($customers->opening_balance, 2) }}</span>
            </div>
        </div>
    </div>
</div>

    <!-- Invoice Details Modal -->
    <div class="modal fade" id="invoiceModal" tabindex="-1" role="dialog" aria-labelledby="invoiceModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="invoiceModalLabel">
                        <i class="fas fa-file-invoice"></i> Invoice Details
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="invoiceModalBody">
                    <div class="text-center">
                        <div class="spinner-border text-primary" role="status">
                            <span class="sr-only">Loading...</span>
                        </div>
                        <p class="mt-2">Loading invoice details...</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <a href="#" id="viewFullInvoice" target="_blank" class="btn btn-primary">
                        <i class="fas fa-external-link-alt"></i> View Full Invoice
                    </a>
                </div>
            </div>
        </div>
    </div>
@stop

@section('additional_css')
<link href="{{ asset('css/modern-admin.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap4.min.css">
    
    {{-- Fix the CSS 404 error --}}
    <link rel="stylesheet" href="{{ asset('css/admin_custom.css') }}">
    
    <style>
        /* Base Styles */
        * {
            box-sizing: border-box;
        }

        .header-actions-group {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .btn-text {
            display: inline;
        }

        /* Overview Cards */
        .overview-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 16px;
            margin-bottom: 24px;
        }

        .overview-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            border: 1px solid #e5e7eb;
        }

        .overview-card.customer-info {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .card-icon {
            width: 48px;
            height: 48px;
            background: #6366f1;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 20px;
            flex-shrink: 0;
        }

        .customer-name {
            font-size: 20px;
            font-weight: 600;
            margin: 0 0 4px 0;
            color: #1f2937;
        }

        .customer-phone {
            font-size: 14px;
            color: #6b7280;
            margin: 0;
        }

        .balance-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .balance-item {
            text-align: center;
        }

        .balance-label {
            display: block;
            font-size: 12px;
            color: #6b7280;
            text-transform: uppercase;
            margin-bottom: 4px;
            font-weight: 500;
        }

        .balance-amount {
            display: block;
            font-size: 18px;
            font-weight: 700;
        }

        .balance-amount.positive {
            color: #059669;
        }

        .balance-amount.negative {
            color: #dc2626;
        }

        .stats-info {
            text-align: center;
        }

        .stat-number {
            display: block;
            font-size: 24px;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 4px;
        }

        .stat-label {
            font-size: 12px;
            color: #6b7280;
            text-transform: uppercase;
            font-weight: 500;
        }

        /* Filter Section */
        .filter-section {
            background: white;
            border-radius: 12px;
            margin-bottom: 24px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            border: 1px solid #e5e7eb;
        }

        .filter-header {
            padding: 16px 20px;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            justify-content: space-between;
            align-items: center;
            cursor: pointer;
            background: #f8fafc;
            border-radius: 12px 12px 0 0;
        }

        .filter-header h4 {
            margin: 0;
            font-size: 16px;
            font-weight: 600;
            color: #1f2937;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .filter-controls {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 14px;
            color: #6b7280;
        }

        .toggle-icon {
            transition: transform 0.2s;
        }

        .filter-header.expanded .toggle-icon {
            transform: rotate(180deg);
        }

        .filter-content {
            padding: 20px;
        }

        .filter-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
            margin-bottom: 16px;
        }

        .filter-col label {
            display: block;
            font-size: 14px;
            font-weight: 500;
            color: #374151;
            margin-bottom: 4px;
        }

        .form-control {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            font-size: 14px;
        }

        .filter-actions {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            padding-top: 16px;
            border-top: 1px solid #e5e7eb;
        }

        .btn {
            padding: 8px 16px;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 500;
            border: 1px solid;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.2s;
        }

        .btn-primary {
            background: #6366f1;
            color: white;
            border-color: #6366f1;
        }

        .btn-primary:hover {
            background: #5855eb;
        }

        .btn-secondary {
            background: #6b7280;
            color: white;
            border-color: #6b7280;
        }

        .btn-outline {
            background: white;
            color: #6366f1;
            border-color: #6366f1;
        }

        /* Transactions Section */
        .transactions-section {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            border: 1px solid #e5e7eb;
        }

        .section-header {
            padding: 16px 20px;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #f8fafc;
            border-radius: 12px 12px 0 0;
        }

        .section-header h4 {
            margin: 0;
            font-size: 16px;
            font-weight: 600;
            color: #1f2937;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        /* Desktop Table */
        .desktop-table {
            display: block;
        }

        .table-container {
            overflow-x: auto;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin: 0;
        }

        .table th {
            background: #f8fafc;
            padding: 12px 8px;
            font-size: 12px;
            font-weight: 600;
            color: #374151;
            text-transform: uppercase;
            border-bottom: 1px solid #e5e7eb;
            white-space: nowrap;
        }

        .table td {
            padding: 12px 8px;
            border-bottom: 1px solid #f3f4f6;
            font-size: 14px;
            vertical-align: middle;
        }

        .table tr:hover {
            background: #f9fafb;
        }

        .date-info .date {
            font-weight: 500;
            color: #1f2937;
        }

        .date-info .time {
            font-size: 12px;
            color: #6b7280;
        }

        .purpose-info .purpose {
            font-weight: 500;
            color: #1f2937;
        }

        .discount-note {
            font-size: 11px;
            color: #059669;
            margin-top: 2px;
        }

        .method-badge {
            background: #e5e7eb;
            color: #374151;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 500;
        }

        .reference-link {
            background: none;
            border: none;
            color: #6366f1;
            text-decoration: underline;
            cursor: pointer;
            padding: 0;
            font-family: monospace;
            font-size: 12px;
        }

        .reference {
            font-family: monospace;
            font-size: 12px;
            color: #6b7280;
        }

        .amount {
            font-weight: 600;
            text-align: right;
        }

        .amount.debit {
            color: #dc2626;
        }

        .amount.credit {
            color: #059669;
        }

        .amount.discount {
            color: #d97706;
        }

        .balance {
            font-weight: 700;
            text-align: right;
            padding: 4px 8px;
            border-radius: 4px;
        }

        .balance.positive {
            background: #ecfdf5;
            color: #059669;
        }

        .balance.negative {
            background: #fef2f2;
            color: #dc2626;
        }

        .balance.opening {
            background: #f0f9ff;
            color: #0284c7;
        }

        .action-buttons {
            display: flex;
            gap: 4px;
        }

        .btn-action {
            width: 28px;
            height: 28px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f3f4f6;
            color: #6b7280;
            border-radius: 4px;
            text-decoration: none;
            font-size: 12px;
            transition: all 0.2s;
        }

        .btn-action:hover {
            background: #6366f1;
            color: white;
        }

        .opening-balance-row {
    background: #fef3c7 !important;
}
.opening-balance-row td {
    color: #92400e;
    font-weight: 600;
}
.discount-row {
    background: #d1fae5 !important;
}

.discount-row td {
    color: #065f46;
}
        .purpose.opening {
            background: #6366f1;
            color: white;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
        }

        /* Mobile Cards */
        .mobile-cards {
            display: none;
            padding: 16px;
            gap: 16px;
        }

        .transaction-card {
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            margin-bottom: 12px;
            overflow: hidden;
        }

        .transaction-card .card-header {
            background: #f8fafc;
            padding: 12px 16px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #e5e7eb;
        }

        .transaction-date strong {
            display: block;
            font-size: 14px;
            color: #1f2937;
        }

        .transaction-date span {
            font-size: 12px;
            color: #6b7280;
        }

        .type-badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .type-badge.credit {
            background: #ecfdf5;
            color: #059669;
        }

        .type-badge.debit {
            background: #fef2f2;
            color: #dc2626;
        }

        .transaction-card .card-body {
            padding: 16px;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
        }

        .info-row .label {
            font-size: 12px;
            color: #6b7280;
            font-weight: 500;
        }

        .info-row .value {
            font-size: 12px;
            color: #1f2937;
            font-weight: 500;
        }

        .amount-info {
            margin-top: 12px;
            padding-top: 12px;
            border-top: 1px solid #f3f4f6;
        }

        .amount-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 6px;
        }

        .amount-row.credit .amount-value {
            color: #059669;
            font-weight: 600;
        }

        .amount-row.debit .amount-value {
            color: #dc2626;
            font-weight: 600;
        }

        .amount-row.discount .amount-value {
            color: #d97706;
            font-weight: 600;
        }

        .balance-row {
            display: flex;
            justify-content: space-between;
            padding: 8px;
            background: #f8fafc;
            border-radius: 4px;
            margin-top: 8px;
        }

        .balance-row.opening {
            background: #f0f9ff;
        }

        .balance-value.positive {
            color: #059669;
            font-weight: 700;
        }

        .balance-value.negative {
            color: #dc2626;
            font-weight: 700;
        }

        .card-actions {
            padding: 12px 16px;
            background: #f8fafc;
            border-top: 1px solid #e5e7eb;
            display: flex;
            gap: 8px;
        }

        .action-btn {
            flex: 1;
            padding: 8px 12px;
            background: white;
            color: #6366f1;
            border: 1px solid #6366f1;
            border-radius: 4px;
            text-decoration: none;
            font-size: 12px;
            text-align: center;
            transition: all 0.2s;
        }

        .action-btn:hover {
            background: #6366f1;
            color: white;
        }
.transaction-note {
    font-size: 11px;
    color: #6b7280;
    font-style: italic;
    margin-top: 3px;
}

/* Table footer styling */
.table tfoot {
    background: #f1f5f9;
}

.table tfoot td {
    padding: 12px 8px;
    font-weight: 600;
    color: #1f2937;
    border-bottom: none;
    border-top: 2px solid #374151;
    font-size: 12px;
}
        .opening-card {
            border: 2px solid #6366f1;
            background: #f0f9ff;
        }

        .opening-card .card-header {
            background: #6366f1;
            color: white;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .header-actions-group {
                flex-direction: column;
            }

            .btn-text {
                display: inline;
            }

            .overview-grid {
                grid-template-columns: 1fr;
                gap: 12px;
            }

            .balance-info {
                flex-direction: column;
                gap: 16px;
            }

            .filter-row {
                grid-template-columns: 1fr;
                gap: 12px;
            }

            .filter-actions {
                flex-direction: column;
            }

            .filter-actions .btn {
                width: 100%;
                justify-content: center;
            }

            .section-header {
                flex-direction: column;
                gap: 12px;
                align-items: flex-start;
            }

            .desktop-table {
                display: none;
            }

            .mobile-cards {
                display: block;
            }
        }

        @media (max-width: 480px) {
            .overview-card.customer-info {
                flex-direction: column;
                text-align: center;
            }

            .customer-name {
                font-size: 18px;
            }

            .filter-content {
                padding: 16px;
            }

            .section-header {
                padding: 12px 16px;
            }

            .transaction-card .card-header,
            .transaction-card .card-body,
            .card-actions {
                padding: 12px;
            }
        }

        /* DataTables Responsive Overrides */
        .dataTables_wrapper {
            padding: 16px;
        }

        .dataTables_length,
        .dataTables_filter,
        .dataTables_info,
        .dataTables_paginate {
            margin: 8px 0;
        }

        .dataTables_length select,
        .dataTables_filter input {
            padding: 4px 8px;
            border: 1px solid #d1d5db;
            border-radius: 4px;
            margin: 0 4px;
        }

        .paginate_button {
            padding: 6px 12px !important;
            margin: 0 2px !important;
            border: 1px solid #d1d5db !important;
            border-radius: 4px !important;
        }

        .paginate_button.current {
            background: #6366f1 !important;
            color: white !important;
            border-color: #6366f1 !important;
        }
    </style>
@stop

@section('additional_js')
    <script src="{{ asset('js/modern-admin.js') }}"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap4.min.js"></script>
    
    <script>
        $(document).ready(function() {
            // Initialize DataTable
            const table = $('#ledger-table').DataTable({
                order: [],
                pageLength: 25,
                lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
                dom: 'rtip',
                responsive: true,
                language: {
                    emptyTable: 'No transactions found',
                    zeroRecords: 'No transactions match your filters'
                },
                columnDefs: [
                    { "orderable": false, "targets": [0, 9] }
                ]
            });

            // Filter toggle
            $('#filter-toggle').on('click', function() {
                const filterSection = $('#filter-section');
                const header = $('.filter-header');
                
                if (filterSection.is(':visible')) {
                    filterSection.slideUp(200);
                    header.removeClass('expanded');
                } else {
                    filterSection.slideDown(200);
                    header.addClass('expanded');
                }
            });

            // Apply filters
            $('#apply-filter').on('click', function() {
                applyFilters();
                updateFilterCount();
            });

            // Reset filters
            $('#reset-filter').on('click', function() {
                $('#ledger-filter')[0].reset();
                table.search('').columns().search('').draw();
                updateFilterCount();
            });

            // Quick filters
            $('#quick-month').on('click', function() {
                const today = new Date();
                const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
                
                $('#date-range-start').val(firstDay.toISOString().split('T')[0]);
                $('#date-range-end').val(today.toISOString().split('T')[0]);
                applyFilters();
                updateFilterCount();
            });

            function applyFilters() {
                const startDate = $('#date-range-start').val();
                const endDate = $('#date-range-end').val();
                const type = $('#transaction-type').val();
                const method = $('#payment-method').val();

                // Clear existing filters
                $.fn.dataTable.ext.search.pop();

                // Date range filter
                if (startDate && endDate) {
                    $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
                        const dateStr = data[0];
                        if (dateStr.includes('Opening')) return true;
                        
                        const rowDate = new Date(dateStr);
                        const start = new Date(startDate);
                        const end = new Date(endDate);
                        
                        return rowDate >= start && rowDate <= end;
                    });
                }

                // Type filter
                if (type) {
                    if (type === 'debit') {
                        table.column(6).search('৳', true, false);
                    } else {
                        table.column(7).search('৳', true, false);
                    }
                }

                // Method filter
                if (method) {
                    table.column(2).search(method, true, false);
                }

                table.draw();
            }

            function updateFilterCount() {
                const activeFilters = [];
                
                $('#ledger-filter input, #ledger-filter select').each(function() {
                    if ($(this).val()) {
                        activeFilters.push(1);
                    }
                });
                
                const count = activeFilters.length;
                $('#active-filters').text(count + (count === 1 ? ' filter' : ' filters'));
            }

            // Invoice modal handling
            $('.invoice-link').on('click', function() {
                const invoiceId = $(this).data('invoice-id');
                loadInvoiceDetails(invoiceId);
            });

            function loadInvoiceDetails(invoiceId) {
                const modalBody = $('#invoiceModalBody');
                const viewFullInvoiceBtn = $('#viewFullInvoice');
                
                modalBody.html(`
                    <div class="text-center">
                        <div class="spinner-border text-primary" role="status">
                            <span class="sr-only">Loading...</span>
                        </div>
                        <p class="mt-2">Loading invoice details...</p>
                    </div>
                `);
                
                viewFullInvoiceBtn.attr('href', '/invoices/' + invoiceId);
                
                $.ajax({
                    url: '/invoices/' + invoiceId + '/modal-details',
                    method: 'GET',
                    success: function(response) {
                        if (response.success) {
                            renderInvoiceDetails(response.invoice);
                        } else {
                            showError(response.message || 'Failed to load invoice details');
                        }
                    },
                    error: function(xhr) {
                        showError('Failed to load invoice details. Please try again.');
                    }
                });
            }

            function renderInvoiceDetails(invoice) {
                const modalBody = $('#invoiceModalBody');
                
                let itemsHtml = '';
                invoice.items.forEach(function(item) {
                    const product = item.product;
                    itemsHtml += `
                        <tr>
                            <td>${product.name}</td>
                            <td class="text-center">${parseFloat(item.quantity).toLocaleString()}</td>
                            <td class="text-right">৳${parseFloat(item.unit_price).toLocaleString()}</td>
                            <td class="text-right font-weight-bold">৳${parseFloat(item.total).toLocaleString()}</td>
                        </tr>
                    `;
                });

                const html = `
                    <div class="invoice-summary">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <h5>${invoice.invoice_number}</h5>
                                <p class="text-muted">${new Date(invoice.invoice_date).toLocaleDateString()}</p>
                            </div>
                            <div class="col-md-6 text-right">
                                <span class="badge badge-${invoice.payment_status === 'paid' ? 'success' : 'warning'}">
                                    ${invoice.payment_status.toUpperCase()}
                                </span>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-6">
                                <small class="text-muted">Total</small>
                                <h6>৳${parseFloat(invoice.total).toLocaleString()}</h6>
                            </div>
                            <div class="col-6">
                                <small class="text-muted">Due</small>
                                <h6 class="${invoice.due_amount > 0 ? 'text-danger' : 'text-success'}">
                                    ৳${parseFloat(invoice.due_amount).toLocaleString()}
                                </h6>
                            </div>
                        </div>
                    </div>

                    <h6 class="font-weight-bold mb-3">Items</h6>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th class="text-center">Qty</th>
                                    <th class="text-right">Price</th>
                                    <th class="text-right">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${itemsHtml}
                            </tbody>
                        </table>
                    </div>
                `;
                
                modalBody.html(html);
            }

            function showError(message) {
                $('#invoiceModalBody').html(`
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle"></i> ${message}
                    </div>
                `);
            }

            // Initialize filter count
            updateFilterCount();
              // Destroy existing DataTable if it exists
    if ($.fn.DataTable.isDataTable('#ledger-table')) {
        $('#ledger-table').DataTable().destroy();
    }
    
    // Initialize DataTable with proper column configuration
    try {
        const table = $('#ledger-table').DataTable({
            order: [],
            pageLength: 25,
            lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
            dom: 'rtip',
            responsive: true,
            language: {
                emptyTable: 'No transactions found',
                zeroRecords: 'No transactions match your filters'
            },
            columnDefs: [
                { 
                    "orderable": false, 
                    "targets": [9] // Actions column (0-indexed, so column 10 = index 9)
                }
            ]
        });
    } catch (error) {
        console.error('DataTable initialization error:', error);
    }
});
    </script>
@stop
