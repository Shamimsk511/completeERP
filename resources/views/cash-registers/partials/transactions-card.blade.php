{{-- 
File: resources/views/cash-registers/partials/transactions-card.blade.php
This partial displays all transactions for the cash register
--}}

<div class="card modern-card">
    <div class="card-header modern-header">
        <div class="header-content">
            <div class="header-title">
                <i class="fas fa-list header-icon"></i>
                <h3 class="card-title">Transaction History</h3>
            </div>
            <div class="header-actions">
                @if($cashRegister->status === 'open')
                    <button class="btn modern-btn modern-btn-primary" data-toggle="modal" data-target="#addTransactionModal">
                        <i class="fas fa-plus"></i> Add Transaction
                    </button>
                @endif
                <button class="btn modern-btn-sm modern-btn-outline" onclick="refreshTransactions()" title="Refresh Transactions">
                    <i class="fas fa-sync-alt"></i>
                </button>
            </div>
        </div>
    </div>
    <div class="card-body modern-card-body p-0">
        <!-- Mobile View -->
        <div class="mobile-transactions-container">
            @forelse($cashRegister->transactions as $transaction)
                <div class="transaction-card transaction-{{ $transaction->transaction_type }}" data-transaction-type="{{ $transaction->transaction_type }}">
                    <div class="transaction-header">
                        <div class="transaction-icon {{ $transaction->type_color }}">
                            <i class="{{ $transaction->type_icon }}"></i>
                        </div>
                        <div class="transaction-info">
                            <div class="transaction-type">{{ ucfirst(str_replace('_', ' ', $transaction->transaction_type)) }}</div>
                            <div class="transaction-time">{{ $transaction->created_at->format('h:i A') }}</div>
                        </div>
                        <div class="transaction-amount {{ $transaction->isIncomeTransaction() ? 'income' : 'expense' }}">
                            {{ $transaction->isIncomeTransaction() ? '+' : '-' }}{{ $transaction->formatted_amount }}
                        </div>
                    </div>
                    
                    @if($transaction->payment_method !== 'system')
                        <div class="transaction-details">
                            <div class="detail-item">
                                <span class="detail-label">Method:</span>
                                <span class="detail-value">
                                    <i class="{{ $transaction->method_icon }}"></i>
                                    {{ ucfirst(str_replace('_', ' ', $transaction->payment_method)) }}
                                </span>
                            </div>
                            
                            @if($transaction->reference_number)
                                <div class="detail-item">
                                    <span class="detail-label">Reference:</span>
                                    <span class="detail-value">{{ $transaction->reference_number }}</span>
                                </div>
                            @endif
                            
                            @if($transaction->notes && !$transaction->isSystemTransaction())
                                <div class="detail-item">
                                    <span class="detail-label">Notes:</span>
                                    <span class="detail-value">{{ Str::limit($transaction->notes, 50) }}</span>
                                </div>
                            @endif
                        </div>
                    @endif
                    
                    @if($cashRegister->status === 'open' && $transaction->canBeVoided())
                        <div class="transaction-actions">
                            <button class="btn btn-sm btn-outline-danger void-transaction-btn" 
                                    data-transaction-id="{{ $transaction->id }}"
                                    title="Void Transaction">
                                <i class="fas fa-ban"></i> Void
                            </button>
                        </div>
                    @endif
                </div>
            @empty
                <div class="empty-transactions">
                    <div class="empty-icon">
                        <i class="fas fa-receipt"></i>
                    </div>
                    <h5>No Transactions Yet</h5>
                    <p>Start by adding your first transaction</p>
                    @if($cashRegister->status === 'open')
                        <button class="btn modern-btn modern-btn-primary" data-toggle="modal" data-target="#addTransactionModal">
                            <i class="fas fa-plus"></i> Add First Transaction
                        </button>
                    @endif
                </div>
            @endforelse
        </div>
        
        <!-- Desktop View -->
        <div class="desktop-transactions-container">
            <div class="table-responsive modern-table-responsive">
                <table class="table modern-table transactions-table">
                    <thead class="modern-thead">
                        <tr>
                            <th>Time</th>
                            <th>Type</th>
                            <th>Method</th>
                            <th>Amount</th>
                            <th>Reference</th>
                            <th>Notes</th>
                            @if($cashRegister->status === 'open')
                                <th>Actions</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody class="modern-tbody">
                        @forelse($cashRegister->transactions as $transaction)
                            <tr class="transaction-row transaction-{{ $transaction->transaction_type }}" 
                                data-transaction-type="{{ $transaction->transaction_type }}"
                                data-transaction-id="{{ $transaction->id }}">
                                <td>
                                    <div class="time-cell">
                                        <div class="transaction-time">{{ $transaction->created_at->format('h:i A') }}</div>
                                        <div class="transaction-date">{{ $transaction->created_at->format('d M') }}</div>
                                    </div>
                                </td>
                                <td>
                                    <div class="type-cell">
                                        <div class="type-icon {{ $transaction->type_color }}">
                                            <i class="{{ $transaction->type_icon }}"></i>
                                        </div>
                                        <div class="type-text">
                                            <div class="type-name">{{ ucfirst(str_replace('_', ' ', $transaction->transaction_type)) }}</div>
                                            @if($transaction->isSystemTransaction())
                                                <div class="type-badge">System</div>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="method-cell">
                                        <i class="{{ $transaction->method_icon }}"></i>
                                        {{ ucfirst(str_replace('_', ' ', $transaction->payment_method)) }}
                                    </div>
                                </td>
                                <td>
                                    <div class="amount-cell {{ $transaction->isIncomeTransaction() ? 'income' : 'expense' }}">
                                        {{ $transaction->isIncomeTransaction() ? '+' : '-' }}{{ $transaction->formatted_amount }}
                                    </div>
                                </td>
                                <td>
                                    <div class="reference-cell">
                                        {{ $transaction->reference_number ?: '-' }}
                                    </div>
                                </td>
                                <td>
                                    <div class="notes-cell">
                                        @if($transaction->notes)
                                            <span class="notes-preview" title="{{ $transaction->notes }}">
                                                {{ Str::limit($transaction->notes, 30) }}
                                            </span>
                                            @if(strlen($transaction->notes) > 30)
                                                <button class="btn btn-sm btn-link p-0 view-notes-btn" 
                                                        data-notes="{{ $transaction->notes }}"
                                                        title="View Full Notes">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                            @endif
                                        @else
                                            -
                                        @endif
                                    </div>
                                </td>
                                @if($cashRegister->status === 'open')
                                    <td>
                                        <div class="action-buttons">
                                            @if($transaction->canBeVoided())
                                                <button class="btn modern-btn-sm modern-btn-danger void-transaction-btn" 
                                                        data-transaction-id="{{ $transaction->id }}"
                                                        title="Void Transaction">
                                                    <i class="fas fa-ban"></i>
                                                </button>
                                            @endif
                                            <button class="btn modern-btn-sm modern-btn-info" 
                                                    onclick="showTransactionDetails({{ $transaction->id }})"
                                                    title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        </div>
                                    </td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $cashRegister->status === 'open' ? '7' : '6' }}" class="text-center py-5">
                                    <div class="empty-transactions">
                                        <div class="empty-icon">
                                            <i class="fas fa-receipt"></i>
                                        </div>
                                        <h5>No Transactions Yet</h5>
                                        <p>Start by adding your first transaction</p>
                                        @if($cashRegister->status === 'open')
                                            <button class="btn modern-btn modern-btn-primary" data-toggle="modal" data-target="#addTransactionModal">
                                                <i class="fas fa-plus"></i> Add First Transaction
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Transaction Summary Footer -->
        @if($cashRegister->transactions->count() > 0)
            <div class="transactions-footer">
                <div class="footer-summary">
                    <div class="summary-item">
                        <span class="summary-label">Total Transactions:</span>
                        <span class="summary-value">{{ $cashRegister->transactions->count() }}</span>
                    </div>
                    <div class="summary-item">
                        <span class="summary-label">Last Updated:</span>
                        <span class="summary-value">{{ $cashRegister->transactions->first()->created_at->diffForHumans() }}</span>
                    </div>
                    <div class="summary-item">
                        <span class="summary-label">Current Balance:</span>
                        <span class="summary-value balance-amount">৳{{ number_format($currentBalance, 2) }}</span>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>

<!-- Transaction Notes Modal -->
<div class="modal fade" id="transactionNotesModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content modern-modal">
            <div class="modal-header modern-modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-sticky-note"></i>
                    Transaction Notes
                </h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="notes-content" id="transactionNotesContent">
                    <!-- Notes will be loaded here -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn modern-btn modern-btn-outline" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<style>
/* Transactions Card Styles */
.mobile-transactions-container {
    padding: 16px;
}

.transaction-card {
    background: white;
    border-radius: 12px;
    padding: 16px;
    margin-bottom: 12px;
    border: 2px solid #e5e7eb;
    transition: all 0.2s ease;
    position: relative;
}

.transaction-card:hover {
    border-color: #6366f1;
    box-shadow: 0 4px 12px rgba(99, 102, 241, 0.1);
}

.transaction-card::before {
    content: '';
    position: absolute;
    left: 0;
    top: 0;
    bottom: 0;
    width: 4px;
    border-radius: 12px 0 0 12px;
}

.transaction-sale::before { background: #10b981; }
.transaction-return::before { background: #ef4444; }
.transaction-expense::before { background: #f59e0b; }
.transaction-deposit::before { background: #06b6d4; }
.transaction-withdrawal::before { background: #8b5cf6; }
.transaction-opening_balance::before { background: #6366f1; }
.transaction-closing_balance::before { background: #374151; }
.transaction-suspension::before { background: #f59e0b; }
.transaction-resumption::before { background: #10b981; }
.transaction-void::before { background: #ef4444; }

.transaction-header {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 12px;
}

.transaction-icon {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 16px;
    flex-shrink: 0;
}

.transaction-icon.success { background: linear-gradient(135deg, #10b981 0%, #059669 100%); }
.transaction-icon.danger { background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); }
.transaction-icon.warning { background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); }
.transaction-icon.info { background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%); }
.transaction-icon.secondary { background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%); }
.transaction-icon.primary { background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%); }
.transaction-icon.dark { background: linear-gradient(135deg, #374151 0%, #1f2937 100%); }

.transaction-info {
    flex: 1;
}

.transaction-type {
    font-size: 14px;
    font-weight: 600;
    color: #374151;
    line-height: 1.2;
}

.transaction-time {
    font-size: 12px;
    color: #6b7280;
    font-weight: 500;
}

.transaction-amount {
    font-size: 16px;
    font-weight: 700;
    text-align: right;
}

.transaction-amount.income {
    color: #059669;
}

.transaction-amount.expense {
    color: #dc2626;
}

.transaction-details {
    background: #f8fafc;
    border-radius: 8px;
    padding: 12px;
    margin-bottom: 8px;
}

.detail-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 4px;
}

.detail-item:last-child {
    margin-bottom: 0;
}

.detail-label {
    font-size: 12px;
    color: #6b7280;
    font-weight: 500;
}

.detail-value {
    font-size: 13px;
    color: #374151;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 4px;
}

.transaction-actions {
    text-align: center;
    margin-top: 8px;
}

/* Desktop Table Styles */
.desktop-transactions-container {
    max-height: 600px;
    overflow-y: auto;
}

.transactions-table {
    margin-bottom: 0;
}

.time-cell {
    text-align: center;
}

.transaction-time {
    font-size: 14px;
    font-weight: 600;
    color: #374151;
    line-height: 1.2;
}

.transaction-date {
    font-size: 11px;
    color: #6b7280;
    font-weight: 500;
}

.type-cell {
    display: flex;
    align-items: center;
    gap: 12px;
}

.type-icon {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 14px;
    flex-shrink: 0;
}

.type-text {
    flex: 1;
}

.type-name {
    font-size: 13px;
    font-weight: 600;
    color: #374151;
    line-height: 1.2;
}

.type-badge {
    font-size: 10px;
    color: #6b7280;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.method-cell {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 13px;
    color: #374151;
    font-weight: 500;
}

.amount-cell {
    font-size: 15px;
    font-weight: 700;
    text-align: right;
}

.amount-cell.income {
    color: #059669;
}

.amount-cell.expense {
    color: #dc2626;
}

.reference-cell {
    font-size: 13px;
    color: #374151;
    font-family: 'Monaco', 'Consolas', monospace;
}

.notes-cell {
    max-width: 200px;
}

.notes-preview {
    font-size: 13px;
    color: #6b7280;
    line-height: 1.4;
}

.view-notes-btn {
    margin-left: 4px;
    color: #6366f1;
}

.view-notes-btn:hover {
    color: #4f46e5;
}

/* Empty State */
.empty-transactions {
    text-align: center;
    padding: 60px 20px;
    color: #6b7280;
}

.empty-icon {
    font-size: 48px;
    margin-bottom: 16px;
    opacity: 0.5;
}

.empty-transactions h5 {
    margin-bottom: 8px;
    color: #374151;
}

.empty-transactions p {
    margin-bottom: 20px;
    font-size: 14px;
}

/* Footer Summary */
.transactions-footer {
    background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
    border-top: 1px solid #e2e8f0;
    padding: 16px 20px;
}

.footer-summary {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 16px;
}

.summary-item {
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.summary-label {
    font-size: 11px;
    color: #6b7280;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.summary-value {
    font-size: 14px;
    font-weight: 600;
    color: #374151;
}

.balance-amount {
    color: #059669 !important;
    font-size: 16px !important;
}

/* Transaction highlight animation */
.transaction-card.highlighted,
.transaction-row.highlighted {
    animation: highlight 2s ease;
}

@keyframes highlight {
    0% { background-color: rgba(99, 102, 241, 0.1); }
    100% { background-color: transparent; }
}

/* Mobile responsiveness */
@media (max-width: 768px) {
    .mobile-transactions-container {
        display: block !important;
    }

    .desktop-transactions-container {
        display: none !important;
    }

    .footer-summary {
        flex-direction: column;
        align-items: flex-start;
        gap: 8px;
    }

    .summary-item {
        width: 100%;
        flex-direction: row;
        justify-content: space-between;
        align-items: center;
        padding: 8px 0;
        border-bottom: 1px solid #e2e8f0;
    }

    .summary-item:last-child {
        border-bottom: none;
    }
}

@media (min-width: 769px) {
    .mobile-transactions-container {
        display: none !important;
    }

    .desktop-transactions-container {
        display: block !important;
    }
}

/* Loading states */
.transactions-loading {
    position: relative;
    opacity: 0.7;
}

.transactions-loading::after {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 32px;
    height: 32px;
    border: 3px solid #e2e8f0;
    border-top-color: #6366f1;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    to { transform: translate(-50%, -50%) rotate(360deg); }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // View notes button handler
    document.querySelectorAll('.view-notes-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const notes = this.dataset.notes;
            document.getElementById('transactionNotesContent').textContent = notes;
            $('#transactionNotesModal').modal('show');
        });
    });

    // Refresh transactions function
    window.refreshTransactions = function() {
        const containers = document.querySelectorAll('.mobile-transactions-container, .desktop-transactions-container');
        
        containers.forEach(container => {
            container.classList.add('transactions-loading');
        });

        // Simulate refresh (in real app, this would be an AJAX call)
        setTimeout(() => {
            containers.forEach(container => {
                container.classList.remove('transactions-loading');
            });
            
            if (typeof toastr !== 'undefined') {
                toastr.success('Transactions refreshed');
            }
        }, 1000);
    };
});
</script>