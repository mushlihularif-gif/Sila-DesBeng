@extends('admin.layouts.admin')

@section('title', 'Pengaturan Sistem')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">

        <!-- Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="fw-bold py-3 mb-0">
                            <span class="text-muted fw-light">Admin /</span> Pengaturan Sistem
                        </h4>
                        <p class="text-muted mb-0">Kelola konfigurasi utama aplikasi, lokasi, dan pembayaran.</p>
                    </div>
                    <div>
                        <form action="{{ route('admin.system-settings.reset') }}" method="POST" style="display:inline;"
                            onsubmit="return confirm('Yakin ingin mereset semua pengaturan ke default?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger">
                                <i class="bx bx-reset me-1"></i> Reset Default
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm border-0 overflow-hidden">
                    <div class="card-body p-0">
                        <form action="{{ route('admin.system-settings.update') }}" method="POST"
                            enctype="multipart/form-data">
                            @csrf
                            @method('PUT')

                            <div class="row g-0">
                                <!-- Left Sidebar Navigation -->
                                <div class="col-md-3 border-end bg-light bg-opacity-50">
                                    <div class="p-4">
                                        <div class="nav flex-column nav-pills" id="v-pills-tab" role="tablist"
                                            aria-orientation="vertical">
                                            <button class="nav-link active text-start mb-2" id="v-pills-location-tab"
                                                data-bs-toggle="pill" data-bs-target="#v-pills-location" type="button"
                                                role="tab">
                                                <i class="bx bx-map me-2"></i> Lokasi dan Pembayaran
                                            </button>
                                            <button class="nav-link text-start" id="v-pills-contact-tab"
                                                data-bs-toggle="pill" data-bs-target="#v-pills-contact" type="button"
                                                role="tab">
                                                <i class="bx bx-phone-call me-2"></i> Kontak & Layanan
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Right Content Area -->
                                <div class="col-md-9">
                                    <div class="tab-content p-4" id="v-pills-tabContent">

                                        <!-- LOKASI TAB -->
                                        <div class="tab-pane fade show active" id="v-pills-location" role="tabpanel">
                                            <h5 class="fw-bold mb-4 text-primary"><i
                                                    class="bx bx-map-pin me-2"></i>Lokasi dan Pembayaran</h5>

                                            <div class="row g-4">
                                                <div class="col-md-12">
                                                    <div class="form-floating mb-3">
                                                        <input type="text" class="form-control" id="location_name"
                                                            name="location_name"
                                                            value="{{ old('location_name', $setting->location_name) }}"
                                                            placeholder="Nama Lokasi">
                                                        <label for="location_name">Nama Lokasi / Kantor</label>
                                                    </div>
                                                    <div class="form-floating mb-3">
                                                        <textarea class="form-control" id="address" name="address" style="height: 150px" placeholder="Alamat Lengkap">{{ old('address', $setting->address) }}</textarea>
                                                        <label for="address">Alamat Lengkap</label>
                                                    </div>
                                                    
                                                    <div class="form-floating mb-3">
                                                        <input type="text" class="form-control" id="cash_payment_description"
                                                            name="cash_payment_description"
                                                            value="{{ old('cash_payment_description', $setting->cash_payment_description) }}"
                                                            placeholder="Keterangan Pembayaran Tunai">
                                                        <label for="cash_payment_description">Info Pembayaran Tunai (Contoh: Bayar ke Bendahara)</label>
                                                    </div>
                                                    <input type="hidden" name="payment_methods[]" value="tunai">
                                                    <div class="alert alert-info d-flex align-items-center"
                                                        role="alert">
                                                        <i class="bx bx-info-circle me-2 fs-4"></i>
                                                        <div class="small">
                                                            Masukkan alamat lengkap BUMDes yang akan ditampilkan kepada pengguna saat melakukan pemesanan.
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>



                                        <!-- KONTAK TAB -->
                                        <div class="tab-pane fade" id="v-pills-contact" role="tabpanel">
                                            <div class="row g-4 mb-4">
                                                <div class="col-md-6">
                                                    <h5 class="fw-bold mb-4 text-primary"><i class="bx bx-support me-2"></i>Kontak
                                                        & Jam Layanan</h5>
                                                    <div class="form-floating mb-3">
                                                        <input type="text" class="form-control" id="whatsapp_number"
                                                            name="whatsapp_number"
                                                            value="{{ old('whatsapp_number', $setting->whatsapp_number) }}"
                                                            placeholder="628..." required
                                                            oninput="updateContactPreview()">
                                                        <label for="whatsapp_number">Nomor WhatsApp (Format:
                                                            628...)</label>
                                                    </div>
                                                    <div class="form-floating mb-3">
                                                        <textarea class="form-control" id="office_address" name="office_address" style="height: 100px"
                                                            placeholder="Alamat Kantor" oninput="updateContactPreview()">{{ old('office_address', $setting->office_address) }}</textarea>
                                                        <label for="office_address">Alamat Kantor</label>
                                                    </div>
                                                    <div class="form-floating mb-3">
                                                        <input type="text" class="form-control" id="operating_hours"
                                                            name="operating_hours"
                                                            value="{{ old('operating_hours', $setting->operating_hours) }}"
                                                            placeholder="Jam Operasional"
                                                            oninput="updateContactPreview()">
                                                        <label for="operating_hours">Jam Operasional</label>
                                                    </div>
                                                </div>

                                                <div class="col-md-6 d-flex flex-column">
                                                    <div class="card border border-0 shadow-sm flex-fill" style="background: #fff; border: 1px solid #eaeaea;">
                                                        <div class="card-body p-4 d-flex flex-column justify-content-center">
                                                            <div class="text-center mb-4">
                                                                <div class="position-relative d-inline-block">
                                                                    <div class="avatar avatar-xl mb-3">
                                                                        <span class="avatar-initial rounded-circle bg-success text-white shadow-sm" style="width: 80px; height: 80px; font-size: 3.5rem; display: flex; align-items: center; justify-content: center;">
                                                                            <i class='bx bxl-whatsapp'></i>
                                                                        </span>
                                                                    </div>
                                                                    <span class="position-absolute bottom-0 end-0 p-2 bg-success border border-white rounded-circle indicator">
                                                                        <span class="visually-hidden">Online</span>
                                                                    </span>
                                                                </div>
                                                                <h5 class="fw-bold text-dark mb-1">WhatsApp Admin</h5>
                                                                <a href="#" class="text-muted text-decoration-none d-inline-flex align-items-center justify-content-center gap-2 mt-1 hover-text-success transition-all" id="preview_wa_link" target="_blank">
                                                                    <span id="preview_wa_number" class="fs-5">+62 8xx-xxxx-xxxx</span>
                                                                    <i class="bx bx-link-external small"></i>
                                                                </a>
                                                            </div>

                                                            <div class="d-flex flex-column gap-3 mt-2">
                                                                <div class="d-flex align-items-start p-3 rounded-3 bg-light bg-opacity-50 settings-info-card transition-all">
                                                                    <div class="me-3 mt-1">
                                                                        <!-- Map SVG Icon -->
                                                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                                            <path d="M12 13.43C13.7231 13.43 15.12 12.0331 15.12 10.31C15.12 8.58687 13.7231 7.19 12 7.19C10.2769 7.19 8.88 8.58687 8.88 10.31C8.88 12.0331 10.2769 13.43 12 13.43Z" stroke="#4F46E5" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                                            <path d="M3.62 8.49003C5.59 -0.169969 18.42 -0.159969 20.38 8.50003C21.53 13.58 18.37 17.88 15.6 20.54C13.59 22.48 10.41 22.48 8.38999 20.54C5.62999 17.88 2.47 13.57 3.62 8.49003Z" stroke="#4F46E5" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                                        </svg>
                                                                    </div>
                                                                    <div>
                                                                        <small class="text-uppercase text-muted fw-bold" style="font-size: 0.7rem; letter-spacing: 0.5px;">Alamat Kantor</small>
                                                                        <span id="preview_address" class="text-dark fw-medium lh-sm d-block mt-1">Alamat kantor...</span>
                                                                    </div>
                                                                </div>

                                                                <div class="d-flex align-items-start p-3 rounded-3 bg-light bg-opacity-50 settings-info-card transition-all">
                                                                    <div class="me-3 mt-1">
                                                                        <!-- Clock SVG Icon -->
                                                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                                            <path d="M22 12C22 17.52 17.52 22 12 22C6.48 22 2 17.52 2 12C2 6.48 6.48 2 12 2C17.52 2 22 6.48 22 12Z" stroke="#06B6D4" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                                            <path d="M15.71 15.18L12.61 13.33C12.07 13.01 11.63 12.24 11.63 11.61V7.51001" stroke="#06B6D4" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                                        </svg>
                                                                    </div>
                                                                    <div>
                                                                        <small class="text-uppercase text-muted fw-bold" style="font-size: 0.7rem; letter-spacing: 0.5px;">Jam Operasional</small>
                                                                        <span id="preview_hours" class="text-dark fw-medium d-block mt-1">Jam operasional...</span>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </div>

                            <div class="card-footer bg-white border-top text-end p-3">
                                <button type="submit" class="btn btn-primary px-4">
                                    <i class="bx bx-save me-1"></i> Simpan Perubahan
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>





    <style>
        /* Enhanced Card Styles */
        .card {
            transition: all 0.3s ease;
        }

        .card:hover {
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1) !important;
        }

        /* Form Floating Enhancements */
        .form-floating > .form-control:focus ~ label,
        .form-floating > .form-control:not(:placeholder-shown) ~ label,
        .form-floating > .form-select ~ label {
            opacity: 0.65;
            transform: scale(0.85) translateY(-0.5rem) translateX(0.15rem);
        }

        /* Nav Pills Enhancement */
        .nav-pills .nav-link {
            transition: all 0.3s ease;
            border-radius: 0.5rem;
            margin-bottom: 0.5rem;
        }

        .nav-pills .nav-link:hover {
            background-color: rgba(105, 108, 255, 0.1);
            transform: translateX(5px);
        }

        .nav-pills .nav-link.active {
            background: linear-gradient(135deg, #0099ff 0%, #0077cc 100%);
            box-shadow: 0 4px 15px rgba(0, 153, 255, 0.4);
        }

        /* Alert Enhancement */
        .alert {
            border-radius: 0.75rem;
            border: none;
        }

        /* Button Enhancements */
        .btn {
            transition: all 0.3s ease;
            border-radius: 0.5rem;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .btn-primary {
            background: linear-gradient(135deg, #0099ff 0%, #0077cc 100%);
            border: none;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #0088ee 0%, #0066bb 100%);
        }

        /* Tab Content Animation */
        .tab-pane {
            animation: fadeIn 0.5s ease;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>

    <script>
        // Initialize other previews on page load
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(updateContactPreview, 500); // Slight delay to ensure values are populated
        });

        function updateContactPreview() {
            var waInput = document.getElementById('whatsapp_number');
            var wa = waInput ? waInput.value : '-';
            
            var addrInput = document.getElementById('office_address');
            var addr = addrInput ? addrInput.value : '-';
            
            var hoursInput = document.getElementById('operating_hours');
            var hours = hoursInput ? hoursInput.value : '-';
            
            // Update Text Content safely
            var elWa = document.getElementById('preview_wa_number');
            if(elWa) elWa.textContent = wa;
            
            var elAddr = document.getElementById('preview_address');
            if(elAddr) elAddr.textContent = addr;
            
            var elHours = document.getElementById('preview_hours');
            if(elHours) elHours.textContent = hours;

            // Clean WhatsApp Number Logic
            var cleanWa = wa.replace(/\D/g, ''); // Remove non-digits
            
            // Handle Indonesian numbers specifically
            if (cleanWa.startsWith('0')) {
                cleanWa = '62' + cleanWa.substring(1);
            } else if (cleanWa.startsWith('8')) {
                cleanWa = '62' + cleanWa;
            }
            
            // Generate Link
            // Using api.whatsapp.com is often more reliable
            var waLink = 'https://api.whatsapp.com/send?phone=' + cleanWa;
            
            var elLink = document.getElementById('preview_wa_link');
            if(elLink) {
                elLink.href = waLink;
            }
        }
    </script>
@endsection
