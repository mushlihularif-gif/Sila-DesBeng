@forelse($mobilBookings as $booking)
                <div class="bg-white rounded-2xl shadow-lg overflow-hidden activity-item" id="activity-mobil-{{ $booking->id }}" data-status="{{ strtolower($booking->status) }}">
                    <div class="p-6">
                        <div class="flex flex-col sm:flex-row gap-6">
                            <div class="w-full sm:w-32 h-48 sm:h-32 bg-gray-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                @if($booking->mobil && $booking->mobil->foto)
                                <img src="{{ asset('storage/' . $booking->mobil->foto) }}" class="w-full h-full object-cover rounded-lg">
                                @else
                                <i class="fas fa-car text-4xl text-gray-400"></i>
                                @endif
                            </div>
                            <div class="flex-1">
                                <h3 class="text-xl font-bold text-gray-800 mb-2">{{ $booking->mobil?->nama_mobil ?? 'Sewa Mobil' }}</h3>
                                <p class="text-sm text-gray-600 mb-4">
                                    {{ \Carbon\Carbon::parse($booking->created_at)->locale('id')->isoFormat('dddd, DD MMMM YYYY HH:mm') }} WIB
                                </p>
                                <p class="text-sm text-gray-600 mb-2">Tanggal Mulai: {{ \Carbon\Carbon::parse($booking->start_date)->format('d M Y') }}</p>

                                @if($booking->assigned_supir_id && $booking->supir)
                                <div class="mt-3 p-3 bg-blue-50 rounded-lg border border-blue-100">
                                    <p class="text-sm font-semibold text-blue-800 mb-1"><i class="fas fa-id-card mr-2"></i>Petugas / Supir yang Ditugaskan:</p>
                                    <p class="text-sm text-blue-700 font-bold mb-1">{{ $booking->supir->nama }}</p>
                                    @if($booking->supir->kontak)
                                    <p class="text-sm text-blue-700">
                                        <a href="https://wa.me/{{ preg_replace('/^0/', '62', $booking->supir->kontak) }}" target="_blank" class="hover:underline">
                                            <i class="fab fa-whatsapp mr-1"></i> {{ $booking->supir->kontak }}
                                        </a>
                                    </p>
                                    @endif
                                </div>
                                @endif
                            </div>
                            <div class="text-left sm:text-right mt-4 sm:mt-0">
                                <p class="text-lg font-bold text-gray-800 mb-2">{{ ucfirst($booking->status) }}</p>
                                @if(in_array($booking->status, ['completed', 'cancelled', 'rejected']))
                                <button type="button" class="delete-order-btn px-4 py-2 bg-red-50 text-red-500 rounded-lg text-sm font-semibold hover:bg-red-100 transition-colors" data-type="mobil" data-id="{{ $booking->id }}">
                                    Hapus Riwayat
                                </button>
                                @elseif($booking->canBeCancelled && $booking->canBeCancelled())
                                <button type="button" class="cancel-order-btn px-4 py-2 bg-yellow-50 text-yellow-600 rounded-lg text-sm font-semibold hover:bg-yellow-100 transition-colors" data-type="mobil" data-id="{{ $booking->id }}">
                                    Batalkan
                                </button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                @empty
                @if($mobilBookings->currentPage() == 1)
                <div class="bg-white rounded-2xl shadow-lg p-8 text-center">
                    <p class="text-gray-500">Belum ada riwayat penyewaan mobil</p>
                </div>
                @endif
                @endforelse
