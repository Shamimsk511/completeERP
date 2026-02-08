<div class="modal fade" id="addTransactionModal" tabindex="-1" role="dialog" aria-labelledby="addTransactionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content modern-modal">
            <div class="modal-header modern-modal-header">
                <h5 class="modal-title" id="addTransactionModalLabel">
                    <i class="fas fa-plus-circle"></i>
                    Add New Transaction
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="addTransactionForm" method="POST">
                @csrf
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                <div class="modal-body">
                    <div class="row">
                        <!-- Transaction Type -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="transaction_type" class="form-label required">
                                    <i class="fas fa-tags text-primary"></i>
                                    Transaction Type
                                </label>
                                <select class="form-control modern-select" id="transaction_type" name="transaction_type" required>
                                    <option value="">Select Type</option>
                                    <option value="sale">
                                        <i class="fas fa-shopping-cart"></i> Sale (+)
                                    </option>
                                    <option value="return">
                                        <i class="fas fa-undo"></i> Return (-)
                                    </option>
                                    <option value="expense">
                                        <i class="fas fa-minus"></i> Expense (-)
                                    </option>
                                    <option value="deposit">
                                        <i class="fas fa-plus"></i> Deposit (+)
                                    </option>
                                    <option value="withdrawal">
                                        <i class="fas fa-arrow-up"></i> Withdrawal (-)
                                    </option>
                                </select>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>

                        <!-- Payment Method -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="payment_method" class="form-label required">
                                    <i class="fas fa-credit-card text-success"></i>
                                    Payment Method
                                </label>
                                <select class="form-control modern-select" id="payment_method" name="payment_method" required>
                                    <option value="">Select Method</option>
                                    <option value="cash">
                                        <i class="fas fa-money-bill-wave"></i> Cash
                                    </option>
                                    <option value="bank">
                                        <i class="fas fa-university"></i> Bank Transfer
                                    </option>
                                    <option value="mobile_bank">
                                        <i class="fas fa-mobile-alt"></i> Mobile Banking
                                    </option>
                                    <option value="cheque">
                                        <i class="fas fa-file-invoice"></i> Cheque
                                    </option>
                                    <option value="card">
                                        <i class="fas fa-credit-card"></i> Card Payment
                                    </option>
                                </select>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Amount -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="amount" class="form-label required">
                                    <i class="fas fa-dollar-sign text-warning"></i>
                                    Amount (৳)
                                </label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">৳</span>
                                    </div>
                                    <input type="number" 
                                           step="0.01" 
                                           class="form-control modern-input" 
                                           id="amount" 
                                           name="amount" 
                                           min="0.01" 
                                           max="999999.99"
                                           placeholder="0.00" 
                                           required>
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Reference Number -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="reference_number" class="form-label">
                                    <i class="fas fa-hashtag text-info"></i>
                                    Reference Number
                                </label>
                                <input type="text" 
                                       class="form-control modern-input" 
                                       id="reference_number" 
                                       name="reference_number" 
                                       placeholder="Receipt #, Invoice #, etc."
                                       maxlength="50">
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Link to Existing Transaction -->
                    @if(isset($pendingTransactions) && $pendingTransactions->count() > 0)
                    <div class="form-group">
                        <label for="transaction_id" class="form-label">
                            <i class="fas fa-link text-secondary"></i>
                            Link to Existing Transaction (Optional)
                        </label>
                        <select class="form-control modern-select" id="transaction_id" name="transaction_id">
                            <option value="">No linking - Create standalone transaction</option>
                            @foreach($pendingTransactions as $transaction)
                                <option value="{{ $transaction->id }}">
                                    {{ $transaction->display_name }}
                                </option>
                            @endforeach
                        </select>
                        <small class="form-text text-muted">
                            Link this cash register transaction to an existing system transaction
                        </small>
                        <div class="invalid-feedback"></div>
                    </div>
                    @endif

                    <!-- Notes -->
                    <div class="form-group">
                        <label for="notes" class="form-label">
                            <i class="fas fa-sticky-note text-muted"></i>
                            Notes
                        </label>
                        <textarea class="form-control modern-input" 
                                  id="notes" 
                                  name="notes" 
                                  rows="3" 
                                  placeholder="Add any additional details about this transaction..."
                                  maxlength="500"></textarea>
                        <small class="form-text text-muted">
                            <span id="notes-count">0</span>/500 characters
                        </small>
                        <div class="invalid-feedback"></div>
                    </div>

                    <!-- Quick Amount Buttons -->
                    <div class="form-group">
                        <label class="form-label">Quick Amounts</label>
                        <div class="quick-amounts">
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="setQuickAmount(10)">৳10</button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="setQuickAmount(20)">৳20</button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="setQuickAmount(50)">৳50</button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="setQuickAmount(100)">৳100</button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="setQuickAmount(500)">৳500</button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="setQuickAmount(1000)">৳1000</button>
                        </div>
                    </div>

                    <!-- Transaction Impact Preview -->
                    <div class="transaction-preview" id="transactionPreview" style="display: none;">
                        <div class="alert alert-info">
                            <h6><i class="fas fa-calculator"></i> Transaction Impact</h6>
                            <div class="preview-content">
                                <div class="preview-item">
                                    <span class="preview-label">Current Balance:</span>
                                    <span class="preview-value">৳{{ number_format($currentBalance ?? 0, 2) }}</span>
                                </div>
                                <div class="preview-item">
                                    <span class="preview-label">Transaction Amount:</span>
                                    <span class="preview-value" id="previewAmount">৳0.00</span>
                                </div>
                                <div class="preview-item">
                                    <span class="preview-label">New Balance:</span>
                                    <span class="preview-value" id="previewNewBalance">৳{{ number_format($currentBalance ?? 0, 2) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn modern-btn modern-btn-outline" data-dismiss="modal">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                    <button type="submit" class="btn modern-btn modern-btn-primary" id="submitTransactionBtn">
                        <i class="fas fa-plus-circle"></i> Add Transaction
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // CSRF Token setup
    function setupCSRF() {
        // Set up CSRF token for all AJAX requests
        const token = document.querySelector('meta[name="csrf-token"]');
        if (token) {
            window.csrfToken = token.getAttribute('content');
            
            // Set default AJAX headers
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': window.csrfToken
                }
            });
        } else {
            console.warn('CSRF token not found. Adding fallback.');
            // Fallback: try to get token from form
            const formToken = document.querySelector('input[name="_token"]');
            if (formToken) {
                window.csrfToken = formToken.value;
            }
        }
    }

    // Initialize CSRF
    setupCSRF();

    // Add Transaction Form Scripts
    const form = document.getElementById('addTransactionForm');
    const amountInput = document.getElementById('amount');
    const typeInput = document.getElementById('transaction_type');
    const notesInput = document.getElementById('notes');
    const previewDiv = document.getElementById('transactionPreview');
    const currentBalance = {{ $currentBalance ?? 0 }};

    // Update character count for notes
    if (notesInput) {
        notesInput.addEventListener('input', function() {
            const count = this.value.length;
            document.getElementById('notes-count').textContent = count;
            
            if (count > 450) {
                document.getElementById('notes-count').style.color = '#ef4444';
            } else {
                document.getElementById('notes-count').style.color = '#6b7280';
            }
        });
    }

    // Update transaction preview
    function updatePreview() {
        const amount = parseFloat(amountInput.value) || 0;
        const type = typeInput.value;
        
        if (amount > 0 && type) {
            const isPositive = ['sale', 'deposit'].includes(type);
            const change = isPositive ? amount : -amount;
            const newBalance = currentBalance + change;
            
            document.getElementById('previewAmount').textContent = 
                (isPositive ? '+' : '-') + '৳' + amount.toFixed(2);
            document.getElementById('previewNewBalance').textContent = 
                '৳' + newBalance.toFixed(2);
            
            previewDiv.style.display = 'block';
        } else {
            previewDiv.style.display = 'none';
        }
    }

    // Add event listeners
    if (amountInput) amountInput.addEventListener('input', updatePreview);
    if (typeInput) typeInput.addEventListener('change', updatePreview);

    // Quick amount buttons
    window.setQuickAmount = function(amount) {
        if (amountInput) {
            amountInput.value = amount;
            updatePreview();
            amountInput.focus();
        }
    };

    // Form submission with proper CSRF handling
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const submitBtn = document.getElementById('submitTransactionBtn');
            const originalText = submitBtn.innerHTML;
            
            // Show loading state
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Adding...';
            submitBtn.disabled = true;
            
            // Prepare form data with CSRF token
            const formData = new FormData(form);
            
            // Ensure CSRF token is included
            if (!formData.has('_token') && window.csrfToken) {
                formData.append('_token', window.csrfToken);
            }
            
            // Get the action URL from cash register data
            const registerData = window.cashRegisterData || {};
            const actionUrl = registerData.addTransactionUrl || 
                            `/cash-registers/${registerData.id || '{{ $cashRegister->id ?? "" }}'}/add-transaction`;
            
            // Submit via fetch with proper headers
            fetch(actionUrl, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': window.csrfToken || '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    $('#addTransactionModal').modal('hide');
                    form.reset();
                    updatePreview();
                    
                    if (typeof toastr !== 'undefined') {
                        toastr.success(data.message);
                    } else {
                        alert(data.message);
                    }
                    
                    // Refresh the page to show new transaction
                    setTimeout(() => location.reload(), 1000);
                } else {
                    if (typeof toastr !== 'undefined') {
                        toastr.error(data.message || 'Failed to add transaction');
                    } else {
                        alert(data.message || 'Failed to add transaction');
                    }
                    
                    // Show validation errors
                    if (data.errors) {
                        Object.keys(data.errors).forEach(field => {
                            const input = document.querySelector(`[name="${field}"]`);
                            if (input) {
                                input.classList.add('is-invalid');
                                const feedback = input.closest('.form-group').querySelector('.invalid-feedback');
                                if (feedback) {
                                    feedback.textContent = data.errors[field][0];
                                }
                            }
                        });
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
                
                if (typeof toastr !== 'undefined') {
                    toastr.error('An error occurred while adding the transaction');
                } else {
                    alert('An error occurred while adding the transaction');
                }
            })
            .finally(() => {
                // Restore button state
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            });
        });
    }

    // Clear validation errors on input
    document.querySelectorAll('.form-control').forEach(input => {
        input.addEventListener('input', function() {
            this.classList.remove('is-invalid');
            const feedback = this.closest('.form-group').querySelector('.invalid-feedback');
            if (feedback) {
                feedback.textContent = '';
            }
        });
    });

    // Reset form when modal is hidden
    $('#addTransactionModal').on('hidden.bs.modal', function() {
        if (form) {
            form.reset();
            updatePreview();
            document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
            document.querySelectorAll('.invalid-feedback').forEach(el => el.textContent = '');
        }
    });

    // Set up cash register data for JavaScript access
    @if(isset($cashRegister))
    window.cashRegisterData = {
        id: {{ $cashRegister->id }},
        status: '{{ $cashRegister->status }}',
        currentBalance: {{ $currentBalance ?? 0 }},
        addTransactionUrl: '{{ route("cash-registers.add-transaction", $cashRegister->id) }}',
        closeUrl: '{{ route("cash-registers.close", $cashRegister->id) }}'
    };
    @endif
});
</script>

{{-- Fixed Close Register Button - Update in show.blade.php --}}
@if($cashRegister->status === 'open')
    <a href="{{ route('cash-registers.close', $cashRegister->id) }}" 
       class="btn modern-btn modern-btn-warning close-register-btn"
       style="pointer-events: auto !important; opacity: 1 !important;">
        <i class="fas fa-lock"></i> <span class="btn-text">Close Register</span>
    </a>
@endif
