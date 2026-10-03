@extends('layouts.user')

@section('title', 'Unit Penjualan Gas - SiladesBeng')

@section('page')
<main class="flex-grow relative w-full">
    <section class="relative z-10 min-h-screen pt-32 pb-16">
        <div class="max-w-7xl mx-auto px-6 relative z-10">
            <!-- Header Section -->
            <div class="text-center mb-12 mt-12">
                <h1 class="text-3xl md:text-4xl font-bold mb-4">
                    <span class="text-gray-800">Unit </span>
                    <span class="bg-gradient-to-r from-[#115789] to-[#60a5fa] bg-clip-text text-transparent">Penjualan Gas</span>
                </h1>
                @if(isset($targetRegion) && $targetRegion)
                <div class="inline-flex items-center gap-2 px-4 py-1.5 bg-blue-50 border border-blue-200 rounded-full text-blue-700 text-sm font-semibold shadow-sm">
                    <i class="bx bx-map-pin"></i> Wilayah: {{ $targetRegion->name }}
                </div>
                @endif
            </div>

            @if($filterRegions->isNotEmpty())
                @include('users.partials.catalog-region-filter', ['catalogRoute' => 'gas.sales', 'filterLabel' => 'Filter wilayah gas'])
            @endif

            <!-- Category Filter -->
            @php
                $categories = $items->pluck('kategori')->filter()->unique()->values();
            @endphp
            
            @if($categories->count() > 0)
            <div class="flex flex-wrap justify-center gap-3 mb-10 max-w-4xl mx-auto px-4">
                <button class="catalog-filter-btn filter-btn active" data-filter="all">
                    Semua
                </button>
                @foreach($categories as $category)
                <button class="catalog-filter-btn filter-btn" data-filter="{{ Str::slug($category) }}">
                    {{ ucfirst(str_replace('-', ' ', $category)) }}
                </button>
                @endforeach
            </div>
            @endif

            <!-- Grid Kartu Produk (2 Kolom di Mobile, 2 di Tablet, 3 di Desktop) -->
            @if($items->count() > 0)
                <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-5 lg:gap-6 mb-12 sm:mb-16 max-w-6xl mx-auto">
                    @foreach($items as $item)
                    <a href="{{ route('gas.sales.show', $item->id) }}" data-turbo="false" class="block group product-item transition-all duration-500" data-category="{{ $item->kategori ? Str::slug($item->kategori) : '' }}">
                    <div class="product-card p-3 sm:p-5">

                        <!-- Gambar Produk -->
                        <div class="product-image-wrapper mb-3 sm:mb-4">
                            <img src="{{ asset('storage/' . $item->foto) }}" 
                                 alt="{{ $item->jenis_gas }}"
                                 loading="lazy"
                                 onerror="this.onerror=null; this.src='{{ asset('User/img/elemen/gas_melon.png') }}';"
                                  class="product-image drop-shadow-sm">
                            
                            <!-- Status Badge -->
                            @if($item->stok > 0)
                                <div class="absolute top-2 right-2 sm:top-4 sm:right-4 px-1.5 sm:px-3 py-0.5 sm:py-1.5 text-[8px] sm:text-[10px] font-bold rounded-full bg-green-500 text-white shadow-md flex items-center gap-0.5 sm:gap-1 tracking-wider uppercase">
                                    <svg class="w-2.5 h-2.5 sm:w-3 sm:h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    <span class="hidden xs:inline sm:inline">Tersedia</span>
                                </div>
                            @else
                                <div class="absolute top-2 right-2 sm:top-4 sm:right-4 px-1.5 sm:px-3 py-0.5 sm:py-1.5 text-[8px] sm:text-[10px] font-bold rounded-full bg-red-500 text-white shadow-md flex items-center gap-0.5 sm:gap-1 tracking-wider uppercase">
                                    <svg class="w-2.5 h-2.5 sm:w-3 sm:h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                    <span class="hidden xs:inline sm:inline">Habis</span>
                                </div>
                            @endif
                        </div>

                        <!-- Info Produk -->
                        <div class="product-info flex flex-col flex-1 px-0.5 sm:px-1">
                            <!-- Kategori -->
                            @if($item->kategori)
                                <div class="mb-1.5 sm:mb-4">
                                    <span class="inline-flex items-center px-2 sm:px-3 py-0.5 sm:py-1.5 rounded-md text-[9px] sm:text-[10px] font-bold text-white bg-blue-600 shadow-sm">
                                        {{ ucfirst(str_replace('-', ' ', $item->kategori)) }}
                                    </span>
                                </div>
                            @endif

                            <h3 class="product-name text-xs sm:text-base font-bold text-gray-800 mb-1 sm:mb-2 line-clamp-2 group-hover:text-[#115789] transition-colors mt-0">
                                {{ $item->jenis_gas }}
                            </h3>
                            
                            <div class="mt-auto pt-3 flex items-end justify-between gap-2 border-t border-slate-100">
                                <div class="flex flex-col">
                                    <span class="text-[10px] sm:text-xs text-gray-500 mb-0.5 font-medium">Harga</span>
                                    <p class="text-gray-900 font-bold text-xs sm:text-xl tracking-tight leading-none">
                                        Rp {{ number_format($item->harga_satuan, 0, ',', '.') }}<span class="text-[9px] sm:text-xs text-gray-400 font-medium tracking-normal ml-0.5">/{{ $item->satuan ?? 'Unit' }}</span>
                                    </p>
                                </div>
                                <div class="text-right flex flex-col">
                                    <span class="text-[10px] sm:text-xs text-gray-400 mb-0.5 font-medium">Stok</span>
                                    <p class="text-xs sm:text-base font-bold {{ $item->stok > 0 ? 'text-gray-800' : 'text-red-500' }} leading-none">
                                        {{ $item->stok }}
                                    </p>
                                </div>
                            </div>
                            <span class="catalog-card-cta bg-orange-600">Pesan Gas</span>
                        </div>
                    </div>
                    </a>
                    @endforeach
                </div>
            @else
                <!-- Kondisi Kosong (Format persis seperti Penyewaan Alat) -->
                <div class="text-center py-20">
                    <svg class="w-24 h-24 mx-auto mb-6 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                    </svg>
                    <h3 class="text-xl font-semibold text-gray-700 mb-2">Belum Ada Produk Gas Tersedia</h3>
                    <p class="text-gray-500">Mohon menunggu, stok produk gas akan segera diperbarui dalam waktu dekat.</p>
                </div>
            @endif
        </div>
    </section>

@if(isset($isGasCrisis) && $isGasCrisis)
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 mt-8 mb-12">
    @if($pendingKk)
    <div class="bg-yellow-50 border border-yellow-200 rounded-3xl p-6 md:p-8 shadow-lg text-center relative overflow-hidden">
        <div class="absolute top-0 left-0 w-full h-1 bg-yellow-500"></div>
        <i class='bx bx-time-five text-yellow-500 text-5xl mb-4'></i>
        <h2 class="text-2xl font-bold text-gray-900 mb-2">Pembaruan KK Sedang Diproses</h2>
        <p class="text-gray-600 mb-4 text-sm md:text-base">
            Foto Kartu Keluarga (KK) yang Anda unggah sedang dalam proses verifikasi oleh Admin Desa. <br>
            Silakan tunggu hingga proses ini disetujui untuk dapat memesan Gas Daerah.
        </p>
    </div>
    @elseif(!$hasKk)
    <div class="bg-white rounded-3xl p-6 md:p-8 shadow-xl border border-red-100 relative overflow-hidden">
        <div class="absolute top-0 left-0 w-full h-2 bg-gradient-to-r from-red-500 to-orange-500"></div>
        <div class="flex flex-col items-center text-center">
            <div class="w-16 h-16 bg-red-100 text-red-500 rounded-full flex items-center justify-center text-3xl mb-4">
                <i class='bx bx-error'></i>
            </div>
            <h2 class="text-2xl font-black text-gray-900 mb-1">STATUS DESA: KRISIS GAS</h2>
            <p class="text-red-600 font-semibold mb-6">Pembelian dibatasi 1 Tabung per Keluarga.</p>
            
            <p class="text-gray-700 mb-6 max-w-lg">
                Untuk melanjutkan pemesanan, kami perlu memverifikasi Kartu Keluarga (KK) Anda. 
            </p>

            <div class="bg-blue-50 border border-blue-100 rounded-xl p-4 mb-8 max-w-lg text-left w-full flex gap-3 items-start">
                <i class='bx bx-shield-quarter text-blue-500 text-xl mt-0.5'></i>
                <p class="text-xs md:text-sm text-blue-800 leading-relaxed">
                    <strong>Info Privasi:</strong> Demi keamanan Anda, foto KK tidak akan disimpan oleh sistem setelah diverifikasi oleh Pemerintah Desa.
                </p>
            </div>

            <button type="button" onclick="openKkModal()" class="w-full max-w-xs bg-gradient-to-r from-red-500 to-orange-500 hover:from-red-600 hover:to-orange-600 text-white font-bold py-3 px-8 rounded-xl shadow-lg transition-all hover:scale-105 flex items-center justify-center gap-2">
                <i class='bx bx-scan'></i> SCAN KARTU KELUARGA
            </button>
        </div>
    </div>
    @else
    <div class="bg-white rounded-3xl p-6 md:p-8 shadow-xl border border-green-100 relative overflow-hidden">
        <div class="absolute top-0 left-0 w-full h-2 bg-gradient-to-r from-green-500 to-emerald-500"></div>
        <div class="flex flex-col items-center text-center">
            <div class="w-16 h-16 bg-green-100 text-green-500 rounded-full flex items-center justify-center text-3xl mb-4">
                <i class='bx bx-check-circle'></i>
            </div>
            <h2 class="text-xl md:text-2xl font-bold text-gray-900 mb-1">Data KK Anda Sudah Terdaftar</h2>
            <p class="text-gray-500 font-mono bg-gray-100 px-4 py-1 rounded-full text-sm mb-6">(No. KK: {{ $familyCardNumber }})</p>
            
            <p class="text-gray-700 mb-4 max-w-lg">
                Jika susunan anggota keluarga Anda tidak mengalami perubahan dan sesuai dengan data anggota keluarga yang Anda masukkan sebelumnya, silakan lanjutkan pemesanan gas.
            </p>
            <p class="text-gray-700 mb-8 max-w-lg font-medium text-orange-600">
                Namun, jika terdapat perubahan data pada Kartu Keluarga Anda, mohon perbarui data terlebih dahulu.
            </p>

            <div class="flex flex-col sm:flex-row gap-4 w-full max-w-lg justify-center">
                <button type="button" onclick="document.getElementById('katalog-gas').scrollIntoView({behavior:'smooth'})" class="flex-1 bg-green-500 hover:bg-green-600 text-white font-bold py-3 px-6 rounded-xl shadow-lg transition-all hover:-translate-y-1">
                    LANJUTKAN PESAN GAS
                    <span class="block text-xs font-normal opacity-80">(Data Lama)</span>
                </button>
                <button type="button" onclick="openKkModal()" class="flex-1 bg-white hover:bg-gray-50 text-gray-800 border-2 border-gray-200 font-bold py-3 px-6 rounded-xl shadow-sm transition-all hover:-translate-y-1">
                    PERBARUI KK
                    <span class="block text-xs font-normal text-gray-500">(Terdapat Perubahan)</span>
                </button>
            </div>
        </div>
    </div>
    @endif
</div>

<!-- Modal Scan KK -->
<div id="kkModal" class="fixed inset-0 z-[100] hidden items-center justify-center">
    <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" onclick="closeKkModal()"></div>
    <div class="bg-white rounded-3xl w-full max-w-md mx-4 relative z-10 overflow-hidden shadow-2xl animate-fade-up">
        <div class="p-6 border-b border-gray-100 flex justify-between items-center bg-gray-50">
            <h3 class="text-lg font-bold text-gray-800">Verifikasi Kartu Keluarga</h3>
            <button type="button" onclick="closeKkModal()" class="text-gray-400 hover:text-red-500 transition-colors">
                <i class='bx bx-x text-2xl'></i>
            </button>
        </div>
        <form action="{{ route('user.gas.verify-kk') }}" method="POST" enctype="multipart/form-data" class="p-6">
            @csrf
            <div class="mb-6 text-center">
                <div class="w-20 h-20 bg-orange-100 text-orange-500 rounded-2xl mx-auto flex items-center justify-center text-4xl mb-4">
                    <i class='bx bx-id-card'></i>
                </div>
                <p class="text-sm text-gray-600">Ambil foto Kartu Keluarga (KK) asli secara jelas. Pastikan NIK anggota keluarga dapat terbaca oleh sistem kami.</p>
            </div>
            
            <div class="mb-6">
                <label class="block text-sm font-bold text-gray-800 mb-2">Foto Kartu Keluarga</label>
                <input type="file" name="kk_image" accept="image/*" capture="environment" class="w-full bg-gray-50 border border-gray-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-orange-500 focus:border-orange-500" required>
            </div>

            <button type="submit" class="w-full bg-orange-500 hover:bg-orange-600 text-white font-bold py-3 px-6 rounded-xl shadow-lg transition-all hover:-translate-y-1 flex items-center justify-center gap-2">
                <i class='bx bx-upload'></i> KIRIM & VERIFIKASI
            </button>
        </form>
    </div>
</div>
<script>
    function openKkModal() {
        document.getElementById('kkModal').classList.remove('hidden');
        document.getElementById('kkModal').classList.add('flex');
    }
    function closeKkModal() {
        document.getElementById('kkModal').classList.add('hidden');
        document.getElementById('kkModal').classList.remove('flex');
    }
</script>
@endif
@include('users.partials.service_chat_widget', ['serviceType' => 'gas', 'serviceTitle' => 'Layanan Gas'])

</main>
@endsection

@push('styles')
@include('users.partials.catalog-ui-styles')
<style>
    * { font-family: 'Inter', sans-serif; }
</style>
@endpush

@push('scripts')
<script>
    // Gulir halus ke atas saat halaman dimuat
    window.scrollTo({ top: 0, behavior: 'smooth' });

    // Tambahkan status loading untuk gambar
    document.addEventListener('DOMContentLoaded', () => {
        const images = document.querySelectorAll('.product-image');
        images.forEach(img => img.addEventListener('error', function () {
            this.classList.add('image-fallback');
        }, { once: true }));

        // Filter Logic with State Persistence
        const filterBtns = document.querySelectorAll('.filter-btn');
        const productItems = document.querySelectorAll('.product-item');
        
        // Gunakan path URL untuk membedakan state antar halaman
        const storageKey = 'filter_' + window.location.pathname;

        function applyFilter(filterValue) {
            filterBtns.forEach(btn => {
                btn.classList.toggle('active', btn.getAttribute('data-filter') === filterValue);
            });

            // Update items display with smooth opacity
            productItems.forEach(item => {
                // Disable transition temporarily to prevent weird jumping
                item.style.transition = 'none';
                
                if (filterValue === 'all' || item.getAttribute('data-category') === filterValue) {
                    item.style.display = 'block';
                    item.style.opacity = '0';
                    // Force reflow
                    void item.offsetWidth; 
                    // Re-enable transition and fade in
                    item.style.transition = 'opacity 0.4s ease-out, transform 0.3s ease';
                    item.style.opacity = '1';
                } else {
                    item.style.display = 'none';
                    item.style.opacity = '0';
                }
            });
            
            // Simpan state pilihan terakhir
            sessionStorage.setItem(storageKey, filterValue);
        }

        // Initialize state saat halaman dimuat (BfCache / Back button support)
        const savedFilter = sessionStorage.getItem(storageKey) || 'all';
        applyFilter(savedFilter);

        // Click handlers
        filterBtns.forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault(); // Mencegah default behavior
                const filterValue = btn.getAttribute('data-filter');
                applyFilter(filterValue);
            });
        });
    });
</script>
@endpush
