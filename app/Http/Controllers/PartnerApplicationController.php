<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Notification;

class PartnerApplicationController extends Controller
{
    public function create()
    {
        // 1. Fetch data for "Direktori BUMDes"
        $kecamatans = \App\Models\Region::where('type', 'kecamatan')
            ->with(['children' => function($query) {
                $query->where('type', 'desa')
                      ->with(['services' => function($q) {
                          $q->wherePivot('is_active', true);
                      }, 'users']);
            }])
            ->get();

        // 2. Fetch regions for the dropdown in the form
        $regions = \App\Models\Region::all();

        return view('pages.kemitraan.create', compact('regions', 'kecamatans'));
    }

    /** Sebutan tingkat wilayah untuk pesan ke pemohon (ucfirst membuat "Rt"). */
    private const LABEL_TINGKAT = [
        'kecamatan' => 'Kecamatan',
        'desa'      => 'Desa',
        'kelurahan' => 'Kelurahan',
        'rw'        => 'RW',
        'rt'        => 'RT',
    ];

    /** Tipe wilayah yang sah berada di bawah tipe induk tertentu. */
    private const TURUNAN = [
        'kabupaten' => ['kecamatan'],
        'kecamatan' => ['desa', 'kelurahan'],
        'desa'      => ['rw'],
        'kelurahan' => ['rw'],
        'rw'        => ['rt'],
        'rt'        => [],
    ];

    public function store(Request $request)
    {
        $validated = $request->validate([
            'applicant_name' => 'required|string|max:255',
            'position' => 'required|string|max:255',
            // Belum memuat 'kelurahan': kolomnya masih enum('kecamatan','desa',
            // 'rw','rt') di database, jadi nilai itu akan gagal saat disimpan.
            // Formulir mengarahkan pemohon kelurahan memakai pilihan 'desa'.
            'region_type' => 'required|in:kecamatan,desa,rw,rt',
            'region_name' => 'required|string|max:255',
            'parent_region_id' => 'required|exists:regions,id',
            'contact_email' => 'required|email|max:255',
            'contact_phone' => 'required|string|max:20',
            'reason' => 'required|string',
            'document' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120', // 5MB max
        ]);

        // Formulirnya memang menuntun pemohon lewat dropdown berjenjang, tetapi
        // parent_region_id di atas hanya diperiksa keberadaannya. Tanpa
        // pemeriksaan ini, kiriman POST langsung bisa mengajukan "desa" yang
        // induknya sebuah RT, dan bentuk pohon wilayah jadi rusak begitu
        // pengajuannya disetujui.
        $induk = \App\Models\Region::find($validated['parent_region_id']);

        if (! in_array($validated['region_type'], self::TURUNAN[$induk->type] ?? [], true)) {
            return back()->withInput()->withErrors([
                'parent_region_id' => (self::LABEL_TINGKAT[$validated['region_type']] ?? $validated['region_type'])
                    . ' tidak dapat berada di bawah ' . $induk->name . '. Silakan pilih ulang wilayahnya.',
            ]);
        }

        if ($request->hasFile('document')) {
            $path = $request->file('document')->store('partner_applications', 'public');
            $validated['document_path'] = $path;
        }

        $validated['user_id'] = auth()->check() ? auth()->id() : null;

        $application = \App\Models\PartnerApplication::create($validated);

        if (auth()->check()) {
            Notification::create([
                'user_id' => auth()->id(),
                'type' => 'kemitraan',
                'title' => 'Pengajuan Terkirim',
                'message' => 'Pengajuan kemitraan Anda terkirim, silakan tunggu notifikasi selanjutnya.',
                'link' => null,
                'icon' => 'bx bx-paper-plane',
                'is_read' => false
            ]);
        }

        return redirect()->back()->with('success_modal', 'Pengajuan kemitraan Anda terkirim. Silakan tunggu notifikasinya, email dan sandi akun Anda akan dikirim setelah disetujui.');
    }
}
