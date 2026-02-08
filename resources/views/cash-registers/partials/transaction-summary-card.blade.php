{{-- 
File: resources/views/cash-registers/partials/transaction-summary-card.blade.php
This partial displays the transaction summary breakdown by type
--}}

<div class="card modern-card">
    <div class="card-header modern-header">
        <div class="header-content">
            <div class="header-title">
                <i class="fas fa-chart-pie header-icon"></i>
                <h3 class="card-title">Transaction Summary</h3>
            </div>
            <div class="header-actions">
                <button class="btn modern-btn-sm modern-btn-outline" onclick="refreshSummary()" title="Refresh Summary">
                    <i class="fas fa-sync-alt"></i>
                </button>
            </div>
        </div>
    </div>
    <div class="card-body modern-card-body p-0">
        <div class="summary-list">
            <!-- Sales Summary -->
            <div class="summary-item" data-type="sale">
                <div class="summary-icon success">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <div class="summary-content">
                    <div class="summary-label">Sales</div>
                    <div class="summary-value">৳{{ number_format($totals['sale'] ?? 0, 2) }}</div>
                    @if(($totals['sale'] ?? 0) > 0)
                        <div class="summary-percentage">
                            {{ round((($totals['sale'] ?? 0) / array_sum($totals)) * 100, 1) }}% of total
                        </div>
                    @endif
                </div>
                <div class="summary-count">
                    <span class="count-number">{{ $cashRegister->transactions->where('transaction_type', 'sale')->count() }}</span>
                    <span class="count-label">transactions</span>
                </div>
            </div>
            
            <!-- Returns Summary -->
            <div class="summary-item" data-type="return">
                <div class="summary-icon danger">
                    <i class="fas fa-undo"></i>
                </div>
                <div class="summary-content">
                    <div class="summary-label">Returns</div>
                    <div class="summary-value">৳{{ number_format($totals['return'] ?? 0, 2) }}</div>
                    @if(($totals['return'] ?? 0) > 0)
                        <div class="summary-percentage">
                            {{ round((($totals['return'] ?? 0) / array_sum($totals)) * 100, 1) }}% of total
                        </div>
                    @endif
                </div>
                <div class="summary-count">
                    <span class="count-number">{{ $cashRegister->transactions->where('transaction_type', 'return')->count() }}</span>
                    <span class="count-label">transactions</span>
                </div>
            </div>
            
            <!-- Expenses Summary -->
            <div class="summary-item" data-type="expense">
                <div class="summary-icon warning">
                    <i class="fas fa-minus"></i>
                </div>
                <div class="summary-content">
                    <div class="summary-label">Expenses</div>
                    <div class="summary-value">৳{{ number_format($totals['expense'] ?? 0, 2) }}</div>
                    @if(($totals['expense'] ?? 0) > 0)
                        <div class="summary-percentage">
                            {{ round((($totals['expense'] ?? 0) / array_sum($totals)) * 100, 1) }}% of total
                        </div>
                    @endif
                </div>
                <div class="summary-count">
                    <span class="count-number">{{ $cashRegister->transactions->where('transaction_type', 'expense')->count() }}</span>
                    <span class="count-label">transactions</span>
                </div>
            </div>
            
            <!-- Deposits Summary -->
            <div class="summary-item" data-type="deposit">
                <div class="summary-icon info">
                    <i class="fas fa-plus"></i>
                </div>
                <div class="summary-content">
                    <div class="summary-label">Deposits</div>
                    <div class="summary-value">৳{{ number_format($totals['deposit'] ?? 0, 2) }}</div>
                    @if(($totals['deposit'] ?? 0) > 0)
                        <div class="summary-percentage">
                            {{ round((($totals['deposit'] ?? 0) / array_sum($totals)) * 100, 1) }}% of total
                        </div>
                    @endif
                </div>
                <div class="summary-count">
                    <span class="count-number">{{ $cashRegister->transactions->where('transaction_type', 'deposit')->count() }}</span>
                    <span class="count-label">transactions</span>
                </div>
            </div>
            
            <!-- Withdrawals Summary -->
            <div class="summary-item" data-type="withdrawal">
                <div class="summary-icon secondary">
                    <i class="fas fa-arrow-up"></i>
                </div>
                <div class="summary-content">
                    <div class="summary-label">Withdrawals</div>
                    <div class="summary-value">৳{{ number_format($totals['withdrawal'] ?? 0, 2) }}</div>
                    @if(($totals['withdrawal'] ?? 0) > 0)
                        <div class="summary-percentage">
                            {{ round((($totals['withdrawal'] ?? 0) / array_sum($totals)) * 100, 1) }}% of total
                        </div>
                    @endif
                </div>
                <div class="summary-count">
                    <span class="count-number">{{ $cashRegister->transactions->where('transaction_type', 'withdrawal')->count() }}</span>
                    <span class="count-label">transactions</span>
                </div>
            </div>
        </div>
        
        <!-- Summary Footer with Totals -->
        <div class="summary-footer">
            <div class="footer-stats">
                <div class="stat-item">
                    <div class="stat-label">Total Transactions</div>
                    <div class="stat-value">{{ $cashRegister->transactions->count() }}</div>
                </div>
                <div class="stat-item">
                    <div class="stat-label">Net Change</div>
                    <div class="stat-value {{ ($currentBalance - $cashRegister->opening_balance) >= 0 ? 'text-success' : 'text-danger' }}">
                        {{ ($currentBalance - $cashRegister->opening_balance) >= 0 ? '+' : '' }}৳{{ number_format($currentBalance - $cashRegister->opening_balance, 2) }}
                    </div>
                </div>
                <div class="stat-item">
                    <div class="stat-label">Average Transaction</div>
                    <div class="stat-value">
                        @if($cashRegister->transactions->count() > 0)
                            ৳{{ number_format(array_sum($totals) / $cashRegister->transactions->count(), 2) }}
                        @else
                            ৳0.00
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Transaction Summary Card Styles */
.summary-list {
    padding: 0;
}

.summary-item {
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 20px 24px;
    border-bottom: 1px solid #f1f5f9;
    transition: all 0.2s ease;
    cursor: pointer;
    position: relative;
}

.summary-item:last-child {
    border-bottom: none;
}

.summary-item:hover {
    background-color: #f8fafc;
    transform: translateX(4px);
}

.summary-item::before {
    content: '';
    position: absolute;
    left: 0;
    top: 0;
    bottom: 0;
    width: 4px;
    background: transparent;
    transition: all 0.2s ease;
}

.summary-item:hover::before {
    background: var(--summary-color);
}

.summary-item[data-type="sale"] {
    --summary-color: #10b981;
}

.summary-item[data-type="return"] {
    --summary-color: #ef4444;
}

.summary-item[data-type="expense"] {
    --summary-color: #f59e0b;
}

.summary-item[data-type="deposit"] {
    --summary-color: #06b6d4;
}

.summary-item[data-type="withdrawal"] {
    --summary-color: #8b5cf6;
}

.summary-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 18px;
    flex-shrink: 0;
    position: relative;
    overflow: hidden;
}

.summary-icon::before {
    content: '';
    position: absolute;
    inset: 0;
    background: inherit;
    opacity: 0.1;
    transition: opacity 0.2s ease;
}

.summary-item:hover .summary-icon::before {
    opacity: 0.2;
}

.summary-icon.success {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
}

.summary-icon.danger {
    background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
}

.summary-icon.warning {
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
}

.summary-icon.info {
    background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);
}

.summary-icon.secondary {
    background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
}

.summary-content {
    flex: 1;
    min-width: 0;
}

.summary-label {
    font-size: 14px;
    color: #6b7280;
    font-weight: 500;
    margin-bottom: 4px;
}

.summary-value {
    font-size: 20px;
    font-weight: 700;
    color: #1f2937;
    line-height: 1.2;
    margin-bottom: 2px;
}

.summary-percentage {
    font-size: 11px;
    color: #9ca3af;
    font-weight: 500;
}

.summary-count {
    text-align: center;
    min-width: 60px;
    flex-shrink: 0;
}

.count-number {
    display: block;
    font-size: 18px;
    font-weight: 700;
    color: #374151;
    line-height: 1;
}

.count-label {
    display: block;
    font-size: 10px;
    color: #9ca3af;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-top: 2px;
}

.summary-footer {
    background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
    border-top: 1px solid #e2e8f0;
    padding: 20px 24px;
}

.footer-stats {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 20px;
}

.stat-item {
    text-align: center;
}

.stat-label {
    font-size: 11px;
    color: #6b7280;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 4px;
}

.stat-value {
    font-size: 16px;
    font-weight: 700;
    color: #374151;
}

.stat-value.text-success {
    color: #059669 !important;
}

.stat-value.text-danger {
    color: #dc2626 !important;
}

/* Animation for value changes */
.summary-value,
.count-number,
.stat-value {
    transition: all 0.3s ease;
}

.summary-item.updated .summary-value,
.summary-item.updated .count-number {
    animation: valueUpdate 0.6s ease;
}

@keyframes valueUpdate {
    0% {
        transform: scale(1);
        color: inherit;
    }
    50% {
        transform: scale(1.05);
        color: #10b981;
    }
    100% {
        transform: scale(1);
        color: inherit;
    }
}

/* Mobile optimizations */
@media (max-width: 768px) {
    .summary-item {
        padding: 16px 20px;
        gap: 12px;
    }
    
    .summary-icon {
        width: 40px;
        height: 40px;
        font-size: 16px;
    }
    
    .summary-value {
        font-size: 18px;
    }
    
    .count-number {
        font-size: 16px;
    }
    
    .footer-stats {
        grid-template-columns: 1fr;
        gap: 12px;
        text-align: left;
    }
    
    .stat-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 8px 0;
        border-bottom: 1px solid #e2e8f0;
    }
    
    .stat-item:last-child {
        border-bottom: none;
    }
    
    .stat-label {
        margin-bottom: 0;
        text-align: left;
    }
    
    .stat-value {
        text-align: right;
    }
}

/* Loading state */
.summary-list.loading {
    opacity: 0.7;
    pointer-events: none;
}

.summary-list.loading::after {
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
    to {
        transform: translate(-50%, -50%) rotate(360deg);
    }
}

/* Empty state */
.summary-empty {
    text-align: center;
    padding: 60px 24px;
    color: #9ca3af;
}

.summary-empty i {
    font-size: 48px;
    margin-bottom: 16px;
    opacity: 0.5;
}

.summary-empty h5 {
    margin-bottom: 8px;
    color: #6b7280;
}

.summary-empty p {
    margin: 0;
    font-size: 14px;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Refresh summary function
    window.refreshSummary = function() {
        const summaryList = document.querySelector('.summary-list');
        const refreshBtn = document.querySelector('[onclick="refreshSummary()"]');
        
        // Show loading state
        summaryList.classList.add('loading');
        refreshBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
        
        // Simulate refresh (in real app, this would be an AJAX call)
        setTimeout(() => {
            summaryList.classList.remove('loading');
            refreshBtn.innerHTML = '<i class="fas fa-sync-alt"></i>';
            
            // Add update animation to all items
            document.querySelectorAll('.summary-item').forEach(item => {
                item.classList.add('updated');
                setTimeout(() => item.classList.remove('updated'), 600);
            });
            
            // Show success message
            if (typeof toastr !== 'undefined') {
                toastr.success('Summary refreshed successfully');
            }
        }, 1000);
    };
    
    // Add click handlers for summary items (for filtering transactions)
    document.querySelectorAll('.summary-item').forEach(item => {
        item.addEventListener('click', function() {
            const type = this.dataset.type;
            filterTransactionsByType(type);
        });
    });
    
    // Filter transactions by type function
    function filterTransactionsByType(type) {
        // Get all transaction rows/cards
        const transactions = document.querySelectorAll('[data-transaction-type], .transaction-card');
        
        // Remove previous highlights
        document.querySelectorAll('.summary-item').forEach(item => {
            item.classList.remove('active');
        });
        
        // Highlight selected summary item
        document.querySelector(`[data-type="${type}"]`).classList.add('active');
        
        // Filter transactions
        transactions.forEach(transaction => {
            const transactionType = transaction.dataset.transactionType || 
                                   transaction.classList.toString().match(/transaction-(\w+)/)?.[1];
            
            if (transactionType === type) {
                transaction.style.display = '';
                transaction.classList.add('highlighted');
                
                // Remove highlight after 2 seconds
                setTimeout(() => {
                    transaction.classList.remove('highlighted');
                }, 2000);
            } else {
                transaction.style.opacity = '0.3';
                
                // Restore opacity after 2 seconds
                setTimeout(() => {
                    transaction.style.opacity = '';
                }, 2000);
            }
        });
        
        // Remove active state after 2 seconds
        setTimeout(() => {
            document.querySelector(`[data-type="${type}"]`).classList.remove('active');
        }, 2000);
        
        // Show filter info
        if (typeof toastr !== 'undefined') {
            toastr.info(`Showing ${type} transactions`);
        }
    }
    
    // Add tooltip for summary items
    document.querySelectorAll('.summary-item').forEach(item => {
        const type = item.dataset.type;
        const typeName = type.charAt(0).toUpperCase() + type.slice(1);
        
        item.setAttribute('title', `Click to highlight ${typeName} transactions`);
        
        // Initialize tooltip if jQuery is available
        if (typeof $ !== 'undefined') {
            $(item).tooltip({
                placement: 'top',
                trigger: 'hover'
            });
        }
    });
});
</script>