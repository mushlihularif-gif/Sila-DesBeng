<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use App\Mail\OtpMail;
use App\Services\FonnteService;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Alamat email atau password salah.'],
            ]);
        }

        $token = $user->createToken('flutter-mobile-app')->plainTextToken;

        return response()->json([
            'status' => 'success',
            'message' => 'Berhasil login',
            'data' => [
                'user' => $user,
                'token' => $token
            ]
        ], 200);
    }
    public function loginGoogle(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'name' => 'required|string',
            'google_id' => 'required|string',
            'location_name' => 'nullable|string',
            'phone' => 'nullable|string',
            'region_id' => 'nullable|integer',
        ]);

        $user = User::where('email', $request->email)->first();

        // 1. Pengguna SUDAH TERDAFTAR di database -> Langsung login!
        if ($user) {
            if ($request->region_id && (!$user->region_id || $user->region_id == 1)) {
                $user->region_id = $request->region_id;
            }
            if ($request->phone && ($user->phone == '-' || empty($user->phone))) {
                $user->phone = $request->phone;
            }
            if (!$user->google_id) {
                $user->google_id = $request->google_id;
            }
            $user->save();

            $token = $user->createToken('flutter-mobile-app')->plainTextToken;
            $user->load('region');

            return response()->json([
                'status' => 'success',
                'is_new_user' => false,
                'message' => 'Berhasil login dengan Google',
                'data' => [
                    'user' => $user,
                    'token' => $token
                ]
            ], 200);
        }

        // 2. Pengguna BARU
        // Jika belum menyertakan region_id atau nomor HP, minta lengkapi profil terlebih dahulu
        if (!$request->region_id || !$request->phone || $request->phone === '-') {
            return response()->json([
                'status' => 'needs_completion',
                'is_new_user' => true,
                'message' => 'Silakan lengkapi wilayah dan nomor WhatsApp.',
                'data' => [
                    'email' => $request->email,
                    'name' => $request->name,
                    'google_id' => $request->google_id,
                ]
            ], 200);
        }

        // Generate username unik dari nama
        $baseUsername = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $request->name));
        if (empty($baseUsername)) {
            $baseUsername = 'warga';
        }
        $username = $baseUsername;
        $counter = 1;
        while (User::where('username', $username)->exists()) {
            $username = $baseUsername . $counter;
            $counter++;
        }

        // Buat akun baru dengan wilayah & nomor telepon valid
        $user = User::create([
            'email' => $request->email,
            'name' => $request->name,
            'username' => $username,
            'password' => Hash::make(uniqid()),
            'nik' => null,
            'phone' => $request->phone,
            'address' => $request->location_name ?? '-',
            'gender' => 'laki-laki',
            'region_id' => $request->region_id,
            'google_id' => $request->google_id,
            'status' => 'aktif',
            'email_verified_at' => now(),
        ]);

        $token = $user->createToken('flutter-mobile-app')->plainTextToken;
        $user->load('region');

        return response()->json([
            'status' => 'success',
            'is_new_user' => true,
            'message' => 'Pendaftaran akun dengan Google berhasil',
            'data' => [
                'user' => $user,
                'token' => $token
            ]
        ], 201);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Berhasil logout'
        ], 200);
    }

    public function register(Request $request)
    {
        $request->validate([
            'username' => 'required|string|max:255|unique:users,username',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'required|string|max:20',
            'region_id' => 'required|exists:regions,id',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $otpCode = str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);
        
        // Simpan data pendaftaran ke Cache selama 5 menit
        $cacheKey = 'register_otp_' . $request->email;
        Cache::put($cacheKey, [
            'username' => $request->username,
            'email' => $request->email,
            'phone' => $request->phone,
            'region_id' => $request->region_id,
            'password' => Hash::make($request->password),
            'otp_code' => $otpCode,
        ], now()->addMinutes(5));

        // Kirim OTP via Email
        try {
            Mail::to($request->email)->queue(new OtpMail($otpCode));
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Gagal kirim email OTP API: " . $e->getMessage());
        }

        // Kirim OTP via WA (opsional / jika Fonnte aktif)
        // try {
        //     $fonnte = new FonnteService();
        //     $fonnte->sendOtp($request->phone, $otpCode);
        // } catch (\Exception $e) {}

        return response()->json([
            'status' => 'success',
            'message' => 'Kode OTP telah dikirim ke email Anda. Berlaku selama 5 menit.',
            'data' => [
                'email' => $request->email
            ]
        ], 200);
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'otp_code' => 'required|string|size:4'
        ]);

        $cacheKey = 'register_otp_' . $request->email;
        $tempData = Cache::get($cacheKey);

        if (!$tempData) {
            return response()->json([
                'status' => 'error',
                'message' => 'Sesi pendaftaran telah kadaluarsa. Silakan daftar ulang.'
            ], 400);
        }

        if ($tempData['otp_code'] !== $request->otp_code) {
            return response()->json([
                'status' => 'error',
                'message' => 'Kode OTP salah.'
            ], 400);
        }

        // Jika benar, buat user
        $user = User::create([
            'nik' => null,
            'username' => $tempData['username'],
            'name' => $tempData['username'],
            'email' => $tempData['email'],
            'phone' => $tempData['phone'],
            'address' => '-',
            'gender' => 'laki-laki',
            'region_id' => $tempData['region_id'],
            'password' => $tempData['password'],
        ]);

        Cache::forget($cacheKey);

        $token = $user->createToken('flutter-mobile-app')->plainTextToken;

        return response()->json([
            'status' => 'success',
            'message' => 'Registrasi berhasil. Akun Anda telah diverifikasi.',
            'data' => [
                'user' => $user,
                'token' => $token
            ]
        ], 201);
    }

    public function user(Request $request)
    {
        $user = $request->user()->load('file');

        // null, BUKAN 'Belum ditentukan'.
        //
        // Teks itu dulu dikirim sebagai NILAI data, dan aplikasi mobile
        // memasukkannya apa adanya ke kolom RT. Saat disimpan, ia dikirim balik
        // sebagai rt = "Belum ditentukan" -- 16 karakter, ditolak validasi
        // 'rt' => 'nullable|string|max:10'. Operator melihatnya sebagai error
        // yang muncul entah dari mana, padahal ia tidak pernah mengetik apa pun.
        //
        // Kosong berarti KOSONG. Kalimat "Belum ditentukan" itu urusan
        // tampilan, dan tempatnya di lapisan tampilan -- bukan di nilai yang
        // akan dikirim balik ke server sebagai data.
        $kecamatan_name = null;
        $desa_name = null;
        $rw_name = null;
        $rt_name = null;

        if ($user->region_id) {
            $currentRegion = \App\Models\Region::find($user->region_id);

            while ($currentRegion) {
                if ($currentRegion->type == 'rt') {
                    $rt_name = $currentRegion->name;
                } elseif ($currentRegion->type == 'rw') {
                    $rw_name = $currentRegion->name;
                } elseif ($currentRegion->type == 'desa') {
                    $desa_name = $currentRegion->name;
                } elseif ($currentRegion->type == 'kecamatan') {
                    $kecamatan_name = $currentRegion->name;
                }

                if ($currentRegion->parent_id) {
                    $currentRegion = \App\Models\Region::find($currentRegion->parent_id);
                } else {
                    $currentRegion = null;
                }
            }
        }

        // Prioritaskan nilai RT dan RW langsung dari tabel user jika ada
        if (!empty($user->rw) && $user->rw !== '-') {
            $rw_name = $user->rw;
        }
        if (!empty($user->rt) && $user->rt !== '-') {
            $rt_name = $user->rt;
        }

        $avatar_url = null;
        if ($user->file && Storage::disk('local')->exists($user->file->path)) {
            $avatar_url = route('media.profile', ['filename' => $user->file->filename]);
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'user' => $user,
                'region_info' => [
                    'kecamatan' => $kecamatan_name,
                    'desa' => $desa_name,
                    'rw' => $rw_name,
                    'rt' => $rt_name,
                ],
                'avatar_url' => $avatar_url,
            ]
        ]);
    }

    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'rt' => 'nullable|string|max:10',
            'rw' => 'nullable|string|max:10',
            'gender' => 'nullable|in:laki-laki,perempuan',
            'profile' => 'nullable|image|mimes:jpg,jpeg,png|max:8192',
        ]);

        $user->update([
            'name' => $validated['name'],
            'phone' => $validated['phone'],
            'address' => $validated['address'],
            'rt' => $validated['rt'] ?? $user->rt,
            'rw' => $validated['rw'] ?? $user->rw,
            'gender' => $validated['gender'],
        ]);

        if ($request->hasFile('profile')) {
            if ($user->file) {
                if (Storage::disk('local')->exists($user->file->path)) {
                    Storage::delete($user->file->path);
                }
                $user->file->delete();
            }

            $file = $request->file('profile');
            $extension = strtolower($file->extension());
            
            $allowedExtensions = ['jpg', 'jpeg', 'png'];
            if (!in_array($extension, $allowedExtensions)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Format file profile tidak valid.'
                ], 403);
            }

            $filename = $user->id . '_' . time() . '_' . \Illuminate\Support\Str::random(10) . '.' . $extension;
            $path = $file->storeAs('profiles', $filename, ['disk' => 'local']);

            $user->file()->create([
                'alias' => 'profile_picture',
                'filename' => $filename,
                'path' => $path,
                'mime_type' => $file->getMimeType(),
                'size' => $file->getSize(),
            ]);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Profil berhasil diperbarui'
        ]);
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        $user = $request->user();

        if (!Hash::check($request->current_password, $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['Password saat ini tidak cocok.'],
            ]);
        }

        $user->update([
            'password' => Hash::make($request->new_password),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Password berhasil diubah',
        ]);
    }

    public function updateFcmToken(Request $request)
    {
        $request->validate([
            'fcm_token' => 'required|string',
        ]);

        $user = $request->user();
        $user->update([
            'fcm_token' => $request->fcm_token,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'FCM Token berhasil diperbarui',
        ]);
    }

    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email_or_phone' => 'required|string',
            'otp_method' => 'required|in:email,whatsapp',
        ]);

        $user = User::where('email', $request->email_or_phone)
                    ->orWhere('phone', $request->email_or_phone)
                    ->first();

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Email atau Nomor Telepon tidak terdaftar'
            ], 404);
        }

        // Generate 4 digit OTP
        $otpCode = str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);

        $user->otp_code = $otpCode;
        $user->otp_expires_at = now()->addMinutes(5);
        $user->save();

        if ($request->otp_method === 'email') {
            Mail::to($user->email)->queue(new OtpMail($otpCode));
        } elseif ($request->otp_method === 'whatsapp') {
            $fonnte = new FonnteService();
            $fonnte->sendOtp($user->phone, $otpCode);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Kode OTP telah dikirim ke ' . $request->otp_method
        ], 200);
    }

    public function verifyForgotPasswordOtp(Request $request)
    {
        $request->validate([
            'email_or_phone' => 'required|string',
            'otp' => 'required|digits:4',
        ]);

        $user = User::where('email', $request->email_or_phone)
                    ->orWhere('phone', $request->email_or_phone)
                    ->first();

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'User tidak ditemukan'
            ], 404);
        }

        if ($user->otp_code !== $request->otp) {
            return response()->json([
                'status' => 'error',
                'message' => 'Kode OTP tidak valid'
            ], 400);
        }

        if (now()->greaterThan($user->otp_expires_at)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Kode OTP sudah kadaluarsa'
            ], 400);
        }

        // Generate reset token
        $resetToken = \Illuminate\Support\Str::random(60);
        $user->reset_token = hash('sha256', $resetToken);
        $user->reset_token_expires_at = now()->addMinutes(15);
        $user->otp_code = null;
        $user->otp_expires_at = null;
        $user->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Kode OTP valid. Silahkan buat kata sandi baru.',
            'data' => [
                'reset_token' => $resetToken
            ]
        ], 200);
    }

    public function resetForgotPassword(Request $request)
    {
        $request->validate([
            'email_or_phone' => 'required|string',
            'reset_token' => 'required|string',
            'password' => 'required|min:8|confirmed',
        ]);

        $user = User::where('email', $request->email_or_phone)
                    ->orWhere('phone', $request->email_or_phone)
                    ->first();

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'User tidak ditemukan'
            ], 404);
        }

        if (!$user->reset_token || hash('sha256', $request->reset_token) !== $user->reset_token) {
            return response()->json([
                'status' => 'error',
                'message' => 'Token reset tidak valid'
            ], 400);
        }

        if (now()->greaterThan($user->reset_token_expires_at)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Token reset sudah kadaluarsa'
            ], 400);
        }

        // Pastikan kata sandi baru tidak sama dengan yang lama
        if (Hash::check($request->password, $user->password)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Anda tidak boleh menggunakan kata sandi yang lama'
            ], 400);
        }

        $user->password = Hash::make($request->password);
        $user->reset_token = null;
        $user->reset_token_expires_at = null;
        $user->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Password berhasil diperbarui',
        ], 200);
    }
}
