<?php

namespace App\Http\Controllers;

use App\Models\Laporan;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Str;

class LaporanController extends Controller
{
    public function index()
    {
        $laporans = Laporan::where('user_id', auth()->id())
            ->latest()
            ->paginate(10);

        return view('user.laporan.index', compact('laporans'));
    }

    public function create()
    {
        return view('user.laporan.create');
    }

    public function store(Request $request)
    {
        // Validasi
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'deskripsi' => 'required|string|min:20',
            'kategori' => 'required|string',
            'lokasi' => 'nullable|string|max:255',
            'bukti' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $user = auth()->user();

        // Prepare data TANPA bukti dulu
        $data = [
            'user_id' => $user->id,
            'nama' => $validated['nama'],
            'deskripsi' => $validated['deskripsi'],
            'kategori' => $validated['kategori'],
            'lokasi' => $validated['lokasi'] ?? null,
            'status' => 'Pending',
            'rw' => $user->rw,
            'rt' => $user->rt,
            'rw_number' => $user->rw,
            'rt_number' => $user->rt,
        ];

        // Upload bukti SETELAH validasi
        if ($request->hasFile('bukti')) {
            $file = $request->file('bukti');
        
            if ($file->isValid()) {
                $extension = $file->getClientOriginalExtension();
                $filename = time() . '_' . Str::random(16) . '.' . $extension;
        
                // ⬇️ PATH KE ROOT SUBDOMAIN (INI KUNCI)
                $destination = $_SERVER['DOCUMENT_ROOT'] . '/storage/laporan';
                if (!is_dir($destination)) {
                    mkdir($destination, 0755, true);
                }
        
$file->move($destination, $filename);
        
                // ⬇️ SIMPAN RELATIVE URL
$data['bukti'] = 'laporan/' . $filename;
            }
        }






        // Simpan laporan
        $laporan = Laporan::create($data);

        Log::info('✅ Laporan created', [
            'id' => $laporan->id,
            'has_bukti' => isset($data['bukti']),
            'bukti_value' => $data['bukti'] ?? 'null',
        ]);

        // Kirim notifikasi
        try {
            $admins = User::where('role', 'admin')->where('rw', $user->rw)->get();

            foreach ($admins as $admin) {
                Notification::create([
                    'user_id' => $admin->id,
                    'laporan_id' => $laporan->id,
                    'type' => 'laporan_baru',
                    'title' => '📋 Laporan Baru Masuk',
                    'message' => "Laporan baru dari {$user->name} (RW {$user->rw}/RT {$user->rt}): {$laporan->nama}",
                    'link' => '/admin/laporan/' . $laporan->id,
                    'icon' => '📋',
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Notif error: ' . $e->getMessage());
        }

        return redirect()->route('user.laporan.show', $laporan->id)
            ->with('success', '✅ Laporan berhasil dibuat!');
    }

       public function exportPdf($id)
{
    // Pastikan user login
    if (!auth()->check()) {
        return redirect()->route('login');
    }

    $laporan = Laporan::with('user')->findOrFail($id);

    // Cast ke int untuk menghindari null / string mismatch
    if ((int) $laporan->user_id !== (int) auth()->user()->id) {
        abort(403);
    }

    return Pdf::loadView('exports.laporan-user-pdf', [
        'title' => 'Bukti Laporan #' . $laporan->id,
        'laporan' => $laporan,
        'date' => now()->format('d F Y H:i'),
    ])->download('Bukti_Laporan_'.$laporan->id.'.pdf');
}

public function show($id)
{
    $userId = auth()->id();

    if (!$userId) {
        abort(403);
    }

    $laporan = Laporan::with(['user', 'rating'])
        ->where('id', $id)
        ->where('user_id', $userId)
        ->firstOrFail();

    return view('user.laporan.show', compact('laporan'));
}


    public function destroy(Laporan $laporan)
{
    // Pastikan login
    if (!auth()->check()) {
        abort(403, 'Unauthorized');
    }

    // Pastikan pemilik
    if ((int) $laporan->user_id !== (int) auth()->id()) {
        abort(403, 'Anda tidak berhak menghapus laporan ini.');
    }

    // Status harus Pending (WAJIB KONSISTEN)
    if ($laporan->status !== 'Pending') {
        return back()->with('error', '❌ Laporan yang sudah diproses tidak dapat dihapus.');
    }

    // Batas waktu 24 jam
    if ($laporan->created_at->diffInHours(now()) >= 24) {
        return back()->with('error', '❌ Laporan sudah melewati batas waktu penghapusan (24 jam).');
    }

    // Hapus file bukti
    if ($laporan->bukti) {
        $filePath = public_path('storage/' . $laporan->bukti);
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }

    // Hapus notifikasi terkait
    Notification::where('laporan_id', $laporan->id)->delete();

    // Hapus laporan
    $laporan->delete();

    return redirect()
        ->route('user.laporan.index')
        ->with('success', '✅ Laporan berhasil dihapus.');
}


}
