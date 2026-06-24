@extends('admin.layouts.admin')

@section('title', 'Pengaturan Pemerintah Kabupaten')

@section('page-title', 'Pengaturan Pemerintah Kabupaten')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Admin /</span> Pengaturan Pemerintah Kabupaten <br> <small class="text-primary fs-6">(Admin Pusat / Admin 1)</small></h4>

    <div class="card mb-4">
        <h5 class="card-header bg-gradient-primary text-white">
            <i class="bx bx-map-alt me-2"></i>Detail & Layanan Pusat
        </h5>
        <div class="card-body mt-4">
            <p class="text-muted mb-4">Kelola informasi kontak pusat, detail rekening kas utama, dan pilih layanan yang diaktifkan untuk tingkat platform / kabupaten.</p>

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

            <form action="{{ route('admin.system-settings.update') }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="row g-4 justify-content-center">
                    <!-- Informasi Kontak -->
                    <div class="col-md-8">
                        <div class="mb-4">
                            <h6 class="fw-semibold border-bottom pb-2 mb-3"><i class="bx bx-phone-call me-1"></i>Informasi Kontak Pusat</h6>
                            <p class="text-muted small mb-3">Informasi dasar ini disiapkan untuk ditampilkan di halaman depan (Landing Page) bagian 'Tentang Kami'.</p>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Deskripsi Singkat / Profil Platform</label>
                                <textarea name="profile_text" rows="3" class="form-control">{{ old('profile_text', $region->profile_text ?? '') }}</textarea>
                            </div>
                        </div>

                        <!-- WhatsApp Box -->
                        <div class="card border border-success shadow-none bg-label-success mb-3">
                            <div class="card-body p-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h6 class="fw-bold text-success mb-0"><i class="bx bxl-whatsapp me-1 fs-5"></i>WhatsApp Layanan Pusat</h6>
                                    <div class="form-check form-switch mb-0">
                                        <input class="form-check-input" type="checkbox" name="whatsapp_active" id="whatsapp_active" onchange="document.getElementById('wa_fields').style.display = this.checked ? 'block' : 'none'" {{ !empty($region->payment_info['whatsapp_active']) ? 'checked' : '' }}>
                                        <label class="form-check-label text-success fw-semibold" for="whatsapp_active">Aktifkan</label>
                                    </div>
                                </div>
                                <p class="text-success small mb-3">Kontak WA ini akan dihubungkan ke tombol chat otomatis di aplikasi untuk melayani warga.</p>
                                
                                <div id="wa_fields" style="{{ empty($region->payment_info['whatsapp_active']) ? 'display: none;' : '' }}">
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold text-success">Nama Kontak WA</label>
                                        <input type="text" name="whatsapp_name" value="{{ old('whatsapp_name', $region->payment_info['whatsapp_name'] ?? '') }}" class="form-control border-success" placeholder="Contoh: Admin Pusat / Customer Service">
                                    </div>

                                    <div class="mb-1">
                                        <label class="form-label fw-semibold text-success">Nomor WhatsApp</label>
                                        <input type="text" name="contact_phone" value="{{ old('contact_phone', $region->contact_phone ?? '') }}" class="form-control border-success" placeholder="Contoh: 081234567890">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Opt-in Layanan -->
                <div class="mt-5">
                    <h6 class="fw-semibold border-bottom pb-2 mb-3"><i class="bx bx-layer me-1"></i>Layanan yang Tersedia</h6>
                    <p class="text-muted small mb-4">Centang layanan yang ingin Anda aktifkan. Jika tidak dicentang, warga tidak dapat melihat atau memesan layanan tersebut di platform Anda.</p>
                    
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
