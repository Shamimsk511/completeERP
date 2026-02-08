{{-- 
File: resources/views/cash-registers/partials/status-banner.blade.php
Status banner showing current register state
--}}

<div class="status-banner status-{{ $cashRegister->status }}" data-status="{{ $cashRegister->status }}">
    <div class="status-content">
        <div class="status-icon">
            @if($cashRegister->status === 'open')
                <i class="fas fa-door-open"></i>
            @elseif($cashRegister->status === 'closed')
                <i class="fas fa-door-closed"></i>
            @elseif($cashRegister->status === 'suspended')
                <i class="fas fa-pause"></i>
            @endif
        </div>
        <div class="status-text">
            <h4>{{ ucfirst($cashRegister->status) }} Register</h4>
            <p>
                @if($cashRegister->status === 'open')
                    Register is currently active and processing transactions
                @elseif($cashRegister->status === 'closed')
                    Register was closed on {{ $cashRegister->closed_at->format('d M Y, h:i A') }}
                @elseif($cashRegister->status === 'suspended')
                    Register is temporarily suspended
                @endif
            </p>
        </div>
        @if($cashRegister->status === 'open')
            <div class="status-timer">
                <div class="timer-label">Active for</div>
                <div class="timer-value" id="active-timer">{{ $cashRegister->opened_at->diffForHumans(null, true) }}</div>
            </div>
        @endif
    </div>
</div>

<style>
/* Status Banner Styles */
.status-banner {
    background: white;
    border-radius: 16px;
    padding: 24px;
    margin-bottom: 32px;
    border: 3px solid;
    position: relative;
    overflow: hidden;
}

.status-banner::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 6px;
}

.status-open {
    border-color: #10b981;
    background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%);
}

.status-open::before {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
}

.status-closed {
    border-color: #6b7280;
    background: linear-gradient(135deg, #f9fafb 0%, #f3f4f6 100%);
}

.status-closed::before {
    background: linear-gradient(135deg, #6b7280 0%, #4b5563 100%);
}

.status-suspended {
    border-color: #f59e0b;
    background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%);
}

.status-suspended::before {
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
}

.status-content {
    display: flex;
    align-items: center;
    gap: 20px;
}

.status-icon {
    width: 64px;
    height: 64px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 28px;
    color: white;
    flex-shrink: 0;
    position: relative;
}

.status-open .status-icon {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
}

.status-open .status-icon::after {
    content: '';
    position: absolute;
    width: 100%;
    height: 100%;
    border-radius: 50%;
    background: rgba(16, 185, 129, 0.3);
    animation: pulse 2s infinite;
}

.status-closed .status-icon {
    background: linear-gradient(135deg, #6b7280 0%, #4b5563 100%);
}

.status-suspended .status-icon {
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
}

.status-text {
    flex: 1;
}

.status-text h4 {
    margin: 0 0 8px 0;
    color: #1f2937;
    font-weight: 700;
}

.status-text p {
    margin: 0;
    color: #6b7280;
    font-size: 14px;
}

.status-timer {
    text-align: right;
    min-width: 120px;
}

.timer-label {
    font-size: 12px;
    color: #6b7280;
    font-weight: 500;
}

.timer-value {
    font-size: 18px;
    font-weight: 700;
    color: #10b981;
}

@keyframes pulse {
    0% {
        transform: scale(1);
        opacity: 0.7;
    }
    50% {
        transform: scale(1.1);
        opacity: 0.4;
    }
    100% {
        transform: scale(1);
        opacity: 0.7;
    }
}

/* Mobile optimizations */
@media (max-width: 768px) {
    .status-content {
        flex-direction: column;
        text-align: center;
        gap: 16px;
    }

    .status-timer {
        text-align: center;
        min-width: auto;
    }
}
</style>