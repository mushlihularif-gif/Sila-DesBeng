@extends('admin.layouts.admin')

@section('title', 'Keuangan Wilayah')
@section('page-title', 'Keuangan Wilayah')

@section('content')
@php
    // Peta nama -> berkas logo, memakai aset yang sama dengan form Pengaturan
    // Pembayaran supaya bank yang dipilih di sana tampil konsisten di sini.
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
@endphp
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Manajemen /</span> Keuangan Wilayah</h4>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible shadow-sm rounded-4 border-0 d-flex align-items-center" role="alert">
            <i class="bx bx-check-circle fs-4 me-2"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible shadow-sm rounded-4 border-0 d-flex align-items-center" role="alert">
            <i class="bx bx-error-circle fs-4 me-2"></i>
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible shadow-sm rounded-4 border-0" role="alert">
            <ul class="mb-0 ps-3">
                @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row g-4 mb-4">
        {{-- Kartu saldo utama. Sengaja paling menonjol: inilah alasan halaman ini ada.
             Warna putih transparan ditulis rgba() langsung, bukan .bg-opacity-* —
             utilitas itu tidak berpengaruh di atas .bg-white tema ini, hasilnya
             kotak putih pekat dengan teks putih yang lenyap. --}}
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm rounded-4 h-100 text-white position-relative overflow-hidden"
                 style="background: linear-gradient(135deg, #0f9d58 0%, #34d399 100%);">

                {{-- Ornamen lingkaran samar, memberi kedalaman supaya kartu tidak
                     terasa seperti blok warna kosong. --}}
                <div class="position-absolute rounded-circle"
                     style="width: 260px; height: 260px; right: -90px; top: -110px; background: rgba(255,255,255,0.10);"></div>
                <div class="position-absolute rounded-circle"
                     style="width: 170px; height: 170px; right: 30px; bottom: -90px; background: rgba(255,255,255,0.08);"></div>

                <div class="card-body p-4 position-relative">
                    <div class="d-flex align-items-start justify-content-between mb-1">
                        <small class="text-uppercase fw-semibold" style="letter-spacing: 1px; opacity: 0.85;">
                            Saldo Aktif Bisa Ditarik
                        </small>
                        <span class="ikon-bulat rounded-circle"
                              style="width: 42px; height: 42px; background: rgba(255,255,255,0.18);">
                            <i class="bx bx-wallet fs-4"></i>
                        </span>
                    </div>

                    <h1 class="fw-bold text-white mb-1" style="letter-spacing: -1px;">
                        Rp {{ number_format($saldo['tersedia'], 0, ',', '.') }}
                    </h1>
                    <small style="opacity: 0.8;">Wilayah {{ $region->name }}</small>

                    <div class="d-flex flex-wrap gap-2 mt-3">
                        <span class="d-inline-flex align-items-center rounded-pill px-3 py-1"
                              style="background: rgba(255,255,255,0.18); font-size: 0.78rem;">
                            <i class="bx bx-time-five me-1"></i>
                            Tertahan Rp {{ number_format($saldo['tertahan'], 0, ',', '.') }}
                        </span>
                        <span class="d-inline-flex align-items-center rounded-pill px-3 py-1"
                              style="background: rgba(255,255,255,0.18); font-size: 0.78rem;">
                            <i class="bx bx-loader-circle me-1"></i>
                            Diajukan Rp {{ number_format($saldo['sedang_ditarik'], 0, ',', '.') }}
                        </span>
                    </div>

                    <hr style="border-color: rgba(255,255,255,0.25); opacity: 1;" class="my-3">

                    @if($penarikanBerjalan)
                        <div class="rounded-3 p-3" style="background: rgba(255,255,255,0.18);">
                            <div class="d-flex align-items-start mb-2">
                                <i class="bx bx-time-five fs-4 me-2"></i>
                                <div>
                                    <div class="fw-semibold">
                                        Pengajuan Rp {{ number_format($penarikanBerjalan->jumlah, 0, ',', '.') }} — {{ $penarikanBerjalan->labelStatus() }}
                                    </div>
                                    <small style="opacity: 0.85;">
                                        Diajukan {{ optional($penarikanBerjalan->diajukan_pada)->format('d M Y, H:i') }} WIB
                                        @if($penarikanBerjalan->umurHari() > 0)
                                            — sudah {{ $penarikanBerjalan->umurHari() }} hari menunggu
                                        @endif
                                    </small>
                                </div>
                            </div>

                            {{-- Jalan keluar kalau Diskominfotik lama tak memproses. Tanpa ini,
                                 satu pengajuan yang didiamkan mengunci wilayah dari pencairan
                                 apa pun, termasuk memperbaiki rekening yang salah. --}}
                            @if($penarikanBerjalan->bisaDibatalkanWilayah())
                                <form action="{{ route('admin.keuangan.batal', $penarikanBerjalan) }}" method="POST"
                                      data-konfirmasi="Batalkan pengajuan ini? Saldo akan kembali tersedia dan Anda bisa mengajukan ulang.">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-light rounded-pill px-3 fw-semibold text-success">
                                        <i class="bx bx-x-circle me-1"></i> Batalkan Pengajuan
                                    </button>
                                </form>
                            @else
                                <small style="opacity: 0.85;">
                                    <i class="bx bx-lock-alt me-1"></i>
                                    Sudah mulai ditransfer — tidak bisa dibatalkan sendiri.
                                </small>
                            @endif
                        </div>
                    @elseif($saldo['tersedia'] < $minimalPenarikan)
                        <div class="d-flex align-items-center rounded-3 p-3" style="background: rgba(255,255,255,0.18);">
                            <i class="bx bx-info-circle fs-4 me-2"></i>
                            <small>
                                Saldo belum mencapai minimal <strong>Rp {{ number_format($minimalPenarikan, 0, ',', '.') }}</strong>
                                untuk bisa diajukan pencairannya.
                            </small>
                        </div>
                    @else
                        <div class="d-flex flex-wrap align-items-center gap-3">
                            <button type="button" class="btn btn-light rounded-pill shadow-sm px-4 fw-bold text-success"
                                    data-bs-toggle="modal" data-bs-target="#modalTarikSaldo">
                                <i class="bx bx-money-withdraw me-1"></i> Tarik Saldo
                            </button>
                            <small style="opacity: 0.85;">
                                Cair ke {{ $tujuan['bank']['tersedia'] ? $tujuan['bank']['nama'] : 'rekening wilayah' }},
                                diproses manual oleh Diskominfotik.
                            </small>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="row g-4 h-100">
                <div class="col-sm-6 col-lg-12">
                    <div class="card border-0 shadow-sm rounded-4 h-100">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <small class="text-muted text-uppercase fw-semibold" style="letter-spacing: 0.5px; font-size: 0.7rem;">
                                    Total Pemasukan
                                </small>
                                <span class="ikon-bulat rounded-circle bg-label-primary" style="width: 36px; height: 36px;">
                                    <i class="bx bx-trending-up"></i>
                                </span>
                            </div>
                            <h4 class="fw-bold mb-1">Rp {{ number_format($totalPemasukan, 0, ',', '.') }}</h4>
                            <small class="text-muted">Sepanjang masa, sebelum dikurangi pencairan</small>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-12">
                    <div class="card border-0 shadow-sm rounded-4 h-100">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <small class="text-muted text-uppercase fw-semibold" style="letter-spacing: 0.5px; font-size: 0.7rem;">
                                    Pesanan Lunas
                                </small>
                                <span class="ikon-bulat rounded-circle bg-label-info" style="width: 36px; height: 36px;">
                                    <i class="bx bx-package"></i>
                                </span>
                            </div>
                            <h4 class="fw-bold mb-1">{{ $pesananSelesai }} Pesanan</h4>
                            <small class="text-muted">Dibayar warga lewat pembayaran otomatis</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        {{-- Tujuan pencairan. Read-only di sini: mengubahnya urusan Pengaturan,
             halaman ini cuma perlu menunjukkan ke mana uangnya akan mendarat. --}}
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-3 border-bottom pb-3">
                        <div class="avatar avatar-sm bg-primary-subtle text-primary rounded-circle me-3 d-flex justify-content-center align-items-center">
                            <i class="bx bx-credit-card fs-5"></i>
                        </div>
                        <h6 class="fw-bold mb-0">Tujuan Pencairan</h6>
                    </div>

                    {{-- Bentuk kartu bertepi dengan logo asli di kanan — sama seperti
                         pilihan tujuan di modal, supaya yang dilihat di sini dan yang
                         dipilih saat mencairkan terasa satu benda yang sama. --}}
                    @foreach([
                        ['kunci' => 'bank', 'label' => 'Rekening Bank', 'warna' => 'primary', 'kosong' => 'Belum diisi'],
                        ['kunci' => 'ewallet', 'label' => 'E-Wallet', 'warna' => 'info', 'kosong' => 'Tidak dipakai'],
                    ] as $opsi)
                        @php
                            $data = $tujuan[$opsi['kunci']];
                            $logo = $cariLogo($data['nama']);
                        @endphp
                        <div class="tujuan-wrapper rounded-3 p-3 mb-3 {{ $data['tersedia'] ? 'tujuan-terisi' : 'tujuan-kosong' }}">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <small class="fw-semibold text-{{ $data['tersedia'] ? $opsi['warna'] : 'muted' }}">{{ $opsi['label'] }}</small>
                                @if($logo)
                                    <img src="{{ $logo }}" alt="{{ $data['nama'] }}" class="tujuan-logo">
                                @elseif($data['tersedia'])
                                    <span class="badge bg-label-{{ $opsi['warna'] }} rounded-pill">{{ $data['nama'] }}</span>
                                @endif
                            </div>

                            @if($data['tersedia'])
                                <div class="fw-bold select-all" style="letter-spacing: 0.5px; word-break: break-all;">{{ $data['nomor'] }}</div>
                                <small class="text-muted">a.n. {{ $data['pemilik'] ?: '—' }}</small>
                            @else
                                <small class="text-muted">
                                    <i class="bx bx-minus-circle me-1"></i>{{ $opsi['kosong'] }}
                                </small>
                            @endif
                        </div>
                    @endforeach

                    <a href="{{ route('admin.region-settings.payment') }}" class="btn btn-sm btn-outline-primary rounded-pill w-100">
                        <i class="bx bx-edit me-1"></i> Ubah di Pengaturan
                    </a>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-4">
                    <ul class="nav nav-pills gap-2 mb-4" role="tablist">
                        <li class="nav-item">
                            <button type="button" class="nav-link active rounded-pill shadow-sm px-4" role="tab"
                                    data-bs-toggle="tab" data-bs-target="#tab-penarikan">
                                <i class="bx bx-money-withdraw me-1"></i> Riwayat Penarikan
                            </button>
                        </li>
                        <li class="nav-item">
                            <button type="button" class="nav-link rounded-pill shadow-sm px-4" role="tab"
                                    data-bs-toggle="tab" data-bs-target="#tab-pemasukan">
                                <i class="bx bx-download me-1"></i> Riwayat Pemasukan
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content p-0 shadow-none bg-transparent">
                        <div class="tab-pane fade show active" id="tab-penarikan" role="tabpanel">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead>
                                        <tr class="text-muted small">
                                            <th class="fw-normal">Tanggal</th>
                                            <th class="fw-normal">Jumlah</th>
                                            <th class="fw-normal">Tujuan</th>
                                            <th class="fw-normal">Status</th>
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
                                            <tr><td colspan="4" class="text-center text-muted py-4">Belum pernah mengajukan penarikan.</td></tr>
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
                                <table class="table table-hover mb-0">
                                    <thead>
                                        <tr class="text-muted small">
                                            <th class="fw-normal">Tanggal</th>
                                            <th class="fw-normal">Jumlah</th>
                                            <th class="fw-normal">Sumber</th>
                                            <th class="fw-normal">Status</th>
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
                                                        'verified' => 'Masuk saldo',
                                                        'pending' => 'Tertahan',
                                                        'rejected' => 'Batal',
                                                        default => $tx->status,
                                                    } }}</span>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="4" class="text-center text-muted py-4">Belum ada pemasukan lewat pembayaran otomatis.</td></tr>
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
</div>

{{-- Modal pengajuan. Tujuan dipilih di sini (bukan diam-diam selalu ke bank),
     karena sebagian wilayah memakai e-wallet sebagai kas hariannya. --}}
<div class="modal fade" id="modalTarikSaldo" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ route('admin.keuangan.tarik') }}" method="POST">
            @csrf
            <div class="modal-content rounded-4 border-0 shadow">
                <div class="modal-header border-bottom">
                    <h6 class="modal-title fw-bold">Tarik Saldo Ke Rekening</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="rounded-4 p-3 mb-4 text-white d-flex align-items-center justify-content-between position-relative overflow-hidden"
                         style="background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);">
                        <div class="position-absolute rounded-circle"
                             style="width: 150px; height: 150px; right: -50px; top: -60px; background: rgba(255,255,255,0.10);"></div>
                        <div class="position-relative">
                            <small style="opacity: 0.9;">Saldo Aktif Anda</small>
                            <h4 class="fw-bold text-white mb-0">Rp {{ number_format($saldo['tersedia'], 0, ',', '.') }}</h4>
                        </div>
                        <span class="ikon-bulat rounded-circle position-relative"
                              style="width: 44px; height: 44px; background: rgba(255,255,255,0.18);">
                            <i class="bx bx-wallet fs-4"></i>
                        </span>
                    </div>

                    <label class="form-label fw-semibold mb-2">Pilih Tujuan Penarikan</label>

                    @foreach([
                        ['kunci' => 'bank', 'nilai' => 'bank', 'label' => 'Rekening Bank', 'warna' => 'primary', 'kosong' => 'Belum diisi di Pengaturan'],
                        ['kunci' => 'ewallet', 'nilai' => 'ewallet', 'label' => 'E-Wallet', 'warna' => 'info', 'kosong' => 'Tidak dipakai wilayah ini'],
                    ] as $opsi)
                        @php
                            $data = $tujuan[$opsi['kunci']];
                            $logo = $cariLogo($data['nama']);
                            // Terpilih otomatis: bank kalau ada, kalau tidak e-wallet.
                            $terpilih = $opsi['kunci'] === 'bank'
                                ? $data['tersedia']
                                : ($data['tersedia'] && ! $tujuan['bank']['tersedia']);
                        @endphp
                        <label class="tujuan-card d-block mb-2">
                            <input type="radio" name="metode" value="{{ $opsi['nilai'] }}" class="tujuan-radio"
                                   {{ $data['tersedia'] ? '' : 'disabled' }} {{ $terpilih ? 'checked' : '' }}>
                            <div class="tujuan-wrapper rounded-3 p-3 d-flex align-items-center justify-content-between">
                                <div class="me-2">
                                    <div class="fw-semibold">{{ $opsi['label'] }}</div>
                                    <small class="text-muted">
                                        @if($data['tersedia'])
                                            {{ $data['nomor'] }} <span class="text-nowrap">(a/n {{ $data['pemilik'] }})</span>
                                        @else
                                            {{ $opsi['kosong'] }}
                                        @endif
                                    </small>
                                </div>
                                @if($logo)
                                    <img src="{{ $logo }}" alt="{{ $data['nama'] }}" class="tujuan-logo flex-shrink-0">
                                @else
                                    <span class="badge bg-label-secondary rounded-pill flex-shrink-0">—</span>
                                @endif
                            </div>
                        </label>
                    @endforeach

                    <small class="text-muted d-block mb-4">
                        <i class="bx bx-info-circle me-1"></i>
                        Untuk mengubah rekening, edit lewat Pengaturan → Pembayaran Wilayah.
                    </small>

                    <label class="form-label fw-semibold mb-1">Nominal Penarikan (Rp)</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white">Rp</span>
                        <input type="number" name="jumlah" class="form-control" required
                               min="{{ $minimalPenarikan }}" max="{{ (int) $saldo['tersedia'] }}" step="1000"
                               placeholder="Minimal Rp {{ number_format($minimalPenarikan, 0, ',', '.') }}">
                    </div>
                    <small class="text-muted">
                        Maksimal Rp {{ number_format($saldo['tersedia'], 0, ',', '.') }}. Diproses manual oleh Diskominfotik.
                    </small>
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

<style>
    .tujuan-card { cursor: pointer; margin-bottom: 0; }
    .tujuan-radio { display: none; }
    .tujuan-wrapper {
        border: 2px solid #e7e7e7;
        background-color: #fff;
        transition: all 0.2s ease-in-out;
    }
    .tujuan-card:hover .tujuan-wrapper { border-color: #b1b1b1; }
    .tujuan-radio:checked + .tujuan-wrapper {
        border-color: #696cff;
        box-shadow: 0 0 0 1px #696cff;
    }
    .tujuan-radio:disabled + .tujuan-wrapper {
        cursor: not-allowed;
        background-color: #f8f8f8;
        opacity: 0.65;
    }

    /* Logo bank/e-wallet: abu-abu saat tidak terpilih, berwarna saat terpilih —
       pola yang sama dengan pemilih bank di halaman Pengaturan Pembayaran. */
    .tujuan-logo {
        max-height: 26px;
        max-width: 82px;
        object-fit: contain;
        filter: grayscale(100%);
        opacity: 0.6;
        transition: all 0.2s ease-in-out;
    }
    .tujuan-radio:checked + .tujuan-wrapper .tujuan-logo,
    .tujuan-terisi .tujuan-logo {
        filter: grayscale(0%);
        opacity: 1;
    }

    /* Kartu ringkas "Tujuan Pencairan" memakai pembungkus yang sama tapi tanpa
       radio, jadi tepinya dibuat lebih tenang. */
    .tujuan-terisi { border-color: #d9dbff; }
    .tujuan-kosong { border-style: dashed; background-color: #fafafa; }

    /* Ikon bulat di pojok kartu. .avatar-initial milik tema hanya memusatkan
       isinya kalau berada di dalam .avatar — dipakai sendirian, ikonnya melenceng
       ke kiri-bawah. Pemusatan ditulis eksplisit di sini. */
    .ikon-bulat {
        display: inline-flex !important;
        align-items: center;
        justify-content: center;
        line-height: 1;
        flex-shrink: 0;
    }
    .ikon-bulat i { line-height: 1; }
</style>
@endsection
