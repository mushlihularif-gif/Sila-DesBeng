<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Region;
use App\Models\PartnerApplication;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class RegionAdminManagementController extends Controller
{
    public function index()
    {
        $admin = auth()->user();
        
        // Hanya Admin Desa yang bisa mengelola Admin RT/RW
        if ($admin->role !== 'admin_desa') {
            abort(403, 'Akses ditolak. Hanya Admin Desa yang dapat mengelola Admin RT/RW.');
        }

        $desaId = $admin->region_id;

        // Ambil semua RW dan RT di bawah desa ini
        $rwIds = Region::where('parent_id', $desaId)->where('type', 'rw')->pluck('id')->toArray();
        $rtIds = Region::whereIn('parent_id', $rwIds)->where('type', 'rt')->pluck('id')->toArray();
        
        $regionIds = array_merge($rwIds, $rtIds);

        // Active Admins
        $admins = User::whereIn('region_id', $regionIds)
            ->whereIn('role', ['admin_rw', 'admin_rt'])
            ->with('region')
            ->get();

        // Pending Applications
        // Only applications that target RWs and RTs under this Desa
        $applications = PartnerApplication::whereIn('region_type', ['rw', 'rt'])
            ->whereIn('parent_region_id', array_merge([$desaId], $rwIds))
            ->where('status', 'pending')
            ->get();

        // Daftar RW dan RT untuk form pembuatan
        $rws = Region::where('parent_id', $desaId)->where('type', 'rw')->get();
        $rts = Region::whereIn('parent_id', $rwIds)->where('type', 'rt')->with('parent')->get();

        // Daftar Warga untuk form promosi
        $wargaList = User::where('role', 'user')->get(['id', 'name', 'email', 'phone', 'nik', 'avatar', 'ktp_photo_path']);

        return view('admin.wilayah-admins.index', compact('admins', 'applications', 'rws', 'rts', 'wargaList'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|string|max:20',
            'role' => 'required|in:admin_rw,admin_rt',
            'region_id' => 'required|exists:regions,id',
            'nik' => 'nullable|string|size:16', // Nullable for bypass mode
        ]);

        if ($request->filled('nik')) {
            $existing = User::where('nik', $request->nik)->first();
            // If NIK exists, and they didn't explicitly check a bypass flag (handled by frontend removing NIK)
            // Wait, if frontend empties the NIK, it will bypass.
            // If it's still filled and exists, we throw error.
            if ($existing) {
                return back()->withInput()->with('duplicate_nik', $existing->id)
                    ->with('error', "Peringatan: NIK ini sudah terdaftar sebagai akun warga atas nama {$existing->name}! Silakan gunakan fitur Promosi Akun, ATAU kosongkan kolom NIK ini jika Anda ingin tetap membuat akun khusus dinas (Akun Tanpa NIK).");
            }
        }

        $password = Str::random(8);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($password),
            'phone' => $request->phone,
            'role' => $request->role,
            'region_id' => $request->region_id,
            'nik' => $request->nik,
            // Verification status: if they have NIK it's essentially verified as citizen. 
            // Even if no NIK, it's an official account (Jalur VIP), so we verify them immediately.
            'verification_status' => 'verified',
            'ktp_path' => null,
            'selfie_path' => null,
            'points' => 0,
        ]);

        return back()->with('success', "Akun berhasil dibuat! Email: {$user->email} | Password sementara: {$password} | Harap catat password ini.");
    }

    public function approveApplication(Request $request, $id)
    {
        $application = PartnerApplication::findOrFail($id);
        
        if ($application->status !== 'pending') {
            return back()->with('error', 'Aplikasi ini sudah diproses sebelumnya.');
        }

        $user = User::findOrFail($application->user_id);

        // Find or create the target region
        // $application->region_name is the requested name, $application->parent_region_id is the parent (Desa or RW)
        $targetRegion = Region::firstOrCreate(
            ['name' => $application->region_name, 'type' => $application->region_type, 'parent_id' => $application->parent_region_id]
        );

        // Update user
        $user->role = 'admin_' . $application->region_type; // admin_rw or admin_rt
        $user->region_id = $targetRegion->id;
        $user->save();

        $application->status = 'approved';
        $application->save();

        return back()->with('success', "Pengajuan berhasil disetujui. Akun {$user->name} kini menjadi " . strtoupper($user->role) . " di " . $targetRegion->name);
    }
    
    public function rejectApplication(Request $request, $id)
    {
        $application = PartnerApplication::findOrFail($id);
        $application->status = 'rejected';
        $application->save();

        return back()->with('success', 'Pengajuan berhasil ditolak.');
    }

    public function promote(Request $request)
    {
        $request->validate([
            'user_email' => 'required|email|exists:users,email',
            'role' => 'required|in:admin_rw,admin_rt',
            'region_id' => 'required|exists:regions,id',
        ]);

        $user = User::where('email', $request->user_email)->first();
        
        if (in_array($user->role, ['admin', 'super_admin', 'admin_desa'])) {
            return back()->with('error', 'Tidak dapat mengubah role akun ini (Akun Super/Desa).');
        }

        $user->role = $request->role;
        $user->region_id = $request->region_id;
        $user->save();

        return back()->with('success', "Akun {$user->name} berhasil dipromosikan menjadi " . strtoupper($user->role) . ".");
    }
    
    public function revoke($id)
    {
        $user = User::findOrFail($id);
        
        if (in_array($user->role, ['admin_rw', 'admin_rt'])) {
            $user->role = 'user';
            $user->save();
            return back()->with('success', 'Akses admin dicabut. Akun kembali menjadi warga biasa.');
        }
        
        return back()->with('error', 'Tidak dapat mencabut akses admin ini.');
    }
}
