@extends('customer.layout')

@section('title', 'Change Password')

@section('content')
<div class="row">
    <div class="col-lg-6 mx-auto">
        <div class="card glass-card p-4">
            <div class="card-header pb-0">
                <h5 class="mb-0"><i class="fas fa-lock me-2 text-primary"></i>Set Your Password</h5>
                <p class="text-muted mb-0">Create a password so only you can access your portal.</p>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('customer.password.update') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fw-semibold">New Password</label>
                        <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" minlength="8" required>
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-semibold">Confirm Password</label>
                        <input type="password" name="password_confirmation" class="form-control" minlength="8" required>
                    </div>
                    <div class="d-flex gap-2 flex-wrap">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Update Password
                        </button>
                        <a href="{{ route('customer.dashboard') }}" class="btn quick-pill quick-pill-outline">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
        <div class="card glass-card p-3 mt-3">
            <div class="text-muted small">
                Use at least 8 characters. Keep it different from your phone number for better security.
            </div>
        </div>
    </div>
</div>
@endsection
