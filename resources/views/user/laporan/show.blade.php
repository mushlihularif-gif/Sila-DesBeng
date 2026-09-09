@extends('layouts.user')

@section('title', 'Detail Laporan')

@section('page')

<div class="min-h-screen bg-gradient-to-br from-blue-50 to-white py-20 text-gray-800">
    <div class="max-w-4xl mx-auto px-4">
        {{-- Alert Messages --}}
        @if (session('success'))
        <div class="mb-6 bg-green-500/20 border border-green-500/50 rounded-xl p-4 animate-fade-in">
            <p class="text-green-400 flex items-center gap-2"><svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg> {{ session('success') }}</p>
        </div>
        @endif

        @if (session('error'))
        <div class="mb-6 bg-red-500/20 border border-red-500/50 rounded-xl p-4 animate-fade-in">
            <p class="text-red-400 flex items-center gap-2"><svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg> {{ session('error') }}</p>
        </div>
        @endif

        {{-- Card Laporan --}}
        <div class="bg-gradient-to-br from-[#004635]/90 to-[#003026]/90 backdrop-blur-xl rounded-2xl border-2 border-yellow-400/30 shadow-2xl overflow-hidden">
            {{-- Header --}}
            <div class="bg-gradient-to-r from-yellow-400 to-amber-500 p-6">
                <div class="flex items-center justify-between">
                    <h1 class="text-2xl font-bold text-[#004635]">Detail Laporan</h1>
                    <div class="flex gap-2">
                        <a href="{{ route('user.laporan.export', $laporan->id) }}"
                            class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg font-bold transition flex items-center gap-2">
                            <i class="fas fa-file-pdf"></i>
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
                        <p class="text-gray-400 text-sm mb-1">Nomor Laporan</p>
                        <p class="text-white font-bold text-lg">#{{ str_pad($laporan->id, 3, '0', STR_PAD_LEFT) }}</p>
                    </div>

                    {{-- Status --}}
                    <div>
                        <p class="text-gray-400 text-sm mb-1">Status</p>
                        <div class="flex items-center gap-2">
                            @if ($laporan->status === 'Pending')
                            <span class="inline-flex px-3 py-1.5 bg-yellow-500/20 text-yellow-400 border border-yellow-400/50 rounded-lg text-sm font-bold flex items-center gap-2">
                                <i class="fas fa-clock"></i> Pending
                            </span>

                            @elseif(in_array($laporan->status, ['Proses','Diproses']))
                            <span class="inline-flex px-3 py-1.5 bg-blue-500/20 text-blue-400 border border-blue-400/50 rounded-lg text-sm font-bold flex items-center gap-2">
                                <i class="fas fa-spinner fa-spin"></i> Proses
                            </span>

                            @elseif($laporan->status === 'Dilanjutkan')
                            <span class="inline-flex px-3 py-1.5 bg-orange-500/20 text-orange-400 border border-orange-400/50 rounded-lg text-sm font-bold flex items-center gap-2">
                                <i class="fas fa-share"></i> Dilanjutkan
                            </span>

                            @elseif($laporan->status === 'Selesai')
                            <span class="inline-flex px-3 py-1.5 bg-green-500/20 text-green-400 border border-green-400/50 rounded-lg text-sm font-bold flex items-center gap-2">
                                <i class="fas fa-check"></i> Selesai
                            </span>

                            @elseif($laporan->status === 'Ditolak')
                            <span class="inline-flex px-3 py-1.5 bg-red-500/20 text-red-400 border border-red-400/50 rounded-lg text-sm font-bold flex items-center gap-2">
                                <i class="fas fa-times"></i> Ditolak
                            </span>
                            @endif
                        </div>
                    </div>

                    {{-- Nama Pelapor & Akun --}}
                    <div>
                        <p class="text-gray-400 text-sm mb-1">Nama Pelapor</p>
                        @php
                            $isVerified = $laporan->user && $laporan->user->kycVerification && $laporan->user->kycVerification->status === 'approved';
                            $namaLaporan = $laporan->nama ?? 'Tidak Diketahui';
                            $namaAkun = $laporan->user->name ?? 'Tidak Diketahui';
                        @endphp
                        
                        @if($isVerified)
                            <p class="text-white font-semibold truncate">{{ $namaLaporan }} 
                                <span class="inline-flex items-center text-[10px] bg-green-500/20 text-green-400 px-2 py-0.5 rounded-full ml-1 font-medium border border-green-400/50"><i class="fas fa-check-circle mr-1"></i> Sesuai KTP</span>
                            </p>
                        @else
                            <p class="text-white font-semibold truncate">{{ $namaLaporan }} 
                                <span class="inline-flex items-center text-[10px] bg-amber-500/20 text-amber-400 px-2 py-0.5 rounded-full ml-1 font-medium border border-amber-400/50"><i class="fas fa-exclamation-triangle mr-1"></i> Belum KTP</span>
                            </p>
                            <p class="text-blue-400 text-xs mt-1">Nama Akun: {{ $namaAkun }}</p>
                        @endif
                    </div>

                    {{-- Kategori --}}
                    <div>
                        <p class="text-gray-400 text-sm mb-1">Kategori</p>
                        <span class="inline-block px-3 py-1 bg-blue-500/20 text-blue-300 border border-blue-400/40 rounded-lg text-sm">
                            {{ $laporan->kategori }}
                        </span>
                    </div>

                    {{-- RW --}}
                    <div>
                        <p class="text-gray-400 text-sm mb-1">RW</p>
                        <p class="text-yellow-400 font-bold text-xl">{{ $laporan->rw ?? '-' }}</p>
                    </div>

                    {{-- RT --}}
                    <div>
                        <p class="text-gray-400 text-sm mb-1">RT</p>
                        <p class="text-green-400 font-bold text-xl">{{ $laporan->rt ?? '-' }}</p>
                    </div>

                    {{-- Tanggal Lapor --}}
                    <div>
                        <p class="text-gray-400 text-sm mb-1">Tanggal Lapor</p>
                        <p class="text-white text-sm">{{ $laporan->created_at->format('d M Y, H:i') }} WIB</p>
                    </div>

                    {{-- Update Terakhir --}}
                    <div>
                        <p class="text-gray-400 text-sm mb-1">Update Terakhir</p>
                        <p class="text-white text-sm">{{ $laporan->updated_at->format('d M Y, H:i') }} WIB</p>
                    </div>
                </div>

                {{-- Deskripsi --}}
                <div class="mb-6">
                    <h2 class="text-yellow-400 font-bold text-xl mb-3"><i class="fas fa-align-left mr-2"></i> Deskripsi Laporan</h2>
                    <div class="bg-[#003026]/50 border border-yellow-400/20 rounded-xl p-5">
                        <p class="text-gray-300 leading-relaxed">{{ $laporan->deskripsi }}</p>
                    </div>
                </div>

                {{-- Lokasi --}}
                @if ($laporan->lokasi)
                <div class="mb-6">
                    <p class="text-gray-400 text-sm mb-2"><i class="fas fa-map-marker-alt mr-2"></i> Lokasi Kejadian</p>
                    <div class="bg-[#003026]/30 border border-yellow-400/20 rounded-lg p-4">
                        <p class="text-white">{{ $laporan->lokasi }}</p>
                        @if($laporan->latitude && $laporan->longitude)
                            <div class="mt-4 rounded-lg overflow-hidden border border-yellow-400/30 shadow-inner" style="height: 250px;">
                                <div id="map-{{ $laporan->id }}" class="w-full h-full"></div>
                            </div>
                            <p class="text-gray-400 text-xs mt-2"><i class="fas fa-satellite"></i> Titik koordinat tersimpan: {{ $laporan->latitude }}, {{ $laporan->longitude }}</p>
                        @endif
                    </div>
                </div>
                @endif

                {{-- Foto Bukti --}}
                @if (!empty($laporan->bukti_array))
                <div class="mb-6">
                    <p class="text-gray-400 text-sm mb-3 flex items-center gap-2"><svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path></svg> Foto Bukti ({{ count($laporan->bukti_array) }} foto)</p>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach ($laporan->bukti_array as $foto)
                        <div class="bg-gray-900 rounded-xl overflow-hidden border-2 border-yellow-400/30 shadow-lg">
                            <img src="{{ asset('storage/' . $foto) }}" alt="Foto Bukti Laporan"
                                class="w-full h-64 object-cover cursor-pointer hover:opacity-90 transition"
                                onclick="openImageModal('{{ asset('storage/' . $foto) }}')">
                        </div>
                        @endforeach
                    </div>
                    <p class="text-xs text-gray-400 mt-2 text-center flex items-center justify-center gap-1"><svg class="w-3.5 h-3.5 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg> Klik gambar untuk memperbesar</p>
                </div>
                @endif

                {{-- Catatan Admin --}}
                @if ($laporan->catatan_admin)
                <div class="mb-6 bg-blue-500/10 border-2 border-blue-500/30 rounded-xl p-6">
                    <div class="flex items-start gap-3 mb-3">
                        <svg class="w-7 h-7 text-blue-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
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
                    <p class="text-gray-500 text-xs mt-3 flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        {{ $laporan->updated_at->format('d M Y, H:i') }} WIB
                    </p>
                </div>
                @endif

                {{-- Status Info Banner --}}
                @if ($laporan->status === 'Pending')
                <div class="mb-6 bg-yellow-500/10 border-2 border-yellow-400/30 rounded-xl p-4">
                    <div class="flex items-center gap-3">
                        <svg class="w-7 h-7 text-yellow-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <div>
                            <p class="text-yellow-300 font-semibold">Laporan Sedang Menunggu</p>
                            <p class="text-gray-400 text-sm">Laporan Anda sedang menunggu ditinjau oleh admin. Mohon bersabar.</p>
                        </div>
                    </div>
                </div>
                @elseif(in_array($laporan->status, ['Proses', 'Diproses']))
                <div class="mb-6 bg-blue-500/10 border-2 border-blue-400/30 rounded-xl p-4">
                    <div class="flex items-center gap-3">
                        <svg class="w-7 h-7 text-blue-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                        <div>
                            <p class="text-blue-300 font-semibold">Laporan Sedang Diproses</p>
                            <p class="text-gray-400 text-sm">Tim kami sedang menangani laporan Anda. Terima kasih atas kesabaran Anda.</p>
                        </div>
                    </div>
                </div>
                @elseif($laporan->status === 'Dilanjutkan')
                <div class="mb-6 bg-orange-500/10 border-2 border-orange-400/30 rounded-xl p-4">
                    <div class="flex items-center gap-3">
                        <svg class="w-7 h-7 text-orange-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path></svg>
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
                        <svg class="w-7 h-7 text-green-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
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
                        <svg class="w-7 h-7 text-red-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
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
                            <svg class="w-7 h-7 text-red-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                            <div>
                                <p class="text-red-400 font-bold text-lg">Laporan Dapat Dihapus</p>
                                <p class="text-gray-400 text-sm">Karena status laporan masih "Pending", Anda dapat menghapus laporan ini.</p>
                            </div>
                        </div>

                        <form action="{{ route('user.laporan.destroy', $laporan) }}"
                            method="POST"
                            data-konfirmasi="Yakin ingin menghapus laporan ini?&#10;&#10;Laporan yang dihapus tidak dapat dikembalikan!">
                            @csrf
                            @method('DELETE')

                            <button type="submit"
                                class="px-6 py-3 bg-red-600 hover:bg-red-700 text-white rounded-lg font-bold transition-all duration-200 transform hover:scale-105 flex items-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
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

@if($laporan->latitude && $laporan->longitude)
<script>
    function initMap{{ $laporan->id }}() {
        const location = { lat: {{ $laporan->latitude }}, lng: {{ $laporan->longitude }} };
        const map = new google.maps.Map(document.getElementById("map-{{ $laporan->id }}"), {
            zoom: 17,
            center: location,
            mapTypeId: 'satellite',
            mapTypeControl: true,
            streetViewControl: false,
        });
        new google.maps.Marker({
            position: location,
            map: map,
            title: "{{ $laporan->lokasi }}",
            animation: google.maps.Animation.DROP
        });
    }
</script>
<script src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google_maps.api_key') }}&callback=initMap{{ $laporan->id }}" async defer></script>
@endif
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