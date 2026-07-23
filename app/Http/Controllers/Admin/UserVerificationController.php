<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class UserVerificationController extends Controller
{
    public function index()
    {
        $admin = Auth::user();
        
        // Admin desa hanya melihat warganya saja
        $query = User::where('verification_status', 'pending');
        if (in_array($admin->role, ['admin_desa', 'admin_rt', 'admin_rw'])) {
            $query->where('region_id', $admin->region_id);
        }
        
        $pendingUsers = $query->orderBy('updated_at', 'asc')->paginate(10);
        return view('admin.warga.verifikasi', compact('pendingUsers'));
    }

    public function viewImage($type, $id)
    {
        $user = User::findOrFail($id);
        $admin = Auth::user();

        // Validasi akses region
        if (in_array($admin->role, ['admin_desa', 'admin_rt', 'admin_rw']) && $user->region_id != $admin->region_id) {
            abort(403, 'Anda tidak berhak melihat data wilayah lain.');
        }

        $path = $type === 'ktp' ? $user->ktp_photo_path : $user->face_photo_path;

        if (!$path || !Storage::disk('private')->exists($path)) {
            abort(404, 'File tidak ditemukan.');
        }

        return response()->file(storage_path('app/private/' . $path));
    }

    public function approve($id)
    {
        $user = User::findOrFail($id);
        $user->verification_status = 'verified';
        $user->verified_at = now();
        $user->save();

        return redirect()->back()->with('success', "Warga {$user->name} berhasil diverifikasi!");
    }

    public function reject(Request $request, $id)
    {
        $request->validate([
            'reason' => 'required|string|max:500'
        ]);

        $user = User::findOrFail($id);
        $user->verification_status = 'rejected';
        $user->ktp_rejection_reason = $request->reason;
        
        // Hapus file yang jelek agar tidak menuhi storage
        if ($user->ktp_photo_path) Storage::disk('private')->delete($user->ktp_photo_path);
        if ($user->face_photo_path) Storage::disk('private')->delete($user->face_photo_path);
        
        $user->ktp_photo_path = null;
        $user->face_photo_path = null;
        $user->save();

        return redirect()->back()->with('success', "Verifikasi warga {$user->name} ditolak karena: {$request->reason}");
    }
}
