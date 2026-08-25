@extends('admin.layouts.admin')
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
