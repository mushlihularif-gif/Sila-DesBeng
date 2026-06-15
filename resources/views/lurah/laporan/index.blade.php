@extends('layouts.lurah')

@section('title', 'Kelola Laporan')
@section('page-title', 'Kelola Semua Laporan')

@section('content')
<div class="space-y-6">
    
    {{-- Header --}}
    <div class="bg-gradient-to-r from-[#004635] to-[#003026] rounded-2xl p-6 shadow-2xl border-2 border-yellow-400/40" data-aos="fade-down">
        <div class="flex items-center gap-3 mb-2">
            <span class="text-4xl">📋</span>
            <h2 class="text-3xl font-bold text-yellow-400">Kelola Semua Laporan</h2>
        </div>
        <p class="text-gray-300 text-lg">Monitoring dan manajemen laporan dari semua RW</p>
    </div>

    {{-- Quick Stats --}}
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
    <p class="text-white/80 text-xs mb-1">Diteruskan</p>
    <p class="text-3xl font-bold text-white">{{ $stats['proses'] }}</p>
</div>
        <div class="bg-gradient-to-br from-red-500 to-red-700 rounded-xl p-4 shadow-xl" data-aos="fade-up" data-aos-delay="250">
            <p class="text-white/80 text-xs mb-1">Ditolak</p>
            <p class="text-3xl font-bold text-white">{{ $stats['ditolak'] }}</p>
        </div>
    </div>

    {{-- Filter & Search --}}
<div class="bg-gradient-to-br from-[#004635]/80 to-[#003026]/80 backdrop-blur-xl rounded-2xl p-6 shadow-2xl border-2 border-yellow-400/30" data-aos="fade-up">
    <div class="flex items-center gap-3 mb-5">
        <span class="text-3xl">🔍</span>
        <h3 class="text-yellow-400 font-bold text-xl">Filter & Pencarian</h3>
    </div>
    
    <form method="GET" action="{{ route('lurah.laporan.index') }}" class="grid grid-cols-1 md:grid-cols-6 gap-4">

        {{-- Filter RW --}}
        <div>
            <label class="text-white text-sm mb-2 block font-semibold">RW</label>
            <select name="rw"
                class="w-full bg-[#003026] border-2 border-yellow-400/40 rounded-xl px-3 py-2
                       text-white focus:border-yellow-400 focus:outline-none
                       transition-all appearance-none">
                <option value="">Semua RW</option>
                @foreach($rwList as $rw)
                    <option value="{{ $rw->rw }}" {{ request('rw') == $rw->rw ? 'selected' : '' }}>
                        RW {{ $rw->rw }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- Filter Status --}}
        <div>
            <label class="text-white text-sm mb-2 block font-semibold">Status</label>
            <select name="status"
                class="w-full bg-[#003026] border-2 border-yellow-400/40 rounded-xl px-3 py-2
                       text-white focus:border-yellow-400 focus:outline-none
                       transition-all appearance-none">
                <option value="">Semua Status</option>
                <option value="Pending" {{ request('status') == 'Pending' ? 'selected' : '' }}>Pending</option>
                <option value="Proses" {{ request('status') == 'Proses' ? 'selected' : '' }}>Proses</option>
                <option value="Selesai" {{ request('status') == 'Selesai' ? 'selected' : '' }}>Selesai</option>
                <option value="Proses" {{ request('status') == 'Proses' ? 'selected' : '' }}>
    Diteruskan
</option>

                <option value="Ditolak" {{ request('status') == 'Ditolak' ? 'selected' : '' }}>Ditolak</option>
            </select>
        </div>

        {{-- Filter Kategori --}}
        <div>
            <label class="text-white text-sm mb-2 block font-semibold">Kategori</label>
            <select name="kategori"
                class="w-full bg-[#003026] border-2 border-yellow-400/40 rounded-xl px-3 py-2
                       text-white focus:border-yellow-400 focus:outline-none
                       transition-all appearance-none">
                <option value="">Semua Kategori</option>
                @foreach($kategoriList as $kat)
                    <option value="{{ $kat->kategori }}" {{ request('kategori') == $kat->kategori ? 'selected' : '' }}>
                        {{ $kat->kategori }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- Search --}}
        <div class="md:col-span-2">
            <label class="text-white text-sm mb-2 block font-semibold">Pencarian</label>
            <input type="text" name="search" value="{{ request('search') }}"
                   placeholder="Cari laporan..."
                   class="w-full bg-[#003026] border-2 border-yellow-400/40 rounded-xl px-3 py-2
                          text-white placeholder-gray-400 focus:border-yellow-400
                          focus:outline-none transition-all">
        </div>

        {{-- Buttons --}}
        <div class="flex items-end gap-2">
            <button type="submit"
                class="flex-1 bg-gradient-to-r from-yellow-400 to-yellow-600
                       text-[#004635] font-bold py-2 px-4 rounded-xl
                       hover:scale-105 transition-all">
                Filter
            </button>
            <a href="{{ route('lurah.laporan.index') }}"
               class="bg-gradient-to-r from-gray-600 to-gray-700
                      text-white font-bold py-2 px-3 rounded-xl
                      hover:scale-105 transition-all"
               title="Reset">
                ↻
            </a>
        </div>

    </form>
</div>


    {{-- Tabel Laporan --}}
    <div class="bg-white/10 backdrop-blur-xl rounded-2xl p-6 shadow-2xl border-2 border-yellow-400/20" data-aos="fade-up">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-yellow-400 font-bold text-2xl flex items-center gap-2">
                <span class="text-3xl">📋</span>
                Daftar Laporan
            </h3>
            <span class="px-4 py-2 bg-yellow-400/20 text-yellow-300 rounded-xl text-sm font-bold">
                {{ $laporans->total() }} Laporan
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b-2 border-yellow-400/30">
                        <th class="text-left text-yellow-400 font-bold py-4 px-4 text-sm">ID</th>
                        <th class="text-left text-yellow-400 font-bold py-4 px-4 text-sm">Laporan</th>
                        <th class="text-left text-yellow-400 font-bold py-4 px-4 text-sm">Pelapor</th>
                        <th class="text-left text-yellow-400 font-bold py-4 px-4 text-sm">RT/RW</th>
                        <th class="text-left text-yellow-400 font-bold py-4 px-4 text-sm">Kategori</th>
                        <th class="text-left text-yellow-400 font-bold py-4 px-4 text-sm">Status</th>
                        <th class="text-left text-yellow-400 font-bold py-4 px-4 text-sm">Tanggal</th>
                        <th class="text-center text-yellow-400 font-bold py-4 px-4 text-sm">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($laporans as $laporan)
                        <tr class="border-b border-gray-700/30 hover:bg-white/5 transition-all">
                            <td class="py-4 px-4">
                                <span class="text-white font-bold">#{{ $laporan->id }}</span>
                            </td>
                            <td class="py-4 px-4">
                                <div class="text-white font-semibold mb-1">{{ Str::limit($laporan->nama, 40) }}</div>
                                <div class="text-gray-400 text-xs">{{ Str::limit($laporan->deskripsi, 50) }}</div>
                            </td>
                            <td class="py-4 px-4">
                                <div class="text-white text-sm font-semibold">{{ $laporan->user->name ?? 'N/A' }}</div>
                                <div class="text-gray-400 text-xs">{{ $laporan->user->email ?? '-' }}</div>
                            </td>
                            <td class="py-4 px-4">
                                <div class="text-white font-semibold">RT {{ $laporan->rt ?? '-' }}</div>
                                <div class="text-yellow-400 text-xs font-bold">RW {{ $laporan->rw ?? '-' }}</div>
                            </td>
                            <td class="py-4 px-4">
                                <span class="px-3 py-1 bg-blue-500/30 text-blue-300 rounded-lg text-xs font-semibold">
                                    {{ $laporan->kategori }}
                                </span>
                            </td>
                            <td class="py-4 px-4">
                                @php
                                    $statusConfig = [
                                        'Pending' => ['bg' => 'bg-orange-500/30', 'text' => 'text-orange-300', 'icon' => '⏳'],
                                        'Proses' => ['bg' => 'bg-cyan-500/30', 'text' => 'text-cyan-300', 'icon' => '⚙️'],
                                        'Selesai' => ['bg' => 'bg-green-500/30', 'text' => 'text-green-300', 'icon' => '✅'],
                                        'Proses' => [
    'bg' => 'bg-purple-500/30',
    'text' => 'text-purple-300',
    'icon' => '📤',
    'label' => 'Diteruskan'
],

                                        'Ditolak' => ['bg' => 'bg-red-500/30', 'text' => 'text-red-300', 'icon' => '❌']
                                    ];
                                    $config = $statusConfig[$laporan->status] ?? ['bg' => 'bg-gray-500/30', 'text' => 'text-gray-300', 'icon' => '❓'];
                                @endphp
                                <span class="px-3 py-1 rounded-lg text-xs font-bold {{ $config['bg'] }} {{ $config['text'] }} inline-flex items-center gap-1">
                                    <span>{{ $config['icon'] }}</span>
                                    {{ $laporan->status }}
                                </span>
                            </td>
                            <td class="py-4 px-4">
                                <div class="text-white text-sm font-semibold">{{ $laporan->created_at->format('d/m/Y') }}</div>
                                <div class="text-gray-400 text-xs">{{ $laporan->created_at->format('H:i') }} WIB</div>
                            </td>
                            <td class="py-4 px-4 text-center">
                                <a href="{{ route('lurah.laporan.show', $laporan->id) }}" 
                                   class="inline-flex items-center gap-2 bg-gradient-to-r from-yellow-400 to-yellow-600 text-[#004635] font-bold py-2 px-4 rounded-xl hover:scale-105 hover:shadow-2xl transition-all text-sm">
                                    <span>👁️</span>
                                    Detail
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-16">
                                <div class="text-7xl mb-4">📭</div>
                                <p class="text-gray-400 text-xl font-semibold">Tidak ada laporan</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($laporans->hasPages())
        <div class="mt-6 flex items-center justify-between border-t border-yellow-400/20 pt-6">
            <div class="text-gray-300 text-sm">
                Menampilkan <span class="font-bold text-yellow-400">{{ $laporans->firstItem() }}</span> - 
                <span class="font-bold text-yellow-400">{{ $laporans->lastItem() }}</span> dari 
                <span class="font-bold text-yellow-400">{{ $laporans->total() }}</span> laporan
            </div>
            <div>
                {{ $laporans->links() }}
            </div>
        </div>
        @endif
    </div>

</div>
@endsection