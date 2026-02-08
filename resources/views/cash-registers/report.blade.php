@extends('layouts.modern-admin')

@section('title', 'Cash Register Report')
@section('page_title', 'Cash Register Report')

@section('header_actions')
    <a href="{{ route('cash-registers.index') }}" class="btn modern-btn modern-btn-secondary">
        <i class="fas fa-arrow-left"></i> Back
    </a>
@stop

@section('page_content')
    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form action="{{ route('cash-registers.report') }}" method="GET" class="row">
                <div class="col-md-3">
                    <label>From Date</label>
                    <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                </div>
                <div class="col-md-3">
                    <label>To Date</label>
                    <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                </div>
                <div class="col-md-3">
                    <label>User</label>
                    <select name="user_id" class="form-control">
                        <option value="">All Users</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                {{ $user->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-filter"></i> Filter
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Summary -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body text-center">
                    <h3>{{ $summary['count'] }}</h3>
                    <p class="mb-0">Total Sessions</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body text-center">
                    <h3>৳{{ number_format($summary['total_opening'], 2) }}</h3>
                    <p class="mb-0">Total Opening</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body text-center">
                    <h3>৳{{ number_format($summary['total_closing'], 2) }}</h3>
                    <p class="mb-0">Total Closing</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card {{ $summary['total_variance'] >= 0 ? 'bg-success' : 'bg-danger' }} text-white">
                <div class="card-body text-center">
                    <h3>৳{{ number_format($summary['total_variance'], 2) }}</h3>
                    <p class="mb-0">Total Variance</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Report Table -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Closed Registers</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped mb-0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>User</th>
                            <th>Opened</th>
                            <th>Closed</th>
                            <th>Opening</th>
                            <th>Closing</th>
                            <th>Variance</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($registers as $register)
                        <tr>
                            <td>{{ $register->id }}</td>
                            <td>{{ $register->user->name ?? 'N/A' }}</td>
                            <td>{{ $register->opened_at->format('d M Y, h:i A') }}</td>
                            <td>{{ $register->closed_at->format('d M Y, h:i A') }}</td>
                            <td>৳{{ number_format($register->opening_balance, 2) }}</td>
                            <td>৳{{ number_format($register->actual_closing_balance, 2) }}</td>
                            <td class="{{ $register->variance >= 0 ? 'text-success' : 'text-danger' }}">
                                ৳{{ number_format($register->variance, 2) }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                No closed registers found
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($registers->hasPages())
        <div class="card-footer">
            {{ $registers->withQueryString()->links() }}
        </div>
        @endif
    </div>
@stop
