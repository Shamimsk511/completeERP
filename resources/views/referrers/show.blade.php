@extends('layouts.modern-admin')

@section('title', 'Referrer Details')
@section('page_title', 'Referrer Details')

@section('header_actions')
    <a href="{{ route('referrers.edit', $referrer->id) }}" class="btn modern-btn modern-btn-primary">
        <i class="fas fa-edit"></i> Edit Referrer
    </a>
    <a href="{{ route('referrers.index') }}" class="btn modern-btn modern-btn-secondary">
        <i class="fas fa-arrow-left"></i> Back to Referrers
    </a>
@stop

@section('page_content')
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6">
            <div class="stats-card stats-card-primary">
                <div class="stats-icon">
                    <i class="fas fa-receipt"></i>
                </div>
                <div class="stats-content">
                    <h3 class="stats-number">{{ $summary['invoice_count'] ?? 0 }}</h3>
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
                    <h3 class="stats-number">৳{{ number_format($summary['total_sales'] ?? 0, 2) }}</h3>
                    <p class="stats-label">Total Sold</p>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="stats-card stats-card-info">
                <div class="stats-icon">
                    <i class="fas fa-hand-holding-usd"></i>
                </div>
                <div class="stats-content">
                    <h3 class="stats-number">৳{{ number_format($summary['total_collected'] ?? 0, 2) }}</h3>
                    <p class="stats-label">Collected</p>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="stats-card stats-card-warning">
                <div class="stats-icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div class="stats-content">
                    <h3 class="stats-number">৳{{ number_format($summary['total_due'] ?? 0, 2) }}</h3>
                    <p class="stats-label">Outstanding</p>
                </div>
            </div>
        </div>
    </div>

    <div class="card modern-card mb-4">
        <div class="card-header modern-header">
            <h3 class="card-title">
                <i class="fas fa-user-tag"></i> Referrer Information
            </h3>
            <div class="card-tools">
                <span class="modern-badge">ID: {{ $referrer->id }}</span>
            </div>
        </div>
        <div class="card-body modern-card-body">
            <div class="row">
                <div class="col-lg-6">
                    <div class="info-item">
                        <div class="info-label"><i class="fas fa-user text-primary"></i> Name</div>
                        <div class="info-value">{{ $referrer->name }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label"><i class="fas fa-phone text-success"></i> Phone</div>
                        <div class="info-value">{{ $referrer->phone ?: 'N/A' }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label"><i class="fas fa-briefcase text-info"></i> Profession</div>
                        <div class="info-value">{{ $referrer->profession ?: 'N/A' }}</div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="info-item">
                        <div class="info-label"><i class="fas fa-sticky-note text-warning"></i> Note</div>
                        <div class="info-value">{{ $referrer->note ?: 'N/A' }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label"><i class="fas fa-hand-holding-usd text-success"></i> Compensation</div>
                        <div class="info-value">
                            <span class="badge {{ $referrer->compensation_enabled ? 'badge-success' : 'badge-secondary' }}">
                                {{ $referrer->compensation_enabled ? 'Enabled' : 'Disabled' }}
                            </span>
                        </div>
                    </div>
                    <div class="info-item">
                        <div class="info-label"><i class="fas fa-gift text-purple"></i> Gift</div>
                        <div class="info-value">
                            <span class="badge {{ $referrer->gift_enabled ? 'badge-success' : 'badge-secondary' }}">
                                {{ $referrer->gift_enabled ? 'Enabled' : 'Disabled' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card modern-card mb-4">
        <div class="card-header modern-header">
            <h3 class="card-title">
                <i class="fas fa-filter"></i> Filters
            </h3>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('referrers.show', $referrer->id) }}">
                <div class="row">
                    <div class="col-md-3">
                        <label class="form-label">Invoice Type</label>
                        <select name="invoice_type" class="form-control modern-input">
                            <option value="">All</option>
                            <option value="tiles" {{ $invoiceType === 'tiles' ? 'selected' : '' }}>Tiles</option>
                            <option value="other" {{ $invoiceType === 'other' ? 'selected' : '' }}>Other</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Product Category</label>
                        <select name="category_id" class="form-control modern-input">
                            <option value="">All</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ (string) $categoryId === (string) $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Date From</label>
                        <input type="date" name="date_from" class="form-control modern-input" value="{{ $dateFrom }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Date To</label>
                        <input type="date" name="date_to" class="form-control modern-input" value="{{ $dateTo }}">
                    </div>
                </div>
                <div class="mt-3 d-flex gap-2">
                    <button type="submit" class="btn modern-btn modern-btn-primary">
                        <i class="fas fa-search"></i> Apply
                    </button>
                    <a href="{{ route('referrers.show', $referrer->id) }}" class="btn modern-btn modern-btn-secondary">
                        <i class="fas fa-times"></i> Clear
                    </a>
                </div>
            </form>
        </div>
    </div>

    <div class="card modern-card">
        <div class="card-header modern-header success-header">
            <h3 class="card-title">
                <i class="fas fa-receipt"></i> Referral Invoices
            </h3>
            <div class="card-tools">
                <span class="modern-badge">{{ $summary['invoice_count'] ?? 0 }} Invoices</span>
                <span class="modern-badge">{{ $summary['compensated_count'] ?? 0 }} Compensated</span>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table modern-table mb-0">
                    <thead>
                        <tr>
                            <th>Invoice #</th>
                            <th>Date</th>
                            <th>Customer</th>
                            <th>Type</th>
                            <th>Categories</th>
                            <th>Total</th>
                            <th>Paid</th>
                            <th>Due</th>
                            <th>Compensated</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($invoices as $invoice)
                            @php
                                $categoryNames = $invoice->items->map(function($item) {
                                    return optional(optional($item->product)->category)->name;
                                })->filter()->unique()->values();
                            @endphp
                            <tr>
                                <td>{{ $invoice->invoice_number }}</td>
                                <td>{{ $invoice->invoice_date ? $invoice->invoice_date->format('Y-m-d') : '' }}</td>
                                <td>{{ $invoice->customer->name ?? 'N/A' }}</td>
                                <td>
                                    <span class="badge badge-{{ $invoice->invoice_type === 'tiles' ? 'info' : 'secondary' }}">
                                        {{ ucfirst($invoice->invoice_type) }}
                                    </span>
                                </td>
                                <td>{{ $categoryNames->implode(', ') ?: 'N/A' }}</td>
                                <td>৳{{ number_format($invoice->total, 2) }}</td>
                                <td>৳{{ number_format($invoice->paid_amount, 2) }}</td>
                                <td>৳{{ number_format($invoice->due_amount, 2) }}</td>
                                <td>
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox"
                                               class="custom-control-input referrer-comp-toggle"
                                               id="comp-{{ $invoice->id }}"
                                               data-referrer="{{ $referrer->id }}"
                                               data-invoice="{{ $invoice->id }}"
                                               {{ $invoice->referrer_compensated ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="comp-{{ $invoice->id }}"></label>
                                    </div>
                                </td>
                                <td>
                                    <a href="{{ route('invoices.show', $invoice->id) }}" class="btn btn-sm btn-info">View</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center text-muted py-4">No invoices found for this referrer.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@stop

@section('additional_js')
<script>
$(document).ready(function() {
    $('.referrer-comp-toggle').on('change', function() {
        const $toggle = $(this);
        const referrerId = $toggle.data('referrer');
        const invoiceId = $toggle.data('invoice');
        const isChecked = $toggle.is(':checked');

        $.ajax({
            url: `/referrers/${referrerId}/invoices/${invoiceId}/compensation`,
            method: 'PATCH',
            data: {
                referrer_compensated: isChecked ? 1 : 0,
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            error: function() {
                $toggle.prop('checked', !isChecked);
                alert('Failed to update compensation status.');
            }
        });
    });
});
</script>
@stop
