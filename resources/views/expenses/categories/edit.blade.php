@extends('layouts.modern-admin')

@section('title', 'Edit Expense Category')
@section('page_title', 'Edit Expense Category')

@section('page_content')
    <div class="card modern-card">
        <div class="card-body">
            <form action="{{ route('expenses.categories.update', $category) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label for="name">Category Name <span class="required">*</span></label>
                    <input type="text" name="name" id="name" class="form-control modern-input" value="{{ old('name', $category->name) }}" required>
                </div>

                <div class="form-group">
                    <label for="account_group_id">Expense Group</label>
                    <select name="account_group_id" id="account_group_id" class="form-control modern-select">
                        <option value="">Auto (Indirect Expenses)</option>
                        @foreach($expenseGroups as $group)
                            <option value="{{ $group->id }}" {{ old('account_group_id', $category->account_group_id) == $group->id ? 'selected' : '' }}>
                                {{ $group->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label for="code">Optional Code</label>
                    <input type="text" name="code" id="code" class="form-control modern-input" value="{{ old('code', $category->code) }}">
                </div>

                <div class="form-group">
                    <label for="notes">Notes</label>
                    <textarea name="notes" id="notes" class="form-control modern-textarea" rows="3">{{ old('notes', $category->notes) }}</textarea>
                </div>

                <div class="form-group form-check">
                    <input type="checkbox" class="form-check-input" id="is_active" name="is_active" value="1" {{ old('is_active', $category->is_active) ? 'checked' : '' }}>
                    <label class="form-check-label" for="is_active">Active</label>
                </div>

                <div class="mt-3">
                    <button type="submit" class="btn modern-btn modern-btn-primary">
                        <i class="fas fa-save"></i> Update Category
                    </button>
                    <a href="{{ route('expenses.categories.index') }}" class="btn modern-btn modern-btn-secondary">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
@endsection
