@extends('layouts.modern-admin')

@section('title', 'Create Account')

@section('page_title', 'Create New Account')

@section('page_content')
    <form action="{{ route('accounting.accounts.store') }}" method="POST">
        @csrf

        <div class="card modern-card">
            <div class="card-header modern-header">
                <h3 class="card-title">
                    <i class="fas fa-book"></i> Account Details
                </h3>
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
                            <label class="form-label">Account Code <span class="required">*</span></label>
                            <input type="text" name="code" class="form-control modern-input @error('code') is-invalid @enderror" value="{{ old('code') }}" required placeholder="e.g., 1001">
                            @error('code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">Account Group <span class="required">*</span></label>
                            <select name="account_group_id" class="form-control select2 @error('account_group_id') is-invalid @enderror" required>
                                <option value="">Select Group</option>
                                @foreach($groups as $group)
                                    <option value="{{ $group->id }}" {{ old('account_group_id') == $group->id ? 'selected' : '' }}>
                                        {{ $group->name }} ({{ ucfirst($group->nature) }})
                                    </option>
                                @endforeach
                            </select>
                            @error('account_group_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">Account Type <span class="required">*</span></label>
                            <select name="account_type" class="form-control modern-select @error('account_type') is-invalid @enderror" required>
                                <option value="">Select Type</option>
                                @foreach($accountTypes as $key => $label)
                                    <option value="{{ $key }}" {{ old('account_type') == $key ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('account_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">Opening Balance</label>
                            <input type="number" name="opening_balance" class="form-control modern-input @error('opening_balance') is-invalid @enderror" value="{{ old('opening_balance', 0) }}" step="0.01" min="0">
                            @error('opening_balance')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">Balance Type</label>
                            <select name="opening_balance_type" class="form-control modern-select @error('opening_balance_type') is-invalid @enderror">
                                <option value="debit" {{ old('opening_balance_type') == 'debit' ? 'selected' : '' }}>Debit</option>
                                <option value="credit" {{ old('opening_balance_type') == 'credit' ? 'selected' : '' }}>Credit</option>
                            </select>
                            @error('opening_balance_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bank Details (conditionally shown) -->
        <div class="card modern-card mt-4" id="bank-details" style="display: none;">
            <div class="card-header modern-header info-header">
                <h3 class="card-title">
                    <i class="fas fa-university"></i> Bank Details
                </h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="form-label">Bank Name</label>
                            <input type="text" name="bank_name" class="form-control modern-input" value="{{ old('bank_name') }}">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="form-label">Account Number</label>
                            <input type="text" name="bank_account_number" class="form-control modern-input" value="{{ old('bank_account_number') }}">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="form-label">IFSC/Routing Code</label>
                            <input type="text" name="ifsc_code" class="form-control modern-input" value="{{ old('ifsc_code') }}">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Additional Details -->
        <div class="card modern-card mt-4">
            <div class="card-header modern-header success-header">
                <h3 class="card-title">
                    <i class="fas fa-info-circle"></i> Additional Details
                </h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label class="form-label">Notes</label>
                            <textarea name="notes" class="form-control modern-textarea" rows="3">{{ old('notes') }}</textarea>
                        </div>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-md-12">
                        <div class="form-check">
                            <input type="checkbox" name="is_active" class="form-check-input" id="is_active" value="1" {{ old('is_active', 1) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">Account is Active</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Submit Buttons -->
        <div class="text-center mt-4 mb-4">
            <button type="submit" class="btn modern-btn modern-btn-primary btn-lg">
                <i class="fas fa-save"></i> Create Account
            </button>
            <a href="{{ route('accounting.accounts.index') }}" class="btn btn-outline-secondary btn-lg ml-3">
                <i class="fas fa-times"></i> Cancel
            </a>
        </div>
    </form>
@stop

@section('additional_js')
<script>
$(document).ready(function() {
    // Show/hide bank details based on account type
    $('select[name="account_type"]').on('change', function() {
        if ($(this).val() === 'bank') {
            $('#bank-details').slideDown();
        } else {
            $('#bank-details').slideUp();
        }
    }).trigger('change');
});
</script>
@stop
