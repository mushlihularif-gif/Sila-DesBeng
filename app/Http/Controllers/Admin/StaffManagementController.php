<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\StaffPermission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StaffManagementController extends Controller
{
    // Kumpulan unit layanan yang tersedia
    private $availableUnits = [
        'gas' => 'Penjualan Gas',
        'sewa_alat' => 'Penyewaan Alat',
        'sewa_mobil' => 'Penyewaan Mobil',
        'fasilitas_umum' => 'Fasilitas Umum',
        'pasar_daerah' => 'Pasar Daerah',
        'kabar_informasi' => 'Kabar dan Informasi Daerah',
        'pelaporan_warga' => 'Pelaporan Warga'
    ];

    public function index(Request $request)
    {
        $user = auth()->user();
        
        // Pastikan hanya Super Admin (dan yang setara) yang bisa akses
        if (!$user->isSuperAdmin()) {
            return redirect()->route('admin.dashboard')->with('error', 'Anda tidak memiliki akses ke halaman ini.');
        }

        $search = $request->get('search');

        // Ambil data staf yang berada di region yang sama dengan super admin
        // Atau jika super admin pusat (region_id null), ambil semua staf
        $query = User::where('role', 'staff')
                     ->with('staffPermissions');

        if ($user->region_id) {
            $query->where('region_id', $user->region_id);
        }

        $staffUsers = $query->when($search, function ($q) use ($search) {
                return $q->where(function($subQ) use ($search) {
                    $subQ->searchWhereLike(['name', 'email'], $search);
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->appends(['search' => $search]);

        if ($request->ajax()) {
            return view('admin.staff.partials.table', compact('staffUsers'))->render();
        }

        $availableUnits = $this->availableUnits;
        return view('admin.staff.index', compact('staffUsers', 'search', 'availableUnits'));
    }

    public function create()
    {
        if (!auth()->user()->isSuperAdmin()) {
            return redirect()->route('admin.dashboard')->with('error', 'Akses ditolak.');
        }

        $availableUnits = $this->availableUnits;
        return view('admin.staff.create', compact('availableUnits'));
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        
        if (!$user->isSuperAdmin()) {
            return redirect()->route('admin.dashboard')->with('error', 'Akses ditolak.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'units' => 'array',
        ]);

        $staff = new User();
        $staff->name = $request->name;
        $staff->email = $request->email;
        $staff->password = Hash::make($request->password);
        $staff->role = 'staff';
        $staff->region_id = $user->region_id;
        $staff->created_by = $user->id;
        $staff->status = 'aktif';
        // Mock default values for required fields or let them be null if nullable
        // Since nik and phone might be required, we can leave them null if allowed, 
        // or generate dummy for staff if required by DB. Let's assume nullable.
        $staff->save();

        if ($request->has('units')) {
            foreach ($request->units as $unitKey) {
                if (array_key_exists($unitKey, $this->availableUnits)) {
                    StaffPermission::create([
                        'user_id' => $staff->id,
                        'unit_key' => $unitKey
                    ]);
                }
            }
        }

        return redirect()->route('admin.staff.index')->with('success', 'Akun Staf berhasil dibuat.');
    }

    public function edit($id)
    {
        $user = auth()->user();
        if (!$user->isSuperAdmin()) {
            return redirect()->route('admin.dashboard')->with('error', 'Akses ditolak.');
        }

        $staff = User::where('role', 'staff')->with('staffPermissions')->findOrFail($id);
        
        // Cek region access
        if ($user->region_id && $staff->region_id !== $user->region_id) {
            return redirect()->route('admin.staff.index')->with('error', 'Anda tidak memiliki akses untuk mengedit staf ini.');
        }

        $availableUnits = $this->availableUnits;
        $activeUnits = $staff->staffPermissions->pluck('unit_key')->toArray();

        return view('admin.staff.edit', compact('staff', 'availableUnits', 'activeUnits'));
    }

    public function update(Request $request, $id)
    {
        $user = auth()->user();
        if (!$user->isSuperAdmin()) {
            return redirect()->route('admin.dashboard')->with('error', 'Akses ditolak.');
        }

        $staff = User::where('role', 'staff')->findOrFail($id);

        if ($user->region_id && $staff->region_id !== $user->region_id) {
            return redirect()->route('admin.staff.index')->with('error', 'Akses ditolak.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($staff->id)],
            'password' => 'nullable|string|min:8|confirmed',
            'units' => 'array',
        ]);

        $staff->name = $request->name;
        $staff->email = $request->email;
        if ($request->filled('password')) {
            $staff->password = Hash::make($request->password);
        }
        $staff->save();

        // Sync permissions
        StaffPermission::where('user_id', $staff->id)->delete();
        if ($request->has('units')) {
            foreach ($request->units as $unitKey) {
                if (array_key_exists($unitKey, $this->availableUnits)) {
                    StaffPermission::create([
                        'user_id' => $staff->id,
                        'unit_key' => $unitKey
                    ]);
                }
            }
        }

        return redirect()->route('admin.staff.index')->with('success', 'Akun Staf berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $user = auth()->user();
        if (!$user->isSuperAdmin()) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak.']);
        }

        $staff = User::where('role', 'staff')->findOrFail($id);
        
        if ($user->region_id && $staff->region_id !== $user->region_id) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak.']);
        }

        $staff->delete();

        return redirect()->route('admin.staff.index')->with('success', 'Akun Staf berhasil dihapus.');
    }

    public function toggleStatus($id)
    {
        $user = auth()->user();
        if (!$user->isSuperAdmin()) {
            return redirect()->route('admin.dashboard')->with('error', 'Akses ditolak.');
        }

        $staff = User::where('role', 'staff')->findOrFail($id);
        
        if ($user->region_id && $staff->region_id !== $user->region_id) {
            return redirect()->route('admin.staff.index')->with('error', 'Akses ditolak.');
        }

        $staff->status = $staff->status === 'aktif' ? 'non_aktif' : 'aktif';
        $staff->save();

        return redirect()->route('admin.staff.index')->with('success', 'Status akun staf berhasil diubah.');
    }
}
