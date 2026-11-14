@extends('errors.layout')

@section('title', '419 - Sesi Kedaluwarsa')
@section('code', '419')
@section('badge-icon', 'bx bx-time-five')
@section('image', asset('Admin/img/halamantidakberfungsi/SegiDaerah1.png'))

@section('heading', 'Sesi Halaman Berakhir')

@section('message')
    Sesi atau token keamanan halaman ini sudah tidak berlaku. Buka kembali halaman melalui Beranda atau masuk kembali, lalu ulangi tindakan Anda.
@endsection

@section('actions')
    <a href="{{ route('login') }}" class="btn btn-primary">
        <i class="bx bx-log-in"></i>
        <span>Buka Halaman Masuk</span>
    </a>
    <x-home-link href="{{ route('beranda') }}" />
@endsection
