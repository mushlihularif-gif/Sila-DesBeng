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
                // User Baru - Auto Register Langsung Masuk
                // Generate unique username
                $baseUsername = strtolower(str_replace(' ', '', $socialUser->name));
                $username = $baseUsername;
                $counter = 1;
                while (User::where('username', $username)->exists()) {
                    $username = $baseUsername . $counter;
                    $counter++;
                }

                // Create User with safe fields only (no role/status via mass assignment)
                $newUser = User::create([
                    'name' => $socialUser->name,
                    'email' => $socialUser->email,
                    'username' => $username,
                    'password' => Hash::make(uniqid()), // Random password
                    'phone' => '-', // Placeholder, user can update later
                    'address' => '-', // Placeholder
                    'gender' => 'laki-laki', // Default placeholder
                ]);

                // Explicitly set sensitive fields to prevent mass assignment injection
                $newUser->role = 'user';
                $newUser->status = 'aktif';
                $newUser->email_verified_at = now();
                $newUser->google_id = $socialUser->id;
                $newUser->save();

                Auth::login($newUser);
                request()->session()->regenerate();

                \App\Models\ActivityLog::create([
                    'user_id' => $newUser->id,
                    'action' => 'Register',
                    'description' => 'Register Otomatis via Google Berhasil',
                    'ip_address' => request()->ip()
                ]);

                return redirect()->route('beranda')->with('success', 'Login Berhasil! Silakan lengkapi profil Anda.');
            }

        } catch (\Exception $e) {
            return redirect()->route('beranda')->with('error', 'Login Google gagal: ' . $e->getMessage());
        }
    }
}
