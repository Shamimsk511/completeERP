@extends('layouts.modern-admin')

@section('title', 'Tiles Calculation Settings')

@section('page_title', 'Tiles Calculation Settings Management')

@section('header_actions')
    <button type="button" class="btn modern-btn modern-btn-primary" data-toggle="modal" data-target="#createSettingsModal">
        <i class="fas fa-plus"></i> Add New Settings
    </button>
@stop

@section('page_content')
    <!-- Statistics Cards Row -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6">
            <div class="card modern-card stat-card total-stat">
                <div class="card-body modern-card-body">
                    <div class="stat-content">
                        <div class="stat-icon">
                            <i class="fas fa-cogs"></i>
                        </div>
                        <div class="stat-info">
                            <h3 class="stat-number">{{ $settings->count() }}</h3>
                            <p class="stat-label">Total Settings</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="card modern-card stat-card light-stat">
                <div class="card-body modern-card-body">
                    <div class="stat-content">
                        <div class="stat-icon">
                            <i class="fas fa-sun"></i>
                        </div>
                        <div class="stat-info">
                            <h3 class="stat-number">{{ number_format($settings->avg('light_times'), 1) }}</h3>
                            <p class="stat-label">Avg Light Times</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="card modern-card stat-card deco-stat">
                <div class="card-body modern-card-body">
                    <div class="stat-content">
                        <div class="stat-icon">
                            <i class="fas fa-palette"></i>
                        </div>
                        <div class="stat-info">
                            <h3 class="stat-number">{{ number_format($settings->avg('deco_times'), 1) }}</h3>
                            <p class="stat-label">Avg Deco Times</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="card modern-card stat-card deep-stat">
                <div class="card-body modern-card-body">
                    <div class="stat-content">
                        <div class="stat-icon">
                            <i class="fas fa-layer-group"></i>
                        </div>
                        <div class="stat-info">
                            <h3 class="stat-number">{{ number_format($settings->avg('deep_times'), 1) }}</h3>
                            <p class="stat-label">Avg Deep Times</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="card modern-card mb-4">
        <div class="card-header modern-header filter-header">
            <h3 class="card-title">
                <i class="fas fa-filter"></i> Filter Options
            </h3>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                    <i class="fas fa-minus"></i>
                </button>
            </div>
        </div>
        <div class="card-body modern-card-body">
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group modern-form-group">
                        <label for="category_filter" class="modern-label">Category</label>
                        <select class="form-control modern-select select2" id="category_filter">
                            <option value="">All Categories</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->name }}">{{ $category->name }} ({{ $category->height }}×{{ $category->width }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group modern-form-group">
                        <label for="light_range" class="modern-label">Light Times Range</label>
                        <select class="form-control modern-select" id="light_range">
                            <option value="">All Ranges</option>
                            <option value="0-2">0 - 2</option>
                            <option value="2-4">2 - 4</option>
                            <option value="4-6">4 - 6</option>
                            <option value="6+">6+</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group modern-form-group">
                        <label>&nbsp;</label>
                        <button type="button" id="filter_button" class="btn modern-btn modern-btn-primary form-control">
                            <i class="fas fa-search"></i> Apply Filter
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Success/Error Messages -->
    @if(session('success'))
        <div class="alert modern-alert modern-alert-success" id="success-alert">
            <div class="alert-content">
                <i class="fas fa-check-circle alert-icon"></i>
                <div class="alert-message">
                    <strong>Success!</strong>
                    <span>{{ session('success') }}</span>
                </div>
                <button type="button" class="alert-close" onclick="this.parentElement.parentElement.style.display='none'">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
    @endif
    
    @if(session('error'))
        <div class="alert modern-alert modern-alert-error" id="error-alert">
            <div class="alert-content">
                <i class="fas fa-exclamation-triangle alert-icon"></i>
                <div class="alert-message">
                    <strong>Error!</strong>
                    <span>{{ session('error') }}</span>
                </div>
                <button type="button" class="alert-close" onclick="this.parentElement.parentElement.style.display='none'">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
    @endif

    <!-- Settings Table Card -->
    <div class="card modern-card">
        <div class="card-header modern-header settings-header">
            <h3 class="card-title">
                <i class="fas fa-cogs"></i> All Calculation Settings
            </h3>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                    <i class="fas fa-minus"></i>
                </button>
                <button type="button" class="btn btn-tool" onclick="refreshTable()">
                    <i class="fas fa-sync-alt"></i>
                </button>
            </div>
        </div>
        <div class="card-body modern-card-body">
            <div class="table-container">
                <div class="table-responsive modern-table-responsive">
                    <table class="table modern-table" id="settings-table">
                        <thead class="modern-thead">
                            <tr>
                                <th width="8%">
                                    <div class="th-content">
                                        <i class="fas fa-hashtag"></i>
                                        <span>ID</span>
                                    </div>
                                </th>
                                <th width="25%">
                                    <div class="th-content">
                                        <i class="fas fa-th-large"></i>
                                        <span>Category</span>
                                    </div>
                                </th>
                                <th width="15%">
                                    <div class="th-content">
                                        <i class="fas fa-sun"></i>
                                        <span>Light Times</span>
                                    </div>
                                </th>
                                <th width="15%">
                                    <div class="th-content">
                                        <i class="fas fa-palette"></i>
                                        <span>Deco Times</span>
                                    </div>
                                </th>
                                <th width="15%">
                                    <div class="th-content">
                                        <i class="fas fa-layer-group"></i>
                                        <span>Deep Times</span>
                                    </div>
                                </th>
                                <th width="12%">
                                    <div class="th-content">
                                        <i class="fas fa-calculator"></i>
                                        <span>Total</span>
                                    </div>
                                </th>
                                <th width="10%">
                                    <div class="th-content">
                                        <i class="fas fa-cogs"></i>
                                        <span>Actions</span>
                                    </div>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="modern-tbody">
                            @foreach($settings as $setting)
                                <tr>
                                    <td>
                                        <span class="id-badge">{{ $setting->id }}</span>
                                    </td>
                                    <td>
                                        <div class="category-info">
                                            <strong class="category-name">{{ $setting->category->name }}</strong>
                                            <small class="text-muted d-block">
                                                <i class="fas fa-expand-arrows-alt"></i> {{ $setting->category->height }}×{{ $setting->category->width }}
                                            </small>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="times-badge light-badge">{{ $setting->light_times }}×</span>
                                    </td>
                                    <td>
                                        <span class="times-badge deco-badge">{{ $setting->deco_times }}×</span>
                                    </td>
                                    <td>
                                        <span class="times-badge deep-badge">{{ $setting->deep_times }}×</span>
                                    </td>
                                    <td>
                                        <div class="total-display">
                                            <span class="total-value">{{ $setting->light_times + $setting->deco_times + $setting->deep_times }}</span>
                                            <small class="text-muted d-block">Combined</small>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <button type="button" class="action-btn action-btn-edit edit-btn" 
                                                    data-id="{{ $setting->id }}"
                                                    data-category="{{ $setting->tiles_category_id }}"
                                                    data-light="{{ $setting->light_times }}"
                                                    data-deco="{{ $setting->deco_times }}"
                                                    data-deep="{{ $setting->deep_times }}"
                                                    title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button type="button" class="action-btn action-btn-delete" 
                                                    onclick="deleteSettings({{ $setting->id }})" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Create Settings Modal -->
    <div class="modal fade" id="createSettingsModal" tabindex="-1" role="dialog" aria-labelledby="createSettingsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content modern-modal">
                <div class="modal-header modern-modal-header">
                    <h5 class="modal-title" id="createSettingsModalLabel">
                        <i class="fas fa-plus-circle"></i> Add New Calculation Settings
                    </h5>
                    <button type="button" class="close modern-close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="{{ route('admin.tiles-settings.store') }}" method="POST" id="createSettingsForm">
                    @csrf
                    <div class="modal-body modern-modal-body">
                        <div class="form-group modern-form-group">
                            <label for="tiles_category_id" class="modern-label">
                                Tiles Category <span class="required">*</span>
                            </label>
                            <select name="tiles_category_id" id="tiles_category_id" class="form-control modern-select select2" required>
                                <option value="">Select Category</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }} ({{ $category->height }}×{{ $category->width }})</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group modern-form-group">
                                    <label for="light_times" class="modern-label">
                                        Light Times <span class="required">*</span>
                                    </label>
                                    <div class="input-group modern-input-group">
                                        <input type="number" step="0.1" name="light_times" id="light_times" 
                                               class="form-control modern-input" value="4" min="0" required>
                                        <div class="input-group-append">
                                            <span class="input-group-text modern-input-addon">×</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group modern-form-group">
                                    <label for="deco_times" class="modern-label">
                                        Deco Times <span class="required">*</span>
                                    </label>
                                    <div class="input-group modern-input-group">
                                        <input type="number" step="0.1" name="deco_times" id="deco_times" 
                                               class="form-control modern-input" value="1" min="0" required>
                                        <div class="input-group-append">
                                            <span class="input-group-text modern-input-addon">×</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group modern-form-group">
                                    <label for="deep_times" class="modern-label">
                                        Deep Times <span class="required">*</span>
                                    </label>
                                    <div class="input-group modern-input-group">
                                        <input type="number" step="0.1" name="deep_times" id="deep_times" 
                                               class="form-control modern-input" value="1" min="0" required>
                                        <div class="input-group-append">
                                            <span class="input-group-text modern-input-addon">×</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Preview Calculation -->
                        <div class="calculation-preview" id="createPreview">
                            <div class="preview-title">
                                <i class="fas fa-eye"></i> Preview (for 100 sq ft)
                            </div>
                            <div class="preview-content">
                                <div class="preview-item">
                                    <span class="preview-label">Light:</span>
                                    <span class="preview-value" id="createPreviewLight">400</span>
                                </div>
                                <div class="preview-item">
                                    <span class="preview-label">Deco:</span>
                                    <span class="preview-value" id="createPreviewDeco">100</span>
                                </div>
                                <div class="preview-item">
                                    <span class="preview-label">Deep:</span>
                                    <span class="preview-value" id="createPreviewDeep">100</span>
                                </div>
                                <div class="preview-item total">
                                    <span class="preview-label">Total:</span>
                                    <span class="preview-value" id="createPreviewTotal">600</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer modern-modal-footer">
                        <button type="button" class="btn modern-btn modern-btn-secondary" data-dismiss="modal">
                            <i class="fas fa-times"></i> Cancel
                        </button>
                        <button type="submit" class="btn modern-btn modern-btn-primary" id="createSubmitBtn">
                            <i class="fas fa-save"></i> Save Settings
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Settings Modal -->
    <div class="modal fade" id="editSettingsModal" tabindex="-1" role="dialog" aria-labelledby="editSettingsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content modern-modal">
                <div class="modal-header modern-modal-header">
                    <h5 class="modal-title" id="editSettingsModalLabel">
                        <i class="fas fa-edit"></i> Edit Calculation Settings
                    </h5>
                    <button type="button" class="close modern-close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="editSettingsForm" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-body modern-modal-body">
                        <div class="form-group modern-form-group">
                            <label for="edit_tiles_category_id" class="modern-label">
                                Tiles Category <span class="required">*</span>
                            </label>
                            <select name="tiles_category_id" id="edit_tiles_category_id" class="form-control modern-select select2" required>
                                <option value="">Select Category</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }} ({{ $category->height }}×{{ $category->width }})</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group modern-form-group">
                                    <label for="edit_light_times" class="modern-label">
                                        Light Times <span class="required">*</span>
                                    </label>
                                    <div class="input-group modern-input-group">
                                        <input type="number" step="0.1" name="light_times" id="edit_light_times" 
                                               class="form-control modern-input" min="0" required>
                                        <div class="input-group-append">
                                            <span class="input-group-text modern-input-addon">×</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group modern-form-group">
                                    <label for="edit_deco_times" class="modern-label">
                                        Deco Times <span class="required">*</span>
                                    </label>
                                    <div class="input-group modern-input-group">
                                        <input type="number" step="0.1" name="deco_times" id="edit_deco_times" 
                                               class="form-control modern-input" min="0" required>
                                        <div class="input-group-append">
                                            <span class="input-group-text modern-input-addon">×</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group modern-form-group">
                                    <label for="edit_deep_times" class="modern-label">
                                        Deep Times <span class="required">*</span>
                                    </label>
                                    <div class="input-group modern-input-group">
                                        <input type="number" step="0.1" name="deep_times" id="edit_deep_times" 
                                               class="form-control modern-input" min="0" required>
                                        <div class="input-group-append">
                                            <span class="input-group-text modern-input-addon">×</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Preview Calculation -->
                        <div class="calculation-preview" id="editPreview">
                            <div class="preview-title">
                                <i class="fas fa-eye"></i> Preview (for 100 sq ft)
                            </div>
                            <div class="preview-content">
                                <div class="preview-item">
                                    <span class="preview-label">Light:</span>
                                    <span class="preview-value" id="editPreviewLight">0</span>
                                </div>
                                <div class="preview-item">
                                    <span class="preview-label">Deco:</span>
                                    <span class="preview-value" id="editPreviewDeco">0</span>
                                </div>
                                <div class="preview-item">
                                    <span class="preview-label">Deep:</span>
                                    <span class="preview-value" id="editPreviewDeep">0</span>
                                </div>
                                <div class="preview-item total">
                                    <span class="preview-label">Total:</span>
                                    <span class="preview-value" id="editPreviewTotal">0</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer modern-modal-footer">
                        <button type="button" class="btn modern-btn modern-btn-secondary" data-dismiss="modal">
                            <i class="fas fa-times"></i> Cancel
                        </button>
                        <button type="submit" class="btn modern-btn modern-btn-primary" id="editSubmitBtn">
                            <i class="fas fa-save"></i> Update Settings
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteSettingsModal" tabindex="-1" role="dialog" aria-labelledby="deleteSettingsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content modern-modal">
                <div class="modal-header modern-modal-header">
                    <h5 class="modal-title" id="deleteSettingsModalLabel">
                        <i class="fas fa-exclamation-triangle"></i> Confirm Deletion
                    </h5>
                    <button type="button" class="close modern-close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body modern-modal-body">
                    <div class="confirmation-content">
                        <div class="confirmation-icon">
                            <i class="fas fa-question-circle"></i>
                        </div>
                        <div class="confirmation-text">
                            <p>Are you sure you want to delete these calculation settings?</p>
                            <small class="text-muted">This action cannot be undone and may affect tile calculations.</small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer modern-modal-footer">
                    <button type="button" class="btn modern-btn modern-btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                    <button type="button" id="confirmDeleteBtn" class="btn modern-btn modern-btn-danger">
                        <i class="fas fa-trash"></i> Delete Settings
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Hidden delete form -->
    <form id="delete-form" method="POST" style="display: none;">
        @csrf
        @method('DELETE')
    </form>
@stop

@section('additional_css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.min.css">
    
    <style>
        /* Modern Form Enhancements */
        .modern-form-group {
            margin-bottom: 24px;
        }

        .modern-label {
            font-weight: 600;
            color: #374151;
            margin-bottom: 8px;
            font-size: 14px;
        }

        .modern-input, .modern-select {
            border: 2px solid #e5e7eb;
            border-radius: 10px;
            padding: 12px 16px;
            font-size: 14px;
            transition: all 0.2s ease;
            background: white;
            color: #374151;
        }

        .modern-input:focus, .modern-select:focus {
            outline: none;
            border-color: #6366f1;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
            transform: translateY(-1px);
        }

        /* Modern Input Group */
        .modern-input-group {
            display: flex;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .modern-input-group .modern-input {
            border-radius: 0;
            border-right: none;
            margin: 0;
        }

        .modern-input-addon {
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            border: 2px solid #e5e7eb;
            border-left: none;
            color: #6b7280;
            font-weight: 600;
            font-size: 14px;
            padding: 12px 16px;
            display: flex;
            align-items: center;
        }

        /* Section-specific header colors */
        .filter-header {
            background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
        }

        .settings-header {
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
        }

        /* Statistics Cards */
        .stat-card {
            border: none;
            border-radius: 15px;
            overflow: hidden;
            transition: all 0.3s ease;
            background: white;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }

        .stat-content {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            color: white;
        }

        .total-stat .stat-icon {
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
        }

        .light-stat .stat-icon {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        }

        .deco-stat .stat-icon {
            background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);
        }

        .deep-stat .stat-icon {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        }

        .stat-info {
            flex: 1;
        }

        .stat-number {
            font-size: 32px;
            font-weight: 700;
            color: #1f2937;
            margin: 0;
            line-height: 1;
        }

        .stat-label {
            font-size: 14px;
            color: #6b7280;
            margin: 4px 0 0 0;
            font-weight: 500;
        }

        /* Modern Table Enhancements */
        .table-container {
            position: relative;
            overflow: hidden;
            border-radius: 12px;
            background: white;
        }

        .modern-table-responsive {
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            background: white;
        }

        .modern-table {
            margin-bottom: 0;
            background: white !important;
            color: #1f2937 !important;
            width: 100%;
        }

        .modern-thead {
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%) !important;
            border-bottom: none;
        }

        .modern-thead th {
            border: none !important;
            padding: 16px 12px;
            font-weight: 600;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: white !important;
            background: transparent !important;
        }

        .th-content {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            color: white;
            text-align: center;
        }

        .modern-tbody {
            background: white !important;
        }

        .modern-tbody tr {
            transition: all 0.2s ease;
            border-bottom: 1px solid #f1f5f9;
            background: white !important;
        }

        .modern-tbody tr:hover {
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%) !important;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.1);
        }

        .modern-tbody td {
            padding: 12px;
            vertical-align: middle;
            border: none !important;
            font-size: 13px;
            color: #374151 !important;
            background: transparent !important;
        }

        /* Custom Badges */
        .id-badge {
            display: inline-block;
            background: linear-gradient(135deg, #6b7280 0%, #4b5563 100%);
            color: white;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
        }

        .times-badge {
            display: inline-block;
            color: white;
            padding: 6px 12px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: 700;
        }

        .light-badge {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        }

        .deco-badge {
            background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);
        }

        .deep-badge {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        }

        .category-name {
            color: #374151;
            font-size: 14px;
        }

        .total-display {
            text-align: center;
        }

        .total-value {
            font-weight: 700;
            color: #059669;
            font-size: 16px;
        }

        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 6px;
            justify-content: center;
        }

        .action-btn {
            padding: 6px 12px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 500;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            transition: all 0.2s ease;
            border: none;
            cursor: pointer;
        }

        .action-btn:hover {
            transform: translateY(-1px);
            text-decoration: none;
        }

        .action-btn-edit {
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
            color: white;
        }

        .action-btn-delete {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: white;
        }

        /* Calculation Preview */
        .calculation-preview {
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            padding: 20px;
            margin-top: 20px;
        }

        .preview-title {
            font-weight: 600;
            color: #374151;
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .preview-content {
            display: flex;
            gap: 24px;
            justify-content: space-around;
        }

        .preview-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 4px;
        }

        .preview-item.total {
            border-left: 2px solid #e5e7eb;
            padding-left: 24px;
        }

        .preview-label {
            font-size: 12px;
            color: #6b7280;
            font-weight: 500;
        }

        .preview-value {
            font-size: 18px;
            font-weight: 700;
            color: #374151;
        }

        .preview-item.total .preview-value {
            color: #059669;
        }

        /* Modern Modal */
        .modern-modal {
            border: none;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        }

        .modern-modal-header {
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
            color: white;
            border-bottom: none;
            padding: 20px 24px;
        }

        .modern-modal-header .modal-title {
            font-weight: 600;
            font-size: 18px;
        }

        .modern-close {
            color: white;
            opacity: 0.8;
            font-size: 24px;
        }

        .modern-close:hover {
            color: white;
            opacity: 1;
        }

        .modern-modal-body {
            padding: 24px;
        }

        .confirmation-content {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .confirmation-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            color: white;
            flex-shrink: 0;
        }

        .confirmation-text {
            flex: 1;
        }

        .confirmation-text p {
            margin: 0 0 8px 0;
            font-size: 16px;
            color: #374151;
        }

        .modern-modal-footer {
            padding: 20px 24px;
            border-top: 1px solid #f1f5f9;
            background: #f8fafc;
        }

        /* Select2 Enhancements */
        .select2-container .select2-selection--single {
            height: 46px;
            border: 2px solid #e5e7eb;
            border-radius: 10px;
            background: white;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 42px;
            color: #374151;
            padding-left: 16px;
            font-size: 14px;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 42px;
            right: 16px;
        }

        .select2-container--default.select2-container--focus .select2-selection--single {
            border-color: #6366f1;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
        }

        .select2-dropdown {
            border: none;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
            border-radius: 10px;
            overflow: hidden;
        }

        .select2-results__option {
            padding: 12px 16px;
            font-size: 14px;
            transition: all 0.2s ease;
        }

        .select2-results__option--highlighted {
            background: linear-gradient(135deg, rgba(99, 102, 241, 0.1) 0%, rgba(139, 92, 246, 0.05) 100%);
            color: #6366f1;
        }

        /* Required field indicator */
        .required {
            color: #ef4444;
            font-weight: 600;
        }

        /* Card Tools */
        .card-tools .btn-tool {
            color: white;
            opacity: 0.8;
            border: none;
            background: transparent;
            padding: 4px 8px;
            border-radius: 4px;
            transition: all 0.2s ease;
        }

        .card-tools .btn-tool:hover {
            opacity: 1;
            background: rgba(255, 255, 255, 0.1);
        }

        /* DataTables Enhancements */
        .dataTables_wrapper .dataTables_length,
        .dataTables_wrapper .dataTables_filter,
        .dataTables_wrapper .dataTables_info,
        .dataTables_wrapper .dataTables_paginate {
            color: #374151;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button {
            border-radius: 8px;
            margin: 0 2px;
            padding: 8px 12px;
            border: 1px solid #e5e7eb;
            background: white;
            color: #374151;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
            background: #6366f1;
            color: white;
            border-color: #6366f1;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button.current {
            background: #6366f1;
            color: white;
            border-color: #6366f1;
        }

        /* Toastr Customization */
        .toast-success {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        }

        .toast-error {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        }

        .toast-warning {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        }

        .toast-info {
            background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .stat-content {
                flex-direction: column;
                text-align: center;
                gap: 12px;
            }

            .stat-icon {
                width: 50px;
                height: 50px;
                font-size: 20px;
            }

            .stat-number {
                font-size: 24px;
            }

            .modern-tbody td {
                padding: 8px 6px;
                font-size: 12px;
            }

            .modern-thead th {
                padding: 12px 8px;
                font-size: 11px;
            }

            .action-buttons {
                flex-direction: column;
                gap: 4px;
            }

            .action-btn {
                padding: 4px 8px;
                font-size: 11px;
            }

            .confirmation-content {
                flex-direction: column;
                text-align: center;
            }

            .preview-content {
                flex-direction: column;
                gap: 12px;
            }

            .preview-item {
                flex-direction: row;
                justify-content: space-between;
            }

            .preview-item.total {
                border-left: none;
                border-top: 2px solid #e5e7eb;
                padding-left: 0;
                padding-top: 12px;
            }
        }
    </style>
@stop

@section('additional_js')
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap4.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"></script>
    
    <script>
        $(document).ready(function() {
            // Configure Toastr
            toastr.options = {
                "closeButton": true,
                "debug": false,
                "newestOnTop": true,
                "progressBar": true,
                "positionClass": "toast-top-right",
                "preventDuplicates": false,
                "onclick": null,
                "showDuration": "300",
                "hideDuration": "1000",
                "timeOut": "5000",
                "extendedTimeOut": "1000",
                "showEasing": "swing",
                "hideEasing": "linear",
                "showMethod": "fadeIn",
                "hideMethod": "fadeOut"
            };
            
            // Initialize Select2 with modern styling
            $('.select2').select2({
                width: '100%',
                placeholder: 'Select an option',
                allowClear: true
            });
            
            // Auto-hide alerts after 5 seconds
            setTimeout(function() {
                $('.modern-alert').fadeOut();
            }, 5000);
            
            // Initialize DataTable with modern styling
            let settingsTable = $('#settings-table').DataTable({
                "paging": true,
                "lengthChange": true,
                "searching": true,
                "ordering": true,
                "info": true,
                "autoWidth": false,
                "responsive": true,
                "pageLength": 25,
                "language": {
                    "processing": '<i class="fas fa-spinner fa-spin"></i> Loading...',
                    "emptyTable": 'No calculation settings found',
                    "zeroRecords": 'No matching records found'
                },
                "order": [[0, 'desc']]
            });
            
            // Apply filters
            $('#filter_button').click(function() {
                const button = $(this);
                button.html('<i class="fas fa-spinner fa-spin"></i> Filtering...').prop('disabled', true);
                
                // Get filter values
                const category = $('#category_filter').val();
                const lightRange = $('#light_range').val();
                
                // Apply filters to DataTable
                if (category) {
                    settingsTable.column(1).search(category);
                } else {
                    settingsTable.column(1).search('');
                }
                
                settingsTable.draw();
                
                setTimeout(() => {
                    button.html('<i class="fas fa-search"></i> Apply Filter').prop('disabled', false);
                    toastr.info('Filters applied successfully');
                }, 1000);
            });
            
            // Clear filters on change
            $('#category_filter, #light_range').on('change', function() {
                if (!$(this).val()) {
                    $('#filter_button').click();
                }
            });
            
            // Refresh table function
            window.refreshTable = function() {
                settingsTable.ajax.reload();
                toastr.info('Table refreshed');
            };
            
            // Update preview calculations
            function updateCreatePreview() {
                const light = parseFloat($('#light_times').val()) || 0;
                const deco = parseFloat($('#deco_times').val()) || 0;
                const deep = parseFloat($('#deep_times').val()) || 0;
                const sampleArea = 100;
                
                $('#createPreviewLight').text((light * sampleArea).toFixed(1));
                $('#createPreviewDeco').text((deco * sampleArea).toFixed(1));
                $('#createPreviewDeep').text((deep * sampleArea).toFixed(1));
                $('#createPreviewTotal').text(((light + deco + deep) * sampleArea).toFixed(1));
            }
            
            function updateEditPreview() {
                const light = parseFloat($('#edit_light_times').val()) || 0;
                const deco = parseFloat($('#edit_deco_times').val()) || 0;
                const deep = parseFloat($('#edit_deep_times').val()) || 0;
                const sampleArea = 100;
                
                $('#editPreviewLight').text((light * sampleArea).toFixed(1));
                $('#editPreviewDeco').text((deco * sampleArea).toFixed(1));
                $('#editPreviewDeep').text((deep * sampleArea).toFixed(1));
                $('#editPreviewTotal').text(((light + deco + deep) * sampleArea).toFixed(1));
            }
            
            // Update previews on input change
            $('#light_times, #deco_times, #deep_times').on('input', updateCreatePreview);
            $('#edit_light_times, #edit_deco_times, #edit_deep_times').on('input', updateEditPreview);
            
            // Initialize create preview
            updateCreatePreview();
            
            // Handle edit button click
            $('.edit-btn').click(function() {
                const id = $(this).data('id');
                const categoryId = $(this).data('category');
                const lightTimes = $(this).data('light');
                const decoTimes = $(this).data('deco');
                const deepTimes = $(this).data('deep');
                
                $('#edit_tiles_category_id').val(categoryId).trigger('change');
                $('#edit_light_times').val(lightTimes);
                $('#edit_deco_times').val(decoTimes);
                $('#edit_deep_times').val(deepTimes);
                
                updateEditPreview();
                
                $('#editSettingsForm').attr('action', `/admin/tiles-settings/${id}`);
                $('#editSettingsModal').modal('show');
            });
            
            // Delete settings function
            window.deleteSettings = function(settingsId) {
                $('#deleteSettingsModal').data('settings-id', settingsId);
                $('#deleteSettingsModal').modal('show');
            };
            
            // Handle delete confirmation
            $('#confirmDeleteBtn').click(function() {
                const settingsId = $('#deleteSettingsModal').data('settings-id');
                const button = $(this);
                
                // Show loading state
                button.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Deleting...');
                
                // Set form action and submit
                const form = $('#delete-form');
                form.attr('action', '/admin/tiles-settings/' + settingsId);
                form.submit();
            });
            
            // Form submissions with loading states
            $('#createSettingsForm').submit(function() {
                const submitBtn = $('#createSubmitBtn');
                submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');
            });
            
            $('#editSettingsForm').submit(function() {
                const submitBtn = $('#editSubmitBtn');
                submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Updating...');
            });
            
            // Reset modal when hidden
            $('#deleteSettingsModal').on('hidden.bs.modal', function() {
                $('#confirmDeleteBtn').prop('disabled', false).html('<i class="fas fa-trash"></i> Delete Settings');
            });
            
            $('#createSettingsModal').on('hidden.bs.modal', function() {
                $('#createSubmitBtn').prop('disabled', false).html('<i class="fas fa-save"></i> Save Settings');
            });
            
            $('#editSettingsModal').on('hidden.bs.modal', function() {
                $('#editSubmitBtn').prop('disabled', false).html('<i class="fas fa-save"></i> Update Settings');
            });
        });
    </script>
@stop
