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
        
        if ($user->verification_status === 'verified') {
            return redirect()->route('profile')->with('info', 'Akun Anda sudah diverifikasi.');
        }

        return view('users.verifikasi', compact('user'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'ktp_photo' => 'required|image|max:5120',
            'face_photo' => 'required|image|max:5120',
        ]);

        $user = Auth::user();

        // Ambil isi file asli KTP & Face
        $ktpFile = $request->file('ktp_photo');
        $faceFile = $request->file('face_photo');

        app(\App\Services\IdentityDocumentStorage::class)->save($user, [
            'ktp' => $ktpFile->get(),
            'face' => $faceFile->get(),
        ], ['ktp_rejection_reason' => null, 'verification_status' => 'pending']);

        return redirect()->back()->with('success', 'Data verifikasi berhasil dikirim. Harap tunggu persetujuan Admin.');
    }
}
