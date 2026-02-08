{{-- 
File: resources/views/cash-registers/partials/metrics-row.blade.php
This partial displays key metrics for the cash register
--}}

<div class="row metrics-row">
    <!-- Opening Balance -->
    <div class="col-lg-3 col-md-6">
        <div class="metric-card metric-primary">
            <div class="metric-icon">
                <i class="fas fa-door-open"></i>
            </div>
            <div class="metric-content">
                <div class="metric-value">৳{{ number_format($cashRegister->opening_balance, 2) }}</div>
                <div class="metric-label">Opening Balance</div>
                <div class="metric-info">
                    <i class="fas fa-calendar"></i>
                    {{ $cashRegister->opened_at->format('d M Y, h:i A') }}
                </div>
            </div>
            <div class="metric-trend">
                <i class="fas fa-play text-primary"></i>
                <span class="text-primary">Initial</span>
            </div>
        </div>
    </div>
    
    <!-- Current Balance -->
    <div class="col-lg-3 col-md-6">
        <div class="metric-card metric-success">
            <div class="metric-icon">
                <i class="fas fa-wallet"></i>
            </div>
            <div class="metric-content">
                <div class="metric-value">৳{{ number_format($currentBalance, 2) }}</div>
                <div class="metric-label">Current Balance</div>
                <div class="metric-info">
                    @php
                        $change = $currentBalance - $cashRegister->opening_balance;
                    @endphp
                    <i class="fas fa-{{ $change >= 0 ? 'arrow-up' : 'arrow-down' }} text-{{ $change >= 0 ? 'success' : 'danger' }}"></i>
                    {{ $change >= 0 ? '+' : '' }}৳{{ number_format(abs($change), 2) }} from opening
                </div>
            </div>
            <div class="metric-trend">
                @if($cashRegister->status === 'open')
                    <i class="fas fa-circle text-success"></i>
                    <span class="text-success">Live</span>
                @else
                    <i class="fas fa-check text-muted"></i>
                    <span class="text-muted">Final</span>
                @endif
            </div>
        </div>
    </div>
    
    <!-- Total Transactions -->
    <div class="col-lg-3 col-md-6">
        <div class="metric-card metric-info">
            <div class="metric-icon">
                <i class="fas fa-receipt"></i>
            </div>
            <div class="metric-content">
                <div class="metric-value">{{ $cashRegister->transactions->where('transaction_type', '!=', 'opening_balance')->count() }}</div>
                <div class="metric-label">Total Transactions</div>
                <div class="metric-info">
                    @if($cashRegister->transactions->count() > 1)
                        <i class="fas fa-clock"></i>
                        Last: {{ $cashRegister->transactions->first()->created_at->diffForHumans() }}
                    @else
                        <i class="fas fa-info-circle"></i>
                        Only opening balance recorded
                    @endif
                </div>
            </div>
            <div class="metric-trend">
                <i class="fas fa-chart-line text-info"></i>
                <span class="text-info">Activity</span>
            </div>
        </div>
    </div>
    
    <!-- Session Duration / Variance -->
    <div class="col-lg-3 col-md-6">
        @if($cashRegister->status === 'closed' && isset($cashRegister->variance))
            <!-- Show Variance for Closed Registers -->
            <div class="metric-card {{ $cashRegister->variance == 0 ? 'metric-success' : ($cashRegister->variance > 0 ? 'metric-warning' : 'metric-danger') }}">
                <div class="metric-icon">
                    <i class="fas fa-balance-scale"></i>
                </div>
                <div class="metric-content">
                    <div class="metric-value">
                        @if($cashRegister->variance == 0)
                            ৳0.00
                        @else
                            {{ $cashRegister->variance > 0 ? '+' : '' }}৳{{ number_format(abs($cashRegister->variance), 2) }}
                        @endif
                    </div>
                    <div class="metric-label">Cash Variance</div>
                    <div class="metric-info">
                        <i class="fas fa-{{ $cashRegister->variance == 0 ? 'check' : ($cashRegister->variance > 0 ? 'arrow-up' : 'arrow-down') }}"></i>
                        @if($cashRegister->variance == 0)
                            Perfect balance match
                        @elseif($cashRegister->variance > 0)
                            Surplus detected
                        @else
                            Shortage detected
                        @endif
                    </div>
                </div>
                <div class="metric-trend">
                    @if($cashRegister->variance == 0)
                        <i class="fas fa-star text-success"></i>
                        <span class="text-success">Perfect</span>
                    @elseif($cashRegister->variance > 0)
                        <i class="fas fa-exclamation-triangle text-warning"></i>
                        <span class="text-warning">Surplus</span>
                    @else
                        <i class="fas fa-exclamation-triangle text-danger"></i>
                        <span class="text-danger">Shortage</span>
                    @endif
                </div>
            </div>
        @else
            <!-- Show Session Duration for Open/Suspended Registers -->
            <div class="metric-card metric-secondary">
                <div class="metric-icon">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="metric-content">
                    <div class="metric-value" id="session-duration">{{ $cashRegister->getSessionDuration() }}</div>
                    <div class="metric-label">Session Duration</div>
                    <div class="metric-info">
                        <i class="fas fa-play"></i>
                        Started {{ $cashRegister->opened_at->format('h:i A') }}
                        @if($cashRegister->status === 'suspended')
                            <span class="text-warning">(Suspended)</span>
                        @endif
                    </div>
                </div>
                <div class="metric-trend">
                    @if($cashRegister->needsBreak())
                        <i class="fas fa-coffee text-warning"></i>
                        <span class="text-warning">Break Due</span>
                    @else
                        <i class="fas fa-running text-secondary"></i>
                        <span class="text-secondary">Active</span>
                    @endif
                </div>
            </div>
        @endif
    </div>
</div>

<!-- Performance Indicators (only for closed registers) -->
@if($cashRegister->status === 'closed')
    <div class="row performance-indicators">
        <div class="col-12">
            <div class="performance-card">
                <div class="performance-header">
                    <h5><i class="fas fa-chart-line"></i> Session Performance</h5>
                </div>
                <div class="performance-body">
                    <div class="performance-grid">
                        <!-- Accuracy Score -->
                        <div class="performance-item">
                            <div class="performance-icon accuracy">
                                <i class="fas fa-bullseye"></i>
                            </div>
                            <div class="performance-content">
                                <div class="performance-value">{{ number_format($cashRegister->getAccuracyPercentage(), 1) }}%</div>
                                <div class="performance-label">Accuracy Score</div>
                            </div>
                        </div>
                        
                        <!-- Average Transaction -->
                        <div class="performance-item">
                            <div class="performance-icon average">
                                <i class="fas fa-calculator"></i>
                            </div>
                            <div class="performance-content">
                                <div class="performance-value">৳{{ number_format($cashRegister->getAverageTransactionAmount(), 2) }}</div>
                                <div class="performance-label">Avg Transaction</div>
                            </div>
                        </div>
                        
                        <!-- Transactions per Hour -->
                        <div class="performance-item">
                            <div class="performance-icon rate">
                                <i class="fas fa-tachometer-alt"></i>
                            </div>
                            <div class="performance-content">
                                @php
                                    $sessionHours = max(1, $cashRegister->getSessionDurationInMinutes() / 60);
                                    $transactionRate = $cashRegister->getTotalTransactionCount() / $sessionHours;
                                @endphp
                                <div class="performance-value">{{ number_format($transactionRate, 1) }}</div>
                                <div class="performance-label">Transactions/Hour</div>
                            </div>
                        </div>
                        
                        <!-- Net Cash Flow -->
                        <div class="performance-item">
                            <div class="performance-icon flow {{ $cashRegister->getNetCashFlow() >= 0 ? 'positive' : 'negative' }}">
                                <i class="fas fa-{{ $cashRegister->getNetCashFlow() >= 0 ? 'arrow-up' : 'arrow-down' }}"></i>
                            </div>
                            <div class="performance-content">
                                <div class="performance-value {{ $cashRegister->getNetCashFlow() >= 0 ? 'text-success' : 'text-danger' }}">
                                    {{ $cashRegister->getNetCashFlow() >= 0 ? '+' : '' }}৳{{ number_format(abs($cashRegister->getNetCashFlow()), 2) }}
                                </div>
                                <div class="performance-label">Net Cash Flow</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif

<style>
/* Metrics Row Styles */
.metrics-row {
    margin-bottom: 32px;
}

.metric-card {
    background: white;
    border-radius: 16px;
    padding: 24px;
    margin-bottom: 24px;
    position: relative;
    overflow: hidden;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    transition: all 0.3s ease;
    border: none;
    height: 100%;
}

.metric-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
}

.metric-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
}

.metric-primary::before {
    background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
}

.metric-success::before {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
}

.metric-info::before {
    background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);
}

.metric-secondary::before {
    background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
}

.metric-warning::before {
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
}

.metric-danger::before {
    background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
}

.metric-icon {
    position: absolute;
    top: 24px;
    right: 24px;
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    color: white;
    opacity: 0.9;
}

.metric-primary .metric-icon {
    background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
}

.metric-success .metric-icon {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
}

.metric-info .metric-icon {
    background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);
}

.metric-secondary .metric-icon {
    background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
}

.metric-warning .metric-icon {
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
}

.metric-danger .metric-icon {
    background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
}

.metric-content {
    padding-right: 70px;
}

.metric-value {
    font-size: 28px;
    font-weight: 700;
    color: #1f2937;
    margin-bottom: 4px;
    line-height: 1;
}

.metric-label {
    font-size: 14px;
    color: #6b7280;
    font-weight: 500;
    margin-bottom: 8px;
}

.metric-info {
    display: flex;
    align-items: center;
    gap: 4px;
    font-size: 12px;
    color: #9ca3af;
    font-weight: 500;
}

.metric-trend {
    display: flex;
    align-items: center;
    gap: 4px;
    font-size: 12px;
    font-weight: 600;
    margin-top: 8px;
}

/* Performance Indicators */
.performance-indicators {
    margin-bottom: 32px;
}

.performance-card {
    background: white;
    border-radius: 16px;
    padding: 24px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    border: 2px solid #e5e7eb;
}

.performance-header {
    margin-bottom: 20px;
    padding-bottom: 16px;
    border-bottom: 2px solid #f1f5f9;
}

.performance-header h5 {
    margin: 0;
    color: #374151;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 8px;
}

.performance-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 24px;
}

.performance-item {
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 16px;
    background: #f8fafc;
    border-radius: 12px;
    border: 1px solid #e2e8f0;
    transition: all 0.2s ease;
}

.performance-item:hover {
    background: #f1f5f9;
    border-color: #cbd5e1;
    transform: translateY(-1px);
}

.performance-icon {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 18px;
    flex-shrink: 0;
}

.performance-icon.accuracy {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
}

.performance-icon.average {
    background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);
}

.performance-icon.rate {
    background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
}

.performance-icon.flow.positive {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
}

.performance-icon.flow.negative {
    background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
}

.performance-content {
    flex: 1;
}

.performance-value {
    font-size: 20px;
    font-weight: 700;
    color: #1f2937;
    line-height: 1.2;
    margin-bottom: 4px;
}

.performance-label {
    font-size: 13px;
    color: #6b7280;
    font-weight: 500;
}

/* Live update animation */
#session-duration {
    transition: all 0.3s ease;
}

.metric-live-update {
    animation: pulseUpdate 0.5s ease;
}

@keyframes pulseUpdate {
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

/* Responsive design */
@media (max-width: 768px) {
    .metric-card {
        padding: 20px;
        margin-bottom: 16px;
    }

    .metric-value {
        font-size: 24px;
    }

    .metric-icon {
        width: 40px;
        height: 40px;
        font-size: 16px;
        top: 20px;
        right: 20px;
    }

    .metric-content {
        padding-right: 60px;
    }

    .performance-grid {
        grid-template-columns: 1fr;
        gap: 16px;
    }

    .performance-item {
        padding: 12px;
    }

    .performance-icon {
        width: 40px;
        height: 40px;
        font-size: 16px;
    }

    .performance-value {
        font-size: 18px;
    }
}

/* Animation for metrics updates */
.metric-card.updated .metric-value {
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

/* Break warning styling */
.metric-trend .text-warning {
    animation: blinkWarning 2s infinite;
}

@keyframes blinkWarning {
    0%, 50% { opacity: 1; }
    75% { opacity: 0.5; }
    100% { opacity: 1; }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Update session duration for open registers
    @if($cashRegister->status === 'open')
    function updateSessionDuration() {
        const openedAt = new Date('{{ $cashRegister->opened_at->toISOString() }}');
        const now = new Date();
        const diff = now - openedAt;
        
        const hours = Math.floor(diff / (1000 * 60 * 60));
        const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
        
        let durationText;
        if (hours > 0) {
            durationText = `${hours}h ${minutes}m`;
        } else {
            durationText = `${minutes}m`;
        }
        
        const durationElement = document.getElementById('session-duration');
        if (durationElement && durationElement.textContent !== durationText) {
            durationElement.textContent = durationText;
            durationElement.classList.add('metric-live-update');
            
            setTimeout(() => {
                durationElement.classList.remove('metric-live-update');
            }, 500);
        }
    }
    
    // Update every minute
    updateSessionDuration();
    setInterval(updateSessionDuration, 60000);
    
    // Check for break time warning
    function checkBreakTime() {
        const openedAt = new Date('{{ $cashRegister->opened_at->toISOString() }}');
        const now = new Date();
        const hoursWorked = (now - openedAt) / (1000 * 60 * 60);
        
        // Show break reminder after 4 hours
        if (hoursWorked >= 4 && !sessionStorage.getItem('break-reminder-shown')) {
            if (typeof toastr !== 'undefined') {
                toastr.warning('Consider taking a break. You\'ve been working for ' + Math.floor(hoursWorked) + ' hours.', 'Break Reminder', {
                    timeOut: 8000
                });
                sessionStorage.setItem('break-reminder-shown', 'true');
            }
        }
        
        // Show break warning after 6 hours
        if (hoursWorked >= 6 && !sessionStorage.getItem('break-warning-shown')) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Break Overdue!',
                    text: 'You\'ve been working for over 6 hours. Please consider taking a break for your wellbeing.',
                    icon: 'warning',
                    confirmButtonText: 'I\'ll take a break soon',
                    confirmButtonColor: '#f59e0b'
                });
                sessionStorage.setItem('break-warning-shown', 'true');
            }
        }
    }
    
    // Check break time every 30 minutes
    checkBreakTime();
    setInterval(checkBreakTime, 30 * 60 * 1000);
    @endif
    
    // Add click handlers for metric cards (optional - for drill-down)
    document.querySelectorAll('.metric-card').forEach(card => {
        card.addEventListener('click', function() {
            // Add a subtle click effect
            this.style.transform = 'scale(0.98)';
            setTimeout(() => {
                this.style.transform = '';
            }, 150);
        });
    });
    
    // Animate metrics on page load
    setTimeout(() => {
        document.querySelectorAll('.metric-card').forEach((card, index) => {
            setTimeout(() => {
                card.classList.add('updated');
                setTimeout(() => {
                    card.classList.remove('updated');
                }, 600);
            }, index * 100);
        });
    }, 500);
});
</script>