@extends('layouts.modern-admin')

@section('title', 'Expenses')
@section('page_title', 'Expenses')

@section('header_actions')
    <a href="{{ route('expenses.create') }}" class="btn modern-btn modern-btn-primary">
        <i class="fas fa-plus"></i> Add Expense
    </a>
@stop

@section('page_content')
    <div class="card modern-card mb-3">
        <div class="card-body">
            <form method="GET" class="form-inline">
                <div class="form-group mr-2">
                    <label class="mr-2" for="from_date">From</label>
                    <input type="date" name="from_date" id="from_date" class="form-control" value="{{ request('from_date') }}">
                </div>
                <div class="form-group mr-2">
                    <label class="mr-2" for="to_date">To</label>
                    <input type="date" name="to_date" id="to_date" class="form-control" value="{{ request('to_date') }}">
                </div>
                <div class="form-group mr-2 d-flex align-items-center">
                    <label class="mr-2 mb-0" for="category_id">Category</label>
                    <button type="button" class="btn btn-sm modern-btn modern-btn-outline mr-2" data-toggle="modal" data-target="#expenseCategoryModal" title="Add Category">
                        <i class="fas fa-plus"></i>
                    </button>
                    <select name="category_id" id="category_id" class="form-control" data-expense-category-select>
                        <option value="">All</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="btn btn-secondary">Filter</button>
            </form>
        </div>
    </div>

    <div class="card modern-card">
        <div class="card-body table-responsive">
            <table class="table modern-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Category</th>
                        <th>Amount</th>
                        <th>Payment Account</th>
                        <th>Reference</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($expenses as $expense)
                        <tr>
                            <td>{{ $expense->expense_date->format('d M, Y') }}</td>
                            <td>{{ $expense->category?->name ?? 'N/A' }}</td>
                            <td>৳{{ number_format($expense->amount, 2) }}</td>
                            <td>{{ $expense->paymentAccount?->name ?? 'N/A' }}</td>
                            <td>{{ $expense->reference ?? '-' }}</td>
                            <td class="text-right">
                                <a href="{{ route('expenses.edit', $expense) }}" class="btn btn-sm btn-info">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('expenses.destroy', $expense) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete this expense?')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted">No expenses found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            {{ $expenses->links() }}
        </div>
    </div>

    @include('expenses.partials.category-modal')
@endsection

@include('expenses.partials.category-modal-script')
