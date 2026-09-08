@extends('layouts.user')

@section('title', 'KTP Digital & Verifikasi Identitas')

@section('page')
<main class="flex-grow relative w-full min-h-screen">
    {{-- Custom Vector Abstract Background (Gelombang Kabar Daerah) --}}
    @include('partials.abstract-bg')

    <section class="relative z-10 min-h-screen pb-20"
             style="padding-top: clamp(105px, 9vw, 130px);">

        @php
            $user = auth()->user();
            $disetujui = $user->verified_at ?? $kyc?->reviewed_at;
            try {
                $disetujui = $disetujui ? \Carbon\Carbon::parse($disetujui) : null;
            } catch (\Throwable $e) {
                $disetujui = null;
            }

            // Sensor NIK (Kepatuhan Privasi UU PDP): 1403********3597
            $rawNik = $user->nik ?? '';
            if (strlen($rawNik) >= 8) {
                $maskedNik = substr($rawNik, 0, 4) . str_repeat('*', max(0, strlen($rawNik) - 8)) . substr($rawNik, -4);
            } else {
                $maskedNik = '1403********3597';
            }

            // Masking Nama: M. M*******L A**F
            $nameParts = array_values(array_filter(explode(' ', trim($user->name))));
            $maskedParts = [];
            foreach ($nameParts as $w) {
                $l = strlen($w);
                if ($l <= 2) {
                    $maskedParts[] = $w;
                } else {
                    $maskedParts[] = substr($w, 0, 1) . str_repeat('*', max(1, $l - 2)) . substr($w, -1);
                }
            }
            $maskedName = implode('&nbsp;&nbsp;', $maskedParts);
        @endphp

        <div class="relative w-full mx-auto px-4"
             style="max-width: 700px;"
             x-data="{ 
                 confirmed: localStorage.getItem('kyc_confirmed_{{ $user->id }}') === 'true',
                 showDetails: false,
                 confirmNotification() {
                     this.confirmed = true;
                     localStorage.setItem('kyc_confirmed_{{ $user->id }}', 'true');
                 }
             }">

            {{-- ========================================================================= --}}
            {{-- STATE 1: NOTIFIKASI KELULUSAN VERIFIKASI (SEKILAS SEBELUM DIKONFIRMASI)   --}}
            {{-- ========================================================================= --}}
            <div x-show="!confirmed" 
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95"
                 class="bg-white rounded-3xl shadow-xl overflow-hidden border border-gray-100">
                
                <div class="px-6 pt-10 pb-6 text-center">
                    <div class="mx-auto flex items-center justify-center h-20 w-20 rounded-full bg-green-50 mb-5 shadow-inner">
                        <svg class="h-10 w-10 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>

                    <h1 class="text-2xl font-bold text-gray-900 mb-2">Identitas Anda Terverifikasi</h1>
                    <p class="text-sm text-gray-500 max-w-md mx-auto leading-relaxed">
                        Akun Anda telah resmi berstatus <strong>Warga Terverifikasi</strong> dengan lencana Centang Biru. Seluruh hak akses layanan publik kini aktif.
                    </p>
                </div>

                <div class="px-6 pb-8">
                    {{-- Tabel Data dengan NIK Ter-Sensor Penuh --}}
                    <div class="rounded-2xl border border-gray-100 divide-y divide-gray-100 bg-gray-50/50 mb-5 overflow-hidden">
                        <div class="flex items-center justify-between px-4 py-3 bg-white">
                            <span class="text-sm text-gray-500">Status</span>
                            <span class="inline-flex items-center gap-1.5 text-sm font-semibold text-green-600">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                Terverifikasi Resmi
                            </span>
                        </div>

                        @if($disetujui)
                        <div class="flex items-center justify-between px-4 py-3">
                            <span class="text-sm text-gray-500">Disetujui pada</span>
                            <span class="text-sm font-medium text-gray-800">
                                {{ $disetujui->translatedFormat('d F Y, H:i') }} WIB
                            </span>
                        </div>
                        @endif

                        {{-- NIK TERLINDUNGI & DISENSOR SESUAI UU PDP --}}
                        <div class="flex items-center justify-between px-4 py-3 bg-white">
                            <span class="text-sm text-gray-500">NIK (Tersensor)</span>
                            <span class="text-sm font-mono font-bold text-blue-900 bg-blue-100/80 px-2.5 py-0.5 rounded tracking-widest">
                                {{ $maskedNik }}
                            </span>
                        </div>

                        <div class="flex items-center justify-between px-4 py-3">
                            <span class="text-sm text-gray-500">Nama</span>
                            <span class="text-sm font-semibold text-gray-800 flex items-center gap-1.5">
                                {{ $user->name }}
                                <img src="{{ asset('images/verified-badge.png?v=2') }}" class="w-4 h-4 object-contain inline-block select-none" alt="Terverifikasi">
                            </span>
                        </div>

                        <div class="flex items-start justify-between gap-4 px-4 py-3 bg-white">
                            <span class="text-sm text-gray-500 flex-shrink-0">Wilayah</span>
                            <span class="text-sm font-medium text-gray-800 text-right">
                                {{ $user->region->name ?? 'Desa Pematang Duku Timur' }}
                            </span>
                        </div>

                        @if($kyc && $kyc->admin_notes)
                        <div class="flex items-start justify-between gap-4 px-4 py-3">
                            <span class="text-sm text-gray-500 flex-shrink-0">Catatan petugas</span>
                            <span class="text-sm text-gray-700 text-right">{{ $kyc->admin_notes }}</span>
                        </div>
                        @endif
                    </div>

                    {{-- Perlindungan Privasi UU PDP --}}
                    <div class="rounded-2xl bg-blue-50 border border-blue-100 p-4 mb-6">
                        <div class="flex items-start gap-3">
                            <div class="p-1.5 bg-blue-100 rounded-lg text-blue-700 mt-0.5 shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                            </div>
                            <p class="text-xs text-blue-900 leading-relaxed">
                                <strong>Kepatuhan UU PDP No. 27 Tahun 2022:</strong> Foto fisik e-KTP dan selfie biometrik telah <strong>dimusnahkan secara permanen</strong> dari penyimpanan server (*Zero Footprint*). Data NIK terenkripsi dan ditutup sebagian untuk menjaga privasi Anda.
                            </p>
                        </div>
                    </div>

                    {{-- Tombol Konfirmasi Utama (Mengalihkan ke KTP Digital) --}}
                    <button type="button" 
                            @click="confirmNotification()"
                            class="w-full text-center py-3.5 px-6 rounded-2xl bg-blue-600 hover:bg-blue-700 text-white font-bold shadow-md shadow-blue-500/25 hover:shadow-lg transition-all cursor-pointer text-sm md:text-base tracking-wide">
                        Konfirmasi &amp; Buka KTP Digital
                    </button>
                </div>
            </div>

            {{-- ========================================================================= --}}
            {{-- STATE 2: TAMPILAN UTAMA PERMANEN (KTP DIGITAL RESMI)                      --}}
            {{-- ========================================================================= --}}
            <div x-show="confirmed" 
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 class="space-y-4">

                {{-- Status Verifikasi Resmi (Dipindahkan dari Profil, Tanpa Tombol) --}}
                <div class="rounded-2xl px-5 py-3.5 border border-blue-100 shadow-sm flex items-center gap-3 bg-white/90 backdrop-blur-md">
                    <img src="{{ asset('images/verified-badge.png?v=2') }}" class="w-6 h-6 object-contain inline-block select-none" alt="Terverifikasi">
                    <div>
                        <span class="text-xs sm:text-sm font-bold text-gray-900 block leading-tight">Warga Terverifikasi Resmi</span>
                        <span class="text-[11px] sm:text-xs text-gray-500 leading-normal">Data kependudukan terintegrasi di sistem</span>
                    </div>
                </div>

                {{-- KARTU KTP DIGITAL - DESAIN MIRIP e-KTP ASLI --}}
                <div class="rounded-2xl overflow-hidden shadow-2xl relative" style="aspect-ratio: 8.56/5.398; background: #AFEAFF;">

                    {{-- Peta Indonesia Watermark (Pastel Azure #7dccf2) --}}
                    <div class="absolute inset-0 pointer-events-none flex items-center justify-center">
                        <img src="{{ asset('images/indonesia-map-azure.png') }}" alt="" class="w-full h-full object-cover select-none" draggable="false">
                    </div>

                    {{-- Konten Kartu --}}
                    <div class="relative z-10 h-full flex flex-col justify-between" style="padding: 3.5% 5.5%;">

                        {{-- Header: Pemerintah Kabupaten Bengkalis + KTP DIGITAL - SILADESBENG --}}
                        <div class="text-center">
                            <p style="font-weight: 900; font-size: clamp(9.5px, 2.25vw, 15px); color: #0a243a; text-transform: uppercase; letter-spacing: 0.1em; line-height: 1.35; margin: 0;">
                                PEMERINTAH KABUPATEN BENGKALIS
                            </p>
                            <p style="font-weight: 900; font-size: clamp(9.5px, 2.25vw, 15px); color: #0a243a; text-transform: uppercase; letter-spacing: 0.1em; line-height: 1.35; margin: 0;">
                                KTP DIGITAL - SILADESBENG
                            </p>
                            {{-- Garis Tebal Merah Putih --}}
                            <div style="width: 80%; margin: 8px auto 0 auto; box-shadow: 0 1px 3px rgba(0,0,0,0.18); border-radius: 3px; overflow: hidden;">
                                <div style="height: 3px; background: #e11d48;"></div>
                                <div style="height: 3px; background: #ffffff;"></div>
                            </div>
                        </div>

                        {{-- Badan: Data Kiri + Foto Kanan --}}
                        <div class="flex flex-1 items-center justify-between" style="gap: 5%; padding-top: 2%; padding-bottom: 1.5%;">

                            {{-- Kolom Kiri: Data Kependudukan (Ukuran Font Sama & Seragam 100%) --}}
                            <div class="flex-1 flex flex-col justify-center" style="min-width: 0;">
                                <table style="width: 100%; border-collapse: collapse; table-layout: fixed; font-size: clamp(9px, 2.1vw, 14.5px); line-height: 1.5;">
                                    <tbody>
                                        {{-- NIK --}}
                                        <tr>
                                            <td style="width: 78px; font-weight: 800; text-transform: uppercase; color: #12385c; vertical-align: middle; white-space: nowrap; padding: 3.5px 0;">NIK</td>
                                            <td style="width: 14px; text-align: center; font-weight: 800; color: #12385c; vertical-align: middle; padding: 3.5px 0;">:</td>
                                            <td style="font-family: monospace; font-weight: 800; color: #0c2842; vertical-align: middle; padding: 3.5px 0; white-space: nowrap; letter-spacing: 0.05em;">{{ $maskedNik }}</td>
                                        </tr>

                                        {{-- NAMA --}}
                                        <tr>
                                            <td style="width: 78px; font-weight: 800; text-transform: uppercase; color: #12385c; vertical-align: middle; white-space: nowrap; padding: 3.5px 0;">NAMA</td>
                                            <td style="width: 14px; text-align: center; font-weight: 800; color: #12385c; vertical-align: middle; padding: 3.5px 0;">:</td>
                                            <td style="font-weight: 800; text-transform: uppercase; color: #0c2842; vertical-align: middle; padding: 3.5px 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                                <span style="display: inline-flex; align-items: center; gap: 5px;">
                                                    <span>{!! $maskedName !!}</span>
                                                    <img src="{{ asset('images/verified-badge.png?v=2') }}" alt="Terverifikasi" style="width: 15px; height: 15px; object-fit: contain; display: inline-block; flex-shrink: 0;">
                                                </span>
                                            </td>
                                        </tr>

                                        {{-- ALAMAT --}}
                                        <tr>
                                            <td style="width: 78px; font-weight: 800; text-transform: uppercase; color: #12385c; vertical-align: middle; white-space: nowrap; padding: 3.5px 0;">ALAMAT</td>
                                            <td style="width: 14px; text-align: center; font-weight: 800; color: #12385c; vertical-align: middle; padding: 3.5px 0;">:</td>
                                            <td style="font-weight: 700; text-transform: uppercase; color: #0c2842; vertical-align: middle; padding: 3.5px 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                                RT {{ $user->rt ?? '002' }} / RW {{ $user->rw ?? '001' }}
                                            </td>
                                        </tr>

                                        {{-- DESA --}}
                                        <tr>
                                            <td style="width: 78px; font-weight: 800; text-transform: uppercase; color: #12385c; vertical-align: middle; white-space: nowrap; padding: 3.5px 0;">DESA</td>
                                            <td style="width: 14px; text-align: center; font-weight: 800; color: #12385c; vertical-align: middle; padding: 3.5px 0;">:</td>
                                            <td style="font-weight: 700; text-transform: uppercase; color: #0c2842; vertical-align: middle; padding: 3.5px 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                                {{ $user->region->name ?? 'PEMATANG DUKU TIMUR' }}
                                            </td>
                                        </tr>

                                        {{-- STATUS --}}
                                        <tr>
                                            <td style="width: 78px; font-weight: 800; text-transform: uppercase; color: #12385c; vertical-align: middle; white-space: nowrap; padding: 3.5px 0;">STATUS</td>
                                            <td style="width: 14px; text-align: center; font-weight: 800; color: #12385c; vertical-align: middle; padding: 3.5px 0;">:</td>
                                            <td style="font-weight: 800; text-transform: uppercase; color: #047857; vertical-align: middle; padding: 3.5px 0; letter-spacing: 0.04em; white-space: nowrap;">
                                                WARGA TERVERIFIKASI
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            {{-- Kolom Kanan: Foto Warga (Pas & Proporsional, Tidak Kebesaran) --}}
                            <div class="shrink-0 flex flex-col items-center justify-center" style="width: 22%; max-width: 125px;">
                                <div class="overflow-hidden relative shadow-md" style="width: 100%; aspect-ratio: 3/4; border-radius: 8px; border: 2.5px solid #ffffff; background: #e0f2fe;">
                                    @if($user->file)
                                        <img src="{{ $user->file->file_stream }}" alt="Foto profil" class="w-full h-full object-cover">
                                    @else
                                        <div class="w-full h-full flex flex-col items-center justify-center bg-blue-100/50">
                                            <svg class="text-blue-400" style="width: 45%; height: 45%;" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                                            </svg>
                                        </div>
                                    @endif
                                </div>
                                {{-- Badge Terverifikasi (Kecil & Rapi di Bawah Foto) --}}
                                <div class="inline-flex items-center justify-center gap-1 bg-blue-600 text-white rounded-full shadow-sm" style="margin-top: 7%; padding: 2.5px 8px; font-size: clamp(6px, 1.2vw, 8.5px);">
                                    <img src="{{ asset('images/verified-badge.png?v=2') }}" style="width: clamp(8px, 1.7vw, 11px); height: clamp(8px, 1.7vw, 11px);" class="object-contain inline-block">
                                    <span class="font-bold uppercase tracking-wider">Terverifikasi</span>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                {{-- Jaminan Privasi UU PDP (Konsisten Senada dengan Tombol, Tanpa Ikon) --}}
                <div class="flex justify-center notranslate" translate="no">
                    <div class="inline-flex items-center justify-center py-2 px-5 sm:px-6 rounded-full bg-blue-50/80 border border-blue-100 text-blue-900 text-xs sm:text-sm font-medium shadow-xs text-center leading-relaxed">
                        Data Terenkripsi &bull; Dilindungi UU PDP No. 27/2022 &bull; Berkas Fisik Telah Dimusnahkan
                    </div>
                </div>

                {{-- Tombol Aksi (Konsisten dengan Halaman User & Pending KYC, Tanpa Ikon) --}}
                <div class="flex flex-col sm:flex-row items-center justify-center gap-3 pt-1">
                    <a href="{{ route('beranda') }}" class="w-full sm:w-auto inline-flex justify-center items-center py-3 px-8 border border-transparent rounded-full shadow-md text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 transition transform hover:-translate-y-0.5">
                        Kembali ke Beranda
                    </a>
                    <a href="{{ route('profile') }}" class="w-full sm:w-auto inline-flex justify-center items-center py-3 px-6 border border-gray-300 rounded-full shadow-xs text-sm font-semibold text-gray-700 bg-white hover:bg-gray-50 transition">
                        Lihat Profil Saya
                    </a>
                </div>

                {{-- Toggle Catatan Petugas --}}
                <div class="text-center pt-2">
                    <button type="button" 
                            @click="showDetails = !showDetails"
                            class="text-xs text-gray-500 hover:text-blue-600 font-medium transition cursor-pointer hover:underline">
                        <span x-text="showDetails ? 'Sembunyikan Catatan Petugas' : 'Lihat Catatan Petugas Verifikasi'"></span>
                    </button>

                    <div x-show="showDetails" 
                         x-transition
                         class="mt-3 text-left p-4 rounded-2xl bg-white/95 border border-gray-100 shadow-sm text-xs text-gray-700 space-y-2 max-w-md mx-auto">
                        <div class="flex justify-between items-center pb-1.5 border-b border-gray-100">
                            <span class="text-gray-500">Waktu Peninjauan:</span>
                            <span class="font-semibold text-gray-800">{{ $disetujui ? $disetujui->translatedFormat('d F Y, H:i') . ' WIB' : '—' }}</span>
                        </div>
                        <div class="flex justify-between items-start pt-0.5">
                            <span class="text-gray-500 shrink-0 mr-3">Catatan Petugas:</span>
                            <span class="font-medium text-gray-800 text-right">{{ $kyc?->admin_notes ?? 'Data Sesuai dengan e-KTP' }}</span>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>
</main>
@endsection
