<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Rahman Tiles and Sanitary')</title>
    
    @yield('meta')
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Additional CSS -->
    <style>
        .container { max-width: 1200px; }
    </style>
</head>
<body class="bg-gray-50">
    @yield('content')
</body>
</html>