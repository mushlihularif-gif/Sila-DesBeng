@extends('layouts.user')

@section('page')
<main class="flex-grow relative w-full">
    <section class="relative z-10 min-h-screen pt-40 pb-16">
        <!-- Decorative Background Elements -->
        <div class="absolute inset-0 z-0 pointer-events-none">
            <img src="{{ asset('Admin/img/elements/background.png') }}" class="w-full h-full object-cover" alt="Background">
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
                <div class="text-center mb-16">
                    <h2 class="text-3xl md:text-4xl font-bold">
                        <span class="bg-gradient-to-r from-[#115789] to-[#60a5fa] bg-clip-text text-transparent">Pemerintah {{ $region ? $region->name : 'Kabupaten Bengkalis' }}</span>
                    </h2>
                </div>

                <!-- Members Grid -->
                <div class="flex flex-wrap justify-center gap-10 mb-16 mt-6">
                    @foreach($members as $member)
                    <div class="member-card transition-all duration-300 text-center w-64">
                        <div class="relative mx-auto mb-8" style="width: 170px; height: 210px;">
                            <!-- Bingkai Belakang (Gradient Biru) -->
                            <div class="absolute inset-0 opacity-90" style="
                                border-radius: 0 50px 0 50px; 
                                transform: translate(6px, 6px); 
                                padding: 3px; 
                                background: linear-gradient(135deg, #115789, #3b82f6); 
                                -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0); 
                                -webkit-mask-composite: xor; 
                                mask-composite: exclude;
                            "></div>
                            <!-- Foto dengan efek mengambang -->
                            <div class="absolute inset-0 overflow-hidden bg-gray-50 animate-float z-10" style="border-radius: 0 50px 0 50px; box-shadow: 2px 2px 6px rgba(0, 0, 0, 0.15);">
                                <img src="{{ $member->photo_url }}" 
                                     alt="{{ $member->name }}"
                                     class="w-full h-full object-cover">
                            </div>
                        </div>
                        <h3 class="text-lg font-bold mt-2 mb-1" style="color: #000000;">{{ $member->name }}</h3>
                        <p class="text-sm font-medium" style="color: #000000;">{{ $member->position }}</p>
                    </div>
                    @endforeach
                </div>
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
<style>
    * {
        font-family: 'Inter', sans-serif;
    }

    .state-4 {
        left: 0% !important;
        transform: translate(-50%, -50%) scale(0.5) !important;
        opacity: 0.6;
        z-index: 10;
        filter: grayscale(30%);
    }

    .state-5 {
        left: -20% !important;
        transform: translate(-50%, -50%) scale(0.5) !important;
        opacity: 0;
        z-index: 5;
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

    /* Responsive Mobile - 3 Column Layout (Center Focus) */
    @media (max-width: 768px) {
        .unit-card {
            width: 150px;
            height: 150px;
        }

        /* Slot Kiri (Background) */
        .state-0 {
            left: 10% !important;
            transform: translate(-50%, -50%) scale(0.6) !important;
            opacity: 0.6 !important;
            z-index: 20;
            filter: grayscale(20%);
        }

        /* Slot Tengah (Focus) */
        .state-1 {
            left: 50% !important;
            transform: translate(-50%, -50%) scale(1.8) !important;
            opacity: 1 !important;
            z-index: 50;
            filter: grayscale(0%) drop-shadow(0 10px 15px rgba(0,0,0,0.2));
        }

        /* Slot Kanan (Background) */
        .state-2 {
            left: 90% !important;
            transform: translate(-50%, -50%) scale(0.6) !important;
            opacity: 0.6 !important;
            z-index: 20;
            filter: grayscale(20%);
        }

        /* Antrian (Hidden) */
        .state-3 {
            left: 150% !important;
            transform: translate(-50%, -50%) scale(0.5) !important;
            opacity: 0;
        }
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
