@extends('layouts.user')

@section('page')
<main class="flex-grow relative w-full">
    <section class="relative z-10 min-h-screen pt-40 pb-16">
        <!-- Decorative Background Elements -->
        <div class="absolute inset-0 pointer-events-none overflow-hidden">
            <!-- Top Left Blue Gradient Oval -->
            <svg class="absolute top-0 left-0 w-[600px] h-[600px] opacity-40" style="transform: translate(-30%, -20%);">
                <defs>
                    <linearGradient id="blueGradient1" x1="0%" y1="0%" x2="100%" y2="100%">
                        <stop offset="0%" style="stop-color:#7dd3fc;stop-opacity:0.6" />
                        <stop offset="100%" style="stop-color:#bae6fd;stop-opacity:0.3" />
                    </linearGradient>
                </defs>
                <ellipse cx="300" cy="300" rx="280" ry="320" fill="url(#blueGradient1)" />
            </svg>

            <!-- Top Right Geometric Shapes -->
            <div class="absolute top-20 right-0" style="transform: translateX(20%);">
                <svg width="400" height="400" viewBox="0 0 400 400" class="opacity-30">
                    <rect x="50" y="50" width="100" height="100" fill="#3b82f6" transform="rotate(45 100 100)" opacity="0.3"/>
                    <rect x="200" y="100" width="80" height="80" fill="#60a5fa" transform="rotate(30 240 140)" opacity="0.4"/>
                    <rect x="280" y="200" width="60" height="60" fill="#93c5fd" transform="rotate(60 310 230)" opacity="0.3"/>
                </svg>
            </div>

            <!-- Bottom Yellow Gradient Oval -->
            <svg class="absolute bottom-0 left-0 w-[500px] h-[500px] opacity-50" style="transform: translate(-20%, 30%);">
                <defs>
                    <linearGradient id="yellowGradient" x1="0%" y1="0%" x2="100%" y2="100%">
                        <stop offset="0%" style="stop-color:#fbbf24;stop-opacity:0.5" />
                        <stop offset="100%" style="stop-color:#fde68a;stop-opacity:0.2" />
                    </linearGradient>
                </defs>
                <ellipse cx="250" cy="250" rx="240" ry="280" fill="url(#yellowGradient)" />
            </svg>

            <!-- Bottom Right Blue Wave -->
            <svg class="absolute bottom-0 right-0 w-[450px] h-[450px] opacity-35" style="transform: translate(25%, 25%) rotate(15deg);">
                <defs>
                    <linearGradient id="blueGradient2" x1="0%" y1="0%" x2="100%" y2="100%">
                        <stop offset="0%" style="stop-color:#60a5fa;stop-opacity:0.4" />
                        <stop offset="100%" style="stop-color:#93c5fd;stop-opacity:0.2" />
                    </linearGradient>
                </defs>
                <ellipse cx="225" cy="225" rx="200" ry="240" fill="url(#blueGradient2)" />
            </svg>
        </div>

        <div class="max-w-7xl mx-auto px-6 relative z-10">
            <!-- Header Section -->
            <div class="text-center mb-16 animate-section">
                <h1 class="text-4xl md:text-5xl font-bold mb-4">
                    <span class="bg-gradient-to-r from-gray-800 to-gray-600 bg-clip-text text-transparent">Pilih </span>
                    <span class="bg-gradient-to-r from-[#115789] to-[#60a5fa] bg-clip-text text-transparent">BUMDes</span>
                </h1>
                <p class="text-gray-700 text-lg mt-2">
                    Ayo Pilih BUMDes Desamu, Dukung dan Gunakan Unit Layanannya!
                </p>
            </div>

            <!-- Village Selection Card -->
            <div class="max-w-3xl mx-auto mb-16 animate-section">
                <div class="backdrop-blur-sm bg-white/70 rounded-2xl p-6 border border-white/80 shadow-lg hover:shadow-xl transition-all duration-300">
                    <div class="flex items-center justify-between">
                        <h3 class="text-xl font-bold text-gray-800">Desa Pematang Duku Timur</h3>
                        <button onclick="selectBumdes()" class="px-8 py-2.5 bg-white text-[#0099ff] font-semibold rounded-full border-2 border-gray-300 hover:bg-gray-50 hover:shadow-lg transition-all duration-300">
                            Pilih
                        </button>
                    </div>
                </div>
            </div>

            <!-- BUMDes Section -->
            <div class="mb-16 animate-section">
                <div class="text-left mb-8">
                    <h2 class="text-3xl md:text-4xl font-bold">
                        <span class="bg-gradient-to-r from-[#115789] to-[#60a5fa] bg-clip-text text-transparent">BUMDes</span>
                    </h2>
                </div>

                <!-- BUMDes Description Card -->
                <div class="backdrop-blur-sm bg-white/70 rounded-3xl p-8 md:p-12 border border-white/80 shadow-xl">
                    <div class="text-gray-700 text-base leading-relaxed text-justify space-y-4">
                        <p>
                            BUMDes (Badan Usaha Milik Desa) merupakan lembaga ekonomi desa yang dibentuk oleh pemerintah desa untuk mengelola potensi dan aset yang dimiliki desa guna meningkatkan kesejahteraan masyarakat. Melalui BUMDes, berbagai kegiatan usaha dapat dijalankan secara mandiri oleh desa, seperti penyewaan alat, perdagangan hasil pertanian, simpan pinjam, hingga penyediaan layanan publik berbasis desa. Kehadiran BUMDes menjadi sarana penting dalam memperkuat ekonomi desa, mengurangi ketergantungan terhadap pihak luar, serta membuka peluang usaha dan lapangan kerja bagi masyarakat desa. Dengan sistem yang terkelola secara transparan, BUMDes menjadi motor penggerak ekonomi yang mendorong kemandirian desa menuju pembangunan yang berkelanjutan.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>
@endsection

@push('styles')
<style>
    * {
        font-family: 'Inter', sans-serif;
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
<script>
    function selectBumdes() {
        // Redirect to BUMDes detail page
        window.location.href = "{{ route('bumdes.detail') }}";
    }
</script>
@endpush