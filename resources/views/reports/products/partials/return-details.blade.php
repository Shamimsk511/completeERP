@if($items->isEmpty())
    <div class="alert alert-info">
        <i class="fas fa-info-circle mr-2"></i>
        No returns found for this product in the selected period.
    </div>
@else
    @php
        $groupedByMonth = $items->groupBy(function($item) {
            return \Carbon\Carbon::parse($item->productReturn->return_date)->format('F Y');
        });
        $groupedByCustomer = $items->groupBy('productReturn.customer_id');
    @endphp

    <!-- Summary Cards -->
    <div class="row mb-3">
        <div class="col-md-4">
            <div class="info-box bg-warning mb-0">
                <span class="info-box-icon"><i class="fas fa-undo"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Total Returned</span>
                    <span class="info-box-number">{{ number_format($items->sum('quantity')) }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="info-box bg-danger mb-0">
                <span class="info-box-icon"><i class="fas fa-money-bill"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Total Value</span>
                    <span class="info-box-number">{{ number_format($items->sum('total'), 2) }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="info-box bg-primary mb-0">
                <span class="info-box-icon"><i class="fas fa-users"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Customers</span>
                    <span class="info-box-number">{{ $groupedByCustomer->count() }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Monthly Summary -->
    <div class="card card-outline card-warning mb-3">
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
                        <th class="text-right">Value</th>
                        <th class="text-right">Returns</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($groupedByMonth as $month => $monthItems)
                    <tr>
                        <td><strong>{{ $month }}</strong></td>
                        <td class="text-right">{{ number_format($monthItems->sum('quantity')) }}</td>
                        <td class="text-right">{{ number_format($monthItems->sum('total'), 2) }}</td>
                        <td class="text-right">{{ $monthItems->pluck('return_id')->unique()->count() }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Customer Summary -->
    <div class="card card-outline card-primary mb-3">
        <div class="card-header">
            <h5 class="card-title mb-0"><i class="fas fa-users mr-2"></i>Customer Breakdown</h5>
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
                        <th>Customer</th>
                        <th class="text-right">Quantity</th>
                        <th class="text-right">Value</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($groupedByCustomer as $customerId => $customerItems)
                    @php $customer = $customerItems->first()->productReturn->customer; @endphp
                    <tr>
                        <td><strong>{{ $customer->name ?? 'N/A' }}</strong></td>
                        <td class="text-right">{{ number_format($customerItems->sum('quantity')) }}</td>
                        <td class="text-right">{{ number_format($customerItems->sum('total'), 2) }}</td>
                        <td class="text-center">
                            @if($customer)
                            <a href="{{ route('customers.show', $customer->id) }}" target="_blank" class="btn btn-xs btn-info" title="View Customer">
                                <i class="fas fa-eye"></i>
                            </a>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Detailed Transactions -->
    <div class="card card-outline card-secondary">
        <div class="card-header">
            <h5 class="card-title mb-0"><i class="fas fa-list mr-2"></i>All Returns</h5>
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
                            <th>Return #</th>
                            <th>Date</th>
                            <th>Customer</th>
                            <th class="text-right">Quantity</th>
                            <th class="text-right">Unit Price</th>
                            <th class="text-right">Total</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($items->sortByDesc('productReturn.return_date') as $item)
                        <tr>
                            <td>
                                <a href="{{ route('returns.show', $item->return_id) }}" target="_blank" class="text-warning font-weight-bold">
                                    #{{ $item->return_id }}
                                </a>
                            </td>
                            <td>{{ \Carbon\Carbon::parse($item->productReturn->return_date)->format('d M Y') }}</td>
                            <td>{{ $item->productReturn->customer->name ?? 'N/A' }}</td>
                            <td class="text-right">{{ number_format($item->quantity) }}</td>
                            <td class="text-right">{{ number_format($item->price ?? 0, 2) }}</td>
                            <td class="text-right">{{ number_format($item->total ?? 0, 2) }}</td>
                            <td class="text-center">
                                <a href="{{ route('returns.show', $item->return_id) }}" target="_blank" class="btn btn-xs btn-warning" title="View Return">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-light font-weight-bold">
                        <tr>
                            <td colspan="3">Total</td>
                            <td class="text-right">{{ number_format($items->sum('quantity')) }}</td>
                            <td></td>
                            <td class="text-right">{{ number_format($items->sum('total'), 2) }}</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
@endif
