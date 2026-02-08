@extends('customer.layout')

@section('title', 'Account Ledger')

@section('content')
<div class="row mb-4">
    <div class="col-md-6">
        <h2>Account Ledger</h2>
        <p class="text-muted">Transaction history for {{ $customer->name }}</p>
    </div>
    <div class="col-md-6 text-md-end">
        <div class="card">
            <div class="card-body py-2">
                <strong>Current Balance: </strong>
                <span class="amount {{ $customer->outstanding_balance > 0 ? 'negative' : ($customer->outstanding_balance < 0 ? 'positive' : 'zero') }}">
                    {{ $customer->formatted_balance }}
                </span>
            </div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-header">
        <button class="btn btn-outline-primary btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#ledgerFilters">
            <i class="fas fa-filter"></i> Filter Transactions
        </button>
    </div>
    <div class="collapse" id="ledgerFilters">
        <div class="card-body">
            <form method="GET" action="{{ route('customer.ledger') }}">
                <div class="row">
                    <div class="col-md-2">
                        <label class="form-label">Type</label>
                        <select name="type" class="form-select">
                            <option value="">All Types</option>
                            <option value="debit" {{ request('type') === 'debit' ? 'selected' : '' }}>Payment</option>
                            <option value="credit" {{ request('type') === 'credit' ? 'selected' : '' }}>Charge</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Method</label>
                        <select name="method" class="form-select">
                            <option value="">All Methods</option>
                            <option value="cash" {{ request('method') === 'cash' ? 'selected' : '' }}>Cash</option>
                            <option value="bank" {{ request('method') === 'bank' ? 'selected' : '' }}>Bank</option>
                            <option value="mobile_bank" {{ request('method') === 'mobile_bank' ? 'selected' : '' }}>Mobile Bank</option>
                            <option value="cheque" {{ request('method') === 'cheque' ? 'selected' : '' }}>Cheque</option>
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
                    <div class="col-md-2">
                        <label class="form-label">&nbsp;</label>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">Apply</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Transactions Table -->
<div class="card">
    <div class="card-body">
        @if($transactions->count() > 0)
            <div class="table-responsive">
                <table class="table" id="ledgerTable">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Purpose</th>
                            <th>Method</th>
                            <th>Reference</th>
                            <th>Debit</th>
                            <th>Credit</th>
                            <th>Balance</th>
                        </tr>
                    </thead>
                    <tbody>
@php
    $runningBalance = $customer->opening_balance;
    $chronologicalTransactions = $transactions->items(); // Now valid with paginator
    // Sorting is handled by latest() in the controller, but keep for balance calculation if needed
    usort($chronologicalTransactions, function($a, $b) {
        return $a->created_at <=> $b->created_at;
    });
    
    $balances = [];
    foreach($chronologicalTransactions as $transaction) {
        if ($transaction->type == 'credit') {
            $runningBalance += $transaction->amount;
        } else {
            $runningBalance -= ($transaction->amount + ($transaction->discount_amount ?? 0));
        }
        $balances[$transaction->id] = $runningBalance;
    }
@endphp
                        
                        @foreach($transactions as $transaction)
                        <tr>
                            <td>
                                <div>
                                    <div class="fw-medium">{{ $transaction->created_at->format('M d, Y') }}</div>
                                    <small class="text-muted">{{ $transaction->created_at->format('H:i') }}</small>
                                </div>
                            </td>
                            <td>
                                <div>
                                    {{ $transaction->purpose ?? 'Transaction' }}
                                    @if($transaction->discount_amount > 0)
                                        <br><small class="text-success"><i class="fas fa-tag"></i> Discount Applied</small>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-secondary">{{ ucfirst(str_replace('_', ' ', $transaction->method)) }}</span>
                            </td>
                            <td>
                                @if($transaction->reference)
                                    <small class="font-monospace">{{ $transaction->reference }}</small>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($transaction->type == 'debit')
                                    <span class="amount positive">৳{{ number_format($transaction->amount + ($transaction->discount_amount ?? 0), 2) }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($transaction->type == 'credit')
                                    <span class="amount negative">৳{{ number_format($transaction->amount, 2) }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                <span class="amount {{ ($balances[$transaction->id] ?? 0) > 0 ? 'negative' : (($balances[$transaction->id] ?? 0) < 0 ? 'positive' : 'zero') }}">
                                    ৳{{ number_format($balances[$transaction->id] ?? 0, 2) }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                        
                        <!-- Opening Balance Row -->
                        <tr class="table-info">
                            <td><strong>Opening Balance</strong></td>
                            <td colspan="3">Account opening balance</td>
                            <td class="text-muted">-</td>
                            <td class="text-muted">-</td>
                            <td>
                                <span class="amount {{ $customer->opening_balance > 0 ? 'negative' : ($customer->opening_balance < 0 ? 'positive' : 'zero') }}">
                                    ৳{{ number_format($customer->opening_balance, 2) }}
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div class="d-flex justify-content-center">
                {{ $transactions->withQueryString()->links() }}
            </div>
        @else
            <div class="text-center py-5">
                <i class="fas fa-book fa-4x text-muted mb-3"></i>
                <h4>No transactions found</h4>
                <p class="text-muted">No transactions match your current filters.</p>
            </div>
        @endif
    </div>
</div>
@endsection

@section('additional_js')
<script>
$(document).ready(function() {
    $('#ledgerTable').DataTable({
        "pageLength": 25,
        "order": [[0, "desc"]],
        "columnDefs": [
            { "orderable": false, "targets": [3] }
        ]
    });
});
</script>
@endsection