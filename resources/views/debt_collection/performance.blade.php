@extends('adminlte::page')

@section('title', 'Collection Performance')

@section('content_header')
    <h1>Collection Performance Metrics</h1>
@stop

@section('content')
<div class="row">
    <!-- Performance KPIs -->
    <div class="col-md-3">
        <div class="info-box bg-gradient-success">
            <span class="info-box-icon"><i class="fas fa-money-bill"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Total Collected</span>
                <span class="info-box-number">৳{{ number_format($performance['total_collections'] ?? 0, 2) }}</span>
                <div class="progress">
                    <div class="progress-bar" style="width: {{ $performance['collection_rate'] ?? 0 }}%"></div>
                </div>
                <span class="progress-description">
                    {{ number_format($performance['collection_rate'] ?? 0, 1) }}% Collection Rate
                </span>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="info-box bg-gradient-info">
            <span class="info-box-icon"><i class="fas fa-phone"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Total Calls</span>
                <span class="info-box-number">{{ $performance['total_calls'] ?? 0 }}</span>
                <div class="progress">
                    <div class="progress-bar" style="width: {{ $performance['call_success_rate'] ?? 0 }}%"></div>
                </div>
                <span class="progress-description">
                    {{ number_format($performance['call_success_rate'] ?? 0, 1) }}% Success Rate
                </span>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="info-box bg-gradient-warning">
            <span class="info-box-icon"><i class="fas fa-calendar-check"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Avg Resolution Days</span>
                <span class="info-box-number">{{ $performance['avg_resolution_days'] ?? 0 }}</span>
                <div class="progress">
                    <div class="progress-bar" style="width: {{ min(100, (20 / max(1, $performance['avg_resolution_days'] ?? 20)) * 100) }}%"></div>
                </div>
                <span class="progress-description">
                    Target: 20 days
                </span>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="info-box bg-gradient-primary">
            <span class="info-box-icon"><i class="fas fa-phone-alt"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Successful Calls</span>
                <span class="info-box-number">{{ $performance['successful_calls'] ?? 0 }}</span>
                <div class="progress">
                    <div class="progress-bar" style="width: {{ $performance['call_success_rate'] ?? 0 }}%"></div>
                </div>
                <span class="progress-description">
                    Out of {{ $performance['total_calls'] ?? 0 }} total calls
                </span>
            </div>
        </div>
    </div>
</div>

<!-- Monthly Performance Trends -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Monthly Collection Trends</h3>
        <div class="card-tools">
            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                <i class="fas fa-minus"></i>
            </button>
        </div>
    </div>
    <div class="card-body">
        <div class="chart">
            <canvas id="monthly-trends-chart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
        </div>
    </div>
</div>

<div class="row">
    <!-- Call Statistics -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Call Statistics Overview</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="description-block border-right">
                            <span class="description-percentage text-success">
                                <i class="fas fa-caret-up"></i> {{ number_format($performance['call_success_rate'] ?? 0, 1) }}%
                            </span>
                            <h5 class="description-header">{{ $performance['successful_calls'] ?? 0 }}</h5>
                            <span class="description-text">SUCCESSFUL CALLS</span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="description-block">
                            <span class="description-percentage text-warning">
                                <i class="fas fa-caret-down"></i> {{ number_format(100 - ($performance['call_success_rate'] ?? 0), 1) }}%
                            </span>
                            <h5 class="description-header">{{ ($performance['total_calls'] ?? 0) - ($performance['successful_calls'] ?? 0) }}</h5>
                            <span class="description-text">MISSED/FAILED CALLS</span>
                        </div>
                    </div>
                </div>
                
                <!-- Call Success Rate Progress -->
                <div class="progress-group">
                    Call Success Rate
                    <span class="float-right"><b>{{ $performance['successful_calls'] ?? 0 }}</b>/{{ $performance['total_calls'] ?? 0 }}</span>
                    <div class="progress progress-sm">
                        <div class="progress-bar bg-primary" style="width: {{ $performance['call_success_rate'] ?? 0 }}%"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Collection Performance Summary -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Collection Performance Summary</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body table-responsive p-0">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Metric</th>
                            <th>Value</th>
                            <th>Performance</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Collection Rate</td>
                            <td>{{ number_format($performance['collection_rate'] ?? 0, 1) }}%</td>
                            <td>
                                <span class="badge badge-{{ ($performance['collection_rate'] ?? 0) >= 75 ? 'success' : (($performance['collection_rate'] ?? 0) >= 50 ? 'warning' : 'danger') }}">
                                    {{ ($performance['collection_rate'] ?? 0) >= 75 ? 'Excellent' : (($performance['collection_rate'] ?? 0) >= 50 ? 'Good' : 'Needs Improvement') }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td>Total Collections</td>
                            <td>৳{{ number_format($performance['total_collections'] ?? 0, 2) }}</td>
                            <td>
                                <span class="badge badge-info">Current Month</span>
                            </td>
                        </tr>
                        <tr>
                            <td>Average Resolution</td>
                            <td>{{ $performance['avg_resolution_days'] ?? 0 }} days</td>
                            <td>
                                <span class="badge badge-{{ ($performance['avg_resolution_days'] ?? 20) <= 15 ? 'success' : (($performance['avg_resolution_days'] ?? 20) <= 25 ? 'warning' : 'danger') }}">
                                    {{ ($performance['avg_resolution_days'] ?? 20) <= 15 ? 'Fast' : (($performance['avg_resolution_days'] ?? 20) <= 25 ? 'Average' : 'Slow') }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td>Call Success Rate</td>
                            <td>{{ number_format($performance['call_success_rate'] ?? 0, 1) }}%</td>
                            <td>
                                <span class="badge badge-{{ ($performance['call_success_rate'] ?? 0) >= 70 ? 'success' : (($performance['call_success_rate'] ?? 0) >= 50 ? 'warning' : 'danger') }}">
                                    {{ ($performance['call_success_rate'] ?? 0) >= 70 ? 'High' : (($performance['call_success_rate'] ?? 0) >= 50 ? 'Medium' : 'Low') }}
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Monthly Trends Table -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Monthly Collection History</h3>
    </div>
    <div class="card-body table-responsive">
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Month</th>
                    <th>Collected Amount (৳)</th>
                    <th>Level</th>
                    <th>Trend</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $months = $performance['monthly_trends'] ?? [];
                    $previousValue = null;
                    $maxValue = !empty($months) ? max($months) : 0;
                @endphp
                @foreach($months as $month => $rate)
                @php
                    $relative = $maxValue > 0 ? ($rate / $maxValue) * 100 : 0;
                @endphp
                <tr>
                    <td><strong>{{ $month }}</strong></td>
                    <td>
                        <div class="progress progress-xs progress-striped active">
                            <div class="progress-bar bg-{{ $relative >= 75 ? 'success' : ($relative >= 40 ? 'warning' : 'danger') }}" style="width: {{ $relative }}%"></div>
                        </div>
                        ৳{{ number_format($rate, 2) }}
                    </td>
                    <td>
                        <span class="badge badge-{{ $relative >= 75 ? 'success' : ($relative >= 40 ? 'warning' : 'danger') }}">
                            {{ $relative >= 75 ? 'High' : ($relative >= 40 ? 'Medium' : 'Low') }}
                        </span>
                    </td>
                    <td>
                        @if($previousValue !== null)
                            @php $trend = $rate - $previousValue; @endphp
                            <span class="text-{{ $trend > 0 ? 'success' : ($trend < 0 ? 'danger' : 'muted') }}">
                                <i class="fas fa-{{ $trend > 0 ? 'arrow-up' : ($trend < 0 ? 'arrow-down' : 'minus') }}"></i>
                                {{ $trend > 0 ? '+' : '' }}৳{{ number_format($trend, 2) }}
                            </span>
                        @else
                            <span class="text-muted">-</span>
                        @endif
                        @php $previousValue = $rate; @endphp
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<!-- Performance Insights -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Performance Insights & Recommendations</h3>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-4">
                <div class="info-box">
                    <span class="info-box-icon bg-{{ ($performance['collection_rate'] ?? 0) >= 75 ? 'success' : 'warning' }}">
                        <i class="fas fa-{{ ($performance['collection_rate'] ?? 0) >= 75 ? 'thumbs-up' : 'exclamation-triangle' }}"></i>
                    </span>
                    <div class="info-box-content">
                        <span class="info-box-text">Collection Status</span>
                        <span class="info-box-number">
                            {{ ($performance['collection_rate'] ?? 0) >= 75 ? 'On Target' : 'Below Target' }}
                        </span>
                        <div class="progress">
                            <div class="progress-bar" style="width: {{ $performance['collection_rate'] ?? 0 }}%"></div>
                        </div>
                        <span class="progress-description">
                            Target: 75% collection rate
                        </span>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="info-box">
                    <span class="info-box-icon bg-{{ ($performance['call_success_rate'] ?? 0) >= 70 ? 'success' : 'warning' }}">
                        <i class="fas fa-{{ ($performance['call_success_rate'] ?? 0) >= 70 ? 'phone' : 'phone-slash' }}"></i>
                    </span>
                    <div class="info-box-content">
                        <span class="info-box-text">Call Efficiency</span>
                        <span class="info-box-number">
                            {{ ($performance['call_success_rate'] ?? 0) >= 70 ? 'Efficient' : 'Needs Work' }}
                        </span>
                        <div class="progress">
                            <div class="progress-bar" style="width: {{ $performance['call_success_rate'] ?? 0 }}%"></div>
                        </div>
                        <span class="progress-description">
                            Target: 70% success rate
                        </span>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="info-box">
                    <span class="info-box-icon bg-{{ ($performance['avg_resolution_days'] ?? 20) <= 15 ? 'success' : 'warning' }}">
                        <i class="fas fa-{{ ($performance['avg_resolution_days'] ?? 20) <= 15 ? 'clock' : 'hourglass-half' }}"></i>
                    </span>
                    <div class="info-box-content">
                        <span class="info-box-text">Resolution Speed</span>
                        <span class="info-box-number">
                            {{ ($performance['avg_resolution_days'] ?? 20) <= 15 ? 'Fast' : 'Slow' }}
                        </span>
                        <div class="progress">
                            <div class="progress-bar" style="width: {{ min(100, (20 / max(1, $performance['avg_resolution_days'] ?? 20)) * 100) }}%"></div>
                        </div>
                        <span class="progress-description">
                            Current: {{ $performance['avg_resolution_days'] ?? 0 }} days
                        </span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Recommendations -->
        <div class="alert alert-info">
            <h5><i class="icon fas fa-info"></i> Recommendations:</h5>
            <ul class="mb-0">
                @if(($performance['collection_rate'] ?? 0) < 75)
                    <li>Focus on improving collection strategies - current rate is below the 75% target</li>
                @endif
                @if(($performance['call_success_rate'] ?? 0) < 70)
                    <li>Review call timing and approach - success rate needs improvement</li>
                @endif
                @if(($performance['avg_resolution_days'] ?? 20) > 20)
                    <li>Implement faster follow-up procedures to reduce resolution time</li>
                @endif
                @if(($performance['collection_rate'] ?? 0) >= 75 && ($performance['call_success_rate'] ?? 0) >= 70)
                    <li>Excellent performance! Consider sharing best practices with the team</li>
                @endif
            </ul>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
.info-box-number {
    font-size: 1.5rem;
    font-weight: bold;
}
.progress {
    margin-top: 5px;
}
.chart {
    position: relative;
    height: 250px;
}
.description-block {
    text-align: center;
    padding: 15px 0;
}
.description-header {
    margin: 0;
    padding: 0;
    font-weight: 600;
    font-size: 2rem;
}
.description-text {
    text-transform: uppercase;
    font-weight: 600;
    font-size: 0.8rem;
}
.description-percentage {
    font-size: 1rem;
    font-weight: 600;
}
.progress-group {
    margin-bottom: 15px;
}
</style>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
$(function() {
    // Monthly Trends Chart
    const ctxMonthlyTrends = document.getElementById('monthly-trends-chart').getContext('2d');
    const monthlyTrendsChart = new Chart(ctxMonthlyTrends, {
        type: 'line',
        data: {
            labels: {!! json_encode(array_keys($performance['monthly_trends'] ?? [])) !!},
            datasets: [{
                label: 'Collections (৳)',
                data: {!! json_encode(array_values($performance['monthly_trends'] ?? [])) !!},
                backgroundColor: 'rgba(60, 141, 188, 0.1)',
                borderColor: 'rgba(60, 141, 188, 1)',
                pointBackgroundColor: 'rgba(60, 141, 188, 1)',
                pointBorderColor: '#fff',
                pointHoverBackgroundColor: '#fff',
                pointHoverBorderColor: 'rgba(60, 141, 188, 1)',
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            maintainAspectRatio: false,
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '৳' + value.toLocaleString();
                        }
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return 'Collected: ৳' + context.parsed.y.toLocaleString();
                        }
                    }
                }
            }
        }
    });
    
    // Auto-refresh data every 5 minutes
    setInterval(function() {
        location.reload();
    }, 300000); // 5 minutes
});
</script>
@stop
