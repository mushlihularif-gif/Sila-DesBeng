<?php

namespace App\Http\Controllers;

use App\Models\Laporan;
use Barryvdh\DomPDF\Facade\Pdf;

class AdminLaporanController extends Controller
{
    /**
     * Export semua laporan (REKAP)
     */
    public function exportAllPdf()
    {
        $laporans = Laporan::with('user')
            ->orderBy('created_at', 'desc')
            ->get();

        return Pdf::loadView('exports.laporan-admin-pdf', [
            'laporans' => $laporans,
            'date' => now()->format('d F Y H:i'),
        ])
        ->setPaper('A4', 'landscape')
        ->download(
            'Laporan_Semua_Data_' . now()->format('Y-m-d_H-i') . '.pdf'
        );
    }

    /**
     * Export DETAIL 1 laporan (ADMIN)
     */
    public function exportDetailPdf($id)
    {
        $laporan = Laporan::with(['user', 'rating'])->findOrFail($id);

        return Pdf::loadView('exports.laporan-admin-detail-pdf', [
            'laporan' => $laporan,
            'title'   => 'Detail Laporan #' . $laporan->id,
            'date'    => now()->format('d F Y H:i'),
        ])
        ->setPaper('A4', 'portrait')
        ->download(
            'Detail_Laporan_' . $laporan->id . '.pdf'
        );
    }
}
