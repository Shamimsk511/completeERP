@extends('layouts.modern-admin')

@section('title', 'Trial Balance')

@section('page_title', 'Trial Balance')

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
                <i class="fas fa-calendar"></i> Report Date
            </h3>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('accounting.reports.trial-balance') }}">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="form-label">As of Date</label>
                            <input type="date" name="date" class="form-control modern-input" value="{{ $date }}">
                        </div>
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <button type="submit" class="btn modern-btn modern-btn-primary">
                            <i class="fas fa-search"></i> Generate Report
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Report Header -->
    <div class="text-center mb-4">
        <h2>{{ $businessSettings->business_name ?? config('adminlte.title') }}</h2>
        <h4>Trial Balance</h4>
        <p class="text-muted">As on {{ \Carbon\Carbon::parse($date)->format('d F, Y') }}</p>
    </div>

    <!-- Trial Balance Table -->
    <div class="card modern-card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table modern-table">
                    <thead>
                        <tr>
                            <th>Account Code</th>
                            <th>Account Name</th>
                            <th class="text-right">Debit (৳)</th>
                            <th class="text-right">Credit (৳)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($trialBalance['accounts'] as $account)
                            <tr>
                                <td>{{ $account['account_code'] }}</td>
                                <td>{{ $account['account_name'] }}</td>
                                <td class="text-right {{ $account['debit'] > 0 ? 'text-success' : '' }}">
                                    {{ $account['debit'] > 0 ? number_format($account['debit'], 2) : '-' }}
                                </td>
                                <td class="text-right {{ $account['credit'] > 0 ? 'text-danger' : '' }}">
                                    {{ $account['credit'] > 0 ? number_format($account['credit'], 2) : '-' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="table-active">
                        <tr>
                            <td colspan="2" class="text-right"><strong>Total:</strong></td>
                            <td class="text-right"><strong>৳{{ number_format($trialBalance['total_debits'], 2) }}</strong></td>
                            <td class="text-right"><strong>৳{{ number_format($trialBalance['total_credits'], 2) }}</strong></td>
                        </tr>
                        <tr>
                            <td colspan="2" class="text-right"><strong>Difference:</strong></td>
                            <td colspan="2" class="text-center">
                                @if($trialBalance['is_balanced'])
                                    <span class="badge badge-success px-4 py-2">Balanced ✓</span>
                                @else
                                    <span class="badge badge-danger px-4 py-2">
                                        Difference: ৳{{ number_format(abs($trialBalance['total_debits'] - $trialBalance['total_credits']), 2) }}
                                    </span>
                                @endif
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
@stop

@section('additional_css')
<style>
    @media print {
        .no-print {
            display: none !important;
        }

        .card {
            border: none !important;
            box-shadow: none !important;
        }

        .table {
            border: 1px solid #000;
        }

        .table th, .table td {
            border: 1px solid #000 !important;
        }
    }
</style>
@stop
