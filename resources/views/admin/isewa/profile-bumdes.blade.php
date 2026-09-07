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
                    <p class="text-muted mb-0 small">Daftar susunan dan profil pejabat aparatur pemerintah daerah</p>
                </div>
                <a href="{{ route('admin.SiladesBeng.bumdes.create') }}" class="btn btn-primary btn-add-member shadow-sm">
                    <i class="bx bx-plus me-1"></i> Tambah Anggota
                </a>
            </div>

            <!-- GRID MEMBERS (2 Kolom Pas di HP, Jarak Rapat di Desktop) -->
            <div class="members-container mt-3">
                @forelse($members as $member)
                    <div class="member-card">
                        <div class="member-photo-wrapper">
                            <img src="{{ $member->photo_url }}" 
                                 alt="{{ $member->name }}"
                                 class="member-photo">
                        </div>
                        <h5 class="member-name">{{ $member->name }}</h5>
                        <p class="member-position">{{ $member->position }}</p>
                        
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
                @empty
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
                @endforelse
            </div>
        </div>
    </div>
</div>

<style>
    /* Button Tambah Anggota */
    .btn-add-member {
        width: 100%;
        font-weight: 500;
    }

    /* Container Kartu */
    .members-container {
        display: flex;
        flex-wrap: wrap;
        gap: 0.65rem;
    }

    /* Member Card Base (Mobile: Pas 2 Kotak per Baris) */
    .member-card {
        background: white;
        border-radius: 0.85rem;
        padding: 0.85rem 0.6rem;
        box-shadow: 0 1px 3px rgba(0,0,0,0.06), 0 4px 12px rgba(0,0,0,0.04);
        text-align: center;
        width: calc(50% - 0.325rem);
        max-width: calc(50% - 0.325rem);
        flex: 0 0 calc(50% - 0.325rem);
        transition: all 0.3s ease;
        display: flex;
        flex-direction: column;
    }

    .member-card:hover {
        box-shadow: 0 8px 30px rgba(0,0,0,0.1);
        transform: translateY(-3px);
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
        .btn-add-member {
            width: auto !important;
            flex-shrink: 0;
            white-space: nowrap;
        }

        .members-container {
            gap: 1.25rem; /* Jarak antar kartu rapat hanya 20px */
        }

        .member-card {
            border-radius: 1rem;
            padding: 1.5rem 1.15rem 1.25rem;
            width: 250px;
            max-width: 250px;
            flex: 0 0 250px;
        }

        .member-photo-wrapper {
            width: 185px;
            height: 210px;
            aspect-ratio: auto;
        }

        .member-name {
            font-size: 1.05rem;
            margin-top: 1rem;
            margin-bottom: 0.25rem;
        }

        .member-position {
            font-size: 0.85rem;
            margin-bottom: 1rem;
        }

        .member-btn {
            font-size: 0.825rem;
            padding: 0.375rem 0.85rem;
        }
    }
</style>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        // Efek animasi saat scroll
        const sections = document.querySelectorAll('.card, .row');

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        });

        sections.forEach(section => {
            section.style.opacity = '0';
            section.style.transform = 'translateY(30px)';
            section.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
            observer.observe(section);
        });

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


