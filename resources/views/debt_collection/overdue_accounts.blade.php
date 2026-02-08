{{-- resources/views/debt_collection/overdue_accounts.blade.php --}}
@extends('layouts.modern-admin')

@section('title', 'Overdue Accounts')
@section('page_title', 'Overdue Accounts Report')

@section('header_actions')
    <div class="btn-group">
        <button type="button" class="btn modern-btn modern-btn-success dropdown-toggle" data-toggle="dropdown">
            <i class="fas fa-download"></i> Export Report
        </button>
        <div class="dropdown-menu">
            <a href="{{ route('debt-collection.export') }}?overdue_only=1&format=csv" class="dropdown-item">
                <i class="fas fa-file-csv text-success"></i> Export as CSV
            </a>
            <a href="{{ route('debt-collection.export') }}?overdue_only=1&format=excel" class="dropdown-item">
                <i class="fas fa-file-excel text-success"></i> Export as Excel
            </a>
            <div class="dropdown-divider"></div>
            <a href="{{ route('debt-collection.index') }}" class="dropdown-item">
                <i class="fas fa-arrow-left text-muted"></i> Back to Dashboard
            </a>
        </div>
    </div>
@stop

@section('page_content')
    @if(isset($overdue) && !empty($overdue))
        <!-- Aging Breakdown -->
        <div class="row mb-4">
            <div class="col-md-3 col-sm-6">
                <div class="stats-card stats-card-info">
                    <div class="stats-icon">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <div class="stats-content">
                        <h3 class="stats-number">{{ $overdue['aging_0_30']['count'] ?? 0 }}</h3>
                        <p class="stats-label">1-30 Days Overdue</p>
                        <div class="stats-trend">
                            ৳{{ number_format($overdue['aging_0_30']['amount'] ?? 0, 2) }}
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3 col-sm-6">
                <div class="stats-card stats-card-warning">
                    <div class="stats-icon">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div class="stats-content">
                        <h3 class="stats-number">{{ $overdue['aging_31_60']['count'] ?? 0 }}</h3>
                        <p class="stats-label">31-60 Days Overdue</p>
                        <div class="stats-trend">
                            ৳{{ number_format($overdue['aging_31_60']['amount'] ?? 0, 2) }}
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3 col-sm-6">
                <div class="stats-card stats-card-danger">
                    <div class="stats-icon">
                        <i class="fas fa-fire"></i>
                    </div>
                    <div class="stats-content">
                        <h3 class="stats-number">{{ $overdue['aging_61_90']['count'] ?? 0 }}</h3>
                        <p class="stats-label">61-90 Days Overdue</p>
                        <div class="stats-trend">
                            ৳{{ number_format($overdue['aging_61_90']['amount'] ?? 0, 2) }}
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3 col-sm-6">
                <div class="stats-card" style="background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);">
                    <div class="stats-icon" style="background: rgba(255,255,255,0.1); color: white;">
                        <i class="fas fa-skull-crossbones"></i>
                    </div>
                    <div class="stats-content">
                        <h3 class="stats-number" style="color: white;">{{ $overdue['aging_90_plus']['count'] ?? 0 }}</h3>
                        <p class="stats-label" style="color: rgba(255,255,255,0.8);">90+ Days Overdue</p>
                        <div class="stats-trend" style="color: #e74c3c;">
                            ৳{{ number_format($overdue['aging_90_plus']['amount'] ?? 0, 2) }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter Controls -->
        <div class="card modern-card mb-4">
            <div class="card-header modern-header">
                <h3 class="card-title">
                    <i class="fas fa-filter"></i> Filter Overdue Accounts
                </h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-sm btn-light" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <form id="overdue-filter-form" class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="days-overdue-filter" class="form-label">Days Overdue</label>
                            <select class="form-control modern-select" id="days-overdue-filter">
                                <option value="">All Overdue</option>
                                <option value="0_30" {{ request('days_overdue') == '0_30' ? 'selected' : '' }}>1-30 Days</option>
                                <option value="31_60" {{ request('days_overdue') == '31_60' ? 'selected' : '' }}>31-60 Days</option>
                                <option value="61_90" {{ request('days_overdue') == '61_90' ? 'selected' : '' }}>61-90 Days</option>
                                <option value="90_plus" {{ request('days_overdue') == '90_plus' ? 'selected' : '' }}>90+ Days</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="min-amount-filter" class="form-label">Minimum Amount</label>
                            <input type="number" class="form-control modern-input" id="min-amount-filter" 
                                   value="{{ request('min_amount') }}" placeholder="Enter minimum amount">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="priority-filter" class="form-label">Priority Level</label>
                            <select class="form-control modern-select" id="priority-filter">
                                <option value="">All Priorities</option>
                                <option value="high" {{ request('priority') == 'high' ? 'selected' : '' }}>High Priority</option>
                                <option value="medium" {{ request('priority') == 'medium' ? 'selected' : '' }}>Medium Priority</option>
                                <option value="low" {{ request('priority') == 'low' ? 'selected' : '' }}>Low Priority</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label class="form-label">&nbsp;</label>
                            <div class="d-flex">
                                <button type="button" class="btn modern-btn modern-btn-primary mr-2" id="apply-overdue-filters">
                                    <i class="fas fa-search"></i> Apply
                                </button>
                                <button type="button" class="btn btn-outline-secondary" id="reset-overdue-filters">
                                    <i class="fas fa-times"></i> Reset
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Overdue Accounts Table -->
        <div class="card modern-card">
            <div class="card-header modern-header danger-header">
                <h3 class="card-title">
                    <i class="fas fa-exclamation-triangle"></i> Overdue Accounts Details
                </h3>
                <div class="card-tools">
                    <span class="badge badge-danger">{{ $overdue['customers']->count() ?? 0 }} Accounts</span>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table modern-table mb-0" id="overdue-accounts-table">
                        <thead>
                            <tr>
                                <th>Customer Information</th>
                                <th>Days Overdue</th>
                                <th>Outstanding Amount</th>
                                <th>Priority</th>
                                <th>Last Contact</th>
                                <th width="200px">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($overdue['customers'] ?? [] as $customer)
                                @php
                                    $daysOverdue = $customer->days_overdue ?? 0;
                                    $badgeClass = 'badge-';
                                    $cardClass = '';
                                    if ($daysOverdue <= 30) {
                                        $badgeClass .= 'info';
                                        $cardClass = 'border-info';
                                    } elseif ($daysOverdue <= 60) {
                                        $badgeClass .= 'warning';
                                        $cardClass = 'border-warning';
                                    } elseif ($daysOverdue <= 90) {
                                        $badgeClass .= 'danger';
                                        $cardClass = 'border-danger';
                                    } else {
                                        $badgeClass .= 'dark';
                                        $cardClass = 'border-dark';
                                    }
                                    
                                    $tracking = $customer->debtCollectionTracking ?? null;
                                    $priority = $tracking?->priority ?? 'medium';
                                    $lastCallDate = $tracking?->last_call_date;
                                @endphp
                                <tr class="{{ $cardClass }}">
                                    <td>
                                        <div class="customer-info">
                                            <strong>{{ $customer->name }}</strong><br>
                                            <small class="text-muted">
                                                <i class="fas fa-phone"></i> {{ $customer->phone ?? 'N/A' }}
                                            </small>
                                            @if(Schema::hasColumn('customers', 'email') && $customer->email)
                                                <br><small class="text-muted">
                                                    <i class="fas fa-envelope"></i> {{ $customer->email }}
                                                </small>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge badge-lg {{ $badgeClass }}">
                                            {{ $daysOverdue }} days
                                        </span>
                                        @if($daysOverdue > 90)
                                            <br><small class="text-danger"><i class="fas fa-exclamation-triangle"></i> Critical</small>
                                        @elseif($daysOverdue > 60)
                                            <br><small class="text-warning"><i class="fas fa-clock"></i> Urgent</small>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="text-danger font-weight-bold h5">
                                            ৳{{ number_format($customer->outstanding_balance ?? 0, 2) }}
                                        </span>
                                    </td>
                                    <td>
                                        @php
                                            $priorityBadge = $priority === 'high' ? 'badge-danger' : 
                                                           ($priority === 'medium' ? 'badge-warning' : 'badge-success');
                                        @endphp
                                        <span class="badge {{ $priorityBadge }}">
                                            {{ strtoupper($priority) }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($lastCallDate)
                                            {{ \Carbon\Carbon::parse($lastCallDate)->format('M d, Y') }}<br>
                                            <small class="text-muted">
                                                {{ \Carbon\Carbon::parse($lastCallDate)->diffForHumans() }}
                                            </small>
                                        @else
                                            <span class="text-danger">
                                                <i class="fas fa-exclamation-circle"></i> No contact
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <button type="button" class="btn btn-success btn-sm log-call-btn" 
                                                    data-customer-id="{{ $customer->id }}" 
                                                    data-customer-name="{{ $customer->name }}"
                                                    title="Log Call" data-toggle="tooltip">
                                                <i class="fas fa-phone"></i>
                                            </button>
                                            <a href="{{ route('debt-collection.call-history', $customer->id) }}" 
                                               class="btn btn-info btn-sm" title="Call History" data-toggle="tooltip">
                                                <i class="fas fa-history"></i>
                                            </a>
                                            <a href="{{ route('debt-collection.edit-tracking', $customer->id) }}" 
                                               class="btn btn-warning btn-sm" title="Edit Tracking" data-toggle="tooltip">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5">
                                        <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                                        <h5>No Overdue Accounts Found</h5>
                                        <p class="text-muted">All customers are up to date with their payments!</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Call Modal -->
        <div class="modal fade" id="callModal" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Log Customer Call</h5>
                        <button type="button" class="close" data-dismiss="modal">
                            <span>&times;</span>
                        </button>
                    </div>
                    <form id="callForm">
                        <div class="modal-body">
                            <input type="hidden" id="call-customer-id">
                            
                            <div class="alert alert-info" id="customer-info-display" style="display: none;">
                                <strong>Customer:</strong> <span id="modal-customer-name"></span><br>
                                <strong>Outstanding:</strong> <span id="modal-customer-balance"></span>
                            </div>

                            <div class="form-group">
                                <label>Call Status <span class="text-danger">*</span></label>
                                <select class="form-control" id="call-status" required>
                                    <option value="successful">Successful Contact</option>
                                    <option value="missed">No Answer/Missed</option>
                                    <option value="busy">Line Busy</option>
                                    <option value="disconnected">Number Disconnected</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label>Duration (minutes)</label>
                                <input type="number" class="form-control" id="call-duration" min="0" placeholder="Call duration in minutes">
                            </div>

                            <div class="form-group">
                                <label>Notes</label>
                                <textarea class="form-control" id="call-notes" rows="4" placeholder="Call details, customer response, next steps, payment arrangements..."></textarea>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Payment Promise Date</label>
                                        <input type="date" class="form-control" id="payment-promise-date">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Follow-up Date</label>
                                        <input type="date" class="form-control" id="follow-up-date">
                                    </div>
                                </div>
                            </div>

                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="follow-up-required">
                                <label class="form-check-label" for="follow-up-required">
                                    <strong>Requires follow-up call</strong>
                                </label>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">
                                <i class="fas fa-times"></i> Cancel
                            </button>
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-save"></i> Save Call Log
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    @else
        <!-- Error State -->
        <div class="card modern-card">
            <div class="card-body text-center py-5">
                <i class="fas fa-exclamation-triangle fa-4x text-warning mb-4"></i>
                <h4>Unable to Load Overdue Accounts</h4>
                <p class="text-muted">There was an error loading the overdue accounts data.</p>
                <a href="{{ route('debt-collection.index') }}" class="btn modern-btn modern-btn-primary">
                    <i class="fas fa-arrow-left"></i> Back to Dashboard
                </a>
            </div>
        </div>
    @endif
@stop

@section('additional_css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap4.min.css">
<style>
/* Enhanced stats cards for overdue report */
.stats-card {
    background: white;
    border-radius: var(--border-radius-lg);
    padding: 1.5rem;
    box-shadow: var(--shadow);
    border: none;
    position: relative;
    overflow: hidden;
    transition: all 0.3s ease;
    margin-bottom: 1.5rem;
}

.stats-card:hover {
    transform: translateY(-3px);
    box-shadow: var(--shadow-lg);
}

.stats-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.stats-card-warning::before {
    background: linear-gradient(135deg, #ffeaa7 0%, #fdcb6e 100%);
}

.stats-card-danger::before {
    background: linear-gradient(135deg, #fd79a8 0%, #e84393 100%);
}

.stats-card-info::before {
    background: linear-gradient(135deg, #74b9ff 0%, #0984e3 100%);
}

.badge-lg {
    font-size: 0.9rem;
    padding: 0.4rem 0.8rem;
}

.border-info { border-left: 4px solid #17a2b8 !important; }
.border-warning { border-left: 4px solid #ffc107 !important; }
.border-danger { border-left: 4px solid #dc3545 !important; }
.border-dark { border-left: 4px solid #343a40 !important; }

.customer-info strong {
    font-size: 1.05rem;
}

/* Responsive design */
@media (max-width: 768px) {
    .btn-group {
        display: flex;
        flex-direction: column;
        width: 100%;
    }
    
    .btn-group .btn {
        margin-bottom: 0.25rem;
        border-radius: var(--border-radius) !important;
    }
}
</style>
@stop

@section('additional_js')
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(document).ready(function() {
    // Initialize DataTable
    $('#overdue-accounts-table').DataTable({
        responsive: true,
        pageLength: 25,
        order: [[1, 'desc']], // Order by days overdue
        language: {
            search: "Search customers:",
            lengthMenu: "Show _MENU_ customers per page",
            info: "Showing _START_ to _END_ of _TOTAL_ overdue accounts",
            emptyTable: "No overdue accounts found"
        },
        columnDefs: [
            { targets: [5], orderable: false } // Actions column
        ]
    });

    // Filter functionality
    $('#apply-overdue-filters').on('click', function(e) {
        e.preventDefault();
        applyFilters();
    });

    $('#reset-overdue-filters').on('click', function(e) {
        e.preventDefault();
        resetFilters();
    });

    function applyFilters() {
        let params = [];
        
        const daysOverdue = $('#days-overdue-filter').val();
        const minAmount = $('#min-amount-filter').val();
        const priority = $('#priority-filter').val();
        
        if (daysOverdue) params.push('days_overdue=' + encodeURIComponent(daysOverdue));
        if (minAmount) params.push('min_amount=' + encodeURIComponent(minAmount));
        if (priority) params.push('priority=' + encodeURIComponent(priority));
        
        const newUrl = window.location.pathname + (params.length ? '?' + params.join('&') : '');
        window.location.href = newUrl;
    }

    function resetFilters() {
        window.location.href = window.location.pathname;
    }

    // Log call functionality
    $(document).on('click', '.log-call-btn', function() {
        const customerId = $(this).data('customer-id');
        const customerName = $(this).data('customer-name');
        
        $('#call-customer-id').val(customerId);
        $('#modal-customer-name').text(customerName);
        $('#customer-info-display').show();
        
        // Reset form
        $('#callForm')[0].reset();
        $('#call-customer-id').val(customerId);
        
        $('#callModal').modal('show');
    });

    // Call form submission
    $('#callForm').on('submit', function(e) {
        e.preventDefault();
        
        const customerId = $('#call-customer-id').val();
        
        if (!customerId) {
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: 'Customer ID is missing'
            });
            return;
        }
        
        const formData = {
            call_status: $('#call-status').val(),
            duration: $('#call-duration').val() || '',
            notes: $('#call-notes').val() || '',
            payment_promise_date: $('#payment-promise-date').val() || '',
            follow_up_required: $('#follow-up-required').is(':checked') ? '1' : '0',
            _token: $('meta[name="csrf-token"]').attr('content')
        };

        // Show loading
        const submitBtn = $(this).find('button[type="submit"]');
        const originalText = submitBtn.html();
        submitBtn.html('<i class="fas fa-spinner fa-spin"></i> Saving...').prop('disabled', true);

        $.ajax({
            url: `/debt-collection/customers/${customerId}/call`,
            method: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    $('#callModal').modal('hide');
                    $('#callForm')[0].reset();
                    
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: 'Call logged successfully',
                        timer: 2000,
                        showConfirmButton: false
                    });
                    
                    // Refresh page to update data
                    setTimeout(() => {
                        location.reload();
                    }, 2000);
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: response.message || 'Failed to log call'
                    });
                }
            },
            error: function(xhr) {
                let errorMessage = 'Failed to log call';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: errorMessage
                });
            },
            complete: function() {
                submitBtn.html(originalText).prop('disabled', false);
            }
        });
    });

    // Initialize tooltips
    $('[data-toggle="tooltip"]').tooltip();
});
</script>
@stop