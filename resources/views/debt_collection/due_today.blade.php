@extends('adminlte::page')

@section('title', 'Due Today')

@section('content_header')
    <h1>Customers Due Today</h1>
@stop

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">{{ $totalDueToday }} Customers Due Today</h3>
        <div class="card-tools">
            <a href="{{ route('debt-collection.index') }}" class="btn btn-sm btn-default">
                <i class="fas fa-arrow-left"></i> Back
            </a>
        </div>
    </div>
    <div class="card-body">
        <table class="table table-bordered table-striped" id="due-today-table">
            <thead>
                <tr>
                    <th>Customer</th>
                    <th>Balance</th>
                    <th>Promise Date</th>
                    <th>Last Interaction</th>
                    <th>Call Tracking</th>
                    <th>Actions</th>
                </tr>
            </thead>
        </table>
    </div>
</div>
@stop

@section('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.10.25/css/dataTables.bootstrap4.min.css">
@stop

@section('js')
<script src="https://cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.10.25/js/dataTables.bootstrap4.min.js"></script>

<script>
$(function() {
    $('#due-today-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('debt-collection.get-customers') }}",
            data: {
                due_date_start: "{{ now()->format('Y-m-d') }}",
                due_date_end: "{{ now()->format('Y-m-d') }}"
            }
        },
        columns: [
            { 
                data: null,
                render: function(data) {
                    return `<strong>${data.name}</strong><br>
                            <small>Phone: ${data.phone}</small>`;
                }
            },
            { 
                data: 'outstanding_balance',
                render: function(data) {
                    return parseFloat(data).toFixed(2);
                }
            },
            { data: 'payment_promise_date', name: 'payment_promise_date', defaultContent: '-' },
            { data: 'last_interaction', name: 'last_interaction' },
            { data: 'call_tracking', name: 'call_tracking' },
            { data: 'action', name: 'action', orderable: false }
        ]
    });
});
</script>
@stop
