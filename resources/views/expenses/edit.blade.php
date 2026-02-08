@extends('layouts.modern-admin')

@section('title', 'Edit Expense')
@section('page_title', 'Edit Expense')

@section('page_content')
    <div class="card modern-card">
        <div class="card-body">
            <form action="{{ route('expenses.update', $expense) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="expense_date">Expense Date <span class="required">*</span></label>
                            <input type="date" name="expense_date" id="expense_date" class="form-control modern-input" value="{{ old('expense_date', $expense->expense_date->format('Y-m-d')) }}" required>
                        </div>
                    </div>
                    <div class="col-md-8">
                        <div class="form-group">
                            <label for="expense_category_id" class="d-flex align-items-center justify-content-between">
                                <span>Category <span class="required">*</span></span>
                                <button type="button" class="btn btn-sm modern-btn modern-btn-outline" data-toggle="modal" data-target="#expenseCategoryModal" title="Add Category">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </label>
                            <select name="expense_category_id" id="expense_category_id" class="form-control modern-select" data-expense-category-select required>
                                <option value="">Select Category</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ old('expense_category_id', $expense->expense_category_id) == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="form-text text-muted">Click the + button to create a new category.</small>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="amount">Amount <span class="required">*</span></label>
                            <input type="number" name="amount" id="amount" class="form-control modern-input" step="0.01" min="0.01" value="{{ old('amount', $expense->amount) }}" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="payment_method">Payment Method</label>
                            <select name="payment_method" id="payment_method" class="form-control modern-select">
                                <option value="">Select Method</option>
                                <option value="cash" {{ old('payment_method', $expense->payment_method) == 'cash' ? 'selected' : '' }}>Cash</option>
                                <option value="bank" {{ old('payment_method', $expense->payment_method) == 'bank' ? 'selected' : '' }}>Bank Transfer</option>
                                <option value="mobile_bank" {{ old('payment_method', $expense->payment_method) == 'mobile_bank' ? 'selected' : '' }}>Mobile Banking</option>
                                <option value="cheque" {{ old('payment_method', $expense->payment_method) == 'cheque' ? 'selected' : '' }}>Cheque</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="payment_account_id">Payment Account <span class="required">*</span></label>
                            <select name="payment_account_id" id="payment_account_id" class="form-control modern-select" required>
                                <option value="">Select Cash/Bank</option>
                                @foreach($paymentAccounts as $account)
                                    @php
                                        $balanceType = ($account->current_balance_type ?? 'debit') === 'credit' ? 'Cr' : 'Dr';
                                        $balanceLabel = '৳' . number_format((float) ($account->current_balance ?? 0), 2) . ' ' . $balanceType;
                                    @endphp
                                    <option value="{{ $account->id }}" data-balance="{{ $balanceLabel }}" {{ old('payment_account_id', $expense->payment_account_id) == $account->id ? 'selected' : '' }}>
                                        {{ $account->name }} ({{ $balanceLabel }})
                                    </option>
                                @endforeach
                            </select>
                            <small id="payment_account_balance" class="form-text text-muted"></small>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="reference">Reference</label>
                    <input type="text" name="reference" id="reference" class="form-control modern-input" value="{{ old('reference', $expense->reference) }}">
                </div>

                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea name="description" id="description" class="form-control modern-textarea" rows="3">{{ old('description', $expense->description) }}</textarea>
                </div>

                <div class="mt-3">
                    <button type="submit" class="btn modern-btn modern-btn-primary">
                        <i class="fas fa-save"></i> Update Expense
                    </button>
                    <a href="{{ route('expenses.index') }}" class="btn modern-btn modern-btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>

    @include('expenses.partials.category-modal')
@endsection

@push('js')
<script>
    (function() {
        const paymentSelect = document.getElementById('payment_account_id');
        const paymentBalance = document.getElementById('payment_account_balance');

        const updateBalance = () => {
            const selected = paymentSelect?.options[paymentSelect.selectedIndex];
            if (!selected || !paymentBalance) return;
            const balance = selected.getAttribute('data-balance') || '';
            paymentBalance.textContent = balance ? `Selected balance: ${balance}` : '';
        };

        if (paymentSelect) {
            paymentSelect.addEventListener('change', updateBalance);
            updateBalance();
        }
    })();
</script>
@endpush

@include('expenses.partials.category-modal-script')
