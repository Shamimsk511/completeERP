<div class="modal fade" id="pinVerificationModal" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content modern-modal pin-modal">
            <div class="modal-header modern-modal-header pin-header">
                <h5 class="modal-title">
                    <i class="fas fa-lock"></i>
                    <span id="pin-modal-title">Enter Security PIN</span>
                </h5>
            </div>
            <div class="modal-body pin-modal-body">
                <div class="pin-verification-content">
                    <div class="register-info">
                        <div class="register-icon">
                            <i class="fas fa-cash-register"></i>
                        </div>
                        <div class="register-details">
                            <h6 id="pin-register-name">Cash Register #123</h6>
                            <p id="pin-register-operator">Operator: John Doe</p>
                            <p id="pin-action-type">Action: Access Register</p>
                        </div>
                    </div>
                    
                    <div class="pin-input-section">
                        <label class="pin-label">Enter 4-digit PIN:</label>
                        <div class="pin-dots-container">
                            <input type="password" id="pin-digit-1" class="pin-dot" maxlength="1" />
                            <input type="password" id="pin-digit-2" class="pin-dot" maxlength="1" />
                            <input type="password" id="pin-digit-3" class="pin-dot" maxlength="1" />
                            <input type="password" id="pin-digit-4" class="pin-dot" maxlength="1" />
                        </div>
                        <div class="pin-error-message" id="pin-error-message">
                            <!-- Error message will appear here -->
                        </div>
                    </div>
                    
                    <div class="pin-keypad">
                        <div class="keypad-row">
                            <button class="keypad-btn" data-digit="1">1</button>
                            <button class="keypad-btn" data-digit="2">2</button>
                            <button class="keypad-btn" data-digit="3">3</button>
                        </div>
                        <div class="keypad-row">
                            <button class="keypad-btn" data-digit="4">4</button>
                            <button class="keypad-btn" data-digit="5">5</button>
                            <button class="keypad-btn" data-digit="6">6</button>
                        </div>
                        <div class="keypad-row">
                            <button class="keypad-btn" data-digit="7">7</button>
                            <button class="keypad-btn" data-digit="8">8</button>
                            <button class="keypad-btn" data-digit="9">9</button>
                        </div>
                        <div class="keypad-row">
                            <button class="keypad-btn keypad-clear" data-action="clear">
                                <i class="fas fa-backspace"></i>
                            </button>
                            <button class="keypad-btn" data-digit="0">0</button>
                            <button class="keypad-btn keypad-submit" data-action="submit">
                                <i class="fas fa-check"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer pin-modal-footer">
                <button type="button" class="btn modern-btn modern-btn-outline" data-dismiss="modal">
                    <i class="fas fa-times"></i> Cancel
                </button>
                <button type="button" class="btn modern-btn modern-btn-primary" id="verify-pin-btn" disabled>
                    <i class="fas fa-unlock"></i> Verify PIN
                </button>
            </div>
        </div>
    </div>
</div>

<style>
/* PIN Modal Styles */
.pin-modal {
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
    border: none;
}

.pin-header {
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
    color: white;
    text-align: center;
    padding: 24px;
    border-bottom: none;
}

.pin-modal-body {
    padding: 32px 24px;
    background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
}

.pin-verification-content {
    text-align: center;
}

.register-info {
    background: white;
    border-radius: 16px;
    padding: 20px;
    margin-bottom: 24px;
    display: flex;
    align-items: center;
    gap: 16px;
    border: 2px solid #e5e7eb;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
}

.register-icon {
    width: 60px;
    height: 60px;
    background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 24px;
    flex-shrink: 0;
}

.register-details {
    text-align: left;
    flex: 1;
}

.register-details h6 {
    margin: 0 0 8px 0;
    color: #1f2937;
    font-weight: 700;
    font-size: 16px;
}

.register-details p {
    margin: 0 0 4px 0;
    color: #6b7280;
    font-size: 14px;
}

.pin-input-section {
    margin-bottom: 24px;
}

.pin-label {
    display: block;
    font-size: 16px;
    font-weight: 600;
    color: #374151;
    margin-bottom: 16px;
}

.pin-dots-container {
    display: flex;
    justify-content: center;
    gap: 12px;
    margin-bottom: 16px;
}

.pin-dot {
    width: 50px;
    height: 50px;
    border: 3px solid #d1d5db;
    border-radius: 12px;
    text-align: center;
    font-size: 24px;
    font-weight: 700;
    background: white;
    transition: all 0.3s ease;
    outline: none;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}

.pin-dot:focus {
    border-color: #6366f1;
    box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.2), 0 2px 8px rgba(0, 0, 0, 0.1);
    transform: scale(1.05);
}

.pin-dot.filled {
    border-color: #10b981;
    background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%);
    color: #059669;
}

.pin-error-message {
    color: #dc2626;
    font-size: 14px;
    font-weight: 600;
    min-height: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    padding: 8px;
    border-radius: 8px;
    background: rgba(239, 68, 68, 0.1);
    border: 1px solid rgba(239, 68, 68, 0.2);
    margin-top: 8px;
    opacity: 0;
    transform: translateY(-10px);
    transition: all 0.3s ease;
}

.pin-error-message.show {
    opacity: 1;
    transform: translateY(0);
}

.pin-keypad {
    background: white;
    border-radius: 16px;
    padding: 20px;
    border: 2px solid #e5e7eb;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
}

.keypad-row {
    display: flex;
    justify-content: center;
    gap: 12px;
    margin-bottom: 12px;
}

.keypad-row:last-child {
    margin-bottom: 0;
}

.keypad-btn {
    width: 60px;
    height: 60px;
    border: 2px solid #e5e7eb;
    border-radius: 12px;
    background: white;
    font-size: 20px;
    font-weight: 700;
    color: #374151;
    transition: all 0.2s ease;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}

.keypad-btn:hover {
    border-color: #6366f1;
    background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(99, 102, 241, 0.2);
}

.keypad-btn:active {
    transform: translateY(0);
}

.keypad-clear {
    background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
    color: white;
    border-color: #ef4444;
}

.keypad-clear:hover {
    background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
    border-color: #dc2626;
}

.keypad-submit {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    color: white;
    border-color: #10b981;
}

.keypad-submit:hover {
    background: linear-gradient(135deg, #059669 0%, #047857 100%);
    border-color: #059669;
}

.pin-modal-footer {
    background: white;
    border-top: 2px solid #e5e7eb;
    padding: 20px 24px;
    border-radius: 0 0 20px 20px;
}

/* Loading animation */
.pin-loading {
    display: inline-block;
    width: 20px;
    height: 20px;
    border: 2px solid #ffffff;
    border-radius: 50%;
    border-top-color: transparent;
    animation: spin 1s ease-in-out infinite;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

/* Shake animation for errors */
.shake {
    animation: shake 0.5s ease-in-out;
}

@keyframes shake {
    0%, 100% { transform: translateX(0); }
    25% { transform: translateX(-5px); }
    75% { transform: translateX(5px); }
}

/* Success animation */
.success-check {
    animation: successPulse 0.6s ease;
}

@keyframes successPulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.1); }
    100% { transform: scale(1); }
}

/* Mobile responsiveness */
@media (max-width: 576px) {
    .pin-modal-body {
        padding: 24px 16px;
    }
    
    .register-info {
        flex-direction: column;
        text-align: center;
    }
    
    .register-details {
        text-align: center;
    }
    
    .pin-dots-container {
        gap: 8px;
    }
    
    .pin-dot {
        width: 45px;
        height: 45px;
        font-size: 20px;
    }
    
    .keypad-btn {
        width: 50px;
        height: 50px;
        font-size: 18px;
    }
    
    .keypad-row {
        gap: 8px;
    }
}
</style>

<script>
// PIN Verification System
class PinVerification {
    constructor() {
        this.currentPinPosition = 0;
        this.enteredPin = '';
        this.pinVerificationCallback = null;
        this.maxAttempts = 3;
        this.currentAttempts = 0;
        this.registerId = null;
        
        this.init();
    }
    
    init() {
        this.bindEvents();
        this.resetModal();
    }
    
    bindEvents() {
        // Handle keypad button clicks
        document.querySelectorAll('.keypad-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                const digit = btn.dataset.digit;
                const action = btn.dataset.action;
                
                if (digit !== undefined) {
                    this.addPinDigit(digit.toString());
                } else if (action === 'clear') {
                    this.clearLastDigit();
                } else if (action === 'submit') {
                    this.verifyEnteredPin();
                }
            });
        });
        
        // Handle keyboard input on PIN dots
        document.querySelectorAll('.pin-dot').forEach((dot, index) => {
            dot.addEventListener('input', (e) => {
                const value = e.target.value;
                if (value && /^\d$/.test(value)) {
                    this.updatePinDisplay(index, value);
                    this.focusNextDot(index);
                }
            });
            
            dot.addEventListener('keydown', (e) => {
                if (e.key === 'Backspace' && e.target.value === '') {
                    this.focusPreviousDot(index);
                } else if (e.key === 'Enter') {
                    this.verifyEnteredPin();
                } else if (e.key === 'Escape') {
                    this.closeModal();
                }
            });
            
            // Prevent non-numeric input
            dot.addEventListener('keypress', (e) => {
                if (!/^\d$/.test(e.key) && !['Backspace', 'Delete', 'Tab', 'Enter'].includes(e.key)) {
                    e.preventDefault();
                }
            });
        });
        
        // Verify PIN button click
        document.getElementById('verify-pin-btn').addEventListener('click', () => {
            this.verifyEnteredPin();
        });
        
        // Clear PIN when modal is closed
        document.getElementById('pinVerificationModal').addEventListener('hidden.bs.modal', () => {
            this.resetModal();
        });
        
        // Focus first input when modal is shown
        document.getElementById('pinVerificationModal').addEventListener('shown.bs.modal', () => {
            setTimeout(() => {
                document.getElementById('pin-digit-1').focus();
            }, 300);
        });
    }
    
    show(options) {
        const {
            registerId,
            registerNumber,
            operatorName,
            actionType,
            onSuccess,
            onCancel
        } = options;
        
        this.registerId = registerId;
        this.pinVerificationCallback = onSuccess;
        
        // Set modal content
        document.getElementById('pin-modal-title').textContent = 'Enter Security PIN';
        document.getElementById('pin-register-name').textContent = `Cash Register #${registerNumber}`;
        document.getElementById('pin-register-operator').textContent = `Operator: ${operatorName}`;
        document.getElementById('pin-action-type').textContent = `Action: ${actionType}`;
        
        // Reset and show modal
        this.resetModal();
        $('#pinVerificationModal').modal('show');
    }
    
    addPinDigit(digit) {
        if (this.currentPinPosition < 4) {
            const dotElement = document.getElementById(`pin-digit-${this.currentPinPosition + 1}`);
            dotElement.value = digit;
            dotElement.classList.add('filled');
            
            this.enteredPin += digit;
            this.currentPinPosition++;
            
            // Add visual feedback
            dotElement.classList.add('success-check');
            setTimeout(() => dotElement.classList.remove('success-check'), 300);
            
            // Update verify button state
            this.updateVerifyButton();
            
            // Auto-submit when 4 digits entered
            if (this.currentPinPosition === 4) {
                setTimeout(() => {
                    this.verifyEnteredPin();
                }, 500);
            }
        }
    }
    
    clearLastDigit() {
        if (this.currentPinPosition > 0) {
            this.currentPinPosition--;
            const dotElement = document.getElementById(`pin-digit-${this.currentPinPosition + 1}`);
            dotElement.value = '';
            dotElement.classList.remove('filled');
            this.enteredPin = this.enteredPin.slice(0, -1);
            
            // Clear error message
            this.hideError();
            
            // Update verify button state
            this.updateVerifyButton();
        }
    }
    
    focusNextDot(currentIndex) {
        if (currentIndex < 3) {
            document.getElementById(`pin-digit-${currentIndex + 2}`).focus();
        }
    }
    
    focusPreviousDot(currentIndex) {
        if (currentIndex > 0) {
            document.getElementById(`pin-digit-${currentIndex}`).focus();
        }
    }
    
    updatePinDisplay(index, digit) {
        this.enteredPin = this.enteredPin.substring(0, index) + digit + this.enteredPin.substring(index + 1);
        this.currentPinPosition = Math.max(this.currentPinPosition, index + 1);
        
        // Mark as filled
        document.getElementById(`pin-digit-${index + 1}`).classList.add('filled');
        
        // Update verify button state
        this.updateVerifyButton();
    }
    
    updateVerifyButton() {
        const verifyBtn = document.getElementById('verify-pin-btn');
        verifyBtn.disabled = this.enteredPin.length < 4;
    }
    
    async verifyEnteredPin() {
        if (this.enteredPin.length !== 4) {
            this.showError('Please enter a complete 4-digit PIN');
            return;
        }
        
        // Show loading state
        const verifyBtn = document.getElementById('verify-pin-btn');
        const originalText = verifyBtn.innerHTML;
        verifyBtn.innerHTML = '<span class="pin-loading"></span> Verifying...';
        verifyBtn.disabled = true;
        
        // Clear any existing error
        this.hideError();
        
        try {
            const response = await fetch(`/cash-registers/${this.registerId}/verify-pin`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    pin: this.enteredPin
                })
            });
            
            const data = await response.json();
            
            if (data.success) {
                // Success - close modal and execute callback
                this.showSuccess();
                setTimeout(() => {
                    $('#pinVerificationModal').modal('hide');
                    if (this.pinVerificationCallback) {
                        this.pinVerificationCallback(this.enteredPin);
                    }
                }, 1000);
            } else {
                // PIN incorrect
                this.currentAttempts++;
                this.handleIncorrectPin(data.message);
            }
        } catch (error) {
            console.error('PIN verification error:', error);
            this.showError('Verification failed. Please try again.');
        } finally {
            // Restore button state
            verifyBtn.innerHTML = originalText;
            this.updateVerifyButton();
        }
    }
    
    handleIncorrectPin(message) {
        const remainingAttempts = this.maxAttempts - this.currentAttempts;
        
        if (remainingAttempts > 0) {
            this.showError(message || `Incorrect PIN. ${remainingAttempts} attempt(s) remaining.`);
            
            // Shake animation
            document.querySelector('.pin-dots-container').classList.add('shake');
            setTimeout(() => {
                document.querySelector('.pin-dots-container').classList.remove('shake');
            }, 500);
            
            // Clear PIN for retry
            setTimeout(() => {
                this.clearAllDigits();
            }, 1500);
        } else {
            this.showError('Maximum attempts exceeded. Access denied.');
            
            // Disable further attempts
            document.querySelectorAll('.keypad-btn, .pin-dot, #verify-pin-btn').forEach(el => {
                el.disabled = true;
            });
            
            // Auto-close modal after delay
            setTimeout(() => {
                this.closeModal();
                if (typeof toastr !== 'undefined') {
                    toastr.error('Maximum PIN attempts exceeded');
                }
            }, 3000);
        }
    }
    
    showError(message) {
        const errorElement = document.getElementById('pin-error-message');
        errorElement.innerHTML = `<i class="fas fa-exclamation-triangle"></i> ${message}`;
        errorElement.classList.add('show');
    }
    
    hideError() {
        const errorElement = document.getElementById('pin-error-message');
        errorElement.classList.remove('show');
        setTimeout(() => {
            errorElement.innerHTML = '';
        }, 300);
    }
    
    showSuccess() {
        const errorElement = document.getElementById('pin-error-message');
        errorElement.innerHTML = '<i class="fas fa-check-circle" style="color: #10b981;"></i> PIN verified successfully!';
        errorElement.style.background = 'rgba(16, 185, 129, 0.1)';
        errorElement.style.borderColor = 'rgba(16, 185, 129, 0.2)';
        errorElement.style.color = '#059669';
        errorElement.classList.add('show');
        
        // Add success animation to all dots
        document.querySelectorAll('.pin-dot').forEach(dot => {
            dot.style.borderColor = '#10b981';
            dot.style.background = 'linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%)';
            dot.classList.add('success-check');
        });
    }
    
    clearAllDigits() {
        document.querySelectorAll('.pin-dot').forEach((dot, index) => {
            dot.value = '';
            dot.classList.remove('filled');
            dot.style.borderColor = '';
            dot.style.background = '';
        });
        
        this.enteredPin = '';
        this.currentPinPosition = 0;
        this.updateVerifyButton();
        this.hideError();
        
        // Focus first input
        document.getElementById('pin-digit-1').focus();
    }
    
    resetModal() {
        this.clearAllDigits();
        this.currentAttempts = 0;
        this.pinVerificationCallback = null;
        this.registerId = null;
        
        // Re-enable all controls
        document.querySelectorAll('.keypad-btn, .pin-dot, #verify-pin-btn').forEach(el => {
            el.disabled = false;
        });
        
        // Reset error element styles
        const errorElement = document.getElementById('pin-error-message');
        errorElement.style.background = '';
        errorElement.style.borderColor = '';
        errorElement.style.color = '';
    }
    
    closeModal() {
        $('#pinVerificationModal').modal('hide');
    }
}

// Initialize PIN verification system
let pinVerification;
document.addEventListener('DOMContentLoaded', function() {
    pinVerification = new PinVerification();
});

// Global function to show PIN verification modal
window.showPinVerification = function(options) {
    if (pinVerification) {
        pinVerification.show(options);
    } else {
        console.error('PIN verification system not initialized');
    }
};

// Export for use in other scripts
window.PinVerification = PinVerification;
</script>