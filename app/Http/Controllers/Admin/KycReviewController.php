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
        $pending = KycVerification::with('user')
            ->where('status', 'pending')
            ->latest()
            ->get();
            
        $approved = KycVerification::with('user')
            ->where('status', 'approved')
            ->latest()
            ->get();
            
        $rejected = KycVerification::with('user')
            ->where('status', 'rejected')
            ->latest()
            ->get();
            
        $all = KycVerification::with('user')
            ->whereIn('status', ['pending', 'approved', 'rejected'])
            ->latest()
            ->get();
        
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
            
            // Sinkronisasi data identitas terverifikasi ke profil pengguna
            $realNik = $kyc->nik_from_ocr ?? $user->nik;
            $realName = $kyc->name_from_ocr ?? $user->name;

            if ($realNik) {
                $user->nik = $realNik;
            }
            if ($realName) {
                $user->name = $realName;
            }
            $user->gender = $kyc->gender_from_ocr ?? $user->gender;
            $user->address = $kyc->address_from_ocr ?? $user->address;
            $user->rt = $kyc->rt_from_ocr ?? $user->rt;
            $user->rw = $kyc->rw_from_ocr ?? $user->rw;
            $user->verification_status = 'verified';
            $user->verified_at = now();

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

            // Kirim notifikasi ke akun warga
            \App\Services\NotificationService::notifyKycApproved($user);

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

            // Kirim notifikasi ke akun warga
            \App\Services\NotificationService::notifyKycRejected($user, $request->admin_notes);

            DB::commit();

            $fonnte->sendNotification($user->phone, "*SiladesBeng (Sistem Layanan Desa)*\n\nMohon maaf, verifikasi identitas Anda ditolak.\nAlasan: {$request->admin_notes}\nSilakan ajukan ulang melalui menu Profil.");

            return redirect()->route('admin.kyc.index')->with('success', 'Verifikasi ditolak. Foto KTP dan Wajah telah dihapus permanen.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}

