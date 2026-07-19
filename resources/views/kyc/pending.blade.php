@extends('layouts.app')

@section('title', 'Verifikasi Sedang Diproses')

@section('content')
<div class="max-w-xl mx-auto py-20 px-4 sm:px-6 lg:px-8 mt-20">
    <div class="bg-white rounded-3xl shadow-xl overflow-hidden p-10 text-center">
        
        <div class="w-24 h-24 mx-auto bg-blue-100 rounded-full flex items-center justify-center mb-6 animate-pulse">
            <svg class="w-12 h-12 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
        </div>

        <h2 class="text-3xl font-bold text-gray-900 mb-4">Verifikasi Sedang Diproses</h2>
        
        <p class="text-gray-600 mb-8 leading-relaxed">
            Data diri dan hasil scan wajah Anda telah kami terima dan sedang dalam tahap peninjauan oleh Admin Desa. Proses ini biasanya memakan waktu maksimal 1x24 jam kerja.
        </p>

        <div class="bg-blue-50 border border-blue-100 rounded-2xl p-6 mb-8 text-left">
            <h4 class="font-bold text-blue-900 mb-2 flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                Informasi
            </h4>
            <ul class="list-disc list-inside text-sm text-blue-800 space-y-1">
                <li>Anda akan menerima notifikasi via WhatsApp/Email setelah disetujui.</li>
                <li>Fitur Lapor dan Aspirasi sementara dikunci.</li>
                <li>Setelah diverifikasi, Anda akan mendapatkan "Centang Biru" (Warga Terverifikasi).</li>
            </ul>
        </div>

        <a href="{{ route('beranda') }}" class="inline-flex justify-center items-center py-3 px-8 border border-transparent rounded-full shadow-sm text-base font-semibold text-white bg-blue-600 hover:bg-blue-700 transition transform hover:-translate-y-0.5">
            Kembali ke Beranda
        </a>

    </div>
</div>
@endsection
