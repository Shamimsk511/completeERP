@if($pendingTransactions->count() > 0)
<div class="card modern-card">
    <div class="card-header modern-header">
        <div class="header-content">
            <div class="header-title">
                <i class="fas fa-clock header-icon"></i>
                <h3 class="card-title">Pending Transactions</h3>
            </div>
            <div class="header-actions">
                <span class="badge badge-warning">{{ $pendingTransactions->count() }}</span>
            </div>
        </div>
    </div>
    <div class="card-body modern-card-body p-0">
        <div class="pending-list">
            @foreach($pendingTransactions->take(5) as $transaction)
                <div class="pending-item">
                    <div class="pending-icon">
                        <i class="fas fa-link"></i>
                    </div>
                    <div class="pending-content">
                        <div class="pending-title">Transaction #{{ $transaction->id }}</div>
                        <div class="pending-details">
                            ৳{{ number_format($transaction->amount ?? 0, 2) }} • 
                            {{ $transaction->created_at->format('d M, h:i A') }}
                        </div>
                    </div>
                    <div class="pending-actions">
                        <button class="btn btn-sm btn-outline-primary" 
                                onclick="linkTransaction({{ $transaction->id }})">
                            <i class="fas fa-link"></i> Link
                        </button>
                    </div>
                </div>
            @endforeach
            
            @if($pendingTransactions->count() > 5)
                <div class="pending-more">
                    <small class="text-muted">
                        + {{ $pendingTransactions->count() - 5 }} more transactions available
                    </small>
                </div>
            @endif
        </div>
    </div>
</div>
@endif

<style>
/* Add Transaction Modal Styles */
.modern-modal {
    border-radius: 16px;
    border: none;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
}

.modern-modal-header {
    background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
    color: white;
    border-radius: 16px 16px 0 0;
    border-bottom: none;
    padding: 20px 24px;
}

.modern-modal-header .modal-title {
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 8px;
}

.modern-modal-header .close {
    color: white;
    opacity: 0.8;
    text-shadow: none;
    font-size: 24px;
    font-weight: 300;
}

.modern-modal-header .close:hover {
    opacity: 1;
}

.form-label.required::after {
    content: ' *';
    color: #ef4444;
}

.modern-select,
.modern-input {
    border: 2px solid #e5e7eb;
    border-radius: 8px;
    padding: 10px 12px;
    transition: all 0.2s ease;
}

.modern-select:focus,
.modern-input:focus {
    border-color: #6366f1;
    box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
}

.quick-amounts {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}

.quick-amounts .btn {
    border-radius: 20px;
    padding: 4px 12px;
    font-size: 12px;
    font-weight: 600;
}

.transaction-preview {
    margin-top: 16px;
}

.preview-content {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.preview-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.preview-label {
    font-weight: 500;
    color: #6b7280;
}

.preview-value {
    font-weight: 700;
    color: #1f2937;
}

/* Pending Transactions Styles */
.pending-list {
    max-height: 300px;
    overflow-y: auto;
}

.pending-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 16px;
    border-bottom: 1px solid #f1f5f9;
    transition: all 0.2s ease;
}

.pending-item:hover {
    background: #f8fafc;
}

.pending-item:last-child {
    border-bottom: none;
}

.pending-icon {
    width: 32px;
    height: 32px;
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 12px;
    flex-shrink: 0;
}

.pending-content {
    flex: 1;
}

.pending-title {
    font-weight: 600;
    color: #1f2937;
    font-size: 14px;
    margin-bottom: 2px;
}

.pending-details {
    font-size: 12px;
    color: #6b7280;
}

.pending-actions {
    flex-shrink: 0;
}

.pending-more {
    padding: 12px 16px;
    text-align: center;
    background: #f8fafc;
    border-top: 1px solid #e5e7eb;
}

/* Action Buttons Styles */
.action-buttons {
    display: flex;
    gap: 4px;
    flex-wrap: wrap;
}

.modern-btn-sm {
    padding: 6px 10px;
    border-radius: 6px;
    font-size: 12px;
    font-weight: 600;
    border: 2px solid transparent;
    transition: all 0.2s ease;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 32px;
    height: 32px;
}

.modern-btn-sm:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.modern-btn-info {
    background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);
    color: white;
    border-color: #06b6d4;
}

.modern-btn-warning {
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
    color: white;
    border-color: #f59e0b;
}

.modern-btn-secondary {
    background: linear-gradient(135deg, #6b7280 0%, #4b5563 100%);
    color: white;
    border-color: #6b7280;
}

.modern-btn-success {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    color: white;
    border-color: #10b981;
}

.modern-btn-danger {
    background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
    color: white;
    border-color: #ef4444;
}

.modern-btn-outline {
    background: transparent;
    color: #6b7280;
    border-color: #e5e7eb;
}

.modern-btn-outline:hover {
    background: #f3f4f6;
    color: #374151;
}

/* Responsive */
@media (max-width: 768px) {
    .quick-amounts {
        justify-content: center;
    }
    
    .pending-item {
        flex-direction: column;
        align-items: flex-start;
        gap: 8px;
        padding: 12px;
    }
    
    .pending-actions {
        width: 100%;
        text-align: right;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
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
        amountInput.value = amount;
        updatePreview();
        amountInput.focus();
    };

    // Link transaction function
    window.linkTransaction = function(transactionId) {
        document.getElementById('transaction_id').value = transactionId;
        $('#addTransactionModal').modal('show');
    };

    // Form submission
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const submitBtn = document.getElementById('submitTransactionBtn');
            const originalText = submitBtn.innerHTML;
            
            // Show loading state
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Adding...';
            submitBtn.disabled = true;
            
            // Submit via AJAX
            const formData = new FormData(form);
            
            fetch(form.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    $('#addTransactionModal').modal('hide');
                    form.reset();
                    updatePreview();
                    
                    if (typeof toastr !== 'undefined') {
                        toastr.success(data.message);
                    }
                    
                    // Refresh the page to show new transaction
                    setTimeout(() => location.reload(), 1000);
                } else {
                    if (typeof toastr !== 'undefined') {
                        toastr.error(data.message || 'Failed to add transaction');
                    }
                    
                    // Show validation errors
                    if (data.errors) {
                        Object.keys(data.errors).forEach(field => {
                            const input = document.querySelector(`[name="${field}"]`);
                            if (input) {
                                input.classList.add('is-invalid');
                                const feedback = input.parentNode.querySelector('.invalid-feedback');
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
            const feedback = this.parentNode.querySelector('.invalid-feedback');
            if (feedback) {
                feedback.textContent = '';
            }
        });
    });

    // Reset form when modal is hidden
    $('#addTransactionModal').on('hidden.bs.modal', function() {
        form.reset();
        updatePreview();
        document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
        document.querySelectorAll('.invalid-feedback').forEach(el => el.textContent = '');
    });
});

// Global functions for action buttons
function deleteRegister(registerId) {
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: 'Delete Cash Register?',
            html: `
                <div class="text-left">
                    <p><strong>Warning:</strong> This action cannot be undone.</p>
                    <p>Deleting this register will:</p>
                    <ul>
                        <li>Permanently remove the register record</li>
                        <li>Delete all associated transactions</li>
                        <li>Remove all audit trail data</li>
                    </ul>
                    <p class="text-danger"><strong>Only closed registers can be deleted.</strong></p>
                </div>
            `,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#6b7280',
            confirmButtonText: '<i class="fas fa-trash"></i> Yes, Delete Permanently',
            cancelButtonText: 'Cancel',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                // Show loading
                Swal.fire({
                    title: 'Deleting...',
                    text: 'Please wait while we delete the register',
                    allowOutsideClick: false,
                    showConfirmButton: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                // Make AJAX request to delete
                fetch(`/cash-registers/${registerId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            title: 'Deleted!',
                            text: 'Cash register has been deleted successfully.',
                            icon: 'success',
                            timer: 2000,
                            showConfirmButton: false
                        });
                        
                        // Refresh table or redirect
                        if (typeof $('#cash-registers-table').DataTable === 'function') {
                            $('#cash-registers-table').DataTable().ajax.reload();
                        } else {
                            setTimeout(() => location.reload(), 1500);
                        }
                        
                        if (typeof toastr !== 'undefined') {
                            toastr.success('Register deleted successfully');
                        }
                    } else {
                        Swal.fire({
                            title: 'Error!',
                            text: data.message || 'Failed to delete the register.',
                            icon: 'error'
                        });
                    }
                })
                .catch(error => {
                    console.error('Delete error:', error);
                    Swal.fire({
                        title: 'Error!',
                        text: 'An error occurred while deleting the register.',
                        icon: 'error'
                    });
                });
            }
        });
    } else {
        if (confirm('Are you sure you want to delete this cash register? This action cannot be undone.')) {
            // Fallback for when SweetAlert2 is not available
            fetch(`/cash-registers/${registerId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.message || 'Failed to delete register');
                }
            })
            .catch(error => {
                console.error('Delete error:', error);
                alert('An error occurred while deleting the register');
            });
        }
    }
}

function suspendRegister(registerId) {
    // This function is defined in the main show.blade.php JavaScript
    if (typeof handleSuspendRegister === 'function') {
        handleSuspendRegister();
    } else {
        // Fallback implementation
        fetch(`/cash-registers/${registerId}/suspend`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(data.message || 'Failed to suspend register');
            }
        })
        .catch(error => {
            console.error('Suspend error:', error);
            alert('An error occurred');
        });
    }
}

function resumeRegister(registerId) {
    // This function is defined in the main show.blade.php JavaScript
    if (typeof handleResumeRegister === 'function') {
        handleResumeRegister();
    } else {
        // Fallback implementation
        fetch(`/cash-registers/${registerId}/resume`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(data.message || 'Failed to resume register');
            }
        })
        .catch(error => {
            console.error('Resume error:', error);
            alert('An error occurred');
        });
    }
}

function generateRegisterReport(registerId) {
    if (typeof toastr !== 'undefined') {
        toastr.info('Generating register-specific report...');
    }
    
    // Open report in new window
    window.open(`/cash-registers/${registerId}/report`, '_blank');
}
</script>