@extends('adminlte::page')

@section('title', 'Customer Payment History')

@section('content_header')
    <h1>Payment History: {{ $customer->name }}</h1>
@stop

@section('content')
<div class="row">
    <div class="col-md-4">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">Customer Information</h3>
            </div>
            <div class="card-body">
                <p><strong>Name:</strong> {{ $customer->name }}</p>
                <p><strong>Phone:</strong> {{ $customer->phone }}</p>
                <p><strong>Email:</strong> {{ $customer->email }}</p>
                <p><strong>Current Balance:</strong> {{ number_format($customer->outstanding_balance, 2) }}</p>
                <p><strong>Payment Terms:</strong> {{ $customer->payment_terms ?? 'Not specified' }}</p>
                
                <hr>
                <h5>Collection Stats</h5>
                <p><strong>Average Days to Pay:</strong> {{ $avgDaysToPay }}</p>
                <p><strong>Payment Reliability:</strong> 
                    <span class="badge badge-{{ $paymentReliability >= 80 ? 'success' : ($paymentReliability >= 50 ? 'warning' : 'danger') }}">
                        {{ $paymentReliability }}%
                    </span>
                </p>
                
                <a href="{{ route('debt-collection.index') }}" class="btn btn-default mt-3">
                    <i class="fas fa-arrow-left"></i> Back to List
                </a>
                <a href="{{ route('debt-collection.call-history', $customer->id) }}" class="btn btn-info mt-3">
                    <i class="fas fa-phone"></i> Call History
                </a>
            </div>
        </div>
    </div>
    
    <div class="col-md-8">
        <div class="card card-success">
            <div class="card-header">
                <h3 class="card-title">Payment Timeline</h3>
            </div>
            <div class="card-body">
                <div class="chart">
                    <canvas id="payment-timeline-chart" height="300"></canvas>
                </div>
            </div>
        </div>
        
        <div class="card card-info">
            <div class="card-header">
                <h3 class="card-title">Payment Records</h3>
            </div>
            <div class="card-body table-responsive p-0">
                <table class="table table-hover text-nowrap">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Invoice #</th>
                            <th>Amount</th>
                            <th>Due Date</th>
                            <th>Payment Date</th>
                            <th>Days Late</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($paymentRecords as $record)
                        <tr class="{{ $record->days_late > 0 ? ($record->days_late > 30 ? 'table-danger' : 'table-warning') : 'table-success' }}">
                            <td>{{ $record->invoice_date->format('d M Y') }}</td>
                            <td>{{ $record->invoice_number }}</td>
                            <td>{{ number_format($record->amount, 2) }}</td>
                            <td>{{ $record->due_date->format('d M Y') }}</td>
                            <td>{{ $record->payment_date ? $record->payment_date->format('d M Y') : 'Unpaid' }}</td>
                            <td>
                                @if($record->payment_date)
                                    @if($record->days_late > 0)
                                        <span class="badge badge-danger">{{ $record->days_late }} days late</span>
                                    @else
                                        <span class="badge badge-success">On time</span>
                                    @endif
                                @else
                                    @if(now()->gt($record->due_date))
                                        <span class="badge badge-danger">{{ now()->diffInDays($record->due_date) }} days overdue</span>
                                    @else
                                        <span class="badge badge-info">Not yet due</span>
                                    @endif
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
$(function() {
    const ctx = document.getElementById('payment-timeline-chart').getContext('2d');
    const chart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: {!! json_encode($timelineLabels) !!},
            datasets: [
                {
                    label: 'Invoiced Amount',
                    backgroundColor: 'rgba(210, 214, 222, 0.2)',
                    borderColor: 'rgba(210, 214, 222, 1)',
                    pointRadius: 4,
                    pointColor: 'rgba(210, 214, 222, 1)',
                    pointStrokeColor: '#c1c7d1',
                    pointHighlightFill: '#fff',
                    pointHighlightStroke: 'rgba(220,220,220,1)',
                    data: {!! json_encode($invoicedData) !!}
                },
                {
                    label: 'Payment Amount',
                    backgroundColor: 'rgba(60,141,188,0.2)',
                    borderColor: 'rgba(60,141,188,1)',
                    pointRadius: 4,
                    pointColor: '#3b8bba',
                    pointStrokeColor: 'rgba(60,141,188,1)',
                    pointHighlightFill: '#fff',
                    pointHighlightStroke: 'rgba(60,141,188,1)',
                    data: {!! json_encode($paymentData) !!}
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });
});
</script>
@stop
