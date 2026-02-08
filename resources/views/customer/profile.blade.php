@extends('customer.layout')

@section('title', 'My Profile')

@section('content')
<div class="row">
    <div class="col-lg-8 mx-auto">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">
                    <i class="fas fa-user"></i> My Profile
                </h5>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i>
                    <strong>Note:</strong> This is a read-only view of your profile. 
                    To update your information, please contact our support team.
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-4">
                            <label class="form-label fw-bold">Customer ID</label>
                            <div class="p-2 bg-light rounded">{{ $customer->id }}</div>
                        </div>
                        
                        <div class="mb-4">
                            <label class="form-label fw-bold">Full Name</label>
                            <div class="p-2 bg-light rounded">{{ $customer->name }}</div>
                        </div>
                        
                        <div class="mb-4">
                            <label class="form-label fw-bold">Login Username</label>
                            <div class="p-2 bg-light rounded font-monospace">{{ $customer->username }}</div>
                        </div>
                        
                        <div class="mb-4">
                            <label class="form-label fw-bold">Phone Number</label>
                            <div class="p-2 bg-light rounded">{{ $customer->phone ?: 'Not provided' }}</div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="mb-4">
                            <label class="form-label fw-bold">Address</label>
                            <div class="p-2 bg-light rounded">{{ $customer->address ?: 'Not provided' }}</div>
                        </div>
                        
                        <div class="mb-4">
                            <label class="form-label fw-bold">Member Since</label>
                            <div class="p-2 bg-light rounded">{{ $customer->created_at->format('F d, Y') }}</div>
                        </div>
                        
                        <div class="mb-4">
                            <label class="form-label fw-bold">Opening Balance</label>
                            <div class="p-2 bg-light rounded amount {{ $customer->opening_balance > 0 ? 'negative' : ($customer->opening_balance < 0 ? 'positive' : 'zero') }}">
                                ৳{{ number_format($customer->opening_balance, 2) }}
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <label class="form-label fw-bold">Current Balance</label>
                            <div class="p-3 bg-primary bg-opacity-10 rounded border border-primary">
                                <span class="amount {{ $customer->outstanding_balance > 0 ? 'negative' : ($customer->outstanding_balance < 0 ? 'positive' : 'zero') }} fs-5 fw-bold">
                                    {{ $customer->formatted_balance }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Account Summary Card -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">
                    <i class="fas fa-chart-bar"></i> Account Summary
                </h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-md-3">
                        <div class="border rounded p-3">
                            <h4 class="text-primary">{{ $customer->invoices->count() }}</h4>
                            <small class="text-muted">Total Invoices</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="border rounded p-3">
                            <h4 class="text-success">{{ $customer->transactions->where('type', 'debit')->count() }}</h4>
                            <small class="text-muted">Payments Made</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="border rounded p-3">
                            <h4 class="text-info">{{ $customer->productReturns->count() }}</h4>
                            <small class="text-muted">Returns</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="border rounded p-3">
                            <h4 class="text-warning">{{ $customer->challans->count() }}</h4>
                            <small class="text-muted">Deliveries</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Login Information Card -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">
                    <i class="fas fa-key"></i> Login Information
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6>How to Login:</h6>
                        <ul class="list-unstyled">
                            <li><strong>Username:</strong> <code>{{ $customer->username }}</code></li>
                            @if($customer->password)
                                <li><strong>Password:</strong> Your custom password</li>
                            @else
                                <li><strong>Password:</strong> Your phone number</li>
                            @endif
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <h6>Example:</h6>
                        <div class="bg-light p-3 rounded">
                            <div><strong>Username:</strong> <code>{{ $customer->username }}</code></div>
                            @if(!$customer->password)
                                <div><strong>Password:</strong> <code>{{ $customer->phone }}</code></div>
                            @else
                                <div><strong>Password:</strong> <span class="text-muted">Your custom password</span></div>
                            @endif
                        </div>
                    </div>
                </div>
                
                <div class="alert alert-warning mt-3">
                    <i class="fas fa-shield-alt"></i>
                    <strong>Security Note:</strong> Keep your login credentials secure. 
                    Contact support immediately if you suspect unauthorized access to your account.
                </div>

                <a href="{{ route('customer.password.show') }}" class="btn btn-primary">
                    <i class="fas fa-lock me-2"></i>Change Password
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
