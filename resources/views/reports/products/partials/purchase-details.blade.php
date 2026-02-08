@if($items->isEmpty())
    <div class="alert alert-info">
        <i class="fas fa-info-circle mr-2"></i>
        No purchases found for this product in the selected period.
    </div>
@else
    @php
        $groupedByMonth = $items->groupBy(function($item) {
            return \Carbon\Carbon::parse($item->purchase_date)->format('F Y');
        });
        $groupedByCompany = $items->groupBy('company_name');
    @endphp

    <!-- Summary Cards -->
    <div class="row mb-3">
        <div class="col-md-3">
            <div class="info-box bg-success mb-0">
                <span class="info-box-icon"><i class="fas fa-boxes"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Total Purchased</span>
                    <span class="info-box-number">{{ number_format($items->sum('quantity')) }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="info-box bg-info mb-0">
                <span class="info-box-icon"><i class="fas fa-money-bill"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Total Cost</span>
                    <span class="info-box-number">{{ number_format($items->sum('total_price'), 2) }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="info-box bg-warning mb-0">
                <span class="info-box-icon"><i class="fas fa-calculator"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Avg. Price</span>
                    <span class="info-box-number">{{ number_format($items->avg('purchase_price'), 2) }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="info-box bg-primary mb-0">
                <span class="info-box-icon"><i class="fas fa-building"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Suppliers</span>
                    <span class="info-box-number">{{ $groupedByCompany->count() }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Monthly Summary -->
    <div class="card card-outline card-success mb-3">
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
                        <th class="text-right">Cost</th>
                        <th class="text-right">Avg. Price</th>
                        <th class="text-right">Orders</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($groupedByMonth as $month => $monthItems)
                    <tr>
                        <td><strong>{{ $month }}</strong></td>
                        <td class="text-right">{{ number_format($monthItems->sum('quantity')) }}</td>
                        <td class="text-right">{{ number_format($monthItems->sum('total_price'), 2) }}</td>
                        <td class="text-right">{{ number_format($monthItems->avg('purchase_price'), 2) }}</td>
                        <td class="text-right">{{ $monthItems->pluck('purchase_id')->unique()->count() }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Supplier Summary -->
    <div class="card card-outline card-primary mb-3">
        <div class="card-header">
            <h5 class="card-title mb-0"><i class="fas fa-building mr-2"></i>Supplier Breakdown</h5>
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
                        <th>Supplier</th>
                        <th class="text-right">Quantity</th>
                        <th class="text-right">Cost</th>
                        <th class="text-right">Avg. Price</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($groupedByCompany as $companyName => $companyItems)
                    <tr>
                        <td><strong>{{ $companyName ?? 'N/A' }}</strong></td>
                        <td class="text-right">{{ number_format($companyItems->sum('quantity')) }}</td>
                        <td class="text-right">{{ number_format($companyItems->sum('total_price'), 2) }}</td>
                        <td class="text-right">{{ number_format($companyItems->avg('purchase_price'), 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Detailed Transactions -->
    <div class="card card-outline card-secondary">
        <div class="card-header">
            <h5 class="card-title mb-0"><i class="fas fa-list mr-2"></i>All Purchases</h5>
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
                            <th>Purchase #</th>
                            <th>Invoice No</th>
                            <th>Date</th>
                            <th>Supplier</th>
                            <th class="text-right">Quantity</th>
                            <th class="text-right">Unit Price</th>
                            <th class="text-right">Total</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($items->sortByDesc('purchase_date') as $item)
                        <tr>
                            <td>
                                <a href="{{ route('purchases.show', $item->purchase_id) }}" target="_blank" class="text-success font-weight-bold">
                                    #{{ $item->purchase_id }}
                                </a>
                            </td>
                            <td>{{ $item->invoice_no ?? '-' }}</td>
                            <td>{{ \Carbon\Carbon::parse($item->purchase_date)->format('d M Y') }}</td>
                            <td>{{ $item->company_name ?? 'N/A' }}</td>
                            <td class="text-right">{{ number_format($item->quantity) }}</td>
                            <td class="text-right">{{ number_format($item->purchase_price, 2) }}</td>
                            <td class="text-right">{{ number_format($item->total_price, 2) }}</td>
                            <td class="text-center">
                                <a href="{{ route('purchases.show', $item->purchase_id) }}" target="_blank" class="btn btn-xs btn-success" title="View Purchase">
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
                            <td></td>
                            <td class="text-right">{{ number_format($items->sum('total_price'), 2) }}</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
@endif
