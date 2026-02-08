@extends('layouts.modern-admin')

@section('title', 'Add Bank')
@section('page_title', 'Add Bank Account')

@section('page_content')
    <form action="{{ route('accounting.banks.store') }}" method="POST">
        @csrf

        <div class="card modern-card">
            <div class="card-header modern-header">
                <h3 class="card-title"><i class="fas fa-university"></i> Bank Details</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">Account Name <span class="required">*</span></label>
                            <input type="text" name="name" class="form-control modern-input @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">Account Code</label>
                            <input type="text" class="form-control modern-input" value="{{ $codePreview }}" readonly>
                            <small class="text-muted">Auto-generated for new banks.</small>
                        </div>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="form-label">Bank Name</label>
                            <input type="text" name="bank_name" class="form-control modern-input @error('bank_name') is-invalid @enderror" value="{{ old('bank_name') }}">
                            @error('bank_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="form-label">Account Number</label>
                            <input type="text" name="bank_account_number" class="form-control modern-input @error('bank_account_number') is-invalid @enderror" value="{{ old('bank_account_number') }}">
                            @error('bank_account_number')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="form-label">IFSC/Routing Code</label>
                            <input type="text" name="ifsc_code" class="form-control modern-input @error('ifsc_code') is-invalid @enderror" value="{{ old('ifsc_code') }}">
                            @error('ifsc_code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card modern-card mt-4">
            <div class="card-header modern-header info-header">
                <h3 class="card-title"><i class="fas fa-wallet"></i> Opening Balance</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">Opening Balance</label>
                            <input type="number" name="opening_balance" class="form-control modern-input @error('opening_balance') is-invalid @enderror" step="0.01" min="0" value="{{ old('opening_balance', 0) }}">
                            @error('opening_balance')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">Balance Type</label>
                            <select name="opening_balance_type" class="form-control modern-select @error('opening_balance_type') is-invalid @enderror">
                                <option value="debit" {{ old('opening_balance_type', 'debit') === 'debit' ? 'selected' : '' }}>Debit</option>
                                <option value="credit" {{ old('opening_balance_type') === 'credit' ? 'selected' : '' }}>Credit</option>
                            </select>
                            @error('opening_balance_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card modern-card mt-4">
            <div class="card-header modern-header success-header">
                <h3 class="card-title"><i class="fas fa-info-circle"></i> Notes</h3>
            </div>
            <div class="card-body">
                <div class="form-group">
                    <label class="form-label">Notes</label>
                    <textarea name="notes" class="form-control modern-textarea" rows="3">{{ old('notes') }}</textarea>
                </div>
                <div class="form-check mt-3">
                    <input type="checkbox" name="is_active" class="form-check-input" id="is_active" value="1" {{ old('is_active', 1) ? 'checked' : '' }}>
                    <label class="form-check-label" for="is_active">Bank account is active</label>
                </div>
            </div>
        </div>

        <div class="text-center mt-4 mb-4">
            <button type="submit" class="btn modern-btn modern-btn-primary btn-lg">
                <i class="fas fa-save"></i> Create Bank
            </button>
            <a href="{{ route('accounting.banks.index') }}" class="btn btn-outline-secondary btn-lg ml-3">
                <i class="fas fa-times"></i> Cancel
            </a>
        </div>
    </form>
@stop
