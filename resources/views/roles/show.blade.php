@extends('layouts.modern-admin')

@section('title', 'Role Details')
@section('page_title', 'Role Details')

@section('header_actions')
    <a href="{{ route('roles.index') }}" class="btn modern-btn modern-btn-outline">
        <i class="fas fa-arrow-left mr-1"></i> Back to Roles
    </a>
@stop

@section('page_content')
    @php
        $groupedPermissions = [];
        foreach ($rolePermissions as $permission) {
            $parts = explode('-', $permission->name);
            $group = $parts[0] ?? 'general';
            if (!isset($groupedPermissions[$group])) {
                $groupedPermissions[$group] = [];
            }
            $groupedPermissions[$group][] = $permission;
        }
        ksort($groupedPermissions);
    @endphp

    <div class="rolex-header mb-3">
        <div>
            <h1 class="rolex-title">Role Details</h1>
            <p class="rolex-subtitle">Review permissions assigned to this role.</p>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-4 mb-3">
            <div class="rolex-card">
                <div class="rolex-card-header">
                    <div>
                        <h3 class="rolex-card-title"><i class="fas fa-user-shield mr-1"></i> {{ $role->name }}</h3>
                        <p class="rolex-card-subtitle">Role ID #{{ $role->id }}</p>
                    </div>
                </div>
                <div class="rolex-card-body">
                    <div class="rolex-metrics">
                        <div class="rolex-metric">
                            <span class="rolex-metric-label">Permission Groups</span>
                            <span class="rolex-metric-value">{{ count($groupedPermissions) }}</span>
                        </div>
                        <div class="rolex-metric">
                            <span class="rolex-metric-label">Permissions</span>
                            <span class="rolex-metric-value">{{ $rolePermissions->count() }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-8 mb-3">
            <div class="rolex-card">
                <div class="rolex-card-header">
                    <div>
                        <h3 class="rolex-card-title"><i class="fas fa-key mr-1"></i> Assigned Permissions</h3>
                        <p class="rolex-card-subtitle">Grouped by module for easier review.</p>
                    </div>
                </div>
                <div class="rolex-card-body">
                    @if($rolePermissions->isEmpty())
                        <div class="rolex-empty">
                            <i class="fas fa-ban"></i>
                            <p class="mb-0">No permissions are assigned to this role.</p>
                        </div>
                    @else
                        <div class="rolex-group-list">
                            @foreach($groupedPermissions as $group => $permissions)
                                <section class="rolex-group">
                                    <div class="rolex-group-head">
                                        <strong>{{ ucfirst($group) }}</strong>
                                        <span class="rolex-badge">{{ count($permissions) }} permissions</span>
                                    </div>
                                    <div class="rolex-group-body">
                                        <div class="rolex-chip-grid">
                                            @foreach($permissions as $permission)
                                                @php
                                                    $permissionLabel = str_replace($group . '-', '', $permission->name);
                                                    $displayLabel = \Illuminate\Support\Str::headline(str_replace(['_', '.'], ' ', $permissionLabel));
                                                @endphp
                                                <label class="rolex-chip rolex-chip-readonly">
                                                    <input type="checkbox" checked disabled>
                                                    <span><i class="fas fa-check-circle"></i> {{ $displayLabel }}</span>
                                                </label>
                                            @endforeach
                                        </div>
                                    </div>
                                </section>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="rolex-sticky-actions">
        <span class="rolex-sticky-actions__hint">Use edit to adjust permissions for this role.</span>
        <div class="rolex-inline-actions">
            @can('role-edit')
                <a href="{{ route('roles.edit', $role->id) }}" class="btn modern-btn modern-btn-primary">
                    <i class="fas fa-edit mr-1"></i> Edit Role
                </a>
            @endcan
            @can('role-delete')
                <form action="{{ route('roles.destroy', $role->id) }}" method="POST" onsubmit="return confirm('Delete this role?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn modern-btn modern-btn-outline">
                        <i class="fas fa-trash mr-1"></i> Delete
                    </button>
                </form>
            @endcan
        </div>
    </div>
@stop

@section('additional_css')
    @include('roles.partials.styles')
@stop
