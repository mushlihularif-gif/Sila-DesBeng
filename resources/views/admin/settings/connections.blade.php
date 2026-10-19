@extends('admin.layouts.admin')
@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4">Koneksi Akun</h4>
    <div class="card"><div class="card-body">
        <h5>Akun Google</h5>
        <p>{{ $user->google_id ? 'Akun SegiDaerah Anda terhubung dengan Google.' : 'Akun SegiDaerah Anda belum terhubung dengan Google.' }}</p>
        <a href="{{ route('admin.profile') }}" class="btn btn-primary">Kembali ke Profil Akun</a>
    </div></div>
</div>
@endsection
