@extends('layouts.modern-admin')

@section('title', 'Open Cash Register')
@section('page_title', 'Open Cash Register')

@section('header_actions')
    <a href="{{ route('cash-registers.index') }}" class="btn modern-btn modern-btn-secondary">
        <i class="fas fa-arrow-left"></i> Back
    </a>
@stop

@section('page_content')
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    @endif

    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-cash-register"></i> Open New Register
                    </h3>
                </div>
                <div class="card-body">
                    <div class="text-center mb-4">
                        <div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                            <i class="fas fa-user fa-2x text-primary"></i>
                        </div>
                        <h5 class="mt-3 mb-0">{{ Auth::user()->name }}</h5>
                        <small class="text-muted">{{ now()->format('d M Y, h:i A') }}</small>
                    </div>

                    <form action="{{ route('cash-registers.store') }}" method="POST">
                        @csrf

                        <div class="form-group">
                            <label for="opening_balance">
                                <i class="fas fa-money-bill-wave text-success"></i> Opening Balance <span class="text-danger">*</span>
                            </label>
                            <div class="input-group input-group-lg">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">৳</span>
                                </div>
                                <input type="number"
                                       class="form-control @error('opening_balance') is-invalid @enderror"
                                       id="opening_balance"
                                       name="opening_balance"
                                       value="{{ old('opening_balance', 0) }}"
                                       min="0"
                                       step="0.01"
                                       required
                                       autofocus>
                            </div>
                            @error('opening_balance')
                                <span class="text-danger small">{{ $message }}</span>
                            @enderror
                            <small class="form-text text-muted">Count the cash in drawer before starting</small>
                        </div>

                        <div class="form-group">
                            <label for="notes">
                                <i class="fas fa-sticky-note text-warning"></i> Notes (Optional)
                            </label>
                            <textarea class="form-control @error('notes') is-invalid @enderror"
                                      id="notes"
                                      name="notes"
                                      rows="2"
                                      placeholder="Any notes for this session...">{{ old('notes') }}</textarea>
                            @error('notes')
                                <span class="text-danger small">{{ $message }}</span>
                            @enderror
                        </div>

                        <hr>

                        <button type="submit" class="btn btn-success btn-lg btn-block">
                            <i class="fas fa-play-circle"></i> Open Cash Register
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@stop
