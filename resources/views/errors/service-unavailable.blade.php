@extends('layouts.app')

@section('title', 'Layanan Tidak Tersedia')

@section('content')
<div class="min-h-screen bg-[#f8f9fa] pt-32 pb-20 relative overflow-hidden flex items-center justify-center">
    <!-- Background Decor (similar to other pages) -->
    <div class="absolute top-0 left-0 w-full h-[400px] bg-gradient-to-b from-[#0099ff]/10 to-transparent"></div>
    <div class="absolute top-20 right-10 w-72 h-72 bg-blue-400 rounded-full mix-blend-multiply filter blur-3xl opacity-20 animate-blob"></div>
    <div class="absolute top-40 left-10 w-72 h-72 bg-teal-400 rounded-full mix-blend-multiply filter blur-3xl opacity-20 animate-blob animation-delay-2000"></div>
    <div class="absolute -bottom-8 left-20 w-72 h-72 bg-cyan-400 rounded-full mix-blend-multiply filter blur-3xl opacity-20 animate-blob animation-delay-4000"></div>

    <div class="container mx-auto px-4 relative z-10 text-center">
        <div class="max-w-2xl mx-auto bg-white/70 backdrop-blur-md rounded-3xl p-10 shadow-2xl border border-white/50">
            <!-- Icon -->
            <div class="w-24 h-24 mx-auto bg-red-100 rounded-full flex items-center justify-center mb-6">
                <svg class="w-12 h-12 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
            </div>
            
            <h1 class="text-3xl font-bold text-gray-800 mb-4">Layanan Belum Tersedia</h1>
            <p class="text-gray-600 mb-8 text-lg">
                Mohon Maaf, Daerah ini belum menyediakan Layanan ini
            </p>

            <div class="flex flex-col sm:flex-row justify-center gap-4">
                <a href="{{ route('beranda') }}" class="px-8 py-3 bg-[#0099ff] text-white rounded-full font-semibold hover:bg-blue-600 transition shadow-lg hover:shadow-xl hover:-translate-y-0.5">
                    Kembali ke Beranda
                </a>
                <a href="{{ route('bumdes.profil') }}" class="px-8 py-3 bg-white text-[#0099ff] border-2 border-gray-200 rounded-full font-semibold hover:bg-gray-50 transition shadow-sm hover:shadow-md hover:-translate-y-0.5">
                    Pilih Daerah Lain
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
