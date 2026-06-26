@extends('admin.layouts.admin')

@section('title', 'Notifikasi')

@section('content')
<div class="container-fluid py-4">
    
    <!-- Notifications List -->
    <div class="card border-0 shadow-sm rounded-4 mb-4 overflow-hidden">
        <div class="card-header bg-white border-bottom p-4 d-flex justify-content-between align-items-end flex-wrap gap-3">
            <div>
                <h4 class="fw-bold fs-3 mb-1 text-primary">Notifikasi</h4>
                <p class="text-muted mb-0">Kelola dan kirim notifikasi ke pengguna</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.notifications.create') }}" class="btn btn-primary rounded-pill px-4 shadow-sm">
                    <i class="bx bx-plus me-1"></i> Buat Notifikasi Baru
                </a>
                <form action="{{ route('admin.notifications.mark-all-read') }}" method="POST" class="d-inline" onsubmit="return confirm('Tandai semua sebagai dibaca?')">
                    @csrf
                    @method('PUT')
                    <button type="submit" class="btn btn-outline-secondary rounded-pill px-4 shadow-sm">
                        <i class="bx bx-check-double me-1"></i> Tandai Semua Dibaca
                    </button>
                </form>
            </div>
        </div>
        <div class="card-body p-0">
            @if($notifications->isEmpty())
                <div class="text-center py-5">
                    <div class="mb-3">
                        <i class="bx bx-bell-off fs-1 text-muted opacity-50"></i>
                    </div>
                    <h6 class="text-muted">Tidak ada notifikasi saat ini</h6>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4 py-3 text-secondary text-uppercase small fw-bold">Status</th>
                                <th class="py-3 text-secondary text-uppercase small fw-bold">Notifikasi</th>
                                <th class="py-3 text-secondary text-uppercase small fw-bold">Penerima</th>
                                <th class="py-3 text-secondary text-uppercase small fw-bold">Waktu</th>
                                <th class="pe-4 py-3 text-center text-secondary text-uppercase small fw-bold">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="border-top-0">
                            @foreach($notifications as $notification)
                            <tr class="{{ !$notification->is_read ? 'bg-primary-subtle bg-opacity-10' : '' }}">
                                <td class="ps-4 py-3">
                                    @if(!$notification->is_read)
                                        <span class="badge bg-danger rounded-pill px-3 py-2">
                                            <i class="bx bx-bell me-1"></i> Baru
                                        </span>
                                    @else
                                        <span class="badge bg-success-subtle text-success rounded-pill px-3 py-2">
                                            <i class="bx bx-check-double me-1"></i> Dibaca
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3">
                                    <div>
                                        <div class="fw-bold text-dark mb-1">{{ $notification->title }}</div>
                                        <p class="text-muted mb-0 small">{{ Str::limit($notification->message, 80) }}</p>
                                        <small class="text-muted">
                                            <i class="bx bx-category me-1"></i>
                                            @if($notification->type == 'pesan_admin')
                                                <span class="badge bg-purple-subtle text-purple">Pesan Admin</span>
                                            @elseif($notification->type == 'status_berubah')
                                                <span class="badge bg-info-subtle text-info">Status Berubah</span>
                                            @elseif($notification->type == 'status_update')
                                                <span class="badge bg-primary-subtle text-primary">Update Status</span>
                                            @elseif($notification->type == 'delivery_proof')
                                                <span class="badge bg-success-subtle text-success">Bukti Pengiriman</span>
                                            @else
                                                <span class="badge bg-secondary-subtle text-secondary">{{ ucfirst($notification->type) }}</span>
                                            @endif
                                        </small>
                                    </div>
                                </td>
                                <td class="py-3">
                                    <span class="badge bg-secondary-subtle text-secondary rounded-pill">
                                        <i class="bx bx-server me-1"></i> Sistem
                                    </span>
                                </td>
                                <td class="py-3">
                                    <div class="fw-medium text-dark small">{{ $notification->created_at->locale('id')->isoFormat('D MMM Y') }}</div>
                                    <small class="text-muted">{{ $notification->created_at->format('H:i') }} WIB</small>
                                </td>
                                <td class="text-center pe-4 py-3">
                                    @if(!$notification->is_read)
                                        <form action="{{ route('admin.notifications.mark-as-read', $notification->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('PUT')
                                            <button type="submit" class="btn btn-sm btn-icon btn-light text-success rounded-circle border shadow-sm hover-success" title="Tandai Dibaca">
                                                <i class="bx bx-check fs-5"></i>
                                            </button>
                                        </form>
                                    @else
                                        <i class="bx bx-check-double text-success fs-4 me-2" title="Sudah Dibaca"></i>
                                    @endif
                                    
                                    <form action="{{ route('admin.notifications.destroy', $notification->id) }}" method="POST" class="d-inline delete-form">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" class="btn btn-sm btn-icon btn-light text-danger rounded-circle border shadow-sm hover-danger delete-btn" title="Hapus Notifikasi">
                                            <i class="bx bx-trash fs-5"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if($notifications->hasPages())
                <div class="p-4 border-top bg-light">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="text-muted small">
                            Menampilkan {{ $notifications->firstItem() }} - {{ $notifications->lastItem() }} dari {{ $notifications->total() }} notifikasi
                        </div>
                        <div class="d-flex gap-2">
                            <!-- Hapus Semua Button -->
                            @if($notifications->count() > 0)
                                <form action="{{ route('admin.notifications.deleteAll') }}" method="POST" class="delete-all-form">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-4 delete-all-btn">
                                        <i class="bx bx-trash me-1"></i> Hapus Semua
                                    </button>
                                </form>
                            @endif

                            @if($notifications->onFirstPage())
                                <button class="btn btn-sm btn-outline-secondary rounded-pill px-4" disabled>
                                    <i class="bx bx-chevron-left me-1"></i> Sebelumnya
                                </button>
                            @else
                                <a href="{{ $notifications->previousPageUrl() }}" class="btn btn-sm btn-outline-primary rounded-pill px-4">
                                    <i class="bx bx-chevron-left me-1"></i> Sebelumnya
                                </a>
                            @endif
                            
                            <span class="btn btn-sm btn-primary rounded-pill px-3">
                                {{ $notifications->currentPage() }} / {{ $notifications->lastPage() }}
                            </span>
                            
                            @if($notifications->hasMorePages())
                                <a href="{{ $notifications->nextPageUrl() }}" class="btn btn-sm btn-outline-primary rounded-pill px-4">
                                    Selanjutnya <i class="bx bx-chevron-right ms-1"></i>
                                </a>
                            @else
                                <button class="btn btn-sm btn-outline-secondary rounded-pill px-4" disabled>
                                    Selanjutnya <i class="bx bx-chevron-right ms-1"></i>
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
                @endif
            @endif
        </div>
    </div>
</div>

<style>
    .hover-success:hover { 
        background-color: #198754 !important; 
        color: white !important; 
        border-color: #198754 !important; 
    }
    .hover-danger:hover {
        background-color: #dc3545 !important;
        color: white !important;
        border-color: #dc3545 !important;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const deleteButtons = document.querySelectorAll('.delete-btn');
        
        deleteButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault(); // Mencegah submit form langsung
                const form = this.closest('form');
                
                Swal.fire({
                    title: 'Hapus Notifikasi?',
                    text: "Notifikasi ini akan dihapus permanen untuk semua user!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Ya, Hapus!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });
        });

        // Handler untuk Hapus Semua Notifikasi
        const deleteAllBtn = document.querySelector('.delete-all-btn');
        if (deleteAllBtn) {
            deleteAllBtn.addEventListener('click', function(e) {
                e.preventDefault();
                const form = this.closest('form');

                Swal.fire({
                    title: 'Hapus SEMUA Notifikasi?',
                    text: "Tindakan ini akan menghapus SELURUH notifikasi di sistem secara PERMANEN. Tidak bisa dikembalikan!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Ya, Hapus Semua!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });
        }
    });
</script>
@endsection