<?php
namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\FamilyCard;
use App\Services\OcrService;

class GasKkVerificationController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'kk_image' => 'required|image|max:10240',
        ]);

        $user = Auth::user();

        // Cek jika sudah ada yg pending
        $existing = FamilyCard::where('submitted_by', $user->id)->where('status', 'pending')->first();
        if ($existing) {
            return redirect()->back()->with('error', 'Anda sudah memiliki pengajuan KK yang sedang diproses.');
        }

        $kkPath = $request->file('kk_image')->store('kk_images', 'private');

        FamilyCard::create([
            'kk_image_path' => $kkPath,
            'status' => 'pending',
            'submitted_by' => $user->id,
            // no_kk_hash akan diisi admin setelah cek OCR
        ]);

        return redirect()->back()->with('success', 'Foto KK berhasil diunggah dan sedang menuggu verifikasi Admin.');
    }
}
