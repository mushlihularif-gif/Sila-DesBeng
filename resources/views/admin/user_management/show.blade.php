@extends('admin.layouts.admin')

@section('title', 'Detail Pengguna - ' . $user->name)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">Detail Pengguna: {{ $user->name }}</h4>
                    <a href="{{ route('admin.manajemen-pengguna.index') }}" class="btn btn-sm btn-outline-secondary">
                        <i class="bx bx-arrow-back"></i> Kembali
                    </a>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <h5>Informasi Profil</h5>
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>Nama Pengguna:</strong> {{ $user->username }}</p>
                                    <p><strong>Nama Lengkap:</strong> {{ $user->name }}</p>
                                    <p><strong>Jenis Kelamin:</strong> {{ ucfirst($user->gender) ?? 'Tidak Ditentukan' }}</p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Email:</strong> {{ $user->email }}</p>
                                    <p><strong>Nomor Telepon:</strong> {{ $user->phone ?? '-' }}</p>
                                    <p><strong>Status Akun:</strong>
                                        <span class="badge {{ $user->status === 'aktif' ? 'badge-success' : 'badge-danger' }}">
                                            {{ ucfirst($user->status) }}
                                        </span>
                                    </p>
                                </div>
                            </div>
                            <p><strong>Alamat:</strong> {{ $user->address ?? 'Alamat belum diisi.' }}</p>
                        </div>
                        <div class="col-md-4">
                            <h5>Aksi</h5>
                            <form action="{{ route('admin.manajemen-pengguna.toggle-status', $user->id) }}" method="POST" onsubmit="return confirm('Yakin ingin mengubah status akun ini?')">
                                @csrf
                                @method('PUT')
                                <button type="submit" class="btn btn-{{ $user->status === 'aktif' ? 'warning' : 'info' }} btn-block">
                                    <i class="bx bx-block"></i> {{ $user->status === 'aktif' ? 'Nonaktifkan Akun' : 'Aktifkan Akun' }}
                                </button>
                            </form>
                        </div>
                    </div>

                    <hr>

                    <h5>Riwayat Transaksi</h5>

                    <!-- Transaksi Penyewaan -->
                    @if($user->rentalTransactions->count() > 0)
                    <h6>Penyewaan Alat:</h6>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Tanggal</th>
                                    <th>Alat</th>
                                    <th>Status</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($user->rentalTransactions as $trans)
                                <tr>
                                    <td>{{ $trans->id }}</td>
                                    <td>{{ $trans->created_at->format('d M Y H:i') }}</td>
                                    <td>{{ $trans->item_name ?? $trans->barang->nama_barang ?? 'N/A' }}</td>
                                    <td><span class="badge bg-label-secondary">{{ $trans->status ?: 'N/A' }}</span></td>
                                    <td>Rp {{ number_format($trans->total_amount ?? 0, 0, ',', '.') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                        <p class="text-muted">Tidak ada riwayat transaksi penyewaan.</p>
                    @endif

                    <!-- Transaksi Gas -->
                    @if($user->gasTransactions->count() > 0)
                    <h6>Penjualan Gas:</h6>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Tanggal</th>
                                    <th>Jenis Gas</th>
                                    <th>Jumlah</th>
                                    <th>Status</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($user->gasTransactions as $trans)
                                <tr>
                                    <td>{{ $trans->id }}</td>
                                    <td>{{ $trans->created_at->format('d M Y H:i') }}</td>
                                    <td>{{ $trans->item_name ?? $trans->gas->jenis_gas ?? 'N/A' }}</td>
                                    <td>{{ $trans->quantity ?? 0 }} Tabung</td>
                                    <td><span class="badge bg-label-secondary">{{ $trans->status ?: 'N/A' }}</span></td>
                                    <td>Rp {{ number_format(($trans->price * $trans->quantity) ?? 0, 0, ',', '.') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                        <p class="text-muted">Tidak ada riwayat transaksi pembelian gas.</p>
                    @endif

                </div>
            </div>
        </div>
    </div>
</div>
@endsection