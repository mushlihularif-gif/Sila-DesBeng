<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AlamatWarga;
use Illuminate\Http\Request;

class ProfileAlamatApiController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $alamat = AlamatWarga::milik($user->id)
            ->with('region')
            ->orderByDesc('is_utama')
            ->orderBy('id')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $alamat
        ]);
    }

    public function store(Request $request)
    {
        $user = $request->user();

        if (AlamatWarga::milik($user->id)->count() >= 10) {
            return response()->json([
                'status' => 'error',
                'message' => 'Anda sudah menyimpan batas maksimal 10 alamat. Hapus salah satunya dulu.'
            ], 422);
        }

        $data = $request->validate([
            'label'         => 'nullable|string|max:50',
            'nama_penerima' => 'required|string|max:100',
            'no_telepon'    => 'required|string|max:20',
            'region_id'     => 'nullable|exists:regions,id',
            'detail_alamat' => 'required|string|max:255',
            'rt'            => 'nullable|string|max:10',
            'rw'            => 'nullable|string|max:10',
            'kode_pos'      => 'nullable|string|max:10',
            'patokan'       => 'nullable|string|max:255',
            'latitude'      => 'nullable|numeric',
            'longitude'     => 'nullable|numeric',
            'is_utama'      => 'boolean'
        ]);

        $data['user_id'] = $user->id;
        $isUtama = $request->boolean('is_utama');

        // Jika belum punya alamat sama sekali, otomatis jadikan utama
        if (AlamatWarga::milik($user->id)->count() === 0) {
            $isUtama = true;
        }

        $data['is_utama'] = $isUtama;

        $alamat = AlamatWarga::create($data);

        if ($isUtama) {
            $alamat->jadikanUtama();
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Alamat berhasil ditambahkan.',
            'data' => $alamat->load('region')
        ], 201);
    }

    public function show(Request $request, $id)
    {
        $user = $request->user();
        $alamat = AlamatWarga::milik($user->id)->with('region')->find($id);

        if (!$alamat) {
            return response()->json([
                'status' => 'error',
                'message' => 'Alamat tidak ditemukan.'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $alamat
        ]);
    }

    public function update(Request $request, $id)
    {
        $user = $request->user();
        $alamat = AlamatWarga::milik($user->id)->find($id);

        if (!$alamat) {
            return response()->json([
                'status' => 'error',
                'message' => 'Alamat tidak ditemukan.'
            ], 404);
        }

        $data = $request->validate([
            'label'         => 'nullable|string|max:50',
            'nama_penerima' => 'sometimes|required|string|max:100',
            'no_telepon'    => 'sometimes|required|string|max:20',
            'region_id'     => 'nullable|exists:regions,id',
            'detail_alamat' => 'sometimes|required|string|max:255',
            'rt'            => 'nullable|string|max:10',
            'rw'            => 'nullable|string|max:10',
            'kode_pos'      => 'nullable|string|max:10',
            'patokan'       => 'nullable|string|max:255',
            'latitude'      => 'nullable|numeric',
            'longitude'     => 'nullable|numeric',
            'is_utama'      => 'boolean'
        ]);

        $alamat->update($data);

        if ($request->boolean('is_utama')) {
            $alamat->jadikanUtama();
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Alamat berhasil diperbarui.',
            'data' => $alamat->load('region')
        ]);
    }

    public function utama(Request $request, $id)
    {
        $user = $request->user();
        $alamat = AlamatWarga::milik($user->id)->find($id);

        if (!$alamat) {
            return response()->json([
                'status' => 'error',
                'message' => 'Alamat tidak ditemukan.'
            ], 404);
        }

        $alamat->jadikanUtama();

        return response()->json([
            'status' => 'success',
            'message' => 'Alamat utama berhasil diperbarui.'
        ]);
    }

    public function destroy(Request $request, $id)
    {
        $user = $request->user();
        $alamat = AlamatWarga::milik($user->id)->find($id);

        if (!$alamat) {
            return response()->json([
                'status' => 'error',
                'message' => 'Alamat tidak ditemukan.'
            ], 404);
        }

        $adalahUtama = $alamat->is_utama;
        $userId = $alamat->user_id;
        $alamat->delete();

        // Kalau yang dihapus adalah alamat utama, alamat tersisa yang paling lama otomatis menggantikannya
        if ($adalahUtama) {
            $pengganti = AlamatWarga::milik($userId)->orderBy('id')->first();
            $pengganti?->jadikanUtama();
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Alamat berhasil dihapus.'
        ]);
    }
}
