@extends('admin.layouts.admin')

@section('title', 'Log Keamanan & Audit')

@section('content')
@php
    $uploadProgressOn = filter_var($runtime['upload_progress'] ?? false, FILTER_VALIDATE_BOOLEAN);
    $debugOn = (bool) ($runtime['debug'] ?? false);
    $securityLinesCount = collect($lines)->filter(fn ($l) => str_contains($l, 'SECURITY:'))->count();
    $superAdminLinesCount = collect($lines)->filter(fn ($l) => str_contains($l, 'SuperAdmin:'))->count();
@endphp

<style>
    .sec-kpi-card {
        border: 1px solid #eef1f6;
        border-radius: 16px;
        background: #ffffff;
    }
    .sec-terminal {
        background: #0f172a;
        color: #e2e8f0;
        border-radius: 0 0 16px 16px;
        max-height: 480px;
        overflow-y: auto;
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
        font-size: 0.78rem;
        line-height: 1.55;
    }
    .sec-terminal::-webkit-scrollbar {
        width: 6px;
    }
    .sec-terminal::-webkit-scrollbar-thumb {
        background: #334155;
        border-radius: 999px;
    }
    .sec-log-line {
        padding: 6px 14px;
        border-bottom: 1px solid rgba(148, 163, 184, 0.08);
        word-break: break-word;
    }
    .sec-log-line:hover {
        background: rgba(255, 255, 255, 0.04);
    }
    .sec-log-line.is-security {
        border-left: 3px solid #f59e0b;
        background: rgba(245, 158, 11, 0.06);
        color: #fde68a;
    }
    .sec-log-line.is-superadmin {
        border-left: 3px solid #38bdf8;
        color: #e0f2fe;
    }
</style>

<div class="container-xxl flex-grow-1 container-p-y">
    {{-- Page Header --}}
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-3 mb-4">
        <div>
            <h4 class="fw-bold mb-1 text-dark">
                <span class="text-muted fw-normal">Sistem Platform /</span> Log Keamanan &amp; Audit
            </h4>
            <p class="text-muted mb-0 small">
                Pantau status runtime server web, riwayat pembuatan akun staf lintas wilayah, dan catatan keamanan aplikasi.
            </p>
        </div>
        <button type="button" class="btn btn-outline-primary rounded-pill px-4 shadow-sm w-100 w-sm-auto d-inline-flex align-items-center justify-content-center gap-1.5" onclick="location.reload()">
            <i class="bx bx-refresh fs-5"></i>
            <span>Refresh Log</span>
        </button>
    </div>

    {{-- 4 Health & Audit KPI Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-xl-3">
            <div class="card sec-kpi-card shadow-sm h-100">
                <div class="card-body p-3 p-md-4 d-flex align-items-start justify-content-between gap-2">
                    <div class="min-w-0">
                        <span class="text-muted small fw-semibold d-block mb-1">Runtime PHP</span>
                        <h5 class="fw-bold text-dark mb-1">{{ $runtime['php'] }}</h5>
                        <small class="text-muted d-block text-truncate" style="font-size: 0.72rem;">Handler: {{ $runtime['handler'] }}</small>
                    </div>
                    <div class="d-flex align-items-center justify-content-center rounded-3 bg-label-primary flex-shrink-0" style="width: 40px; height: 40px;">
                        <i class="bx bx-code-block fs-4 text-primary"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-xl-3">
            <div class="card sec-kpi-card shadow-sm h-100">
                <div class="card-body p-3 p-md-4 d-flex align-items-start justify-content-between gap-2">
                    <div class="min-w-0">
                        <span class="text-muted small fw-semibold d-block mb-1">Laravel</span>
                        <h5 class="fw-bold text-dark mb-1">v{{ $runtime['laravel'] }}</h5>
                        <small class="text-muted d-block text-truncate" style="font-size: 0.72rem;">Framework terpasang</small>
                    </div>
                    <div class="d-flex align-items-center justify-content-center rounded-3 bg-label-info flex-shrink-0" style="width: 40px; height: 40px;">
                        <i class="bx bx-layer fs-4 text-info"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-xl-3">
            <div class="card sec-kpi-card shadow-sm h-100">
                <div class="card-body p-3 p-md-4 d-flex align-items-start justify-content-between gap-2">
                    <div class="min-w-0">
                        <span class="text-muted small fw-semibold d-block mb-1">Mode Debug</span>
                        <h5 class="fw-bold mb-1 {{ $debugOn ? 'text-danger' : 'text-success' }}">
                            {{ $debugOn ? 'Aktif' : 'Aman' }}
                        </h5>
                        <small class="{{ $debugOn ? 'text-danger fw-semibold' : 'text-muted' }} d-block" style="font-size: 0.72rem;">
                            {{ $debugOn ? 'Matikan di produksi!' : 'Nonaktif (Produksi)' }}
                        </small>
                    </div>
                    <div class="d-flex align-items-center justify-content-center rounded-3 bg-label-{{ $debugOn ? 'danger' : 'success' }} flex-shrink-0" style="width: 40px; height: 40px;">
                        <i class="bx {{ $debugOn ? 'bx-error' : 'bx-shield-alt-2' }} fs-4 text-{{ $debugOn ? 'danger' : 'success' }}"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-xl-3">
            <div class="card sec-kpi-card shadow-sm h-100">
                <div class="card-body p-3 p-md-4 d-flex align-items-start justify-content-between gap-2">
                    <div class="min-w-0">
                        <span class="text-muted small fw-semibold d-block mb-1">Catatan Log</span>
                        <h5 class="fw-bold text-dark mb-1">{{ count($lines) }}</h5>
                        <small class="text-muted d-block" style="font-size: 0.72rem;">
                            {{ $securityLinesCount }} security &bull; {{ $superAdminLinesCount }} admin
                        </small>
                    </div>
                    <div class="d-flex align-items-center justify-content-center rounded-3 bg-label-warning flex-shrink-0" style="width: 40px; height: 40px;">
                        <i class="bx bx-terminal fs-4 text-warning"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Server Configuration & Advisory Card --}}
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-header bg-white border-bottom px-4 py-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div class="d-flex align-items-center gap-2.5">
                <div class="d-flex align-items-center justify-content-center rounded-3 bg-label-primary" style="width: 36px; height: 36px;">
                    <i class="bx bx-server fs-5 text-primary"></i>
                </div>
                <div>
                    <h6 class="fw-bold text-dark mb-0">Konfigurasi Keamanan Sesi Server</h6>
                    <small class="text-muted">Dibaca langsung dari worker web yang melayani permintaan ini</small>
                </div>
            </div>
            <button type="button" class="btn btn-xs btn-outline-warning rounded-pill px-3 fw-semibold" data-bs-toggle="collapse" data-bs-target="#advisoryPhp2Shell">
                <i class="bx bx-info-circle me-1"></i>Catatan Keamanan PHP
            </button>
        </div>
        <div class="card-body p-4">
            <div class="row g-3">
                <div class="col-12 col-sm-6">
                    <div class="p-3 rounded-3 border bg-light bg-opacity-50 d-flex justify-content-between align-items-center gap-2">
                        <div>
                            <small class="text-muted d-block" style="font-size: 0.72rem;">Session Upload Progress</small>
                            <span class="fw-semibold text-dark small">{{ $uploadProgressOn ? 'Aktif' : 'Nonaktif / Tidak Tersedia' }}</span>
                        </div>
                        <span class="badge bg-label-{{ $uploadProgressOn ? 'warning' : 'success' }} rounded-pill">
                            {{ $uploadProgressOn ? 'Periksa' : 'Aman' }}
                        </span>
                    </div>
                </div>
                <div class="col-12 col-sm-6">
                    <div class="p-3 rounded-3 border bg-light bg-opacity-50 d-flex justify-content-between align-items-center gap-2">
                        <div>
                            <small class="text-muted d-block" style="font-size: 0.72rem;">Session Serialize Handler</small>
                            <span class="fw-semibold text-dark small">{{ $runtime['serialize_handler'] ?: 'Tidak tersedia' }}</span>
                        </div>
                        <span class="badge bg-label-info rounded-pill">Worker Web</span>
                    </div>
                </div>
            </div>

            <div class="collapse mt-3" id="advisoryPhp2Shell">
                <div class="alert alert-warning border-0 rounded-3 small mb-0" style="line-height: 1.55;">
                    <strong>Pemeriksaan PHP2Shell (26 Oktober 2026):</strong>
                    Perbaikan upstream tersedia pada PHP 8.2.35, 8.3.36, 8.4.27, dan 8.5.12. Jika hosting menggunakan backport CloudLinux, pastikan build paket terbaru aktif dan worker telah di-restart. Untuk mitigasi session upload progress, atur lewat <code>php.ini</code> server sebelum parsing request.
                    <a href="https://www.php.net/archive/2026.php" target="_blank" rel="noopener noreferrer" class="fw-semibold text-decoration-underline ms-1">Advisori resmi PHP ↗</a>
                </div>
            </div>
        </div>
    </div>

    {{-- Staff Account Audit Trail --}}
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
        <div class="card-header bg-white border-bottom px-4 py-3 d-flex flex-column flex-md-row justify-content-between align-items-stretch align-items-md-center gap-3">
            <div class="d-flex align-items-center gap-2.5">
                <div class="d-flex align-items-center justify-content-center rounded-3 bg-label-success" style="width: 36px; height: 36px;">
                    <i class="bx bx-user-plus fs-5 text-success"></i>
                </div>
                <div>
                    <h6 class="fw-bold text-dark mb-0">Audit Pembuatan Akun Staf</h6>
                    <small class="text-muted">Jejak siapa membuat akun staf lintas wilayah ({{ $recentStaffAccounts->count() }} terbaru)</small>
                </div>
            </div>

            @if($recentStaffAccounts->isNotEmpty())
                <div class="input-group input-group-sm" style="max-width: 260px;">
                    <span class="input-group-text bg-light border-end-0 text-muted"><i class="bx bx-search"></i></span>
                    <input type="text" id="searchStaffAudit" class="form-control bg-light border-start-0" placeholder="Cari staf, email, wilayah...">
                </div>
            @endif
        </div>

        @if($recentStaffAccounts->isEmpty())
            <div class="card-body text-center py-5">
                <i class="bx bx-user-check fs-1 text-muted opacity-50 d-block mb-2"></i>
                <p class="text-muted small mb-0">Belum ada akun staf layanan yang dibuat.</p>
            </div>
        @else
            {{-- Desktop Table --}}
            <div class="table-responsive d-none d-md-block">
                <table class="table table-hover align-middle mb-0" id="tableStaffAudit">
                    <thead class="bg-light">
                        <tr class="text-muted text-uppercase" style="font-size: 0.72rem;">
                            <th class="ps-4 py-3">Akun Staf</th>
                            <th class="py-3">Wilayah</th>
                            <th class="py-3">Dibuat Oleh</th>
                            <th class="pe-4 py-3 text-end">Waktu</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentStaffAccounts as $staff)
                            <tr class="staff-audit-item" data-search="{{ mb_strtolower(($staff->name ?? '') . ' ' . ($staff->email ?? '') . ' ' . ($staff->region->name ?? '') . ' ' . ($staff->creator->name ?? '')) }}">
                                <td class="ps-4 py-3">
                                    <div class="fw-semibold text-dark small">{{ $staff->name }}</div>
                                    <small class="text-muted">{{ $staff->email }}</small>
                                </td>
                                <td class="py-3">
                                    <span class="badge bg-label-primary rounded-pill">{{ $staff->region->name ?? '-' }}</span>
                                </td>
                                <td class="py-3 small text-dark">{{ $staff->creator->name ?? 'Tidak diketahui' }}</td>
                                <td class="pe-4 py-3 text-end small text-muted text-nowrap">{{ $staff->created_at->format('d M Y, H:i') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Mobile Cards --}}
            <div class="d-md-none p-3 d-flex flex-column gap-2.5">
                @foreach($recentStaffAccounts as $staff)
                    <div class="p-3 rounded-3 border bg-light bg-opacity-50 staff-audit-item" data-search="{{ mb_strtolower(($staff->name ?? '') . ' ' . ($staff->email ?? '') . ' ' . ($staff->region->name ?? '') . ' ' . ($staff->creator->name ?? '')) }}">
                        <div class="d-flex justify-content-between align-items-start gap-2 mb-1">
                            <span class="fw-bold text-dark small">{{ $staff->name }}</span>
                            <span class="badge bg-label-primary rounded-pill flex-shrink-0" style="font-size: 0.68rem;">{{ $staff->region->name ?? '-' }}</span>
                        </div>
                        <small class="text-muted d-block mb-2 text-break">{{ $staff->email }}</small>
                        <div class="d-flex justify-content-between align-items-center pt-2 border-top small text-muted" style="font-size: 0.72rem;">
                            <span>Oleh: <strong class="text-dark">{{ $staff->creator->name ?? 'Tidak diketahui' }}</strong></span>
                            <span>{{ $staff->created_at->format('d M Y H:i') }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- Security Log Terminal Viewer --}}
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="card-header bg-white border-bottom px-4 py-3">
            <div class="d-flex flex-column flex-lg-row justify-content-between align-items-stretch align-items-lg-center gap-3">
                <div class="d-flex align-items-center gap-2.5">
                    <div class="d-flex align-items-center justify-content-center rounded-3 bg-label-danger" style="width: 36px; height: 36px;">
                        <i class="bx bx-shield-quarter fs-5 text-danger"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold text-dark mb-0">Log Aktivitas Keamanan Sistem</h6>
                        <small class="text-muted">Menampilkan hingga 200 entri terbaru (<code>SECURITY:</code> &amp; <code>SuperAdmin:</code>)</small>
                    </div>
                </div>

                <div class="d-flex flex-wrap align-items-center gap-2">
                    <div class="btn-group btn-group-sm" role="group">
                        <button type="button" class="btn btn-primary rounded-start-pill px-3 log-filter-btn active" data-type="all">Semua</button>
                        <button type="button" class="btn btn-outline-warning px-3 log-filter-btn" data-type="security">SECURITY ({{ $securityLinesCount }})</button>
                        <button type="button" class="btn btn-outline-info rounded-end-pill px-3 log-filter-btn" data-type="superadmin">SuperAdmin ({{ $superAdminLinesCount }})</button>
                    </div>
                    <div class="input-group input-group-sm" style="max-width: 210px;">
                        <span class="input-group-text bg-light border-end-0 text-muted"><i class="bx bx-search"></i></span>
                        <input type="text" id="searchLogLines" class="form-control bg-light border-start-0" placeholder="Filter log...">
                    </div>
                </div>
            </div>
        </div>

        <div class="sec-terminal" id="securityLogContainer">
            @forelse($lines as $idx => $line)
                @php
                    $isSec = str_contains($line, 'SECURITY:');
                    $isSA  = str_contains($line, 'SuperAdmin:');
                    $logType = $isSec ? 'security' : ($isSA ? 'superadmin' : 'other');
                @endphp
                <div class="sec-log-line {{ $isSec ? 'is-security' : ($isSA ? 'is-superadmin' : '') }}"
                     data-type="{{ $logType }}"
                     data-text="{{ mb_strtolower($line) }}">
                    <span class="text-secondary me-2 select-none" style="opacity: 0.55;">{{ str_pad((string) ($idx + 1), 3, '0', STR_PAD_LEFT) }}</span>{{ $line }}
                </div>
            @empty
                <div class="p-4 text-center text-secondary">Belum ada catatan log keamanan yang tercatat.</div>
            @endforelse
            <div id="logFilterEmpty" class="p-4 text-center text-secondary d-none">Tidak ada baris log yang cocok dengan filter.</div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Filter Audit Staf
    const staffSearch = document.getElementById('searchStaffAudit');
    if (staffSearch) {
        staffSearch.addEventListener('input', function () {
            const q = this.value.trim().toLowerCase();
            document.querySelectorAll('.staff-audit-item').forEach(function (el) {
                el.classList.toggle('d-none', q !== '' && !(el.dataset.search || '').includes(q));
            });
        });
    }

    // Filter Terminal Log
    const logSearch = document.getElementById('searchLogLines');
    const logEmpty = document.getElementById('logFilterEmpty');
    let activeLogType = 'all';

    function filterLogs() {
        const q = (logSearch ? logSearch.value : '').trim().toLowerCase();
        const rows = document.querySelectorAll('#securityLogContainer .sec-log-line');
        let shown = 0;

        rows.forEach(function (row) {
            const matchType = activeLogType === 'all' || row.dataset.type === activeLogType;
            const matchText = !q || (row.dataset.text || '').includes(q);
            const visible = matchType && matchText;
            row.classList.toggle('d-none', !visible);
            if (visible) shown++;
        });

        if (logEmpty) {
            logEmpty.classList.toggle('d-none', rows.length === 0 || shown > 0);
        }
    }

    if (logSearch) logSearch.addEventListener('input', filterLogs);
    document.querySelectorAll('.log-filter-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            document.querySelectorAll('.log-filter-btn').forEach(function (b) {
                b.classList.remove('active', 'btn-primary');
                if (b.dataset.type === 'all') b.classList.add('btn-outline-secondary');
            });
            this.classList.add('active');
            if (this.dataset.type === 'all') {
                this.classList.remove('btn-outline-secondary');
                this.classList.add('btn-primary');
            }
            activeLogType = this.dataset.type || 'all';
            filterLogs();
        });
    });
});
</script>
@endpush
@endsection
