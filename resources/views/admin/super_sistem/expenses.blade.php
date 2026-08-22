@extends('admin.layouts.admin')

@section('title', 'Biaya Server & Domain')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Sistem Platform /</span> Biaya Server, Domain &amp; Hosting</h4>

    <div class="alert alert-info d-flex align-items-center mb-4">
        <i class="bx bx-info-circle me-2 fs-5"></i>
        <div>Halaman ini murni pencatatan &amp; pengingat jatuh tempo. Biaya tetap dibayar lewat APBD melalui Diskominfotik — <strong>tidak</strong> ditarik dari saldo BUM Desa atau fee platform manapun.</div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @php
        $badgeMap = [
            'lunas' => ['success', 'Lunas'],
            'mendekati_jatuh_tempo' => ['warning', 'Mendekati Jatuh Tempo'],
            'terlambat' => ['danger', 'Terlambat'],
            'aman' => ['secondary', 'Aman'],
        ];
    @endphp

    <div class="row">
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-1"><i class="bx bx-receipt me-2 text-primary"></i>Daftar Tagihan Berlangganan</h5>
                        <small class="text-muted">{{ $expenses->count() }} item tercatat</small>
                    </div>
                </div>
                <div class="table-responsive text-nowrap">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th>Kategori</th>
                                <th class="text-end">Nominal</th>
                                <th>Siklus</th>
                                <th>Jatuh Tempo</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="table-border-bottom-0">
                            @forelse($expenses as $expense)
                                @php [$badgeColor, $badgeLabel] = $badgeMap[$expense->due_badge]; @endphp
                                <tr>
                                    <td class="fw-semibold">{{ $expense->item_name }}</td>
                                    <td class="text-capitalize">{{ str_replace('_', ' ', $expense->category) }}</td>
                                    <td class="text-end">Rp {{ number_format($expense->amount, 0, ',', '.') }}</td>
                                    <td class="text-capitalize">{{ $expense->billing_cycle }}</td>
                                    <td>{{ $expense->due_date->format('d M Y') }}</td>
                                    <td><span class="badge bg-label-{{ $badgeColor }} rounded-pill">{{ $badgeLabel }}</span></td>
                                    <td>
                                        @if($expense->status !== 'lunas' || $expense->billing_cycle !== 'sekali_bayar')
                                        <form action="{{ route('admin.sistem-platform.expenses.mark-paid', $expense) }}" method="POST" class="d-inline">
                                            @csrf @method('PUT')
                                            <button type="submit" class="btn btn-sm btn-outline-success" title="Tandai Lunas &amp; Perpanjang">
                                                <i class="bx bx-check"></i>
                                            </button>
                                        </form>
                                        @endif
                                        <form action="{{ route('admin.sistem-platform.expenses.destroy', $expense) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus item ini?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus">
                                                <i class="bx bx-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="text-center text-muted py-4">Belum ada item biaya operasional dicatat.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-1"><i class="bx bx-plus-circle me-2 text-primary"></i>Tambah Item</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.sistem-platform.expenses.store') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Nama Item</label>
                            <input type="text" name="item_name" class="form-control" placeholder="Contoh: Domain siladesbeng.id" required value="{{ old('item_name') }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Kategori</label>
                            <select name="category" class="form-select" required>
                                <option value="domain">Domain</option>
                                <option value="hosting">Hosting / VPS</option>
                                <option value="ssl">SSL</option>
                                <option value="api_service">Layanan API Pihak Ketiga</option>
                                <option value="lainnya">Lainnya</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Nominal (Rp)</label>
                            <input type="number" step="0.01" min="0" name="amount" class="form-control" required value="{{ old('amount') }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Siklus Tagihan</label>
                            <select name="billing_cycle" class="form-select" required>
                                <option value="tahunan">Tahunan</option>
                                <option value="bulanan">Bulanan</option>
                                <option value="sekali_bayar">Sekali Bayar</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Tanggal Jatuh Tempo</label>
                            <input type="date" name="due_date" class="form-control" required value="{{ old('due_date') }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Catatan (opsional)</label>
                            <textarea name="notes" class="form-control" rows="2">{{ old('notes') }}</textarea>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bx bx-save me-1"></i> Simpan
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
