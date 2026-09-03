<!-- Edit Modal -->
<div class="modal fade" id="editSupirModal{{ $supir->id }}">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Data Supir</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('supir.update', $supir->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="row">
                        <div class="col mb-3">
                            <label class="form-label">Nama Supir / Petugas <span class="text-danger">*</span></label>
                            <input type="text" name="nama" class="form-control" value="{{ $supir->nama }}" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col mb-3">
                            <label class="form-label">No. WhatsApp / Kontak</label>
                            <input type="text" name="kontak" class="form-control" value="{{ $supir->kontak }}" placeholder="Contoh: 08123456789">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col mb-3">
                            <label class="form-label">Status Ketersediaan <span class="text-danger">*</span></label>
                            <select name="status" class="form-select" required>
                                <option value="Tersedia" {{ $supir->status == 'Tersedia' ? 'selected' : '' }}>Tersedia</option>
                                <option value="Sedang Bertugas" {{ $supir->status == 'Sedang Bertugas' ? 'selected' : '' }}>Sedang Bertugas</option>
                                <option value="Tidak Aktif" {{ $supir->status == 'Tidak Aktif' ? 'selected' : '' }}>Tidak Aktif (Cuti/Sakit)</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>
