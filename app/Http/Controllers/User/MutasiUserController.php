<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\MutasiPenduduk;
use App\Models\Region;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MutasiUserController extends Controller
{
    public function store(Request $request)
    {
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'to_region_id' => 'required|exists:regions,id',
            'reason' => 'required|string|max:500',
            'alamat_baru' => 'nullable|string|max:255',
            'rt_baru' => 'nullable|string|max:10',
            'rw_baru' => 'nullable|string|max:10',
            'ktp_image' => 'nullable|image|max:10240', // Maks 10MB
        ], [
            'to_region_id.required' => 'Silakan pilih desa tujuan.',
            'to_region_id.exists' => 'Desa tujuan tidak valid.',
            'reason.required' => 'Alasan kepindahan wajib diisi.',
            'reason.max' => 'Alasan kepindahan maksimal 500 karakter.',
            'ktp_image.image' => 'Berkas KTP harus berupa gambar.',
            'ktp_image.max' => 'Ukuran berkas KTP maksimal 10MB.',
        ]);

        if ($validator->fails()) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors()->first(),
                    'errors' => $validator->errors()
                ], 422);
            }
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $user = Auth::user();

        // Cek jika KTP belum terverifikasi
        if ($user->verification_status !== 'verified') {
            $msg = 'Hanya warga terverifikasi yang bisa mengajukan pindah desa.';
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 403);
            }
            return redirect()->back()->with('error', $msg);
        }

        // Cek jika sedang ada pengajuan pending
        $existing = MutasiPenduduk::where('user_id', $user->id)
                                  ->whereIn('status', ['pending', 'pending_asal', 'pending_tujuan'])
                                  ->first();

        if ($existing) {
            $msg = 'Anda sudah memiliki pengajuan pindah desa yang sedang diproses.';
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }
            return redirect()->back()->with('error', $msg);
        }

        // Tentukan desa asal (jika akun terikat ke RT/RW, cari desa induknya)
        $fromDesaId = $user->region_id;
        if ($user->region_id) {
            $curr = Region::find($user->region_id);
            while ($curr && $curr->type !== 'desa' && $curr->parent_id) {
                $curr = Region::find($curr->parent_id);
            }
            if ($curr && $curr->type === 'desa') {
                $fromDesaId = $curr->id;
            }
        }

        if ($fromDesaId == $request->to_region_id) {
            $msg = 'Desa tujuan tidak boleh sama dengan desa saat ini.';
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }
            return redirect()->back()->with('error', $msg);
        }

        $ktpPath = null;
        if ($request->hasFile('ktp_image')) {
            $ktpPath = $request->file('ktp_image')->store('mutasi_ktp', 'private');
        }

        $mutasi = MutasiPenduduk::create([
            'user_id' => $user->id,
            'from_region_id' => $fromDesaId,
            'to_region_id' => $request->to_region_id,
            'status' => 'pending_asal',
            'requested_by' => 'user',
            'reason' => $request->reason,
            'alamat_baru' => $request->alamat_baru,
            'rt_baru' => $request->rt_baru,
            'rw_baru' => $request->rw_baru,
            'ktp_image_path' => $ktpPath,
        ]);

        \App\Models\AdminNotification::create([
            'type' => 'mutasi',
            'title' => 'Pengajuan Mutasi Penduduk (Pelepasan)',
            'message' => ($user->name ?? 'Warga') . ' mengajukan permohonan pindah domisili. Menunggu persetujuan pelepasan desa.',
            'reference_id' => $mutasi->id,
            'region_id' => $fromDesaId,
            'is_read' => false,
        ]);

        // Notifikasi ke warga pemohon
        \App\Services\NotificationService::notifyMutasiSubmitted($mutasi);

        $successMsg = 'Pengajuan pindah desa berhasil dikirim dan sedang menunggu persetujuan pelepasan dari Pemerintah Desa asal.';
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $successMsg
            ]);
        }

        return redirect()->back()->with('success', $successMsg);
    }

    public function cancel(Request $request)
    {
        $user = Auth::user();

        $mutasi = MutasiPenduduk::where('user_id', $user->id)
                                ->whereIn('status', ['pending', 'pending_asal', 'pending_tujuan'])
                                ->first();

        if (!$mutasi) {
            $msg = 'Tidak ada permohonan pindah desa aktif yang dapat dibatalkan.';
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 404);
            }
            return redirect()->back()->with('error', $msg);
        }

        // Hapus berkas KTP jika ada
        if ($mutasi->ktp_image_path && \Illuminate\Support\Facades\Storage::disk('private')->exists($mutasi->ktp_image_path)) {
            \Illuminate\Support\Facades\Storage::disk('private')->delete($mutasi->ktp_image_path);
        }

        // Hapus notifikasi admin terkait permohonan ini
        \App\Models\AdminNotification::where('type', 'mutasi')
            ->where('reference_id', $mutasi->id)
            ->delete();

        $mutasi->delete();

        $successMsg = 'Pengajuan pindah desa berhasil dibatalkan.';
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $successMsg
            ]);
        }

        return redirect()->back()->with('success', $successMsg);
    }
}
