<script>
    // Global function to open change password modal
    window.openChangePasswordModal = function() {
        const modal = new bootstrap.Modal(document.getElementById('changePasswordModal'));
        modal.show();
    };

    document.addEventListener('DOMContentLoaded', function() {
        console.log('Admin Profile Script Loaded');

        // Toggle Password Visibility (Robust)
        document.body.addEventListener('click', function(e) {
            // Check if clicked element is .toggle-password or visible child
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
                    // Focus back to input
                    input.focus();
                }
            }
        });

        // Manual Close Button Handler (Robust)
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
        document.getElementById('confirmChangePasswordBtn').addEventListener('click', function() {
            const form = document.getElementById('changePasswordForm');
            const formData = new FormData(form);
            const button = this;
            const originalText = button.innerHTML;

            // Client-side validation
            const currentPassword = formData.get('current_password');
            const newPassword = formData.get('new_password');
            const confirmPassword = formData.get('new_password_confirmation');

            if (!currentPassword || !newPassword || !confirmPassword) {
                alert('Semua field harus diisi');
                return;
            }

            if (newPassword.length < 8) {
                alert('Kata sandi baru minimal 8 karakter');
                return;
            }

            if (newPassword !== confirmPassword) {
                alert('Konfirmasi kata sandi tidak cocok');
                return;
            }

            // Show loading
            button.disabled = true;
            button.innerHTML = '<i class="bx bx-loader-alt bx-spin me-1"></i>Memproses...';

            // AJAX Request
            fetch("{{ route('admin.profile.change-password') }}", {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Hide change password modal
                    bootstrap.Modal.getInstance(document.getElementById('changePasswordModal')).hide();
                    
                    // Show OTP modal
                    const otpModal = new bootstrap.Modal(document.getElementById('otpVerificationModal'));
                    otpModal.show();
                    
                    // Reset form
                    form.reset();
                } else {
                    alert(data.message || 'Terjadi kesalahan');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan: ' + error.message);
            })
            .finally(() => {
                button.disabled = false;
                button.innerHTML = originalText;
            });
        });

        // OTP Input Handling
        const otpInputs = document.querySelectorAll('.otp-input');
        
        otpInputs.forEach((input, index) => {
            // Input event - auto advance
            input.addEventListener('input', function(e) {
                const value = this.value;
                
                // Only allow numbers
                if (!/^\d*$/.test(value)) {
                    this.value = '';
                    return;
                }
                
                // Add filled class
                if (value) {
                    this.classList.add('filled');
                } else {
                    this.classList.remove('filled');
                }
                
                // Auto advance to next input
                if (value && index < otpInputs.length - 1) {
                    otpInputs[index + 1].focus();
                }
            });
            
            // Keydown event - backspace navigation
            input.addEventListener('keydown', function(e) {
                if (e.key === 'Backspace' && !this.value && index > 0) {
                    otpInputs[index - 1].focus();
                }
            });
            
            // Paste event - distribute digits
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

        // Auto-focus first OTP input when modal is shown
        document.getElementById('otpVerificationModal').addEventListener('shown.bs.modal', function() {
            otpInputs[0].focus();
        });

        // Verify OTP
        document.getElementById('verifyOtpBtn').addEventListener('click', function() {
            const otp = Array.from(otpInputs).map(input => input.value).join('');
            const button = this;
            const originalText = button.innerHTML;
            
            if (otp.length !== 4) {
                alert('Masukkan 4 digit kode OTP');
                return;
            }

            // Show loading
            button.disabled = true;
            button.innerHTML = '<i class="bx bx-loader-alt bx-spin me-1"></i>Memverifikasi...';

            // AJAX Request
            fetch("{{ route('admin.profile.verify-otp') }}", {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ otp: otp })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Hide OTP modal
                    bootstrap.Modal.getInstance(document.getElementById('otpVerificationModal')).hide();
                    
                    // Show success modal
                    const successModal = new bootstrap.Modal(document.getElementById('successModal'));
                    successModal.show();
                    
                    // Clear OTP inputs
                    otpInputs.forEach(input => {
                        input.value = '';
                        input.classList.remove('filled');
                    });
                } else {
                    alert(data.message || 'Kode OTP tidak valid');
                    // Clear OTP inputs for retry
                    otpInputs.forEach(input => {
                        input.value = '';
                        input.classList.remove('filled');
                    });
                    otpInputs[0].focus();
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan jaringan');
            })
            .finally(() => {
                button.disabled = false;
                button.innerHTML = originalText;
            });
        });

        // Resend OTP
        document.getElementById('resendOtpBtn').addEventListener('click', function(e) {
            e.preventDefault();
            const link = this;
            const originalText = link.innerHTML;
            
            link.innerHTML = '<i class="bx bx-loader-alt bx-spin"></i> Mengirim...';
            link.style.pointerEvents = 'none';

            // AJAX Request
            fetch("{{ route('admin.profile.resend-otp') }}", {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message || 'Kode OTP baru telah dikirim');
                    
                    // Clear and focus first input
                    otpInputs.forEach(input => {
                        input.value = '';
                        input.classList.remove('filled');
                    });
                    otpInputs[0].focus();
                } else {
                    alert(data.message || 'Gagal mengirim ulang OTP');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan jaringan');
            })
            .finally(() => {
                link.innerHTML = originalText;
                link.style.pointerEvents = 'auto';
            });
        });

        // Close Success Modal
        document.getElementById('closeSuccessModalBtn').addEventListener('click', function() {
            window.location.reload();
        });

        // --- Avatar Logic ---
        const accountUserImage = document.getElementById('uploadedAvatar');
        const fileInput = document.querySelector('.account-file-input');
        const resetFileInput = document.querySelector('.account-image-reset');
        const deleteAvatarInput = document.getElementById('deleteAvatarInput');

        if (accountUserImage && fileInput) {
            fileInput.onchange = () => {
                if (fileInput.files[0]) {
                    if(deleteAvatarInput) deleteAvatarInput.value = '0';
                    
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        if (document.getElementById('uploadedAvatar').classList.contains('avatar-default')) {
                            document.getElementById('uploadedAvatar').outerHTML = '<img src="' + e.target.result + '" alt="user-avatar" class="avatar-preview rounded-circle" id="uploadedAvatar" />';
                        } else {
                            const img = document.getElementById('uploadedAvatar');
                            if(img) img.src = e.target.result;
                        }
                    };
                    reader.readAsDataURL(fileInput.files[0]);
                }
            };

            if (resetFileInput) {
                resetFileInput.onclick = () => {
                    fileInput.value = '';
                    if(deleteAvatarInput) deleteAvatarInput.value = '1';

                    const defaultPlaceholder = `
                        <div class="avatar-preview avatar-default rounded-circle d-flex align-items-center justify-content-center" id="uploadedAvatar" style="background-color: #D1D5DB; background-image: none;">
                            <svg viewBox="0 0 24 24" fill="currentColor" style="width: 60px; height: 60px; color: white;">
                                <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                            </svg>
                        </div>
                    `;
                    
                    const currentAvatar = document.getElementById('uploadedAvatar');
                    if(currentAvatar) {
                        if(currentAvatar.tagName === 'IMG') {
                            currentAvatar.outerHTML = defaultPlaceholder;
                        }
                    }
                };
            }
        }
    });
</script>
