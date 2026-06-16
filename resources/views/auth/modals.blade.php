{{-- Modal Auth Container --}}
<div id="auth-modal-overlay"
    class="fixed inset-0 bg-black/40 backdrop-blur-sm z-50 hidden opacity-0 transition-opacity duration-300">
    <div class="flex items-center justify-center min-h-screen p-4">

        {{-- MODAL LOGIN --}}
        <div id="modal-login"
            class="modal-content bg-white rounded-2xl shadow-lg max-w-[420px] w-full p-8 transform scale-95 opacity-0 transition-all duration-300 hidden relative">
            <button type="button"
                class="modal-close absolute top-4 right-4 text-gray-400 hover:text-gray-600 transition z-10">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>

            <div class="text-center mb-6">
                <h2 class="text-2xl font-bold text-gray-900 mb-4">Masuk</h2>
                <div class="bg-yellow-50 border border-yellow-200 text-yellow-800 text-xs px-3 py-2 rounded-lg mb-4 text-left flex gap-2 items-start shadow-sm">
                    <svg class="w-4 h-4 flex-shrink-0 mt-0.5 text-yellow-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path></svg>
                    <span><b>Anti-Phishing:</b> Pastikan URL di browser Anda adalah <b>{{ request()->getHost() }}</b> sebelum memasukkan sandi.</span>
                </div>
                <p class="text-sm text-gray-600 mb-4">Silahkan masuk atau daftar menggunakan akun anda</p>
            </div>

            {{-- Tab Buttons --}}
            <div class="flex gap-3 mb-6">
                <button type="button" id="tab-login"
                    class="flex-1 py-3 rounded-full text-sm font-semibold bg-blue-500 text-white transition shadow-sm">
                    Masuk
                </button>
                <button type="button" id="tab-register"
                    class="flex-1 py-3 rounded-full text-sm font-semibold bg-white text-gray-700 border-2 border-blue-400 hover:bg-gray-50 transition">
                    Daftar
                </button>
            </div>

            {{-- Form Login --}}
            <form action="{{ route('auth.login') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <input type="text" name="email_or_phone" placeholder="Email / Username / No Telepon" required
                        class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none transition">
                    <span class="text-red-500 text-sm hidden block mt-1" data-error="email_or_phone"></span>
                </div>

                <div class="relative">
                    <input type="password" name="password" id="login-password" placeholder="Kata Sandi" required
                        class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none transition pr-12">
                    <button type="button"
                        class="toggle-password absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600"
                        data-target="login-password">
                        <svg class="w-5 h-5 eye-open" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                        <svg class="w-5 h-5 eye-closed hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                        </svg>
                    </button>
                </div>
                <span class="text-red-500 text-sm hidden block mt-1" data-error="password"></span>

                <div class="flex items-center justify-between text-sm">
                    <label class="flex items-center cursor-pointer">
                        <input type="checkbox" name="remember" id="remember-me" value="1" class="mr-2 rounded">
                        <span class="text-gray-600">Ingat Saya</span>
                    </label>
                    <button type="button" id="btn-open-forgot-password" class="text-blue-500 hover:underline">Lupa Kata Sandi?</button>
                </div>

                <button type="submit"
                    class="w-full py-3 bg-blue-500 text-white rounded-full font-semibold hover:bg-blue-600 transition disabled:opacity-50 disabled:cursor-not-allowed">
                    <span class="btn-text">Masuk</span>
                    <span class="btn-loading hidden">
                        <svg class="animate-spin h-5 w-5 mx-auto" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                            </path>
                        </svg>
                    </span>
                </button>
            </form>

            <div class="mt-6 text-center text-gray-500 text-sm">atau</div>

            {{-- Social Login --}}
            <div class="mt-4 space-y-3 flex flex-col gap-3">
                <a href="{{ route('auth.google') }}"
                    class="w-full py-3 border border-gray-300 rounded-full flex items-center justify-center gap-3 hover:bg-gray-50 transition text-decoration-none">
                    <svg class="w-5 h-5" viewBox="0 0 24 24">
                        <path fill="#4285F4"
                            d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" />
                        <path fill="#34A853"
                            d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" />
                        <path fill="#FBBC05"
                            d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" />
                        <path fill="#EA4335"
                            d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" />
                    </svg>
                    <span class="font-medium text-gray-700">Lanjutkan dengan Google</span>
                </a>
            </div>
        </div>

        {{-- MODAL GOOGLE REGISTER --}}
        <div id="modal-google-register"
            class="modal-content bg-white rounded-2xl shadow-2xl max-w-2xl w-full p-8 transform scale-95 opacity-0 transition-all duration-300 hidden relative">
            {{-- Close Button --}}
            <button type="button" class="modal-close absolute top-4 right-4 text-gray-400 hover:text-gray-600 transition z-10">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>

            <div class="text-center mb-6">
                <h2 class="text-2xl font-bold text-gray-900 mb-2">Lengkapi Profil</h2>
                <div class="flex items-center justify-center gap-2 text-sm text-gray-600 mb-4">
                    <img src="https://www.google.com/favicon.ico" class="w-4 h-4" alt="Google">
                    <span>Melanjutkan pendaftaran dengan Google</span>
                </div>
                
                {{-- Avatar Preview --}}
                <div class="relative inline-block mb-4 group">
                    <img id="google-avatar-preview" 
                         src="{{ session('google_register_data.avatar') }}" 
                         class="w-24 h-24 rounded-full object-cover border-4 border-blue-50 shadow-md" 
                         alt="Avatar">
                    <label for="google-avatar-input" 
                           class="absolute bottom-0 right-0 bg-blue-600 text-white rounded-full p-2 cursor-pointer hover:bg-blue-700 transition shadow-sm transform group-hover:scale-110">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                        </svg>
                    </label>
                </div>
            </div>

            <form id="form-google-register" class="space-y-3 max-h-96 overflow-y-auto pr-2">
                <input type="file" id="google-avatar-input" name="avatar" class="hidden" accept="image/*">
                
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 mb-1">Username</label>
                        <input type="text" name="username" placeholder="Username Unik" required
                            class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none transition text-sm">
                        <span class="text-red-500 text-xs hidden block mt-1" data-error="username"></span>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 mb-1">Email (Google)</label>
                        <input type="email" name="email" value="{{ session('google_register_data.email') }}" readonly
                            class="w-full px-4 py-2.5 rounded-lg border border-gray-300 bg-gray-100 text-gray-500 cursor-not-allowed outline-none transition text-sm">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1">Nama Lengkap</label>
                    <input type="text" name="name" value="{{ session('google_register_data.name') }}" required
                        class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none transition text-sm">
                    <span class="text-red-500 text-xs hidden block mt-1" data-error="name"></span>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 mb-1">No. Handphone</label>
                        <input type="tel" name="phone" placeholder="08xxxxxxxxxx" required
                            class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none transition text-sm">
                        <span class="text-red-500 text-xs hidden block mt-1" data-error="phone"></span>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 mb-1">Jenis Kelamin</label>
                        <select name="gender" required
                            class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none transition text-sm">
                            <option value="">Pilih...</option>
                            <option value="laki-laki">Laki-laki</option>
                            <option value="perempuan">Perempuan</option>
                        </select>
                        <span class="text-red-500 text-xs hidden block mt-1" data-error="gender"></span>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1">Alamat</label>
                    <textarea name="address" placeholder="Alamat Lengkap" required rows="2"
                        class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none transition text-sm resize-none"></textarea>
                    <span class="text-red-500 text-xs hidden block mt-1" data-error="address"></span>
                </div>

                {{-- Password Section --}}
                <div class="bg-blue-50 p-3 rounded-lg border border-blue-100 mb-2">
                    <div class="flex items-start gap-2">
                        <svg class="w-4 h-4 text-blue-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <p class="text-xs text-blue-700">Opsional: Buat kata sandi agar bisa login tanpa Google.</p>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div class="relative">
                        <input type="password" name="password" id="google-password" placeholder="Kata Sandi (Opsional)"
                            class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none transition text-sm pr-12">
                        <button type="button" class="toggle-password absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600" data-target="google-password">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                        </button>
                    </div>
                    <div class="relative">
                        <input type="password" name="password_confirmation" id="google-password-confirm" placeholder="Konfirmasi"
                            class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none transition text-sm pr-12">
                    </div>
                </div>
                <span class="text-red-500 text-xs hidden block mt-1" data-error="password"></span>

                <button type="submit"
                    class="w-full py-3 bg-blue-500 text-white rounded-full font-semibold hover:bg-blue-600 transition disabled:opacity-50 disabled:cursor-not-allowed mt-4">
                    <span class="btn-text">Selesaikan Pendaftaran</span>
                    <span class="btn-loading hidden">
                        <svg class="animate-spin h-5 w-5 mx-auto" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </span>
                </button>
            </form>
        </div>

        {{-- MODAL REGISTER --}}
        <div id="modal-register"
            class="modal-content bg-white rounded-2xl shadow-2xl max-w-2xl w-full p-8 transform scale-95 opacity-0 transition-all duration-300 hidden relative">
            <button type="button"
                class="modal-close absolute top-4 right-4 text-gray-400 hover:text-gray-600 transition z-10">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>

            <div class="text-center mb-6">
                <h2 class="text-2xl font-bold text-gray-900 mb-4">Daftar</h2>
                <p class="text-sm text-gray-600 mb-4">Silahkan masuk atau daftar menggunakan akun anda</p>
            </div>

            {{-- Tab Buttons --}}
            <div class="flex gap-3 mb-6">
                <button type="button" id="tab-login-2"
                    class="flex-1 py-3 rounded-full text-sm font-semibold bg-white text-gray-700 border-2 border-blue-400 hover:bg-gray-50 transition">
                    Masuk
                </button>
                <button type="button" id="tab-register-2"
                    class="flex-1 py-3 rounded-full text-sm font-semibold bg-blue-500 text-white transition shadow-sm">
                    Daftar
                </button>
            </div>

            {{-- Form Register --}}
            <form id="form-register" action="{{ route('auth.register') }}" method="POST" class="space-y-3 max-h-96 overflow-y-auto pr-2">
                @csrf
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <input type="text" name="username" placeholder="Username" required
                            class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none transition text-sm">
                        <span class="text-red-500 text-xs hidden block mt-1" data-error="username"></span>
                    </div>
                    <div>
                        <input type="email" name="email" placeholder="Email" required
                            class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none transition text-sm">
                        <span class="text-red-500 text-xs hidden block mt-1" data-error="email"></span>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <input type="text" name="name" placeholder="Nama Lengkap" required
                            class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none transition text-sm">
                        <span class="text-red-500 text-xs hidden block mt-1" data-error="name"></span>
                    </div>
                    <div>
                        <input type="tel" name="phone" placeholder="No Telepon" required
                            class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none transition text-sm">
                        <span class="text-red-500 text-xs hidden block mt-1" data-error="phone"></span>
                    </div>
                </div>

                <div>
                    <textarea name="address" placeholder="Alamat" required rows="2"
                        class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none transition text-sm resize-none"></textarea>
                    <span class="text-red-500 text-xs hidden block mt-1" data-error="address"></span>
                </div>

                <div>
                    <select name="gender" required
                        class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none transition text-sm">
                        <option value="">Jenis Kelamin</option>
                        <option value="laki-laki">Laki-laki</option>
                        <option value="perempuan">Perempuan</option>
                    </select>
                    <span class="text-red-500 text-xs hidden block mt-1" data-error="gender"></span>
                </div>

                <div class="relative">
                    <input type="password" name="password" id="register-password" placeholder="Kata Sandi" required
                        class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none transition text-sm pr-12">
                    <button type="button"
                        class="toggle-password absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600"
                        data-target="register-password">
                        <svg class="w-5 h-5 eye-open" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                        <svg class="w-5 h-5 eye-closed hidden" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                        </svg>
                    </button>
                </div>
                <span class="text-red-500 text-xs hidden block mt-1" data-error="password"></span>

                <div class="relative">
                    <input type="password" name="password_confirmation" id="register-password-confirm"
                        placeholder="Konfirmasi Kata Sandi" required
                        class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none transition text-sm pr-12">
                    <button type="button"
                        class="toggle-password absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600"
                        data-target="register-password-confirm">
                        <svg class="w-5 h-5 eye-open" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                        <svg class="w-5 h-5 eye-closed hidden" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                        </svg>
                    </button>
                </div>
                <span class="text-red-500 text-xs hidden block mt-1" data-error="password_confirmation"></span>

                <button type="submit"
                    class="w-full py-3 bg-blue-500 text-white rounded-full font-semibold hover:bg-blue-600 transition disabled:opacity-50 disabled:cursor-not-allowed">
                    <span class="btn-text">Daftar</span>
                    <span class="btn-loading hidden">
                        <svg class="animate-spin h-5 w-5 mx-auto" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                            </path>
                        </svg>
                    </span>
                </button>
            </form>
        </div>

        {{-- MODAL OTP --}}
        <div id="modal-otp"
            class="modal-content bg-white rounded-3xl shadow-2xl max-w-md w-full p-8 transform scale-95 opacity-0 transition-all duration-300 hidden relative">
            <button type="button"
                class="modal-close absolute top-4 right-4 text-gray-400 hover:text-gray-600 transition">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>

            <div class="text-center mb-8">
                <h2 class="text-2xl font-bold text-gray-900 mb-2">Verifikasi Kode</h2>
                <div class="flex justify-center mb-6">
                    <img src="{{ asset('User/img/elemen/verifff.png') }}" alt="Verification Icon" class="h-32 object-contain">
                </div>
                <p class="text-lg font-semibold text-gray-900 mb-2">Masukkan Kode Untuk Melanjutkan</p>
                <p class="text-sm text-gray-500">Silahkan masukkan kode konfirmasi yang anda terima</p>
            </div>

            @if(session('error'))
                <div class="bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded-lg mb-4 text-sm text-center font-medium">
                    {{ session('error') }}
                </div>
            @endif

            @if(session('success'))
                <div class="bg-green-50 border border-green-200 text-green-600 px-4 py-3 rounded-lg mb-4 text-sm text-center font-medium">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('trigger_open_otp_tab'))
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                title: 'SANDBOX OTP (Demo)',
                                html: 'Kode OTP Anda adalah: <b>{{ session("otp_demo_sandbox_code") }}</b><br><br><small><i>Ini muncul karena mode email asli dimatikan untuk mencegah spam.</i></small>',
                                icon: 'info',
                                confirmButtonText: 'Tutup'
                            });
                        } else {
                            alert("SANDBOX OTP (Demo)\n\nKode OTP Anda adalah: {{ session('otp_demo_sandbox_code') }}");
                        }
                    });
                </script>
            @endif

            <form action="{{ route('auth.verify-otp') }}" method="POST" id="form-verify-otp" class="space-y-6">
                @csrf
                <div class="flex justify-center gap-4 mb-8">
                    <input type="text" class="otp-input w-14 h-14 text-center text-2xl font-bold rounded-xl border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none transition" maxlength="1" data-index="0">
                    <input type="text" class="otp-input w-14 h-14 text-center text-2xl font-bold rounded-xl border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none transition" maxlength="1" data-index="1">
                    <input type="text" class="otp-input w-14 h-14 text-center text-2xl font-bold rounded-xl border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none transition" maxlength="1" data-index="2">
                    <input type="text" class="otp-input w-14 h-14 text-center text-2xl font-bold rounded-xl border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none transition" maxlength="1" data-index="3">
                </div>
                <input type="hidden" name="otp_code" id="real_otp_code">

                <button type="submit"
                    class="w-full py-3 bg-blue-500 text-white rounded-full font-semibold hover:bg-blue-600 transition">
                    Konfirmasi
                </button>
            </form>

            <form action="{{ route('auth.resend-otp') }}" method="POST" class="mt-6 text-center">
                @csrf
                <p class="text-sm text-gray-600">Belum Terima Kode? 
                    <button type="submit" class="font-medium text-blue-500 hover:text-blue-600 hover:underline">
                        Kirim Ulang Kode
                    </button>
                </p>
            </form>
        </div>

        {{-- MODAL FORGOT PASSWORD OTP --}}
        <div id="modal-forgot-otp"
            class="modal-content bg-white rounded-3xl shadow-2xl max-w-md w-full p-8 transform scale-95 opacity-0 transition-all duration-300 hidden relative">
            <button type="button"
                class="modal-close absolute top-4 right-4 text-gray-400 hover:text-gray-600 transition">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>

            <div class="text-center mb-8">
                <h2 class="text-2xl font-bold text-gray-900 mb-2">Lupa Kata Sandi</h2>
                <div class="flex justify-center mb-6">
                    <img src="{{ asset('User/img/elemen/verifff.png') }}" alt="Verification Icon" class="h-32 object-contain">
                </div>
                <p class="text-lg font-semibold text-gray-900 mb-2">Masukkan Kode Untuk Melanjutkan</p>
                <p class="text-sm text-gray-500">Silahkan masukkan kode OTP yang dikirim ke email Anda</p>
            </div>

            @if(session('error'))
                <div class="bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded-lg mb-4 text-sm text-center font-medium">
                    {{ session('error') }}
                </div>
            @endif

            @if(session('success'))
                <div class="bg-green-50 border border-green-200 text-green-600 px-4 py-3 rounded-lg mb-4 text-sm text-center font-medium">
                    {{ session('success') }}
                </div>
            @endif



            <form action="{{ route('auth.forgot-password.verify-otp') }}" method="POST" id="form-verify-forgot-otp" class="space-y-6">
                @csrf
                <div class="flex justify-center gap-4 mb-8">
                    <input type="text" class="otp-input w-14 h-14 text-center text-2xl font-bold rounded-xl border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none transition" maxlength="1" data-index="0">
                    <input type="text" class="otp-input w-14 h-14 text-center text-2xl font-bold rounded-xl border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none transition" maxlength="1" data-index="1">
                    <input type="text" class="otp-input w-14 h-14 text-center text-2xl font-bold rounded-xl border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none transition" maxlength="1" data-index="2">
                    <input type="text" class="otp-input w-14 h-14 text-center text-2xl font-bold rounded-xl border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none transition" maxlength="1" data-index="3">
                </div>
                <input type="hidden" name="otp" id="real_forgot_otp">

                <button type="submit"
                    class="w-full py-3 bg-blue-500 text-white rounded-full font-semibold hover:bg-blue-600 transition">
                    Konfirmasi
                </button>
            </form>

            <form action="{{ route('auth.forgot-password.resend-otp') }}" method="POST" class="mt-6 text-center">
                @csrf
                <p class="text-sm text-gray-600">Belum Terima Kode? 
                    <button type="submit" class="font-medium text-blue-500 hover:text-blue-600 hover:underline">
                        Kirim Ulang Kode
                    </button>
                </p>
            </form>
        </div>

        {{-- MODAL SUCCESS --}}
        <div id="modal-success"
            class="modal-content bg-white rounded-3xl shadow-2xl max-w-md w-full p-8 transform scale-95 opacity-0 transition-all duration-300 hidden relative">
            <div class="text-center">
                <h2 class="text-2xl font-bold text-gray-900 mb-6">Selamat Datang</h2>

                <div class="flex justify-center mb-6">
                    <img src="{{ asset('User/img/logo/iSewaT.png') }}" alt="SiladesBeng Logo"
                        class="h-32">
                </div>

                <p class="text-lg font-semibold text-gray-900 mb-2">Pembuatan Akun Berhasil</p>
                <p class="text-sm text-gray-500 mb-8">Silahkan konfirmasi untuk melanjutkan</p>

                <button type="button" id="btn-confirm-success"
                    class="w-full py-3 bg-blue-500 text-white rounded-full font-semibold hover:bg-blue-600 transition">
                    Konfirmasi
                </button>
            </div>
        </div>
        
        {{-- MODAL FORGOT PASSWORD STEP 1: Input Email --}}
        <div id="modal-forgot-password"
            class="modal-content bg-white rounded-3xl shadow-2xl max-w-md w-full p-8 transform scale-95 opacity-0 transition-all duration-300 hidden relative">
            <button type="button"
                class="modal-close absolute top-4 right-4 text-gray-400 hover:text-gray-600 transition">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>

            <div class="text-center mb-8">
                <h2 class="text-2xl font-bold text-gray-900 mb-2">Lupa Kata Sandi</h2>
                <p class="text-sm text-gray-600">Masukkan Email terkait untuk perbarui Password</p>
            </div>

            <div class="flex justify-center mb-8">
                <img src="{{ asset('User/img/logo/iSewaT.png') }}" alt="SiladesBeng Logo" class="h-32">
            </div>

            <form action="{{ route('auth.forgot-password') }}" method="POST" class="space-y-6">
                @csrf
                <div>
                    <input type="email" name="email" placeholder="Email" required
                        class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none transition">
                    <span class="text-red-500 text-sm hidden block mt-1" data-error="email"></span>
                </div>

                <button type="submit"
                    class="w-full py-3 bg-blue-500 text-white rounded-full font-semibold hover:bg-blue-600 transition disabled:opacity-50 disabled:cursor-not-allowed">
                    <span class="btn-text">Kirim OTP</span>
                    <span class="btn-loading hidden">
                        <svg class="animate-spin h-5 w-5 mx-auto" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                            </path>
                        </svg>
                    </span>
                </button>
            </form>
        </div>

        {{-- MODAL RESET PASSWORD (Buat Kata Sandi Baru) --}}
        <div id="modal-reset-password"
            class="modal-content bg-white rounded-3xl shadow-2xl max-w-md w-full p-8 transform scale-95 opacity-0 transition-all duration-300 hidden relative">
            <button type="button"
                class="modal-close absolute top-4 right-4 text-gray-400 hover:text-gray-600 transition">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>

            <div class="text-center mb-8">
                <h2 class="text-2xl font-bold text-gray-900 mb-2">Buat Kata Sandi Baru</h2>
                <div class="flex justify-center mb-6">
                    <img src="{{ asset('User/img/elemen/verifff.png') }}" alt="Reset Password Icon" class="h-32 object-contain">
                </div>
                <p class="text-sm text-gray-500">Silahkan masukkan kata sandi baru untuk akun Anda</p>
            </div>

            @if(session('error'))
                <div class="bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded-lg mb-4 text-sm text-center font-medium">
                    {{ session('error') }}
                </div>
            @endif

            @if(session('success'))
                <div class="bg-green-50 border border-green-200 text-green-600 px-4 py-3 rounded-lg mb-4 text-sm text-center font-medium">
                    {{ session('success') }}
                </div>
            @endif

            <form action="{{ route('auth.forgot-password.reset') }}" method="POST" class="space-y-6">
                @csrf

                <div>
                    <label for="reset-password" class="block text-sm font-medium text-gray-700 mb-2">Kata Sandi Baru</label>
                    <div class="relative">
                        <input id="reset-password" name="password" type="password" required placeholder="Minimal 8 karakter"
                            class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none transition pr-12">
                        <button type="button"
                            class="toggle-password absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600"
                            data-target="reset-password">
                            <svg class="w-5 h-5 eye-open" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            <svg class="w-5 h-5 eye-closed hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                            </svg>
                        </button>
                    </div>
                    @error('password')
                        <span class="text-red-500 text-xs block mt-1">{{ $message }}</span>
                    @enderror
                </div>

                <div>
                    <label for="reset-password-confirm" class="block text-sm font-medium text-gray-700 mb-2">Konfirmasi Kata Sandi Baru</label>
                    <div class="relative">
                        <input id="reset-password-confirm" name="password_confirmation" type="password" required placeholder="Ulangi kata sandi baru"
                            class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none transition pr-12">
                        <button type="button"
                            class="toggle-password absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600"
                            data-target="reset-password-confirm">
                            <svg class="w-5 h-5 eye-open" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            <svg class="w-5 h-5 eye-closed hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                            </svg>
                        </button>
                    </div>
                </div>

                <button type="submit"
                    class="w-full py-3 bg-blue-500 text-white rounded-full font-semibold hover:bg-blue-600 transition">
                    Simpan Kata Sandi
                </button>
            </form>
        </div>

    </div>
</div>

<style>
    /* Modal Animation */
    #auth-modal-overlay.show {
        opacity: 1;
    }

    #auth-modal-overlay.show .modal-content {
        transform: scale(1);
        opacity: 1;
    }

    /* Custom scrollbar untuk form register */
    #form-register::-webkit-scrollbar {
        width: 6px;
    }

    #form-register::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 10px;
    }

    #form-register::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 10px;
    }

    #form-register::-webkit-scrollbar-thumb:hover {
        background: #94a3b8;
    }

    /* OTP Input Animation */
    .otp-input:focus {
        animation: pulse 0.3s ease-in-out;
    }

    @keyframes pulse {

        0%,
        100% {
            transform: scale(1);
        }

        50% {
            transform: scale(1.05);
        }
    }
</style>
