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
        
        $users = User::query()
            ->when($search, function ($query, $search) {
                return $query->where('name', 'LIKE', "%{$search}%")
                           ->orWhere('email', 'LIKE', "%{$search}%");
            })
            ->orderBy('name', 'asc')
            ->paginate(10)
            ->appends(['search' => $search]);

        return view('admin.user_management.index', compact('users', 'search'));
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
        $user->status = $user->status === 'aktif' ? 'non_aktif' : 'aktif';
        $user->save();

        return redirect()->back()->with('success', 'Status akun pengguna berhasil diubah.');
    }
}