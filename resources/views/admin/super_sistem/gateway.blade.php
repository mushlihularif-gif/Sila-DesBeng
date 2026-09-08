@extends('admin.layouts.admin')

@section('title', 'Integrasi & API Key Platform')

@php
    $appUrl = rtrim(config('app.url'), '/');
@endphp

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Sistem Platform /</span> Integrasi &amp; API Key Platform</h4>
    <p class="text-muted mb-4" style="margin-top: -1rem;">
        Semua kredensial pihak ketiga disimpan terenkripsi di database, satu kartu = satu kategori = satu baris data.
        Menekan <strong>Terapkan</strong> akan <strong>menimpa</strong> data lama kategori tersebut, jadi tidak ada data yang menumpuk.
    </p>

    {{-- Kredensial tersimpan tapi gagal diuji ke server penyedia --}}
    @if(session('warning'))
        <div class="alert alert-warning alert-dismissible" role="alert">
            <i class="bx bx-error me-1"></i> {{ session('warning') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('success'))
        <div class="alert alert-success alert-dismissible" role="alert">
            <i class="bx bx-check-circle me-1"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row">
        <div class="col-lg-8">

            {{-- ============================================================
                 Pengaturan gateway yang sifatnya bisnis, bukan kredensial
                 ============================================================ --}}
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-1"><i class="bx bx-cog me-2 text-primary"></i>Pengaturan Gateway Platform</h5>
                    <small class="text-muted"><i class="bx bx-info-circle"></i> Berlaku untuk seluruh transaksi dari semua desa/kecamatan.</small>
                </div>
                <div class="card-body">
                    @if($errors->hasBag('umum'))
                        <div class="alert alert-danger">
                            <ul class="mb-0 ps-3">
                                @foreach($errors->getBag('umum')->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('admin.sistem-platform.gateway.update') }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="row g-4">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Penyedia Gateway Aktif</label>
                                <select name="gateway_provider" class="form-select" required>
                                    <option value="midtrans" {{ old('gateway_provider', $penyedia) === 'midtrans' ? 'selected' : '' }}>Midtrans</option>
                                    <option value="xendit" {{ old('gateway_provider', $penyedia) === 'xendit' ? 'selected' : '' }}>Xendit for Platforms</option>
                                </select>
                                <small class="text-muted d-block mt-1">
                                    Pilihan ini berlaku seketika untuk seluruh transaksi warga di semua wilayah.
                                </small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Fee Platform (%) <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="number" step="0.01" min="0" max="100" name="platform_fee_percentage"
                                           class="form-control"
                                           value="{{ old('platform_fee_percentage', $settings->platform_fee_percentage ?? 0) }}" required>
                                    <span class="input-group-text">%</span>
                                </div>
                            </div>
                            {{-- Akibat dari pilihan di atas. Tanpa bagian ini, dropdown
                                 penyedia terlihat seperti catatan biasa: Super Admin tidak
                                 tahu apa yang berubah bagi admin daerah saat ia menggantinya. --}}
                            <div class="col-12">
                                <div class="border rounded p-3 bg-light">
                                    <div class="fw-semibold mb-2">
                                        <i class="bx bx-git-branch me-1"></i>
                                        Yang berlaku sekarang: {{ $labelPenyedia }}
                                    </div>

                                    {{-- Sejak Midtrans dipusatkan (satu akun Diskominfotik, dana wilayah
                                         dibukukan sebagai saldo lalu dicairkan lewat pengajuan penarikan),
                                         kedua penyedia sama-sama tidak menuntut kunci dari wilayah. --}}
                                    <p class="mb-2 small">
                                        Kredensial induk dipegang Diskominfotik dan melayani semua wilayah.
                                        Admin daerah tidak mengisi API key apa pun — mereka cukup mengisi
                                        rekening bank wilayahnya.
                                        @if($penyedia === \App\Support\PenyediaPembayaran::MIDTRANS)
                                            Pemasukan gateway dibukukan sebagai <strong>saldo wilayah</strong> dan
                                            dicairkan lewat menu <em>Sistem Platform &rarr; Penarikan Saldo Wilayah</em>
                                            setelah admin daerah mengajukan.
                                        @else
                                            Wilayah menjadi <strong>sub-akun</strong> dengan saldo dan pencairannya
                                            sendiri, tidak lewat pengajuan manual.
                                        @endif
                                    </p>

                                    @if(! $platformSiap)
                                        <div class="alert alert-warning py-2 px-3 mb-2 small">
                                            <i class="bx bx-error me-1"></i>
                                            Kredensial {{ $labelPenyedia }} induk belum diisi di kartu di bawah.
                                            Selama itu kosong, tidak ada wilayah yang bisa menerima pembayaran otomatis.
                                        </div>
                                    @endif

                                    <div class="d-flex align-items-center gap-2 mb-2">
                                        <span class="badge bg-{{ $jumlahSiap > 0 ? 'success' : 'secondary' }}">
                                            {{ $jumlahSiap }} dari {{ $kesiapanWilayah->count() }} wilayah siap
                                        </span>
                                        <small class="text-muted">menerima pembayaran otomatis</small>
                                    </div>

                                    {{-- Supaya kartu yang hilang tidak terasa seperti fitur
                                         yang raib. --}}
                                    <p class="small text-muted mb-2">
                                        <i class="bx bx-hide me-1"></i>
                                        Kartu kredensial <strong>{{ $labelPenyediaLain }}</strong> disembunyikan
                                        selama {{ $labelPenyedia }} yang aktif. Kunci yang sudah tersimpan
                                        tidak terhapus &mdash; pilih {{ $labelPenyediaLain }} di atas lalu simpan
                                        untuk menampilkannya kembali.
                                    </p>

                                    @if($kesiapanWilayah->count())
                                        <details>
                                            <summary class="small text-primary" style="cursor: pointer;">
                                                Lihat rincian per wilayah
                                            </summary>
                                            <div class="table-responsive mt-2" style="max-height: 260px; overflow-y: auto;">
                                                <table class="table table-sm mb-0">
                                                    <tbody>
                                                        @foreach($kesiapanWilayah as $w)
                                                            <tr>
                                                                <td style="width: 1%;">
                                                                    <i class="bx bx-{{ $w['siap'] ? 'check-circle text-success' : 'x-circle text-muted' }}"></i>
                                                                </td>
                                                                <td class="text-nowrap">{{ $w['nama'] }}</td>
                                                                <td class="small text-muted">{{ $w['alasan'] }}</td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </details>
                                    @endif
                                </div>
                            </div>
                            <div class="col-12 text-end">
                                <button type="submit" class="btn btn-outline-primary">
                                    <i class="bx bx-save me-1"></i> Simpan Pengaturan Gateway
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            {{-- ============================================================
                 Kartu kredensial, dirender otomatis dari config/api_providers.php
                 ============================================================ --}}
            @foreach($providers as $category => $provider)
                @php
                    $baris     = $tersimpan->get($category);
                    $nilai     = $baris?->credentials ?? [];
                    $sudahIsi  = ! empty($nilai);
                    $bagErrors = $errors->getBag($category);
                @endphp

                <div class="card mb-4" id="kartu-{{ $category }}">
                    <div class="card-header d-flex justify-content-between align-items-start">
                        <div>
                            <h5 class="mb-1">
                                <i class="bx {{ $provider['icon'] ?? 'bx-key' }} me-2 text-primary"></i>{{ $provider['label'] }}
                            </h5>
                            <small class="text-muted">{{ $provider['description'] ?? '' }}</small>
                        </div>
                        <span class="badge bg-label-{{ $sudahIsi ? 'success' : 'secondary' }} rounded-pill flex-shrink-0 ms-2">
                            {{ $sudahIsi ? 'Aktif dari panel' : 'Memakai .env' }}
                        </span>
                    </div>

                    <div class="card-body">
                        @if($bagErrors->any())
                            <div class="alert alert-danger">
                                <h6 class="alert-heading fw-bold mb-1"><i class="bx bx-error-circle me-1"></i>Gagal diterapkan</h6>
                                <ul class="mb-0 ps-3">
                                    @foreach($bagErrors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        @if(! empty($provider['notes']))
                            <div class="alert alert-info py-2">
                                <ul class="mb-0 ps-3 small">
                                    @foreach($provider['notes'] as $note)
                                        <li>{{ str_replace('{APP_URL}', $appUrl, $note) }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        {{-- Tautan keluar ke dashboard/simulator penyedia. URL-nya mengikuti
                             mode yang TERSIMPAN, bukan posisi sakelar yang belum disimpan,
                             supaya tidak menyesatkan sebelum ditekan Terapkan. --}}
                        @if(! empty($provider['tautan']))
                            @php
                                $modeField = $provider['mode_field'] ?? null;
                                $modeProduksi = $modeField ? (bool) ($nilai[$modeField] ?? false) : false;
                            @endphp
                            <div class="border rounded-3 p-3 mb-4 bg-light">
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <span class="badge bg-label-{{ $modeProduksi ? 'success' : 'warning' }} rounded-pill">
                                        <i class="bx {{ $modeProduksi ? 'bx-check-shield' : 'bx-test-tube' }} me-1"></i>
                                        Mode tersimpan: {{ $modeProduksi ? 'Production' : 'Sandbox' }}
                                    </span>
                                    <small class="text-muted">Tautan di bawah menyesuaikan mode ini.</small>
                                </div>

                                <div class="d-flex flex-wrap gap-2">
                                    @foreach($provider['tautan'] as $t)
                                        @php
                                            $hanyaSandbox = $t['hanya_sandbox'] ?? false;
                                            $url = $modeProduksi
                                                ? ($t['url_production'] ?? null)
                                                : ($t['url_sandbox'] ?? null);
                                        @endphp
                                        @if($url && ! ($hanyaSandbox && $modeProduksi))
                                            <a href="{{ $url }}" target="_blank" rel="noopener noreferrer"
                                               class="btn btn-sm btn-outline-primary"
                                               title="{{ $t['catatan'] ?? '' }}">
                                                <i class="bx {{ $t['ikon'] ?? 'bx-link-external' }} me-1"></i>{{ $t['label'] }}
                                            </a>
                                        @endif
                                    @endforeach
                                </div>

                                <ul class="mb-0 ps-3 mt-2 text-muted" style="font-size:.72rem">
                                    @foreach($provider['tautan'] as $t)
                                        @if(! (($t['hanya_sandbox'] ?? false) && $modeProduksi))
                                            <li>{{ $t['label'] }} &mdash; {{ $t['catatan'] ?? '' }}</li>
                                        @endif
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form action="{{ route('admin.sistem-platform.credential.update', $category) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="row g-4">
                                @foreach($provider['fields'] as $field => $definition)
                                    @php
                                        $type     = $definition['type'] ?? 'text';
                                        $terisi   = old($field, $nilai[$field] ?? '');
                                        $adaError = $bagErrors->has($field);
                                    @endphp

                                    @if($type === 'boolean')
                                        <div class="col-12">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox"
                                                       name="{{ $field }}" id="{{ $category }}_{{ $field }}" value="1"
                                                       {{ old($field, $nilai[$field] ?? false) ? 'checked' : '' }}>
                                                <label class="form-check-label fw-semibold" for="{{ $category }}_{{ $field }}">
                                                    {{ $definition['label'] }}
                                                </label>
                                            </div>
                                            @if(! empty($definition['hint']))
                                                <small class="text-muted">{{ $definition['hint'] }}</small>
                                            @endif
                                        </div>

                                    @elseif($type === 'select')
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">{{ $definition['label'] }} <span class="text-danger">*</span></label>
                                            <select name="{{ $field }}" class="form-select @if($adaError) is-invalid @endif" required>
                                                <option value="">- Pilih -</option>
                                                @foreach($definition['options'] ?? [] as $opsi => $labelOpsi)
                                                    <option value="{{ $opsi }}" {{ (string) $terisi === (string) $opsi ? 'selected' : '' }}>{{ $labelOpsi }}</option>
                                                @endforeach
                                            </select>
                                            @if($adaError)<div class="invalid-feedback d-block">{{ $bagErrors->first($field) }}</div>@endif
                                        </div>

                                    @else
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">
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
                                            <small class="text-muted">
                                                @if(isset($definition['min']) && isset($definition['max']))
                                                    Wajib diisi, {{ $definition['min'] }}&ndash;{{ $definition['max'] }} karakter.
                                                @else
                                                    Wajib diisi.
                                                @endif
                                                {{ $definition['hint'] ?? '' }}
                                            </small>
                                        </div>
                                    @endif
                                @endforeach

                                <div class="col-12 d-flex justify-content-between align-items-center">
                                    <small class="text-muted">
                                        @if($baris)
                                            <i class="bx bx-time-five"></i>
                                            Terakhir diubah {{ $baris->updated_at?->translatedFormat('d M Y H:i') }}
                                            @if($baris->updated_by) oleh {{ $baris->updated_by }} @endif
                                        @else
                                            <i class="bx bx-lock-alt"></i> Nilai akan dienkripsi AES-256 sebelum disimpan.
                                        @endif
                                    </small>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bx bx-check me-1"></i> Terapkan
                                    </button>
                                </div>
                            </div>
                        </form>

                        @if($baris)
                            <hr class="my-3">
                            <form action="{{ route('admin.sistem-platform.credential.destroy', $category) }}" method="POST"
                                  data-konfirmasi="Hapus kredensial {{ $provider['label'] }}? Sistem akan kembali memakai nilai dari file .env."
                                  data-konfirmasi-judul="Hapus Kredensial"
                                  data-konfirmasi-ya="Ya, Hapus">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                    <i class="bx bx-trash me-1"></i> Hapus &amp; kembalikan ke .env
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            @endforeach

        </div>

        <div class="col-lg-4">
            <div class="d-flex align-items-center bg-light rounded-3 p-3 shadow-sm mb-3">
                <div class="avatar flex-shrink-0 me-3">
                    <span class="avatar-initial rounded bg-label-primary"><i class="bx bx-percentage fs-4"></i></span>
                </div>
                <div>
                    <h6 class="mb-0 fw-bold text-dark">Fee Platform Saat Ini</h6>
                    <small class="text-muted fs-5 fw-bold text-primary">{{ $settings->platform_fee_percentage ?? 0 }}%</small>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header pb-2"><h6 class="mb-0"><i class="bx bx-list-check me-1"></i>Status Integrasi</h6></div>
                <div class="card-body pt-2">
                    <ul class="list-unstyled mb-0">
                        @foreach($providers as $category => $provider)
                            @php $aktif = ! empty($tersimpan->get($category)?->credentials); @endphp
                            <li class="d-flex justify-content-between align-items-center py-1">
                                <span class="small">{{ $provider['label'] }}</span>
                                <i class="bx {{ $aktif ? 'bx-check-circle text-success' : 'bx-minus-circle text-muted' }}"></i>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>

            <div class="alert alert-info mb-3">
                <i class="bx bx-info-circle me-1"></i>
                Kredensial di halaman ini menimpa nilai di file <code>.env</code> secara otomatis begitu diterapkan &mdash;
                tidak perlu edit file server atau restart aplikasi. Kategori yang belum diisi tetap memakai <code>.env</code>.
            </div>

            <div class="alert alert-secondary mb-0">
                <i class="bx bx-plus-circle me-1"></i>
                <strong>Menambah layanan baru?</strong><br>
                Daftarkan saja di <code>config/api_providers.php</code>. Kartu, validasi, dan penyimpanannya muncul sendiri
                di halaman ini tanpa perlu migration atau mengubah tampilan.
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
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
</script>
@endpush
@endsection
