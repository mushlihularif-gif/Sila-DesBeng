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
    // Hak akses untuk staf UNIT LAYANAN, dibuat oleh admin kabupaten/kecamatan/desa.
    private $unitLayanan = [
        'gas' => 'Penjualan Gas',
        'sewa_alat' => 'Penyewaan Alat',
        'sewa_mobil' => 'Penyewaan Mobil',
        'fasilitas_umum' => 'Fasilitas Umum',
        'pasar_daerah' => 'Pasar Daerah',
        'kabar_informasi' => 'Kabar dan Informasi Daerah',
        'pelaporan_warga' => 'Pelaporan Warga'
    ];

    /**
     * Daftar hak akses yang ditawarkan, TERGANTUNG SIAPA YANG MEMBUAT AKUN.
     *
     * Super Admin Sistem membuat akun pendamping untuk dashboard Sistem Platform,
     * jadi yang relevan adalah modul platform — bukan unit layanan per wilayah
     * yang memang bukan wewenangnya. Admin wilayah tetap mendapat daftar unit
     * layanan seperti sebelumnya.
     */
    private function availableUnits(): array
    {
        $user = auth()->user();

        if ($user->role === 'super_admin') {
            return User::izinPlatform();
        }

        // PENGAMAN NAIK HAK AKSES: staf pemegang izin "Kelola Staf" hanya boleh
        // membagikan izin yang DIA SENDIRI punya. Tanpa batas ini, satu akun staf
        // bisa membuat akun baru dengan izin apa pun — termasuk Integrasi & API Key
        // yang memuat kredensial platform.
        if ($user->role === 'staff') {
            $miliknya = $user->staffPermissions()->pluck('unit_key')->all();

            return array_intersect_key(User::izinPlatform(), array_flip($miliknya));
        }

        return $this->unitLayananWilayah($user);
    }

    /**
     * Unit yang boleh dibagikan admin wilayah = LAYANAN YANG AKTIF di wilayahnya.
     *
     * Alasannya: menu staf nanti disaring dua kali — layanan aktif wilayah DAN
     * izin yang dicentang. Menawarkan unit yang layanannya belum aktif hanya
     * menghasilkan centang yang tidak berefek apa-apa dan membingungkan admin.
     *
     * "Kabar dan Informasi Daerah" selalu ikut karena menunya memang tidak
     * terikat layanan aktif wilayah.
     */
    private function unitLayananWilayah(User $user): array
    {
        // Kabupaten/pusat tidak terikat satu wilayah: tawarkan semuanya.
        if (in_array($user->role, ['admin'], true) || ! $user->region_id) {
            return $this->unitLayanan;
        }

        $region = \App\Models\Region::with('services')->find($user->region_id);

        if (! $region) {
            return $this->unitLayanan;
        }

        // Nama layanan di tabel services -> kunci izin staf.
        $peta = [
            'Penyewaan Alat'  => 'sewa_alat',
            'Penjualan Gas'   => 'gas',
            'Penyewaan Mobil' => 'sewa_mobil',
            'Fasilitas Umum'  => 'fasilitas_umum',
            'Pasar Daerah'    => 'pasar_daerah',
            'Pelaporan Warga' => 'pelaporan_warga',
        ];

        $aktif = [];

        foreach ($region->services->pluck('name') as $nama) {
            if (isset($peta[$nama], $this->unitLayanan[$peta[$nama]])) {
                $aktif[$peta[$nama]] = $this->unitLayanan[$peta[$nama]];
            }
        }

        $aktif['kabar_informasi'] = $this->unitLayanan['kabar_informasi'];

        return $aktif;
    }

    /**
     * Boleh membuka menu Kelola Staf?
     *
     * isSuperAdmin() mencakup semua role admin wilayah; staf hanya lolos kalau
     * izin platform_staf-nya dicentang.
     */
    private function bolehKelolaStaf(User $user): bool
    {
        return $user->isSuperAdmin() || $user->hasPlatformPermission('platform_staf');
    }

    /**
     * Gabungan semua kunci izin, dipakai hanya untuk menerjemahkan kunci
     * menjadi label saat menampilkan daftar staf.
     */
    private function semuaIzin(): array
    {
        return $this->unitLayanan + User::izinPlatform();
    }

    /**
     * Daftar izin saat MENGEDIT staf yang sudah ada.
     *
     * Sengaja mengikuti jenis izin yang sudah dimiliki staf, bukan role
     * pengeditnya. Tanpa ini, super admin (region_id NULL, jadi melihat SEMUA
     * staf termasuk staf unit bentukan admin desa) akan disuguhi daftar modul
     * platform ketika membuka staf unit — dan penyimpanannya akan menghapus
     * izin unit yang tidak muncul di form.
     */
    private function availableUnitsFor(User $staff): array
    {
        $dimiliki = $staff->staffPermissions()->pluck('unit_key')->all();

        if (array_intersect($dimiliki, array_keys(User::izinPlatform()))) {
            // Pengaman yang sama seperti di availableUnits(): kalau yang mengedit
            // adalah sesama staf, dia hanya boleh mengutak-atik izin yang dia punya.
            if (auth()->user()->role === 'staff') {
                $miliknya = auth()->user()->staffPermissions()->pluck('unit_key')->all();

                return array_intersect_key(User::izinPlatform(), array_flip($miliknya));
            }

            return User::izinPlatform();
        }

        if (array_intersect($dimiliki, array_keys($this->unitLayanan))) {
            return $this->unitLayanan;
        }

        // Staf tanpa izin sama sekali: ikuti kewenangan pengeditnya.
        return $this->availableUnits();
    }

    /**
     * Kelompokkan daftar izin mengikuti tab di sidebar, supaya kartu pilihannya
     * tidak tersaji sebagai satu tumpukan datar yang sulit dibaca.
     *
     * Untuk daftar unit layanan (admin wilayah) tidak ada pengelompokan, jadi
     * dikembalikan sebagai satu grup tanpa judul.
     */
    private function grupIzin(array $daftar): array
    {
        // Daftar unit layanan: satu grup polos.
        if (! array_intersect(array_keys($daftar), array_keys(User::izinPlatform()))) {
            return ['' => $daftar];
        }

        $hasil = [];

        foreach (User::IZIN_PLATFORM_GRUP as $namaGrup => $isiGrup) {
            $anggota = [];

            foreach ($isiGrup as $kunci => [$label, $ikon]) {
                if (array_key_exists($kunci, $daftar)) {
                    $anggota[$kunci] = ['label' => $label, 'ikon' => $ikon];
                }
            }

            if ($anggota) {
                $hasil[$namaGrup] = $anggota;
            }
        }

        return $hasil;
    }

    public function index(Request $request)
    {
        $user = auth()->user();

        // Pastikan hanya Super Admin (dan yang setara) yang bisa akses
        if (! $this->bolehKelolaStaf($user)) {
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

        // Daftar staf bisa memuat kedua jenis izin sekaligus, jadi pakai gabungan
        // supaya label badge-nya tetap terbaca.
        $availableUnits = $this->semuaIzin();
        return view('admin.staff.index', compact('staffUsers', 'search', 'availableUnits'));
    }

    public function create()
    {
        if (! $this->bolehKelolaStaf(auth()->user())) {
            return redirect()->route('admin.dashboard')->with('error', 'Akses ditolak.');
        }

        $availableUnits = $this->availableUnits();
        $grupIzin = $this->grupIzin($availableUnits);

        return view('admin.staff.create', compact('availableUnits', 'grupIzin'));
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        
        if (! $this->bolehKelolaStaf($user)) {
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
                if (array_key_exists($unitKey, $this->availableUnits())) {
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
        if (! $this->bolehKelolaStaf($user)) {
            return redirect()->route('admin.dashboard')->with('error', 'Akses ditolak.');
        }

        $staff = User::where('role', 'staff')->with('staffPermissions')->findOrFail($id);
        
        // Cek region access
        if ($user->region_id && $staff->region_id !== $user->region_id) {
            return redirect()->route('admin.staff.index')->with('error', 'Anda tidak memiliki akses untuk mengedit staf ini.');
        }

        $availableUnits = $this->availableUnitsFor($staff);
        $grupIzin = $this->grupIzin($availableUnits);
        $activeUnits = $staff->staffPermissions->pluck('unit_key')->toArray();

        return view('admin.staff.edit', compact('staff', 'availableUnits', 'grupIzin', 'activeUnits'));
    }

    public function update(Request $request, $id)
    {
        $user = auth()->user();
        if (! $this->bolehKelolaStaf($user)) {
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

        // Sync permissions.
        // Hanya izin yang MEMANG ditawarkan di form ini yang dihapus — kalau
        // menghapus semuanya, jenis izin lain yang tidak tampil di form akan
        // ikut hilang tanpa disadari.
        $ditawarkan = array_keys($this->availableUnitsFor($staff));
        StaffPermission::where('user_id', $staff->id)->whereIn('unit_key', $ditawarkan)->delete();

        if ($request->has('units')) {
            foreach ($request->units as $unitKey) {
                if (in_array($unitKey, $ditawarkan, true)) {
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
        if (! $this->bolehKelolaStaf($user)) {
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
        if (! $this->bolehKelolaStaf($user)) {
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
