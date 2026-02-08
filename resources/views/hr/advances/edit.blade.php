@extends('layouts.modern-admin')

@section('title', 'Edit Advance')
@section('page_title', 'Edit Employee Advance')

@section('page_content')
<div class="card modern-card">
    <div class="card-header modern-header">
        <h3 class="card-title"><i class="fas fa-edit"></i> Edit Advance</h3>
    </div>
    <form method="POST" action="{{ route('hr.advances.update', $advance) }}">
        @csrf
        @method('PUT')
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Employee</label>
                        <select name="employee_id" class="form-control" required>
                            @foreach($employees as $employee)
                                <option value="{{ $employee->id }}" {{ old('employee_id', $advance->employee_id) == $employee->id ? 'selected' : '' }}>
                                    {{ $employee->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Amount</label>
                        <input type="number" step="0.01" name="amount" class="form-control" value="{{ old('amount', $advance->amount) }}" required>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Date</label>
                        <input type="date" name="date" class="form-control" value="{{ old('date', $advance->date->toDateString()) }}" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Status</label>
                        <select name="status" class="form-control">
                            <option value="open" {{ old('status', $advance->status) === 'open' ? 'selected' : '' }}>Open</option>
                            <option value="settled" {{ old('status', $advance->status) === 'settled' ? 'selected' : '' }}>Settled</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Notes</label>
                        <input type="text" name="notes" class="form-control" value="{{ old('notes', $advance->notes) }}">
                    </div>
                </div>
            </div>
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-primary">Update</button>
            <a href="{{ route('hr.advances.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@stop
