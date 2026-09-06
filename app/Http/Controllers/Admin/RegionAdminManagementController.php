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
            ->with(['region.parent', 'file'])
            ->get();

        // Urutkan admin terstruktur: Kelompok RW, Admin RW di atas, disusul Admin RT
        $admins = $admins->sort(function ($a, $b) {
            $rwA = $a->region ? ($a->region->type === 'rw' ? $a->region->name : ($a->region->parent->name ?? '')) : '';
            $rwB = $b->region ? ($b->region->type === 'rw' ? $b->region->name : ($b->region->parent->name ?? '')) : '';
            
            if ($rwA !== $rwB) {
                return strnatcasecmp($rwA, $rwB);
            }
            
            // RW admin di atas (0), lalu RT admin (1)
            $roleOrderA = $a->role === 'admin_rw' ? 0 : 1;
            $roleOrderB = $b->role === 'admin_rw' ? 0 : 1;
            if ($roleOrderA !== $roleOrderB) {
                return $roleOrderA <=> $roleOrderB;
            }
            
            $nameA = $a->region->name ?? '';
            $nameB = $b->region->name ?? '';
            return strnatcasecmp($nameA, $nameB);
        })->values();

        // Pending Applications
        $applications = PartnerApplication::whereIn('region_type', ['rw', 'rt'])
            ->whereIn('parent_region_id', array_merge([$desaId], $rwIds))
            ->where('status', 'pending')
            ->with(['user.file', 'parentRegion'])
            ->get();

        // Daftar RW dan RT untuk form pembuatan
        $rws = Region::where('parent_id', $desaId)->where('type', 'rw')->get();
        $rts = Region::whereIn('parent_id', $rwIds)->where('type', 'rt')->with('parent')->get();

        // Data Terstruktur Riil untuk Validasi Otomatis di Frontend
        $rwAdminMap = [];
        foreach ($rws as $rw) {
            $adminUser = $admins->firstWhere('region_id', $rw->id);
            $rwAdminMap[] = [
                'id' => $rw->id,
                'name' => $rw->name,
                'has_admin' => $adminUser ? true : false,
                'admin_name' => $adminUser ? $adminUser->name : null,
                'admin_email' => $adminUser ? $adminUser->email : null,
            ];
        }

        $rtAdminMap = [];
        foreach ($rts as $rt) {
            $adminUser = $admins->firstWhere('region_id', $rt->id);
            $rtAdminMap[] = [
                'id' => $rt->id,
                'name' => $rt->name,
                'parent_id' => $rt->parent_id,
                'parent_name' => $rt->parent ? $rt->parent->name : 'RW',
                'has_admin' => $adminUser ? true : false,
                'admin_name' => $adminUser ? $adminUser->name : null,
                'admin_email' => $adminUser ? $adminUser->email : null,
            ];
        }

        // Daftar Warga untuk form promosi (Warga desa ini, RT/RW binaan, atau belum berwilayah)
        $wargaList = User::whereIn('role', ['user', 'warga'])
            ->where(function ($q) use ($desaId, $regionIds) {
                $q->where('region_id', $desaId)
                  ->orWhereIn('region_id', $regionIds)
                  ->orWhereNull('region_id');
            })
            ->with('file')
            ->get(['id', 'name', 'email', 'phone', 'nik', 'avatar', 'ktp_photo_path']);

        $wargaJson = $wargaList->map(function ($warga) {
            $photo = null;
            if ($warga->file && !empty($warga->file->file_stream)) {
                $photo = $warga->file->file_stream;
            } elseif ($warga->avatar && !empty($warga->avatar)) {
                $photo = asset('storage/' . $warga->avatar);
            }

            return [
                'id' => $warga->id,
                'name' => $warga->name,
                'email' => $warga->email,
                'phone' => $warga->phone ?? '-',
                'nik_status' => $warga->nik ? 'Terverifikasi' : 'Belum KTP',
                'is_verified' => $warga->nik ? true : false,
                'photo' => $photo,
                'initials' => strtoupper(substr(trim($warga->name), 0, 2)),
            ];
        })->values();

        // Struktur Hierarki Wilayah (RW & RT) beserta keterisian pejabat dan jumlah warga
        $rwStructure = Region::where('parent_id', $desaId)
            ->where('type', 'rw')
            ->with([
                'children' => function($q) {
                    $q->where('type', 'rt')->withCount(['users as citizens_count' => function($qu) {
                        $qu->where('role', 'user');
                    }])->orderBy('name', 'asc');
                },
                'children.users' => function($q) {
                    $q->where('role', 'admin_rt')->with('file');
                },
                'users' => function($q) {
                    $q->where('role', 'admin_rw')->with('file');
                }
            ])
            ->withCount(['users as citizens_count' => function($qu) {
                $qu->where('role', 'user');
            }])
            ->orderBy('name', 'asc')
            ->get();

        return view('admin.wilayah-admins.index', compact('admins', 'applications', 'rws', 'rts', 'rwAdminMap', 'rtAdminMap', 'wargaList', 'wargaJson', 'rwStructure'));
    }

    private function formatRegionName(string $prefix, string $input): string
    {
        $clean = trim($input);
        $num = preg_replace('/^(rw|rt)[\s\.\-]*/i', '', $clean);
        $num = trim($num);
        if (is_numeric($num)) {
            return strtoupper($prefix) . ' ' . str_pad($num, 2, '0', STR_PAD_LEFT);
        }
        return strtoupper($prefix) . ' ' . strtoupper($num);
    }

    private function resolveTargetRegion(Request $request, $desaId, $excludeUserId = null)
    {
        if ($request->filled('region_id') && !$request->filled('rw_number') && !$request->filled('rt_number')) {
            $reg = Region::find($request->region_id);
            if ($reg) return ['region' => $reg];
        }

        if ($request->role === 'admin_rw') {
            if (!$request->filled('rw_number')) {
                return ['error' => 'Nomor RW wajib ditentukan.'];
            }

            $rwName = $this->formatRegionName('RW', $request->rw_number);

            // Cek apakah RW ini sudah ada di database desa
            $targetRegion = Region::where('parent_id', $desaId)
                ->where('type', 'rw')
                ->where(function ($q) use ($rwName, $request) {
                    $q->where('name', $rwName)
                      ->orWhere('name', 'LIKE', '%' . trim($request->rw_number) . '%');
                })
                ->first();

            if ($targetRegion) {
                // Cek apakah sudah ada admin aktif di RW ini
                $existingAdminQuery = User::where('region_id', $targetRegion->id)
                    ->where('role', 'admin_rw');
                if ($excludeUserId) {
                    $existingAdminQuery->where('id', '!=', $excludeUserId);
                }
                $existingAdmin = $existingAdminQuery->first();

                if ($existingAdmin) {
                    return ['error' => "Gagal: {$targetRegion->name} sudah memiliki Admin aktif ({$existingAdmin->name}). Silakan tentukan nomor RW lain."];
                }
            } else {
                $targetRegion = Region::create([
                    'name' => $rwName,
                    'type' => 'rw',
                    'parent_id' => $desaId,
                ]);
            }

            return ['region' => $targetRegion];
        } elseif ($request->role === 'admin_rt') {
            if (!$request->filled('rt_number')) {
                return ['error' => 'Nomor RT wajib ditentukan.'];
            }
            if (!$request->filled('parent_rw_id')) {
                return ['error' => 'RW Induk (wilayah RW tempat RT berada) wajib dipilih.'];
            }

            $parentRw = null;
            if (is_numeric($request->parent_rw_id)) {
                $parentRw = Region::where('id', $request->parent_rw_id)
                    ->where('parent_id', $desaId)
                    ->where('type', 'rw')
                    ->first();
            }

            if (!$parentRw) {
                $parentRwName = $this->formatRegionName('RW', $request->parent_rw_id);
                $parentRw = Region::firstOrCreate(
                    ['name' => $parentRwName, 'type' => 'rw', 'parent_id' => $desaId]
                );
            }

            $rtName = $this->formatRegionName('RT', $request->rt_number);

            $targetRegion = Region::where('parent_id', $parentRw->id)
                ->where('type', 'rt')
                ->where(function ($q) use ($rtName, $request) {
                    $q->where('name', $rtName)
                      ->orWhere('name', 'LIKE', '%' . trim($request->rt_number) . '%');
                })
                ->first();

            if ($targetRegion) {
                $existingAdminQuery = User::where('region_id', $targetRegion->id)
                    ->where('role', 'admin_rt');
                if ($excludeUserId) {
                    $existingAdminQuery->where('id', '!=', $excludeUserId);
                }
                $existingAdmin = $existingAdminQuery->first();

                if ($existingAdmin) {
                    return ['error' => "Gagal: {$targetRegion->name} di {$parentRw->name} sudah memiliki Admin aktif ({$existingAdmin->name}). Silakan tentukan nomor RT lain."];
                }
            } else {
                $targetRegion = Region::create([
                    'name' => $rtName,
                    'type' => 'rt',
                    'parent_id' => $parentRw->id,
                ]);
            }

            return ['region' => $targetRegion];
        }

        return ['error' => 'Tingkat jabatan tidak valid.'];
    }

    public function store(Request $request)
    {
        $admin = auth()->user();
        if ($admin->role !== 'admin_desa') {
            abort(403, 'Akses ditolak. Hanya Admin Desa yang dapat mengelola Admin RT/RW.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|string|max:20',
            'role' => 'required|in:admin_rw,admin_rt',
            'nik' => 'nullable|string|size:16',
        ]);

        $result = $this->resolveTargetRegion($request, $admin->region_id);
        if (isset($result['error'])) {
            return back()->withInput()->with('error', $result['error']);
        }
        $targetRegion = $result['region'];

        $validRegions = $this->getValidRegionIds($admin);
        if (!in_array($targetRegion->id, $validRegions)) {
            return back()->with('error', 'Akses ditolak: Anda tidak dapat membuat admin untuk wilayah di luar wewenang desa Anda.');
        }

        if ($request->filled('nik')) {
            $nikHash = hash_hmac('sha256', $request->nik, config('app.key'));
            $existing = User::where('nik_hash', $nikHash)->orWhere('nik', $request->nik)->first();
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
            'region_id' => $targetRegion->id,
            'nik' => $request->nik,
            'verification_status' => 'verified',
            'ktp_path' => null,
            'selfie_path' => null,
            'points' => 0,
        ]);

        return back()->with('success', "Akun berhasil dibuat! Email: {$user->email} | Password: {$password} | Wilayah: {$targetRegion->name} | Harap catat password ini.");
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
        if ($admin->role !== 'admin_desa') {
            abort(403, 'Akses ditolak. Hanya Admin Desa yang dapat mengelola Admin RT/RW.');
        }

        $request->validate([
            'user_email' => 'required|email|exists:users,email',
            'role' => 'required|in:admin_rw,admin_rt',
        ]);

        $user = User::where('email', $request->user_email)->first();

        $validRegions = $this->getValidRegionIds($admin);
        if ($user->region_id !== $admin->region_id && !in_array($user->region_id, $validRegions)) {
            return back()->with('error', 'Akses ditolak: Anda hanya dapat mengangkat warga dari desa Anda sendiri.');
        }

        if (in_array($user->role, ['admin', 'super_admin', 'admin_desa'])) {
            return back()->with('error', 'Tidak dapat mengubah role akun ini (Akun Super/Desa).');
        }

        $result = $this->resolveTargetRegion($request, $admin->region_id, $user->id);
        if (isset($result['error'])) {
            return back()->withInput()->with('error', $result['error']);
        }
        $targetRegion = $result['region'];

        if (!in_array($targetRegion->id, $validRegions)) {
            return back()->with('error', 'Akses ditolak: Anda tidak dapat menempatkan admin di luar wilayah desa Anda.');
        }

        $user->role = $request->role;
        $user->region_id = $targetRegion->id;
        $user->save();

        return back()->with('success', "Akun {$user->name} berhasil diangkat menjadi " . strtoupper($user->role) . " ({$targetRegion->name}).");
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
            $user->region_id = $admin->region_id;
            $user->save();
            return back()->with('success', 'Akses admin dicabut. Akun kembali menjadi warga biasa.');
        }
        
        return back()->with('error', 'Tidak dapat mencabut akses admin ini.');
    }

    /**
     * Tambah Struktur Wilayah Baru (RW atau RT)
     */
    public function storeRegion(Request $request)
    {
        $admin = auth()->user();
        if ($admin->role !== 'admin_desa') {
            abort(403, 'Akses ditolak. Hanya Admin Desa yang dapat mengelola wilayah.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:rw,rt',
            'parent_rw_id' => 'required_if:type,rt|nullable|exists:regions,id',
        ]);

        $desaId = $admin->region_id;

        if ($request->type === 'rw') {
            $formattedName = $this->formatRegionName('RW', $request->name);
            $exists = Region::where('parent_id', $desaId)->where('type', 'rw')->where('name', $formattedName)->exists();
            if ($exists) {
                return back()->withInput()->with('error', "Wilayah {$formattedName} sudah terdaftar di desa ini.")->with('active_tab', 'struktur');
            }

            Region::create([
                'name' => $formattedName,
                'type' => 'rw',
                'parent_id' => $desaId,
            ]);

            return back()->with('success', "Wilayah {$formattedName} berhasil ditambahkan.")->with('active_tab', 'struktur');
        } else {
            // RT
            $parentRw = Region::where('id', $request->parent_rw_id)->where('parent_id', $desaId)->where('type', 'rw')->firstOrFail();
            $formattedName = $this->formatRegionName('RT', $request->name);
            $exists = Region::where('parent_id', $parentRw->id)->where('type', 'rt')->where('name', $formattedName)->exists();
            if ($exists) {
                return back()->withInput()->with('error', "Wilayah {$formattedName} sudah terdaftar di {$parentRw->name}.")->with('active_tab', 'struktur');
            }

            Region::create([
                'name' => $formattedName,
                'type' => 'rt',
                'parent_id' => $parentRw->id,
            ]);

            return back()->with('success', "Wilayah {$formattedName} di bawah {$parentRw->name} berhasil ditambahkan.")->with('active_tab', 'struktur');
        }
    }

    /**
     * Ubah Nama Wilayah (RW atau RT)
     */
    public function updateRegion(Request $request, $id)
    {
        $admin = auth()->user();
        if ($admin->role !== 'admin_desa') {
            abort(403, 'Akses ditolak.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $region = Region::findOrFail($id);
        $validRegions = $this->getValidRegionIds($admin);
        if (!in_array($region->id, $validRegions)) {
            return back()->with('error', 'Akses ditolak: Wilayah ini berada di luar kewenangan desa Anda.')->with('active_tab', 'struktur');
        }

        $region->update([
            'name' => trim($request->name),
        ]);

        return back()->with('success', "Nama wilayah berhasil diperbarui menjadi {$region->name}.")->with('active_tab', 'struktur');
    }

    /**
     * Hapus Wilayah Kosong (Hanya jika belum ada admin, warga, atau sub-wilayah)
     */
    public function destroyRegion($id)
    {
        $admin = auth()->user();
        if ($admin->role !== 'admin_desa') {
            abort(403, 'Akses ditolak.');
        }

        $region = Region::findOrFail($id);
        $validRegions = $this->getValidRegionIds($admin);
        if (!in_array($region->id, $validRegions)) {
            return back()->with('error', 'Akses ditolak: Wilayah ini berada di luar kewenangan desa Anda.')->with('active_tab', 'struktur');
        }

        // Cek jika memiliki sub-wilayah (misal RW punya RT)
        if ($region->children()->count() > 0) {
            return back()->with('error', "Tidak dapat menghapus {$region->name} karena masih memiliki RT di dalamnya.")->with('active_tab', 'struktur');
        }

        // Cek jika memiliki pengguna terdaftar
        if ($region->users()->count() > 0) {
            return back()->with('error', "Tidak dapat menghapus {$region->name} karena masih ada pengguna atau pejabat terdaftar di wilayah ini.")->with('active_tab', 'struktur');
        }

        $name = $region->name;
        $region->delete();

        return back()->with('success', "Wilayah {$name} berhasil dihapus.")->with('active_tab', 'struktur');
    }
}
