@extends('adminlte::page')

@section('title', 'Edit Payee')

@section('content_header')
    <h1>Edit Payee</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Edit Payee Information</h3>
        </div>
        <div class="card-body">
            <form action="{{ route('payees.update', $payee->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="form-group">
                    <label for="name">Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $payee->name) }}" required>
                    @error('name')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="category">Category <span class="text-danger">*</span></label>
                    <select class="form-control @error('category') is-invalid @enderror" id="category" name="category" required>
                        @foreach($categories as $value => $label)
                            <option value="{{ $value }}" {{ old('category', $payee->category) == $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('category')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="phone">Phone</label>
                    <input type="text" class="form-control @error('phone') is-invalid @enderror" id="phone" name="phone" value="{{ old('phone', $payee->phone) }}">
                    @error('phone')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="address">Address</label>
                    <textarea class="form-control @error('address') is-invalid @enderror" id="address" name="address" rows="3">{{ old('address', $payee->address) }}</textarea>
                    @error('address')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group opening-balance-field">
                    <label for="opening_balance">Opening Balance</label>
                    <input type="number" step="0.01" class="form-control @error('opening_balance') is-invalid @enderror" id="opening_balance" name="opening_balance" value="{{ old('opening_balance', $payee->opening_balance) }}">
                    @error('opening_balance')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                    <small class="form-text text-muted">Opening balance for non-loan payees.</small>
                </div>

                <div class="loan-fields" style="display: none;">
                    <div class="form-group">
                        <label for="principal_amount">Principal Amount</label>
                        <input type="number" step="0.01" min="0" class="form-control @error('principal_amount') is-invalid @enderror" id="principal_amount" name="principal_amount" value="{{ old('principal_amount', $payee->principal_amount) }}">
                        @error('principal_amount')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="interest_rate">Interest Rate (%)</label>
                        <input type="number" step="0.01" min="0" class="form-control @error('interest_rate') is-invalid @enderror" id="interest_rate" name="interest_rate" value="{{ old('interest_rate', $payee->interest_rate) }}">
                        @error('interest_rate')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="loan_start_date">Loan Start Date</label>
                        <input type="date" class="form-control @error('loan_start_date') is-invalid @enderror" id="loan_start_date" name="loan_start_date" value="{{ old('loan_start_date', optional($payee->loan_start_date)->format('Y-m-d')) }}">
                        @error('loan_start_date')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group sme-fields" style="display: none;">
                        <label for="loan_term_months">Loan Term (Months)</label>
                        <input type="number" min="1" class="form-control @error('loan_term_months') is-invalid @enderror" id="loan_term_months" name="loan_term_months" value="{{ old('loan_term_months', $payee->loan_term_months) }}">
                        @error('loan_term_months')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group daily-kisti-fields" style="display: none;">
                        <label for="daily_kisti_amount">Daily Kisti Amount</label>
                        <input type="number" step="0.01" min="0" class="form-control @error('daily_kisti_amount') is-invalid @enderror" id="daily_kisti_amount" name="daily_kisti_amount" value="{{ old('daily_kisti_amount', $payee->daily_kisti_amount) }}">
                        @error('daily_kisti_amount')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group daily-kisti-fields" style="display: none;">
                        <label for="daily_kisti_start_date">Daily Kisti Start Date</label>
                        <input type="date" class="form-control @error('daily_kisti_start_date') is-invalid @enderror" id="daily_kisti_start_date" name="daily_kisti_start_date" value="{{ old('daily_kisti_start_date', optional($payee->daily_kisti_start_date)->format('Y-m-d')) }}">
                        @error('daily_kisti_start_date')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="form-group">
                    <label>Current Balance</label>
                    <p class="form-control-static">{{ number_format($payee->payable_balance, 2) }}</p>
                    <small class="form-text text-muted">To adjust the balance, create a new transaction.</small>
                </div>

                <button type="submit" class="btn btn-primary">Update Payee</button>
                <a href="{{ route('payees.index') }}" class="btn btn-default">Cancel</a>
            </form>
        </div>
    </div>
@stop

@section('js')
<script>
    $(document).ready(function() {
        function toggleLoanFields() {
            const category = $('#category').val();
            const loanCategories = ['cc', 'sme', 'term_loan', 'daily_kisti'];
            const isLoan = loanCategories.includes(category);
            $('.loan-fields').toggle(isLoan);
            $('.opening-balance-field').toggle(!isLoan);
            $('.sme-fields').toggle(category === 'sme' || category === 'term_loan');
            $('.daily-kisti-fields').toggle(category === 'daily_kisti');
        }

        $('#category').on('change', toggleLoanFields);
        toggleLoanFields();
    });
</script>
@stop
