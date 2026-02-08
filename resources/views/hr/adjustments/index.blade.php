@extends('layouts.modern-admin')

@section('title', 'Adjustments')
@section('page_title', 'Employee Adjustments')

@section('header_actions')
    <a class="btn modern-btn modern-btn-success" href="{{ route('hr.adjustments.create') }}">
        <i class="fas fa-plus"></i> Add Adjustment
    </a>
@stop

@section('page_content')
<div class="card modern-card">
    <div class="card-header modern-header">
        <h3 class="card-title"><i class="fas fa-sliders-h"></i> Adjustments</h3>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table mb-0">
                <thead>
                    <tr>
                        <th>Employee</th>
                        <th>Type</th>
                        <th>Amount</th>
                        <th>Effective Date</th>
                        <th>Notes</th>
                        <th width="120">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($adjustments as $adjustment)
                        <tr>
                            <td>{{ $adjustment->employee->name }}</td>
                            <td>{{ ucfirst(str_replace('_', ' ', $adjustment->type)) }}</td>
                            <td>৳{{ number_format($adjustment->amount, 2) }}</td>
                            <td>{{ $adjustment->effective_date->format('d M, Y') }}</td>
                            <td>{{ $adjustment->notes ?? '-' }}</td>
                            <td>
                                <div class="btn-group">
                                    <a href="{{ route('hr.adjustments.edit', $adjustment) }}" class="btn btn-sm btn-primary">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button" class="btn btn-sm btn-danger" onclick="deleteAdjustment({{ $adjustment->id }})">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                                <form id="delete-adjustment-{{ $adjustment->id }}" action="{{ route('hr.adjustments.destroy', $adjustment) }}" method="POST" class="d-none">
                                    @csrf
                                    @method('DELETE')
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-3">No adjustments found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer">
        {{ $adjustments->links() }}
    </div>
</div>
@stop

@section('additional_js')
<script>
function deleteAdjustment(id) {
    if (!confirm('Delete this adjustment?')) {
        return;
    }
    document.getElementById('delete-adjustment-' + id).submit();
}
</script>
@stop
