{{-- 
    ADMIN PROFILE MODALS (Sneat Design - Modern 2025)
    Fixed: Close Button & Toggle Password Z-Index Issues
--}}

{{-- Modal Styles (Sneat Aesthetics) --}}
<style>
    @import url('https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap');

    .profile-modal {
        font-family: 'Public Sans', sans-serif;
    }

    .profile-modal .modal-content {
        border: none;
        border-radius: 0.75rem; /* Sneat standard rounded */
        box-shadow: 0 0.25rem 1rem rgba(161, 172, 184, 0.45);
        background-color: #fff;
        position: relative; /* Ensure absolute children position correctly */
    }

    .profile-modal .modal-header {
        padding: 1.5rem 1.5rem 0.5rem;
        border-bottom: none;
        position: relative;
    }

    /* Fixed Close Button */
    .profile-modal .btn-close-custom {
        position: absolute;
        top: 1.25rem;
        right: 1.25rem;
        width: 2rem;
        height: 2rem;
        border-radius: 0.375rem;
        background-color: rgba(67, 89, 113, 0.05);
        border: none;
        color: #697a8d;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s ease-in-out;
        z-index: 1060; /* Higher than Modal (1055) */
        cursor: pointer;
    }

    .profile-modal .btn-close-custom:hover {
        background-color: rgba(67, 89, 113, 0.15);
        transform: scale(1.05);
        color: #566a7f;
    }

    .profile-modal .modal-title {
        font-weight: 600;
        font-size: 1.375rem;
        color: #566a7f;
        text-align: center;
        width: 100%;
    }

    .profile-modal .modal-body {
        padding: 1.5rem;
    }

    .profile-modal .form-label {
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-weight: 500;
        color: #566a7f;
        margin-bottom: 0.5rem;
        display: block;
    }

    .profile-modal .form-control {
        display: block;
        width: 100%;
        padding: 0.6rem 2.875rem 0.6rem 0.875rem; /* Right padding for eye icon */
        font-size: 0.9375rem;
        font-weight: 400;
        line-height: 1.53;
        color: #697a8d;
        background-color: #fff;
        background-clip: padding-box;
        border: 1px solid #d9dee3;
        appearance: none;
        border-radius: 0.375rem;
        transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
    }

    .profile-modal .form-control:focus {
        color: #697a8d;
        background-color: #fff;
        border-color: #696cff;
        outline: 0;
        box-shadow: 0 0 0.25rem 0.05rem rgba(105, 108, 255, 0.1);
    }

    .profile-modal .input-wrapper {
        position: relative;
    }

    .profile-modal .input-group-text.toggle-password {
        position: absolute;
        right: 1px;
        top: 1px;
        bottom: 1px;
        background: transparent;
        border: none;
        z-index: 20; /* Ensure clickable over input */
        display: flex;
        align-items: center;
        padding: 0 0.875rem;
        cursor: pointer;
        color: #b4bdc6;
        transition: color 0.2s;
    }

    .profile-modal .input-group-text.toggle-password:hover {
        color: #696cff;
    }

    .profile-modal .btn-primary-custom {
        display: inline-block;
        font-weight: 500;
        line-height: 1.53;
        color: #fff;
        text-align: center;
        vertical-align: middle;
        cursor: pointer;
        user-select: none;
        background-color: #696cff;
        border: 1px solid #696cff;
        padding: 0.6rem 1.25rem;
        font-size: 0.9375rem;
        border-radius: 0.375rem;
        transition: all 0.2s ease-in-out;
        box-shadow: 0 0.125rem 0.25rem 0 rgba(105, 108, 255, 0.4);
        width: 100%;
        border: none; /* Fix default border */
    }

    .profile-modal .btn-primary-custom:hover {
        background-color: #5f61e6;
        border-color: #5f61e6;
        transform: translateY(-1px);
    }

    /* OTP Box Styling */
    .otp-input-custom {
        width: 3rem;
        height: 3rem;
        font-size: 1.5rem;
        font-weight: 600;
        text-align: center;
        border: 1px solid #d9dee3;
        border-radius: 0.375rem;
        color: #696cff;
        transition: all 0.2s;
        background: #f5f5f9;
    }

    .otp-input-custom:focus {
        background: #fff;
        border-color: #696cff;
        box-shadow: 0 0 0.25rem 0.05rem rgba(105, 108, 255, 0.1);
        transform: translateY(-2px);
    }

    .otp-input-custom.filled {
        background: #fff;
        border-color: #696cff;
        box-shadow: 0 0 0 1px #696cff;
    }

    /* Success Animation */
    @keyframes checkmark {
        0% { transform: scale(0); opacity: 0; }
        50% { transform: scale(1.2); }
        100% { transform: scale(1); opacity: 1; }
    }
    
    .success-icon-animate {
        animation: checkmark 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards;
    }
</style>

{{-- Modal 1: Ubah Kata Sandi --}}
<div class="modal fade profile-modal" id="changePasswordModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-sm"> 
        <div class="modal-content">
            {{-- Close Button with high z-index --}}
            <button type="button" class="btn-close-custom" data-bs-dismiss="modal" aria-label="Close">
                <i class='bx bx-x' style="font-size: 1.5rem;"></i>
            </button>
            
            <div class="modal-header">
                <h5 class="modal-title">Ubah Kata Sandi</h5>
            </div>

            <div class="modal-body">
                <form id="changePasswordForm">
                    @csrf
                    
                    <div class="mb-3">
                        <label class="form-label" for="current_password">Kata Sandi Lama</label>
                        <div class="input-wrapper position-relative">
                            <input type="password" class="form-control" id="current_password" name="current_password" placeholder="············" required>
                            <span class="input-group-text toggle-password">
                                <i class="bx bx-show" style="font-size: 1.25rem;"></i>
                            </span>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="form-label mb-0" for="new_password">Kata Sandi Baru</label>
                            <small class="text-muted" style="font-size: 0.7rem;">(Min. 8 Karakter)</small>
                        </div>
                        <div class="input-wrapper position-relative">
                            <input type="password" class="form-control" id="new_password" name="new_password" placeholder="············" required>
                            <span class="input-group-text toggle-password">
                                <i class="bx bx-show" style="font-size: 1.25rem;"></i>
                            </span>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label" for="new_password_confirmation">Konfirmasi</label>
                        <div class="input-wrapper position-relative">
                            <input type="password" class="form-control" id="new_password_confirmation" name="new_password_confirmation" placeholder="············" required>
                            <span class="input-group-text toggle-password">
                                <i class="bx bx-show" style="font-size: 1.25rem;"></i>
                            </span>
                        </div>
                    </div>

                    <button type="button" id="confirmChangePasswordBtn" class="btn-primary-custom mb-3">
                        Simpan Perubahan
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- Modal 2: OTP Verification --}}
<div class="modal fade profile-modal" id="otpVerificationModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content text-center overflow-hidden">
            <button type="button" class="btn-close-custom" data-bs-dismiss="modal" aria-label="Close">
                <i class='bx bx-x' style="font-size: 1.5rem;"></i>
            </button>

            <div class="modal-body pt-5 pb-4">
                <div class="mb-4">
                    <div class="mx-auto bg-label-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 80px; height: 80px; background-color: rgba(105, 108, 255, 0.16);">
                        <i class='bx bx-shield-quarter' style="font-size: 40px; color: #696cff;"></i>
                    </div>
                </div>

                <h4 class="mb-2" style="color: #566a7f; font-weight: 600;">Verifikasi 2 Langkah</h4>
                <p class="text-muted mb-4" style="font-size: 0.9rem;">
                    Kami mengirimkan kode verifikasi ke email/ponsel Anda. Masukkan kode tersebut di bawah.
                </p>

                <form id="otpForm">
                    <div class="d-flex justify-content-center align-items-center gap-2 mb-4">
                        <input type="text" maxlength="1" id="otp_1" class="otp-input otp-input-custom" autocomplete="off" autofocus>
                        <input type="text" maxlength="1" id="otp_2" class="otp-input otp-input-custom" autocomplete="off">
                        <input type="text" maxlength="1" id="otp_3" class="otp-input otp-input-custom" autocomplete="off">
                        <input type="text" maxlength="1" id="otp_4" class="otp-input otp-input-custom" autocomplete="off">
                    </div>
                    
                    <div id="otpDebugDisplay" class="alert alert-secondary small py-1" style="display: none; font-size: 0.75rem;">
                         Code: <strong id="otpDebugCode"></strong>
                    </div>

                    <button type="button" id="verifyOtpBtn" class="btn-primary-custom w-100 mb-3">
                        Verifikasi
                    </button>

                    <p class="text-muted mb-0" style="font-size: 0.85rem;">
                        Belum terima kode? 
                        <a href="#" id="resendOtpBtn" style="color: #696cff; font-weight: 600; text-decoration: none;">
                            Kirim Ulang
                        </a>
                    </p>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- Modal 3: Success --}}
<div class="modal fade profile-modal" id="successModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content text-center">
            
            <div class="modal-body pt-5 pb-4">
                <div class="mb-4 success-icon-animate">
                    <div class="mx-auto rounded-circle d-flex align-items-center justify-content-center" style="width: 80px; height: 80px; background-color: rgba(113, 221, 55, 0.16);">
                        <i class='bx bx-check' style="font-size: 48px; color: #71dd37;"></i>
                    </div>
                </div>

                <h4 class="mb-2" style="color: #566a7f; font-weight: 600;">Berhasil!</h4>
                <p class="text-muted mb-4" style="font-size: 0.9rem;">Kata sandi Anda telah berhasil diperbarui.</p>

                <button type="button" id="closeSuccessModalBtn" class="btn-primary-custom w-100" style="background-color: #71dd37; border-color: #71dd37; box-shadow: 0 0.125rem 0.25rem 0 rgba(113, 221, 55, 0.4);">
                    OK, Mengerti
                </button>
            </div>
        </div>
    </div>
</div>
