@extends('admin.layouts.admin')

@section('title', 'Ketentuan SOP - ' . $unitLabel)

@include('admin.unit.partials.unit_product_styles')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    {{-- Page Header --}}
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-3 mb-4">
        <div class="min-w-0">
            <h4 class="fw-bold mb-1 text-dark">
                <span class="text-muted fw-normal">
                    <a href="{{ $backUrl }}" class="text-muted text-decoration-none">{{ $unitLabel }}</a> /
                </span>
                Ketentuan SOP
            </h4>
            <p class="text-muted mb-0 small">{{ $subtitle }}</p>
        </div>
        <a href="{{ $backUrl }}"
           class="segi-back btn btn-outline-secondary rounded-pill px-4 w-100 w-sm-auto d-inline-flex align-items-center justify-content-center gap-1">
            <i class="bx bx-arrow-back"></i>
            <span>Kembali</span>
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible border-0 shadow-sm rounded-4 d-flex align-items-center mb-4" role="alert">
            <i class="bx bx-check-circle fs-4 me-2 flex-shrink-0"></i>
            <div>{{ session('success') }}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
        </div>
    @endif

    @if(auth()->user()->isStaff())
        <div class="alert alert-info border-0 shadow-sm rounded-4 d-flex align-items-center gap-2 mb-4" role="alert">
            <i class="bx bx-info-circle fs-4 flex-shrink-0"></i>
            <span class="small mb-0">Ketentuan layanan ditetapkan admin desa. Staf dapat membacanya di sini.</span>
        </div>
    @endif

    {{-- Panduan Banner --}}
    <div class="card border-0 bg-label-primary rounded-4 mb-4">
        <div class="card-body p-3 p-md-4 d-flex align-items-start gap-3">
            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 shadow-sm" style="width: 42px; height: 42px;">
                <i class="bx bx-book-content fs-4"></i>
            </div>
            <div>
                <h6 class="fw-bold text-primary mb-1">Pilih Ketentuan SOP yang Diberlakukan</h6>
                <p class="mb-0 text-primary small" style="opacity: 0.92; line-height: 1.55;">
                    {{ $bannerText }} Anda dapat mengubah isi ketentuan sesuai aturan desa atau mengembalikannya ke teks bawaan kapan saja.
                </p>
            </div>
        </div>
    </div>

    <form action="{{ $updateAction }}" method="POST" class="up-form">
        @csrf
        <fieldset @disabled(auth()->user()->isStaff())>
            <div class="row g-4 mb-4">
                {{-- Opsi A: Ditanggung Pengguna --}}
                <div class="col-12 col-lg-6">
                    <div class="card up-sop-card sop-card {{ $sop_active == 'ditanggung' ? 'active-sop' : '' }}">
                        <div class="up-card-header d-flex justify-content-between align-items-center gap-2">
                            <div class="form-check d-flex align-items-center gap-2 m-0">
                                <input class="form-check-input m-0" type="radio"
                                       name="{{ $activeField }}"
                                       id="sop_active_ditanggung"
                                       value="ditanggung"
                                       @checked($sop_active == 'ditanggung')>
                                <label class="form-check-label fw-bold text-dark" for="sop_active_ditanggung" style="cursor: pointer;">
                                    Ditanggung Pengguna
                                </label>
                            </div>
                            <span class="badge {{ $sop_active == 'ditanggung' ? 'bg-primary' : 'bg-label-secondary' }} rounded-pill px-2.5 py-1 sop-status-badge" data-for="ditanggung" style="font-size: 0.7rem;">
                                {{ $sop_active == 'ditanggung' ? 'Aktif' : 'Tidak Aktif' }}
                            </span>
                        </div>
                        <div class="card-body p-3 p-md-4 d-flex flex-column">
                            <p class="text-muted small mb-3">{{ $descDitanggung }}</p>
                            <div class="mb-3 flex-grow-1">
                                <textarea data-terms-editor
                                          class="form-control"
                                          name="{{ $ditanggungField }}"
                                          id="sop_ditanggung_text"
                                          rows="8">{{ old($ditanggungField, $sop_ditanggung) }}</textarea>
                            </div>
                            <div>
                                <button type="button" class="btn btn-sm btn-outline-warning rounded-pill px-3" onclick="resetSop('ditanggung')">
                                    <i class="bx bx-reset me-1"></i>Reset ke Bawaan
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Opsi B: Tidak Ditanggung Pengguna --}}
                <div class="col-12 col-lg-6">
                    <div class="card up-sop-card sop-card {{ $sop_active == 'tidak_ditanggung' ? 'active-sop' : '' }}">
                        <div class="up-card-header d-flex justify-content-between align-items-center gap-2">
                            <div class="form-check d-flex align-items-center gap-2 m-0">
                                <input class="form-check-input m-0" type="radio"
                                       name="{{ $activeField }}"
                                       id="sop_active_tidak_ditanggung"
                                       value="tidak_ditanggung"
                                       @checked($sop_active == 'tidak_ditanggung')>
                                <label class="form-check-label fw-bold text-dark" for="sop_active_tidak_ditanggung" style="cursor: pointer;">
                                    Tidak Ditanggung Pengguna
                                </label>
                            </div>
                            <span class="badge {{ $sop_active == 'tidak_ditanggung' ? 'bg-primary' : 'bg-label-secondary' }} rounded-pill px-2.5 py-1 sop-status-badge" data-for="tidak_ditanggung" style="font-size: 0.7rem;">
                                {{ $sop_active == 'tidak_ditanggung' ? 'Aktif' : 'Tidak Aktif' }}
                            </span>
                        </div>
                        <div class="card-body p-3 p-md-4 d-flex flex-column">
                            <p class="text-muted small mb-3">{{ $descTidakDitanggung }}</p>
                            <div class="mb-3 flex-grow-1">
                                <textarea data-terms-editor
                                          class="form-control"
                                          name="{{ $tidakDitanggungField }}"
                                          id="sop_tidak_ditanggung_text"
                                          rows="8">{{ old($tidakDitanggungField, $sop_tidak_ditanggung) }}</textarea>
                            </div>
                            <div>
                                <button type="button" class="btn btn-sm btn-outline-warning rounded-pill px-3" onclick="resetSop('tidak_ditanggung')">
                                    <i class="bx bx-reset me-1"></i>Reset ke Bawaan
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card up-card">
                <div class="card-body p-3 p-md-4 d-flex flex-column flex-sm-row justify-content-between align-items-stretch align-items-sm-center gap-2">
                    <small class="text-muted">Perubahan SOP langsung berlaku pada pemesanan warga berikutnya.</small>
                    <div class="d-flex flex-column flex-sm-row gap-2">
                        <a href="{{ $backUrl }}" class="btn btn-outline-secondary rounded-pill px-4">Kembali</a>
                        @unless(auth()->user()->isStaff())
                            <button type="submit" class="btn btn-primary rounded-pill px-4 fw-semibold shadow-sm">
                                <i class="bx bx-save me-1"></i>Simpan Ketentuan SOP
                            </button>
                        @endunless
                    </div>
                </div>
            </div>
        </fieldset>
    </form>
</div>
@endsection

@push('scripts')
<script>
    const defaultSops = {
        'ditanggung': @json($default_ditanggung),
        'tidak_ditanggung': @json($default_tidak_ditanggung)
    };

    function resetSop(type) {
        konfirmasi({
            judul: 'Reset Teks SOP',
            pesan: 'Teks SOP akan dikembalikan ke versi bawaan. Perubahan yang belum disimpan akan hilang.',
            jenis: 'bahaya',
            tombolYa: 'Ya, Reset'
        }).then(function (setuju) {
            if (setuju) {
                const source = document.getElementById('sop_' + type + '_text');
                if (source) {
                    source.value = defaultSops[type];
                    source.dispatchEvent(new Event('change'));
                }
            }
        });
    }

    document.querySelectorAll('input[name="{{ $activeField }}"]').forEach(function (radio) {
        radio.addEventListener('change', function () {
            document.querySelectorAll('.sop-card').forEach(function (card) {
                card.classList.remove('active-sop');
            });
            document.querySelectorAll('.sop-status-badge').forEach(function (badge) {
                const isThis = badge.getAttribute('data-for') === radio.value;
                badge.textContent = isThis ? 'Aktif' : 'Tidak Aktif';
                badge.className = 'badge rounded-pill px-2.5 py-1 sop-status-badge ' + (isThis ? 'bg-primary' : 'bg-label-secondary');
            });
            if (this.checked) {
                this.closest('.sop-card')?.classList.add('active-sop');
            }
        });
    });
</script>
<script src="{{ asset('js/service-terms-editor.js') }}?v={{ filemtime(public_path('js/service-terms-editor.js')) }}" defer></script>
@endpush
