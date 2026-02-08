<button type="button" class="decor-fab" id="decorCalcFab" aria-label="Open Decor Calculator">
    <i class="fas fa-calculator"></i>
</button>

<div class="decor-window" id="decorCalculatorWindow" aria-hidden="true">
    <div class="decor-window-header" id="decorCalculatorHeader">
        <div class="decor-window-title">
            <i class="fas fa-calculator"></i>
            <span>Decor Calculator</span>
        </div>
        <div class="decor-window-actions">
            <button type="button" class="btn btn-light btn-sm" id="decorCalculatorMinimize" title="Minimize">
                <i class="fas fa-minus"></i>
            </button>
            <button type="button" class="btn btn-light btn-sm" id="decorCalculatorClose" title="Close">
                <i class="fas fa-times"></i>
            </button>
        </div>
    </div>

    <div class="decor-window-body" id="decorCalculatorBody">
                <form id="calculatorForm">
                    <!-- Category Selection -->
                    <div class="form-group mb-3">
                        <select class="form-control modern-select" id="tilesCategory">
                            <option value="">Select Tiles Category</option>
                            @foreach(\App\Models\TilesCategory::all() as $category)
                                <option value="{{ $category->id }}" 
                                        data-height="{{ $category->height }}" 
                                        data-width="{{ $category->width }}">
                                    {{ $category->name }} ({{ $category->height }}x{{ $category->width }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <!-- Compact Measurements Row -->
                    <div class="row mb-3">
                        <div class="col-6">
                            <label class="form-label modern-label-sm">Area</label>
                            <div class="input-group input-group-sm">
                                <input type="number" class="form-control modern-input" id="quantity" value="100">
                                <div class="input-group-append">
                                    <span class="input-group-text">sq ft</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <label class="form-label modern-label-sm">Height</label>
                            <div class="input-group input-group-sm">
                                <input type="number" class="form-control modern-input" id="height" value="7">
                                <div class="input-group-append">
                                    <span class="input-group-text">ft</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Compact Times Settings -->
                    <div class="card modern-card-compact mb-3">
                        <div class="card-header modern-card-header-sm">
                            <span class="card-title-sm"><i class="fas fa-sliders-h"></i> Times Settings</span>
                            <div class="modern-switch-sm">
                                <input type="checkbox" class="modern-switch-input" id="excludeDeep">
                                <label class="modern-switch-label-sm" for="excludeDeep">Exclude Deep</label>
                            </div>
                        </div>
                        <div class="card-body p-2">
                            <div class="row">
                                <div class="col-4">
                                    <div class="spec-item-sm">
                                        <div class="spec-icon-sm spec-light">
                                            <i class="fas fa-sun"></i>
                                        </div>
                                        <div class="spec-details-sm">
                                            <label class="spec-label-sm">Light</label>
                                            <input type="number" class="form-control form-control-sm modern-input-sm" id="lightTimes" value="4" step="0.1">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="spec-item-sm">
                                        <div class="spec-icon-sm spec-deco">
                                            <i class="fas fa-paint-brush"></i>
                                        </div>
                                        <div class="spec-details-sm">
                                            <label class="spec-label-sm">Deco</label>
                                            <input type="number" class="form-control form-control-sm modern-input-sm" id="decoTimes" value="1" step="0.1">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-4" id="deepSection">
                                    <div class="spec-item-sm">
                                        <div class="spec-icon-sm spec-deep">
                                            <i class="fas fa-layer-group"></i>
                                        </div>
                                        <div class="spec-details-sm">
                                            <label class="spec-label-sm">Deep</label>
                                            <input type="number" class="form-control form-control-sm modern-input-sm" id="deepTimes" value="1" step="0.1">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Compact Calculate Button -->
                    <button type="button" class="btn modern-btn modern-btn-primary btn-block mb-3" id="calculateBtn">
                        <i class="fas fa-calculator"></i> Calculate
                    </button>
                </form>
                
                <!-- Compact Results -->
                <div id="resultBox" class="card modern-card-compact result-card" style="display: none;">
                    <div class="card-header modern-card-header-sm bg-success text-white">
                        <span class="card-title-sm"><i class="fas fa-chart-bar"></i> Results</span>
                    </div>
                    <div class="card-body p-2">
                        <!-- Compact Results Grid -->
                        <div class="row text-center mb-2">
                            <div class="col-4" id="lightResultContainer">
                                <div class="result-item-sm result-light">
                                    <div class="result-icon-sm"><i class="fas fa-sun"></i></div>
                                    <div class="result-label-sm">Light</div>
                                    <div class="result-value-sm" id="lightResult">0</div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="result-item-sm result-deco">
                                    <div class="result-icon-sm"><i class="fas fa-paint-brush"></i></div>
                                    <div class="result-label-sm">Deco</div>
                                    <div class="result-value-sm" id="decoResult">0</div>
                                </div>
                            </div>
                            <div class="col-4" id="deepResultContainer">
                                <div class="result-item-sm result-deep">
                                    <div class="result-icon-sm"><i class="fas fa-layer-group"></i></div>
                                    <div class="result-label-sm">Deep</div>
                                    <div class="result-value-sm" id="deepResult">0</div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Compact Total -->
                        <div class="total-result-sm text-center mb-2">
                            <strong id="totalResult">Total: 0 pcs (0.00 sq ft)</strong>
                        </div>
                        
                        <!-- Compact Details -->
                        <div class="calculation-details-sm">
                            <small class="text-muted" id="calculationDetails">Waiting for calculation...</small>
                        </div>
                    </div>
                </div>
    </div>
</div>

<style>
    .decor-fab {
        position: fixed;
        right: 24px;
        bottom: 24px;
        width: 56px;
        height: 56px;
        border-radius: 50%;
        background: linear-gradient(135deg, #2563eb, #6366f1);
        color: #fff;
        border: none;
        box-shadow: 0 10px 25px rgba(37, 99, 235, 0.35);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        z-index: 1050;
        cursor: pointer;
    }

    .decor-window {
        position: fixed;
        right: 24px;
        bottom: 90px;
        width: 360px;
        max-width: calc(100vw - 32px);
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        box-shadow: 0 16px 40px rgba(0, 0, 0, 0.18);
        display: none;
        z-index: 1051;
        resize: both;
        overflow: hidden;
        min-width: 280px;
        min-height: 220px;
    }

    .decor-window.is-open {
        display: block;
    }

    .decor-window-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 8px 12px;
        background: linear-gradient(135deg, #f8fafc, #eef2ff);
        border-bottom: 1px solid #e5e7eb;
        cursor: move;
        user-select: none;
    }

    .decor-window-title {
        display: flex;
        align-items: center;
        gap: 8px;
        font-weight: 600;
        color: #1f2937;
        font-size: 14px;
    }

    .decor-window-actions .btn {
        padding: 2px 6px;
        margin-left: 6px;
    }

    .decor-window-body {
        padding: 12px;
        height: calc(100% - 42px);
        overflow: auto;
    }

    .decor-window.minimized .decor-window-body {
        display: none;
    }

    .decor-window.minimized {
        height: 44px;
        min-height: 44px;
        resize: none;
        overflow: hidden;
    }

    @media (max-width: 768px) {
        .decor-window {
            width: calc(100vw - 32px);
            right: 16px;
            left: 16px;
            bottom: 90px;
        }

        .decor-fab {
            right: 16px;
            bottom: 16px;
        }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const fab = document.getElementById('decorCalcFab');
        const windowEl = document.getElementById('decorCalculatorWindow');
        const headerEl = document.getElementById('decorCalculatorHeader');
        const bodyEl = document.getElementById('decorCalculatorBody');
        const closeBtn = document.getElementById('decorCalculatorClose');
        const minimizeBtn = document.getElementById('decorCalculatorMinimize');

    function openWindow() {
            windowEl.classList.add('is-open');
            windowEl.setAttribute('aria-hidden', 'false');
        }

        function closeWindow() {
            windowEl.classList.remove('is-open');
            windowEl.classList.remove('minimized');
            windowEl.setAttribute('aria-hidden', 'true');
        }

        function toggleMinimize() {
            windowEl.classList.toggle('minimized');
        }

        fab.addEventListener('click', function() {
            if (windowEl.classList.contains('is-open')) {
                closeWindow();
            } else {
                openWindow();
            }
        });
        closeBtn.addEventListener('click', closeWindow);
        minimizeBtn.addEventListener('click', toggleMinimize);

        let isDragging = false;
        let dragOffsetX = 0;
        let dragOffsetY = 0;

        headerEl.addEventListener('mousedown', function(e) {
            if (e.target.closest('.decor-window-actions')) {
                return;
            }
            isDragging = true;
            const rect = windowEl.getBoundingClientRect();
            dragOffsetX = e.clientX - rect.left;
            dragOffsetY = e.clientY - rect.top;
            windowEl.style.right = 'auto';
            windowEl.style.bottom = 'auto';
            windowEl.style.left = rect.left + 'px';
            windowEl.style.top = rect.top + 'px';
            document.body.style.userSelect = 'none';
        });

        document.addEventListener('mousemove', function(e) {
            if (!isDragging) return;
            const newLeft = e.clientX - dragOffsetX;
            const newTop = e.clientY - dragOffsetY;
            windowEl.style.left = Math.max(8, newLeft) + 'px';
            windowEl.style.top = Math.max(8, newTop) + 'px';
        });

        document.addEventListener('mouseup', function() {
            if (!isDragging) return;
            isDragging = false;
            document.body.style.userSelect = '';
        });

        // Compact Modern Calculator
        const CompactCalculator = {
            init: function() {
                this.initCategoryHandlers();
                this.initExcludeToggle();
                this.initCalculateButton();
            },

            initCategoryHandlers: function() {
                document.getElementById('tilesCategory').addEventListener('change', function() {
                    const categoryId = this.value;
                    if (categoryId) {
                        fetch(`/admin/decor-calculator/settings/${categoryId}`)
                            .then(response => response.json())
                            .then(data => {
                                if (data) {
                                    document.getElementById('lightTimes').value = data.light_times || 4;
                                    document.getElementById('decoTimes').value = data.deco_times || 1;
                                    document.getElementById('deepTimes').value = data.deep_times || 1;
                                }
                            })
                            .catch(error => console.error('Error loading settings:', error));
                    }
                });
            },
            
            initExcludeToggle: function() {
                document.getElementById('excludeDeep').addEventListener('change', function() {
                    const deepSection = document.getElementById('deepSection');
                    const deepResult = document.getElementById('deepResultContainer');
                    
                    if (this.checked) {
                        deepSection.style.display = 'none';
                        deepResult.style.display = 'none';
                    } else {
                        deepSection.style.display = 'block';
                        deepResult.style.display = 'block';
                    }
                });
            },
            
            initCalculateButton: function() {
                document.getElementById('calculateBtn').addEventListener('click', function() {
                    const categoryId = document.getElementById('tilesCategory').value;
                    if (!categoryId) {
                        alert('Please select a tiles category');
                        return;
                    }
                    
                    const btn = this;
                    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Calculating...';
                    btn.disabled = true;
                    
                    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                    const data = {
                        category_id: categoryId,
                        quantity: document.getElementById('quantity').value,
                        height: document.getElementById('height').value,
                        light_times: document.getElementById('lightTimes').value,
                        light_qty: 1,
                        deco_times: document.getElementById('decoTimes').value,
                        deco_qty: 1,
                        deep_times: document.getElementById('deepTimes').value,
                        deep_qty: 1,
                        exclude_deep: document.getElementById('excludeDeep').checked
                    };
                    
                    fetch('/admin/decor-calculator/calculate', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        },
                        body: JSON.stringify(data)
                    })
                    .then(response => {
                        if (!response.ok) throw new Error(`HTTP error! Status: ${response.status}`);
                        return response.json();
                    })
                    .then(response => {
                        // Update results
                        document.getElementById('lightResult').innerHTML = 
                            `<strong>${response.light_sqft.toFixed(1)} sq ft</strong><br><small>${Math.round(response.light_quantity)} pcs</small>`;
                        document.getElementById('decoResult').innerHTML = 
                            `<strong>${response.deco_sqft.toFixed(1)} sq ft</strong><br><small>${Math.round(response.deco_quantity)} pcs</small>`;
                        document.getElementById('deepResult').innerHTML = 
                            `<strong>${response.deep_sqft.toFixed(1)} sq ft</strong><br><small>${Math.round(response.deep_quantity)} pcs</small>`;
                        document.getElementById('totalResult').textContent = 
                            `Total: ${response.total_sqft.toFixed(1)} sq ft (${Math.round(response.total_tiles)} pcs)`;
                        document.getElementById('calculationDetails').textContent = 
                            `V: ${response.total_vertical_tiles.toFixed(1)} | H: ${response.horizontal_tiles.toFixed(1)} | ${response.light_rows.toFixed(1)}L, ${response.deco_rows}D, ${response.deep_rows.toFixed(1)}Dp`;
                        
                        document.getElementById('resultBox').style.display = 'block';
                    })
                    .catch(error => {
                        console.error('Calculation error:', error);
                        alert('Error in calculation. Please check your inputs.');
                    })
                    .finally(() => {
                        btn.innerHTML = '<i class="fas fa-calculator"></i> Calculate';
                        btn.disabled = false;
                    });
                });
            }
        };

        CompactCalculator.init();
    });
</script>
