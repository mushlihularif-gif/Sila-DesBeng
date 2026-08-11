<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MutasiPenduduk;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MutasiAdminController extends Controller
{
    public function index()
    {
        $admin = Auth::user();
        
        // Hanya membatasi jika admin desa. Super admin bebas.
        $region_id = in_array($admin->role, ['admin_desa', 'admin_rt', 'admin_rw']) ? $admin->region_id : null;
        
        // Pengajuan Keluar (Handshake menunggu approve dari KITA)
        $pengajuanKeluar = MutasiPenduduk::with(['user', 'toRegion'])
            ->where('from_region_id', $region_id)
            ->where('status', 'pending')
            ->get();
            
        // Pengajuan Masuk (Handshake menunggu approve dari DESA LAMA)
        $pengajuanMasuk = MutasiPenduduk::with(['user', 'fromRegion'])
            ->where('to_region_id', $region_id)
            ->where('status', 'pending')
            ->get();

        // Riwayat
        $riwayat = MutasiPenduduk::with(['user', 'fromRegion', 'toRegion'])
            ->where(function($q) use ($region_id) {
                $q->where('from_region_id', $region_id)
                  ->orWhere('to_region_id', $region_id);
            })
            ->whereIn('status', ['approved', 'rejected'])
            ->orderBy('updated_at', 'desc')
            ->paginate(10);

        return view('admin.warga.mutasi', compact('pengajuanKeluar', 'pengajuanMasuk', 'riwayat'));
    }

    public function tarikWarga(Request $request)
    {
        $request->validate([
            'nik' => 'required|string',
            'reason' => 'required|string'
        ]);

        $admin = Auth::user();
        
        // Cari warga berdasarkan NIK
        $user = User::where('nik', $request->nik)->first();
        
        if (!$user) {
            return redirect()->back()->with('error', 'Warga dengan NIK tersebut tidak ditemukan di sistem.');
        }
        
        if ($user->region_id == $admin->region_id) {
            return redirect()->back()->with('error', 'Warga ini sudah terdaftar di desa Anda.');
        }

        // Cek apakah sudah ada pending
        $existing = MutasiPenduduk::where('user_id', $user->id)->where('status', 'pending')->first();
        if ($existing) {
            return redirect()->back()->with('error', 'Warga ini sedang dalam proses mutasi (Pending).');
        }

        MutasiPenduduk::create([
            'user_id' => $user->id,
            'from_region_id' => $user->region_id,
            'to_region_id' => $admin->region_id,
            'status' => 'pending',
            'requested_by' => 'admin',
            'reason' => $request->reason,
        ]);

        return redirect()->back()->with('success', 'Berhasil melakukan request penarikan. Silakan tunggu persetujuan (Handshake) dari Kepala Desa asal warga tersebut.');
    }

    public function approve($id)
    {
        $mutasi = MutasiPenduduk::findOrFail($id);
        $admin = Auth::user();

        // Hanya desa asal yang berhak approve pelepasannya
        if ($mutasi->from_region_id != $admin->region_id && !in_array($admin->role, ['super_admin', 'admin_kecamatan'])) {
            abort(403, 'Anda tidak berhak menyetujui pelepasan ini.');
        }

        $mutasi->status = 'approved';
        $mutasi->save();

        // Pindahkan user ke desa baru
        $user = User::findOrFail($mutasi->user_id);
        $user->region_id = $mutasi->to_region_id;
        $user->save();

        return redirect()->back()->with('success', "Pelepasan disetujui. Warga {$user->name} telah resmi berpindah ke desa tujuan.");
    }

    public function reject(Request $request, $id)
    {
        $request->validate(['rejection_reason' => 'required|string']);
        
        $mutasi = MutasiPenduduk::findOrFail($id);
        $admin = Auth::user();

        if ($mutasi->from_region_id != $admin->region_id && !in_array($admin->role, ['super_admin', 'admin_kecamatan'])) {
            abort(403);
        }

        $mutasi->status = 'rejected';
        $mutasi->rejection_reason = $request->rejection_reason;
        $mutasi->save();

        return redirect()->back()->with('success', 'Mutasi ditolak dan dikembalikan.');
    }
}
