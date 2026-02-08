@extends('layouts.modern-admin')

@section('title', 'Bank Transactions')
@section('page_title', 'Bank Transactions')

@section('header_actions')
    <a href="{{ route('accounting.bank-transactions.create') }}" class="btn modern-btn modern-btn-primary">
        <i class="fas fa-plus"></i> Add Transaction
    </a>
    <a href="{{ route('accounting.banks.index') }}" class="btn modern-btn modern-btn-outline">
        <i class="fas fa-university"></i> Banks
    </a>
@stop

@section('page_content')
    <div class="card modern-card mb-3">
        <div class="card-header modern-header">
            <h3 class="card-title"><i class="fas fa-filter"></i> Filters</h3>
        </div>
        <div class="card-body">
            <form method="GET" class="form-inline">
                <div class="form-group mr-2">
                    <label class="mr-2" for="bank_account_id">Bank</label>
                    <select name="bank_account_id" id="bank_account_id" class="form-control modern-select">
                        <option value="">All Banks</option>
                        @foreach($banks as $bank)
                            <option value="{{ $bank->id }}" {{ request('bank_account_id') == $bank->id ? 'selected' : '' }}>
                                {{ $bank->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group mr-2">
                    <label class="mr-2" for="transaction_type">Type</label>
                    <select name="transaction_type" id="transaction_type" class="form-control modern-select">
                        <option value="">All Types</option>
                        <option value="deposit" {{ request('transaction_type') === 'deposit' ? 'selected' : '' }}>Deposit</option>
                        <option value="withdraw" {{ request('transaction_type') === 'withdraw' ? 'selected' : '' }}>Withdraw</option>
                        <option value="adjustment" {{ request('transaction_type') === 'adjustment' ? 'selected' : '' }}>Adjustment</option>
                    </select>
                </div>
                <div class="form-group mr-2">
                    <label class="mr-2" for="from_date">From</label>
                    <input type="date" name="from_date" id="from_date" class="form-control" value="{{ request('from_date') }}">
                </div>
                <div class="form-group mr-2">
                    <label class="mr-2" for="to_date">To</label>
                    <input type="date" name="to_date" id="to_date" class="form-control" value="{{ request('to_date') }}">
                </div>
                <button type="submit" class="btn btn-secondary">Filter</button>
            </form>
        </div>
    </div>

    <div class="card modern-card">
        <div class="card-header modern-header">
            <h3 class="card-title"><i class="fas fa-list"></i> Transactions</h3>
        </div>
        <div class="card-body table-responsive">
            <table class="table modern-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Bank</th>
                        <th>Type</th>
                        <th>Counter Account</th>
                        <th>Amount</th>
                        <th>Reference</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transactions as $transaction)
                        <tr>
                            <td>{{ $transaction->transaction_date->format('d M, Y') }}</td>
                            <td>{{ $transaction->bankAccount?->name ?? '-' }}</td>
                            <td>
                                @if($transaction->transaction_type === 'deposit')
                                    <span class="badge badge-success">Deposit</span>
                                @elseif($transaction->transaction_type === 'withdraw')
                                    <span class="badge badge-danger">Withdraw</span>
                                @else
                                    <span class="badge badge-info">Adjustment</span>
                                @endif
                            </td>
                            <td>{{ $transaction->counterAccount?->name ?? '-' }}</td>
                            <td>৳{{ number_format($transaction->amount, 2) }}</td>
                            <td>{{ $transaction->reference ?? '-' }}</td>
                            <td class="text-right">
                                <a href="{{ route('accounting.bank-transactions.edit', $transaction) }}" class="btn btn-sm btn-warning">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button type="button" class="btn btn-sm btn-danger js-delete-transaction" data-id="{{ $transaction->id }}">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted">No bank transactions found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            {{ $transactions->links() }}
        </div>
    </div>
@stop

@section('additional_js')
<script>
    $(document).ready(function() {
        $(document).on('click', '.js-delete-transaction', function() {
            const transactionId = $(this).data('id');
            const url = `{{ url('accounting/bank-transactions') }}/${transactionId}`;

            const runDelete = function() {
                $.ajax({
                    url: url,
                    type: 'DELETE',
                    data: {
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(response) {
                        if (response.success) {
                            if (window.Swal) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Deleted',
                                    text: response.message,
                                    timer: 2000,
                                    showConfirmButton: false
                                }).then(function() {
                                    window.location.reload();
                                });
                            } else {
                                window.location.reload();
                            }
                        } else if (window.Swal) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Delete Failed',
                                text: response.message || 'Unable to delete transaction.'
                            });
                        }
                    },
                    error: function() {
                        if (window.Swal) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Delete Failed',
                                text: 'Something went wrong. Please try again.'
                            });
                        }
                    }
                });
            };

            if (!window.Swal) {
                if (confirm('Are you sure you want to delete this transaction?')) {
                    runDelete();
                }
                return;
            }

            Swal.fire({
                title: 'Delete Transaction',
                text: 'Are you sure you want to delete this transaction?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Yes, delete it',
                cancelButtonText: 'Cancel',
                reverseButtons: true,
                focusCancel: true
            }).then((result) => {
                if (result.isConfirmed) {
                    runDelete();
                }
            });
        });
    });
</script>
@stop
