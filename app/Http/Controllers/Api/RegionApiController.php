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
            
        // Fallback untuk Kabupaten jika kosong
        if ($members->isEmpty() && ($region->type === 'kabupaten' || $regionId == 1)) {
             $members = BumdesMember::whereNull('region_id')
                ->orWhere('region_id', 0)
                ->orWhere('region_id', 1)
                ->orderBy('level', 'asc')
                ->orderBy('order', 'asc')
                ->get();
        }

        // Jika desa atau kecamatan belum ada data pengurus di DB, generate struktur cerdas standar
        if ($members->isEmpty()) {
            $formattedMembers = [];
            if ($region->type === 'desa') {
                $desaName = $region->name;
                $formattedMembers = [
                    [
                        'level' => 1,
                        'level_name' => 'Kepala Desa',
                        'members' => [
                            [
                                'id' => 1000 + $region->id,
                                'name' => 'Kepala ' . $desaName,
                                'position' => 'Kepala Desa (Pucuk Pimpinan)',
                                'photo_url' => '',
                                'level' => 1,
                                'order' => 1,
                            ]
                        ]
                    ],
                    [
                        'level' => 2,
                        'level_name' => 'Sekretariat Desa',
                        'members' => [
                            [
                                'id' => 2000 + $region->id,
                                'name' => 'Sekretaris Desa ' . $desaName,
                                'position' => 'Sekretaris Desa (Sekdes)',
                                'photo_url' => '',
                                'level' => 2,
                                'order' => 1,
                            ],
                            [
                                'id' => 2001 + $region->id,
                                'name' => 'Kaur Keuangan & Perencanaan',
                                'position' => 'Kaur Keuangan',
                                'photo_url' => '',
                                'level' => 2,
                                'order' => 2,
                            ]
                        ]
                    ],
                    [
                        'level' => 3,
                        'level_name' => 'Badan Permusyawaratan Desa (BPD)',
                        'members' => [
                            [
                                'id' => 3000 + $region->id,
                                'name' => 'Ketua BPD ' . $desaName,
                                'position' => 'Ketua BPD',
                                'photo_url' => '',
                                'level' => 3,
                                'order' => 1,
                            ],
                            [
                                'id' => 3001 + $region->id,
                                'name' => 'Kasi Pelayanan & Kesejahteraan',
                                'position' => 'Kasi Pelayanan',
                                'photo_url' => '',
                                'level' => 3,
                                'order' => 2,
                            ]
                        ]
                    ],
                    [
                        'level' => 4,
                        'level_name' => 'Pengurus BUMDes & Kewilayahan',
                        'members' => [
                            [
                                'id' => 4000 + $region->id,
                                'name' => 'Direktur BUMDes ' . $desaName,
                                'position' => 'Direktur BUMDes',
                                'photo_url' => '',
                                'level' => 4,
                                'order' => 1,
                            ],
                            [
                                'id' => 4001 + $region->id,
                                'name' => 'Kepala Dusun I',
                                'position' => 'Kepala Dusun',
                                'photo_url' => '',
                                'level' => 4,
                                'order' => 2,
                            ],
                            [
                                'id' => 4002 + $region->id,
                                'name' => 'Kepala Dusun II',
                                'position' => 'Kepala Dusun',
                                'photo_url' => '',
                                'level' => 4,
                                'order' => 3,
                            ]
                        ]
                    ]
                ];
            } else if ($region->type === 'kecamatan') {
                $kecName = $region->name;
                $formattedMembers = [
                    [
                        'level' => 1,
                        'level_name' => 'Camat',
                        'members' => [
                            [
                                'id' => 1000 + $region->id,
                                'name' => 'Camat ' . $kecName,
                                'position' => 'Camat (Pucuk Pimpinan Wilayah)',
                                'photo_url' => '',
                                'level' => 1,
                                'order' => 1,
                            ]
                        ]
                    ],
                    [
                        'level' => 2,
                        'level_name' => 'Sekretariat Kecamatan',
                        'members' => [
                            [
                                'id' => 2000 + $region->id,
                                'name' => 'Sekretaris ' . $kecName,
                                'position' => 'Sekretaris Camat (Sekcam)',
                                'photo_url' => '',
                                'level' => 2,
                                'order' => 1,
                            ]
                        ]
                    ],
                    [
                        'level' => 3,
                        'level_name' => 'Seksi & Pelayanan Terpadu',
                        'members' => [
                            [
                                'id' => 3000 + $region->id,
                                'name' => 'Kasi Pemerintahan & Trantib',
                                'position' => 'Kepala Seksi',
                                'photo_url' => '',
                                'level' => 3,
                                'order' => 1,
                            ],
                            [
                                'id' => 3001 + $region->id,
                                'name' => 'Kasi Pelayanan Umum & Kesra',
                                'position' => 'Kepala Seksi',
                                'photo_url' => '',
                                'level' => 3,
                                'order' => 2,
                            ]
                        ]
                    ]
                ];
            }
        } else {
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
                    $levelLabel = ($region->type === 'kabupaten') ? 'Kepala Daerah' : (($region->type === 'desa') ? 'Kepala Desa' : 'Camat');
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
