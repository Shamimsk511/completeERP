<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stock Count Sheet</title>
    <style>
        @page {
            size: A4;
            margin: 15mm;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            line-height: 1.4;
            color: #333;
        }

        .header {
            text-align: center;
            border-bottom: 2px solid #333;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }

        .header h1 {
            font-size: 20px;
            margin-bottom: 5px;
        }

        .header .date {
            font-size: 12px;
            color: #666;
        }

        .instructions {
            background: #f5f5f5;
            padding: 10px 15px;
            margin-bottom: 20px;
            border-radius: 4px;
            font-size: 10px;
        }

        .instructions strong {
            display: block;
            margin-bottom: 5px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th, td {
            border: 1px solid #ddd;
            padding: 6px 8px;
            text-align: left;
        }

        th {
            background: #f0f0f0;
            font-weight: bold;
            font-size: 10px;
        }

        td {
            font-size: 10px;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .count-column {
            width: 80px;
            background: #fffef0;
        }

        .diff-column {
            width: 70px;
        }

        .product-name {
            font-weight: 500;
        }

        .system-stock {
            font-weight: 600;
        }

        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
        }

        .signature-row {
            display: flex;
            justify-content: space-between;
            margin-top: 40px;
        }

        .signature-box {
            width: 200px;
            text-align: center;
        }

        .signature-line {
            border-top: 1px solid #333;
            padding-top: 5px;
            margin-top: 30px;
        }

        .page-break {
            page-break-after: always;
        }

        @media print {
            .no-print {
                display: none;
            }
        }

        .print-btn {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 10px 20px;
            background: #6366f1;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
        }

        .print-btn:hover {
            background: #4f46e5;
        }
    </style>
</head>
<body>
    <button class="print-btn no-print" onclick="window.print()">
        Print Count Sheet
    </button>

    <div class="header">
        <h1>Physical Stock Count Sheet</h1>
        <div class="date">Generated on: {{ now()->format('d M Y, h:i A') }}</div>
        @if(isset($selectedGodown) && $selectedGodown)
            <div class="date">Godown: {{ $selectedGodown->name }}{{ $selectedGodown->location ? ' - ' . $selectedGodown->location : '' }}</div>
        @endif
    </div>

    <div class="instructions">
        <strong>Instructions:</strong>
        1. Count each product physically and write the count in the "Physical Count" column.<br>
        2. Calculate the difference (Physical Count - System Stock) and write in the "Difference" column.<br>
        3. Initial each row after counting.<br>
        4. After completion, sign at the bottom and submit to management.
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 30px;">#</th>
                <th>Product Name</th>
                <th style="width: 100px;">Company</th>
                <th style="width: 90px;">Category</th>
                <th class="text-right" style="width: 80px;">System Stock</th>
                <th class="count-column text-center">Physical Count</th>
                <th class="diff-column text-right">Difference</th>
                <th style="width: 40px;">Initial</th>
            </tr>
        </thead>
        <tbody>
            @foreach($products as $index => $product)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td class="product-name">{{ $product->name }}</td>
                <td>{{ $product->company->name ?? 'N/A' }}</td>
                <td>{{ $product->category->name ?? 'N/A' }}</td>
                <td class="text-right system-stock">{{ number_format($product->godown_stock ?? $product->current_stock, 2) }}</td>
                <td class="count-column"></td>
                <td class="diff-column"></td>
                <td></td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p><strong>Total Products:</strong> {{ $products->count() }}</p>

        <div class="signature-row">
            <div class="signature-box">
                <div class="signature-line">Counted By</div>
            </div>
            <div class="signature-box">
                <div class="signature-line">Verified By</div>
            </div>
            <div class="signature-box">
                <div class="signature-line">Date</div>
            </div>
        </div>
    </div>
</body>
</html>
