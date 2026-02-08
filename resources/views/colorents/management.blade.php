@extends('layouts.modern-admin')

@section('title', 'Colorent Management')

@section('page_title', 'Colorent Stock & Price Management')

@section('header_actions')
    <div class="colorent-header-actions">
        <a href="{{ route('colorents.create') }}" class="btn modern-btn modern-btn-outline colorent-action-btn">
            <i class="fas fa-plus"></i> Add Colorent
        </a>
        <button type="button" class="btn modern-btn modern-btn-success colorent-action-btn" id="open-purchase-modal">
            <i class="fas fa-cart-plus"></i> Purchase Colorent
        </button>
        <button type="button" class="btn modern-btn modern-btn-info colorent-action-btn" onclick="refreshTotals()">
            <i class="fas fa-sync-alt"></i> Refresh Totals
        </button>
    </div>
@stop

@section('page_content')
    <div class="colorent-shell">
        <section class="colorent-hero">
            <div class="hero-copy">
                <span class="hero-kicker"><i class="fas fa-swatchbook"></i> Tinting Room</span>
                <h2 class="hero-title">Colorent Inventory</h2>
                <p class="hero-subtitle">
                    Track stock, purchase from suppliers, and pour into the tinting machine without accounting impact.
                </p>
                <div class="hero-hints">
                    <span><i class="fas fa-mouse-pointer"></i> Click price to edit</span>
                    <span><i class="fas fa-cart-plus"></i> Purchase adds stock</span>
                    <span><i class="fas fa-fill-drip"></i> Pour 1 reduces stock</span>
                    <span><i class="fas fa-receipt"></i> Purchases update supplier balance</span>
                </div>
            </div>
            <div class="hero-metrics">
                <div class="metric-card">
                    <div class="metric-icon">
                        <i class="fas fa-boxes"></i>
                    </div>
                    <div>
                        <p class="metric-label">Total Stock Units</p>
                        <h3 class="metric-value" id="total-stock">{{ number_format($totalStock) }}</h3>
                    </div>
                </div>
                <div class="metric-card metric-accent">
                    <div class="metric-icon">
                        <i class="fas fa-coins"></i>
                    </div>
                    <div>
                        <p class="metric-label">Inventory Value</p>
                        <h3 class="metric-value" id="total-value">৳{{ number_format($totalValue, 2) }}</h3>
                    </div>
                </div>
            </div>
        </section>

    <!-- Success/Error Messages -->
    <div id="alert-container"></div>
    @if (session('success'))
        <div class="alert alert-success modern-alert">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger modern-alert">
            <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
        </div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger modern-alert">
            <i class="fas fa-exclamation-circle"></i> Please fix the highlighted errors.
        </div>
    @endif

    <!-- Main Management Table -->
    <div class="card modern-card colorent-card">
        <div class="card-header colorent-card-header">
            <div>
                <h3 class="card-title">
                    <i class="fas fa-palette"></i> All Colorents
                </h3>
                <p class="card-subtitle">Keep stock balanced for tinting machine usage.</p>
            </div>
            <div class="card-tools">
                <button type="button" class="btn btn-tool colorent-tool-btn" onclick="refreshTotals()">
                    <i class="fas fa-sync-alt"></i>
                </button>
            </div>
        </div>
        <div class="card-body modern-card-body">
            <div class="table-container">
                <div class="table-responsive colorent-table-responsive">
                    <table class="table colorent-table">
                        <thead class="colorent-thead">
                            <tr>
                                <th width="32%">
                                    <div class="th-content">
                                        <i class="fas fa-tag"></i>
                                        <span>Product Name</span>
                                    </div>
                                </th>
                                <th width="20%">
                                    <div class="th-content">
                                        <i class="fas fa-boxes"></i>
                                        <span>Stock</span>
                                    </div>
                                </th>
                                <th width="20%">
                                    <div class="th-content">
                                        <i class="fas fa-dollar-sign"></i>
                                        <span>Price (৳)</span>
                                    </div>
                                </th>
                                <th width="15%">
                                    <div class="th-content">
                                        <i class="fas fa-calculator"></i>
                                        <span>Value (৳)</span>
                                    </div>
                                </th>
                                <th width="15%">
                                    <div class="th-content">
                                        <i class="fas fa-tools"></i>
                                        <span>Actions</span>
                                    </div>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="colorent-tbody">
                            @foreach($colorents as $colorent)
                            <tr id="colorent-{{ $colorent->id }}" class="colorent-row">
                                <td>
                                    <div class="product-info">
                                        <div class="d-flex align-items-center gap-2">
                                            <strong class="product-name">{{ $colorent->name }}</strong>
                                            <a href="{{ route('colorents.edit', $colorent->id) }}" class="colorent-edit-link" title="Edit name">
                                                <i class="fas fa-pen"></i>
                                            </a>
                                        </div>
                                        <small class="text-muted d-block">
                                            <i class="fas fa-palette"></i> Colorent Product
                                        </small>
                                    </div>
                                </td>
                                
                                <!-- Stock Management -->
                                <td>
                                    <div class="stock-pill">
                                        <span class="stock-count" id="stock-{{ $colorent->id }}">{{ number_format($colorent->stock) }}</span>
                                        <span class="stock-label">units</span>
                                    </div>
                                </td>
                                
                                <!-- Price Management -->
                                <td>
                                    <div class="price-container">
                                        <span class="price-display" 
                                              data-id="{{ $colorent->id }}" 
                                              title="Click to edit price">
                                            ৳{{ number_format($colorent->price, 2) }}
                                        </span>
                                        
                                        <div class="price-edit" 
                                             data-id="{{ $colorent->id }}" 
                                             style="display: none;">
                                            <div class="input-group modern-input-group-sm">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text modern-input-addon">৳</span>
                                                </div>
                                                <input type="number" 
                                                       class="form-control modern-input-sm price-input" 
                                                       data-id="{{ $colorent->id }}"
                                                       value="{{ $colorent->price }}"
                                                       step="0.01"
                                                       min="0">
                                                <div class="input-group-append">
                                                    <button class="btn action-btn action-btn-success save-price-btn" 
                                                            data-id="{{ $colorent->id }}"
                                                            title="Save Price">
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                    <button class="btn action-btn action-btn-secondary cancel-price-btn" 
                                                            data-id="{{ $colorent->id }}"
                                                            title="Cancel">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                
                                <!-- Item Value -->
                                <td>
                                    <span class="value-badge item-value" data-id="{{ $colorent->id }}">
                                        ৳{{ number_format($colorent->stock * $colorent->price, 2) }}
                                    </span>
                                </td>
                                <td>
                                    <div class="action-stack">
                                        <button type="button"
                                                class="btn action-btn action-btn-success action-btn-sm purchase-colorent-btn"
                                                data-colorent-id="{{ $colorent->id }}"
                                                data-colorent-name="{{ $colorent->name }}"
                                                title="Purchase Colorent">
                                            <i class="fas fa-cart-plus"></i>
                                        </button>
                                        <form action="{{ route('colorents.usage.store') }}" method="POST" class="d-inline">
                                            @csrf
                                            <input type="hidden" name="colorent_id" value="{{ $colorent->id }}">
                                            <input type="hidden" name="quantity" value="1">
                                            <input type="hidden" name="used_at" value="{{ date('Y-m-d') }}">
                                            <input type="hidden" name="notes" value="Quick pour (1 unit)">
                                            <button type="submit"
                                                    class="btn action-btn action-btn-warning action-btn-sm"
                                                    title="Pour 1 unit to machine"
                                                    {{ $colorent->stock <= 0 ? 'disabled' : '' }}>
                                                <i class="fas fa-fill-drip"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Audit Trail -->
    <div class="card modern-card colorent-card">
        <div class="card-header colorent-card-header">
            <div>
                <h3 class="card-title">
                    <i class="fas fa-clipboard-list"></i> Colorent Movement Log
                </h3>
                <p class="card-subtitle">Latest 50 stock additions and pours.</p>
            </div>
            <div class="movement-header-actions">
                <a href="{{ route('colorents.management.export', request()->query()) }}" class="btn modern-btn modern-btn-outline movement-export-btn">
                    <i class="fas fa-file-export"></i> Export CSV
                </a>
            </div>
        </div>
        <div class="card-body modern-card-body">
            <form method="GET" action="{{ route('colorents.management') }}" class="movement-filter-form">
                <div class="movement-filter-row">
                    <div class="filter-group">
                        <label for="filter_colorent">Colorent</label>
                        <select name="colorent_id" id="filter_colorent" class="form-control modern-input">
                            <option value="">All</option>
                            @foreach($colorents as $colorent)
                                <option value="{{ $colorent->id }}" @selected(($filters['colorent_id'] ?? '') == $colorent->id)>
                                    {{ $colorent->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="filter-group">
                        <label for="filter_type">Type</label>
                        <select name="movement_type" id="filter_type" class="form-control modern-input">
                            <option value="">All</option>
                            <option value="in" @selected(($filters['movement_type'] ?? '') === 'in')>Added</option>
                            <option value="out" @selected(($filters['movement_type'] ?? '') === 'out')>Poured</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label for="filter_from">From</label>
                        <input type="date" name="date_from" id="filter_from" class="form-control modern-input" value="{{ $filters['date_from'] ?? '' }}">
                    </div>
                    <div class="filter-group">
                        <label for="filter_to">To</label>
                        <input type="date" name="date_to" id="filter_to" class="form-control modern-input" value="{{ $filters['date_to'] ?? '' }}">
                    </div>
                    <div class="filter-actions">
                        <button type="submit" class="btn modern-btn modern-btn-primary">
                            <i class="fas fa-filter"></i> Apply
                        </button>
                        <a href="{{ route('colorents.management') }}" class="btn modern-btn modern-btn-outline">
                            Reset
                        </a>
                    </div>
                </div>
            </form>
            <div class="table-container">
                <div class="table-responsive colorent-table-responsive">
                    <table class="table colorent-table">
                        <thead class="colorent-thead">
                            <tr>
                                <th width="12%">Date</th>
                                <th width="18%">Colorent</th>
                                <th width="10%">Type</th>
                                <th width="10%">Qty</th>
                                <th width="12%">Unit Cost</th>
                                <th width="14%">Total</th>
                                <th width="14%">Reference</th>
                                <th width="20%">Source / By</th>
                            </tr>
                        </thead>
                        <tbody class="colorent-tbody">
                            @forelse($movements as $movement)
                                <tr>
                                    <td>{{ $movement['date']->format('Y-m-d') }}</td>
                                    <td>{{ $movement['colorent'] }}</td>
                                    <td>
                                        <span class="badge {{ $movement['type'] === 'in' ? 'badge-success' : 'badge-warning' }}">
                                            {{ $movement['type'] === 'in' ? 'Added' : 'Poured' }}
                                        </span>
                                    </td>
                                    <td>{{ number_format($movement['quantity']) }}</td>
                                    <td>
                                        @if($movement['unit_cost'] !== null)
                                            ৳{{ number_format($movement['unit_cost'], 2) }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>
                                        @if($movement['total'] !== null)
                                            ৳{{ number_format($movement['total'], 2) }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>{{ $movement['reference'] ?? '-' }}</td>
                                    <td>
                                        <div class="movement-meta">
                                            <div class="movement-source">{{ $movement['source'] }}</div>
                                            <div class="movement-user">by {{ $movement['user'] }}</div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted">No movements recorded yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Purchase Colorent Modal -->
    <div class="modal fade" id="purchaseColorentModal" tabindex="-1" role="dialog" aria-labelledby="purchaseColorentModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <form action="{{ route('colorents.purchases.store') }}" method="POST" id="purchase-colorent-form">
                @csrf
                <div class="modal-content modern-modal">
                    <div class="modal-header">
                        <h5 class="modal-title" id="purchaseColorentModalLabel">
                            <i class="fas fa-cart-plus"></i> Purchase Colorent
                        </h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-5">
                                <div class="form-group">
                                    <label for="purchase_payee_id">Supplier <span class="text-danger">*</span></label>
                                    <select name="payee_id" id="purchase_payee_id" class="form-control modern-select" required>
                                        <option value="">Select Supplier</option>
                                        @foreach($payees as $payee)
                                            <option value="{{ $payee->id }}"
                                                data-balance="{{ $payee->current_balance ?? 0 }}"
                                                @selected($defaultPayeeId && $defaultPayeeId === $payee->id)>
                                                {{ $payee->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <small class="text-muted d-block mt-1">
                                        Current Balance: <span id="purchase-payee-balance">৳0.00</span>
                                    </small>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="purchase_transaction_date">Date <span class="text-danger">*</span></label>
                                    <input type="date" name="transaction_date" id="purchase_transaction_date" class="form-control modern-input" value="{{ date('Y-m-d') }}" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="purchase_reference_no">Reference No</label>
                                    <input type="text" name="reference_no" id="purchase_reference_no" class="form-control modern-input" placeholder="Invoice/PO">
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="purchase_notes">Notes</label>
                            <textarea name="notes" id="purchase_notes" rows="2" class="form-control modern-input" placeholder="Optional notes"></textarea>
                        </div>

                        <div class="purchase-items-card">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <strong>Items</strong>
                                <button type="button" class="btn modern-btn modern-btn-outline btn-sm" id="add-purchase-item">
                                    <i class="fas fa-plus"></i> Add Item
                                </button>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered purchase-items-table">
                                    <thead>
                                        <tr>
                                            <th>Colorent</th>
                                            <th width="100">Qty</th>
                                            <th width="130">Unit Cost</th>
                                            <th width="130">Line Total</th>
                                            <th width="110">Update Price</th>
                                            <th width="60"></th>
                                        </tr>
                                    </thead>
                                    <tbody id="purchase-items-body">
                                        <tr class="purchase-item-row">
                                            <td>
                                                <select name="items[0][colorent_id]" class="form-control form-control-sm purchase-colorent-select">
                                                    <option value="">Select</option>
                                                    @foreach($colorents as $colorent)
                                                        <option value="{{ $colorent->id }}" data-price="{{ $colorent->price }}">
                                                            {{ $colorent->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td>
                                                <input type="number" name="items[0][quantity]" class="form-control form-control-sm purchase-qty" min="1" value="1">
                                            </td>
                                            <td>
                                                <input type="number" name="items[0][unit_cost]" class="form-control form-control-sm purchase-unit-cost" min="0.01" step="0.01">
                                            </td>
                                            <td class="purchase-line-total">৳0.00</td>
                                            <td class="text-center">
                                                <input type="checkbox" name="items[0][update_price]" class="purchase-update-price" value="1" @checked($canUpdatePrice) @disabled(!$canUpdatePrice)>
                                            </td>
                                            <td class="text-center">
                                                <button type="button" class="btn btn-sm btn-danger remove-purchase-item" title="Remove">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="text-right mt-2">
                                <strong>Total: <span id="purchase-grand-total">৳0.00</span></strong>
                            </div>
                            @if(!$canUpdatePrice)
                                <small class="text-muted d-block mt-2">
                                    Price update is disabled (missing "update prices" permission).
                                </small>
                            @endif
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn modern-btn modern-btn-outline" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn modern-btn modern-btn-primary">
                            <i class="fas fa-save"></i> Save Purchase
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Loading Overlay -->
    <div id="loading-overlay" class="loading-overlay" style="display: none;">
        <div class="loading-content">
            <div class="spinner"></div>
            <span>Updating...</span>
        </div>
    </div>
    </div>
@stop

@section('additional_css')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.min.css">
    
    <style>
        :root {
            --colorent-ink: #0f172a;
            --colorent-muted: #64748b;
            --colorent-surface: #ffffff;
            --colorent-border: #e2e8f0;
            --colorent-bg: #f8fafc;
            --colorent-primary: #0f766e;
            --colorent-primary-strong: #115e59;
            --colorent-accent: #f59e0b;
            --colorent-accent-strong: #b45309;
        }

        .colorent-header-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .colorent-action-btn {
            box-shadow: 0 8px 18px rgba(15, 118, 110, 0.18);
            border: none;
        }

        .colorent-shell {
            position: relative;
            display: flex;
            flex-direction: column;
            gap: 24px;
        }

        .colorent-shell::before {
            content: '';
            position: absolute;
            left: -40px;
            right: -40px;
            top: -80px;
            height: 220px;
            background:
                radial-gradient(circle at 15% 35%, rgba(20, 184, 166, 0.25), transparent 55%),
                radial-gradient(circle at 80% 0%, rgba(245, 158, 11, 0.18), transparent 50%);
            z-index: 0;
            pointer-events: none;
        }

        .colorent-shell > section,
        .colorent-shell > .card {
            position: relative;
            z-index: 1;
        }

        .colorent-hero {
            position: relative;
            overflow: hidden;
            border-radius: 20px;
            padding: 24px 26px;
            background: linear-gradient(120deg, #0f766e 0%, #14b8a6 55%, #5eead4 100%);
            color: #f8fafc;
            display: grid;
            grid-template-columns: minmax(0, 2fr) minmax(0, 1fr);
            gap: 24px;
            box-shadow: 0 20px 30px rgba(15, 118, 110, 0.25);
        }

        .colorent-hero::after {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(120deg, rgba(15, 118, 110, 0.85), rgba(20, 184, 166, 0.65));
            opacity: 0.8;
            z-index: 0;
            pointer-events: none;
        }

        .hero-copy,
        .hero-metrics {
            position: relative;
            z-index: 1;
        }

        .hero-kicker {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 11px;
            letter-spacing: 0.18em;
            text-transform: uppercase;
            color: rgba(255, 255, 255, 0.8);
            padding: 4px 10px;
            border-radius: 999px;
            border: 1px solid rgba(255, 255, 255, 0.25);
            background: rgba(15, 118, 110, 0.35);
        }

        .hero-title {
            font-size: 28px;
            font-weight: 700;
            margin: 10px 0 6px;
            color: #ffffff;
        }

        .hero-subtitle {
            font-size: 14px;
            color: rgba(255, 255, 255, 0.85);
            margin: 0 0 12px;
            max-width: 520px;
        }

        .hero-hints {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            font-size: 12px;
            color: rgba(255, 255, 255, 0.85);
        }

        .hero-hints span {
            padding: 4px 10px;
            border-radius: 999px;
            background: rgba(15, 118, 110, 0.35);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .hero-metrics {
            display: grid;
            gap: 12px;
        }

        .metric-card {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 14px;
            border-radius: 16px;
            background: rgba(255, 255, 255, 0.12);
            border: 1px solid rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(6px);
        }

        .metric-accent {
            border-color: rgba(251, 191, 36, 0.7);
        }

        .metric-icon {
            width: 46px;
            height: 46px;
            border-radius: 14px;
            background: rgba(255, 255, 255, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            color: #ffffff;
        }

        .metric-label {
            margin: 0;
            font-size: 11px;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: rgba(255, 255, 255, 0.7);
        }

        .metric-value {
            margin: 0;
            font-size: 24px;
            font-weight: 700;
            color: #ffffff;
        }

        .colorent-card {
            border-radius: 18px;
            overflow: hidden;
            border: 1px solid var(--colorent-border);
            box-shadow: 0 12px 24px rgba(15, 23, 42, 0.08);
            background: var(--colorent-surface);
        }

        .colorent-card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 16px 20px;
            background: var(--colorent-surface);
            border-bottom: 1px solid var(--colorent-border);
        }

        .colorent-card-header .card-title {
            font-size: 16px;
            font-weight: 700;
            color: var(--colorent-ink);
            margin: 0;
        }

        .card-subtitle {
            margin: 4px 0 0;
            font-size: 12px;
            color: var(--colorent-muted);
        }

        .colorent-tool-btn {
            background: var(--colorent-bg);
            color: var(--colorent-primary);
            border: 1px solid var(--colorent-border);
            border-radius: 8px;
            padding: 4px 10px;
        }

        .table-container {
            position: relative;
            overflow: hidden;
            border-radius: 14px;
        }

        .colorent-table-responsive {
            border-radius: 14px;
            overflow: hidden;
            background: var(--colorent-surface);
            border: 1px solid var(--colorent-border);
        }

        .colorent-table {
            margin-bottom: 0;
            width: 100%;
            border-collapse: separate;
            border-spacing: 0 8px;
            color: var(--colorent-ink);
        }

        .colorent-thead th {
            border: none;
            padding: 14px 12px;
            font-weight: 600;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #f8fafc;
            background: #0f172a;
        }

        .th-content {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            color: #f8fafc;
            text-align: center;
        }

        .colorent-tbody tr {
            background: var(--colorent-surface);
            transition: all 0.2s ease;
        }

        .colorent-tbody tr:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(15, 23, 42, 0.08);
        }

        .colorent-tbody td {
            padding: 12px;
            vertical-align: middle;
            font-size: 13px;
            color: var(--colorent-ink);
            border-top: 1px solid var(--colorent-border);
            border-bottom: 1px solid var(--colorent-border);
            background: var(--colorent-surface);
        }

        .colorent-tbody td:first-child {
            border-left: 1px solid var(--colorent-border);
            border-radius: 12px 0 0 12px;
        }

        .colorent-tbody td:last-child {
            border-right: 1px solid var(--colorent-border);
            border-radius: 0 12px 12px 0;
        }

        .product-name {
            color: var(--colorent-ink);
            font-size: 14px;
        }

        .modern-input-group-sm {
            display: flex;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 10px rgba(15, 23, 42, 0.06);
            max-width: 150px;
        }

        .modern-input-sm {
            border: 1px solid var(--colorent-border);
            border-radius: 0;
            padding: 6px 8px;
            font-size: 13px;
            background: #ffffff;
            color: var(--colorent-ink);
            border-left: none;
            border-right: none;
        }

        .modern-input-sm:focus {
            outline: none;
            border-color: var(--colorent-primary);
            box-shadow: 0 0 0 2px rgba(15, 118, 110, 0.15);
            z-index: 1;
        }

        .modern-input-addon {
            background: var(--colorent-bg);
            border: 1px solid var(--colorent-border);
            color: var(--colorent-muted);
            font-weight: 600;
            font-size: 13px;
            padding: 6px 8px;
            display: flex;
            align-items: center;
        }

        .action-btn {
            padding: 6px 10px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 4px;
            transition: all 0.2s ease;
            border: 1px solid transparent;
            cursor: pointer;
            min-width: 32px;
        }

        .action-btn:hover {
            transform: translateY(-1px);
            text-decoration: none;
        }

        .action-btn-success {
            background: linear-gradient(135deg, var(--colorent-primary) 0%, #14b8a6 100%);
            color: #ffffff;
        }

        .action-btn-danger {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: #ffffff;
        }

        .action-btn-warning {
            background: linear-gradient(135deg, var(--colorent-accent) 0%, #fbbf24 100%);
            color: #1f2937;
        }

        .action-btn-secondary {
            background: linear-gradient(135deg, #64748b 0%, #475569 100%);
            color: #ffffff;
        }

        .action-btn-sm {
            padding: 4px 6px;
            font-size: 11px;
            min-width: 28px;
        }

        .action-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            transform: none;
        }

        .action-stack {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 6px;
        }

        .price-container {
            position: relative;
        }

        .colorent-edit-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 24px;
            height: 24px;
            border-radius: 8px;
            background: #f8fafc;
            border: 1px solid var(--colorent-border);
            color: var(--colorent-muted);
            font-size: 11px;
            transition: all 0.2s ease;
        }

        .colorent-edit-link:hover {
            color: var(--colorent-primary);
            border-color: rgba(15, 118, 110, 0.4);
            background: #ecfeff;
        }

        .stock-pill {
            display: inline-flex;
            align-items: baseline;
            gap: 6px;
            padding: 8px 12px;
            border-radius: 999px;
            background: #f1f5f9;
            border: 1px solid var(--colorent-border);
            font-weight: 700;
            color: var(--colorent-ink);
        }

        .stock-count {
            font-size: 14px;
        }

        .stock-label {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: var(--colorent-muted);
        }

        .price-display {
            display: inline-block;
            background: #ecfeff;
            color: #0f766e;
            padding: 8px 12px;
            border-radius: 10px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s ease;
            border: 1px solid rgba(15, 118, 110, 0.2);
        }

        .price-display:hover {
            background: #cffafe;
            border-color: rgba(15, 118, 110, 0.5);
            transform: translateY(-1px);
        }

        .value-badge {
            display: inline-block;
            background: #0f172a;
            color: #f8fafc;
            padding: 8px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 700;
        }

        .loading-overlay {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #ffffff;
            padding: 16px 20px;
            border-radius: 12px;
            box-shadow: 0 12px 30px rgba(15, 23, 42, 0.15);
            z-index: 1000;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .loading-content {
            display: flex;
            align-items: center;
            gap: 12px;
            color: var(--colorent-ink);
            font-weight: 600;
        }

        .spinner {
            width: 20px;
            height: 20px;
            border: 2px solid rgba(15, 118, 110, 0.2);
            border-radius: 50%;
            border-top-color: var(--colorent-primary);
            animation: spin 1s ease-in-out infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        .toast-success {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        }

        .toast-error {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        }

        .toast-warning {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        }

        .toast-info {
            background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);
        }

        .modern-modal .modal-header {
            background: linear-gradient(120deg, #0f766e 0%, #14b8a6 100%);
            color: #ffffff;
            border-bottom: none;
        }

        .modern-modal .modal-header .close {
            color: #ffffff;
            opacity: 0.9;
        }

        .purchase-items-table th {
            background: var(--colorent-bg);
        }

        .purchase-items-card {
            border: 1px solid var(--colorent-border);
            border-radius: 12px;
            padding: 12px;
            background: #f8fafc;
        }

        .modern-alert {
            border-radius: 12px;
            padding: 12px 16px;
        }

        .movement-meta {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .movement-source {
            font-weight: 600;
            color: var(--colorent-ink);
        }

        .movement-user {
            font-size: 11px;
            color: var(--colorent-muted);
            text-transform: uppercase;
            letter-spacing: 0.06em;
        }

        .movement-header-actions {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .movement-export-btn {
            border-radius: 10px;
            font-weight: 600;
        }

        .movement-filter-form {
            margin-bottom: 16px;
            padding: 12px 14px;
            border-radius: 12px;
            background: var(--colorent-bg);
            border: 1px solid var(--colorent-border);
        }

        .movement-filter-row {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr)) auto;
            gap: 12px;
            align-items: end;
        }

        .filter-group label {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: var(--colorent-muted);
            margin-bottom: 4px;
        }

        .filter-actions {
            display: flex;
            gap: 8px;
            align-items: center;
        }

        @media (max-width: 992px) {
            .colorent-hero {
                grid-template-columns: 1fr;
            }

            .movement-filter-row {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .filter-actions {
                grid-column: span 2;
                justify-content: flex-start;
            }
        }

        @media (max-width: 768px) {
            .hero-title {
                font-size: 22px;
            }

            .colorent-tbody td {
                padding: 8px 6px;
                font-size: 12px;
            }

            .colorent-thead th {
                padding: 12px 8px;
                font-size: 10px;
            }

            .modern-input-group-sm {
                max-width: 120px;
            }

            .action-btn {
                padding: 4px 6px;
                font-size: 11px;
                min-width: 28px;
            }

            .price-display {
                padding: 6px 8px;
                font-size: 12px;
            }

            .value-badge {
                padding: 6px 8px;
                font-size: 11px;
            }

            .movement-filter-row {
                grid-template-columns: 1fr;
            }

            .filter-actions {
                grid-column: span 1;
            }
        }
    </style>
@stop

@section('additional_js')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"></script>
    
    <script>
        $(document).ready(function() {
            // Configure Toastr
            toastr.options = {
                "closeButton": true,
                "debug": false,
                "newestOnTop": true,
                "progressBar": true,
                "positionClass": "toast-top-right",
                "preventDuplicates": false,
                "onclick": null,
                "showDuration": "300",
                "hideDuration": "1000",
                "timeOut": "5000",
                "extendedTimeOut": "1000",
                "showEasing": "swing",
                "hideEasing": "linear",
                "showMethod": "fadeIn",
                "hideMethod": "fadeOut"
            };

            // Setup CSRF token
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            // Price display clicks
            $('.price-display').click(function() {
                const id = $(this).data('id');
                showPriceEdit(id);
            });

            // Save price button
            $('.save-price-btn').click(function() {
                const id = $(this).data('id');
                const newPrice = $(`.price-input[data-id="${id}"]`).val();
                updatePrice(id, newPrice);
            });

            // Cancel price edit
            $('.cancel-price-btn').click(function() {
                const id = $(this).data('id');
                hidePriceEdit(id);
            });

            // Enter key on price input
            $(document).on('keypress', '.price-input', function(e) {
                if (e.which === 13) {
                    const id = $(this).data('id');
                    const newPrice = $(this).val();
                    updatePrice(id, newPrice);
                }
            });

            // Escape key to cancel price edit
            $(document).on('keyup', '.price-input', function(e) {
                if (e.which === 27) {
                    const id = $(this).data('id');
                    hidePriceEdit(id);
                }
            });

            function updatePrice(id, newPrice) {
                showLoading();
                
                $.ajax({
                    url: `/colorents/${id}/update-price`,
                    method: 'POST',
                    data: { price: newPrice },
                    success: function(response) {
                        if (response.success) {
                            $(`.price-display[data-id="${id}"]`).text(`৳${response.new_price}`);
                            
                            // Update totals with animation
                            animateNumberChange($('#total-stock'), response.total_stock);
                            animateNumberChange($('#total-value'), `৳${response.total_value}`);
                            
                            // Update item value
                            const stock = parseInt($(`#stock-${id}`).text().replace(/,/g, '')) || 0;
                            const itemValue = stock * parseFloat(newPrice);
                            $(`.item-value[data-id="${id}"]`).text('৳' + itemValue.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
                            
                            hidePriceEdit(id);
                            toastr.success('Price updated successfully!');
                        }
                    },
                    error: function(xhr) {
                        toastr.error('Error updating price!');
                        console.error('Error:', xhr);
                    },
                    complete: function() {
                        hideLoading();
                    }
                });
            }

            function showPriceEdit(id) {
                $(`.price-display[data-id="${id}"]`).hide();
                $(`.price-edit[data-id="${id}"]`).show();
                $(`.price-input[data-id="${id}"]`).focus().select();
            }

            function hidePriceEdit(id) {
                $(`.price-edit[data-id="${id}"]`).hide();
                $(`.price-display[data-id="${id}"]`).show();
            }

            function showLoading() {
                $('#loading-overlay').fadeIn(200);
            }

            function hideLoading() {
                $('#loading-overlay').fadeOut(200);
            }

            function animateNumberChange($element, newValue) {
                $element.addClass('updating');
                setTimeout(() => {
                    $element.text(newValue);
                    $element.removeClass('updating');
                }, 100);
            }

            const canUpdatePrice = @json($canUpdatePrice);
            const defaultPayeeId = @json($defaultPayeeId);
            const $purchaseItemsBody = $('#purchase-items-body');
            const $purchaseRowTemplate = $purchaseItemsBody.find('tr').first().clone();

            function updatePayeeBalance() {
                const balance = parseFloat($('#purchase_payee_id option:selected').data('balance')) || 0;
                $('#purchase-payee-balance').text(`৳${balance.toFixed(2)}`);
            }

            function reindexPurchaseItems() {
                $purchaseItemsBody.find('tr').each(function(index) {
                    $(this).find('.purchase-colorent-select').attr('name', `items[${index}][colorent_id]`);
                    $(this).find('.purchase-qty').attr('name', `items[${index}][quantity]`);
                    $(this).find('.purchase-unit-cost').attr('name', `items[${index}][unit_cost]`);
                    $(this).find('.purchase-update-price').attr('name', `items[${index}][update_price]`);
                });
            }

            function updatePurchaseRowTotal($row) {
                const qty = parseFloat($row.find('.purchase-qty').val()) || 0;
                const cost = parseFloat($row.find('.purchase-unit-cost').val()) || 0;
                const lineTotal = qty * cost;
                $row.find('.purchase-line-total').text(`৳${lineTotal.toFixed(2)}`);
            }

            function updatePurchaseTotal() {
                let total = 0;
                $purchaseItemsBody.find('tr').each(function() {
                    const qty = parseFloat($(this).find('.purchase-qty').val()) || 0;
                    const cost = parseFloat($(this).find('.purchase-unit-cost').val()) || 0;
                    total += qty * cost;
                });
                $('#purchase-grand-total').text(`৳${total.toFixed(2)}`);
            }

            function createPurchaseRow() {
                const $row = $purchaseRowTemplate.clone();
                $row.find('.purchase-colorent-select').val('');
                $row.find('.purchase-qty').val(1);
                $row.find('.purchase-unit-cost').val('').data('auto', true);
                $row.find('.purchase-line-total').text('৳0.00');

                const $updateCheckbox = $row.find('.purchase-update-price');
                if (!canUpdatePrice) {
                    $updateCheckbox.prop('checked', false).prop('disabled', true);
                } else {
                    $updateCheckbox.prop('checked', true).prop('disabled', false);
                }

                return $row;
            }

            function addPurchaseRow(prefillColorentId = null) {
                const $row = createPurchaseRow();

                const $select = $row.find('.purchase-colorent-select');
                if (prefillColorentId) {
                    $select.val(prefillColorentId);
                }
                if (!$select.val() && $select.find('option').length > 1) {
                    $select.prop('selectedIndex', 1);
                }

                const price = parseFloat($select.find(':selected').data('price')) || 0;
                const $unitCost = $row.find('.purchase-unit-cost');
                $unitCost.val(price ? price.toFixed(2) : '').data('auto', true);

                $purchaseItemsBody.append($row);
                reindexPurchaseItems();
                updatePurchaseRowTotal($row);
                updatePurchaseTotal();
            }

            function resetPurchaseForm(prefillColorentId = null) {
                $('#purchase_reference_no').val('');
                $('#purchase_notes').val('');
                $('#purchase_transaction_date').val(new Date().toISOString().slice(0, 10));
                if (defaultPayeeId) {
                    $('#purchase_payee_id').val(defaultPayeeId);
                }
                updatePayeeBalance();
                const $rows = $purchaseItemsBody.find('tr');
                $rows.slice(1).remove();
                const $firstRow = $rows.first();
                if ($firstRow.length === 0) {
                    $purchaseItemsBody.append(createPurchaseRow());
                }
                const $row = $purchaseItemsBody.find('tr').first();
                $row.find('.purchase-colorent-select').val('');
                $row.find('.purchase-qty').val(1);
                $row.find('.purchase-unit-cost').val('').data('auto', true);
                $row.find('.purchase-line-total').text('৳0.00');

                const $updateCheckbox = $row.find('.purchase-update-price');
                if (!canUpdatePrice) {
                    $updateCheckbox.prop('checked', false).prop('disabled', true);
                } else {
                    $updateCheckbox.prop('checked', true).prop('disabled', false);
                }

                if (prefillColorentId) {
                    $row.find('.purchase-colorent-select').val(prefillColorentId);
                }

                const price = parseFloat($row.find('.purchase-colorent-select :selected').data('price')) || 0;
                if (price) {
                    $row.find('.purchase-unit-cost').val(price.toFixed(2)).data('auto', true);
                    $row.find('.purchase-line-total').text(`৳${(price * 1).toFixed(2)}`);
                }

                reindexPurchaseItems();
                updatePurchaseTotal();
            }

            $('#purchase_payee_id').on('change', updatePayeeBalance);
            $('#add-purchase-item').on('click', function() {
                addPurchaseRow();
            });

            $purchaseItemsBody.on('click', '.remove-purchase-item', function() {
                $(this).closest('tr').remove();
                if ($purchaseItemsBody.find('tr').length === 0) {
                    addPurchaseRow();
                }
                reindexPurchaseItems();
                updatePurchaseTotal();
            });

            $purchaseItemsBody.on('change', '.purchase-colorent-select', function() {
                const $row = $(this).closest('tr');
                const price = parseFloat($(this).find(':selected').data('price')) || 0;
                const $unitCost = $row.find('.purchase-unit-cost');
                if ($unitCost.data('auto') !== false) {
                    $unitCost.val(price ? price.toFixed(2) : '').data('auto', true);
                }
                updatePurchaseRowTotal($row);
                updatePurchaseTotal();
            });

            $purchaseItemsBody.on('input', '.purchase-unit-cost', function() {
                $(this).data('auto', false);
                const $row = $(this).closest('tr');
                updatePurchaseRowTotal($row);
                updatePurchaseTotal();
            });

            $purchaseItemsBody.on('input', '.purchase-qty', function() {
                const $row = $(this).closest('tr');
                updatePurchaseRowTotal($row);
                updatePurchaseTotal();
            });

            $('#open-purchase-modal').on('click', function() {
                resetPurchaseForm();
                $('#purchaseColorentModal').modal('show');
            });

            $('.purchase-colorent-btn').on('click', function() {
                const colorentId = $(this).data('colorent-id');
                resetPurchaseForm(colorentId);
                $('#purchaseColorentModal').modal('show');
            });
            updatePayeeBalance();

            // Refresh totals function
            window.refreshTotals = function() {
                showLoading();
                setTimeout(() => {
                    location.reload();
                }, 500);
            };
        });

        // Add CSS for updating animation
        const style = document.createElement('style');
        style.textContent = `
            .updating {
                transform: scale(1.1);
                transition: transform 0.2s ease;
            }
        `;
        document.head.appendChild(style);
    </script>
@stop
