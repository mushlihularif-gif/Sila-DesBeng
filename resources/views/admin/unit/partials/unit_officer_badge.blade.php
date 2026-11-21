@php
    $unitKey = $unitKey ?? '';
    $officers = \App\Models\User::getStaffForUnit($unitKey, auth()->user()->region_id);
@endphp

@if($officers->isNotEmpty())
    <div class="d-inline-flex flex-wrap align-items-center gap-1.5 bg-white border border-primary border-opacity-25 px-2.5 py-1.5 shadow-sm mw-100 unit-officer-badge" style="border-radius: 14px; line-height: 1.3;">
        <span class="d-inline-flex align-items-center gap-1 min-w-0">
            <i class="bx bx-user-check text-primary fs-6 flex-shrink-0"></i>
            <span class="small text-muted">Dikelola oleh:</span>
            <span class="small fw-bold text-primary text-break">{{ $officers->pluck('name')->join(', ') }}</span>
        </span>
        <span class="badge bg-label-primary rounded-pill flex-shrink-0" style="font-size: 0.65rem;">Staf Operasional</span>
    </div>
@else
    <div class="d-inline-flex flex-wrap align-items-center gap-1.5 bg-white border border-secondary border-opacity-25 px-2.5 py-1.5 shadow-sm mw-100 unit-officer-badge" style="border-radius: 14px; line-height: 1.3;">
        <i class="bx bx-shield-quarter text-secondary fs-6 flex-shrink-0"></i>
        <span class="small text-muted">Pengelola:</span>
        <span class="small fw-semibold text-dark text-break">Dikelola langsung oleh Admin Desa</span>
    </div>
@endif
