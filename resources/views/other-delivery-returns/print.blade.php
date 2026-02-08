@extends('adminlte::page')

@php
    $activePrintTemplate = $selectedTemplate ?? ($businessSettings->invoice_template ?? 'standard');
    if (!in_array($activePrintTemplate, ['standard', 'modern', 'simple', 'bold', 'elegant', 'imaginative'], true)) {
        $activePrintTemplate = 'standard';
    }
@endphp

@section('title', 'Print Return')

@section('content')
    <div class="container mt-4 print-container print-theme template-{{ $activePrintTemplate }}">
        <div class="text-center mb-4">
            <h2>Return Receipt</h2>
        </div>
        
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h5>Return Information</h5>
                        <table class="table table-bordered">
                            <tr>
                                <th width="40%">Return Number</th>
                                <td>{{ $otherDeliveryReturn->return_number }}</td>
                            </tr>
                            <tr>
                                <th>Return Date</th>
                                <td>{{ date('d M Y', strtotime($otherDeliveryReturn->return_date)) }}</td>
                            </tr>
                            <tr>
                                <th>Status</th>
                                <td>
                                    @if($otherDeliveryReturn->status == 'pending')
                                        Pending
                                    @elseif($otherDeliveryReturn->status == 'completed')
                                        Completed
                                    @elseif($otherDeliveryReturn->status == 'rejected')
                                        Rejected
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h5>Returner Information</h5>
                        <table class="table table-bordered">
                            <tr>
                                <th width="40%">Name</th>
                                <td>{{ $otherDeliveryReturn->returner_name }}</td>
                            </tr>
                            <tr>
                                <th>Phone</th>
                                <td>{{ $otherDeliveryReturn->returner_phone ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Address</th>
                                <td>{{ $otherDeliveryReturn->returner_address }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card mt-4">
            <div class="card-body">
                <h5>Returned Products</h5>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Product</th>
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
                                <td>{{ $item->product->name }}</td>
                                <td>{{ $item->description ?? 'N/A' }}</td>
                                <td>{{ $item->quantity }}</td>
                                <td>{{ $item->cartons ?? 'N/A' }}</td>
                                <td>{{ $item->pieces ?? 'N/A' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        
        <div class="row mt-5">
            <div class="col-md-4 text-center">
                <div class="signature-line"></div>
                <p>Received By</p>
            </div>
            <div class="col-md-4 text-center">
                <div class="signature-line"></div>
                <p>Returned By</p>
            </div>
            <div class="col-md-4 text-center">
                <div class="signature-line"></div>
                <p>Authorized By</p>
            </div>
        </div>
        
        <div class="row mt-5 d-print-none">
            <div class="col-md-12 text-center">
                <button class="btn btn-primary" onclick="window.print()">
                    <i class="fas fa-print"></i> Print
                </button>
                <a href="{{ route('other-delivery-returns.show', ['otherDeliveryReturn' => $otherDeliveryReturn->id]) }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
            </div>
        </div>
    </div>
@stop

@section('css')
    @include('partials.print-theme-styles')
    <style>
        @media print {
            body {
                font-size: 12pt;
            }
            
            .d-print-none {
                display: none !important;
            }
            
            .print-container {
                width: 100%;
                max-width: 100%;
            }
            
            .card {
                border: 1px solid #ddd;
                margin-bottom: 20px;
            }
            
            .card-body {
                padding: 15px;
            }
            
            .table {
                width: 100%;
                border-collapse: collapse;
            }
            
            .table th, .table td {
                border: 1px solid #ddd;
                padding: 8px;
            }
            
            .signature-line {
                border-top: 1px solid #000;
                width: 80%;
                margin: 50px auto 10px;
            }
        }
        
        .signature-line {
            border-top: 1px solid #000;
            width: 80%;
            margin: 50px auto 10px;
        }
    </style>
@stop

@section('js')
    <script>
        $(document).ready(function() {
            document.body.classList.add('print-theme', 'template-{{ $activePrintTemplate }}');
            // Auto-print when page loads
            // window.print();
        });
    </script>
@stop
