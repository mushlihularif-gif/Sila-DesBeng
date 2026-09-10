@extends('layouts.user')

@section('page')
<main class="flex-grow relative w-full">
    <section class="relative z-10 min-h-screen pt-32 pb-16 bg-cover bg-center bg-no-repeat bg-fixed" 
             style="background-image: url('{{ asset('Admin/img/elements/background1.png') }}');">
        
        <!-- White Overlay -->
        <div class="absolute inset-0 bg-white/40 pointer-events-none"></div>

        <div class="max-w-4xl mx-auto px-6 relative z-20">
            <!-- Header -->
            <div class="text-center mb-10 mt-8">
                <div class="inline-block p-4 rounded-full bg-red-100 mb-4 animate-bounce">
                    <img src="{{ asset('Admin/img/elements/ambulance.png') }}" alt="Ambulans" class="w-16 h-16 object-contain" onerror="this.src='{{ asset('Admin/img/illustrations/isewalogo.webp') }}'">
                </div>
                <h1 class="text-3xl md:text-5xl font-black mb-4 tracking-tight">
                    <span class="text-gray-900">Layanan </span>
                    <span class="text-red-600">Ambulans Desa</span>
                </h1>
                <p class="text-gray-700 font-medium text-lg max-w-2xl mx-auto">
                    Siaga melayani kondisi darurat dan kebutuhan transportasi medis warga desa.
                </p>
            </div>

            <!-- Tombol Darurat -->
            <div class="flex justify-center mb-16">
                @if(isset($regionSettings['kontak_ambulans']) && $regionSettings['kontak_ambulans'])
                <a href="https://wa.me/{{ preg_replace('/^0/', '62', $regionSettings['kontak_ambulans']) }}" target="_blank" 
                   class="relative group block">
                    <!-- Animasi Ping/Pulse di belakang tombol -->
                    <div class="absolute inset-0 bg-red-500 rounded-full animate-ping opacity-75 group-hover:opacity-100"></div>
                    <div class="relative px-10 py-6 bg-gradient-to-r from-red-600 to-red-500 hover:from-red-500 hover:to-red-400 text-white font-black text-2xl md:text-3xl rounded-full shadow-[0_0_40px_rgba(220,38,38,0.6)] hover:shadow-[0_0_60px_rgba(220,38,38,0.8)] transition-all duration-300 flex items-center justify-center gap-4 border-4 border-red-300">
                        <i class='bx bxs-phone-call animate-pulse text-4xl'></i>
                        PANGGIL DARURAT
                    </div>
                </a>
                @else
                <div class="bg-yellow-100 border-l-4 border-yellow-500 p-4 rounded text-yellow-700">
                    <p class="font-bold">Info:</p> Nomor darurat ambulans belum diatur oleh perangkat desa.
                </div>
                @endif
            </div>

            <!-- Daftar Armada -->
            <div class="mb-12">
                <h2 class="text-2xl font-bold text-gray-800 mb-6 flex items-center gap-2">
                    <i class='bx bx-car text-red-500'></i> Armada Bersiaga
                </h2>
                
                @if($ambulansList->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    @foreach($ambulansList as $amb)
                    @php
                        $cleanPlat = $amb->plat_nomor ?: str_replace('Plat: ', '', $amb->deskripsi);
                        $images = collect([$amb->foto, $amb->foto_2, $amb->foto_3])->filter()->values();
                        $hasMultiple = $images->count() > 1;
                        $hasCustomDesc = $amb->deskripsi && !str_starts_with(trim($amb->deskripsi), 'Plat:');
                    @endphp
                    <div class="bg-white/95 backdrop-blur-md rounded-3xl p-5 sm:p-6 shadow-xl border border-gray-100 hover:shadow-2xl transition-all duration-300 flex flex-col h-full">
                        
                        <!-- Foto Armada Slider / Banner -->
                        <div class="relative w-full aspect-video sm:aspect-[16/10] rounded-2xl overflow-hidden mb-5 bg-gray-100 group">
                            @if($images->count() > 0)
                                <div id="slider-amb-{{ $amb->id }}" class="flex w-full h-full transition-transform duration-500 ease-out">
                                    @foreach($images as $imgIdx => $img)
                                    <div class="w-full h-full flex-shrink-0 flex-grow-0">
                                        <a href="{{ route('user.ambulans.show', $amb->id) }}" class="block w-full h-full">
                                            <img src="{{ asset('storage/' . $img) }}" alt="{{ $amb->nama_mobil }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                                        </a>
                                    </div>
                                    @endforeach
                                </div>

                                @if($hasMultiple)
                                <button type="button" onclick="slideAmb({{ $amb->id }}, -1)" class="absolute left-2 top-1/2 -translate-y-1/2 w-8 h-8 rounded-full bg-white/90 text-gray-800 flex items-center justify-center shadow opacity-90 sm:opacity-0 group-hover:opacity-100 transition-all z-10" aria-label="Sebelumnya">
                                    <i class="bx bx-chevron-left text-xl"></i>
                                </button>
                                <button type="button" onclick="slideAmb({{ $amb->id }}, 1)" class="absolute right-2 top-1/2 -translate-y-1/2 w-8 h-8 rounded-full bg-white/90 text-gray-800 flex items-center justify-center shadow opacity-90 sm:opacity-0 group-hover:opacity-100 transition-all z-10" aria-label="Berikutnya">
                                    <i class="bx bx-chevron-right text-xl"></i>
                                </button>
                                <div class="absolute bottom-2.5 left-1/2 -translate-x-1/2 flex gap-1.5 z-10 bg-black/40 backdrop-blur-sm px-2.5 py-1 rounded-full">
                                    @foreach($images as $dotIdx => $dot)
                                    <span class="amb-dot-{{ $amb->id }} {{ $dotIdx === 0 ? 'w-4 bg-white' : 'w-1.5 bg-white/50' }} h-1.5 rounded-full transition-all"></span>
                                    @endforeach
                                </div>
                                @endif
                            @else
                                <div class="w-full h-full flex flex-col items-center justify-center text-gray-400">
                                    <i class="bx bx-car text-5xl text-gray-300 mb-1"></i>
                                    <span class="text-xs">Foto belum tersedia</span>
                                </div>
                            @endif

                            <!-- Plat Badge Overlay -->
                            <div class="absolute top-3 left-3 px-3 py-1 rounded-full bg-red-600 text-white text-xs font-bold shadow flex items-center gap-1">
                                <i class="bx bxs-ambulance"></i>
                                <span>{{ $cleanPlat ?: 'Siaga Medis' }}</span>
                            </div>
                        </div>

                        <!-- Header Info Armada -->
                        <div class="mb-3">
                            <a href="{{ route('user.ambulans.show', $amb->id) }}" class="block">
                                <h3 class="text-lg sm:text-xl font-bold text-gray-900 hover:text-red-600 transition-colors leading-snug mb-1">
                                    {{ $amb->nama_mobil }}
                                </h3>
                            </a>
                            @if($hasCustomDesc)
                            <p class="text-xs sm:text-sm text-gray-600 line-clamp-2 leading-relaxed mt-1">
                                {{ $amb->deskripsi }}
                            </p>
                            @else
                            <p class="text-xs text-gray-500 line-clamp-2 mt-1">
                                Armada transportasi medis darurat desa siaga 24 jam.
                            </p>
                            @endif
                        </div>
                        
                        <!-- Section Supir Siaga -->
                        <div class="mb-5 flex-grow">
                            <div class="flex items-center justify-between gap-2 mb-2.5">
                                <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider flex items-center gap-1">
                                    <i class="bx bx-user-pin text-sm text-red-500"></i> Tim Supir / Penanggung Jawab
                                </h4>
                                @if($amb->supirs->count() > 0)
                                <span class="text-[11px] font-semibold text-gray-500">{{ $amb->supirs->count() }} Orang</span>
                                @endif
                            </div>

                            @if($amb->supirs->count() > 0)
                                <div class="space-y-2">
                                    @foreach($amb->supirs as $supir)
                                    @php
                                        $cleanWa = preg_replace('/[^0-9]/', '', $supir->kontak ?? '');
                                        $waUrl = $cleanWa ? ('https://wa.me/' . (str_starts_with($cleanWa, '0') ? '62' . substr($cleanWa, 1) : $cleanWa)) : null;
                                        $avatar = $supir->foto ? asset('storage/' . $supir->foto) : asset('Admin/img/avatars/pria.png');
                                        $isTersedia = ($supir->status == 'Tersedia');
                                    @endphp
                                    <div class="flex items-center justify-between p-2.5 bg-gray-50/90 rounded-xl border border-gray-100 hover:bg-red-50/50 transition-colors gap-2">
                                        <div class="flex items-center gap-2.5 min-w-0">
                                            <div class="relative flex-shrink-0">
                                                <img src="{{ $avatar }}" class="w-9 h-9 rounded-full object-cover shadow-xs border border-white" onerror="this.src='{{ asset('Admin/img/avatars/pria.png') }}'">
                                                <span class="absolute bottom-0 right-0 w-2.5 h-2.5 rounded-full {{ $isTersedia ? 'bg-green-500' : 'bg-yellow-500' }} border-2 border-white"></span>
                                            </div>
                                            <div class="min-w-0">
                                                <p class="font-bold text-gray-900 text-xs truncate leading-tight">{{ $supir->nama }}</p>
                                                <p class="text-[11px] text-gray-500 flex items-center gap-1 mt-0.5">
                                                    <i class="bx bx-phone text-[10px] text-gray-400"></i>
                                                    <span>{{ $supir->kontak ?? '-' }}</span>
                                                </p>
                                            </div>
                                        </div>
                                        @if($waUrl)
                                        <a href="{{ $waUrl }}" target="_blank" class="flex-shrink-0 inline-flex items-center gap-1 px-2.5 py-1.5 bg-green-600 hover:bg-green-700 text-white rounded-lg text-xs font-bold transition shadow-xs">
                                            <i class="bx bxl-whatsapp text-sm"></i>
                                            <span>WA</span>
                                        </a>
                                        @endif
                                    </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="bg-gray-50 p-3 rounded-xl text-center border border-dashed border-gray-200">
                                    <span class="text-xs text-gray-500 italic">Belum ada supir ditugaskan oleh admin desa</span>
                                </div>
                            @endif
                        </div>

                        <!-- Action Buttons -->
                        <div class="mt-auto pt-3 border-t border-gray-100 flex flex-col sm:flex-row gap-2">
                            <a href="{{ route('user.ambulans.show', $amb->id) }}" class="flex-1 py-2.5 px-3 bg-red-50 hover:bg-red-100 text-red-700 border border-red-200 font-bold text-center rounded-xl transition-all text-xs flex items-center justify-center gap-1.5">
                                <i class="bx bx-info-circle text-sm"></i> Detail Armada
                            </a>
                            <a href="{{ route('mobil.rental.booking', $amb->id) }}" class="flex-1 py-2.5 px-3 bg-gray-900 hover:bg-gray-800 text-white font-bold text-center rounded-xl transition-colors text-xs flex items-center justify-center gap-1.5">
                                <i class="bx bx-calendar text-sm"></i> Jadwalkan
                            </a>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="bg-white/80 p-8 rounded-2xl shadow-sm text-center border border-gray-200">
                    <i class='bx bx-car text-6xl text-gray-300 mb-4'></i>
                    <h3 class="text-xl font-bold text-gray-700 mb-2">Belum Ada Armada</h3>
                    <p class="text-gray-500">Pemerintah desa belum mendaftarkan armada ambulans ke dalam sistem.</p>
                </div>
                @endif
            </div>

            <!-- SOP Section -->
            @if(isset($regionSettings['sop_ambulans']) && $regionSettings['sop_ambulans'])
            <div class="bg-blue-50 border border-blue-100 rounded-2xl p-6 shadow-sm">
                <h3 class="text-lg font-bold text-blue-900 mb-3 flex items-center gap-2">
                    <i class='bx bx-info-circle text-blue-600'></i> Ketentuan & SOP Ambulans
                </h3>
                <div class="text-blue-800 text-sm whitespace-pre-wrap leading-relaxed">
                    {{ $regionSettings['sop_ambulans'] }}
                </div>
            </div>
            @endif

        </div>
    </section>
</main>
@endsection

@push('scripts')
<script>
    const ambIndices = {};
    function slideAmb(id, direction) {
        const slider = document.getElementById('slider-amb-' + id);
        const dots = document.querySelectorAll('.amb-dot-' + id);
        if (!slider || dots.length <= 1) return;
        
        if (typeof ambIndices[id] === 'undefined') ambIndices[id] = 0;
        ambIndices[id] = (ambIndices[id] + direction + dots.length) % dots.length;
        slider.style.transform = `translateX(-${ambIndices[id] * 100}%)`;
        
        dots.forEach((dot, idx) => {
            if (idx === ambIndices[id]) {
                dot.classList.remove('w-1.5', 'bg-white/50');
                dot.classList.add('w-4', 'bg-white');
            } else {
                dot.classList.remove('w-4', 'bg-white');
                dot.classList.add('w-1.5', 'bg-white/50');
            }
        });
    }
</script>
@endpush
