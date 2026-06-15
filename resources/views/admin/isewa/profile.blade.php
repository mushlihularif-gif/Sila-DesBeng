@extends('admin.layouts.admin')

@section('title', 'Profil SiladesBeng')

@section('content')
<link rel="stylesheet" href="{{ asset('css/isewa-profile.css') }}">
<style>
    /* Custom UI Improvements */
    .glass-card {
        background: rgba(255, 255, 255, 0.9);
        backdrop-filter: blur(10px);
        border-radius: 24px;
        border: 1px solid rgba(255, 255, 255, 0.2);
        box-shadow: 0 8px 32px rgba(31, 38, 135, 0.1);
        padding: 1.5rem;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .glass-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 12px 40px rgba(31, 38, 135, 0.15);
    }

    .section-title {
        font-size: 2rem;
        font-weight: 800;
        text-align: center;
        margin-bottom: 1.5rem;
        background: linear-gradient(to right, #1a1a1a, #0099ff, #33b5ff);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .list-item-modern {
        display: flex;
        align-items: center; /* Changed to center alignment */
        gap: 0.75rem;
        margin-bottom: 1rem;
        padding: 0.75rem;
        border-radius: 12px;
        transition: background 0.2s ease;
    }

    .list-item-modern:hover {
        background: rgba(0, 153, 255, 0.05);
    }

    .icon-box {
        width: 2.5rem;
        height: 2.5rem;
        background: linear-gradient(135deg, #e0f2fe 0%, #bae6fd 100%);
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #0284c7;
        flex-shrink: 0;
        font-size: 1.25rem; /* Ensure icon font size is controlled */
    }

    .feature-card {
        background: white;
        border-radius: 20px;
        padding: 1.5rem;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        border: 1px solid #f3f4f6;
        height: 100%;
        transition: all 0.3s ease;
    }

    .feature-card:hover {
        border-color: #3b82f6;
        transform: translateY(-5px);
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
    }
</style>

<div class="isewa-">

    <!-- HERO -->
    <header class="hero">
        <div class="hero-inner container-xxl">
            <!-- LEFT -->
            <div class="hero-left">
                <h2 class="heading-small">Cerita <span class="accent">Kami</span></h2>
                <h1 class="hero-title">Memberikan Solusi Digital untuk Kemajuan Desa</h1>

                <div class="hero-paragraphs">
                    <p>
                        SiladesBeng merupakan platform digital terpadu yang dirancang untuk mendukung kegiatan operasional dan pelayanan BUMDes secara modern dan efisien. Sistem ini hadir sebagai solusi inovatif untuk mengelola berbagai unit usaha desa, seperti penyewaan alat dan layanan pembelian gas.
                    </p>
                    <p>
                        Melalui SiladesBeng, proses administrasi dan transaksi menjadi lebih cepat, transparan, dan mudah dijangkau oleh masyarakat desa. Dengan berbasis sistem terintegrasi, seluruh data penyewaan, transaksi, dan laporan dapat dikelola secara otomatis dan terdokumentasi dengan baik. Dengan mengedepankan kemudahan akses dan efisiensi layanan, SiladesBeng membantu BUMDes dalam meningkatkan produktivitas, serta memperkuat perekonomian desa secara berkelanjutan.
                    </p>
                </div>
            </div>

            <!-- RIGHT LOGO -->
            <div class="hero-right">
                <div class="logo-wrap">
                    <img src="{{ asset('Admin/img/avatars/sewais.png') }}" alt="Logo SiladesBeng">
                </div>
            </div>
        </div>
    </header>

    <!-- NILAI KAMI -->
    <section class="section nilai-kami container-xxl">
        <h2 class="section-title">Nilai <span class="accent">Kami</span></h2>
        
        <div class="glass-card">
            <div class="nilai-grid">
                <div class="list-item-modern">
                    <div class="icon-box">
                        <i class='bx bx-bulb'></i>
                    </div>
                    <div>
                        <h4 style="font-weight: 700; margin-bottom: 0.1rem; color: #111827; font-size: 1rem;">Inovatif</h4>
                        <p style="color: #4b5563; margin: 0; font-size: 0.875rem;">Selalu berinovasi untuk memberikan solusi terbaik yang sesuai dengan kebutuhan desa</p>
                    </div>
                </div>
                <div class="list-item-modern">
                    <div class="icon-box">
                        <i class='bx bx-time-five'></i>
                    </div>
                    <div>
                        <h4 style="font-weight: 700; margin-bottom: 0.1rem; color: #111827; font-size: 1rem;">Efisien</h4>
                        <p style="color: #4b5563; margin: 0; font-size: 0.875rem;">Mengoptimalkan proses manual menjadi digital untuk penghematan waktu dan sumber daya</p>
                    </div>
                </div>
                <div class="list-item-modern">
                    <div class="icon-box">
                        <i class='bx bx-shield-quarter'></i>
                    </div>
                    <div>
                        <h4 style="font-weight: 700; margin-bottom: 0.1rem; color: #111827; font-size: 1rem;">Terpercaya</h4>
                        <p style="color: #4b5563; margin: 0; font-size: 0.875rem;">Menjaga integritas data dengan sistem keamanan yang handal dan terpercaya</p>
                    </div>
                </div>
                <div class="list-item-modern">
                    <div class="icon-box">
                        <i class='bx bx-smile'></i>
                    </div>
                    <div>
                        <h4 style="font-weight: 700; margin-bottom: 0.1rem; color: #111827; font-size: 1rem;">Kemudahan</h4>
                        <p style="color: #4b5563; margin: 0; font-size: 0.875rem;">Menyediakan antarmuka yang intuitif dan mudah digunakan untuk semua kalangan</p>
                    </div>
                </div>
                <div class="list-item-modern">
                    <div class="icon-box">
                        <i class='bx bx-mobile-alt'></i>
                    </div>
                    <div>
                        <h4 style="font-weight: 700; margin-bottom: 0.1rem; color: #111827; font-size: 1rem;">Aksesibilitas</h4>
                        <p style="color: #4b5563; margin: 0; font-size: 0.875rem;">Dapat diakses kapan saja dan dimana saja melalui perangkat apapun</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- FUNGSI UTAMA -->
    <section class="section fungsi container-xxl">
        <h2 class="section-title">Fungsi <span class="accent">Utama</span></h2>

        <div class="glass-card">
            <div class="func-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem;">
                
                <div class="feature-card">
                    <div style="margin-bottom: 1rem;">
                        <div class="icon-box" style="background: #ecfdf5; color: #10b981;">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M9 12L11 14L15 10M21 12C21 16.9706 16.9706 21 12 21C7.02944 21 3 16.9706 3 12C3 7.02944 7.02944 3 12 3C16.9706 3 21 7.02944 21 12Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </div>
                    </div>
                    <h4 style="font-weight: 700; color: #1f2937; margin-bottom: 0.5rem; font-size: 1.1rem;">Digitalisasi Penyewaan Alat Desa</h4>
                    <p style="color: #6b7280; font-size: 0.95rem; line-height: 1.5;">Menyediakan layanan pemesanan alat (tenda, kursi, meja, sound system, diesel) secara online dengan pencatatan otomatis</p>
                </div>

                <div class="feature-card">
                    <div style="margin-bottom: 1rem;">
                        <div class="icon-box" style="background: #ecfdf5; color: #10b981;">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M19 11H5M19 11C20.1046 11 21 11.8954 21 13V19C21 20.1046 20.1046 21 19 21H5C3.89543 21 3 20.1046 3 19V13C3 11.8954 3.89543 11 5 11M19 11V9C19 7.89543 18.1046 7 17 7M5 11V9C5 7.89543 5.89543 7 7 7M7 7V5C7 3.89543 7.89543 3 9 3H15C16.1046 3 17 3.89543 17 5V7M7 7H17" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </div>
                    </div>
                    <h4 style="font-weight: 700; color: #1f2937; margin-bottom: 0.5rem; font-size: 1.1rem;">Pengelolaan Unit Usaha</h4>
                    <p style="color: #6b7280; font-size: 0.95rem; line-height: 1.5;">Mengelola proses penyewaan alat dan pembelian gas dengan sistem yang terintegrasi</p>
                </div>

                <div class="feature-card">
                    <div style="margin-bottom: 1rem;">
                        <div class="icon-box" style="background: #ecfdf5; color: #10b981;">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M9 17V11M13 17V7M17 17V13M21 17H3" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </div>
                    </div>
                    <h4 style="font-weight: 700; color: #1f2937; margin-bottom: 0.5rem; font-size: 1.1rem;">Pelaporan dan Monitoring</h4>
                    <p style="color: #6b7280; font-size: 0.95rem; line-height: 1.5;">Menyediakan laporan penyewaan, transaksi, dan keuangan yang dapat diakses oleh BUMDes dan Pemerintah Desa</p>
                </div>

            </div>
        </div>
    </section>

    <!-- MISI -->
    <section class="section misi container-xxl">
        <h2 class="section-title">Misi</h2>

        <div class="glass-card">
            <div class="space-y-4">
                <div class="list-item-modern" style="margin-bottom: 0.5rem; padding: 0.5rem;">
                    <div class="icon-box" style="background: #f0fdf4; color: #16a34a; width: 2rem; height: 2rem;">
                        <i class='bx bx-check-circle'></i>
                    </div>
                    <p style="margin: 0; color: #4b5563; font-size: 0.875rem; align-self: center;">Meningkatkan efisiensi dan profesionalitas pengelolaan unit usaha desa</p>
                </div>
                <div class="list-item-modern" style="margin-bottom: 0.5rem; padding: 0.5rem;">
                    <div class="icon-box" style="background: #f0fdf4; color: #16a34a; width: 2rem; height: 2rem;">
                        <i class='bx bx-check-circle'></i>
                    </div>
                    <p style="margin: 0; color: #4b5563; font-size: 0.875rem; align-self: center;">Menyediakan layanan digital yang mudah diakses oleh masyarakat dan pelaku usaha desa</p>
                </div>
                <div class="list-item-modern" style="margin-bottom: 0.5rem; padding: 0.5rem;">
                    <div class="icon-box" style="background: #f0fdf4; color: #16a34a; width: 2rem; height: 2rem;">
                        <i class='bx bx-check-circle'></i>
                    </div>
                    <p style="margin: 0; color: #4b5563; font-size: 0.875rem; align-self: center;">Membangun kepercayaan masyarakat melalui transparansi data digital</p>
                </div>
                <div class="list-item-modern" style="margin-bottom: 0.5rem; padding: 0.5rem;">
                    <div class="icon-box" style="background: #f0fdf4; color: #16a34a; width: 2rem; height: 2rem;">
                        <i class='bx bx-check-circle'></i>
                    </div>
                    <p style="margin: 0; color: #4b5563; font-size: 0.875rem; align-self: center;">Mendorong digitalisasi desa menuju tata kelola ekonomi mandiri & modern</p>
                </div>
            </div>
        </div>
    </section>

    <!-- STRUKTUR PENGEMBANG -->
    <section class="section pengembang container-xxl">
        <h2 class="section-title">Struktur <span class="accent">Pengembang SiladesBeng</span></h2>

        <div class="pengembang-row">

            <div class="dev-card">
                <div class="dev-card-bg"></div>
                <div class="avatar">
                    <img src="{{ asset('Admin/img/avatars/wahid1.jpg') }}" alt="M.Wahid Riono">
                </div>
                <h5>M.Wahid Riono</h5>
                <p style="color: #4b5563; font-size: 0.875rem; font-weight: 500; margin-top: 0.25rem;">Full Stack Developer</p>

            </div>

            <div class="dev-card">
                <div class="dev-card-bg"></div>
                <div class="avatar">
                    <img src="{{ asset('Admin/img/avatars/ayep123.jpg') }}" alt="Mushlihul Arif">
                </div>
                <h5>Mushlihul Arif</h5>
                <p style="color: #4b5563; font-size: 0.875rem; font-weight: 500; margin-top: 0.25rem;">UI/UX Designer <br> Frontend Developer</p>
                
            </div>

            <div class="dev-card">
                <div class="dev-card-bg"></div>
                <div class="avatar">
                    <img src="{{ asset('Admin/img/avatars/safika1.png') }}" alt="Safika">
                </div>
                <h5>Safika</h5>
                <p style="color: #4b5563; font-size: 0.875rem; font-weight: 500; margin-top: 0.25rem;">Project Manager</p>
                
            </div>

        </div>
    </section>

    <!-- KUNJUNGI KAMI -->
    <section class="section kunjungi-kami container-xxl" style="margin-bottom: 5rem;">
        <h2 class="section-title">Kunjungi <span class="accent">Kami</span></h2>

        <div style="display: flex; justify-content: center; gap: 3rem; flex-wrap: wrap; margin-top: 3rem;">
            
            {{-- TikTok --}}
            <a href="#" target="_blank" class="social-icon-link" style="text-decoration: none;">
                <div class="icon-box" style="width: 4.5rem; height: 4.5rem; background: #000; color: #fff; border-radius: 50%; display: flex; align-items: center; justify-content: center; transition: all 0.3s ease;">
                    <svg style="width: 28px; height: 28px;" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M19.59 6.69a4.83 4.83 0 01-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 01-5.2 1.74 2.89 2.89 0 012.31-4.64 2.93 2.93 0 01.88.13V9.4a6.84 6.84 0 00-1-.05A6.33 6.33 0 005 20.1a6.34 6.34 0 0010.86-4.43v-7a8.16 8.16 0 004.77 1.52v-3.4a4.85 4.85 0 01-1-.1z" />
                    </svg>
                </div>
            </a>

            {{-- Facebook --}}
            <a href="#" target="_blank" class="social-icon-link" style="text-decoration: none;">
                <div class="icon-box" style="width: 4.5rem; height: 4.5rem; background: #1877F2; color: #fff; border-radius: 50%; display: flex; align-items: center; justify-content: center; transition: all 0.3s ease;">
                    <svg style="width: 28px; height: 28px;" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z" />
                    </svg>
                </div>
            </a>

            {{-- Instagram --}}
            <a href="https://www.instagram.com/isewa_id?igsh=Zng0b2VqZnFhYzd6" target="_blank" class="social-icon-link" style="text-decoration: none;">
                <div class="icon-box" style="width: 4.5rem; height: 4.5rem; background: linear-gradient(45deg, #f09433 0%, #e6683c 25%, #dc2743 50%, #cc2366 75%, #bc1888 100%); color: #fff; border-radius: 50%; display: flex; align-items: center; justify-content: center; transition: all 0.3s ease;">
                    <svg style="width: 28px; height: 28px;" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z" />
                    </svg>
                </div>
            </a>

            {{-- YouTube --}}
            <a href="#" target="_blank" class="social-icon-link" style="text-decoration: none;">
                <div class="icon-box" style="width: 4.5rem; height: 4.5rem; background: #FF0000; color: #fff; border-radius: 50%; display: flex; align-items: center; justify-content: center; transition: all 0.3s ease;">
                    <svg style="width: 28px; height: 28px;" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z" />
                    </svg>
                </div>
            </a>
        </div>
    </section>
    
    <style>
        .social-icon-link:hover .icon-box {
            transform: translateY(-8px) scale(1.1);
            box-shadow: 0 0 25px currentColor;
            filter: brightness(1.2);
        }
    </style>
</div>
@endsection


@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {

    const items = document.querySelectorAll('.section, .hero-inner, .pengembang-row, .partners-row');

    const observer = new IntersectionObserver(entries => {
        entries.forEach(e => {
            if (e.isIntersecting) e.target.classList.add('inview');
        });
    }, { threshold: 0.15 });

    items.forEach(el => observer.observe(el));

    // Sidebar Active
    const path = window.location.pathname;
    const link = document.querySelector(`a[href="${path}"]`);
    if (link) {
        const li = link.closest('li');
        if (li) li.classList.add('active');
    }
});
</script>
@endsection