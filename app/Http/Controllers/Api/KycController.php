<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\KycVerification;
use App\Services\OcrService;
use Illuminate\Http\Request;

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
            'nik' => 'nullable|string|max:32',
            'name' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:2000',
            'rt' => 'nullable|string|max:20',
            'rw' => 'nullable|string|max:20',
            'kecamatan' => 'nullable|string|max:255',
            'desa' => 'nullable|string|max:255',
        ]);

        $user = $request->user();

        // Ambil isi file asli
        $ktpFile = $request->file('ktp_image');
        $fileContent = $ktpFile->get();

        // Proses OCR menggunakan file temporer asli
        $ocrData = $this->ocrService->extractKtpData($ktpFile->getRealPath());

        // Pemeriksaan kelengkapan hasil OCR, bukan pembuktian keaslian KTP.
        if (empty($ocrData) || empty($ocrData['nik'])) {
            return response()->json([
                'success' => false,
                'message' => 'Gambar tidak terdeteksi sebagai e-KTP yang valid. Pastikan gambar jelas dan NIK terlihat.'
            ], 400);
        }

        $kyc = app(\App\Services\IdentityDocumentStorage::class)->createDraft($user, [
            'nik_from_ocr' => $request->nik ?? $ocrData['nik'] ?? null,
            'name_from_ocr' => $request->name ?? $ocrData['name'] ?? null,
            'address_from_ocr' => $request->address ?? $ocrData['address'] ?? null,
            'rt_from_ocr' => $request->rt ?? $ocrData['rt'] ?? null,
            'rw_from_ocr' => $request->rw ?? $ocrData['rw'] ?? null,
            'kecamatan_from_ocr' => $request->kecamatan ?? $ocrData['kecamatan'] ?? null,
            'desa_from_ocr' => $request->desa ?? $ocrData['desa'] ?? null,
            'gender_from_ocr' => $ocrData['gender'] ?? null,
        ], $fileContent);

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
            'face_data' => 'required', // Now can be string or array
            'face_image' => 'nullable|image|max:5120',
            'nik' => 'sometimes|required|digits:16',
            'name' => 'sometimes|required|string|max:255',
            'address' => 'sometimes|nullable|string|max:2000',
            'rt' => 'sometimes|nullable|string|max:20',
            'rw' => 'sometimes|nullable|string|max:20',
            'desa' => 'sometimes|nullable|string|max:255',
            'kecamatan' => 'sometimes|nullable|string|max:255',
        ]);

        $kyc = KycVerification::where('id', $request->kyc_id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        // Handle face_data whether it's array or string (from multipart form data)
        $faceData = is_string($request->face_data) ? json_decode($request->face_data, true) : $request->face_data;
        if (!is_array($faceData)) {
            throw \Illuminate\Validation\ValidationException::withMessages(['face_data' => 'Data kamera harus berupa objek atau daftar JSON.']);
        }
        $updateData = [
            'face_scan_data' => $faceData,
            'status' => 'pending',
        ];
        
        $documents = [];
        // Handle face image if uploaded
        if ($request->hasFile('face_image')) {
            $faceFile = $request->file('face_image');
            $fileContent = $faceFile->get();
            $documents['face'] = $fileContent;
        }
        if (empty($documents['face']) && !$kyc->face_image_path) {
            throw \Illuminate\Validation\ValidationException::withMessages(['face_image' => 'Lampirkan foto wajah sebelum mengirim pengajuan.']);
        }

        if ($request->has('nik')) $updateData['nik_from_ocr'] = $request->nik;
        if ($request->has('name')) $updateData['name_from_ocr'] = $request->name;
        if ($request->has('address')) $updateData['address_from_ocr'] = $request->address;
        if ($request->has('rt')) $updateData['rt_from_ocr'] = $request->rt;
        if ($request->has('rw')) $updateData['rw_from_ocr'] = $request->rw;
        if ($request->has('kecamatan')) $updateData['kecamatan_from_ocr'] = $request->kecamatan;
        if ($request->has('desa')) $updateData['desa_from_ocr'] = $request->desa;

        app(\App\Services\IdentityDocumentStorage::class)->save($kyc, $documents, $updateData);

        return response()->json([
            'success' => true,
            'message' => 'Data verifikasi berhasil dikirim.'
        ]);
    }
}
