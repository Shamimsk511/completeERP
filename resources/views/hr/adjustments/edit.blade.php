@extends('layouts.modern-admin')

@section('title', 'Edit Adjustment')
@section('page_title', 'Edit Adjustment')

@section('page_content')
<div class="card modern-card">
    <div class="card-header modern-header">
        <h3 class="card-title"><i class="fas fa-edit"></i> Edit Adjustment</h3>
    </div>
    <form method="POST" action="{{ route('hr.adjustments.update', $adjustment) }}">
        @csrf
        @method('PUT')
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Employee</label>
                        <select name="employee_id" class="form-control" required>
                            @foreach($employees as $employee)
                                <option value="{{ $employee->id }}" {{ old('employee_id', $adjustment->employee_id) == $employee->id ? 'selected' : '' }}>
                                    {{ $employee->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Type</label>
                        <select name="type" class="form-control" required>
                            @foreach($types as $value => $label)
                                <option value="{{ $value }}" {{ old('type', $adjustment->type) == $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Amount</label>
                        <input type="number" step="0.01" name="amount" class="form-control" value="{{ old('amount', $adjustment->amount) }}" required>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Effective Date</label>
                        <input type="date" name="effective_date" class="form-control" value="{{ old('effective_date', $adjustment->effective_date->toDateString()) }}" required>
                    </div>
                </div>
                <div class="col-md-9">
                    <div class="form-group">
                        <label>Notes</label>
                        <input type="text" name="notes" class="form-control" value="{{ old('notes', $adjustment->notes) }}">
                    </div>
                </div>
            </div>
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-primary">Update</button>
            <a href="{{ route('hr.adjustments.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@stop
