@extends('adminlte::page')

@section('title', $title ?? 'Dashboard')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            @if(isset($breadcrumbs))
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb bg-transparent p-0 m-0">
                        @foreach($breadcrumbs as $breadcrumb)
                            @if($loop->last)
                                <li class="breadcrumb-item active">{{ $breadcrumb['title'] }}</li>
                            @else
                                <li class="breadcrumb-item">
                                    <a href="{{ $breadcrumb['url'] }}" class="text-primary">{{ $breadcrumb['title'] }}</a>
                                </li>
                            @endif
                        @endforeach
                    </ol>
                </nav>
            @endif
            <h1 class="m-0">@yield('page_title', $pageTitle ?? 'Page Title')</h1>
        </div>
        <div class="d-flex align-items-center gap-2">
            @include('partials.company-switcher')
            @yield('header_actions')
        </div>
    </div>
@stop

@section('content')
    <div class="container-fluid">
        @yield('page_content')
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="/css/admin_custom.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    
        <link rel="stylesheet" href="{{ asset('css/modern-core.css') }}">
    <link rel="stylesheet" href="{{ asset('css/perf-lite.css') }}">
    
    @yield('additional_css')
    @stack('css')
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script src="{{ asset('js/modern-admin.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (window.ModernAdmin) {
                window.ModernAdmin.init();
            }
        });
    </script>

    @php
        $flashMessages = [];
        if (session('success')) {
            $flashMessages[] = ['type' => 'success', 'title' => 'Success', 'message' => session('success')];
        }
        if (session('error')) {
            $flashMessages[] = ['type' => 'error', 'title' => 'Error', 'message' => session('error')];
        }
        if (session('warning')) {
            $flashMessages[] = ['type' => 'warning', 'title' => 'Warning', 'message' => session('warning')];
        }
    @endphp
    @if(count($flashMessages))
        <script>
            window.addEventListener('load', function() {
                const messages = @json($flashMessages);
                if (!messages.length) {
                    return;
                }

                const showNext = function(index) {
                    if (!window.Swal) {
                        return;
                    }
                    const msg = messages[index];
                    Swal.fire({
                        icon: msg.type,
                        title: msg.title,
                        text: msg.message,
                        timer: 2500,
                        showConfirmButton: false,
                        timerProgressBar: true
                    }).then(function() {
                        if (index + 1 < messages.length) {
                            showNext(index + 1);
                        }
                    });
                };

                if (window.Swal) {
                    showNext(0);
                } else if (window.ModernAdmin) {
                    messages.forEach(function(msg) {
                        window.ModernAdmin.showAlert(msg.message, msg.type);
                    });
                }
            });
        </script>
    @endif
    
    @yield('additional_js')
    @stack('js')
@stop

