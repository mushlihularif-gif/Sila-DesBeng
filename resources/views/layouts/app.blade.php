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
    <link rel="icon" type="image/png" href="{{ asset('Admin/img/illustrations/logodomain.webp') }}?v={{ time() }}" />
    <link href='https://cdnjs.cloudflare.com/ajax/libs/boxicons/2.1.4/css/boxicons.min.css' rel='stylesheet'>

    {{-- Alpine.js --}}
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    {{-- Hotwire Turbo for SPA Navigation --}}
    <meta name="turbo-cache-control" content="no-cache">
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

        {{-- KYC Required Modal --}}
    @if(session('show_kyc_modal') || session('error_kyc_wilayah'))
    <div id="kyc-prompt-modal" class="fixed inset-0 flex items-center justify-center p-4" style="z-index: 999999;">
        <div class="absolute inset-0" style="background-color: rgba(17, 24, 39, 0.7); backdrop-filter: blur(4px);" onclick="document.getElementById('kyc-prompt-modal').remove()"></div>
        
        <div class="relative w-full max-w-lg z-10" style="animation: fadeInUp 0.3s ease-out;">
            <div class="bg-white rounded-2xl shadow-2xl border border-gray-100 overflow-hidden relative">
                <button onclick="document.getElementById('kyc-prompt-modal').remove()" class="absolute top-4 right-4 z-50 p-2 bg-gray-100 rounded-full hover:bg-gray-200 transition-colors">
                    <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
                <div class="p-8 text-center">
                    <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2" />
                        </svg>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-800 mb-2">Verifikasi Identitas Diperlukan</h2>
                    <p class="text-gray-600 mb-6 leading-relaxed">
                        {{ session('error_kyc_wilayah') ?? 'Anda harus menyelesaikan verifikasi KTP terlebih dahulu sebelum dapat menggunakan layanan ini. Hal ini bertujuan untuk mencegah penyalahgunaan dan memastikan keamanan bersama.' }}
                    </p>
                    <a href="{{ route('kyc.index') }}" class="inline-flex justify-center w-full px-6 py-3 border border-transparent text-base font-medium rounded-full text-white bg-blue-600 hover:bg-blue-700 shadow-lg hover:shadow-xl transition-all">
                        Mulai Verifikasi KTP Sekarang
                    </a>
                </div>
            </div>
        </div>
    </div>
    @endif

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

    @if(session('show_login_modal'))
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(function() {
                var btn = document.getElementById('btn-open-login');
                if (btn) btn.click();
            }, 300);
        });
        document.addEventListener('turbo:load', function() {
            setTimeout(function() {
                var btn = document.getElementById('btn-open-login');
                if (btn) btn.click();
            }, 300);
        });
    </script>
    @endif

    {{-- Global Modern Toast Notification System --}}
    <style>
        .sdb-toast-container { position: fixed; top: 70px; right: 24px; z-index: 999999 !important; display: flex; flex-direction: column; gap: 12px; pointer-events: none; }
        .sdb-toast { pointer-events: auto; display: flex; align-items: flex-start; padding: 16px 18px; border-radius: 14px; box-shadow: 0 8px 30px rgba(0,0,0,0.12), 0 2px 8px rgba(0,0,0,0.06); border-left: 4px solid; max-width: 380px; width: 100%; background: white; opacity: 0; transform: translateX(50px) scale(0.95); transition: all 0.5s cubic-bezier(0.16, 1, 0.3, 1); }
        .sdb-toast.sdb-toast-show { opacity: 1; transform: translateX(0) scale(1); }
        .sdb-toast.sdb-toast-hide { opacity: 0; transform: translateX(50px) scale(0.95); transition: all 0.4s ease-in; }
        .sdb-toast-success { border-left-color: #22c55e; }
        .sdb-toast-error { border-left-color: #ef4444; }
        .sdb-toast-warning { border-left-color: #f59e0b; }
        .sdb-toast-info { border-left-color: #3b82f6; }
        .sdb-toast-icon { flex-shrink: 0; width: 28px; height: 28px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 14px; margin-top: 1px; }
        .sdb-toast-success .sdb-toast-icon { background: #dcfce7; color: #16a34a; }
        .sdb-toast-error .sdb-toast-icon { background: #fee2e2; color: #dc2626; }
        .sdb-toast-warning .sdb-toast-icon { background: #fef3c7; color: #d97706; }
        .sdb-toast-info .sdb-toast-icon { background: #dbeafe; color: #2563eb; }
        .sdb-toast-body { flex: 1; min-width: 0; }
        .sdb-toast-title { font-size: 14px; font-weight: 700; color: #1e293b; margin-bottom: 2px; }
        .sdb-toast-msg { font-size: 13px; color: #64748b; line-height: 1.4; }
        .sdb-toast-close { flex-shrink: 0; margin-left: 12px; background: none; border: none; cursor: pointer; color: #94a3b8; padding: 4px; border-radius: 6px; transition: all 0.2s; display: flex; align-items: center; justify-content: center; }
        .sdb-toast-close:hover { background: #f1f5f9; color: #475569; }
        .sdb-toast-progress { position: absolute; bottom: 0; left: 4px; right: 0; height: 3px; border-radius: 0 0 14px 0; }
        .sdb-toast-success .sdb-toast-progress { background: linear-gradient(90deg, #22c55e, #86efac); }
        .sdb-toast-error .sdb-toast-progress { background: linear-gradient(90deg, #ef4444, #fca5a5); }
        .sdb-toast-warning .sdb-toast-progress { background: linear-gradient(90deg, #f59e0b, #fde68a); }
        .sdb-toast-info .sdb-toast-progress { background: linear-gradient(90deg, #3b82f6, #93c5fd); }
        @keyframes sdb-toast-progress { from { width: 100%; } to { width: 0%; } }
    </style>

    <div id="sdbToastContainer" class="sdb-toast-container"></div>

    <script>
    // ==========================================
    // GLOBAL TOAST NOTIFICATION - SiladesBeng
    // ==========================================
    window.showSiladesBengToast = function(type, title, message, duration) {
        duration = duration || 5000;
        var container = document.getElementById('sdbToastContainer');
        if (!container) return;

        var icons = {
            success: '<svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>',
            error: '<svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>',
            warning: '<svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01M10.29 3.86l-8.4 14.56a1.35 1.35 0 001.16 2.02h16.88a1.35 1.35 0 001.16-2.02L12.7 3.86a1.35 1.35 0 00-2.42 0z"></path></svg>',
            info: '<svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>'
        };

        var toast = document.createElement('div');
        toast.className = 'sdb-toast sdb-toast-' + type;
        toast.style.position = 'relative';
        toast.style.overflow = 'hidden';
        toast.innerHTML =
            '<div class="sdb-toast-icon">' + (icons[type] || icons.info) + '</div>' +
            '<div class="sdb-toast-body">' +
                '<div class="sdb-toast-title">' + title + '</div>' +
                (message ? '<div class="sdb-toast-msg">' + message + '</div>' : '') +
            '</div>' +
            '<button class="sdb-toast-close" onclick="this.parentElement.classList.add(\'sdb-toast-hide\'); setTimeout(function(){this.parentElement.remove()}.bind(this), 400)">' +
                '<svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>' +
            '</button>' +
            '<div class="sdb-toast-progress" style="animation: sdb-toast-progress ' + duration + 'ms linear forwards;"></div>';

        container.appendChild(toast);

        // Animate in
        setTimeout(function() { toast.classList.add('sdb-toast-show'); }, 30);

        // Auto dismiss
        setTimeout(function() {
            toast.classList.add('sdb-toast-hide');
            setTimeout(function() { if(toast.parentElement) toast.remove(); }, 400);
        }, duration);
    };
    </script>

    {{-- Server-side session flash toasts --}}
    @if(session('success'))
    <script>document.addEventListener('DOMContentLoaded', function(){ showSiladesBengToast('success', 'Berhasil', {!! json_encode(session('success')) !!}); });</script>
    @endif
    @if(session('error'))
    <script>document.addEventListener('DOMContentLoaded', function(){ showSiladesBengToast('error', 'Peringatan', {!! json_encode(session('error')) !!}); });</script>
    @endif
    @if(session('warning'))
    <script>document.addEventListener('DOMContentLoaded', function(){ showSiladesBengToast('warning', 'Perhatian', {!! json_encode(session('warning')) !!}); });</script>
    @endif
    @if(session('info'))
    <script>document.addEventListener('DOMContentLoaded', function(){ showSiladesBengToast('info', 'Informasi', {!! json_encode(session('info')) !!}); });</script>
    @endif
    @include('components.cropper-modal')
</body>
</html>
