@extends('layouts.modern-admin')

@section('title', 'Edit Colorent')

@section('page_title', 'Edit Colorent')

@section('header_actions')
    <a href="{{ route('colorents.management') }}" class="btn modern-btn modern-btn-outline">
        <i class="fas fa-arrow-left"></i> Back
    </a>
@stop

@section('page_content')
    <div class="card modern-card">
        <div class="card-header modern-header">
            <h3 class="card-title"><i class="fas fa-pen"></i> Update Colorent</h3>
        </div>
        <div class="card-body modern-card-body">
            <form method="POST" action="{{ route('colorents.update', $colorent->id) }}">
                @csrf
                @method('PUT')
                <div class="form-group">
                    <label for="name">Colorent Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" id="name" class="form-control modern-input @error('name') is-invalid @enderror" value="{{ old('name', $colorent->name) }}" required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="price">Default Price (৳)</label>
                    <input type="number" name="price" id="price" class="form-control modern-input @error('price') is-invalid @enderror" value="{{ old('price', $colorent->price) }}" min="0" step="0.01">
                    @error('price')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="text-muted d-block mt-1">Stock is managed via Purchase and Pour actions.</small>
                </div>
                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('colorents.management') }}" class="btn modern-btn modern-btn-outline">Cancel</a>
                    <button type="submit" class="btn modern-btn modern-btn-primary">
                        <i class="fas fa-save"></i> Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
@stop
