@extends('adminlte::page')

@section('title', 'Collection Dashboard')

@section('content_header')
    <h1>Collection Dashboard</h1>
@stop

@section('content')
<div class="row">
    <!-- Performance Summary -->
    <div class="col-md-8">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">Collection Performance</h3>
            </div>
            <div class="card-body">
                <canvas id="collection-performance-chart" height="300"></canvas>
            </div>
        </div>
    </div>
    
    <!-- Key Metrics -->
    <div class="col-md-4">
        <div class="card card-success">
            <div class="card-header">
                <h3 class="card-title">Key Metrics</h3>
            </div>
            <div class="card-body">
                <div class="info-box bg-light">
                    <div class="info-box-content">
                        <span class="info-box-text text-center text-muted">Total Outstanding</span>
                        <span class="info-box-number text-center text-muted mb-0">{{ number_format($totalOutstanding, 2) }}</span>
                    </div>
                </div>
                <div class="info-box bg-light">
                    <div class="info-box-content">
                        <span class="info-box-text text-center text-muted">Collection Rate</span>
                        <span class="info-box-number text-center text-muted mb-0">{{ number_format($collectionRate, 2) }}%</span>
                    </div>
                </div>
                <div class="info-box bg-light">
                    <div class="info-box-content">
                        <span class="info-box-text text-center text-muted">Average Days to Pay</span>
                        <span class="info-box-number text-center text-muted mb-0">{{ $avgDaysToPay }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Collection Activities -->
<div class="row">
    <div class="col-md-6">
        <div class="card card-info">
            <div class="card-header">
                <h3 class="card-title">Recent Collections</h3>
            </div>
            <div class="card-body">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Customer</th>
                            <th>Amount</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentCollections as $collection)
                        <tr>
                            <td>{{ $collection->customer->name }}</td>
                            <td>{{ number_format($collection->amount, 2) }}</td>
                            <td>{{ $collection->created_at->format('d M Y') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card card-warning">
            <div class="card-header">
                <h3 class="card-title">Upcoming Due Dates</h3>
            </div>
            <div class="card-body">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Customer</th>
                            <th>Due Amount</th>
                            <th>Due Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($upcomingDueDates as $tracking)
                        <tr>
                            <td>{{ $tracking->customer->name }}</td>
                            <td>{{ number_format($tracking->customer->outstanding_balance, 2) }}</td>
                            <td>{{ $tracking->due_date->format('d M Y') }}</td>
                            <td>
                                <a href="{{ route('debt-collection.edit-tracking', $tracking->customer_id) }}" class="btn btn-xs btn-info">
                                    <i class="fas fa-edit"></i>
                                </a>
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
    // Sample chart data - replace with dynamic data from your controller
    const ctx = document.getElementById('collection-performance-chart').getContext('2d');
    const chart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: {!! json_encode($performanceLabels) !!},
            datasets: [{
                label: 'Collections',
                backgroundColor: 'rgba(60,141,188,0.2)',
                borderColor: 'rgba(60,141,188,1)',
                pointRadius: 3,
                pointColor: '#3b8bba',
                pointStrokeColor: 'rgba(60,141,188,1)',
                pointHighlightFill: '#fff',
                pointHighlightStroke: 'rgba(60,141,188,1)',
                data: {!! json_encode($collectionData) !!}
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
});
</script>
@stop
