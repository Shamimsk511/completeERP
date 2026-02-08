@extends('layouts.modern-admin')

@section('title', 'Cash Register #' . $cashRegister->id)
@section('page_title', 'Cash Register #' . $cashRegister->id)

@section('header_actions')
    <a href="{{ route('cash-registers.index') }}" class="btn modern-btn modern-btn-secondary">
        <i class="fas fa-arrow-left"></i> Back
    </a>
@stop

@section('page_content')
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    @endif

    <div class="row">
        <!-- Left Column - Register Info & Actions -->
        <div class="col-lg-4">
            <!-- Register Status Card -->
            <div class="card mb-4">
                <div class="card-header {{ $cashRegister->status === 'open' ? 'bg-success' : 'bg-secondary' }} text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-cash-register"></i>
                        {{ ucfirst($cashRegister->status) }}
                    </h5>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        <h2 class="text-primary mb-0">৳{{ number_format($cashRegister->expected_closing_balance, 2) }}</h2>
                        <small class="text-muted">Current Balance</small>
                    </div>

                    <table class="table table-sm mb-0">
                        <tr>
                            <td><i class="fas fa-user text-info"></i> Cashier</td>
                            <td class="text-right"><strong>{{ $cashRegister->user->name }}</strong></td>
                        </tr>
                        <tr>
                            <td><i class="fas fa-clock text-success"></i> Opened</td>
                            <td class="text-right">{{ $cashRegister->opened_at->format('d M, h:i A') }}</td>
                        </tr>
                        <tr>
                            <td><i class="fas fa-money-bill text-warning"></i> Opening</td>
                            <td class="text-right">৳{{ number_format($cashRegister->opening_balance, 2) }}</td>
                        </tr>
                        @if($cashRegister->status === 'closed')
                        <tr>
                            <td><i class="fas fa-lock text-secondary"></i> Closed</td>
                            <td class="text-right">{{ $cashRegister->closed_at->format('d M, h:i A') }}</td>
                        </tr>
                        <tr>
                            <td><i class="fas fa-balance-scale text-danger"></i> Variance</td>
                            <td class="text-right {{ $cashRegister->variance > 0 ? 'text-success' : ($cashRegister->variance < 0 ? 'text-danger' : '') }}">
                                ৳{{ number_format($cashRegister->variance, 2) }}
                            </td>
                        </tr>
                        @endif
                    </table>
                </div>
            </div>

            <!-- Quick Add Transaction (Only if Open) -->
            @if($cashRegister->status === 'open')
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-plus-circle"></i> Add Transaction</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('cash-registers.add-transaction', $cashRegister->id) }}" method="POST">
                        @csrf
                        <div class="form-group">
                            <label>Type</label>
                            <select name="transaction_type" class="form-control" required>
                                <option value="deposit">Deposit (Cash In)</option>
                                <option value="withdrawal">Withdrawal (Cash Out)</option>
                                <option value="sale">Sale</option>
                                <option value="return">Return</option>
                                <option value="expense">Expense</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Amount</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">৳</span>
                                </div>
                                <input type="number" name="amount" class="form-control" min="0.01" step="0.01" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Notes (Optional)</label>
                            <input type="text" name="notes" class="form-control" placeholder="Reference or description">
                        </div>
                        <button type="submit" class="btn btn-primary btn-block">
                            <i class="fas fa-plus"></i> Add Transaction
                        </button>
                    </form>
                </div>
            </div>

            <!-- Close Register -->
            <div class="card">
                <div class="card-header bg-warning">
                    <h5 class="mb-0"><i class="fas fa-lock"></i> Close Register</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('cash-registers.close', $cashRegister->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to close this register?')">
                        @csrf
                        <div class="form-group">
                            <label>Actual Cash Count</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">৳</span>
                                </div>
                                <input type="number" name="actual_closing_balance" class="form-control" value="{{ $cashRegister->expected_closing_balance }}" min="0" step="0.01" required>
                            </div>
                            <small class="text-muted">Expected: ৳{{ number_format($cashRegister->expected_closing_balance, 2) }}</small>
                        </div>
                        <div class="form-group">
                            <label>Closing Notes</label>
                            <textarea name="closing_notes" class="form-control" rows="2" placeholder="Any notes..."></textarea>
                        </div>
                        <button type="submit" class="btn btn-warning btn-block">
                            <i class="fas fa-lock"></i> Close Register
                        </button>
                    </form>
                </div>
            </div>
            @endif
        </div>

        <!-- Right Column - Transaction History -->
        <div class="col-lg-8">
            <!-- Summary Cards -->
            <div class="row mb-4">
                <div class="col-6 col-md-3">
                    <div class="small-box bg-success">
                        <div class="inner">
                            <h4>৳{{ number_format($totals['deposits'] ?? 0, 2) }}</h4>
                            <p>Deposits</p>
                        </div>
                        <div class="icon"><i class="fas fa-arrow-down"></i></div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="small-box bg-danger">
                        <div class="inner">
                            <h4>৳{{ number_format($totals['withdrawals'] ?? 0, 2) }}</h4>
                            <p>Withdrawals</p>
                        </div>
                        <div class="icon"><i class="fas fa-arrow-up"></i></div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="small-box bg-info">
                        <div class="inner">
                            <h4>৳{{ number_format($totals['sales'] ?? 0, 2) }}</h4>
                            <p>Sales</p>
                        </div>
                        <div class="icon"><i class="fas fa-shopping-cart"></i></div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="small-box bg-warning">
                        <div class="inner">
                            <h4>৳{{ number_format($totals['returns'] ?? 0, 2) }}</h4>
                            <p>Returns</p>
                        </div>
                        <div class="icon"><i class="fas fa-undo"></i></div>
                    </div>
                </div>
            </div>

            <!-- Transaction List -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-history"></i> Transaction History</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped mb-0">
                            <thead>
                                <tr>
                                    <th>Time</th>
                                    <th>Type</th>
                                    <th>Amount</th>
                                    <th>Notes</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($cashRegister->transactions as $txn)
                                <tr>
                                    <td>{{ $txn->created_at->format('h:i A') }}</td>
                                    <td>
                                        @php
                                            $badges = [
                                                'opening_balance' => 'badge-primary',
                                                'closing_balance' => 'badge-secondary',
                                                'deposit' => 'badge-success',
                                                'withdrawal' => 'badge-danger',
                                                'sale' => 'badge-info',
                                                'return' => 'badge-warning',
                                                'expense' => 'badge-dark',
                                            ];
                                        @endphp
                                        <span class="badge {{ $badges[$txn->transaction_type] ?? 'badge-secondary' }}">
                                            {{ ucfirst(str_replace('_', ' ', $txn->transaction_type)) }}
                                        </span>
                                    </td>
                                    <td class="{{ in_array($txn->transaction_type, ['deposit', 'sale', 'opening_balance']) ? 'text-success' : 'text-danger' }}">
                                        {{ in_array($txn->transaction_type, ['deposit', 'sale', 'opening_balance']) ? '+' : '-' }}৳{{ number_format($txn->amount, 2) }}
                                    </td>
                                    <td>{{ $txn->notes ?? '-' }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-4">
                                        <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                                        No transactions yet
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('additional_css')
<style>
.small-box {
    border-radius: 8px;
    padding: 15px;
    position: relative;
    color: white;
}
.small-box .inner h4 {
    font-size: 1.5rem;
    font-weight: bold;
    margin: 0;
}
.small-box .inner p {
    margin: 0;
    font-size: 0.9rem;
}
.small-box .icon {
    position: absolute;
    right: 15px;
    top: 50%;
    transform: translateY(-50%);
    font-size: 2rem;
    opacity: 0.3;
}
</style>
@stop
