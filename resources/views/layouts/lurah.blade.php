@extends('layouts.app')

@push('styles')
    <!-- AOS CSS -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <style>
        .glass-panel {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.07);
        }
    </style>
@endpush

@section('content')
<div class="min-h-screen bg-gray-50 flex">
    <!-- Sidebar -->
    <aside class="w-64 bg-gradient-to-b from-[#115789] to-[#0d4065] text-white flex flex-col hidden md:flex shadow-2xl relative z-20">
        <div class="p-6 text-center border-b border-white/10">
            <div class="w-16 h-16 mx-auto bg-white rounded-full flex items-center justify-center mb-3 shadow-lg">
                <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1v2H9V7zm0 6h1v2H9v-2zm0 6h1v2H9v-2zm4-12h1v2h-1V7zm0 6h1v2h-1v-2zm0 6h1v2h-1v-2z"></path></svg>
            </div>
            <h2 class="text-xl font-bold text-amber-400">Portal Kelurahan</h2>
            <p class="text-xs text-gray-300 mt-1">{{ Auth::user()->region?->nama_desa ?? 'Desa' }}</p>
        </div>
        
        <div class="overflow-y-auto flex-1">
            <nav class="px-4 py-6 space-y-2">
                <p class="px-4 text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Menu Utama</p>
                
                <a href="{{ route('lurah.dashboard') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-white/10 transition {{ request()->routeIs('lurah.dashboard') ? 'bg-white/20 border-l-4 border-amber-400' : '' }}">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                    <span class="font-medium">Dashboard</span>
                </a>
                
                <a href="{{ route('lurah.laporan.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-white/10 transition {{ request()->routeIs('lurah.laporan.*') ? 'bg-white/20 border-l-4 border-amber-400' : '' }}">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                    <span class="font-medium">Kelola Laporan</span>
                </a>

                <div class="pt-6 mt-6 border-t border-white/10">
                    <p class="px-4 text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Kembali</p>
                    <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-white/10 transition text-gray-300 hover:text-white">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                        <span class="font-medium">Ke Panel Admin</span>
                    </a>
                </div>
            </nav>
        </div>
        
        <div class="p-4 border-t border-white/10">
            <div class="flex items-center gap-3 px-2">
                <div class="w-10 h-10 rounded-full bg-white/20 flex items-center justify-center overflow-hidden">
                    @if(Auth::user() && Auth::user()->file)
                        <img src="{{ route('media.avatar', basename(Auth::user()->file->path)) }}" class="w-full h-full object-cover">
                    @else
                        <svg class="w-6 h-6 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                    @endif
                </div>
                <div class="flex-1 overflow-hidden">
                    <p class="text-sm font-semibold truncate">{{ Auth::user()->name ?? 'Lurah' }}</p>
                    <p class="text-xs text-amber-400 truncate">{{ Auth::user()->role ?? 'Admin' }}</p>
                </div>
            </div>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="flex-1 flex flex-col h-screen overflow-hidden bg-gray-50/50 relative">
        <!-- Top Navbar -->
        <header class="glass-panel sticky top-0 px-8 py-4 flex items-center justify-between z-10">
            <div class="flex items-center gap-4">
                <!-- Mobile menu button -->
                <button class="md:hidden p-2 rounded-lg bg-gray-100 text-gray-600 hover:bg-gray-200">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                </button>
                <h1 class="text-2xl font-bold text-gray-800">@yield('page-title', 'Portal Lurah')</h1>
            </div>
            
            <div class="flex items-center gap-4">
                <span class="text-sm text-gray-500 hidden sm:block">{{ now()->translatedFormat('l, d F Y') }}</span>
                <a href="{{ route('admin.dashboard') }}" class="px-4 py-2 bg-white border border-gray-200 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-50 hover:text-gray-900 transition shadow-sm">
                    Panel Admin
                </a>
            </div>
        </header>

        <!-- Content Area -->
        <div class="flex-1 overflow-auto p-4 md:p-8">
            @yield('content')
        </div>
    </main>
</div>
@endsection

@push('scripts')
    <!-- AOS JS -->
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            AOS.init({
                duration: 800,
                once: true,
                offset: 50,
            });
        });
        
        document.addEventListener('turbo:load', function() {
            AOS.init({
                duration: 800,
                once: true,
                offset: 50,
            });
        });
    </script>
@endpush
