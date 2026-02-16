@extends('layouts.modern-admin')

@section('title', 'View Challan')

@section('page_title', 'Challan: ' . $challan->challan_number)

@section('header_actions')
    <div class="header-actions-group">
        <a href="{{ route('challans.index') }}" class="btn modern-btn modern-btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to List
        </a>
        <a href="{{ route('challans.edit', $challan) }}" class="btn modern-btn modern-btn-warning">
            <i class="fas fa-edit"></i> Edit
        </a>
        <a href="{{ route('challans.print', $challan) }}" target="_blank" class="btn modern-btn modern-btn-outline">
            <i class="fas fa-print"></i> Print Challan
        </a>
        @if(Auth::user()->hasRole('Admin') || Auth::user()->can('challan-delete'))
        <button type="button" class="btn modern-btn modern-btn-danger" onclick="confirmDelete()">
            <i class="fas fa-trash"></i> Delete
        </button>
        @endif
    </div>
@stop

@section('page_content')
    <!-- Challan Overview Cards -->
    <div class="row mb-4">
        <!-- Basic Information -->
        <div class="col-lg-8">
            <div class="card modern-card">
                <div class="card-header modern-header challan-info-header">
                    <h3 class="card-title">
                        <i class="fas fa-file-alt"></i> Challan Information
                    </h3>
                    <div class="header-badge">
                        <span class="status-badge badge-success">
                            <i class="fas fa-check-circle"></i>
                            Delivered: {{ $challan->delivered_at ? $challan->delivered_at->format('d M Y, H:i') : $challan->created_at->format('d M Y, H:i') }}
                        </span>
                    </div>
                </div>
                <div class="card-body modern-card-body">
                    <div class="info-grid">
                        <div class="info-item">
                            <div class="info-label">
                                <i class="fas fa-hashtag info-icon"></i>
                                Challan Number
                            </div>
                            <div class="info-value">{{ $challan->challan_number }}</div>
                        </div>
                        
                        <div class="info-item">
                            <div class="info-label">
                                <i class="fas fa-calendar info-icon"></i>
                                Challan Date
                            </div>
                            <div class="info-value">{{ $challan->challan_date->format('d M Y') }}</div>
                        </div>
                        
                        <div class="info-item">
                            <div class="info-label">
                                <i class="fas fa-user info-icon"></i>
                                Customer
                            </div>
                            <div class="info-value">{{ $challan->invoice->customer->name }}</div>
                        </div>
                        
                        <div class="info-item">
                            <div class="info-label">
                                <i class="fas fa-user-check info-icon"></i>
                                Receiver
                            </div>
                            <div class="info-value">{{ $challan->receiver_name }}</div>
                        </div>
                        
                        @if($challan->receiver_phone)
                        <div class="info-item">
                            <div class="info-label">
                                <i class="fas fa-phone info-icon"></i>
                                Receiver Phone
                            </div>
                            <div class="info-value">{{ $challan->receiver_phone }}</div>
                        </div>
                        @endif
                        
                        <div class="info-item full-width">
                            <div class="info-label">
                                <i class="fas fa-map-marker-alt info-icon"></i>
                                Shipping Address
                            </div>
                            <div class="info-value">{{ $challan->shipping_address }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Quick Stats -->
        <div class="col-lg-4">
            <div class="card modern-card quick-stats-card">
                <div class="card-header modern-header stats-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-pie"></i> Quick Stats
                    </h3>
                </div>
                <div class="card-body modern-card-body quick-stats-body">
                    <div class="stats-container">
                        <div class="stat-card">
                            <div class="stat-number">{{ $challan->items->count() }}</div>
                            <div class="stat-label">Total Items</div>
                        </div>
                        
                        <div class="stat-card">
                            <div class="stat-number">{{ number_format($challan->items->sum('quantity'), 2) }}</div>
                            <div class="stat-label">Total Quantity</div>
                        </div>
                        
                        @if($challan->items->whereNotNull('boxes')->count() > 0)
                        <div class="stat-card">
                            <div class="stat-number">{{ $challan->items->sum('boxes') }}</div>
                            <div class="stat-label">Total Boxes</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-number">{{ $challan->items->sum('pieces') }}</div>
                            <div class="stat-label">Total Pieces</div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            
            <!-- Delivery Information -->
            @if($challan->vehicle_number || $challan->driver_name || $challan->driver_phone)
            <div class="card modern-card mt-3">
                <div class="card-header modern-header delivery-header">
                    <h3 class="card-title">
                        <i class="fas fa-truck"></i> Delivery Details
                    </h3>
                </div>
                <div class="card-body modern-card-body">
                    @if($challan->vehicle_number)
                    <div class="delivery-item">
                        <i class="fas fa-car delivery-icon"></i>
                        <div>
                            <div class="delivery-label">Vehicle Number</div>
                            <div class="delivery-value">{{ $challan->vehicle_number }}</div>
                        </div>
                    </div>
                    @endif
                    
                    @if($challan->driver_name)
                    <div class="delivery-item">
                        <i class="fas fa-user-tie delivery-icon"></i>
                        <div>
                            <div class="delivery-label">Driver Name</div>
                            <div class="delivery-value">{{ $challan->driver_name }}</div>
                        </div>
                    </div>
                    @endif
                    
                    @if($challan->driver_phone)
                    <div class="delivery-item">
                        <i class="fas fa-mobile-alt delivery-icon"></i>
                        <div>
                            <div class="delivery-label">Driver Phone</div>
                            <div class="delivery-value">{{ $challan->driver_phone }}</div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
            @endif
        </div>
    </div>
    
    <!-- Items Table -->
    @php
        $totalWeight = 0;
        foreach ($challan->items as $item) {
            if (!$item->product) {
                continue;
            }
            $category = $item->product->category ?? null;
            if (!$category || !$category->weight_value || !$category->weight_unit) {
                continue;
            }
            $weightValue = (float) $category->weight_value;
            $weightUnit = $category->weight_unit;
            $boxPcs = (float) ($category->box_pcs ?? 0);
            $piecesFeet = (float) ($category->pieces_feet ?? 0);
            $quantity = (float) $item->quantity;
            $boxes = (float) ($item->boxes ?? 0);
            $pieces = (float) ($item->pieces ?? 0);

            $totalPieces = 0;
            if ($boxPcs > 0) {
                $totalPieces = ($boxes * $boxPcs) + $pieces;
            } elseif ($pieces > 0) {
                $totalPieces = $pieces;
            } elseif ($piecesFeet > 0 && $quantity > 0) {
                $totalPieces = $quantity / $piecesFeet;
            }

            if ($weightUnit === 'per_piece') {
                $totalWeight += $totalPieces * $weightValue;
            } elseif ($weightUnit === 'per_box') {
                $boxCount = $boxPcs > 0 ? ($totalPieces / $boxPcs) : $boxes;
                $totalWeight += $boxCount * $weightValue;
            } elseif ($weightUnit === 'per_unit') {
                $totalWeight += $quantity * $weightValue;
            }
        }
    @endphp
    <div class="card modern-card">
        <div class="card-header modern-header items-header">
            <h3 class="card-title">
                <i class="fas fa-boxes"></i> Items for Delivery
            </h3>
        </div>
        <div class="card-body modern-card-body p-0">
            <div class="table-container">
                <div class="table-responsive modern-table-responsive">
                    <table class="table modern-table">
                        <thead class="modern-thead">
                            <tr>
                                <th>
                                    <div class="th-content">
                                        <span>Product</span>
                                    </div>
                                </th>
                                <th>
                                    <div class="th-content">
                                        <span>Description</span>
                                    </div>
                                </th>
                                <th>
                                    <div class="th-content">
                                        <span>Godown</span>
                                    </div>
                                </th>
                                <th class="text-center">
                                    <div class="th-content">
                                        <span>Quantity</span>
                                    </div>
                                </th>
                                <th class="text-center">
                                    <div class="th-content">
                                        <span>Boxes</span>
                                    </div>
                                </th>
                                <th class="text-center">
                                    <div class="th-content">
                                        <span>Pieces</span>
                                    </div>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="modern-tbody">
                            @forelse($challan->items as $item)
                            <tr>
                                <td>
                                    <div class="product-info">
                                        <div class="product-name">{{ $item->product->name ?? $item->description ?? 'Product' }}</div>
                                        @if($item->product && $item->product->category)
                                            <div class="product-category">{{ $item->product->category->name }}</div>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <div class="description-cell">
                                        {{ $item->description ?? '-' }}
                                    </div>
                                </td>
                                <td>
                                    @if($item->godown)
                                        <div class="description-cell">
                                            {{ $item->godown->name }}
                                            @if($item->godown->location)
                                                <div class="text-muted small">{{ $item->godown->location }}</div>
                                            @endif
                                        </div>
                                    @else
                                        <span class="empty-value">-</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <span class="quantity-badge">{{ number_format($item->quantity, 2) }}</span>
                                </td>
                                <td class="text-center">
                                    @if($item->boxes !== null)
                                        <span class="boxes-badge">{{ $item->boxes }}</span>
                                    @else
                                        <span class="empty-value">-</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($item->pieces !== null)
                                        <span class="pieces-badge">{{ $item->pieces }}</span>
                                    @else
                                        <span class="empty-value">-</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">
                                    No items found for this challan.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                        <tfoot class="modern-tfoot">
                            <tr>
                                <td colspan="3" style="text-align: right; font-weight: 600;">
                                    <strong>TOTAL:</strong>
                                </td>
                                <td class="text-center">
                                    <span class="quantity-badge">{{ number_format($challan->items->sum('quantity'), 2) }}</span>
                                </td>
                                <td class="text-center">
                                    <div class="boxes-badge">{{ $challan->items->sum('boxes') ?? 0 }}</div>
                                    <small class="text-muted d-block mt-1">Apprx. {{ number_format($totalWeight, 2) }} kg</small>
                                </td>
                                <td class="text-center">
                                    <span class="pieces-badge">{{ $challan->items->sum('pieces') ?? 0 }}</span>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Notes Section -->
    @if($challan->notes)
    <div class="card modern-card mt-4">
        <div class="card-header modern-header notes-header">
            <h3 class="card-title">
                <i class="fas fa-sticky-note"></i> Notes
            </h3>
        </div>
        <div class="card-body modern-card-body">
            <div class="notes-content">
                {{ $challan->notes }}
            </div>
        </div>
    </div>
    @endif

    <!-- Hidden Delete Form -->
    @if(Auth::user()->hasRole('Admin') || Auth::user()->can('challan-delete'))
    <form id="delete-form" action="{{ route('challans.destroy', $challan) }}" method="POST" style="display: none;">
        @csrf
        @method('DELETE')
    </form>
    @endif
@stop

@section('additional_css')
<style>
    /* Header Actions Group */
    .header-actions-group {
        display: flex;
        gap: 12px;
        align-items: center;
    }

    /* Section Headers */
    .challan-info-header {
        background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
    }

    .stats-header {
        background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
    }

    .delivery-header {
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
    }

    .items-header {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    }

    .notes-header {
        background: linear-gradient(135deg, #6b7280 0%, #4b5563 100%);
    }

    /* Header Badge */
    .header-badge {
        margin-left: auto;
    }

    .status-badge {
        font-size: 12px;
        font-weight: 600;
        padding: 8px 16px;
        border-radius: 25px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border: none;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
    }

    .badge-warning {
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        color: white;
    }

    .badge-success {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: white;
    }

    .badge-danger {
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        color: white;
    }

    /* Info Grid */
    .info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 24px;
    }

    .info-item.full-width {
        grid-column: 1 / -1;
    }

    .info-item {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .info-label {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 12px;
        font-weight: 600;
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .info-icon {
        font-size: 14px;
        color: #9ca3af;
    }

    .info-value {
        font-size: 15px;
        font-weight: 500;
        color: #374151;
        line-height: 1.5;
    }

    /* Stats Container */
    .quick-stats-card .stats-header {
        padding: 12px 16px;
    }

    .quick-stats-card .card-title {
        font-size: 16px;
        margin: 0;
    }

    .quick-stats-body {
        padding: 12px;
    }

    .stats-container {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 10px;
    }

    .stat-card {
        background: rgba(139, 92, 246, 0.05);
        border: 1px solid rgba(139, 92, 246, 0.1);
        border-radius: 10px;
        padding: 12px 10px;
        text-align: center;
        transition: all 0.2s ease;
    }

    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(139, 92, 246, 0.15);
    }

    .stat-number {
        font-size: 18px;
        font-weight: 700;
        color: #8b5cf6;
        margin-bottom: 2px;
        line-height: 1.2;
    }

    .stat-label {
        font-size: 10px;
        font-weight: 500;
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: 0.4px;
        line-height: 1.2;
    }

    /* Delivery Items */
    .delivery-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px 0;
        border-bottom: 1px solid #f1f5f9;
    }

    .delivery-item:last-child {
        border-bottom: none;
    }

    .delivery-icon {
        font-size: 16px;
        color: #f59e0b;
        width: 20px;
        text-align: center;
    }

    .delivery-label {
        font-size: 12px;
        font-weight: 500;
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .delivery-value {
        font-size: 14px;
        font-weight: 500;
        color: #374151;
    }

    /* Modern Table Styles */
    .table-container {
        position: relative;
        overflow: hidden;
        border-radius: 12px;
        background: white;
    }

    .modern-table-responsive {
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        background: white;
    }

    .modern-table {
        margin-bottom: 0;
        background: white !important;
        color: #1f2937 !important;
        width: 100%;
    }

    .modern-thead {
        background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%) !important;
        border-bottom: none;
    }

    .modern-thead th {
        border: none !important;
        padding: 16px;
        font-weight: 600;
        font-size: 13px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: white !important;
        background: transparent !important;
    }

    .th-content {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        color: white;
    }

    .modern-tbody {
        background: white !important;
    }

    .modern-tbody tr {
        transition: all 0.2s ease;
        border-bottom: 1px solid #f1f5f9;
        background: white !important;
    }

    .modern-tbody tr:hover {
        background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%) !important;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(99, 102, 241, 0.1);
    }

    .modern-tbody td {
        padding: 16px;
        vertical-align: middle;
        border: none !important;
        font-size: 14px;
        color: #374151 !important;
        background: transparent !important;
    }

    .modern-tfoot {
        background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%) !important;
    }

    .modern-tfoot td {
        padding: 16px;
        border-top: 2px solid #6366f1 !important;
        font-weight: 600;
        color: #374151 !important;
    }

    /* Product Info */
    .product-info {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .product-name {
        font-weight: 600;
        color: #374151;
    }

    .product-category {
        font-size: 12px;
        color: #6b7280;
        font-style: italic;
    }

    /* Description Cell */
    .description-cell {
        max-width: 200px;
        word-wrap: break-word;
        line-height: 1.4;
    }

    /* Badges */
    .quantity-badge, .boxes-badge, .pieces-badge {
        display: inline-block;
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: white;
        padding: 6px 12px;
        border-radius: 15px;
        font-size: 12px;
        font-weight: 600;
        min-width: 50px;
        text-align: center;
    }

    .boxes-badge {
        background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
    }

    .pieces-badge {
        background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
    }

    .empty-value {
        color: #9ca3af;
        font-style: italic;
        font-size: 14px;
    }

    /* Notes Content */
    .notes-content {
        background: rgba(107, 114, 128, 0.05);
        border: 1px solid rgba(107, 114, 128, 0.1);
        border-radius: 8px;
        padding: 16px;
        font-size: 14px;
        line-height: 1.6;
        color: #374151;
        white-space: pre-wrap;
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .header-actions-group {
            flex-direction: column;
            gap: 8px;
            width: 100%;
        }

        .header-actions-group .btn {
            width: 100%;
            justify-content: center;
        }

        .info-grid {
            grid-template-columns: 1fr;
            gap: 16px;
        }

        .stats-container {
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 8px;
        }

        .quick-stats-body {
            padding: 10px;
        }

        .stat-card {
            min-width: 0;
            padding: 10px 8px;
        }

        .modern-tbody td {
            padding: 12px 8px;
            font-size: 13px;
        }

        .product-info {
            gap: 2px;
        }

        .product-category {
            font-size: 11px;
        }

        .quantity-badge, .boxes-badge, .pieces-badge {
            padding: 4px 8px;
            font-size: 11px;
            min-width: 40px;
        }
    }

    /* Animation */
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .modern-card {
        animation: fadeInUp 0.3s ease-out;
    }

    .modern-card:nth-child(1) { animation-delay: 0.1s; }
    .modern-card:nth-child(2) { animation-delay: 0.2s; }
    .modern-card:nth-child(3) { animation-delay: 0.3s; }
</style>
@stop

@section('additional_js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function confirmDelete() {
    Swal.fire({
        title: 'Delete Challan?',
        text: 'Are you sure you want to delete this challan? Stock will be restored to products.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Yes, Delete & Restore Stock',
        cancelButtonText: 'Cancel',
        reverseButtons: true,
        customClass: {
            popup: 'swal-wide'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('delete-form').submit();
        }
    });
}
</script>
@stop
