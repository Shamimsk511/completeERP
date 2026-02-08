@extends('adminlte::page')

@section('title', 'User Details')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-user-circle text-success"></i> User Details</h1>
        <a href="{{ route('users.index') }}" class="btn btn-outline-success">
            <i class="fas fa-arrow-left"></i> Back to Users
        </a>
    </div>
@stop

@section('content')
<div class="row">
    <div class="col-md-4">
        <div class="card card-widget widget-user shadow-sm">
            <div class="widget-user-header bg-success">
                <h3 class="widget-user-username">{{ $user->name }}</h3>
                <h5 class="widget-user-desc">{{ $user->email }}</h5>
            </div>

            <div class="card-footer">
                <div class="row">
                    <div class="col-sm-12">
                        <div class="description-block">
                            <h5 class="description-header">ROLES</h5>
                            <div class="mt-3">
                                @if(!empty($user->getRoleNames()))
                                    @foreach($user->getRoleNames() as $role)
                                        <span class="badge badge-success p-2 mb-1">
                                            <i class="fas fa-user-tag mr-1"></i> {{ $role }}
                                        </span>
                                    @endforeach
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-8">
        <div class="card shadow-sm">
            <div class="card-header bg-light">
                <h3 class="card-title">
                    <i class="fas fa-info-circle mr-2"></i> User Information
                </h3>
            </div>
            <div class="card-body">
                <table class="table table-striped">
                    <tbody>
                        <tr>
                            <th style="width: 30%"><i class="fas fa-id-card mr-2"></i> User ID</th>
                            <td>{{ $user->id }}</td>
                        </tr>
                        <tr>
                            <th><i class="fas fa-user mr-2"></i> Full Name</th>
                            <td>{{ $user->name }}</td>
                        </tr>
                        <tr>
                            <th><i class="fas fa-envelope mr-2"></i> Email</th>
                            <td>{{ $user->email }}</td>
                        </tr>
                        <tr>
                            <th><i class="fas fa-calendar-alt mr-2"></i> Created At</th>
                            <td>{{ $user->created_at->format('F d, Y h:i A') }}</td>
                        </tr>
                        <tr>
                            <th><i class="fas fa-edit mr-2"></i> Last Updated</th>
                            <td>{{ $user->updated_at->format('F d, Y h:i A') }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="card-footer bg-white">
                <div class="d-flex justify-content-between">
                    <a href="{{ route('users.edit', $user->id) }}" class="btn btn-info">
                        <i class="fas fa-edit mr-1"></i> Edit
                    </a>
                    <form action="{{ route('users.destroy', $user->id) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this user?')">
                            <i class="fas fa-trash mr-1"></i> Delete
                        </button>
                    </form>
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
    .widget-user .widget-user-header {
        height: 120px;
        border-top-left-radius: 0.5rem;
        border-top-right-radius: 0.5rem;
    }
    .widget-user .widget-user-image {
        top: 80px;
    }
    .badge {
        font-size: 0.9rem;
    }
</style>
@stop
