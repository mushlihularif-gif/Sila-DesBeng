<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{Mobil, RegionService, Service, Supir};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class UnitKendaraanOperasionalController extends Controller
{
    private function regionId(): int
    {
        abort_unless(auth()->user()?->region_id, 403);
        return (int) auth()->user()->region_id;
    }

    private function vehicles()
    {
        return Mobil::where('region_id', $this->regionId())->where('kategori', 'kendaraan_operasional');
    }

    private function drivers()
    {
        return Supir::where('region_id', $this->regionId())->where('tipe', 'supir')->orderBy('nama')->get();
    }

    public function index()
    {
        return redirect()->route('admin.unit.fasilitas_umum.index', ['tab' => 'kendaraan']);
    }

    public function create()
    {
        return view('admin.unit.kendaraan_operasional.form', ['kendaraan' => new Mobil(), 'supirs' => $this->drivers()]);
    }

    public function show($id)
    {
        return view('admin.unit.kendaraan_operasional.show', ['kendaraan' => $this->vehicles()->with(['supirs', 'region'])->findOrFail($id)]);
    }

    public function edit($id)
    {
        return view('admin.unit.kendaraan_operasional.form', ['kendaraan' => $this->vehicles()->with('supirs')->findOrFail($id), 'supirs' => $this->drivers()]);
    }

    private function validated(Request $request): array
    {
        $regionId = $this->regionId();
        return $request->validate([
            'nama_mobil' => 'required|string|max:255',
            'kategori' => 'nullable|in:kendaraan_operasional',
            'nomor_plat' => 'nullable|string|max:20',
            'deskripsi' => 'nullable|string|max:5000',
            'supir_ids' => 'nullable|array',
            'supir_ids.*' => ['integer', 'distinct', Rule::exists('supirs', 'id')->where('region_id', $regionId)->where('tipe', 'supir')],
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:8192',
            'foto_2' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:8192',
            'foto_3' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:8192',
            'delete_foto' => 'nullable|boolean',
            'delete_foto_2' => 'nullable|boolean',
            'delete_foto_3' => 'nullable|boolean',
        ]);
    }

    private function saveVehicle(Request $request, Mobil $kendaraan, array $validated): void
    {
        $kendaraan->fill([
            'nama_mobil' => $validated['nama_mobil'],
            'kategori' => 'kendaraan_operasional',
            'region_id' => $this->regionId(),
            'plat_nomor' => $validated['nomor_plat'] ?? null,
            'deskripsi' => $validated['deskripsi'] ?? null,
        ]);
        if (!$kendaraan->exists) {
            $kendaraan->fill(['harga_sewa' => 0, 'harga_dalam_desa' => 0, 'harga_luar_desa' => 0, 'harga_luar_kota' => 0, 'is_harian_active' => false, 'is_borongan_active' => false]);
        }
        $oldPhotos = [];
        foreach (['foto', 'foto_2', 'foto_3'] as $field) {
            if ($request->hasFile($field) || $request->boolean('delete_'.$field)) {
                if ($kendaraan->$field) $oldPhotos[] = $kendaraan->$field;
                $kendaraan->$field = $request->hasFile($field) ? $request->file($field)->store('unit_layanan/mobil', 'public') : null;
            }
        }
        $kendaraan->save();
        $kendaraan->supirs()->sync($validated['supir_ids'] ?? []);
        foreach ($oldPhotos as $photo) Storage::disk('public')->delete($photo);
    }

    public function store(Request $request)
    {
        $this->saveVehicle($request, new Mobil(), $this->validated($request));
        $service = Service::where('slug', 'fasilitas-umum')->first();
        if ($service) {
            RegionService::updateOrInsert(['region_id' => $this->regionId(), 'service_id' => $service->id], ['is_active' => true, 'updated_at' => now()]);
        }
        return $this->index()->with('success', 'Kendaraan operasional berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        $kendaraan = $this->vehicles()->findOrFail($id);
        $this->saveVehicle($request, $kendaraan, $this->validated($request));
        return $this->index()->with('success', 'Kendaraan operasional berhasil diubah.');
    }

    public function destroy($id)
    {
        $kendaraan = $this->vehicles()->findOrFail($id);
        $photos = array_filter([$kendaraan->foto, $kendaraan->foto_2, $kendaraan->foto_3]);
        $kendaraan->supirs()->detach();
        $kendaraan->delete();
        foreach ($photos as $photo) Storage::disk('public')->delete($photo);
        return $this->index()->with('success', 'Kendaraan operasional berhasil dihapus.');
    }
}
