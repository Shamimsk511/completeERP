@extends('layouts.modern-admin')

@section('title', 'Return Details')
@section('page_title', 'Return Details')

@section('header_actions')
    <a class="btn modern-btn modern-btn-secondary" href="{{ route('returns.index') }}">
        <i class="fas fa-arrow-left"></i> Back to Returns
    </a>
@stop

@section('page_content')
    <!-- Return Overview Cards -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6">
            <div class="stats-card stats-card-danger">
                <div class="stats-icon">
                    <i class="fas fa-undo"></i>
                </div>
                <div class="stats-content">
                    <h3 class="stats-number">{{ $return->return_number }}</h3>
                    <p class="stats-label">Return Number</p>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6">
            <div class="stats-card stats-card-info">
                <div class="stats-icon">
                    <i class="fas fa-calendar"></i>
                </div>
                <div class="stats-content">
                    <h3 class="stats-number">{{ $return->return_date->format('d M') }}</h3>
                    <p class="stats-label">Return Date</p>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6">
            <div class="stats-card stats-card-warning">
                <div class="stats-icon">
                    <i class="fas fa-dollar-sign"></i>
                </div>
                <div class="stats-content">
                    <h3 class="stats-number">৳{{ number_format($return->total, 2) }}</h3>
                    <p class="stats-label">Return Amount</p>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6">
            <div class="stats-card stats-card-success">
                <div class="stats-icon">
                    <i class="fas fa-boxes"></i>
                </div>
                <div class="stats-content">
                    <h3 class="stats-number">{{ $return->items->count() }}</h3>
                    <p class="stats-label">Total Items</p>
                </div>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible modern-alert">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
            <i class="fas fa-check-circle"></i>
            {{ session('success') }}
        </div>
    @endif

    <!-- Return Information Card -->
    <div class="card modern-card mb-4">
        <div class="card-header modern-header">
            <h3 class="card-title">
                <i class="fas fa-undo"></i> Return Information
            </h3>
            <div class="card-tools">
                <span class="modern-badge">ID: {{ $return->id }}</span>
                <a href="{{ route('returns.edit', $return) }}" class="btn modern-btn modern-btn-warning btn-sm">
                    <i class="fas fa-edit"></i> Edit
                </a>
                <a href="{{ route('returns.print', $return) }}" class="btn modern-btn modern-btn-secondary btn-sm" target="_blank">
                    <i class="fas fa-print"></i> Print
                </a>
            </div>
        </div>
        <div class="card-body modern-card-body">
            <div class="row">
                <div class="col-lg-6">
                    <div class="info-item">
                        <div class="info-label">
                            <i class="fas fa-hashtag text-danger"></i> Return Number
                        </div>
                        <div class="info-value return-number">{{ $return->return_number }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">
                            <i class="fas fa-calendar text-info"></i> Return Date
                        </div>
                        <div class="info-value">{{ $return->return_date->format('d M, Y') }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">
                            <i class="fas fa-info-circle text-primary"></i> Status
                        </div>
                        <div class="info-value">
                            @if($return->status == 'completed')
                                <span class="status-badge status-success">Completed</span>
                            @elseif($return->status == 'pending')
                                <span class="status-badge status-warning">Pending</span>
                            @else
                                <span class="status-badge status-info">{{ ucfirst($return->status) }}</span>
                            @endif
                        </div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">
                            <i class="fas fa-credit-card text-success"></i> Payment Method
                        </div>
                        <div class="info-value">{{ ucfirst(str_replace('_', ' ', $return->payment_method)) }}</div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="info-item">
                        <div class="info-label">
                            <i class="fas fa-receipt text-warning"></i> Related Invoice
                        </div>
                        <div class="info-value">
                            @if($return->invoice_id)
                                <a href="{{ route('invoices.show', $return->invoice_id) }}" class="invoice-link">
                                    <i class="fas fa-file-invoice"></i> {{ $return->invoice->invoice_number }}
                                </a>
                            @else
                                <span class="text-muted">No Related Invoice</span>
                            @endif
                        </div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">
                            <i class="fas fa-dollar-sign text-danger"></i> Return Amount
                        </div>
                        <div class="info-value balance-amount text-danger">
                            ৳{{ number_format($return->total, 2) }}
                        </div>
                    </div>
                    @if($return->notes)
                    <div class="info-item">
                        <div class="info-label">
                            <i class="fas fa-sticky-note text-info"></i> Notes
                        </div>
                        <div class="info-value">{{ $return->notes }}</div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Customer Information Card -->
    <div class="card modern-card mb-4">
        <div class="card-header modern-header info-header">
            <h3 class="card-title">
                <i class="fas fa-user"></i> Customer Information
            </h3>
            <div class="card-tools">
                <a href="{{ route('customers.show', $return->customer->id) }}" class="btn modern-btn modern-btn-info btn-sm">
                    <i class="fas fa-eye"></i> View Customer
                </a>
            </div>
        </div>
        <div class="card-body modern-card-body">
            <div class="row">
                <div class="col-lg-6">
                    <div class="info-item">
                        <div class="info-label">
                            <i class="fas fa-user text-primary"></i> Customer Name
                        </div>
                        <div class="info-value customer-name">{{ $return->customer->name }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">
                            <i class="fas fa-phone text-success"></i> Phone Number
                        </div>
                        <div class="info-value">{{ $return->customer->phone ?: 'N/A' }}</div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="info-item">
                        <div class="info-label">
                            <i class="fas fa-map-marker-alt text-danger"></i> Address
                        </div>
                        <div class="info-value">{{ $return->customer->address ?: 'No address provided' }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">
                            <i class="fas fa-wallet text-warning"></i> Current Balance
                        </div>
                        <div class="info-value balance-amount {{ $return->customer->outstanding_balance > 0 ? 'text-danger' : ($return->customer->outstanding_balance < 0 ? 'text-info' : 'text-success') }}">
                            ৳{{ number_format($return->customer->outstanding_balance, 2) }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Return Items Card -->
    <div class="card modern-card mb-4">
        <div class="card-header modern-header success-header">
            <h3 class="card-title">
                <i class="fas fa-boxes"></i> Return Items
            </h3>
            <div class="card-tools">
                <span class="modern-badge">{{ $return->items->count() }} Items</span>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table modern-table mb-0">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Description</th>
                            <th>Category</th>
                            <th>Company</th>
                            <th>Quantity</th>
                            <th>Unit Price</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($return->items as $item)
                        <tr>
                            <td>
                                <span class="product-name">{{ $item->product->name }}</span>
                            </td>
                            <td>{{ $item->description }}</td>
                            <td>
                                @if($item->product->category)
                                    <span class="category-badge">{{ $item->product->category->name }}</span>
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </td>
                            <td>
                                @if($item->product->company)
                                    <span class="badge badge-info">{{ $item->product->company->name }}</span>
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </td>
                            <td>
                                <span class="count-badge text-danger">{{ number_format($item->quantity, 2) }}</span>
                            </td>
                            <td>
                                <span class="amount-text">৳{{ number_format($item->unit_price, 2) }}</span>
                            </td>
                            <td>
                                <span class="amount-text text-danger">৳{{ number_format($item->total, 2) }}</span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="table-totals">
                            <th colspan="6">Subtotal:</th>
                            <th>৳{{ number_format($return->subtotal, 2) }}</th>
                        </tr>
                        @if($return->tax > 0)
                        <tr class="table-totals">
                            <th colspan="6">Tax:</th>
                            <th>৳{{ number_format($return->tax, 2) }}</th>
                        </tr>
                        @endif
                        <tr class="table-totals total-row">
                            <th colspan="6">Total Return Amount:</th>
                            <th class="text-danger">৳{{ number_format($return->total, 2) }}</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <!-- Return Summary Card -->
    <div class="card modern-card">
        <div class="card-header modern-header danger-header">
            <h3 class="card-title">
                <i class="fas fa-chart-pie"></i> Return Summary
            </h3>
        </div>
        <div class="card-body modern-card-body">
            <div class="row">
                <div class="col-md-3">
                    <div class="summary-item">
                        <div class="summary-icon bg-info">
                            <i class="fas fa-boxes"></i>
                        </div>
                        <div class="summary-content">
                            <h4>{{ $return->items->count() }}</h4>
                            <p>Total Items</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="summary-item">
                        <div class="summary-icon bg-warning">
                            <i class="fas fa-weight"></i>
                        </div>
                        <div class="summary-content">
                            <h4>{{ number_format($return->items->sum('quantity'), 2) }}</h4>
                            <p>Total Quantity</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="summary-item">
                        <div class="summary-icon bg-success">
                            <i class="fas fa-dollar-sign"></i>
                        </div>
                        <div class="summary-content">
                            <h4>৳{{ number_format($return->subtotal, 2) }}</h4>
                            <p>Subtotal</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="summary-item">
                        <div class="summary-icon bg-danger">
                            <i class="fas fa-receipt"></i>
                        </div>
                        <div class="summary-content">
                            <h4>৳{{ number_format($return->total, 2) }}</h4>
                            <p>Total Return</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('additional_css')
<link rel="stylesheet" href="/css/modern-admin.css">

<style>
/* Return-specific styles */
.return-number {
    font-size: 1.25rem;
    color: #dc2626;
    font-weight: 700;
}

.invoice-link {
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

.invoice-link:hover {
    background: rgba(102, 126, 234, 0.1);
    color: #5a67d8;
    text-decoration: none;
}

/* Summary items styling */
.summary-item {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem;
    background: rgba(102, 126, 234, 0.02);
    border-radius: var(--border-radius);
    border-left: 4px solid transparent;
    transition: all 0.3s ease;
}

.summary-item:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.summary-icon {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.5rem;
    flex-shrink: 0;
}

.summary-content h4 {
    font-size: 1.5rem;
    font-weight: 700;
    margin: 0;
    color: #374151;
}

.summary-content p {
    font-size: 0.875rem;
    color: #6b7280;
    margin: 0;
    font-weight: 500;
}

/* Total row emphasis */
.total-row {
    background: linear-gradient(135deg, rgba(220, 38, 38, 0.1), rgba(185, 28, 28, 0.1));
    font-size: 1.1rem;
}

.total-row th {
    border-top: 2px solid #dc2626;
    padding: 1rem 0.75rem;
    font-weight: 700;
}

/* Stats card variations for return */
.stats-card-danger::before {
    background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
}

.stats-card-danger .stats-icon {
    background: rgba(220, 38, 38, 0.1);
    color: #dc2626;
}

/* Modern alert styling */
.modern-alert {
    border: none;
    border-radius: 8px;
    padding: 1rem 1.5rem;
    margin-bottom: 1.5rem;
    display: flex;
    align-items: center;
    gap: 0.75rem;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.modern-alert.alert-success {
    background: linear-gradient(135deg, rgba(16, 185, 129, 0.1), rgba(5, 150, 105, 0.1));
    border-left: 4px solid #10b981;
    color: #065f46;
}

.modern-alert i {
    font-size: 1.25rem;
}

/* Responsive design */
@media (max-width: 768px) {
    .summary-item {
        flex-direction: column;
        text-align: center;
        gap: 0.5rem;
    }
    
    .summary-icon {
        width: 50px;
        height: 50px;
        font-size: 1.25rem;
    }
    
    .summary-content h4 {
        font-size: 1.25rem;
    }
    
    .card-tools {
        flex-direction: column;
        gap: 0.5rem;
    }
    
    .card-tools .btn {
        width: 100%;
        justify-content: center;
    }
}

/* Print styles */
@media print {
    .card-tools,
    .modern-btn,
    .btn {
        display: none !important;
    }
    
    .stats-card {
        break-inside: avoid;
    }
    
    .modern-card {
        break-inside: avoid;
        margin-bottom: 1rem;
    }
}
</style>
@stop

@section('additional_js')
<script src="/js/modern-admin.js"></script>

<script>
$(document).ready(function() {
    // Initialize tooltips
    $('[data-toggle="tooltip"]').tooltip();
    
    // Add copy to clipboard functionality for return number
    $('.return-number').on('click', function() {
        const text = $(this).text().trim();
        navigator.clipboard.writeText(text).then(() => {
            ModernAdmin.showAlert(`Copied "${text}" to clipboard`, 'success', 2000);
        }).catch(() => {
            ModernAdmin.showAlert('Failed to copy to clipboard', 'error', 2000);
        });
    });
    
    // Add hover effects for interactive elements
    $('.invoice-link').hover(
        function() {
            $(this).addClass('shadow-sm');
        },
        function() {
            $(this).removeClass('shadow-sm');
        }
    );
    
    // Animate summary items on scroll
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.animation = 'fadeInUp 0.6s ease-out';
            }
        });
    }, observerOptions);
    
    $('.summary-item').each(function() {
        observer.observe(this);
    });
    
    // Add print functionality
    $('.btn[href*="print"]').on('click', function(e) {
        e.preventDefault();
        const printUrl = $(this).attr('href');
        const printWindow = window.open(printUrl, '_blank');
        
        printWindow.onload = function() {
            setTimeout(() => {
                printWindow.print();
            }, 500);
        };
    });
    
    console.log('Return details page initialized successfully');
});

// CSS animation for scroll effects
const style = document.createElement('style');
style.textContent = `
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
`;
document.head.appendChild(style);
</script>
@stop
