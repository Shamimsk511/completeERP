<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #{{ $invoice->invoice_number }}</title>
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
        
        .invoice-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 30px;
            background: white;
        }
        
        /* Header */
        .invoice-header {
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
        
        /* Bill Section */
        .bill-section {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 25px;
            margin-bottom: 25px;
        }
        
        .bill-to, .invoice-details {
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
        
        .bill-to p, .invoice-details p {
            margin: 6px 0;
            font-size: 13px;
            color: #374151;
            line-height: 1.4;
        }
        
        .invoice-details {
            text-align: right;
        }
        
        .invoice-details h2 {
            font-size: 20px;
            color: #1f2937;
            margin-bottom: 15px;
        }
        
        /* Table Styles */
        .table-container {
            margin-bottom: 25px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            overflow: hidden;
        }
        
        .invoice-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            font-size: 12px;
        }
        
        .invoice-table thead {
            background: #f3f4f6;
        }
        
        .invoice-table th {
            padding: 12px 8px;
            text-align: left;
            font-size: 11px;
            font-weight: 600;
            color: #1f2937;
            text-transform: uppercase;
            border-bottom: 1px solid #d1d5db;
            border-right: 1px solid #e5e7eb;
        }
        
        .invoice-table th:last-child {
            border-right: none;
            text-align: right;
        }
        
        .invoice-table th:first-child {
            width: 40px;
            text-align: center;
        }
        
        .invoice-table tbody tr:nth-child(even) {
            background: #f9fafb;
        }
        
        .invoice-table td {
            padding: 10px 8px;
            font-size: 11px;
            color: #374151;
            border-bottom: 1px solid #e5e7eb;
            border-right: 1px solid #e5e7eb;
            vertical-align: top;
        }
        
        .invoice-table td:last-child {
            border-right: none;
            text-align: right;
            font-weight: 500;
        }
        
        .invoice-table td:first-child {
            text-align: center;
            font-weight: 500;
        }
        
        .product-description {
            font-weight: 500;
            line-height: 1.3;
        }
        
        .product-code {
            font-size: 10px;
            color: #6b7280;
            font-style: italic;
            margin-top: 3px;
        }
        
        .quantity-cell {
            text-align: center !important;
            font-weight: 500;
        }
        
        /* Footer Row */
        .invoice-table tfoot {
            background: #f1f5f9;
        }
        
        .invoice-table tfoot td {
            padding: 12px 8px;
            font-weight: 600;
            color: #1f2937;
            border-bottom: none;
            border-top: 2px solid #374151;
            font-size: 12px;
        }
        
        /* Amount Section */
        .amount-section {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 25px;
        }
        
        .words-total {
            flex: 1;
            margin-right: 25px;
        }
        
        .words-total p {
            font-size: 13px;
            color: #4b5563;
            font-style: italic;
            line-height: 1.4;
        }
        
        .amount-totals {
            width: 280px;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            background: #f9fafb;
            padding: 15px;
        }
        
        .amount-totals table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .amount-totals table th,
        .amount-totals table td {
            padding: 6px 8px;
            text-align: right;
            border: none;
            font-size: 12px;
            line-height: 1.4;
        }
        
        .amount-totals table th {
            font-weight: 500;
            color: #6b7280;
            text-align: left;
            white-space: nowrap;
        }
        
        .amount-totals table td {
            font-weight: 500;
            color: #1f2937;
        }
        
        .amount-totals table tr.total-row th,
        .amount-totals table tr.total-row td {
            font-weight: 600;
            color: #1f2937;
            border-top: 1px solid #d1d5db;
            border-bottom: 1px solid #d1d5db;
            padding-top: 8px;
            padding-bottom: 8px;
        }
        
        /* Terms + Bank Details */
        .terms-bank {
            display: grid;
            grid-template-columns: 1fr 3fr;
            gap: 16px;
            margin-bottom: 25px;
        }

        .bank-box,
        .terms-box {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 16px;
            font-size: 12px;
            line-height: 1.5;
            color: #4b5563;
        }

        .terms-box {
            color: #1f2937;
            font-weight: 500;
        }

        .terms-title,
        .bank-title {
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
            color: #111827;
        }

        .terms-footer {
            margin-top: 10px;
            font-size: 11px;
            color: #6b7280;
        }
        
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
        
        /* Paid Stamp */
        .paid-stamp {
            position: absolute;
            top: 45%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-25deg);
            font-size: 80px;
            color: rgba(34, 197, 94, 0.3);
            font-weight: bold;
            border: 8px solid rgba(34, 197, 94, 0.3);
            padding: 15px 25px;
            border-radius: 12px;
            z-index: 100;
            pointer-events: none;
            text-transform: uppercase;
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
            
            .invoice-container {
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
            .invoice-header {
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
            
            /* Bill Section Compacting */
            .bill-section {
                margin-bottom: 12px !important;
                gap: 12px !important;
                page-break-inside: avoid;
            }
            
            .bill-to, .invoice-details {
                padding: 8px !important;
            }
            
            .section-title {
                font-size: 10px !important;
                margin-bottom: 4px !important;
                padding-bottom: 2px !important;
            }
            
            .bill-to p, .invoice-details p {
                margin: 2px 0 !important;
                font-size: 9px !important;
            }
            
            .invoice-details h2 {
                font-size: 14px !important;
                margin-bottom: 6px !important;
            }
            
            /* Table Compacting */
            .table-container {
                margin-bottom: 10px !important;
                page-break-before: avoid;
            }
            
            .invoice-table th {
                padding: 4px 3px !important;
                font-size: 8px !important;
            }
            
            .invoice-table td {
                padding: 3px 3px !important;
                font-size: 8px !important;
            }
            
            .product-code {
                font-size: 7px !important;
            }
            
            .invoice-table tfoot td {
                padding: 6px 3px !important;
                font-size: 9px !important;
            }
            
            /* Amount Section Compacting */
            .amount-section {
                margin-bottom: 10px !important;
                page-break-inside: avoid;
            }
            
            .words-total {
                margin-right: 10px !important;
            }
            
            .words-total p {
                font-size: 9px !important;
            }
            
            .amount-totals {
                width: 200px !important;
                padding: 6px !important;
            }
            
            .amount-totals table th,
            .amount-totals table td {
                padding: 2px 4px !important;
                font-size: 8px !important;
            }
            
            .amount-totals table tr.total-row th,
            .amount-totals table tr.total-row td {
                padding-top: 3px !important;
                padding-bottom: 3px !important;
            }
            
            /* Footer Compacting */
            .terms-bank {
                gap: 6px !important;
                margin-bottom: 10px !important;
                grid-template-columns: 1fr 2fr !important;
            }

            .bank-box,
            .terms-box {
                padding: 6px !important;
                font-size: 8px !important;
                line-height: 1.2 !important;
            }

            .terms-footer {
                margin-top: 6px !important;
                font-size: 8px !important;
            }
            
            .signatures {
                gap: 10px !important;
                margin-bottom: 8px !important;
                page-break-before: avoid;
            }
            
            .signature-box {
                padding: 8px 6px 5px !important;
                height: 50px !important;
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
            
            /* Paid Stamp Adjustment */
            .paid-stamp {
                font-size: 60px !important;
                border-width: 6px !important;
                padding: 10px 15px !important;
            }
        }
        
        /* Mobile Responsive */
        @media (max-width: 768px) {
            .invoice-container {
                padding: 12px;
                transform: scale(1);
            }
            
            .invoice-header {
                flex-direction: column;
                text-align: center;
                gap: 8px;
            }
            
            .logo-container {
                margin-right: 0;
                margin-bottom: 8px;
            }
            
            .bill-section {
                grid-template-columns: 1fr;
                gap: 10px;
            }
            
            .bill-to, .invoice-details {
                height: auto;
            }
            
            .amount-section {
                flex-direction: column;
                gap: 10px;
            }
            
            .amount-totals {
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

            .terms-bank {
                grid-template-columns: 1fr;
            }
        }
@media print {
    /* Existing print styles ... */
    .qr-code-section img {
        width: 60px !important;
        height: 60px !important;
    }
    .qr-code-section p {
        font-size: 8px !important;
    }
}
.qr-container {
  display: flex;
  align-items: center;        /* vertically center QR and text */
  justify-content: center;    /* center the entire group within parent */
  gap: 20px;                  /* space between QR and text */
  /* optional: set a max width */
  max-width: 600px;
  margin: 18px auto 0;        /* add space above the QR block */
}

.qr-box {
  flex-shrink: 0;             /* prevents QR from shrinking */
}

.qr-text {
  text-align: left;           /* ensures text starts from left edge */
}
    </style>
</head>
<body>
    <div class="invoice-container">
@php
    // Use snapshot fields for historical accuracy
    // previous_balance: customer's balance BEFORE this invoice
    // initial_paid_amount: payment made AT THE TIME of invoice creation
    $previousDue = $invoice->previous_balance ?? 0;
    $totalPayable = $invoice->total + $previousDue;
    $givenAmount = $invoice->initial_paid_amount ?? 0;
    $totalDue = $totalPayable - $givenAmount;
@endphp

@if(number_format($totalDue, 2) == '0.00')
    <div class="paid-stamp">PAID</div>
@endif
        
        <div class="document-title">INVOICE</div>
        
        <div class="invoice-header">
            <div class="logo-container">
                @if($businessSettings->logo)
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
        
        <div class="bill-section">
            <div class="bill-to">
                <div class="section-title">Bill To</div>
                <p><strong>{{ $invoice->customer->name }}</strong></p>
                <p>{{ $invoice->customer->address }}</p>
                <p>{{ $invoice->customer->phone }}</p>
                @if($invoice->customer->email)
                    <p>{{ $invoice->customer->email }}</p>
                @endif
            </div>
            
            <div class="invoice-details">
                <h2>INVOICE</h2>
                <p>Date: {{ $invoice->invoice_date->format('d-m-Y') }}</p>
                <p>Invoice #: {{ $invoice->invoice_number }}</p>
                <p>Payment: {{ ucfirst($invoice->payment_method) }}</p>
            </div>
        </div>

        @php
            $totalWeight = 0;
            foreach ($invoice->items as $item) {
                $product = $item->product ?? null;
                $category = $product->category ?? null;

                // Check product weight first, then fall back to category weight
                $weightValue = null;
                $weightUnit = null;

                if ($product && !empty($product->weight_value) && !empty($product->weight_unit)) {
                    // Use product-specific weight (overrides category)
                    $weightValue = (float) $product->weight_value;
                    $weightUnit = $product->weight_unit;
                } elseif ($category && !empty($category->weight_value) && !empty($category->weight_unit)) {
                    // Fall back to category weight
                    $weightValue = (float) $category->weight_value;
                    $weightUnit = $category->weight_unit;
                }

                if (!$weightValue || !$weightUnit) {
                    continue;
                }

                $quantity = (float) $item->quantity;
                $boxes = (float) ($item->boxes ?? 0);
                $pieces = (float) ($item->pieces ?? 0);

                // For per_unit, just multiply quantity by weight - no need for box/pieces calculation
                if ($weightUnit === 'per_unit') {
                    $totalWeight += $quantity * $weightValue;
                    continue;
                }

                // For per_piece and per_box, we need category info
                $boxPcs = (float) ($category->box_pcs ?? 0);
                $piecesFeet = (float) ($category->pieces_feet ?? 0);

                $totalPieces = 0;
                if ($boxPcs > 0) {
                    $totalPieces = ($boxes * $boxPcs) + $pieces;
                } elseif ($pieces > 0) {
                    $totalPieces = $pieces;
                } elseif ($piecesFeet > 0 && $quantity > 0) {
                    $totalPieces = $quantity / $piecesFeet;
                }

                if ($weightUnit === 'per_piece') {
                    $totalWeight += $totalPieces * $weightValue;
                } elseif ($weightUnit === 'per_box') {
                    $boxCount = $boxPcs > 0 ? ($totalPieces / $boxPcs) : $boxes;
                    $totalWeight += $boxCount * $weightValue;
                }
            }
        @endphp
        
        <div class="table-container">
            <table class="invoice-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Product Description</th>
                        <th>Category</th>
                        @if($invoice->invoice_type != 'other')
                            <th style="text-align: center;">Box</th>
                            <th style="text-align: center;">Pcs</th>
                        @endif
                        <th style="text-align: center;">Qty</th>
                        <th style="text-align: right;">Price</th>
                        <th style="text-align: right;">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($invoice->items as $index => $item)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>
                            <div class="product-description">
                                {{ $item->description }}
                            </div>
                            @if($item->code)
                                <div class="product-code">{{ $item->code }}</div>
                            @endif
                        </td>
                        <td style="font-size: 7px;">
                            {{ $item->product->category->name ?? 'N/A' }}
                        </td>
                        @if($invoice->invoice_type != 'other')
                            <td class="quantity-cell">{{ $item->boxes ?? 0 }}</td>
                            <td class="quantity-cell">{{ $item->pieces ?? 0 }}</td>
                        @endif
                        <td class="quantity-cell">{{ number_format($item->quantity, 2) }}</td>
                        <td style="text-align: right;">{{ number_format($item->unit_price, 2) }}</td>
                        <td style="text-align: right; font-weight: 600;">{{ number_format($item->total, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="{{ $invoice->invoice_type != 'other' ? 2 : 2 }}" style="text-align: right;"><strong>TOTAL:</strong></td>
                        <td></td>
                        @if($invoice->invoice_type != 'other')
                            <td class="quantity-cell">
                                <strong>{{ $invoice->items->sum('boxes') }}</strong>
                            </td>
                            <td class="quantity-cell">
                                <strong>{{ $invoice->items->sum('pieces') }}</strong>
                                @if($totalWeight > 0)
                                    <div style="font-size: 10px; color: #6b7280;">Apprx. {{ number_format($totalWeight, 2) }} kg</div>
                                @endif
                            </td>
                        @else
                            {{-- For "other" invoice type, show weight under quantity --}}
                        @endif
                        <td class="quantity-cell">
                            <strong>{{ number_format($invoice->items->sum('quantity'), 2) }}</strong>
                            @if($invoice->invoice_type == 'other' && $totalWeight > 0)
                                <div style="font-size: 10px; color: #6b7280;">Apprx. {{ number_format($totalWeight, 2) }} kg</div>
                            @endif
                        </td>
                        <td></td>
                        <td style="text-align: right; font-weight: 600;">{{ number_format($invoice->subtotal, 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
        
        <div class="amount-section">
            <div class="words-total">
                <p><strong>In Words:</strong> {{ $invoice->total_in_words ?? '' }}</p>
            </div>
            
            <div class="amount-totals">
                <table>
                    <tr>
                        <th>Sub Total:</th>
                        <td>{{ number_format($invoice->subtotal, 2) }}</td>
                    </tr>
                    <tr>
                        <th>Discount:</th>
                        <td>{{ number_format($invoice->discount, 2) }}</td>
                    </tr>
                    <tr class="total-row">
                        <th>Invoice Total:</th>
                        <td>{{ number_format($invoice->total, 2) }}</td>
                    </tr>
                    <tr>
                        <th>Previous Due:</th>
                        <td>{{ number_format($previousDue, 2) }}</td>
                    </tr>
                    <tr class="total-row">
                        <th>Total Payable:</th>
                        <td>{{ number_format($totalPayable, 2) }}</td>
                    </tr>
                    <tr>
                        <th>Given Amount:</th>
                        <td>{{ number_format($givenAmount, 2) }}</td>
                    </tr>
                    <tr class="total-row">
                        <th>Total Due:</th>
                        <td>{{ number_format($totalDue, 2) }}</td>
                    </tr>
                </table>
            </div>
        </div>
        
        <div class="terms-bank">
            <div class="bank-box">
                <div class="bank-title">Bank Info</div>
                @if($businessSettings->bank_details)
                    {!! nl2br(e($businessSettings->bank_details)) !!}
                @else
                    Dutch Bangla Bank Ltd.<br>
                    Shariatpur Branch (Routing: 090860678)<br>
                    A/C Name: {{ $businessSettings->business_name ?? config('adminlte.title') }}<br>
                    A/C No: 181 110 0000 282
                @endif
            </div>
            <div class="terms-box">
                <div class="terms-title">Terms &amp; Conditions</div>
                @if($businessSettings->return_policy_message)
                    {{ $businessSettings->return_policy_message }}
                @else
                    All sold goods should be returnable within {{ $businessSettings->return_policy_days ?? 90 }} days.
                @endif
                @if(!empty($businessSettings->footer_message))
                    <div class="terms-footer">
                        {{ $businessSettings->footer_message }}
                    </div>
                @endif
            </div>
        </div>
        
        <div class="signatures">
            <div class="signature-box">
                <div class="signature-title">Customer</div>
                <div style="font-size: 8px; margin-bottom: 8px;">{{ $invoice->customer->name }}</div>
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
        

<!-- QR Code and Login Instructions -->

<div class="qr-container">
  <div class="qr-box">
    <div style="margin-bottom: 10px;">
        @if($invoice->customer)
            @php
                $settings = \App\Models\BusinessSetting::first();
                $expiryDays = $settings?->customer_qr_expiry_days ?? 30;
                $magicLink = \Illuminate\Support\Facades\URL::temporarySignedRoute(
                    'customer.magic-login',
                    now()->addDays($expiryDays),
                    ['customer' => $invoice->customer->id, 'invoice' => $invoice->id]
                );
            @endphp
            <img src="https://api.qrserver.com/v1/create-qr-code/?size=80x80&data={{ urlencode($magicLink) }}" alt="QR Code" style="width: 80px; height: 80px;">
        @else
            <p>QR code unavailable: Missing customer details.</p>
        @endif
    </div>
  </div>
  <div class="qr-text">
    <p><strong>Access Your Dashboard</strong></p>
    <p>Scan the QR code or visit: <a href="{{ $magicLink ?? route('customer.login') }}" target="_blank">{{ route('customer.dashboard') }}</a></p>
    @if($invoice->customer)
        <p class="text-muted" style="margin-top: 6px;">
            Default login: ID <strong>{{ $invoice->customer->id }}</strong>,
            @if(!$invoice->customer->password)
                Password <strong>{{ $invoice->customer->phone }}</strong>
            @else
                Password <strong>Your custom password</strong>
            @endif
        </p>
    @endif
    <p class="text-muted">This link signs you in securely and opens your invoice.</p>
  </div>
</div>


        <div class="print-actions no-print">
            <button onclick="window.print()" class="print-btn print-btn-primary">
                Print Invoice
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
