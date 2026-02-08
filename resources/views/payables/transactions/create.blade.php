@extends('layouts.modern-admin')

@section('title', 'Add Payable Transaction')

@section('page_title', 'Add Payable Transaction')

@section('header_actions')
    <a href="{{ route('payable-transactions.index') }}" class="btn modern-btn modern-btn-outline">
        <i class="fas fa-arrow-left"></i> Back to Transactions
    </a>
@stop

@section('page_content')
    <form action="{{ route('payable-transactions.store') }}" method="POST" id="payable-transaction-form">
        @csrf
        @if(request()->filled('installment_id'))
            <input type="hidden" name="installment_id" value="{{ request('installment_id') }}">
        @endif

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

        <div class="card modern-card">
            <div class="card-header modern-header">
                <h3 class="card-title"><i class="fas fa-user-tie"></i> Payee & Transaction</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-lg-7 mb-4 mb-lg-0">
                        <div class="info-section">
                            <div class="section-header">
                                <i class="fas fa-user"></i> Payee Information
                            </div>
                            <div class="section-content">
                                <div class="form-group">
                                    <label for="payee_id">Payee <span class="required">*</span></label>
                                    <select name="payee_id" id="payee_id" class="form-control modern-select select2 @error('payee_id') is-invalid @enderror" required>
                                        <option value="">Select Payee</option>
                                        @foreach($payees as $payee)
                                            <option value="{{ $payee->id }}"
                                                data-balance="{{ $payee->ledger_balance ?? $payee->current_balance }}"
                                                data-category="{{ $payee->category ?? $payee->type }}"
                                                data-principal="{{ $payee->principal_balance }}"
                                                data-interest="{{ $payee->interest_accrued }}"
                                                data-daily-kisti="{{ $payee->daily_kisti_amount }}"
                                                {{ old('payee_id', $selectedPayee?->id ?? request('payee_id')) == $payee->id ? 'selected' : '' }}>
                                                {{ $payee->name }} ({{ $payee->display_category }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('payee_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div id="payee-summary" class="mt-3" style="display: none;">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Current Balance</label>
                                            <div class="form-control modern-input" id="payee-balance">৳0.00</div>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Category</label>
                                            <div class="form-control modern-input" id="payee-category">-</div>
                                        </div>
                                        <div class="col-md-6 mb-3 loan-field">
                                            <label class="form-label">Principal Balance</label>
                                            <div class="form-control modern-input" id="payee-principal">৳0.00</div>
                                        </div>
                                        <div class="col-md-6 mb-3 loan-field">
                                            <label class="form-label">Interest Accrued</label>
                                            <div class="form-control modern-input" id="payee-interest">৳0.00</div>
                                        </div>
                                        <div class="col-md-6 mb-3 loan-field" id="daily-kisti-row" style="display: none;">
                                            <label class="form-label">Daily Kisti</label>
                                            <div class="form-control modern-input" id="payee-daily-kisti">৳0.00</div>
                                        </div>
                                    </div>
                                    <a href="#" id="payee-view-link" class="btn modern-btn modern-btn-outline btn-sm" target="_blank">
                                        <i class="fas fa-eye"></i> View Payee
                                    </a>
                                </div>

                                @if($interestPreview)
                                    <div class="mt-3">
                                        <small class="text-muted">CC Interest Preview: {{ $interestPreview['days'] }} days, daily rate ৳{{ number_format($interestPreview['daily_rate'], 2) }}, pending ৳{{ number_format($interestPreview['amount'], 2) }}</small>
                                    </div>
                                @endif
                                @if($kistiSummary)
                                    <div class="mt-2">
                                        <small class="text-muted">Daily Kisti Pending: {{ $kistiSummary['pending_days'] }} days (৳{{ number_format($kistiSummary['pending_amount'], 2) }})</small>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-5">
                        <div class="info-section">
                            <div class="section-header">
                                <i class="fas fa-receipt"></i> Transaction Details
                            </div>
                            <div class="section-content">
                                <div class="form-group">
                                    <label for="transaction_date">Transaction Date <span class="required">*</span></label>
                                    <input type="date" name="transaction_date" id="transaction_date" class="form-control modern-input @error('transaction_date') is-invalid @enderror" value="{{ old('transaction_date', date('Y-m-d')) }}" required>
                                    @error('transaction_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="transaction_type">Type <span class="required">*</span></label>
                                    <select name="transaction_type" id="transaction_type" class="form-control modern-select @error('transaction_type') is-invalid @enderror" required>
                                        <option value="cash_in" @selected(old('transaction_type', 'cash_in') == 'cash_in')>Payment to Payee (Cash In)</option>
                                        <option value="cash_out" @selected(old('transaction_type') == 'cash_out')>Received from Payee (Cash Out)</option>
                                    </select>
                                    @error('transaction_type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="category">Category <span class="required">*</span></label>
                                    <select name="category" id="category" class="form-control modern-select @error('category') is-invalid @enderror" required>
                                        <option value="">Select Category</option>
                                        <optgroup label="Cash In Categories" id="cash-in-categories">
                                            <option value="payment" @selected(old('category') == 'payment')>Payment</option>
                                            <option value="commission" @selected(old('category') == 'commission')>Commission</option>
                                            <option value="adjustment" @selected(old('category') == 'adjustment')>Adjustment</option>
                                            <option value="other_in" @selected(old('category') == 'other_in')>Other</option>
                                        </optgroup>
                                        <optgroup label="Cash Out Categories" id="cash-out-categories">
                                            <option value="purchase" @selected(old('category') == 'purchase')>Purchase</option>
                                            <option value="borrow" @selected(old('category') == 'borrow')>Borrow</option>
                                            <option value="other_out" @selected(old('category') == 'other_out')>Other</option>
                                        </optgroup>
                                        <optgroup label="Loan Categories" id="loan-categories">
                                            <option value="interest_payment" @selected(old('category') == 'interest_payment')>Interest Payment</option>
                                        </optgroup>
                                    </select>
                                    @error('category')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @php
            $defaultAmount = old('amount');
            if ($defaultAmount === null || $defaultAmount === '') {
                if (!empty($installment)) {
                    $defaultAmount = $installment->total_due;
                } elseif (request()->filled('principal_amount') || request()->filled('interest_amount')) {
                    $defaultAmount = (float) request('principal_amount') + (float) request('interest_amount');
                } elseif (request()->filled('amount')) {
                    $defaultAmount = request('amount');
                }
            }
            $principalValue = old('principal_amount', $installment?->principal_due ?? request('principal_amount'));
            $interestValue = old('interest_amount', $installment?->interest_due ?? request('interest_amount'));
            $kistiDaysValue = old('kisti_days', request('kisti_days'));
            $installmentLocked = !empty($installment);
        @endphp

        <div class="card modern-card mt-4">
            <div class="card-header modern-header">
                <h3 class="card-title"><i class="fas fa-calculator"></i> Amounts</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="amount">Amount <span class="required">*</span></label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text modern-input-addon">৳</span>
                                </div>
                                <input type="number" step="0.01" min="0.01" name="amount" id="amount" class="form-control modern-input @error('amount') is-invalid @enderror" value="{{ $defaultAmount }}" required>
                            </div>
                            @error('amount')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-4 loan-field">
                        <div class="form-group">
                            <label for="principal_amount">Principal Amount</label>
                            <input type="number" step="0.01" min="0" name="principal_amount" id="principal_amount" class="form-control modern-input @error('principal_amount') is-invalid @enderror" value="{{ $principalValue }}" {{ $installmentLocked ? 'readonly' : '' }}>
                            @error('principal_amount')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-4 loan-field">
                        <div class="form-group">
                            <label for="interest_amount">Interest Amount</label>
                            <input type="number" step="0.01" min="0" name="interest_amount" id="interest_amount" class="form-control modern-input @error('interest_amount') is-invalid @enderror" value="{{ $interestValue }}" {{ $installmentLocked ? 'readonly' : '' }}>
                            @error('interest_amount')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-4 loan-field" id="kisti-days-field">
                        <div class="form-group">
                            <label for="kisti_days">Kisti Days</label>
                            <input type="number" min="0" name="kisti_days" id="kisti_days" class="form-control modern-input @error('kisti_days') is-invalid @enderror" value="{{ $kistiDaysValue }}">
                            @error('kisti_days')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
                <small class="text-muted loan-field">Loan fields are applicable for CC/SME/Term/Daily Kisti payees.</small>
            </div>
        </div>

        <div class="card modern-card mt-4">
            <div class="card-header modern-header">
                <h3 class="card-title"><i class="fas fa-credit-card"></i> Payment & Reference</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="payment_method">Payment Method</label>
                            <select name="payment_method" id="payment_method" class="form-control modern-select @error('payment_method') is-invalid @enderror">
                                <option value="">Select Method</option>
                                <option value="cash" @selected(old('payment_method') == 'cash')>Cash</option>
                                <option value="bank_transfer" @selected(old('payment_method') == 'bank_transfer')>Bank Transfer</option>
                                <option value="credit_card" @selected(old('payment_method') == 'credit_card')>Credit Card</option>
                                <option value="check" @selected(old('payment_method') == 'check')>Cheque</option>
                                <option value="mobile_bank" @selected(old('payment_method') == 'mobile_bank')>Mobile Bank</option>
                                <option value="other" @selected(old('payment_method') == 'other')>Other</option>
                            </select>
                            @error('payment_method')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="account_id">Payment Account</label>
                            <select name="account_id" id="account_id" class="form-control modern-select @error('account_id') is-invalid @enderror">
                                <option value="">Auto-select based on method</option>
                                @foreach($cashBankAccounts as $account)
                                    <option value="{{ $account->id }}" @selected(old('account_id') == $account->id)>{{ $account->name }} [{{ $account->formatted_balance }}]</option>
                                @endforeach
                            </select>
                            @error('account_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="reference_no">Reference Number</label>
                            <input type="text" name="reference_no" id="reference_no" class="form-control modern-input @error('reference_no') is-invalid @enderror" value="{{ old('reference_no') }}">
                            @error('reference_no')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea name="description" id="description" rows="3" class="form-control modern-input @error('description') is-invalid @enderror">{{ old('description') }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-end gap-2 mt-3">
            <a href="{{ route('payable-transactions.index') }}" class="btn modern-btn modern-btn-outline">Cancel</a>
            <button type="submit" class="btn modern-btn modern-btn-primary">
                <i class="fas fa-save"></i> Save Transaction
            </button>
        </div>
    </form>
@stop

@section('additional_js')
    <script>
        $(document).ready(function() {
            if ($.fn.select2) {
                $('#payee_id').select2({
                    width: '100%'
                });
            }

            const payeeLinkTemplate = @json(route('payees.show', 0));
            const loanCategories = ['cc', 'sme', 'term_loan', 'daily_kisti'];

            const updatePayeeSummary = function() {
                const option = $('#payee_id option:selected');
                const payeeId = option.val();
                if (!payeeId) {
                    $('#payee-summary').hide();
                    toggleLoanFields(null);
                    return;
                }

                const balance = parseFloat(option.data('balance')) || 0;
                const category = option.data('category') || '-';
                const principal = parseFloat(option.data('principal')) || 0;
                const interest = parseFloat(option.data('interest')) || 0;
                const dailyKisti = parseFloat(option.data('daily-kisti')) || 0;

                $('#payee-balance').text(`৳${balance.toFixed(2)}`);
                $('#payee-category').text(category);
                $('#payee-principal').text(`৳${principal.toFixed(2)}`);
                $('#payee-interest').text(`৳${interest.toFixed(2)}`);
                $('#payee-daily-kisti').text(`৳${dailyKisti.toFixed(2)}`);
                $('#payee-view-link').attr('href', payeeLinkTemplate.replace('/0', `/${payeeId}`));

                if (category === 'daily_kisti') {
                    $('#daily-kisti-row').show();
                } else {
                    $('#daily-kisti-row').hide();
                }

                $('#payee-summary').show();

                toggleLoanFields(category);
            };

            const toggleLoanFields = function(category) {
                const showLoan = category && loanCategories.includes(category);
                $('.loan-field').toggle(showLoan);
                $('#kisti-days-field').toggle(category === 'daily_kisti');
            };

            const updateCategoryOptions = function() {
                const type = $('#transaction_type').val();
                if (type === 'cash_in') {
                    $('#cash-in-categories').show();
                    $('#cash-out-categories').hide();
                } else {
                    $('#cash-in-categories').hide();
                    $('#cash-out-categories').show();
                }
            };

            $('#transaction_type').on('change', function() {
                updateCategoryOptions();
            });

            $('#payee_id').on('change', updatePayeeSummary);

            $('#principal_amount, #interest_amount').on('input', function() {
                const category = $('#payee_id option:selected').data('category');
                if (!category || !loanCategories.includes(category)) {
                    return;
                }
                const principal = parseFloat($('#principal_amount').val()) || 0;
                const interest = parseFloat($('#interest_amount').val()) || 0;
                if (principal > 0 || interest > 0) {
                    $('#amount').val((principal + interest).toFixed(2));
                }
            });

            updateCategoryOptions();
            updatePayeeSummary();
        });
    </script>
@stop
