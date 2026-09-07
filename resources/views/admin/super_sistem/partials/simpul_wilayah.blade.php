{{-- Satu simpul wilayah beserta turunannya. Memanggil dirinya sendiri, jadi
     kedalamannya mengikuti data: kabupaten -> kecamatan -> desa -> RW -> RT. --}}
@php
    $warnaTipe = [
        'kabupaten' => 'primary',
        'kecamatan' => 'info',
        'desa'      => 'success',
        'kelurahan' => 'warning',
        'rw'        => 'secondary',
        'rt'        => 'dark',
    ];
    $warna    = $warnaTipe[$simpul['tipe']] ?? 'secondary';
    $punyaAnak = $simpul['anak']->isNotEmpty();
    $idKolaps = 'wil-' . $simpul['id'];
    // Kabupaten & kecamatan terbuka sejak awal supaya susunan utamanya langsung
    // terbaca; desa ke bawah dibiarkan tertutup agar daftarnya tidak meledak.
    $terbuka = $punyaAnak && in_array($simpul['tipe'], ['kabupaten'], true);
@endphp

<div class="simpul-wilayah" data-nama="{{ mb_strtolower($simpul['nama']) }}" data-tipe="{{ $simpul['tipe'] }}">
    <div class="d-flex align-items-center gap-2 py-2 px-2 rounded-3 baris-wilayah"
         style="margin-left: {{ $level * 22 }}px;">

        @if($punyaAnak)
            <button class="btn btn-icon btn-sm btn-text-secondary rounded-circle p-0 tombol-lipat {{ $terbuka ? '' : 'collapsed' }}"
                    type="button" data-bs-toggle="collapse" data-bs-target="#{{ $idKolaps }}"
                    aria-expanded="{{ $terbuka ? 'true' : 'false' }}"
                    style="width: 26px; height: 26px; flex-shrink: 0;">
                <i class="bx bx-chevron-right fs-5 panah-lipat"></i>
            </button>
        @else
            <span class="d-inline-block" style="width: 26px; flex-shrink: 0;"></span>
        @endif

        <span class="ikon-bulat rounded-circle bg-{{ $warna }}-subtle text-{{ $warna }}"
              style="width: 34px; height: 34px; flex-shrink: 0;">
            <i class="bx {{ $punyaAnak ? 'bx-git-branch' : 'bx-map-pin' }}"></i>
        </span>

        <div class="flex-grow-1 min-w-0">
            <div class="d-flex align-items-center flex-wrap gap-2">
                <span class="fw-semibold text-truncate">{{ $simpul['nama'] }}</span>
                <span class="badge bg-{{ $warna }}-subtle text-{{ $warna }} rounded-pill text-uppercase"
                      style="font-size: 0.65rem; letter-spacing: 0.5px;">{{ $simpul['tipe'] }}</span>
                <span class="text-muted" style="font-size: 0.72rem;">#{{ $simpul['id'] }}</span>
            </div>

            <div class="d-flex align-items-center flex-wrap gap-3 text-muted mt-1" style="font-size: 0.75rem;">
                @if($punyaAnak)
                    <span><i class="bx bx-sitemap me-1"></i>{{ $simpul['anak']->count() }} wilayah bawahan</span>
                @endif
                @if($simpul['warga'] > 0)
                    <span><i class="bx bx-user me-1"></i>{{ $simpul['warga'] }} akun</span>
                @endif
                @if($simpul['layanan'] > 0)
                    <span><i class="bx bx-grid-alt me-1"></i>{{ $simpul['layanan'] }} layanan aktif</span>
                @endif
                @foreach($simpul['pengurus'] as $p)
                    <span class="text-primary" title="{{ $p->email }}">
                        <i class="bx bx-id-card me-1"></i>{{ $p->name }}
                    </span>
                @endforeach
            </div>
        </div>
    </div>

    @if($punyaAnak)
        <div class="collapse {{ $terbuka ? 'show' : '' }}" id="{{ $idKolaps }}">
            @foreach($simpul['anak'] as $anak)
                @include('admin.super_sistem.partials.simpul_wilayah', [
                    'simpul' => $anak,
                    'level'  => $level + 1,
                ])
            @endforeach
        </div>
    @endif
</div>
