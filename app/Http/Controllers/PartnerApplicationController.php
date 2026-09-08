<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Region;
use App\Models\PartnerApplication;
use App\Models\AdminNotification;
use App\Models\User;
use App\Services\NotificationService;

class PartnerApplicationController extends Controller
{
    public function create()
    {
        // 1. Ambil data direktori kecamatan dan desa
        $kecamatans = Region::where('type', 'kecamatan')
            ->with(['children' => function($query) {
                $query->where('type', 'desa')
                      ->with(['services' => function($q) {
                          $q->wherePivot('is_active', true);
                      }, 'users']);
            }])
            ->get();

        // 2. Ambil seluruh region untuk kalkulasi relasi
        $regions = Region::all();

        $isJoined = false;
        $userDesa = null;
        $userKecamatan = null;
        $existingRws = collect();
        $existingRwsData = [];
        $pendingApplication = null;
        $user = auth()->user();

        if ($user) {
            $pendingApplication = PartnerApplication::where('user_id', $user->id)
                ->where('status', 'pending')
                ->latest()
                ->first();

            if ($user->region_id) {
                $temp = Region::find($user->region_id);
                while ($temp) {
                    if ($temp->type === 'desa' || $temp->type === 'kelurahan') {
                        $userDesa = $temp;
                        break;
                    }
                    $temp = $temp->parent;
                }

                if ($userDesa) {
                    $userKecamatan = $userDesa->parent;

                    // Cek apakah desa ini sudah punya admin
                    $hasAdmin = $userDesa->users()
                        ->whereIn('role', ['admin_desa', 'admin'])
                        ->exists();

                    if ($hasAdmin) {
                        $isJoined = true;
                    }

                    // Ambil daftar RW di desa ini beserta adminnya dan anak RT-nya
                    $existingRws = Region::where('parent_id', $userDesa->id)
                        ->where('type', 'rw')
                        ->orderBy('name')
                        ->with([
                            'users' => function($q) {
                                $q->where('role', 'admin_rw');
                            },
                            'children' => function($q) {
                                $q->where('type', 'rt')->orderBy('name');
                            },
                            'children.users' => function($q) {
                                $q->where('role', 'admin_rt');
                            }
                        ])
                        ->get();

                    $existingRwsData = $existingRws->map(function($rw) {
                        $adminRw = $rw->users->where('role', 'admin_rw')->first();
                        return [
                            'id' => $rw->id,
                            'name' => $rw->name,
                            'has_admin' => (bool)$adminRw,
                            'admin_name' => $adminRw ? $adminRw->name : null,
                            'rts' => $rw->children->map(function($rt) {
                                $adminRt = $rt->users->where('role', 'admin_rt')->first();
                                return [
                                    'id' => $rt->id,
                                    'name' => $rt->name,
                                    'has_admin' => (bool)$adminRt,
                                    'admin_name' => $adminRt ? $adminRt->name : null,
                                ];
                            })->values()->all(),
                        ];
                    })->values()->all();
                }
            }
        }

        return view('pages.kemitraan.create', compact(
            'regions',
            'kecamatans',
            'isJoined',
            'userDesa',
            'userKecamatan',
            'existingRws',
            'existingRwsData',
            'pendingApplication'
        ));
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
        $user = auth()->user();
        $userDesa = null;

        if ($user && $user->region_id) {
            $temp = Region::find($user->region_id);
            while ($temp) {
                if ($temp->type === 'desa' || $temp->type === 'kelurahan') {
                    $userDesa = $temp;
                    break;
                }
                $temp = $temp->parent;
            }
        }

        // Cek pengajuan yang masih pending dari pengguna ini
        if ($user) {
            $existingPending = PartnerApplication::where('user_id', $user->id)
                ->where('status', 'pending')
                ->first();

            if ($existingPending) {
                return back()->withInput()->withErrors([
                    'applicant_name' => 'Anda masih memiliki pengajuan kemitraan yang sedang dalam proses peninjauan oleh Pemerintah Desa.',
                ]);
            }
        }

        // Normalisasi, Validasi Keberadaan Admin, dan Penguncian Wilayah Pengurus RW / RT
        if ($userDesa && in_array($request->region_type, ['rw', 'rt'], true)) {
            if ($request->region_type === 'rw') {
                // Kunci induk RW ke desa domisili user
                $rawRw = trim($request->region_name ?? $request->rw_number ?? '');
                $cleanRwNum = preg_replace('/[^0-9]/', '', $rawRw);
                $formattedRwName = $cleanRwNum ? 'RW ' . str_pad($cleanRwNum, 2, '0', STR_PAD_LEFT) : $rawRw;

                if (! $formattedRwName) {
                    return back()->withInput()->withErrors([
                        'region_name' => 'Nomor RW wajib diisi.',
                    ]);
                }

                // Cek apakah RW ini sudah terdaftar dan memiliki Admin aktif
                $targetRw = Region::where('parent_id', $userDesa->id)
                    ->where('type', 'rw')
                    ->where(function($q) use ($formattedRwName, $cleanRwNum) {
                        $q->where('name', $formattedRwName);
                        if ($cleanRwNum) {
                            $q->orWhere('name', 'RW ' . (int)$cleanRwNum)
                              ->orWhere('name', 'RW ' . str_pad($cleanRwNum, 2, '0', STR_PAD_LEFT));
                        }
                    })
                    ->first();

                if ($targetRw) {
                    $existingAdmin = User::where('region_id', $targetRw->id)
                        ->where('role', 'admin_rw')
                        ->first();

                    if ($existingAdmin) {
                        return back()->withInput()->withErrors([
                            'region_name' => "Gagal: {$targetRw->name} di Desa {$userDesa->name} sudah memiliki Admin aktif ({$existingAdmin->name}). Silakan tentukan nomor RW lain.",
                        ]);
                    }
                }

                // Cek apakah ada pengajuan lain untuk RW ini yang sedang pending
                $pendingRwApp = PartnerApplication::where('status', 'pending')
                    ->where('region_type', 'rw')
                    ->where('parent_region_id', $userDesa->id)
                    ->where(function($q) use ($formattedRwName, $cleanRwNum) {
                        $q->where('region_name', $formattedRwName);
                        if ($cleanRwNum) {
                            $q->orWhere('region_name', 'RW ' . (int)$cleanRwNum)
                              ->orWhere('region_name', 'RW ' . str_pad($cleanRwNum, 2, '0', STR_PAD_LEFT));
                        }
                    })
                    ->first();

                if ($pendingRwApp) {
                    return back()->withInput()->withErrors([
                        'region_name' => "Pengajuan kemitraan untuk {$formattedRwName} saat ini sudah ada yang sedang diproses oleh Pemerintah Desa.",
                    ]);
                }

                $request->merge([
                    'parent_region_id' => $userDesa->id,
                    'region_name' => $formattedRwName,
                ]);
            } elseif ($request->region_type === 'rt') {
                $parentRwId = $request->parent_region_id;
                $parentRw = null;

                // Jika pemohon menginput nomor RW induk secara manual
                if ($request->filled('parent_rw_name')) {
                    $rawParentRw = trim($request->parent_rw_name);
                    $cleanParentRwNum = preg_replace('/[^0-9]/', '', $rawParentRw);
                    $formattedParentRw = $cleanParentRwNum ? 'RW ' . str_pad($cleanParentRwNum, 2, '0', STR_PAD_LEFT) : $rawParentRw;

                    $parentRw = Region::firstOrCreate(
                        ['name' => $formattedParentRw, 'type' => 'rw', 'parent_id' => $userDesa->id],
                        ['profile_text' => 'Lingkungan ' . $formattedParentRw]
                    );
                    $parentRwId = $parentRw->id;
                } else {
                    $parentRw = Region::find($parentRwId);
                    if (! $parentRw || $parentRw->type !== 'rw' || $parentRw->parent_id != $userDesa->id) {
                        return back()->withInput()->withErrors([
                            'parent_region_id' => 'RW yang dipilih tidak valid atau berada di luar wilayah desa Anda (' . $userDesa->name . ').',
                        ]);
                    }
                }

                $rawRt = trim($request->region_name ?? $request->rt_number ?? '');
                $cleanRtNum = preg_replace('/[^0-9]/', '', $rawRt);
                $formattedRtName = $cleanRtNum ? 'RT ' . str_pad($cleanRtNum, 2, '0', STR_PAD_LEFT) : $rawRt;

                if (! $formattedRtName) {
                    return back()->withInput()->withErrors([
                        'region_name' => 'Nomor RT wajib diisi.',
                    ]);
                }

                // Cek apakah RT ini sudah terdaftar di bawah RW tersebut dan memiliki Admin aktif
                $targetRt = Region::where('parent_id', $parentRwId)
                    ->where('type', 'rt')
                    ->where(function($q) use ($formattedRtName, $cleanRtNum) {
                        $q->where('name', $formattedRtName);
                        if ($cleanRtNum) {
                            $q->orWhere('name', 'RT ' . (int)$cleanRtNum)
                              ->orWhere('name', 'RT ' . str_pad($cleanRtNum, 2, '0', STR_PAD_LEFT));
                        }
                    })
                    ->first();

                if ($targetRt) {
                    $existingAdmin = User::where('region_id', $targetRt->id)
                        ->where('role', 'admin_rt')
                        ->first();

                    if ($existingAdmin) {
                        return back()->withInput()->withErrors([
                            'region_name' => "Gagal: {$targetRt->name} di lingkungan {$parentRw->name} sudah memiliki Admin aktif ({$existingAdmin->name}). Silakan tentukan nomor RT lain.",
                        ]);
                    }
                }

                // Cek apakah ada pengajuan lain untuk RT ini di bawah RW tersebut yang sedang pending
                $pendingRtApp = PartnerApplication::where('status', 'pending')
                    ->where('region_type', 'rt')
                    ->where('parent_region_id', $parentRwId)
                    ->where(function($q) use ($formattedRtName, $cleanRtNum) {
                        $q->where('region_name', $formattedRtName);
                        if ($cleanRtNum) {
                            $q->orWhere('region_name', 'RT ' . (int)$cleanRtNum)
                              ->orWhere('region_name', 'RT ' . str_pad($cleanRtNum, 2, '0', STR_PAD_LEFT));
                        }
                    })
                    ->first();

                if ($pendingRtApp) {
                    return back()->withInput()->withErrors([
                        'region_name' => "Pengajuan kemitraan untuk {$formattedRtName} di lingkungan {$parentRw->name} saat ini sudah ada yang sedang diproses oleh Pemerintah Desa.",
                    ]);
                }

                $request->merge([
                    'parent_region_id' => $parentRwId,
                    'region_name' => $formattedRtName,
                ]);
            }
        } elseif ($userDesa && $request->region_type === 'desa') {
            // Cek apakah desa user sudah memiliki admin
            $hasAdmin = $userDesa->users()->whereIn('role', ['admin_desa', 'admin'])->exists();
            if ($hasAdmin) {
                return back()->withInput()->withErrors([
                    'region_type' => 'Desa Anda (' . $userDesa->name . ') sudah terdaftar dan memiliki Admin Desa. Silakan ajukan pendaftaran sebagai Pengurus RW atau RT.',
                ]);
            }
        }

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

        $induk = Region::find($validated['parent_region_id']);

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

        $application = PartnerApplication::create($validated);

        if (auth()->check()) {
            NotificationService::notifyPartnerApplicationSubmitted($application);
        }

        if ($validated['parent_region_id']) {
            $namaTingkat = self::LABEL_TINGKAT[$validated['region_type']] ?? ucfirst($validated['region_type']);
            AdminNotification::create([
                'type' => 'kemitraan',
                'reference_id' => $application->id,
                'region_id' => $validated['parent_region_id'],
                'title' => "Pengajuan Kemitraan {$namaTingkat} Baru",
                'message' => "Pemohon {$validated['applicant_name']} mengajukan pendaftaran {$namaTingkat} {$validated['region_name']} ({$validated['position']}).",
                'is_read' => false,
            ]);

            // Jika pengajuan RT di bawah RW, beri tahu juga Admin Desa induknya
            if ($induk && $induk->type === 'rw' && $induk->parent_id) {
                AdminNotification::create([
                    'type' => 'kemitraan',
                    'reference_id' => $application->id,
                    'region_id' => $induk->parent_id,
                    'title' => 'Pengajuan Pengurus RT Baru',
                    'message' => "Warga {$validated['applicant_name']} mengajukan pendaftaran pengurus {$validated['region_name']} ({$validated['position']}) di lingkungan {$induk->name}.",
                    'is_read' => false,
                ]);
            }
        }

        return redirect()->back()->with('success_modal', 'Pengajuan kemitraan Anda berhasil dikirim. Silakan menunggu proses verifikasi dan peninjauan oleh pihak Pemerintah Desa.');
    }
}
