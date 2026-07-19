@extends('admin.layouts.app')

@section('title', 'Manajemen Verifikasi KYC')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Verifikasi Identitas (KYC)</h1>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Daftar Pengajuan KYC</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" width="100%" cellspacing="0">
                    <thead class="thead-light">
                        <tr>
                            <th>No</th>
                            <th>Nama Pengguna</th>
                            <th>Tanggal Pengajuan</th>
                            <th>NIK KTP (OCR)</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($verifications as $kyc)
                        <tr>
                            <td>{{ $loop->iteration + $verifications->firstItem() - 1 }}</td>
                            <td>
                                <strong>{{ $kyc->user->name }}</strong><br>
                                <small class="text-muted">{{ $kyc->user->email }}</small>
                            </td>
                            <td>{{ $kyc->created_at->format('d M Y, H:i') }}</td>
                            <td>{{ $kyc->nik_from_ocr ?? '-' }}</td>
                            <td>
                                @if($kyc->status === 'pending')
                                    <span class="badge badge-warning">Menunggu</span>
                                @elseif($kyc->status === 'approved')
                                    <span class="badge badge-success">Disetujui</span>
                                @else
                                    <span class="badge badge-danger">Ditolak</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('admin.kyc.show', $kyc->id) }}" class="btn btn-sm btn-info">
                                    <i class="fas fa-eye"></i> Detail
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center">Belum ada data pengajuan KYC.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <div class="mt-3">
                {{ $verifications->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
