<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KycVerification;
use App\Models\User;
use App\Models\Region;
use App\Services\FonnteService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class KycReviewController extends Controller
{
    /**
     * Meninjau KTP dan foto wajah warga adalah kewenangan pemerintah wilayah.
     *
     * Grup rute memakai `role:admin`, dan pseudo-role itu di CheckRole ikut
     * meloloskan 'staff' - tanpa penjaga ini setiap staf unit (gas, sewa alat)
     * bisa membuka dan menyetujui berkas identitas warga se-kabupaten.
     */
    private function pastikanPeninjau(): void
    {
        abort_unless(
            in_array(auth()->user()?->role, ['super_admin', 'admin', 'admin_kecamatan', 'admin_desa'], true),
            403,
            'Peninjauan verifikasi identitas hanya untuk admin wilayah dan Super Admin.'
        );
    }

    /**
     * Wilayah yang pengajuannya boleh ditinjau akun ini.
     *
     * Rantainya menurun: admin desa meninjau warganya sendiri, admin kecamatan
     * ikut melihat seluruh desa di bawahnya (jadi desa yang belum punya admin
     * atau yang pengajuannya menggantung tetap ada yang mengurus), dan Kominfo
     * melihat semuanya sebagai jaring terakhir.
     *
     * @return array<int>|null null berarti tanpa batas wilayah
     */
    private function wilayahDitinjau(): ?array
    {
        $admin = Auth::user();

        // Kominfo, dan akun pusat yang memang tidak ditempatkan di wilayah mana pun.
        if ($admin->role === 'super_admin' || ($admin->role === 'admin' && ! $admin->region_id)) {
            return null;
        }

        if (! $admin->region_id) {
            return [];
        }

        return array_merge([$admin->region_id], Region::getDescendantIds($admin->region_id));
    }

    /** Tolak pengajuan milik wilayah di luar jangkauan peninjau. */
    private function pastikanDalamJangkauan(KycVerification $kyc): void
    {
        $wilayah = $this->wilayahDitinjau();

        if ($wilayah === null) {
            return;
        }

        abort_unless(
            in_array($kyc->user?->region_id, $wilayah),
            403,
            'Pengajuan ini milik warga wilayah lain.'
        );
    }

    public function index(Request $request)
    {
        $this->pastikanPeninjau();

        $wilayah = $this->wilayahDitinjau();

        // Keempat kueri di bawah datang dari main dan dibiarkan apa adanya.
        // Yang ditambahkan hanya batas wilayahnya: sebelumnya daftar ini
        // diambil tanpa syarat wilayah sama sekali, sehingga admin desa mana
        // pun melihat - dan bisa menyetujui - berkas KTP warga sekabupaten.
        $batasWilayah = fn ($q) => $q->when($wilayah !== null, fn ($k) => $k->whereHas(
            'user',
            fn ($u) => $u->whereIn('region_id', $wilayah)
        ));

        $pending = $batasWilayah(KycVerification::with('user'))
            ->where('status', 'pending')
            ->latest()
            ->get();
            
        $approved = $batasWilayah(KycVerification::with('user'))
            ->where('status', 'approved')
            ->latest()
            ->get();
            
        $rejected = $batasWilayah(KycVerification::with('user'))
            ->where('status', 'rejected')
            ->latest()
            ->get();
            
        $all = $batasWilayah(KycVerification::with('user'))
            ->whereIn('status', ['pending', 'approved', 'rejected'])
            ->latest()
            ->get();
        
        $counts = [
            'all' => $all->count(),
            'pending' => $pending->count(),
            'approved' => $approved->count(),
            'rejected' => $rejected->count(),
        ];

        // Dipakai tabel untuk menandai mana warga wilayah sendiri dan mana yang
        // berasal dari wilayah bawahan.
        $wilayahSendiri = Auth::user()->region_id;
        $lingkup = $wilayah === null
            ? 'seluruh kabupaten'
            : (Region::find($wilayahSendiri)?->name ?? 'wilayah Anda') . ' dan wilayah di bawahnya';

        return view('admin.kyc.index', compact(
            'all', 'pending', 'approved', 'rejected', 'counts', 'wilayahSendiri', 'lingkup'
        ));
    }

    public function show($id)
    {
        $this->pastikanPeninjau();

        $kyc = KycVerification::with('user')->findOrFail($id);
        $this->pastikanDalamJangkauan($kyc);
        return view('admin.kyc.show', compact('kyc'));
    }

    public function approve(Request $request, $id, FonnteService $fonnte)
    {
        $this->pastikanPeninjau();

        $kyc = KycVerification::with('user')->findOrFail($id);
        $this->pastikanDalamJangkauan($kyc);

        $user = $kyc->user;

        DB::beginTransaction();
        try {
            $kyc->update([
                'status' => 'approved',
                'admin_notes' => $request->admin_notes,
                'reviewed_by' => Auth::id(),
                'reviewed_at' => now(),
            ]);

            if ($kyc->desa_from_ocr) {
                $region = \App\Models\Region::where('name', 'like', '%' . $kyc->desa_from_ocr . '%')
                                ->where('type', 'desa')
                                ->first();
                if ($region && $user->region_id != $region->id) {
                    $user->region_id = $region->id;
                }
            }
            
            // Sinkronisasi data identitas terverifikasi ke profil pengguna
            $realNik = $kyc->nik_from_ocr ?? $user->nik;
            $realName = $kyc->name_from_ocr ?? $user->name;

            if ($realNik) {
                $user->nik = $realNik;
            }
            if ($realName) {
                $user->name = $realName;
            }
            $user->gender = $kyc->gender_from_ocr ?? $user->gender;
            $user->address = $kyc->address_from_ocr ?? $user->address;
            $user->rt = $kyc->rt_from_ocr ?? $user->rt;
            $user->rw = $kyc->rw_from_ocr ?? $user->rw;
            $user->verification_status = 'verified';
            $user->verified_at = now();

            $user->save();
            
            // Hapus file fisik secara permanen untuk privasi!
            if ($kyc->ktp_image_path && \Illuminate\Support\Facades\Storage::disk('private')->exists($kyc->ktp_image_path)) {
                \Illuminate\Support\Facades\Storage::disk('private')->delete($kyc->ktp_image_path);
            }
            if ($kyc->face_image_path && \Illuminate\Support\Facades\Storage::disk('private')->exists($kyc->face_image_path)) {
                \Illuminate\Support\Facades\Storage::disk('private')->delete($kyc->face_image_path);
            }
            
            // Hapus data wajah dan path dari database
            $kyc->update([
                'ktp_image_path' => null,
                'face_image_path' => null,
                'face_scan_data' => null
            ]);

            // Kirim notifikasi ke akun warga
            \App\Services\NotificationService::notifyKycApproved($user);

            DB::commit();

            $fonnte->sendNotification($user->phone, "*SiladesBeng (Sistem Sinergi Layanan dan Aspirasi Desa di Kabupaten Bengkalis)*\n\nSelamat! Akun Anda telah berhasil diverifikasi. Anda sekarang mendapatkan lencana Centang Biru (Identitas Terverifikasi).");

            return redirect()->route('admin.kyc.index')->with('success', 'Verifikasi disetujui. Foto KTP dan Wajah telah dihapus permanen.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function reject(Request $request, $id, FonnteService $fonnte)
    {
        $this->pastikanPeninjau();

        $request->validate(['admin_notes' => 'required|string']);

        $kyc = KycVerification::with('user')->findOrFail($id);
        $this->pastikanDalamJangkauan($kyc);

        $user = $kyc->user;

        DB::beginTransaction();
        try {
            $kyc->update([
                'status' => 'rejected',
                'admin_notes' => $request->admin_notes,
                'reviewed_by' => Auth::id(),
                'reviewed_at' => now(),
            ]);

            $user->update([
                'verification_status' => 'unverified'
            ]);
            
            // Hapus file fisik secara permanen untuk privasi!
            if ($kyc->ktp_image_path && \Illuminate\Support\Facades\Storage::disk('private')->exists($kyc->ktp_image_path)) {
                \Illuminate\Support\Facades\Storage::disk('private')->delete($kyc->ktp_image_path);
            }
            if ($kyc->face_image_path && \Illuminate\Support\Facades\Storage::disk('private')->exists($kyc->face_image_path)) {
                \Illuminate\Support\Facades\Storage::disk('private')->delete($kyc->face_image_path);
            }
            
            // Hapus path dari database
            $kyc->update([
                'ktp_image_path' => null,
                'face_image_path' => null,
                'face_scan_data' => null
            ]);

            // Kirim notifikasi ke akun warga
            \App\Services\NotificationService::notifyKycRejected($user, $request->admin_notes);

            DB::commit();

            $fonnte->sendNotification($user->phone, "*SiladesBeng (Sistem Sinergi Layanan dan Aspirasi Desa di Kabupaten Bengkalis)*\n\nMohon maaf, verifikasi identitas Anda ditolak.\nAlasan: {$request->admin_notes}\nSilakan ajukan ulang melalui menu Profil.");

            return redirect()->route('admin.kyc.index')->with('success', 'Verifikasi ditolak. Foto KTP dan Wajah telah dihapus permanen.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}

