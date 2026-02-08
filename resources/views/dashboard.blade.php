@extends('layouts.modern-admin')

@section('title', 'ERP Dashboard')
@section('page_title', 'Dashboard')

@section('header_actions')
    <div class="time-filter">
        <form action="{{ route('dashboard') }}" method="GET" class="form-inline">
            <select name="time_filter" class="form-control modern-select mr-2" onchange="toggleCustomDateInputs(this.value)">
                <option value="month" {{ request('time_filter', 'month') == 'month' ? 'selected' : '' }}>This Month</option>
                <option value="year" {{ request('time_filter') == 'year' ? 'selected' : '' }}>This Year</option>
                <option value="custom" {{ request('time_filter') == 'custom' ? 'selected' : '' }}>Custom Range</option>
            </select>
            <div id="custom-date-inputs" style="{{ request('time_filter') == 'custom' ? '' : 'display: none;' }}">
                <input type="date" name="start_date" class="form-control modern-input mr-2" value="{{ request('start_date', now()->startOfMonth()->format('Y-m-d')) }}">
                <input type="date" name="end_date" class="form-control modern-input mr-2" value="{{ request('end_date', now()->format('Y-m-d')) }}">
            </div>
            <button type="submit" class="btn modern-btn modern-btn-primary">
                <i class="fas fa-chart-line"></i> Apply
            </button>
        </form>
    </div>
@stop

@section('page_content')
    <!-- Modern Stats Cards Row -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6">
            <div class="stats-card stats-card-primary">
                <div class="stats-icon">
                    <i class="fas fa-file-invoice"></i>
                </div>
                <div class="stats-content">
                    <h3 class="stats-number">{{ $totalInvoices }}</h3>
                    <p class="stats-label">Total Invoices</p>
                    <div class="stats-trend">
                        <i class="fas fa-arrow-up"></i> 12% from last month
                    </div>
                </div>
                <a href="{{ route('invoices.index') }}" class="stats-link">
                    View Details <i class="fas fa-arrow-right"></i>
                </a>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6">
            <div class="stats-card stats-card-success">
                <div class="stats-icon">
                    <i class="fas fa-dollar-sign"></i>
                </div>
                <div class="stats-content">
                    <h3 class="stats-number">{{ number_format($totalInvoiceAmount, 2) }}</h3>
                    <p class="stats-label">Total Sales</p>
                    <div class="stats-trend">
                        <i class="fas fa-arrow-up"></i> 8% from last month
                    </div>
                </div>
                <a href="{{ route('invoices.index') }}" class="stats-link">
                    View Details <i class="fas fa-arrow-right"></i>
                </a>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6">
            <div class="stats-card stats-card-warning">
                <div class="stats-icon">
                    <i class="fas fa-credit-card"></i>
                </div>
                <div class="stats-content">
                    <h3 class="stats-number">{{ number_format($totalBalanceDue, 2) }}</h3>
                    <p class="stats-label">Total Due Balance</p>
                    <div class="stats-trend">
                        <i class="fas fa-arrow-down"></i> 5% from last month
                    </div>
                </div>
                <a href="{{ route('customers.index') }}" class="stats-link">
                    View Details <i class="fas fa-arrow-right"></i>
                </a>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6">
            <div class="stats-card stats-card-info">
                <div class="stats-icon">
                    <i class="fas fa-boxes"></i>
                </div>
                <div class="stats-content">
                    <h3 class="stats-number">{{ $totalProductQuantity }}</h3>
                    <p class="stats-label">Total Product Stock</p>
                    <div class="stats-trend">
                        <i class="fas fa-minus"></i> No change
                    </div>
                </div>
                <a href="{{ route('products.index') }}" class="stats-link">
                    View Details <i class="fas fa-arrow-right"></i>
                </a>
            </div>
        </div>
    </div>

    <!-- Modern Data Tables Row -->
    <div class="row">
        <div class="col-md-6">
            <div class="card modern-card">
                <div class="card-header modern-header">
                    <h3 class="card-title">
                        <i class="fas fa-file-invoice"></i> Latest Invoices
                    </h3>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table modern-table mb-0">
                            <thead>
                                <tr>
                                    <th>Invoice #</th>
                                    <th>Customer</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th>Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($latestInvoices as $invoice)
                                <tr>
                                    <td>
                                        <strong class="text-primary">{{ $invoice->invoice_number }}</strong>
                                    </td>
                                    <td>{{ $invoice->customer->name }}</td>
                                    <td>
                                        <small class="text-muted">{{ $invoice->invoice_date->format('d M Y') }}</small>
                                    </td>
                                    <td>
                                        @if($invoice->payment_status == 'paid')
                                            <span class="badge badge-success">
                                                <i class="fas fa-check"></i> Paid
                                            </span>
                                        @elseif($invoice->payment_status == 'partial')
                                            <span class="badge badge-warning">
                                                <i class="fas fa-clock"></i> Partial
                                            </span>
                                        @else
                                            <span class="badge badge-danger">
                                                <i class="fas fa-times"></i> Unpaid
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        <strong>{{ number_format($invoice->total, 2) }}</strong>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4">
                                        <div class="empty-state">
                                            <i class="fas fa-file-invoice fa-3x text-muted mb-3"></i>
                                            <p class="text-muted">No invoices found</p>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer text-center">
                    <a href="{{ route('invoices.index') }}" class="btn modern-btn modern-btn-primary btn-sm">
                        <i class="fas fa-list"></i> View All Invoices
                    </a>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card modern-card">
                <div class="card-header modern-header success-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-bar"></i> Top 10 Most Stocked Items
                    </h3>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table modern-table mb-0">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Category</th>
                                    <th>Stock</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($topStockItems as $product)
                                <tr>
                                    <td>
                                        <strong>{{ $product->name }}</strong>
                                    </td>
                                    <td>
                                        <span class="text-muted">{{ $product->category->name ?? 'N/A' }}</span>
                                    </td>
                                    <td>
                                        <span class="stock-quantity">{{ $product->current_stock }}</span>
                                    </td>
                                    <td>
                                        @if($product->current_stock > 20)
                                            <span class="badge badge-success">
                                                <i class="fas fa-check-circle"></i> In Stock
                                            </span>
                                        @elseif($product->current_stock > 5)
                                            <span class="badge badge-warning">
                                                <i class="fas fa-exclamation-triangle"></i> Low Stock
                                            </span>
                                        @else
                                            <span class="badge badge-danger">
                                                <i class="fas fa-exclamation-circle"></i> Critical
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center py-4">
                                        <div class="empty-state">
                                            <i class="fas fa-boxes fa-3x text-muted mb-3"></i>
                                            <p class="text-muted">No products found</p>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer text-center">
                    <a href="{{ route('products.index') }}" class="btn modern-btn modern-btn-success btn-sm">
                        <i class="fas fa-boxes"></i> View All Products
                    </a>
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
                            <a href="{{ route('invoices.create') }}" class="quick-action-card">
                                <div class="quick-action-icon bg-primary">
                                    <i class="fas fa-plus"></i>
                                </div>
                                <div class="quick-action-content">
                                    <h5>Create Invoice</h5>
                                    <p>Generate new invoice</p>
                                </div>
                            </a>
                        </div>
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
                            <a href="{{ route('products.create') }}" class="quick-action-card">
                                <div class="quick-action-icon bg-warning">
                                    <i class="fas fa-box"></i>
                                </div>
                                <div class="quick-action-content">
                                    <h5>Add Product</h5>
                                    <p>Add new product</p>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-3 col-sm-6 mb-3">
                            <a href="{{ route('transactions.create') }}" class="quick-action-card">
                                <div class="quick-action-icon bg-info">
                                    <i class="fas fa-money-bill"></i>
                                </div>
                                <div class="quick-action-content">
                                    <h5>Add Transaction</h5>
                                    <p>Record payment</p>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('additional_css')
<style>
/* Modern Stats Cards */
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

/* Enhanced Badges */
.badge {
    font-size: 0.75rem;
    padding: 0.375rem 0.75rem;
    border-radius: var(--border-radius);
    font-weight: 500;
}

.badge i {
    margin-right: 0.25rem;
}

/* Empty State */
.empty-state {
    padding: 2rem;
}

.empty-state i {
    opacity: 0.3;
}

/* Stock Quantity Styling */
.stock-quantity {
    font-weight: 600;
    font-size: 1.1rem;
    color: #333;
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
    font-size: 0.875rem;
    color: var(--text-muted);
    margin: 0;
}

/* Time Filter Styling */
.time-filter .form-inline {
    align-items: center;
}

.time-filter .modern-select,
.time-filter .modern-input {
    min-width: 120px;
}

/* Responsive adjustments */
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
    
    .time-filter .form-inline {
        flex-direction: column;
        align-items: stretch;
    }
    
    .time-filter .form-control {
        margin-bottom: 0.5rem;
        margin-right: 0 !important;
    }
}
</style>
@stop
@section('js')
    <script>
        function toggleCustomDateInputs(value) {
            const customDateInputs = document.getElementById('custom-date-inputs');
            if (value === 'custom') {
                customDateInputs.style.display = 'inline-flex';
            } else {
                customDateInputs.style.display = 'none';
            }
        }
        // Add smooth animations on page load
$(document).ready(function() {
    $('.stats-card').each(function(index) {
        $(this).delay(index * 100).animate({
            opacity: 1
        }, 500);
    });
});
    </script>
@stop
