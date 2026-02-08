@extends('layouts.modern-admin')

@section('title', 'Add Godown')
@section('page_title', 'Add Godown')

@section('header_actions')
    <a href="{{ route('godowns.index') }}" class="btn modern-btn modern-btn-secondary">
        <i class="fas fa-arrow-left"></i> Back
    </a>
@stop

@section('page_content')
    <div class="card modern-card">
        <div class="card-header modern-header">
            <h3 class="card-title"><i class="fas fa-warehouse"></i> Godown Details</h3>
        </div>
        <div class="card-body modern-card-body">
            <form action="{{ route('godowns.store') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label for="name">Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" id="name" class="form-control" value="{{ old('name') }}" required>
                    @error('name')<small class="text-danger">{{ $message }}</small>@enderror
                </div>
                <div class="form-group">
                    <label for="location">Location</label>
                    <input type="text" name="location" id="location" class="form-control" value="{{ old('location') }}">
                    @error('location')<small class="text-danger">{{ $message }}</small>@enderror
                </div>
                <div class="form-group">
                    <div class="custom-control custom-switch">
                        <input type="checkbox" class="custom-control-input" id="is_default" name="is_default" value="1" {{ old('is_default') ? 'checked' : '' }}>
                        <label class="custom-control-label" for="is_default">Set as Default Godown</label>
                    </div>
                </div>
                <div class="form-group">
                    <div class="custom-control custom-switch">
                        <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                        <label class="custom-control-label" for="is_active">Active</label>
                    </div>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn modern-btn modern-btn-primary">
                        <i class="fas fa-save"></i> Save
                    </button>
                    <a href="{{ route('godowns.index') }}" class="btn modern-btn modern-btn-outline">Cancel</a>
                </div>
            </form>
        </div>
    </div>
@stop
