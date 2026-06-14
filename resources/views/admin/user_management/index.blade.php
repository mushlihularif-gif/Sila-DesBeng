@extends('admin.layouts.admin')

@section('title', 'Manajemen Pengguna')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center py-3">
                    <h5 class="mb-0 fw-bold">Daftar Pengguna</h5>
                    <span class="badge bg-label-primary rounded-pill">{{ $users->total() }} Pengguna</span>
                </div>
                <div class="table-responsive text-nowrap">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th class="py-3 ps-4">Pengguna</th>
                                <th class="py-3">Kontak</th>
                                <th class="py-3">Role</th>
                                <th class="py-3">Status</th>
                                <th class="py-3 text-end pe-4">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="table-border-bottom-0">
                            @forelse($users as $user)
                            <tr>
                                <td class="ps-4">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar avatar-md me-3">
                                            @if($user->avatar)
                                                <img src="{{ asset('storage/' . $user->avatar) }}" alt="Avatar" class="rounded-circle">
                                            @else
                                                <span class="avatar-initial rounded-circle bg-label-primary">{{ strtoupper(substr($user->name, 0, 2)) }}</span>
                                            @endif
                                        </div>
                                        <div>
                                            <span class="fw-bold text-dark d-block">{{ $user->name }}</span>
                                            <small class="text-muted">@ {{ $user->username }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex flex-column">
                                        <span class="text-dark mb-1"><i class="bx bx-envelope me-1 text-muted"></i> {{ $user->email }}</span>
                                        <span class="text-muted small"><i class="bx bx-phone me-1"></i> {{ $user->phone ?? '-' }}</span>
                                    </div>
                                </td>
                                <td>
                                    @if($user->role === 'admin')
                                        <span class="badge bg-label-danger">Admin</span>
                                    @else
                                        <span class="badge bg-label-info">User</span>
                                    @endif
                                </td>
                                <td>
                                    @if($user->status === 'aktif')
                                        <span class="badge bg-success bg-opacity-75 px-3 py-2 rounded-pill">Aktif</span>
                                    @else
                                        <span class="badge bg-danger bg-opacity-75 px-3 py-2 rounded-pill">Nonaktif</span>
                                    @endif
                                </td>
                                <td class="text-end pe-4">
                                    <div class="d-inline-flex gap-2">
                                        <a href="{{ route('admin.manajemen-pengguna.show', $user->id) }}" class="btn btn-sm btn-icon btn-outline-primary" data-bs-toggle="tooltip" title="Lihat Detail">
                                            <i class="bx bx-show"></i>
                                        </a>
                                        <form action="{{ route('admin.manajemen-pengguna.toggle-status', $user->id) }}" method="POST" onsubmit="return confirm('Yakin ingin mengubah status akun ini?')">
                                            @csrf
                                            @method('PUT')
                                            <button type="submit" class="btn btn-sm btn-icon {{ $user->status === 'aktif' ? 'btn-outline-danger' : 'btn-outline-success' }}" data-bs-toggle="tooltip" title="{{ $user->status === 'aktif' ? 'Nonaktifkan' : 'Aktifkan' }}">
                                                <i class="bx {{ $user->status === 'aktif' ? 'bx-block' : 'bx-check-circle' }}"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center py-5">
                                    <div class="text-muted">
                                        <i class="bx bx-user-x fs-1 mb-2"></i>
                                        <p class="mb-0">Belum ada data pengguna.</p>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="card-footer bg-white border-top py-3">
                    {{ $users->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection