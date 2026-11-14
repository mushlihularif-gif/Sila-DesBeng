@extends('errors.layout')

@section('title', '429 - Terlalu Banyak Permintaan')
@section('code', '429')
@section('badge-icon', 'bx bx-tachometer')
@section('image', asset('Admin/img/halamantidakberfungsi/SegiDaerah1.png'))

@section('heading', 'Terlalu Banyak Permintaan')

@section('message')
    {{ $limitMessage ?? 'Terlalu banyak permintaan dari koneksi ini. Tunggu sebentar sebelum mencoba lagi.' }}
@endsection

@section('actions')
    @if(request()->isMethod('GET'))
    <button type="button" onclick="window.location.reload()" class="btn btn-primary">
        <i class="bx bx-refresh"></i>
        <span>Coba Lagi</span>
    </button>
    @endif
    <x-home-link href="{{ route('beranda') }}" />
@endsection
