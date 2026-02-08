@extends('layouts.modern-admin')

@section('title', 'Close Cash Register')

@section('page_title', 'Close Cash Register #' . $cashRegister->id)

@section('header_actions')
    <div class="header-actions-group">
        <a href="{{ route('cash-registers.show', $cashRegister->id) }}" class="btn modern-btn modern-btn-outline">
            <i class="fas fa-arrow-left"></i> <span class="btn-text">Back to Register</span>
        </a>
    </div>
@stop

@section('page_content')
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible modern-alert">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
            <i class="fas fa-exclamation-circle"></i>
            {{ session('error') }}
        </div>
    @endif

    <!-- Warning Banner -->
    <div class="warning-banner">
        <div class="warning-content">
            <div class="warning-icon">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <div class="warning-text">
                <h4>Important: Register Closing Process</h4>
                <p>Once you close this register, you cannot reopen it. Make sure all transactions are complete and your cash count is accurate.</p>
            </div>
        </div>
    </div>

    <form action="{{ route('cash-registers.process-close', $cashRegister->id) }}" method="POST" id="close-form">
        @csrf
        
        <div class="row">
            <!-- Main Closing Form -->
            <div class="col-lg-8">
                <!-- Current Status Overview -->
                <div class="card modern-card">
                    <div class="card-header modern-header">
                        <div class="header-content">
                            <div class="header-title">
                                <i class="fas fa-info-circle header-icon"></i>
                                <h3 class="card-title">Current Register Status</h3>
                            </div>
                        </div>
                    </div>
                    <div class="card-body modern-card-body">
                        <div class="status-grid">
                            <div class="status-item">
                                <div class="status-label">Opening Balance</div>
                                <div class="status-value primary">৳{{ number_format($cashRegister->opening_balance, 2) }}</div>
                                <div class="status-time">{{ $cashRegister->opened_at->format('d M Y, h:i A') }}</div>
                            </div>
                            
                            <div class="status-item">
                                <div class="status-label">Expected Closing Balance</div>
                                <div class="status-value success">৳{{ number_format($cashRegister->expected_closing_balance, 2) }}</div>
                                <div class="status-calculation">
                                    Based on {{ $cashRegister->transactions->count() }} transactions
                                </div>
                            </div>
                            
                            <div class="status-item">
                                <div class="status-label">Session Duration</div>
                                <div class="status-value info">{{ $cashRegister->opened_at->diffForHumans(null, true) }}</div>
                                <div class="status-time">Active since opening</div>
                            </div>
                            
                            <div class="status-item">
                                <div class="status-label">Operator</div>
                                <div class="status-value secondary">{{ $cashRegister->user->name }}</div>
                                <div class="status-time">{{ $cashRegister->user->email }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Cash Count Section -->
                <div class="card modern-card">
                    <div class="card-header modern-header">
                        <div class="header-content">
                            <div class="header-title">
                                <i class="fas fa-calculator header-icon"></i>
                                <h3 class="card-title">Physical Cash Count</h3>
                            </div>
                        </div>
                    </div>
                    <div class="card-body modern-card-body">
                        <!-- Actual Closing Balance -->
                        <div class="balance-section">
                            <div class="form-group">
                                <label for="actual_closing_balance" class="form-label">
                                    <i class="fas fa-money-bill-wave text-success"></i>
                                    Actual Closing Balance (Taka) <span class="text-danger">*</span>
                                </label>
                                <div class="balance-input-group">
                                    <div class="currency-symbol">৳</div>
                                    <input type="number" 
                                           step="0.01" 
                                           class="form-control modern-input balance-input @error('actual_closing_balance') is-invalid @enderror" 
                                           id="actual_closing_balance" 
                                           name="actual_closing_balance" 
                                           value="{{ old('actual_closing_balance') }}" 
                                           required>
                                    <div class="balance-variance" id="variance-display">
                                        <span class="variance-label">Variance:</span>
                                        <span class="variance-amount">৳0.00</span>
                                    </div>
                                </div>
                                @error('actual_closing_balance')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                                <div class="form-hint">
                                    <i class="fas fa-info-circle"></i>
                                    Count all physical cash in your drawer and enter the exact amount
                                </div>
                            </div>

                            <!-- Expected vs Actual Comparison -->
                            <div class="comparison-card" id="comparison-card" style="display: none;">
                                <div class="comparison-header">
                                    <h6><i class="fas fa-balance-scale"></i> Balance Comparison</h6>
                                </div>
                                <div class="comparison-body">
                                    <div class="comparison-item">
                                        <div class="comparison-label">Expected Amount</div>
                                        <div class="comparison-value expected">৳{{ number_format($cashRegister->expected_closing_balance, 2) }}</div>
                                    </div>
                                    <div class="comparison-divider">
                                        <i class="fas fa-arrows-alt-h"></i>
                                    </div>
                                    <div class="comparison-item">
                                        <div class="comparison-label">Actual Amount</div>
                                        <div class="comparison-value actual" id="actual-amount">৳0.00</div>
                                    </div>
                                </div>
                                <div class="comparison-result" id="comparison-result">
                                    <!-- Variance result will be shown here -->
                                </div>
                            </div>
                        </div>

                        <!-- Denomination Helper -->
                        <div class="denomination-helper">
                            <button type="button" class="btn btn-link p-0 mb-3" data-toggle="collapse" data-target="#denomination-calculator">
                                <i class="fas fa-calculator text-primary"></i>
                                Use Denomination Calculator (Optional)
                                <i class="fas fa-chevron-down ml-2"></i>
                            </button>
                            
                            <div class="collapse" id="denomination-calculator">
                                <div class="denomination-calculator">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <h6><i class="fas fa-money-bill text-success"></i> Notes</h6>
                                            <div class="denomination-grid">
                                                <div class="denomination-item">
                                                    <label>৳১০০০</label>
                                                    <input type="number" class="form-control denomination-count" data-value="1000" min="0">
                                                    <span class="denomination-total">৳০</span>
                                                </div>
                                                <div class="denomination-item">
                                                    <label>৳৫০০</label>
                                                    <input type="number" class="form-control denomination-count" data-value="500" min="0">
                                                    <span class="denomination-total">৳০</span>
                                                </div>
                                                <div class="denomination-item">
                                                    <label>৳১০০</label>
                                                    <input type="number" class="form-control denomination-count" data-value="100" min="0">
                                                    <span class="denomination-total">৳০</span>
                                                </div>
                                                <div class="denomination-item">
                                                    <label>৳৫০</label>
                                                    <input type="number" class="form-control denomination-count" data-value="50" min="0">
                                                    <span class="denomination-total">৳০</span>
                                                </div>
                                                <div class="denomination-item">
                                                    <label>৳২০</label>
                                                    <input type="number" class="form-control denomination-count" data-value="20" min="0">
                                                    <span class="denomination-total">৳০</span>
                                                </div>
                                                <div class="denomination-item">
                                                    <label>৳১০</label>
                                                    <input type="number" class="form-control denomination-count" data-value="10" min="0">
                                                    <span class="denomination-total">৳০</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <h6><i class="fas fa-coins text-warning"></i> Coins</h6>
                                            <div class="denomination-grid">
                                                <div class="denomination-item">
                                                    <label>৳৫</label>
                                                    <input type="number" class="form-control denomination-count" data-value="5" min="0">
                                                    <span class="denomination-total">৳০</span>
                                                </div>
                                                <div class="denomination-item">
                                                    <label>৳২</label>
                                                    <input type="number" class="form-control denomination-count" data-value="2" min="0">
                                                    <span class="denomination-total">৳০</span>
                                                </div>
                                                <div class="denomination-item">
                                                    <label>৳১</label>
                                                    <input type="number" class="form-control denomination-count" data-value="1" min="0">
                                                    <span class="denomination-total">৳০</span>
                                                </div>
                                                <div class="denomination-item">
                                                    <label>৫০ পয়সা</label>
                                                    <input type="number" class="form-control denomination-count" data-value="0.50" min="0">
                                                    <span class="denomination-total">৳০</span>
                                                </div>
                                                <div class="denomination-item">
                                                    <label>২৫ পয়সা</label>
                                                    <input type="number" class="form-control denomination-count" data-value="0.25" min="0">
                                                    <span class="denomination-total">৳০</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="denomination-summary">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <h5 class="mb-0">Total Calculated: <span id="calculated-total" class="text-primary">৳০.০০</span></h5>
                                            <button type="button" class="btn modern-btn modern-btn-success btn-sm" onclick="applyCalculatedAmount()">
                                                <i class="fas fa-check"></i> Apply Amount
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Closing Notes -->
                        <div class="form-group">
                            <label for="closing_notes" class="form-label">
                                <i class="fas fa-sticky-note text-info"></i>
                                Closing Notes <span class="variance-required" style="display: none;">*</span>
                            </label>
                            <textarea class="form-control modern-input @error('closing_notes') is-invalid @enderror" 
                                      id="closing_notes" 
                                      name="closing_notes" 
                                      rows="4" 
                                      placeholder="Please explain any discrepancies between expected and actual balances, or note any issues during the shift...">{{ old('closing_notes') }}</textarea>
                            @error('closing_notes')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                            <div class="form-hint">
                                <i class="fas fa-lightbulb"></i>
                                Document any discrepancies, unusual transactions, or important handover information
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Summary and Instructions -->
            <div class="col-lg-4">
                <!-- Transaction Summary -->
                <div class="card modern-card">
                    <div class="card-header modern-header">
                        <div class="header-content">
                            <div class="header-title">
                                <i class="fas fa-chart-pie header-icon"></i>
                                <h3 class="card-title">Session Summary</h3>
                            </div>
                        </div>
                    </div>
                    <div class="card-body modern-card-body p-0">
                        <div class="summary-list">
                            <div class="summary-item">
                                <div class="summary-icon success">
                                    <i class="fas fa-shopping-cart"></i>
                                </div>
                                <div class="summary-content">
                                    <div class="summary-label">Sales</div>
                                    <div class="summary-value">৳{{ number_format($cashRegister->transactions->where('transaction_type', 'sale')->sum('amount'), 2) }}</div>
                                </div>
                                <div class="summary-count">{{ $cashRegister->transactions->where('transaction_type', 'sale')->count() }}</div>
                            </div>
                            
                            <div class="summary-item">
                                <div class="summary-icon danger">
                                    <i class="fas fa-undo"></i>
                                </div>
                                <div class="summary-content">
                                    <div class="summary-label">Returns</div>
                                    <div class="summary-value">৳{{ number_format($cashRegister->transactions->where('transaction_type', 'return')->sum('amount'), 2) }}</div>
                                </div>
                                <div class="summary-count">{{ $cashRegister->transactions->where('transaction_type', 'return')->count() }}</div>
                            </div>
                            
                            <div class="summary-item">
                                <div class="summary-icon warning">
                                    <i class="fas fa-minus"></i>
                                </div>
                                <div class="summary-content">
                                    <div class="summary-label">Expenses</div>
                                    <div class="summary-value">৳{{ number_format($cashRegister->transactions->where('transaction_type', 'expense')->sum('amount'), 2) }}</div>
                                </div>
                                <div class="summary-count">{{ $cashRegister->transactions->where('transaction_type', 'expense')->count() }}</div>
                            </div>
                            
                            <div class="summary-item">
                                <div class="summary-icon info">
                                    <i class="fas fa-plus"></i>
                                </div>
                                <div class="summary-content">
                                    <div class="summary-label">Deposits</div>
                                    <div class="summary-value">৳{{ number_format($cashRegister->transactions->where('transaction_type', 'deposit')->sum('amount'), 2) }}</div>
                                </div>
                                <div class="summary-count">{{ $cashRegister->transactions->where('transaction_type', 'deposit')->count() }}</div>
                            </div>
                            
                            <div class="summary-item">
                                <div class="summary-icon secondary">
                                    <i class="fas fa-arrow-up"></i>
                                </div>
                                <div class="summary-content">
                                    <div class="summary-label">Withdrawals</div>
                                    <div class="summary-value">৳{{ number_format($cashRegister->transactions->where('transaction_type', 'withdrawal')->sum('amount'), 2) }}</div>
                                </div>
                                <div class="summary-count">{{ $cashRegister->transactions->where('transaction_type', 'withdrawal')->count() }}</div>
                            </div>
                        </div>
                        
                        <div class="summary-total">
                            <div class="total-label">Net Change</div>
                            <div class="total-value">
                                @php
                                    $netChange = $cashRegister->expected_closing_balance - $cashRegister->opening_balance;
                                @endphp
                                @if($netChange >= 0)
                                    <span class="text-success">+৳{{ number_format($netChange, 2) }}</span>
                                @else
                                    <span class="text-danger">৳{{ number_format($netChange, 2) }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Closing Instructions -->
                <div class="card modern-card">
                    <div class="card-header modern-header">
                        <div class="header-content">
                            <div class="header-title">
                                <i class="fas fa-list-check header-icon"></i>
                                <h3 class="card-title">Closing Checklist</h3>
                            </div>
                        </div>
                    </div>
                    <div class="card-body modern-card-body">
                        <div class="checklist">
                            <div class="checklist-item">
                                <div class="checklist-checkbox">
                                    <input type="checkbox" id="check1" class="checklist-input">
                                    <label for="check1" class="checklist-label"></label>
                                </div>
                                <div class="checklist-content">
                                    <div class="checklist-title">Count Physical Cash</div>
                                    <div class="checklist-desc">Count all notes and coins in your drawer</div>
                                </div>
                            </div>
                            
                            <div class="checklist-item">
                                <div class="checklist-checkbox">
                                    <input type="checkbox" id="check2" class="checklist-input">
                                    <label for="check2" class="checklist-label"></label>
                                </div>
                                <div class="checklist-content">
                                    <div class="checklist-title">Verify Transactions</div>
                                    <div class="checklist-desc">Review all transactions for accuracy</div>
                                </div>
                            </div>
                            
                            <div class="checklist-item">
                                <div class="checklist-checkbox">
                                    <input type="checkbox" id="check3" class="checklist-input">
                                    <label for="check3" class="checklist-label"></label>
                                </div>
                                <div class="checklist-content">
                                    <div class="checklist-title">Check Discrepancies</div>
                                    <div class="checklist-desc">Investigate any variance in amounts</div>
                                </div>
                            </div>
                            
                            <div class="checklist-item">
                                <div class="checklist-checkbox">
                                    <input type="checkbox" id="check4" class="checklist-input">
                                    <label for="check4" class="checklist-label"></label>
                                </div>
                                <div class="checklist-content">
                                    <div class="checklist-title">Document Issues</div>
                                    <div class="checklist-desc">Add notes for any problems or concerns</div>
                                </div>
                            </div>
                            
                            <div class="checklist-item">
                                <div class="checklist-checkbox">
                                    <input type="checkbox" id="check5" class="checklist-input">
                                    <label for="check5" class="checklist-label"></label>
                                </div>
                                <div class="checklist-content">
                                    <div class="checklist-title">Secure Cash Drawer</div>
                                    <div class="checklist-desc">Lock and secure the cash drawer</div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="checklist-progress">
                            <div class="progress-bar">
                                <div class="progress-fill" id="progress-fill"></div>
                            </div>
                            <div class="progress-text">
                                <span id="progress-count">0</span> of 5 completed
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Security Reminders -->
                <div class="card modern-card">
                    <div class="card-header modern-header">
                        <div class="header-content">
                            <div class="header-title">
                                <i class="fas fa-shield-alt header-icon"></i>
                                <h3 class="card-title">Security Reminders</h3>
                            </div>
                        </div>
                    </div>
                    <div class="card-body modern-card-body">
                        <div class="security-tips">
                            <div class="tip-item">
                                <i class="fas fa-lock text-danger"></i>
                                <span>Lock the cash drawer immediately after closing</span>
                            </div>
                            
                            <div class="tip-item">
                                <i class="fas fa-eye text-warning"></i>
                                <span>Double-check your cash count before submitting</span>
                            </div>
                            
                            <div class="tip-item">
                                <i class="fas fa-handshake text-success"></i>
                                <span>Hand over to the next shift operator properly</span>
                            </div>
                            
                            <div class="tip-item">
                                <i class="fas fa-clipboard-check text-info"></i>
                                <span>Keep all receipts and transaction records</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Submit Section -->
        <div class="submit-section">
            <div class="submit-card">
                <div class="submit-content">
                    <div class="submit-info">
                        <h5><i class="fas fa-lock text-warning"></i> Ready to Close Register?</h5>
                        <p class="text-muted">This action cannot be undone. Make sure all information is accurate.</p>
                        <div class="submit-warning" id="variance-warning" style="display: none;">
                            <i class="fas fa-exclamation-triangle text-warning"></i>
                            <span>There's a variance in your cash count. Please provide explanation in notes.</span>
                        </div>
                    </div>
                    <div class="submit-actions">
                        <button type="submit" class="btn modern-btn modern-btn-warning btn-lg" id="submit-btn" disabled>
                            <i class="fas fa-lock"></i> Close Cash Register
                        </button>
                        <a href="{{ route('cash-registers.show', $cashRegister->id) }}" class="btn modern-btn modern-btn-outline btn-lg">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </form>
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
@stop

@section('additional_css')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    
    <style>
        /* Warning Banner */
        .warning-banner {
            background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%);
            border: 3px solid #f59e0b;
            border-radius: 16px;
            padding: 24px;
            margin-bottom: 32px;
        }

        .warning-content {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .warning-icon {
            width: 64px;
            height: 64px;
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 28px;
            flex-shrink: 0;
        }

        .warning-text h4 {
            margin: 0 0 8px 0;
            color: #92400e;
            font-weight: 700;
        }

        .warning-text p {
            margin: 0;
            color: #a16207;
            font-size: 14px;
        }

        /* Status Grid */
        .status-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 24px;
        }

        .status-item {
            background: #8bb8d0;
            border-radius: 12px;
            padding: 20px;
            border: 2px solid #e5e7eb;
            text-align: center;
        }

        .status-label {
            font-size: 12px;
            color: #6b7280;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
        }

        .status-value {
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 6px;
        }

        .status-value.primary { color: #6366f1; }
        .status-value.success { color: #10b981; }
        .status-value.info { color: #06b6d4; }
        .status-value.secondary { color: #1f2937; }

        .status-time,
        .status-calculation {
            font-size: 11px;
            color: #9ca3af;
        }

        /* Balance Input */
        .balance-section {
            background: #f8fafc;
            border-radius: 16px;
            padding: 24px;
            margin-bottom: 24px;
            border: 2px solid #e5e7eb;
        }

        .balance-input-group {
            position: relative;
            margin-bottom: 16px;
        }

        .currency-symbol {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #6b7280;
            font-weight: 600;
            font-size: 18px;
            z-index: 10;
        }

        .balance-input {
            padding-left: 45px !important;
            padding-right: 120px !important;
            font-size: 20px !important;
            font-weight: 700 !important;
            height: 64px !important;
            text-align: center;
        }

        .balance-variance {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            background: #fff;
            padding: 8px 12px;
            border-radius: 8px;
            border: 1px solid #e5e7eb;
            font-size: 12px;
            display: none;
        }

        .variance-label {
            color: #6b7280;
            font-weight: 500;
        }

        .variance-amount {
            font-weight: 700;
            margin-left: 4px;
        }

        /* Comparison Card */
        .comparison-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            border: 2px solid #e5e7eb;
            margin-top: 20px;
        }

        .comparison-header {
            text-align: center;
            margin-bottom: 20px;
        }

        .comparison-header h6 {
            margin: 0;
            color: #374151;
            font-weight: 600;
        }

        .comparison-body {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
        }

        .comparison-item {
            text-align: center;
            flex: 1;
        }

        .comparison-label {
            font-size: 12px;
            color: #6b7280;
            font-weight: 500;
            margin-bottom: 8px;
        }

        .comparison-value {
            font-size: 18px;
            font-weight: 700;
        }

        .comparison-value.expected {
            color: #6366f1;
        }

        .comparison-value.actual {
            color: #059669;
        }

        .comparison-divider {
            padding: 0 20px;
            color: #9ca3af;
            font-size: 20px;
        }

        .comparison-result {
            background: #f8fafc;
            border-radius: 8px;
            padding: 16px;
            text-align: center;
        }

        .variance-perfect {
            color: #059669;
            font-weight: 600;
        }

        .variance-surplus {
            color: #0891b2;
            font-weight: 600;
        }

        .variance-shortage {
            color: #dc2626;
            font-weight: 600;
        }

        /* Denomination Calculator */
        .denomination-calculator {
            background: white;
            border-radius: 12px;
            padding: 20px;
            border: 2px solid #e5e7eb;
            margin-top: 16px;
        }

        .denomination-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(100px, 1fr));
            gap: 12px;
            margin-bottom: 20px;
        }

        .denomination-item {
            text-align: center;
            padding: 12px;
            background: #f8fafc;
            border-radius: 8px;
            border: 1px solid #e5e7eb;
        }

        .denomination-item label {
            display: block;
            font-weight: 600;
            color: #374151;
            margin-bottom: 8px;
            font-size: 13px;
        }

        .denomination-item input {
            width: 100%;
            text-align: center;
            margin-bottom: 8px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            padding: 6px;
            font-size: 14px;
        }

        .denomination-total {
            display: block;
            font-size: 11px;
            color: #6b7280;
            font-weight: 500;
        }

        .denomination-summary {
            background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
            border-radius: 12px;
            padding: 20px;
            border: 2px solid #bfdbfe;
        }

        /* Summary List */
        .summary-list {
            padding: 16px;
        }

        .summary-item {
            display: flex;
            align-items: center;
            gap: 16px;
            padding: 16px 0;
            border-bottom: 1px solid #f1f5f9;
        }

        .summary-item:last-child {
            border-bottom: none;
        }

        .summary-icon {
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
        }

        .summary-label {
            font-size: 13px;
            color: #6b7280;
            font-weight: 500;
        }

        .summary-value {
            font-size: 16px;
            font-weight: 700;
            color: #1f2937;
        }

        .summary-count {
            background: #f3f4f6;
            color: #6b7280;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
            min-width: 24px;
            text-align: center;
        }

        .summary-total {
            background: linear-gradient(135deg, #f8fafc 0%, #e5e7eb 100%);
            border-radius: 12px;
            padding: 16px;
            margin: 16px;
            text-align: center;
            border-top: 3px solid #6366f1;
        }

        .total-label {
            font-size: 14px;
            color: #6b7280;
            font-weight: 500;
            margin-bottom: 8px;
        }

        .total-value {
            font-size: 20px;
            font-weight: 700;
        }

        /* Checklist */
        .checklist {
            margin-bottom: 24px;
        }

        .checklist-item {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            padding: 16px 0;
            border-bottom: 1px solid #e5e7eb;
        }

        .checklist-item:last-child {
            border-bottom: none;
        }

        .checklist-checkbox {
            position: relative;
            flex-shrink: 0;
        }

        .checklist-input {
            appearance: none;
            width: 20px;
            height: 20px;
            border: 2px solid #d1d5db;
            border-radius: 4px;
            background: white;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .checklist-input:checked {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            border-color: #10b981;
        }

        .checklist-label {
            position: absolute;
            top: 2px;
            left: 2px;
            width: 16px;
            height: 16px;
            cursor: pointer;
        }

        .checklist-input:checked + .checklist-label::after {
            content: '✓';
            color: white;
            font-size: 14px;
            font-weight: bold;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            height: 100%;
        }

        .checklist-content {
            flex: 1;
        }

        .checklist-title {
            font-weight: 600;
            color: #374151;
            margin-bottom: 4px;
        }

        .checklist-desc {
            font-size: 13px;
            color: #6b7280;
        }

        .checklist-progress {
            background: #f8fafc;
            border-radius: 12px;
            padding: 16px;
            border: 2px solid #e5e7eb;
        }

        .progress-bar {
            width: 100%;
            height: 8px;
            background: #e5e7eb;
            border-radius: 4px;
            overflow: hidden;
            margin-bottom: 12px;
        }

        .progress-fill {
            height: 100%;
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            width: 0%;
            transition: width 0.3s ease;
        }

        .progress-text {
            text-align: center;
            font-size: 14px;
            color: #6b7280;
            font-weight: 600;
        }

        /* Security Tips */
        .security-tips {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .tip-item {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            padding: 16px;
            background: #f8fafc;
            border-radius: 12px;
            border: 1px solid #e5e7eb;
        }

        .tip-item i {
            margin-top: 2px;
            flex-shrink: 0;
        }

        .tip-item span {
            font-size: 14px;
            color: #374151;
            line-height: 1.5;
        }

        /* Submit Section */
        .submit-section {
            margin-top: 40px;
            padding: 0 15px;
        }

        .submit-card {
            background: white;
            border-radius: 20px;
            padding: 32px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            border: 3px solid #e5e7eb;
            background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
        }

        .submit-content {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 24px;
        }

        .submit-info h5 {
            margin: 0 0 8px 0;
            color: #1f2937;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .submit-info p {
            margin: 0 0 8px 0;
            font-size: 14px;
        }

        .submit-warning {
            font-size: 13px;
            color: #92400e;
            display: flex;
            align-items: center;
            gap: 8px;
            background: #fef3c7;
            padding: 8px 12px;
            border-radius: 8px;
            border: 1px solid #f59e0b;
        }

        .submit-actions {
            display: flex;
            gap: 16px;
            flex-shrink: 0;
        }

        /* Enhanced button styling */
        .modern-btn-lg {
            padding: 14px 28px;
            font-size: 16px;
            border-radius: 12px;
            font-weight: 600;
        }

        .modern-btn-warning {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: white;
            border-color: #f59e0b;
        }

        .modern-btn-warning:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(245, 158, 11, 0.4);
            color: white;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .warning-content {
                flex-direction: column;
                text-align: center;
            }

            .warning-icon {
                width: 56px;
                height: 56px;
                font-size: 24px;
            }

            .status-grid {
                grid-template-columns: 1fr;
                gap: 16px;
            }

            .status-item {
                padding: 16px;
            }

            .balance-input {
                font-size: 18px !important;
                height: 56px !important;
                padding-right: 15px !important;
            }

            .balance-variance {
                position: static;
                transform: none;
                margin-top: 12px;
                text-align: center;
            }

            .comparison-body {
                flex-direction: column;
                gap: 16px;
            }

            .comparison-divider {
                transform: rotate(90deg);
                padding: 0;
            }

            .denomination-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 8px;
            }

            .submit-content {
                flex-direction: column;
                text-align: center;
            }

            .submit-actions {
                width: 100%;
                flex-direction: column;
            }

            .submit-actions .btn {
                width: 100%;
            }

            .header-actions-group {
                flex-direction: column;
                width: 100%;
                gap: 8px;
            }

            .header-actions-group .btn {
                width: 100%;
                justify-content: center;
            }

            .btn-text {
                display: inline;
            }
        }

        /* Loading states */
        .btn:disabled {
            opacity: 0.7;
            cursor: not-allowed;
        }

        /* Form validation */
        .is-invalid {
            border-color: #ef4444 !important;
        }

        .is-invalid:focus {
            box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1) !important;
        }

        /* Animation */
        .card {
            animation: slideInUp 0.5s ease-out;
        }

        @keyframes slideInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Variance required indicator */
        .variance-required {
            color: #ef4444 !important;
        }

        /* Enhanced collapsible */
        .btn-link {
            color: #6366f1;
            text-decoration: none;
            font-weight: 600;
        }

        .btn-link:hover {
            color: #4f46e5;
            text-decoration: none;
        }

        .collapse {
            transition: all 0.3s ease;
        }
        .pin-modal {
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
    border: none;
}

.pin-header {
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
    color: white;
    text-align: center;
    padding: 24px;
}

.pin-modal-body {
    padding: 32px 24px;
    background: #f8fafc;
}

.pin-verification-content {
    text-align: center;
}

.register-info {
    background: white;
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 24px;
    display: flex;
    align-items: center;
    gap: 16px;
    border: 2px solid #e5e7eb;
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
    transition: all 0.2s ease;
    outline: none;
}

.pin-dot:focus {
    border-color: #6366f1;
    box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.2);
}

.pin-dot.filled {
    border-color: #10b981;
    background: #ecfdf5;
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
}

.pin-keypad {
    background: white;
    border-radius: 16px;
    padding: 20px;
    border: 2px solid #e5e7eb;
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
}

.keypad-btn:hover {
    border-color: #6366f1;
    background: #f8fafc;
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
.close-register-btn,
.btn[href*="close"],
#submit-btn,
.submit-section .btn {
    pointer-events: auto !important;
    opacity: 1 !important;
    cursor: pointer !important;
}
    </style>
@stop

@section('additional_js')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <script>
$(document).ready(function() {
    initializePinModal();
    // Configure toastr
    toastr.options = {
        "closeButton": true,
        "newestOnTop": true,
        "progressBar": true,
        "positionClass": "toast-top-right",
        "timeOut": "3000"
    };
let currentPinPosition = 0;
let enteredPin = '';
let pinVerificationCallback = null;
let maxAttempts = 3;
let currentAttempts = 0;
    const expectedBalance = {{ $cashRegister->expected_closing_balance }};
    let hasVariance = false;

    // Calculate variance on input
    $('#actual_closing_balance').on('input', function() {
        const actualBalance = parseFloat($(this).val()) || 0;
        const variance = actualBalance - expectedBalance;
        
        updateVarianceDisplay(variance, actualBalance);
        checkFormValidity();
    });

    function updateVarianceDisplay(variance, actualBalance) {
        const varianceDisplay = $('#variance-display');
        const varianceAmount = varianceDisplay.find('.variance-amount');
        const comparisonCard = $('#comparison-card');
        const actualAmountDisplay = $('#actual-amount');
        const comparisonResult = $('#comparison-result');
        
        if (actualBalance > 0) {
            varianceDisplay.show();
            comparisonCard.show();
            actualAmountDisplay.text('৳' + actualBalance.toLocaleString('en-BD', {minimumFractionDigits: 2}));
            
            if (Math.abs(variance) < 0.01) {
                // Perfect match
                varianceAmount.text('৳0.00').css('color', '#059669');
                comparisonResult.html('<div class="variance-perfect"><i class="fas fa-check-circle"></i> Perfect Balance Match!</div>');
                hasVariance = false;
            } else if (variance > 0) {
                // Surplus
                varianceAmount.text('+৳' + variance.toFixed(2)).css('color', '#0891b2');
                comparisonResult.html('<div class="variance-surplus"><i class="fas fa-arrow-up"></i> Surplus of ৳' + variance.toFixed(2) + '</div>');
                hasVariance = true;
            } else {
                // Shortage
                varianceAmount.text('৳' + variance.toFixed(2)).css('color', '#dc2626');
                comparisonResult.html('<div class="variance-shortage"><i class="fas fa-arrow-down"></i> Shortage of ৳' + Math.abs(variance).toFixed(2) + '</div>');
                hasVariance = true;
            }
        } else {
            varianceDisplay.hide();
            comparisonCard.hide();
            hasVariance = false;
        }
        
        // Show/hide variance warning and required notes
        const varianceWarning = $('#variance-warning');
        const varianceRequired = $('.variance-required');
        const notesField = $('#closing_notes');
        
        if (hasVariance) {
            varianceWarning.show();
            varianceRequired.show();
            notesField.attr('required', true);
        } else {
            varianceWarning.hide();
            varianceRequired.hide();
            notesField.removeAttr('required');
        }
    }

    // Denomination calculator
    $('.denomination-count').on('input', function() {
        const value = parseFloat($(this).data('value'));
        const count = parseInt($(this).val()) || 0;
        const total = value * count;
        
        $(this).siblings('.denomination-total').text('৳' + total.toFixed(2));
        
        // Calculate grand total
        let grandTotal = 0;
        $('.denomination-count').each(function() {
            const value = parseFloat($(this).data('value'));
            const count = parseInt($(this).val()) || 0;
            grandTotal += value * count;
        });
        
        $('#calculated-total').text('৳' + grandTotal.toFixed(2));
    });

    // Apply calculated amount
    window.applyCalculatedAmount = function() {
        const total = parseFloat($('#calculated-total').text().replace('৳', '').replace(',', ''));
        if (total > 0) {
            $('#actual_closing_balance').val(total.toFixed(2)).trigger('input');
            toastr.success('Calculated amount applied: ৳' + total.toFixed(2));
            $('#denomination-calculator').collapse('hide');
        } else {
            toastr.warning('Please enter some denominations first');
        }
    };

    // Checklist functionality
    $('.checklist-input').on('change', function() {
        updateChecklistProgress();
        checkFormValidity();
    });

    function updateChecklistProgress() {
        const totalItems = $('.checklist-input').length;
        const checkedItems = $('.checklist-input:checked').length;
        const progress = (checkedItems / totalItems) * 100;
        
        $('#progress-fill').css('width', progress + '%');
        $('#progress-count').text(checkedItems);
        
        if (checkedItems === totalItems) {
            toastr.success('All checklist items completed!');
        }
    }

    function checkFormValidity() {
        const actualBalance = parseFloat($('#actual_closing_balance').val()) || 0;
        const checkedItems = $('.checklist-input:checked').length;
        const totalItems = $('.checklist-input').length;
        const notesRequired = hasVariance && $('#closing_notes').val().trim().length < 10;
        
        const isValid = actualBalance > 0 && 
                       checkedItems === totalItems && 
                       (!hasVariance || !notesRequired);
        
        $('#submit-btn').prop('disabled', !isValid);
        
        if (actualBalance > 0 && checkedItems < totalItems) {
            toastr.info('Please complete all checklist items before closing');
        }
        
        if (hasVariance && $('#closing_notes').val().trim().length < 10) {
            toastr.warning('Variance detected - please provide detailed explanation in notes');
        }
    }
$('.close-register-btn, #submit-btn').each(function() {
        $(this).prop('disabled', false)
               .css({
                   'pointer-events': 'auto',
                   'opacity': '1',
                   'cursor': 'pointer'
               })
               .removeAttr('disabled');
    });
    // Notes validation for variance
    $('#closing_notes').on('input', function() {
        checkFormValidity();
    });
    $('.close-register-btn').css({
        'pointer-events': 'auto',
        'opacity': '1',
        'cursor': 'pointer'
    }).prop('disabled', false);

    // Remove any disabled attributes
    $('.close-register-btn').removeAttr('disabled');

    // Ensure the button is clickable
    $('.close-register-btn').on('click', function(e) {
        // Don't prevent default - let the link work normally
        console.log('Close register button clicked');
    });
    // Form submission with confirmation
    $('#close-form').on('submit', function(e) {
        e.preventDefault();
        
        const actualBalance = parseFloat($('#actual_closing_balance').val());
        const variance = actualBalance - expectedBalance;
        const notes = $('#closing_notes').val().trim();
        
        let confirmMessage = `
            <div class="text-left">
                <p><strong>Register Details:</strong></p>
                <ul>
                    <li>Expected Balance: ৳${expectedBalance.toLocaleString('en-BD', {minimumFractionDigits: 2})}</li>
                    <li>Actual Balance: ৳${actualBalance.toLocaleString('en-BD', {minimumFractionDigits: 2})}</li>
                    <li>Variance: ${variance === 0 ? 'None' : (variance > 0 ? '+' : '') + '৳' + variance.toFixed(2)}</li>
                </ul>
                <p class="text-warning"><strong>Warning:</strong> This action cannot be undone.</p>
            </div>
        `;

        Swal.fire({
            title: 'Close Cash Register?',
            html: confirmMessage,
            icon: variance === 0 ? 'question' : 'warning',
            showCancelButton: true,
            confirmButtonColor: variance === 0 ? '#f59e0b' : '#ef4444',
            cancelButtonColor: '#6b7280',
            confirmButtonText: '<i class="fas fa-lock"></i> Yes, Close Register',
            cancelButtonText: 'Cancel',
            reverseButtons: true,
            customClass: {
                popup: 'swal-modern'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                // Show loading state
                const submitBtn = $('#submit-btn');
                const originalText = submitBtn.html();
                
                submitBtn.prop('disabled', true);
                submitBtn.html('<i class="fas fa-spinner fa-spin"></i> Closing Register...');
                
                // Submit the form
                this.submit();
            }
        });
    });

    // Auto-focus actual balance field
    setTimeout(function() {
        $('#actual_closing_balance').focus();
    }, 500);

    // Keyboard shortcuts
    $(document).on('keydown', function(e) {
        // Ctrl+Enter to submit (if form is valid)
        if (e.ctrlKey && e.which === 13 && !$('#submit-btn').prop('disabled')) {
            $('#close-form').trigger('submit');
        }
        
        // Escape to cancel
        if (e.which === 27) {
            window.location.href = "{{ route('cash-registers.show', $cashRegister->id) }}";
        }
    });

    // Format balance input on blur
    $('#actual_closing_balance').on('blur', function() {
        const value = parseFloat($(this).val());
        if (!isNaN(value)) {
            $(this).val(value.toFixed(2));
        }
    });

    // Prevent negative values
    $('#actual_closing_balance').on('input', function() {
        if (parseFloat($(this).val()) < 0) {
            $(this).val(0);
        }
    });

    // Denomination calculator toggle
    $('[data-target="#denomination-calculator"]').on('click', function() {
        const icon = $(this).find('.fa-chevron-down, .fa-chevron-up');
        const isExpanded = $('#denomination-calculator').hasClass('show');
        
        if (isExpanded) {
            icon.removeClass('fa-chevron-up').addClass('fa-chevron-down');
        } else {
            icon.removeClass('fa-chevron-down').addClass('fa-chevron-up');
        }
    });

    // Input validation helpers
    $('.denomination-count').on('input', function() {
        const value = parseInt($(this).val());
        if (value < 0) {
            $(this).val(0);
        }
    });

    // Initialize progress
    updateChecklistProgress();
    
    // Auto-save notes to localStorage for recovery
    $('#closing_notes').on('input', function() {
        localStorage.setItem('cash_register_closing_notes_{{ $cashRegister->id }}', $(this).val());
    });

    // Restore notes from localStorage
    const savedNotes = localStorage.getItem('cash_register_closing_notes_{{ $cashRegister->id }}');
    if (savedNotes && !$('#closing_notes').val()) {
        $('#closing_notes').val(savedNotes);
        toastr.info('Draft notes restored');
    }

    // Clear saved notes on successful submission
    $('#close-form').on('submit', function() {
        localStorage.removeItem('cash_register_closing_notes_{{ $cashRegister->id }}');
    });

    // Show helpful tooltips
    $('[title]').tooltip({
        placement: 'top',
        trigger: 'hover'
    });

    // Real-time validation feedback
    $('#actual_closing_balance').on('input', function() {
        const value = parseFloat($(this).val());
        if (!isNaN(value) && value >= 0) {
            $(this).removeClass('is-invalid');
        }
    });

    // Enhanced checklist interaction - fix for all checkboxes
    $('.checklist-item').on('click', function(e) {
        // Prevent double triggering if clicking directly on checkbox or label
        if (e.target.type !== 'checkbox' && !$(e.target).hasClass('checklist-label')) {
            const checkbox = $(this).find('.checklist-input');
            checkbox.prop('checked', !checkbox.prop('checked')).trigger('change');
        }
    });

    // Ensure individual checkbox clicks work
    $('.checklist-input').on('click', function(e) {
        e.stopPropagation(); // Prevent event bubbling to parent
    });

    $('.checklist-label').on('click', function(e) {
        e.stopPropagation(); // Prevent event bubbling to parent
    });
    $('#close-form').off('submit').on('submit', function(e) {
        e.preventDefault();
        
        const actualBalance = parseFloat($('#actual_closing_balance').val()) || 0;
        
        // Only validate balance, not transaction count
        if (isNaN(actualBalance) || actualBalance < 0) {
            alert('Please enter a valid closing balance');
            return false;
        }
        
        // Submit form directly
        this.submit();
    });
});
// Initialize PIN modal
function initializePinModal() {
    // Handle keypad button clicks
    $('.keypad-btn').on('click', function() {
        const digit = $(this).data('digit');
        const action = $(this).data('action');
        
        if (digit !== undefined) {
            addPinDigit(digit.toString());
        } else if (action === 'clear') {
            clearLastDigit();
        } else if (action === 'submit') {
            verifyEnteredPin();
        }
    });
    
    // Handle keyboard input on PIN dots
    $('.pin-dot').on('input', function() {
        const value = $(this).val();
        const index = $(this).attr('id').split('-')[2] - 1;
        
        if (value && /^\d$/.test(value)) {
            updatePinDisplay(index, value);
            focusNextDot(index);
        }
    });
    
    // Handle keyboard events
    $('.pin-dot').on('keydown', function(e) {
        const index = $(this).attr('id').split('-')[2] - 1;
        
        if (e.key === 'Backspace' && $(this).val() === '') {
            focusPreviousDot(index);
        } else if (e.key === 'Enter') {
            verifyEnteredPin();
        }
    });
    
    // Verify PIN button click
    $('#verify-pin-btn').on('click', function() {
        verifyEnteredPin();
    });
    
    // Clear PIN when modal is closed
    $('#pinVerificationModal').on('hidden.bs.modal', function() {
        resetPinModal();
    });
}

// Show PIN verification modal
function showPinVerification(options) {
    const {
        registerId,
        registerNumber,
        operatorName,
        actionType,
        onSuccess,
        onCancel
    } = options;
    
    // Set modal content
    $('#pin-modal-title').text(`Enter Security PIN`);
    $('#pin-register-name').text(`Cash Register #${registerNumber}`);
    $('#pin-register-operator').text(`Operator: ${operatorName}`);
    $('#pin-action-type').text(`Action: ${actionType}`);
    
    // Set callback
    pinVerificationCallback = onSuccess;
    
    // Reset and show modal
    resetPinModal();
    $('#pinVerificationModal').modal('show');
    
    // Focus first PIN input
    setTimeout(() => {
        $('#pin-digit-1').focus();
    }, 500);
}

// Add digit to PIN
function addPinDigit(digit) {
    if (currentPinPosition < 4) {
        $(`#pin-digit-${currentPinPosition + 1}`).val(digit).addClass('filled');
        enteredPin += digit;
        currentPinPosition++;
        
        // Update verify button state
        $('#verify-pin-btn').prop('disabled', currentPinPosition < 4);
        
        // Auto-submit when 4 digits entered
        if (currentPinPosition === 4) {
            setTimeout(() => {
                verifyEnteredPin();
            }, 300);
        }
    }
}

// Clear last digit
function clearLastDigit() {
    if (currentPinPosition > 0) {
        currentPinPosition--;
        $(`#pin-digit-${currentPinPosition + 1}`).val('').removeClass('filled');
        enteredPin = enteredPin.slice(0, -1);
        
        // Update verify button state
        $('#verify-pin-btn').prop('disabled', currentPinPosition < 4);
        
        // Clear error message
        $('#pin-error-message').text('');
    }
}

// Focus next PIN dot
function focusNextDot(currentIndex) {
    if (currentIndex < 3) {
        $(`#pin-digit-${currentIndex + 2}`).focus();
    }
}

// Focus previous PIN dot
function focusPreviousDot(currentIndex) {
    if (currentIndex > 0) {
        $(`#pin-digit-${currentIndex}`).focus();
    }
}

// Update PIN display
function updatePinDisplay(index, digit) {
    enteredPin = enteredPin.substring(0, index) + digit + enteredPin.substring(index + 1);
    currentPinPosition = Math.max(currentPinPosition, index + 1);
    
    // Update verify button state
    $('#verify-pin-btn').prop('disabled', enteredPin.length < 4);
}

// Verify entered PIN
function verifyEnteredPin() {
    if (enteredPin.length !== 4) {
        showPinError('Please enter a complete 4-digit PIN');
        return;
    }
    
    // Show loading state
    const verifyBtn = $('#verify-pin-btn');
    const originalText = verifyBtn.html();
    verifyBtn.html('<span class="pin-loading"></span> Verifying...').prop('disabled', true);
    
    // Clear any existing error
    $('#pin-error-message').text('');
    
    // Call the verification callback
    if (pinVerificationCallback) {
        pinVerificationCallback(enteredPin)
            .then(function(response) {
                if (response.success) {
                    // Success - close modal
                    $('#pinVerificationModal').modal('hide');
                    toastr.success('PIN verified successfully');
                } else {
                    // PIN incorrect
                    currentAttempts++;
                    handleIncorrectPin();
                }
            })
            .catch(function(error) {
                console.error('PIN verification error:', error);
                showPinError('Verification failed. Please try again.');
            })
            .finally(function() {
                // Restore button state
                verifyBtn.html(originalText).prop('disabled', enteredPin.length < 4);
            });
    }
}

// Handle incorrect PIN
function handleIncorrectPin() {
    const remainingAttempts = maxAttempts - currentAttempts;
    
    if (remainingAttempts > 0) {
        showPinError(`<i class="fas fa-exclamation-triangle"></i> Incorrect PIN. ${remainingAttempts} attempt(s) remaining.`);
        
        // Clear PIN for retry
        setTimeout(() => {
            clearAllDigits();
        }, 1500);
    } else {
        showPinError('<i class="fas fa-ban"></i> Maximum attempts exceeded. Access denied.');
        
        // Disable further attempts
        $('.keypad-btn, .pin-dot, #verify-pin-btn').prop('disabled', true);
        
        // Auto-close modal after delay
        setTimeout(() => {
            $('#pinVerificationModal').modal('hide');
            toastr.error('Maximum PIN attempts exceeded');
        }, 2000);
    }
}

// Show PIN error
function showPinError(message) {
    $('#pin-error-message').html(message);
    
    // Add shake animation to PIN dots
    $('.pin-dots-container').addClass('shake');
    setTimeout(() => {
        $('.pin-dots-container').removeClass('shake');
    }, 500);
}

// Clear all PIN digits
function clearAllDigits() {
    $('.pin-dot').val('').removeClass('filled');
    enteredPin = '';
    currentPinPosition = 0;
    $('#verify-pin-btn').prop('disabled', true);
    $('#pin-error-message').text('');
    $('#pin-digit-1').focus();
}

// Reset PIN modal to initial state
function resetPinModal() {
    clearAllDigits();
    currentAttempts = 0;
    pinVerificationCallback = null;
    
    // Re-enable all controls
    $('.keypad-btn, .pin-dot, #verify-pin-btn').prop('disabled', false);
    $('#verify-pin-btn').prop('disabled', true); // Except verify button until PIN is complete
}
// Custom SweetAlert2 styling
const swalStyle = document.createElement('style');
swalStyle.textContent = `
    .swal-modern {
        border-radius: 16px !important;
        padding: 0 !important;
    }
    
    .swal2-header {
        padding: 24px 24px 0 24px !important;
    }
    
    .swal2-content {
        padding: 16px 24px !important;
    }
    
    .swal2-actions {
        padding: 16px 24px 24px 24px !important;
    }
    
    .swal2-confirm.btn {
        margin: 0 8px 0 0 !important;
    }
    
    .swal2-cancel.btn {
        margin: 0 !important;
    }
`;
// Add shake animation CSS
const shakeCSS = `
.shake {
    animation: shake 0.5s ease-in-out;
}

@keyframes shake {
    0%, 100% { transform: translateX(0); }
    25% { transform: translateX(-5px); }
    75% { transform: translateX(5px); }
}
`;

// Inject shake CSS
if (!document.getElementById('shake-animation-css')) {
    const style = document.createElement('style');
    style.id = 'shake-animation-css';
    style.textContent = shakeCSS;
    document.head.appendChild(style);
}

// Export functions for use in other scripts
window.showPinVerification = showPinVerification;
window.resetPinModal = resetPinModal;
document.head.appendChild(swalStyle);
    </script>
@stop