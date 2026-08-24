<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\FamilyCard;
use App\Models\FamilyMember;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

class GasKkAdminController extends Controller
{
    public function index()
    {
        $admin = Auth::user();
        
        // Admin desa hanya melihat warga desanya
        $query = FamilyCard::with('submitter')->where('status', 'pending');
        
        if (!in_array($admin->role, ['super_admin', 'admin_kecamatan'])) {
            $query->whereHas('submitter', function($q) use ($admin) {
                $q->where('region_id', $admin->region_id);
            });
        }
        
        $pengajuan = $query->get();
        return view('admin.gas.verifikasi_kk', compact('pengajuan'));
    }

    public function showImage($id)
    {
        $kk = FamilyCard::findOrFail($id);
        $admin = Auth::user();

        if (!$kk->kk_image_path) abort(404, 'File sudah dihancurkan');
        
        $path = storage_path('app/private/' . $kk->kk_image_path);
        if (!file_exists($path)) abort(404);
        
        return response()->file($path);
    }

    public function approve(Request $request, $id)
    {
        $request->validate([
            'no_kk' => 'required|string|size:16',
            'kepala_keluarga' => 'required|string',
            'niks' => 'required|array',
            'niks.*' => 'string|size:16',
        ]);

        $kk = FamilyCard::findOrFail($id);
        
        $plainNoKk = $request->no_kk;
        $hashedNoKk = hash_hmac('sha256', $plainNoKk, config('app.key'));
        
        $kk->no_kk_hash = $hashedNoKk;
        $kk->no_kk_masked = substr($plainNoKk, 0, 4) . '********' . substr($plainNoKk, -4);
        
        $nama = $request->kepala_keluarga;
        $kk->kepala_keluarga_masked = substr($nama, 0, 1) . '***' . substr($nama, -1);
        
        $kk->status = 'verified';
        $kk->reviewed_by = Auth::id();
        $kk->reviewed_at = now();

        // PRIVACY RULE: BURN AFTER READING
        if ($kk->kk_image_path) {
            Storage::disk('private')->delete($kk->kk_image_path);
            $kk->kk_image_path = null;
        }
        
        $kk->save();

        // AUTO-CABUT NIK LOGIC
        foreach($request->niks as $plainNik) {
            $hashedNik = hash_hmac('sha256', $plainNik, config('app.key'));
            
            // Hapus NIK ini dari KK mana pun sebelumnya (Mesin Auto-Cabut)
            FamilyMember::where('nik_hash', $hashedNik)->delete();
            
            // Masukkan ke KK yang baru ini
            FamilyMember::create([
                'family_card_id' => $kk->id,
                'nik_hash' => $hashedNik
            ]);
        }

        return redirect()->back()->with('success', 'KK Disetujui. Foto KK telah dihancurkan, dan NIK ganda telah dicabut otomatis oleh sistem.');
    }

    public function reject(Request $request, $id)
    {
        $kk = FamilyCard::findOrFail($id);
        $kk->status = 'rejected';
        $kk->admin_notes = $request->reason ?? 'Foto buram atau tidak valid';
        $kk->reviewed_by = Auth::id();
        $kk->reviewed_at = now();
        
        // PRIVACY RULE: BURN AFTER READING
        if ($kk->kk_image_path) {
            Storage::disk('private')->delete($kk->kk_image_path);
            $kk->kk_image_path = null;
        }
        
        $kk->save();
        return redirect()->back()->with('success', 'KK Ditolak dan foto telah dihancurkan.');
    }
}
