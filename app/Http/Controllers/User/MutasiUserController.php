<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\MutasiPenduduk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MutasiUserController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'to_region_id' => 'required|exists:regions,id',
            'alamat_baru' => 'required|string|max:255',
            'rt_baru' => 'required|string|max:10',
            'rw_baru' => 'required|string|max:10',
            'reason' => 'required|string|max:500',
            'ktp_image' => 'required|image|max:10240', // Maks 10MB
        ]);

        $user = Auth::user();

        // Cek jika KTP belum terverifikasi
        if ($user->verification_status !== 'verified') {
            return redirect()->back()->with('error', 'Hanya warga terverifikasi yang bisa mengajukan pindah desa.');
        }

        // Cek jika sedang ada pengajuan pending
        $existing = MutasiPenduduk::where('user_id', $user->id)
                                  ->where('status', 'pending')
                                  ->first();

        if ($existing) {
            return redirect()->back()->with('error', 'Anda sudah memiliki pengajuan pindah desa yang sedang diproses.');
        }

        if ($user->region_id == $request->to_region_id) {
            return redirect()->back()->with('error', 'Desa tujuan tidak boleh sama dengan desa saat ini.');
        }

        $ktpPath = null;
        if ($request->hasFile('ktp_image')) {
            $ktpPath = $request->file('ktp_image')->store('mutasi_ktp', 'private');
        }

        $mutasi = MutasiPenduduk::create([
            'user_id' => $user->id,
            'from_region_id' => $user->region_id,
            'to_region_id' => $request->to_region_id,
            'status' => 'pending',
            'requested_by' => 'user',
            'reason' => $request->reason,
            'alamat_baru' => $request->alamat_baru,
            'rt_baru' => $request->rt_baru,
            'rw_baru' => $request->rw_baru,
            'ktp_image_path' => $ktpPath,
        ]);

        \App\Models\AdminNotification::create([
            'type' => 'mutasi',
            'title' => 'Pengajuan Mutasi Penduduk',
            'message' => ($user->name ?? 'Warga') . ' mengajukan permohonan pindah domisili.',
            'reference_id' => $mutasi->id,
            'region_id' => $user->region_id,
            'is_read' => false,
        ]);

        return redirect()->back()->with('success', 'Pengajuan pindah desa berhasil dikirim ke Admin Desa Anda untuk persetujuan (Handshake Protocol).');
    }
}
