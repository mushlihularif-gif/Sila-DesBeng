<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\OtpMail;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        try {
            $validated = $request->validate([
                'nik' => 'required|string|size:16|unique:users,nik',
                'username' => 'required|string|max:255|unique:users,username',
                'email' => 'required|email|unique:users,email',
                'name' => 'required|string|max:255',
                'phone' => 'required|string|max:20',
                'address' => 'required|string',
                'gender' => 'required|in:laki-laki,perempuan',
                'password' => 'required|string|min:8|confirmed',
                'region_id' => 'required|exists:regions,id',
                'otp_method' => 'required|in:email,sms',
            ], [
                'username.unique' => 'Username sudah digunakan',
                'email.unique' => 'Email sudah terdaftar',
                'password.min' => 'Password minimal 8 karakter',
                'password.confirmed' => 'Konfirmasi password tidak cocok',
                'otp_method.required' => 'Metode OTP harus dipilih',
            ]);

            $otpCode = str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);
            $tempUserId = 'temp_' . time() . '_' . rand(1000, 9999);

            session([
                'temp_registration' => [
                    'temp_id' => $tempUserId,
                    'nik' => $validated['nik'],
                    'username' => $validated['username'],
                    'email' => $validated['email'],
                    'name' => $validated['name'],
                    'phone' => $validated['phone'],
                    'address' => $validated['address'],
                    'gender' => $validated['gender'],
                    'password' => Hash::make($validated['password']),
                    'region_id' => $validated['region_id'],
                    'otp_code' => $otpCode,
                    'otp_expires_at' => now()->addMinutes(5),
                    'otp_method' => $validated['otp_method'],
                ]
            ]);

            // # ===================================================================
            // # MODE SANDBOX UNTUK MASS-TESTING DEMO
            // # ===================================================================
            if ($validated['otp_method'] === 'email') {
                Mail::to($validated['email'])->send(new OtpMail($otpCode));
            } else {
                // Implement SMS API logic here
            }
            // # ===================================================================

            session()->flash('otp_demo_sandbox_code', $otpCode);
            session()->flash('trigger_open_otp_tab', true);

            $methodText = $validated['otp_method'] === 'sms' ? 'nomor telepon' : 'email';
            return redirect()->route('beranda')->with('open_otp_modal', true)
                ->with('success', 'Kode OTP telah dikirim ke ' . $methodText . ' Anda');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()->withErrors($e->errors())->withInput()->with('open_register_modal', true);
        } catch (\Exception $e) {
            Log::error('Registration error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan sistem.')->with('open_register_modal', true);
        }
    }

    public function showOtpForm()
    {
        if (!session('temp_registration')) {
            return redirect()->route('beranda')->with('error', 'Silahkan daftar terlebih dahulu.');
        }
        return redirect()->route('beranda')->with('open_otp_modal', true);
    }

    public function showSandboxOtp()
    {
        $otpCode = '1234'; // Default fallback
        $method = 'Email';

        if (session('temp_registration')) {
            $otpCode = session('temp_registration')['otp_code'] ?? '1234';
            if (isset(session('temp_registration')['otp_method']) && session('temp_registration')['otp_method'] === 'sms') {
                $method = 'SMS';
            }
        } elseif (session('forgot_password_data')) {
            $otpCode = session('forgot_password_data')['otp_code'] ?? '1234';
            if (isset(session('forgot_password_data')['otp_method']) && session('forgot_password_data')['otp_method'] === 'sms') {
                $method = 'SMS';
            }
        }
        
        return response("
            <html>
            <head><title>🛡️ TESTING MODE: LAB OTP INTERCEPTOR</title></head>
            <body style='background: #1e1e2e; color: #a6e3a1; font-family: monospace; padding: 50px; text-align: center;'>
                <div style='border: 2px dashed #a6e3a1; padding: 30px; display: inline-block; border-radius: 10px; background: #252538;'>
                    <h2 style='color: #cdd6f4; margin-top: 0;'>🔑 [SANDBOX LAB MODE]</h2>
                    <p style='color: #bac2de; font-size: 1.1rem;'>Sistem mendeteksi request OTP dari Localhost. Log $method dialihkan ke layar ini:</p>
                    <hr style='border: 1px dashed #45475a;'>
                    <h1 style='font-size: 3rem; letter-spacing: 5px; margin: 20px 0;'>$otpCode</h1>
                    <hr style='border: 1px dashed #45475a;'>
                    <p style='color: #f38ba8; font-size: 0.9rem; margin-bottom: 0;'>⚠️ Jangan tutup tab ini sebelum memasukkan kode ke halaman verifikasi utama.</p>
                </div>
            </body>
            </html>
        ");
    }

    public function verifyOtp(Request $request)
    {
        try {
            $validated = $request->validate([
                'otp_code' => 'required|digits:4',
            ]);

            $tempData = session('temp_registration');

            if (!$tempData) {
                // FALLBACK FOR GOOGLE REGISTRATION
                $otpEmail = session('otp_email');
                $otpCode = session('otp_code');
                if ($otpEmail && $otpCode) {
                    if (session('otp_code') !== $validated['otp_code']) {
                        return redirect()->back()->with('error', 'Kode OTP Salah!')->with('open_otp_modal', true);
                    }
                    
                    $user = User::where('email', $otpEmail)->first();
                    if ($user) {
                        $user->status = 'aktif';
                        $user->email_verified_at = now();
                        $user->save();
                        session()->forget(['otp_email', 'otp_code', 'google_otp_method']);
                        Auth::login($user);
                        $request->session()->regenerate();
                        return redirect()->route('beranda')->with('success', 'Verifikasi Akun Google Berhasil!');
                    }
                }
                return redirect()->route('beranda')->with('error', 'Session tidak valid atau sudah berakhir.');
            }

            if (now()->greaterThan($tempData['otp_expires_at'])) {
                return redirect()->back()->with('error', 'Kode OTP sudah kadaluarsa')->with('open_otp_modal', true);
            }

            if ($tempData['otp_code'] !== $validated['otp_code']) {
                return redirect()->back()->with('error', 'Kode OTP Salah!')->with('open_otp_modal', true);
            }

            // Create user with safe fields only (no role/status via mass assignment)
            $user = User::create([
                'nik' => $tempData['nik'],
                'username' => $tempData['username'],
                'email' => $tempData['email'],
                'name' => $tempData['name'],
                'phone' => $tempData['phone'],
                'address' => $tempData['address'],
                'gender' => $tempData['gender'],
                'password' => $tempData['password'],
                'region_id' => $tempData['region_id'],
            ]);

            // Explicitly set sensitive fields to prevent mass assignment injection
            $user->role = 'user';
            $user->status = 'aktif';
            $user->email_verified_at = now();
            $user->save();

            session()->forget('temp_registration');
            Auth::login($user);
            $request->session()->regenerate();

            return redirect()->route('beranda')->with('success', 'Registrasi dan Verifikasi Berhasil!');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()->withErrors($e->errors())->withInput()->with('open_otp_modal', true);
        } catch (\Exception $e) {
            Log::error('OTP verification error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan sistem.')->with('open_otp_modal', true);
        }
    }

    public function resendOtp(Request $request)
    {
        try {
            $tempData = session('temp_registration');
            $otpEmail = session('otp_email');

            if (!$tempData && !$otpEmail) {
                return redirect()->route('beranda')->with('error', 'Session tidak valid atau sudah berakhir.');
            }

            $newOtpCode = str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);

            if ($tempData) {
                $tempData['otp_code'] = $newOtpCode;
                $tempData['otp_expires_at'] = now()->addMinutes(5);
                if ($request->has('switch_method')) {
                    $tempData['otp_method'] = $request->switch_method;
                }
                session(['temp_registration' => $tempData]);
                $method = $tempData['otp_method'] ?? 'email';
            } else {
                session(['otp_code' => $newOtpCode]);
                if ($request->has('switch_method')) {
                    session(['google_otp_method' => $request->switch_method]);
                }
                $method = session('google_otp_method', 'email');
            }

            session()->flash('otp_demo_sandbox_code', $newOtpCode);
            session()->flash('trigger_open_otp_tab', true);

            $methodText = ($method === 'sms') ? 'nomor telepon' : 'email';
            return redirect()->route('beranda')
                ->with('success', 'Kode OTP baru telah dikirim ke ' . $methodText . ' Anda.')
                ->with('open_otp_modal', true);
        } catch (\Exception $e) {
            Log::error('Resend OTP error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal mengirim ulang OTP.')->with('open_otp_modal', true);
        }
    }

    public function login(Request $request)
    {
        try {
            $validated = $request->validate([
                'email_or_phone' => 'required|string',
                'password' => 'required|string',
                'remember' => 'nullable|boolean',
            ]);

            // SILENT BLOCKING (SECURITY THROUGH OBSCURITY)
            // Kunci 1: Berdasarkan IP address (Menangkal spammer dari 1 lokasi)
            $throttleKey = 'login-attempt:' . $request->ip();
            // Kunci 2: Berdasarkan Username/Email (ANTI IP ROTATION - menangkal botnet VPN)
            $accountThrottleKey = 'login-attempt-account:' . $validated['email_or_phone'];

            // CEK: Apakah IP ini ATAU Akun ini sudah menyentuh batas 5 kali gagal?
            if (\Illuminate\Support\Facades\RateLimiter::tooManyAttempts($throttleKey, 5) || 
                \Illuminate\Support\Facades\RateLimiter::tooManyAttempts($accountThrottleKey, 5)) {
                
                $seconds = max(
                    \Illuminate\Support\Facades\RateLimiter::availableIn($throttleKey),
                    \Illuminate\Support\Facades\RateLimiter::availableIn($accountThrottleKey)
                );
                $minutes = ceil($seconds / 60);

                return redirect()->back()->with('error', "Terlalu banyak percobaan gagal. Silahkan coba login kembali setelah $minutes menit.")->with('open_login_modal', true)->withInput();
            }

            $loginField = $validated['email_or_phone'];
            
            // Check if it's an email, otherwise it could be username or phone
            $user = User::where('email', $loginField)
                ->orWhere('username', $loginField)
                ->orWhere('phone', $loginField)
                ->first();

            if (!$user) {
                // Catat kegagalan ke IP dan Akun (Blokir 5 menit)
                \Illuminate\Support\Facades\RateLimiter::hit($throttleKey, 300);
                \Illuminate\Support\Facades\RateLimiter::hit($accountThrottleKey, 300);
                return redirect()->back()->with('error', 'Username atau Password yang Anda masukkan salah.')->with('open_login_modal', true)->withInput();
            }

            if ($user->status !== 'aktif') {
                \Illuminate\Support\Facades\RateLimiter::hit($throttleKey, 300);
                \Illuminate\Support\Facades\RateLimiter::hit($accountThrottleKey, 300);
                return redirect()->back()->with('error', 'Akun belum aktif')->with('open_login_modal', true)->withInput();
            }

            if (!Hash::check($validated['password'], $user->password)) {
                \Illuminate\Support\Facades\RateLimiter::hit($throttleKey, 300);
                \Illuminate\Support\Facades\RateLimiter::hit($accountThrottleKey, 300);
                return redirect()->back()->with('error', 'Username atau Password yang Anda masukkan salah.')->with('open_login_modal', true)->withInput();
            }

            // LOGIN BERHASIL -> Hapus riwayat blokir IP dan Akun
            \Illuminate\Support\Facades\RateLimiter::clear($throttleKey);
            \Illuminate\Support\Facades\RateLimiter::clear($accountThrottleKey);

            // Convert remember value to boolean
            $rememberMe = isset($validated['remember']) && ($validated['remember'] === true || $validated['remember'] === '1' || $validated['remember'] === 1);
            Auth::login($user, $rememberMe);
            $request->session()->regenerate();

            // Redirect based on user role
            $adminRoles = ['admin', 'super_admin', 'admin_kecamatan', 'admin_desa'];
            if (in_array($user->role, $adminRoles)) {
                $redirectUrl = route('admin.dashboard');
            } elseif ($user->role === 'lurah') {
                $redirectUrl = route('lurah.dashboard');
            } else {
                $redirectUrl = route('beranda');
            }

            // Log Activity
            \App\Models\ActivityLog::create([
                'user_id' => $user->id,
                'action' => 'Login',
                'description' => 'Login Berhasil sebagai ' . $user->role,
                'ip_address' => $request->ip()
            ]);

            return redirect($redirectUrl)->with('success', 'Login berhasil');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()->withErrors($e->errors())->withInput()->with('open_login_modal', true);
        } catch (\Exception $e) {
            // Log detail error untuk debugging
            Log::error('Login error: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return redirect()->back()->with('error', 'Terjadi kesalahan sistem.')->with('open_login_modal', true);
        }
    }

    public function logout(Request $request)
    {
        if (Auth::check()) {
            \App\Models\ActivityLog::create([
                'user_id' => Auth::id(),
                'action' => 'Logout',
                'description' => 'Logout Berhasil',
                'ip_address' => $request->ip()
            ]);
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('beranda')->with('success', 'Berhasil keluar dari akun.');
    }

    public function forgotPassword(Request $request)
    {
        try {
            $validated = $request->validate([
                'email_or_phone' => 'required|string',
                'otp_method' => 'required|in:email,sms',
            ], [
                'email_or_phone.required' => 'Email atau Nomor Telepon harus diisi',
                'otp_method.required' => 'Metode OTP harus dipilih',
            ]);

            $user = User::where('email', $validated['email_or_phone'])
                        ->orWhere('phone', $validated['email_or_phone'])
                        ->first();

            if (!$user) {
                return redirect()->route('beranda')->with('error', 'Email atau Nomor Telepon tidak terdaftar')->with('open_forgot_password_modal', true);
            }

            // Generate 4 digit OTP
            $otpCode = str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);

            // Simpan OTP ke session (bukan ke database!)
            session([
                'forgot_password_data' => [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'otp_code' => $otpCode,
                    'otp_expires_at' => now()->addMinutes(5),
                    'otp_method' => $validated['otp_method'],
                ]
            ]);

            // # MODE SANDBOX UNTUK MASS-TESTING DEMO
            if ($validated['otp_method'] === 'email') {
                Mail::to($user->email)->send(new OtpMail($otpCode));
            }

            session()->flash('otp_demo_sandbox_code', $otpCode);
            session()->flash('trigger_open_otp_tab', true);

            $methodText = $validated['otp_method'] === 'sms' ? 'nomor telepon' : 'email';
            return redirect()->route('beranda')->with('open_forgot_otp_modal', true)
                ->with('success', 'Kode OTP telah dikirim ke ' . $methodText . ' Anda');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()->withErrors($e->errors())->withInput()->with('open_forgot_modal', true);
        } catch (\Exception $e) {
            \Log::error('Forgot password error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan sistem.')->with('open_forgot_modal', true);
        }
    }

    public function showForgotOtpForm()
    {
        if (!session('forgot_password_data')) {
            return redirect()->route('beranda')->with('error', 'Sesi Anda telah berakhir. Silahkan ulangi proses.');
        }
        return redirect()->route('beranda')->with('open_forgot_otp_modal', true);
    }

    public function verifyForgotPasswordOtp(Request $request)
    {
        try {
            $validated = $request->validate([
                'otp' => 'required|digits:4',
            ], [
                'otp.required' => 'Kode OTP harus diisi',
                'otp.digits' => 'Kode OTP harus 4 digit',
            ]);

            $sessionData = session('forgot_password_data');

            if (!$sessionData) {
                return redirect()->route('beranda')->with('error', 'Session tidak valid atau sudah berakhir.');
            }

            // Check expiration
            if (now()->greaterThan($sessionData['otp_expires_at'])) {
                return redirect()->back()->with('error', 'Kode OTP sudah kadaluarsa')->with('open_forgot_otp_modal', true);
            }

            // Verify OTP
            if ($sessionData['otp_code'] !== $validated['otp']) {
                return redirect()->back()->with('error', 'Kode OTP tidak valid')->with('open_forgot_otp_modal', true);
            }

            // OTP valid → Set flag di session
            session(['forgot_password_otp_verified' => true]);

            return redirect()->route('beranda')->with('success', 'Kode OTP valid. Silahkan buat kata sandi baru.')->with('open_reset_password_modal', true);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()->withErrors($e->errors())->withInput()->with('open_forgot_otp_modal', true);
        } catch (\Exception $e) {
            \Log::error('Verify OTP error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan sistem.')->with('open_forgot_otp_modal', true);
        }
    }

    public function showResetPasswordForm()
    {
        if (!session('forgot_password_otp_verified')) {
            return redirect()->route('beranda')->with('error', 'Verifikasi OTP diperlukan.');
        }
        return redirect()->route('beranda')->with('open_reset_password_modal', true);
    }

    public function resetForgotPassword(Request $request)
    {
        try {
            // Check if OTP verified
            if (!session('forgot_password_otp_verified')) {
                return redirect()->route('beranda')->with('error', 'Verifikasi OTP diperlukan.');
            }

            $validated = $request->validate([
                'password' => 'required|min:8|confirmed',
            ], [
                'password.required' => 'Password baru harus diisi',
                'password.min' => 'Password minimal 8 karakter',
                'password.confirmed' => 'Konfirmasi password tidak cocok',
            ]);

            $sessionData = session('forgot_password_data');

            if (!$sessionData) {
                return redirect()->route('beranda')->with('error', 'Session tidak valid atau sudah berakhir.');
            }

            // Update password using explicit assignment
            $user = User::find($sessionData['user_id']);
            $user->password = Hash::make($validated['password']);
            $user->save();

            // Clear session
            session()->forget(['forgot_password_data', 'forgot_password_otp_verified']);

            // Auto login user
            Auth::login($user);
            $request->session()->regenerate();

            return redirect()->route('beranda')->with('success', 'Password berhasil diperbarui dan Anda telah login.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->route('beranda')->withErrors($e->errors())->withInput()->with('open_reset_password_modal', true);
        } catch (\Exception $e) {
            \Log::error('Reset password error: ' . $e->getMessage());
            return redirect()->route('beranda')->with('error', 'Terjadi kesalahan sistem.')->with('open_reset_password_modal', true);
        }
    }

    public function resendForgotPasswordOtp(Request $request)
    {
        try {
            $sessionData = session('forgot_password_data');

            if (!$sessionData) {
                return redirect()->route('beranda')->with('error', 'Session tidak valid atau sudah berakhir.');
            }

            // Generate new OTP
            $newOtpCode = str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);

            // Update session
            $sessionData['otp_code'] = $newOtpCode;
            $sessionData['otp_expires_at'] = now()->addMinutes(5);
            if ($request->has('switch_method')) {
                session(['forgot_password_otp_method' => $request->switch_method]);
            }
            session(['forgot_password_data' => $sessionData]);

            $method = session('forgot_password_otp_method', 'email');

            session()->flash('otp_demo_sandbox_code', $newOtpCode);
            session()->flash('trigger_open_otp_tab', true);

            $methodText = ($method === 'sms') ? 'nomor telepon' : 'email';
            return redirect()->route('beranda')
                ->with('success', 'Kode OTP baru telah dikirim ke ' . $methodText . ' Anda.')
                ->with('open_forgot_otp_modal', true);
        } catch (\Exception $e) {
            \Log::error('Resend forgot password OTP error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal mengirim ulang OTP.')->with('open_forgot_otp_modal', true);
        }
    }
}
