@extends('layouts.app')

@section('content')
<div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8 relative bg-cover bg-center bg-no-repeat" style="background-image: url('{{ asset('User/img/elemen/entrance.png') }}');">
    <!-- Glassmorphism Overlay -->
    <div class="absolute inset-0 bg-white/30 backdrop-blur-md"></div>
    
    <div class="max-w-md w-full bg-white p-10 rounded-3xl shadow-2xl relative z-10 text-center">
        <h2 class="text-2xl font-bold text-gray-900 mb-6">Verifikasi Kode</h2>
        
        <div class="flex justify-center mb-6">
            <img src="{{ asset('User/img/elemen/verifff.png') }}" alt="Verification Icon" class="h-32 object-contain">
        </div>

        <p class="text-lg font-semibold text-gray-900 mb-2">Masukkan Kode Untuk Melanjutkan</p>
        <p class="text-sm text-gray-500 mb-8">Silahkan masukkan kode konfirmasi yang anda terima</p>

        <form action="{{ route('auth.verify-otp') }}" method="POST" id="otp-form" class="space-y-6">
            @csrf

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
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const otpInputs = document.querySelectorAll('.otp-input');
        const form = document.getElementById('otp-form');
        const realOtp = document.getElementById('real_otp_code');

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
        });

        form.addEventListener('submit', function(e) {
            let code = '';
            otpInputs.forEach(input => code += input.value);
            realOtp.value = code;
            
            // Basic validation
            if (code.length < 4) {
                e.preventDefault();
                alert('Silahkan lengkapi 4 digit kode OTP');
            }
        });
    });
</script>

<style>
    /* OTP Input Animation */
    .otp-input:focus {
        animation: pulse 0.3s ease-in-out;
    }

    @keyframes pulse {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.05); }
    }
</style>
@endsection
