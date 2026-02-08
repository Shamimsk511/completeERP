@extends('layouts.modern-admin')

@section('title', 'Decor Calculator')

@section('page_title', 'Tiles Decor Calculator')

@section('header_actions')
    <button type="button" class="btn modern-btn modern-btn-info" data-toggle="modal" data-target="#settingsModal">
        <i class="fas fa-cog"></i> Settings
    </button>
@stop

@section('page_content')
    <div class="row">
        <!-- Calculator Section -->
        <div class="col-lg-6">
            <div class="card modern-card">
                <div class="card-header modern-header calculator-header">
                    <h3 class="card-title">
                        <i class="fas fa-calculator"></i> Calculator
                    </h3>
                </div>
                <div class="card-body modern-card-body">
                    <form id="calculatorForm">
                        <!-- Category Selection -->
                        <div class="form-group modern-form-group">
                            <label for="tilesCategory" class="modern-label">
                                Tiles Category <span class="required">*</span>
                            </label>
                            <select class="form-control select2 modern-select" id="tilesCategory" required>
                                <option value="">Select category</option>
                                @foreach($categories as $category)
                                    @php
                                        $settings = $category->calculationSettings;
                                        $lightTimes = $settings?->light_times ?? 4;
                                        $decoTimes = $settings?->deco_times ?? 1;
                                        $deepTimes = $settings?->deep_times ?? 1;
                                    @endphp
                                    <option value="{{ $category->id }}" 
                                            data-height="{{ $category->height }}" 
                                            data-width="{{ $category->width }}"
                                            data-light="{{ $lightTimes }}"
                                            data-deco="{{ $decoTimes }}"
                                            data-deep="{{ $deepTimes }}">
                                        {{ $category->name }} ({{ $category->height }}x{{ $category->width }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        
                        <!-- Main Inputs -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group modern-form-group">
                                    <label for="quantity" class="modern-label">
                                        Total Area <span class="required">*</span>
                                    </label>
                                    <div class="input-group modern-input-group">
                                        <input type="number" class="form-control modern-input" id="quantity" value="100" required>
                                        <div class="input-group-append">
                                            <span class="input-group-text modern-input-addon">sq ft</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group modern-form-group">
                                    <label for="height" class="modern-label">
                                        Wall Height <span class="required">*</span>
                                    </label>
                                    <div class="input-group modern-input-group">
                                        <input type="number" class="form-control modern-input" id="height" value="7" required>
                                        <div class="input-group-append">
                                            <span class="input-group-text modern-input-addon">feet</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Times Settings Card -->
                        <div class="card modern-card mb-4">
                            <div class="card-header modern-header times-header">
                                <h3 class="card-title">
                                    <i class="fas fa-sliders-h"></i> Times Settings
                                </h3>
                                <div class="card-tools">
                                    <div class="modern-switch">
                                        <input type="checkbox" id="excludeDeep" class="modern-switch-input">
                                        <label for="excludeDeep" class="modern-switch-label">
                                            <span class="modern-switch-text">Exclude Deep</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body modern-card-body">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group modern-form-group">
                                            <label for="lightTimes" class="modern-label">Light Times</label>
                                            <input type="number" class="form-control modern-input" id="lightTimes" value="4" step="0.1">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group modern-form-group">
                                            <label for="decoTimes" class="modern-label">Deco Times</label>
                                            <input type="number" class="form-control modern-input" id="decoTimes" value="1" step="0.1">
                                        </div>
                                    </div>
                                    <div class="col-md-4" id="deepSection">
                                        <div class="form-group modern-form-group">
                                            <label for="deepTimes" class="modern-label">Deep Times</label>
                                            <input type="number" class="form-control modern-input" id="deepTimes" value="1" step="0.1">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Calculate Button -->
                        <div class="form-group modern-form-group">
                            <button type="button" class="btn modern-btn modern-btn-primary btn-lg btn-block" id="calculateBtn">
                                <i class="fas fa-calculator"></i> Calculate
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Results Section -->
        <div class="col-lg-6">
            <div class="card modern-card" id="resultCard" style="display: none;">
                <div class="card-header modern-header results-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-bar"></i> Results
                    </h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" onclick="printResults()">
                            <i class="fas fa-print"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body modern-card-body">
                    <!-- Result Cards -->
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="result-card light-card">
                                <div class="result-icon">
                                    <i class="fas fa-tint"></i>
                                </div>
                                <div class="result-content">
                                    <h4 id="lightResult">0</h4>
                                    <p>Light</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="result-card deco-card">
                                <div class="result-icon">
                                    <i class="fas fa-palette"></i>
                                </div>
                                <div class="result-content">
                                    <h4 id="decoResult">0</h4>
                                    <p>Deco</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4" id="deepResultContainer">
                            <div class="result-card deep-card">
                                <div class="result-icon">
                                    <i class="fas fa-layer-group"></i>
                                </div>
                                <div class="result-content">
                                    <h4 id="deepResult">0</h4>
                                    <p>Deep</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Total Result -->
                    <div class="total-result-card">
                        <div class="total-icon">
                            <i class="fas fa-calculator"></i>
                        </div>
                        <div class="total-content">
                            <h3>Total</h3>
                            <p id="totalResult">0 pcs (0 sq ft)</p>
                        </div>
                    </div>
                    
                    <!-- Calculation Details -->
                    <div class="card modern-card mt-4">
                        <div class="card-header modern-header details-header">
                            <h3 class="card-title">
                                <i class="fas fa-info-circle"></i> Calculation Details
                            </h3>
                        </div>
                        <div class="card-body modern-card-body">
                            <div id="calculationDetails">
                                <p class="text-muted">No calculation performed yet.</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Distribution Chart -->
                    <div class="card modern-card mt-4">
                        <div class="card-header modern-header chart-header">
                            <h3 class="card-title">
                                <i class="fas fa-chart-pie"></i> Distribution
                            </h3>
                        </div>
                        <div class="card-body modern-card-body">
                            <div class="chart-container">
                                <canvas id="distributionChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Settings Modal -->
    <div class="modal fade" id="settingsModal" tabindex="-1" role="dialog" aria-labelledby="settingsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content modern-modal">
                <div class="modal-header modern-modal-header">
                    <h5 class="modal-title" id="settingsModalLabel">
                        <i class="fas fa-cog"></i> Calculator Settings
                    </h5>
                    <button type="button" class="close modern-close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body modern-modal-body">
                    <p class="text-muted">Configure default settings for different tile categories.</p>
                    <!-- Settings form would go here -->
                </div>
                <div class="modal-footer modern-modal-footer">
                    <button type="button" class="btn modern-btn modern-btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times"></i> Close
                    </button>
                    <button type="button" class="btn modern-btn modern-btn-primary">
                        <i class="fas fa-save"></i> Save Settings
                    </button>
                </div>
            </div>
        </div>
    </div>
@stop

@section('additional_css')
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
        .calculator-header {
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
        }

        .times-header {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        }

        .results-header {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        }

        .details-header {
            background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);
        }

        .chart-header {
            background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
        }

        /* Modern Switch */
        .modern-switch {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .modern-switch-input {
            position: relative;
            width: 44px;
            height: 24px;
            appearance: none;
            background: #d1d5db;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .modern-switch-input:checked {
            background: #6366f1;
        }

        .modern-switch-input::before {
            content: '';
            position: absolute;
            top: 2px;
            left: 2px;
            width: 20px;
            height: 20px;
            background: white;
            border-radius: 50%;
            transition: all 0.2s ease;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .modern-switch-input:checked::before {
            transform: translateX(20px);
        }

        .modern-switch-label {
            margin: 0;
            cursor: pointer;
        }

        .modern-switch-text {
            color: white;
            font-size: 12px;
            font-weight: 600;
        }

        /* Result Cards */
        .result-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            display: flex;
            align-items: center;
            gap: 16px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            transition: all 0.3s ease;
            border-left: 4px solid;
        }

        .result-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }

        .light-card {
            border-left-color: #06b6d4;
        }

        .deco-card {
            border-left-color: #f59e0b;
        }

        .deep-card {
            border-left-color: #ef4444;
        }

        .result-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            color: white;
            flex-shrink: 0;
        }

        .light-card .result-icon {
            background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);
        }

        .deco-card .result-icon {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        }

        .deep-card .result-icon {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        }

        .result-content h4 {
            margin: 0;
            font-size: 24px;
            font-weight: 700;
            color: #1f2937;
            line-height: 1;
        }

        .result-content p {
            margin: 4px 0 0 0;
            font-size: 14px;
            color: #6b7280;
            font-weight: 500;
        }

        /* Total Result Card */
        .total-result-card {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            border-radius: 15px;
            padding: 24px;
            display: flex;
            align-items: center;
            gap: 20px;
            color: white;
            box-shadow: 0 8px 25px rgba(16, 185, 129, 0.25);
        }

        .total-icon {
            width: 60px;
            height: 60px;
            border-radius: 15px;
            background: rgba(255, 255, 255, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            flex-shrink: 0;
        }

        .total-content h3 {
            margin: 0;
            font-size: 20px;
            font-weight: 700;
            line-height: 1;
        }

        .total-content p {
            margin: 8px 0 0 0;
            font-size: 18px;
            font-weight: 600;
            opacity: 0.9;
        }

        /* Chart Container */
        .chart-container {
            position: relative;
            height: 300px;
            width: 100%;
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

            .modern-input, .modern-select {
                padding: 10px 14px;
                font-size: 16px;
            }

            .result-card {
                padding: 16px;
                gap: 12px;
            }

            .result-icon {
                width: 40px;
                height: 40px;
                font-size: 16px;
            }

            .result-content h4 {
                font-size: 20px;
            }

            .total-result-card {
                padding: 20px;
                gap: 16px;
            }

            .total-icon {
                width: 50px;
                height: 50px;
                font-size: 20px;
            }

            .total-content h3 {
                font-size: 18px;
            }

            .total-content p {
                font-size: 16px;
            }

            .chart-container {
                height: 250px;
            }
        }
    </style>
@stop

@section('additional_js')
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
                placeholder: 'Select category',
                allowClear: true
            });
            
            let distributionChart = null;
            let chartJsPromise = null;

            function ensureChartJsLoaded() {
                if (window.Chart) {
                    return Promise.resolve();
                }
                if (chartJsPromise) {
                    return chartJsPromise;
                }
                chartJsPromise = new Promise((resolve, reject) => {
                    const script = document.createElement('script');
                    script.src = 'https://cdn.jsdelivr.net/npm/chart.js';
                    script.async = true;
                    script.onload = () => resolve();
                    script.onerror = () => reject(new Error('Failed to load chart library'));
                    document.head.appendChild(script);
                });
                return chartJsPromise;
            }
            
            // Handle category change
            $('#tilesCategory').on('change', function() {
                const selected = this.options[this.selectedIndex];
                if (!selected || !selected.value) {
                    return;
                }

                const light = selected.dataset.light || 4;
                const deco = selected.dataset.deco || 1;
                const deep = selected.dataset.deep || 1;
                document.getElementById('lightTimes').value = light;
                document.getElementById('decoTimes').value = deco;
                document.getElementById('deepTimes').value = deep;
            });
            
            // Handle exclude deep checkbox
            $('#excludeDeep').on('change', function() {
                if (this.checked) {
                    $('#deepSection').fadeOut();
                    $('#deepResultContainer').fadeOut();
                } else {
                    $('#deepSection').fadeIn();
                    $('#deepResultContainer').fadeIn();
                }
            });
            
            // Calculate button click
            $('#calculateBtn').on('click', function() {
                const categoryId = $('#tilesCategory').val();
                if (!categoryId) {
                    toastr.error('Please select a tiles category');
                    return;
                }
                
                // Show loading state
                const button = $(this);
                const originalText = button.html();
                button.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Calculating...');
                
                const data = {
                    category_id: categoryId,
                    quantity: $('#quantity').val(),
                    height: $('#height').val(),
                    light_times: $('#lightTimes').val(),
                    light_qty: 1,
                    deco_times: $('#decoTimes').val(),
                    deco_qty: 1,
                    deep_times: $('#deepTimes').val(),
                    deep_qty: 1,
                    exclude_deep: $('#excludeDeep').prop('checked')
                };
                
                fetch('/admin/decor-calculator/calculate', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    body: JSON.stringify(data)
                })
                .then(response => {
                    if (!response.ok) {
                        return response.json().then(err => {
                            throw new Error(err.message || `HTTP error! Status: ${response.status}`);
                        });
                    }
                    return response.json();
                })
                .then(response => {
                    // Display results with animations
                    $('#lightResult').text(`${Math.round(response.light_quantity)} pcs (${response.light_sqft.toFixed(2)} sq ft)`);
                    $('#decoResult').text(`${Math.round(response.deco_quantity)} pcs (${response.deco_sqft.toFixed(2)} sq ft)`);
                    $('#deepResult').text(`${Math.round(response.deep_quantity)} pcs (${response.deep_sqft.toFixed(2)} sq ft)`);
                    
                    // Show total
                    $('#totalResult').text(`${Math.round(response.total_tiles)} pcs (${response.total_sqft.toFixed(2)} sq ft)`);
                    
                    // Show calculation details
                    $('#calculationDetails').html(`
                        <div class="table-responsive">
                            <table class="table table-sm modern-table">
                                <tbody>
                                    <tr>
                                        <td><strong>Vertical Tiles:</strong></td>
                                        <td>${response.total_vertical_tiles.toFixed(2)}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Horizontal Tiles:</strong></td>
                                        <td>${response.horizontal_tiles.toFixed(2)}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Distribution:</strong></td>
                                        <td>${response.light_rows.toFixed(1)} Light, ${response.deco_rows} Deco, ${response.deep_rows.toFixed(1)} Deep</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    `);
                    
                    // Show the result card with animation
                    $('#resultCard').fadeIn();
                    
                    // Update chart
                    updateDistributionChart(response);
                    
                    // Show success message
                    toastr.success('Calculation completed successfully');
                    
                    // Scroll to results
                    $('html, body').animate({
                        scrollTop: $('#resultCard').offset().top - 100
                    }, 500);
                })
                .catch(error => {
                    toastr.error(error.message || 'Error in calculation. Please check your inputs.');
                    console.error('Calculation error:', error);
                })
                .finally(() => {
                    // Reset button
                    button.prop('disabled', false).html(originalText);
                });
            });
            
            function updateDistributionChart(data) {
                ensureChartJsLoaded()
                    .then(() => {
                        const ctx = document.getElementById('distributionChart').getContext('2d');
                        
                        if (distributionChart) {
                            distributionChart.destroy();
                        }
                        
                        distributionChart = new Chart(ctx, {
                            type: 'doughnut',
                            data: {
                                labels: ['Light', 'Deco', 'Deep'],
                                datasets: [{
                                    data: [
                                        Math.round(data.light_quantity), 
                                        Math.round(data.deco_quantity), 
                                        Math.round(data.deep_quantity)
                                    ],
                                    backgroundColor: [
                                        'rgba(6, 182, 212, 0.8)',
                                        'rgba(245, 158, 11, 0.8)',
                                        'rgba(239, 68, 68, 0.8)'
                                    ],
                                    borderColor: [
                                        'rgba(6, 182, 212, 1)',
                                        'rgba(245, 158, 11, 1)',
                                        'rgba(239, 68, 68, 1)'
                                    ],
                                    borderWidth: 2,
                                    hoverOffset: 10
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                cutout: '60%',
                                plugins: {
                                    legend: {
                                        position: 'bottom',
                                        labels: {
                                            padding: 20,
                                            usePointStyle: true,
                                            font: {
                                                size: 14,
                                                weight: '600'
                                            }
                                        }
                                    },
                                    tooltip: {
                                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                                        titleColor: 'white',
                                        bodyColor: 'white',
                                        borderColor: 'rgba(255, 255, 255, 0.1)',
                                        borderWidth: 1,
                                        cornerRadius: 8,
                                        callbacks: {
                                            label: function(context) {
                                                const label = context.label || '';
                                                const value = context.raw || 0;
                                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                                const percentage = Math.round((value / total) * 100);
                                                return `${label}: ${value} pcs (${percentage}%)`;
                                            }
                                        }
                                    }
                                },
                                animation: {
                                    animateRotate: true,
                                    duration: 1000
                                }
                            }
                        });
                    })
                    .catch(error => {
                        toastr.warning('Chart library failed to load. Results are still available.');
                        console.error('Chart load error:', error);
                    });
            }
            
            // Print results function
            window.printResults = function() {
                window.print();
            };
        });
    </script>
@stop
