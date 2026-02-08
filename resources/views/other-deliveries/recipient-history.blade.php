@extends('layouts.modern-admin')

@section('title', 'Delivery History')

@section('page_title', 'Delivery History for ' . $recipient->recipient_name)

@section('header_actions')
    <div class="header-actions-group">
        <a href="{{ route('other-deliveries.index') }}" class="btn modern-btn modern-btn-outline">
            <i class="fas fa-arrow-left"></i> Back to Deliveries
        </a>
        <button type="button" class="btn modern-btn modern-btn-secondary" onclick="window.print()">
            <i class="fas fa-print"></i> Print History
        </button>
    </div>
@stop

@section('page_content')
    <!-- Recipient Info Card -->
    <div class="card modern-card">
        <div class="card-header modern-header">
            <div class="header-content">
                <div class="header-title">
                    <i class="fas fa-user header-icon"></i>
                    <h3 class="card-title">Recipient Information</h3>
                </div>
            </div>
        </div>
        <div class="card-body modern-card-body">
            <div class="row">
                <div class="col-md-4">
                    <strong>Name:</strong> {{ $recipient->recipient_name }}
                </div>
                <div class="col-md-4">
                    <strong>Phone:</strong> {{ $recipient->recipient_phone ?: 'N/A' }}
                </div>
                <div class="col-md-4">
                    <strong>Total Deliveries:</strong> {{ $deliveries->count() }}
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-md-12">
                    <strong>Address:</strong> {{ $recipient->recipient_address }}
                </div>
            </div>
        </div>
    </div>

    <!-- Delivery History -->
    <div class="card modern-card">
        <div class="card-header modern-header">
            <div class="header-content">
                <div class="header-title">
                    <i class="fas fa-history header-icon"></i>
                    <h3 class="card-title">Delivery History</h3>
                </div>
            </div>
        </div>
        <div class="card-body modern-card-body">
            <div class="table-responsive modern-table-responsive">
                <table class="table modern-table" id="history-table">
                    <thead class="modern-thead">
                        <tr>
                            <th>Challan #</th>
                            <th>Date</th>
                            <th>Items</th>
                            <th>Status</th>
                            <th>Delivered By</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody class="modern-tbody">
                        @foreach($deliveries as $delivery)
                            <tr>
                                <td>
                                    <span class="font-weight-bold text-primary">{{ $delivery->challan_number }}</span>
                                </td>
                                <td>{{ $delivery->delivery_date->format('d-m-Y') }}</td>
                                <td>
                                    <div class="items-list">
                                        @foreach($delivery->items as $item)
                                            <div class="item-row">
                                                <strong>{{ $item->product->name }}</strong>
                                                <span class="text-muted">({{ $item->quantity }})</span>
                                                @if($item->description)
                                                    <br><small class="text-muted">{{ $item->description }}</small>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                </td>
                                <td>
                                    @if($delivery->status == 'pending')
                                        <span class="badge badge-warning status-badge">
                                            <i class="fas fa-clock"></i> Pending
                                        </span>
                                    @elseif($delivery->status == 'delivered')
                                        <span class="badge badge-success status-badge">
                                            <i class="fas fa-check-circle"></i> Delivered
                                        </span>
                                    @else
                                        <span class="badge badge-danger status-badge">
                                            <i class="fas fa-times-circle"></i> Cancelled
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    @if($delivery->deliveredBy)
                                        <div class="delivery-person">
                                            <i class="fas fa-user"></i>
                                            {{ $delivery->deliveredBy->name }}
                                        </div>
                                    @else
                                        <span class="text-muted">Not assigned</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="{{ route('other-deliveries.show', $delivery) }}" 
                                           class="btn modern-btn-sm modern-btn-info" 
                                           title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('other-deliveries.print', $delivery) }}" 
                                           class="btn modern-btn-sm modern-btn-secondary" 
                                           target="_blank"
                                           title="Print Challan">
                                            <i class="fas fa-print"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@stop

@section('additional_css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.25/css/dataTables.bootstrap4.min.css">
    
    <style>
        .items-list {
            max-width: 300px;
        }

        .item-row {
            margin-bottom: 8px;
            padding-bottom: 8px;
            border-bottom: 1px solid #f1f5f9;
        }

        .item-row:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }

        .status-badge {
            padding: 6px 12px;
            font-size: 12px;
            font-weight: 600;
            border-radius: 20px;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        .delivery-person {
            color: #374151;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .action-buttons {
            display: flex;
            gap: 4px;
        }

        .modern-btn-sm {
            padding: 6px 10px;
            border-radius: 6px;
            font-size: 12px;
            border: none;
            transition: all 0.2s ease;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 32px;
            height: 32px;
        }

        .modern-btn-sm.modern-btn-info {
            background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);
            color: white;
        }

        .modern-btn-sm.modern-btn-secondary {
            background: linear-gradient(135deg, #6b7280 0%, #4b5563 100%);
            color: white;
        }

        .modern-btn-sm:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        /* Inherit all other styles from the main design */
        .modern-card {
            background: white;
            border: none;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            margin-bottom: 24px;
            backdrop-filter: blur(10px);
            overflow: hidden;
        }

        .modern-header {
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
            color: white;
            border-radius: 16px 16px 0 0;
            padding: 20px 24px;
            border-bottom: none;
        }

        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            width: 100%;
        }

        .header-title {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .header-icon {
            font-size: 24px;
            color: white;
        }

        .card-title {
            color: white;
            font-weight: 600;
            margin: 0;
            font-size: 18px;
        }

        .modern-card-body {
            padding: 24px;
            background: white;
        }

        .modern-table-responsive {
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
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
            padding: 18px 16px;
            font-weight: 600;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: white !important;
            white-space: nowrap;
            background: transparent !important;
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

        .modern-btn {
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 14px;
            border: 2px solid transparent;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            cursor: pointer;
        }

        .modern-btn-outline {
            background: white;
            color: #6366f1;
            border-color: #6366f1;
        }

        .modern-btn-outline:hover {
            background: #6366f1;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(99, 102, 241, 0.3);
        }

        .modern-btn-secondary {
            background: linear-gradient(135deg, #6b7280 0%, #4b5563 100%);
            color: white;
            border-color: #6b7280;
        }

        .modern-btn-secondary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(107, 114, 128, 0.4);
            color: white;
        }

        .header-actions-group {
            display: flex;
            gap: 12px;
            align-items: center;
        }

        @media print {
            .header-actions-group {
                display: none;
            }
        }
    </style>
@stop

@section('additional_js')
    <script src="https://cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.25/js/dataTables.bootstrap4.min.js"></script>
    
    <script>
        $(document).ready(function() {
            $('#history-table').DataTable({
                "order": [[1, "desc"]],
                "pageLength": 25,
                "responsive": true
            });
        });
    </script>
@stop
