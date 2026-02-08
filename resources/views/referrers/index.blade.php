@extends('layouts.modern-admin')

@section('title', 'Referrers')
@section('page_title', 'Referrer Management')

@section('header_actions')
    <a class="btn modern-btn modern-btn-success" href="{{ route('referrers.create') }}">
        <i class="fas fa-user-tag"></i> Add Referrer
    </a>
@stop

@section('page_content')
    <div class="card modern-card">
        <div class="card-header modern-header">
            <h3 class="card-title">
                <i class="fas fa-users"></i> Referrers
            </h3>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table modern-table mb-0" id="referrers-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Phone</th>
                            <th>Profession</th>
                            <th>Invoices</th>
                            <th>Total Sold</th>
                            <th>Collected</th>
                            <th>Outstanding</th>
                            <th>Compensation</th>
                            <th>Gift</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($referrers as $referrer)
                            <tr>
                                <td>
                                    <a href="{{ route('referrers.show', $referrer->id) }}" class="text-primary">
                                        {{ $referrer->name }}
                                    </a>
                                </td>
                                <td>{{ $referrer->phone ?: 'N/A' }}</td>
                                <td>{{ $referrer->profession ?: 'N/A' }}</td>
                                <td>{{ $referrer->invoices_count ?? 0 }}</td>
                                <td>৳{{ number_format($referrer->invoices_sum_total ?? 0, 2) }}</td>
                                <td>৳{{ number_format($referrer->invoices_sum_paid_amount ?? 0, 2) }}</td>
                                <td>৳{{ number_format($referrer->invoices_sum_due_amount ?? 0, 2) }}</td>
                                <td>
                                    <span class="badge {{ $referrer->compensation_enabled ? 'badge-success' : 'badge-secondary' }}">
                                        {{ $referrer->compensation_enabled ? 'On' : 'Off' }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge {{ $referrer->gift_enabled ? 'badge-success' : 'badge-secondary' }}">
                                        {{ $referrer->gift_enabled ? 'On' : 'Off' }}
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <a href="{{ route('referrers.show', $referrer->id) }}" class="btn btn-sm btn-info">View</a>
                                        <a href="{{ route('referrers.edit', $referrer->id) }}" class="btn btn-sm btn-primary">Edit</a>
                                        <form action="{{ route('referrers.destroy', $referrer->id) }}" method="POST" onsubmit="return confirm('Delete this referrer?')" style="display:inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center text-muted py-4">No referrers found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@stop

@section('additional_css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.bootstrap4.min.css">
@stop

@section('additional_js')
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.2.9/js/responsive.bootstrap4.min.js"></script>

<script>
$(document).ready(function() {
    $('#referrers-table').DataTable({
        responsive: true,
        pageLength: 25,
        order: [[0, 'asc']]
    });
});
</script>
@stop
