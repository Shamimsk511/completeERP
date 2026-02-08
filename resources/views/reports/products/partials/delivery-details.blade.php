@if($items->isEmpty())
    <div class="alert alert-info">
        <i class="fas fa-info-circle mr-2"></i>
        No other deliveries found for this product in the selected period.
    </div>
@else
    @php
        $groupedByMonth = $items->groupBy(function($item) {
            return \Carbon\Carbon::parse($item->otherDelivery->delivery_date)->format('F Y');
        });
        $groupedByStatus = $items->groupBy('otherDelivery.status');
    @endphp

    <!-- Summary Cards -->
    <div class="row mb-3">
        <div class="col-md-3">
            <div class="info-box bg-danger mb-0">
                <span class="info-box-icon"><i class="fas fa-truck"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Total Delivered</span>
                    <span class="info-box-number">{{ number_format($items->sum('quantity')) }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="info-box bg-success mb-0">
                <span class="info-box-icon"><i class="fas fa-check-circle"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Completed</span>
                    <span class="info-box-number">{{ number_format($groupedByStatus->get('delivered', collect())->sum('quantity')) }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="info-box bg-warning mb-0">
                <span class="info-box-icon"><i class="fas fa-clock"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Pending</span>
                    <span class="info-box-number">{{ number_format($groupedByStatus->get('pending', collect())->sum('quantity')) }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="info-box bg-secondary mb-0">
                <span class="info-box-icon"><i class="fas fa-times-circle"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Cancelled</span>
                    <span class="info-box-number">{{ number_format($groupedByStatus->get('cancelled', collect())->sum('quantity')) }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Monthly Summary -->
    <div class="card card-outline card-danger mb-3">
        <div class="card-header">
            <h5 class="card-title mb-0"><i class="fas fa-calendar-alt mr-2"></i>Monthly Breakdown</h5>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                    <i class="fas fa-minus"></i>
                </button>
            </div>
        </div>
        <div class="card-body p-0">
            <table class="table table-sm table-striped mb-0">
                <thead class="thead-light">
                    <tr>
                        <th>Month</th>
                        <th class="text-right">Quantity</th>
                        <th class="text-right">Deliveries</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($groupedByMonth as $month => $monthItems)
                    <tr>
                        <td><strong>{{ $month }}</strong></td>
                        <td class="text-right">{{ number_format($monthItems->sum('quantity')) }}</td>
                        <td class="text-right">{{ $monthItems->pluck('other_delivery_id')->unique()->count() }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Status Summary -->
    <div class="card card-outline card-primary mb-3">
        <div class="card-header">
            <h5 class="card-title mb-0"><i class="fas fa-info-circle mr-2"></i>Status Breakdown</h5>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                    <i class="fas fa-minus"></i>
                </button>
            </div>
        </div>
        <div class="card-body p-0">
            <table class="table table-sm table-striped mb-0">
                <thead class="thead-light">
                    <tr>
                        <th>Status</th>
                        <th class="text-right">Quantity</th>
                        <th class="text-right">Deliveries</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($groupedByStatus as $status => $statusItems)
                    <tr>
                        <td>
                            @if($status == 'delivered')
                                <span class="badge badge-success">{{ ucfirst($status) }}</span>
                            @elseif($status == 'pending')
                                <span class="badge badge-warning">{{ ucfirst($status) }}</span>
                            @elseif($status == 'cancelled')
                                <span class="badge badge-secondary">{{ ucfirst($status) }}</span>
                            @else
                                <span class="badge badge-info">{{ ucfirst($status ?? 'Unknown') }}</span>
                            @endif
                        </td>
                        <td class="text-right">{{ number_format($statusItems->sum('quantity')) }}</td>
                        <td class="text-right">{{ $statusItems->pluck('other_delivery_id')->unique()->count() }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Detailed Transactions -->
    <div class="card card-outline card-secondary">
        <div class="card-header">
            <h5 class="card-title mb-0"><i class="fas fa-list mr-2"></i>All Deliveries</h5>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                    <i class="fas fa-minus"></i>
                </button>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm table-bordered table-hover mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th>Delivery #</th>
                            <th>Date</th>
                            <th>Recipient</th>
                            <th>Reason</th>
                            <th class="text-right">Quantity</th>
                            <th class="text-center">Status</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($items->sortByDesc('otherDelivery.delivery_date') as $item)
                        <tr>
                            <td>
                                <a href="{{ route('other-deliveries.show', $item->other_delivery_id) }}" target="_blank" class="text-danger font-weight-bold">
                                    #{{ $item->other_delivery_id }}
                                </a>
                            </td>
                            <td>{{ \Carbon\Carbon::parse($item->otherDelivery->delivery_date)->format('d M Y') }}</td>
                            <td>{{ $item->otherDelivery->recipient_name ?? 'N/A' }}</td>
                            <td>{{ $item->otherDelivery->reason ?? '-' }}</td>
                            <td class="text-right">{{ number_format($item->quantity) }}</td>
                            <td class="text-center">
                                @if($item->otherDelivery->status == 'delivered')
                                    <span class="badge badge-success">Delivered</span>
                                @elseif($item->otherDelivery->status == 'pending')
                                    <span class="badge badge-warning">Pending</span>
                                @elseif($item->otherDelivery->status == 'cancelled')
                                    <span class="badge badge-secondary">Cancelled</span>
                                @else
                                    <span class="badge badge-info">{{ ucfirst($item->otherDelivery->status ?? 'Unknown') }}</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <a href="{{ route('other-deliveries.show', $item->other_delivery_id) }}" target="_blank" class="btn btn-xs btn-danger" title="View Delivery">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-light font-weight-bold">
                        <tr>
                            <td colspan="4">Total</td>
                            <td class="text-right">{{ number_format($items->sum('quantity')) }}</td>
                            <td colspan="2"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
@endif
