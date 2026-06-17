@extends('layouts.user')

@section('title', 'Laporan Saya')

@section('page')
<div class="min-h-screen bg-[#f0f4f8] pt-32 pb-20 text-gray-800 relative" style="background: #f0f4f8 url('{{ asset("Admin/img/elements/background.png") }}') no-repeat center center fixed; background-size: cover;">
    <div class="max-w-7xl mx-auto px-6 relative z-10">
        
        {{-- Header dengan Avatar --}}
        <div class="mb-10" data-aos="fade-down">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6 mb-6 bg-white/80 backdrop-blur-md p-6 rounded-2xl border border-gray-100 shadow-sm">
                {{-- User Info dengan Avatar --}}
                <div class="flex items-center gap-5">
                    @if(Auth::user()->avatar)
                        <img src="{{ asset(Auth::user()->avatar) }}" 
                             alt="{{ Auth::user()->name }}"
                             class="w-16 h-16 rounded-full object-cover border-2 border-blue-400 shadow-md">
                    @else
                        <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-blue-700 rounded-full flex items-center justify-center border-2 border-blue-200 shadow-md">
                            <span class="text-2xl font-bold text-white">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</span>
                        </div>
                    @endif
                    
                    <div>
                        <h1 class="text-3xl md:text-4xl font-bold bg-gradient-to-r from-[#115789] to-blue-300 bg-clip-text text-transparent relative inline-block drop-shadow-[0_0_15px_rgba(59,130,246,0.5)] mb-1">Laporan Saya</h1>
                        <p class="text-sm md:text-base text-gray-600">
                            <span class="font-semibold text-blue-900">{{ Auth::user()->name }}</span>
                            <span class="text-gray-400 mx-2">•</span>
                            <span>{{ Auth::user()->email }}</span>
                        </p>
                    </div>
                </div>
                
                {{-- Button Buat Laporan --}}
                <a href="{{ route('user.laporan.create') }}" 
                   class="sd-btn-register hover:-translate-y-1 transition-transform duration-300 whitespace-nowrap" style="display: inline-flex; align-items: center; justify-content: center; gap: 8px; font-weight: bold;">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                    </svg>
                    <span>Buat Laporan Baru</span>
                </a>
            </div>
        </div>

        {{-- Alert Success --}}
        @if(session('success'))
            <div class="mb-8 bg-green-50 border-l-4 border-green-500 p-4 rounded-r-lg shadow-sm" data-aos="fade-up">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-green-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
                    </div>
                </div>
            </div>
        @endif

        {{-- Statistik Singkat --}}
        @php
            $totalLaporan = $laporans->total();
            $pending = \App\Models\Laporan::where('user_id', Auth::id())->where('status', 'Pending')->count();
            $proses = \App\Models\Laporan::where('user_id', Auth::id())->whereIn('status', ['Proses', 'Diproses'])->count();
            $selesai = \App\Models\Laporan::where('user_id', Auth::id())->where('status', 'Selesai')->count();
        @endphp

        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-6 mb-10">
            <div class="bg-white/85 backdrop-blur-md shadow-sm hover:shadow-md transition-shadow border border-gray-100 rounded-2xl p-6 text-gray-800" data-aos="fade-up" data-aos-delay="100">
                <div class="flex items-center gap-3 mb-2">
                    <div class="p-2 bg-blue-50 rounded-lg text-blue-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    </div>
                    <p class="text-gray-500 font-medium text-sm">Total Laporan</p>
                </div>
                <h3 class="text-3xl font-bold text-[#1e3a5f]">{{ $totalLaporan }}</h3>
            </div>
            
            <div class="bg-white/85 backdrop-blur-md shadow-sm hover:shadow-md transition-shadow border border-gray-100 rounded-2xl p-6 text-gray-800" data-aos="fade-up" data-aos-delay="200">
                <div class="flex items-center gap-3 mb-2">
                    <div class="p-2 bg-yellow-50 rounded-lg text-yellow-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <p class="text-gray-500 font-medium text-sm">Pending</p>
                </div>
                <h3 class="text-3xl font-bold text-yellow-500">{{ $pending }}</h3>
            </div>
            
            <div class="bg-white/85 backdrop-blur-md shadow-sm hover:shadow-md transition-shadow border border-gray-100 rounded-2xl p-6 text-gray-800" data-aos="fade-up" data-aos-delay="300">
                <div class="flex items-center gap-3 mb-2">
                    <div class="p-2 bg-purple-50 rounded-lg text-purple-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                    </div>
                    <p class="text-gray-500 font-medium text-sm">Diproses</p>
                </div>
                <h3 class="text-3xl font-bold text-purple-500">{{ $proses }}</h3>
            </div>
            
            <div class="bg-white/85 backdrop-blur-md shadow-sm hover:shadow-md transition-shadow border border-gray-100 rounded-2xl p-6 text-gray-800" data-aos="fade-up" data-aos-delay="400">
                <div class="flex items-center gap-3 mb-2">
                    <div class="p-2 bg-green-50 rounded-lg text-green-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <p class="text-gray-500 font-medium text-sm">Selesai</p>
                </div>
                <h3 class="text-3xl font-bold text-green-500">{{ $selesai }}</h3>
            </div>
        </div>

        {{-- Tabel Laporan --}}
        <div class="bg-white/95 backdrop-blur-md shadow-sm border border-gray-100 rounded-2xl overflow-hidden" data-aos="fade-up">
            @if($laporans->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100">
                        <thead class="bg-gray-50/80">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Kategori</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Lokasi</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Deskripsi</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Tanggal</th>
                                <th class="px-6 py-4 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-transparent">
                            @foreach($laporans as $laporan)
                                <tr class="hover:bg-blue-50/50 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                        <span class="px-3 py-1 bg-blue-100 text-blue-700 rounded-full text-xs font-medium border border-blue-200">
                                            {{ $laporan->kategori }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-600">
                                        <span class="flex items-start gap-1">
                                            <span class="text-gray-400 mt-0.5"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg></span>
                                            {{ Str::limit($laporan->lokasi, 30) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-600">
                                        {{ Str::limit($laporan->deskripsi, 50) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        @if($laporan->status === 'Pending')
                                            <span class="inline-flex items-center justify-center px-3 py-1 bg-yellow-100 text-yellow-700 rounded-full text-xs font-medium border border-yellow-200 min-w-[90px]">
                                                Pending
                                            </span>
                                        @elseif(in_array($laporan->status, ['Proses', 'Diproses']))
                                            <span class="inline-flex items-center justify-center px-3 py-1 bg-purple-100 text-purple-700 rounded-full text-xs font-medium border border-purple-200 min-w-[90px]">
                                                Diproses
                                            </span>
                                        @elseif($laporan->status === 'Selesai')
                                            <span class="inline-flex items-center justify-center px-3 py-1 bg-green-100 text-green-700 rounded-full text-xs font-medium border border-green-200 min-w-[90px]">
                                                Selesai
                                            </span>
                                        @else
                                            <span class="inline-flex items-center justify-center px-3 py-1 bg-red-100 text-red-700 rounded-full text-xs font-medium border border-red-200 min-w-[90px]">
                                                {{ $laporan->status }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $laporan->created_at->format('d M Y, H:i') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
                                        <a href="{{ route('user.laporan.show', $laporan->id) }}" 
                                           class="inline-flex items-center gap-1 px-4 py-2 bg-white text-blue-600 border border-blue-200 rounded-lg hover:bg-blue-50 transition-colors font-medium text-xs">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                            Detail
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-100">
                    {{ $laporans->links() }}
                </div>
            @else
                <div class="text-center py-20 px-6">
                    <div class="inline-flex items-center justify-center w-24 h-24 bg-blue-50 rounded-full mb-6">
                        <svg class="w-12 h-12 text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path></svg>
                    </div>
                    <h3 class="text-2xl font-bold text-[#1e3a5f] mb-3">Belum Ada Laporan</h3>
                    <p class="text-gray-500 mb-8 max-w-md mx-auto">Anda belum membuat laporan apapun. Mari mulai berpartisipasi dengan membuat laporan pertama Anda.</p>
                    <a href="{{ route('user.laporan.create') }}" 
                       class="sd-btn-register hover:-translate-y-1 transition-transform duration-300" style="display: inline-flex; align-items: center; justify-content: center; gap: 8px; font-weight: bold;">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                        </svg>
                        <span>Buat Laporan Pertama</span>
                    </a>
                </div>
            @endif
        </div>

    </div>
</div>

<script>
// Auto-hide success alert
document.addEventListener('DOMContentLoaded', function() {
    const alert = document.querySelector('.bg-green-50');
    if (alert) {
        setTimeout(() => {
            alert.style.transition = 'opacity 0.5s ease-out, transform 0.5s ease-out';
            alert.style.opacity = '0';
            alert.style.transform = 'translateY(-10px)';
            setTimeout(() => alert.remove(), 500);
        }, 5000);
    }
});
</script>
@endsection