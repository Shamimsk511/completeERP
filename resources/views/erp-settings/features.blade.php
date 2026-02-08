@extends('layouts.modern-admin')

@section('title', 'ERP Feature Settings')

@section('page_title', 'ERP Feature Settings')

@section('header_actions')
    <a href="{{ url('business-settings') }}" class="btn modern-btn modern-btn-secondary">
        <i class="fas fa-arrow-left"></i> Back to Settings
    </a>
@stop

@section('page_content')
    @if(session('success'))
        <div class="alert alert-success alert-dismissible modern-alert">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
            <i class="fas fa-check-circle"></i> {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible modern-alert">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
            <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
        </div>
    @endif

    <div class="card modern-card">
        <div class="card-header modern-header">
            <h3 class="card-title">
                <i class="fas fa-cogs"></i> Feature Toggles
            </h3>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                    <i class="fas fa-minus"></i>
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i>
                <strong>Note:</strong> Disabling a feature will hide it from the navigation menu and prevent access to its pages.
                Users will not be able to see or use disabled features. Changes take effect immediately.
            </div>

            <form action="{{ route('erp-settings.features.update') }}" method="POST" id="features-form">
                @csrf
                @method('PUT')

                @foreach($groups as $groupKey => $groupName)
                    @if(isset($featuresGrouped[$groupKey]))
                        <div class="feature-group mb-4">
                            <div class="feature-group-header">
                                <h5 class="mb-0">
                                    @switch($groupKey)
                                        @case('invoices')
                                            <i class="fas fa-file-invoice text-primary"></i>
                                            @break
                                        @case('delivery')
                                            <i class="fas fa-truck text-success"></i>
                                            @break
                                        @case('inventory')
                                            <i class="fas fa-boxes text-warning"></i>
                                            @break
                                        @case('financial')
                                            <i class="fas fa-money-bill-wave text-info"></i>
                                            @break
                                        @case('reports')
                                            <i class="fas fa-chart-bar text-danger"></i>
                                            @break
                                        @default
                                            <i class="fas fa-cog text-secondary"></i>
                                    @endswitch
                                    {{ $groupName }}
                                </h5>
                            </div>

                            <div class="feature-list">
                                @foreach($featuresGrouped[$groupKey] as $feature)
                                    <div class="feature-item">
                                        <div class="feature-info">
                                            <div class="feature-name">{{ $feature->feature_name }}</div>
                                            <div class="feature-description">{{ $feature->description }}</div>
                                        </div>
                                        <div class="feature-toggle">
                                            <div class="custom-control custom-switch custom-switch-lg">
                                                <input type="checkbox"
                                                       class="custom-control-input feature-switch"
                                                       id="feature_{{ $feature->feature_key }}"
                                                       name="features[]"
                                                       value="{{ $feature->feature_key }}"
                                                       data-feature-key="{{ $feature->feature_key }}"
                                                       {{ $feature->is_enabled ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="feature_{{ $feature->feature_key }}">
                                                    <span class="switch-status">{{ $feature->is_enabled ? 'Enabled' : 'Disabled' }}</span>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                @endforeach

                <div class="form-actions mt-4">
                    <button type="submit" class="btn modern-btn modern-btn-success btn-lg" id="save-btn">
                        <i class="fas fa-save"></i> Save All Changes
                    </button>
                    <button type="button" class="btn btn-outline-secondary btn-lg ml-2" onclick="location.reload()">
                        <i class="fas fa-undo"></i> Reset
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Quick Toggle All Card -->
    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card modern-card">
                <div class="card-header bg-success text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-toggle-on"></i> Quick Actions
                    </h5>
                </div>
                <div class="card-body">
                    <p class="text-muted">Use these buttons to quickly enable or disable all features at once.</p>
                    <div class="btn-group">
                        <button type="button" class="btn btn-success" id="enable-all-btn">
                            <i class="fas fa-check-circle"></i> Enable All
                        </button>
                        <button type="button" class="btn btn-danger" id="disable-all-btn">
                            <i class="fas fa-times-circle"></i> Disable All
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card modern-card">
                <div class="card-header bg-info text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-info-circle"></i> Feature Status Summary
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6">
                            <div class="h2 text-success mb-0" id="enabled-count">0</div>
                            <small class="text-muted">Enabled</small>
                        </div>
                        <div class="col-6">
                            <div class="h2 text-danger mb-0" id="disabled-count">0</div>
                            <small class="text-muted">Disabled</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
<style>
    .feature-group {
        background: #fff;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        overflow: hidden;
    }

    .feature-group-header {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        padding: 15px 20px;
        border-bottom: 1px solid #dee2e6;
    }

    .feature-group-header h5 {
        color: #495057;
        font-weight: 600;
    }

    .feature-group-header i {
        margin-right: 10px;
        width: 20px;
    }

    .feature-list {
        padding: 0;
    }

    .feature-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 15px 20px;
        border-bottom: 1px solid #f0f0f0;
        transition: background-color 0.2s ease;
    }

    .feature-item:last-child {
        border-bottom: none;
    }

    .feature-item:hover {
        background-color: #f8f9fa;
    }

    .feature-info {
        flex: 1;
        padding-right: 20px;
    }

    .feature-name {
        font-weight: 600;
        color: #333;
        font-size: 15px;
        margin-bottom: 4px;
    }

    .feature-description {
        color: #6c757d;
        font-size: 13px;
    }

    .feature-toggle {
        min-width: 120px;
        text-align: right;
    }

    /* Custom large switch */
    .custom-switch-lg .custom-control-label::before {
        height: 1.5rem;
        width: 2.75rem;
        border-radius: 1rem;
    }

    .custom-switch-lg .custom-control-label::after {
        height: calc(1.5rem - 4px);
        width: calc(1.5rem - 4px);
        border-radius: 50%;
    }

    .custom-switch-lg .custom-control-input:checked ~ .custom-control-label::after {
        transform: translateX(1.25rem);
    }

    .switch-status {
        font-size: 12px;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .custom-control-input:checked ~ .custom-control-label .switch-status {
        color: #28a745;
    }

    .custom-control-input:not(:checked) ~ .custom-control-label .switch-status {
        color: #dc3545;
    }

    /* Modern card styling */
    .modern-card {
        border: none;
        border-radius: 10px;
        box-shadow: 0 0 15px rgba(0,0,0,0.1);
    }

    .modern-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 10px 10px 0 0;
    }

    .modern-btn {
        border-radius: 25px;
        padding: 10px 25px;
        font-weight: 500;
        transition: all 0.3s ease;
    }

    .modern-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(0,0,0,0.2);
    }

    .modern-btn-success {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        border: none;
        color: white;
    }

    .form-actions {
        padding: 20px 0;
        border-top: 1px solid #dee2e6;
    }

    /* Disabled state styling */
    .feature-item.disabled {
        opacity: 0.6;
    }
</style>
@stop

@section('js')
<script>
$(document).ready(function() {
    // Update counts on page load
    updateCounts();

    // Handle individual toggle with instant save
    $('.feature-switch').on('change', function() {
        const $switch = $(this);
        const featureKey = $switch.data('feature-key');
        const isEnabled = $switch.is(':checked');
        const $label = $switch.siblings('.custom-control-label').find('.switch-status');

        // Update label immediately
        $label.text(isEnabled ? 'Enabled' : 'Disabled');

        // Update counts
        updateCounts();

        // Auto-save via AJAX and refresh page
        $.ajax({
            url: '{{ route("erp-settings.features.toggle") }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                feature_key: featureKey,
                is_enabled: isEnabled ? 1 : 0
            },
            success: function(response) {
                if (response.success) {
                    showToast('success', response.message + ' Refreshing...');
                    // Refresh page after short delay to update menu
                    setTimeout(function() {
                        location.reload();
                    }, 1000);
                }
            },
            error: function(xhr) {
                // Revert on error
                $switch.prop('checked', !isEnabled);
                $label.text(!isEnabled ? 'Enabled' : 'Disabled');
                updateCounts();
                showToast('error', 'Failed to update feature');
            }
        });
    });

    // Enable all features
    $('#enable-all-btn').on('click', function() {
        $('.feature-switch').prop('checked', true).each(function() {
            $(this).siblings('.custom-control-label').find('.switch-status').text('Enabled');
        });
        updateCounts();
    });

    // Disable all features
    $('#disable-all-btn').on('click', function() {
        if (confirm('Are you sure you want to disable all features? This will hide all modules from the navigation.')) {
            $('.feature-switch').prop('checked', false).each(function() {
                $(this).siblings('.custom-control-label').find('.switch-status').text('Disabled');
            });
            updateCounts();
        }
    });

    // Form submission
    $('#features-form').on('submit', function(e) {
        e.preventDefault();

        const $btn = $('#save-btn');
        const originalText = $btn.html();
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');

        $.ajax({
            url: $(this).attr('action'),
            method: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                if (response.success) {
                    showToast('success', response.message);
                }
            },
            error: function(xhr) {
                showToast('error', 'Failed to save settings');
            },
            complete: function() {
                $btn.prop('disabled', false).html(originalText);
            }
        });
    });

    function updateCounts() {
        const total = $('.feature-switch').length;
        const enabled = $('.feature-switch:checked').length;
        const disabled = total - enabled;

        $('#enabled-count').text(enabled);
        $('#disabled-count').text(disabled);
    }

    function showToast(type, message) {
        if (typeof Swal !== 'undefined') {
            const Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true
            });

            Toast.fire({
                icon: type,
                title: message
            });
        } else {
            alert(message);
        }
    }
});
</script>
@stop
