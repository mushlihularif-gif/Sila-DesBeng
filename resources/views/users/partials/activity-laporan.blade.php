@forelse($laporans as $laporan)
                <div class="bg-white rounded-2xl shadow-lg overflow-hidden activity-item" id="activity-laporan-{{ $laporan->id }}" data-status="{{ strtolower($laporan->status) }}">
                    <div class="p-6">
                        <div class="flex flex-col sm:flex-row gap-6">
                            <div class="w-full sm:w-32 h-48 sm:h-32 bg-gray-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-bullhorn text-4xl text-gray-400"></i>
                            </div>
                            <div class="flex-1">
                                <h3 class="text-xl font-bold text-gray-800 mb-2">{{ $laporan->kategori }}</h3>
                                <p class="text-sm text-gray-600 mb-2">
                                    {{ \Carbon\Carbon::parse($laporan->created_at)->locale('id')->isoFormat('dddd, DD MMMM YYYY HH:mm') }} WIB
                                </p>
                                <p class="text-sm text-gray-600 mb-2 line-clamp-2">{{ $laporan->deskripsi }}</p>
                            </div>
                            <div class="text-left sm:text-right mt-4 sm:mt-0">
                                <p class="text-lg font-bold text-gray-800 mb-2">{{ ucfirst($laporan->status) }}</p>
                                <button type="button" class="delete-order-btn px-4 py-2 bg-red-50 text-red-500 rounded-lg text-sm font-semibold hover:bg-red-100 transition-colors" data-type="laporan" data-id="{{ $laporan->id }}">
                                    Hapus Riwayat
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                @empty
                @if($laporans->currentPage() == 1)
                <div class="bg-white rounded-2xl shadow-lg p-8 text-center">
                    <p class="text-gray-500">Belum ada riwayat laporan warga</p>
                </div>
                @endif
                @endforelse
