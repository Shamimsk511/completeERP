@extends('adminlte::page')

@section('title', 'Payables Aging Report')

@section('content_header')
    <h1>Payables Aging Report</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Aging Report as of {{ $referenceDate->format('M d, Y') }}</h3>
            <div class="card-tools">
                <form action="{{ route('payables.reports.aging') }}" method="GET" class="form-inline">
                    <div class="input-group input-group-sm">
                        <div class="input-group-prepend">
                            <span class="input-group-text">Reference Date</span>
                        </div>
                        <input type="date" name="reference_date" class="form-control" value="{{ $referenceDate->format('Y-m-d') }}">
                        <div class="input-group-append">
                            <button type="submit" class="btn btn-default">Update</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Payee</th>
                            <th>Type</th>
                            <th class="text-right">Current</th>
                            <th class="text-right">1-30 Days</th>
                            <th class="text-right">31-60 Days</th>
                            <th class="text-right">61-90 Days</th>
                            <th class="text-right">Over 90 Days</th>
                            <th class="text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($agingData as $data)
                            <tr>
                                <td>
                                    <a href="{{ route('payees.show', $data['id']) }}">{{ $data['name'] }}</a>
                                </td>
                                <td>{{ ucfirst($data['type']) }}</td>
                                <td class="text-right">${{ number_format($data['current'], 2) }}</td>
                                <td class="text-right">${{ number_format($data['1-30'], 2) }}</td>
                                <td class="text-right">${{ number_format($data['31-60'], 2) }}</td>
                                <td class="text-right">${{ number_format($data['61-90'], 2) }}</td>
                                <td class="text-right">${{ number_format($data['over_90'], 2) }}</td>
                                <td class="text-right">${{ number_format($data['total'], 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center">No payables found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr class="font-weight-bold">
                            <td colspan="2">TOTAL</td>
                            <td class="text-right">${{ number_format($totals['current'], 2) }}</td>
                            <td class="text-right">${{ number_format($totals['1-30'], 2) }}</td>
                            <td class="text-right">${{ number_format($totals['31-60'], 2) }}</td>
                            <td class="text-right">${{ number_format($totals['61-90'], 2) }}</td>
                            <td class="text-right">${{ number_format($totals['over_90'], 2) }}</td>
                            <td class="text-right">${{ number_format($totals['total'], 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
        <div class="card-footer">
            <a href="{{ route('payables.reports.detailed-aging') }}" class="btn btn-info">
                <i class="fas fa-list"></i> View Detailed Aging Report
            </a>
            <button onclick="window.print()" class="btn btn-default">
                <i class="fas fa-print"></i> Print Report
            </button>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Aging Summary</h3>
                </div>
                <div class="card-body">
                    <canvas id="aging-chart" height="200"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Aging Analysis</h3>
                </div>
                <div class="card-body">
                    <p>
                        <strong>Total Payables:</strong> ${{ number_format($totals['total'], 2) }}
                    </p>
                    <p>
                        <strong>Current Payables:</strong> ${{ number_format($totals['current'], 2) }}
                        ({{ $totals['total'] > 0 ? round(($totals['current'] / $totals['total']) * 100, 2) : 0 }}%)
                    </p>
                    <p>
                        <strong>Overdue Payables:</strong> ${{ number_format($totals['total'] - $totals['current'], 2) }}
                        ({{ $totals['total'] > 0 ? round((($totals['total'] - $totals['current']) / $totals['total']) * 100, 2) : 0 }}%)
                    </p>
                    <p>
                        <strong>Severely Overdue (>90 days):</strong> ${{ number_format($totals['over_90'], 2) }}
                        ({{ $totals['total'] > 0 ? round(($totals['over_90'] / $totals['total']) * 100, 2) : 0 }}%)
                    </p>
                </div>
            </div>
        </div>
    </div>
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        $(function() {
            // Create aging chart
            var ctx = document.getElementById('aging-chart').getContext('2d');
            var agingChart = new Chart(ctx, {
                type: 'pie',
                data: {
                    labels: ['Current', '1-30 Days', '31-60 Days', '61-90 Days', 'Over 90 Days'],
                    datasets: [{
                        data: [
                            {{ $totals['current'] }},
                            {{ $totals['1-30'] }},
                            {{ $totals['31-60'] }},
                            {{ $totals['61-90'] }},
                            {{ $totals['over_90'] }}
                        ],
                        backgroundColor: [
                            '#28a745', // Green for current
                            '#17a2b8', // Blue for 1-30
                            '#ffc107', // Yellow for 31-60
                            '#fd7e14', // Orange for 61-90
                            '#dc3545'  // Red for over 90
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });
        });
    </script>
@stop
