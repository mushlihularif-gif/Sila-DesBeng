@extends('admin.layouts.admin')

@section('title', 'Profil SiladesBeng')

@section('styles')
<style>
    .dev-card {
        transition: all 0.3s ease;
        text-align: center;
        height: 100%;
    }

    .dev-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0,0,0,0.1) !important;
    }

    .avatar-ring {
        background: linear-gradient(135deg, #3B82F6 0%, #60a5fa 50%, #FCD34D 100%);
        padding: 4px;
        border-radius: 50%;
        display: inline-block;
        margin-bottom: 1rem;
    }

    .avatar-inner {
        background: white;
        padding: 4px;
        border-radius: 50%;
        width: 120px;
        height: 120px;
        overflow: hidden;
        margin: 0 auto;
    }

    .avatar-inner img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        border-radius: 50%;
    }

    .icon-bullet {
        width: 12px;
        height: 12px;
        background-color: #696cff; /* Tema utama Sneat */
        border-radius: 50%;
        margin-top: 6px;
        flex-shrink: 0;
    }

    .icon-box {
        width: 40px;
        height: 40px;
        background-color: #e7e7ff; /* Latar belakang icon Sneat primary */
        color: #696cff; /* Warna icon Sneat primary */
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    /* Animations */
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .animate-section {
        opacity: 0;
        transform: translateY(20px);
    }

    .animate-section.show {
        animation: fadeInUp 0.6s ease-out forwards;
    }

    .animate-section:nth-child(1) { animation-delay: 0.1s; }
    .animate-section:nth-child(2) { animation-delay: 0.2s; }
    .animate-section:nth-child(3) { animation-delay: 0.3s; }
    .animate-section:nth-child(4) { animation-delay: 0.4s; }
    .animate-section:nth-child(5) { animation-delay: 0.5s; }
</style>
@endsection

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    
    <!-- Cerita Kami Section -->
    <div class="card mb-4 border-0 shadow-sm animate-section">
        <div class="card-body p-5">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <h3 class="text-primary fw-bold mb-2">
                        Cerita Kami
                    </h3>
                    <h5 class="fw-semibold text-dark mb-4">
                        Langkah Awal Mewujudkan Digitalisasi Bengkalis
                    </h5>
                    <p class="text-muted lh-lg mb-4 text-justify">
                        Perjalanan <span class="fw-bold text-dark">SiladesBeng</span> (Sistem Sinergi Layanan dan Aspirasi Daerah di Kabupaten Bengkalis) bermula dari sebuah visi besar untuk mendorong percepatan digitalisasi pelayanan publik di wilayah Kabupaten Bengkalis. Gagasan ini lahir sebagai solusi inovatif untuk memutus kendala jarak dan mengoptimalkan potensi daerah melalui pemanfaatan teknologi. Mimpi utama kami adalah menghubungkan seluruh jaringan kecamatan hingga pelosok daerah ke dalam satu ekosistem digital yang canggih, terpadu, dan mudah diakses oleh seluruh lapisan masyarakat.
                    </p>
                    <p class="text-muted lh-lg mb-0 text-justify">
                        Sebagai wujud nyata dari visi tersebut, SilaDesBeng hadir mengintegrasikan berbagai pilar layanan esensial daerah, mulai dari sarana mobilitas (kendaraan), pemanfaatan fasilitas umum, hingga penyewaan alat dan pendistribusian gas. Lebih jauh lagi, kami juga menghadirkan ruang interaksi inklusif melalui fitur Pelaporan Warga dan Informasi Pengumuman. Cerita SilaDesBeng adalah cerita tentang inovasi dan kolaborasi bagaimana sentuhan teknologi mentransformasi cara dan masyarakat aparatur berinteraksi demi mewujudkan tata kelola Bengkalis yang mandiri, produktif, dan berkelanjutan.
                    </p>
                </div>
                <div class="col-lg-4 text-center d-none d-lg-block">
                    <img src="{{ asset('User/img/avatars/logodomain.png') }}" alt="SiladesBeng Logo" class="img-fluid" style="max-width: 250px;">
                </div>
            </div>
        </div>
    </div>

    <!-- Nilai Kami Section -->
    <div class="mb-4 animate-section">
        <h4 class="fw-bold mb-3 ms-1 text-primary">Nilai Kami</h4>
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <div class="row g-4">
                    <div class="col-md-6 col-lg-4">
                        <div class="d-flex align-items-start gap-3">
                            <div class="icon-bullet"></div>
                            <div>
                                <h6 class="fw-bold text-dark mb-1">Inovatif</h6>
                                <p class="text-muted small mb-0">Selalu berinovasi untuk memberikan solusi terbaik yang sesuai dengan kebutuhan daerah</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <div class="d-flex align-items-start gap-3">
                            <div class="icon-bullet"></div>
                            <div>
                                <h6 class="fw-bold text-dark mb-1">Efisien</h6>
                                <p class="text-muted small mb-0">Mengoptimalkan proses manual menjadi digital untuk penghematan waktu dan sumber daya</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <div class="d-flex align-items-start gap-3">
                            <div class="icon-bullet"></div>
                            <div>
                                <h6 class="fw-bold text-dark mb-1">Terpercaya</h6>
                                <p class="text-muted small mb-0">Menjaga integritas data dengan sistem keamanan yang handal dan terpercaya</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <div class="d-flex align-items-start gap-3">
                            <div class="icon-bullet"></div>
                            <div>
                                <h6 class="fw-bold text-dark mb-1">Kemudahan</h6>
                                <p class="text-muted small mb-0">Menyediakan antarmuka yang intuitif dan mudah digunakan untuk semua kalangan</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <div class="d-flex align-items-start gap-3">
                            <div class="icon-bullet"></div>
                            <div>
                                <h6 class="fw-bold text-dark mb-1">Aksesibilitas</h6>
                                <p class="text-muted small mb-0">Dapat diakses kapan saja dan dimana saja melalui perangkat apapun</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Fungsi Utama Section -->
    <div class="mb-4 animate-section">
        <h4 class="fw-bold mb-3 ms-1 text-primary">Fungsi Utama</h4>
        <div class="row g-4">
            <!-- Service 1: Sarana Mobilitas -->
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <div class="icon-box mb-3">
                            <i class='bx bx-car fs-4'></i>
                        </div>
                        <h6 class="fw-bold text-dark mb-2">Sarana Mobilitas</h6>
                        <p class="text-muted small mb-0">Fasilitas penyediaan sarana transportasi terpadu guna mendukung efisiensi pergerakan operasional instansi dan kemudahan mobilitas masyarakat di berbagai wilayah kabupaten.</p>
                    </div>
                </div>
            </div>
            <!-- Service 2: Fasilitas Umum -->
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <div class="icon-box mb-3">
                            <i class='bx bx-building-house fs-4'></i>
                        </div>
                        <h6 class="fw-bold text-dark mb-2">Pemanfaatan Fasilitas Umum</h6>
                        <p class="text-muted small mb-0">Sistem reservasi digital terpadu untuk penggunaan fasilitas umum seperti gedung pertemuan, lapangan, dan ruang publik lainnya.</p>
                    </div>
                </div>
            </div>
            <!-- Service 3: Penyewaan Alat -->
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <div class="icon-box mb-3">
                            <i class='bx bx-wrench fs-4'></i>
                        </div>
                        <h6 class="fw-bold text-dark mb-2">Penyewaan Alat</h6>
                        <p class="text-muted small mb-0">Layanan peminjaman dan penyewaan peralatan pendukung acara dengan sistem inventarisasi dan pencatatan yang akurat.</p>
                    </div>
                </div>
            </div>
            <!-- Service 4: Pendistribusian Gas -->
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <div class="icon-box mb-3">
                            <i class='bx bx-package fs-4'></i>
                        </div>
                        <h6 class="fw-bold text-dark mb-2">Pendistribusian Gas</h6>
                        <p class="text-muted small mb-0">Manajemen terintegrasi untuk memantau proses ketersediaan dan pendistribusian gas secara merata dan transparan.</p>
                    </div>
                </div>
            </div>
            <!-- Service 5: Pelaporan Warga -->
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <div class="icon-box mb-3">
                            <i class='bx bx-message-alt-detail fs-4'></i>
                        </div>
                        <h6 class="fw-bold text-dark mb-2">Pelaporan Warga</h6>
                        <p class="text-muted small mb-0">Wadah interaktif bagi masyarakat untuk menyampaikan aspirasi, aduan, dan masukan langsung kepada instansi daerah dengan respons cepat.</p>
                    </div>
                </div>
            </div>
            <!-- Service 6: Informasi Pengumuman -->
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <div class="icon-box mb-3">
                            <i class='bx bx-news fs-4'></i>
                        </div>
                        <h6 class="fw-bold text-dark mb-2">Informasi Pengumuman</h6>
                        <p class="text-muted small mb-0">Pusat penyebaran informasi resmi, berita terkini, dan pengumuman penting dari pemerintah daerah untuk seluruh lapisan masyarakat.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Misi Section -->
    <div class="mb-4 animate-section">
        <h4 class="fw-bold mb-3 ms-1 text-primary">Misi</h4>
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <ul class="list-unstyled mb-0 d-flex flex-column gap-3">
                    <li class="d-flex align-items-start gap-3">
                        <i class='bx bx-check-circle text-success fs-5 mt-1'></i>
                        <span class="text-muted">Meningkatkan efisiensi dan profesionalitas pengelolaan unit usaha daerah</span>
                    </li>
                    <li class="d-flex align-items-start gap-3">
                        <i class='bx bx-check-circle text-success fs-5 mt-1'></i>
                        <span class="text-muted">Menyediakan layanan digital yang mudah diakses oleh masyarakat dan pelaku usaha daerah</span>
                    </li>
                    <li class="d-flex align-items-start gap-3">
                        <i class='bx bx-check-circle text-success fs-5 mt-1'></i>
                        <span class="text-muted">Membangun kepercayaan masyarakat melalui transparansi data digital</span>
                    </li>
                    <li class="d-flex align-items-start gap-3">
                        <i class='bx bx-check-circle text-success fs-5 mt-1'></i>
                        <span class="text-muted">Mendorong digitalisasi daerah menuju tata kelola ekonomi mandiri & modern</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Struktur Pengembang SiladesBeng Section -->
    <div class="mb-4 animate-section">
        <h4 class="fw-bold mb-4 mt-2 text-center text-primary">Struktur Pengembang SiladesBeng</h4>
        <div class="row g-4 justify-content-center">
            <!-- Card 1: Rizqy Hamadi Ken -->
            <div class="col-sm-6 col-md-4">
                <div class="card border-0 shadow-sm dev-card">
                    <div class="card-body p-4">
                        <div class="avatar-ring">
                            <div class="avatar-inner">
                                <img src="{{ asset('User/img/avatars/ken1.jpg') }}" alt="Rizqy Hamadi Ken">
                            </div>
                        </div>
                        <h5 class="fw-bold text-dark mb-1">Rizqy Hamadi Ken</h5>
                        <p class="text-muted small fw-semibold mb-0">Full Stack Developer</p>
                    </div>
                </div>
            </div>

            <!-- Card 2: Mushlihul Arif -->
            <div class="col-sm-6 col-md-4">
                <div class="card border-0 shadow-sm dev-card">
                    <div class="card-body p-4">
                        <div class="avatar-ring">
                            <div class="avatar-inner">
                                <img src="{{ asset('User/img/avatars/ayep123.jpg') }}" alt="Mushlihul Arif">
                            </div>
                        </div>
                        <h5 class="fw-bold text-dark mb-1">Mushlihul Arif</h5>
                        <p class="text-muted small fw-semibold mb-0">UI/UX Designer <br> Frontend Developer</p>
                    </div>
                </div>
            </div>

            <!-- Card 3: Dicki Wahyudi -->
            <div class="col-sm-6 col-md-4">
                <div class="card border-0 shadow-sm dev-card">
                    <div class="card-body p-4">
                        <div class="avatar-ring">
                            <div class="avatar-inner">
                                <img src="{{ asset('User/img/avatars/diki.png') }}" alt="Dicki Wahyudi">
                            </div>
                        </div>
                        <h5 class="fw-bold text-dark mb-1">Dicki Wahyudi</h5>
                        <p class="text-muted small fw-semibold mb-0">Project Manager</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const sections = document.querySelectorAll('.animate-section');
        sections.forEach((section, index) => {
            setTimeout(() => {
                section.classList.add('show');
            }, index * 100 + 50);
        });
    });
</script>
@endsection