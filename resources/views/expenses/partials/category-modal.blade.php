<!-- Create Expense Category Modal -->
<div class="modal fade" id="expenseCategoryModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="expense-category-form">
                <div class="modal-header">
                    <h5 class="modal-title">Create Expense Category</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-danger d-none" id="expense-category-errors"></div>

                    <div class="form-group mb-3">
                        <label for="category_name">Name <span class="required">*</span></label>
                        <input type="text" id="category_name" name="name" class="form-control modern-input" required>
                    </div>

                    <div class="form-group mb-3">
                        <label for="category_code">Code</label>
                        <input type="text" id="category_code" name="code" class="form-control modern-input">
                        <small class="form-text text-muted">Auto-generated, you can edit.</small>
                    </div>

                    <div class="form-group mb-3">
                        <label for="category_group">Expense Group</label>
                        <select id="category_group" name="account_group_id" class="form-control modern-select">
                            <option value="">Auto (default)</option>
                            @foreach($expenseGroups as $group)
                                <option value="{{ $group->id }}">{{ $group->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="category_active" name="is_active" value="1" checked>
                        <label class="form-check-label" for="category_active">Active</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn modern-btn modern-btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn modern-btn modern-btn-primary">
                        <i class="fas fa-save"></i> Save Category
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
