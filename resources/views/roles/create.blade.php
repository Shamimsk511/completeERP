@extends('layouts.modern-admin')

@section('title', 'Create Role')
@section('page_title', 'Create Role')

@section('header_actions')
    <a href="{{ route('roles.index') }}" class="btn modern-btn modern-btn-outline">
        <i class="fas fa-arrow-left mr-1"></i> Back to Roles
    </a>
@stop

@section('page_content')
    @if ($errors->any())
        <div class="alert alert-danger modern-alert">
            <h6 class="mb-2"><i class="fas fa-exclamation-circle mr-1"></i> Please fix the following:</h6>
            <ul class="mb-0 pl-3">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="rolex-header mb-3">
        <div>
            <h1 class="rolex-title">Create Role</h1>
            <p class="rolex-subtitle">Set role identity and attach permission access.</p>
        </div>
    </div>

    <form method="POST" action="{{ route('roles.store') }}">
        @csrf
        <div class="row">
            <div class="col-lg-4 mb-3">
                <div class="rolex-card">
                    <div class="rolex-card-header">
                        <div>
                            <h3 class="rolex-card-title"><i class="fas fa-id-badge mr-1"></i> Role Details</h3>
                            <p class="rolex-card-subtitle">Give this role a clear, readable name.</p>
                        </div>
                    </div>
                    <div class="rolex-card-body">
                        <label class="rolex-input-label" for="name">Role Name</label>
                        <input
                            id="name"
                            type="text"
                            name="name"
                            class="form-control modern-input @error('name') is-invalid @enderror"
                            value="{{ old('name') }}"
                            placeholder="e.g. Sales Manager"
                            required
                        >
                        @error('name')
                            <span class="text-danger small">{{ $message }}</span>
                        @enderror
                        <p class="rolex-help">Use business-friendly names to keep access control understandable.</p>
                    </div>
                </div>
            </div>

            <div class="col-lg-8 mb-3">
                @include('roles.partials.permission-builder', [
                    'groupedPermissions' => $groupedPermissions,
                    'selectedPermissionIds' => old('permission', []),
                ])
            </div>
        </div>

        <div class="rolex-sticky-actions">
            <span class="rolex-sticky-actions__hint">At least one permission is required to save this role.</span>
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('roles.index') }}" class="btn modern-btn modern-btn-outline">Cancel</a>
                <button type="submit" class="btn modern-btn modern-btn-primary">
                    <i class="fas fa-save mr-1"></i> Create Role
                </button>
            </div>
        </div>
    </form>
@stop

@section('additional_css')
    @include('roles.partials.styles')
@stop

@section('additional_js')
    @include('roles.partials.scripts')
@stop
