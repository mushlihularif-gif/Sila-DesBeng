<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Region;
use App\Models\PartnerApplication;
use App\Models\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class PartnerApplicationApiController extends Controller
{
    /**
     * Get regions (Kecamatan and its Desa) for the dropdown.
     */
    public function getRegions()
    {
        $kecamatans = Region::where('type', 'kecamatan')
            ->with(['children' => function($query) {
                $query->where('type', 'desa');
            }])
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $kecamatans
        ]);
    }

    /**
     * Store the partnership application from mobile.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
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

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        $validated = $validator->validated();

        if ($request->hasFile('document')) {
            $path = $request->file('document')->store('partner_applications', 'public');
            $validated['document_path'] = $path;
        }

        // Handle user_id if token is provided
        if (auth('sanctum')->check()) {
            $validated['user_id'] = auth('sanctum')->id();
        } else {
            $validated['user_id'] = null;
        }

        $application = PartnerApplication::create($validated);

        if (auth('sanctum')->check()) {
            Notification::create([
                'user_id' => auth('sanctum')->id(),
                'type' => 'kemitraan',
                'title' => 'Pengajuan Terkirim',
                'message' => 'Pengajuan kemitraan Anda terkirim, silakan tunggu notifikasi selanjutnya.',
                'link' => null,
                'icon' => 'bx bx-paper-plane',
                'is_read' => false
            ]);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Pengajuan kemitraan berhasil dikirim. Silakan tunggu info selanjutnya.',
            'data' => $application
        ], 201);
    }
}
