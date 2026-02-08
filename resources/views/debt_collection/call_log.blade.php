@extends('adminlte::page')

@section('title', 'Call History')

@section('content_header')
    <h1>Call History - {{ $customer->name }}</h1>
@stop

@section('content')
<div class="row">
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Customer Details</h3>
            </div>
            <div class="card-body">
                <p><strong>Name:</strong> {{ $customer->name }}</p>
                <p><strong>Phone:</strong> {{ $customer->phone }}</p>
                <p><strong>Outstanding Balance:</strong> {{ number_format($customer->outstanding_balance, 2) }}</p>
                <p><strong>Total Calls:</strong> {{ $tracking->calls_made ?? 0 }}</p>
                <p><strong>Missed Calls:</strong> {{ $tracking->missed_calls ?? 0 }}</p>
                <p><strong>Priority:</strong> {{ ucfirst($tracking->priority ?? 'medium') }}</p>
                <p><strong>Due Date:</strong> {{ $tracking->due_date ? $tracking->due_date->format('Y-m-d') : 'Not set' }}</p>
                <a href="{{ route('debt-collection.index') }}" class="btn btn-default">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
                <a href="{{ route('debt-collection.edit-tracking', $customer->id) }}" class="btn btn-primary">
                    <i class="fas fa-edit"></i> Edit Tracking
                </a>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Call Notes</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-toggle="modal" data-target="#logCallModal">
                        <i class="fas fa-plus"></i> Log Call
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="timeline">
                    @if($tracking && $tracking->notes)
                        @php
                            $notes = explode("\n", $tracking->notes);
                            $notes = array_filter($notes);
                        @endphp
                        
                        @foreach($notes as $note)
                            @php
                                preg_match('/\[(.*?)\]/', $note, $dateMatch);
                                $date = $dateMatch[1] ?? '';
                                $content = preg_replace('/\[(.*?)\]/', '', $note);
                            @endphp
                            
                            <div>
                                <i class="fas fa-phone bg-blue"></i>
                                <div class="timeline-item">
                                    <span class="time"><i class="fas fa-clock"></i> {{ $date }}</span>
                                    <div class="timeline-body">{{ trim($content) }}</div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="text-center py-3">
                            <p>No call history found</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Log Call Modal -->
<div class="modal fade" id="logCallModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Log Call</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('debt-collection.log-call', $customer->id) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label>Call Status</label>
                        <select class="form-control" name="call_status">
                            <option value="successful">Successful</option>
                            <option value="missed">Missed</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Notes</label>
                        <textarea class="form-control" name="notes" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>
@stop
