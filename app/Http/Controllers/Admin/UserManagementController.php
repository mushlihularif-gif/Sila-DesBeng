<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserManagementController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');
        $filter_kecamatan_id = $request->get('filter_kecamatan_id');
        $filter_desa_id = $request->get('filter_desa_id');
        
        // Jika kecamatan tidak dipilih, pastikan desa juga dikosongkan
        if (empty($filter_kecamatan_id)) {
            $filter_desa_id = null;
        } else if ($filter_desa_id) {
            // Validasi apakah desa yang dipilih benar-benar berada di bawah kecamatan yang dipilih
            // (Mencegah bug ketika pindah kecamatan tapi ID desa sebelumnya masih terkirim)
            $desa = \App\Models\Region::find($filter_desa_id);
            if (!$desa || $desa->parent_id != $filter_kecamatan_id) {
                $filter_desa_id = null;
            }
        }
        
        // Tentukan filter wilayah yang paling spesifik yang dipilih
        $filter_region_id = $filter_desa_id ?: ($filter_kecamatan_id ?: null);
        
        $user = auth()->user();
        
        $usersQuery = User::with('region');

        // Jika admin memiliki region_id (bukan super_admin/admin pusat), filter berdasarkan wilayahnya
        if ($user->region_id && in_array($user->role, ['admin_kecamatan', 'admin_desa', 'admin_rw', 'admin_rt', 'lurah'])) {
            $allowedRegionIds = \App\Models\Region::getDescendantIds($user->region_id);
            $allowedRegionIds[] = $user->region_id;
            
            $usersQuery->whereIn('region_id', $allowedRegionIds);
            
            // Sembunyikan akun admin yang levelnya lebih tinggi atau sama dari list jika diperlukan,
            // Tapi untuk amannya kita hanya tampilkan role 'user' jika dia adalah RT/RW
            if (in_array($user->role, ['admin_rt', 'admin_rw'])) {
                $usersQuery->where('role', 'user');
            }
        }
        
        // Filter opsional berdasarkan dropdown (hanya berlaku jika super_admin yang punya akses semua, atau admin desa yang memfilter per RT, dll)
        if ($filter_region_id) {
            $filterAllowedIds = \App\Models\Region::getDescendantIds($filter_region_id);
            $filterAllowedIds[] = $filter_region_id;
            $usersQuery->whereIn('region_id', $filterAllowedIds);
        }
        
        $users = $usersQuery->when($search, function ($query, $search) {
                return $query->where(function($q) use ($search) {
                    $q->where('name', 'LIKE', "%{$search}%")
                      ->orWhere('email', 'LIKE', "%{$search}%");
                });
            })
            ->orderBy('name', 'asc')
            ->paginate(10)
            ->appends([
                'search' => $search, 
                'filter_kecamatan_id' => $filter_kecamatan_id,
                'filter_desa_id' => $filter_desa_id
            ]);

        // Siapkan opsi dropdown khusus Super Admin
        $kecamatanOptions = collect();
        $desaOptions = collect();
        
        if (in_array($user->role, ['super_admin', 'admin'])) {
            $kecamatanOptions = \App\Models\Region::where('type', 'kecamatan')->orderBy('name')->get();
            if ($filter_kecamatan_id) {
                $desaOptions = \App\Models\Region::where('type', 'desa')->where('parent_id', $filter_kecamatan_id)->orderBy('name')->get();
            }
        }

        return view('admin.user_management.index', compact('users', 'search', 'kecamatanOptions', 'desaOptions', 'filter_kecamatan_id', 'filter_desa_id'));
    }

    public function show($id)
    {
        $user = User::with([
            'rentalTransactions' => function ($query) {
                $query->withTrashed()->with('barang')->latest()->take(10);
            },
            'gasTransactions' => function ($query) {
                $query->withTrashed()->with('gas')->latest()->take(10);
            }
        ])->findOrFail($id);

        return view('admin.user_management.show', compact('user'));
    }

    public function toggleStatus(Request $request, $id)
    {
        $user = User::findOrFail($id);
        
        // Verifikasi hak akses (Opsional tapi baik untuk keamanan)
        $currentUser = auth()->user();
        if ($currentUser->region_id && in_array($currentUser->role, ['admin_rt', 'admin_rw'])) {
            $allowedRegionIds = \App\Models\Region::getDescendantIds($currentUser->region_id);
            $allowedRegionIds[] = $currentUser->region_id;
            if (!in_array($user->region_id, $allowedRegionIds)) {
                return redirect()->back()->with('error', 'Anda tidak memiliki akses ke pengguna ini.');
            }
        }

        $user->status = $user->status === 'aktif' ? 'non_aktif' : 'aktif';
        $user->save();

        return redirect()->back()->with('success', 'Status akun pengguna berhasil diubah.');
    }

    public function kick(Request $request, $id)
    {
        $user = User::findOrFail($id);
        
        // Verifikasi hak akses
        $currentUser = auth()->user();
        if ($currentUser->region_id && in_array($currentUser->role, ['admin_rt', 'admin_rw', 'admin_desa', 'admin_kecamatan', 'lurah'])) {
            $allowedRegionIds = \App\Models\Region::getDescendantIds($currentUser->region_id);
            $allowedRegionIds[] = $currentUser->region_id;
            if (!in_array($user->region_id, $allowedRegionIds)) {
                return redirect()->back()->with('error', 'Anda tidak memiliki akses ke pengguna ini.');
            }
        }

        // Keluarkan pengguna dengan mengosongkan region_id
        $user->region_id = null;
        $user->save();

        return redirect()->back()->with('success', 'Pengguna berhasil dikeluarkan dari wilayah Anda.');
    }
}