@extends('layouts.modern-admin')

@section('title', 'SMS Logs')
@section('page_title', 'SMS Activity Logs')

@section('header_actions')
    <div class="btn-group">
        <a class="btn modern-btn modern-btn-primary" href="{{ route('sms.dashboard') }}">
            <i class="fas fa-tachometer-alt"></i> Dashboard
        </a>
        <button type="button" class="btn modern-btn modern-btn-info" id="export-logs">
            <i class="fas fa-download"></i> Export Logs
        </button>
        <button type="button" class="btn modern-btn modern-btn-warning" id="clear-old-logs">
            <i class="fas fa-trash-alt"></i> Clear Old Logs
        </button>
    </div>
@stop

@section('page_content')
    <!-- Filter Controls -->
    <div class="card modern-card mb-4">
        <div class="card-header modern-header">
            <h3 class="card-title">
                <i class="fas fa-filter"></i> Filters & Search
            </h3>
            <div class="card-tools">
                <button type="button" class="btn btn-sm btn-light" id="clear-filters">
                    <i class="fas fa-times"></i> Clear All
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="filter-phone" class="form-label">Phone Number</label>
                        <input type="text" id="filter-phone" class="form-control modern-input" placeholder="Search by phone...">
                    </div>
                </div>
                
                <div class="col-md-2">
                    <div class="form-group">
                        <label for="filter-status" class="form-label">Status</label>
                        <select id="filter-status" class="form-control modern-select">
                            <option value="">All Status</option>
                            <option value="sent">Sent</option>
                            <option value="failed">Failed</option>
                            <option value="pending">Pending</option>
                        </select>
                    </div>
                </div>
                
                <div class="col-md-2">
                    <div class="form-group">
                        <label for="filter-provider" class="form-label">Provider</label>
                        <select id="filter-provider" class="form-control modern-select">
                            <option value="">All Providers</option>
                            <option value="bdbulksms">BD Bulk SMS</option>
                            <option value="greenweb">GreenWeb</option>
                            <option value="ssl">SSL Wireless</option>
                        </select>
                    </div>
                </div>
                
                <div class="col-md-2">
                    <div class="form-group">
                        <label for="filter-date-from" class="form-label">From Date</label>
                        <input type="date" id="filter-date-from" class="form-control modern-input">
                    </div>
                </div>
                
                <div class="col-md-2">
                    <div class="form-group">
                        <label for="filter-date-to" class="form-label">To Date</label>
                        <input type="date" id="filter-date-to" class="form-control modern-input">
                    </div>
                </div>
                
                <div class="col-md-1">
                    <div class="form-group">
                        <label class="form-label">&nbsp;</label>
                        <button type="button" class="btn modern-btn modern-btn-primary btn-block" id="apply-filters">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="stats-card stats-card-success">
                <div class="stats-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stats-content">
                    <h3 class="stats-number" id="successful-count">0</h3>
                    <p class="stats-label">Successful</p>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="stats-card stats-card-danger">
                <div class="stats-icon">
                    <i class="fas fa-times-circle"></i>
                </div>
                <div class="stats-content">
                    <h3 class="stats-number" id="failed-count">0</h3>
                    <p class="stats-label">Failed</p>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="stats-card stats-card-warning">
                <div class="stats-icon">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stats-content">
                    <h3 class="stats-number" id="pending-count">0</h3>
                    <p class="stats-label">Pending</p>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="stats-card stats-card-info">
                <div class="stats-icon">
                    <i class="fas fa-dollar-sign"></i>
                </div>
                <div class="stats-content">
                    <h3 class="stats-number" id="total-cost">৳0.00</h3>
                    <p class="stats-label">Total Cost</p>
                </div>
            </div>
        </div>
    </div>

    <!-- SMS Logs Table -->
    <div class="card modern-card">
        <div class="card-header modern-header">
            <h3 class="card-title">
                <i class="fas fa-list"></i> SMS Activity Log
            </h3>
            <div class="card-tools">
                <button type="button" class="btn btn-sm btn-light" onclick="refreshTable();" title="Refresh" data-toggle="tooltip">
                    <i class="fas fa-sync-alt"></i>
                </button>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table id="sms-logs-table" class="table modern-table mb-0">
                    <thead>
                        <tr>
                            <th>Date & Time</th>
                            <th>Phone Number</th>
                            <th>Message</th>
                            <th>Status</th>
                            <th>Provider</th>
                            <th>Cost</th>
                            <th>User</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Data will be loaded via AJAX -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- SMS Log Details Modal -->
    <div class="modal fade" id="logDetailsModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">SMS Log Details</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="logDetailsContent">
                    <!-- Content will be loaded dynamically -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
@stop

@section('additional_css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.3/css/buttons.bootstrap4.min.css">

<style>
/* Stats Cards */
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
    transform: translateY(-2px);
    box-shadow: var(--shadow-lg);
}

.stats-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
}

.stats-card-success::before {
    background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
}

.stats-card-danger::before {
    background: linear-gradient(135deg, #ff9a9e 0%, #fecfef 100%);
}

.stats-card-warning::before {
    background: linear-gradient(135deg, #fc466b 0%, #3f5efb 100%);
}

.stats-card-info::before {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.stats-icon {
    position: absolute;
    top: 1rem;
    right: 1rem;
    width: 50px;
    height: 50px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
}

.stats-card-success .stats-icon {
    background: rgba(17, 153, 142, 0.1);
    color: #11998e;
}

.stats-card-danger .stats-icon {
    background: rgba(255, 154, 158, 0.1);
    color: #ff9a9e;
}

.stats-card-warning .stats-icon {
    background: rgba(252, 70, 107, 0.1);
    color: #fc466b;
}

.stats-card-info .stats-icon {
    background: rgba(102, 126, 234, 0.1);
    color: #667eea;
}

.stats-content {
    padding-right: 60px;
}

.stats-number {
    font-size: 1.75rem;
    font-weight: 700;
    color: #333;
    margin-bottom: 0.25rem;
    line-height: 1;
}

.stats-label {
    font-size: 0.875rem;
    color: var(--text-muted);
    margin: 0;
    font-weight: 500;
}

/* Message preview styling */
.message-preview {
    max-width: 200px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    cursor: pointer;
}

.message-preview:hover {
    color: #667eea;
}

/* Phone number formatting */
.phone-number {
    font-family: 'Monaco', 'Menlo', monospace;
    font-size: 0.9em;
}

/* Log details styling */
.log-detail-item {
    margin-bottom: 1rem;
    padding-bottom: 0.5rem;
    border-bottom: 1px solid #eee;
}

.log-detail-item:last-child {
    border-bottom: none;
    margin-bottom: 0;
}

.log-detail-label {
    font-weight: 600;
    color: #333;
    margin-bottom: 0.25rem;
}

.log-detail-value {
    color: #666;
}

/* DataTable custom styling */
.dataTables_wrapper .dataTables_length select,
.dataTables_wrapper .dataTables_filter input {
    border: 1px solid #d1d3e2;
    border-radius: 0.35rem;
    padding: 0.375rem 0.75rem;
}

.dataTables_wrapper .dataTables_paginate .paginate_button.current,
.dataTables_wrapper .dataTables_paginate .paginate_button.current:hover {
    background: #667eea;
    border-color: #667eea;
    color: white !important;
}

.dataTables_wrapper .dataTables_paginate .paginate_button:hover {
    background: #f8f9fa;
    border-color: #dee2e6;
    color: #495057 !important;
}
</style>
@stop

@section('additional_js')
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.3/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.3/js/buttons.bootstrap4.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(document).ready(function() {
    // Initialize DataTable
    const table = $('#sms-logs-table').DataTable({
        processing: true,
        serverSide: true,
        searching: false, // We'll use custom filters
        ajax: {
            url: "{{ route('sms.logs.index') }}",
            data: function(d) {
                d.phone = $('#filter-phone').val();
                d.status = $('#filter-status').val();
                d.provider = $('#filter-provider').val();
                d.date_from = $('#filter-date-from').val();
                d.date_to = $('#filter-date-to').val();
            }
        },
        columns: [
            {
                data: 'date_formatted',
                name: 'created_at',
                width: '130px'
            },
            {
                data: 'phone_formatted',
                name: 'phone',
                width: '120px',
                render: function(data, type, row) {
                    return '<span class="phone-number">' + data + '</span>';
                }
            },
            {
                data: 'message_truncated',
                name: 'message',
                orderable: false,
                render: function(data, type, row) {
                    return '<span class="message-preview" title="Click to view full message" onclick="showMessageDetails(\'' + row.id + '\')">' + data + '</span>';
                }
            },
            {
                data: 'status_badge',
                name: 'status',
                width: '80px',
                orderable: false
            },
            {
                data: 'provider',
                name: 'provider',
                width: '100px',
                render: function(data, type, row) {
                    const providerNames = {
                        'bdbulksms': 'BD Bulk SMS',
                        'greenweb': 'GreenWeb',
                        'ssl': 'SSL Wireless'
                    };
                    return providerNames[data] || data.toUpperCase();
                }
            },
            {
                data: 'cost_formatted',
                name: 'cost',
                width: '80px'
            },
            {
                data: 'user_name',
                name: 'user_id',
                width: '100px'
            },
            {
                data: 'actions',
                name: 'actions',
                orderable: false,
                searchable: false,
                width: '60px'
            }
        ],
        pageLength: 25,
        lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
        order: [[0, 'desc']], // Sort by date descending
        language: {
            processing: '<div class="d-flex justify-content-center"><div class="spinner-border text-primary" role="status"><span class="sr-only">Loading...</span></div></div>',
            emptyTable: '<div class="text-center py-4"><i class="fas fa-inbox fa-3x text-muted mb-3"></i><br><h5>No SMS logs found</h5><p class="text-muted">SMS activity will appear here once messages are sent</p></div>',
            zeroRecords: '<div class="text-center py-4"><i class="fas fa-search fa-3x text-muted mb-3"></i><br><h5>No matching records found</h5><p class="text-muted">Try adjusting your filter criteria</p></div>'
        },
        drawCallback: function(settings) {
            updateStatistics();
            $('[data-toggle="tooltip"]').tooltip();
        }
    });

    // Apply filters
    $('#apply-filters').on('click', function() {
        table.ajax.reload();
    });

    // Clear filters
    $('#clear-filters').on('click', function() {
        $('#filter-phone').val('');
        $('#filter-status').val('');
        $('#filter-provider').val('');
        $('#filter-date-from').val('');
        $('#filter-date-to').val('');
        table.ajax.reload();
    });

    // Enter key support for phone filter
    $('#filter-phone').on('keypress', function(e) {
        if (e.which === 13) {
            table.ajax.reload();
        }
    });

    // View log details
    $(document).on('click', '.view-details', function() {
        const logId = $(this).data('id');
        showLogDetails(logId);
    });

    // Export logs
    $('#export-logs').on('click', function() {
        Swal.fire({
            title: 'Export SMS Logs',
            text: 'This will export the current filtered logs to Excel format.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Export',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                // Build export URL with current filters
                let exportUrl = '/sms/logs/export?';
                const filters = {
                    phone: $('#filter-phone').val(),
                    status: $('#filter-status').val(),
                    provider: $('#filter-provider').val(),
                    date_from: $('#filter-date-from').val(),
                    date_to: $('#filter-date-to').val()
                };
                
                const params = Object.keys(filters)
                    .filter(key => filters[key])
                    .map(key => key + '=' + encodeURIComponent(filters[key]))
                    .join('&');
                
                window.location.href = exportUrl + params;
            }
        });
    });

    // Clear old logs
    $('#clear-old-logs').on('click', function() {
        Swal.fire({
            title: 'Clear Old Logs?',
            text: 'This will permanently delete SMS logs older than 3 months. This action cannot be undone.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, clear them!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '/sms/logs/clear-old',
                    method: 'POST',
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Logs Cleared',
                            text: `${response.deleted_count} old log entries were deleted.`,
                            timer: 3000
                        });
                        table.ajax.reload();
                    },
                    error: function(xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: xhr.responseJSON?.message || 'Failed to clear logs'
                        });
                    }
                });
            }
        });
    });
});

// Show log details
function showLogDetails(logId) {
    $.ajax({
        url: `/sms/logs/${logId}/details`,
        method: 'GET',
        success: function(response) {
            if (response.success) {
                const log = response.data;
                const content = `
                    <div class="log-detail-item">
                        <div class="log-detail-label">Log ID</div>
                        <div class="log-detail-value">#${log.id}</div>
                    </div>
                    <div class="log-detail-item">
                        <div class="log-detail-label">Phone Number</div>
                        <div class="log-detail-value phone-number">${log.phone}</div>
                    </div>
                    <div class="log-detail-item">
                        <div class="log-detail-label">Full Message</div>
                        <div class="log-detail-value">${log.message}</div>
                    </div>
                    <div class="log-detail-item">
                        <div class="log-detail-label">Status</div>
                        <div class="log-detail-value">
                            <span class="badge badge-${getStatusColor(log.status)}">${log.status.toUpperCase()}</span>
                        </div>
                    </div>
                    <div class="log-detail-item">
                        <div class="log-detail-label">Provider</div>
                        <div class="log-detail-value">${log.provider}</div>
                    </div>
                    <div class="log-detail-item">
                        <div class="log-detail-label">Cost</div>
                        <div class="log-detail-value">৳${parseFloat(log.cost).toFixed(4)}</div>
                    </div>
                    <div class="log-detail-item">
                        <div class="log-detail-label">User</div>
                        <div class="log-detail-value">${log.user}</div>
                    </div>
                    ${log.reference_id ? `
                    <div class="log-detail-item">
                        <div class="log-detail-label">Reference ID</div>
                        <div class="log-detail-value">${log.reference_id}</div>
                    </div>
                    ` : ''}
                    ${log.response ? `
                    <div class="log-detail-item">
                        <div class="log-detail-label">Provider Response</div>
                        <div class="log-detail-value">
                            <pre style="background: #f8f9fa; padding: 0.5rem; border-radius: 0.25rem; font-size: 0.85em;">${log.response}</pre>
                        </div>
                    </div>
                    ` : ''}
                    <div class="log-detail-item">
                        <div class="log-detail-label">Created At</div>
                        <div class="log-detail-value">${log.created_at}</div>
                    </div>
                    <div class="log-detail-item">
                        <div class="log-detail-label">Updated At</div>
                        <div class="log-detail-value">${log.updated_at}</div>
                    </div>
                `;
                
                $('#logDetailsContent').html(content);
                $('#logDetailsModal').modal('show');
            }
        },
        error: function(xhr) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Failed to load log details'
            });
        }
    });
}

// Show message details
function showMessageDetails(logId) {
    showLogDetails(logId);
}

// Get status color helper
function getStatusColor(status) {
    switch(status) {
        case 'sent': return 'success';
        case 'failed': return 'danger';
        case 'pending': return 'warning';
        default: return 'secondary';
    }
}

// Update statistics
function updateStatistics() {
    // This would typically be updated from the server response
    // For now, we'll simulate it
    $.ajax({
        url: '/sms/logs/statistics',
        method: 'GET',
        data: {
            phone: $('#filter-phone').val(),
            status: $('#filter-status').val(),
            provider: $('#filter-provider').val(),
            date_from: $('#filter-date-from').val(),
            date_to: $('#filter-date-to').val()
        },
        success: function(response) {
            if (response.success) {
                $('#successful-count').text(response.data.successful || 0);
                $('#failed-count').text(response.data.failed || 0);
                $('#pending-count').text(response.data.pending || 0);
                $('#total-cost').text('৳' + (response.data.total_cost || 0).toFixed(2));
            }
        },
        error: function() {
            // Silently fail for statistics
        }
    });
}

// Refresh table
function refreshTable() {
    $('#sms-logs-table').DataTable().ajax.reload();
}

// Auto-refresh every 30 seconds for pending messages
setInterval(function() {
    if ($('#filter-status').val() === 'pending' || $('#filter-status').val() === '') {
        refreshTable();
    }
}, 30000);
</script>
@stop