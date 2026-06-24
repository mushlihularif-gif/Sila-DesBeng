@extends('layouts.user')

@section('title', 'Daftar Warga - SilaDesBeng')

@push('styles')
<style>
    /* Custom styling if needed */
</style>
@endpush

@section('page')
<main class="flex-grow relative w-full min-h-screen">
    {{-- Custom Vector Abstract Background --}}
    @include('partials.abstract-bg')

    <section class="relative z-10 pt-32 pb-16">
        <div class="max-w-7xl mx-auto px-6" x-data="wargaData()">
            <!-- Header Section -->
            <div class="text-center mb-12 animate-section">
                <h1 class="text-4xl md:text-5xl font-bold mb-4">
                    <span class="bg-gradient-to-r from-gray-800 to-gray-600 bg-clip-text text-transparent">Daftar </span>
                    <span class="bg-gradient-to-r from-[#115789] to-[#60a5fa] bg-clip-text text-transparent">Warga</span>
                </h1>
                <p class="text-gray-700 text-lg mt-2 mb-6">
                    Daftar warga yang terdaftar di wilayah Anda.
                </p>
            </div>

            <!-- Filter & Search Bar -->
            <div class="max-w-5xl mx-auto mb-12 animate-section">
                <div class="backdrop-blur-sm bg-white/70 rounded-3xl p-4 md:p-6 border border-white/80 shadow-lg">
                    <div class="flex flex-col md:flex-row gap-6 justify-end items-center w-full">
                        {{-- Search Input (Style gradient) --}}
                        <div class="w-full lg:w-fit flex items-center justify-end">
                            <form action="{{ route('wilayah.warga.index') }}" method="GET" class="w-full sm:w-[280px] lg:w-[320px] relative group flex-shrink-0">
                                <div class="absolute -inset-0.5 bg-gradient-to-r from-blue-500 via-blue-400 to-amber-400 rounded-full opacity-70 group-hover:opacity-100 transition-opacity duration-300"></div>
                                <div class="relative flex items-center bg-white rounded-full overflow-hidden">
                                    <input type="text" name="search" value="{{ $search }}" placeholder="Cari nama warga..." 
                                        class="w-full pl-6 pr-4 py-3 text-gray-700 text-sm focus:outline-none bg-transparent">
                                    <button type="submit" class="flex-shrink-0 px-4 text-blue-500 hover:text-blue-600 focus:outline-none bg-transparent border-none cursor-pointer">
                                        <svg class="w-5 h-5 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                        </svg>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Content Warga -->
            <div class="max-w-5xl mx-auto animate-section" style="animation-delay: 0.3s;">
                @if($wargas->count() > 0)
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        @foreach($wargas as $warga)
                        <div class="backdrop-blur-sm bg-white/80 rounded-3xl border border-white/80 p-6 shadow-sm hover:shadow-xl transition-all duration-300 relative group overflow-hidden">
                            <!-- Aksen Gradient -->
                            <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-[#115789] to-[#60a5fa] transform origin-left scale-x-0 group-hover:scale-x-100 transition-transform duration-500"></div>
                            
                            <div class="flex items-center gap-4 mb-4">
                                <div class="w-14 h-14 rounded-full bg-blue-100 text-blue-500 flex items-center justify-center flex-shrink-0 overflow-hidden border-2 border-white shadow-sm">
                                    @if($warga->file)
                                        <img src="{{ $warga->file->file_stream }}" alt="Avatar" class="w-full h-full object-cover">
                                    @else
                                        <span class="text-xl font-bold uppercase">{{ substr($warga->name, 0, 1) }}</span>
                                    @endif
                                </div>
                                <div>
                                    <h3 class="font-bold text-gray-800 text-lg line-clamp-1" title="{{ $warga->name }}">{{ $warga->name }}</h3>
                                    <p class="text-sm text-gray-500 line-clamp-1">{{ $warga->email }}</p>
                                </div>
                            </div>
                            
                            <div class="space-y-3 mt-4">
                                <div class="flex items-center gap-2 text-sm text-gray-600 bg-gray-50/50 p-2 rounded-xl">
                                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.243-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                    <span class="truncate">{{ $warga->address ?? 'Alamat tidak tersedia' }}</span>
                                </div>
                                <div class="flex items-center gap-2 text-sm text-gray-600 bg-gray-50/50 p-2 rounded-xl">
                                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                                    <span class="truncate">{{ $warga->phone ?? 'Telepon tidak tersedia' }}</span>
                                </div>
                                <div class="flex items-center gap-2 text-sm text-gray-600 bg-gray-50/50 p-2 rounded-xl">
                                    <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                                    <span class="font-medium truncate">{{ $warga->region->name ?? 'Wilayah tidak diketahui' }}</span>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>

                    <!-- Pagination -->
                    <div class="mt-12 flex justify-center">
                        {{ $wargas->links() }}
                    </div>
                @else
                    <div class="backdrop-blur-sm bg-white/70 rounded-3xl text-center border border-white/80 shadow-lg py-16 px-6">
                        <div class="w-20 h-20 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-5 border border-gray-100">
                            <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                        </div>
                        <h3 class="text-2xl font-bold text-gray-800 mb-2">Tidak ada data warga</h3>
                        <p class="text-gray-600 mb-6 max-w-md mx-auto">Belum ada warga yang terdaftar di wilayah Anda atau yang sesuai dengan kata kunci pencarian Anda.</p>
                        @if($search)
                            <a href="{{ route('wilayah.warga.index') }}" class="inline-flex items-center justify-center px-6 py-3 rounded-full bg-blue-500 text-white font-medium hover:bg-blue-600 shadow-md transition-all">Reset Pencarian</a>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </section>
</main>
@endsection

@push('scripts')
<script>
    function wargaData() {
        return {
            // State for interactive features
        }
    }
</script>
@endpush
