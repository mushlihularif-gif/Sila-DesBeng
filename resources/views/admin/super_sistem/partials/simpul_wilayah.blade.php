{{-- Satu simpul wilayah beserta turunannya (Kecamatan -> Desa/Kelurahan -> RW -> RT). --}}
@php
    $warnaTipe = [
        'kecamatan' => 'info',
        'desa'      => 'success',
        'kelurahan' => 'warning',
        'rw'        => 'primary',
        'rt'        => 'secondary',
    ];
    $warna     = $warnaTipe[$simpul['tipe']] ?? 'secondary';
    $punyaAnak = $simpul['anak']->isNotEmpty();
    $bisaTambahAnak = in_array($simpul['tipe'], ['kecamatan', 'desa', 'kelurahan', 'rw'], true);
    $idKolaps  = 'wil-' . $simpul['id'];
    $terbuka   = $punyaAnak && in_array($simpul['tipe'], ['kecamatan'], true);
@endphp

<div class="simpul-wilayah mb-1" data-nama="{{ mb_strtolower($simpul['nama']) }}" data-tipe="{{ $simpul['tipe'] }}">
    <div class="d-flex align-items-start align-items-sm-center gap-2 py-2 px-2.5 rounded-3 baris-wilayah">
        @if($punyaAnak)
            <button class="btn btn-icon btn-sm btn-light border rounded-circle p-0 tombol-lipat mt-0.5 mt-sm-0 {{ $terbuka ? '' : 'collapsed' }}"
                    type="button" data-bs-toggle="collapse" data-bs-target="#{{ $idKolaps }}"
                    aria-expanded="{{ $terbuka ? 'true' : 'false' }}"
                    style="width: 26px; height: 26px; flex-shrink: 0;">
                <i class="bx bx-chevron-right fs-6 panah-lipat"></i>
            </button>
        @else
            <span class="d-inline-block" style="width: 26px; flex-shrink: 0;"></span>
        @endif

        <span class="d-inline-flex align-items-center justify-content-center rounded-3 bg-label-{{ $warna }} flex-shrink-0"
              style="width: 34px; height: 34px;">
            <i class="bx {{ $punyaAnak ? 'bx-git-branch' : 'bx-map-pin' }} text-{{ $warna }}"></i>
        </span>

        <div class="flex-grow-1 min-w-0">
            <div class="d-flex align-items-center flex-wrap gap-1.5">
                <span class="fw-bold text-dark small text-break">{{ $simpul['nama'] }}</span>
                <span class="badge bg-label-{{ $warna }} rounded-pill text-uppercase fw-semibold"
                      style="font-size: 0.64rem; letter-spacing: 0.4px;">{{ $simpul['tipe'] }}</span>
            </div>

            <div class="d-flex align-items-center flex-wrap gap-2 text-muted mt-1" style="font-size: 0.73rem;">
                @if($punyaAnak)
                    <span class="d-inline-flex align-items-center gap-1">
                        <i class="bx bx-sitemap"></i>{{ $simpul['anak']->count() }} bawahan
                    </span>
                @endif
                @if($simpul['warga'] > 0)
                    <span class="d-inline-flex align-items-center gap-1">
                        <i class="bx bx-user"></i>{{ $simpul['warga'] }} akun
                    </span>
                @endif
                @if($simpul['layanan'] > 0)
                    <span class="d-inline-flex align-items-center gap-1 text-success fw-medium">
                        <i class="bx bx-grid-alt"></i>{{ $simpul['layanan'] }} layanan aktif
                    </span>
                @endif
                @foreach($simpul['pengurus'] as $p)
                    <span class="badge bg-label-primary rounded-pill fw-medium" style="font-size: 0.68rem;" title="{{ $p->email }}">
                        <i class="bx bx-id-card me-0.5"></i>{{ $p->name }}
                    </span>
                @endforeach
            </div>
        </div>

        @if($bisaTambahAnak)
            <button type="button"
                    class="btn btn-xs btn-outline-primary rounded-pill px-2.5 flex-shrink-0 wilayah-quick-add btn-quick-add-child d-none d-sm-inline-flex align-items-center gap-1"
                    data-parent-id="{{ $simpul['id'] }}"
                    title="Tambah wilayah di bawah {{ $simpul['nama'] }}">
                <i class="bx bx-plus"></i>
                <span>Bawahan</span>
            </button>
        @endif
    </div>

    @if($punyaAnak)
        <div class="collapse {{ $terbuka ? 'show' : '' }}" id="{{ $idKolaps }}">
            <div class="wilayah-children-branch pt-1">
                @foreach($simpul['anak'] as $anak)
                    @include('admin.super_sistem.partials.simpul_wilayah', [
                        'simpul' => $anak,
                        'level'  => $level + 1,
                    ])
                @endforeach
            </div>
        </div>
    @endif
</div>
