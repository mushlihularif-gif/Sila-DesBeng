<div class="table-responsive text-nowrap">
    <table class="table table-hover align-middle">
        <thead class="table-light">
            <tr>
                <th class="py-3 ps-4">Staf</th>
                <th class="py-3">Kontak</th>
                <th class="py-3">Akses Unit Layanan</th>
                <th class="py-3">Status</th>
                <th class="py-3 text-end pe-4">Aksi</th>
            </tr>
        </thead>
        <tbody class="table-border-bottom-0">
            @forelse($staffUsers as $stf)
            <tr>
                <td class="ps-4">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-md me-3">
                            <span class="avatar-initial rounded-circle bg-label-primary notranslate" translate="no">{{ strtoupper(substr($stf->name, 0, 2)) }}</span>
                        </div>
                        <div>
                            <span class="fw-bold text-dark d-block">{{ $stf->name }}</span>
                            <small class="text-muted">Ditambahkan: {{ $stf->created_at->format('d M Y') }}</small>
                        </div>
                    </div>
                </td>
                <td>
                    <div class="d-flex flex-column">
                        <span class="text-dark mb-1"><i class="bx bx-envelope me-1 text-muted"></i> {{ $stf->email }}</span>
                    </div>
                </td>
                <td>
                    <div class="d-flex flex-wrap gap-1" style="max-width: 300px;">
                        @if($stf->staffPermissions->count() > 0)
                            @foreach($stf->staffPermissions as $perm)
                                @if(isset($availableUnits[$perm->unit_key]))
                                    <span class="badge bg-label-info">{{ $availableUnits[$perm->unit_key] }}</span>
                                @else
                                    <span class="badge bg-label-secondary">{{ $perm->unit_key }}</span>
                                @endif
                            @endforeach
                        @else
                            <span class="text-muted fst-italic small">Belum ada akses</span>
                        @endif
                    </div>
                </td>
                <td>
                    @if($stf->status === 'aktif')
                        <span class="badge bg-success bg-opacity-75 px-3 py-2 rounded-pill">Aktif</span>
                    @else
                        <span class="badge bg-danger bg-opacity-75 px-3 py-2 rounded-pill">Nonaktif</span>
                    @endif
                </td>
                <td class="text-end pe-4">
                    <div class="d-inline-flex gap-2">
                        <a href="{{ route('admin.staff.edit', $stf->id) }}" class="btn btn-sm btn-icon btn-outline-primary" data-bs-toggle="tooltip" title="Edit Staf">
                            <i class="bx bx-edit-alt"></i>
                        </a>
                        <form action="{{ route('admin.staff.toggle-status', $stf->id) }}" method="POST" onsubmit="return confirm('Yakin ingin mengubah status akun ini?')">
                            @csrf
                            @method('PUT')
                            <button type="submit" class="btn btn-sm btn-icon {{ $stf->status === 'aktif' ? 'btn-outline-warning' : 'btn-outline-success' }}" data-bs-toggle="tooltip" title="{{ $stf->status === 'aktif' ? 'Blokir' : 'Buka Blokir' }}">
                                <i class="bx {{ $stf->status === 'aktif' ? 'bx-block' : 'bx-check-circle' }}"></i>
                            </button>
                        </form>
                        <form action="{{ route('admin.staff.destroy', $stf->id) }}" method="POST" onsubmit="return confirm('PERINGATAN: Yakin ingin menghapus akun staf ini secara permanen?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-icon btn-outline-danger" data-bs-toggle="tooltip" title="Hapus Permanen">
                                <i class="bx bx-trash"></i>
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
                        <p class="mb-0">Belum ada data staf.</p>
                    </div>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="card-footer bg-white border-top py-3">
    {{ $staffUsers->appends(request()->except('page'))->links() }}
</div>
