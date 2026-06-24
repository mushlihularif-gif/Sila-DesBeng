@extends('layouts.user')

@section('page')
<main class="flex-grow relative w-full">
    <section class="relative z-10 min-h-screen pt-40 pb-16">
        <!-- Static Background Wrapper -->
        <div class="absolute inset-0 z-0 overflow-hidden pointer-events-none">
            <div class="absolute inset-0 bg-cover bg-top bg-no-repeat" 
                 style="background-image: url('{{ asset('Admin/img/elements/background1.png') }}');">
            </div>
            <!-- White Overlay -->
            <div class="absolute inset-0 bg-white/25"></div>
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
                            Perjalanan <span class="font-semibold text-gray-800">SiladesBeng</span> (Sistem Sinergi Layanan dan Aspirasi Daerah di Kabupaten Bengkalis) bermula dari sebuah visi besar untuk mendorong percepatan digitalisasi pelayanan publik di wilayah Kabupaten Bengkalis. Gagasan ini lahir sebagai solusi inovatif untuk memutus kendala jarak dan mengoptimalkan potensi daerah melalui pemanfaatan teknologi. Mimpi utama kami adalah menghubungkan seluruh jaringan kecamatan hingga pelosok daerah ke dalam satu ekosistem digital yang canggih, terpadu, dan mudah diakses oleh seluruh lapisan masyarakat.
                        </p>
                        <p class="text-gray-700 leading-relaxed">
                            Sebagai wujud nyata dari visi tersebut, SilaDesBeng hadir mengintegrasikan berbagai pilar layanan esensial daerah, mulai dari sarana mobilitas (kendaraan), pemanfaatan fasilitas umum, hingga penyewaan alat dan pendistribusian gas. Lebih jauh lagi, kami juga menghadirkan ruang interaksi inklusif melalui fitur Pelaporan Warga dan Informasi Pengumuman. Cerita SilaDesBeng adalah cerita tentang inovasi dan kolaborasi bagaimana sentuhan teknologi mentransformasi cara dan masyarakat aparatur berinteraksi demi mewujudkan tata kelola Bengkalis yang mandiri, produktif, dan berkelanjutan.
                        </p>
                    </div>
                    
                    <!-- Logo with Shadow Overlay -->
                    <div class="relative flex-shrink-0 hidden md:block">
                        <div class="relative">
                            <img src="{{ asset('User/img/avatars/logodomain.png') }}" 
                                 alt="SiladesBeng Logo" 
                                 class="w-72 h-72 lg:w-80 lg:h-80 object-contain drop-shadow-xl scale-110">
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
                                <p class="text-gray-600">Selalu berinovasi untuk memberikan solusi terbaik yang sesuai dengan kebutuhan daerah</p>
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
                        <!-- Service 1: Sarana Mobilitas -->
                        <div class="flex items-start gap-4">
                            <svg class="w-6 h-6 flex-shrink-0 mt-0.5" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <circle cx="12" cy="12" r="10" fill="#10B981"/>
                                <path d="M8 12.5L10.5 15L16 9" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            <div>
                                <h3 class="font-bold text-lg text-gray-900">Sarana Mobilitas</h3>
                                <p class="text-gray-600">Fasilitas penyediaan sarana transportasi terpadu guna mendukung efisiensi pergerakan operasional instansi dan kemudahan mobilitas masyarakat di berbagai wilayah kabupaten.</p>
                            </div>
                        </div>
                        <!-- Service 2: Fasilitas Umum -->
                        <div class="flex items-start gap-4">
                            <svg class="w-6 h-6 flex-shrink-0 mt-0.5" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <circle cx="12" cy="12" r="10" fill="#10B981"/>
                                <path d="M8 12.5L10.5 15L16 9" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            <div>
                                <h3 class="font-bold text-lg text-gray-900">Pemanfaatan Fasilitas Umum</h3>
                                <p class="text-gray-600">Sistem reservasi digital terpadu untuk penggunaan fasilitas umum seperti gedung pertemuan, lapangan, dan ruang publik lainnya.</p>
                            </div>
                        </div>
                        <!-- Service 3: Penyewaan Alat -->
                        <div class="flex items-start gap-4">
                            <svg class="w-6 h-6 flex-shrink-0 mt-0.5" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <circle cx="12" cy="12" r="10" fill="#10B981"/>
                                <path d="M8 12.5L10.5 15L16 9" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            <div>
                                <h3 class="font-bold text-lg text-gray-900">Penyewaan Alat</h3>
                                <p class="text-gray-600">Layanan peminjaman dan penyewaan peralatan pendukung acara dengan sistem inventarisasi dan pencatatan yang akurat.</p>
                            </div>
                        </div>
                        <!-- Service 4: Pendistribusian Gas -->
                        <div class="flex items-start gap-4">
                            <svg class="w-6 h-6 flex-shrink-0 mt-0.5" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <circle cx="12" cy="12" r="10" fill="#10B981"/>
                                <path d="M8 12.5L10.5 15L16 9" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            <div>
                                <h3 class="font-bold text-lg text-gray-900">Pendistribusian Gas</h3>
                                <p class="text-gray-600">Manajemen terintegrasi untuk memantau proses ketersediaan dan pendistribusian gas secara merata dan transparan.</p>
                            </div>
                        </div>
                        <!-- Service 5: Pelaporan Warga -->
                        <div class="flex items-start gap-4">
                            <svg class="w-6 h-6 flex-shrink-0 mt-0.5" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <circle cx="12" cy="12" r="10" fill="#10B981"/>
                                <path d="M8 12.5L10.5 15L16 9" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            <div>
                                <h3 class="font-bold text-lg text-gray-900">Pelaporan Warga</h3>
                                <p class="text-gray-600">Wadah interaktif bagi masyarakat untuk menyampaikan aspirasi, aduan, dan masukan langsung kepada instansi daerah dengan respons cepat.</p>
                            </div>
                        </div>
                        <!-- Service 6: Informasi Pengumuman -->
                        <div class="flex items-start gap-4">
                            <svg class="w-6 h-6 flex-shrink-0 mt-0.5" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <circle cx="12" cy="12" r="10" fill="#10B981"/>
                                <path d="M8 12.5L10.5 15L16 9" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            <div>
                                <h3 class="font-bold text-lg text-gray-900">Informasi Pengumuman</h3>
                                <p class="text-gray-600">Pusat penyebaran informasi resmi, berita terkini, dan pengumuman penting dari pemerintah daerah untuk seluruh lapisan masyarakat.</p>
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
                            <p class="text-gray-700 leading-relaxed">Meningkatkan efisiensi dan profesionalitas pengelolaan unit usaha daerah</p>
                        </div>
                        <div class="flex items-start gap-4">
                            <svg class="w-6 h-6 flex-shrink-0 mt-0.5" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <circle cx="12" cy="12" r="10" fill="#10B981"/>
                                <path d="M8 12.5L10.5 15L16 9" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            <p class="text-gray-700 leading-relaxed">Menyediakan layanan digital yang mudah diakses oleh masyarakat dan pelaku usaha daerah</p>
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
                            <p class="text-gray-700 leading-relaxed">Mendorong digitalisasi daerah menuju tata kelola ekonomi mandiri & modern</p>
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
                    <!-- Card 1: Rizqy Hamadi Ken -->
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
                bg.classList.remove('opacity-0', 'scale-110');
                bg.classList.add('opacity-100', 'scale-100');
            }, 50);
        }

        // Get all sections to animate
        const sections = document.querySelectorAll('.animate-section');
        
        // Add show class to trigger animations
        sections.forEach((section, index) => {
            setTimeout(() => {
                section.classList.add('show');
            }, index * 100 + 300); // Stagger by 100ms, wait for bg animation
        });
    });
</script>
@endpush
