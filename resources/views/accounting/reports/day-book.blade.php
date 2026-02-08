@extends('layouts.modern-admin')

@section('title', 'Day Book')

@section('page_title', 'Day Book')

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
                <i class="fas fa-calendar"></i> Select Date
            </h3>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('accounting.reports.day-book') }}">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="form-label">Date</label>
                            <input type="date" name="date" class="form-control modern-input" value="{{ $date }}">
                        </div>
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <button type="submit" class="btn modern-btn modern-btn-primary">
                            <i class="fas fa-search"></i> Show Day Book
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Report Header -->
    <div class="text-center mb-4">
        <h2>{{ $businessSettings->business_name ?? config('adminlte.title') }}</h2>
        <h4>Day Book</h4>
        <p class="text-muted">Date: {{ \Carbon\Carbon::parse($date)->format('d F, Y') }}</p>
    </div>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body text-center">
                    <h6>Total Entries</h6>
                    <h3>{{ $dayBook['summary']['total_entries'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body text-center">
                    <h6>Total Debits</h6>
                    <h3>৳{{ number_format($dayBook['summary']['total_debits'], 2) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-danger text-white">
                <div class="card-body text-center">
                    <h6>Total Credits</h6>
                    <h3>৳{{ number_format($dayBook['summary']['total_credits'], 2) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body text-center">
                    <h6>Net Movement</h6>
                    <h3>৳{{ number_format($dayBook['summary']['total_debits'] - $dayBook['summary']['total_credits'], 2) }}</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Entries List -->
    <div class="card modern-card">
        <div class="card-header modern-header success-header">
            <h3 class="card-title">
                <i class="fas fa-list"></i> Ledger Entries
            </h3>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table modern-table mb-0">
                    <thead>
                        <tr>
                            <th>Reference</th>
                            <th>Source</th>
                            <th>Account</th>
                            <th>Description</th>
                            <th class="text-right">Debit (৳)</th>
                            <th class="text-right">Credit (৳)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($dayBook['entries'] as $entry)
                            <tr>
                                <td>{{ $entry['reference'] ?? '-' }}</td>
                                <td>{{ $entry['source_type'] ?? '-' }}</td>
                                <td>{{ $entry['account'] ?? '-' }}</td>
                                <td>{{ Str::limit($entry['description'] ?? '-', 50) }}</td>
                                <td class="text-right">{{ number_format($entry['debit'] ?? 0, 2) }}</td>
                                <td class="text-right">{{ number_format($entry['credit'] ?? 0, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4">
                                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">No ledger entries found for this date.</p>
                                </td>
                            </tr>
                        @endforelse
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
