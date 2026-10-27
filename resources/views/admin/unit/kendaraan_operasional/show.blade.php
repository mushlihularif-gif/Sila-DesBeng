@extends('admin.layouts.admin')
@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <a href="{{ route('admin.unit.fasilitas_umum.index', ['tab' => 'kendaraan']) }}" class="btn btn-outline-secondary mb-3"><i class="bx bx-arrow-back me-1"></i>Kembali ke Kendaraan Operasional</a>
    <div class="card"><div class="card-body">
        <div class="d-flex flex-wrap justify-content-between gap-2 mb-3"><h4 class="fw-bold">{{ $kendaraan->nama_mobil }}</h4><a class="btn btn-warning" href="{{ route('admin.unit.kendaraan_operasional.edit', $kendaraan->id) }}"><i class="bx bx-edit me-1"></i>Ubah kendaraan</a></div>
        <div class="row g-3 mb-4">
            @foreach(['foto', 'foto_2', 'foto_3'] as $field)
                @if($kendaraan->$field)<div class="col-md-4"><img src="{{ asset('storage/'.$kendaraan->$field) }}" alt="Foto {{ $kendaraan->nama_mobil }}" class="rounded" style="width:100%;height:240px;object-fit:contain"></div>@endif
            @endforeach
        </div>
        <dl class="row"><dt class="col-sm-3">Nomor polisi</dt><dd class="col-sm-9">{{ $kendaraan->plat_nomor ?: 'Belum diisi' }}</dd><dt class="col-sm-3">Wilayah</dt><dd class="col-sm-9">{{ $kendaraan->region?->name }}</dd><dt class="col-sm-3">Keterangan</dt><dd class="col-sm-9" style="white-space:pre-line">{{ $kendaraan->deskripsi ?: 'Belum diisi' }}</dd></dl>
        <h5>Supir penanggung jawab</h5>
        @forelse($kendaraan->supirs as $supir)<p>{{ $supir->nama }} — {{ $supir->kontak ?: 'Kontak belum diisi' }}</p>@empty<p class="text-muted">Belum ada supir ditugaskan.</p>@endforelse
    </div></div>
</div>
@endsection
