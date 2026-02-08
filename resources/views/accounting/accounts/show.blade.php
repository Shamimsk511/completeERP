@extends('layouts.modern-admin')

@section('title', 'Account - ' . $account->name)

@section('page_title', $account->name)

@section('header_actions')
    <a href="{{ route('accounting.accounts.ledger', $account) }}" class="btn modern-btn modern-btn-primary">
        <i class="fas fa-book"></i> View Ledger
    </a>
    @if(!$account->is_system)
        <a href="{{ route('accounting.accounts.edit', $account) }}" class="btn modern-btn modern-btn-warning">
            <i class="fas fa-edit"></i> Edit
        </a>
    @endif
@stop

@section('page_content')
    <div class="card modern-card">
        <div class="card-header modern-header">
            <h3 class="card-title">
                <i class="fas fa-book"></i> Account Details
            </h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <table class="table table-borderless">
                        <tr>
                            <td class="text-muted" width="40%">Account Code:</td>
                            <td><strong>{{ $account->code }}</strong></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Account Name:</td>
                            <td><strong>{{ $account->name }}</strong></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Account Group:</td>
                            <td>{{ $account->accountGroup->name ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Account Type:</td>
                            <td>
                                <span class="badge badge-info">{{ ucfirst($account->account_type) }}</span>
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted">Status:</td>
                            <td>
                                @if($account->is_active)
                                    <span class="badge badge-success">Active</span>
                                @else
                                    <span class="badge badge-secondary">Inactive</span>
                                @endif
                                @if($account->is_system)
                                    <span class="badge badge-warning">System Account</span>
                                @endif
                            </td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-borderless">
                        <tr>
                            <td class="text-muted" width="40%">Opening Balance:</td>
                            <td>
                                ৳{{ number_format($account->opening_balance, 2) }}
                                ({{ ucfirst($account->opening_balance_type ?? 'debit') }})
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted">Current Balance:</td>
                            <td>
                                <strong class="text-primary" style="font-size: 1.25rem;">
                                    ৳{{ number_format($account->current_balance, 2) }}
                                    ({{ ucfirst($account->current_balance_type) }})
                                </strong>
                            </td>
                        </tr>
                        @if($account->account_type === 'bank')
                            <tr>
                                <td class="text-muted">Bank Name:</td>
                                <td>{{ $account->bank_name ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Bank A/C No:</td>
                                <td>{{ $account->bank_account_number ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">IFSC/Routing:</td>
                                <td>{{ $account->ifsc_code ?? '-' }}</td>
                            </tr>
                        @endif
                    </table>
                </div>
            </div>

            @if($account->notes)
                <div class="row mt-3">
                    <div class="col-12">
                        <div class="form-group">
                            <label class="form-label text-muted">Notes</label>
                            <p class="bg-light p-3 rounded">{{ $account->notes }}</p>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <div class="text-center mt-4">
        <a href="{{ route('accounting.accounts.index') }}" class="btn btn-outline-secondary btn-lg">
            <i class="fas fa-arrow-left"></i> Back to Accounts
        </a>
    </div>
@stop
