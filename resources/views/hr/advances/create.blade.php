@extends('layouts.modern-admin')

@section('title', 'Add Advance')
@section('page_title', 'Add Employee Advance')

@section('page_content')
<div class="card modern-card">
    <div class="card-header modern-header">
        <h3 class="card-title"><i class="fas fa-hand-holding-usd"></i> New Advance</h3>
    </div>
    <form method="POST" action="{{ route('hr.advances.store') }}">
        @csrf
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Employee</label>
                        <select name="employee_id" class="form-control" required>
                            <option value="">-- Select Employee --</option>
                            @foreach($employees as $employee)
                                <option value="{{ $employee->id }}" {{ old('employee_id') == $employee->id ? 'selected' : '' }}>
                                    {{ $employee->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Amount</label>
                        <input type="number" step="0.01" name="amount" class="form-control" value="{{ old('amount') }}" required>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Date</label>
                        <input type="date" name="date" class="form-control" value="{{ old('date', now()->toDateString()) }}" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Cash/Bank Account</label>
                        <select name="cash_account_id" class="form-control" required>
                            <option value="">-- Select Account --</option>
                            @foreach($cashAccounts as $account)
                                <option value="{{ $account->id }}" {{ old('cash_account_id') == $account->id ? 'selected' : '' }}>
                                    {{ $account->name }} ({{ $account->account_type }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Notes</label>
                        <input type="text" name="notes" class="form-control" value="{{ old('notes') }}">
                    </div>
                </div>
            </div>
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-success">Save</button>
            <a href="{{ route('hr.advances.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@stop
