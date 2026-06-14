<footer class="relative z-10 bg-[#115789] text-white pt-8 sm:pt-10 pb-6 mt-auto border-t border-white/10">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 md:gap-12 lg:gap-8 mb-0">

            {{-- Column 1: Logo - responsive --}}
            <div class="flex flex-col items-center md:items-start -mt-8 md:-mt-20">
                <img src="{{ asset('User/img/logo/iSewaF.png') }}" alt="iSewa Logo"
                    class="h-32 sm:h-48 md:h-65 w-auto object-contain relative z-10">

                <img src="{{ asset('User/img/logo/bklss.png') }}" alt="Bengkalis Bermasa"
                    class="h-32 sm:h-48 md:h-65 w-auto object-contain -mt-4 sm:-mt-8 md:-mt-12 relative z-0">
            </div>

            {{-- Column 2: Navigation --}}
            <div class="flex flex-col items-center md:items-center md:pt-2">
                <div class="flex flex-col space-y-3 sm:space-y-5 text-center md:text-left">
                    <a href="{{ route('beranda') }}"
                        class="text-base sm:text-lg font-medium hover:text-blue-300 transition-colors duration-200">
                        Beranda
                    </a>
                    <a href="{{ route('pelayanan') }}"
                        class="text-base sm:text-lg font-medium hover:text-blue-300 transition-colors duration-200">
                        Pelayanan
                    </a>
                    <a href="{{ route('bumdes.profil') }}"
                        class="text-base sm:text-lg font-medium hover:text-blue-300 transition-colors duration-200">
                        BUMDes
                    </a>
                    <a href="{{ route('isewa.profile') }}" class="text-base sm:text-lg font-medium hover:text-blue-300 transition-colors duration-200">
                        Profil iSewa
                    </a>
                </div>
            </div>

            {{-- Column 3: Contact --}}
            <div class="flex flex-col space-y-4 sm:space-y-6 items-center md:items-end md:pt-2">
                {{-- Location --}}
                {{-- Location --}}
                <a href="https://maps.app.goo.gl/77Vy8U9MWY8rJpys6" target="_blank" class="flex items-start gap-3 md:flex-row-reverse text-center md:text-right group hover:text-blue-300 transition-colors">
                    <div class="bg-white/10 p-2 rounded-full group-hover:bg-white/20 transition-colors">
                        <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </div>
                    <span class="text-sm sm:text-[15px] leading-relaxed mt-1 group-hover:underline">Bengkalis, Riau, Indonesia</span>
                </a>

                {{-- Email --}}
                <div class="flex items-center gap-3 md:flex-row-reverse text-center md:text-right group">
                    <div class="bg-white/10 p-2 rounded-full group-hover:bg-white/20 transition-colors">
                        <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <a href="mailto:iSewa.digital@gmail.com"
                        class="text-sm sm:text-[15px] hover:text-blue-300 transition-colors">iSewa.digital@gmail.com</a>
                </div>

                {{-- Phone --}}
                <div class="flex items-center gap-3 md:flex-row-reverse text-center md:text-right group">
                    <div class="bg-white/10 p-2 rounded-full group-hover:bg-white/20 transition-colors">
                        <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                        </svg>
                    </div>
                    <a href="https://wa.me/6282249213061" class="text-sm sm:text-[15px] hover:text-blue-300 transition-colors">(+62)
                        822-4921-3061</a>
                </div>

                {{-- Social Media --}}
                <div class="flex items-center gap-2 sm:gap-3 pt-2">
                    <a href="#"
                        class="bg-white text-[#115789] rounded-md p-2 hover:bg-blue-100 transition-all hover:-translate-y-1">
                        <svg class="w-4 h-4 sm:w-5 sm:h-5" viewBox="0 0 24 24" fill="currentColor">
                            <path
                                d="M19.59 6.69a4.83 4.83 0 01-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 01-5.2 1.74 2.89 2.89 0 012.31-4.64 2.93 2.93 0 01.88.13V9.4a6.84 6.84 0 00-1-.05A6.33 6.33 0 005 20.1a6.34 6.34 0 0010.86-4.43v-7a8.16 8.16 0 004.77 1.52v-3.4a4.85 4.85 0 01-1-.1z" />
                        </svg>
                    </a>
                    <a href="#"
                        class="bg-white text-[#115789] rounded-md p-2 hover:bg-blue-100 transition-all hover:-translate-y-1">
                        <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="currentColor" viewBox="0 0 24 24">
                            <path
                                d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z" />
                        </svg>
                    </a>
                    <a href="https://www.instagram.com/isewa_id?igsh=Zng0b2VqZnFhYzd6" target="_blank"
                        class="bg-white text-[#115789] rounded-md p-2 hover:bg-blue-100 transition-all hover:-translate-y-1">
                        <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="currentColor" viewBox="0 0 24 24">
                            <path
                                d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z" />
                        </svg>
                    </a>
                    <a href="#"
                        class="bg-white text-[#115789] rounded-md p-2 hover:bg-blue-100 transition-all hover:-translate-y-1">
                        <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="currentColor" viewBox="0 0 24 24">
                            <path
                                d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z" />
                        </svg>
                    </a>
                </div>
            </div>
        </div>

        <div class="border-t border-white/20 mb-6 sm:mb-8"></div>

        <div class="text-center text-xs sm:text-sm text-gray-100 font-medium tracking-wide px-2">
            <p>&copy; 2025 SISTEM PENYEWAAN ALAT DAN PROMOSI USAHA BUMDES BERBASIS DIGITAL</p>
        </div>
    </div>
</footer>