@extends('adminlte::page')

@section('title', 'Import Status')

@section('content_header')
    <h1>Import Status</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Product Import Progress</h3>
        </div>
        <div class="card-body">
            @if($batch)
                <div class="progress mb-3">
                    <div class="progress-bar" role="progressbar" style="width: {{ $batch->progress() }}%;" 
                         aria-valuenow="{{ $batch->progress() }}" aria-valuemin="0" aria-valuemax="100">
                        {{ $batch->progress() }}%
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="info-box">
                            <span class="info-box-icon bg-info"><i class="far fa-clock"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Status</span>
                                <span class="info-box-number">{{ $batch->finished() ? 'Completed' : 'Processing' }}</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="info-box">
                            <span class="info-box-icon bg-success"><i class="fas fa-tasks"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Jobs Processed</span>
                                <span class="info-box-number">{{ $batch->processedJobs() }} / {{ $batch->totalJobs }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                @if($batch->finished())
                    <div class="alert alert-success">
                        <h5><i class="icon fas fa-check"></i> Import Completed!</h5>
                        <p>Your product import has been successfully processed.</p>
                        <a href="{{ route('products.index') }}" class="btn btn-primary">View Products</a>
                    </div>
                @elseif($batch->cancelled())
                    <div class="alert alert-warning">
                        <h5><i class="icon fas fa-exclamation-triangle"></i> Import Cancelled</h5>
                        <p>The import process was cancelled.</p>
                    </div>
                @elseif($batch->failedJobs)
                    <div class="alert alert-danger">
                        <h5><i class="icon fas fa-ban"></i> Import Failed</h5>
                        <p>{{ $batch->failedJobs }} jobs failed during the import process.</p>
                    </div>
                @else
                    <div class="text-center">
                        <p>The import is being processed. This page will refresh automatically.</p>
                    </div>
                    
                    <script>
                        setTimeout(function() {
                            window.location.reload();
                        }, 5000);
                    </script>
                @endif
                
            @else
                <div class="alert alert-info">
                    <h5><i class="icon fas fa-info"></i> No Active Import</h5>
                    <p>There is no active import process to track.</p>
                    <a href="{{ route('products.import.form') }}" class="btn btn-primary">Start New Import</a>
                </div>
            @endif
        </div>
    </div>
@stop
