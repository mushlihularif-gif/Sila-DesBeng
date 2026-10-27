@extends('admin.layouts.admin')
@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <a href="{{ route('admin.unit.fasilitas_umum.index', ['tab' => 'kendaraan']) }}" class="btn btn-outline-secondary mb-3"><i class="bx bx-arrow-back me-1"></i>Kembali ke Kendaraan Operasional</a>
    <h4 class="fw-bold mb-4">{{ $kendaraan->exists ? 'Ubah' : 'Tambah' }} Kendaraan Operasional</h4>
    <form method="POST" enctype="multipart/form-data" action="{{ $kendaraan->exists ? route('admin.unit.kendaraan_operasional.update', $kendaraan->id) : route('admin.unit.kendaraan_operasional.store') }}" class="card">
        @csrf
        @if($kendaraan->exists) @method('PUT') @endif
        <div class="card-body">
            @if($errors->any())
                <div class="alert alert-danger" role="alert"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
            @endif
            <div class="row g-3">
                <div class="col-md-8"><label for="nama_mobil" class="form-label">Nama kendaraan</label><input id="nama_mobil" name="nama_mobil" class="form-control" required maxlength="255" value="{{ old('nama_mobil', $kendaraan->nama_mobil) }}"></div>
                <div class="col-md-4"><label for="nomor_plat" class="form-label">Nomor polisi</label><input id="nomor_plat" name="nomor_plat" class="form-control" maxlength="20" value="{{ old('nomor_plat', $kendaraan->plat_nomor) }}"></div>
                <div class="col-12"><label for="deskripsi" class="form-label">Keterangan kendaraan</label><textarea id="deskripsi" name="deskripsi" class="form-control" rows="3" maxlength="5000">{{ old('deskripsi', $kendaraan->deskripsi) }}</textarea></div>
                @foreach(['foto' => 'Foto utama', 'foto_2' => 'Foto tambahan 1', 'foto_3' => 'Foto tambahan 2'] as $field => $label)
                    <div class="col-md-4">
                        <label for="{{ $field }}" class="form-label">{{ $label }}</label>
                        @if($kendaraan->$field)
                            <img src="{{ asset('storage/'.$kendaraan->$field) }}" alt="{{ $label }} kendaraan" class="d-block rounded mb-2" style="width:100%;height:160px;object-fit:contain">
                            <label class="form-check mb-2"><input type="checkbox" class="form-check-input" name="delete_{{ $field }}" value="1" @checked(old('delete_'.$field))><span class="form-check-label">Hapus foto ini</span></label>
                        @endif
                        <input id="{{ $field }}" name="{{ $field }}" type="file" accept="image/jpeg,image/png,image/webp" class="form-control">
                        <small class="text-muted">JPG, PNG, atau WebP. Maksimal 8 MB.</small>
                    </div>
                @endforeach
                <div class="col-12">
                    <h5 class="mt-3">Supir penanggung jawab</h5>
                    @php($selectedDrivers = old('supir_ids', $kendaraan->exists ? $kendaraan->supirs->modelKeys() : []))
                    @forelse($supirs as $supir)
                        <label class="form-check mb-2"><input type="checkbox" class="form-check-input" name="supir_ids[]" value="{{ $supir->id }}" @checked(in_array($supir->id, $selectedDrivers))><span class="form-check-label">{{ $supir->nama }} — {{ $supir->kontak ?: 'Kontak belum diisi' }}</span></label>
                    @empty
                        <p class="text-muted">Belum ada supir terdaftar di wilayah ini. Tambahkan melalui menu Kelola Staf Layanan.</p>
                    @endforelse
                </div>
            </div>
            <button type="submit" class="btn btn-primary mt-4"><i class="bx bx-save me-1"></i>Simpan kendaraan</button>
        </div>
    </form>
</div>
@endsection
