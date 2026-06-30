@extends('layouts.user')

@section('title', 'Kelola Laporan Warga - SilaDesBeng')

@push('styles')
<style>
    .stat-value {
        font-size: clamp(2.5rem, 4vw, 3.5rem); font-weight: 900;
        -webkit-background-clip: text; -webkit-text-fill-color: transparent;
        background-clip: text; margin-bottom: 12px;
    }
    .stat-value.blue { background-image: linear-gradient(to right, #60a5fa, #2563eb); }
    .stat-value.yellow { background-image: linear-gradient(to right, #facc15, #f97316); }
    .stat-value.purple { background-image: linear-gradient(to right, #c084fc, #ec4899); }
    .stat-value.green { background-image: linear-gradient(to right, #4ade80, #059669); }
    .stat-value.red { background-image: linear-gradient(to right, #f87171, #e11d48); }

    .stat-label { color: #4b5563; font-size: 1rem; font-weight: 700; letter-spacing: 0.05em; margin-bottom: 16px; }
    
    .stat-bar { height: 8px; background: rgba(0,0,0,0.08); border-radius: 9999px; overflow: hidden; width: 100%; margin-top: auto; }
    .stat-bar-fill { height: 100%; border-radius: 9999px; width: 100%; }
    .stat-bar-fill.blue { background: linear-gradient(to right, #60a5fa, #2563eb); }
    .stat-bar-fill.yellow { background: linear-gradient(to right, #facc15, #f97316); }
    .stat-bar-fill.purple { background: linear-gradient(to right, #c084fc, #ec4899); }
    .stat-bar-fill.green { background: linear-gradient(to right, #4ade80, #059669); }
    .stat-bar-fill.red { background: linear-gradient(to right, #f87171, #e11d48); }
</style>
@endpush

@section('page')
<main class="flex-grow relative w-full min-h-screen">
    {{-- Custom Vector Abstract Background --}}
    @include('partials.abstract-bg')

    <section class="relative z-10 pt-32 pb-16">
        <div class="max-w-7xl mx-auto px-6" x-data="laporanWarga()">
            <!-- Header Section -->
            <div class="text-center mb-12 animate-section">
                <h1 class="text-4xl md:text-5xl font-bold mb-4">
                    <span class="bg-gradient-to-r from-gray-800 to-gray-600 bg-clip-text text-transparent">Kelola </span>
                    <span class="bg-gradient-to-r from-[#115789] to-[#60a5fa] bg-clip-text text-transparent">Laporan Warga</span>
                </h1>
                <p class="text-gray-700 text-lg mt-2 mb-6">
                    Pemantauan dan manajemen laporan warga di wilayah Anda.
                </p>
            </div>

            <!-- Statistik -->
            <!-- Statistik -->
            <div class="max-w-5xl mx-auto grid grid-cols-2 md:grid-cols-3 gap-6 mb-12 animate-section">
                <!-- Total -->
                <div class="backdrop-blur-sm bg-white/70 rounded-3xl border border-white/80 p-6 flex flex-col items-center justify-center shadow-sm hover:shadow-md transition-all">
                    <div class="w-12 h-12 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center mb-4">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                    </div>
                    <div class="stat-value blue">{{ $stats['total_laporan'] ?? 0 }}</div>
                    <div class="stat-label">Total Laporan</div>
                    <div class="stat-bar"><div class="stat-bar-fill blue"></div></div>
                </div>
                <!-- Pending -->
                <div class="backdrop-blur-sm bg-white/70 rounded-3xl border border-white/80 p-6 flex flex-col items-center justify-center shadow-sm hover:shadow-md transition-all">
                    <div class="w-12 h-12 rounded-full bg-yellow-50 text-yellow-600 flex items-center justify-center mb-4">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <div class="stat-value yellow">{{ $stats['pending'] ?? 0 }}</div>
                    <div class="stat-label">Tertunda</div>
                    <div class="stat-bar"><div class="stat-bar-fill yellow"></div></div>
                </div>
                <!-- Proses -->
                <div class="backdrop-blur-sm bg-white/70 rounded-3xl border border-white/80 p-6 flex flex-col items-center justify-center shadow-sm hover:shadow-md transition-all">
                    <div class="w-12 h-12 rounded-full bg-indigo-50 text-indigo-500 flex items-center justify-center mb-4">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                    </div>
                    <div class="stat-value purple">{{ $stats['proses'] ?? 0 }}</div>
                    <div class="stat-label">Proses</div>
                    <div class="stat-bar"><div class="stat-bar-fill purple"></div></div>
                </div>
                <!-- Selesai -->
                <div class="backdrop-blur-sm bg-white/70 rounded-3xl border border-white/80 p-6 flex flex-col items-center justify-center shadow-sm hover:shadow-md transition-all">
                    <div class="w-12 h-12 rounded-full bg-green-50 text-green-500 flex items-center justify-center mb-4">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <div class="stat-value green">{{ $stats['selesai'] ?? 0 }}</div>
                    <div class="stat-label">Selesai</div>
                    <div class="stat-bar"><div class="stat-bar-fill green"></div></div>
                </div>
                <!-- Ditolak -->
                <div class="backdrop-blur-sm bg-white/70 rounded-3xl border border-white/80 p-6 flex flex-col items-center justify-center shadow-sm hover:shadow-md transition-all">
                    <div class="w-12 h-12 rounded-full bg-red-50 text-red-400 flex items-center justify-center mb-4">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <div class="stat-value red">{{ $stats['ditolak'] ?? 0 }}</div>
                    <div class="stat-label">Ditolak</div>
                    <div class="stat-bar"><div class="stat-bar-fill red"></div></div>
                </div>
            </div>

            <!-- Filter & Search Bar -->
            <div class="max-w-5xl mx-auto mb-12 animate-section">
                <div class="backdrop-blur-sm bg-white/70 rounded-3xl p-4 md:p-6 border border-white/80 shadow-lg">
                    <div class="flex flex-col lg:flex-row gap-6 justify-between items-center w-full">
                        
                        {{-- Filter Pills --}}
                        <div class="flex flex-nowrap overflow-x-auto gap-2 w-full lg:flex-1 justify-start pb-1 filter-scroll" style="scrollbar-width: none; -ms-overflow-style: none;">
                            <style>
                                .filter-scroll::-webkit-scrollbar { display: none; }
                            </style>
                            <button type="button" @click.prevent="updateFilter('')" 
                               :class="!status ? 'bg-blue-500 text-white border-blue-500 shadow-md' : 'bg-white text-gray-600 border-gray-200 hover:border-blue-300 hover:text-blue-500'"
                               class="px-4 py-2 rounded-full font-semibold text-sm transition-all duration-300 border-2 focus:outline-none whitespace-nowrap flex-shrink-0">
                               Semua
                            </button>
                            <button type="button" @click.prevent="updateFilter('Pending')" 
                               :class="status === 'Pending' ? 'bg-blue-500 text-white border-blue-500 shadow-md' : 'bg-white text-gray-600 border-gray-200 hover:border-blue-300 hover:text-blue-500'"
                               class="px-4 py-2 rounded-full font-semibold text-sm transition-all duration-300 border-2 focus:outline-none whitespace-nowrap flex-shrink-0">
                               Pending
                            </button>
                            <button type="button" @click.prevent="updateFilter('Proses')" 
                               :class="status === 'Proses' ? 'bg-blue-500 text-white border-blue-500 shadow-md' : 'bg-white text-gray-600 border-gray-200 hover:border-blue-300 hover:text-blue-500'"
                               class="px-4 py-2 rounded-full font-semibold text-sm transition-all duration-300 border-2 focus:outline-none whitespace-nowrap flex-shrink-0">
                               Proses
                            </button>
                            <button type="button" @click.prevent="updateFilter('Selesai')" 
                               :class="status === 'Selesai' ? 'bg-blue-500 text-white border-blue-500 shadow-md' : 'bg-white text-gray-600 border-gray-200 hover:border-blue-300 hover:text-blue-500'"
                               class="px-4 py-2 rounded-full font-semibold text-sm transition-all duration-300 border-2 focus:outline-none whitespace-nowrap flex-shrink-0">
                               Selesai
                            </button>
                            <button type="button" @click.prevent="updateFilter('Ditolak')" 
                               :class="status === 'Ditolak' ? 'bg-blue-500 text-white border-blue-500 shadow-md' : 'bg-white text-gray-600 border-gray-200 hover:border-blue-300 hover:text-blue-500'"
                               class="px-4 py-2 rounded-full font-semibold text-sm transition-all duration-300 border-2 focus:outline-none whitespace-nowrap flex-shrink-0">
                               Ditolak
                            </button>
                        </div>

                        {{-- Search Input & Kategori Select --}}
                        <div class="w-full lg:w-fit flex items-center justify-end">
                            <!-- Search Input -->
                            <div class="w-full sm:w-72 relative group">
                                <div class="absolute -inset-0.5 bg-gradient-to-r from-blue-500 via-blue-400 to-amber-400 rounded-full opacity-70 group-hover:opacity-100 transition-opacity duration-300"></div>
                                <div class="relative flex items-center bg-white rounded-full overflow-hidden">
                                    <input type="text" x-model="search" @input.debounce.500ms="fetchData()" placeholder="Cari laporan..." 
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

            <div id="laporan-list-container" class="transition-all duration-300" :class="{ 'opacity-50 pointer-events-none scale-[0.98]': loading }">
            <!-- Table Laporan -->
            <div class="bg-white/80 backdrop-blur-md rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50/80">
                            <tr>
                                <th scope="col" class="px-8 py-5 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">ID & Pelapor</th>
                                <th scope="col" class="px-6 py-5 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Kategori & Lokasi</th>
                                <th scope="col" class="px-6 py-5 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Tanggal</th>
                                <th scope="col" class="px-6 py-5 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Status</th>
                                <th scope="col" class="px-8 py-5 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($laporans as $laporan)
                            <tr class="hover:bg-blue-50/50 transition-colors">
                                <td class="px-8 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10 rounded-full bg-gradient-to-br from-blue-100 to-blue-200 flex items-center justify-center text-blue-700 font-bold shadow-sm border border-blue-200">
                                            {{ substr($laporan->nama, 0, 1) }}
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-bold text-gray-900">#{{ str_pad($laporan->id, 5, '0', STR_PAD_LEFT) }}</div>
                                            <div class="text-sm text-gray-500 font-medium">{{ $laporan->nama }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-900 font-semibold">{{ $laporan->kategori }}</div>
                                    <div class="text-sm text-gray-500 mt-0.5 flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                        {{ \Illuminate\Support\Str::limit($laporan->lokasi, 30) }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900 font-medium">{{ $laporan->created_at->format('d M Y') }}</div>
                                    <div class="text-sm text-gray-500">{{ $laporan->created_at->format('H:i') }} WIB</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($laporan->status == 'Pending')
                                        <span class="px-3 py-1.5 inline-flex text-xs leading-5 font-semibold rounded-lg bg-yellow-50 text-yellow-700 border border-yellow-200">Menunggu</span>
                                    @elseif($laporan->status == 'Proses')
                                        <span class="px-3 py-1.5 inline-flex text-xs leading-5 font-semibold rounded-lg bg-blue-50 text-blue-700 border border-blue-200">Diproses</span>
                                    @elseif($laporan->status == 'Selesai')
                                        <span class="px-3 py-1.5 inline-flex text-xs leading-5 font-semibold rounded-lg bg-green-50 text-green-700 border border-green-200">Selesai</span>
                                    @else
                                        <span class="px-3 py-1.5 inline-flex text-xs leading-5 font-semibold rounded-lg bg-red-50 text-red-700 border border-red-200">Ditolak</span>
                                    @endif
                                </td>
                                <td class="px-8 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <button onclick="alert('Fitur detail sedang disiapkan.')" class="inline-flex items-center justify-center px-3 py-2 border border-blue-200 text-blue-600 bg-blue-50 hover:bg-blue-600 hover:text-white rounded-lg transition-all text-xs font-semibold shadow-sm gap-1.5">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                        Lihat
                                    </button>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center">
                                    <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-4 border border-gray-100">
                                        <svg class="h-8 w-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                                    </div>
                                    <h3 class="text-base font-bold text-gray-900 mb-1">Belum ada laporan</h3>
                                    <p class="text-sm text-gray-500">Saat ini tidak ada laporan warga yang masuk di wilayah Anda.</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            <!-- Pagination -->
            @if($laporans->hasPages())
            <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/50">
                {{ $laporans->appends(request()->query())->links() }}
            </div>
            @endif
            </div>

            </div>

        </div>
    </section>
</main>
@endsection

@push('scripts')
<script>
    (() => {
        const registerLaporanWarga = () => {
            if (!window.Alpine) return;
            window.Alpine.data('laporanWarga', () => ({
                status: '{{ request('status', '') }}',
                search: '{{ request('search', '') }}',
                loading: false,

                init() {
                    const container = document.getElementById('laporan-list-container');
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

                updateFilter(newStatus) {
                    this.status = newStatus;
                    this.fetchData();
                },

                fetchData(urlOverride = null) {
                    this.loading = true;
                    
                    let url;
                    if (urlOverride) {
                        url = new URL(urlOverride);
                    } else {
                        url = new URL(window.location.origin + window.location.pathname);
                        if (this.status) url.searchParams.append('status', this.status);
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
                        
                        let newContainer = doc.querySelector('#laporan-list-container');
                        if (newContainer) {
                            document.querySelector('#laporan-list-container').innerHTML = newContainer.innerHTML;
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
            registerLaporanWarga();
        } else {
            document.addEventListener('alpine:init', registerLaporanWarga);
        }

        document.addEventListener('livewire:navigated', () => {
            if (window.Alpine) {
                registerLaporanWarga();
            }
        });
    })();
</script>
@endpush
