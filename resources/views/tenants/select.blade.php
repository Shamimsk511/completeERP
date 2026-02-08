@extends('layouts.modern-admin')

@section('title', 'Select Company')
@section('page_title', 'Select Company')

@section('header_actions')
    @if(auth()->user()?->hasRole('Super Admin'))
        <a href="{{ route('tenants.index') }}" class="btn modern-btn modern-btn-outline">
            <i class="fas fa-building"></i> Manage Companies
        </a>
    @endif
@stop

@section('page_content')
    <div class="card modern-card">
        <div class="card-header modern-header">
            <h3 class="card-title">Choose Company</h3>
        </div>
        <div class="card-body">
            @if(auth()->user()?->hasRole('Super Admin'))
                <div class="d-flex justify-content-end mb-3">
                    <a href="{{ route('tenants.index') }}" class="btn modern-btn modern-btn-outline">
                        Manage Companies
                    </a>
                </div>
            @endif
            <form method="POST" action="{{ route('tenants.switch') }}">
                @csrf
                <div class="form-group">
                    <label for="tenant_id" class="form-label">Company</label>
                    <select name="tenant_id" id="tenant_id" class="form-control" required>
                        <option value="">Select Company</option>
                        @foreach($tenants as $tenant)
                            <option value="{{ $tenant->id }}" {{ (int) $currentTenantId === (int) $tenant->id ? 'selected' : '' }}>
                                {{ $tenant->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <button type="submit" class="btn modern-btn modern-btn-primary">
                    Switch Company
                </button>
            </form>
        </div>
    </div>

    @if(auth()->user()?->hasRole('Super Admin'))
        <div class="card modern-card">
            <div class="card-header modern-header warning-header">
                <h3 class="card-title">Assign Existing Data</h3>
            </div>
            <div class="card-body">
                <p class="text-muted">
                    This will assign all existing records without a company to the selected company.
                </p>
                <form method="POST" action="{{ route('tenants.assign-existing') }}">
                    @csrf
                    <div class="form-group">
                        <label for="assign_tenant_id" class="form-label">Company</label>
                        <select name="tenant_id" id="assign_tenant_id" class="form-control" required>
                            <option value="">Select Company</option>
                            @foreach($tenants as $tenant)
                                <option value="{{ $tenant->id }}">{{ $tenant->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="btn modern-btn modern-btn-warning">
                        Assign Existing Data
                    </button>
                </form>

                @if(session('assign_results'))
                    <div class="mt-3">
                        <h6>Updated Records</h6>
                        <ul class="mb-0">
                            @foreach(session('assign_results') as $table => $count)
                                <li><strong>{{ $table }}:</strong> {{ $count }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>
        </div>
    @endif
@stop
