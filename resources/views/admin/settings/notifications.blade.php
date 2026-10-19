@extends('admin.layouts.admin')
@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4">Notifikasi Akun</h4>
    <div class="card"><div class="card-body">
        <h5>Pusat notifikasi SegiDaerah</h5>
        <p>Pantau pemberitahuan layanan dan aktivitas akun melalui pusat notifikasi.</p>
        <a href="{{ route('admin.notifications.index') }}" class="btn btn-primary"><i class="bx bx-bell me-1"></i>Buka Notifikasi</a>
        <a href="{{ route('admin.profile') }}" class="btn btn-outline-secondary">Profil Akun</a>
    </div></div>
</div>
@endsection
