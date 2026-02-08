@extends('layouts.modern-admin')

@section('title', 'Employees')
@section('page_title', 'Employee Management')

@section('header_actions')
    <a class="btn modern-btn modern-btn-success" href="{{ route('hr.employees.create') }}">
        <i class="fas fa-user-plus"></i> Add Employee
    </a>
@stop

@section('page_content')
<div class="card modern-card">
    <div class="card-header modern-header">
        <h3 class="card-title"><i class="fas fa-user-tie"></i> Employees</h3>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table modern-table mb-0">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Phone</th>
                        <th>Email</th>
                        <th>Basic Salary</th>
                        <th>Status</th>
                        <th width="220">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($employees as $employee)
                        <tr>
                            <td>
                                <a href="{{ route('hr.employees.show', $employee) }}">
                                    {{ $employee->name }}
                                </a>
                            </td>
                            <td>{{ $employee->phone ?? '-' }}</td>
                            <td>{{ $employee->email ?? '-' }}</td>
                            <td>৳{{ number_format($employee->basic_salary ?? 0, 2) }}</td>
                            <td>
                                <span class="badge {{ $employee->status === 'active' ? 'badge-success' : 'badge-secondary' }}">
                                    {{ ucfirst($employee->status ?? 'active') }}
                                </span>
                            </td>
                            <td>
                                <div class="btn-group">
                                    <a href="{{ route('hr.employees.show', $employee) }}" class="btn btn-sm btn-info">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('hr.employees.edit', $employee) }}" class="btn btn-sm btn-primary">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="{{ route('hr.employees.ledger', $employee) }}" class="btn btn-sm btn-success">
                                        <i class="fas fa-book"></i>
                                    </a>
                                    <button type="button" class="btn btn-sm btn-danger" onclick="deleteEmployee({{ $employee->id }})">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                                <form id="delete-form-{{ $employee->id }}" action="{{ route('hr.employees.destroy', $employee) }}" method="POST" class="d-none">
                                    @csrf
                                    @method('DELETE')
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-4">No employees found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer">
        {{ $employees->links() }}
    </div>
</div>
@stop

@section('additional_js')
<script>
function deleteEmployee(id) {
    if (!confirm('Are you sure you want to delete this employee?')) {
        return;
    }
    document.getElementById('delete-form-' + id).submit();
}
</script>
@stop
