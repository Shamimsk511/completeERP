{{-- resources/views/debt_collection/call_history.blade.php --}}
@extends('layouts.modern-admin')

@section('title', 'Call History - ' . $customer->name)
@section('page_title', 'Call History')

@section('header_actions')
    <div class="btn-group">
        <a class="btn modern-btn modern-btn-success" href="#" onclick="openCallModal()">
            <i class="fas fa-phone"></i> Log New Call
        </a>
        <button type="button" class="btn modern-btn modern-btn-success dropdown-toggle dropdown-toggle-split" data-toggle="dropdown">
            <span class="sr-only">Toggle Dropdown</span>
        </button>
        <div class="dropdown-menu">
            <a class="dropdown-item" href="{{ route('debt-collection.edit-tracking', $customer->id) }}">
                <i class="fas fa-edit text-warning"></i> Edit Tracking
            </a>
            <a class="dropdown-item" href="/customers/{{ $customer->id }}">
                <i class="fas fa-user text-info"></i> View Customer Profile
            </a>
            <div class="dropdown-divider"></div>
            <a class="dropdown-item" href="{{ route('debt-collection.index') }}">
                <i class="fas fa-arrow-left text-muted"></i> Back to Dashboard
            </a>
        </div>
    </div>
@stop

@section('page_content')
    <!-- Customer Info Card -->
    <div class="row mb-4">
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
                        <div class="col-sm-8">{{ $customer->phone }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4"><strong>Outstanding:</strong></div>
                        <div class="col-sm-8">
                            <span class="text-danger font-weight-bold">৳{{ number_format($customer->outstanding_balance, 2) }}</span>
                        </div>
                    </div>
                    @if($customer->debtCollectionTracking)
                    <div class="row mb-3">
                        <div class="col-sm-4"><strong>Priority:</strong></div>
                        <div class="col-sm-8">
                            <span class="badge {{ 
                                $customer->debtCollectionTracking->priority === 'high' ? 'badge-danger' : 
                                ($customer->debtCollectionTracking->priority === 'medium' ? 'badge-warning' : 'badge-success') 
                            }}">
                                {{ ucfirst($customer->debtCollectionTracking->priority) }}
                            </span>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4"><strong>Due Date:</strong></div>
                        <div class="col-sm-8">{{ $customer->debtCollectionTracking->due_date ? $customer->debtCollectionTracking->due_date->format('M d, Y') : 'Not set' }}</div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Call Statistics -->
        <div class="col-lg-8">
            @if(isset($history['stats']))
            <div class="card modern-card">
                <div class="card-header modern-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-bar"></i> Call Statistics
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 text-center">
                            <div class="stats-card stats-card-primary">
                                <div class="stats-content">
                                    <h3 class="stats-number">{{ $history['stats']->total_calls ?? 0 }}</h3>
                                    <p class="stats-label">Total Calls</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 text-center">
                            <div class="stats-card stats-card-success">
                                <div class="stats-content">
                                    <h3 class="stats-number">{{ $history['stats']->successful_calls ?? 0 }}</h3>
                                    <p class="stats-label">Successful</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 text-center">
                            <div class="stats-card stats-card-warning">
                                <div class="stats-content">
                                    <h3 class="stats-number">{{ $history['stats']->missed_calls ?? 0 }}</h3>
                                    <p class="stats-label">Missed</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 text-center">
                            <div class="stats-card stats-card-info">
                                <div class="stats-content">
                                    <h3 class="stats-number">{{ number_format($history['stats']->avg_duration ?? 0, 1) }}</h3>
                                    <p class="stats-label">Avg Minutes</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>

    <!-- Call History -->
    <div class="card modern-card">
        <div class="card-header modern-header">
            <h3 class="card-title">
                <i class="fas fa-history"></i> Call History
            </h3>
            <div class="card-tools">
                <button type="button" class="btn btn-sm btn-light" onclick="openCallModal()">
                    <i class="fas fa-plus"></i> Log Call
                </button>
            </div>
        </div>
        
        <div class="card-body">
            @if(isset($history['call_logs']) && $history['call_logs']->count() > 0)
                <div class="timeline">
                    @foreach($history['call_logs'] as $log)
                    <div class="timeline-item mb-4">
                        <div class="card">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-8">
                                        <div class="d-flex align-items-center mb-2">
                                            <span class="badge {{ 
                                                $log->call_status === 'successful' ? 'badge-success' : 
                                                ($log->call_status === 'missed' ? 'badge-danger' : 'badge-warning') 
                                            }} mr-2">
                                                {{ ucfirst($log->call_status) }}
                                            </span>
                                            
                                            @if($log->duration)
                                            <small class="text-muted mr-2">{{ $log->duration }} minutes</small>
                                            @endif
                                            
                                            @if($log->payment_promise_date)
                                            <small class="text-info">
                                                <i class="fas fa-calendar mr-1"></i>
                                                Promise: {{ $log->payment_promise_date->format('M d') }}
                                            </small>
                                            @endif
                                        </div>
                                        
                                        @if($log->notes)
                                        <p class="mb-0">{{ $log->notes }}</p>
                                        @endif
                                    </div>
                                    
                                    <div class="col-md-4 text-right">
                                        <div class="text-muted">
                                            <div>{{ $log->called_at->format('M d, Y') }}</div>
                                            <div>{{ $log->called_at->format('h:i A') }}</div>
                                            @if(isset($log->user))
                                            <div class="small">by {{ $log->user->name }}</div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                
                <!-- Pagination -->
                @if($history['call_logs']->hasPages())
                <div class="d-flex justify-content-center">
                    {{ $history['call_logs']->links() }}
                </div>
                @endif
            @else
                <div class="text-center py-5">
                    <i class="fas fa-phone fa-4x text-muted mb-4"></i>
                    <h4>No Call History</h4>
                    <p class="text-muted">No calls have been logged for this customer yet.</p>
                    <button onclick="openCallModal()" class="btn modern-btn modern-btn-success">
                        <i class="fas fa-phone mr-2"></i>Log First Call
                    </button>
                </div>
            @endif
        </div>
    </div>

    <!-- Call Modal -->
    <div class="modal fade" id="callModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Log Call</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>

                <form id="callForm" action="{{ route('debt-collection.log-call', $customer->id) }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="alert alert-info">
                            <strong>Customer:</strong> {{ $customer->name }}<br>
                            <strong>Phone:</strong> {{ $customer->phone }}
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
                            <textarea class="form-control" name="notes" rows="4" placeholder="Call details, customer response, next steps..."></textarea>
                        </div>

                        <div class="form-group">
                            <label>Payment Promise Date</label>
                            <input type="date" class="form-control" name="payment_promise_date">
                        </div>

                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" name="follow_up_required" id="follow_up">
                            <label class="form-check-label" for="follow_up">Requires follow-up</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-save"></i> Save Call
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop

@section('additional_js')
<script>
    function openCallModal() {
        $('#callModal').modal('show');
    }
    
    // Handle form submission via AJAX (JSON response)
    $('#callForm').on('submit', function(e) {
        e.preventDefault();

        const formData = $(this).serialize();
        const submitBtn = $(this).find('button[type="submit"]');
        const originalText = submitBtn.html();

        submitBtn.html('<i class="fas fa-spinner fa-spin"></i> Saving...').prop('disabled', true);

        $.ajax({
            url: $(this).attr('action'),
            method: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    $('#callModal').modal('hide');
                    window.location.reload();
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response.message || 'Failed to log call'
                    });
                }
            },
            error: function(xhr) {
                const message = xhr.responseJSON?.message || 'Failed to log call';
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: message
                });
            },
            complete: function() {
                submitBtn.html(originalText).prop('disabled', false);
            }
        });
    });
</script>
@stop
