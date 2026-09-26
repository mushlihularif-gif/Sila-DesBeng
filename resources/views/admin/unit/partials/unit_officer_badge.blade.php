@php
    $unitKey = $unitKey ?? '';
    $officers = \App\Models\User::getStaffForUnit($unitKey);
@endphp

@if($officers->isNotEmpty())
    <div class="d-inline-flex align-items-center bg-white border border-primary border-opacity-25 rounded-pill px-3 py-1 shadow-sm">
        <i class="bx bx-user-check text-primary me-2 fs-5"></i>
        <span class="small text-muted me-1">Dikelola oleh:</span>
        <span class="small fw-bold text-primary">{{ $officers->pluck('name')->join(', ') }}</span>
        <span class="badge bg-label-primary rounded-pill ms-2" style="font-size: 0.65rem;">Staf Operasional</span>
    </div>
@else
    <div class="d-inline-flex align-items-center bg-white border border-secondary border-opacity-25 rounded-pill px-3 py-1 shadow-sm">
        <i class="bx bx-shield-quarter text-secondary me-2 fs-5"></i>
        <span class="small text-muted me-1">Pengelola:</span>
        <span class="small fw-semibold text-dark">Dikelola langsung oleh Admin Desa</span>
    </div>
@endif
