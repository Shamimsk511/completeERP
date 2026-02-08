@extends('layouts.modern-admin')

@section('title', 'Create Company')

@section('page_title', 'Create Company')

@section('header_actions')
    <a href="{{ route('companies.index') }}" class="btn modern-btn modern-btn-outline">
        <i class="fas fa-arrow-left"></i> Back to Companies
    </a>
@stop

@section('page_content')
    @if($errors->any())
        <div class="alert alert-danger">
            <strong>Please fix the errors below.</strong>
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('companies.store') }}" method="POST" id="company-form">
        @csrf

        <div class="card modern-card">
            <div class="card-header modern-header">
                <h3 class="card-title"><i class="fas fa-building"></i> Company Details</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-lg-6 mb-4">
                        <div class="info-section">
                            <div class="section-header">
                                <i class="fas fa-info-circle"></i> Company Info
                            </div>
                            <div class="section-content">
                                <div class="form-group">
                                    <label for="name" class="form-label">Company Name <span class="required">*</span></label>
                                    <input type="text"
                                           name="name"
                                           id="name"
                                           class="form-control modern-input @error('name') is-invalid @enderror"
                                           value="{{ old('name') }}"
                                           placeholder="Enter company name"
                                           required
                                           autocomplete="organization">
                                    @error('name')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="type" class="form-label">Company Type <span class="required">*</span></label>
                                    <select name="type"
                                            id="type"
                                            class="form-control modern-select @error('type') is-invalid @enderror"
                                            required>
                                        <option value="supplier" {{ old('type', 'supplier') === 'supplier' ? 'selected' : '' }}>Supplier</option>
                                        <option value="brand" {{ old('type') === 'brand' ? 'selected' : '' }}>Brand</option>
                                        <option value="both" {{ old('type') === 'both' ? 'selected' : '' }}>Both</option>
                                    </select>
                                    <small class="form-text">Supplier, Brand, or both.</small>
                                    @error('type')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group mb-0">
                                    <label for="description" class="form-label">Company Description</label>
                                    <textarea name="description"
                                              id="description"
                                              class="form-control modern-textarea @error('description') is-invalid @enderror"
                                              rows="4"
                                              placeholder="Describe the company (optional)"
                                              maxlength="500">{{ old('description') }}</textarea>
                                    @error('description')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-6 mb-4">
                        <div class="info-section">
                            <div class="section-header">
                                <i class="fas fa-address-book"></i> Contact & Balance
                            </div>
                            <div class="section-content">
                                <div class="form-group">
                                    <label for="opening_balance" class="form-label">Opening Balance</label>
                                    <input type="number"
                                           name="opening_balance"
                                           id="opening_balance"
                                           class="form-control modern-input @error('opening_balance') is-invalid @enderror"
                                           value="{{ old('opening_balance') }}"
                                           step="0.01"
                                           min="0"
                                           placeholder="0.00">
                                    <small class="form-text">Supplier only.</small>
                                    @error('opening_balance')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="opening_balance_direction" class="form-label">Opening Balance Meaning</label>
                                    <select name="opening_balance_direction"
                                            id="opening_balance_direction"
                                            class="form-control modern-select @error('opening_balance_direction') is-invalid @enderror">
                                        <option value="we_owe" {{ old('opening_balance_direction', 'we_owe') === 'we_owe' ? 'selected' : '' }}>
                                            We owe this supplier
                                        </option>
                                        <option value="they_owe" {{ old('opening_balance_direction') === 'they_owe' ? 'selected' : '' }}>
                                            Supplier owes us
                                        </option>
                                    </select>
                                    <small class="form-text">Simple terms instead of debit/credit.</small>
                                    @error('opening_balance_direction')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="contact" class="form-label">Primary Contact</label>
                                    <input type="text"
                                           name="contact"
                                           id="contact"
                                           class="form-control modern-input @error('contact') is-invalid @enderror"
                                           value="{{ old('contact') }}"
                                           placeholder="Phone or email">
                                    @error('contact')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group mb-0">
                                    <label for="address" class="form-label">Company Address</label>
                                    <textarea name="address"
                                              id="address"
                                              class="form-control modern-textarea @error('address') is-invalid @enderror"
                                              rows="3"
                                              placeholder="Enter address (optional)"
                                              maxlength="300">{{ old('address') }}</textarea>
                                    @error('address')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end">
                    <a href="{{ route('companies.index') }}" class="btn modern-btn modern-btn-outline mr-2">
                        Cancel
                    </a>
                    <button type="submit" class="btn modern-btn modern-btn-success">
                        <i class="fas fa-building"></i> Create Company
                    </button>
                </div>
            </div>
        </div>
    </form>
@stop
