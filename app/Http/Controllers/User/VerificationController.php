<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VerificationController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        // Hanya yang status unverified atau pending/rejected yang butuh kesini
        // Jika sudah verified, bisa di-redirect atau tampilkan KTP digital.
        return view('users.verifikasi', compact('user'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'ktp_photo' => 'required|image|max:5120',
            'face_photo' => 'required|image|max:5120',
        ]);

        $user = Auth::user();

        // Menyimpan di Private Disk
        $ktpPath = $request->file('ktp_photo')->store('verifications/ktp', 'private');
        $facePath = $request->file('face_photo')->store('verifications/face', 'private');

        $user->ktp_photo_path = $ktpPath;
        $user->face_photo_path = $facePath;
        $user->verification_status = 'pending';
        $user->ktp_rejection_reason = null; // reset alasan
        $user->save();

        return redirect()->route('user.profile')->with('success', 'Data verifikasi berhasil dikirim. Silakan tunggu persetujuan Admin.');
    }
}
