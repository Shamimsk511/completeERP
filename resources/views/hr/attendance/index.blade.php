@extends('layouts.modern-admin')

@section('title', 'Attendance')
@section('page_title', 'Employee Attendance')

@section('page_content')
<div class="card modern-card">
    <div class="card-header modern-header">
        <h3 class="card-title"><i class="fas fa-calendar-check"></i> Attendance</h3>
    </div>
    <form method="POST" action="{{ route('hr.attendance.store') }}">
        @csrf
        <div class="card-body">
            <div class="d-flex flex-wrap align-items-end mb-3">
                <div class="form-group mr-3 mb-0">
                    <label>Date</label>
                    <input type="date" name="date" class="form-control" value="{{ $date }}" required>
                </div>
                <button type="button" class="btn btn-outline-success mb-0" id="mark-all-present">
                    <i class="fas fa-check-circle"></i> Mark All Present
                </button>
            </div>
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Employee</th>
                            <th>Status</th>
                            <th>Notes</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($employees as $employee)
                            @php
                                $record = $attendance[$employee->id] ?? null;
                            @endphp
                            <tr>
                                <td>{{ $employee->name }}</td>
                                <td>
                                    <select name="status[{{ $employee->id }}]" class="form-control attendance-status">
                                        <option value="">-- Select --</option>
                                        <option value="present" {{ ($record && $record->status === 'present') ? 'selected' : '' }}>Present</option>
                                        <option value="absent" {{ ($record && $record->status === 'absent') ? 'selected' : '' }}>Absent</option>
                                        <option value="paid_absent" {{ ($record && $record->status === 'paid_absent') ? 'selected' : '' }}>Paid Absent</option>
                                    </select>
                                </td>
                                <td>
                                    <input type="text" name="notes[{{ $employee->id }}]" class="form-control" value="{{ $record->notes ?? '' }}">
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-success">Save Attendance</button>
        </div>
    </form>
</div>
@stop

@section('additional_js')
<script>
document.getElementById('mark-all-present')?.addEventListener('click', function () {
    document.querySelectorAll('.attendance-status').forEach(function (select) {
        select.value = 'present';
    });
});
</script>
@stop
