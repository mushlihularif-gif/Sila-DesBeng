@forelse($supirs as $supir)
<tr>
    <td class="ps-4">
        <div class="d-flex align-items-center">
            <div class="avatar avatar-md me-3 flex-shrink-0">
                @if($supir->foto)
                    <img src="{{ asset('storage/' . $supir->foto) }}" alt="Avatar" class="rounded-circle object-fit-cover shadow-sm border" style="width: 40px; height: 40px;">
                @else
                    <span class="avatar-initial rounded-circle bg-label-{{ $supir->isPengurusGedung() ? 'success' : 'primary' }} shadow-sm border fw-bold">{{ strtoupper(substr($supir->nama, 0, 1)) }}</span>
                @endif
            </div>
            <div>
                <h6 class="mb-0 fw-bold">{{ $supir->nama }}</h6>
                <small class="text-muted">ID: #{{ str_pad($supir->id, 4, '0', STR_PAD_LEFT) }}</small>
            </div>
        </div>
    </td>
    <td>
        <div class="d-flex align-items-center mb-1">
            <i class="bx bxl-whatsapp text-success me-1"></i> 
            <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $supir->kontak ?? '') }}" target="_blank" class="fw-semibold text-dark text-decoration-none">
                {{ $supir->kontak ?? '-' }}
            </a>
        </div>
        @if($supir->user_id)
            <span class="badge bg-label-info d-inline-flex align-items-center" style="font-size: 0.7rem;"><i class="bx bx-link me-1"></i> Tertaut: {{ $supir->user->name }}</span>
        @else
            <span class="badge bg-label-secondary d-inline-flex align-items-center" style="font-size: 0.7rem;" title="Tautkan ke akun warga agar personil bisa menerima notifikasi"><i class="bx bx-unlink me-1"></i> Belum tertaut akun</span>
        @endif
    </td>
    <td>
        <div class="d-flex flex-column gap-1">
            @if($supir->isPengurusGedung())
                <span class="badge bg-label-success w-100 text-start"><i class="bx bx-building-house me-1"></i> Pengurus / Pemegang Kunci</span>
                @if($supir->fasilitas && $supir->fasilitas->count() > 0)
                    <small class="text-muted" style="font-size: 0.72rem;">Ditugaskan: {{ $supir->fasilitas->pluck('nama_fasilitas')->join(', ') }}</small>
                @endif
            @else
                @if($supir->is_sewa_mobil)
                    <span class="badge bg-label-primary w-100 text-start"><i class="bx bx-car me-1"></i> Rental Mobil</span>
                @endif
                @if($supir->is_fasilitas_umum)
                    <span class="badge bg-label-danger w-100 text-start"><i class="bx bx-plus-medical me-1"></i> Ambulans & Kendaraan</span>
                @endif
                @if(!$supir->is_sewa_mobil && !$supir->is_fasilitas_umum)
                    <span class="text-muted fst-italic" style="font-size: 0.8rem;">Belum ada kategori</span>
                @endif
            @endif
        </div>
    </td>
    <td>
        @if($supir->status == 'Tersedia')
            <span class="badge bg-label-success"><i class="bx bx-check-circle me-1"></i> Tersedia</span>
        @elseif($supir->status == 'Sedang Bertugas')
            <span class="badge bg-label-warning"><i class="bx bx-run me-1"></i> Bertugas</span>
        @else
            <span class="badge bg-label-secondary"><i class="bx bx-minus-circle me-1"></i> Tidak Aktif</span>
        @endif
    </td>
    <td class="text-center pe-4">
        <button type="button" class="btn btn-sm btn-icon btn-outline-secondary me-1 shadow-none" data-bs-toggle="modal" data-bs-target="#detailSupirModal{{ $supir->id }}" title="Lihat Detail Profil">
            <i class="bx bx-show"></i>
        </button>
        <button type="button" class="btn btn-sm btn-icon btn-outline-primary me-1 shadow-none" data-bs-toggle="modal" data-bs-target="#editSupirModal{{ $supir->id }}" title="Edit Data">
            <i class="bx bx-edit-alt"></i>
        </button>
        <form action="{{ route('supir.destroy', $supir->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data ini?');">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-sm btn-icon btn-outline-danger shadow-none" title="Hapus Data">
                <i class="bx bx-trash"></i>
            </button>
        </form>
    </td>
</tr>
@empty
<tr>
    <td colspan="5" class="text-center py-5">
        <div class="empty-state">
            <i class="bx {{ ($tab ?? 'supir') === 'pengurus_gedung' ? 'bx-building-house' : 'bx-user-x' }} fs-1 text-muted mb-3 d-block"></i>
            <h6>Belum ada data {{ ($tab ?? 'supir') === 'pengurus_gedung' ? 'pengurus atau pemegang kunci gedung' : 'supir kendaraan' }}.</h6>
            <p class="text-muted mb-0">Klik tombol "{{ ($tab ?? 'supir') === 'pengurus_gedung' ? 'Tambah Pengurus Gedung' : 'Tambah Supir Baru' }}" di sudut kanan atas untuk mulai.</p>
        </div>
    </td>
</tr>
@endforelse
