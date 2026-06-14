<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\OtpMail;

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
            'email' => 'required|email|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'position' => 'nullable|string|max:255',
            'gender' => 'nullable|in:laki-laki,perempuan',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:8192',
        ]);

        // Handle avatar upload or deletion
        if ($request->hasFile('avatar') || $request->input('delete_avatar') == '1') {
            // Delete old file if exists
            if ($user->file) {
                if (Storage::disk('local')->exists($user->file->path)) {
                    Storage::disk('local')->delete($user->file->path);
                }
                $user->file()->delete();
            }
        }

        if ($request->hasFile('avatar')) {
            // Store new avatar in private storage (local disk)
            $file = $request->file('avatar');
            $extension = $file->getClientOriginalExtension();
            $filename = $user->id . '_' . time() . '.' . $extension;
            $path = $file->storeAs('profiles', $filename, ['disk' => 'local']);

            // Create new file record
            $user->file()->create([
                'alias' => 'admin_avatar',
                'filename' => $filename,
                'path' => $path,
                'mime_type' => $file->getMimeType(),
                'size' => $file->getSize(),
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
        
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'current_password' => 'required',
            'new_password' => 'required|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ]);
        }

        // Verify current password
        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json(['success' => false, 'message' => 'Kata sandi lama tidak sesuai.']);
        }

        // Generate OTP
        $otp = rand(1000, 9999);
        
        // Save OTP to Database
        $user->otp_code = $otp;
        $user->otp_expires_at = now()->addMinutes(5);
        $user->save();

        // Store hashed new password in session for security
        session(['new_password_hash' => Hash::make($request->new_password)]);

        // Send OTP via Email
        Mail::to($user->email)->send(new OtpMail($otp));

        return response()->json([
            'success' => true, 
            'message' => 'OTP telah dikirim ke email Anda.',
        ]);
    }

    public function verifyOtp(Request $request)
    {
        $user = Auth::user();
        
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'otp' => 'required|digits:4',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ]);
        }

        // Check OTP from Database
        if (!$user->otp_code || !$user->otp_expires_at || now()->gt($user->otp_expires_at)) {
            return response()->json(['success' => false, 'message' => 'Kode OTP telah kedaluwarsa.']);
        }

        if ($request->otp != $user->otp_code) {
            return response()->json(['success' => false, 'message' => 'Kode OTP tidak valid.']);
        }

        // Update password
        $user->password = session('new_password_hash');
        
        // Clear OTP in Database
        $user->otp_code = null;
        $user->otp_expires_at = null;
        $user->save();

        // Clear session
        session()->forget(['new_password_hash']);

        return response()->json(['success' => true, 'message' => 'Kata sandi berhasil diperbarui.']);
    }

    public function resendOtp()
    {
        $user = Auth::user();

        // Generate New OTP
        $otp = rand(1000, 9999);
        
        // Update to Database
        $user->otp_code = $otp;
        $user->otp_expires_at = now()->addMinutes(5);
        $user->save();

        // Send OTP via Email
        Mail::to($user->email)->send(new OtpMail($otp));

        return response()->json([
            'success' => true, 
            'message' => 'Kode OTP baru telah dikirim ke email Anda.',
        ]);
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