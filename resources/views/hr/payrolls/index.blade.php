@extends('layouts.modern-admin')

@section('title', 'Payroll')
@section('page_title', 'Payroll')

@section('page_content')
@if($hasFilter)
    <div class="alert alert-info">
        Showing payrolls for {{ $periodStart }} to {{ $periodEnd }}.
    </div>
@else
    <div class="alert alert-info">
        Showing latest payrolls. Use the filter to view a specific period.
    </div>
@endif

<div class="card modern-card mb-3">
    <div class="card-body">
        <form method="GET" action="{{ route('hr.payrolls.index') }}" class="form-inline">
            <div class="form-group mr-2">
                <label class="mr-2">Period Start</label>
                <input type="date" name="period_start" class="form-control" value="{{ $periodStart }}">
            </div>
            <div class="form-group mr-2">
                <label class="mr-2">Period End</label>
                <input type="date" name="period_end" class="form-control" value="{{ $periodEnd }}">
            </div>
            <button type="submit" class="btn btn-primary">Filter</button>
        </form>
    </div>
</div>

<div class="card modern-card mb-3">
    <div class="card-header modern-header">
        <h3 class="card-title"><i class="fas fa-cogs"></i> Generate Payroll</h3>
    </div>
    <form method="POST" action="{{ route('hr.payrolls.run') }}">
        @csrf
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Period Start</label>
                        <input type="date" name="period_start" class="form-control" value="{{ $periodStart }}" required>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Period End</label>
                        <input type="date" name="period_end" class="form-control" value="{{ $periodEnd }}" required>
                    </div>
                </div>
                <div class="col-md-4 d-flex align-items-center">
                    <div class="form-check mt-4">
                        <input class="form-check-input" type="checkbox" name="deduct_advances" id="deduct_advances" checked>
                        <label class="form-check-label" for="deduct_advances">Deduct advances</label>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-success">Generate</button>
        </div>
    </form>
</div>

<div class="card modern-card">
    <div class="card-header modern-header">
        <h3 class="card-title"><i class="fas fa-money-check-alt"></i> Payroll List</h3>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table mb-0">
                <thead>
                    <tr>
                        <th>Employee</th>
                        <th>Gross</th>
                        <th>Deduction</th>
                        <th>Advance Deduction</th>
                        <th>Net Pay</th>
                        <th>Status</th>
                        <th width="120">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($payrolls as $payroll)
                        <tr>
                            <td>{{ $payroll->employee->name }}</td>
                            <td>৳{{ number_format($payroll->gross_salary, 2) }}</td>
                            <td>৳{{ number_format($payroll->deduction_amount, 2) }}</td>
                            <td>৳{{ number_format($payroll->advance_deduction, 2) }}</td>
                            <td>৳{{ number_format($payroll->net_pay, 2) }}</td>
                            <td>
                                <span class="badge {{ $payroll->status === 'paid' ? 'badge-success' : 'badge-warning' }}">
                                    {{ ucfirst($payroll->status) }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('hr.payrolls.show', $payroll) }}" class="btn btn-sm btn-info">View</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-3">No payrolls found for this period.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@stop
