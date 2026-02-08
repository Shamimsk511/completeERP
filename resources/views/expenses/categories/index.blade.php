@extends('layouts.modern-admin')

@section('title', 'Expense Categories')
@section('page_title', 'Expense Categories')

@section('header_actions')
    <a href="{{ route('expenses.categories.create') }}" class="btn modern-btn modern-btn-primary">
        <i class="fas fa-plus"></i> New Category
    </a>
@stop

@section('page_content')
    <div class="card modern-card">
        <div class="card-body table-responsive">
            <table class="table modern-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Account</th>
                        <th>Group</th>
                        <th>Status</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($categories as $category)
                        <tr>
                            <td>{{ $category->name }}</td>
                            <td>
                                {{ $category->account?->name ?? 'N/A' }}
                                @if($category->account?->code)
                                    <small class="text-muted">({{ $category->account->code }})</small>
                                @endif
                            </td>
                            <td>{{ $category->accountGroup?->name ?? 'N/A' }}</td>
                            <td>
                                @if($category->is_active)
                                    <span class="badge badge-success">Active</span>
                                @else
                                    <span class="badge badge-secondary">Inactive</span>
                                @endif
                            </td>
                            <td class="text-right">
                                <a href="{{ route('expenses.categories.edit', $category) }}" class="btn btn-sm btn-info">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('expenses.categories.destroy', $category) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete this category?')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted">No categories found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            {{ $categories->links() }}
        </div>
    </div>
@endsection
