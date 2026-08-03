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

        // Ambil isi file asli
        $ktpFile = $request->file('ktp_image');
        $fileContent = $ktpFile->get();

        // Enkripsi isi file menggunakan ChaCha20
        $encryptedContent = \App\Services\FileEncryptionService::encrypt($fileContent);

        // Simpan file terenkripsi ke disk private
        $fileName = 'ktp_' . uniqid() . '.enc';
        $path = 'kyc/ktp/' . $fileName;
        Storage::disk('private')->put($path, $encryptedContent);

        // Proses OCR menggunakan file temporer asli
        $ocrData = $this->ocrService->extractKtpData($ktpFile->getRealPath());

        KycVerification::where('user_id', $user->id)->whereIn('status', ['pending', 'rejected'])->delete();

        $kyc = KycVerification::create([
            'user_id' => $user->id,
            'ktp_image_path' => $path,
            'nik_from_ocr' => $request->nik ?? $ocrData['nik'] ?? null,
            'name_from_ocr' => $request->name ?? $ocrData['name'] ?? null,
            'address_from_ocr' => $request->address ?? $ocrData['address'] ?? null,
            'rt_from_ocr' => $request->rt ?? $ocrData['rt'] ?? null,
            'rw_from_ocr' => $request->rw ?? $ocrData['rw'] ?? null,
            'kecamatan_from_ocr' => $request->kecamatan ?? $ocrData['kecamatan'] ?? null,
            'desa_from_ocr' => $request->desa ?? $ocrData['desa'] ?? null,
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

        // Catatan: Pada submit API, sepertinya tidak menerima face_image, hanya face_data.
        // Jika API ini tidak mengupload foto face (hanya JSON array titik wajah), maka tidak perlu enkripsi image.
        
        $kyc = KycVerification::where('id', $request->kyc_id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $updateData = ['face_scan_data' => $request->face_data];
        
        if ($request->has('nik')) $updateData['nik_from_ocr'] = $request->nik;
        if ($request->has('name')) $updateData['name_from_ocr'] = $request->name;
        if ($request->has('address')) $updateData['address_from_ocr'] = $request->address;
        if ($request->has('rt')) $updateData['rt_from_ocr'] = $request->rt;
        if ($request->has('rw')) $updateData['rw_from_ocr'] = $request->rw;
        if ($request->has('kecamatan')) $updateData['kecamatan_from_ocr'] = $request->kecamatan;
        if ($request->has('desa')) $updateData['desa_from_ocr'] = $request->desa;

        $kyc->update($updateData);

        $user = $request->user();
        $user->update(['verification_status' => 'pending']);

        return response()->json([
            'success' => true,
            'message' => 'Data verifikasi berhasil dikirim.'
        ]);
    }
}
