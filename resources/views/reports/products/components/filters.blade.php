<div class="card">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-filter"></i> Filter Report</h3>
        <div class="card-tools">
            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                <i class="fas fa-minus"></i>
            </button>
        </div>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ $action }}">
            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="start_date">Start Date</label>
                        <input type="date" class="form-control" id="start_date" name="start_date" 
                               value="{{ $filters['start_date'] ?? '' }}">
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="end_date">End Date</label>
                        <input type="date" class="form-control" id="end_date" name="end_date" 
                               value="{{ $filters['end_date'] ?? '' }}">
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="product_id">Product</label>
                        <select class="form-control" id="product_id" name="product_id">
                            <option value="">All Products</option>
                            @foreach($products as $product)
                                <option value="{{ $product->id }}" 
                                    {{ (isset($filters['product_id']) && $filters['product_id'] == $product->id) ? 'selected' : '' }}>
                                    {{ $product->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="category_id">Category</label>
                        <select class="form-control" id="category_id" name="category_id">
                            <option value="">All Categories</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" 
                                    {{ (isset($filters['category_id']) && $filters['category_id'] == $category->id) ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="company_id">Company</label>
                        <select class="form-control" id="company_id" name="company_id">
                            <option value="">All Companies</option>
                            @foreach($companies as $company)
                                <option value="{{ $company->id }}" 
                                    {{ (isset($filters['company_id']) && $filters['company_id'] == $company->id) ? 'selected' : '' }}>
                                    {{ $company->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                @if(isset($godowns) && $godowns->isNotEmpty())
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="godown_id">Godown</label>
                            <select class="form-control" id="godown_id" name="godown_id">
                                <option value="">All Godowns</option>
                                @foreach($godowns as $godown)
                                    <option value="{{ $godown->id }}"
                                        {{ (isset($filters['godown_id']) && $filters['godown_id'] == $godown->id) ? 'selected' : '' }}>
                                        {{ $godown->name }}{{ $godown->location ? ' - ' . $godown->location : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                @endif
                
                @php
                    $actionColClass = (isset($godowns) && $godowns->isNotEmpty()) ? 'col-md-6' : 'col-md-9';
                @endphp
                <div class="{{ $actionColClass }} d-flex align-items-end">
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i> Generate Report
                        </button>
                        
                        <a href="{{ $action }}" class="btn btn-default ml-2">
                            <i class="fas fa-sync"></i> Reset Filters
                        </a>
                        
                        <button type="button" class="btn btn-success ml-2" id="export-excel">
                            <i class="fas fa-file-excel"></i> Export to Excel
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
