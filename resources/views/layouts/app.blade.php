<!DOCTYPE html>
<html lang="{{ str_replace('_','-',app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SiladesBeng</title>

    {{-- GOOGLE FONTS + FAVICON --}}
    <link rel="dns-prefetch" href="https://fonts.googleapis.com">
    <link rel="dns-prefetch" href="https://fonts.gstatic.com">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" href="{{ asset('Admin/img/illustrations/logodomain.png') }}?v={{ time() }}" />

    {{-- Alpine.js --}}
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    {{-- Hotwire Turbo for SPA Navigation --}}
    <script type="module" src="https://cdn.jsdelivr.net/npm/@hotwired/turbo/+esm"></script>
    <style>
        .turbo-progress-bar {
            height: 4px;
            background-color: #45aaf2;
        }
    </style>

    {{-- Vite Tailwind--}}
    @vite('resources/css/app.css')

    {{-- Midtrans Snap JS (Global for Turbo SPA) --}}
    <script type="text/javascript" src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ config('services.midtrans.client_key') }}"></script>

    {{-- Page-specific styles --}}
    @stack('styles')
</head>
<body class="antialiased text-gray-900 bg-white min-h-screen flex flex-col overflow-x-hidden">

    {{-- Main content --}}
    @yield('content')

    {{-- Page-specific scripts --}}
    @stack('scripts')

    {{-- Service Unavailable Modal --}}
    @if(session('error_service_unavailable'))
    <div id="service-unavailable-overlay" class="fixed inset-0 flex items-center justify-center p-4" style="z-index: 9999; background-color: rgba(0,0,0,0.5); backdrop-filter: blur(4px);">
        <div id="service-unavailable-modal" class="bg-white rounded-2xl shadow-2xl max-w-sm w-full p-8 text-center relative" style="z-index: 10000;">

            <div class="w-20 h-20 mx-auto bg-red-100 rounded-full flex items-center justify-center mb-5">
                <svg class="w-10 h-10 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
            </div>
            
            <h2 class="text-2xl font-bold text-gray-900 mb-3">Layanan Belum Tersedia</h2>
            <p class="text-gray-600 mb-6 text-base">
                {{ session('error_service_unavailable') }}
            </p>

            <button id="btn-ok-service-error" class="w-full py-3 rounded-full font-semibold transition shadow-md hover:shadow-lg" style="background-color: #0099ff; color: white;">
                Tutup Peringatan
            </button>
        </div>
    </div>
    
    <script>
        function initModal() {
            var overlay = document.getElementById('service-unavailable-overlay');
            var btnOk = document.getElementById('btn-ok-service-error');

            function closeModal() {
                if (overlay) {
                    overlay.style.display = 'none';
                    overlay.remove();
                }
            }

            if (btnOk) btnOk.addEventListener('click', closeModal);
            if (overlay) overlay.addEventListener('click', function(e) {
                if (e.target === overlay) {
                    closeModal();
                }
            });
        }
        document.addEventListener('DOMContentLoaded', initModal);
        document.addEventListener('turbo:load', initModal);
        initModal();
    </script>
    @endif
</body>
</html>
