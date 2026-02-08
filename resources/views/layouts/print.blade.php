<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') - {{ $businessSettings->business_name ?? config('adminlte.title') ?? config('app.name') }}</title>
    @yield('styles')
</head>
<body>
    <div class="print-container">
        @yield('content')
    </div>
</body>
</html>
