<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DomicileTransfer;
use Illuminate\Http\Request;

class DomicileTransferApiController extends Controller
{
    public function index(Request $request)
    {
        $transfers = DomicileTransfer::where('user_id', $request->user()->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $transfers,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'nik' => 'required|string|max:16',
            'no_kk' => 'nullable|string|max:16',
            'desa_asal' => 'required|string',
            'desa_tujuan' => 'required|string',
            'alamat' => 'nullable|string',
            'status_pemohon' => 'nullable|string',
            'alasan' => 'required|string',
            'tipe' => 'required|in:keluar,masuk',
        ]);

        $transfer = DomicileTransfer::create([
            'user_id' => $request->user()->id,
            'nama' => $request->nama,
            'nik' => $request->nik,
            'no_kk' => $request->no_kk,
            'desa_asal' => $request->desa_asal,
            'desa_tujuan' => $request->desa_tujuan,
            'alamat' => $request->alamat,
            'status_pemohon' => $request->status_pemohon,
            'alasan' => $request->alasan,
            'tipe' => $request->tipe,
            'status' => 'pending',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Pengajuan mutasi domisili berhasil dikirim.',
            'data' => $transfer,
        ], 201);
    }

    public function cancel(Request $request, $id)
    {
        $transfer = DomicileTransfer::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->where('status', 'pending')
            ->firstOrFail();

        $transfer->delete();

        return response()->json([
            'success' => true,
            'message' => 'Pengajuan mutasi berhasil dibatalkan.',
        ]);
    }
}
