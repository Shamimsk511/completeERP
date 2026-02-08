@extends('layouts.modern-admin')

@section('title', 'Bank Book')

@section('page_title', 'Bank Book')

@section('header_actions')
    <button onclick="window.print()" class="btn modern-btn modern-btn-primary">
        <i class="fas fa-print"></i> Print
    </button>
@stop

@section('page_content')
    <!-- Date Filter -->
    <div class="card modern-card mb-4 no-print">
        <div class="card-header modern-header">
            <h3 class="card-title">
                <i class="fas fa-calendar"></i> Filter
            </h3>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('accounting.reports.bank-book') }}">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label class="form-label">Bank Account</label>
                            <select name="bank_account_id" class="form-control select2">
                                <option value="">All Bank Accounts</option>
                                @foreach($bankAccounts as $bank)
                                    <option value="{{ $bank->id }}" {{ ($bankAccountId ?? '') == $bank->id ? 'selected' : '' }}>
                                        {{ $bank->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label class="form-label">From Date</label>
                            <input type="date" name="from_date" class="form-control modern-input" value="{{ $fromDate }}">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label class="form-label">To Date</label>
                            <input type="date" name="to_date" class="form-control modern-input" value="{{ $toDate }}">
                        </div>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="submit" class="btn modern-btn modern-btn-primary">
                            <i class="fas fa-search"></i> Generate Report
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @php
        $selectedBank = $bankAccountId ? $bankAccounts->firstWhere('id', $bankAccountId) : null;
    @endphp

    <!-- Report Header -->
    <div class="text-center mb-4">
        <h2>{{ $businessSettings->business_name ?? config('adminlte.title') }}</h2>
        <h4>Bank Book</h4>
        @if($selectedBank)
            <h5 class="text-primary">{{ $selectedBank->name }}</h5>
            @if($selectedBank->bank_name ?? null)
                <p class="text-muted">{{ $selectedBank->bank_name }} - A/C: {{ $selectedBank->bank_account_number ?? '' }}</p>
            @endif
        @endif
        <p class="text-muted">
            From {{ \Carbon\Carbon::parse($fromDate)->format('d F, Y') }}
            to {{ \Carbon\Carbon::parse($toDate)->format('d F, Y') }}
        </p>
    </div>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body text-center">
                    <h6>Opening Balance</h6>
                    <h4>৳{{ number_format($data['opening_balance'], 2) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body text-center">
                    <h6>Total Deposits</h6>
                    <h4>৳{{ number_format($data['totals']['deposits'], 2) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-danger text-white">
                <div class="card-body text-center">
                    <h6>Total Withdrawals</h6>
                    <h4>৳{{ number_format($data['totals']['withdrawals'], 2) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body text-center">
                    <h6>Closing Balance</h6>
                    <h4>৳{{ number_format($data['closing_balance'], 2) }}</h4>
                </div>
            </div>
        </div>
    </div>

    <!-- Bank Book Entries -->
    <div class="card modern-card">
        <div class="card-header modern-header success-header">
            <h3 class="card-title">
                <i class="fas fa-university"></i> Bank Transactions
            </h3>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table modern-table mb-0">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Reference</th>
                            <th>Source</th>
                            <th>Particulars</th>
                            <th class="text-right">Deposit (৳)</th>
                            <th class="text-right">Withdrawal (৳)</th>
                            <th class="text-right">Balance (৳)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Opening Balance -->
                        <tr class="table-active">
                            <td>{{ \Carbon\Carbon::parse($fromDate)->format('d M, Y') }}</td>
                            <td>-</td>
                            <td>-</td>
                            <td><strong>Opening Balance</strong></td>
                            <td class="text-right">-</td>
                            <td class="text-right">-</td>
                            <td class="text-right"><strong>৳{{ number_format($data['opening_balance'], 2) }}</strong></td>
                        </tr>

                        @forelse($data['entries'] as $entry)
                            <tr>
                                <td>{{ $entry['date']->format('d M, Y') }}</td>
                                <td>{{ $entry['reference'] ?? '-' }}</td>
                                <td>{{ $entry['source_type'] ?? '-' }}</td>
                                <td>{{ $entry['particulars'] }}</td>
                                <td class="text-right {{ $entry['deposit'] > 0 ? 'text-success' : '' }}">
                                    {{ $entry['deposit'] > 0 ? number_format($entry['deposit'], 2) : '-' }}
                                </td>
                                <td class="text-right {{ $entry['withdrawal'] > 0 ? 'text-danger' : '' }}">
                                    {{ $entry['withdrawal'] > 0 ? number_format($entry['withdrawal'], 2) : '-' }}
                                </td>
                                <td class="text-right">৳{{ number_format($entry['balance'], 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4">
                                    <p class="text-muted">No bank transactions found for this period.</p>
                                </td>
                            </tr>
                        @endforelse

                        <!-- Closing Balance -->
                        <tr class="table-active">
                            <td>{{ \Carbon\Carbon::parse($toDate)->format('d M, Y') }}</td>
                            <td>-</td>
                            <td>-</td>
                            <td><strong>Closing Balance</strong></td>
                            <td class="text-right"><strong>৳{{ number_format($data['totals']['deposits'], 2) }}</strong></td>
                            <td class="text-right"><strong>৳{{ number_format($data['totals']['withdrawals'], 2) }}</strong></td>
                            <td class="text-right"><strong>৳{{ number_format($data['closing_balance'], 2) }}</strong></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@stop

@section('additional_css')
<style>
    @media print {
        .no-print { display: none !important; }
        .card { border: none !important; box-shadow: none !important; }
    }
</style>
@stop
