@extends('admin.layouts.admin')

@section('title', 'Tambah Produk Gas')

@include('admin.unit.partials.unit_product_styles')

@section('content')
@php
    $selectedKategori = old('kategori', '');
    $selectedSatuan = old('satuan', 'Tabung');
    $defaultSatuans = ['Tabung', 'Unit', 'Paket'];
    if ($selectedSatuan && !in_array($selectedSatuan, $defaultSatuans, true)) {
        $defaultSatuans[] = $selectedSatuan;
    }
@endphp

<div class="container-xxl flex-grow-1 container-p-y">
    {{-- Page Header --}}
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-3 mb-4">
        <div class="min-w-0">
            <h4 class="fw-bold mb-1 text-dark">
                <span class="text-muted fw-normal">
                    <a href="{{ route('admin.unit.penjualan_gas.index') }}" class="text-muted text-decoration-none">Penjualan Gas</a> /
                </span>
                Tambah Gas
            </h4>
            <p class="text-muted mb-0 small">Tambahkan produk gas baru lengkap dengan harga satuan, stok tabung, foto, dan pangkalan.</p>
        </div>
        <a href="{{ route('admin.unit.penjualan_gas.index') }}"
           class="segi-back btn btn-outline-secondary rounded-pill px-4 w-100 w-sm-auto d-inline-flex align-items-center justify-content-center gap-1">
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

    <form action="{{ route('admin.unit.penjualan_gas.store') }}" method="POST" enctype="multipart/form-data" class="up-form">
        @csrf

        <div class="row g-4">
            {{-- Kolom Kiri --}}
            <div class="col-12 col-lg-8">
                {{-- 1. Informasi Produk Gas --}}
                <div class="card up-card mb-4">
                    <div class="up-card-header d-flex align-items-center gap-2.5">
                        <span class="up-step-num bg-label-primary text-primary">1</span>
                        <div>
                            <h6 class="fw-bold text-dark mb-0">Informasi Produk Gas</h6>
                            <small class="text-muted">Jenis tabung gas, kategori, dan keterangan pembelian</small>
                        </div>
                    </div>
                    <div class="card-body p-3 p-md-4">
                        <div class="row g-3">
                            <div class="col-12 col-md-7">
                                <label class="form-label" for="jenis_gas">
                                    Jenis Gas <span class="text-danger">*</span>
                                </label>
                                <input type="text"
                                       class="form-control @error('jenis_gas') is-invalid @enderror"
                                       id="jenis_gas"
                                       name="jenis_gas"
                                       value="{{ old('jenis_gas') }}"
                                       placeholder="Contoh: LPG 3 Kg / Bright Gas 5,5 Kg"
                                       required />
                                @error('jenis_gas')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-12 col-md-5">
                                <label class="form-label" for="kategori">
                                    Kategori <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <select class="form-select @error('kategori') is-invalid @enderror" id="kategori" name="kategori" required>
                                        <option value="" disabled @selected(!$selectedKategori)>Pilih Kategori</option>
                                        @foreach($categories as $category)
                                            <option value="{{ $category->name }}" @selected($selectedKategori === $category->name)>
                                                {{ $category->name }}
                                            </option>
                                        @endforeach
                                        @if($selectedKategori && !$categories->contains('name', $selectedKategori))
                                            <option value="{{ $selectedKategori }}" selected>{{ $selectedKategori }}</option>
                                        @endif
                                    </select>
                                    <button type="button" class="btn btn-outline-primary px-3" data-bs-toggle="modal" data-bs-target="#addCategoryModal" title="Tambah kategori baru">
                                        <i class="bx bx-plus"></i>
                                    </button>
                                </div>
                                @error('kategori')<small class="text-danger d-block mt-1">{{ $message }}</small>@enderror
                            </div>

                            <div class="col-12">
                                <label class="form-label" for="deskripsi">
                                    Deskripsi Produk <span class="text-danger">*</span>
                                </label>
                                <textarea class="form-control @error('deskripsi') is-invalid @enderror"
                                          id="deskripsi"
                                          name="deskripsi"
                                          rows="4"
                                          placeholder="Jelaskan detail isi ulang / tabung baru, syarat penukaran tabung kosong, atau jam pelayanan..."
                                          required>{{ old('deskripsi') }}</textarea>
                                @error('deskripsi')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>
                </div>

                {{-- 2. Harga & Stok --}}
                <div class="card up-card mb-4">
                    <div class="up-card-header d-flex align-items-center gap-2.5">
                        <span class="up-step-num bg-label-success text-success">2</span>
                        <div>
                            <h6 class="fw-bold text-dark mb-0">Harga &amp; Stok Tersedia</h6>
                            <small class="text-muted">Tentukan harga jual per tabung/unit dan jumlah stok saat ini</small>
                        </div>
                    </div>
                    <div class="card-body p-3 p-md-4">
                        <div class="row g-3">
                            <div class="col-12 col-md-5">
                                <label class="form-label" for="harga_satuan">
                                    Harga Satuan <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="text"
                                           class="form-control fw-semibold @error('harga_satuan') is-invalid @enderror"
                                           id="harga_satuan"
                                           name="harga_satuan"
                                           value="{{ old('harga_satuan') }}"
                                           placeholder="21.000"
                                           inputmode="numeric"
                                           required
                                           oninput="formatRupiah(this)" />
                                </div>
                                @error('harga_satuan')<small class="text-danger d-block mt-1">{{ $message }}</small>@enderror
                            </div>

                            <div class="col-6 col-md-3">
                                <label class="form-label" for="stok">
                                    Stok Tersedia <span class="text-danger">*</span>
                                </label>
                                <input type="number"
                                       class="form-control @error('stok') is-invalid @enderror"
                                       id="stok"
                                       name="stok"
                                       value="{{ old('stok', 0) }}"
                                       placeholder="50"
                                       min="0"
                                       required />
                                @error('stok')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-6 col-md-4">
                                <label class="form-label" for="satuan">
                                    Satuan <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <select class="form-select @error('satuan') is-invalid @enderror" id="satuan" name="satuan" required>
                                        @foreach($defaultSatuans as $sat)
                                            <option value="{{ $sat }}" @selected($selectedSatuan === $sat)>{{ $sat }}</option>
                                        @endforeach
                                    </select>
                                    <button type="button" class="btn btn-outline-primary px-3" data-bs-toggle="modal" data-bs-target="#addSatuanModal" title="Tambah satuan baru">
                                        <i class="bx bx-plus"></i>
                                    </button>
                                </div>
                                @error('satuan')<small class="text-danger d-block mt-1">{{ $message }}</small>@enderror
                            </div>
                        </div>
                    </div>
                </div>

                {{-- 3. Foto Produk --}}
                <div class="card up-card mb-4">
                    <div class="up-card-header d-flex align-items-center gap-2.5">
                        <span class="up-step-num bg-label-info text-info">3</span>
                        <div>
                            <h6 class="fw-bold text-dark mb-0">Foto Produk Gas</h6>
                            <small class="text-muted">Unggah foto produk agar mudah dikenali warga (rasio kotak 1:1)</small>
                        </div>
                    </div>
                    <div class="card-body p-3 p-md-4">
                        @include('admin.unit.partials.photo_upload_slots', [
                            'item' => null,
                            'primaryName' => 'foto',
                            'primaryRequired' => false,
                            'cropRatioExpr' => '1',
                            'altPrefix' => 'Foto Produk Gas',
                        ])
                    </div>
                </div>

                {{-- 4. Lokasi Pangkalan --}}
                <div class="card up-card">
                    <div class="up-card-header d-flex align-items-center gap-2.5">
                        <span class="up-step-num bg-label-warning text-warning">4</span>
                        <div>
                            <h6 class="fw-bold text-dark mb-0">Lokasi Pangkalan / Pengambilan</h6>
                            <small class="text-muted">Pilih pangkalan tersimpan desa atau masukkan alamat baru</small>
                        </div>
                    </div>
                    <div class="card-body p-3 p-md-4">
                        @include('admin.unit.partials.location_selector', [
                            'savedLocations' => $savedLocations,
                            'lokasiValue' => '',
                            'latValue' => '',
                            'lngValue' => '',
                            'placeholderText' => 'Contoh: Pangkalan Gas BUMDes',
                        ])
                    </div>
                </div>
            </div>

            {{-- Kolom Kanan Sticky --}}
            <div class="col-12 col-lg-4">
                <div class="up-sticky">
                    <div class="card up-card mb-4">
                        <div class="up-card-header d-flex align-items-center gap-2.5">
                            <div class="up-icon-box bg-label-primary">
                                <i class="bx bx-slider-alt fs-5 text-primary"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold text-dark mb-0">Status Katalog</h6>
                                <small class="text-muted">Atur ketersediaan produk gas</small>
                            </div>
                        </div>
                        <div class="card-body p-3 p-md-4">
                            <label class="form-label" for="status">Status Produk <span class="text-danger">*</span></label>
                            <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                                <option value="tersedia" @selected(old('status', 'tersedia') === 'tersedia')>Tersedia — Siap Dipesan</option>
                                <option value="dipesan" @selected(old('status') === 'dipesan')>Dipesan / Habis Sementara</option>
                                <option value="rusak" @selected(old('status') === 'rusak')>Tidak Aktif / Rusak</option>
                            </select>
                            @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="card up-card mb-4">
                        <div class="card-body p-3 p-md-4">
                            <button type="submit" class="btn btn-primary rounded-pill w-100 py-2.5 fw-semibold shadow-sm mb-2">
                                <i class="bx bx-save me-1"></i>Simpan Produk Gas
                            </button>
                            <a href="{{ route('admin.unit.penjualan_gas.index') }}" class="btn btn-outline-secondary rounded-pill w-100">
                                Batal
                            </a>
                        </div>
                    </div>

                    <div class="card border-0 bg-label-info rounded-4">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-start gap-2">
                                <i class="bx bx-info-circle fs-5 text-info mt-0.5 flex-shrink-0"></i>
                                <div class="small text-info" style="line-height: 1.55;">
                                    <strong class="d-block mb-1">Gas Subsidi 3 Kg</strong>
                                    Produk dengan nama mengandung <strong>3 Kg</strong> otomatis mengikuti aturan kuota &amp; verifikasi KK jika Mode Krisis aktif.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@section('modals')
    @include('admin.unit.partials.category_satuan_modals', [
        'categoryType' => 'gas',
        'categoryPlaceholder' => 'Contoh: Gas Subsidi / Non-Subsidi',
        'satuanPlaceholder' => 'Contoh: Tabung',
        'includeSatuanModal' => true,
    ])
@endsection
