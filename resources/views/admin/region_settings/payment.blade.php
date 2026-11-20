@extends('admin.layouts.admin')

@section('title', 'Pengaturan Pembayaran Wilayah')
@section('page-title', 'Pengaturan Pembayaran Wilayah')

@section('content')
<style>
    .pay-status-pill {
        border-radius: 12px;
        border: 1px solid #eef1f6;
        background: #fff;
        padding: 0.75rem 1rem;
        transition: all 0.2s ease;
    }
    .pay-method-card {
        border-radius: 16px;
        border: 1.5px solid #eef1f6;
        background: #fff;
        transition: border-color 0.2s ease, box-shadow 0.2s ease;
    }
    .pay-method-card:hover {
        box-shadow: 0 6px 18px rgba(67, 89, 113, 0.06);
    }
    .atm-card-preview {
        width: 100%;
        max-width: 390px;
        margin: 0 auto;
    }
    .atm-card-inner {
        position: relative;
        width: 100%;
        padding-top: 58%;
        border-radius: 16px;
        box-shadow: 0 10px 24px rgba(30, 60, 114, 0.2);
        overflow: hidden;
    }
    .atm-card-front {
        position: absolute;
        inset: 0;
        padding: 1.15rem 1.25rem;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        color: #fff;
    }
    .atm-number-text {
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
        letter-spacing: 2px;
        font-size: clamp(0.95rem, 2.5vw, 1.25rem);
        text-shadow: 0 1px 2px rgba(0, 0, 0, 0.35);
        word-break: break-word;
    }
    .theme-blue { background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%) !important; }
    .theme-gold { background: linear-gradient(135deg, #9a6e1c 0%, #d4af37 50%, #8f6517 100%) !important; }
    .theme-dark { background: linear-gradient(135deg, #1f2937 0%, #374151 100%) !important; }
    .theme-green { background: linear-gradient(135deg, #0f766e 0%, #10b981 100%) !important; }

    .payment-logo-card {
        display: block;
        cursor: pointer;
        margin-bottom: 0;
        position: relative;
    }
    .payment-radio {
        display: none;
    }
    .logo-wrapper {
        border: 1.5px solid #e4e7ec;
        border-radius: 10px;
        padding: 6px 10px;
        height: 50px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s ease;
        background-color: #fff;
        position: relative;
    }
    .payment-logo-card:hover .logo-wrapper {
        border-color: #b4b9c8;
    }
    .payment-radio:checked + .logo-wrapper {
        border-color: #696cff;
        background-color: rgba(105, 108, 255, 0.05);
        box-shadow: 0 0 0 2px rgba(105, 108, 255, 0.18);
    }
    .payment-logo-card.border-info .payment-radio:checked + .logo-wrapper {
        border-color: #03c3ec;
        background-color: rgba(3, 195, 236, 0.05);
        box-shadow: 0 0 0 2px rgba(3, 195, 236, 0.18);
    }
    .payment-logo {
        max-height: 28px;
        max-width: 100%;
        object-fit: contain;
        filter: grayscale(100%);
        opacity: 0.6;
        transition: all 0.2s ease;
    }
    .payment-radio:checked + .logo-wrapper .payment-logo {
        filter: grayscale(0%);
        opacity: 1;
    }
    .logo-check-dot {
        position: absolute;
        top: -6px;
        right: -6px;
        width: 18px;
        height: 18px;
        border-radius: 50%;
        background: #696cff;
        color: #fff;
        font-size: 0.7rem;
        display: none;
        align-items: center;
        justify-content: center;
        box-shadow: 0 2px 5px rgba(0,0,0,0.15);
    }
    .payment-logo-card.border-info .logo-check-dot {
        background: #03c3ec;
    }
    .payment-radio:checked + .logo-wrapper .logo-check-dot {
        display: flex;
    }
</style>

@php
    $bankActive = (bool) old('bank_active', $region->payment_info['bank_active'] ?? true);
    $cashActive = (bool) old('cash_only_active', $region->payment_info['cash_only_active'] ?? false);
    $ewalletActive = (bool) old('ewallet_active', $region->payment_info['ewallet_active'] ?? false);
    $currentBank = old('bank_name', $region->payment_info['bank_name'] ?? '');
    $currentEwallet = old('ewallet_name', $region->payment_info['ewallet_name'] ?? '');
    $currentTheme = old('card_theme', $region->payment_info['card_theme'] ?? 'blue');

    $bankOptions = [
        ['value' => 'BSI', 'logo' => 'bsi.png'],
        ['value' => 'BRK Syariah', 'logo' => 'brksyariah.png'],
        ['value' => 'Mandiri', 'logo' => 'mandiri.png'],
        ['value' => 'BRI', 'logo' => 'bri.png'],
        ['value' => 'BNI', 'logo' => 'bni.png'],
        ['value' => 'BCA', 'logo' => 'bca.jpg'],
    ];

    $ewalletOptions = [
        ['value' => 'DANA', 'logo' => 'dana.png'],
        ['value' => 'OVO', 'logo' => 'ovo.png'],
        ['value' => 'GoPay', 'logo' => 'gopay.png'],
        ['value' => 'ShopeePay', 'logo' => 'shopeepay.png'],
    ];
@endphp

<div class="container-xxl flex-grow-1 container-p-y">
    <!-- Page Header -->
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-3 mb-4">
        <div>
            <h4 class="fw-bold mb-1">
                <span class="text-muted fw-light">Pengaturan Desa /</span> Pembayaran Wilayah
            </h4>
            <p class="text-muted small mb-0">Atur rekening tujuan transfer manual dan opsi pembayaran tunai untuk warga {{ $region->name }}.</p>
        </div>
    </div>

    <!-- Flash Alerts -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible shadow-sm rounded-4 border-0 d-flex align-items-center mb-4" role="alert">
            <i class="bx bx-check-circle fs-4 me-2 flex-shrink-0"></i>
            <div>{{ session('success') }}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible shadow-sm rounded-4 border-0 d-flex align-items-center mb-4" role="alert">
            <i class="bx bx-error-circle fs-4 me-2 flex-shrink-0"></i>
            <div>{{ session('error') }}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible shadow-sm rounded-4 border-0 mb-4" role="alert">
            <ul class="mb-0 ps-3 small">
                @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Info Banner -->
    <div class="card bg-label-primary border-0 shadow-none rounded-4 mb-4">
        <div class="card-body p-3 p-md-4 d-flex align-items-start gap-3">
            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 shadow-sm" style="width: 44px; height: 44px;">
                <i class="bx bx-credit-card-front fs-4"></i>
            </div>
            <div>
                <h6 class="fw-bold text-primary mb-1">Metode Pembayaran Langsung ke Desa</h6>
                <p class="mb-0 text-primary small" style="opacity: 0.9; line-height: 1.5;">
                    Seluruh pembayaran warga dilakukan secara langsung melalui <strong>Transfer Bank Manual</strong>, <strong>Tunai (Cash)</strong>, atau <strong>E-Wallet</strong> yang Anda aktifkan di bawah ini. Pastikan minimal satu metode aktif agar warga dapat menyelesaikan pesanan.
                </p>
            </div>
        </div>
    </div>

    <!-- Live Status Ringkas -->
    <div class="row g-2 g-md-3 mb-4">
        <div class="col-12 col-sm-4">
            <div class="pay-status-pill shadow-xs d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2">
                    <i class="bx bxs-bank text-primary fs-5"></i>
                    <span class="fw-semibold small text-dark">Transfer Bank</span>
                </div>
                <span id="badge-status-bank" class="badge rounded-pill {{ $bankActive ? 'bg-label-success' : 'bg-label-secondary' }}">
                    {{ $bankActive ? 'Aktif' : 'Nonaktif' }}
                </span>
            </div>
        </div>
        <div class="col-12 col-sm-4">
            <div class="pay-status-pill shadow-xs d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2">
                    <i class="bx bx-money text-success fs-5"></i>
                    <span class="fw-semibold small text-dark">Tunai (Cash)</span>
                </div>
                <span id="badge-status-cash" class="badge rounded-pill {{ $cashActive ? 'bg-label-success' : 'bg-label-secondary' }}">
                    {{ $cashActive ? 'Aktif' : 'Nonaktif' }}
                </span>
            </div>
        </div>
        <div class="col-12 col-sm-4">
            <div class="pay-status-pill shadow-xs d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2">
                    <i class="bx bx-wallet-alt text-info fs-5"></i>
                    <span class="fw-semibold small text-dark">E-Wallet</span>
                </div>
                <span id="badge-status-ewallet" class="badge rounded-pill {{ $ewalletActive ? 'bg-label-success' : 'bg-label-secondary' }}">
                    {{ $ewalletActive ? 'Aktif' : 'Nonaktif' }}
                </span>
            </div>
        </div>
    </div>

    <form action="{{ route('admin.region-settings.payment.update') }}" method="POST">
        @csrf
        @method('PUT')

        <div class="row g-4">
            <!-- KOLOM KIRI: TRANSFER BANK MANUAL -->
            <div class="col-12 col-lg-6">
                <div class="card pay-method-card shadow-sm h-100">
                    <div class="card-header bg-white border-bottom py-3 px-3 px-md-4 d-flex justify-content-between align-items-center gap-2">
                        <div class="d-flex align-items-center gap-2">
                            <span class="avatar avatar-sm bg-label-primary text-primary rounded-3 d-flex align-items-center justify-content-center">
                                <i class="bx bxs-bank fs-5"></i>
                            </span>
                            <div>
                                <h6 class="fw-bold mb-0 text-dark">Transfer Bank Manual</h6>
                                <small class="text-muted">Rekening utama desa / BUMDes</small>
                            </div>
                        </div>
                        <div class="form-check form-switch mb-0">
                            <input class="form-check-input" type="checkbox" name="bank_active" id="bank_active" value="1"
                                   style="width: 2.6em; height: 1.3em; cursor: pointer;"
                                   {{ $bankActive ? 'checked' : '' }}
                                   onchange="syncPaymentToggles()">
                        </div>
                    </div>

                    <div class="card-body p-3 p-md-4" id="bank_fields" style="opacity: {{ $bankActive ? '1' : '0.45' }}; transition: opacity 0.25s ease;">
                        <!-- Live ATM Card Preview -->
                        <div class="mb-4">
                            <div class="atm-card-preview" id="atmCardPreview">
                                <div class="atm-card-inner">
                                    <div class="atm-card-front theme-{{ $currentTheme }}" id="atmCardFront">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <i class="bx bx-chip text-warning" style="font-size: 2.1rem;"></i>
                                            <span class="fw-bold text-white fs-5 fst-italic" id="previewBankName">{{ $currentBank ?: 'PILIH BANK' }}</span>
                                        </div>
                                        <div class="text-center my-2 atm-number-text" id="previewAccountNumber">0000 0000 0000 0000</div>
                                        <div class="d-flex justify-content-between align-items-end">
                                            <div class="overflow-hidden pe-2">
                                                <small class="text-white-50 d-block" style="font-size: 0.65rem; letter-spacing: 0.5px;">ATAS NAMA</small>
                                                <span class="text-white fw-semibold text-uppercase small text-truncate d-block" id="previewAccountName">PEMILIK REKENING</span>
                                            </div>
                                            <i class="bx bx-wifi bx-rotate-90 text-white-50 fs-5 flex-shrink-0"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold small text-dark d-block">Pilih Bank</label>
                            <div class="row g-2">
                                @foreach($bankOptions as $bank)
                                <div class="col-4">
                                    <label class="payment-logo-card">
                                        <input type="radio" name="bank_name" value="{{ $bank['value'] }}" class="payment-radio" onchange="updateCardPreview()" {{ $currentBank == $bank['value'] ? 'checked' : '' }}>
                                        <div class="logo-wrapper">
                                            <span class="logo-check-dot"><i class="bx bx-check"></i></span>
                                            <img src="{{ asset('assets/img/payment_logos/' . $bank['logo']) }}" alt="{{ $bank['value'] }}" class="img-fluid payment-logo">
                                        </div>
                                    </label>
                                </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold small text-dark" for="account_number">Nomor Rekening</label>
                            <div class="input-group input-group-merge">
                                <span class="input-group-text bg-light"><i class="bx bx-hash text-muted"></i></span>
                                <input type="text" name="account_number" id="account_number"
                                       value="{{ old('account_number', $region->payment_info['account_number'] ?? '') }}"
                                       class="form-control" placeholder="Contoh: 7123456789" oninput="updateCardPreview()">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold small text-dark" for="account_name">Atas Nama (A/N)</label>
                            <div class="input-group input-group-merge">
                                <span class="input-group-text bg-light"><i class="bx bx-user text-muted"></i></span>
                                <input type="text" name="account_name" id="account_name"
                                       value="{{ old('account_name', $region->payment_info['account_name'] ?? '') }}"
                                       class="form-control" placeholder="Contoh: BUMDes Pematang Duku" oninput="updateCardPreview()">
                            </div>
                        </div>

                        <div class="mb-0">
                            <label class="form-label fw-semibold small text-dark" for="card_theme">Warna Tampilan Kartu</label>
                            <select name="card_theme" id="card_theme" class="form-select" onchange="updateCardPreview()">
                                <option value="blue" {{ $currentTheme == 'blue' ? 'selected' : '' }}>Biru Klasik</option>
                                <option value="gold" {{ $currentTheme == 'gold' ? 'selected' : '' }}>Emas Premium</option>
                                <option value="dark" {{ $currentTheme == 'dark' ? 'selected' : '' }}>Hitam Elegan</option>
                                <option value="green" {{ $currentTheme == 'green' ? 'selected' : '' }}>Hijau Syariah</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- KOLOM KANAN: TUNAI (CASH) & E-WALLET (OPSIONAL) -->
            <div class="col-12 col-lg-6 d-flex flex-column gap-4">
                <!-- Pembayaran Tunai (Cash) -->
                <div class="card pay-method-card shadow-sm">
                    <div class="card-header bg-white border-bottom py-3 px-3 px-md-4 d-flex justify-content-between align-items-center gap-2">
                        <div class="d-flex align-items-center gap-2">
                            <span class="avatar avatar-sm bg-label-success text-success rounded-3 d-flex align-items-center justify-content-center">
                                <i class="bx bx-money fs-5"></i>
                            </span>
                            <div>
                                <h6 class="fw-bold mb-0 text-dark">Pembayaran Tunai (Cash)</h6>
                                <small class="text-muted">Bayar langsung di kantor / tempat</small>
                            </div>
                        </div>
                        <div class="form-check form-switch mb-0">
                            <input class="form-check-input" type="checkbox" name="cash_only_active" id="cash_only_active" value="1"
                                   style="width: 2.6em; height: 1.3em; cursor: pointer;"
                                   {{ $cashActive ? 'checked' : '' }}
                                   onchange="syncPaymentToggles()">
                        </div>
                    </div>
                    <div class="card-body p-3 p-md-4">
                        <div class="rounded-3 p-3 bg-label-success small text-success d-flex align-items-start gap-2">
                            <i class="bx bx-check-circle fs-5 flex-shrink-0 mt-1"></i>
                            <div>
                                Jika diaktifkan, warga dapat memilih opsi <strong>Bayar Tunai / Cash</strong> saat memesan layanan, lalu menyerahkan uang tunai langsung kepada petugas desa.
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Dompet Elektronik / E-Wallet (Opsional) -->
                <div class="card pay-method-card shadow-sm flex-grow-1">
                    <div class="card-header bg-white border-bottom py-3 px-3 px-md-4 d-flex justify-content-between align-items-center gap-2">
                        <div class="d-flex align-items-center gap-2">
                            <span class="avatar avatar-sm bg-label-info text-info rounded-3 d-flex align-items-center justify-content-center">
                                <i class="bx bx-wallet-alt fs-5"></i>
                            </span>
                            <div>
                                <h6 class="fw-bold mb-0 text-dark">E-Wallet <span class="badge bg-label-secondary rounded-pill ms-1" style="font-size: 0.68rem;">Opsional</span></h6>
                                <small class="text-muted">Transfer manual ke DANA, OVO, GoPay, ShopeePay</small>
                            </div>
                        </div>
                        <div class="form-check form-switch mb-0">
                            <input class="form-check-input" type="checkbox" name="ewallet_active" id="ewallet_active" value="1"
                                   style="width: 2.6em; height: 1.3em; cursor: pointer;"
                                   {{ $ewalletActive ? 'checked' : '' }}
                                   onchange="syncPaymentToggles()">
                        </div>
                    </div>

                    <div class="card-body p-3 p-md-4">
                        <div id="ewallet_off_note" class="text-muted small {{ $ewalletActive ? 'd-none' : '' }}">
                            <i class="bx bx-info-circle me-1"></i>
                            Aktifkan tombol di kanan atas bila desa juga menerima transfer manual via dompet elektronik (E-Wallet).
                        </div>

                        <div id="ewallet_fields" style="display: {{ $ewalletActive ? 'block' : 'none' }};">
                            <div class="mb-3">
                                <label class="form-label fw-semibold small text-dark d-block">Pilih Layanan E-Wallet</label>
                                <div class="row g-2">
                                    @foreach($ewalletOptions as $ew)
                                    <div class="col-6 col-sm-3 col-lg-6 col-xl-3">
                                        <label class="payment-logo-card border-info">
                                            <input type="radio" name="ewallet_name" value="{{ $ew['value'] }}" class="payment-radio" {{ $currentEwallet == $ew['value'] ? 'checked' : '' }}>
                                            <div class="logo-wrapper">
                                                <span class="logo-check-dot"><i class="bx bx-check"></i></span>
                                                <img src="{{ asset('assets/img/payment_logos/' . $ew['logo']) }}" alt="{{ $ew['value'] }}" class="img-fluid payment-logo">
                                            </div>
                                        </label>
                                    </div>
                                    @endforeach
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold small text-dark" for="ewallet_number">Nomor HP / E-Wallet</label>
                                <div class="input-group input-group-merge">
                                    <span class="input-group-text bg-light"><i class="bx bx-phone text-muted"></i></span>
                                    <input type="text" id="ewallet_number" name="ewallet_number"
                                           value="{{ old('ewallet_number', $region->payment_info['ewallet_number'] ?? '') }}"
                                           placeholder="Contoh: 081234567890" class="form-control">
                                </div>
                            </div>

                            <div class="mb-0">
                                <label class="form-label fw-semibold small text-dark" for="ewallet_account_name">Atas Nama (A/N)</label>
                                <div class="input-group input-group-merge">
                                    <span class="input-group-text bg-light"><i class="bx bx-user text-muted"></i></span>
                                    <input type="text" id="ewallet_account_name" name="ewallet_account_name"
                                           value="{{ old('ewallet_account_name', $region->payment_info['ewallet_account_name'] ?? '') }}"
                                           placeholder="Nama pemilik akun E-Wallet" class="form-control">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Submit Footer Card -->
        <div class="card border-0 shadow-sm rounded-4 mt-4">
            <div class="card-body p-3 p-md-4 d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-3">
                <small class="text-muted">
                    <i class="bx bx-shield-quarter text-primary me-1"></i>
                    Perubahan akan langsung diterapkan pada halaman pembayaran warga.
                </small>
                <button type="submit" class="btn btn-primary btn-lg rounded-pill px-5 shadow-sm w-100 w-sm-auto">
                    <i class="bx bx-save me-1"></i> Simpan Pengaturan Pembayaran
                </button>
            </div>
        </div>
    </form>
</div>

<script>
    function updateCardPreview() {
        const checkedBank = document.querySelector('input[name="bank_name"]:checked');
        const bankName = checkedBank ? checkedBank.value : 'PILIH BANK';
        let accNum = document.getElementById('account_number')?.value || '0000 0000 0000 0000';
        const accName = document.getElementById('account_name')?.value || 'PEMILIK REKENING';
        const theme = document.getElementById('card_theme')?.value || 'blue';

        accNum = accNum.replace(/\s/g, '').replace(/(.{4})/g, '$1 ').trim();

        const elBank = document.getElementById('previewBankName');
        const elNum = document.getElementById('previewAccountNumber');
        const elName = document.getElementById('previewAccountName');
        const cardFront = document.getElementById('atmCardFront');

        if (elBank) elBank.innerText = bankName;
        if (elNum) elNum.innerText = accNum;
        if (elName) elName.innerText = accName;
        if (cardFront) cardFront.className = 'atm-card-front theme-' + theme;
    }

    function syncPaymentToggles() {
        const bankChecked = document.getElementById('bank_active')?.checked ?? false;
        const cashChecked = document.getElementById('cash_only_active')?.checked ?? false;
        const ewChecked = document.getElementById('ewallet_active')?.checked ?? false;

        const bankFields = document.getElementById('bank_fields');
        if (bankFields) bankFields.style.opacity = bankChecked ? '1' : '0.45';

        const ewFields = document.getElementById('ewallet_fields');
        const ewNote = document.getElementById('ewallet_off_note');
        if (ewFields) ewFields.style.display = ewChecked ? 'block' : 'none';
        if (ewNote) ewNote.classList.toggle('d-none', ewChecked);

        const setBadge = (id, active) => {
            const el = document.getElementById(id);
            if (!el) return;
            el.textContent = active ? 'Aktif' : 'Nonaktif';
            el.className = 'badge rounded-pill ' + (active ? 'bg-label-success' : 'bg-label-secondary');
        };
        setBadge('badge-status-bank', bankChecked);
        setBadge('badge-status-cash', cashChecked);
        setBadge('badge-status-ewallet', ewChecked);
    }

    document.addEventListener('DOMContentLoaded', function() {
        updateCardPreview();
        syncPaymentToggles();
    });
</script>
@endsection
