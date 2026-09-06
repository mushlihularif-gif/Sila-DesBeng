@extends('layouts.user')

@section('title', 'Status Verifikasi Identitas')

@section('page')
<main class="flex-grow relative w-full">
    <section class="relative z-10 min-h-screen pt-32 pb-16 bg-cover bg-center bg-no-repeat bg-fixed"
             style="background-image: url('{{ asset('Admin/img/elements/background1.png') }}');">
        <div class="absolute inset-0 bg-white/25 pointer-events-none"></div>

        <div class="relative max-w-2xl mx-auto px-4">
            <div class="bg-white rounded-3xl shadow-lg overflow-hidden">
                <div class="px-6 pt-10 pb-8 text-center">
                    <div class="mx-auto flex items-center justify-center h-20 w-20 rounded-full bg-green-50 mb-6">
                        <svg class="h-10 w-10 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>

                    <h1 class="text-2xl font-bold text-gray-900 mb-2">Identitas Anda Terverifikasi</h1>
                    <p class="text-sm text-gray-500 leading-relaxed">
                        Akun Anda sudah mendapat lencana Centang Biru. Seluruh layanan yang menuntut
                        verifikasi kini terbuka untuk Anda.
                    </p>
                </div>

                <div class="px-6 pb-6">
                    <div class="rounded-2xl border border-gray-100 divide-y divide-gray-100">
                        <div class="flex items-center justify-between px-4 py-3">
                            <span class="text-sm text-gray-500">Status</span>
                            <span class="inline-flex items-center gap-1.5 text-sm font-semibold text-green-600">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                Terverifikasi
                            </span>
                        </div>

                        @php
                            // Dibungkus parse(): kolomnya baru diberi cast datetime,
                            // dan barisan lama bisa saja masih tersimpan sebagai
                            // string dengan bentuk yang tidak seragam.
                            $disetujui = auth()->user()->verified_at ?? $kyc?->reviewed_at;
                            try {
                                $disetujui = $disetujui ? \Carbon\Carbon::parse($disetujui) : null;
                            } catch (\Throwable $e) {
                                $disetujui = null;
                            }
                        @endphp
                        @if($disetujui)
                        <div class="flex items-center justify-between px-4 py-3">
                            <span class="text-sm text-gray-500">Disetujui pada</span>
                            <span class="text-sm font-medium text-gray-800">
                                {{ $disetujui->translatedFormat('d F Y, H:i') }} WIB
                            </span>
                        </div>
                        @endif

                        <div class="flex items-center justify-between px-4 py-3">
                            <span class="text-sm text-gray-500">NIK</span>
                            <span class="text-sm font-medium text-gray-800">{{ auth()->user()->nik ?? '—' }}</span>
                        </div>

                        <div class="flex items-center justify-between px-4 py-3">
                            <span class="text-sm text-gray-500">Nama</span>
                            <span class="text-sm font-medium text-gray-800">{{ auth()->user()->name }}</span>
                        </div>

                        <div class="flex items-start justify-between gap-4 px-4 py-3">
                            <span class="text-sm text-gray-500 flex-shrink-0">Wilayah</span>
                            <span class="text-sm font-medium text-gray-800 text-right">
                                {{ auth()->user()->region->name ?? 'Belum ditentukan' }}
                            </span>
                        </div>

                        @if($kyc && $kyc->admin_notes)
                        <div class="flex items-start justify-between gap-4 px-4 py-3">
                            <span class="text-sm text-gray-500 flex-shrink-0">Catatan petugas</span>
                            <span class="text-sm text-gray-700 text-right">{{ $kyc->admin_notes }}</span>
                        </div>
                        @endif
                    </div>

                    {{-- Penting dijelaskan, bukan disembunyikan: warga sering mencari
                         foto KTP-nya di sini dan mengira sistemnya rusak karena
                         tidak menemukannya. --}}
                    <div class="mt-4 rounded-2xl bg-blue-50 border border-blue-100 px-4 py-3">
                        <p class="text-sm text-blue-800 leading-relaxed">
                            <strong>Foto KTP dan wajah Anda sudah dihapus permanen</strong> dari sistem
                            begitu verifikasi disetujui. Yang disimpan hanya data teks di atas, dengan
                            NIK ditutup sebagian. Karena itu fotonya tidak dapat ditampilkan lagi di halaman ini.
                        </p>
                    </div>

                    <div class="mt-6 flex flex-col sm:flex-row gap-3">
                        <a href="{{ route('profile') }}"
                           class="flex-1 text-center px-5 py-3 rounded-xl border border-gray-300 text-gray-700 font-semibold hover:bg-gray-50 transition">
                            Lihat Profil
                        </a>
                        <a href="{{ route('beranda') }}"
                           class="flex-1 text-center px-5 py-3 rounded-xl bg-blue-600 text-white font-semibold hover:bg-blue-700 transition">
                            Kembali ke Beranda
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>
@endsection
