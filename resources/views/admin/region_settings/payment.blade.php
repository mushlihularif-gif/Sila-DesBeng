@extends('admin.layouts.admin')

@section('title', 'Pengaturan Pembayaran Wilayah')
@section('page-title', 'Pengaturan Pembayaran Wilayah')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Admin /</span> Pengaturan Pembayaran Wilayah <br> <small class="text-primary fs-6">(Admin Wilayah)</small></h4>

    <div class="card mb-4">
        <h5 class="card-header bg-gradient-primary text-white">
            <i class="bx bx-credit-card-front me-2"></i>Informasi Kas & Pembayaran
        </h5>
        <div class="card-body mt-4">
            <p class="text-muted mb-4">Kelola rekening bank utama, dompet elektronik (e-wallet), dan integrasi otomatis Payment Gateway (Midtrans).</p>

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

            <form action="{{ route('admin.region-settings.payment.update') }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="row g-4">
                    <!-- Kolom Kiri: Transfer Bank & Preview -->
                    <div class="col-md-6">
                        <!-- Bank Utama -->
                        <div class="card border border-primary shadow-none mb-3 bg-label-primary">
                            <div class="card-body p-3">
                                <div class="d-flex justify-content-between align-items-center mb-4">
                                    <h6 class="fw-bold text-primary mb-0">Rekening Bank Utama (Transfer Manual)</h6>
                                    <div class="form-check form-switch mb-0">
                                        <input class="form-check-input" type="checkbox" name="bank_active" id="bank_active" value="1" {{ (old('bank_active', $region->payment_info['bank_active'] ?? true)) ? 'checked' : '' }} onchange="document.getElementById('bank_fields').style.opacity = this.checked ? '1' : '0.5'">
                                        <label class="form-check-label fw-semibold text-primary" for="bank_active">Aktifkan</label>
                                    </div>
                                </div>
                                
                                <div id="bank_fields" style="opacity: {{ (old('bank_active', $region->payment_info['bank_active'] ?? true)) ? '1' : '0.5' }}; transition: opacity 0.3s;">
                                
                                <!-- ATM Card Preview -->
                                <div class="mb-4">
                                    <div class="atm-card-preview" id="atmCardPreview">
                                        <div class="atm-card-inner">
                                            <div class="atm-card-front" id="atmCardFront" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);">
                                                <div class="d-flex justify-content-between align-items-start mb-4">
                                                    <i class="bx bx-chip text-warning" style="font-size: 2.5rem;"></i>
                                                    <h5 class="text-white fw-bold mb-0" id="previewBankName" style="font-style: italic;">BSI</h5>
                                                </div>
                                                <h4 class="text-white text-center mb-4" id="previewAccountNumber" style="font-family: monospace; letter-spacing: 2px; text-shadow: 1px 1px 2px rgba(0,0,0,0.5);">0000 0000 0000 0000</h4>
                                                <div class="d-flex justify-content-between align-items-end">
                                                    <div>
                                                        <small class="text-white-50 d-block" style="font-size: 0.7rem;">ATAS NAMA</small>
                                                        <span class="text-white fw-semibold text-uppercase" id="previewAccountName">PEMILIK</span>
                                                    </div>
                                                    <i class="bx bx-wifi bx-rotate-90 text-white-50 fs-4"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-semibold text-primary d-block">Pilih Bank</label>
                                    <div class="row g-2">
                                        @php $currentBank = old('bank_name', $region->payment_info['bank_name'] ?? ''); @endphp
                                        <div class="col-4">
                                            <label class="payment-logo-card">
                                                <input type="radio" name="bank_name" value="BSI" class="payment-radio" onchange="updateCardPreview()" {{ $currentBank == 'BSI' ? 'checked' : '' }}>
                                                <div class="logo-wrapper">
                                                    <img src="{{ asset('assets/img/payment_logos/bsi.png') }}" alt="BSI" class="img-fluid payment-logo">
                                                </div>
                                            </label>
                                        </div>
                                        <div class="col-4">
                                            <label class="payment-logo-card">
                                                <input type="radio" name="bank_name" value="BRK Syariah" class="payment-radio" onchange="updateCardPreview()" {{ $currentBank == 'BRK Syariah' ? 'checked' : '' }}>
                                                <div class="logo-wrapper">
                                                    <img src="{{ asset('assets/img/payment_logos/brksyariah.png') }}" alt="BRK Syariah" class="img-fluid payment-logo">
                                                </div>
                                            </label>
                                        </div>
                                        <div class="col-4">
                                            <label class="payment-logo-card">
                                                <input type="radio" name="bank_name" value="Mandiri" class="payment-radio" onchange="updateCardPreview()" {{ $currentBank == 'Mandiri' ? 'checked' : '' }}>
                                                <div class="logo-wrapper">
                                                    <img src="{{ asset('assets/img/payment_logos/mandiri.png') }}" alt="Mandiri" class="img-fluid payment-logo">
                                                </div>
                                            </label>
                                        </div>
                                        <div class="col-4">
                                            <label class="payment-logo-card">
                                                <input type="radio" name="bank_name" value="BRI" class="payment-radio" onchange="updateCardPreview()" {{ $currentBank == 'BRI' ? 'checked' : '' }}>
                                                <div class="logo-wrapper">
                                                    <img src="{{ asset('assets/img/payment_logos/bri.png') }}" alt="BRI" class="img-fluid payment-logo">
                                                </div>
                                            </label>
                                        </div>
                                        <div class="col-4">
                                            <label class="payment-logo-card">
                                                <input type="radio" name="bank_name" value="BNI" class="payment-radio" onchange="updateCardPreview()" {{ $currentBank == 'BNI' ? 'checked' : '' }}>
                                                <div class="logo-wrapper">
                                                    <img src="{{ asset('assets/img/payment_logos/bni.png') }}" alt="BNI" class="img-fluid payment-logo">
                                                </div>
                                            </label>
                                        </div>
                                        <div class="col-4">
                                            <label class="payment-logo-card">
                                                <input type="radio" name="bank_name" value="BCA" class="payment-radio" onchange="updateCardPreview()" {{ $currentBank == 'BCA' ? 'checked' : '' }}>
                                                <div class="logo-wrapper">
                                                    <img src="{{ asset('assets/img/payment_logos/bca.jpg') }}" alt="BCA" class="img-fluid payment-logo">
                                                </div>
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-semibold text-primary">Nomor Rekening</label>
                                    <input type="text" name="account_number" id="account_number" value="{{ old('account_number', $region->payment_info['account_number'] ?? '') }}" class="form-control border-primary" placeholder="0000000000" oninput="updateCardPreview()">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-semibold text-primary">Atas Nama (A/N)</label>
                                    <input type="text" name="account_name" id="account_name" value="{{ old('account_name', $region->payment_info['account_name'] ?? '') }}" class="form-control border-primary" placeholder="BUMDes Pematang Duku" oninput="updateCardPreview()">
                                </div>
                                
                                <div class="mb-1">
                                    <label class="form-label fw-semibold text-primary">Warna Tema Kartu</label>
                                    <select name="card_theme" id="card_theme" class="form-select border-primary" onchange="updateCardPreview()">
                                        <option value="blue" {{ (old('card_theme', $region->payment_info['card_theme'] ?? 'blue') == 'blue') ? 'selected' : '' }}>Biru Klasik</option>
                                        <option value="gold" {{ (old('card_theme', $region->payment_info['card_theme'] ?? '') == 'gold') ? 'selected' : '' }}>Emas Premium</option>
                                        <option value="dark" {{ (old('card_theme', $region->payment_info['card_theme'] ?? '') == 'dark') ? 'selected' : '' }}>Hitam Elegan</option>
                                        <option value="green" {{ (old('card_theme', $region->payment_info['card_theme'] ?? '') == 'green') ? 'selected' : '' }}>Hijau Syariah</option>
                                    </select>
                                </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Kolom Kanan: E-Wallet & Midtrans -->
                    <div class="col-md-6">
                        <!-- Cash Only (Bayar di Tempat) -->
                        <div class="card border border-success shadow-none mb-3 bg-label-success">
                            <div class="card-body p-3">
                                <h6 class="fw-bold text-success mb-2"><i class="bx bx-money me-1"></i>Pembayaran Di Tempat (Cash)</h6>
                                <p class="text-success small mb-3">Aktifkan jika wilayah Anda menerima pembayaran secara tunai (bayar langsung di tempat).</p>
                                <div class="form-check form-switch mb-0 d-flex align-items-center">
                                    <input class="form-check-input me-2" type="checkbox" name="cash_only_active" id="cash_only_active" value="1" style="width: 2.5em; height: 1.2em; cursor: pointer;" {{ (old('cash_only_active', $region->payment_info['cash_only_active'] ?? false)) ? 'checked' : '' }}>
                                    <label class="form-check-label fw-semibold text-success" for="cash_only_active" style="cursor: pointer;">Terima Tunai (Cash)</label>
                                </div>
                            </div>
                        </div>

                        <!-- E-Wallet (Opsional) -->
                        <div class="card border border-info shadow-none mb-3 bg-label-info">
                            <div class="card-body p-3">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h6 class="fw-bold text-info mb-0">Dompet Elektronik / E-Wallet (Opsional)</h6>
                                    <div class="form-check form-switch mb-0">
                                        <input class="form-check-input" type="checkbox" name="ewallet_active" id="ewallet_active" value="1" {{ (old('ewallet_active', $region->payment_info['ewallet_active'] ?? false)) ? 'checked' : '' }} onchange="document.getElementById('ewallet_fields').style.display = this.checked ? 'block' : 'none'">
                                        <label class="form-check-label fw-semibold text-info" for="ewallet_active">Aktifkan</label>
                                    </div>
                                </div>
                                
                                <div id="ewallet_fields" style="display: {{ (old('ewallet_active', $region->payment_info['ewallet_active'] ?? false)) ? 'block' : 'none' }}; border-top: 1px dashed #03c3ec; padding-top: 15px;">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold text-info d-block">Pilih E-Wallet</label>
                                    <div class="row g-2">
                                        @php $currentEwallet = old('ewallet_name', $region->payment_info['ewallet_name'] ?? ''); @endphp
                                        <div class="col-4">
                                            <label class="payment-logo-card border-info">
                                                <input type="radio" name="ewallet_name" value="DANA" class="payment-radio" {{ $currentEwallet == 'DANA' ? 'checked' : '' }}>
                                                <div class="logo-wrapper">
                                                    <img src="{{ asset('assets/img/payment_logos/dana.png') }}" alt="DANA" class="img-fluid payment-logo">
                                                </div>
                                            </label>
                                        </div>
                                        <div class="col-4">
                                            <label class="payment-logo-card border-info">
                                                <input type="radio" name="ewallet_name" value="OVO" class="payment-radio" {{ $currentEwallet == 'OVO' ? 'checked' : '' }}>
                                                <div class="logo-wrapper">
                                                    <img src="{{ asset('assets/img/payment_logos/ovo.png') }}" alt="OVO" class="img-fluid payment-logo">
                                                </div>
                                            </label>
                                        </div>
                                        <div class="col-4">
                                            <label class="payment-logo-card border-info">
                                                <input type="radio" name="ewallet_name" value="GoPay" class="payment-radio" {{ $currentEwallet == 'GoPay' ? 'checked' : '' }}>
                                                <div class="logo-wrapper">
                                                    <img src="{{ asset('assets/img/payment_logos/gopay.png') }}" alt="GoPay" class="img-fluid payment-logo">
                                                </div>
                                            </label>
                                        </div>
                                        <div class="col-4">
                                            <label class="payment-logo-card border-info">
                                                <input type="radio" name="ewallet_name" value="ShopeePay" class="payment-radio" {{ $currentEwallet == 'ShopeePay' ? 'checked' : '' }}>
                                                <div class="logo-wrapper">
                                                    <img src="{{ asset('assets/img/payment_logos/shopeepay.png') }}" alt="ShopeePay" class="img-fluid payment-logo">
                                                </div>
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-semibold text-info">Nomor HP / E-Wallet</label>
                                    <input type="text" name="ewallet_number" value="{{ old('ewallet_number', $region->payment_info['ewallet_number'] ?? '') }}" placeholder="0812345678" class="form-control border-info">
                                </div>

                                <div class="mb-1">
                                    <label class="form-label fw-semibold text-info">Atas Nama (A/N)</label>
                                    <input type="text" name="ewallet_account_name" value="{{ old('ewallet_account_name', $region->payment_info['ewallet_account_name'] ?? '') }}" class="form-control border-info">
                                </div>
                                </div>
                            </div>
                        </div>

                        <!-- Payment Gateway -->
                        {{-- Isi kartu ini menyesuaikan penyedia yang dinyalakan Super Admin.
                             Midtrans menuntut tiap wilayah punya akun sendiri, jadi kuncinya
                             diisi di sini. Xendit memakai kredensial induk, jadi wilayah cukup
                             punya sub-akun dan tidak perlu menyentuh kunci apa pun. --}}
                        <div class="card border border-warning shadow-none mb-3 bg-label-warning">
                            <div class="card-body p-3">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <h6 class="fw-bold text-warning mb-0">
                                        <i class="bx bx-bolt-circle me-1"></i>Pembayaran Otomatis
                                    </h6>
                                    <span class="badge bg-label-secondary rounded-pill">{{ $labelPenyedia }}</span>
                                </div>

                                {{-- Status kesiapan: menjawab "kenapa pembayaran otomatis saya belum jalan" --}}
                                <div class="alert {{ $kesiapan['siap'] ? 'alert-success' : 'alert-warning' }} p-2 mb-3" style="font-size: 0.82rem;">
                                    <i class="bx {{ $kesiapan['siap'] ? 'bx-check-circle' : 'bx-error-circle' }} me-1"></i>
                                    {{ $kesiapan['alasan'] }}
                                </div>

                                <div class="form-check form-switch mb-0 d-flex align-items-center">
                                    <input class="form-check-input me-2" type="checkbox" name="payment_gateway_active" id="payment_gateway_active" value="1"
                                           style="width: 2.5em; height: 1.2em; cursor: pointer;"
                                           {{ old('payment_gateway_active', $region->payment_info['payment_gateway_active'] ?? false) ? 'checked' : '' }}
                                           onchange="document.getElementById('gateway_fields').style.display = this.checked ? 'block' : 'none'">
                                    <label class="form-check-label fw-semibold text-warning" for="payment_gateway_active" style="cursor: pointer;">
                                        Aktifkan Pembayaran Otomatis
                                    </label>
                                </div>

                                <div id="gateway_fields" class="mt-3"
                                     style="display: {{ old('payment_gateway_active', $region->payment_info['payment_gateway_active'] ?? false) ? 'block' : 'none' }}; border-top: 1px dashed #ffab00; padding-top: 15px;">

                                    @if($kunciDiWilayah)
                                        {{-- MIDTRANS: satu akun merchant = satu rekening pencairan,
                                             jadi wilayah ini harus punya akun Midtrans sendiri. --}}
                                        <div class="alert alert-warning p-2 mb-3" style="font-size: 0.8rem;">
                                            <i class="bx bx-info-circle me-1"></i>
                                            Wilayah Anda memerlukan <strong>akun Midtrans sendiri</strong>, karena satu akun
                                            Midtrans hanya bisa mencairkan ke satu rekening. Dana akan masuk ke rekening
                                            yang terdaftar di akun tersebut.
                                        </div>

                                        <div class="d-flex flex-wrap gap-2 mb-3">
                                            <a href="https://dashboard.midtrans.com/register" target="_blank" rel="noopener noreferrer"
                                               class="btn btn-sm btn-outline-warning">
                                                <i class="bx bx-user-plus me-1"></i>Daftar Akun Midtrans
                                            </a>
                                            <a href="https://dashboard.midtrans.com/settings/config_info" target="_blank" rel="noopener noreferrer"
                                               class="btn btn-sm btn-outline-warning">
                                                <i class="bx bx-key me-1"></i>Ambil Kunci API
                                            </a>
                                        </div>

                                        @php
                                            $skAda = filled($region->payment_info['midtrans_server_key'] ?? null);
                                            $ckAda = filled($region->payment_info['midtrans_client_key'] ?? null);
                                        @endphp

                                        <div class="mb-2">
                                            <label class="form-label text-warning fw-semibold" style="font-size: 0.8rem;">Midtrans Server Key</label>
                                            <input type="password" name="midtrans_server_key" autocomplete="off"
                                                   class="form-control form-control-sm border-warning bg-white"
                                                   placeholder="{{ $skAda ? '•••••••• (tersimpan)' : 'Belum diisi' }}">
                                        </div>
                                        <div class="mb-1">
                                            <label class="form-label text-warning fw-semibold" style="font-size: 0.8rem;">Midtrans Client Key</label>
                                            <input type="password" name="midtrans_client_key" autocomplete="off"
                                                   class="form-control form-control-sm border-warning bg-white"
                                                   placeholder="{{ $ckAda ? '•••••••• (tersimpan)' : 'Belum diisi' }}">
                                        </div>
                                        <small class="text-warning d-block mb-2" style="font-size: 0.72rem;">
                                            Kosongkan bila tidak ingin mengubah kunci yang sudah tersimpan.
                                        </small>

                                        @php
                                            $modeProduksi = (bool) ($region->payment_info['midtrans_is_production'] ?? false);
                                        @endphp

                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" role="switch"
                                                   id="midtransModeProduksi" name="midtrans_is_production" value="1"
                                                   {{ $modeProduksi ? 'checked' : '' }}>
                                            <label class="form-check-label fw-semibold" for="midtransModeProduksi"
                                                   style="font-size: 0.8rem;">
                                                Mode Production
                                                <span class="badge bg-{{ $modeProduksi ? 'success' : 'secondary' }} ms-1">
                                                    {{ $modeProduksi ? 'PRODUCTION' : 'SANDBOX' }}
                                                </span>
                                            </label>
                                        </div>
                                        <small class="text-warning d-block" style="font-size: 0.72rem;">
                                            Harus cocok dengan kunci di atas: kunci Sandbox hanya jalan di mode Sandbox,
                                            kunci Production hanya jalan di mode Production. Nyalakan hanya kalau
                                            wilayah Anda memang sudah siap menerima uang sungguhan.
                                        </small>
                                    @else
                                        {{-- XENDIT: kredensial induk dipegang Diskominfotik. --}}
                                        <div class="alert alert-warning p-2 mb-0" style="font-size: 0.8rem;">
                                            <i class="bx bx-check-shield me-1"></i>
                                            Pembayaran otomatis disiapkan oleh <strong>Diskominfotik</strong> lewat {{ $labelPenyedia }}.
                                            Anda tidak perlu memasukkan kunci API apa pun.
                                            Pastikan <strong>nomor rekening di sebelah kiri sudah benar</strong>,
                                            karena ke situlah pemasukan wilayah Anda diteruskan.
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-5 border-top pt-4 pb-4 mb-2 text-center">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="bx bx-save me-1"></i> Simpan Pengaturan Pembayaran
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
    .bg-label-info {
        background-color: rgba(3, 195, 236, 0.08) !important;
    }
    .bg-label-warning {
        background-color: rgba(255, 171, 0, 0.08) !important;
    }
    .bg-label-success {
        background-color: rgba(113, 221, 55, 0.08) !important;
    }
    
    .atm-card-preview {
        perspective: 1000px;
        width: 100%;
        max-width: 400px;
        margin: 0 auto;
    }
    .atm-card-inner {
        position: relative;
        width: 100%;
        padding-top: 56.25%; /* 16:9 Aspect Ratio */
        transition: transform 0.6s;
        transform-style: preserve-3d;
        border-radius: 15px;
        box-shadow: 0 10px 20px rgba(0,0,0,0.15);
    }
    .atm-card-front {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        backface-visibility: hidden;
        border-radius: 15px;
        padding: 20px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }
    
    .theme-blue { background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%) !important; }
    .theme-gold { background: linear-gradient(135deg, #bf953f 0%, #fcf6ba 25%, #b38728 50%, #fbf5b7 75%, #aa771c 100%) !important; }
    .theme-dark { background: linear-gradient(135deg, #232526 0%, #414345 100%) !important; }
    .theme-green { background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%) !important; }

    .payment-logo-card {
        display: block;
        cursor: pointer;
        height: 100%;
        margin-bottom: 0;
    }
    .payment-radio {
        display: none;
    }
    .logo-wrapper {
        border: 2px solid #e7e7e7;
        border-radius: 8px;
        padding: 5px 10px;
        height: 50px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s ease-in-out;
        background-color: #fff;
    }
    .payment-logo-card:hover .logo-wrapper {
        border-color: #b1b1b1;
    }
    .payment-radio:checked + .logo-wrapper {
        border-color: #696cff;
        box-shadow: 0 0 0 1px #696cff;
    }
    .payment-logo-card.border-info .payment-radio:checked + .logo-wrapper {
        border-color: #03c3ec;
        box-shadow: 0 0 0 1px #03c3ec;
    }
    .payment-logo {
        max-height: 32px;
        max-width: 100%;
        object-fit: contain;
        filter: grayscale(100%);
        opacity: 0.6;
        transition: all 0.2s ease-in-out;
    }
    .logo-wrapper span {
        filter: grayscale(100%);
        opacity: 0.6;
        transition: all 0.2s ease-in-out;
    }
    .payment-radio:checked + .logo-wrapper .payment-logo,
    .payment-radio:checked + .logo-wrapper span {
        filter: grayscale(0%);
        opacity: 1;
    }
</style>

<script>
    function updateCardPreview() {
        const checkedBank = document.querySelector('input[name="bank_name"]:checked');
        const bankName = checkedBank ? checkedBank.value : 'BANK / E-WALLET';
        let accNum = document.getElementById('account_number').value || '0000 0000 0000 0000';
        const accName = document.getElementById('account_name').value || 'PEMILIK';
        const theme = document.getElementById('card_theme').value;

        // Format account number to look like card (groups of 4)
        accNum = accNum.replace(/\s/g, '').replace(/(.{4})/g, '$1 ').trim();

        document.getElementById('previewBankName').innerText = bankName;
        document.getElementById('previewAccountNumber').innerText = accNum;
        document.getElementById('previewAccountName').innerText = accName;

        // Update theme
        const cardFront = document.getElementById('atmCardFront');
        cardFront.className = 'atm-card-front theme-' + theme;
    }

    // Initialize on load
    document.addEventListener('DOMContentLoaded', function() {
        updateCardPreview();
    });
</script>
@endsection
