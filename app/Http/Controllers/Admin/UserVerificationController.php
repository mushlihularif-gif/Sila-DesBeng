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

        abort_unless(in_array($type, ['ktp', 'face'], true), 404);
        abort_unless($user->verification_status === 'pending', 404);
        $path = $type === 'ktp' ? $user->ktp_photo_path : $user->face_photo_path;
        abort_unless($path, 404);
        return app(\App\Services\IdentityMediaService::class)->render($path, $type);
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
