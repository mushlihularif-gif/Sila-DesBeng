<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Region;
use App\Models\User;
use App\Support\BengkalisScope;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class RegionManagementController extends Controller
{
    private function manager(): User
    {
        $user = auth()->user();
        abort_unless($user && in_array($user->role, ['super_admin', 'admin_desa', 'admin_rw'], true), 403);
        if (!$user->isSuperAdmin()) {
            abort_unless($user->region_id && (User::REGION_ROLES[$user->region?->type] ?? null) === $user->role, 403);
        }
        return $user;
    }

    private function assertRegionScope(Region $region, bool $includeOwn = false): void
    {
        $user = $this->manager();
        if ($user->isSuperAdmin()) {
            BengkalisScope::assertContains($region, $includeOwn);
            return;
        }
        $ids = Region::getDescendantIds($user->region_id);
        if ($includeOwn) $ids[] = $user->region_id;
        abort_unless(in_array((int) $region->id, array_map('intval', $ids), true), 404);
    }

    public function index($region_id = null)
    {
        $user = $this->manager();
        $isSuperAdmin = $user->isSuperAdmin();
        
        if ($region_id) {
            $parentRegion = Region::with(['children.users.file'])->findOrFail($region_id);
            $this->assertRegionScope($parentRegion, true);
        } elseif ($isSuperAdmin) {
            $parentRegion = BengkalisScope::root();
        } else {
            // Admin desa/RW hanya membuka struktur di wilayah tugasnya.
            $parentRegion = Region::with(['children.users.file'])->find($user->region_id);
        }
        
        if (!$parentRegion && !$isSuperAdmin) {
            return redirect()->back()->with('error', 'Wilayah Anda tidak ditemukan.');
        }

        $childrenQuery = Region::with(['users.file', 'children.users.file'])->orderBy('name', 'asc');

        $childrenRegions = $childrenQuery->where('parent_id', $parentRegion->id)->get();
        $targetType = match ($parentRegion->type) {
            'kecamatan' => 'desa',
            'desa', 'kelurahan' => 'rw',
            'rw' => 'rt',
            default => '',
        };

        return view('admin.region_management.index', compact('parentRegion', 'childrenRegions', 'targetType'));
    }

    /**
     * Menyimpan data wilayah baru (RT/RW)
     */
    public function store(Request $request)
    {
        abort_unless(in_array($request->input('type'), ['desa', 'kelurahan', 'rw', 'rt'], true), 422);
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => ['required', Rule::in(['desa', 'kelurahan', 'rw', 'rt'])],
            'parent_id' => 'nullable|exists:regions,id',
            'admin_name' => 'required_with:admin_email|nullable|string|max:255',
            'admin_email' => 'nullable|string|email|max:255|unique:users,email',
            'admin_password' => 'required_with:admin_email|nullable|string|min:8',
        ]);

        $user = $this->manager();
        
        // Tentukan parent: Jika request membawa parent_id, gunakan itu. Jika tidak, gunakan region milik user.
        $targetParentId = $request->input('parent_id', $user->region_id);
        
        $isSuperAdmin = $user->isSuperAdmin();
        if ($isSuperAdmin && !$request->filled('parent_id')) {
            $targetParentId = BengkalisScope::root()->id;
        }

        $parentRegion = Region::find($targetParentId);
        if (!$parentRegion) {
            return back()->with('error', 'Wilayah parent tidak ditemukan.');
        }

        if ($parentRegion) $this->assertRegionScope($parentRegion, true);
        $allowedChildren = ['kecamatan' => ['desa', 'kelurahan'], 'desa' => ['rw'], 'kelurahan' => ['rw'], 'rw' => ['rt']];
        abort_unless(in_array($request->type, $allowedChildren[$parentRegion->type] ?? [], true), 422);
        if ($request->filled('admin_email')) {
            abort_unless(isset(User::REGION_ROLES[$request->type]), 422, 'Wilayah ini tidak memiliki peran admin.');
        }

        // Sanitasi KETAT nama wilayah agar tidak ada duplikasi akibat salah ketik (spasi/huruf besar-kecil)
        $cleanName = ucwords(strtolower(trim(preg_replace('/\s+/', ' ', $request->name))));

        // Pastikan prefix otomatis untuk meminimalisir duplikasi
        if (in_array($request->type, ['desa', 'kelurahan']) && !str_starts_with(strtolower($cleanName), 'desa') && !str_starts_with(strtolower($cleanName), 'kelurahan')) {
            $cleanName = ($request->type === 'kelurahan' ? 'Kelurahan ' : 'Desa ') . $cleanName;
        }

        $newRegion = Region::firstOrCreate([
            'name' => $cleanName,
            'type' => $request->type,
            'parent_id' => $targetParentId,
        ]);

        if ($request->filled('admin_name') && $request->filled('admin_email')) {
            $roleMapping = User::REGION_ROLES;
            
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
        $this->assertRegionScope($region);

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
        $this->assertRegionScope($region);

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
        $this->assertRegionScope($region);

        // Tentukan Role
        $roleMapping = User::REGION_ROLES;
        
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
        $user = $this->manager();
        
        if (!in_array($user->role, ['admin_desa', 'super_admin', 'admin_rw'])) {
            return redirect()->back()->with('error', 'Anda tidak memiliki hak akses.');
        }

        $targetUser = User::findOrFail($user_id);
        abort_unless($targetUser->region && $targetUser->id !== $user->id, 403);
        $this->assertRegionScope($targetUser->region);
        
        // Pastikan user tersebut adalah akun pengurus wilayah
        if (!in_array($targetUser->role, ['admin_desa', 'admin_rw', 'admin_rt'])) {
            return back()->with('error', 'Hanya akun pengurus wilayah yang dapat dihapus.');
        }

        $targetUser->delete();

        return back()->with('success', 'Akun pengurus berhasil dihapus.');
    }
}
