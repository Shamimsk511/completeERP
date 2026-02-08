<div class="btn-group">
    <button type="button" class="btn btn-sm btn-success log-call-btn" data-customer-id="{{ $customer->id }}">
        <i class="fas fa-phone"></i>
    </button>
    <a href="{{ route('debt-collection.call-history', $customer->id) }}" class="btn btn-sm btn-info">
        <i class="fas fa-history"></i>
    </a>
    <a href="{{ route('debt-collection.edit-tracking', $customer->id) }}" class="btn btn-sm btn-primary">
        <i class="fas fa-edit"></i>
    </a>
</div>
