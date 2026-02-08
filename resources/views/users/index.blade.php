@extends('adminlte::page')

@section('title', 'Users Management')

@section('content_header')
    <div class="users-hero">
        <div class="users-hero__content">
            <div class="users-hero__badge">
                <i class="fas fa-users"></i>
                <span>User Directory</span>
            </div>
            <h1 class="users-hero__title">Users Management</h1>
            <p class="users-hero__subtitle">Manage access, roles, and team members in one place.</p>
        </div>
        <div class="users-hero__actions">
            @can('user-create')
            <a href="{{ route('users.create', $selectedTenantId ? ['tenant_id' => $selectedTenantId] : []) }}" class="btn btn-primary users-hero__cta">
                <i class="fas fa-user-plus mr-1"></i> Create New User
            </a>
            @endcan
        </div>
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

    @if(!empty($selectedTenantId))
        <div class="alert alert-info">
            <i class="fas fa-filter"></i>
            Showing users for
            <strong>{{ $selectedTenant?->name ?? 'Selected Company' }}</strong>.
            <a href="{{ route('users.index') }}" class="ml-2">Clear filter</a>
        </div>
    @endif

    <div class="users-stats">
        <div class="users-stat-card">
            <div class="users-stat-icon">
                <i class="fas fa-user-check"></i>
            </div>
            <div>
                <p class="users-stat-label">Total Users</p>
                <p class="users-stat-value">{{ $data->total() }}</p>
            </div>
        </div>
        <div class="users-stat-card">
            <div class="users-stat-icon accent">
                <i class="fas fa-layer-group"></i>
            </div>
            <div>
                <p class="users-stat-label">Showing</p>
                <p class="users-stat-value">{{ $data->count() }} users</p>
            </div>
        </div>
        <div class="users-stat-card">
            <div class="users-stat-icon soft">
                <i class="fas fa-user-clock"></i>
            </div>
            <div>
                <p class="users-stat-label">Current Page</p>
                <p class="users-stat-value">{{ $data->currentPage() }} / {{ $data->lastPage() }}</p>
            </div>
        </div>
    </div>

    <div class="card users-card">
        <div class="card-header users-card__header">
            <div>
                <h3 class="users-card__title">
                    <i class="fas fa-user-friends"></i> User List
                </h3>
                <p class="users-card__subtitle">Search, view, and manage team access.</p>
            </div>
            <div class="users-card__search">
                <i class="fas fa-search"></i>
                <input type="text" name="table_search" class="form-control users-search-input" placeholder="Search users by name, email, or role...">
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table users-table">
                    <thead>
                        <tr>
                            <th width="60">#</th>
                            <th>User</th>
                            <th>Email</th>
                            @if(auth()->user()?->hasRole('Super Admin'))
                                <th>Company</th>
                            @endif
                            <th>Roles</th>
                            <th width="280">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($data as $key => $user)
                            <tr>
                                <td class="users-table__index">{{ ++$i }}</td>
                                <td>
                                    <div class="users-table__profile">
                                        <div class="users-table__avatar">
                                            {{ strtoupper(substr($user->name ?? 'U', 0, 1)) }}
                                        </div>
                                        <div>
                                            <div class="users-table__name">{{ $user->name }}</div>
                                            <div class="users-table__meta">User ID: #{{ $user->id }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="users-table__email">{{ $user->email }}</td>
                                @if(auth()->user()?->hasRole('Super Admin'))
                                    <td>
                                        <span class="badge badge-light">
                                            {{ $user->tenant?->name ?? 'Unassigned' }}
                                        </span>
                                    </td>
                                @endif
                                <td>
                                    @if(!empty($user->getRoleNames()))
                                        <div class="users-table__roles">
                                            @foreach($user->getRoleNames() as $role)
                                                <span class="badge users-role-badge">{{ $role }}</span>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="users-role-empty">No roles</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="users-actions">
                                        <a href="{{ route('users.show', $user->id) }}" class="btn btn-sm btn-success users-action-btn">
                                            <i class="fas fa-eye"></i> View
                                        </a>
                                        @can('user-edit')
                                        <a href="{{ route('users.edit', $user->id) }}" class="btn btn-sm btn-info users-action-btn">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                        @endcan
                                        @can('user-delete')
                                        <form action="{{ route('users.destroy', $user->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger users-action-btn" onclick="return confirm('Are you sure you want to delete this user?')">
                                                <i class="fas fa-trash"></i> Delete
                                            </button>
                                        </form>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer users-card__footer">
            <div class="d-flex justify-content-center">
                {{ $data->links('pagination::bootstrap-4') }}
            </div>
        </div>
    </div>
@stop

@section('css')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap');

    .content-wrapper {
        background: radial-gradient(circle at top, rgba(15, 23, 42, 0.06), transparent 55%),
            linear-gradient(160deg, #f8fafc 0%, #eef2ff 100%);
        font-family: 'Outfit', sans-serif;
    }

    .users-hero {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 1.5rem;
        padding: 1.5rem 1.75rem;
        border-radius: 1.25rem;
        background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #312e81 100%);
        color: #f8fafc;
        margin-bottom: 1.5rem;
        box-shadow: 0 20px 45px rgba(15, 23, 42, 0.2);
        flex-wrap: wrap;
    }

    .users-hero__badge {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.35rem 0.75rem;
        border-radius: 999px;
        background: rgba(248, 250, 252, 0.15);
        font-size: 0.85rem;
        letter-spacing: 0.03em;
    }

    .users-hero__title {
        font-size: 2rem;
        margin: 0.6rem 0 0.25rem;
        font-weight: 700;
    }

    .users-hero__subtitle {
        margin: 0;
        color: rgba(248, 250, 252, 0.8);
    }

    .users-hero__cta {
        border-radius: 999px;
        padding: 0.6rem 1.4rem;
        font-weight: 600;
        box-shadow: 0 10px 25px rgba(99, 102, 241, 0.4);
    }

    .users-stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 1rem;
        margin-bottom: 1.5rem;
    }

    .users-stat-card {
        background: #fff;
        border-radius: 1rem;
        padding: 1rem 1.25rem;
        display: flex;
        align-items: center;
        gap: 1rem;
        box-shadow: 0 10px 30px rgba(15, 23, 42, 0.08);
    }

    .users-stat-icon {
        height: 48px;
        width: 48px;
        border-radius: 16px;
        background: rgba(99, 102, 241, 0.15);
        color: #4f46e5;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
    }

    .users-stat-icon.accent {
        background: rgba(14, 165, 233, 0.15);
        color: #0284c7;
    }

    .users-stat-icon.soft {
        background: rgba(249, 115, 22, 0.12);
        color: #ea580c;
    }

    .users-stat-label {
        margin: 0;
        font-size: 0.85rem;
        color: #64748b;
    }

    .users-stat-value {
        margin: 0;
        font-size: 1.4rem;
        font-weight: 600;
        color: #0f172a;
    }

    .users-card {
        border: none;
        border-radius: 1.25rem;
        box-shadow: 0 24px 50px rgba(15, 23, 42, 0.1);
        overflow: hidden;
    }

    .users-card__header {
        background: #fff;
        border-bottom: 1px solid rgba(148, 163, 184, 0.2);
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 1rem;
        flex-wrap: wrap;
    }

    .users-card__title {
        margin: 0;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        color: #0f172a;
    }

    .users-card__subtitle {
        margin: 0.35rem 0 0;
        color: #64748b;
        font-size: 0.9rem;
    }

    .users-card__search {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.45rem 0.8rem;
        border-radius: 999px;
        border: 1px solid rgba(148, 163, 184, 0.4);
        background: #f8fafc;
        min-width: 260px;
        flex: 1;
        max-width: 360px;
    }

    .users-search-input {
        border: none;
        background: transparent;
        padding: 0;
        box-shadow: none;
    }

    .users-search-input:focus {
        box-shadow: none;
    }

    .users-table {
        margin: 0;
    }

    .users-table thead {
        background: #0f172a;
        color: #e2e8f0;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.08em;
    }

    .users-table tbody tr {
        border-bottom: 1px solid rgba(148, 163, 184, 0.2);
        transition: background 0.2s ease, transform 0.2s ease;
    }

    .users-table tbody tr:hover {
        background: rgba(99, 102, 241, 0.06);
        transform: translateY(-1px);
    }

    .users-table__index {
        font-weight: 600;
        color: #475569;
    }

    .users-table__profile {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .users-table__avatar {
        height: 40px;
        width: 40px;
        border-radius: 14px;
        background: linear-gradient(135deg, #6366f1, #22d3ee);
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
    }

    .users-table__name {
        font-weight: 600;
        color: #0f172a;
    }

    .users-table__meta {
        font-size: 0.75rem;
        color: #94a3b8;
    }

    .users-table__email {
        color: #475569;
    }

    .users-table__roles {
        display: flex;
        gap: 0.4rem;
        flex-wrap: wrap;
    }

    .users-role-badge {
        background: rgba(99, 102, 241, 0.12);
        color: #4338ca;
        font-weight: 600;
        border-radius: 999px;
        padding: 0.35rem 0.65rem;
        border: 1px solid rgba(99, 102, 241, 0.2);
    }

    .users-role-empty {
        font-size: 0.8rem;
        color: #94a3b8;
    }

    .users-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
    }

    .users-action-btn {
        border-radius: 999px;
        padding: 0.35rem 0.9rem;
        font-weight: 500;
    }

    .users-card__footer {
        background: #fff;
        border-top: 1px solid rgba(148, 163, 184, 0.2);
    }

    .pagination {
        margin-bottom: 0;
    }

    @media (max-width: 768px) {
        .users-hero {
            padding: 1.25rem;
        }

        .users-hero__title {
            font-size: 1.6rem;
        }

        .users-card__header {
            align-items: flex-start;
        }

        .users-card__search {
            width: 100%;
            max-width: 100%;
        }

        .users-actions {
            flex-direction: column;
            align-items: flex-start;
        }

        .users-action-btn {
            width: 100%;
            justify-content: center;
        }
    }
</style>
@stop

@section('js')
<script>
    $(document).ready(function() {
        // Add animation to alerts
        $('.alert').addClass('animate__animated animate__fadeIn');
        
        // Table search functionality
        $('input[name="table_search"]').on('keyup', function() {
            var value = $(this).val().toLowerCase();
            $('table tbody tr').filter(function() {
                $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
            });
        });
    });
</script>
@stop
