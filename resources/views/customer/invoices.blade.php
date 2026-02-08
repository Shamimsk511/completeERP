@extends('customer.layout')

@section('title', 'My Invoices')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>My Invoices</h2>
    <div>
        <button class="btn btn-outline-primary" type="button" data-bs-toggle="collapse" data-bs-target="#filterCollapse">
            <i class="fas fa-filter"></i> Filters
        </button>
    </div>
</div>

<!-- Filters -->
<div class="collapse mb-4" id="filterCollapse">
    <div class="card">
        <div class="card-body">
            <form method="GET" action="{{ route('customer.invoices') }}">
                <div class="row">
                    <div class="col-md-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="">All Status</option>
                            <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>Paid</option>
                            <option value="partial" {{ request('status') === 'partial' ? 'selected' : '' }}>Partial</option>
                            <option value="unpaid" {{ request('status') === 'unpaid' ? 'selected' : '' }}>Unpaid</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">From Date</label>
                        <input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">To Date</label>
                        <input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">&nbsp;</label>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">Apply Filters</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Invoices Table -->
<div class="card">
    <div class="card-body">
        @if($invoices->count() > 0)
            <div class="table-responsive">
                <table class="table" id="invoicesTable">
                    <thead>
                        <tr>
                            <th>Invoice #</th>
                            <th>Date</th>
                            <th>Total</th>
                            <th>Paid</th>
                            <th>Due</th>
                            <th>Status</th>
                            <th>Items</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($invoices as $invoice)
                        <tr>
                            <td>
                                <a href="{{ route('customer.invoices.show', $invoice->id) }}" class="text-decoration-none fw-bold">
                                    {{ $invoice->invoice_number ?? 'INV-' . $invoice->id }}
                                </a>
                            </td>
                            <td>{{ $invoice->invoice_date ? \Carbon\Carbon::parse($invoice->invoice_date)->format('M d, Y') : $invoice->created_at->format('M d, Y') }}</td>
                            <td class="amount">৳{{ number_format($invoice->total ?? 0, 2) }}</td>
                            <td class="amount positive">৳{{ number_format($invoice->paid_amount ?? 0, 2) }}</td>
                            <td class="amount {{ ($invoice->due_amount ?? 0) > 0 ? 'negative' : 'positive' }}">৳{{ number_format($invoice->due_amount ?? 0, 2) }}</td>
                            <td>
                                <span class="badge badge-{{ $invoice->payment_status === 'paid' ? 'success' : ($invoice->payment_status === 'partial' ? 'warning' : 'danger') }}">
                                    {{ ucfirst($invoice->payment_status ?? 'pending') }}
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-info">{{ $invoice->items ? $invoice->items->count() : 0 }} items</span>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('customer.invoices.show', $invoice->id) }}" class="btn btn-outline-primary" title="View Invoice">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div class="d-flex justify-content-center">
                {{ $invoices->withQueryString()->links() }}
            </div>
        @else
            <div class="text-center py-5">
                <i class="fas fa-file-invoice fa-4x text-muted mb-3"></i>
                <h4>No invoices found</h4>
                <p class="text-muted">No invoices match your current filters.</p>
            </div>
        @endif
    </div>
</div>
@endsection

@section('additional_js')
<script>
$(document).ready(function() {
    $('#invoicesTable').DataTable({
        "pageLength": 25,
        "order": [[1, "desc"]],
        "columnDefs": [
            { "orderable": false, "targets": [7] }
        ],
        "paging": false,
        "info": false,
        "searching": false
    });
});
</script>
@endsection