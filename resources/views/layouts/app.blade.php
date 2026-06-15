<!DOCTYPE html>
<html lang="{{ str_replace('_','-',app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SidesBeng</title>

    {{-- GOOGLE FONTS + FAVICON --}}
    <link rel="dns-prefetch" href="https://fonts.googleapis.com">
    <link rel="dns-prefetch" href="https://fonts.gstatic.com">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" href="{{ asset('Admin/img/illustrations/logodomain.png') }}?v={{ time() }}" />

    {{-- Vite Tailwind--}}
    @vite('resources/css/app.css')

    {{-- Page-specific styles --}}
    @stack('styles')
</head>
<body class="antialiased text-gray-900 bg-white min-h-screen overflow-x-hidden">

    {{-- Main content --}}
    @yield('content')

    {{-- Page-specific scripts --}}
    @stack('scripts')
</body>
</html>
