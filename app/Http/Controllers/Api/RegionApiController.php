<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Region;
use App\Models\BumdesMember;

class RegionApiController extends Controller
{
    /**
     * Get all regions formatted as a hierarchy for dropdowns
     */
    public function getHierarchy()
    {
        $kecamatanFokus = Region::where('type', 'kecamatan')
            ->where('name', 'Kecamatan Bengkalis')
            ->first();
        $kabupaten = $kecamatanFokus?->parent;
        
        if (!$kabupaten) {
            return response()->json(['status' => 'error', 'message' => 'Kabupaten tidak ditemukan'], 404);
        }

        $kecamatans = collect([$kecamatanFokus])->map(function ($kec) {
            $desas = Region::whereIn('type', ['desa', 'kelurahan'])->where('parent_id', $kec->id)->orderBy('name')->get()->map(function ($desa) {
                return [
                    'id' => $desa->id,
                    'name' => $desa->name,
                ];
            });

            return [
                'id' => $kec->id,
                'name' => $kec->name,
                'desas' => $desas
            ];
        });

        return response()->json([
            'status' => 'success',
            'data' => [
                'kabupaten' => [
                    'id' => $kabupaten->id,
                    'name' => $kabupaten->name,
                ],
                'kecamatans' => $kecamatans
            ]
        ]);
    }

    /**
     * Get profile/structure for a specific region
     */
    public function getProfile($regionId)
    {
        $kecamatanFokus = Region::where('type', 'kecamatan')->where('name', 'Kecamatan Bengkalis')->firstOrFail();
        $wilayahFokus = array_map('intval', array_merge(
            [$kecamatanFokus->id],
            Region::getDescendantIds($kecamatanFokus->id)
        ));
        abort_unless(in_array((int) $regionId, $wilayahFokus, true), 404);

        $region = Region::with(['services' => function($q) {
            $q->where('is_active', true);
        }])->find($regionId);

        if (!$region) {
            return response()->json(['status' => 'error', 'message' => 'Wilayah tidak ditemukan'], 404);
        }
        
        $members = BumdesMember::where('region_id', $regionId)
            ->orderBy('level', 'asc')
            ->orderBy('order', 'asc')
            ->get();
            
        // Jangan membuat aparatur fiktif atau menghidupkan kembali struktur kecamatan.
        if ($region->type === 'kecamatan') {
            $members = collect();
        }
        $formattedMembers = [];
        if ($members->isNotEmpty()) {
            // Pisahkan Wakil Bupati / Wakil Kades agar TIDAK sejajar di Level 1
            $hasWakilInLevel1 = false;
            foreach ($members as $m) {
                $pos = strtolower($m->position);
                if ((str_contains($pos, 'wakil') || str_contains($pos, 'sekretaris')) && $m->level == 1) {
                    $hasWakilInLevel1 = true;
                    break;
                }
            }

            if ($hasWakilInLevel1) {
                // Geser level 2 dst naik 1 tingkat untuk menempatkan wakil di level 2
                foreach ($members as $m) {
                    $pos = strtolower($m->position);
                    if ($m->level >= 2) {
                        $m->level = $m->level + 1;
                    } else if ($m->level == 1 && (str_contains($pos, 'wakil') || str_contains($pos, 'sekretaris'))) {
                        $m->level = 2;
                    }
                }
            }

            $groupedMembers = $members->groupBy('level');
                
            $formattedMembers = [];
            foreach($groupedMembers as $level => $group) {
                $firstMember = $group->first();
                $levelLabel = 'Tingkat ' . $level;
                if ($level == 1) {
                    $levelLabel = $region->type === 'desa' ? 'Kepala Desa' : ($region->type === 'kelurahan' ? 'Lurah' : 'Pimpinan');
                } else if ($level == 2 && $hasWakilInLevel1) {
                    $levelLabel = ($region->type === 'kabupaten') ? 'Wakil Kepala Daerah' : 'Wakil Pimpinan / Sekretariat';
                } else if (!empty($firstMember->level_short_label)) {
                    $levelLabel = $firstMember->level_short_label;
                }

                $formattedMembers[] = [
                    'level' => (int)$level,
                    'level_name' => $levelLabel,
                    'members' => $group->map(function($m) {
                        return [
                            'id' => $m->id,
                            'name' => $m->name,
                            'position' => $m->position,
                            'photo_url' => $m->photo_url,
                            'level' => (int)$m->level,
                            'order' => (int)$m->order,
                        ];
                    })->values()
                ];
            }
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'region' => [
                    'id' => $region->id,
                    'name' => $region->name,
                    'type' => $region->type,
                    'profile_text' => $region->profile_text,
                    'contact_phone' => $region->contact_phone,
                    'contact_email' => $region->contact_email,
                    'active_services' => $region->services ? $region->services->pluck('name')->toArray() : [],
                ],
                'structure' => $formattedMembers
            ]
        ]);
    }
}
