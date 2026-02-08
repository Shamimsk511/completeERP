@extends('layouts.modern-admin')

@section('title', 'Cash Registers')
@section('page_title', 'Cash Registers')

@section('header_actions')
    @if($myOpenRegister ?? null)
        <a href="{{ route('cash-registers.show', $myOpenRegister->id) }}" class="btn modern-btn modern-btn-success">
            <i class="fas fa-cash-register"></i> My Open Register
        </a>
    @else
        <a href="{{ route('cash-registers.open') }}" class="btn modern-btn modern-btn-primary">
            <i class="fas fa-plus"></i> Open Register
        </a>
    @endif
@stop

@section('page_content')
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    @endif

    @if(session('info'))
        <div class="alert alert-info alert-dismissible fade show">
            <i class="fas fa-info-circle"></i> {{ session('info') }}
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    @endif

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">All Cash Registers</h3>
            <div class="card-tools">
                <select id="filter-status" class="form-control form-control-sm" style="width: 150px; display: inline-block;">
                    <option value="">All Status</option>
                    <option value="open">Open</option>
                    <option value="closed">Closed</option>
                </select>
            </div>
        </div>
        <div class="card-body">
            <table id="registers-table" class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>User</th>
                        <th>Opening Balance</th>
                        <th>Current/Closing</th>
                        <th>Variance</th>
                        <th>Opened At</th>
                        <th>Closed At</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
@stop

@section('additional_js')
<script>
$(function() {
    var table = $('#registers-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("cash-registers.index") }}',
            data: function(d) {
                d.status = $('#filter-status').val();
            }
        },
        columns: [
            { data: 'id', name: 'id' },
            { data: 'user_name', name: 'user.name' },
            { data: 'opening_balance', name: 'opening_balance', render: function(data) {
                return '৳' + parseFloat(data).toLocaleString('en-BD', {minimumFractionDigits: 2});
            }},
            { data: 'expected_closing_balance', name: 'expected_closing_balance', render: function(data, type, row) {
                var amount = row.status === 'closed' ? row.actual_closing_balance : data;
                return '৳' + parseFloat(amount || 0).toLocaleString('en-BD', {minimumFractionDigits: 2});
            }},
            { data: 'variance', name: 'variance', render: function(data, type, row) {
                if (row.status !== 'closed' || !data) return '-';
                var v = parseFloat(data);
                var cls = v > 0 ? 'text-success' : (v < 0 ? 'text-danger' : '');
                return '<span class="' + cls + '">৳' + v.toLocaleString('en-BD', {minimumFractionDigits: 2}) + '</span>';
            }},
            { data: 'opened_at', name: 'opened_at' },
            { data: 'closed_at', name: 'closed_at' },
            { data: 'status', name: 'status', render: function(data) {
                var badge = data === 'open' ? 'badge-success' : 'badge-secondary';
                return '<span class="badge ' + badge + '">' + data.charAt(0).toUpperCase() + data.slice(1) + '</span>';
            }},
            { data: 'id', orderable: false, searchable: false, render: function(data, type, row) {
                return '<a href="/cash-registers/' + data + '" class="btn btn-sm btn-info"><i class="fas fa-eye"></i></a>';
            }}
        ],
        order: [[0, 'desc']]
    });

    $('#filter-status').on('change', function() {
        table.ajax.reload();
    });
});
</script>
@stop
