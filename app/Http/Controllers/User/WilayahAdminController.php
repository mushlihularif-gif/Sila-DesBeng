<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class WilayahAdminController extends Controller
{
    use \App\Traits\ChecksStaffDelegation;



    /**
     * Wilayah yang boleh diurus admin RT/RW ini.
     *
     * Region::getDescendantIds(null) memakai where('parent_id', null), yang oleh
     * Laravel diubah menjadi IS NULL — jadi yang kembali adalah SELURUH pohon
     * wilayah dari akarnya. Akibatnya admin yang region_id-nya kosong justru
     * mengakses data satu kabupaten penuh. Di sini
     * ketiadaan wilayah berarti tidak ada yang bisa dijangkau.
     *
     * @return array<int>
     */
    private function wilayahDiurus($user): array
    {
        if (! $user->region_id) {
            return [];
        }

        $ids = \App\Models\Region::getDescendantIds($user->region_id);
        $ids[] = $user->region_id;

        return $ids;
    }

    /**
     * Tingkat jabatan admin ini: 'rt', 'rw', 'desa', dan seterusnya.
     *
     * $user->region bisa null — akun yang belum ditempatkan, atau wilayahnya
     * sudah dihapus — dan membaca ->type langsung membuat halamannya jatuh 500.
     */
    private function tingkatAdmin($user): ?string
    {
        return $user->region?->type;
    }

    public function indexPengumuman(Request $request)
    {
        return redirect()->route('content.index');
    }

    public function indexBerita(Request $request)
    {
        return redirect()->route('content.index');
    }

    public function storePengumuman(Request $request)
    {
        abort(409, 'Gunakan editor Kabar dan Informasi Daerah terbaru untuk mengajukan pengumuman.');
    }

    public function storeBerita(Request $request)
    {
        abort(409, 'Gunakan editor Kabar dan Informasi Daerah terbaru untuk mengajukan kabar.');
    }

    public function editKonten(\App\Models\Announcement $announcement)
    {
        abort_unless(app(\App\Services\ContentAccess::class)->canInspect(auth()->user(), $announcement),403);
        return redirect()->route('content.show',$announcement);
    }

    public function updateKonten(Request $request, \App\Models\Announcement $announcement)
    {
        abort(409, 'Mulai penanganan pada editor terbaru sebelum menyimpan revisi.');
    }

    public function destroyKonten(\App\Models\Announcement $announcement)
    {
        abort(409, 'Gunakan editor terbaru untuk menangani atau membatalkan publikasi.');
    }

    public function indexWarga(Request $request)
    {
        $search = $request->get('search');
        $user = auth()->user();
        
        $allowedRegionIds = $this->wilayahDiurus($user);
        
        $usersQuery = \App\Models\User::with('region')
            ->whereIn('region_id', $allowedRegionIds)
            ->where('role', 'user');

        $wargas = $usersQuery->when($search, function ($query, $search) {
                return $query->where(function($q) use ($search) {
                    $q->searchWhereLike(['name', 'email'], $search);
                });
            })
            ->orderBy('name', 'asc')
            ->paginate(15)
            ->appends(['search' => $search]);

        return view('user.wilayah.warga', compact('wargas', 'search'));
    }

}
