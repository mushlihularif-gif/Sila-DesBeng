@extends('layouts.lurah')

@section('title', 'Detail Laporan #' . $laporan->id)
@section('page-title', 'Detail Laporan #' . $laporan->id)

@section('content')
    <div class="space-y-6">

        {{-- Back Button --}}
        <div data-aos="fade-down">
            <a href="{{ route('lurah.laporan.index') }}"
                class="inline-flex items-center gap-2 bg-white/10 hover:bg-white/20 text-white px-5 py-3 rounded-xl transition-all border-2 border-yellow-400/30 font-semibold">
                <span class="text-xl">←</span>
                <span>Kembali ke Daftar Laporan</span>
            </a>
        </div>

        {{-- Header Card --}}
        <div class="bg-gradient-to-r from-[#004635] to-[#003026] rounded-2xl p-8 shadow-2xl border-2 border-yellow-400/40 relative overflow-hidden"
            data-aos="fade-down">
            
            {{-- ✅ FIX: Header Buttons dengan Route yang Benar --}}
            <div class="flex items-center justify-between mb-6 relative z-20">
                <h1 class="text-2xl font-bold text-yellow-400">📋 Detail Laporan #{{ $laporan->id }}</h1>
                <div class="flex gap-2">
                    {{-- ✅ CORRECT: Gunakan route lurah, bukan admin --}}
<a href="{{ route('lurah.laporan.export.detail', $laporan->id) }}"
   class="inline-flex items-center gap-2 px-4 py-2 bg-red-500 hover:bg-red-600 text-white rounded-lg font-bold">
    <span>📄</span>
    <span>Export PDF</span>
</a>


                    <a href="{{ route('lurah.dashboard') }}"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-[#004635] text-yellow-400 rounded-lg hover:bg-[#003026] transition font-bold">
                        <span>←</span>
                        <span>Kembali</span>
                    </a>
                </div>
            </div>
            
            {{-- Background pattern --}}
            <div class="absolute inset-0 opacity-5">
                <img src="{{ asset('assets/img/Melayu1-desktop.png') }}" class="w-full h-full object-cover" alt="Pattern">
            </div>

            <div class="relative z-10">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <div class="flex items-center gap-4 mb-4">
                            <span class="text-6xl">📋</span>
                            <div>
                                <div class="flex items-center gap-3 mb-2">
                                    <span
                                        class="px-3 py-1 bg-yellow-400/20 text-yellow-300 rounded-lg text-xs font-bold border border-yellow-400/40">
                                        ID: #{{ $laporan->id }}
                                    </span>
                                    <span class="px-3 py-1 bg-white/10 text-gray-300 rounded-lg text-xs font-semibold">
                                        {{ $laporan->created_at->diffForHumans() }}
                                    </span>
                                </div>
                                <h2 class="text-4xl font-bold text-white mb-3">{{ $laporan->nama }}</h2>
                            </div>
                        </div>

                        <div class="flex flex-wrap items-center gap-3">
                            @php
                                $statusConfig = [
                                    'Pending' => ['bg' => 'bg-orange-500', 'text' => 'text-white', 'icon' => '⏳', 'border' => 'border-orange-300'],
                                    'Proses' => ['bg' => 'bg-cyan-500', 'text' => 'text-white', 'icon' => '⚙️', 'border' => 'border-cyan-300'],
                                    'Selesai' => ['bg' => 'bg-green-500', 'text' => 'text-white', 'icon' => '✅', 'border' => 'border-green-300'],
                                    'Ditolak' => ['bg' => 'bg-red-500', 'text' => 'text-white', 'icon' => '❌', 'border' => 'border-red-300'],
                                ];
                                $config = $statusConfig[$laporan->status] ?? ['bg' => 'bg-gray-500', 'text' => 'text-white', 'icon' => '❓', 'border' => 'border-gray-300'];
                            @endphp
                            <span class="px-5 py-2 rounded-xl font-bold {{ $config['bg'] }} {{ $config['text'] }} border-2 {{ $config['border'] }} inline-flex items-center gap-2 shadow-lg text-base">
                                <span class="text-xl">{{ $config['icon'] }}</span>
                                {{ $laporan->status }}
                            </span>
                            <span class="px-5 py-2 bg-blue-500/30 text-blue-300 rounded-xl font-bold border-2 border-blue-400/40 text-base">
                                {{ $laporan->kategori }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- Main Content (Kiri - 2 kolom) --}}
            <div class="lg:col-span-2 space-y-6">

                {{-- Deskripsi Laporan --}}
                <div class="bg-white/10 backdrop-blur-xl rounded-2xl p-6 shadow-2xl border-2 border-yellow-400/20" data-aos="fade-up">
                    <h3 class="text-yellow-400 font-bold text-xl mb-4 flex items-center gap-2">
                        <span class="text-3xl">📝</span>
                        Deskripsi Laporan
                    </h3>
                    <div class="text-gray-200 leading-relaxed text-base">
                        {{ $laporan->deskripsi }}
                    </div>
                </div>

                {{-- Lokasi --}}
                @if ($laporan->lokasi)
                    <div class="bg-white/10 backdrop-blur-xl rounded-2xl p-6 shadow-2xl border-2 border-yellow-400/20" data-aos="fade-up">
                        <h3 class="text-yellow-400 font-bold text-xl mb-4 flex items-center gap-2">
                            <span class="text-3xl">📍</span>
                            Lokasi Kejadian
                        </h3>
                        <p class="text-gray-200 text-base font-semibold">{{ $laporan->lokasi }}</p>
                    </div>
                @endif

                {{-- Foto Laporan --}}
                @if ($laporan->bukti || $laporan->bukti)
                    <div class="bg-white/10 backdrop-blur-xl rounded-2xl p-6 shadow-2xl border-2 border-yellow-400/20" data-aos="fade-up">
                        <h3 class="text-yellow-400 font-bold text-xl mb-4 flex items-center gap-2">
                            <span class="text-3xl">📷</span>
                            Dokumentasi Foto
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @php
                                $fotoField = $laporan->bukti ?? $laporan->bukti;
                                $photos = is_array(json_decode($fotoField, true)) ? json_decode($fotoField, true) : [$fotoField];
                            @endphp
                            @foreach ($photos as $foto)
                                <div class="relative group">
                                    <img src="{{ asset('storage/' . $foto) }}" alt="Foto Laporan"
                                        class="w-full h-64 object-cover rounded-xl border-2 border-yellow-400/30 hover:border-yellow-400 transition-all cursor-pointer"
                                        onclick="window.open(this.src, '_blank')">
                                    <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-all rounded-xl flex items-center justify-center">
                                        <span class="text-white text-3xl">🔍</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Catatan dari Lurah/Admin --}}
                @if ($laporan->catatan_admin)
                    <div class="bg-gradient-to-br from-yellow-500/20 to-yellow-700/20 backdrop-blur-xl rounded-2xl p-6 shadow-2xl border-2 border-yellow-400/40" data-aos="fade-up">
                        <h3 class="text-yellow-400 font-bold text-xl mb-4 flex items-center gap-2">
                            <span class="text-3xl">👑</span>
                            Catatan dari Lurah
                        </h3>
                        <div class="bg-white/10 rounded-lg p-4 border border-yellow-400/30">
                            <p class="text-gray-200 leading-relaxed">{{ $laporan->catatan_admin }}</p>
                        </div>
                    </div>
                @endif

            </div>

            {{-- Sidebar Info (Kanan - 1 kolom) --}}
            <div class="space-y-6">

                {{-- Info Pelapor --}}
                <div class="bg-white/10 backdrop-blur-xl rounded-2xl p-6 shadow-2xl border-2 border-yellow-400/20" data-aos="fade-left">
                    <h3 class="text-yellow-400 font-bold text-lg mb-4 flex items-center gap-2">
                        <span class="text-2xl">👤</span>
                        Info Pelapor
                    </h3>
                    <div class="space-y-4">
                        <div class="bg-white/5 rounded-lg p-3 border border-yellow-400/10">
                            <p class="text-gray-400 text-xs mb-1">Nama Lengkap</p>
                            <p class="text-white font-bold text-base">{{ $laporan->user->name ?? $laporan->nama ?? 'N/A' }}</p>
                        </div>
                        <div class="bg-white/5 rounded-lg p-3 border border-yellow-400/10">
                            <p class="text-gray-400 text-xs mb-1">Email</p>
                            <p class="text-white text-sm">{{ $laporan->user->email ?? 'N/A' }}</p>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div class="bg-white/5 rounded-lg p-3 border border-yellow-400/10">
                                <p class="text-gray-400 text-xs mb-1">RT</p>
                                <p class="text-white font-bold text-lg">{{ $laporan->rt ?? '-' }}</p>
                            </div>
                            <div class="bg-white/5 rounded-lg p-3 border border-yellow-400/10">
                                <p class="text-gray-400 text-xs mb-1">RW</p>
                                <p class="text-yellow-400 font-bold text-lg">{{ $laporan->rw ?? '-' }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Info Waktu --}}
                <div class="bg-white/10 backdrop-blur-xl rounded-2xl p-6 shadow-2xl border-2 border-yellow-400/20" data-aos="fade-left" data-aos-delay="100">
                    <h3 class="text-yellow-400 font-bold text-lg mb-4 flex items-center gap-2">
                        <span class="text-2xl">⏰</span>
                        Info Waktu
                    </h3>
                    <div class="space-y-4">
                        <div class="bg-white/5 rounded-lg p-3 border border-yellow-400/10">
                            <p class="text-gray-400 text-xs mb-1">Tanggal Dibuat</p>
                            <p class="text-white font-semibold">{{ $laporan->created_at->format('d F Y') }}</p>
                            <p class="text-gray-400 text-xs mt-1">{{ $laporan->created_at->format('H:i') }} WIB</p>
                        </div>
                        <div class="bg-white/5 rounded-lg p-3 border border-yellow-400/10">
                            <p class="text-gray-400 text-xs mb-1">Terakhir Update</p>
                            <p class="text-white font-semibold">{{ $laporan->updated_at->format('d F Y') }}</p>
                            <p class="text-gray-400 text-xs mt-1">{{ $laporan->updated_at->format('H:i') }} WIB</p>
                        </div>
                        @if ($laporan->status == 'Selesai')
                            @php
    $totalSeconds = $laporan->created_at->diffInSeconds($laporan->updated_at);

    $days = intdiv($totalSeconds, 86400);
    $hours = intdiv($totalSeconds % 86400, 3600);
    $minutes = intdiv($totalSeconds % 3600, 60);

    if ($days > 0) {
        $waktuPenyelesaian = $days . ' hari';
        if ($hours > 0) {
            $waktuPenyelesaian .= ' ' . $hours . ' jam';
        }
    } elseif ($hours > 0) {
        $waktuPenyelesaian = $hours . ' jam';
        if ($minutes > 0) {
            $waktuPenyelesaian .= ' ' . $minutes . ' menit';
        }
    } else {
        $waktuPenyelesaian = $minutes . ' menit';
    }
@endphp

                            <div class="bg-green-500/20 rounded-lg p-3 border-2 border-green-400/40">
                                <p class="text-green-300 text-xs mb-1 font-semibold">⏱️ Waktu Penyelesaian</p>
                                <p class="text-green-400 font-bold text-2xl">{{ $waktuPenyelesaian }}</p>
                                <p class="text-green-300 text-xs mt-1">
                                    Dari {{ $laporan->created_at->format('d/m/Y H:i') }} s/d {{ $laporan->updated_at->format('d/m/Y H:i') }}
                                </p>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Form Update Status Lurah --}}
                <div class="bg-gradient-to-br from-yellow-500/20 to-yellow-700/20 backdrop-blur-xl rounded-2xl p-6 shadow-2xl border-2 border-yellow-400/40" data-aos="fade-left" data-aos-delay="200">
                    <h3 class="text-yellow-400 font-bold text-lg mb-4 flex items-center gap-2">
                        <span class="text-2xl">👑</span>
                        Update Status (Lurah)
                    </h3>
                    <form method="POST" action="{{ route('lurah.laporan.updateStatus', $laporan->id) }}">
                        @csrf

                        <div class="mb-4">
                            <label class="text-white text-sm mb-2 block font-semibold">Ubah Status</label>
                            <select name="status" required
                                class="w-full bg-white/10 border-2 border-yellow-400/40 rounded-xl px-4 py-3 text-white font-semibold focus:border-yellow-400 focus:ring-2 focus:ring-yellow-400/50 transition-all">
                                <option value="Pending" class="bg-[#003026]" {{ $laporan->status == 'Pending' ? 'selected' : '' }}>⏳ Pending</option>
                                <option value="Proses" class="bg-[#003026]" {{ $laporan->status == 'Proses' ? 'selected' : '' }}>⚙️ Proses</option>
                                <option value="Selesai" class="bg-[#003026]" {{ $laporan->status == 'Selesai' ? 'selected' : '' }}>✅ Selesai</option>
                                <option value="Diteruskan" class="bg-[#003026]" {{ $laporan->status == 'Diteruskan' ? 'selected' : '' }}>📤 Diteruskan</option>
                                <option value="Ditolak" class="bg-[#003026]" {{ $laporan->status == 'Ditolak' ? 'selected' : '' }}>❌ Ditolak</option>
                            </select>
                        </div>

                        <div class="mb-5">
                            <label class="text-white text-sm mb-2 block font-semibold">Catatan Lurah (Opsional)</label>
                            <textarea name="catatan_admin" rows="5"
                                class="w-full bg-white/10 border-2 border-yellow-400/40 rounded-xl px-4 py-3 text-white placeholder-gray-400 focus:border-yellow-400 focus:ring-2 focus:ring-yellow-400/50 transition-all"
                                placeholder="Tambahkan catatan atau arahan dari Lurah...">{{ $laporan->catatan_admin }}</textarea>
                            <p class="text-gray-400 text-xs mt-2">Catatan ini akan terlihat oleh RW, RT, dan Warga</p>
                        </div>

                        <button type="submit"
                            class="w-full bg-gradient-to-r from-yellow-400 to-yellow-600 text-[#004635] font-bold py-4 rounded-xl hover:scale-105 hover:shadow-2xl transition-all flex items-center justify-center gap-2 text-lg">
                            <span class="text-xl">💾</span>
                            <span>Simpan Perubahan</span>
                        </button>
                    </form>
                </div>

                {{-- ✅ FIX: Quick Actions dengan Route yang Benar --}}
                <div class="bg-white/10 backdrop-blur-xl rounded-2xl p-6 shadow-2xl border-2 border-yellow-400/20" data-aos="fade-left" data-aos-delay="300">
                    <h3 class="text-yellow-400 font-bold text-lg mb-4 flex items-center gap-2">
                        <span class="text-2xl">⚡</span>
                        Quick Actions
                    </h3>
                    <div class="space-y-3">
                        {{-- ✅ CORRECT: Export PDF Detail --}}
                        <a href="{{ route('lurah.laporan.export.detail', $laporan->id) }}"
   class="inline-flex items-center gap-2 px-4 py-2 bg-red-500 hover:bg-red-600 text-white rounded-lg font-bold transition shadow-lg hover:shadow-red-500/50">
    <span>📄</span>
    <span>Export PDF</span>
</a>

                        <button onclick="window.print()"
                            class="w-full bg-gradient-to-r from-blue-600 to-blue-700 text-white font-bold py-3 rounded-xl hover:scale-105 transition-all flex items-center justify-center gap-2">
                            <span>🖨️</span>
                            <span>Print Laporan</span>
                        </button>
                    </div>
                </div>

            </div>
        </div>

    </div>
@endsection