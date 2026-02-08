@extends('layouts.modern-admin')

@section('title', 'Employee Details')
@section('page_title', 'Employee Details')

@section('header_actions')
    <a class="btn modern-btn modern-btn-primary" href="{{ route('hr.employees.edit', $employee) }}">
        <i class="fas fa-edit"></i> Edit
    </a>
    <a class="btn modern-btn modern-btn-success" href="{{ route('hr.employees.ledger', $employee) }}">
        <i class="fas fa-book"></i> Ledger
    </a>
@stop

@section('page_content')
<div class="row">
    <div class="col-md-4">
        <div class="card modern-card">
            <div class="card-body">
                <div class="text-center mb-3">
                    @if($employee->photo_path)
                        <img src="{{ asset('storage/' . $employee->photo_path) }}" class="img-thumbnail" style="max-height: 160px;">
                    @else
                        <div class="bg-light border rounded p-4">No Photo</div>
                    @endif
                </div>
                <h5 class="mb-1">{{ $employee->name }}</h5>
                <p class="text-muted mb-2">{{ $employee->email ?? 'No email' }}</p>
                <p class="mb-1"><strong>Phone:</strong> {{ $employee->phone ?? '-' }}</p>
                <p class="mb-1"><strong>NID:</strong> {{ $employee->nid ?? '-' }}</p>
                <p class="mb-1"><strong>Basic Salary:</strong> ৳{{ number_format($employee->basic_salary ?? 0, 2) }}</p>
                <p class="mb-1"><strong>Status:</strong> {{ ucfirst($employee->status ?? 'active') }}</p>
                <p class="mb-0"><strong>Join Date:</strong> {{ optional($employee->join_date)->format('d M, Y') ?? '-' }}</p>
                @if($employee->file_path)
                    <div class="mt-3">
                        <a href="{{ asset('storage/' . $employee->file_path) }}" target="_blank" class="btn btn-sm btn-outline-primary">View Document</a>
                    </div>
                @endif
            </div>
        </div>
    </div>
    <div class="col-md-8">
        <div class="card modern-card mb-4">
            <div class="card-header modern-header">
                <h3 class="card-title"><i class="fas fa-money-check-alt"></i> Recent Payrolls</h3>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead>
                            <tr>
                                <th>Period</th>
                                <th>Gross</th>
                                <th>Net</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($employee->payrolls->take(5) as $payroll)
                                <tr>
                                    <td>{{ $payroll->period_start->format('d M') }} - {{ $payroll->period_end->format('d M, Y') }}</td>
                                    <td>৳{{ number_format($payroll->gross_salary, 2) }}</td>
                                    <td>৳{{ number_format($payroll->net_pay, 2) }}</td>
                                    <td>
                                        <span class="badge {{ $payroll->status === 'paid' ? 'badge-success' : 'badge-warning' }}">
                                            {{ ucfirst($payroll->status) }}
                                        </span>
                                    </td>
                                    <td>
                                        <a href="{{ route('hr.payrolls.show', $payroll) }}" class="btn btn-sm btn-info">View</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-3">No payrolls found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="card modern-card">
            <div class="card-header modern-header">
                <h3 class="card-title"><i class="fas fa-hand-holding-usd"></i> Advances</h3>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Amount</th>
                                <th>Outstanding</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($employee->advances->take(5) as $advance)
                                <tr>
                                    <td>{{ $advance->date->format('d M, Y') }}</td>
                                    <td>৳{{ number_format($advance->amount, 2) }}</td>
                                    <td>৳{{ number_format($advance->outstanding_amount, 2) }}</td>
                                    <td>{{ ucfirst($advance->status) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-3">No advances found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@stop
