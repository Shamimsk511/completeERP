@extends('adminlte::page')

@section('title', 'Role Details')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-user-shield text-success"></i> Role Details</h1>
        <a href="{{ route('roles.index') }}" class="btn btn-outline-success">
            <i class="fas fa-arrow-left"></i> Back to Roles
        </a>
    </div>
@stop

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card shadow-sm">
            <div class="card-header bg-success text-white">
                <h3 class="card-title">
                    <i class="fas fa-tag mr-2"></i> {{ $role->name }}
                </h3>
            </div>
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-12">
                        <h5 class="font-weight-bold mb-3">
                            <i class="fas fa-key mr-1"></i> Permissions:
                        </h5>
                        
                        @php
                            $groupedPermissions = [];
                            foreach($rolePermissions as $permission) {
                                $parts = explode('-', $permission->name);
                                $group = $parts[0];
                                
                                if (!isset($groupedPermissions[$group])) {
                                    $groupedPermissions[$group] = [];
                                }
                                
                                $groupedPermissions[$group][] = $permission;
                            }
                            ksort($groupedPermissions);
                        @endphp
                        
                        <div class="accordion" id="permissionsAccordion">
                            @foreach($groupedPermissions as $group => $permissions)
                                <div class="card mb-2">
                                    <div class="card-header bg-light" id="heading{{ $group }}">
                                        <h5 class="mb-0">
                                            <button class="btn btn-link text-dark" type="button" data-toggle="collapse" data-target="#collapse{{ $group }}" aria-expanded="true" aria-controls="collapse{{ $group }}">
                                                <i class="fas fa-layer-group mr-2"></i> {{ ucfirst($group) }} ({{ count($permissions) }})
                                            </button>
                                        </h5>
                                    </div>

                                    <div id="collapse{{ $group }}" class="collapse" aria-labelledby="heading{{ $group }}" data-parent="#permissionsAccordion">
                                        <div class="card-body">
                                            <div class="d-flex flex-wrap">
                                                @foreach($permissions as $permission)
                                                    <span class="badge badge-success p-2 m-1">
                                                        <i class="fas fa-check-circle mr-1"></i> {{ str_replace($group.'-', '', $permission->name) }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer bg-white">
                <div class="d-flex justify-content-between">
                    @can('role-edit')
                    <a href="{{ route('roles.edit', $role->id) }}" class="btn btn-info">
                        <i class="fas fa-edit mr-1"></i> Edit
                    </a>
                    @endcan
                    @can('role-delete')
                    <form action="{{ route('roles.destroy', $role->id) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this role?')">
                            <i class="fas fa-trash mr-1"></i> Delete
                        </button>
                    </form>
                    @endcan
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
    .card {
        border-radius: 0.5rem;
        border: none;
    }
    .card-header {
        border-top-left-radius: 0.5rem;
        border-top-right-radius: 0.5rem;
    }
    .badge {
        font-size: 0.9rem;
    }
    .btn-link {
        text-decoration: none;
    }
    .btn-link:hover {
        text-decoration: none;
    }
</style>
@stop

@section('js')
<script>
    $(document).ready(function() {
        // Expand first accordion by default
        $('#permissionsAccordion .collapse:first').addClass('show');
    });
</script>
@stop
