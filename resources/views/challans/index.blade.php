@extends('layouts.modern-admin')

@section('title', 'Delivery Challans')

@section('page_title', 'Delivery Challans')

@section('header_actions')
    <a href="{{ route('challans.create') }}" class="btn modern-btn modern-btn-primary">
        <i class="fas fa-plus"></i> Create New Challan
    </a>
@stop

@section('page_content')
    <!-- Main Content Card -->
    <div class="card modern-card">
        <div class="card-header modern-header">
            <div class="header-content">
                <div class="header-title">
                    <i class="fas fa-truck header-icon"></i>
                    <h3 class="card-title">All Challans</h3>
                </div>
                <div class="header-stats">
                    <div class="stat-item">
                        <span class="stat-label">Total Challans</span>
                        <span class="stat-value" id="total-count">-</span>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card-body modern-card-body">
            <div class="table-container">
                <div class="table-responsive modern-table-responsive">
                    <table class="table modern-table" id="challans-table">
                        <thead class="modern-thead">
                            <tr>
                                <th class="sortable">
                                    <div class="th-content">
                                        <span>Challan Number</span>
                                        <i class="fas fa-sort sort-icon"></i>
                                    </div>
                                </th>
                                <th class="sortable">
                                    <div class="th-content">
                                        <span>Date</span>
                                        <i class="fas fa-sort sort-icon"></i>
                                    </div>
                                </th>
                                <th class="sortable">
                                    <div class="th-content">
                                        <span>Invoice Number</span>
                                        <i class="fas fa-sort sort-icon"></i>
                                    </div>
                                </th>
                                <th class="sortable">
                                    <div class="th-content">
                                        <span>Customer</span>
                                        <i class="fas fa-sort sort-icon"></i>
                                    </div>
                                </th>
                                <th class="sortable">
                                    <div class="th-content">
                                        <span>Receiver</span>
                                        <i class="fas fa-sort sort-icon"></i>
                                    </div>
                                </th>
                                <th class="sortable">
                                    <div class="th-content">
                                        <span>Delivered At</span>
                                        <i class="fas fa-sort sort-icon"></i>
                                    </div>
                                </th>
                                <th class="sortable">
                                    <div class="th-content">
                                        <span>Items</span>
                                        <i class="fas fa-sort sort-icon"></i>
                                    </div>
                                </th>
                                <th class="actions-column">
                                    <div class="th-content">
                                        <span>Actions</span>
                                    </div>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="modern-tbody">
                            <!-- DataTable will populate this -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@stop

@section('additional_css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.25/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.min.css">
    
    <style>
        /* Modern Alert Styles */
        .modern-alert {
            border: none;
            border-radius: 12px;
            padding: 0;
            margin-bottom: 24px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            backdrop-filter: blur(10px);
            animation: slideInDown 0.3s ease-out;
        }

        .modern-alert-success {
            background: linear-gradient(135deg, rgba(34, 197, 94, 0.1) 0%, rgba(34, 197, 94, 0.05) 100%);
            border-left: 4px solid #22c55e;
        }

        .modern-alert-error {
            background: linear-gradient(135deg, rgba(239, 68, 68, 0.1) 0%, rgba(239, 68, 68, 0.05) 100%);
            border-left: 4px solid #ef4444;
        }

        .alert-content {
            display: flex;
            align-items: center;
            padding: 16px 20px;
            gap: 12px;
        }

        .alert-icon {
            font-size: 20px;
            flex-shrink: 0;
        }

        .modern-alert-success .alert-icon {
            color: #22c55e;
        }

        .modern-alert-error .alert-icon {
            color: #ef4444;
        }

        .alert-message {
            flex: 1;
            font-size: 14px;
            line-height: 1.5;
        }

        .alert-message strong {
            font-weight: 600;
            margin-right: 8px;
        }

        .alert-close {
            background: none;
            border: none;
            padding: 4px;
            cursor: pointer;
            color: #6b7280;
            transition: all 0.2s ease;
            border-radius: 6px;
        }

        .alert-close:hover {
            background: rgba(0, 0, 0, 0.05);
            color: #374151;
        }

        /* Modern Header Enhancements */
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
            color: #6366f1;
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .header-stats {
            display: flex;
            gap: 24px;
        }

        .stat-item {
            text-align: center;
            padding: 12px 20px;
            background: white;
            border-radius: 10px;
            border: 1px solid #e5e7eb;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .stat-label {
            display: block;
            font-size: 12px;
            color: #6b7280;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .stat-value {
            display: block;
            font-size: 20px;
            font-weight: 700;
            color: #374151;
            margin-top: 4px;
        }

        /* Modern Table Enhancements */
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
            padding: 18px 16px;
            font-weight: 600;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: white !important;
            position: relative;
            white-space: nowrap;
            background: transparent !important;
        }

        .th-content {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            color: white;
        }

        .sortable .th-content {
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .sortable:hover .th-content {
            color: #e0e7ff;
            transform: translateY(-1px);
        }

        .sort-icon {
            font-size: 10px;
            opacity: 0.7;
            transition: all 0.2s ease;
            color: white;
        }

        .sortable:hover .sort-icon {
            opacity: 1;
            color: #e0e7ff;
        }

        .actions-column {
            width: 15%;
            text-align: center;
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

        /* Override any dark backgrounds */
        #challans-table {
            background: white !important;
            color: #1f2937 !important;
        }

        #challans-table tbody tr {
            background: white !important;
        }

        #challans-table tbody tr td {
            background: white !important;
            color: #374151 !important;
        }

        /* Enhanced Status Badges */
        .delivery-status-container {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .status-badge {
            min-width: 90px;
            text-align: center;
            font-size: 12px;
            font-weight: 600;
            padding: 8px 16px;
            border-radius: 25px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border: none;
            transition: all 0.3s ease;
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

        .badge-secondary {
            background: linear-gradient(135deg, #6b7280 0%, #4b5563 100%);
            color: white;
        }

        /* Enhanced Dropdown */
        .dropdown-toggle {
            background: rgba(99, 102, 241, 0.1);
            border: 1px solid rgba(99, 102, 241, 0.3);
            color: #6366f1;
            padding: 6px 12px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 500;
            transition: all 0.2s ease;
        }

        .dropdown-toggle:hover {
            background: rgba(99, 102, 241, 0.15);
            border-color: #6366f1;
            transform: translateY(-1px);
        }

        .dropdown-menu {
            border: none;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
            border-radius: 10px;
            padding: 10px;
            background: white;
            margin-top: 5px;
        }

        .dropdown-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 12px;
            border-radius: 8px;
            transition: all 0.2s ease;
            font-size: 13px;
            color: #374151;
        }

        .dropdown-item:hover {
            background: linear-gradient(135deg, rgba(99, 102, 241, 0.1) 0%, rgba(139, 92, 246, 0.05) 100%);
            color: #6366f1;
            transform: translateX(2px);
        }

        .dropdown-item .badge {
            width: 80px;
            text-align: center;
            font-size: 11px;
        }

        /* DataTable Enhancements */
        .dataTables_wrapper {
            background: white;
            padding: 0;
        }

        .dataTables_wrapper .dataTables_length {
            margin-bottom: 20px;
        }

        .dataTables_wrapper .dataTables_length select {
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            padding: 8px 12px;
            background: white;
            color: #374151;
            margin: 0 8px;
            transition: all 0.2s ease;
        }

        .dataTables_wrapper .dataTables_length select:focus {
            outline: none;
            border-color: #6366f1;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
        }

        .dataTables_wrapper .dataTables_filter {
            margin-bottom: 20px;
        }

        .dataTables_wrapper .dataTables_filter input {
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            padding: 10px 15px;
            margin-left: 8px;
            background: white;
            color: #374151;
            transition: all 0.2s ease;
            font-size: 14px;
        }

        .dataTables_wrapper .dataTables_filter input:focus {
            outline: none;
            border-color: #6366f1;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
        }

        .dataTables_wrapper .dataTables_info {
            color: #6b7280;
            font-size: 14px;
            font-weight: 500;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button {
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            padding: 8px 12px;
            margin: 0 2px;
            background: white;
            color: #374151;
            transition: all 0.2s ease;
            font-weight: 500;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
            background: #6366f1;
            color: white;
            border-color: #6366f1;
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(99, 102, 241, 0.3);
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button.current {
            background: #6366f1;
            color: white;
            border-color: #6366f1;
            box-shadow: 0 4px 8px rgba(99, 102, 241, 0.3);
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button.disabled {
            color: #9ca3af;
            background: #f9fafb;
            border-color: #e5e7eb;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button.disabled:hover {
            background: #f9fafb;
            color: #9ca3af;
            border-color: #e5e7eb;
            transform: none;
            box-shadow: none;
        }

        /* Loading States */
        .dataTables_processing {
            background: rgba(255, 255, 255, 0.95) !important;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            padding: 20px;
            font-weight: 600;
            color: #374151 !important;
            font-size: 14px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }

        /* Remove dark mode styles that were causing issues */
        .modern-card-body {
            background: white !important;
            color: #1f2937 !important;
        }

        /* Animations */
        @keyframes slideInDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .modern-table tbody tr {
            animation: fadeIn 0.3s ease-in-out;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .header-content {
                flex-direction: column;
                gap: 16px;
                align-items: flex-start;
            }

            .header-stats {
                align-self: stretch;
                justify-content: space-between;
            }

            .stat-item {
                flex: 1;
                text-align: center;
            }

            .modern-thead th {
                padding: 12px 8px;
                font-size: 11px;
            }

            .modern-tbody td {
                padding: 12px 8px;
                font-size: 13px;
            }

            .status-badge {
                min-width: 70px;
                font-size: 10px;
                padding: 4px 8px;
            }
        }

        /* Dark mode compatibility - removed problematic styles */
        /* Keeping styles light and readable */
    </style>
@stop

@section('additional_js')
    <script src="https://cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.25/js/dataTables.bootstrap4.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"></script>
    
    <script>
        $(document).ready(function() {
            // Configure toastr for modern notifications
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

            // Initialize DataTable with server-side processing
            var table = $('#challans-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('challans.index') }}",
                columns: [
                    {data: 'challan_number', name: 'challan_number'},
                    {data: 'formatted_date', name: 'challan_date'},
                    {data: 'invoice_number', name: 'invoice.invoice_number'},
                    {data: 'customer_name', name: 'invoice.customer.name'},
                    {data: 'receiver_name', name: 'receiver_name'},
                    {data: 'delivered_time', name: 'delivered_at'},
                    {data: 'items_count', name: 'items_count', orderable: false, searchable: false},
                    {data: 'action', name: 'action', orderable: false, searchable: false}
                ],
                order: [[0, 'desc']],
                drawCallback: function(settings) {
                    // Update total count in header
                    $('#total-count').text(this.api().page.info().recordsTotal);

                    // Add smooth animation to newly loaded rows
                    $('.modern-tbody tr').each(function(index) {
                        $(this).css('animation-delay', (index * 50) + 'ms');
                    });
                }
            });

            // Auto-hide alerts after 5 seconds
            setTimeout(function() {
                $('.modern-alert').fadeOut(500);
            }, 5000);

            // Enhanced sorting animations
            $('#challans-table').on('order.dt', function() {
                $('.modern-tbody tr').css('animation', 'fadeIn 0.3s ease-in-out');
            });
        });
    </script>
@stop