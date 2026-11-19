<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Region;
use App\Models\PartnerApplication;
use App\Models\Notification;
use App\Support\VillageParticipation;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class PartnerApplicationApiController extends Controller
{
    /**
     * Get regions (Kecamatan and its Desa) with admin status.
     */
    public function getRegions()
    {
        $kecamatanFokus = Region::where('type', 'kecamatan')->where('name', 'Kecamatan Bengkalis')->firstOrFail();
        $kecamatans = Region::whereKey($kecamatanFokus->id)
            ->with(['children' => function($query) {
                VillageParticipation::query($query)->whereIn('type', ['desa', 'kelurahan'])->with(['users' => function($u) {
                    $u->whereIn('role', ['admin_desa'])->where('status', 'aktif');
                }]);
            }])
            ->get();

        $data = $kecamatans->map(function($kecamatan) {
            return [
                'id' => $kecamatan->id,
                'name' => $kecamatan->name,
                'type' => $kecamatan->type,
                'children' => $kecamatan->children->sortBy([
                    fn ($a, $b) => (int) VillageParticipation::joined($b) <=> (int) VillageParticipation::joined($a),
                    fn ($a, $b) => strnatcasecmp($a->name, $b->name),
                ])->values()->map(function($desa) {
                    $hasAdmin = $desa->users->isNotEmpty();
                    return [
                        'id' => $desa->id,
                        'name' => $desa->name,
                        'type' => $desa->type,
                        'parent_id' => $desa->parent_id,
                        'has_admin' => $hasAdmin,
                        'is_joined' => VillageParticipation::joined($desa),
                        'admin_name' => $hasAdmin ? $desa->users->first()->name : null,
                    ];
                })
            ];
        });

        return response()->json([
            'status' => 'success',
            'data' => $data
        ]);
    }

    /**
     * Check if a specific desa has an active admin.
     */
    public function checkDesaAdmin($id)
    {
        $kecamatanFokusId = Region::where('type', 'kecamatan')->where('name', 'Kecamatan Bengkalis')->value('id');
        $desa = Region::whereIn('type', ['desa', 'kelurahan'])
            ->where('parent_id', $kecamatanFokusId)
            ->with(['users' => function($u) {
            $u->whereIn('role', ['admin_desa'])->where('status', 'aktif');
        }])->find($id);

        if (!$desa) {
            return response()->json([
                'status' => 'error',
                'message' => 'Desa / Kelurahan tidak ditemukan'
            ], 404);
        }

        $hasAdmin = $desa->users->isNotEmpty();

        return response()->json([
            'status' => 'success',
            'data' => [
                'desa_id' => $desa->id,
                'desa_name' => $desa->name,
                'has_admin' => $hasAdmin,
                'admin_name' => $hasAdmin ? $desa->users->first()->name : null,
            ]
        ]);
    }

    /**
     * Store the partnership application from mobile.
     */
    public function store(Request $request)
    {
        $actor = $request->user();
        abort_unless($actor && $actor->status === 'aktif', 403);
        $request->merge(['applicant_name' => $actor->name, 'contact_email' => $actor->email]);
        if (PartnerApplication::where('user_id', $actor->id)->where('status', 'pending')->exists()) {
            return response()->json(['message' => 'Pengajuan sebelumnya masih ditinjau.'], 422);
        }
        $validator = Validator::make($request->all(), [
            'applicant_name' => 'required|string|max:255',
            'position' => 'required|string|max:255',
            'region_type' => 'required|in:desa,rw,rt',
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

        $kecamatanFokus = Region::where('type', 'kecamatan')->where('name', 'Kecamatan Bengkalis')->firstOrFail();
        $parent = Region::findOrFail($validated['parent_region_id']);
        $allowedParentIds = array_map('intval', array_merge(
            [$kecamatanFokus->parent_id, $kecamatanFokus->id],
            Region::getDescendantIds($kecamatanFokus->id)
        ));
        $parentTypes = match ($validated['region_type']) {
            'kecamatan' => ['kabupaten'],
            'desa' => ['kecamatan'],
            'rw' => ['desa', 'kelurahan'],
            'rt' => ['rw'],
            default => [],
        };
        $parentIsInScope = in_array((int) $parent->id, $allowedParentIds, true)
            && in_array($parent->type, $parentTypes, true);
        $kecamatanIsInScope = $validated['region_type'] !== 'kecamatan'
            || ((int) $parent->id === (int) $kecamatanFokus->parent_id
                && mb_strtolower(trim($validated['region_name'])) === mb_strtolower($kecamatanFokus->name));

        if (! $parentIsInScope || ! $kecamatanIsInScope) {
            return response()->json([
                'status' => 'error',
                'message' => 'Pengajuan kemitraan hanya tersedia untuk Kecamatan Bengkalis dan desa/kelurahan di dalamnya.',
            ], 422);
        }

        if (in_array($validated['region_type'], ['rw', 'rt'], true)) {
            $domicile = $actor->region;
            while ($domicile && ! in_array($domicile->type, ['desa', 'kelurahan'], true)) {
                $domicile = $domicile->parent;
            }
            $targetDesaId = $validated['region_type'] === 'rt' ? $parent->parent_id : $parent->id;
            abort_unless($domicile && (int) $domicile->id === (int) $targetDesaId, 403);
        }

        if ($request->hasFile('document')) {
            $path = app(\App\Services\PartnerDocumentService::class)->store($request->file('document'));
            $validated['document_path'] = $path;
        }

        $validated['user_id'] = $actor->id;

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
