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
    /** Tipe wilayah yang sah berada di bawah tipe induk tertentu. */
    private const TURUNAN = [
        'kabupaten' => ['kecamatan'],
        'kecamatan' => ['desa', 'kelurahan'],
        'desa'      => ['rw'],
        'kelurahan' => ['rw'],
        'rw'        => ['rt'],
        'rt'        => [],
    ];

    /** Sebutan tingkat wilayah untuk pesan ke pengguna (ucfirst membuat "Rt"). */
    private const LABEL_TINGKAT = [
        'kabupaten' => 'Kabupaten',
        'kecamatan' => 'Kecamatan',
        'desa'      => 'Desa',
        'kelurahan' => 'Kelurahan',
        'rw'        => 'RW',
        'rt'        => 'RT',
    ];

    /** Role yang diberikan untuk tiap tingkat wilayah. */
    private const PETA_ROLE = [
        'kabupaten' => 'super_admin',
        'kecamatan' => 'admin_kecamatan',
        'desa'      => 'admin_desa',
        'kelurahan' => 'admin_desa',
        'rw'        => 'admin_rw',
        'rt'        => 'admin_rt',
    ];

    /**
     * Menyetujui kemitraan berarti membuat wilayah baru DAN mencetak akun admin
     * untuknya - kewenangan pimpinan wilayah, bukan pekerjaan staf unit.
     *
     * Penjaga ini perlu karena grup rute memakai `role:admin`, dan pseudo-role
     * itu di CheckRole ikut meloloskan 'staff'. Modul admin lain menutupinya
     * dengan middleware staff.permission; modul kemitraan satu-satunya yang
     * tidak, sehingga staf unit mana pun bisa menerbitkan akun admin desa.
     */
    private function pastikanPeninjau(): void
    {
        abort_unless(
            in_array(auth()->user()?->role, ['super_admin', 'admin', 'admin_kecamatan', 'admin_desa'], true),
            403,
            'Peninjauan kemitraan hanya untuk admin wilayah dan Super Admin.'
        );
    }

    public function index()
    {
        $this->pastikanPeninjau();

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
        $this->pastikanPeninjau();

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
        $this->pastikanPeninjau();

        $request->validate(['reason' => 'required|string']);
        $application = PartnerApplication::findOrFail($id);

        // Security check
        $user = auth()->user();
        if ($user->role !== 'super_admin' && $application->parent_region_id !== $user->region_id) {
            return back()->with('error', 'Anda tidak memiliki akses untuk menyetujui aplikasi ini.');
        }

        // Role tiap tingkat wilayah. Sebelumnya ada fallback `?? 'admin'`, jadi
        // tingkat yang tak dikenali justru mendapat role Admin Pusat - kegagalan
        // yang mengarah ke atas, bukan ke bawah. Sekarang ditolak.
        $role = self::PETA_ROLE[$application->region_type] ?? null;

        if (! $role) {
            return back()->with('error', 'Tingkat wilayah "' . $application->region_type . '" tidak dikenali, pengajuan tidak dapat disetujui.');
        }

        // Hierarki diperiksa ULANG di sini, tidak cukup mengandalkan formulir
        // pemohon: parent_region_id hanya divalidasi `exists`, sehingga kiriman
        // POST langsung bisa menaruh desa di bawah RT.
        $induk = Region::find($application->parent_region_id);

        if (! $induk) {
            return back()->with('error', 'Wilayah induk pada pengajuan ini sudah tidak ada.');
        }

        if (! in_array($application->region_type, self::TURUNAN[$induk->type] ?? [], true)) {
            return back()->with('error', 'Pengajuan tidak sah: '
                . (self::LABEL_TINGKAT[$application->region_type] ?? $application->region_type)
                . ' tidak boleh berada di bawah ' . $induk->name . '.');
        }

        // Menyetujui akan MENIMPA role akun yang emailnya tercantum. Karena
        // contact_email diisi bebas oleh pemohon, tanpa penjagaan ini pengajuan
        // desa yang memakai email Super Admin akan menurunkannya jadi admin
        // desa dan memindahkan wilayahnya - Kominfo terkunci dari sistemnya
        // sendiri. Hanya akun warga biasa yang boleh diangkat.
        $existingUser = User::where('email', $application->contact_email)->first();

        if ($existingUser && $existingUser->role !== 'user') {
            $sudahSesuai = $existingUser->role === $role
                && (int) $existingUser->region_id === (int) $application->parent_region_id;

            if (! $sudahSesuai) {
                return back()->with('error',
                    'Email ' . $application->contact_email . ' sudah dipakai akun dengan hak akses '
                    . $existingUser->labelRole() . '. Menyetujui pengajuan ini akan mengubah hak akses akun tersebut, '
                    . 'jadi dihentikan. Minta pemohon memakai email lain, atau ubah akun itu lewat Manajemen Pengguna.');
            }
        }

        // Create or Find Region to prevent duplicates (dengan SANITASI KETAT agar tidak double)
        // 1. Hilangkan spasi berlebih di awal, akhir, dan tengah kata
        // 2. Format jadi Huruf Kapital di Awal Kata
        $cleanName = ucwords(strtolower(trim(preg_replace('/\s+/', ' ', $application->region_name))));

        $regionName = $application->region_type === 'desa' && !str_starts_with(strtolower($cleanName), 'desa') && !str_starts_with(strtolower($cleanName), 'kelurahan') 
            ? 'Desa ' . $cleanName 
            : $cleanName;

        if ($application->region_type === 'kecamatan' && !str_starts_with(strtolower($cleanName), 'kecamatan')) {
            $regionName = 'Kecamatan ' . $cleanName;
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
        //
        // syncWithoutDetaching, bukan attach: firstOrCreate di atas sering
        // MENEMUKAN wilayah yang sudah ada (formulir pemohon memilih desa dari
        // daftar, bukan mengetik nama baru). attach() akan menambah satu baris
        // pivot lagi untuk tiap layanan yang sudah terdaftar, sehingga
        // region_services berisi duplikat dan hitungan layanan jadi berlipat.
        // Layanan yang sudah sengaja dimatikan wilayah juga tidak dinyalakan
        // paksa - hanya yang belum ada yang ditambahkan.
        if (in_array($application->region_type, ['desa', 'kelurahan'], true)) {
            $baru = Service::whereNotIn('id', $region->services()->pluck('services.id'))->pluck('id');

            $region->services()->syncWithoutDetaching(
                $baru->mapWithKeys(fn ($id) => [$id => ['is_active' => true]])->all()
            );
        }

        // Generate Username and Password
        $baseUsername = strtolower(str_replace(' ', '', $application->applicant_name));
        $username = $baseUsername;
        $counter = 1;
        while (User::where('username', $username)->exists()) {
            $username = $baseUsername . $counter;
            $counter++;
        }
        
        // $role dan $existingUser sudah ditetapkan & diperiksa di awal method,
        // sebelum wilayah dibuat - supaya pengajuan yang ditolak tidak sempat
        // meninggalkan wilayah baru yang tidak terpakai.
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
        $this->pastikanPeninjau();

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
