@extends('layouts.user')

@section('title', 'Laporan Saya')

@section('page')
<div class="min-h-screen bg-gradient-to-br from-blue-50 to-white py-20 text-gray-800">
    <div class="max-w-7xl mx-auto px-6">
        
        {{-- Header dengan Avatar --}}
<div class="mb-8" data-aos="fade-down">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
        {{-- User Info dengan Avatar --}}
        <div class="flex items-center gap-4">
            @if(Auth::user()->avatar)
                <img src="{{ asset(Auth::user()->avatar) }}" 
                     alt="{{ Auth::user()->name }}"
                     class="w-16 h-16 rounded-full object-cover border-2 border-yellow-400 shadow-lg">
            @else
                <div class="w-16 h-16 bg-gradient-to-br from-yellow-400 to-yellow-600 rounded-full flex items-center justify-center border-2 border-yellow-400 shadow-lg">
                    <span class="text-2xl font-bold text-[#004635]">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</span>
                </div>
            @endif
            
            <div>
                <h1 class="font-melayu text-3xl md:text-4xl text-yellow-400 mb-1">📋 Laporan Saya</h1>
                <p class="text-gray-200 text-sm md:text-base">
                    <span class="font-semibold text-yellow-300">{{ Auth::user()->name }}</span>
                    <span class="text-gray-400 mx-2">•</span>
                    <span class="text-gray-400">{{ Auth::user()->email }}</span>
                </p>
            </div>
        </div>
        
        {{-- Button Buat Laporan --}}
        <a href="{{ route('user.laporan.create') }}" 
           class="bg-yellow-400 text-[#004635] px-6 py-3 rounded-lg font-bold hover:bg-yellow-300 transition shadow-lg whitespace-nowrap">
            ➕ Buat Laporan Baru
        </a>
    </div>
</div>

        {{-- Alert Success --}}
        @if(session('success'))
            <div class="mb-6 bg-green-700/40 border border-green-400 text-white px-4 py-3 rounded-lg" data-aos="fade-up">
                ✅ {{ session('success') }}
            </div>
        @endif

        {{-- Statistik Singkat --}}
        @php
            $totalLaporan = $laporans->total();
            $pending = \App\Models\Laporan::where('user_id', Auth::id())->where('status', 'Pending')->count();
            $proses = \App\Models\Laporan::where('user_id', Auth::id())->whereIn('status', ['Proses', 'Diproses'])->count();
            $selesai = \App\Models\Laporan::where('user_id', Auth::id())->where('status', 'Selesai')->count();
        @endphp

        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
            <div class="bg-white shadow-sm border border-gray-200 rounded-xl p-5 text-gray-800" data-aos="fade-up" data-aos-delay="100">
                <p class="text-gray-300 text-sm">Total Laporan</p>
                <h3 class="text-3xl font-bold text-yellow-400">{{ $totalLaporan }}</h3>
            </div>
            <div class="bg-white shadow-sm border border-gray-200 rounded-xl p-5 text-gray-800" data-aos="fade-up" data-aos-delay="200">
                <p class="text-gray-300 text-sm">⏳ Pending</p>
                <h3 class="text-3xl font-bold text-yellow-400">{{ $pending }}</h3>
            </div>
            <div class="bg-white shadow-sm border border-gray-200 rounded-xl p-5 text-gray-800" data-aos="fade-up" data-aos-delay="300">
                <p class="text-gray-300 text-sm">🔄 Diproses</p>
                <h3 class="text-3xl font-bold text-yellow-400">{{ $proses }}</h3>
            </div>
            <div class="bg-white shadow-sm border border-gray-200 rounded-xl p-5 text-gray-800" data-aos="fade-up" data-aos-delay="400">
                <p class="text-gray-500 text-sm">✅ Selesai</p>
                <h3 class="text-3xl font-bold text-blue-600">{{ $selesai }}</h3>
            </div>
        </div>

        {{-- Tabel Laporan --}}
        <div class="bg-white backdrop-blur shadow border-b border-gray-200 rounded-2xl shadow-lg overflow-hidden" data-aos="fade-up">
            @if($laporans->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kategori</th>
                                <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Lokasi</th>
                                <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Deskripsi</th>
                                <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                                <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach($laporans as $laporan)
                                <tr class="hover:bg-[#004635]/30 transition">
                                    <td class="px-6 py-4 text-sm text-gray-700">
                                        <span class="px-2 py-1 bg-[#004635] border border-yellow-400 rounded text-xs">
                                            {{ $laporan->kategori }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-200">
                                        <i class="text-yellow-400">📍</i> {{ Str::limit($laporan->lokasi, 30) }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-200">
                                        {{ Str::limit($laporan->deskripsi, 50) }}
                                    </td>
                                    <td class="px-6 py-4 text-sm">
                                        @if($laporan->status === 'Pending')
                                            <span class="inline-block px-4 py-2 bg-yellow-400 text-[#004635] rounded-full text-xs font-semibold min-w-[110px] text-center">
                                                ⏳ Pending
                                            </span>
                                        @elseif(in_array($laporan->status, ['Proses', 'Diproses']))
                                            <span class="inline-block px-4 py-2 bg-blue-400 text-white rounded-full text-xs font-semibold min-w-[110px] text-center">
                                                🔄 Diproses
                                            </span>
                                        @elseif($laporan->status === 'Selesai')
                                            <span class="inline-block px-4 py-2 bg-green-400 text-[#004635] rounded-full text-xs font-semibold min-w-[110px] text-center">
                                                ✅ Selesai
                                            </span>
                                        @else
                                            <span class="inline-block px-4 py-2 bg-red-400 text-white rounded-full text-xs font-semibold min-w-[110px] text-center">
                                                ❌ {{ $laporan->status }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-200">
                                        {{ $laporan->created_at->format('d M Y, H:i') }}
                                    </td>
                                    <td class="px-6 py-4 text-sm">
                                        <a href="{{ route('user.laporan.show', $laporan->id) }}" 
                                           class="inline-block px-5 py-2 bg-yellow-400/20 text-yellow-400 border border-yellow-400/50 rounded-lg hover:bg-yellow-400/30 transition font-semibold text-xs min-w-[100px] text-center">
                                            👁️ Detail
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                <div class="px-6 py-4 bg-[#004635]/50">
                    {{ $laporans->links() }}
                </div>
            @else
                <div class="text-center py-16">
                    <div class="text-6xl mb-4">📭</div>
                    <h3 class="text-2xl font-semibold text-yellow-400 mb-2">Belum Ada Laporan</h3>
                    <p class="text-gray-300 mb-6">Anda belum membuat laporan apapun</p>
                    <a href="{{ route('user.laporan.create') }}" 
                       class="inline-block bg-yellow-400 text-[#004635] px-6 py-3 rounded-lg font-bold hover:bg-yellow-300 transition">
                        ➕ Buat Laporan Pertama
                    </a>
                </div>
            @endif
        </div>

    </div>
</div>

<script>
// Auto-hide success alert
document.addEventListener('DOMContentLoaded', function() {
    const alert = document.querySelector('.bg-green-700\\/40');
    if (alert) {
        setTimeout(() => {
            alert.style.transition = 'opacity 0.5s';
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 500);
        }, 5000);
    }
});
</script>
@endsection