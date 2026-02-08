@extends('adminlte::page')

@section('title', 'Edit User')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-user-edit text-info"></i> Edit User</h1>
        <a href="{{ route('users.index') }}" class="btn btn-outline-info">
            <i class="fas fa-arrow-left"></i> Back to Users
        </a>
    </div>
@stop

@section('content')
<div class="card shadow-sm">
    <div class="card-header bg-light">
        <h3 class="card-title">
            <i class="fas fa-user-circle mr-2"></i> {{ $user->name }}
        </h3>
        <div class="card-tools">
            <span class="badge badge-info">{{ $user->email }}</span>
        </div>
    </div>
    <div class="card-body">
        @if (count($errors) > 0)
            <div class="alert alert-danger alert-dismissible fade show">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h5><i class="icon fas fa-ban"></i> Error!</h5>
                <ul class="list-unstyled mb-0">
                    @foreach ($errors->all() as $error)
                        <li><i class="fas fa-exclamation-circle mr-2"></i>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('users.update', $user->id) }}" class="form-horizontal">
            @csrf
            @method('PATCH')
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="name" class="font-weight-bold">
                            <i class="fas fa-user mr-1"></i> Full Name
                        </label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-user"></i></span>
                            </div>
                            <input type="text" name="name" id="name" class="form-control" value="{{ $user->name }}">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="email" class="font-weight-bold">
                            <i class="fas fa-envelope mr-1"></i> Email Address
                        </label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                            </div>
                            <input type="email" name="email" id="email" class="form-control" value="{{ $user->email }}">
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="password" class="font-weight-bold">
                            <i class="fas fa-lock mr-1"></i> Password
                        </label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-lock"></i></span>
                            </div>
                            <input type="password" name="password" id="password" class="form-control" placeholder="Leave blank to keep current password">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm-password" class="font-weight-bold">
                            <i class="fas fa-check-circle mr-1"></i> Confirm Password
                        </label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-check-circle"></i></span>
                            </div>
                            <input type="password" name="confirm-password" id="confirm-password" class="form-control" placeholder="Confirm password">
                        </div>
                    </div>
                </div>
                
                <div class="col-md-12">
                    @if(isset($tenants) && $tenants->count())
                        <div class="form-group">
                            <label for="tenant_id" class="font-weight-bold">
                                <i class="fas fa-building mr-1"></i> Company
                            </label>
                            <select name="tenant_id" id="tenant_id" class="form-control select2" data-placeholder="Select company">
                                @foreach ($tenants as $tenant)
                                    <option value="{{ $tenant->id }}" {{ (int) old('tenant_id', $selectedTenantId) === (int) $tenant->id ? 'selected' : '' }}>
                                        {{ $tenant->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('tenant_id')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    @endif

                    <div class="form-group">
                        <label for="roles" class="font-weight-bold">
                            <i class="fas fa-user-tag mr-1"></i> Assign Roles
                        </label>
                        <select name="roles[]" id="roles" class="form-control select2"  data-placeholder="Select roles">
                            @foreach ($roles as $value => $label)
                                <option value="{{ $value }}" {{ in_array($value, $userRole) ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
            
            <div class="text-center mt-4">
                <button type="submit" class="btn btn-info px-5">
                    <i class="fas fa-save mr-2"></i> Update User
                </button>
            </div>
        </form>
    </div>
</div>
@stop

@section('css')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap4-theme@1.0.0/dist/select2-bootstrap4.min.css">
<style>
    .card {
        border-radius: 0.5rem;
        border: none;
    }
    .form-control, .input-group-text {
        border-radius: 0.25rem;
    }
    .select2-container--bootstrap4 .select2-selection {
        height: calc(2.25rem + 2px);
        border-radius: 0.25rem;
    }
</style>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        $('.select2').select2({
            theme: 'bootstrap4',
        });
    });
</script>
@stop
