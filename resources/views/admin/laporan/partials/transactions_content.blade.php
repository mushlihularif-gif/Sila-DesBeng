    <div class="row g-3 mb-4">
        <div class="col-md-4 col-6">
            <div class="card border-0 shadow-sm h-100 rounded-4 overflow-hidden position-relative">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center mb-2">
                        <div class="avatar avatar-md bg-primary-subtle text-primary rounded-3 p-2 me-3">
                            <i class="bx bx-receipt fs-3"></i>
                        </div>
                        <div>
                            <small class="text-muted text-uppercase fw-bold ls-1" style="font-size: 0.7rem;">Total Transaksi</small>
                            <h4 class="fw-bold mb-0 text-dark"><span class="count-up" data-value="{{ $rentalRequests->count() + $gasOrders->count() + $mobilBookings->count() + $fasilitasBookings->count() }}">0</span></h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-6">
            <div class="card border-0 shadow-sm h-100 rounded-4 overflow-hidden position-relative">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center mb-2">
                        <div class="avatar avatar-md bg-success-subtle text-success rounded-3 p-2 me-3">
                            <i class="bx bx-check-double fs-3"></i>
                        </div>
                        <div>
                            <small class="text-muted text-uppercase fw-bold ls-1" style="font-size: 0.7rem;">Selesai</small>
                            <h4 class="fw-bold mb-0 text-dark"><span class="count-up" data-value="{{ $rentalRequests->where('status', 'completed')->count() + $gasOrders->where('status', 'completed')->count() + $mobilBookings->where('status', 'completed')->count() + $fasilitasBookings->where('status', 'completed')->count() }}">0</span></h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-6">
            <div class="card border-0 shadow-sm h-100 rounded-4 overflow-hidden position-relative">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center mb-2">
                         <div class="avatar avatar-md bg-info-subtle text-info rounded-3 p-2 me-3">
                            <i class="bx bx-wrench fs-3"></i>
                        </div>
                        <div>
                            <small class="text-muted text-uppercase fw-bold ls-1" style="font-size: 0.7rem;">Penyewaan</small>
                            <h4 class="fw-bold mb-0 text-dark"><span class="count-up" data-value="{{ $rentalRequests->count() }}">0</span></h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-6">
             <div class="card border-0 shadow-sm h-100 rounded-4 overflow-hidden position-relative">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center mb-2">
                         <div class="avatar avatar-md bg-warning-subtle text-warning rounded-3 p-2 me-3">
                            <i class="bx bxs-gas-pump fs-3"></i>
                        </div>
                        <div>
                            <small class="text-muted text-uppercase fw-bold ls-1" style="font-size: 0.7rem;">Gas</small>
                            <h4 class="fw-bold mb-0 text-dark"><span class="count-up" data-value="{{ $gasOrders->count() }}">0</span></h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-6">
             <div class="card border-0 shadow-sm h-100 rounded-4 overflow-hidden position-relative">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center mb-2">
                         <div class="avatar avatar-md bg-danger-subtle text-danger rounded-3 p-2 me-3">
                            <i class="bx bx-car fs-3"></i>
                        </div>
                        <div>
                            <small class="text-muted text-uppercase fw-bold ls-1" style="font-size: 0.7rem;">Sewa Mobil</small>
                            <h4 class="fw-bold mb-0 text-dark"><span class="count-up" data-value="{{ $mobilBookings->count() }}">0</span></h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-6">
             <div class="card border-0 shadow-sm h-100 rounded-4 overflow-hidden position-relative">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center mb-2">
                         <div class="avatar avatar-md bg-secondary-subtle text-secondary rounded-3 p-2 me-3">
                            <i class="bx bx-building fs-3"></i>
                        </div>
                        <div>
                            <small class="text-muted text-uppercase fw-bold ls-1" style="font-size: 0.7rem;">Fasilitas Umum</small>
                            <h4 class="fw-bold mb-0 text-dark"><span class="count-up" data-value="{{ $fasilitasBookings->count() }}">0</span></h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Tabs -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="card-header bg-white border-bottom py-3 px-4">
             <ul class="nav nav-pills card-header-pills gap-2" id="reportTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active rounded-pill px-4 fw-semibold" id="rental-tab" data-bs-toggle="tab" data-bs-target="#rental-pane" type="button" role="tab">
                        <i class="bx bx-wrench me-2"></i>Penyewaan Alat
                        <span class="badge bg-white text-primary ms-2 shadow-sm">{{ $rentalRequests->count() }}</span>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link rounded-pill px-4 fw-semibold" id="gas-tab" data-bs-toggle="tab" data-bs-target="#gas-pane" type="button" role="tab">
                        <i class="bx bxs-gas-pump me-2"></i>Pembelian Gas
                        <span class="badge bg-white text-primary ms-2 shadow-sm">{{ $gasOrders->count() }}</span>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link rounded-pill px-4 fw-semibold" id="mobil-tab" data-bs-toggle="tab" data-bs-target="#mobil-pane" type="button" role="tab">
                        <i class="bx bx-car me-2"></i>Sewa Mobil
                        <span class="badge bg-white text-primary ms-2 shadow-sm">{{ $mobilBookings->count() }}</span>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link rounded-pill px-4 fw-semibold" id="fasilitas-tab" data-bs-toggle="tab" data-bs-target="#fasilitas-pane" type="button" role="tab">
                        <i class="bx bx-building me-2"></i>Fasilitas Umum
                        <span class="badge bg-white text-primary ms-2 shadow-sm">{{ $fasilitasBookings->count() }}</span>
                    </button>
                </li>
            </ul>
        </div>
        
        <div class="card-body p-0">
             <div class="tab-content" id="reportTabsContent">
                
                <!-- RENTAL RESULTS -->
                <div class="tab-pane fade show active" id="rental-pane" role="tabpanel">
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

                <!-- GAS RESULTS -->
                <div class="tab-pane fade" id="gas-pane" role="tabpanel">
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
                <!-- MOBIL RESULTS -->
                <div class="tab-pane fade" id="mobil-pane" role="tabpanel">
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

                <!-- FASILITAS RESULTS -->
                <div class="tab-pane fade" id="fasilitas-pane" role="tabpanel">
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

            </div>
        </div>
    </div>
</div>
