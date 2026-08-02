@extends('admin.layouts.admin')

@section('title', 'Kabar dan Informasi Daerah & Pengumuman')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Sistem /</span> Kabar dan Informasi Daerah & Pengumuman</h4>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="nav-align-top mb-4">
        <ul class="nav nav-tabs" role="tablist">
            <li class="nav-item">
                <button type="button" class="nav-link {{ $tab == 'berita' ? 'active' : '' }}" role="tab" data-bs-toggle="tab" data-bs-target="#navs-top-berita" aria-controls="navs-top-berita" aria-selected="{{ $tab == 'berita' ? 'true' : 'false' }}"><i class="bx bx-news me-1"></i> Berita Daerah</button>
            </li>
            <li class="nav-item">
                <button type="button" class="nav-link {{ $tab == 'pengumuman' ? 'active' : '' }}" role="tab" data-bs-toggle="tab" data-bs-target="#navs-top-pengumuman" aria-controls="navs-top-pengumuman" aria-selected="{{ $tab == 'pengumuman' ? 'true' : 'false' }}"><i class="bx bx-bell me-1"></i> Pengumuman Warga</button>
            </li>
        </ul>
        
        <div class="tab-content">
            <!-- TAB 1: BERITA DAERAH -->
            <div class="tab-pane fade {{ $tab == 'berita' ? 'show active' : '' }}" id="navs-top-berita" role="tabpanel">
                
                <div class="alert alert-info d-flex align-items-center mb-4" role="alert">
                    <span class="alert-icon text-info me-2">
                        <i class="bx bx-info-circle bx-sm"></i>
                    </span>
                    <div>
                        <strong>Kapan menggunakan Berita Daerah?</strong><br>
                        Gunakan untuk mempublikasikan <b>dokumentasi kegiatan, acara yang telah/sedang berlangsung, atau pencapaian</b> desa/kecamatan Anda. Berita ini akan tampil untuk seluruh warga se-Kabupaten.<br>
                        <i>Contoh: "Keseruan Lomba 17 Agustus di Desa X", "Senam Massal Hari Minggu", "Pembangunan Jalan Sukses Diselesaikan".</i>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h5 class="mb-0">Daftar Berita Daerah</h5>
                        <small class="text-muted">Dokumentasi kegiatan dan informasi publik lintas wilayah.</small>
                    </div>
                    <div>
                        <a href="{{ route('admin.announcements.create', ['category' => 'Berita']) }}" class="btn btn-primary"><i class="bx bx-plus"></i> Buat Berita Baru</a>
                    </div>
                </div>
                
                <div class="border-bottom pb-3 mb-3">
                    <form class="row g-3 align-items-center" method="GET">
                        <input type="hidden" name="tab" value="berita">
                        <div class="col-md-6">
                            <input type="text" name="search" class="form-control" placeholder="Cari judul berita..." value="{{ request('search') }}">
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-secondary w-100">Cari</button>
                        </div>
                    </form>
                </div>

                @include('admin.announcements.partials.table', ['announcements' => $beritas, 'category' => 'Berita'])
            </div>

            <!-- TAB 2: PENGUMUMAN WARGA -->
            <div class="tab-pane fade {{ $tab == 'pengumuman' ? 'show active' : '' }}" id="navs-top-pengumuman" role="tabpanel">
                
                <div class="alert alert-warning d-flex align-items-center mb-4" role="alert">
                    <span class="alert-icon text-warning me-2">
                        <i class="bx bx-error-circle bx-sm"></i>
                    </span>
                    <div>
                        <strong>Kapan menggunakan Pengumuman Warga?</strong><br>
                        Gunakan untuk memberikan <b>instruksi, peringatan, jadwal layanan, atau himbauan penting</b> yang ditujukan khusus untuk warga di wilayah tertentu.<br>
                        <i>Contoh: "Jadwal Posyandu Balita Bulan Ini", "Pemadaman Listrik Sementara Besok Pagi", "Himbauan Waspada Banjir".</i>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h5 class="mb-0">Daftar Pengumuman Warga</h5>
                        <small class="text-muted">Informasi teknis dan instruksi yang ditujukan untuk wilayah spesifik.</small>
                    </div>
                    <div>
                        <a href="{{ route('admin.announcements.create', ['category' => 'Pengumuman']) }}" class="btn btn-primary"><i class="bx bx-plus"></i> Buat Pengumuman Baru</a>
                    </div>
                </div>

                <div class="border-bottom pb-3 mb-3">
                    <form class="row g-3 align-items-center" method="GET">
                        <input type="hidden" name="tab" value="pengumuman">
                        <div class="col-md-3">
                            <select id="filter_type" name="type" class="form-select" onchange="this.form.submit()">
                                <option value="">Semua Tipe</option>
                                <option value="Pengumuman" {{ request('type') == 'Pengumuman' ? 'selected' : '' }}>Pengumuman</option>
                                <option value="Event" {{ request('type') == 'Event' ? 'selected' : '' }}>Event</option>
                                <option value="Gotong Royong" {{ request('type') == 'Gotong Royong' ? 'selected' : '' }}>Gotong Royong</option>
                            </select>
                        </div>
                        @if(in_array(auth()->user()->role, ['super_admin', 'admin']))
                        <div class="col-md-4">
                            <select id="filter_kecamatan_id" name="filter_kecamatan_id" class="form-select" onchange="this.form.submit()">
                                <option value="">-- Semua Kecamatan --</option>
                                @foreach($kecamatanOptions as $opt)
                                    <option value="{{ $opt->id }}" {{ request('filter_kecamatan_id') == $opt->id ? 'selected' : '' }}>
                                        {{ $opt->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <select id="filter_desa_id" name="filter_desa_id" class="form-select" onchange="this.form.submit()">
                                <option value="">-- Semua Desa --</option>
                                @foreach($desaOptions as $opt)
                                    <option value="{{ $opt->id }}" {{ request('filter_desa_id') == $opt->id ? 'selected' : '' }}>
                                        {{ $opt->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        @elseif(auth()->user()->role === 'admin_kecamatan')
                        <div class="col-md-4">
                            <select id="filter_desa_id" name="filter_desa_id" class="form-select" onchange="this.form.submit()">
                                <option value="">-- Semua Desa --</option>
                                @foreach($desaOptions as $opt)
                                    <option value="{{ $opt->id }}" {{ request('filter_desa_id') == $opt->id ? 'selected' : '' }}>
                                        {{ $opt->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        @endif
                    </form>
                </div>

                @include('admin.announcements.partials.table', ['announcements' => $pengumumans, 'category' => 'Pengumuman'])
            </div>
        </div>
    </div>
</div>
@endsection
