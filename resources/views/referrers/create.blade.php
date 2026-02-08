@extends('layouts.modern-admin')

@section('title', 'Add Referrer')
@section('page_title', 'Add Referrer')

@section('header_actions')
    <a href="{{ route('referrers.index') }}" class="btn modern-btn modern-btn-secondary">
        <i class="fas fa-arrow-left"></i> Back to Referrers
    </a>
@stop

@section('page_content')
    <div class="card modern-card">
        <div class="card-header modern-header success-header">
            <h3 class="card-title">
                <i class="fas fa-user-tag"></i> Referrer Information
            </h3>
        </div>
        <div class="card-body">
            @if ($errors->any())
                <div class="modern-alert modern-alert-danger mb-4">
                    <div class="alert-icon">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div class="alert-content">
                        <h5>Whoops! There were some problems with your input.</h5>
                        <ul class="error-list">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            <form action="{{ route('referrers.store') }}" method="POST" class="modern-form">
                @csrf
                <div class="form-grid">
                    <div class="form-group-modern full-width">
                        <label for="name" class="modern-label">
                            <i class="fas fa-user"></i>
                            Referrer Name <span class="required">*</span>
                        </label>
                        <input type="text" name="name" id="name" class="modern-input" value="{{ old('name') }}" required>
                    </div>

                    <div class="form-group-modern half-width">
                        <label for="phone" class="modern-label">
                            <i class="fas fa-phone"></i>
                            Phone
                        </label>
                        <input type="text" name="phone" id="phone" class="modern-input" value="{{ old('phone') }}">
                    </div>

                    <div class="form-group-modern half-width">
                        <label for="profession" class="modern-label">
                            <i class="fas fa-briefcase"></i>
                            Profession
                        </label>
                        <input type="text" name="profession" id="profession" class="modern-input" value="{{ old('profession') }}">
                    </div>

                    <div class="form-group-modern full-width">
                        <label for="note" class="modern-label">
                            <i class="fas fa-sticky-note"></i>
                            Note
                        </label>
                        <textarea name="note" id="note" class="modern-input" rows="3">{{ old('note') }}</textarea>
                    </div>

                    <div class="form-group-modern half-width">
                        <label class="modern-label">
                            <i class="fas fa-hand-holding-usd"></i>
                            Compensation
                        </label>
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="compensation_enabled" name="compensation_enabled" value="1" {{ old('compensation_enabled') ? 'checked' : '' }}>
                            <label class="custom-control-label" for="compensation_enabled">Enable Compensation</label>
                        </div>
                    </div>

                    <div class="form-group-modern half-width">
                        <label class="modern-label">
                            <i class="fas fa-gift"></i>
                            Gift
                        </label>
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="gift_enabled" name="gift_enabled" value="1" {{ old('gift_enabled') ? 'checked' : '' }}>
                            <label class="custom-control-label" for="gift_enabled">Enable Gift</label>
                        </div>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn modern-btn modern-btn-success">
                        <i class="fas fa-save"></i> Create Referrer
                    </button>
                    <a href="{{ route('referrers.index') }}" class="btn modern-btn modern-btn-secondary">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
@stop
