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

    public function store(Request $request)
    {
        $validated = $request->validate([
            'applicant_name' => 'required|string|max:255',
            'position' => 'required|string|max:255',
            'region_type' => 'required|in:kecamatan,desa,rw,rt',
            'region_name' => 'required|string|max:255',
            'parent_region_id' => 'required|exists:regions,id',
            'contact_email' => 'required|email|max:255',
            'contact_phone' => 'required|string|max:20',
            'reason' => 'required|string',
            'document' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120', // 5MB max
        ]);

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
