<style>
    .table-modern th {
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 1px;
        font-weight: 700;
        color: #a1acb8 !important;
        border-bottom: 2px solid #f0f2f4;
    }
    .table-modern td {
        vertical-align: middle;
        padding: 1rem 1.25rem;
        border-bottom: 1px solid #f0f2f4;
        transition: all 0.2s;
    }
    .table-modern tbody tr:hover {
        background-color: #f8f9fa;
        transform: scale(1.001);
    }
    .status-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        display: inline-block;
        margin-right: 6px;
    }
    .status-dot.active {
        background-color: #71dd37;
        box-shadow: 0 0 0 3px rgba(113, 221, 55, 0.2);
        animation: pulse 2s infinite;
    }
    .status-dot.inactive {
        background-color: #ff3e1d;
        box-shadow: 0 0 0 3px rgba(255, 62, 29, 0.2);
    }
    @keyframes pulse {
        0% { box-shadow: 0 0 0 0 rgba(113, 221, 55, 0.4); }
        70% { box-shadow: 0 0 0 6px rgba(113, 221, 55, 0); }
        100% { box-shadow: 0 0 0 0 rgba(113, 221, 55, 0); }
    }
    .btn-icon-soft {
        width: 32px;
        height: 32px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        transition: all 0.2s;
        border: none;
        background: #f8f9fa;
    }
    .btn-icon-soft.edit:hover { background: #e7e7ff; color: #696cff; }
    .btn-icon-soft.suspend:hover { background: #fff2d6; color: #ffab00; }
    .btn-icon-soft.activate:hover { background: #e8fadf; color: #71dd37; }
    .btn-icon-soft.delete:hover { background: #ffe0db; color: #ff3e1d; }
    .badge-soft {
        padding: 0.4em 0.8em;
        font-weight: 600;
        border-radius: 6px;
        font-size: 0.75rem;
    }
</style>

<div class="table-responsive w-100 px-0">
    <table class="table table-modern table-hover align-middle mb-0 text-nowrap">
        <thead class="bg-light bg-opacity-50">
            <tr>
                <th class="py-3 ps-4">PROFIL STAF</th>
                <th class="py-3">KONTAK</th>
                <th class="py-3">HAK AKSES</th>
                <th class="py-3">STATUS</th>
                <th class="py-3 text-end pe-4">AKSI</th>
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
                    <div class="d-flex flex-wrap gap-2" style="max-width: 320px;">
                        @if($stf->staffPermissions->count() > 0)
                            @foreach($stf->staffPermissions as $perm)
                                @if(isset($availableUnits[$perm->unit_key]))
                                    <span class="badge bg-label-primary badge-soft"><i class="bx bx-check-shield me-1"></i>{{ $availableUnits[$perm->unit_key] }}</span>
                                @else
                                    <span class="badge bg-label-secondary badge-soft">{{ $perm->unit_key }}</span>
                                @endif
                            @endforeach
                        @else
                            <span class="badge bg-label-secondary badge-soft text-muted fst-italic"><i class="bx bx-x-circle me-1"></i>Belum ada akses</span>
                        @endif
                    </div>
                </td>
                <td>
                    @if($stf->status === 'aktif')
                        <div class="d-flex align-items-center">
                            <span class="status-dot active"></span>
                            <span class="text-success fw-bold ms-1" style="font-size: 13px;">Aktif</span>
                        </div>
                    @else
                        <div class="d-flex align-items-center">
                            <span class="status-dot inactive"></span>
                            <span class="text-danger fw-bold ms-1" style="font-size: 13px;">Nonaktif</span>
                        </div>
                    @endif
                </td>
                <td class="text-end pe-4">
                    <div class="d-inline-flex gap-2">
                        <a href="{{ route('admin.staff.edit', $stf->id) }}" class="btn-icon-soft edit text-muted" data-bs-toggle="tooltip" title="Edit Staf">
                            <i class="bx bx-edit-alt"></i>
                        </a>
                        <form action="{{ route('admin.staff.toggle-status', $stf->id) }}" method="POST" onsubmit="return confirm('Yakin ingin mengubah status akun ini?')">
                            @csrf
                            @method('PUT')
                            <button type="submit" class="btn-icon-soft {{ $stf->status === 'aktif' ? 'suspend' : 'activate' }} text-muted" data-bs-toggle="tooltip" title="{{ $stf->status === 'aktif' ? 'Blokir Akses' : 'Buka Blokir' }}">
                                <i class="bx {{ $stf->status === 'aktif' ? 'bx-block' : 'bx-check-circle' }}"></i>
                            </button>
                        </form>
                        <form action="{{ route('admin.staff.destroy', $stf->id) }}" method="POST" onsubmit="return confirm('PERINGATAN: Yakin ingin menghapus akun staf ini secara permanen? Data yang dihapus tidak bisa dikembalikan.')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn-icon-soft delete text-muted" data-bs-toggle="tooltip" title="Hapus Permanen">
                                <i class="bx bx-trash"></i>
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="text-center py-5">
                    <div class="d-flex flex-column align-items-center justify-content-center py-4">
                        <div class="bg-label-primary p-4 rounded-circle mb-3">
                            <i class="bx bx-user-x fs-1 text-primary"></i>
                        </div>
                        <h6 class="fw-bold mb-1">Belum Ada Staf Layanan</h6>
                        <p class="text-muted mb-3 text-wrap" style="max-width: 400px; text-align: center;">Sistem belum memiliki data staf terdaftar atau tidak ada kecocokan pencarian.</p>
                        <a href="{{ route('admin.staff.create') }}" class="btn btn-sm btn-primary">
                            <i class="bx bx-plus me-1"></i> Tambah Sekarang
                        </a>
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
