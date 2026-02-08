@extends('layouts.modern-admin')

@section('title', 'Profit & Loss Statement')

@section('page_title', 'Profit & Loss Statement')

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
                <i class="fas fa-calendar"></i> Report Period
            </h3>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('accounting.reports.profit-loss') }}">
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
        <h4>Profit & Loss Statement</h4>
        <p class="text-muted">
            For the period {{ \Carbon\Carbon::parse($fromDate)->format('d F, Y') }}
            to {{ \Carbon\Carbon::parse($toDate)->format('d F, Y') }}
        </p>
    </div>

    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card modern-card">
                <div class="card-body p-0">
                    <table class="table modern-table mb-0">
                        <!-- Income Section -->
                        <thead class="bg-success text-white">
                            <tr>
                                <th colspan="2"><i class="fas fa-arrow-up"></i> Income</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($profitLoss['income']['groups'] ?? [] as $group)
                                <tr class="table-light">
                                    <td><strong>{{ $group['group'] ?? $group['name'] ?? 'Unknown' }}</strong></td>
                                    <td class="text-right">৳{{ number_format($group['balance'] ?? 0, 2) }}</td>
                                </tr>
                                @if(!empty($group['children']))
                                    @foreach($group['children'] as $child)
                                        <tr>
                                            <td class="pl-4">{{ $child['group'] ?? $child['name'] ?? '' }}</td>
                                            <td class="text-right">৳{{ number_format($child['balance'] ?? 0, 2) }}</td>
                                        </tr>
                                    @endforeach
                                @endif
                            @endforeach
                        </tbody>
                        <tfoot class="table-success">
                            <tr>
                                <td><strong>Total Income</strong></td>
                                <td class="text-right"><strong>৳{{ number_format($profitLoss['income']['total'] ?? 0, 2) }}</strong></td>
                            </tr>
                        </tfoot>

                        <!-- Expenses Section -->
                        <thead class="bg-danger text-white">
                            <tr>
                                <th colspan="2"><i class="fas fa-arrow-down"></i> Expenses</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($profitLoss['expenses']['groups'] ?? [] as $group)
                                <tr class="table-light">
                                    <td><strong>{{ $group['group'] ?? $group['name'] ?? 'Unknown' }}</strong></td>
                                    <td class="text-right">৳{{ number_format($group['balance'] ?? 0, 2) }}</td>
                                </tr>
                                @if(!empty($group['children']))
                                    @foreach($group['children'] as $child)
                                        <tr>
                                            <td class="pl-4">{{ $child['group'] ?? $child['name'] ?? '' }}</td>
                                            <td class="text-right">৳{{ number_format($child['balance'] ?? 0, 2) }}</td>
                                        </tr>
                                    @endforeach
                                @endif
                            @endforeach
                        </tbody>
                        <tfoot class="table-danger">
                            <tr>
                                <td><strong>Total Expenses</strong></td>
                                <td class="text-right"><strong>৳{{ number_format($profitLoss['expenses']['total'] ?? 0, 2) }}</strong></td>
                            </tr>
                        </tfoot>

                        <!-- Net Profit/Loss -->
                        <tfoot class="table-{{ $profitLoss['net_profit'] >= 0 ? 'primary' : 'warning' }}">
                            <tr>
                                <td>
                                    <strong style="font-size: 1.1rem;">
                                        {{ $profitLoss['net_profit'] >= 0 ? 'Net Profit' : 'Net Loss' }}
                                    </strong>
                                </td>
                                <td class="text-right">
                                    <strong style="font-size: 1.2rem;" class="{{ $profitLoss['net_profit'] >= 0 ? 'text-success' : 'text-danger' }}">
                                        ৳{{ number_format(abs($profitLoss['net_profit']), 2) }}
                                    </strong>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
@stop

@section('additional_css')
<style>
    @media print {
        .no-print { display: none !important; }
        .card { border: none !important; box-shadow: none !important; }
        .table th, .table td { border: 1px solid #000 !important; }
    }
</style>
@stop
