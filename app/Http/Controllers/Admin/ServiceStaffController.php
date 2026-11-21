<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\StaffPermission;
use App\Models\User;
use App\Support\StaffAccess;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ServiceStaffController extends Controller
{
    private function manager(): User
    {
        $user = auth()->user();
        abort_unless($user?->role === 'admin_desa' && $user->status === 'aktif'
            && in_array($user->region?->type, ['desa', 'kelurahan'], true), 403);

        return $user;
    }

    public function index()
    {
        $manager = $this->manager();
        $staff = User::where('role', 'staff')->where('region_id', $manager->region_id)->with('staffPermissions')->get()->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE)->values();

        return view('admin.staff.index', ['staff' => $staff, 'units' => StaffAccess::UNITS]);
    }

    public function store(Request $request)
    {
        $manager = $this->manager();
        $data = $request->validate([
            'name' => 'required|string|max:255', 'email' => 'required|email|max:255|unique:users,email',
            'username' => 'required|alpha_dash|max:50|unique:users,username',
            'password' => 'required|string|min:12|confirmed',
            'units' => 'required|array|min:1', 'units.*' => ['required', 'distinct', Rule::in(array_keys(StaffAccess::UNITS))],
        ], [
            'name.required' => 'Nama lengkap petugas wajib diisi.',
            'email.required' => 'Alamat email wajib diisi.',
            'email.email' => 'Format alamat email tidak valid.',
            'email.unique' => 'Alamat email ini sudah digunakan akun lain.',
            'username.required' => 'Username login wajib diisi.',
            'username.alpha_dash' => 'Username hanya boleh berisi huruf, angka, garis bawah (_), atau tanda hubung (-) tanpa spasi.',
            'username.unique' => 'Username ini sudah digunakan akun lain.',
            'password.required' => 'Kata sandi wajib diisi.',
            'password.min' => 'Kata sandi minimal harus 12 karakter demi keamanan akun.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak cocok.',
            'units.required' => 'Pilih minimal satu unit layanan yang dikelola petugas.',
            'units.min' => 'Pilih minimal satu unit layanan yang dikelola petugas.',
        ]);
        DB::transaction(function () use ($manager, $data, $request) {
            $staff = new User(['name' => $data['name'], 'email' => $data['email'], 'username' => $data['username'],
                'password' => Hash::make($data['password']), 'role' => 'staff', 'region_id' => $manager->region_id]);
            $staff->created_by = $manager->id;
            $staff->staff_enabled = true;
            $staff->status = 'aktif';
            $staff->save();
            foreach ($data['units'] as $unit) {
                StaffPermission::create(['user_id' => $staff->id, 'unit_key' => $unit]);
            }
            $this->audit($request, $staff, 'Membuat akun staf layanan');
        });

        return back()->with('success', 'Akun staf dibuat. Sampaikan username dan kata sandi langsung kepada petugas.');
    }

    public function update(Request $request, int $id)
    {
        $manager = $this->manager();
        $data = $request->validate(['name' => 'required|string|max:255', 'enabled' => 'required|boolean',
            'units' => ['nullable', 'array', Rule::requiredIf($request->boolean('enabled')), 'min:1'], 'units.*' => ['required', 'distinct', Rule::in(array_keys(StaffAccess::UNITS))],
            'password' => 'nullable|string|min:12|confirmed'], [
            'name.required' => 'Nama petugas wajib diisi.',
            'units.required' => 'Staf yang aktif wajib memiliki minimal satu izin unit layanan.',
            'units.min' => 'Staf yang aktif wajib memiliki minimal satu izin unit layanan.',
            'password.min' => 'Kata sandi baru minimal harus 12 karakter.',
            'password.confirmed' => 'Konfirmasi kata sandi baru tidak cocok.',
        ]);
        $isEnabled = (bool) $data['enabled'];
        $passwordChanged = ! empty($data['password']);
        $shouldRevokeSessions = ! $isEnabled || $passwordChanged;

        DB::transaction(function () use ($manager, $data, $id, $request, $isEnabled, $passwordChanged, $shouldRevokeSessions) {
            $staff = User::where('role', 'staff')->where('region_id', $manager->region_id)->lockForUpdate()->findOrFail($id);
            $staff->name = $data['name'];
            $staff->staff_enabled = $isEnabled;
            $staff->status = $isEnabled ? 'aktif' : 'non_aktif';
            $staff->created_by = $manager->id;
            if ($passwordChanged) {
                $staff->password = Hash::make($data['password']);
            }
            if ($shouldRevokeSessions) {
                $staff->setRememberToken(Str::random(60));
            }
            $staff->save();
            $staff->staffPermissions()->delete();
            foreach (($data['units'] ?? []) as $unit) {
                $staff->staffPermissions()->create(['unit_key' => $unit]);
            }
            if ($shouldRevokeSessions) {
                $sessionTable = config('session.table', 'sessions');
                if (Schema::hasTable($sessionTable)) {
                    DB::table($sessionTable)->where('user_id', $staff->id)->delete();
                }
                if (Schema::hasTable('personal_access_tokens')) {
                    $staff->tokens()->delete();
                }
            }
            $this->audit($request, $staff, 'Mengubah izin/status staf layanan');
        });

        if (! $isEnabled) {
            return back()->with('success', 'Akun staf berhasil dinonaktifkan dan sesi loginnya telah diakhiri.');
        }

        if ($passwordChanged) {
            return back()->with('success', 'Data dan kata sandi staf diperbarui. Petugas perlu masuk kembali menggunakan kata sandi baru.');
        }

        return back()->with('success', 'Izin dan data staf diperbarui. Perubahan langsung berlaku tanpa perlu login ulang.');
    }

    private function audit(Request $request, User $staff, string $action): void
    {
        ActivityLog::create(['user_id' => auth()->id(), 'action' => 'service_staff', 'description' => $action.' #'.$staff->id, 'ip_address' => $request->ip()]);
    }
}
