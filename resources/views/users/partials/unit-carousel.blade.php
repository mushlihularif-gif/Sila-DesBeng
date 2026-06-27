            <!-- Unit Pelayanan Section -->
            <div id="unit-carousel-container" class="max-w-7xl mx-auto px-6 py-16 overflow-hidden">
                <div class="max-w-7xl mx-auto">
                    <div class="text-center mb-16 relative">
                        <h2 class="text-3xl font-bold mb-2">
                            <span class="text-gray-800">Unit </span>
                            <span class="bg-gradient-to-r from-[#115789] to-[#60a5fa] bg-clip-text text-transparent">Pelayanan</span>
                        </h2>
                    </div>

                    <div class="relative h-[400px] w-full flex justify-center items-center">
                        <div class="relative w-full max-w-6xl mx-auto h-full">
                            @php
                                $isServiceActive = function($name) use ($activeServices, $region) {
                                    // Jika tidak ada region spesifik (misal diakses manual), tampilkan semua
                                    if (!$region) return true; 
                                    
                                    // Mapping nama tampilan ke nama layanan di database
                                    $map = [
                                        'Unit Penyewaan Alat' => 'Penyewaan Alat',
                                        'Unit Penjualan Gas' => 'Penjualan Gas',
                                        'Unit Penyewaan Mobil' => 'Penyewaan Mobil',
                                        'Unit Peminjaman Fasilitas Umum' => 'Peminjaman Fasilitas Umum',
                                        'Pelaporan Warga' => 'Pelaporan Warga',
                                        'Pengumuman dan Event' => 'Pengumuman dan Event'
                                    ];
                                    
                                    $dbName = $map[$name] ?? $name;
                                    
                                    // Pengecualian: Mungkin 'Pengumuman dan Event' selalu aktif untuk semua desa
                                    if ($dbName === 'Pengumuman dan Event') return true;
                                    
                                    return in_array($dbName, $activeServices);
                                };

                                $index = 0;
                            @endphp

                            @if($isServiceActive('Unit Penyewaan Alat'))
                            <div class="unit-card cursor-pointer hover:scale-105 transition-transform" data-index="{{ $index++ }}" data-name="Unit Penyewaan Alat" onclick="window.location.href='{{ route('rental.equipment') . '?region_id=' . $region->id }}'">
                                <img src="{{ asset('User/img/elemen/F1.png') }}" alt="Alat">
                            </div>
                            @endif

                            @if($isServiceActive('Unit Penjualan Gas'))
                            <div class="unit-card cursor-pointer hover:scale-105 transition-transform" data-index="{{ $index++ }}" data-name="Unit Penjualan Gas" onclick="window.location.href='{{ route('gas.sales') . '?region_id=' . $region->id }}'">
                                <img src="{{ asset('User/img/elemen/F2.png') }}" alt="Gas">
                            </div>
                            @endif

                            @if($isServiceActive('Unit Penyewaan Mobil'))
                            <div class="unit-card cursor-pointer hover:scale-105 transition-transform" data-index="{{ $index++ }}" data-name="Unit Penyewaan Mobil" onclick="window.location.href='{{ route('mobil.rental.equipment') . '?region_id=' . $region->id }}'">
                                <img src="{{ asset('User/img/elemen/mobil.png') }}" alt="Mobil">
                            </div>
                            @endif

                            @if($isServiceActive('Unit Peminjaman Fasilitas Umum'))
                            <div class="unit-card cursor-pointer hover:scale-105 transition-transform" data-index="{{ $index++ }}" data-name="Unit Peminjaman Fasilitas Umum" onclick="window.location.href='{{ route('user.fasilitas-umum.equipment') . '?region_id=' . $region->id }}'">
                                <img src="{{ asset('User/img/elemen/fasilitas.png') }}" alt="Fasilitas">
                            </div>
                            @endif
                            
                            @if($isServiceActive('Pelaporan Warga'))
                            <div class="unit-card cursor-pointer hover:scale-105 transition-transform" data-index="{{ $index++ }}" data-name="Pelaporan Warga" onclick="window.location.href='{{ route('pelaporan.landing') . '?region_id=' . $region->id }}'">
                                <img src="{{ asset('User/img/elemen/lapor.png') }}" alt="Lapor">
                            </div>
                            @endif

                            @if($isServiceActive('Pengumuman dan Event'))
                            <div class="unit-card cursor-pointer hover:scale-105 transition-transform" data-index="{{ $index++ }}" data-name="Pengumuman dan Event" onclick="window.location.href='{{ route('announcements.index') . '?region_id=' . $region->id }}'">
                                <img src="{{ asset('User/img/elemen/event.png') }}" alt="Event">
                            </div>
                            @endif
                        </div>

                        <div class="absolute -bottom-6 left-0 right-0 flex items-center justify-center gap-4 md:gap-12 z-[60]">
                            <button id="unit-prev"
                                class="bg-white hover:bg-gray-50 text-gray-800 rounded-full p-3 shadow-lg border border-gray-100 transition-transform active:scale-95">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                        d="M15 19l-7-7 7-7" />
                                </svg>
                            </button>

                            <div class="min-w-[300px] text-center">
                                <h3 id="unit-title"
                                    class="text-xl md:text-2xl font-bold text-black transition-all duration-300">
                                    Unit Penyewaan Alat
                                </h3>
                            </div>

                            <button id="unit-next"
                                class="bg-white hover:bg-gray-50 text-gray-800 rounded-full p-3 shadow-lg border border-gray-100 transition-transform active:scale-95">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                        d="M9 5l7 7-7 7" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

