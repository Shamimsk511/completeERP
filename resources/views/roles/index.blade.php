@extends('layouts.modern-admin')

@section('title', 'Roles Management')
@section('page_title', 'Roles Management')

@section('header_actions')
    @can('role-create')
        <a href="{{ route('roles.create') }}" class="btn modern-btn modern-btn-primary">
            <i class="fas fa-plus-circle mr-1"></i> New Role
        </a>
    @endcan
@stop

@section('page_content')
    <div class="rolex-header mb-3">
        <div>
            <h1 class="rolex-title">Roles Management</h1>
            <p class="rolex-subtitle">Maintain role definitions and permission coverage.</p>
        </div>
    </div>

    <div class="rolex-card">
        <div class="rolex-card-header">
            <div>
                <h3 class="rolex-card-title"><i class="fas fa-user-tag mr-1"></i> Role Directory</h3>
                <p class="rolex-card-subtitle">Total {{ $roles->total() }} roles</p>
            </div>
            <div class="rolex-search">
                <i class="fas fa-search"></i>
                <input type="text" class="form-control form-control-sm" id="roles-search-input" placeholder="Search roles...">
            </div>
        </div>
        <div class="table-responsive">
            <table class="table rolex-table mb-0" id="roles-table">
                <thead>
                    <tr>
                        <th width="70">#</th>
                        <th>Role</th>
                        <th width="180">Permission Count</th>
                        <th width="320">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($roles as $key => $role)
                        <tr>
                            <td>{{ ($roles->firstItem() ?? 0) + $key }}</td>
                            <td>
                                <span class="rolex-role-name">{{ $role->name }}</span>
                                <span class="rolex-role-meta">Role ID #{{ $role->id }}</span>
                            </td>
                            <td>
                                <span class="rolex-badge">
                                    <i class="fas fa-key"></i> {{ $role->permissions_count ?? 0 }}
                                </span>
                            </td>
                            <td>
                                <div class="rolex-inline-actions">
                                    <a href="{{ route('roles.show', $role->id) }}" class="btn modern-btn modern-btn-outline btn-sm">
                                        <i class="fas fa-eye mr-1"></i> View
                                    </a>
                                    @can('role-edit')
                                        <a href="{{ route('roles.edit', $role->id) }}" class="btn modern-btn modern-btn-outline btn-sm">
                                            <i class="fas fa-edit mr-1"></i> Edit
                                        </a>
                                    @endcan
                                    @can('role-delete')
                                        <form action="{{ route('roles.destroy', $role->id) }}" method="POST" onsubmit="return confirm('Delete this role?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn modern-btn modern-btn-outline btn-sm">
                                                <i class="fas fa-trash mr-1"></i> Delete
                                            </button>
                                        </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4">
                                <div class="rolex-empty">
                                    <i class="fas fa-user-shield"></i>
                                    <p class="mb-0">No roles found.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="rolex-card-body pt-3 pb-3">
            {{ $roles->links('pagination::bootstrap-4') }}
        </div>
    </div>
@stop

@section('additional_css')
    @include('roles.partials.styles')
@stop

@section('additional_js')
    @include('roles.partials.scripts')
@stop
