@extends('layouts.modern-admin')

@section('title', 'Edit Company')
@section('page_title', 'Edit Company')

@section('header_actions')
    <a href="{{ route('tenants.index') }}" class="btn modern-btn modern-btn-outline">
        <i class="fas fa-arrow-left"></i> Back
    </a>
@stop

@section('page_content')
    <div class="card modern-card">
        <div class="card-header modern-header">
            <h3 class="card-title">
                <i class="fas fa-building"></i> Edit Company
            </h3>
        </div>
        <div class="card-body modern-card-body">
            @if($errors->any())
                <div class="alert modern-alert modern-alert-error">
                    <div class="alert-content">
                        <i class="fas fa-exclamation-triangle alert-icon"></i>
                        <div class="alert-message">
                            <strong>Error!</strong>
                            <span>Please check the form for errors</span>
                        </div>
                    </div>
                </div>
            @endif

            <form method="POST" action="{{ route('tenants.update', $tenant) }}">
                @csrf
                @method('PUT')

                <div class="form-group modern-form-group">
                    <label for="name" class="modern-label">Company Name</label>
                    <input type="text" name="name" id="name"
                           class="form-control modern-input @error('name') is-invalid @enderror"
                           value="{{ old('name', $tenant->name) }}" required>
                    @error('name')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group modern-form-group">
                    <label for="slug" class="modern-label">Slug (optional)</label>
                    <input type="text" name="slug" id="slug"
                           class="form-control modern-input @error('slug') is-invalid @enderror"
                           value="{{ old('slug', $tenant->slug) }}">
                    @error('slug')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group modern-form-group">
                    <label class="modern-label">Status</label>
                    <div class="d-flex gap-3">
                        <label class="d-flex align-items-center gap-2">
                            <input type="radio" name="is_active" value="1" {{ old('is_active', $tenant->is_active ? '1' : '0') === '1' ? 'checked' : '' }}>
                            <span>Active</span>
                        </label>
                        <label class="d-flex align-items-center gap-2">
                            <input type="radio" name="is_active" value="0" {{ old('is_active', $tenant->is_active ? '1' : '0') === '0' ? 'checked' : '' }}>
                            <span>Inactive</span>
                        </label>
                    </div>
                </div>

                <div class="d-flex gap-2 mt-4">
                    <button type="submit" class="btn modern-btn modern-btn-primary">
                        <i class="fas fa-save"></i> Update Company
                    </button>
                    <a href="{{ route('tenants.index') }}" class="btn modern-btn modern-btn-outline">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
@stop
