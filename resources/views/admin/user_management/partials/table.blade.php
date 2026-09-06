<style>
    /* Table Modern (Consistent with Admin Staff & Sneat Standard) */
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
        font-size: 1.1rem;
    }
    .btn-icon-soft.view { background: #e7e7ff; color: #696cff; }
    .btn-icon-soft.view:hover { background: #696cff; color: #fff; }
    .btn-icon-soft.suspend { background: #ffe0db; color: #ff3e1d; }
    .btn-icon-soft.suspend:hover { background: #ff3e1d; color: #fff; }
    .btn-icon-soft.activate { background: #e8fadf; color: #71dd37; }
    .btn-icon-soft.activate:hover { background: #71dd37; color: #fff; }
    .btn-icon-soft.kick { background: #fff2d6; color: #ffab00; }
    .btn-icon-soft.kick:hover { background: #ffab00; color: #fff; }

    /* Soft Badge */
    .badge-soft {
        padding: 0.35em 0.7em;
        font-weight: 600;
        border-radius: 6px;
        font-size: 0.75rem;
    }

    /* Fixed 38px Avatar Wrapper (Immune to external CSS conflicts) */
    .user-avatar-wrap {
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
    .user-avatar-wrap img {
        width: 38px !important;
        height: 38px !important;
        border-radius: 50%;
        object-fit: cover;
    }

    /* Mobile Card Box Container */
    .user-mobile-list {
        background: #f5f6f8;
        padding: 0.85rem 0.75rem;
        overflow-x: hidden;
        box-sizing: border-box;
        width: 100%;
    }

    /* Individual User Card Box (Strictly Bounded) */
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
    .user-card-box .user-avatar-wrap {
        width: 42px !important;
        height: 42px !important;
        min-width: 42px !important;
        max-width: 42px !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    .user-card-box .user-avatar-wrap img {
        width: 42px !important;
        height: 42px !important;
        border-radius: 50%;
        object-fit: cover;
    }
    .user-card-box .user-avatar-wrap .avatar-initial {
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
    .user-card-name:hover {
        color: #696cff;
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

    /* Middle: Region Badge */
    .user-card-region {
        background-color: #f8f9fa;
        border: 1px solid #edf0f5;
        border-radius: 7px;
        padding: 0.35rem 0.65rem;
        margin-bottom: 0.65rem;
        display: flex;
        align-items: center;
        gap: 0.4rem;
        font-size: 0.775rem;
        color: #566a7f;
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
        gap: 8px; /* Clean, comfortable gap so buttons never touch */
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
    .btn-icon-mobile.view {
        background: #e7e7ff !important;
        color: #696cff !important;
    }
    .btn-icon-mobile.suspend {
        background: #ffe0db !important;
        color: #ff3e1d !important;
    }
    .btn-icon-mobile.activate {
        background: #e8fadf !important;
        color: #71dd37 !important;
    }
    .btn-icon-mobile.kick {
        background: #fff2d6 !important;
        color: #ffab00 !important;
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

@if($users->isEmpty())
    <div class="text-center py-5 px-3">
        <div class="avatar avatar-xl bg-label-secondary rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 56px; height: 56px;">
            <i class="bx bx-user-x fs-2 text-muted"></i>
        </div>
        <h6 class="fw-bold text-dark mb-1">Pengguna Tidak Ditemukan</h6>
        <p class="text-muted small mb-0">Coba ubah kata kunci pencarian atau bersihkan filter wilayah.</p>
    </div>
@else

    <!-- ==================== DESKTOP & TABLET VIEW (>= 768px) ==================== -->
    <div class="table-responsive w-100 px-0 d-none d-md-block">
        <table class="table table-modern table-hover align-middle mb-0 text-nowrap">
            <thead>
                <tr>
                    <th class="py-3 ps-4">PENGGUNA</th>
                    <th class="py-3">ASAL WILAYAH</th>
                    <th class="py-3">KONTAK</th>
                    <th class="py-3 text-center">ROLE</th>
                    <th class="py-3 text-center">STATUS</th>
                    <th class="py-3 text-end pe-4">AKSI</th>
                </tr>
            </thead>
            <tbody class="table-border-bottom-0">
                @foreach($users as $user)
                @php
                    $avatarUrl = null;
                    if (!empty($user->avatar)) {
                        $avatarUrl = route('media.avatar', ['filename' => basename($user->avatar)]);
                    } elseif ($user->file && !empty($user->file->path)) {
                        $avatarUrl = route('media.avatar', ['filename' => basename($user->file->path)]);
                    }

                    $roleLabel = 'User';
                    $roleBadgeClass = 'bg-label-info';
                    if (in_array($user->role, ['super_admin', 'admin'])) {
                        $roleLabel = 'Admin';
                        $roleBadgeClass = 'bg-label-danger';
                    } elseif ($user->role === 'admin_kecamatan') {
                        $roleLabel = 'Kecamatan';
                        $roleBadgeClass = 'bg-label-warning';
                    } elseif ($user->role === 'admin_desa') {
                        $roleLabel = 'Desa';
                        $roleBadgeClass = 'bg-label-primary';
                    } elseif ($user->role === 'admin_rw') {
                        $roleLabel = 'RW';
                        $roleBadgeClass = 'bg-label-secondary';
                    } elseif ($user->role === 'admin_rt') {
                        $roleLabel = 'RT';
                        $roleBadgeClass = 'bg-label-dark';
                    }
                @endphp
                <tr>
                    <td class="ps-4">
                        <div class="d-flex align-items-center">
                            <div class="user-avatar-wrap me-3 flex-shrink-0">
                                @if($avatarUrl)
                                    <img src="{{ $avatarUrl }}" alt="Avatar" class="shadow-xs">
                                @else
                                    <span class="avatar-initial rounded-circle bg-label-primary shadow-xs fw-bold notranslate" translate="no" style="width: 38px; height: 38px; font-size: 13px; display: inline-flex; align-items: center; justify-content: center;">
                                        {{ strtoupper(substr($user->name, 0, 2)) }}
                                    </span>
                                @endif
                            </div>
                            <div class="min-w-0">
                                <a href="{{ route('admin.manajemen-pengguna.show', $user->id) }}" class="fw-bold text-dark text-decoration-none d-block text-truncate" style="max-width: 180px;">
                                    {{ $user->name }}
                                </a>
                                <small class="text-muted text-truncate d-block" style="max-width: 180px;">@ {{ $user->username }}</small>
                            </div>
                        </div>
                    </td>
                    <td>
                        <div class="d-flex flex-column" style="max-width: 220px;">
                            @if($user->region)
                                <span class="text-dark fw-semibold text-truncate d-block" style="font-size: 0.85rem;" title="{{ $user->region->name }}">
                                    <i class="bx bx-map-pin me-1 text-primary"></i>{{ $user->region->name }}
                                </span>
                                @if($user->region->parent)
                                    <small class="text-muted text-truncate d-block" style="font-size: 0.75rem;" title="{{ $user->region->full_path }}">
                                        {{ $user->region->parent->name }}
                                    </small>
                                @endif
                            @else
                                <span class="text-muted fst-italic small"><i class="bx bx-map me-1"></i>Belum diatur</span>
                            @endif
                        </div>
                    </td>
                    <td>
                        <div class="d-flex flex-column" style="max-width: 200px;">
                            <span class="text-dark text-truncate d-block" style="font-size: 0.85rem;" title="{{ $user->email }}">
                                <i class="bx bx-envelope me-1 text-muted"></i>{{ $user->email }}
                            </span>
                            @if(!empty($user->phone) && $user->phone !== '-')
                                <small class="text-muted text-truncate d-block" style="font-size: 0.75rem;">
                                    <i class="bx bx-phone me-1 text-muted"></i>{{ $user->phone }}
                                </small>
                            @endif
                        </div>
                    </td>
                    <td class="text-center">
                        <span class="badge {{ $roleBadgeClass }} badge-soft">{{ $roleLabel }}</span>
                    </td>
                    <td class="text-center">
                        @if($user->status === 'aktif')
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
                        <div class="d-inline-flex gap-2 align-items-center justify-content-end">
                            <a href="{{ route('admin.manajemen-pengguna.show', $user->id) }}" class="btn-icon-soft view" data-bs-toggle="tooltip" title="Lihat Detail">
                                <i class="bx bx-show"></i>
                            </a>
                            <form action="{{ route('admin.manajemen-pengguna.toggle-status', $user->id) }}" method="POST" onsubmit="return confirm('Yakin ingin mengubah status akun ini?')" class="d-inline m-0 p-0">
                                @csrf
                                @method('PUT')
                                <button type="submit" class="btn-icon-soft {{ $user->status === 'aktif' ? 'suspend' : 'activate' }}" data-bs-toggle="tooltip" title="{{ $user->status === 'aktif' ? 'Nonaktifkan Akun' : 'Aktifkan Akun' }}">
                                    <i class="bx {{ $user->status === 'aktif' ? 'bx-block' : 'bx-check-circle' }}"></i>
                                </button>
                            </form>
                            @if($user->role === 'user')
                            <form action="{{ route('admin.manajemen-pengguna.kick', $user->id) }}" method="POST" onsubmit="return confirm('Yakin ingin mengeluarkan pengguna ini dari wilayah Anda?')" class="d-inline m-0 p-0">
                                @csrf
                                @method('PUT')
                                <button type="submit" class="btn-icon-soft kick" data-bs-toggle="tooltip" title="Keluarkan dari Wilayah">
                                    <i class="bx bx-user-minus"></i>
                                </button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- ==================== MOBILE CARD LIST (< 768px) ==================== -->
    <div class="user-mobile-list d-block d-md-none">
        @foreach($users as $user)
        @php
            $avatarUrl = null;
            if (!empty($user->avatar)) {
                $avatarUrl = route('media.avatar', ['filename' => basename($user->avatar)]);
            } elseif ($user->file && !empty($user->file->path)) {
                $avatarUrl = route('media.avatar', ['filename' => basename($user->file->path)]);
            }

            $roleLabel = 'User';
            $roleBadgeClass = 'bg-label-info';
            if (in_array($user->role, ['super_admin', 'admin'])) {
                $roleLabel = 'Admin';
                $roleBadgeClass = 'bg-label-danger';
            } elseif ($user->role === 'admin_kecamatan') {
                $roleLabel = 'Kecamatan';
                $roleBadgeClass = 'bg-label-warning';
            } elseif ($user->role === 'admin_desa') {
                $roleLabel = 'Desa';
                $roleBadgeClass = 'bg-label-primary';
            } elseif ($user->role === 'admin_rw') {
                $roleLabel = 'RW';
                $roleBadgeClass = 'bg-label-secondary';
            } elseif ($user->role === 'admin_rt') {
                $roleLabel = 'RT';
                $roleBadgeClass = 'bg-label-dark';
            }
        @endphp
        <div class="user-card-box">
            <!-- Top Section: Avatar + Name + Username on Left, Role Badge on Right -->
            <!-- Top Section: Avatar + Name on Left, Role Badge on Right (Strictly Bounded) -->
            <div class="user-card-header-row">
                <div class="user-card-identity">
                    <a href="{{ route('admin.manajemen-pengguna.show', $user->id) }}" class="user-avatar-wrap text-decoration-none">
                        @if($avatarUrl)
                            <img src="{{ $avatarUrl }}" alt="Avatar" class="shadow-xs">
                        @else
                            <span class="avatar-initial rounded-circle bg-label-primary shadow-xs fw-bold notranslate" translate="no">
                                {{ strtoupper(substr($user->name, 0, 2)) }}
                            </span>
                        @endif
                    </a>
                    <div class="user-card-text">
                        <a href="{{ route('admin.manajemen-pengguna.show', $user->id) }}" class="user-card-name text-decoration-none" title="{{ $user->name }}">
                            {{ $user->name }}
                        </a>
                        <span class="user-card-username">
                            @ {{ $user->username }}
                        </span>
                    </div>
                </div>

                <!-- Role Badge (Fixed width, never pushed out) -->
                <span class="badge {{ $roleBadgeClass }} badge-soft py-1 px-2 user-card-badge" style="font-size: 0.72rem; font-weight: 700; letter-spacing: 0.3px;">
                    {{ $roleLabel }}
                </span>
            </div>

            <!-- Middle Section: Wilayah (if available) -->
            @if($user->region)
            <div class="user-card-region">
                <i class="bx bx-map-pin text-primary flex-shrink-0" style="font-size: 0.9rem;"></i>
                <span class="text-truncate fw-medium" style="min-width: 0; flex: 1 1 0%; overflow: hidden;">
                    {{ $user->region->name }}
                    @if($user->region->parent)
                        <span class="text-muted fw-normal">({{ $user->region->parent->name }})</span>
                    @endif
                </span>
            </div>
            @endif

            <!-- Bottom Section: Status on Left, Action Buttons on Right -->
            <div class="user-card-footer">
                <!-- Status Pill -->
                <div>
                    @if($user->status === 'aktif')
                        <span class="badge bg-label-success badge-soft py-1 px-2.5 d-inline-flex align-items-center" style="font-size: 0.75rem; font-weight: 600;">
                            <span class="status-dot active" style="width: 6px; height: 6px; margin-right: 5px;"></span> Aktif
                        </span>
                    @else
                        <span class="badge bg-label-danger badge-soft py-1 px-2.5 d-inline-flex align-items-center" style="font-size: 0.75rem; font-weight: 600;">
                            <span class="status-dot inactive" style="width: 6px; height: 6px; margin-right: 5px;"></span> Nonaktif
                        </span>
                    @endif
                </div>

                <!-- Action Buttons (Direct, Touch-friendly) -->
                <div class="user-card-actions">
                    <a href="{{ route('admin.manajemen-pengguna.show', $user->id) }}" class="btn-icon-mobile view" data-bs-toggle="tooltip" title="Lihat Detail">
                        <i class="bx bx-show"></i>
                    </a>
                    <form action="{{ route('admin.manajemen-pengguna.toggle-status', $user->id) }}" method="POST" onsubmit="return confirm('Yakin ingin mengubah status akun ini?')" class="d-inline m-0 p-0">
                        @csrf
                        @method('PUT')
                        <button type="submit" class="btn-icon-mobile {{ $user->status === 'aktif' ? 'suspend' : 'activate' }}" data-bs-toggle="tooltip" title="{{ $user->status === 'aktif' ? 'Nonaktifkan Akun' : 'Aktifkan Akun' }}">
                            <i class="bx {{ $user->status === 'aktif' ? 'bx-block' : 'bx-check-circle' }}"></i>
                        </button>
                    </form>
                    @if($user->role === 'user')
                    <form action="{{ route('admin.manajemen-pengguna.kick', $user->id) }}" method="POST" onsubmit="return confirm('Yakin ingin mengeluarkan pengguna ini dari wilayah Anda?')" class="d-inline m-0 p-0">
                        @csrf
                        @method('PUT')
                        <button type="submit" class="btn-icon-mobile kick" data-bs-toggle="tooltip" title="Keluarkan dari Wilayah">
                            <i class="bx bx-user-minus"></i>
                        </button>
                    </form>
                    @endif
                </div>
            </div>
        </div>
        @endforeach
    </div>

@endif

<!-- ==================== PAGINATION FOOTER ==================== -->
<div class="card-footer bg-white border-top py-2.5 px-3 px-sm-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
    <small class="text-muted" style="font-size: 0.75rem;">
        Menampilkan {{ $users->firstItem() ?? 0 }} - {{ $users->lastItem() ?? 0 }} dari {{ $users->total() }} pengguna
    </small>
    <div class="ms-auto">
        {{ $users->appends(request()->except('page'))->links() }}
    </div>
</div>

