@extends('errors.layout')

@section('title', '503 - Sistem Dalam Pemeliharaan')
@section('code', '503')
@section('badge-icon', 'bx bx-wrench')
@section('image', asset('Admin/img/halamantidakberfungsi/SegiDaerah2.png'))

@section('heading', 'Sedang Dalam Pemeliharaan')

@section('message')
    SegiDaerah sementara belum dapat melayani permintaan karena pemeliharaan atau kendala layanan. Silakan kembali beberapa saat lagi.
@endsection

@section('actions')
    @if(request()->isMethod('GET'))
    <button type="button" onclick="window.location.reload()" class="btn btn-primary">
        <i class="bx bx-refresh"></i>
        <span>Periksa Status / Coba Lagi</span>
    </button>
    @endif
    <x-home-link href="{{ route('beranda') }}" />
@endsection
