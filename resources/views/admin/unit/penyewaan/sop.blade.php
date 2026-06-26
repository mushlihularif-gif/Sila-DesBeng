@extends('admin.layouts.admin')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Unit Layanan / Penyewaan Alat /</span> Ketentuan SOP</h4>

        <div class="row">
            <div class="col-md-12">
                <div class="card mb-4">
                    <h5 class="card-header">Manajemen Ketentuan SOP Penyewaan</h5>
                    <div class="card-body">
                        <div class="alert alert-danger mt-3 text-dark">
                            <h6 class="alert-heading fw-bold mb-2"><i class="bx bx-error me-1"></i> PENTING</h6>
                            <p class="mb-0">Pilih salah satu dari opsi SOP di bawah ini yang akan diberlakukan kepada pengguna saat mereka menyewa alat. Anda dapat mengubah isi teks sesuai kebutuhan, atau mengembalikannya ke pengaturan bawaan jika terjadi kesalahan.</p>
                        </div>
                        
                        <form action="{{ route('admin.unit.penyewaan.sop.update') }}" method="POST">
                            @csrf
                            
                            <style>
                                .sop-card {
                                    transition: all 0.2s ease-in-out;
                                    border: 2px solid #ffab00 !important;
                                    background-color: #fff3cd !important;
                                }
                                .sop-card.active-sop {
                                    border-width: 2px !important;
                                    box-shadow: 0 0.25rem 1rem rgba(255, 171, 0, 0.4) !important;
                                }
                                .sop-icon {
                                    color: #ffab00;
                                    font-size: 1.25rem;
                                    vertical-align: middle;
                                }
                            </style>

                            <div class="row mb-4">
                                <!-- Opsi A: Ditanggung -->
                                <div class="col-md-6 mb-3">
                                    <div class="card sop-card {{ $sop_active == 'ditanggung' ? 'active-sop' : '' }} h-100">
                                        <div class="card-header d-flex justify-content-between align-items-center pb-2">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="sop_penyewaan_active" id="sop_active_ditanggung" value="ditanggung" {{ $sop_active == 'ditanggung' ? 'checked' : '' }}>
                                                <label class="form-check-label fw-bold text-dark" for="sop_active_ditanggung">
                                                    <i class="bx bx-error sop-icon"></i> <span class="align-middle">PENTING: Ditanggung Pengguna</span>
                                                </label>
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            <p class="text-muted small mb-2">Kerusakan atau kehilangan alat menjadi tanggung jawab pengguna.</p>
                                            
                                            <div class="mb-3">
                                                <textarea class="form-control" name="sop_penyewaan_ditanggung" id="sop_ditanggung_text" rows="8">{{ old('sop_penyewaan_ditanggung', $sop_ditanggung) }}</textarea>
                                            </div>
                                            
                                            <button type="button" class="btn btn-sm btn-outline-warning" onclick="resetSop('ditanggung')">
                                                <i class="bx bx-reset"></i> Reset ke Bawaan
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Opsi B: Tidak Ditanggung -->
                                <div class="col-md-6 mb-3">
                                    <div class="card sop-card {{ $sop_active == 'tidak_ditanggung' ? 'active-sop' : '' }} h-100">
                                        <div class="card-header d-flex justify-content-between align-items-center pb-2">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="sop_penyewaan_active" id="sop_active_tidak_ditanggung" value="tidak_ditanggung" {{ $sop_active == 'tidak_ditanggung' ? 'checked' : '' }}>
                                                <label class="form-check-label fw-bold text-dark" for="sop_active_tidak_ditanggung">
                                                    <i class="bx bx-error sop-icon"></i> <span class="align-middle">PENTING: Tidak Ditanggung Pengguna</span>
                                                </label>
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            <p class="text-muted small mb-2">Kerusakan tidak disengaja ditanggung oleh Dana Operasional.</p>
                                            
                                            <div class="mb-3">
                                                <textarea class="form-control" name="sop_penyewaan_tidak_ditanggung" id="sop_tidak_ditanggung_text" rows="8">{{ old('sop_penyewaan_tidak_ditanggung', $sop_tidak_ditanggung) }}</textarea>
                                            </div>
                                            
                                            <button type="button" class="btn btn-sm btn-outline-warning" onclick="resetSop('tidak_ditanggung')">
                                                <i class="bx bx-reset"></i> Reset ke Bawaan
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mt-4">
                                <button type="submit" class="btn btn-primary me-2">Simpan Ketentuan</button>
                                <a href="{{ route('admin.unit.penyewaan.index') }}" class="btn btn-outline-secondary">Kembali</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    // Menyimpan teks default dari server
    const defaultSops = {
        'ditanggung': @json($default_ditanggung),
        'tidak_ditanggung': @json($default_tidak_ditanggung)
    };

    function resetSop(type) {
        if (confirm('Apakah Anda yakin ingin mereset teks SOP ini ke versi bawaan?')) {
            document.getElementById('sop_' + type + '_text').value = defaultSops[type];
        }
    }

    // Ubah style card jika radio button diklik
    document.querySelectorAll('input[name="sop_penyewaan_active"]').forEach(function(radio) {
        radio.addEventListener('change', function() {
            // Reset semua border card
            document.querySelectorAll('.sop-card').forEach(function(card) {
                card.classList.remove('active-sop');
            });
            // Set active class ke card yang terpilih
            if(this.checked) {
                this.closest('.sop-card').classList.add('active-sop');
            }
        });
    });
</script>
@endpush
