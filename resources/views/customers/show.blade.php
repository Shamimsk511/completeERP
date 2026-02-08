@extends('layouts.modern-admin')

@section('title', 'Customer Details')
@section('page_title', 'Customer Details')

@section('header_actions')
    <a class="btn modern-btn modern-btn-primary" href="{{ route('customers.ledger', $customer->id) }}">
        <i class="fas fa-book"></i> Customer Ledger
    </a>
    <a class="btn modern-btn modern-btn-warning" href="{{ route('customers.edit', $customer->id) }}">
        <i class="fas fa-edit"></i> Edit Customer
    </a>
    <a class="btn modern-btn modern-btn-secondary" href="{{ route('customers.index') }}">
        <i class="fas fa-arrow-left"></i> Back to Customers
    </a>
@stop

@section('page_content')
    <!-- Customer Overview Cards -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6">
            <div class="stats-card stats-card-primary">
                <div class="stats-icon">
                    <i class="fas fa-receipt"></i>
                </div>
                <div class="stats-content">
                    <h3 class="stats-number">{{ isset($customer->invoices) ? $customer->invoices->count() : 0 }}</h3>
                    <p class="stats-label">Total Invoices</p>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6">
            <div class="stats-card stats-card-success">
                <div class="stats-icon">
                    <i class="fas fa-dollar-sign"></i>
                </div>
                <div class="stats-content">
                    <h3 class="stats-number">৳{{ isset($customer->invoices) ? number_format($customer->invoices->sum('total'), 2) : '0.00' }}</h3>
                    <p class="stats-label">Total Sales</p>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6">
            <div class="stats-card stats-card-warning">
                <div class="stats-icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div class="stats-content">
                    <h3 class="stats-number">৳{{ number_format($customer->outstanding_balance, 2) }}</h3>
                    <p class="stats-label">Outstanding</p>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6">
            <div class="stats-card stats-card-info">
                <div class="stats-icon">
                    <i class="fas fa-undo"></i>
                </div>
                <div class="stats-content">
                    <h3 class="stats-number">{{ isset($customer->productReturns) ? $customer->productReturns->count() : 0 }}</h3>
                    <p class="stats-label">Returns</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Customer Insights -->
    <div class="card modern-card mb-3 compact-card">
        <div class="card-header modern-header">
            <h3 class="card-title">
                <i class="fas fa-chart-pie"></i> Customer Insights
            </h3>
        </div>
        <div class="card-body modern-card-body">
            <div class="insights-grid">
                <div class="insight-item">
                    <div class="insight-label">Visits / Month</div>
                    <div class="insight-value">{{ number_format($customerInsights['visits_per_month'] ?? 0, 2) }}</div>
                </div>
                <div class="insight-item">
                    <div class="insight-label">Avg Basket</div>
                    <div class="insight-value">৳{{ number_format($customerInsights['avg_basket'] ?? 0, 2) }}</div>
                </div>
                <div class="insight-item">
                    <div class="insight-label">Last Purchase</div>
                    <div class="insight-value">
                        @if(!empty($customerInsights['last_invoice']))
                            {{ \Carbon\Carbon::parse($customerInsights['last_invoice']->invoice_date)->format('d M, Y') }}
                        @else
                            N/A
                        @endif
                    </div>
                </div>
                <div class="insight-item">
                    <div class="insight-label">Since Last Purchase</div>
                    <div class="insight-value">
                        @if(!is_null($customerInsights['days_since_last']))
                            {{ $customerInsights['days_since_last'] }} days
                        @else
                            N/A
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Customer Information Card -->
    <div class="card modern-card mb-4">
        <div class="card-header modern-header">
            <h3 class="card-title">
                <i class="fas fa-user"></i> Customer Information
            </h3>
            <div class="card-tools">
                <span class="modern-badge">ID: {{ $customer->id }}</span>
            </div>
        </div>
        <div class="card-body modern-card-body">
            <div class="row">
                <div class="col-lg-6">
                    <div class="info-item">
                        <div class="info-label">
                            <i class="fas fa-user text-primary"></i> Customer Name
                        </div>
                        <div class="info-value customer-name">{{ $customer->name }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">
                            <i class="fas fa-phone text-success"></i> Phone Number
                        </div>
                        <div class="info-value">{{ $customer->phone ?: 'N/A' }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">
                            <i class="fas fa-map-marker-alt text-danger"></i> Address
                        </div>
                        <div class="info-value">{{ $customer->address ?: 'No address provided' }}</div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="info-item">
                        <div class="info-label">
                            <i class="fas fa-wallet text-info"></i> Opening Balance
                        </div>
                        <div class="info-value balance-amount {{ $customer->opening_balance > 0 ? 'text-danger' : ($customer->opening_balance < 0 ? 'text-info' : 'text-success') }}">
                            ৳{{ number_format($customer->opening_balance, 2) }}
                        </div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">
                            <i class="fas fa-dollar-sign text-warning"></i> Outstanding Balance
                        </div>
                        <div class="info-value balance-amount {{ $customer->outstanding_balance > 0 ? 'text-danger' : ($customer->outstanding_balance < 0 ? 'text-info' : 'text-success') }}">
                            ৳{{ number_format($customer->outstanding_balance, 2) }}
                        </div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">
                            <i class="fas fa-chart-line text-purple"></i> Status
                        </div>
                        <div class="info-value">
                            @if($customer->outstanding_balance > 5000)
                                <span class="status-badge status-danger">High Outstanding</span>
                            @elseif($customer->outstanding_balance > 0)
                                <span class="status-badge status-warning">Outstanding</span>
                            @elseif($customer->outstanding_balance < 0)
                                <span class="status-badge status-info">Credit Balance</span>
                            @else
                                <span class="status-badge status-success">Clear</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Invoices Card -->
    <div class="card modern-card mb-4">
        <div class="card-header modern-header success-header">
            <h3 class="card-title">
                <i class="fas fa-receipt"></i> Customer Invoices
            </h3>
            <div class="card-tools">
                <span class="modern-badge">{{ isset($customer->invoices) ? $customer->invoices->count() : 0 }} Invoices</span>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table modern-table mb-0" id="invoices-table">
                    <thead>
                        <tr>
                            <th>Invoice #</th>
                            <th>Date</th>
                            <th>Total</th>
                            <th>Paid</th>
                            <th>Due</th>
                            <th>Status</th>
                            <th>Items</th>
                            <th>Quantity</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if(isset($customer->invoices) && $customer->invoices->count() > 0)
                            @foreach($customer->invoices as $invoice)
                            <tr>
                                <td>
                                    <a href="{{ route('invoices.show', $invoice->id) }}"
                                       class="invoice-link js-invoice-modal"
                                       data-invoice-id="{{ $invoice->id }}"
                                       data-invoice-number="{{ $invoice->invoice_number }}">
                                        <i class="fas fa-file-invoice"></i> {{ $invoice->invoice_number }}
                                    </a>
                                </td>
                                <td>{{ \Carbon\Carbon::parse($invoice->invoice_date)->format('d M, Y') }}</td>
                                <td><span class="amount-text">৳{{ number_format($invoice->total, 2) }}</span></td>
                                <td><span class="amount-text text-success">৳{{ number_format($invoice->paid_amount, 2) }}</span></td>
                                <td><span class="amount-text {{ $invoice->due_amount > 0 ? 'text-danger' : 'text-success' }}">৳{{ number_format($invoice->due_amount, 2) }}</span></td>
                                <td>
                                    @if($invoice->payment_status == 'paid')
                                        <span class="status-badge status-success">Paid</span>
                                    @elseif($invoice->payment_status == 'partial')
                                        <span class="status-badge status-warning">Partial</span>
                                    @else
                                        <span class="status-badge status-danger">Unpaid</span>
                                    @endif
                                </td>
                                <td><span class="count-badge">{{ $invoice->items_count ?? 0 }}</span></td>
                                <td><span class="count-badge">{{ number_format($invoice->items_sum_quantity ?? 0, 2) }}</span></td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="{{ route('invoices.show', $invoice->id) }}"
                                           class="btn modern-btn modern-btn-primary btn-sm js-invoice-modal"
                                           data-invoice-id="{{ $invoice->id }}"
                                           data-invoice-number="{{ $invoice->invoice_number }}"
                                           title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('invoices.print', $invoice->id) }}" class="btn modern-btn modern-btn-secondary btn-sm" title="Print">
                                            <i class="fas fa-print"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        @endif
                    </tbody>
                    @if(isset($customer->invoices) && $customer->invoices->count() > 0)
                    <tfoot>
                        <tr class="table-totals">
                            <th colspan="2">Totals</th>
                            <th>৳{{ number_format($customer->invoices->sum('total'), 2) }}</th>
                            <th>৳{{ number_format($customer->invoices->sum('paid_amount'), 2) }}</th>
                            <th>৳{{ number_format($customer->invoices->sum('due_amount'), 2) }}</th>
                            <th></th>
                            <th>{{ $customer->invoices->sum('items_count') }}</th>
                            <th>{{ number_format($customer->invoices->sum('items_sum_quantity'), 2) }}</th>
                            <th></th>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>

    <!-- Product Returns Card -->
    <div class="card modern-card mb-4">
        <div class="card-header modern-header danger-header">
            <h3 class="card-title">
                <i class="fas fa-undo"></i> Product Returns
            </h3>
            <div class="card-tools">
                <span class="modern-badge">{{ isset($customer->productReturns) ? $customer->productReturns->count() : 0 }} Returns</span>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table modern-table mb-0" id="returns-table">
                    <thead>
                        <tr>
                            <th>Return #</th>
                            <th>Date</th>
                            <th>Invoice #</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Items</th>
                            <th>Quantity</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if(isset($customer->productReturns) && $customer->productReturns->count() > 0)
                            @foreach($customer->productReturns as $return)
                            <tr>
                                <td>
                                    <a href="{{ route('returns.show', $return->id) }}"
                                       class="return-link js-return-modal"
                                       data-return-id="{{ $return->id }}"
                                       data-return-number="{{ $return->return_number }}">
                                        <i class="fas fa-undo"></i> {{ $return->return_number }}
                                    </a>
                                </td>
                                <td>{{ \Carbon\Carbon::parse($return->return_date)->format('d M, Y') }}</td>
                                <td>
                                    @if(isset($return->invoice) && $return->invoice)
                                        <a href="{{ route('invoices.show', $return->invoice_id) }}"
                                           class="invoice-link js-invoice-modal"
                                           data-invoice-id="{{ $return->invoice_id }}"
                                           data-invoice-number="{{ $return->invoice->invoice_number }}">
                                            {{ $return->invoice->invoice_number }}
                                        </a>
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                                <td><span class="amount-text text-danger">৳{{ number_format($return->total, 2) }}</span></td>
                                <td>
                                    @if($return->status == 'completed')
                                        <span class="status-badge status-success">Completed</span>
                                    @elseif($return->status == 'pending')
                                        <span class="status-badge status-warning">Pending</span>
                                    @else
                                        <span class="status-badge status-info">{{ ucfirst($return->status) }}</span>
                                    @endif
                                </td>
                                <td><span class="count-badge">{{ $return->items_count ?? 0 }}</span></td>
                                <td><span class="count-badge">{{ number_format($return->items_sum_quantity ?? 0, 2) }}</span></td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="{{ route('returns.show', $return->id) }}"
                                           class="btn modern-btn modern-btn-primary btn-sm js-return-modal"
                                           data-return-id="{{ $return->id }}"
                                           data-return-number="{{ $return->return_number }}"
                                           title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('returns.print', $return->id) }}" class="btn modern-btn modern-btn-secondary btn-sm" title="Print">
                                            <i class="fas fa-print"></i>
                                        </a>
                                        @if(isset($return->invoice) && $return->invoice)
                                            <a href="{{ route('invoices.show', $return->invoice_id) }}"
                                               class="btn modern-btn modern-btn-info btn-sm js-invoice-modal"
                                               data-invoice-id="{{ $return->invoice_id }}"
                                               data-invoice-number="{{ $return->invoice->invoice_number }}"
                                               title="View Invoice">
                                                <i class="fas fa-file-invoice"></i>
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        @endif
                    </tbody>
                    @if(isset($customer->productReturns) && $customer->productReturns->count() > 0)
                    <tfoot>
                        <tr class="table-totals">
                            <th colspan="3">Totals</th>
                            <th>৳{{ number_format($customer->productReturns->sum('total'), 2) }}</th>
                            <th></th>
                            <th>{{ $customer->productReturns->sum('items_count') }}</th>
                            <th>{{ number_format($customer->productReturns->sum('items_sum_quantity'), 2) }}</th>
                            <th></th>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>

    <!-- Delivery Challans Card -->
    <div class="card modern-card mb-4">
        <div class="card-header modern-header warning-header">
            <h3 class="card-title">
                <i class="fas fa-truck"></i> Delivery Challans
            </h3>
            <div class="card-tools">
                <span class="modern-badge">{{ isset($customer->challans) ? $customer->challans->count() : 0 }} Challans</span>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table modern-table mb-0" id="challans-table">
                    <thead>
                        <tr>
                            <th>Challan #</th>
                            <th>Date</th>
                            <th>Invoice #</th>
                            <th>Items</th>
                            <th>Quantity</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if(isset($customer->challans) && $customer->challans->count() > 0)
                            @foreach($customer->challans as $challan)
                            <tr>
                                <td>
                                    <a href="{{ route('challans.show', $challan->id) }}" class="challan-link">
                                        <i class="fas fa-truck"></i> {{ $challan->challan_number }}
                                    </a>
                                </td>
                                <td>{{ \Carbon\Carbon::parse($challan->challan_date)->format('d M, Y') }}</td>
                                <td>
                                    @if(isset($challan->invoice) && $challan->invoice)
                                    <a href="{{ route('invoices.show', $challan->invoice_id) }}" class="invoice-link">
                                        {{ $challan->invoice->invoice_number }}
                                    </a>
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                                <td><span class="count-badge">{{ $challan->items_count ?? 0 }}</span></td>
                                <td><span class="count-badge">{{ number_format($challan->items_sum_quantity ?? 0, 2) }}</span></td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="{{ route('challans.show', $challan->id) }}" class="btn modern-btn modern-btn-primary btn-sm" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('challans.print', $challan->id) }}" class="btn modern-btn modern-btn-secondary btn-sm" title="Print">
                                            <i class="fas fa-print"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        @endif
                    </tbody>
                    @if(isset($customer->challans) && $customer->challans->count() > 0)
                    <tfoot>
                        <tr class="table-totals">
                            <th colspan="3">Totals</th>
                            <th>{{ $customer->challans->sum('items_count') }}</th>
                            <th>{{ number_format($customer->challans->sum('items_sum_quantity'), 2) }}</th>
                            <th></th>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>

    <!-- Product Purchase Summary -->
    <div class="card modern-card mb-4">
        <div class="card-header modern-header info-header">
            <h3 class="card-title">
                <i class="fas fa-chart-bar"></i> Product Purchase Summary
            </h3>
            <div class="card-tools">
                <div class="d-flex align-items-center gap-2">
                    <label for="purchase-category-filter" class="mb-0 text-white-50 small">Category</label>
                    <select id="purchase-category-filter" class="form-control form-control-sm">
                        <option value="">All</option>
                        <option value="0">Uncategorized</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
        
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table modern-table mb-0" id="purchase-summary-table">
                    <thead>
                        <tr>
                            <th>Product Name</th>
                            <th>Category</th>
                            <th>Total Quantity</th>
                            <th>Total Amount</th>
                            <th>Last Purchase</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Returned Products Summary -->
    <div class="card modern-card">
        <div class="card-header modern-header danger-header">
            <h3 class="card-title">
                <i class="fas fa-chart-line"></i> Returned Products
            </h3>
        </div>
        
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table modern-table mb-0" id="return-items-table">
                    <thead>
                        <tr>
                            <th>Return #</th>
                            <th>Date</th>
                            <th>Invoice #</th>
                            <th>Product</th>
                            <th>Category</th>
                            <th>Quantity</th>
                            <th>Amount</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Invoice Items Modal -->
    <div class="modal fade" id="invoiceItemsModal" tabindex="-1" role="dialog" aria-labelledby="invoiceItemsLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="invoiceItemsLabel">
                        Invoice Items <span id="invoice-modal-number" class="text-muted"></span>
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table table-sm mb-0">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th class="text-right">Qty</th>
                                    <th class="text-right">Unit Price</th>
                                    <th class="text-right">Total</th>
                                </tr>
                            </thead>
                            <tbody id="invoice-items-body">
                                <tr>
                                    <td colspan="4" class="text-center text-muted">Loading...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <a href="#" class="btn btn-outline-secondary" id="invoice-modal-view" target="_blank">View Invoice</a>
                    <a href="#" class="btn btn-outline-primary" id="invoice-modal-print" target="_blank">Print</a>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Return Items Modal -->
    <div class="modal fade" id="returnItemsModal" tabindex="-1" role="dialog" aria-labelledby="returnItemsLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="returnItemsLabel">
                        Return Items <span id="return-modal-number" class="text-muted"></span>
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table table-sm mb-0">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th class="text-right">Qty</th>
                                    <th class="text-right">Unit Price</th>
                                    <th class="text-right">Total</th>
                                </tr>
                            </thead>
                            <tbody id="return-items-body">
                                <tr>
                                    <td colspan="4" class="text-center text-muted">Loading...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <a href="#" class="btn btn-outline-secondary" id="return-modal-view" target="_blank">View Return</a>
                    <a href="#" class="btn btn-outline-primary" id="return-modal-print" target="_blank">Print</a>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
@stop

@section('additional_css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="/css/modern-admin.css">

<style>
/* Customer-specific styles */
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

/* Info items styling */
.info-item {
    display: flex;
    flex-direction: column;
    margin-bottom: 1.5rem;
    padding: 1rem;
    background: rgba(102, 126, 234, 0.02);
    border-radius: var(--border-radius);
    border-left: 4px solid #667eea;
}

.info-label {
    font-size: 0.875rem;
    font-weight: 600;
    color: #6b7280;
    margin-bottom: 0.5rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.info-value {
    font-size: 1rem;
    font-weight: 600;
    color: #374151;
}

.customer-name {
    font-size: 1.25rem;
    color: #667eea;
}

.balance-amount {
    font-size: 1.1rem;
    font-weight: 700;
}

/* Table styling */
.invoice-link,
.return-link,
.challan-link {
    color: #667eea;
    text-decoration: none;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    transition: all 0.3s ease;
}

.invoice-link:hover,
.return-link:hover,
.challan-link:hover {
    background: rgba(102, 126, 234, 0.1);
    color: #5a67d8;
    text-decoration: none;
}

.amount-text {
    font-weight: 600;
    font-family: 'Courier New', monospace;
}

.count-badge {
    background: rgba(102, 126, 234, 0.1);
    color: #667eea;
    padding: 0.25rem 0.5rem;
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: 600;
}

.category-badge {
    background: rgba(17, 153, 142, 0.1);
    color: #11998e;
    padding: 0.25rem 0.75rem;
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: 500;
}

.product-name {
    font-weight: 600;
    color: #374151;
}

/* Status badges */
.status-badge {
    padding: 0.25rem 0.75rem;
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.status-success {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    color: white;
}

.status-warning {
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
    color: white;
}

.status-danger {
    background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
    color: white;
}

.status-info {
    background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
    color: white;
}

/* Action buttons */
.action-buttons {
    display: flex;
    gap: 0.25rem;
    justify-content: center;
}

.action-buttons .btn {
    padding: 0.5rem 0.75rem;
    font-size: 0.875rem;
    border-radius: var(--border-radius);
    min-width: 40px;
}

/* Table totals */
.table-totals {
    background: linear-gradient(135deg, rgba(102, 126, 234, 0.1), rgba(118, 75, 162, 0.1));
    font-weight: 600;
}

.table-totals th {
    border-top: 2px solid #667eea;
    padding: 1rem 0.75rem;
}

/* Empty states */
.empty-state {
    padding: 3rem 2rem;
    text-align: center;
}

.empty-state i {
    opacity: 0.5;
}

.empty-state h5 {
    color: #6b7280;
    margin: 1rem 0 0.5rem 0;
}

.empty-state p {
    color: #9ca3af;
    margin: 0;
}

/* Filter sections */
.filter-section {
    background: rgba(102, 126, 234, 0.02);
    border-bottom: 1px solid rgba(102, 126, 234, 0.1);
    transition: all 0.3s ease;
}

.filter-section .form-label {
    font-weight: 600;
    color: #374151;
    margin-bottom: 0.5rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.filter-section .select2-container {
    width: 100% !important;
}

.filter-section .select2-selection {
    border: 2px solid #e5e7eb !important;
    border-radius: 8px !important;
    min-height: 42px !important;
}

.filter-section .select2-selection:focus-within {
    border-color: #667eea !important;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1) !important;
}

.filter-active {
    background: rgba(102, 126, 234, 0.05) !important;
    border-left: 4px solid #667eea;
}

.filter-count {
    background: #667eea;
    color: white;
    padding: 0.25rem 0.5rem;
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: 600;
    margin-left: 0.5rem;
}

/* Hidden rows animation */
.table tbody tr.filtered-out {
    display: none;
}

.table tbody tr.filtered-in {
    display: table-row;
    animation: fadeIn 0.3s ease-in;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

/* Filter toggle button */
.card-tools .btn {
    transition: all 0.3s ease;
}

.card-tools .btn.active {
    background: rgba(255, 255, 255, 0.2) !important;
    transform: rotate(180deg);
}

/* Responsive design */
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
    
    .info-item {
        margin-bottom: 1rem;
        padding: 0.75rem;
    }
    
    .action-buttons {
        flex-direction: column;
        gap: 0.25rem;
    }
    
    .action-buttons .btn {
        width: 100%;
    }
    
    .filter-section .btn-group {
        flex-direction: column;
        gap: 0.5rem;
    }
    
    .filter-section .btn {
        width: 100%;
    }
}
/* Compact + no animation overrides */
* {
    animation: none !important;
    transition: none !important;
}

.card-header {
    padding: 0.5rem 0.75rem;
}

.card-body {
    padding: 0.75rem;
}

.stats-card {
    padding: 0.75rem;
    box-shadow: none;
    margin-bottom: 0.75rem;
}

.stats-card:hover {
    transform: none;
    box-shadow: none;
}

.stats-icon {
    width: 44px;
    height: 44px;
    font-size: 1rem;
    top: 0.5rem;
    right: 0.5rem;
}

.stats-content {
    padding-right: 60px;
}

.stats-number {
    font-size: 1.25rem;
}

.info-item {
    padding: 0.5rem;
    margin-bottom: 0.5rem;
}

.modern-card {
    margin-bottom: 0.75rem !important;
}

.modern-table th,
.modern-table td {
    padding: 0.5rem 0.5rem;
}

.action-buttons .btn {
    padding: 0.35rem 0.5rem;
    min-width: 32px;
}

.insights-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
    gap: 0.5rem;
}

.insight-item {
    background: #f9fafb;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    padding: 0.5rem 0.75rem;
}

.insight-label {
    font-size: 0.75rem;
    color: #6b7280;
    text-transform: uppercase;
    letter-spacing: 0.04em;
}

.insight-value {
    font-size: 0.95rem;
    font-weight: 600;
    color: #111827;
}

</style>
@stop

@section('additional_js')
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap4.min.js"></script>

<script>
$(document).ready(function() {
    const tableConfig = {
        paging: true,
        pageLength: 10,
        lengthChange: true,
        searching: true,
        ordering: true,
        info: true,
        autoWidth: false,
        language: {
            emptyTable: 'No data available',
            info: 'Showing _START_ to _END_ of _TOTAL_ entries',
            infoEmpty: 'Showing 0 to 0 of 0 entries',
            infoFiltered: '(filtered from _MAX_ total entries)',
            lengthMenu: 'Show _MENU_ entries',
            loadingRecords: 'Loading...',
            processing: 'Processing...',
            search: 'Search:',
            zeroRecords: 'No matching records found',
            paginate: {
                first: 'First',
                last: 'Last',
                next: 'Next',
                previous: 'Previous'
            }
        },
        dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>' +
             '<"row"<"col-sm-12"tr>>' +
             '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>'
    };

    $('#invoices-table').DataTable(Object.assign({}, tableConfig, {
        order: [[1, 'desc']],
        columnDefs: [{ orderable: false, targets: [8] }]
    }));

    $('#returns-table').DataTable(Object.assign({}, tableConfig, {
        order: [[1, 'desc']],
        columnDefs: [{ orderable: false, targets: [7] }]
    }));

    $('#challans-table').DataTable(Object.assign({}, tableConfig, {
        order: [[1, 'desc']],
        columnDefs: [{ orderable: false, targets: [5] }]
    }));

    const purchaseSummaryTable = $('#purchase-summary-table').DataTable({
        processing: true,
        serverSide: true,
        searching: true,
        order: [[4, 'desc']],
        ajax: {
            url: '{{ route('customers.purchase-summary.data', $customer->id, false) }}',
            data: function(d) {
                d.category_id = $('#purchase-category-filter').val();
            }
        },
        columns: [
            { data: 'product_name', name: 'product_name' },
            { data: 'category_name', name: 'category_name' },
            { data: 'total_quantity', name: 'total_quantity', className: 'text-right' },
            { data: 'total_amount', name: 'total_amount', className: 'text-right', render: data => `৳${data}` },
            { data: 'last_purchase_date', name: 'last_purchase_date' }
        ],
        pageLength: 25,
        lengthChange: true
    });

    $('#purchase-category-filter').on('change', function() {
        purchaseSummaryTable.draw();
    });

    $('#return-items-table').DataTable({
        processing: true,
        serverSide: true,
        searching: true,
        order: [[1, 'desc']],
        ajax: {
            url: '{{ route('customers.return-items.data', $customer->id, false) }}'
        },
        columns: [
            {
                data: 'return_number',
                name: 'return_number',
                render: function(data, type, row) {
                    return `<a href="/returns/${row.return_id}" class="js-return-modal" data-return-id="${row.return_id}" data-return-number="${data}">${data}</a>`;
                }
            },
            { data: 'return_date', name: 'return_date' },
            {
                data: 'invoice_number',
                name: 'invoice_number',
                render: function(data, type, row) {
                    if (!data || !row.invoice_id) {
                        return '-';
                    }
                    return `<a href="/invoices/${row.invoice_id}" class="js-invoice-modal" data-invoice-id="${row.invoice_id}" data-invoice-number="${data}">${data}</a>`;
                }
            },
            { data: 'product_name', name: 'product_name' },
            { data: 'category_name', name: 'category_name' },
            { data: 'quantity', name: 'quantity', className: 'text-right' },
            { data: 'total', name: 'total', className: 'text-right', render: data => `৳${data}` }
        ],
        pageLength: 25,
        lengthChange: true
    });

    function openReturnModal(returnId, returnNumber) {
        $('#return-modal-number').text(returnNumber ? `#${returnNumber}` : '');
        $('#return-items-body').html('<tr><td colspan="4" class="text-center text-muted">Loading...</td></tr>');
        $('#return-modal-view').attr('href', `/returns/${returnId}`);
        $('#return-modal-print').attr('href', `/returns/${returnId}/print`);

        $.ajax({
            url: `/returns/${returnId}`,
            type: 'GET',
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            success: function(response) {
                const data = response.return || response;
                let rows = '';
                if (data.items && data.items.length) {
                    data.items.forEach(item => {
                        const name = item.product?.name || item.description || 'N/A';
                        rows += `<tr>
                            <td>${name}</td>
                            <td class="text-right">${parseFloat(item.quantity || 0).toFixed(2)}</td>
                            <td class="text-right">৳${parseFloat(item.unit_price || 0).toFixed(2)}</td>
                            <td class="text-right">৳${parseFloat(item.total || 0).toFixed(2)}</td>
                        </tr>`;
                    });
                } else {
                    rows = '<tr><td colspan="4" class="text-center text-muted">No items found.</td></tr>';
                }
                $('#return-items-body').html(rows);
            },
            error: function() {
                $('#return-items-body').html('<tr><td colspan="4" class="text-center text-danger">Failed to load items.</td></tr>');
            }
        });

        $('#returnItemsModal').modal('show');
    }

    function openInvoiceModal(invoiceId, invoiceNumber) {
        $('#invoice-modal-number').text(invoiceNumber ? `#${invoiceNumber}` : '');
        $('#invoice-items-body').html('<tr><td colspan="4" class="text-center text-muted">Loading...</td></tr>');
        $('#invoice-modal-view').attr('href', `/invoices/${invoiceId}`);
        $('#invoice-modal-print').attr('href', `/invoices/${invoiceId}/print`);

        $.ajax({
            url: `/invoices/${invoiceId}`,
            type: 'GET',
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            success: function(response) {
                const invoice = response.invoice || response;
                let rows = '';
                if (invoice.items && invoice.items.length) {
                    invoice.items.forEach(item => {
                        const name = item.product?.name || item.description || 'N/A';
                        rows += `<tr>
                            <td>${name}</td>
                            <td class="text-right">${parseFloat(item.quantity || 0).toFixed(2)}</td>
                            <td class="text-right">৳${parseFloat(item.unit_price || 0).toFixed(2)}</td>
                            <td class="text-right">৳${parseFloat(item.total || 0).toFixed(2)}</td>
                        </tr>`;
                    });
                } else {
                    rows = '<tr><td colspan="4" class="text-center text-muted">No items found.</td></tr>';
                }
                $('#invoice-items-body').html(rows);
            },
            error: function() {
                $('#invoice-items-body').html('<tr><td colspan="4" class="text-center text-danger">Failed to load items.</td></tr>');
            }
        });

        $('#invoiceItemsModal').modal('show');
    }

    $(document).on('click', '.js-return-modal', function(e) {
        e.preventDefault();
        const returnId = $(this).data('return-id');
        const returnNumber = $(this).data('return-number');
        if (returnId) {
            openReturnModal(returnId, returnNumber);
        }
    });

    $(document).on('click', '.js-invoice-modal', function(e) {
        e.preventDefault();
        const invoiceId = $(this).data('invoice-id');
        const invoiceNumber = $(this).data('invoice-number');
        if (invoiceId) {
            openInvoiceModal(invoiceId, invoiceNumber);
        }
    });
});
</script>
@stop
