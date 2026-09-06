@extends('admin.layouts.admin')

@section('title', 'Detail Verifikasi Identitas - ' . $kyc->user->name)

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-dark">Detail Verifikasi Identitas</h1>
        <a href="{{ route('admin.kyc.index') }}" class="btn btn-secondary btn-sm">
            <i class="bx bx-arrow-back"></i> Kembali
        </a>
    </div>

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="row">
        <!-- Foto KTP -->
        <div class="col-xl-5 col-lg-5">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 fw-bold text-primary">Foto KTP</h6>
                </div>
                <div class="card-body text-center">
                    @if($kyc->ktp_image_path)
                        <img src="{{ route('media.secure.ktp', basename($kyc->ktp_image_path)) }}" class="img-fluid rounded border p-1 mb-3" alt="KTP {{ $kyc->user->name }}">
                        <a href="{{ route('media.secure.ktp', basename($kyc->ktp_image_path)) }}" target="_blank" class="btn btn-sm btn-outline-primary"><i class="bx bx-zoom-in"></i> Perbesar</a>
                    @else
                        <div class="alert alert-warning">Foto KTP tidak tersedia.</div>
                    @endif
                </div>
            </div>

            <div class="card shadow mb-4">
                <div class="card-header py-3">

                    @if($kyc->face_scan_data)
                        <div class="alert alert-success text-start">
                            <i class="bx bx-check-circle"></i> Data Liveness terdeteksi 
                            ({{ count($kyc->face_scan_data) }} frame terekam).
                        </div>
                        <p class="small text-muted text-start">Sistem otomatis mendeteksi kedipan mata dan gerakan kepala sebelum mengirimkan form ini.</p>
                    @else
                        <div class="alert alert-danger text-start">
                            <i class="bx bx-x-circle"></i> Tidak ada data liveness.
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Data Perbandingan -->
        <div class="col-xl-7 col-lg-7">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 fw-bold text-primary">Hasil Ekstraksi OCR KTP</h6>
                    @if($kyc->status === 'pending')
                        <span class="badge bg-warning p-2">Menunggu Persetujuan</span>
                    @elseif($kyc->status === 'approved')
                        <span class="badge bg-success p-2">Disetujui</span>
                    @else
                        <span class="badge bg-danger p-2">Ditolak</span>
                    @endif
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <thead class="thead-light">
                            <tr>
                                <th>Field</th>
                                <th>Data Akun (Awal)</th>
                                <th>Data KTP (OCR)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <th>NIK</th>
                                <td>{{ $kyc->user->nik ?? '-' }}</td>
                                <td class="fw-bold text-primary">{{ $kyc->nik_from_ocr ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>Nama Lengkap</th>
                                <td>{{ $kyc->user->name }}</td>
                                <td class="fw-bold text-primary">{{ $kyc->name_from_ocr ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>Jenis Kelamin</th>
                                <td>{{ $kyc->user->gender }}</td>
                                <td class="fw-bold text-primary">{{ ucfirst($kyc->gender_from_ocr) ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>Alamat</th>
                                <td>{{ $kyc->user->address }}</td>
                                <td class="fw-bold text-primary">
                                    {{ $kyc->address_from_ocr ?? '-' }}<br>
                                    <small>RT {{ $kyc->rt_from_ocr ?? '-' }} / RW {{ $kyc->rw_from_ocr ?? '-' }}</small>
                                </td>
                            </tr>
                            <tr>
                                <th>Desa/Kelurahan</th>
                                <td>{{ $kyc->user->region->name ?? '-' }}</td>
                                <td class="fw-bold text-primary">{{ $kyc->desa_from_ocr ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>Kecamatan</th>
                                <td>-</td>
                                <td class="fw-bold text-primary">{{ $kyc->kecamatan_from_ocr ?? '-' }}</td>
                            </tr>
                        </tbody>
                    </table>

                    @if($kyc->status === 'pending')
                    <div class="mt-4 border-top pt-4">
                        <h6 class="fw-bold mb-3">Tindakan Persetujuan</h6>

                        <form action="{{ route('admin.kyc.approve', $kyc->id) }}" method="POST" class="d-inline" id="form-approve">
                            @csrf
                            <input type="hidden" name="admin_notes" id="approve_notes" value="Data Sesuai">
                            <button type="button" class="btn btn-success" onclick="confirmApprove()">
                                <i class="bx bx-check"></i> Setujui &amp; Update Profil
                            </button>
                        </form>

                        {{-- data-bs-toggle, bukan data-toggle: Bootstrap yang dimuat
                             tema ini versi 5.1.3, dan BS5 tidak lagi mengenali
                             atribut data-toggle/data-target milik BS4. Tombol ini
                             karena itu tidak pernah membuka apa pun. --}}
                        <button type="button" class="btn btn-danger ms-2" data-bs-toggle="modal" data-bs-target="#rejectModal">
                            <i class="bx bx-x"></i> Tolak
                        </button>

                        <p class="small text-muted mt-2">
                            * Jika disetujui, data profil pengguna akan ditimpa dengan data hasil OCR KTP.
                        </p>
                    </div>
                    @else
                    <div class="mt-4 border-top pt-4">
                        <h6 class="fw-bold">Catatan Admin:</h6>
                        <p>{{ $kyc->admin_notes ?? 'Tidak ada catatan.' }}</p>
                        <small class="text-muted">Ditinjau oleh: {{ $kyc->reviewer->name ?? 'Admin' }} pada {{ $kyc->reviewed_at ? $kyc->reviewed_at->format('d M Y H:i') : '-' }}</small>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

{{-- Modal Tolak.

     WAJIB lewat stack ini, bukan di dalam @section('content'). Isi content
     berada di dalam .layout-page yang diberi `animation: pageFadeIn ... forwards`
     oleh layout. Animasi pada properti transform menjadikan elemen itu
     containing block bagi keturunan position:fixed, sehingga .modal terkurung
     di dalamnya sementara .modal-backdrop tetap ditempel Bootstrap ke <body>.
     Hasilnya backdrop menimpa dialognya dan halaman tampak beku.
     @stack('modals') dirender di luar layout-wrapper, jadi aman. --}}
@push('modals')
<div class="modal fade" id="rejectModal" tabindex="-1" aria-labelledby="rejectModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="{{ route('admin.kyc.reject', $kyc->id) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="rejectModalLabel">Tolak Verifikasi Identitas</h5>
                    {{-- btn-close + data-bs-dismiss: penulisan Bootstrap 5.
                         Sebelumnya class="close" dengan data-dismiss milik BS4,
                         jadi tombol tutupnya pun tidak berfungsi. --}}
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label" for="admin_notes">Alasan Penolakan <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="admin_notes" name="admin_notes" rows="3" required placeholder="Contoh: Foto KTP buram, wajah tidak cocok dengan KTP, dll."></textarea>
                    </div>
                    <p class="small text-muted mb-0">Pengguna akan menerima notifikasi penolakan via WhatsApp beserta alasan ini.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger">Tolak &amp; Kirim Notifikasi</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endpush

@push('scripts')
<script>
    function confirmApprove() {
        konfirmasi({
            judul: 'Setujui Verifikasi',
            pesan: 'Data profil pengguna akan otomatis diperbarui sesuai KTP, dan foto KTP serta wajah dihapus permanen.',
            jenis: 'peringatan',
            tombolYa: 'Ya, Setujui'
        }).then(function (setuju) {
            if (setuju) document.getElementById('form-approve').submit();
        });
    }
</script>
@endpush
