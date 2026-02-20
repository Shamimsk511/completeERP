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
    <title>Challan #{{ $challan->challan_number }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', Arial, sans-serif;
            font-size: 12px;
            line-height: 1.3;
            color: #1f2937;
            background: white;
            margin: 0;
            padding: 0;
        }
        
        .challan-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 15px;
            background: white;
        }
        
        /* Header */
        .challan-header {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 12px;
            border-bottom: 2px solid #374151;
        }
        
        .logo-container {
            width: 60px;
            height: 60px;
            margin-right: 15px;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
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
            font-size: 18px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #1f2937;
            margin-bottom: 4px;
        }
        
        .company-details {
            font-size: 10px;
            color: #4b5563;
            line-height: 1.3;
        }
        
        .company-details span {
            margin: 0 6px;
        }
        
        /* Document Title */
        .document-title {
            position: absolute;
            top: 10px;
            right: 10px;
            background: #1f2937;
            color: white;
            padding: 6px 12px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        /* Bill Section */
        .bill-section {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-bottom: 15px;
        }
        
        .bill-to, .challan-details {
            border: 1px solid #e5e7eb;
            padding: 12px;
            border-radius: 6px;
            background: #f9fafb;
        }
        
        .section-title {
            font-size: 12px;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 8px;
            text-transform: uppercase;
            border-bottom: 1px solid #d1d5db;
            padding-bottom: 4px;
        }
        
        .bill-to p, .challan-details p {
            margin: 3px 0;
            font-size: 11px;
            color: #374151;
            line-height: 1.3;
        }
        
        .detail-item {
            display: flex;
            justify-content: space-between;
            margin: 3px 0;
            font-size: 11px;
            line-height: 1.3;
        }
        
        .detail-label {
            color: #6b7280;
            flex-shrink: 0;
            margin-right: 6px;
        }
        
        .detail-value {
            font-weight: 500;
            color: #1f2937;
            text-align: right;
            word-break: break-word;
        }
        
        /* Table Styles */
        .table-container {
            margin-bottom: 15px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            overflow: hidden;
        }
        
        .challan-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            font-size: 10px;
        }
        
        .challan-table thead {
            background: #f3f4f6;
        }
        
        .challan-table th {
            padding: 8px 6px;
            text-align: left;
            font-size: 9px;
            font-weight: 600;
            color: #1f2937;
            text-transform: uppercase;
            border-bottom: 1px solid #d1d5db;
            border-right: 1px solid #e5e7eb;
        }
        
        .challan-table th:last-child {
            border-right: none;
            text-align: right;
        }
        
        .challan-table th:first-child {
            width: 30px;
            text-align: center;
        }
        
        .challan-table tbody tr:nth-child(even) {
            background: #f9fafb;
        }
        
        .challan-table td {
            padding: 6px 6px;
            font-size: 9px;
            color: #374151;
            border-bottom: 1px solid #e5e7eb;
            border-right: 1px solid #e5e7eb;
            vertical-align: top;
        }
        
        .challan-table td:last-child {
            border-right: none;
            text-align: right;
            font-weight: 500;
        }
        
        .challan-table td:first-child {
            text-align: center;
            font-weight: 500;
        }
        
        .product-description {
            font-weight: 500;
            line-height: 1.2;
        }
        
        .product-code {
            font-size: 8px;
            color: #6b7280;
            font-style: italic;
            margin-top: 2px;
        }
        
        .category-cell {
            font-size: 8px;
            color: #4b5563;
        }
        
        .quantity-cell {
            text-align: center !important;
            font-weight: 500;
        }
        
        /* Footer Row */
        .challan-table tfoot {
            background: #f1f5f9;
        }
        
        .challan-table tfoot td {
            padding: 8px 6px;
            font-weight: 600;
            color: #1f2937;
            border-bottom: none;
            border-top: 2px solid #374151;
            font-size: 10px;
        }
        
        /* Other Sections */
        .notes-section {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            padding: 10px;
            margin-bottom: 12px;
        }
        
        .notes-title {
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 6px;
            font-size: 10px;
            text-transform: uppercase;
        }
        
        .notes-content {
            color: #4b5563;
            font-size: 10px;
            line-height: 1.3;
        }
        
        .disclaimer {
            text-align: center;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            padding: 8px;
            margin: 12px 0;
            font-weight: 500;
            color: #1f2937;
            font-size: 10px;
            text-transform: uppercase;
            background: #f9fafb;
        }
        
        .signatures {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-bottom: 12px;
        }
        
        .signature-box {
            text-align: center;
            padding: 12px 10px 8px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            background: white;
            height: 70px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        
        .signature-title {
            font-weight: 600;
            color: #1f2937;
            font-size: 10px;
            text-transform: uppercase;
            margin-bottom: 3px;
        }
        
        .signature-company {
            font-size: 9px;
            margin-bottom: 6px;
            color: #374151;
        }
        
        .signature-line {
            border-top: 1px solid #374151;
            margin: 6px auto 3px;
            width: 120px;
        }
        
        .signature-label {
            font-size: 8px;
            color: #6b7280;
        }
        
        .footer-text {
            text-align: center;
            font-size: 10px;
            color: #6b7280;
            margin-bottom: 12px;
            font-style: italic;
        }
        
        /* Print Buttons */
        .print-actions {
            display: flex;
            justify-content: center;
            gap: 6px;
            margin-top: 10px;
            padding-top: 8px;
            border-top: 1px solid #e5e7eb;
        }
        
        .print-btn {
            padding: 4px 10px;
            border: 1px solid #d1d5db;
            border-radius: 3px;
            font-size: 8px;
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
        
        /* Print Styles */
        @media print {
            body {
                print-color-adjust: exact;
                -webkit-print-color-adjust: exact;
                font-size: 12px !important;
            }
            
            .challan-container {
                padding: 10px !important;
                margin: 0 !important;
                max-width: none !important;
            }
            
            .no-print {
                display: none !important;
            }
            
            .document-title {
                position: static !important;
                display: inline-block !important;
                margin-bottom: 10px !important;
            }
            
            .challan-header {
                page-break-after: avoid;
            }
            
            .bill-section {
                page-break-inside: avoid;
            }
            
            .table-container {
                page-break-before: avoid;
            }
            
            .signatures {
                page-break-before: avoid;
            }
            
            .challan-table th {
                font-size: 9px !important;
            }
            
            .challan-table td {
                font-size: 9px !important;
            }
        }
        
        /* Mobile Responsive */
        @media (max-width: 768px) {
            .challan-container {
                padding: 8px;
            }
            
            .challan-header {
                flex-direction: column;
                text-align: center;
                gap: 6px;
            }
            
            .logo-container {
                margin-right: 0;
                margin-bottom: 6px;
            }
            
            .bill-section {
                grid-template-columns: 1fr;
                gap: 8px;
            }
            
            .bill-to, .challan-details {
                height: auto;
            }
            
            .signatures {
                grid-template-columns: 1fr;
                gap: 8px;
            }
            
            .signature-box {
                height: auto;
            }
            
            .document-title {
                position: static;
                margin-bottom: 8px;
                display: inline-block;
            }
        }
        
        @page {
            margin: 0.3in;
            size: A4;
        }
    </style>
    @include('partials.print-theme-styles')
</head>
<body class="print-theme template-{{ $activePrintTemplate }}">
    <div class="challan-container">
        <div class="document-title">DELIVERY CHALLAN</div>
        
        <div class="challan-header">
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
                <div class="section-title">Deliver To</div>
                @if($challan->invoice && $challan->invoice->customer)
                    <p><strong>{{ $challan->invoice->customer->name }}</strong></p>
                    <p>{{ $challan->invoice->customer->address }}</p>
                    <p>{{ $challan->invoice->customer->phone }}</p>
                    @if($challan->invoice->customer->email)
                        <p>{{ $challan->invoice->customer->email }}</p>
                    @endif
                @else
                    <p><strong>{{ $challan->customer_name ?? 'N/A' }}</strong></p>
                    <p>{{ $challan->address ?? 'N/A' }}</p>
                    <p>{{ $challan->phone ?? 'N/A' }}</p>
                @endif
                
                @if($challan->receiver_name)
                    <hr style="margin: 3px 0; border: none; border-top: 1px solid #e5e7eb;">
                    <p><strong>Receiver:</strong> {{ $challan->receiver_name }}</p>
                    @if($challan->receiver_phone)
                        <p><strong>Phone:</strong> {{ $challan->receiver_phone }}</p>
                    @endif
                @endif
            </div>
            
            <div class="challan-details">
                <div class="section-title">Challan Details</div>
                <div class="detail-item">
                    <span class="detail-label">Date:</span>
                    <span class="detail-value">{{ $challan->challan_date->format('d-m-Y') }}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Challan #:</span>
                    <span class="detail-value">{{ $challan->challan_number }}</span>
                </div>
                @if($challan->invoice)
                    <div class="detail-item">
                        <span class="detail-label">Invoice #:</span>
                        <span class="detail-value">{{ $challan->invoice->invoice_number }}</span>
                    </div>
                @endif
                <div class="detail-item">
                    <span class="detail-label">Status:</span>
                    <span class="detail-value">{{ ucfirst($challan->status) }}</span>
                </div>
                @if($challan->vehicle_number)
                    <div class="detail-item">
                        <span class="detail-label">Vehicle:</span>
                        <span class="detail-value">{{ $challan->vehicle_number }}</span>
                    </div>
                @endif
                @if($challan->driver_name)
                    <div class="detail-item">
                        <span class="detail-label">Driver:</span>
                        <span class="detail-value">{{ $challan->driver_name }}</span>
                    </div>
                @endif
            </div>
        </div>
        
        @php
            $totalWeight = 0;
            foreach ($challan->items as $item) {
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
            <table class="challan-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Product Description</th>
                        <th>Category</th>
                        <th>Godown</th>
                        @if($challan->invoice && $challan->invoice->invoice_type != 'other')
                            <th style="text-align: center;">Box</th>
                            <th style="text-align: center;">Pcs</th>
                        @endif
                        <th style="text-align: right;">Quantity</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($challan->items as $index => $item)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>
                            <div class="product-description">
                                {{ $item->invoiceItem->description ?? $item->description ?? ($item->product->name ?? 'N/A') }}
                            </div>
                            @php($productCode = $item->invoiceItem->code ?? ($item->code ?? null))
                            @if($productCode)
                                <div class="product-code">{{ $productCode }}</div>
                            @endif
                        </td>
                        <td class="category-cell">
                            {{ $item->product->category->name ?? 'N/A' }}
                        </td>
                        <td>
                            {{ $item->godown->name ?? '-' }}
                            @if($item->godown && $item->godown->location)
                                <div class="product-code">{{ $item->godown->location }}</div>
                            @endif
                        </td>
                        @if($challan->invoice && $challan->invoice->invoice_type != 'other')
                            <td class="quantity-cell">{{ $item->boxes ?? 0 }}</td>
                            <td class="quantity-cell">{{ $item->pieces ?? 0 }}</td>
                        @endif
                        <td style="text-align: right; font-weight: 500;">
                            {{ number_format($item->quantity, 2) }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="4" style="text-align: right;">
                            <strong>TOTAL:</strong>
                        </td>
                        @if($challan->invoice && $challan->invoice->invoice_type != 'other')
                            <td class="quantity-cell" style="font-weight: 600;">
                                {{ $challan->items->sum('boxes') ?? 0 }}
                            </td>
                            <td class="quantity-cell" style="font-weight: 600;">
                                {{ $challan->items->sum('pieces') ?? 0 }}
                                @if($totalWeight > 0)
                                    <div style="font-size: 10px; color: #6b7280;">Apprx. {{ number_format($totalWeight, 2) }} kg</div>
                                @endif
                            </td>
                        @else
                            {{-- For "other" invoice type, show weight under quantity --}}
                        @endif
                        <td style="text-align: right; font-weight: 600;">
                            {{ number_format($challan->items->sum('quantity'), 2) }}
                            @if((!$challan->invoice || $challan->invoice->invoice_type == 'other') && $totalWeight > 0)
                                <div style="font-size: 10px; color: #6b7280;">Apprx. {{ number_format($totalWeight, 2) }} kg</div>
                            @endif
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
        
        @if(isset($challan->notes) && $challan->notes)
        <div class="notes-section">
            <div class="notes-title">Notes</div>
            <div class="notes-content">{{ $challan->notes }}</div>
        </div>
        @endif
        
        <div class="disclaimer">
            This is a delivery challan only, not an invoice.
        </div>
        
        <div class="signatures">
            <div class="signature-box">
                <div class="signature-title">Received By</div>
                <div class="signature-line"></div>
                <div class="signature-label">Signature & Date</div>
            </div>
            <div class="signature-box">
                <div class="signature-title">Authorized By</div>
                <div class="signature-company">
                    {{ $businessSettings->business_name ?? config('adminlte.title') }}
                </div>
                <div class="signature-line"></div>
                <div class="signature-label">Signature & Date</div>
            </div>
        </div>
        
        <div class="footer-text">
            Thanks for being with us.
        </div>
        
        <div class="print-actions no-print">
            <button onclick="window.print()" class="print-btn print-btn-primary">
                Print Challan
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
