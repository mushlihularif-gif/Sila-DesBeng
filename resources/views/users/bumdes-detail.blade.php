@extends('layouts.user')

@section('page')
<main class="flex-grow relative w-full">
    <section class="relative z-10 min-h-screen pt-40 pb-16">
        <!-- Decorative Background Elements -->
        <div class="absolute inset-0 z-0 pointer-events-none">
            <img src="{{ asset('Admin/img/elements/background.webp') }}" class="w-full h-full object-cover" alt="Background">
        </div>

        <div class="max-w-7xl mx-auto px-6 relative z-10">
            <!-- Header Section -->
            <div class="text-center mb-16">
                <h1 class="text-3xl md:text-4xl font-bold text-gray-800 mb-8">
                    {{ $region ? $region->name : 'Kabupaten Bengkalis' }}
                </h1>
            </div>

            <!-- Unit Pelayanan Section -->
            @include('users.partials.unit-carousel')

            <!-- Pemerintahan Section -->
            <div class="mb-16 mt-24">
                <div class="text-center mb-12">
                    <h2 class="text-3xl md:text-4xl font-bold">
                        <span class="bg-gradient-to-r from-[#115789] to-[#60a5fa] bg-clip-text text-transparent">Pemerintah {{ $region ? $region->name : 'Kabupaten Bengkalis' }}</span>
                    </h2>
                    <p class="text-gray-500 text-sm mt-2">Bagan Struktur Organisasi dan Tata Kerja Pemerintahan</p>
                </div>

                @php
                    $layoutMode = ($region && isset($region->settings['struktur_layout'])) ? $region->settings['struktur_layout'] : 'hierarki';
                    $level1 = $members->where('level', 1);
                    $level2 = $members->where('level', 2);
                    $level3 = $members->where('level', 3);
                    $level4 = $members->where('level', 4);
                    $unclassified = $members->whereNotIn('level', [1, 2, 3, 4]);
                @endphp

                @if($layoutMode === 'sejajar')
                    {{-- TAMPILAN SEJAJAR (GRID MENDATAR) --}}
                    <div class="flex flex-wrap justify-center gap-10 mb-16 mt-6">
                        @foreach($members as $member)
                        <div class="member-card transition-all duration-300 text-center w-64">
                            <div class="relative mx-auto mb-6" style="width: 175px; height: 215px;">
                                <div class="absolute inset-0 opacity-90" style="
                                    border-radius: 0 50px 0 50px; 
                                    transform: translate(6px, 6px); 
                                    padding: 3px; 
                                    background: linear-gradient(135deg, #115789, #3b82f6); 
                                    -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0); 
                                    -webkit-mask-composite: xor; 
                                    mask-composite: exclude;
                                "></div>
                                <div class="absolute inset-0 overflow-hidden bg-gray-50 animate-float z-10" style="border-radius: 0 50px 0 50px; box-shadow: 2px 2px 8px rgba(0, 0, 0, 0.18);">
                                    <img src="{{ $member->photo_url }}" 
                                         alt="{{ $member->name }}"
                                         class="w-full h-full object-cover">
                                </div>
                            </div>
                            <h3 class="text-lg font-bold mb-1" style="color: #000000;">{{ $member->name }}</h3>
                            <p class="text-sm font-semibold text-blue-900">{{ $member->position }}</p>
                        </div>
                        @endforeach
                    </div>
                @else
                    {{-- TAMPILAN BERJENJANG (HIERARKI) --}}
                    <div class="hierarchy-container max-w-6xl mx-auto mb-16">
                        {{-- TINGKAT 1: PIMPINAN UTAMA --}}
                        @if($level1->count() > 0)
                            <div class="hierarchy-tier hierarchy-tier-1">
                                <div class="flex flex-wrap justify-center gap-10">
                                    @foreach($level1 as $member)
                                    <div class="member-card transition-all duration-300 text-center w-64">
                                    <div class="relative mx-auto mb-6" style="width: 175px; height: 215px;">
                                        <div class="absolute inset-0 opacity-90" style="
                                            border-radius: 0 50px 0 50px; 
                                            transform: translate(6px, 6px); 
                                            padding: 3px; 
                                            background: linear-gradient(135deg, #115789, #3b82f6); 
                                            -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0); 
                                            -webkit-mask-composite: xor; 
                                            mask-composite: exclude;
                                        "></div>
                                        <div class="absolute inset-0 overflow-hidden bg-gray-50 animate-float z-10" style="border-radius: 0 50px 0 50px; box-shadow: 2px 2px 8px rgba(0, 0, 0, 0.18);">
                                            <img src="{{ $member->photo_url }}" 
                                                 alt="{{ $member->name }}"
                                                 class="w-full h-full object-cover">
                                        </div>
                                    </div>
                                    <h3 class="text-lg font-bold mb-1" style="color: #000000;">{{ $member->name }}</h3>
                                    <p class="text-sm font-semibold text-blue-900">{{ $member->position }}</p>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- PENGHUBUNG HIERARKI TINGKAT 1 KE 2 --}}
                    @if($level1->count() > 0 && ($level2->count() > 0 || $level3->count() > 0 || $level4->count() > 0))
                        <div class="flex justify-center my-3">
                            <div class="h-8 w-0.5 bg-gradient-to-b from-[#115789] to-[#3b82f6]"></div>
                        </div>
                    @endif

                    {{-- TINGKAT 2: SEKRETARIS / WAKIL --}}
                    @if($level2->count() > 0)
                        <div class="hierarchy-tier hierarchy-tier-2">
                            <div class="flex flex-wrap justify-center gap-10">
                                @foreach($level2 as $member)
                                <div class="member-card transition-all duration-300 text-center w-64">
                                    <div class="relative mx-auto mb-6" style="width: 165px; height: 205px;">
                                        <div class="absolute inset-0 opacity-90" style="
                                            border-radius: 0 50px 0 50px; 
                                            transform: translate(6px, 6px); 
                                            padding: 3px; 
                                            background: linear-gradient(135deg, #0284c7, #38bdf8); 
                                            -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0); 
                                            -webkit-mask-composite: xor; 
                                            mask-composite: exclude;
                                        "></div>
                                        <div class="absolute inset-0 overflow-hidden bg-gray-50 animate-float z-10" style="border-radius: 0 50px 0 50px; box-shadow: 2px 2px 6px rgba(0, 0, 0, 0.15);">
                                            <img src="{{ $member->photo_url }}" 
                                                 alt="{{ $member->name }}"
                                                 class="w-full h-full object-cover">
                                        </div>
                                    </div>
                                    <h3 class="text-lg font-bold mb-1" style="color: #000000;">{{ $member->name }}</h3>
                                    <p class="text-sm font-semibold text-sky-900">{{ $member->position }}</p>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- PENGHUBUNG HIERARKI TINGKAT 2 KE 3 --}}
                    @if($level2->count() > 0 && ($level3->count() > 0 || $level4->count() > 0))
                        <div class="flex justify-center my-3">
                            <div class="h-8 w-0.5 bg-gradient-to-b from-[#38bdf8] to-[#10b981]"></div>
                        </div>
                    @endif

                    {{-- TINGKAT 3: KEPALA SEKSI / KAUR / KEPALA UNIT --}}
                    @if($level3->count() > 0)
                        <div class="hierarchy-tier hierarchy-tier-3">
                            <div class="flex flex-wrap justify-center gap-8">
                                @foreach($level3 as $member)
                                <div class="member-card transition-all duration-300 text-center w-64">
                                    <div class="relative mx-auto mb-6" style="width: 160px; height: 200px;">
                                        <div class="absolute inset-0 opacity-90" style="
                                            border-radius: 0 50px 0 50px; 
                                            transform: translate(6px, 6px); 
                                            padding: 3px; 
                                            background: linear-gradient(135deg, #059669, #34d399); 
                                            -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0); 
                                            -webkit-mask-composite: xor; 
                                            mask-composite: exclude;
                                        "></div>
                                        <div class="absolute inset-0 overflow-hidden bg-gray-50 animate-float z-10" style="border-radius: 0 50px 0 50px; box-shadow: 2px 2px 6px rgba(0, 0, 0, 0.15);">
                                            <img src="{{ $member->photo_url }}" 
                                                 alt="{{ $member->name }}"
                                                 class="w-full h-full object-cover">
                                        </div>
                                    </div>
                                    <h3 class="text-base font-bold mb-1" style="color: #000000;">{{ $member->name }}</h3>
                                    <p class="text-sm font-medium text-gray-700">{{ $member->position }}</p>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- PENGHUBUNG HIERARKI TINGKAT 3 KE 4 --}}
                    @if($level3->count() > 0 && ($level4->count() > 0 || $unclassified->count() > 0))
                        <div class="flex justify-center my-3">
                            <div class="h-8 w-0.5 bg-gradient-to-b from-[#34d399] to-[#94a3b8]"></div>
                        </div>
                    @endif

                    {{-- TINGKAT 4: STAF PELAKSANA / DUSUN / LAINNYA --}}
                    @if($level4->count() > 0 || $unclassified->count() > 0)
                        <div class="hierarchy-tier hierarchy-tier-4">
                            <div class="flex flex-wrap justify-center gap-8">
                                @foreach($level4->concat($unclassified) as $member)
                                <div class="member-card transition-all duration-300 text-center w-64">
                                    <div class="relative mx-auto mb-6" style="width: 155px; height: 195px;">
                                        <div class="absolute inset-0 opacity-90" style="
                                            border-radius: 0 50px 0 50px; 
                                            transform: translate(6px, 6px); 
                                            padding: 3px; 
                                            background: linear-gradient(135deg, #64748b, #94a3b8); 
                                            -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0); 
                                            -webkit-mask-composite: xor; 
                                            mask-composite: exclude;
                                        "></div>
                                        <div class="absolute inset-0 overflow-hidden bg-gray-50 animate-float z-10" style="border-radius: 0 50px 0 50px; box-shadow: 2px 2px 6px rgba(0, 0, 0, 0.15);">
                                            <img src="{{ $member->photo_url }}" 
                                                 alt="{{ $member->name }}"
                                                 class="w-full h-full object-cover">
                                        </div>
                                    </div>
                                    <h3 class="text-base font-bold mb-1" style="color: #000000;">{{ $member->name }}</h3>
                                    <p class="text-sm font-medium text-gray-600">{{ $member->position }}</p>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
                @endif
            </div>

            <!-- WhatsApp Contact Button -->
            @if($isWhatsappActive)
            <div class="text-center mb-16">
                <a href="{{ $whatsappLink }}" 
                   target="_blank"
                   class="inline-flex items-center gap-3 px-8 py-4 bg-[#25D366] text-white font-semibold rounded-full hover:bg-[#20BA5A] hover:shadow-lg transition-all duration-300 transform hover:scale-105">
                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
                    </svg>
                    <span>Halo Layanan</span>
                </a>
            </div>
            @endif
        </div>
    </section>
</main>
@endsection

@push('styles')
@include('users.partials.unit-carousel-styles')
<style>
    * {
        font-family: 'Inter', sans-serif;
    }

    /* Member Card Styles */
    .member-card {
        text-align: center;
        width: 240px;
        padding: 1rem;
        transition: all 0.3s ease;
    }

    .member-card:hover {
        transform: translateY(-4px);
    }

    /* Float Animation */
    @keyframes float {
        0% { transform: translateY(0px); }
        50% { transform: translateY(-10px); }
        100% { transform: translateY(0px); }
    }
    
    .animate-float {
        animation: float 4s ease-in-out infinite;
    }

    /* Smooth animations */
    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    section > div {
        animation: fadeIn 0.6s ease-out;
    }
</style>
@endpush

@push('scripts')
    @include('users.partials.unit-carousel-scripts')
@endpush
