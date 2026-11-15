<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FasilitasUmumBooking;
use App\Models\MobilBooking;
use App\Models\Region;
use App\Models\Supir;
use App\Models\User;
use App\Services\ImageCompressorService;
use App\Services\ServiceAssignment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class SupirController extends Controller
{
    private function manager()
    {
        $user = Auth::user();
        abort_unless($user && $user->isAdmin() && ($user->hasUnitPermission('sewa_mobil') || $user->hasUnitPermission('fasilitas_umum')), 403);
        abort_if($user->role !== 'super_admin' && ! $user->region_id, 403);

        return $user;
    }

    private function scopedSupirs()
    {
        $user = $this->manager();

        return Supir::query()->when($user->role !== 'super_admin', fn ($q) => $q->where('region_id', $user->region_id))
            ->when($user->isStaff(), fn ($q) => $q->where(function ($q) use ($user) {
                $q->whereRaw('1 = 0');
                if ($user->hasUnitPermission('sewa_mobil')) {
                    $q->orWhere('is_sewa_mobil', true);
                }
                if ($user->hasUnitPermission('fasilitas_umum')) {
                    $q->orWhere('is_fasilitas_umum', true);
                }
            }));
    }

    private function validateAssignedUnits(array $data): void
    {
        $user = $this->manager();
        foreach (['is_sewa_mobil' => 'sewa_mobil', 'is_fasilitas_umum' => 'fasilitas_umum'] as $field => $unit) {
            abort_if(! empty($data[$field]) && ! $user->hasUnitPermission($unit), 403, 'Pengelolaan petugas lintas layanan hanya dapat dilakukan oleh admin desa atau staf yang memiliki kedua izin.');
        }
    }

    public function index(Request $request)
    {
        $user = $this->manager();
        $tab = $request->get('tab', 'supir');
        if (! in_array($tab, ['supir', 'pengurus_gedung'])) {
            $tab = 'supir';
        }

        $query = $this->scopedSupirs()->with(['user', 'kendaraanOperasional', 'fasilitas']);
        if ($user && $user->role !== 'super_admin' && $user->region_id) {
            $query->where('region_id', $user->region_id);
            $users = User::whereIn('region_id', [$user->region_id, ...Region::getDescendantIds($user->region_id)])->get();
        } else {
            $users = User::all();
        }

        // Hitung total untuk badge tab
        $countQuery = clone $query;
        $countSupir = (clone $countQuery)->where('tipe', 'supir')->count();
        $countPengurus = (clone $countQuery)->where('tipe', 'pengurus_gedung')->count();

        // Ambil data sesuai tab aktif
        $supirs = $query->where('tipe', $tab)->get();

        // Jika request via AJAX untuk pergantian tab cepat
        if ($request->ajax() && $request->has('ajax_tab')) {
            $tableHtml = view('admin.supir.partials.table_rows', compact('supirs', 'tab'))->render();
            $cardsHtml = view('admin.supir.partials.mobile_cards', compact('supirs', 'tab'))->render();
            $modalsHtml = view('admin.supir.partials.modals', compact('supirs', 'users', 'tab'))->render();

            return response()->json([
                'success' => true,
                'tab' => $tab,
                'count_supir' => $countSupir,
                'count_pengurus' => $countPengurus,
                'table_html' => $tableHtml,
                'cards_html' => $cardsHtml,
                'modals_html' => $modalsHtml,
            ]);
        }

        return view('admin.supir.index', compact('supirs', 'users', 'tab', 'countSupir', 'countPengurus'));
    }

    public function store(Request $request)
    {
        $admin = $this->manager();
        $regionId = $admin->region_id ?: $request->input('region_id');
        validator(['region_id' => $regionId], ['region_id' => ['required', Rule::exists('regions', 'id')->whereIn('type', ['desa', 'kelurahan'])]])->validate();
        // Default tipe ke 'supir' jika tidak dikirim dari form
        if (! $request->filled('tipe')) {
            $request->merge(['tipe' => 'supir']);
        }

        $request->validate([
            'nama' => 'required|string|max:255',
            'tipe' => 'required|in:supir,pengurus_gedung',
            'kontak' => 'nullable|string|max:50',
            'status' => 'required|in:Tersedia,Sedang Bertugas,Tidak Aktif',
            'user_id' => ['nullable', Rule::exists('users', 'id')->whereIn('region_id', [$regionId, ...Region::getDescendantIds($regionId)])],
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:8192',
        ]);

        $user = Auth::user();
        $region_id = ($user && $user->region_id) ? $user->region_id : ($request->region_id ?? null);
        $tipe = $request->input('tipe', 'supir');

        $data = [
            'region_id' => $region_id,
            'tipe' => $tipe,
            'nama' => $request->nama,
            'kontak' => $request->kontak,
            'status' => $request->status,
            'user_id' => $request->user_id,
            'is_sewa_mobil' => ($tipe === 'supir' && $request->has('is_sewa_mobil')) ? 1 : 0,
            'is_fasilitas_umum' => ($tipe === 'pengurus_gedung' || $request->has('is_fasilitas_umum')) ? 1 : 0,
            'layanan' => null,
        ];

        $this->validateAssignedUnits($data);

        if ($request->hasFile('foto')) {
            $data['foto'] = ImageCompressorService::compressAndStore($request->file('foto'), 'supirs');
        }

        $newSupir = Supir::create($data);

        // Respons AJAX (misal dari modal penambahan langsung di form kendaraan operasional atau fasilitas umum)
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $tipe === 'pengurus_gedung' ? 'Pengurus gedung berhasil ditambahkan' : 'Supir berhasil ditambahkan',
                'supir' => $newSupir,
                'avatar_url' => $newSupir->foto ? asset('storage/'.$newSupir->foto) : asset('Admin/img/avatars/pria.png'),
            ]);
        }

        $pesan = $tipe === 'pengurus_gedung' ? 'Data pengurus gedung berhasil ditambahkan!' : 'Data supir berhasil ditambahkan!';

        return redirect()->route('supir.index', ['tab' => $tipe])->with('success', $pesan);
    }

    public function update(Request $request, Supir $supir)
    {
        return DB::transaction(function () use ($request, $supir) {
            $supir = $this->scopedSupirs()->lockForUpdate()->findOrFail($supir->id);
            $this->validateAssignedUnits($supir->toArray());
            abort_if(ServiceAssignment::hasOtherBooking($supir->id), 409, 'Petugas masih menangani pesanan. Selesaikan atau alihkan tugasnya sebelum mengubah data penugasan.');
            if (! $request->filled('tipe')) {
                $request->merge(['tipe' => $supir->tipe ?? 'supir']);
            }

            $request->validate([
                'nama' => 'required|string|max:255',
                'tipe' => 'required|in:supir,pengurus_gedung',
                'kontak' => 'nullable|string|max:50',
                'status' => 'required|in:Tersedia,Sedang Bertugas,Tidak Aktif',
                'user_id' => ['nullable', Rule::exists('users', 'id')->whereIn('region_id', [$supir->region_id, ...Region::getDescendantIds($supir->region_id)])],
                'foto' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:8192',
            ]);

            $tipe = $request->input('tipe', $supir->tipe ?? 'supir');

            $data = [
                'tipe' => $tipe,
                'nama' => $request->nama,
                'kontak' => $request->kontak,
                'status' => $request->status,
                'user_id' => $request->user_id,
                'is_sewa_mobil' => ($tipe === 'supir' && $request->has('is_sewa_mobil')) ? 1 : 0,
                'is_fasilitas_umum' => ($tipe === 'pengurus_gedung' || $request->has('is_fasilitas_umum')) ? 1 : 0,
            ];

            $this->validateAssignedUnits($data);

            if ($request->hasFile('foto')) {
                if ($supir->foto) {
                    $oldPhoto = $supir->foto;
                    DB::afterCommit(fn () => Storage::disk('public')->delete($oldPhoto));
                }
                $data['foto'] = ImageCompressorService::compressAndStore($request->file('foto'), 'supirs');
            } elseif ($request->input('delete_foto') == '1') {
                if ($supir->foto) {
                    $oldPhoto = $supir->foto;
                    DB::afterCommit(fn () => Storage::disk('public')->delete($oldPhoto));
                }
                $data['foto'] = null;
            }

            $supir->update($data);

            $pesan = $tipe === 'pengurus_gedung' ? 'Data pengurus gedung berhasil diperbarui!' : 'Data supir berhasil diperbarui!';

            return redirect()->route('supir.index', ['tab' => $tipe])->with('success', $pesan);
        });
    }

    public function destroy(Supir $supir)
    {
        return DB::transaction(function () use ($supir) {
            $supir = $this->scopedSupirs()->lockForUpdate()->findOrFail($supir->id);
            $this->validateAssignedUnits($supir->toArray());
            abort_if(ServiceAssignment::hasOtherBooking($supir->id), 409, 'Petugas masih menangani pesanan. Selesaikan atau alihkan tugasnya terlebih dahulu.');
            abort_if(MobilBooking::withTrashed()->where('assigned_supir_id', $supir->id)->exists() || FasilitasUmumBooking::withTrashed()->where('assigned_supir_id', $supir->id)->exists(), 409, 'Petugas memiliki riwayat pesanan. Gunakan status Tidak Aktif agar riwayat tetap utuh.');
            $tab = $supir->tipe ?? 'supir';
            if ($supir->foto) {
                $oldPhoto = $supir->foto;
                DB::afterCommit(fn () => Storage::disk('public')->delete($oldPhoto));
            }
            $supir->delete();

            $pesan = $tab === 'pengurus_gedung' ? 'Data pengurus gedung berhasil dihapus!' : 'Data supir berhasil dihapus!';

            return redirect()->route('supir.index', ['tab' => $tab])->with('success', $pesan);
        });
    }
}
