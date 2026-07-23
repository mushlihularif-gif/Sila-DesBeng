@extends('admin.layouts.admin')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Unit Layanan /</span> Ambulans Darurat</h4>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="nav-align-top mb-4">
        <ul class="nav nav-tabs" role="tablist">
            <li class="nav-item">
                <button type="button" class="nav-link active" role="tab" data-bs-toggle="tab" data-bs-target="#navs-top-data" aria-controls="navs-top-data" aria-selected="true">Data Ambulans & Supir</button>
            </li>
            <li class="nav-item">
                <button type="button" class="nav-link" role="tab" data-bs-toggle="tab" data-bs-target="#navs-top-pengaturan" aria-controls="navs-top-pengaturan" aria-selected="false">Pengaturan & SOP</button>
            </li>
        </ul>
        <div class="tab-content">
            <!-- TAB: Data Ambulans -->
            <div class="tab-pane fade show active" id="navs-top-data" role="tabpanel">
                <div class="d-flex justify-content-between mb-3">
                    <h5>Daftar Armada Ambulans</h5>
                    <a href="{{ route('admin.unit.ambulans.create') }}" class="btn btn-primary"><i class="bx bx-plus"></i> Tambah Ambulans</a>
                </div>
                
                <div class="table-responsive text-nowrap">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Nama Armada</th>
                                <th>Plat Nomor</th>
                                <th>Nama Supir</th>
                                <th>Kontak Darurat</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($ambulansList as $amb)
                            <tr>
                                <td>{{ $amb->nama_mobil }}</td>
                                <td>{{ str_replace('Plat: ', '', $amb->deskripsi) }}</td>
                                <td>{{ $amb->nama_supir }}</td>
                                <td>{{ $amb->kontak_supir }}</td>
                                <td>
                                    <a href="{{ route('admin.unit.ambulans.edit', $amb->id) }}" class="btn btn-sm btn-warning"><i class="bx bx-edit"></i> Edit</a>
                                    <form action="{{ route('admin.unit.ambulans.destroy', $amb->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin hapus?');">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-danger"><i class="bx bx-trash"></i> Hapus</button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center">Belum ada data ambulans</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">
                    {{ $ambulansList->links() }}
                </div>
            </div>

            <!-- TAB: Pengaturan SOP -->
            <div class="tab-pane fade" id="navs-top-pengaturan" role="tabpanel">
                <form action="{{ route('admin.unit.ambulans.sop.update') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label text-danger fw-bold">Nomor WhatsApp Darurat Desa (Halo Ambulans)</label>
                        <input type="text" class="form-control" name="kontak_ambulans" value="{{ $regionSettings->settings['kontak_ambulans'] ?? '' }}" placeholder="Contoh: 08123456789">
                        <small class="text-muted">Nomor ini akan dihubungi warga saat mereka menekan tombol darurat.</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">SOP Penggunaan Ambulans & Rujukan RS</label>
                        <textarea class="form-control" name="sop_ambulans" rows="8" placeholder="Tuliskan aturan, syarat penggratisan, dsb...">{{ $regionSettings->settings['sop_ambulans'] ?? '' }}</textarea>
                    </div>

                    <button type="submit" class="btn btn-primary">Simpan Pengaturan</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
