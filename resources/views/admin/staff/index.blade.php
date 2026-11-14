@extends('admin.layouts.admin')
@section('content')
<div class="container-xxl py-4">
<h3>Staf Layanan Desa</h3><p>Bagikan tugas kepada petugas gas, penyewaan, atau fasilitas umum. Staf hanya dapat membuka layanan yang Anda centang di desa ini.</p>
@if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
<div class="card p-4 mb-4"><h5>Tambah Petugas</h5>
<form method="post" action="{{ route('admin.staff.store') }}">@csrf
<div class="row g-3"><label class="col-md-6">Nama petugas<input class="form-control" name="name" required maxlength="255" value="{{ old('name') }}"></label>
<label class="col-md-6">Email<input class="form-control" type="email" name="email" required value="{{ old('email') }}"></label>
<label class="col-md-6">Username untuk masuk<input class="form-control" name="username" required maxlength="50" value="{{ old('username') }}" autocomplete="off"></label>
<label class="col-md-6">Kata sandi (minimal 12 karakter)<input class="form-control" name="password" type="password" required minlength="12" autocomplete="new-password"></label>
<label class="col-md-6">Ulangi kata sandi<input class="form-control" name="password_confirmation" type="password" required minlength="12" autocomplete="new-password"></label></div>
<fieldset class="mt-3"><legend class="fs-6">Layanan yang boleh dikelola</legend>@foreach($units as $key=>$label)<label class="d-inline-block me-4 mb-2"><input type="checkbox" class="form-check-input me-1" name="units[]" value="{{ $key }}" @checked(in_array($key, old('units', [])))> {{ $label }}</label>@endforeach</fieldset>
<p class="text-muted small">Staf dapat mengelola produk, pesanan, bukti pembayaran, dan chat pada layanan pilihannya. Rekening desa, verifikasi warga, akun admin, serta pengaturan sistem tetap dipegang admin.</p>
<button class="btn btn-primary mt-2">Buat Akun Staf</button></form></div>
<h4>Petugas Terdaftar</h4>
@forelse($staff as $person)<details class="card p-4 mb-3"><summary class="fw-bold">{{ $person->name }} <span class="badge {{ $person->hasSupportedRole() ? 'bg-success' : 'bg-secondary' }}">{{ $person->hasSupportedRole() ? 'Aktif' : 'Nonaktif / perlu izin ulang' }}</span></summary>
<p class="mt-3">Username: {{ $person->username }} &middot; {{ $person->email }}</p>
<form method="post" action="{{ route('admin.staff.update', $person->id) }}">@csrf @method('PUT')
<label class="d-block mb-3">Nama petugas<input class="form-control" name="name" required value="{{ $person->name }}"></label>
<fieldset><legend class="fs-6">Izin layanan</legend>@foreach($units as $key=>$label)<label class="d-inline-block me-4 mb-2"><input type="checkbox" class="form-check-input me-1" name="units[]" value="{{ $key }}" @checked($person->staffPermissions->contains('unit_key', $key))> {{ $label }}</label>@endforeach</fieldset>
<label class="d-block mt-3">Status akun<select name="enabled" class="form-select"><option value="0" @selected(!$person->hasSupportedRole())>Nonaktif</option><option value="1" @selected($person->hasSupportedRole())>Aktif</option></select></label>
<div class="row mt-3"><label class="col-md-6">Kata sandi baru (opsional)<input type="password" name="password" minlength="12" class="form-control" autocomplete="new-password"></label><label class="col-md-6">Ulangi kata sandi baru<input type="password" name="password_confirmation" class="form-control" autocomplete="new-password"></label></div>
<p class="text-muted small mt-3">Perubahan izin akan mengakhiri sesi staf. Riwayat pekerjaan tetap tersimpan.</p><button class="btn btn-primary">Simpan Izin dan Status</button></form></details>
@empty<div class="alert alert-info">Belum ada staf. Tambahkan petugas dan pilih layanan yang akan dibantunya.</div>@endforelse
</div>
@endsection
