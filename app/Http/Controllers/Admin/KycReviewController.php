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
        $status = $request->input('status', 'all');
        
        $query = KycVerification::with('user');
        
        if ($status !== 'all' && in_array($status, ['pending', 'approved', 'rejected'])) {
            $query->where('status', $status);
        }

        $counts = [
            'all' => KycVerification::count(),
            'pending' => KycVerification::where('status', 'pending')->count(),
            'approved' => KycVerification::where('status', 'approved')->count(),
            'rejected' => KycVerification::where('status', 'rejected')->count(),
        ];

        $verifications = $query->orderByRaw("FIELD(status, 'pending', 'approved', 'rejected')")
            ->latest()
            ->paginate(15)
            ->appends(request()->query());
            
        return view('admin.kyc.index', compact('verifications', 'status', 'counts'));
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
                $region = Region::where('name', 'like', '%' . $kyc->desa_from_ocr . '%')
                                ->where('type', 'desa')
                                ->first();
                if ($region && $user->region_id != $region->id) {
                    $user->region_id = $region->id;
                }
            }

            $user->update([
                'nik' => $kyc->nik_from_ocr ?? $user->nik,
                'name' => $kyc->name_from_ocr ?? $user->name,
                'gender' => $kyc->gender_from_ocr ?? $user->gender,
                'address' => $kyc->address_from_ocr ?? $user->address,
                'rt' => $kyc->rt_from_ocr ?? $user->rt,
                'rw' => $kyc->rw_from_ocr ?? $user->rw,
                'verification_status' => 'verified',
                'verified_at' => now(),
            ]);

            DB::commit();

            $fonnte->sendNotification($user->phone, "*SilaDesBeng (Sistem Layanan Desa)*\n\nSelamat! Akun Anda telah berhasil diverifikasi. Anda sekarang mendapatkan lencana Centang Biru (Identitas Terverifikasi).");

            return redirect()->route('admin.kyc.index')->with('success', 'Verifikasi disetujui.');
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

        $kyc->update([
            'status' => 'rejected',
            'admin_notes' => $request->admin_notes,
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
        ]);

        $user->update(['verification_status' => 'rejected']);

        $fonnte->sendNotification($user->phone, "*SilaDesBeng (Sistem Layanan Desa)*\n\nMohon maaf, verifikasi KTP Anda ditolak dengan alasan: *" . $request->admin_notes . "*. Silakan ajukan ulang melalui aplikasi.");

        return redirect()->route('admin.kyc.index')->with('success', 'Verifikasi ditolak.');
    }
}
