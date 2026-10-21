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
        $users = $this->scopedCitizens()->select('id', 'name', 'email', 'phone', 'rt', 'rw', 'address', 'verification_status', 'created_at')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($user) {
                $kyc = KycVerification::where('user_id', $user->id)->latest()->first();
                $rtStr = $user->rt ? 'RT ' . str_pad($user->rt, 2, '0', STR_PAD_LEFT) : 'RT 02';
                $rwStr = $user->rw ? 'RW ' . str_pad($user->rw, 2, '0', STR_PAD_LEFT) : 'RW 01';
                
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'rt' => $user->rt,
                    'rw' => $user->rw,
                    'rt_rw' => "$rtStr / $rwStr",
                    'address' => $user->address ?? '-',
                    'verification_status' => $user->verification_status ?? 'unverified',
                    'kyc_status' => $kyc ? $kyc->status : null,
                    'kyc_nik' => $this->maskedNik($kyc ? $kyc->nik_from_ocr : $user->nik),
                    'kyc_name' => $kyc ? $kyc->name_from_ocr : $user->name,
                    'registered_date' => $user->created_at ? $user->created_at->format('d M Y') : '-',
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $users,
        ]);
    }

    public function show($id)
    {
        $user = $this->scopedCitizens()->findOrFail($id);
        $kyc = KycVerification::where('user_id', $id)->latest()->first();
        $rtStr = $user->rt ? 'RT ' . str_pad($user->rt, 2, '0', STR_PAD_LEFT) : 'RT 02';
        $rwStr = $user->rw ? 'RW ' . str_pad($user->rw, 2, '0', STR_PAD_LEFT) : 'RW 01';

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'rt' => $user->rt,
                'rw' => $user->rw,
                'rt_rw' => "$rtStr / $rwStr",
                'address' => $user->address ?? '-',
                'verification_status' => $user->verification_status ?? 'unverified',
                'registered_date' => $user->created_at ? $user->created_at->format('d M Y') : '-',
                'kyc' => $kyc ? [
                    'id' => $kyc->id,
                    'status' => $kyc->status,
                    'nik' => $this->maskedNik($kyc->nik_from_ocr),
                    'name' => $kyc->name_from_ocr,
                    'address' => $kyc->address_from_ocr,
                    'rt' => $kyc->rt_from_ocr,
                    'rw' => $kyc->rw_from_ocr,
                    'kecamatan' => $kyc->kecamatan_from_ocr,
                    'desa' => $kyc->desa_from_ocr,
                    'submitted_at' => $kyc->created_at ? $kyc->created_at->format('d M Y H:i') : '-',
                ] : null,
            ],
        ]);
    }

    private function scopedCitizens(): \Illuminate\Database\Eloquent\Builder
    {
        $admin = auth()->user();
        abort_unless($admin && in_array($admin->role, ['admin_rt', 'admin_rw'], true), 403);
        $query = User::query()->where('role', 'user');
        if (!$admin->region_id || !$admin->rw || ($admin->role === 'admin_rt' && !$admin->rt)) {
            return $query->whereRaw('1 = 0');
        }
        $query->whereIn('region_id', array_merge([$admin->region_id], \App\Models\Region::getDescendantIds($admin->region_id)))
            ->where('rw', $admin->rw);
        if ($admin->role === 'admin_rt') $query->where('rt', $admin->rt);
        return $query;
    }

    private function maskedNik(?string $nik): ?string
    {
        return $nik ? str_repeat('*', max(0, strlen($nik) - 4)) . substr($nik, -4) : null;
    }

    public function approveKyc(Request $request, $id)
    {
        // RT/RW melihat warga wilayahnya; keputusan KYC mengikuti kewenangan dashboard.
        abort(403, 'Verifikasi identitas harus ditinjau admin desa/kecamatan melalui dashboard KYC.');
    }

    public function rejectKyc(Request $request, $id)
    {
        abort(403, 'Verifikasi identitas harus ditinjau admin desa/kecamatan melalui dashboard KYC.');
    }
}
