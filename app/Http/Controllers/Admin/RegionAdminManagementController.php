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
    private function getValidRegionIds($admin)
    {
        if ($admin->role !== 'admin_desa') return [];
        $rwIds = Region::where('parent_id', $admin->region_id)->where('type', 'rw')->pluck('id')->toArray();
        $rtIds = Region::whereIn('parent_id', $rwIds)->where('type', 'rt')->pluck('id')->toArray();
        return array_merge($rwIds, $rtIds);
    }

    public function index()
    {
        $admin = auth()->user();
        if ($admin->role !== 'admin_desa') {
            abort(403, 'Akses ditolak. Hanya Admin Desa yang dapat mengelola Admin RT/RW.');
        }

        $desaId = $admin->region_id;
        $rwIds = Region::where('parent_id', $desaId)->where('type', 'rw')->pluck('id')->toArray();
        
        $regionIds = $this->getValidRegionIds($admin);

        // Active Admins
        $admins = User::whereIn('region_id', $regionIds)
            ->whereIn('role', ['admin_rw', 'admin_rt'])
            ->with('region')
            ->get();

        // Pending Applications
        $applications = PartnerApplication::whereIn('region_type', ['rw', 'rt'])
            ->whereIn('parent_region_id', array_merge([$desaId], $rwIds))
            ->where('status', 'pending')
            ->get();

        // Daftar RW dan RT untuk form pembuatan
        $rws = Region::where('parent_id', $desaId)->where('type', 'rw')->get();
        $rts = Region::whereIn('parent_id', $rwIds)->where('type', 'rt')->with('parent')->get();

        // Daftar Warga untuk form promosi (Hanya warga dari desa ini)
        $wargaList = User::whereIn('role', ['user', 'warga'])
            ->where('region_id', $desaId)
            ->get(['id', 'name', 'email', 'phone', 'nik', 'avatar', 'ktp_photo_path']);

        return view('admin.wilayah-admins.index', compact('admins', 'applications', 'rws', 'rts', 'wargaList'));
    }

    public function store(Request $request)
    {
        $admin = auth()->user();
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|string|max:20',
            'role' => 'required|in:admin_rw,admin_rt',
            'region_id' => 'required|exists:regions,id',
            'nik' => 'nullable|string|size:16',
        ]);

        $validRegions = $this->getValidRegionIds($admin);
        if (!in_array($request->region_id, $validRegions)) {
            return back()->with('error', 'Akses ditolak: Anda tidak dapat membuat admin untuk wilayah di luar wewenang desa Anda.');
        }

        if ($request->filled('nik')) {
            $existing = User::where('nik', $request->nik)->first();
            if ($existing) {
                return back()->withInput()->with('duplicate_nik', $existing->id)
                    ->with('error', "Peringatan: NIK ini sudah terdaftar atas nama {$existing->name}! Gunakan fitur 'Jadikan Warga RT/RW' atau kosongkan NIK untuk buat Akun Dinas.");
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
            'verification_status' => 'verified',
            'ktp_path' => null,
            'selfie_path' => null,
            'points' => 0,
        ]);

        return back()->with('success', "Akun berhasil dibuat! Email: {$user->email} | Password: {$password} | Harap catat password ini.");
    }

    public function approveApplication(Request $request, $id)
    {
        $admin = auth()->user();
        $application = PartnerApplication::findOrFail($id);
        
        $desaId = $admin->region_id;
        $rwIds = Region::where('parent_id', $desaId)->where('type', 'rw')->pluck('id')->toArray();
        $validParentRegions = array_merge([$desaId], $rwIds);

        if (!in_array($application->parent_region_id, $validParentRegions)) {
            return back()->with('error', 'Akses ditolak: Pengajuan ini berada di luar wewenang desa Anda.');
        }

        if ($application->status !== 'pending') {
            return back()->with('error', 'Aplikasi ini sudah diproses sebelumnya.');
        }

        $user = User::findOrFail($application->user_id);

        $targetRegion = Region::firstOrCreate(
            ['name' => $application->region_name, 'type' => $application->region_type, 'parent_id' => $application->parent_region_id]
        );

        $user->role = 'admin_' . $application->region_type;
        $user->region_id = $targetRegion->id;
        $user->save();

        $application->status = 'approved';
        $application->save();

        return back()->with('success', "Pengajuan disetujui. {$user->name} kini menjadi " . strtoupper($user->role) . ".");
    }
    
    public function rejectApplication(Request $request, $id)
    {
        $admin = auth()->user();
        $application = PartnerApplication::findOrFail($id);

        $desaId = $admin->region_id;
        $rwIds = Region::where('parent_id', $desaId)->where('type', 'rw')->pluck('id')->toArray();
        $validParentRegions = array_merge([$desaId], $rwIds);

        if (!in_array($application->parent_region_id, $validParentRegions)) {
            return back()->with('error', 'Akses ditolak: Pengajuan ini berada di luar wewenang desa Anda.');
        }

        $application->status = 'rejected';
        $application->save();

        return back()->with('success', 'Pengajuan berhasil ditolak.');
    }

    public function promote(Request $request)
    {
        $admin = auth()->user();
        $request->validate([
            'user_email' => 'required|email|exists:users,email',
            'role' => 'required|in:admin_rw,admin_rt',
            'region_id' => 'required|exists:regions,id',
        ]);

        $validRegions = $this->getValidRegionIds($admin);
        if (!in_array($request->region_id, $validRegions)) {
            return back()->with('error', 'Akses ditolak: Anda tidak dapat menempatkan admin di luar wilayah desa Anda.');
        }

        $user = User::where('email', $request->user_email)->first();
        
        if ($user->region_id !== $admin->region_id && !in_array($user->region_id, $validRegions)) {
            return back()->with('error', 'Akses ditolak: Anda hanya dapat mengangkat warga dari desa Anda sendiri.');
        }
        
        if (in_array($user->role, ['admin', 'super_admin', 'admin_desa'])) {
            return back()->with('error', 'Tidak dapat mengubah role akun ini (Akun Super/Desa).');
        }

        $user->role = $request->role;
        $user->region_id = $request->region_id;
        $user->save();

        return back()->with('success', "Akun {$user->name} berhasil diangkat menjadi " . strtoupper($user->role) . ".");
    }
    
    public function revoke($id)
    {
        $admin = auth()->user();
        $user = User::findOrFail($id);
        
        $validRegions = $this->getValidRegionIds($admin);
        if (!in_array($user->region_id, $validRegions)) {
            return back()->with('error', 'Akses ditolak: Admin RT/RW ini berada di luar wewenang desa Anda.');
        }
        
        if (in_array($user->role, ['admin_rw', 'admin_rt'])) {
            $user->role = 'user';
            $user->save();
            return back()->with('success', 'Akses admin dicabut. Akun kembali menjadi warga biasa.');
        }
        
        return back()->with('error', 'Tidak dapat mencabut akses admin ini.');
    }
}
