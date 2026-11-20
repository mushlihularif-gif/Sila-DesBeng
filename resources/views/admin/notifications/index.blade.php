@extends('admin.layouts.admin')

@section('title', 'Notifikasi')

@section('content')
@php
    $totalHalaman = $notifications->total();
    $belumDibacaHalaman = $notifications->getCollection()->where('is_read', false)->count();
    $sudahDibacaHalaman = $notifications->getCollection()->where('is_read', true)->count();
@endphp

<div class="container-xxl flex-grow-1 container-p-y">
    {{-- Page Header --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
        <div>
            <h4 class="fw-bold mb-1 text-dark">
                <span class="text-muted fw-normal">Aktivitas /</span> Notifikasi
            </h4>
            <p class="text-muted mb-0 small">
                Pantau seluruh pemberitahuan aktivitas wilayah dan layanan Anda.
            </p>
        </div>
        <div class="d-flex flex-wrap gap-2 w-100 w-md-auto">
            @unless(auth()->user()->isStaff())
                <a href="{{ route('admin.notifications.create') }}" class="btn btn-primary rounded-pill px-4 shadow-sm w-100 w-sm-auto">
                    <i class="bx bx-plus me-1"></i>Buat Notifikasi
                </a>
            @endunless
            <form action="{{ route('admin.notifications.mark-all-read') }}" method="POST" class="w-100 w-sm-auto" data-konfirmasi="Tandai semua notifikasi sebagai dibaca?">
                @csrf
                @method('PUT')
                <button type="submit" class="btn btn-outline-secondary rounded-pill px-4 w-100 w-sm-auto">
                    <i class="bx bx-check-double me-1"></i>Tandai Semua Dibaca
                </button>
            </form>
        </div>
    </div>

    {{-- KPI Summary --}}
    <div class="row g-3 mb-4">
        <div class="col-4">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-3 p-md-4 text-center">
                    <span class="text-muted small d-block mb-1">Total Notifikasi</span>
                    <h4 class="fw-bold text-primary mb-0">{{ $totalHalaman }}</h4>
                </div>
            </div>
        </div>
        <div class="col-4">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-3 p-md-4 text-center">
                    <span class="text-muted small d-block mb-1">Belum Dibaca</span>
                    <h4 class="fw-bold text-danger mb-0">{{ $belumDibacaHalaman }}</h4>
                </div>
            </div>
        </div>
        <div class="col-4">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-3 p-md-4 text-center">
                    <span class="text-muted small d-block mb-1">Sudah Dibaca</span>
                    <h4 class="fw-bold text-success mb-0">{{ $sudahDibacaHalaman }}</h4>
                </div>
            </div>
        </div>
    </div>

    {{-- Main List Card --}}
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="card-header bg-white border-bottom px-4 py-3 d-flex flex-column flex-md-row justify-content-between align-items-stretch align-items-md-center gap-3">
            <div class="d-flex align-items-center gap-2">
                <i class="bx bx-bell text-primary fs-4"></i>
                <h6 class="fw-bold text-dark mb-0">Daftar Pemberitahuan</h6>
                <span class="badge bg-label-primary rounded-pill">{{ $totalHalaman }}</span>
            </div>

            <form method="GET" action="{{ route('admin.notifications.index') }}" class="d-flex gap-2 w-100 w-md-auto">
                <div class="input-group input-group-sm" style="min-width: 240px;">
                    <span class="input-group-text bg-light border-end-0 text-muted">
                        <i class="bx bx-search"></i>
                    </span>
                    <input type="text" name="search" value="{{ $search ?? request('search') }}" class="form-control bg-light border-start-0" placeholder="Cari judul atau pesan...">
                </div>
                @if(!empty($search) || request('search'))
                    <a href="{{ route('admin.notifications.index') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3">Reset</a>
                @endif
            </form>
        </div>

        <div class="card-body p-0">
            @if($notifications->isEmpty())
                <div class="text-center py-5 px-3">
                    <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-light mb-3" style="width: 64px; height: 64px;">
                        <i class="bx bx-bell-off fs-1 text-muted"></i>
                    </div>
                    <h6 class="fw-semibold text-dark mb-1">Belum ada notifikasi</h6>
                    <p class="text-muted small mb-0">Tidak ada pemberitahuan yang sesuai saat ini.</p>
                </div>
            @else
                {{-- Desktop Table --}}
                <div class="table-responsive d-none d-md-block">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4 py-3 text-secondary text-uppercase small fw-bold">Status</th>
                                <th class="py-3 text-secondary text-uppercase small fw-bold">Notifikasi</th>
                                <th class="py-3 text-secondary text-uppercase small fw-bold">Waktu</th>
                                <th class="pe-4 py-3 text-end text-secondary text-uppercase small fw-bold">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($notifications as $notification)
                                <tr class="{{ !$notification->is_read ? 'bg-primary bg-opacity-10' : '' }}">
                                    <td class="ps-4 py-3" style="width: 130px;">
                                        @if(!$notification->is_read)
                                            <span class="badge bg-label-danger rounded-pill px-2.5 py-1 fw-semibold">
                                                <i class="bx bxs-circle me-1" style="font-size: 0.45rem; vertical-align: middle;"></i>Baru
                                            </span>
                                        @else
                                            <span class="badge bg-label-success rounded-pill px-2.5 py-1 fw-semibold">
                                                <i class="bx bx-check-double me-1"></i>Dibaca
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-3">
                                        <div class="fw-bold text-dark mb-0.5">{{ $notification->title }}</div>
                                        <p class="text-muted mb-1 small">{{ Str::limit($notification->message, 120) }}</p>
                                        <span class="badge bg-light text-secondary border fw-medium" style="font-size: 0.7rem;">
                                            {{ ucfirst(str_replace('_', ' ', (string) $notification->type)) }}
                                        </span>
                                    </td>
                                    <td class="py-3 text-nowrap" style="width: 160px;">
                                        <div class="fw-semibold text-dark small">{{ $notification->created_at->locale('id')->isoFormat('D MMM Y') }}</div>
                                        <small class="text-muted">{{ $notification->created_at->format('H:i') }} WIB</small>
                                    </td>
                                    <td class="text-end pe-4 py-3 text-nowrap" style="width: 120px;">
                                        @if(!$notification->is_read)
                                            <form action="{{ route('admin.notifications.mark-as-read', $notification->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('PUT')
                                                <button type="submit" class="btn btn-sm btn-icon btn-outline-success rounded-circle me-1" title="Tandai Dibaca">
                                                    <i class="bx bx-check"></i>
                                                </button>
                                            </form>
                                        @endif
                                        @unless(auth()->user()->isStaff())
                                            <form action="{{ route('admin.notifications.destroy', $notification->id) }}" method="POST" class="d-inline delete-form">
                                                @csrf
                                                @method('DELETE')
                                                <button type="button" class="btn btn-sm btn-icon btn-outline-danger rounded-circle delete-btn" title="Hapus Notifikasi">
                                                    <i class="bx bx-trash"></i>
                                                </button>
                                            </form>
                                        @endunless
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Mobile Cards --}}
                <div class="d-md-none p-3 d-flex flex-column gap-2.5">
                    @foreach($notifications as $notification)
                        <div class="p-3 rounded-4 border {{ !$notification->is_read ? 'bg-primary bg-opacity-10 border-primary border-opacity-25' : 'bg-white' }}">
                            <div class="d-flex justify-content-between align-items-start gap-2 mb-1.5">
                                <span class="fw-bold text-dark small">{{ $notification->title }}</span>
                                @if(!$notification->is_read)
                                    <span class="badge bg-label-danger rounded-pill flex-shrink-0" style="font-size: 0.68rem;">Baru</span>
                                @else
                                    <span class="badge bg-label-success rounded-pill flex-shrink-0" style="font-size: 0.68rem;">Dibaca</span>
                                @endif
                            </div>
                            <p class="text-muted small mb-2" style="word-break: break-word;">{{ $notification->message }}</p>
                            <div class="d-flex justify-content-between align-items-center gap-2 pt-2 border-top">
                                <small class="text-muted" style="font-size: 0.72rem;">
                                    <i class="bx bx-time-five me-1"></i>{{ $notification->created_at->diffForHumans() }}
                                </small>
                                <div class="d-flex gap-1.5">
                                    @if(!$notification->is_read)
                                        <form action="{{ route('admin.notifications.mark-as-read', $notification->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('PUT')
                                            <button type="submit" class="btn btn-xs btn-outline-success rounded-pill px-2.5">
                                                <i class="bx bx-check me-0.5"></i>Dibaca
                                            </button>
                                        </form>
                                    @endif
                                    @unless(auth()->user()->isStaff())
                                        <form action="{{ route('admin.notifications.destroy', $notification->id) }}" method="POST" class="d-inline delete-form">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" class="btn btn-xs btn-outline-danger rounded-pill px-2.5 delete-btn">
                                                <i class="bx bx-trash"></i>
                                            </button>
                                        </form>
                                    @endunless
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Footer Pagination & Delete All --}}
                <div class="p-3 p-md-4 border-top bg-light bg-opacity-50 d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
                    <div class="text-muted small">
                        Menampilkan {{ $notifications->firstItem() }}–{{ $notifications->lastItem() }} dari {{ $notifications->total() }} notifikasi
                    </div>
                    <div class="d-flex flex-wrap justify-content-center align-items-center gap-2">
                        @if($notifications->count() > 0)
                            @unless(auth()->user()->isStaff())
                                <form action="{{ route('admin.notifications.deleteAll') }}" method="POST" class="delete-all-form">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-3 delete-all-btn">
                                        <i class="bx bx-trash me-1"></i>Hapus Semua
                                    </button>
                                </form>
                            @endunless
                        @endif

                        @if($notifications->hasPages())
                            @if($notifications->onFirstPage())
                                <button class="btn btn-sm btn-outline-secondary rounded-pill px-3" disabled>
                                    <i class="bx bx-chevron-left"></i>
                                </button>
                            @else
                                <a href="{{ $notifications->previousPageUrl() }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                    <i class="bx bx-chevron-left"></i>
                                </a>
                            @endif

                            <span class="badge bg-primary rounded-pill px-3 py-2">
                                {{ $notifications->currentPage() }} / {{ $notifications->lastPage() }}
                            </span>

                            @if($notifications->hasMorePages())
                                <a href="{{ $notifications->nextPageUrl() }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                    <i class="bx bx-chevron-right"></i>
                                </a>
                            @else
                                <button class="btn btn-sm btn-outline-secondary rounded-pill px-3" disabled>
                                    <i class="bx bx-chevron-right"></i>
                                </button>
                            @endif
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.delete-btn').forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                const form = this.closest('form');
                Swal.fire({
                    title: 'Hapus Notifikasi?',
                    text: 'Notifikasi ini akan dihapus permanen.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Ya, Hapus!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) form.submit();
                });
            });
        });

        const deleteAllBtn = document.querySelector('.delete-all-btn');
        if (deleteAllBtn) {
            deleteAllBtn.addEventListener('click', function(e) {
                e.preventDefault();
                const form = this.closest('form');
                Swal.fire({
                    title: 'Hapus Semua Notifikasi?',
                    text: 'Seluruh notifikasi di sistem akan dihapus permanen.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Ya, Hapus Semua!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) form.submit();
                });
            });
        }
    });
</script>
@endsection
