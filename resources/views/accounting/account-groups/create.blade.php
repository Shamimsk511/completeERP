@extends('layouts.modern-admin')

@section('title', 'Create Account Group')

@section('page_title', 'Create Account Group')

@section('page_content')
    <form action="{{ route('accounting.account-groups.store') }}" method="POST">
        @csrf

        <div class="card modern-card">
            <div class="card-header modern-header">
                <h3 class="card-title">
                    <i class="fas fa-folder-plus"></i> Group Details
                </h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">Group Name <span class="required">*</span></label>
                            <input type="text" name="name" class="form-control modern-input @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">Group Code <span class="required">*</span></label>
                            <div class="input-group">
                                <input type="text" name="code" id="code" class="form-control modern-input @error('code') is-invalid @enderror" value="{{ old('code') }}" required placeholder="Auto-generated from name">
                                <div class="input-group-append">
                                    <button type="button" class="btn btn-outline-secondary" id="regenerate-code" title="Regenerate code">
                                        <i class="fas fa-sync-alt"></i>
                                    </button>
                                </div>
                            </div>
                            @error('code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">Auto-generated, but can be edited</small>
                        </div>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">Parent Group</label>
                            <select name="parent_id" class="form-control select2 @error('parent_id') is-invalid @enderror" id="parent_id">
                                <option value="">None (Root Level)</option>
                                @foreach($parentGroups as $group)
                                    <option value="{{ $group->id }}" data-nature="{{ $group->nature }}" {{ old('parent_id') == $group->id ? 'selected' : '' }}>
                                        {{ $group->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('parent_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">Leave empty to create a root-level group</small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">Nature <span class="required">*</span></label>
                            <input type="hidden" name="nature" id="nature_hidden" value="{{ old('nature', $natures[0] ?? '') }}">
                            <select class="form-control modern-select @error('nature') is-invalid @enderror" id="nature" required>
                                @foreach($natures as $nature)
                                    <option value="{{ $nature }}" {{ old('nature') == $nature ? 'selected' : '' }}>{{ ucfirst($nature) }}</option>
                                @endforeach
                            </select>
                            @error('nature')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">Inherited from parent if selected</small>
                        </div>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">Affects Gross Profit <span class="required">*</span></label>
                            <select name="affects_gross_profit" class="form-control modern-select @error('affects_gross_profit') is-invalid @enderror" required>
                                <option value="no" {{ old('affects_gross_profit') == 'no' ? 'selected' : '' }}>No</option>
                                <option value="yes" {{ old('affects_gross_profit') == 'yes' ? 'selected' : '' }}>Yes</option>
                            </select>
                            @error('affects_gross_profit')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">Display Order</label>
                            <input type="number" name="display_order" class="form-control modern-input @error('display_order') is-invalid @enderror" value="{{ old('display_order', 0) }}" min="0">
                            @error('display_order')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control modern-textarea" rows="3">{{ old('description') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="text-center mt-4 mb-4">
            <button type="submit" class="btn modern-btn modern-btn-primary btn-lg">
                <i class="fas fa-save"></i> Create Group
            </button>
            <a href="{{ route('accounting.account-groups.index') }}" class="btn btn-outline-secondary btn-lg ml-3">
                <i class="fas fa-times"></i> Cancel
            </a>
        </div>
    </form>
@stop

@section('additional_js')
<script>
$(document).ready(function() {
    let codeManuallyEdited = {{ old('code') ? 'true' : 'false' }};

    // Generate code from name
    function generateCode(name) {
        return name
            .toUpperCase()
            .replace(/[^A-Z0-9\s]/g, '') // Remove special chars
            .replace(/\s+/g, '-')         // Replace spaces with hyphens
            .substring(0, 30);            // Limit length
    }

    // Auto-generate code when name changes
    $('input[name="name"]').on('input', function() {
        if (!codeManuallyEdited) {
            const name = $(this).val();
            $('#code').val(generateCode(name));
        }
    });

    // Track if user manually edits the code
    $('#code').on('input', function() {
        codeManuallyEdited = true;
    });

    // Regenerate code button
    $('#regenerate-code').on('click', function() {
        const name = $('input[name="name"]').val();
        $('#code').val(generateCode(name));
        codeManuallyEdited = false;
    });

    // Sync hidden field with select value
    function syncNatureHidden() {
        $('#nature_hidden').val($('#nature').val());
    }

    // When nature select changes, update hidden field
    $('#nature').on('change', function() {
        syncNatureHidden();
    });

    // When parent is selected, auto-set nature
    $('#parent_id').on('change', function() {
        const selectedOption = $(this).find('option:selected');
        const nature = selectedOption.data('nature');

        if (nature) {
            $('#nature').val(nature).prop('disabled', true);
        } else {
            $('#nature').prop('disabled', false);
        }
        // Always sync hidden field
        syncNatureHidden();
    }).trigger('change');

    // Initial sync
    syncNatureHidden();
});
</script>
@stop
