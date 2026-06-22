@extends('admin.layouts.admin')
@section('content')
    <div class="content-wrapper">
        <div class="container-xxl flex-grow-1 container-p-y">

            <!-- Kartu Selamat Datang & Grafik Kinerja -->
            <div class="row mb-2 align-items-stretch">
                <div class="col-lg-5 mb-4 mb-lg-0">
                    <div class="card h-100">
                        <div class="d-flex flex-column h-100">
                            <div class="col-12">
                                <div class="card-body p-4">
                                    <h5 class="card-title text-primary fw-bold">Selamat Datang di SiladesBeng 🏛️</h5>
                                    <p class="mb-3 text-muted">Sistem Pelayanan Terpadu berbasis Digital <span
                                            class="fw-bold text-dark">Pemerintahan Desa Pematang Duku Timur</span></p>
                                    <a href="{{ route('admin.isewa.profile-bumdes') }}"
                                        class="btn btn-outline-primary">Profil Pemerintah Desa</a>
                                </div>
                            </div>
                            <div class="col-12 mt-auto">
                                <div class="px-3 pb-3">
                                    <div id="dashboardBannerCarousel" class="carousel slide" data-bs-ride="carousel">
                                        <div class="carousel-inner rounded-3 shadow-sm">
                                            @php
                                                $banners = \App\Models\Banner::where('is_active', true)->latest()->get();
                                            @endphp
                                            @if($banners->count() > 0)
                                                @foreach($banners as $index => $banner)
                                                    <div class="carousel-item {{ $index == 0 ? 'active' : '' }}">
                                                        <img src="{{ Storage::url($banner->image_path) }}" class="d-block w-100 rounded-3" style="object-fit: cover;" alt="Banner {{ $index + 1 }}">
                                                    </div>
                                                @endforeach
                                            @else
                                                <div class="carousel-item active">
                                                    <img src="{{ asset('User/img/elemen/entrance.png') }}" class="d-block w-100 rounded-3" style="object-fit: cover;" alt="Slide 1">
                                                </div>
                                                <div class="carousel-item">
                                                    <img src="{{ asset('User/img/elemen/biru.png') }}" class="d-block w-100 rounded-3" style="object-fit: cover;" alt="Slide 2">
                                                </div>
                                                <div class="carousel-item">
                                                    <img src="{{ asset('User/img/elemen/ppq.png') }}" class="d-block w-100 rounded-3" style="object-fit: cover;" alt="Slide 3">
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-7 mb-4 mb-lg-0">
                    <div class="card h-100">
                        <div class="card-body d-flex flex-column">
                            <div
                                class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center mb-4">
                                <div>
                                    <h5 class="card-title fw-bold mb-2">Kinerja Pemerintah Desa</h5>
                                    <span class="badge bg-label-warning rounded-pill">Tahun {{ $selectedYear }}</span>
                                </div>
                                <div class="d-flex flex-column flex-sm-row gap-2 mt-3 mt-sm-0">
                                    <select class="form-select form-select-sm" id="desaSelect" style="min-width: 200px;">
                                        <option selected>Desa Pematang Duku Timur</option>
                                    </select>
                                    <select class="form-select form-select-sm" id="tahunSelect" style="min-width: 100px;">
                                        @foreach($availableYears as $year)
                                            <option value="{{ $year }}" {{ $year == $selectedYear ? 'selected' : '' }}>{{ $year }}</option>
                                        @endforeach
                                    </select>
                                    <script>
                                        document.getElementById('tahunSelect').addEventListener('change', function() {
                                            window.location.href = "{{ route('admin.dashboard') }}?year=" + this.value;
                                        });
                                    </script>
                                </div>
                            </div>
                            <div class="flex-grow-1 d-flex flex-column justify-content-center">
                                <div id="kinerjaChart" style="min-height: 300px; width: 100%;"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="mb-3"></div>

            <!-- Kartu Unit - Lebar Penuh -->
            <div class="row mb-4">
                @php
                    $laporanPendingCount = 0;
                    if(class_exists('\App\Models\Laporan')) {
                        $laporanPendingCount = \App\Models\Laporan::where('status', 'Pending')->count() ?? 0;
                    }
                    $unitConfigs = [
                        'Penyewaan Alat' => [
                            'title' => 'Unit Penyewaan Alat',
                            'count' => ($unitPenyewaan ?? \App\Models\Barang::count() ?? 0) . ' Item',
                            'route' => route('admin.unit.penyewaan.index'),
                            'image' => asset('User/img/elemen/F1.png'),
                            'color' => 'warning'
                        ],
                        'Penjualan Gas' => [
                            'title' => 'Unit Penjualan Gas',
                            'count' => ($unitGas ?? \App\Models\Gas::count() ?? 0) . ' Jenis Tabung',
                            'route' => route('admin.unit.penjualan_gas.index'),
                            'image' => asset('User/img/elemen/F2.png'),
                            'color' => 'danger'
                        ],
                        'Penyewaan Mobil' => [
                            'title' => 'Unit Penyewaan Mobil',
                            'count' => (\App\Models\Mobil::count() ?? 0) . ' Kendaraan',
                            'route' => route('admin.unit.mobil.index'),
                            'image' => asset('User/img/elemen/mobil.png'),
                            'color' => 'info'
                        ],
                        'Peminjaman Fasilitas Umum' => [
                            'title' => 'Unit Peminjaman Fasilitas Umum',
                            'count' => (\App\Models\FasilitasUmum::count() ?? 0) . ' Fasilitas',
                            'route' => route('admin.unit.fasilitas_umum.index'),
                            'image' => asset('User/img/elemen/fasilitas.png'),
                            'color' => 'success'
                        ],
                        'Pelaporan Warga' => [
                            'title' => 'Pelaporan Warga',
                            'count' => $laporanPendingCount . ' Pending',
                            'route' => route('lurah.laporan.index'),
                            'image' => asset('User/img/elemen/lapor.png'),
                            'color' => 'primary'
                        ],
                        'Pengumuman dan Event' => [
                            'title' => 'Pengumuman & Event',
                            'count' => (\App\Models\Announcement::count() ?? 0) . ' Info',
                            'route' => route('admin.announcements.index'),
                            'image' => asset('User/img/elemen/event.png'),
                            'color' => 'secondary'
                        ]
                    ];

                    $activeServicesList = isset($activeServices) && count($activeServices) > 0 ? $activeServices : ['Penyewaan Alat', 'Penjualan Gas'];
                @endphp

                @foreach($activeServicesList as $serviceName)
                    @if(isset($unitConfigs[$serviceName]))
                        @php $config = $unitConfigs[$serviceName]; @endphp
                        <div class="col-md-6 mb-3">
                            <div class="card unit-card {{ $config['color'] }} hover-lift"
                                onclick="window.location='{{ $config['route'] }}'">
                                <div class="card-body p-4">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar flex-shrink-0 me-3" style="width: 70px; height: 70px;">
                                            <img src="{{ $config['image'] }}" alt="{{ $config['title'] }}" class="rounded w-100" />
                                        </div>
                                        <div class="flex-grow-1">
                                            <span class="fw-semibold d-block mb-2 text-muted">{{ $config['title'] }}</span>
                                            <h3 class="card-title mb-0">{{ $config['count'] }}</h3>
                                        </div>
                                        <i class="bx bx-chevron-right bx-lg text-{{ $config['color'] }}"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>

                <!-- Bagian Notifikasi -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card shadow-sm border-0 rounded-4">
                            <div class="card-header bg-white py-3 border-bottom px-4">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h5 class="mb-1 fw-bold d-flex align-items-center text-primary">
                                            <span class="badge badge-center rounded-pill bg-primary-subtle text-primary me-2"
                                                style="width: 32px; height: 32px;">
                                                <i class="bx bx-bell fs-5"></i>
                                            </span>
                                            Notifikasi Permintaan
                                        </h5>
                                    </div>
                                    <a href="{{ route('admin.aktivitas.permintaan-pengajuan.index') }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                        Lihat Semua <i class="bx bx-right-arrow-alt ms-1"></i>
                                    </a>
                                </div>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0">
                                        <thead class="bg-light">
                                            <tr>
                                                <th class="ps-4 py-3 text-secondary text-uppercase small fw-bold">Pengguna</th>
                                                <th class="py-3 text-secondary text-uppercase small fw-bold">Kategori</th>
                                                <th class="py-3 text-secondary text-uppercase small fw-bold">Detail</th>
                                                <th class="py-3 text-secondary text-uppercase small fw-bold">Tanggal</th>
                                                <th class="py-3 text-secondary text-uppercase small fw-bold">Status</th>
                                                <th class="text-end pe-4 py-3 text-secondary text-uppercase small fw-bold">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($latestRequests as $request)
                                                <tr class="notification-item">
                                                    <td class="ps-4">
                                                        <div class="d-flex align-items-center">
                                                            <div class="avatar avatar-sm border rounded-circle p-1 me-3">
                                                                @if($request->user->avatar)
                                                                    <img src="{{ asset('storage/' . $request->user->avatar) }}" class="rounded-circle">
                                                                @else
                                                                    <span class="avatar-initial rounded-circle bg-label-primary fw-bold">
                                                                        {{ strtoupper(substr($request->user->name, 0, 1)) }}
                                                                    </span>
                                                                @endif
                                                            </div>
                                                            <div>
                                                                <h6 class="mb-0 fw-semibold text-dark">
                                                                    @if($request->type == 'rental')
                                                                        {{ $request->recipient_name ?? $request->user->name }}
                                                                    @else
                                                                        {{ $request->full_name ?? $request->user->name }}
                                                                    @endif
                                                                </h6>
                                                                <small class="text-muted" style="font-size: 0.75rem;">{{ $request->user->email }}</small>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        @if($request->type == 'rental')
                                                            <span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill px-3">
                                                                <i class="bx bx-wrench me-1"></i>Penyewaan
                                                            </span>
                                                        @else
                                                            <span class="badge bg-info-subtle text-info border border-info-subtle rounded-pill px-3">
                                                                <i class="bx bxs-gas-pump me-1"></i>Gas
                                                            </span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <div class="fw-medium text-dark">{{ $request->item_name }}</div>
                                                        <small class="text-muted">
                                                            @if($request->type == 'rental')
                                                                {{ $request->quantity }} Unit • {{ $request->days_count }} Hari
                                                            @else
                                                                {{ $request->quantity }} Tabung
                                                            @endif
                                                        </small>
                                                    </td>
                                                    <td>
                                                        <div class="d-flex flex-column">
                                                            <span class="fw-medium text-dark">{{ $request->created_at->format('d M') }}</span>
                                                            <small class="text-muted">{{ $request->created_at->format('H:i') }} WIB</small>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        @include('admin.partials.status-badge', ['status' => $request->status, 'cancelStatus' => $request->cancellation_status])
                                                    </td>
                                                    <td class="text-end pe-4">
                                                        <div class="d-flex gap-2 justify-content-end">
                                                            <a href="{{ route('admin.aktivitas.permintaan-pengajuan.show', [$request->id, $request->type]) }}" 
                                                               class="btn btn-sm btn-icon btn-outline-primary" 
                                                               data-bs-toggle="tooltip" title="Lihat Detail">
                                                                <i class="bx bx-show"></i>
                                                            </a>
                                                            
                                                            @if($request->cancellation_status == 'pending')
                                                                {{-- Tombol Aksi untuk Pembatalan --}}
                                                                <button type="button" class="btn btn-sm btn-icon btn-outline-success" 
                                                                        onclick="handleCancellation({{ $request->id }}, '{{ $request->type }}', 'approve')"
                                                                        data-bs-toggle="tooltip" title="Setujui Pembatalan">
                                                                    <i class="bx bx-check-double"></i>
                                                                </button>
                                                                <button type="button" class="btn btn-sm btn-icon btn-outline-danger" 
                                                                        onclick="handleCancellation({{ $request->id }}, '{{ $request->type }}', 'reject')"
                                                                        data-bs-toggle="tooltip" title="Tolak Pembatalan">
                                                                    <i class="bx bx-x-circle"></i>
                                                                </button>
                                                            @elseif($request->status == 'pending')
                                                                {{-- Tombol Aksi untuk Pesanan Baru --}}
                                                                <button type="button" class="btn btn-sm btn-icon btn-outline-success" 
                                                                        onclick="approveRequest({{ $request->id }}, '{{ $request->type }}')"
                                                                        data-bs-toggle="tooltip" title="Setujui">
                                                                    <i class="bx bx-check"></i>
                                                                </button>
                                                                <button type="button" class="btn btn-sm btn-icon btn-outline-danger" 
                                                                        onclick="rejectRequest({{ $request->id }}, '{{ $request->type }}')"
                                                                        data-bs-toggle="tooltip" title="Tolak">
                                                                    <i class="bx bx-x"></i>
                                                                </button>
                                                            @endif
                                                        </div>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="6" class="text-center py-5">
                                                        <div class="mb-3"><i class="bx bx-bell-off fs-1 text-muted opacity-25"></i></div>
                                                        <h6 class="text-muted fw-bold">Tidak ada notifikasi baru</h6>
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modal Tolak -->
                <div class="modal fade" id="rejectModal" tabindex="-1">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content rounded-4 border-0">
                            <div class="modal-header border-bottom-0 pb-0">
                                <h5 class="modal-title fw-bold">Tolak Permintaan</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <form id="rejectForm" method="POST">
                                @csrf
                                <div class="modal-body">
                                    <div class="mb-3">
                                        <label class="form-label text-muted">Alasan Penolakan <span class="text-danger">*</span></label>
                                        <textarea name="reason" class="form-control bg-light border-0 py-3" rows="4" placeholder="Jelaskan alasan penolakan permintaan..." required></textarea>
                                    </div>
                                </div>
                                <div class="modal-footer border-top-0 pt-0">
                                    <button type="button" class="btn btn-link text-secondary text-decoration-none" data-bs-dismiss="modal">Batal</button>
                                    <button type="submit" class="btn btn-danger rounded-pill px-4">
                                        Tolak Permintaan
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <script>
                    function handleCancellation(id, type, action) {
                        let title, text, confirmBtn, icon;
                        
                        if (action === 'approve') {
                            title = 'Setujui Pembatalan?';
                            text = "Pesanan akan dibatalkan sesuai permintaan pengguna.";
                            confirmBtn = 'Ya, Setujui Pembatalan';
                            icon = 'warning';
                        } else {
                            // Untuk penolakan pembatalan, kita butuh input alasan
                            // Gunakan SweetAlert dengan input
                            Swal.fire({
                                title: 'Tolak Pembatalan',
                                input: 'textarea',
                                inputLabel: 'Alasan Penolakan',
                                inputPlaceholder: 'Jelaskan kenapa pembatalan ditolak...',
                                inputAttributes: {
                                    'aria-label': 'Jelaskan kenapa pembatalan ditolak'
                                },
                                showCancelButton: true,
                                confirmButtonText: 'Tolak Pembatalan',
                                cancelButtonText: 'Batal',
                                inputValidator: (value) => {
                                    if (!value) {
                                        return 'Anda harus menuliskan alasan penolakan!'
                                    }
                                }
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    submitCancellationResponse(id, type, action, result.value);
                                }
                            });
                            return; // Hentikan eksekusi di sini, lanjut di submitCancellationResponse
                        }

                        Swal.fire({
                            title: title,
                            text: text,
                            icon: icon,
                            showCancelButton: true,
                            confirmButtonColor: action === 'approve' ? '#198754' : '#dc3545',
                            cancelButtonColor: '#6c757d',
                            confirmButtonText: confirmBtn,
                            cancelButtonText: 'Batal'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                submitCancellationResponse(id, type, action, null);
                            }
                        });
                    }

                    function submitCancellationResponse(id, type, action, reason) {
                        Swal.fire({ title: 'Memproses...', didOpen: () => Swal.showLoading() });
                        
                        let url = `{{ url('admin/aktivitas/permintaan-pengajuan') }}/${type}/${id}/cancellation/${action}`;
                        let body = { 
                            _token: '{{ csrf_token() }}' 
                        };
                        
                        if (reason) {
                            body.admin_response = reason;
                        }

                        fetch(url, {
                            method: 'POST',
                            headers: { 
                                'Content-Type': 'application/json',
                                'Accept': 'application/json' 
                            },
                            body: JSON.stringify(body)
                        })
                        .then(res => res.json())
                        .then(data => {
                            if(data.success) {
                                Swal.fire('Berhasil', data.message, 'success').then(() => location.reload());
                            } else {
                                Swal.fire('Gagal', data.message, 'error');
                            }
                        })
                        .catch(err => Swal.fire('Error', 'Terjadi kesalahan sistem', 'error'));
                    }

                    function approveRequest(id, type) {
                        Swal.fire({
                            title: 'Setujui Pesanan?',
                            text: "Pastikan stok tersedia. Pesanan akan diproses.",
                            icon: 'question',
                            showCancelButton: true,
                            confirmButtonColor: '#198754',
                            cancelButtonColor: '#6c757d',
                            confirmButtonText: 'Ya, Lanjutkan',
                            cancelButtonText: 'Batal'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                // Show loader
                                Swal.fire({ title: 'Memproses...', didOpen: () => Swal.showLoading() });
                                
                                fetch(`{{ url('admin/aktivitas/permintaan-pengajuan') }}/${id}/${type}/approve`, {
                                    method: 'POST',
                                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
                                })
                                .then(res => res.json())
                                .then(data => {
                                    if(data.success) {
                                        Swal.fire('Berhasil', data.message, 'success').then(() => location.reload());
                                    } else {
                                        Swal.fire('Gagal', data.message, 'error');
                                    }
                                })
                                .catch(err => Swal.fire('Error', 'Terjadi kesalahan sistem', 'error'));
                            }
                        });
                    }

                    function rejectRequest(id, type) {
                        const modalEl = document.getElementById('rejectModal');
                        if(modalEl) {
                            const modal = new bootstrap.Modal(modalEl);
                            document.getElementById('rejectForm').action = `{{ url('admin/aktivitas/permintaan-pengajuan') }}/${id}/${type}/reject`;
                            modal.show();
                        } else {
                            console.error('Reject Modal not found');
                        }
                    }
                </script>

                <!-- Tambahkan Animate.css untuk animasi halus -->
                <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>

                <!-- Tata Letak Tiga Kolom untuk Statistik Keuangan -->
                <div class="row mb-4">
                    <!-- Left Column: Total Pendapatan Unit Pelayanan Usaha -->
                    <div class="col-lg-8 mb-4 mb-lg-0">
                        <div class="card h-100">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="card-title mb-0 text-dark">
                                    Total Pendapatan Unit Pelayanan Usaha
                                </h5>
                                <select id="pendapatan-month" class="form-select form-select-sm" style="width: auto;">
                                    @foreach(['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'] as $index => $month)
                                        <option value="{{ $index + 1 }}" {{ ($index + 1) == ($totalPendapatanData['month'] ?? date('m')) ? 'selected' : '' }}>
                                            {{ $month }} {{ $totalPendapatanData['year'] ?? date('Y') }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="card-body">
                                <div class="row h-100 align-items-center">
                                    <!-- Data List -->
                                    <div class="col-md-7">
                                        @php
                                            $revenueServices = ['Penyewaan Alat', 'Penjualan Gas', 'Penyewaan Mobil'];
                                            $activeRevenueServices = array_intersect($activeServicesList, $revenueServices);
                                        @endphp
                                        @foreach($activeRevenueServices as $serviceName)
                                            @php
                                                $dataItem = $totalPendapatanData[$serviceName] ?? ['revenue' => 0, 'transactions' => 0, 'percentage' => 0, 'color' => 'secondary'];
                                            @endphp
                                            <div class="mb-4">
                                                <div class="d-flex justify-content-between mb-1">
                                                    <span class="fw-medium">Unit {{ $serviceName }}</span>
                                                    <span class="fw-bold">Rp {{ number_format($dataItem['revenue'], 0, ',', '.') }}</span>
                                                </div>
                                                <div class="progress" style="height: 8px;">
                                                    <div class="progress-bar bg-{{ $dataItem['color'] }}" role="progressbar" style="width: {{ $dataItem['percentage'] }}%"></div>
                                                </div>
                                                <small class="text-muted">{{ $dataItem['transactions'] }} Transaksi</small>
                                            </div>
                                        @endforeach

                                        <!-- Total -->
                                        <div class="pt-3 border-top">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <h6 class="mb-0">Total Keseluruhan</h6>
                                                <h6 class="mb-0 fw-bold">Rp {{ number_format($totalPendapatanData['total']['revenue'] ?? 0, 0, ',', '.') }}</h6>
                                            </div>
                                            <small class="text-muted">{{ $totalPendapatanData['total']['transactions'] ?? 0 }} Transaksi</small>
                                        </div>
                                    </div>

                                    <!-- Pie Chart -->
                                    <div class="col-md-5 d-flex align-items-center justify-content-center">
                                        <div id="pendapatanPieChart" style="width: 100%;"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column: Grafik Transaksi -->
                    <div class="col-lg-4">
                        <div class="card h-100">
                            <div class="card-header pb-0">
                                <h5 class="card-title mb-0">Perbandingan Transaksi</h5>
                            </div>
                            <div class="card-body d-flex flex-column justify-content-center align-items-center"
                                style="min-height: 280px; padding: 1rem;">
                                <div id="transactionDonutChart" style="width: 100%;"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Produk Populer -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card shadow-sm">
                            <div class="card-header bg-white py-3 border-bottom">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h5 class="mb-1 fw-semibold d-flex align-items-center">
                                            <span class="badge badge-center rounded-pill bg-label-warning me-2"
                                                style="width: 32px; height: 32px;">
                                                <i class="bx bx-star fs-5"></i>
                                            </span>
                                            Populer
                                        </h5>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body p-4">
                                <div class="row g-4">

                                    <!-- Product 2 - Sound System -->
                                    @forelse($popularProducts as $item)
                                    <div class="col-lg-3 col-md-6">
                                        <div class="card product-card h-100 border shadow-sm" onclick="window.location.href='{{ $item->link }}'" style="cursor: pointer;">
                                            <div class="card-body p-0">
                                                <!-- Product Image -->
                                                <div class="product-img-wrapper position-relative overflow-hidden"
                                                    style="height: 200px;">
                                                    <img src="{{ Str::startsWith($item->image, ['http', 'https', 'User', 'Admin']) ? asset($item->image) : asset('storage/' . $item->image) }}"
                                                        alt="{{ $item->name }}" class="product-image"
                                                        style="width: 100%; height: 100%; object-fit: cover;">
                                                    @if($loop->iteration <= 2)
                                                    <span class="badge bg-danger position-absolute top-0 end-0 m-3">
                                                        <i class="bx bx-trending-up me-1"></i>Hot
                                                    </span>
                                                    @endif
                                                </div>
                                                <!-- Product Info -->
                                                <div class="p-3">
                                                    <div class="mb-2">
                                                        <span class="badge bg-label-{{ $item->type == 'rental' ? 'warning' : ($item->type == 'mobil' ? 'info' : 'primary') }} text-uppercase"
                                                            style="font-size: 0.7rem; font-weight: 600;">
                                                            {{ $item->category }}
                                                        </span>
                                                    </div>
                                                    <h6 class="mb-2 fw-semibold">{{ $item->name }}</h6>
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <div>
                                                            <span class="text-primary fw-bold fs-6">{{ $item->price_formatted }}</span>
                                                            <small class="text-muted d-block">
                                                                @if($item->type == 'rental')
                                                                    Per 24 jam
                                                                @elseif($item->type == 'mobil')
                                                                    Sewa Harian
                                                                @else
                                                                    Per tabung
                                                                @endif
                                                            </small>
                                                        </div>
                                                    </div>
                                                    <div class="mt-3 pt-3 border-top">
                                                        <div class="d-flex justify-content-between text-muted small">
                                                            <span><i class="bx bx-check-circle me-1"></i>Stok: {{ $item->stock }}</span>
                                                            <span><i class="bx bx-time me-1"></i>{{ $item->sold }} {{ $item->type == 'gas' ? 'Terjual' : 'Booking' }}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @empty
                                    <div class="col-12 text-center py-5">
                                        <i class="bx bx-data text-muted fs-1 mb-3"></i>
                                        <p class="text-muted">Belum ada data produk populer untuk tahun ini.</p>
                                    </div>
                                    @endforelse

                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Custom CSS untuk Product Cards -->
                <style>
                    .product-card {
                        transition: all 0.3s ease;
                        border-radius: 0.5rem;
                        overflow: hidden;
                    }

                    .product-card:hover {
                        transform: translateY(-8px);
                        box-shadow: 0 0.5rem 1.5rem rgba(0, 0, 0, 0.15) !important;
                    }

                    .product-img-wrapper {
                        transition: all 0.3s ease;
                        border-radius: 0.5rem 0.5rem 0 0;
                    }

                    .product-card:hover .product-img-wrapper {
                        transform: scale(1.05);
                    }

                    .product-image {
                        transition: all 0.3s ease;
                    }

                    .badge-center {
                        display: flex;
                        align-items: center;
                        justify-content: center;
                    }

                    .badge.bg-danger {
                        background-color: #dc3545 !important;
                    }

                    /* Responsive adjustments */
                    @media (max-width: 991px) {
                        .product-img-wrapper {
                            height: 180px !important;
                        }
                    }

                    @media (max-width: 767px) {
                        .product-img-wrapper {
                            height: 220px !important;
                        }
                    }
                </style>

            <!-- Footer -->
            <footer class="content-footer footer bg-footer-theme">
                <div class="container-xxl d-flex flex-wrap justify-content-between py-2 flex-md-row flex-column">
                    <div class="mb-2 mb-md-0">
                        ©
                        {{ date('Y') }}
                        , made with by
                        <a href="https://themeselection.com" target="_blank" class="footer-link fw-bolder">SiladesBeng Project
                            Team 😎</a>
                    </div>
                </div>
            </footer>
            <div class="content-backdrop fade"></div>

        <!-- SCRIPT LANGSUNG DI SINI -->
        <script>
            // Tunggu sampai halaman selesai load
            window.addEventListener('load', function() {
                // Add delay to ensure layout is stable (prevent glitch when toast appears)
                setTimeout(function() {
                    console.log('Page loaded, initializing charts...');

                    // Cek apakah element ada
                    const chartElement = document.querySelector("#kinerjaChart");
                    console.log('Chart element:', chartElement);

                    if (!chartElement) {
                        console.error('Chart element not found!');
                        return;
                    }

                // ========================================
                // GRAFIK KINERJA BUMDES (AREA CHART) - REAL DATA
                // ========================================
                const kinerjaOptions = {
                    series: [{
                        name: 'Transaksi',
                        data: {!! json_encode($monthlyPerformance ?? [0,0,0,0,0,0,0,0,0,0,0,0]) !!}
                    }],
                    chart: {
                        type: 'area',
                        height: 300,
                        toolbar: {
                            show: false
                        },
                        zoom: {
                            enabled: false
                        }
                    },
                    colors: ['#f59e0b'],
                    stroke: {
                        curve: 'smooth',
                        width: 3
                    },
                    fill: {
                        type: 'gradient',
                        gradient: {
                            shadeIntensity: 1,
                            opacityFrom: 0.45,
                            opacityTo: 0.05,
                            stops: [0, 90, 100]
                        }
                    },
                    dataLabels: {
                        enabled: false
                    },
                    markers: {
                        size: 0,
                        hover: {
                            size: 5
                        }
                    },
                    xaxis: {
                        categories: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'],
                        labels: {
                            style: {
                                colors: '#374151',
                                fontSize: '12px',
                                fontWeight: 500
                            }
                        },
                        axisBorder: {
                            show: false
                        },
                        axisTicks: {
                            show: false
                        }
                    },
                    yaxis: {
                        labels: {
                            formatter: function(val) {
                                return Math.round(val);
                            },
                            style: {
                                colors: '#6b7280',
                                fontSize: '11px'
                            }
                        }
                    },
                    grid: {
                        borderColor: '#e5e7eb',
                        strokeDashArray: 3,
                        xaxis: {
                            lines: {
                                show: true
                            }
                        },
                        yaxis: {
                            lines: {
                                show: true
                            }
                        },
                        padding: {
                            top: 0,
                            right: 5,
                            bottom: 0,
                            left: 5
                        }
                    },
                    tooltip: {
                        y: {
                            formatter: function(val) {
                                return val + ' transaksi';
                            }
                        }
                    }
                };

                try {
                    const kinerjaChart = new ApexCharts(chartElement, kinerjaOptions);
                    kinerjaChart.render();
                    console.log('Kinerja chart rendered successfully!');
                } catch (error) {
                    console.error('Error rendering kinerja chart:', error);
                }

                // ========================================
                // PIE CHART TOTAL PENDAPATAN - REAL DATA
                // ========================================
                const pieContainer = document.querySelector("#pendapatanPieChart");
                if (pieContainer) {
                    @php
                        $pieSeries = [];
                        $pieLabels = [];
                        $pieColors = [];
                        $hexColors = [
                            'warning' => '#ffc107',
                            'primary' => '#696cff',
                            'info' => '#0dcaf0',
                            'success' => '#198754',
                            'danger' => '#dc3545',
                            'secondary' => '#8592a3'
                        ];
                        foreach($activeRevenueServices as $serviceName) {
                            $dataItem = $totalPendapatanData[$serviceName] ?? ['percentage' => 0, 'color' => 'secondary'];
                            $pieSeries[] = $dataItem['percentage'];
                            $pieLabels[] = $serviceName;
                            $pieColors[] = $hexColors[$dataItem['color']] ?? '#8592a3';
                        }
                    @endphp

                    const pieOptions = {
                        series: {!! json_encode($pieSeries) !!},
                        chart: {
                            type: 'pie',
                            height: 250 // slightly smaller to fit
                        },
                        labels: {!! json_encode($pieLabels) !!},
                        colors: {!! json_encode($pieColors) !!},

                        legend: {
                            show: false
                        },
                        dataLabels: {
                            enabled: true,
                            formatter: function(val) {
                                return val.toFixed(1) + '%';
                            },
                            style: {
                                fontSize: '12px',
                                fontWeight: 'bold',
                                colors: ['#fff']
                            },
                             dropShadow: { enabled: true }
                        },
                        tooltip: {
                             y: {
                                formatter: function(val) {
                                    return val.toFixed(1) + '%';
                                }
                            }
                        }
                    };
                    
                    try {
                        const pieChart = new ApexCharts(pieContainer, pieOptions);
                        pieChart.render();
                        console.log('Pie chart rendered successfully!');
                    } catch (error) {
                        console.error('Error rendering pie chart:', error);
                    }
                }

                // Handle select change
                const monthSelect = document.getElementById('pendapatan-month');
                if (monthSelect) {
                    monthSelect.addEventListener('change', function() {
                        const selectedMonth = this.value;
                        const url = new URL(window.location.href);
                        url.searchParams.set('month', selectedMonth);
                        url.searchParams.set('year', '{{ $totalPendapatanData['year'] ?? date('Y') }}');
                        window.location.href = url.toString();
                    });
                }


                // Donut Chart untuk Transaksi (Large centered chart)
                const orderChartElement = document.querySelector("#transactionDonutChart");
                if (orderChartElement) {
                    @php
                        $donutSeries = [];
                        $donutLabels = [];
                        $donutColors = [];
                        $totalDonut = 0;
                        
                        $countMap = [
                            'Penyewaan Alat' => ['count' => $rentalCount ?? 0, 'color' => '#ffc107'],
                            'Penjualan Gas' => ['count' => $gasCount ?? 0, 'color' => '#696cff'],
                            'Penyewaan Mobil' => ['count' => $mobilCount ?? 0, 'color' => '#0dcaf0']
                        ];
                        
                        foreach($activeRevenueServices as $serviceName) {
                            $c = $countMap[$serviceName]['count'] ?? 0;
                            $donutSeries[] = $c;
                            $donutLabels[] = $serviceName . " " . $c . " Transaksi";
                            $donutColors[] = $countMap[$serviceName]['color'] ?? '#8592a3';
                            $totalDonut += $c;
                        }
                    @endphp
                    var optionsOrder = {
                        series: {!! json_encode($donutSeries) !!},
                        chart: {
                            type: "donut",
                            width: "100%",
                            height: 220,
                            events: {
                                dataPointSelection: function(event, chartContext, config) {
                                    event.preventDefault();
                                }
                            }
                        },
                        labels: {!! json_encode($donutLabels) !!},
                        colors: {!! json_encode($donutColors) !!},
                        legend: {
                            show: true,
                            position: 'bottom',
                            horizontalAlign: 'center',
                            fontSize: '13px',
                            fontWeight: 500,
                            markers: {
                                width: 10,
                                height: 10,
                                radius: 12
                            },
                            itemMargin: {
                                horizontal: 10,
                                vertical: 5
                            }
                        },
                        dataLabels: {
                            enabled: false
                        },
                        plotOptions: {
                            pie: {
                                donut: {
                                    size: "70%",
                                    labels: {
                                        show: true,
                                        name: {
                                            show: false
                                        },
                                        value: {
                                            show: true,
                                            fontSize: "30px",
                                            fontWeight: 600,
                                            color: "#5e5873",
                                            offsetY: 5,
                                            formatter: function() {
                                                return "{{ $totalDonut }}";
                                            },
                                        },
                                        total: {
                                            show: true,
                                            label: "{{ $selectedYear }}",
                                            fontSize: "16px",
                                            color: "#6e6b7b",
                                            offsetY: 25,
                                        },
                                    },
                                },
                            },
                        },
                        tooltip: {
                            enabled: true,
                            y: {
                                formatter: function(value, { seriesIndex, dataPointIndex, w }) {
                                    // Return empty string to hide the value, only show label
                                    return '';
                                },
                                title: {
                                    formatter: function(seriesName) {
                                        // Return only the label name without any value
                                        return seriesName;
                                    }
                                }
                            },
                            custom: function({ series, seriesIndex, dataPointIndex, w }) {
                                // Custom tooltip to show only the label name
                                return '<div class="apexcharts-tooltip-custom" style="padding: 8px 12px; background: #fff; border: 1px solid #e3e3e3; border-radius: 4px;">' +
                                    '<span style="font-weight: 500; color: #333;">' + w.config.labels[seriesIndex] + '</span>' +
                                    '</div>';
                            }
                        },
                        states: {
                            active: {
                                filter: {
                                    type: 'none'
                                }
                            }
                        }
                    };

                    try {
                        var chartOrder = new ApexCharts(orderChartElement, optionsOrder);
                        chartOrder.render();
                        console.log('Order chart rendered successfully!');
                    } catch (error) {
                        console.error('Error rendering order chart:', error);
                    }
                } // End if orderChartElement
            }, 500); // End Timeout
            });
        </script>
    @endsection
