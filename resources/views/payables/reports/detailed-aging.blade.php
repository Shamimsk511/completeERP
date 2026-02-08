@extends('adminlte::page')

@section('title', 'Detailed Payables Aging Report')

@section('content_header')
    <h1>Detailed Payables Aging Report</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Detailed Aging Report as of {{ $referenceDate->format('M d, Y') }}</h3>
            <div class="card-tools">
                <form action="{{ route('aging.detailed') }}" method="GET" class="form-inline">
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
            @forelse($agingData as $data)
                <div class="payee-section mb-4">
                    <h5>{{ $data['payee']->name }} ({{ ucfirst($data['payee']->type) }})</h5>
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Reference</th>
                                    <th>Category</th>
                                    <th class="text-right">Current</th>
                                    <th class="text-right">1-30 Days</th>
                                    <th class="text-right">31-60 Days</th>
                                    <th class="text-right">61-90 Days</th>
                                    <th class="text-right">Over 90 Days</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($data['transactions'] as $transaction)
                                    <tr>
                                        <td>{{ $transaction['date'] }}</td>
                                        <td>{{ $transaction['reference'] ?: 'N/A' }}</td>
                                        <td>{{ ucfirst($transaction['category']) }}</td>
                                        <td class="text-right">${{ number_format($transaction['current'], 2) }}</td>
                                        <td class="text-right">${{ number_format($transaction['1-30'], 2) }}</td>
                                        <td class="text-right">${{ number_format($transaction['31-60'], 2) }}</td>
                                        <td class="text-right">${{ number_format($transaction['61-90'], 2) }}</td>
                                        <td class="text-right">${{ number_format($transaction['over_90'], 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="font-weight-bold">
                                    <td colspan="3">Subtotal</td>
                                    <td class="text-right">${{ number_format($data['totals']['current'], 2) }}</td>
                                    <td class="text-right">${{ number_format($data['totals']['1-30'], 2) }}</td>
                                    <td class="text-right">${{ number_format($data['totals']['31-60'], 2) }}</td>
                                    <td class="text-right">${{ number_format($data['totals']['61-90'], 2) }}</td>
                                    <td class="text-right">${{ number_format($data['totals']['over_90'], 2) }}</td>
                                </tr>
                                <tr class="font-weight-bold">
                                    <td colspan="3">Total</td>
                                    <td colspan="5" class="text-right">${{ number_format($data['totals']['total'], 2) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            @empty
                <div class="alert alert-info">
                    No payables found for the selected date range.
                </div>
            @endforelse

            <div class="grand-total mt-4">
                <h4>Grand Totals</h4>
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th class="text-right">Current</th>
                                <th class="text-right">1-30 Days</th>
                                <th class="text-right">31-60 Days</th>
                                <th class="text-right">61-90 Days</th>
                                <th class="text-right">Over 90 Days</th>
                                <th class="text-right">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="font-weight-bold">
                                <td class="text-right">${{ number_format($totals['current'], 2) }}</td>
                                <td class="text-right">${{ number_format($totals['1-30'], 2) }}</td>
                                <td class="text-right">${{ number_format($totals['31-60'], 2) }}</td>
                                <td class="text-right">${{ number_format($totals['61-90'], 2) }}</td>
                                <td class="text-right">${{ number_format($totals['over_90'], 2) }}</td>
                                <td class="text-right">${{ number_format($totals['total'], 2) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="card-footer">
            <a href="{{ route('aging.index') }}" class="btn btn-default">
                <i class="fas fa-arrow-left"></i> Back to Summary Report
            </a>
            <button onclick="window.print()" class="btn btn-default">
                <i class="fas fa-print"></i> Print Report
            </button>
        </div>
    </div>
@stop
