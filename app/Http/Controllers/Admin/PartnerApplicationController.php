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
use Illuminate\Support\Facades\Mail;
use App\Mail\AccountApprovedMail;
use App\Models\Notification;

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

    public function document($id)
    {
        $application = PartnerApplication::findOrFail($id);
        
        $user = auth()->user();
        if ($user->role !== 'super_admin' && $application->parent_region_id !== $user->region_id) {
            abort(403);
        }

        if ($application->user_id && $application->status === 'pending') {
            $existingNotif = Notification::where('user_id', $application->user_id)
                ->where('title', 'Pengajuan Sedang Diproses')
                ->where('message', 'like', '%'. $application->region_name .'%')
                ->exists();

            if (!$existingNotif) {
                Notification::create([
                    'user_id' => $application->user_id,
                    'type' => 'kemitraan',
                    'title' => 'Pengajuan Sedang Diproses',
                    'message' => 'Pengajuan kemitraan untuk ' . $application->region_name . ' sedang diproses oleh tim kami.',
                    'icon' => 'bx bx-time',
                    'is_read' => false
                ]);
            }
        }

        $path = storage_path('app/public/' . $application->document_path);
        if (!file_exists($path)) {
            return back()->with('error', 'Dokumen tidak ditemukan.');
        }

        return response()->file($path);
    }

    public function approve(Request $request, $id)
    {
        $request->validate(['reason' => 'required|string']);
        $application = PartnerApplication::findOrFail($id);
        
        // Security check
        $user = auth()->user();
        if ($user->role !== 'super_admin' && $application->parent_region_id !== $user->region_id) {
            return back()->with('error', 'Anda tidak memiliki akses untuk menyetujui aplikasi ini.');
        }

        // Create or Find Region to prevent duplicates
        $regionName = $application->region_type === 'desa' && !str_starts_with(strtolower($application->region_name), 'desa') && !str_starts_with(strtolower($application->region_name), 'kelurahan') 
            ? 'Desa ' . $application->region_name 
            : $application->region_name;

        if ($application->region_type === 'kecamatan' && !str_starts_with(strtolower($application->region_name), 'kecamatan')) {
            $regionName = 'Kecamatan ' . $application->region_name;
        }

        $region = Region::firstOrCreate(
            ['name' => $regionName, 'type' => $application->region_type, 'parent_id' => $application->parent_region_id],
            [
                'profile_text' => 'Profil ' . $regionName,
                'contact_phone' => $application->contact_phone,
                'contact_email' => $application->contact_email,
            ]
        );

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
        
        // Determine Role
        $roleMap = [
            'kabupaten' => 'super_admin',
            'kecamatan' => 'admin_kecamatan',
            'desa' => 'admin_desa',
            'rw' => 'admin_rw',
            'rt' => 'admin_rt',
        ];
        $role = $roleMap[$application->region_type] ?? 'admin';

        // Check if user already exists
        $existingUser = User::where('email', $application->contact_email)->first();

        if ($existingUser) {
            // Upgrade existing user
            $existingUser->update([
                'role' => $role,
                'region_id' => $region->id,
                'position' => $application->position,
                'phone' => $application->contact_phone,
            ]);
            
            $username = $existingUser->username ?? $existingUser->email;
            $password = "(Sandi Anda Sebelumnya)"; 
            // We tell them to use their existing password
        } else {
            // Generate password yang mudah diketik (Tidak full acak)
            // Format: Silades + 4 angka acak (contoh: Silades1945)
            $password = 'Silades' . rand(1000, 9999);

            // Create Admin User for this region
            $newAdmin = User::create([
                'name' => $application->applicant_name,
                'username' => $username,
                'email' => $application->contact_email,
                'password' => Hash::make($password),
                'phone' => $application->contact_phone,
                'position' => $application->position,
                'role' => $role,
                'region_id' => $region->id,
            ]);
        }

        // Update Application Status
        $application->update(['status' => 'approved']);

        if ($application->user_id) {
            Notification::create([
                'user_id' => $application->user_id,
                'type' => 'kemitraan',
                'title' => 'Pengajuan Disetujui',
                'message' => 'Pengajuan kemitraan untuk ' . $region->name . ' telah disetujui. Alasan/Catatan: ' . $request->reason,
                'icon' => 'bx bx-check-circle text-success',
                'is_read' => false
            ]);
        }

        // Send Email to Applicant
        try {
            Mail::to($application->contact_email)->send(new AccountApprovedMail($username, $password, $region->name));
            return back()->with('success', "Kemitraan disetujui! Wilayah dan Akun Admin berhasil dibuat. Username dan Password telah dikirimkan ke email: <b>" . $application->contact_email . "</b>");
        } catch (\Exception $e) {
            // Format nomor HP (ubah 0 jadi 62 jika perlu)
            $phone = $application->contact_phone;
            if (str_starts_with($phone, '0')) {
                $phone = '62' . substr($phone, 1);
            }
            
            // Siapkan teks WhatsApp
            $waText = "Halo " . $application->applicant_name . ", Pengajuan kemitraan " . $region->name . " telah disetujui.\n\nBerikut adalah informasi akun Admin Anda:\nUsername: " . $username . "\nPassword: " . $password . "\n\nHarap segera login dan ubah password Anda demi keamanan.";
            $waLink = "https://api.whatsapp.com/send?phone=" . $phone . "&text=" . urlencode($waText);

            $fallbackMsg = "Email GAGAL terkirim karena masalah koneksi. <br>Silahkan kirim email dan sandi ini ke WhatsApp <b>{$application->applicant_name}</b> ({$application->contact_phone}):<br><br>";
            $fallbackMsg .= "Username: <b>{$username}</b><br>Password: <b>{$password}</b><br>";
            $fallbackMsg .= "<a href='{$waLink}' target='_blank' class='inline-block mt-3 px-4 py-2 bg-green-500 text-white text-xs font-bold rounded-full shadow hover:bg-green-600 transition-colors'>Kirim via WhatsApp</a>";

            return back()->with('success', $fallbackMsg);
        }
    }

    public function reject(Request $request, $id)
    {
        $request->validate(['reason' => 'required|string']);
        $application = PartnerApplication::findOrFail($id);
        
        $user = auth()->user();
        if ($user->role !== 'super_admin' && $application->parent_region_id !== $user->region_id) {
            return back()->with('error', 'Anda tidak memiliki akses untuk menolak aplikasi ini.');
        }

        $application->update(['status' => 'rejected']);

        if ($application->user_id) {
            Notification::create([
                'user_id' => $application->user_id,
                'type' => 'kemitraan',
                'title' => 'Pengajuan Ditolak',
                'message' => 'Mohon maaf, pengajuan kemitraan untuk ' . $application->region_name . ' ditolak. Alasan: ' . $request->reason,
                'icon' => 'bx bx-x-circle text-danger',
                'is_read' => false
            ]);
        }

        return back()->with('success', 'Permohonan kemitraan ditolak.');
    }
}
