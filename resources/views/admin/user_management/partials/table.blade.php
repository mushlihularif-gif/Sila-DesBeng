<div class="table-responsive text-nowrap">
    <table class="table table-hover align-middle">
        <thead class="table-light">
            <tr>
                <th class="py-3 ps-4">Pengguna</th>
                <th class="py-3">Asal Wilayah</th>
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
                            @if($user->file)
                                <img src="{{ route('media.avatar', ['filename' => basename($user->file->path)]) }}" alt="Avatar" class="rounded-circle" style="object-fit: cover;">
                            @else
                                <span class="avatar-initial rounded-circle bg-label-primary notranslate" translate="no">{{ strtoupper(substr($user->name, 0, 2)) }}</span>
                            @endif
                        </div>
                        <div>
                            <span class="fw-bold text-dark d-block">{{ $user->name }}</span>
                            <small class="text-muted">@ {{ $user->username }}</small>
                        </div>
                    </div>
                </td>
                <td>
                    <div class="d-flex flex-column" style="max-width: 250px; white-space: normal;">
                        @if($user->region)
                            <span class="text-dark fw-semibold mb-1"><i class="bx bx-map-pin me-1 text-muted"></i>{{ $user->region->name }}</span>
                            <small class="text-muted" style="font-size: 0.75rem;">{{ $user->region->full_path }}</small>
                        @else
                            <span class="text-muted fst-italic"><i class="bx bx-map me-1"></i>Belum diatur</span>
                        @endif
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
                            <button type="submit" class="btn btn-sm btn-icon {{ $user->status === 'aktif' ? 'btn-outline-danger' : 'btn-outline-success' }}" data-bs-toggle="tooltip" title="{{ $user->status === 'aktif' ? 'Blokir' : 'Buka Blokir' }}">
                                <i class="bx {{ $user->status === 'aktif' ? 'bx-block' : 'bx-check-circle' }}"></i>
                            </button>
                        </form>
                        @if($user->role === 'user')
                        <form action="{{ route('admin.manajemen-pengguna.kick', $user->id) }}" method="POST" onsubmit="return confirm('Yakin ingin mengeluarkan pengguna ini dari wilayah Anda? Akun akan tetap ada namun tidak lagi terikat pada wilayah ini.')">
                            @csrf
                            @method('PUT')
                            <button type="submit" class="btn btn-sm btn-icon btn-outline-warning" data-bs-toggle="tooltip" title="Keluarkan dari Wilayah">
                                <i class="bx bx-user-minus"></i>
                            </button>
                        </form>
                        @endif
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="text-center py-5">
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
    {{ $users->appends(request()->except('page'))->links() }}
</div>
