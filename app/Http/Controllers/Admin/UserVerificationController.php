<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class UserVerificationController extends Controller
{
    public function index()
    {
        $admin = Auth::user();
        
        // Admin desa hanya melihat warganya saja
        $query = User::where('verification_status', 'pending');
        if (in_array($admin->role, ['admin_desa', 'admin_rt', 'admin_rw'])) {
            $query->where('region_id', $admin->region_id);
        }
        
        $pendingUsers = $query->orderBy('updated_at', 'asc')->paginate(10);
        return view('admin.warga.verifikasi', compact('pendingUsers'));
    }

    public function viewImage($type, $id)
    {
        $user = User::findOrFail($id);
        $admin = Auth::user();

        // Validasi akses region
        if (in_array($admin->role, ['admin_desa', 'admin_rt', 'admin_rw']) && $user->region_id != $admin->region_id) {
            abort(403, 'Anda tidak berhak melihat data wilayah lain.');
        }

        $path = $type === 'ktp' ? $user->ktp_photo_path : $user->face_photo_path;

        if (!$path || !Storage::disk('private')->exists($path)) {
            abort(404, 'File tidak ditemukan.');
        }

        $filePath = storage_path('app/private/' . $path);

        try {
            // Membaca file gambar dari storage private
            $fileContent = file_get_contents($filePath);
            
            // Coba dekripsi jika dienkripsi dengan ChaCha20
            $decryptedContent = \App\Services\FileEncryptionService::decrypt($fileContent);
            
            // Jika hasil dekripsi kosong, asumsikan ini file lama yang belum dienkripsi
            $imageContent = $decryptedContent ?: $fileContent;

            // Buat image manager dengan GD driver
            $manager = \Intervention\Image\ImageManager::gd();
            
            // Baca gambar dari binary data
            $image = $manager->read($imageContent);

            $width = $image->width();
            $height = $image->height();

            // Hanya berikan watermark pada KTP/KK, foto wajah dibiarkan bersih
            if ($type === 'ktp' || $type === 'kk') {
                $watermarkPath = public_path('Admin/img/watermarkprivasi/WatermarkPrivasi.png');
                if (file_exists($watermarkPath)) {
                    $watermark = $manager->read($watermarkPath);
                    $watermarkWidth = intval($width * 0.9);
                    $watermark->scaleDown(width: $watermarkWidth);
                    $image->place($watermark, 'center');
                }
            }

            // Encode gambar sebagai JPEG
            $encoded = $image->toJpeg(85);

            return response($encoded->toString(), 200, [
                'Content-Type' => 'image/jpeg',
                'Content-Disposition' => 'inline',
                // Header keamanan: larang browser menyimpan cache gambar
                'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
                'Pragma' => 'no-cache',
                'Expires' => '0',
            ]);
        } catch (\Exception $e) {
            // Fallback: jika watermark gagal, tetap tampilkan gambar asli yang sudah didekripsi
            \Log::warning('Watermark gagal diterapkan: ' . $e->getMessage());
            
            // Return raw image bytes karena $imageContent sudah didekripsi
            return response($imageContent, 200, [
                'Content-Type' => 'image/jpeg',
                'Content-Disposition' => 'inline',
                'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
            ]);
        }
    }

    public function approve($id)
    {
        $user = User::findOrFail($id);
        $user->verification_status = 'verified';
        $user->verified_at = now();
        $user->save();

        return redirect()->back()->with('success', "Warga {$user->name} berhasil diverifikasi!");
    }

    public function reject(Request $request, $id)
    {
        $request->validate([
            'reason' => 'required|string|max:500'
        ]);

        $user = User::findOrFail($id);
        $user->verification_status = 'rejected';
        $user->ktp_rejection_reason = $request->reason;
        
        // Hapus file yang jelek agar tidak menuhi storage
        if ($user->ktp_photo_path) Storage::disk('private')->delete($user->ktp_photo_path);
        if ($user->face_photo_path) Storage::disk('private')->delete($user->face_photo_path);
        
        $user->ktp_photo_path = null;
        $user->face_photo_path = null;
        $user->save();

        return redirect()->back()->with('success', "Verifikasi warga {$user->name} ditolak karena: {$request->reason}");
    }
}
