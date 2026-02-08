@extends('layouts.modern-admin')

@section('title', 'Godowns')
@section('page_title', 'Godowns')

@section('header_actions')
    <a href="{{ route('godowns.create') }}" class="btn modern-btn modern-btn-primary">
        <i class="fas fa-plus"></i> Add Godown
    </a>
@stop

@section('page_content')
    @if(session('success'))
        <div class="alert modern-alert modern-alert-success">
            <div class="alert-content">
                <i class="fas fa-check-circle alert-icon"></i>
                <div class="alert-message">
                    <strong>Success!</strong>
                    <span>{{ session('success') }}</span>
                </div>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="alert modern-alert modern-alert-error">
            <div class="alert-content">
                <i class="fas fa-exclamation-triangle alert-icon"></i>
                <div class="alert-message">
                    <strong>Error!</strong>
                    <span>{{ session('error') }}</span>
                </div>
            </div>
        </div>
    @endif

    <div class="card modern-card">
        <div class="card-header modern-header">
            <div class="header-content">
                <div class="header-title">
                    <i class="fas fa-warehouse header-icon"></i>
                    <h3 class="card-title">Godown Management</h3>
                </div>
                <div class="header-badge">
                    <span class="badge modern-badge">{{ $godowns->count() }} Godowns</span>
                </div>
            </div>
        </div>
        <div class="card-body modern-card-body">
            <div class="table-responsive">
                <table class="table modern-table">
                    <thead class="modern-thead">
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Location</th>
                            <th>Default</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody class="modern-tbody">
                        @forelse($godowns as $godown)
                            <tr>
                                <td>{{ $godown->id }}</td>
                                <td>{{ $godown->name }}</td>
                                <td>{{ $godown->location ?? '-' }}</td>
                                <td>
                                    @if($godown->is_default)
                                        <span class="badge badge-success">Default</span>
                                    @else
                                        <span class="badge badge-secondary">No</span>
                                    @endif
                                </td>
                                <td>
                                    @if($godown->is_active)
                                        <span class="badge badge-primary">Active</span>
                                    @else
                                        <span class="badge badge-warning">Inactive</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('godowns.edit', $godown) }}" class="btn btn-sm modern-btn modern-btn-warning">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('godowns.destroy', $godown) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this godown?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm modern-btn modern-btn-danger" {{ $godown->is_default ? 'disabled' : '' }}>
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted">No godowns found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@stop
