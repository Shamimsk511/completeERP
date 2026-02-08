@extends('layouts.modern-admin')

@section('title', 'Return Details - ' . $otherDeliveryReturn->return_number)

@section('page_title')
    <div class="page-title-container">
        <div class="title-main">
            <i class="fas fa-undo-alt"></i>
            Return Details
        </div>
        <div class="title-sub">{{ $otherDeliveryReturn->return_number }}</div>
    </div>
@stop

@section('header_actions')
    <div class="header-actions-group">
        @if($otherDeliveryReturn->status !== 'completed')
            @can('other-delivery-return-edit')
                <a href="{{ route('other-delivery-returns.edit', $otherDeliveryReturn) }}" class="btn modern-btn modern-btn-primary">
                    <i class="fas fa-edit"></i> <span class="btn-text">Edit Return</span>
                </a>
            @endcan
        @endif
        
        @can('other-delivery-return-print')
            <a href="{{ route('other-delivery-returns.print', $otherDeliveryReturn) }}" 
               class="btn modern-btn modern-btn-secondary" target="_blank">
                <i class="fas fa-print"></i> <span class="btn-text">Print</span>
            </a>
        @endcan
        
        <div class="action-dropdown">
            <button type="button" class="btn modern-btn modern-btn-outline dropdown-toggle" data-toggle="dropdown">
                <i class="fas fa-ellipsis-v"></i> <span class="btn-text">More</span>
            </button>
            <div class="dropdown-menu dropdown-menu-right">
                <a class="dropdown-item" href="{{ route('other-delivery-returns.index') }}">
                    <i class="fas fa-arrow-left"></i> Back to List
                </a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item returner-history" href="#" 
                   data-returner="{{ $otherDeliveryReturn->returner_name }}">
                    <i class="fas fa-history"></i> Returner History
                </a>
                @if($otherDeliveryReturn->status !== 'completed')
                    @can('other-delivery-return-delete')
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item text-danger delete-return" href="#"
                           data-return-id="{{ $otherDeliveryReturn->id }}"
                           data-return-number="{{ $otherDeliveryReturn->return_number }}">
                            <i class="fas fa-trash"></i> Delete Return
                        </a>
                    @endcan
                @endif
            </div>
        </div>
    </div>
@stop

@section('page_content')
    <!-- Status Alert Bar -->
    <div class="status-alert-bar">
        <div class="status-indicator status-{{ $otherDeliveryReturn->status }}">
            <div class="status-icon">
                @if($otherDeliveryReturn->status == 'pending')
                    <i class="fas fa-clock"></i>
                @elseif($otherDeliveryReturn->status == 'completed')
                    <i class="fas fa-check-circle"></i>
                @else
                    <i class="fas fa-times-circle"></i>
                @endif
            </div>
            <div class="status-content">
                <div class="status-label">Current Status</div>
                <div class="status-text">
                    {{ ucfirst($otherDeliveryReturn->status) }}
                    @if($otherDeliveryReturn->status !== 'completed')
                        <button type="button" class="status-change-btn" 
                                data-return-id="{{ $otherDeliveryReturn->id }}" 
                                data-current-status="{{ $otherDeliveryReturn->status }}"
                                data-return-number="{{ $otherDeliveryReturn->return_number }}">
                            <i class="fas fa-edit"></i> Change
                        </button>
                    @endif
                </div>
            </div>
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

    <!-- Mobile-First Information Cards -->
    <div class="info-grid">
        <!-- Return Information Card -->
        <div class="info-card return-info-card">
            <div class="card-header">
                <div class="card-title">
                    <i class="fas fa-file-alt"></i>
                    Return Information
                </div>
            </div>
            <div class="card-body">
                <div class="info-row">
                    <div class="info-label">Return Number</div>
                    <div class="info-value return-number">{{ $otherDeliveryReturn->return_number }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Return Date</div>
                    <div class="info-value">
                        {{ $otherDeliveryReturn->return_date ? $otherDeliveryReturn->return_date->format('d M Y') : 'Pending' }}
                        @if($otherDeliveryReturn->return_date)
                            <span class="info-time">{{ $otherDeliveryReturn->return_date->format('h:i A') }}</span>
                        @endif
                    </div>
                </div>
                <div class="info-row">
                    <div class="info-label">Received By</div>
                    <div class="info-value">
                        @if($otherDeliveryReturn->receivedBy)
                            <div class="staff-info">
                                <i class="fas fa-user"></i>
                                {{ $otherDeliveryReturn->receivedBy->name }}
                                <span class="staff-role">Staff Member</span>
                            </div>
                        @else
                            <span class="no-data">Not assigned</span>
                        @endif
                    </div>
                </div>
                @if($otherDeliveryReturn->notes)
                    <div class="info-row">
                        <div class="info-label">Notes</div>
                        <div class="info-value notes-text">{{ $otherDeliveryReturn->notes }}</div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Returner Information Card -->
        <div class="info-card returner-info-card">
            <div class="card-header">
                <div class="card-title">
                    <i class="fas fa-user"></i>
                    Returner Information
                </div>
                <button type="button" class="card-action returner-history" 
                        data-returner="{{ $otherDeliveryReturn->returner_name }}">
                    <i class="fas fa-history"></i>
                </button>
            </div>
            <div class="card-body">
                <div class="info-row">
                    <div class="info-label">Name</div>
                    <div class="info-value returner-name">{{ $otherDeliveryReturn->returner_name }}</div>
                </div>
                @if($otherDeliveryReturn->returner_phone)
                    <div class="info-row">
                        <div class="info-label">Phone</div>
                        <div class="info-value phone-number">
                            <a href="tel:{{ $otherDeliveryReturn->returner_phone }}">
                                <i class="fas fa-phone"></i>
                                {{ $otherDeliveryReturn->returner_phone }}
                            </a>
                        </div>
                    </div>
                @endif
                <div class="info-row">
                    <div class="info-label">Address</div>
                    <div class="info-value address-text">
                        <i class="fas fa-map-marker-alt"></i>
                        {{ $otherDeliveryReturn->returner_address }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Return Summary Stats -->
    <div class="summary-stats">
        <div class="stat-item">
            <div class="stat-icon">
                <i class="fas fa-boxes"></i>
            </div>
            <div class="stat-content">
                <div class="stat-number">{{ $otherDeliveryReturn->items->count() }}</div>
                <div class="stat-label">Product Types</div>
            </div>
        </div>
        <div class="stat-item">
            <div class="stat-icon">
                <i class="fas fa-calculator"></i>
            </div>
            <div class="stat-content">
                <div class="stat-number">{{ number_format($otherDeliveryReturn->items->sum('quantity'), 2) }}</div>
                <div class="stat-label">Total Quantity</div>
            </div>
        </div>
        @if($otherDeliveryReturn->items->sum('cartons') > 0)
            <div class="stat-item">
                <div class="stat-icon">
                    <i class="fas fa-archive"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-number">{{ $otherDeliveryReturn->items->sum('cartons') }}</div>
                    <div class="stat-label">Total Cartons</div>
                </div>
            </div>
        @endif
        @if($otherDeliveryReturn->items->sum('pieces') > 0)
            <div class="stat-item">
                <div class="stat-icon">
                    <i class="fas fa-cubes"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-number">{{ $otherDeliveryReturn->items->sum('pieces') }}</div>
                    <div class="stat-label">Total Pieces</div>
                </div>
            </div>
        @endif
    </div>

    <!-- Returned Products Section -->
    <div class="products-section">
        <div class="section-header">
            <div class="section-title">
                <i class="fas fa-list"></i>
                Returned Products
            </div>
            <div class="section-actions">
                <button type="button" class="view-toggle" id="view-toggle">
                    <i class="fas fa-th-large" id="view-icon"></i>
                    <span id="view-text">Card View</span>
                </button>
            </div>
        </div>

        <!-- Desktop Table View -->
        <div class="desktop-table-view" id="table-view">
            <div class="table-container">
                <div class="table-wrapper">
                    <table class="modern-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Product</th>
                                <th>Category</th>
                                <th>Description</th>
                                <th>Quantity</th>
                                <th>Cartons</th>
                                <th>Pieces</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($otherDeliveryReturn->items as $index => $item)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>
                                        <div class="product-info">
                                            <div class="product-name">{{ $item->product->name }}</div>
                                            <div class="product-code">{{ $item->product->code ?? 'N/A' }}</div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="category-badge">
                                            {{ $item->product->category->name ?? 'N/A' }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="description-text">
                                            {{ $item->description ?? 'No description' }}
                                        </div>
                                    </td>
                                    <td>
                                        <span class="quantity-value">{{ number_format($item->quantity, 2) }}</span>
                                    </td>
                                    <td>
                                        <span class="cartons-value">{{ $item->cartons ?? '-' }}</span>
                                    </td>
                                    <td>
                                        <span class="pieces-value">{{ $item->pieces ?? '-' }}</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="totals-row">
                                <th colspan="4" class="text-right">Totals:</th>
                                <th>{{ number_format($otherDeliveryReturn->items->sum('quantity'), 2) }}</th>
                                <th>{{ $otherDeliveryReturn->items->sum('cartons') ?: '-' }}</th>
                                <th>{{ $otherDeliveryReturn->items->sum('pieces') ?: '-' }}</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <!-- Mobile Card View -->
        <div class="mobile-card-view" id="card-view">
            <div class="products-grid">
                @foreach($otherDeliveryReturn->items as $index => $item)
                    <div class="product-card">
                        <div class="product-header">
                            <div class="product-number">#{{ $index + 1 }}</div>
                            <div class="product-category">
                                <span class="category-badge">
                                    {{ $item->product->category->name ?? 'N/A' }}
                                </span>
                            </div>
                        </div>
                        
                        <div class="product-body">
                            <div class="product-name">{{ $item->product->name }}</div>
                            @if($item->product->code)
                                <div class="product-code">Code: {{ $item->product->code }}</div>
                            @endif
                            
                            @if($item->description)
                                <div class="product-description">
                                    <div class="description-label">Description:</div>
                                    <div class="description-text">{{ $item->description }}</div>
                                </div>
                            @endif
                        </div>
                        
                        <div class="product-quantities">
                            <div class="quantity-item primary">
                                <div class="quantity-label">Quantity</div>
                                <div class="quantity-value">{{ number_format($item->quantity, 2) }}</div>
                            </div>
                            
                            @if($item->cartons)
                                <div class="quantity-item">
                                    <div class="quantity-label">Cartons</div>
                                    <div class="quantity-value">{{ $item->cartons }}</div>
                                </div>
                            @endif
                            
                            @if($item->pieces)
                                <div class="quantity-item">
                                    <div class="quantity-label">Pieces</div>
                                    <div class="quantity-value">{{ $item->pieces }}</div>
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Danger Zone (Only for non-completed returns) -->
    @if($otherDeliveryReturn->status !== 'completed')
        @can('other-delivery-return-delete')
            <div class="danger-zone">
                <div class="danger-card">
                    <div class="danger-header">
                        <div class="danger-title">
                            <i class="fas fa-exclamation-triangle"></i>
                            Danger Zone
                        </div>
                    </div>
                    <div class="danger-body">
                        <div class="danger-warning">
                            <p><strong>Delete Return Record</strong></p>
                            <p class="warning-text">
                                Deleting this return record will reverse all inventory adjustments and permanently remove all data. 
                                This action cannot be undone.
                            </p>
                        </div>
                        <button type="button" class="btn-danger-action delete-return" 
                                data-return-id="{{ $otherDeliveryReturn->id }}"
                                data-return-number="{{ $otherDeliveryReturn->return_number }}">
                            <i class="fas fa-trash"></i> Delete Return Record
                        </button>
                    </div>
                </div>
            </div>
        @endcan
    @endif

    <!-- Floating Action Button for Mobile -->
    <div class="fab-container">
        @if($otherDeliveryReturn->status !== 'completed')
            @can('other-delivery-return-edit')
                <a href="{{ route('other-delivery-returns.edit', $otherDeliveryReturn) }}" class="fab fab-primary">
                    <i class="fas fa-edit"></i>
                </a>
            @endcan
        @endif
        
        <a href="{{ route('other-delivery-returns.index') }}" class="fab fab-secondary">
            <i class="fas fa-arrow-left"></i>
        </a>
    </div>

    <!-- Modals -->
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
                    <p class="text-muted">This action will permanently remove the return record and reverse all inventory adjustments. This cannot be undone.</p>
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

/* Page Title */
.page-title-container {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.title-main {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 24px;
    font-weight: 700;
    color: var(--gray-900);
}

.title-sub {
    font-size: 16px;
    color: var(--gray-600);
    font-family: 'Monaco', 'Menlo', monospace;
}

/* Status Alert Bar */
.status-alert-bar {
    margin-bottom: 24px;
}

.status-indicator {
    background: white;
    border-radius: var(--border-radius);
    padding: 20px;
    box-shadow: var(--shadow);
    display: flex;
    align-items: center;
    gap: 16px;
    border-left: 4px solid var(--gray-200);
}

.status-indicator.status-pending {
    border-left-color: var(--warning);
}

.status-indicator.status-completed {
    border-left-color: var(--success);
}

.status-indicator.status-rejected {
    border-left-color: var(--danger);
}

.status-icon {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    color: white;
}

.status-pending .status-icon {
    background: var(--warning);
}

.status-completed .status-icon {
    background: var(--success);
}

.status-rejected .status-icon {
    background: var(--danger);
}

.status-content {
    flex: 1;
}

.status-label {
    font-size: 14px;
    color: var(--gray-500);
    margin-bottom: 4px;
}

.status-text {
    font-size: 18px;
    font-weight: 600;
    color: var(--gray-900);
    display: flex;
    align-items: center;
    gap: 12px;
}

.status-change-btn {
    background: var(--primary);
    color: white;
    border: none;
    padding: 6px 12px;
    border-radius: 6px;
    font-size: 12px;
    cursor: pointer;
    transition: var(--transition);
}

.status-change-btn:hover {
    background: var(--primary-dark);
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

/* Info Grid */
.info-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 20px;
    margin-bottom: 24px;
}

@media (min-width: 768px) {
    .info-grid {
        grid-template-columns: 1fr 1fr;
    }
}

.info-card {
    background: white;
    border-radius: var(--border-radius);
    box-shadow: var(--shadow);
    overflow: hidden;
}

.card-header {
    padding: 16px 20px;
    border-bottom: 1px solid var(--gray-200);
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.card-title {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 16px;
    font-weight: 600;
    color: var(--gray-900);
}

.return-info-card .card-title {
    color: var(--primary);
}

.returner-info-card .card-title {
    color: var(--info);
}

.card-action {
    background: none;
    border: none;
    color: var(--gray-500);
    cursor: pointer;
    padding: 8px;
    border-radius: 6px;
    transition: var(--transition);
}

.card-action:hover {
    background: var(--gray-100);
    color: var(--gray-700);
}

.card-body {
    padding: 20px;
}

.info-row {
    display: flex;
    flex-direction: column;
    gap: 4px;
    margin-bottom: 16px;
}

.info-row:last-child {
    margin-bottom: 0;
}

.info-label {
    font-size: 14px;
    font-weight: 600;
    color: var(--gray-500);
}

.info-value {
    font-size: 16px;
    color: var(--gray-900);
}

.return-number {
    font-family: 'Monaco', 'Menlo', monospace;
    font-weight: 700;
    color: var(--primary);
}

.info-time {
    font-size: 14px;
    color: var(--gray-500);
    margin-left: 8px;
}

.staff-info {
    display: flex;
    align-items: center;
    gap: 8px;
}

.staff-role {
    font-size: 12px;
    color: var(--gray-500);
    background: var(--gray-100);
    padding: 2px 8px;
    border-radius: 12px;
}

.no-data {
    color: var(--gray-400);
    font-style: italic;
}

.notes-text {
    background: var(--gray-50);
    padding: 12px;
    border-radius: 8px;
    border-left: 4px solid var(--primary);
}

.returner-name {
    font-weight: 600;
}

.phone-number a {
    color: var(--primary);
    text-decoration: none;
    display: flex;
    align-items: center;
    gap: 8px;
}

.phone-number a:hover {
    text-decoration: underline;
}

.address-text {
    display: flex;
    align-items: flex-start;
    gap: 8px;
    line-height: 1.5;
}

/* Summary Stats */
.summary-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 16px;
    margin-bottom: 24px;
}

.stat-item {
    background: white;
    border-radius: var(--border-radius);
    padding: 20px;
    box-shadow: var(--shadow);
    display: flex;
    align-items: center;
    gap: 16px;
    transition: var(--transition);
}

.stat-item:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-md);
}

.stat-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    background: var(--primary);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
}

.stat-content {
    flex: 1;
}

.stat-number {
    font-size: 24px;
    font-weight: 700;
    color: var(--gray-900);
    line-height: 1;
}

.stat-label {
    font-size: 14px;
    color: var(--gray-500);
    margin-top: 4px;
}

/* Products Section */
.products-section {
    background: white;
    border-radius: var(--border-radius);
    box-shadow: var(--shadow);
    overflow: hidden;
    margin-bottom: 24px;
}

.section-header {
    padding: 20px;
    border-bottom: 1px solid var(--gray-200);
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.section-title {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 18px;
    font-weight: 600;
    color: var(--gray-900);
}

.view-toggle {
    background: var(--gray-100);
    border: none;
    padding: 8px 16px;
    border-radius: 8px;
    cursor: pointer;
    transition: var(--transition);
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 14px;
    color: var(--gray-700);
}

.view-toggle:hover {
    background: var(--gray-200);
}

/* Desktop Table View */
.desktop-table-view {
    display: none;
}

@media (min-width: 1024px) {
    .desktop-table-view {
        display: block;
    }
    .mobile-card-view {
        display: none;
    }
}

.table-container {
    padding: 20px;
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
    padding: 12px 16px;
    text-align: left;
    font-weight: 600;
    color: var(--gray-700);
    border-bottom: 2px solid var(--gray-200);
    font-size: 14px;
}

.modern-table td {
    padding: 12px 16px;
    border-bottom: 1px solid var(--gray-100);
    vertical-align: top;
}

.modern-table tr:hover {
    background: var(--gray-50);
}

.product-info {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.product-name {
    font-weight: 600;
    color: var(--gray-900);
}

.product-code {
    font-size: 12px;
    color: var(--gray-500);
    font-family: 'Monaco', 'Menlo', monospace;
}

.category-badge {
    background: var(--primary);
    color: white;
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 500;
}

.description-text {
    max-width: 200px;
    line-height: 1.4;
}

.quantity-value,
.cartons-value,
.pieces-value {
    font-weight: 600;
    color: var(--gray-900);
}

.totals-row {
    background: var(--gray-50);
    font-weight: 600;
}

/* Mobile Card View */
.mobile-card-view {
    display: block;
    padding: 20px;
}

@media (min-width: 1024px) {
    .mobile-card-view {
        display: none;
    }
}

.products-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 16px;
}

@media (min-width: 640px) {
    .products-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

.product-card {
    background: var(--gray-50);
    border-radius: var(--border-radius);
    padding: 16px;
    border-left: 4px solid var(--primary);
    transition: var(--transition);
}

.product-card:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-md);
}

.product-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 12px;
}

.product-number {
    font-size: 14px;
    font-weight: 600;
    color: var(--gray-600);
}

.product-body {
    margin-bottom: 16px;
}

.product-body .product-name {
    font-size: 16px;
    font-weight: 600;
    color: var(--gray-900);
    margin-bottom: 4px;
}

.product-body .product-code {
    font-size: 12px;
    color: var(--gray-500);
    margin-bottom: 8px;
}

.product-description {
    margin-top: 8px;
}

.description-label {
    font-size: 12px;
    font-weight: 600;
    color: var(--gray-500);
    margin-bottom: 4px;
}

.product-description .description-text {
    font-size: 14px;
    color: var(--gray-700);
    line-height: 1.4;
}

.product-quantities {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(80px, 1fr));
    gap: 12px;
}

.quantity-item {
    text-align: center;
    padding: 8px;
    background: white;
    border-radius: 8px;
}

.quantity-item.primary {
    background: var(--primary);
    color: white;
}

.quantity-label {
    font-size: 12px;
    font-weight: 500;
    margin-bottom: 4px;
}

.quantity-item.primary .quantity-label {
    color: rgba(255, 255, 255, 0.8);
}

.quantity-item .quantity-value {
    font-size: 16px;
    font-weight: 700;
}

/* Danger Zone */
.danger-zone {
    margin-top: 32px;
}

.danger-card {
    background: white;
    border-radius: var(--border-radius);
    box-shadow: var(--shadow);
    border: 2px solid #fee2e2;
    overflow: hidden;
}

.danger-header {
    background: #fee2e2;
    padding: 16px 20px;
    border-bottom: 1px solid #fecaca;
}

.danger-title {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 16px;
    font-weight: 600;
    color: var(--danger);
}

.danger-body {
    padding: 20px;
}

.danger-warning {
    margin-bottom: 20px;
}

.danger-warning p {
    margin-bottom: 8px;
}

.warning-text {
    color: var(--gray-600);
    line-height: 1.5;
}

.btn-danger-action {
    background: var(--danger);
    color: white;
    border: none;
    padding: 12px 24px;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    transition: var(--transition);
    display: flex;
    align-items: center;
    gap: 8px;
}

.btn-danger-action:hover {
    background: #dc2626;
}

/* Floating Action Button */
.fab-container {
    position: fixed;
    bottom: 24px;
    right: 24px;
    z-index: 1000;
    display: flex;
    flex-direction: column;
    gap: 12px;
}

@media (min-width: 1024px) {
    .fab-container {
        display: none;
    }
}

.fab {
    width: 56px;
    height: 56px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    box-shadow: var(--shadow-lg);
    transition: var(--transition);
    text-decoration: none;
}

.fab-primary {
    background: var(--primary);
    color: white;
}

.fab-primary:hover {
    background: var(--primary-dark);
    color: white;
    transform: scale(1.1);
}

.fab-secondary {
    background: var(--gray-600);
    color: white;
}

.fab-secondary:hover {
    background: var(--gray-700);
    color: white;
    transform: scale(1.1);
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
    .info-grid {
        grid-template-columns: 1fr;
    }
    
    .summary-stats {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .products-grid {
        grid-template-columns: 1fr;
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
</style>
@stop

@section('additional_js')
<script>
$(document).ready(function() {
    let isLoading = false;
    
    // Initialize view toggle
    initializeViewToggle();
    
    function initializeViewToggle() {
        const isMobile = window.innerWidth < 1024;
        if (isMobile) {
            $('#view-toggle').hide();
        }
    }
    
    // View toggle functionality
    $('#view-toggle').on('click', function() {
        const tableView = $('#table-view');
        const cardView = $('#card-view');
        const icon = $('#view-icon');
        const text = $('#view-text');
        
        if (tableView.is(':visible')) {
            tableView.hide();
            cardView.show();
            icon.removeClass('fa-th-large').addClass('fa-table');
            text.text('Table View');
        } else {
            cardView.hide();
            tableView.show();
            icon.removeClass('fa-table').addClass('fa-th-large');
            text.text('Card View');
        }
    });
    
    // Status change functionality
    $('.status-change-btn').on('click', function() {
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
    
    // Update status
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
                    setTimeout(() => {
                        location.reload();
                    }, 1000);
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
                $('#statusModal').modal('hide');
            }
        });
    });
    
    function getStatusBadgeHtml(status) {
        const statusConfig = {
            pending: { icon: 'fas fa-clock', text: 'Pending', class: 'badge-warning' },
            completed: { icon: 'fas fa-check-circle', text: 'Completed', class: 'badge-success' },
            rejected: { icon: 'fas fa-times-circle', text: 'Rejected', class: 'badge-danger' }
        };
        
        const config = statusConfig[status] || { icon: 'fas fa-question', text: status, class: 'badge-secondary' };
        return `<span class="badge ${config.class}"><i class="${config.icon}"></i> ${config.text}</span>`;
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
        
        $('.page-content').prepend(alertHtml);
        
        setTimeout(() => {
            $('.alert').fadeOut();
        }, 5000);
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
    $('.returner-history').on('click', function() {
        const returnerName = '{{ $otherDeliveryReturn->returner_name }}';
        const returnerPhone = '{{ $otherDeliveryReturn->returner_phone ?? "N/A" }}';
        const returnerAddress = '{{ $otherDeliveryReturn->returner_address ?? "N/A" }}';
        
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
    
    // Auto-hide alerts
    setTimeout(() => {
        $('.alert').fadeOut();
    }, 5000);
    
    // Handle window resize
    $(window).on('resize', function() {
        initializeViewToggle();
    });
    
    // Keyboard shortcuts
    $(document).on('keydown', function(e) {
        // Escape to close modals
        if (e.keyCode === 27) {
            $('.modal').modal('hide');
        }
        
        // Ctrl/Cmd + E for edit (if allowed)
        if ((e.ctrlKey || e.metaKey) && e.keyCode === 69) {
            e.preventDefault();
            const editBtn = $('.fab-primary');
            if (editBtn.length) {
                window.location.href = editBtn.attr('href');
            }
        }
        
        // Ctrl/Cmd + P for print
        if ((e.ctrlKey || e.metaKey) && e.keyCode === 80) {
            e.preventDefault();
            const printBtn = $('a[href*="print"]');
            if (printBtn.length) {
                window.open(printBtn.attr('href'), '_blank');
            }
        }
    });
});
</script>
@stop
