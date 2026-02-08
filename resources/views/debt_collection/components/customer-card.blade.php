{{-- File: resources/views/debt_collection/components/customer-card.blade.php --}}

<div class="customer-card modern-card" data-customer-id="{{ $customer->id }}">
    <div class="customer-header">
        <div class="customer-info">
            <h4 class="customer-name">{{ $customer->name }}</h4>
            <div class="customer-contact">
                <span class="customer-phone">
                    <i class="fas fa-phone"></i> {{ $customer->phone }}
                </span>
                @if($customer->email)
                    <span class="customer-email">
                        <i class="fas fa-envelope"></i> {{ $customer->email }}
                    </span>
                @endif
            </div>
        </div>
        <div class="customer-badges">
            <span class="priority-badge priority-{{ $customer->priority ?? 'medium' }}">
                {{ ucfirst($customer->priority ?? 'medium') }}
            </span>
            @if($customer->days_overdue > 0)
                <span class="overdue-badge">
                    {{ $customer->days_overdue }} days overdue
                </span>
            @endif
        </div>
    </div>

    <div class="customer-metrics">
        <div class="metric-row">
            <div class="metric-item metric-primary">
                <div class="metric-icon">
                    <i class="fas fa-dollar-sign"></i>
                </div>
                <div class="metric-content">
                    <div class="metric-value">{{ number_format($customer->outstanding_balance, 2) }}</div>
                    <div class="metric-label">Outstanding Balance</div>
                </div>
            </div>
            
            <div class="metric-item metric-warning">
                <div class="metric-icon">
                    <i class="fas fa-calendar-alt"></i>
                </div>
                <div class="metric-content">
                    <div class="metric-value">
                        {{ $customer->due_date ? $customer->due_date->format('M d') : 'Not set' }}
                    </div>
                    <div class="metric-label">Due Date</div>
                </div>
            </div>
        </div>

        <div class="metric-row">
            <div class="metric-item metric-info">
                <div class="metric-icon">
                    <i class="fas fa-phone"></i>
                </div>
                <div class="metric-content">
                    <div class="metric-value">{{ $customer->calls_made ?? 0 }}</div>
                    <div class="metric-label">Calls Made</div>
                </div>
            </div>
            
            <div class="metric-item metric-danger">
                <div class="metric-icon">
                    <i class="fas fa-phone-slash"></i>
                </div>
                <div class="metric-content">
                    <div class="metric-value">{{ $customer->missed_calls ?? 0 }}</div>
                    <div class="metric-label">Missed Calls</div>
                </div>
            </div>
        </div>
    </div>

    <div class="customer-timeline">
        <div class="timeline-item">
            <div class="timeline-icon">
                <i class="fas fa-clock"></i>
            </div>
            <div class="timeline-content">
                <div class="timeline-title">Last Contact</div>
                <div class="timeline-date">
                    {{ $customer->last_call_date ? $customer->last_call_date->diffForHumans() : 'No contact yet' }}
                </div>
            </div>
        </div>
        
        @if($customer->last_payment_date)
            <div class="timeline-item">
                <div class="timeline-icon">
                    <i class="fas fa-credit-card"></i>
                </div>
                <div class="timeline-content">
                    <div class="timeline-title">Last Payment</div>
                    <div class="timeline-date">{{ $customer->last_payment_date->diffForHumans() }}</div>
                </div>
            </div>
        @endif
    </div>

    <div class="customer-actions">
        <button class="btn modern-btn modern-btn-success btn-sm log-call-btn" 
                data-customer-id="{{ $customer->id }}"
                data-customer-name="{{ $customer->name }}"
                title="Log Call">
            <i class="fas fa-phone"></i>
            <span>Call</span>
        </button>
        
        <a href="{{ route('debt-collection.call-history', $customer->id) }}" 
           class="btn modern-btn modern-btn-info btn-sm" 
           title="View Call History">
            <i class="fas fa-history"></i>
            <span>History</span>
        </a>
        
        <a href="{{ route('debt-collection.edit-tracking', $customer->id) }}" 
           class="btn modern-btn modern-btn-warning btn-sm" 
           title="Edit Tracking">
            <i class="fas fa-edit"></i>
            <span>Edit</span>
        </a>
        
        <div class="dropdown">
            <button class="btn modern-btn modern-btn-outline btn-sm dropdown-toggle" 
                    type="button" 
                    data-toggle="dropdown">
                <i class="fas fa-ellipsis-v"></i>
            </button>
            <div class="dropdown-menu dropdown-menu-right">
                <a class="dropdown-item" href="{{ route('customer.ledger', $customer->id) }}">
                    <i class="fas fa-file-invoice"></i> View Ledger
                </a>
                <a class="dropdown-item" href="{{ route('debt-collection.customer-payment-history', $customer->id) }}">
                    <i class="fas fa-chart-line"></i> Payment History
                </a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="#" onclick="schedulePayment({{ $customer->id }})">
                    <i class="fas fa-calendar-plus"></i> Schedule Payment
                </a>
                <a class="dropdown-item text-warning" href="#" onclick="markHighPriority({{ $customer->id }})">
                    <i class="fas fa-exclamation-triangle"></i> Mark High Priority
                </a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item text-danger" href="#" onclick="markWriteOff({{ $customer->id }})">
                    <i class="fas fa-times-circle"></i> Mark Write-off
                </a>
            </div>
        </div>
    </div>

    @if($customer->notes)
        <div class="customer-notes">
            <div class="notes-header">
                <i class="fas fa-sticky-note"></i>
                <span>Latest Note</span>
            </div>
            <div class="notes-content">
                {{ Str::limit($customer->getLatestNote(), 100) }}
            </div>
        </div>
    @endif
</div>

<style>
    .customer-card {
        background: white;
        border: 2px solid #e5e7eb;
        border-radius: 16px;
        padding: 20px;
        transition: all 0.3s ease;
        cursor: pointer;
        position: relative;
        overflow: hidden;
    }

    .customer-card:hover {
        border-color: #6366f1;
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(99, 102, 241, 0.15);
    }

    .customer-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 16px;
    }

    .customer-name {
        margin: 0;
        font-size: 18px;
        font-weight: 600;
        color: #374151;
    }

    .customer-contact {
        margin-top: 8px;
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .customer-phone, .customer-email {
        font-size: 14px;
        color: #6b7280;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .customer-badges {
        display: flex;
        flex-direction: column;
        gap: 8px;
        align-items: flex-end;
    }

    .priority-badge {
        padding: 4px 8px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
    }

    .priority-high {
        background: #fee2e2;
        color: #dc2626;
    }

    .priority-medium {
        background: #fef3c7;
        color: #d97706;
    }

    .priority-low {
        background: #dbeafe;
        color: #2563eb;
    }

    .overdue-badge {
        background: #fee2e2;
        color: #dc2626;
        padding: 4px 8px;
        border-radius: 12px;
        font-size: 11px;
        font-weight: 600;
    }

    .customer-metrics {
        margin-bottom: 16px;
    }

    .metric-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 12px;
        margin-bottom: 12px;
    }

    .metric-row:last-child {
        margin-bottom: 0;
    }

    .metric-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px;
        border-radius: 12px;
        background: #f8fafc;
        border: 1px solid #e5e7eb;
    }

    .metric-icon {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
        color: white;
        flex-shrink: 0;
    }

    .metric-primary .metric-icon { background: #3b82f6; }
    .metric-warning .metric-icon { background: #f59e0b; }
    .metric-info .metric-icon { background: #06b6d4; }
    .metric-danger .metric-icon { background: #ef4444; }

    .metric-content {
        flex: 1;
        min-width: 0;
    }

    .metric-value {
        font-size: 16px;
        font-weight: 700;
        color: #374151;
        line-height: 1;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .metric-label {
        font-size: 11px;
        color: #6b7280;
        margin-top: 2px;
    }

    .customer-timeline {
        margin-bottom: 16px;
        padding: 12px;
        background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
        border-radius: 12px;
        border: 1px solid #e5e7eb;
    }

    .timeline-item {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 8px;
    }

    .timeline-item:last-child {
        margin-bottom: 0;
    }

    .timeline-icon {
        width: 24px;
        height: 24px;
        border-radius: 6px;
        background: #6366f1;
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 10px;
        flex-shrink: 0;
    }

    .timeline-content {
        flex: 1;
    }

    .timeline-title {
        font-size: 12px;
        font-weight: 600;
        color: #374151;
    }

    .timeline-date {
        font-size: 11px;
        color: #6b7280;
    }

    .customer-actions {
        display: flex;
        gap: 8px;
        justify-content: flex-end;
        padding-top: 16px;
        border-top: 1px solid #e5e7eb;
        flex-wrap: wrap;
    }

    .customer-actions .btn {
        flex: 1;
        min-width: 70px;
        justify-content: center;
    }

    .customer-notes {
        margin-top: 16px;
        padding: 12px;
        background: #fffbeb;
        border: 1px solid #fbbf24;
        border-radius: 12px;
    }

    .notes-header {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 12px;
        font-weight: 600;
        color: #92400e;
        margin-bottom: 6px;
    }

    .notes-content {
        font-size: 12px;
        color: #78350f;
        line-height: 1.4;
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .customer-card {
            padding: 16px;
        }

        .customer-header {
            flex-direction: column;
            gap: 12px;
        }

        .customer-badges {
            align-items: flex-start;
            flex-direction: row;
        }

        .metric-row {
            grid-template-columns: 1fr;
            gap: 8px;
        }

        .customer-actions {
            flex-direction: column;
        }

        .customer-actions .btn {
            flex: none;
        }
    }
</style>