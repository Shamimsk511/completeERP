@extends('layouts.modern-admin')

@section('title', 'Categories')

@section('page_title', 'Categories')

@section('header_actions')
    <a href="{{ route('categories.create') }}" class="btn modern-btn modern-btn-primary">
        <i class="fas fa-plus"></i> Add New Category
    </a>
@stop

@section('page_content')
    <!-- Categories Table Card -->
    <div class="card modern-card">
        <div class="card-header modern-header">
            <div class="header-content">
                <div class="header-title">
                    <i class="fas fa-tags header-icon"></i>
                    <h3 class="card-title">Categories Management</h3>
                </div>
                <div class="header-badge">
                    <span class="badge modern-badge">{{ count($categories) }} Categories</span>
                </div>
            </div>
        </div>
        
        <div class="card-body modern-card-body">
            <!-- Table Controls -->
            <div class="table-controls">
                <div class="controls-left">
                    <div class="search-wrapper">
                        <input type="text" id="table-search" class="form-control modern-input" placeholder="Search categories...">
                        <div class="search-icon">
                            <i class="fas fa-search"></i>
                        </div>
                    </div>
                </div>
                <div class="controls-right">
                    <div class="view-controls">
                        <button type="button" class="btn modern-btn modern-btn-outline btn-sm view-toggle active" data-view="grid">
                            <i class="fas fa-th"></i> Grid
                        </button>
                        <button type="button" class="btn modern-btn modern-btn-outline btn-sm view-toggle" data-view="table">
                            <i class="fas fa-list"></i> Table
                        </button>
                    </div>
                    <div class="sort-controls">
                        <select id="sort-by" class="form-control modern-select">
                            <option value="name">Sort by Name</option>
                            <option value="box_pcs">Sort by Box PCS</option>
                            <option value="pieces_feet">Sort by Pieces/Feet</option>
                            <option value="created_at">Sort by Date</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Grid View -->
            <div id="grid-view" class="categories-grid">
                @foreach($categories as $category)
                    <div class="category-card" 
                         data-name="{{ strtolower($category->name) }}" 
                         data-box-pcs="{{ $category->box_pcs }}" 
                         data-pieces-feet="{{ $category->pieces_feet }}"
                         data-created="{{ $category->created_at->timestamp }}">
                        <div class="category-header">
                            <div class="category-title">
                                <h4>{{ $category->name }}</h4>
                                <span class="category-id">#{{ $category->id }}</span>
                            </div>
                        </div>
                        
                        <div class="category-content">
                            <div class="category-specs">
                                <div class="spec-item">
                                    <div class="spec-icon">
                                        <i class="fas fa-cube"></i>
                                    </div>
                                    <div class="spec-details">
                                        <div class="spec-label">Box (PCS)</div>
                                        <div class="spec-value">{{ $category->box_pcs }}</div>
                                    </div>
                                </div>
                                
                                <div class="spec-item">
                                    <div class="spec-icon">
                                        <i class="fas fa-ruler"></i>
                                    </div>
                                    <div class="spec-details">
                                        <div class="spec-label">Pieces (Feet)</div>
                                        <div class="spec-value">{{ $category->pieces_feet }}</div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="category-meta">
                                <div class="meta-item">
                                    <i class="fas fa-calendar"></i>
                                    Created {{ $category->created_at->diffForHumans() }}
                                </div>
                            </div>
                        </div>
                        
                        <div class="category-actions">
                            <a href="{{ route('categories.show', $category->id) }}" 
                               class="btn modern-btn modern-btn-info btn-sm" 
                               data-toggle="tooltip" 
                               title="View Details">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('categories.edit', $category->id) }}" 
                               class="btn modern-btn modern-btn-warning btn-sm" 
                               data-toggle="tooltip" 
                               title="Edit Category">
                                <i class="fas fa-edit"></i>
                            </a>
                            <button type="button" 
                                    class="btn modern-btn modern-btn-danger btn-sm delete-btn" 
                                    data-id="{{ $category->id }}" 
                                    data-name="{{ $category->name }}"
                                    data-toggle="tooltip" 
                                    title="Delete Category">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Table View -->
            <div id="table-view" class="categories-table" style="display: none;">
                <div class="table-responsive">
                    <table class="table modern-table" id="categories-data-table">
                        <thead>
                            <tr>
                                <th>
                                    <div class="th-content">
                                        <span>ID</span>
                                        <i class="fas fa-sort sort-icon"></i>
                                    </div>
                                </th>
                                <th>
                                    <div class="th-content">
                                        <span>Name</span>
                                        <i class="fas fa-sort sort-icon"></i>
                                    </div>
                                </th>
                                <th>
                                    <div class="th-content">
                                        <span>Box (PCS)</span>
                                        <i class="fas fa-sort sort-icon"></i>
                                    </div>
                                </th>
                                <th>
                                    <div class="th-content">
                                        <span>Pieces (Feet)</span>
                                        <i class="fas fa-sort sort-icon"></i>
                                    </div>
                                </th>
                                <th>
                                    <div class="th-content">
                                        <span>Created</span>
                                        <i class="fas fa-sort sort-icon"></i>
                                    </div>
                                </th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($categories as $category)
                                <tr data-id="{{ $category->id }}">
                                    <td>
                                        <span class="id-badge">{{ $category->id }}</span>
                                    </td>
                                    <td>
                                        <div class="name-cell">
                                            <div class="name-primary">{{ $category->name }}</div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="spec-badge spec-primary">{{ $category->box_pcs }}</span>
                                    </td>
                                    <td>
                                        <span class="spec-badge spec-secondary">{{ $category->pieces_feet }}</span>
                                    </td>
                                    <td>
                                        <div class="date-cell">
                                            <div class="date-primary">{{ $category->created_at->format('M d, Y') }}</div>
                                            <div class="date-secondary">{{ $category->created_at->format('h:i A') }}</div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <a href="{{ route('categories.show', $category->id) }}" 
                                               class="btn modern-btn modern-btn-info btn-sm" 
                                               data-toggle="tooltip" 
                                               title="View">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('categories.edit', $category->id) }}" 
                                               class="btn modern-btn modern-btn-warning btn-sm" 
                                               data-toggle="tooltip" 
                                               title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button type="button" 
                                                    class="btn modern-btn modern-btn-danger btn-sm delete-btn" 
                                                    data-id="{{ $category->id }}" 
                                                    data-name="{{ $category->name }}"
                                                    data-toggle="tooltip" 
                                                    title="Delete">
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

            <!-- Empty State -->
            @if(count($categories) == 0)
                <div class="empty-state">
                    <div class="empty-icon">
                        <i class="fas fa-tags"></i>
                    </div>
                    <div class="empty-title">No Categories Found</div>
                    <div class="empty-description">
                        Get started by creating your first category to organize your products.
                    </div>
                    <a href="{{ route('categories.create') }}" class="btn modern-btn modern-btn-primary">
                        <i class="fas fa-plus"></i> Create First Category
                    </a>
                </div>
            @endif
        </div>
    </div>

    <!-- Stats Cards -->
    @if(count($categories) > 0)
        <div class="row mt-4">
            <div class="col-md-3">
                <div class="stat-card stat-primary">
                    <div class="stat-icon">
                        <i class="fas fa-tags"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-value">{{ count($categories) }}</div>
                        <div class="stat-label">Total Categories</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card stat-success">
                    <div class="stat-icon">
                        <i class="fas fa-cube"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-value">{{ $categories->avg('box_pcs') ? number_format($categories->avg('box_pcs'), 0) : 0 }}</div>
                        <div class="stat-label">Avg Box PCS</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card stat-warning">
                    <div class="stat-icon">
                        <i class="fas fa-ruler"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-value">{{ $categories->avg('pieces_feet') ? number_format($categories->avg('pieces_feet'), 0) : 0 }}</div>
                        <div class="stat-label">Avg Pieces/Feet</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card stat-info">
                    <div class="stat-icon">
                        <i class="fas fa-calendar"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-value">{{ $categories->where('created_at', '>=', now()->subDays(30))->count() }}</div>
                        <div class="stat-label">Added This Month</div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Hidden form for deletions -->
    <form id="delete-form" method="POST" style="display: none;">
        @csrf
        @method('DELETE')
    </form>
@stop

@section('additional_css')
    <!-- External Modern Admin Styles -->
    <link href="{{ asset('css/modern-admin.css') }}" rel="stylesheet">
    
    <!-- Page-specific styles for categories index -->
    <style>
        /* Table Controls */
        .table-controls {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
            gap: 16px;
            flex-wrap: wrap;
        }

        .controls-left, .controls-right {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .search-wrapper {
            position: relative;
            min-width: 300px;
        }

        .search-wrapper .search-icon {
            position: absolute;
            right: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #6b7280;
            pointer-events: none;
        }

        .view-controls {
            display: flex;
            gap: 4px;
        }

        .view-toggle.active {
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
            color: white;
            border-color: #6366f1;
        }

        /* Categories Grid */
        .categories-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 20px;
        }

        .category-card {
            background: white;
            border: 2px solid #e5e7eb;
            border-radius: 16px;
            padding: 20px;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .category-card:hover {
            border-color: #6366f1;
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(99, 102, 241, 0.15);
        }

        .category-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 16px;
        }

        .category-title h4 {
            margin: 0;
            font-size: 18px;
            font-weight: 600;
            color: #374151;
        }

        .category-id {
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
            color: white;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
        }

        .category-specs {
            display: flex;
            gap: 16px;
            margin-bottom: 16px;
        }

        .spec-item {
            flex: 1;
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px;
            background: linear-gradient(135deg, rgba(99, 102, 241, 0.05) 0%, rgba(139, 92, 246, 0.05) 100%);
            border: 1px solid rgba(99, 102, 241, 0.1);
            border-radius: 12px;
        }

        .spec-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
        }

        .spec-details {
            flex: 1;
        }

        .spec-label {
            font-size: 12px;
            color: #6b7280;
            font-weight: 500;
        }

        .spec-value {
            font-size: 18px;
            font-weight: 700;
            color: #374151;
        }

        .category-meta {
            margin-bottom: 16px;
        }

        .meta-item {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 12px;
            color: #6b7280;
        }

        .meta-item i {
            color: #6366f1;
        }

        .category-actions {
            display: flex;
            gap: 8px;
            justify-content: flex-end;
            border-top: 1px solid #e5e7eb;
            padding-top: 16px;
        }

        /* Modern Table */
        .modern-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .modern-table thead {
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
            color: white;
        }

        .modern-table th {
            padding: 16px;
            font-weight: 600;
            text-align: left;
            border: none;
            position: relative;
        }

        .th-content {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
        }

        .sort-icon {
            opacity: 0.6;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .sort-icon:hover {
            opacity: 1;
        }

        .modern-table td {
            padding: 16px;
            border-bottom: 1px solid #e5e7eb;
            vertical-align: middle;
        }

        .modern-table tbody tr:hover {
            background: linear-gradient(135deg, rgba(99, 102, 241, 0.02) 0%, rgba(139, 92, 246, 0.02) 100%);
        }

        /* Table Cell Styles */
        .id-badge {
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
            color: white;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
        }

        .name-cell {
            display: flex;
            flex-direction: column;
        }

        .name-primary {
            font-weight: 600;
            color: #374151;
            font-size: 14px;
        }

        .spec-badge {
            background: linear-gradient(135deg, rgba(99, 102, 241, 0.1) 0%, rgba(139, 92, 246, 0.1) 100%);
            color: #6366f1;
            padding: 6px 12px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 12px;
            border: 1px solid rgba(99, 102, 241, 0.2);
        }

        .spec-primary {
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.1) 0%, rgba(5, 150, 105, 0.1) 100%);
            color: #059669;
            border-color: rgba(16, 185, 129, 0.2);
        }

        .spec-secondary {
            background: linear-gradient(135deg, rgba(245, 158, 11, 0.1) 0%, rgba(217, 119, 6, 0.1) 100%);
            color: #d97706;
            border-color: rgba(245, 158, 11, 0.2);
        }

        .date-cell {
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
        }

        .action-buttons {
            display: flex;
            gap: 8px;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
        }

        .empty-icon {
            width: 80px;
            height: 80px;
            margin: 0 auto 20px;
            background: linear-gradient(135deg, rgba(99, 102, 241, 0.1) 0%, rgba(139, 92, 246, 0.1) 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
            color: #6366f1;
        }

        .empty-title {
            font-size: 24px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 12px;
        }

        .empty-description {
            font-size: 16px;
            color: #6b7280;
            margin-bottom: 24px;
            max-width: 400px;
            margin-left: auto;
            margin-right: auto;
        }

        /* Stats Cards */
        .stat-card {
            background: white;
            border: 2px solid #e5e7eb;
            border-radius: 16px;
            padding: 24px;
            display: flex;
            align-items: center;
            gap: 16px;
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(135deg, var(--stat-color-1), var(--stat-color-2));
            border-radius: 16px 16px 0 0;
        }

        .stat-card {
            position: relative;
        }

        .stat-primary {
            --stat-color-1: #6366f1;
            --stat-color-2: #8b5cf6;
        }

        .stat-success {
            --stat-color-1: #10b981;
            --stat-color-2: #059669;
        }

        .stat-warning {
            --stat-color-1: #f59e0b;
            --stat-color-2: #d97706;
        }

        .stat-info {
            --stat-color-1: #3b82f6;
            --stat-color-2: #2563eb;
        }

        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 16px;
            background: linear-gradient(135deg, var(--stat-color-1), var(--stat-color-2));
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
        }

        .stat-content {
            flex: 1;
        }

        .stat-value {
            font-size: 32px;
            font-weight: 700;
            color: #374151;
            line-height: 1;
        }

        .stat-label {
            font-size: 14px;
            color: #6b7280;
            font-weight: 500;
            margin-top: 4px;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .table-controls {
                flex-direction: column;
                align-items: stretch;
                gap: 12px;
            }

            .controls-left, .controls-right {
                justify-content: space-between;
            }

            .search-wrapper {
                min-width: auto;
                flex: 1;
            }

            .categories-grid {
                grid-template-columns: 1fr;
                gap: 16px;
            }

            .category-specs {
                flex-direction: column;
                gap: 12px;
            }

            .category-actions {
                justify-content: center;
            }

            .stat-value {
                font-size: 24px;
            }
        }

        /* Animation Classes */
        .fade-in {
            animation: fadeIn 0.3s ease-in-out;
        }

        .slide-up {
            animation: slideUp 0.3s ease-in-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes slideUp {
            from { 
                opacity: 0; 
                transform: translateY(20px); 
            }
            to { 
                opacity: 1; 
                transform: translateY(0); 
            }
        }
    </style>
@stop

@section('additional_js')
    <!-- External Modern Admin Scripts -->
    <script src="{{ asset('js/modern-admin.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <!-- Page-specific Categories Index Logic -->
    <script>
        $(document).ready(function() {
            // Categories Index specific functionality
            const CategoriesIndex = {
                currentView: 'grid',
                
                // Initialize categories index specific features
                init: function() {
                    this.initViewToggle();
                    this.initSearch();
                    this.initSort();
                    this.initDeleteHandlers();
                    this.initAnimations();
                    
                    console.log('Categories Index initialized');
                },

                // View Toggle (Grid/Table)
                initViewToggle: function() {
                    $('.view-toggle').on('click', function() {
                        const view = $(this).data('view');
                        
                        // Update active state
                        $('.view-toggle').removeClass('active');
                        $(this).addClass('active');
                        
                        // Switch views
                        if (view === 'grid') {
                            $('#table-view').hide();
                            $('#grid-view').show().addClass('fade-in');
                            CategoriesIndex.currentView = 'grid';
                        } else {
                            $('#grid-view').hide();
                            $('#table-view').show().addClass('fade-in');
                            CategoriesIndex.currentView = 'table';
                        }
                        
                        // Save preference
                        ModernAdmin.utils.storage.set('categories_view', view);
                    });

                    // Load saved preference
                    const savedView = ModernAdmin.utils.storage.get('categories_view', 'grid');
                    $(`.view-toggle[data-view="${savedView}"]`).click();
                },

                // Search Functionality
                initSearch: function() {
                    const searchInput = $('#table-search');
                    
                    // Debounced search function
                    const debouncedSearch = ModernAdmin.utils.debounce(function(query) {
                        CategoriesIndex.filterCategories(query);
                    }, 300);

                    searchInput.on('input', function() {
                        const query = $(this).val().toLowerCase();
                        debouncedSearch(query);
                    });

                    // Clear search on escape
                    searchInput.on('keydown', function(e) {
                        if (e.keyCode === 27) { // Escape key
                            $(this).val('');
                            CategoriesIndex.filterCategories('');
                        }
                    });
                },

                // Filter categories based on search query
                filterCategories: function(query) {
                    if (this.currentView === 'grid') {
                        $('.category-card').each(function() {
                            const name = $(this).data('name');
                            const isVisible = !query || name.includes(query);
                            
                            if (isVisible) {
                                $(this).show().addClass('slide-up');
                            } else {
                                $(this).hide().removeClass('slide-up');
                            }
                        });
                    } else {
                        $('#categories-data-table tbody tr').each(function() {
                            const text = $(this).text().toLowerCase();
                            const isVisible = !query || text.includes(query);
                            
                            if (isVisible) {
                                $(this).show();
                            } else {
                                $(this).hide();
                            }
                        });
                    }

                    // Show/hide empty state
                    this.updateEmptyState(query);
                },

                // Sort Functionality
                initSort: function() {
                    $('#sort-by').on('change', function() {
                        const sortBy = $(this).val();
                        CategoriesIndex.sortCategories(sortBy);
                    });
                },

                // Sort categories
                sortCategories: function(sortBy) {
                    if (this.currentView === 'grid') {
                        const container = $('.categories-grid');
                        const cards = container.children('.category-card').get();
                        
                        cards.sort(function(a, b) {
                            let aVal, bVal;
                            
                            switch(sortBy) {
                                case 'name':
                                    aVal = $(a).data('name');
                                    bVal = $(b).data('name');
                                    return aVal.localeCompare(bVal);
                                case 'box_pcs':
                                    aVal = parseInt($(a).data('box-pcs'));
                                    bVal = parseInt($(b).data('box-pcs'));
                                    return aVal - bVal;
                                case 'pieces_feet':
                                    aVal = parseInt($(a).data('pieces-feet'));
                                    bVal = parseInt($(b).data('pieces-feet'));
                                    return aVal - bVal;
                                case 'created_at':
                                    aVal = parseInt($(a).data('created'));
                                    bVal = parseInt($(b).data('created'));
                                    return bVal - aVal; // Newest first
                                default:
                                    return 0;
                            }
                        });
                        
                        // Re-append sorted cards
                        container.empty().append(cards);
                        
                        // Add animation
                        cards.forEach((card, index) => {
                            setTimeout(() => {
                                $(card).addClass('slide-up');
                            }, index * 50);
                        });
                    } else {
                        // For table view, use simpler sorting
                        this.sortTable(sortBy);
                    }
                },

                // Sort table
                sortTable: function(sortBy) {
                    const table = $('#categories-data-table');
                    const tbody = table.find('tbody');
                    const rows = tbody.find('tr').get();
                    
                    const columnMap = {
                        'name': 1,
                        'box_pcs': 2,
                        'pieces_feet': 3,
                        'created_at': 4
                    };
                    
                    const columnIndex = columnMap[sortBy] || 1;
                    
                    rows.sort(function(a, b) {
                        const aText = $(a).find(`td:eq(${columnIndex})`).text().trim();
                        const bText = $(b).find(`td:eq(${columnIndex})`).text().trim();
                        
                        if (sortBy === 'box_pcs' || sortBy === 'pieces_feet') {
                            return parseInt(aText) - parseInt(bText);
                        } else if (sortBy === 'created_at') {
                            return new Date(bText) - new Date(aText); // Newest first
                        } else {
                            return aText.localeCompare(bText);
                        }
                    });
                    
                    tbody.empty().append(rows);
                },

                // Delete Handlers
                initDeleteHandlers: function() {
                    $(document).on('click', '.delete-btn', function(e) {
                        e.preventDefault();
                        
                        const categoryId = $(this).data('id');
                        const categoryName = $(this).data('name');
                        
                        Swal.fire({
                            title: 'Delete Category?',
                            html: `Are you sure you want to delete "<strong>${categoryName}</strong>"?<br><br><small class="text-muted">This action cannot be undone.</small>`,
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#ef4444',
                            cancelButtonColor: '#6b7280',
                            confirmButtonText: '<i class="fas fa-trash"></i> Yes, Delete',
                            cancelButtonText: '<i class="fas fa-times"></i> Cancel',
                            customClass: {
                                popup: 'swal-modern',
                                confirmButton: 'btn-delete',
                                cancelButton: 'btn-cancel'
                            }
                        }).then((result) => {
                            if (result.isConfirmed) {
                                CategoriesIndex.deleteCategory(categoryId, categoryName);
                            }
                        });
                    });
                },

                // Delete category via AJAX
                deleteCategory: function(categoryId, categoryName) {
                    const deleteForm = $('#delete-form');
                    deleteForm.attr('action', `/categories/${categoryId}`);
                    
                    // Show loading
                    Swal.fire({
                        title: 'Deleting...',
                        text: 'Please wait while we delete the category.',
                        icon: 'info',
                        allowOutsideClick: false,
                        showConfirmButton: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                    
                    $.ajax({
                        url: `/categories/${categoryId}`,
                        type: 'POST',
                        data: deleteForm.serialize(),
                        success: function(response) {
                            Swal.fire({
                                title: 'Deleted!',
                                text: `"${categoryName}" has been deleted successfully.`,
                                icon: 'success',
                                timer: 2000,
                                showConfirmButton: false
                            }).then(() => {
                                // Remove the category from view
                                CategoriesIndex.removeCategoryFromView(categoryId);

                                // Update stats
                                CategoriesIndex.updateStats();
                            });
                        },
                        error: function(xhr) {
                            let errorMessage = 'Failed to delete category.';
                            
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                errorMessage = xhr.responseJSON.message;
                            }
                            
                            Swal.fire({
                                title: 'Error!',
                                text: errorMessage,
                                icon: 'error',
                                confirmButtonText: 'OK'
                            });
                        }
                    });
                },

                // Remove category from current view
                removeCategoryFromView: function(categoryId) {
                    // Remove from grid view
                    $(`.category-card[data-id="${categoryId}"]`).fadeOut(300, function() {
                        $(this).remove();
                        CategoriesIndex.updateEmptyState();
                    });
                    
                    // Remove from table view
                    $(`#categories-data-table tbody tr[data-id="${categoryId}"]`).fadeOut(300, function() {
                        $(this).remove();
                    });
                },

                // Update stats after deletion
                updateStats: function() {
                    const remainingCategories = $('.category-card:visible').length;
                    $('.stat-card .stat-value').first().text(remainingCategories);
                    
                    if (remainingCategories === 0) {
                        setTimeout(() => {
                            location.reload();
                        }, 1000);
                    }
                },

                // Update empty state
                updateEmptyState: function(searchQuery = '') {
                    const visibleCards = $('.category-card:visible').length;
                    const visibleRows = $('#categories-data-table tbody tr:visible').length;
                    
                    if (visibleCards === 0 && visibleRows === 0) {
                        if (searchQuery) {
                            // Show no results message
                            if (!$('.no-results').length) {
                                const noResults = `
                                    <div class="no-results text-center py-4">
                                        <i class="fas fa-search fa-3x text-muted mb-3"></i>
                                        <h5>No categories found</h5>
                                        <p class="text-muted">No categories match your search "${searchQuery}"</p>
                                        <button class="btn modern-btn modern-btn-outline btn-sm" onclick="$('#table-search').val('').trigger('input')">
                                            <i class="fas fa-times"></i> Clear Search
                                        </button>
                                    </div>
                                `;
                                $('.categories-grid, .categories-table').after(noResults);
                            }
                        }
                    } else {
                        $('.no-results').remove();
                    }
                },

                // Initialize animations
                initAnimations: function() {
                    // Stagger animation for category cards
                    $('.category-card').each(function(index) {
                        $(this).css('animation-delay', `${index * 0.1}s`);
                        $(this).addClass('slide-up');
                    });

                    // Animate stats cards
                    $('.stat-card').each(function(index) {
                        setTimeout(() => {
                            $(this).addClass('slide-up');
                        }, 500 + (index * 100));
                    });

                    // Animate table rows
                    $('#categories-data-table tbody tr').each(function(index) {
                        $(this).css('animation-delay', `${index * 0.05}s`);
                    });
                }
            };

            // Initialize categories index functionality
            CategoriesIndex.init();

            // Initialize tooltips
            ModernAdmin.initTooltips();

            // Add keyboard shortcuts
            $(document).on('keydown', function(e) {
                // 'N' key to create new category
                if (e.keyCode === 78 && !e.ctrlKey && !e.altKey) {
                    const activeElement = document.activeElement;
                    if (activeElement.tagName !== 'INPUT' && activeElement.tagName !== 'TEXTAREA') {
                        e.preventDefault();
                        window.location.href = "{{ route('categories.create') }}";
                    }
                }
                
                // '/' key to focus search
                if (e.keyCode === 191 && !e.ctrlKey && !e.altKey) {
                    e.preventDefault();
                    $('#table-search').focus();
                }
                
                // 'G' key to toggle grid view
                if (e.keyCode === 71 && !e.ctrlKey && !e.altKey) {
                    const activeElement = document.activeElement;
                    if (activeElement.tagName !== 'INPUT' && activeElement.tagName !== 'TEXTAREA') {
                        e.preventDefault();
                        $('.view-toggle[data-view="grid"]').click();
                    }
                }
                
                // 'T' key to toggle table view
                if (e.keyCode === 84 && !e.ctrlKey && !e.altKey) {
                    const activeElement = document.activeElement;
                    if (activeElement.tagName !== 'INPUT' && activeElement.tagName !== 'TEXTAREA') {
                        e.preventDefault();
                        $('.view-toggle[data-view="table"]').click();
                    }
                }
            });

            // Show keyboard shortcuts help on first visit
            if (!ModernAdmin.utils.storage.get('categories_help_shown')) {
                setTimeout(() => {
                    ModernAdmin.showAlert(
                        'Keyboard shortcuts: Press "N" for new, "/" to search, "G" for grid, "T" for table', 
                        'info', 
                        6000
                    );
                    ModernAdmin.utils.storage.set('categories_help_shown', true);
                }, 1500);
            }

            console.log('Categories index page loaded successfully');
        });
    </script>
@stop