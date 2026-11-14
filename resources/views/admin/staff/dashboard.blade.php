@extends('admin.layouts.admin')
@section('content')
<div class="container-xxl py-4"><h3>Tugas Layanan Saya</h3><p>{{ auth()->user()->region?->name }}. Pilih layanan yang ingin dikelola.</p>
<div class="row g-4">
@php
    $links = ['gas'=>'admin.unit.penjualan_gas.index','sewa_alat'=>'admin.unit.penyewaan.index','sewa_mobil'=>'admin.unit.mobil.index','fasilitas_umum'=>'admin.unit.fasilitas_umum.index'];
@endphp
@foreach(\App\Support\StaffAccess::UNITS as $key=>$label)@if(auth()->user()->hasUnitPermission($key))<div class="col-md-6"><div class="card p-4"><h5>{{ $label }}</h5><p>Kelola katalog, ketersediaan, dan chat warga.</p><a href="{{ route($links[$key]) }}" class="btn btn-primary">Buka {{ $label }}</a></div></div>@endif
@endforeach
</div><div class="mt-4 d-flex gap-3"><a class="btn btn-outline-primary" href="{{ route('admin.aktivitas.permintaan-pengajuan.index') }}">Pesanan Layanan Saya</a><a class="btn btn-outline-primary" href="{{ route('admin.aktivitas.bukti-transaksi.index') }}">Periksa Pembayaran</a></div></div>
@endsection
