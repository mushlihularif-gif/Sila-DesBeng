@extends('errors.layout')

@section('title', '404 - Halaman Tidak Ditemukan')
@section('code', '404')
@section('badge-icon', 'bx bx-search-alt')
@section('image', asset('Admin/img/halamantidakberfungsi/SegiDaerah2.png'))

@section('heading', 'Halaman Tidak Ditemukan :(')

@section('message')
    Oops! Alamat web yang Anda cari tidak ditemukan, mungkin salah ketik, sudah dihapus, atau sedang dipindahkan. Jangan cemas, mari kembali ke halaman utama SegiDaerah.
@endsection

@section('actions')
    <x-home-link href="{{ route('beranda') }}" />
    <button type="button" onclick="history.back()" class="btn btn-secondary">
        <i class="bx bx-arrow-back"></i>
        <span>Halaman Sebelumnya</span>
    </button>
@endsection
