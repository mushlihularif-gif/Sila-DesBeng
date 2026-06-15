@extends('layouts.user')

@section('title', 'Detail Laporan')

@section('page')

<div class="min-h-screen bg-gradient-to-br from-blue-50 to-white py-20 text-gray-800">
    <div class="max-w-4xl mx-auto px-4">
        {{-- Alert Messages --}}
        @if (session('success'))
        <div class="mb-6 bg-green-500/20 border border-green-500/50 rounded-xl p-4 animate-fade-in">
            <p class="text-green-400">✅ {{ session('success') }}</p>
        </div>
        @endif

        @if (session('error'))
        <div class="mb-6 bg-red-500/20 border border-red-500/50 rounded-xl p-4 animate-fade-in">
            <p class="text-red-400">❌ {{ session('error') }}</p>
        </div>
        @endif

        {{-- Card Laporan --}}
        <div class="bg-gradient-to-br from-[#004635]/90 to-[#003026]/90 backdrop-blur-xl rounded-2xl border-2 border-yellow-400/30 shadow-2xl overflow-hidden">
            {{-- Header --}}
            <div class="bg-gradient-to-r from-yellow-400 to-amber-500 p-6">
                <div class="flex items-center justify-between">
                    <h1 class="text-2xl font-bold text-[#004635]">📋 Detail Laporan</h1>
                    <div class="flex gap-2">
                        <a href="{{ route('user.laporan.export', $laporan->id) }}"
                            class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg font-bold transition flex items-center gap-2">
                            <span>📄</span>
                            <span>Download PDF</span>
                        </a>
                        <a href="{{ route('user.laporan.index') }}"
                            class="px-4 py-2 bg-[#004635] text-yellow-400 rounded-lg hover:bg-[#003026] transition">
                            ← Kembali
                        </a>
                    </div>
                </div>
            </div>

            <div class="p-8">
                {{-- Info Grid --}}
                <div class="grid md:grid-cols-2 gap-6 mb-8">
                    {{-- Nomor Laporan --}}
                    <div>
                        <p class="text-gray-400 text-sm mb-1">🔢 Nomor Laporan</p>
                        <p class="text-white font-bold text-lg">#{{ str_pad($laporan->id, 3, '0', STR_PAD_LEFT) }}</p>
                    </div>

                    {{-- Status --}}
                    <div>
                        <p class="text-gray-400 text-sm mb-1">📊 Status</p>
                        <div class="flex items-center gap-2">
                            @if ($laporan->status === 'Pending')
                            <span class="inline-flex px-3 py-1.5 bg-yellow-500/20 text-yellow-400 border border-yellow-400/50 rounded-lg text-sm font-bold">
                                ⏳ Pending
                            </span>

                            @elseif(in_array($laporan->status, ['Proses','Diproses']))
                            <span class="inline-flex px-3 py-1.5 bg-blue-500/20 text-blue-400 border border-blue-400/50 rounded-lg text-sm font-bold">
                                🔄 Proses
                            </span>

                            @elseif($laporan->status === 'Dilanjutkan')
                            <span class="inline-flex px-3 py-1.5 bg-orange-500/20 text-orange-400 border border-orange-400/50 rounded-lg text-sm font-bold">
                                📤 Dilanjutkan
                            </span>

                            @elseif($laporan->status === 'Selesai')
                            <span class="inline-flex px-3 py-1.5 bg-green-500/20 text-green-400 border border-green-400/50 rounded-lg text-sm font-bold">
                                ✅ Selesai
                            </span>

                            @elseif($laporan->status === 'Ditolak')
                            <span class="inline-flex px-3 py-1.5 bg-red-500/20 text-red-400 border border-red-400/50 rounded-lg text-sm font-bold">
                                ❌ Ditolak
                            </span>
                            @endif
                        </div>
                    </div>

                    {{-- Nama Pelapor --}}
                    <div>
                        <p class="text-gray-400 text-sm mb-1">👤 Nama Pelapor</p>
                        <p class="text-white font-semibold truncate">
                            {{ $laporan->nama ?? ($laporan->user->name ?? 'Tidak Diketahui') }}
                        </p>
                    </div>

                    {{-- Kategori --}}
                    <div>
                        <p class="text-gray-400 text-sm mb-1">📂 Kategori</p>
                        <span class="inline-block px-3 py-1 bg-blue-500/20 text-blue-300 border border-blue-400/40 rounded-lg text-sm">
                            {{ $laporan->kategori }}
                        </span>
                    </div>

                    {{-- RW --}}
                    <div>
                        <p class="text-gray-400 text-sm mb-1">🏘️ RW</p>
                        <p class="text-yellow-400 font-bold text-xl">{{ $laporan->rw ?? '-' }}</p>
                    </div>

                    {{-- RT --}}
                    <div>
                        <p class="text-gray-400 text-sm mb-1">🏠 RT</p>
                        <p class="text-green-400 font-bold text-xl">{{ $laporan->rt ?? '-' }}</p>
                    </div>

                    {{-- Tanggal Lapor --}}
                    <div>
                        <p class="text-gray-400 text-sm mb-1">📅 Tanggal Lapor</p>
                        <p class="text-white text-sm">{{ $laporan->created_at->format('d M Y, H:i') }} WIB</p>
                    </div>

                    {{-- Update Terakhir --}}
                    <div>
                        <p class="text-gray-400 text-sm mb-1">🔄 Update Terakhir</p>
                        <p class="text-white text-sm">{{ $laporan->updated_at->format('d M Y, H:i') }} WIB</p>
                    </div>
                </div>

                {{-- Deskripsi --}}
                <div class="mb-6">
                    <h2 class="text-yellow-400 font-bold text-xl mb-3">📝 Deskripsi Laporan</h2>
                    <div class="bg-[#003026]/50 border border-yellow-400/20 rounded-xl p-5">
                        <p class="text-gray-300 leading-relaxed">{{ $laporan->deskripsi }}</p>
                    </div>
                </div>

                {{-- Lokasi --}}
                @if ($laporan->lokasi)
                <div class="mb-6">
                    <p class="text-gray-400 text-sm mb-2">📍 Lokasi Kejadian</p>
                    <div class="bg-[#003026]/30 border border-yellow-400/20 rounded-lg p-4">
                        <p class="text-white">{{ $laporan->lokasi }}</p>
                    </div>
                </div>
                @endif

                {{-- Foto Bukti --}}
                @if ($laporan->bukti)
                <div class="mb-6">
                    <p class="text-gray-400 text-sm mb-3">📸 Foto Bukti</p>
                    <div class="bg-gray-900 rounded-xl overflow-hidden border-2 border-yellow-400/30 shadow-lg">
                        <img
                            src="{{ asset('storage/' . $laporan->bukti) }}"
                            alt="Foto Bukti Laporan"
                            class="w-full h-64 object-cover cursor-pointer hover:opacity-90 transition"
                            onclick="openImageModal('{{ asset('storage/' . $laporan->bukti) }}')">
                    </div>
                    <p class="text-xs text-gray-400 mt-2 text-center">
                        💡 Klik gambar untuk memperbesar
                    </p>
                </div>
                @endif

                {{-- Multiple Photos Support --}}
                @if (!empty($fotoPath) && is_array($fotoPath))
                <div class="mb-6">
                    <p class="text-gray-400 text-sm mb-3">📸 Foto Bukti ({{ count($fotoPath) }} foto)</p>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach ($fotoPath as $foto)
                        @if ($foto)
                        <div class="bg-gray-900 rounded-xl overflow-hidden border-2 border-yellow-400/30 shadow-lg">
                            <img src="{{ asset('storage/' . $foto) }}" alt="Foto Bukti Laporan"
                                class="w-full h-64 object-cover cursor-pointer hover:opacity-90 transition"
                                onclick="openImageModal('{{ asset('storage/' . $foto) }}')">
                        </div>
                        @endif
                        @endforeach
                    </div>
                    <p class="text-xs text-gray-400 mt-2 text-center">💡 Klik gambar untuk memperbesar</p>
                </div>
                @endif

                {{-- Catatan Admin --}}
                @if ($laporan->catatan_admin)
                <div class="mb-6 bg-blue-500/10 border-2 border-blue-500/30 rounded-xl p-6">
                    <div class="flex items-start gap-3 mb-3">
                        <span class="text-3xl">💬</span>
                        <div>
                            <p class="text-blue-400 font-bold text-lg">Catatan dari Admin</p>
                            @if ($laporan->admin)
                            <p class="text-gray-400 text-sm">Oleh: {{ $laporan->admin->name }}</p>
                            @endif
                        </div>
                    </div>
                    <div class="bg-[#003026]/50 rounded-lg p-4">
                        <p class="text-gray-300 leading-relaxed">{{ $laporan->catatan_admin }}</p>
                    </div>
                    <p class="text-gray-500 text-xs mt-3">
                        📅 {{ $laporan->updated_at->format('d M Y, H:i') }} WIB
                    </p>
                </div>
                @endif

                {{-- Status Info Banner --}}
                @if ($laporan->status === 'Pending')
                <div class="mb-6 bg-yellow-500/10 border-2 border-yellow-400/30 rounded-xl p-4">
                    <div class="flex items-center gap-3">
                        <span class="text-3xl">⏰</span>
                        <div>
                            <p class="text-yellow-300 font-semibold">Laporan Sedang Menunggu</p>
                            <p class="text-gray-400 text-sm">Laporan Anda sedang menunggu ditinjau oleh admin. Mohon bersabar.</p>
                        </div>
                    </div>
                </div>
                @elseif(in_array($laporan->status, ['Proses', 'Diproses']))
                <div class="mb-6 bg-blue-500/10 border-2 border-blue-400/30 rounded-xl p-4">
                    <div class="flex items-center gap-3">
                        <span class="text-3xl">🔄</span>
                        <div>
                            <p class="text-blue-300 font-semibold">Laporan Sedang Diproses</p>
                            <p class="text-gray-400 text-sm">Tim kami sedang menangani laporan Anda. Terima kasih atas kesabaran Anda.</p>
                        </div>
                    </div>
                </div>
                @elseif($laporan->status === 'Dilanjutkan')
                <div class="mb-6 bg-orange-500/10 border-2 border-orange-400/30 rounded-xl p-4">
                    <div class="flex items-center gap-3">
                        <span class="text-3xl">📤</span>
                        <div>
                            <p class="text-orange-300 font-semibold">Laporan Dilanjutkan</p>
                            <p class="text-gray-400 text-sm">
                                Laporan Anda telah diteruskan ke pihak terkait untuk penanganan lanjutan.
                            </p>
                        </div>
                    </div>
                </div>
                @elseif($laporan->status === 'Selesai')
                <div class="mb-6 bg-green-500/10 border-2 border-green-400/30 rounded-xl p-4">
                    <div class="flex items-center gap-3">
                        <span class="text-3xl">✅</span>
                        <div>
                            <p class="text-green-300 font-semibold">Laporan Selesai Ditangani</p>
                            <p class="text-gray-400 text-sm">
                                Laporan Anda telah selesai ditangani. Terima kasih atas laporannya!
                            </p>
                        </div>
                    </div>
                </div>
                @elseif($laporan->status === 'Ditolak')
                <div class="mb-6 bg-red-500/10 border-2 border-red-400/30 rounded-xl p-4">
                    <div class="flex items-center gap-3">
                        <span class="text-3xl">❌</span>
                        <div>
                            <p class="text-red-300 font-semibold">Laporan Ditolak</p>
                            <p class="text-gray-400 text-sm">Laporan Anda telah ditolak. Silakan cek catatan admin untuk informasi lebih lanjut.</p>
                        </div>
                    </div>
                </div>
                @endif

                {{-- TOMBOL HAPUS - DIPERBAIKI --}}
                @if ($laporan->status === 'Pending')
                    @php
                        // Cek apakah user adalah pemilik laporan
                        $isOwner = false;
                        
                        // Cek berdasarkan user_id
                        if (isset($laporan->user_id) && $laporan->user_id === auth()->id()) {
                            $isOwner = true;
                        }
                        
                        // Cek berdasarkan nama (untuk laporan lama yang tidak ada user_id)
                        if (!$isOwner && isset($laporan->nama) && auth()->user()) {
                            $isOwner = strtolower(trim($laporan->nama)) === strtolower(trim(auth()->user()->name));
                        }
                    @endphp

                    @if ($isOwner)
                    <div class="mt-6 bg-red-500/10 border-2 border-red-400/30 rounded-xl p-5">
                        <div class="flex items-start gap-3 mb-4">
                            <span class="text-3xl">⚠️</span>
                            <div>
                                <p class="text-red-400 font-bold text-lg">Laporan Dapat Dihapus</p>
                                <p class="text-gray-400 text-sm">Karena status laporan masih "Pending", Anda dapat menghapus laporan ini.</p>
                            </div>
                        </div>

                        <form action="{{ route('user.laporan.destroy', $laporan) }}"
                            method="POST"
                            onsubmit="return confirm('⚠️ Yakin ingin menghapus laporan ini?\n\nLaporan yang dihapus tidak dapat dikembalikan!')">
                            @csrf
                            @method('DELETE')

                            <button type="submit"
                                class="px-6 py-3 bg-red-600 hover:bg-red-700 text-white rounded-lg font-bold transition-all duration-200 transform hover:scale-105 flex items-center gap-2">
                                <span>🗑️</span>
                                <span>Hapus Laporan</span>
                            </button>
                        </form>
                    </div>
                    @endif
                @endif

            </div>
        </div>
    </div>
</div>

{{-- Modal untuk zoom gambar --}}
<div id="imageModal" class="hidden fixed inset-0 bg-black/90 z-50 items-center justify-center p-4" onclick="closeImageModal()">
    <div class="relative max-w-7xl max-h-full">
        <button onclick="closeImageModal()" class="absolute top-4 right-4 text-white bg-red-600 hover:bg-red-700 rounded-full w-10 h-10 flex items-center justify-center text-2xl font-bold z-10">
            ×
        </button>
        <img id="modalImage" src="" alt="Preview" class="max-w-full max-h-[90vh] object-contain rounded-lg">
    </div>
</div>

@endsection

@push('scripts')
<script>
    function openImageModal(src) {
        const modal = document.getElementById('imageModal');
        document.getElementById('modalImage').src = src;
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.body.style.overflow = 'hidden';
    }

    function closeImageModal() {
        const modal = document.getElementById('imageModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        document.body.style.overflow = 'auto';
    }

    // Close modal when pressing ESC key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeImageModal();
        }
    });
</script>
@endpush

@push('styles')
<style>
    /* Animation for bounce */
    @keyframes bounce-slow {
        0%, 100% {
            transform: translateY(0);
        }
        50% {
            transform: translateY(-20px);
        }
    }

    .animate-bounce-slow {
        animation: bounce-slow 2s ease-in-out infinite;
    }

    /* Border 3px */
    .border-3 {
        border-width: 3px;
    }

    /* Fade in animation */
    @keyframes fade-in {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .animate-fade-in {
        animation: fade-in 0.3s ease-out;
    }

    /* Smooth transitions */
    button, a {
        transition: all 0.2s ease-in-out;
    }
</style>
@endpush