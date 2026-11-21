@extends('admin.layouts.admin')

@section('title', 'Integrasi & API Key Platform')

@php
    $appUrl = rtrim(config('app.url'), '/');
    $totalProviders = count($providers);
    $activePanelCount = collect($providers)->keys()->filter(fn ($cat) => ! empty($tersimpan->get($cat)?->credentials))->count();
    $envFallbackCount = $totalProviders - $activePanelCount;
@endphp

@section('content')
<style>
    .gw-kpi-card {
        border: 1px solid #eef1f6;
        border-radius: 16px;
        background: #ffffff;
    }
    .gw-provider-card {
        border: 1px solid #eef1f6;
        border-radius: 16px;
        background: #ffffff;
        transition: border-color 0.18s ease, box-shadow 0.18s ease;
    }
    .gw-provider-card:hover {
        border-color: #dbeafe;
        box-shadow: 0 8px 24px rgba(15, 23, 42, 0.06) !important;
    }
    .gw-guide-box {
        background: #f8fbff;
        border: 1px solid #dbeafe;
        border-radius: 12px;
    }
    .gw-status-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        padding: 9px 10px;
        border-radius: 10px;
        cursor: pointer;
        transition: background-color 0.15s ease;
        text-decoration: none;
        color: #1e293b;
    }
    .gw-status-row:hover {
        background: #f1f5f9;
        color: #0d6efd;
    }
</style>

<div class="container-xxl flex-grow-1 container-p-y">
    {{-- Page Header --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
        <div>
            <h4 class="fw-bold mb-1 text-dark">
                <span class="text-muted fw-normal">Sistem Platform /</span> Integrasi &amp; API Key
            </h4>
            <p class="text-muted mb-0 small">
                Kelola kredensial layanan pihak ketiga secara terenkripsi AES-256 langsung dari panel tanpa perlu mengedit file <code>.env</code> server.
            </p>
        </div>
        <span class="badge bg-label-success rounded-pill px-3 py-2 fw-semibold">
            <i class="bx bx-lock-alt me-1"></i>Terenkripsi AES-256
        </span>
    </div>

    {{-- Flash Alerts --}}
    @if(session('warning'))
        <div class="alert alert-warning border-0 shadow-sm rounded-4 alert-dismissible fade show d-flex align-items-center gap-2 mb-4" role="alert">
            <i class="bx bx-error fs-4 flex-shrink-0"></i>
            <div class="flex-grow-1">{{ session('warning') }}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
        </div>
    @endif

    @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm rounded-4 alert-dismissible fade show d-flex align-items-center gap-2 mb-4" role="alert">
            <i class="bx bx-check-circle fs-4 flex-shrink-0"></i>
            <div class="flex-grow-1">{{ session('success') }}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
        </div>
    @endif

    {{-- 3 Summary KPI Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-4">
            <div class="card gw-kpi-card shadow-sm h-100">
                <div class="card-body p-3 p-md-4 text-center">
                    <span class="text-muted small fw-semibold d-block mb-1">Total Layanan</span>
                    <h4 class="fw-bold text-primary mb-0">{{ $totalProviders }}</h4>
                </div>
            </div>
        </div>
        <div class="col-4">
            <div class="card gw-kpi-card shadow-sm h-100">
                <div class="card-body p-3 p-md-4 text-center">
                    <span class="text-muted small fw-semibold d-block mb-1">Aktif di Panel</span>
                    <h4 class="fw-bold text-success mb-0">{{ $activePanelCount }}</h4>
                </div>
            </div>
        </div>
        <div class="col-4">
            <div class="card gw-kpi-card shadow-sm h-100">
                <div class="card-body p-3 p-md-4 text-center">
                    <span class="text-muted small fw-semibold d-block mb-1">Default .env</span>
                    <h4 class="fw-bold text-secondary mb-0">{{ $envFallbackCount }}</h4>
                </div>
            </div>
        </div>
    </div>

    {{-- Filter Pill Bar --}}
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-2.5 p-sm-3">
            <ul class="nav nav-pills gap-2 flex-nowrap overflow-x-auto mb-0" id="gwFilterTabs">
                <li class="nav-item flex-shrink-0">
                    <button type="button" class="nav-link active rounded-pill px-3 py-1.5 fw-semibold gw-filter-btn text-nowrap" data-target="all" style="font-size: 0.82rem;">
                        <i class="bx bx-grid-alt me-1"></i>Semua ({{ $totalProviders }})
                    </button>
                </li>
                @foreach($providers as $category => $provider)
                    @php $aktif = ! empty($tersimpan->get($category)?->credentials); @endphp
                    <li class="nav-item flex-shrink-0">
                        <button type="button" class="nav-link rounded-pill px-3 py-1.5 fw-semibold gw-filter-btn text-nowrap d-inline-flex align-items-center gap-1.5" data-target="{{ $category }}" style="font-size: 0.82rem;">
                            <i class="bx {{ $provider['icon'] ?? 'bx-key' }}"></i>
                            <span>{{ $provider['label'] }}</span>
                            <span class="rounded-circle {{ $aktif ? 'bg-success' : 'bg-secondary opacity-50' }}" style="width: 7px; height: 7px;"></span>
                        </button>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>

    <div class="row g-4 align-items-start">
        {{-- Left Column: Provider Cards --}}
        <div class="col-12 col-lg-8">
            @foreach($providers as $category => $provider)
                @php
                    $baris     = $tersimpan->get($category);
                    $nilai     = $baris?->credentials ?? [];
                    $sudahIsi  = ! empty($nilai);
                    $bagErrors = $errors->getBag($category);
                    $collapseGuideId = 'guide-' . $category;
                @endphp

                <div class="card gw-provider-card shadow-sm mb-4 gw-card-item" id="kartu-{{ $category }}" data-category="{{ $category }}">
                    <div class="card-header bg-white border-bottom px-4 py-3.5">
                        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start gap-2">
                            <div class="d-flex align-items-start gap-3">
                                <div class="d-flex align-items-center justify-content-center rounded-3 bg-label-primary flex-shrink-0" style="width: 44px; height: 44px;">
                                    <i class="bx {{ $provider['icon'] ?? 'bx-key' }} fs-4 text-primary"></i>
                                </div>
                                <div class="min-w-0">
                                    <div class="d-flex align-items-center flex-wrap gap-2 mb-0.5">
                                        <h6 class="fw-bold text-dark mb-0">{{ $provider['label'] }}</h6>
                                        <span class="badge bg-label-{{ $sudahIsi ? 'success' : 'secondary' }} rounded-pill" style="font-size: 0.7rem;">
                                            {{ $sudahIsi ? 'Aktif dari Panel' : 'Memakai .env' }}
                                        </span>
                                    </div>
                                    <p class="text-muted small mb-0" style="line-height: 1.45;">{{ $provider['description'] ?? '' }}</p>
                                </div>
                            </div>

                            @if(! empty($provider['console_url']))
                                <a href="{{ $provider['console_url'] }}" target="_blank" rel="noopener noreferrer"
                                   class="btn btn-xs btn-outline-primary rounded-pill px-3 flex-shrink-0 d-inline-flex align-items-center gap-1">
                                    <span>Console</span>
                                    <i class="bx bx-link-external"></i>
                                </a>
                            @endif
                        </div>
                    </div>

                    <div class="card-body p-4">
                        @if($bagErrors->any())
                            <div class="alert alert-danger border-0 rounded-3 mb-3">
                                <h6 class="alert-heading fw-bold small mb-1"><i class="bx bx-error-circle me-1"></i>Gagal diterapkan</h6>
                                <ul class="mb-0 ps-3 small">
                                    @foreach($bagErrors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        @if($category === 'google_maps')
                            @include('admin.super_sistem.partials.maps-check')
                        @endif

                        {{-- Collapsible Guide --}}
                        @if(! empty($provider['notes']))
                            <div class="mb-3">
                                <button type="button" class="btn btn-sm btn-link text-primary text-decoration-none p-0 fw-semibold d-inline-flex align-items-center gap-1"
                                        data-bs-toggle="collapse" data-bs-target="#{{ $collapseGuideId }}" aria-expanded="{{ $bagErrors->any() ? 'true' : 'false' }}">
                                    <i class="bx bx-help-circle"></i>
                                    <span>Panduan Pengaturan</span>
                                    <i class="bx bx-chevron-down"></i>
                                </button>
                                <div class="collapse {{ $bagErrors->any() ? 'show' : '' }} mt-2" id="{{ $collapseGuideId }}">
                                    <div class="gw-guide-box p-3">
                                        <ul class="mb-0 ps-3 small text-dark" style="line-height: 1.55;">
                                            @foreach($provider['notes'] as $note)
                                                <li class="mb-1">{{ str_replace('{APP_URL}', $appUrl, $note) }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <form action="{{ route('admin.sistem-platform.credential.update', $category) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="row g-3">
                                @foreach($provider['fields'] as $field => $definition)
                                    @php
                                        $type     = $definition['type'] ?? 'text';
                                        $terisi   = old($field, $nilai[$field] ?? '');
                                        $adaError = $bagErrors->has($field);
                                        $colClass = count($provider['fields']) === 1 ? 'col-12' : 'col-12 col-md-6';
                                    @endphp

                                    @if($type === 'boolean')
                                        <div class="col-12">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox"
                                                       name="{{ $field }}" id="{{ $category }}_{{ $field }}" value="1"
                                                       {{ old($field, $nilai[$field] ?? false) ? 'checked' : '' }}>
                                                <label class="form-check-label fw-semibold small" for="{{ $category }}_{{ $field }}">
                                                    {{ $definition['label'] }}
                                                </label>
                                            </div>
                                            @if(! empty($definition['hint']))
                                                <small class="text-muted d-block mt-1" style="font-size: 0.74rem;">{{ $definition['hint'] }}</small>
                                            @endif
                                        </div>
                                    @elseif($type === 'select')
                                        <div class="{{ $colClass }}">
                                            <label class="form-label fw-semibold small text-dark">{{ $definition['label'] }} <span class="text-danger">*</span></label>
                                            <select name="{{ $field }}" class="form-select rounded-3 @if($adaError) is-invalid @endif" required>
                                                <option value="">- Pilih -</option>
                                                @foreach($definition['options'] ?? [] as $opsi => $labelOpsi)
                                                    <option value="{{ $opsi }}" {{ (string) $terisi === (string) $opsi ? 'selected' : '' }}>{{ $labelOpsi }}</option>
                                                @endforeach
                                            </select>
                                            @if($adaError)<div class="invalid-feedback d-block">{{ $bagErrors->first($field) }}</div>@endif
                                        </div>
                                    @else
                                        <div class="{{ $colClass }}">
                                            <label class="form-label fw-semibold small text-dark">
                                                {{ $definition['label'] }} <span class="text-danger">*</span>
                                            </label>
                                            <div class="input-group">
                                                <input type="{{ $type === 'secret' ? 'password' : 'text' }}"
                                                       name="{{ $field }}"
                                                       class="form-control @if($adaError) is-invalid @endif"
                                                       value="{{ $terisi }}"
                                                       placeholder="{{ $definition['placeholder'] ?? '' }}"
                                                       @if(isset($definition['min'])) minlength="{{ $definition['min'] }}" @endif
                                                       @if(isset($definition['max'])) maxlength="{{ $definition['max'] }}" @endif
                                                       autocomplete="off"
                                                       spellcheck="false"
                                                       required>
                                                @if($type === 'secret')
                                                    <button class="btn btn-outline-secondary toggle-rahasia" type="button" tabindex="-1" title="Tampilkan / sembunyikan">
                                                        <i class="bx bx-hide"></i>
                                                    </button>
                                                @endif
                                                @if($adaError)<div class="invalid-feedback">{{ $bagErrors->first($field) }}</div>@endif
                                            </div>
                                            <small class="text-muted d-block mt-1" style="font-size: 0.74rem;">
                                                {{ $definition['hint'] ?? '' }}
                                            </small>
                                        </div>
                                    @endif
                                @endforeach

                                <div class="col-12 pt-2 border-top d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2">
                                    <small class="text-muted" style="font-size: 0.75rem;">
                                        @if($baris)
                                            <i class="bx bx-time-five me-0.5"></i>Diperbarui {{ $baris->updated_at?->translatedFormat('d M Y, H:i') }}
                                        @else
                                            <i class="bx bx-lock-alt me-0.5"></i>Disimpan terenkripsi AES-256
                                        @endif
                                    </small>
                                    <div class="d-flex flex-wrap gap-2 w-100 w-sm-auto justify-content-end">
                                        @if($baris)
                                            <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-3 btn-trigger-reset-cred w-100 w-sm-auto"
                                                    data-form-id="reset-cred-{{ $category }}">
                                                <i class="bx bx-reset me-1"></i>Reset ke .env
                                            </button>
                                        @endif
                                        <button type="submit" class="btn btn-sm btn-primary rounded-pill px-4 fw-semibold shadow-sm w-100 w-sm-auto">
                                            <i class="bx bx-check me-1"></i>Simpan &amp; Terapkan
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>

                        @if($baris)
                            <form id="reset-cred-{{ $category }}" action="{{ route('admin.sistem-platform.credential.destroy', $category) }}" method="POST" class="d-none"
                                  data-konfirmasi="Hapus kredensial {{ $provider['label'] }}? Sistem akan kembali memakai nilai default dari file .env."
                                  data-konfirmasi-judul="Reset Kredensial"
                                  data-konfirmasi-ya="Ya, Reset">
                                @csrf
                                @method('DELETE')
                            </form>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Right Column: Status Overview & Info --}}
        <div class="col-12 col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-white border-bottom px-4 py-3 d-flex align-items-center gap-2">
                    <i class="bx bx-list-check text-primary fs-5"></i>
                    <h6 class="fw-bold text-dark mb-0">Status Integrasi</h6>
                </div>
                <div class="card-body p-3">
                    @foreach($providers as $category => $provider)
                        @php $aktif = ! empty($tersimpan->get($category)?->credentials); @endphp
                        <a href="#kartu-{{ $category }}" class="gw-status-row gw-jump-link" data-target="{{ $category }}">
                            <div class="d-flex align-items-center gap-2 min-w-0">
                                <i class="bx {{ $provider['icon'] ?? 'bx-key' }} text-primary"></i>
                                <span class="small fw-semibold text-truncate">{{ $provider['label'] }}</span>
                            </div>
                            <span class="badge bg-label-{{ $aktif ? 'success' : 'secondary' }} rounded-pill flex-shrink-0" style="font-size: 0.68rem;">
                                {{ $aktif ? 'Aktif' : '.env' }}
                            </span>
                        </a>
                    @endforeach
                </div>
            </div>

            <div class="card bg-label-primary border-0 shadow-none rounded-4 mb-4">
                <div class="card-body p-4">
                    <h6 class="fw-bold text-primary mb-2">
                        <i class="bx bx-shield-quarter me-1"></i>Cara Kerja &amp; Keamanan
                    </h6>
                    <ul class="small text-primary mb-0 ps-3" style="opacity: 0.9; line-height: 1.6;">
                        <li class="mb-1">Seluruh kunci disimpan terenkripsi di database dan langsung aktif tanpa restart server.</li>
                        <li class="mb-1">Kategori yang belum diisi tetap menggunakan konfigurasi default dari file <code>.env</code>.</li>
                        <li>Pembayaran layanan desa dikelola langsung oleh masing-masing desa (tunai/transfer), tanpa payment gateway pihak ketiga.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.toggle-rahasia').forEach(function (tombol) {
        tombol.addEventListener('click', function () {
            var input = tombol.parentElement.querySelector('input');
            var ikon = tombol.querySelector('i');
            var disembunyikan = input.type === 'password';
            input.type = disembunyikan ? 'text' : 'password';
            ikon.classList.toggle('bx-hide', !disembunyikan);
            ikon.classList.toggle('bx-show', disembunyikan);
        });
    });

    document.querySelectorAll('.btn-trigger-reset-cred').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var form = document.getElementById(this.getAttribute('data-form-id'));
            if (form) form.requestSubmit ? form.requestSubmit() : form.submit();
        });
    });

    function selectProviderTab(target) {
        document.querySelectorAll('.gw-filter-btn').forEach(function (b) {
            b.classList.toggle('active', b.getAttribute('data-target') === target);
        });
        document.querySelectorAll('.gw-card-item').forEach(function (card) {
            var match = target === 'all' || card.getAttribute('data-category') === target;
            card.classList.toggle('d-none', !match);
        });
    }

    document.querySelectorAll('.gw-filter-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            selectProviderTab(this.getAttribute('data-target') || 'all');
        });
    });

    document.querySelectorAll('.gw-jump-link').forEach(function (link) {
        link.addEventListener('click', function (e) {
            e.preventDefault();
            var target = this.getAttribute('data-target');
            selectProviderTab(target);
            var el = document.getElementById('kartu-' + target);
            if (el) el.scrollIntoView({ behavior: 'smooth', block: 'start' });
        });
    });
});
</script>
@endpush
@endsection
