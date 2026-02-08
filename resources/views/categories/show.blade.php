@extends('layouts.modern-admin')

@section('title', 'Category Details')

@section('page_title', 'Category Details')

@section('header_actions')
    <a href="{{ route('categories.index') }}" class="btn modern-btn modern-btn-outline">
        <i class="fas fa-arrow-left"></i> Back to Categories
    </a>
    <a href="{{ route('categories.edit', $category) }}" class="btn modern-btn modern-btn-warning">
        <i class="fas fa-edit"></i> Edit Category
    </a>
@stop

@section('page_content')
    <!-- Main Category Card -->
    <div class="card modern-card">
        <div class="card-header modern-header">
            <div class="header-content">
                <div class="header-title">
                    <i class="fas fa-tag header-icon"></i>
                    <h3 class="card-title">{{ $category->name }}</h3>
                </div>
                <div class="header-badge">
                    <span class="badge modern-badge">Category #{{ $category->id }}</span>
                </div>
            </div>
        </div>
        
        <div class="card-body modern-card-body">
            <div class="details-grid">
                <!-- Category Information Section -->
                <div class="details-section">
                    <div class="section-header">
                        <h4 class="section-title">
                            <i class="fas fa-info-circle"></i>
                            Category Information
                        </h4>
                    </div>
                    
                    <div class="details-content">
                        <div class="detail-item">
                            <div class="detail-label">
                                <i class="fas fa-tag"></i>
                                Category Name
                            </div>
                            <div class="detail-value">{{ $category->name }}</div>
                        </div>
                        
                        <div class="detail-item">
                            <div class="detail-label">
                                <i class="fas fa-hashtag"></i>
                                Category ID
                            </div>
                            <div class="detail-value">
                                <span class="id-badge">#{{ $category->id }}</span>
                            </div>
                        </div>
                        
                        <div class="detail-item">
                            <div class="detail-label">
                                <i class="fas fa-calendar-plus"></i>
                                Created Date
                            </div>
                            <div class="detail-value">
                                <div class="date-display">
                                    <div class="date-primary">{{ $category->created_at->format('F d, Y') }}</div>
                                    <div class="date-secondary">{{ $category->created_at->format('h:i A') }} ({{ $category->created_at->diffForHumans() }})</div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="detail-item">
                            <div class="detail-label">
                                <i class="fas fa-edit"></i>
                                Last Updated
                            </div>
                            <div class="detail-value">
                                <div class="date-display">
                                    <div class="date-primary">{{ $category->updated_at->format('F d, Y') }}</div>
                                    <div class="date-secondary">{{ $category->updated_at->format('h:i A') }} ({{ $category->updated_at->diffForHumans() }})</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Specifications Section -->
                <div class="details-section">
                    <div class="section-header">
                        <h4 class="section-title">
                            <i class="fas fa-ruler-combined"></i>
                            Product Specifications
                        </h4>
                    </div>
                    
                    <div class="specs-grid">
                        <div class="spec-card spec-primary">
                            <div class="spec-icon">
                                <i class="fas fa-cube"></i>
                            </div>
                            <div class="spec-content">
                                <div class="spec-label">Box (PCS)</div>
                                <div class="spec-value">{{ $category->box_pcs }}</div>
                                <div class="spec-unit">pieces per box</div>
                            </div>
                        </div>
                        
                        <div class="spec-card spec-secondary">
                            <div class="spec-icon">
                                <i class="fas fa-ruler"></i>
                            </div>
                            <div class="spec-content">
                                <div class="spec-label">Pieces (Feet)</div>
                                <div class="spec-value">{{ number_format($category->pieces_feet, 4) }}</div>
                                <div class="spec-unit">pieces per sq ft</div>
                            </div>
                        </div>
                        @if(\App\Models\ErpFeatureSetting::isEnabled('product_weight'))
                            @php
                                $weightValue = $category->weight_value;
                                $weightUnit = $category->weight_unit;
                                $weightUnitLabels = [
                                    'per_piece' => 'Per Piece',
                                    'per_box' => 'Per Box',
                                    'per_unit' => 'Per Unit',
                                ];
                                $weightLabel = $weightUnit ? ($weightUnitLabels[$weightUnit] ?? ucwords(str_replace('_', ' ', $weightUnit))) : null;
                                $perPieceWeight = null;
                                $perBoxWeight = null;

                                if ($weightValue !== null && $weightUnit) {
                                    if ($weightUnit === 'per_piece') {
                                        $perPieceWeight = (float) $weightValue;
                                        if (!empty($category->box_pcs)) {
                                            $perBoxWeight = $perPieceWeight * (float) $category->box_pcs;
                                        }
                                    } elseif ($weightUnit === 'per_box') {
                                        $perBoxWeight = (float) $weightValue;
                                        if (!empty($category->box_pcs)) {
                                            $perPieceWeight = $perBoxWeight / (float) $category->box_pcs;
                                        }
                                    }
                                }
                            @endphp
                            <div class="spec-card spec-info spec-wide">
                                <div class="spec-icon">
                                    <i class="fas fa-weight-hanging"></i>
                                </div>
                                <div class="spec-content">
                                    <div class="spec-label">Weight</div>
                                    <div class="spec-value">
                                        @if(!empty($weightValue) && !empty($weightLabel))
                                            {{ number_format($weightValue, 3) }} KG
                                        @else
                                            N/A
                                        @endif
                                    </div>
                                    <div class="spec-unit">{{ $weightLabel ?? 'Not set' }}</div>
                                    @if(!is_null($perPieceWeight))
                                        <div class="spec-unit">Per Piece: {{ number_format($perPieceWeight, 3) }} KG</div>
                                    @endif
                                    @if(!is_null($perBoxWeight))
                                        <div class="spec-unit">Per Box: {{ number_format($perBoxWeight, 3) }} KG</div>
                                    @endif
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Calculations Section -->
                <div class="details-section full-width">
                    <div class="section-header">
                        <h4 class="section-title">
                            <i class="fas fa-calculator"></i>
                            Packaging Calculations
                        </h4>
                    </div>
                    
                    <div class="calculations-display">
                        @php
                            $coveragePerBox = $category->box_pcs > 0 && $category->pieces_feet > 0 
                                ? $category->box_pcs * $category->pieces_feet
                                : 0;
                            $boxesPerHundred = $coveragePerBox > 0 ? 100 / $coveragePerBox : 0;
                            
                            // Efficiency rating
                            $efficiency = 'N/A';
                            $efficiencyClass = 'neutral';
                            if ($coveragePerBox >= 50) {
                                $efficiency = 'Excellent';
                                $efficiencyClass = 'excellent';
                            } elseif ($coveragePerBox >= 25) {
                                $efficiency = 'Good';
                                $efficiencyClass = 'good';
                            } elseif ($coveragePerBox >= 10) {
                                $efficiency = 'Average';
                                $efficiencyClass = 'average';
                            } elseif ($coveragePerBox > 0) {
                                $efficiency = 'Low';
                                $efficiencyClass = 'low';
                            }
                        @endphp
                        
                        <div class="calc-metrics">
                            <div class="calc-metric calc-info">
                                <div class="calc-icon">
                                    <i class="fas fa-expand-arrows-alt"></i>
                                </div>
                                <div class="calc-content">
                                    <div class="calc-label">Coverage per Box</div>
                                    <div class="calc-value">{{ $coveragePerBox > 0 ? number_format($coveragePerBox, 2) : 'N/A' }}</div>
                                    <div class="calc-unit">{{ $coveragePerBox > 0 ? 'sq ft' : '' }}</div>
                                </div>
                            </div>
                            
                            <div class="calc-metric calc-warning">
                                <div class="calc-icon">
                                    <i class="fas fa-boxes"></i>
                                </div>
                                <div class="calc-content">
                                    <div class="calc-label">Boxes per 100 sq ft</div>
                                    <div class="calc-value">{{ $boxesPerHundred > 0 ? number_format($boxesPerHundred, 1) : 'N/A' }}</div>
                                    <div class="calc-unit">{{ $boxesPerHundred > 0 ? 'boxes' : '' }}</div>
                                </div>
                            </div>
                            
                            <div class="calc-metric calc-{{ $efficiencyClass }}">
                                <div class="calc-icon">
                                    <i class="fas fa-chart-line"></i>
                                </div>
                                <div class="calc-content">
                                    <div class="calc-label">Efficiency Rating</div>
                                    <div class="calc-value">{{ $efficiency }}</div>
                                    <div class="calc-unit">packaging efficiency</div>
                                </div>
                            </div>
                        </div>
                        
                        @if($coveragePerBox > 0)
                            <div class="calc-explanation">
                                <div class="explanation-card">
                                    <div class="explanation-header">
                                        <h6><i class="fas fa-lightbulb"></i> How These Numbers Work</h6>
                                    </div>
                                    <div class="explanation-content">
                                        <div class="explanation-item">
                                            <strong>Coverage Calculation:</strong> 
                                            {{ $category->box_pcs }} pieces × {{ $category->pieces_feet }} pieces/sq ft = {{ number_format($coveragePerBox, 2) }} sq ft per box
                                        </div>
                                        <div class="explanation-item">
                                            <strong>Box Requirements:</strong> 
                                            To cover 100 sq ft, you need {{ number_format($boxesPerHundred, 1) }} boxes
                                        </div>
                                        <div class="explanation-item">
                                            <strong>Efficiency Rating:</strong> 
                                            @if($efficiency === 'Excellent')
                                                This category has excellent packaging efficiency with high coverage per box.
                                            @elseif($efficiency === 'Good')
                                                This category has good packaging efficiency with decent coverage per box.
                                            @elseif($efficiency === 'Average')
                                                This category has average packaging efficiency.
                                            @elseif($efficiency === 'Low')
                                                This category has low packaging efficiency - consider reviewing specifications.
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Usage Examples Section -->
                @if($coveragePerBox > 0)
                    <div class="details-section full-width">
                        <div class="section-header">
                            <h4 class="section-title">
                                <i class="fas fa-calculator"></i>
                                Practical Usage Examples
                            </h4>
                        </div>
                        
                        <div class="usage-examples">
                            <div class="example-grid">
                                @php
                                    $examples = [
                                        ['area' => 140, 'room' => 'Small Bathroom'],
                                        ['area' => 450, 'room' => 'Average Bedroom'],
                                        ['area' => 400, 'room' => 'Living Room'],
                                        ['area' => 600, 'room' => 'Large Hall']
                                    ];
                                @endphp
                                
                                @foreach($examples as $example)
                                    @php
                                        $boxesNeeded = $example['area'] / $coveragePerBox;
                                        $piecesNeeded = $example['area'] / $category->pieces_feet;
                                    @endphp
                                    <div class="example-card">
                                        <div class="example-header">
                                            <div class="example-room">{{ $example['room'] }}</div>
                                            <div class="example-area">{{ $example['area'] }} sq ft</div>
                                        </div>
                                        <div class="example-content">
                                            <div class="example-requirement">
                                                <span class="req-label">Boxes needed:</span>
                                                <span class="req-value">{{ number_format($boxesNeeded, 1) }}</span>
                                            </div>
                                            <div class="example-requirement">
                                                <span class="req-label">Total pieces:</span>
                                                <span class="req-value">{{ number_format($piecesNeeded, 0) }}</span>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Activity Timeline Section -->
                <div class="details-section full-width">
                    <div class="section-header">
                        <h4 class="section-title">
                            <i class="fas fa-history"></i>
                            Activity Timeline
                        </h4>
                    </div>
                    
                    <div class="timeline">
                        <div class="timeline-item">
                            <div class="timeline-marker timeline-created">
                                <i class="fas fa-plus"></i>
                            </div>
                            <div class="timeline-content">
                                <div class="timeline-title">Category Created</div>
                                <div class="timeline-description">
                                    Category "{{ $category->name }}" was created with specifications: {{ $category->box_pcs }} pcs/box and {{ number_format($category->pieces_feet, 4) }} pcs/feet
                                </div>
                                <div class="timeline-time">
                                    <i class="fas fa-clock"></i>
                                    {{ $category->created_at->format('F d, Y \a\t h:i A') }}
                                </div>
                            </div>
                        </div>
                        
                        @if($category->updated_at->ne($category->created_at))
                            <div class="timeline-item">
                                <div class="timeline-marker timeline-updated">
                                    <i class="fas fa-edit"></i>
                                </div>
                                <div class="timeline-content">
                                    <div class="timeline-title">Last Updated</div>
                                    <div class="timeline-description">
                                        Category information was last modified
                                    </div>
                                    <div class="timeline-time">
                                        <i class="fas fa-clock"></i>
                                        {{ $category->updated_at->format('F d, Y \a\t h:i A') }}
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions Card -->
    <div class="card modern-card mt-4">
        <div class="card-header modern-header">
            <div class="header-content">
                <div class="header-title">
                    <i class="fas fa-bolt header-icon"></i>
                    <h3 class="card-title">Quick Actions</h3>
                </div>
            </div>
        </div>
        
        <div class="card-body modern-card-body">
            <div class="quick-actions-grid">
                <a href="{{ route('categories.edit', $category) }}" class="action-card action-primary">
                    <div class="action-icon">
                        <i class="fas fa-edit"></i>
                    </div>
                    <div class="action-content">
                        <div class="action-title">Edit Category</div>
                        <div class="action-description">Modify specifications, name, or packaging details</div>
                    </div>
                </a>
                
                <a href="{{ route('categories.index') }}" class="action-card action-secondary">
                    <div class="action-icon">
                        <i class="fas fa-list"></i>
                    </div>
                    <div class="action-content">
                        <div class="action-title">All Categories</div>
                        <div class="action-description">View complete categories list</div>
                    </div>
                </a>
                
                <a href="{{ route('categories.create') }}" class="action-card action-success">
                    <div class="action-icon">
                        <i class="fas fa-plus"></i>
                    </div>
                    <div class="action-content">
                        <div class="action-title">Add New Category</div>
                        <div class="action-description">Create another category</div>
                    </div>
                </a>
                
                <button type="button" class="action-card action-info" onclick="printCategory()">
                    <div class="action-icon">
                        <i class="fas fa-print"></i>
                    </div>
                    <div class="action-content">
                        <div class="action-title">Print Details</div>
                        <div class="action-description">Print category information</div>
                    </div>
                </button>
            </div>
        </div>
    </div>
@stop

@section('additional_css')
    <!-- External Modern Admin Styles -->
    <link href="{{ asset('css/modern-admin.css') }}" rel="stylesheet">
    
    <!-- Page-specific styles for category details -->
    <style>
        /* Details Grid Layout */
        .details-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 24px;
        }

        .details-section.full-width {
            grid-column: 1 / -1;
        }

        /* Detail Items */
        .details-content {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .detail-item {
            display: flex;
            align-items: flex-start;
            padding: 16px;
            background: linear-gradient(135deg, rgba(99, 102, 241, 0.02) 0%, rgba(139, 92, 246, 0.02) 100%);
            border: 1px solid rgba(99, 102, 241, 0.1);
            border-radius: 12px;
            transition: all 0.2s ease;
        }

        .detail-item:hover {
            background: linear-gradient(135deg, rgba(99, 102, 241, 0.05) 0%, rgba(139, 92, 246, 0.05) 100%);
            border-color: rgba(99, 102, 241, 0.2);
            transform: translateY(-1px);
        }

        .detail-label {
            min-width: 140px;
            font-weight: 600;
            color: #374151;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .detail-label i {
            color: #6366f1;
            width: 16px;
        }

        .detail-value {
            flex: 1;
            color: #6b7280;
            font-size: 14px;
            line-height: 1.5;
        }

        .id-badge {
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
            color: white;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
        }

        .date-display {
            display: flex;
            flex-direction: column;
        }

        .date-primary {
            font-weight: 600;
            color: #374151;
            font-size: 14px;
        }

        .date-secondary {
            font-size: 12px;
            color: #6b7280;
            margin-top: 2px;
        }

        /* Specifications Grid */
        .specs-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
        }

        .spec-card {
            background: white;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            padding: 20px;
            display: flex;
            align-items: center;
            gap: 16px;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .spec-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        }

        .spec-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(135deg, var(--spec-color-1), var(--spec-color-2));
        }

        .spec-wide {
            grid-column: span 2;
        }

        .spec-primary {
            --spec-color-1: #10b981;
            --spec-color-2: #059669;
        }

        .spec-secondary {
            --spec-color-1: #f59e0b;
            --spec-color-2: #d97706;
        }

        .spec-info {
            --spec-color-1: #3b82f6;
            --spec-color-2: #2563eb;
        }

        .section-header .section-title,
        .section-header .section-title i {
            color: #ffffff;
        }

        .spec-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            color: white;
            background: linear-gradient(135deg, var(--spec-color-1), var(--spec-color-2));
        }

        .spec-content {
            flex: 1;
        }

        .spec-label {
            font-size: 12px;
            color: #6b7280;
            font-weight: 500;
            margin-bottom: 4px;
        }

        .spec-value {
            font-size: 24px;
            font-weight: 700;
            color: #374151;
            line-height: 1;
        }

        .spec-unit {
            font-size: 12px;
            color: #9ca3af;
            margin-top: 4px;
        }

        /* Calculations Display */
        .calculations-display {
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.02) 0%, rgba(37, 99, 235, 0.02) 100%);
            border: 1px solid rgba(59, 130, 246, 0.1);
            border-radius: 16px;
            padding: 24px;
        }

        .calc-metrics {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
            margin-bottom: 24px;
        }

        .calc-metric {
            background: white;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            padding: 16px;
            display: flex;
            align-items: center;
            gap: 12px;
            transition: all 0.3s ease;
        }

        .calc-metric:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .calc-info {
            border-color: #3b82f6;
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.05) 0%, rgba(37, 99, 235, 0.05) 100%);
        }

        .calc-warning {
            border-color: #f59e0b;
            background: linear-gradient(135deg, rgba(245, 158, 11, 0.05) 0%, rgba(217, 119, 6, 0.05) 100%);
        }

        .calc-excellent {
            border-color: #10b981;
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.05) 0%, rgba(5, 150, 105, 0.05) 100%);
        }

        .calc-good {
            border-color: #10b981;
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.05) 0%, rgba(5, 150, 105, 0.05) 100%);
        }

        .calc-average {
            border-color: #f59e0b;
            background: linear-gradient(135deg, rgba(245, 158, 11, 0.05) 0%, rgba(217, 119, 6, 0.05) 100%);
        }

        .calc-low {
            border-color: #ef4444;
            background: linear-gradient(135deg, rgba(239, 68, 68, 0.05) 0%, rgba(220, 38, 38, 0.05) 100%);
        }

        .calc-neutral {
            border-color: #6b7280;
            background: linear-gradient(135deg, rgba(107, 114, 128, 0.05) 0%, rgba(75, 85, 99, 0.05) 100%);
        }

        .calc-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            color: white;
        }

        .calc-info .calc-icon {
            background: linear-gradient(135deg, #3b82f6, #2563eb);
        }

        .calc-warning .calc-icon {
            background: linear-gradient(135deg, #f59e0b, #d97706);
        }

        .calc-excellent .calc-icon, .calc-good .calc-icon {
            background: linear-gradient(135deg, #10b981, #059669);
        }

        .calc-average .calc-icon {
            background: linear-gradient(135deg, #f59e0b, #d97706);
        }

        .calc-low .calc-icon {
            background: linear-gradient(135deg, #ef4444, #dc2626);
        }

        .calc-neutral .calc-icon {
            background: linear-gradient(135deg, #6b7280, #4b5563);
        }

        .calc-content {
            flex: 1;
        }

        .calc-label {
            font-size: 12px;
            color: #6b7280;
            font-weight: 500;
            margin-bottom: 4px;
        }

        .calc-value {
            font-size: 18px;
            font-weight: 700;
            color: #374151;
        }

        .calc-unit {
            font-size: 12px;
            color: #9ca3af;
            margin-top: 2px;
        }

        /* Calculation Explanation */
        .calc-explanation {
            margin-top: 20px;
        }

        .explanation-card {
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            overflow: hidden;
        }

        .explanation-header {
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
            color: white;
            padding: 12px 16px;
        }

        .explanation-header h6 {
            margin: 0;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .explanation-content {
            padding: 16px;
        }

        .explanation-item {
            margin-bottom: 12px;
            font-size: 14px;
            line-height: 1.5;
        }

        .explanation-item:last-child {
            margin-bottom: 0;
        }

        .explanation-item strong {
            color: #374151;
        }

        /* Usage Examples */
        .usage-examples {
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.02) 0%, rgba(5, 150, 105, 0.02) 100%);
            border: 1px solid rgba(16, 185, 129, 0.1);
            border-radius: 16px;
            padding: 24px;
        }

        .example-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
        }

        .example-card {
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 16px;
            transition: all 0.3s ease;
        }

        .example-card:hover {
            border-color: #10b981;
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.15);
        }

        .example-header {
            margin-bottom: 12px;
        }

        .example-room {
            font-weight: 600;
            color: #374151;
            font-size: 14px;
        }

        .example-area {
            font-size: 12px;
            color: #6b7280;
        }

        .example-content {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .example-requirement {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .req-label {
            font-size: 12px;
            color: #6b7280;
        }

        .req-value {
            font-weight: 600;
            color: #10b981;
            font-size: 14px;
        }

        /* Timeline */
        .timeline {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .timeline-item {
            display: flex;
            align-items: flex-start;
            gap: 16px;
        }

        .timeline-marker {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            color: white;
            flex-shrink: 0;
        }

        .timeline-created {
            background: linear-gradient(135deg, #10b981, #059669);
        }

        .timeline-updated {
            background: linear-gradient(135deg, #3b82f6, #2563eb);
        }

        .timeline-content {
            flex: 1;
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 16px;
        }

        .timeline-title {
            font-size: 16px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 8px;
        }

        .timeline-description {
            font-size: 14px;
            color: #6b7280;
            margin-bottom: 12px;
            line-height: 1.5;
        }

        .timeline-time {
            font-size: 12px;
            color: #9ca3af;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        /* Quick Actions Grid */
        .quick-actions-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 16px;
        }

        .action-card {
            background: white;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            padding: 20px;
            display: flex;
            align-items: center;
            gap: 16px;
            text-decoration: none;
            color: inherit;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .action-card:hover {
            text-decoration: none;
            color: inherit;
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        }

        .action-primary:hover {
            border-color: #6366f1;
            background: linear-gradient(135deg, rgba(99, 102, 241, 0.05) 0%, rgba(139, 92, 246, 0.05) 100%);
        }

        .action-secondary:hover {
            border-color: #6b7280;
            background: linear-gradient(135deg, rgba(107, 114, 128, 0.05) 0%, rgba(75, 85, 99, 0.05) 100%);
        }

        .action-success:hover {
            border-color: #10b981;
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.05) 0%, rgba(5, 150, 105, 0.05) 100%);
        }

        .action-info:hover {
            border-color: #3b82f6;
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.05) 0%, rgba(37, 99, 235, 0.05) 100%);
        }

        .action-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            color: white;
        }

        .action-primary .action-icon {
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
        }

        .action-secondary .action-icon {
            background: linear-gradient(135deg, #6b7280, #4b5563);
        }

        .action-success .action-icon {
            background: linear-gradient(135deg, #10b981, #059669);
        }

        .action-info .action-icon {
            background: linear-gradient(135deg, #3b82f6, #2563eb);
        }

        .action-content {
            flex: 1;
        }

        .action-title {
            font-size: 16px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 6px;
        }

        .action-description {
            font-size: 14px;
            color: #6b7280;
            line-height: 1.4;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .details-grid {
                grid-template-columns: 1fr;
                gap: 16px;
            }

            .specs-grid {
                grid-template-columns: 1fr;
                gap: 12px;
            }

            .spec-wide {
                grid-column: span 1;
            }

            .calc-metrics {
                grid-template-columns: 1fr;
                gap: 12px;
            }

            .example-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 12px;
            }

            .quick-actions-grid {
                grid-template-columns: 1fr;
                gap: 12px;
            }

            .detail-item {
                flex-direction: column;
                gap: 8px;
                align-items: flex-start;
            }

            .detail-label {
                min-width: auto;
            }

            .timeline-item {
                flex-direction: column;
                gap: 12px;
            }

            .timeline-marker {
                align-self: flex-start;
            }

            .explanation-item {
                font-size: 13px;
            }
        }

        /* Print Styles */
        @media print {
            .modern-header,
            .quick-actions-grid,
            .action-card {
                display: none !important;
            }
            
            .modern-card {
                box-shadow: none;
                border: 1px solid #ddd;
            }
            
            .details-grid {
                display: block;
            }
            
            .details-section {
                page-break-inside: avoid;
                margin-bottom: 20px;
            }
        }

        /* Enhanced Visual Effects */
        .spec-card:hover .spec-icon {
            transform: scale(1.1);
        }

        .calc-metric:hover .calc-icon {
            transform: scale(1.1);
        }

        .example-card:hover .req-value {
            color: #059669;
            font-weight: 700;
        }

        /* Loading Animation for Dynamic Content */
        .fade-in {
            animation: fadeIn 0.6s ease-in-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Highlight Animation */
        .highlight {
            animation: highlight 1s ease-in-out;
        }

        @keyframes highlight {
            0%, 100% {
                background: inherit;
            }
            50% {
                background: rgba(99, 102, 241, 0.1);
            }
        }
    </style>
@stop

@section('additional_js')
    <!-- External Modern Admin Scripts -->
    <script src="{{ asset('js/modern-admin.js') }}"></script>
    
    <!-- Page-specific Category Show Logic -->
    <script>
        $(document).ready(function() {
            // Category Show specific functionality
            const CategoryShow = {
                // Initialize category show specific features
                init: function() {
                    this.initAnimations();
                    this.initInteractions();
                    this.addCalculatorFeatures();
                    
                    console.log('Category Show initialized');
                },

                // Initialize scroll animations
                initAnimations: function() {
                    // Animate elements on scroll
                    const observerOptions = {
                        threshold: 0.1,
                        rootMargin: '0px 0px -50px 0px'
                    };

                    const observer = new IntersectionObserver((entries) => {
                        entries.forEach(entry => {
                            if (entry.isIntersecting) {
                                entry.target.classList.add('fade-in');
                            }
                        });
                    }, observerOptions);

                    // Observe all animatable elements
                    document.querySelectorAll('.spec-card, .calc-metric, .example-card, .timeline-item, .action-card').forEach(el => {
                        observer.observe(el);
                    });

                    // Stagger animation for spec cards
                    document.querySelectorAll('.spec-card').forEach((card, index) => {
                        setTimeout(() => {
                            card.classList.add('fade-in');
                        }, index * 200);
                    });

                    // Stagger animation for calc metrics
                    document.querySelectorAll('.calc-metric').forEach((metric, index) => {
                        setTimeout(() => {
                            metric.classList.add('fade-in');
                        }, 300 + (index * 150));
                    });
                },

                // Initialize interactive elements
                initInteractions: function() {
                    // Add click to copy functionality for values
                    $('.spec-value, .calc-value').on('click', function() {
                        const text = $(this).text();
                        if (navigator.clipboard) {
                            navigator.clipboard.writeText(text).then(() => {
                                ModernAdmin.showAlert(`Copied "${text}" to clipboard`, 'success', 2000);
                                $(this).closest('.spec-card, .calc-metric').addClass('highlight');
                                setTimeout(() => {
                                    $(this).closest('.spec-card, .calc-metric').removeClass('highlight');
                                }, 1000);
                            });
                        }
                    });

                    // Add hover effects for detail items
                    $('.detail-item').on('mouseenter', function() {
                        $(this).addClass('pulse');
                        setTimeout(() => {
                            $(this).removeClass('pulse');
                        }, 300);
                    });

                    // Add tooltips for calculations
                    $('.calc-metric').each(function() {
                        const label = $(this).find('.calc-label').text();
                        const value = $(this).find('.calc-value').text();
                        const unit = $(this).find('.calc-unit').text();
                        $(this).attr('title', `${label}: ${value} ${unit}`);
                    });

                    // Initialize tooltips
                    ModernAdmin.initTooltips();
                },

                // Add calculator features
                addCalculatorFeatures: function() {
                    // Add a mini calculator for custom area calculations
                    this.createMiniCalculator();
                },

                // Create mini calculator
                createMiniCalculator: function() {
                    const boxPcs = {{ $category->box_pcs }};
                    const piecesFeet = {{ $category->pieces_feet }};
                    
                    if (boxPcs > 0 && piecesFeet > 0) {
                        const calculatorHtml = `
                            <div class="mini-calculator">
                                <div class="calculator-header">
                                    <h6><i class="fas fa-calculator"></i> Custom Area Calculator</h6>
                                </div>
                                <div class="calculator-content">
                                    <div class="calc-input-group">
                                        <label for="custom-area">Area (sq ft):</label>
                                        <input type="number" id="custom-area" class="form-control modern-input" placeholder="Enter area" min="0" step="0.01">
                                    </div>
                                    <div class="calc-results" id="calc-results" style="display: none;">
                                        <div class="result-item">
                                            <span class="result-label">Boxes needed:</span>
                                            <span class="result-value" id="boxes-needed">-</span>
                                        </div>
                                        <div class="result-item">
                                            <span class="result-label">Total pieces:</span>
                                            <span class="result-value" id="pieces-needed">-</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `;
                        
                        $('.usage-examples').after(calculatorHtml);
                        
                        // Add calculator functionality
                        $('#custom-area').on('input', function() {
                            const area = parseFloat($(this).val()) || 0;
                            
                            if (area > 0) {
                                const coveragePerBox = boxPcs * piecesFeet;
                                const boxesNeeded = area / coveragePerBox;
                                const piecesNeeded = area / piecesFeet;
                                
                                $('#boxes-needed').text(boxesNeeded.toFixed(1));
                                $('#pieces-needed').text(piecesNeeded.toFixed(0));
                                $('#calc-results').show().addClass('fade-in');
                            } else {
                                $('#calc-results').hide().removeClass('fade-in');
                            }
                        });
                    }
                }
            };

            // Global functions
            window.printCategory = function() {
                // Set print title
                document.title = 'Category Details - {{ $category->name }}';
                
                // Show print message
                ModernAdmin.showAlert('Preparing category details for printing...', 'info', 2000);
                
                // Trigger print after short delay
                setTimeout(() => {
                    window.print();
                }, 500);
            };

            // Enhanced keyboard shortcuts
            $(document).on('keydown', function(e) {
                // 'E' key to edit
                if (e.keyCode === 69 && !e.ctrlKey && !e.altKey) {
                    const activeElement = document.activeElement;
                    if (activeElement.tagName !== 'INPUT' && activeElement.tagName !== 'TEXTAREA') {
                        e.preventDefault();
                        window.location.href = "{{ route('categories.edit', $category) }}";
                    }
                }
                
                // 'P' key to print
                if (e.keyCode === 80 && !e.ctrlKey && !e.altKey) {
                    const activeElement = document.activeElement;
                    if (activeElement.tagName !== 'INPUT' && activeElement.tagName !== 'TEXTAREA') {
                        e.preventDefault();
                        printCategory();
                    }
                }
                
                // 'B' key to go back
                if (e.keyCode === 66 && !e.ctrlKey && !e.altKey) {
                    const activeElement = document.activeElement;
                    if (activeElement.tagName !== 'INPUT' && activeElement.tagName !== 'TEXTAREA') {
                        e.preventDefault();
                        window.location.href = "{{ route('categories.index') }}";
                    }
                }
                
                // 'C' key to focus custom calculator
                if (e.keyCode === 67 && !e.ctrlKey && !e.altKey) {
                    const activeElement = document.activeElement;
                    if (activeElement.tagName !== 'INPUT' && activeElement.tagName !== 'TEXTAREA') {
                        e.preventDefault();
                        const customArea = document.getElementById('custom-area');
                        if (customArea) {
                            customArea.focus();
                        }
                    }
                }
            });

            // Initialize category show functionality
            CategoryShow.init();

            // Show keyboard shortcuts help on first visit
            if (!ModernAdmin.utils.storage.get('category_show_help_shown')) {
                setTimeout(() => {
                    ModernAdmin.showAlert(
                        'Keyboard shortcuts: Press "E" to edit, "P" to print, "B" to go back, "C" for calculator', 
                        'info', 
                        6000
                    );
                    ModernAdmin.utils.storage.set('category_show_help_shown', true);
                }, 2000);
            }

            // Add smooth scrolling for anchor links
            $('a[href^="#"]').on('click', function(e) {
                e.preventDefault();
                const target = $(this.getAttribute('href'));
                if (target.length) {
                    ModernAdmin.utils.scrollTo(target, 80);
                }
            });

            console.log('Category details page loaded successfully');
        });
    </script>
    
    <!-- Additional Calculator Styles -->
    <style>
        .mini-calculator {
            background: linear-gradient(135deg, rgba(99, 102, 241, 0.05) 0%, rgba(139, 92, 246, 0.05) 100%);
            border: 1px solid rgba(99, 102, 241, 0.1);
            border-radius: 12px;
            padding: 20px;
            margin-top: 24px;
        }
        
        .calculator-header h6 {
            margin: 0 0 16px 0;
            color: #6366f1;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .calc-input-group {
            margin-bottom: 16px;
        }
        
        .calc-input-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #374151;
        }
        
        .calc-results {
            background: white;
            border-radius: 8px;
            padding: 16px;
            border: 1px solid rgba(99, 102, 241, 0.1);
        }
        
        .result-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
        }
        
        .result-item:last-child {
            margin-bottom: 0;
        }
        
        .result-label {
            font-size: 14px;
            color: #6b7280;
        }
        
        .result-value {
            font-weight: 600;
            color: #6366f1;
            font-size: 16px;
        }
    </style>
@stop
