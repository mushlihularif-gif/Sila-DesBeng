@php
$activeServices = $activeServices ?? [];
$isRentalActive = collect($activeServices)->contains(fn($name) => str_contains(strtolower($name), 'alat'));
$isGasActive = collect($activeServices)->contains(fn($name) => str_contains(strtolower($name), 'gas'));
$isMobilActive = collect($activeServices)->contains(fn($name) => str_contains(strtolower($name), 'mobil'));
$isFasilitasActive = collect($activeServices)->contains(fn($name) => str_contains(strtolower($name), 'fasilitas'));
$isPasarActive = collect($activeServices)->contains(fn($name) => str_contains(strtolower($name), 'pasar'));
$totalActive = collect([$isRentalActive, $isGasActive, $isMobilActive, $isFasilitasActive, $isPasarActive])->filter()->count();
@endphp

@if($totalActive === 0)
    <div class="alert alert-warning border-0 shadow-sm rounded-4 p-4 text-center mt-4">
        <div class="avatar avatar-lg bg-warning-subtle text-warning rounded-circle mx-auto mb-3">
            <i class="bx bx-info-circle fs-2"></i>
        </div>
        <h5 class="fw-bold text-dark mb-2">Saat ini Layanan Belum Di Aktifkan</h5>
        <p class="text-muted mb-0">Silakan aktifkan setidaknya satu layanan pada menu Pengaturan Wilayah.</p>
    </div>
@else
    <div class="row g-2 g-md-3 mb-4">
        <!-- Total Transaksi -->
        <div class="col-6 col-md-3 col-lg">
            <div class="card border-0 shadow-sm h-100 rounded-4 stat-card">
                <div class="card-body p-2 p-md-3 d-flex flex-column align-items-center justify-content-center text-center">
                    <div class="stat-icon bg-primary-subtle text-primary mb-1 mx-auto">
                        <i class="bx bx-receipt"></i>
                    </div>
                    <small class="text-muted text-uppercase fw-bold ls-1 mb-1 text-truncate w-100" style="font-size: 0.65rem;">Total Transaksi</small>
                    <div class="stat-number text-dark">
                        <span class="count-up" data-value="{{ $rentalRequests->count() + $gasOrders->count() + $mobilBookings->count() + $fasilitasBookings->count() + $pasarOrders->count() }}">0</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Selesai -->
        <div class="col-6 col-md-3 col-lg">
            <div class="card border-0 shadow-sm h-100 rounded-4 stat-card">
                <div class="card-body p-2 p-md-3 d-flex flex-column align-items-center justify-content-center text-center">
                    <div class="stat-icon bg-success-subtle text-success mb-1 mx-auto">
                        <i class="bx bx-check-double"></i>
                    </div>
                    <small class="text-muted text-uppercase fw-bold ls-1 mb-1 text-truncate w-100" style="font-size: 0.65rem;">Selesai</small>
                    <div class="stat-number text-dark">
                        <span class="count-up" data-value="{{ $rentalRequests->where('status', 'completed')->count() + $gasOrders->where('status', 'completed')->count() + $mobilBookings->where('status', 'completed')->count() + $fasilitasBookings->where('status', 'completed')->count() + $pasarOrders->where('status', 'completed')->count() }}">0</span>
                    </div>
                </div>
            </div>
        </div>

        @if($isRentalActive)
        <!-- Penyewaan Alat -->
        <div class="col-6 col-md-3 col-lg">
            <div class="card border-0 shadow-sm h-100 rounded-4 stat-card">
                <div class="card-body p-2 p-md-3 d-flex flex-column align-items-center justify-content-center text-center">
                    <div class="stat-icon bg-info-subtle mb-1 mx-auto d-flex align-items-center justify-content-center">
                        <img src="{{ asset('User/img/elemen/F1.png') }}" style="width: 24px; height: 24px; object-fit: contain;">
                    </div>
                    <small class="text-muted text-uppercase fw-bold ls-1 mb-1 text-truncate w-100" style="font-size: 0.65rem;">Penyewaan</small>
                    <div class="stat-number text-dark">
                        <span class="count-up" data-value="{{ $rentalRequests->count() }}">0</span>
                    </div>
                </div>
            </div>
        </div>
        @endif

        @if($isGasActive)
        <!-- Pembelian Gas -->
        <div class="col-6 col-md-3 col-lg">
            <div class="card border-0 shadow-sm h-100 rounded-4 stat-card">
                <div class="card-body p-2 p-md-3 d-flex flex-column align-items-center justify-content-center text-center">
                    <div class="stat-icon bg-success-subtle mb-1 mx-auto d-flex align-items-center justify-content-center">
                        <img src="{{ asset('User/img/elemen/F2.png') }}" style="width: 24px; height: 24px; object-fit: contain;">
                    </div>
                    <small class="text-muted text-uppercase fw-bold ls-1 mb-1 text-truncate w-100" style="font-size: 0.65rem;">Gas LPG</small>
                    <div class="stat-number text-dark">
                        <span class="count-up" data-value="{{ $gasOrders->count() }}">0</span>
                    </div>
                </div>
            </div>
        </div>
        @endif

        @if($isMobilActive)
        <!-- Sewa Mobil -->
        <div class="col-6 col-md-4 col-lg">
            <div class="card border-0 shadow-sm h-100 rounded-4 stat-card">
                <div class="card-body p-2 p-md-3 d-flex flex-column align-items-center justify-content-center text-center">
                    <div class="stat-icon bg-primary-subtle mb-1 mx-auto d-flex align-items-center justify-content-center">
                        <img src="{{ asset('User/img/elemen/mobil.png') }}" style="width: 24px; height: 24px; object-fit: contain;">
                    </div>
                    <small class="text-muted text-uppercase fw-bold ls-1 mb-1 text-truncate w-100" style="font-size: 0.65rem;">Sewa Mobil</small>
                    <div class="stat-number text-dark">
                        <span class="count-up" data-value="{{ $mobilBookings->count() }}">0</span>
                    </div>
                </div>
            </div>
        </div>
        @endif

        @if($isFasilitasActive)
        <!-- Fasilitas Umum -->
        <div class="col-6 col-md-4 col-lg">
            <div class="card border-0 shadow-sm h-100 rounded-4 stat-card">
                <div class="card-body p-2 p-md-3 d-flex flex-column align-items-center justify-content-center text-center">
                    <div class="stat-icon bg-warning-subtle mb-1 mx-auto d-flex align-items-center justify-content-center">
                        <img src="{{ asset('User/img/elemen/fasilitas.png') }}" style="width: 24px; height: 24px; object-fit: contain;">
                    </div>
                    <small class="text-muted text-uppercase fw-bold ls-1 mb-1 text-truncate w-100" style="font-size: 0.65rem;">Fasilitas Umum</small>
                    <div class="stat-number text-dark">
                        <span class="count-up" data-value="{{ $fasilitasBookings->count() }}">0</span>
                    </div>
                </div>
            </div>
        </div>
        @endif

        @if($isPasarActive)
        <!-- Pasar Daerah -->
        <div class="col-12 col-md-4 col-lg">
            <div class="card border-0 shadow-sm h-100 rounded-4 stat-card">
                <div class="card-body p-2 p-md-3 d-flex flex-column align-items-center justify-content-center text-center">
                    <div class="stat-icon bg-secondary-subtle mb-1 mx-auto d-flex align-items-center justify-content-center">
                        <img src="{{ asset('Admin/img/pasardaerah/PasarDaerah2.png') }}" style="width: 24px; height: 24px; object-fit: contain;">
                    </div>
                    <small class="text-muted text-uppercase fw-bold ls-1 mb-1 text-truncate w-100" style="font-size: 0.65rem;">Pasar Daerah</small>
                    <div class="stat-number text-dark">
                        <span class="count-up" data-value="{{ $pasarOrders->count() }}">0</span>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>

    <!-- Main Content Tabs -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="card-header bg-white border-bottom py-3 px-3 px-md-4">
             <div class="tabs-scroll-wrapper">
                 <ul class="nav nav-pills card-header-pills flex-nowrap flex-md-wrap gap-2 mb-0" id="reportTabs" role="tablist">
                    @if($isRentalActive)
                    <li class="nav-item flex-shrink-0" role="presentation">
                        <button class="nav-link {{ $totalActive > 0 ? 'active' : '' }} rounded-pill px-3 py-2 fw-semibold text-nowrap" id="rental-tab" data-bs-toggle="tab" data-bs-target="#rental-pane" type="button" role="tab">
                            <img src="{{ asset('User/img/elemen/F1.png') }}" class="me-2" style="width: 20px; height: 20px; object-fit: contain;">Penyewaan Alat
                            <span class="badge bg-white text-primary ms-2 shadow-sm">{{ $rentalRequests->count() }}</span>
                        </button>
                    </li>
                    @endif
                    @if($isGasActive)
                    <li class="nav-item flex-shrink-0" role="presentation">
                        <button class="nav-link {{ !$isRentalActive ? 'active' : '' }} rounded-pill px-3 py-2 fw-semibold text-nowrap" id="gas-tab" data-bs-toggle="tab" data-bs-target="#gas-pane" type="button" role="tab">
                            <img src="{{ asset('User/img/elemen/F2.png') }}" class="me-2" style="width: 20px; height: 20px; object-fit: contain;">Pembelian Gas
                            <span class="badge bg-white text-primary ms-2 shadow-sm">{{ $gasOrders->count() }}</span>
                        </button>
                    </li>
                    @endif
                    @if($isMobilActive)
                    <li class="nav-item flex-shrink-0" role="presentation">
                        <button class="nav-link {{ !$isRentalActive && !$isGasActive ? 'active' : '' }} rounded-pill px-3 py-2 fw-semibold text-nowrap" id="mobil-tab" data-bs-toggle="tab" data-bs-target="#mobil-pane" type="button" role="tab">
                            <img src="{{ asset('User/img/elemen/mobil.png') }}" class="me-2" style="width: 20px; height: 20px; object-fit: contain;">Sewa Mobil
                            <span class="badge bg-white text-primary ms-2 shadow-sm">{{ $mobilBookings->count() }}</span>
                        </button>
                    </li>
                    @endif
                    @if($isFasilitasActive)
                    <li class="nav-item flex-shrink-0" role="presentation">
                        <button class="nav-link {{ !$isRentalActive && !$isGasActive && !$isMobilActive ? 'active' : '' }} rounded-pill px-3 py-2 fw-semibold text-nowrap" id="fasilitas-tab" data-bs-toggle="tab" data-bs-target="#fasilitas-pane" type="button" role="tab">
                            <img src="{{ asset('User/img/elemen/fasilitas.png') }}" class="me-2" style="width: 20px; height: 20px; object-fit: contain;">Fasilitas Umum
                            <span class="badge bg-white text-primary ms-2 shadow-sm">{{ $fasilitasBookings->count() }}</span>
                        </button>
                    </li>
                    @endif
                    @if($isPasarActive)
                    <li class="nav-item flex-shrink-0" role="presentation">
                        <button class="nav-link {{ !$isRentalActive && !$isGasActive && !$isMobilActive && !$isFasilitasActive ? 'active' : '' }} rounded-pill px-3 py-2 fw-semibold text-nowrap" id="pasar-tab" data-bs-toggle="tab" data-bs-target="#pasar-pane" type="button" role="tab">
                            <img src="{{ asset('Admin/img/pasardaerah/PasarDaerah2.png') }}" class="me-2" style="width: 20px; height: 20px; object-fit: contain;">Pasar Daerah
                            <span class="badge bg-white text-primary ms-2 shadow-sm">{{ $pasarOrders->count() }}</span>
                        </button>
                    </li>
                    @endif
                </ul>
             </div>
        </div>
        
        <div class="card-body p-0">
             <div class="tab-content" id="reportTabsContent">
                
                <!-- RENTAL RESULTS -->
                @if($isRentalActive)
                <div class="tab-pane fade {{ $totalActive > 0 ? 'show active' : '' }}" id="rental-pane" role="tabpanel">
                    @if($rentalRequests->isEmpty())
                        <div class="text-center py-5">
                            <div class="mb-3"><i class="bx bx-bar-chart-alt-2 fs-1 text-muted opacity-25"></i></div>
                            <h6 class="text-muted fw-bold">Tidak ada data transaksi penyewaan</h6>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="ps-4 py-3 text-secondary text-uppercase small fw-bold">ID & Tanggal</th>
                                        <th class="py-3 text-secondary text-uppercase small fw-bold">Penyewa</th>
                                        <th class="py-3 text-secondary text-uppercase small fw-bold">Alat</th>
                                        <th class="py-3 text-secondary text-uppercase small fw-bold">Total</th>
                                        <th class="py-3 text-secondary text-uppercase small fw-bold">Status</th>
                                        <th class="text-end pe-4 py-3 text-secondary text-uppercase small fw-bold">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($rentalRequests as $req)
                                    <tr>
                                        <td class="ps-4">
                                            <div class="fw-bold text-dark">#{{ $req->order_number ?? $req->id }}</div>
                                            <small class="text-muted">{{ $req->created_at->format('d/m/Y H:i') }}</small>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar avatar-sm border rounded-circle p-1 me-2">
                                                    <span class="avatar-initial rounded-circle bg-primary-subtle text-primary fw-bold">
                                                        {{ strtoupper(substr($req->recipient_name ?? $req->user->name ?? 'U', 0, 1)) }}
                                                    </span>
                                                </div>
                                                <span class="fw-medium text-dark">{{ $req->recipient_name ?? $req->user->name }}</span>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="text-dark">{{ $req->item_name ?? 'Alat' }}</div>
                                            <small class="text-muted">{{ $req->quantity }} Unit</small>
                                        </td>
                                        <td>
                                            @php
                                                $showSensitive = auth()->user()->role === 'super_admin' || (auth()->user()->region_id && $req->user && $req->user->region_id == auth()->user()->region_id);
                                            @endphp
                                            @if($showSensitive)
                                                <span class="fw-bold text-primary">Rp {{ number_format($req->price ?? $req->total_amount, 0, ',', '.') }}</span>
                                            @else
                                                <span class="fw-bold text-muted fst-italic">Rp *** (Privasi)</span>
                                            @endif
                                        </td>
                                        <td>
                                            @include('admin.partials.status-badge', ['status' => $req->status])
                                        </td>
                                        <td class="text-end pe-4">
                                            <a href="{{ route('admin.aktivitas.permintaan-pengajuan.show', [$req->id, 'rental']) }}" class="btn btn-sm btn-light border shadow-sm rounded-pill px-3 text-primary">
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
                @endif

                <!-- GAS RESULTS -->
                @if($isGasActive)
                <div class="tab-pane fade {{ !$isRentalActive ? 'show active' : '' }}" id="gas-pane" role="tabpanel">
                    @if($gasOrders->isEmpty())
                        <div class="text-center py-5">
                            <div class="mb-3"><i class="bx bx-bar-chart-alt-2 fs-1 text-muted opacity-25"></i></div>
                            <h6 class="text-muted fw-bold">Tidak ada data transaksi gas</h6>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="ps-4 py-3 text-secondary text-uppercase small fw-bold">ID & Tanggal</th>
                                        <th class="py-3 text-secondary text-uppercase small fw-bold">Pembeli</th>
                                        <th class="py-3 text-secondary text-uppercase small fw-bold">Produk</th>
                                        <th class="py-3 text-secondary text-uppercase small fw-bold">Total</th>
                                        <th class="py-3 text-secondary text-uppercase small fw-bold">Status</th>
                                        <th class="text-end pe-4 py-3 text-secondary text-uppercase small fw-bold">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($gasOrders as $order)
                                    <tr>
                                        <td class="ps-4">
                                            <div class="fw-bold text-dark">#{{ $order->order_number ?? $order->id }}</div>
                                            <small class="text-muted">{{ $order->created_at->format('d/m/Y H:i') }}</small>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                 <div class="avatar avatar-sm border rounded-circle p-1 me-2">
                                                    <span class="avatar-initial rounded-circle bg-info-subtle text-info fw-bold">
                                                        {{ strtoupper(substr($order->full_name ?? $order->user->name ?? 'U', 0, 1)) }}
                                                    </span>
                                                </div>
                                                <span class="fw-medium text-dark">{{ $order->full_name ?? $order->user->name }}</span>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="text-dark">{{ $order->item_name ?? 'Gas LPG' }}</div>
                                            <small class="text-muted">{{ $order->quantity }} Tabung</small>
                                        </td>
                                        <td>
                                            @php
                                                $showSensitive = auth()->user()->role === 'super_admin' || (auth()->user()->region_id && $order->user && $order->user->region_id == auth()->user()->region_id);
                                            @endphp
                                            @if($showSensitive)
                                                <span class="fw-bold text-primary">Rp {{ number_format($order->price ?? $order->total_amount, 0, ',', '.') }}</span>
                                            @else
                                                <span class="fw-bold text-muted fst-italic">Rp *** (Privasi)</span>
                                            @endif
                                        </td>
                                        <td>
                                            @include('admin.partials.status-badge', ['status' => $order->status])
                                        </td>
                                        <td class="text-end pe-4">
                                            <a href="{{ route('admin.aktivitas.permintaan-pengajuan.show', [$order->id, 'gas']) }}" class="btn btn-sm btn-light border shadow-sm rounded-pill px-3 text-primary">
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
                @endif
                <!-- MOBIL RESULTS -->
                @if($isMobilActive)
                <div class="tab-pane fade {{ !$isRentalActive && !$isGasActive ? 'show active' : '' }}" id="mobil-pane" role="tabpanel">
                    @if($mobilBookings->isEmpty())
                        <div class="text-center py-5">
                            <div class="mb-3"><i class="bx bx-bar-chart-alt-2 fs-1 text-muted opacity-25"></i></div>
                            <h6 class="text-muted fw-bold">Tidak ada data transaksi sewa mobil</h6>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="ps-4 py-3 text-secondary text-uppercase small fw-bold">ID & Tanggal</th>
                                        <th class="py-3 text-secondary text-uppercase small fw-bold">Penyewa</th>
                                        <th class="py-3 text-secondary text-uppercase small fw-bold">Mobil</th>
                                        <th class="py-3 text-secondary text-uppercase small fw-bold">Total</th>
                                        <th class="py-3 text-secondary text-uppercase small fw-bold">Status</th>
                                        <th class="text-end pe-4 py-3 text-secondary text-uppercase small fw-bold">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($mobilBookings as $order)
                                    <tr>
                                        <td class="ps-4">
                                            <div class="fw-bold text-dark">#{{ $order->order_number ?? $order->id }}</div>
                                            <small class="text-muted">{{ $order->created_at->format('d/m/Y H:i') }}</small>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                 <div class="avatar avatar-sm border rounded-circle p-1 me-2">
                                                    <span class="avatar-initial rounded-circle bg-danger-subtle text-danger fw-bold">
                                                        {{ strtoupper(substr($order->recipient_name ?? $order->user->name ?? 'U', 0, 1)) }}
                                                    </span>
                                                </div>
                                                <span class="fw-medium text-dark">{{ $order->recipient_name ?? $order->user->name }}</span>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="text-dark">{{ $order->mobil->nama_mobil ?? 'Sewa Mobil' }}</div>
                                            <small class="text-muted">{{ $order->lama_sewa }} Hari</small>
                                        </td>
                                        <td>
                                            @php
                                                $showSensitive = auth()->user()->role === 'super_admin' || (auth()->user()->region_id && $order->user && $order->user->region_id == auth()->user()->region_id);
                                            @endphp
                                            @if($showSensitive)
                                                <span class="fw-bold text-primary">Rp {{ number_format($order->total_amount ?? 0, 0, ',', '.') }}</span>
                                            @else
                                                <span class="fw-bold text-muted fst-italic">Rp *** (Privasi)</span>
                                            @endif
                                        </td>
                                        <td>
                                            @include('admin.partials.status-badge', ['status' => $order->status])
                                        </td>
                                        <td class="text-end pe-4">
                                            <a href="{{ route('admin.aktivitas.permintaan-pengajuan.show', [$order->id, 'mobil']) }}" class="btn btn-sm btn-light border shadow-sm rounded-pill px-3 text-primary">
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
                @endif

                <!-- FASILITAS RESULTS -->
                @if($isFasilitasActive)
                <div class="tab-pane fade {{ !$isRentalActive && !$isGasActive && !$isMobilActive ? 'show active' : '' }}" id="fasilitas-pane" role="tabpanel">
                    @if($fasilitasBookings->isEmpty())
                        <div class="text-center py-5">
                            <div class="mb-3"><i class="bx bx-bar-chart-alt-2 fs-1 text-muted opacity-25"></i></div>
                            <h6 class="text-muted fw-bold">Tidak ada data transaksi fasilitas umum</h6>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="ps-4 py-3 text-secondary text-uppercase small fw-bold">ID & Tanggal</th>
                                        <th class="py-3 text-secondary text-uppercase small fw-bold">Peminjam</th>
                                        <th class="py-3 text-secondary text-uppercase small fw-bold">Fasilitas</th>
                                        <th class="py-3 text-secondary text-uppercase small fw-bold">Status</th>
                                        <th class="text-end pe-4 py-3 text-secondary text-uppercase small fw-bold">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($fasilitasBookings as $order)
                                    <tr>
                                        <td class="ps-4">
                                            <div class="fw-bold text-dark">#{{ $order->order_number ?? $order->id }}</div>
                                            <small class="text-muted">{{ $order->created_at->format('d/m/Y H:i') }}</small>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                 <div class="avatar avatar-sm border rounded-circle p-1 me-2">
                                                    <span class="avatar-initial rounded-circle bg-secondary-subtle text-secondary fw-bold">
                                                        {{ strtoupper(substr($order->recipient_name ?? $order->user->name ?? 'U', 0, 1)) }}
                                                    </span>
                                                </div>
                                                <span class="fw-medium text-dark">{{ $order->recipient_name ?? $order->user->name }}</span>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="text-dark">{{ $order->fasilitas->nama_fasilitas ?? 'Fasilitas Umum' }}</div>
                                            <small class="text-muted">{{ $order->lama_sewa }} Hari</small>
                                        </td>
                                        <td>
                                            @include('admin.partials.status-badge', ['status' => $order->status])
                                        </td>
                                        <td class="text-end pe-4">
                                            <a href="{{ route('admin.aktivitas.permintaan-pengajuan.show', [$order->id, 'fasilitas']) }}" class="btn btn-sm btn-light border shadow-sm rounded-pill px-3 text-primary">
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
                @endif

                <!-- PASAR DAERAH RESULTS -->
                @if($isPasarActive)
                <div class="tab-pane fade {{ !$isRentalActive && !$isGasActive && !$isMobilActive && !$isFasilitasActive ? 'show active' : '' }}" id="pasar-pane" role="tabpanel">
                    @if($pasarOrders->isEmpty())
                        <div class="text-center py-5">
                            <div class="mb-3"><i class="bx bx-store-alt fs-1 text-muted opacity-25"></i></div>
                            <h6 class="text-muted fw-bold">Tidak ada data transaksi pasar daerah</h6>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="ps-4 py-3 text-secondary text-uppercase small fw-bold">ID & Tanggal</th>
                                        <th class="py-3 text-secondary text-uppercase small fw-bold">Pelanggan</th>
                                        <th class="py-3 text-secondary text-uppercase small fw-bold">Total Pembayaran</th>
                                        <th class="py-3 text-secondary text-uppercase small fw-bold">Status</th>
                                        <th class="text-end pe-4 py-3 text-secondary text-uppercase small fw-bold">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($pasarOrders as $order)
                                    <tr>
                                        <td class="ps-4">
                                            <div class="fw-bold text-dark">#{{ $order->order_number ?? $order->id }}</div>
                                            <small class="text-muted">{{ $order->created_at->format('d/m/Y H:i') }}</small>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                 <div class="avatar avatar-sm border rounded-circle p-1 me-2">
                                                    <span class="avatar-initial rounded-circle bg-secondary-subtle text-secondary fw-bold">
                                                        {{ strtoupper(substr($order->user->name ?? 'U', 0, 1)) }}
                                                    </span>
                                                </div>
                                                <span class="fw-medium text-dark">{{ $order->user->name ?? 'Anonim' }}</span>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="text-primary fw-bold">Rp {{ number_format($order->grand_total ?? 0, 0, ',', '.') }}</div>
                                            <small class="text-muted">Metode: {{ strtoupper($order->payment_method ?? 'COD') }}</small>
                                        </td>
                                        <td>
                                            @include('admin.partials.status-badge', ['status' => $order->status])
                                        </td>
                                        <td class="text-end pe-4">
                                            <a href="{{ route('admin.unit.pasar_daerah.pesanan.show', $order->id) }}" class="btn btn-sm btn-light border shadow-sm rounded-pill px-3 text-primary">
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
                @endif

            </div>
        </div>
    </div>
    @endif
