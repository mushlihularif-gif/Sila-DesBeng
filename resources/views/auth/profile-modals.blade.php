{{-- ========================================
    PROFILE CHANGE PASSWORD MODALS
    - Modal 1: Input 3 passwords (Current, New, Confirm)
    - Modal 2: OTP Verification (4 digit)
    - Modal 3: Success Message
======================================== --}}

{{-- Modal Overlay --}}
<div id="profile-modal-overlay" class="fixed inset-0 bg-black/40 backdrop-blur-sm z-50 hidden opacity-0 transition-opacity duration-300">
    <div class="flex items-center justify-center min-h-screen p-4">

        {{-- ================================
            MODAL 1: CHANGE PASSWORD 
            (Input 3 passwords)
        ================================ --}}
        <div id="modal-change-password" class="modal-content bg-white rounded-3xl shadow-2xl max-w-md w-full p-8 transform scale-95 opacity-0 transition-all duration-300 hidden relative">
            <button type="button" class="modal-close-profile absolute top-4 right-4 text-gray-400 hover:text-gray-600 transition z-10">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>

            <div class="text-center mb-6">
                <h2 class="text-3xl font-bold text-gray-900 mb-2">Kata Sandi Baru</h2>
            </div>

            <form id="form-change-password" class="space-y-4">
                {{-- Password Lama --}}
                <div class="relative">
                    <input type="password" name="current_password" id="profile-password-current" placeholder="Kata Sandi Lama" required
                        class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none transition pr-12">
                    <button type="button" class="toggle-password-profile absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600"
                        data-target="profile-password-current">
                        <svg class="w-5 h-5 eye-open" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                        <svg class="w-5 h-5 eye-closed hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                        </svg>
                    </button>
                    <span class="text-red-500 text-xs hidden mt-1 block" data-error="current_password"></span>
                </div>

                {{-- Password Baru --}}
                <div class="relative">
                    <input type="password" name="new_password" id="profile-password-new" placeholder="Kata Sandi Baru" required
                        class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none transition pr-12">
                    <button type="button" class="toggle-password-profile absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600"
                        data-target="profile-password-new">
                        <svg class="w-5 h-5 eye-open" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                        <svg class="w-5 h-5 eye-closed hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                        </svg>
                    </button>
                    <span class="text-red-500 text-xs hidden mt-1 block" data-error="new_password"></span>
                </div>

                {{-- Konfirmasi Password Baru --}}
                <div class="relative">
                    <input type="password" name="new_password_confirmation" id="profile-password-confirm" placeholder="Konfirmasi Kata Sandi Baru" required
                        class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none transition pr-12">
                    <button type="button" class="toggle-password-profile absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600"
                        data-target="profile-password-confirm">
                        <svg class="w-5 h-5 eye-open" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                        <svg class="w-5 h-5 eye-closed hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                        </svg>
                    </button>
                    <span class="text-red-500 text-xs hidden mt-1 block" data-error="new_password_confirmation"></span>
                </div>

                <p class="text-center text-gray-600 text-sm mt-4">Silahkan konfirmasi untuk melanjutkan</p>

                <button type="submit" class="w-full py-3 bg-blue-500 text-white rounded-full font-semibold hover:bg-blue-600 transition disabled:opacity-50 disabled:cursor-not-allowed">
                    <span class="btn-text">Konfirmasi</span>
                    <span class="btn-loading hidden">
                        <svg class="animate-spin h-5 w-5 mx-auto" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </span>
                </button>
            </form>
        </div>

        {{-- ================================
            MODAL 2: OTP VERIFICATION
            (4 digit input boxes)
        ================================ --}}
        <div id="modal-change-password-otp" class="modal-content bg-white rounded-3xl shadow-2xl max-w-md w-full p-10 transform scale-95 opacity-0 transition-all duration-300 hidden relative">
            <button type="button" class="modal-close-profile absolute top-4 right-4 text-gray-400 hover:text-gray-600 transition z-10">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>

            <div class="text-center mb-8">
                {{-- Title at the top --}}
                <h2 class="text-3xl font-bold text-gray-900 mb-6">Verifikasi Kode</h2>
                
                {{-- Image below title --}}
                <div class="flex justify-center mb-6">
                    <img src="{{ asset('User/img/elemen/verifff.png') }}" alt="Verification Icon" class="h-48 w-auto">
                </div>

                {{-- Subtitles --}}
                <p class="text-lg font-semibold text-gray-800 mb-2">Masukkan Kode Untuk Melanjutkan</p>
                <p class="text-sm text-gray-500">Silahkan masukkan kode konfirmasi yang anda terima</p>
            </div>

            <form id="form-change-password-otp">
                {{-- 4 OTP Input Boxes --}}
                <div class="flex justify-center gap-4 mb-8">
                    <input type="text" maxlength="1" class="profile-otp-input w-16 h-16 text-center text-2xl font-bold border border-gray-300 rounded-2xl focus:border-blue-500 focus:ring-4 focus:ring-blue-100 outline-none transition bg-white shadow-sm" data-index="0">
                    <input type="text" maxlength="1" class="profile-otp-input w-16 h-16 text-center text-2xl font-bold border border-gray-300 rounded-2xl focus:border-blue-500 focus:ring-4 focus:ring-blue-100 outline-none transition bg-white shadow-sm" data-index="1">
                    <input type="text" maxlength="1" class="profile-otp-input w-16 h-16 text-center text-2xl font-bold border border-gray-300 rounded-2xl focus:border-blue-500 focus:ring-4 focus:ring-blue-100 outline-none transition bg-white shadow-sm" data-index="2">
                    <input type="text" maxlength="1" class="profile-otp-input w-16 h-16 text-center text-2xl font-bold border border-gray-300 rounded-2xl focus:border-blue-500 focus:ring-4 focus:ring-blue-100 outline-none transition bg-white shadow-sm" data-index="3">
                </div>

                <button type="submit" class="w-full py-4 bg-blue-500 text-white rounded-full font-bold text-lg hover:bg-blue-600 transition disabled:opacity-50 disabled:cursor-not-allowed shadow-lg hover:shadow-xl mb-6">
                    <span class="btn-text">Konfirmasi</span>
                    <span class="btn-loading hidden">
                        <svg class="animate-spin h-5 w-5 mx-auto" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </span>
                </button>

                <div class="text-center">
                    <p class="text-sm text-gray-600">
                        Belum Terima Kode? 
                        <button type="button" id="btn-resend-profile-otp" class="text-blue-500 hover:text-blue-600 hover:underline font-bold transition">
                            Kirim Ulang Kode
                        </button>
                    </p>
                </div>
            </form>
        </div>

        {{-- ================================
            MODAL 3: SUCCESS MESSAGE
        ================================ --}}
        <div id="modal-change-password-success" class="modal-content bg-white rounded-3xl shadow-2xl max-w-md w-full p-10 transform scale-95 opacity-0 transition-all duration-300 hidden relative">
            <div class="text-center">
                {{-- Title at the top --}}
                <h2 class="text-3xl font-bold text-gray-900 mb-8">Berhasil Diperbarui</h2>
                
                {{-- iSewa Logo below title --}}
                <div class="flex justify-center mb-8">
                    <img src="{{ asset('User/img/logo/iSewaT.png') }}" alt="iSewa Logo" class="h-32 w-auto object-contain">
                </div>

                <p class="text-lg font-semibold text-gray-800 mb-2">Kata Sandi Telah Diperbarui</p>
                <p class="text-sm text-gray-500 mb-8">Silahkan konfirmasi untuk melanjutkan</p>

                <button type="button" id="btn-confirm-change-password-success" class="w-full py-4 bg-blue-500 text-white rounded-full font-bold text-lg hover:bg-blue-600 transition shadow-lg hover:shadow-xl">
                    Konfirmasi
                </button>
            </div>
        </div>

    </div>
</div>

<style>
    /* Modal Animation */
    #profile-modal-overlay.show {
        opacity: 1;
    }

    #profile-modal-overlay.show .modal-content {
        transform: scale(1);
        opacity: 1;
    }

    /* OTP Input Animation */
    .profile-otp-input:focus {
        animation: pulse 0.3s ease-in-out;
    }

    @keyframes pulse {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.05); }
    }
</style>