<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Region;
use App\Models\PartnerApplication;
use App\Models\AdminNotification;
use App\Models\Notification;
use App\Models\User;
use App\Services\NotificationService;
use App\Support\VillageParticipation;
use Illuminate\Support\Facades\DB;
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
            'parent_rw_name' => ['nullable', 'regex:/^RW\s*[0-9]{1,3}$/i'],
            'contact_email' => 'required|email|max:255',
            'contact_phone' => 'required|string|max:20',
            'reason' => 'required|string|max:10000',
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
        $parentRwNameInput = trim((string) ($validated['parent_rw_name'] ?? ''));
        unset($validated['parent_rw_name']);

        $kecamatanFokus = Region::where('type', 'kecamatan')->where('name', 'Kecamatan Bengkalis')->firstOrFail();
        $parent = Region::findOrFail($validated['parent_region_id']);
        $allowedParentIds = array_map('intval', array_merge(
            [$kecamatanFokus->parent_id, $kecamatanFokus->id],
            Region::getDescendantIds($kecamatanFokus->id)
        ));

        // Selaras dengan formulir web: pengajuan RT dari mobile dapat mengirim desa induk + nama RW (field parent_rw_name atau format "RT 01 / RW 02")
        $newParentRwName = null;
        $parentTypes = match ($validated['region_type']) {
            'kecamatan' => ['kabupaten'],
            'desa' => ['kecamatan'],
            'rw' => ['desa', 'kelurahan'],
            'rt' => ['rw', 'desa', 'kelurahan'],
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

        $domicile = $actor->region;
        while ($domicile && ! in_array($domicile->type, ['desa', 'kelurahan'], true)) {
            $domicile = $domicile->parent;
        }

        if (in_array($validated['region_type'], ['rw', 'rt'], true)) {
            $targetDesaId = ($validated['region_type'] === 'rt' && $parent->type === 'rw')
                ? $parent->parent_id
                : $parent->id;
            abort_unless($domicile && (int) $domicile->id === (int) $targetDesaId, 403);

            if ($validated['region_type'] === 'rw') {
                $cleanRwNum = preg_replace('/[^0-9]/', '', $validated['region_name']);
                if ($cleanRwNum !== '') {
                    $validated['region_name'] = 'RW ' . str_pad($cleanRwNum, 2, '0', STR_PAD_LEFT);
                }

                $targetRw = Region::where('parent_id', $domicile->id)
                    ->where('type', 'rw')
                    ->where(function ($q) use ($validated, $cleanRwNum) {
                        $q->where('name', $validated['region_name']);
                        if ($cleanRwNum !== '') {
                            $q->orWhere('name', 'RW ' . (int) $cleanRwNum)
                                ->orWhere('name', 'RW ' . str_pad($cleanRwNum, 2, '0', STR_PAD_LEFT));
                        }
                    })
                    ->first();

                if ($targetRw && User::where('region_id', $targetRw->id)->where('role', 'admin_rw')->where('status', 'aktif')->exists()) {
                    return response()->json([
                        'status' => 'error',
                        'message' => "{$targetRw->name} di {$domicile->name} sudah memiliki Admin aktif. Silakan tentukan nomor RW lain.",
                    ], 422);
                }
            } elseif ($validated['region_type'] === 'rt') {
                if (in_array($parent->type, ['desa', 'kelurahan'], true)) {
                    $rawRegion = trim($validated['region_name']);
                    $resolvedRw = $parentRwNameInput;
                    $resolvedRt = $rawRegion;

                    if ($resolvedRw === '' && preg_match('/RW\s*([0-9]{1,3})/i', $rawRegion, $rwMatch)) {
                        $resolvedRw = 'RW ' . str_pad($rwMatch[1], 2, '0', STR_PAD_LEFT);
                    }
                    if (preg_match('/RT\s*([0-9]{1,3})/i', $rawRegion, $rtMatch)) {
                        $resolvedRt = 'RT ' . str_pad($rtMatch[1], 2, '0', STR_PAD_LEFT);
                    }

                    $cleanRwNum = preg_replace('/[^0-9]/', '', $resolvedRw);
                    if ($cleanRwNum === '') {
                        return response()->json([
                            'status' => 'error',
                            'message' => 'Nomor RW induk wajib diisi untuk pengajuan pengurus RT.',
                        ], 422);
                    }

                    $formattedRw = 'RW ' . str_pad($cleanRwNum, 2, '0', STR_PAD_LEFT);
                    $existingRw = Region::where('parent_id', $domicile->id)
                        ->where('type', 'rw')
                        ->where(function ($q) use ($formattedRw, $cleanRwNum) {
                            $q->where('name', $formattedRw)
                                ->orWhere('name', 'RW ' . (int) $cleanRwNum);
                        })
                        ->first();

                    if ($existingRw) {
                        $parent = $existingRw;
                        $validated['parent_region_id'] = $existingRw->id;
                    } else {
                        $newParentRwName = $formattedRw;
                    }
                    $validated['region_name'] = $resolvedRt;
                } else {
                    $cleanRtNum = preg_replace('/[^0-9]/', '', $validated['region_name']);
                    if ($cleanRtNum !== '') {
                        $validated['region_name'] = 'RT ' . str_pad($cleanRtNum, 2, '0', STR_PAD_LEFT);
                    }
                }

                if ($parent->type === 'rw') {
                    $cleanRtNum = preg_replace('/[^0-9]/', '', $validated['region_name']);
                    $targetRt = Region::where('parent_id', $parent->id)
                        ->where('type', 'rt')
                        ->where(function ($q) use ($validated, $cleanRtNum) {
                            $q->where('name', $validated['region_name']);
                            if ($cleanRtNum !== '') {
                                $q->orWhere('name', 'RT ' . (int) $cleanRtNum)
                                    ->orWhere('name', 'RT ' . str_pad($cleanRtNum, 2, '0', STR_PAD_LEFT));
                            }
                        })
                        ->first();

                    if ($targetRt && User::where('region_id', $targetRt->id)->where('role', 'admin_rt')->where('status', 'aktif')->exists()) {
                        return response()->json([
                            'status' => 'error',
                            'message' => "{$targetRt->name} di lingkungan {$parent->name} sudah memiliki Admin aktif. Silakan tentukan nomor RT lain.",
                        ], 422);
                    }
                }
            }
        } elseif ($validated['region_type'] === 'desa') {
            $targetDesa = Region::where('parent_id', $kecamatanFokus->id)
                ->whereIn('type', ['desa', 'kelurahan'])
                ->whereRaw('LOWER(name) = ?', [mb_strtolower(trim($validated['region_name']))])
                ->first();
            if ($targetDesa && User::where('region_id', $targetDesa->id)->where('role', 'admin_desa')->where('status', 'aktif')->exists()) {
                return response()->json([
                    'status' => 'error',
                    'message' => "{$targetDesa->name} sudah memiliki Admin Desa aktif. Silakan ajukan sebagai Pengurus RW atau RT.",
                ], 422);
            }
        }

        if ($request->hasFile('document')) {
            $path = app(\App\Services\PartnerDocumentService::class)->store($request->file('document'));
            $validated['document_path'] = $path;
        }

        $validated['user_id'] = $actor->id;

        $application = DB::transaction(function () use ($validated, $newParentRwName, $domicile) {
            if ($newParentRwName && $domicile) {
                $parentRw = Region::firstOrCreate(
                    ['name' => $newParentRwName, 'type' => 'rw', 'parent_id' => $domicile->id],
                    ['profile_text' => 'Lingkungan ' . $newParentRwName]
                );
                $validated['parent_region_id'] = $parentRw->id;
            }
            return PartnerApplication::create($validated);
        });

        try {
            NotificationService::notifyPartnerApplicationSubmitted($application);
        } catch (\Throwable $e) {
            if (auth('sanctum')->check()) {
                try {
                    Notification::create([
                        'user_id' => auth('sanctum')->id(),
                        'type' => 'kemitraan',
                        'title' => 'Pengajuan Terkirim',
                        'message' => 'Pengajuan kemitraan Anda terkirim, silakan tunggu notifikasi selanjutnya.',
                        'link' => null,
                        'icon' => 'bx bx-paper-plane',
                        'is_read' => false,
                    ]);
                } catch (\Throwable $inner) {
                }
            }
        }

        try {
            $induk = Region::find($application->parent_region_id);
            $namaTingkat = match ($application->region_type) {
                'desa' => 'Desa',
                'rw' => 'RW',
                'rt' => 'RT',
                default => ucfirst($application->region_type),
            };

            if ($application->region_type === 'desa') {
                AdminNotification::create([
                    'type' => 'kemitraan',
                    'reference_id' => $application->id,
                    'region_id' => null,
                    'title' => "Pengajuan Kemitraan {$namaTingkat} Baru",
                    'message' => "Pemohon {$application->applicant_name} mengajukan pendaftaran Admin {$namaTingkat} {$application->region_name} ({$application->position}).",
                    'is_read' => false,
                ]);
            } elseif ($application->parent_region_id) {
                AdminNotification::create([
                    'type' => 'kemitraan',
                    'reference_id' => $application->id,
                    'region_id' => $application->parent_region_id,
                    'title' => "Pengajuan Kemitraan {$namaTingkat} Baru",
                    'message' => "Pemohon {$application->applicant_name} mengajukan pendaftaran {$namaTingkat} {$application->region_name} ({$application->position}).",
                    'is_read' => false,
                ]);

                if ($induk && $induk->type === 'rw' && $induk->parent_id) {
                    AdminNotification::create([
                        'type' => 'kemitraan',
                        'reference_id' => $application->id,
                        'region_id' => $induk->parent_id,
                        'title' => 'Pengajuan Pengurus RT Baru',
                        'message' => "Warga {$application->applicant_name} mengajukan pendaftaran pengurus {$application->region_name} ({$application->position}) di lingkungan {$induk->name}.",
                        'is_read' => false,
                    ]);
                }
            }
        } catch (\Throwable $e) {
            // Abaikan bila tabel admin_notifications tidak tersedia pada konteks uji terbatas
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Pengajuan kemitraan berhasil dikirim. Silakan tunggu info selanjutnya.',
            'data' => $application
        ], 201);
    }
}
