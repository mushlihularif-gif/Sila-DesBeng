<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Region;
use App\Models\RegionService;
use App\Models\User;
use App\Support\BengkalisScope;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/** Peta struktur Kecamatan Bengkalis sampai RT; dikelola Super Admin. */
class WilayahPlatformController extends Controller
{
    /** Tipe wilayah yang boleh dibuat di bawah tipe induk tertentu. */
    private const TURUNAN = [
        'kecamatan'  => ['desa', 'kelurahan'],
        'desa'       => ['rw'],
        'kelurahan'  => ['rw'],
        'rw'         => ['rt'],
        'rt'         => [],
    ];

    public function index()
    {
        $root = BengkalisScope::root();
        $ids = BengkalisScope::ids();
        $semua = Region::whereIn('id', $ids)->orderBy('name')->get();

        // Semua data pendamping diambil sekali sebagai peta, bukan lewat relasi
        // per simpul. Dengan desa ditambah RW/RT, pola N+1 di sini akan
        // berarti ribuan query untuk satu kali buka halaman.
        $jumlahWarga = User::whereIn('region_id', $ids)
            ->select('region_id', DB::raw('count(*) as n'))
            ->groupBy('region_id')->pluck('n', 'region_id');

        $pengurus = User::whereIn('role', ['admin_desa', 'admin_rw', 'admin_rt'])
            ->whereIn('region_id', $ids)
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'role', 'region_id'])
            ->groupBy('region_id');

        $layananAktif = RegionService::where('is_active', true)
            ->whereIn('region_id', $ids)
            ->select('region_id', DB::raw('count(*) as n'))
            ->groupBy('region_id')->pluck('n', 'region_id');

        $perInduk = $semua->groupBy(fn ($r) => $r->id === $root->id ? '' : $r->parent_id);

        $bangun = function ($indukId) use (&$bangun, $perInduk, $jumlahWarga, $pengurus, $layananAktif) {
            $kunci = $indukId === null ? '' : $indukId;

            return collect($perInduk[$kunci] ?? [])->map(fn ($r) => [
                'id'       => $r->id,
                'nama'     => $r->name,
                'tipe'     => $r->type,
                'warga'    => (int) ($jumlahWarga[$r->id] ?? 0),
                'pengurus' => $pengurus[$r->id] ?? collect(),
                'layanan'  => (int) ($layananAktif[$r->id] ?? 0),
                'anak'     => $bangun($r->id),
            ])->values();
        };

        $pohon = $bangun(null);

        // Simpul yatim: induknya menunjuk id yang sudah tidak ada. Tanpa
        // penanganan ini mereka hilang dari pohon tanpa jejak, padahal
        // wilayahnya tetap dipakai data lain.
        $idAda = $semua->pluck('id')->all();
        $yatim = $semua->filter(fn ($r) => $r->id !== $root->id && $r->parent_id && ! in_array($r->parent_id, $idAda))->values();

        $rekap = $semua->groupBy('type')->map->count();

        // Pilihan induk untuk formulir, diurutkan mengikuti pohon supaya
        // hierarkinya terbaca lewat indentasi.
        $pilihanInduk = collect();
        $ratakan = function ($simpul, int $level) use (&$ratakan, $pilihanInduk) {
            foreach ($simpul as $s) {
                if (self::TURUNAN[$s['tipe']] ?? []) {
                    $pilihanInduk->push([
                        'id'    => $s['id'],
                        'label' => str_repeat('  ', $level) . $s['nama'],
                        'tipe'  => $s['tipe'],
                    ]);
                }
                $ratakan($s['anak'], $level + 1);
            }
        };
        $ratakan($pohon, 0);

        return view('admin.super_sistem.wilayah', [
            'pohon'        => $pohon,
            'rekap'        => $rekap,
            'total'        => $semua->count(),
            'yatim'        => $yatim,
            'pilihanInduk' => $pilihanInduk,
            'turunan'      => self::TURUNAN,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'parent_id' => 'required|exists:regions,id',
            'type'      => ['required', Rule::in(['desa', 'kelurahan', 'rw', 'rt'])],
            'name'      => 'required|string|max:255',
        ], [
            'name.required' => 'Nama wilayah wajib diisi.',
            'type.required' => 'Tingkat wilayah wajib dipilih.',
        ]);

        $induk = Region::findOrFail($validated['parent_id']);
        BengkalisScope::assertContains($induk);

        if ($induk) {
            $boleh = self::TURUNAN[$induk->type] ?? [];

            if (! in_array($validated['type'], $boleh, true)) {
                return back()->withInput()->with('error', $boleh
                    ? 'Di bawah ' . $induk->name . ' hanya boleh ditambahkan '
                        . implode(' atau ', array_map('ucfirst', $boleh)) . '.'
                    : $induk->name . ' adalah tingkat terkecil, tidak bisa punya wilayah di bawahnya.');
            }
        }

        $nama = $this->rapikanNama($validated['name'], $validated['type']);

        // Duplikat wilayah adalah masalah yang sudah pernah terjadi di data ini
        // (satu pohon kabupaten kembar). Dicegah di sini, bukan sekadar
        // diandalkan pada kerapian pengetikan operator.
        $kembar = Region::where('type', $validated['type'])
            ->where('parent_id', $induk?->id)
            ->whereRaw('LOWER(name) = ?', [mb_strtolower($nama)])
            ->first();

        if ($kembar) {
            return back()->withInput()
                ->with('error', 'Wilayah "' . $nama . '" sudah terdaftar di sini, jadi tidak ditambahkan lagi.');
        }

        Region::create([
            'name'         => $nama,
            'type'         => $validated['type'],
            'parent_id'    => $induk?->id,
            'profile_text' => 'Pemerintah ' . $nama,
        ]);

        return back()->with('success', 'Wilayah "' . $nama . '" berhasil ditambahkan'
            . ($induk ? ' di bawah ' . $induk->name . '.' : '.'));
    }

    /**
     * Samakan gaya penulisan nama, dan bubuhkan awalan tingkat kalau operator
     * hanya mengetik nama tempatnya. Tanpa ini "Bantan" dan "Kecamatan Bantan"
     * akan hidup berdampingan sebagai dua wilayah berbeda.
     */
    private function rapikanNama(string $mentah, string $tipe): string
    {
        $nama = ucwords(mb_strtolower(trim(preg_replace('/\s+/', ' ', $mentah))));

        $awalan = [
            'kabupaten' => ['Kabupaten'],
            'kecamatan' => ['Kecamatan'],
            'desa'      => ['Desa', 'Kelurahan'],
            'kelurahan' => ['Kelurahan', 'Desa'],
            'rw'        => ['RW'],
            'rt'        => ['RT'],
        ];

        foreach ($awalan[$tipe] ?? [] as $kata) {
            if (str_starts_with(mb_strtolower($nama), mb_strtolower($kata) . ' ')) {
                // RW/RT ditulis huruf besar semua; ucwords() tadi menjadikannya "Rw 03".
                return in_array($kata, ['RW', 'RT'], true)
                    ? $kata . ' ' . trim(mb_substr($nama, mb_strlen($kata)))
                    : $nama;
            }
        }

        $utama = $awalan[$tipe][0] ?? null;

        return $utama ? $utama . ' ' . $nama : $nama;
    }
}
