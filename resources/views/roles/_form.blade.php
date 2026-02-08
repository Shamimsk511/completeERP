@php
    $selectedPermissionIdsForForm = collect(old('permission', $selectedPermissionIds ?? []))
        ->map(fn ($id) => (int) $id)
        ->all();
@endphp

@if ($errors->any())
    <div class="alert alert-danger modern-alert">
        <i class="fas fa-exclamation-circle"></i>
        <strong>Please fix the following:</strong>
        <ul class="mb-0 mt-2 pl-3">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form action="{{ $formAction }}" method="POST" id="role-form">
    @csrf
    @if(!empty($formMethod) && strtoupper($formMethod) !== 'POST')
        @method($formMethod)
    @endif

    <div class="card modern-card mb-3">
        <div class="card-header modern-header">
            <h3 class="card-title"><i class="fas fa-shield-alt"></i> Role Details</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-lg-7">
                    <div class="form-group mb-0">
                        <label for="name">Role Name <span class="required">*</span></label>
                        <input
                            type="text"
                            name="name"
                            id="name"
                            class="form-control modern-input @error('name') is-invalid @enderror"
                            value="{{ old('name', $role->name ?? '') }}"
                            placeholder="e.g. Sales Manager"
                            required
                        >
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-lg-5 mt-3 mt-lg-0">
                    <div class="permission-counter-box">
                        <div class="permission-counter-label">Selected Permissions</div>
                        <div class="permission-counter-value" id="selected-count">{{ count($selectedPermissionIdsForForm) }}</div>
                        <small class="text-muted">out of {{ $matrix['total_permissions'] }}</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card modern-card mb-3">
        <div class="card-header modern-header">
            <h3 class="card-title"><i class="fas fa-key"></i> Permission Matrix</h3>
        </div>
        <div class="card-body">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                <div class="permission-search-wrap">
                    <i class="fas fa-search"></i>
                    <input type="text" class="form-control" id="permission-search" placeholder="Search module or permission action">
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <button type="button" class="btn modern-btn modern-btn-outline btn-sm" id="select-all-permissions">
                        <i class="fas fa-check-square"></i> Select All
                    </button>
                    <button type="button" class="btn modern-btn modern-btn-outline btn-sm" id="clear-all-permissions">
                        <i class="fas fa-eraser"></i> Clear
                    </button>
                </div>
            </div>

            <div class="table-responsive permission-matrix-wrap">
                <table class="table table-sm permission-matrix mb-0">
                    <thead>
                        <tr>
                            <th style="width: 60px;" class="text-center">Row</th>
                            <th style="min-width: 220px;">Module</th>
                            @foreach($matrix['actions'] as $action)
                                <th class="text-center action-col" style="min-width: 100px;">
                                    <div class="action-col-label">{{ $action['label'] }}</div>
                                    <input
                                        type="checkbox"
                                        class="action-selector"
                                        data-action="{{ $action['slug'] }}"
                                        title="Toggle {{ $action['label'] }} for all modules"
                                    >
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($matrix['modules'] as $module)
                            <tr class="permission-row" data-search="{{ $module['search_text'] }}">
                                <td class="text-center align-middle">
                                    <input type="checkbox" class="module-selector" data-module="{{ $module['key'] }}" title="Toggle module row">
                                </td>
                                <td class="align-middle">
                                    <div class="module-label">{{ $module['label'] }}</div>
                                    <div class="module-meta" data-module-meta="{{ $module['key'] }}" data-total="{{ $module['total_count'] }}">{{ $module['assigned_count'] }} / {{ $module['total_count'] }} selected</div>
                                </td>
                                @foreach($matrix['actions'] as $action)
                                    @php
                                        $permission = $module['permissions'][$action['name']] ?? null;
                                        $checked = $permission ? in_array($permission['id'], $selectedPermissionIdsForForm, true) : false;
                                    @endphp
                                    <td class="text-center align-middle">
                                        @if($permission)
                                            <div class="custom-control custom-checkbox d-inline-flex">
                                                <input
                                                    type="checkbox"
                                                    class="custom-control-input permission-checkbox action-{{ $action['slug'] }} module-{{ \Illuminate\Support\Str::slug($module['key'], '_') }}"
                                                    id="permission_{{ $permission['id'] }}"
                                                    data-module="{{ $module['key'] }}"
                                                    data-action="{{ $action['slug'] }}"
                                                    name="permission[]"
                                                    value="{{ $permission['id'] }}"
                                                    {{ $checked ? 'checked' : '' }}
                                                >
                                                <label class="custom-control-label" for="permission_{{ $permission['id'] }}"></label>
                                            </div>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ 2 + count($matrix['actions']) }}" class="text-center text-muted py-4">
                                    No permissions available.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-end gap-2">
        <a href="{{ route('roles.index') }}" class="btn modern-btn modern-btn-outline">Cancel</a>
        <button type="submit" class="btn modern-btn modern-btn-primary">
            <i class="fas fa-save"></i> {{ $submitLabel }}
        </button>
    </div>
</form>
