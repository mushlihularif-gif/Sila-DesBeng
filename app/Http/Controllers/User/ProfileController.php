<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\OtpMail;

class ProfileController extends Controller
{
    /**
     * Tampilkan halaman profil
     */
    public function index()
    {
        if (!auth()->check()) {
            return redirect()->route('beranda')->with('error', 'Silakan login terlebih dahulu');
        }

        $user = auth()->user();
        return view('users.profile', compact('user'));
    }

    /**
     * Perbarui data profil
     */
    public function update(Request $request)
    {
        if (!auth()->check()) {
            return redirect()->route('beranda')->with('error', 'Silakan login terlebih dahulu');
        }

        $user = auth()->user();

        $validated = $request->validate([
            'username' => 'required|string|max:255|unique:users,username,' . $user->id,
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'gender' => 'nullable|in:laki-laki,perempuan',
            'profile' => 'nullable|image|mimes:jpg,jpeg,png|max:8192',
        ], [
            'username.required' => 'Username harus diisi',
            'name.required' => 'Nama harus diisi',
            'profile.image' => 'File harus berupa gambar',
            'profile.mimes' => 'Format file harus JPG, JPEG, atau PNG',
            'profile.max' => 'Ukuran file maksimal 8MB',
        ]);

        $user->update([
            'username' => $validated['username'],
            'name' => $validated['name'],
            'phone' => $validated['phone'],
            'address' => $validated['address'],
            'gender' => $validated['gender'],
        ]);

        // Tangani unggahan atau penghapusan avatar
        if ($request->hasFile('profile') || $request->input('delete_avatar') == '1') {
            // Hapus avatar lama jika ada
            if ($user->file) {
                // Periksa apakah file ada di penyimpanan sebelum menghapus
                if (Storage::disk('local')->exists($user->file->path)) {
                    Storage::delete($user->file->path);
                }
                $user->file->delete();
            }
        }

        if ($request->hasFile('profile')) {
            $file = $request->file('profile');
            $extension = $file->getClientOriginalExtension();
            $filename = $user->id . '_' . time() . '.' . $extension;
            $path = $file->storeAs('profiles', $filename, ['disk' => 'local']);

            $user->file()->create([
                'alias' => 'profile_picture',
                'filename' => $filename,
                'path' => $path,
                'mime_type' => $file->getMimeType(),
                'size' => $file->getSize(),
            ]);
        }

        return back()->with('success', 'Profil berhasil diperbarui');
    }

    /**
     * LANGKAH 1: Kirim OTP untuk ganti password
     */
    public function changePassword(Request $request)
    {
        if (!auth()->check()) {
            return response()->json([
                'success' => false,
                'message' => 'Silakan login terlebih dahulu'
            ], 401);
        }

        try {
            $validated = $request->validate([
                'current_password' => 'required',
                'new_password' => 'required|min:8|confirmed',
            ], [
                'current_password.required' => 'Password lama harus diisi',
                'new_password.required' => 'Password baru harus diisi',
                'new_password.min' => 'Password baru minimal 8 karakter',
                'new_password.confirmed' => 'Konfirmasi password tidak cocok',
            ]);

            $user = auth()->user();

            // Verifikasi password saat ini
            if (!Hash::check($validated['current_password'], $user->password)) {
                return response()->json([
                    'success' => false,
                    'errors' => [
                        'current_password' => ['Password lama tidak sesuai']
                    ]
                ], 422);
            }

            // Buat OTP
            $otpCode = str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);

            // Simpan ke sesi (BUKAN database)
            session([
                'profile_password_change' => [
                    'user_id' => $user->id,
                    'new_password' => $validated['new_password'],
                    'otp_code' => $otpCode,
                    'otp_expires_at' => now()->addMinutes(5),
                ]
            ]);

            // Send OTP via Email
            Mail::to($user->email)->send(new OtpMail($otpCode));

            return response()->json([
                'success' => true,
                'message' => 'Kode OTP telah dikirim ke email Anda',
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Change password error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan'
            ], 500);
        }
    }

    /**
     * LANGKAH 2: Verifikasi OTP dan perbarui password
     */
    public function verifyOtp(Request $request)
    {
        if (!auth()->check()) {
            return response()->json([
                'success' => false,
                'message' => 'Silakan login terlebih dahulu'
            ], 401);
        }

        try {
            $validated = $request->validate([
                'otp' => 'required|digits:4',
            ]);

            $sessionData = session('profile_password_change');

            if (!$sessionData) {
                return response()->json([
                    'success' => false,
                    'message' => 'Session tidak valid'
                ], 400);
            }

            // Periksa kadaluarsa
            if (now()->greaterThan($sessionData['otp_expires_at'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kode OTP sudah kadaluarsa'
                ], 400);
            }

            // Verifikasi OTP
            if ($sessionData['otp_code'] !== $validated['otp']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kode OTP tidak valid'
                ], 400);
            }

            // Perbarui password
            $user = auth()->user();
            $user->update([
                'password' => Hash::make($sessionData['new_password'])
            ]);

            // Hapus sesi
            session()->forget('profile_password_change');

            return response()->json([
                'success' => true,
                'message' => 'Password berhasil diperbarui'
            ], 200);

        } catch (\Exception $e) {
            Log::error('Verify OTP error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan'
            ], 500);
        }
    }

    /**
     * LANGKAH 3: Kirim ulang OTP
     */
    public function resendOtp(Request $request)
    {
        if (!auth()->check()) {
            return response()->json([
                'success' => false,
                'message' => 'Silakan login terlebih dahulu'
            ], 401);
        }

        try {
            $sessionData = session('profile_password_change');

            if (!$sessionData) {
                return response()->json([
                    'success' => false,
                    'message' => 'Session tidak valid'
                ], 400);
            }

            // Buat OTP baru
            $newOtpCode = str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);
            $sessionData['otp_code'] = $newOtpCode;
            $sessionData['otp_expires_at'] = now()->addMinutes(5);
            session(['profile_password_change' => $sessionData]);

            $user = auth()->user();
            // Send OTP via Email
            Mail::to($user->email)->send(new OtpMail($newOtpCode));

            return response()->json([
                'success' => true,
                'message' => 'Kode OTP baru telah dikirim ke email Anda',
            ], 200);

        } catch (\Exception $e) {
            Log::error('Resend OTP error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengirim ulang OTP'
            ], 500);
        }
    }
}