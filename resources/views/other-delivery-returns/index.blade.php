@extends('layouts.modern-admin')

@section('title', 'Other Delivery Returns')

@section('page_title', 'Other Delivery Returns')

@section('header_actions')
    <div class="header-actions-group">
        @can('other-delivery-return-create')
            <a href="{{ route('other-delivery-returns.create') }}" class="btn modern-btn modern-btn-primary">
                <i class="fas fa-plus"></i> <span class="btn-text">New Return</span>
            </a>
        @endcan
        
        <div class="action-dropdown">
            <button type="button" class="btn modern-btn modern-btn-outline dropdown-toggle" data-toggle="dropdown">
                <i class="fas fa-ellipsis-v"></i> <span class="btn-text">More</span>
            </button>
            <div class="dropdown-menu dropdown-menu-right">
                <a class="dropdown-item" href="#" id="bulk-export">
                    <i class="fas fa-file-excel"></i> Export All
                </a>
                <a class="dropdown-item" href="#" id="print-all">
                    <i class="fas fa-print"></i> Print List
                </a>
            </div>
        </div>
    </div>
@stop

@section('page_content')
    <!-- Mobile-First Stats Dashboard -->
    <div class="stats-container">
        <div class="stats-grid">
            <div class="stat-card stat-pending">
                <div class="stat-content">
                    <div class="stat-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-details">
                        <div class="stat-number">{{ $returns->where('status', 'pending')->count() }}</div>
                        <div class="stat-label">Pending</div>
                        <div class="stat-change">Awaiting processing</div>
                    </div>
                </div>
            </div>
            
            <div class="stat-card stat-completed">
                <div class="stat-content">
                    <div class="stat-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-details">
                        <div class="stat-number">{{ $returns->where('status', 'completed')->count() }}</div>
                        <div class="stat-label">Completed</div>
                        <div class="stat-change">Successfully processed</div>
                    </div>
                </div>
            </div>
            
            <div class="stat-card stat-rejected">
                <div class="stat-content">
                    <div class="stat-icon">
                        <i class="fas fa-times-circle"></i>
                    </div>
                    <div class="stat-details">
                        <div class="stat-number">{{ $returns->where('status', 'rejected')->count() }}</div>
                        <div class="stat-label">Rejected</div>
                        <div class="stat-change">Rejected returns</div>
                    </div>
                </div>
            </div>
            
            <div class="stat-card stat-total">
                <div class="stat-content">
                    <div class="stat-icon">
                        <i class="fas fa-undo-alt"></i>
                    </div>
                    <div class="stat-details">
                        <div class="stat-number">{{ $returns->count() }}</div>
                        <div class="stat-label">Total Returns</div>
                        <div class="stat-change">All time</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Smart Search & Filter Bar -->
    <div class="search-filter-bar">
        <div class="search-section">
            <div class="search-input-wrapper">
                <i class="fas fa-search search-icon"></i>
                <input type="text" id="smart-search" class="search-input" placeholder="Search returns, returners, or status...">
                <button type="button" class="search-clear" id="clear-search">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
        
        <div class="filter-section">
            <button type="button" class="filter-toggle" id="filter-toggle">
                <i class="fas fa-filter"></i>
                <span class="filter-count" id="filter-count" style="display: none;">0</span>
            </button>
        </div>
    </div>

    <!-- Collapsible Advanced Filters -->
    <div class="advanced-filters" id="advanced-filters">
        <div class="filter-row">
            <div class="filter-group">
                <label class="filter-label">Date Range</label>
                <div class="date-range-inputs">
                    <input type="date" id="date-from" class="filter-input">
                    <span class="date-separator">to</span>
                    <input type="date" id="date-to" class="filter-input">
                </div>
            </div>
            
            <div class="filter-group">
                <label class="filter-label">Status</label>
                <select id="status-filter" class="filter-select">
                    <option value="">All Status</option>
                    <option value="pending">Pending</option>
                    <option value="completed">Completed</option>
                    <option value="rejected">Rejected</option>
                </select>
            </div>
            
            <div class="filter-group">
                <label class="filter-label">Returner</label>
                <input type="text" id="returner-filter" class="filter-input" placeholder="Filter by returner name">
            </div>
        </div>
        
        <div class="filter-actions">
            <button type="button" class="btn-filter btn-filter-apply" id="apply-filters">
                <i class="fas fa-search"></i> Apply
            </button>
            <button type="button" class="btn-filter btn-filter-reset" id="reset-filters">
                <i class="fas fa-undo"></i> Reset
            </button>
        </div>
    </div>

    <!-- Alert Messages -->
    @if(session('success'))
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i>
            <span>{{ session('success') }}</span>
            <button type="button" class="alert-close">&times;</button>
        </div>
    @endif
    
    @if(session('error'))
        <div class="alert alert-error">
            <i class="fas fa-exclamation-circle"></i>
            <span>{{ session('error') }}</span>
            <button type="button" class="alert-close">&times;</button>
        </div>
    @endif

    <!-- Returns List Container -->
    <div class="returns-container">
        <!-- Desktop Table View -->
        <div class="desktop-view">
            <div class="table-container">
                <div class="table-header">
                    <div class="table-title">
                        <h3><i class="fas fa-list"></i> Return Records</h3>
                        <span class="record-count">{{ $returns->count() }} returns</span>
                    </div>
                </div>
                
                <div class="table-wrapper">
                    <table class="modern-table" id="returns-table">
                        <thead>
                            <tr>
                                <th>Return Details</th>
                                <th>Returner Info</th>
                                <th>Status</th>
                                <th>Received By</th>
                                <th width="120">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($returns as $return)
                                <tr data-return-id="{{ $return->id }}" data-status="{{ $return->status }}">
                                    <td>
                                        <div class="return-details">
                                            <div class="return-number">{{ $return->return_number }}</div>
                                            <div class="return-date">
                                                {{ $return->return_date ? $return->return_date->format('M d, Y') : 'Pending' }}
                                                @if($return->return_date)
                                                    <span class="return-time">{{ $return->return_date->format('h:i A') }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="returner-info" data-returner="{{ $return->returner_name }}" 
                                             data-phone="{{ $return->returner_phone ?? 'N/A' }}"
                                             data-address="{{ $return->returner_address ?? 'N/A' }}">
                                            <div class="returner-name">{{ $return->returner_name }}</div>
                                            @if($return->returner_phone)
                                                <div class="returner-contact">
                                                    <i class="fas fa-phone"></i> {{ $return->returner_phone }}
                                                </div>
                                            @endif
                                            @if($return->returner_address)
                                                <div class="returner-address">
                                                    <i class="fas fa-map-marker-alt"></i> {{ Str::limit($return->returner_address, 40) }}
                                                </div>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <div class="status-wrapper" data-return-id="{{ $return->id }}" 
                                             data-current-status="{{ $return->status }}"
                                             data-return-number="{{ $return->return_number }}">
                                            <span class="status-badge status-{{ $return->status }}">
                                                @if($return->status == 'pending')
                                                    <i class="fas fa-clock"></i> Pending
                                                @elseif($return->status == 'completed')
                                                    <i class="fas fa-check-circle"></i> Completed
                                                @else
                                                    <i class="fas fa-times-circle"></i> Rejected
                                                @endif
                                            </span>
                                        </div>
                                    </td>
                                    <td>
                                        @if($return->receivedBy)
                                            <div class="received-by">
                                                <div class="staff-name">{{ $return->receivedBy->name }}</div>
                                                <div class="staff-role">Staff Member</div>
                                            </div>
                                        @else
                                            <span class="no-assignment">Not assigned</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <button type="button" class="btn-action btn-view" 
                                                    onclick="window.location.href='{{ route('other-delivery-returns.show', $return) }}'">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            
                                            @if($return->status != 'completed')
                                                @can('other-delivery-return-edit')
                                                    <button type="button" class="btn-action btn-edit"
                                                            onclick="window.location.href='{{ route('other-delivery-returns.edit', $return) }}'">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                @endcan
                                                
                                                @can('other-delivery-return-delete')
                                                    <button type="button" class="btn-action btn-delete delete-return" 
                                                            data-return-id="{{ $return->id }}"
                                                            data-return-number="{{ $return->return_number }}">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                @endcan
                                            @endif
                                            
                                            <div class="action-dropdown">
                                                <button type="button" class="btn-action btn-more" data-toggle="dropdown">
                                                    <i class="fas fa-ellipsis-v"></i>
                                                </button>
                                                <div class="dropdown-menu dropdown-menu-right">
                                                    @can('other-delivery-return-print')
                                                        <a class="dropdown-item" href="{{ route('other-delivery-returns.print', $return) }}" target="_blank">
                                                            <i class="fas fa-print"></i> Print Receipt
                                                        </a>
                                                    @endcan
                                                    <a class="dropdown-item returner-history" href="#" 
                                                       data-returner="{{ $return->returner_name }}">
                                                        <i class="fas fa-history"></i> Returner History
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Mobile Card View -->
        <div class="mobile-view">
            <div class="mobile-returns-list" id="mobile-returns">
                @foreach($returns as $return)
                    <div class="return-card" data-return-id="{{ $return->id }}" 
                         data-status="{{ $return->status }}"
                         data-returner="{{ $return->returner_name }}"
                         data-return-number="{{ $return->return_number }}">
                        
                        <div class="card-header">
                            <div class="return-info">
                                <div class="return-number">{{ $return->return_number }}</div>
                                <div class="return-date">
                                    {{ $return->return_date ? $return->return_date->format('M d, Y') : 'Pending' }}
                                </div>
                            </div>
                            <div class="status-container">
                                <span class="status-badge status-{{ $return->status }}" 
                                      data-return-id="{{ $return->id }}" 
                                      data-current-status="{{ $return->status }}"
                                      data-return-number="{{ $return->return_number }}">
                                    @if($return->status == 'pending')
                                        <i class="fas fa-clock"></i> Pending
                                    @elseif($return->status == 'completed')
                                        <i class="fas fa-check-circle"></i> Completed
                                    @else
                                        <i class="fas fa-times-circle"></i> Rejected
                                    @endif
                                </span>
                            </div>
                        </div>

                        <div class="card-body">
                            <div class="returner-section">
                                <div class="section-label">
                                    <i class="fas fa-user"></i> Returner
                                </div>
                                <div class="returner-details" data-returner="{{ $return->returner_name }}" 
                                     data-phone="{{ $return->returner_phone ?? 'N/A' }}"
                                     data-address="{{ $return->returner_address ?? 'N/A' }}">
                                    <div class="returner-name">{{ $return->returner_name }}</div>
                                    @if($return->returner_phone)
                                        <div class="returner-contact">
                                            <i class="fas fa-phone"></i> {{ $return->returner_phone }}
                                        </div>
                                    @endif
                                    @if($return->returner_address)
                                        <div class="returner-address">
                                            <i class="fas fa-map-marker-alt"></i> {{ Str::limit($return->returner_address, 50) }}
                                        </div>
                                    @endif
                                </div>
                            </div>

                            @if($return->receivedBy)
                                <div class="received-section">
                                    <div class="section-label">
                                        <i class="fas fa-user-check"></i> Received By
                                    </div>
                                    <div class="staff-info">{{ $return->receivedBy->name }}</div>
                                </div>
                            @endif
                        </div>

                        <div class="card-actions">
                            <button type="button" class="btn-card-action btn-primary" 
                                    onclick="window.location.href='{{ route('other-delivery-returns.show', $return) }}'">
                                <i class="fas fa-eye"></i> View
                            </button>
                            
                            <button type="button" class="btn-card-action btn-secondary returner-history"
                                    data-returner="{{ $return->returner_name }}">
                                <i class="fas fa-history"></i> History
                            </button>
                            
                            <div class="action-dropdown">
                                <button type="button" class="btn-card-action btn-outline" data-toggle="dropdown">
                                    <i class="fas fa-ellipsis-h"></i>
                                </button>
                                <div class="dropdown-menu dropdown-menu-right">
                                    @if($return->status != 'completed')
                                        @can('other-delivery-return-edit')
                                            <a class="dropdown-item" href="{{ route('other-delivery-returns.edit', $return) }}">
                                                <i class="fas fa-edit"></i> Edit Return
                                            </a>
                                        @endcan
                                        
                                        @can('other-delivery-return-delete')
                                            <a class="dropdown-item text-danger delete-return" href="#"
                                               data-return-id="{{ $return->id }}"
                                               data-return-number="{{ $return->return_number }}">
                                                <i class="fas fa-trash"></i> Delete Return
                                            </a>
                                        @endcan
                                        <div class="dropdown-divider"></div>
                                    @endif
                                    
                                    @can('other-delivery-return-print')
                                        <a class="dropdown-item" href="{{ route('other-delivery-returns.print', $return) }}" target="_blank">
                                            <i class="fas fa-print"></i> Print Receipt
                                        </a>
                                    @endcan
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Floating Action Button for Mobile -->
    @can('other-delivery-return-create')
        <div class="fab-container">
            <a href="{{ route('other-delivery-returns.create') }}" class="fab">
                <i class="fas fa-plus"></i>
            </a>
        </div>
    @endcan

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-exclamation-triangle text-warning"></i>
                        Confirm Deletion
                    </h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p><strong>Are you sure you want to delete return <span id="delete-return-number" class="text-primary"></span>?</strong></p>
                    <p class="text-muted">This action cannot be undone and will permanently remove the return record and reverse all inventory adjustments.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <form id="delete-form" method="POST" style="display: inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-trash"></i> Delete Return
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Status Update Modal -->
    <div class="modal fade" id="statusModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-edit text-primary"></i>
                        Update Return Status
                    </h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="status-info">
                        <p><strong>Return:</strong> <span id="status-return-number"></span></p>
                        <p><strong>Current Status:</strong> <span id="current-status-display"></span></p>
                    </div>
                    
                    <div class="form-group">
                        <label for="new-status">New Status</label>
                        <select id="new-status" class="form-control">
                            <option value="pending">Pending</option>
                            <option value="completed">Completed</option>
                            <option value="rejected">Rejected</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="status-notes">Notes (Optional)</label>
                        <textarea id="status-notes" class="form-control" rows="3" 
                                  placeholder="Add any notes about this status change..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="update-status-btn">
                        <i class="fas fa-save"></i> Update Status
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Returner History Modal -->
    <div class="modal fade" id="returnerHistoryModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-history text-info"></i>
                        Returner History
                    </h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="returner-profile">
                        <h6><strong>Name:</strong> <span id="history-returner-name"></span></h6>
                        <p><strong>Phone:</strong> <span id="history-returner-phone"></span></p>
                        <p><strong>Address:</strong> <span id="history-returner-address"></span></p>
                    </div>
                    
                    <div class="history-loading text-center" style="padding: 20px;">
                        <i class="fas fa-spinner fa-spin fa-2x text-primary"></i>
                        <p class="mt-2">Loading return history...</p>
                    </div>
                    
                    <div class="history-content" style="display: none;">
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Return #</th>
                                        <th>Date</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="history-table-body">
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <div class="no-history text-center" style="display: none; padding: 20px;">
                        <i class="fas fa-inbox fa-2x text-muted"></i>
                        <p class="mt-2">No return history found for this returner.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('additional_css')
<style>
:root {
    --primary: #6366f1;
    --primary-dark: #4f46e5;
    --success: #10b981;
    --warning: #f59e0b;
    --danger: #ef4444;
    --info: #06b6d4;
    --gray-50: #f9fafb;
    --gray-100: #f3f4f6;
    --gray-200: #e5e7eb;
    --gray-300: #d1d5db;
    --gray-400: #9ca3af;
    --gray-500: #6b7280;
    --gray-600: #4b5563;
    --gray-700: #374151;
    --gray-800: #1f2937;
    --gray-900: #111827;
    --border-radius: 12px;
    --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
    --shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
    --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
    --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

/* Stats Container */
.stats-container {
    margin-bottom: 24px;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 16px;
}

@media (min-width: 768px) {
    .stats-grid {
        grid-template-columns: repeat(4, 1fr);
    }
}

.stat-card {
    background: white;
    border-radius: var(--border-radius);
    padding: 20px;
    box-shadow: var(--shadow);
    transition: var(--transition);
    position: relative;
    overflow: hidden;
}

.stat-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: var(--gray-200);
}

.stat-pending::before { background: var(--warning); }
.stat-completed::before { background: var(--success); }
.stat-rejected::before { background: var(--danger); }
.stat-total::before { background: var(--primary); }

.stat-card:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-lg);
}

.stat-content {
    display: flex;
    align-items: center;
    gap: 16px;
}

.stat-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    color: white;
    flex-shrink: 0;
}

.stat-pending .stat-icon { background: var(--warning); }
.stat-completed .stat-icon { background: var(--success); }
.stat-rejected .stat-icon { background: var(--danger); }
.stat-total .stat-icon { background: var(--primary); }

.stat-details {
    flex: 1;
}

.stat-number {
    font-size: 28px;
    font-weight: 700;
    color: var(--gray-900);
    line-height: 1;
    margin-bottom: 4px;
}

.stat-label {
    font-size: 14px;
    font-weight: 600;
    color: var(--gray-600);
    margin-bottom: 4px;
}

.stat-change {
    font-size: 12px;
    color: var(--gray-500);
}

/* Search & Filter Bar */
.search-filter-bar {
    display: flex;
    gap: 12px;
    margin-bottom: 16px;
    align-items: center;
}

.search-section {
    flex: 1;
}

.search-input-wrapper {
    position: relative;
    display: flex;
    align-items: center;
}

.search-icon {
    position: absolute;
    left: 16px;
    color: var(--gray-400);
    z-index: 2;
}

.search-input {
    width: 100%;
    padding: 12px 16px 12px 48px;
    border: 2px solid var(--gray-200);
    border-radius: var(--border-radius);
    font-size: 16px;
    transition: var(--transition);
    background: white;
}

.search-input:focus {
    outline: none;
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
}

.search-clear {
    position: absolute;
    right: 12px;
    background: none;
    border: none;
    color: var(--gray-400);
    cursor: pointer;
    padding: 4px;
    border-radius: 4px;
    display: none;
}

.search-clear:hover {
    color: var(--gray-600);
    background: var(--gray-100);
}

.filter-section {
    position: relative;
}

.filter-toggle {
    width: 48px;
    height: 48px;
    border: 2px solid var(--gray-200);
    border-radius: var(--border-radius);
    background: white;
    color: var(--gray-600);
    cursor: pointer;
    transition: var(--transition);
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
}

.filter-toggle:hover {
    border-color: var(--primary);
    color: var(--primary);
}

.filter-toggle.active {
    border-color: var(--primary);
    background: var(--primary);
    color: white;
}

.filter-count {
    position: absolute;
    top: -8px;
    right: -8px;
    background: var(--danger);
    color: white;
    border-radius: 50%;
    width: 20px;
    height: 20px;
    font-size: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
}

/* Advanced Filters */
.advanced-filters {
    background: white;
    border-radius: var(--border-radius);
    padding: 20px;
    margin-bottom: 20px;
    box-shadow: var(--shadow);
    display: none;
}

.filter-row {
    display: grid;
    grid-template-columns: 1fr;
    gap: 16px;
    margin-bottom: 20px;
}

@media (min-width: 768px) {
    .filter-row {
        grid-template-columns: repeat(3, 1fr);
    }
}

.filter-group {
    display: flex;
    flex-direction: column;
}

.filter-label {
    font-size: 14px;
    font-weight: 600;
    color: var(--gray-700);
    margin-bottom: 8px;
}

.filter-input,
.filter-select {
    padding: 10px 12px;
    border: 2px solid var(--gray-200);
    border-radius: 8px;
    font-size: 14px;
    transition: var(--transition);
}

.filter-input:focus,
.filter-select:focus {
    outline: none;
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
}

.date-range-inputs {
    display: flex;
    align-items: center;
    gap: 8px;
}

.date-separator {
    font-size: 14px;
    color: var(--gray-500);
    font-weight: 500;
}

.filter-actions {
    display: flex;
    gap: 12px;
}

.btn-filter {
    padding: 10px 20px;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: var(--transition);
    border: none;
    display: flex;
    align-items: center;
    gap: 8px;
}

.btn-filter-apply {
    background: var(--primary);
    color: white;
}

.btn-filter-apply:hover {
    background: var(--primary-dark);
}

.btn-filter-reset {
    background: var(--gray-100);
    color: var(--gray-700);
}

.btn-filter-reset:hover {
    background: var(--gray-200);
}

/* Alert Messages */
.alert {
    padding: 16px 20px;
    border-radius: var(--border-radius);
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 12px;
    position: relative;
}

.alert-success {
    background: #d1fae5;
    color: #065f46;
    border: 1px solid #a7f3d0;
}

.alert-error {
    background: #fee2e2;
    color: #991b1b;
    border: 1px solid #fecaca;
}

.alert-close {
    position: absolute;
    right: 16px;
    background: none;
    border: none;
    font-size: 18px;
    cursor: pointer;
    opacity: 0.7;
}

.alert-close:hover {
    opacity: 1;
}

/* Desktop Table View */
.desktop-view {
    display: none;
}

@media (min-width: 1024px) {
    .desktop-view {
        display: block;
    }
    .mobile-view {
        display: none;
    }
}

.table-container {
    background: white;
    border-radius: var(--border-radius);
    box-shadow: var(--shadow);
    overflow: hidden;
}

.table-header {
    padding: 20px;
    border-bottom: 1px solid var(--gray-200);
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.table-title h3 {
    margin: 0;
    font-size: 18px;
    font-weight: 600;
    color: var(--gray-900);
    display: flex;
    align-items: center;
    gap: 8px;
}

.record-count {
    font-size: 14px;
    color: var(--gray-500);
    margin-left: 8px;
}

.table-wrapper {
    overflow-x: auto;
}

.modern-table {
    width: 100%;
    border-collapse: collapse;
}

.modern-table th {
    background: var(--gray-50);
    padding: 16px;
    text-align: left;
    font-weight: 600;
    color: var(--gray-700);
    border-bottom: 1px solid var(--gray-200);
    font-size: 14px;
}

.modern-table td {
    padding: 16px;
    border-bottom: 1px solid var(--gray-100);
    vertical-align: top;
}

.modern-table tr:hover {
    background: var(--gray-50);
}

/* Table Cell Styles */
.return-details {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.return-number {
    font-weight: 600;
    color: var(--primary);
    font-family: 'Monaco', 'Menlo', monospace;
}

.return-date {
    font-size: 14px;
    color: var(--gray-600);
}

.return-time {
    font-size: 12px;
    color: var(--gray-500);
    margin-left: 8px;
}

.returner-info {
    cursor: pointer;
    transition: var(--transition);
}

.returner-info:hover {
    background: var(--gray-50);
    border-radius: 6px;
    padding: 4px;
    margin: -4px;
}

.returner-name {
    font-weight: 600;
    color: var(--gray-900);
    margin-bottom: 4px;
}

.returner-contact,
.returner-address {
    font-size: 13px;
    color: var(--gray-600);
    display: flex;
    align-items: center;
    gap: 6px;
    margin-bottom: 2px;
}

.status-wrapper {
    cursor: pointer;
}

.status-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 13px;
    font-weight: 600;
    transition: var(--transition);
}

.status-pending {
    background: #fef3c7;
    color: #92400e;
}

.status-completed {
    background: #d1fae5;
    color: #065f46;
}

.status-rejected {
    background: #fee2e2;
    color: #991b1b;
}

.status-badge:hover {
    transform: scale(1.05);
}

.received-by {
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.staff-name {
    font-weight: 600;
    color: var(--gray-900);
}

.staff-role {
    font-size: 12px;
    color: var(--gray-500);
}

.no-assignment {
    color: var(--gray-400);
    font-style: italic;
}

/* Action Buttons */
.action-buttons {
    display: flex;
    gap: 4px;
    align-items: center;
}

.btn-action {
    width: 32px;
    height: 32px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    transition: var(--transition);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
}

.btn-view {
    background: var(--info);
    color: white;
}

.btn-view:hover {
    background: #0891b2;
}

.btn-edit {
    background: var(--warning);
    color: white;
}

.btn-edit:hover {
    background: #d97706;
}

.btn-delete {
    background: var(--danger);
    color: white;
}

.btn-delete:hover {
    background: #dc2626;
}

.btn-more {
    background: var(--gray-100);
    color: var(--gray-600);
}

.btn-more:hover {
    background: var(--gray-200);
}

/* Mobile Card View */
.mobile-view {
    display: block;
}

@media (min-width: 1024px) {
    .mobile-view {
        display: none;
    }
}

.mobile-returns-list {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.return-card {
    background: white;
    border-radius: var(--border-radius);
    box-shadow: var(--shadow);
    overflow: hidden;
    transition: var(--transition);
    border-left: 4px solid var(--gray-200);
}

.return-card:hover {
    box-shadow: var(--shadow-md);
    transform: translateY(-1px);
}

.return-card[data-status="pending"] {
    border-left-color: var(--warning);
}

.return-card[data-status="completed"] {
    border-left-color: var(--success);
}

.return-card[data-status="rejected"] {
    border-left-color: var(--danger);
}

.card-header {
    padding: 16px 20px;
    border-bottom: 1px solid var(--gray-100);
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.return-info {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.card-body {
    padding: 16px 20px;
}

.returner-section,
.received-section {
    margin-bottom: 16px;
}

.returner-section:last-child,
.received-section:last-child {
    margin-bottom: 0;
}

.section-label {
    font-size: 12px;
    font-weight: 600;
    color: var(--gray-500);
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 8px;
    display: flex;
    align-items: center;
    gap: 6px;
}

.returner-details {
    cursor: pointer;
    padding: 8px;
    border-radius: 8px;
    transition: var(--transition);
}

.returner-details:hover {
    background: var(--gray-50);
}

.staff-info {
    font-weight: 600;
    color: var(--gray-900);
}

.card-actions {
    padding: 16px 20px;
    border-top: 1px solid var(--gray-100);
    display: flex;
    gap: 12px;
    align-items: center;
}

.btn-card-action {
    padding: 10px 16px;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: var(--transition);
    border: none;
    display: flex;
    align-items: center;
    gap: 6px;
    text-decoration: none;
}

.btn-primary {
    background: var(--primary);
    color: white;
}

.btn-primary:hover {
    background: var(--primary-dark);
    color: white;
}

.btn-secondary {
    background: var(--gray-100);
    color: var(--gray-700);
}

.btn-secondary:hover {
    background: var(--gray-200);
    color: var(--gray-700);
}

.btn-outline {
    background: transparent;
    color: var(--gray-600);
    border: 1px solid var(--gray-300);
}

.btn-outline:hover {
    background: var(--gray-50);
    color: var(--gray-700);
}

/* Floating Action Button */
.fab-container {
    position: fixed;
    bottom: 24px;
    right: 24px;
    z-index: 1000;
}

@media (min-width: 1024px) {
    .fab-container {
        display: none;
    }
}

.fab {
    width: 56px;
    height: 56px;
    background: var(--primary);
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    box-shadow: var(--shadow-lg);
    transition: var(--transition);
    text-decoration: none;
}

.fab:hover {
    background: var(--primary-dark);
    transform: scale(1.1);
    color: white;
}

/* Dropdown Menus */
.dropdown-menu {
    border: none;
    box-shadow: var(--shadow-lg);
    border-radius: 8px;
    padding: 8px 0;
    margin-top: 4px;
}

.dropdown-item {
    padding: 10px 16px;
    font-size: 14px;
    display: flex;
    align-items: center;
    gap: 8px;
    transition: var(--transition);
}

.dropdown-item:hover {
    background: var(--gray-50);
    color: var(--gray-900);
}

.dropdown-item.text-danger:hover {
    background: #fee2e2;
    color: var(--danger);
}

/* Modal Enhancements */
.modal-content {
    border: none;
    border-radius: var(--border-radius);
    box-shadow: var(--shadow-lg);
}

.modal-header {
    border-bottom: 1px solid var(--gray-200);
    padding: 20px;
}

.modal-title {
    display: flex;
    align-items: center;
    gap: 8px;
    font-weight: 600;
}

.modal-body {
    padding: 20px;
}

.modal-footer {
    border-top: 1px solid var(--gray-200);
    padding: 20px;
    display: flex;
    gap: 12px;
    justify-content: flex-end;
}

/* Form Controls */
.form-control {
    padding: 10px 12px;
    border: 2px solid var(--gray-200);
    border-radius: 8px;
    font-size: 14px;
    transition: var(--transition);
    width: 100%;
}

.form-control:focus {
    outline: none;
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
}

.form-group {
    margin-bottom: 16px;
}

.form-group label {
    font-size: 14px;
    font-weight: 600;
    color: var(--gray-700);
    margin-bottom: 6px;
    display: block;
}

/* Button Styles */
.btn {
    padding: 10px 20px;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: var(--transition);
    border: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    text-decoration: none;
}

.btn-primary {
    background: var(--primary);
    color: white;
}

.btn-primary:hover {
    background: var(--primary-dark);
    color: white;
}

.btn-secondary {
    background: var(--gray-500);
    color: white;
}

.btn-secondary:hover {
    background: var(--gray-600);
    color: white;
}

.btn-danger {
    background: var(--danger);
    color: white;
}

.btn-danger:hover {
    background: #dc2626;
    color: white;
}

/* Responsive Adjustments */
@media (max-width: 640px) {
    .stats-grid {
        grid-template-columns: 1fr;
        gap: 12px;
    }
    
    .search-filter-bar {
        flex-direction: column;
        gap: 12px;
    }
    
    .filter-section {
        align-self: flex-end;
    }
    
    .card-actions {
        flex-direction: column;
        gap: 8px;
    }
    
    .btn-card-action {
        width: 100%;
        justify-content: center;
    }
    
    .action-dropdown {
        width: 100%;
    }
    
    .fab-container {
        bottom: 16px;
        right: 16px;
    }
}

/* Animation Classes */
.fade-in {
    animation: fadeIn 0.3s ease-in-out;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.slide-down {
    animation: slideDown 0.3s ease-in-out;
}

@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>
@stop

@section('additional_js')
<script>
$(document).ready(function() {
    let currentFilters = {};
    let isLoading = false;
    
    // Initialize
    initializePage();
    
    function initializePage() {
        // Set default date range (current month)
        const today = new Date();
        const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
        const lastDay = new Date(today.getFullYear(), today.getMonth() + 1, 0);
        
        $('#date-from').val(formatDateForInput(firstDay));
        $('#date-to').val(formatDateForInput(lastDay));
        
        // Auto-hide alerts
        setTimeout(() => {
            $('.alert').fadeOut();
        }, 5000);
    }
    
    function isMobile() {
        return window.innerWidth < 1024;
    }
    
    function formatDateForInput(date) {
        return date.toISOString().split('T')[0];
    }
    
    // Smart Search Functionality
    $('#smart-search').on('input', function() {
        const searchTerm = $(this).val().toLowerCase().trim();
        const clearBtn = $('#clear-search');
        
        if (searchTerm.length > 0) {
            clearBtn.show();
            performSearch(searchTerm);
        } else {
            clearBtn.hide();
            resetSearch();
        }
    });
    
    $('#clear-search').on('click', function() {
        $('#smart-search').val('');
        $(this).hide();
        resetSearch();
    });
    
    function performSearch(searchTerm) {
        if (isMobile()) {
            $('.return-card').each(function() {
                const card = $(this);
                const returnNumber = card.data('return-number').toString().toLowerCase();
                const returner = card.data('returner').toString().toLowerCase();
                const status = card.data('status').toString().toLowerCase();
                
                if (returnNumber.includes(searchTerm) || 
                    returner.includes(searchTerm) || 
                    status.includes(searchTerm)) {
                    card.show().addClass('fade-in');
                } else {
                    card.hide();
                }
            });
        } else {
            // Desktop table search
            $('#returns-table tbody tr').each(function() {
                const row = $(this);
                const text = row.text().toLowerCase();
                
                if (text.includes(searchTerm)) {
                    row.show();
                } else {
                    row.hide();
                }
            });
        }
    }
    
    function resetSearch() {
        if (isMobile()) {
            $('.return-card').show().addClass('fade-in');
        } else {
            $('#returns-table tbody tr').show();
        }
    }
    
    // Filter Toggle
    $('#filter-toggle').on('click', function() {
        const filtersPanel = $('#advanced-filters');
        const button = $(this);
        
        if (filtersPanel.is(':visible')) {
            filtersPanel.slideUp(300);
            button.removeClass('active');
        } else {
            filtersPanel.slideDown(300).addClass('slide-down');
            button.addClass('active');
        }
    });
    
    // Apply Filters
    $('#apply-filters').on('click', function() {
        if (isLoading) return;
        
        const filters = {
            dateFrom: $('#date-from').val(),
            dateTo: $('#date-to').val(),
            status: $('#status-filter').val(),
            returner: $('#returner-filter').val().toLowerCase()
        };
        
        applyFilters(filters);
        updateFilterCount(filters);
    });
    
    // Reset Filters
    $('#reset-filters').on('click', function() {
        $('#date-from').val('');
        $('#date-to').val('');
        $('#status-filter').val('');
        $('#returner-filter').val('');
        $('#smart-search').val('');
        $('#clear-search').hide();
        
        resetSearch();
        updateFilterCount({});
    });
    
    function applyFilters(filters) {
        currentFilters = filters;
        
        if (isMobile()) {
            $('.return-card').each(function() {
                const card = $(this);
                let show = true;
                
                // Status filter
                if (filters.status && card.data('status') !== filters.status) {
                    show = false;
                }
                
                // Returner filter
                if (filters.returner && !card.data('returner').toString().toLowerCase().includes(filters.returner)) {
                    show = false;
                }
                
                if (show) {
                    card.show().addClass('fade-in');
                } else {
                    card.hide();
                }
            });
        } else {
            // Desktop table filtering
            $('#returns-table tbody tr').each(function() {
                const row = $(this);
                let show = true;
                
                // Status filter
                if (filters.status && row.data('status') !== filters.status) {
                    show = false;
                }
                
                // Returner filter
                if (filters.returner) {
                    const returnerText = row.find('.returner-name').text().toLowerCase();
                    if (!returnerText.includes(filters.returner)) {
                        show = false;
                    }
                }
                
                if (show) {
                    row.show();
                } else {
                    row.hide();
                }
            });
        }
    }
    
    function updateFilterCount(filters) {
        const count = Object.values(filters).filter(val => val && val.length > 0).length;
        const filterCount = $('#filter-count');
        const filterToggle = $('#filter-toggle');
        
        if (count > 0) {
            filterCount.text(count).show();
            filterToggle.addClass('active');
        } else {
            filterCount.hide();
            filterToggle.removeClass('active');
        }
    }
    
    // Status Update Modal
    $(document).on('click', '.status-badge', function() {
        const returnId = $(this).data('return-id');
        const currentStatus = $(this).data('current-status');
        const returnNumber = $(this).data('return-number');
        
        $('#status-return-number').text(returnNumber);
        $('#current-status-display').html(getStatusBadgeHtml(currentStatus));
        $('#new-status').val(currentStatus);
        $('#status-notes').val('');
        
        $('#update-status-btn').data('return-id', returnId);
        $('#statusModal').modal('show');
    });
    
    // Update Status
    $('#update-status-btn').on('click', function() {
        const returnId = $(this).data('return-id');
        const newStatus = $('#new-status').val();
        const notes = $('#status-notes').val();
        
        if (isLoading) return;
        
        isLoading = true;
        $(this).addClass('loading').html('<span class="spinner"></span> Updating...');
        
        $.ajax({
            url: `/other-delivery-returns/${returnId}/update-status`,
            method: 'PUT',
            data: {
                status: newStatus,
                notes: notes,
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    showAlert('Status updated successfully!', 'success');
                    updateStatusInUI(returnId, newStatus);
                    $('#statusModal').modal('hide');
                } else {
                    showAlert('Failed to update status. Please try again.', 'error');
                }
            },
            error: function(xhr) {
                console.error('Status update failed:', xhr.responseText);
                showAlert('Failed to update status. Please try again.', 'error');
            },
            complete: function() {
                isLoading = false;
                $('#update-status-btn').removeClass('loading').html('<i class="fas fa-save"></i> Update Status');
            }
        });
    });
    
    function updateStatusInUI(returnId, newStatus) {
        // Update desktop table
        $(`.modern-table tr[data-return-id="${returnId}"] .status-badge`).removeClass().addClass(`status-badge status-${newStatus}`).html(getStatusBadgeHtml(newStatus));
        
        // Update mobile cards
        $(`.return-card[data-return-id="${returnId}"] .status-badge`).removeClass().addClass(`status-badge status-${newStatus}`).html(getStatusBadgeHtml(newStatus));
        $(`.return-card[data-return-id="${returnId}"]`).attr('data-status', newStatus);
        
        // Update stats
        updateStatsDisplay();
    }
    
    function getStatusBadgeHtml(status) {
        const statusConfig = {
            pending: { icon: 'fas fa-clock', text: 'Pending' },
            completed: { icon: 'fas fa-check-circle', text: 'Completed' },
            rejected: { icon: 'fas fa-times-circle', text: 'Rejected' }
        };
        
        const config = statusConfig[status] || { icon: 'fas fa-question', text: status };
        return `<i class="${config.icon}"></i> ${config.text}`;
    }
    
    function showAlert(message, type) {
        const alertClass = type === 'success' ? 'alert-success' : 'alert-error';
        const icon = type === 'success' ? 'fas fa-check-circle' : 'fas fa-exclamation-circle';
        
        const alertHtml = `
            <div class="alert ${alertClass}">
                <i class="${icon}"></i>
                <span>${message}</span>
                <button type="button" class="alert-close">&times;</button>
            </div>
        `;
        
        $('.returns-container').prepend(alertHtml);
        
        setTimeout(() => {
            $('.alert').fadeOut();
        }, 5000);
    }
    
    function updateStatsDisplay() {
        // This would require a server call to get updated counts
        // For now, we'll just reload the page
        setTimeout(() => {
            location.reload();
        }, 1000);
    }
    
    // Delete return modal
    $('.delete-return').on('click', function() {
        const returnId = $(this).data('return-id');
        const returnNumber = $(this).data('return-number');
        
        $('#delete-return-number').text(returnNumber);
        $('#delete-form').attr('action', `/other-delivery-returns/${returnId}`);
        $('#deleteModal').modal('show');
    });
    
    // Returner history functionality
    $(document).on('click', '.returner-info, .returner-details, .returner-history', function() {
        const returnerName = $(this).data('returner');
        const returnerPhone = $(this).data('phone') || 'N/A';
        const returnerAddress = $(this).data('address') || 'N/A';
        
        $('#history-returner-name').text(returnerName);
        $('#history-returner-phone').text(returnerPhone);
        $('#history-returner-address').text(returnerAddress);
        
        $('.history-loading').show();
        $('.history-content').hide();
        $('.no-history').hide();
        
        $('#returnerHistoryModal').modal('show');
        
        // Simulate loading returner history
        setTimeout(() => {
            $('.history-loading').hide();
            $('.no-history').show();
        }, 1500);
    });
    
    // Alert close functionality
    $(document).on('click', '.alert-close', function() {
        $(this).closest('.alert').fadeOut();
    });
    
    // Keyboard shortcuts
    $(document).on('keydown', function(e) {
        // Ctrl/Cmd + F for search
        if ((e.ctrlKey || e.metaKey) && e.keyCode === 70) {
            e.preventDefault();
            $('#smart-search').focus();
        }
        
        // Escape to close modals
        if (e.keyCode === 27) {
            $('.modal').modal('hide');
        }
    });
});
</script>
@stop
