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

                        <!-- Opsi Pengiriman Box (Mobil, Alat, Gas) -->
                        <div id="main_delivery_section" style="display: none;">
                            <h6 class="fw-semibold border-bottom pb-2 mb-3 mt-4"><i class="bx bx-truck me-1"></i>Metode Pengiriman Layanan</h6>
                            <p class="text-muted small mb-4">Pilih metode pengiriman (Antar/Jemput) yang Anda sediakan untuk tiap-tiap layanan. Minimal satu metode harus menyala untuk masing-masing layanan.</p>

                            <div class="row g-3 mb-3">
                                <!-- Mobil -->
                                <div class="col-md-6 col-xl-6" id="box_delivery_mobil" style="display: none;">
                                    <div class="card border border-primary shadow-none bg-label-primary h-100">
                                        <div class="card-body p-3">
                                            <div class="d-flex align-items-center mb-3">
                                                <div class="bg-white rounded-3 p-2 me-3 shadow-sm d-flex align-items-center justify-content-center border border-primary border-opacity-25" style="width: 55px; height: 55px;">
                                                    <img src="{{ asset('User/img/elemen/mobil.png') }}" alt="Mobil" class="w-100 h-100 object-contain" style="object-fit: contain;">
                                                </div>
                                                <h5 class="fw-bold text-primary mb-0">Mobil</h5>
                                            </div>
                                            <div class="d-flex flex-column gap-2 mt-3">
                                                <div class="d-flex justify-content-between align-items-center border-bottom pb-2">
                                                    <span class="text-primary fw-semibold small">Diantar</span>
                                                    <div class="form-check form-switch mb-0">
                                                        <input class="form-check-input" type="checkbox" name="mobil_delivery_antar_active" id="mobil_delivery_antar_active" {{ isset($region->payment_info['mobil_delivery_antar_active']) ? ($region->payment_info['mobil_delivery_antar_active'] ? 'checked' : '') : 'checked' }}>
                                                    </div>
                                                </div>
                                                <div class="d-flex justify-content-between align-items-center pb-2 border-bottom">
                                                    <span class="text-primary fw-semibold small">Jemput Sendiri</span>
                                                    <div class="form-check form-switch mb-0">
                                                        <input class="form-check-input" type="checkbox" name="mobil_delivery_jemput_active" id="mobil_delivery_jemput_active" {{ isset($region->payment_info['mobil_delivery_jemput_active']) ? ($region->payment_info['mobil_delivery_jemput_active'] ? 'checked' : '') : 'checked' }}>
                                                    </div>
                                                </div>
                                                
                                                <!-- Global BBM & Supir for Mobil -->
                                                <div class="mt-2">
                                                    <label class="form-label text-primary small fw-semibold mb-1">BBM Default</label>
                                                    <select name="mobil_bbm" class="form-select form-select-sm border-primary text-primary">
                                                        <option value="Penyewa" {{ ($region->payment_info['mobil_bbm_default'] ?? 'Penyewa') == 'Penyewa' ? 'selected' : '' }}>Ditanggung Penyewa</option>
                                                        <option value="Pemerintah Desa" {{ ($region->payment_info['mobil_bbm_default'] ?? '') == 'Pemerintah Desa' ? 'selected' : '' }}>Gratis BBM (Desa)</option>
                                                    </select>
                                                </div>
                                                <div class="mt-1">
                                                    <label class="form-label text-primary small fw-semibold mb-1">Supir Default</label>
                                                    <select name="mobil_supir" class="form-select form-select-sm border-primary text-primary">
                                                        <option value="Tanpa Supir (Bawa Sendiri)" {{ ($region->payment_info['mobil_supir_default'] ?? 'Tanpa Supir (Bawa Sendiri)') == 'Tanpa Supir (Bawa Sendiri)' || ($region->payment_info['mobil_supir_default'] ?? '') == 'Lepas Kunci' ? 'selected' : '' }}>Tanpa Supir (Bawa Sendiri)</option>
                                                        <option value="Dengan Supir" {{ ($region->payment_info['mobil_supir_default'] ?? '') == 'Dengan Supir' ? 'selected' : '' }}>Dengan Supir</option>
                                                        <option value="Bebas Pilih" {{ ($region->payment_info['mobil_supir_default'] ?? '') == 'Bebas Pilih' ? 'selected' : '' }}>Bebas Pilih</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Alat -->
                                <div class="col-md-6 col-xl-6" id="box_delivery_alat" style="display: none;">
                                    <div class="card border border-info shadow-none bg-label-info h-100">
                                        <div class="card-body p-3">
                                            <div class="d-flex align-items-center mb-3">
                                                <div class="bg-white rounded-3 p-2 me-3 shadow-sm d-flex align-items-center justify-content-center border border-info border-opacity-25" style="width: 55px; height: 55px;">
                                                    <img src="{{ asset('User/img/elemen/F0.png') }}" alt="Alat" class="w-100 h-100 object-contain" style="object-fit: contain;">
                                                </div>
                                                <h5 class="fw-bold text-info mb-0">Alat</h5>
                                            </div>
                                            <div class="d-flex flex-column gap-2 mt-3">
                                                <div class="d-flex justify-content-between align-items-center border-bottom pb-2">
                                                    <span class="text-info fw-semibold small">Diantar</span>
                                                    <div class="form-check form-switch mb-0">
                                                        <input class="form-check-input" type="checkbox" name="alat_delivery_antar_active" id="alat_delivery_antar_active" {{ isset($region->payment_info['alat_delivery_antar_active']) ? ($region->payment_info['alat_delivery_antar_active'] ? 'checked' : '') : 'checked' }}>
                                                    </div>
                                                </div>
                                                <div class="d-flex justify-content-between align-items-center pt-1">
                                                    <span class="text-info fw-semibold small">Jemput Sendiri</span>
                                                    <div class="form-check form-switch mb-0">
                                                        <input class="form-check-input" type="checkbox" name="alat_delivery_jemput_active" id="alat_delivery_jemput_active" {{ isset($region->payment_info['alat_delivery_jemput_active']) ? ($region->payment_info['alat_delivery_jemput_active'] ? 'checked' : '') : 'checked' }}>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Gas -->
                                <div class="col-md-6 col-xl-6" id="box_delivery_gas" style="display: none;">
                                    <div class="card border border-success shadow-none bg-label-success h-100">
                                        <div class="card-body p-3">
                                            <div class="d-flex align-items-center mb-3">
                                                <div class="bg-white rounded-3 p-2 me-3 shadow-sm d-flex align-items-center justify-content-center border border-success border-opacity-25" style="width: 55px; height: 55px;">
                                                    <img src="{{ asset('User/img/elemen/C3.png') }}" alt="Gas" class="w-100 h-100 object-contain" style="object-fit: contain;">
                                                </div>
                                                <h5 class="fw-bold text-success mb-0">Gas</h5>
                                            </div>
                                            <div class="d-flex flex-column gap-2 mt-3">
                                                <div class="d-flex justify-content-between align-items-center border-bottom pb-2">
                                                    <span class="text-success fw-semibold small">Diantar</span>
                                                    <div class="form-check form-switch mb-0">
                                                        <input class="form-check-input" type="checkbox" name="gas_delivery_antar_active" id="gas_delivery_antar_active" {{ isset($region->payment_info['gas_delivery_antar_active']) ? ($region->payment_info['gas_delivery_antar_active'] ? 'checked' : '') : 'checked' }}>
                                                    </div>
                                                </div>
                                                <div class="d-flex justify-content-between align-items-center pt-1">
                                                    <span class="text-success fw-semibold small">Jemput Sendiri</span>
                                                    <div class="form-check form-switch mb-0">
                                                        <input class="form-check-input" type="checkbox" name="gas_delivery_jemput_active" id="gas_delivery_jemput_active" {{ isset($region->payment_info['gas_delivery_jemput_active']) ? ($region->payment_info['gas_delivery_jemput_active'] ? 'checked' : '') : 'checked' }}>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Fasilitas Umum -->
                                <div class="col-md-6 col-xl-6" id="box_delivery_fasilitas" style="display: none;">
                                    <div class="card border border-warning shadow-none bg-label-warning h-100">
                                        <div class="card-body p-3">
                                            <div class="d-flex align-items-center mb-3">
                                                <div class="bg-white rounded-3 p-2 me-3 shadow-sm d-flex align-items-center justify-content-center border border-warning border-opacity-25" style="width: 55px; height: 55px;">
                                                    <img src="{{ asset('User/img/elemen/fasilitas.png') }}" alt="Fasilitas Umum" class="w-100 h-100 object-contain" style="object-fit: contain;">
                                                </div>
                                                <h5 class="fw-bold text-warning mb-0">Fasilitas Umum</h5>
                                            </div>
                                            <div class="d-flex flex-column gap-2 mt-3">
                                                <div class="d-flex justify-content-between align-items-center border-bottom pb-2">
                                                    <span class="text-warning fw-semibold small">Diantar</span>
                                                    <div class="form-check form-switch mb-0">
                                                        <input class="form-check-input" type="checkbox" name="fasilitas_delivery_antar_active" id="fasilitas_delivery_antar_active" {{ isset($region->payment_info['fasilitas_delivery_antar_active']) ? ($region->payment_info['fasilitas_delivery_antar_active'] ? 'checked' : '') : 'checked' }}>
                                                    </div>
                                                </div>
                                                <div class="d-flex justify-content-between align-items-center pb-2 border-bottom">
                                                    <span class="text-warning fw-semibold small">Jemput Sendiri</span>
                                                    <div class="form-check form-switch mb-0">
                                                        <input class="form-check-input" type="checkbox" name="fasilitas_delivery_jemput_active" id="fasilitas_delivery_jemput_active" {{ isset($region->payment_info['fasilitas_delivery_jemput_active']) ? ($region->payment_info['fasilitas_delivery_jemput_active'] ? 'checked' : '') : 'checked' }}>
                                                    </div>
                                                </div>
                                                
                                                <!-- Global BBM & Supir for Fasilitas -->
                                                @if($hasFasilitasKendaraan)
                                                <div class="mt-2 pt-2 border-top">
                                                    <label class="form-label text-warning small fw-semibold mb-1">BBM Default</label>
                                                    <select name="fasilitas_bbm" class="form-select form-select-sm border-warning text-warning">
                                                        <option value="Penyewa" {{ ($region->payment_info['fasilitas_bbm_default'] ?? 'Penyewa') == 'Penyewa' ? 'selected' : '' }}>Ditanggung Penyewa</option>
                                                        <option value="Pemerintah Desa" {{ ($region->payment_info['fasilitas_bbm_default'] ?? '') == 'Pemerintah Desa' ? 'selected' : '' }}>Gratis BBM (Desa)</option>
                                                    </select>
                                                </div>
                                                <div class="mt-1">
                                                    <label class="form-label text-warning small fw-semibold mb-1">Supir Default</label>
                                                    <select name="fasilitas_supir" class="form-select form-select-sm border-warning text-warning">
                                                        <option value="Tanpa Supir (Bawa Sendiri)" {{ ($region->payment_info['fasilitas_supir_default'] ?? 'Tanpa Supir (Bawa Sendiri)') == 'Tanpa Supir (Bawa Sendiri)' || ($region->payment_info['fasilitas_supir_default'] ?? '') == 'Lepas Kunci' ? 'selected' : '' }}>Tanpa Supir (Bawa Sendiri)</option>
                                                        <option value="Dengan Supir" {{ ($region->payment_info['fasilitas_supir_default'] ?? '') == 'Dengan Supir' ? 'selected' : '' }}>Dengan Supir</option>
                                                        <option value="Bebas Pilih" {{ ($region->payment_info['fasilitas_supir_default'] ?? '') == 'Bebas Pilih' ? 'selected' : '' }}>Bebas Pilih</option>
                                                    </select>
                                                </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                <!-- Opt-in Layanan -->
                <div class="mt-5 px-1 px-md-4">
                    <div class="row g-4">
                        <div class="col-12">
                            <h6 class="fw-semibold border-bottom pb-2 mb-3"><i class="bx bx-layer me-1"></i>Layanan yang Tersedia</h6>
                            <p class="text-muted small mb-2">Centang layanan yang ingin Anda aktifkan untuk wilayah ini. Jika tidak dicentang, warga tidak dapat melihat atau memesan layanan tersebut di platform Anda.</p>
                        </div>
                        
                        @foreach($allServices as $service)
                            <div class="col-md-6 col-xl-4">
                                <label class="card border {{ in_array($service->id, $activeServices) ? 'border-primary shadow-sm bg-label-primary' : 'border-secondary shadow-none' }} h-100" style="cursor: pointer; transition: all 0.2s;">
                                    <div class="card-body p-4 d-flex flex-column justify-content-between">
                                        <div class="d-flex align-items-start mb-3">
                                            <div class="form-check mt-1 me-3">
                                                <input type="checkbox" name="services[]" value="{{ $service->id }}" class="form-check-input" style="width: 1.4em; height: 1.4em;" {{ in_array($service->id, $activeServices) ? 'checked' : '' }}>
                                            </div>
                                            <div>
                                                <span class="fw-bold fs-6 text-dark d-block mb-1">{{ $service->name }}</span>
                                                <p class="text-muted small mb-0">Aktifkan modul {{ strtolower($service->name) }}.</p>
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    const checkboxes = document.querySelectorAll('input[name="services[]"]');
    
    function updateDeliveryBoxes() {
        let showMobil = false, showAlat = false, showGas = false, showFasilitas = false;
        
        checkboxes.forEach(cb => {
            if(cb.checked) {
                const name = cb.closest('.card-body').querySelector('.fw-bold').innerText;
                if(name.includes('Mobil')) showMobil = true;
                if(name.includes('Alat')) showAlat = true;
                if(name.includes('Gas')) showGas = true;
                if(name.includes('Fasilitas Umum')) showFasilitas = true;
            }
        });
        
        const boxMobil = document.getElementById('box_delivery_mobil');
        const boxAlat = document.getElementById('box_delivery_alat');
        const boxGas = document.getElementById('box_delivery_gas');
        const boxFasilitas = document.getElementById('box_delivery_fasilitas');
        const mainBox = document.getElementById('main_delivery_section');
        
        if(boxMobil) boxMobil.style.display = showMobil ? 'block' : 'none';
        if(boxAlat) boxAlat.style.display = showAlat ? 'block' : 'none';
        if(boxGas) boxGas.style.display = showGas ? 'block' : 'none';
        if(boxFasilitas) boxFasilitas.style.display = showFasilitas ? 'block' : 'none';
        
        if(mainBox) mainBox.style.display = (showMobil || showAlat || showGas || showFasilitas) ? 'block' : 'none';
    }

    checkboxes.forEach(cb => {
        cb.addEventListener('change', updateDeliveryBoxes);
    });
    
    updateDeliveryBoxes();
});
</script>
@endsection
