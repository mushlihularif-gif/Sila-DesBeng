<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\OtpMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProfileController extends Controller
{
    public function index()
    {
        // Get the authenticated admin user
        $user = Auth::user();

        // Pass real admin data to view
        return view('admin.profile.index', compact('user'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,'.$user->id,
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'position' => 'nullable|string|max:255',
            'gender' => 'nullable|in:laki-laki,perempuan',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:8192',
        ]);

        // Handle avatar upload or deletion
        if ($request->hasFile('avatar') || $request->input('delete_avatar') == '1') {
            // Delete old file if exists
            if ($user->file) {
                if (Storage::disk('local')->exists($user->file->path)) {
                    Storage::disk('local')->delete($user->file->path);
                } elseif (Storage::disk('public')->exists($user->file->path)) {
                    Storage::disk('public')->delete($user->file->path);
                }
                $user->file()->delete();
            }
        }

        if ($request->hasFile('avatar')) {
            // Store new avatar in private storage (local disk)
            $file = $request->file('avatar');
            $extension = strtolower($file->getClientOriginalExtension() ?: $file->extension() ?: 'jpg');

            // Strict whitelist extension
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
            if (! in_array($extension, $allowedExtensions)) {
                return back()->with('error', 'Format file avatar tidak valid.')->withInput();
            }

            $filename = $user->id.'_'.time().'_'.Str::random(10).'.'.$extension;
            $path = $file->storeAs('profiles', $filename, ['disk' => 'local']);

            // Create new file record
            $user->file()->create([
                'alias' => 'admin_avatar',
                'filename' => $filename,
                'path' => $path,
                'mime_type' => $file->getMimeType() ?: 'image/jpeg',
                'size' => $file->getSize() ?: 0,
            ]);
        }

        // Update user data
        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
            'position' => $request->position,
            'gender' => $request->gender,
        ]);

        return redirect()->back()->with('success', 'Profil berhasil diperbarui.');
    }

    public function changePassword(Request $request)
    {
        $user = Auth::user();
        $data = $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:12|confirmed',
        ]);
        if (! Hash::check($data['current_password'], $user->password)) {
            return response()->json(['success' => false, 'message' => 'Kata sandi lama tidak sesuai.'], 422);
        }

        $otp = (string) random_int(1000, 9999);
        session(['admin_password_change' => [
            'user_id' => $user->id,
            'original_hash' => $user->password,
            'new_hash' => Hash::make($data['new_password']),
            'otp_hash' => Hash::make($otp),
            'expires_at' => now()->addMinutes(5)->timestamp,
            'attempts' => 0,
            'resends' => 0,
        ]]);
        Mail::to($user->email)->send(new OtpMail($otp));

        return response()->json(['success' => true, 'message' => 'OTP telah dikirim ke email Anda.']);
    }

    private function pendingPasswordChange(): array
    {
        $pending = session('admin_password_change');
        $user = Auth::user();
        if (! is_array($pending) || (int) ($pending['user_id'] ?? 0) !== $user->id
            || ($pending['expires_at'] ?? 0) <= now()->timestamp
            || ($pending['attempts'] ?? 5) >= 5
            || ! hash_equals($user->password, $pending['original_hash'] ?? '')) {
            session()->forget('admin_password_change');
            abort(422, 'Permintaan perubahan kata sandi telah berakhir. Masukkan kembali kata sandi lama.');
        }

        return $pending;
    }

    public function verifyOtp(Request $request)
    {
        $request->validate(['otp' => 'required|digits:4']);
        $pending = $this->pendingPasswordChange();
        if (! Hash::check((string) $request->otp, $pending['otp_hash'])) {
            $pending['attempts']++;
            session(['admin_password_change' => $pending]);

            return response()->json(['success' => false, 'message' => 'Kode OTP tidak valid.'], 422);
        }

        DB::transaction(function () use ($pending, $request) {
            $user = User::lockForUpdate()->findOrFail(Auth::id());
            abort_unless(hash_equals($user->password, $pending['original_hash']), 409, 'Kata sandi telah berubah. Ulangi permintaan.');
            $user->password = $pending['new_hash'];
            $user->remember_token = Str::random(60);
            $user->save();
            // Sesi lain dan token aplikasi tidak lagi boleh memakai kredensial lama.
            if (config('session.driver') === 'database') {
                DB::table(config('session.table', 'sessions'))
                    ->where('user_id', $user->id)->where('id', '!=', $request->session()->getId())->delete();
            }
            $user->tokens()->delete();
        });
        session()->forget(['admin_password_change', 'new_password_hash']);
        $request->session()->regenerate();

        return response()->json(['success' => true, 'message' => 'Kata sandi berhasil diperbarui.']);
    }

    public function resendOtp()
    {
        $pending = $this->pendingPasswordChange();
        abort_if(($pending['resends'] ?? 0) >= 3, 429, 'Batas kirim ulang tercapai. Ulangi perubahan kata sandi.');
        $otp = (string) random_int(1000, 9999);
        $pending['otp_hash'] = Hash::make($otp);
        $pending['resends']++;
        // Kirim ulang tidak menghapus percobaan gagal atau memperpanjang batas waktu.
        session(['admin_password_change' => $pending]);
        Mail::to(Auth::user()->email)->send(new OtpMail($otp));

        return response()->json(['success' => true, 'message' => 'Kode OTP baru telah dikirim ke email Anda.']);
    }

    public function deleteAvatar()
    {
        $user = Auth::user();

        if ($user->file) {
            if (Storage::disk('local')->exists($user->file->path)) {
                Storage::disk('local')->delete($user->file->path);
            }
            $user->file()->delete();
        }

        return response()->json(['success' => true, 'message' => 'Avatar berhasil dihapus.']);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/'); // atau halaman login nanti
    }
}
