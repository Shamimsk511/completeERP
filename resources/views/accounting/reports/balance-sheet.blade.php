@extends('layouts.modern-admin')

@section('title', 'Balance Sheet')

@section('page_title', 'Balance Sheet')

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
            <form method="GET" action="{{ route('accounting.reports.balance-sheet') }}">
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
        <h4>Balance Sheet</h4>
        <p class="text-muted">As on {{ \Carbon\Carbon::parse($date)->format('d F, Y') }}</p>
    </div>

    <div class="row">
        <!-- Assets Side -->
        <div class="col-md-6">
            <div class="card modern-card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-arrow-up"></i> Assets</h5>
                </div>
                <div class="card-body p-0">
                    <table class="table modern-table mb-0">
                        <tbody>
                            @foreach($balanceSheet['assets']['groups'] ?? [] as $group)
                                <tr class="table-light">
                                    <td><strong>{{ $group['group'] ?? $group['name'] ?? 'Unknown' }}</strong></td>
                                    <td></td>
                                </tr>
                                @foreach($group['children'] ?? [] as $child)
                                    <tr>
                                        <td class="pl-4">{{ $child['group'] ?? $child['name'] ?? '' }}</td>
                                        <td class="text-right">৳{{ number_format($child['balance'] ?? 0, 2) }}</td>
                                    </tr>
                                @endforeach
                                <tr>
                                    <td class="text-right"><em>Subtotal:</em></td>
                                    <td class="text-right"><em>৳{{ number_format($group['balance'] ?? 0, 2) }}</em></td>
                                </tr>
                            @endforeach

                            @foreach($balanceSheet['assets']['computed'] ?? [] as $computed)
                                <tr>
                                    <td><strong>{{ $computed['label'] }}</strong></td>
                                    <td class="text-right">৳{{ number_format($computed['amount'] ?? 0, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="table-primary">
                            <tr>
                                <td><strong>Total Assets</strong></td>
                                <td class="text-right"><strong>৳{{ number_format($balanceSheet['assets']['total'] ?? 0, 2) }}</strong></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <!-- Liabilities & Capital Side -->
        <div class="col-md-6">
            <div class="card modern-card">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0"><i class="fas fa-arrow-down"></i> Liabilities & Capital</h5>
                </div>
                <div class="card-body p-0">
                    <table class="table modern-table mb-0">
                        <tbody>
                            <!-- Liabilities -->
                            @foreach($balanceSheet['liabilities']['groups'] ?? [] as $group)
                                <tr class="table-light">
                                    <td><strong>{{ $group['group'] ?? $group['name'] ?? 'Unknown' }}</strong></td>
                                    <td></td>
                                </tr>
                                @foreach($group['children'] ?? [] as $child)
                                    <tr>
                                        <td class="pl-4">{{ $child['group'] ?? $child['name'] ?? '' }}</td>
                                        <td class="text-right">৳{{ number_format($child['balance'] ?? 0, 2) }}</td>
                                    </tr>
                                @endforeach
                                <tr>
                                    <td class="text-right"><em>Subtotal:</em></td>
                                    <td class="text-right"><em>৳{{ number_format($group['balance'] ?? 0, 2) }}</em></td>
                                </tr>
                            @endforeach

                            @foreach($balanceSheet['liabilities']['computed'] ?? [] as $computed)
                                <tr>
                                    <td><strong>{{ $computed['label'] }}</strong></td>
                                    <td class="text-right">৳{{ number_format($computed['amount'] ?? 0, 2) }}</td>
                                </tr>
                            @endforeach

                            <!-- Capital -->
                            @foreach($balanceSheet['capital']['groups'] ?? [] as $group)
                                <tr class="table-light">
                                    <td><strong>{{ $group['group'] ?? $group['name'] ?? 'Unknown' }}</strong></td>
                                    <td></td>
                                </tr>
                                @foreach($group['children'] ?? [] as $child)
                                    <tr>
                                        <td class="pl-4">{{ $child['group'] ?? $child['name'] ?? '' }}</td>
                                        <td class="text-right">৳{{ number_format($child['balance'] ?? 0, 2) }}</td>
                                    </tr>
                                @endforeach
                                <tr>
                                    <td class="text-right"><em>Subtotal:</em></td>
                                    <td class="text-right"><em>৳{{ number_format($group['balance'] ?? 0, 2) }}</em></td>
                                </tr>
                            @endforeach

                            <!-- Net Profit/Loss -->
                            <tr class="table-warning">
                                <td><strong>Net Profit/Loss</strong></td>
                                <td class="text-right">
                                    <strong class="{{ ($balanceSheet['capital']['profit_loss'] ?? 0) >= 0 ? 'text-success' : 'text-danger' }}">
                                        ৳{{ number_format($balanceSheet['capital']['profit_loss'] ?? 0, 2) }}
                                    </strong>
                                </td>
                            </tr>
                        </tbody>
                        <tfoot class="table-danger">
                            <tr>
                                <td><strong>Total Liabilities & Capital</strong></td>
                                <td class="text-right"><strong>৳{{ number_format($balanceSheet['liabilities_and_capital'] ?? 0, 2) }}</strong></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Balance Check -->
    <div class="card modern-card mt-4">
        <div class="card-body text-center">
            @if($balanceSheet['is_balanced'] ?? false)
                <span class="badge badge-success px-5 py-3" style="font-size: 1.1rem;">
                    <i class="fas fa-check-circle"></i> Balance Sheet is Balanced
                </span>
            @else
                <span class="badge badge-danger px-5 py-3" style="font-size: 1.1rem;">
                    <i class="fas fa-exclamation-triangle"></i>
                    Difference: ৳{{ number_format($balanceSheet['difference'] ?? 0, 2) }}
                </span>
            @endif
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
