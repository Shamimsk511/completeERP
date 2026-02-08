@extends('customer.layout')

@section('title', 'Dashboard')

@section('content')
<div class="container-fluid">
    <div class="portal-header">
        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
            <div>
                <div class="d-flex align-items-center gap-3">
                    <div class="stat-chip">
                        <i class="fas fa-circle text-success"></i>
                        {{ $customer->last_login_at?->format('M d, Y H:i') ?? 'First time here' }}
                    </div>
                    <div class="stat-chip">
                        ID: {{ $customer->id }}
                    </div>
                </div>
                <h1 class="portal-title mt-3">Hello {{ $customer->name }}, your account at a glance.</h1>
                <p class="portal-subtitle">Track invoices, monitor outstanding balance, and manage payments in one place.</p>
            </div>
            <div class="d-flex gap-2 flex-wrap">
                <a href="{{ route('customer.invoices') }}" class="btn btn-primary quick-pill">
                    <i class="fas fa-file-invoice me-2"></i>Invoices
                </a>
                <a href="{{ route('customer.ledger') }}" class="btn quick-pill quick-pill-outline">
                    <i class="fas fa-book me-2"></i>Ledger
                </a>
                <a href="{{ route('customer.profile') }}" class="btn quick-pill quick-pill-outline">
                    <i class="fas fa-user me-2"></i>Profile
                </a>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-12 col-md-6 col-lg-3" data-reveal>
            <div class="card glass-card p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-uppercase text-muted mb-1" style="font-size:0.75rem; letter-spacing:0.1em;">Total Invoices</p>
                        <h3 class="mb-0">{{ $stats['total_invoices'] }}</h3>
                    </div>
                    <div class="rounded-circle bg-info bg-opacity-10 text-info d-flex align-items-center justify-content-center" style="width:48px;height:48px;">
                        <i class="fas fa-file-invoice"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6 col-lg-3" data-reveal>
            <div class="card glass-card p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-uppercase text-muted mb-1" style="font-size:0.75rem; letter-spacing:0.1em;">Outstanding</p>
                        <h3 class="mb-0">৳{{ number_format($stats['pending_amount'], 2) }}</h3>
                    </div>
                    <div class="rounded-circle bg-warning bg-opacity-10 text-warning d-flex align-items-center justify-content-center" style="width:48px;height:48px;">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6 col-lg-3" data-reveal>
            <div class="card glass-card p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-uppercase text-muted mb-1" style="font-size:0.75rem; letter-spacing:0.1em;">Total Paid</p>
                        <h3 class="mb-0">৳{{ number_format($stats['total_paid'], 2) }}</h3>
                    </div>
                    <div class="rounded-circle bg-success bg-opacity-10 text-success d-flex align-items-center justify-content-center" style="width:48px;height:48px;">
                        <i class="fas fa-wallet"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6 col-lg-3" data-reveal>
            <div class="card glass-card p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-uppercase text-muted mb-1" style="font-size:0.75rem; letter-spacing:0.1em;">Customer Since</p>
                        <h3 class="mb-0">{{ $customer->created_at->format('Y') }}</h3>
                    </div>
                    <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center" style="width:48px;height:48px;">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-12 col-lg-6" data-reveal>
            <div class="card glass-card p-4 h-100">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0"><i class="fas fa-clock me-2 text-info"></i>Recent Transactions</h5>
                    <a href="{{ route('customer.ledger') }}" class="text-decoration-none text-info">View all</a>
                </div>
                @if($stats['recent_transactions']->count() > 0)
                    <div class="list-group list-group-flush">
                        @foreach($stats['recent_transactions'] as $transaction)
                        <div class="list-group-item border-0 d-flex justify-content-between align-items-center px-0">
                            <div>
                                <div class="fw-semibold">{{ $transaction->created_at->format('M d, Y') }}</div>
                                <small class="text-muted">{{ ucfirst($transaction->type) }} transaction</small>
                            </div>
                            <div class="text-end">
                                <span class="badge bg-{{ $transaction->type == 'credit' ? 'danger' : 'success' }} bg-opacity-10 text-{{ $transaction->type == 'credit' ? 'danger' : 'success' }}">
                                    {{ ucfirst($transaction->type) }}
                                </span>
                                <div class="fw-semibold mt-1">৳{{ number_format($transaction->amount, 2) }}</div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center text-muted py-4">
                        <i class="fas fa-info-circle me-2"></i>No transactions yet.
                    </div>
                @endif
            </div>
        </div>

        <div class="col-12 col-lg-6" data-reveal>
            <div class="card glass-card p-4 h-100">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0"><i class="fas fa-file-invoice me-2 text-primary"></i>Recent Invoices</h5>
                    <a href="{{ route('customer.invoices') }}" class="text-decoration-none text-primary">View all</a>
                </div>
                @if($stats['recent_invoices']->count() > 0)
                    <div class="list-group list-group-flush">
                        @foreach($stats['recent_invoices'] as $invoice)
                        <div class="list-group-item border-0 d-flex justify-content-between align-items-center px-0">
                            <div>
                                <div class="fw-semibold">
                                    <a href="{{ route('customer.invoices.show', $invoice->id) }}" class="text-decoration-none text-dark">
                                        Invoice #{{ $invoice->id }}
                                    </a>
                                </div>
                                <small class="text-muted">{{ $invoice->created_at->format('M d, Y') }}</small>
                            </div>
                            <div class="fw-semibold">৳{{ number_format($invoice->total ?? $invoice->total_amount ?? 0, 2) }}</div>
                        </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center text-muted py-4">
                        <i class="fas fa-info-circle me-2"></i>No invoices yet.
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const items = document.querySelectorAll('[data-reveal]');
    items.forEach((item, index) => {
        item.style.opacity = '0';
        item.style.transform = 'translateY(16px)';
        item.style.transition = 'all 0.6s ease';
        setTimeout(() => {
            item.style.opacity = '1';
            item.style.transform = 'translateY(0)';
        }, 120 + (index * 120));
    });
});
</script>
@endpush
