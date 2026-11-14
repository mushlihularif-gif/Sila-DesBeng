@extends('errors.layout')

@section('title', '403 - Akses Ditolak')
@section('code', '403')
@section('badge-icon', 'bx bx-shield-x')
@section('image', asset('Admin/img/halamantidakberfungsi/SegiDaerah1.png'))

@section('heading', 'Akses Terbatas / Ditolak')

@section('message')
    {{ ($exception ?? null)?->getMessage() ?: 'Mohon maaf, Anda tidak memiliki hak akses atau kewenangan untuk membuka halaman ini. Pastikan Anda telah masuk menggunakan akun yang sesuai wilayah tugas Anda.' }}
@endsection

@section('actions')
    <x-home-link href="{{ route('beranda') }}" />
    <a href="{{ route('login') }}" class="btn btn-secondary">
        <i class="bx bx-log-in"></i>
        <span>Masuk ke Akun</span>
    </a>
@endsection
