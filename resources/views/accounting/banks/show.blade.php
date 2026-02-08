@extends('layouts.modern-admin')

@section('title', 'Bank - ' . $bank->name)
@section('page_title', $bank->name)

@section('header_actions')
    <a href="{{ route('accounting.bank-transactions.create', ['bank_account_id' => $bank->id, 'type' => 'deposit']) }}" class="btn modern-btn modern-btn-primary">
        <i class="fas fa-arrow-down"></i> Deposit
    </a>
    <a href="{{ route('accounting.bank-transactions.create', ['bank_account_id' => $bank->id, 'type' => 'withdraw']) }}" class="btn modern-btn modern-btn-danger">
        <i class="fas fa-arrow-up"></i> Withdraw
    </a>
    <a href="{{ route('accounting.bank-transactions.create', ['bank_account_id' => $bank->id, 'type' => 'adjustment']) }}" class="btn modern-btn modern-btn-outline">
        <i class="fas fa-sliders-h"></i> Adjust
    </a>
    <a href="{{ route('accounting.accounts.ledger', $bank) }}" class="btn modern-btn modern-btn-outline">
        <i class="fas fa-book"></i> Ledger
    </a>
    @if(!$bank->is_system)
        <a href="{{ route('accounting.banks.edit', $bank) }}" class="btn modern-btn modern-btn-warning">
            <i class="fas fa-edit"></i> Edit
        </a>
    @endif
@stop

@section('page_content')
    <div class="card modern-card">
        <div class="card-header modern-header">
            <h3 class="card-title"><i class="fas fa-university"></i> Bank Details</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <table class="table table-borderless">
                        <tr>
                            <td class="text-muted" width="40%">Account Code:</td>
                            <td><strong>{{ $bank->code }}</strong></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Account Name:</td>
                            <td><strong>{{ $bank->name }}</strong></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Bank Name:</td>
                            <td>{{ $bank->bank_name ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Account Number:</td>
                            <td>{{ $bank->bank_account_number ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">IFSC/Routing:</td>
                            <td>{{ $bank->ifsc_code ?? '-' }}</td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-borderless">
                        <tr>
                            <td class="text-muted" width="40%">Opening Balance:</td>
                            <td>
                                ৳{{ number_format($bank->opening_balance, 2) }}
                                ({{ ucfirst($bank->opening_balance_type ?? 'debit') }})
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted">Current Balance:</td>
                            <td>
                                <strong class="text-primary" style="font-size: 1.25rem;">
                                    ৳{{ number_format($bank->current_balance, 2) }}
                                    ({{ ucfirst($bank->current_balance_type) }})
                                </strong>
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted">Status:</td>
                            <td>
                                @if($bank->is_active)
                                    <span class="badge badge-success">Active</span>
                                @else
                                    <span class="badge badge-secondary">Inactive</span>
                                @endif
                                @if($bank->is_system)
                                    <span class="badge badge-warning">System Account</span>
                                @endif
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            @if($bank->notes)
                <div class="row mt-3">
                    <div class="col-12">
                        <div class="form-group">
                            <label class="form-label text-muted">Notes</label>
                            <p class="bg-light p-3 rounded">{{ $bank->notes }}</p>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <div class="card modern-card mt-4">
        <div class="card-header modern-header">
            <div class="d-flex justify-content-between align-items-center">
                <h3 class="card-title"><i class="fas fa-exchange-alt"></i> Recent Transactions</h3>
                <a href="{{ route('accounting.bank-transactions.index', ['bank_account_id' => $bank->id]) }}" class="btn modern-btn modern-btn-outline btn-sm">
                    View All
                </a>
            </div>
        </div>
        <div class="card-body table-responsive">
            <table class="table modern-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Type</th>
                        <th>Counter Account</th>
                        <th>Amount</th>
                        <th>Reference</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transactions as $transaction)
                        <tr>
                            <td>{{ $transaction->transaction_date->format('d M, Y') }}</td>
                            <td>
                                @if($transaction->transaction_type === 'deposit')
                                    <span class="badge badge-success">Deposit</span>
                                @elseif($transaction->transaction_type === 'withdraw')
                                    <span class="badge badge-danger">Withdraw</span>
                                @else
                                    <span class="badge badge-info">Adjustment</span>
                                @endif
                            </td>
                            <td>{{ $transaction->counterAccount?->name ?? '-' }}</td>
                            <td>৳{{ number_format($transaction->amount, 2) }}</td>
                            <td>{{ $transaction->reference ?? '-' }}</td>
                            <td class="text-right">
                                <a href="{{ route('accounting.bank-transactions.edit', $transaction) }}" class="btn btn-sm btn-warning">
                                    <i class="fas fa-edit"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted">No transactions found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            {{ $transactions->links() }}
        </div>
    </div>

    <div class="text-center mt-4">
        <a href="{{ route('accounting.banks.index') }}" class="btn btn-outline-secondary btn-lg">
            <i class="fas fa-arrow-left"></i> Back to Banks
        </a>
    </div>
@stop
