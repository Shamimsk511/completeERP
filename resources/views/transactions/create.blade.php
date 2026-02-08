@extends('layouts.modern-admin')

@section('title', 'Create Transaction')

@section('page_title', 'Create Transaction')

@section('header_actions')
    <a href="{{ route('transactions.index') }}" class="btn modern-btn modern-btn-outline">
        <i class="fas fa-arrow-left"></i> Back to Transactions
    </a>
@stop

@section('page_content')
    <form action="{{ route('transactions.store') }}" method="POST" id="transaction-form">
        @csrf

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
                <h3 class="card-title"><i class="fas fa-receipt"></i> Transaction Details</h3>
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
                                        @foreach ($customers as $customer)
                                            <option value="{{ $customer->id }}" {{ old('customer_id', $selectedCustomer?->id) == $customer->id ? 'selected' : '' }}>
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
                                <div class="form-group">
                                    <label for="type">Type <span class="required">*</span></label>
                                    <select name="type" id="type" class="form-control modern-select @error('type') is-invalid @enderror" required>
                                        <option value="debit" @selected(old('type', 'debit') == 'debit')>Payment (Debit)</option>
                                        <option value="credit" @selected(old('type') == 'credit')>Charge (Credit)</option>
                                    </select>
                                    @error('type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group debit-only">
                                    <label>Outstanding Invoices</label>
                                    <div id="invoice-loading" class="text-muted small">Select a customer to load unpaid invoices.</div>
                                    <div id="invoice-empty" class="text-muted small" style="display: none;">No outstanding invoices found.</div>
                                    <div id="invoice-table-wrap" class="table-responsive" style="display: none;">
                                        <table class="table table-sm modern-table" id="invoice-table">
                                            <thead>
                                                <tr>
                                                    <th style="width: 36px;">
                                                        <input type="checkbox" id="select-all-invoices">
                                                    </th>
                                                    <th>Invoice #</th>
                                                    <th>Due</th>
                                                </tr>
                                            </thead>
                                            <tbody></tbody>
                                        </table>
                                    </div>
                                    <input type="hidden" name="invoice_id" id="invoice_id">
                                    <small class="form-text text-muted">Select invoices to auto-fill amount and purpose.</small>
                                    @error('invoice_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="purpose">Purpose <span class="required">*</span></label>
                                    <input type="text" name="purpose" id="purpose" class="form-control modern-input @error('purpose') is-invalid @enderror" value="{{ old('purpose', 'Customer Payment') }}" placeholder="Payment received / charge applied" required>
                                    @error('purpose')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-lg-7 mb-4 mb-lg-0">
                <div class="card modern-card">
                    <div class="card-header modern-header">
                        <h3 class="card-title"><i class="fas fa-credit-card"></i> Payment Details</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group amount-group">
                                    <label for="amount">Amount <span class="required">*</span></label>
                                    <span class="badge remaining-floating-badge remaining-zero debit-only" id="remaining-balance-label">
                                        Remaining: <strong id="remaining-balance-value">৳0.00</strong>
                                    </span>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text modern-input-addon">৳</span>
                                        </div>
                                        <input type="number" step="0.01" name="amount" id="amount" class="form-control modern-input @error('amount') is-invalid @enderror" value="{{ old('amount') }}" required placeholder="0.00">
                                    </div>
                                    @error('amount')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="method">Payment Method <span class="required">*</span></label>
                                    <select name="method" id="method" class="form-control modern-select @error('method') is-invalid @enderror" required>
                                        <option value="">Select Method</option>
                                        <option value="cash" @selected(old('method', 'cash') == 'cash')>Cash</option>
                                        <option value="bank" @selected(old('method') == 'bank')>Bank</option>
                                        <option value="mobile_bank" @selected(old('method') == 'mobile_bank')>Mobile Bank</option>
                                        <option value="cheque" @selected(old('method') == 'cheque')>Cheque</option>
                                    </select>
                                    @error('method')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <div class="d-flex flex-wrap gap-2 mb-3 debit-only">
                            <button type="button" class="btn modern-btn modern-btn-outline btn-sm" id="full-payment">
                                <i class="fas fa-wallet"></i> Full Payment
                            </button>
                            <button type="button" class="btn modern-btn modern-btn-outline btn-sm" id="discount-from-remaining">
                                <i class="fas fa-tag"></i> Discount?
                            </button>
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
                                        <option value="{{ $account->id }}" {{ old('account_id') == $account->id ? 'selected' : '' }}>
                                            {{ $account->name }} [৳{{ $balanceStr }}]
                                        </option>
                                    @endforeach
                                </select>
                                @error('account_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        @endif

                        <div class="row debit-only">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="discount_amount">Discount Amount</label>
                                    <input type="number" step="0.01" name="discount_amount" id="discount_amount" class="form-control modern-input @error('discount_amount') is-invalid @enderror" value="{{ old('discount_amount') }}" placeholder="0.00">
                                    @error('discount_amount')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="discount_reason">Discount Reason</label>
                                    <input type="text" name="discount_reason" id="discount_reason" class="form-control modern-input @error('discount_reason') is-invalid @enderror" value="{{ old('discount_reason') }}" placeholder="Optional">
                                    @error('discount_reason')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <small class="text-muted debit-only">Discounts are applied to debit (payment) transactions.</small>
                    </div>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="card modern-card">
                    <div class="card-header modern-header">
                        <h3 class="card-title"><i class="fas fa-sticky-note"></i> Notes & Reference</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="reference">Reference</label>
                            <input type="text" name="reference" id="reference" class="form-control modern-input @error('reference') is-invalid @enderror" value="{{ old('reference') }}" placeholder="Cheque no, bank ref, etc.">
                            @error('reference')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="note">Notes</label>
                            <textarea name="note" id="note" rows="4" class="form-control modern-input @error('note') is-invalid @enderror" placeholder="Additional notes">{{ old('note') }}</textarea>
                            @error('note')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-end gap-2 mt-3">
            <a href="{{ route('transactions.index') }}" class="btn modern-btn modern-btn-outline">Cancel</a>
            <button type="submit" class="btn modern-btn modern-btn-primary">
                <i class="fas fa-save"></i> Save Transaction
            </button>
        </div>
    </form>
@stop

@section('additional_css')
    <style>
        .amount-group {
            position: relative;
        }

        .remaining-floating-badge {
            position: absolute;
            top: 0;
            right: 0;
            transform: translateY(2px);
            font-size: 11px;
            padding: 0.35rem 0.55rem;
            border-radius: 999px;
            z-index: 2;
            border: 1px solid transparent;
            transition: all 0.2s ease;
        }

        .remaining-floating-badge.remaining-positive {
            background: color-mix(in srgb, var(--app-primary, #4f46e5) 14%, #ffffff);
            border-color: color-mix(in srgb, var(--app-primary, #4f46e5) 35%, #ffffff);
            color: var(--app-primary-dark, #1f2937);
        }

        .remaining-floating-badge.remaining-zero {
            background: color-mix(in srgb, #10b981 14%, #ffffff);
            border-color: color-mix(in srgb, #10b981 35%, #ffffff);
            color: #065f46;
        }

        .remaining-floating-badge.remaining-negative {
            background: color-mix(in srgb, #ef4444 12%, #ffffff);
            border-color: color-mix(in srgb, #ef4444 35%, #ffffff);
            color: #991b1b;
        }

        @supports not (color: color-mix(in srgb, #000 50%, #fff)) {
            .remaining-floating-badge.remaining-positive {
                background: rgba(79, 70, 229, 0.14);
                border-color: rgba(79, 70, 229, 0.35);
                color: #1f2937;
            }

            .remaining-floating-badge.remaining-zero {
                background: rgba(16, 185, 129, 0.14);
                border-color: rgba(16, 185, 129, 0.35);
                color: #065f46;
            }

            .remaining-floating-badge.remaining-negative {
                background: rgba(239, 68, 68, 0.12);
                border-color: rgba(239, 68, 68, 0.35);
                color: #991b1b;
            }
        }

        @media (max-width: 767.98px) {
            .remaining-floating-badge {
                position: static;
                transform: none;
                display: inline-block;
                margin-bottom: 0.35rem;
            }
        }
    </style>
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
            const invoicesBaseUrl = @json(url('customers'));
            const ledgerUrlTemplate = @json(route('customers.ledger', 0));
            const initialInvoiceId = @json(old('invoice_id', $selectedInvoice?->id));

            const $customer = $('#customer_id');
            const $invoice = $('#invoice_id');
            const $phone = $('#customer_phone');
            const $balance = $('#customer_balance');
            const $ledgerWrap = $('#customer-ledger-wrapper');
            const $ledgerLink = $('#customer-ledger-link');
            const $invoiceLoading = $('#invoice-loading');
            const $invoiceEmpty = $('#invoice-empty');
            const $invoiceTableWrap = $('#invoice-table-wrap');
            const $invoiceTableBody = $('#invoice-table tbody');
            const $selectAllInvoices = $('#select-all-invoices');
            const $purpose = $('#purpose');
            const $amount = $('#amount');
            const $discountAmount = $('#discount_amount');
            const $remainingLabel = $('#remaining-balance-label');
            const $remainingValue = $('#remaining-balance-value');
            const $discountFromRemaining = $('#discount-from-remaining');
            const basePurpose = $purpose.val() || 'Customer Payment';

            let invoiceList = [];
            let selectedInvoices = new Map();
            let currentBalance = 0;

            const getRemainingBalance = function() {
                const balance = parseFloat(currentBalance) || 0;
                const amountValue = parseFloat($amount.val()) || 0;
                return balance - amountValue;
            };

            const updateRemainingBalanceDisplay = function() {
                if ($('#type').val() !== 'debit') {
                    $remainingLabel.hide();
                    $discountFromRemaining.hide();
                    return;
                }

                const remaining = getRemainingBalance();
                const remainingForDiscount = Math.max(0, remaining);

                $remainingLabel.show();
                $discountFromRemaining.show();
                $remainingValue.text(`৳${remaining.toFixed(2)}`);
                $discountFromRemaining.prop('disabled', remainingForDiscount <= 0);

                $remainingLabel
                    .removeClass('remaining-positive remaining-zero remaining-negative')
                    .addClass(
                        remaining > 0 ? 'remaining-positive' :
                        (remaining === 0 ? 'remaining-zero' : 'remaining-negative')
                    );
            };

            const clearCustomerInfo = function() {
                $phone.val('');
                $balance.val('');
                $ledgerWrap.hide();
                $ledgerLink.attr('href', '#');
            };

            const loadCustomerDetails = function(customerId) {
                if (!customerId) {
                    clearCustomerInfo();
                    currentBalance = 0;
                    updateRemainingBalanceDisplay();
                    return;
                }

                $.get(`${customerDetailsBaseUrl}/${customerId}`)
                    .done(function(data) {
                        $phone.val(data.phone || '');
                        const balanceDisplay = data.ledger_balance_formatted || data.outstanding_balance || '0.00';
                        $balance.val(balanceDisplay);
                        currentBalance = parseFloat(data.ledger_balance ?? data.outstanding_balance ?? 0) || 0;
                        updateRemainingBalanceDisplay();
                        $ledgerLink.attr('href', ledgerUrlTemplate.replace('/0/ledger', `/${customerId}/ledger`));
                        $ledgerWrap.show();
                    })
                    .fail(function() {
                        clearCustomerInfo();
                        currentBalance = 0;
                        updateRemainingBalanceDisplay();
                    });
            };

            const updateSelectAllState = function() {
                const total = invoiceList.length;
                const selectedCount = selectedInvoices.size;
                if (selectedCount === 0) {
                    $selectAllInvoices.prop('checked', false).prop('indeterminate', false);
                } else if (selectedCount === total) {
                    $selectAllInvoices.prop('checked', true).prop('indeterminate', false);
                } else {
                    $selectAllInvoices.prop('checked', false).prop('indeterminate', true);
                }
            };

            const updateSelectionEffects = function() {
                if ($('#type').val() !== 'debit') {
                    return;
                }
                const invoices = Array.from(selectedInvoices.values());
                const total = invoices.reduce(function(sum, invoice) {
                    return sum + (parseFloat(invoice.due_amount) || 0);
                }, 0);

                if (selectedInvoices.size > 0) {
                    $amount.val(total.toFixed(2));
                }

                if (selectedInvoices.size === 0) {
                    $invoice.val('');
                    $amount.val('');
                    $purpose.val(basePurpose);
                    updateRemainingBalanceDisplay();
                    return;
                }

                if (selectedInvoices.size === 1) {
                    const invoice = invoices[0];
                    $invoice.val(invoice.id);
                    $purpose.val(`Payment for Invoice #${invoice.invoice_number}`);
                } else {
                    const numbers = invoices.map(function(invoice) {
                        return `#${invoice.invoice_number}`;
                    });
                    $invoice.val('');
                    $purpose.val(`Payment for Invoices: ${numbers.join(', ')}`);
                }

                updateRemainingBalanceDisplay();
            };

            const clearInvoiceSelection = function() {
                selectedInvoices.clear();
                $invoiceTableBody.find('.invoice-checkbox').prop('checked', false);
                updateSelectAllState();
                updateSelectionEffects();
            };

            const renderInvoiceTable = function(invoices) {
                invoiceList = invoices;
                selectedInvoices.clear();
                $invoiceTableBody.empty();
                $selectAllInvoices.prop('checked', false).prop('indeterminate', false);

                if (!invoices.length) {
                    $invoiceLoading.hide();
                    $invoiceTableWrap.hide();
                    $invoiceEmpty.show();
                    return;
                }

                invoices.forEach(function(invoice) {
                    const dueAmount = parseFloat(invoice.due_amount) || 0;
                    const isSelected = initialInvoiceId && String(invoice.id) === String(initialInvoiceId);
                    if (isSelected) {
                        selectedInvoices.set(String(invoice.id), invoice);
                    }
                    $invoiceTableBody.append(`
                        <tr>
                            <td>
                                <input type="checkbox" class="invoice-checkbox" data-id="${invoice.id}" ${isSelected ? 'checked' : ''}>
                            </td>
                            <td>#${invoice.invoice_number}</td>
                            <td>৳${dueAmount.toFixed(2)}</td>
                        </tr>
                    `);
                });

                $invoiceLoading.hide();
                $invoiceEmpty.hide();
                $invoiceTableWrap.show();
                updateSelectAllState();
                updateSelectionEffects();
            };

            const loadInvoices = function(customerId) {
                if (!customerId) {
                    invoiceList = [];
                    selectedInvoices.clear();
                    $invoiceLoading.text('Select a customer to load unpaid invoices.').show();
                    $invoiceEmpty.hide();
                    $invoiceTableWrap.hide();
                    updateSelectAllState();
                    updateSelectionEffects();
                    return;
                }

                $invoiceLoading.text('Loading unpaid invoices...').show();
                $invoiceEmpty.hide();
                $invoiceTableWrap.hide();

                $.get(`${invoicesBaseUrl}/${customerId}/invoices`)
                    .done(function(response) {
                        if (response && response.success) {
                            renderInvoiceTable(response.data || []);
                        } else {
                            renderInvoiceTable([]);
                        }
                    })
                    .fail(function() {
                        renderInvoiceTable([]);
                    });
            };

            const toggleDebitFields = function() {
                const isDebit = $('#type').val() === 'debit';
                $('.debit-only').toggle(isDebit);
                if (!isDebit) {
                    $invoice.val('');
                    $('#discount_amount').val('');
                    $('#discount_reason').val('');
                    clearInvoiceSelection();
                }
                updateRemainingBalanceDisplay();
            };

            $customer.on('change', function() {
                const customerId = $(this).val();
                loadCustomerDetails(customerId);
                loadInvoices(customerId);
                $purpose.val(basePurpose);
                $amount.val('');
                updateRemainingBalanceDisplay();
            });

            $('#type').on('change', function() {
                toggleDebitFields();
            });

            $(document).on('change', '.invoice-checkbox', function() {
                const id = String($(this).data('id'));
                const invoice = invoiceList.find(function(item) {
                    return String(item.id) === id;
                });
                if ($(this).is(':checked')) {
                    if (invoice) {
                        selectedInvoices.set(id, invoice);
                    }
                } else {
                    selectedInvoices.delete(id);
                }
                updateSelectAllState();
                updateSelectionEffects();
            });

            $selectAllInvoices.on('change', function() {
                const checked = $(this).is(':checked');
                selectedInvoices.clear();
                $invoiceTableBody.find('.invoice-checkbox').prop('checked', checked);
                if (checked) {
                    invoiceList.forEach(function(invoice) {
                        selectedInvoices.set(String(invoice.id), invoice);
                    });
                }
                updateSelectAllState();
                updateSelectionEffects();
            });

            $('#full-payment').on('click', function() {
                if ($('#type').val() !== 'debit') {
                    return;
                }
                clearInvoiceSelection();
                const amount = Math.max(0, parseFloat(currentBalance) || 0);
                if (amount > 0) {
                    $amount.val(amount.toFixed(2));
                    $purpose.val('Full payment of outstanding balance');
                } else {
                    $amount.val('0.00');
                    $purpose.val(basePurpose);
                }
                updateRemainingBalanceDisplay();
            });

            $amount.on('input change', function() {
                updateRemainingBalanceDisplay();
            });

            $discountFromRemaining.on('click', function() {
                if ($('#type').val() !== 'debit') {
                    return;
                }
                const remainingForDiscount = Math.max(0, getRemainingBalance());
                $discountAmount.val(remainingForDiscount.toFixed(2)).trigger('change');
            });

            const initialCustomerId = $customer.val();
            if (initialCustomerId) {
                loadCustomerDetails(initialCustomerId);
                loadInvoices(initialCustomerId);
            }

            toggleDebitFields();
            updateRemainingBalanceDisplay();
        });
    </script>
@stop
