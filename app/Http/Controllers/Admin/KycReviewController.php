<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KycVerification;
use App\Models\Region;
use App\Services\IdentityReviewService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class KycReviewController extends Controller
{
    /** Keputusan identitas menjadi kewenangan admin desa, bukan staf layanan. */
    private function pastikanPeninjau(): void
    {
        abort_unless(
            in_array(auth()->user()?->role, ['admin_desa'], true),
            403,
            'Peninjauan verifikasi identitas hanya untuk admin desa.'
        );
    }

    /** Wilayah desa peninjau beserta RW dan RT di bawahnya. */
    private function wilayahDitinjau(): array
    {
        $admin = Auth::user();

        return $admin->region_id
            ? array_merge([$admin->region_id], Region::getDescendantIds($admin->region_id))
            : [];
    }

    /** Tolak pengajuan milik wilayah di luar jangkauan peninjau. */
    private function pastikanDalamJangkauan(KycVerification $kyc): void
    {
        $wilayah = $this->wilayahDitinjau();

        abort_unless(
            in_array($kyc->user?->region_id, $wilayah),
            403,
            'Pengajuan ini milik warga wilayah lain.'
        );
    }

    public function index(Request $request)
    {
        $this->pastikanPeninjau();

        $wilayah = $this->wilayahDitinjau();

        $all = KycVerification::with('user')
            ->whereHas('user', fn ($query) => $query->whereIn('region_id', $wilayah))
            ->whereIn('status', ['pending', 'approved', 'rejected'])
            ->latest()->get();
        $pending = $all->where('status', 'pending')->values();
        $approved = $all->where('status', 'approved')->values();
        $rejected = $all->where('status', 'rejected')->values();

        $counts = [
            'all' => $all->count(),
            'pending' => $pending->count(),
            'approved' => $approved->count(),
            'rejected' => $rejected->count(),
        ];

        // Dipakai tabel untuk menandai mana warga wilayah sendiri dan mana yang
        // berasal dari wilayah bawahan.
        $wilayahSendiri = Auth::user()->region_id;
        $lingkup = (Region::find($wilayahSendiri)?->name ?? 'wilayah Anda').' dan wilayah di bawahnya';

        return view('admin.kyc.index', compact(
            'all', 'pending', 'approved', 'rejected', 'counts', 'wilayahSendiri', 'lingkup'
        ));
    }

    public function show($id)
    {
        $this->pastikanPeninjau();

        $kyc = KycVerification::with('user')->findOrFail($id);
        $this->pastikanDalamJangkauan($kyc);

        return view('admin.kyc.show', compact('kyc'));
    }

    public function approve(Request $request, $id)
    {
        $this->pastikanPeninjau();
        $data = $request->validate(['admin_notes' => 'nullable|string|max:1000']);
        $cleaned = app(IdentityReviewService::class)
            ->reviewKyc((int) $id, Auth::user(), true, $data['admin_notes'] ?? null);

        return $this->reviewResponse($cleaned, 'Verifikasi disetujui.');
    }

    public function reject(Request $request, $id)
    {
        $this->pastikanPeninjau();
        $data = $request->validate(['admin_notes' => 'required|string|max:1000']);
        $cleaned = app(IdentityReviewService::class)
            ->reviewKyc((int) $id, Auth::user(), false, $data['admin_notes']);

        return $this->reviewResponse($cleaned, 'Verifikasi ditolak.');
    }

    private function reviewResponse(bool $cleaned, string $message)
    {
        return redirect()->route('admin.kyc.index')->with($cleaned ? 'success' : 'warning', $message.' '.($cleaned
            ? 'Berkas foto telah dihapus dari penyimpanan aplikasi.'
            : 'Pembersihan berkas belum selesai. Hubungi pengelola untuk mengulang pembersihan.'));
    }
}
