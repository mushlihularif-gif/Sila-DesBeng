@extends('layouts.user')

@section('page')
<main class="flex-grow relative w-full">
    <section class="relative z-10 min-h-screen pt-40 pb-16 bg-cover bg-center bg-no-repeat" 
             style="background-image: url('{{ asset('Admin/img/elements/background1.png') }}');">
        
        <!-- White Overlay -->
        <div class="absolute inset-0 bg-white/25 pointer-events-none"></div>

        <div class="max-w-6xl mx-auto px-6 relative z-20">
            
            <!-- Cerita Kami Section -->
            <div class="mb-20 relative animate-section">
                <div class="flex items-start justify-between gap-8">
                    <div class="flex-1">
                        <h2 class="text-3xl md:text-4xl font-bold mb-3 bg-gradient-to-r from-[#1a1a1a] via-[#0099ff] to-[#33b5ff] bg-clip-text text-transparent">
                            Cerita Kami
                        </h2>
                        <p class="text-lg font-bold text-gray-800 mb-4">
                            Memberikan Solusi Digital untuk Kemajuan Desa
                        </p>
                        <p class="text-gray-700 leading-relaxed mb-4">
                            iSewa merupakan platform digital terpadu yang dirancang untuk mendukung kegiatan operasional dan pelayanan BUMDes secara modern dan efisien. Sistem ini hadir sebagai solusi inovatif untuk mengelola berbagai unit usaha desa, seperti penyewaan alat dan layanan pembelian gas.
                        </p>
                        <p class="text-gray-700 leading-relaxed">
                            Melalui iSewa, proses administrasi dan transaksi menjadi lebih cepat, transparan, dan mudah dijangkau oleh masyarakat desa. Dengan berbasis sistem terintegrasi, seluruh data penyewaan, transaksi, dan laporan dapat dikelola secara otomatis dan terdokumentasi dengan baik. Dengan mengedepankan kemudahan akses dan efisiensi layanan, iSewa membantu BUMDes dalam meningkatkan produktivitas, serta memperkuat perekonomian desa secara berkelanjutan.
                        </p>
                    </div>
                    
                    <!-- Logo with Shadow Overlay -->
                    <div class="relative flex-shrink-0 hidden md:block">
                        <div class="relative">
                            <img src="{{ asset('Admin/img/avatars/sewais.png') }}" 
                                 alt="iSewa Logo" 
                                 class="w-48 h-48 object-contain">
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
                                <h3 class="font-bold text-lg text-gray-900">Inovatif</h3>
                                <p class="text-gray-600">Selalu berinovasi untuk memberikan solusi terbaik yang sesuai dengan kebutuhan desa</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-4">
                            <div class="w-3 h-3 rounded-full bg-blue-500 mt-2 flex-shrink-0"></div>
                            <div>
                                <h3 class="font-bold text-lg text-gray-900">Efisien</h3>
                                <p class="text-gray-600">Mengoptimalkan proses manual menjadi digital untuk penghematan waktu dan sumber daya</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-4">
                            <div class="w-3 h-3 rounded-full bg-blue-500 mt-2 flex-shrink-0"></div>
                            <div>
                                <h3 class="font-bold text-lg text-gray-900">Terpercaya</h3>
                                <p class="text-gray-600">Menjaga integritas data dengan sistem keamanan yang handal dan terpercaya</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-4">
                            <div class="w-3 h-3 rounded-full bg-blue-500 mt-2 flex-shrink-0"></div>
                            <div>
                                <h3 class="font-bold text-lg text-gray-900">Kemudahan</h3>
                                <p class="text-gray-600">Menyediakan antarmuka yang intuitif dan mudah digunakan untuk semua kalangan</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-4">
                            <div class="w-3 h-3 rounded-full bg-blue-500 mt-2 flex-shrink-0"></div>
                            <div>
                                <h3 class="font-bold text-lg text-gray-900">Aksesibilitas</h3>
                                <p class="text-gray-600">Dapat diakses kapan saja dan dimana saja melalui perangkat apapun</p>
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
                                <h3 class="font-bold text-lg text-gray-900">Digitalisasi Penyewaan Alat Desa</h3>
                                <p class="text-gray-600">Menyediakan layanan pemesanan alat (tenda, kursi, meja, sound system, diesel) secara online dengan pencatatan otomatis</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-4">
                            <svg class="w-6 h-6 flex-shrink-0 mt-0.5" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <circle cx="12" cy="12" r="10" fill="#10B981"/>
                                <path d="M8 12.5L10.5 15L16 9" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            <div>
                                <h3 class="font-bold text-lg text-gray-900">Pengelolaan Unit Usaha</h3>
                                <p class="text-gray-600">Mengelola proses penyewaan alat dan pembelian gas dengan sistem yang terintegrasi</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-4">
                            <svg class="w-6 h-6 flex-shrink-0 mt-0.5" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <circle cx="12" cy="12" r="10" fill="#10B981"/>
                                <path d="M8 12.5L10.5 15L16 9" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            <div>
                                <h3 class="font-bold text-lg text-gray-900">Pelaporan dan Monitoring</h3>
                                <p class="text-gray-600">Menyediakan laporan penyewaan, transaksi, dan keuangan yang dapat diakses oleh BUMDes dan Pemerintah Desa</p>
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
                            <p class="text-gray-700 leading-relaxed">Meningkatkan efisiensi dan profesionalitas pengelolaan unit usaha desa</p>
                        </div>
                        <div class="flex items-start gap-4">
                            <svg class="w-6 h-6 flex-shrink-0 mt-0.5" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <circle cx="12" cy="12" r="10" fill="#10B981"/>
                                <path d="M8 12.5L10.5 15L16 9" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            <p class="text-gray-700 leading-relaxed">Menyediakan layanan digital yang mudah diakses oleh masyarakat dan pelaku usaha desa</p>
                        </div>
                        <div class="flex items-start gap-4">
                            <svg class="w-6 h-6 flex-shrink-0 mt-0.5" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <circle cx="12" cy="12" r="10" fill="#10B981"/>
                                <path d="M8 12.5L10.5 15L16 9" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            <p class="text-gray-700 leading-relaxed">Membangun kepercayaan masyarakat melalui transparansi data digital</p>
                        </div>
                        <div class="flex items-start gap-4">
                            <svg class="w-6 h-6 flex-shrink-0 mt-0.5" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <circle cx="12" cy="12" r="10" fill="#10B981"/>
                                <path d="M8 12.5L10.5 15L16 9" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            <p class="text-gray-700 leading-relaxed">Mendorong digitalisasi desa menuju tata kelola ekonomi mandiri & modern</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Struktur Pengembang iSewa Section -->
            <div class="mb-16 animate-section">
                <h2 class="text-3xl md:text-4xl font-bold mb-12 text-center bg-gradient-to-r from-[#1a1a1a] via-[#0099ff] to-[#33b5ff] bg-clip-text text-transparent">
                    Struktur Pengembang iSewa
                </h2>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8 max-w-5xl mx-auto">
                    <!-- Card 1: Wahid Riono -->
                    <div class="group">
                        <div class="relative rounded-3xl overflow-hidden shadow-lg hover:shadow-xl hover:-translate-y-3 transition-all duration-300 bg-white/70 backdrop-blur-sm p-8">
                            <div class="flex flex-col items-center text-center">
                                <!-- Avatar with Gradient Border -->
                                <div class="relative mb-4">
                                    <div class="p-1 rounded-full" style="background: linear-gradient(135deg, #3B82F6 0%, #60a5fa 50%, #FCD34D 100%);">
                                        <div class="w-32 h-32 rounded-full overflow-hidden bg-white p-1">
                                            <img src="{{ asset('User/img/avatars/wahid1.jpg') }}" 
                                                 alt="M.Wahid Riono" 
                                                 class="w-full h-full object-cover rounded-full">
                                        </div>
                                    </div>
                                </div>
                                <h3 class="text-xl font-bold text-gray-900">M.Wahid Riono</h3>
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

                    <!-- Card 3: Safika -->
                    <div class="group">
                        <div class="relative rounded-3xl overflow-hidden shadow-lg hover:shadow-xl hover:-translate-y-3 transition-all duration-300 bg-white/70 backdrop-blur-sm p-8">
                            <div class="flex flex-col items-center text-center">
                                <!-- Avatar with Gradient Border -->
                                <div class="relative mb-4">
                                    <div class="p-1 rounded-full" style="background: linear-gradient(135deg, #3B82F6 0%, #60a5fa 50%, #FCD34D 100%);">
                                        <div class="w-32 h-32 rounded-full overflow-hidden bg-white p-1">
                                            <img src="{{ asset('User/img/avatars/safika1.png') }}" 
                                                 alt="Safika" 
                                                 class="w-full h-full object-cover rounded-full">
                                        </div>
                                    </div>
                                </div>
                                <h3 class="text-xl font-bold text-gray-900">Safika</h3>
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
