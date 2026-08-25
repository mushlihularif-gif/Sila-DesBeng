import codecs
import re

filepath = "D:/laragon/www/SilaDesBeng/resources/views/admin/warga/mutasi.blade.php"
with codecs.open(filepath, 'r', encoding='utf-8') as f:
    content = f.read()

# Add KTP link and Alamat info to the table row (Pelepasan/Keluar)
# Find the line showing Desa Tujuan
old_row_keluar = """                                <td>{{ $p->toRegion->desa }}</td>"""
new_row_keluar = """                                <td>
                                    <strong>{{ $p->toRegion->desa }}</strong><br>
                                    <small class="text-muted">{{ $p->alamat_baru }}, RT {{ $p->rt_baru }}/RW {{ $p->rw_baru }}</small>
                                </td>"""
content = content.replace(old_row_keluar, new_row_keluar)

# Find the reason column to inject KTP link
old_reason_col = """                                <td style="max-width:200px; white-space:pre-wrap;">{{ $p->reason }}</td>"""
new_reason_col = """                                <td style="max-width:200px; white-space:pre-wrap;">
                                    {{ $p->reason }}
                                    @if($p->ktp_image_path)
                                    <div class="mt-2">
                                        <a href="{{ route('admin.warga.mutasi.ktp', $p->id) }}" target="_blank" class="btn btn-xs btn-outline-primary"><i class="bx bx-id-card"></i> Lihat KTP</a>
                                    </div>
                                    @endif
                                </td>"""
content = content.replace(old_reason_col, new_reason_col)

# Add required Alamat, RT, RW to the "Tarik Warga" form
old_form_tarik = """                    <div class="mb-3">
                        <label class="form-label">NIK Warga</label>
                        <input type="text" name="nik" class="form-control" required placeholder="Masukkan 16 digit NIK">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Alasan Penarikan</label>
                        <input type="text" name="reason" class="form-control" required placeholder="Contoh: Warga lansia pindah domisili ikut anaknya">
                    </div>"""
new_form_tarik = """                    <div class="mb-3">
                        <label class="form-label">NIK Warga</label>
                        <input type="text" name="nik" class="form-control" required placeholder="Masukkan 16 digit NIK">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Alamat Lengkap Baru</label>
                        <input type="text" name="alamat_baru" class="form-control" required placeholder="Contoh: Jl. Merdeka No 12">
                    </div>
                    <div class="row mb-3">
                        <div class="col-6">
                            <label class="form-label">RT Baru</label>
                            <input type="text" name="rt_baru" class="form-control" required placeholder="001">
                        </div>
                        <div class="col-6">
                            <label class="form-label">RW Baru</label>
                            <input type="text" name="rw_baru" class="form-control" required placeholder="002">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Alasan Penarikan</label>
                        <input type="text" name="reason" class="form-control" required placeholder="Contoh: Warga lansia pindah domisili ikut anaknya">
                    </div>"""
content = content.replace(old_form_tarik, new_form_tarik)

with codecs.open(filepath, 'w', encoding='utf-8') as f:
    f.write(content)

print("Admin view updated.")
