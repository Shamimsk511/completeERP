@extends('layouts.modern-admin')

@section('title', 'Create Expense Category')
@section('page_title', 'Create Expense Category')

@section('page_content')
    <div class="card modern-card">
        <div class="card-body">
            <form action="{{ route('expenses.categories.store') }}" method="POST">
                @csrf

                <div class="form-group">
                    <label for="name">Category Name <span class="required">*</span></label>
                    <input type="text" name="name" id="name" class="form-control modern-input" value="{{ old('name') }}" required>
                </div>

                <div class="form-group">
                    <label for="account_group_id">Expense Group</label>
                    <select name="account_group_id" id="account_group_id" class="form-control modern-select">
                        <option value="">Auto (Indirect Expenses)</option>
                        @foreach($expenseGroups as $group)
                            <option value="{{ $group->id }}" {{ old('account_group_id') == $group->id ? 'selected' : '' }}>
                                {{ $group->name }}
                            </option>
                        @endforeach
                    </select>
                    <small class="form-text text-muted">A linked expense account will be created automatically.</small>
                </div>

                <div class="form-group">
                    <label for="code">Optional Code</label>
                    <input type="text" name="code" id="code" class="form-control modern-input" value="{{ old('code') }}">
                </div>

                <div class="form-group">
                    <label for="notes">Notes</label>
                    <textarea name="notes" id="notes" class="form-control modern-textarea" rows="3">{{ old('notes') }}</textarea>
                </div>

                <div class="form-group form-check">
                    <input type="checkbox" class="form-check-input" id="is_active" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                    <label class="form-check-label" for="is_active">Active</label>
                </div>

                <div class="mt-3">
                    <button type="submit" class="btn modern-btn modern-btn-primary">
                        <i class="fas fa-save"></i> Save Category
                    </button>
                    <a href="{{ route('expenses.categories.index') }}" class="btn modern-btn modern-btn-secondary">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('js')
<script>
    (function() {
        const nameInput = document.getElementById('name');
        const codeInput = document.getElementById('code');
        if (!nameInput || !codeInput) {
            return;
        }

        let codeTouched = false;
        codeInput.addEventListener('input', () => {
            codeTouched = true;
        });

        const toCode = (value) => {
            const base = (value || '')
                .toString()
                .trim()
                .toUpperCase()
                .replace(/[^A-Z0-9]+/g, '-')
                .replace(/^-+|-+$/g, '')
                .slice(0, 20);
            return base ? `EXP-${base}` : '';
        };

        nameInput.addEventListener('input', () => {
            if (codeTouched && codeInput.value.trim() !== '') {
                return;
            }
            codeInput.value = toCode(nameInput.value);
        });
    })();
</script>
@endpush
