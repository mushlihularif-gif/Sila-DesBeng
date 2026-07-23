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
                    <div class="bg-white/90 backdrop-blur-md rounded-2xl p-6 shadow-xl border border-gray-100 hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-1">
                        <div class="flex items-start justify-between mb-4">
                            <div>
                                <h3 class="text-xl font-bold text-gray-900 mb-1">{{ $amb->nama_mobil }}</h3>
                                <div class="inline-flex items-center px-3 py-1 rounded-full bg-red-100 text-red-700 text-sm font-bold">
                                    {{ str_replace('Plat: ', '', $amb->deskripsi) }}
                                </div>
                            </div>
                            @if($amb->foto)
                            <img src="{{ asset('storage/' . $amb->foto) }}" alt="Ambulans" class="w-20 h-20 object-cover rounded-lg shadow">
                            @else
                            <div class="w-16 h-16 bg-gray-100 rounded-lg flex items-center justify-center text-gray-400">
                                <i class='bx bx-image-alt text-2xl'></i>
                            </div>
                            @endif
                        </div>
                        
                        <div class="space-y-3 mb-6">
                            <div class="flex items-center text-gray-700">
                                <i class='bx bx-user-circle text-xl text-gray-400 w-8'></i>
                                <div>
                                    <p class="text-xs text-gray-500 font-semibold uppercase">Nama Supir</p>
                                    <p class="font-medium">{{ $amb->nama_supir }}</p>
                                </div>
                            </div>
                            <div class="flex items-center text-gray-700">
                                <i class='bx bxl-whatsapp text-xl text-gray-400 w-8'></i>
                                <div>
                                    <p class="text-xs text-gray-500 font-semibold uppercase">Kontak Supir</p>
                                    <p class="font-medium">{{ $amb->kontak_supir }}</p>
                                </div>
                            </div>
                        </div>

                        <a href="{{ route('mobil.rental.booking', $amb->id) }}" class="block w-full py-3 bg-gray-900 hover:bg-gray-800 text-white font-bold text-center rounded-xl transition-colors">
                            Jadwalkan Ambulans (Non-Darurat)
                        </a>
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
