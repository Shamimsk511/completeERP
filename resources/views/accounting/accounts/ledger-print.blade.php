@php
    $activePrintTemplate = $selectedTemplate ?? ($businessSettings->invoice_template ?? 'standard');
    if (!in_array($activePrintTemplate, ['standard', 'modern', 'simple', 'bold', 'elegant', 'imaginative'], true)) {
        $activePrintTemplate = 'standard';
    }
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Ledger - {{ $account->name }}</title>
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
        
        /* Header - Same as Invoice */
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
        
        /* Document Title - Same as Invoice */
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
        
        /* Account Section - Same as Invoice Bill Section */
        .account-section {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 25px;
            margin-bottom: 25px;
        }
        
        .account-details, .ledger-summary {
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
        
        .account-details p, .ledger-summary p {
            margin: 6px 0;
            font-size: 13px;
            color: #374151;
            line-height: 1.4;
        }
        
        .ledger-summary {
            text-align: right;
        }
        
        .ledger-summary h2 {
            font-size: 20px;
            color: #1f2937;
            margin-bottom: 15px;
        }
        
        .balance-positive {
            color: #059669;
            font-weight: 600;
        }
        
        .balance-negative {
            color: #dc2626;
            font-weight: 600;
        }
        
        /* Table Styles - Same as Invoice */
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
            text-align: right;
            font-weight: 500;
        }
        
        .transaction-description {
            font-weight: 500;
            line-height: 1.3;
        }
        
        .transaction-note {
            font-size: 10px;
            color: #6b7280;
            font-style: italic;
            margin-top: 3px;
        }
        
        .amount-cell {
            text-align: right !important;
            font-weight: 500;
        }
        
        .amount-positive {
            color: #059669;
        }
        
        .amount-negative {
            color: #dc2626;
        }
        
        /* Special Rows */
        .opening-balance-row {
            background: #fef3c7 !important;
        }
        
        .opening-balance-row td {
            color: #92400e;
            font-weight: 600;
        }
        
        /* Footer Row - Same as Invoice */
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
        
        /* Summary Section - Same as Invoice Amount Section */
        .summary-section {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 25px;
        }
        
        .summary-info {
            flex: 1;
            margin-right: 25px;
        }
        
        .summary-info p {
            font-size: 13px;
            color: #4b5563;
            line-height: 1.4;
            margin-bottom: 8px;
        }
        
        .balance-totals {
            width: 280px;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            background: #f9fafb;
            padding: 15px;
        }
        
        .balance-totals table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .balance-totals table th,
        .balance-totals table td {
            padding: 6px 8px;
            text-align: right;
            border: none;
            font-size: 12px;
            line-height: 1.4;
        }
        
        .balance-totals table th {
            font-weight: 500;
            color: #6b7280;
            text-align: left;
            white-space: nowrap;
        }
        
        .balance-totals table td {
            font-weight: 500;
            color: #1f2937;
        }
        
        .balance-totals table tr.total-row th,
        .balance-totals table tr.total-row td {
            font-weight: 600;
            color: #1f2937;
            border-top: 1px solid #d1d5db;
            border-bottom: 1px solid #d1d5db;
            padding-top: 8px;
            padding-bottom: 8px;
        }
        
        /* Disclaimer and Notes - Same as Invoice */
        .disclaimer {
            text-align: center;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
            font-weight: 500;
            color: #1f2937;
            font-size: 12px;
            background: #f9fafb;
        }
        
        .footer-text {
            text-align: center;
            font-size: 12px;
            color: #6b7280;
            margin-bottom: 20px;
            font-style: italic;
        }
        
        /* Print Buttons - Same as Invoice */
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
        
        /* Print Styles - Same as Invoice */
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
            }
            
            .company-info h1 {
                font-size: 16px !important;
                margin-bottom: 3px !important;
            }
            
            .company-details {
                font-size: 9px !important;
            }
            
            /* Account Section Compacting */
            .account-section {
                margin-bottom: 12px !important;
                gap: 12px !important;
                page-break-inside: avoid;
            }
            
            .account-details, .ledger-summary {
                padding: 8px !important;
            }
            
            .section-title {
                font-size: 10px !important;
                margin-bottom: 4px !important;
                padding-bottom: 2px !important;
            }
            
            .account-details p, .ledger-summary p {
                margin: 2px 0 !important;
                font-size: 9px !important;
            }
            
            .ledger-summary h2 {
                font-size: 14px !important;
                margin-bottom: 6px !important;
            }
            
            /* Table Compacting */
            .table-container {
                margin-bottom: 10px !important;
                page-break-before: avoid;
            }
            
            .ledger-table th {
                padding: 4px 3px !important;
                font-size: 8px !important;
            }
            
            .ledger-table td {
                padding: 3px 3px !important;
                font-size: 8px !important;
            }
            
            .transaction-note {
                font-size: 7px !important;
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
            
            .summary-info {
                margin-right: 10px !important;
            }
            
            .summary-info p {
                font-size: 9px !important;
            }
            
            .balance-totals {
                width: 200px !important;
                padding: 6px !important;
            }
            
            .balance-totals table th,
            .balance-totals table td {
                padding: 2px 4px !important;
                font-size: 8px !important;
            }
            
            .balance-totals table tr.total-row th,
            .balance-totals table tr.total-row td {
                padding-top: 3px !important;
                padding-bottom: 3px !important;
            }
            
            /* Footer Compacting */
            .disclaimer {
                padding: 6px !important;
                margin-bottom: 8px !important;
                font-size: 8px !important;
            }
            
            .footer-text {
                font-size: 8px !important;
                margin-bottom: 5px !important;
            }
            
            /* Ensure special row colors print */
            .opening-balance-row {
                print-color-adjust: exact;
                -webkit-print-color-adjust: exact;
            }
        }
        
        /* Mobile Responsive - Same as Invoice */
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
            
            .account-section {
                grid-template-columns: 1fr;
                gap: 10px;
            }
            
            .account-details, .ledger-summary {
                height: auto;
            }
            
            .summary-section {
                flex-direction: column;
                gap: 10px;
            }
            
            .balance-totals {
                width: 100%;
            }
            
            .document-title {
                position: static;
                margin-bottom: 12px;
                display: inline-block;
            }
        }
    </style>
    @include('partials.print-theme-styles')
</head>
<body class="print-theme template-{{ $activePrintTemplate }}">
    @php
        $totalDebits = collect($ledger['entries'])->sum('debit');
        $totalCredits = collect($ledger['entries'])->sum('credit');
    @endphp
    
    <div class="ledger-container">
        <div class="document-title">ACCOUNT LEDGER</div>
        
        <div class="ledger-header">
            <div class="logo-container">
                @if($businessSettings && $businessSettings->logo)
                    <img src="{{ Storage::url($businessSettings->logo) }}" alt="{{ $businessSettings->business_name ?? 'Logo' }}">
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
        
        <div class="account-section">
            <div class="account-details">
                <div class="section-title">Account Details</div>
                <p><strong>{{ $account->name }}</strong></p>
                <p>Code: {{ $account->account_code }}</p>
                <p>Group: {{ $account->accountGroup->name ?? 'N/A' }}</p>
                <p>Nature: {{ ucfirst($account->accountGroup->nature ?? 'N/A') }}</p>
            </div>
            
            <div class="ledger-summary">
                <h2>LEDGER SUMMARY</h2>
                <p>Period: {{ \Carbon\Carbon::parse($ledger['period']['from'])->format('d-m-Y') }} to {{ \Carbon\Carbon::parse($ledger['period']['to'])->format('d-m-Y') }}</p>
                <p>Opening Balance: <span class="{{ $ledger['opening_balance']['type'] === 'debit' ? 'balance-positive' : 'balance-negative' }}">{{ number_format($ledger['opening_balance']['balance'], 2) }}</span></p>
                <p>Closing Balance: <span class="{{ $ledger['closing_balance']['type'] === 'debit' ? 'balance-positive' : 'balance-negative' }}">{{ number_format($ledger['closing_balance']['balance'], 2) }}</span></p>
                <p>Total Transactions: {{ count($ledger['entries']) }}</p>
            </div>
        </div>
        
        <div class="table-container">
            <table class="ledger-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Reference</th>
                        <th>Source</th>
                        <th>Particulars</th>
                        <th style="text-align: right;">Debit (৳)</th>
                        <th style="text-align: right;">Credit (৳)</th>
                        <th style="text-align: right;">Balance (৳)</th>
                    </tr>
                </thead>
                <tbody>
                    {{-- Opening Balance Row --}}
                    <tr class="opening-balance-row">
                        <td>{{ \Carbon\Carbon::parse($ledger['period']['from'])->format('d-m-Y') }}</td>
                        <td>-</td>
                        <td>-</td>
                        <td>
                            <div class="transaction-description">Opening Balance</div>
                            <div class="transaction-note">Initial account balance</div>
                        </td>
                        <td class="amount-cell">-</td>
                        <td class="amount-cell">-</td>
                        <td class="amount-cell {{ $ledger['opening_balance']['type'] === 'debit' ? 'amount-positive' : 'amount-negative' }}">{{ number_format($ledger['opening_balance']['balance'], 2) }}</td>
                    </tr>
                    
                    {{-- Transaction Rows --}}
                    @forelse($ledger['entries'] as $entry)
                        <tr>
                            <td>{{ $entry['date']->format('d-m-Y') }}</td>
                            <td>{{ $entry['reference'] ?? '-' }}</td>
                            <td>{{ $entry['source_type'] ?? '-' }}</td>
                            <td>
                                <div class="transaction-description">{{ $entry['particulars'] ?? '-' }}</div>
                            </td>
                            <td class="amount-cell {{ $entry['debit'] > 0 ? 'amount-positive' : '' }}">
                                {{ $entry['debit'] > 0 ? number_format($entry['debit'], 2) : '-' }}
                            </td>
                            <td class="amount-cell {{ $entry['credit'] > 0 ? 'amount-negative' : '' }}">
                                {{ $entry['credit'] > 0 ? number_format($entry['credit'], 2) : '-' }}
                            </td>
                            <td class="amount-cell {{ $entry['balance_type'] === 'debit' ? 'amount-positive' : 'amount-negative' }}">
                                {{ number_format($entry['running_balance'], 2) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 20px;">
                                No transactions found for this period.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="4" style="text-align: right;"><strong>TOTALS:</strong></td>
                        <td class="amount-cell"><strong>{{ number_format($totalDebits, 2) }}</strong></td>
                        <td class="amount-cell"><strong>{{ number_format($totalCredits, 2) }}</strong></td>
                        <td class="amount-cell"><strong>{{ number_format($ledger['closing_balance']['balance'], 2) }}</strong></td>
                    </tr>
                </tfoot>
            </table>
        </div>
        
        <div class="summary-section">
            <div class="summary-info">
                <p><strong>Period Summary:</strong></p>
                <p>• Period: {{ \Carbon\Carbon::parse($ledger['period']['from'])->format('d-m-Y') }} to {{ \Carbon\Carbon::parse($ledger['period']['to'])->format('d-m-Y') }}</p>
                <p>• Total Debits: ৳{{ number_format($totalDebits, 2) }}</p>
                <p>• Total Credits: ৳{{ number_format($totalCredits, 2) }}</p>
                <p>• Net: ৳{{ number_format($totalDebits - $totalCredits, 2) }}</p>
            </div>
            
            <div class="balance-totals">
                <table>
                    <tr>
                        <th>Opening Balance:</th>
                        <td>{{ number_format($ledger['opening_balance']['balance'], 2) }}</td>
                    </tr>
                    <tr>
                        <th>Total Debits:</th>
                        <td>{{ number_format($totalDebits, 2) }}</td>
                    </tr>
                    <tr>
                        <th>Total Credits:</th>
                        <td>{{ number_format($totalCredits, 2) }}</td>
                    </tr>
                    <tr class="total-row">
                        <th>Closing Balance:</th>
                        <td class="{{ $ledger['closing_balance']['type'] === 'debit' ? 'amount-positive' : 'amount-negative' }}">{{ number_format($ledger['closing_balance']['balance'], 2) }}</td>
                    </tr>
                </table>
            </div>
        </div>
        
        <div class="disclaimer">
            This account ledger statement shows all posted transactions for the specified period. 
            All amounts are in BDT (Bangladeshi Taka). Please verify all entries carefully.
        </div>
        
        <div class="footer-text">
            Generated on: {{ now()->format('F d, Y h:i A') }} | {{ $businessSettings->business_name ?? config('adminlte.title') }}
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

