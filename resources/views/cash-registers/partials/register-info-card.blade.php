{{-- 
File: resources/views/cash-registers/partials/register-info-card.blade.php
This partial displays detailed information about the cash register
--}}

<div class="card modern-card">
    <div class="card-header modern-header">
        <div class="header-content">
            <div class="header-title">
                <i class="fas fa-info-circle header-icon"></i>
                <h3 class="card-title">Register Information</h3>
            </div>
            <div class="header-actions">
                @if($cashRegister->status === 'open')
                    <div class="status-indicator status-open">
                        <i class="fas fa-circle"></i>
                        <span>Live</span>
                    </div>
                @elseif($cashRegister->status === 'suspended')
                    <div class="status-indicator status-suspended">
                        <i class="fas fa-pause-circle"></i>
                        <span>Suspended</span>
                    </div>
                @else
                    <div class="status-indicator status-closed">
                        <i class="fas fa-stop-circle"></i>
                        <span>Closed</span>
                    </div>
                @endif
            </div>
        </div>
    </div>
    <div class="card-body modern-card-body">
        <div class="info-grid">
            <!-- Basic Information -->
            <div class="info-section">
                <h5 class="section-title">
                    <i class="fas fa-cash-register text-primary"></i>
                    Register Details
                </h5>
                
                <div class="info-item">
                    <div class="info-label">Register ID</div>
                    <div class="info-value">
                        <span class="register-id">#{{ str_pad($cashRegister->id, 4, '0', STR_PAD_LEFT) }}</span>
                        <button class="btn btn-sm btn-outline-secondary copy-btn" 
                                data-copy="{{ $cashRegister->id }}" 
                                title="Copy Register ID">
                            <i class="fas fa-copy"></i>
                        </button>
                    </div>
                </div>
                
                <div class="info-item">
                    <div class="info-label">Operator</div>
                    <div class="info-value">
                        <div class="operator-info">
                            <div class="operator-avatar">
                                <i class="fas fa-user"></i>
                            </div>
                            <div class="operator-details">
                                <div class="operator-name">{{ $cashRegister->user->name }}</div>
                                <div class="operator-role">{{ $cashRegister->user->role ?? 'Cashier' }}</div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="info-item">
                    <div class="info-label">Terminal/Location</div>
                    <div class="info-value">
                        <i class="fas fa-map-marker-alt text-info"></i>
                        {{ $cashRegister->terminal ?? 'Terminal 1' }}
                    </div>
                </div>
            </div>
            
            <!-- Timing Information -->
            <div class="info-section">
                <h5 class="section-title">
                    <i class="fas fa-clock text-success"></i>
                    Session Timing
                </h5>
                
                <div class="info-item">
                    <div class="info-label">Opened At</div>
                    <div class="info-value">
                        <div class="datetime-display">
                            <div class="date">{{ $cashRegister->opened_at->format('d M Y') }}</div>
                            <div class="time">{{ $cashRegister->opened_at->format('h:i A') }}</div>
                        </div>
                    </div>
                </div>
                
                @if($cashRegister->closed_at)
                    <div class="info-item">
                        <div class="info-label">Closed At</div>
                        <div class="info-value">
                            <div class="datetime-display">
                                <div class="date">{{ $cashRegister->closed_at->format('d M Y') }}</div>
                                <div class="time">{{ $cashRegister->closed_at->format('h:i A') }}</div>
                            </div>
                        </div>
                    </div>
                @endif
                
                <div class="info-item">
                    <div class="info-label">Session Duration</div>
                    <div class="info-value">
                        <div class="duration-display">
                            @if($cashRegister->closed_at)
                                <span class="duration-text">
                                    {{ $cashRegister->opened_at->diffForHumans($cashRegister->closed_at, true) }}
                                </span>
                                <span class="duration-status completed">Completed</span>
                            @else
                                <span class="duration-text" id="live-duration">
                                    {{ $cashRegister->opened_at->diffForHumans(null, true) }}
                                </span>
                                <span class="duration-status active">Active</span>
                            @endif
                        </div>
                    </div>
                </div>
                
                @if($cashRegister->status === 'open')
                    <div class="info-item">
                        <div class="info-label">Next Break Suggested</div>
                        <div class="info-value">
                            @php
                                $nextBreak = $cashRegister->opened_at->addHours(4);
                                $isBreakTime = now()->greaterThan($nextBreak);
                            @endphp
                            <div class="break-suggestion {{ $isBreakTime ? 'overdue' : 'upcoming' }}">
                                <i class="fas fa-coffee"></i>
                                @if($isBreakTime)
                                    <span class="break-text">Break overdue</span>
                                @else
                                    <span class="break-text">{{ $nextBreak->diffForHumans() }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif
            </div>
            
            <!-- Financial Summary -->
            <div class="info-section">
                <h5 class="section-title">
                    <i class="fas fa-chart-line text-warning"></i>
                    Financial Summary
                </h5>
                
                <div class="info-item">
                    <div class="info-label">Opening Balance</div>
                    <div class="info-value">
                        <span class="amount opening-balance">৳{{ number_format($cashRegister->opening_balance, 2) }}</span>
                    </div>
                </div>
                
                <div class="info-item">
                    <div class="info-label">Current Balance</div>
                    <div class="info-value">
                        <span class="amount current-balance">৳{{ number_format($currentBalance ?? $cashRegister->opening_balance, 2) }}</span>
                    </div>
                </div>
                
                <div class="info-item">
                    <div class="info-label">Net Change</div>
                    <div class="info-value">
                        @php
                            $netChange = ($currentBalance ?? $cashRegister->opening_balance) - $cashRegister->opening_balance;
                        @endphp
                        <span class="amount net-change {{ $netChange >= 0 ? 'positive' : 'negative' }}">
                            {{ $netChange >= 0 ? '+' : '' }}৳{{ number_format(abs($netChange), 2) }}
                        </span>
                    </div>
                </div>
                
                @if(isset($cashRegister->variance) && $cashRegister->variance !== null)
                    <div class="info-item">
                        <div class="info-label">Variance</div>
                        <div class="info-value">
                            <div class="variance-display">
                                <span class="amount variance {{ $cashRegister->variance >= 0 ? 'positive' : 'negative' }}">
                                    {{ $cashRegister->variance >= 0 ? '+' : '' }}৳{{ number_format(abs($cashRegister->variance), 2) }}
                                </span>
                                @if($cashRegister->variance > 0)
                                    <span class="variance-status surplus">
                                        <i class="fas fa-arrow-up"></i> Surplus
                                    </span>
                                @elseif($cashRegister->variance < 0)
                                    <span class="variance-status shortage">
                                        <i class="fas fa-arrow-down"></i> Shortage
                                    </span>
                                @else
                                    <span class="variance-status balanced">
                                        <i class="fas fa-check"></i> Balanced
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif
            </div>
            
            <!-- Notes and Additional Info -->
            @if(isset($cashRegister->opening_notes) && $cashRegister->opening_notes)
                <div class="info-section">
                    <h5 class="section-title">
                        <i class="fas fa-sticky-note text-info"></i>
                        Opening Notes
                    </h5>
                    
                    <div class="notes-display">
                        <p>{{ $cashRegister->opening_notes }}</p>
                    </div>
                </div>
            @endif
            
            @if(isset($cashRegister->closing_notes) && $cashRegister->closing_notes)
                <div class="info-section">
                    <h5 class="section-title">
                        <i class="fas fa-file-alt text-danger"></i>
                        Closing Notes
                    </h5>
                    
                    <div class="notes-display">
                        <p>{{ $cashRegister->closing_notes }}</p>
                    </div>
                </div>
            @endif
            
            <!-- System Information -->
            <div class="info-section">
                <h5 class="section-title">
                    <i class="fas fa-cog text-muted"></i>
                    System Information
                </h5>
                
                <div class="info-item">
                    <div class="info-label">Created</div>
                    <div class="info-value">
                       <span class="text-muted">
    {{ optional($cashRegister->created_at)->format('d M Y, h:i A') ?? 'N/A' }}
</span>

                    </div>
                </div>
                
                <div class="info-item">
                    <div class="info-label">Last Updated</div>
                    <div class="info-value">
                        <span class="text-muted">{{ optional($cashRegister->updated_at)->diffForHumans() ?? 'N/A' }}</span>
                    </div>
                </div>
                
                @if($cashRegister->status === 'open')
                    <div class="info-item">
                        <div class="info-label">Auto Backup</div>
                        <div class="info-value">
                            <div class="backup-status">
                                <i class="fas fa-shield-alt text-success"></i>
                                <span class="text-success">Protected</span>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<style>
/* Register Information Card Styles */
.status-indicator {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.status-indicator.status-open {
    background: rgba(16, 185, 129, 0.1);
    color: #059669;
}

.status-indicator.status-suspended {
    background: rgba(245, 158, 11, 0.1);
    color: #d97706;
}

.status-indicator.status-closed {
    background: rgba(107, 114, 128, 0.1);
    color: #4b5563;
}

.status-indicator i {
    animation: pulse 2s infinite;
}

.info-grid {
    display: flex;
    flex-direction: column;
    gap: 24px;
}

.info-section {
    padding: 0;
}

.section-title {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 16px;
    padding-bottom: 8px;
    border-bottom: 2px solid #f1f5f9;
    font-size: 14px;
    font-weight: 600;
    color: #374151;
}

.info-item {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 12px;
    padding: 8px 0;
}

.info-item:last-child {
    margin-bottom: 0;
}

.info-label {
    font-size: 12px;
    color: #6b7280;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    min-width: 100px;
    flex-shrink: 0;
}

.info-value {
    flex: 1;
    text-align: right;
    font-size: 14px;
    color: #374151;
    font-weight: 600;
}

.register-id {
    font-family: 'Monaco', 'Consolas', monospace;
    background: #f3f4f6;
    padding: 2px 6px;
    border-radius: 4px;
    margin-right: 8px;
}

.copy-btn {
    padding: 2px 6px;
    font-size: 10px;
    border-radius: 4px;
}

.operator-info {
    display: flex;
    align-items: center;
    gap: 8px;
}

.operator-avatar {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 12px;
}

.operator-details {
    text-align: left;
}

.operator-name {
    font-size: 14px;
    font-weight: 600;
    color: #374151;
    line-height: 1.2;
}

.operator-role {
    font-size: 11px;
    color: #9ca3af;
    font-weight: 500;
}

.datetime-display {
    text-align: right;
}

.datetime-display .date {
    font-size: 13px;
    color: #374151;
    font-weight: 600;
    line-height: 1.2;
}

.datetime-display .time {
    font-size: 11px;
    color: #6b7280;
    font-weight: 500;
}

.duration-display {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 2px;
}

.duration-text {
    font-size: 14px;
    font-weight: 600;
    color: #374151;
}

.duration-status {
    font-size: 10px;
    padding: 2px 6px;
    border-radius: 8px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    font-weight: 600;
}

.duration-status.active {
    background: rgba(16, 185, 129, 0.1);
    color: #059669;
}

.duration-status.completed {
    background: rgba(107, 114, 128, 0.1);
    color: #4b5563;
}

.break-suggestion {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 12px;
    font-weight: 600;
}

.break-suggestion.upcoming {
    color: #6b7280;
}

.break-suggestion.overdue {
    color: #ef4444;
}

.break-suggestion i {
    font-size: 14px;
}

.amount {
    font-family: 'Monaco', 'Consolas', monospace;
    font-size: 15px;
    font-weight: 700;
}

.opening-balance {
    color: #6366f1;
}

.current-balance {
    color: #059669;
}

.net-change.positive {
    color: #059669;
}

.net-change.negative {
    color: #ef4444;
}

.variance-display {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 4px;
}

.variance.positive {
    color: #059669;
}

.variance.negative {
    color: #ef4444;
}

.variance-status {
    display: flex;
    align-items: center;
    gap: 4px;
    font-size: 10px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    padding: 2px 6px;
    border-radius: 8px;
}

.variance-status.surplus {
    background: rgba(16, 185, 129, 0.1);
    color: #059669;
}

.variance-status.shortage {
    background: rgba(239, 68, 68, 0.1);
    color: #ef4444;
}

.variance-status.balanced {
    background: rgba(59, 130, 246, 0.1);
    color: #3b82f6;
}

.notes-display {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 12px;
    margin-top: 8px;
}

.notes-display p {
    margin: 0;
    font-size: 13px;
    line-height: 1.5;
    color: #4b5563;
    font-style: italic;
}

.backup-status {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 12px;
}

/* Mobile optimizations */
@media (max-width: 768px) {
    .info-item {
        flex-direction: column;
        align-items: flex-start;
        gap: 4px;
    }
    
    .info-label {
        min-width: auto;
    }
    
    .info-value {
        text-align: left;
        width: 100%;
    }
    
    .datetime-display,
    .duration-display,
    .variance-display {
        align-items: flex-start;
    }
    
    .operator-info {
        justify-content: flex-start;
    }
    
    .section-title {
        font-size: 13px;
    }
}

/* Animation for live updates */
@keyframes pulse {
    0%, 100% {
        opacity: 1;
    }
    50% {
        opacity: 0.5;
    }
}

.live-update {
    animation: flash 0.5s ease-in-out;
}

@keyframes flash {
    0% {
        background-color: rgba(16, 185, 129, 0.1);
    }
    100% {
        background-color: transparent;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Update live duration for open registers
    @if($cashRegister->status === 'open')
    function updateLiveDuration() {
        const durationElement = document.getElementById('live-duration');
        if (durationElement) {
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
            
            durationElement.textContent = durationText;
            durationElement.classList.add('live-update');
            
            setTimeout(() => {
                durationElement.classList.remove('live-update');
            }, 500);
        }
    }
    
    // Update every minute
    updateLiveDuration();
    setInterval(updateLiveDuration, 60000);
    @endif
    
    // Copy functionality
    document.querySelectorAll('.copy-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const textToCopy = this.dataset.copy;
            
            // Create temporary textarea
            const textarea = document.createElement('textarea');
            textarea.value = textToCopy;
            document.body.appendChild(textarea);
            textarea.select();
            
            try {
                document.execCommand('copy');
                
                // Visual feedback
                const originalHtml = this.innerHTML;
                this.innerHTML = '<i class="fas fa-check"></i>';
                this.classList.add('btn-success');
                this.classList.remove('btn-outline-secondary');
                
                setTimeout(() => {
                    this.innerHTML = originalHtml;
                    this.classList.remove('btn-success');
                    this.classList.add('btn-outline-secondary');
                }, 1000);
                
                // Show toast if available
                if (typeof toastr !== 'undefined') {
                    toastr.success('Copied to clipboard');
                }
            } catch (err) {
                console.error('Copy failed:', err);
                if (typeof toastr !== 'undefined') {
                    toastr.error('Failed to copy');
                }
            }
            
            document.body.removeChild(textarea);
        });
    });
    
    // Break time notification
    @if($cashRegister->status === 'open')
    function checkBreakTime() {
        const openedAt = new Date('{{ $cashRegister->opened_at->toISOString() }}');
        const now = new Date();
        const hoursWorked = (now - openedAt) / (1000 * 60 * 60);
        
        // Suggest break after 4 hours, warn after 6 hours
        if (hoursWorked >= 6 && !sessionStorage.getItem('break-warning-shown')) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Break Overdue!',
                    html: `
                        <div class="text-left">
                            <p>You've been working for over 6 hours continuously.</p>
                            <p><strong>For your wellbeing, please consider taking a break:</strong></p>
                            <ul>
                                <li>Suspend the register temporarily</li>
                                <li>Take a 15-30 minute break</li>
                                <li>Resume when you're ready</li>
                            </ul>
                        </div>
                    `,
                    icon: 'warning',
                    confirmButtonText: 'I\'ll take a break',
                    confirmButtonColor: '#f59e0b'
                });
                
                sessionStorage.setItem('break-warning-shown', 'true');
            }
        } else if (hoursWorked >= 4 && !sessionStorage.getItem('break-suggestion-shown')) {
            if (typeof toastr !== 'undefined') {
                toastr.info('Consider taking a break soon. You\'ve been working for 4+ hours.', 'Break Suggestion', {
                    timeOut: 10000
                });
                
                sessionStorage.setItem('break-suggestion-shown', 'true');
            }
        }
    }
    
    // Check break time every 30 minutes
    checkBreakTime();
    setInterval(checkBreakTime, 30 * 60 * 1000);
    @endif
});
</script>