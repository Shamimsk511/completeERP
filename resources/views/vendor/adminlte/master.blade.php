<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>

    {{-- Base Meta Tags --}}
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- Custom Meta Tags --}}
    @yield('meta_tags')

    {{-- Title --}}
    <title>
        @yield('title_prefix', config('adminlte.title_prefix', ''))
        @yield('title', config('adminlte.title', 'AdminLTE 3'))
        @yield('title_postfix', config('adminlte.title_postfix', ''))
    </title>

    {{-- Custom stylesheets (pre AdminLTE) --}}
    @yield('adminlte_css_pre')

    {{-- Base Stylesheets (depends on Laravel asset bundling tool) --}}
    @if(config('adminlte.enabled_laravel_mix', false))
        <link rel="stylesheet" href="{{ mix(config('adminlte.laravel_mix_css_path', 'css/app.css')) }}">
    @else
        @switch(config('adminlte.laravel_asset_bundling', false))
            @case('mix')
                <link rel="stylesheet" href="{{ mix(config('adminlte.laravel_css_path', 'css/app.css')) }}">
            @break

            @case('vite')
                @vite([config('adminlte.laravel_css_path', 'resources/css/app.css'), config('adminlte.laravel_js_path', 'resources/js/app.js')])
            @break

            @case('vite_js_only')
                @vite(config('adminlte.laravel_js_path', 'resources/js/app.js'))
            @break

            @default
                <link rel="stylesheet" href="{{ asset('vendor/fontawesome-free/css/all.min.css') }}">
                <link rel="stylesheet" href="{{ asset('vendor/overlayScrollbars/css/OverlayScrollbars.min.css') }}">
                <link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/adminlte.min.css') }}">

                @if(config('adminlte.google_fonts.allowed', true))
                    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700,300italic,400italic,600italic">
                @endif
        @endswitch
    @endif

    {{-- Extra Configured Plugins Stylesheets --}}
    @include('adminlte::plugins', ['type' => 'css'])

    @php
        $themes = config('themes', []);
        $themeKey = $businessSettings->theme ?? 'indigo';
        $theme = $themes[$themeKey] ?? reset($themes);
        $theme = $theme ?: [
            'primary' => '#5b6cff',
            'primary_dark' => '#3b4edb',
            'accent' => '#f59e0b',
            'bg' => '#f5f7ff',
            'surface' => '#ffffff',
            'text' => '#0f172a',
            'muted' => '#64748b',
            'border' => '#e2e8f0',
            'sidebar' => '#0f172a',
            'sidebar_text' => '#e2e8f0',
        ];
    @endphp

    <style>
        :root {
            --app-primary: {{ $theme['primary'] }};
            --app-primary-dark: {{ $theme['primary_dark'] }};
            --app-accent: {{ $theme['accent'] }};
            --app-bg: {{ $theme['bg'] }};
            --app-surface: {{ $theme['surface'] }};
            --app-text: {{ $theme['text'] }};
            --app-muted: {{ $theme['muted'] }};
            --app-border: {{ $theme['border'] }};
            --app-sidebar: {{ $theme['sidebar'] }};
            --app-sidebar-text: {{ $theme['sidebar_text'] }};
            --app-topbar-start: var(--app-primary-dark);
            --app-topbar-end: var(--app-primary);
            --app-topbar-gradient: linear-gradient(135deg, var(--app-topbar-start), var(--app-topbar-end));
        }

        body {
            background-color: var(--app-bg);
            color: var(--app-text);
        }

        a {
            color: var(--app-primary);
        }

        a:hover {
            color: var(--app-primary-dark);
        }

        .content-wrapper {
            background-color: var(--app-bg);
        }

        .card,
        .modal-content,
        .info-box {
            background-color: var(--app-surface);
            border-color: var(--app-border);
        }

        .btn-primary,
        .bg-primary {
            background-color: var(--app-primary) !important;
            border-color: var(--app-primary-dark) !important;
        }

        .btn-primary:hover {
            background-color: var(--app-primary-dark) !important;
        }

        .badge-primary {
            background-color: var(--app-primary) !important;
        }

        .text-primary {
            color: var(--app-primary) !important;
        }

        .nav-pills .nav-link.active,
        .page-item.active .page-link {
            background-color: var(--app-primary);
            border-color: var(--app-primary);
        }

        .main-sidebar {
            background-color: var(--app-sidebar) !important;
        }

        .sidebar-dark-primary,
        .sidebar-dark-primary .sidebar {
            background-color: var(--app-sidebar) !important;
        }

        .main-sidebar .nav-sidebar .nav-link,
        .brand-link {
            color: var(--app-sidebar-text) !important;
        }

        .brand-link {
            background-color: var(--app-topbar) !important;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        }

        .main-header,
        .navbar {
            background-color: var(--app-surface) !important;
        }

        .navbar-primary {
            background-color: var(--app-topbar) !important;
        }

        .main-header.navbar.navbar-dark.navbar-primary,
        .main-header.navbar.navbar-dark {
            background-color: var(--app-topbar) !important;
            border-bottom: 1px solid rgba(255, 255, 255, 0.15);
        }

        .main-header.navbar.navbar-dark.navbar-primary .nav-link,
        .main-header.navbar.navbar-dark.navbar-primary .navbar-brand,
        .main-header.navbar.navbar-dark .nav-link,
        .main-header.navbar.navbar-dark .navbar-brand {
            color: #fff !important;
        }

        .main-header.navbar.navbar-dark.navbar-primary .nav-link:hover,
        .main-header.navbar.navbar-dark .nav-link:hover {
            color: #f8fafc !important;
        }

        .navbar-primary .nav-link,
        .navbar-primary .navbar-brand {
            color: #fff !important;
        }

        .navbar-nav .nav-link,
        .navbar-brand {
            color: var(--app-text) !important;
        }

        .navbar-nav .nav-link:hover {
            color: var(--app-primary) !important;
        }

        .sidebar-dark-primary .nav-sidebar > .nav-item > .nav-link.active {
            background-color: rgba(255, 255, 255, 0.12);
            color: #fff;
        }

        .sidebar-dark-primary .nav-sidebar > .nav-item > .nav-link:hover {
            background-color: rgba(255, 255, 255, 0.08);
        }

        .sidebar-dark-primary .nav-sidebar > .nav-item > .nav-link .nav-icon {
            color: inherit;
        }

        .navbar-white,
        .navbar-light {
            background-color: var(--app-surface);
        }
    </style>

    {{-- Livewire Styles --}}
    @if(config('adminlte.livewire'))
        @if(intval(app()->version()) >= 7)
            @livewireStyles
        @else
            <livewire:styles />
        @endif
    @endif

    {{-- Custom Stylesheets (post AdminLTE) --}}
    @yield('adminlte_css')
    <link rel="stylesheet" href="/css/admin_custom.css">

    {{-- Favicon --}}
    @if(config('adminlte.use_ico_only'))
        <link rel="shortcut icon" href="{{ asset('favicons/favicon.ico') }}" />
    @elseif(config('adminlte.use_full_favicon'))
        <link rel="shortcut icon" href="{{ asset('favicons/favicon.ico') }}" />
        <link rel="apple-touch-icon" sizes="57x57" href="{{ asset('favicons/apple-icon-57x57.png') }}">
        <link rel="apple-touch-icon" sizes="60x60" href="{{ asset('favicons/apple-icon-60x60.png') }}">
        <link rel="apple-touch-icon" sizes="72x72" href="{{ asset('favicons/apple-icon-72x72.png') }}">
        <link rel="apple-touch-icon" sizes="76x76" href="{{ asset('favicons/apple-icon-76x76.png') }}">
        <link rel="apple-touch-icon" sizes="114x114" href="{{ asset('favicons/apple-icon-114x114.png') }}">
        <link rel="apple-touch-icon" sizes="120x120" href="{{ asset('favicons/apple-icon-120x120.png') }}">
        <link rel="apple-touch-icon" sizes="144x144" href="{{ asset('favicons/apple-icon-144x144.png') }}">
        <link rel="apple-touch-icon" sizes="152x152" href="{{ asset('favicons/apple-icon-152x152.png') }}">
        <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('favicons/apple-icon-180x180.png') }}">
        <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicons/favicon-16x16.png') }}">
        <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicons/favicon-32x32.png') }}">
        <link rel="icon" type="image/png" sizes="96x96" href="{{ asset('favicons/favicon-96x96.png') }}">
        <link rel="icon" type="image/png" sizes="192x192" href="{{ asset('favicons/android-icon-192x192.png') }}">
        <link rel="manifest" crossorigin="use-credentials" href="{{ asset('favicons/manifest.json') }}">
        <meta name="msapplication-TileColor" content="#ffffff">
        <meta name="msapplication-TileImage" content="{{ asset('favicon/ms-icon-144x144.png') }}">
    @endif

</head>

<body class="@yield('classes_body')" @yield('body_data')>

    {{-- Body Content --}}
    @yield('body')

    {{-- Base Scripts (depends on Laravel asset bundling tool) --}}
    @if(config('adminlte.enabled_laravel_mix', false))
        <script src="{{ mix(config('adminlte.laravel_mix_js_path', 'js/app.js')) }}"></script>
    @else
        @switch(config('adminlte.laravel_asset_bundling', false))
            @case('mix')
                <script src="{{ mix(config('adminlte.laravel_js_path', 'js/app.js')) }}"></script>
            @break

            @case('vite')
            @case('vite_js_only')
            @break

            @default
                <script src="{{ asset('vendor/jquery/jquery.min.js') }}"></script>
                <script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
                <script src="{{ asset('vendor/overlayScrollbars/js/jquery.overlayScrollbars.min.js') }}"></script>
                <script src="{{ asset('vendor/adminlte/dist/js/adminlte.min.js') }}"></script>
        @endswitch
    @endif

    {{-- Extra Configured Plugins Scripts --}}
    @include('adminlte::plugins', ['type' => 'js'])

    {{-- Livewire Script --}}
    @if(config('adminlte.livewire'))
        @if(intval(app()->version()) >= 7)
            @livewireScripts
        @else
            <livewire:scripts />
        @endif
    @endif

    {{-- Custom Scripts --}}
    @yield('adminlte_js')

</body>

</html>
