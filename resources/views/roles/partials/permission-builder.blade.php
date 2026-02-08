<div class="rolex-perm-builder" data-role-permission-builder>
    @php
        $selectedPermissionIds = collect(old('permission', $selectedPermissionIds ?? []))
            ->map(fn($id) => (int) $id)
            ->all();
    @endphp

    <div class="rolex-card">
        <div class="rolex-card-header">
            <div>
                <h3 class="rolex-card-title"><i class="fas fa-key mr-1"></i> Permission Matrix</h3>
                <p class="rolex-card-subtitle">Choose exactly what this role can access.</p>
            </div>
        </div>
        <div class="rolex-card-body">
            <div class="rolex-perm-toolbar">
                <div class="rolex-search">
                    <i class="fas fa-search"></i>
                    <input type="text" class="form-control form-control-sm" data-role-search placeholder="Search permissions...">
                </div>
                <div class="rolex-toolbar-actions">
                    <button type="button" class="btn modern-btn modern-btn-outline btn-sm" data-role-select-all>
                        <i class="far fa-check-square mr-1"></i> Toggle All
                    </button>
                    <button type="button" class="btn modern-btn modern-btn-outline btn-sm" data-role-clear>
                        <i class="fas fa-eraser mr-1"></i> Clear
                    </button>
                    <button type="button" class="btn modern-btn modern-btn-outline btn-sm" data-role-expand>
                        <i class="fas fa-chevron-down mr-1"></i> Expand
                    </button>
                    <button type="button" class="btn modern-btn modern-btn-outline btn-sm" data-role-collapse>
                        <i class="fas fa-chevron-up mr-1"></i> Collapse
                    </button>
                </div>
                <div class="rolex-toolbar-summary">
                    Selected: <strong data-role-selected-count>0</strong> / <strong data-role-total-count>0</strong>
                </div>
            </div>

            <div class="rolex-group-list">
                @foreach($groupedPermissions as $group => $permissions)
                    @php
                        $groupKey = preg_replace('/[^a-z0-9_-]/i', '-', strtolower($group));
                    @endphp
                    <section class="rolex-group" data-role-group data-group="{{ $groupKey }}">
                        <div class="rolex-group-head">
                            <button type="button" class="rolex-group-title-btn">
                                <i class="fas fa-chevron-down rolex-chevron"></i>
                                <span>{{ ucfirst($group) }}</span>
                                <span class="rolex-group-meta">
                                    <span data-role-group-selected-count>0</span>/{{ count($permissions) }}
                                </span>
                            </button>
                            <label class="rolex-group-check">
                                <input type="checkbox" class="rolex-group-check-input">
                                Select Group
                            </label>
                        </div>
                        <div class="rolex-group-body">
                            <div class="rolex-chip-grid">
                                @foreach($permissions as $permission)
                                    @php
                                        $permissionLabel = str_replace($group . '-', '', $permission->name);
                                        $displayLabel = \Illuminate\Support\Str::headline(str_replace(['_', '.'], ' ', $permissionLabel));
                                    @endphp
                                    <label class="rolex-chip" data-label="{{ strtolower($displayLabel) }}">
                                        <input
                                            type="checkbox"
                                            class="rolex-perm-check"
                                            name="permission[]"
                                            value="{{ $permission->id }}"
                                            {{ in_array((int)$permission->id, $selectedPermissionIds, true) ? 'checked' : '' }}
                                        >
                                        <span><i class="fas fa-check-circle"></i> {{ $displayLabel }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    </section>
                @endforeach
            </div>
        </div>
    </div>
</div>
