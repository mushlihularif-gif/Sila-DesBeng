@extends('layouts.admin')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold py-3 mb-0">
            <span class="text-muted fw-light">Warga /</span> Verifikasi Identitas
        </h4>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <div class="card">
        <div class="card-header border-bottom">
            <h5 class="card-title mb-0">Antrean Verifikasi KTP & Wajah</h5>
        </div>
        
        <div class="table-responsive text-nowrap">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Nama & NIK</th>
                        <th>Kontak</th>
                        <th>Alamat</th>
                        <th>Foto Wajah (Selfie)</th>
                        <th>Foto KTP</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody class="table-border-bottom-0">
                    @forelse($pendingUsers as $u)
                    <tr>
                        <td>
                            <strong>{{ $u->name }}</strong><br>
                            <span class="text-muted text-sm">NIK: {{ $u->nik ?? 'Belum diisi' }}</span>
                        </td>
                        <td>
                            <i class='bx bx-envelope'></i> {{ $u->email }}<br>
                            <i class='bx bx-phone'></i> {{ $u->phone }}
                        </td>
                        <td>
                            RT {{ $u->rt }} / RW {{ $u->rw }}<br>
                            <span class="text-xs text-muted" style="max-width:200px; white-space:pre-wrap; display:block;">{{ $u->address }}</span>
                        </td>
                        <td>
                            @if($u->face_photo_path)
                            <a href="#" data-bs-toggle="modal" data-bs-target="#imgModal" onclick="showImage('{{ route('admin.warga.verifikasi.image', ['type' => 'face', 'id' => $u->id]) }}')">
                                <img src="{{ route('admin.warga.verifikasi.image', ['type' => 'face', 'id' => $u->id]) }}" alt="Wajah" class="rounded" style="width: 80px; height: 80px; object-fit: cover; border: 2px solid #ddd;">
                            </a>
                            @else
                            <span class="badge bg-label-warning">Kosong</span>
                            @endif
                        </td>
                        <td>
                            @if($u->ktp_photo_path)
                            <a href="#" data-bs-toggle="modal" data-bs-target="#imgModal" onclick="showImage('{{ route('admin.warga.verifikasi.image', ['type' => 'ktp', 'id' => $u->id]) }}')">
                                <img src="{{ route('admin.warga.verifikasi.image', ['type' => 'ktp', 'id' => $u->id]) }}" alt="KTP" class="rounded" style="width: 120px; height: 80px; object-fit: cover; border: 2px solid #ddd;">
                            </a>
                            @else
                            <span class="badge bg-label-warning">Kosong</span>
                            @endif
                        </td>
                        <td>
                            <div class="d-flex gap-2">
                                <form action="{{ route('admin.warga.verifikasi.approve', $u->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Apakah Anda yakin data ini valid dan menyetujuinya?')">
                                        <i class='bx bx-check'></i> Setujui
                                    </button>
                                </form>
                                <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#rejectModal{{ $u->id }}">
                                    <i class='bx bx-x'></i> Tolak
                                </button>
                            </div>

                            <!-- Reject Modal -->
                            <div class="modal fade" id="rejectModal{{ $u->id }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Tolak Verifikasi: {{ $u->name }}</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <form action="{{ route('admin.warga.verifikasi.reject', $u->id) }}" method="POST">
                                            @csrf
                                            <div class="modal-body">
                                                <p>Silakan berikan alasan spesifik mengapa foto ini ditolak agar warga dapat memperbaikinya.</p>
                                                <div class="mb-3">
                                                    <label class="form-label">Alasan Penolakan <span class="text-danger">*</span></label>
                                                    <select name="reason" class="form-select mb-2" onchange="if(this.value === 'Lainnya'){ this.nextElementSibling.style.display='block'; this.nextElementSibling.required=true; } else { this.nextElementSibling.style.display='none'; this.nextElementSibling.required=false; this.nextElementSibling.value=this.value; }">
                                                        <option value="">Pilih Alasan Umum...</option>
                                                        <option value="Foto KTP terlalu silau/buram, tulisan tidak terbaca.">Foto KTP terlalu silau/buram, tulisan tidak terbaca.</option>
                                                        <option value="Foto wajah tidak cocok dengan KTP.">Foto wajah tidak cocok dengan KTP.</option>
                                                        <option value="KTP tampak seperti editan/palsu.">KTP tampak seperti editan/palsu.</option>
                                                        <option value="Bukan KTP (SIM/KK/Dokumen lain).">Bukan KTP (SIM/KK/Dokumen lain).</option>
                                                        <option value="Lainnya">Lainnya (Ketik sendiri)</option>
                                                    </select>
                                                    <textarea name="reason" class="form-control mt-2" rows="2" style="display:none;" placeholder="Ketik alasan spesifik..."></textarea>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                                                <button type="submit" class="btn btn-danger">Tolak & Hapus Foto</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-5">
                            <i class='bx bx-check-shield text-muted' style="font-size: 3rem;"></i>
                            <p class="mt-2 text-muted">Semua warga telah diverifikasi. Antrean kosong.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="mt-3 px-4">
            {{ $pendingUsers->links() }}
        </div>
    </div>
</div>

<!-- Image Preview Modal -->
<div class="modal fade" id="imgModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content bg-transparent border-0 shadow-none">
            <div class="modal-header border-0">
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center p-0">
                <img id="modalPreviewImg" src="" class="img-fluid rounded shadow-lg protected-image" style="max-height: 85vh;">
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    /* Proteksi gambar: cegah drag, save, dan seleksi */
    .protected-image,
    td img {
        -webkit-user-select: none;
        -moz-user-select: none;
        -ms-user-select: none;
        user-select: none;
        -webkit-user-drag: none;
        pointer-events: auto;
        -webkit-touch-callout: none;
    }
</style>
@endpush

@push('scripts')
<script>
    function showImage(src) {
        document.getElementById('modalPreviewImg').src = src;
    }

    // Cegah klik kanan pada gambar KTP/Selfie
    document.addEventListener('contextmenu', function(e) {
        if (e.target.tagName === 'IMG') {
            e.preventDefault();
            alert('⚠️ PERINGATAN KEAMANAN\n\nAnda tidak diizinkan menyimpan atau menyalin foto identitas warga.\nSeluruh aktivitas Anda pada halaman ini tercatat dalam sistem audit.');
            return false;
        }
    });

    // Cegah drag gambar
    document.addEventListener('dragstart', function(e) {
        if (e.target.tagName === 'IMG') {
            e.preventDefault();
            return false;
        }
    });

    // Peringatan jika mendeteksi PrintScreen (hanya edukasi, tidak bisa benar-benar mencegah)
    document.addEventListener('keyup', function(e) {
        if (e.key === 'PrintScreen') {
            alert('⚠️ PERINGATAN KEAMANAN\n\nScreenshot terdeteksi!\nSeluruh foto identitas sudah dilindungi watermark.\nPenyalahgunaan data warga adalah pelanggaran hukum (UU PDP No. 27/2022).');
        }
    });
</script>
@endpush
@endsection

