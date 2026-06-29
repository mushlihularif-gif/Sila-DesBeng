@extends('admin.layouts.admin')

@section('title', 'Log Aktivitas')

@section('content')
<div class="container-fluid py-4">
    
    <!-- Filter & Search -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-4">
            <!-- Header & Action Buttons Row -->
            <div class="d-flex justify-content-between align-items-end flex-wrap gap-3 mb-3">
                <div>
                    <h4 class="fw-bold fs-3 mb-1 text-primary">Log Aktivitas</h4>
                    <p class="text-muted mb-0">Riwayat lengkap aktivitas pengguna dan sistem</p>
                </div>
                <div class="d-flex gap-2 position-relative" style="z-index: 1050;">
                    @if(in_array(auth()->user()->role, ['admin', 'super_admin']))
                    <button type="button" class="btn btn-outline-danger shadow-sm rounded-pill px-4" onclick="confirmClearLogs()">
                        <i class="bx bx-trash me-2"></i>Bersihkan Log
                    </button>
                    <form id="clear-logs-form" action="{{ route('admin.laporan.log.clear') }}" method="POST" style="display: none;">
                        @csrf
                    </form>
                    @endif
                    <a href="{{ route('admin.laporan.log') }}" class="btn btn-white border shadow-sm rounded-pill px-4">
                        <i class="bx bx-refresh me-2"></i>Refresh
                    </a>
                </div>
            </div>
            <hr class="mt-0 mb-3">
            <!-- Filter Fields -->
            <form action="{{ route('admin.laporan.log') }}" method="GET" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label fw-semibold text-muted small text-uppercase">Cari Aktivitas</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-0"><i class="bx bx-search text-secondary"></i></span>
                        <input type="text" name="search" class="form-control border-0 bg-light" placeholder="Cari deskripsi, user..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold text-muted small text-uppercase">Tipe Aksi</label>
                    <select name="action" class="form-select border-0 bg-light">
                        <option value="">Semua Aksi</option>
                        <optgroup label="Otentikasi">
                            <option value="Login" {{ request('action') == 'Login' ? 'selected' : '' }}>Login</option>
                            <option value="Logout" {{ request('action') == 'Logout' ? 'selected' : '' }}>Logout</option>
                        </optgroup>
                        <optgroup label="Pesanan & Transaksi">
                            <option value="Update Status" {{ request('action') == 'Update Status' ? 'selected' : '' }}>Update Status Pesanan</option>
                            <option value="Cancellation Review" {{ request('action') == 'Cancellation Review' ? 'selected' : '' }}>Review Pembatalan</option>
                            <option value="Return Rental" {{ request('action') == 'Return Rental' ? 'selected' : '' }}>Pengembalian Alat</option>
                        </optgroup>
                        <optgroup label="Laporan Manual">
                            <option value="Create Manual Report" {{ request('action') == 'Create Manual Report' ? 'selected' : '' }}>Buat Laporan Manual</option>
                            <option value="Update Manual Report" {{ request('action') == 'Update Manual Report' ? 'selected' : '' }}>Update Laporan Manual</option>
                        </optgroup>

                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold text-muted small text-uppercase">Tanggal</label>
                    <input type="date" name="date" class="form-control border-0 bg-light" value="{{ request('date') }}">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary rounded-pill w-100 fw-semibold">
                        Filter Data
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Logs Table -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="card-body p-0">
            @if($logs->isEmpty())
                <div class="text-center py-5">
                    <div class="mb-3"><i class="bx bx-history fs-1 text-muted opacity-25"></i></div>
                    <h6 class="text-muted fw-bold">Tidak ada riwayat aktivitas ditemukan</h6>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4 py-3 text-secondary text-uppercase small fw-bold">Waktu</th>
                                <th class="py-3 text-secondary text-uppercase small fw-bold">Pengguna</th>
                                <th class="py-3 text-secondary text-uppercase small fw-bold">Aksi</th>
                                <th class="py-3 text-secondary text-uppercase small fw-bold">Deskripsi</th>
                                <th class="py-3 text-secondary text-uppercase small fw-bold">IP Address</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($logs as $log)
                            <tr>
                                <td class="ps-4">
                                    <div class="d-flex flex-column">
                                        <span class="fw-semibold text-dark">{{ $log->created_at->format('d M Y') }}</span>
                                        <small class="text-muted">{{ $log->created_at->format('H:i:s') }}</small>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar avatar-sm border rounded-circle me-2 d-flex justify-content-center align-items-center" style="width: 32px; height: 32px; overflow: hidden;">
                                            @if($log->user && $log->user->file)
                                                <img src="{{ route('media.avatar', ['filename' => basename($log->user->file->path)]) }}" class="w-100 h-100 object-fit-cover" alt="Avatar">
                                            @else
                                                <span class="w-100 h-100 bg-secondary d-flex justify-content-center align-items-center text-white fw-bold" style="font-size: 14px;">
                                                    {{ strtoupper(substr($log->user->name ?? 'S', 0, 1)) }}
                                                </span>
                                            @endif
                                        </div>
                                        <span class="fw-medium text-dark">{{ $log->user->name ?? 'System' }}</span>
                                    </div>
                                </td>
                                <td>
                                    @php
                                        $actionLower = strtolower($log->action);
                                        $badgeClass = 'bg-secondary-subtle text-secondary';
                                        $icon = 'bx-radio-circle';
                                        
                                        if(str_contains($actionLower, 'create') || str_contains($actionLower, 'tambah')) {
                                            $badgeClass = 'bg-success-subtle text-success border border-success-subtle';
                                            $icon = 'bx-plus-circle';
                                        } elseif(str_contains($actionLower, 'update') || str_contains($actionLower, 'ubah')) {
                                            $badgeClass = 'bg-warning-subtle text-warning border border-warning-subtle';
                                            $icon = 'bx-edit';
                                        } elseif(str_contains($actionLower, 'delete') || str_contains($actionLower, 'hapus')) {
                                            $badgeClass = 'bg-danger-subtle text-danger border border-danger-subtle';
                                            $icon = 'bx-trash';
                                        } elseif(str_contains($actionLower, 'login') || str_contains($actionLower, 'masuk')) {
                                            $badgeClass = 'bg-info-subtle text-info border border-info-subtle';
                                            $icon = 'bx-log-in-circle';
                                        } else {
                                            $badgeClass = 'bg-primary-subtle text-primary border border-primary-subtle';
                                            $icon = 'bx-check-circle';
                                        }
                                    @endphp
                                    <span class="badge {{ $badgeClass }} rounded-pill px-3 py-2 fw-normal">
                                        <i class="bx {{ $icon }} me-1"></i> {{ ucfirst($log->action) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="text-dark">{{ $log->description }}</span>
                                </td>
                                <td>
                                    <span class="font-monospace small text-muted bg-light px-2 py-1 rounded">
                                        {{ $log->ip_address ?? '-' }}
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                @if($logs->hasPages())
                <div class="card-footer bg-white border-top py-3 px-4">
                    <div class="d-flex justify-content-end">
                        {{ $logs->appends(request()->query())->links('pagination::bootstrap-5') }}
                    </div>
                </div>
                @endif
            @endif
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function confirmClearLogs() {
        Swal.fire({
            title: 'Bersihkan Log?',
            text: "Masukkan password Anda untuk konfirmasi.",
            icon: 'warning',
            input: 'password',
            inputPlaceholder: 'Masukkan password...',
            inputAttributes: {
                autocapitalize: 'off',
                autocorrect: 'off'
            },
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Bersihkan',
            cancelButtonText: 'Batal',
            inputValidator: (value) => {
                if (!value) {
                    return 'Password wajib diisi!'
                }
            }
        }).then((result) => {
            if (result.isConfirmed) {
                const form = document.getElementById('clear-logs-form');
                // Add password input to form
                let passwordInput = form.querySelector('input[name="password"]');
                if (!passwordInput) {
                    passwordInput = document.createElement('input');
                    passwordInput.type = 'hidden';
                    passwordInput.name = 'password';
                    form.appendChild(passwordInput);
                }
                passwordInput.value = result.value;
                form.submit();
            }
        });
    }
</script>

<style>
    /* Pagination Customization */
    .page-link {
        border: none;
        color: #697a8d;
        border-radius: 50%;
        width: 36px;
        height: 36px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 2px;
    }
    .page-item.active .page-link {
        background-color: #696cff;
        color: white;
        box-shadow: 0 2px 4px rgba(105, 108, 255, 0.4);
    }
</style>
@endsection