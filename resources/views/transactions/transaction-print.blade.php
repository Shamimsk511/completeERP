@php
    $activePrintTemplate = $selectedTemplate ?? ($businessSettings->invoice_template ?? 'standard');
    if (!in_array($activePrintTemplate, ['standard', 'modern', 'simple', 'bold', 'elegant', 'imaginative'], true)) {
        $activePrintTemplate = 'standard';
    }
@endphp
<!DOCTYPE html>
<html>
<head>
    <title>Transaction #{{ $transaction->id }} - Print View</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #1f2937;
            background: white;
            margin: 0;
            padding: 20px;
        }
        
        .receipt-container {
            max-width: 700px;
            margin: 0 auto;
            background: white;
            position: relative;
        }
        
        /* Header Section */
        .header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #374151;
        }
        
        .shop-name {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 5px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #1f2937;
        }
        
        .shop-info {
            margin-bottom: 3px;
            font-size: 11px;
            color: #4b5563;
            font-weight: 500;
        }
        
        .transaction-title {
            text-align: center;
            margin: 15px 0;
            font-size: 16px;
            font-weight: 600;
            color: #1f2937;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        /* Transaction Type Badge */
        .transaction-type-badge {
            position: absolute;
            top: 8px;
            right: 8px;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            background: #1f2937;
            color: white;
        }
        
        /* Transaction Details Table */
        .details-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            border: 1px solid #d1d5db;
            border-radius: 4px;
            overflow: hidden;
        }
        
        .details-table th {
            background: #f3f4f6;
            color: #1f2937;
            padding: 8px 10px;
            text-align: left;
            font-size: 10px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-right: 1px solid #e5e7eb;
            border-bottom: 1px solid #d1d5db;
            width: 25%;
        }
        
        .details-table td {
            padding: 8px 10px;
            font-size: 11px;
            color: #374151;
            border-bottom: 1px solid #f1f5f9;
            border-right: 1px solid #f1f5f9;
            background: white;
        }
        
        .details-table td:last-child {
            border-right: none;
        }
        
        .details-table tr:last-child td {
            border-bottom: none;
        }
        
        .details-table tr:nth-child(even) td {
            background: #f9fafb;
        }
        
        /* Balance Summary Card */
        .balance-summary {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 4px;
            padding: 15px;
            margin: 15px 0;
        }
        
        .balance-summary h4 {
            font-size: 12px;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 1px solid #d1d5db;
            padding-bottom: 5px;
        }
        
        .balance-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 6px 0;
            padding: 4px 0;
            font-size: 11px;
        }
        
        .balance-label {
            font-weight: 500;
            color: #374151;
        }
        
        .balance-value {
            font-weight: 600;
            color: #1f2937;
        }
        
        .discount-value {
            color: #059669;
            font-weight: 600;
        }
        
        .total-row {
            font-size: 12px;
            font-weight: 700;
            margin-top: 8px;
            padding-top: 8px;
            border-top: 1px solid #e5e7eb;
        }
        
        .total-row .balance-label,
        .total-row .balance-value {
            color: #1f2937;
            font-size: 12px;
        }
        
        /* Discount Note */
        .discount-note {
            background: #f9fafb;
            border: 1px solid #d1d5db;
            border-radius: 4px;
            padding: 10px;
            margin: 15px 0;
            color: #374151;
            font-size: 11px;
        }
        
        .discount-note strong {
            color: #1f2937;
        }
        
        /* Additional Details Table */
        .additional-table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
            border: 1px solid #e5e7eb;
            border-radius: 4px;
            overflow: hidden;
        }
        
        .additional-table th {
            background: #f3f4f6;
            color: #374151;
            padding: 8px 10px;
            text-align: left;
            font-size: 10px;
            font-weight: 600;
            width: 120px;
            border-right: 1px solid #e5e7eb;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .additional-table td {
            padding: 8px 10px;
            font-size: 11px;
            color: #4b5563;
            background: white;
            border-bottom: 1px solid #f1f5f9;
        }
        
        .additional-table tr:last-child th,
        .additional-table tr:last-child td {
            border-bottom: none;
        }
        
        /* Footer */
        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 10px;
            color: #6b7280;
            border-top: 1px solid #e5e7eb;
            padding-top: 10px;
        }
        
        .footer p {
            margin: 3px 0;
        }
        
        .footer strong {
            color: #374151;
        }
        
        /* Print Actions */
        .print-actions {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
        }
        
        .print-btn {
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            color: white;
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(59, 130, 246, 0.4);
        }
        
        .btn-secondary {
            background: linear-gradient(135deg, #6b7280 0%, #4b5563 100%);
            color: white;
            box-shadow: 0 4px 12px rgba(107, 114, 128, 0.3);
        }
        
        .btn-secondary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(107, 114, 128, 0.4);
        }
        
        /* Print Styles */
        @media print {
            body {
                margin: 0;
                padding: 20px;
                font-size: 14px;
            }
            
            .no-print {
                display: none !important;
            }
            
            .receipt-container {
                max-width: none;
            }
            
            .balance-summary {
                page-break-inside: avoid;
            }
            
            .details-table {
                page-break-inside: avoid;
            }
            
            /* Ensure colors print */
            .balance-summary,
            .discount-note {
                print-color-adjust: exact;
                -webkit-print-color-adjust: exact;
            }
            
            .details-table th {
                background: #6366f1 !important;
                color: white !important;
                print-color-adjust: exact;
                -webkit-print-color-adjust: exact;
            }
            
            .transaction-type-badge {
                print-color-adjust: exact;
                -webkit-print-color-adjust: exact;
            }
        }
        
        /* Mobile Responsive */
        @media (max-width: 768px) {
            body {
                padding: 15px;
            }
            
            .details-table th,
            .details-table td {
                padding: 10px 8px;
                font-size: 12px;
            }
            
            .balance-summary {
                padding: 20px 15px;
            }
            
            .shop-name {
                font-size: 22px;
            }
            
            .transaction-title {
                font-size: 20px;
            }
            
            .print-actions {
                flex-direction: column;
                align-items: center;
            }
            
            .print-btn {
                width: 200px;
            }
            
            .transaction-type-badge {
                position: static;
                display: inline-block;
                margin-bottom: 15px;
            }
        }
        
        /* Animation */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .details-table,
        .balance-summary,
        .discount-note {
            animation: fadeInUp 0.6s ease-out;
        }
        
        .details-table { animation-delay: 0.1s; }
        .balance-summary { animation-delay: 0.2s; }
        .discount-note { animation-delay: 0.3s; }
    </style>
    @include('partials.print-theme-styles')
</head>
<body class="print-theme template-{{ $activePrintTemplate }}">
    @php
        // Calculate previous balance before this transaction
        $currentBalance = $transaction->customer->outstanding_balance;
        $transactionAmount = $transaction->amount + ($transaction->discount_amount ?? 0);
        
        if ($transaction->type == 'debit') {
            $previousBalance = $currentBalance + $transactionAmount;
        } else {
            $previousBalance = $currentBalance - $transaction->amount;
        }
    @endphp

    <div class="receipt-container">
        <!-- Logo Section -->
        @if($businessSettings->logo)
        <div style="text-align: center; margin-bottom: 15px;">
            <img src="{{ Storage::url($businessSettings->logo) }}" alt="{{ $businessSettings->business_name }} Logo" 
                 style="max-width: 80px; height: auto; border-radius: 4px;">
        </div>
        @endif
        
        <div class="transaction-type-badge">
            {{ $transaction->type == 'debit' ? 'Payment' : 'Charge' }}
        </div>
        
        <div class="header">
            <div class="shop-name">{{ $businessSettings->business_name ?? config('adminlte.title') }}</div>
            @if(!empty($businessSettings->address))
                <div class="shop-info">Address: {{ $businessSettings->address }}</div>
            @endif
            @if(!empty($businessSettings->phone))
                <div class="shop-info">Phone: {{ $businessSettings->phone }}</div>
            @endif
            @if(!empty($businessSettings->email))
                <div class="shop-info">Email: {{ $businessSettings->email }}</div>
            @endif
            @if(!empty($businessSettings->bin_number))
                <div class="shop-info">BIN: {{ $businessSettings->bin_number }}</div>
            @endif
        </div>

        <div class="transaction-title">
            Transaction Receipt #{{ $transaction->id }}
        </div>

        <table class="details-table">
            <tr>
                <th>Customer Name</th>
                <td>{{ $transaction->customer->name }}</td>
                <th>Transaction Date</th>
                <td>{{ $transaction->created_at->format('F d, Y h:i A') }}</td>
            </tr>
            <tr>
                <th>Transaction Type</th>
                <td>
                    <span style="font-weight: 600; color: {{ $transaction->type == 'debit' ? '#059669' : '#dc2626' }};">
                        {{ $transaction->type == 'debit' ? 'Payment Received' : 'Amount Charged' }}
                    </span>
                </td>
                <th>Payment Method</th>
                <td>{{ ucfirst($transaction->method) }}</td>
            </tr>
            <tr>
                <th>Purpose</th>
                <td colspan="3">{{ $transaction->purpose }}</td>
            </tr>
        </table>

        <div class="balance-summary">
            <h4>Balance Summary</h4>
            
            <div class="balance-row">
                <span class="balance-label">Previous Balance Due:</span>
                <span class="balance-value">৳{{ number_format($previousBalance, 2) }}</span>
            </div>
            
            @if($transaction->type == 'debit')
                <div class="balance-row">
                    <span class="balance-label">Payment Amount:</span>
                    <span class="balance-value">৳{{ number_format($transaction->amount, 2) }}</span>
                </div>
                @if($transaction->has_discount)
                <div class="balance-row">
                    <span class="balance-label" style="color: #059669;">Discount Applied:</span>
                    <span class="balance-value discount-value">৳{{ number_format($transaction->discount_amount, 2) }}</span>
                </div>
                <div class="balance-row">
                    <span class="balance-label">Total Reduction:</span>
                    <span class="balance-value">৳{{ number_format($transactionAmount, 2) }}</span>
                </div>
                @endif
            @else
                <div class="balance-row">
                    <span class="balance-label">Amount Charged:</span>
                    <span class="balance-value">৳{{ number_format($transaction->amount, 2) }}</span>
                </div>
            @endif
            
            <div class="balance-row total-row">
                <span class="balance-label">Current Balance Due:</span>
                <span class="balance-value">৳{{ number_format($currentBalance, 2) }}</span>
            </div>
        </div>

        @if($transaction->has_discount && $transaction->discount_reason)
        <div class="discount-note">
            <strong>Discount Reason:</strong> {{ $transaction->discount_reason }}
        </div>
        @endif

        @if($transaction->reference || $transaction->note)
        <table class="additional-table">
            @if($transaction->reference)
            <tr>
                <th>Reference</th>
                <td>{{ $transaction->reference }}</td>
            </tr>
            @endif
            @if($transaction->note)
            <tr>
                <th>Note</th>
                <td>{{ $transaction->note }}</td>
            </tr>
            @endif
        </table>
        @endif

        <div class="footer">
            <p><strong>This is a computer-generated receipt and does not require a signature.</strong></p>
            <p>Printed on: {{ now()->format('F d, Y h:i A') }}</p>
            <p><strong>{{ $businessSettings->business_name ?? config('adminlte.title') }}</strong></p>
            @if($businessSettings->footer_message)
                <p style="margin-top: 10px; font-style: italic;">{{ $businessSettings->footer_message }}</p>
            @endif
        </div>

        <div class="print-actions no-print">
            <button onclick="window.print();" class="print-btn btn-primary">
                🖨️ Print Receipt
            </button>
            <button onclick="window.location='{{ route('transactions.show', $transaction) }}';" class="print-btn btn-secondary">
                ⬅️ Back
            </button>
        </div>
    </div>
</body>
</html>

