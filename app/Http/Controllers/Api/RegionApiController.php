<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Region;
use App\Models\BumdesMember;

class RegionApiController extends Controller
{
    /**
     * Get all regions formatted as a hierarchy for dropdowns
     */
    public function getHierarchy()
    {
        $kabupaten = Region::where('type', 'kabupaten')->first();
        
        if (!$kabupaten) {
            return response()->json(['status' => 'error', 'message' => 'Kabupaten tidak ditemukan'], 404);
        }

        $kecamatans = Region::where('type', 'kecamatan')->orderBy('name')->get()->map(function ($kec) {
            $desas = Region::where('type', 'desa')->where('parent_id', $kec->id)->orderBy('name')->get()->map(function ($desa) {
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
        $region = Region::find($regionId);
        if (!$region) {
            return response()->json(['status' => 'error', 'message' => 'Wilayah tidak ditemukan'], 404);
        }
        
        // Coba cari data di id tersebut
        $members = BumdesMember::where('region_id', $regionId)
            ->orderBy('level', 'asc')
            ->orderBy('order', 'asc')
            ->get();
            
        // Fallback: Jika tidak ada data spesifik untuk region ini (misal baru dibuat),
        // di web kadang memakai: whereNull('region_id')->orWhere('region_id', 0) untuk default kabupaten.
        if ($members->isEmpty() && $region->type === 'kabupaten') {
             $members = BumdesMember::whereNull('region_id')
                ->orWhere('region_id', 0)
                ->orderBy('level', 'asc')
                ->orderBy('order', 'asc')
                ->get();
        }

        $groupedMembers = $members->groupBy('level');
            
        $formattedMembers = [];
        foreach($groupedMembers as $level => $group) {
            $formattedMembers[] = [
                'level' => $level,
                'level_name' => $group->first()->level_short_label ?? 'Tingkat ' . $level,
                'members' => $group->map(function($m) {
                    return [
                        'id' => $m->id,
                        'name' => $m->name,
                        'position' => $m->position,
                        'photo_url' => $m->photo_url,
                    ];
                })->values()
            ];
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'region' => [
                    'id' => $region->id,
                    'name' => $region->name,
                    'type' => $region->type,
                ],
                'structure' => $formattedMembers
            ]
        ]);
    }
}
