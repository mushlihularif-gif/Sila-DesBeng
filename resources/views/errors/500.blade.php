@extends('errors.layout')

@section('title', '500 - Kendala Server')
@section('code', '500')
@section('badge-icon', 'bx bx-server')
@section('image', asset('Admin/img/halamantidakberfungsi/SegiDaerah2.png'))

@section('heading', 'Terjadi Kendala Teknis :(')

@section('message')
    Mohon maaf, SegiDaerah belum dapat menyelesaikan permintaan Anda. Silakan coba kembali beberapa saat lagi. Jika kendala berulang, hubungi pengelola layanan.
@endsection

@section('actions')
    @if(request()->isMethod('GET'))
    <button type="button" onclick="window.location.reload()" class="btn btn-primary">
        <i class="bx bx-refresh"></i>
        <span>Muat Ulang Halaman</span>
    </button>
    @endif
    <x-home-link href="{{ route('beranda') }}" />
@endsection
