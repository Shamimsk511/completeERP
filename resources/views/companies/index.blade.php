@extends('layouts.modern-admin')

@section('title', 'Companies')

@section('page_title', 'Companies')

@section('header_actions')
    <a href="{{ route('companies.create') }}" class="btn modern-btn modern-btn-primary">
        <i class="fas fa-plus"></i> Add New Company
    </a>
@stop

@section('page_content')
    @if($message = Session::get('success'))
        <div class="alert alert-success">
            {{ $message }}
        </div>
    @endif

    <div class="card modern-card">
        <div class="card-header modern-header">
            <h3 class="card-title"><i class="fas fa-building"></i> Companies</h3>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="company-search" class="form-label">Search</label>
                    <input type="text" id="company-search" class="form-control modern-input" placeholder="Search by name, contact, or type">
                </div>
                <div class="col-md-6 text-md-right d-flex align-items-end justify-content-md-end">
                    <span class="badge badge-light">{{ $companies->count() }} Companies</span>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table modern-table" id="companies-table">
                    <thead>
                        <tr>
                            <th>Company</th>
                            <th>Type</th>
                            <th>Contact</th>
                            <th>Opening Balance</th>
                            <th>Balance</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($companies as $company)
                            @php
                                $openingBalance = $company->opening_balance ?? 0;
                                $openingType = ($company->opening_balance_type ?? 'credit') === 'debit' ? 'Dr' : 'Cr';
                                $ledger = $company->ledgerAccount;
                                $currentBalance = $ledger?->current_balance ?? null;
                                $currentType = ($ledger?->current_balance_type ?? 'credit') === 'debit' ? 'Dr' : 'Cr';
                            @endphp
                            <tr>
                                <td>
                                    <a href="{{ route('companies.show', $company->id) }}" class="font-weight-bold">
                                        {{ $company->name }}
                                    </a>
                                </td>
                                <td>
                                    <span class="badge badge-info text-uppercase">
                                        {{ $company->type ? ucfirst($company->type) : 'Both' }}
                                    </span>
                                </td>
                                <td>{{ $company->contact ?? 'N/A' }}</td>
                                <td>
                                    ৳{{ number_format($openingBalance, 2) }} {{ $openingType }}
                                </td>
                                <td>
                                    @if($currentBalance === null)
                                        <span class="text-muted">—</span>
                                    @else
                                        ৳{{ number_format($currentBalance, 2) }} {{ $currentType }}
                                    @endif
                                </td>
                                <td>{{ $company->created_at->format('M d, Y') }}</td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="{{ route('companies.show', $company->id) }}" class="btn modern-btn modern-btn-info btn-sm" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('companies.edit', $company->id) }}" class="btn modern-btn modern-btn-warning btn-sm" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('companies.destroy', $company->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete {{ $company->name }}?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn modern-btn modern-btn-danger btn-sm" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">No companies found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@stop

@section('additional_js')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('company-search');
            const rows = document.querySelectorAll('#companies-table tbody tr');

            if (!searchInput) {
                return;
            }

            searchInput.addEventListener('input', function() {
                const query = this.value.toLowerCase();
                rows.forEach(function(row) {
                    const text = row.innerText.toLowerCase();
                    row.style.display = text.includes(query) ? '' : 'none';
                });
            });
        });
    </script>
@stop
