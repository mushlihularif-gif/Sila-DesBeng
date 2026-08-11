@extends('admin.layouts.admin')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row justify-content-center align-items-center" style="min-height: 60vh;">
        <div class="col-12 col-md-8 col-lg-6">
            <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
                <div class="card-header bg-primary text-white text-center py-4">
                    <i class="bx bx-shield-quarter bx-lg mb-2"></i>
                    <h4 class="text-white mb-0 fw-bold">Layanan Telah Didelegasikan</h4>
                </div>
                <div class="card-body text-center p-5">
                    <div class="mb-4">
                        <img src="{{ asset('Admin/img/illustrations/man-with-laptop-light.png') }}" alt="Delegasi Staf" class="img-fluid" style="max-height: 150px;">
                    </div>
                    
                    <h5 class="mb-3 text-dark fw-semibold">
                        Layanan <span class="text-primary">{{ $serviceName }}</span> saat ini dikelola oleh Staf:
                    </h5>
                    
                    <div class="bg-light rounded-3 p-4 mb-4 text-start border d-inline-block text-center w-100">
                        <h4 class="text-dark fw-bold mb-1"><i class="bx bx-user-circle text-primary me-2"></i>{{ $staff->name }}</h4>
                        <p class="text-muted mb-0"><i class="bx bx-envelope me-2"></i>{{ $staff->email }}</p>
                    </div>

                    <p class="text-muted mb-4">
                        Sebagai <strong>{{ Auth::user()->role === 'super_admin' ? 'Super Admin' : (Auth::user()->role === 'admin_kecamatan' ? 'Admin Kecamatan' : 'Admin Desa') }}</strong>, Anda bertindak sebagai <strong>Supervisor</strong>. Anda tetap dapat masuk, memantau, dan jika dalam keadaan darurat (staf kewalahan/berhalangan), Anda dapat mengambil alih operasional harian.
                    </p>

                    <div class="d-grid gap-2">
                        <a href="{{ $bypassUrl }}" class="btn btn-primary btn-lg fw-semibold d-flex align-items-center justify-content-center shadow-sm">
                            <i class="bx bx-log-in-circle me-2"></i> Tetap Masuk & Pantau Layanan
                        </a>
                        <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary">
                            <i class="bx bx-arrow-back me-2"></i> Kembali ke Dashboard
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
