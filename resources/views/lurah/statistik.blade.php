@extends('layouts.lurah')

@section('title', 'Statistik')
@section('page-title', 'Statistik & Laporan')

@section('content')
<div class="space-y-8">
    
    {{-- Header --}}
    <div class="bg-gradient-to-r from-[#004635] to-[#003026] rounded-2xl p-6 shadow-2xl border-2 border-yellow-400/40" data-aos="fade-down">
        <div class="flex items-center gap-3 mb-2">
            <span class="text-4xl">📈</span>
            <h2 class="text-3xl font-bold text-yellow-400">Statistik & Analisis</h2>
        </div>
        <p class="text-gray-300 text-lg">Analisis data pelaporan kelurahan</p>
    </div>

    {{-- Summary Cards --}}
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
        <div class="bg-gradient-to-br from-blue-500 to-blue-700 rounded-xl p-4 shadow-xl" data-aos="fade-up">
            <p class="text-white/80 text-xs mb-1">Total</p>
            <p class="text-3xl font-bold text-white">{{ $stats['total_laporan'] }}</p>
        </div>
        <div class="bg-gradient-to-br from-orange-500 to-orange-700 rounded-xl p-4 shadow-xl" data-aos="fade-up" data-aos-delay="50">
            <p class="text-white/80 text-xs mb-1">Pending</p>
            <p class="text-3xl font-bold text-white">{{ $stats['pending'] }}</p>
        </div>
        <div class="bg-gradient-to-br from-cyan-500 to-cyan-700 rounded-xl p-4 shadow-xl" data-aos="fade-up" data-aos-delay="100">
            <p class="text-white/80 text-xs mb-1">Proses</p>
            <p class="text-3xl font-bold text-white">{{ $stats['proses'] }}</p>
        </div>
        <div class="bg-gradient-to-br from-green-500 to-green-700 rounded-xl p-4 shadow-xl" data-aos="fade-up" data-aos-delay="150">
            <p class="text-white/80 text-xs mb-1">Selesai</p>
            <p class="text-3xl font-bold text-white">{{ $stats['selesai'] }}</p>
        </div>
<div class="bg-gradient-to-br from-purple-500 to-purple-700 rounded-xl p-4 shadow-xl">
    <p class="text-white/80 text-xs mb-1">Dilanjutkan</p>
    <p class="text-3xl font-bold text-white">{{ $stats['proses'] }}</p>
</div>

        <div class="bg-gradient-to-br from-red-500 to-red-700 rounded-xl p-4 shadow-xl" data-aos="fade-up" data-aos-delay="250">
            <p class="text-white/80 text-xs mb-1">Ditolak</p>
            <p class="text-3xl font-bold text-white">{{ $stats['ditolak'] }}</p>
        </div>
    </div>

    {{-- Charts Row --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        
        {{-- Statistik per RW --}}
        <div class="bg-white/10 backdrop-blur-xl rounded-2xl p-6 shadow-2xl border-2 border-yellow-400/20" data-aos="fade-right">
            <h3 class="text-yellow-400 font-bold text-xl mb-6 flex items-center gap-2">
                <span class="text-2xl">🏘️</span>
                Statistik per RW
            </h3>
            <div class="space-y-4">
                @foreach($statistikRW as $rw)
                    <div class="bg-white/5 rounded-xl p-4 border border-yellow-400/10">
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-white font-bold text-lg">RW {{ $rw->rw }}</span>
                            <span class="text-yellow-400 font-bold text-xl">{{ $rw->total }}</span>
                        </div>
                        <div class="grid grid-cols-5 gap-2 text-xs">
                            <div class="text-center">
                                <p class="text-gray-400">Pending</p>
                                <p class="text-orange-400 font-bold">{{ $rw->pending }}</p>
                            </div>
                            <div class="text-center">
                                <p class="text-gray-400">Proses</p>
                                <p class="text-cyan-400 font-bold">{{ $rw->proses }}</p>
                            </div>
                            <div class="text-center">
                                <p class="text-gray-400">Selesai</p>
                                <p class="text-green-400 font-bold">{{ $rw->selesai }}</p>
                            </div>
                            <div class="text-center">
                                <p class="text-gray-400">Teruskan</p>
                                <p class="text-purple-400 font-bold">{{ $rw->proses }}</p>
                            </div>
                            <div class="text-center">
                                <p class="text-gray-400">Ditolak</p>
                                <p class="text-red-400 font-bold">{{ $rw->ditolak }}</p>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Statistik per Kategori --}}
        <div class="bg-white/10 backdrop-blur-xl rounded-2xl p-6 shadow-2xl border-2 border-yellow-400/20" data-aos="fade-left">
            <h3 class="text-yellow-400 font-bold text-xl mb-6 flex items-center gap-3">
                <span class="text-2xl">🏷️</span>
                Statistik per Kategori
            </h3>
            <div class="space-y-4">
                @foreach($statistikKategori as $kategori)
                    <div class="bg-white/5 rounded-xl p-4 border border-blue-400/10">
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-white font-bold">{{ $kategori->kategori }}</span>
                            <span class="text-blue-400 font-bold text-xl">{{ $kategori->total }}</span>
                        </div>
                        <div class="flex items-center gap-4 text-xs">
                            <div>
                                <span class="text-gray-400">Selesai: </span>
                                <span class="text-green-400 font-bold">{{ $kategori->selesai }}</span>
                            </div>
                            <div>
                                <span class="text-gray-400">Pending: </span>
                                <span class="text-orange-400 font-bold">{{ $kategori->pending }}</span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

    </div>

    {{-- Statistik Bulanan --}}
    <div class="bg-white/10 backdrop-blur-xl rounded-2xl p-6 shadow-2xl border-2 border-yellow-400/20" data-aos="fade-up">
        <h3 class="text-yellow-400 font-bold text-xl mb-6 flex items-center gap-2">
            <span class="text-2xl">📅</span>
            Trend Bulanan (12 Bulan Terakhir)
        </h3>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b-2 border-yellow-400/30">
                        <th class="text-left text-yellow-400 font-bold py-3 px-4">Periode</th>
                        <th class="text-center text-yellow-400 font-bold py-3 px-4">Total</th>
                        <th class="text-center text-yellow-400 font-bold py-3 px-4">Pending</th>
                        <th class="text-center text-yellow-400 font-bold py-3 px-4">Proses</th>
                        <th class="text-center text-yellow-400 font-bold py-3 px-4">Selesai</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($statistikBulanan as $bulan)
                        <tr class="border-b border-gray-700/30 hover:bg-white/5">
                            <td class="py-3 px-4 text-white font-semibold">
                                {{ \Carbon\Carbon::create($bulan->tahun, $bulan->bulan, 1)->isoFormat('MMMM Y') }}
                            </td>
                            <td class="text-center py-3 px-4">
                                <span class="text-white font-bold">{{ $bulan->total }}</span>
                            </td>
                            <td class="text-center py-3 px-4">
                                <span class="px-2 py-1 bg-orange-500/30 text-orange-300 rounded text-sm font-bold">{{ $bulan->pending }}</span>
                            </td>
                            <td class="text-center py-3 px-4">
                                <span class="px-2 py-1 bg-cyan-500/30 text-cyan-300 rounded text-sm font-bold">{{ $bulan->proses }}</span>
                            </td>
                            <td class="text-center py-3 px-4">
                                <span class="px-2 py-1 bg-green-500/30 text-green-300 rounded text-sm font-bold">{{ $bulan->selesai }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-8 text-gray-400">Belum ada data statistik bulanan</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Waktu Penyelesaian per RW --}}
    <div class="bg-white/10 backdrop-blur-xl rounded-2xl p-6 shadow-2xl border-2 border-yellow-400/20" data-aos="fade-up">
        <h3 class="text-yellow-400 font-bold text-xl mb-6 flex items-center gap-2">
            <span class="text-2xl">⏱️</span>
            Rata-rata Waktu Penyelesaian per RW
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($waktuPenyelesaian as $waktu)
                <div class="bg-white/5 rounded-xl p-5 border border-green-400/20 hover:bg-white/10 transition-all">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-white font-bold text-lg">RW {{ $waktu->rw }}</span>
                        <span class="px-3 py-1 bg-green-500/30 text-green-300 rounded-lg text-xs font-bold">
                            {{ $waktu->total_selesai }} selesai
                        </span>
                    </div>
                    <div class="text-center">
                        <p class="text-4xl font-bold text-green-400">{{ round($waktu->rata_rata_hari, 1) }}</p>
                        <p class="text-gray-400 text-sm mt-1">Hari (rata-rata)</p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

</div>
@endsection