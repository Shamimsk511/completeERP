@extends('customer.layout')

@section('title', 'Dashboard')

@section('content')
<div class="card shadow-sm border-0">
    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Invoice #{{ $invoice->invoice_number ?? 'INV-' . $invoice->id }}</h5>
        <span class="badge bg-light text-dark">
            @if($invoice->invoice_date)
                {{ \Carbon\Carbon::parse($invoice->invoice_date)->format('M d, Y') }}
            @else
                {{ $invoice->created_at->format('M d, Y') }}
            @endif
        </span>
    </div>
    <div class="card-body">
        <!-- Back Button -->
        <div class="mb-3">
            <a href="{{ route('customer.invoices') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Back to Invoices
            </a>
        </div>

        <!-- Invoice Summary -->
        <div class="row mb-4">
            <div class="col-12 col-md-6 mb-3 mb-md-0">
                <h6 class="fw-bold">Customer Details</h6>
                <p class="mb-1">{{ $invoice->customer->name }}</p>
                <p class="mb-1 text-muted">{{ $invoice->customer->phone ?? 'No phone' }}</p>
                <p class="mb-1 text-muted">{{ $invoice->customer->address ?? 'No address' }}</p>
            </div>
            <div class="col-12 col-md-6 text-md-end">
                <h6 class="fw-bold">Invoice Summary</h6>
                <p class="mb-1"><strong>Subtotal:</strong> ৳{{ number_format($invoice->subtotal ?? 0, 2) }}</p>
                <p class="mb-1"><strong>Discount:</strong> ৳{{ number_format($invoice->discount ?? 0, 2) }}</p>
                <p class="mb-1"><strong>Total:</strong> ৳{{ number_format($invoice->total ?? 0, 2) }}</p>
                <p class="mb-1">
                    <strong>Paid:</strong>
                    <span class="amount positive">৳{{ number_format($invoice->paid_amount ?? 0, 2) }}</span>
                </p>
                <p class="mb-1">
                    <strong>Due:</strong>
                    <span class="amount {{ ($invoice->due_amount ?? 0) > 0 ? 'negative' : 'positive' }}">৳{{ number_format($invoice->due_amount ?? 0, 2) }}</span>
                </p>
                <p class="mb-1">
                    <strong>Payment Status:</strong>
                    <span class="badge bg-{{ $invoice->payment_status === 'paid' ? 'success' : ($invoice->payment_status === 'partial' ? 'warning' : 'danger') }}">
                        {{ ucfirst($invoice->payment_status ?? 'pending') }}
                    </span>
                </p>
                <p class="mb-1">
                    <strong>Delivery Status:</strong>
                    <span class="badge bg-{{ $invoice->delivery_status === 'delivered' ? 'success' : ($invoice->delivery_status === 'partial' ? 'warning' : 'danger') }}">
                        {{ ucfirst($invoice->delivery_status ?? 'pending') }}
                    </span>
                </p>
            </div>
        </div>

        <!-- Invoice Items Table -->
        <h6 class="fw-bold mb-3">Invoice Items</h6>
        @if($invoice->items->isNotEmpty())
            <div class="table-responsive">
                <table class="table table-hover table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th>Product</th>
                            <th>Category</th>
                            <th>Company</th>
                            <th>Quantity</th>
                            @if($invoice->invoice_type === 'tiles')
                                <th>Boxes</th>
                                <th>Pieces</th>
                            @endif
                            <th>Unit Price</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($invoice->items as $item)
                            <tr>
                                <td>{{ $item->product->name }}</td>
                                <td>{{ $item->product->category->name ?? 'N/A' }}</td>
                                <td>{{ $item->product->company->name ?? 'N/A' }}</td>
                                <td>{{ number_format($item->quantity, 2) }}</td>
                                @if($invoice->invoice_type === 'tiles')
                                    <td>{{ $item->boxes ?? 'N/A' }}</td>
                                    <td>{{ $item->pieces ?? 'N/A' }}</td>
                                @endif
                                <td>৳{{ number_format($item->unit_price, 2) }}</td>
                                <td>৳{{ number_format($item->total, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="alert alert-info text-center">
                No items found for this invoice.
            </div>
        @endif

        <!-- Undelivered Items Table -->
@php
    $undeliveredItems = $invoice->items->filter(function ($item) {
        return $item->quantity > $item->getDeliveredQuantityViaChallans();
    });
    $hasUndelivered = $undeliveredItems->contains(function ($item) {
        return ($item->quantity - $item->getDeliveredQuantityViaChallans()) > 0;
    });
@endphp

{{-- Only show undelivered items if delivery status is not 'delivered' --}}
@if($invoice->delivery_status !== 'delivered' && $hasUndelivered)
    <h6 class="fw-bold mb-3 mt-4">Undelivered Items</h6>
    <div class="table-responsive">
        <table class="table table-hover table-bordered">
            <thead class="table-light">
                <tr>
                    <th>Product</th>
                    <th>Category</th>
                    <th>Company</th>
                    <th>Ordered Quantity</th>
                    <th>Delivered Quantity</th>
                    <th>Remaining Quantity</th>
                    @if($invoice->invoice_type === 'tiles')
                        <th>Boxes</th>
                        <th>Pieces</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @foreach($undeliveredItems as $item)
                    @php
                        $delivered = $item->getDeliveredQuantityViaChallans();
                        $remaining = $item->quantity - $delivered;
                    @endphp
                    @if($remaining > 0)
                        <tr>
                            <td>{{ $item->product->name }}</td>
                            <td>{{ $item->product->category->name ?? 'N/A' }}</td>
                            <td>{{ $item->product->company->name ?? 'N/A' }}</td>
                            <td>{{ number_format($item->quantity, 2) }}</td>
                            <td>{{ number_format($delivered, 2) }}</td>
                            <td>{{ number_format($remaining, 2) }}</td>
                            @if($invoice->invoice_type === 'tiles')
                                <td>{{ $item->boxes ?? 'N/A' }}</td>
                                <td>{{ $item->pieces ?? 'N/A' }}</td>
                            @endif
                        </tr>
                    @endif
                @endforeach
            </tbody>
        </table>
    </div>
@elseif($invoice->delivery_status === 'delivered')
    <div class="alert alert-success text-center mt-4">
        <i class="fas fa-check-circle"></i> All items have been fully delivered.
    </div>
@elseif(!$hasUndelivered)
    <div class="alert alert-success text-center mt-4">
        <i class="fas fa-check-circle"></i> All items have been fully delivered.
    </div>
@endif

        <!-- Notes -->
        @isset($invoice->notes)
            <h6 class="fw-bold mb-3 mt-4">Notes</h6>
            <div class="alert alert-secondary">
                {{ $invoice->notes }}
            </div>
        @endisset
    </div>
</div>
@stop
