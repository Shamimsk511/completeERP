@extends('layouts.modern-admin')

@section('title', 'Employee Ledger')
@section('page_title', 'Employee Ledger')

@section('page_content')
<div class="card modern-card mb-3">
    <div class="card-body">
        <form method="GET" action="{{ route('hr.employees.ledger', $employee) }}" class="form-inline">
            <div class="form-group mr-2">
                <label class="mr-2">From</label>
                <input type="date" name="from_date" class="form-control" value="{{ $fromDate }}">
            </div>
            <div class="form-group mr-2">
                <label class="mr-2">To</label>
                <input type="date" name="to_date" class="form-control" value="{{ $toDate }}">
            </div>
            <button type="submit" class="btn btn-primary mr-2">Filter</button>
            <a href="{{ route('hr.employees.ledger.print', [$employee, 'from_date' => $fromDate, 'to_date' => $toDate]) }}" target="_blank" class="btn btn-secondary">Print</a>
        </form>
    </div>
</div>

<div class="card modern-card">
    <div class="card-header modern-header">
        <h3 class="card-title"><i class="fas fa-book"></i> {{ $employee->name }} Ledger</h3>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table mb-0">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Reference</th>
                        <th>Source</th>
                        <th>Particulars</th>
                        <th>Debit</th>
                        <th>Credit</th>
                        <th>Balance</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td colspan="6"><strong>Opening Balance</strong></td>
                        <td>
                            {{ number_format($ledger['opening_balance']['balance'], 2) }}
                            {{ strtoupper(substr($ledger['opening_balance']['type'], 0, 2)) }}
                        </td>
                    </tr>
                    @forelse($ledger['entries'] as $entry)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($entry['date'])->format('d M, Y') }}</td>
                            <td>{{ $entry['reference'] ?? '-' }}</td>
                            <td>{{ $entry['source_type'] ?? '-' }}</td>
                            <td>{{ $entry['particulars'] }}</td>
                            <td>{{ $entry['debit'] > 0 ? number_format($entry['debit'], 2) : '-' }}</td>
                            <td>{{ $entry['credit'] > 0 ? number_format($entry['credit'], 2) : '-' }}</td>
                            <td>
                                {{ number_format($entry['running_balance'], 2) }}
                                {{ strtoupper(substr($entry['balance_type'], 0, 2)) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-3">No ledger entries found.</td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="4">Totals</th>
                        <th>{{ number_format($ledger['totals']['debit'], 2) }}</th>
                        <th>{{ number_format($ledger['totals']['credit'], 2) }}</th>
                        <th>
                            {{ number_format($ledger['closing_balance']['balance'], 2) }}
                            {{ strtoupper(substr($ledger['closing_balance']['type'], 0, 2)) }}
                        </th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
@stop
