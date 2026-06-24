@extends('layouts.user')

@section('title', 'Kelola Pengumuman - SilaDesBeng')

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
        <div class="max-w-7xl mx-auto px-6" x-data="pengumumanAdmin()">
            <!-- Header Section -->
            <div class="text-center mb-12 animate-section">
                <h1 class="text-4xl md:text-5xl font-bold mb-4">
                    <span class="bg-gradient-to-r from-gray-800 to-gray-600 bg-clip-text text-transparent">Kelola </span>
                    <span class="bg-gradient-to-r from-[#115789] to-[#60a5fa] bg-clip-text text-transparent">Pengumuman</span>
                </h1>
                <p class="text-gray-700 text-lg mt-2 mb-6">
                    Buat dan kelola pengumuman atau event untuk warga di wilayah Anda.
                </p>
                <button @click.prevent="isModalOpen = true" class="inline-flex items-center justify-center px-6 py-3 rounded-full bg-blue-500 text-white font-medium hover:bg-blue-600 shadow-md hover:shadow-lg transition-all border border-blue-500 focus:outline-none">
                    Buat Pengumuman Baru
                </button>
            </div>

            <!-- Filter & Search Bar -->
            <div class="max-w-5xl mx-auto mb-12 animate-section">
                <div class="backdrop-blur-sm bg-white/70 rounded-3xl p-4 md:p-6 border border-white/80 shadow-lg">
                    <div class="flex flex-col lg:flex-row gap-6 justify-between items-center w-full">
                        
                        {{-- Filter Pills --}}
                        <div class="flex flex-wrap md:flex-nowrap gap-2 w-full lg:w-auto justify-center lg:justify-start overflow-x-auto pb-2 md:pb-0 hide-scrollbar">
                            <button type="button" @click.prevent="updateFilter('')" 
                               :class="!type ? 'bg-blue-500 text-white border-blue-500 shadow-md' : 'bg-white text-gray-600 border-gray-200 hover:border-blue-300 hover:text-blue-500'"
                               class="px-4 py-2 whitespace-nowrap rounded-full font-semibold text-sm transition-all duration-300 border-2 focus:outline-none">
                               Semua
                            </button>
                            <button type="button" @click.prevent="updateFilter('Pengumuman')" 
                               :class="type === 'Pengumuman' ? 'bg-blue-500 text-white border-blue-500 shadow-md' : 'bg-white text-gray-600 border-gray-200 hover:border-blue-300 hover:text-blue-500'"
                               class="px-4 py-2 whitespace-nowrap rounded-full font-semibold text-sm transition-all duration-300 border-2 focus:outline-none">
                               Pengumuman
                            </button>
                            <button type="button" @click.prevent="updateFilter('Event')" 
                               :class="type === 'Event' ? 'bg-blue-500 text-white border-blue-500 shadow-md' : 'bg-white text-gray-600 border-gray-200 hover:border-blue-300 hover:text-blue-500'"
                               class="px-4 py-2 whitespace-nowrap rounded-full font-semibold text-sm transition-all duration-300 border-2 focus:outline-none">
                               Peristiwa
                            </button>
                            <button type="button" @click.prevent="updateFilter('Gotong Royong')" 
                               :class="type === 'Gotong Royong' ? 'bg-blue-500 text-white border-blue-500 shadow-md' : 'bg-white text-gray-600 border-gray-200 hover:border-blue-300 hover:text-blue-500'"
                               class="px-4 py-2 whitespace-nowrap rounded-full font-semibold text-sm transition-all duration-300 border-2 focus:outline-none">
                               Gotong Royong
                            </button>
                        </div>

                        {{-- Search Input (Style gradient dari beranda) --}}
                        <div class="w-full lg:w-fit flex items-center justify-end">
                            <div class="w-full sm:w-[280px] lg:w-[320px] relative group flex-shrink-0">
                                <div class="absolute -inset-0.5 bg-gradient-to-r from-blue-500 via-blue-400 to-amber-400 rounded-full opacity-70 group-hover:opacity-100 transition-opacity duration-300"></div>
                                <div class="relative flex items-center bg-white rounded-full overflow-hidden">
                                    <input type="text" x-model="search" @input.debounce.500ms="fetchData()" placeholder="Cari judul..." 
                                        class="w-full pl-6 pr-4 py-3 text-gray-700 text-sm focus:outline-none bg-transparent">
                                    <div class="flex-shrink-0 px-4" :class="loading ? 'text-amber-500 animate-spin' : 'text-blue-500 hover:text-blue-600'">
                                        <svg x-show="!loading" class="w-5 h-5 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                        </svg>
                                        <svg x-show="loading" class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" style="display: none;">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            <div id="pengumuman-list-container" class="transition-all duration-300" :class="{ 'opacity-50 pointer-events-none scale-[0.98]': loading }">
            <!-- Grid Pengumuman -->
            @if($pengumumans->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    @foreach($pengumumans as $item)
                        <div class="group flex flex-col backdrop-blur-sm bg-white/80 rounded-3xl overflow-hidden border border-gray-100 shadow-sm hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1">
                            
                            {{-- Image Header --}}
                            <div class="h-48 relative overflow-hidden bg-gray-100">
                                @if($item->image_path)
                                    <img src="{{ Storage::url($item->image_path) }}" alt="{{ $item->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                                @else
                                    <div class="w-full h-full flex items-center justify-center text-6xl opacity-50 bg-gradient-to-br from-blue-50 to-blue-100">
                                        @if($item->type == 'Pengumuman') 📢 
                                        @elseif($item->type == 'Event') 🎉
                                        @else 🤝
                                        @endif
                                    </div>
                                @endif
                                
                                {{-- Type Badge --}}
                                <div class="absolute top-4 left-4">
                                    @if($item->type == 'Gotong Royong')
                                        <span class="px-3 py-1.5 bg-emerald-500/90 backdrop-blur-md text-white rounded-full text-xs font-bold shadow-sm flex items-center gap-1.5">🤝 Gotong Royong</span>
                                    @elseif($item->type == 'Event')
                                        <span class="px-3 py-1.5 bg-purple-500/90 backdrop-blur-md text-white rounded-full text-xs font-bold shadow-sm flex items-center gap-1.5">🎉 Event</span>
                                    @else
                                        <span class="px-3 py-1.5 bg-blue-500/90 backdrop-blur-md text-white rounded-full text-xs font-bold shadow-sm flex items-center gap-1.5">📢 Pengumuman</span>
                                    @endif
                                </div>

                                {{-- Status Badge --}}
                                <div class="absolute top-4 right-4">
                                    @if($item->is_active)
                                        <span class="px-2.5 py-1 bg-green-100/90 backdrop-blur-md text-green-700 rounded-full text-xs font-bold shadow-sm border border-green-200">Aktif</span>
                                    @else
                                        <span class="px-2.5 py-1 bg-gray-100/90 backdrop-blur-md text-gray-600 rounded-full text-xs font-bold shadow-sm border border-gray-200">Nonaktif</span>
                                    @endif
                                </div>
                            </div>

                            {{-- Content --}}
                            <div class="p-6 flex-1 flex flex-col">
                                <div class="flex items-center gap-2 text-xs font-semibold text-gray-500 mb-3 uppercase tracking-wider">
                                    <span class="text-blue-500 flex items-center gap-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                        {{ $item->created_at->format('d M Y') }}
                                    </span>
                                    <span>•</span>
                                    <span class="text-gray-500 truncate flex items-center gap-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                        {{ $item->region->name ?? 'Semua Wilayah' }}
                                    </span>
                                </div>
                                
                                <h3 class="text-lg font-bold text-gray-900 mb-2 line-clamp-2">{{ $item->title }}</h3>
                                
                                <p class="text-gray-600 line-clamp-2 mb-4 text-sm leading-relaxed">
                                    {{ \Illuminate\Support\Str::limit(strip_tags($item->description), 100) }}
                                </p>

                                @if($item->event_date)
                                <div class="bg-blue-50/50 rounded-xl p-3 mb-4 border border-blue-50">
                                    <div class="flex items-center gap-2 text-blue-700 font-bold text-[10px] mb-1 uppercase tracking-wider">
                                        🗓️ Pelaksanaan
                                    </div>
                                    <div class="text-gray-800 font-semibold text-xs">
                                        {{ $item->event_date->format('d M Y, H:i') }} WIB
                                    </div>
                                </div>
                                @endif
                                
                                <div class="mt-auto pt-4 border-t border-gray-100 flex items-center justify-between">
                                    <div class="text-xs text-gray-500 font-medium">Oleh: {{ $item->admin->name ?? 'Sistem' }}</div>
                                    <div class="flex items-center gap-2">
                                        <button onclick="alert('Fitur Edit sedang disiapkan.')" class="p-2 text-blue-600 bg-blue-50 hover:bg-blue-600 hover:text-white rounded-lg transition-all" title="Edit">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                        </button>
                                        <button onclick="alert('Fitur Hapus sedang disiapkan.')" class="p-2 text-red-600 bg-red-50 hover:bg-red-600 hover:text-white rounded-lg transition-all" title="Hapus">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Pagination -->
                @if($pengumumans->hasPages())
                <div class="mt-10 flex justify-center">
                    {{ $pengumumans->appends(request()->query())->links() }}
                </div>
                @endif
            @else
                <div class="backdrop-blur-sm bg-white/70 rounded-3xl text-center border border-white/80 shadow-lg py-16 px-6">
                    <div class="w-20 h-20 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-5 border border-gray-100">
                        <svg class="h-10 w-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"></path></svg>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-800 mb-2">Belum ada pengumuman</h3>
                    <p class="text-gray-600 mb-6 max-w-md mx-auto">Saat ini belum ada pengumuman atau event yang diterbitkan di wilayah Anda.</p>
                    <button @click.prevent="isModalOpen = true" class="inline-flex items-center justify-center px-6 py-3 rounded-full bg-blue-500 text-white font-medium hover:bg-blue-600 shadow-md transition-all">
                        Buat Pengumuman Baru
                    </button>
                </div>
            @endif
            </div>

            <!-- Modal Buat Pengumuman -->
            <div x-show="isModalOpen" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                    <div x-show="isModalOpen" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-gray-900 bg-opacity-75 transition-opacity backdrop-blur-sm" aria-hidden="true"></div>
                    <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                    <div x-show="isModalOpen" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="inline-block align-bottom bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl w-full border border-gray-100">
                        <form action="{{ route('wilayah.pengumuman.store') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="bg-white px-6 pt-6 pb-6 sm:px-8 sm:pt-8">
                                <div class="flex justify-between items-center mb-6">
                                    <h3 class="text-2xl leading-6 font-bold text-gray-900" id="modal-title">Buat Pengumuman Baru</h3>
                                    <button type="button" @click="isModalOpen = false" class="bg-gray-50 rounded-full p-2 text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none transition-all">
                                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                    </button>
                                </div>
                                
                                <div class="space-y-5">
                                    <!-- Judul -->
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 mb-1">Judul Pengumuman <span class="text-red-500">*</span></label>
                                        <input type="text" name="title" required class="w-full rounded-xl border-gray-200 bg-gray-50/50 focus:bg-white focus:border-blue-500 focus:ring-blue-500 px-4 py-2.5 text-sm transition-colors" placeholder="Contoh: Kerja Bakti Massal">
                                    </div>
                                    
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                        <!-- Kategori -->
                                        <div>
                                            <label class="block text-sm font-semibold text-gray-700 mb-1">Kategori <span class="text-red-500">*</span></label>
                                            <select name="type" required class="w-full rounded-xl border-gray-200 bg-gray-50/50 focus:bg-white focus:border-blue-500 focus:ring-blue-500 px-4 py-2.5 text-sm transition-colors">
                                                <option value="" disabled selected>Pilih Kategori...</option>
                                                <option value="Pengumuman">📢 Pengumuman</option>
                                                <option value="Event">🎉 Event</option>
                                                <option value="Gotong Royong">🤝 Gotong Royong</option>
                                                <option value="Lainnya">📌 Lainnya</option>
                                            </select>
                                        </div>
                                        
                                        <!-- Jangkauan Publikasi -->
                                        <div>
                                            <label class="block text-sm font-semibold text-gray-700 mb-1">Jangkauan Publikasi <span class="text-red-500">*</span></label>
                                            <select name="target_region_id" required class="w-full rounded-xl border-gray-200 bg-gray-50/50 focus:bg-white focus:border-blue-500 focus:ring-blue-500 px-4 py-2.5 text-sm transition-colors">
                                                @foreach($jangkauanOptions ?? [] as $opt)
                                                    <option value="{{ $opt['id'] }}">{{ $opt['label'] }}</option>
                                                @endforeach
                                            </select>
                                            <p class="text-[10px] text-gray-500 mt-1">Pilih kepada siapa pengumuman ini disebarkan.</p>
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                        <!-- Tanggal Pelaksanaan -->
                                        <div>
                                            <label class="block text-sm font-semibold text-gray-700 mb-1">Tanggal Pelaksanaan <span class="text-gray-400 font-normal">(Opsional)</span></label>
                                            <input type="datetime-local" name="event_date" class="w-full rounded-xl border-gray-200 bg-gray-50/50 focus:bg-white focus:border-blue-500 focus:ring-blue-500 px-4 py-2.5 text-sm transition-colors">
                                        </div>
                                        
                                        <!-- Lokasi -->
                                        <div>
                                            <label class="block text-sm font-semibold text-gray-700 mb-1">Lokasi Tempat <span class="text-gray-400 font-normal">(Opsional)</span></label>
                                            <input type="text" name="location" placeholder="Contoh: Balai Desa, Lapangan RT 01" class="w-full rounded-xl border-gray-200 bg-gray-50/50 focus:bg-white focus:border-blue-500 focus:ring-blue-500 px-4 py-2.5 text-sm transition-colors">
                                        </div>
                                    </div>

                                    <!-- Deskripsi -->
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 mb-1">Isi Pengumuman / Deskripsi <span class="text-red-500">*</span></label>
                                        <textarea name="description" rows="4" required class="w-full rounded-xl border-gray-200 bg-gray-50/50 focus:bg-white focus:border-blue-500 focus:ring-blue-500 px-4 py-3 text-sm transition-colors" placeholder="Tuliskan isi pengumuman secara detail..."></textarea>
                                    </div>

                                    <!-- Upload Foto -->
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 mb-1">Unggah Gambar/Poster <span class="text-gray-400 font-normal">(Opsional)</span></label>
                                        <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-2xl hover:border-blue-400 transition-colors group bg-gray-50/30">
                                            <div class="space-y-1 text-center">
                                                <svg class="mx-auto h-10 w-10 text-gray-400 group-hover:text-blue-500 transition-colors" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
                                                    <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                                </svg>
                                                <div class="flex text-sm text-gray-600 justify-center">
                                                    <label for="image-upload" class="relative cursor-pointer bg-white rounded-md font-medium text-blue-600 hover:text-blue-500 focus-within:outline-none">
                                                        <span>Pilih File Gambar</span>
                                                        <input id="image-upload" name="image" type="file" class="sr-only" accept="image/jpeg,image/png,image/jpg">
                                                    </label>
                                                </div>
                                                <p class="text-xs text-gray-500">PNG, JPG maksimal 2MB</p>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>
                            <div class="bg-gray-50/80 px-6 py-5 sm:px-8 sm:flex sm:flex-row-reverse border-t border-gray-100">
                                <button type="submit" class="w-full inline-flex justify-center rounded-xl border border-transparent shadow-sm px-6 py-2.5 bg-blue-600 text-base font-semibold text-white hover:bg-blue-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm transition-all">
                                    Publikasikan
                                </button>
                                <button type="button" @click="isModalOpen = false" class="mt-3 w-full inline-flex justify-center rounded-xl border border-gray-300 shadow-sm px-6 py-2.5 bg-white text-base font-semibold text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm transition-all">
                                    Batal
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

        </div>
    </section>
</main>
@endsection

@push('scripts')
<script>
    (() => {
        const registerPengumumanAdmin = () => {
            if (!window.Alpine) return;
            window.Alpine.data('pengumumanAdmin', () => ({
                type: '{{ request('type', '') }}',
                search: '{{ request('search', '') }}',
                loading: false,
                isModalOpen: false,

                init() {
                    const container = document.getElementById('pengumuman-list-container');
                    if (container) {
                        container.addEventListener('click', (e) => {
                            let link = e.target.closest('a');
                            if (link && link.href && link.href.includes('page=')) {
                                e.preventDefault();
                                this.fetchData(link.href);
                                window.scrollTo({ top: 100, behavior: 'smooth' });
                            }
                        });
                    }
                },

                updateFilter(newType) {
                    this.type = newType;
                    this.fetchData();
                },

                fetchData(urlOverride = null) {
                    this.loading = true;
                    
                    let url;
                    if (urlOverride) {
                        url = new URL(urlOverride);
                    } else {
                        url = new URL(window.location.origin + window.location.pathname);
                        if (this.type) url.searchParams.append('type', this.type);
                        if (this.search) url.searchParams.append('search', this.search);
                    }

                    fetch(url, {
                        headers: { 'X-Requested-With': 'XMLHttpRequest' }
                    })
                    .then(res => {
                        if (res.redirected && res.url.includes('/login')) {
                            window.location.href = res.url;
                            return null;
                        }
                        if (res.status === 401 || res.status === 419) {
                            window.location.reload();
                            return null;
                        }
                        return res.text();
                    })
                    .then(html => {
                        if (!html) return;
                        let parser = new DOMParser();
                        let doc = parser.parseFromString(html, 'text/html');
                        
                        let newContainer = doc.querySelector('#pengumuman-list-container');
                        if (newContainer) {
                            document.querySelector('#pengumuman-list-container').innerHTML = newContainer.innerHTML;
                        }
                        
                        window.history.pushState({}, '', url);
                        this.loading = false;
                    })
                    .catch(err => {
                        console.error(err);
                        this.loading = false;
                    });
                }
            }));
        };

        if (window.Alpine) {
            registerPengumumanAdmin();
        } else {
            document.addEventListener('alpine:init', registerPengumumanAdmin);
        }

        document.addEventListener('livewire:navigated', () => {
            if (window.Alpine) {
                registerPengumumanAdmin();
            }
        });
    })();
</script>
@endpush
