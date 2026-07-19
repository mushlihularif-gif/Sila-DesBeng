<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\KycVerification;
use App\Services\OcrService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class KycController extends Controller
{
    protected $ocrService;

    public function __construct(OcrService $ocrService)
    {
        $this->ocrService = $ocrService;
    }

    public function process(Request $request)
    {
        $request->validate([
            'ktp_image' => 'required|image|max:5120', 
        ]);

        $user = $request->user();

        $path = $request->file('ktp_image')->store('ktp', 'public');
        $fullPath = storage_path('app/public/' . $path);

        $ocrData = $this->ocrService->extractKtpData($fullPath);

        KycVerification::where('user_id', $user->id)->whereIn('status', ['pending', 'rejected'])->delete();

        $kyc = KycVerification::create([
            'user_id' => $user->id,
            'ktp_image_path' => $path,
            'nik_from_ocr' => $ocrData['nik'] ?? null,
            'name_from_ocr' => $ocrData['name'] ?? null,
            'address_from_ocr' => $ocrData['address'] ?? null,
            'rt_from_ocr' => $ocrData['rt'] ?? null,
            'rw_from_ocr' => $ocrData['rw'] ?? null,
            'kecamatan_from_ocr' => $ocrData['kecamatan'] ?? null,
            'desa_from_ocr' => $ocrData['desa'] ?? null,
            'gender_from_ocr' => $ocrData['gender'] ?? null,
            'status' => 'pending' 
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'kyc_id' => $kyc->id,
                'ocr_data' => $ocrData
            ]
        ]);
    }

    public function submit(Request $request)
    {
        $request->validate([
            'kyc_id' => 'required|exists:kyc_verifications,id',
            'face_data' => 'required|array', 
        ]);

        $kyc = KycVerification::where('id', $request->kyc_id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $kyc->update([
            'face_scan_data' => $request->face_data,
        ]);

        $user = $request->user();
        $user->update(['verification_status' => 'pending']);

        return response()->json([
            'success' => true,
            'message' => 'Data verifikasi berhasil dikirim.'
        ]);
    }
}
