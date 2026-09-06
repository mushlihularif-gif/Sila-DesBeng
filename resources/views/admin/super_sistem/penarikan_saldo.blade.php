@extends('admin.layouts.admin')

@section('title', 'Penarikan Saldo Wilayah')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Sistem Platform /</span> Penarikan Saldo Wilayah</h4>

    <div class="alert alert-info d-flex align-items-start mb-4">
        <i class="bx bx-info-circle me-2 fs-5 mt-1"></i>
        <div>
            Midtrans sekarang satu akun milik Diskominfotik: uang gateway dari <strong>semua</strong> wilayah
            mendarat di rekening ini lebih dulu, dibukukan sebagai saldo tiap wilayah. Daftar di bawah adalah
            pengajuan admin daerah untuk mencairkan saldo itu ke rekening wilayahnya sendiri.
            Menyetujui di sini <strong>tidak</strong> mentransfer uang secara otomatis — transfernya lewat
            m-banking seperti biasa, tombol Setujui hanya menandai sudah dikirim.
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Posisi dana kelolaan. Angka utamanya sengaja "dana milik wilayah yang
         kami pegang", bukan "pendapatan": Diskominfotik penampung, bukan pemilik.
         Membingkainya sebagai pendapatan akan salah secara akuntansi sekaligus
         salah secara politik. --}}
    <div class="row g-4 mb-4">
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm rounded-4 h-100 text-white position-relative overflow-hidden"
                 style="background: linear-gradient(135deg, #4c1d95 0%, #7c3aed 55%, #a78bfa 100%);">
                <div class="position-absolute rounded-circle"
                     style="width: 280px; height: 280px; right: -100px; top: -120px; background: rgba(255,255,255,0.10);"></div>
                <div class="position-absolute rounded-circle"
                     style="width: 180px; height: 180px; right: 40px; bottom: -100px; background: rgba(255,255,255,0.07);"></div>

                <div class="card-body p-4 position-relative">
                    <div class="d-flex align-items-start justify-content-between mb-1">
                        <small class="text-uppercase fw-semibold" style="letter-spacing: 1px; opacity: 0.85;">
                            Dana Dikelola Diskominfotik
                        </small>
                        <span class="ikon-bulat rounded-circle" style="width: 42px; height: 42px; background: rgba(255,255,255,0.18);">
                            <i class="bx bx-buildings fs-4"></i>
                        </span>
                    </div>

                    <h1 class="fw-bold text-white mb-1" style="letter-spacing: -1px;">
                        Rp {{ number_format($dana['dikelola'], 0, ',', '.') }}
                    </h1>
                    <small style="opacity: 0.85;">
                        Ada di rekening Diskominfotik, tetapi <strong>milik wilayah</strong> — belum dicairkan.
                    </small>

                    <div class="d-flex flex-wrap gap-2 mt-3">
                        <span class="d-inline-flex align-items-center rounded-pill px-3 py-1"
                              style="background: rgba(255,255,255,0.18); font-size: 0.78rem;">
                            <i class="bx bx-loader-circle me-1"></i>
                            Diajukan Rp {{ number_format($dana['sedang_diajukan'], 0, ',', '.') }}
                        </span>
                        <span class="d-inline-flex align-items-center rounded-pill px-3 py-1"
                              style="background: rgba(255,255,255,0.18); font-size: 0.78rem;">
                            <i class="bx bx-time-five me-1"></i>
                            Escrow Rp {{ number_format($dana['tertahan'], 0, ',', '.') }}
                        </span>
                    </div>

                    <hr style="border-color: rgba(255,255,255,0.25); opacity: 1;" class="my-3">

                    <div class="row g-3">
                        <div class="col-6">
                            <small class="d-block" style="opacity: 0.8;">Masuk sepanjang masa</small>
                            <div class="fw-bold fs-5">Rp {{ number_format($dana['total_masuk'], 0, ',', '.') }}</div>
                        </div>
                        <div class="col-6">
                            <small class="d-block" style="opacity: 0.8;">Sudah dicairkan ke wilayah</small>
                            <div class="fw-bold fs-5">Rp {{ number_format($dana['sudah_dicairkan'], 0, ',', '.') }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-4 d-flex flex-column">
                    <div class="d-flex align-items-center mb-3 border-bottom pb-3">
                        <div class="avatar avatar-sm bg-primary-subtle text-primary rounded-circle me-3 d-flex justify-content-center align-items-center">
                            <i class="bx bx-list-ul fs-5"></i>
                        </div>
                        <h6 class="fw-bold mb-0">Milik Siapa Saja</h6>
                    </div>

                    @if($rincianWilayah->isEmpty())
                        <div class="text-center text-muted my-auto py-3">
                            <i class="bx bx-wallet-alt fs-1 d-block mb-2 opacity-50"></i>
                            <small>Belum ada pemasukan gateway dari wilayah mana pun.</small>
                        </div>
                    @else
                        <div class="table-responsive" style="max-height: 260px; overflow-y: auto;">
                            <table class="table table-sm mb-0">
                                <thead>
                                    <tr class="text-muted small">
                                        <th class="fw-normal">Wilayah</th>
                                        <th class="fw-normal text-end">Dipegang</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($rincianWilayah as $w)
                                        <tr>
                                            <td>
                                                <div class="fw-semibold">{{ $w['nama'] }}</div>
                                                <small class="text-muted text-capitalize">{{ $w['tipe'] }}</small>
                                                @if($w['diajukan'] > 0)
                                                    <span class="badge bg-label-warning rounded-pill ms-1">
                                                        mengajukan Rp {{ number_format($w['diajukan'], 0, ',', '.') }}
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="text-end">
                                                <div class="fw-bold">Rp {{ number_format($w['dipegang'], 0, ',', '.') }}</div>
                                                <small class="text-muted">dari Rp {{ number_format($w['masuk'], 0, ',', '.') }}</small>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @if($ringkasan['terlambat'] > 0)
        {{-- Uang ini milik wilayah dan sedang tertahan di rekening Diskominfotik.
             Keterlambatan memprosesnya ditampilkan mencolok, bukan diselipkan
             sebagai baris biasa di tabel. --}}
        <div class="alert alert-danger d-flex align-items-center shadow-sm" role="alert">
            <i class="bx bx-error fs-4 me-2"></i>
            <div>
                <strong>{{ $ringkasan['terlambat'] }} pengajuan lewat batas {{ $ringkasan['batas_hari'] }} hari.</strong>
                Selama belum diproses, wilayah tidak bisa mencairkan apa pun karena hanya satu pengajuan
                yang boleh berjalan.
            </div>
        </div>
    @endif

    <div class="row g-4 mb-2">
        <div class="col-md-4">
            <div class="d-flex align-items-center bg-light rounded-3 p-3 shadow-sm h-100">
                <div class="avatar flex-shrink-0 me-3">
                    <span class="avatar-initial rounded bg-label-warning">
                        <i class="bx bx-time-five fs-4"></i>
                    </span>
                </div>
                <div>
                    <h6 class="mb-0 fw-bold text-dark">{{ $ringkasan['menunggu'] }}</h6>
                    <small class="text-muted">Menunggu diproses</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="d-flex align-items-center bg-light rounded-3 p-3 shadow-sm h-100">
                <div class="avatar flex-shrink-0 me-3">
                    <span class="avatar-initial rounded bg-label-{{ $ringkasan['terlambat'] > 0 ? 'danger' : 'secondary' }}">
                        <i class="bx bx-alarm-exclamation fs-4"></i>
                    </span>
                </div>
                <div>
                    <h6 class="mb-0 fw-bold {{ $ringkasan['terlambat'] > 0 ? 'text-danger' : 'text-dark' }}">
                        {{ $ringkasan['terlambat'] }}
                    </h6>
                    <small class="text-muted">Lewat {{ $ringkasan['batas_hari'] }} hari</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="d-flex align-items-center bg-light rounded-3 p-3 shadow-sm h-100">
                <div class="avatar flex-shrink-0 me-3">
                    <span class="avatar-initial rounded bg-label-primary">
                        <i class="bx bx-wallet fs-4"></i>
                    </span>
                </div>
                <div>
                    <h6 class="mb-0 fw-bold text-dark">Rp {{ number_format($ringkasan['total_menunggu'], 0, ',', '.') }}</h6>
                    <small class="text-muted">Total yang perlu dicairkan</small>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h6 class="mb-0">Daftar Pengajuan</h6>
            <div class="btn-group btn-group-sm">
                <a href="{{ route('admin.sistem-platform.penarikan.index', ['status' => 'berjalan']) }}"
                   class="btn btn-{{ $status === 'berjalan' ? 'primary' : 'outline-primary' }}">Berjalan</a>
                <a href="{{ route('admin.sistem-platform.penarikan.index', ['status' => 'selesai']) }}"
                   class="btn btn-{{ $status === 'selesai' ? 'primary' : 'outline-primary' }}">Selesai</a>
                <a href="{{ route('admin.sistem-platform.penarikan.index', ['status' => 'ditolak']) }}"
                   class="btn btn-{{ $status === 'ditolak' ? 'primary' : 'outline-primary' }}">Terkendala</a>
                <a href="{{ route('admin.sistem-platform.penarikan.index', ['status' => 'dibatalkan']) }}"
                   class="btn btn-{{ $status === 'dibatalkan' ? 'primary' : 'outline-primary' }}">Dibatalkan</a>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Wilayah</th>
                        <th>Jumlah</th>
                        <th>Rekening Tujuan</th>
                        <th>Menunggu</th>
                        <th>Status</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($penarikan as $p)
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $p->region->name ?? '—' }}</div>
                                <small class="text-muted">oleh {{ $p->pengaju->name ?? '—' }}</small>
                            </td>
                            <td class="fw-bold">Rp {{ number_format($p->jumlah, 0, ',', '.') }}</td>
                            <td>
                                {{-- Petugas perlu tahu ini transfer m-banking atau kirim ke
                                     dompet digital: dua alur transfer yang berbeda. --}}
                                <span class="badge bg-label-{{ $p->metode === 'ewallet' ? 'info' : 'primary' }} rounded-pill mb-1">
                                    <i class="bx {{ $p->metode === 'ewallet' ? 'bx-mobile-alt' : 'bx-building-house' }} me-1"></i>
                                    {{ $p->metode === 'ewallet' ? 'E-Wallet' : 'Bank' }}
                                </span>
                                <div class="fw-semibold">{{ $p->nama_bank }}</div>
                                <small class="text-muted select-all">{{ $p->no_rekening }}</small><br>
                                <small class="text-muted">a.n. {{ $p->nama_pemilik }}</small>
                            </td>
                            <td>
                                {{-- Umur pengajuan dibuat kelihatan supaya kelalaian memproses
                                     tidak tenggelam di antrean. --}}
                                @if($p->status === \App\Models\PenarikanSaldo::MENUNGGU)
                                    <span class="badge rounded-pill bg-{{ $p->terlambat() ? 'danger' : 'label-secondary' }}">
                                        {{ $p->umurHari() }} hari
                                    </span>
                                    @if($p->terlambat())
                                        <div><small class="text-danger fw-semibold">Lewat batas {{ $ringkasan['batas_hari'] }} hari</small></div>
                                    @endif
                                @endif
                                <small class="text-muted d-block">{{ optional($p->diajukan_pada)->format('d M Y H:i') }}</small>
                            </td>
                            <td>
                                <span class="badge bg-label-{{ match($p->status) {
                                    'pending' => 'warning',
                                    'diproses' => 'info',
                                    'selesai' => 'success',
                                    'ditolak' => 'danger',
                                    'dibatalkan' => 'secondary',
                                    default => 'secondary',
                                } }}">{{ $p->labelStatus() }}</span>
                                @if($p->sudahSelesai() && $p->catatan_admin)
                                    <div><small class="text-muted">{{ $p->catatan_admin }}</small></div>
                                @endif
                            </td>
                            <td class="text-end">
                                @if(! $p->sudahSelesai())
                                    <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#acc-{{ $p->id }}">
                                        <i class="bx bx-check"></i> Setujui
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#tolak-{{ $p->id }}">
                                        <i class="bx bx-error-circle"></i> Tidak Bisa Diproses
                                    </button>

                                    <div class="modal fade" id="acc-{{ $p->id }}" tabindex="-1">
                                        <div class="modal-dialog">
                                            <form method="POST" action="{{ route('admin.sistem-platform.penarikan.approve', $p) }}">
                                                @csrf
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h6 class="modal-title">Setujui Penarikan</h6>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <p>Pastikan transfer <strong>Rp {{ number_format($p->jumlah, 0, ',', '.') }}</strong>
                                                           ke <strong>{{ $p->nama_bank }} {{ $p->no_rekening }}</strong> (a.n. {{ $p->nama_pemilik }})
                                                           sudah benar-benar dikirim sebelum menekan tombol ini.</p>
                                                        <label class="form-label small">Catatan (opsional)</label>
                                                        <input type="text" name="catatan_admin" class="form-control form-control-sm" placeholder="Misal: no. referensi transfer">
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                                                        <button type="submit" class="btn btn-success btn-sm">Ya, Sudah Ditransfer</button>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    </div>

                                    <div class="modal fade" id="tolak-{{ $p->id }}" tabindex="-1">
                                        <div class="modal-dialog">
                                            <form method="POST" action="{{ route('admin.sistem-platform.penarikan.reject', $p) }}">
                                                @csrf
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h6 class="modal-title">Tandai Tidak Bisa Diproses</h6>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="alert alert-warning py-2 px-3 small mb-3">
                                                            <i class="bx bx-info-circle me-1"></i>
                                                            Ini bukan menolak hak wilayah atas uangnya — saldo mereka kembali utuh
                                                            dan bisa diajukan lagi. Pakai ini untuk kendala teknis: rekening salah,
                                                            nama tidak cocok dengan data bank, atau dana Midtrans belum settle.
                                                        </div>
                                                        <label class="form-label small">Kendalanya apa? <span class="text-danger">*</span></label>
                                                        <textarea name="catatan_admin" class="form-control form-control-sm" rows="3" required
                                                                  placeholder="Cth: nomor rekening tidak ditemukan di BRI"></textarea>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                                                        <button type="submit" class="btn btn-danger btn-sm">Simpan Kendala</button>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                @else
                                    <small class="text-muted">
                                        {{ optional($p->diselesaikan_pada)->format('d M Y H:i') }}
                                        @if($p->petugas) oleh {{ $p->petugas->name }} @endif
                                    </small>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-4">Tidak ada pengajuan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($penarikan->hasPages())
            <div class="card-footer">{{ $penarikan->links() }}</div>
        @endif
    </div>
</div>

<style>
    /* .avatar-initial milik tema hanya memusatkan isinya kalau berada di dalam
       .avatar — dipakai sendirian, ikonnya melenceng ke kiri-bawah. */
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
