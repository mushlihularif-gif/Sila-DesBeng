@extends('admin.layouts.admin')

@section('title', 'Kelola Laporan')

@section('content')
<div class="container-fluid py-4">

    <!-- Judul Header Halaman -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold fs-3 mb-1 text-primary">Kelola Semua Laporan</h4>
            <p class="text-muted mb-0">Pemantauan dan manajemen laporan warga dari seluruh RW</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('lurah.laporan.index') }}" class="btn btn-white border shadow-sm rounded-pill px-4">
                <i class="bx bx-refresh me-2"></i>Refresh
            </a>
        </div>
    </div>

    <!-- Statistik Laporan -->
    <div class="row g-3 mb-4 row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-6">
        <!-- Total -->
        <div class="col">
            <div class="card border-0 shadow-sm h-100 rounded-4">
                <div class="card-body p-3 d-flex flex-column align-items-center justify-content-center text-center">
                    <div class="avatar avatar-md bg-primary-subtle text-primary rounded-circle p-2 mb-2">
                        <i class="bx bx-clipboard fs-3"></i>
                    </div>
                    <small class="text-muted text-uppercase fw-bold ls-1 mb-1" style="font-size: 0.65rem;">Total Laporan</small>
                    <h3 class="fw-bold mb-0 text-dark">{{ $stats['total_laporan'] }}</h3>
                </div>
            </div>
        </div>
        <!-- Tertunda -->
        <div class="col">
            <div class="card border-0 shadow-sm h-100 rounded-4">
                <div class="card-body p-3 d-flex flex-column align-items-center justify-content-center text-center">
                    <div class="avatar avatar-md bg-warning-subtle text-warning rounded-circle p-2 mb-2">
                        <i class="bx bx-time fs-3"></i>
                    </div>
                    <small class="text-muted text-uppercase fw-bold ls-1 mb-1" style="font-size: 0.65rem;">Tertunda</small>
                    <h3 class="fw-bold mb-0 text-dark">{{ $stats['pending'] }}</h3>
                </div>
            </div>
        </div>
        <!-- Proses -->
        <div class="col">
            <div class="card border-0 shadow-sm h-100 rounded-4">
                <div class="card-body p-3 d-flex flex-column align-items-center justify-content-center text-center">
                    <div class="avatar avatar-md bg-info-subtle text-info rounded-circle p-2 mb-2">
                        <i class="bx bx-loader-circle fs-3"></i>
                    </div>
                    <small class="text-muted text-uppercase fw-bold ls-1 mb-1" style="font-size: 0.65rem;">Proses</small>
                    <h3 class="fw-bold mb-0 text-dark">{{ $stats['proses'] }}</h3>
                </div>
            </div>
        </div>
        <!-- Selesai -->
        <div class="col">
            <div class="card border-0 shadow-sm h-100 rounded-4">
                <div class="card-body p-3 d-flex flex-column align-items-center justify-content-center text-center">
                    <div class="avatar avatar-md bg-success-subtle text-success rounded-circle p-2 mb-2">
                        <i class="bx bx-check-circle fs-3"></i>
                    </div>
                    <small class="text-muted text-uppercase fw-bold ls-1 mb-1" style="font-size: 0.65rem;">Selesai</small>
                    <h3 class="fw-bold mb-0 text-dark">{{ $stats['selesai'] }}</h3>
                </div>
            </div>
        </div>
        <!-- Diteruskan -->
        <div class="col">
            <div class="card border-0 shadow-sm h-100 rounded-4">
                <div class="card-body p-3 d-flex flex-column align-items-center justify-content-center text-center">
                    <div class="avatar avatar-md bg-secondary-subtle text-secondary rounded-circle p-2 mb-2">
                        <i class="bx bx-share fs-3"></i>
                    </div>
                    <small class="text-muted text-uppercase fw-bold ls-1 mb-1" style="font-size: 0.65rem;">Diteruskan</small>
                    <h3 class="fw-bold mb-0 text-dark">{{ $stats['diteruskan'] ?? 0 }}</h3>
                </div>
            </div>
        </div>
        <!-- Ditolak -->
        <div class="col">
            <div class="card border-0 shadow-sm h-100 rounded-4">
                <div class="card-body p-3 d-flex flex-column align-items-center justify-content-center text-center">
                    <div class="avatar avatar-md bg-danger-subtle text-danger rounded-circle p-2 mb-2">
                        <i class="bx bx-x-circle fs-3"></i>
                    </div>
                    <small class="text-muted text-uppercase fw-bold ls-1 mb-1" style="font-size: 0.65rem;">Ditolak</small>
                    <h3 class="fw-bold mb-0 text-dark">{{ $stats['ditolak'] }}</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Status Pills -->
    <div class="d-flex gap-2 mb-4 overflow-auto pb-2">
        <a href="{{ route('lurah.laporan.index') }}" 
           class="btn btn-sm rounded-pill px-3 {{ !request('status') ? 'btn-dark' : 'btn-outline-dark border-0 bg-white shadow-sm' }}">
            Semua
        </a>
        <a href="{{ route('lurah.laporan.index', ['status' => 'Pending'] + request()->except('status')) }}" 
           class="btn btn-sm rounded-pill px-3 {{ request('status') == 'Pending' ? 'btn-warning text-white' : 'btn-outline-secondary border-0 bg-white shadow-sm' }}">
            Tertunda
        </a>
        <a href="{{ route('lurah.laporan.index', ['status' => 'Proses'] + request()->except('status')) }}" 
           class="btn btn-sm rounded-pill px-3 {{ request('status') == 'Proses' ? 'btn-info text-white' : 'btn-outline-secondary border-0 bg-white shadow-sm' }}">
            Proses
        </a>
        <a href="{{ route('lurah.laporan.index', ['status' => 'Selesai'] + request()->except('status')) }}" 
           class="btn btn-sm rounded-pill px-3 {{ request('status') == 'Selesai' ? 'btn-success text-white' : 'btn-outline-secondary border-0 bg-white shadow-sm' }}">
            Selesai
        </a>
        <a href="{{ route('lurah.laporan.index', ['status' => 'Diteruskan'] + request()->except('status')) }}" 
           class="btn btn-sm rounded-pill px-3 {{ request('status') == 'Diteruskan' ? 'btn-secondary text-white' : 'btn-outline-secondary border-0 bg-white shadow-sm' }}">
            Diteruskan
        </a>
        <a href="{{ route('lurah.laporan.index', ['status' => 'Ditolak'] + request()->except('status')) }}" 
           class="btn btn-sm rounded-pill px-3 {{ request('status') == 'Ditolak' ? 'btn-danger text-white' : 'btn-outline-secondary border-0 bg-white shadow-sm' }}">
            Ditolak
        </a>
    </div>

    <!-- Card Utama Tabel -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="card-header bg-white border-bottom py-3 px-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div class="d-flex align-items-center gap-2">
                <i class="bx bx-list-ul fs-4 text-primary"></i>
                <h5 class="mb-0 fw-bold">Daftar Laporan</h5>
                <span class="badge bg-primary-subtle text-primary ms-2 shadow-sm">{{ $laporans->total() }}</span>
            </div>

            <!-- Search & Filter -->
            <form method="GET" action="{{ route('lurah.laporan.index') }}" class="d-flex gap-2 align-items-center flex-wrap">
                @if(request('status'))
                    <input type="hidden" name="status" value="{{ request('status') }}">
                @endif
                <select name="rw" class="form-select form-select-sm rounded-pill border shadow-sm" style="width: auto; min-width: 110px;">
                    <option value="">Semua RW</option>
                    @foreach($rwList as $rw)
                        <option value="{{ $rw->rw }}" {{ request('rw') == $rw->rw ? 'selected' : '' }}>RW {{ $rw->rw }}</option>
                    @endforeach
                </select>
                <select name="kategori" class="form-select form-select-sm rounded-pill border shadow-sm" style="width: auto; min-width: 140px;">
                    <option value="">Semua Kategori</option>
                    @foreach($kategoriList as $kat)
                        <option value="{{ $kat->kategori }}" {{ request('kategori') == $kat->kategori ? 'selected' : '' }}>{{ $kat->kategori }}</option>
                    @endforeach
                </select>
                <div class="input-group input-group-sm" style="width: 220px;">
                    <input type="text" name="search" class="form-control rounded-start-pill border shadow-sm" placeholder="Cari laporan..." value="{{ request('search') }}">
                    <button type="submit" class="btn btn-primary rounded-end-pill px-3 shadow-sm">
                        <i class="bx bx-search"></i>
                    </button>
                </div>
            </form>
        </div>

        <div class="card-body p-0">
            @if($laporans->isEmpty())
                <div class="text-center py-5">
                    <div class="mb-3"><i class="bx bx-folder-open fs-1 text-muted opacity-25"></i></div>
                    <h6 class="text-muted fw-bold">Belum ada laporan masuk</h6>
                    <p class="text-muted small mb-0">Data laporan akan tampil di sini ketika warga mengirimkan laporan.</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4 py-3 text-secondary text-uppercase small fw-bold">Pelapor</th>
                                <th class="py-3 text-secondary text-uppercase small fw-bold">Laporan</th>
                                <th class="py-3 text-secondary text-uppercase small fw-bold">RT/RW</th>
                                <th class="py-3 text-secondary text-uppercase small fw-bold">Kategori</th>
                                <th class="py-3 text-secondary text-uppercase small fw-bold">Status</th>
                                <th class="py-3 text-secondary text-uppercase small fw-bold">Tanggal</th>
                                <th class="text-end pe-4 py-3 text-secondary text-uppercase small fw-bold">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($laporans as $laporan)
                            <tr>
                                <td class="ps-4">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar avatar-sm border rounded-circle p-1 me-3">
                                            <span class="avatar-initial rounded-circle bg-primary-subtle text-primary fw-bold">
                                                {{ strtoupper(substr($laporan->user->name ?? 'N', 0, 1)) }}
                                            </span>
                                        </div>
                                        <div>
                                            <h6 class="mb-0 fw-semibold text-dark">{{ $laporan->user->name ?? 'N/A' }}</h6>
                                            <small class="text-muted">{{ $laporan->user->email ?? '-' }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="fw-medium text-dark">{{ Str::limit($laporan->nama, 35) }}</div>
                                    <small class="text-muted">{{ Str::limit($laporan->deskripsi, 45) }}</small>
                                </td>
                                <td>
                                    <div class="d-flex flex-column">
                                        <span class="fw-medium text-dark">RT {{ $laporan->rt ?? '-' }} / RW {{ $laporan->rw ?? '-' }}</span>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-light text-secondary border rounded-pill px-3 py-1">{{ $laporan->kategori }}</span>
                                </td>
                                <td>
                                    @php
                                        $statusMap = [
                                            'Pending'    => ['class' => 'bg-warning-subtle text-warning', 'icon' => 'bx-time'],
                                            'Proses'     => ['class' => 'bg-info-subtle text-info', 'icon' => 'bx-loader-circle'],
                                            'Selesai'    => ['class' => 'bg-success-subtle text-success', 'icon' => 'bx-check-circle'],
                                            'Diteruskan' => ['class' => 'bg-secondary-subtle text-secondary', 'icon' => 'bx-share'],
                                            'Ditolak'    => ['class' => 'bg-danger-subtle text-danger', 'icon' => 'bx-x-circle'],
                                        ];
                                        $sc = $statusMap[$laporan->status] ?? $statusMap['Pending'];
                                    @endphp
                                    <span class="badge {{ $sc['class'] }} rounded-pill px-3 py-2 fw-semibold">
                                        <i class="bx {{ $sc['icon'] }} me-1"></i>{{ $laporan->status }}
                                    </span>
                                </td>
                                <td>
                                    <div class="d-flex flex-column">
                                        <span class="fw-medium text-dark">{{ $laporan->created_at->format('d M Y') }}</span>
                                        <small class="text-muted">{{ $laporan->created_at->format('H:i') }} WIB</small>
                                    </div>
                                </td>
                                <td class="text-end pe-4">
                                    <a href="{{ route('lurah.laporan.show', $laporan->id) }}" 
                                       class="btn btn-sm btn-outline-primary shadow-sm rounded-pill px-3">
                                        <i class="bx bx-show me-1"></i>Detail
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        @if($laporans->hasPages())
        <div class="card-footer bg-white border-top py-3 px-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <small class="text-muted">
                Menampilkan <strong>{{ $laporans->firstItem() }}</strong> – <strong>{{ $laporans->lastItem() }}</strong> dari <strong>{{ $laporans->total() }}</strong> laporan
            </small>
            <div>
                {{ $laporans->links('pagination::bootstrap-5') }}
            </div>
        </div>
        @endif
    </div>

</div>
@endsection