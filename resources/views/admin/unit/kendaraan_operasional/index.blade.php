@extends('admin.layouts.admin')
@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <a href="{{ route('admin.unit.fasilitas_umum.index') }}" class="btn btn-outline-secondary mb-3"><i class="bx bx-arrow-back me-1"></i>Kembali ke Fasilitas Umum</a>
    <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
        <div><h4 class="fw-bold mb-2">Kendaraan Operasional</h4><p class="text-muted mb-0">Kelola inventaris kendaraan operasional dan supir wilayah Anda.</p></div>
        <a href="{{ route('admin.unit.kendaraan_operasional.create') }}" class="btn btn-primary"><i class="bx bx-plus me-1"></i>Tambah Kendaraan</a>
    </div>
    <form method="GET" class="d-flex gap-2 mb-4"><label class="flex-grow-1"><span class="visually-hidden">Cari nama kendaraan</span><input name="search" value="{{ $search }}" maxlength="100" placeholder="Cari nama kendaraan" class="form-control"></label><button class="btn btn-outline-primary">Cari</button></form>
    <div class="row g-3">
        @forelse($kendaraans as $kendaraan)
        <div class="col-md-6 col-xl-4"><article class="card h-100"><div class="card-body">
            <h5>{{ $kendaraan->nama_mobil }}</h5><p class="text-muted">{{ $kendaraan->plat_nomor ?: 'Nomor polisi belum diisi' }}</p>
            <p>Supir: {{ $kendaraan->supirs->pluck('nama')->join(', ') ?: 'Belum ditugaskan' }}</p>
            <div class="d-flex flex-wrap gap-2"><a class="btn btn-outline-primary" href="{{ route('admin.unit.kendaraan_operasional.show', $kendaraan->id) }}">Lihat Detail</a><a class="btn btn-primary" href="{{ route('admin.unit.kendaraan_operasional.edit', $kendaraan->id) }}">Ubah Kendaraan</a></div>
        </div></article></div>
        @empty
        <div class="col-12"><div class="card card-body text-center text-muted">Belum ada kendaraan yang sesuai.</div></div>
        @endforelse
    </div>
    <div class="mt-4">{{ $kendaraans->links() }}</div>
</div>
@endsection
