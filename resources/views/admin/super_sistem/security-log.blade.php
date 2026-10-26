@extends('admin.layouts.admin')

@section('title', 'Log Keamanan & Audit')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Sistem Platform /</span> Log Keamanan dan Audit</h4>

    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-1"><i class="bx bx-shield-quarter me-2 text-primary"></i>PHP dan Laravel pada Server Web</h5>
            <small class="text-muted">Nilai dari proses yang melayani halaman ini. Versi PHP terminal cPanel bisa berbeda.</small>
        </div>
        <div class="card-body">
            <dl class="row mb-3">
                <dt class="col-sm-4">PHP / handler</dt><dd class="col-sm-8">{{ $runtime['php'] }} / {{ $runtime['handler'] }}</dd>
                <dt class="col-sm-4">Laravel terpasang</dt><dd class="col-sm-8">{{ $runtime['laravel'] }}</dd>
                <dt class="col-sm-4">Mode debug</dt><dd class="col-sm-8 {{ $runtime['debug'] ? 'text-danger fw-bold' : '' }}">{{ $runtime['debug'] ? 'Aktif — nonaktifkan pada produksi' : 'Nonaktif' }}</dd>
                <dt class="col-sm-4">Session upload progress</dt><dd class="col-sm-8">{{ filter_var($runtime['upload_progress'], FILTER_VALIDATE_BOOLEAN) ? 'Aktif' : 'Nonaktif / tidak tersedia' }}</dd>
                <dt class="col-sm-4">Session serialize handler</dt><dd class="col-sm-8">{{ $runtime['serialize_handler'] ?: 'Tidak tersedia' }}</dd>
            </dl>
            <div class="alert alert-warning mb-0">
                <strong>Pemeriksaan PHP2Shell, 26 Oktober 2026:</strong>
                perbaikan upstream tersedia pada PHP 8.2.35, 8.3.36, 8.4.27, dan 8.5.12.
                Jika hosting memakai backport CloudLinux, minta bukti build/changelog paket dan restart worker kepada penyedia.
                Versi dan nilai di atas saja tidak membuktikan seluruh server aman.
                Untuk mitigasi session upload progress, gunakan php.ini atau konfigurasi server sebelum parsing request; jangan hanya mengandalkan .user.ini atau ini_set().
                <a href="https://www.php.net/archive/2026.php" target="_blank" rel="noopener noreferrer">Advisori resmi PHP</a>.
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-1"><i class="bx bx-user-plus me-2 text-primary"></i>Akun Staf Terbaru Dibuat</h5>
                <small class="text-muted"><i class="bx bx-info-circle"></i> Jejak siapa membuat akun staf siapa, untuk audit lintas region.</small>
            </div>
        </div>
        <div class="table-responsive text-nowrap">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Akun Staf</th>
                        <th>Region</th>
                        <th>Dibuat Oleh</th>
                        <th>Waktu</th>
                    </tr>
                </thead>
                <tbody class="table-border-bottom-0">
                    @forelse($recentStaffAccounts as $staff)
                        <tr>
                            <td>
                                <span class="fw-semibold">{{ $staff->name }}</span>
                                <small class="text-muted d-block">{{ $staff->email }}</small>
                            </td>
                            <td>{{ $staff->region->name ?? '-' }}</td>
                            <td>{{ $staff->creator->name ?? 'Tidak diketahui' }}</td>
                            <td>{{ $staff->created_at->format('d M Y H:i') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center text-muted py-4">Belum ada akun staf yang dibuat.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-1"><i class="bx bx-shield-quarter me-2 text-primary"></i>Log Aktivitas Keamanan Sistem</h5>
                <small class="text-muted"><i class="bx bx-info-circle"></i> 200 entri terbaru dari log sistem.</small>
            </div>
        </div>
        <div class="card-body">
            <div class="bg-dark rounded-3 p-3" style="max-height: 500px; overflow-y: auto; font-family: monospace; font-size: 0.8rem;">
                @forelse($lines as $line)
                    <div class="mb-1 {{ str_contains($line, 'SECURITY:') ? 'text-warning' : 'text-light' }}">{{ $line }}</div>
                @empty
                    <div class="text-muted">Belum ada log tercatat.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
