@extends('layouts.modern-admin')

@section('title', 'Edit Bank Transaction')
@section('page_title', 'Edit Bank Transaction')

@section('page_content')
    <form action="{{ route('accounting.bank-transactions.update', $bankTransaction) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="card modern-card">
            <div class="card-header modern-header">
                <h3 class="card-title"><i class="fas fa-exchange-alt"></i> Transaction Details</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="form-label">Date <span class="required">*</span></label>
                            <input type="date" name="transaction_date" class="form-control modern-input @error('transaction_date') is-invalid @enderror" value="{{ old('transaction_date', $bankTransaction->transaction_date->toDateString()) }}" required>
                            @error('transaction_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="form-label">Bank Account <span class="required">*</span></label>
                            <select name="bank_account_id" class="form-control modern-select @error('bank_account_id') is-invalid @enderror" required>
                                <option value="">Select Bank</option>
                                @foreach($banks as $bank)
                                    <option value="{{ $bank->id }}" {{ old('bank_account_id', $bankTransaction->bank_account_id) == $bank->id ? 'selected' : '' }}>
                                        {{ $bank->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('bank_account_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="form-label">Type <span class="required">*</span></label>
                            <select name="transaction_type" id="transaction_type" class="form-control modern-select @error('transaction_type') is-invalid @enderror" required>
                                <option value="">Select Type</option>
                                <option value="deposit" {{ old('transaction_type', $bankTransaction->transaction_type) === 'deposit' ? 'selected' : '' }}>Deposit</option>
                                <option value="withdraw" {{ old('transaction_type', $bankTransaction->transaction_type) === 'withdraw' ? 'selected' : '' }}>Withdraw</option>
                                <option value="adjustment" {{ old('transaction_type', $bankTransaction->transaction_type) === 'adjustment' ? 'selected' : '' }}>Adjustment</option>
                            </select>
                            @error('transaction_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="form-label">Amount <span class="required">*</span></label>
                            <input type="number" name="amount" class="form-control modern-input @error('amount') is-invalid @enderror" step="0.01" min="0.01" value="{{ old('amount', $bankTransaction->amount) }}" required>
                            @error('amount')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-4 transfer-fields">
                        <div class="form-group">
                            <label class="form-label">Cash/Bank Account <span class="required">*</span></label>
                            <select name="counter_account_id" id="transfer_counter_account_id" class="form-control modern-select @error('counter_account_id') is-invalid @enderror">
                                <option value="">Select Account</option>
                                <optgroup label="Cash Accounts">
                                    @foreach($cashAccounts as $account)
                                        <option value="{{ $account->id }}" {{ old('counter_account_id', $bankTransaction->counter_account_id) == $account->id ? 'selected' : '' }}>
                                            {{ $account->name }}
                                        </option>
                                    @endforeach
                                </optgroup>
                                <optgroup label="Bank Accounts">
                                    @foreach($banks as $bank)
                                        <option value="{{ $bank->id }}" {{ old('counter_account_id', $bankTransaction->counter_account_id) == $bank->id ? 'selected' : '' }}>
                                            {{ $bank->name }}
                                        </option>
                                    @endforeach
                                </optgroup>
                            </select>
                            @error('counter_account_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-4 adjustment-fields" style="display: none;">
                        <div class="form-group">
                            <label class="form-label">Adjustment Direction <span class="required">*</span></label>
                            <select name="direction" class="form-control modern-select @error('direction') is-invalid @enderror">
                                <option value="">Select Direction</option>
                                <option value="out" {{ old('direction', $bankTransaction->direction) === 'out' ? 'selected' : '' }}>Bank Charge (Out)</option>
                                <option value="in" {{ old('direction', $bankTransaction->direction) === 'in' ? 'selected' : '' }}>Bank Income (In)</option>
                            </select>
                            @error('direction')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row mt-3 adjustment-fields" style="display: none;">
                    <div class="col-md-8">
                        <div class="form-group">
                            <label class="form-label">Expense/Income Account <span class="required">*</span></label>
                            <select name="counter_account_id" id="adjustment_counter_account_id" class="form-control modern-select @error('counter_account_id') is-invalid @enderror" disabled>
                                <option value="">Select Account</option>
                                <optgroup label="Expense Accounts">
                                    @foreach($expenseAccounts as $account)
                                        <option value="{{ $account->id }}" {{ old('counter_account_id', $bankTransaction->counter_account_id) == $account->id ? 'selected' : '' }}>
                                            {{ $account->name }}
                                        </option>
                                    @endforeach
                                </optgroup>
                                <optgroup label="Income Accounts">
                                    @foreach($incomeAccounts as $account)
                                        <option value="{{ $account->id }}" {{ old('counter_account_id', $bankTransaction->counter_account_id) == $account->id ? 'selected' : '' }}>
                                            {{ $account->name }}
                                        </option>
                                    @endforeach
                                </optgroup>
                            </select>
                            @error('counter_account_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">Reference</label>
                            <input type="text" name="reference" class="form-control modern-input @error('reference') is-invalid @enderror" value="{{ old('reference', $bankTransaction->reference) }}">
                            @error('reference')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">Description</label>
                            <input type="text" name="description" class="form-control modern-input @error('description') is-invalid @enderror" value="{{ old('description', $bankTransaction->description) }}">
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="text-center mt-4 mb-4">
            <button type="submit" class="btn modern-btn modern-btn-primary btn-lg">
                <i class="fas fa-save"></i> Update Transaction
            </button>
            <a href="{{ route('accounting.bank-transactions.index') }}" class="btn btn-outline-secondary btn-lg ml-3">
                <i class="fas fa-times"></i> Cancel
            </a>
        </div>
    </form>
@stop

@section('additional_js')
<script>
    $(document).ready(function() {
        const toggleFields = function() {
            const type = $('#transaction_type').val();
            if (type === 'adjustment') {
                $('.transfer-fields').hide();
                $('.adjustment-fields').show();
                $('#transfer_counter_account_id').prop('disabled', true);
                $('#adjustment_counter_account_id').prop('disabled', false);
            } else {
                $('.transfer-fields').show();
                $('.adjustment-fields').hide();
                $('#transfer_counter_account_id').prop('disabled', false);
                $('#adjustment_counter_account_id').prop('disabled', true);
            }
        };

        $('#transaction_type').on('change', toggleFields);
        toggleFields();
    });
</script>
@stop
