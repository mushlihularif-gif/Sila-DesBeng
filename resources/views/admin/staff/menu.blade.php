<li class="menu-item"><a class="menu-link" href="{{ route('admin.dashboard') }}"><i class="menu-icon bx bx-home"></i>Tugas Saya</a></li>
@php
    $staffLinks = ['gas'=>'admin.unit.penjualan_gas.index','sewa_alat'=>'admin.unit.penyewaan.index','sewa_mobil'=>'admin.unit.mobil.index','fasilitas_umum'=>'admin.unit.fasilitas_umum.index'];
@endphp
@foreach(\App\Support\StaffAccess::UNITS as $key=>$label)@if(auth()->user()->hasUnitPermission($key))<li class="menu-item"><a class="menu-link" href="{{ route($staffLinks[$key]) }}"><i class="menu-icon bx bx-grid-alt"></i>{{ $label }}</a></li>@endif
@endforeach
<li class="menu-item"><a class="menu-link" href="{{ route('admin.aktivitas.permintaan-pengajuan.index') }}"><i class="menu-icon bx bx-receipt"></i>Pesanan Saya</a></li>
<li class="menu-item"><a class="menu-link" href="{{ route('admin.aktivitas.bukti-transaksi.index') }}"><i class="menu-icon bx bx-wallet"></i>Bukti Pembayaran</a></li>
<li class="menu-item"><a class="menu-link" href="{{ route('admin.profile') }}"><i class="menu-icon bx bx-user"></i>Profil Saya</a></li>
