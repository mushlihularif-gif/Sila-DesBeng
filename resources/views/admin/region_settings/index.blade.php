@extends('admin.layouts.admin')

@section('title', 'Pengaturan Wilayah & Layanan')

@section('page-title', 'Pengaturan Wilayah & Layanan')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Pengaturan /</span> Wilayah: {{ $region->name }}</h4>

    <div class="card mb-4">
        <h5 class="card-header bg-gradient-primary text-white">
            <i class="bx bx-map-alt me-2"></i>Detail & Layanan Wilayah
        </h5>
        <div class="card-body mt-4">
            <p class="text-muted mb-4">Kelola informasi kontak, detail rekening kas, dan pilih layanan yang diaktifkan untuk wilayah ini.</p>

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <strong>Berhasil!</strong> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <strong>Gagal!</strong> {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div class="alert alert-info border-info mb-4">
                <h6 class="alert-heading fw-bold mb-2"><i class="bx bx-info-circle me-1"></i>Profil Wilayah Administratif Anda</h6>
                <ul class="mb-0">
                    <li><strong>Desa/Kelurahan:</strong> {{ $region->type == 'desa' ? $region->name : '-' }}</li>
                    <li><strong>Kecamatan:</strong> {{ $region->parent ? $region->parent->name : '-' }}</li>
                    <li><strong>Kabupaten:</strong> {{ $region->parent && $region->parent->parent ? $region->parent->parent->name : '-' }}</li>
                </ul>
            </div>

            <form action="{{ route('admin.region-settings.update') }}" method="POST">
                @csrf
                
                <div class="row g-4">
                    <!-- Informasi Kontak -->
                    <div class="col-md-6">
                        <h6 class="fw-semibold border-bottom pb-2 mb-3"><i class="bx bx-phone-call me-1"></i>Informasi Kontak</h6>
                        
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Deskripsi Singkat / Profil</label>
                            <textarea name="profile_text" rows="3" class="form-control">{{ old('profile_text', $region->profile_text) }}</textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Telepon / WA</label>
                            <input type="text" name="contact_phone" value="{{ old('contact_phone', $region->contact_phone) }}" class="form-control">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Email</label>
                            <input type="email" name="contact_email" value="{{ old('contact_email', $region->contact_email) }}" class="form-control">
                        </div>
                    </div>

                    <!-- Informasi Kas (Rekening) -->
                    <div class="col-md-6">
                        <h6 class="fw-semibold border-bottom pb-2 mb-3"><i class="bx bx-wallet me-1"></i>Informasi Kas / Pembayaran</h6>
                        <p class="text-muted small mb-3">Dana dari pemesanan layanan di wilayah ini akan diarahkan ke rekening ini.</p>
                        
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Nama Bank / E-Wallet</label>
                            <input type="text" name="bank_name" value="{{ old('bank_name', $region->payment_info['bank_name'] ?? '') }}" placeholder="Contoh: BRI, Mandiri, Dana" class="form-control">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Nomor Rekening</label>
                            <input type="text" name="account_number" value="{{ old('account_number', $region->payment_info['account_number'] ?? '') }}" class="form-control">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Atas Nama (A/N)</label>
                            <input type="text" name="account_name" value="{{ old('account_name', $region->payment_info['account_name'] ?? '') }}" class="form-control">
                        </div>
                    </div>
                </div>

                <!-- Opt-in Layanan -->
                <div class="mt-5">
                    <h6 class="fw-semibold border-bottom pb-2 mb-3"><i class="bx bx-layer me-1"></i>Layanan yang Tersedia</h6>
                    <p class="text-muted small mb-4">Centang layanan yang ingin Anda aktifkan untuk wilayah ini. Jika tidak dicentang, warga tidak dapat melihat atau memesan layanan tersebut dari wilayah Anda.</p>
                    
                    <div class="row g-3">
                        @foreach($allServices as $service)
                            <div class="col-md-4">
                                <label class="card border {{ in_array($service->id, $activeServices) ? 'border-primary bg-label-primary' : 'border-secondary' }} h-100 cursor-pointer" style="cursor: pointer;">
                                    <div class="card-body p-3 d-flex flex-column justify-content-between">
                                        <div class="d-flex align-items-center w-100">
                                            <div class="form-check mb-0 me-3">
                                                <input type="checkbox" name="services[]" value="{{ $service->id }}" class="form-check-input" style="width: 1.2em; height: 1.2em;" {{ in_array($service->id, $activeServices) ? 'checked' : '' }}>
                                            </div>
                                            <div>
                                                <span class="fw-bold d-block text-dark">{{ $service->name }}</span>
                                                <small class="text-muted">Aktifkan modul {{ strtolower($service->name) }}</small>
                                            </div>
                                        </div>
                                        
                                        <div class="mt-3 pt-2 border-top w-100">
                                            <div class="form-check form-switch mb-0" onclick="event.stopPropagation();">
                                                <input class="form-check-input" type="checkbox" name="exclusive_services[]" value="{{ $service->id }}" id="exclusive_{{ $service->id }}" {{ in_array($service->id, $exclusiveServices ?? []) ? 'checked' : '' }}>
                                                <label class="form-check-label text-muted" style="font-size: 0.8rem;" for="exclusive_{{ $service->id }}">Khusus Warga Lokal</label>
                                            </div>
                                        </div>
                                    </div>
                                </label>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="mt-5 border-top pt-4 text-end">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="bx bx-save me-1"></i> Simpan Pengaturan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .bg-gradient-primary {
        background: #4a4a4a;
    }
    .bg-label-primary {
        background-color: rgba(105, 108, 255, 0.08) !important;
    }
</style>
@endsection
