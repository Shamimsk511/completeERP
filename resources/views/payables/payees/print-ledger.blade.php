<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payee Ledger - {{ $payee->name }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', Arial, sans-serif;
            font-size: 14px;
            line-height: 1.5;
            color: #1f2937;
            background: white;
            margin: 0;
            padding: 0;
        }
        
        .ledger-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 30px;
            background: white;
        }
        
        /* Header */
        .ledger-header {
            display: flex;
            align-items: center;
            margin-bottom: 25px;
            padding-bottom: 20px;
            border-bottom: 2px solid #374151;
        }
        
        .logo-container {
            width: 80px;
            height: 80px;
            margin-right: 25px;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            overflow: hidden;
            background: #f9fafb;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        
        .logo-container img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .company-info {
            flex-grow: 1;
            text-align: center;
        }
        
        .company-info h1 {
            font-size: 22px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #1f2937;
            margin-bottom: 8px;
        }
        
        .company-details {
            font-size: 12px;
            color: #4b5563;
            line-height: 1.4;
        }
        
        .company-details span {
            margin: 0 8px;
        }
        
        /* Document Title */
        .document-title {
            position: absolute;
            top: 15px;
            right: 15px;
            background: #1f2937;
            color: white;
            padding: 8px 16px;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        /* Period Info */
        .period-info {
            text-align: center;
            margin-bottom: 20px;
            font-size: 13px;
            color: #6b7280;
            font-weight: 500;
        }
        
        /* Payee Section */
        .payee-section {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 25px;
            margin-bottom: 25px;
        }
        
        .payee-info, .ledger-summary {
            border: 1px solid #e5e7eb;
            padding: 20px;
            border-radius: 8px;
            background: #f9fafb;
        }
        
        .section-title {
            font-size: 14px;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 12px;
            text-transform: uppercase;
            border-bottom: 1px solid #d1d5db;
            padding-bottom: 6px;
        }
        
        .payee-info p, .ledger-summary p {
            margin: 6px 0;
            font-size: 13px;
            color: #374151;
            line-height: 1.4;
        }
        
        .payee-info p strong, .ledger-summary p strong {
            display: inline-block;
            width: 120px;
            color: #1f2937;
        }
        
        .ledger-summary {
            text-align: left;
        }
        
        .balance-positive {
            color: #dc2626;
            font-weight: 600;
        }
        
        .balance-negative {
            color: #059669;
            font-weight: 600;
        }
        
        .balance-zero {
            color: #6b7280;
            font-weight: 600;
        }
        
        /* Table Styles */
        .table-container {
            margin-bottom: 25px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            overflow: hidden;
        }
        
        .ledger-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            font-size: 12px;
        }
        
        .ledger-table thead {
            background: #f3f4f6;
        }
        
        .ledger-table th {
            padding: 12px 8px;
            text-align: left;
            font-size: 11px;
            font-weight: 600;
            color: #1f2937;
            text-transform: uppercase;
            border-bottom: 1px solid #d1d5db;
            border-right: 1px solid #e5e7eb;
        }
        
        .ledger-table th:last-child {
            border-right: none;
        }
        
        .ledger-table th.text-center {
            text-align: center;
        }
        
        .ledger-table th.text-right {
            text-align: right;
        }
        
        .ledger-table tbody tr:nth-child(even) {
            background: #f9fafb;
        }
        
        .ledger-table td {
            padding: 10px 8px;
            font-size: 11px;
            color: #374151;
            border-bottom: 1px solid #e5e7eb;
            border-right: 1px solid #e5e7eb;
            vertical-align: top;
        }
        
        .ledger-table td:last-child {
            border-right: none;
        }
        
        .ledger-table td.text-center {
            text-align: center;
        }
        
        .ledger-table td.text-right {
            text-align: right;
            font-weight: 500;
        }
        
        .transaction-type {
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: 600;
            text-transform: uppercase;
            border: 1px solid;
        }
        
        .cash-in {
            background: white;
            color: #059669;
            border-color: #059669;
        }
        
        .cash-out {
            background: white;
            color: #dc2626;
            border-color: #dc2626;
        }
        
        .amount-in {
            color: #059669;
            font-weight: 600;
        }
        
        .amount-out {
            color: #dc2626;
            font-weight: 600;
        }
        
        .reference-cell {
            font-size: 10px;
            color: #6b7280;
        }
        
        .description-cell {
            font-size: 10px;
            line-height: 1.3;
            max-width: 120px;
            word-wrap: break-word;
        }
        
        .category-cell {
            font-size: 10px;
            color: #4b5563;
            text-transform: capitalize;
        }
        
        /* Footer Row */
        .ledger-table tfoot {
            background: #f1f5f9;
        }
        
        .ledger-table tfoot td {
            padding: 12px 8px;
            font-weight: 600;
            color: #1f2937;
            border-bottom: none;
            border-top: 2px solid #374151;
            font-size: 12px;
        }
        
        /* Summary Section */
        .summary-section {
            display: flex;
            justify-content: flex-end;
            align-items: flex-start;
            margin-bottom: 25px;
        }
        
        .summary-totals {
            width: 300px;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            background: #f9fafb;
            padding: 15px;
        }
        
        .summary-totals table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .summary-totals table th,
        .summary-totals table td {
            padding: 6px 8px;
            text-align: right;
            border: none;
            font-size: 12px;
            line-height: 1.4;
        }
        
        .summary-totals table th {
            font-weight: 500;
            color: #6b7280;
            text-align: left;
            white-space: nowrap;
        }
        
        .summary-totals table td {
            font-weight: 500;
            color: #1f2937;
        }
        
        .summary-totals table tr.total-row th,
        .summary-totals table tr.total-row td {
            font-weight: 600;
            color: #1f2937;
            border-top: 1px solid #d1d5db;
            border-bottom: 1px solid #d1d5db;
            padding-top: 8px;
            padding-bottom: 8px;
        }
        
        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #6b7280;
        }
        
        .empty-state h3 {
            font-size: 16px;
            margin-bottom: 8px;
            color: #9ca3af;
        }
        
        .empty-state p {
            font-size: 12px;
        }
        
        /* Signatures */
        .signatures {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 25px;
            margin-bottom: 20px;
        }
        
        .signature-box {
            text-align: center;
            padding: 20px 15px 15px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            background: white;
            height: 100px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        
        .signature-title {
            font-weight: 600;
            color: #1f2937;
            font-size: 12px;
            margin-bottom: 5px;
        }
        
        .signature-line {
            border-top: 1px solid #374151;
            margin: 10px auto 5px;
            width: 150px;
        }
        
        .signature-label {
            font-size: 10px;
            color: #6b7280;
        }
        
        .footer-text {
            text-align: center;
            font-size: 12px;
            color: #6b7280;
            margin-bottom: 20px;
            font-style: italic;
        }
        
        /* Print Buttons */
        .print-actions {
            display: flex;
            justify-content: center;
            gap: 8px;
            margin-top: 15px;
            padding-top: 12px;
            border-top: 1px solid #e5e7eb;
        }
        
        .print-btn {
            padding: 6px 12px;
            border: 1px solid #d1d5db;
            border-radius: 4px;
            font-size: 9px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
            text-transform: uppercase;
        }
        
        .print-btn-primary {
            background: #1f2937;
            color: white;
            border-color: #1f2937;
        }
        
        .print-btn-primary:hover {
            background: #374151;
        }
        
        .print-btn-secondary {
            background: white;
            color: #1f2937;
        }
        
        .print-btn-secondary:hover {
            background: #f3f4f6;
        }
        
        /* Compact Print Styles */
        @media print {
            @page {
                margin: 0.3in;
                size: A4;
            }
            
            body {
                print-color-adjust: exact;
                -webkit-print-color-adjust: exact;
                font-size: 12px !important;
            }
            
            .ledger-container {
                padding: 8px !important;
                margin: 0 !important;
                max-width: none !important;
                transform: none !important;
            }
            
            .no-print {
                display: none !important;
            }
            
            .document-title {
                position: static !important;
                display: inline-block !important;
                margin-bottom: 10px !important;
                padding: 4px 8px !important;
                font-size: 12px !important;
                background: white !important;
                color: #1f2937 !important;
                border: 1px solid #1f2937 !important;
            }
            
            /* Header Compacting */
            .ledger-header {
                margin-bottom: 10px !important;
                padding-bottom: 8px !important;
                page-break-after: avoid;
            }
            
            .logo-container {
                width: 50px !important;
                height: 50px !important;
                margin-right: 12px !important;
                background: white !important;
            }
            
            .company-info h1 {
                font-size: 16px !important;
                margin-bottom: 3px !important;
            }
            
            .company-details {
                font-size: 9px !important;
            }
            
            .period-info {
                margin-bottom: 8px !important;
                font-size: 10px !important;
            }
            
            /* Payee Section Compacting */
            .payee-section {
                margin-bottom: 12px !important;
                gap: 12px !important;
                page-break-inside: avoid;
            }
            
            .payee-info, .ledger-summary {
                padding: 8px !important;
                background: white !important;
            }
            
            .section-title {
                font-size: 10px !important;
                margin-bottom: 4px !important;
                padding-bottom: 2px !important;
            }
            
            .payee-info p, .ledger-summary p {
                margin: 2px 0 !important;
                font-size: 9px !important;
            }
            
            .payee-info p strong, .ledger-summary p strong {
                width: 80px !important;
            }
            
            /* Table Compacting */
            .table-container {
                margin-bottom: 10px !important;
                page-break-before: avoid;
            }
            
            .ledger-table thead {
                background: white !important;
            }
            
            .ledger-table th {
                padding: 4px 3px !important;
                font-size: 8px !important;
            }
            
            .ledger-table td {
                padding: 3px 3px !important;
                font-size: 8px !important;
            }
            
            .ledger-table tbody tr:nth-child(even) {
                background: white !important;
            }
            
            .transaction-type {
                padding: 1px 4px !important;
                font-size: 7px !important;
                background: white !important;
            }
            
            .reference-cell, .description-cell, .category-cell {
                font-size: 7px !important;
            }
            
            .ledger-table tfoot {
                background: white !important;
            }
            
            .ledger-table tfoot td {
                padding: 6px 3px !important;
                font-size: 9px !important;
            }
            
            /* Summary Section Compacting */
            .summary-section {
                margin-bottom: 10px !important;
                page-break-inside: avoid;
            }
            
            .summary-totals {
                width: 200px !important;
                padding: 6px !important;
                background: white !important;
            }
            
            .summary-totals table th,
            .summary-totals table td {
                padding: 2px 4px !important;
                font-size: 8px !important;
            }
            
            .summary-totals table tr.total-row th,
            .summary-totals table tr.total-row td {
                padding-top: 3px !important;
                padding-bottom: 3px !important;
            }
            
            /* Footer Compacting */
            .signatures {
                gap: 10px !important;
                margin-bottom: 8px !important;
                page-break-before: avoid;
            }
            
            .signature-box {
                padding: 8px 6px 5px !important;
                height: 50px !important;
                background: white !important;
            }
            
            .signature-title {
                font-size: 8px !important;
            }
            
            .signature-line {
                width: 100px !important;
                margin: 6px auto 3px !important;
            }
            
            .signature-label {
                font-size: 7px !important;
            }
            
            .footer-text {
                font-size: 8px !important;
                margin-bottom: 5px !important;
            }
            
            .empty-state {
                padding: 20px 10px !important;
            }
            
            .empty-state h3 {
                font-size: 12px !important;
            }
            
            .empty-state p {
                font-size: 9px !important;
            }
        }
        
        /* Mobile Responsive */
        @media (max-width: 768px) {
            .ledger-container {
                padding: 12px;
                transform: scale(1);
            }
            
            .ledger-header {
                flex-direction: column;
                text-align: center;
                gap: 8px;
            }
            
            .logo-container {
                margin-right: 0;
                margin-bottom: 8px;
            }
            
            .payee-section {
                grid-template-columns: 1fr;
                gap: 10px;
            }
            
            .payee-info, .ledger-summary {
                height: auto;
            }
            
            .summary-section {
                flex-direction: column;
                gap: 10px;
            }
            
            .summary-totals {
                width: 100%;
            }
            
            .signatures {
                grid-template-columns: 1fr;
                gap: 10px;
            }
            
            .signature-box {
                height: auto;
            }
            
            .document-title {
                position: static;
                margin-bottom: 12px;
                display: inline-block;
            }
            
            .ledger-table {
                font-size: 10px;
            }
            
            .ledger-table th,
            .ledger-table td {
                padding: 6px 4px;
            }
        }
    </style>
</head>
<body>
    <div class="ledger-container">
        <div class="document-title">PAYEE LEDGER</div>
        
        <div class="ledger-header">
            <div class="logo-container">
                @if(isset($businessSettings->logo) && $businessSettings->logo)
                    <img src="{{ Storage::url($businessSettings->logo) }}" alt="{{ $businessSettings->business_name }} Logo">
                @else
                    <img src="{{ asset('logo/logo.png') }}" alt="Logo">
                @endif
            </div>
            <div class="company-info">
                <h1>{{ $businessSettings->business_name ?? config('adminlte.title') }}</h1>
                <div class="company-details">
                    @if(!empty($businessSettings->phone))
                        Phone: {{ $businessSettings->phone }}
                    @endif
                    @if(!empty($businessSettings->email))
                        <span>|</span> Email: {{ $businessSettings->email }}
                    @endif
                    @if(!empty($businessSettings->address))
                        <br>{{ $businessSettings->address }}
                    @endif
                    @if(!empty($businessSettings->bin_number))
                        <span>|</span> BIN: {{ $businessSettings->bin_number }}
                    @endif
                </div>
            </div>
        </div>
        
        <div class="period-info">
            @if($startDate && $endDate)
                Period: {{ date('M d, Y', strtotime($startDate)) }} to {{ date('M d, Y', strtotime($endDate)) }}
            @else
                All Transactions
            @endif
        </div>
        
        <div class="payee-section">
            <div class="payee-info">
                <div class="section-title">Payee Information</div>
                <p><strong>Payee Name:</strong> {{ $payee->name }}</p>
                <p><strong>Type:</strong> {{ ucfirst($payee->type) }}</p>
                <p><strong>Phone:</strong> {{ $payee->phone ?: 'N/A' }}</p>
                <p><strong>Address:</strong> {{ $payee->address ?: 'N/A' }}</p>
            </div>
            
            <div class="ledger-summary">
                <div class="section-title">Balance Summary</div>
                @php
                    $openingBalance = $ledgerOpeningBalance ?? $payee->opening_balance;
                    $currentBalance = $ledgerCurrentBalance ?? $payee->current_balance;
                @endphp
                <p><strong>Opening Balance:</strong> 
                    <span class="{{ $openingBalance > 0 ? 'balance-positive' : ($openingBalance < 0 ? 'balance-negative' : 'balance-zero') }}">
                        ৳{{ number_format($openingBalance, 2) }}
                    </span>
                </p>
                <p><strong>Current Balance:</strong> 
                    <span class="{{ $currentBalance > 0 ? 'balance-positive' : ($currentBalance < 0 ? 'balance-negative' : 'balance-zero') }}">
                        ৳{{ number_format($currentBalance, 2) }}
                    </span>
                </p>
                <p><strong>Created Date:</strong> {{ $payee->created_at->format('M d, Y') }}</p>
                <p><strong>Report Date:</strong> {{ date('M d, Y') }}</p>
            </div>
        </div>
        
        <div class="table-container">
            <table class="ledger-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Reference</th>
                        <th class="text-center">Type</th>
                        <th>Category</th>
                        <th>Description</th>
                        <th class="text-right">Amount</th>
                        <th class="text-right">Balance</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $balance = $openingBalance;
                        $totalDebit = 0;
                        $totalCredit = 0;
                    @endphp
                    
                    @if($transactions->isEmpty())
                        <tr>
                            <td colspan="7">
                                <div class="empty-state">
                                    <h3>No Transactions Found</h3>
                                    <p>No transactions found for this period.</p>
                                </div>
                            </td>
                        </tr>
                    @else
                        @foreach($transactions as $transaction)
                            @php
                                if($transaction->transaction_type == 'debit') {
                                    $totalDebit += $transaction->amount;
                                } else {
                                    $totalCredit += $transaction->amount;
                                }
                            @endphp
                            <tr>
                                <td>{{ $transaction->transaction_date->format('d-m-Y') }}</td>
                                <td class="reference-cell">{{ $transaction->reference_no ?: 'N/A' }}</td>
                                <td class="text-center">
                                    <span class="transaction-type {{ $transaction->transaction_type == 'credit' ? 'cash-in' : 'cash-out' }}">
                                        {{ $transaction->transaction_type == 'credit' ? 'Credit' : 'Debit' }}
                                    </span>
                                </td>
                                <td class="category-cell">{{ ucfirst($transaction->category ?: 'General') }}</td>
                                <td class="description-cell">{{ $transaction->description ?: 'N/A' }}</td>
                                <td class="text-right {{ $transaction->transaction_type == 'credit' ? 'amount-in' : 'amount-out' }}">
                                    ৳{{ number_format($transaction->amount, 2) }}
                                </td>
                                <td class="text-right {{ $transaction->running_balance > 0 ? 'balance-positive' : ($transaction->running_balance < 0 ? 'balance-negative' : 'balance-zero') }}">
                                    ৳{{ number_format($transaction->running_balance, 2) }}
                                </td>
                            </tr>
                        @endforeach
                    @endif
                </tbody>
                @if(!$transactions->isEmpty())
                <tfoot>
                    <tr>
                        <td colspan="5" style="text-align: right;"><strong>TOTAL TRANSACTIONS:</strong></td>
                        <td class="text-right"><strong>৳{{ number_format($totalDebit + $totalCredit, 2) }}</strong></td>
                        <td class="text-right"><strong>{{ $transactions->count() }} Records</strong></td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
        
        @if(!$transactions->isEmpty())
        <div class="summary-section">
            <div class="summary-totals">
                <table>
                    <tr>
                        <th>Total Debit:</th>
                        <td class="amount-out">৳{{ number_format($totalDebit, 2) }}</td>
                    </tr>
                    <tr>
                        <th>Total Credit:</th>
                        <td class="amount-in">৳{{ number_format($totalCredit, 2) }}</td>
                    </tr>
                    <tr class="total-row">
                        <th>Net Change:</th>
                        <td class="{{ ($totalCredit - $totalDebit) > 0 ? 'balance-positive' : (($totalCredit - $totalDebit) < 0 ? 'balance-negative' : 'balance-zero') }}">
                            ৳{{ number_format($totalCredit - $totalDebit, 2) }}
                        </td>
                    </tr>
                    <tr>
                        <th>Opening Balance:</th>
                        <td>৳{{ number_format($openingBalance, 2) }}</td>
                    </tr>
                    <tr class="total-row">
                        <th>Current Balance:</th>
                        <td class="{{ $currentBalance > 0 ? 'balance-positive' : ($currentBalance < 0 ? 'balance-negative' : 'balance-zero') }}">
                            ৳{{ number_format($currentBalance, 2) }}
                        </td>
                    </tr>
                </table>
            </div>
        </div>
        @endif
        
        <div class="signatures">
            <div class="signature-box">
                <div class="signature-title">Payee</div>
                <div style="font-size: 8px; margin-bottom: 8px;">{{ $payee->name }}</div>
                <div class="signature-line"></div>
                <div class="signature-label">Signature & Date</div>
            </div>
            <div class="signature-box">
                <div class="signature-title">Authorized By</div>
                <div style="font-size: 8px; margin-bottom: 8px;">{{ $businessSettings->business_name ?? config('adminlte.title') }}</div>
                <div class="signature-line"></div>
                <div class="signature-label">Signature & Date</div>
            </div>
        </div>
        
        <div class="footer-text">
            Generated on {{ date('F d, Y h:i A') }} | {{ $businessSettings->footer_message ?? 'Thanks for being with us.' }}
        </div>
        
        <div class="print-actions no-print">
            <button onclick="window.print()" class="print-btn print-btn-primary">
                Print Ledger
            </button>
            <button onclick="window.close()" class="print-btn print-btn-secondary">
                Close
            </button>
        </div>
    </div>
    
    <script>
        window.onload = function() {
            setTimeout(() => {
                window.print();
            }, 300);
        };
        
        document.addEventListener('keydown', function(e) {
            if (e.ctrlKey && e.key === 'p') {
                e.preventDefault();
                window.print();
            }
            if (e.key === 'Escape') {
                window.close();
            }
        });
    </script>
</body>
</html>
