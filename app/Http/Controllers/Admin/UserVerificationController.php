<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Region;
use App\Models\User;
use App\Services\IdentityMediaService;
use App\Services\IdentityReviewService;
use Illuminate\Http\Request;
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
            in_array(Auth::user()?->role, ['super_admin', 'admin_desa'], true),
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

        if ($admin->role === 'super_admin') {
            return null;
        }

        if (! $admin->region_id) {
            return [];
        }

        return array_merge([$admin->region_id], Region::getDescendantIds($admin->region_id));
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

        // Pengajuan KYC baru memiliki antrean sendiri. Halaman kompatibilitas
        // hanya menampilkan foto yang memang tersimpan pada akun lama.
        $query = User::where('verification_status', 'pending')
            ->where(fn ($q) => $q->whereNotNull('ktp_photo_path')->orWhereNotNull('face_photo_path'))
            ->whereNotExists(function ($q) {
                $q->selectRaw('1')->from('kyc_verifications')
                    ->whereColumn('kyc_verifications.user_id', 'users.id')
                    ->whereIn('kyc_verifications.status', ['draft', 'pending']);
            });

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

        return app(IdentityMediaService::class)->renderIdentity($user, $type);
    }

    public function approve($id)
    {
        $this->pastikanPeninjau();
        $cleaned = app(IdentityReviewService::class)
            ->reviewLegacy((int) $id, Auth::user(), true, null);

        return $this->reviewResponse($cleaned, 'Verifikasi warga disetujui.');
    }

    public function reject(Request $request, $id)
    {
        $this->pastikanPeninjau();
        $data = $request->validate(['reason' => 'required|string|max:500']);
        $cleaned = app(IdentityReviewService::class)
            ->reviewLegacy((int) $id, Auth::user(), false, $data['reason']);

        return $this->reviewResponse($cleaned, 'Verifikasi warga ditolak.');
    }

    private function reviewResponse(bool $cleaned, string $message)
    {
        return redirect()->back()->with($cleaned ? 'success' : 'warning', $message.' '.($cleaned
            ? 'Berkas foto telah dihapus dari penyimpanan aplikasi.'
            : 'Pembersihan berkas belum selesai. Hubungi pengelola untuk mengulang pembersihan.'));
    }
}
