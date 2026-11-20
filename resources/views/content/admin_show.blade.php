@extends('admin.layouts.admin')

@section('title', 'Editor Kabar & Pengumuman Daerah')

@section('content')
<style>
    #content-workspace [hidden] { display: none !important; }
    #content-workspace button:disabled { opacity: 0.55; cursor: not-allowed; }
    #content-workspace dialog {
        margin: auto;
        max-height: 90dvh;
        overflow-y: auto;
        width: calc(100% - 2rem);
        max-width: 720px;
        border: 0;
        border-radius: 16px;
        padding: 1.5rem;
        box-shadow: 0 20px 50px rgba(15, 23, 42, 0.25);
        background: #fff;
    }
    #content-workspace dialog::backdrop { background: rgba(15, 23, 42, 0.55); }
    #content-workspace summary { cursor: pointer; }
    .cw-step-box {
        border-radius: 14px;
        border: 1px solid #eef1f6;
        background: #fff;
        padding: 0.9rem 1rem;
    }
    /* Grid helpers for dynamic JS content inside photo-slots, gallery, source-options */
    #photo-slots {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 0.75rem;
    }
    #gallery, #review-photos {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(130px, 1fr));
        gap: 0.75rem;
    }
    #source-options {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
        gap: 0.85rem;
    }
    #photo-slots img, #gallery img, #review-photos img {
        width: 100%;
        height: 120px;
        object-fit: cover;
        border-radius: 10px;
    }
    .segi-ai-progress {
        background: linear-gradient(135deg, #f0f4ff 0%, #e8f8ff 100%);
        border: 1px solid rgba(105, 108, 255, 0.25);
        border-radius: 14px;
        padding: 1rem 1.25rem;
        color: #3b4978;
    }
    .segi-ai-scan {
        height: 4px;
        border-radius: 99px;
        background: linear-gradient(90deg, #696cff, #03c3ec, #696cff);
        background-size: 200% 100%;
        animation: cwAiScan 1.5s linear infinite;
        margin-top: 0.75rem;
    }
    @keyframes cwAiScan {
        0% { background-position: 0% 50%; }
        100% { background-position: 200% 50%; }
    }
    @media (max-width: 575.98px) {
        #photo-slots { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    }
</style>

@php
    $isManager = app(\App\Services\ContentAccess::class)->manager(auth()->user());
@endphp

<div class="container-xxl flex-grow-1 container-p-y" id="content-workspace"
     data-manager="{{ $isManager ? '1' : '0' }}"
     data-base="{{ url('/kelola-kabar/'.$item->id) }}"
     data-ai="{{ route('content.ai') }}"
     data-csrf="{{ csrf_token() }}">

    <!-- Page Header -->
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-start align-items-lg-center gap-3 mb-4">
        <div class="min-w-0">
            <div class="mb-2">
                <a href="{{ route('content.index') }}" class="segi-back btn btn-sm btn-outline-secondary rounded-pill px-3 d-inline-flex align-items-center gap-1">
                    <i class="bx bx-arrow-back"></i>
                    <span>Kembali ke Daftar Kabar</span>
                </a>
            </div>
            <h4 class="fw-bold mb-1">
                <span class="text-muted fw-light">{{ $item->post_category }} /</span>
                {{ $isManager ? 'Periksa & Kelola Kiriman' : 'Tulis Kabar Daerah' }}
            </h4>
            <p class="text-muted small mb-0">
                Pengirim: <strong class="text-dark">{{ $item->admin->name ?? 'Warga' }}</strong>
                @if($item->region) &middot; {{ $item->region->name }} @endif
            </p>
        </div>

        <div class="d-flex flex-wrap gap-2 w-100 w-lg-auto justify-content-start justify-content-lg-end flex-shrink-0">
            <button id="claim" type="button" class="btn btn-primary rounded-pill px-4 shadow-sm d-inline-flex align-items-center justify-content-center gap-1.5 flex-grow-1 flex-sm-grow-0">
                <i class="bx bx-edit-alt"></i>
                <span>{{ $isManager ? 'Periksa dan Edit' : 'Mulai Menulis' }}</span>
            </button>
            <button id="release" type="button" hidden class="btn btn-outline-secondary rounded-pill px-4 d-inline-flex align-items-center justify-content-center gap-1.5 flex-grow-1 flex-sm-grow-0">
                <i class="bx bx-check"></i>
                <span>Simpan dan Selesai Mengedit</span>
            </button>
        </div>
    </div>

    <!-- Panduan 3 Langkah -->
    <div class="row g-2 g-md-3 mb-4" aria-label="Langkah mengelola kiriman">
        <div class="col-12 col-md-4">
            <div class="cw-step-box shadow-sm h-100">
                <div class="fw-bold text-primary small mb-1">1. {{ $isManager ? 'Periksa kiriman' : 'Isi kegiatan' }}</div>
                <p class="text-muted small mb-0">{{ $isManager ? 'Baca cerita, waktu, lokasi, dan fotonya.' : 'Tulis tempat dan waktu kegiatan, lalu tambahkan foto.' }}</p>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="cw-step-box shadow-sm h-100">
                <div class="fw-bold text-primary small mb-1">2. {{ $isManager ? 'Lengkapi ceritanya' : 'Tulis atau minta bantuan AI' }}</div>
                <p class="text-muted small mb-0">{{ $isManager ? 'Rapikan sendiri, gunakan AI, atau gabungkan kiriman kegiatan yang sama.' : 'Cukup ceritakan yang terjadi. AI dapat membantu menyusun tulisan.' }}</p>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="cw-step-box shadow-sm h-100">
                <div class="fw-bold text-primary small mb-1">3. {{ $isManager ? 'Periksa lalu terbitkan' : 'Kirim ke admin' }}</div>
                <p class="text-muted small mb-0">{{ $isManager ? 'Lihat hasil akhir dan penerimanya sebelum diterbitkan.' : 'Admin wilayah meninjau kiriman. Hasilnya masuk ke notifikasi Anda.' }}</p>
            </div>
        </div>
    </div>

    <!-- Live Sync, Presence & Alerts -->
    <div id="sync-status" role="status" aria-live="polite" class="alert bg-label-primary border-0 rounded-4 py-2.5 px-3 mb-2 small fw-medium">
        Memeriksa siapa yang sedang mengedit…
    </div>
    <p id="presence" class="small text-muted mb-3"></p>
    <div id="error" role="alert" class="alert alert-danger border-0 rounded-4 mb-3 small" hidden></div>
    <div id="recovery" hidden class="alert alert-warning border-0 rounded-4 mb-3 small">
        <p class="mb-2 fw-semibold">Tulisan Anda masih ada di halaman ini, tetapi belum tersimpan. Unduh salinannya sebelum membuka tulisan terbaru.</p>
        <div class="d-flex flex-wrap gap-2">
            <button id="download-draft" type="button" class="btn btn-sm btn-outline-dark rounded-pill">Unduh Salinan Tulisan Saya</button>
            <button id="load-latest" type="button" class="btn btn-sm btn-warning rounded-pill">Ganti dengan Tulisan Terbaru</button>
        </div>
    </div>
    <div id="updated" role="status" aria-live="polite" class="small text-primary fw-semibold mb-3"></div>

    <!-- Status & Manager Tools (Top Grid) -->
    <div class="row g-3 mb-4">
        <div class="col-12 {{ $isManager && $item->post_category === 'Berita' ? 'col-lg-6' : '' }}">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-3 p-md-4">
                    <h6 class="fw-bold text-dark mb-2 d-flex align-items-center gap-2">
                        <i class="bx bx-info-circle text-primary"></i>
                        <span>Status Kiriman</span>
                    </h6>
                    <p id="workflow-state" class="fw-bold text-primary mb-1"></p>
                    <p id="next-step" class="small text-muted mb-1"></p>
                    <p id="published-at" class="small text-muted mb-1"></p>
                    <p id="reason" class="small text-danger fw-semibold mb-1"></p>
                    <a id="public-link" hidden class="btn btn-sm btn-outline-primary rounded-pill mt-2" target="_blank">
                        <i class="bx bx-link-external me-1"></i>Lihat Berita yang Terbit
                    </a>
                </div>
            </div>
        </div>

        @if($isManager && $item->post_category === 'Berita')
        <div class="col-12 col-lg-6">
            <section id="source-panel" class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-3 p-md-4">
                    <h6 class="fw-bold text-dark mb-1 d-flex align-items-center gap-2">
                        <i class="bx bx-merge text-info"></i>
                        <span>Gabungkan Kiriman Warga</span>
                    </h6>
                    <p class="small text-muted mb-3">Kegiatan yang sama dikirim beberapa warga? Pilih foto dan cerita mereka untuk dijadikan satu berita.</p>
                    <button id="load-sources" type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                        <i class="bx bx-images me-1"></i>Pilih Kiriman Warga
                    </button>
                    <p class="small text-muted mt-2 mb-0">Opsional. Nama penyumbang foto dan cerita dicatat otomatis.</p>
                    <div id="selected-sources" class="d-flex flex-column gap-2 mt-3"></div>
                </div>
            </section>
        </div>
        @endif
    </div>

    <!-- Main Editor Card -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-3 p-md-4">
            @if($item->contribution_to)
                <div class="alert bg-label-info border-0 rounded-3 small mb-4">
                    Anda menambahkan dokumentasi untuk kabar yang sudah ada. Pilih foto dan tulis keterangan singkat; admin akan meninjaunya.
                </div>
            @endif

            <form id="editor-form">
                <fieldset id="editor-fields" disabled class="min-w-0">
                    <legend class="fs-6 fw-bold text-dark border-bottom pb-2 mb-3">Tentang Kegiatan</legend>

                    <div class="row g-3 mb-3">
                        <div class="col-12 col-md-6">
                            <label for="location" class="form-label fw-semibold small text-dark">Lokasi Kegiatan</label>
                            <input id="location" name="location" maxlength="255" class="form-control" placeholder="Contoh: Lapangan RT 01, Desa Pematang Duku Timur">
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="event_date" class="form-label fw-semibold small text-dark">Waktu Kegiatan</label>
                            <input id="event_date" name="event_date" type="datetime-local" class="form-control" aria-describedby="event-help">
                            <small id="event-help" class="text-muted">Isi waktu kejadian. Waktu terbit dicatat otomatis.</small>
                        </div>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-12 {{ $item->post_category === 'Pengumuman' ? 'col-md-6' : '' }}">
                            <label for="type" class="form-label fw-semibold small text-dark">Jenis Kegiatan</label>
                            <select id="type" name="type" class="form-select">
                                <option value="Event">Kegiatan / Acara</option>
                                @if($item->post_category === 'Pengumuman')
                                    <option value="Pengumuman">Pengumuman</option>
                                @endif
                                <option value="Gotong Royong">Gotong Royong</option>
                            </select>
                        </div>

                        @if($item->post_category === 'Pengumuman')
                        <div class="col-12 col-md-6">
                            <label for="target_audience_id" class="form-label fw-semibold small text-dark">Siapa yang Menerima Pengumuman?</label>
                            <select id="target_audience_id" name="target_audience_id" class="form-select">
                                @foreach($targets as $target)
                                    <option value="{{ $target->id }}">{{ $target->name }} ({{ strtoupper($target->type) }})</option>
                                @endforeach
                            </select>
                            <small class="text-muted">Pilih cakupan wilayah penerima notifikasi pengumuman.</small>
                        </div>
                        @endif
                    </div>

                    <!-- Foto Kegiatan -->
                    <div class="border-top pt-3 mb-4">
                        <label for="photos" class="form-label fw-bold text-dark">Foto Kegiatan</label>
                        <input id="photos" type="file" accept="image/jpeg,image/png,image/webp" multiple hidden aria-describedby="photo-help photo-status">
                        <p id="photo-help" class="small text-muted mb-2">Pilih hingga 3 foto di bawah. Maksimal 5 MB per foto.</p>
                        <div id="photo-slots" class="mb-2"></div>
                        <p id="photo-status" role="status" class="small text-primary mb-2"></p>
                        <button id="upload" type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                            <i class="bx bx-upload me-1"></i>Simpan Foto ke Draf
                        </button>
                        <div id="gallery" class="mt-3"></div>
                    </div>

                    <!-- Cara Menulis & AI -->
                    <div class="border-top pt-3 mb-3">
                        <label for="write-mode" class="form-label fw-bold text-dark">Cara menulis</label>
                        <select id="write-mode" class="form-select" aria-describedby="write-help">
                            <option value="manual">Saya tulis sendiri</option>
                            <option value="assisted">Bantu tuliskan dengan AI</option>
                        </select>
                        <small id="write-help" class="text-muted d-block mt-1">Tuliskan ceritanya dengan bahasa Anda atau gunakan bantuan AI untuk menyusun draf berita.</small>
                    </div>

                    <div id="facts-panel" hidden class="bg-label-primary rounded-4 p-3 p-md-4 mb-3">
                        <label for="facts" class="form-label fw-bold text-primary">Ceritakan Poin Pentingnya</label>
                        <textarea id="facts" rows="4" maxlength="8000" class="form-control bg-white" placeholder="Contoh: Lomba 17 Agustus. Diikuti warga RT 01. Ada balap karung dan tarik tambang. Setelah lomba, panitia membagikan hadiah."></textarea>
                        <small class="text-primary d-block mt-2" style="opacity: 0.9;">Isi sesuai kejadian. Anda akan melihat dan memilih hasil AI sebelum menggunakannya.</small>
                        <button id="compose" type="button" class="btn btn-primary rounded-pill px-4 mt-3 shadow-sm">
                            <i class="bx bx-magic-wand me-1"></i>Buat Judul dan Cerita dengan AI
                        </button>
                    </div>

                    <div class="mb-3">
                        <label for="title" class="form-label fw-semibold small text-dark">Judul</label>
                        <input id="title" name="title" maxlength="255" class="form-control" placeholder="Contoh: Warga RT 01 Meriahkan Lomba 17 Agustus">
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label fw-semibold small text-dark">{{ $item->post_category === 'Pengumuman' ? 'Isi Pengumuman' : 'Isi Berita Daerah' }}</label>
                        <textarea id="description" name="description" rows="8" maxlength="20000" class="form-control" placeholder="Apa kegiatannya? Siapa yang ikut? Apa saja yang dilakukan dan bagaimana hasilnya?"></textarea>
                        <button id="correct" type="button" class="btn btn-sm btn-outline-info rounded-pill px-3 mt-2">
                            <i class="bx bx-check-double me-1"></i>Periksa Ejaan dengan AI
                        </button>
                    </div>

                    @if($isManager)
                    <details class="border rounded-4 p-3 mb-3 bg-light">
                        <summary class="fw-semibold small text-dark">Atur waktu terbit dan masa berlaku (opsional)</summary>
                        <p class="small text-muted mt-2 mb-3">Biarkan kosong jika ingin langsung terbit setelah Anda menyetujuinya.</p>
                        <div class="row g-3">
                            <div class="col-12 col-sm-6">
                                <label for="scheduled_at" class="form-label fw-semibold small text-dark">Jadwalkan Terbit</label>
                                <input id="scheduled_at" name="scheduled_at" type="datetime-local" class="form-control bg-white">
                            </div>
                            <div class="col-12 col-sm-6">
                                <label for="ends_at" class="form-label fw-semibold small text-dark">Berlaku Sampai</label>
                                <input id="ends_at" name="ends_at" type="datetime-local" class="form-control bg-white">
                            </div>
                        </div>
                    </details>
                    @endif
                </fieldset>

                <p id="saved" class="small text-muted mt-3 mb-2" role="status">Saat Anda mengedit, tulisan disimpan otomatis sebagai draf. Draf belum terbit.</p>
                <div class="d-flex flex-wrap gap-2 mt-2">
                    <button id="save" type="submit" disabled class="btn btn-outline-secondary rounded-pill px-4">Simpan Draf</button>
                    <button id="submit" type="button" hidden class="btn btn-primary rounded-pill px-4 shadow-sm">Kirim ke Admin</button>
                    <button id="publish" type="button" hidden class="btn btn-success rounded-pill px-4 shadow-sm">Periksa Hasil dan Terbitkan</button>
                </div>
                <p id="send-help" class="small text-muted mt-2 mb-0">{{ $isManager ? 'Anda akan melihat hasil akhir sebelum menyetujui penerbitan.' : 'Kiriman masuk ke admin RT, RW, dan desa sesuai wilayah akun Anda.' }}</p>
            </form>

            <!-- AI Panels -->
            <div id="ai-unavailable" hidden class="alert alert-warning rounded-4 mt-4 mb-0">
                <p class="mb-2 small">AI belum dapat membantu saat ini. Anda tetap bisa melanjutkan setelah memeriksa tulisan sendiri.</p>
                <button id="continue-manual" type="button" class="btn btn-sm btn-outline-dark rounded-pill">Sudah Saya Periksa, Lanjutkan</button>
            </div>

            <div id="ai-progress" hidden class="segi-ai-progress mt-4" role="status" aria-live="polite">
                <strong id="ai-progress-label">AI sedang memproses tulisan…</strong>
                <p class="small mb-0 mt-1">Tunggu sebentar. Hasil akan muncul di bawah untuk Anda periksa.</p>
                <div class="segi-ai-scan" aria-hidden="true"></div>
            </div>

            <section id="ai-preview" hidden class="mt-4 border rounded-4 p-3 p-md-4 bg-light" aria-labelledby="ai-heading">
                <h6 id="ai-heading" class="fw-bold text-dark mb-1">Pilih tulisan yang ingin dipakai</h6>
                <p class="small text-muted mb-3">Periksa nama, tanggal, dan fakta. Memilih tulisan di sini belum menerbitkan kabar.</p>
                <div class="row g-3">
                    <div class="col-12 col-md-6">
                        <div class="bg-white rounded-3 p-3 border h-100">
                            <span class="badge bg-label-secondary mb-2">Tulisan Anda</span>
                            <p id="original-title" class="fw-bold text-dark mb-2"></p>
                            <p id="original-body" class="small mb-0" style="white-space: pre-wrap;"></p>
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <div class="bg-white rounded-3 p-3 border border-primary h-100">
                            <span class="badge bg-label-primary mb-2">Usulan AI</span>
                            <p id="suggested-title" class="fw-bold text-primary mb-2"></p>
                            <p id="suggested-body" class="small mb-0" style="white-space: pre-wrap;"></p>
                            <ul id="ai-questions" class="mt-3 ps-3 small text-muted mb-0"></ul>
                        </div>
                    </div>
                </div>
                <div class="d-flex flex-wrap gap-2 mt-3">
                    <button id="use-ai" type="button" class="btn btn-primary rounded-pill px-4">Pakai Hasil AI</button>
                    <button id="keep-original" type="button" class="btn btn-outline-secondary rounded-pill px-4">Tetap Pakai Tulisan Saya</button>
                </div>
            </section>
        </div>
    </div>

    <!-- Manager Actions & Activity History -->
    @if($isManager)
    <div class="row g-3">
        <div class="col-12 col-lg-6">
            <details class="card border-0 shadow-sm rounded-4 p-3 p-md-4">
                <summary class="fw-bold text-danger">Tidak diterbitkan / batalkan kegiatan</summary>
                <p class="small text-muted mt-2">Cerita kurang rapi bisa Anda perbaiki langsung. Gunakan keputusan ini jika kiriman memang tidak dapat diterbitkan atau kegiatannya dibatalkan.</p>
                <div class="mb-2">
                    <label for="reason-choice" class="form-label small fw-semibold">Pilih alasan</label>
                    <select id="reason-choice" class="form-select form-select-sm">
                        <option value="">Pilih alasan</option>
                        <option>Informasi tidak dapat dikonfirmasi</option>
                        <option>Sudah dimuat dalam kabar lain</option>
                        <option>Tidak sesuai kegiatan wilayah</option>
                        <option>Mengandung data pribadi yang tidak boleh disebarkan</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="reason-text" class="form-label small fw-semibold">Penjelasan tambahan / alasan sendiri</label>
                    <textarea id="reason-text" rows="3" maxlength="1000" class="form-control form-control-sm" placeholder="Jelaskan alasannya dengan singkat dan sopan. Warga akan menerima penjelasan ini."></textarea>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <button id="reject" type="button" disabled class="btn btn-sm btn-outline-danger rounded-pill px-3">Tidak Diterbitkan</button>
                    <button id="cancel" type="button" hidden class="btn btn-sm btn-danger rounded-pill px-3">Batalkan Kegiatan</button>
                </div>
            </details>
        </div>
        <div class="col-12 col-lg-6">
            <details class="card border-0 shadow-sm rounded-4 p-3 p-md-4">
                <summary class="fw-bold text-dark">Riwayat Penanganan</summary>
                <p class="small text-muted mt-2">Daftar admin yang membuka, mengedit, dan mengambil keputusan.</p>
                <ol id="activity" class="ps-3 small text-muted mb-0"></ol>
            </details>
        </div>
    </div>
    @endif

    <!-- Native Dialogs for Publish Review & Source Picker -->
    <dialog id="publish-review" aria-labelledby="review-heading">
        <h5 id="review-heading" class="fw-bold text-dark">Periksa Sebelum Terbit</h5>
        <p id="review-error" role="alert" hidden class="text-danger small mt-2"></p>
        <p id="review-target" class="small fw-semibold text-primary mt-2"></p>
        <p id="review-meta" class="small text-muted mt-1"></p>
        <div id="review-photos" class="mt-3"></div>
        <h6 id="review-title" class="fw-bold text-dark mt-3"></h6>
        <p id="review-body" class="small mt-2" style="white-space: pre-wrap; max-height: 260px; overflow: auto;"></p>
        <p class="small text-muted mt-3">Pastikan fakta, waktu, dan penerimanya sudah sesuai.</p>
        <div class="d-flex flex-wrap gap-2 mt-3">
            <button id="confirm-publish" type="button" class="btn btn-success rounded-pill px-4">Ya, Terbitkan</button>
            <button id="close-review" type="button" class="btn btn-outline-secondary rounded-pill px-4">Kembali Mengedit</button>
        </div>
    </dialog>

    @if($isManager && $item->post_category === 'Berita')
    <dialog id="source-picker" aria-labelledby="source-heading">
        <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
            <div>
                <h5 id="source-heading" class="fw-bold text-dark mb-1">Pilih Foto dan Cerita Warga</h5>
                <p class="small text-muted mb-0">Pilih kiriman tentang kegiatan yang sama. Foto dan cerita boleh diambil dari warga yang berbeda.</p>
            </div>
            <button id="close-sources" type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3">Tutup</button>
        </div>
        <p id="source-error" role="alert" hidden class="small text-danger mt-2"></p>
        <form id="source-search-form" class="d-flex gap-2 mt-3">
            <input id="source-search" type="search" maxlength="100" class="form-control" placeholder="Cari judul atau nama warga">
            <button id="source-search-button" type="submit" class="btn btn-primary rounded-pill px-4">Cari</button>
        </form>
        <p id="source-results" class="small text-muted mt-2" role="status"></p>
        <div id="source-options" class="mt-3"></div>
        <div class="d-flex justify-content-between align-items-center gap-2 mt-3">
            <button id="sources-prev" type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3">Sebelumnya</button>
            <span id="source-page" class="small text-muted"></span>
            <button id="sources-next" type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3">Berikutnya</button>
        </div>
        <div class="border-top mt-4 pt-3">
            <p id="source-selection" role="status" class="fw-semibold small mb-1">Belum ada foto atau cerita yang dipilih.</p>
            <p class="small text-muted mb-2">Pilihan tetap tersimpan saat Anda mencari atau pindah halaman.</p>
            <button id="merge" type="button" disabled class="btn btn-primary rounded-pill px-4">Periksa Pilihan</button>
        </div>
        <section id="merge-review" hidden class="mt-3 p-3 bg-label-primary rounded-4" aria-labelledby="merge-heading">
            <h6 id="merge-heading" class="fw-bold text-primary">Gabungkan ke berita ini?</h6>
            <ul id="merge-summary" class="ps-3 small mt-2 mb-2"></ul>
            <p class="small text-dark mb-2">Foto masuk ke galeri. Cerita tersedia untuk ditambahkan ke tulisan atau dirapikan dengan AI.</p>
            <div class="d-flex flex-wrap gap-2 mt-3">
                <button id="confirm-merge" type="button" class="btn btn-primary rounded-pill px-4">Ya, Gabungkan ke Draf</button>
                <button id="change-selection" type="button" class="btn btn-outline-secondary rounded-pill px-4">Ubah Pilihan</button>
            </div>
        </section>
    </dialog>
    @endif
</div>

<script id="content-initial" type="application/json">{!! json_encode($data, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
@push('scripts')
<script type="module" src="{{ asset('js/content-workspace.js') }}?v={{ filemtime(public_path('js/content-workspace.js')) }}"></script>
@endpush
@endsection
