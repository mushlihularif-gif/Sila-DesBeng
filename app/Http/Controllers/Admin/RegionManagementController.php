<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Region;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class RegionManagementController extends Controller
{
    public function index($region_id = null)
    {
        $user = auth()->user();
        $isSuperAdmin = in_array($user->role, ['super_admin', 'admin']);
        
        // Izinkan super_admin untuk melihat halaman ini (kebutuhan testing)
        if (!in_array($user->role, ['admin_kecamatan', 'admin_desa', 'lurah', 'admin_rw', 'super_admin', 'admin'])) {
            return redirect()->back()->with('error', 'Anda tidak memiliki hak akses ke halaman ini.');
        }

        if ($region_id) {
            $parentRegion = Region::with(['children.users'])->find($region_id);
        } elseif ($isSuperAdmin) {
            // Admin Kabupaten: Tampilkan Kabupaten Bengkalis (root region)
            $parentRegion = Region::with(['children.users'])->whereNull('parent_id')->orWhere('parent_id', 0)->first();
            if (!$parentRegion) {
                $parentRegion = Region::with(['children.users'])->where('type', 'kabupaten')->first();
            }
        } else {
            // Admin tingkat lain (Kecamatan, Desa, RW, dll)
            $parentRegion = Region::with(['children.users'])->find($user->region_id);
        }
        
        if (!$parentRegion && !$isSuperAdmin) {
            return redirect()->back()->with('error', 'Wilayah Anda tidak ditemukan.');
        }

        $childrenQuery = Region::with(['users', 'children.users'])->orderBy('name', 'asc');

        if ($parentRegion) {
            $childrenRegions = $childrenQuery->where('parent_id', $parentRegion->id)->get();
        } else {
            $childrenRegions = collect([]);
        }

        $targetType = '';
        if ($parentRegion && $parentRegion->type == 'kabupaten') {
            $targetType = 'kecamatan';
        } elseif ($parentRegion && $parentRegion->type == 'kecamatan') {
            $targetType = 'desa';
        } elseif ($parentRegion && in_array($parentRegion->type, ['desa', 'kelurahan'])) {
            $targetType = 'rw';
        } elseif ($parentRegion && $parentRegion->type == 'rw') {
            $targetType = 'rt';
        }

        return view('admin.region_management.index', compact('parentRegion', 'childrenRegions', 'targetType'));
    }

    /**
     * Menyimpan data wilayah baru (RT/RW)
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => ['required', Rule::in(['kecamatan', 'desa', 'kelurahan', 'rw', 'rt'])],
            'parent_id' => 'nullable|exists:regions,id',
            'admin_name' => 'nullable|string|max:255',
            'admin_email' => 'nullable|string|email|max:255|unique:users,email',
            'admin_password' => 'required_with:admin_email|string|min:8',
        ]);

        $user = auth()->user();
        
        // Tentukan parent: Jika request membawa parent_id, gunakan itu. Jika tidak, gunakan region milik user.
        $targetParentId = $request->input('parent_id', $user->region_id);
        
        $isSuperAdmin = in_array($user->role, ['super_admin', 'admin']);
        if ($isSuperAdmin && !$request->filled('parent_id')) {
             $kabupaten = Region::where('type', 'kabupaten')->first() ?? Region::whereNull('parent_id')->first();
             $targetParentId = $kabupaten ? $kabupaten->id : 0;
        }

        $parentRegion = Region::find($targetParentId);
        if (!$parentRegion && !($isSuperAdmin && $targetParentId === 0)) {
            return back()->with('error', 'Wilayah parent tidak ditemukan.');
        }

        // Validasikan tipe turunan yang diizinkan
        if ($parentRegion && $parentRegion->type === 'kabupaten' && $request->type !== 'kecamatan') {
            return back()->with('error', 'Kabupaten hanya dapat menambahkan struktur Kecamatan.');
        }
        if ($parentRegion && $parentRegion->type === 'kecamatan' && !in_array($request->type, ['desa', 'kelurahan'])) {
            return back()->with('error', 'Kecamatan hanya dapat menambahkan struktur Desa/Kelurahan.');
        }
        if ($parentRegion && in_array($parentRegion->type, ['desa', 'kelurahan']) && $request->type !== 'rw') {
            return back()->with('error', 'Desa/Kelurahan hanya dapat menambahkan struktur RW.');
        }
        if ($parentRegion && $parentRegion->type === 'rw' && $request->type !== 'rt') {
            return back()->with('error', 'RW hanya dapat menambahkan struktur RT.');
        }

        $newRegion = Region::firstOrCreate([
            'name' => $request->name,
            'type' => $request->type,
            'parent_id' => $targetParentId,
        ]);

        if ($request->filled('admin_name') && $request->filled('admin_email')) {
            $roleMapping = [
                'kecamatan' => 'admin_kecamatan',
                'desa' => 'admin_desa',
                'kelurahan' => 'admin_desa',
                'rw' => 'admin_rw',
                'rt' => 'admin_rt',
            ];
            
            $role = $roleMapping[$request->type] ?? null;
            
            if ($role) {
                User::create([
                    'name' => $request->admin_name,
                    'email' => $request->admin_email,
                    'username' => 'admin_' . $request->type . '_' . uniqid(),
                    'password' => Hash::make($request->admin_password),
                    'role' => $role,
                    'region_id' => $newRegion->id,
                    'status' => 'aktif',
                ]);
                return back()->with('success', 'Struktur wilayah dan akun admin berhasil ditambahkan.');
            }
        }

        return back()->with('success', 'Struktur wilayah baru berhasil ditambahkan.');
    }

    /**
     * Update nama wilayah
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $region = Region::findOrFail($id);
        
        $user = auth()->user();
        $isSuperAdmin = in_array($user->role, ['super_admin', 'admin']);

        // Verifikasi bahwa region ini benar anak dari region admin
        if (!$isSuperAdmin && $region->parent_id !== $user->region_id) {
            return back()->with('error', 'Akses ditolak.');
        }

        $region->update([
            'name' => $request->name
        ]);

        return back()->with('success', 'Nama wilayah berhasil diperbarui.');
    }

    /**
     * Menghapus wilayah (Hanya jika belum ada admin atau transaksi)
     */
    public function destroy($id)
    {
        $region = Region::findOrFail($id);
        
        $user = auth()->user();
        $isSuperAdmin = in_array($user->role, ['super_admin', 'admin']);
        
        if (!$isSuperAdmin && $region->parent_id !== $user->region_id) {
            return back()->with('error', 'Akses ditolak.');
        }

        // Cek jika sudah ada user
        if ($region->users()->count() > 0) {
            return back()->with('error', 'Tidak dapat menghapus wilayah karena sudah ada pengguna yang terdaftar di wilayah ini.');
        }

        // Cek jika punya anak wilayah
        if ($region->children()->count() > 0) {
            return back()->with('error', 'Tidak dapat menghapus wilayah ini karena masih memiliki struktur wilayah di bawahnya.');
        }

        $region->delete();

        return back()->with('success', 'Wilayah berhasil dihapus.');
    }

    /**
     * Membuatkan akun admin untuk region tertentu
     */
    public function generateAdmin(Request $request, $region_id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
        ]);

        $region = Region::findOrFail($region_id);
        
        $user = auth()->user();
        $isSuperAdmin = in_array($user->role, ['super_admin', 'admin']);
        
        if (!$isSuperAdmin && $region->parent_id !== $user->region_id) {
            return back()->with('error', 'Akses ditolak.');
        }

        // Tentukan Role
        $roleMapping = [
            'kecamatan' => 'admin_kecamatan',
            'desa' => 'admin_desa',
            'kelurahan' => 'admin_desa',
            'rw' => 'admin_rw',
            'rt' => 'admin_rt',
        ];
        
        $role = $roleMapping[$region->type] ?? null;
        
        if (!$role) {
            return back()->with('error', 'Tipe wilayah tidak didukung untuk pembuatan akun admin otomatis.');
        }

        // Buat User
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'username' => 'admin_' . $region->type . '_' . uniqid(),
            'password' => Hash::make($request->password),
            'role' => $role,
            'region_id' => $region->id,
            'status' => 'aktif',
        ]);

        return back()->with('success', 'Akun admin berhasil dibuat. Silakan serahkan email dan password kepada pengurus terkait.');
    }

    /**
     * Hapus Akun Admin secara permanen tanpa menghapus wilayahnya
     */
    public function destroyAdmin($user_id)
    {
        $user = auth()->user();
        
        if (!in_array($user->role, ['admin_kecamatan', 'admin_desa', 'lurah', 'super_admin', 'admin', 'admin_rw'])) {
            return redirect()->back()->with('error', 'Anda tidak memiliki hak akses.');
        }

        $targetUser = User::findOrFail($user_id);
        
        // Pastikan user tersebut adalah akun pengurus wilayah
        if (!in_array($targetUser->role, ['admin_kecamatan', 'admin_desa', 'admin_rw', 'admin_rt'])) {
            return back()->with('error', 'Hanya akun pengurus wilayah yang dapat dihapus.');
        }

        $targetUser->delete();

        return back()->with('success', 'Akun pengurus berhasil dihapus.');
    }
}
