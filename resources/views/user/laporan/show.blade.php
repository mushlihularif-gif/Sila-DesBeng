@extends('layouts.user')

@section('title', 'Detail Laporan #' . str_pad($laporan->id, 3, '0', STR_PAD_LEFT) . ' - SiladesBeng')

@push('styles')
<style>
    /* Halus transisi */
    .transition-smooth {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    @keyframes scaleUp {
        from {
            opacity: 0;
            transform: scale(0.94);
        }
        to {
            opacity: 1;
            transform: scale(1);
        }
    }
    .animate-scale-up {
        animation: scaleUp 0.25s cubic-bezier(0.16, 1, 0.3, 1) forwards;
    }
</style>
@endpush

@section('page')
<main class="flex-grow relative w-full">
    {{-- Background Gelombang Interaktif Khas SiladesBeng --}}
    @include('partials.abstract-bg')

    <section class="relative z-10 min-h-screen pt-32 sm:pt-36 md:pt-40 pb-20">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 relative z-10">

            {{-- Navigasi Atas (Kembali & Unduh PDF) --}}
            <div class="flex flex-wrap items-center justify-between gap-3 mb-6 sm:mb-8 animate-section">
                <a href="{{ route('user.activity', ['tab' => 'laporan']) }}" 
                   class="inline-flex items-center gap-2 px-5 py-2.5 rounded-full bg-white/85 hover:bg-white text-gray-700 hover:text-blue-600 border border-gray-200/80 shadow-sm backdrop-blur-md text-sm font-semibold transition-all">
                    <i class="bx bx-arrow-back text-base"></i>
                    <span>Kembali ke Riwayat</span>
                </a>

                <a href="{{ route('user.laporan.export', $laporan->id) }}"
                   class="inline-flex items-center gap-2 px-5 py-2.5 rounded-full bg-white/85 hover:bg-red-50 text-red-600 border border-red-200 shadow-sm backdrop-blur-md text-sm font-semibold transition-all">
                    <i class="bx bxs-file-pdf text-lg text-red-500"></i>
                    <span>Unduh PDF</span>
                </a>
            </div>

            {{-- Judul Halaman dengan Gradasi Khas Kabar Daerah --}}
            <div class="text-center mb-8 sm:mb-10 animate-section">
                <h1 class="text-3xl sm:text-4xl md:text-5xl font-bold mb-3 tracking-tight">
                    <span class="bg-gradient-to-r from-gray-800 to-gray-600 bg-clip-text text-transparent">Detail </span>
                    <span class="bg-gradient-to-r from-[#115789] to-[#60a5fa] bg-clip-text text-transparent">Laporan Warga</span>
                </h1>
                <p class="text-gray-600 text-sm sm:text-base max-w-xl mx-auto">
                    Pantau perkembangan, bukti dokumentasi, dan tindak lanjut laporan pengaduan Anda di SiladesBeng.
                </p>
            </div>

            {{-- Flash Notification Alerts --}}
            @if (session('success'))
            <div class="mb-6 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl p-4 flex items-center gap-3 shadow-sm animate-section">
                <i class="bx bx-check-circle text-2xl text-emerald-600 flex-shrink-0"></i>
                <p class="text-sm font-medium">{{ session('success') }}</p>
            </div>
            @endif

            @if (session('error'))
            <div class="mb-6 bg-rose-50 border border-rose-200 text-rose-800 rounded-2xl p-4 flex items-center gap-3 shadow-sm animate-section">
                <i class="bx bx-error-circle text-2xl text-rose-600 flex-shrink-0"></i>
                <p class="text-sm font-medium">{{ session('error') }}</p>
            </div>
            @endif

            @php
                $statusLower = strtolower($laporan->status);
                $isVerified = $laporan->user && $laporan->user->kycVerification && $laporan->user->kycVerification->status === 'approved';
                $namaLaporan = $laporan->nama ?? 'Warga';
                $namaAkun = $laporan->user->name ?? $namaLaporan;

                // Konfigurasi Status
                $statusConfig = match($statusLower) {
                    'pending' => [
                        'badge' => 'bg-amber-50 text-amber-800 border-amber-200',
                        'dot' => 'bg-amber-500',
                        'label' => 'Menunggu Verifikasi',
                        'desc' => 'Laporan Anda telah tercatat dalam sistem dan sedang menunggu tinjauan dari petugas terkait.',
                        'step' => 1
                    ],
                    'proses', 'diproses' => [
                        'badge' => 'bg-blue-50 text-blue-800 border-blue-200',
                        'dot' => 'bg-blue-500',
                        'label' => 'Sedang Diproses',
                        'desc' => 'Laporan telah diverifikasi dan saat ini sedang ditindaklanjuti oleh petugas lapangan.',
                        'step' => 2
                    ],
                    'dilanjutkan' => [
                        'badge' => 'bg-purple-50 text-purple-800 border-purple-200',
                        'dot' => 'bg-purple-500',
                        'label' => 'Dieskalasi ke ' . strtoupper($laporan->escalation_level ?? 'Desa'),
                        'desc' => 'Laporan dialihkan ke jenjang kewenangan yang lebih tinggi untuk penanganan komprehensif.',
                        'step' => 3
                    ],
                    'selesai' => [
                        'badge' => 'bg-emerald-50 text-emerald-800 border-emerald-200',
                        'dot' => 'bg-emerald-500',
                        'label' => 'Selesai Ditangani',
                        'desc' => 'Seluruh tindak lanjut atas aduan ini telah diselesaikan oleh pemerintah desa.',
                        'step' => 4
                    ],
                    'ditolak' => [
                        'badge' => 'bg-rose-50 text-rose-800 border-rose-200',
                        'dot' => 'bg-rose-500',
                        'label' => 'Laporan Ditolak',
                        'desc' => 'Laporan tidak dapat diproses lebih lanjut. Silakan periksa catatan dari petugas di bawah.',
                        'step' => 0
                    ],
                    default => [
                        'badge' => 'bg-gray-50 text-gray-800 border-gray-200',
                        'dot' => 'bg-gray-400',
                        'label' => ucfirst($laporan->status),
                        'desc' => 'Status laporan saat ini: ' . ucfirst($laporan->status),
                        'step' => 1
                    ]
                };
            @endphp

            {{-- Kartu Utama Detail Laporan --}}
            <div class="backdrop-blur-md bg-white/90 rounded-3xl shadow-xl border border-white/80 p-6 sm:p-8 md:p-10 space-y-8 animate-section">
                
                {{-- Header Kartu: Nomor, Kategori, Tujuan & Badge Status --}}
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-6 border-b border-gray-100">
                    <div class="flex flex-wrap items-center gap-2.5">
                        <span class="px-3.5 py-1.5 rounded-full text-xs font-bold bg-gray-900 text-white shadow-xs">
                            #{{ str_pad($laporan->id, 3, '0', STR_PAD_LEFT) }}
                        </span>
                        
                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-100">
                            <i class="bx bx-category"></i>
                            <span>{{ $laporan->kategori }}</span>
                        </span>

                        @if($laporan->tujuan_laporan)
                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold bg-indigo-50 text-indigo-700 border border-indigo-100">
                            <i class="bx bx-paper-plane"></i>
                            <span>Ditujukan: {{ strtoupper($laporan->tujuan_laporan) }}</span>
                        </span>
                        @endif
                    </div>

                    <div>
                        <span class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full text-xs font-bold border {{ $statusConfig['badge'] }}">
                            <span class="w-2 h-2 rounded-full {{ $statusConfig['dot'] }} animate-pulse"></span>
                            <span>{{ $statusConfig['label'] }}</span>
                        </span>
                    </div>
                </div>

                {{-- Alur Penanganan Aduan (Progress Tracker) --}}
                <div class="bg-gray-50/80 rounded-2xl p-5 sm:p-6 border border-gray-100">
                    <p class="text-xs font-bold uppercase tracking-wider text-gray-500 mb-4 flex items-center gap-1.5">
                        <i class="bx bx-git-commit text-blue-600"></i>
                        <span>Alur Tindak Lanjut Pengaduan</span>
                    </p>

                    @if($statusLower === 'ditolak')
                        <div class="flex items-center gap-3 p-3.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-sm">
                            <i class="bx bx-x-circle text-2xl text-rose-600 flex-shrink-0"></i>
                            <div>
                                <p class="font-bold">Laporan Dihentikan / Ditolak</p>
                                <p class="text-xs text-rose-700 mt-0.5">{{ $statusConfig['desc'] }}</p>
                            </div>
                        </div>
                    @else
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 relative">
                            {{-- Tahap 1: Laporan Dikirim --}}
                            <div class="flex flex-col items-center text-center">
                                <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm mb-2 shadow-xs {{ $statusConfig['step'] >= 1 ? 'bg-emerald-500 text-white' : 'bg-gray-200 text-gray-500' }}">
                                    <i class="bx bx-check text-lg"></i>
                                </div>
                                <span class="text-xs font-bold text-gray-800">Laporan Dikirim</span>
                                <span class="text-[11px] text-gray-500 mt-0.5">{{ $laporan->created_at->format('d M Y') }}</span>
                            </div>

                            {{-- Tahap 2: Verifikasi Petugas --}}
                            <div class="flex flex-col items-center text-center">
                                <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm mb-2 shadow-xs {{ $statusConfig['step'] >= 2 ? ($statusConfig['step'] == 2 ? 'bg-blue-600 text-white ring-4 ring-blue-100' : 'bg-emerald-500 text-white') : 'bg-gray-200 text-gray-500' }}">
                                    @if($statusConfig['step'] > 2)
                                        <i class="bx bx-check text-lg"></i>
                                    @else
                                        <i class="bx bx-file-find text-lg"></i>
                                    @endif
                                </div>
                                <span class="text-xs font-bold text-gray-800">Verifikasi</span>
                                <span class="text-[11px] text-gray-500 mt-0.5">Petugas RT/RW/Desa</span>
                            </div>

                            {{-- Tahap 3: Tindak Lanjut Lapangan --}}
                            <div class="flex flex-col items-center text-center">
                                <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm mb-2 shadow-xs {{ $statusConfig['step'] >= 3 ? ($statusConfig['step'] == 3 ? 'bg-purple-600 text-white ring-4 ring-purple-100' : 'bg-emerald-500 text-white') : 'bg-gray-200 text-gray-500' }}">
                                    @if($statusConfig['step'] > 3)
                                        <i class="bx bx-check text-lg"></i>
                                    @else
                                        <i class="bx bx-wrench text-lg"></i>
                                    @endif
                                </div>
                                <span class="text-xs font-bold text-gray-800">Tindak Lanjut</span>
                                <span class="text-[11px] text-gray-500 mt-0.5">Penanganan</span>
                            </div>

                            {{-- Tahap 4: Selesai Ditangani --}}
                            <div class="flex flex-col items-center text-center">
                                <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm mb-2 shadow-xs {{ $statusConfig['step'] >= 4 ? 'bg-emerald-500 text-white ring-4 ring-emerald-100' : 'bg-gray-200 text-gray-500' }}">
                                    <i class="bx bx-badge-check text-lg"></i>
                                </div>
                                <span class="text-xs font-bold text-gray-800">Selesai</span>
                                <span class="text-[11px] text-gray-500 mt-0.5">{{ $statusLower === 'selesai' ? $laporan->updated_at->format('d M Y') : 'Tuntas' }}</span>
                            </div>
                        </div>

                        <p class="text-xs text-gray-600 text-center mt-4 pt-3 border-t border-gray-200/60">
                            {{ $statusConfig['desc'] }}
                        </p>
                    @endif
                </div>

                {{-- Informasi Metadata Pelapor & Wilayah --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    {{-- Kolom Pelapor --}}
                    <div class="p-4 rounded-2xl bg-gray-50/70 border border-gray-100 flex items-start gap-3.5">
                        <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center flex-shrink-0 text-xl border border-blue-100">
                            <i class="bx bx-user"></i>
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-xs text-gray-500 font-medium">Pelapor</p>
                            <p class="text-sm font-bold text-gray-900 truncate mt-0.5">
                                {{ $namaLaporan }}
                            </p>
                            <div class="mt-1 flex items-center gap-2">
                                @if($isVerified)
                                    <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-emerald-700 bg-emerald-50 border border-emerald-200 px-2 py-0.5 rounded-full">
                                        <i class="bx bxs-badge-check"></i>
                                        <span>Sesuai KTP</span>
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-amber-700 bg-amber-50 border border-amber-200 px-2 py-0.5 rounded-full">
                                        <i class="bx bx-info-circle"></i>
                                        <span>Belum KTP</span>
                                    </span>
                                @endif
                                @if($namaAkun && $namaAkun !== $namaLaporan)
                                    <span class="text-xs text-gray-500 truncate">Akun: {{ $namaAkun }}</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Kolom Wilayah Administratif --}}
                    <div class="p-4 rounded-2xl bg-gray-50/70 border border-gray-100 flex items-start gap-3.5">
                        <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center flex-shrink-0 text-xl border border-indigo-100">
                            <i class="bx bx-buildings"></i>
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-xs text-gray-500 font-medium">Wilayah Administratif</p>
                            <div class="flex items-center gap-3 mt-0.5">
                                <div>
                                    <span class="text-xs text-gray-500">RT: </span>
                                    <span class="text-sm font-bold text-gray-900">{{ $laporan->rt ?? '-' }}</span>
                                </div>
                                <span class="text-gray-300">|</span>
                                <div>
                                    <span class="text-xs text-gray-500">RW: </span>
                                    <span class="text-sm font-bold text-gray-900">{{ $laporan->rw ?? '-' }}</span>
                                </div>
                                @if($laporan->region)
                                <span class="text-gray-300">|</span>
                                <div class="truncate">
                                    <span class="text-xs font-semibold text-blue-700 truncate">{{ $laporan->region->name }}</span>
                                </div>
                                @endif
                            </div>
                            <p class="text-[11px] text-gray-500 mt-1">
                                Waktu Lapor: {{ \Carbon\Carbon::parse($laporan->created_at)->locale('id')->isoFormat('DD MMMM YYYY, HH:mm') }} WIB
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Isi Deskripsi Laporan --}}
                <div>
                    <h3 class="text-base font-bold text-gray-900 mb-3 flex items-center gap-2">
                        <i class="bx bx-align-left text-blue-600 text-lg"></i>
                        <span>Uraian / Deskripsi Pengaduan</span>
                    </h3>
                    <div class="bg-gray-50/80 rounded-2xl p-5 sm:p-6 border border-gray-100 text-gray-700 leading-relaxed text-sm sm:text-base whitespace-pre-line">
                        {{ $laporan->deskripsi }}
                    </div>
                </div>

                {{-- Bukti Dokumentasi / Foto Laporan --}}
                @if (!empty($laporan->bukti_array))
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="text-base font-bold text-gray-900 flex items-center gap-2">
                            <i class="bx bx-camera text-blue-600 text-lg"></i>
                            <span>Foto Bukti Lapangan</span>
                            <span class="text-xs font-normal text-gray-500">({{ count($laporan->bukti_array) }} Foto)</span>
                        </h3>
                        <span class="text-xs text-gray-400">Klik untuk memperbesar</span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                        @foreach ($laporan->bukti_array as $foto)
                        <div class="group relative rounded-2xl overflow-hidden border border-gray-200 shadow-xs bg-gray-100 cursor-pointer aspect-video transition-all hover:shadow-md"
                             onclick="openImageModal('{{ asset('storage/' . $foto) }}')">
                            <img src="{{ asset('storage/' . $foto) }}" 
                                 alt="Bukti Laporan" 
                                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                            <div class="absolute inset-0 bg-black/35 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center text-white">
                                <span class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full bg-white/20 backdrop-blur-sm text-xs font-bold border border-white/40 shadow-sm">
                                    <i class="bx bx-zoom-in text-base"></i>
                                    <span>Lihat Foto Penuh</span>
                                </span>
                            </div>
                            <div class="absolute bottom-2.5 right-2.5 bg-black/60 backdrop-blur-xs text-white text-[11px] font-semibold px-2.5 py-1 rounded-lg flex items-center gap-1 opacity-85 group-hover:opacity-100 transition-opacity">
                                <i class="bx bx-expand-alt"></i>
                                <span>Perbesar</span>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- Lokasi Kejadian & Peta Leaflet --}}
                @if ($laporan->lokasi || ($laporan->latitude && $laporan->longitude))
                <div>
                    <div class="flex flex-wrap items-center justify-between gap-2 mb-3">
                        <h3 class="text-base font-bold text-gray-900 flex items-center gap-2">
                            <i class="bx bx-map text-red-500 text-lg"></i>
                            <span>Lokasi Kejadian</span>
                        </h3>
                        
                        @if($laporan->latitude && $laporan->longitude)
                        <a href="https://www.google.com/maps?q={{ $laporan->latitude }},{{ $laporan->longitude }}" 
                           target="_blank" rel="noopener noreferrer"
                           class="inline-flex items-center gap-1.5 text-xs font-semibold text-blue-600 hover:text-blue-700 bg-blue-50 hover:bg-blue-100 px-3 py-1.5 rounded-full border border-blue-200 transition-colors">
                            <i class="bx bx-link-external"></i>
                            <span>Buka di Google Maps</span>
                        </a>
                        @endif
                    </div>

                    <div class="bg-gray-50/80 rounded-2xl p-4 sm:p-5 border border-gray-100">
                        <p class="text-sm font-semibold text-gray-800 flex items-start gap-2">
                            <i class="bx bx-map-pin text-red-500 text-lg flex-shrink-0 mt-0.5"></i>
                            <span>{{ $laporan->display_lokasi }}</span>
                        </p>

                        @if($laporan->latitude && $laporan->longitude)
                            <div class="mt-3 rounded-xl overflow-hidden border border-gray-200 shadow-xs relative bg-gray-100" style="height: 300px; z-index: 1;">
                                <iframe
                                    width="100%"
                                    height="100%"
                                    style="border:0; width: 100%; height: 300px;"
                                    loading="lazy"
                                    allowfullscreen
                                    referrerpolicy="no-referrer-when-downgrade"
                                    src="https://maps.google.com/maps?q={{ $laporan->latitude }},{{ $laporan->longitude }}&hl=id&z=16&output=embed">
                                </iframe>
                            </div>
                            <div class="flex flex-wrap items-center justify-between gap-2 mt-2 px-1">
                                <p class="text-[11px] text-gray-500 flex items-center gap-1">
                                    <i class="bx bx-target-lock text-blue-500"></i>
                                    <span>Titik Koordinat: {{ $laporan->latitude }}, {{ $laporan->longitude }}</span>
                                </p>
                                <span class="text-[11px] text-gray-400">Peta Lokasi Kejadian</span>
                            </div>
                        @endif
                    </div>
                </div>
                @endif

                {{-- Catatan & Tanggapan Petugas / Admin --}}
                @if ($laporan->catatan_admin || $laporan->catatan_rw || $laporan->catatan_rt)
                <div class="p-5 sm:p-6 rounded-2xl bg-blue-50/60 border border-blue-100 space-y-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-blue-600 text-white flex items-center justify-center flex-shrink-0 text-xl shadow-xs">
                            <i class="bx bx-message-rounded-check"></i>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-gray-900">Tanggapan Resmi Petugas</h3>
                            <p class="text-xs text-gray-500">Pembaruan dan catatan tindak lanjut penanganan pengaduan</p>
                        </div>
                    </div>

                    @if($laporan->catatan_admin)
                    <div class="bg-white rounded-xl p-4 border border-blue-100/80 shadow-xs">
                        <div class="flex items-center justify-between gap-2 mb-2">
                            <span class="text-xs font-bold text-blue-800">Admin {{ $laporan->admin->name ?? 'Pemerintah Desa' }}</span>
                            <span class="text-[11px] text-gray-400">{{ $laporan->updated_at->format('d M Y, H:i') }} WIB</span>
                        </div>
                        <p class="text-sm text-gray-700 leading-relaxed">{{ $laporan->catatan_admin }}</p>
                    </div>
                    @endif

                    @if($laporan->catatan_rw)
                    <div class="bg-white rounded-xl p-4 border border-blue-100/80 shadow-xs">
                        <div class="flex items-center justify-between gap-2 mb-2">
                            <span class="text-xs font-bold text-indigo-800">Admin RW {{ $laporan->rw ?? '' }}</span>
                            @if($laporan->escalated_to_rw_at)
                            <span class="text-[11px] text-gray-400">{{ $laporan->escalated_to_rw_at->format('d M Y, H:i') }} WIB</span>
                            @endif
                        </div>
                        <p class="text-sm text-gray-700 leading-relaxed">{{ $laporan->catatan_rw }}</p>
                    </div>
                    @endif

                    @if($laporan->catatan_rt)
                    <div class="bg-white rounded-xl p-4 border border-blue-100/80 shadow-xs">
                        <div class="flex items-center justify-between gap-2 mb-2">
                            <span class="text-xs font-bold text-emerald-800">Admin RT {{ $laporan->rt ?? '' }}</span>
                        </div>
                        <p class="text-sm text-gray-700 leading-relaxed">{{ $laporan->catatan_rt }}</p>
                    </div>
                    @endif
                </div>
                @endif

                {{-- Batalkan / Hapus Laporan (Jika Status Masih Pending & Memenuhi Syarat) --}}
                @if ($laporan->canBeDeletedBy(auth()->id()))
                <div class="pt-6 border-t border-gray-100 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                    <div>
                        <p class="font-bold text-gray-900 text-sm">Opsi Pembatalan Pengaduan</p>
                        <p class="text-xs text-gray-500 mt-0.5">
                            Laporan masih berstatus menunggu verifikasi. Anda dapat membatalkan laporan ini dalam batas waktu 24 jam pertama.
                        </p>
                    </div>

                    <form action="{{ route('user.laporan.destroy', $laporan) }}"
                          method="POST"
                          onsubmit="return confirm('Apakah Anda yakin ingin membatalkan dan menghapus laporan ini? Tindakan ini tidak dapat diurungkan.');"
                          class="flex-shrink-0">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                                class="px-4 py-2.5 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-600 border border-rose-200 text-xs font-bold inline-flex items-center gap-1.5 transition-colors cursor-pointer shadow-xs">
                            <i class="bx bx-trash text-sm"></i>
                            <span>Batalkan & Hapus Laporan</span>
                        </button>
                    </form>
                </div>
                @endif

            </div>
        </div>
    </section>
</main>

{{-- Modal Lightbox Peninjau Bukti Foto --}}
<div id="imageModal" 
     class="hidden fixed inset-0 bg-slate-950/85 backdrop-blur-md items-center justify-center p-3 sm:p-6 transition-all duration-300"
     style="z-index: 100000;"
     onclick="closeImageModal()">
    
    <div class="relative w-full max-w-4xl bg-white rounded-3xl shadow-2xl overflow-hidden flex flex-col max-h-[92vh] border border-white/20 animate-scale-up" 
         onclick="event.stopPropagation();">
        
        {{-- Header Modal --}}
        <div class="flex items-center justify-between px-5 sm:px-6 py-4 bg-white border-b border-gray-100 flex-shrink-0">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl flex-shrink-0 border border-blue-100">
                    <i class="bx bx-image"></i>
                </div>
                <div>
                    <h4 class="font-bold text-gray-900 text-sm sm:text-base leading-tight">Foto Bukti Lapangan</h4>
                    <p class="text-xs text-gray-500 mt-0.5">
                        Laporan #{{ str_pad($laporan->id, 3, '0', STR_PAD_LEFT) }} • {{ $laporan->kategori }}
                        @if(!empty($laporan->bukti_array) && count($laporan->bukti_array) > 1)
                            • <span id="modalFotoCounter" class="font-semibold text-blue-600">1 / {{ count($laporan->bukti_array) }}</span>
                        @endif
                    </p>
                </div>
            </div>

            <div>
                <button type="button" 
                        onclick="closeImageModal()" 
                        class="w-9 h-9 rounded-full bg-gray-100 hover:bg-rose-50 text-gray-500 hover:text-rose-600 flex items-center justify-center text-xl transition-colors cursor-pointer"
                        title="Tutup">
                    <i class="bx bx-x"></i>
                </button>
            </div>
        </div>

        {{-- Area Tampilan Gambar (Tampil Penuh dan Proporsional) --}}
        <div class="relative bg-slate-950 flex items-center justify-center p-3 sm:p-6 overflow-hidden min-h-[340px] sm:min-h-[460px] select-none">
            {{-- Tombol Navigasi Kiri --}}
            <button type="button" 
                    id="modalPrevBtn"
                    onclick="prevModalImage()"
                    style="display: none;"
                    class="absolute left-3 top-1/2 -translate-y-1/2 w-10 h-10 rounded-full bg-black/60 hover:bg-black/90 text-white flex items-center justify-center text-2xl transition-all cursor-pointer z-10 backdrop-blur-xs shadow-lg">
                <i class="bx bx-chevron-left"></i>
            </button>

            {{-- Gambar Bukti Utama (Otomatis berskala penuh agar tidak tampak kecil) --}}
            <img id="modalImage" 
                 src="" 
                 alt="Foto Bukti Laporan" 
                 class="w-full max-h-[75vh] min-h-[300px] sm:min-h-[420px] object-contain rounded-xl transition-transform duration-200">

            {{-- Tombol Navigasi Kanan --}}
            <button type="button" 
                    id="modalNextBtn"
                    onclick="nextModalImage()"
                    style="display: none;"
                    class="absolute right-3 top-1/2 -translate-y-1/2 w-10 h-10 rounded-full bg-black/60 hover:bg-black/90 text-white flex items-center justify-center text-2xl transition-all cursor-pointer z-10 backdrop-blur-xs shadow-lg">
                <i class="bx bx-chevron-right"></i>
            </button>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script>
    const fotoList = @json(array_map(fn($f) => asset('storage/' . $f), $laporan->bukti_array));
    let currentFotoIndex = 0;

    function openImageModal(src) {
        const idx = fotoList.indexOf(src);
        currentFotoIndex = idx >= 0 ? idx : 0;
        updateModalImage();
        
        const modal = document.getElementById('imageModal');
        if (modal) {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            document.body.style.overflow = 'hidden';
        }
    }

    function updateModalImage() {
        if (!fotoList || fotoList.length === 0) return;
        const currentSrc = fotoList[currentFotoIndex];
        const img = document.getElementById('modalImage');
        const counter = document.getElementById('modalFotoCounter');
        const prevBtn = document.getElementById('modalPrevBtn');
        const nextBtn = document.getElementById('modalNextBtn');

        if (img) img.src = currentSrc;
        if (counter) counter.textContent = (currentFotoIndex + 1) + ' / ' + fotoList.length;

        if (prevBtn && nextBtn) {
            const hasMultiple = fotoList.length > 1;
            prevBtn.style.display = hasMultiple ? 'flex' : 'none';
            nextBtn.style.display = hasMultiple ? 'flex' : 'none';
        }
    }

    function prevModalImage() {
        if (!fotoList || fotoList.length <= 1) return;
        currentFotoIndex = (currentFotoIndex - 1 + fotoList.length) % fotoList.length;
        updateModalImage();
    }

    function nextModalImage() {
        if (!fotoList || fotoList.length <= 1) return;
        currentFotoIndex = (currentFotoIndex + 1) % fotoList.length;
        updateModalImage();
    }

    function closeImageModal() {
        const modal = document.getElementById('imageModal');
        if (modal) {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            document.body.style.overflow = 'auto';
        }
    }

    document.addEventListener('keydown', function(e) {
        const modal = document.getElementById('imageModal');
        if (!modal || modal.classList.contains('hidden')) return;

        if (e.key === 'Escape') {
            closeImageModal();
        } else if (e.key === 'ArrowLeft') {
            prevModalImage();
        } else if (e.key === 'ArrowRight') {
            nextModalImage();
        }
    });
</script>
@endpush