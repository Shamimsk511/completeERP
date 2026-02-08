@extends('layouts.modern-admin')

@section('title', 'Account Group - ' . $accountGroup->name)

@section('page_title', $accountGroup->name)

@section('header_actions')
    @if(!$accountGroup->is_system)
        <a href="{{ route('accounting.account-groups.edit', $accountGroup) }}" class="btn modern-btn modern-btn-warning">
            <i class="fas fa-edit"></i> Edit
        </a>
    @endif
@stop

@section('page_content')
    <div class="card modern-card">
        <div class="card-header modern-header">
            <h3 class="card-title">
                <i class="fas fa-folder"></i> Group Details
            </h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <table class="table table-borderless">
                        <tr>
                            <td class="text-muted" width="40%">Group Code:</td>
                            <td><strong>{{ $accountGroup->code }}</strong></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Group Name:</td>
                            <td><strong>{{ $accountGroup->name }}</strong></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Parent Group:</td>
                            <td>
                                @if($accountGroup->parent)
                                    <a href="{{ route('accounting.account-groups.show', $accountGroup->parent) }}">
                                        {{ $accountGroup->parent->name }}
                                    </a>
                                @else
                                    <span class="text-muted">Root Level</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted">Nature:</td>
                            <td><span class="badge badge-info">{{ ucfirst($accountGroup->nature) }}</span></td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-borderless">
                        <tr>
                            <td class="text-muted" width="40%">Affects Gross Profit:</td>
                            <td>{{ $accountGroup->affects_gross_profit === 'yes' ? 'Yes' : 'No' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Display Order:</td>
                            <td>{{ $accountGroup->display_order }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">System Group:</td>
                            <td>
                                @if($accountGroup->is_system)
                                    <span class="badge badge-warning">Yes</span>
                                @else
                                    <span class="badge badge-secondary">No</span>
                                @endif
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            @if($accountGroup->description)
                <div class="row mt-3">
                    <div class="col-12">
                        <div class="form-group">
                            <label class="form-label text-muted">Description</label>
                            <p class="bg-light p-3 rounded">{{ $accountGroup->description }}</p>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Child Groups -->
    @if($accountGroup->children->count() > 0)
        <div class="card modern-card mt-4">
            <div class="card-header modern-header success-header">
                <h3 class="card-title">
                    <i class="fas fa-folder-open"></i> Sub Groups ({{ $accountGroup->children->count() }})
                </h3>
            </div>
            <div class="card-body">
                <div class="list-group">
                    @foreach($accountGroup->children as $child)
                        <a href="{{ route('accounting.account-groups.show', $child) }}" class="list-group-item list-group-item-action">
                            <i class="fas fa-folder text-warning"></i>
                            {{ $child->name }} <small class="text-muted">({{ $child->code }})</small>
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    <!-- Accounts in this Group -->
    @if($accountGroup->accounts->count() > 0)
        <div class="card modern-card mt-4">
            <div class="card-header modern-header info-header">
                <h3 class="card-title">
                    <i class="fas fa-book"></i> Accounts ({{ $accountGroup->accounts->count() }})
                </h3>
            </div>
            <div class="card-body p-0">
                <table class="table modern-table">
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Name</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($accountGroup->accounts as $account)
                            <tr>
                                <td>{{ $account->code }}</td>
                                <td>{{ $account->name }}</td>
                                <td><span class="badge badge-secondary">{{ ucfirst($account->account_type) }}</span></td>
                                <td>
                                    @if($account->is_active)
                                        <span class="badge badge-success">Active</span>
                                    @else
                                        <span class="badge badge-secondary">Inactive</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('accounting.accounts.show', $account) }}" class="btn btn-sm btn-info">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('accounting.accounts.ledger', $account) }}" class="btn btn-sm btn-primary">
                                        <i class="fas fa-book"></i>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <div class="text-center mt-4">
        <a href="{{ route('accounting.account-groups.index') }}" class="btn btn-outline-secondary btn-lg">
            <i class="fas fa-arrow-left"></i> Back to Chart of Accounts
        </a>
    </div>
@stop
