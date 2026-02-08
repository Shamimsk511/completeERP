@extends('layouts.modern-admin')

@section('title', 'Invoice Details')

@section('page_title', 'Invoice #' . $invoice->invoice_number)

@section('header_actions')
    <div class="btn-group">
        <a href="{{ route('invoices.index') }}" class="btn modern-btn modern-btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to List
        </a>
        <a href="{{ route('invoices.edit', $invoice) }}" class="btn modern-btn modern-btn-warning">
            <i class="fas fa-edit"></i> Edit
        </a>
        <a href="{{ route('invoices.print', $invoice) }}" class="btn modern-btn modern-btn-primary" target="_blank">
            <i class="fas fa-print"></i> Print
        </a>
        @if($invoice->delivery_status != 'delivered')
        <a href="{{ route('challans.create', ['invoice_id' => $invoice->id]) }}" class="btn modern-btn modern-btn-info">
            <i class="fas fa-truck"></i> Create Challan
        </a>
        <button type="button" class="btn modern-btn modern-btn-success" data-toggle="modal" data-target="#deliveryModal">
            <i class="fas fa-check-circle"></i> Mark as Delivered
        </button>
        @endif
    </div>
@stop

@section('page_content')
    @if(session('success'))
        <div class="alert alert-success alert-dismissible modern-alert">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
            {{ session('success') }}
        </div>
    @endif
    
    <!-- Invoice and Customer Information Row -->
    <div class="row">
        <!-- Invoice Information -->
        <div class="col-md-6">
            <div class="card modern-card">
                <div class="card-header modern-header">
                    <h3 class="card-title"><i class="fas fa-file-invoice"></i> Invoice Information</h3>
                </div>
                <div class="card-body">
                    <div class="info-section">
                        <div class="section-content">
                            <div class="detail-row">
                                <label>Invoice Number:</label>
                                <span class="detail-value">{{ $invoice->invoice_number }}</span>
                            </div>
                            
                            <div class="detail-row">
                                <label>Invoice Date:</label>
                                <span class="detail-value">{{ $invoice->invoice_date->format('d M, Y') }}</span>
                            </div>
                            
                            <div class="detail-row">
                                <label>Invoice Type:</label>
                                <span class="detail-value">
                                    <span class="badge modern-badge modern-badge-info">
                                        <i class="fas fa-{{ $invoice->invoice_type == 'tiles' ? 'th-large' : 'cube' }}"></i>
                                        {{ ucfirst($invoice->invoice_type) }}
                                    </span>
                                </span>
                            </div>
                            
                            <div class="detail-row">
                                <label>Delivery Status:</label>
                                <span class="detail-value">
                                    <span class="badge modern-badge modern-badge-{{ $invoice->delivery_status == 'delivered' ? 'success' : ($invoice->delivery_status == 'partial' ? 'warning' : 'secondary') }}">
                                        <i class="fas fa-{{ $invoice->delivery_status == 'delivered' ? 'check' : ($invoice->delivery_status == 'partial' ? 'clock' : 'times') }}"></i>
                                        {{ ucfirst($invoice->delivery_status) }}
                                    </span>
                                </span>
                            </div>
                            
                            <div class="detail-row">
                                <label>Payment Method:</label>
                                <span class="detail-value">{{ ucfirst(str_replace('_', ' ', $invoice->payment_method)) }}</span>
                            </div>
                            
                            <div class="detail-row">
                                <label>Payment Status:</label>
                                <span class="detail-value">
                                    @if($invoice->payment_status == 'paid')
                                        <span class="badge modern-badge modern-badge-success">
                                            <i class="fas fa-check-circle"></i> Paid
                                        </span>
                                    @elseif($invoice->payment_status == 'partial')
                                        <span class="badge modern-badge modern-badge-warning">
                                            <i class="fas fa-exclamation-circle"></i> Partial
                                        </span>
                                    @else
                                        <span class="badge modern-badge modern-badge-danger">
                                            <i class="fas fa-times-circle"></i> Due
                                        </span>
                                    @endif
                                </span>
                            </div>

                            <div class="detail-row">
                                <label>Referrer:</label>
                                <span class="detail-value">
                                    @if($invoice->referrer)
                                        <a href="{{ route('referrers.show', $invoice->referrer_id) }}" class="modern-link">
                                            {{ $invoice->referrer->name }}
                                        </a>
                                        @if($invoice->referrer->phone)
                                            <small class="text-muted"> - {{ $invoice->referrer->phone }}</small>
                                        @endif
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </span>
                            </div>

                            @if($invoice->referrer)
                            <div class="detail-row">
                                <label>Compensated:</label>
                                <span class="detail-value">
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox"
                                               class="custom-control-input"
                                               id="invoice-referrer-compensated"
                                               {{ $invoice->referrer_compensated ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="invoice-referrer-compensated">Mark Compensated</label>
                                    </div>
                                </span>
                            </div>
                            @endif
                            
                            <div class="detail-row">
                                <label>Created At:</label>
                                <span class="detail-value">{{ $invoice->created_at->format('d M, Y h:i A') }}</span>
                            </div>
                            
                            <div class="detail-row">
                                <label>Updated At:</label>
                                <span class="detail-value">{{ $invoice->updated_at->format('d M, Y h:i A') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Customer Information -->
        <div class="col-md-6">
            <div class="card modern-card">
                <div class="card-header modern-header">
                    <h3 class="card-title"><i class="fas fa-user"></i> Customer Information</h3>
                </div>
                <div class="card-body">
                    <div class="info-section">
                        <div class="section-content">
                            <div class="detail-row">
                                <label>Customer Name:</label>
                                <span class="detail-value">
                                    <a href="{{ route('customers.show', $invoice->customer_id) }}" class="modern-link">
                                        {{ $invoice->customer->name }}
                                    </a>
                                </span>
                            </div>
                            
                            <div class="detail-row">
                                <label>Phone:</label>
                                <span class="detail-value">{{ $invoice->customer->phone }}</span>
                            </div>
                            
                            <div class="detail-row">
                                <label>Address:</label>
                                <span class="detail-value">{{ $invoice->customer->address }}</span>
                            </div>
                            
                            <div class="detail-row">
                                <label>Opening Balance:</label>
                                <span class="detail-value">{{ number_format($invoice->customer->opening_balance, 2) }}</span>
                            </div>
                            
                            <div class="detail-row">
                                <label>Outstanding Balance:</label>
                                <span class="detail-value amount-highlight">
                                    {{ $ledgerType === 'credit' ? '-' : '' }}{{ number_format($ledgerOutstanding, 2) }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Related Challans Section -->
    @if($invoice->challans->count() > 0)
    <div class="card modern-card mt-4">
        <div class="card-header modern-header d-flex justify-content-between align-items-center">
            <h3 class="card-title"><i class="fas fa-truck"></i> Related Challans ({{ $invoice->challans->count() }})</h3>
            @if($invoice->delivery_status != 'delivered')
            <a href="{{ route('challans.create', ['invoice_id' => $invoice->id]) }}" class="btn btn-sm modern-btn modern-btn-success">
                <i class="fas fa-plus"></i> Create New Challan
            </a>
            @endif
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table modern-table mb-0">
                    <thead>
                        <tr>
                            <th>Challan Number</th>
                            <th>Date</th>
                            <th>Delivered At</th>
                            <th>Vehicle</th>
                            <th>Driver</th>
                            <th>Receiver</th>
                            <th>Items</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($invoice->challans as $challan)
                        <tr>
                            <td>
                                <a href="{{ route('challans.show', $challan) }}" class="modern-link font-weight-bold">
                                    {{ $challan->challan_number }}
                                </a>
                            </td>
                            <td>{{ $challan->challan_date->format('d M, Y') }}</td>
                            <td>
                                <span class="badge modern-badge modern-badge-success">
                                    <i class="fas fa-check-circle"></i>
                                    {{ $challan->delivered_at ? $challan->delivered_at->format('d M, Y H:i') : $challan->created_at->format('d M, Y H:i') }}
                                </span>
                            </td>
                            <td>{{ $challan->vehicle_number ?: 'N/A' }}</td>
                            <td>
                                @if($challan->driver_name)
                                    {{ $challan->driver_name }}
                                    @if($challan->driver_phone)
                                        <br><small class="text-muted">{{ $challan->driver_phone }}</small>
                                    @endif
                                @else
                                    N/A
                                @endif
                            </td>
                            <td>
                                {{ $challan->receiver_name }}
                                @if($challan->receiver_phone)
                                    <br><small class="text-muted">{{ $challan->receiver_phone }}</small>
                                @endif
                            </td>
                            <td>
                                <span class="badge badge-light">{{ $challan->items->count() }} items</span>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('challans.show', $challan) }}" class="btn btn-info btn-sm" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('challans.edit', $challan) }}" class="btn btn-primary btn-sm" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="{{ route('challans.print', $challan) }}" class="btn btn-secondary btn-sm" target="_blank" title="Print">
                                        <i class="fas fa-print"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif
    
    <!-- Related Transactions Section -->
    @if($invoice->transactions->count() > 0)
    <div class="card modern-card mt-4">
        <div class="card-header modern-header d-flex justify-content-between align-items-center">
            <h3 class="card-title"><i class="fas fa-money-bill-wave"></i> Related Transactions ({{ $invoice->transactions->count() }})</h3>
            <a href="{{ route('transactions.create', ['customer_id' => $invoice->customer_id, 'invoice_id' => $invoice->id]) }}" class="btn btn-sm modern-btn modern-btn-success">
                <i class="fas fa-plus"></i> Add Payment
            </a>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table modern-table mb-0">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Type</th>
                            <th>Purpose</th>
                            <th>Method</th>
                            <th>Amount</th>
                            <th>Reference</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($invoice->transactions as $transaction)
                        <tr>
                            <td>{{ $transaction->created_at->format('d M, Y') }}</td>
                            <td>
                                @if($transaction->type == 'debit')
                                    <span class="badge modern-badge modern-badge-success">
                                        <i class="fas fa-arrow-down"></i> Payment
                                    </span>
                                @else
                                    <span class="badge modern-badge modern-badge-warning">
                                        <i class="fas fa-arrow-up"></i> Charge
                                    </span>
                                @endif
                            </td>
                            <td>{{ $transaction->purpose }}</td>
                            <td>
                                @php
                                    $methods = [
                                        'cash' => 'Cash',
                                        'bank' => 'Bank',
                                        'mobile_bank' => 'Mobile Bank',
                                        'cheque' => 'Cheque'
                                    ];
                                @endphp
                                <span class="badge badge-light">{{ $methods[$transaction->method] ?? ucfirst($transaction->method) }}</span>
                            </td>
                            <td class="font-weight-bold">৳{{ number_format($transaction->amount, 2) }}</td>
                            <td>{{ $transaction->reference ?: '-' }}</td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('transactions.show', $transaction) }}" class="btn btn-info btn-sm" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('transactions.edit', $transaction) }}" class="btn btn-primary btn-sm" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        @php
                            $totalCharges = $invoice->transactions->where('type', 'credit')->sum('amount');
                            $totalPayments = $invoice->transactions->where('type', 'debit')->sum('amount');
                        @endphp
                        <tr class="bg-light">
                            <td colspan="4" class="font-weight-bold">Total Charges (Credit):</td>
                            <td class="font-weight-bold text-warning">৳{{ number_format($totalCharges, 2) }}</td>
                            <td colspan="2"></td>
                        </tr>
                        <tr class="bg-light">
                            <td colspan="4" class="font-weight-bold">Total Payments (Debit):</td>
                            <td class="font-weight-bold text-success">৳{{ number_format($totalPayments, 2) }}</td>
                            <td colspan="2"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
    @endif
    
    <!-- Invoice Items -->
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
            $boxPcs = $category ? (float) ($category->box_pcs ?? 0) : 0;
            $piecesFeet = $category ? (float) ($category->pieces_feet ?? 0) : 0;

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
    <div class="card modern-card mt-4">
        <div class="card-header modern-header">
            <h3 class="card-title"><i class="fas fa-boxes"></i> Invoice Items</h3>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table modern-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Product</th>
                            <th>Description</th>
                            <th>Company</th>
                            <th>Category</th>
                            <th>Quantity</th>
                            @if($invoice->invoice_type != 'other')
                            <th class="box-pieces-column">Box</th>
                            <th class="box-pieces-column">Pieces</th>
                            @endif
                            <th>Unit Price</th>
                            <th>Total</th>
                            @if($invoice->challans->count() > 0)
                            <th>Delivered</th>
                            <th>Remaining</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($invoice->items as $index => $item)
                        @php
                            $deliveredQty = $item->challanItems->sum('quantity');
                            $remainingQty = $item->quantity - $deliveredQty;
                        @endphp
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $item->product->name }}</td>
                            <td>
                                {{ $item->description }}
                                @if($item->code)
                                <br><small class="text-muted">{{ $item->code }}</small>
                                @endif
                            </td>
                            <td>{{ $item->product->company->name ?? 'N/A' }}</td>
                            <td>{{ $item->product->category->name ?? 'N/A' }}</td>
                            <td>{{ number_format($item->quantity, 2) }}</td>
                            @if($invoice->invoice_type != 'other')
                            <td class="box-pieces-cell">{{ $item->boxes }}</td>
                            <td class="box-pieces-cell">{{ $item->pieces }}</td>
                            @endif
                            <td>{{ number_format($item->unit_price, 2) }}</td>
                            <td>{{ number_format($item->total, 2) }}</td>
                            @if($invoice->challans->count() > 0)
                            <td>
                                <span class="badge badge-{{ $deliveredQty > 0 ? 'success' : 'light' }}">
                                    {{ number_format($deliveredQty, 2) }}
                                </span>
                            </td>
                            <td>
                                <span class="badge badge-{{ $remainingQty > 0 ? 'warning' : 'success' }}">
                                    {{ number_format($remainingQty, 2) }}
                                </span>
                            </td>
                            @endif
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="bg-light font-weight-bold">
                            <td colspan="5" class="text-right">TOTAL:</td>
                            <td>
                                <div>{{ number_format($invoice->items->sum('quantity'), 2) }}</div>
                                @if($invoice->invoice_type == 'other' && $totalWeight > 0)
                                <small class="text-muted">Apprx. {{ number_format($totalWeight, 2) }} kg</small>
                                @endif
                            </td>
                            @if($invoice->invoice_type != 'other')
                            <td class="box-pieces-cell">{{ $invoice->items->sum('boxes') }}</td>
                            <td class="box-pieces-cell">
                                <div>{{ $invoice->items->sum('pieces') }}</div>
                                @if($totalWeight > 0)
                                <small class="text-muted">Apprx. {{ number_format($totalWeight, 2) }} kg</small>
                                @endif
                            </td>
                            @endif
                            <td></td>
                            <td>{{ number_format($invoice->items->sum('total'), 2) }}</td>
                            @if($invoice->challans->count() > 0)
                            <td></td>
                            <td></td>
                            @endif
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Totals and Notes Row -->
    <div class="row mt-4">
        <!-- Notes Section -->
        @if($invoice->notes)
        <div class="col-md-6">
            <div class="card modern-card">
                <div class="card-header modern-header">
                    <h3 class="card-title"><i class="fas fa-sticky-note"></i> Notes</h3>
                </div>
                <div class="card-body">
                    <p class="mb-0">{{ $invoice->notes }}</p>
                </div>
            </div>
        </div>
        @endif
        
        <!-- Invoice Totals -->
        <div class="col-md-{{ $invoice->notes ? '6' : '12' }}">
            <div class="card modern-card">
                <div class="card-header totals-header">
                    <h3 class="card-title"><i class="fas fa-calculator"></i> Invoice Totals</h3>
                </div>
                <div class="card-body">
                    @php
                        // Snapshot data (at time of invoice creation)
                        $previousBalance = $invoice->previous_balance ?? 0;
                        $initialPaidAmount = $invoice->initial_paid_amount ?? 0;
                        $totalPayable = $invoice->total + $previousBalance;
                        $initialDue = $totalPayable - $initialPaidAmount;

                        // Current allocation data
                        $totalPaymentsMade = $invoice->transactions->where('type', 'debit')->sum('amount');
                    @endphp

                    <div class="totals-section">
                        <div class="total-row">
                            <label>Subtotal:</label>
                            <span class="total-value">{{ number_format($invoice->subtotal, 2) }}</span>
                        </div>

                        <div class="total-row">
                            <label>Discount:</label>
                            <span class="total-value">{{ number_format($invoice->discount, 2) }}</span>
                        </div>

                        <div class="total-row total-main">
                            <label>Invoice Total:</label>
                            <span class="total-value">{{ number_format($invoice->total, 2) }}</span>
                        </div>

                        <div class="total-row">
                            <label>Previous Due (at invoice time):</label>
                            <span class="total-value">{{ number_format($previousBalance, 2) }}</span>
                        </div>

                        <div class="total-row">
                            <label>Total Payable:</label>
                            <span class="total-value">{{ number_format($totalPayable, 2) }}</span>
                        </div>

                        <div class="total-row">
                            <label>Payment at Invoice Time:</label>
                            <span class="total-value text-success">{{ number_format($initialPaidAmount, 2) }}</span>
                        </div>

                        <div class="total-row total-highlight">
                            <label>Due at Invoice Time:</label>
                            <span class="total-value">{{ number_format($initialDue, 2) }}</span>
                        </div>
                    </div>

                    <!-- Current Status Section -->
                    <hr class="my-3">
                    <h6 class="text-muted mb-2"><i class="fas fa-sync-alt"></i> Current Status (After Payment Allocation)</h6>
                    <div class="totals-section">
                        <div class="total-row">
                            <label>Allocated Payment:</label>
                            <span class="total-value">{{ number_format($invoice->paid_amount, 2) }}</span>
                        </div>

                        @if($totalPaymentsMade > 0)
                        <div class="total-row">
                            <label>Total Payments (This Invoice):</label>
                            <span class="total-value text-success">{{ number_format($totalPaymentsMade, 2) }}</span>
                        </div>
                        @endif

                        <div class="total-row">
                            <label>Current Due Amount:</label>
                            <span class="total-value {{ $invoice->due_amount > 0 ? 'text-danger' : 'text-success' }}">
                                {{ number_format($invoice->due_amount, 2) }}
                            </span>
                        </div>

                        @php
                            $ledgerOutstandingSign = $ledgerType === 'credit' ? '-' : '';
                            $ledgerOutstandingClass = $ledgerType === 'credit' ? 'text-success' : 'text-danger';
                        @endphp
                        <div class="total-row total-highlight">
                            <label>Customer Total Outstanding:</label>
                            <span class="total-value {{ $ledgerOutstandingClass }}">
                                {{ $ledgerOutstandingSign }}{{ number_format($ledgerOutstanding, 2) }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Action Buttons -->
    <div class="text-center mt-4 mb-4">
        <div class="btn-group">
            <a href="{{ route('invoices.index') }}" class="btn modern-btn modern-btn-secondary btn-lg">
                <i class="fas fa-arrow-left"></i> Back to List
            </a>
            <a href="{{ route('invoices.edit', $invoice) }}" class="btn modern-btn modern-btn-warning btn-lg">
                <i class="fas fa-edit"></i> Edit Invoice
            </a>
            <a href="{{ route('invoices.print', $invoice) }}" class="btn modern-btn modern-btn-primary btn-lg" target="_blank">
                <i class="fas fa-print"></i> Print Invoice
            </a>
            @if($invoice->payment_status != 'paid')
            <a href="{{ route('transactions.create', ['customer_id' => $invoice->customer_id, 'invoice_id' => $invoice->id]) }}" class="btn modern-btn modern-btn-success btn-lg">
                <i class="fas fa-credit-card"></i> Add Payment
            </a>
            @endif
        </div>
    </div>

    <!-- Delivery Modal -->
    @if($invoice->delivery_status != 'delivered')
    <div class="modal fade" id="deliveryModal" tabindex="-1" role="dialog" aria-labelledby="deliveryModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="deliveryModalLabel">
                        <i class="fas fa-truck"></i> Mark Invoice as Delivered
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="deliveryForm">
                    @csrf
                    <input type="hidden" name="invoice_id" value="{{ $invoice->id }}">
                    <div class="modal-body">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            This will create a challan for all remaining items and adjust stock accordingly.
                            Delivery info is optional - leave blank if not applicable.
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Vehicle Number</label>
                                    <input type="text" name="vehicle_number" class="form-control" placeholder="e.g., DHA-123456">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Driver Name</label>
                                    <input type="text" name="driver_name" class="form-control" placeholder="Driver's name">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Driver Phone</label>
                                    <input type="text" name="driver_phone" class="form-control" placeholder="Driver's phone">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Receiver Name</label>
                                    <input type="text" name="receiver_name" class="form-control" value="{{ $invoice->customer->name }}" placeholder="Receiver's name">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Receiver Phone</label>
                                    <input type="text" name="receiver_phone" class="form-control" value="{{ $invoice->customer->phone }}" placeholder="Receiver's phone">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Shipping Address</label>
                                    <input type="text" name="shipping_address" class="form-control" value="{{ $invoice->customer->address }}" placeholder="Delivery address">
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Notes</label>
                            <textarea name="notes" class="form-control" rows="2" placeholder="Any delivery notes..."></textarea>
                        </div>

                        <!-- Items to be delivered -->
                        <div class="card mt-3">
                            <div class="card-header bg-light">
                                <strong><i class="fas fa-boxes"></i> Items to be Delivered</strong>
                            </div>
                            <div class="card-body p-0">
                                <table class="table table-sm mb-0">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>Product</th>
                                            <th>Invoice Qty</th>
                                            <th>Delivered</th>
                                            <th>Remaining</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php $hasRemainingItems = false; @endphp
                                        @foreach($invoice->items as $item)
                                        @php
                                            $deliveredQty = $item->challanItems->sum('quantity');
                                            $remainingQty = $item->quantity - $deliveredQty;
                                        @endphp
                                        @if($remainingQty > 0)
                                        @php $hasRemainingItems = true; @endphp
                                        <tr>
                                            <td>{{ $item->product->name }}</td>
                                            <td>{{ number_format($item->quantity, 2) }}</td>
                                            <td><span class="badge badge-info">{{ number_format($deliveredQty, 2) }}</span></td>
                                            <td><span class="badge badge-warning">{{ number_format($remainingQty, 2) }}</span></td>
                                        </tr>
                                        @endif
                                        @endforeach
                                        @if(!$hasRemainingItems)
                                            <tr>
                                                <td colspan="4" class="text-center text-muted">
                                                    All items already delivered.
                                                </td>
                                            </tr>
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">
                            <i class="fas fa-times"></i> Cancel
                        </button>
                        <button type="submit" class="btn btn-success" id="submitDeliveryBtn">
                            <i class="fas fa-check"></i> Confirm Delivery
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
@stop

@section('css')
    <style>
        .detail-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 0;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .detail-row:last-child {
            border-bottom: none;
        }
        
        .detail-row label {
            font-weight: 600;
            color: #555;
            margin-bottom: 0;
            min-width: 150px;
        }
        
        .detail-value {
            color: #333;
            text-align: right;
        }
        
        .modern-link {
            color: #007bff;
            text-decoration: none;
            font-weight: 500;
        }
        
        .modern-link:hover {
            color: #0056b3;
            text-decoration: underline;
        }
        
        .modern-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.85em;
            font-weight: 500;
        }
        
        .modern-badge-success {
            background-color: #d4edda;
            color: #155724;
        }
        
        .modern-badge-warning {
            background-color: #fff3cd;
            color: #856404;
        }
        
        .modern-badge-danger {
            background-color: #f8d7da;
            color: #721c24;
        }
        
        .modern-badge-info {
            background-color: #d1ecf1;
            color: #0c5460;
        }
        
        .modern-badge-secondary {
            background-color: #e2e3e5;
            color: #383d41;
        }
        
        .amount-highlight {
            font-weight: 600;
            color: #dc3545;
        }
        
        .total-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .total-row:last-child {
            border-bottom: none;
        }
        
        .total-row label {
            font-weight: 600;
            color: #555;
            margin-bottom: 0;
        }
        
        .total-value {
            font-weight: 600;
            color: #333;
        }
        
        .total-main {
            background-color: #f8f9fa;
            padding: 15px 10px;
            margin: 10px -10px;
            border-radius: 5px;
        }
        
        .total-main label,
        .total-main .total-value {
            font-size: 1.1em;
            color: #007bff;
        }
        
        .total-highlight {
            background-color: #fff3cd;
            padding: 15px 10px;
            margin: 10px -10px;
            border-radius: 5px;
        }
        
        .total-highlight label,
        .total-highlight .total-value {
            font-size: 1.1em;
            color: #856404;
            font-weight: 700;
        }
        
        .modern-alert {
            border: none;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .modern-table {
            border-collapse: separate;
            border-spacing: 0;
        }
        
        .modern-table th {
            background-color: #f8f9fa;
            border-bottom: 2px solid #dee2e6;
            font-weight: 600;
            color: #495057;
            padding: 12px;
        }
        
        .modern-table td {
            padding: 10px 12px;
            vertical-align: middle;
            border-bottom: 1px solid #dee2e6;
        }
        
        .modern-table tbody tr:hover {
            background-color: #f8f9fa;
        }
        
        .btn-group-sm > .btn, .btn-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.775rem;
            border-radius: 0.2rem;
        }
        
        .modern-card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        
        .modern-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 10px 10px 0 0;
            padding: 15px 20px;
        }
        
        .modern-btn {
            border-radius: 25px;
            padding: 8px 20px;
            font-weight: 500;
            border: none;
            transition: all 0.3s ease;
        }
        
        .modern-btn-secondary {
            background-color: #6c757d;
            color: white;
        }
        
        .modern-btn-warning {
            background-color: #ffc107;
            color: #212529;
        }
        
        .modern-btn-primary {
            background-color: #007bff;
            color: white;
        }
        
        .modern-btn-success {
            background-color: #28a745;
            color: white;
        }
        
        .modern-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }
    </style>
@stop

@section('js')
    <script>
        $(document).ready(function() {
            $('#invoice-referrer-compensated').on('change', function() {
                const isChecked = $(this).is(':checked');

                $.ajax({
                    url: '{{ route("invoices.referrer-compensation", $invoice->id) }}',
                    method: 'PATCH',
                    data: {
                        referrer_compensated: isChecked ? 1 : 0,
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    error: function() {
                        $('#invoice-referrer-compensated').prop('checked', !isChecked);
                        alert('Failed to update compensation status.');
                    }
                });
            });

            // Handle delivery form submission
            $('#deliveryForm').on('submit', function(e) {
                e.preventDefault();

                const btn = $('#submitDeliveryBtn');
                const originalText = btn.html();
                btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Processing...');

                $.ajax({
                    url: '{{ route("challans.quick-store") }}',
                    method: 'POST',
                    data: $(this).serialize(),
                    success: function(response) {
                        if (response.success) {
                            $('#deliveryModal').modal('hide');

                            if (typeof Swal !== 'undefined') {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Delivery Completed!',
                                    text: response.message,
                                    showConfirmButton: true
                                }).then(() => {
                                    location.reload();
                                });
                            } else {
                                alert(response.message);
                                location.reload();
                            }
                        } else {
                            if (typeof Swal !== 'undefined') {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: response.message || 'An error occurred'
                                });
                            } else {
                                alert(response.message || 'An error occurred');
                            }
                        }
                    },
                    error: function(xhr) {
                        let msg = 'An error occurred';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            msg = xhr.responseJSON.message;
                        }

                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: msg
                            });
                        } else {
                            alert(msg);
                        }
                    },
                    complete: function() {
                        btn.prop('disabled', false).html(originalText);
                    }
                });
            });
        });
    </script>
@stop
