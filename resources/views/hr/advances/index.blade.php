@extends('layouts.modern-admin')

@section('title', 'Employee Advances')
@section('page_title', 'Employee Advances')

@section('header_actions')
    <a class="btn modern-btn modern-btn-success" href="{{ route('hr.advances.create') }}">
        <i class="fas fa-plus"></i> Add Advance
    </a>
@stop

@section('page_content')
<div class="card modern-card">
    <div class="card-header modern-header">
        <h3 class="card-title"><i class="fas fa-hand-holding-usd"></i> Advances</h3>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table mb-0">
                <thead>
                    <tr>
                        <th>Employee</th>
                        <th>Date</th>
                        <th>Amount</th>
                        <th>Outstanding</th>
                        <th>Status</th>
                        <th width="120">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($advances as $advance)
                        <tr>
                            <td>{{ $advance->employee->name }}</td>
                            <td>{{ $advance->date->format('d M, Y') }}</td>
                            <td>৳{{ number_format($advance->amount, 2) }}</td>
                            <td>৳{{ number_format($advance->outstanding_amount, 2) }}</td>
                            <td>{{ ucfirst($advance->status) }}</td>
                            <td>
                                <div class="btn-group">
                                    <a href="{{ route('hr.advances.edit', $advance) }}" class="btn btn-sm btn-primary">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button" class="btn btn-sm btn-danger" onclick="deleteAdvance({{ $advance->id }})">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                                <form id="delete-advance-{{ $advance->id }}" action="{{ route('hr.advances.destroy', $advance) }}" method="POST" class="d-none">
                                    @csrf
                                    @method('DELETE')
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-3">No advances found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer">
        {{ $advances->links() }}
    </div>
</div>
@stop

@section('additional_js')
<script>
function deleteAdvance(id) {
    if (!confirm('Delete this advance?')) {
        return;
    }
    document.getElementById('delete-advance-' + id).submit();
}
</script>
@stop
