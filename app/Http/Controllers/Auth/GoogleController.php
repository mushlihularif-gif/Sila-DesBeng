<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;

class GoogleController extends Controller
{
    // Redirect to Google
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    // Handle Google Callback
    public function handleGoogleCallback()
    {
        try {
            $socialUser = Socialite::driver('google')->user();

            $findUser = User::where('google_id', $socialUser->id)
                            ->orWhere('email', $socialUser->email)
                            ->first();

            if ($findUser) {
                // User exists
                if (!$findUser->google_id) {
                    // Explicitly set fields instead of mass assignment
                    $findUser->google_id = $socialUser->id;
                    $findUser->email_verified_at = now();
                    $findUser->save();
                }
                
                Auth::login($findUser);
                request()->session()->regenerate();
                
                // Cek status aktif
                if ($findUser->status !== 'aktif') {
                    Auth::logout();
                    return redirect()->route('beranda')->with('error', 'Akun Anda belum aktif.');
                }
                
                 \App\Models\ActivityLog::create([
                    'user_id' => $findUser->id,
                    'action' => 'Login',
                    'description' => 'Login via Google Berhasil',
                    'ip_address' => request()->ip()
                ]);

                return redirect()->intended($findUser->role === 'admin' ? route('admin.dashboard') : route('beranda'));

            } else {
                // User Baru - Tampilkan Form Lengkapi Profil Google
                session(['google_register_data' => [
                    'id' => $socialUser->id,
                    'name' => $socialUser->name,
                    'email' => $socialUser->email,
                    'avatar' => $socialUser->avatar,
                ]]);

                return redirect()->route('beranda')->with('show_google_register', true);
            }

        } catch (\Exception $e) {
            return redirect()->route('beranda')->with('error', 'Login Google gagal: ' . $e->getMessage());
        }
    }

    // Complete Google Registration via AJAX
    public function completeRegistration(Request $request)
    {
        $request->validate([
            'region_id' => 'required|exists:regions,id',
            'address' => 'required|string',
            'phone' => 'required|string|max:20',
        ]);

        $googleData = session('google_register_data');
        if (!$googleData) {
            return response()->json(['success' => false, 'message' => 'Sesi pendaftaran Google telah kedaluwarsa. Silakan ulangi.'], 400);
        }

        // Generate unique username
        $baseUsername = strtolower(str_replace(' ', '', $googleData['name']));
        $username = $baseUsername;
        $counter = 1;
        while (User::where('username', $username)->exists()) {
            $username = $baseUsername . $counter;
            $counter++;
        }

        // Create User
        $newUser = User::create([
            'name' => $googleData['name'],
            'email' => $googleData['email'],
            'username' => $username,
            'password' => Hash::make(uniqid()), // Random password
            'phone' => $request->phone,
            'address' => $request->address,
            'gender' => 'laki-laki', // Default placeholder
            'region_id' => $request->region_id,
        ]);

        // Set explicit safe fields
        $newUser->role = 'user';
        $newUser->status = 'belum verifikasi'; // Require OTP Verification
        $newUser->google_id = $googleData['id'];
        // Note: For Google accounts, email is inherently verified, but we'll use email_verified_at for OTP later if needed, or set it now.
        $newUser->email_verified_at = now(); 
        $newUser->save();

        // Clear google session
        session()->forget('google_register_data');

        \App\Models\ActivityLog::create([
            'user_id' => $newUser->id,
            'action' => 'Register',
            'description' => 'Register via Google (Menunggu OTP)',
            'ip_address' => request()->ip()
        ]);

        // Generate OTP
        $otp = sprintf("%06d", mt_rand(1, 999999));
        session(['otp_email' => $newUser->email, 'otp_code' => $otp]);

        return response()->json([
            'success' => true,
            'require_otp' => true,
            'message' => 'Silakan verifikasi OTP.'
        ]);
    }
}
