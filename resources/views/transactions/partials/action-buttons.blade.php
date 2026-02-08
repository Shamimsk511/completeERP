<div class="action-buttons">
    <a href="{{ route('transactions.show', $transaction->id) }}" 
       class="btn modern-btn modern-btn-info btn-sm" 
       data-toggle="tooltip" 
       title="View Transaction">
        <i class="fas fa-eye"></i>
    </a>
    
    <a href="{{ route('transactions.edit', $transaction->id) }}" 
       class="btn modern-btn modern-btn-warning btn-sm" 
       data-toggle="tooltip" 
       title="Edit Transaction">
        <i class="fas fa-edit"></i>
    </a>
    
    <button type="button" 
            class="btn modern-btn modern-btn-danger btn-sm delete-btn" 
            data-id="{{ $transaction->id }}" 
            data-toggle="tooltip" 
            title="Delete Transaction">
        <i class="fas fa-trash"></i>
    </button>
</div>
