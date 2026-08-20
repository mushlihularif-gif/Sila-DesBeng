@extends('admin.layouts.admin')

@section('title', 'Integrasi Payment Gateway')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Sistem Platform /</span> Integrasi Payment Gateway</h4>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible" role="alert">
            <h6 class="alert-heading d-flex align-items-center fw-bold mb-1"><i class="bx bx-error-circle me-2"></i>Terjadi Kesalahan!</h6>
            <ul class="mb-0 ps-3">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row">
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-1"><i class="bx bx-bolt-circle me-2 text-primary"></i>Kredensial Payment Gateway Platform</h5>
                        <small class="text-muted"><i class="bx bx-info-circle"></i> Satu kredensial dipakai sistem untuk seluruh transaksi dari semua desa/kecamatan.</small>
                    </div>
                    <span class="badge bg-label-{{ $settings->gateway_is_production ? 'success' : 'warning' }} rounded-pill">
                        {{ $settings->gateway_is_production ? 'Production' : 'Sandbox' }}
                    </span>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.sistem-platform.gateway.update') }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row g-4">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Penyedia Gateway</label>
                                <select name="gateway_provider" class="form-select">
                                    <option value="">- Belum dipilih -</option>
                                    <option value="xendit" {{ old('gateway_provider', $settings->gateway_provider) === 'xendit' ? 'selected' : '' }}>Xendit for Platforms</option>
                                    <option value="oy" {{ old('gateway_provider', $settings->gateway_provider) === 'oy' ? 'selected' : '' }}>OY! Indonesia</option>
                                    <option value="midtrans" {{ old('gateway_provider', $settings->gateway_provider) === 'midtrans' ? 'selected' : '' }}>Midtrans</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold d-block">Mode</label>
                                <div class="form-check form-switch mt-2">
                                    <input class="form-check-input" type="checkbox" name="gateway_is_production" id="gateway_is_production" value="1" {{ old('gateway_is_production', $settings->gateway_is_production) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="gateway_is_production">Aktifkan Mode Production</label>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Secret / Server Key</label>
                                <input type="password" name="gateway_secret_key" class="form-control" placeholder="{{ $settings->gateway_secret_key ? '•••••••• (tersimpan, kosongkan jika tidak diganti)' : 'Belum diisi' }}" autocomplete="off">
                                <small class="text-muted">Dienkripsi otomatis. Kosongkan kalau tidak ingin mengubah key yang sudah ada.</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Public / Client Key</label>
                                <input type="password" name="gateway_public_key" class="form-control" placeholder="{{ $settings->gateway_public_key ? '•••••••• (tersimpan, kosongkan jika tidak diganti)' : 'Belum diisi' }}" autocomplete="off">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Fee Platform (%)</label>
                                <div class="input-group">
                                    <input type="number" step="0.01" min="0" max="100" name="platform_fee_percentage" class="form-control" value="{{ old('platform_fee_percentage', $settings->platform_fee_percentage ?? 0) }}" required>
                                    <span class="input-group-text">%</span>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 pt-3 border-top text-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="bx bx-save me-1"></i> Simpan Pengaturan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="d-flex align-items-center bg-light rounded-3 p-3 shadow-sm mb-3">
                <div class="avatar flex-shrink-0 me-3">
                    <span class="avatar-initial rounded bg-label-primary">
                        <i class="bx bx-percentage fs-4"></i>
                    </span>
                </div>
                <div>
                    <h6 class="mb-0 fw-bold text-dark">Fee Platform Saat Ini</h6>
                    <small class="text-muted fs-5 fw-bold text-primary">{{ $settings->platform_fee_percentage ?? 0 }}%</small>
                </div>
            </div>

            <div class="alert alert-info mb-0">
                <i class="bx bx-info-circle me-1"></i>
                Fee saat ini <strong>0%</strong> — biaya server/hosting ditanggung APBD lewat Diskominfotik. BUM Desa tidak pernah melihat kredensial ini, mereka cukup mendaftarkan rekening bank tujuan pencairan.
            </div>
        </div>
    </div>
</div>
@endsection
