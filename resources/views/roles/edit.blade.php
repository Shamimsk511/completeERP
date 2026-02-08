@extends('adminlte::page')

@section('title', 'Edit Role')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-edit text-info"></i> Edit Role</h1>
        <a href="{{ route('roles.index') }}" class="btn btn-outline-info">
            <i class="fas fa-arrow-left"></i> Back to Roles
        </a>
    </div>
@stop

@section('content')
<div class="card shadow-sm">
    <div class="card-header bg-light">
        <h3 class="card-title">
            <i class="fas fa-user-shield mr-2"></i> {{ $role->name }}
        </h3>
    </div>
    <div class="card-body">
        @if (count($errors) > 0)
            <div class="alert alert-danger alert-dismissible fade show">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h5><i class="icon fas fa-ban"></i> Error!</h5>
                <ul class="list-unstyled mb-0">
                    @foreach ($errors->all() as $error)
                        <li><i class="fas fa-exclamation-circle mr-2"></i>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('roles.update', $role->id) }}">
            @csrf
            @method('PATCH')
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <label for="name" class="font-weight-bold">
                            <i class="fas fa-tag mr-1"></i> Role Name
                        </label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-user-tag"></i></span>
                            </div>
                            <input type="text" name="name" id="name" class="form-control" value="{{ $role->name }}">
                        </div>
                    </div>
                </div>
                
                <div class="col-md-12">
                    <div class="form-group">
                        <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-2">
                            <label class="font-weight-bold mb-0">
                                <i class="fas fa-key mr-1"></i> Assign Permissions
                            </label>
                        </div>

                        <div class="permissions-toolbar">
                            <div class="permissions-search">
                                <i class="fas fa-search"></i>
                                <input type="text" id="permission-search" class="form-control permission-search-input" placeholder="Search permissions...">
                            </div>
                            <div class="permissions-actions">
                                <button type="button" id="selectAllPermissions" class="btn btn-outline-primary btn-sm">
                                    <i class="fas fa-check-square mr-1"></i> Select All
                                </button>
                                <button type="button" id="clearAllPermissions" class="btn btn-outline-secondary btn-sm">
                                    <i class="fas fa-eraser mr-1"></i> Clear
                                </button>
                                <button type="button" id="expandAllPermissions" class="btn btn-outline-secondary btn-sm">
                                    <i class="fas fa-chevron-down mr-1"></i> Expand
                                </button>
                                <button type="button" id="collapseAllPermissions" class="btn btn-outline-secondary btn-sm">
                                    <i class="fas fa-chevron-up mr-1"></i> Collapse
                                </button>
                            </div>
                            <div class="permissions-count">
                                Selected: <span id="selectedCount">0</span>
                            </div>
                        </div>
                        
                        <div class="accordion" id="permissionsAccordion">
                            @foreach($groupedPermissions as $group => $permissions)
                                <div class="card mb-2 permission-group" data-group="{{ $group }}">
                                    <div class="card-header bg-light permission-group-header" id="heading{{ $group }}">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <h5 class="mb-0">
                                                <button class="btn btn-link text-dark" type="button" data-toggle="collapse" data-target="#collapse{{ $group }}" aria-expanded="false" aria-controls="collapse{{ $group }}">
                                                    <i class="fas fa-layer-group mr-2"></i> {{ ucfirst($group) }}
                                                </button>
                                            </h5>
                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox" class="custom-control-input group-selector" id="group_{{ $group }}" data-group="{{ $group }}">
                                                <label class="custom-control-label" for="group_{{ $group }}">Select All</label>
                                            </div>
                                        </div>
                                    </div>

                                    <div id="collapse{{ $group }}" class="collapse permission-collapse" aria-labelledby="heading{{ $group }}">
                                        <div class="card-body">
                                            <div class="row">
                                                @foreach($permissions as $value)
                                                    <div class="col-lg-3 col-md-4 col-sm-6 mb-2 permission-item" data-name="{{ strtolower(str_replace($group.'-', '', $value->name)) }}">
                                                        <div class="custom-control custom-checkbox">
                                                            <input type="checkbox" class="custom-control-input permission-checkbox" data-group="{{ $group }}" id="permission_{{ $value->id }}" name="permission[]" value="{{ $value->id }}" {{ in_array($value->id, $rolePermissions) ? 'checked' : '' }}>
                                                            <label class="custom-control-label" for="permission_{{ $value->id }}">{{ str_replace($group.'-', '', $value->name) }}</label>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="text-center mt-4">
                <button type="submit" class="btn btn-info px-5">
                    <i class="fas fa-save mr-2"></i> Update Role
                </button>
            </div>
        </form>
    </div>
</div>
@stop

@section('css')
<style>
    .card {
        border-radius: 0.5rem;
        border: none;
    }
    .form-control, .input-group-text {
        border-radius: 0.25rem;
    }
    .custom-checkbox .custom-control-label::before {
        border-radius: 0.25rem;
    }
    .btn-link {
        text-decoration: none;
    }
    .btn-link:hover {
        text-decoration: none;
    }
    .permissions-toolbar {
        display: flex;
        flex-wrap: wrap;
        gap: 0.75rem;
        align-items: center;
        justify-content: space-between;
        padding: 0.75rem;
        border: 1px solid #e5e7eb;
        border-radius: 0.5rem;
        background: #f9fafb;
        margin-bottom: 0.75rem;
    }
    .permissions-search {
        position: relative;
        min-width: 220px;
        flex: 1;
        max-width: 320px;
    }
    .permissions-search i {
        position: absolute;
        left: 10px;
        top: 50%;
        transform: translateY(-50%);
        color: #9ca3af;
    }
    .permission-search-input {
        padding-left: 30px;
        height: 34px;
        font-size: 0.85rem;
    }
    .permissions-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
    }
    .permissions-count {
        font-size: 0.85rem;
        color: #6b7280;
    }
    .permission-group-header {
        background: #f8fafc;
    }
    .permission-item {
        font-size: 0.85rem;
    }
</style>
@stop

@section('js')
<script>
    $(document).ready(function() {
        function updateSelectedCount() {
            $('#selectedCount').text($('.permission-checkbox:checked').length);
        }

        // Handle "Select All Permissions" button
        $('#selectAllPermissions').click(function() {
            const allChecked = $('.permission-checkbox:checked').length === $('.permission-checkbox').length;
            
            // If all are checked, uncheck all. Otherwise, check all
            $('.permission-checkbox').prop('checked', !allChecked);
            
            // Update all group selectors
            updateAllGroupSelectors();
            
            // Update button text
            updateSelectAllButtonText();
            updateSelectedCount();
        });

        $('#clearAllPermissions').click(function() {
            $('.permission-checkbox').prop('checked', false);
            updateAllGroupSelectors();
            updateSelectAllButtonText();
            updateSelectedCount();
        });

        $('#expandAllPermissions').click(function() {
            $('.permission-collapse').collapse('show');
        });

        $('#collapseAllPermissions').click(function() {
            $('.permission-collapse').collapse('hide');
        });
        
        // Handle "Select All" checkboxes for groups
        $('.group-selector').change(function() {
            const group = $(this).data('group');
            const isChecked = $(this).prop('checked');
            
            $(`input.permission-checkbox[data-group="${group}"]`).prop('checked', isChecked);
            
            // Update main button text
            updateSelectAllButtonText();
            updateSelectedCount();
        });
        
        // Update group selector when individual permissions change
        $('.permission-checkbox').change(function() {
            const group = $(this).data('group');
            const totalInGroup = $(`input.permission-checkbox[data-group="${group}"]`).length;
            const checkedInGroup = $(`input.permission-checkbox[data-group="${group}"]:checked`).length;
            
            $(`#group_${group}`).prop('checked', totalInGroup === checkedInGroup);
            
            // Update main button text
            updateSelectAllButtonText();
            updateSelectedCount();
        });
        
        // Function to update all group selectors
        function updateAllGroupSelectors() {
            $('.group-selector').each(function() {
                const group = $(this).data('group');
                const totalInGroup = $(`input.permission-checkbox[data-group="${group}"]`).length;
                const checkedInGroup = $(`input.permission-checkbox[data-group="${group}"]:checked`).length;
                
                $(this).prop('checked', totalInGroup === checkedInGroup && totalInGroup > 0);
            });
        }
        
        // Function to update the "Select All" button text
        function updateSelectAllButtonText() {
            const allChecked = $('.permission-checkbox:checked').length === $('.permission-checkbox').length;
            
            if (allChecked) {
                $('#selectAllPermissions').html('<i class="fas fa-times-circle mr-1"></i> Deselect All Permissions');
                $('#selectAllPermissions').removeClass('btn-outline-primary').addClass('btn-outline-danger');
            } else {
                $('#selectAllPermissions').html('<i class="fas fa-check-square mr-1"></i> Select All Permissions');
                $('#selectAllPermissions').removeClass('btn-outline-danger').addClass('btn-outline-primary');
            }
        }

        function expandGroupsWithSelection() {
            $('.permission-group').each(function() {
                var hasChecked = $(this).find('.permission-checkbox:checked').length > 0;
                if (hasChecked) {
                    $(this).find('.permission-collapse').addClass('show');
                }
            });
        }

        function applyPermissionSearch() {
            var query = $('#permission-search').val().toLowerCase().trim();
            $('.permission-group').each(function() {
                var anyVisible = false;
                $(this).find('.permission-item').each(function() {
                    var name = ($(this).data('name') || '').toString().toLowerCase();
                    var show = !query || name.indexOf(query) !== -1;
                    $(this).toggle(show);
                    if (show) {
                        anyVisible = true;
                    }
                });

                if (anyVisible) {
                    $(this).show();
                    if (query) {
                        $(this).find('.permission-collapse').addClass('show');
                    }
                } else {
                    $(this).hide();
                }
            });
        }

        $('#permission-search').on('input', function() {
            applyPermissionSearch();
        });

        // Set initial state of group selectors
        updateAllGroupSelectors();
        updateSelectAllButtonText();
        updateSelectedCount();
        expandGroupsWithSelection();
    });
</script>
@stop
