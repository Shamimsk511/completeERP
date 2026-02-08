@extends('layouts.modern-admin')

@section('title', 'Add New Payee')
@section('page_title', 'Add New Payee')

@section('header_actions')
    <a href="{{ route('payees.index') }}" class="btn modern-btn modern-btn-outline">
        <i class="fas fa-arrow-left"></i> Back to Payees
    </a>
    <a href="{{ route('payable-transactions.create') }}" class="btn modern-btn modern-btn-outline">
        <i class="fas fa-credit-card"></i> Add Transaction
    </a>
@stop

@section('page_content')
    @if ($errors->any())
        <div class="alert alert-danger modern-alert">
            <i class="fas fa-exclamation-circle"></i>
            <strong>Validation Error:</strong>
            <ul class="mb-0 mt-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('payees.store') }}" method="POST" id="payee-form">
        @csrf

        <div class="row">
            <div class="col-lg-8">
                <div class="card modern-card">
                    <div class="card-header modern-header">
                        <h3 class="card-title"><i class="fas fa-user"></i> Payee Details</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                                    <input type="text"
                                           class="form-control modern-input @error('name') is-invalid @enderror"
                                           id="name"
                                           name="name"
                                           value="{{ old('name') }}"
                                           required
                                           placeholder="Enter payee name">
                                    @error('name')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="category" class="form-label">Category <span class="text-danger">*</span></label>
                                    <select class="form-control modern-select @error('category') is-invalid @enderror"
                                            id="category"
                                            name="category"
                                            required>
                                        <option value="">Select Payee Category</option>
                                        @foreach($categories as $value => $label)
                                            <option value="{{ $value }}" {{ old('category') == $value ? 'selected' : '' }}>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    @error('category')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="phone" class="form-label">Phone</label>
                                    <input type="text"
                                           class="form-control modern-input @error('phone') is-invalid @enderror"
                                           id="phone"
                                           name="phone"
                                           value="{{ old('phone') }}"
                                           placeholder="Enter phone number">
                                    @error('phone')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="address" class="form-label">Address</label>
                                    <textarea class="form-control modern-input @error('address') is-invalid @enderror"
                                              id="address"
                                              name="address"
                                              rows="3"
                                              placeholder="Enter address">{{ old('address') }}</textarea>
                                    @error('address')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card modern-card mt-4">
                    <div class="card-header modern-header">
                        <h3 class="card-title"><i class="fas fa-wallet"></i> Account Setup</h3>
                    </div>
                    <div class="card-body">
                        <div class="opening-balance-field">
                            <div class="form-group">
                                <label for="opening_balance" class="form-label">Opening Balance <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">৳</span>
                                    </div>
                                    <input type="number"
                                           step="0.01"
                                           class="form-control modern-input @error('opening_balance') is-invalid @enderror"
                                           id="opening_balance"
                                           name="opening_balance"
                                           value="{{ old('opening_balance', '0.00') }}"
                                           placeholder="0.00">
                                </div>
                                <small class="form-text text-muted">
                                    Opening balance is used for non-loan payees.
                                </small>
                                @error('opening_balance')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="loan-fields" style="display: none;">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="principal_amount" class="form-label">Principal Amount</label>
                                        <input type="number" step="0.01" min="0"
                                               class="form-control modern-input @error('principal_amount') is-invalid @enderror"
                                               id="principal_amount"
                                               name="principal_amount"
                                               value="{{ old('principal_amount') }}"
                                               placeholder="0.00">
                                        @error('principal_amount')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="interest_rate" class="form-label">Interest Rate (%)</label>
                                        <input type="number" step="0.01" min="0"
                                               class="form-control modern-input @error('interest_rate') is-invalid @enderror"
                                               id="interest_rate"
                                               name="interest_rate"
                                               value="{{ old('interest_rate') }}"
                                               placeholder="0.00">
                                        @error('interest_rate')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="loan_start_date" class="form-label">Loan Start Date</label>
                                        <input type="date"
                                               class="form-control modern-input @error('loan_start_date') is-invalid @enderror"
                                               id="loan_start_date"
                                               name="loan_start_date"
                                               value="{{ old('loan_start_date') }}">
                                        @error('loan_start_date')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 sme-fields" style="display: none;">
                                    <div class="form-group">
                                        <label for="loan_term_months" class="form-label">Loan Term (Months)</label>
                                        <input type="number" min="1"
                                               class="form-control modern-input @error('loan_term_months') is-invalid @enderror"
                                               id="loan_term_months"
                                               name="loan_term_months"
                                               value="{{ old('loan_term_months') }}">
                                        @error('loan_term_months')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6 daily-kisti-fields" style="display: none;">
                                    <div class="form-group">
                                        <label for="daily_kisti_amount" class="form-label">Daily Kisti Amount</label>
                                        <input type="number" step="0.01" min="0"
                                               class="form-control modern-input @error('daily_kisti_amount') is-invalid @enderror"
                                               id="daily_kisti_amount"
                                               name="daily_kisti_amount"
                                               value="{{ old('daily_kisti_amount') }}"
                                               placeholder="0.00">
                                        @error('daily_kisti_amount')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6 daily-kisti-fields" style="display: none;">
                                    <div class="form-group">
                                        <label for="daily_kisti_start_date" class="form-label">Daily Kisti Start Date</label>
                                        <input type="date"
                                               class="form-control modern-input @error('daily_kisti_start_date') is-invalid @enderror"
                                               id="daily_kisti_start_date"
                                               name="daily_kisti_start_date"
                                               value="{{ old('daily_kisti_start_date') }}">
                                        @error('daily_kisti_start_date')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card modern-card">
                    <div class="card-header modern-header">
                        <h3 class="card-title"><i class="fas fa-info-circle"></i> Category Guide</h3>
                    </div>
                    <div class="card-body">
                        <p class="text-muted mb-2">Choose a category to unlock the right fields:</p>
                        <ul class="text-muted mb-0">
                            <li>Suppliers/Banks use opening balance.</li>
                            <li>Loan categories require principal and rate.</li>
                            <li>Daily Kisti shows daily payment fields.</li>
                        </ul>
                    </div>
                </div>

                <div class="card modern-card mt-4">
                    <div class="card-header modern-header">
                        <h3 class="card-title"><i class="fas fa-check-circle"></i> Actions</h3>
                    </div>
                    <div class="card-body">
                        <button type="submit" class="btn modern-btn modern-btn-primary btn-block mb-2">
                            <i class="fas fa-save"></i> Save Payee
                        </button>
                        <a href="{{ route('payees.index') }}" class="btn modern-btn modern-btn-outline btn-block">
                            Cancel
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </form>
@stop

@push('js')
<script>
    $(document).ready(function() {
        const toggleLoanFields = () => {
            const category = $('#category').val();
            const loanCategories = ['cc', 'sme', 'term_loan', 'daily_kisti'];
            const isLoan = loanCategories.includes(category);
            $('.loan-fields').toggle(isLoan);
            $('.opening-balance-field').toggle(!isLoan);
            $('.sme-fields').toggle(category === 'sme' || category === 'term_loan');
            $('.daily-kisti-fields').toggle(category === 'daily_kisti');
        };

        $('#category').on('change', toggleLoanFields);
        toggleLoanFields();
    });
</script>
@endpush
