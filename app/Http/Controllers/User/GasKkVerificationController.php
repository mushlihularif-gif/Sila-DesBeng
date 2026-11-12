<?php
namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\{FamilyCard, User};
use App\Services\FileEncryptionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{DB, Storage};
use Illuminate\Support\Str;

class GasKkVerificationController extends Controller
{
    public function store(Request $request)
    {
        abort_unless($request->user()?->verification_status === 'verified' && $request->user()?->region_id, 403, 'Verifikasi identitas dan wilayah akun terlebih dahulu.');
        $request->validate(['kk_image' => 'required|image|mimes:jpg,jpeg,png,webp|max:10240|dimensions:max_width=8000,max_height=8000']);
        $path = null;
        try {
            DB::transaction(function () use ($request, &$path) {
                User::whereKey($request->user()->id)->lockForUpdate()->firstOrFail();
                abort_if(app(\App\Services\GasOrdering::class)->household($request->user()), 409, 'KK Anda sudah terdaftar. Hubungi admin desa jika susunan keluarga berubah.');
                abort_if(FamilyCard::where('submitted_by', $request->user()->id)->where('status', 'pending')->exists(), 409, 'Pengajuan KK Anda masih diperiksa.');
                $path = 'kk_images/'.Str::uuid().'.enc';
                abort_unless(Storage::disk('private')->put($path, FileEncryptionService::encrypt($request->file('kk_image')->getContent())), 503, 'Foto KK gagal disimpan.');
                FamilyCard::create(['kk_image_path' => $path, 'status' => 'pending', 'submitted_by' => $request->user()->id]);
            });
        } catch (\Throwable $e) {
            if ($path) Storage::disk('private')->delete($path);
            throw $e;
        }
        return back()->with('success', 'Foto KK dikirim. Admin desa akan memeriksanya; hasilnya masuk ke notifikasi Anda.');
    }
}
