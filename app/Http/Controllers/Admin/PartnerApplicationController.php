<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PartnerApplication;
use App\Models\Region;
use App\Models\User;
use App\Models\Service;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class PartnerApplicationController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        // Filter applications based on the admin's region
        if ($user->role === 'super_admin') {
            // Super Admin sees ALL pending applications, especially Kabupaten/Kecamatan
            $applications = PartnerApplication::where('status', 'pending')->latest()->get();
        } else {
            // Region Admin only sees applications that have their region as parent
            $applications = PartnerApplication::where('status', 'pending')
                ->where('parent_region_id', $user->region_id)
                ->latest()
                ->get();
        }

        return view('admin.partner-applications.index', compact('applications'));
    }

    public function approve(Request $request, $id)
    {
        $application = PartnerApplication::findOrFail($id);
        
        // Security check
        $user = auth()->user();
        if ($user->role !== 'super_admin' && $application->parent_region_id !== $user->region_id) {
            return back()->with('error', 'Anda tidak memiliki akses untuk menyetujui aplikasi ini.');
        }

        // Create Region
        $region = Region::create([
            'name' => $application->region_name,
            'type' => $application->region_type,
            'parent_id' => $application->parent_region_id,
            'profile_text' => 'Profil ' . $application->region_name,
            'contact_phone' => $application->contact_phone,
            'contact_email' => $application->contact_email,
        ]);

        // If Desa/Kelurahan, auto-activate services (Penyewaan, Gas, Pelaporan)
        if ($application->region_type === 'desa') {
            $services = Service::all();
            foreach ($services as $service) {
                $region->services()->attach($service->id, ['is_active' => true]);
            }
        }

        // Generate Username and Password
        $baseUsername = strtolower(str_replace(' ', '', $application->applicant_name));
        $username = $baseUsername;
        $counter = 1;
        while (User::where('username', $username)->exists()) {
            $username = $baseUsername . $counter;
            $counter++;
        }

        $password = Str::random(8); // or you could use a default like 'password123'

        // Determine Role
        $roleMap = [
            'kabupaten' => 'super_admin', // if someone applies for Kabupaten
            'kecamatan' => 'admin_kecamatan',
            'desa' => 'admin_desa',
            'rw' => 'admin_rw',
            'rt' => 'admin_rt',
        ];
        $role = $roleMap[$application->region_type] ?? 'admin';

        // Create Admin User for this region
        $newAdmin = User::create([
            'name' => $application->applicant_name,
            'username' => $username,
            'email' => $application->contact_email,
            'password' => Hash::make($password),
            'phone' => $application->contact_phone,
            'role' => $role,
            'region_id' => $region->id,
        ]);

        // Update Application Status
        $application->update(['status' => 'approved']);

        // In a real application, you would send an email to $application->contact_email with their username and password.
        // For now, we'll return it in the success message.
        return back()->with('success', "Kemitraan disetujui! Wilayah dan Akun Admin berhasil dibuat. Username: $username | Password: $password");
    }

    public function reject(Request $request, $id)
    {
        $application = PartnerApplication::findOrFail($id);
        
        $user = auth()->user();
        if ($user->role !== 'super_admin' && $application->parent_region_id !== $user->region_id) {
            return back()->with('error', 'Anda tidak memiliki akses untuk menolak aplikasi ini.');
        }

        $application->update(['status' => 'rejected']);

        return back()->with('success', 'Permohonan kemitraan ditolak.');
    }
}
