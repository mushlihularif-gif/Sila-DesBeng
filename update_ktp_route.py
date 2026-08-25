import codecs
import re

# Update Controller to add ktp method
filepath = "D:/laragon/www/SilaDesBeng/app/Http/Controllers/Admin/MutasiAdminController.php"
with codecs.open(filepath, 'r', encoding='utf-8') as f:
    admin_content = f.read()

ktp_method = """    public function showKtp($id)
    {
        $mutasi = MutasiPenduduk::findOrFail($id);
        $admin = Auth::user();

        // Check auth
        if ($mutasi->from_region_id != $admin->region_id && $mutasi->to_region_id != $admin->region_id && !in_array($admin->role, ['super_admin', 'admin_kecamatan'])) {
            abort(403);
        }

        if (!$mutasi->ktp_image_path) {
            abort(404, 'KTP tidak ditemukan atau sudah dihapus.');
        }

        $path = storage_path('app/private/' . $mutasi->ktp_image_path);
        if (!file_exists($path)) {
            abort(404, 'File fisik tidak ditemukan.');
        }

        return response()->file($path);
    }
}"""
admin_content = re.sub(r'}\s*$', ktp_method, admin_content)

with codecs.open(filepath, 'w', encoding='utf-8') as f:
    f.write(admin_content)

# Update web.php
webpath = "D:/laragon/www/SilaDesBeng/routes/web.php"
with codecs.open(webpath, 'r', encoding='utf-8') as f:
    web = f.read()

old_route = """        Route::post('warga/mutasi/{id}/approve', [MutasiAdminController::class, 'approve'])->name('admin.warga.mutasi.approve');"""
new_route = """        Route::post('warga/mutasi/{id}/approve', [MutasiAdminController::class, 'approve'])->name('admin.warga.mutasi.approve');
        Route::get('warga/mutasi/{id}/ktp', [MutasiAdminController::class, 'showKtp'])->name('admin.warga.mutasi.ktp');"""
web = web.replace(old_route, new_route)

with codecs.open(webpath, 'w', encoding='utf-8') as f:
    f.write(web)

print("KTP Route and Controller updated.")
