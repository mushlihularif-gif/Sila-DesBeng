<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\KycVerification;
use Illuminate\Http\Request;

class AdminWargaApiController extends Controller
{
    public function index(Request $request)
    {
        $users = User::select('id', 'name', 'email', 'phone', 'rt_rw', 'address', 'verification_status', 'created_at')
            ->withCount(['kycVerification as has_kyc' => function ($q) {
                $q->whereIn('status', ['pending', 'approved']);
            }])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($user) {
                $kyc = KycVerification::where('user_id', $user->id)->latest()->first();
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'rt_rw' => $user->rt_rw,
                    'address' => $user->address,
                    'verification_status' => $user->verification_status ?? 'unverified',
                    'kyc_status' => $kyc ? $kyc->status : null,
                    'kyc_nik' => $kyc ? $kyc->nik_from_ocr : null,
                    'kyc_name' => $kyc ? $kyc->name_from_ocr : null,
                    'registered_date' => $user->created_at->format('d M Y'),
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $users,
        ]);
    }

    public function show($id)
    {
        $user = User::findOrFail($id);
        $kyc = KycVerification::where('user_id', $id)->latest()->first();

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'rt_rw' => $user->rt_rw,
                'address' => $user->address,
                'verification_status' => $user->verification_status ?? 'unverified',
                'registered_date' => $user->created_at->format('d M Y'),
                'kyc' => $kyc ? [
                    'id' => $kyc->id,
                    'status' => $kyc->status,
                    'nik' => $kyc->nik_from_ocr,
                    'name' => $kyc->name_from_ocr,
                    'address' => $kyc->address_from_ocr,
                    'rt' => $kyc->rt_from_ocr,
                    'rw' => $kyc->rw_from_ocr,
                    'kecamatan' => $kyc->kecamatan_from_ocr,
                    'desa' => $kyc->desa_from_ocr,
                    'submitted_at' => $kyc->created_at->format('d M Y H:i'),
                ] : null,
            ],
        ]);
    }

    public function approveKyc(Request $request, $id)
    {
        $kyc = KycVerification::where('user_id', $id)
            ->where('status', 'pending')
            ->latest()
            ->firstOrFail();

        $kyc->update([
            'status' => 'approved',
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
            'admin_notes' => $request->notes ?? 'Disetujui oleh Admin.',
        ]);

        $user = User::findOrFail($id);
        $user->update(['verification_status' => 'verified']);

        return response()->json([
            'success' => true,
            'message' => "Verifikasi warga {$user->name} berhasil disetujui.",
        ]);
    }

    public function rejectKyc(Request $request, $id)
    {
        $kyc = KycVerification::where('user_id', $id)
            ->where('status', 'pending')
            ->latest()
            ->firstOrFail();

        $kyc->update([
            'status' => 'rejected',
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
            'admin_notes' => $request->notes ?? 'Ditolak oleh Admin.',
        ]);

        $user = User::findOrFail($id);
        $user->update(['verification_status' => 'rejected']);

        return response()->json([
            'success' => true,
            'message' => "Verifikasi warga {$user->name} ditolak.",
        ]);
    }
}
