@forelse($supirs as $supir)
<div class="card border-0 shadow-sm rounded-3 mb-2 w-100 overflow-hidden">
    <div class="card-body p-2.5">
        <div class="d-flex align-items-center justify-content-between mb-2 pb-1.5 border-bottom">
            <div class="d-flex align-items-center overflow-hidden min-w-0">
                <div class="avatar avatar-sm me-2 flex-shrink-0">
                    @if($supir->foto)
                        <img src="{{ asset('storage/' . $supir->foto) }}" alt="Avatar" class="rounded-circle object-fit-cover shadow-sm border" style="width: 34px; height: 34px;">
                    @else
                        <span class="avatar-initial rounded-circle bg-label-{{ $supir->isPengurusGedung() ? 'success' : 'primary' }} shadow-sm border fw-bold d-flex align-items-center justify-content-center" style="width: 34px; height: 34px; font-size: 0.8rem;">{{ strtoupper(substr($supir->nama, 0, 1)) }}</span>
                    @endif
                </div>
                <div class="overflow-hidden min-w-0">
                    <h6 class="mb-0 fw-bold text-dark text-truncate" style="font-size: 0.88rem;">{{ $supir->nama }}</h6>
                    <small class="text-muted d-block" style="font-size: 0.7rem;">ID: #{{ str_pad($supir->id, 4, '0', STR_PAD_LEFT) }}</small>
                </div>
            </div>
            <div class="flex-shrink-0 ms-1">
                @if($supir->status == 'Tersedia')
                    <span class="badge bg-label-success rounded-pill px-2 py-0.5" style="font-size: 0.68rem;"><i class="bx bx-check-circle me-0.5"></i>Tersedia</span>
                @elseif($supir->status == 'Sedang Bertugas')
                    <span class="badge bg-label-warning rounded-pill px-2 py-0.5" style="font-size: 0.68rem;"><i class="bx bx-run me-0.5"></i>Bertugas</span>
                @else
                    <span class="badge bg-label-secondary rounded-pill px-2 py-0.5" style="font-size: 0.68rem;"><i class="bx bx-minus-circle me-0.5"></i>Nonaktif</span>
                @endif
            </div>
        </div>

        <div class="d-flex flex-wrap align-items-center justify-content-between gap-1 mb-1.5">
            <div class="d-flex align-items-center">
                <i class="bx bxl-whatsapp text-success me-1 fs-6"></i>
                <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $supir->kontak ?? '') }}" target="_blank" class="fw-semibold text-dark text-decoration-none" style="font-size: 0.78rem;">
                    {{ $supir->kontak ?? '-' }}
                </a>
            </div>
            <div>
                @if($supir->user_id)
                    <span class="badge bg-label-info rounded-pill px-1.5 py-0.5" style="font-size: 0.65rem;"><i class="bx bx-link me-0.5"></i>{{ $supir->user->name }}</span>
                @else
                    <span class="badge bg-label-secondary rounded-pill px-1.5 py-0.5" style="font-size: 0.65rem;"><i class="bx bx-unlink me-0.5"></i>Belum tertaut</span>
                @endif
            </div>
        </div>

        <div class="mb-2 pt-1 border-top">
            <div class="d-flex flex-wrap gap-1 align-items-center">
                @if($supir->isPengurusGedung())
                    <span class="badge bg-label-success rounded-pill px-2 py-0.5" style="font-size: 0.68rem;"><i class="bx bx-building-house me-0.5"></i>Pengurus Gedung</span>
                    @if($supir->fasilitas && $supir->fasilitas->count() > 0)
                        <span class="badge bg-light text-secondary border rounded-pill px-1.5 py-0.5" style="font-size: 0.65rem;">{{ $supir->fasilitas->pluck('nama_fasilitas')->join(', ') }}</span>
                    @endif
                @else
                    @if($supir->is_sewa_mobil)
                        <span class="badge bg-label-primary rounded-pill px-2 py-0.5" style="font-size: 0.68rem;"><i class="bx bx-car me-0.5"></i>Rental Mobil</span>
                    @endif
                    @if($supir->is_fasilitas_umum)
                        <span class="badge bg-label-danger rounded-pill px-2 py-0.5" style="font-size: 0.68rem;"><i class="bx bx-plus-medical me-0.5"></i>Ambulans</span>
                    @endif
                    @if(!$supir->is_sewa_mobil && !$supir->is_fasilitas_umum)
                        <span class="text-muted fst-italic" style="font-size: 0.7rem;">Belum ada kategori</span>
                    @endif
                @endif
            </div>
        </div>

        <div class="d-flex gap-1.5 pt-1.5 border-top">
            <button type="button" class="btn btn-sm btn-outline-secondary flex-grow-1 rounded-pill d-flex align-items-center justify-content-center gap-1 py-1" data-bs-toggle="modal" data-bs-target="#detailSupirModal{{ $supir->id }}" style="font-size: 0.75rem;">
                <i class="bx bx-show"></i> Detail
            </button>
            <button type="button" class="btn btn-sm btn-outline-primary flex-grow-1 rounded-pill d-flex align-items-center justify-content-center gap-1 py-1" data-bs-toggle="modal" data-bs-target="#editSupirModal{{ $supir->id }}" style="font-size: 0.75rem;">
                <i class="bx bx-edit-alt"></i> Edit
            </button>
            <form action="{{ route('supir.destroy', $supir->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data ini?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-sm btn-outline-danger rounded-circle p-1 d-flex align-items-center justify-content-center" style="width: 28px; height: 28px;" title="Hapus Data">
                    <i class="bx bx-trash" style="font-size: 0.85rem;"></i>
                </button>
            </form>
        </div>
    </div>
</div>
@empty
<div class="card border-0 shadow-sm rounded-4">
    <div class="card-body text-center py-5">
        <i class="bx {{ ($tab ?? 'supir') === 'pengurus_gedung' ? 'bx-building-house' : 'bx-user-x' }} fs-1 text-muted mb-3 d-block"></i>
        <h6>Belum ada data {{ ($tab ?? 'supir') === 'pengurus_gedung' ? 'pengurus gedung' : 'supir kendaraan' }}.</h6>
        <p class="text-muted small mb-0">Klik tombol "{{ ($tab ?? 'supir') === 'pengurus_gedung' ? 'Tambah Pengurus Gedung' : 'Tambah Supir Baru' }}" di atas untuk mulai.</p>
    </div>
</div>
@endforelse
