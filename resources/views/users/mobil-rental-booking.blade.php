@extends('layouts.user')

@php
    $harianActive = $item->is_harian_active ?? true;
    $boronganActive = $item->is_borongan_active ?? true;
    $defaultJenis = $harianActive ? 'harian' : ($boronganActive ? 'borongan' : 'harian');

    // Normalisasi opsi supir dari database ('Lepas Kunci' -> 'sendiri', 'Dengan Supir' -> 'pengelola', 'Bebas Pilih' -> 'bebas')
    $normalizeSupir = function($val) {
        return match($val) {
            'Lepas Kunci', 'sendiri' => 'sendiri',
            'Dengan Supir', 'pengelola' => 'pengelola',
            'Bebas Pilih', 'bebas' => 'bebas',
            default => 'bebas'
        };
    };

    $harianOpsiSupir = $normalizeSupir($item->opsi_supir ?? 'Bebas Pilih');
    $boronganOpsiSupir = $normalizeSupir($item->opsi_supir_borongan ?? 'Bebas Pilih');

    // Normalisasi ketentuan BBM dari database (1 = ditanggung penyewa, 0 = termasuk layanan / disediakan pengelola)
    $normalizeBbm = function($val) {
        if (is_bool($val)) return $val ? 1 : 0;
        if (is_numeric($val)) return (int)$val ? 1 : 0;
        return in_array($val, ['Pengelola', 'Pemerintah Desa', 'disediakan']) ? 0 : 1;
    };

    $harianBbm = $normalizeBbm($item->bbm_ditanggung ?? 'Penyewa');
    $boronganBbm = $normalizeBbm($item->bbm_ditanggung_borongan ?? 'Pengelola');

    // Petugas pembayaran tunai (strictly no BUMDes)
    $cashDescription = $setting->cash_payment_description ?? 'Petugas / Bendahara Layanan Pengelola';
    if (str_contains(strtolower($cashDescription), 'bumdes')) {
        $cashDescription = str_ireplace('bumdes', 'Pengelola', $cashDescription);
    }
@endphp

@section('page')
<main class="flex-grow relative w-full">
    <section class="relative z-10 min-h-screen pt-32 pb-20 bg-cover bg-center bg-no-repeat bg-fixed" 
             style="background-image: url('{{ asset('Admin/img/elements/background1.png') }}');">
        
        <!-- White Overlay (25% opacity / 75% transparent) to make background visible -->
        <div class="absolute inset-0 bg-white/30 backdrop-blur-[2px] pointer-events-none"></div>

        <div class="max-w-4xl mx-auto px-4 sm:px-6 relative z-20">
            <!-- Header Halaman -->
            <div class="text-center mb-10 mt-2">
                <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-blue-50 border border-blue-200 text-blue-700 text-xs font-semibold uppercase tracking-wider mb-3 shadow-sm">
                    <i class="bx bx-car text-base"></i> Layanan Transportasi Warga
                </div>
                <h1 class="text-3xl md:text-4xl font-extrabold text-gray-900 tracking-tight mb-2">
                    Formulir Pemesanan <span class="bg-gradient-to-r from-[#115789] to-[#2563eb] bg-clip-text text-transparent">Penyewaan Mobil</span>
                </h1>
                <p class="text-gray-600 text-sm md:text-base max-w-xl mx-auto">
                    Pilih opsi layanan pengemudi yang sesuai dan lengkapi rincian jadwal sewa dengan transparan.
                </p>
            </div>

            <form id="booking-form" action="#" method="POST" enctype="multipart/form-data" onsubmit="return false;">
                @csrf
                <input type="hidden" name="mobil_id" value="{{ $item->id }}">
                <input type="hidden" name="quantity" id="hidden-quantity" value="{{ $quantity }}">
                <input type="hidden" name="jenis_sewa" id="jenis-sewa-input" value="{{ $defaultJenis }}">
                <input type="hidden" name="opsi_layanan_supir" id="opsi-layanan-supir-input" value="sendiri">
                <input type="hidden" name="delivery_method" id="delivery-method-input" value="jemput">
                <input type="hidden" name="dengan_supir" id="dengan-supir-input" value="0">
                <input type="hidden" name="payment_method" value="tunai">

                <!-- 1. Pilihan Jenis Sewa (Pill Tabs) -->
                <div class="flex justify-center mb-8" id="jenis-sewa-container">
                    @if($harianActive && $boronganActive)
                    <div class="bg-gray-200/80 backdrop-blur p-1.5 rounded-full inline-flex relative overflow-hidden shadow-inner border border-gray-300">
                        <div id="jenis-sewa-slider" class="absolute top-1.5 bottom-1.5 left-1.5 bg-blue-600 rounded-full transition-all duration-300 ease-in-out shadow-md" style="width: calc(50% - 3px); z-index: 0; transform: translateX({{ $defaultJenis == 'borongan' ? '100%' : '0' }});"></div>
                        
                        <button type="button" 
                                class="jenis-sewa-btn relative z-10 px-8 py-2.5 text-sm font-bold rounded-full transition-colors duration-300 {{ $defaultJenis == 'harian' ? 'text-white' : 'text-gray-700 hover:text-gray-900' }}" 
                                data-jenis="harian" 
                                style="width: 170px;">
                            Sewa Harian
                        </button>
                        
                        <button type="button" 
                                class="jenis-sewa-btn relative z-10 px-8 py-2.5 text-sm font-bold rounded-full transition-colors duration-300 {{ $defaultJenis == 'borongan' ? 'text-white' : 'text-gray-700 hover:text-gray-900' }}" 
                                data-jenis="borongan" 
                                style="width: 170px;">
                            Sewa Borongan
                        </button>
                    </div>
                    @elseif($harianActive)
                    <div class="bg-blue-600 text-white px-8 py-2.5 text-sm font-bold rounded-full shadow-md flex items-center gap-2">
                        <i class="bx bx-calendar"></i> Sewa Harian
                    </div>
                    @elseif($boronganActive)
                    <div class="bg-blue-600 text-white px-8 py-2.5 text-sm font-bold rounded-full shadow-md flex items-center gap-2">
                        <i class="bx bx-map-pin"></i> Sewa Borongan (Drop Off)
                    </div>
                    @endif
                </div>

                <!-- 2. Pilihan Layanan Pengemudi (Supir) -->
                <div class="bg-white rounded-3xl shadow-xl border border-gray-100 p-6 md:p-8 mb-8 transition-all">
                    <div class="text-center mb-6">
                        <h2 class="text-xl md:text-2xl font-extrabold text-gray-800 flex items-center justify-center gap-2">
                            <i class="bx bx-user-check text-blue-600 text-2xl"></i>
                            <span>Pilihan Layanan Pengemudi</span>
                        </h2>
                        <p class="text-sm text-gray-500 mt-1" id="supir-section-subtitle">
                            Tentukan apakah Anda ingin mengemudi sendiri atau didampingi supir resmi pengelola
                        </p>
                    </div>

                    <!-- Cards Container -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-6" id="supir-cards-grid">
                        <!-- Card A: Supir Sendiri (Lepas Kunci) -->
                        <div id="card-supir-sendiri" 
                             class="supir-card cursor-pointer border-2 rounded-2xl p-5 transition-all duration-300 relative flex flex-col justify-between hover:shadow-md bg-white border-gray-200"
                             data-supir="sendiri">
                            <div>
                                <div class="flex items-start justify-between gap-3 mb-4">
                                    <div class="p-3 bg-blue-50 rounded-2xl border border-blue-100">
                                        <img src="{{ asset('User/img/iconbaru/supirsendiri.png') }}" alt="Supir Sendiri" class="w-14 h-14 object-contain">
                                    </div>
                                    <div class="text-right">
                                        <span class="inline-block px-3 py-1 bg-amber-100 text-amber-800 font-bold text-xs rounded-full border border-amber-200">
                                            Lepas Kunci
                                        </span>
                                        <div class="mt-2 text-blue-600 check-badge hidden">
                                            <i class="bx bxs-check-circle text-2xl"></i>
                                        </div>
                                    </div>
                                </div>
                                <h3 class="font-extrabold text-lg text-gray-900 mb-1">Supir Sendiri (Bawa Sendiri)</h3>
                                <p class="text-xs font-medium text-blue-600 mb-3 flex items-center gap-1">
                                    <i class="bx bx-store-alt"></i> Ambil Mandiri di Kantor Pengelola
                                </p>
                                
                                <div class="space-y-2 text-xs text-gray-600 border-t border-gray-100 pt-3">
                                    <div class="flex items-start gap-2">
                                        <i class="bx bx-check text-green-600 text-base flex-shrink-0 mt-0.5"></i>
                                        <span>Penyewa menyetir kendaraan sendiri secara mandiri.</span>
                                    </div>
                                    <div class="flex items-start gap-2">
                                        <i class="bx bx-check text-green-600 text-base flex-shrink-0 mt-0.5"></i>
                                        <span>Unit kendaraan diambil & dikembalikan langsung ke Kantor Pengelola.</span>
                                    </div>
                                    <div class="flex items-start gap-2">
                                        <i class="bx bx-check text-green-600 text-base flex-shrink-0 mt-0.5"></i>
                                        <span><strong>Wajib</strong> membawa & verifikasi fisik KTP & SIM asli yang aktif saat pengambilan.</span>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-4 pt-3 border-t border-gray-100 text-center">
                                <span class="select-indicator text-xs font-bold text-gray-400">Klik untuk memilih</span>
                            </div>
                        </div>

                        <!-- Card B: Dengan Supir Pengelola -->
                        <div id="card-supir-pengelola" 
                             class="supir-card cursor-pointer border-2 rounded-2xl p-5 transition-all duration-300 relative flex flex-col justify-between hover:shadow-md bg-white border-gray-200"
                             data-supir="pengelola">
                            <div>
                                <div class="flex items-start justify-between gap-3 mb-4">
                                    <div class="p-3 bg-emerald-50 rounded-2xl border border-emerald-100">
                                        <img src="{{ asset('User/img/iconbaru/disediakansupir.png') }}" alt="Dengan Supir" class="w-14 h-14 object-contain">
                                    </div>
                                    <div class="text-right">
                                        <span class="inline-block px-3 py-1 bg-emerald-100 text-emerald-800 font-bold text-xs rounded-full border border-emerald-200">
                                            Supir Disediakan
                                        </span>
                                        <div class="mt-2 text-blue-600 check-badge hidden">
                                            <i class="bx bxs-check-circle text-2xl"></i>
                                        </div>
                                    </div>
                                </div>
                                <h3 class="font-extrabold text-lg text-gray-900 mb-1">Dengan Supir Pengelola</h3>
                                <p class="text-xs font-medium text-emerald-600 mb-3 flex items-center gap-1">
                                    <i class="bx bx-map-pin"></i> Supir Menjemput di Alamat Anda
                                </p>
                                
                                <div class="space-y-2 text-xs text-gray-600 border-t border-gray-100 pt-3">
                                    <div class="flex items-start gap-2">
                                        <i class="bx bx-check text-green-600 text-base flex-shrink-0 mt-0.5"></i>
                                        <span>Didampingi supir resmi pihak pengelola yang telah berpengalaman.</span>
                                    </div>
                                    <div class="flex items-start gap-2">
                                        <i class="bx bx-check text-green-600 text-base flex-shrink-0 mt-0.5"></i>
                                        <span>Supir mendatangi dan menjemput rombongan di titik alamat pemesan.</span>
                                    </div>
                                    <div class="flex items-start gap-2">
                                        <i class="bx bx-check text-green-600 text-base flex-shrink-0 mt-0.5"></i>
                                        <span>Perjalanan lebih santai & aman tanpa lelah menyetir di jalan.</span>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-4 pt-3 border-t border-gray-100 text-center">
                                <span class="select-indicator text-xs font-bold text-gray-400">Klik untuk memilih</span>
                            </div>
                        </div>
                    </div>

                    <!-- Single Policy Alert (when only 1 option available by admin) -->
                    <div id="single-driver-notice" class="hidden p-4 rounded-2xl bg-blue-50 border border-blue-200 text-blue-900 text-sm flex items-center gap-3 mb-4">
                        <i class="bx bx-info-circle text-2xl text-blue-600 flex-shrink-0"></i>
                        <span id="single-driver-notice-text">Kebijakan layanan supir telah diatur sesuai ketentuan kendaraan ini.</span>
                    </div>

                    <!-- 3. Informasi Ketentuan Bahan Bakar (BBM) -->
                    <div id="bbm-info-container" class="mt-4">
                        <!-- BBM Ditanggung Penyewa -->
                        <div id="bbm-ditanggung-box" class="p-4 rounded-2xl bg-amber-50/80 border border-amber-200 flex items-start gap-3.5 shadow-sm">
                            <div class="p-2.5 bg-amber-100 rounded-xl text-amber-700 flex-shrink-0 mt-0.5">
                                <img src="{{ asset('User/img/iconbaru/bbmditanggungpengguna.png') }}" alt="BBM Ditanggung" class="w-8 h-8 object-contain">
                            </div>
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="font-extrabold text-amber-900 text-sm md:text-base">Ketentuan BBM: Ditanggung Penyewa</span>
                                    <span class="px-2 py-0.5 text-[10px] font-bold bg-amber-200 text-amber-800 rounded-full">Mandiri</span>
                                </div>
                                <p class="text-amber-800 text-xs md:text-sm mt-1 leading-relaxed">
                                    Bahan bakar (BBM) selama masa sewa diisi mandiri oleh penyewa. Harap mengembalikan kendaraan dengan posisi jarum bahan bakar yang sama seperti saat awal serah terima.
                                </p>
                            </div>
                        </div>

                        <!-- BBM Termasuk Layanan -->
                        <div id="bbm-termasuk-box" class="hidden p-4 rounded-2xl bg-emerald-50/80 border border-emerald-200 flex items-start gap-3.5 shadow-sm">
                            <div class="p-2.5 bg-emerald-100 rounded-xl text-emerald-700 flex-shrink-0 mt-0.5">
                                <img src="{{ asset('User/img/iconbaru/bbmdisediakan.png') }}" alt="BBM Disediakan" class="w-8 h-8 object-contain">
                            </div>
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="font-extrabold text-emerald-900 text-sm md:text-base">Ketentuan BBM: Sudah Termasuk Layanan</span>
                                    <span class="px-2 py-0.5 text-[10px] font-bold bg-emerald-200 text-emerald-800 rounded-full">Gratis BBM</span>
                                </div>
                                <p class="text-emerald-800 text-xs md:text-sm mt-1 leading-relaxed">
                                    Biaya operasional bahan bakar (BBM) sudah termasuk dalam tarif sewa pengelola. Anda dapat menikmati perjalanan tanpa memikirkan biaya pengisian bahan bakar.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 4. Lokasi & Detail Logistik Pengambilan / Penjemputan -->
                <div class="bg-white rounded-3xl shadow-xl border border-gray-100 p-6 md:p-8 mb-8">
                    <!-- Heading Dinamis -->
                    <div class="flex items-center gap-3 mb-5 border-b border-gray-100 pb-4">
                        <div class="p-2.5 bg-blue-50 text-blue-600 rounded-xl text-xl flex-shrink-0">
                            <i id="lokasi-header-icon" class="bx bx-map-pin"></i>
                        </div>
                        <div>
                            <h3 id="lokasi-header-title" class="text-lg md:text-xl font-extrabold text-gray-800">
                                Lokasi Pengambilan & Pengembalian Unit
                            </h3>
                            <p id="lokasi-header-desc" class="text-xs text-gray-500">
                                Informasi serah terima kunci dan kendaraan di Kantor Pengelola
                            </p>
                        </div>
                    </div>

                    <!-- Panel Info Kantor Pengelola (Hanya muncul jika Supir Sendiri / Lepas Kunci) -->
                    <div id="kantor-pengelola-info-box" class="mb-6 space-y-4">
                        <div class="bg-gradient-to-br from-blue-50/70 to-indigo-50/50 border border-blue-100 rounded-2xl p-5 shadow-sm">
                            <div class="flex items-start justify-between gap-4 mb-3">
                                <div>
                                    <span class="text-[11px] font-bold uppercase tracking-wider text-blue-700 bg-blue-100/80 px-2.5 py-0.5 rounded-md">
                                        Titik Temu Serah Terima
                                    </span>
                                    <h4 class="text-base md:text-lg font-bold text-gray-900 mt-1.5">
                                        {{ $setting->location_name ?? 'Kantor Pengelola Layanan Daerah' }}
                                    </h4>
                                    <p class="text-xs md:text-sm text-gray-600 mt-1 leading-relaxed">
                                        {{ $setting->address ?? 'Alamat operasional kantor pengelola' }}
                                    </p>
                                </div>
                                @if($setting && $setting->latitude && $setting->longitude)
                                <a href="https://www.google.com/maps?q={{ $setting->latitude }},{{ $setting->longitude }}" 
                                   target="_blank" 
                                   class="flex-shrink-0 inline-flex items-center gap-1.5 px-3 py-2 bg-white text-blue-600 hover:text-blue-700 text-xs font-bold rounded-xl shadow-sm border border-blue-200 transition-all hover:shadow">
                                    <i class="bx bx-map"></i> Buka Peta
                                </a>
                                @endif
                            </div>

                            @if($setting && $setting->operating_hours)
                            <div class="flex items-center gap-2 text-xs text-gray-600 pt-2 border-t border-blue-100">
                                <i class="bx bx-time text-blue-600 text-base"></i>
                                <span>Jam Operasional: <strong>{{ $setting->operating_hours }}</strong></span>
                            </div>
                            @endif
                        </div>

                        <div class="bg-amber-50/80 border-l-4 border-amber-400 p-4 rounded-r-2xl">
                            <div class="flex items-start gap-2.5">
                                <i class="bx bx-info-circle text-amber-600 text-lg flex-shrink-0 mt-0.5"></i>
                                <div class="text-xs md:text-sm text-amber-800 leading-relaxed">
                                    <strong class="font-bold">Ketentuan Serah Terima:</strong> Penyewa wajib hadir langsung ke Kantor Pengelola di atas untuk pemeriksaan fisik kendaraan, serah terima kunci, serta verifikasi fisik KTP & SIM asli yang masih aktif.
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Panel Info Supir Menjemput (Hanya muncul jika Dengan Supir Pengelola) -->
                    <div id="supir-antar-info-box" class="hidden mb-6">
                        <div class="bg-blue-50 border-l-4 border-blue-500 p-4 rounded-r-2xl">
                            <div class="flex items-start gap-2.5">
                                <i class="bx bx-navigation text-blue-600 text-lg flex-shrink-0 mt-0.5"></i>
                                <div class="text-xs md:text-sm text-blue-900 leading-relaxed">
                                    <strong class="font-bold">Layanan Penjemputan Rombongan:</strong> Supir resmi pengelola akan hadir menjemput rombongan di alamat/titik kumpul penjemputan di bawah ini sesuai jadwal yang Anda tentukan.
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Form Input Identitas Pemesan & Alamat -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="md:col-span-2">
                            <label class="block text-xs md:text-sm font-bold text-gray-700 mb-1.5" id="label-recipient-name">
                                Nama Lengkap Pemesan / Penyewa <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-gray-400">
                                    <i class="bx bx-user text-lg"></i>
                                </span>
                                <input type="text" 
                                       name="recipient_name" 
                                       id="recipient-name"
                                       value="{{ Auth::user()->name ?? '' }}"
                                       placeholder="Nama Lengkap sesuai KTP" 
                                       class="w-full pl-10 pr-4 py-3 bg-gray-50/50 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition-all" 
                                       required>
                            </div>
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-xs md:text-sm font-bold text-gray-700 mb-1.5" id="label-delivery-address">
                                Alamat Domisili / Tempat Tinggal Pemesan <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <textarea name="delivery_address" 
                                          id="delivery-address"
                                          rows="3" 
                                          placeholder="Masukkan alamat lengkap (Nama Jalan, No. Rumah, RT/RW, Dusun, Desa)" 
                                          class="w-full px-4 py-3 bg-gray-50/50 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition-all"
                                          required>{{ Auth::user()->address ?? '' }}</textarea>
                            </div>
                            <p class="text-[11px] text-gray-400 mt-1" id="address-help-text">
                                Alamat domisili digunakan untuk verifikasi data peminjam kendaraan.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- 5. Keterangan / Tujuan Acara -->
                <div class="bg-white rounded-3xl shadow-xl border border-gray-100 p-6 md:p-8 mb-8">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="p-2.5 bg-blue-50 text-blue-600 rounded-xl text-xl flex-shrink-0">
                            <i class="bx bx-notepad"></i>
                        </div>
                        <div>
                            <h3 class="text-lg md:text-xl font-extrabold text-gray-800">
                                Keterangan / Tujuan Perjalanan
                            </h3>
                            <p class="text-xs text-gray-500">
                                Jelaskan keperluan atau rute pemakaian kendaraan secara singkat
                            </p>
                        </div>
                    </div>

                    <div>
                        <textarea name="rental_purpose" 
                                  id="rental-purpose"
                                  rows="3" 
                                  placeholder="Contoh: Perjalanan dinas rapat ke kantor bupati, antar rombongan pengantin keluarga, kondangan ke luar kota, dll." 
                                  class="w-full px-4 py-3 bg-gray-50/50 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition-all"
                                  required></textarea>
                    </div>
                </div>

                <!-- 6. Jadwal Waktu & Jarak / Rute Perjalanan -->
                <div class="bg-white rounded-3xl shadow-xl border border-gray-100 p-6 md:p-8 mb-8">
                    <div class="flex items-center justify-between flex-wrap gap-3 mb-6 pb-4 border-b border-gray-100">
                        <div class="flex items-center gap-3">
                            <div class="p-2.5 bg-blue-50 text-blue-600 rounded-xl text-xl flex-shrink-0">
                                <i class="bx bx-calendar-event"></i>
                            </div>
                            <div>
                                <h3 class="text-lg md:text-xl font-extrabold text-gray-800">
                                    Jadwal & Waktu Penyewaan
                                </h3>
                                <p class="text-xs text-gray-500" id="waktu-section-subtitle">
                                    Tentukan tanggal mulai dan selesai pemakaian kendaraan
                                </p>
                            </div>
                        </div>

                        <!-- Badge Durasi / Rute -->
                        <div id="duration-badge-container">
                            <span class="inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-blue-50 border border-blue-200 text-blue-800 font-bold text-xs rounded-full shadow-sm">
                                <i class="bx bx-time-five"></i>
                                <span id="days-count-display">1 Hari</span>
                            </span>
                        </div>
                    </div>

                    <!-- Row Tanggal Sewa -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-xs md:text-sm font-bold text-gray-700 mb-1.5" id="label-start-date">
                                Tanggal Mulai Pemakaian <span class="text-red-500">*</span>
                            </label>
                            <input type="date" 
                                   name="start_date" 
                                   id="start-date"
                                   min="{{ date('Y-m-d') }}"
                                   value="{{ date('Y-m-d') }}"
                                   class="w-full px-4 py-3 bg-gray-50/50 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition-all"
                                   required>
                        </div>

                        <!-- Container Tanggal Selesai (Khusus Sewa Harian) -->
                        <div id="end-date-container">
                            <label class="block text-xs md:text-sm font-bold text-gray-700 mb-1.5">
                                Tanggal Selesai Pemakaian <span class="text-red-500">*</span>
                            </label>
                            <input type="date" 
                                   name="end_date" 
                                   id="end-date"
                                   min="{{ date('Y-m-d') }}"
                                   value="{{ date('Y-m-d') }}"
                                   class="w-full px-4 py-3 bg-gray-50/50 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition-all">
                        </div>

                        <!-- Container Rute / Wilayah / Jarak (Khusus Sewa Borongan) -->
                        <div id="borongan-route-container" class="hidden">
                            @if($isWilayah)
                                <label class="block text-xs md:text-sm font-bold text-gray-700 mb-1.5">
                                    Wilayah Rute Tujuan <span class="text-red-500">*</span>
                                </label>
                                <select name="tujuan_wilayah" id="tujuan_wilayah_select" class="w-full px-4 py-3 bg-gray-50/50 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition-all">
                                    <option value="dalam_desa">Rute Dalam Desa</option>
                                    <option value="luar_desa">Rute Luar Desa (Dalam 1 Kecamatan)</option>
                                    @if(($tarifWilayah['tipe_luar_kecamatan'] ?? 'pukul_rata') == 'per_kecamatan')
                                        @foreach($kecamatanKhusus as $kec)
                                            <option value="kec_{{ $kec->id }}">Kec. {{ $kec->name }}</option>
                                        @endforeach
                                    @else
                                        <option value="luar_kecamatan">Rute Luar Kecamatan</option>
                                    @endif
                                </select>
                            @else
                                <label class="block text-xs md:text-sm font-bold text-gray-700 mb-1.5">
                                    Estimasi Jarak Tempuh (km) <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <input type="number" 
                                           name="distance_km" 
                                           id="distance_km" 
                                           value="1" 
                                           min="1" 
                                           class="w-full px-4 py-3 bg-gray-50/50 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition-all">
                                    <span class="absolute inset-y-0 right-0 flex items-center pr-4 font-bold text-gray-500 text-sm pointer-events-none">KM</span>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="flex items-center gap-2 text-xs text-gray-500 bg-gray-50 p-3 rounded-xl border border-gray-100">
                        <i class="bx bx-shield-quarter text-blue-600 text-base"></i>
                        <span>Pembayaran sewa dapat dilakukan secara fleksibel di tempat hingga selesainya pemakaian mobil.</span>
                    </div>
                </div>

                <!-- 7. Rincian Unit Kendaraan & Kalkulasi Biaya -->
                <div class="bg-white rounded-3xl shadow-xl border border-gray-100 p-6 md:p-8 mb-8">
                    <div class="flex items-center justify-between gap-3 mb-6 pb-4 border-b border-gray-100">
                        <div class="flex items-center gap-3">
                            <div class="p-2.5 bg-blue-50 text-blue-600 rounded-xl text-xl flex-shrink-0">
                                <i class="bx bx-car"></i>
                            </div>
                            <div>
                                <h3 class="text-lg md:text-xl font-extrabold text-gray-800">
                                    {{ $item->nama_mobil ?? $item->nama_barang }}
                                </h3>
                                <p class="text-xs text-gray-500">
                                    Kategori: <span class="font-semibold text-gray-700 capitalize">{{ $item->kategori ?? 'Kendaraan' }}</span>
                                </p>
                            </div>
                        </div>
                        <span class="px-3 py-1 bg-green-50 text-green-700 font-bold text-xs rounded-full border border-green-200">
                            Unit Tersedia
                        </span>
                    </div>

                    <div class="flex flex-col sm:flex-row gap-6 items-center">
                        <div class="w-full sm:w-36 h-28 bg-gray-100 rounded-2xl overflow-hidden flex-shrink-0 border border-gray-200">
                            <img src="{{ asset('storage/' . ($item->foto ?? $item->foto_utama)) }}" 
                                 alt="{{ $item->nama_mobil ?? $item->nama_barang }}" 
                                 class="w-full h-full object-cover">
                        </div>

                        <div class="flex-1 w-full space-y-3">
                            <div>
                                <p class="text-xs font-semibold text-gray-500">Tarif Satuan</p>
                                <p class="text-xl font-extrabold text-gray-900" id="unit-price-display">
                                    Rp. {{ number_format($item->harga_sewa, 0, ',', '.') }}
                                    <span class="text-xs font-medium text-gray-500" id="unit-price-suffix">/ hari</span>
                                </p>
                            </div>

                            <div class="flex items-center gap-3">
                                <label class="text-xs font-semibold text-gray-600">Jumlah Unit:</label>
                                <div class="flex items-center border border-gray-300 rounded-xl bg-gray-50 px-2 py-1 shadow-inner">
                                    <button type="button" id="decrease-qty" class="w-7 h-7 flex items-center justify-center text-gray-600 hover:text-blue-600 hover:bg-white rounded-lg transition">
                                        <i class="bx bx-minus text-base"></i>
                                    </button>
                                    <input type="number" 
                                           id="quantity-display" 
                                           value="{{ $quantity }}" 
                                           min="1" 
                                           max="{{ $item->stok ?? 10 }}"
                                           class="w-10 text-center bg-transparent font-bold text-sm border-0 focus:outline-none focus:ring-0">
                                    <button type="button" id="increase-qty" class="w-7 h-7 flex items-center justify-center text-gray-600 hover:text-blue-600 hover:bg-white rounded-lg transition">
                                        <i class="bx bx-plus text-base"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="w-full sm:w-auto text-left sm:text-right border-t sm:border-t-0 pt-3 sm:pt-0">
                            <p class="text-xs font-semibold text-gray-500">Subtotal Estimasi</p>
                            <p class="text-2xl font-black text-blue-700" id="subtotal-display">
                                Rp. {{ number_format($item->harga_sewa * $quantity, 0, ',', '.') }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- 8. Metode Pembayaran (Tunai / Bayar Ditempat) -->
                <div class="bg-white rounded-3xl shadow-xl border border-gray-100 p-6 md:p-8 mb-8">
                    <div class="flex items-center gap-3 mb-6 pb-4 border-b border-gray-100">
                        <div class="p-2.5 bg-blue-50 text-blue-600 rounded-xl text-xl flex-shrink-0">
                            <i class="bx bx-wallet"></i>
                        </div>
                        <div>
                            <h3 class="text-lg md:text-xl font-extrabold text-gray-800">
                                Metode Pembayaran
                            </h3>
                            <p class="text-xs text-gray-500">
                                Pembayaran tunai saat serah terima unit di lokasi
                            </p>
                        </div>
                    </div>

                    <div class="bg-gradient-to-br from-gray-50 to-gray-100/70 border border-gray-200 rounded-2xl p-6 shadow-sm">
                        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                            <div>
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="px-2.5 py-0.5 bg-blue-600 text-white font-bold text-xs rounded-full">
                                        Tunai
                                    </span>
                                    <h4 class="font-bold text-gray-900 text-base">Pembayaran Ditempat (COD)</h4>
                                </div>
                                <p class="text-xs md:text-sm text-gray-600">
                                    Diserahkan langsung kepada: <strong class="text-gray-800">{{ $cashDescription }}</strong>
                                </p>
                            </div>

                            <div class="text-left md:text-right border-t md:border-t-0 pt-3 md:pt-0">
                                <p class="text-xs font-semibold text-gray-500">Total Yang Harus Dibayar</p>
                                <p class="text-3xl font-black text-red-600 tracking-tight" id="total-amount-display">
                                    Rp. {{ number_format($item->harga_sewa * $quantity, 0, ',', '.') }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 9. Ketentuan SOP & Persetujuan -->
                @if(!empty($sop_mobil))
                <div class="bg-white rounded-3xl shadow-xl border border-gray-100 p-6 md:p-8 mb-8">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="p-2.5 bg-blue-50 text-blue-600 rounded-xl text-xl flex-shrink-0">
                            <i class="bx bx-file"></i>
                        </div>
                        <div>
                            <h3 class="text-lg md:text-xl font-extrabold text-gray-800">
                                Ketentuan SOP Layanan Mobil
                            </h3>
                            <p class="text-xs text-gray-500">
                                Harap membaca dan menyetujui syarat & ketentuan sebelum menyelesaikan pesanan
                            </p>
                        </div>
                    </div>

                    <div class="bg-gray-50 border border-gray-200 rounded-2xl p-4 text-xs md:text-sm text-gray-700 h-36 overflow-y-auto mb-4 whitespace-pre-wrap leading-relaxed">
                        {{ $sop_mobil }}
                    </div>

                    <div class="flex items-center gap-3 bg-blue-50/50 p-3 rounded-xl border border-blue-100">
                        <input type="checkbox" id="agree-sop" class="w-5 h-5 text-blue-600 border-gray-300 rounded focus:ring-blue-500 cursor-pointer">
                        <label for="agree-sop" class="text-xs md:text-sm text-gray-800 font-semibold cursor-pointer select-none">
                            Saya telah membaca, memahami, dan menyetujui Ketentuan SOP di atas.
                        </label>
                    </div>
                </div>
                @endif

                <!-- 10. Tombol Konfirmasi -->
                <div class="flex justify-end mb-12">
                    <button type="button" 
                            id="btn-submit-booking"
                            class="px-10 py-4 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-extrabold text-base rounded-full shadow-lg hover:shadow-blue-500/25 transition-all duration-300 transform hover:-translate-y-0.5 flex items-center gap-2">
                        <span>Konfirmasi Pemesanan</span>
                        <i class="bx bx-right-arrow-alt text-xl"></i>
                    </button>
                </div>
            </form>
        </div>
    </section>

    <!-- Confirmation Modal -->
    <div id="confirmation-modal" class="fixed inset-0 bg-black/60 backdrop-blur-sm hidden items-center justify-center z-50 p-4" style="display: none;">
        <div class="bg-white rounded-3xl p-8 max-w-md w-full shadow-2xl transform transition-all text-center">
            <img src="{{ asset('Admin/img/illustrations/isewalogo.webp') }}" alt="Logo" class="w-36 mx-auto mb-5">
            <h3 class="text-2xl font-black text-gray-900 mb-2">Konfirmasi Pemesanan</h3>
            <p class="text-gray-600 text-sm mb-1">Apakah data pemesanan mobil Anda sudah benar?</p>
            <p class="text-xs text-gray-400 mb-6">Pesanan Anda akan segera diteruskan ke petugas pengelola.</p>
            
            <div class="flex gap-3">
                <button type="button" 
                        id="cancel-confirmation"
                        class="flex-1 px-6 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold text-sm rounded-full transition-colors">
                    Batal
                </button>
                <button type="button" 
                        id="proceed-confirmation"
                        class="flex-1 px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-bold text-sm rounded-full shadow-md hover:shadow-lg transition-all">
                    Ya, Pesan Sekarang
                </button>
            </div>
        </div>
    </div>

    <!-- Success Modal -->
    <div id="success-modal" class="fixed inset-0 bg-black/65 backdrop-blur-sm hidden items-center justify-center z-50 p-4" style="display: none;">
        <div class="bg-white rounded-3xl p-8 md:p-10 max-w-lg w-full shadow-2xl transform transition-all relative text-center">
            <div class="w-20 h-20 bg-emerald-100 text-emerald-600 rounded-full flex items-center justify-center mx-auto mb-5 text-4xl shadow-inner">
                <i class="bx bx-check"></i>
            </div>
            
            <h2 class="text-2xl md:text-3xl font-black text-gray-900 mb-2">Pemesanan Berhasil Dibuat</h2>
            <p class="text-gray-600 text-sm mb-6">
                Pesanan Anda telah diterima dan sedang menunggu konfirmasi dari pihak pengelola.
            </p>
            
            <div class="space-y-3">
                <div class="grid grid-cols-2 gap-3">
                    <button type="button" 
                            id="view-receipt-btn"
                            class="px-4 py-3 bg-blue-50 text-blue-700 font-bold rounded-xl hover:bg-blue-100 transition text-xs md:text-sm flex items-center justify-center gap-1.5 border border-blue-200">
                        <i class="bx bx-show text-base"></i> Lihat Bukti
                    </button>
                    <button type="button" 
                            id="download-receipt-btn"
                            class="px-4 py-3 bg-blue-50 text-blue-700 font-bold rounded-xl hover:bg-blue-100 transition text-xs md:text-sm flex items-center justify-center gap-1.5 border border-blue-200">
                        <i class="bx bx-download text-base"></i> Unduh Bukti
                    </button>
                </div>
                <button type="button" 
                        id="view-activity-btn"
                        class="w-full px-6 py-3.5 bg-blue-600 hover:bg-blue-700 text-white font-extrabold rounded-2xl shadow-lg transition-all text-sm flex items-center justify-center gap-2">
                    <span>Lihat Aktivitas Pemesanan</span>
                    <i class="bx bx-right-arrow-alt text-lg"></i>
                </button>
            </div>
        </div>
    </div>
</main>
@endsection

@push('styles')
<style>
    .supir-card.active {
        border-color: #2563eb !important;
        background-color: #eff6ff !important;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
    }
    .supir-card.active .check-badge {
        display: block !important;
    }
    .supir-card.active .select-indicator {
        color: #2563eb !important;
        font-weight: 800 !important;
    }
    .supir-card.locked {
        cursor: default !important;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    'use strict';

    // Konfigurasi Layanan dari Controller
    const config = {
        harian: {
            opsi_supir: '{{ $harianOpsiSupir }}',
            bbm: {{ $harianBbm ? 1 : 0 }},
            price: {{ (float) ($item->harga_sewa ?? 0) }}
        },
        borongan: {
            opsi_supir: '{{ $boronganOpsiSupir }}',
            bbm: {{ $boronganBbm ? 1 : 0 }},
            isWilayah: {{ $isWilayah ? 'true' : 'false' }},
            tarifWilayah: @json($tarifWilayah ?? []),
            batasDalam: {{ $item->batas_km_dalam_desa ?? 0 }},
            hargaDalam: {{ $item->harga_dalam_desa ?? $item->harga_sewa }},
            batasLuar: {{ $item->batas_km_luar_desa ?? 0 }},
            hargaLuar: {{ $item->harga_luar_desa ?? $item->harga_sewa }},
            hargaLuarKota: {{ $item->harga_luar_kota ?? $item->harga_sewa }}
        },
        maxStock: {{ $item->stok ?? 10 }}
    };

    // Referensi Elemen DOM
    const jenisInput = document.getElementById('jenis-sewa-input');
    const supirInput = document.getElementById('opsi-layanan-supir-input');
    const deliveryMethodInput = document.getElementById('delivery-method-input');
    const denganSupirInput = document.getElementById('dengan-supir-input');
    const hiddenQty = document.getElementById('hidden-quantity');
    const qtyDisplay = document.getElementById('quantity-display');
    const decreaseBtn = document.getElementById('decrease-qty');
    const increaseBtn = document.getElementById('increase-qty');

    const cardSendiri = document.getElementById('card-supir-sendiri');
    const cardPengelola = document.getElementById('card-supir-pengelola');
    const singleDriverNotice = document.getElementById('single-driver-notice');
    const singleDriverNoticeText = document.getElementById('single-driver-notice-text');

    const bbmDitanggungBox = document.getElementById('bbm-ditanggung-box');
    const bbmTermasukBox = document.getElementById('bbm-termasuk-box');

    const lokasiHeaderTitle = document.getElementById('lokasi-header-title');
    const lokasiHeaderDesc = document.getElementById('lokasi-header-desc');
    const lokasiHeaderIcon = document.getElementById('lokasi-header-icon');
    const kantorPengelolaBox = document.getElementById('kantor-pengelola-info-box');
    const supirAntarBox = document.getElementById('supir-antar-info-box');
    const labelRecipientName = document.getElementById('label-recipient-name');
    const labelDeliveryAddress = document.getElementById('label-delivery-address');
    const deliveryAddressInput = document.getElementById('delivery-address');
    const addressHelpText = document.getElementById('address-help-text');

    const startDateInput = document.getElementById('start-date');
    const endDateInput = document.getElementById('end-date');
    const endDateContainer = document.getElementById('end-date-container');
    const boronganRouteContainer = document.getElementById('borongan-route-container');
    const daysCountDisplay = document.getElementById('days-count-display');
    const tujuanWilayahSelect = document.getElementById('tujuan_wilayah_select');
    const distanceKmInput = document.getElementById('distance_km');

    const unitPriceDisplay = document.getElementById('unit-price-display');
    const unitPriceSuffix = document.getElementById('unit-price-suffix');
    const subtotalDisplay = document.getElementById('subtotal-display');
    const totalAmountDisplay = document.getElementById('total-amount-display');

    let receiptId = null;

    // 1. Fungsi Ganti Layanan Supir (Sendiri vs Pengelola)
    function setDriverOption(option) {
        supirInput.value = option;

        if (option === 'sendiri') {
            denganSupirInput.value = '0';
            deliveryMethodInput.value = 'jemput';

            cardSendiri?.classList.add('active');
            cardPengelola?.classList.remove('active');

            const indSendiri = cardSendiri?.querySelector('.select-indicator');
            const indPengelola = cardPengelola?.querySelector('.select-indicator');
            if (indSendiri) indSendiri.textContent = 'Opsi Terpilih';
            if (indPengelola) indPengelola.textContent = 'Klik untuk memilih';

            // Adaptasi Tampilan Lokasi & Logistik
            if (lokasiHeaderTitle) lokasiHeaderTitle.textContent = 'Lokasi Pengambilan & Pengembalian Unit';
            if (lokasiHeaderDesc) lokasiHeaderDesc.textContent = 'Informasi serah terima kunci dan kendaraan di Kantor Pengelola';
            if (lokasiHeaderIcon) lokasiHeaderIcon.className = 'bx bx-store-alt';

            kantorPengelolaBox?.classList.remove('hidden');
            supirAntarBox?.classList.add('hidden');

            if (labelRecipientName) labelRecipientName.innerHTML = 'Nama Lengkap Penyewa <span class="text-red-500">*</span>';
            if (labelDeliveryAddress) labelDeliveryAddress.innerHTML = 'Alamat Domisili / Tempat Tinggal Penyewa <span class="text-red-500">*</span>';
            if (deliveryAddressInput) deliveryAddressInput.placeholder = 'Masukkan alamat lengkap domisili tempat tinggal Anda saat ini';
            if (addressHelpText) addressHelpText.textContent = 'Alamat domisili digunakan untuk verifikasi data peminjam kendaraan saat serah terima.';

        } else {
            denganSupirInput.value = '1';
            deliveryMethodInput.value = 'antar';

            cardPengelola?.classList.add('active');
            cardSendiri?.classList.remove('active');

            const indSendiri = cardSendiri?.querySelector('.select-indicator');
            const indPengelola = cardPengelola?.querySelector('.select-indicator');
            if (indPengelola) indPengelola.textContent = 'Opsi Terpilih';
            if (indSendiri) indSendiri.textContent = 'Klik untuk memilih';

            // Adaptasi Tampilan Lokasi & Logistik
            if (lokasiHeaderTitle) lokasiHeaderTitle.textContent = 'Titik Lokasi Penjemputan Rombongan';
            if (lokasiHeaderDesc) lokasiHeaderDesc.textContent = 'Supir resmi pengelola akan hadir menjemput di alamat penjemputan berikut';
            if (lokasiHeaderIcon) lokasiHeaderIcon.className = 'bx bx-navigation';

            kantorPengelolaBox?.classList.add('hidden');
            supirAntarBox?.classList.remove('hidden');

            if (labelRecipientName) labelRecipientName.innerHTML = 'Nama Pemesan / Kontak Rombongan <span class="text-red-500">*</span>';
            if (labelDeliveryAddress) labelDeliveryAddress.innerHTML = 'Alamat Lengkap Titik Penjemputan <span class="text-red-500">*</span>';
            if (deliveryAddressInput) deliveryAddressInput.placeholder = 'Contoh: Jl. Diponegoro No. 4 RT 02/RW 03 (Depan Pos Ronda), rumah cat putih';
            if (addressHelpText) addressHelpText.textContent = 'Pastikan alamat penjemputan jelas dan dapat diakses oleh kendaraan roda empat.';
        }
    }

    // Event Listener Klik Kartu Supir
    cardSendiri?.addEventListener('click', function() {
        if (!this.classList.contains('locked')) {
            setDriverOption('sendiri');
        }
    });

    cardPengelola?.addEventListener('click', function() {
        if (!this.classList.contains('locked')) {
            setDriverOption('pengelola');
        }
    });

    // 2. Fungsi Update Berdasarkan Jenis Sewa (Harian vs Borongan)
    function updateByJenisSewa(jenis) {
        const currentConfig = config[jenis];
        if (!currentConfig) return;

        // Atur Driver Options
        const allowedSupir = currentConfig.opsi_supir; // 'sendiri', 'pengelola', 'bebas'

        if (allowedSupir === 'sendiri') {
            cardSendiri?.classList.remove('hidden');
            cardSendiri?.classList.add('locked');
            cardPengelola?.classList.add('hidden');
            singleDriverNotice?.classList.remove('hidden');
            if (singleDriverNoticeText) singleDriverNoticeText.textContent = 'Kendaraan ini khusus disewakan dengan layanan Supir Sendiri (Lepas Kunci).';
            setDriverOption('sendiri');
        } else if (allowedSupir === 'pengelola') {
            cardPengelola?.classList.remove('hidden');
            cardPengelola?.classList.add('locked');
            cardSendiri?.classList.add('hidden');
            singleDriverNotice?.classList.remove('hidden');
            if (singleDriverNoticeText) singleDriverNoticeText.textContent = 'Kendaraan ini disewakan lengkap dengan Supir Resmi Pengelola.';
            setDriverOption('pengelola');
        } else {
            // Bebas Pilih
            cardSendiri?.classList.remove('hidden', 'locked');
            cardPengelola?.classList.remove('hidden', 'locked');
            singleDriverNotice?.classList.add('hidden');
            // Pertahankan pilihan sebelumnya atau default ke supir sendiri
            if (!supirInput.value || (supirInput.value !== 'sendiri' && supirInput.value !== 'pengelola')) {
                setDriverOption('sendiri');
            } else {
                setDriverOption(supirInput.value);
            }
        }

        // Atur Ketentuan BBM
        if (currentConfig.bbm === 1) {
            bbmDitanggungBox?.classList.remove('hidden');
            bbmTermasukBox?.classList.add('hidden');
        } else {
            bbmDitanggungBox?.classList.add('hidden');
            bbmTermasukBox?.classList.remove('hidden');
        }

        // Atur Input Form Waktu & Jarak
        if (jenis === 'harian') {
            endDateContainer?.classList.remove('hidden');
            boronganRouteContainer?.classList.add('hidden');
            if (endDateInput) endDateInput.required = true;
            if (unitPriceSuffix) unitPriceSuffix.textContent = '/ hari';
        } else {
            endDateContainer?.classList.add('hidden');
            boronganRouteContainer?.classList.remove('hidden');
            if (endDateInput) {
                endDateInput.required = false;
                endDateInput.value = startDateInput ? startDateInput.value : '';
            }
            if (unitPriceSuffix) unitPriceSuffix.textContent = '/ trip (drop-off)';
        }

        calculateTotals();
    }

    // 3. Kalkulasi Total Biaya
    function calculateTotals() {
        const jenis = jenisInput ? jenisInput.value : 'harian';
        const qty = parseInt(qtyDisplay ? qtyDisplay.value : 1) || 1;
        let unitPrice = 0;
        let total = 0;

        if (jenis === 'harian') {
            unitPrice = config.harian.price;
            let days = 1;
            if (startDateInput && endDateInput && startDateInput.value && endDateInput.value) {
                const s = new Date(startDateInput.value);
                const e = new Date(endDateInput.value);
                if (e >= s) {
                    const diffTime = Math.abs(e - s);
                    days = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
                }
            }
            if (daysCountDisplay) daysCountDisplay.textContent = days + ' Hari';
            total = unitPrice * qty * days;

        } else {
            // Borongan
            if (daysCountDisplay) daysCountDisplay.textContent = 'Drop Off (1 Arah)';
            if (config.borongan.isWilayah) {
                const tujuan = tujuanWilayahSelect ? tujuanWilayahSelect.value : 'dalam_desa';
                const tw = config.borongan.tarifWilayah;

                if (tujuan === 'dalam_desa') {
                    unitPrice = parseInt(tw.harga_dalam_desa) || 0;
                } else if (tujuan === 'luar_desa') {
                    unitPrice = parseInt(tw.harga_luar_desa) || 0;
                } else if (tujuan.startsWith('kec_')) {
                    const kecId = tujuan.replace('kec_', '');
                    unitPrice = parseInt(tw.harga_kecamatan_khusus ? tw.harga_kecamatan_khusus[kecId] : 0) || 0;
                } else {
                    unitPrice = parseInt(tw.harga_luar_kecamatan) || 0;
                }
            } else {
                const dist = parseInt(distanceKmInput ? distanceKmInput.value : 1) || 1;
                unitPrice = config.borongan.hargaLuarKota;
                if (config.borongan.batasDalam > 0 && dist <= config.borongan.batasDalam) {
                    unitPrice = config.borongan.hargaDalam;
                } else if (config.borongan.batasLuar > 0 && dist <= config.borongan.batasLuar) {
                    unitPrice = config.borongan.hargaLuar;
                }
            }
            total = unitPrice * qty;
        }

        if (unitPriceDisplay) unitPriceDisplay.textContent = 'Rp. ' + unitPrice.toLocaleString('id-ID');
        if (subtotalDisplay) subtotalDisplay.textContent = 'Rp. ' + total.toLocaleString('id-ID');
        if (totalAmountDisplay) totalAmountDisplay.textContent = 'Rp. ' + total.toLocaleString('id-ID');
    }

    // 4. Slider & Tombol Pill Tabs Jenis Sewa
    const slider = document.getElementById('jenis-sewa-slider');
    const jenisBtns = document.querySelectorAll('.jenis-sewa-btn');

    if (jenisBtns.length > 0) {
        jenisBtns.forEach((btn, index) => {
            btn.addEventListener('click', function() {
                const jenis = this.dataset.jenis;
                if (jenisInput) jenisInput.value = jenis;

                jenisBtns.forEach(b => {
                    b.classList.remove('text-white');
                    b.classList.add('text-gray-700', 'hover:text-gray-900');
                });
                this.classList.remove('text-gray-700', 'hover:text-gray-900');
                this.classList.add('text-white');

                if (slider) {
                    slider.style.transform = (index === 0) ? 'translateX(0)' : 'translateX(100%)';
                }

                updateByJenisSewa(jenis);
            });
        });
    }

    // Stepper Quantity
    decreaseBtn?.addEventListener('click', function() {
        let val = parseInt(qtyDisplay.value) || 1;
        if (val > 1) {
            val -= 1;
            qtyDisplay.value = val;
            if (hiddenQty) hiddenQty.value = val;
            calculateTotals();
        }
    });

    increaseBtn?.addEventListener('click', function() {
        let val = parseInt(qtyDisplay.value) || 1;
        if (val < config.maxStock) {
            val += 1;
            qtyDisplay.value = val;
            if (hiddenQty) hiddenQty.value = val;
            calculateTotals();
        }
    });

    qtyDisplay?.addEventListener('change', function() {
        let val = parseInt(this.value) || 1;
        if (val < 1) val = 1;
        if (val > config.maxStock) val = config.maxStock;
        this.value = val;
        if (hiddenQty) hiddenQty.value = val;
        calculateTotals();
    });

    // Event Listeners Tanggal & Rute
    startDateInput?.addEventListener('change', function() {
        if (endDateInput && endDateInput.value < this.value) {
            endDateInput.value = this.value;
        }
        if (endDateInput) endDateInput.min = this.value;
        calculateTotals();
    });

    endDateInput?.addEventListener('change', calculateTotals);
    tujuanWilayahSelect?.addEventListener('change', calculateTotals);
    distanceKmInput?.addEventListener('input', calculateTotals);

    // 5. Inisialisasi Pertama Kali
    updateByJenisSewa(jenisInput ? jenisInput.value : 'harian');

    // 6. Konfirmasi & Pengiriman Form
    const submitBtn = document.getElementById('btn-submit-booking');
    const confirmationModal = document.getElementById('confirmation-modal');
    const cancelConfirmation = document.getElementById('cancel-confirmation');
    const proceedConfirmation = document.getElementById('proceed-confirmation');
    const successModal = document.getElementById('success-modal');
    const viewReceiptBtn = document.getElementById('view-receipt-btn');
    const downloadReceiptBtn = document.getElementById('download-receipt-btn');
    const viewActivityBtn = document.getElementById('view-activity-btn');
    const agreeSop = document.getElementById('agree-sop');

    submitBtn?.addEventListener('click', function(e) {
        e.preventDefault();

        const recipientName = document.getElementById('recipient-name')?.value.trim();
        const deliveryAddress = document.getElementById('delivery-address')?.value.trim();
        const rentalPurpose = document.getElementById('rental-purpose')?.value.trim();
        const startDate = startDateInput?.value;
        const endDate = endDateInput?.value;
        const jenis = jenisInput?.value || 'harian';

        if (!recipientName) {
            Swal.fire({ icon: 'warning', title: 'Data Belum Lengkap', text: 'Silakan isi Nama Lengkap Pemesan.' });
            return;
        }
        if (!deliveryAddress) {
            Swal.fire({ icon: 'warning', title: 'Data Belum Lengkap', text: 'Silakan isi Alamat secara lengkap.' });
            return;
        }
        if (!rentalPurpose) {
            Swal.fire({ icon: 'warning', title: 'Data Belum Lengkap', text: 'Silakan isi Keterangan / Tujuan Perjalanan.' });
            return;
        }
        if (!startDate) {
            Swal.fire({ icon: 'warning', title: 'Data Belum Lengkap', text: 'Silakan tentukan Tanggal Mulai Pemakaian.' });
            return;
        }
        if (jenis === 'harian' && !endDate) {
            Swal.fire({ icon: 'warning', title: 'Data Belum Lengkap', text: 'Silakan tentukan Tanggal Selesai Pemakaian.' });
            return;
        }
        if (agreeSop && !agreeSop.checked) {
            Swal.fire({ icon: 'warning', title: 'Persetujuan SOP', text: 'Anda harus menyetujui Ketentuan SOP terlebih dahulu.' });
            return;
        }

        // Buka Modal Konfirmasi
        if (confirmationModal) {
            confirmationModal.style.display = 'flex';
            confirmationModal.classList.remove('hidden');
        }
    });

    cancelConfirmation?.addEventListener('click', function() {
        if (confirmationModal) {
            confirmationModal.style.display = 'none';
            confirmationModal.classList.add('hidden');
        }
    });

    proceedConfirmation?.addEventListener('click', function() {
        if (confirmationModal) {
            confirmationModal.style.display = 'none';
            confirmationModal.classList.add('hidden');
        }

        Swal.fire({
            title: 'Sedang Memproses...',
            text: 'Mohon tunggu beberapa saat',
            allowOutsideClick: false,
            showConfirmButton: false,
            didOpen: () => { Swal.showLoading(); }
        });

        const form = document.getElementById('booking-form');
        const formData = new FormData(form);

        fetch('{{ route("mobil.rental.booking.store") }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        })
        .then(res => {
            Swal.close();
            if (res.status === 401) {
                window.location.reload();
                throw new Error('Silakan login terlebih dahulu');
            }
            return res.json();
        })
        .then(data => {
            if (data.success) {
                receiptId = data.receipt_id || data.booking_id;
                if (successModal) {
                    successModal.style.display = 'flex';
                    successModal.classList.remove('hidden');
                }
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal Memesan',
                    text: data.message || 'Terjadi kendala saat memproses pesanan.'
                });
            }
        })
        .catch(err => {
            Swal.fire({
                icon: 'error',
                title: 'Terjadi Kesalahan',
                text: 'Terjadi kesalahan sistem saat mengirim data pemesanan.'
            });
        });
    });

    viewReceiptBtn?.addEventListener('click', function() {
        if (receiptId) window.open('/receipt/mobil/' + receiptId + '/view', '_blank');
    });

    downloadReceiptBtn?.addEventListener('click', function() {
        if (receiptId) window.location.href = '/receipt/mobil/' + receiptId + '/download';
    });

    viewActivityBtn?.addEventListener('click', function() {
        window.location.href = '{{ route("user.activity") }}';
    });
});
</script>
@endpush