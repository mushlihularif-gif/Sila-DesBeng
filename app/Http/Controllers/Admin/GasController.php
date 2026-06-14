<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Gas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class GasController extends Controller
{
    // ===========================
    // INDEX
    // ===========================
    public function index(Request $request)
    {
        $search = $request->get('search');
        
        $gases = Gas::query()
            ->when($search, function ($query, $search) {
                return $query->where('jenis_gas', 'LIKE', "%{$search}%")
                           ->orWhere('kategori', 'LIKE', "%{$search}%");
            })
            ->orderBy('created_at', 'desc')
            ->paginate(9)
            ->appends(['search' => $search]);

        return view('admin.unit.penjualan_gas.index', compact('gases', 'search'));
    }


    // ===========================
    // CREATE
    // ===========================
    public function create()
    {
        return view('admin.unit.penjualan_gas.create');
    }

    // ===========================
    // STORE
    // ===========================
    public function store(Request $request)
    {
        $validated = $request->validate([
            'jenis_gas' => 'required|string|max:255',
            'deskripsi' => 'required|string',
            'harga_satuan' => 'required|string|min:0',
            'stok' => 'required|integer|min:0',
            'status' => 'required|in:tersedia,dipesan,rusak',
            'kategori' => 'required|string|max:255',
            'lokasi' => 'required|string|max:255',
            'satuan' => 'required|string|max:255',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:8192',
            'foto_2' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:8192',
            'foto_3' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:8192',
        ]);

        // Bersihkan harga dari karakter non-angka
        $hargaBersih = (int) preg_replace('/[^0-9]/', '', $request->harga_satuan);
        if ($hargaBersih <= 0) {
            return back()->withErrors(['harga_satuan' => 'Harga satuan harus angka valid dan lebih dari 0.'])->withInput();
        }

        $gas = new Gas();
        $gas->jenis_gas = $validated['jenis_gas'];
        $gas->deskripsi = $validated['deskripsi'];
        $gas->harga_satuan = $hargaBersih;
        $gas->stok = $validated['stok'];
        $gas->status = $validated['status'];
        $gas->kategori = $validated['kategori'];
        $gas->lokasi = $validated['lokasi'];
        $gas->satuan = $validated['satuan'];

        if ($request->hasFile('foto')) {
            $gas->foto = $request->file('foto')->store('gas', 'public');
        }
        if ($request->hasFile('foto_2')) {
            $gas->foto_2 = $request->file('foto_2')->store('gas', 'public');
        }
        if ($request->hasFile('foto_3')) {
            $gas->foto_3 = $request->file('foto_3')->store('gas', 'public');
        }

        $gas->save();

        return redirect()->route('admin.unit.penjualan_gas.index')->with('success', 'Gas berhasil ditambahkan.');
    }

    // ===========================
    // SHOW
    // ===========================
    public function show($id)
    {
        $gas = Gas::findOrFail($id);
        return view('admin.unit.penjualan_gas.show', compact('gas'));
    }

    // ===========================
    // EDIT
    // ===========================
    public function edit($id)
    {
        $gas = Gas::findOrFail($id);
        return view('admin.unit.penjualan_gas.edit', compact('gas'));
    }

    // ===========================
    // UPDATE
    // ===========================
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'jenis_gas' => 'required|string|max:255',
            'deskripsi' => 'required|string',
            'harga_satuan' => 'required|string|min:0',
            'stok' => 'required|integer|min:0',
            'status' => 'required|in:tersedia,dipesan,rusak',
            'kategori' => 'required|string|max:255',
            'lokasi' => 'required|string|max:255',
            'satuan' => 'required|string|max:255',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:8192',
            'foto_2' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:8192',
            'foto_3' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:8192',
        ]);

        // Bersihkan harga
        $hargaBersih = (int) preg_replace('/[^0-9]/', '', $request->harga_satuan);
        if ($hargaBersih <= 0) {
            return back()->withErrors(['harga_satuan' => 'Harga satuan harus angka valid dan lebih dari 0.'])->withInput();
        }

        // Cari data gas
        $gas = Gas::findOrFail($id);

        // Siapkan data untuk update
        $dataUpdate = [
            'jenis_gas' => $validated['jenis_gas'],
            'deskripsi' => $validated['deskripsi'],
            'harga_satuan' => $hargaBersih,
            'stok' => $validated['stok'],
            'status' => $validated['status'],
            'kategori' => $validated['kategori'],
            'lokasi' => $validated['lokasi'],
            'satuan' => $validated['satuan'],
        ];

        // Foto Utama
        if ($request->hasFile('foto')) {
            if ($gas->foto)
                Storage::disk('public')->delete($gas->foto);
            $dataUpdate['foto'] = $request->file('foto')->store('gas', 'public');
        } elseif ($request->input('delete_foto') == '1') {
            if ($gas->foto)
                Storage::disk('public')->delete($gas->foto);
            $dataUpdate['foto'] = null;
        }

        // Foto 2
        if ($request->hasFile('foto_2')) {
            if ($gas->foto_2)
                Storage::disk('public')->delete($gas->foto_2);
            $dataUpdate['foto_2'] = $request->file('foto_2')->store('gas', 'public');
        } elseif ($request->input('delete_foto_2') == '1') {
            if ($gas->foto_2)
                Storage::disk('public')->delete($gas->foto_2);
            $dataUpdate['foto_2'] = null;
        }

        // Foto 3
        if ($request->hasFile('foto_3')) {
            if ($gas->foto_3)
                Storage::disk('public')->delete($gas->foto_3);
            $dataUpdate['foto_3'] = $request->file('foto_3')->store('gas', 'public');
        } elseif ($request->input('delete_foto_3') == '1') {
            if ($gas->foto_3)
                Storage::disk('public')->delete($gas->foto_3);
            $dataUpdate['foto_3'] = null;
        }

        // Eksekusi Update Satu Kali
        $gas->update($dataUpdate);

        return redirect()->route('admin.unit.penjualan_gas.index')->with('success', 'Gas berhasil diubah.');
    }

    // ===========================
    // DESTROY
    // ===========================
    // ===========================
    // DESTROY
    // ===========================
    public function destroy($id)
    {
        try {
            $gas = Gas::findOrFail($id);

            if ($gas->foto)
                Storage::disk('public')->delete($gas->foto);
            if ($gas->foto_2)
                Storage::disk('public')->delete($gas->foto_2);
            if ($gas->foto_3)
                Storage::disk('public')->delete($gas->foto_3);

            $gas->delete();

            return redirect()->route('admin.unit.penjualan_gas.index')->with('success', 'Gas berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()->route('admin.unit.penjualan_gas.index')->with('error', 'Gagal menghapus gas: ' . $e->getMessage());
        }
    }
}
