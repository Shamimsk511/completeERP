@extends('adminlte::page')

@section('title', 'Import Customers')

@section('content_header')
    <h1>Import Customers</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Upload Excel File</h3>
        </div>
        <div class="card-body">
            @if ($message = Session::get('error'))
                <div class="alert alert-danger">
                    <p>{{ $message }}</p>
                </div>
            @endif

            <form action="{{ route('customers.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="form-group">
                    <label for="file">Excel File</label>
                    <input type="file" name="file" class="form-control @error('file') is-invalid @enderror" required>
                    @error('file')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
                <div class="form-group">
                    <button type="submit" class="btn btn-primary">Import</button>
                    <a href="{{ route('customers.index') }}" class="btn btn-secondary">Back</a>
                </div>
            </form>

            <div class="mt-4">
                <h4>Instructions:</h4>
                <ol>
                    <li>Download the <a href="{{ route('customers.export.template') }}">template file</a> (optional)</li>
                    <li>Fill in your customer data</li>
                    <li>Upload the file using the form above</li>
                </ol>
                <div class="alert alert-info">
                    <strong>Note:</strong> The Excel file should have the following columns:
                    <ul>
                        <li>name (required)</li>
                        <li>phone (required)</li>
                        <li>address (optional)</li>
                        <li>opening_balance (optional, default: 0)</li>
                        <li>outstanding_balance (optional, default: same as opening_balance)</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
@stop
