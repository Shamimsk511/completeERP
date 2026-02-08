{{-- resources/views/debt_collection/edit_tracking.blade.php --}}
@extends('layouts.modern-admin')

@section('title', 'Edit Tracking' . ($customer ? ' - ' . $customer->name : ''))
@section('page_title', 'Edit Collection Tracking')

@section('header_actions')
    @if($customer)
    <div class="btn-group">
        <a class="btn modern-btn modern-btn-primary" href="{{ route('debt-collection.call-history', $customer->id) }}">
            <i class="fas fa-history"></i> View Call History
        </a>
        <button type="button" class="btn modern-btn modern-btn-primary dropdown-toggle dropdown-toggle-split" data-toggle="dropdown">
            <span class="sr-only">Toggle Dropdown</span>
        </button>
        <div class="dropdown-menu">
            <a class="dropdown-item" href="/customers/{{ $customer->id }}">
                <i class="fas fa-user text-info"></i> View Customer Profile
            </a>
            <div class="dropdown-divider"></div>
            <a class="dropdown-item" href="{{ route('debt-collection.index') }}">
                <i class="fas fa-arrow-left text-muted"></i> Back to Dashboard
            </a>
        </div>
    </div>
    @endif
@stop

@section('page_content')
    @if(isset($error))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <h5><i class="fas fa-exclamation-triangle"></i> Error</h5>
            <p>{{ $error }}</p>
            <a href="{{ route('debt-collection.index') }}" class="btn btn-primary">Back to Dashboard</a>
            <button type="button" class="close" data-dismiss="alert">
                <span>&times;</span>
            </button>
        </div>
    @elseif(!$customer)
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <h5><i class="fas fa-exclamation-triangle"></i> Customer Not Found</h5>
            <p>The requested customer could not be found.</p>
            <a href="{{ route('debt-collection.index') }}" class="btn btn-primary">Back to Dashboard</a>
        </div>
    @else
        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <h5><i class="fas fa-exclamation-triangle"></i> Please correct the following errors:</h5>
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="close" data-dismiss="alert">
                    <span>&times;</span>
                </button>
            </div>
        @endif

        <div class="row">
            <!-- Customer Info -->
            <div class="col-lg-4">
                <div class="card modern-card">
                    <div class="card-header modern-header">
                        <h3 class="card-title">
                            <i class="fas fa-user"></i> Customer Details
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-sm-4"><strong>Name:</strong></div>
                            <div class="col-sm-8">{{ $customer->name }}</div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-sm-4"><strong>Phone:</strong></div>
                            <div class="col-sm-8">{{ $customer->phone ?? 'Not provided' }}</div>
                        </div>
                        @if(Schema::hasColumn('customers', 'email'))
                        <div class="row mb-3">
                            <div class="col-sm-4"><strong>Email:</strong></div>
                            <div class="col-sm-8">{{ $customer->email ?? 'Not provided' }}</div>
                        </div>
                        @endif
                        <div class="row mb-3">
                            <div class="col-sm-4"><strong>Outstanding:</strong></div>
                            <div class="col-sm-8">
                                <span class="text-danger font-weight-bold h4">৳{{ number_format($customer->outstanding_balance ?? 0, 2) }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="card modern-card">
                    <div class="card-header modern-header">
                        <h3 class="card-title">
                            <i class="fas fa-bolt"></i> Quick Actions
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="{{ route('debt-collection.call-history', $customer->id) }}" class="btn modern-btn modern-btn-info btn-block">
                                <i class="fas fa-history"></i> View Call History
                            </a>
                            <a href="/customers/{{ $customer->id }}" class="btn modern-btn modern-btn-secondary btn-block">
                                <i class="fas fa-user"></i> View Customer Profile
                            </a>
                            <button type="button" class="btn modern-btn modern-btn-success btn-block" onclick="openQuickCallModal({{ $customer->id }}, '{{ $customer->name }}')">
                                <i class="fas fa-phone"></i> Log Quick Call
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tracking Form -->
            <div class="col-lg-8">
                <form action="{{ route('debt-collection.update-tracking', $customer->id) }}" method="POST" id="trackingForm">
                    @csrf
                    @method('PUT')
                    
                    <div class="card modern-card">
                        <div class="card-header modern-header">
                            <h3 class="card-title">
                                <i class="fas fa-edit"></i> Collection Tracking Information
                            </h3>
                        </div>
                        
                        <div class="card-body">
                            <!-- Priority and Due Date Row -->
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="priority" class="form-label">
                                            Priority Level <span class="text-danger">*</span>
                                        </label>
                                        <select name="priority" id="priority" class="form-control modern-select" required>
                                            <option value="low" {{ ($tracking && $tracking->priority === 'low') ? 'selected' : '' }}>Low Priority</option>
                                            <option value="medium" {{ (!$tracking || $tracking->priority === 'medium' || !$tracking->priority) ? 'selected' : '' }}>Medium Priority</option>
                                            <option value="high" {{ ($tracking && $tracking->priority === 'high') ? 'selected' : '' }}>High Priority</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="due_date" class="form-label">Due Date</label>
                                        <input type="date" name="due_date" id="due_date" 
                                               value="{{ ($tracking && $tracking->due_date) ? $tracking->due_date->format('Y-m-d') : '' }}"
                                               class="form-control modern-input">
                                    </div>
                                </div>
                            </div>

                            <!-- Call Information Row -->
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="last_call_date" class="form-label">Last Call Date</label>
                                        <input type="date" name="last_call_date" id="last_call_date" 
                                               value="{{ ($tracking && $tracking->last_call_date) ? $tracking->last_call_date->format('Y-m-d') : '' }}"
                                               class="form-control modern-input">
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="calls_made" class="form-label">Total Calls Made</label>
                                        <input type="number" name="calls_made" id="calls_made" min="0" 
                                               value="{{ ($tracking && $tracking->calls_made) ? $tracking->calls_made : 0 }}"
                                               class="form-control modern-input">
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="missed_calls" class="form-label">Missed Calls</label>
                                        <input type="number" name="missed_calls" id="missed_calls" min="0" 
                                               value="{{ ($tracking && $tracking->missed_calls) ? $tracking->missed_calls : 0 }}"
                                               class="form-control modern-input">
                                    </div>
                                </div>
                            </div>

                            <!-- Promise and Follow-up Dates (Only show if columns exist) -->
                            @if(Schema::hasColumn('debt_collection_trackings', 'payment_promise_date') || Schema::hasColumn('debt_collection_trackings', 'follow_up_date'))
                            <div class="row">
                                @if(Schema::hasColumn('debt_collection_trackings', 'payment_promise_date'))
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="payment_promise_date" class="form-label">Payment Promise Date</label>
                                        <input type="date" name="payment_promise_date" id="payment_promise_date" 
                                               value="{{ ($tracking && $tracking->payment_promise_date) ? $tracking->payment_promise_date->format('Y-m-d') : '' }}"
                                               class="form-control modern-input">
                                    </div>
                                </div>
                                @endif

                                @if(Schema::hasColumn('debt_collection_trackings', 'follow_up_date'))
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="follow_up_date" class="form-label">Follow-up Date</label>
                                        <input type="date" name="follow_up_date" id="follow_up_date" 
                                               value="{{ ($tracking && $tracking->follow_up_date) ? $tracking->follow_up_date->format('Y-m-d') : '' }}"
                                               class="form-control modern-input">
                                    </div>
                                </div>
                                @endif
                            </div>
                            @endif

                            <!-- Notes (Only show if column exists) -->
                            @if(Schema::hasColumn('debt_collection_trackings', 'notes'))
                            <div class="form-group">
                                <label for="notes" class="form-label">Collection Notes</label>
                                <textarea name="notes" id="notes" rows="6" 
                                          placeholder="Enter collection notes, customer responses, payment arrangements, etc..."
                                          class="form-control modern-input">{{ ($tracking && $tracking->notes) ? $tracking->notes : '' }}</textarea>
                                <small class="form-text text-muted">Use this field to track all communication and arrangements with the customer.</small>
                            </div>
                            @endif
                        </div>

                        <!-- Form Actions -->
                        <div class="card-footer bg-light">
                            <div class="row">
                                <div class="col-md-6">
                                    <a href="{{ route('debt-collection.index') }}" class="btn btn-secondary">
                                        <i class="fas fa-arrow-left"></i> Back to Dashboard
                                    </a>
                                </div>
                                <div class="col-md-6 text-right">
                                    <button type="button" onclick="resetForm()" class="btn btn-outline-secondary mr-2">
                                        <i class="fas fa-undo"></i> Reset
                                    </button>
                                    <button type="submit" class="btn modern-btn modern-btn-success">
                                        <i class="fas fa-save"></i> Save Tracking Info
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Quick Call Modal -->
        <div class="modal fade" id="quickCallModal" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Quick Call Log</h5>
                        <button type="button" class="close" data-dismiss="modal">
                            <span>&times;</span>
                        </button>
                    </div>
                    <form id="quickCallForm">
                        @csrf
                        <div class="modal-body">
                            <div class="alert alert-info">
                                <strong>Customer:</strong> <span id="quick-call-customer-name">{{ $customer->name }}</span><br>
                                <strong>Phone:</strong> {{ $customer->phone ?? 'Not provided' }}
                            </div>

                            <div class="form-group">
                                <label>Call Status <span class="text-danger">*</span></label>
                                <select class="form-control" name="call_status" required>
                                    <option value="successful">Successful Contact</option>
                                    <option value="missed">No Answer/Missed</option>
                                    <option value="busy">Line Busy</option>
                                    <option value="disconnected">Number Disconnected</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label>Duration (minutes)</label>
                                <input type="number" class="form-control" name="duration" min="0" placeholder="Call duration">
                            </div>

                            <div class="form-group">
                                <label>Notes</label>
                                <textarea class="form-control" name="notes" rows="3" placeholder="Quick call notes..."></textarea>
                            </div>

                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" name="follow_up_required" id="quick-follow-up">
                                <label class="form-check-label" for="quick-follow-up">
                                    Requires follow-up call
                                </label>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-success">Save Call</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
@stop

@section('additional_js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function resetForm() {
        if (confirm('Are you sure you want to reset all changes?')) {
            document.getElementById('trackingForm').reset();
        }
    }
    
    // Form validation
    document.getElementById('trackingForm').addEventListener('submit', function(e) {
        const callsMade = parseInt(document.getElementById('calls_made').value) || 0;
        const missedCalls = parseInt(document.getElementById('missed_calls').value) || 0;
        
        if (missedCalls > callsMade) {
            e.preventDefault();
            Swal.fire({
                icon: 'error',
                title: 'Validation Error',
                text: 'Missed calls cannot exceed total calls made.'
            });
            return false;
        }
        
        const dueDate = document.getElementById('due_date').value;
        const paymentPromiseDate = document.getElementById('payment_promise_date');
        
        if (paymentPromiseDate && dueDate && paymentPromiseDate.value && new Date(paymentPromiseDate.value) < new Date(dueDate)) {
            if (!confirm('Payment promise date is before due date. Continue?')) {
                e.preventDefault();
                return false;
            }
        }
    });

    // Quick call modal
    function openQuickCallModal(customerId, customerName) {
        document.getElementById('quick-call-customer-name').textContent = customerName;
        document.getElementById('quickCallForm').action = `/debt-collection/customers/${customerId}/call`;
        $('#quickCallModal').modal('show');
    }

    // Quick call form submission
    document.getElementById('quickCallForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const form = this;
        const formData = new FormData(form);
        
        // Convert checkbox to proper boolean
        const followUpRequired = formData.get('follow_up_required') ? '1' : '0';
        formData.set('follow_up_required', followUpRequired);
        
        // Show loading
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
        submitBtn.disabled = true;
        
        fetch(form.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                $('#quickCallModal').modal('hide');
                form.reset();
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: 'Call logged successfully',
                    timer: 2000,
                    showConfirmButton: false
                });
                
                // Optionally refresh call counts in the form
                setTimeout(() => {
                    location.reload();
                }, 2000);
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: data.message || 'Failed to log call'
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: 'Failed to log call'
            });
        })
        .finally(() => {
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        });
    });

    // Auto-save functionality (optional)
    let autoSaveTimeout;
    const formInputs = document.querySelectorAll('#trackingForm input, #trackingForm select, #trackingForm textarea');
    
    formInputs.forEach(input => {
        input.addEventListener('input', function() {
            clearTimeout(autoSaveTimeout);
            autoSaveTimeout = setTimeout(() => {
                showAutoSaveIndicator();
            }, 2000);
        });
    });
    
    function showAutoSaveIndicator() {
        let indicator = document.getElementById('autosave-indicator');
        if (!indicator) {
            indicator = document.createElement('div');
            indicator.id = 'autosave-indicator';
            indicator.className = 'alert alert-info alert-dismissible fade show position-fixed';
            indicator.style.top = '20px';
            indicator.style.right = '20px';
            indicator.style.zIndex = '9999';
            indicator.innerHTML = `
                <i class="fas fa-info-circle"></i> Changes detected - remember to save
                <button type="button" class="close" onclick="this.parentElement.style.display='none'">
                    <span>&times;</span>
                </button>
            `;
            document.body.appendChild(indicator);
        }
        
        indicator.style.display = 'block';
        
        setTimeout(() => {
            if (indicator) {
                indicator.style.display = 'none';
            }
        }, 4000);
    }
</script>
@stop
