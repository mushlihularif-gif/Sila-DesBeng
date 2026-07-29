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
            return redirect()->route('user.profile')->with('info', 'Akun Anda sudah diverifikasi.');
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

        // Enkripsi isi file
        $ktpEncrypted = \App\Services\FileEncryptionService::encrypt($ktpFile->get());
        $faceEncrypted = \App\Services\FileEncryptionService::encrypt($faceFile->get());

        // Simpan file terenkripsi ke disk private
        $ktpPath = 'verifications/ktp/ktp_' . uniqid() . '.enc';
        $facePath = 'verifications/face/face_' . uniqid() . '.enc';
        
        Storage::disk('private')->put($ktpPath, $ktpEncrypted);
        Storage::disk('private')->put($facePath, $faceEncrypted);

        $user->ktp_photo_path = $ktpPath;
        $user->face_photo_path = $facePath;
        $user->ktp_rejection_reason = null; // reset alasan
        $user->verification_status = 'pending';
        $user->save();

        return redirect()->back()->with('success', 'Data verifikasi berhasil dikirim. Harap tunggu persetujuan Admin.');
    }
}
