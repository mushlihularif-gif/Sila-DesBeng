@extends('admin.layouts.admin')

@section('title', 'Profil Pemerintah Daerah')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-12">
            <!-- HEADER -->
            <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-3 mb-4">
                <div>
                    <h4 class="text-primary fw-bold mb-1">Struktur <span class="text-info">
                        @if(in_array(auth()->user()->role, ['admin', 'super_admin']))
                            Pemerintah Kabupaten Bengkalis
                        @elseif(auth()->user()->role == 'admin_kecamatan')
                            Pemerintah Kecamatan {{ str_ireplace('Kecamatan ', '', auth()->user()->region->name ?? 'Daerah') }}
                        @else
                            Pemerintah Desa {{ str_ireplace('Desa ', '', auth()->user()->region->name ?? 'Daerah') }}
                        @endif
                    </span></h4>
                    <p class="text-muted mb-0 small">Daftar susunan aparatur pemerintah daerah berjenjang berdasarkan tingkatan hierarki jabatan</p>
                </div>
                <div class="d-flex gap-2 w-100 w-sm-auto">
                    <a href="{{ route('admin.SiladesBeng.bumdes.create') }}" class="btn btn-primary shadow-sm flex-fill flex-sm-grow-0">
                        <i class="bx bx-plus me-1"></i> Tambah Anggota
                    </a>
                </div>
            </div>

            <!-- INFO CARD HIERARKI -->
            <div class="alert alert-primary border-0 shadow-sm rounded-3 mb-4 p-3 d-flex align-items-center">
                <i class="bx bx-sitemap fs-3 me-3 text-primary"></i>
                <div class="small">
                    <strong>Hierarki Struktur Organisasi:</strong> Aparatur ditata secara berjenjang (Tingkat 1 s.d. 4) sehingga pada halaman publik tidak lagi sejajar dalam satu baris. Gunakan tombol panah pada setiap kartu atau ubah tingkatan untuk menyesuaikan posisi aparatur di bawah pimpinan.
                </div>
            </div>

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert">
                    <i class="bx bx-check-circle me-1"></i> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if(session('info'))
                <div class="alert alert-info alert-dismissible fade show border-0 shadow-sm" role="alert">
                    <i class="bx bx-info-circle me-1"></i> {{ session('info') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @php
                $levelsConfig = [
                    1 => [
                        'title' => 'Tingkat 1 - Pimpinan Utama',
                        'subtitle' => 'Puncak struktur bagan (baris teratas)',
                        'badge' => 'bg-primary text-white',
                        'border' => 'border-primary',
                        'icon' => 'bx-crown',
                        'example' => in_array(auth()->user()->role, ['admin', 'super_admin']) ? 'Bupati' : (auth()->user()->role == 'admin_kecamatan' ? 'Camat' : 'Kepala Desa / Lurah')
                    ],
                    2 => [
                        'title' => 'Tingkat 2 - Pimpinan Kedua / Sekretaris',
                        'subtitle' => 'Baris kedua di bawah Pimpinan Utama',
                        'badge' => 'bg-info text-white',
                        'border' => 'border-info',
                        'icon' => 'bx-user-check',
                        'example' => in_array(auth()->user()->role, ['admin', 'super_admin']) ? 'Wakil Bupati / Sekda' : (auth()->user()->role == 'admin_kecamatan' ? 'Sekretaris Camat (Sekcam)' : 'Sekretaris Desa (Sekdes)')
                    ],
                    3 => [
                        'title' => 'Tingkat 3 - Kepala Seksi / Kaur / Kepala Unit',
                        'subtitle' => 'Baris ketiga di bawah Sekretaris',
                        'badge' => 'bg-success text-white',
                        'border' => 'border-success',
                        'icon' => 'bx-briefcase-alt-2',
                        'example' => in_array(auth()->user()->role, ['admin', 'super_admin']) ? 'Kepala Dinas / Bagian' : (auth()->user()->role == 'admin_kecamatan' ? 'Kasi / Kasubag' : 'Kasi Pem, Kaur Keuangan, Kepala Unit Usaha')
                    ],
                    4 => [
                        'title' => 'Tingkat 4 - Staf Pelaksana / Aparatur Lainnya',
                        'subtitle' => 'Baris keempat di bawah Kepala Seksi/Unit',
                        'badge' => 'bg-secondary text-white',
                        'border' => 'border-secondary',
                        'icon' => 'bx-group',
                        'example' => in_array(auth()->user()->role, ['admin', 'super_admin']) ? 'Staf Teknis' : (auth()->user()->role == 'admin_kecamatan' ? 'Staf Kecamatan' : 'Kepala Dusun (Kadus), Staf Desa')
                    ],
                ];

                $groupedMembers = $members->groupBy('level');
            @endphp

            @if($members->count() === 0)
                <div class="w-100 text-center py-5">
                    <div class="card border-0 shadow-sm rounded-4 p-5">
                        <div class="text-center">
                            <i class="bx bx-user-x text-muted" style="font-size: 4rem;"></i>
                            <h5 class="fw-bold mt-3 mb-1">Belum Ada Anggota Struktur</h5>
                            <p class="text-muted mb-3">Silakan tambahkan anggota struktur organisasi pemerintah daerah pertama Anda.</p>
                            <a href="{{ route('admin.SiladesBeng.bumdes.create') }}" class="btn btn-primary shadow-sm">
                                <i class="bx bx-plus me-1"></i> Tambah Anggota Pertama
                            </a>
                        </div>
                    </div>
                </div>
            @else
                @foreach($levelsConfig as $levelNum => $cfg)
                    @php
                        $levelMembers = $groupedMembers->get($levelNum, collect());
                    @endphp
                    <div class="card border-0 shadow-sm rounded-4 mb-4">
                        <div class="card-header bg-transparent border-bottom py-3 d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2">
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge {{ $cfg['badge'] }} px-3 py-2 rounded-pill d-flex align-items-center gap-1 font-monospace">
                                    <i class="bx {{ $cfg['icon'] }}"></i> {{ $cfg['title'] }}
                                </span>
                                <span class="text-muted small d-none d-md-inline">({{ $cfg['subtitle'] }})</span>
                            </div>
                            <span class="badge bg-label-dark rounded-pill">
                                {{ $levelMembers->count() }} Pejabat
                            </span>
                        </div>
                        <div class="card-body p-3 p-md-4">
                            @if($levelMembers->count() === 0)
                                <div class="text-center py-4 text-muted border border-dashed rounded-3 bg-light-subtle">
                                    <i class="bx bx-user-plus fs-2 mb-1"></i>
                                    <p class="mb-0 small">Belum ada aparatur di tingkatan ini (Contoh: {{ $cfg['example'] }}).</p>
                                </div>
                            @else
                                <div class="members-container">
                                    @foreach($levelMembers as $member)
                                        <div class="member-card">
                                            <!-- Top Tier Indicator -->
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <span class="badge bg-label-primary px-2 py-1" style="font-size: 0.7rem;">
                                                    Urutan #{{ $member->order }}
                                                </span>
                                                <!-- Quick Move Buttons -->
                                                <div class="d-flex gap-1">
                                                    <form action="{{ route('admin.SiladesBeng.bumdes.move-up', $member->id) }}" method="POST" class="d-inline m-0">
                                                        @csrf
                                                        <button type="submit" class="btn btn-xs btn-outline-secondary p-1 rounded" title="Geser Naik / Ke Tingkat Atas">
                                                            <i class="bx bx-chevron-up fs-6"></i>
                                                        </button>
                                                    </form>
                                                    <form action="{{ route('admin.SiladesBeng.bumdes.move-down', $member->id) }}" method="POST" class="d-inline m-0">
                                                        @csrf
                                                        <button type="submit" class="btn btn-xs btn-outline-secondary p-1 rounded" title="Geser Turun / Ke Tingkat Bawah">
                                                            <i class="bx bx-chevron-down fs-6"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>

                                            <div class="member-photo-wrapper">
                                                <img src="{{ $member->photo_url }}" 
                                                     alt="{{ $member->name }}"
                                                     class="member-photo">
                                            </div>
                                            <h5 class="member-name">{{ $member->name }}</h5>
                                            <p class="member-position">{{ $member->position }}</p>
                                            
                                            <!-- Level Selector Dropdown -->
                                            <div class="mb-3 w-100">
                                                <form action="{{ route('admin.SiladesBeng.bumdes.change-level', $member->id) }}" method="POST">
                                                    @csrf
                                                    <div class="input-group input-group-sm">
                                                        <span class="input-group-text bg-light text-muted" style="font-size: 0.72rem;">Tingkat:</span>
                                                        <select name="level" class="form-select form-select-sm" style="font-size: 0.75rem;" onchange="this.form.submit()">
                                                            <option value="1" {{ $member->level == 1 ? 'selected' : '' }}>1 - Pimpinan</option>
                                                            <option value="2" {{ $member->level == 2 ? 'selected' : '' }}>2 - Sekretaris</option>
                                                            <option value="3" {{ $member->level == 3 ? 'selected' : '' }}>3 - Kasi / Unit</option>
                                                            <option value="4" {{ $member->level == 4 ? 'selected' : '' }}>4 - Staf</option>
                                                        </select>
                                                    </div>
                                                </form>
                                            </div>

                                            <!-- ACTION BUTTONS -->
                                            <div class="d-flex justify-content-center gap-1 gap-sm-2 mt-auto w-100">
                                                <a href="{{ route('admin.SiladesBeng.bumdes.edit', $member->id) }}" class="btn btn-sm btn-outline-primary rounded-pill member-btn flex-fill d-flex align-items-center justify-content-center">
                                                    <i class="bx bx-pencil me-1"></i> Edit
                                                </a>
                                                <form action="{{ route('admin.SiladesBeng.bumdes.destroy', $member->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus {{ $member->name }}?');" class="flex-fill d-inline m-0">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill member-btn w-100 d-flex align-items-center justify-content-center">
                                                        <i class="bx bx-trash me-1"></i> Hapus
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            @endif
        </div>
    </div>
</div>

<style>
    /* Container Kartu */
    .members-container {
        display: flex;
        flex-wrap: wrap;
        gap: 0.75rem;
    }

    /* Member Card Base */
    .member-card {
        background: white;
        border: 1px solid #e2e8f0;
        border-radius: 0.85rem;
        padding: 0.85rem 0.65rem;
        box-shadow: 0 1px 3px rgba(0,0,0,0.04);
        text-align: center;
        width: calc(50% - 0.375rem);
        max-width: calc(50% - 0.375rem);
        flex: 0 0 calc(50% - 0.375rem);
        transition: all 0.3s ease;
        display: flex;
        flex-direction: column;
    }

    .member-card:hover {
        box-shadow: 0 6px 20px rgba(0,0,0,0.08);
        border-color: #cbd5e1;
        transform: translateY(-2px);
    }

    .member-photo-wrapper {
        width: 100%;
        aspect-ratio: 1 / 1.1;
        margin: 0 auto;
        overflow: hidden;
        border-radius: 0.65rem;
        background: #f8fafc;
    }

    .member-photo {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }

    .member-name {
        font-size: 0.875rem;
        font-weight: 700;
        margin-top: 0.65rem;
        margin-bottom: 0.2rem;
        line-height: 1.25;
        color: #344054;
    }

    .member-position {
        font-size: 0.75rem;
        font-weight: 500;
        color: #667085;
        margin-bottom: 0.75rem;
        line-height: 1.25;
    }

    .member-btn {
        font-size: 0.75rem;
        padding: 0.3rem 0.45rem;
        font-weight: 500;
    }

    /* Tampilan Desktop & Tablet: Rapat, Rapi & Elegan */
    @media (min-width: 768px) {
        .members-container {
            gap: 1.25rem;
        }

        .member-card {
            border-radius: 1rem;
            padding: 1.25rem 1rem;
            width: 250px;
            max-width: 250px;
            flex: 0 0 250px;
        }

        .member-photo-wrapper {
            width: 185px;
            height: 200px;
            aspect-ratio: auto;
        }

        .member-name {
            font-size: 1rem;
            margin-top: 0.85rem;
            margin-bottom: 0.25rem;
        }

        .member-position {
            font-size: 0.825rem;
            margin-bottom: 0.85rem;
        }

        .member-btn {
            font-size: 0.8rem;
            padding: 0.375rem 0.75rem;
        }
    }
</style>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        // Sidebar Active
        const sidebarLink = document.querySelector('a[href="{{ route("admin.SiladesBeng.bumdes.index") }}"]');
        if (sidebarLink) {
            const listItem = sidebarLink.closest('li');
            if (listItem) {
                listItem.classList.add('active');
            }
        }
    });
</script>
@endsection
