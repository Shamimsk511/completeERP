<!DOCTYPE html>
<html>
<head>
    @php
        $activePrintTemplate = $selectedTemplate ?? ($businessSettings->invoice_template ?? 'standard');
        if (!in_array($activePrintTemplate, ['standard', 'modern', 'simple', 'bold', 'elegant', 'imaginative'], true)) {
            $activePrintTemplate = 'standard';
        }
    @endphp
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') - {{ $businessSettings->business_name ?? config('adminlte.title') ?? config('app.name') }}</title>
    @include('partials.print-theme-styles')
    @yield('styles')
</head>
<body class="print-theme template-{{ $activePrintTemplate }}">
    <div class="print-container print-theme template-{{ $activePrintTemplate }}">
        @yield('content')
    </div>
</body>
</html>
