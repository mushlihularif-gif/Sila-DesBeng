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

    public function completeRegistration(Request $request)
    {
        $request->validate([
            'nik' => 'required|string|size:16|unique:users,nik',
            'name' => 'required|string|max:255',
            'gender' => 'required|in:laki-laki,perempuan',
            'region_id' => 'required|exists:regions,id',
            'address' => 'required|string',
            'phone' => 'required|string|max:20',
        ], [
            'nik.unique' => 'NIK sudah digunakan',
        ]);

        $googleData = session('google_register_data');
        if (!$googleData) {
            return response()->json(['success' => false, 'message' => 'Sesi pendaftaran Google telah kedaluwarsa. Silakan ulangi.'], 400);
        }

        // Cek apakah user sudah terlanjur dibuat tapi gagal di proses sebelumnya (e.g. error 500)
        $existingUser = User::where('email', $googleData['email'])->first();

        if ($existingUser) {
            $newUser = $existingUser;
            $newUser->nik = $request->nik;
            $newUser->name = $request->name;
            $newUser->gender = $request->gender;
            $newUser->phone = $request->phone;
            $newUser->address = $request->address;
            $newUser->region_id = $request->region_id;
            $newUser->google_id = $googleData['id'];
            $newUser->status = 'aktif';
            $newUser->email_verified_at = now();
            $newUser->save();
        } else {
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
                'nik' => $request->nik,
                'name' => $request->name,
                'email' => $googleData['email'],
                'username' => $username,
                'password' => Hash::make(uniqid()), // Random password
                'phone' => $request->phone,
                'address' => $request->address,
                'gender' => $request->gender,
                'region_id' => $request->region_id,
            ]);

            // Set explicit safe fields
            $newUser->role = 'user';
            $newUser->status = 'aktif'; // Google User automatically active (email sudah diverifikasi Google)
            $newUser->google_id = $googleData['id'];
            $newUser->email_verified_at = now(); 
            $newUser->save();
        }

        // Clear google session
        session()->forget('google_register_data');

        \App\Models\ActivityLog::create([
            'user_id' => $newUser->id,
            'action' => 'Register',
            'description' => 'Register via Google Berhasil',
            'ip_address' => request()->ip()
        ]);

        Auth::login($newUser);
        request()->session()->regenerate();

        return response()->json([
            'success' => true,
            'require_otp' => false,
            'message' => 'Pendaftaran berhasil. Anda akan dialihkan...',
            'redirect' => route('beranda')
        ]);
    }
}
