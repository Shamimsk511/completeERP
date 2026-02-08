@extends('layouts.modern-admin')

@section('title', 'Edit Tiles Category')

@section('page_title', 'Edit Tiles Category')

@section('header_actions')
    <a href="{{ route('admin.tiles-categories.index') }}" class="btn modern-btn modern-btn-secondary">
        <i class="fas fa-arrow-left"></i> Back to Categories
    </a>
@stop

@section('page_content')
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

    @if($errors->any())
        <div class="alert modern-alert modern-alert-error" id="error-alert">
            <div class="alert-content">
                <i class="fas fa-exclamation-triangle alert-icon"></i>
                <div class="alert-message">
                    <strong>Error!</strong>
                    <span>Please check the form for errors</span>
                </div>
                <button type="button" class="alert-close" onclick="this.parentElement.parentElement.style.display='none'">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
    @endif

    <!-- Category Information -->
    <div class="card modern-card mb-4">
        <div class="card-header modern-header category-header">
            <h3 class="card-title">
                <i class="fas fa-th-large"></i> Category Information
            </h3>
            <div class="card-tools">
                <div class="category-id-badge">
                    <i class="fas fa-hashtag"></i>
                    <span>ID: {{ $category->id }}</span>
                </div>
            </div>
        </div>
        <div class="card-body modern-card-body">
            <form action="{{ route('admin.tiles-categories.update', $category) }}" method="POST" id="categoryForm">
                @csrf
                @method('PUT')
                
                <div class="form-group modern-form-group">
                    <label for="name" class="modern-label">
                        Category Name <span class="required">*</span>
                    </label>
                    <input type="text" name="name" id="name" 
                           class="form-control modern-input @error('name') is-invalid @enderror" 
                           value="{{ old('name', $category->name) }}" 
                           placeholder="e.g., 12x24, Subway Tile, etc." required>
                    <div class="form-help">
                        <i class="fas fa-info-circle"></i>
                        <span>Use format like "12x24" to automatically update dimensions</span>
                    </div>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <!-- Dimensions Row -->
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group modern-form-group">
                            <label for="height" class="modern-label">
                                Height <span class="required">*</span>
                            </label>
                            <div class="input-group modern-input-group">
                                <input type="number" name="height" id="height" step="0.01" min="0"
                                       class="form-control modern-input @error('height') is-invalid @enderror" 
                                       value="{{ old('height', $category->height) }}" required>
                                <div class="input-group-append">
                                    <span class="input-group-text modern-input-addon">inches</span>
                                </div>
                            </div>
                            @error('height')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group modern-form-group">
                            <label for="width" class="modern-label">
                                Width <span class="required">*</span>
                            </label>
                            <div class="input-group modern-input-group">
                                <input type="number" name="width" id="width" step="0.01" min="0"
                                       class="form-control modern-input @error('width') is-invalid @enderror" 
                                       value="{{ old('width', $category->width) }}" required>
                                <div class="input-group-append">
                                    <span class="input-group-text modern-input-addon">inches</span>
                                </div>
                            </div>
                            @error('width')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
                
                <!-- Calculated Area Display -->
                <div class="area-display" id="areaDisplay">
                    <div class="area-card">
                        <div class="area-icon">
                            <i class="fas fa-calculator"></i>
                        </div>
                        <div class="area-content">
                            <h4 id="areaValue">{{ $category->height * $category->width }}</h4>
                            <p>Square Inches</p>
                        </div>
                    </div>
                </div>
                
                <!-- Submit Buttons -->
                <div class="form-actions">
                    <button type="submit" class="btn modern-btn modern-btn-primary" id="submit-btn">
                        <i class="fas fa-save"></i> Update Category
                    </button>
                    <a href="{{ route('admin.tiles-categories.index') }}" class="btn modern-btn modern-btn-secondary ml-3">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Calculation Settings -->
    <div class="card modern-card mb-4">
        <div class="card-header modern-header settings-header">
            <h3 class="card-title">
                <i class="fas fa-cogs"></i> Calculation Settings
            </h3>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" onclick="resetSettings()">
                    <i class="fas fa-undo"></i> Reset to Defaults
                </button>
            </div>
        </div>
        <div class="card-body modern-card-body">
            <form action="{{ route('admin.tiles-categories.settings', $category) }}" method="POST" id="settingsForm">
                @csrf
                
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group modern-form-group">
                            <label for="light_times" class="modern-label">
                                Light Times
                            </label>
                            <div class="input-group modern-input-group">
                                <input type="number" name="light_times" id="light_times" step="0.1" min="0"
                                       class="form-control modern-input" 
                                       value="{{ old('light_times', $settings->light_times ?? 4) }}">
                                <div class="input-group-append">
                                    <span class="input-group-text modern-input-addon">×</span>
                                </div>
                            </div>
                            <small class="form-text text-muted">Multiplier for light tiles calculation</small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group modern-form-group">
                            <label for="deco_times" class="modern-label">
                                Deco Times
                            </label>
                            <div class="input-group modern-input-group">
                                <input type="number" name="deco_times" id="deco_times" step="0.1" min="0"
                                       class="form-control modern-input" 
                                       value="{{ old('deco_times', $settings->deco_times ?? 1) }}">
                                <div class="input-group-append">
                                    <span class="input-group-text modern-input-addon">×</span>
                                </div>
                            </div>
                            <small class="form-text text-muted">Multiplier for decorative tiles calculation</small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group modern-form-group">
                            <label for="deep_times" class="modern-label">
                                Deep Times
                            </label>
                            <div class="input-group modern-input-group">
                                <input type="number" name="deep_times" id="deep_times" step="0.1" min="0"
                                       class="form-control modern-input" 
                                       value="{{ old('deep_times', $settings->deep_times ?? 1) }}">
                                <div class="input-group-append">
                                    <span class="input-group-text modern-input-addon">×</span>
                                </div>
                            </div>
                            <small class="form-text text-muted">Multiplier for deep tiles calculation</small>
                        </div>
                    </div>
                </div>
                
                <!-- Settings Preview -->
                <div class="settings-preview" id="settingsPreview">
                    <div class="preview-title">
                        <i class="fas fa-eye"></i> Preview Calculation
                    </div>
                    <div class="preview-content">
                        <div class="preview-item">
                            <span class="preview-label">Light:</span>
                            <span class="preview-value" id="previewLight">-</span>
                        </div>
                        <div class="preview-item">
                            <span class="preview-label">Deco:</span>
                            <span class="preview-value" id="previewDeco">-</span>
                        </div>
                        <div class="preview-item">
                            <span class="preview-label">Deep:</span>
                            <span class="preview-value" id="previewDeep">-</span>
                        </div>
                    </div>
                </div>
                
                <!-- Settings Submit Button -->
                <div class="form-actions">
                    <button type="submit" class="btn modern-btn modern-btn-success" id="settings-submit-btn">
                        <i class="fas fa-save"></i> Save Settings
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Tile Preview Section -->
    <div class="card modern-card mb-4">
        <div class="card-header modern-header preview-header">
            <h3 class="card-title">
                <i class="fas fa-eye"></i> Tile Preview
            </h3>
        </div>
        <div class="card-body modern-card-body">
            <div class="preview-container">
                <div class="tile-preview">
                    <div class="tile-visual" id="tileVisual">
                        <span class="tile-dimensions" id="tileDimensions">{{ $category->height }}" × {{ $category->width }}"</span>
                    </div>
                    <div class="tile-info">
                        <h5 id="previewName">{{ $category->name }}</h5>
                        <p id="previewArea">Area: {{ $category->height * $category->width }} sq in</p>
                        <small class="text-muted">Created: {{ $category->created_at->format('d M, Y') }}</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('additional_css')
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

        .modern-input {
            border: 2px solid #e5e7eb;
            border-radius: 10px;
            padding: 12px 16px;
            font-size: 14px;
            transition: all 0.2s ease;
            background: white;
            color: #374151;
        }

        .modern-input:focus {
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
        .category-header {
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
        }

        .settings-header {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        }

        .preview-header {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        }

        /* Category ID Badge */
        .category-id-badge {
            display: flex;
            align-items: center;
            gap: 6px;
            color: white;
            font-size: 12px;
            background: rgba(255, 255, 255, 0.2);
            padding: 4px 8px;
            border-radius: 12px;
            font-weight: 600;
        }

        /* Form Help Text */
        .form-help {
            display: flex;
            align-items: center;
            gap: 6px;
            margin-top: 8px;
            font-size: 12px;
            color: #6b7280;
        }

        .form-help i {
            color: #6366f1;
        }

        /* Area Display */
        .area-display {
            margin: 20px 0;
            display: flex;
            justify-content: center;
        }

        .area-card {
            background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
            border: 2px solid #10b981;
            border-radius: 15px;
            padding: 20px;
            display: flex;
            align-items: center;
            gap: 16px;
            min-width: 200px;
        }

        .area-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            color: white;
        }

        .area-content h4 {
            margin: 0;
            font-size: 24px;
            font-weight: 700;
            color: #059669;
            line-height: 1;
        }

        .area-content p {
            margin: 4px 0 0 0;
            font-size: 14px;
            color: #047857;
            font-weight: 500;
        }

        /* Settings Preview */
        .settings-preview {
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            padding: 20px;
            margin: 20px 0;
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
            justify-content: center;
        }

        .preview-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 4px;
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

        /* Tile Preview */
        .preview-container {
            display: flex;
            justify-content: center;
            padding: 20px;
        }

        .tile-preview {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 20px;
        }

        .tile-visual {
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            border: 3px solid #6366f1;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            min-width: 120px;
            min-height: 80px;
            position: relative;
            transition: all 0.3s ease;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }

        .tile-dimensions {
            font-weight: 600;
            color: #6366f1;
            font-size: 14px;
        }

        .tile-info {
            text-align: center;
        }

        .tile-info h5 {
            margin: 0;
            font-size: 18px;
            font-weight: 600;
            color: #374151;
        }

        .tile-info p {
            margin: 4px 0 0 0;
            font-size: 14px;
            color: #6b7280;
        }

        /* Form Actions */
        .form-actions {
            margin-top: 24px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            display: flex;
            align-items: center;
        }

        /* Form validation styles */
        .is-invalid {
            border-color: #ef4444 !important;
            box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1) !important;
        }

        .invalid-feedback {
            color: #ef4444;
            font-size: 12px;
            margin-top: 4px;
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
            font-size: 12px;
        }

        .card-tools .btn-tool:hover {
            opacity: 1;
            background: rgba(255, 255, 255, 0.1);
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
            .modern-form-group {
                margin-bottom: 20px;
            }

            .modern-input {
                padding: 10px 14px;
                font-size: 16px;
            }

            .area-card {
                padding: 16px;
                min-width: 180px;
            }

            .area-icon {
                width: 40px;
                height: 40px;
                font-size: 16px;
            }

            .area-content h4 {
                font-size: 20px;
            }

            .tile-visual {
                min-width: 100px;
                min-height: 60px;
            }

            .tile-dimensions {
                font-size: 12px;
            }

            .preview-content {
                flex-direction: column;
                gap: 12px;
            }

            .preview-item {
                flex-direction: row;
                justify-content: space-between;
            }
        }
    </style>
@stop

@section('additional_js')
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
            
            // Auto-hide alerts after 5 seconds
            setTimeout(function() {
                $('.modern-alert').fadeOut();
            }, 5000);
            
            // Auto-extract dimensions from name
            $('#name').on('blur input', function() {
                const name = this.value.trim();
                const match = name.match(/(\d+(?:\.\d+)?)x(\d+(?:\.\d+)?)/i);
                
                if (match && match.length === 3) {
                    const height = parseFloat(match[1]);
                    const width = parseFloat(match[2]);
                    
                    $('#height').val(height);
                    $('#width').val(width);
                    
                    // Update calculations
                    updateCalculations();
                    updateTileVisual(height, width);
                    updatePreview();
                    
                    // Show success message
                    toastr.success('Dimensions updated from name');
                }
            });
            
            // Update calculations when dimensions change
            $('#height, #width').on('input', function() {
                updateCalculations();
                updateTileVisual();
                updatePreview();
            });
            
            // Update preview when settings change
            $('#light_times, #deco_times, #deep_times').on('input', function() {
                updateSettingsPreview();
            });
            
            // Update preview when name changes
            $('#name').on('input', function() {
                updatePreview();
            });
            
            function updateCalculations() {
                const height = parseFloat($('#height').val()) || 0;
                const width = parseFloat($('#width').val()) || 0;
                
                if (height > 0 && width > 0) {
                    const area = height * width;
                    $('#areaValue').text(area.toFixed(2));
                }
            }
            
            function updateTileVisual(height = null, width = null) {
                if (height === null) height = parseFloat($('#height').val()) || 0;
                if (width === null) width = parseFloat($('#width').val()) || 0;
                
                if (height > 0 && width > 0) {
                    const maxSize = 150;
                    const ratio = Math.min(maxSize / Math.max(height, width), 1);
                    
                    const visualHeight = height * ratio;
                    const visualWidth = width * ratio;
                    
                    $('#tileVisual').css({
                        'width': Math.max(visualWidth, 60) + 'px',
                        'height': Math.max(visualHeight, 40) + 'px'
                    });
                    
                    $('#tileDimensions').text(`${height}" × ${width}"`);
                }
            }
            
            function updatePreview() {
                const name = $('#name').val() || 'Category Name';
                const height = parseFloat($('#height').val()) || 0;
                const width = parseFloat($('#width').val()) || 0;
                const area = height * width;
                
                $('#previewName').text(name);
                $('#previewArea').text(`Area: ${area.toFixed(2)} sq in`);
            }
            
            function updateSettingsPreview() {
                const lightTimes = parseFloat($('#light_times').val()) || 0;
                const decoTimes = parseFloat($('#deco_times').val()) || 0;
                const deepTimes = parseFloat($('#deep_times').val()) || 0;
                
                // Sample calculation for 100 sq ft
                const sampleArea = 100;
                const lightValue = (sampleArea * lightTimes).toFixed(1);
                const decoValue = (sampleArea * decoTimes).toFixed(1);
                const deepValue = (sampleArea * deepTimes).toFixed(1);
                
                $('#previewLight').text(lightValue);
                $('#previewDeco').text(decoValue);
                $('#previewDeep').text(deepValue);
            }
            
            // Reset settings function
            window.resetSettings = function() {
                if (confirm('Reset all calculation settings to default values?')) {
                    $('#light_times').val(4);
                    $('#deco_times').val(1);
                    $('#deep_times').val(1);
                    updateSettingsPreview();
                    toastr.info('Settings reset to defaults');
                }
            };
            
            // Form submissions
            $('#categoryForm').submit(function(e) {
                const height = parseFloat($('#height').val());
                const width = parseFloat($('#width').val());
                
                if (height <= 0 || width <= 0) {
                    e.preventDefault();
                    toastr.error('Please enter valid dimensions');
                    return false;
                }
                
                // Show loading state
                const submitBtn = $('#submit-btn');
                const originalText = submitBtn.html();
                submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Updating Category...');
                
                return true;
            });
            
            $('#settingsForm').submit(function(e) {
                // Show loading state
                const submitBtn = $('#settings-submit-btn');
                const originalText = submitBtn.html();
                submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving Settings...');
                
                return true;
            });
            
            // Real-time validation
            $('input').on('blur', function() {
                $(this).removeClass('is-invalid');
            });
            
            // Initialize calculations and preview
            updateCalculations();
            updateTileVisual();
            updatePreview();
            updateSettingsPreview();
        });
    </script>
@stop
