@extends('layouts.user')

@section('page')
@php
    $skemaBiaya = $item->effectivePricingScheme();
    $tarifPerHari = $item->isPaidScheme() ? (float) $item->harga_sewa : 0;
    $tarifFormatted = 'Rp ' . number_format($tarifPerHari, 0, ',', '.');
    $bisaBerbayar = $item->isPaidScheme();
    $sosialBerbayar = $item->isPaidForEvent('sosial');
    $komersialBerbayar = $item->isPaidForEvent('komersial');
@endphp
<main class="flex-grow relative w-full">
    <section class="relative z-10 min-h-screen pt-32 pb-16 bg-cover bg-center bg-no-repeat bg-fixed" 
             style="background-image: url('{{ asset('Admin/img/elements/background1.png') }}');">
        
        <!-- White Overlay -->
        <div class="absolute inset-0 bg-white/25 pointer-events-none"></div>

        <div class="max-w-3xl mx-auto px-6 relative z-20">
            <!-- Header -->
            <div class="text-center mb-12 mt-8">
                <h1 class="text-3xl md:text-4xl font-bold mb-2">
                    <span class="text-gray-800">Form </span>
                    <span class="bg-gradient-to-r from-[#115789] to-[#60a5fa] bg-clip-text text-transparent">Peminjaman Fasilitas Umum</span>
                </h1>
                @if($skemaBiaya === 'berbayar_semua')
                    <p class="text-gray-600">Fasilitas ini dikenakan tarif sewa <strong>{{ $tarifFormatted }} / hari</strong> untuk semua kategori acara.</p>
                @elseif($skemaBiaya === 'berbayar')
                    <p class="text-gray-600">Gratis untuk acara sosial/masyarakat, dan berbayar <strong>{{ $tarifFormatted }} / hari</strong> untuk acara komersial/pribadi.</p>
                @else
                    <p class="text-gray-600">Peminjaman fasilitas ini tidak dipungut biaya (Gratis) untuk acara sosial maupun pribadi.</p>
                @endif
            </div>

            <form id="booking-form" action="#" method="POST" onsubmit="return false;" enctype="multipart/form-data" data-turbo="false">
                @csrf
                <input type="hidden" name="fasilitas_id" value="{{ $item->id }}">

                <div class="bg-white rounded-2xl shadow-lg p-6 mb-6">
                    <div class="flex items-center gap-3 mb-6 border-b pb-4">
                        <img src="{{ asset('storage/' . $item->foto) }}" alt="{{ $item->nama_fasilitas }}" class="w-20 h-20 object-cover rounded-lg flex-shrink-0">
                        <div class="min-w-0">
                            <h3 class="text-xl font-bold text-gray-800">{{ $item->nama_fasilitas }}</h3>
                            <span class="text-sm text-gray-600 block">{{ str_replace('&', 'dan', $item->kategori) }} | Stok: {{ $item->stok }} {{ $item->satuan }}</span>
                            <div class="mt-2">
                                @if($skemaBiaya === 'berbayar_semua')
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                        Tarif Sewa: {{ $tarifFormatted }} / hari (Semua Acara)
                                    </span>
                                @elseif($skemaBiaya === 'berbayar')
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                        Sosial Gratis · Pribadi {{ $tarifFormatted }} / hari
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        Gratis Sepenuhnya
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Kategori Acara -->
                    <div class="mb-6">
                        <label class="block text-sm font-bold text-gray-700 mb-3">Kategori Acara <span class="text-red-500">*</span></label>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <label id="label-acara-sosial" class="border border-gray-300 rounded-lg p-3 cursor-pointer bg-white hover:bg-blue-50 hover:border-blue-300 flex items-center gap-2 transition-colors">
                                <input type="radio" name="jenis_acara" value="sosial" class="w-4 h-4 text-blue-600 focus:ring-blue-500 flex-shrink-0" required>
                                <span class="text-sm font-semibold text-gray-700">
                                    Sosial / Masyarakat ({{ $sosialBerbayar ? $tarifFormatted . ' / hari' : 'Gratis' }})
                                </span>
                            </label>
                            <label id="label-acara-komersial" class="border border-gray-300 rounded-lg p-3 cursor-pointer bg-white hover:bg-blue-50 hover:border-blue-300 flex items-center gap-2 transition-colors">
                                <input type="radio" name="jenis_acara" value="komersial" class="w-4 h-4 text-blue-600 focus:ring-blue-500 flex-shrink-0">
                                <span class="text-sm font-semibold text-gray-700">
                                    Komersial / Pribadi ({{ $komersialBerbayar ? $tarifFormatted . ' / hari' : 'Gratis' }})
                                </span>
                            </label>
                        </div>
                    </div>

                    <!-- Surat Pengantar (Hanya muncul untuk acara Sosial) -->
                    <div id="surat-pengantar-container" class="mb-6 hidden bg-amber-50 border border-amber-200 p-5 rounded-xl">
                        <div class="flex flex-wrap items-center gap-2 mb-1">
                            <span class="text-sm font-bold text-gray-800">Surat Pengantar RT/RW atau Panitia</span>
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-800">Opsional</span>
                        </div>
                        <p class="text-xs text-gray-600 mb-3">
                            Tidak wajib. Jika ada surat keterangan dari RT/RW atau panitia acara, silakan lampirkan agar pengajuan Anda lebih cepat disetujui petugas desa.
                        </p>
                        <x-file-upload
                            name="surat_pengantar"
                            accept=".pdf,.jpg,.jpeg,.png"
                            tone="amber"
                            button-text="Pilih Surat / Foto"
                            empty-text="Belum ada surat dipilih"
                            helper="Format JPG, PNG, atau PDF · Maksimal 5 MB."
                        />
                    </div>

                    <!-- Keterangan / Tujuan Peminjaman -->
                    <div class="mb-6">
                        <label class="block text-sm font-bold text-gray-700 mb-2">Keterangan / Tujuan Peminjaman <span class="text-red-500">*</span></label>
                        <textarea name="rental_purpose" id="rental-purpose" rows="3" placeholder="Contoh: Untuk acara RT, rapat warga, musyawarah desa, senam pagi, dll." class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required></textarea>
                        
                        <div class="mt-3 flex items-center gap-2">
                            <input type="checkbox" name="butuh_gudang" id="butuh_gudang" value="1" class="w-4 h-4 text-blue-600 rounded border-gray-300 focus:ring-blue-500">
                            <label for="butuh_gudang" class="text-sm text-gray-700 font-medium cursor-pointer">Saya juga membutuhkan kunci akses fasilitas pendukung/gudang (Opsional)</label>
                        </div>
                    </div>

                    <!-- Jumlah -->
                    <div class="mb-6">
                        <label class="block text-sm font-bold text-gray-700 mb-2">Jumlah Pinjam <span class="text-red-500">*</span></label>
                        <div class="flex items-center gap-2 border border-gray-300 rounded-lg px-4 py-2 w-32">
                            <button type="button" id="decrease-qty" class="text-gray-600 hover:text-gray-800 focus:outline-none">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/></svg>
                            </button>
                            <input type="number" name="quantity" id="quantity" value="{{ $quantity }}" min="1" max="{{ $item->stok }}" class="w-full text-center border-0 focus:outline-none focus:ring-0 p-0 text-lg font-semibold bg-transparent" readonly required>
                            <button type="button" id="increase-qty" class="text-gray-600 hover:text-gray-800 focus:outline-none">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            </button>
                        </div>
                    </div>

                    <!-- Waktu Penyewaan -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Tanggal Mulai <span class="text-red-500">*</span></label>
                            <input type="date" name="start_date" id="start-date" min="{{ date('Y-m-d') }}" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Tanggal Selesai <span class="text-red-500">*</span></label>
                            <input type="date" name="end_date" id="end-date" min="{{ date('Y-m-d') }}" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                        </div>
                    </div>

                    <!-- Ringkasan Biaya Peminjaman -->
                    <div id="ringkasan-biaya-fasilitas" class="rounded-xl border {{ $bisaBerbayar ? 'bg-slate-50 border-slate-200' : 'bg-emerald-50 border-emerald-200' }} p-4 flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <div class="text-xs font-bold uppercase tracking-wider {{ $bisaBerbayar ? 'text-slate-600' : 'text-emerald-700' }}">Ringkasan Biaya Peminjaman</div>
                            <p id="fasilitas-cost-detail" class="text-xs sm:text-sm text-gray-600 mt-0.5 mb-0">
                                @if($skemaBiaya === 'berbayar_semua')
                                    {{ $tarifFormatted }} / hari × 1 hari × {{ $quantity }} unit (pilih tanggal untuk hitung durasi)
                                @elseif($skemaBiaya === 'berbayar')
                                    Pilih Kategori Acara untuk melihat rincian biaya peminjaman.
                                @else
                                    Tidak dipungut biaya (Gratis untuk acara sosial maupun pribadi).
                                @endif
                            </p>
                        </div>
                        <div id="fasilitas-cost-total" class="text-xl sm:text-2xl font-black {{ $bisaBerbayar ? 'text-blue-600' : 'text-emerald-700' }}">
                            @if($skemaBiaya === 'berbayar_semua')
                                Rp {{ number_format($tarifPerHari * $quantity, 0, ',', '.') }}
                            @elseif($skemaBiaya === 'berbayar')
                                -
                            @else
                                Gratis
                            @endif
                        </div>
                    </div>
                    </div>

                    <!-- Metode Penggunaan dan Lokasi Fasilitas (Gedung/Ruang Publik) -->
                    <input type="hidden" name="delivery_method" id="delivery-method-input" value="lokasi">
                    <input type="hidden" name="recipient_name" id="recipient-name" value="{{ Auth::user()->name }}">
                    <input type="hidden" name="delivery_address" id="delivery-address" value="{{ $item->lokasi ?? 'Lokasi Gedung / Fasilitas Umum' }}">

                    <!-- Card Lokasi dan Cara Peminjaman -->
                    <div class="bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200 rounded-2xl p-5 sm:p-6 mb-6">
                        <div class="flex items-start gap-4">
                            <div class="w-12 h-12 rounded-2xl bg-blue-600 text-white flex items-center justify-center flex-shrink-0 shadow-md">
                                <i class="bx bx-buildings text-2xl"></i>
                            </div>
                            <div class="flex-grow">
                                <div class="flex flex-wrap items-center justify-between gap-2 mb-1">
                                    <h4 class="font-bold text-gray-800 text-base sm:text-lg">Lokasi dan Penggunaan Fasilitas</h4>
                                    <span class="px-3 py-1 bg-blue-100 text-blue-700 text-xs font-bold rounded-full border border-blue-200">
                                        Penggunaan di Lokasi
                                    </span>
                                </div>
                                <p class="text-xs sm:text-sm text-gray-600 mb-3">
                                    Peminjaman fasilitas gedung/ruang publik digunakan langsung di lokasi bangunan yang bersangkutan.
                                </p>
                                <div class="flex flex-wrap items-center gap-3 pt-2 border-t border-blue-100/80 text-xs sm:text-sm">
                                    <div class="flex items-center gap-1.5 text-gray-700">
                                        <i class="bx bx-map-pin text-red-500 text-base flex-shrink-0"></i>
                                        <span class="font-semibold">{{ $item->lokasi ?? 'Area Desa / Gedung Serbaguna' }}</span>
                                    </div>
                                    @if($item->latitude && $item->longitude)
                                    <a href="https://www.google.com/maps?q={{ $item->latitude }},{{ $item->longitude }}" target="_blank" class="inline-flex items-center gap-1 text-blue-600 hover:text-blue-700 font-semibold underline text-xs">
                                        <i class="bx bx-navigation"></i> Buka di Google Maps
                                    </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Petugas Kunci dan Penanggung Jawab Gedung -->
                    <div class="bg-white border border-gray-200 rounded-2xl p-5 sm:p-6 mb-6 shadow-sm">
                        <div class="flex items-center justify-between gap-2 mb-4 pb-3 border-b border-gray-100">
                            <div class="flex items-center gap-2.5">
                                <div class="w-8 h-8 rounded-xl bg-amber-500 text-white flex items-center justify-center flex-shrink-0 shadow-sm">
                                    <i class="bx bx-key text-lg"></i>
                                </div>
                                <div>
                                    <h4 class="font-bold text-gray-800 text-sm sm:text-base leading-tight">Petugas Pemegang Kunci</h4>
                                    <p class="text-xs text-gray-500 mt-0.5">Personil penanggung jawab serah terima kunci dan pengawasan gedung</p>
                                </div>
                            </div>
                            <span class="px-2.5 py-1 bg-amber-50 text-amber-800 border border-amber-200 rounded-lg text-xs font-bold">
                                Kunci dan Akses
                            </span>
                        </div>

                        @if($item->pengurus && $item->pengurus->count() > 0)
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                @foreach($item->pengurus as $pengurus)
                                    @php
                                        $cleanWa = preg_replace('/[^0-9]/', '', $pengurus->kontak ?? '');
                                        $waUrl = $cleanWa ? ('https://wa.me/' . (str_starts_with($cleanWa, '0') ? '62' . substr($cleanWa, 1) : $cleanWa)) : null;
                                        $avatar = $pengurus->foto ? asset('storage/' . $pengurus->foto) : asset('Admin/img/avatars/pria.png');
                                        $isTersedia = ($pengurus->status == 'Tersedia');
                                    @endphp
                                    <div class="p-3.5 bg-gray-50/80 hover:bg-amber-50/50 rounded-xl border border-gray-200/80 transition-all flex items-center justify-between gap-3">
                                        <div class="flex items-center gap-3 min-w-0">
                                            <div class="relative flex-shrink-0">
                                                <img src="{{ $avatar }}" alt="{{ $pengurus->nama }}" class="w-10 h-10 rounded-full object-cover border border-gray-200 shadow-sm" onerror="this.src='{{ asset('Admin/img/avatars/pria.png') }}'">
                                                <span class="absolute bottom-0 right-0 w-2.5 h-2.5 rounded-full {{ $isTersedia ? 'bg-green-500' : 'bg-yellow-500' }} border-2 border-white"></span>
                                            </div>
                                            <div class="min-w-0">
                                                <h5 class="text-xs sm:text-sm font-bold text-gray-800 truncate leading-snug">{{ $pengurus->nama }}</h5>
                                                <div class="text-[11px] text-gray-500 flex items-center gap-1 mt-0.5">
                                                    <i class="bx bx-phone text-xs"></i>
                                                    <span>{{ $pengurus->kontak ?? '-' }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        @if($waUrl)
                                        <a href="{{ $waUrl }}" target="_blank" class="flex-shrink-0 px-3 py-1.5 bg-green-600 hover:bg-green-700 text-white rounded-lg text-xs font-bold transition flex items-center gap-1 shadow-sm">
                                            <i class="bx bxl-whatsapp text-sm"></i>
                                            <span>WhatsApp</span>
                                        </a>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="bg-amber-50/60 border border-dashed border-amber-200 rounded-xl p-4 text-center">
                                <i class="bx bx-key text-amber-500 text-2xl mb-1"></i>
                                <p class="text-xs sm:text-sm text-gray-700 font-medium mb-0.5">Belum ada petugas kunci yang ditugaskan khusus oleh pihak pengelola.</p>
                                <p class="text-[11px] text-gray-500 mb-0">Untuk pengambilan kunci dan koordinasi pembukaan fasilitas, silakan hubungi pengelola layanan daerah setempat.</p>
                            </div>
                        @endif
                    </div>

                    <!-- Informasi Pemohon / Penanggung Jawab Peminjaman -->
                    <div class="bg-white border border-gray-200 rounded-2xl p-5 sm:p-6 mb-6 shadow-sm">
                        <h4 class="font-bold text-gray-800 text-sm sm:text-base mb-3 flex items-center gap-2">
                            <i class="bx bx-user text-blue-600 text-lg"></i>
                            Data Pemohon Peminjaman
                        </h4>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs sm:text-sm">
                            <div class="p-3 bg-gray-50 rounded-xl border border-gray-100">
                                <span class="text-gray-400 block text-[11px] font-semibold mb-0.5">NAMA PEMOHON</span>
                                <span class="text-gray-800 font-bold text-sm">{{ Auth::user()->name }}</span>
                            </div>
                            <div class="p-3 bg-gray-50 rounded-xl border border-gray-100">
                                <span class="text-gray-400 block text-[11px] font-semibold mb-0.5">NOMOR TELEPON / WHATSAPP</span>
                                <span class="text-gray-800 font-bold text-sm">{{ Auth::user()->phone ?? (Auth::user()->kontak ?? 'Sesuai Profil Warga') }}</span>
                            </div>
                        </div>
                    </div>



                    @php
                        // Semua metode pembayaran mengikuti sakelar Pembayaran Wilayah.
                        $metodeWilayah = is_array($setting?->payment_methods) ? $setting->payment_methods : [];
                        $hasTransfer = in_array('transfer', $metodeWilayah, true);
                        $hasTunai = in_array('tunai', $metodeWilayah, true);
                        $hasEwallet = in_array('ewallet', $metodeWilayah, true)
                            && !empty($setting->ewallet_name) && !empty($setting->ewallet_number);
                        $hasAnyPayment = $hasTransfer || $hasTunai || $hasEwallet;
                        $defaultPaymentMethod = $hasTransfer ? 'transfer' : ($hasEwallet ? 'ewallet' : ($hasTunai ? 'tunai' : ''));

                        $bankLogos = [
                            'Bank Syariah Indonesia' => 'Admin/img/banks/bsi.png',
                            'BSI' => 'Admin/img/banks/bsi.png',
                            'BRI' => 'Admin/img/banks/bri.png',
                            'BRIMO' => 'Admin/img/banks/bri.png',
                            'Mandiri' => 'Admin/img/banks/mandiri.png',
                            'BNI' => 'Admin/img/banks/bni.png',
                            'BCA' => 'Admin/img/banks/bca.png',
                            'Bank Riau Kepri Syariah' => 'Admin/img/banks/brk.png',
                            'BRK' => 'Admin/img/banks/brk.png',
                            'Bank Mega' => 'Admin/img/banks/mega.png',
                        ];
                        $bankLogoPath = null;
                        $namaBank = strtoupper($setting->bank_name ?? '');
                        foreach ($bankLogos as $kunci => $jalur) {
                            if ($namaBank !== '' && str_contains($namaBank, strtoupper($kunci))) {
                                $bankLogoPath = $jalur;
                                break;
                            }
                        }

                        $ewalletLogos = [
                            'DANA' => 'assets/img/payment_logos/dana.png',
                            'OVO' => 'assets/img/payment_logos/ovo.png',
                            'GOPAY' => 'assets/img/payment_logos/gopay.png',
                            'SHOPEEPAY' => 'assets/img/payment_logos/shopeepay.png',
                        ];
                        $ewalletName = strtoupper($setting->ewallet_name ?? '');
                        $ewalletLogoPath = null;
                        foreach ($ewalletLogos as $kunci => $jalur) {
                            if ($ewalletName !== '' && str_contains($ewalletName, strtoupper($kunci))) {
                                $ewalletLogoPath = $jalur;
                                break;
                            }
                        }
                        $adaLogoEwallet = $ewalletLogoPath !== null && is_file(public_path($ewalletLogoPath));
                    @endphp

                    @if($bisaBerbayar)
                    <div id="pembayaran-fasilitas" class="mb-6 {{ $skemaBiaya === 'berbayar_semua' ? '' : 'hidden' }}">
                        <h3 class="text-xl font-bold text-gray-800 mb-4">Metode Pembayaran</h3>
                        <input type="hidden" name="payment_method" id="payment-method-fasilitas" value="{{ $defaultPaymentMethod }}">

                        @if($hasAnyPayment)
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-3 mb-6">
                            @if($hasTransfer)
                            <button type="button" onclick="pilihMetodeFasilitas('transfer')" id="btn-fasilitas-transfer"
                                    class="py-4 px-3 rounded-2xl font-bold border-2 {{ $defaultPaymentMethod === 'transfer' ? 'border-blue-500 bg-blue-50 text-blue-700' : 'border-gray-200 bg-white text-gray-600' }} transition-all">
                                Transfer Bank
                            </button>
                            @endif
                            @if($hasTunai)
                            <button type="button" onclick="pilihMetodeFasilitas('tunai')" id="btn-fasilitas-tunai"
                                    class="py-4 px-3 rounded-2xl font-bold border-2 {{ $defaultPaymentMethod === 'tunai' ? 'border-blue-500 bg-blue-50 text-blue-700' : 'border-gray-200 bg-white text-gray-600' }} transition-all">
                                Bayar di Tempat
                            </button>
                            @endif
                            @if($hasEwallet)
                            <button type="button" onclick="pilihMetodeFasilitas('ewallet')" id="btn-fasilitas-ewallet"
                                    class="py-4 px-3 rounded-2xl font-bold border-2 {{ $defaultPaymentMethod === 'ewallet' ? 'border-blue-500 bg-blue-50 text-blue-700' : 'border-gray-200 bg-white text-gray-600' }} transition-all">
                                {{ $setting->ewallet_name }}
                            </button>
                            @endif
                        </div>

                        @if($hasTransfer)
                        <div id="transfer-payment-fasilitas" class="fasilitas-payment-content {{ $defaultPaymentMethod === 'transfer' ? '' : 'hidden' }}">
                            <div class="bg-white border-2 border-blue-50 rounded-2xl shadow-lg p-6">
                                <div class="flex flex-wrap items-center justify-between gap-2 bg-blue-50 border border-blue-200 rounded-xl p-4 mb-5">
                                    <div>
                                        <div class="text-xs font-bold uppercase tracking-wider text-blue-700">Total yang Harus Ditransfer</div>
                                        <div class="text-xs text-gray-600 mt-1 fasilitas-total-breakdown">{{ $tarifFormatted }} / hari × 1 hari × {{ $quantity }} unit</div>
                                    </div>
                                    <div class="text-2xl font-black text-blue-600 fasilitas-total-nominal">Rp {{ number_format($tarifPerHari * $quantity, 0, ',', '.') }}</div>
                                </div>

                                <h4 class="font-bold text-gray-800 mb-4">Transfer ke Rekening Berikut</h4>

                                <div class="flex items-center gap-4 bg-gradient-to-br from-blue-50 to-indigo-50 rounded-2xl p-5 mb-4">
                                    @if(!empty($bankLogoPath))
                                        <img src="{{ asset($bankLogoPath) }}" alt="{{ $setting->bank_name }}" class="h-8 object-contain">
                                    @endif
                                    <div class="min-w-0">
                                        <div class="text-xs uppercase tracking-wider text-gray-500">{{ $setting->bank_name ?: 'Bank' }}</div>
                                        <div class="text-xl font-black tracking-wide text-gray-900 select-all break-all">
                                            {{ $setting->bank_account_number ?: 'Belum diatur' }}
                                        </div>
                                        <div class="text-sm text-gray-600">a.n. {{ $setting->bank_account_holder ?: '-' }}</div>
                                    </div>
                                </div>

                                <div class="bg-amber-50 border-l-4 border-amber-400 p-4 rounded-r-xl mb-5">
                                    <p class="text-sm text-amber-800">
                                        <strong>Gunakan bank yang sama ({{ $setting->bank_name ?: 'bank tujuan' }})</strong>
                                        agar tidak dikenai biaya admin antarbank. Transfer dari bank lain tetap diterima,
                                        tetapi biayanya ditanggung Anda.
                                    </p>
                                </div>

                                <label class="block text-sm font-bold text-gray-700 mb-2">
                                    Unggah Bukti Transfer <span class="text-red-500">*</span>
                                </label>
                                <x-file-upload
                                    name="payment_proof"
                                    accept=".jpg,.jpeg,.png,.pdf"
                                    tone="blue"
                                    button-text="Pilih Bukti Transfer"
                                    empty-text="Belum ada bukti transfer dipilih"
                                    helper="Format JPG, PNG, atau PDF · Maksimal 5 MB. Bukti Anda akan diperiksa petugas desa sebelum pengajuan diproses."
                                />
                            </div>
                        </div>
                        @endif

                        @if($hasEwallet)
                        <div id="ewallet-payment-fasilitas" class="fasilitas-payment-content {{ $defaultPaymentMethod === 'ewallet' ? '' : 'hidden' }}">
                            <div class="bg-white border-2 border-blue-50 rounded-2xl shadow-lg p-6">
                                <div class="flex flex-wrap items-center justify-between gap-2 bg-blue-50 border border-blue-200 rounded-xl p-4 mb-5">
                                    <div>
                                        <div class="text-xs font-bold uppercase tracking-wider text-blue-700">Total yang Harus Dikirim</div>
                                        <div class="text-xs text-gray-600 mt-1 fasilitas-total-breakdown">{{ $tarifFormatted }} / hari × 1 hari × {{ $quantity }} unit</div>
                                    </div>
                                    <div class="text-2xl font-black text-blue-600 fasilitas-total-nominal">Rp {{ number_format($tarifPerHari * $quantity, 0, ',', '.') }}</div>
                                </div>

                                <h4 class="font-bold text-gray-800 mb-4">Kirim ke E-Wallet Berikut</h4>

                                <div class="flex items-center gap-4 bg-gradient-to-br from-sky-50 to-cyan-50 rounded-2xl p-5 mb-4">
                                    @if($adaLogoEwallet)
                                        <img src="{{ asset($ewalletLogoPath) }}" alt="{{ $setting->ewallet_name }}" class="h-8 object-contain">
                                    @endif
                                    <div class="min-w-0">
                                        <div class="text-xs uppercase tracking-wider text-gray-500">{{ $setting->ewallet_name }}</div>
                                        <div class="text-xl font-black tracking-wide text-gray-900 select-all break-all">
                                            {{ $setting->ewallet_number }}
                                        </div>
                                        <div class="text-sm text-gray-600">a.n. {{ $setting->ewallet_account_holder ?: '-' }}</div>
                                    </div>
                                </div>

                                <div class="bg-amber-50 border-l-4 border-amber-400 p-4 rounded-r-xl mb-5">
                                    <p class="text-sm text-amber-800">
                                        <strong>Kirim dari sesama {{ $setting->ewallet_name }}</strong>
                                        agar tidak dikenai biaya transfer. Pengiriman dari aplikasi lain tetap diterima,
                                        tetapi biayanya ditanggung Anda.
                                    </p>
                                </div>

                                <label class="block text-sm font-bold text-gray-700 mb-2">
                                    Unggah Bukti Pengiriman <span class="text-red-500">*</span>
                                </label>
                                <x-file-upload
                                    name="payment_proof"
                                    accept=".jpg,.jpeg,.png,.pdf"
                                    tone="sky"
                                    button-text="Pilih Bukti Pengiriman"
                                    empty-text="Belum ada bukti pengiriman dipilih"
                                    helper="Format JPG, PNG, atau PDF · Maksimal 5 MB. Bukti Anda akan diperiksa petugas desa sebelum pengajuan diproses."
                                />
                            </div>
                        </div>
                        @endif

                        @if($hasTunai)
                        <div id="cash-payment-fasilitas" class="fasilitas-payment-content {{ $defaultPaymentMethod === 'tunai' ? '' : 'hidden' }}">
                            <div class="bg-white border border-gray-200 rounded-2xl shadow-sm p-6">
                                <div class="flex flex-wrap items-center justify-between gap-2 bg-slate-50 border border-slate-200 rounded-xl p-4 mb-3">
                                    <div>
                                        <div class="text-xs font-bold uppercase tracking-wider text-slate-600">Total Pembayaran Tunai</div>
                                        <div class="text-xs text-gray-600 mt-1 fasilitas-total-breakdown">{{ $tarifFormatted }} / hari × 1 hari × {{ $quantity }} unit</div>
                                    </div>
                                    <div class="text-2xl font-black text-blue-600 fasilitas-total-nominal">Rp {{ number_format($tarifPerHari * $quantity, 0, ',', '.') }}</div>
                                </div>
                                <p class="text-sm text-gray-700 mb-0">Pembayaran tunai dilakukan langsung kepada petugas pengelola di lokasi.</p>
                            </div>
                        </div>
                        @endif
                        @else
                        <div class="rounded-xl bg-amber-50 border border-amber-200 px-4 py-3 text-sm text-amber-800">
                            Wilayah ini belum mengaktifkan metode pembayaran apa pun. Silakan hubungi pengelola layanan.
                        </div>
                        @endif
                    </div>
                    @endif

                    @if($skemaBiaya !== 'berbayar_semua')
                    <div id="fasilitas-gratis-notice" class="bg-emerald-50 border border-emerald-200 p-4 mb-6 rounded-xl {{ $bisaBerbayar ? 'hidden' : '' }}">
                        <div class="flex items-start gap-3">
                            <svg class="h-5 w-5 text-emerald-700 flex-shrink-0 mt-0.5" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <p class="text-sm text-emerald-800 mb-0">
                                @if($skemaBiaya === 'berbayar')
                                    Peminjaman untuk kategori <b>Acara Sosial / Masyarakat</b> tidak dipungut biaya (<b>Gratis</b>). Harap kembalikan fasilitas sesuai jadwal dalam kondisi baik.
                                @else
                                    Peminjaman fasilitas ini <b>gratis</b> (tidak dipungut biaya). Harap kembalikan fasilitas sesuai dengan tanggal selesai peminjaman dalam kondisi baik.
                                @endif
                            </p>
                        </div>
                    </div>
                    @endif

                    @if(!empty($sop_fasilitas_umum))
                    <!-- SOP Section -->
                    <div class="bg-white rounded-2xl shadow-lg p-6 mb-6">
                        <div class="flex items-center gap-3 mb-4">
                            <svg class="w-6 h-6 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                            </svg>
                            <h3 class="text-lg font-bold text-gray-800">Ketentuan SOP</h3>
                        </div>
                        
                        <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 text-sm text-gray-700 h-40 overflow-y-auto mb-4">
                            <x-service-terms :text="$sop_fasilitas_umum" />
                        </div>
                        
                        <div class="flex items-center">
                            <input type="checkbox" id="agree-sop" class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                            <label for="agree-sop" class="ml-2 text-sm text-gray-800 font-medium">Saya telah membaca dan menyetujui Ketentuan SOP</label>
                        </div>
                    </div>
                    @endif

                    <div class="flex justify-between items-center">
                        @if(isset($regionSettings['kontak_aula']) && $regionSettings['kontak_aula'])
                        <a href="https://wa.me/{{ preg_replace('/^0/', '62', $regionSettings['kontak_aula']) }}" target="_blank" class="flex items-center gap-2 px-6 py-3 bg-green-500 hover:bg-green-600 text-white font-bold rounded-full shadow-lg transition-all duration-300">
                            <i class='bx bxl-whatsapp text-2xl'></i> Halo Layanan
                        </a>
                        @else
                        <div></div>
                        @endif
                        <button type="button" class="confirm-action-btn px-8 py-3 bg-blue-500 hover:bg-blue-600 text-white font-bold rounded-full shadow-lg hover:shadow-xl transition-all duration-300 transform hover:-translate-y-0.5">
                            Konfirmasi Peminjaman
                        </button>
                    </div>
            </form>
        </div>
    </section>

    <!-- Confirmation Modal -->
    <div id="confirmation-modal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50" style="display: none;">
        <div class="bg-white rounded-3xl p-8 max-w-md w-full mx-4 shadow-2xl transform transition-all">
            <div class="text-center">
                <img src="{{ asset('Admin/img/illustrations/isewalogo.webp') }}" alt="SegiDaerah" class="w-40 mx-auto mb-6">
                <h2 class="text-2xl font-bold text-gray-800 mb-2">Konfirmasi Peminjaman</h2>
                <p class="text-gray-600 mb-3">Apakah Anda yakin ingin meminjam fasilitas ini?</p>
                <div id="confirmation-modal-summary" class="text-sm font-bold text-blue-700 bg-blue-50 border border-blue-200 rounded-xl px-4 py-2.5 mb-6"></div>
                
                <div class="flex gap-4">
                    <button type="button" id="cancel-confirmation" class="flex-1 px-6 py-3 bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold rounded-full transition-colors">
                        Tidak
                    </button>
                    <button type="button" id="proceed-confirmation" class="flex-1 px-6 py-3 bg-blue-500 hover:bg-blue-600 text-white font-semibold rounded-full transition-colors">
                        Ya
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Success Modal -->
    <div id="success-modal" class="fixed inset-0 bg-black/60 hidden items-center justify-center z-50 backdrop-blur-sm transition-all duration-300" style="display: none;">
        <div class="bg-white rounded-[2rem] p-10 max-w-lg w-full mx-4 shadow-2xl transform transition-all relative">
            <button type="button" id="close-success-modal" class="absolute top-6 right-6 text-gray-400 hover:text-gray-800 transition-colors" onclick="window.location.href='{{ route('user.activity') }}'">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
            
            <div class="text-center">
                <div class="mb-8 mt-4">
                    <div class="checkmark-circle mx-auto">
                        <svg class="checkmark" viewBox="0 0 52 52">
                            <circle class="checkmark-circle-path" cx="26" cy="26" r="25" fill="none"/>
                            <path class="checkmark-check" fill="none" d="M14.1 27.2l7.1 7.2 16.7-16.8"/>
                        </svg>
                    </div>
                </div>
                
                <h2 class="text-3xl font-extrabold text-gray-900 mb-3 tracking-tight">Peminjaman Berhasil</h2>
                
                <div class="mb-8">
                    <p class="text-gray-800 font-bold text-lg mb-1">Permintaan Anda sedang Diproses</p>
                    <p class="text-gray-500 text-sm">Silahkan cek riwayat aktivitas Anda.</p>
                </div>
                
                <div class="space-y-4">
                    <button type="button" onclick="window.location.href='{{ route('user.activity') }}'" class="w-full px-6 py-4 bg-[#2395FF] hover:bg-blue-600 text-white font-extrabold rounded-2xl shadow-lg hover:shadow-blue-200/50 transition-all duration-300 transform hover:scale-[1.02] text-lg">
                        Lihat Aktivitas
                    </button>
                </div>
            </div>
        </div>
    </div>
</main>
@endsection

@push('styles')
<style>
    * { font-family: 'Inter', sans-serif; }
    input[type="number"]::-webkit-inner-spin-button,
    input[type="number"]::-webkit-outer-spin-button { -webkit-appearance: none; margin: 0; }
    input[type="number"] { -moz-appearance: textfield; }

    /* Checkmark Animation */
    .checkmark-circle { width: 150px; height: 150px; position: relative; display: inline-block; }
    .checkmark { width: 150px; height: 150px; border-radius: 50%; display: block; stroke-width: 3; stroke: #4ade80; stroke-miterlimit: 10; box-shadow: inset 0px 0px 0px #4ade80; animation: fill .4s ease-in-out .4s forwards, scale .3s ease-in-out .9s both; }
    .checkmark-circle-path { stroke-dasharray: 166; stroke-dashoffset: 166; stroke-width: 3; stroke-miterlimit: 10; stroke: #4ade80; animation: stroke 0.6s cubic-bezier(0.65, 0, 0.45, 1) forwards; }
    .checkmark-check { transform-origin: 50% 50%; stroke-dasharray: 48; stroke-dashoffset: 48; stroke: #fff; animation: stroke 0.3s cubic-bezier(0.65, 0, 0.45, 1) 0.8s forwards; }
    @keyframes stroke { 100% { stroke-dashoffset: 0; } }
    @keyframes scale { 0%, 100% { transform: none; } 50% { transform: scale3d(1.1, 1.1, 1); } }
    @keyframes fill { 100% { box-shadow: inset 0px 0px 0px 75px #4ade80; } }

    /* Delivery Method Card Styles */
    .delivery-method-card {
        border-color: #e5e7eb;
    }
    .delivery-method-card.active {
        border-color: #3b82f6;
        background-color: #eff6ff;
        transform: scale(1.05);
    }
    .delivery-method-card:hover:not(.active) {
        border-color: #93c5fd;
        transform: translateY(-5px);
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // Pemilih metode bayar fasilitas umum.
    window.pilihMetodeFasilitas = function (metode) {
        var input = document.getElementById('payment-method-fasilitas');
        if (input) input.value = metode;

        var panelUtama = document.getElementById('pembayaran-fasilitas');
        var panelUtamaHidden = !panelUtama || panelUtama.classList.contains('hidden');

        var panels = [
            ['transfer-payment-fasilitas', 'transfer'],
            ['ewallet-payment-fasilitas', 'ewallet'],
            ['cash-payment-fasilitas', 'tunai']
        ];
        panels.forEach(function (pair) {
            var panel = document.getElementById(pair[0]);
            if (!panel) return;
            var hidden = pair[1] !== metode;
            panel.classList.toggle('hidden', hidden);
            panel.querySelectorAll('input[type="file"]').forEach(function (inp) {
                inp.disabled = panelUtamaHidden || hidden;
                if (panelUtamaHidden || hidden) inp.value = '';
            });
        });

        [
            ['btn-fasilitas-transfer', 'transfer'],
            ['btn-fasilitas-tunai', 'tunai'],
            ['btn-fasilitas-ewallet', 'ewallet']
        ].forEach(function (pasangan) {
            var el = document.getElementById(pasangan[0]);
            if (!el) return;
            var aktif = pasangan[1] === metode;
            el.classList.toggle('border-blue-500', aktif);
            el.classList.toggle('bg-blue-50', aktif);
            el.classList.toggle('text-blue-700', aktif);
            el.classList.toggle('border-gray-200', !aktif);
            el.classList.toggle('bg-white', !aktif);
            el.classList.toggle('text-gray-600', !aktif);
        });
    };

    (() => {
        function initFasilitasBooking() {
            const form = document.getElementById('booking-form');
            if (!form || form.dataset.initialized === 'true') return;
            form.dataset.initialized = 'true';

            const qtyInput = document.getElementById('quantity');
            const decreaseBtn = document.getElementById('decrease-qty');
            const increaseBtn = document.getElementById('increase-qty');
            const startDateInput = document.getElementById('start-date');
            const endDateInput = document.getElementById('end-date');
            const maxStock = {{ (int) $item->stok }};
            const skemaBiaya = @json($skemaBiaya);
            const tarifPerHari = {{ number_format($tarifPerHari, 0, '.', '') }};
            const hasAnyPayment = {{ $hasAnyPayment ? 'true' : 'false' }};

            const jenisAcaraRadios = document.querySelectorAll('input[name="jenis_acara"]');
            const suratContainer = document.getElementById('surat-pengantar-container');
            const panelBayar = document.getElementById('pembayaran-fasilitas');
            const gratisNotice = document.getElementById('fasilitas-gratis-notice');
            const costSummaryCard = document.getElementById('ringkasan-biaya-fasilitas');
            const costDetailEl = document.getElementById('fasilitas-cost-detail');
            const costTotalEl = document.getElementById('fasilitas-cost-total');
            const modalSummaryEl = document.getElementById('confirmation-modal-summary');

            function formatRp(amount) {
                return 'Rp ' + Math.round(amount || 0).toLocaleString('id-ID');
            }

            function getSelectedAcara() {
                const checked = document.querySelector('input[name="jenis_acara"]:checked');
                return checked ? checked.value : null;
            }

            function isPaidSelection(jenisAcara) {
                if (tarifPerHari <= 0) return false;
                if (skemaBiaya === 'berbayar_semua') return true;
                if (skemaBiaya === 'berbayar') return jenisAcara === 'komersial';
                return false;
            }

            function getDurationDays() {
                const s = startDateInput?.value;
                const e = endDateInput?.value;
                if (!s || !e) return { days: 1, estimated: true };
                const start = new Date(s + 'T00:00:00');
                const end = new Date(e + 'T00:00:00');
                if (isNaN(start.getTime()) || isNaN(end.getTime()) || end < start) {
                    return { days: 1, estimated: true };
                }
                const diffDays = Math.round((end - start) / 86400000) + 1;
                return { days: Math.max(1, diffDays), estimated: false };
            }

            function updateFasilitasPricing() {
                const selectedAcara = getSelectedAcara();
                const qty = Math.max(1, parseInt(qtyInput?.value || '1', 10) || 1);
                const duration = getDurationDays();
                const days = duration.days;
                const paid = isPaidSelection(selectedAcara);
                const total = paid ? (tarifPerHari * days * qty) : 0;

                // Highlight pilihan Kategori Acara
                const labelSosial = document.getElementById('label-acara-sosial');
                const labelKomersial = document.getElementById('label-acara-komersial');
                if (labelSosial) {
                    const active = selectedAcara === 'sosial';
                    labelSosial.classList.toggle('border-blue-500', active);
                    labelSosial.classList.toggle('bg-blue-50', active);
                    labelSosial.classList.toggle('border-gray-300', !active);
                    labelSosial.classList.toggle('bg-white', !active);
                }
                if (labelKomersial) {
                    const active = selectedAcara === 'komersial';
                    labelKomersial.classList.toggle('border-blue-500', active);
                    labelKomersial.classList.toggle('bg-blue-50', active);
                    labelKomersial.classList.toggle('border-gray-300', !active);
                    labelKomersial.classList.toggle('bg-white', !active);
                }

                // Surat pengantar opsional untuk acara sosial
                if (suratContainer) {
                    suratContainer.classList.toggle('hidden', selectedAcara !== 'sosial');
                }

                // Tampilkan / sembunyikan panel pembayaran & pastikan file input tidak ikut terkirim saat gratis
                if (panelBayar) {
                    const showPayment = (skemaBiaya === 'berbayar_semua') || (skemaBiaya === 'berbayar' && selectedAcara === 'komersial');
                    panelBayar.classList.toggle('hidden', !showPayment);
                    if (!showPayment) {
                        panelBayar.querySelectorAll('input[type="file"]').forEach(inp => {
                            inp.disabled = true;
                            inp.value = '';
                        });
                    } else {
                        const activeMethod = document.getElementById('payment-method-fasilitas')?.value;
                        if (activeMethod && typeof window.pilihMetodeFasilitas === 'function') {
                            window.pilihMetodeFasilitas(activeMethod);
                        }
                    }
                }

                if (gratisNotice) {
                    if (skemaBiaya === 'gratis') {
                        gratisNotice.classList.remove('hidden');
                    } else if (skemaBiaya === 'berbayar') {
                        gratisNotice.classList.toggle('hidden', selectedAcara !== 'sosial');
                    } else {
                        gratisNotice.classList.add('hidden');
                    }
                }

                // Perbarui nominal di seluruh panel pembayaran (Transfer Bank, E-Wallet, Tunai)
                const breakdownText = formatRp(tarifPerHari) + ' / hari × ' + days + ' hari × ' + qty + ' unit' + (duration.estimated ? ' (estimasi)' : '');
                document.querySelectorAll('.fasilitas-total-nominal').forEach(el => {
                    el.textContent = formatRp(total);
                });
                document.querySelectorAll('.fasilitas-total-breakdown').forEach(el => {
                    el.textContent = breakdownText;
                });

                // Perbarui Ringkasan Biaya di dalam kartu form
                if (costDetailEl && costTotalEl && costSummaryCard) {
                    if (skemaBiaya === 'gratis') {
                        costDetailEl.textContent = 'Tidak dipungut biaya (Gratis untuk acara sosial maupun pribadi).';
                        costTotalEl.textContent = 'Gratis';
                        costTotalEl.className = 'text-xl sm:text-2xl font-black text-emerald-700';
                        costSummaryCard.className = 'rounded-xl border bg-emerald-50 border-emerald-200 p-4 flex flex-wrap items-center justify-between gap-3';
                    } else if (skemaBiaya === 'berbayar' && !selectedAcara) {
                        costDetailEl.textContent = 'Pilih Kategori Acara untuk melihat rincian biaya peminjaman.';
                        costTotalEl.textContent = '-';
                        costTotalEl.className = 'text-xl sm:text-2xl font-black text-slate-600';
                        costSummaryCard.className = 'rounded-xl border bg-slate-50 border-slate-200 p-4 flex flex-wrap items-center justify-between gap-3';
                    } else if (!paid) {
                        costDetailEl.textContent = 'Acara Sosial / Masyarakat tidak dipungut biaya (Gratis).';
                        costTotalEl.textContent = 'Gratis';
                        costTotalEl.className = 'text-xl sm:text-2xl font-black text-emerald-700';
                        costSummaryCard.className = 'rounded-xl border bg-emerald-50 border-emerald-200 p-4 flex flex-wrap items-center justify-between gap-3';
                    } else {
                        costDetailEl.textContent = breakdownText;
                        costTotalEl.textContent = formatRp(total);
                        costTotalEl.className = 'text-xl sm:text-2xl font-black text-blue-600';
                        costSummaryCard.className = 'rounded-xl border bg-blue-50 border-blue-200 p-4 flex flex-wrap items-center justify-between gap-3';
                    }
                }

                return { selectedAcara, paid, total, days, qty };
            }

            jenisAcaraRadios.forEach(radio => {
                radio.addEventListener('change', updateFasilitasPricing);
            });

            if (startDateInput) {
                startDateInput.addEventListener('change', () => {
                    if (endDateInput) {
                        endDateInput.min = startDateInput.value || '{{ date('Y-m-d') }}';
                        if (endDateInput.value && endDateInput.value < startDateInput.value) {
                            endDateInput.value = startDateInput.value;
                        }
                    }
                    updateFasilitasPricing();
                });
            }
            if (endDateInput) {
                endDateInput.addEventListener('change', updateFasilitasPricing);
            }

            if (decreaseBtn && increaseBtn && qtyInput) {
                decreaseBtn.addEventListener('click', () => {
                    let val = parseInt(qtyInput.value, 10) || 1;
                    if (val > 1) {
                        qtyInput.value = val - 1;
                        updateFasilitasPricing();
                    }
                });
                increaseBtn.addEventListener('click', () => {
                    let val = parseInt(qtyInput.value, 10) || 1;
                    if (val < maxStock) {
                        qtyInput.value = val + 1;
                        updateFasilitasPricing();
                    }
                });
            }

            updateFasilitasPricing();

            const confirmBtns = document.querySelectorAll('.confirm-action-btn');
            const modal = document.getElementById('confirmation-modal');
            const cancelBtn = document.getElementById('cancel-confirmation');
            const proceedBtn = document.getElementById('proceed-confirmation');
            const successModal = document.getElementById('success-modal');

            confirmBtns.forEach(btn => {
                btn.addEventListener('click', () => {
                    const summary = updateFasilitasPricing();
                    const purposeInput = document.getElementById('rental-purpose');
                    const purposeVal = purposeInput ? purposeInput.value.trim() : '';

                    const startDate = startDateInput ? startDateInput.value : '';
                    const endDate = endDateInput ? endDateInput.value : '';
                    const agreeSop = document.getElementById('agree-sop');

                    if (!summary.selectedAcara) {
                        Swal.fire({ icon: 'warning', title: 'Pilih Kategori Acara', text: 'Silakan pilih Kategori Acara (Sosial / Masyarakat atau Komersial / Pribadi) terlebih dahulu.' });
                        return;
                    }

                    if (!purposeVal || !startDate || !endDate) {
                        Swal.fire({ icon: 'warning', title: 'Data Belum Lengkap', text: 'Mohon lengkapi Tujuan, Tanggal Mulai dan Tanggal Selesai.' });
                        return;
                    }

                    if (endDate < startDate) {
                        Swal.fire({ icon: 'warning', title: 'Tanggal Tidak Valid', text: 'Tanggal Selesai tidak boleh lebih awal dari Tanggal Mulai.' });
                        return;
                    }

                    if (summary.paid) {
                        if (!hasAnyPayment) {
                            Swal.fire({ icon: 'warning', title: 'Metode Pembayaran Belum Aktif', text: 'Pengelola wilayah belum mengaktifkan metode pembayaran. Silakan hubungi pengelola layanan.' });
                            return;
                        }
                        const activeMethod = document.getElementById('payment-method-fasilitas')?.value || '';
                        if (activeMethod === 'transfer' || activeMethod === 'ewallet') {
                            const activePanelId = activeMethod === 'transfer' ? 'transfer-payment-fasilitas' : 'ewallet-payment-fasilitas';
                            const proofInput = document.querySelector('#' + activePanelId + ' input[type="file"]');
                            if (!proofInput || !proofInput.files || proofInput.files.length === 0) {
                                Swal.fire({ icon: 'warning', title: 'Bukti Pembayaran Wajib', text: 'Silakan unggah bukti transfer/pembayaran sebesar ' + formatRp(summary.total) + ' terlebih dahulu.' });
                                return;
                            }
                        }
                    }

                    if (agreeSop && !agreeSop.checked) {
                        Swal.fire({ icon: 'warning', title: 'Ketentuan SOP', text: 'Anda harus menyetujui Ketentuan SOP terlebih dahulu sebelum melanjutkan pemesanan.' });
                        return;
                    }

                    if (modalSummaryEl) {
                        modalSummaryEl.textContent = summary.paid
                            ? ('Total Biaya: ' + formatRp(summary.total) + ' (' + summary.days + ' hari × ' + summary.qty + ' unit)')
                            : 'Biaya Peminjaman: Gratis (Rp 0)';
                    }

                    modal.style.display = 'flex';
                    modal.classList.remove('hidden');
                });
            });

        if (cancelBtn) {
            cancelBtn.addEventListener('click', () => {
                modal.style.display = 'none';
                modal.classList.add('hidden');
            });
        }

        if (proceedBtn) {
            proceedBtn.addEventListener('click', () => {
                modal.style.display = 'none';
                modal.classList.add('hidden');

                Swal.fire({
                    title: 'Memproses...', allowOutsideClick: false, showConfirmButton: false, didOpen: () => { Swal.showLoading(); }
                });

                const formData = new FormData(document.getElementById('booking-form'));
                fetch('{{ route("user.fasilitas-umum.book.store") }}', {
                    method: 'POST',
                    body: formData,
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
                })
                .then(response => {
                    Swal.close();
                    if (response.status === 401) {
                        throw new Error('Anda harus login terlebih dahulu');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        successModal.style.display = 'flex';
                        successModal.classList.remove('hidden');
                    } else {
                        Swal.fire({ icon: 'error', title: 'Gagal', text: Object.values(data.errors || {}).flat()[0] || data.message || 'Terjadi kesalahan' });
                    }
                })
                .catch(error => {
                    Swal.fire({ icon: 'error', title: 'Error', text: error.message });
                });
            });
        }
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initFasilitasBooking);
        } else {
            initFasilitasBooking();
        }
        document.addEventListener('turbo:load', initFasilitasBooking);
    })();
</script>
@endpush
