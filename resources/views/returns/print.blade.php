<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Return Invoice #{{ $return->return_number }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
        }
        
        .return-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            position: relative;
        }
        
        .return-header {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .logo-container {
            width: 80px;
            margin-right: 10px;
        }
        
        .logo-container img {
            width: 100%;
            height: auto;
        }
        
        .company-info {
            text-align: center;
            flex-grow: 1;
        }
        
        .company-info h1 {
            margin: 0;
            font-size: 16px;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .company-info p {
            margin: 1px 0;
            font-size: 11px;
            line-height: 1.3;
        }
        
        .bill-section {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
        }
        
        .bill-to {
            width: 50%;
            text-align: left;
        }
        
        .bill-to h3 {
            margin: 0 0 3px 0;
            font-size: 12px;
        }
        
        .bill-to p {
            margin: 1px 0;
            font-size: 11px;
        }
        
        .return-title {
            width: 50%;
            text-align: right;
        }
        
        .return-title h2 {
            margin: 0;
            color: blue;
            font-size: 16px;
        }
        
        .return-title p {
            margin: 1px 0;
            font-size: 11px;
        }
        
        .return-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 5px;
        }
        
        .return-table th, .return-table td {
            border: 1px solid #000;
            padding: 4px;
            text-align: left;
            font-size: 11px;
        }
        
        .return-table th {
            background-color: #f2f2f2;
        }
        
        .words-total {
            text-align: left;
            font-style: italic;
            margin: 3px 0;
            font-size: 11px;
        }
        
        .words-total p {
            margin: 0;
        }
        
        .amount-totals {
            width: 40%;
            float: right;
            margin-top: 3px;
        }
        
        .amount-totals table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .amount-totals table th, .amount-totals table td {
            padding: 2px;
            text-align: right;
            border: none;
            font-size: 11px;
            line-height: 1.2;
        }
        
        .amount-totals table th {
            font-weight: normal;
            padding-right: 8px;
            white-space: nowrap;
        }
        
        .amount-totals table td {
            min-width: 80px;
        }
        
        .amount-totals table tr.total-row th,
        .amount-totals table tr.total-row td {
            font-weight: bold;
            border-top: 1px solid #ddd;
            border-bottom: 1px solid #ddd;
            padding-top: 3px;
            padding-bottom: 3px;
        }
        
        .disclaimer {
            clear: both;
            text-align: center;
            margin: 15px 0 10px 0;
            font-weight: bold;
            font-size: 11px;
        }
        
        .payment-info {
            font-size: 11px;
            margin: 15px 0;
        }
        
        .signatures {
            display: flex;
            justify-content: space-between;
            margin-top: 30px;
        }
        
        .signature-box {
            width: 45%;
            text-align: center;
            border-top: 1px solid #333;
            padding-top: 2px;
        }
        
        .signature-box p {
            margin: 1px 0;
            font-size: 10px;
        }
        
        .footer-text {
            text-align: center;
            font-size: 10px;
            margin-top: 5px;
        }
        
        .paid-stamp {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-30deg);
            font-size: 80px;
            color: rgba(255, 0, 0, 0.3);
            font-weight: bold;
            border: 10px solid rgba(255, 0, 0, 0.3);
            padding: 10px 20px;
            border-radius: 10px;
            z-index: 100;
            pointer-events: none;
        }
        
        @media print {
            body {
                print-color-adjust: exact;
                -webkit-print-color-adjust: exact;
            }
            
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="return-container">
        @if($return->total == 0)
        <div class="paid-stamp">PAID</div>
        @endif
        
        <div class="return-header">
            <div class="logo-container">
                <img src="{{ asset('logo/logo.png') }}" alt="RTS Logo">
            </div>
                <div class="company-info">
                    <h1>{{ $businessSettings->business_name ?? config('adminlte.title') }}</h1>
                    @if(!empty($businessSettings->phone))
                        <p>Phone: {{ $businessSettings->phone }}</p>
                    @endif
                    @if(!empty($businessSettings->email))
                        <p>Email: {{ $businessSettings->email }}</p>
                    @endif
                    @if(!empty($businessSettings->address) || !empty($businessSettings->bin_number))
                        <p>
                            {{ $businessSettings->address }}
                            @if(!empty($businessSettings->bin_number))
                                (BIN: {{ $businessSettings->bin_number }})
                            @endif
                        </p>
                    @endif
                </div>
        </div>
        
        <div class="bill-section">
            <div class="bill-to">
                <h3>Customer Details</h3>
                <p><strong>{{ $return->customer->name }}</strong></p>
                <p>Address: {{ $return->customer->address }}</p>
                <p>Phone: {{ $return->customer->phone }}</p>
                @if(isset($return->customer->email) && $return->customer->email)
                <p>Email: {{ $return->customer->email }}</p>
                @endif
            </div>
            <div class="return-title">
                <h2>RETURN INVOICE</h2>
                <p>Date: {{ $return->return_date->format('Y-m-d') }}</p>
                <p>Return ID: #{{ $return->return_number }}</p>
                @if(isset($return->invoice_number) && $return->invoice_number)
                <p>Original Invoice: #{{ $return->invoice_number }}</p>
                @endif
            </div>
        </div>
        
        <table class="return-table">
            <thead>
                <tr>
                    <th>SL</th>
                    <th>Product</th>
                    <th>Description</th>
                    <th>Quantity</th>
                    <th>Unit Price</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($return->items as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $item->product->name }}</td>
                    <td>{{ $item->description }}
                        @if(isset($item->code) && $item->code)
                        <br><small>{{ $item->code }}</small>
                        @endif
                    </td>
                    <td>{{ number_format($item->quantity, 2) }}</td>
                    <td>{{ number_format($item->unit_price, 2) }}</td>
                    <td>{{ number_format($item->total, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        
        <div class="amount-totals">
            <table>
                <tr>
                    <th>Sub Total:</th>
                    <td>{{ number_format($return->subtotal, 2) }}</td>
                </tr>
                @if(isset($return->tax) && $return->tax > 0)
                <tr>
                    <th>Tax:</th>
                    <td>{{ number_format($return->tax, 2) }}</td>
                </tr>
                @endif
                <tr class="total-row">
                    <th>Total Amount:</th>
                    <td>{{ number_format($return->total, 2) }}</td>
                </tr>
                <tr>
                    <th>Outstanding Before Return:</th>
                    <td>{{ number_format($outstandingBeforeReturn ?? 0, 2) }}</td>
                </tr>
                @if(($refundTotal ?? 0) > 0)
                <tr>
                    <th>Refund Paid:</th>
                    <td>{{ number_format($refundTotal, 2) }}</td>
                </tr>
                @endif
                <tr class="total-row">
                    <th>Outstanding After Return:</th>
                    <td>{{ number_format($outstandingAfterReturn ?? 0, 2) }}</td>
                </tr>
            </table>
        </div>
        
        <div style="clear: both;"></div>
        
        <div class="payment-info">
            <p><strong>Payment Method:</strong> {{ ucfirst(str_replace('_', ' ', $return->payment_method)) }}</p>
            @if(isset($return->notes) && $return->notes)
            <p><strong>Notes:</strong> {{ $return->notes }}</p>
            @endif
        </div>
        
        <div class="disclaimer">
            This document confirms the return of goods listed above.
        </div>
        
        <div class="signatures">
            <div class="signature-box">
                <p>Customer: {{ $return->customer->name }}</p>
                <p>Signature and Date</p>
            </div>
                <div class="signature-box">
                    <p>Issued By: {{ $businessSettings->business_name ?? config('adminlte.title') }}</p>
                    <p>Signature and Date</p>
                </div>
        </div>
        
        <div class="footer-text">
            Thanks for being with us.
        </div>
        
        <div class="no-print" style="margin-top: 20px; text-align: center;">
            <button onclick="window.print()" style="padding: 8px 16px; background-color: #4CAF50; color: white; border: none; cursor: pointer; font-size: 14px; border-radius: 4px;">
                Print Return Invoice
            </button>
            <button onclick="window.close()" style="padding: 8px 16px; background-color: #f44336; color: white; border: none; cursor: pointer; font-size: 14px; margin-left: 10px; border-radius: 4px;">
                Close
            </button>
        </div>
    </div>
    
    <script>
        window.onload = function() {
            // Uncomment to automatically print when the page loads
            // window.print();
        };
    </script>
</body>
</html>
