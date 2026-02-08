@extends('layouts.modern-admin')

@section('title', 'Payroll Details')
@section('page_title', 'Payroll Details')

@section('header_actions')
    <a href="{{ route('hr.payrolls.print', $payroll) }}" target="_blank" class="btn modern-btn modern-btn-secondary">
        <i class="fas fa-print"></i> Print
    </a>
@stop

@section('page_content')
<div class="row">
    <div class="col-md-6">
        <div class="card modern-card">
            <div class="card-header modern-header">
                <h3 class="card-title"><i class="fas fa-user"></i> Employee</h3>
            </div>
            <div class="card-body">
                <p><strong>Name:</strong> {{ $payroll->employee->name }}</p>
                <p><strong>Period:</strong> {{ $payroll->period_start->format('d M, Y') }} - {{ $payroll->period_end->format('d M, Y') }}</p>
                <p><strong>Status:</strong> {{ ucfirst($payroll->status) }}</p>
                <p><strong>Paid At:</strong> {{ $payroll->paid_at ? $payroll->paid_at->format('d M, Y') : '-' }}</p>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card modern-card">
            <div class="card-header modern-header">
                <h3 class="card-title"><i class="fas fa-calculator"></i> Salary Summary</h3>
            </div>
            <div class="card-body">
                <p><strong>Basic Salary:</strong> ৳{{ number_format($payroll->basic_salary, 2) }}</p>
                <p><strong>Present Days:</strong> {{ $payroll->present_days }}</p>
                <p><strong>Absent Days:</strong> {{ $payroll->absent_days }}</p>
                <p><strong>Paid Absent Days:</strong> {{ $payroll->paid_absent_days }}</p>
                <p><strong>Bonus:</strong> ৳{{ number_format($payroll->bonus_amount, 2) }}</p>
                <p><strong>Other Bonus:</strong> ৳{{ number_format($payroll->other_bonus_amount, 2) }}</p>
                <p><strong>Increment:</strong> ৳{{ number_format($payroll->increment_amount, 2) }}</p>
                <p><strong>Deduction:</strong> ৳{{ number_format($payroll->deduction_amount, 2) }}</p>
                <p><strong>Advance Deduction:</strong> ৳{{ number_format($payroll->advance_deduction, 2) }}</p>
                <p><strong>Gross Salary:</strong> ৳{{ number_format($payroll->gross_salary, 2) }}</p>
                <p><strong>Net Pay:</strong> ৳{{ number_format($payroll->net_pay, 2) }}</p>
            </div>
        </div>
    </div>
</div>

@if($payroll->status !== 'paid')
    <div class="card modern-card">
        <div class="card-header modern-header">
            <h3 class="card-title"><i class="fas fa-check"></i> Mark as Paid</h3>
        </div>
        <form method="POST" action="{{ route('hr.payrolls.pay', $payroll) }}">
            @csrf
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Paid At</label>
                            <input type="date" name="paid_at" class="form-control" value="{{ now()->toDateString() }}" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Cash/Bank Account</label>
                            <select name="cash_account_id" class="form-control" required>
                                <option value="">-- Select Account --</option>
                                @foreach($cashAccounts as $account)
                                    <option value="{{ $account->id }}">{{ $account->name }} ({{ $account->account_type }})</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-success">Mark Paid</button>
            </div>
        </form>
    </div>
@endif
@stop
