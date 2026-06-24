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
        if (!in_array($user->role, ['admin_desa', 'lurah', 'admin_rw', 'super_admin', 'admin'])) {
            return redirect()->back()->with('error', 'Anda tidak memiliki hak akses ke halaman ini.');
        }

        if ($region_id) {
            // Jika ada region_id spesifik (Admin Desa ingin melihat RT di dalam RW tertentu)
            $parentRegion = Region::with(['children.users'])->find($region_id);
            // Idealnya tambahkan validasi apakah $parentRegion ini benar-benar di bawah kewenangan $user->region_id
        } elseif ($isSuperAdmin) {
            // Untuk keperluan testing/UI oleh Super Admin, ambil desa pertama sebagai sampel
            $parentRegion = Region::with(['children.users'])->whereIn('type', ['desa', 'kelurahan'])->first();
            if (!$parentRegion) {
                // Buat dummy region jika tidak ada di database sama sekali
                $parentRegion = new Region(['id' => 0, 'name' => 'Desa Simulasi (Super Admin)', 'type' => 'desa']);
            }
        } else {
            // Default: Ambil region milik admin yang sedang login
            $parentRegion = Region::with(['children.users'])->find($user->region_id);
        }
        
        if (!$parentRegion && !$isSuperAdmin) {
            return redirect()->back()->with('error', 'Wilayah Anda tidak ditemukan.');
        }

        $childrenQuery = Region::with(['users' => function($q) {
                $q->whereIn('role', ['admin_rw', 'admin_rt']);
            }, 'children.users' => function($q) {
                $q->where('role', 'admin_rt');
            }])
            ->orderBy('name', 'asc');

        if ($isSuperAdmin && $parentRegion->id === 0) {
            $childrenRegions = collect([]); // Kosongkan jika dummy
        } else {
            $childrenRegions = $childrenQuery->where('parent_id', $parentRegion->id)->get();
        }

        $targetType = '';
        if (in_array($parentRegion->type, ['desa', 'kelurahan'])) {
            $targetType = 'rw';
        } elseif ($parentRegion->type == 'rw') {
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
            'type' => ['required', Rule::in(['rw', 'rt'])],
            'parent_id' => 'nullable|exists:regions,id',
            'admin_name' => 'nullable|string|max:255',
            'admin_email' => 'nullable|string|email|max:255|unique:users,email',
            'admin_password' => 'nullable|string|min:8',
        ]);

        $user = auth()->user();
        
        // Tentukan parent: Jika request membawa parent_id, gunakan itu. Jika tidak, gunakan region milik user.
        $targetParentId = $request->input('parent_id', $user->region_id);
        
        // Untuk super admin testing
        $isSuperAdmin = in_array($user->role, ['super_admin', 'admin']);
        if ($isSuperAdmin && !$request->filled('parent_id')) {
             $targetParentId = Region::whereIn('type', ['desa', 'kelurahan'])->first()->id ?? 0;
        }

        $parentRegion = Region::find($targetParentId);
        if (!$parentRegion && !($isSuperAdmin && $targetParentId === 0)) {
            return back()->with('error', 'Wilayah parent tidak ditemukan.');
        }

        if ($parentRegion && in_array($parentRegion->type, ['desa', 'kelurahan']) && $request->type !== 'rw') {
            return back()->with('error', 'Desa/Kelurahan hanya dapat menambahkan struktur RW.');
        }

        if ($parentRegion && $parentRegion->type === 'rw' && $request->type !== 'rt') {
            return back()->with('error', 'RW hanya dapat menambahkan struktur RT.');
        }

        $newRegion = Region::create([
            'name' => $request->name,
            'type' => $request->type,
            'parent_id' => $targetParentId,
        ]);

        if ($request->filled('admin_name') && $request->filled('admin_email')) {
            $role = ($request->type == 'rw') ? 'admin_rw' : 'admin_rt';
            User::create([
                'name' => $request->admin_name,
                'email' => $request->admin_email,
                'username' => 'admin_' . $request->type . '_' . uniqid(),
                'password' => Hash::make($request->admin_password ?: 'password123'),
                'role' => $role,
                'region_id' => $newRegion->id,
                'status' => 'aktif',
            ]);
            return back()->with('success', 'Struktur wilayah dan akun admin berhasil ditambahkan.');
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
        
        // Verifikasi bahwa region ini benar anak dari region admin
        if ($region->parent_id !== auth()->user()->region_id) {
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
        
        if ($region->parent_id !== auth()->user()->region_id) {
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
            'password' => 'nullable|string|min:8',
        ]);

        $region = Region::findOrFail($region_id);
        
        if ($region->parent_id !== auth()->user()->region_id) {
            return back()->with('error', 'Akses ditolak.');
        }

        // Tentukan Role
        $role = '';
        if ($region->type == 'rw') $role = 'admin_rw';
        elseif ($region->type == 'rt') $role = 'admin_rt';
        else {
            return back()->with('error', 'Tipe wilayah tidak didukung untuk pembuatan akun admin otomatis.');
        }

        // Buat User
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'username' => 'admin_' . $region->type . '_' . uniqid(),
            'password' => Hash::make($request->password ?: 'password123'),
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
        
        if (!in_array($user->role, ['admin_desa', 'lurah', 'super_admin', 'admin', 'admin_rw'])) {
            return redirect()->back()->with('error', 'Anda tidak memiliki hak akses.');
        }

        $targetUser = User::findOrFail($user_id);
        
        // Pastikan user tersebut adalah admin_rw atau admin_rt
        if (!in_array($targetUser->role, ['admin_rw', 'admin_rt'])) {
            return back()->with('error', 'Hanya akun pengurus yang dapat dihapus.');
        }

        $targetUser->delete();

        return back()->with('success', 'Akun pengurus berhasil dihapus.');
    }
}
