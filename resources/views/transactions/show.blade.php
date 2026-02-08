@extends('layouts.modern-admin')

@section('title', 'Transaction Details')

@section('page_title', 'Transaction Details')

@section('header_actions')
    <a href="{{ route('transactions.index') }}" class="btn modern-btn modern-btn-outline">
        <i class="fas fa-arrow-left"></i> Back to Transactions
    </a>
    <a href="{{ route('transactions.edit', $transaction) }}" class="btn modern-btn modern-btn-warning">
        <i class="fas fa-edit"></i> Edit
    </a>
    <a href="{{ route('transactions.print', $transaction) }}" class="btn modern-btn modern-btn-primary" target="_blank">
        <i class="fas fa-print"></i> Print
    </a>
@stop

@section('page_content')
    <div class="row">
        <div class="col-lg-7 mb-4 mb-lg-0">
            <div class="card modern-card">
                <div class="card-header modern-header">
                    <h3 class="card-title">
                        <i class="fas fa-receipt"></i> Transaction #{{ $transaction->id }}
                        <span class="badge ml-2 {{ $transaction->type === 'debit' ? 'badge-success' : 'badge-danger' }}">
                            {{ ucfirst($transaction->type) }}
                        </span>
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Date</label>
                            <div class="form-control modern-input" style="background: #f8fafc;">{{ $transaction->created_at->format('Y-m-d H:i') }}</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Purpose</label>
                            <div class="form-control modern-input" style="background: #f8fafc;">{{ $transaction->purpose }}</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Payment Method</label>
                            <div class="form-control modern-input" style="background: #f8fafc;">{{ ucfirst(str_replace('_', ' ', $transaction->method)) }}</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Reference</label>
                            <div class="form-control modern-input" style="background: #f8fafc;">{{ $transaction->reference ?? 'N/A' }}</div>
                        </div>
                        @if($transaction->account)
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Account</label>
                                <div class="form-control modern-input" style="background: #f8fafc;">{{ $transaction->account->name }}</div>
                            </div>
                        @endif
                        @if($transaction->invoice)
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Invoice</label>
                                <div class="form-control modern-input" style="background: #f8fafc;">
                                    @if(Route::has('invoices.show'))
                                        <a href="{{ route('invoices.show', $transaction->invoice_id) }}" target="_blank">#{{ $transaction->invoice->invoice_number }}</a>
                                    @else
                                        #{{ $transaction->invoice->invoice_number }}
                                    @endif
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card modern-card">
                <div class="card-header modern-header">
                    <h3 class="card-title"><i class="fas fa-user"></i> Customer</h3>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <strong>{{ $transaction->customer->name }}</strong>
                        @if($transaction->customer->phone)
                            <div class="text-muted">{{ $transaction->customer->phone }}</div>
                        @endif
                        @if($transaction->customer->email)
                            <div class="text-muted">{{ $transaction->customer->email }}</div>
                        @endif
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Current Balance</label>
                        <div class="form-control modern-input" style="background: #f8fafc;">৳{{ number_format($transaction->customer->outstanding_balance, 2) }}</div>
                    </div>
                    <a href="{{ route('customers.ledger', $transaction->customer_id) }}" class="btn modern-btn modern-btn-outline btn-sm">
                        <i class="fas fa-book"></i> View Ledger
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="card modern-card mt-4">
        <div class="card-header modern-header">
            <h3 class="card-title"><i class="fas fa-calculator"></i> Amount Summary</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Amount</label>
                    <div class="form-control modern-input" style="background: #f8fafc;">৳{{ number_format($transaction->amount, 2) }}</div>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Discount</label>
                    <div class="form-control modern-input" style="background: #f8fafc;">৳{{ number_format($transaction->discount_amount ?? 0, 2) }}</div>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Total Effective</label>
                    <div class="form-control modern-input" style="background: #f8fafc;">৳{{ number_format($transaction->total_amount, 2) }}</div>
                </div>
            </div>

            @if($transaction->discount_reason)
                <div class="mb-3">
                    <label class="form-label">Discount Reason</label>
                    <div class="form-control modern-input" style="background: #f8fafc;">{{ $transaction->discount_reason }}</div>
                </div>
            @endif

            @if($transaction->note)
                <div>
                    <label class="form-label">Notes</label>
                    <div class="form-control modern-input" style="background: #f8fafc;">{{ $transaction->note }}</div>
                </div>
            @endif
        </div>
    </div>
@stop
