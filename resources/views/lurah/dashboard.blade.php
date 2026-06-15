@extends('layouts.lurah')

@section('title', 'Dashboard Lurah')
@section('page-title', 'Dashboard Monitoring & Evaluasi')

@section('content')
    <div class="space-y-8">

        {{-- Welcome Banner Premium --}}
        <div class="bg-gradient-to-r from-[#004635] to-[#003026] rounded-2xl p-8 shadow-2xl border-2 border-yellow-400/40 relative overflow-hidden"
            data-aos="fade-down">
            {{-- Background pattern --}}
            <div class="absolute inset-0 opacity-5">
                <img src="{{ asset('assets/img/Melayu1-desktop.png') }}" class="w-full h-full object-cover" alt="Pattern">
            </div>

            <div class="relative z-10">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <div class="mb-3">
                            <p class="text-yellow-400 text-sm font-semibold mb-2">Selamat Datang</p>
                            <h2 class="text-4xl font-bold text-white mb-1">{{ auth()->user()->name }}</h2>
                        </div>
                        <p class="text-gray-300 text-lg mb-4 max-w-2xl">Sistem Monitoring & Evaluasi Pelaporan Kelurahan
                            Sungai Pakning</p>
                        <div class="flex items-center gap-6 text-sm text-gray-300">
                            <span class="flex items-center gap-2 bg-white/10 px-3 py-2 rounded-lg">
                                <span class="text-yellow-400">📅</span>
                                {{ now()->isoFormat('dddd, D MMMM Y') }}
                            </span>
                            <span class="flex items-center gap-2 bg-white/10 px-3 py-2 rounded-lg">
                                <span class="text-yellow-400">⏰</span>
                                <span class="live-time">{{ now()->format('H:i') }}</span> WIB
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Statistik Cards Premium --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-6">
            {{-- Total Laporan --}}
            <div class="bg-gradient-to-br from-blue-500 to-blue-700 rounded-2xl p-6 shadow-2xl hover:scale-105 transition-all"
                data-aos="fade-up">
                <div class="flex items-center justify-between mb-4">
                    <div class="text-5xl">📊</div>
                    <span class="px-3 py-1 bg-white/20 rounded-lg text-white text-xs font-bold backdrop-blur">TOTAL</span>
                </div>
                <h3 class="text-white text-sm mb-2 font-semibold">Total Laporan</h3>
                <p class="text-5xl font-bold text-white mb-2">{{ $stats['total_laporan'] }}</p>
                <p class="text-white/70 text-xs">Semua laporan</p>
            </div>

            {{-- Pending --}}
            <div class="bg-gradient-to-br from-orange-500 to-orange-700 rounded-2xl p-6 shadow-2xl hover:scale-105 transition-all"
                data-aos="fade-up" data-aos-delay="50">
                <div class="flex items-center justify-between mb-4">
                    <div class="text-5xl">⏳</div>
                    <span class="px-3 py-1 bg-white/20 rounded-lg text-white text-xs font-bold backdrop-blur">PENDING</span>
                </div>
                <h3 class="text-white text-sm mb-2 font-semibold">Menunggu</h3>
                <p class="text-5xl font-bold text-white mb-2">{{ $stats['pending'] }}</p>
                <p class="text-white/70 text-xs">Belum ditinjau</p>
            </div>

            {{-- Proses --}}
            <div class="bg-gradient-to-br from-cyan-500 to-cyan-700 rounded-2xl p-6 shadow-2xl hover:scale-105 transition-all"
                data-aos="fade-up" data-aos-delay="100">
                <div class="flex items-center justify-between mb-4">
                    <div class="text-5xl">⚙️</div>
                    <span class="px-3 py-1 bg-white/20 rounded-lg text-white text-xs font-bold backdrop-blur">PROSES</span>
                </div>
                <h3 class="text-white text-sm mb-2 font-semibold">Diproses</h3>
                <p class="text-5xl font-bold text-white mb-2">{{ $stats['proses'] }}</p>
                <p class="text-white/70 text-xs">Dalam penanganan</p>
            </div>

            {{-- Selesai --}}
            <div class="bg-gradient-to-br from-green-500 to-green-700 rounded-2xl p-6 shadow-2xl hover:scale-105 transition-all"
                data-aos="fade-up" data-aos-delay="150">
                <div class="flex items-center justify-between mb-4">
                    <div class="text-5xl">✅</div>
                    <span class="px-3 py-1 bg-white/20 rounded-lg text-white text-xs font-bold backdrop-blur">SELESAI</span>
                </div>
                <h3 class="text-white text-sm mb-2 font-semibold">Selesai</h3>
                <p class="text-5xl font-bold text-white mb-2">{{ $stats['selesai'] }}</p>
                <p class="text-white/70 text-xs">Berhasil ditangani</p>
            </div>

            {{-- Diteruskan --}}
            <div class="bg-gradient-to-br from-purple-500 to-purple-700 rounded-2xl p-6 shadow-2xl hover:scale-105 transition-all"
                data-aos="fade-up" data-aos-delay="200">
                <div class="flex items-center justify-between mb-4">
                    <div class="text-5xl">📤</div>
                    <span
                        class="px-3 py-1 bg-white/20 rounded-lg text-white text-xs font-bold backdrop-blur">ESKALASI</span>
                </div>
                <h3 class="text-white text-sm mb-2 font-semibold">Diteruskan</h3>
<p class="text-5xl font-bold text-white mb-2">{{ $stats['proses'] }}</p>
<p class="text-white/70 text-xs">Ke Lurah</p>

            </div>

            {{-- Ditolak --}}
            <div class="bg-gradient-to-br from-red-500 to-red-700 rounded-2xl p-6 shadow-2xl hover:scale-105 transition-all"
                data-aos="fade-up" data-aos-delay="250">
                <div class="flex items-center justify-between mb-4">
                    <div class="text-5xl">❌</div>
                    <span class="px-3 py-1 bg-white/20 rounded-lg text-white text-xs font-bold backdrop-blur">DITOLAK</span>
                </div>
                <h3 class="text-white text-sm mb-2 font-semibold">Ditolak</h3>
                <p class="text-5xl font-bold text-white mb-2">{{ $stats['ditolak'] }}</p>
                <p class="text-white/70 text-xs">Tidak valid</p>
            </div>
        </div>

        {{-- Charts Section Premium --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            {{-- Laporan per RW --}}
            <div class="bg-white/10 backdrop-blur-xl rounded-2xl p-6 shadow-2xl border-2 border-yellow-400/20"
                data-aos="fade-right">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-yellow-400 font-bold text-xl flex items-center gap-2">
                        <span class="text-2xl">🏘️</span>
                        Laporan per RW
                    </h3>
                    <span class="px-3 py-1 bg-yellow-400/20 text-yellow-300 rounded-lg text-xs font-bold">
                        {{ count($laporanPerRw) }} RW
                    </span>
                </div>
                <div class="space-y-4">
                    @forelse($laporanPerRw as $rw)
                        <div class="bg-white/5 rounded-xl p-4 hover:bg-white/10 transition-all border border-yellow-400/10">
                            <div class="flex items-center justify-between mb-3">
                                <span class="text-white font-bold text-lg">RW {{ $rw->rw }}</span>
                                <span class="text-yellow-400 font-bold text-xl">{{ $rw->total }}</span>
                            </div>
                            <div class="w-full bg-gray-700 rounded-full h-3 overflow-hidden">
                                @php
                                    $percentage =
                                        $stats['total_laporan'] > 0 ? ($rw->total / $stats['total_laporan']) * 100 : 0;
                                @endphp
                                <div class="bg-gradient-to-r from-yellow-400 via-yellow-500 to-yellow-600 h-3 rounded-full transition-all duration-1000 flex items-center justify-end pr-2"
                                    style="width: {{ $percentage }}%">
                                    @if ($percentage > 15)
                                        <span class="text-xs font-bold text-[#004635]">{{ round($percentage) }}%</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-8 text-gray-400">
                            <p class="text-4xl mb-2">📭</p>
                            <p>Belum ada laporan</p>
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- Laporan per Kategori --}}
            <div class="bg-white/10 backdrop-blur-xl rounded-2xl p-6 shadow-2xl border-2 border-yellow-400/20"
                data-aos="fade-left">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-yellow-400 font-bold text-xl flex items-center gap-2">
                        <span class="text-2xl">🏷️</span>
                        Laporan per Kategori
                    </h3>
                    <span class="px-3 py-1 bg-blue-400/20 text-blue-300 rounded-lg text-xs font-bold">
                        {{ count($laporanPerKategori) }} Kategori
                    </span>
                </div>
                <div class="space-y-4">
                    @forelse($laporanPerKategori as $kategori)
                        <div class="bg-white/5 rounded-xl p-4 hover:bg-white/10 transition-all border border-blue-400/10">
                            <div class="flex items-center justify-between mb-3">
                                <span class="text-white font-bold">{{ $kategori->kategori }}</span>
                                <span class="text-blue-400 font-bold text-xl">{{ $kategori->total }}</span>
                            </div>
                            <div class="w-full bg-gray-700 rounded-full h-3 overflow-hidden">
                                @php
                                    $percentage =
                                        $stats['total_laporan'] > 0
                                            ? ($kategori->total / $stats['total_laporan']) * 100
                                            : 0;
                                @endphp
                                <div class="bg-gradient-to-r from-blue-400 via-blue-500 to-blue-600 h-3 rounded-full transition-all duration-1000 flex items-center justify-end pr-2"
                                    style="width: {{ $percentage }}%">
                                    @if ($percentage > 15)
                                        <span class="text-xs font-bold text-white">{{ round($percentage) }}%</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-8 text-gray-400">
                            <p class="text-4xl mb-2">📁</p>
                            <p>Belum ada kategori</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Filter & Search Premium --}}
        <div class="bg-gradient-to-br from-[#004635]/80 to-[#003026]/80 backdrop-blur-xl rounded-2xl p-6 shadow-2xl border-2 border-yellow-400/30"
            data-aos="fade-up">
            <div class="flex items-center gap-3 mb-6">
                <span class="text-4xl">🔍</span>
                <h3 class="text-yellow-400 font-bold text-2xl">Filter & Pencarian Laporan</h3>
            </div>

            <form method="GET" action="{{ route('lurah.dashboard') }}" class="grid grid-cols-1 md:grid-cols-5 gap-4">
                {{-- Filter Status --}}
                <div>
                    <label class="text-white text-sm mb-2 block font-semibold">Status</label>
                    <select name="status"
                        class="w-full bg-white/10 border-2 border-yellow-400/30 rounded-xl px-3 py-2 text-white focus:border-yellow-400 transition-all">
                        <option value="" class="bg-[#003026] text-white">Semua Status</option>
                        <option value="Pending" class="bg-[#003026] text-white"
                            {{ request('status') == 'Pending' ? 'selected' : '' }}>
                            ⏳ Pending
                        </option>
                        <option value="Proses" class="bg-[#003026] text-white"
                            {{ request('status') == 'Proses' ? 'selected' : '' }}>
                            ⚙️ Proses
                        </option>
                        <option value="Selesai" class="bg-[#003026] text-white"
                            {{ request('status') == 'Selesai' ? 'selected' : '' }}>
                            ✅ Selesai
                        </option>
                        <option value="Proses" {{ request('status') == 'Proses' ? 'selected' : '' }}>
    📤 Diteruskan
</option>

                        <option value="Ditolak" class="bg-[#003026] text-white"
                            {{ request('status') == 'Ditolak' ? 'selected' : '' }}>
                            ❌ Ditolak
                        </option>
                    </select>
                </div>

                {{-- Filter Kategori --}}
                <div>
                    <label class="text-white text-sm mb-2 block font-semibold">Kategori</label>
                    <select name="kategori"
                        class="w-full bg-white/10 border-2 border-yellow-400/30 rounded-xl px-3 py-2 text-white focus:border-yellow-400 transition-all">
                        <option value="" class="bg-[#003026] text-white">Semua</option>
                        @foreach ($kategoriList as $kat)
                            <option value="{{ $kat->kategori }}" class="bg-[#003026] text-white"
                                {{ request('kategori') == $kat->kategori ? 'selected' : '' }}>
                                {{ $kat->kategori }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Filter RW --}}
                <div>
                    <label class="text-white text-sm mb-2 block font-semibold">RW</label>
                    <select name="rw"
                        class="w-full bg-white/10 border-2 border-yellow-400/30 rounded-xl px-3 py-2 text-white focus:border-yellow-400 transition-all">
                        <option value="" class="bg-[#003026] text-white">Semua RW</option>
                        @foreach ($rwList as $rw)
                            <option value="{{ $rw->rw }}" class="bg-[#003026] text-white"
                                {{ request('rw') == $rw->rw ? 'selected' : '' }}>
                                RW {{ $rw->rw }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Search --}}
                <div>
                    <label class="text-white text-sm mb-2 block font-semibold">Pencarian</label>
                    <input type="text" name="search" value="{{ request('search') }}"
                        placeholder="🔎 Cari laporan..."
                        class="w-full bg-white/10 border-2 border-yellow-400/30 rounded-xl px-4 py-3 text-white placeholder-gray-400 focus:border-yellow-400 focus:ring-2 focus:ring-yellow-400/50 transition-all">
                </div>

                <div class="flex items-end gap-2">
                    <button type="submit"
                        class="flex-1 bg-gradient-to-r from-yellow-400 to-yellow-600 text-[#004635] font-bold py-3 px-6 rounded-xl hover:scale-105 hover:shadow-2xl transition-all flex items-center justify-center gap-2">
                        <span>🔍</span>
                        Filter
                    </button>

                    {{--  PDF Export - --}}
<a href="{{ url()->route('lurah.laporan.export.dashboard', request()->query()) }}"
   class="bg-gradient-to-r from-red-500 to-red-700 text-white font-bold py-3 px-3 rounded-xl hover:scale-105 hover:shadow-2xl transition-all flex items-center justify-center gap-2"
   title="Export to PDF">
    📄 PDF
</a>
                </div>
            </form>
        </div>

        {{-- Tabel Laporan Premium --}}
        <div class="bg-white/10 backdrop-blur-xl rounded-2xl p-6 shadow-2xl border-2 border-yellow-400/20"
            data-aos="fade-up">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-yellow-400 font-bold text-2xl flex items-center gap-2">
                    <span class="text-3xl">📋</span>
                    Daftar Laporan Terbaru
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
                                    <div class="text-gray-400 text-xs">{{ Str::limit($laporan->deskripsi, 60) }}</div>
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
                                            'Pending' => [
                                                'bg' => 'bg-orange-500/30',
                                                'text' => 'text-orange-300',
                                                'icon' => '⏳',
                                            ],
                                            'Proses' => [
                                                'bg' => 'bg-cyan-500/30',
                                                'text' => 'text-cyan-300',
                                                'icon' => '⚙️',
                                            ],
                                            'Selesai' => [
                                                'bg' => 'bg-green-500/30',
                                                'text' => 'text-green-300',
                                                'icon' => '✅',
                                            ],
                                            'Dilanjutkan' => [
    'bg' => 'bg-purple-500/30',
    'text' => 'text-purple-300',
    'icon' => '📤',
    'label' => 'Diteruskan'
],

                                            'Ditolak' => [
                                                'bg' => 'bg-red-500/30',
                                                'text' => 'text-red-300',
                                                'icon' => '❌',
                                            ],
                                        ];
                                        $config = $statusConfig[$laporan->status] ?? [
                                            'bg' => 'bg-gray-500/30',
                                            'text' => 'text-gray-300',
                                            'icon' => '❓',
                                        ];
                                    @endphp
                                    <span
                                        class="px-3 py-1 rounded-lg text-xs font-bold {{ $config['bg'] }} {{ $config['text'] }} inline-flex items-center gap-1">
                                        <span>{{ $config['icon'] }}</span>
                                        {{ $laporan->status }}
                                    </span>
                                </td>
                                <td class="py-4 px-4">
                                    <div class="text-white text-sm font-semibold">
                                        {{ $laporan->created_at->format('d/m/Y') }}</div>
                                    <div class="text-gray-400 text-xs">{{ $laporan->created_at->format('H:i') }} WIB</div>
                                </td>
                                <td class="py-4 px-4 text-center">
                                    <a href="{{ route('lurah.laporan.show', $laporan->id) }}"
                                        class="inline-flex items-center gap-2 bg-gradient-to-r from-yellow-400 to-yellow-600 text-[#004635] font-bold py-2 px-4 rounded-xl hover:scale-105 hover:shadow-2xl transition-all">
                                        <span>👁️</span>
                                        Detail
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-16">
                                    <div class="text-7xl mb-4">📭</div>
                                    <p class="text-gray-400 text-xl font-semibold">Tidak ada laporan yang ditemukan</p>
                                    <p class="text-gray-500 text-sm mt-2">Coba ubah filter atau kata kunci pencarian</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination Premium --}}
            @if ($laporans->hasPages())
                <div
                    class="mt-6 flex flex-col md:flex-row items-center justify-between gap-4 border-t border-yellow-400/20 pt-6">
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

    @push('scripts')
        <script>
            // Live time update
            setInterval(() => {
                const now = new Date();
                const timeString = now.toLocaleTimeString('id-ID', {
                    hour: '2-digit',
                    minute: '2-digit'
                });
                document.querySelectorAll('.live-time').forEach(el => {
                    el.textContent = timeString;
                });
            }, 1000);
        </script>
    @endpush
@endsection
