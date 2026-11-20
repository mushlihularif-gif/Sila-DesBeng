<script>
    // Global function to open change password modal
    window.openChangePasswordModal = function() {
        const modalEl = document.getElementById('changePasswordModal');
        if (!modalEl) return;
        const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
        modal.show();
    };

    document.addEventListener('DOMContentLoaded', function() {
        function notifyToast(type, title, message) {
            if (typeof showSiladesBengToast === 'function') {
                showSiladesBengToast(type, title, message);
            } else {
                alert(title + ': ' + message);
            }
        }

        function extractErrorMessage(data, fallback) {
            if (data && data.message) return data.message;
            if (data && data.errors) {
                const firstKey = Object.keys(data.errors)[0];
                if (firstKey && Array.isArray(data.errors[firstKey]) && data.errors[firstKey][0]) {
                    return data.errors[firstKey][0];
                }
            }
            return fallback;
        }

        // Toggle Password Visibility
        document.body.addEventListener('click', function(e) {
            const toggleBtn = e.target.closest('.toggle-password');
            if (toggleBtn) {
                e.preventDefault();
                e.stopPropagation();

                const inputWrapper = toggleBtn.closest('.input-wrapper');
                const input = inputWrapper ? inputWrapper.querySelector('input') : null;
                const icon = toggleBtn.querySelector('i');

                if (input && icon) {
                    if (input.type === 'password') {
                        input.type = 'text';
                        icon.classList.remove('bx-show');
                        icon.classList.add('bx-hide');
                    } else {
                        input.type = 'password';
                        icon.classList.remove('bx-hide');
                        icon.classList.add('bx-show');
                    }
                    input.focus();
                }
            }
        });

        // Manual Close Button Handler
        document.body.addEventListener('click', function(e) {
            const closeBtn = e.target.closest('.btn-close-custom');
            if (closeBtn) {
                const modalEl = closeBtn.closest('.modal');
                if (modalEl) {
                    const modalInstance = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
                    modalInstance.hide();
                }
            }
        });

        // Submit Change Password
        const confirmPasswordBtn = document.getElementById('confirmChangePasswordBtn');
        if (confirmPasswordBtn) {
            confirmPasswordBtn.addEventListener('click', function() {
                const form = document.getElementById('changePasswordForm');
                const formData = new FormData(form);
                const button = this;
                const originalText = button.innerHTML;

                const currentPassword = formData.get('current_password');
                const newPassword = formData.get('new_password');
                const confirmPassword = formData.get('new_password_confirmation');

                if (!currentPassword || !newPassword || !confirmPassword) {
                    notifyToast('warning', 'Perhatian', 'Semua kolom kata sandi wajib diisi.');
                    return;
                }

                if (newPassword.length < 12) {
                    notifyToast('warning', 'Perhatian', 'Kata sandi baru minimal 12 karakter.');
                    return;
                }

                if (newPassword !== confirmPassword) {
                    notifyToast('error', 'Gagal', 'Konfirmasi kata sandi baru tidak cocok.');
                    return;
                }

                button.disabled = true;
                button.innerHTML = '<i class="bx bx-loader-alt bx-spin"></i><span>Memproses...</span>';

                fetch("{{ route('admin.profile.change-password') }}", {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                        'Accept': 'application/json'
                    },
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data && data.success) {
                        const cpModal = bootstrap.Modal.getInstance(document.getElementById('changePasswordModal'));
                        if (cpModal) cpModal.hide();

                        const otpModal = bootstrap.Modal.getOrCreateInstance(document.getElementById('otpVerificationModal'));
                        otpModal.show();

                        form.reset();
                    } else {
                        notifyToast('error', 'Gagal', extractErrorMessage(data, 'Gagal memproses perubahan kata sandi.'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    notifyToast('error', 'Gagal', 'Terjadi kesalahan jaringan. Silakan coba lagi.');
                })
                .finally(() => {
                    button.disabled = false;
                    button.innerHTML = originalText;
                });
            });
        }

        // OTP Input Handling
        const otpInputs = document.querySelectorAll('.otp-input');

        otpInputs.forEach((input, index) => {
            input.addEventListener('input', function() {
                const value = this.value;
                if (!/^\d*$/.test(value)) {
                    this.value = '';
                    return;
                }

                this.classList.toggle('filled', Boolean(value));

                if (value && index < otpInputs.length - 1) {
                    otpInputs[index + 1].focus();
                }
            });

            input.addEventListener('keydown', function(e) {
                if (e.key === 'Backspace' && !this.value && index > 0) {
                    otpInputs[index - 1].focus();
                }
            });

            input.addEventListener('paste', function(e) {
                e.preventDefault();
                const pastedData = e.clipboardData.getData('text');
                const digits = pastedData.replace(/\D/g, '').split('').slice(0, 4);

                digits.forEach((digit, i) => {
                    if (otpInputs[i]) {
                        otpInputs[i].value = digit;
                        otpInputs[i].classList.add('filled');
                    }
                });

                if (digits.length > 0) {
                    const lastIndex = Math.min(digits.length, otpInputs.length - 1);
                    otpInputs[lastIndex].focus();
                }
            });
        });

        const otpModalEl = document.getElementById('otpVerificationModal');
        if (otpModalEl) {
            otpModalEl.addEventListener('shown.bs.modal', function() {
                if (otpInputs[0]) otpInputs[0].focus();
            });
        }

        // Verify OTP
        const verifyOtpBtn = document.getElementById('verifyOtpBtn');
        if (verifyOtpBtn) {
            verifyOtpBtn.addEventListener('click', function() {
                const otp = Array.from(otpInputs).map(input => input.value).join('');
                const button = this;
                const originalText = button.innerHTML;

                if (otp.length !== 4) {
                    notifyToast('warning', 'Perhatian', 'Masukkan 4 digit kode OTP.');
                    return;
                }

                button.disabled = true;
                button.innerHTML = '<i class="bx bx-loader-alt bx-spin"></i><span>Memverifikasi...</span>';

                fetch("{{ route('admin.profile.verify-otp') }}", {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ otp: otp })
                })
                .then(response => response.json())
                .then(data => {
                    if (data && data.success) {
                        const otpModal = bootstrap.Modal.getInstance(document.getElementById('otpVerificationModal'));
                        if (otpModal) otpModal.hide();

                        const successModal = bootstrap.Modal.getOrCreateInstance(document.getElementById('successModal'));
                        successModal.show();

                        otpInputs.forEach(input => {
                            input.value = '';
                            input.classList.remove('filled');
                        });
                    } else {
                        notifyToast('error', 'Gagal', extractErrorMessage(data, 'Kode OTP tidak valid.'));
                        otpInputs.forEach(input => {
                            input.value = '';
                            input.classList.remove('filled');
                        });
                        if (otpInputs[0]) otpInputs[0].focus();
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    notifyToast('error', 'Gagal', 'Terjadi kesalahan jaringan.');
                })
                .finally(() => {
                    button.disabled = false;
                    button.innerHTML = originalText;
                });
            });
        }

        // Resend OTP
        const resendOtpBtn = document.getElementById('resendOtpBtn');
        if (resendOtpBtn) {
            resendOtpBtn.addEventListener('click', function(e) {
                e.preventDefault();
                const link = this;
                const originalText = link.innerHTML;

                link.innerHTML = '<i class="bx bx-loader-alt bx-spin"></i> Mengirim...';
                link.style.pointerEvents = 'none';

                fetch("{{ route('admin.profile.resend-otp') }}", {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data && data.success) {
                        notifyToast('success', 'Berhasil', data.message || 'Kode OTP baru telah dikirim.');
                        otpInputs.forEach(input => {
                            input.value = '';
                            input.classList.remove('filled');
                        });
                        if (otpInputs[0]) otpInputs[0].focus();
                    } else {
                        notifyToast('error', 'Gagal', extractErrorMessage(data, 'Gagal mengirim ulang OTP.'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    notifyToast('error', 'Gagal', 'Terjadi kesalahan jaringan.');
                })
                .finally(() => {
                    link.innerHTML = originalText;
                    link.style.pointerEvents = 'auto';
                });
            });
        }

        // Close Success Modal
        const closeSuccessBtn = document.getElementById('closeSuccessModalBtn');
        if (closeSuccessBtn) {
            closeSuccessBtn.addEventListener('click', function() {
                window.location.reload();
            });
        }

        // --- Avatar Upload & Reset Logic ---
        const fileInput = document.querySelector('.account-file-input');
        const resetFileInput = document.querySelector('.account-image-reset');
        const deleteAvatarInput = document.getElementById('deleteAvatarInput');
        const avatarTrigger = document.querySelector('.avatar-trigger');

        if (avatarTrigger && fileInput) {
            avatarTrigger.addEventListener('click', function() {
                fileInput.click();
            });
        }

        if (fileInput) {
            fileInput.onchange = () => {
                if (fileInput.files && fileInput.files[0]) {
                    if (deleteAvatarInput) deleteAvatarInput.value = '0';

                    if (typeof initGlobalCropper === 'function') {
                        initGlobalCropper(fileInput, 'uploadedAvatar', NaN, true);
                    } else {
                        const reader = new FileReader();
                        reader.onload = function(ev) {
                            const currentAvatar = document.getElementById('uploadedAvatar');
                            if (currentAvatar) {
                                if (currentAvatar.tagName === 'IMG') {
                                    currentAvatar.src = ev.target.result;
                                } else {
                                    currentAvatar.outerHTML = '<img src="' + ev.target.result + '" alt="Foto Profil" class="avatar-preview rounded-circle" id="uploadedAvatar" />';
                                }
                            }
                        };
                        reader.readAsDataURL(fileInput.files[0]);
                    }
                }
            };

            if (resetFileInput) {
                resetFileInput.onclick = () => {
                    fileInput.value = '';
                    if (deleteAvatarInput) deleteAvatarInput.value = '1';

                    const defaultPlaceholder = `
                        <div class="avatar-preview avatar-default rounded-circle d-flex align-items-center justify-content-center" id="uploadedAvatar">
                            <svg viewBox="0 0 24 24" fill="currentColor" style="width: 52px; height: 52px; color: #ffffff;">
                                <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                            </svg>
                        </div>
                    `;

                    const currentAvatar = document.getElementById('uploadedAvatar');
                    if (currentAvatar && currentAvatar.tagName === 'IMG') {
                        currentAvatar.outerHTML = defaultPlaceholder;
                    }
                };
            }
        }
    });
</script>
