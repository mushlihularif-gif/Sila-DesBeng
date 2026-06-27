@extends('admin.layouts.admin')

@section('title', 'Profil Pemerintah Daerah')

@section('content')
<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-12 col-lg-10">
            <!-- HEADER -->
            <h2 class="text-primary fw-bold mb-4">Struktur <span class="text-info">Pemerintah Daerah</span></h2>

            <!-- GRID MEMBERS -->
            <div class="row g-4">
                @foreach($members as $member)
                    <div class="col-md-4">
                        <div class="card shadow-sm border-0 rounded-4 overflow-hidden text-center h-100" style="transition: transform 0.3s ease, box-shadow 0.3s ease;" onmouseover="this.style.transform='translateY(-5px)'; this.classList.replace('shadow-sm', 'shadow-lg');" onmouseout="this.style.transform='translateY(0)'; this.classList.replace('shadow-lg', 'shadow-sm');">
                            <div class="position-relative w-100">
                                <!-- Gradasi putih di bagian bawah foto agar menyatu dengan background konten -->
                                <div class="position-absolute w-100 h-100" style="background: linear-gradient(to bottom, rgba(255,255,255,0) 60%, rgba(255,255,255,1) 100%); pointer-events: none; bottom: 0; left: 0; z-index: 1;"></div>
                                <img src="{{ $member->photo_url }}" 
                                     alt="{{ $member->name }}"
                                     class="w-100" 
                                     style="height: 280px; object-fit: cover; object-position: top; z-index: 0;">
                            </div>
                            <div class="card-body p-4 bg-white position-relative" style="z-index: 2; margin-top: -20px;">
                                <h5 class="fw-bold text-dark mb-1">{{ $member->name }}</h5>
                                <p class="text-muted mb-4" style="font-size: 14px; font-weight: 500;">{{ $member->position }}</p>
                                
                                <!-- ACTION BUTTONS -->
                                <div class="d-flex justify-content-center gap-2">
                                    <a href="{{ route('admin.siladesbeng.bumdes.edit', $member->id) }}" class="btn btn-sm btn-outline-primary px-4 rounded-pill" style="font-weight: 500;">
                                        <i class="bi bi-pencil"></i> Edit
                                    </a>
                                    <form action="{{ route('admin.siladesbeng.bumdes.destroy', $member->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus {{ $member->name }}?');" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger px-4 rounded-pill" style="font-weight: 500;">
                                            <i class="bi bi-trash"></i> Hapus
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- TOMBOL TAMBAH ANGGOTA -->
            <div class="mt-4 text-center">
                <a href="{{ route('admin.siladesbeng.bumdes.create') }}" class="btn btn-primary px-4 py-2 fw-bold">
                    <i class="bi bi-plus"></i> Tambah Anggota
                </a>
            </div>
        </div>
    </div>
</div>
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