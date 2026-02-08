@extends('layouts.modern-admin')

@section('title', 'Ledger - ' . $account->name)

@section('page_title', 'Account Ledger: ' . $account->name)

@section('header_actions')
    <a href="{{ route('accounting.accounts.ledger.print', ['account' => $account, 'from_date' => $fromDate, 'to_date' => $toDate]) }}" class="btn modern-btn modern-btn-primary" target="_blank">
        <i class="fas fa-print"></i> Print
    </a>
@stop

@section('page_content')
    <!-- Date Filter -->
    <div class="card modern-card mb-4">
        <div class="card-header modern-header">
            <h3 class="card-title">
                <i class="fas fa-calendar"></i> Date Range
            </h3>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('accounting.accounts.ledger', $account) }}">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="form-label">From Date</label>
                            <input type="date" name="from_date" class="form-control modern-input" value="{{ $fromDate }}">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="form-label">To Date</label>
                            <input type="date" name="to_date" class="form-control modern-input" value="{{ $toDate }}">
                        </div>
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <button type="submit" class="btn modern-btn modern-btn-primary">
                            <i class="fas fa-search"></i> Show Ledger
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Account Summary -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body text-center">
                    <h6>Opening Balance</h6>
                    <h4>৳{{ number_format($ledger['opening_balance']['balance'], 2) }}</h4>
                    <small>{{ ucfirst($ledger['opening_balance']['type']) }}</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body text-center">
                    <h6>Total Debit</h6>
                    <h4>৳{{ number_format($ledger['totals']['debit'], 2) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-danger text-white">
                <div class="card-body text-center">
                    <h6>Total Credit</h6>
                    <h4>৳{{ number_format($ledger['totals']['credit'], 2) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body text-center">
                    <h6>Closing Balance</h6>
                    <h4>৳{{ number_format($ledger['closing_balance']['balance'], 2) }}</h4>
                    <small>{{ ucfirst($ledger['closing_balance']['type']) }}</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Ledger Entries -->
    <div class="card modern-card">
        <div class="card-header modern-header success-header">
            <h3 class="card-title">
                <i class="fas fa-list"></i> Ledger Entries
            </h3>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table modern-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Reference</th>
                            <th>Source</th>
                            <th>Particulars</th>
                            <th class="text-right">Debit (৳)</th>
                            <th class="text-right">Credit (৳)</th>
                            <th class="text-right">Balance (৳)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Opening Balance Row -->
                        <tr class="table-active">
                            <td>{{ \Carbon\Carbon::parse($fromDate)->format('d M, Y') }}</td>
                            <td>-</td>
                            <td>-</td>
                            <td><strong>Opening Balance</strong></td>
                            <td class="text-right">{{ $ledger['opening_balance']['type'] === 'debit' ? number_format($ledger['opening_balance']['balance'], 2) : '-' }}</td>
                            <td class="text-right">{{ $ledger['opening_balance']['type'] === 'credit' ? number_format($ledger['opening_balance']['balance'], 2) : '-' }}</td>
                            <td class="text-right">
                                {{ number_format($ledger['opening_balance']['balance'], 2) }}
                                <small class="text-muted">{{ ucfirst($ledger['opening_balance']['type']) }}</small>
                            </td>
                        </tr>

                        @forelse($ledger['entries'] as $entry)
                            <tr>
                                <td>{{ $entry['date']->format('d M, Y') }}</td>
                                <td>{{ $entry['reference'] ?? '-' }}</td>
                                <td>{{ $entry['source_type'] ?? '-' }}</td>
                                <td>{{ $entry['particulars'] ?? '-' }}</td>
                                <td class="text-right {{ $entry['debit'] > 0 ? 'text-success' : '' }}">
                                    {{ $entry['debit'] > 0 ? number_format($entry['debit'], 2) : '-' }}
                                </td>
                                <td class="text-right {{ $entry['credit'] > 0 ? 'text-danger' : '' }}">
                                    {{ $entry['credit'] > 0 ? number_format($entry['credit'], 2) : '-' }}
                                </td>
                                <td class="text-right">
                                    {{ number_format($entry['running_balance'], 2) }}
                                    <small class="text-muted">{{ ucfirst($entry['balance_type']) }}</small>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4">
                                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">No transactions found for this period.</p>
                                </td>
                            </tr>
                        @endforelse

                        <!-- Closing Balance Row -->
                        <tr class="table-active">
                            <td>{{ \Carbon\Carbon::parse($toDate)->format('d M, Y') }}</td>
                            <td>-</td>
                            <td>-</td>
                            <td><strong>Closing Balance</strong></td>
                            <td class="text-right"><strong>{{ number_format($ledger['totals']['debit'], 2) }}</strong></td>
                            <td class="text-right"><strong>{{ number_format($ledger['totals']['credit'], 2) }}</strong></td>
                            <td class="text-right">
                                <strong>{{ number_format($ledger['closing_balance']['balance'], 2) }}</strong>
                                <small class="text-muted">{{ ucfirst($ledger['closing_balance']['type']) }}</small>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="text-center mt-4">
        <a href="{{ route('accounting.accounts.index') }}" class="btn btn-outline-secondary btn-lg">
            <i class="fas fa-arrow-left"></i> Back to Accounts
        </a>
    </div>
@stop
