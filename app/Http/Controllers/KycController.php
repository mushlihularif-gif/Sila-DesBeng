<?php

namespace App\Http\Controllers;

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

    public function index()
    {
        $user = Auth::user();
        if ($user->verification_status === 'verified') {
            return redirect()->route('beranda')->with('info', 'Akun Anda sudah diverifikasi.');
        }

        if ($user->verification_status === 'pending') {
            return view('kyc.pending');
        }

        return view('kyc.index');
    }

    public function process(Request $request)
    {
        $request->validate([
            'ktp_image' => 'required|image|max:5120', // Max 5MB
        ]);

        $user = Auth::user();

        // Ambil isi file asli
        $ktpFile = $request->file('ktp_image');
        
        // --- BACA GAMBAR KTP ---
        $manager = new \Intervention\Image\ImageManager(new \Intervention\Image\Drivers\Gd\Driver());
        $image = $manager->read($ktpFile->getRealPath());

        // Encode image ke jpg
        $watermarkedKtp = (string) $image->encodeByExtension('jpg', 80);

        // Enkripsi isi file yang sudah di-watermark menggunakan ChaCha20
        $encryptedContent = \App\Services\FileEncryptionService::encrypt($watermarkedKtp);

        // Simpan file terenkripsi ke disk private
        $fileName = 'ktp_' . uniqid() . '.enc';
        $path = 'kyc/ktp/' . $fileName;
        Storage::disk('private')->put($path, $encryptedContent);

        // Proses OCR menggunakan file temporer asli (belum terenkripsi)
        $ocrData = $this->ocrService->extractKtpData($ktpFile->getRealPath());

        // Validasi: pastikan gambar yang diunggah benar-benar KTP dengan mengecek apakah NIK berhasil dideteksi
        if (empty($ocrData) || empty($ocrData['nik'])) {
            return response()->json([
                'success' => false,
                'message' => 'Gambar tidak terdeteksi sebagai e-KTP yang valid. Pastikan gambar jelas dan NIK terlihat.'
            ], 400);
        }

        // Hapus verifikasi yang pending/rejected sebelumnya (jika ada)
        KycVerification::where('user_id', $user->id)->whereIn('status', ['pending', 'rejected'])->delete();

        // Buat record KYC baru
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
            'kyc_id' => $kyc->id,
            'ocr_data' => $ocrData
        ]);
    }

    public function submit(Request $request)
    {
        $request->validate([
            'kyc_id' => 'required|exists:kyc_verifications,id',
            'face_data' => 'required|array',
            'face_image' => 'required|string', // Base64 image
            'edited_nik' => 'nullable|string|max:16',
            'edited_nama' => 'nullable|string|max:255',
            'edited_alamat' => 'nullable|string',
            'edited_rt' => 'nullable|string|max:10',
            'edited_rw' => 'nullable|string|max:10',
            'edited_desa' => 'nullable|string|max:255',
            'edited_kecamatan' => 'nullable|string|max:255',
        ]);

        $kyc = KycVerification::where('id', $request->kyc_id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        // Process base64 face_image
        $imagePath = null;
        if ($request->face_image) {
            $image_parts = explode(";base64,", $request->face_image);
            if (count($image_parts) == 2) {
                $image_base64 = base64_decode($image_parts[1]);
                
                // --- BACA SELFIE ---
                $manager = new \Intervention\Image\ImageManager(new \Intervention\Image\Drivers\Gd\Driver());
                $image = $manager->read($image_base64);
                
                $watermarkedFace = (string) $image->encodeByExtension('jpg', 80);

                // Enkripsi isi gambar menggunakan ChaCha20
                $encryptedFace = \App\Services\FileEncryptionService::encrypt($watermarkedFace);
                
                $fileName = 'face_' . uniqid() . '.enc';
                $imagePath = 'kyc/face/' . $fileName;
                
                // Simpan ke disk private
                Storage::disk('private')->put($imagePath, $encryptedFace);
            }
        }

        $updateData = [
            'face_scan_data' => $request->face_data,
            'face_image_path' => $imagePath,
        ];
        
        if ($request->filled('edited_nik')) {
            $updateData['nik_from_ocr'] = $request->edited_nik;
        }
        if ($request->filled('edited_nama')) {
            $updateData['name_from_ocr'] = $request->edited_nama;
        }
        if ($request->filled('edited_alamat')) {
            $updateData['address_from_ocr'] = $request->edited_alamat;
        }
        if ($request->filled('edited_rt')) {
            $updateData['rt_from_ocr'] = $request->edited_rt;
        }
        if ($request->filled('edited_rw')) {
            $updateData['rw_from_ocr'] = $request->edited_rw;
        }
        if ($request->filled('edited_desa')) {
            $updateData['desa_from_ocr'] = $request->edited_desa;
        }
        if ($request->filled('edited_kecamatan')) {
            $updateData['kecamatan_from_ocr'] = $request->edited_kecamatan;
        }

        $kyc->update($updateData);

        $user = Auth::user();
        $user->update(['verification_status' => 'pending']);

        return response()->json([
            'success' => true,
            'message' => 'Data verifikasi berhasil dikirim dan menunggu persetujuan Admin.'
        ]);
    }
}
