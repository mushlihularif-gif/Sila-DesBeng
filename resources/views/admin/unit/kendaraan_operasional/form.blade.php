@extends('admin.layouts.admin')

@section('title', ($kendaraan->exists ? 'Ubah' : 'Tambah') . ' Kendaraan Operasional')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    {{-- Page Header --}}
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-3 mb-4">
        <div>
            <h4 class="fw-bold mb-1 text-dark">
                <span class="text-muted fw-normal">Kendaraan Operasional /</span> {{ $kendaraan->exists ? 'Ubah Data' : 'Tambah Baru' }}
            </h4>
            <p class="text-muted mb-0 small">
                Lengkapi informasi kendaraan, foto dokumentasi, dan supir penanggung jawab.
            </p>
        </div>
        <a href="{{ route('admin.unit.kendaraan_operasional.index') }}" class="segi-back btn btn-outline-secondary rounded-pill px-4 w-100 w-sm-auto d-inline-flex align-items-center justify-content-center gap-1">
            <i class="bx bx-arrow-back"></i>
            <span>Kembali</span>
        </a>
    </div>

    @if($errors->any())
        <div class="alert alert-danger border-0 shadow-sm rounded-4 mb-4" role="alert">
            <div class="d-flex align-items-start gap-2">
                <i class="bx bx-error-circle fs-4 mt-0.5 flex-shrink-0"></i>
                <div>
                    <strong class="d-block mb-1">Periksa kembali data yang dimasukkan:</strong>
                    <ul class="mb-0 ps-3 small">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    <form method="POST" enctype="multipart/form-data"
          action="{{ $kendaraan->exists ? route('admin.unit.kendaraan_operasional.update', $kendaraan->id) : route('admin.unit.kendaraan_operasional.store') }}">
        @csrf
        @if($kendaraan->exists) @method('PUT') @endif

        <div class="row g-4">
            <div class="col-12 col-lg-8">
                {{-- Informasi Dasar --}}
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-header bg-white border-bottom px-4 py-3 d-flex align-items-center gap-2.5">
                        <div class="d-flex align-items-center justify-content-center rounded-3 bg-label-primary" style="width: 36px; height: 36px;">
                            <i class="bx bx-car fs-5 text-primary"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold text-dark mb-0">Informasi Kendaraan</h6>
                            <small class="text-muted">Identitas dan keterangan kendaraan operasional</small>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3">
                            <div class="col-12 col-md-8">
                                <label for="nama_mobil" class="form-label fw-semibold small text-dark">
                                    Nama Kendaraan <span class="text-danger">*</span>
                                </label>
                                <input id="nama_mobil" name="nama_mobil" class="form-control rounded-3" required maxlength="255"
                                       placeholder="Contoh: Mobil Pickup Operasional Desa"
                                       value="{{ old('nama_mobil', $kendaraan->nama_mobil) }}">
                            </div>
                            <div class="col-12 col-md-4">
                                <label for="nomor_plat" class="form-label fw-semibold small text-dark">Nomor Polisi</label>
                                <input id="nomor_plat" name="nomor_plat" class="form-control rounded-3" maxlength="20"
                                       placeholder="BM 1234 XX"
                                       value="{{ old('nomor_plat', $kendaraan->plat_nomor) }}">
                            </div>
                            <div class="col-12">
                                <label for="deskripsi" class="form-label fw-semibold small text-dark">Keterangan Kendaraan</label>
                                <textarea id="deskripsi" name="deskripsi" class="form-control rounded-3" rows="3" maxlength="5000"
                                          placeholder="Tuliskan kondisi, kegunaan, atau catatan operasional kendaraan...">{{ old('deskripsi', $kendaraan->deskripsi) }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Foto Dokumentasi --}}
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-header bg-white border-bottom px-4 py-3 d-flex align-items-center gap-2.5">
                        <div class="d-flex align-items-center justify-content-center rounded-3 bg-label-info" style="width: 36px; height: 36px;">
                            <i class="bx bx-image fs-5 text-info"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold text-dark mb-0">Foto Kendaraan</h6>
                            <small class="text-muted">Format JPG, PNG, atau WebP &bull; Maks. 8MB per foto</small>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3">
                            @foreach(['foto' => 'Foto Utama', 'foto_2' => 'Foto Tambahan 1', 'foto_3' => 'Foto Tambahan 2'] as $field => $label)
                                <div class="col-12 col-md-4">
                                    <div class="p-3 rounded-3 border bg-light bg-opacity-50 h-100">
                                        <label for="{{ $field }}" class="form-label fw-semibold small text-dark">{{ $label }}</label>
                                        @if($kendaraan->$field)
                                            <img src="{{ asset('storage/' . $kendaraan->$field) }}" alt="{{ $label }} kendaraan"
                                                 class="d-block rounded-3 mb-2 border bg-white w-100" style="height: 140px; object-fit: cover;">
                                            <div class="form-check mb-2">
                                                <input type="checkbox" class="form-check-input" id="del_{{ $field }}" name="delete_{{ $field }}" value="1" @checked(old('delete_' . $field))>
                                                <label class="form-check-label small text-danger" for="del_{{ $field }}">Hapus foto ini</label>
                                            </div>
                                        @endif
                                        <input id="{{ $field }}" name="{{ $field }}" type="file" accept="image/jpeg,image/png,image/webp" class="form-control form-control-sm rounded-3">
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            {{-- Kolom Kanan: Supir & Simpan --}}
            <div class="col-12 col-lg-4">
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-header bg-white border-bottom px-4 py-3 d-flex align-items-center gap-2.5">
                        <div class="d-flex align-items-center justify-content-center rounded-3 bg-label-success" style="width: 36px; height: 36px;">
                            <i class="bx bx-user-check fs-5 text-success"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold text-dark mb-0">Supir Penanggung Jawab</h6>
                            <small class="text-muted">Pilih satu atau lebih supir desa</small>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        @php($selectedDrivers = old('supir_ids', $kendaraan->exists ? $kendaraan->supirs->modelKeys() : []))
                        @forelse($supirs as $supir)
                            <label class="d-flex align-items-start gap-2.5 p-2.5 rounded-3 border mb-2 bg-light bg-opacity-50" style="cursor: pointer;">
                                <input type="checkbox" class="form-check-input mt-0.5 flex-shrink-0" name="supir_ids[]" value="{{ $supir->id }}" @checked(in_array($supir->id, $selectedDrivers))>
                                <span class="min-w-0">
                                    <span class="fw-semibold text-dark small d-block">{{ $supir->nama }}</span>
                                    <small class="text-muted d-block" style="font-size: 0.72rem;">{{ $supir->kontak ?: 'Kontak belum diisi' }}</small>
                                </span>
                            </label>
                        @empty
                            <div class="text-center py-3">
                                <i class="bx bx-user-x fs-2 text-muted opacity-50 d-block mb-1"></i>
                                <p class="text-muted small mb-0">Belum ada supir terdaftar. Tambahkan melalui menu <strong>Data Supir &amp; Petugas</strong>.</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body p-4">
                        <button type="submit" class="btn btn-primary rounded-pill w-100 fw-semibold shadow-sm mb-2">
                            <i class="bx bx-save me-1"></i>Simpan Kendaraan
                        </button>
                        <a href="{{ route('admin.unit.kendaraan_operasional.index') }}" class="btn btn-outline-secondary rounded-pill w-100">
                            Batal
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
