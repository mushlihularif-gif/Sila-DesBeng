import codecs
import os
import re

# 1. Create User Controller for KK Upload
os.makedirs("D:/laragon/www/SilaDesBeng/app/Http/Controllers/User", exist_ok=True)
filepath = "D:/laragon/www/SilaDesBeng/app/Http/Controllers/User/GasKkVerificationController.php"
user_controller = """<?php
namespace App\\Http\\Controllers\\User;

use App\\Http\\Controllers\\Controller;
use Illuminate\\Http\\Request;
use Illuminate\\Support\\Facades\\Auth;
use App\\Models\\FamilyCard;
use App\\Services\\OcrService;

class GasKkVerificationController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'kk_image' => 'required|image|max:10240',
        ]);

        $user = Auth::user();

        // Cek jika sudah ada yg pending
        $existing = FamilyCard::where('submitted_by', $user->id)->where('status', 'pending')->first();
        if ($existing) {
            return redirect()->back()->with('error', 'Anda sudah memiliki pengajuan KK yang sedang diproses.');
        }

        $kkPath = $request->file('kk_image')->store('kk_images', 'private');

        FamilyCard::create([
            'kk_image_path' => $kkPath,
            'status' => 'pending',
            'submitted_by' => $user->id,
            // no_kk_hash akan diisi admin setelah cek OCR
        ]);

        return redirect()->back()->with('success', 'Foto KK berhasil diunggah dan sedang menuggu verifikasi Admin.');
    }
}
"""
with codecs.open(filepath, 'w', encoding='utf-8') as f:
    f.write(user_controller)


# 2. Create Admin Controller for KK Verification
os.makedirs("D:/laragon/www/SilaDesBeng/app/Http/Controllers/Admin", exist_ok=True)
filepath = "D:/laragon/www/SilaDesBeng/app/Http/Controllers/Admin/GasKkAdminController.php"
admin_controller = """<?php
namespace App\\Http\\Controllers\\Admin;

use App\\Http\\Controllers\\Controller;
use Illuminate\\Http\\Request;
use Illuminate\\Support\\Facades\\Auth;
use App\\Models\\FamilyCard;
use App\\Models\\FamilyMember;
use App\\Models\\User;
use Illuminate\\Support\\Facades\\Storage;

class GasKkAdminController extends Controller
{
    public function index()
    {
        $admin = Auth::user();
        
        // Admin desa hanya melihat warga desanya
        $query = FamilyCard::with('submitter')->where('status', 'pending');
        
        if (!in_array($admin->role, ['super_admin', 'admin_kecamatan'])) {
            $query->whereHas('submitter', function($q) use ($admin) {
                $q->where('region_id', $admin->region_id);
            });
        }
        
        $pengajuan = $query->get();
        return view('admin.gas.verifikasi_kk', compact('pengajuan'));
    }

    public function showImage($id)
    {
        $kk = FamilyCard::findOrFail($id);
        $admin = Auth::user();

        if (!$kk->kk_image_path) abort(404, 'File sudah dihancurkan');
        
        $path = storage_path('app/private/' . $kk->kk_image_path);
        if (!file_exists($path)) abort(404);
        
        return response()->file($path);
    }

    public function approve(Request $request, $id)
    {
        $request->validate([
            'no_kk' => 'required|string|size:16',
            'kepala_keluarga' => 'required|string',
            'niks' => 'required|array',
            'niks.*' => 'string|size:16',
        ]);

        $kk = FamilyCard::findOrFail($id);
        
        $plainNoKk = $request->no_kk;
        $hashedNoKk = hash_hmac('sha256', $plainNoKk, config('app.key'));
        
        $kk->no_kk_hash = $hashedNoKk;
        $kk->no_kk_masked = substr($plainNoKk, 0, 4) . '********' . substr($plainNoKk, -4);
        
        $nama = $request->kepala_keluarga;
        $kk->kepala_keluarga_masked = substr($nama, 0, 1) . '***' . substr($nama, -1);
        
        $kk->status = 'verified';
        $kk->reviewed_by = Auth::id();
        $kk->reviewed_at = now();

        // PRIVACY RULE: BURN AFTER READING
        if ($kk->kk_image_path) {
            Storage::disk('private')->delete($kk->kk_image_path);
            $kk->kk_image_path = null;
        }
        
        $kk->save();

        // AUTO-CABUT NIK LOGIC
        foreach($request->niks as $plainNik) {
            $hashedNik = hash_hmac('sha256', $plainNik, config('app.key'));
            
            // Hapus NIK ini dari KK mana pun sebelumnya (Mesin Auto-Cabut)
            FamilyMember::where('nik_hash', $hashedNik)->delete();
            
            // Masukkan ke KK yang baru ini
            FamilyMember::create([
                'family_card_id' => $kk->id,
                'nik_hash' => $hashedNik
            ]);
        }

        return redirect()->back()->with('success', 'KK Disetujui. Foto KK telah dihancurkan, dan NIK ganda telah dicabut otomatis oleh sistem.');
    }

    public function reject(Request $request, $id)
    {
        $kk = FamilyCard::findOrFail($id);
        $kk->status = 'rejected';
        $kk->admin_notes = $request->reason ?? 'Foto buram atau tidak valid';
        $kk->reviewed_by = Auth::id();
        $kk->reviewed_at = now();
        
        // PRIVACY RULE: BURN AFTER READING
        if ($kk->kk_image_path) {
            Storage::disk('private')->delete($kk->kk_image_path);
            $kk->kk_image_path = null;
        }
        
        $kk->save();
        return redirect()->back()->with('success', 'KK Ditolak dan foto telah dihancurkan.');
    }
}
"""
with codecs.open(filepath, 'w', encoding='utf-8') as f:
    f.write(admin_controller)


# 3. Create Admin View
os.makedirs("D:/laragon/www/SilaDesBeng/resources/views/admin/gas", exist_ok=True)
filepath = "D:/laragon/www/SilaDesBeng/resources/views/admin/gas/verifikasi_kk.blade.php"
admin_view = """@extends('admin.layouts.admin')
@section('title', 'Verifikasi Kartu Keluarga (Krisis Gas)')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Gas Daerah /</span> Verifikasi Kartu Keluarga (Krisis Gas)</h4>

    <div class="alert alert-warning d-flex align-items-center" role="alert">
        <i class="bx bx-error-circle me-2"></i>
        <div>
            <strong>Perhatian:</strong> Sistem menganut hukum <em>Burn After Reading</em>. Foto fisik KK akan <strong>dihancurkan secara otomatis</strong> dari server begitu Anda menekan tombol Setuju atau Tolak.
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="row">
        @forelse($pengajuan as $p)
        <div class="col-md-12 col-lg-6 mb-4">
            <div class="card h-100">
                <div class="card-header border-bottom d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Pengajuan dari: {{ $p->submitter->name }}</h5>
                    <small class="text-muted">{{ $p->created_at->diffForHumans() }}</small>
                </div>
                <div class="card-body mt-3">
                    <div class="text-center mb-4 bg-light p-3 rounded">
                        <p class="text-muted mb-2"><i class='bx bx-id-card'></i> Foto KK Warga</p>
                        <a href="{{ route('admin.gas.kk.image', $p->id) }}" target="_blank" class="btn btn-outline-primary">
                            <i class='bx bx-zoom-in'></i> Lihat Foto Penuh (Aman)
                        </a>
                    </div>
                    
                    <form action="{{ route('admin.gas.kk.approve', $p->id) }}" method="POST">
                        @csrf
                        <div class="alert alert-info py-2"><small><strong>INFO:</strong> Masukkan data hasil baca mata Anda dari foto di atas. NIK yang dimasukkan akan <strong>Otomatis Tercabut</strong> dari KK lamanya (Auto-Cabut).</small></div>
                        
                        <div class="mb-3">
                            <label class="form-label text-danger fw-bold">Nomor Kartu Keluarga (KK) 16 Digit</label>
                            <input type="text" name="no_kk" class="form-control" required minlength="16" maxlength="16" placeholder="Contoh: 1472010101010001">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nama Kepala Keluarga</label>
                            <input type="text" name="kepala_keluarga" class="form-control" required placeholder="Contoh: Budi Santoso">
                        </div>
                        <div class="mb-3" id="nik-container-{{ $p->id }}">
                            <label class="form-label text-danger fw-bold">Daftar NIK Anggota (16 Digit)</label>
                            <div class="input-group mb-2">
                                <input type="text" name="niks[]" class="form-control" required minlength="16" maxlength="16" placeholder="NIK Kepala Keluarga (Wajib)">
                            </div>
                            <div class="input-group mb-2">
                                <input type="text" name="niks[]" class="form-control" required minlength="16" maxlength="16" placeholder="NIK Istri (Jika ada)">
                            </div>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-secondary mb-3" onclick="addNikField({{ $p->id }})"><i class='bx bx-plus'></i> Tambah Anggota Lainnya</button>

                        <div class="d-flex justify-content-between mt-4 pt-3 border-top">
                            <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#rejectModal-{{ $p->id }}">Tolak (Buram)</button>
                            <button type="submit" class="btn btn-success" onclick="return confirm('Anda yakin data ini sudah benar? Foto KK akan langsung dihapus setelah disetujui.')">Setujui & Hapus Foto</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Reject Modal -->
        <div class="modal fade" id="rejectModal-{{ $p->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <form action="{{ route('admin.gas.kk.reject', $p->id) }}" method="POST" class="modal-content">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Tolak Foto KK</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p class="text-danger">Foto KK akan dihapus permanen. Warga akan diminta mengunggah ulang.</p>
                        <div class="mb-3">
                            <label class="form-label">Alasan Penolakan</label>
                            <input type="text" name="reason" class="form-control" required placeholder="Contoh: Foto terpotong / buram">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-danger">Tolak & Hapus Foto</button>
                    </div>
                </form>
            </div>
        </div>
        @empty
        <div class="col-12">
            <div class="alert alert-secondary text-center">Tidak ada pengajuan foto KK yang masuk saat ini.</div>
        </div>
        @endforelse
    </div>
</div>
<script>
    function addNikField(id) {
        let container = document.getElementById('nik-container-' + id);
        let div = document.createElement('div');
        div.className = 'input-group mb-2';
        div.innerHTML = `<input type="text" name="niks[]" class="form-control" minlength="16" maxlength="16" placeholder="NIK Anggota Lainnya">
                         <button type="button" class="btn btn-outline-danger" onclick="this.parentElement.remove()">Hapus</button>`;
        container.appendChild(div);
    }
</script>
@endsection
"""
with codecs.open(filepath, 'w', encoding='utf-8') as f:
    f.write(admin_view)


# 4. Update web.php
filepath = "D:/laragon/www/SilaDesBeng/routes/web.php"
with codecs.open(filepath, 'r', encoding='utf-8') as f:
    web = f.read()

user_route = """        Route::post('gas/verify-kk', [\\App\\Http\\Controllers\\User\\GasKkVerificationController::class, 'store'])->name('user.gas.verify-kk');"""
if "user.gas.verify-kk" not in web:
    web = web.replace("        Route::get('gas/payment/{id}',", user_route + "\n        Route::get('gas/payment/{id}',")

admin_routes = """
        // Gas Crisis KK
        Route::get('gas/verifikasi-kk', [\\App\\Http\\Controllers\\Admin\\GasKkAdminController::class, 'index'])->name('admin.gas.kk.index');
        Route::get('gas/verifikasi-kk/{id}/image', [\\App\\Http\\Controllers\\Admin\\GasKkAdminController::class, 'showImage'])->name('admin.gas.kk.image');
        Route::post('gas/verifikasi-kk/{id}/approve', [\\App\\Http\\Controllers\\Admin\\GasKkAdminController::class, 'approve'])->name('admin.gas.kk.approve');
        Route::post('gas/verifikasi-kk/{id}/reject', [\\App\\Http\\Controllers\\Admin\\GasKkAdminController::class, 'reject'])->name('admin.gas.kk.reject');
"""
if "admin.gas.kk.index" not in web:
    web = web.replace("        Route::get('pengaturan',", admin_routes + "\n        Route::get('pengaturan',")

with codecs.open(filepath, 'w', encoding='utf-8') as f:
    f.write(web)

print("Gas Crisis Full Backend Completed.")
