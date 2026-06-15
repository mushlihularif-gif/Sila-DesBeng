<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PartnerApplicationController extends Controller
{
    public function create()
    {
        $regions = \App\Models\Region::all();
        return view('pages.kemitraan.create', compact('regions'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'applicant_name' => 'required|string|max:255',
            'region_type' => 'required|in:kecamatan,desa,rw,rt',
            'region_name' => 'required|string|max:255',
            'parent_region_id' => 'required|exists:regions,id',
            'contact_email' => 'required|email|max:255',
            'contact_phone' => 'required|string|max:20',
            'reason' => 'required|string',
        ]);

        $validated['user_id'] = auth()->id();

        \App\Models\PartnerApplication::create($validated);

        return redirect()->back()->with('success', 'Permohonan kemitraan berhasil dikirim. Kami akan meninjau pendaftaran Anda segera.');
    }
}
