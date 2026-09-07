@extends('layouts.user')

@section('title', 'Status Verifikasi Identitas')

@push('styles')
<style>
    .status-verifikasi-heading {
        font-family: 'Inter', sans-serif !important;
        font-size: 26px !important;
        font-weight: 700 !important;
        color: #111827 !important;
        letter-spacing: -0.025em !important;
        line-height: 1.25 !important;
        margin-bottom: 18px !important;
    }
    @media (min-width: 640px) {
        .status-verifikasi-heading {
            font-size: 32px !important;
            margin-bottom: 20px !important;
        }
    }
    @media (min-width: 768px) {
        .status-verifikasi-heading {
            font-size: 34px !important;
            margin-bottom: 22px !important;
        }
    }
</style>
@endpush

@section('page')
<div class="relative w-full min-h-screen">
    {{-- Custom Vector Abstract Background (Gelombang) --}}
    @include('partials.abstract-bg')

    <div class="max-w-2xl mx-auto py-16 px-4 sm:px-6 lg:px-8 mt-20 relative z-10 animate-section">
        <div class="bg-white/95 backdrop-blur-md rounded-3xl shadow-xl border border-gray-100 p-8 sm:p-12 text-center pending-container">
            
            {{-- Icon Status --}}
            <div class="relative w-24 h-24 mx-auto mb-6">
                <div class="absolute inset-0 bg-amber-400/20 rounded-full animate-ping"></div>
                <div class="relative w-24 h-24 bg-amber-50 border-2 border-amber-200 rounded-full flex items-center justify-center text-amber-500 shadow-sm">
                    <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>

            <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-amber-100 text-amber-800 text-xs font-bold uppercase tracking-wider mb-4">
                <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                Menunggu Peninjauan
            </div>

            <h2 class="status-verifikasi-heading">Status Verifikasi Identitas</h2>
            
            <p class="text-gray-600 mb-8 leading-relaxed max-w-lg mx-auto text-sm sm:text-base">
                Data diri dan foto identitas Anda telah kami terima dan saat ini sedang dalam proses peninjauan berkas. Proses ini biasanya membutuhkan waktu maksimal <strong>1×24 jam kerja</strong>.
            </p>

            {{-- Informasi Box --}}
            <div class="bg-blue-50/80 border border-blue-100 rounded-2xl p-6 mb-8 text-left shadow-xs">
                <h4 class="font-bold text-blue-900 mb-3 flex items-center text-sm sm:text-base">
                    <svg class="w-5 h-5 mr-2 text-blue-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Informasi Verifikasi
                </h4>
                <ul class="space-y-2.5 text-xs sm:text-sm text-blue-900/90">
                    <li class="flex items-start gap-2">
                        <svg class="w-4 h-4 text-blue-600 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                        <span>Notifikasi hasil peninjauan akan dikirimkan langsung ke akun SiladesBeng Anda.</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <svg class="w-4 h-4 text-blue-600 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                        <span>Setelah disetujui, akun Anda resmi berstatus <strong>"Warga Terverifikasi"</strong> dengan lencana Centang Biru.</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <svg class="w-4 h-4 text-blue-600 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                        <span>Seluruh fitur layanan publik desa, permohonan surat, dan pelaporan warga akan langsung aktif.</span>
                    </li>
                </ul>
            </div>

            {{-- Tombol Aksi --}}
            <div class="flex flex-col sm:flex-row items-center justify-center gap-3">
                <a href="{{ route('beranda') }}" class="w-full sm:w-auto inline-flex justify-center items-center py-3 px-8 border border-transparent rounded-full shadow-md text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 transition transform hover:-translate-y-0.5">
                    Kembali ke Beranda
                </a>
                <a href="{{ route('profile') }}" class="w-full sm:w-auto inline-flex justify-center items-center py-3 px-6 border border-gray-300 rounded-full shadow-xs text-sm font-semibold text-gray-700 bg-white hover:bg-gray-50 transition">
                    Lihat Profil Saya
                </a>
            </div>

        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .pending-container, .pending-container * {
        font-family: 'Inter', sans-serif;
    }
</style>
@endpush
