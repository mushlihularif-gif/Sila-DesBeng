@extends('admin.layouts.admin')

@section('title', 'Keuangan Wilayah')
@section('page-title', 'Keuangan Wilayah')

@section('content')
@php
    $petaLogo = [
        'BSI' => 'bsi.png', 'BRK SYARIAH' => 'brksyariah.png', 'MANDIRI' => 'mandiri.png',
        'BRI' => 'bri.png', 'BNI' => 'bni.png', 'BCA' => 'bca.jpg',
        'DANA' => 'dana.png', 'OVO' => 'ovo.png', 'GOPAY' => 'gopay.png', 'SHOPEEPAY' => 'shopeepay.png',
    ];
    $cariLogo = function (?string $nama) use ($petaLogo) {
        if (! $nama) return null;
        $kunci = strtoupper(trim($nama));
        return isset($petaLogo[$kunci]) ? asset('assets/img/payment_logos/' . $petaLogo[$kunci]) : null;
    };

    $hasLegacyGatewayData = ($saldo['tersedia'] ?? 0) > 0
        || ($saldo['tertahan'] ?? 0) > 0
        || ($saldo['sedang_ditarik'] ?? 0) > 0
        || ($totalPemasukan ?? 0) > 0
        || $riwayatPenarikan->isNotEmpty()
        || $riwayatPemasukan->isNotEmpty();
@endphp

<div class="container-xxl flex-grow-1 container-p-y">
    <!-- Page Header -->
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-3 mb-4">
        <div>
            <h4 class="fw-bold mb-1">
                <span class="text-muted fw-light">Tata Kelola Desa /</span> Keuangan Wilayah
            </h4>
            <p class="text-muted small mb-0">Pusat pengelolaan pembayaran langsung dan arsip keuangan {{ $region->name }}.</p>
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

    <!-- Penjelasan Sistem Pembayaran Langsung (Tanpa Payment Gateway) -->
    <div class="card bg-label-primary border-0 shadow-none rounded-4 mb-4">
        <div class="card-body p-3 p-md-4 d-flex align-items-start gap-3">
            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 shadow-sm" style="width: 44px; height: 44px;">
                <i class="bx bx-info-circle fs-4"></i>
            </div>
            <div>
                <h6 class="fw-bold text-primary mb-1">Pembayaran Langsung ke Rekening &amp; Kas Desa</h6>
                <p class="mb-0 text-primary small" style="opacity: 0.92; line-height: 1.5;">
                    Sistem saat ini <strong>tidak menggunakan payment gateway</strong>. Seluruh pembayaran warga melalui <strong>Transfer Bank</strong>, <strong>E-Wallet</strong>, maupun <strong>Tunai (Cash)</strong> langsung masuk ke rekening/kas desa Anda tanpa perlu penarikan saldo dari pusat.
                </p>
            </div>
        </div>
    </div>

    <!-- Menu Keuangan Manual yang Digunakan Sehari-hari -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-md-4">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-3 p-md-4 d-flex flex-column">
                    <div class="avatar bg-label-primary text-primary rounded-3 d-flex align-items-center justify-content-center mb-3" style="width: 44px; height: 44px;">
                        <i class="bx bx-credit-card fs-4"></i>
                    </div>
                    <h6 class="fw-bold text-dark mb-1">Rekening &amp; Metode Bayar</h6>
                    <p class="text-muted small mb-3 flex-grow-1">Atur nomor rekening bank tujuan transfer, pembayaran tunai (cash), serta E-Wallet desa.</p>
                    <a href="{{ route('admin.region-settings.payment') }}" class="btn btn-outline-primary rounded-pill btn-sm w-100">
                        Buka Pengaturan Pembayaran <i class="bx bx-right-arrow-alt ms-1"></i>
                    </a>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-4">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-3 p-md-4 d-flex flex-column">
                    <div class="avatar bg-label-success text-success rounded-3 d-flex align-items-center justify-content-center mb-3" style="width: 44px; height: 44px;">
                        <i class="bx bx-line-chart fs-4"></i>
                    </div>
                    <h6 class="fw-bold text-dark mb-1">Laporan Pendapatan</h6>
                    <p class="text-muted small mb-3 flex-grow-1">Pantau total uang masuk dari transfer/tunai terverifikasi serta catat pemasukan manual desa.</p>
                    <a href="{{ route('admin.laporan.pendapatan') }}" class="btn btn-outline-success rounded-pill btn-sm w-100">
                        Buka Laporan Pendapatan <i class="bx bx-right-arrow-alt ms-1"></i>
                    </a>
                </div>
            </div>
        </div>

        @if(auth()->user()->role === 'admin_desa')
        <div class="col-12 col-md-4">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-3 p-md-4 d-flex flex-column">
                    <div class="avatar bg-label-warning text-warning rounded-3 d-flex align-items-center justify-content-center mb-3" style="width: 44px; height: 44px;">
                        <i class="bx bx-undo fs-4"></i>
                    </div>
                    <h6 class="fw-bold text-dark mb-1">Pengembalian Dana Warga</h6>
                    <p class="text-muted small mb-3 flex-grow-1">Catat dan unggah bukti pengembalian dana untuk pesanan lunas yang dibatalkan warga.</p>
                    <a href="{{ route('admin.refunds.index') }}" class="btn btn-outline-warning rounded-pill btn-sm w-100">
                        Periksa Pengembalian Dana <i class="bx bx-right-arrow-alt ms-1"></i>
                    </a>
                </div>
            </div>
        </div>
        @endif
    </div>

    <!-- Arsip Saldo Pembayaran Otomatis (Legacy) - Hanya tampil terbuka jika ada sisa saldo/riwayat lama -->
    @if(!$hasLegacyGatewayData)
        <div class="text-center mb-3">
            <button type="button" class="btn btn-sm btn-link text-muted text-decoration-none" data-bs-toggle="collapse" data-bs-target="#legacyGatewaySection">
                <i class="bx bx-archive me-1"></i>Lihat Arsip Saldo Pembayaran Otomatis Terdahulu (Rp 0)
            </button>
        </div>
    @endif

    <div class="collapse {{ $hasLegacyGatewayData ? 'show' : '' }}" id="legacyGatewaySection">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <h6 class="fw-bold text-muted text-uppercase small mb-0" style="letter-spacing: 0.6px;">
                <i class="bx bx-archive me-1"></i>Arsip Saldo Pembayaran Otomatis (Terdahulu)
            </h6>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-lg-7">
                <div class="card border-0 shadow-sm rounded-4 h-100 text-white position-relative overflow-hidden"
                     style="background: linear-gradient(135deg, #0f9d58 0%, #34d399 100%);">
                    <div class="position-absolute rounded-circle"
                         style="width: 240px; height: 240px; right: -80px; top: -100px; background: rgba(255,255,255,0.10); pointer-events: none;"></div>
                    <div class="card-body p-3 p-md-4 position-relative">
                        <div class="d-flex align-items-start justify-content-between mb-1">
                            <small class="text-uppercase fw-semibold" style="letter-spacing: 0.8px; opacity: 0.85;">
                                Sisa Saldo Otomatis Bisa Ditarik
                            </small>
                            <span class="ikon-bulat rounded-circle" style="width: 40px; height: 40px; background: rgba(255,255,255,0.18);">
                                <i class="bx bx-wallet fs-4"></i>
                            </span>
                        </div>

                        <h2 class="fw-bold text-white mb-1">Rp {{ number_format($saldo['tersedia'], 0, ',', '.') }}</h2>
                        <small style="opacity: 0.85;">Wilayah {{ $region->name }}</small>

                        <div class="d-flex flex-wrap gap-2 mt-3">
                            <span class="d-inline-flex align-items-center rounded-pill px-3 py-1" style="background: rgba(255,255,255,0.18); font-size: 0.78rem;">
                                <i class="bx bx-time-five me-1"></i>Tertahan Rp {{ number_format($saldo['tertahan'], 0, ',', '.') }}
                            </span>
                            <span class="d-inline-flex align-items-center rounded-pill px-3 py-1" style="background: rgba(255,255,255,0.18); font-size: 0.78rem;">
                                <i class="bx bx-loader-circle me-1"></i>Diajukan Rp {{ number_format($saldo['sedang_ditarik'], 0, ',', '.') }}
                            </span>
                        </div>

                        <hr style="border-color: rgba(255,255,255,0.25); opacity: 1;" class="my-3">

                        @if($penarikanBerjalan)
                            <div class="rounded-3 p-3" style="background: rgba(255,255,255,0.18);">
                                <div class="fw-semibold mb-1">
                                    Pengajuan Rp {{ number_format($penarikanBerjalan->jumlah, 0, ',', '.') }} — {{ $penarikanBerjalan->labelStatus() }}
                                </div>
                                <small class="d-block mb-2" style="opacity: 0.9;">
                                    Diajukan {{ optional($penarikanBerjalan->diajukan_pada)->format('d M Y, H:i') }} WIB
                                </small>
                                @if($penarikanBerjalan->bisaDibatalkanWilayah())
                                    <form action="{{ route('admin.keuangan.batal', $penarikanBerjalan) }}" method="POST"
                                          data-konfirmasi="Batalkan pengajuan ini? Saldo akan kembali tersedia dan Anda bisa mengajukan ulang.">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-light rounded-pill px-3 fw-semibold text-success">
                                            <i class="bx bx-x-circle me-1"></i> Batalkan Pengajuan
                                        </button>
                                    </form>
                                @else
                                    <small style="opacity: 0.9;"><i class="bx bx-lock-alt me-1"></i>Sudah mulai ditransfer — tidak bisa dibatalkan sendiri.</small>
                                @endif
                            </div>
                        @elseif($saldo['tersedia'] < $minimalPenarikan)
                            <small class="d-flex align-items-center rounded-3 p-2 px-3" style="background: rgba(255,255,255,0.16);">
                                <i class="bx bx-info-circle fs-5 me-2"></i>
                                Minimal penarikan sisa saldo lama adalah Rp {{ number_format($minimalPenarikan, 0, ',', '.') }}.
                            </small>
                        @else
                            <button type="button" class="btn btn-light rounded-pill shadow-sm px-4 fw-bold text-success"
                                    data-bs-toggle="modal" data-bs-target="#modalTarikSaldo">
                                <i class="bx bx-money me-1"></i> Tarik Sisa Saldo
                            </button>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-body p-3 p-md-4">
                        <h6 class="fw-bold mb-3 pb-2 border-bottom">Rekening Tujuan Aktif</h6>
                        @foreach([
                            ['kunci' => 'bank', 'label' => 'Rekening Bank', 'warna' => 'primary', 'kosong' => 'Belum diisi'],
                            ['kunci' => 'ewallet', 'label' => 'E-Wallet', 'warna' => 'info', 'kosong' => 'Tidak dipakai'],
                        ] as $opsi)
                            @php
                                $data = $tujuan[$opsi['kunci']];
                                $logo = $cariLogo($data['nama']);
                            @endphp
                            <div class="tujuan-wrapper rounded-3 p-3 mb-2 {{ $data['tersedia'] ? 'tujuan-terisi' : 'tujuan-kosong' }}">
                                <div class="d-flex align-items-center justify-content-between mb-1">
                                    <small class="fw-semibold text-{{ $data['tersedia'] ? $opsi['warna'] : 'muted' }}">{{ $opsi['label'] }}</small>
                                    @if($logo)
                                        <img src="{{ $logo }}" alt="{{ $data['nama'] }}" class="tujuan-logo">
                                    @elseif($data['tersedia'])
                                        <span class="badge bg-label-{{ $opsi['warna'] }} rounded-pill">{{ $data['nama'] }}</span>
                                    @endif
                                </div>
                                @if($data['tersedia'])
                                    <div class="fw-bold select-all small" style="word-break: break-all;">{{ $data['nomor'] }}</div>
                                    <small class="text-muted">a.n. {{ $data['pemilik'] ?: '—' }}</small>
                                @else
                                    <small class="text-muted">{{ $opsi['kosong'] }}</small>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- Riwayat Arsip Penarikan & Pemasukan Otomatis -->
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-3 p-md-4">
                <ul class="nav nav-pills gap-2 mb-3" role="tablist">
                    <li class="nav-item">
                        <button type="button" class="nav-link active rounded-pill px-3" role="tab" data-bs-toggle="tab" data-bs-target="#tab-penarikan">
                            Riwayat Penarikan
                        </button>
                    </li>
                    <li class="nav-item">
                        <button type="button" class="nav-link rounded-pill px-3" role="tab" data-bs-toggle="tab" data-bs-target="#tab-pemasukan">
                            Arsip Pemasukan Otomatis
                        </button>
                    </li>
                </ul>

                <div class="tab-content p-0 shadow-none bg-transparent">
                    <div class="tab-pane fade show active" id="tab-penarikan" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light">
                                    <tr class="text-muted small">
                                        <th>Tanggal</th>
                                        <th>Jumlah</th>
                                        <th>Tujuan</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($riwayatPenarikan as $p)
                                        <tr>
                                            <td><small>{{ optional($p->diajukan_pada)->format('d M Y H:i') }}</small></td>
                                            <td class="fw-bold text-danger">- Rp {{ number_format($p->jumlah, 0, ',', '.') }}</td>
                                            <td>
                                                <div class="fw-semibold">{{ $p->nama_bank }}</div>
                                                <small class="text-muted">{{ $p->no_rekening }}</small>
                                            </td>
                                            <td>
                                                <span class="badge rounded-pill bg-label-{{ match($p->status) {
                                                    'pending' => 'warning', 'diproses' => 'info',
                                                    'selesai' => 'success', 'ditolak' => 'danger', default => 'secondary',
                                                } }}">{{ $p->labelStatus() }}</span>
                                                @if($p->catatan_admin)
                                                    <div><small class="text-muted">{{ $p->catatan_admin }}</small></div>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="4" class="text-center text-muted py-4">Belum ada riwayat penarikan.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        @if($riwayatPenarikan->hasPages())
                            <div class="mt-3">{{ $riwayatPenarikan->links() }}</div>
                        @endif
                    </div>

                    <div class="tab-pane fade" id="tab-pemasukan" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light">
                                    <tr class="text-muted small">
                                        <th>Tanggal</th>
                                        <th>Jumlah</th>
                                        <th>Sumber</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($riwayatPemasukan as $tx)
                                        <tr>
                                            <td><small>{{ $tx->created_at?->format('d M Y H:i') }}</small></td>
                                            <td class="fw-bold text-success">+ Rp {{ number_format($tx->amount, 0, ',', '.') }}</td>
                                            <td>
                                                <span class="text-capitalize fw-semibold">{{ $tx->reference_type }}</span>
                                                <small class="text-muted">#{{ $tx->reference_id }}</small>
                                            </td>
                                            <td>
                                                <span class="badge rounded-pill bg-label-{{ match($tx->status) {
                                                    'verified' => 'success', 'pending' => 'warning',
                                                    'rejected' => 'danger', default => 'secondary',
                                                } }}">{{ match($tx->status) {
                                                    'verified' => 'Masuk saldo', 'pending' => 'Tertahan', 'rejected' => 'Batal', default => $tx->status,
                                                } }}</span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="4" class="text-center text-muted py-4">Tidak ada arsip pemasukan pembayaran otomatis.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        @if($riwayatPemasukan->hasPages())
                            <div class="mt-3">{{ $riwayatPemasukan->links() }}</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('modals')
<div class="modal fade" id="modalTarikSaldo" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ route('admin.keuangan.tarik') }}" method="POST">
            @csrf
            <div class="modal-content rounded-4 border-0 shadow-lg">
                <div class="modal-header border-bottom">
                    <h6 class="modal-title fw-bold">Tarik Sisa Saldo ke Rekening</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="rounded-4 p-3 mb-4 text-white d-flex align-items-center justify-content-between"
                         style="background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);">
                        <div>
                            <small style="opacity: 0.9;">Saldo Tersedia</small>
                            <h4 class="fw-bold text-white mb-0">Rp {{ number_format($saldo['tersedia'], 0, ',', '.') }}</h4>
                        </div>
                        <span class="ikon-bulat rounded-circle" style="width: 42px; height: 42px; background: rgba(255,255,255,0.18);">
                            <i class="bx bx-wallet fs-4"></i>
                        </span>
                    </div>

                    <label class="form-label fw-semibold mb-2">Pilih Tujuan Penarikan</label>
                    @foreach([
                        ['kunci' => 'bank', 'nilai' => 'bank', 'label' => 'Rekening Bank', 'kosong' => 'Belum diisi di Pengaturan'],
                        ['kunci' => 'ewallet', 'nilai' => 'ewallet', 'label' => 'E-Wallet', 'kosong' => 'Tidak dipakai wilayah ini'],
                    ] as $opsi)
                        @php
                            $data = $tujuan[$opsi['kunci']];
                            $logo = $cariLogo($data['nama']);
                            $terpilih = $opsi['kunci'] === 'bank' ? $data['tersedia'] : ($data['tersedia'] && ! $tujuan['bank']['tersedia']);
                        @endphp
                        <label class="tujuan-card d-block mb-2">
                            <input type="radio" name="metode" value="{{ $opsi['nilai'] }}" class="tujuan-radio"
                                   {{ $data['tersedia'] ? '' : 'disabled' }} {{ $terpilih ? 'checked' : '' }}>
                            <div class="tujuan-wrapper rounded-3 p-3 d-flex align-items-center justify-content-between">
                                <div class="me-2">
                                    <div class="fw-semibold">{{ $opsi['label'] }}</div>
                                    <small class="text-muted">
                                        @if($data['tersedia'])
                                            {{ $data['nomor'] }} (a/n {{ $data['pemilik'] }})
                                        @else
                                            {{ $opsi['kosong'] }}
                                        @endif
                                    </small>
                                </div>
                                @if($logo)
                                    <img src="{{ $logo }}" alt="{{ $data['nama'] }}" class="tujuan-logo flex-shrink-0">
                                @endif
                            </div>
                        </label>
                    @endforeach

                    <div class="mt-3">
                        <label class="form-label fw-semibold mb-1">Nominal Penarikan (Rp)</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white">Rp</span>
                            <input type="number" name="jumlah" class="form-control" required
                                   min="{{ $minimalPenarikan }}" max="{{ (int) $saldo['tersedia'] }}" step="1000"
                                   placeholder="Minimal Rp {{ number_format($minimalPenarikan, 0, ',', '.') }}">
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm">
                        <i class="bx bx-send me-1"></i> Ajukan Penarikan
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endpush

<style>
    .tujuan-card { cursor: pointer; margin-bottom: 0; }
    .tujuan-radio { display: none; }
    .tujuan-wrapper { border: 1.5px solid #e7e7e7; background-color: #fff; transition: all 0.2s ease; }
    .tujuan-radio:checked + .tujuan-wrapper { border-color: #696cff; box-shadow: 0 0 0 1px #696cff; }
    .tujuan-radio:disabled + .tujuan-wrapper { cursor: not-allowed; background-color: #f8f8f8; opacity: 0.65; }
    .tujuan-logo { max-height: 24px; max-width: 78px; object-fit: contain; }
    .tujuan-terisi { border-color: #d9dbff; }
    .tujuan-kosong { border-style: dashed; background-color: #fafafa; }
    .ikon-bulat { display: inline-flex !important; align-items: center; justify-content: center; line-height: 1; flex-shrink: 0; }
</style>
@endsection
