@php
    $staffUser = auth()->user();
    $staffUnitsMeta = [
        'gas' => [
            'route' => 'admin.unit.penjualan_gas.index',
            'icon'  => 'bxs-gas-pump',
            'color' => 'text-danger',
            'active' => request()->is('admin/unit/gas*'),
        ],
        'sewa_alat' => [
            'route' => 'admin.unit.penyewaan.index',
            'icon'  => 'bx-wrench',
            'color' => 'text-warning',
            'active' => request()->is('admin/unit/penyewaan*'),
        ],
        'sewa_mobil' => [
            'route' => 'admin.unit.mobil.index',
            'icon'  => 'bx-car',
            'color' => 'text-info',
            'active' => request()->is('admin/unit/mobil*'),
        ],
        'fasilitas_umum' => [
            'route' => 'admin.unit.fasilitas_umum.index',
            'icon'  => 'bx-building-house',
            'color' => 'text-success',
            'active' => request()->is('admin/unit/fasilitas_umum*'),
        ],
        'kabar_informasi' => [
            'route' => 'admin.announcements.index',
            'icon'  => 'bx-news',
            'color' => 'text-primary',
            'active' => request()->routeIs('admin.announcements.*') || request()->routeIs('content.*'),
        ],
    ];
    $assignedStaffUnits = collect(\App\Support\StaffAccess::UNITS)
        ->filter(fn ($label, $key) => $staffUser && $staffUser->hasUnitPermission($key));
    $hasCommerceUnits = $staffUser && (
        $staffUser->hasUnitPermission('gas')
        || $staffUser->hasUnitPermission('sewa_alat')
        || $staffUser->hasUnitPermission('sewa_mobil')
        || $staffUser->hasUnitPermission('fasilitas_umum')
    );
@endphp

<!-- UTAMA -->
<li class="menu-header small text-uppercase">
    <span class="menu-header-text">Utama</span>
</li>
<li class="menu-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
    <a class="menu-link" href="{{ route('admin.dashboard') }}">
        <i class="menu-icon tf-icons bx bx-home-circle text-primary"></i>
        <div>Tugas Saya</div>
    </a>
</li>

<!-- UNIT LAYANAN DITUGASKAN -->
@if($assignedStaffUnits->isNotEmpty())
<li class="menu-header small text-uppercase">
    <span class="menu-header-text">Unit Layanan Anda</span>
</li>
@foreach($assignedStaffUnits as $key => $label)
    @php $meta = $staffUnitsMeta[$key]; @endphp
    <li class="menu-item {{ $meta['active'] ? 'active' : '' }}">
        <a class="menu-link" href="{{ route($meta['route']) }}">
            <i class="menu-icon tf-icons bx {{ $meta['icon'] }} {{ $meta['color'] }}"></i>
            <div>{{ $label }}</div>
        </a>
    </li>
@endforeach
@endif

@if($hasCommerceUnits)
<!-- OPERASIONAL HARIAN -->
<li class="menu-header small text-uppercase">
    <span class="menu-header-text">Operasional Harian</span>
</li>
<li class="menu-item {{ request()->is('admin/aktivitas/permintaan-pengajuan*') ? 'active' : '' }}">
    <a class="menu-link" href="{{ route('admin.aktivitas.permintaan-pengajuan.index') }}">
        <i class="menu-icon tf-icons bx bx-calendar-check text-warning"></i>
        <div>Pesanan &amp; Booking</div>
    </a>
</li>
<li class="menu-item {{ request()->is('admin/aktivitas/bukti-transaksi*') ? 'active' : '' }}">
    <a class="menu-link" href="{{ route('admin.aktivitas.bukti-transaksi.index') }}">
        <i class="menu-icon tf-icons bx bx-check-shield text-success"></i>
        <div>Bukti Pembayaran</div>
    </a>
</li>
@if($staffUser && ($staffUser->hasUnitPermission('sewa_mobil') || $staffUser->hasUnitPermission('fasilitas_umum')))
<li class="menu-item {{ request()->is('admin/unit/supir*') ? 'active' : '' }}">
    <a class="menu-link" href="{{ route('supir.index') }}">
        <i class="menu-icon tf-icons bx bx-user-check text-info"></i>
        <div>Sopir &amp; Pengurus</div>
    </a>
</li>
@endif
@endif

<!-- AKUN -->
<li class="menu-header small text-uppercase">
    <span class="menu-header-text">Akun</span>
</li>
<li class="menu-item {{ request()->routeIs('admin.profile*') ? 'active' : '' }}">
    <a class="menu-link" href="{{ route('admin.profile') }}">
        <i class="menu-icon tf-icons bx bx-user-circle text-secondary"></i>
        <div>Profil Saya</div>
    </a>
</li>
