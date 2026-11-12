<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{ActivityLog, FamilyCard, FamilyMember, Notification, Region, User};
use App\Services\{FileEncryptionService, IdentityMediaService};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{DB, Storage};
use Illuminate\Validation\ValidationException;

class GasKkAdminController extends Controller
{
    private function scoped()
    {
        $user = auth()->user();
        abort_unless($user && in_array($user->role, ['admin_desa', 'super_admin'], true) && $user->region_id, 403);
        $ids = array_merge([$user->region_id], Region::getDescendantIds($user->region_id));
        return FamilyCard::whereHas('submitter', fn ($q) => $q->whereIn('region_id', $ids));
    }

    public function index()
    {
        $pengajuan = $this->scoped()->with('submitter')->where('status', 'pending')->oldest()->paginate(15);
        return view('admin.gas.verifikasi_kk', compact('pengajuan'));
    }

    public function showImage($id)
    {
        $kk = $this->scoped()->where('status', 'pending')->findOrFail($id);
        $path = $kk->kk_image_path;
        abort_unless($path && str_starts_with($path, 'kk_images/') && !str_contains($path, '..') && Storage::disk('private')->exists($path), 404);
        // Legacy uploads were private but unencrypted. Upgrade before using the same watermarked renderer.
        $bytes = Storage::disk('private')->get($path);
        if (!str_starts_with($bytes, '$chacha20$')) {
            abort_unless(@getimagesizefromstring($bytes), 404);
            abort_unless(Storage::disk('private')->put($path, FileEncryptionService::encrypt($bytes)), 503);
        }
        return app(IdentityMediaService::class)->render($path, 'ktp');
    }

    public function approve(Request $request, $id)
    {
        $this->scoped()->findOrFail($id);
        if (is_string($request->niks)) $request->merge(['niks' => preg_split('/[\s,;]+/', trim($request->niks), -1, PREG_SPLIT_NO_EMPTY)]);
        $data = $request->validate(['no_kk' => 'required|digits:16', 'kepala_keluarga' => 'required|string|max:255',
            'niks' => 'required|array|min:1|max:30', 'niks.*' => 'required|digits:16|distinct']);
        return $this->decide($request, $id, function ($kk) use ($data) {
            $hash = hash_hmac('sha256', $data['no_kk'], config('app.key'));
            $niks = array_map(fn ($nik) => hash_hmac('sha256', $nik, config('app.key')), $data['niks']);
            if (!in_array($kk->submitter?->nik_hash, $niks, true)) throw ValidationException::withMessages(['niks' => 'NIK pemohon harus tercantum sebagai anggota KK.']);
            if (FamilyCard::where('no_kk_hash', $hash)->where('id', '!=', $kk->id)->exists()) throw ValidationException::withMessages(['no_kk' => 'KK ini sudah terdaftar. Periksa data keluarga yang ada; jangan membuat KK ganda.']);
            if (FamilyMember::whereIn('nik_hash', $niks)->where('family_card_id', '!=', $kk->id)->exists()) throw ValidationException::withMessages(['niks' => 'Ada NIK yang sudah terdaftar di KK lain. Periksa perpindahan keluarga terlebih dahulu; keanggotaan tidak dipindahkan otomatis.']);
            $kk->no_kk_hash = $hash;
            $kk->no_kk_masked = substr($data['no_kk'], 0, 4).'********'.substr($data['no_kk'], -4);
            $kk->kepala_keluarga_masked = mb_substr($data['kepala_keluarga'], 0, 1).'***';
            $kk->status = 'approved';
            foreach ($niks as $nik) FamilyMember::create(['family_card_id' => $kk->id, 'nik_hash' => $nik]);
        });
    }

    public function reject(Request $request, $id)
    {
        $this->scoped()->findOrFail($id);
        $request->validate(['reason' => 'required|string|max:500']);
        return $this->decide($request, $id, function ($kk) use ($request) { $kk->status = 'rejected'; $kk->admin_notes = $request->reason; });
    }

    private function decide(Request $request, $id, callable $decision)
    {
        DB::transaction(function () use ($id, $request, $decision) {
            $kk = $this->scoped()->lockForUpdate()->findOrFail($id);
            abort_unless($kk->status === 'pending', 409, 'Pengajuan ini sudah diperiksa.');
            $decision($kk);
            $path = $kk->kk_image_path;
            $kk->reviewed_by = auth()->id();
            $kk->reviewed_at = now();
            $kk->kk_image_path = null;
            $kk->save();
            Notification::create(['user_id' => $kk->submitted_by, 'type' => 'status_berubah', 'title' => 'Hasil Pemeriksaan KK',
                'message' => $kk->status === 'approved' ? 'KK disetujui. Anda dapat memesan gas sesuai kuota wilayah.' : 'KK belum disetujui: '.$kk->admin_notes,
                'link' => route('gas.sales'), 'is_read' => false]);
            ActivityLog::create(['user_id' => auth()->id(), 'action' => 'review_gas_kk', 'description' => 'Pengajuan KK #'.$kk->id.' '.$kk->status, 'ip_address' => $request->ip()]);
            // Retain the file reference until deletion succeeds. Failed cleanup can be retried by the operator.
            DB::afterCommit(function () use ($kk, $path) {
                if ($path && !Storage::disk('private')->delete($path)) {
                    $kk->updateQuietly(['kk_image_path' => $path]);
                    \Log::warning('Pembersihan foto KK perlu diulang.', ['family_card_id' => $kk->id]);
                }
            });
        });
        return back()->with('success', 'Hasil pemeriksaan tersimpan dan notifikasi dikirim kepada pemohon.');
    }
}
