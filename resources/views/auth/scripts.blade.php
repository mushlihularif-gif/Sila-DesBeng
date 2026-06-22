<script>
    (() => {
        // Execute immediately since it's at the end of the body
        const overlay = document.getElementById('auth-modal-overlay');
        const modalLogin = document.getElementById('modal-login');
        const modalRegister = document.getElementById('modal-register');
        const modalOtp = document.getElementById('modal-otp');
        const modalSuccess = document.getElementById('modal-success');

        // ========================================
        // UTILITY FUNCTIONS
        // ========================================
        function getScrollbarWidth() {
            return window.innerWidth - document.documentElement.clientWidth;
        }

        function openModal(modal) {
            if (!modal) return;

            const scrollbarWidth = getScrollbarWidth();
            document.body.style.paddingRight = `${scrollbarWidth}px`;

            // 1. Reset semua modal content lain agar hidden
            document.querySelectorAll('.modal-content').forEach(m => {
                m.classList.add('hidden');
                m.classList.remove('scale-100', 'opacity-100');
                m.classList.add('scale-95', 'opacity-0');
            });

            // 2. Tampilkan Overlay (Hapus opacity-0)
            overlay.classList.remove('hidden');
            // Force reflow
            void overlay.offsetWidth; 
            
            setTimeout(() => {
                overlay.classList.remove('opacity-0'); // Ganti .show dengan native Tailwind
                
                // 3. Tampilkan Modal Content
                modal.classList.remove('hidden');
                // Force reflow
                void modal.offsetWidth;
                
                setTimeout(() => {
                    modal.classList.remove('scale-95', 'opacity-0');
                    modal.classList.add('scale-100', 'opacity-100');
                }, 50); // Delay dikit biar transisi jalan
            }, 10);
            
            document.body.style.overflow = 'hidden'; // Lock scroll
        }

        function closeModal() {
            // 1. Hide Overlay
            overlay.classList.add('opacity-0');
            
            // 2. Hide All Modals (Scale down)
            document.querySelectorAll('.modal-content').forEach(m => {
                m.classList.remove('scale-100', 'opacity-100');
                m.classList.add('scale-95', 'opacity-0');
            });

            setTimeout(() => {
                overlay.classList.add('hidden');
                document.querySelectorAll('.modal-content').forEach(m => {
                    m.classList.add('hidden');
                });
                document.body.style.overflow = ''; // Restore scroll
                document.body.style.paddingRight = ''; // Restore padding
            }, 300); // Sesuaikan dengan duration-300
        }

        // ⭐ FIX: SMOOTH MODAL SWITCH (Tanpa Hilang)
        function switchModal(fromModal, toModal) {
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

        function showError(form, field, message) {
            const errorSpan = form.querySelector(`[data-error="${field}"]`);
            if (errorSpan) {
                errorSpan.textContent = message;
                errorSpan.classList.remove('hidden');

                // ⭐ FIX: Auto-hide setelah 3 detik
                setTimeout(() => {
                    errorSpan.classList.add('opacity-0');
                    setTimeout(() => {
                        errorSpan.classList.add('hidden');
                        errorSpan.classList.remove('opacity-0');
                    }, 300);
                }, 3000);
            }
        }

        function clearErrors(form) {
            form.querySelectorAll('[data-error]').forEach(span => {
                span.textContent = '';
                span.classList.add('hidden');
            });
        }

        function setButtonLoading(button, loading) {
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
            
            let bgClass = 'bg-green-500';
            if (type === 'error') bgClass = 'bg-red-500';
            else if (type === 'warning') bgClass = 'bg-yellow-500';
            else if (type === 'info') bgClass = 'bg-blue-500';

            toast.className = `sd-toast px-6 py-3 rounded-lg shadow-lg text-white transform transition-all duration-300 translate-x-full ${bgClass}`;
            toast.style.position = 'fixed';
            toast.style.top = '24px';
            toast.style.right = '30px';
            toast.style.zIndex = '9999';
            toast.textContent = message;
            document.body.appendChild(toast);

            if (!window.toastCacheHandlerAdded) {
                document.addEventListener('turbo:before-cache', () => {
                    document.querySelectorAll('.sd-toast').forEach(t => t.remove());
                });
                window.toastCacheHandlerAdded = true;
            }

            setTimeout(() => toast.classList.remove('translate-x-full'), 100);
            setTimeout(() => {
                toast.classList.add('translate-x-full');
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        }

        // ⭐ NEW: Handle Session Flash Messages on Page Load
        @if(session('success'))
            showToast("{{ session('success') }}", 'success');
        @endif

        @if(session('error'))
            showToast("{{ session('error') }}", 'error');
        @endif

        // ⭐ INJECT SERVER-SIDE VALIDATION ERRORS
        @if($errors->any())
            const serverErrors = @json($errors->toArray());
            
            let activeForm = null;
            @if(session('open_register_modal'))
                activeForm = document.getElementById('form-register');
            @elseif(session('open_login_modal'))
                activeForm = document.querySelector('#modal-login form');
            @elseif(session('open_forgot_modal'))
                activeForm = document.querySelector('#modal-forgot-password form');
            @endif

            if (activeForm) {
                Object.keys(serverErrors).forEach(field => {
                    showError(activeForm, field, serverErrors[field][0]);
                });
            }
        @endif

        // ========================================
        // GOOGLE REGISTER HANDLING
        // ========================================
        const modalGoogleRegister = document.getElementById('modal-google-register');
        
        @if(session('open_google_register_modal'))
            setTimeout(() => {
                openModal(modalGoogleRegister);
            }, 500);
        @endif

        // Avatar Preview Handling
        document.getElementById('google-avatar-input')?.addEventListener('change', function(e) {
            if (e.target.files && e.target.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('google-avatar-preview').src = e.target.result;
                }
                reader.readAsDataURL(e.target.files[0]);
            }
        });

        // Google Register Form Submit
        const formGoogleRegister = document.getElementById('form-google-register');
        formGoogleRegister?.addEventListener('submit', async function(e) {
            e.preventDefault();
            clearErrors(this);

            const formData = new FormData(this);
            const submitBtn = this.querySelector('button[type="submit"]');

            setButtonLoading(submitBtn, true);

            try {
                const response = await fetch('{{ route('register.google.complete') }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                    body: formData
                });

                const data = await response.json();

                if (response.ok) {
                    if (data.require_otp) {
                        switchModal(modalGoogleRegister, modalOtp);
                        startOtpTimer();
                        showToast(data.message, 'success');
                    } else {
                        showToast(data.message, 'success');
                        closeModal();
                        setTimeout(() => window.location.href = data.redirect || '{{ route("beranda") }}', 1000);
                    }
                } else {
                    if (data.errors) {
                        Object.keys(data.errors).forEach(field => {
                            showError(this, field, data.errors[field][0]);
                        });
                    } else {
                        showToast(data.message || 'Registrasi gagal', 'error');
                        if(response.status === 419) {
                             setTimeout(() => window.location.reload(), 2000);
                        }
                    }
                }
            } catch (error) {
                console.error('Google register error:', error);
                showToast('Terjadi kesalahan sistem', 'error');
            } finally {
                setButtonLoading(submitBtn, false);
            }
        });

        @if(session('info'))
            showToast("{{ session('info') }}", 'info');
        @endif

        @if(session('warning'))
            showToast("{{ session('warning') }}", 'warning');
        @endif

        // ========================================
        // MODAL TRIGGERS
        // ========================================
        document.getElementById('btn-open-login')?.addEventListener('click', () => openModal(modalLogin));
        document.getElementById('btn-open-register')?.addEventListener('click', () => openModal(modalRegister));

        document.getElementById('btn-open-login-mobile')?.addEventListener('click', () => {
             // 1. Open Modal Immediately (Overlay z-60 covers Sidebar z-51)
            openModal(modalLogin);
            
            // 2. Close Sidebar in background (better UX)
            if (typeof window.closeMobileSidebar === 'function') {
                window.closeMobileSidebar();
            }
        });

        document.getElementById('btn-open-register-mobile')?.addEventListener('click', () => {
            openModal(modalRegister);
            if (typeof window.closeMobileSidebar === 'function') {
                 window.closeMobileSidebar();
            }
        });

        document.querySelectorAll('.modal-close').forEach(btn => {
            btn.addEventListener('click', closeModal);
        });

        overlay.addEventListener('click', function(e) {
            if (e.target === overlay) closeModal();
        });

        // ========================================
        // TAB SWITCHING - ⭐ SMOOTH!
        // ========================================
        document.getElementById('tab-login')?.addEventListener('click', () => switchModal(modalRegister,
            modalLogin));
        document.getElementById('tab-register')?.addEventListener('click', () => switchModal(modalLogin,
            modalRegister));
        document.getElementById('tab-login-2')?.addEventListener('click', () => switchModal(modalRegister,
            modalLogin));
        document.getElementById('tab-register-2')?.addEventListener('click', () => switchModal(modalLogin,
            modalRegister));

        // ========================================
        // PASSWORD TOGGLE
        // ========================================
        document.querySelectorAll('.toggle-password').forEach(btn => {
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


        // OTP INPUT HANDLING
        // ========================================
        const otpForms = document.querySelectorAll('#form-verify-otp, #form-verify-forgot-otp');
        
        otpForms.forEach(form => {
            const otpInputs = form.querySelectorAll('.otp-input');
            
            otpInputs.forEach((input, index) => {
                input.addEventListener('input', function(e) {
                    const value = e.target.value;
                    if (!/^\d*$/.test(value)) {
                        e.target.value = '';
                        return;
                    }
                    if (value.length === 1 && index < otpInputs.length - 1) {
                        otpInputs[index + 1].focus();
                    }
                });

                input.addEventListener('keydown', function(e) {
                    if (e.key === 'Backspace' && !e.target.value && index > 0) {
                        otpInputs[index - 1].focus();
                    }
                });

                input.addEventListener('paste', function(e) {
                    e.preventDefault();
                    const pasteData = e.clipboardData.getData('text').trim();
                    if (/^\d{4}$/.test(pasteData)) {
                        pasteData.split('').forEach((char, i) => {
                            if (otpInputs[i]) otpInputs[i].value = char;
                        });
                        otpInputs[3].focus();
                    }
                });
            });
        });

        // ========================================
        // RE-OPEN MODAL ON VALIDATION ERRORS OR OTP
        // ========================================
        @if(session('open_login_modal'))
            setTimeout(() => openModal(modalLogin), 500);
        @endif

        @if(session('open_register_modal'))
            setTimeout(() => openModal(modalRegister), 500);
        @endif

        @if(session('open_forgot_modal'))
            const modalForgotPassword = document.getElementById('modal-forgot-password');
            setTimeout(() => openModal(modalForgotPassword), 500);
        @endif

        @if(session('open_otp_modal'))
            setTimeout(() => openModal(document.getElementById('modal-otp')), 500);
        @endif

        @if(session('open_forgot_otp_modal'))
            setTimeout(() => openModal(document.getElementById('modal-forgot-otp')), 500);
        @endif

        @if(session('open_reset_password_modal'))
            setTimeout(() => openModal(document.getElementById('modal-reset-password')), 500);
        @endif

        // OTP FORM SUBMIT HANDLERS
        document.getElementById('form-verify-otp')?.addEventListener('submit', function(e) {
            let code = '';
            const inputs = this.querySelectorAll('.otp-input');
            inputs.forEach(input => code += input.value);
            this.querySelector('#real_otp_code').value = code;
            
            if (code.length < 4) {
                e.preventDefault();
                showToast('Silahkan lengkapi 4 digit kode OTP', 'error');
            }
        });

        document.getElementById('form-verify-forgot-otp')?.addEventListener('submit', function(e) {
            let code = '';
            const inputs = this.querySelectorAll('.otp-input');
            inputs.forEach(input => code += input.value);
            this.querySelector('#real_forgot_otp').value = code;
            
            if (code.length < 4) {
                e.preventDefault();
                showToast('Silahkan lengkapi 4 digit kode OTP', 'error');
            }
        });

        // ========================================
        // LOGIN FORM - LOADING STATE
        // ========================================
        document.getElementById('form-login')?.addEventListener('submit', function() {
            const submitBtn = this.querySelector('button[type="submit"]');
            if (submitBtn) setButtonLoading(submitBtn, true);
        });

        // ========================================
        // REGISTER FORM - VALIDASI CLIENT-SIDE
        // ========================================
        document.getElementById('form-register')?.addEventListener('submit', function(e) {
            const form = this;
            clearErrors(form);

            const passwordInput = document.getElementById('register-password');
            const confirmInput  = document.getElementById('register-password-confirm');
            const password      = passwordInput ? passwordInput.value : '';
            const confirm       = confirmInput  ? confirmInput.value  : '';

            let hasError = false;

            // Cek panjang password
            if (password.length > 0 && password.length < 8) {
                showError(form, 'password', '⚠️ Kata sandi minimal 8 karakter (saat ini ' + password.length + ' karakter)');
                hasError = true;
            } else if (password.length === 0) {
                showError(form, 'password', '⚠️ Kata sandi wajib diisi');
                hasError = true;
            }

            // Cek konfirmasi password
            if (confirm.length === 0) {
                showError(form, 'password_confirmation', '⚠️ Konfirmasi kata sandi wajib diisi');
                hasError = true;
            } else if (password !== confirm) {
                showError(form, 'password_confirmation', '⚠️ Konfirmasi kata sandi tidak cocok');
                hasError = true;
            }

            if (hasError) {
                e.preventDefault();
                // Scroll ke field password agar peringatan terlihat
                if (passwordInput) {
                    passwordInput.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
                return false;
            }
        });

        // Real-time: peringatan saat ketik password kurang dari 8 karakter
        document.getElementById('register-password')?.addEventListener('input', function() {
            const form = document.getElementById('form-register');
            if (!form) return;
            const errSpan = form.querySelector('[data-error="password"]');
            if (this.value.length > 0 && this.value.length < 8) {
                showError(form, 'password', '⚠️ Kata sandi minimal 8 karakter');
            } else {
                if (errSpan) {
                    errSpan.textContent = '';
                    errSpan.classList.add('hidden');
                }
            }
        });

        // Real-time: cek konfirmasi cocok saat ketik
        document.getElementById('register-password-confirm')?.addEventListener('input', function() {
            const form = document.getElementById('form-register');
            if (!form) return;
            const password = document.getElementById('register-password')?.value || '';
            const errSpan = form.querySelector('[data-error="password_confirmation"]');
            if (this.value.length > 0 && this.value !== password) {
                showError(form, 'password_confirmation', '⚠️ Konfirmasi kata sandi tidak cocok');
            } else {
                if (errSpan) {
                    errSpan.textContent = '';
                    errSpan.classList.add('hidden');
                }
            }
        });



        // Lupa Kata Sandi Trigger from Login Modal
        document.getElementById('btn-open-forgot-password')?.addEventListener('click', () => {
            const modalForgotPassword = document.getElementById('modal-forgot-password');
            switchModal(modalLogin, modalForgotPassword);
        });

        // ========================================
        // SUCCESS CONFIRMATION
        // ========================================
        document.getElementById('btn-confirm-success')?.addEventListener('click', function() {
            closeModal();
            setTimeout(() => window.location.href = '{{ route('beranda') }}', 300);
        });

        // STEP 4: Success Confirmation
        document.getElementById('btn-confirm-forgot-success')?.addEventListener('click', function() {
            closeModal();
            setTimeout(() => window.location.href = '{{ route('beranda') }}', 300);
        });

        // ========================================
        // REGION DROPDOWN LOGIC
        // ========================================
        function initRegionDropdowns(kabId, kecId, desaId) {
            const regKab = document.getElementById(kabId);
            const regKec = document.getElementById(kecId);
            const regDesa = document.getElementById(desaId);
            
            if (!regKab || !regKec || !regDesa) return;

            if (allRegions.length > 0) {
                populateRegions(regKab, regKec, regDesa);
            } else {
                fetch('/api/regions')
                    .then(res => res.json())
                    .then(data => {
                        allRegions = data;
                        populateRegions(regKab, regKec, regDesa);
                    })
                    .catch(err => {
                        console.error('Failed to load regions:', err);
                        regKab.innerHTML = '<option value="">Gagal memuat data wilayah</option>';
                    });
            }

            // Handle Kecamatan change
            regKec.addEventListener('change', function() {
                const kecVal = parseInt(this.value);
                regDesa.innerHTML = '<option value="">Pilih Desa/Kelurahan</option>';
                regDesa.disabled = true;

                if (kecVal) {
                    const desas = allRegions.filter(r => r.type === 'desa' && r.parent_id === kecVal);
                    desas.sort((a,b) => a.name.localeCompare(b.name)).forEach(d => {
                        regDesa.innerHTML += `<option value="${d.id}">${d.name}</option>`;
                    });
                    regDesa.disabled = false;
                }
            });
        }

        function populateRegions(regKab, regKec, regDesa) {
            const kabupaten = allRegions.find(r => r.type === 'kabupaten' && r.name === 'Kabupaten Bengkalis');
            if (kabupaten) {
                regKab.innerHTML = `<option value="${kabupaten.id}">${kabupaten.name}</option>`;
                regKab.value = kabupaten.id;
                
                // Populate Kecamatan
                const kecamatans = allRegions.filter(r => r.type === 'kecamatan' && r.parent_id === kabupaten.id);
                regKec.innerHTML = '<option value="">Pilih Kecamatan</option>';
                kecamatans.sort((a,b) => a.name.localeCompare(b.name)).forEach(k => {
                    regKec.innerHTML += `<option value="${k.id}">${k.name.replace('Kecamatan ', '')}</option>`;
                });
                regKec.disabled = false;
            }
        }

        let allRegions = [];
        initRegionDropdowns('reg-kabupaten', 'reg-kecamatan', 'reg-desa');
        initRegionDropdowns('google-reg-kabupaten', 'google-reg-kecamatan', 'google-reg-desa');
    })();
</script>
