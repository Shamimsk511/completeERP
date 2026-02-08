@extends('adminlte::page')

@section('title', 'Import Products')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Import Products</h1>
        <a href="{{ route('products.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Back to Products
        </a>
    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-md-8">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">Upload Excel File</h3>
                </div>
                <div class="card-body">
                    <form action="{{ route('products.import') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="form-group">
                            <label for="file">Select Excel File (.xlsx or .xls)</label>
                            <div class="input-group">
                                <div class="custom-file">
                                    <input type="file" class="custom-file-input @error('file') is-invalid @enderror" id="file" name="file">
                                    <label class="custom-file-label" for="file">Choose file</label>
                                </div>
                            </div>
                            @error('file')
                                <span class="error invalid-feedback d-block">{{ $message }}</span>
                            @enderror
                        </div>
                        
                        <div class="alert alert-info">
                            <h5><i class="icon fas fa-info"></i> Important Notes:</h5>
                            <ul class="mb-0">
                                <li>Maximum file size: 50MB</li>
                                <li>All required fields must be filled in</li>
                                <li>The import process may take some time for large files</li>
                                <li>Make sure company IDs and category IDs exist in the system</li>
                            </ul>
                        </div>
                        
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-upload"></i> Upload and Import
                            </button>
                            <a href="{{ route('products.template') }}" class="btn btn-info ml-2">
                                <i class="fas fa-download"></i> Download Template
                            </a>
                        </div>
                    </form>
                </div>
            </div>
            
            @if (session('import_errors'))
                <div class="card card-warning">
                    <div class="card-header">
                        <h3 class="card-title">Import Errors</h3>
                    </div>
                    <div class="card-body">
                        <p>The following rows had errors and were not imported:</p>
                        <div class="table-responsive" style="max-height: 300px; overflow-y: auto;">
                            <table class="table table-sm table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th>Row</th>
                                        <th>Errors</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach(session('import_errors') as $error)
                                        <tr>
                                            <td>{{ $error['row'] }}</td>
                                            <td>
                                                <ul class="mb-0 pl-3">
                                                    @foreach($error['errors'] as $field => $messages)
                                                        @foreach($messages as $message)
                                                            <li>{{ $field }}: {{ $message }}</li>
                                                        @endforeach
                                                    @endforeach
                                                </ul>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif
        </div>
        
        <div class="col-md-4">
            <div class="card card-info">
                <div class="card-header">
                    <h3 class="card-title">Import Instructions</h3>
                </div>
                <div class="card-body">
                    <ol>
                        <li>Download the Excel template using the button below</li>
                        <li>Fill in your product data according to the template</li>
                        <li>Save the Excel file</li>
                        <li>Upload the file using the form</li>
                        <li>Click "Upload and Import" to begin the import process</li>
                    </ol>
                    <p><strong>Required fields:</strong></p>
                    <ul>
                        <li>name</li>
                        <li>company_id</li>
                        <li>category_id</li>
                        <li>purchase_price</li>
                        <li>sale_price</li>
                    </ul>
                    <p><strong>Optional fields:</strong></p>
                    <ul>
                        <li>opening_stock</li>
                        <li>is_stock_managed</li>
                        <li>default_godown_id</li>
                        <li>weight_value</li>
                        <li>weight_unit</li>
                        <li>description</li>
                    </ul>
                    <a href="{{ route('products.template.download') }}" class="btn btn-info btn-block">
                        <i class="fas fa-download"></i> Download Template
                    </a>
                </div>
            </div>
            
            <div class="card card-success">
                <div class="card-header">
                    <h3 class="card-title">Reference Data</h3>
                </div>
                <div class="card-body p-0">
                    <div class="nav-tabs-custom">
                        <ul class="nav nav-tabs nav-justified">
                            <li class="nav-item">
                                <a class="nav-link active" href="#companies" data-toggle="tab">Companies</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="#categories" data-toggle="tab">Categories</a>
                            </li>
                        </ul>
                        <div class="tab-content p-3" style="max-height: 300px; overflow-y: auto;">
                            <div class="tab-pane active" id="companies">
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Company Name</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach(App\Models\Company::brands()->orderBy('name')->get() as $company)
                                                <tr>
                                                    <td>{{ $company->id }}</td>
                                                    <td>{{ $company->name }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="tab-pane" id="categories">
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Category Name</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach(App\Models\Category::orderBy('name')->get() as $category)
                                                <tr>
                                                    <td>{{ $category->id }}</td>
                                                    <td>{{ $category->name }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script>
        $(document).ready(function() {
            // Show filename when selected
            $(".custom-file-input").on("change", function() {
                var fileName = $(this).val().split("\\").pop();
                $(this).siblings(".custom-file-label").addClass("selected").html(fileName);
            });
        });
    </script>
@stop

@section('css')
    <style>
        .nav-tabs-custom {
            background-color: #fff;
        }
        .nav-tabs-custom .nav-link {
            border-top-left-radius: 0;
            border-top-right-radius: 0;
        }
        .nav-tabs-custom .nav-link.active {
            border-top: 3px solid #17a2b8;
            margin-top: -3px;
        }
    </style>
@stop
