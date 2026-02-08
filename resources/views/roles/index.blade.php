@extends('adminlte::page')

@section('title', 'Roles Management')

@section('content_header')
    <div class="roles-header">
        <div class="roles-header__title">
            <div class="roles-header__icon">
                <i class="fas fa-user-shield"></i>
            </div>
            <div>
                <h1>Roles Management</h1>
                <p>Keep permissions organized and easy to review.</p>
            </div>
        </div>
        @can('role-create')
        <a href="{{ route('roles.create') }}" class="btn btn-primary btn-sm roles-header__cta">
            <i class="fas fa-plus-circle mr-1"></i> New Role
        </a>
        @endcan
    </div>
@stop

@section('content')
    @if ($message = Session::get('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
            <h5><i class="icon fas fa-check"></i> Success!</h5>
            {{ $message }}
        </div>
    @endif

    <div class="card roles-card">
        <div class="card-header roles-card__header">
            <div>
                <h3 class="roles-card__title">
                    <i class="fas fa-user-tag"></i> Role Directory
                </h3>
                <div class="roles-card__meta">
                    <span>Total: <strong>{{ $roles->total() }}</strong></span>
                    <span>Showing: <strong>{{ $roles->count() }}</strong></span>
                    <span>Page: <strong>{{ $roles->currentPage() }} / {{ $roles->lastPage() }}</strong></span>
                </div>
            </div>
            <div class="roles-card__search">
                <i class="fas fa-search"></i>
                <input type="text" id="roles-search-input" class="form-control roles-search-input" placeholder="Search roles by name...">
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table roles-table" id="roles-table">
                    <thead>
                        <tr>
                            <th width="60">#</th>
                            <th>Role Name</th>
                            <th width="160">Permissions</th>
                            <th width="280">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($roles as $key => $role)
                        <tr>
                            <td class="roles-table__index">{{ ++$i }}</td>
                            <td>
                                <div class="roles-table__name">
                                    <span class="roles-table__name-text">{{ $role->name }}</span>
                                    <span class="roles-table__meta">Role ID: #{{ $role->id }}</span>
                                </div>
                            </td>
                            <td>
                                <span class="roles-permissions-pill">
                                    {{ $role->permissions_count ?? 0 }} permissions
                                </span>
                            </td>
                            <td>
                                <div class="roles-actions">
                                    <a href="{{ route('roles.show', $role->id) }}" class="btn btn-sm btn-success roles-action-btn">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                    @can('role-edit')
                                    <a href="{{ route('roles.edit', $role->id) }}" class="btn btn-sm btn-info roles-action-btn">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    @endcan
                                    @can('role-delete')
                                    <form action="{{ route('roles.destroy', $role->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger roles-action-btn" onclick="return confirm('Are you sure you want to delete this role?')">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4">
                                <div class="roles-empty">
                                    <i class="fas fa-user-shield"></i>
                                    <p>No roles found yet. Create your first role to get started.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer roles-card__footer">
            <div class="d-flex justify-content-center">
                {{ $roles->links('pagination::bootstrap-4') }}
            </div>
        </div>
    </div>
@stop

@section('css')
<style>
    .roles-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        flex-wrap: wrap;
        padding: 0.75rem 1rem;
        border-radius: 12px;
        background: #ffffff;
        border: 1px solid #e5e7eb;
        margin-bottom: 1rem;
    }

    .roles-header__title {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .roles-header__icon {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        background: rgba(37, 99, 235, 0.12);
        color: #2563eb;
        display: grid;
        place-items: center;
        font-size: 1.1rem;
    }

    .roles-header__title h1 {
        margin: 0;
        font-size: 1.3rem;
        font-weight: 700;
        color: #111827;
    }

    .roles-header__title p {
        margin: 0.15rem 0 0;
        color: #6b7280;
        font-size: 0.85rem;
    }

    .roles-header__cta {
        border-radius: 999px;
        padding: 0.35rem 1rem;
    }

    .roles-card {
        border-radius: 12px;
        border: 1px solid #e5e7eb;
        box-shadow: none;
        overflow: hidden;
        background: #ffffff;
    }

    .roles-card__header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 1rem;
        padding: 0.85rem 1rem;
        border-bottom: 1px solid #e5e7eb;
        background: #ffffff;
        flex-wrap: wrap;
    }

    .roles-card__title {
        margin: 0;
        font-size: 1.15rem;
        font-weight: 700;
        color: #0f172a;
    }

    .roles-card__meta {
        display: flex;
        flex-wrap: wrap;
        gap: 0.75rem;
        font-size: 0.8rem;
        color: #6b7280;
        margin-top: 0.25rem;
    }

    .roles-card__search {
        position: relative;
        min-width: 200px;
        flex: 1;
        max-width: 320px;
    }

    .roles-card__search i {
        position: absolute;
        left: 14px;
        top: 50%;
        transform: translateY(-50%);
        color: #94a3b8;
    }

    .roles-search-input {
        padding-left: 34px;
        border-radius: 999px;
        border: 1px solid rgba(148, 163, 184, 0.4);
        height: 34px;
        font-size: 0.85rem;
    }

    .roles-table {
        margin-bottom: 0;
        background: #ffffff;
    }

    .roles-table thead th {
        border-top: none;
        background: #f8fafc;
        color: #475569;
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        padding: 0.65rem 1rem;
    }

    .roles-table td {
        padding: 0.7rem 1rem;
        vertical-align: middle;
        border-top: 1px solid rgba(148, 163, 184, 0.2);
    }

    .roles-table__index {
        font-weight: 600;
        color: #94a3b8;
    }

    .roles-table__name {
        display: flex;
        flex-direction: column;
        gap: 0.2rem;
    }

    .roles-table__name-text {
        font-weight: 600;
        color: #0f172a;
    }

    .roles-table__meta {
        font-size: 0.75rem;
        color: #94a3b8;
    }

    .roles-permissions-pill {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0.25rem 0.6rem;
        border-radius: 999px;
        background: rgba(59, 130, 246, 0.12);
        color: #2563eb;
        font-size: 0.75rem;
        font-weight: 600;
    }

    .roles-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
    }

    .roles-action-btn {
        border-radius: 999px;
        padding: 0.25rem 0.75rem;
        font-size: 0.75rem;
    }

    .roles-empty {
        padding: 2rem;
        text-align: center;
        color: #94a3b8;
    }

    .roles-empty i {
        font-size: 2rem;
        color: #cbd5f5;
        margin-bottom: 0.75rem;
    }

    .roles-card__footer {
        background: #ffffff;
        border-top: 1px solid #e5e7eb;
        padding: 0.75rem;
    }

    @media (max-width: 768px) {
        .roles-hero {
            padding: 1.25rem;
        }

        .roles-card__search {
            width: 100%;
            max-width: none;
        }
    }
</style>
@stop

@section('js')
<script>
    (function() {
        var input = document.getElementById('roles-search-input');
        if (!input) return;
        input.addEventListener('input', function() {
            var query = input.value.toLowerCase();
            var rows = document.querySelectorAll('#roles-table tbody tr');
            rows.forEach(function(row) {
                if (row.querySelector('.roles-empty')) {
                    return;
                }
                var text = row.innerText.toLowerCase();
                row.style.display = text.indexOf(query) !== -1 ? '' : 'none';
            });
        });
    })();
</script>
@stop
