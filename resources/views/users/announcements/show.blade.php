@extends('layouts.user')

@section('title', $announcement->title)

@section('page')
<main class="flex-grow relative w-full">
    <section class="relative z-10 min-h-screen pt-32 pb-16">
        {{-- Background Image --}}
        <div class="fixed inset-0 pointer-events-none overflow-hidden z-0">
            <img src="{{ asset('Admin/img/elements/background.png') }}" class="w-full h-full object-cover" alt="">
        </div>

        <div class="max-w-7xl mx-auto px-6 relative z-10">
            
            {{-- Back Button --}}
            <div class="mb-8 animate-section">
                <a href="{{ route('announcements.index') }}"
                    class="inline-flex items-center gap-2 backdrop-blur-sm bg-white/70 hover:bg-white text-blue-600 hover:text-blue-700 px-6 py-2.5 rounded-full transition-all border border-white/80 shadow-md font-semibold text-sm">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                    <span>Kembali ke Daftar Kabar</span>
                </a>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                
                {{-- Main Content (Kiri) --}}
                <div class="lg:col-span-2 space-y-8 animate-section">
                    <div class="backdrop-blur-sm bg-white/80 rounded-3xl overflow-hidden shadow-xl border border-white/80">
                        
                        {{-- Image/Banner --}}
                        <div class="w-full h-64 md:h-96 bg-gray-100 relative">
                            @if($announcement->image_path)
                                <img src="{{ Storage::url($announcement->image_path) }}" alt="{{ $announcement->title }}" class="w-full h-full object-cover">
                            @else
                                <div class="w-full h-full flex items-center justify-center text-8xl opacity-30 bg-gradient-to-br from-blue-50 to-blue-200">
                                    @if($announcement->type == 'Pengumuman') 📢 
                                    @elseif($announcement->type == 'Event') 🎉
                                    @else 🤝
                                    @endif
                                </div>
                            @endif
                            
                            {{-- Gradient Overlay --}}
                            <div class="absolute inset-0 bg-gradient-to-t from-gray-900/80 via-gray-900/30 to-transparent"></div>
                            
                            {{-- Title Overlay --}}
                            <div class="absolute bottom-6 left-6 right-6">
                                <div class="flex items-center gap-3 mb-3">
                                    @if($announcement->type == 'Gotong Royong')
                                        <span class="px-4 py-1.5 bg-emerald-500 text-white rounded-full text-xs font-bold shadow-lg flex items-center gap-1.5">🤝 Gotong Royong</span>
                                    @elseif($announcement->type == 'Event')
                                        <span class="px-4 py-1.5 bg-purple-500 text-white rounded-full text-xs font-bold shadow-lg flex items-center gap-1.5">🎉 Event</span>
                                    @else
                                        <span class="px-4 py-1.5 bg-blue-500 text-white rounded-full text-xs font-bold shadow-lg flex items-center gap-1.5">📢 Pengumuman</span>
                                    @endif
                                    
                                    <span class="px-4 py-1.5 bg-white/20 backdrop-blur-md text-white rounded-full text-xs font-semibold border border-white/30 flex items-center gap-1.5">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                        {{ $announcement->created_at->format('d M Y') }}
                                    </span>
                                </div>
                                <h1 class="text-3xl md:text-4xl font-bold text-white drop-shadow-md leading-tight">{{ $announcement->title }}</h1>
                            </div>
                        </div>

                        {{-- Content Body --}}
                        <div class="p-6 md:p-8">
                            {{-- Author Info --}}
                            <div class="flex items-center justify-between border-b border-gray-200 pb-6 mb-6">
                                <div class="flex items-center gap-4">
                                    <div class="w-12 h-12 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center text-xl font-bold shadow-sm">
                                        {{ substr($announcement->admin->name ?? 'A', 0, 1) }}
                                    </div>
                                    <div>
                                        <p class="text-gray-900 font-bold text-sm md:text-base">{{ $announcement->admin->name ?? 'Admin Sistem' }}</p>
                                        <p class="text-blue-500 text-xs md:text-sm font-semibold flex items-center gap-1">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                            Pemerintah {{ $announcement->region->name ?? 'Pusat' }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="prose prose-lg max-w-none text-gray-700 leading-relaxed text-justify">
                                {!! nl2br(e($announcement->description)) !!}
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Sidebar (Kanan) --}}
                <div class="space-y-6 animate-section" style="animation-delay: 0.2s;">
                    
                    {{-- Info Event Card --}}
                    @if($announcement->event_date || $announcement->location)
                    <div class="backdrop-blur-sm bg-blue-50/90 rounded-3xl p-6 shadow-md border border-blue-100">
                        <h3 class="text-lg font-bold text-blue-800 mb-5 flex items-center gap-2 border-b border-blue-200 pb-3">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            Detail Pelaksanaan
                        </h3>
                        <div class="space-y-5">
                            @if($announcement->event_date)
                            <div class="flex gap-4">
                                <div class="w-10 h-10 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center shrink-0 shadow-sm">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500 font-semibold uppercase tracking-wider mb-0.5">Waktu</p>
                                    <p class="text-gray-900 font-bold">{{ $announcement->event_date->format('l, d F Y') }}</p>
                                    <p class="text-blue-600 font-semibold text-sm">{{ $announcement->event_date->format('H:i') }} WIB</p>
                                </div>
                            </div>
                            @endif
                            
                            @if($announcement->location)
                            <div class="flex gap-4">
                                <div class="w-10 h-10 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center shrink-0 shadow-sm">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500 font-semibold uppercase tracking-wider mb-0.5">Lokasi</p>
                                    <p class="text-gray-900 font-bold text-sm leading-snug">{{ $announcement->location }}</p>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                    @endif

                    {{-- Laporan Reference Card --}}
                    @if($announcement->laporan)
                    <div class="backdrop-blur-sm bg-white/80 rounded-3xl p-6 shadow-md border border-white/80 relative overflow-hidden">
                        <div class="absolute -right-4 -top-4 text-7xl opacity-5">💬</div>
                        <h3 class="text-md font-bold text-gray-800 mb-2">Tindak Lanjut Laporan Warga</h3>
                        <p class="text-xs text-gray-600 mb-4 leading-relaxed">Pengumuman ini diterbitkan sebagai tindak lanjut dari laporan warga berikut:</p>
                        
                        <div class="bg-gray-50 rounded-2xl p-4 border border-gray-100">
                            <p class="text-blue-600 font-bold mb-1.5 text-sm line-clamp-2">"{{ $announcement->laporan->nama }}"</p>
                            <p class="text-xs text-gray-500 mb-3">Oleh: <span class="font-semibold text-gray-700">{{ $announcement->laporan->user->name ?? 'Warga' }}</span></p>
                            <span class="inline-flex px-2.5 py-1 bg-green-100 text-green-700 text-xs rounded-md border border-green-200 font-semibold">Sedang Diproses</span>
                        </div>
                    </div>
                    @endif

                    {{-- Related Announcements --}}
                    @if($relatedAnnouncements->count() > 0)
                    <div class="backdrop-blur-sm bg-white/80 rounded-3xl p-6 shadow-md border border-white/80">
                        <h3 class="text-md font-bold text-gray-800 mb-5 border-b border-gray-200 pb-3">Kabar Lainnya dari {{ $announcement->region->name ?? 'Pusat' }}</h3>
                        <div class="space-y-4">
                            @foreach($relatedAnnouncements as $related)
                            <a href="{{ route('announcements.show', $related->id) }}" class="block group">
                                <div class="flex gap-4 items-start p-2 rounded-2xl hover:bg-blue-50 transition-colors">
                                    <div class="w-16 h-16 rounded-xl overflow-hidden bg-gray-100 shrink-0 border border-gray-200 shadow-sm">
                                        @if($related->image_path)
                                            <img src="{{ Storage::url($related->image_path) }}" class="w-full h-full object-cover group-hover:scale-110 transition-transform">
                                        @else
                                            <div class="w-full h-full flex items-center justify-center text-2xl bg-gradient-to-br from-gray-50 to-gray-200 opacity-60">📰</div>
                                        @endif
                                    </div>
                                    <div class="flex-1">
                                        <h4 class="text-gray-900 text-sm font-bold group-hover:text-blue-600 transition-colors line-clamp-2 leading-snug">{{ $related->title }}</h4>
                                        <p class="text-xs text-gray-500 mt-1.5 font-medium flex items-center gap-1">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                            {{ $related->created_at->format('d M Y') }}
                                        </p>
                                    </div>
                                </div>
                            </a>
                            @endforeach
                        </div>
                    </div>
                    @endif

                </div>
            </div>
        </div>
    </section>
</main>
@endsection

@push('styles')
<style>
    * {
        font-family: 'Inter', sans-serif;
    }

    /* Smooth animations */
    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .animate-section {
        animation: fadeIn 0.6s ease-out forwards;
        opacity: 0;
    }
</style>
@endpush
