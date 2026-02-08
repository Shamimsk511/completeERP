<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delivery Challan #{{ $otherDelivery->challan_number }}</title>
    <style>
    body {
        font-family: Arial, sans-serif;
        margin: 0;
        padding: 0;
        font-size: 12px;
        line-height: 1.4;
        color: #333;
    }
    
    .challan-container {
        max-width: 800px;
        margin: 0 auto;
        padding: 20px;
        position: relative;
    }
    
    .challan-header {
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
    
    .info-section {
        display: flex;
        justify-content: space-between;
        margin-bottom: 15px;
    }
    
    .recipient-info {
        width: 50%;
        text-align: left;
    }
    
    .recipient-info h3 {
        margin: 0 0 3px 0;
        font-size: 12px;
    }
    
    .recipient-info p {
        margin: 1px 0;
        font-size: 11px;
    }
    
    .challan-title {
        width: 50%;
        text-align: right;
    }
    
    .challan-title h2 {
        margin: 0;
        color: blue;
        font-size: 16px;
    }
    
    .challan-title p {
        margin: 1px 0;
        font-size: 11px;
    }
    
    .products-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 5px;
    }
    
    .products-table th, .products-table td {
        border: 1px solid #000;
        padding: 4px;
        text-align: left;
        font-size: 11px;
    }
    
    .products-table th {
        background-color: #f2f2f2;
    }
    
    .notes-section {
        margin: 10px 0;
        font-size: 11px;
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
    <div class="challan-container">
        <div class="challan-header">
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
        
        <div class="info-section">
            <div class="recipient-info">
                <h3>Recipient</h3>
                <p><strong>{{ $otherDelivery->recipient_name }}</strong></p>
                <p>Address: {{ $otherDelivery->recipient_address }}</p>
                <p>Phone: {{ $otherDelivery->recipient_phone ?? 'N/A' }}</p>
            </div>
            <div class="challan-title">
                <h2>DELIVERY CHALLAN</h2>
                <p>Date: {{ $otherDelivery->delivery_date->format('Y-m-d') }}</p>
                <p>Challan ID: #{{ $otherDelivery->challan_number }}</p>
            </div>
        </div>
        
        <div class="vehicle-info" style="margin-bottom: 15px;">
            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    <td style="width: 25%;"><strong>Vehicle Type:</strong></td>
                    <td style="width: 25%;">{{ $otherDelivery->vehicle_type ?? 'N/A' }}</td>
                    <td style="width: 25%;"><strong>Vehicle Number:</strong></td>
                    <td style="width: 25%;">{{ $otherDelivery->vehicle_number ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td><strong>Driver Name:</strong></td>
                    <td>{{ $otherDelivery->driver_name ?? 'N/A' }}</td>
                    <td><strong>Driver Phone:</strong></td>
                    <td>{{ $otherDelivery->driver_phone ?? 'N/A' }}</td>
                </tr>
            </table>
        </div>
        
        <table class="products-table">
            <thead>
                <tr>
                    <th>SL</th>
                    <th>Product Description</th>
                    <th>Category</th>
                    <th>Cartons</th>
                    <th>Pieces</th>
                    <th>Quantity</th>
                </tr>
            </thead>
            <tbody>
                @foreach($otherDelivery->items as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $item->product->name }}
                        @if($item->description)
                        <br><small>{{ $item->description }}</small>
                        @endif
                    </td>
                    <td>{{ $item->product->category->name ?? 'N/A' }}</td>
                    <td>{{ $item->cartons ?? 'N/A' }}</td>
                    <td>{{ $item->pieces ?? 'N/A' }}</td>
                    <td>{{ $item->quantity }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="5" style="text-align: right;"><strong>Total Quantity:</strong></td>
                    <td>{{ $otherDelivery->items->sum('quantity') }}</td>
                </tr>
            </tfoot>
        </table>
        
        @if($otherDelivery->notes)
        <div class="notes-section">
            <strong>Notes:</strong> {{ $otherDelivery->notes }}
        </div>
        @endif
        
        <div class="signatures">
            <div class="signature-box">
                <p>Recipient: {{ $otherDelivery->recipient_name }}</p>
                <p>Signature and Date</p>
            </div>
                <div class="signature-box">
                    <p>Issued By: {{ $businessSettings->business_name ?? config('adminlte.title') }}</p>
                    <p>Signature and Date</p>
                </div>
        </div>
        
        <div class="footer-text">
            <p>This is a computer generated document.</p>
            <p>Delivered By: {{ $otherDelivery->deliveredBy->name ?? 'N/A' }} | Status: {{ ucfirst($otherDelivery->status) }}</p>
            <p>Thanks for being with us.</p>
        </div>
        
        <div class="no-print" style="margin-top: 20px; text-align: center;">
            <button onclick="window.print()" style="padding: 8px 16px; background-color: #4CAF50; color: white; border: none; cursor: pointer; font-size: 14px; border-radius: 4px;">
                Print Challan
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
