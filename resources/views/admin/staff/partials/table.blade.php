<style>
    /* Table Modern (Consistent with Sneat Standard & Manajemen Pengguna) */
    .table-modern th {
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.8px;
        font-weight: 700;
        color: #a1acb8 !important;
        border-bottom: 2px solid #f0f2f4;
        background-color: #fafbfc;
    }
    .table-modern td {
        vertical-align: middle;
        padding: 0.85rem 1.25rem;
        border-bottom: 1px solid #f0f2f4;
        transition: all 0.2s;
    }
    .table-modern tbody tr:hover {
        background-color: #f8f9fa;
    }

    /* Status Dot with Pulse Animation */
    .status-dot {
        width: 7px;
        height: 7px;
        border-radius: 50%;
        display: inline-block;
        margin-right: 5px;
    }
    .status-dot.active {
        background-color: #71dd37;
        box-shadow: 0 0 0 2px rgba(113, 221, 55, 0.25);
        animation: pulse-dot 2s infinite;
    }
    .status-dot.inactive {
        background-color: #ff3e1d;
        box-shadow: 0 0 0 2px rgba(255, 62, 29, 0.25);
    }
    @keyframes pulse-dot {
        0% { box-shadow: 0 0 0 0 rgba(113, 221, 55, 0.4); }
        70% { box-shadow: 0 0 0 5px rgba(113, 221, 55, 0); }
        100% { box-shadow: 0 0 0 0 rgba(113, 221, 55, 0); }
    }

    /* Soft Icon Buttons (Desktop) */
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

    /* Soft Badge */
    .badge-soft {
        padding: 0.35em 0.7em;
        font-weight: 600;
        border-radius: 6px;
        font-size: 0.75rem;
    }

    /* Fixed Avatar Wrapper */
    .staff-avatar-wrap {
        width: 38px !important;
        height: 38px !important;
        min-width: 38px !important;
        max-width: 38px !important;
        flex-shrink: 0;
        position: relative;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
    .staff-avatar-wrap .avatar-initial {
        width: 38px !important;
        height: 38px !important;
        font-size: 13px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    /* Mobile Card Box Container */
    .user-mobile-list {
        background: #f5f6f8;
        padding: 0.85rem 0.75rem;
        overflow-x: hidden;
        box-sizing: border-box;
        width: 100%;
    }

    /* Individual Card Box (Strictly Bounded) */
    .user-card-box {
        background: #ffffff;
        border: 1px solid #e7eaf0;
        border-radius: 12px;
        padding: 0.85rem 0.95rem;
        margin-bottom: 0.75rem;
        box-shadow: 0 1px 4px rgba(67, 89, 113, 0.05);
        overflow: hidden !important;
        box-sizing: border-box !important;
        width: 100% !important;
        max-width: 100% !important;
        transition: transform 0.15s ease, box-shadow 0.15s ease;
    }
    .user-card-box:last-child {
        margin-bottom: 0 !important;
    }
    .user-card-box:active {
        transform: scale(0.99);
        box-shadow: 0 1px 2px rgba(67, 89, 113, 0.08);
    }

    /* Card Top Header Row */
    .user-card-header-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.5rem;
        width: 100%;
        min-width: 0;
        overflow: hidden;
        margin-bottom: 0.5rem;
    }
    .user-card-identity {
        display: flex;
        align-items: center;
        gap: 0.65rem;
        flex: 1 1 0%;
        min-width: 0;
        overflow: hidden;
    }
    .user-card-box .staff-avatar-wrap {
        width: 42px !important;
        height: 42px !important;
        min-width: 42px !important;
        max-width: 42px !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    .user-card-box .staff-avatar-wrap .avatar-initial {
        width: 42px !important;
        height: 42px !important;
        font-size: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .user-card-text {
        flex: 1 1 0%;
        min-width: 0;
        overflow: hidden;
    }
    .user-card-name {
        font-size: 0.925rem;
        font-weight: 700;
        color: #384554;
        line-height: 1.3;
        display: block;
        width: 100%;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    .user-card-username {
        font-size: 0.775rem;
        color: #8592a3;
        line-height: 1.2;
        display: block;
        width: 100%;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    .user-card-badge {
        flex-shrink: 0;
        margin-left: auto;
    }

    /* Middle: Unit Layanan Badges */
    .user-card-units {
        background-color: #f8f9fa;
        border: 1px solid #edf0f5;
        border-radius: 8px;
        padding: 0.5rem 0.65rem;
        margin-bottom: 0.65rem;
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        min-width: 0;
        overflow: hidden;
        width: 100%;
        box-sizing: border-box;
    }

    /* Bottom: Footer with Status & Action Buttons */
    .user-card-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding-top: 0.75rem;
        margin-top: 0.65rem;
        border-top: 1px solid #f2f4f7;
    }

    /* Action Buttons Container (Generous Spacing) */
    .user-card-actions {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-shrink: 0;
    }
    .user-card-actions form {
        margin: 0;
        padding: 0;
        display: inline-flex;
    }

    /* Action Buttons (Mobile) */
    .btn-icon-mobile {
        width: 36px;
        height: 36px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 9px;
        font-size: 1.15rem;
        flex-shrink: 0;
        border: none !important;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06);
        transition: transform 0.15s ease, opacity 0.15s ease, box-shadow 0.15s ease;
    }
    .btn-icon-mobile:active {
        transform: scale(0.92);
        box-shadow: none;
        opacity: 0.85;
    }
    .btn-icon-mobile.edit {
        background: #e7e7ff !important;
        color: #696cff !important;
    }
    .btn-icon-mobile.suspend {
        background: #fff2d6 !important;
        color: #ffab00 !important;
    }
    .btn-icon-mobile.activate {
        background: #e8fadf !important;
        color: #71dd37 !important;
    }
    .btn-icon-mobile.delete {
        background: #ffe0db !important;
        color: #ff3e1d !important;
    }

    @media (max-width: 767.98px) {
        .pagination {
            margin-bottom: 0 !important;
        }
        .page-link {
            padding: 0.35rem 0.65rem !important;
            font-size: 0.8rem !important;
        }
    }
</style>

@if($staffUsers->isEmpty())
    <div class="text-center py-5 px-3">
        <div class="avatar avatar-xl bg-label-primary rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 56px; height: 56px;">
            <i class="bx bx-user-x fs-2 text-primary"></i>
        </div>
        <h6 class="fw-bold text-dark mb-1">Belum Ada Staf Layanan</h6>
        <p class="text-muted small mb-3">Sistem belum memiliki data staf terdaftar atau tidak ada kecocokan pencarian.</p>
        <a href="{{ route('admin.staff.create') }}" class="btn btn-sm btn-primary">
            <i class="bx bx-plus me-1"></i> Tambah Sekarang
        </a>
    </div>
@else

    <!-- ==================== DESKTOP & TABLET VIEW (>= 768px) ==================== -->
    <div class="table-responsive w-100 px-0 d-none d-md-block">
        <table class="table table-modern table-hover align-middle mb-0 text-nowrap">
            <thead class="bg-light bg-opacity-50">
                <tr>
                    <th class="py-3 ps-4">PROFIL STAF</th>
                    <th class="py-3">KONTAK</th>
                    <th class="py-3">UNIT LAYANAN</th>
                    <th class="py-3 text-center">STATUS</th>
                    <th class="py-3 text-end pe-4">AKSI</th>
                </tr>
            </thead>
            <tbody class="table-border-bottom-0">
                @foreach($staffUsers as $stf)
                <tr>
                    <td class="ps-4">
                        <div class="d-flex align-items-center">
                            <div class="staff-avatar-wrap me-3">
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
                    <td class="text-center">
                        @if($stf->status === 'aktif')
                            <span class="badge bg-label-success badge-soft d-inline-flex align-items-center">
                                <span class="status-dot active"></span> Aktif
                            </span>
                        @else
                            <span class="badge bg-label-danger badge-soft d-inline-flex align-items-center">
                                <span class="status-dot inactive"></span> Nonaktif
                            </span>
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
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- ==================== MOBILE CARD LIST (< 768px) ==================== -->
    <div class="user-mobile-list d-block d-md-none">
        @foreach($staffUsers as $stf)
        <div class="user-card-box">
            <!-- Top Section: Avatar + Name on Left, Role Badge on Right -->
            <div class="user-card-header-row">
                <div class="user-card-identity">
                    <div class="staff-avatar-wrap">
                        <span class="avatar-initial rounded-circle bg-label-primary shadow-xs fw-bold notranslate" translate="no">
                            {{ strtoupper(substr($stf->name, 0, 2)) }}
                        </span>
                    </div>
                    <div class="user-card-text">
                        <span class="user-card-name" title="{{ $stf->name }}">
                            {{ $stf->name }}
                        </span>
                        <span class="user-card-username">
                            <i class="bx bx-envelope me-1"></i>{{ $stf->email }}
                        </span>
                    </div>
                </div>

                <!-- Staf Role Badge -->
                <span class="badge bg-label-primary badge-soft py-1 px-2 user-card-badge" style="font-size: 0.72rem; font-weight: 700; letter-spacing: 0.3px;">
                    STAF
                </span>
            </div>

            <!-- Middle Section: Unit Layanan Badges -->
            <div class="user-card-units">
                @if($stf->staffPermissions->count() > 0)
                    @foreach($stf->staffPermissions as $perm)
                        @if(isset($availableUnits[$perm->unit_key]))
                            <span class="badge bg-white text-primary border badge-soft"><i class="bx bx-check-shield me-1"></i>{{ $availableUnits[$perm->unit_key] }}</span>
                        @else
                            <span class="badge bg-white text-secondary border badge-soft">{{ $perm->unit_key }}</span>
                        @endif
                    @endforeach
                @else
                    <span class="text-muted small fst-italic"><i class="bx bx-x-circle me-1"></i>Belum ada akses unit</span>
                @endif
            </div>

            <!-- Bottom Section: Status on Left, Action Buttons on Right -->
            <div class="user-card-footer">
                <!-- Status Pill -->
                <div>
                    @if($stf->status === 'aktif')
                        <span class="badge bg-label-success badge-soft py-1 px-2.5 d-inline-flex align-items-center" style="font-size: 0.75rem; font-weight: 600;">
                            <span class="status-dot active" style="width: 6px; height: 6px; margin-right: 5px;"></span> Aktif
                        </span>
                    @else
                        <span class="badge bg-label-danger badge-soft py-1 px-2.5 d-inline-flex align-items-center" style="font-size: 0.75rem; font-weight: 600;">
                            <span class="status-dot inactive" style="width: 6px; height: 6px; margin-right: 5px;"></span> Nonaktif
                        </span>
                    @endif
                </div>

                <!-- Action Buttons (36x36px with 8px gap) -->
                <div class="user-card-actions">
                    <a href="{{ route('admin.staff.edit', $stf->id) }}" class="btn-icon-mobile edit" data-bs-toggle="tooltip" title="Edit Staf">
                        <i class="bx bx-edit-alt"></i>
                    </a>
                    <form action="{{ route('admin.staff.toggle-status', $stf->id) }}" method="POST" onsubmit="return confirm('Yakin ingin mengubah status akun ini?')">
                        @csrf
                        @method('PUT')
                        <button type="submit" class="btn-icon-mobile {{ $stf->status === 'aktif' ? 'suspend' : 'activate' }}" data-bs-toggle="tooltip" title="{{ $stf->status === 'aktif' ? 'Blokir Akses' : 'Buka Blokir' }}">
                            <i class="bx {{ $stf->status === 'aktif' ? 'bx-block' : 'bx-check-circle' }}"></i>
                        </button>
                    </form>
                    <form action="{{ route('admin.staff.destroy', $stf->id) }}" method="POST" onsubmit="return confirm('PERINGATAN: Yakin ingin menghapus akun staf ini secara permanen? Data yang dihapus tidak bisa dikembalikan.')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn-icon-mobile delete" data-bs-toggle="tooltip" title="Hapus Permanen">
                            <i class="bx bx-trash"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
        @endforeach
    </div>

@endif

<!-- ==================== PAGINATION FOOTER ==================== -->
<div class="card-footer bg-white border-top py-2.5 px-3 px-sm-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
    <small class="text-muted" style="font-size: 0.75rem;">
        Menampilkan {{ $staffUsers->firstItem() ?? 0 }} - {{ $staffUsers->lastItem() ?? 0 }} dari {{ $staffUsers->total() }} staf
    </small>
    <div class="ms-auto">
        {{ $staffUsers->appends(request()->except('page'))->links() }}
    </div>
</div>
