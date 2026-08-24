import codecs

filepath = "D:/laragon/www/SilaDesBeng/app/Http/Controllers/User/MutasiUserController.php"
content = """<?php

namespace App\\Http\\Controllers\\User;

use App\\Http\\Controllers\\Controller;
use App\\Models\\MutasiPenduduk;
use Illuminate\\Http\\Request;
use Illuminate\\Support\\Facades\\Auth;

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

        MutasiPenduduk::create([
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

        return redirect()->back()->with('success', 'Pengajuan pindah desa berhasil dikirim ke Admin Desa Anda untuk persetujuan (Handshake Protocol).');
    }
}
"""
with codecs.open(filepath, 'w', encoding='utf-8') as f:
    f.write(content)


filepath = "D:/laragon/www/SilaDesBeng/app/Http/Controllers/Admin/MutasiAdminController.php"
with codecs.open(filepath, 'r', encoding='utf-8') as f:
    admin_content = f.read()

# Replace approve method
old_approve = """    public function approve($id)
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
    }"""

new_approve = """    public function approve($id)
    {
        $mutasi = MutasiPenduduk::findOrFail($id);
        $admin = Auth::user();

        // Hanya desa asal yang berhak approve pelepasannya (Handshake awal)
        if ($mutasi->from_region_id != $admin->region_id && !in_array($admin->role, ['super_admin', 'admin_kecamatan'])) {
            abort(403, 'Anda tidak berhak menyetujui pelepasan ini.');
        }

        $mutasi->status = 'approved';
        
        // PRIVACY RULE: Burn after reading
        if ($mutasi->ktp_image_path) {
            \\Illuminate\\Support\\Facades\\Storage::disk('private')->delete($mutasi->ktp_image_path);
            $mutasi->ktp_image_path = null;
        }
        $mutasi->save();

        // Pindahkan user ke desa baru
        $user = User::findOrFail($mutasi->user_id);
        $user->region_id = $mutasi->to_region_id;
        if ($mutasi->alamat_baru) $user->address = $mutasi->alamat_baru;
        // Kita juga bisa update RT RW jika ada kolomnya di users table
        $user->save();

        return redirect()->back()->with('success', "Pelepasan disetujui. Warga {$user->name} telah resmi berpindah ke desa tujuan.");
    }"""
admin_content = admin_content.replace(old_approve, new_approve)

old_reject = """    public function reject(Request $request, $id)
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
    }"""

new_reject = """    public function reject(Request $request, $id)
    {
        $request->validate(['rejection_reason' => 'required|string']);
        
        $mutasi = MutasiPenduduk::findOrFail($id);
        $admin = Auth::user();

        if ($mutasi->from_region_id != $admin->region_id && !in_array($admin->role, ['super_admin', 'admin_kecamatan'])) {
            abort(403);
        }

        $mutasi->status = 'rejected';
        $mutasi->rejection_reason = $request->rejection_reason;
        
        // PRIVACY RULE: Burn after reading even if rejected
        if ($mutasi->ktp_image_path) {
            \\Illuminate\\Support\\Facades\\Storage::disk('private')->delete($mutasi->ktp_image_path);
            $mutasi->ktp_image_path = null;
        }
        
        $mutasi->save();

        return redirect()->back()->with('success', 'Mutasi ditolak dan dikembalikan.');
    }"""
admin_content = admin_content.replace(old_reject, new_reject)

with codecs.open(filepath, 'w', encoding='utf-8') as f:
    f.write(admin_content)

print("Controllers updated.")
