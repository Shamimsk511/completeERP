<!-- Add Transaction Modal -->
@if($cashRegister->status === 'open')
    <div class="modal fade" id="addTransactionModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg modal-dialog-mobile" role="document">
            <div class="modal-content modern-modal">
                <div class="modal-header modern-modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-plus-circle"></i>
                        Add New Transaction
                    </h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <form action="{{ route('cash-registers.add-transaction', $cashRegister->id) }}" method="POST" id="transaction-form">
                    @csrf
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Transaction Type <span class="text-danger">*</span></label>
                                    <select class="form-control modern-select" name="transaction_type" required>
                                        <option value="">Select Type</option>
                                        <option value="sale"><i class="fas fa-shopping-cart"></i> Sale</option>
                                        <option value="return"><i class="fas fa-undo"></i> Return</option>
                                        <option value="expense"><i class="fas fa-minus"></i> Expense</option>
                                        <option value="deposit"><i class="fas fa-plus"></i> Deposit</option>
                                        <option value="withdrawal"><i class="fas fa-arrow-up"></i> Withdrawal</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Payment Method <span class="text-danger">*</span></label>
                                    <select class="form-control modern-select" name="payment_method" required>
                                        <option value="">Select Method</option>
                                        <option value="cash">Cash</option>
                                        <option value="bank">Bank Transfer</option>
                                        <option value="mobile_bank">Mobile Banking</option>
                                        <option value="cheque">Cheque</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Amount (৳) <span class="text-danger">*</span></label>
                            <div class="amount-input-group">
                                <div class="currency-symbol">৳</div>
                                <input type="number" step="0.01" class="form-control modern-input amount-input" name="amount" required min="0.01">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Notes</label>
                            <textarea class="form-control modern-input" name="notes" rows="3" placeholder="Add any relevant details about this transaction..."></textarea>
                        </div>
                        
                        <!-- Quick Amount Buttons -->
                        <div class="quick-amounts">
                            <div class="form-label">Quick Amounts:</div>
                            <div class="quick-amount-buttons">
                                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="setTransactionAmount(50)">৳50</button>
                                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="setTransactionAmount(100)">৳100</button>
                                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="setTransactionAmount(500)">৳500</button>
                                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="setTransactionAmount(1000)">৳1000</button>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn modern-btn modern-btn-outline" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn modern-btn modern-btn-primary">
                            <i class="fas fa-plus"></i> Add Transaction
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endif
