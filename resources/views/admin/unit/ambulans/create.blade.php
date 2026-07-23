@extends('admin.layouts.admin')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4">
        <span class="text-muted fw-light"><a href="{{ route('admin.unit.ambulans.index') }}">Ambulans Darurat</a> /</span> Tambah Armada
    </h4>

    <div class="card mb-4">
        <div class="card-body">
            <form action="{{ route('admin.unit.ambulans.store') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Nama Armada (Misal: Ambulans Siaga 1)</label>
                    <input type="text" class="form-control" name="nama_mobil" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Plat Nomor</label>
                    <input type="text" class="form-control" name="nomor_plat" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Nama Supir</label>
                    <input type="text" class="form-control" name="nama_supir" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Nomor WhatsApp Supir</label>
                    <input type="text" class="form-control" name="kontak_supir" required>
                </div>
                
                <button type="submit" class="btn btn-primary">Simpan</button>
                <a href="{{ route('admin.unit.ambulans.index') }}" class="btn btn-secondary">Batal</a>
            </form>
        </div>
    </div>
</div>
@endsection
