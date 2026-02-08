@extends('layouts.modern-admin')

@section('title', 'New Bank Transaction')
@section('page_title', 'New Bank Transaction')

@section('page_content')
    <form action="{{ route('accounting.bank-transactions.store') }}" method="POST">
        @csrf

        <div class="card modern-card">
            <div class="card-header modern-header">
                <h3 class="card-title"><i class="fas fa-exchange-alt"></i> Transaction Details</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="form-label">Date <span class="required">*</span></label>
                            <input type="date" name="transaction_date" class="form-control modern-input @error('transaction_date') is-invalid @enderror" value="{{ old('transaction_date', now()->toDateString()) }}" required>
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
                                    <option value="{{ $bank->id }}"
                                        data-balance="{{ $bank->current_balance }}"
                                        data-balance-type="{{ $bank->current_balance_type }}"
                                        {{ old('bank_account_id', $selectedBank) == $bank->id ? 'selected' : '' }}>
                                        {{ $bank->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('bank_account_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted d-block mt-1" id="bank-balance-display">Current Balance: -</small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="form-label">Type <span class="required">*</span></label>
                            <select name="transaction_type" id="transaction_type" class="form-control modern-select @error('transaction_type') is-invalid @enderror" required>
                                <option value="">Select Type</option>
                                <option value="deposit" {{ old('transaction_type', $selectedType) === 'deposit' ? 'selected' : '' }}>Deposit</option>
                                <option value="withdraw" {{ old('transaction_type', $selectedType) === 'withdraw' ? 'selected' : '' }}>Withdraw</option>
                                <option value="adjustment" {{ old('transaction_type', $selectedType) === 'adjustment' ? 'selected' : '' }}>Adjustment</option>
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
                            <input type="number" name="amount" class="form-control modern-input @error('amount') is-invalid @enderror" step="0.01" min="0.01" value="{{ old('amount') }}" required>
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
                                        <option value="{{ $account->id }}"
                                            data-balance="{{ $account->current_balance }}"
                                            data-balance-type="{{ $account->current_balance_type }}"
                                            {{ old('counter_account_id') == $account->id ? 'selected' : '' }}>
                                            {{ $account->name }}
                                        </option>
                                    @endforeach
                                </optgroup>
                                <optgroup label="Bank Accounts">
                                    @foreach($banks as $bank)
                                        <option value="{{ $bank->id }}"
                                            data-balance="{{ $bank->current_balance }}"
                                            data-balance-type="{{ $bank->current_balance_type }}"
                                            {{ old('counter_account_id') == $bank->id ? 'selected' : '' }}>
                                            {{ $bank->name }}
                                        </option>
                                    @endforeach
                                </optgroup>
                            </select>
                            @error('counter_account_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted d-block">Transfer between cash and bank accounts.</small>
                            <small class="text-muted d-block mt-1" id="counter-balance-display">Current Balance: -</small>
                        </div>
                    </div>
                    <div class="col-md-4 adjustment-fields" style="display: none;">
                        <div class="form-group">
                            <label class="form-label">Adjustment Direction <span class="required">*</span></label>
                            <select name="direction" class="form-control modern-select @error('direction') is-invalid @enderror">
                                <option value="">Select Direction</option>
                                <option value="out" {{ old('direction') === 'out' ? 'selected' : '' }}>Bank Charge (Out)</option>
                                <option value="in" {{ old('direction') === 'in' ? 'selected' : '' }}>Bank Income (In)</option>
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
                                        <option value="{{ $account->id }}" {{ old('counter_account_id') == $account->id ? 'selected' : '' }}>
                                            {{ $account->name }}
                                        </option>
                                    @endforeach
                                </optgroup>
                                <optgroup label="Income Accounts">
                                    @foreach($incomeAccounts as $account)
                                        <option value="{{ $account->id }}" {{ old('counter_account_id') == $account->id ? 'selected' : '' }}>
                                            {{ $account->name }}
                                        </option>
                                    @endforeach
                                </optgroup>
                            </select>
                            @error('counter_account_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Use expense accounts for bank charges and income accounts for interest.</small>
                        </div>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">Reference</label>
                            <input type="text" name="reference" class="form-control modern-input @error('reference') is-invalid @enderror" value="{{ old('reference') }}">
                            @error('reference')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">Description</label>
                            <input type="text" name="description" class="form-control modern-input @error('description') is-invalid @enderror" value="{{ old('description') }}">
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
                <i class="fas fa-save"></i> Save Transaction
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

        const formatBalance = function(balance, type) {
            if (balance === undefined || balance === null || balance === '') {
                return '-';
            }
            const amount = Number(balance);
            if (Number.isNaN(amount)) {
                return '-';
            }
            const label = (type || 'debit').toString().toUpperCase();
            return `৳${amount.toFixed(2)} (${label})`;
        };

        const updateBankBalance = function() {
            const selected = $('select[name="bank_account_id"] option:selected');
            const balance = selected.data('balance');
            const type = selected.data('balance-type');
            $('#bank-balance-display').text(`Current Balance: ${formatBalance(balance, type)}`);
        };

        const updateCounterBalance = function() {
            const selected = $('#transfer_counter_account_id option:selected');
            const balance = selected.data('balance');
            const type = selected.data('balance-type');
            $('#counter-balance-display').text(`Current Balance: ${formatBalance(balance, type)}`);
        };

        $('select[name="bank_account_id"]').on('change', updateBankBalance);
        $('#transfer_counter_account_id').on('change', updateCounterBalance);
        $('#transaction_type').on('change', toggleFields);

        toggleFields();
        updateBankBalance();
        updateCounterBalance();
    });
</script>
@stop
