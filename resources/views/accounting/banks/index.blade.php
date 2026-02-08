@extends('layouts.modern-admin')

@section('title', 'Bank Management')
@section('page_title', 'Bank Management')

@section('header_actions')
    <a href="{{ route('accounting.banks.create') }}" class="btn modern-btn modern-btn-primary">
        <i class="fas fa-plus"></i> Add Bank
    </a>
    <a href="{{ route('accounting.bank-transactions.create') }}" class="btn modern-btn modern-btn-outline">
        <i class="fas fa-exchange-alt"></i> New Transaction
    </a>
@stop

@section('page_content')
    <div class="card modern-card">
        <div class="card-header modern-header">
            <h3 class="card-title"><i class="fas fa-university"></i> Bank Accounts</h3>
        </div>
        <div class="card-body table-responsive">
            <table class="table modern-table">
                <thead>
                    <tr>
                        <th>Bank</th>
                        <th>Account No.</th>
                        <th>Balance</th>
                        <th>Status</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($banks as $bank)
                        <tr>
                            <td>
                                <strong>{{ $bank->name }}</strong>
                                @if($bank->bank_name)
                                    <div class="text-muted">{{ $bank->bank_name }}</div>
                                @endif
                            </td>
                            <td>{{ $bank->bank_account_number ?? '-' }}</td>
                            <td>
                                ৳{{ number_format($bank->current_balance, 2) }}
                                <span class="text-muted">({{ ucfirst($bank->current_balance_type) }})</span>
                            </td>
                            <td>
                                @if($bank->is_active)
                                    <span class="badge badge-success">Active</span>
                                @else
                                    <span class="badge badge-secondary">Inactive</span>
                                @endif
                            </td>
                            <td class="text-right">
                                <a href="{{ route('accounting.banks.show', $bank) }}" class="btn btn-sm btn-info">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @if(!$bank->is_system)
                                    <a href="{{ route('accounting.banks.edit', $bank) }}" class="btn btn-sm btn-warning">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                @endif
                                <a href="{{ route('accounting.bank-transactions.create', ['bank_account_id' => $bank->id, 'type' => 'deposit']) }}" class="btn btn-sm btn-success" title="Deposit">
                                    <i class="fas fa-arrow-down"></i>
                                </a>
                                <a href="{{ route('accounting.bank-transactions.create', ['bank_account_id' => $bank->id, 'type' => 'withdraw']) }}" class="btn btn-sm btn-danger" title="Withdraw">
                                    <i class="fas fa-arrow-up"></i>
                                </a>
                                <a href="{{ route('accounting.bank-transactions.create', ['bank_account_id' => $bank->id, 'type' => 'adjustment']) }}" class="btn btn-sm btn-secondary" title="Adjust">
                                    <i class="fas fa-sliders-h"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted">No bank accounts found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@stop
