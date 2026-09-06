<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class UserVerificationController extends Controller
{
    /**
     * Sama seperti KycReviewController: memeriksa identitas warga adalah
     * kewenangan pemerintah wilayah, bukan staf unit layanan. Grup rutenya
     * memakai `role:admin` yang di CheckRole ikut meloloskan 'staff'.
     */
    private function pastikanPeninjau(): void
    {
        abort_unless(
            in_array(Auth::user()?->role, ['super_admin', 'admin', 'admin_kecamatan', 'admin_desa'], true),
            403,
            'Peninjauan verifikasi identitas hanya untuk admin wilayah dan Super Admin.'
        );
    }

    /**
     * Wilayah yang warganya boleh diperiksa akun ini: wilayahnya sendiri
     * ditambah seluruh wilayah di bawahnya. null berarti tanpa batas.
     *
     * @return array<int>|null
     */
    private function wilayahDitinjau(): ?array
    {
        $admin = Auth::user();

        if ($admin->role === 'super_admin' || ($admin->role === 'admin' && ! $admin->region_id)) {
            return null;
        }

        if (! $admin->region_id) {
            return [];
        }

        return array_merge([$admin->region_id], \App\Models\Region::getDescendantIds($admin->region_id));
    }

    private function pastikanDalamJangkauan(User $warga): void
    {
        $wilayah = $this->wilayahDitinjau();

        if ($wilayah === null) {
            return;
        }

        abort_unless(in_array($warga->region_id, $wilayah), 403, 'Warga ini berada di wilayah lain.');
    }

    public function index()
    {
        $this->pastikanPeninjau();

        $query = User::where('verification_status', 'pending');

        // Dulu hanya admin_desa/RT/RW yang dibatasi, sehingga admin kecamatan
        // ikut melihat warga kecamatan LAIN - bukan cuma desa bawahannya.
        $wilayah = $this->wilayahDitinjau();

        if ($wilayah !== null) {
            $query->whereIn('region_id', $wilayah);
        }

        $pendingUsers = $query->orderBy('updated_at', 'asc')->paginate(10);

        return view('admin.warga.verifikasi', compact('pendingUsers'));
    }

    public function viewImage($type, $id)
    {
        $this->pastikanPeninjau();

        $user = User::findOrFail($id);
        $this->pastikanDalamJangkauan($user);

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
        $this->pastikanPeninjau();

        $user = User::findOrFail($id);
        $this->pastikanDalamJangkauan($user);

        $user->verification_status = 'verified';
        $user->verified_at = now();
        $user->save();

        return redirect()->back()->with('success', "Warga {$user->name} berhasil diverifikasi!");
    }

    public function reject(Request $request, $id)
    {
        $this->pastikanPeninjau();

        $request->validate([
            'reason' => 'required|string|max:500'
        ]);

        $user = User::findOrFail($id);
        $this->pastikanDalamJangkauan($user);

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
