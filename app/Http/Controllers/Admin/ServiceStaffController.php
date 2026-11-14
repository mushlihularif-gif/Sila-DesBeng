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
        $staff = User::where('role', 'staff')->where('region_id', $manager->region_id)->with('staffPermissions')->orderBy('name')->get();

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
            'password' => 'nullable|string|min:12|confirmed']);
        DB::transaction(function () use ($manager, $data, $id, $request) {
            $staff = User::where('role', 'staff')->where('region_id', $manager->region_id)->lockForUpdate()->findOrFail($id);
            $staff->name = $data['name'];
            $staff->staff_enabled = (bool) $data['enabled'];
            $staff->status = $data['enabled'] ? 'aktif' : 'non_aktif';
            $staff->created_by = $manager->id;
            if (! empty($data['password'])) {
                $staff->password = Hash::make($data['password']);
            }
            $staff->setRememberToken(Str::random(60));
            $staff->save();
            $staff->staffPermissions()->delete();
            foreach (($data['units'] ?? []) as $unit) {
                $staff->staffPermissions()->create(['unit_key' => $unit]);
            }
            if (Schema::hasTable('sessions')) {
                DB::table('sessions')->where('user_id', $staff->id)->delete();
            }
            if (Schema::hasTable('personal_access_tokens')) {
                $staff->tokens()->delete();
            }
            $this->audit($request, $staff, 'Mengubah izin/status staf layanan');
        });

        return back()->with('success', 'Izin staf diperbarui. Petugas perlu masuk kembali.');
    }

    private function audit(Request $request, User $staff, string $action): void
    {
        ActivityLog::create(['user_id' => auth()->id(), 'action' => 'service_staff', 'description' => $action.' #'.$staff->id, 'ip_address' => $request->ip()]);
    }
}
