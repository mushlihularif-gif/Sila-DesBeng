@forelse($laporans as $laporan)
    @php
        $firstBukti = !empty($laporan->bukti_array) ? $laporan->bukti_array[0] : null;
        $statusLower = strtolower($laporan->status);
        $statusBadge = match($statusLower) {
            'pending' => ['bg' => 'bg-amber-100 text-amber-800 border-amber-200', 'label' => 'Menunggu Verifikasi'],
            'proses' => ['bg' => 'bg-blue-100 text-blue-800 border-blue-200', 'label' => 'Sedang Diproses'],
            'dilanjutkan' => ['bg' => 'bg-purple-100 text-purple-800 border-purple-200', 'label' => 'Dieskalasi ke ' . strtoupper($laporan->escalation_level ?? 'Desa')],
            'selesai' => ['bg' => 'bg-green-100 text-green-800 border-green-200', 'label' => 'Selesai Ditangani'],
            'ditolak' => ['bg' => 'bg-red-100 text-red-800 border-red-200', 'label' => 'Laporan Ditolak'],
            default => ['bg' => 'bg-gray-100 text-gray-800 border-gray-200', 'label' => ucfirst($laporan->status)],
        };
    @endphp
    <div class="transaction-card bg-white rounded-2xl shadow-lg overflow-hidden activity-item hover:shadow-xl transition-all duration-300 border border-gray-100" id="activity-laporan-{{ $laporan->id }}" data-status="{{ $statusLower }}">
        <div class="p-4 sm:p-6">
            <div class="flex flex-col sm:flex-row gap-4 sm:gap-6">
                <!-- Thumbnail Foto Bukti atau Placeholder Laporan -->
                <div class="w-full sm:w-32 h-48 sm:h-32 rounded-xl overflow-hidden flex-shrink-0 relative">
                    @if($firstBukti)
                        <img src="{{ asset('storage/' . $firstBukti) }}" 
                             alt="Bukti {{ $laporan->kategori }}" 
                             class="w-full h-full object-cover rounded-xl border border-gray-100"
                             onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                        <div class="w-full h-full bg-gradient-to-br from-blue-50 to-indigo-50 border border-blue-100 rounded-xl hidden items-center justify-center flex-col text-blue-500">
                            <i class="bx bx-megaphone text-4xl mb-1"></i>
                            <span class="text-[10px] font-bold text-blue-600 uppercase tracking-wider">Laporan</span>
                        </div>
                    @else
                        <div class="w-full h-full bg-gradient-to-br from-blue-50 to-indigo-50 border border-blue-100 rounded-xl flex items-center justify-center flex-col text-blue-500">
                            <i class="bx bx-megaphone text-4xl mb-1"></i>
                            <span class="text-[10px] font-bold text-blue-600 uppercase tracking-wider">Laporan</span>
                        </div>
                    @endif
                </div>

                <!-- Info Laporan -->
                <div class="flex-1 min-w-0">
                    <div class="flex flex-wrap items-center gap-2 mb-1.5">
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-700">
                            <i class="bx bx-category mr-1"></i>{{ $laporan->kategori }}
                        </span>
                        @if($laporan->tujuan_laporan)
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-100">
                                Ditujukan: {{ strtoupper($laporan->tujuan_laporan) }}
                            </span>
                        @endif
                    </div>

                    <h3 class="text-lg sm:text-xl font-bold text-gray-800 mb-1.5 leading-snug">
                        {{ $laporan->deskripsi ? Str::limit($laporan->deskripsi, 80) : 'Laporan ' . $laporan->kategori }}
                    </h3>

                    <p class="text-xs text-gray-500 mb-2 flex items-center gap-1">
                        <i class="bx bx-time-five"></i>
                        {{ \Carbon\Carbon::parse($laporan->created_at)->locale('id')->isoFormat('dddd, DD MMMM YYYY HH:mm') }} WIB
                    </p>

                    @if($laporan->lokasi)
                        <p class="text-xs text-gray-600 flex items-start gap-1">
                            <i class="bx bx-map-pin text-red-500 flex-shrink-0 mt-0.5"></i>
                            <span class="truncate">{{ $laporan->lokasi }}</span>
                        </p>
                    @endif
                </div>

                <!-- Status & Tombol Aksi -->
                <div class="text-left sm:text-right flex flex-col justify-between items-start sm:items-end gap-3 mt-4 sm:mt-0">
                    <span class="px-3 py-1 rounded-full text-xs font-bold border {{ $statusBadge['bg'] }}">
                        {{ $statusBadge['label'] }}
                    </span>

                    <div class="flex items-center gap-2 mt-auto">
                        <a href="{{ route('user.laporan.show', $laporan->id) }}" 
                           class="px-3.5 py-2 bg-blue-50 hover:bg-blue-100 text-blue-600 border border-blue-200 rounded-xl text-xs font-semibold transition-colors inline-flex items-center gap-1 shadow-xs">
                            <i class="bx bx-show text-sm"></i>
                            <span>Detail</span>
                        </a>

                        <button type="button" 
                                class="delete-order-btn px-3.5 py-2 bg-red-50 hover:bg-red-100 text-red-500 border border-red-200 rounded-xl text-xs font-semibold transition-colors inline-flex items-center gap-1 shadow-xs cursor-pointer" 
                                data-type="laporan" 
                                data-id="{{ $laporan->id }}">
                            <i class="bx bx-trash text-sm"></i>
                            <span>Hapus</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@empty
    @if($laporans->currentPage() == 1)
    <div class="bg-white rounded-2xl shadow-lg p-10 text-center border border-gray-100">
        <div class="w-16 h-16 bg-blue-50 text-blue-500 rounded-full flex items-center justify-center mx-auto mb-3">
            <i class="bx bx-folder-open text-3xl"></i>
        </div>
        <h4 class="font-bold text-gray-800 text-base mb-1">Belum Ada Riwayat Laporan</h4>
        <p class="text-gray-500 text-sm">Anda belum pernah membuat laporan warga di SiladesBeng.</p>
    </div>
    @endif
@endforelse
