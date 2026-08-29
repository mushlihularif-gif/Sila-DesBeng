<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KycVerification;
use App\Models\User;
use App\Models\Region;
use App\Services\FonnteService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class KycReviewController extends Controller
{
    public function index(Request $request)
    {
        $all = KycVerification::with('user')->whereNotNull('face_scan_data')->latest()->get();
        $pending = KycVerification::with('user')->where('status', 'pending')->whereNotNull('face_scan_data')->latest()->get();
        $approved = KycVerification::with('user')->where('status', 'approved')->whereNotNull('face_scan_data')->latest()->get();
        $rejected = KycVerification::with('user')->where('status', 'rejected')->whereNotNull('face_scan_data')->latest()->get();
        
        $counts = [
            'all' => $all->count(),
            'pending' => $pending->count(),
            'approved' => $approved->count(),
            'rejected' => $rejected->count(),
        ];

        return view('admin.kyc.index', compact('all', 'pending', 'approved', 'rejected', 'counts'));
    }

    public function show($id)
    {
        $kyc = KycVerification::with('user')->findOrFail($id);
        return view('admin.kyc.show', compact('kyc'));
    }

    public function approve(Request $request, $id, FonnteService $fonnte)
    {
        $kyc = KycVerification::with('user')->findOrFail($id);
        $user = $kyc->user;

        DB::beginTransaction();
        try {
            $kyc->update([
                'status' => 'approved',
                'admin_notes' => $request->admin_notes,
                'reviewed_by' => Auth::id(),
                'reviewed_at' => now(),
            ]);

            if ($kyc->desa_from_ocr) {
                $region = \App\Models\Region::where('name', 'like', '%' . $kyc->desa_from_ocr . '%')
                                ->where('type', 'desa')
                                ->first();
                if ($region && $user->region_id != $region->id) {
                    $user->region_id = $region->id;
                }
            }
            
            // Get real data before masking
            $realNik = $kyc->nik_from_ocr ?? $user->nik;
            $realName = $kyc->name_from_ocr ?? $user->name;
            
            // Compute real hash before masking NIK
            $realNikHash = null;
            if ($realNik) {
                $realNikHash = hash_hmac('sha256', $realNik, config('app.key'));
            }

            // Masking NIK (4 front, 4 back)
            $maskedNik = $realNik;
            if ($realNik && strlen($realNik) >= 16) {
                $maskedNik = substr($realNik, 0, 4) . str_repeat('*', strlen($realNik) - 8) . substr($realNik, -4);
            }
            
            // Masking Name (1 front, 1 mid, 1 back)
            $maskedName = $realName;
            if ($realName && strlen($realName) > 3) {
                $len = strlen($realName);
                $mid = (int)($len / 2);
                $maskedName = substr($realName, 0, 1) . str_repeat('*', $mid - 1) . substr($realName, $mid, 1) . str_repeat('*', $len - $mid - 2) . substr($realName, -1);
            }

            // Temporarily unguard to set nik_hash directly
            $user->nik = $maskedNik;
            $user->name = $maskedName;
            $user->gender = $kyc->gender_from_ocr ?? $user->gender;
            $user->address = $kyc->address_from_ocr ?? $user->address;
            $user->rt = $kyc->rt_from_ocr ?? $user->rt;
            $user->rw = $kyc->rw_from_ocr ?? $user->rw;
            $user->verification_status = 'verified';
            $user->verified_at = now();
            
            if ($realNikHash) {
                $user->nik_hash = $realNikHash;
            }
            
            $user->save();
            
            // Hapus file fisik secara permanen untuk privasi!
            if ($kyc->ktp_image_path && \Illuminate\Support\Facades\Storage::disk('private')->exists($kyc->ktp_image_path)) {
                \Illuminate\Support\Facades\Storage::disk('private')->delete($kyc->ktp_image_path);
            }
            if ($kyc->face_image_path && \Illuminate\Support\Facades\Storage::disk('private')->exists($kyc->face_image_path)) {
                \Illuminate\Support\Facades\Storage::disk('private')->delete($kyc->face_image_path);
            }
            
            // Hapus data wajah dan path dari database
            $kyc->update([
                'ktp_image_path' => null,
                'face_image_path' => null,
                'face_scan_data' => null
            ]);

            DB::commit();

            $fonnte->sendNotification($user->phone, "*SiladesBeng (Sistem Layanan Desa)*\n\nSelamat! Akun Anda telah berhasil diverifikasi. Anda sekarang mendapatkan lencana Centang Biru (Identitas Terverifikasi).");

            return redirect()->route('admin.kyc.index')->with('success', 'Verifikasi disetujui. Foto KTP dan Wajah telah dihapus permanen.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function reject(Request $request, $id, FonnteService $fonnte)
    {
        $request->validate(['admin_notes' => 'required|string']);

        $kyc = KycVerification::findOrFail($id);
        $user = $kyc->user;

        DB::beginTransaction();
        try {
            $kyc->update([
                'status' => 'rejected',
                'admin_notes' => $request->admin_notes,
                'reviewed_by' => Auth::id(),
                'reviewed_at' => now(),
            ]);

            $user->update([
                'verification_status' => 'unverified'
            ]);
            
            // Hapus file fisik secara permanen untuk privasi!
            if ($kyc->ktp_image_path && \Illuminate\Support\Facades\Storage::disk('private')->exists($kyc->ktp_image_path)) {
                \Illuminate\Support\Facades\Storage::disk('private')->delete($kyc->ktp_image_path);
            }
            if ($kyc->face_image_path && \Illuminate\Support\Facades\Storage::disk('private')->exists($kyc->face_image_path)) {
                \Illuminate\Support\Facades\Storage::disk('private')->delete($kyc->face_image_path);
            }
            
            // Hapus path dari database
            $kyc->update([
                'ktp_image_path' => null,
                'face_image_path' => null,
                'face_scan_data' => null
            ]);

            DB::commit();

            $fonnte->sendNotification($user->phone, "*SiladesBeng (Sistem Layanan Desa)*\n\nMohon maaf, verifikasi identitas Anda ditolak.\nAlasan: {$request->admin_notes}\nSilakan ajukan ulang melalui menu Profil.");

            return redirect()->route('admin.kyc.index')->with('success', 'Verifikasi ditolak. Foto KTP dan Wajah telah dihapus permanen.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}
