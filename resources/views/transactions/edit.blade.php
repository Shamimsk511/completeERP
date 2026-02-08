@extends('layouts.modern-admin')

@section('title', 'Edit Transaction')

@section('page_title', 'Edit Transaction')

@section('header_actions')
    <a href="{{ route('transactions.index') }}" class="btn modern-btn modern-btn-outline">
        <i class="fas fa-arrow-left"></i> Back to Transactions
    </a>
    <a href="{{ route('transactions.show', $transaction->id) }}" class="btn modern-btn modern-btn-info">
        <i class="fas fa-eye"></i> View Transaction
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

    <div class="card modern-card">
        <div class="card-header modern-header">
            <h3 class="card-title"><i class="fas fa-info-circle"></i> Transaction Info</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3 mb-3">
                    <label class="form-label">Transaction ID</label>
                    <div class="form-control modern-input" style="background: #f8fafc;">#{{ $transaction->id }}</div>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Created</label>
                    <div class="form-control modern-input" style="background: #f8fafc;">{{ $transaction->created_at->format('Y-m-d H:i') }}</div>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Type</label>
                    <div class="form-control modern-input" style="background: #f8fafc;">{{ ucfirst($transaction->type) }}</div>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Amount</label>
                    <div class="form-control modern-input" style="background: #f8fafc;">৳{{ number_format($transaction->amount, 2) }}</div>
                </div>
                @if($transaction->invoice)
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Invoice</label>
                        <div class="form-control modern-input" style="background: #f8fafc;">
                            @if(Route::has('invoices.show'))
                                <a href="{{ route('invoices.show', $transaction->invoice_id) }}" target="_blank">#{{ $transaction->invoice->invoice_number }}</a>
                            @else
                                #{{ $transaction->invoice->invoice_number }}
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <form action="{{ route('transactions.update', $transaction) }}" method="POST" id="transaction-form" class="mt-4">
        @csrf
        @method('PUT')

        <div class="card modern-card">
            <div class="card-header modern-header">
                <h3 class="card-title"><i class="fas fa-edit"></i> Update Details</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-lg-6 mb-4 mb-lg-0">
                        <div class="info-section">
                            <div class="section-header">
                                <i class="fas fa-user"></i> Customer Information
                            </div>
                            <div class="section-content">
                                <div class="form-group">
                                    <label for="customer_id">Customer <span class="required">*</span></label>
                                    <select name="customer_id" id="customer_id" class="form-control modern-select select2 @error('customer_id') is-invalid @enderror" required>
                                        <option value="">Select Customer</option>
                                        @foreach($customers as $customer)
                                            <option value="{{ $customer->id }}" {{ $transaction->customer_id == $customer->id ? 'selected' : '' }}>
                                                {{ $customer->name }}{{ $customer->phone ? ' - ' . $customer->phone : '' }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('customer_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Phone</label>
                                            <input type="text" id="customer_phone" class="form-control modern-input" readonly>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Balance</label>
                                            <input type="text" id="customer_balance" class="form-control modern-input" readonly>
                                        </div>
                                    </div>
                                </div>

                                <div id="customer-ledger-wrapper" class="mt-2" style="display: none;">
                                    <a href="#" id="customer-ledger-link" class="btn modern-btn modern-btn-outline btn-sm" target="_blank">
                                        <i class="fas fa-book"></i> View Ledger
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-6">
                        <div class="info-section">
                            <div class="section-header">
                                <i class="fas fa-exchange-alt"></i> Transaction Info
                            </div>
                            <div class="section-content">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="type">Type <span class="required">*</span></label>
                                            <select name="type" id="type" class="form-control modern-select @error('type') is-invalid @enderror" required>
                                                <option value="debit" {{ $transaction->type == 'debit' ? 'selected' : '' }}>Payment (Debit)</option>
                                                <option value="credit" {{ $transaction->type == 'credit' ? 'selected' : '' }}>Charge (Credit)</option>
                                            </select>
                                            @error('type')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="amount">Amount <span class="required">*</span></label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text modern-input-addon">৳</span>
                                                </div>
                                                <input type="number" step="0.01" name="amount" id="amount" class="form-control modern-input @error('amount') is-invalid @enderror" value="{{ $transaction->amount }}" required>
                                            </div>
                                            @error('amount')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="method">Payment Method <span class="required">*</span></label>
                                            <select name="method" id="method" class="form-control modern-select @error('method') is-invalid @enderror" required>
                                                <option value="cash" {{ $transaction->method == 'cash' ? 'selected' : '' }}>Cash</option>
                                                <option value="bank" {{ $transaction->method == 'bank' ? 'selected' : '' }}>Bank</option>
                                                <option value="mobile_bank" {{ $transaction->method == 'mobile_bank' ? 'selected' : '' }}>Mobile Bank</option>
                                                <option value="cheque" {{ $transaction->method == 'cheque' ? 'selected' : '' }}>Cheque</option>
                                            </select>
                                            @error('method')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="purpose">Purpose <span class="required">*</span></label>
                                            <input type="text" name="purpose" id="purpose" class="form-control modern-input @error('purpose') is-invalid @enderror" value="{{ $transaction->purpose }}" required>
                                            @error('purpose')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                @if(isset($cashBankAccounts) && $cashBankAccounts->count() > 0)
                                    <div class="form-group">
                                        <label for="account_id">Cash/Bank Account (Accounting)</label>
                                        <select name="account_id" id="account_id" class="form-control modern-select @error('account_id') is-invalid @enderror">
                                            <option value="">Auto-select based on method</option>
                                            @foreach ($cashBankAccounts as $account)
                                                @php
                                                    $balance = $account->running_balance;
                                                    $balanceStr = number_format($balance['balance'], 2) . ' ' . ($balance['balance_type'] === 'debit' ? 'Dr' : 'Cr');
                                                @endphp
                                                <option value="{{ $account->id }}" {{ $transaction->account_id == $account->id ? 'selected' : '' }}>
                                                    {{ $account->name }} [৳{{ $balanceStr }}]
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('account_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mt-4">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="discount_amount">Discount Amount</label>
                            <input type="number" step="0.01" name="discount_amount" id="discount_amount" class="form-control modern-input @error('discount_amount') is-invalid @enderror" value="{{ $transaction->discount_amount }}" placeholder="0.00">
                            @error('discount_amount')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="discount_reason">Discount Reason</label>
                            <input type="text" name="discount_reason" id="discount_reason" class="form-control modern-input @error('discount_reason') is-invalid @enderror" value="{{ $transaction->discount_reason }}" placeholder="Optional">
                            @error('discount_reason')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="reference">Reference</label>
                            <input type="text" name="reference" id="reference" class="form-control modern-input @error('reference') is-invalid @enderror" value="{{ $transaction->reference }}" placeholder="Cheque no, bank ref, etc.">
                            @error('reference')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="note">Notes</label>
                            <textarea name="note" id="note" rows="3" class="form-control modern-input @error('note') is-invalid @enderror" placeholder="Additional notes">{{ $transaction->note }}</textarea>
                            @error('note')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer modern-footer">
                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('transactions.index') }}" class="btn modern-btn modern-btn-outline">Cancel</a>
                    <button type="submit" class="btn modern-btn modern-btn-primary">
                        <i class="fas fa-save"></i> Update Transaction
                    </button>
                </div>
            </div>
        </div>
    </form>
@stop

@section('additional_js')
    <script>
        $(document).ready(function() {
            if ($.fn.select2) {
                $('.select2').select2({
                    width: '100%'
                });
            }

            const customerDetailsBaseUrl = @json(url('customer-details'));
            const ledgerUrlTemplate = @json(route('customers.ledger', 0));

            const $customer = $('#customer_id');
            const $phone = $('#customer_phone');
            const $balance = $('#customer_balance');
            const $ledgerWrap = $('#customer-ledger-wrapper');
            const $ledgerLink = $('#customer-ledger-link');

            const clearCustomerInfo = function() {
                $phone.val('');
                $balance.val('');
                $ledgerWrap.hide();
                $ledgerLink.attr('href', '#');
            };

            const loadCustomerDetails = function(customerId) {
                if (!customerId) {
                    clearCustomerInfo();
                    return;
                }

                $.get(`${customerDetailsBaseUrl}/${customerId}`)
                    .done(function(data) {
                        $phone.val(data.phone || '');
                        const balanceDisplay = data.ledger_balance_formatted || data.outstanding_balance || '0.00';
                        $balance.val(balanceDisplay);
                        $ledgerLink.attr('href', ledgerUrlTemplate.replace('/0/ledger', `/${customerId}/ledger`));
                        $ledgerWrap.show();
                    })
                    .fail(function() {
                        clearCustomerInfo();
                    });
            };

            $customer.on('change', function() {
                loadCustomerDetails($(this).val());
            });

            if ($customer.val()) {
                loadCustomerDetails($customer.val());
            }
        });
    </script>
@stop
