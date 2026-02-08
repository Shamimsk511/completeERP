@extends('layouts.modern-admin')

@section('title', 'Customers')
@section('page_title', 'Customer Management')

@section('header_actions')
    <div class="btn-group">
        <a class="btn modern-btn modern-btn-success" href="{{ route('customers.create') }}">
            <i class="fas fa-user-plus"></i> Add New Customer
        </a>
        <button type="button" class="btn modern-btn modern-btn-success dropdown-toggle dropdown-toggle-split" data-toggle="dropdown">
            <span class="sr-only">Toggle Dropdown</span>
        </button>
        <div class="dropdown-menu">
            <a class="dropdown-item" href="{{ route('customers.import.form') }}">
                <i class="fas fa-file-excel text-success"></i> Import from Excel
            </a>
            <a class="dropdown-item" href="{{ route('customers.export.template') }}">
                <i class="fas fa-download text-info"></i> Download Template
            </a>
        </div>
    </div>
@stop

@section('page_content')
    <!-- Customer Statistics Cards -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6">
            <div class="stats-card stats-card-primary">
                <div class="stats-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stats-content">
                    <h3 class="stats-number" id="total-customers">0</h3>
                    <p class="stats-label">Total Customers</p>
                    <div class="stats-trend">
                        <i class="fas fa-arrow-up"></i> 5% from last month
                    </div>
                </div>
                <a href="#" class="stats-link" onclick="clearFilters();">
                    View All <i class="fas fa-arrow-right"></i>
                </a>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6">
            <div class="stats-card stats-card-success">
                <div class="stats-icon">
                    <i class="fas fa-dollar-sign"></i>
                </div>
                <div class="stats-content">
                    <h3 class="stats-number" id="total-outstanding">0.00</h3>
                    <p class="stats-label">Total Outstanding</p>
                    <div class="stats-trend">
                        <i class="fas fa-arrow-down"></i> 2% from last month
                    </div>
                </div>
                <a href="#" class="stats-link" onclick="filterByBalance('positive');">
                    View Details <i class="fas fa-arrow-right"></i>
                </a>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6">
            <div class="stats-card stats-card-warning">
                <div class="stats-icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div class="stats-content">
                    <h3 class="stats-number" id="overdue-customers">0</h3>
                    <p class="stats-label">High Outstanding</p>
                    <div class="stats-trend">
                        <i class="fas fa-minus"></i> No change
                    </div>
                </div>
                <a href="#" class="stats-link" onclick="filterByBalance('high');">
                    View Details <i class="fas fa-arrow-right"></i>
                </a>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6">
            <div class="stats-card stats-card-info">
                <div class="stats-icon">
                    <i class="fas fa-user-check"></i>
                </div>
                <div class="stats-content">
                    <h3 class="stats-number" id="active-customers">0</h3>
                    <p class="stats-label">Recently Active</p>
                    <div class="stats-trend">
                        <i class="fas fa-arrow-up"></i> 8% from last month
                    </div>
                </div>
                <a href="#" class="stats-link" onclick="filterByActivity('recent');">
                    View Details <i class="fas fa-arrow-right"></i>
                </a>
            </div>
        </div>
    </div>

    <!-- Advanced Filters Section -->
    <div class="card modern-card mb-4">
        <div class="card-header modern-header">
            <h3 class="card-title">
                <i class="fas fa-filter"></i> Search & Filters
            </h3>
            <div class="card-tools">
                <button type="button" class="btn btn-sm btn-light" id="clear-all-filters">
                    <i class="fas fa-times"></i> Clear All
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="search-input" class="form-label">Search Customers</label>
                        <div class="input-group">
                            <input type="text" id="search-input" class="form-control modern-input" placeholder="Name, phone, address...">
                            <div class="input-group-append">
                                <button class="btn btn-outline-secondary" type="button" id="clear-search-btn" title="Clear search" data-toggle="tooltip">
                                    <i class="fas fa-times"></i>
                                </button>
                                <button class="btn modern-btn modern-btn-primary" type="button" id="search-btn">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-2">
                    <div class="form-group">
                        <label for="group-filter" class="form-label">Customer Group</label>
                        <select id="group-filter" class="form-control modern-select">
                            <option value="">All Groups</option>
                            @foreach($customerGroups as $group)
                                <option value="{{ $group->id }}">
                                    {{ str_repeat('— ', $group->depth) }}{{ $group->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="col-md-2">
                    <div class="form-group">
                        <label for="balance-filter" class="form-label">Balance Status</label>
                        <select id="balance-filter" class="form-control modern-select">
                            <option value="">All Customers</option>
                            <option value="positive">Has Outstanding</option>
                            <option value="zero">Zero Balance</option>
                            <option value="negative">Credit Balance</option>
                            <option value="high">High Outstanding (>5000)</option>
                        </select>
                    </div>
                </div>

                <div class="col-md-2">
                    <div class="form-group">
                        <label for="sort-filter" class="form-label">Sort By</label>
                        <select id="sort-filter" class="form-control modern-select">
                            <option value="name_asc">Name (A-Z)</option>
                            <option value="name_desc">Name (Z-A)</option>
                            <option value="balance_desc">Highest Balance</option>
                            <option value="balance_asc">Lowest Balance</option>
                            <option value="recent">Most Recent</option>
                        </select>
                    </div>
                </div>
                
                <div class="col-md-2">
                    <div class="form-group">
                        <label class="form-label">Quick Actions</label>
                        <div class="btn-group btn-block">
                            <button type="button" class="btn modern-btn modern-btn-info btn-sm" id="export-btn">
                                <i class="fas fa-download"></i> Export
                            </button>
                            <button type="button" class="btn modern-btn modern-btn-warning btn-sm" id="bulk-actions-btn">
                                <i class="fas fa-cogs"></i> Bulk Actions
                            </button>
                            <button type="button" class="btn modern-btn modern-btn-success btn-sm" id="refresh-btn">
                                <i class="fas fa-sync-alt"></i> Refresh
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Customers Table -->
    <div class="card modern-card">
        <div class="card-header modern-header success-header">
            <h3 class="card-title">
                <i class="fas fa-users"></i> Customer Directory
            </h3>
            <div class="card-tools">
                <button type="button" class="btn btn-sm btn-light" data-toggle="tooltip" title="Refresh Table" id="refresh-table">
                    <i class="fas fa-sync-alt"></i>
                </button>
                <button type="button" class="btn btn-sm btn-light" data-toggle="tooltip" title="Column Visibility" id="column-visibility">
                    <i class="fas fa-columns"></i>
                </button>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table id="customers-table" class="table modern-table mb-0">
                    <thead>
                        <tr>
                            <th width="40px">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="select-all">
                                    <label class="custom-control-label" for="select-all"></label>
                                </div>
                            </th>
                            <th>Customer Info</th>
                            <th>Contact Details</th>
                            <th>Opening Balance</th>
                            <th>Outstanding Balance</th>
                            <th>Status</th>
                            <th width="200px">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Data will be loaded via AJAX -->
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <div class="dataTables_info">
                        Showing <span id="showing-start">0</span> to <span id="showing-end">0</span> of <span id="total-records">0</span> entries
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="float-right">
                        <div class="btn-group">
                            <button class="btn modern-btn modern-btn-primary btn-sm" id="export-selected" disabled>
                                <i class="fas fa-download"></i> Export Selected
                            </button>
                            <button class="btn modern-btn modern-btn-warning btn-sm" id="bulk-edit" disabled>
                                <i class="fas fa-edit"></i> Bulk Edit
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions Section -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card modern-card">
                <div class="card-header modern-header info-header">
                    <h3 class="card-title">
                        <i class="fas fa-bolt"></i> Quick Actions
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 col-sm-6 mb-3">
                            <a href="{{ route('customers.create') }}" class="quick-action-card">
                                <div class="quick-action-icon bg-success">
                                    <i class="fas fa-user-plus"></i>
                                </div>
                                <div class="quick-action-content">
                                    <h5>Add Customer</h5>
                                    <p>Register new customer</p>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-3 col-sm-6 mb-3">
                            <a href="{{ route('customers.import.form') }}" class="quick-action-card">
                                <div class="quick-action-icon bg-info">
                                    <i class="fas fa-file-excel"></i>
                                </div>
                                <div class="quick-action-content">
                                    <h5>Import Customers</h5>
                                    <p>Bulk import from Excel</p>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-3 col-sm-6 mb-3">
                            <a href="{{ route('transactions.create') }}" class="quick-action-card">
                                <div class="quick-action-icon bg-primary">
                                    <i class="fas fa-money-bill"></i>
                                </div>
                                <div class="quick-action-content">
                                    <h5>Add Payment</h5>
                                    <p>Record customer payment</p>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-3 col-sm-6 mb-3">
                            <a href="{{ route('customers.export.template') }}" class="quick-action-card">
                                <div class="quick-action-icon bg-warning">
                                    <i class="fas fa-download"></i>
                                </div>
                                <div class="quick-action-content">
                                    <h5>Download Template</h5>
                                    <p>Excel import template</p>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
       ස

        </div>
    </div>
@stop

@section('additional_css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.3/css/buttons.bootstrap4.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.bootstrap4.min.css">

<style>
/* Stats Cards - Matching your invoice design */
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
    transform: translateY(-5px);
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

.stats-card-primary::before {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.stats-card-success::before {
    background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
}

.stats-card-warning::before {
    background: linear-gradient(135deg, #fc466b 0%, #3f5efb 100%);
}

.stats-card-info::before {
    background: linear-gradient(135deg, #ff9a9e 0%, #fecfef 100%);
}

.stats-icon {
    position: absolute;
    top: 1rem;
    right: 1rem;
    width: 60px;
    height: 60px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(102, 126, 234, 0.1);
    color: #667eea;
    font-size: 1.5rem;
}

.stats-card-success .stats-icon {
    background: rgba(17, 153, 142, 0.1);
    color: #11998e;
}

.stats-card-warning .stats-icon {
    background: rgba(252, 70, 107, 0.1);
    color: #fc466b;
}

.stats-card-info .stats-icon {
    background: rgba(255, 154, 158, 0.1);
    color: #ff9a9e;
}

.stats-content {
    padding-right: 80px;
}

.stats-number {
    font-size: 2rem;
    font-weight: 700;
    color: #333;
    margin-bottom: 0.5rem;
    line-height: 1;
}

.stats-label {
    font-size: 0.875rem;
    color: var(--text-muted);
    margin-bottom: 0.5rem;
    font-weight: 500;
}

.stats-trend {
    font-size: 0.75rem;
    color: #28a745;
    font-weight: 500;
}

.stats-trend .fa-arrow-down {
    color: #dc3545;
}

.stats-trend .fa-minus {
    color: var(--text-muted);
}

.stats-link {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    padding: 0.75rem 1.5rem;
    background: rgba(0, 0, 0, 0.03);
    color: #667eea;
    text-decoration: none;
    font-size: 0.875rem;
    font-weight: 500;
    transition: all 0.3s ease;
}

.stats-link:hover {
    background: rgba(102, 126, 234, 0.1);
    color: #667eea;
    text-decoration: none;
}

/* Customer Info Cell Styling - Updated */
.customer-info {
    display: flex;
    flex-direction: column;
}

.customer-name-link {
    font-size: 1rem;
    font-weight: 600;
    color: #667eea;
    text-decoration: none;
    margin-bottom: 0.25rem;
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    transition: all 0.3s ease;
    display: inline-block;
    background: linear-gradient(135deg, rgba(102, 126, 234, 0.1), rgba(118, 75, 162, 0.1));
    border: 1px solid rgba(102, 126, 234, 0.2);
}

.customer-name-link:hover {
    color: #5a67d8;
    text-decoration: none;
    background: linear-gradient(135deg, rgba(102, 126, 234, 0.15), rgba(118, 75, 162, 0.15));
    border-color: rgba(102, 126, 234, 0.3);
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(102, 126, 234, 0.2);
}

.customer-details {
    display: flex;
    flex-direction: column;
}

.customer-details small {
    color: var(--text-muted);
    font-size: 0.8rem;
    margin-top: 0.25rem;
}

.customer-details small.customer-id {
    display: block;
}

.customer-group-badge {
    display: inline-block;
    background: linear-gradient(135deg, rgba(40, 167, 69, 0.1), rgba(32, 201, 151, 0.1));
    color: #28a745;
    padding: 0.15rem 0.5rem;
    border-radius: 0.25rem;
    font-size: 0.7rem;
    font-weight: 500;
    border: 1px solid rgba(40, 167, 69, 0.2);
    margin-top: 0.25rem;
}

.customer-group-badge i {
    margin-right: 0.25rem;
    font-size: 0.65rem;
}

/* Contact Details Styling */
.contact-info {
    line-height: 1.4;
}

.contact-info .phone {
    font-weight: 500;
    color: #333;
    margin-bottom: 0.25rem;
}

.contact-info .address {
    color: var(--text-muted);
    font-size: 0.85rem;
}

/* Balance Styling */
.balance-amount {
    font-weight: 600;
    font-size: 1rem;
}

.balance-positive {
    color: #dc3545;
}

.balance-zero {
    color: #28a745;
}

.balance-negative {
    color: #17a2b8;
}

/* Status Badges */
.status-badge {
    padding: 0.25rem 0.75rem;
    border-radius: var(--border-radius);
    font-size: 0.75rem;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.status-active {
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    color: white;
}

.status-inactive {
    background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
    color: white;
}

.status-overdue {
    background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
    color: white;
}

.status-credit {
    background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
    color: white;
}

/* Action Buttons */
.action-buttons {
    display: flex;
    gap: 0.25rem;
    justify-content: center;
    flex-wrap: wrap;
}

.action-buttons .btn {
    padding: 0.5rem 0.75rem;
    font-size: 0.875rem;
    border-radius: var(--border-radius);
    min-width: 40px;
}

/* Quick Action Cards */
.quick-action-card {
    display: block;
    background: white;
    border-radius: var(--border-radius);
    padding: 1.5rem;
    text-decoration: none;
    color: inherit;
    border: 2px solid var(--border-color);
    transition: all 0.3s ease;
    height: 100%;
}

.quick-action-card:hover {
    text-decoration: none;
    color: inherit;
    border-color: #667eea;
    transform: translateY(-3px);
    box-shadow: var(--shadow-lg);
}

.quick-action-icon {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 1rem;
    color: white;
    font-size: 1.25rem;
}

.quick-action-content h5 {
    font-size: 1rem;
    font-weight: 600;
    margin-bottom: 0.5rem;
    color: #333;
}

.quick-action-content p {
    font–

-size: 0.875rem;
    color: var(--text-muted);
    margin: 0;
}

/* Enhanced Checkbox Styling */
.custom-control-input:checked ~ .custom-control-label::before {
    background-color: var(--primary-color);
    border-color: var(--primary-color);
}

/* Loading State */
.table-loading {
    position: relative;
    opacity: 0.6;
}

.table-loading::after {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 40px;
    height: 40px;
    border: 4px solid #f3f3f3;
    border-top: 4px solid var(--primary-color);
    border-radius: 50%;
    animation: spin 1s linear infinite;
    z-index: 10;
}

@keyframes spin {
    0% { transform: translate(-50%, -50%) rotate(0deg); }
    100% { transform: translate(-50%, -50%) rotate(360deg); }
}

/* Responsive Design */
@media (max-width: 768px) {
    .stats-content {
        padding-right: 0;
    }
    
    .stats-icon {
        position: static;
        margin-bottom: 1rem;
        width: 50px;
        height: 50px;
        font-size: 1.25rem;
    }
    
    .stats-number {
        font-size: 1.5rem;
    }
    
    .customer-info {
        text-align: left;
    }
    
    .customer-name-link {
        font-size: 0.9rem;
        padding: 0.2rem 0.4rem;
    }
    
    .action-buttons {
        flex-direction: column;
    }
}
</style>
@stop

@section('additional_js')
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.3/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.3/js/buttons.bootstrap4.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.2.9/js/responsive.bootstrap4.min.js"></script>
<script>
$(document).ready(function() {
    const table = $('#customers-table').DataTable({
        processing: true,
        serverSide: true,
        searching: false,
        ajax: {
            url: "{{ route('customers.getCustomers') }}",
            data: function(d) {
                d.search_input = $('#search-input').val();
                d.group_filter = $('#group-filter').val();
                d.balance_filter = $('#balance-filter').val();
                d.sort_filter = $('#sort-filter').val();
            }
        },
        columns: [
            {
                data: null,
                name: 'checkbox',
                orderable: false,
                searchable: false,
                width: '40px',
                render: function(data, type, row) {
                    return `<div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input customer-checkbox" id="check_${row.id}" value="${row.id}">
                                <label class="custom-control-label" for="check_${row.id}"></label>
                            </div>`;
                }
            },
            {
                data: 'name',
                name: 'name',
                render: function(data, type, row) {
                    const groupBadge = row.account_group_name
                        ? `<small class="customer-group-badge"><i class="fas fa-users"></i> ${row.account_group_name}</small>`
                        : '';
                    return `
                        <div class="customer-info">
                            <div class="customer-details">
                                <a href="/customers/${row.id}" class="customer-name-link">
                                    <i class="fas fa-user mr-1"></i>${row.name}
                                </a>
                                ${groupBadge}
                                <small class="customer-id">ID: ${row.id}</small>
                            </div>
                        </div>
                    `;
                }
            },
            {
                data: 'phone',
                name: 'phone',
                render: function(data, type, row) {
                    return `
                        <div class="contact-info">
                            <div class="phone">
                                <i class="fas fa-phone text-primary"></i> ${row.phone || 'N/A'}
                            </div>
                            <div class="address">
                                <i class="fas fa-map-marker-alt text-muted"></i> ${row.address || 'No address provided'}
                            </div>
                        </div>
                    `;
                }
            },
            {
                data: 'opening_balance',
                name: 'opening_balance',
                render: function(data, type, row) {
                    const amount = parseFloat(data) || 0;
                    const className = amount > 0 ? 'balance-positive' : amount < 0 ? 'balance-negative' : 'balance-zero';
                    return `<span class="balance-amount ${className}">৳${amount.toFixed(2)}</span>`;
                }
            },
            {
                data: 'outstanding_balance',
                name: 'outstanding_balance',
                render: function(data, type, row) {
                    const amount = parseFloat(data) || 0;
                    const className = amount > 0 ? 'balance-positive' : amount < 0 ? 'balance-negative' : 'balance-zero';
                    return `<span class="balance-amount ${className}">৳${amount.toFixed(2)}</span>`;
                }
            },
            {
                data: null,
                name: 'status',
                render: function(data, type, row) {
                    const outstanding = parseFloat(row.outstanding_balance) || 0;
                    if (outstanding > 5000) {
                        return '<span class="status-badge status-overdue">High Outstanding</span>';
                    } else if (outstanding > 0) {
                        return '<span class="status-badge status-active">Outstanding</span>';
                    } else if (outstanding < 0) {
                        return '<span class="status-badge status-credit">Credit</span>';
                    } else {
                        return '<span class="status-badge status-active">Clear</span>';
                    }
                }
            },
            {
                data: 'action',
                name: 'action',
                orderable: false,
                searchable: false,
                render: function(data, type, row) {
                    const smsButton = (parseFloat(row.outstanding_balance) > 0 && row.phone && row.phone !== 'N/A') ? 
                        `<button type="button" class="btn modern-btn modern-btn-info btn-sm send-sms" data-id="${row.id}" data-balance="${row.outstanding_balance}" data-phone="${row.phone}" title="Send SMS Reminder" data-toggle="tooltip">
                            <i class="fas fa-sms"></i>
                        </button>` : '';
                    return `
                        <div class="action-buttons">
                            <a href="/customers/${row.id}" class="btn modern-btn modern-btn-primary btn-sm" title="View" data-toggle="tooltip">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="/customers/${row.id}/edit" class="btn modern-btn modern-btn-warning btn-sm" title="Edit" data-toggle="tooltip">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="/customers/${row.id}/ledger/" class="btn modern-btn modern-btn-success btn-sm" title="Ledger" data-toggle="tooltip">
                                <i class="fas fa-book"></i>
                            </a>
                            ${smsButton}
                            <button type="button" class="btn modern-btn modern-btn-danger btn-sm delete-customer" data-id="${row.id}" title="Delete" data-toggle="tooltip">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    `;
                }
            }
        ],
        pageLength: 25,
        lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
        order: [[1, 'asc']],
        responsive: true,
        language: {
            processing: '<div class="d-flex justify-content-center"><div class="spinner-border text-primary" role="status"><span class="sr-only">Loading...</span></div></div>',
            emptyTable: '<div class="text-center py-4"><i class="fas fa-users fa-3x text-muted mb-3"></i><br><h5>No customers found</h5><p class="text-muted">Start by adding your first customer</p></div>',
            zeroRecords: '<div class="text-center py-4"><i class="fas fa-search fa-3x text-muted mb-3"></i><br><h5>No matching customers found</h5><p class="text-muted">Try adjusting your search criteria</p></div>'
        },
        drawCallback: function(settings) {
            const api = this.api();
            const info = api.page.info();
            $('#showing-start').text(info.start + 1);
            $('#showing-end').text(info.end);
            $('#total-records').text(info.recordsTotal);
            updateStatistics(settings.json ? settings.json.summary : null);
            updateBulkActionButtons();
        }
    });

    // Search functionality (debounced)
    let searchTimeout;
    $('#search-input').on('keyup', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => table.ajax.reload(), 300);
    });

    $('#search-btn').on('click', function() {
        table.ajax.reload();
    });

    $('#clear-search-btn').on('click', function() {
        $('#search-input').val('').focus();
        table.ajax.reload();
    });

    $('#group-filter, #balance-filter, #sort-filter').on('change', function() {
        table.ajax.reload();
    });

    $('#clear-all-filters').on('click', function() {
        clearFilters();
    });

    function clearFilters() {
        $('#search-input').val('');
        $('#group-filter').val('');
        $('#balance-filter').val('');
        $('#sort-filter').val('name_asc');
        table.ajax.reload();
    }

    $('#select-all').on('change', function() {
        const isChecked = $(this).is(':checked');
        $('.customer-checkbox').prop('checked', isChecked);
        updateBulkActionButtons();
    });

    $(document).on('change', '.customer-checkbox', function() {
        updateBulkActionButtons();
        const totalCheckboxes = $('.customer-checkbox').length;
        const checkedCheckboxes = $('.customer-checkbox:checked').length;
        $('#select-all').prop('checked', totalCheckboxes === checkedCheckboxes);
    });

    function updateBulkActionButtons() {
        const checkedCount = $('.customer-checkbox:checked').length;
        $('#export-selected, #bulk-edit').prop('disabled', checkedCount === 0);
    }

    $(document).on('click', '.delete-customer', function() {
        const customerId = $(this).data('id');
        Swal.fire({
            title: 'Are you sure?',
            text: "This action cannot be undone!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Deleting...',
                    text: 'Please wait while we delete the customer.',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                const form = $('<form>', {
                    method: 'POST',
                    action: `/customers/${customerId}`
                });
                form.append($('<input>', {
                    type: 'hidden',
                    name: '_token',
                    value: $('meta[name="csrf-token"]').attr('content')
                }));
                form.append($('<input>', {
                    type: 'hidden',
                    name: '_method',
                    value: 'DELETE'
                }));
                $.ajax({
                    url: `/customers/${customerId}`,
                    type: 'POST',
                    data: form.serialize(),
                    success: function(response) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Deleted!',
                            text: 'Customer has been deleted successfully.',
                            timer: 2000,
                            showConfirmButton: false
                        });
                        table.ajax.reload();
                    },
                    error: function(xhr) {
                        let errorMessage = 'Something went wrong. Please try again.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        }
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: errorMessage
                        });
                    }
                });
            }
        });
    });

    $(document).on('click', '.send-sms', function() {
        const customerId = $(this).data('id');
        const balance = $(this).data('balance');
        const phone = $(this).data('phone');

        if (!phone || phone === 'N/A') {
            Swal.fire({
                icon: 'warning',
                title: 'No Phone Number',
                text: 'This customer does not have a valid phone number.'
            });
            return;
        }

        if (parseFloat(balance) <= 0) {
            Swal.fire({
                icon: 'warning',
                title: 'No Outstanding Balance',
                text: 'This customer has no outstanding balance to remind.'
            });
            return;
        }

        Swal.fire({
            title: 'Send SMS Reminder?',
            text: `Send a payment reminder SMS for outstanding balance ৳${parseFloat(balance).toFixed(2)}?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Send SMS',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Sending SMS...',
                    text: 'Please wait while the SMS is being sent.',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                $.ajax({
                    url: `/customers/${customerId}/send-sms`,
                    type: 'POST',
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        Swal.fire({
                            icon: 'success',
                            title: 'SMS Sent!',
                            text: 'Reminder SMS has been sent successfully.',
                            timer: 2000,
                            showConfirmButton: false
                        });
                    },
                    error: function(xhr) {
                        let errorMessage = 'Failed to send SMS. Please try again.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        }
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: errorMessage
                        });
                    }
                });
            }
        });
    });

    $('#refresh-table, #refresh-btn').on('click', function() {
        $(this).find('i').addClass('fa-spin');
        table.ajax.reload(function() {
            $('.fa-spin').removeClass('fa-spin');
        });
    });

    window.filterByBalance = function(type) {
        $('#balance-filter').val(type);
        table.ajax.reload();
    };

    window.filterByActivity = function(period) {
        table.ajax.reload();
    };

    function updateStatistics(summary) {
        if (summary) {
            $('#total-customers').text(summary.total_customers ?? 0);
            $('#total-outstanding').text(parseFloat(summary.total_outstanding || 0).toFixed(2));
            $('#overdue-customers').text(summary.high_outstanding ?? 0);
            $('#active-customers').text(summary.active_customers ?? 0);
            return;
        }
        const info = table.page.info();
        $('#total-customers').text(info.recordsTotal);
        $('#total-outstanding').text('0.00');
        $('#overdue-customers').text('0');
        $('#active-customers').text('0');
    }

    $('#export-btn, #export-selected').on('click', function() {
        const isSelected = $(this).attr('id') === 'export-selected';
        let url = '/customers/export';
        if (isSelected) {
            const selectedIds = $('.customer-checkbox:checked').map(function() {
                return $(this).val();
            }).get();
            if (selectedIds.length === 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'No Selection',
                    text: 'Please select customers to export.'
                });
                return;
            }
            url += '?selected=' + selectedIds.join(',');
        }
        window.location.href = url;
    });

});
</script>
@stop
