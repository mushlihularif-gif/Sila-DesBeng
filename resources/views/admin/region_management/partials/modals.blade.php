<!-- Modal Edit Region -->
<div class="modal fade text-start" id="editRegionModal{{ $region->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">
            <form action="{{ route('admin.kelola-wilayah.update', $region->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title">Edit Wilayah</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <label class="form-label">Nama Wilayah</label>
                    <input type="text" name="name" class="form-control" value="{{ $region->name }}" required>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Tutup</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Generate Admin -->
<div class="modal fade" id="generateAdminModal{{ $region->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="{{ route('admin.kelola-wilayah.generate-admin', $region->id) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Buat Akun Admin untuk {{ $region->name }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        Sistem akan otomatis membuatkan akun dengan level <strong>Admin {{ strtoupper($region->type) }}</strong>.
                    </div>
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Nama Pengurus <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" placeholder="Contoh: Bpk. Budi Santoso" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control" placeholder="Contoh: {{ strtolower(str_replace(' ', '', $region->name)) }}@siladesbeng.com" required>
                            <div class="form-text">Bisa menggunakan email asli atau email khusus untuk akun ini.</div>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Password Sementara</label>
                            <input type="text" name="password" class="form-control" placeholder="Biarkan kosong untuk: password123">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Tutup</button>
                    <button type="submit" class="btn btn-primary">Buat Akun</button>
                </div>
            </form>
        </div>
    </div>
</div>
