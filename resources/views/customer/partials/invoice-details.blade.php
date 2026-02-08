<div class="invoice-details">
    <div class="row mb-3">
        <div class="col-md-6">
            <h6>Invoice Information</h6>
            <table class="table table-sm table-borderless">
                <tr>
                    <td><strong>Invoice Number:</strong></td>
                    <td>{{ $invoice->invoice_number ?? 'INV-' . $invoice->id }}</td>
                </tr>
                <tr>
                    <td><strong>Date:</strong></td>
                    <td>{{ $invoice->invoice_date ? \Carbon\Carbon::parse($invoice->invoice_date)->format('F d, Y') : $invoice->created_at->format('F d, Y') }}</td>
                </tr>
                <tr>
                    <td><strong>Status:</strong></td>
                    <td>
                        <span class="badge badge-{{ $invoice->payment_status === 'paid' ? 'success' : ($invoice->payment_status === 'partial' ? 'warning' : 'danger') }}">
                            {{ ucfirst($invoice->payment_status ?? 'pending') }}
                        </span>
                    </td>
                </tr>
            </table>
        </div>
        <div class="col-md-6">
            <h6>Amount Details</h6>
            <table class="table table-sm table-borderless">
                <tr>
                    <td><strong>Subtotal:</strong></td>
                    <td class="amount">৳{{ number_format($invoice->subtotal ?? 0, 2) }}</td>
                </tr>
                <tr>
                    <td><strong>Tax:</strong></td>
                    <td class="amount">৳{{ number_format($invoice->tax_amount ?? 0, 2) }}</td>
                </tr>
                <tr>
                    <td><strong>Total:</strong></td>
                    <td class="amount fw-bold">৳{{ number_format($invoice->total ?? 0, 2) }}</td>
                </tr>
                <tr>
                    <td><strong>Paid:</strong></td>
                    <td class="amount positive">৳{{ number_format($invoice->paid_amount ?? 0, 2) }}</td>
                </tr>
                <tr>
                    <td><strong>Due:</strong></td>
                    <td class="amount {{ ($invoice->due_amount ?? 0) > 0 ? 'negative' : 'positive' }}">৳{{ number_format($invoice->due_amount ?? 0, 2) }}</td>
                </tr>
            </table>
        </div>
    </div>
    
    @if($invoice->items && $invoice->items->count() > 0)
    <h6>Invoice Items</h6>
    <div class="table-responsive">
        <table class="table table-sm">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Category</th>
                    <th class="text-center">Quantity</th>
                    <th class="text-end">Unit Price</th>
                    <th class="text-end">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice->items as $item)
                <tr>
                    <td>{{ $item->product->name ?? 'Product' }}</td>
                    <td>
                        @if($item->product && $item->product->category)
                            <span class="badge bg-info">{{ $item->product->category->name }}</span>
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </td>
                    <td class="text-center">{{ number_format($item->quantity, 2) }}</td>
                    <td class="text-end amount">৳{{ number_format($item->unit_price ?? 0, 2) }}</td>
                    <td class="text-end amount fw-bold">৳{{ number_format($item->total ?? 0, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="table-secondary">
                    <th colspan="4">Total</th>
                    <th class="text-end">৳{{ number_format($invoice->items->sum('total'), 2) }}</th>
                </tr>
            </tfoot>
        </table>
    </div>
    @endif
    
    @if($invoice->challans && $invoice->challans->count() > 0)
    <h6 class="mt-3">Delivery Information</h6>
    <div class="table-responsive">
        <table class="table table-sm">
            <thead>
                <tr>
                    <th>Challan #</th>
                    <th>Date</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice->challans as $challan)
                <tr>
                    <td>{{ $challan->challan_number ?? 'CHN-' . $challan->id }}</td>
                    <td>{{ $challan->challan_date ? \Carbon\Carbon::parse($challan->challan_date)->format('M d, Y') : $challan->created_at->format('M d, Y') }}</td>
                    <td>
                        <span class="badge badge-{{ $challan->status === 'delivered' ? 'success' : ($challan->status === 'cancelled' ? 'danger' : 'warning') }}">
                            {{ ucfirst($challan->status ?? 'pending') }}
                        </span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
    
    @if($invoice->productReturns && $invoice->productReturns->count() > 0)
    <h6 class="mt-3">Returns</h6>
    <div class="table-responsive">
        <table class="table table-sm">
            <thead>
                <tr>
                    <th>Return #</th>
                    <th>Date</th>
                    <th>Amount</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice->productReturns as $return)
                <tr>
                    <td>{{ $return->return_number ?? 'RET-' . $return->id }}</td>
                    <td>{{ $return->return_date ? \Carbon\Carbon::parse($return->return_date)->format('M d, Y') : $return->created_at->format('M d, Y') }}</td>
                    <td class="amount negative">৳{{ number_format($return->total ?? 0, 2) }}</td>
                    <td>
                        <span class="badge badge-{{ $return->status === 'completed' ? 'success' : 'warning' }}">
                            {{ ucfirst($return->status ?? 'pending') }}
                        </span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
</div>