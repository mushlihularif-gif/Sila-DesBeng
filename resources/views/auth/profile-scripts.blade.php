<script>
document.addEventListener('DOMContentLoaded', function() {
    // ========================================
    // PROFILE PAGE JAVASCRIPT
    // ========================================

    const profileOverlay = document.getElementById('profile-modal-overlay');
    const modalChangePassword = document.getElementById('modal-change-password');
    const modalChangePasswordOtp = document.getElementById('modal-change-password-otp');
    const modalChangePasswordSuccess = document.getElementById('modal-change-password-success');

    // ========================================
    // UTILITY FUNCTIONS
    // ========================================
    function openProfileModal(modal) {
        document.querySelectorAll('#profile-modal-overlay .modal-content').forEach(m => {
            m.classList.add('hidden');
            m.classList.remove('scale-100', 'opacity-100');
        });

        profileOverlay.classList.remove('hidden');
        setTimeout(() => {
            profileOverlay.classList.add('show');
            modal.classList.remove('hidden');
            setTimeout(() => {
                modal.classList.add('scale-100', 'opacity-100');
            }, 50);
        }, 10);
    }

    function closeProfileModal() {
        profileOverlay.classList.remove('show');
        setTimeout(() => {
            profileOverlay.classList.add('hidden');
            document.querySelectorAll('#profile-modal-overlay .modal-content').forEach(m => {
                m.classList.add('hidden');
                m.classList.remove('scale-100', 'opacity-100');
            });
        }, 300);
    }

    function switchProfileModal(fromModal, toModal) {
        fromModal.classList.remove('scale-100', 'opacity-100');
        fromModal.classList.add('scale-95', 'opacity-0');
        
        setTimeout(() => {
            fromModal.classList.add('hidden');
            toModal.classList.remove('hidden');
            
            setTimeout(() => {
                toModal.classList.remove('scale-95', 'opacity-0');
                toModal.classList.add('scale-100', 'opacity-100');
            }, 50);
        }, 200);
    }

    function showProfileError(form, field, message) {
        const errorSpan = form.querySelector(`[data-error="${field}"]`);
        if (errorSpan) {
            errorSpan.textContent = message;
            errorSpan.classList.remove('hidden');
            
            // Auto hide setelah 5 detik
            setTimeout(() => {
                errorSpan.classList.add('opacity-0');
                setTimeout(() => {
                    errorSpan.classList.add('hidden');
                    errorSpan.classList.remove('opacity-0');
                }, 300);
            }, 5000);
        }
    }

    function clearProfileErrors(form) {
        form.querySelectorAll('[data-error]').forEach(span => {
            span.textContent = '';
            span.classList.add('hidden');
        });
    }

    function setProfileButtonLoading(button, loading) {
        const btnText = button.querySelector('.btn-text');
        const btnLoading = button.querySelector('.btn-loading');
        
        if (loading) {
            btnText.classList.add('hidden');
            btnLoading.classList.remove('hidden');
            button.disabled = true;
        } else {
            btnText.classList.remove('hidden');
            btnLoading.classList.add('hidden');
            button.disabled = false;
        }
    }

    function showToast(message, type = 'success') {
        const toast = document.createElement('div');
        toast.className = `fixed top-4 right-4 px-6 py-3 rounded-lg shadow-lg text-white z-[60] transform transition-all duration-300 translate-x-full ${
            type === 'success' ? 'bg-green-500' : 'bg-red-500'
        }`;
        toast.textContent = message;
        document.body.appendChild(toast);

        setTimeout(() => toast.classList.remove('translate-x-full'), 100);
        setTimeout(() => {
            toast.classList.add('translate-x-full');
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }

    // ========================================
    // MODAL TRIGGERS
    // ========================================
    const btnOpenChangePassword = document.getElementById('btn-open-change-password');
    if (btnOpenChangePassword) {
        btnOpenChangePassword.addEventListener('click', () => {
            openProfileModal(modalChangePassword);
        });
    }

    // Close buttons
    document.querySelectorAll('.modal-close-profile').forEach(btn => {
        btn.addEventListener('click', closeProfileModal);
    });

    profileOverlay?.addEventListener('click', function(e) {
        if (e.target === profileOverlay) closeProfileModal();
    });

    // ========================================
    // PASSWORD TOGGLE (Eye Icon)
    // ========================================
    document.querySelectorAll('.toggle-password-profile').forEach(btn => {
        btn.addEventListener('click', function() {
            const targetId = this.getAttribute('data-target');
            const input = document.getElementById(targetId);
            const eyeOpen = this.querySelector('.eye-open');
            const eyeClosed = this.querySelector('.eye-closed');

            if (input.type === 'password') {
                input.type = 'text';
                eyeOpen.classList.add('hidden');
                eyeClosed.classList.remove('hidden');
            } else {
                input.type = 'password';
                eyeOpen.classList.remove('hidden');
                eyeClosed.classList.add('hidden');
            }
        });
    });

    // ========================================
    // FORM: CHANGE PASSWORD (STEP 1)
    // ========================================
    const formChangePassword = document.getElementById('form-change-password');
    formChangePassword?.addEventListener('submit', async function(e) {
        e.preventDefault();
        clearProfileErrors(this);

        // Get form values
        const newPassword = document.getElementById('profile-password-new').value;
        const confirmPassword = document.getElementById('profile-password-confirm').value;

        // Validasi frontend: Password minimal 8 karakter
        if (newPassword.length < 8) {
            showProfileError(this, 'new_password', 'Password minimal 8 karakter');
            return;
        }

        // Validasi frontend: Password dan konfirmasi harus sama
        if (newPassword !== confirmPassword) {
            showProfileError(this, 'new_password_confirmation', 'Konfirmasi password tidak cocok');
            return;
        }

        const formData = new FormData(this);
        const submitBtn = this.querySelector('button[type="submit"]');
        setProfileButtonLoading(submitBtn, true);

        try {
            const response = await fetch('{{ route('profile.change-password') }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                },
                body: formData
            });

            const data = await response.json();

            if (response.ok) {
                showToast(data.message, 'success');
                
                // Pindah ke modal OTP
                setTimeout(() => {
                    switchProfileModal(modalChangePassword, modalChangePasswordOtp);
                    document.querySelector('.profile-otp-input[data-index="0"]')?.focus();
                }, 500);
            } else {
                if (data.errors) {
                    Object.keys(data.errors).forEach(field => {
                        showProfileError(this, field, data.errors[field][0]);
                    });
                } else {
                    showToast(data.message || 'Terjadi kesalahan', 'error');
                }
            }
        } catch (error) {
            console.error('Change password error:', error);
            showToast('Terjadi kesalahan', 'error');
        } finally {
            setProfileButtonLoading(submitBtn, false);
        }
    });

    // ========================================
    // OTP INPUT HANDLING
    // ========================================
    const profileOtpInputs = document.querySelectorAll('.profile-otp-input');
    
    profileOtpInputs.forEach((input, index) => {
        input.addEventListener('input', function(e) {
            const value = e.target.value;
            if (!/^\d*$/.test(value)) {
                e.target.value = '';
                return;
            }
            if (value.length === 1 && index < profileOtpInputs.length - 1) {
                profileOtpInputs[index + 1].focus();
            }
        });

        input.addEventListener('keydown', function(e) {
            if (e.key === 'Backspace' && !e.target.value && index > 0) {
                profileOtpInputs[index - 1].focus();
            }
        });

        input.addEventListener('paste', function(e) {
            e.preventDefault();
            const pasteData = e.clipboardData.getData('text').trim();
            if (/^\d{4}$/.test(pasteData)) {
                pasteData.split('').forEach((char, i) => {
                    if (profileOtpInputs[i]) profileOtpInputs[i].value = char;
                });
                profileOtpInputs[3].focus();
            }
        });
    });

    // ========================================
    // FORM: OTP VERIFICATION (STEP 2)
    // ========================================
    const formChangePasswordOtp = document.getElementById('form-change-password-otp');
    formChangePasswordOtp?.addEventListener('submit', async function(e) {
        e.preventDefault();

        const otp = Array.from(profileOtpInputs).map(input => input.value).join('');
        if (otp.length !== 4) {
            showToast('Kode OTP harus 4 digit', 'error');
            return;
        }

        const submitBtn = this.querySelector('button[type="submit"]');
        setProfileButtonLoading(submitBtn, true);

        try {
            const response = await fetch('{{ route('profile.verify-otp') }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ otp: otp })
            });

            const data = await response.json();

            if (response.ok) {
                showToast(data.message, 'success');
                switchProfileModal(modalChangePasswordOtp, modalChangePasswordSuccess);
            } else {
                showToast(data.message || 'Verifikasi gagal', 'error');
                profileOtpInputs.forEach(input => input.value = '');
                profileOtpInputs[0].focus();
            }
        } catch (error) {
            console.error('OTP verification error:', error);
            showToast('Terjadi kesalahan', 'error');
        } finally {
            setProfileButtonLoading(submitBtn, false);
        }
    });

    // ========================================
    // RESEND OTP
    // ========================================
    const btnResendProfileOtp = document.getElementById('btn-resend-profile-otp');
    btnResendProfileOtp?.addEventListener('click', function() {
        resendProfileOtp();
    });

    const btnSwitchProfileOtp = document.getElementById('btn-switch-profile-otp');
    let currentProfileOtpMethod = 'email';
    btnSwitchProfileOtp?.addEventListener('click', function() {
        const switchMethod = currentProfileOtpMethod === 'email' ? 'sms' : 'email';
        resendProfileOtp(switchMethod);
    });

    async function resendProfileOtp(switchMethod = null) {
        if (btnResendProfileOtp) btnResendProfileOtp.disabled = true;
        if (btnSwitchProfileOtp) btnSwitchProfileOtp.disabled = true;

        try {
            const bodyData = {};
            if (switchMethod) {
                bodyData.switch_method = switchMethod;
            }

            const response = await fetch('{{ route('profile.resend-otp') }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(bodyData)
            });

            const data = await response.json();

            if (response.ok) {
                showToast(data.message, 'success');
                profileOtpInputs.forEach(input => input.value = '');
                profileOtpInputs[0].focus();
                
                if (switchMethod) {
                    currentProfileOtpMethod = switchMethod;
                    if (btnSwitchProfileOtp) {
                        btnSwitchProfileOtp.textContent = 'Kirim OTP melalui ' + (currentProfileOtpMethod === 'email' ? 'No. Telepon' : 'Email');
                    }
                }
            } else {
                showToast(data.message || 'Gagal kirim OTP', 'error');
            }
        } catch (error) {
            console.error('Error:', error);
            showToast('Terjadi kesalahan sistem', 'error');
        } finally {
            setTimeout(() => {
                if (btnResendProfileOtp) btnResendProfileOtp.disabled = false;
                if (btnSwitchProfileOtp) btnSwitchProfileOtp.disabled = false;
            }, 30000);
        }
    }

    // ========================================
    // SUCCESS CONFIRMATION
    // ========================================
    const btnConfirmSuccess = document.getElementById('btn-confirm-change-password-success');
    btnConfirmSuccess?.addEventListener('click', function() {
        closeProfileModal();
        window.location.reload(); // Refresh halaman
    });

    // ========================================
    // AVATAR PREVIEW
    // ========================================
    const profileInput = document.getElementById('profile-input');
    const avatarPreview = document.getElementById('avatar-preview');
    const avatarPlaceholder = document.getElementById('avatar-placeholder');

    profileInput?.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (!file) return;

        // Validasi type
        const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png'];
        if (!allowedTypes.includes(file.type)) {
            showToast('Format file harus JPG, JPEG, atau PNG', 'error');
            this.value = '';
            return;
        }

        // Validasi size (max 2MB)
        if (file.size > 2 * 1024 * 1024) {
            showToast('Ukuran file maksimal 2MB', 'error');
            this.value = '';
            return;
        }

        // Preview
        const reader = new FileReader();
        reader.onload = function(e) {
            if (avatarPreview) {
                avatarPreview.src = e.target.result;
                avatarPreview.classList.remove('hidden');
            }
            if (avatarPlaceholder) {
                avatarPlaceholder.classList.add('hidden');
            }
        };
        reader.readAsDataURL(file);
    });

    // ========================================
    // AUTO-HIDE SUCCESS ALERT
    // ========================================
    const successAlert = document.getElementById('success-alert');
    if (successAlert) {
        setTimeout(() => {
            successAlert.style.opacity = '0';
            setTimeout(() => successAlert.remove(), 300);
        }, 3000);
    }
});
</script>