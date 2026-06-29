@extends('admin.layouts.admin')

@section('title', 'Profil Pemerintah Daerah')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-12">
            <!-- HEADER -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="text-primary fw-bold mb-0">Struktur <span class="text-info">
                    @if(in_array(auth()->user()->role, ['admin', 'super_admin']))
                        Pemerintah Kabupaten Bengkalis
                    @elseif(auth()->user()->role == 'admin_kecamatan')
                        Pemerintah Kecamatan {{ auth()->user()->region->name ?? 'Daerah' }}
                    @else
                        Pemerintah Desa {{ auth()->user()->region->name ?? 'Daerah' }}
                    @endif
                </span></h4>
                <a href="{{ route('admin.siladesbeng.bumdes.create') }}" class="btn btn-primary">
                    <i class="bx bx-plus me-1"></i> Tambah Anggota
                </a>
            </div>

            <!-- GRID MEMBERS -->
            <div class="d-flex flex-wrap gap-4 mt-4">
                @foreach($members as $member)
                    <div class="member-card">
                        <div class="member-photo-wrapper">
                            <img src="{{ $member->photo_url }}" 
                                 alt="{{ $member->name }}"
                                 class="member-photo">
                        </div>
                        <h5 class="fw-bold text-dark mt-4 mb-1" style="font-size: 1.1rem;">{{ $member->name }}</h5>
                        <p class="text-muted mb-4" style="font-size: 0.9rem; font-weight: 500;">{{ $member->position }}</p>
                        
                        <!-- ACTION BUTTONS -->
                        <div class="d-flex justify-content-center gap-2 mt-auto">
                            <a href="{{ route('admin.siladesbeng.bumdes.edit', $member->id) }}" class="btn btn-sm btn-outline-primary px-3 rounded-pill" style="font-weight: 500;">
                                <i class="bx bx-pencil me-1"></i> Edit
                            </a>
                            <form action="{{ route('admin.siladesbeng.bumdes.destroy', $member->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus {{ $member->name }}?');" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger px-3 rounded-pill" style="font-weight: 500;">
                                    <i class="bx bx-trash me-1"></i> Hapus
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

<style>
    /* Member Card Styles (Sesuai dengan User View) */
    .member-card {
        background: white;
        border-radius: 1rem;
        padding: 2rem 1.5rem 1.5rem;
        box-shadow: 0 1px 3px rgba(0,0,0,0.06), 0 4px 12px rgba(0,0,0,0.04);
        text-align: center;
        width: 280px;
        transition: all 0.3s ease;
        display: flex;
        flex-direction: column;
    }

    .member-card:hover {
        box-shadow: 0 8px 30px rgba(0,0,0,0.1);
        transform: translateY(-4px);
    }

    .member-photo-wrapper {
        width: 210px;
        height: 230px;
        margin: 0 auto;
        overflow: hidden;
        border-radius: 0.75rem;
        background: #f8fafc;
    }

    .member-photo {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
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
        const sidebarLink = document.querySelector('a[href="{{ route("admin.siladesbeng.bumdes.index") }}"]');
        if (sidebarLink) {
            const listItem = sidebarLink.closest('li');
            if (listItem) {
                listItem.classList.add('active');
            }
        }
    });
</script>
@endsection
