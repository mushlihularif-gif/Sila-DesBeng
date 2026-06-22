@extends('layouts.app')

@push('styles')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <style>
        /* Animasi Pemuatan Halaman Global */
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
        
        .animate-section {
            opacity: 0;
            transform: translateY(30px);
        }
        
        .animate-section.show {
            animation: fadeInUp 0.8s ease-out forwards;
        }
        
        .animate-section:nth-child(1) { animation-delay: 0.1s; }
        .animate-section:nth-child(2) { animation-delay: 0.2s; }
        .animate-section:nth-child(3) { animation-delay: 0.3s; }
        .animate-section:nth-child(4) { animation-delay: 0.4s; }
        .animate-section:nth-child(5) { animation-delay: 0.5s; }
        .animate-section:nth-child(6) { animation-delay: 0.6s; }
        .animate-section:nth-child(7) { animation-delay: 0.7s; }
        .animate-section:nth-child(8) { animation-delay: 0.8s; }

        /* ================================================ */
        /* RESPONSIVE BACKGROUND OPTIMIZATION */
        /* ================================================ */
        
        /* Hide complex backgrounds on mobile */
        @media (max-width: 767px) {
            .bg-decorative {
                opacity: 0.3 !important;
            }
            .bg-hide-mobile {
                display: none !important;
            }
        }

        /* Tablet adjustments */
        @media (min-width: 768px) and (max-width: 1023px) {
            .bg-decorative {
                opacity: 0.5 !important;
            }
        }

        /* Desktop - full opacity */
        @media (min-width: 1024px) {
            .bg-decorative {
                opacity: 0.85 !important;
            }
        }
    </style>
@endpush

@section('content')
    {{-- NAVBAR PENGGUNA --}}
    @include('partials.navbar')

    <main id="main-content" class="flex-grow relative w-full">
        @yield('page')
    </main>

    {{-- FOOTER PENGGUNA --}}
    @include('partials.footer')

    {{-- MODAL AUTENTIKASI --}}
    @include('auth.modals')

@endsection

@push('scripts')
    {{-- Skrip Global --}}
    <script>
        (() => {
            /**
             * Fungsionalitas Navbar & Menu Seluler
             */
            const Navbar = {
                init() {
                    this.initSidebar();
                    this.initMobileDropdowns();
                    this.initScrollEffect();
                    this.initMobileAuthButtons();
                },

                // Sidebar Seluler
                initSidebar() {
                    const menuBtn = document.getElementById('mobile-menu-btn');
                    const sidebar = document.getElementById('mobile-sidebar');
                    const overlay = document.getElementById('mobile-overlay');
                    const closeBtn = document.getElementById('sidebar-close');

                    if (!menuBtn || !sidebar || !overlay) {
                        return;
                    }

                    const openSidebar = () => {
                        sidebar.classList.remove('-translate-x-full');
                        overlay.classList.remove('hidden');
                        setTimeout(() => overlay.classList.remove('opacity-0'), 10);
                        document.body.style.overflow = 'hidden';
                    };

                    const closeSidebar = () => {
                        sidebar.classList.add('-translate-x-full');
                        overlay.classList.add('opacity-0');
                        setTimeout(() => overlay.classList.add('hidden'), 300);
                        document.body.style.overflow = '';
                    };

                    // Simpan ke window untuk akses global
                    window.closeMobileSidebar = closeSidebar;

                    menuBtn.addEventListener('click', openSidebar);
                    closeBtn?.addEventListener('click', closeSidebar);
                    overlay.addEventListener('click', closeSidebar);

                    // Tutup saat link diklik
                    sidebar.querySelectorAll('a').forEach(link => {
                        link.addEventListener('click', closeSidebar);
                    });
                },

                // Dropdown Seluler (BUMDes)
                initMobileDropdowns() {
                    const toggle = document.getElementById('bumdes-toggle');
                    const subMenu = document.getElementById('bumdes-sub');
                    const arrow = document.getElementById('bumdes-arrow');

                    if (!toggle || !subMenu) return;

                    toggle.addEventListener('click', (e) => {
                        e.preventDefault();
                        subMenu.classList.toggle('hidden');
                        if (arrow) {
                            arrow.classList.toggle('rotate-180');
                        }
                    });
                },

                // Efek Scroll Navbar
                initScrollEffect() {
                    if (!window.userNavbarScrollHandler) {
                        window.userNavbarScrollHandler = () => {
                            const navbar = document.querySelector('.sd-navbar');
                            if (!navbar) return;
                            
                            if (window.scrollY > 10) {
                                navbar.classList.add('scrolled');
                            } else {
                                navbar.classList.remove('scrolled');
                            }
                        };

                        window.addEventListener('scroll', window.userNavbarScrollHandler);
                        window.addEventListener('resize', window.userNavbarScrollHandler);
                    }
                    
                    // Trigger once on init
                    if(window.userNavbarScrollHandler) {
                        window.userNavbarScrollHandler();
                    }
                },

                // Handler untuk tombol auth di mobile
                initMobileAuthButtons() {
                    const mobileLoginBtn = document.getElementById('btn-open-login-mobile');
                    const mobileRegisterBtn = document.getElementById('btn-open-register-mobile');
                    const desktopLoginBtn = document.getElementById('btn-open-login');
                    const desktopRegisterBtn = document.getElementById('btn-open-register');

                    // Tombol Login Mobile -> trigger modal login
                    if (mobileLoginBtn) {
                        mobileLoginBtn.addEventListener('click', () => {
                            // Tutup sidebar dulu
                            if (window.closeMobileSidebar) {
                                window.closeMobileSidebar();
                            }
                            // Trigger tombol desktop setelah sidebar tertutup
                            setTimeout(() => {
                                if (desktopLoginBtn) {
                                    desktopLoginBtn.click();
                                }
                            }, 350);
                        });
                    }

                    // Tombol Register Mobile -> trigger modal register
                    if (mobileRegisterBtn) {
                        mobileRegisterBtn.addEventListener('click', () => {
                            // Tutup sidebar dulu
                            if (window.closeMobileSidebar) {
                                window.closeMobileSidebar();
                            }
                            // Trigger tombol desktop setelah sidebar tertutup
                            setTimeout(() => {
                                if (desktopRegisterBtn) {
                                    desktopRegisterBtn.click();
                                }
                            }, 350);
                        });
                    }
                }
            };

            // Initialize Navbar
            Navbar.init();
        })();
    </script>

    {{-- Picu Modal Login jika Sesi Ada --}}
    @if(session('open_login_modal'))
        <script>
            (() => {
                setTimeout(() => {
                    const overlay = document.getElementById('auth-modal-overlay');
                    const modalLogin = document.getElementById('modal-login');
                    
                    if (overlay && modalLogin) {
                        document.querySelectorAll('.modal-content').forEach(m => {
                            m.classList.add('hidden');
                            m.classList.remove('scale-100', 'opacity-100');
                        });

                        overlay.classList.remove('hidden');
                        setTimeout(() => {
                            overlay.classList.add('show');
                            modalLogin.classList.remove('hidden');
                            setTimeout(() => {
                                modalLogin.classList.add('scale-100', 'opacity-100');
                            }, 50);
                        }, 10);
                    }
                }, 300);
            })();
        </script>
    @endif

    {{-- Pemicu Animasi Global --}}
    <script>
        (() => {
            const sections = document.querySelectorAll('.animate-section');
            sections.forEach((section, index) => {
                setTimeout(() => {
                    section.classList.add('show');
                }, index * 100);
            });
        })();
    </script>

    @include('auth.scripts')
@endpush