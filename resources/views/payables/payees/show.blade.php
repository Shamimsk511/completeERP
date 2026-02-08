@extends('layouts.modern-admin')

@section('title', 'Payee Details')
@section('page_title', 'Payee Details')

@section('header_actions')
    <a class="btn modern-btn modern-btn-primary" href="{{ route('payees.ledger', $payee->id) }}">
        <i class="fas fa-book"></i> Payee Ledger
    </a>
    <a class="btn modern-btn modern-btn-secondary" href="{{ route('payees.index') }}">
        <i class="fas fa-arrow-left"></i> Back to Payees
    </a>
@stop

@section('page_content')
    <!-- Payee Overview Cards -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6">
            <div class="stats-card stats-card-primary">
                <div class="stats-icon">
                    <i class="fas fa-receipt"></i>
                </div>
                <div class="stats-content">
                    <h3 class="stats-number">{{ $ledgerStats['transaction_count'] ?? $payee->transactions()->count() }}</h3>
                    <p class="stats-label">Total Transactions</p>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6">
            <div class="stats-card stats-card-success">
                <div class="stats-icon">
                    <i class="fas fa-arrow-down"></i>
                </div>
                <div class="stats-content">
                    <h3 class="stats-number">৳{{ number_format($ledgerStats['total_debit'] ?? $payee->transactions()->where('transaction_type', 'cash_in')->sum('amount'), 2) }}</h3>
                    <p class="stats-label">Total Cash In</p>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6">
            <div class="stats-card stats-card-danger">
                <div class="stats-icon">
                    <i class="fas fa-arrow-up"></i>
                </div>
                <div class="stats-content">
                    <h3 class="stats-number">৳{{ number_format($ledgerStats['total_credit'] ?? $payee->transactions()->where('transaction_type', 'cash_out')->sum('amount'), 2) }}</h3>
                    <p class="stats-label">Total Cash Out</p>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6">
            <div class="stats-card stats-card-warning">
                <div class="stats-icon">
                    <i class="fas fa-wallet"></i>
                </div>
                <div class="stats-content">
                    <h3 class="stats-number">৳{{ number_format($ledgerCurrentBalance ?? $payee->current_balance, 2) }}</h3>
                    <p class="stats-label">Current Balance</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Payee Information Card -->
    <div class="card modern-card mb-4">
        <div class="card-header modern-header">
            <h3 class="card-title">
                <i class="fas fa-user-tie"></i> Payee Information
            </h3>
            <div class="card-tools">
                <span class="modern-badge">ID: {{ $payee->id }}</span>
            </div>
        </div>
        <div class="card-body modern-card-body">
            <div class="row">
                <div class="col-lg-6">
                    <div class="info-item">
                        <div class="info-label">
                            <i class="fas fa-user-tie text-primary"></i> Payee Name
                        </div>
                        <div class="info-value payee-name">{{ $payee->name }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">
                            <i class="fas fa-tag text-info"></i> Category
                        </div>
                        <div class="info-value">
                            <span class="type-badge type-{{ $payee->category }}">{{ $payee->display_category }}</span>
                        </div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">
                            <i class="fas fa-phone text-success"></i> Phone Number
                        </div>
                        <div class="info-value">{{ $payee->phone ?: 'N/A' }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">
                            <i class="fas fa-map-marker-alt text-danger"></i> Address
                        </div>
                        <div class="info-value">{{ $payee->address ?: 'No address provided' }}</div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="info-item">
                        <div class="info-label">
                            <i class="fas fa-wallet text-info"></i> Opening Balance
                        </div>
                        @php
                            $openingBalance = $ledgerOpeningBalance ?? $payee->opening_balance;
                        @endphp
                        <div class="info-value balance-amount {{ $openingBalance > 0 ? 'text-danger' : ($openingBalance < 0 ? 'text-info' : 'text-success') }}">
                            ৳{{ number_format($openingBalance, 2) }}
                        </div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">
                            <i class="fas fa-dollar-sign text-warning"></i> Current Balance
                        </div>
                        @php
                            $currentBalance = $ledgerCurrentBalance ?? $payee->current_balance;
                        @endphp
                        <div class="info-value balance-amount {{ $currentBalance > 0 ? 'text-danger' : ($currentBalance < 0 ? 'text-info' : 'text-success') }}">
                            ৳{{ number_format($currentBalance, 2) }}
                        </div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">
                            <i class="fas fa-chart-line text-purple"></i> Status
                        </div>
                        <div class="info-value">
                            @if($currentBalance > 5000)
                                <span class="status-badge status-danger">High Payable</span>
                            @elseif($currentBalance > 0)
                                <span class="status-badge status-warning">Outstanding</span>
                            @elseif($currentBalance < 0)
                                <span class="status-badge status-info">Credit Balance</span>
                            @else
                                <span class="status-badge status-success">Clear</span>
                            @endif
                        </div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">
                            <i class="fas fa-calendar text-secondary"></i> Created Date
                        </div>
                        <div class="info-value">{{ $payee->created_at->format('d M, Y') }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-footer modern-footer">
            <div class="action-buttons-row">
                <a href="{{ route('payees.edit', $payee->id) }}" class="btn modern-btn modern-btn-warning">
                    <i class="fas fa-edit"></i> Edit Payee
                </a>
                <a href="{{ route('payees.ledger', $payee->id) }}" class="btn modern-btn modern-btn-primary">
                    <i class="fas fa-book"></i> View Ledger
                </a>
                <a href="{{ route('payees.print-ledger', $payee->id) }}" class="btn modern-btn modern-btn-info" target="_blank">
                    <i class="fas fa-print"></i> Print Ledger
                </a>
                <a href="{{ route('payable-transactions.create') }}?payee_id={{ $payee->id }}" class="btn modern-btn modern-btn-success">
                    <i class="fas fa-plus"></i> Add Transaction
                </a>
            </div>
        </div>
    </div>

    @if($payee->isLoanCategory())
        <div class="card modern-card mb-4">
            <div class="card-header modern-header">
                <h3 class="card-title">
                    <i class="fas fa-hand-holding-usd"></i> Loan Details
                </h3>
                <div class="card-tools">
                    <span class="modern-badge">{{ $payee->display_category }}</span>
                </div>
            </div>
            <div class="card-body modern-card-body">
                <div class="row">
                    <div class="col-lg-6">
                        <div class="info-item">
                            <div class="info-label">
                                <i class="fas fa-layer-group text-info"></i> Principal Balance
                            </div>
                            <div class="info-value">৳{{ number_format($payee->principal_balance ?? 0, 2) }}</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">
                                <i class="fas fa-percentage text-warning"></i> Interest Rate
                            </div>
                            <div class="info-value">{{ number_format($payee->interest_rate ?? 0, 2) }}%</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">
                                <i class="fas fa-calendar text-secondary"></i> Start Date
                            </div>
                            <div class="info-value">
                                {{ ($payee->isDailyKisti() ? $payee->daily_kisti_start_date : $payee->loan_start_date)?->format('d M, Y') ?? 'N/A' }}
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        @if($payee->isCcLoan())
                            @php
                                $accruedInterest = (float) ($payee->interest_accrued ?? 0);
                                $pendingInterest = (float) ($interestPreview['amount'] ?? 0);
                                $totalDueInterest = $accruedInterest + $pendingInterest;
                                $roundedDueInterest = $totalDueInterest > 0 ? round($totalDueInterest / 100) * 100 : 0;
                            @endphp
                            <div class="info-item">
                                <div class="info-label">
                                    <i class="fas fa-coins text-success"></i> Accrued Interest
                                </div>
                                <div class="info-value">৳{{ number_format($payee->interest_accrued ?? 0, 2) }}</div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">
                                    <i class="fas fa-clock text-info"></i> Interest Due Today
                                </div>
                                <div class="info-value">
                                    ৳{{ number_format($roundedDueInterest, 2) }}
                                    <small class="text-muted">(Exact ৳{{ number_format($totalDueInterest, 2) }}, {{ $interestPreview['days'] ?? 0 }} days)</small>
                                </div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">
                                    <i class="fas fa-calendar-check text-primary"></i> Last Accrual
                                </div>
                                <div class="info-value">{{ $payee->interest_last_accrual_date?->format('d M, Y') ?? 'N/A' }}</div>
                            </div>
                            <div class="mt-3">
                                <form action="{{ route('payees.accrue-interest', $payee->id) }}" method="POST" class="form-inline mb-2">
                                    @csrf
                                    <input type="date" name="as_of_date" class="form-control mr-2" value="{{ now()->format('Y-m-d') }}">
                                    <button type="submit" class="btn modern-btn modern-btn-warning">
                                        <i class="fas fa-sync"></i> Accrue Interest
                                    </button>
                                </form>
                                <button type="button" class="btn modern-btn modern-btn-success" data-toggle="modal" data-target="#payInterestModal" {{ $roundedDueInterest > 0 ? '' : 'disabled' }}>
                                    <i class="fas fa-hand-holding-usd"></i> Pay Interest Now
                                    @if($roundedDueInterest > 0)
                                        (৳{{ number_format($roundedDueInterest, 2) }})
                                    @endif
                                </button>
                                <small class="text-muted ml-2">Choose payment account & rounding</small>
                            </div>
                        @endif

                        @if($payee->isSmeLoan())
                            <div class="info-item">
                                <div class="info-label">
                                    <i class="fas fa-list-ol text-primary"></i> Term (Months)
                                </div>
                                <div class="info-value">{{ $payee->loan_term_months ?? 'N/A' }}</div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">
                                    <i class="fas fa-money-check-alt text-success"></i> Installment Amount
                                </div>
                                <div class="info-value">৳{{ number_format($payee->installment_amount ?? 0, 2) }}</div>
                            </div>
                        @endif

                        @if($payee->category === 'term_loan')
                            <div class="info-item">
                                <div class="info-label">
                                    <i class="fas fa-list-ol text-primary"></i> Term (Months)
                                </div>
                                <div class="info-value">{{ $payee->loan_term_months ?? 'N/A' }}</div>
                            </div>
                        @endif

                        @if($payee->isDailyKisti())
                            <div class="info-item">
                                <div class="info-label">
                                    <i class="fas fa-calendar-day text-primary"></i> Daily Kisti
                                </div>
                                <div class="info-value">৳{{ number_format($payee->daily_kisti_amount ?? 0, 2) }}</div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">
                                    <i class="fas fa-hourglass-half text-warning"></i> Pending Days
                                </div>
                                <div class="info-value">
                                    {{ $kistiSummary['pending_days'] ?? 0 }} days (৳{{ number_format($kistiSummary['pending_amount'] ?? 0, 2) }})
                                </div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">
                                    <i class="fas fa-ban text-danger"></i> Skipped Days
                                </div>
                                <div class="info-value">{{ $kistiSummary['skipped_days'] ?? 0 }}</div>
                            </div>
                        @endif
                    </div>
                </div>

                @if($payee->isSmeLoan() && $installments)
                    <div class="mt-4">
                        <h5 class="mb-3">Installment Schedule</h5>
                        <div class="table-responsive">
                            <table class="table modern-table">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Due Date</th>
                                        <th>Principal</th>
                                        <th>Interest</th>
                                        <th>Total</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($installments as $installment)
                                        <tr>
                                            <td>{{ $installment->installment_number }}</td>
                                            <td>{{ $installment->due_date->format('d M, Y') }}</td>
                                            <td>৳{{ number_format($installment->principal_due, 2) }}</td>
                                            <td>৳{{ number_format($installment->interest_due, 2) }}</td>
                                            <td>৳{{ number_format($installment->total_due, 2) }}</td>
                                            <td>
                                                <span class="badge badge-{{ $installment->status === 'paid' ? 'success' : ($installment->status === 'waived' ? 'secondary' : 'warning') }}">
                                                    {{ ucfirst($installment->status) }}
                                                </span>
                                            </td>
                                            <td>
                                                @if($installment->status === 'pending')
                                                    <a href="{{ route('payable-transactions.create') }}?payee_id={{ $payee->id }}&installment_id={{ $installment->id }}" class="btn btn-sm modern-btn modern-btn-success">
                                                        Pay
                                                    </a>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif

                @if($payee->isDailyKisti())
                    <div class="mt-4">
                        <h5 class="mb-3">Daily Kisti Management</h5>
                        <div class="mb-3">
                            <a href="{{ route('payable-transactions.create') }}?payee_id={{ $payee->id }}&principal_amount={{ $kistiSummary['pending_amount'] ?? 0 }}&amount={{ $kistiSummary['pending_amount'] ?? 0 }}" class="btn modern-btn modern-btn-success">
                                <i class="fas fa-hand-holding-usd"></i> Pay Pending Kisti
                            </a>
                        </div>
                        <form action="{{ route('payees.kisti-skip', $payee->id) }}" method="POST" class="form-inline mb-3">
                            @csrf
                            <input type="date" name="skip_date" class="form-control mr-2" value="{{ now()->format('Y-m-d') }}">
                            <input type="text" name="reason" class="form-control mr-2" placeholder="Weekend/Holiday">
                            <button type="submit" class="btn modern-btn modern-btn-outline">
                                <i class="fas fa-ban"></i> Write Off Day
                            </button>
                        </form>
                        @if($payee->kistiSkips()->count() > 0)
                            <div class="table-responsive">
                                <table class="table modern-table">
                                    <thead>
                                        <tr>
                                            <th>Skip Date</th>
                                            <th>Reason</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($payee->kistiSkips()->orderBy('skip_date', 'desc')->limit(10)->get() as $skip)
                                            <tr>
                                                <td>{{ $skip->skip_date->format('d M, Y') }}</td>
                                                <td>{{ $skip->reason ?? 'N/A' }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    @endif

    @if($payee->isCcLoan())
        <div class="modal fade" id="payInterestModal" tabindex="-1" role="dialog" aria-labelledby="payInterestModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="payInterestModalLabel">Pay Interest</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form action="{{ route('payees.pay-interest', $payee->id) }}" method="POST">
                        @csrf
                        <div class="modal-body">
                            <input type="hidden" name="as_of_date" value="{{ now()->format('Y-m-d') }}">
                            <div class="form-group">
                                <label for="interest-account">Payment Account</label>
                                <select class="form-control" id="interest-account" name="account_id">
                                    @foreach($cashBankAccounts as $account)
                                        <option value="{{ $account->id }}" {{ $account->code === 'CASH-PRIMARY' ? 'selected' : '' }}>
                                            {{ $account->name }} [{{ $account->formatted_balance }}]
                                        </option>
                                    @endforeach
                                </select>
                                <small class="text-muted">Default selects cash; change if needed.</small>
                            </div>
                            <div class="form-group">
                                <label for="rounding-rule">Rounding Rule</label>
                                <select class="form-control" id="rounding-rule" name="rounding_rule">
                                    <option value="nearest_100" selected>Nearest 100</option>
                                    <option value="nearest_10">Nearest 10</option>
                                    <option value="none">No Rounding (Exact)</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="reference-no">Receipt Reference (optional)</label>
                                <input type="text" class="form-control" id="reference-no" name="reference_no" placeholder="Auto-generated if empty">
                            </div>
                            <div class="form-group mb-0">
                                <label for="interest-note">Note (optional)</label>
                                <input type="text" class="form-control" id="interest-note" name="description" placeholder="Interest payment (auto)">
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn modern-btn modern-btn-secondary" data-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn modern-btn modern-btn-success">
                                <i class="fas fa-check"></i> Confirm Payment
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <!-- Recent Transactions Card -->
    <div class="card modern-card mb-4">
        <div class="card-header modern-header success-header">
            <h3 class="card-title">
                <i class="fas fa-exchange-alt"></i> Recent Transactions
            </h3>
            <div class="card-tools">
                <span class="modern-badge">Last 10 Transactions</span>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table modern-table mb-0" id="transactions-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Type</th>
                            <th>Category</th>
                            <th>Amount</th>
                            <th>Reference</th>
                            <th>Description</th>
                            <th>Balance</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $recentTransactions = $payee->transactions()
                                ->orderBy('transaction_date', 'desc')
                                ->orderBy('created_at', 'desc')
                                ->take(10)
                                ->get();
                                
                            $runningBalance = $payee->opening_balance;
                            $allTransactions = $payee->transactions()
                                ->orderBy('transaction_date', 'asc')
                                ->orderBy('created_at', 'asc')
                                ->get();
                        @endphp
                        
                        @if($recentTransactions->count() > 0)
                            @foreach($recentTransactions as $transaction)
                                @php
                                    // Calculate running balance up to this transaction
                                    $tempBalance = $payee->opening_balance;
                                    foreach($allTransactions as $t) {
                                        if ($t->transaction_type == 'cash_in') {
                                            $tempBalance -= $t->amount;
                                        } else {
                                            $tempBalance += $t->amount;
                                        }
                                        
                                        if ($t->id == $transaction->id) {
                                            $transactionBalance = $tempBalance;
                                            break;
                                        }
                                    }
                                @endphp
                                <tr>
                                    <td>{{ \Carbon\Carbon::parse($transaction->transaction_date)->format('d M, Y') }}</td>
                                    <td>
                                        @if($transaction->transaction_type == 'cash_in')
                                            <span class="status-badge status-success">
                                                <i class="fas fa-arrow-down"></i> Cash In
                                            </span>
                                        @else
                                            <span class="status-badge status-danger">
                                                <i class="fas fa-arrow-up"></i> Cash Out
                                            </span>
                                        @endif
                                    </td>
                                    <td><span class="category-badge">{{ ucfirst($transaction->category) }}</span></td>
                                    <td>
                                        <span class="amount-text {{ $transaction->transaction_type == 'cash_in' ? 'text-success' : 'text-danger' }}">
                                            ৳{{ number_format($transaction->amount, 2) }}
                                        </span>
                                    </td>
                                    <td>{{ $transaction->reference_no ?: 'N/A' }}</td>
                                    <td>
                                        <span class="description-text" title="{{ $transaction->description }}">
                                            {{ Str::limit($transaction->description ?: 'N/A', 30) }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="balance-amount {{ isset($transactionBalance) && $transactionBalance > 0 ? 'text-danger' : (isset($transactionBalance) && $transactionBalance < 0 ? 'text-info' : 'text-success') }}">
                                            ৳{{ isset($transactionBalance) ? number_format($transactionBalance, 2) : '0.00' }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <a href="{{ route('payable-transactions.edit', $transaction->id) }}" class="btn modern-btn modern-btn-warning btn-sm" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="8" class="text-center empty-state">
                                    <i class="fas fa-exchange-alt fa-3x text-muted mb-3"></i>
                                    <h5>No transactions found</h5>
                                    <p class="text-muted">This payee has no transactions yet</p>
                                    <a href="{{ route('payable-transactions.create') }}?payee_id={{ $payee->id }}" class="btn modern-btn modern-btn-success mt-2">
                                        <i class="fas fa-plus"></i> Add First Transaction
                                    </a>
                                </td>
                            </tr>
                        @endif
                    </tbody>
                    @if($recentTransactions->count() > 0)
                    <tfoot>
                        <tr class="table-totals">
                            <th colspan="3">Recent Totals</th>
                            <th>৳{{ number_format($recentTransactions->sum('amount'), 2) }}</th>
                            <th colspan="4"></th>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
        </div>
        @if($recentTransactions->count() > 0)
        <div class="card-footer modern-footer">
            <a href="{{ route('payees.ledger', $payee->id) }}" class="btn modern-btn modern-btn-info">
                <i class="fas fa-list"></i> View All Transactions
            </a>
            <a href="{{ route('payable-transactions.create') }}?payee_id={{ $payee->id }}" class="btn modern-btn modern-btn-success">
                <i class="fas fa-plus"></i> Add New Transaction
            </a>
        </div>
        @endif
    </div>

    <!-- Transaction Summary by Category -->
    <div class="card modern-card mb-4">
        <div class="card-header modern-header info-header">
            <h3 class="card-title">
                <i class="fas fa-chart-pie"></i> Transaction Summary by Category
            </h3>
            <div class="card-tools">
                <button type="button" class="btn btn-sm btn-light" id="toggle-category-filters" data-toggle="tooltip" title="Show/Hide Filters">
                    <i class="fas fa-plus"></i>
                </button>
            </div>
        </div>
        
        <!-- Filter Section -->
        <div class="card-body border-bottom" id="category-filters" style="display: none;">
            <div class="row">
                <div class="col-md-4">
                    <label for="transaction-type-filter" class="form-label">
                        <i class="fas fa-exchange-alt"></i> Transaction Type
                    </label>
                    <select id="transaction-type-filter" class="form-control">
                        <option value="">All Types</option>
                        <option value="cash_in">Cash In</option>
                        <option value="cash_out">Cash Out</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="category-filter" class="form-label">
                        <i class="fas fa-tags"></i> Categories
                    </label>
                    <select id="category-filter" class="form-control select2" multiple="multiple" data-placeholder="Select categories...">
                        @php
                            $categories = $payee->transactions()
                                ->select('category')
                                ->distinct()
                                ->whereNotNull('category')
                                ->where('category', '!=', '')
                                ->pluck('category')
                                ->sort()
                                ->values();
                        @endphp
                        
                        @foreach($categories as $category)
                            <option value="{{ $category }}">{{ ucfirst($category) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <div class="btn-group w-100">
                        <button type="button" class="btn modern-btn modern-btn-primary" id="apply-category-filter">
                            <i class="fas fa-search"></i> Apply
                        </button>
                        <button type="button" class="btn modern-btn modern-btn-secondary" id="clear-category-filter">
                            <i class="fas fa-times"></i> Clear
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table modern-table mb-0" id="category-summary-table">
                    <thead>
                        <tr>
                            <th>Category</th>
                            <th>Transaction Count</th>
                            <th>Cash In Amount</th>
                            <th>Cash Out Amount</th>
                            <th>Net Amount</th>
                            <th>Last Transaction</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $categorySummary = [];
                            
                            foreach($payee->transactions as $transaction) {
                                $category = $transaction->category ?: 'Uncategorized';
                                
                                if (!isset($categorySummary[$category])) {
                                    $categorySummary[$category] = [
                                        'count' => 0,
                                        'cash_in' => 0,
                                        'cash_out' => 0,
                                        'last_date' => $transaction->transaction_date
                                    ];
                                }
                                
                                $categorySummary[$category]['count']++;
                                
                                if ($transaction->transaction_type == 'cash_in') {
                                    $categorySummary[$category]['cash_in'] += $transaction->amount;
                                } else {
                                    $categorySummary[$category]['cash_out'] += $transaction->amount;
                                }
                                
                                if ($transaction->transaction_date > $categorySummary[$category]['last_date']) {
                                    $categorySummary[$category]['last_date'] = $transaction->transaction_date;
                                }
                            }
                            
                            ksort($categorySummary);
                        @endphp
                        
                        @if(count($categorySummary) > 0)
                            @foreach($categorySummary as $category => $summary)
                                @php
                                    $netAmount = $summary['cash_out'] - $summary['cash_in'];
                                @endphp
                                <tr data-category="{{ $category }}" data-type="both">
                                    <td><span class="category-badge">{{ ucfirst($category) }}</span></td>
                                    <td><span class="count-badge">{{ $summary['count'] }}</span></td>
                                    <td><span class="amount-text text-success">৳{{ number_format($summary['cash_in'], 2) }}</span></td>
                                    <td><span class="amount-text text-danger">৳{{ number_format($summary['cash_out'], 2) }}</span></td>
                                    <td>
                                        <span class="amount-text {{ $netAmount > 0 ? 'text-danger' : ($netAmount < 0 ? 'text-success' : 'text-muted') }}">
                                            ৳{{ number_format($netAmount, 2) }}
                                        </span>
                                    </td>
                                    <td>{{ \Carbon\Carbon::parse($summary['last_date'])->format('d M, Y') }}</td>
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="6" class="text-center empty-state">
                                    <i class="fas fa-chart-pie fa-3x text-muted mb-3"></i>
                                    <h5>No transaction data</h5>
                                    <p class="text-muted">No transaction data available for categorization</p>
                                </td>
                            </tr>
                        @endif
                    </tbody>
                    @if(count($categorySummary) > 0)
                    <tfoot>
                        <tr class="table-totals" id="category-totals">
                            <th>Grand Totals</th>
                            <th id="category-total-count">{{ array_sum(array_column($categorySummary, 'count')) }}</th>
                            <th id="category-total-in">৳{{ number_format(array_sum(array_column($categorySummary, 'cash_in')), 2) }}</th>
                            <th id="category-total-out">৳{{ number_format(array_sum(array_column($categorySummary, 'cash_out')), 2) }}</th>
                            <th id="category-total-net">৳{{ number_format(array_sum(array_column($categorySummary, 'cash_out')) - array_sum(array_column($categorySummary, 'cash_in')), 2) }}</th>
                            <th></th>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>

@stop

@section('additional_css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="/css/modern-admin.css">

<style>
/* Payee-specific styles following customer pattern */
.stats-card {
    background: white;
    border-radius: var(--border-radius-lg);
    padding: 1.5rem;
    box-shadow: var(--shadow);
    border: none;
    position: relative;
    overflow: hidden;
    transition: all 0.3s ease;
    margin-bottom: 1.5rem;
}

.stats-card:hover {
    transform: translateY(-5px);
    box-shadow: var(--shadow-lg);
}

.stats-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.stats-card-primary::before {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.stats-card-success::before {
    background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
}

.stats-card-danger::before {
    background: linear-gradient(135deg, #fc466b 0%, #3f5efb 100%);
}

.stats-card-warning::before {
    background: linear-gradient(135deg, #ff9a9e 0%, #fecfef 100%);
}

.stats-icon {
    position: absolute;
    top: 1rem;
    right: 1rem;
    width: 60px;
    height: 60px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(102, 126, 234, 0.1);
    color: #667eea;
    font-size: 1.5rem;
}

.stats-card-success .stats-icon {
    background: rgba(17, 153, 142, 0.1);
    color: #11998e;
}

.stats-card-danger .stats-icon {
    background: rgba(252, 70, 107, 0.1);
    color: #fc466b;
}

.stats-card-warning .stats-icon {
    background: rgba(255, 154, 158, 0.1);
    color: #ff9a9e;
}

.stats-content {
    padding-right: 80px;
}

.stats-number {
    font-size: 2rem;
    font-weight: 700;
    color: #333;
    margin-bottom: 0.5rem;
    line-height: 1;
}

.stats-label {
    font-size: 0.875rem;
    color: var(--text-muted);
    margin-bottom: 0.5rem;
    font-weight: 500;
}

/* Info items styling */
.info-item {
    display: flex;
    flex-direction: column;
    margin-bottom: 1.5rem;
    padding: 1rem;
    background: rgba(102, 126, 234, 0.02);
    border-radius: var(--border-radius);
    border-left: 4px solid #667eea;
}

.info-label {
    font-size: 0.875rem;
    font-weight: 600;
    color: #6b7280;
    margin-bottom: 0.5rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.info-value {
    font-size: 1rem;
    font-weight: 600;
    color: #374151;
}

.payee-name {
    font-size: 1.25rem;
    color: #667eea;
}

.balance-amount {
    font-size: 1.1rem;
    font-weight: 700;
    font-family: 'Courier New', monospace;
}

.type-badge {
    padding: 0.25rem 0.75rem;
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.type-supplier {
    background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
    color: white;
}

.type-individual {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    color: white;
}

/* Action buttons row */
.action-buttons-row {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
    justify-content: flex-start;
}

.action-buttons-row .btn {
    flex: 1;
    min-width: 120px;
}

/* Table styling */
.amount-text {
    font-weight: 600;
    font-family: 'Courier New', monospace;
}

.count-badge {
    background: rgba(102, 126, 234, 0.1);
    color: #667eea;
    padding: 0.25rem 0.5rem;
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: 600;
}

.category-badge {
    background: rgba(17, 153, 142, 0.1);
    color: #11998e;
    padding: 0.25rem 0.75rem;
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: 500;
}

.description-text {
    font-size: 0.875rem;
    color: #6b7280;
}

/* Status badges */
.status-badge {
    padding: 0.25rem 0.75rem;
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
}

.status-success {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    color: white;
}

.status-warning {
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
    color: white;
}

.status-danger {
    background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
    color: white;
}

.status-info {
    background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
    color: white;
}

/* Action buttons */
.action-buttons {
    display: flex;
    gap: 0.25rem;
    justify-content: center;
}

.action-buttons .btn {
    padding: 0.5rem 0.75rem;
    font-size: 0.875rem;
    border-radius: var(--border-radius);
    min-width: 40px;
}

/* Table totals */
.table-totals {
    background: linear-gradient(135deg, rgba(102, 126, 234, 0.1), rgba(118, 75, 162, 0.1));
    font-weight: 600;
}

.table-totals th {
    border-top: 2px solid #667eea;
    padding: 1rem 0.75rem;
}

/* Empty states */
.empty-state {
    padding: 3rem 2rem;
    text-align: center;
}

.empty-state i {
    opacity: 0.5;
}

.empty-state h5 {
    color: #6b7280;
    margin: 1rem 0 0.5rem 0;
}

.empty-state p {
    color: #9ca3af;
    margin: 0;
}


/* Filter sections */
.filter-section {
    background: rgba(102, 126, 234, 0.02);
    border-bottom: 1px solid rgba(102, 126, 234, 0.1);
    transition: all 0.3s ease;
}

.filter-section .form-label {
    font-weight: 600;
    color: #374151;
    margin-bottom: 0.5rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.filter-section .select2-container {
    width: 100% !important;
}

.filter-section .select2-selection {
    border: 2px solid #e5e7eb !important;
    border-radius: 8px !important;
    min-height: 42px !important;
}

.filter-section .select2-selection:focus-within {
    border-color: #667eea !important;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1) !important;
}

.filter-active {
    background: rgba(102, 126, 234, 0.05) !important;
    border-left: 4px solid #667eea;
}

.filter-count {
    background: #667eea;
    color: white;
    padding: 0.25rem 0.5rem;
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: 600;
    margin-left: 0.5rem;
}

/* Hidden rows animation */
.table tbody tr.filtered-out {
    display: none;
}

.table tbody tr.filtered-in {
    display: table-row;
    animation: fadeIn 0.3s ease-in;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

/* Filter toggle button */
.card-tools .btn {
    transition: all 0.3s ease;
}

.card-tools .btn.active {
    background: rgba(255, 255, 255, 0.2) !important;
    transform: rotate(180deg);
}

/* Modern footer */
.modern-footer {
    background: #f8fafc;
    border-top: 1px solid #e5e7eb;
    padding: 1rem;
}

.modern-footer .btn {
    margin-right: 0.5rem;
    margin-bottom: 0.5rem;
}

/* Responsive design */
@media (max-width: 768px) {
    .stats-content {
        padding-right: 0;
    }
    
    .stats-icon {
        position: static;
        margin-bottom: 1rem;
        width: 50px;
        height: 50px;
        font-size: 1.25rem;
    }
    
    .stats-number {
        font-size: 1.5rem;
    }
    
    .info-item {
        margin-bottom: 1rem;
        padding: 0.75rem;
    }
    
    .action-buttons-row {
        flex-direction: column;
        gap: 0.5rem;
    }
    
    .action-buttons-row .btn {
        width: 100%;
        min-width: auto;
    }
    
    .action-buttons {
        flex-direction: column;
        gap: 0.25rem;
    }
    
    .action-buttons .btn {
        width: 100%;
    }
    
    .filter-section .btn-group {
        flex-direction: column;
        gap: 0.5rem;
    }
    
    .filter-section .btn {
        width: 100%;
    }
    
    .modern-footer .btn {
        width: 100%;
        margin-right: 0;
    }
}

@media (max-width: 576px) {
    .stats-card {
        padding: 1rem;
    }
    
    .stats-number {
        font-size: 1.25rem;
    }
    
    .stats-label {
        font-size: 0.75rem;
    }
    
}
</style>
@stop

@section('additional_js')
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap4.min.js"></script>
<script src="/js/modern-admin.js"></script>

<script>
$(document).ready(function() {
    // Initialize DataTables for all tables
    const tableConfig = {
        "paging": true,
        "pageLength": 10,
        "lengthChange": true,
        "lengthMenu": [[10, 25, 50, 100], [10, 25, 50, 100]],
        "searching": true,
        "ordering": true,
        "info": true,
        "autoWidth": false,
        "responsive": true,
        "language": {
            "emptyTable": "No data available",
            "info": "Showing _START_ to _END_ of _TOTAL_ entries",
            "infoEmpty": "Showing 0 to 0 of 0 entries",
            "infoFiltered": "(filtered from _MAX_ total entries)",
            "lengthMenu": "Show _MENU_ entries",
            "loadingRecords": "Loading...",
            "processing": "Processing...",
            "search": "Search:",
            "zeroRecords": "No matching records found",
            "paginate": {
                "first": "First",
                "last": "Last",
                "next": "Next",
                "previous": "Previous"
            }
        },
        "dom": '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>' +
               '<"row"<"col-sm-12"tr>>' +
               '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>'
    };

    // Initialize individual tables
    $('#transactions-table').DataTable(Object.assign({}, tableConfig, {
        "order": [[0, "desc"]], // Sort by date descending
        "columnDefs": [
            { "orderable": false, "targets": [7] } // Actions column
        ]
    }));

    $('#category-summary-table').DataTable(Object.assign({}, tableConfig, {
        "order": [[4, "desc"]], // Sort by net amount descending
        "pageLength": 25
    }));

    // Initialize tooltips
    $('[data-toggle="tooltip"]').tooltip();

    // Filter functionality for Category Summary
    $('#toggle-category-filters').on('click', function() {
        const $filters = $('#category-filters');
        const $button = $(this);
        const $icon = $button.find('i');
        
        $filters.slideToggle(300);
        $button.toggleClass('active');
        
        if ($filters.is(':visible')) {
            $icon.removeClass('fa-plus').addClass('fa-minus');
            $('#transaction-type-filter').focus();
        } else {
            $icon.removeClass('fa-minus').addClass('fa-plus');
        }
    });

    // Apply Category Filter
    $('#apply-category-filter').on('click', function() {
        const selectedType = $('#transaction-type-filter').val();
        const selectedCategories = $('#category-filter').val() || [];
        
        filterCategoryTable(selectedType, selectedCategories);
        
        // Update filter indicator
        const filterCount = (selectedType ? 1 : 0) + selectedCategories.length;
        updateFilterIndicator('#toggle-category-filters', filterCount);
        
        if (filterCount > 0) {
            ModernAdmin.showAlert(`Applied ${filterCount} filter${filterCount === 1 ? '' : 's'}`, 'success', 3000);
        }
    });

    // Clear Category Filter
    $('#clear-category-filter').on('click', function() {
        $('#transaction-type-filter').val('');
        $('#category-filter').val(null).trigger('change');
        filterCategoryTable('', []);
        updateFilterIndicator('#toggle-category-filters', 0);
        ModernAdmin.showAlert('Category filters cleared', 'info', 2000);
    });

    // Filter category table function
    function filterCategoryTable(type, categories) {
        const $table = $('#category-summary-table');
        const $tbody = $table.find('tbody');
        const $rows = $tbody.find('tr[data-category]');
        
        let visibleRows = 0;
        let totalCount = 0;
        let totalIn = 0;
        let totalOut = 0;
        let totalNet = 0;
        
        if (type === '' && categories.length === 0) {
            // Show all rows
            $rows.removeClass('filtered-out').addClass('filtered-in').show();
            visibleRows = $rows.length;
            
            // Calculate totals for all visible rows
            $rows.each(function() {
                const $row = $(this);
                totalCount += parseInt($row.find('.count-badge').text()) || 0;
                totalIn += parseFloat($row.find('.amount-text.text-success').text().replace(/[৳,]/g, '')) || 0;
                totalOut += parseFloat($row.find('.amount-text.text-danger').text().replace(/[৳,]/g, '')) || 0;
            });
        } else {
            // Filter rows
            $rows.each(function() {
                const $row = $(this);
                const rowCategory = $row.data('category');
                let showRow = true;
                
                // Check category filter
                if (categories.length > 0 && !categories.includes(rowCategory)) {
                    showRow = false;
                }
                
                // Note: Type filter would need more complex logic based on cash_in/cash_out amounts
                // For now, we'll keep it simple and just filter by category
                
                if (showRow) {
                    $row.removeClass('filtered-out').addClass('filtered-in').show();
                    visibleRows++;
                    
                    // Add to totals
                    totalCount += parseInt($row.find('.count-badge').text()) || 0;
                    totalIn += parseFloat($row.find('.amount-text.text-success').text().replace(/[৳,]/g, '')) || 0;
                    totalOut += parseFloat($row.find('.amount-text.text-danger').text().replace(/[৳,]/g, '')) || 0;
                } else {
                    $row.removeClass('filtered-in').addClass('filtered-out').hide();
                }
            });
        }
        
        totalNet = totalOut - totalIn;
        
        // Update totals
        updateCategoryTotals(totalCount, totalIn, totalOut, totalNet);
        
        // Update DataTable info if initialized
        if ($.fn.DataTable.isDataTable('#category-summary-table')) {
            $('#category-summary-table').DataTable().draw(false);
        }
    }

    // Update category totals
    function updateCategoryTotals(count, cashIn, cashOut, net) {
        $('#category-total-count').text(count.toLocaleString());
        $('#category-total-in').text('৳' + cashIn.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
        $('#category-total-out').text('৳' + cashOut.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
        $('#category-total-net').text('৳' + net.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
        
        // Add visual feedback
        $('#category-totals').addClass('pulse');
        setTimeout(() => $('#category-totals').removeClass('pulse'), 500);
    }

    // Update filter indicator
    function updateFilterIndicator(buttonSelector, count) {
        const $button = $(buttonSelector);
        const $existing = $button.find('.filter-count');
        
        if (count > 0) {
            if ($existing.length) {
                $existing.text(count);
            } else {
                $button.append(`<span class="filter-count">${count}</span>`);
            }
            $button.addClass('btn-warning').removeClass('btn-light');
        } else {
            $existing.remove();
            $button.removeClass('btn-warning').addClass('btn-light');
        }
    }

    // Initialize Select2 for category filters
    $('#category-filter').select2({
        width: '100%',
        placeholder: 'Select categories to filter...',
        allowClear: true,
        closeOnSelect: false,
        escapeMarkup: function(markup) {
            return markup;
        },
        templateResult: function(data) {
            if (!data.id) return data.text;
            return $(`<span><i class="fas fa-tag text-primary"></i> ${data.text}</span>`);
        },
        templateSelection: function(data) {
            if (!data.id) return data.text;
            return data.text;
        }
    });

    // Keyboard shortcuts for filters
    $(document).on('keydown', function(e) {
        // Alt + C for Category filters
        if (e.altKey && e.keyCode === 67) {
            e.preventDefault();
            $('#toggle-category-filters').click();
        }
        
        // Enter to apply filter when in filter dropdowns
        if (e.keyCode === 13) {
            if ($('#transaction-type-filter').is(':focus') || $('#category-filter').is(':focus') || $('.select2-search__field').is(':focus')) {
                $('#apply-category-filter').click();
            }
        }
        
        // Escape to clear filters
        if (e.keyCode === 27) {
            if ($('#category-filters').is(':visible')) {
                $('#clear-category-filter').click();
            }
        }
        
        // Ctrl + F to focus search
        if ((e.ctrlKey || e.metaKey) && e.keyCode === 70) {
            e.preventDefault();
            $('.dataTables_filter input').focus();
        }
    });

    // Auto-apply filter on select change
    $('#transaction-type-filter').on('change', function() {
        if ($(this).val() === '') {
            $('#apply-category-filter').click();
        }
    });

    $('#category-filter').on('change', function() {
        const selectedCount = $(this).val() ? $(this).val().length : 0;
        if (selectedCount === 0) {
            $('#apply-category-filter').click();
        }
    });

    // Add loading animation to action buttons
    $('.action-buttons .btn, .action-buttons-row .btn').on('click', function() {
        const $btn = $(this);
        if (!$btn.hasClass('loading')) {
            $btn.addClass('loading');
            setTimeout(() => $btn.removeClass('loading'), 2000);
        }
    });

    // Add visual feedback for balance amounts
    $('.balance-amount').each(function() {
        const amount = parseFloat($(this).text().replace(/[৳,]/g, ''));
        if (Math.abs(amount) > 10000) {
            $(this).addClass('high-amount');
        }
    });

    // Save filter preferences to localStorage
    function saveFilterPreferences() {
        const preferences = {
            transactionType: $('#transaction-type-filter').val(),
            categories: $('#category-filter').val() || [],
            categoryFiltersVisible: $('#category-filters').is(':visible')
        };
        
        ModernAdmin.utils.storage.set('payee-filter-preferences', preferences);
    }

    // Load filter preferences from localStorage
    function loadFilterPreferences() {
        const preferences = ModernAdmin.utils.storage.get('payee-filter-preferences');
        
        if (preferences) {
            // Restore selections
            if (preferences.transactionType) {
                $('#transaction-type-filter').val(preferences.transactionType);
            }
            
            if (preferences.categories && preferences.categories.length > 0) {
                $('#category-filter').val(preferences.categories).trigger('change');
            }
            
            // Apply filters if any were set
            if (preferences.transactionType || (preferences.categories && preferences.categories.length > 0)) {
                $('#apply-category-filter').click();
            }
            
            // Restore filter visibility
            if (preferences.categoryFiltersVisible) {
                $('#toggle-category-filters').click();
            }
        }
    }

    // Save preferences when filters change
    $('#transaction-type-filter, #category-filter').on('change', saveFilterPreferences);
    $('#toggle-category-filters').on('click', function() {
        setTimeout(saveFilterPreferences, 100);
    });

    // Load preferences on page load
    setTimeout(loadFilterPreferences, 500);

    // Update tooltip for filter button
    $('#toggle-category-filters').attr('data-original-title', 
        'Toggle transaction filters<br><small>Shortcut: Alt + C</small>');

    console.log('Payee details page with advanced filtering initialized successfully');
});
</script>
@stop
