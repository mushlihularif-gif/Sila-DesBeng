@extends('admin.layouts.admin')

@section('title', 'Dashboard Sistem Platform')

@section('content')
<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">

        <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold mb-1">
                    <span class="text-muted fw-light">Sistem Platform /</span> Dashboard
                </h4>
                <p class="text-muted mb-0">
                    Rangkuman seluruh modul SiLaDesBeng. Halaman ini melihat platform secara keseluruhan,
                    bukan operasional per desa.
                </p>
            </div>
            {{-- Aksi utama ditaruh di header, bukan di kartu kolom kanan:
                 kolom kanan tertutup panel kotak masuk selebar 320px dan letaknya
                 jauh di bawah lipatan, sehingga tombolnya praktis tidak ketemu. --}}
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-label-primary rounded-pill">
                    <i class="bx bx-shield-quarter me-1"></i>{{ auth()->user()->name }}
                </span>
                @if(Route::has('admin.staff.create'))
                    <a href="{{ route('admin.staff.create') }}" class="btn btn-primary">
                        <i class="bx bx-user-plus me-1"></i>Tambah Akun Staf
                    </a>
                @endif
            </div>
        </div>

        {{-- ============ Peringatan tagihan mendekati / lewat jatuh tempo ============ --}}
        @if(auth()->user()->hasPlatformPermission('platform_biaya') && count($biaya['peringatan']))
            @php $adaTerlambat = $biaya['jumlah_terlambat'] > 0; @endphp
            <div class="alert {{ $adaTerlambat ? 'alert-danger' : 'alert-warning' }} d-flex align-items-start mb-4" role="alert">
                <i class="bx {{ $adaTerlambat ? 'bx-error' : 'bx-time-five' }} fs-4 me-3 mt-1"></i>
                <div class="flex-grow-1">
                    <h6 class="alert-heading fw-bold mb-1">
                        @if($adaTerlambat)
                            {{ $biaya['jumlah_terlambat'] }} tagihan sudah LEWAT jatuh tempo
                        @else
                            Tagihan mendekati jatuh tempo
                        @endif
                    </h6>
                    <ul class="mb-2 ps-3 small">
                        @foreach($biaya['peringatan'] as $p)
                            <li>
                                <strong>{{ $p['nama'] }}</strong>
                                &mdash; Rp {{ number_format($p['nominal'], 0, ',', '.') }},
                                jatuh tempo {{ \Carbon\Carbon::parse($p['jatuh_tempo'])->translatedFormat('d M Y') }}
                                @if($p['sisa_hari'] < 0)
                                    <span class="fw-bold text-danger">(terlambat {{ abs($p['sisa_hari']) }} hari)</span>
                                @elseif($p['sisa_hari'] === 0)
                                    <span class="fw-bold">(jatuh tempo hari ini)</span>
                                @else
                                    <span class="fw-bold">({{ $p['sisa_hari'] }} hari lagi)</span>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                    <a href="{{ route('admin.sistem-platform.expenses') }}" class="btn btn-sm {{ $adaTerlambat ? 'btn-danger' : 'btn-warning' }}">
                        <i class="bx bx-wallet me-1"></i>Kelola Tagihan
                    </a>
                </div>
            </div>
        @endif
        {{-- ============ Kartu ringkas ============ --}}
        <div class="row g-4 mb-4">
            @php
                $kpi = [
                    ['Wilayah Terdaftar', $wilayah['total'], $wilayah['desa'] . ' desa · ' . $wilayah['kecamatan'] . ' kecamatan', 'bx-map', 'primary'],
                    ['Pengguna', $pengguna['total'], $pengguna['baru_7hari'] . ' baru 7 hari terakhir', 'bx-group', 'success'],
                    ['Dana Tertahan', $dompet['tertahan'], $dompet['gagal_verifikasi'] . ' gagal verifikasi', 'bx-wallet', 'warning'],
                    $biaya['terdekat']
                        ? ['Tagihan Terdekat', $biaya['terdekat']['sisa_hari'] . ' hari',
                           $biaya['terdekat']['nama'], 'bx-time-five',
                           $biaya['terdekat']['sisa_hari'] < 0 ? 'danger' : ($biaya['terdekat']['sisa_hari'] <= 14 ? 'warning' : 'info')]
                        : ['Tagihan Terdekat', '-', 'tidak ada tagihan aktif', 'bx-time-five', 'secondary'],
                ];
            @endphp

            @foreach($kpi as [$judul, $nilai, $sub, $ikon, $warna])
                <div class="col-lg-3 col-sm-6">
                    <div class="card h-100">
                        <div class="card-body d-flex align-items-center">
                            <div class="avatar flex-shrink-0 me-3">
                                <span class="avatar-initial rounded bg-label-{{ $warna }}"><i class="bx {{ $ikon }} fs-4"></i></span>
                            </div>
                            <div class="min-w-0">
                                <small class="text-muted d-block">{{ $judul }}</small>
                                <h5 class="mb-0 fw-bold">{{ is_numeric($nilai) ? number_format($nilai, 0, ',', '.') : $nilai }}</h5>
                                <small class="text-muted" style="font-size:.72rem">{{ $sub }}</small>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="row g-4">

            {{-- ============ Tagihan server & domain, dengan hitung mundur ============ --}}
            @if(auth()->user()->hasPlatformPermission('platform_biaya'))
            <div class="col-12">
                <div class="card">
                    <div class="card-header pb-2 d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="mb-0"><i class="bx bx-server me-2 text-danger"></i>Biaya Server &amp; Domain</h5>
                            <small class="text-muted">Tagihan berlangganan yang belum lunas, diurutkan dari yang paling dekat.</small>
                        </div>
                        <a href="{{ route('admin.sistem-platform.expenses') }}" class="btn btn-sm btn-outline-danger">Kelola</a>
                    </div>
                    <div class="card-body pt-3">
                        @if(count($biaya['items']))
                            <div class="row g-3">
                                @foreach($biaya['items'] as $t)
                                    @php
                                        $warna = $t['sisa_hari'] < 0 ? 'danger' : ($t['sisa_hari'] <= 14 ? 'warning' : 'success');
                                        $ikonKategori = match($t['kategori']) {
                                            'domain' => 'bx-globe',
                                            'hosting' => 'bx-server',
                                            'ssl' => 'bx-lock-alt',
                                            'api_service' => 'bx-plug',
                                            default => 'bx-receipt',
                                        };
                                    @endphp
                                    <div class="col-md-6 col-xl-4">
                                        <div class="border rounded-3 h-100 p-3 border-{{ $warna }}" style="border-width:1px">
                                            <div class="d-flex align-items-center mb-2">
                                                <div class="avatar avatar-sm flex-shrink-0 me-2">
                                                    <span class="avatar-initial rounded bg-label-{{ $warna }}"><i class="bx {{ $ikonKategori }}"></i></span>
                                                </div>
                                                <div class="min-w-0 flex-grow-1">
                                                    <div class="fw-semibold small text-truncate">{{ $t['nama'] }}</div>
                                                    <small class="text-muted" style="font-size:.7rem">
                                                        {{ ucfirst(str_replace('_', ' ', $t['kategori'])) }} &middot; {{ ucfirst(str_replace('_', ' ', $t['siklus'])) }}
                                                    </small>
                                                </div>
                                            </div>

                                            <div class="d-flex justify-content-between align-items-baseline mb-2">
                                                <span class="fw-bold">Rp {{ number_format($t['nominal'], 0, ',', '.') }}</span>
                                                <small class="text-muted" style="font-size:.72rem">
                                                    {{ \Carbon\Carbon::parse($t['jatuh_tempo'])->translatedFormat('d M Y') }}
                                                </small>
                                            </div>

                                            {{-- Hitung mundur hidup; diperbarui tiap detik oleh skrip di bawah --}}
                                            <div class="bg-label-{{ $warna }} rounded-2 text-center py-2 px-2 hitung-mundur"
                                                 data-tenggat="{{ $t['tenggat_iso'] }}">
                                                <div class="fw-bold" style="font-size:.95rem; line-height:1.2">
                                                    <span class="nilai">&mdash;</span>
                                                </div>
                                                <small style="font-size:.65rem; opacity:.85" class="keterangan">menuju jatuh tempo</small>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-muted small text-center mb-0 py-3">
                                Tidak ada tagihan aktif. Tambahkan lewat halaman Biaya Server &amp; Domain.
                            </p>
                        @endif
                    </div>
                </div>
            </div>
            @endif
            {{-- ============ Ringkasan unit layanan ============ --}}
            <div class="col-lg-7">
                <div class="card h-100">
                    <div class="card-header pb-2">
                        <h5 class="mb-0"><i class="bx bx-bar-chart-alt-2 me-2 text-primary"></i>Unit Layanan Usaha</h5>
                        <small class="text-muted">Agregat seluruh wilayah, bukan per desa.</small>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-sm mb-0">
                            <thead>
                                <tr class="text-muted" style="font-size:.75rem">
                                    <th>Unit</th>
                                    <th class="text-end">Total</th>
                                    <th class="text-end">Selesai</th>
                                    <th class="text-end">Menunggu</th>
                                    <th class="text-end">Gagal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($unit as $u)
                                    <tr>
                                        <td class="fw-semibold small">{{ $u['label'] }}</td>
                                        <td class="text-end small">{{ number_format($u['total'], 0, ',', '.') }}</td>
                                        <td class="text-end small text-success">{{ $u['selesai'] }}</td>
                                        <td class="text-end small text-warning">{{ $u['menunggu'] }}</td>
                                        <td class="text-end small text-danger">{{ $u['gagal'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="card-footer text-end py-2">
                        @if(auth()->user()->hasPlatformPermission('platform_monitoring'))
                        <a href="{{ route('admin.sistem-platform.monitoring') }}" class="small">
                            Monitoring rinci <i class="bx bx-chevron-right"></i>
                        </a>
                        @endif
                    </div>
                </div>
            </div>

            {{-- ============ Status integrasi ============ --}}
            @if(auth()->user()->hasPlatformPermission('platform_integrasi'))
            <div class="col-lg-5">
                <div class="card h-100">
                    <div class="card-header pb-2">
                        <h5 class="mb-0"><i class="bx bx-plug me-2 text-info"></i>Integrasi Pihak Ketiga</h5>
                        <small class="text-muted">Kategori yang belum diisi memakai nilai <code>.env</code>.</small>
                    </div>
                    <div class="card-body pt-2">
                        @foreach($integrasi as $i)
                            <div class="d-flex align-items-center py-2 border-bottom">
                                <i class="bx {{ $i['ikon'] }} me-2 text-muted"></i>
                                <span class="flex-grow-1 small">{{ $i['label'] }}</span>
                                @if($i['aktif'])
                                    <span class="badge bg-label-success rounded-pill">Aktif</span>
                                @else
                                    <span class="badge bg-label-secondary rounded-pill">Belum diisi</span>
                                @endif
                            </div>
                        @endforeach
                        <div class="text-end mt-3">
                            <a href="{{ route('admin.sistem-platform.gateway') }}" class="btn btn-sm btn-outline-primary">
                                <i class="bx bx-cog me-1"></i>Kelola API Key
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            {{-- ============ Aktivitas terbaru ============ --}}
            <div class="col-lg-7">
                <div class="card h-100">
                    <div class="card-header pb-2 d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="mb-0"><i class="bx bx-history me-2 text-secondary"></i>Aktivitas Terbaru</h5>
                            <small class="text-muted">Delapan catatan terakhir dari log aktivitas.</small>
                        </div>
                        @if(auth()->user()->hasPlatformPermission('platform_keamanan'))
                        <a href="{{ route('admin.sistem-platform.security-log') }}" class="small">Log keamanan</a>
                        @endif
                    </div>
                    <div class="card-body pt-2">
                        @forelse($aktivitas as $log)
                            <div class="d-flex align-items-start py-2 border-bottom">
                                <div class="avatar avatar-sm flex-shrink-0 me-3">
                                    <span class="avatar-initial rounded-circle bg-label-secondary">
                                        <i class="bx bx-user"></i>
                                    </span>
                                </div>
                                <div class="flex-grow-1 min-w-0">
                                    <div class="small fw-semibold text-truncate">
                                        {{ $log['nama'] ?? 'Sistem' }}
                                        <span class="badge bg-label-primary ms-1" style="font-size:.6rem">{{ $log['action'] }}</span>
                                    </div>
                                    <div class="text-muted text-truncate" style="font-size:.75rem">{{ $log['description'] }}</div>
                                </div>
                                <small class="text-muted flex-shrink-0 ms-2" style="font-size:.7rem">
                                    {{ $log['created_at'] ? \Carbon\Carbon::parse($log['created_at'])->diffForHumans(short: true) : '' }}
                                </small>
                            </div>
                        @empty
                            <p class="text-muted text-center small mb-0 py-3">Belum ada aktivitas tercatat.</p>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- ============ Komposisi pengguna ============ --}}
            <div class="col-lg-5">
                <div class="card h-100">
                    <div class="card-header pb-2">
                        <h5 class="mb-0"><i class="bx bx-group me-2 text-success"></i>Komposisi Pengguna</h5>
                        @if($pengguna['belum_aktif'] > 0)
                            <small class="text-warning">{{ $pengguna['belum_aktif'] }} akun belum aktif.</small>
                        @else
                            <small class="text-muted">Semua akun berstatus aktif.</small>
                        @endif
                    </div>
                    <div class="card-body pt-2">
                        @forelse($pengguna['per_role'] as $role => $n)
                            @php $persen = $pengguna['total'] ? round($n / $pengguna['total'] * 100) : 0; @endphp
                            <div class="mb-2">
                                <div class="d-flex justify-content-between small">
                                    <span>{{ str_replace('_', ' ', ucfirst($role)) }}</span>
                                    <span class="text-muted">{{ $n }}</span>
                                </div>
                                <div class="progress" style="height:5px">
                                    <div class="progress-bar" style="width: {{ $persen }}%"></div>
                                </div>
                            </div>
                        @empty
                            <p class="text-muted small mb-0">Belum ada pengguna.</p>
                        @endforelse
                        {{-- Mendaftarkan akun staf: dibuatkan admin, orangnya
                             langsung bisa login memakai email & sandi itu. --}}
                        <div class="d-flex gap-2 mt-3">
                            @if(Route::has('admin.staff.create'))
                                <a href="{{ route('admin.staff.create') }}" class="btn btn-sm btn-primary flex-grow-1">
                                    <i class="bx bx-user-plus me-1"></i>Tambah Akun Staf
                                </a>
                            @endif
                            {{-- Menuju Kelola Staf, bukan Manajemen Pengguna: yang terakhir
                                 itu data warga per wilayah dan tidak ada di sidebar super admin. --}}
                            <a href="{{ route('admin.staff.index') }}" class="btn btn-sm btn-outline-secondary">
                                Kelola Staf
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ============ Biaya operasional + pintasan ============ --}}
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="row g-4 align-items-center">
                            <div class="col-12">
                                <h6 class="fw-bold mb-2"><i class="bx bx-grid-alt me-2 text-primary"></i>Pintasan Modul</h6>
                                <div class="d-flex flex-wrap gap-2">
                                    @php
                                        // Elemen keempat = izin platform yang diperlukan.
                                        // Tanpa penyaringan ini, akun staf platform akan
                                        // melihat tombol yang pasti berujung ditolak 403.
                                        $pintasan = [
                                            ['Monitoring Transaksi', 'admin.sistem-platform.monitoring', 'bx-line-chart', 'platform_monitoring'],
                                            ['Integrasi & API Key', 'admin.sistem-platform.gateway', 'bx-plug', 'platform_integrasi'],
                                            ['Log Keamanan', 'admin.sistem-platform.security-log', 'bx-shield', 'platform_keamanan'],
                                            ['Biaya Server & Domain', 'admin.sistem-platform.expenses', 'bx-server', 'platform_biaya'],
                                            ['Kelola Staf', 'admin.staff.index', 'bx-user-voice', 'platform_staf'],
                                            ['Tambah Akun Staf', 'admin.staff.create', 'bx-user-plus', 'platform_staf'],
                                            ['Banner', 'admin.banners.index', 'bx-image', 'platform_banner'],
                                            ['Log Aktivitas', 'admin.laporan.log', 'bx-history', 'platform_aktivitas'],
                                        ];
                                    @endphp
                                    @foreach($pintasan as $p)
                                        @php
                                            [$label, $rute, $ikon] = $p;
                                            $izin = $p[3] ?? null;
                                            $boleh = ! $izin || auth()->user()->hasPlatformPermission($izin);
                                        @endphp
                                        @if($boleh && Route::has($rute))
                                            <a href="{{ route($rute) }}" class="btn btn-sm btn-label-secondary">
                                                <i class="bx {{ $ikon }} me-1"></i>{{ $label }}
                                            </a>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

{{-- Hitung mundur dijalankan di browser supaya angkanya bergerak tanpa reload.
     Sumber kebenarannya tetap tanggal dari server (data-tenggat, ISO 8601). --}}
<script>
(function () {
    const kotak = document.querySelectorAll('.hitung-mundur');
    if (!kotak.length) return;

    function perbarui() {
        const sekarang = Date.now();

        kotak.forEach(function (el) {
            const tenggat = new Date(el.dataset.tenggat).getTime();
            if (isNaN(tenggat)) return;

            const nilai = el.querySelector('.nilai');
            const ket = el.querySelector('.keterangan');
            let selisih = tenggat - sekarang;
            const lewat = selisih < 0;
            selisih = Math.abs(selisih);

            const hari = Math.floor(selisih / 86400000);
            const jam = Math.floor((selisih % 86400000) / 3600000);
            const menit = Math.floor((selisih % 3600000) / 60000);
            const detik = Math.floor((selisih % 60000) / 1000);

            nilai.textContent = hari > 0
                ? hari + ' hari ' + jam + ' jam'
                : jam + ':' + String(menit).padStart(2, '0') + ':' + String(detik).padStart(2, '0');

            ket.textContent = lewat ? 'TERLAMBAT sejak jatuh tempo' : 'menuju jatuh tempo';
        });
    }

    perbarui();
    setInterval(perbarui, 1000);
})();
</script>
@endsection
