<div class="d-flex">
    <a href="{{ route('accounting.accounts.show', $account) }}" class="action-btn btn-view" title="View">
        <i class="fas fa-eye"></i>
    </a>
    <a href="{{ route('accounting.accounts.ledger', $account) }}" class="action-btn btn-ledger" title="View Ledger">
        <i class="fas fa-book"></i>
    </a>
    @if(!$account->is_system)
        <a href="{{ route('accounting.accounts.edit', $account) }}" class="action-btn btn-edit" title="Edit">
            <i class="fas fa-edit"></i>
        </a>
        @if($account->canDelete())
            <button class="action-btn btn-delete delete-account" data-id="{{ $account->id }}" title="Delete">
                <i class="fas fa-trash"></i>
            </button>
        @endif
    @endif
</div>
