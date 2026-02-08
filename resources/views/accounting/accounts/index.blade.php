@extends('layouts.modern-admin')

@section('title', 'Accounts (Ledgers)')

@section('page_title', 'Chart of Accounts - Ledgers')

@section('header_actions')
    <form action="{{ route('accounting.accounts.sync') }}" method="POST" class="d-inline" onsubmit="return confirm('This will sync all customers and companies to their ledger accounts. Continue?')">
        @csrf
        <button type="submit" class="btn modern-btn modern-btn-success">
            <i class="fas fa-sync"></i> Sync Customers & Companies
        </button>
    </form>
    <a href="{{ route('accounting.accounts.create') }}" class="btn modern-btn modern-btn-primary">
        <i class="fas fa-plus"></i> New Account
    </a>
@stop

@section('page_content')
    <!-- Search & Filters Section -->
    <div class="card mb-4 border-0 shadow-sm">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="fas fa-filter"></i> Filters
            </h5>
            <button type="button" class="btn btn-light btn-sm" id="clear-filters">
                <i class="fas fa-times"></i> Clear
            </button>
        </div>
        <div class="card-body bg-light">
            <form id="filter-form">
                <div class="row g-3">
                    <div class="col-lg-3 col-md-6">
                        <label class="form-label">Account Group</label>
                        <select class="form-select" id="account_group_id" name="account_group_id">
                            <option value="">All Groups</option>
                            @foreach($groups as $group)
                                <option value="{{ $group->id }}">{{ $group->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <label class="form-label">Account Type</label>
                        <select class="form-select" id="account_type" name="account_type">
                            <option value="">All Types</option>
                            @foreach($accountTypes as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-2 col-md-6">
                        <label class="form-label">Status</label>
                        <select class="form-select" id="is_active" name="is_active">
                            <option value="">All</option>
                            <option value="yes">Active</option>
                            <option value="no">Inactive</option>
                        </select>
                    </div>
                    <div class="col-lg-2 col-md-6 d-flex align-items-end">
                        <button type="button" class="btn btn-primary w-100" id="apply-filters">
                            <i class="fas fa-search"></i> Search
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Accounts List -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="fas fa-book"></i> Accounts List
            </h5>
            <button class="btn btn-light btn-sm" id="refresh-table">
                <i class="fas fa-sync-alt"></i>
            </button>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="accounts-table">
                    <thead class="table-light">
                        <tr>
                            <th>Code</th>
                            <th>Account Name</th>
                            <th>Group</th>
                            <th>Type</th>
                            <th class="text-right">Balance</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
@stop

@section('additional_css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastr@2.1.4/build/toastr.min.css">

<style>
    .action-btn {
        width: 32px;
        height: 32px;
        border-radius: 6px;
        border: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin: 0 2px;
    }
    .btn-view { background-color: #0ea5e9; color: white; }
    .btn-edit { background-color: #f59e0b; color: white; }
    .btn-ledger { background-color: #8b5cf6; color: white; }
    .btn-delete { background-color: #ef4444; color: white; }
</style>
@stop

@section('additional_js')
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/toastr@2.1.4/build/toastr.min.js"></script>

<script>
$(document).ready(function() {
    const csrfToken = '{{ csrf_token() }}';

    const table = $('#accounts-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route('accounting.accounts.data') }}',
            data: function(d) {
                d.account_group_id = $('#account_group_id').val();
                d.account_type = $('#account_type').val();
                d.is_active = $('#is_active').val();
            }
        },
        columns: [
            { data: 'code', name: 'code' },
            { data: 'name', name: 'name' },
            { data: 'group_name', name: 'account_group_id' },
            { data: 'account_type', name: 'account_type', render: function(data) {
                return data.charAt(0).toUpperCase() + data.slice(1);
            }},
            { data: 'balance', name: 'current_balance', className: 'text-right' },
            { data: 'status', name: 'is_active' },
            { data: 'actions', name: 'actions', orderable: false, searchable: false }
        ],
        order: [[1, 'asc']],
        pageLength: 25
    });

    $('#apply-filters').on('click', function() {
        table.draw();
    });

    $('#clear-filters').on('click', function() {
        $('#filter-form')[0].reset();
        table.draw();
    });

    $('#refresh-table').on('click', function() {
        table.draw();
    });

    // Delete account
    $(document).on('click', '.delete-account', function() {
        const accountId = $(this).data('id');

        Swal.fire({
            title: 'Delete Account?',
            text: 'This action cannot be undone.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Yes, delete it'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `/accounting/accounts/${accountId}`,
                    type: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': csrfToken },
                    success: function(response) {
                        if (response.success) {
                            toastr.success(response.message);
                            table.draw(false);
                        } else {
                            toastr.error(response.message);
                        }
                    },
                    error: function(xhr) {
                        toastr.error(xhr.responseJSON?.message || 'Failed to delete account');
                    }
                });
            }
        });
    });

    toastr.options = { closeButton: true, progressBar: true, positionClass: 'toast-top-right' };
});
</script>
@stop
