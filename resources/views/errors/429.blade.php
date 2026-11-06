@extends('errors.layout')

@section('title', '429 - Terlalu Banyak Permintaan')
@section('code', '429')
@section('badge-icon', 'bx bx-tachometer')
@section('image', asset('Admin/img/halamantidakberfungsi/SegiDaerah1.png'))

@section('heading', 'Terlalu Banyak Permintaan')

@section('message')
    Sistem mendeteksi terlalu banyak permintaan dalam waktu singkat dari koneksi Anda. Mohon tunggu beberapa saat sebelum mencoba kembali demi kelancaran bersama.
@endsection

@section('actions')
    @if(request()->isMethod('GET'))
    <button type="button" onclick="window.location.reload()" class="btn btn-primary">
        <i class="bx bx-refresh"></i>
        <span>Coba Lagi</span>
    </button>
    @endif
    <a href="{{ route('beranda') }}" class="btn btn-secondary">
        <i class="bx bx-home-circle"></i>
        <span>Kembali ke Beranda</span>
    </a>
@endsection
