{{--
    ADMIN PROFILE MODALS (Dashboard Admin Theme)
    Preserve all IDs, input names, classes, and OTP flow.
--}}

<style>
    .profile-modal .modal-dialog {
        max-width: 420px;
    }

    .profile-modal .modal-content {
        border: none;
        border-radius: 18px;
        box-shadow: 0 18px 48px rgba(15, 23, 42, 0.18);
        background-color: #ffffff;
        position: relative;
        overflow: hidden;
    }

    .profile-modal .btn-close-custom {
        position: absolute;
        top: 1rem;
        right: 1rem;
        width: 34px;
        height: 34px;
        border-radius: 10px;
        background-color: #f1f5f9;
        border: none;
        color: #64748b;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.18s ease;
        z-index: 1060;
        cursor: pointer;
    }

    .profile-modal .btn-close-custom:hover {
        background-color: #e2e8f0;
        color: #0f172a;
    }

    .profile-modal .form-label {
        font-size: 0.78rem;
        font-weight: 600;
        color: #334155;
        margin-bottom: 0.4rem;
        display: block;
    }

    .profile-modal .form-control {
        display: block;
        width: 100%;
        padding: 0.62rem 2.75rem 0.62rem 0.9rem;
        font-size: 0.92rem;
        color: #0f172a;
        background-color: #ffffff;
        border: 1px solid #dbe2ea;
        border-radius: 10px;
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }

    .profile-modal .form-control:focus {
        border-color: #0d6efd;
        outline: 0;
        box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.14);
    }

    .profile-modal .input-wrapper {
        position: relative;
    }

    .profile-modal .input-group-text.toggle-password {
        position: absolute;
        right: 2px;
        top: 2px;
        bottom: 2px;
        background: transparent;
        border: none;
        z-index: 20;
        display: flex;
        align-items: center;
        padding: 0 0.75rem;
        cursor: pointer;
        color: #94a3b8;
        transition: color 0.18s ease;
    }

    .profile-modal .input-group-text.toggle-password:hover {
        color: #0d6efd;
    }

    .profile-modal .btn-primary-custom {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        font-weight: 600;
        color: #ffffff;
        background-color: #0d6efd;
        border: none;
        padding: 0.65rem 1.25rem;
        font-size: 0.92rem;
        border-radius: 999px;
        transition: all 0.18s ease;
        box-shadow: 0 4px 12px rgba(13, 110, 253, 0.25);
        width: 100%;
        cursor: pointer;
    }

    .profile-modal .btn-primary-custom:hover {
        background-color: #0b5ed7;
        transform: translateY(-1px);
    }

    .profile-modal .btn-primary-custom:disabled {
        opacity: 0.7;
        transform: none;
    }

    /* OTP Box Styling */
    .otp-input-custom {
        width: 3.35rem;
        height: 3.5rem;
        font-size: 1.5rem;
        font-weight: 700;
        text-align: center;
        border: 1.5px solid #cbd5e1;
        border-radius: 12px;
        color: #0d6efd;
        transition: all 0.18s ease;
        background: #f8fafc;
    }

    .otp-input-custom:focus {
        background: #ffffff;
        border-color: #0d6efd;
        box-shadow: 0 0 0 0.22rem rgba(13, 110, 253, 0.16);
        outline: none;
    }

    .otp-input-custom.filled {
        background: #eff6ff;
        border-color: #0d6efd;
    }

    @keyframes checkmark {
        0% { transform: scale(0); opacity: 0; }
        50% { transform: scale(1.15); }
        100% { transform: scale(1); opacity: 1; }
    }

    .success-icon-animate {
        animation: checkmark 0.45s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards;
    }
</style>

{{-- Modal 1: Ubah Kata Sandi --}}
<div class="modal fade profile-modal" id="changePasswordModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <button type="button" class="btn-close-custom" data-bs-dismiss="modal" aria-label="Tutup">
                <i class="bx bx-x fs-4"></i>
            </button>

            <div class="modal-body p-4">
                <div class="text-center mb-4 pt-1">
                    <div class="mx-auto mb-3 rounded-4 bg-label-primary d-flex align-items-center justify-content-center" style="width: 56px; height: 56px;">
                        <i class="bx bx-lock-open-alt fs-2 text-primary"></i>
                    </div>
                    <h5 class="fw-bold text-dark mb-1">Ubah Kata Sandi</h5>
                    <p class="text-muted small mb-0">
                        Masukkan kata sandi lama dan buat kata sandi baru minimal 12 karakter.
                    </p>
                </div>

                <form id="changePasswordForm">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label" for="current_password">Kata Sandi Lama</label>
                        <div class="input-wrapper position-relative">
                            <input type="password" class="form-control" id="current_password" name="current_password" placeholder="Masukkan kata sandi saat ini" required>
                            <span class="input-group-text toggle-password">
                                <i class="bx bx-show fs-5"></i>
                            </span>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="form-label mb-0" for="new_password">Kata Sandi Baru</label>
                            <small class="text-muted" style="font-size: 0.72rem;">Min. 12 karakter</small>
                        </div>
                        <div class="input-wrapper position-relative">
                            <input type="password" class="form-control" id="new_password" name="new_password" placeholder="Buat kata sandi baru" required>
                            <span class="input-group-text toggle-password">
                                <i class="bx bx-show fs-5"></i>
                            </span>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label" for="new_password_confirmation">Konfirmasi Kata Sandi Baru</label>
                        <div class="input-wrapper position-relative">
                            <input type="password" class="form-control" id="new_password_confirmation" name="new_password_confirmation" placeholder="Ulangi kata sandi baru" required>
                            <span class="input-group-text toggle-password">
                                <i class="bx bx-show fs-5"></i>
                            </span>
                        </div>
                    </div>

                    <button type="button" id="confirmChangePasswordBtn" class="btn-primary-custom">
                        <i class="bx bx-mail-send"></i>
                        <span>Kirim Kode OTP &amp; Lanjutkan</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- Modal 2: OTP Verification --}}
<div class="modal fade profile-modal" id="otpVerificationModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content text-center">
            <button type="button" class="btn-close-custom" data-bs-dismiss="modal" aria-label="Tutup">
                <i class="bx bx-x fs-4"></i>
            </button>

            <div class="modal-body p-4">
                <div class="mb-3 pt-1">
                    <div class="mx-auto bg-label-primary rounded-4 d-flex align-items-center justify-content-center" style="width: 64px; height: 64px;">
                        <i class="bx bx-shield-quarter text-primary" style="font-size: 2rem;"></i>
                    </div>
                </div>

                <h5 class="fw-bold text-dark mb-1">Verifikasi Kode OTP</h5>
                <p class="text-muted small mb-4">
                    Kami telah mengirimkan 4 digit kode verifikasi ke email terdaftar Anda. Masukkan kode di bawah ini.
                </p>

                <form id="otpForm">
                    <div class="d-flex justify-content-center align-items-center gap-2.5 mb-4">
                        <input type="text" inputmode="numeric" maxlength="1" id="otp_1" class="otp-input otp-input-custom" autocomplete="off" autofocus>
                        <input type="text" inputmode="numeric" maxlength="1" id="otp_2" class="otp-input otp-input-custom" autocomplete="off">
                        <input type="text" inputmode="numeric" maxlength="1" id="otp_3" class="otp-input otp-input-custom" autocomplete="off">
                        <input type="text" inputmode="numeric" maxlength="1" id="otp_4" class="otp-input otp-input-custom" autocomplete="off">
                    </div>

                    <div id="otpDebugDisplay" class="alert alert-secondary small py-1" style="display: none; font-size: 0.75rem;">
                        Code: <strong id="otpDebugCode"></strong>
                    </div>

                    <button type="button" id="verifyOtpBtn" class="btn-primary-custom mb-3">
                        <i class="bx bx-check-shield"></i>
                        <span>Verifikasi &amp; Simpan</span>
                    </button>

                    <p class="text-muted mb-0 small">
                        Belum menerima kode?
                        <a href="#" id="resendOtpBtn" class="text-primary fw-semibold text-decoration-none">
                            Kirim Ulang OTP
                        </a>
                    </p>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- Modal 3: Success --}}
<div class="modal fade profile-modal" id="successModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content text-center">
            <div class="modal-body p-4">
                <div class="mb-3 pt-2 success-icon-animate">
                    <div class="mx-auto rounded-circle bg-label-success d-flex align-items-center justify-content-center" style="width: 72px; height: 72px;">
                        <i class="bx bx-check text-success" style="font-size: 2.75rem;"></i>
                    </div>
                </div>

                <h5 class="fw-bold text-dark mb-1">Kata Sandi Diperbarui!</h5>
                <p class="text-muted small mb-4">
                    Kata sandi akun administrator Anda telah berhasil diperbarui dan diamankan.
                </p>

                <button type="button" id="closeSuccessModalBtn" class="btn-primary-custom" style="background-color: #198754; box-shadow: 0 4px 12px rgba(25, 135, 84, 0.25);">
                    OK, Mengerti
                </button>
            </div>
        </div>
    </div>
</div>
