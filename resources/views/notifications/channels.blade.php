@extends('layouts.user')
@section('title','Pengaturan Pemberitahuan — SegiDaerah')
@section('page')
@include('partials.abstract-bg')
<main class="relative z-10 max-w-5xl mx-auto w-full px-4 pt-36 pb-16">
    <a href="{{ route('content.index') }}" class="segi-back">Kembali ke Daftar Kiriman</a>
    @include('notifications.preferences')
</main>
@endsection
