@extends('adminlte::page')

@section('title', 'Verify Email - ERP System')

@section('content_header')
    <h1>Verify Your Email Address</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            @if (session('status') == 'verification-link-sent')
                <div class="alert alert-success" role="alert">
                    A new verification link has been sent to the email address you provided during registration.
                </div>
            @endif

            <p>Before proceeding, please check your email for a verification link. If you did not receive the email, click the button below to request another.</p>

            <form method="POST" action="{{ route('verification.send') }}">
                @csrf
                <button type="submit" class="btn btn-primary">
                    Resend Verification Email
                </button>
            </form>

            <form method="POST" action="{{ route('logout') }}" class="mt-3">
                @csrf
                <button type="submit" class="btn btn-link">
                    Log Out
                </button>
            </form>
        </div>
    </div>
@stop
