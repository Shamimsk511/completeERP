@extends('adminlte::page')

@section('title', 'Call Schedule')

@section('content_header')
    <h1>Call Schedule Management</h1>
@stop

@section('content')
<!-- Schedule Stats -->
<div class="row">
    <div class="col-lg-3 col-6">
        <div class="small-box bg-info">
            <div class="inner">
                <h3>{{ $scheduleData['stats']['today_calls'] ?? 0 }}</h3>
                <p>Today's Calls</p>
            </div>
            <div class="icon">
                <i class="fas fa-phone"></i>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-6">
        <div class="small-box bg-success">
            <div class="inner">
                <h3>{{ $scheduleData['stats']['week_calls'] ?? 0 }}</h3>
                <p>This Week</p>
            </div>
            <div class="icon">
                <i class="fas fa-calendar-week"></i>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-6">
        <div class="small-box bg-warning">
            <div class="inner">
                <h3>{{ $scheduleData['stats']['overdue_calls'] ?? 0 }}</h3>
                <p>Overdue Calls</p>
            </div>
            <div class="icon">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-6">
        <div class="small-box bg-danger">
            <div class="inner">
                <h3>{{ $scheduleData['stats']['high_priority'] ?? 0 }}</h3>
                <p>High Priority</p>
            </div>
            <div class="icon">
                <i class="fas fa-fire"></i>
            </div>
        </div>
    </div>
</div>

<!-- View Toggle -->
<div class="row mb-3">
    <div class="col-md-6">
        <div class="btn-group" role="group">
            <button type="button" class="btn btn-outline-primary active" id="calendar-view-btn">
                <i class="fas fa-calendar"></i> Calendar View
            </button>
            <button type="button" class="btn btn-outline-primary" id="table-view-btn">
                <i class="fas fa-table"></i> Table View
            </button>
        </div>
    </div>
    <div class="col-md-6 text-right">
        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#scheduleCallModal">
            <i class="fas fa-plus"></i> Schedule Call
        </button>
    </div>
</div>

<!-- Calendar View -->
<div class="card" id="calendar-container">
    <div class="card-header">
        <h3 class="card-title">Call Schedule Calendar</h3>
    </div>
    <div class="card-body">
        <div id="call-calendar"></div>
    </div>
</div>

<!-- Table View -->
<div class="card" id="table-container" style="display: none;">
    <div class="card-header">
        <h3 class="card-title">Scheduled Calls</h3>
        <div class="card-tools">
            <div class="input-group input-group-sm" style="width: 250px;">
                <input type="text" id="schedule-search" class="form-control float-right" placeholder="Search customers...">
                <div class="input-group-append">
                    <button type="submit" class="btn btn-default">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
    <div class="card-body table-responsive p-0">
        <table id="scheduled-calls-table" class="table table-hover text-nowrap">
            <thead>
                <tr>
                    <th>Customer</th>
                    <th>Phone</th>
                    <th>Scheduled Date</th>
                    <th>Priority</th>
                    <th>Call Type</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
        </table>
    </div>
</div>

<!-- Today's Calls Quick View -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Today's Scheduled Calls</h3>
    </div>
    <div class="card-body">
        <div class="row">
            @forelse($scheduleData['today_calls'] ?? [] as $call)
            <div class="col-md-6 col-lg-4 mb-3">
                <div class="card card-outline card-{{ $call->priority === 'high' ? 'danger' : ($call->priority === 'medium' ? 'warning' : 'success') }}">
                    <div class="card-header">
                        <h5 class="card-title">{{ $call->customer_name }}</h5>
                        <div class="card-tools">
                            <span class="badge badge-{{ $call->priority === 'high' ? 'danger' : ($call->priority === 'medium' ? 'warning' : 'success') }}">
                                {{ ucfirst($call->priority) }}
                            </span>
                        </div>
                    </div>
                    <div class="card-body">
                        <p><strong>Phone:</strong> {{ $call->phone }}</p>
                        <p><strong>Time:</strong> {{ Carbon\Carbon::parse($call->scheduled_date)->format('h:i A') }}</p>
                        <p><strong>Type:</strong> {{ Str::headline($call->call_type ?? 'call') }}</p>
                        <p><strong>Balance:</strong> ৳{{ number_format($call->outstanding_balance, 2) }}</p>
                        @if($call->notes)
                        <p><strong>Notes:</strong> {{ Str::limit($call->notes, 50) }}</p>
                        @endif
                    </div>
                    <div class="card-footer">
                        <button class="btn btn-sm btn-success make-call-btn" data-customer-id="{{ $call->customer_id }}">
                            <i class="fas fa-phone"></i> Call Now
                        </button>
                        @if(($call->entry_type ?? 'schedule') === 'schedule')
                            <button class="btn btn-sm btn-info edit-schedule-btn" data-schedule-id="{{ $call->schedule_id }}">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                        @endif
                    </div>
                </div>
            </div>
            @empty
            <div class="col-12">
                <p class="text-center text-muted">No calls scheduled for today</p>
            </div>
            @endforelse
        </div>
    </div>
</div>

<!-- Schedule Call Modal -->
<div class="modal fade" id="scheduleCallModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Schedule Call</h4>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="schedule-call-form">
                <div class="modal-body">
                    <div class="form-group">
                        <label>Customer</label>
                        <select name="customer_id" class="form-control select2" required>
                            <option value="">Select Customer</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Scheduled Date & Time</label>
                        <input type="datetime-local" name="scheduled_date" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Priority</label>
                        <select name="priority" class="form-control" required>
                            <option value="low">Low</option>
                            <option value="medium" selected>Medium</option>
                            <option value="high">High</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Call Type</label>
                        <select name="call_type" class="form-control" required>
                            <option value="follow_up">Follow Up</option>
                            <option value="payment_reminder">Payment Reminder</option>
                            <option value="final_notice">Final Notice</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Notes</label>
                        <textarea name="notes" class="form-control" rows="3" placeholder="Additional notes..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Schedule Call</button>
                </div>
            </form>
        </div>
    </div>
</div>
@stop

@section('css')
<link href='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css' rel='stylesheet' />
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme@x.x.x/dist/select2-bootstrap4.min.css" rel="stylesheet">

<style>
.fc-event {
    cursor: pointer;
}
.small-box .icon {
    top: -10px;
    right: 10px;
}

/* Select2 custom styling */
.select2-customer-result {
    padding: 5px 0;
}

.select2-customer-result .customer-name {
    font-weight: 600;
    color: #333;
}

.select2-customer-result .customer-details {
    font-size: 0.9em;
    color: #666;
    margin-top: 2px;
}

.select2-container {
    width: 100% !important;
}

.select2-container--default .select2-selection--single {
    height: 38px;
    border: 1px solid #ced4da;
    border-radius: 0.25rem;
}

.select2-container--default .select2-selection--single .select2-selection__rendered {
    line-height: 36px;
    padding-left: 12px;
}

.select2-container--default .select2-selection--single .select2-selection__arrow {
    height: 36px;
    right: 10px;
}

.select2-dropdown {
    border: 1px solid #ced4da;
    border-radius: 0.25rem;
}

.select2-container--default .select2-search--dropdown .select2-search__field {
    border: 1px solid #ced4da;
    border-radius: 0.25rem;
}
</style>
<style>
.fc-event {
    cursor: pointer;
}
.small-box .icon {
    top: -10px;
    right: 10px;
}
</style>
@stop

@section('js')
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js'></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(function() {
    let calendar;
    let scheduledCallsTable;
    
    // Initialize calendar
    initializeCalendar();
    
    // Initialize DataTable
    initializeDataTable();
    
    // View toggle
    $('#calendar-view-btn').click(function() {
        $(this).addClass('active');
        $('#table-view-btn').removeClass('active');
        $('#calendar-container').show();
        $('#table-container').hide();
    });
    
    $('#table-view-btn').click(function() {
        $(this).addClass('active');
        $('#calendar-view-btn').removeClass('active');
        $('#calendar-container').hide();
        $('#table-container').show();
        if (scheduledCallsTable) {
            scheduledCallsTable.ajax.reload();
        }
    });
    
    // Schedule call form
    $('#schedule-call-form').submit(function(e) {
        e.preventDefault();
        
        $.ajax({
            url: "{{ route('debt-collection.schedule-call') }}",
            method: 'POST',
            data: $(this).serialize(),
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    $('#scheduleCallModal').modal('hide');
                    $('#schedule-call-form')[0].reset();
                    toastr.success('Call scheduled successfully');
                    
                    // Refresh views
                    if (calendar) calendar.refetchEvents();
                    if (scheduledCallsTable) scheduledCallsTable.ajax.reload();
                    location.reload(); // Refresh stats
                } else {
                    toastr.error(response.message || 'Failed to schedule call');
                }
            },
            error: function(xhr) {
                let message = 'Failed to schedule call';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                }
                toastr.error(message);
            }
        });
    });
    
    function initializeCalendar() {
        const calendarEl = document.getElementById('call-calendar');
        
        calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay'
            },
            events: function(info, successCallback, failureCallback) {
                $.ajax({
                    url: "{{ route('debt-collection.get-scheduled-calls') }}",
                    data: {
                        calendar_view: true,
                        start: info.startStr,
                        end: info.endStr
                    },
                    success: function(response) {
                        successCallback(response.events);
                    },
                    error: function() {
                        failureCallback();
                    }
                });
            },
            eventClick: function(info) {
                showCallDetails(info.event);
            },
            dateClick: function(info) {
                // Pre-fill date when clicking on calendar
                const datetime = info.dateStr + 'T09:00';
                $('input[name="scheduled_date"]').val(datetime);
                $('#scheduleCallModal').modal('show');
            }
        });
        
        calendar.render();
    }
    
    function initializeDataTable() {
        scheduledCallsTable = $('#scheduled-calls-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('debt-collection.get-scheduled-calls') }}",
                data: function(d) {
                    d.search = $('#schedule-search').val();
                }
            },
            columns: [
                { data: 'customer_name', name: 'customer_name' },
                { data: 'phone', name: 'phone' },
                { 
                    data: 'scheduled_date', 
                    name: 'scheduled_date',
                    render: function(data) {
                        return moment(data).format('MMM DD, YYYY hh:mm A');
                    }
                },
                { 
                    data: 'priority', 
                    name: 'priority',
                    render: function(data) {
                        const badgeClass = data === 'high' ? 'danger' : (data === 'medium' ? 'warning' : 'success');
                        return `<span class="badge badge-${badgeClass}">${data.charAt(0).toUpperCase() + data.slice(1)}</span>`;
                    }
                },
                { 
                    data: 'call_type', 
                    name: 'call_type',
                    render: function(data) {
                        if (!data) return '-';
                        return data.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
                    }
                },
                { 
                    data: 'status', 
                    name: 'status',
                    render: function(data) {
                        if (!data) return '-';
                        const badgeClass = data === 'completed'
                            ? 'success'
                            : (data === 'cancelled' ? 'danger' : (data === 'promised' ? 'primary' : 'info'));
                        return `<span class="badge badge-${badgeClass}">${data.charAt(0).toUpperCase() + data.slice(1)}</span>`;
                    }
                },
                {
                    data: null,
                    orderable: false,
                    render: function(data) {
                        const canEdit = data.entry_type === 'schedule';
                        const editButton = canEdit ? `
                            <button class="btn btn-sm btn-info edit-schedule-btn" data-schedule-id="${data.schedule_id}">
                                <i class="fas fa-edit"></i>
                            </button>
                        ` : '';
                        return `
                            <button class="btn btn-sm btn-success make-call-btn" data-customer-id="${data.customer_id}">
                                <i class="fas fa-phone"></i>
                            </button>
                            ${editButton}
                        `;
                    }
                }
            ],
            order: [[2, 'asc']],
            pageLength: 25
        });
        
        // Search functionality
        $('#schedule-search').on('keyup', function() {
            scheduledCallsTable.ajax.reload();
        });
    }
    
    function showCallDetails(event) {
        const props = event.extendedProps;
        const callType = props.call_type ? props.call_type.replace(/_/g, ' ') : 'call';
        const status = props.status ? props.status.replace(/_/g, ' ') : 'pending';
        
        Swal.fire({
            title: event.title,
            html: `
                <div class="text-left">
                    <p><strong>Customer:</strong> ${event.title.split(' - ')[0]}</p>
                    <p><strong>Phone:</strong> ${props.phone}</p>
                    <p><strong>Balance:</strong> ৳${parseFloat(props.balance).toLocaleString()}</p>
                    <p><strong>Priority:</strong> <span class="badge badge-${props.priority === 'high' ? 'danger' : (props.priority === 'medium' ? 'warning' : 'success')}">${props.priority}</span></p>
                    <p><strong>Type:</strong> ${callType}</p>
                    <p><strong>Status:</strong> ${status}</p>
                    <p><strong>Date:</strong> ${moment(event.start).format('MMM DD, YYYY hh:mm A')}</p>
                    ${props.notes ? `<p><strong>Notes:</strong> ${props.notes}</p>` : ''}
                </div>
            `,
            showCancelButton: true,
            confirmButtonText: '<i class="fas fa-phone"></i> Call Now',
            cancelButtonText: 'Close',
            confirmButtonColor: '#28a745'
        }).then((result) => {
            if (result.isConfirmed) {
                makeCall(props.customer_id);
            }
        });
    }
    
    // Load customers for dropdown
    loadCustomers();
    
    function loadCustomers() {
    $.ajax({
        url: "{{ route('debt-collection.get-customers') }}", // Use the main customers endpoint
        data: { 
            for_dropdown: true,
            length: 100 // Get more customers for dropdown
        },
        success: function(response) {
            const select = $('select[name="customer_id"]');
            select.empty().append('<option value="">Select Customer</option>');
            
            console.log('Customer data received:', response); // Debug log
            
            // Handle different response formats
            let customers = [];
            if (response.data && Array.isArray(response.data)) {
                customers = response.data;
            } else if (Array.isArray(response)) {
                customers = response;
            }
            
            if (customers.length > 0) {
                customers.forEach(function(customer) {
                    const customerName = customer.name || customer.customer_name || 'Unknown Customer';
                    const balance = customer.outstanding_balance || 0;
                    const phone = customer.phone || '';
                    
                    select.append(`<option value="${customer.id}" data-name="${customerName}" data-phone="${phone}" data-balance="${balance}">
                        ${customerName} - ৳${parseFloat(balance).toLocaleString()}
                    </option>`);
                });
            } else {
                console.warn('No customers found in response');
            }
        },
        error: function(xhr) {
            console.error('Failed to load customers:', xhr);
            toastr.error('Failed to load customer list');
        }
    });
}

    
    function makeCall(customerId) {
        // Redirect to call logging or open call modal
        window.location.href = `{{ route('debt-collection.index') }}?call_customer=${customerId}`;
    }

    // Initialize Select2 when modal is shown
    $('#scheduleCallModal').on('shown.bs.modal', function() {
        initializeCustomerSelect();
    });
    
    function initializeCustomerSelect() {
        const customerSelect = $('select[name="customer_id"]');
        
        // Destroy existing Select2 if it exists
        if (customerSelect.hasClass('select2-hidden-accessible')) {
            customerSelect.select2('destroy');
        }
        
        // Initialize Select2 with AJAX search
        customerSelect.select2({
            placeholder: 'Search and select customer...',
            allowClear: true,
            width: '100%',
            dropdownParent: $('#scheduleCallModal'),
            ajax: {
                url: "{{ route('debt-collection.get-customers') }}",
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                        search: {
                            value: params.term || ''
                        },
                        for_dropdown: true,
                        length: 50
                    };
                },
                processResults: function (response) {
                    console.log('Select2 response:', response);
                    
                    let customers = [];
                    
                    // Handle different response formats
                    if (response.data && Array.isArray(response.data)) {
                        customers = response.data;
                    } else if (Array.isArray(response)) {
                        customers = response;
                    } else if (response.success && response.data) {
                        customers = response.data;
                    }
                    
                    const results = customers.map(function(customer) {
                        const customerName = customer.name || customer.customer_name || 'Unknown Customer';
                        const balance = customer.outstanding_balance || 0;
                        const phone = customer.phone || '';
                        
                        return {
                            id: customer.id,
                            text: `${customerName} - ৳${parseFloat(balance).toLocaleString()} ${phone ? '(' + phone + ')' : ''}`,
                            customerName: customerName,
                            phone: phone,
                            balance: balance
                        };
                    });
                    
                    return {
                        results: results
                    };
                },
                cache: true
            },
            minimumInputLength: 0,
            templateResult: function(customer) {
                if (customer.loading) {
                    return customer.text;
                }
                
                if (!customer.customerName) {
                    return $('<span>' + customer.text + '</span>');
                }
                
                const $result = $(
                    '<div class="select2-customer-result">' +
                        '<div class="customer-name">' + customer.customerName + '</div>' +
                        '<div class="customer-details text-muted small">' +
                            'Balance: ৳' + parseFloat(customer.balance || 0).toLocaleString() +
                            (customer.phone ? ' | Phone: ' + customer.phone : '') +
                        '</div>' +
                    '</div>'
                );
                
                return $result;
            },
            templateSelection: function(customer) {
                return customer.customerName || customer.text;
            }
        });
        
        // Load initial data if no search is performed
        customerSelect.trigger('change');
    }
    
    // Alternative: Load customers without Select2 AJAX (simpler approach)
    function loadCustomersSimple() {
        $.ajax({
            url: "{{ route('debt-collection.get-customers') }}",
            data: { 
                for_dropdown: true,
                length: 100
            },
            success: function(response) {
                console.log('Customer data received:', response);
                
                const select = $('select[name="customer_id"]');
                select.empty().append('<option value="">Select Customer</option>');
                
                let customers = [];
                
                // Handle different response formats
                if (response.data && Array.isArray(response.data)) {
                    customers = response.data;
                } else if (Array.isArray(response)) {
                    customers = response;
                } else if (response.success && response.data) {
                    customers = response.data;
                }
                
                console.log('Processed customers:', customers);
                
                if (customers.length > 0) {
                    customers.forEach(function(customer) {
                        const customerName = customer.name || customer.customer_name || 'Unknown Customer';
                        const balance = customer.outstanding_balance || 0;
                        const phone = customer.phone || '';
                        
                        select.append(`<option value="${customer.id}" 
                            data-name="${customerName}" 
                            data-phone="${phone}" 
                            data-balance="${balance}">
                            ${customerName} - ৳${parseFloat(balance).toLocaleString()} ${phone ? '(' + phone + ')' : ''}
                        </option>`);
                    });
                    
                    // Initialize Select2 after loading data
                    select.select2({
                        placeholder: 'Search and select customer...',
                        allowClear: true,
                        width: '100%',
                        dropdownParent: $('#scheduleCallModal')
                    });
                } else {
                    console.warn('No customers found in response');
                    toastr.warning('No customers with outstanding balance found');
                }
            },
            error: function(xhr) {
                console.error('Failed to load customers:', xhr);
                console.error('Response:', xhr.responseText);
                toastr.error('Failed to load customer list');
            }
        });
    }
    
    // Use the simple approach initially
    loadCustomersSimple();
});
</script>
@stop
