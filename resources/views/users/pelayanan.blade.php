@extends('layouts.user')

@section('page')
    {{-- SECTION PELAYANAN --}}
    <section id="pelayanan" class="relative min-h-screen py-20">
        {{-- Background Decorations --}}
        <div class="absolute top-0 right-0 w-96 h-96 pointer-events-none overflow-hidden">
            <div class="absolute top-0 right-0 w-full h-full bg-gradient-to-bl from-blue-200/40 via-blue-300/30 to-transparent"
                style="clip-path: polygon(100% 0, 100% 100%, 40% 100%, 0 0);"></div>
        </div>

        <div class="absolute bottom-0 left-0 w-96 h-64 pointer-events-none">
            <div
                class="absolute bottom-0 left-0 w-full h-full bg-gradient-to-tr from-blue-300/30 via-yellow-200/20 to-transparent rounded-tr-full">
            </div>
        </div>

        <div class="absolute bottom-0 right-0 w-80 h-80 pointer-events-none">
            <div
                class="absolute bottom-0 right-0 w-full h-full bg-gradient-to-tl from-yellow-200/30 via-blue-200/20 to-transparent rounded-tl-full">
            </div>
        </div>

        <div class="relative z-10 max-w-6xl mx-auto px-6">
            {{-- Header --}}
            <div class="text-left mb-16 mt-16 md:mt-20 animate-section">
                <h1 class="text-5xl font-bold mb-2">
                    <span class="bg-gradient-to-r from-[#115789] to-blue-300 bg-clip-text text-transparent drop-shadow-[0_0_15px_rgba(59,130,246,0.5)]">Pelayanan</span>
                </h1>
            </div>

            {{-- Services Container --}}
            <div class="space-y-6">
                {{-- Service 1: Unit Penyewaan Alat --}}
                <div
                    class="bg-white/80 backdrop-blur-sm rounded-2xl p-8 shadow-md hover:shadow-xl transition-all duration-300 border border-white/50 animate-section">
                    <div class="flex flex-col md:flex-row gap-8 items-start">
                        <div class="flex-shrink-0">
                            <div class="w-32 h-32 flex items-center justify-center">
                                <img src="{{ asset('User/img/elemen/F0.png') }}" alt="Unit Penyewaan Alat"
                                    class="w-full h-full object-contain drop-shadow-md">
                            </div>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-2xl font-bold text-gray-900 mb-4">Unit Penyewaan Alat</h3>
                            <p class="text-gray-700 leading-relaxed text-justify">
                                Masyarakat dapat melakukan pemesanan sewa alat seperti tenda, kursi, meja, sound system, dan
                                diesel secara online. Sistem menampilkan ketersediaan alat secara real-time, harga sewa yang
                                transparan, serta bukti transaksi digital. Hal ini membantu menghindari bentrok jadwal dan
                                mempercepat pelayanan warga tanpa harus datang langsung ke kantor BUMDes
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Service 2: Pelaporan dan Monitoring Usaha --}}
                <div
                    class="bg-white/80 backdrop-blur-sm rounded-2xl p-8 shadow-md hover:shadow-xl transition-all duration-300 border border-white/50 animate-section">
                    <div class="flex flex-col md:flex-row gap-8 items-start">
                        <div class="flex-shrink-0">
                            <div class="w-32 h-32 flex items-center justify-center">
                                <img src="{{ asset('User/img/elemen/C2.png') }}" alt="Pelaporan dan Monitoring Usaha"
                                    class="w-full h-full object-contain drop-shadow-md">
                            </div>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-2xl font-bold text-gray-900 mb-4">Pelaporan dan Monitoring Usaha</h3>
                            <p class="text-gray-700 leading-relaxed text-justify">
                                Laporan keuangan, laporan transaksi, serta kinerja unit usaha secara otomatis dan real-time.
                                Sistem ini membantu meningkatkan akuntabilitas dan mempermudah evaluasi pengelolaan dana
                                desa
                                dengan laporan digital yang rapi dan terintegrasi
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Service 3: Penjualan Gas Desa --}}
                <div
                    class="bg-white/80 backdrop-blur-sm rounded-2xl p-8 shadow-md hover:shadow-xl transition-all duration-300 border border-white/50 animate-section">
                    <div class="flex flex-col md:flex-row gap-8 items-start">
                        <div class="flex-shrink-0">
                            <div class="w-32 h-32 flex items-center justify-center">
                                <img src="{{ asset('User/img/elemen/C3.png') }}" alt="Penjualan Gas Desa"
                                    class="w-full h-full object-contain drop-shadow-md">
                            </div>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-2xl font-bold text-gray-900 mb-4">Penjualan Gas Desa</h3>
                            <p class="text-gray-700 leading-relaxed text-justify">
                                Warga dapat membeli tabung gas seperti gas LPG 3 kg secara digital melalui sistem iSewa.
                                Proses pencatatan transaksi, validasi pembayaran, dan laporan penjualan dilakukan otomatis
                                oleh sistem untuk menjamin transparansi dan keakuratan data
                            </p>
                        </div>
                    </div>
                </div>
            </div>
    </section>
@endsection

@push('styles')
    <style>
        * {
            font-family: 'Inter', sans-serif;
        }

        /* Smooth scroll behavior */
        html {
            scroll-behavior: smooth;
        }

        /* Additional responsive adjustments */
        @media (max-width: 768px) {
            .text-5xl {
                font-size: 2.5rem;
            }

            .text-2xl {
                font-size: 1.5rem;
            }
        }
        
        /* Animation keyframes */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        /* Initial hidden state */
        .animate-section {
            opacity: 0;
            transform: translateY(30px);
        }
        
        /* Animated state */
        .animate-section.show {
            animation: fadeInUp 0.8s ease-out forwards;
        }
        
        /* Staggered delays for each section */
        .animate-section:nth-child(1) { animation-delay: 0.1s; }
        .animate-section:nth-child(2) { animation-delay: 0.2s; }
        .animate-section:nth-child(3) { animation-delay: 0.3s; }
        .animate-section:nth-child(4) { animation-delay: 0.4s; }
    </style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Get all sections to animate
        const sections = document.querySelectorAll('.animate-section');
        
        // Add show class to trigger animations
        sections.forEach((section, index) => {
            setTimeout(() => {
                section.classList.add('show');
            }, index * 100); // Stagger by 100ms
        });
    });
</script>
@endpush