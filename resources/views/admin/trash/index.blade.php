@extends('adminlte::page')

@section('title', 'Trash')

@section('content_header')
    <h1>Trash</h1>
@stop

@section('content')
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="card">
        <div class="card-header">
            <div class="d-flex flex-wrap" style="gap:8px;">
                @foreach($types as $key => $label)
                    <a href="{{ route('trash.index', ['type' => $key]) }}"
                       class="btn btn-sm {{ $type === $key ? 'btn-primary' : 'btn-outline-secondary' }}">
                        {{ $label }}
                    </a>
                @endforeach
            </div>
        </div>

        <div class="card-body table-responsive p-0">
            <table class="table table-hover table-striped mb-0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Details</th>
                        <th>Deleted By</th>
                        <th>Deleted At</th>
                        <th style="width: 220px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($records as $record)
                        <tr>
                            <td>{{ $record->id }}</td>
                            <td>
                                @if($type === 'transactions')
                                    <div><strong>{{ ucfirst($record->type) }}</strong> - {{ number_format((float) $record->amount, 2) }}</div>
                                    <div>{{ $record->purpose }}</div>
                                    <small>Customer: {{ $record->customer->name ?? 'N/A' }}</small>
                                @elseif($type === 'customers')
                                    <div><strong>{{ $record->name }}</strong></div>
                                    <small>{{ $record->phone }}</small>
                                @elseif($type === 'challans')
                                    <div><strong>{{ $record->challan_number }}</strong></div>
                                    <small>Invoice: {{ $record->invoice->invoice_number ?? 'N/A' }}</small>
                                @elseif($type === 'invoices')
                                    <div><strong>{{ $record->invoice_number }}</strong></div>
                                    <small>Customer: {{ $record->customer->name ?? 'N/A' }}</small>
                                    <div><small>Total: {{ number_format((float) $record->total, 2) }}</small></div>
                                @elseif($type === 'products')
                                    <div><strong>{{ $record->name }}</strong></div>
                                    <small>{{ $record->company->name ?? 'N/A' }} / {{ $record->category->name ?? 'N/A' }}</small>
                                @elseif($type === 'other-deliveries')
                                    <div><strong>{{ $record->challan_number }}</strong></div>
                                    <small>Recipient: {{ $record->recipient_name }}</small>
                                @endif
                            </td>
                            <td>
                                @if($record->deletedBy)
                                    <div><strong>{{ $record->deletedBy->name }}</strong></div>
                                    <small>{{ $record->deletedBy->email ?? 'User ID: ' . $record->deletedBy->id }}</small>
                                @else
                                    <small>Legacy / Unknown</small>
                                @endif
                            </td>
                            <td>{{ optional($record->deleted_at)->format('d M Y, h:i A') }}</td>
                            <td>
                                <form action="{{ route('trash.restore', ['type' => $type, 'id' => $record->id]) }}"
                                      method="POST"
                                      style="display:inline-block;">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-success">Restore</button>
                                </form>

                                <form action="{{ route('trash.force-delete', ['type' => $type, 'id' => $record->id]) }}"
                                      method="POST"
                                      style="display:inline-block;"
                                      onsubmit="return confirm('Permanently delete this record? This cannot be undone.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger">Delete Permanently</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-4">No records found in trash.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(method_exists($records, 'links'))
            <div class="card-footer">
                {{ $records->links() }}
            </div>
        @endif
    </div>
@stop
