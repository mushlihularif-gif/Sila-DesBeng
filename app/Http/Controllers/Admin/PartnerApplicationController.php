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
    private ?array $cakupanFokusCached = null;

    private function wilayahFokusIds(): array
    {
        if ($this->cakupanFokusCached !== null) {
            return $this->cakupanFokusCached;
        }

        $kecamatan = Region::where('type', 'kecamatan')->where('name', 'Kecamatan Bengkalis')->firstOrFail();

        $this->cakupanFokusCached = array_map('intval', array_merge(
            $kecamatan->parent_id ? [$kecamatan->parent_id] : [],
            [$kecamatan->id],
            Region::getDescendantIds($kecamatan->id)
        ));

        return $this->cakupanFokusCached;
    }

    private function pengajuanDalamCakupan(PartnerApplication $application): bool
    {
        if (!isset(User::REGION_ROLES[$application->region_type])) return false;
        if (! in_array((int) $application->parent_region_id, $this->wilayahFokusIds(), true)) {
            return false;
        }

        if ($application->region_type === 'kecamatan') {
            $kecamatan = Region::where('type', 'kecamatan')->where('name', 'Kecamatan Bengkalis')->first();
            $parent = $kecamatan?->parent_id;
            $nama = strtolower(trim(preg_replace('/^kecamatan\\s*/i', '', $application->region_name)));

            return $parent && (int) $application->parent_region_id === (int) $parent
                && $nama === strtolower(preg_replace('/^kecamatan\\s*/i', '', $kecamatan->name));
        }

        $induk = Region::find($application->parent_region_id);
        $indukTipe = match ($application->region_type) {
            'desa', 'kelurahan' => 'kecamatan',
            'rw' => ['desa', 'kelurahan'],
            'rt' => 'rw',
            default => null,
        };

        return $induk && ($indukTipe === $induk->type
            || (is_array($indukTipe) && in_array($induk->type, $indukTipe, true)));
    }

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
    private const PETA_ROLE = User::REGION_ROLES;

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
        $actor = auth()->user();
        abort_unless($actor && ($actor->isSuperAdmin() || ($actor->region_id && (User::REGION_ROLES[$actor->region?->type] ?? null) === $actor->role)), 403);
        abort_unless(
            in_array(auth()->user()?->role, ['super_admin', 'admin_desa', 'admin_rw'], true),
            403,
            'Peninjauan kemitraan hanya untuk admin wilayah dan Super Admin.'
        );
    }

    public function index()
    {
        $this->pastikanPeninjau();

        $user = auth()->user();

        // Semua peran hanya meninjau pengajuan untuk Kecamatan Bengkalis.
        if ($user->role === 'super_admin') {
            $applications = PartnerApplication::where('status', 'pending')->latest()->get()
                ->filter(fn ($application) => $this->pengajuanDalamCakupan($application))
                ->values();
        } else {
            $allowedRegionIds = array_merge([$user->region_id], Region::getDescendantIds($user->region_id));
            $applications = PartnerApplication::where('status', 'pending')
                ->whereIn('parent_region_id', $allowedRegionIds)
                ->latest()
                ->get()
                ->filter(fn ($application) => $this->pengajuanDalamCakupan($application))
                ->values();
        }

        return view('admin.partner-applications.index', compact('applications'));
    }

    public function document($id)
    {
        $this->pastikanPeninjau();

        $application = PartnerApplication::findOrFail($id);
        abort_unless($this->pengajuanDalamCakupan($application), 404);

        $user = auth()->user();
        if ($user->role !== 'super_admin') {
            $allowedRegionIds = array_merge([$user->region_id], Region::getDescendantIds($user->region_id));
            if (!in_array($application->parent_region_id, $allowedRegionIds)) {
                abort(403);
            }
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

        $path = app(\App\Services\PartnerDocumentService::class)->path($application->document_path);
        return response()->file($path, [
            'Cache-Control' => 'private, no-store, max-age=0',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    public function approve(Request $request, $id)
    {
        $this->pastikanPeninjau();

        $request->validate(['reason' => 'nullable|string|max:1000']);
        $approvalReason = trim((string) ($request->input('reason') ?? '')) ?: 'Disetujui oleh admin wilayah';
        $application = PartnerApplication::findOrFail($id);
        if ($application->status !== 'pending') {
            return back()->with('error', 'Pengajuan ini sudah diproses sebelumnya.');
        }
        abort_unless($this->pengajuanDalamCakupan($application), 404);

        // Security check
        $user = auth()->user();
        if ($user->role !== 'super_admin') {
            $allowedRegionIds = array_merge([$user->region_id], Region::getDescendantIds($user->region_id));
            if (!in_array($application->parent_region_id, $allowedRegionIds)) {
                return back()->with('error', 'Anda tidak memiliki akses untuk menyetujui aplikasi ini.');
            }
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
        if ($application->user_id && (! $existingUser || (int) $existingUser->id !== (int) $application->user_id)) {
            return back()->with('error', 'Email pengajuan tidak sesuai dengan akun pemohon. Minta pemohon mengajukan ulang dengan akunnya sendiri.');
        }
        if (! $application->user_id && $existingUser) {
            return back()->with('error', 'Pengajuan lama belum terhubung ke pemilik akun. Minta pemohon masuk dan mengajukan ulang.');
        }
        if ($existingUser && in_array($application->region_type, ['rw', 'rt'], true)) {
            $domicile = $existingUser->region;
            while ($domicile && ! in_array($domicile->type, ['desa', 'kelurahan'], true)) {
                $domicile = $domicile->parent;
            }
            $targetDesaId = $application->region_type === 'rt' ? $induk->parent_id : $induk->id;
            if (! $domicile || (int) $domicile->id !== (int) $targetDesaId) {
                return back()->with('error', 'Domisili pemohon berbeda dari desa pada pengajuan. Periksa domisili terlebih dahulu.');
            }
        }


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

        // If Desa/Kelurahan, auto-activate services (Penyewaan, Gas, Fasilitas Umum )
        //
        // syncWithoutDetaching, bukan attach: firstOrCreate di atas sering
        // MENEMUKAN wilayah yang sudah ada (formulir pemohon memilih desa dari
        // daftar, bukan mengetik nama baru). attach() akan menambah satu baris
        // pivot lagi untuk tiap layanan yang sudah terdaftar, sehingga
        // region_services berisi duplikat dan hitungan layanan jadi berlipat.
        // Layanan yang sudah sengaja dimatikan wilayah juga tidak dinyalakan
        // paksa - hanya yang belum ada yang ditambahkan.
        if (in_array($application->region_type, ['desa', 'kelurahan'], true)) {
            $existingIds = $region->services()->pluck('services.id');
            $baru = Service::available()->whereNotIn('id', $existingIds)->get(['id', 'slug']);

            $region->services()->syncWithoutDetaching(
                $baru->mapWithKeys(fn ($service) => [
                    $service->id => [
                        'is_active' => true,
                        'is_exclusive' => $service->slug !== 'pengumuman',
                    ],
                ])->all()
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
            // Akun baru memakai sandi awal acak, bukan ruang tebakan empat digit.
            $password = Str::password(20);

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
            \App\Services\NotificationService::notifyPartnerApplicationApproved($application, $region->name, $approvalReason);
        }

        // Send Email to Applicant
        try {
            Mail::to($application->contact_email)->send(new AccountApprovedMail($username, $password, $region->name));
            return back()->with('success', "Kemitraan disetujui! Wilayah dan Akun Admin berhasil dibuat. Username dan Password telah dikirimkan ke email: {$application->contact_email}");
        } catch (\Exception $e) {
            // Format nomor HP (ubah 0 jadi 62 jika perlu)
            $phone = $application->contact_phone;
            if (str_starts_with($phone, '0')) {
                $phone = '62' . substr($phone, 1);
            }
            
            // Siapkan teks WhatsApp
            $waText = "Halo " . $application->applicant_name . ", Pengajuan kemitraan " . $region->name . " telah disetujui.\n\nBerikut adalah informasi akun Admin Anda:\nUsername: " . $username . "\nPassword: " . $password . "\n\nHarap segera login dan ubah password Anda demi keamanan.";
            $waLink = "https://api.whatsapp.com/send?phone=" . $phone . "&text=" . urlencode($waText);

            return back()
                ->with('success', "Kemitraan disetujui, namun email gagal terkirim. Silakan kirim kredensial akun ({$username}) ke {$application->applicant_name} ({$application->contact_phone}) melalui WhatsApp.")
                ->with('partner_wa_fallback', [
                    'applicant_name' => $application->applicant_name,
                    'contact_phone' => $application->contact_phone,
                    'username' => $username,
                    'password' => $password,
                    'wa_link' => $waLink,
                ]);
        }
    }

    public function reject(Request $request, $id)
    {
        $this->pastikanPeninjau();

        if (!$request->filled('reason') && $request->filled('rejection_reason')) {
            $request->merge(['reason' => $request->input('rejection_reason')]);
        }
        $request->validate(['reason' => 'required|string|max:1000']);
        $application = PartnerApplication::findOrFail($id);
        if ($application->status !== 'pending') {
            return back()->with('error', 'Pengajuan ini sudah diproses sebelumnya.');
        }
        abort_unless($this->pengajuanDalamCakupan($application), 404);
        
        $user = auth()->user();
        if ($user->role !== 'super_admin') {
            $allowedRegionIds = array_merge([$user->region_id], Region::getDescendantIds($user->region_id));
            if (!in_array($application->parent_region_id, $allowedRegionIds)) {
                return back()->with('error', 'Anda tidak memiliki akses untuk menolak aplikasi ini.');
            }
        }

        $application->update(['status' => 'rejected']);

        if ($application->user_id) {
            \App\Services\NotificationService::notifyPartnerApplicationRejected($application, $request->reason);
        }

        return back()->with('success', 'Permohonan kemitraan ditolak.');
    }
}
