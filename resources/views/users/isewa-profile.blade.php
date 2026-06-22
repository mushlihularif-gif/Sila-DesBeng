@extends('layouts.user')

@section('page')
<main class="flex-grow relative w-full">
    <section class="relative z-10 min-h-screen pt-40 pb-16">
        <!-- Animated Background Wrapper -->
        <div class="absolute inset-0 z-0 overflow-hidden pointer-events-none">
            <div id="animated-bg" class="absolute inset-0 bg-cover bg-top bg-no-repeat transition-all duration-1000 ease-out" 
                 style="background-image: url('{{ asset('Admin/img/elements/background.png') }}');">
            </div>
            <!-- White Overlay -->
            <div class="absolute inset-0 bg-white/10"></div>
        </div>

        <div class="max-w-6xl mx-auto px-6 relative z-20">
            
            <!-- Cerita Kami Section -->
            <div class="mb-20 relative animate-section">
                <div class="flex items-start justify-between gap-8">
                    <div class="flex-1">
                        <h2 class="text-3xl md:text-4xl font-bold mb-3 bg-gradient-to-r from-[#1a1a1a] via-[#0099ff] to-[#33b5ff] bg-clip-text text-transparent">
                            Cerita Kami
                        </h2>
                        <p class="text-lg font-bold text-gray-800 mb-4">
                            Langkah Awal Mewujudkan Digitalisasi Bengkalis
                        </p>
                        <p class="text-gray-700 leading-relaxed mb-4">
                            Perjalanan <span class="font-semibold text-gray-800">SiladesBeng</span> (Sistem Sinergi Layanan dan Aspirasi Desa di Kabupaten Bengkalis) bermula dari sebuah visi besar untuk mendorong percepatan digitalisasi pelayanan publik di wilayah Kabupaten Bengkalis. Gagasan ini lahir sebagai solusi inovatif untuk memutus kendala jarak dan mengoptimalkan potensi desa melalui pemanfaatan teknologi. Mimpi utama kami adalah menghubungkan seluruh jaringan kecamatan hingga ke pelosok desa ke dalam satu ekosistem digital yang canggih, terpadu, dan mudah diakses oleh seluruh lapisan masyarakat.
                        </p>
                        <p class="text-gray-700 leading-relaxed">
                            Sebagai wujud nyata dari visi tersebut, SilaDesBeng hadir mengintegrasikan berbagai pilar layanan publik esensial, mulai dari sarana mobilitas (kendaraan), pemanfaatan fasilitas umum, hingga penyewaan alat dan pendistribusian gas. Lebih jauh lagi, kami juga menghadirkan ruang interaksi inklusif melalui fitur Pelaporan Warga dan Informasi Pengumuman. Cerita SilaDesBeng adalah cerita tentang inovasi dan kolaborasi bagaimana sentuhan teknologi mentransformasi cara masyarakat dan aparatur berinteraksi demi mewujudkan tata kelola Bengkalis yang mandiri, produktif, dan berkelanjutan.
                        </p>
                    </div>
                    
                    <!-- Logo with Shadow Overlay -->
                    <div class="relative flex-shrink-0 hidden md:block">
                        <div class="relative">
                            <img src="{{ asset('User/img/avatars/logodomain.png') }}" 
                                 alt="SiladesBeng Logo" 
                                 class="w-64 h-64 object-contain">
                            <div class="absolute inset-0 bg-gradient-to-b from-transparent via-transparent to-white/60"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Nilai Kami Section -->
            <div class="mb-20 animate-section">
                <h2 class="text-3xl md:text-4xl font-bold mb-6 text-center bg-gradient-to-r from-[#1a1a1a] via-[#0099ff] to-[#33b5ff] bg-clip-text text-transparent">
                    Nilai Kami
                </h2>
                <div class="bg-white/80 backdrop-blur-sm rounded-3xl shadow-lg p-8 border border-gray-200 hover:-translate-y-2 transition-all duration-300">
                    <div class="space-y-4">
                        <div class="flex items-start gap-4">
                            <div class="w-3 h-3 rounded-full bg-blue-500 mt-2 flex-shrink-0"></div>
                            <div>
                                <h3 class="font-bold text-lg text-gray-900">Sinergi</h3>
                                <p class="text-gray-600">Membangun kolaborasi yang erat antara masyarakat luas, dan pemerintahan se-Kabupaten Bengkalis dalam satu ekosistem terpadu</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-4">
                            <div class="w-3 h-3 rounded-full bg-blue-500 mt-2 flex-shrink-0"></div>
                            <div>
                                <h3 class="font-bold text-lg text-gray-900">Inovatif</h3>
                                <p class="text-gray-600">Terus beradaptasi menghadirkan fitur-fitur cerdas yang relevan dengan kebutuhan modernisasi layanan publik daerah</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-4">
                            <div class="w-3 h-3 rounded-full bg-blue-500 mt-2 flex-shrink-0"></div>
                            <div>
                                <h3 class="font-bold text-lg text-gray-900">Transparan</h3>
                                <p class="text-gray-600">Mewujudkan tata kelola layanan serta penanganan pelaporan warga yang akuntabel dan dapat dipantau secara <i>real-time</i></p>
                            </div>
                        </div>
                        <div class="flex items-start gap-4">
                            <div class="w-3 h-3 rounded-full bg-blue-500 mt-2 flex-shrink-0"></div>
                            <div>
                                <h3 class="font-bold text-lg text-gray-900">Inklusif</h3>
                                <p class="text-gray-600">Menyediakan antarmuka digital yang ramah pengguna serta merangkul partisipasi aktif seluruh lapisan masyarakat tanpa batas</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-4">
                            <div class="w-3 h-3 rounded-full bg-blue-500 mt-2 flex-shrink-0"></div>
                            <div>
                                <h3 class="font-bold text-lg text-gray-900">Aksesibilitas</h3>
                                <p class="text-gray-600">Menghadirkan kemudahan jangkauan beragam fasilitas operasional dan informasi publik secara instan kapan saja dan di mana saja</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Fungsi Utama Section -->
            <div class="mb-20 animate-section">
                <h2 class="text-3xl md:text-4xl font-bold mb-6 text-center bg-gradient-to-r from-[#1a1a1a] via-[#0099ff] to-[#33b5ff] bg-clip-text text-transparent">
                    Fungsi Utama
                </h2>
                <div class="bg-white/80 backdrop-blur-sm rounded-3xl shadow-lg p-8 border border-gray-200 hover:-translate-y-2 transition-all duration-300">
                    <div class="space-y-4">
                        <div class="flex items-start gap-4">
                            <svg class="w-6 h-6 flex-shrink-0 mt-0.5" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <circle cx="12" cy="12" r="10" fill="#10B981"/>
                                <path d="M8 12.5L10.5 15L16 9" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            <div>
                                <h3 class="font-bold text-lg text-gray-900">Digitalisasi Layanan Publik</h3>
                                <p class="text-gray-600">Menyediakan platform pemesanan online untuk berbagai fasilitas mulai dari penyewaan alat, mobilitas kendaraan, hingga penggunaan fasilitas umum</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-4">
                            <svg class="w-6 h-6 flex-shrink-0 mt-0.5" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <circle cx="12" cy="12" r="10" fill="#10B981"/>
                                <path d="M8 12.5L10.5 15L16 9" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            <div>
                                <h3 class="font-bold text-lg text-gray-900">Manajemen Operasional Terpadu</h3>
                                <p class="text-gray-600">Mengoptimalkan manajemen operasional dan distribusi layanan masyarakat dengan sistem yang terintegrasi penuh</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-4">
                            <svg class="w-6 h-6 flex-shrink-0 mt-0.5" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <circle cx="12" cy="12" r="10" fill="#10B981"/>
                                <path d="M8 12.5L10.5 15L16 9" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            <div>
                                <h3 class="font-bold text-lg text-gray-900">Pelaporan dan Keterbukaan Informasi</h3>
                                <p class="text-gray-600">Menyediakan instrumen laporan kinerja, transaksi, serta pengaduan masyarakat yang dapat diakses secara transparan</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Misi Section -->
            <div class="mb-20 animate-section">
                <h2 class="text-3xl md:text-4xl font-bold mb-6 text-center bg-gradient-to-r from-[#1a1a1a] via-[#0099ff] to-[#33b5ff] bg-clip-text text-transparent">
                    Misi
                </h2>
                <div class="bg-white/80 backdrop-blur-sm rounded-3xl shadow-lg p-8 border border-gray-200 hover:-translate-y-2 transition-all duration-300">
                    <div class="space-y-4">
                        <div class="flex items-start gap-4">
                            <svg class="w-6 h-6 flex-shrink-0 mt-0.5" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <circle cx="12" cy="12" r="10" fill="#10B981"/>
                                <path d="M8 12.5L10.5 15L16 9" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            <p class="text-gray-700 leading-relaxed">Meningkatkan efisiensi dan profesionalitas tata kelola layanan publik se-Kabupaten Bengkalis</p>
                        </div>
                        <div class="flex items-start gap-4">
                            <svg class="w-6 h-6 flex-shrink-0 mt-0.5" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <circle cx="12" cy="12" r="10" fill="#10B981"/>
                                <path d="M8 12.5L10.5 15L16 9" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            <p class="text-gray-700 leading-relaxed">Menyediakan platform digital yang inklusif dan mudah diakses oleh seluruh lapisan masyarakat</p>
                        </div>
                        <div class="flex items-start gap-4">
                            <svg class="w-6 h-6 flex-shrink-0 mt-0.5" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <circle cx="12" cy="12" r="10" fill="#10B981"/>
                                <path d="M8 12.5L10.5 15L16 9" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            <p class="text-gray-700 leading-relaxed">Membangun kepercayaan publik melalui keterbukaan informasi dan transparansi data digital</p>
                        </div>
                        <div class="flex items-start gap-4">
                            <svg class="w-6 h-6 flex-shrink-0 mt-0.5" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <circle cx="12" cy="12" r="10" fill="#10B981"/>
                                <path d="M8 12.5L10.5 15L16 9" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            <p class="text-gray-700 leading-relaxed">Mendorong digitalisasi birokrasi daerah menuju ekosistem masyarakat yang mandiri & modern</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Struktur Pengembang SiladesBeng Section -->
            <div class="mb-16 animate-section">
                <h2 class="text-3xl md:text-4xl font-bold mb-12 text-center bg-gradient-to-r from-[#1a1a1a] via-[#0099ff] to-[#33b5ff] bg-clip-text text-transparent">
                    Struktur Pengembang SiladesBeng
                </h2>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8 max-w-5xl mx-auto">
                    <!-- Card 1: Rizqy Hamadi Ayabaristo Ken -->
                    <div class="group">
                        <div class="relative rounded-3xl overflow-hidden shadow-lg hover:shadow-xl hover:-translate-y-3 transition-all duration-300 bg-white/70 backdrop-blur-sm p-8">
                            <div class="flex flex-col items-center text-center">
                                <!-- Avatar with Gradient Border -->
                                <div class="relative mb-4">
                                    <div class="p-1 rounded-full" style="background: linear-gradient(135deg, #3B82F6 0%, #60a5fa 50%, #FCD34D 100%);">
                                        <div class="w-32 h-32 rounded-full overflow-hidden bg-white p-1">
                                            <img src="{{ asset('User/img/avatars/ken2.png') }}" 
                                                 alt="Rizqy Hamadi Ken" 
                                                 class="w-full h-full object-cover rounded-full">
                                        </div>
                                    </div>
                                </div>
                                <h3 class="text-xl font-bold text-gray-900">Rizqy Hamadi Ken</h3>
                                <p class="text-gray-600 text-sm font-medium mt-1">Full Stack Developer</p>
                            </div>
                        </div>
                    </div>

                    <!-- Card 2: Mushlihul Arif -->
                    <div class="group">
                        <div class="relative rounded-3xl overflow-hidden shadow-lg hover:shadow-xl hover:-translate-y-3 transition-all duration-300 bg-white/70 backdrop-blur-sm p-8">
                            <div class="flex flex-col items-center text-center">
                                <!-- Avatar with Gradient Border -->
                                <div class="relative mb-4">
                                    <div class="p-1 rounded-full" style="background: linear-gradient(135deg, #3B82F6 0%, #60a5fa 50%, #FCD34D 100%);">
                                        <div class="w-32 h-32 rounded-full overflow-hidden bg-white p-1">
                                            <img src="{{ asset('User/img/avatars/ayep123.jpg') }}" 
                                                 alt="Mushlihul Arif" 
                                                 class="w-full h-full object-cover rounded-full">
                                        </div>
                                    </div>
                                </div>
                                <h3 class="text-xl font-bold text-gray-900">Mushlihul Arif</h3>
                                <p class="text-gray-600 text-sm font-medium mt-1">UI/UX Designer <br> Frontend Developer</p>
                            </div>
                        </div>
                    </div>

                    <!-- Card 3: Dicki Wahyudi -->
                    <div class="group">
                        <div class="relative rounded-3xl overflow-hidden shadow-lg hover:shadow-xl hover:-translate-y-3 transition-all duration-300 bg-white/70 backdrop-blur-sm p-8">
                            <div class="flex flex-col items-center text-center">
                                <!-- Avatar with Gradient Border -->
                                <div class="relative mb-4">
                                    <div class="p-1 rounded-full" style="background: linear-gradient(135deg, #3B82F6 0%, #60a5fa 50%, #FCD34D 100%);">
                                        <div class="w-32 h-32 rounded-full overflow-hidden bg-white p-1">
                                            <img src="{{ asset('User/img/avatars/diki.png') }}" 
                                                 alt="Dicki Wahyudi" 
                                                 class="w-full h-full object-cover rounded-full">
                                        </div>
                                    </div>
                                </div>
                                <h3 class="text-xl font-bold text-gray-900">Dicki Wahyudi</h3>
                                <p class="text-gray-600 text-sm font-medium mt-1">Project Manager</p>
                            </div>
                        </div>
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
    .animate-section:nth-child(5) { animation-delay: 0.5s; }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Animate background
        const bg = document.getElementById('animated-bg');
        if (bg) {
            setTimeout(() => {
                bg.classList.add('scale-105');
            }, 50);
        }

        // Get all sections to animate
        (() => {
            const sections = document.querySelectorAll('.animate-section');
            
            // Add show class to trigger animations
            sections.forEach((section, index) => {
                setTimeout(() => {
                    section.classList.add('show');
                }, index * 100 + 300); // Stagger by 100ms, wait for bg animation
            });
        })();
    });
</script>
@endpush
