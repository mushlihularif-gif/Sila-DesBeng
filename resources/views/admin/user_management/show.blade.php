@extends('admin.layouts.admin')

@section('title', 'Detail Pengguna - ' . $user->name)

@section('content')
<style>
    .animate-fade-up {
        animation: fadeUp 0.5s ease-out forwards;
    }
    @keyframes fadeUp {
        0% { opacity: 0; transform: translateY(20px); }
        100% { opacity: 1; transform: translateY(0); }
    }
    .profile-avatar-circle {
        width: 100px;
        height: 100px;
        border-radius: 50%;
        background: #f8f9fa;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 3.5rem;
        color: #696cff;
        border: 4px solid #fff;
        box-shadow: 0 4px 15px rgba(0,0,0,0.08);
        margin: 0 auto;
    }
    .info-label {
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 1px;
        color: #a1acb8;
        font-weight: 700;
        margin-bottom: 0.25rem;
    }
    .info-value {
        font-size: 1.05rem;
        color: #38424d;
        font-weight: 600;
        margin-bottom: 1.5rem;
    }
    .table-modern {
        border-collapse: separate;
        border-spacing: 0 10px;
    }
    .table-modern tbody tr {
        box-shadow: 0 2px 6px rgba(0,0,0,0.02);
        border-radius: 8px;
        transition: all 0.2s;
        background: #fff;
    }
    .table-modern tbody tr:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    }
    .table-modern td {
        border: none;
        padding: 1.2rem 1.5rem;
        vertical-align: middle;
    }
    .table-modern td:first-child { border-top-left-radius: 8px; border-bottom-left-radius: 8px; }
    .table-modern td:last-child { border-top-right-radius: 8px; border-bottom-right-radius: 8px; }
    
    .status-dot {
        display: inline-block;
        width: 10px;
        height: 10px;
        border-radius: 50%;
        margin-right: 8px;
    }
    .status-dot.aktif { background-color: #16a34a; box-shadow: 0 0 0 3px rgba(22, 163, 74, 0.2); }
    .status-dot.nonaktif { background-color: #dc2626; box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.2); }
    
    .nav-tabs-modern {
        border-bottom: 2px solid #f0f2f4;
        display: flex;
        flex-wrap: nowrap;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        scrollbar-width: none;
    }
    .nav-tabs-modern::-webkit-scrollbar {
        display: none;
    }
    .nav-tabs-modern .nav-item {
        flex: 0 0 auto;
    }
    .nav-tabs-modern .nav-link {
        border: none;
        color: #697a8d;
        font-weight: 600;
        padding: 0.75rem 0.9rem;
        font-size: 0.88rem;
        border-radius: 8px 8px 0 0;
        margin-right: 0.25rem;
        transition: all 0.2s;
        position: relative;
        white-space: nowrap;
        background: transparent !important;
    }
    .nav-tabs-modern .nav-link:hover {
        color: #696cff;
    }
    .nav-tabs-modern .nav-link.active {
        color: #696cff;
        background: transparent !important;
    }
    .nav-tabs-modern .nav-link.active::after {
        content: '';
        position: absolute;
        bottom: -2px;
        left: 0;
        right: 0;
        height: 3px;
        background: #696cff;
        border-radius: 3px 3px 0 0;
    }
    @media (min-width: 992px) {
        .profile-sidebar-card {
            position: sticky;
            top: 24px;
            z-index: 10;
        }
    }
</style>

<div class="container-xxl flex-grow-1 container-p-y animate-fade-up">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h4 class="fw-bold py-2 mb-0" style="font-size: 1.25rem;">
                <span class="text-muted fw-light">Sistem / Manajemen Pengguna /</span> Detail Profil
            </h4>
            <a href="{{ route('admin.manajemen-pengguna.index') }}" class="btn btn-label-secondary shadow-sm">
                <i class="bx bx-arrow-back me-1"></i> Kembali
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Sidebar Kiri: Info Singkat & Aksi -->
        <div class="col-xl-4 col-lg-5 col-md-12 mb-4">
            <div class="card border-0 shadow-sm text-center profile-sidebar-card" style="border-radius: 16px; overflow: hidden;">
                <!-- Background Banner -->
                <div style="height: 100px; background: linear-gradient(135deg, #696cff 0%, #00d4ff 100%);"></div>
                
                <div class="card-body pt-0" style="margin-top: -50px;">
                    <div class="profile-avatar-circle mb-3" style="overflow: hidden;">
                        @if($user->avatar)
                            <img src="{{ route('media.avatar', ['filename' => basename($user->avatar)]) }}" alt="Avatar" class="w-100 h-100" style="object-fit: cover;">
                        @elseif($user->file)
                            <img src="{{ route('media.avatar', ['filename' => basename($user->file->path)]) }}" alt="Avatar" class="w-100 h-100" style="object-fit: cover;">
                        @else
                            @php
                                $initials = collect(explode(' ', $user->name))->map(function($segment) { return strtoupper(substr($segment, 0, 1)); })->take(2)->join('');
                            @endphp
                            <span style="font-size: 2.5rem; font-weight: 700; color: #696cff; letter-spacing: 1px;">{{ $initials }}</span>
                        @endif
                    </div>
                    <h4 class="fw-bold mb-1">{{ $user->name }}</h4>
                    <p class="text-muted mb-4">{{ '@' . ($user->username ?? explode('@', $user->email)[0]) }}</p>
                    
                    <div class="d-flex justify-content-center align-items-center mb-4">
                        <span class="status-dot {{ $user->status === 'aktif' ? 'aktif' : 'nonaktif' }}"></span>
                        <span class="fw-bold text-{{ $user->status === 'aktif' ? 'success' : 'danger' }}">
                            {{ $user->status === 'aktif' ? 'Akun Aktif' : 'Akun Ditangguhkan' }}
                        </span>
                    </div>

                    <form action="{{ route('admin.manajemen-pengguna.toggle-status', $user->id) }}" method="POST" onsubmit="return confirm('Yakin ingin mengubah status akun ini?')">
                        @csrf
                        @method('PUT')
                        @if($user->status === 'aktif')
                        <button type="submit" class="btn btn-outline-danger w-100 rounded-pill py-2 fw-semibold">
                            <i class="bx bx-block me-1"></i> Nonaktifkan Pengguna
                        </button>
                        @else
                        <button type="submit" class="btn btn-success w-100 rounded-pill py-2 shadow-sm fw-semibold">
                            <i class="bx bx-check-shield me-1"></i> Aktifkan Pengguna
                        </button>
                        @endif
                    </form>
                </div>
                <div class="card-footer bg-light bg-opacity-50 border-top py-4">
                    <div class="d-flex justify-content-between text-start px-2">
                        <div>
                            <div class="info-label">Jenis Kelamin</div>
                            <div class="fw-semibold text-dark"><i class="bx bx-male-sign text-muted me-1"></i> {{ ucfirst($user->gender) ?? '-' }}</div>
                        </div>
                        <div class="text-end">
                            <div class="info-label">Bergabung Pada</div>
                            <div class="fw-semibold text-dark"><i class="bx bx-calendar text-muted me-1"></i> {{ $user->created_at->format('d M Y') }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Konten Kanan: Detail & Transaksi -->
        <div class="col-xl-8 col-lg-7 col-md-12">
            <!-- Card Informasi Kontak -->
            <div class="card border-0 shadow-sm mb-4" style="border-radius: 16px;">
                <div class="card-header bg-white border-bottom py-4">
                    <h5 class="mb-0 fw-bold text-primary"><i class="bx bx-id-card fs-4 me-2 align-middle"></i> Informasi Pribadi & Kontak</h5>
                </div>
                <div class="card-body pt-4">
                    <div class="row">
                        <div class="col-sm-6">
                            <div class="info-label"><i class="bx bx-envelope"></i> Alamat Email</div>
                            <div class="info-value">{{ $user->email }}</div>
                        </div>
                        <div class="col-sm-6">
                            <div class="info-label"><i class="bx bx-phone"></i> Nomor Telepon</div>
                            <div class="info-value">{{ $user->phone ?? 'Tidak ada data' }}</div>
                        </div>
                        <div class="col-12 mt-2">
                            <div class="info-label"><i class="bx bx-map"></i> Alamat Lengkap</div>
                            <div class="info-value mb-0 p-3 bg-light rounded" style="border: 1px dashed #d9dee3;">
                                {{ $user->address ?? 'Pengguna belum mengisi detail alamat.' }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card Riwayat Transaksi (Tabs) -->
            <div class="card border-0 shadow-sm" style="border-radius: 16px;">
                <div class="card-header bg-white pt-4 pb-0 border-bottom-0">
                    <h5 class="mb-4 fw-bold text-primary"><i class="bx bx-history fs-4 me-2 align-middle"></i> Riwayat Aktivitas Terbaru</h5>
                    <ul class="nav nav-tabs nav-tabs-modern" role="tablist">
                        <li class="nav-item">
                            <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-penyewaan" role="tab"><i class="bx bx-box me-1"></i> Sewa Alat</button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-gas" role="tab"><i class="bx bx-gas-pump me-1"></i> Gas</button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-mobil" role="tab"><i class="bx bx-car me-1"></i> Sewa Mobil</button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-fasilitas" role="tab"><i class="bx bx-building-house me-1"></i> Fasilitas</button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-pasar" role="tab"><i class="bx bx-store-alt me-1"></i> Pasar</button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-laporan" role="tab"><i class="bx bx-message-error me-1"></i> Pelaporan</button>
                        </li>
                    </ul>
                </div>
                
                <div class="card-body bg-light bg-opacity-25 pt-4">
                    <div class="tab-content p-0 border-0 bg-transparent">
                        
                        <!-- Tab Penyewaan -->
                        <div class="tab-pane fade show active" id="tab-penyewaan" role="tabpanel">
                            @if($user->rentalTransactions->count() > 0)
                            <div class="table-responsive w-100">
                                <table class="table table-modern align-middle">
                                    <thead class="text-muted" style="font-size: 0.75rem; letter-spacing: 1px;">
                                        <tr>
                                            <th>ID</th>
                                            <th>TANGGAL</th>
                                            <th>ITEM / ALAT</th>
                                            <th>STATUS</th>
                                            <th class="text-end">TOTAL</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($user->rentalTransactions as $trans)
                                        <tr>
                                            <td class="fw-bold text-primary">#{{ $trans->id }}</td>
                                            <td><span class="text-muted">{{ $trans->created_at->format('d M Y') }}</span></td>
                                            <td class="fw-semibold">{{ $trans->item_name ?? $trans->barang->nama_barang ?? 'N/A' }}</td>
                                            <td><span class="badge bg-label-{{ $trans->status === 'completed' ? 'success' : ($trans->status === 'pending' ? 'warning' : 'secondary') }}">{{ ucfirst($trans->status ?: 'N/A') }}</span></td>
                                            <td class="text-end fw-bold text-dark">Rp {{ number_format($trans->total_amount ?? 0, 0, ',', '.') }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            @else
                            <div class="text-center py-5">
                                <div class="bg-white p-4 rounded-circle d-inline-block shadow-sm mb-3 border"><img src="{{ asset('User/img/elemen/F1.png') }}" alt="Sewa Alat" style="width: 48px; height: 48px; object-fit: contain;"></div>
                                <h6 class="fw-bold text-dark mb-1">Belum Ada Riwayat</h6>
                                <p class="text-muted mb-0">Pengguna ini belum pernah melakukan penyewaan alat.</p>
                            </div>
                            @endif
                        </div>

                        <!-- Tab Gas -->
                        <div class="tab-pane fade" id="tab-gas" role="tabpanel">
                            @if($user->gasTransactions->count() > 0)
                            <div class="table-responsive w-100">
                                <table class="table table-modern align-middle">
                                    <thead class="text-muted" style="font-size: 0.75rem; letter-spacing: 1px;">
                                        <tr>
                                            <th>ID</th>
                                            <th>TANGGAL</th>
                                            <th>JENIS GAS</th>
                                            <th>JML</th>
                                            <th>STATUS</th>
                                            <th class="text-end">TOTAL</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($user->gasTransactions as $trans)
                                        <tr>
                                            <td class="fw-bold text-primary">#{{ $trans->id }}</td>
                                            <td><span class="text-muted">{{ $trans->created_at->format('d M Y') }}</span></td>
                                            <td class="fw-semibold">{{ $trans->item_name ?? $trans->gas->jenis_gas ?? 'N/A' }}</td>
                                            <td>{{ $trans->quantity ?? 0 }} <span class="text-muted small">TBG</span></td>
                                            <td><span class="badge bg-label-{{ $trans->status === 'completed' ? 'success' : ($trans->status === 'pending' ? 'warning' : 'secondary') }}">{{ ucfirst($trans->status ?: 'N/A') }}</span></td>
                                            <td class="text-end fw-bold text-dark">Rp {{ number_format(($trans->price * $trans->quantity) ?? 0, 0, ',', '.') }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            @else
                            <div class="text-center py-5">
                                <div class="bg-white p-4 rounded-circle d-inline-block shadow-sm mb-3 border"><img src="{{ asset('User/img/elemen/F2.png') }}" alt="Gas" style="width: 48px; height: 48px; object-fit: contain;"></div>
                                <h6 class="fw-bold text-dark mb-1">Belum Ada Riwayat</h6>
                                <p class="text-muted mb-0">Pengguna ini belum pernah melakukan pemesanan gas.</p>
                            </div>
                            @endif
                        </div>

                        <!-- Tab Mobil -->
                        <div class="tab-pane fade" id="tab-mobil" role="tabpanel">
                            @if($user->mobilTransactions->count() > 0)
                            <div class="table-responsive w-100">
                                <table class="table table-modern align-middle">
                                    <thead class="text-muted" style="font-size: 0.75rem; letter-spacing: 1px;">
                                        <tr>
                                            <th>ID</th>
                                            <th>TANGGAL</th>
                                            <th>MOBIL</th>
                                            <th>STATUS</th>
                                            <th class="text-end">TOTAL</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($user->mobilTransactions as $trans)
                                        <tr>
                                            <td class="fw-bold text-primary">#{{ $trans->id }}</td>
                                            <td><span class="text-muted">{{ $trans->created_at->format('d M Y') }}</span></td>
                                            <td class="fw-semibold">{{ $trans->mobil->nama_mobil ?? 'N/A' }}</td>
                                            <td><span class="badge bg-label-{{ $trans->status === 'completed' ? 'success' : ($trans->status === 'pending' ? 'warning' : 'secondary') }}">{{ ucfirst($trans->status ?: 'N/A') }}</span></td>
                                            <td class="text-end fw-bold text-dark">Rp {{ number_format($trans->total_price ?? 0, 0, ',', '.') }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            @else
                            <div class="text-center py-5">
                                <div class="bg-white p-4 rounded-circle d-inline-block shadow-sm mb-3 border"><img src="{{ asset('User/img/elemen/mobil.png') }}" alt="Mobil" style="width: 48px; height: 48px; object-fit: contain;"></div>
                                <h6 class="fw-bold text-dark mb-1">Belum Ada Riwayat</h6>
                                <p class="text-muted mb-0">Pengguna ini belum pernah melakukan penyewaan mobil.</p>
                            </div>
                            @endif
                        </div>

                        <!-- Tab Fasilitas -->
                        <div class="tab-pane fade" id="tab-fasilitas" role="tabpanel">
                            @if($user->fasilitasTransactions->count() > 0)
                            <div class="table-responsive w-100">
                                <table class="table table-modern align-middle">
                                    <thead class="text-muted" style="font-size: 0.75rem; letter-spacing: 1px;">
                                        <tr>
                                            <th>ID</th>
                                            <th>TANGGAL</th>
                                            <th>FASILITAS</th>
                                            <th>STATUS</th>
                                            <th class="text-end">TOTAL</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($user->fasilitasTransactions as $trans)
                                        <tr>
                                            <td class="fw-bold text-primary">#{{ $trans->id }}</td>
                                            <td><span class="text-muted">{{ $trans->created_at->format('d M Y') }}</span></td>
                                            <td class="fw-semibold">{{ $trans->fasilitas->nama_fasilitas ?? 'N/A' }}</td>
                                            <td><span class="badge bg-label-{{ $trans->status === 'completed' ? 'success' : ($trans->status === 'pending' ? 'warning' : 'secondary') }}">{{ ucfirst($trans->status ?: 'N/A') }}</span></td>
                                            <td class="text-end fw-bold text-dark">Rp {{ number_format($trans->total_price ?? 0, 0, ',', '.') }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            @else
                            <div class="text-center py-5">
                                <div class="bg-white p-4 rounded-circle d-inline-block shadow-sm mb-3 border"><img src="{{ asset('User/img/elemen/fasilitas.png') }}" alt="Fasilitas" style="width: 48px; height: 48px; object-fit: contain;"></div>
                                <h6 class="fw-bold text-dark mb-1">Belum Ada Riwayat</h6>
                                <p class="text-muted mb-0">Pengguna ini belum pernah menyewa fasilitas umum.</p>
                            </div>
                            @endif
                        </div>

                        <!-- Tab Pasar -->
                        <div class="tab-pane fade" id="tab-pasar" role="tabpanel">
                            @if($user->pasarTransactions->count() > 0)
                            <div class="table-responsive w-100">
                                <table class="table table-modern align-middle">
                                    <thead class="text-muted" style="font-size: 0.75rem; letter-spacing: 1px;">
                                        <tr>
                                            <th>ID</th>
                                            <th>TANGGAL</th>
                                            <th>PRODUK</th>
                                            <th>STATUS</th>
                                            <th class="text-end">TOTAL</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($user->pasarTransactions as $trans)
                                        <tr>
                                            <td class="fw-bold text-primary">#{{ $trans->id }}</td>
                                            <td><span class="text-muted">{{ $trans->created_at->format('d M Y') }}</span></td>
                                            <td class="fw-semibold">{{ $trans->items->count() }} Macam Produk</td>
                                            <td><span class="badge bg-label-{{ $trans->status === 'completed' ? 'success' : ($trans->status === 'pending' ? 'warning' : 'secondary') }}">{{ ucfirst($trans->status ?: 'N/A') }}</span></td>
                                            <td class="text-end fw-bold text-dark">Rp {{ number_format($trans->total_price ?? 0, 0, ',', '.') }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            @else
                            <div class="text-center py-5">
                                <div class="bg-white p-4 rounded-circle d-inline-block shadow-sm mb-3 border"><img src="{{ asset('Admin/img/pasardaerah/PasarDaerah2.png') }}" alt="Pasar" style="width: 48px; height: 48px; object-fit: contain;"></div>
                                <h6 class="fw-bold text-dark mb-1">Belum Ada Riwayat</h6>
                                <p class="text-muted mb-0">Pengguna ini belum pernah belanja di Pasar Daerah.</p>
                            </div>
                            @endif
                        </div>

                        <!-- Tab Laporan -->
                        <div class="tab-pane fade" id="tab-laporan" role="tabpanel">
                            @if($user->laporans->count() > 0)
                            <div class="table-responsive w-100">
                                <table class="table table-modern align-middle">
                                    <thead class="text-muted" style="font-size: 0.75rem; letter-spacing: 1px;">
                                        <tr>
                                            <th>ID</th>
                                            <th>TANGGAL</th>
                                            <th>JUDUL LAPORAN</th>
                                            <th>KATEGORI</th>
                                            <th class="text-end">STATUS</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($user->laporans as $trans)
                                        <tr>
                                            <td class="fw-bold text-primary">#{{ $trans->id }}</td>
                                            <td><span class="text-muted">{{ $trans->created_at->format('d M Y') }}</span></td>
                                            <td class="fw-semibold">{{ Str::limit($trans->title, 30) }}</td>
                                            <td>{{ $trans->category ?? 'N/A' }}</td>
                                            <td class="text-end"><span class="badge bg-label-{{ $trans->status === 'selesai' ? 'success' : ($trans->status === 'pending' ? 'warning' : 'secondary') }}">{{ ucfirst($trans->status ?: 'N/A') }}</span></td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            @else
                            <div class="text-center py-5">
                                <div class="bg-white p-4 rounded-circle d-inline-block shadow-sm mb-3 border"><img src="{{ asset('User/img/elemen/lapor.png') }}" alt="Pelaporan" style="width: 48px; height: 48px; object-fit: contain;"></div>
                                <h6 class="fw-bold text-dark mb-1">Belum Ada Riwayat</h6>
                                <p class="text-muted mb-0">Pengguna ini belum pernah membuat pelaporan.</p>
                            </div>
                            @endif
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
